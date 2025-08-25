<?php
// C:\xampp\htdocs\cusquena\backend\api\controllers\vista_alquileres\actualizar.php

require_once __DIR__ . '/../../../includes/db.php';
require_once __DIR__ . '/../../../includes/auth.php';

header('Content-Type: application/json');

verificarPermiso(['Administrador', 'Secretaria']);

$data = json_decode(file_get_contents('php://input'), true);

if (!isset($data['id'], $data['nombre'], $data['tipo'], $data['fechaInicio'], $data['periodicidad'], $data['pago'], $data['estado'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Datos incompletos para actualizar el alquiler.']);
    exit();
}

$id = (int)$data['id'];
$nombre = $data['nombre'];
$tipo = $data['tipo'];
$fecha_inicio = $data['fechaInicio'];
$periodicidad = $data['periodicidad'];
$pago = (float)$data['pago'];
$estado = $data['estado'];

// Validar enums
$allowed_tipos = ['Local', 'Cochera'];
$allowed_periodicidades = ['Mensual', 'Semanal', 'Diario'];
$allowed_estados = ['Activo', 'Inactivo'];

if (!in_array($tipo, $allowed_tipos)) {
    http_response_code(400);
    echo json_encode(['error' => 'Tipo de alquiler inválido.']);
    exit();
}
if (!in_array($periodicidad, $allowed_periodicidades)) {
    http_response_code(400);
    echo json_encode(['error' => 'Periodicidad inválida.']);
    exit();
}
if (!in_array($estado, $allowed_estados)) {
    http_response_code(400);
    echo json_encode(['error' => 'Estado de alquiler inválido.']);
    exit();
}

try {
    $stmt = $conn->prepare("UPDATE alquileres SET nombre = :nombre, tipo = :tipo, fecha_inicio = :fecha_inicio, periodicidad = :periodicidad, pago = :pago, estado = :estado WHERE id = :id");
    if ($stmt === false) {
        throw new Exception("Error al preparar la consulta: " . implode(" ", $conn->errorInfo()));
    }

    $stmt->bindParam(':nombre', $nombre);
    $stmt->bindParam(':tipo', $tipo);
    $stmt->bindParam(':fecha_inicio', $fecha_inicio);
    $stmt->bindParam(':periodicidad', $periodicidad);
    $stmt->bindParam(':pago', $pago, PDO::PARAM_STR); // PDO::PARAM_STR para DECIMAL
    $stmt->bindParam(':estado', $estado);
    $stmt->bindParam(':id', $id, PDO::PARAM_INT);

    if ($stmt->execute()) {
        if ($stmt->rowCount() > 0) {
            echo json_encode(['success' => true, 'message' => 'Alquiler actualizado exitosamente!']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Alquiler no encontrado o no se realizaron cambios.']);
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
