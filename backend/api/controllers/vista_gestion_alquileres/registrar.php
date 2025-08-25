<?php
// C:\xampp\htdocs\cusquena\backend\api\controllers\vista_alquileres\registrar.php

require_once __DIR__ . '/../../../includes/db.php';
require_once __DIR__ . '/../../../includes/auth.php';

header('Content-Type: application/json');

verificarPermiso(['Administrador']); // Solo administradores pueden registrar alquileres

$data = json_decode(file_get_contents('php://input'), true);

if (!isset($data['nombre'], $data['tipo'], $data['fechaInicio'], $data['periodicidad'], $data['pago'], $data['estado'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Datos incompletos para registrar el alquiler.']);
    exit();
}

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
    $stmt = $conn->prepare("INSERT INTO alquileres (nombre, tipo, fecha_inicio, periodicidad, pago, estado) VALUES (:nombre, :tipo, :fecha_inicio, :periodicidad, :pago, :estado)");
    if ($stmt === false) {
        throw new Exception("Error al preparar la consulta: " . implode(" ", $conn->errorInfo()));
    }

    $stmt->bindParam(':nombre', $nombre);
    $stmt->bindParam(':tipo', $tipo);
    $stmt->bindParam(':fecha_inicio', $fecha_inicio);
    $stmt->bindParam(':periodicidad', $periodicidad);
    $stmt->bindParam(':pago', $pago, PDO::PARAM_STR); // PDO::PARAM_STR para DECIMAL
    $stmt->bindParam(':estado', $estado);

    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Alquiler registrado exitosamente!', 'id' => $conn->lastInsertId()]);
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
