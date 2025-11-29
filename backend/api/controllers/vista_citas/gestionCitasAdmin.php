<?php
header('Content-Type: application/json');
require_once "../../../includes/db.php"; 

$method = $_SERVER['REQUEST_METHOD'];
$data = json_decode(file_get_contents('php://input'), true);

// Configuración Paginación
$limit = 15; 

switch ($method) {
    case 'GET':
        try {
            $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
            $start = ($page - 1) * $limit;
            $params = [];
            
            // Construcción de filtros (WHERE)
            $whereSQL = "WHERE 1=1";

            // Filtro específico para historial de cliente
            if (!empty($_GET['cliente_id'])) {
                $whereSQL .= " AND c.cliente_id = :cliente_id";
                $params[':cliente_id'] = $_GET['cliente_id'];
            }

            // Filtros generales
            if (!empty($_GET['fecha'])) {
                $whereSQL .= " AND c.fecha = :fecha";
                $params[':fecha'] = $_GET['fecha'];
            }
            if (!empty($_GET['estado']) && $_GET['estado'] != 'todas') {
                $whereSQL .= " AND c.estado = :estado";
                $params[':estado'] = $_GET['estado'];
            }
            if (!empty($_GET['telefono'])) {
                $whereSQL .= " AND c.telefono_cliente LIKE :telefono";
                $params[':telefono'] = '%' . $_GET['telefono'] . '%';
            }
            if (!empty($_GET['nombre'])) {
                $whereSQL .= " AND (c.nombre_cliente LIKE :nombre OR c.apellido_cliente LIKE :nombre)";
                $params[':nombre'] = '%' . $_GET['nombre'] . '%';
            }
            if (!empty($_GET['dni'])) {
                $whereSQL .= " AND cli.dni_ruc = :dni";
                $params[':dni'] = $_GET['dni'];
            }

            // 1. Contar TOTAL de registros
            $sqlCount = "SELECT COUNT(*) as total 
                         FROM citas c 
                         LEFT JOIN clientes cli ON c.cliente_id = cli.id
                         $whereSQL";
            $stmtCount = $conn->prepare($sqlCount);
            $stmtCount->execute($params);
            $totalRecords = $stmtCount->fetch(PDO::FETCH_ASSOC)['total'];
            $totalPages = ceil($totalRecords / $limit);

            // 2. Obtener datos PAGINADOS con MÚLTIPLES SERVICIOS (GROUP_CONCAT)
            $sql = "SELECT 
                        c.*, 
                        cli.dni_ruc,
                        -- Concatenamos Nombres
                        GROUP_CONCAT(s.nombre SEPARATOR ', ') as servicios_nombres,
                        -- Concatenamos IDs (Para que el JS sepa qué marcar al editar)
                        GROUP_CONCAT(s.id SEPARATOR ',') as servicios_ids,
                        -- Sumamos precios
                        SUM(cd.precio_al_momento) as suma_automatica
                    FROM citas c
                    LEFT JOIN clientes cli ON c.cliente_id = cli.id
                    LEFT JOIN citas_detalles cd ON c.id = cd.cita_id
                    LEFT JOIN servicios s ON cd.servicio_id = s.id
                    $whereSQL
                    GROUP BY c.id  -- Agrupar por cita
                    ORDER BY c.fecha DESC, c.hora DESC
                    LIMIT $start, $limit";
            
            $stmt = $conn->prepare($sql);
            $stmt->execute($params);
            $resultados = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Formatear respuesta
            $citas = [];
            foreach ($resultados as $fila) {
                $fila['servicio_solicitado'] = $fila['servicios_nombres'] ?? 'Sin servicios';
                // Si el admin puso un precio manual, se usa ese. Si no, la suma automática.
                $fila['precio_mostrar'] = ($fila['precio_final'] > 0.00) ? $fila['precio_final'] : ($fila['suma_automatica'] ?? 0);
                $citas[] = $fila;
            }
            
            echo json_encode([
                'data' => $citas,
                'pagination' => [
                    'current_page' => $page,
                    'total_pages' => $totalPages,
                    'total_records' => $totalRecords
                ]
            ]);

        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode(['error' => 'Error BD: ' . $e->getMessage()]);
        }
        break;

    case 'PUT': // Actualizar Cita
         try {
            $conn->beginTransaction();

            // 1. Actualizar datos básicos y PRECIO FINAL
            $sql = "UPDATE citas SET 
                        nombre_cliente = :nom, 
                        apellido_cliente = :ape, 
                        telefono_cliente = :tel, 
                        fecha = :fec, 
                        hora = :hor, 
                        estado = :est, 
                        precio_final = :prec
                    WHERE id = :id";
            
            $stmt = $conn->prepare($sql);
            $stmt->execute([
                'nom' => $data['nombre_cliente'], 
                'ape' => $data['apellido_cliente'],
                'tel' => $data['telefono_cliente'], 
                'fec' => $data['fecha'],
                'hor' => $data['hora'], 
                'est' => $data['estado'],
                'prec' => $data['precio_final'], 
                'id' => $data['id']
            ]);

            // 2. Actualizar Servicios (Borrar viejos -> Insertar nuevos)
            if (isset($data['servicios']) && is_array($data['servicios'])) {
                // A. Borrar detalles actuales
                $conn->prepare("DELETE FROM citas_detalles WHERE cita_id = ?")->execute([$data['id']]);

                // B. Insertar los nuevos
                $stmtDet = $conn->prepare("INSERT INTO citas_detalles (cita_id, servicio_id, precio_al_momento) VALUES (?, ?, (SELECT precio FROM servicios WHERE id = ?))");
                
                foreach ($data['servicios'] as $sid) {
                    $stmtDet->execute([$data['id'], $sid, $sid]);
                }
            }

            $conn->commit();
            echo json_encode(['message' => 'Cita actualizada correctamente.']);

        } catch (Exception $e) { 
            $conn->rollBack();
            http_response_code(500); 
            echo json_encode(['error' => $e->getMessage()]); 
        }
        break;

    case 'DELETE': // Cancelar Cita
        try {
            $id = $_GET['id'] ?? null;
            if (!$id) { http_response_code(400); echo json_encode(['error' => 'ID faltante']); exit; }
            
            $sql = "UPDATE citas SET estado = 'cancelada' WHERE id = :id";
            $stmt = $conn->prepare($sql);
            $stmt->execute(['id' => $id]);
            echo json_encode(['message' => 'Cita cancelada.']);
        } catch (PDOException $e) { 
            http_response_code(500); 
            echo json_encode(['error' => $e->getMessage()]); 
        }
        break;

    default:
        http_response_code(405);
        echo json_encode(['error' => 'Método no permitido']);
        break;
}
?>