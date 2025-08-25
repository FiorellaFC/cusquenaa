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
        listarServicios($conn);
        break;

    case 'registrar':
        registrarServicio($conn);
        break;

    case 'modificar':
        modificarServicio($conn);
        break;

    case 'eliminar':
        eliminarServicio($conn);
        break;

    default:
        echo json_encode(["success" => false, "message" => "Acción no válida"]);
}

function listarServicios($conn) {
    $buscar = $_GET['buscar'] ?? '';
    $buscar = "%$buscar%";

    $sql = "SELECT * FROM servicios 
            WHERE nombre_servicio LIKE ? 
               OR tipo_servicio LIKE ? 
               OR estado LIKE ?
            ORDER BY id_servicio ASC";

    $stmt = $conn->prepare($sql);
    $stmt->execute([$buscar, $buscar, $buscar]);

    $servicios = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode($servicios);
}


function registrarServicio($conn) {
    $nombre = $_POST['nombre'] ?? '';
    $tipo = $_POST['tipoServicio'] ?? '';
    $precio = $_POST['precioUnitario'] ?? 0;
    $cantidad = $_POST['cantidad'] ?? 0;
    $fecha = $_POST['fechaRegistro'] ?? '';
    $estado = $_POST['estado'] ?? 'Activo';

    $sql = "INSERT INTO servicios (nombre_servicio, tipo_servicio, precio_unitario, cantidad, fecha_registro, estado)
            VALUES (?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $ok = $stmt->execute([$nombre, $tipo, $precio, $cantidad, $fecha, $estado]);

    echo json_encode(["success" => $ok]);
}

function modificarServicio($conn) {
    $id = $_POST['id'] ?? '';
    $nombre = $_POST['nombre'] ?? '';
    $tipo = $_POST['tipoServicio'] ?? '';
    $precio = $_POST['precioUnitario'] ?? 0;
    $cantidad = $_POST['cantidad'] ?? 0;
    $fecha = $_POST['fechaRegistro'] ?? '';
    $estado = $_POST['estado'] ?? 'Activo';

    if (!$id) {
        echo json_encode(["success" => false, "message" => "ID no recibido"]);
        return;
    }

    $sql = "UPDATE servicios SET nombre_servicio=?, tipo_servicio=?, precio_unitario=?, cantidad=?, fecha_registro=?, estado=? WHERE id_servicio=?";
    $stmt = $conn->prepare($sql);
    $ok = $stmt->execute([$nombre, $tipo, $precio, $cantidad, $fecha, $estado, $id]);

    echo json_encode(["success" => $ok, "message" => $ok ? "Modificado correctamente" : $stmt->errorInfo()]);
}

function eliminarServicio($conn) {
    $id = $_GET['id'] ?? '';
    $sql = "UPDATE servicios SET estado='Inactivo' WHERE id_servicio=?";
    $stmt = $conn->prepare($sql);
    $ok = $stmt->execute([$id]);

    echo json_encode(["success" => $ok]);
}
