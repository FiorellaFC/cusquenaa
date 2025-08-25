<?php
require_once __DIR__ . '/../../../includes/db.php';
require_once __DIR__ . '/../../../includes/auth.php';

// Establecer el encabezado para JSON
header('Content-Type: application/json');

// Verificar permisos. Ajusta los roles según tu necesidad.
verificarPermiso(['Administrador', 'Secretaria']);

// Inicializar filtros y paginación
$descripcion = $_GET['descripcion'] ?? '';
$fecha_inicio = $_GET['fecha_inicio'] ?? '';
$fecha_fin = $_GET['fecha_fin'] ?? '';
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 10;
$offset = ($page - 1) * $limit;

try {
    $conditions = []; // Array para almacenar las partes de la cláusula WHERE
    $filterParams = []; // Array para almacenar los valores de los parámetros de filtro

    // Construir dinámicamente las condiciones WHERE y los parámetros de filtro
    if (!empty($descripcion)) {
    $conditions[] = "(descripcion LIKE ? OR tipo_gasto LIKE ?)";
    $filterParams[] = "%" . $descripcion . "%";
    $filterParams[] = "%" . $descripcion . "%";
    }
    $sql = "SELECT * FROM gastos";
    if (!empty($conditions)) {
        $sql .= " WHERE " . implode(" AND ", $conditions);
    }

    if (!empty($fecha_inicio)) {
        $conditions[] = "fecha >= ?";
        $filterParams[] = $fecha_inicio;
    }
    if (!empty($fecha_fin)) {
        $conditions[] = "fecha <= ?";
        $filterParams[] = $fecha_fin;
    }

    // Construir la cláusula WHERE completa
    $whereClause = "WHERE 1=1";
    if (!empty($conditions)) {
        $whereClause .= " AND " . implode(" AND ", $conditions);
    }

    // --- Obtener el total de registros para la paginación ---
    $sqlTotal = "SELECT COUNT(*) FROM gastos_lubricentros " . $whereClause;
    $stmtTotal = $conn->prepare($sqlTotal);
    if ($stmtTotal === false) {
        throw new Exception("Error al preparar la consulta de conteo: " . implode(" ", $conn->errorInfo()));
    }
    // Ejecutar la consulta de conteo con los parámetros de filtro
    $stmtTotal->execute($filterParams);
    $totalRecords = $stmtTotal->fetchColumn();
    $stmtTotal = null; // Liberar el statement

    // --- Obtener los gastos con paginación y filtros ---
  
    $sqlGastos = "SELECT id, descripcion, tipo_gasto, monto, fecha, detalle FROM gastos_lubricentros " . $whereClause . " ORDER BY id ASC LIMIT ? OFFSET ?";
    $stmtGastos = $conn->prepare($sqlGastos);
    if ($stmtGastos === false) {
        throw new Exception("Error al preparar la consulta de gastos: " . implode(" ", $conn->errorInfo()));
    }

    // Vincular explícitamente los parámetros uno por uno
    $paramIndex = 1;

    // Vincular los parámetros de filtro
    foreach ($filterParams as $value) {
        $stmtGastos->bindValue($paramIndex++, $value);
    }

    // Vincular los parámetros de paginación con tipo entero explícito
    $stmtGastos->bindValue($paramIndex++, $limit, PDO::PARAM_INT);
    $stmtGastos->bindValue($paramIndex++, $offset, PDO::PARAM_INT);

    // Ejecutar la consulta (sin pasar un array de parámetros aquí, ya están vinculados)
    $stmtGastos->execute();

    $gastos = $stmtGastos->fetchAll(PDO::FETCH_ASSOC);
    $stmtGastos = null; // Liberar el statement
    $conn = null; // Cerrar la conexión

    echo json_encode(['gastos' => $gastos, 'total' => $totalRecords]);

} catch (Exception $e) {
    http_response_code(500); // Internal Server Error
    echo json_encode(['error' => $e->getMessage()]);
}
?>