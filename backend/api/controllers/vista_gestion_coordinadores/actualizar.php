<?php
require_once __DIR__ . '/../../../includes/db.php';
require_once __DIR__ . '/../../../includes/auth.php';

header('Content-Type: application/json');

verificarPermiso(['Administrador', 'Secretaria']);

$data = json_decode(file_get_contents('php://input'), true);

// Validate that all MANDATORY fields are present.
// We've removed 'contacto' from this initial strict 'isset' check.
if (!isset($data['id'], $data['nombre'], $data['apellidos'], $data['paradero'], $data['monto_diario'], $data['fecha'], $data['estado'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Datos incompletos para actualizar: faltan campos obligatorios.']);
    exit();
}

$id = $data['id'];
$nombre = $data['nombre'];
$apellidos = $data['apellidos'];
$paradero = $data['paradero'];
$monto_diario = (float)$data['monto_diario'];
$fecha = $data['fecha'];
$estado = $data['estado'];

// Handle 'contacto' separately: if it's not set or empty, treat it as NULL for the database.
// This assumes your database column for 'contacto' is nullable.
$contacto = isset($data['contacto']) && !empty($data['contacto']) ? $data['contacto'] : null; 

try {
    $stmt = $conn->prepare("UPDATE coordinadores 
                            SET nombre = :nombre, apellidos = :apellidos, paradero = :paradero, monto_diario = :monto_diario, fecha = :fecha, estado = :estado, contacto = :contacto 
                            WHERE id = :id");

    $stmt->bindParam(':id', $id, PDO::PARAM_INT);
    $stmt->bindParam(':nombre', $nombre);
    $stmt->bindParam(':apellidos', $apellidos);
    $stmt->bindParam(':paradero', $paradero);
    $stmt->bindParam(':monto_diario', $monto_diario, PDO::PARAM_STR); // Use PARAM_STR for decimals
    $stmt->bindParam(':fecha', $fecha);
    $stmt->bindParam(':estado', $estado);
    $stmt->bindParam(':contacto', $contacto); // PDO handles NULL correctly for nullable columns

    $stmt->execute();

    echo json_encode(['success' => true, 'message' => 'Coordinador actualizado correctamente.']);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Error al actualizar: ' . $e->getMessage()]);
}
?>