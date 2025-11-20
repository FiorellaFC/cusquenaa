<?php
$conexion = new mysqli("localhost", "root", "", "la_cusquena");

if ($conexion->connect_error) {
    die("Error de conexión: " . $conexion->connect_error);
}

$fecha = $_POST['fecha'];
$hora = $_POST['hora'];
$nombre = $_POST['nombre_cliente'];
$telefono = $_POST['telefono_cliente'];
$email = $_POST['email_cliente'];
$servicio = $_POST['servicio_solicitado'];

$sql = "INSERT INTO citas 
        (fecha, hora, nombre_cliente, telefono_cliente, email_cliente, servicio_solicitado, estado)
        VALUES 
        ('$fecha', '$hora', '$nombre', '$telefono', '$email', '$servicio', 'confirmada')";

if ($conexion->query($sql) === TRUE) {
    echo "<script>
            // Avisamos al navegador que sí se registró
            localStorage.setItem('reserva_ok', '1');
            // Volver a la vista de servicios
            window.location = 'http://localhost/cusquena/frontend/pages/vistaServicios.html';
          </script>";
} else {
    echo 'Error: ' . $conexion->error;
}

$conexion->close();
?>
