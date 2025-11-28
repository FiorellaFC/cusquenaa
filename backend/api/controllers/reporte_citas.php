<?php
header("Content-Type: application/json; charset=utf-8");

// RUTA CORRECTA SEGÚN TU PROYECTO REAL
require_once "../../includes/db.php";

$inicio = $_GET['inicio'] ?? date('Y-m-d', strtotime('-30 days'));
$fin    = $_GET['fin']    ?? date('Y-m-d');

try {
    $sql = "
        SELECT 
            c.fecha,
            c.hora,
            CONCAT(TRIM(c.nombre_cliente), ' ', TRIM(c.apellido_cliente)) as nombre_completo,
            c.telefono_cliente,
            COALESCE(s.nombre, 'Sin servicio') as servicio_nombre
        FROM citas c
        LEFT JOIN servicios s ON c.servicio_id = s.id
        WHERE c.estado = 'confirmada'
          AND c.fecha BETWEEN ? AND ?
        ORDER BY c.fecha DESC, c.hora DESC
    ";

    $stmt = $conn->prepare($sql);
    $stmt->execute([$inicio, $fin]);
    $citas = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Estadísticas
    $total = count($citas);

    $servicios = array_column($citas, 'servicio_nombre');
    $conteoServ = array_count_values(array_filter($servicios));
    $servicio_top = !empty($conteoServ) ? array_search(max($conteoServ), $conteoServ) : 'N/A';

    $clientes = array_column($citas, 'nombre_completo');
    $conteoCli = array_count_values(array_filter($clientes));
    $cliente_top = !empty($conteoCli) ? array_search(max($conteoCli), $conteoCli) : 'N/A';

    echo json_encode([
        "total"        => $total,
        "servicio_top" => $servicio_top,
        "cliente_top"  => $cliente_top,
        "citas"        => $citas
    ], JSON_UNESCAPED_UNICODE);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(["error" => $e->getMessage()]);
}
?>