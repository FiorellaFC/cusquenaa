<?php
header('Content-Type: application/json');
require_once "../../includes/db.php"; // Ruta de conexión que funciona

$method = $_SERVER['REQUEST_METHOD'];
$data = json_decode(file_get_contents('php://input'), true);

switch ($method) {
    case 'GET':
        try {
            $params = [];
            // Omitimos 'direccion' como solicitaste
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
            echo json_encode(['error' => 'Error en la base de datos: ' . $e->getMessage()]);
        }
        break;

    case 'POST':
        // Crear nuevo cliente
         try {
            $sql = "INSERT INTO clientes (nombre, dni_ruc, telefono, email) 
                    VALUES (:nombre, :dni_ruc, :telefono, :email)";
            $stmt = $conn->prepare($sql);
            $stmt->execute([
                'nombre' => $data['nombre'],
                'dni_ruc' => $data['dni_ruc'],
                'telefono' => $data['telefono'],
                'email' => $data['email']
            ]);
            $lastId = $conn->lastInsertId();
            http_response_code(201);
            echo json_encode(['id' => $lastId, 'message' => 'Cliente creado exitosamente.']);
        } catch (PDOException $e) {
            http_response_code(500);
            // Manejo de error de DNI duplicado
            if ($e->getCode() == 23000) {
                 echo json_encode(['error' => 'Error: El DNI/RUC o Teléfono ya está registrado.']);
            } else {
                 echo json_encode(['error' => 'Error al crear el cliente: ' . $e->getMessage()]);
            }
        }
        break;

    case 'PUT':
        // Actualizar cliente
         try {
            $sql = "UPDATE clientes SET 
                        nombre = :nombre, 
                        dni_ruc = :dni_ruc, 
                        telefono = :telefono, 
                        email = :email
                    WHERE id = :id";
            $stmt = $conn->prepare($sql);
            $stmt->execute([
                'id' => $data['id'],
                'nombre' => $data['nombre'],
                'dni_ruc' => $data['dni_ruc'],
                'telefono' => $data['telefono'],
                'email' => $data['email']
            ]);
            echo json_encode(['message' => 'Cliente actualizado exitosamente.']);
        } catch (PDOException $e) {
            http_response_code(500);
             if ($e->getCode() == 23000) {
                 echo json_encode(['error' => 'Error: El DNI/RUC o Teléfono ya está registrado.']);
            } else {
                 echo json_encode(['error' => 'Error al actualizar el cliente: ' . $e->getMessage()]);
            }
        }
        break;

    case 'DELETE':
        // Eliminar cliente (Hard Delete, como en tu tabla `clientes` no hay 'activo')
        try {
            $id = $_GET['id'] ?? null;
            if (!$id) {
                http_response_code(400);
                echo json_encode(['error' => 'ID de cliente no proporcionado.']);
                exit;
            }
            // NOTA: Si tienes FK (como en `citas`), puede que necesites `ON DELETE SET NULL`
            // o manejar la eliminación de otra forma.
            $sql = "DELETE FROM clientes WHERE id = :id";
            $stmt = $conn->prepare($sql);
            $stmt->execute(['id' => $id]);
            echo json_encode(['message' => 'Cliente eliminado permanentemente.']);
        } catch (PDOException $e) {
            http_response_code(500);
            // Error si el cliente tiene citas asociadas
             if ($e->getCode() == 23000) { 
                 echo json_encode(['error' => 'No se puede eliminar el cliente porque tiene citas u otros registros asociados.']);
             } else {
                 echo json_encode(['error' => 'Error al eliminar el cliente: ' . $e->getMessage()]);
             }
        }
        break;

    default:
        http_response_code(405);
        echo json_encode(['error' => 'Método no permitido']);
        break;
}
?>