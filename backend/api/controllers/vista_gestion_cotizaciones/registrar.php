<?php
require_once __DIR__ . '/../../../includes/db.php';
require_once __DIR__ . '/../../../includes/auth.php';

header('Content-Type: application/json');

// Verifica el permiso del usuario. Asumo que Administrador y Secretaria pueden registrar cotizaciones.
verificarPermiso(['Administrador', 'Secretaria']);

// Decodifica los datos JSON enviados en el cuerpo de la solicitud
$data = json_decode(file_get_contents('php://input'), true);

// Opcional: Para depuración, puedes escribir los datos recibidos en el log de errores de PHP
// file_put_contents('php://stderr', print_r($data, true));

// Validar campos obligatorios
// Todos los campos de la tabla cotizaciones son necesarios para un registro completo
if (
    empty($data['nombre']) ||
    empty($data['apellido']) ||
    empty($data['tipo_cotizacion']) ||
    !isset($data['pago']) || // 'pago' puede ser 0, así que usamos isset
    empty($data['fecha_inicio']) ||
    empty($data['fecha_fin']) ||
    empty($data['estado'])
) {
    http_response_code(400); // Bad Request
    echo json_encode(['success' => false, 'message' => 'Datos incompletos. Todos los campos de la cotización son obligatorios.']);
    exit();
}

// Asignar valores a variables
$nombre = $data['nombre'];
$apellido = $data['apellido'];
$tipo_cotizacion = $data['tipo_cotizacion'];
$pago = (float)$data['pago']; // Asegura que 'pago' sea un número flotante
$fecha_inicio = $data['fecha_inicio'];
$fecha_fin = $data['fecha_fin'];
$estado = $data['estado'];

// Validación adicional de fechas
if (strtotime($fecha_fin) < strtotime($fecha_inicio)) {
    http_response_code(400); // Bad Request
    echo json_encode(['success' => false, 'message' => 'La Fecha Fin no puede ser anterior a la Fecha Inicio.']);
    exit();
}

try {
    // Prepara la consulta SQL para insertar una nueva cotización
    $stmt = $conn->prepare("INSERT INTO cotizaciones (nombre, apellido, tipo_cotizacion, pago, fecha_inicio, fecha_fin, estado)
                            VALUES (:nombre, :apellido, :tipo_cotizacion, :pago, :fecha_inicio, :fecha_fin, :estado)");

    // Vincula los parámetros
    $stmt->bindParam(':nombre', $nombre);
    $stmt->bindParam(':apellido', $apellido);
    $stmt->bindParam(':tipo_cotizacion', $tipo_cotizacion);
    $stmt->bindParam(':pago', $pago);
    $stmt->bindParam(':fecha_inicio', $fecha_inicio);
    $stmt->bindParam(':fecha_fin', $fecha_fin);
    $stmt->bindParam(':estado', $estado);

    // Ejecuta la consulta
    if ($stmt->execute()) {
        // Devuelve una respuesta de éxito con el ID de la nueva cotización
        echo json_encode(['success' => true, 'message' => 'Cotización registrada exitosamente!', 'id' => $conn->lastInsertId()]);
    } else {
        // Lanza una excepción si la ejecución de la consulta falla
        throw new Exception("Error al ejecutar la consulta para registrar la cotización: " . implode(" ", $stmt->errorInfo()));
    }

    // Cierra la conexión y el statement
    $stmt = null;
    $conn = null;

} catch (Exception $e) {
    // Captura cualquier excepción y devuelve un mensaje de error 500
    http_response_code(500); // Internal Server Error
    echo json_encode(['success' => false, 'message' => 'Error en el servidor al intentar registrar la cotización: ' . $e->getMessage()]);
}
?>