<?php
// C:\xampp\htdocs\cusquena\backend\api\controllers\vista_gastos_empresa\registrar.php

require_once __DIR__ . '/../../../includes/db.php';
require_once __DIR__ . '/../../../includes/auth.php';

header('Content-Type: application/json');

verificarPermiso(['Administrador', 'Secretaria']);

$data = json_decode(file_get_contents('php://input'), true);

// ⭐ CORRECCIÓN: 'tipo_gasto' eliminado de la validación
if (!isset($data['concepto'], $data['monto'], $data['fecha'], $data['observaciones'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Datos incompletos para registrar el gasto. Faltan: concepto, monto, fecha, o observaciones.']);
    exit();
}

$concepto = trim($data['concepto']);
$monto = (float)$data['monto'];
$fecha = trim($data['fecha']);
$observaciones = trim($data['observaciones']);

if (!is_numeric($monto)) {
    http_response_code(400);
    echo json_encode(['error' => 'El monto debe ser un valor numérico.']);
    exit();
}

if (!preg_match("/^\d{4}-\d{2}-\d{2}$/", $fecha)) {
    http_response_code(400);
    echo json_encode(['error' => 'El formato de la fecha es inválido. Se espera YYYY-MM-DD.']);
    exit();
}

try {
    // ⭐ CORRECCIÓN: 'tipo_gasto' eliminado de la consulta INSERT
    $stmt = $conn->prepare("INSERT INTO gastos_empresa (concepto, monto, fecha, observaciones) VALUES (:concepto, :monto, :fecha, :observaciones)");

    if ($stmt === false) {
        error_log("Error preparing statement for gastos_empresa: " . implode(" ", $conn->errorInfo()));
        throw new Exception("Error al preparar la consulta.");
    }

    // ⭐ CORRECCIÓN: 'tipo_gasto' eliminado de los parámetros
    $stmt->bindParam(':concepto', $concepto, PDO::PARAM_STR);
    $stmt->bindParam(':monto', $monto, PDO::PARAM_STR);
    $stmt->bindParam(':fecha', $fecha, PDO::PARAM_STR);
    $stmt->bindParam(':observaciones', $observaciones, PDO::PARAM_STR);

    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Gasto de empresa registrado exitosamente!', 'id' => $conn->lastInsertId()]);
    } else {
        error_log("Error executing statement for gastos_empresa: " . implode(" ", $stmt->errorInfo()));
        throw new Exception("Error al ejecutar la consulta.");
    }

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
} finally {
    if (isset($stmt)) {
        $stmt = null;
    }
    if (isset($conn)) {
        $conn = null;
    }
}
?>