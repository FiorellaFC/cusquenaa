<?php
// C:\xampp\htdocs\cusquena\backend\api\controllers\vista_gestion_dominical\listar.php

require_once __DIR__ . '/../../../includes/db.php';
require_once __DIR__ . '/../../../includes/auth.php';

header('Content-Type: application/json');

try {
    verificarPermiso(['Administrador', 'Secretaria']);

    // Obtener parámetros de filtro
    $nombre_apellido = $_GET['nombre'] ?? '';
    $semana_inicio_filtro = $_GET['semana_inicio'] ?? '';
    $semana_fin_filtro = $_GET['semana_fin'] ?? '';
    $estado = $_GET['estado'] ?? '';
    
    // Obtener parámetros de paginación
    $page = isset($_GET['pagina']) ? (int)$_GET['pagina'] : 1;
    $limit = isset($_GET['limite']) ? (int)$_GET['limite'] : 10;
    $offset = ($page - 1) * $limit;

    $conditions = [];
    $params = [];

    // Llenar el array de parámetros y condiciones de filtro
    if (!empty($nombre_apellido)) {
        $conditions[] = "(nombre LIKE ? OR apellidos LIKE ?)";
        $params[] = "%" . $nombre_apellido . "%";
        $params[] = "%" . $nombre_apellido . "%";
    }

    if (!empty($semana_inicio_filtro)) {
        $conditions[] = "semana_inicio >= ?";
        $params[] = $semana_inicio_filtro;
    }

    if (!empty($semana_fin_filtro)) {
        $conditions[] = "semana_fin <= ?";
        $params[] = $semana_fin_filtro;
    }

    if (!empty($estado)) {
        $conditions[] = "estado = ?";
        $params[] = $estado;
    }

    // Usar la cláusula WHERE 1=1 para una construcción de SQL más segura
    $whereClause = "WHERE 1=1";
    if (!empty($conditions)) {
        $whereClause .= " AND " . implode(" AND ", $conditions);
    }

    // Consulta para el total de registros
    $sqlTotal = "SELECT COUNT(*) FROM dominical $whereClause";
    $stmtTotal = $conn->prepare($sqlTotal);
    $stmtTotal->execute($params);
    $totalRecords = $stmtTotal->fetchColumn();
    $stmtTotal = null;

    // Consulta para el total del monto dominical
    $sqlMontoTotal = "SELECT SUM(monto_dominical) FROM dominical $whereClause";
    $stmtMonto = $conn->prepare($sqlMontoTotal);
    $stmtMonto->execute($params);
    $totalGeneralMonto = $stmtMonto->fetchColumn();
    $totalGeneralMonto = $totalGeneralMonto !== null ? (float)$totalGeneralMonto : 0.00;
    $stmtMonto = null;

    // Consulta principal con paginación
    $sql = "SELECT id, nombre, apellidos, fecha_domingo, semana_inicio, semana_fin, monto_dominical, estado
            FROM dominical
            $whereClause
            ORDER BY fecha_domingo DESC
            LIMIT ? OFFSET ?";
    
    $stmt = $conn->prepare($sql);

    // Bind dinámico: primero los parámetros de filtro, luego los de paginación
    $paramIndex = 1;
    foreach ($params as $val) {
        $stmt->bindValue($paramIndex++, $val);
    }
    $stmt->bindValue($paramIndex++, $limit, PDO::PARAM_INT);
    $stmt->bindValue($paramIndex++, $offset, PDO::PARAM_INT);

    $stmt->execute();
    $dominicales = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        'success' => true,
        'dominicales' => $dominicales,
        'total_registros' => $totalRecords,
        'total_general_monto' => $totalGeneralMonto,
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Error al listar dominicales: ' . $e->getMessage()
    ]);
}