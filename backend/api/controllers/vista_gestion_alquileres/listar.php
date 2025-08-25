<?php
// C:\xampp\htdocs\cusquena\backend\api\controllers\vista_alquileres\listar.php

require_once __DIR__ . '/../../../includes/db.php';
require_once __DIR__ . '/../../../includes/auth.php';

header('Content-Type: application/json');

verificarPermiso(['Administrador', 'Secretaria']);

$nombre = $_GET['nombre'] ?? '';
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

    $whereClause = "WHERE 1=1";
    if (!empty($conditions)) {
        $whereClause .= " AND " . implode(" AND ", $conditions);
    }

    // --- Obtener el total de registros para paginación ---
    $sqlTotal = "SELECT COUNT(*) FROM alquileres " . $whereClause;
    $stmtTotal = $conn->prepare($sqlTotal);
    if ($stmtTotal === false) {
        throw new Exception("Error al preparar la consulta de conteo: " . implode(" ", $conn->errorInfo()));
    }
    $stmtTotal->execute($params);
    $totalRecords = $stmtTotal->fetchColumn();
    $stmtTotal = null;

    // --- Obtener los alquileres con paginación y filtros ---
    $sqlAlquileres = "SELECT id, nombre, tipo, fecha_inicio, periodicidad, pago, estado FROM alquileres " . $whereClause . " ORDER BY fecha_inicio DESC, nombre ASC LIMIT ? OFFSET ?";
    $stmtAlquileres = $conn->prepare($sqlAlquileres);
    if ($stmtAlquileres === false) {
        throw new Exception("Error al preparar la consulta de alquileres: " . implode(" ", $conn->errorInfo()));
    }

    $paramIndex = 1;
    foreach ($params as $value) {
        $stmtAlquileres->bindValue($paramIndex++, $value);
    }
    $stmtAlquileres->bindValue($paramIndex++, $limit, PDO::PARAM_INT);
    $stmtAlquileres->bindValue($paramIndex++, $offset, PDO::PARAM_INT);

    $stmtAlquileres->execute();
    $alquileres = $stmtAlquileres->fetchAll(PDO::FETCH_ASSOC);
    $stmtAlquileres = null;
    $conn = null;

    echo json_encode(['alquileres' => $alquileres, 'total' => $totalRecords]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
?>
