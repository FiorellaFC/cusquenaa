<?php
// C:\xampp\htdocs\cusquena\backend\api\controllers\vista_gastos_empresa\eliminar.php

require_once __DIR__ . '/../../../includes/db.php';
require_once __DIR__ . '/../../../includes/auth.php';

header('Content-Type: application/json');

verificarPermiso(['Administrador']); // Solo administradores pueden eliminar gastos

$data = json_decode(file_get_contents('php://input'), true);

if (!isset($data['id'])) {
    http_response_code(400);
    echo json_encode(['error' => 'ID de gasto no proporcionado.']);
    exit();
}

$id = (int)$data['id'];

try {
    $stmt = $conn->prepare("DELETE FROM gastos_empresa WHERE id = :id");
    if ($stmt === false) {
        throw new Exception("Error al preparar la consulta: " . implode(" ", $conn->errorInfo()));
    }
    $stmt->bindParam(':id', $id, PDO::PARAM_INT);

    if ($stmt->execute()) {
        if ($stmt->rowCount() > 0) {
            echo json_encode(['success' => true, 'message' => 'Gasto de empresa eliminado exitosamente!']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Gasto de empresa no encontrado.']);
        }
    } else {
        throw new Exception("Error al ejecutar la consulta: " . implode(" ", $stmt->errorInfo()));
    }

    $stmt = null;
    $conn = null;

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
?>
