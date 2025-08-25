<?php
require_once __DIR__ . '/../../../includes/db.php';
require_once __DIR__ . '/../../../includes/auth.php';

header('Content-Type: application/json');

// Verifica el permiso del usuario. Asumo que Administrador y Secretaria pueden actualizar cotizaciones.
verificarPermiso(['Administrador', 'Secretaria']);

// Decodifica los datos JSON enviados en el cuerpo de la solicitud
$data = json_decode(file_get_contents('php://input'), true);

// Valida que todos los campos necesarios estén presentes para la actualización
if (
    !isset($data['id']) ||
    !isset($data['nombre']) ||
    !isset($data['apellido']) ||
    !isset($data['tipo_cotizacion']) ||
    !isset($data['pago']) || // 'pago' puede ser 0, así que usamos isset
    !isset($data['fecha_inicio']) ||
    !isset($data['fecha_fin']) ||
    !isset($data['estado'])
) {
    http_response_code(400); // Bad Request
    echo json_encode(['success' => false, 'message' => 'Datos incompletos para actualizar la cotización.']);
    exit();
}

// Asignar valores a variables desde los datos recibidos
$id = $data['id'];
$nombre = $data['nombre'];
$apellido = $data['apellido'];
$tipo_cotizacion = $data['tipo_cotizacion'];
$pago = (float)$data['pago']; // Asegura que 'pago' sea un número flotante
$fecha_inicio = $data['fecha_inicio'];
$fecha_fin = $data['fecha_fin'];
$estado = $data['estado'];

// Validación adicional de fechas (Fecha Fin no puede ser anterior a Fecha Inicio)
if (strtotime($fecha_fin) < strtotime($fecha_inicio)) {
    http_response_code(400); // Bad Request
    echo json_encode(['success' => false, 'message' => 'La Fecha Fin no puede ser anterior a la Fecha Inicio.']);
    exit();
}

try {
    // Prepara la consulta SQL para actualizar la cotización por su ID
    $stmt = $conn->prepare("UPDATE cotizaciones
                            SET nombre = :nombre,
                                apellido = :apellido,
                                tipo_cotizacion = :tipo_cotizacion,
                                pago = :pago,
                                fecha_inicio = :fecha_inicio,
                                fecha_fin = :fecha_fin,
                                estado = :estado
                            WHERE id = :id");

    // Vincula los parámetros
    $stmt->bindParam(':id', $id, PDO::PARAM_INT);
    $stmt->bindParam(':nombre', $nombre);
    $stmt->bindParam(':apellido', $apellido);
    $stmt->bindParam(':tipo_cotizacion', $tipo_cotizacion);
    $stmt->bindParam(':pago', $pago);
    $stmt->bindParam(':fecha_inicio', $fecha_inicio);
    $stmt->bindParam(':fecha_fin', $fecha_fin);
    $stmt->bindParam(':estado', $estado);

    $stmt->execute();

    // Verifica si se afectó alguna fila (si la cotización existía y se actualizó)
    if ($stmt->rowCount() > 0) {
        echo json_encode(['success' => true, 'message' => 'Cotización actualizada correctamente.']);
    } else {
        // Podría ser que el ID no existe o no hubo cambios en los datos
        http_response_code(404); // Not Found
        echo json_encode(['success' => false, 'message' => 'La cotización con el ID especificado no fue encontrada o no hubo cambios para actualizar.']);
    }

    $stmt = null;
    $conn = null;

} catch (PDOException $e) {
    // Captura cualquier excepción de PDO (errores de base de datos)
    http_response_code(500); // Internal Server Error
    echo json_encode(['success' => false, 'message' => 'Error al actualizar la cotización: ' . $e->getMessage()]);
}
?>