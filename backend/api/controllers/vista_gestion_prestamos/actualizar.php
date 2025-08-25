<?php
require_once __DIR__ . '/../../../includes/db.php';
require_once __DIR__ . '/../../../includes/auth.php';

header('Content-Type: application/json');

verificarPermiso(['Administrador', 'Secretaria']);

$data = json_decode(file_get_contents('php://input'), true);


if (
    !isset($data['id'], $data['nombre'], $data['tipo_persona'], $data['monto_deuda'], 
             $data['saldo_pendiente'], $data['estado'], $data['fecha_inicio_deuda'])
) {
    http_response_code(400);
    echo json_encode(['error' => 'Datos incompletos para actualizar el préstamo.']);
    exit();
}

// Asignar valores
$id = (int)$data['id'];
$nombre = $data['nombre'];
$tipo_persona = $data['tipo_persona'];
$monto_deuda = (float)$data['monto_deuda'];
$saldo_pendiente = (float)$data['saldo_pendiente'];
$estado = $data['estado'];
$fecha_inicio_deuda = $data['fecha_inicio_deuda'];

try {
    $stmt = $conn->prepare("UPDATE prestamos 
        SET nombre = :nombre, tipo_persona = :tipo_persona, monto_deuda = :monto_deuda, 
            saldo_pendiente = :saldo_pendiente, estado = :estado, fecha_inicio_deuda = :fecha_inicio_deuda 
        WHERE id = :id");

    $stmt->bindParam(':id', $id, PDO::PARAM_INT);
    $stmt->bindParam(':nombre', $nombre);
    $stmt->bindParam(':tipo_persona', $tipo_persona);
    $stmt->bindParam(':monto_deuda', $monto_deuda);
    $stmt->bindParam(':saldo_pendiente', $saldo_pendiente);
    $stmt->bindParam(':estado', $estado);
    $stmt->bindParam(':fecha_inicio_deuda', $fecha_inicio_deuda);

    $stmt->execute();

    echo json_encode(['success' => true, 'message' => 'Préstamo actualizado correctamente.']);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Error al actualizar préstamo: ' . $e->getMessage()]);
}
