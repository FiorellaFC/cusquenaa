<?php
// --- CONFIGURACIÓN DE CORREO ---
define('MAIL_HOST', 'smtp.gmail.com');
define('MAIL_USER', 'cusquena.oficial@gmail.com'); 
define('MAIL_PASS', 'qbjk ymyj satg qlzh'); 

// --- INCLUDES DE PHPMAILER ---
require_once __DIR__ . '/../../../includes/phpmailer/PHPMailer.php';
require_once __DIR__ . '/../../../includes/phpmailer/SMTP.php';
require_once __DIR__ . '/../../../includes/phpmailer/Exception.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

// Configuración General
header('Content-Type: application/json');
date_default_timezone_set('America/Lima');
require_once "../../../includes/db.php"; 

$HORA_INICIO = '08:00';
$HORA_FIN = '18:00'; 
$INTERVALO_MINUTOS = 60; 
$TIEMPO_BLOQUEO_SEGUNDOS = 300;

$method = $_SERVER['REQUEST_METHOD'];
$data = json_decode(file_get_contents('php://input'), true);

// Limpieza automática
try { $conn->query("DELETE FROM horarios_bloqueados WHERE expires_at < NOW()"); } catch (Exception $e) {}

// ==========================================
// GET
// ==========================================
if ($method === 'GET') {
    
    // 1. Obtener Servicios
    if (isset($_GET['accion']) && $_GET['accion'] === 'obtener_servicios') {
        $tipo = $_GET['tipo'] ?? 'Mantenimiento';
        try {
            $stmt = $conn->prepare("SELECT id, nombre, precio FROM servicios WHERE tipo = :tipo");
            $stmt->execute(['tipo' => $tipo]);
            echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
        } catch (Exception $e) { echo json_encode(['error' => $e->getMessage()]); }
        exit;
    }

    // 2. Obtener Horarios
    try {
        $hoy = new DateTime();
        $fecha_actual_str = $hoy->format('Y-m-d');
        $hora_actual_str = $hoy->format('H:i:s');
        $dia_semana = $hoy->format('N');
        
        $lunes = clone $hoy;
        $lunes->modify('-' . ($dia_semana - 1) . ' days');
        
        $fechas_semana = [];
        for ($i = 0; $i < 6; $i++) { $fechas_semana[] = (clone $lunes)->modify("+$i days")->format('Y-m-d'); }

        $stmt_citas = $conn->prepare("SELECT fecha, hora FROM citas WHERE fecha BETWEEN :i AND :f AND estado IN ('confirmada', 'pendiente')");
        $stmt_citas->execute(['i' => $fechas_semana[0], 'f' => $fechas_semana[5]]);
        $citas_ocupadas = [];
        foreach($stmt_citas->fetchAll(PDO::FETCH_ASSOC) as $c) $citas_ocupadas[$c['fecha']][] = $c['hora'];

        $stmt_bloq = $conn->prepare("SELECT fecha, hora FROM horarios_bloqueados WHERE fecha BETWEEN :i AND :f");
        $stmt_bloq->execute(['i' => $fechas_semana[0], 'f' => $fechas_semana[5]]);
        $horarios_bloqueados = [];
        foreach($stmt_bloq->fetchAll(PDO::FETCH_ASSOC) as $b) $horarios_bloqueados[$b['fecha']][] = $b['hora'];

        $respuesta = [];
        $nombres_dias = ["", "Lunes", "Martes", "Miércoles", "Jueves", "Viernes", "Sábado"];

        foreach ($fechas_semana as $fecha_str) {
            $fecha_obj = new DateTime($fecha_str);
            $horarios_del_dia = [];
            $inicio = new DateTime($fecha_str.' '.$HORA_INICIO);
            $fin = new DateTime($fecha_str.' '.$HORA_FIN);
            $periodo = new DatePeriod($inicio, new DateInterval('PT'.$INTERVALO_MINUTOS.'M'), $fin);

            foreach ($periodo as $dt) {
                $hora = $dt->format('H:i:s');
                $hora_corta = $dt->format('H:i');
                $estado = 'disponible';

                if ($fecha_str < $fecha_actual_str || ($fecha_str === $fecha_actual_str && $hora < $hora_actual_str)) {
                    $estado = 'bloqueado'; 
                } elseif (isset($citas_ocupadas[$fecha_str]) && in_array($hora, $citas_ocupadas[$fecha_str])) {
                    $estado = 'ocupado';
                } elseif (isset($horarios_bloqueados[$fecha_str]) && in_array($hora, $horarios_bloqueados[$fecha_str])) {
                    $estado = 'bloqueado';
                }
                $horarios_del_dia[] = ['hora' => $hora_corta, 'estado' => $estado];
            }
            $respuesta[] = [
                'dia_nombre' => $nombres_dias[$fecha_obj->format('N')],
                'fecha_completa' => $fecha_obj->format('d/m/Y'),
                'fecha_iso' => $fecha_str,
                'horarios' => $horarios_del_dia
            ];
        }
        echo json_encode($respuesta);
    } catch (Exception $e) { echo json_encode(['error' => $e->getMessage()]); }
}

