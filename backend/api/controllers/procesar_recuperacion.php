<?php
session_start();

// Incluir PHPMailer
require_once __DIR__ . '/../../includes/phpmailer/PHPMailer.php';
require_once __DIR__ . '/../../includes/phpmailer/SMTP.php';
require_once __DIR__ . '/../../includes/phpmailer/Exception.php';
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

// Incluir conexión centralizada
require_once __DIR__ . '/../../includes/db.php';

// Procesar el formulario
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);

    // Verificar si el email existe
    $stmt = $conn->prepare("SELECT * FROM usuarios WHERE correo = :email");
    $stmt->bindParam(':email', $email);
    $stmt->execute();

    if ($stmt->rowCount() === 0) {
        $_SESSION['error'] = "No existe una cuenta con ese correo electrónico.";
        header("Location: ../../cusquena/frontend/pages/recuperar_contrasena.php");
        exit();
    }

    // Obtener datos del usuario
    $stmt = $conn->prepare("SELECT usuario, rol FROM usuarios WHERE correo = :email");
    $stmt->bindParam(':email', $email);
    $stmt->execute();
    $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

    $_SESSION['recovery_data'] = [
        'correo' => $email,
        'usuario' => $usuario['usuario'],
        'rol' => $usuario['rol']
    ];

    // Generar token
    $selector = bin2hex(random_bytes(8));
    $token = random_bytes(32);
    $expires = date("U") + 1800;

    // Eliminar tokens anteriores
    $stmt = $conn->prepare("DELETE FROM pwdReset WHERE pwdResetEmail = :email");
    $stmt->bindParam(':email', $email);
    $stmt->execute();

    // Insertar nuevo token
    $hashedToken = password_hash($token, PASSWORD_DEFAULT);
    $stmt = $conn->prepare("INSERT INTO pwdReset (pwdResetEmail, pwdResetSelector, pwdResetToken, pwdResetExpires) 
                            VALUES (:email, :selector, :token, :expires)");
    $stmt->bindParam(':email', $email);
    $stmt->bindParam(':selector', $selector);
    $stmt->bindParam(':token', $hashedToken);
    $stmt->bindParam(':expires', $expires);
    $stmt->execute();

    // Crear URL de recuperación
    $url = "http://localhost/cusquena/frontend/pages/contrasena_nueva.php?email=" . urlencode($email);

    // Enviar email
    $mail = new PHPMailer(true);
    try {
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'jian947200@gmail.com'; // Tu correo
        $mail->Password = 'mcvd wyvn zops tpmy';  // Contraseña de app
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587;
        $mail->CharSet = 'UTF-8';

        $mail->setFrom('no-reply@cusquena.com', 'Soporte Cusqueña');
        $mail->addAddress($email);

        $mail->isHTML(true);
        $mail->Subject = 'Restablece tu contraseña de Cusqueña';
        $mail->Body = '
        <html><body>
        <div style="max-width:600px;margin:auto;border:1px solid #ddd;padding:20px;">
        <h2 style="color:#0066cc;">Hola,</h2>
        <p>Haz clic en el siguiente enlace para restablecer tu contraseña:</p>
        <a href="' . $url . '" style="display:inline-block;background:#0066cc;color:white;padding:10px 15px;text-decoration:none;border-radius:5px;">
        Restablecer contraseña</a>
        <p>Este enlace expirará en 30 minutos.</p>
        <p><small>Si no solicitaste este cambio, ignora este mensaje.</small></p>
        <hr><p style="font-size:12px;color:#777;">Equipo de Soporte Cusqueña</p>
        </div></body></html>';

        $mail->AltBody = "Para restablecer tu contraseña, visita este enlace: $url";
        $mail->send();

        $_SESSION['mensaje'] = "¡Hemos enviado un enlace de recuperación a tu correo!";
    } catch (Exception $e) {
        error_log('Error al enviar correo: ' . $e->getMessage());
        $_SESSION['error'] = "Ocurrió un error al enviar el correo. Por favor, intenta nuevamente.";
    }

    header("Location: ../../../frontend/pages/recuperar_contrasena.php");
    exit();
} else {
    header("Location: ../../../frontend/pages/recuperar_contrasena.php");
    exit();
}
