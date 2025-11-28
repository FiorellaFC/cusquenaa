<?php
session_start();
header('Content-Type: application/json');
require_once "../../../includes/db.php"; 

$data = json_decode(file_get_contents('php://input'), true);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $data['login_email'];
    $password = $data['login_password'];

    try {
        // Buscamos solo usuarios ACTIVOS (tiene_cuenta = 1)
        $stmt = $conn->prepare("SELECT id, nombre, apellido, password, dni_ruc, telefono, email FROM clientes WHERE email = ? AND tiene_cuenta = 1");
        $stmt->execute([$email]);
        $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($usuario && password_verify($password, $usuario['password'])) {
            // Login Exitoso
            $_SESSION['cliente_id'] = $usuario['id'];
            $_SESSION['cliente_nombre'] = $usuario['nombre'] . ' ' . $usuario['apellido'];
            
            // Guardamos datos en sesión para AUTOCOMPLETAR reservas luego
            $_SESSION['cliente_data'] = [
                'nombre' => $usuario['nombre'],
                'apellido' => $usuario['apellido'],
                'dni' => $usuario['dni_ruc'],
                'telefono' => $usuario['telefono'],
                'email' => $usuario['email']
            ];

            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['error' => 'Correo o contraseña incorrectos. (Asegúrate de haber activado tu cuenta desde el correo)']);
        }
    } catch (Exception $e) {
        echo json_encode(['error' => 'Error del servidor.']);
    }
}
?>