// ==========================================
// POST
// ==========================================
elseif ($method === 'POST') {
    $accion = $data['accion'] ?? '';
    
    try {
        switch ($accion) {
            case 'bloquear':
                $exp = date('Y-m-d H:i:s', time() + $TIEMPO_BLOQUEO_SEGUNDOS);
                $conn->prepare("INSERT INTO horarios_bloqueados (fecha, hora, session_id, expires_at) VALUES (?,?,?,?)")
                     ->execute([$data['fecha'], $data['hora'], $data['session_id'], $exp]);
                echo json_encode(['success' => true]);
                break;
            
            case 'liberar':
                $conn->prepare("DELETE FROM horarios_bloqueados WHERE fecha=? AND hora=? AND session_id=?")
                     ->execute([$data['fecha'], $data['hora'], $data['session_id']]);
                echo json_encode(['success' => true]);
                break;

           case 'confirmar':
                $conn->beginTransaction();

                // 1. Verificar bloqueo
                $stmt_check = $conn->prepare("SELECT id FROM horarios_bloqueados WHERE fecha=? AND hora=? AND session_id=?");
                $stmt_check->execute([$data['fecha'], $data['hora'], $data['session_id']]);

                if ($stmt_check->fetch()) {
                    
                    // 2. Gestionar Cliente (Buscar o Crear)
                    $stmt_cli = $conn->prepare("SELECT id FROM clientes WHERE dni_ruc = ?");
                    $stmt_cli->execute([$data['dni_cliente']]);
                    $cli = $stmt_cli->fetch(PDO::FETCH_ASSOC);
                    
                    if ($cli) {
                        // El cliente existe: Actualizamos Nombre Y APELLIDO
                        $cid = $cli['id'];
                        $sql_update = "UPDATE clientes SET nombre=?, apellido=?, telefono=?, email=? WHERE id=?";
                        $conn->prepare($sql_update)
                             ->execute([
                                 $data['nombre_cliente'], 
                                 $data['apellido_cliente'], // <--- Nuevo dato
                                 $data['telefono_cliente'], 
                                 $data['email_cliente'], 
                                 $cid
                             ]);
                    } else {
                        // El cliente no existe: Lo creamos con Nombre Y APELLIDO
                        $sql_create = "INSERT INTO clientes (dni_ruc, nombre, apellido, telefono, email) VALUES (?,?,?,?,?)";
                        $conn->prepare($sql_create)
                             ->execute([
                                 $data['dni_cliente'], 
                                 $data['nombre_cliente'], 
                                 $data['apellido_cliente'], // <--- Nuevo dato
                                 $data['telefono_cliente'], 
                                 $data['email_cliente']
                             ]);
                        $cid = $conn->lastInsertId();
                    }

                    // 3. Generar Token
                    $token = bin2hex(random_bytes(32));
                    
                    // 4. Insertar Cita (Incluyendo apellido_cliente)
                    $sql_ins = "INSERT INTO citas (cliente_id, fecha, hora, nombre_cliente, apellido_cliente, telefono_cliente, email_cliente, servicio_id, estado, token_confirmacion) 
                                VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'pendiente', ?)";
                    
                    $conn->prepare($sql_ins)->execute([
                        $cid, 
                        $data['fecha'], 
                        $data['hora'], 
                        $data['nombre_cliente'], 
                        $data['apellido_cliente'], // <--- Nuevo dato en tabla citas
                        $data['telefono_cliente'], 
                        $data['email_cliente'], 
                        $data['servicio_solicitado'], 
                        $token
                    ]);
                    
                    // 5. Eliminar bloqueo
                    $conn->prepare("DELETE FROM horarios_bloqueados WHERE fecha=? AND hora=?")->execute([$data['fecha'], $data['hora']]);
                    
                    // 6. ENVIAR CORREO
                    $mail = new PHPMailer(true);
                    try {
                        $emailDestino = $data['email_cliente'];
                        // Validar formato
                        if (!PHPMailer::validateAddress($emailDestino)) {
                            throw new Exception("FORMATO_INVALIDO");
                        }

                        $mail->isSMTP();
                        $mail->Host = MAIL_HOST;
                        $mail->SMTPAuth = true;
                        $mail->Username = MAIL_USER;
                        $mail->Password = MAIL_PASS;
                        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                        $mail->Port = 587;
                        $mail->CharSet = 'UTF-8';

                        $mail->setFrom(MAIL_USER, 'Lubricentro La Cusqueña');
                        $mail->addAddress($emailDestino, $data['nombre_cliente']);

                        $link = "http://localhost/cusquena/backend/api/controllers/vista_reservas/confirmar_asistencia.php?token=" . $token;

                        $mail->isHTML(true);
                        $mail->Subject = 'Confirma tu Cita - La Cusqueña';
                        // Actualizamos el cuerpo del correo para que sea más formal con el apellido
                        $nombreCompleto = $data['nombre_cliente'] . ' ' . $data['apellido_cliente'];
                        
                        $mail->Body = "
                            <div style='font-family: sans-serif; padding: 20px; border: 1px solid #ddd;'>
                                <h2 style='color: #d4af37;'>Hola, {$nombreCompleto}</h2>
                                <p>Has solicitado una reserva para el <strong>{$data['fecha']}</strong> a las <strong>{$data['hora']}</strong>.</p>
                                <p>Para completar el proceso, por favor confirma tu asistencia:</p>
                                <a href='$link' style='display: inline-block; background: #198754; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>CONFIRMAR ASISTENCIA</a>
                                <p><small>Si no confirmas, la cita se cancelará automáticamente.</small></p>
                            </div>
                        ";

                        $mail->send();
                        $conn->commit();
                        echo json_encode(['success' => true, 'message' => 'Correo enviado.']);

                    } catch (Exception $e) {
                        $conn->rollBack();
                        // ... (Manejo de errores igual al anterior) ...
                        $msg = $e->getMessage();
                        $mailerError = $mail->ErrorInfo;
                        $jsonError = ''; $tipoError = 'general';

                        if ($msg === "FORMATO_INVALIDO") {
                            $jsonError = 'El formato del correo es inválido.';
                            $tipoError = 'email';
                        } else {
                            $jsonError = 'Error al enviar la confirmación. Intente nuevamente.';
                        }
                        echo json_encode(['success' => false, 'error' => $jsonError, 'tipo_error' => $tipoError]);
                    }

                } else {
                    $conn->rollBack();
                    http_response_code(409); 
                    echo json_encode(['error' => 'Tiempo expirado.']);
                }
                break;

            default:
                http_response_code(400);
                echo json_encode(['error' => 'Acción desconocida']);
        }
    } catch (Exception $e) {
        if($conn->inTransaction()) $conn->rollBack();
        http_response_code(500);
        echo json_encode(['error' => 'Error Servidor: ' . $e->getMessage()]);
    }
}
?>