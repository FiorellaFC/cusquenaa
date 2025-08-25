<?php
// C:\xampp\htdocs\cusquena\backend\api\controllers\vista_gastos_empresa\registrar.php

require_once __DIR__ . '/../../../includes/db.php';
require_once __DIR__ . '/../../../includes/auth.php';

header('Content-Type: application/json');

// Ensure the user has Administrator permissions
verificarPermiso(['Administrador', 'Secretaria']); // Corrected based on your HTML, both roles can access this page

// Decode the JSON input from the request body
$data = json_decode(file_get_contents('php://input'), true);

// --- Input Validation ---
// Check if all required data fields are present
// ⭐ CORRECTION: Changed 'tipoGasto' to 'tipo_gasto' to match JS payload
if (!isset($data['descripcion'], $data['tipo_gasto'], $data['monto'], $data['fecha'], $data['detalle'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Datos incompletos para registrar el gasto. Faltan: descripcion, tipo_gasto, monto, fecha, o detalle.']);
    exit();
}

// Sanitize and assign input variables
$descripcion = trim($data['descripcion']); // Trim whitespace
// ⭐ CORRECTION: Assign from 'tipo_gasto'
$tipo_gasto = trim($data['tipo_gasto']);    // Trim whitespace
$monto = (float)$data['monto'];             // Cast to float for numeric operations
$fecha = trim($data['fecha']);              // Trim whitespace
$detalle = trim($data['detalle']);          // Trim whitespace

// Validate that the expense type is one of the allowed values
// IMPORTANT: These values MUST match the ENUM values defined in your database's `tipo_gasto` column.
$allowed_types = ['operativo', 'administrativo', 'mantenimiento', 'otro'];
if (!in_array($tipo_gasto, $allowed_types)) {
    http_response_code(400); // Bad Request
    echo json_encode(['error' => 'Tipo de gasto inválido. El tipo debe ser uno de: ' . implode(', ', $allowed_types) . '.']);
    exit();
}

// Validate numeric value for monto
if (!is_numeric($monto)) { 
    http_response_code(400);
    echo json_encode(['error' => 'El monto debe ser un valor numérico.']);
    exit();
}

// Basic date format validation (you might want more robust validation based on your exact date format)
if (!preg_match("/^\d{4}-\d{2}-\d{2}$/", $fecha)) {
    http_response_code(400);
    echo json_encode(['error' => 'El formato de la fecha es inválido. Se espera YYYY-MM-DD.']);
    exit();
}


try {
    // Prepare the SQL INSERT statement
    $stmt = $conn->prepare("INSERT INTO gastos_empresa (descripcion, tipo_gasto, monto, fecha, detalle) VALUES (:descripcion, :tipo_gasto, :monto, :fecha, :detalle)");

    // Check if the statement preparation failed
    if ($stmt === false) {
        // Log the detailed error for debugging purposes (useful during development)
        error_log("Error preparing statement for gastos_empresa: " . implode(" ", $conn->errorInfo()));
        throw new Exception("Error al preparar la consulta."); // Generic message for client
    }

    // Bind parameters to the prepared statement
    $stmt->bindParam(':descripcion', $descripcion, PDO::PARAM_STR);
    $stmt->bindParam(':tipo_gasto', $tipo_gasto, PDO::PARAM_STR);
    $stmt->bindParam(':monto', $monto, PDO::PARAM_STR); // Use PDO::PARAM_STR for DECIMAL/NUMERIC types to prevent precision issues
    $stmt->bindParam(':fecha', $fecha, PDO::PARAM_STR);
    $stmt->bindParam(':detalle', $detalle, PDO::PARAM_STR);

    // Execute the prepared statement
    if ($stmt->execute()) {
        // Respond with success message and the ID of the newly inserted record
        echo json_encode(['success' => true, 'message' => 'Gasto de empresa registrado exitosamente!', 'id' => $conn->lastInsertId()]);
    } else {
        // Log the detailed error for debugging purposes (useful during development)
        error_log("Error executing statement for gastos_empresa: " . implode(" ", $stmt->errorInfo()));
        throw new Exception("Error al ejecutar la consulta."); // Generic message for client
    }

} catch (Exception $e) {
    // Set HTTP status code to 500 (Internal Server Error)
    http_response_code(500);
    // Respond with the error message
    echo json_encode(['error' => $e->getMessage()]);
} finally {
    // Close the statement and connection in the finally block to ensure they are always closed
    if (isset($stmt)) {
        $stmt = null;
    }
    if (isset($conn)) {
        $conn = null;
    }
}
?>