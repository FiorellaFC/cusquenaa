<?php
// backend/api/controllers/vista_gestion_dominical/listar_pagos.php

require_once __DIR__ . '/../../../includes/db.php';
require_once __DIR__ . '/../../../includes/auth.php';

header('Content-Type: application/json');

try {
    verificarPermiso(['Administrador', 'Secretaria']);

    // Asegúrate de que el ID del dominical se envía en la URL
    $dominical_id = isset($_GET['dominical_id']) ? (int)$_GET['dominical_id'] : 0;

    if ($dominical_id === 0) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'ID de dominical no proporcionado.']);
        exit();
    }

    // Consulta para obtener los pagos asociados a un dominical_id
    $sql = "SELECT id, dominical_id, fecha_pago, monto_pagado, fecha_creacion
            FROM pagos_dominical
            WHERE dominical_id = :dominical_id
            ORDER BY fecha_pago DESC, fecha_creacion DESC"; 

    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':dominical_id', $dominical_id, PDO::PARAM_INT);
    $stmt->execute();
    $pagos = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode(['success' => true, 'pagos' => $pagos]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Error al listar pagos: ' . $e->getMessage()]);
}
?>