<?php
require_once __DIR__ . '/../../../includes/db.php';
require_once __DIR__ . '/../../../includes/auth.php';

header('Content-Type: application/json');

verificarPermiso(['Administrador', 'Secretaria']);

$data = json_decode(file_get_contents('php://input'), true);

// Validar que los campos necesarios estén presentes
if (
    !isset(
        $data['id'],
        $data['nombre'],
        $data['apellidos'],
        $data['fecha_domingo'],
        $data['semana_inicio'],
        $data['semana_fin'],
        $data['monto_dominical'],
        $data['estado'] 
    )
) {
    http_response_code(400);
    echo json_encode(['error' => 'Datos incompletos para actualizar.']);
    exit();
}

// Asignar datos
$id = $data['id'];
$nombre = $data['nombre'];
$apellidos = $data['apellidos'];
$fecha_domingo = $data['fecha_domingo'];
$semana_inicio = $data['semana_inicio'];
$semana_fin = $data['semana_fin'];
$monto_dominical = (float)$data['monto_dominical'];
$estado = $data['estado'];

try {
    $stmt = $conn->prepare("UPDATE dominical 
        SET nombre = :nombre, 
            apellidos = :apellidos, 
            fecha_domingo = :fecha_domingo, 
            semana_inicio = :semana_inicio, 
            semana_fin = :semana_fin, 
            monto_dominical = :monto_dominical, 
            estado = :estado
        WHERE id = :id");

    $stmt->bindParam(':nombre', $nombre);
    $stmt->bindParam(':apellidos', $apellidos);
    $stmt->bindParam(':fecha_domingo', $fecha_domingo);
    $stmt->bindParam(':semana_inicio', $semana_inicio);
    $stmt->bindParam(':semana_fin', $semana_fin);
    $stmt->bindParam(':monto_dominical', $monto_dominical);
    $stmt->bindParam(':estado', $estado);
    $stmt->bindParam(':id', $id, PDO::PARAM_INT);

    $stmt->execute();

    echo json_encode(['success' => true, 'message' => 'Dominical actualizado correctamente.']);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Error al actualizar: ' . $e->getMessage()]);
}