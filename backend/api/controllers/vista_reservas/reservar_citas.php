<?php
header('Content-Type: application/json');
$pdo = require_once "../../../config/config.php";

// --- CONFIGURACIÓN DE HORARIOS ---
$HORA_INICIO = '08:00';
$HORA_FIN = '18:00';
$INTERVALO_MINUTOS = 30; // Intervalo de cada cita en minutos
$TIEMPO_BLOQUEO_SEGUNDOS = 300; // 5 minutos

// --- LÓGICA PRINCIPAL ---
$method = $_SERVER['REQUEST_METHOD'];
$data = json_decode(file_get_contents('php://input'), true);

// Limpiar bloqueos expirados (Idealmente esto lo hace un cron job, pero lo ponemos aquí por simplicidad)
$pdo->query("DELETE FROM horarios_bloqueados WHERE expires_at < NOW()");

if ($method === 'GET') {
    try {
        $fecha = $_GET['fecha'] ?? date('Y-m-d');
        
        // Obtener citas confirmadas
        $stmt_citas = $pdo->prepare("SELECT hora FROM citas WHERE fecha = :fecha AND estado = 'confirmada'");
        $stmt_citas->execute(['fecha' => $fecha]);
        $citas_confirmadas = $stmt_citas->fetchAll(PDO::FETCH_COLUMN, 0);

        // Obtener horarios bloqueados temporalmente
        $stmt_bloqueos = $pdo->prepare("SELECT hora FROM horarios_bloqueados WHERE fecha = :fecha");
        $stmt_bloqueos->execute(['fecha' => $fecha]);
        $horarios_bloqueados = $stmt_bloqueos->fetchAll(PDO::FETCH_COLUMN, 0);

        // Generar la lista completa de horarios del día
        $horarios_del_dia = [];
        $inicio = new DateTime($HORA_INICIO);
        $fin = new DateTime($HORA_FIN);
        $intervalo = new DateInterval('PT' . $INTERVALO_MINUTOS . 'M');
        $periodo = new DatePeriod($inicio, $intervalo, $fin);

        foreach ($periodo as $dt) {
            $hora = $dt->format('H:i:s');
            $estado = 'disponible';
            if (in_array($hora, $citas_confirmadas)) {
                $estado = 'ocupado';
            } elseif (in_array($hora, $horarios_bloqueados)) {
                $estado = 'bloqueado';
            }
            $horarios_del_dia[] = ['hora' => $dt->format('H:i'), 'estado' => $estado];
        }

        echo json_encode($horarios_del_dia);

    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(['error' => 'Error en la base de datos: ' . $e->getMessage()]);
    }
} 
elseif ($method === 'POST') {
    $accion = $data['accion'] ?? '';
    
    try {
        switch ($accion) {
            case 'bloquear':
                $expires_at = date('Y-m-d H:i:s', time() + $TIEMPO_BLOQUEO_SEGUNDOS);
                $sql = "INSERT INTO horarios_bloqueados (fecha, hora, session_id, expires_at) VALUES (:fecha, :hora, :session_id, :expires_at)";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([
                    'fecha' => $data['fecha'],
                    'hora' => $data['hora'],
                    'session_id' => $data['session_id'],
                    'expires_at' => $expires_at
                ]);
                echo json_encode(['success' => true, 'message' => 'Horario bloqueado.']);
                break;
            
            case 'liberar':
                $sql = "DELETE FROM horarios_bloqueados WHERE fecha = :fecha AND hora = :hora AND session_id = :session_id";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([
                    'fecha' => $data['fecha'],
                    'hora' => $data['hora'],
                    'session_id' => $data['session_id']
                ]);
                echo json_encode(['success' => true, 'message' => 'Horario liberado.']);
                break;

            case 'confirmar':
                $pdo->beginTransaction();
                // Verificar que el bloqueo aún pertenece al usuario
                $sql_check = "SELECT id FROM horarios_bloqueados WHERE fecha = :fecha AND hora = :hora AND session_id = :session_id";
                $stmt_check = $pdo->prepare($sql_check);
                $stmt_check->execute(['fecha' => $data['fecha'], 'hora' => $data['hora'], 'session_id' => $data['session_id']]);

                if ($stmt_check->fetch()) {
                    // Insertar la cita
                    $sql_insert = "INSERT INTO citas (fecha, hora, nombre_cliente, telefono_cliente, email_cliente, servicio_solicitado) VALUES (:fecha, :hora, :nombre, :telefono, :email, :servicio)";
                    $stmt_insert = $pdo->prepare($sql_insert);
                    $stmt_insert->execute([
                        'fecha' => $data['fecha'],
                        'hora' => $data['hora'],
                        'nombre' => $data['nombre_cliente'],
                        'telefono' => $data['telefono_cliente'],
                        'email' => $data['email_cliente'],
                        'servicio' => $data['servicio_solicitado']
                    ]);
                    
                    // Eliminar el bloqueo
                    $sql_delete = "DELETE FROM horarios_bloqueados WHERE fecha = :fecha AND hora = :hora";
                    $stmt_delete = $pdo->prepare($sql_delete);
                    $stmt_delete->execute(['fecha' => $data['fecha'], 'hora' => $data['hora']]);
                    
                    $pdo->commit();
                    echo json_encode(['success' => true, 'message' => 'Cita confirmada exitosamente.']);
                } else {
                    $pdo->rollBack();
                    http_response_code(409); // Conflict
                    echo json_encode(['error' => 'El horario ya no está disponible o tu sesión ha expirado.']);
                }
                break;

            default:
                http_response_code(400);
                echo json_encode(['error' => 'Acción no válida.']);
        }
    } catch (PDOException $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        http_response_code(500);
        // Si es un error de duplicado (UNIQUE KEY)
        if ($e->getCode() == 23000) {
            echo json_encode(['error' => 'Este horario acaba de ser ocupado. Por favor, seleccione otro.']);
        } else {
            echo json_encode(['error' => 'Error en el servidor: ' . $e->getMessage()]);
        }
    }
}
?>
