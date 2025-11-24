<?php
header('Content-Type: application/json');
require_once "../../../includes/db.php"; // Ajusta la ruta según tu estructura real

$method = $_SERVER['REQUEST_METHOD'];
$data = json_decode(file_get_contents('php://input'), true);

switch ($method) {
    case 'GET':
        try {
            $params = [];
            // Unimos 'citas' con 'clientes' para obtener datos extra
            $sql = "SELECT citas.*, clientes.dni_ruc, clientes.nombre as nombre_cliente_real 
                    FROM citas 
                    LEFT JOIN clientes ON citas.cliente_id = clientes.id 
                    WHERE 1=1";

            // --- NUEVO FILTRO: POR ID DE CLIENTE ---
            if (!empty($_GET['cliente_id'])) {
                $sql .= " AND citas.cliente_id = :cliente_id";
                $params[':cliente_id'] = $_GET['cliente_id'];
            }
            // ---------------------------------------

            if (!empty($_GET['fecha'])) {
                $sql .= " AND citas.fecha = :fecha";
                $params[':fecha'] = $_GET['fecha'];
            }
            if (!empty($_GET['estado']) && $_GET['estado'] != 'todas') {
                $sql .= " AND citas.estado = :estado";
                $params[':estado'] = $_GET['estado'];
            }
            // Filtros generales
            if (!empty($_GET['telefono'])) {
                $sql .= " AND citas.telefono_cliente LIKE :telefono";
                $params[':telefono'] = '%' . $_GET['telefono'] . '%';
            }
            if (!empty($_GET['nombre'])) {
                $sql .= " AND citas.nombre_cliente LIKE :nombre";
                $params[':nombre'] = '%' . $_GET['nombre'] . '%';
            }
            if (!empty($_GET['dni'])) {
                $sql .= " AND clientes.dni_ruc LIKE :dni";
                $params[':dni'] = '%' . $_GET['dni'] . '%';
            }

            $sql .= " ORDER BY citas.fecha DESC, citas.hora ASC";
            $stmt = $conn->prepare($sql);
            $stmt->execute($params);
            echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));

        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode(['error' => 'Error en la base de datos: ' . $e->getMessage()]);
        }
        break;

    // ... (Los métodos PUT y DELETE se mantienen igual que antes) ...
    case 'PUT':
         try {
            $sql = "UPDATE citas SET nombre_cliente = :nombre, telefono_cliente = :telefono, fecha = :fecha, hora = :hora, servicio_solicitado = :servicio, estado = :estado WHERE id = :id";
            $stmt = $conn->prepare($sql);
            $stmt->execute([
                'id' => $data['id'],
                'nombre' => $data['nombre_cliente'],
                'telefono' => $data['telefono_cliente'],
                'fecha' => $data['fecha'],
                'hora' => $data['hora'],
                'servicio' => $data['servicio_solicitado'],
                'estado' => $data['estado']
            ]);
            echo json_encode(['message' => 'Cita actualizada.']);
        } catch (PDOException $e) { http_response_code(500); echo json_encode(['error' => $e->getMessage()]); }
        break;

    case 'DELETE':
        try {
            $id = $_GET['id'] ?? null;
            if (!$id) { http_response_code(400); echo json_encode(['error' => 'ID faltante']); exit; }
            $sql = "UPDATE citas SET estado = 'cancelada' WHERE id = :id";
            $stmt = $conn->prepare($sql);
            $stmt->execute(['id' => $id]);
            echo json_encode(['message' => 'Cita cancelada.']);
        } catch (PDOException $e) { http_response_code(500); echo json_encode(['error' => $e->getMessage()]); }
        break;

    default:
        http_response_code(405);
        echo json_encode(['error' => 'Método no permitido']);
        break;
}
?>