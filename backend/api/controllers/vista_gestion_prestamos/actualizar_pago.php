<?php
// backend/api/controllers/vista_gestion_prestamos/actualizar_pago.php

require_once __DIR__ . '/../../../includes/db.php'; // Asume que db.php define $conn
require_once __DIR__ . '/../../../backend/includes/auth.php'; // Incluye tu sistema de autenticación/permisos

header('Content-Type: application/json');

// verificarPermiso(['Administrador', 'Secretaria']); // Descomenta si necesitas esta capa de seguridad

$data = json_decode(file_get_contents('php://input'), true);

// Validar campos obligatorios
if (empty($data['id_pago']) || empty($data['id_prestamo']) || empty($data['fecha_pago']) || !isset($data['monto_pagado']) || !is_numeric($data['monto_pagado']) || $data['monto_pagado'] <= 0) {
    http_response_code(400); // Bad Request
    echo json_encode(['success' => false, 'error' => 'Datos incompletos o inválidos para actualizar el pago.']);
    exit();
}

$pago_id = (int)$data['id_pago'];
$prestamo_id = (int)$data['id_prestamo'];
$fecha_pago = $data['fecha_pago'];
$monto_pagado = (float)$data['monto_pagado'];

try {
    $conn->beginTransaction(); // Iniciar una transacción para asegurar la consistencia

    // 1. Actualizar el pago en la tabla 'pagos_prestamos'
    // Se incluye prestamo_id en el WHERE para mayor seguridad, asegurando que el pago pertenezca a ese préstamo
    $stmtUpdatePago = $conn->prepare("UPDATE pagos_prestamos SET fecha_pago = :fecha_pago, monto_pagado = :monto_pagado WHERE id = :pago_id AND prestamo_id = :prestamo_id");
    $stmtUpdatePago->bindParam(':fecha_pago', $fecha_pago);
    $stmtUpdatePago->bindParam(':monto_pagado', $monto_pagado);
    $stmtUpdatePago->bindParam(':pago_id', $pago_id, PDO::PARAM_INT);
    $stmtUpdatePago->bindParam(':prestamo_id', $prestamo_id, PDO::PARAM_INT); // Bind para seguridad

    if (!$stmtUpdatePago->execute()) {
        $conn->rollBack(); // Si falla la actualización del pago, revertir la transacción
        throw new Exception("Error al actualizar el pago: " . implode(" ", $stmtUpdatePago->errorInfo()));
    }

   
    if ($stmtUpdatePago->rowCount() === 0) {
        // Esto podría indicar que el pago_id no existe o no pertenece a ese prestamo_id
        $conn->rollBack();
        http_response_code(404); // Not Found
        echo json_encode(['success' => false, 'error' => 'Pago no encontrado o no pertenece al préstamo especificado. No se realizó la actualización.']);
        exit();
    }

    // 2. Recalcular el 'saldo_pendiente' y el 'estado' en la tabla 'prestamos'
    // Obtener el monto de deuda original del préstamo y sumar todos los pagos actuales
    $stmtRecalculate = $conn->prepare("SELECT p.monto_deuda, SUM(COALESCE(pp.monto_pagado, 0)) as total_pagado_acumulado
                                       FROM prestamos p
                                       LEFT JOIN pagos_prestamos pp ON p.id = pp.prestamo_id
                                       WHERE p.id = :prestamo_id
                                       GROUP BY p.monto_deuda
                                       FOR UPDATE"); // Bloquear la fila del préstamo para la actualización
    $stmtRecalculate->bindParam(':prestamo_id', $prestamo_id, PDO::PARAM_INT);
    $stmtRecalculate->execute();
    $prestamoRecalcData = $stmtRecalculate->fetch(PDO::FETCH_ASSOC);

    if ($prestamoRecalcData) {
        $monto_deuda_original = (float)$prestamoRecalcData['monto_deuda'];
        $total_pagado_actual = (float)$prestamoRecalcData['total_pagado_acumulado'];
        
        $nuevo_saldo_pendiente = $monto_deuda_original - $total_pagado_actual;

        // Asegurarse de que el saldo no sea negativo ni exceda el monto de deuda original
        if ($nuevo_saldo_pendiente < 0) {
            $nuevo_saldo_pendiente = 0;
        }
        if ($nuevo_saldo_pendiente > $monto_deuda_original) { // En caso de que se haya pagado de más
            $nuevo_saldo_pendiente = $monto_deuda_original;
        }

        // Determinar el nuevo estado del préstamo
        $nuevo_estado = ($nuevo_saldo_pendiente <= 0) ? 'inactivo' : 'activo';

        // Actualizar la tabla prestamos
        $stmtUpdatePrestamo = $conn->prepare("UPDATE prestamos SET saldo_pendiente = :saldo_pendiente, estado = :estado WHERE id = :id");
        $stmtUpdatePrestamo->bindParam(':saldo_pendiente', $nuevo_saldo_pendiente);
        $stmtUpdatePrestamo->bindParam(':estado', $nuevo_estado);
        $stmtUpdatePrestamo->bindParam(':id', $prestamo_id, PDO::PARAM_INT);
        
        if (!$stmtUpdatePrestamo->execute()) {
            $conn->rollBack(); // Si falla la actualización del préstamo, revertir
            throw new Exception("Error al actualizar el saldo y estado del préstamo después de editar pago: " . implode(" ", $stmtUpdatePrestamo->errorInfo()));
        }
    } else {
        $conn->rollBack(); // Revertir si el préstamo principal no fue encontrado
        http_response_code(404); // Not Found
        throw new Exception("Préstamo principal no encontrado para actualizar el saldo.");
    }

    $conn->commit(); // Confirmar la transacción si todo fue exitoso
    echo json_encode(['success' => true, 'message' => 'Pago actualizado y préstamo recalculado exitosamente.']);

} catch (Exception $e) {
    if ($conn->inTransaction()) {
        $conn->rollBack(); // Revertir la transacción si algo falla
    }
    http_response_code(500); // Internal Server Error
    error_log("Error en actualizar_pago.php: " . $e->getMessage()); // Log del error para depuración
    echo json_encode(['success' => false, 'error' => 'Error al actualizar pago: ' . $e->getMessage()]);
}
?>