<?php
// backend/api/controllers/vista_gestion_dominical/eliminar_pago.php

require_once __DIR__ . '/../../../includes/db.php';
require_once __DIR__ . '/../../../includes/auth.php';

header('Content-Type: application/json');

// verificarPermiso(['Administrador', 'Secretaria']); // Si quieres restringir la eliminación

$data = json_decode(file_get_contents('php://input'), true);

if (empty($data['id'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'ID de pago no proporcionado para eliminar.']);
    exit();
}

$pago_id = (int)$data['id'];

try {
    // 1. Obtener el dominical_id antes de eliminar el pago para poder recalcular la diferencia
    $stmtGetDominicalId = $conn->prepare("SELECT dominical_id, monto_pagado FROM pagos_dominical WHERE id = :pago_id");
    $stmtGetDominicalId->bindParam(':pago_id', $pago_id, PDO::PARAM_INT);
    $stmtGetDominicalId->execute();
    $pagoData = $stmtGetDominicalId->fetch(PDO::FETCH_ASSOC);

    if (!$pagoData) {
        http_response_code(404);
        echo json_encode(['success' => false, 'error' => 'Pago no encontrado.']);
        exit();
    }

    $dominical_id = $pagoData['dominical_id'];
    $monto_eliminado = $pagoData['monto_pagado'];

    // 2. Eliminar el pago
    $stmtDelete = $conn->prepare("DELETE FROM pagos_dominical WHERE id = :id");
    $stmtDelete->bindParam(':id', $pago_id, PDO::PARAM_INT);

    if (!$stmtDelete->execute()) {
        throw new Exception("Error al eliminar el pago: " . implode(" ", $stmtDelete->errorInfo()));
    }

    // 3. Recalcular la 'diferencia' y el 'estado' en la tabla 'dominical'
    // Obtener el monto dominical original y el total pagado actual (después de la eliminación)
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
            throw new Exception("Error al actualizar la diferencia y estado del dominical después de eliminar pago: " . implode(" ", $stmtUpdateDominical->errorInfo()));
        }
    }

    echo json_encode(['success' => true, 'message' => 'Pago eliminado y dominical actualizado exitosamente.']);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
?>