<?php
// backend/api/controllers/vista_gestion_prestamos/registrar_pago.php

// ESTAS RUTAS SON CLAVE. DEBEN COINCIDIR CON LAS QUE FUNCIONAN EN DOMINICALES.
require_once __DIR__ . '/../../../includes/db.php'; 
require_once __DIR__ . '/../../../includes/auth.php'; 

header('Content-Type: application/json');

// verificarPermiso(['Administrador', 'Secretaria']); // Descomenta si necesitas esta capa de seguridad

$data = json_decode(file_get_contents('php://input'), true);

// Validar campos obligatorios para el pago de PRÉSTAMO
// Asegúrate que 'id_prestamo' es el nombre que tu JS envía
if (empty($data['id_prestamo']) || empty($data['fecha_pago']) || !isset($data['monto_pagado']) || !is_numeric($data['monto_pagado']) || $data['monto_pagado'] <= 0) {
    http_response_code(400); // Bad Request
    echo json_encode(['success' => false, 'error' => 'Datos incompletos o inválidos para registrar el pago.']);
    exit();
}

// Asegúrate que estos nombres de variables coinciden con los de la tabla y tu JS
$prestamo_id = (int)$data['id_prestamo'];
$fecha_pago = $data['fecha_pago'];
$monto_pagado = (float)$data['monto_pagado'];

try {
    // 1. Registrar el nuevo pago en la tabla 'pagos_prestamos'
    // 'fecha_creacion' se llenará automáticamente por DEFAULT CURRENT_TIMESTAMP() en la DB.
    $stmtPago = $conn->prepare("INSERT INTO pagos_prestamos (prestamo_id, fecha_pago, monto_pagado) VALUES (:prestamo_id, :fecha_pago, :monto_pagado)");
    $stmtPago->bindParam(':prestamo_id', $prestamo_id, PDO::PARAM_INT);
    $stmtPago->bindParam(':fecha_pago', $fecha_pago);
    $stmtPago->bindParam(':monto_pagado', $monto_pagado);

    if (!$stmtPago->execute()) {
        throw new Exception("Error al registrar el pago: " . implode(" ", $stmtPago->errorInfo()));
    }

  
    $stmtPrestamo = $conn->prepare("SELECT p.monto_deuda, SUM(COALESCE(pago.monto_pagado, 0)) as total_pagado
                                    FROM prestamos p
                                    LEFT JOIN pagos_prestamos pago ON p.id = pago.prestamo_id
                                    WHERE p.id = :prestamo_id
                                    GROUP BY p.monto_deuda");
    $stmtPrestamo->bindParam(':prestamo_id', $prestamo_id, PDO::PARAM_INT);
    $stmtPrestamo->execute();
    $prestamoData = $stmtPrestamo->fetch(PDO::FETCH_ASSOC);

    if ($prestamoData) {
        $monto_deuda_original = (float)$prestamoData['monto_deuda'];
        $total_pagado_acumulado = (float)($prestamoData['total_pagado'] ?? 0.0);

        $nuevo_saldo_pendiente = $monto_deuda_original - $total_pagado_acumulado;

        // Asegurarse de que el saldo pendiente no sea negativo
        if ($nuevo_saldo_pendiente < 0) {
            $nuevo_saldo_pendiente = 0;
        }

        // Determinar el nuevo estado del préstamo
        // Asumo que 'activo' y 'inactivo' son los estados deseados.
        $nuevo_estado = ($nuevo_saldo_pendiente <= 0) ? 'inactivo' : 'activo'; 

        // Actualizar la tabla prestamos
        $stmtUpdatePrestamo = $conn->prepare("UPDATE prestamos SET saldo_pendiente = :saldo_pendiente, estado = :estado WHERE id = :id");
        $stmtUpdatePrestamo->bindParam(':saldo_pendiente', $nuevo_saldo_pendiente);
        $stmtUpdatePrestamo->bindParam(':estado', $nuevo_estado);
        $stmtUpdatePrestamo->bindParam(':id', $prestamo_id, PDO::PARAM_INT);
        
        if (!$stmtUpdatePrestamo->execute()) {
            throw new Exception("Error al actualizar el saldo y estado del préstamo: " . implode(" ", $stmtUpdatePrestamo->errorInfo()));
        }
    } else {
        throw new Exception("Préstamo no encontrado para actualizar el saldo.");
    }

    echo json_encode(['success' => true, 'message' => 'Pago registrado y préstamo actualizado exitosamente.']);

} catch (Exception $e) {
    http_response_code(500); // Internal Server Error
    error_log("Error en registrar_pago.php (prestamos): " . $e->getMessage()); // Log del error para depuración
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
?>