<?php
// Define el tipo de contenido de la respuesta como JSON
header('Content-Type: application/json');

// Usamos tu archivo de conexión original
require_once "../../includes/db.php"; 

// --- CONFIGURACIÓN ---
$HORA_INICIO = '08:00';
$HORA_FIN = '18:00'; 
$INTERVALO_MINUTOS = 60; 
$TIEMPO_BLOQUEO_SEGUNDOS = 300;

// --- PROCESAMIENTO ---
$method = $_SERVER['REQUEST_METHOD'];
$data = json_decode(file_get_contents('php://input'), true);

// Limpieza de bloqueos expirados
try {
    $conn->query("DELETE FROM horarios_bloqueados WHERE expires_at < NOW()");
} catch (PDOException $e) {
    // Silenciar error de limpieza
}

if ($method === 'GET') {
    try {
        // --- CÁLCULO SEMANAL ---
        $hoy = new DateTime();
        $dia_semana = $hoy->format('N');
        $lunes = clone $hoy;
        $lunes->modify('-' . ($dia_semana - 1) . ' days');
        
        $fechas_semana = [];
        for ($i = 0; $i < 6; $i++) {
            $fechas_semana[] = (clone $lunes)->modify("+$i days")->format('Y-m-d');
        }

        $fecha_inicio_semana = $fechas_semana[0];
        $fecha_fin_semana = $fechas_semana[5];

        // Citas confirmadas
        $stmt_citas = $conn->prepare("SELECT fecha, hora FROM citas WHERE fecha BETWEEN :inicio AND :fin AND estado = 'confirmada'");
        $stmt_citas->execute(['inicio' => $fecha_inicio_semana, 'fin' => $fecha_fin_semana]);
        $citas_confirmadas_raw = $stmt_citas->fetchAll(PDO::FETCH_ASSOC);
        $citas_confirmadas = [];
        foreach($citas_confirmadas_raw as $cita) {
            $citas_confirmadas[$cita['fecha']][] = $cita['hora'];
        }

        // Horarios bloqueados
        $stmt_bloqueos = $conn->prepare("SELECT fecha, hora FROM horarios_bloqueados WHERE fecha BETWEEN :inicio AND :fin");
        $stmt_bloqueos->execute(['inicio' => $fecha_inicio_semana, 'fin' => $fecha_fin_semana]);
        $bloqueos_raw = $stmt_bloqueos->fetchAll(PDO::FETCH_ASSOC);
        $horarios_bloqueados = [];
        foreach($bloqueos_raw as $bloqueo) {
            $horarios_bloqueados[$bloqueo['fecha']][] = $bloqueo['hora'];
        }

        // Armar respuesta
        $respuesta_semanal = [];
        $nombres_dias = ["", "Lunes", "Martes", "Miércoles", "Jueves", "Viernes", "Sábado"];
        
        foreach ($fechas_semana as $fecha_str) {
            $fecha_obj = new DateTime($fecha_str);
            $horarios_del_dia = [];
            
            $inicio_dt = new DateTime($fecha_str . ' ' . $HORA_INICIO);
            $fin_dt = new DateTime($fecha_str . ' ' . $HORA_FIN);
            $intervalo = new DateInterval('PT' . $INTERVALO_MINUTOS . 'M');
            $periodo = new DatePeriod($inicio_dt, $intervalo, $fin_dt);

            foreach ($periodo as $dt) {
                $hora_str = $dt->format('H:i:s');
                $estado = 'disponible';
                
                if (isset($citas_confirmadas[$fecha_str]) && in_array($hora_str, $citas_confirmadas[$fecha_str])) {
                    $estado = 'ocupado';
                } elseif (isset($horarios_bloqueados[$fecha_str]) && in_array($hora_str, $horarios_bloqueados[$fecha_str])) {
                    $estado = 'bloqueado';
                }

                $horarios_del_dia[] = ['hora' => $dt->format('H:i'), 'estado' => $estado];
            }
            
            $respuesta_semanal[] = [
                'dia_nombre' => $nombres_dias[$fecha_obj->format('N')],
                'fecha_completa' => $fecha_obj->format('d/m/Y'),
                'fecha_iso' => $fecha_str,
                'horarios' => $horarios_del_dia
            ];
        }

        echo json_encode($respuesta_semanal);

    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(['error' => 'Error BD: ' . $e->getMessage()]);
    }

} elseif ($method === 'POST') {
    $accion = $data['accion'] ?? '';
    
    try {
        switch ($accion) {
            case 'bloquear':
                $expires_at = date('Y-m-d H:i:s', time() + $TIEMPO_BLOQUEO_SEGUNDOS);
                $sql = "INSERT INTO horarios_bloqueados (fecha, hora, session_id, expires_at) VALUES (:fecha, :hora, :session_id, :expires_at)";
                $stmt = $conn->prepare($sql);
                $stmt->execute([
                    'fecha' => $data['fecha'],
                    'hora' => $data['hora'],
                    'session_id' => $data['session_id'],
                    'expires_at' => $expires_at
                ]);
                echo json_encode(['success' => true, 'message' => 'Bloqueado']);
                break;
            
            case 'liberar':
                $sql = "DELETE FROM horarios_bloqueados WHERE fecha = :fecha AND hora = :hora AND session_id = :session_id";
                $stmt = $conn->prepare($sql);
                $stmt->execute([
                    'fecha' => $data['fecha'],
                    'hora' => $data['hora'],
                    'session_id' => $data['session_id']
                ]);
                echo json_encode(['success' => true, 'message' => 'Liberado']);
                break;

            case 'confirmar':
                $conn->beginTransaction();

                // 1. Verificar bloqueo
                $sql_check = "SELECT id FROM horarios_bloqueados WHERE fecha = :fecha AND hora = :hora AND session_id = :session_id";
                $stmt_check = $conn->prepare($sql_check);
                $stmt_check->execute(['fecha' => $data['fecha'], 'hora' => $data['hora'], 'session_id' => $data['session_id']]);

                if ($stmt_check->fetch()) {
                    
                    // --- LÓGICA DE CLIENTE / DNI ---
                    $cliente_id = null;
                    $dni = $data['dni_cliente'];

                    // Buscar si el cliente ya existe
                    $stmt_cliente = $conn->prepare("SELECT id FROM clientes WHERE dni_ruc = :dni");
                    $stmt_cliente->execute(['dni' => $dni]);
                    $cliente_existente = $stmt_cliente->fetch(PDO::FETCH_ASSOC);

                    if ($cliente_existente) {
                        // Existe: Actualizamos datos y obtenemos ID
                        $cliente_id = $cliente_existente['id'];
                        $sql_update = "UPDATE clientes SET nombre = :nombre, telefono = :telefono, email = :email WHERE id = :id";
                        $stmt_update = $conn->prepare($sql_update);
                        $stmt_update->execute([
                            'nombre' => $data['nombre_cliente'],
                            'telefono' => $data['telefono_cliente'],
                            'email' => $data['email_cliente'],
                            'id' => $cliente_id
                        ]);
                    } else {
                        // No existe: Creamos nuevo cliente
                        $sql_create = "INSERT INTO clientes (dni_ruc, nombre, telefono, email) VALUES (:dni, :nombre, :telefono, :email)";
                        $stmt_create = $conn->prepare($sql_create);
                        $stmt_create->execute([
                            'dni' => $dni,
                            'nombre' => $data['nombre_cliente'],
                            'telefono' => $data['telefono_cliente'],
                            'email' => $data['email_cliente']
                        ]);
                        $cliente_id = $conn->lastInsertId();
                    }
                    // -------------------------------

                    // 2. Insertar la cita vinculada al cliente_id
                    $sql_insert = "INSERT INTO citas (cliente_id, fecha, hora, nombre_cliente, telefono_cliente, email_cliente, servicio_solicitado) 
                                   VALUES (:cliente_id, :fecha, :hora, :nombre, :telefono, :email, :servicio)";
                    $stmt_insert = $conn->prepare($sql_insert);
                    $stmt_insert->execute([
                        'cliente_id' => $cliente_id,
                        'fecha' => $data['fecha'],
                        'hora' => $data['hora'],
                        'nombre' => $data['nombre_cliente'],
                        'telefono' => $data['telefono_cliente'],
                        'email' => $data['email_cliente'],
                        'servicio' => $data['servicio_solicitado']
                    ]);
                    
                    // 3. Eliminar el bloqueo
                    $sql_delete = "DELETE FROM horarios_bloqueados WHERE fecha = :fecha AND hora = :hora";
                    $stmt_delete = $conn->prepare($sql_delete);
                    $stmt_delete->execute(['fecha' => $data['fecha'], 'hora' => $data['hora']]);
                    
                    $conn->commit();
                    echo json_encode(['success' => true, 'message' => 'Cita confirmada exitosamente.']);
                } else {
                    $conn->rollBack();
                    http_response_code(409); 
                    echo json_encode(['error' => 'Tiempo expirado o horario no disponible.']);
                }
                break;

            default:
                http_response_code(400);
                echo json_encode(['error' => 'Acción no válida.']);
        }
    } catch (PDOException $e) {
        if ($conn->inTransaction()) {
            $conn->rollBack();
        }
        http_response_code(500);
        // Manejo específico de duplicados (por si acaso)
        if ($e->getCode() == 23000) {
             echo json_encode(['error' => 'Error de duplicidad (DNI o Horario ya registrado).']);
        } else {
             echo json_encode(['error' => 'Error interno: ' . $e->getMessage()]);
        }
    }
}
?>