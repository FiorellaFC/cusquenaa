<?php
header('Content-Type: application/json');

// --- CORRECCIÓN DE RUTA PARA SUBCARPETA ---
require_once "../../../includes/db.php"; 

$method = $_SERVER['REQUEST_METHOD'];
$data = json_decode(file_get_contents('php://input'), true);

switch ($method) {
    case 'GET':
        try {
            $params = [];
            $sql = "SELECT id, nombre, telefono, dni_ruc, email FROM clientes WHERE 1=1";

            if (!empty($_GET['nombre'])) {
                $sql .= " AND nombre LIKE :nombre";
                $params[':nombre'] = '%' . $_GET['nombre'] . '%';
            }
            if (!empty($_GET['dni'])) {
                $sql .= " AND dni_ruc LIKE :dni";
                $params[':dni'] = '%' . $_GET['dni'] . '%';
            }

            $sql .= " ORDER BY nombre ASC";
            $stmt = $conn->prepare($sql);
            $stmt->execute($params);
            echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode(['error' => 'Error BD: ' . $e->getMessage()]);
        }
        break;

    case 'POST':
         try {
            $sql = "INSERT INTO clientes (nombre, dni_ruc, telefono, email) VALUES (:nombre, :dni_ruc, :telefono, :email)";
            $stmt = $conn->prepare($sql);
            $stmt->execute([
                'nombre' => $data['nombre'], 'dni_ruc' => $data['dni_ruc'],
                'telefono' => $data['telefono'], 'email' => $data['email']
            ]);
            echo json_encode(['id' => $conn->lastInsertId(), 'message' => 'Cliente creado.']);
        } catch (PDOException $e) {
            http_response_code(500);
            if ($e->getCode() == 23000) echo json_encode(['error' => 'DNI o Teléfono ya registrado.']);
            else echo json_encode(['error' => 'Error: ' . $e->getMessage()]);
        }
        break;

    case 'PUT':
         try {
            $sql = "UPDATE clientes SET nombre = :nombre, dni_ruc = :dni_ruc, telefono = :telefono, email = :email WHERE id = :id";
            $stmt = $conn->prepare($sql);
            $stmt->execute([
                'id' => $data['id'], 'nombre' => $data['nombre'],
                'dni_ruc' => $data['dni_ruc'], 'telefono' => $data['telefono'], 'email' => $data['email']
            ]);
            echo json_encode(['message' => 'Cliente actualizado.']);
        } catch (PDOException $e) {
            http_response_code(500);
             if ($e->getCode() == 23000) echo json_encode(['error' => 'DNI o Teléfono ya registrado.']);
             else echo json_encode(['error' => 'Error: ' . $e->getMessage()]);
        }
        break;

    case 'DELETE':
        try {
            $id = $_GET['id'] ?? null;
            if (!$id) { http_response_code(400); echo json_encode(['error' => 'ID faltante']); exit; }
            $sql = "DELETE FROM clientes WHERE id = :id";
            $stmt = $conn->prepare($sql);
            $stmt->execute(['id' => $id]);
            echo json_encode(['message' => 'Cliente eliminado.']);
        } catch (PDOException $e) {
            http_response_code(500);
             if ($e->getCode() == 23000) echo json_encode(['error' => 'No se puede eliminar, tiene registros asociados.']);
             else echo json_encode(['error' => 'Error: ' . $e->getMessage()]);
        }
        break;

    default:
        http_response_code(405);
        echo json_encode(['error' => 'Método no permitido']);
        break;
}
?>