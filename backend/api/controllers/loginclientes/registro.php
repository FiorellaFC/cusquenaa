<?php
// Configuración inicial
header('Content-Type: application/json');
require_once "../../../includes/db.php"; 

// Credenciales de correo (MISMAS QUE EN CITAS)
define('MAIL_HOST', 'smtp.gmail.com');
define('MAIL_USER', 'cusquena.oficial@gmail.com'); 
define('MAIL_PASS', 'qbjk ymyj satg qlzh'); 

// --- INCLUDES DE PHPMAILER ---
require_once __DIR__ . '/../../../includes/phpmailer/PHPMailer.php';
require_once __DIR__ . '/../../../includes/phpmailer/SMTP.php';
require_once __DIR__ . '/../../../includes/phpmailer/Exception.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

$data = json_decode(file_get_contents('php://input'), true);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = $data['reg_nombre'];
    $apellido = $data['reg_apellido'];
    $dni = $data['reg_dni'];
    $telefono = $data['reg_telefono'];
    $email = $data['reg_email'];

    try {
        // 1. Verificar si el cliente ya existe (por DNI o Email)
        $stmt = $conn->prepare("SELECT id, tiene_cuenta FROM clientes WHERE dni_ruc = ? OR email = ?");
        $stmt->execute([$dni, $email]);
        $cliente = $stmt->fetch(PDO::FETCH_ASSOC);

        $cliente_id = null;
        $token = bin2hex(random_bytes(32)); // Token para crear contraseña

        if ($cliente) {
            // Si ya tiene cuenta activa (tiene_cuenta = 1), no dejamos registrar de nuevo
            if ($cliente['tiene_cuenta'] == 1) {
                echo json_encode(['error' => 'Ya existe una cuenta registrada con este DNI o Correo. Por favor inicie sesión.']);
                exit;
            }
            // Si existe como invitado (hizo reserva antes), actualizamos sus datos y generamos token
            $cliente_id = $cliente['id'];
            $sql = "UPDATE clientes SET nombre=?, apellido=?, telefono=?, email=?, token_registro=? WHERE id=?";
            $conn->prepare($sql)->execute([$nombre, $apellido, $telefono, $email, $token, $cliente_id]);
        } else {
            // Es un cliente totalmente nuevo
            $sql = "INSERT INTO clientes (nombre, apellido, dni_ruc, telefono, email, token_registro, tiene_cuenta) VALUES (?, ?, ?, ?, ?, ?, 0)";
            $stmtInsert = $conn->prepare($sql);
            $stmtInsert->execute([$nombre, $apellido, $dni, $telefono, $email, $token]);
            $cliente_id = $conn->lastInsertId();
        }

        // 2. Enviar Correo de Activación
        $mail = new PHPMailer(true);
        $mail->isSMTP();
        $mail->Host = MAIL_HOST;
        $mail->SMTPAuth = true;
        $mail->Username = MAIL_USER;
        $mail->Password = MAIL_PASS;
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587;
        $mail->CharSet = 'UTF-8';

        $mail->setFrom(MAIL_USER, 'Lubricentro La Cusqueña');
        $mail->addAddress($email, "$nombre $apellido");

        // ENLACE ACTUALIZADO A LA NUEVA UBICACIÓN
        $link = "http://localhost/cusquena/backend/api/controllers/loginclientes/crear_password.php?token=" . $token;

        $mail->isHTML(true);
        $mail->Subject = 'Activa tu cuenta - La Cusqueña';
        $mail->Body = "
            <div style='font-family: sans-serif; padding: 20px; border: 1px solid #ddd; text-align: center;'>
                <h2 style='color: #d4af37;'>Bienvenido, $nombre</h2>
                <p>Gracias por registrarte. Para terminar de crear tu cuenta y definir tu contraseña, haz clic aquí:</p>
                <div style='margin: 30px 0;'>
                    <a href='$link' style='background:#198754; color:white; padding:12px 25px; text-decoration:none; border-radius:5px; font-weight:bold;'>ACTIVAR CUENTA</a>
                </div>
                <p><small>Si no solicitaste esto, ignora este mensaje.</small></p>
            </div>
        ";

        $mail->send();
        echo json_encode(['success' => true, 'message' => 'Correo enviado. Revisa tu bandeja para crear tu contraseña.']);

    } catch (Exception $e) {
        echo json_encode(['error' => 'Error al procesar: ' . $e->getMessage()]);
    }
}
?>