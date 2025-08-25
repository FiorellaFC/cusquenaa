<?php
// backend/api/controllers/vista_gestion_prestamos/listar_pagos.php

// Las rutas deben ser idénticas a las que funcionan en tus scripts de dominicales
require_once __DIR__ . '/../../../includes/db.php'; 
require_once __DIR__ . '/../../../includes/auth.php'; 

header('Content-Type: application/json');

try {
    // verificaPermiso(['Administrador', 'Secretaria']); // Descomenta si lo necesitas aquí

    // Asegúrate de que el ID del préstamo se envía en la URL
    $prestamo_id = isset($_GET['id_prestamo']) ? (int)$_GET['id_prestamo'] : 0;

    if ($prestamo_id === 0) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'ID de préstamo no proporcionado para listar pagos.']);
        exit();
    }

    // Consulta para obtener los pagos asociados a un prestamo_id
    $sql = "SELECT id, prestamo_id, fecha_pago, monto_pagado, fecha_creacion
            FROM pagos_prestamos
            WHERE prestamo_id = :prestamo_id
            ORDER BY fecha_pago DESC, fecha_creacion DESC"; 

    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':prestamo_id', $prestamo_id, PDO::PARAM_INT);
    $stmt->execute();
    $pagos = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode(['success' => true, 'pagos' => $pagos]);

} catch (Exception $e) {
    http_response_code(500);
    error_log("Error al listar pagos de préstamo: " . $e->getMessage()); // Para registro de errores
    echo json_encode(['success' => false, 'error' => 'Error al listar pagos de préstamo: ' . $e->getMessage()]);
}
?>