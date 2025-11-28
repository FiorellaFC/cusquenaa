<?php
header('Content-Type: application/json');
require_once "../../../includes/db.php";  

$method = $_SERVER['REQUEST_METHOD'];
$data = json_decode(file_get_contents('php://input'), true);

switch ($method) {

    case 'GET':
        try {
            $sql = "SELECT * FROM contacto ORDER BY fecha_envio DESC";
            $stmt = $conn->query($sql);
            echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode(['error' => $e->getMessage()]);
        }
        break;


    case 'POST':
        try {
            $sql = "INSERT INTO contacto (nombre_completo, correo, mensaje) 
                    VALUES (:nombre, :correo, :mensaje)";
            $stmt = $conn->prepare($sql);

            $stmt->execute([
                ':nombre' => $data['nombre_completo'],
                ':correo' => $data['correo'],
                ':mensaje' => $data['mensaje']
            ]);

            echo json_encode(['message' => 'Mensaje registrado correctamente']);
        } catch (PDOException $e) {
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
