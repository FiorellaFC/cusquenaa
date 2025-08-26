<?php
require_once 'conexion.php'; // tu archivo de conexión

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $tipo = $_POST['tipo'] ?? '';
    $fechaInicio = $_POST['fechaInicio'] ?? '';
    $fechaFin = $_POST['fechaFin'] ?? '';

    $data = [];

    if ($tipo === 'general') {
        // Aquí unificas todas las consultas
        $data['balances'] = obtenerDatos("SELECT * FROM balances WHERE fecha BETWEEN ? AND ?", [$fechaInicio, $fechaFin]);
        $data['gastos'] = obtenerDatos("SELECT * FROM gastos WHERE fecha BETWEEN ? AND ?", [$fechaInicio, $fechaFin]);
        $data['prestamos'] = obtenerDatos("SELECT * FROM prestamos WHERE fecha BETWEEN ? AND ?", [$fechaInicio, $fechaFin]);
        $data['recaudos'] = obtenerDatos("SELECT * FROM recaudos WHERE fecha BETWEEN ? AND ?", [$fechaInicio, $fechaFin]);
        $data['trabajadores'] = obtenerDatos("SELECT * FROM trabajadores WHERE fecha_ingreso BETWEEN ? AND ?", [$fechaInicio, $fechaFin]);
        // Dashboard no lo consultas directamente porque es más estadístico
    } elseif ($tipo === 'lubricentro') {
        $data['lubricentro'] = obtenerDatos("SELECT * FROM lubricentro WHERE fecha BETWEEN ? AND ?", [$fechaInicio, $fechaFin]);
    }

    echo json_encode($data);
}

function obtenerDatos($sql, $params = []) {
    global $pdo;
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}
