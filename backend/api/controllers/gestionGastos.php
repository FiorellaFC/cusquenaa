<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json");
header("Access-Control-Allow-Methods: POST");

$conexion = new PDO("mysql:host=localhost;dbname=la_cusquena;charset=utf8", "root", "");

$data = json_decode(file_get_contents("php://input"), true);
$accion = $data['accion'] ?? '';

switch ($accion) {
  case "listar":
    $sql = "SELECT * FROM gastos ORDER BY fecha DESC";
    $stmt = $conexion->prepare($sql);
    $stmt->execute();
    echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
    break;

  case "buscar":
    $descripcion = '%' . ($data['descripcion'] ?? '') . '%';
    $inicio = $data['inicio'] ?? '1900-01-01';
    $fin = $data['fin'] ?? '2100-12-31';
    $sql = "SELECT * FROM gastos WHERE descripcion LIKE ? AND fecha BETWEEN ? AND ? ORDER BY fecha DESC";
    $stmt = $conexion->prepare($sql);
    $stmt->execute([$descripcion, $inicio, $fin]);
    echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
    break;

  case "agregar":
    $sql = "INSERT INTO gastos (descripcion, tipo, monto, fecha, detalle) VALUES (?, ?, ?, ?, ?)";
    $stmt = $conexion->prepare($sql);
    $stmt->execute([
      $data['descripcion'],
      $data['tipo'],
      $data['monto'],
      $data['fecha'],
      $data['detalle']
    ]);
    echo json_encode(["success" => true]);
    break;

  case "editar":
    $sql = "UPDATE gastos SET descripcion=?, tipo=?, monto=?, fecha=?, detalle=? WHERE id=?";
    $stmt = $conexion->prepare($sql);
    $stmt->execute([
      $data['descripcion'],
      $data['tipo'],
      $data['monto'],
      $data['fecha'],
      $data['detalle'],
      $data['id']
    ]);
    echo json_encode(["success" => true]);
    break;

  case "eliminar":
    $stmt = $conexion->prepare("DELETE FROM gastos WHERE id=?");
    $stmt->execute([$data['id']]);
    echo json_encode(["success" => true]);
    break;

  case "obtener":
    $stmt = $conexion->prepare("SELECT * FROM gastos WHERE id=?");
    $stmt->execute([$data['id']]);
    echo json_encode($stmt->fetch(PDO::FETCH_ASSOC));
    break;

  default:
    echo json_encode(["error" => "Acción no válida"]);
    break;
}
