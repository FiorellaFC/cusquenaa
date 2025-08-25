<?php
require_once __DIR__ . '/../../../includes/db.php';
require_once __DIR__ . '/../../../includes/auth.php';

header('Content-Type: application/json');
verificarPermiso(['Administrador', 'Secretaria']);

$data = json_decode(file_get_contents('php://input'), true);
file_put_contents('php://stderr', print_r($data, true)); // Para depuración en consola

// Validar campos obligatorios
if (
    empty($data['nombre']) ||
    empty($data['apellidos']) ||
    empty($data['fecha_domingo']) ||
    empty($data['semana_inicio']) ||
    empty($data['semana_fin']) ||
    !isset($data['monto_dominical']) || // puede ser 0.00
    empty($data['estado'])
) {
    http_response_code(400);
    echo json_encode(['error' => 'Datos incompletos para registrar el dominical.']);
    exit();
}

// Asignar valores
$nombre = $data['nombre'];
$apellidos = $data['apellidos'];
$fecha_domingo = $data['fecha_domingo'];
$semana_inicio = $data['semana_inicio'];
$semana_fin = $data['semana_fin'];
$monto_dominical = (float)$data['monto_dominical'];
$estado = $data['estado'];

try {
    $stmt = $conn->prepare("INSERT INTO dominical 
        (nombre, apellidos, fecha_domingo, semana_inicio, semana_fin, monto_dominical, estado) 
        VALUES (:nombre, :apellidos, :fecha_domingo, :semana_inicio, :semana_fin, :monto_dominical, :estado)");

    $stmt->bindParam(':nombre', $nombre);
    $stmt->bindParam(':apellidos', $apellidos);
    $stmt->bindParam(':fecha_domingo', $fecha_domingo);
    $stmt->bindParam(':semana_inicio', $semana_inicio);
    $stmt->bindParam(':semana_fin', $semana_fin);
    $stmt->bindParam(':monto_dominical', $monto_dominical);
    $stmt->bindParam(':estado', $estado);

    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Dominical registrado exitosamente!', 'id' => $conn->lastInsertId()]);
    } else {
        throw new Exception("Error al ejecutar la consulta: " . implode(" ", $stmt->errorInfo()));
    }

    $stmt = null;
    $conn = null;

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}