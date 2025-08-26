<?php
// C:\xampp\htdocs\cusquena\backend\api\controllers\vista_gastos_empresa\actualizar.php

require_once __DIR__ . '/../../../includes/db.php';
require_once __DIR__ . '/../../../includes/auth.php';

header('Content-Type: application/json');

verificarPermiso(['Administrador', 'Secretaria']);

$data = json_decode(file_get_contents('php://input'), true);

// ⭐ CORRECCIÓN: 'tipo_gasto' eliminado de la validación
if (!isset($data['id'], $data['concepto'], $data['monto'], $data['fecha'], $data['observaciones'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Datos incompletos para actualizar el gasto. Asegúrate de enviar id, concepto, monto, fecha, y observaciones.']);
    exit();
}

$id = (int)$data['id'];
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
    // ⭐ CORRECCIÓN: 'tipo_gasto' eliminado de la consulta UPDATE
    $stmt = $conn->prepare("UPDATE gastos_empresa SET concepto = :concepto, monto = :monto, fecha = :fecha, observaciones = :observaciones WHERE id = :id");
    if ($stmt === false) {
        error_log("Error preparing statement for update gastos_empresa: " . implode(" ", $conn->errorInfo()));
        throw new Exception("Error al preparar la consulta.");
    }

    // ⭐ CORRECCIÓN: 'tipo_gasto' eliminado de los parámetros
    $stmt->bindParam(':concepto', $concepto, PDO::PARAM_STR);
    $stmt->bindParam(':monto', $monto, PDO::PARAM_STR);
    $stmt->bindParam(':fecha', $fecha, PDO::PARAM_STR);
    $stmt->bindParam(':observaciones', $observaciones, PDO::PARAM_STR);
    $stmt->bindParam(':id', $id, PDO::PARAM_INT);

    if ($stmt->execute()) {
        if ($stmt->rowCount() > 0) {
            echo json_encode(['success' => true, 'message' => 'Gasto de empresa actualizado exitosamente!']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Gasto de empresa no encontrado o no se realizaron cambios.']);
        }
    } else {
        error_log("Error executing statement for update gastos_empresa: " . implode(" ", $stmt->errorInfo()));
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