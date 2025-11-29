<?php
// --- CONFIGURACIÓN DE CORREO ---
define('MAIL_HOST', 'smtp.gmail.com');
define('MAIL_USER', 'cusquena.oficial@gmail.com'); 
define('MAIL_PASS', 'qbjk ymyj satg qlzh'); 

// --- INCLUDES ---
require_once __DIR__ . '/../../../includes/phpmailer/PHPMailer.php';
require_once __DIR__ . '/../../../includes/phpmailer/SMTP.php';
require_once __DIR__ . '/../../../includes/phpmailer/Exception.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

header('Content-Type: application/json');
date_default_timezone_set('America/Lima');
require_once "../../../includes/db.php"; 

$HORA_INICIO = '08:00';
$HORA_FIN = '18:00'; 
$INTERVALO_MINUTOS = 60; 
$TIEMPO_BLOQUEO_SEGUNDOS = 300;

$method = $_SERVER['REQUEST_METHOD'];
$data = json_decode(file_get_contents('php://input'), true);

// Limpieza
try { $conn->query("DELETE FROM horarios_bloqueados WHERE expires_at < NOW()"); } catch (Exception $e) {}

// ==========================================
// GET
// ==========================================
if ($method === 'GET') {
    // 1. Servicios
    if (isset($_GET['accion']) && $_GET['accion'] === 'obtener_servicios') {
        $tipo = $_GET['tipo'] ?? 'Mantenimiento';
        try {
            $stmt = $conn->prepare("SELECT id, nombre, precio FROM servicios WHERE tipo = :tipo");
            $stmt->execute(['tipo' => $tipo]);
            echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
        } catch (Exception $e) { echo json_encode(['error' => $e->getMessage()]); }
        exit;
    }

    // 2. Horarios
    try {
        $hoy = new DateTime();
        $fecha_actual_str = $hoy->format('Y-m-d');
        $hora_actual_str = $hoy->format('H:i:s');
        
        $fechas_disponibles = [];
        for ($i = 0; $i < 14; $i++) {
            $fechas_disponibles[] = (clone $hoy)->modify("+$i days")->format('Y-m-d');
        }
        $fecha_inicio = $fechas_disponibles[0];
        $fecha_fin = end($fechas_disponibles);

        $stmt_citas = $conn->prepare("SELECT fecha, hora FROM citas WHERE fecha BETWEEN :i AND :f AND estado IN ('confirmada', 'pendiente')");
        $stmt_citas->execute(['i' => $fecha_inicio, 'f' => $fecha_fin]);
        $citas_ocupadas = [];
        foreach($stmt_citas->fetchAll(PDO::FETCH_ASSOC) as $c) $citas_ocupadas[$c['fecha']][] = $c['hora'];

        $stmt_bloq = $conn->prepare("SELECT fecha, hora FROM horarios_bloqueados WHERE fecha BETWEEN :i AND :f");
        $stmt_bloq->execute(['i' => $fecha_inicio, 'f' => $fecha_fin]);
        $horarios_bloqueados = [];
        foreach($stmt_bloq->fetchAll(PDO::FETCH_ASSOC) as $b) $horarios_bloqueados[$b['fecha']][] = $b['hora'];

        $respuesta = [];
        $nombres_dias = ["", "Lunes", "Martes", "Miércoles", "Jueves", "Viernes", "Sábado", "Domingo"];

        foreach ($fechas_disponibles as $fecha_str) {
            $fecha_obj = new DateTime($fecha_str);
            $dia_num = $fecha_obj->format('N');
            $horarios_del_dia = [];
            
            $hora_fin_dia = ($dia_num == 7) ? '13:00' : $HORA_FIN;
            $periodo = new DatePeriod(new DateTime($fecha_str.' '.$HORA_INICIO), new DateInterval('PT60M'), new DateTime($fecha_str.' '.$hora_fin_dia));

            foreach ($periodo as $dt) {
                $hora = $dt->format('H:i:s');
                $hora_corta = $dt->format('H:i');
                $estado = 'disponible';

                if ($fecha_str < $fecha_actual_str || ($fecha_str === $fecha_actual_str && $hora < $hora_actual_str)) $estado = 'bloqueado';
                elseif (isset($citas_ocupadas[$fecha_str]) && in_array($hora, $citas_ocupadas[$fecha_str])) $estado = 'ocupado';
                elseif (isset($horarios_bloqueados[$fecha_str]) && in_array($hora, $horarios_bloqueados[$fecha_str])) $estado = 'bloqueado';
                
                $horarios_del_dia[] = ['hora' => $hora_corta, 'estado' => $estado];
            }
            $respuesta[] = [
                'dia_nombre' => $nombres_dias[$dia_num],
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
                    
                    // 2. Gestionar Cliente (CON APELLIDO)
                    $stmt_cli = $conn->prepare("SELECT id FROM clientes WHERE dni_ruc = ?");
                    $stmt_cli->execute([$data['dni_cliente']]);
                    $cli = $stmt_cli->fetch(PDO::FETCH_ASSOC);
                    
                    if ($cli) {
                        $cid = $cli['id'];
                        $conn->prepare("UPDATE clientes SET nombre=?, apellido=?, telefono=?, email=? WHERE id=?")
                             ->execute([
                                $data['nombre_cliente'], 
                                $data['apellido_cliente'], // Actualizar apellido
                                $data['telefono_cliente'], 
                                $data['email_cliente'], 
                                $cid
                             ]);
                    } else {
                        $conn->prepare("INSERT INTO clientes (dni_ruc, nombre, apellido, telefono, email) VALUES (?,?,?,?,?)")
                             ->execute([
                                $data['dni_cliente'], 
                                $data['nombre_cliente'], 
                                $data['apellido_cliente'], // Insertar apellido
                                $data['telefono_cliente'], 
                                $data['email_cliente']
                             ]);
                        $cid = $conn->lastInsertId();
                    }

                    // 3. Token
                    $token = bin2hex(random_bytes(32));
                    
                    // 4. Insertar Cita (CON APELLIDO en histórico)
                    // IMPORTANTE: servicio_id es opcional si usas múltiples servicios después, 
                    // pero aquí lo mantenemos para compatibilidad.
                    // Si usas array de servicios, aquí iría NULL y luego insertarías en citas_detalles.
                    // Para este código mantengo servicio_id único como estaba antes del cambio de checkboxes.
                    
                    // OPCIÓN A: UN SOLO SERVICIO (Como estaba)
                    if (!isset($data['servicios'])) {
                        $sql_ins = "INSERT INTO citas (cliente_id, fecha, hora, nombre_cliente, apellido_cliente, telefono_cliente, email_cliente, servicio_id, estado, token_confirmacion) 
                                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'pendiente', ?)";
                        $conn->prepare($sql_ins)->execute([
                            $cid, $data['fecha'], $data['hora'], 
                            $data['nombre_cliente'], $data['apellido_cliente'], 
                            $data['telefono_cliente'], $data['email_cliente'], 
                            $data['servicio_solicitado'], // ID del servicio único
                            $token
                        ]);
                        $cita_id = $conn->lastInsertId();
                    } 
                    // OPCIÓN B: MÚLTIPLES SERVICIOS (Si usas checkboxes en el frontend)
                    else {
                        $sql_ins = "INSERT INTO citas (cliente_id, fecha, hora, nombre_cliente, apellido_cliente, telefono_cliente, email_cliente, estado, token_confirmacion) 
                                    VALUES (?, ?, ?, ?, ?, ?, ?, 'pendiente', ?)";
                        $conn->prepare($sql_ins)->execute([
                            $cid, $data['fecha'], $data['hora'], 
                            $data['nombre_cliente'], $data['apellido_cliente'], 
                            $data['telefono_cliente'], $data['email_cliente'], 
                            $token
                        ]);
                        $cita_id = $conn->lastInsertId();

                        // Insertar detalles
                        $stmt_det = $conn->prepare("INSERT INTO citas_detalles (cita_id, servicio_id, precio_al_momento) VALUES (?, ?, (SELECT precio FROM servicios WHERE id = ?))");
                        foreach ($data['servicios'] as $serv_id) {
                            $stmt_det->execute([$cita_id, $serv_id, $serv_id]);
                        }
                    }
                    
                    // 5. Borrar bloqueo
                    $conn->prepare("DELETE FROM horarios_bloqueados WHERE fecha=? AND hora=?")->execute([$data['fecha'], $data['hora']]);
                    
                    // 6. ENVIAR CORREO
                    $mail = new PHPMailer(true);
                    try {
                        $emailDestino = $data['email_cliente'];
                        
                        // Validar formato básico
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
                        
                        $nombreCompleto = $data['nombre_cliente'] . ' ' . $data['apellido_cliente'];
                        
                        $mail->Body = "
                            <div style='font-family: sans-serif; padding: 20px; border: 1px solid #ddd;'>
                                <h2 style='color: #d4af37;'>Hola, {$nombreCompleto}</h2>
                                <p>Has solicitado una reserva para el <strong>{$data['fecha']}</strong> a las <strong>{$data['hora']}</strong>.</p>
                                <p>Para completar el proceso, por favor confirma tu asistencia:</p>
                                <div style='text-align:center; margin: 20px 0;'>
                                    <a href='$link' style='display: inline-block; background: #198754; color: white; padding: 12px 25px; text-decoration: none; border-radius: 5px; font-weight: bold;'>CONFIRMAR ASISTENCIA</a>
                                </div>
                                <p><small>Si no confirmas, la cita se cancelará automáticamente.</small></p>
                            </div>
                        ";

                        $mail->send();
                        $conn->commit();
                        echo json_encode(['success' => true, 'message' => 'Correo enviado.']);

                    } catch (Exception $e) {
                        $conn->rollBack();
                        http_response_code(500);
                        
                        $msg = $e->getMessage();
                        $jsonError = 'Error al enviar la confirmación.';
                        $tipoError = 'general';

                        if ($msg === "FORMATO_INVALIDO") {
                            $jsonError = 'El formato del correo es inválido.';
                            $tipoError = 'email';
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