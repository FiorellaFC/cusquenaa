<?php
// C:\xampp\htdocs\cusquena\backend\api\controllers\vista_gastos_lubricentros\eliminar.php

require_once __DIR__ . '/../../../includes/db.php';
require_once __DIR__ . '/../../../includes/auth.php';

header('Content-Type: application/json');

// Verificar permisos
verificarPermiso(['Administrador']); // Solo administradores pueden eliminar gastos

$data = json_decode(file_get_contents('php://input'), true);

// Validación básica de datos
if (!isset($data['id'])) {
    http_response_code(400); // Bad Request
    echo json_encode(['error' => 'ID de gasto no proporcionado.']);
    exit();
}

$id = (int)$data['id'];

try {
    $stmt = $conn->prepare("DELETE FROM gastos_lubricentros WHERE id = :id");
    if ($stmt === false) {
        throw new Exception("Error al preparar la consulta.");
    }
    $stmt->bindParam(':id', $id, PDO::PARAM_INT);

    if ($stmt->execute()) {
        if ($stmt->rowCount() > 0) { // rowCount() para PDO
            echo json_encode(['success' => true, 'message' => 'Gasto eliminado exitosamente!']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Gasto no encontrado.']);
        }
    } else {
        throw new Exception("Error al ejecutar la consulta: " . $stmt->errorInfo()[2]);
    }

    $stmt = null;
    $conn = null;

} catch (Exception $e) {
    http_response_code(500); // Internal Server Error
    echo json_encode(['error' => $e->getMessage()]);
}
?>
