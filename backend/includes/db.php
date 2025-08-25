<?php
// backend/includes/db.php

$host = 'localhost';       // Servidor de la base de datos
$dbname = 'la_cusquena';   // Nombre de la base de datos
$username = 'root';        // Usuario de MySQL
$password = '';            // Contraseña de MySQL

try {
    // Crear una conexión PDO
    $conn = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    // Si hay un error, mostrar un mensaje
    die("Error de conexión: " . $e->getMessage());
}
?>