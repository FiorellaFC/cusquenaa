<?php
header("Content-Type: application/json; charset=utf-8");

require_once __DIR__ . '/../../includes/db.php';

$inicio = $_GET['inicio'] ?? date('Y-m-01'); 
$fin    = $_GET['fin']    ?? date('Y-m-t');

try {
    // CONSULTA AVANZADA CON GANANCIAS Y SERVICIOS MÚLTIPLES
    $sql = "
        SELECT 
            c.fecha,
            c.hora,
            TRIM(CONCAT(COALESCE(c.nombre_cliente, ''), ' ', COALESCE(c.apellido_cliente, ''))) as nombre_completo,
            COALESCE(c.telefono_cliente, '-') as telefono_cliente,
            
            -- Lista de servicios
            GROUP_CONCAT(s.nombre SEPARATOR ', ') as servicio_nombre,
            
            -- Cálculo de Precio: Si precio_final > 0 usa ese, si no, suma los detalles
            CASE 
                WHEN c.precio_final > 0 THEN c.precio_final 
                ELSE COALESCE(SUM(cd.precio_al_momento), 0) 
            END as precio_total

        FROM citas c
        -- Unir con detalles y servicios
        LEFT JOIN citas_detalles cd ON c.id = cd.cita_id
        LEFT JOIN servicios s ON cd.servicio_id = s.id
        
        WHERE c.estado = 'completada'
          AND c.fecha BETWEEN ? AND ?
        
        GROUP BY c.id
        ORDER BY c.fecha DESC, c.hora DESC
    ";

    $stmt = $conn->prepare($sql);
    $stmt->execute([$inicio, $fin]);
    $citas = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Estadísticas
    $total_citas = count($citas);
    $total_ganancia = 0;

    // Calcular ganancia total y buscar tops
    $todos_servicios = [];
    $todos_clientes = [];

    foreach ($citas as $c) {
        $total_ganancia += (float)$c['precio_total'];
        if($c['nombre_completo']) $todos_clientes[] = $c['nombre_completo'];
        
        // Separar servicios para contar individuales
        if($c['servicio_nombre']) {
            $servs = explode(', ', $c['servicio_nombre']);
            foreach($servs as $sv) $todos_servicios[] = $sv;
        }
    }

    // Tops
    $conteoServ = array_count_values($todos_servicios);
    $servicio_top = !empty($conteoServ) ? array_search(max($conteoServ), $conteoServ) : 'N/A';

    $conteoCli = array_count_values($todos_clientes);
    $cliente_top = !empty($conteoCli) ? array_search(max($conteoCli), $conteoCli) : 'N/A';

    echo json_encode([
        "total"          => $total_citas,
        "ganancia_total" => number_format($total_ganancia, 2), // Formato 1,500.00
        "servicio_top"   => $servicio_top,
        "cliente_top"    => $cliente_top,
        "citas"          => $citas
    ], JSON_UNESCAPED_UNICODE);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(["error" => "Error en servidor: " . $e->getMessage()]);
}
?>