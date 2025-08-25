<?php
// backend/api/controllers/vista_gestion_dominical/actualizar_pago.php

require_once __DIR__ . '/../../../includes/db.php';
require_once __DIR__ . '/../../../includes/auth.php';

header('Content-Type: application/json');

// verificarPermiso(['Administrador', 'Secretaria']); // Restringe el permiso si es necesario

$data = json_decode(file_get_contents('php://input'), true);

// Validar campos obligatorios
if (empty($data['id']) || empty($data['dominical_id']) || empty($data['fecha_pago']) || !isset($data['monto_pagado'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Datos incompletos para actualizar el pago.']);
    exit();
}

$pago_id = (int)$data['id'];
$dominical_id = (int)$data['dominical_id'];
$fecha_pago = $data['fecha_pago'];
$monto_pagado = (float)$data['monto_pagado'];

try {
    // 1. Actualizar el pago en la tabla 'pagos_dominical'
    $stmtUpdatePago = $conn->prepare("UPDATE pagos_dominical SET fecha_pago = :fecha_pago, monto_pagado = :monto_pagado WHERE id = :pago_id AND dominical_id = :dominical_id");
    $stmtUpdatePago->bindParam(':fecha_pago', $fecha_pago);
    $stmtUpdatePago->bindParam(':monto_pagado', $monto_pagado);
    $stmtUpdatePago->bindParam(':pago_id', $pago_id, PDO::PARAM_INT);
    $stmtUpdatePago->bindParam(':dominical_id', $dominical_id, PDO::PARAM_INT);

    if (!$stmtUpdatePago->execute()) {
        throw new Exception("Error al actualizar el pago: " . implode(" ", $stmtUpdatePago->errorInfo()));
    }

    // 2. Recalcular la 'diferencia' y el 'estado' en la tabla 'dominical'
    // Obtener el monto dominical original y el total pagado actual
    $stmtRecalculate = $conn->prepare("SELECT d.monto_dominical, SUM(COALESCE(p.monto_pagado, 0)) as total_pagado_acumulado
                                       FROM dominical d
                                       LEFT JOIN pagos_dominical p ON d.id = p.dominical_id
                                       WHERE d.id = :dominical_id
                                       GROUP BY d.monto_dominical");
    $stmtRecalculate->bindParam(':dominical_id', $dominical_id, PDO::PARAM_INT);
    $stmtRecalculate->execute();
    $dominicalRecalcData = $stmtRecalculate->fetch(PDO::FETCH_ASSOC);

    if ($dominicalRecalcData) {
        $monto_dominical_original = (float)$dominicalRecalcData['monto_dominical'];
        $total_pagado_actual = (float)$dominicalRecalcData['total_pagado_acumulado'];
        
        $nueva_diferencia = $monto_dominical_original - $total_pagado_actual;
        $nuevo_estado = ($nueva_diferencia <= 0) ? 'Pagado' : 'Pendiente';

        // Actualizar la tabla dominical
        $stmtUpdateDominical = $conn->prepare("UPDATE dominical SET diferencia = :diferencia, estado = :estado WHERE id = :id");
        $stmtUpdateDominical->bindParam(':diferencia', $nueva_diferencia);
        $stmtUpdateDominical->bindParam(':estado', $nuevo_estado);
        $stmtUpdateDominical->bindParam(':id', $dominical_id, PDO::PARAM_INT);
        
        if (!$stmtUpdateDominical->execute()) {
            throw new Exception("Error al actualizar la diferencia y estado del dominical después de editar pago: " . implode(" ", $stmtUpdateDominical->errorInfo()));
        }
    }

    echo json_encode(['success' => true, 'message' => 'Pago actualizado y dominical recalculado exitosamente.']);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
?>