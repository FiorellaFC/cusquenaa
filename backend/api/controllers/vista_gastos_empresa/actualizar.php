<?php
// C:\xampp\htdocs\cusquena\backend\api\controllers\vista_gastos_empresa\actualizar.php

require_once __DIR__ . '/../../../includes/db.php';
require_once __DIR__ . '/../../../includes/auth.php';

header('Content-Type: application/json');

verificarPermiso(['Administrador']); // Solo administradores pueden actualizar gastos

$data = json_decode(file_get_contents('php://input'), true);

// ⭐ CORRECCIÓN 1: Cambiar 'tipoGasto' a 'tipo_gasto' en el isset check
if (!isset($data['id'], $data['descripcion'], $data['tipo_gasto'], $data['monto'], $data['fecha'], $data['detalle'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Datos incompletos para actualizar el gasto. Asegúrate de enviar id, descripcion, tipo_gasto, monto, fecha, y detalle.']); // Más específico
    exit();
}

$id = (int)$data['id'];
$descripcion = trim($data['descripcion']); // Añadir trim para limpiar espacios
// ⭐ CORRECCIÓN 2: Asignar a $tipo_gasto desde $data['tipo_gasto']
$tipo_gasto = trim($data['tipo_gasto']); 
$monto = (float)$data['monto'];
$fecha = trim($data['fecha']);
$detalle = trim($data['detalle']);

// Validaciones adicionales (copiadas de registrar.php para consistencia)
// Validar que el tipo de gasto sea uno de los permitidos por el ENUM
$allowed_types = ['operativo', 'administrativo', 'mantenimiento', 'otro'];
if (!in_array($tipo_gasto, $allowed_types)) {
    http_response_code(400);
    echo json_encode(['error' => 'Tipo de gasto inválido. El tipo debe ser uno de: ' . implode(', ', $allowed_types) . '.']);
    exit();
}

// Validar numeric value for monto
if (!is_numeric($monto)) {
    http_response_code(400);
    echo json_encode(['error' => 'El monto debe ser un valor numérico.']);
    exit();
}

// Basic date format validation
if (!preg_match("/^\d{4}-\d{2}-\d{2}$/", $fecha)) {
    http_response_code(400);
    echo json_encode(['error' => 'El formato de la fecha es inválido. Se espera YYYY-MM-DD.']);
    exit();
}


try {
    $stmt = $conn->prepare("UPDATE gastos_empresa SET descripcion = :descripcion, tipo_gasto = :tipo_gasto, monto = :monto, fecha = :fecha, detalle = :detalle WHERE id = :id");
    if ($stmt === false) {
        error_log("Error preparing statement for update gastos_empresa: " . implode(" ", $conn->errorInfo()));
        throw new Exception("Error al preparar la consulta.");
    }

    $stmt->bindParam(':descripcion', $descripcion, PDO::PARAM_STR);
    $stmt->bindParam(':tipo_gasto', $tipo_gasto, PDO::PARAM_STR);
    $stmt->bindParam(':monto', $monto, PDO::PARAM_STR); // PDO::PARAM_STR para DECIMAL para prevenir problemas de precisión
    $stmt->bindParam(':fecha', $fecha, PDO::PARAM_STR);
    $stmt->bindParam(':detalle', $detalle, PDO::PARAM_STR);
    $stmt->bindParam(':id', $id, PDO::PARAM_INT);

    if ($stmt->execute()) {
        if ($stmt->rowCount() > 0) {
            echo json_encode(['success' => true, 'message' => 'Gasto de empresa actualizado exitosamente!']);
        } else {
            // Esto puede ocurrir si el ID no existe o si los datos enviados son idénticos a los existentes
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
    // Asegurar que las conexiones se cierren
    if (isset($stmt)) {
        $stmt = null;
    }
    if (isset($conn)) {
        $conn = null;
    }
}
?>