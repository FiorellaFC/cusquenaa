<?php
header('Content-Type: application/json');
require_once "../../../includes/db.php"; 

$method = $_SERVER['REQUEST_METHOD'];
$data = json_decode(file_get_contents('php://input'), true);

// Configuración de Paginación
$limit = 15; 

switch ($method) {
    case 'GET':
        try {
            $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
            $start = ($page - 1) * $limit;
            
            $params = [];
            $whereSQL = "WHERE 1=1";

            // Filtros de búsqueda
            if (!empty($_GET['nombre'])) {
                $whereSQL .= " AND (nombre LIKE :nombre OR apellido LIKE :nombre)";
                $params[':nombre'] = '%' . $_GET['nombre'] . '%';
            }
            
            if (!empty($_GET['dni'])) {
                // Búsqueda EXACTA para DNI
                $whereSQL .= " AND dni_ruc = :dni";
                $params[':dni'] = $_GET['dni'];
            }

            // 1. Obtener el TOTAL de registros
            $sqlCount = "SELECT COUNT(*) as total FROM clientes $whereSQL";
            $stmtCount = $conn->prepare($sqlCount);
            $stmtCount->execute($params);
            $totalRecords = $stmtCount->fetch(PDO::FETCH_ASSOC)['total'];
            $totalPages = ceil($totalRecords / $limit);

            // 2. Obtener los registros PAGINADOS
            $sql = "SELECT id, nombre, apellido, telefono, dni_ruc, email, tiene_cuenta 
                    FROM clientes 
                    $whereSQL 
                    ORDER BY nombre ASC 
                    LIMIT $start, $limit";
            
            $stmt = $conn->prepare($sql);
            $stmt->execute($params);
            $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

            echo json_encode([
                'data' => $data,
                'pagination' => [
                    'current_page' => $page,
                    'total_pages' => $totalPages,
                    'total_records' => $totalRecords
                ]
            ]);

        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode(['error' => 'Error BD: ' . $e->getMessage()]);
        }
        break;

    case 'POST':
         try {
            $sql = "INSERT INTO clientes (nombre, apellido, dni_ruc, telefono, email, tiene_cuenta) VALUES (:nombre, :apellido, :dni_ruc, :telefono, :email, 0)";
            $stmt = $conn->prepare($sql);
            $stmt->execute([
                'nombre' => $data['nombre'], 
                'apellido' => $data['apellido'],
                'dni_ruc' => $data['dni_ruc'],
                'telefono' => $data['telefono'], 
                'email' => $data['email']
            ]);
            echo json_encode(['id' => $conn->lastInsertId(), 'message' => 'Cliente creado.']);
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode(['error' => $e->getMessage()]);
        }
        break;

    case 'PUT':
         try {
            $sql = "UPDATE clientes SET nombre = :nombre, apellido = :apellido, dni_ruc = :dni_ruc, telefono = :telefono, email = :email WHERE id = :id";
            $stmt = $conn->prepare($sql);
            $stmt->execute([
                'id' => $data['id'], 
                'nombre' => $data['nombre'],
                'apellido' => $data['apellido'],
                'dni_ruc' => $data['dni_ruc'], 
                'telefono' => $data['telefono'], 
                'email' => $data['email']
            ]);
            echo json_encode(['message' => 'Cliente actualizado.']);
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode(['error' => $e->getMessage()]);
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
            echo json_encode(['error' => 'No se puede eliminar, tiene registros asociados.']);
        }
        break;

    default:
        http_response_code(405);
        echo json_encode(['error' => 'Método no permitido']);
        break;
}
?>