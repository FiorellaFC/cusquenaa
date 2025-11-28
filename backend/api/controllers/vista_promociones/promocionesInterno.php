<?php
// backend/api/controllers/promocionesInterno.php
header("Content-Type: application/json; charset=utf-8");
require_once "../../../includes/db.php";  

$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case 'GET':
        $stmt = $conn->query("SELECT * FROM promociones ORDER BY id DESC");
        $promos = $stmt->fetchAll();
        echo json_encode($promos);
        break;

    case 'POST':
        $data = json_decode(file_get_contents("php://input"), true);

        $titulo      = $conn->quote($data['titulo']);
        $descripcion = $conn->quote($data['descripcion']);
        $precio      = floatval($data['precio']);
        $badge       = isset($data['badge']) ? $conn->quote($data['badge']) : "NULL";
        $icono       = $conn->quote($data['icono']);
        $activa      = $data['activa'] ? 1 : 0;

        if ($data['id'] > 0) {
            // EDITAR
            $id = intval($data['id']);
            $sql = "UPDATE promociones SET 
                    titulo=$titulo, 
                    descripcion=$descripcion, 
                    precio=$precio, 
                    badge=$badge, 
                    icono=$icono, 
                    activa=$activa 
                    WHERE id=$id";
        } else {
            // CREAR
            $sql = "INSERT INTO promociones 
                    (titulo, descripcion, precio, badge, icono, activa) 
                    VALUES ($titulo, $descripcion, $precio, $badge, $icono, $activa)";
        }

        $success = $conn->exec($sql);
        echo json_encode(["success" => $success !== false]);
        break;

    case 'DELETE':
        $data = json_decode(file_get_contents("php://input"), true);
        $id = intval($data['id']);
        $sql = "DELETE FROM promociones WHERE id = $id";
        $success = $conn->exec($sql);
        echo json_encode(["success" => $success !== false]);
        break;
}
?>