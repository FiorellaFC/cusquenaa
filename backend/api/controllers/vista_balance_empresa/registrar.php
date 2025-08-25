<?php
require_once __DIR__ . '/../../../includes/db.php';
require_once __DIR__ . '/../../../includes/auth.php';

header('Content-Type: application/json');

verificarPermiso(['Administrador', 'Secretaria']);

$data = json_decode(file_get_contents('php://input'), true);

if (
    empty($data['nombre']) ||
    empty($data['tipoBalance']) ||
    empty($data['mes']) ||
    !isset($data['monto'])
) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Datos incompletos para registrar el balance.']);
    exit();
}

$nombre_descripcion = htmlspecialchars(trim($data['nombre']));
$tipo_balance = $data['tipoBalance'];
$mes_raw = $data['mes']; // Formato 'YYYY-MM'
$monto = (float)$data['monto'];

// Extraer el mes del formato 'YYYY-MM' y convertirlo al nombre del mes en español
$mes_numero = (int)substr($mes_raw, 5, 2);
$mes_map = [
    1 => 'Enero', 2 => 'Febrero', 3 => 'Marzo', 4 => 'Abril',
    5 => 'Mayo', 6 => 'Junio', 7 => 'Julio', 8 => 'Agosto',
    9 => 'Septiembre', 10 => 'Octubre', 11 => 'Noviembre', 12 => 'Diciembre'
];
$mes_nombre = $mes_map[$mes_numero] ?? null;

// EXTRAER EL AÑO DEL mes_raw
$anio = (int)substr($mes_raw, 0, 4);

// Validar que el nombre del mes sea uno de los valores permitidos por el ENUM
$allowed_meses = ['Enero','Febrero','Marzo','Abril','Mayo','Junio','Julio','Agosto','Septiembre','Octubre','Noviembre','Diciembre'];
if (!in_array($mes_nombre, $allowed_meses)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'El valor del mes es inválido.']);
    exit();
}

// Validar que el año sea un número válido
if ($anio < 1900 || $anio > 2100) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'El valor del año es inválido.']);
    exit();
}

// ✅ ahora con los nuevos tipos añadidos
$allowed_tipos_balance = ['Cotizaciones', 'Prestamos', 'Alquileres', 'Gastos', 'Dominical', 'Coordinadores'];
if (!in_array($tipo_balance, $allowed_tipos_balance)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'El tipo de balance es inválido.']);
    exit();
}

try {
    $stmt = $conn->prepare("INSERT INTO balances_empresa (nombre_descripcion, tipo_balance, mes, monto, anio)
                            VALUES (:nombre_descripcion, :tipo_balance, :mes, :monto, :anio)");

    $stmt->bindParam(':nombre_descripcion', $nombre_descripcion);
    $stmt->bindParam(':tipo_balance', $tipo_balance);
    $stmt->bindParam(':mes', $mes_nombre);
    $stmt->bindParam(':monto', $monto);
    $stmt->bindParam(':anio', $anio, PDO::PARAM_INT);

    if ($stmt->execute()) {
        echo json_encode([
            'success' => true,
            'message' => 'Balance registrado exitosamente!',
            'id' => $conn->lastInsertId()
        ]);
    } else {
        throw new Exception("Error al ejecutar la consulta: " . implode(" ", $stmt->errorInfo()));
    }

    $stmt = null;
    $conn = null;
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
?>
