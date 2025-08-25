<?php
require_once __DIR__ . '/../../../includes/db.php';
require_once __DIR__ . '/../../../includes/auth.php';

header('Content-Type: application/json');

verificarPermiso(['Administrador', 'Secretaria']);

$data = json_decode(file_get_contents('php://input'), true);

if (
    !isset($data['id'], $data['nombre'], $data['tipoBalance'], $data['mes'], $data['monto'])
) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Datos incompletos para actualizar el balance.']);
    exit();
}

$id = (int)$data['id'];
$nombre_descripcion = htmlspecialchars(trim($data['nombre']));
$tipo_balance = $data['tipoBalance'];
$mes_raw = $data['mes']; // Formato 'YYYY-MM'
$monto = (float)$data['monto'];

// Extraer el mes y el año del formato 'YYYY-MM'
$mes_numero = (int)substr($mes_raw, 5, 2);
$mes_map = [
    1 => 'Enero', 2 => 'Febrero', 3 => 'Marzo', 4 => 'Abril',
    5 => 'Mayo', 6 => 'Junio', 7 => 'Julio', 8 => 'Agosto',
    9 => 'Septiembre', 10 => 'Octubre', 11 => 'Noviembre', 12 => 'Diciembre'
];
$mes_nombre = $mes_map[$mes_numero] ?? null;

$anio = (int)substr($mes_raw, 0, 4); // AÑADIDO: EXTRAER EL AÑO

// Validaciones
$allowed_meses = ['Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'];
if (!in_array($mes_nombre, $allowed_meses)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'El valor del mes es inválido.']);
    exit();
}
if ($anio < 1900 || $anio > 2100) { // Rango razonable de años
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'El valor del año es inválido.']);
    exit();
}
$allowed_tipos_balance = ['Cotizaciones', 'Prestamos', 'Alquileres', 'Gastos', 'Dominical', 'Coordinadores'];
if (!in_array($tipo_balance, $allowed_tipos_balance)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'El tipo de balance es inválido.']);
    exit();
}

try {
    $stmt = $conn->prepare("UPDATE balances_empresa
        SET nombre_descripcion = :nombre_descripcion,
            tipo_balance = :tipo_balance,
            mes = :mes,
            monto = :monto,
            anio = :anio  -- AÑADIDO: Actualizar la columna 'anio'
        WHERE id = :id");

    $stmt->bindParam(':id', $id, PDO::PARAM_INT);
    $stmt->bindParam(':nombre_descripcion', $nombre_descripcion);
    $stmt->bindParam(':tipo_balance', $tipo_balance);
    $stmt->bindParam(':mes', $mes_nombre);
    $stmt->bindParam(':monto', $monto);
    $stmt->bindParam(':anio', $anio, PDO::PARAM_INT); // AÑADIDO: Bindear el año

    $stmt->execute();

    if ($stmt->rowCount() > 0) {
        echo json_encode(['success' => true, 'message' => 'Balance actualizado correctamente.']);
    } else {
        http_response_code(404);
        echo json_encode(['success' => false, 'error' => 'No se encontró el balance con el ID proporcionado o no hubo cambios.']);
    }

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Error de base de datos al actualizar balance: ' . $e->getMessage()]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Error al actualizar balance: ' . $e->getMessage()]);
}
?>