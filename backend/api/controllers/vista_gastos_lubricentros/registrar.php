<?php
// C:\xampp\htdocs\cusquena\backend\api\controllers\vista_gastos_lubricentros\registrar.php

require_once __DIR__ . '/../../../includes/db.php';
require_once __DIR__ . '/../../../includes/auth.php';

header('Content-Type: application/json');

// Verificar permisos
verificarPermiso(['Administrador']); // Solo administradores pueden registrar gastos

$data = json_decode(file_get_contents('php://input'), true);

// Validación básica de datos
if (!isset($data['descripcion'], $data['tipoGasto'], $data['monto'], $data['fecha'], $data['detalle'])) {
    http_response_code(400); // Bad Request
    echo json_encode(['error' => 'Datos incompletos para registrar el gasto.']);
    exit();
}

$descripcion = $data['descripcion'];
$tipo_gasto = $data['tipoGasto'];
$monto = (float)$data['monto'];
$fecha = $data['fecha'];
$detalle = $data['detalle'];

// Validar que el tipo de gasto sea uno de los permitidos por el ENUM
$allowed_types = ['operativo', 'administrativo', 'mantenimiento', 'otro'];
if (!in_array($tipo_gasto, $allowed_types)) {
    http_response_code(400);
    echo json_encode(['error' => 'Tipo de gasto inválido.']);
    exit();
}

try {
    $stmt = $conn->prepare("INSERT INTO gastos_lubricentros (descripcion, tipo_gasto, monto, fecha, detalle) VALUES (:descripcion, :tipo_gasto, :monto, :fecha, :detalle)");
    if ($stmt === false) {
        throw new Exception("Error al preparar la consulta.");
    }
    $stmt->bindParam(':descripcion', $descripcion);
    $stmt->bindParam(':tipo_gasto', $tipo_gasto);
    $stmt->bindParam(':monto', $monto);
    $stmt->bindParam(':fecha', $fecha);
    $stmt->bindParam(':detalle', $detalle);

    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Gasto registrado exitosamente!', 'id' => $conn->lastInsertId()]);
    } else {
        throw new Exception("Error al ejecutar la consulta: " . $stmt->errorInfo()[2]); // Obtener error más detallado
    }

    $stmt = null; // Cerrar statement
    $conn = null; // Cerrar conexión

} catch (Exception $e) {
    http_response_code(500); // Internal Server Error
    echo json_encode(['error' => $e->getMessage()]);
}
?>
