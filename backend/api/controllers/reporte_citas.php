<?php
header("Content-Type: application/json; charset=utf-8");

require_once __DIR__ . '/../../includes/db.php';

// CAMBIO: Por defecto tomamos el primer día del mes actual y el último día del mes actual
$inicio = $_GET['inicio'] ?? date('Y-m-01'); 
$fin    = $_GET['fin']    ?? date('Y-m-t');

try {
    $sql = "
        SELECT 
            c.fecha,
            c.hora,
            TRIM(CONCAT(COALESCE(c.nombre_cliente, ''), ' ', COALESCE(c.apellido_cliente, ''))) as nombre_completo,
            COALESCE(c.telefono_cliente, '-') as telefono_cliente,
            COALESCE(s.nombre, 'Sin servicio especificado') as servicio_nombre
        FROM citas c
        LEFT JOIN servicios s ON c.servicio_id = s.id
        
        -- CAMBIO: AHORA FILTRAMOS SOLO LAS 'COMPLETADAS' (YA PAGADAS)
        WHERE c.estado = 'completada'
          AND c.fecha BETWEEN ? AND ?
        ORDER BY c.fecha DESC, c.hora DESC
    ";

    $stmt = $conn->prepare($sql);
    $stmt->execute([$inicio, $fin]);
    $citas = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Estadísticas
    $total = count($citas);

    $servicios = array_column($citas, 'servicio_nombre');
    $servicios_validos = array_filter($servicios, function($s) { return $s !== 'Sin servicio especificado'; });
    $conteoServ = array_count_values($servicios_validos);
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
    echo json_encode(["error" => "Error en servidor: " . $e->getMessage()]);
}
?>