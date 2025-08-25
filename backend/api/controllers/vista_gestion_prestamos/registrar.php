<?php
require_once __DIR__ . '/../../../includes/db.php';
require_once __DIR__ . '/../../../includes/auth.php';

header('Content-Type: application/json');

verificarPermiso(['Administrador', 'Secretaria']);

$data = json_decode(file_get_contents('php://input'), true);
file_put_contents('php://stderr', print_r($data, true));

// Validar campos obligatorios
if (
    empty($data['nombre']) ||
    empty($data['tipo_persona']) ||
    !isset($data['monto_deuda']) || // puede ser 0
    !isset($data['saldo_pendiente']) || // puede ser 0
    empty($data['estado']) ||
    empty($data['fecha_inicio_deuda'])
) {
    http_response_code(400);
    echo json_encode(['error' => 'Datos incompletos para registrar el préstamo.']);
    exit();
}

// Asignar valores
$nombre = $data['nombre'];
$tipo_persona = $data['tipo_persona'];
$monto_deuda = (float)$data['monto_deuda'];
$saldo_pendiente = (float)$data['saldo_pendiente'];
$estado = $data['estado'];
$fecha_inicio_deuda = $data['fecha_inicio_deuda'];

try {
    $stmt = $conn->prepare("INSERT INTO prestamos (nombre, tipo_persona, monto_deuda, saldo_pendiente, estado, fecha_inicio_deuda)
                            VALUES (:nombre, :tipo_persona, :monto_deuda, :saldo_pendiente, :estado, :fecha_inicio_deuda)");

    $stmt->bindParam(':nombre', $nombre);
    $stmt->bindParam(':tipo_persona', $tipo_persona);
    $stmt->bindParam(':monto_deuda', $monto_deuda);
    $stmt->bindParam(':saldo_pendiente', $saldo_pendiente);
    $stmt->bindParam(':estado', $estado);
    $stmt->bindParam(':fecha_inicio_deuda', $fecha_inicio_deuda);

    if ($stmt->execute()) {
        echo json_encode([
            'success' => true,
            'message' => 'Préstamo registrado exitosamente!',
            'id' => $conn->lastInsertId()
        ]);
    } else {
        throw new Exception("Error al ejecutar la consulta: " . implode(" ", $stmt->errorInfo()));
    }

    $stmt = null;
    $conn = null;
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
