<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') exit(0);
header('Content-Type: application/json');

require_once '../../includes/db.php';

if ($conn === null) {
    http_response_code(500);
    echo json_encode(["success" => false, "message" => "Error de conexión a la base de datos"]);
    exit;
}

$accion = $_REQUEST['accion'] ?? '';

switch ($accion) {
    case 'listar':
        listarBalances($conn);
        break;
    case 'registrar':
        registrarBalance($conn);
        break;
    case 'modificar':
        modificarBalance($conn);
        break;
    case 'eliminar':
        eliminarBalance($conn);
        break;
    default:
        echo json_encode(["success" => false, "message" => "Acción no válida"]);
}

// LISTAR BALANCES (con filtros por nombre, mes y año)
function listarBalances($conn) {
    $nombre = $_GET['buscarNombre'] ?? '';
    $mes = $_GET['buscarMes'] ?? '';
    $anio = $_GET['buscarAnio'] ?? '';

    $condiciones = [];
    $parametros = [];

    if ($nombre !== '') {
        $condiciones[] = "(nombre_descripcion LIKE ? OR tipo_balance LIKE ?)";
        $parametros[] = "%" . $nombre . "%";
        $parametros[] = "%" . $nombre . "%";
    }
    if ($mes !== '') {
        $condiciones[] = "mes = ?";
        $parametros[] = $mes;
    }
    if ($anio !== '') {
        $condiciones[] = "anio = ?";
        $parametros[] = $anio;
    }

    $sql = "SELECT * FROM balances_lubricentro";
    if (!empty($condiciones)) {
        $sql .= " WHERE " . implode(" AND ", $condiciones);
    }
    $sql .= " ORDER BY anio DESC, mes ASC";

    $stmt = $conn->prepare($sql);
    $stmt->execute($parametros);
    $balances = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode($balances);
}

// REGISTRAR BALANCE
function registrarBalance($conn) {
    $nombre = $_POST['nombre'] ?? '';
    $tipo = $_POST['tipoBalance'] ?? '';
    $mes = $_POST['mes'] ?? '';
    $anio = $_POST['anio'] ?? 0;
    $monto = $_POST['monto'] ?? 0;

    $sql = "INSERT INTO balances_lubricentro (nombre_descripcion, tipo_balance, mes, anio, monto)
            VALUES (?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $ok = $stmt->execute([$nombre, $tipo, $mes, $anio, $monto]);

    echo json_encode(["success" => $ok]);
}

// MODIFICAR BALANCE
function modificarBalance($conn) {
    $id = $_POST['balanceId'] ?? '';
    $nombre = $_POST['nombre'] ?? '';
    $tipo = $_POST['tipoBalance'] ?? '';
    $mes = $_POST['mes'] ?? '';
    $anio = $_POST['anio'] ?? 0;
    $monto = $_POST['monto'] ?? 0;

    if (!$id) {
        echo json_encode(["success" => false, "message" => "ID no recibido"]);
        return;
    }

    $sql = "UPDATE balances_lubricentro 
            SET nombre_descripcion=?, tipo_balance=?, mes=?, anio=?, monto=?
            WHERE id=?";
    $stmt = $conn->prepare($sql);
    $ok = $stmt->execute([$nombre, $tipo, $mes, $anio, $monto, $id]);

    echo json_encode([
        "success" => $ok,
        "message" => $ok ? "Modificado correctamente" : "Error al modificar"
    ]);
}

// ELIMINAR BALANCE (puedes hacer eliminación lógica si agregas campo "estado")
function eliminarBalance($conn) {
    $id = $_GET['id'] ?? '';
    if (!$id) {
        echo json_encode(["success" => false, "message" => "ID no recibido"]);
        return;
    }

    $sql = "DELETE FROM balances_lubricentro WHERE id=?";
    $stmt = $conn->prepare($sql);
    $ok = $stmt->execute([$id]);

    echo json_encode(["success" => $ok]);
}
