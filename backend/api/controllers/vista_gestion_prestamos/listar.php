<?php
require_once __DIR__ . '/../../../includes/db.php';
require_once __DIR__ . '/../../../includes/auth.php';

header('Content-Type: application/json');

verificarPermiso(['Administrador', 'Secretaria']);

$nombre = $_GET['nombre'] ?? '';
$fecha_inicio = $_GET['fecha_inicio'] ?? '';
$fecha_fin = $_GET['fecha_fin'] ?? '';
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 10;
$offset = ($page - 1) * $limit;

try {
    $conditions = [];
    $params = [];

    if (!empty($nombre)) {
        $conditions[] = "nombre LIKE ?";
        $params[] = "%" . $nombre . "%";
    }

    if (!empty($fecha_inicio)) {
        $conditions[] = "fecha_inicio_deuda >= ?";
        $params[] = $fecha_inicio;
    }

    if (!empty($fecha_fin)) {
        $conditions[] = "fecha_inicio_deuda <= ?";
        $params[] = $fecha_fin;
    }

    $whereClause = "WHERE 1=1";
    if (!empty($conditions)) {
        $whereClause .= " AND " . implode(" AND ", $conditions);
    }

    // Total de registros
    $sqlTotal = "SELECT COUNT(*) FROM prestamos $whereClause";
    $stmtTotal = $conn->prepare($sqlTotal);
    $stmtTotal->execute($params);
    $totalRecords = $stmtTotal->fetchColumn();
    $stmtTotal = null;

    // Total general de monto_deuda
    $sqlTotalMonto = "SELECT SUM(monto_deuda) FROM prestamos $whereClause";
    $stmtMonto = $conn->prepare($sqlTotalMonto);
    $stmtMonto->execute($params);
    $totalGeneralMonto = $stmtMonto->fetchColumn();
    $totalGeneralMonto = $totalGeneralMonto !== null ? (float)$totalGeneralMonto : 0.00;
    $stmtMonto = null;

    // Datos paginados
    $sql = "SELECT id, nombre, tipo_persona, monto_deuda, saldo_pendiente, estado, fecha_inicio_deuda 
            FROM prestamos 
            $whereClause 
            ORDER BY fecha_inicio_deuda DESC, nombre ASC 
            LIMIT ? OFFSET ?";
    $stmt = $conn->prepare($sql);

    // Enlazar parámetros dinámicos
    $paramIndex = 1;
    foreach ($params as $val) {
        $stmt->bindValue($paramIndex++, $val);
    }
    $stmt->bindValue($paramIndex++, $limit, PDO::PARAM_INT);
    $stmt->bindValue($paramIndex++, $offset, PDO::PARAM_INT);

    $stmt->execute();
    $prestamos = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        'prestamos' => $prestamos,
        'total' => $totalRecords,
        'total_general_monto' => $totalGeneralMonto
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Error al listar préstamos: ' . $e->getMessage()]);
}
