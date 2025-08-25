<?php
// backend/api/controllers/vista_gestion_dominical/registrar_pago.php

require_once __DIR__ . '/../../../includes/db.php';
require_once __DIR__ . '/../../../includes/auth.php';

header('Content-Type: application/json');

// verificarPermiso(['Administrador', 'Secretaria']); // Si quieres que solo administradores y secretarias puedan registrar pagos

$data = json_decode(file_get_contents('php://input'), true);

// Validar campos obligatorios para el pago
if (empty($data['dominical_id']) || empty($data['fecha_pago']) || !isset($data['monto_pagado'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Datos incompletos para registrar el pago.']);
    exit();
}

$dominical_id = (int)$data['dominical_id'];
$fecha_pago = $data['fecha_pago'];
$monto_pagado = (float)$data['monto_pagado'];

try {
    // 1. Registrar el nuevo pago en la tabla 'pagos_dominical'
    $stmtPago = $conn->prepare("INSERT INTO pagos_dominical (dominical_id, fecha_pago, monto_pagado) VALUES (:dominical_id, :fecha_pago, :monto_pagado)");
    $stmtPago->bindParam(':dominical_id', $dominical_id, PDO::PARAM_INT);
    $stmtPago->bindParam(':fecha_pago', $fecha_pago);
    $stmtPago->bindParam(':monto_pagado', $monto_pagado);

    if (!$stmtPago->execute()) {
        throw new Exception("Error al registrar el pago: " . implode(" ", $stmtPago->errorInfo()));
    }

    // 2. Actualizar la 'diferencia' y el 'estado' en la tabla 'dominical'
    // Primero, obtener el monto dominical original y la diferencia actual
    $stmtDominical = $conn->prepare("SELECT monto_dominical, SUM(pago.monto_pagado) as total_pagado
                                    FROM dominical d
                                    LEFT JOIN pagos_dominical pago ON d.id = pago.dominical_id
                                    WHERE d.id = :dominical_id
                                    GROUP BY d.monto_dominical");
    $stmtDominical->bindParam(':dominical_id', $dominical_id, PDO::PARAM_INT);
    $stmtDominical->execute();
    $dominicalData = $stmtDominical->fetch(PDO::FETCH_ASSOC);

    if ($dominicalData) {
        $monto_dominical_original = (float)$dominicalData['monto_dominical'];
        $total_pagado_acumulado = (float)$dominicalData['total_pagado'];

        $nueva_diferencia = $monto_dominical_original - $total_pagado_acumulado;
        $nuevo_estado = ($nueva_diferencia <= 0) ? 'Pagado' : 'Pendiente';

        // Actualizar la tabla dominical
        $stmtUpdateDominical = $conn->prepare("UPDATE dominical SET diferencia = :diferencia, estado = :estado WHERE id = :id");
        $stmtUpdateDominical->bindParam(':diferencia', $nueva_diferencia);
        $stmtUpdateDominical->bindParam(':estado', $nuevo_estado);
        $stmtUpdateDominical->bindParam(':id', $dominical_id, PDO::PARAM_INT);
        
        if (!$stmtUpdateDominical->execute()) {
            throw new Exception("Error al actualizar la diferencia y estado del dominical: " . implode(" ", $stmtUpdateDominical->errorInfo()));
        }
    } else {
        throw new Exception("Dominical no encontrado para actualizar la diferencia.");
    }

    echo json_encode(['success' => true, 'message' => 'Pago registrado y dominical actualizado exitosamente.']);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
?>