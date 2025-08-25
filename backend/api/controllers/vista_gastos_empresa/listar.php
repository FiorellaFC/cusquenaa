<?php
// C:\xampp\htdocs\cusquena\backend\api\controllers\vista_gastos_empresa\listar.php

require_once __DIR__ . '/../../../includes/db.php';
require_once __DIR__ . '/../../../includes/auth.php';

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

header('Content-Type: application/json');

verificarPermiso(['Administrador', 'Secretaria']);

$descripcion = $_GET['descripcion'] ?? '';
$fecha_inicio = $_GET['fecha_inicio'] ?? '';
$fecha_fin = $_GET['fecha_fin'] ?? '';
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 10;
$offset = ($page - 1) * $limit;

try {
    $conditions = [];
    $params = [];

    if (!empty($descripcion)) {
        $conditions[] = "descripcion LIKE ?";
        $params[] = "%" . $descripcion . "%";
    }
    if (!empty($fecha_inicio)) {
        $conditions[] = "fecha >= ?";
        $params[] = $fecha_inicio;
    }
    if (!empty($fecha_fin)) {
        $conditions[] = "fecha <= ?";
        $params[] = $fecha_fin;
    }

    $whereClause = "WHERE 1=1";
    if (!empty($conditions)) {
        $whereClause .= " AND " . implode(" AND ", $conditions);
    }

    // --- Obtener el total de registros para paginación ---
    $sqlTotal = "SELECT COUNT(*) FROM gastos_empresa " . $whereClause;
    $stmtTotal = $conn->prepare($sqlTotal);
    if ($stmtTotal === false) {
        throw new Exception("Error al preparar la consulta de conteo: " . implode(" ", $conn->errorInfo()));
    }
    $stmtTotal->execute($params);
    $totalRecords = $stmtTotal->fetchColumn();
    $stmtTotal = null;

    // --- Obtener el total general del monto de gastos filtrados ---
    $sqlTotalMonto = "SELECT SUM(monto) FROM gastos_empresa " . $whereClause;
    $stmtTotalMonto = $conn->prepare($sqlTotalMonto);
    if ($stmtTotalMonto === false) {
        throw new Exception("Error al preparar la consulta de suma total: " . implode(" ", $conn->errorInfo()));
    }
    $stmtTotalMonto->execute($params);
    $totalGeneralMonto = $stmtTotalMonto->fetchColumn();
    $totalGeneralMonto = $totalGeneralMonto !== null ? (float)$totalGeneralMonto : 0.00;
    $stmtTotalMonto = null;

    // --- Obtener los gastos con paginación y filtros ---
    $sqlGastos = "SELECT id, descripcion, tipo_gasto, monto, fecha, detalle FROM gastos_empresa " . $whereClause . " ORDER BY fecha ASC LIMIT ? OFFSET ?";
    $stmtGastos = $conn->prepare($sqlGastos);
    if ($stmtGastos === false) {
        throw new Exception("Error al preparar la consulta de gastos: " . implode(" ", $conn->errorInfo()));
    }

    $paramIndex = 1;
    foreach ($params as $value) {
        $stmtGastos->bindValue($paramIndex++, $value);
    }
    $stmtGastos->bindValue($paramIndex++, $limit, PDO::PARAM_INT);
    $stmtGastos->bindValue($paramIndex++, $offset, PDO::PARAM_INT);

    $stmtGastos->execute();
    $gastos = $stmtGastos->fetchAll(PDO::FETCH_ASSOC);
    $stmtGastos = null;
    $conn = null;

    echo json_encode(['gastos' => $gastos, 'total' => $totalRecords, 'total_general_monto' => $totalGeneralMonto]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
?>
