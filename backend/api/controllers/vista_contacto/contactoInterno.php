<?php
header('Content-Type: application/json');
require_once "../../../includes/db.php";  

// Modo debug temporal
$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$method = $_SERVER['REQUEST_METHOD'];
$data = json_decode(file_get_contents('php://input'), true);

switch ($method) {

    /* ===========================
       📌 1. LISTAR CONTACTOS (GET)
       =========================== */
    case 'GET':
        try {
            $params = [];
            $sql = "SELECT * FROM contacto WHERE 1=1";

            // Filtro por nombre
            if (!empty($_GET['nombre'])) {
                $sql .= " AND nombre_completo LIKE :nombre";
                $params[':nombre'] = '%' . $_GET['nombre'] . '%';
            }

            // Filtro por correo
            if (!empty($_GET['correo'])) {
                $sql .= " AND correo LIKE :correo";
                $params[':correo'] = '%' . $_GET['correo'] . '%';
            }

            // 🔥 Filtro por fecha (faltaba)
            if (!empty($_GET['fecha'])) {
                $sql .= " AND DATE(fecha_envio) = :fecha";
                $params[':fecha'] = $_GET['fecha'];
            }

            $sql .= " ORDER BY fecha_envio ASC";

            $stmt = $conn->prepare($sql);
            $stmt->execute($params);

            echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
        } 
        catch (PDOException $e) {
            http_response_code(500);
            echo json_encode(['error' => $e->getMessage()]);
        }
        break;

    /* ===========================
       📌 2. REGISTRAR CONTACTO (POST)
       =========================== */
    case 'POST':
        try {
            if (!$data) {
                http_response_code(400);
                echo json_encode(['error' => 'Datos inválidos']);
                exit;
            }

            $sql = "INSERT INTO contacto (nombre_completo, correo, mensaje, fecha_envio)
                    VALUES (:nombre, :correo, :mensaje, NOW())";

            $stmt = $conn->prepare($sql);
            $stmt->execute([
                ':nombre'  => $data['nombre_completo'],
                ':correo'  => $data['correo'],
                ':mensaje' => $data['mensaje']
            ]);

            echo json_encode(['message' => 'Contacto guardado correctamente']);
        } 
        catch (PDOException $e) {
            http_response_code(500);
            echo json_encode(['error' => $e->getMessage()]);
        }
        break;

    /* ===========================
       📌 3. EDITAR CONTACTO (PUT)
       =========================== */
    case 'PUT':
        try {
            if (empty($data['id'])) {
                http_response_code(400);
                echo json_encode(['error' => 'ID requerido']);
                exit;
            }

            $sql = "UPDATE contacto
                    SET nombre_completo = :nombre,
                        correo = :correo,
                        mensaje = :mensaje
                    WHERE id = :id";

            $stmt = $conn->prepare($sql);
            $stmt->execute([
                ':id'      => $data['id'],
                ':nombre'  => $data['nombre_completo'],
                ':correo'  => $data['correo'],
                ':mensaje' => $data['mensaje']
            ]);

            echo json_encode(['message' => 'Contacto actualizado correctamente']);
        } 
        catch (PDOException $e) {
            http_response_code(500);
            echo json_encode(['error' => $e->getMessage()]);
        }
        break;

    /* ===========================
       📌 4. ELIMINAR CONTACTO (DELETE)
       =========================== */
    case 'DELETE':
        try {
            $id = $_GET['id'] ?? null;

            if (!$id) {
                http_response_code(400);
                echo json_encode(['error' => 'ID requerido']);
                exit;
            }

            $sql = "DELETE FROM contacto WHERE id = :id";
            $stmt = $conn->prepare($sql);
            $stmt->execute([':id' => $id]);

            echo json_encode(['message' => 'Contacto eliminado']);
        } 
        catch (PDOException $e) {
            http_response_code(500);
            echo json_encode(['error' => $e->getMessage()]);
        }
        break;

    default:
        http_response_code(405);
        echo json_encode(['error' => 'Método no permitido']);
        break;
}
?>
