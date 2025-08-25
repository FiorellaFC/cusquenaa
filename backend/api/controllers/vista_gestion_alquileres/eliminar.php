<?php
require_once __DIR__ . '/../../../includes/db.php';
require_once __DIR__ . '/../../../includes/auth.php';

header('Content-Type: application/json');

verificarPermiso(['Administrador', 'Secretaria']);

$data = json_decode(file_get_contents('php://input'), true);

if (!isset($data['id'])) {
    http_response_code(400);
    echo json_encode(['error' => 'ID de alquiler no proporcionado.']);
    exit();
}

$id = (int)$data['id'];

try {
    $stmt = $conn->prepare("DELETE FROM alquileres WHERE id = :id");

    if ($stmt->execute([':id' => $id])) {
        if ($stmt->rowCount() > 0) {
            echo json_encode(['success' => true, 'message' => 'Alquiler eliminado exitosamente!']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Alquiler no encontrado.']);
        }
    } else {
        echo json_encode(['error' => 'Error al ejecutar la consulta.']);
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Error del servidor: ' . $e->getMessage()]);
}
?>
