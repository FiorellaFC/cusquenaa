<?php
require_once __DIR__ . '/../../../includes/db.php';
require_once __DIR__ . '/../../../includes/auth.php';

header('Content-Type: application/json');

verificarPermiso(['Administrador', 'Secretaria']);

$data = json_decode(file_get_contents('php://input'), true);
file_put_contents('php://stderr', print_r($data, true));
// Validar campos obligatorios (contacto obligatorio)
if (
    empty($data['nombre']) ||
    empty($data['apellidos']) ||
    empty($data['paradero']) ||
    !isset($data['monto_diario']) || // monto_diario puede ser 0, por eso isset
    empty($data['fecha']) ||
    empty($data['estado']) ||
    empty($data['contacto'])
) {
    http_response_code(400);
    echo json_encode(['error' => 'Datos incompletos para registrar el coordinador.']);
    exit();
}

// Asignar valores
$nombre = $data['nombre'];
$apellidos = $data['apellidos'];
$paradero = $data['paradero'];
$monto_diario = (float)$data['monto_diario'];
$fecha = $data['fecha'];
$estado = $data['estado'];
$contacto = $data['contacto'];

try {
    $stmt = $conn->prepare("INSERT INTO coordinadores (nombre, apellidos, paradero, monto_diario, fecha, estado, contacto)
                            VALUES (:nombre, :apellidos, :paradero, :monto_diario, :fecha, :estado, :contacto)");

    $stmt->bindParam(':nombre', $nombre);
    $stmt->bindParam(':apellidos', $apellidos);
    $stmt->bindParam(':paradero', $paradero);
    $stmt->bindParam(':monto_diario', $monto_diario);
    $stmt->bindParam(':fecha', $fecha);
    $stmt->bindParam(':estado', $estado);
    $stmt->bindParam(':contacto', $contacto);

    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Coordinador registrado exitosamente!', 'id' => $conn->lastInsertId()]);
    } else {
        throw new Exception("Error al ejecutar la consulta: " . implode(" ", $stmt->errorInfo()));
    }

    $stmt = null;
    $conn = null;

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
