<?php
require_once '../includes/conexion.php'; // tu archivo de conexión
header('Content-Type: application/json');

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    // Listar recaudos con filtros opcionales
    $tipo = $_GET['tipo'] ?? '';
    $from = $_GET['from'] ?? '';
    $to   = $_GET['to']   ?? '';

    $query = "SELECT r.*, u.usuario AS registrado_por 
              FROM recaudos r
              LEFT JOIN usuarios u ON r.usuario_id = u.id
              WHERE 1=1";
    $params = [];

    if (!empty($tipo)) {
        $query .= " AND r.tipo = ?";
        $params[] = $tipo;
    }
    if (!empty($from)) {
        $query .= " AND r.fecha >= ?";
        $params[] = $from;
    }
    if (!empty($to)) {
        $query .= " AND r.fecha <= ?";
        $params[] = $to;
    }

    $stmt = $pdo->prepare($query);
    $stmt->execute($params);
    echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
    exit;
}

if ($method === 'POST') {
    // Registrar un nuevo recaudo
    $input = json_decode(file_get_contents("php://input"), true);

    if (!$input) {
        echo json_encode(["error" => "Datos inválidos"]);
        exit;
    }

    // usuario actual (si lo guardas en sesión)
    session_start();
    $usuario_id = $_SESSION['id'] ?? null;

    $stmt = $pdo->prepare("INSERT INTO recaudos (tipo, cliente, fecha, monto, pagado, observacion, usuario_id) 
                           VALUES (?, ?, ?, ?, ?, ?, ?)");
    $ok = $stmt->execute([
        $input['tipo'],
        $input['cliente'],
        $input['fecha'],
        $input['monto'],
        $input['pagado'],
        $input['observacion'],
        $usuario_id
    ]);

    if ($ok) {
        echo json_encode(["id" => $pdo->lastInsertId()]);
    } else {
        echo json_encode(["error" => "No se pudo registrar"]);
    }
    exit;
}

http_response_code(405);
echo json_encode(["error" => "Método no permitido"]);
