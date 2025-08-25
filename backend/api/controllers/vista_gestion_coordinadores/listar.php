<?php
// C:\xampp\htdocs\cusquena\backend\api\controllers\coordinadores\listar.php

require_once __DIR__ . '/../../../includes/db.php';
require_once __DIR__ . '/../../../includes/auth.php';

header('Content-Type: application/json');

try {
    verificarPermiso(['Administrador', 'Secretaria']);

    $nombre_apellido = $_GET['nombre'] ?? '';
    $fecha = $_GET['fecha'] ?? ''; // Ahora solo 'fecha'
    $paradero = $_GET['paradero'] ?? ''; // Agregamos paradero aquí
    $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
    $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 10;
    $offset = ($page - 1) * $limit;

    $conditions = [];
    $params = [];

    // Filtro por nombre o apellidos
    if (!empty($nombre_apellido)) {
        $conditions[] = "(nombre LIKE ? OR apellidos LIKE ?)";
        $params[] = "%" . $nombre_apellido . "%";
        $params[] = "%" . $nombre_apellido . "%";
    }

    // Filtro por fecha exacta
    if (!empty($fecha)) {
        $conditions[] = "fecha = ?"; // Condición para fecha exacta
        $params[] = $fecha;
    }

    // Filtro por paradero
    if (!empty($paradero)) {
        $conditions[] = "paradero LIKE ?";
        $params[] = "%" . $paradero . "%";
    }

    $whereClause = "WHERE 1=1";
    if (!empty($conditions)) {
        $whereClause .= " AND " . implode(" AND ", $conditions);
    }

    // Total de registros
    $sqlTotal = "SELECT COUNT(*) FROM coordinadores $whereClause";
    $stmtTotal = $conn->prepare($sqlTotal);
    $stmtTotal->execute($params);
    $totalRecords = $stmtTotal->fetchColumn();
    $stmtTotal = null;

    // Total general de monto_diario
    $sqlTotalMonto = "SELECT SUM(monto_diario) FROM coordinadores $whereClause";
    $stmtMonto = $conn->prepare($sqlTotalMonto);
    $stmtMonto->execute($params);
    $totalGeneralMonto = $stmtMonto->fetchColumn();
    $totalGeneralMonto = $totalGeneralMonto !== null ? (float)$totalGeneralMonto : 0.00;
    $stmtMonto = null;

    // Datos paginados
    $sql = "SELECT id, nombre, apellidos, paradero, monto_diario, fecha, estado, contacto 
            FROM coordinadores $whereClause 
            ORDER BY fecha DESC, nombre ASC 
            LIMIT ? OFFSET ?";
    $stmt = $conn->prepare($sql);

    // Bind dinámico
    $paramIndex = 1;
    foreach ($params as $val) {
        $stmt->bindValue($paramIndex++, $val);
    }
    $stmt->bindValue($paramIndex++, $limit, PDO::PARAM_INT);
    $stmt->bindValue($paramIndex++, $offset, PDO::PARAM_INT);

    $stmt->execute();
    $coordinadores = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        'coordinadores' => $coordinadores,
        'total' => $totalRecords,
        'total_general_monto' => $totalGeneralMonto
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Error al listar coordinadores: ' . $e->getMessage()]);
}
?>