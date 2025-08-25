<?php
// backend/api/controllers/vista_gestion_prestamos/eliminar_pago.php

require_once __DIR__ . '/../../../includes/db.php';
require_once __DIR__ . '/../../../includes/auth.php';

header('Content-Type: application/json');

$data = json_decode(file_get_contents('php://input'), true);

// --- AGREGAR ESTO PARA DEPURACIÓN (déjalo mientras depuras, luego puedes quitarlo) ---
error_log("Datos recibidos para eliminar pago: " . print_r($data, true));
// --- FIN DEPURACIÓN ---

if (empty($data['id'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'ID de pago no proporcionado para eliminar.']);
    exit();
}

$pago_id = (int)$data['id'];

// --- AGREGAR ESTO PARA DEPURACIÓN (déjalo mientras depuras, luego puedes quitarlo) ---
error_log("ID de pago a eliminar (después de cast): " . $pago_id);
// --- FIN DEPURACIÓN ---

try {
    // 1. Obtener el prestamo_id del pago a eliminar
    // Esto es crucial para saber a qué préstamo pertenece el pago que se va a borrar
    $stmtGetPrestamoId = $conn->prepare("SELECT prestamo_id FROM pagos_prestamos WHERE id = :pago_id");
    $stmtGetPrestamoId->bindParam(':pago_id', $pago_id, PDO::PARAM_INT);
    $stmtGetPrestamoId->execute();
    $pagoData = $stmtGetPrestamoId->fetch(PDO::FETCH_ASSOC);

    if (!$pagoData) {
        http_response_code(404);
        echo json_encode(['success' => false, 'error' => 'Pago no encontrado.']);
        exit();
    }

    $prestamo_id = $pagoData['prestamo_id'];

    // 2. Eliminar el pago
    // Esta parte ya está correcta: elimina SOLO el pago con el ID especificado
    $stmtDelete = $conn->prepare("DELETE FROM pagos_prestamos WHERE id = :id");
    $stmtDelete->bindParam(':id', $pago_id, PDO::PARAM_INT);

    if (!$stmtDelete->execute()) {
        throw new Exception("Error al eliminar el pago: " . implode(" ", $stmtDelete->errorInfo()));
    }

    // 3. Recalcular el 'saldo_pendiente' y el 'estado' en la tabla 'prestamos'
    // Obtener el monto_deuda original y el TOTAL pagado ACUMULADO (después de la eliminación)
    // La unión y el WHERE son CRÍTICOS aquí.
    $stmtRecalculate = $conn->prepare("SELECT p.monto_deuda, SUM(COALESCE(pa.monto_pagado, 0)) as total_pagado_acumulado
                                       FROM prestamos p  -- Alias 'p' para la tabla principal prestamos
                                       LEFT JOIN pagos_prestamos pa ON p.id = pa.prestamo_id -- Alias 'pa' para pagos_prestamos
                                       WHERE p.id = :prestamo_id  -- ¡Asegura que se filtra por el ID del PRÉSTAMO principal!
                                       GROUP BY p.monto_deuda");
    $stmtRecalculate->bindParam(':prestamo_id', $prestamo_id, PDO::PARAM_INT);
    $stmtRecalculate->execute();
    $prestamoRecalcData = $stmtRecalculate->fetch(PDO::FETCH_ASSOC);

    if ($prestamoRecalcData) {
        $monto_deuda_original = (float)$prestamoRecalcData['monto_deuda'];
        $total_pagado_actual = (float)$prestamoRecalcData['total_pagado_acumulado'];
        
        $nuevo_saldo_pendiente = $monto_deuda_original - $total_pagado_actual;
        
        if ($nuevo_saldo_pendiente < 0) {
            $nuevo_saldo_pendiente = 0;
        }

        $nuevo_estado = ($nuevo_saldo_pendiente <= 0) ? 'inactivo' : 'activo';

        $stmtUpdatePrestamo = $conn->prepare("UPDATE prestamos SET saldo_pendiente = :saldo_pendiente, estado = :estado WHERE id = :id");
        $stmtUpdatePrestamo->bindParam(':saldo_pendiente', $nuevo_saldo_pendiente);
        $stmtUpdatePrestamo->bindParam(':estado', $nuevo_estado);
        $stmtUpdatePrestamo->bindParam(':id', $prestamo_id, PDO::PARAM_INT);
        
        if (!$stmtUpdatePrestamo->execute()) {
            throw new Exception("Error al actualizar el saldo y estado del préstamo después de eliminar pago: " . implode(" ", $stmtUpdatePrestamo->errorInfo()));
        }
    } else {
        throw new Exception("Préstamo principal no encontrado después de eliminar el pago asociado.");
    }

    echo json_encode(['success' => true, 'message' => 'Pago eliminado y préstamo actualizado exitosamente.']);

} catch (Exception $e) {
    http_response_code(500);
    error_log("Error en eliminar_pago.php (prestamos): " . $e->getMessage());
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}

$stmtDelete = $conn->prepare("DELETE FROM pagos_prestamos WHERE id = :id");
$stmtDelete->bindParam(':id', $pago_id, PDO::PARAM_INT);

// --- AÑADE ESTA LÍNEA TEMPORALMENTE ---
error_log("DEBUG: Ejecutando DELETE para pagos_prestamos con ID: " . $pago_id);
// ------------------------------------

if (!$stmtDelete->execute()) {
    throw new Exception("Error al eliminar el pago: " . implode(" ", $stmtDelete->errorInfo()));
}
?>