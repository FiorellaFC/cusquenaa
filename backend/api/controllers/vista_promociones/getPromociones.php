<?php
// backend/api/getPromociones.php
header("Content-Type: application/json; charset=utf-8");
require_once "../../../includes/db.php"; 

$stmt = $conn->query("SELECT * FROM promociones WHERE activa = 1 ORDER BY id DESC");
$promos = $stmt->fetchAll();
echo json_encode($promos);
?>