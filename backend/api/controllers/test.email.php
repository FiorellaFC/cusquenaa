<?php
require_once __DIR__ . '/../includes/phpmailer/src/PHPMailer.php';
require_once __DIR__ . '/../includes/phpmailer/src/SMTP.php';
require_once __DIR__ . '/../includes/phpmailer/src/Exception.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

$mail = new PHPMailer(true);
try {
    // Configuración del servidor
    $mail->SMTPDebug = SMTP::DEBUG_SERVER; // Habilita debugging
    $mail->isSMTP();
    $mail->Host       = 'smtp.gmail.com';
    $mail->SMTPAuth   = true;
    $mail->Username   = 'jian947200@gmail.com';
    $mail->Password   = 'mcvd wyvn zops tpmy';
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS; // Usa STARTTLS
    $mail->Port       = 587; // Puerto correcto para STARTTLS
    
    // Opcional: Configuración adicional para evitar problemas SSL
    $mail->SMTPOptions = [
        'ssl' => [
            'verify_peer' => false,
            'verify_peer_name' => false,
            'allow_self_signed' => true
        ]
    ];
    
    // Remitente y destinatario
    $mail->setFrom('jian947200@gmail.com', 'Soporte Cusqueña'); // Debe coincidir con tu email
    $mail->addAddress('jian947200@gmail.com', 'gian');
    
    // Contenido
    $mail->isHTML(true);
    $mail->Subject = 'Recuperación de Contraseña';
    $mail->Body    = 'Hola, este correo fue generado para realizar cambios en tu contraseña. 
                     <a href="http://localhost/cusquena/cusquena/frontend/pages/contraseña_nueva.php">
                     Haz clic aquí para restablecer tu contraseña</a>';
    
    $mail->send();
    echo 'El mensaje ha sido enviado correctamente';
} catch (Exception $e) {
    echo "Error al enviar el mensaje: " . $e->getMessage();
    echo "<br>Detalles técnicos: " . $mail->ErrorInfo;
}