<?php
// 1. LÓGICA DE SESIÓN
session_start(); 
$session_id = uniqid('reserva_', true);

// Variables por defecto
$c_nombre = ''; $c_apellido = ''; $c_telefono = ''; $c_dni = ''; $c_email = '';
$usuario_logueado = false;

if (isset($_SESSION['cliente_data'])) {
    $usuario_logueado = true;
    $u = $_SESSION['cliente_data'];
    $c_nombre = htmlspecialchars($u['nombre']);
    $c_apellido = htmlspecialchars($u['apellido']);
    $c_dni = htmlspecialchars($u['dni']);
    $c_telefono = htmlspecialchars($u['telefono']);
    $c_email = htmlspecialchars($u['email']);
}

// 2. TIPO DE SERVICIO
$tipo_url = isset($_GET['tipo']) ? $_GET['tipo'] : 'mantenimiento';

if ($tipo_url === 'lavado') {
    $tipo_servicio_db = 'Lavado'; 
    $titulo_pagina = 'RESERVA DE LAVADO';
    $breadcrumb = 'HOME / RESERVAS / LAVADO';
} else {
    $tipo_servicio_db = 'Mantenimiento'; 
    $titulo_pagina = 'RESERVA DE MANTENIMIENTO';
    $breadcrumb = 'HOME / RESERVAS / MANTENIMIENTO';
}
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reservar Cita - Lubricentro La Cusqueña</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://use.fontawesome.com/releases/v6.3.0/js/all.js" crossorigin="anonymous"></script>
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">

    <style>
        body { padding-top: 70px; font-family: 'Poppins', sans-serif; background-color: #f2f2f2; color: #333; }
        .aviso-container { background-color: #1a1b1e; color: white; padding: 40px 20px; text-align: center; width: 100%; border-bottom: 4px solid #d4af37; }
        .horario-highlight { font-weight: 700; color: #fff; display: block; margin-bottom: 5px; font-size: 1.1rem; }
        .aviso-text { font-size: 0.9rem; opacity: 0.8; margin-top: 15px; margin-bottom: 0; }
        .main-title { text-align: center; margin-top: 50px; font-weight: 600; font-size: 2.5rem; text-transform: uppercase; margin-bottom: 10px; }
        .breadcrumb-custom { text-align: center; color: #6c757d; font-size: 0.9rem; letter-spacing: 2px; text-transform: uppercase; margin-bottom: 50px; }
        .card-clean { background: white; border-radius: 8px; border: 1px solid #e0e0e0; padding: 30px; margin-bottom: 20px; box-shadow: 0 2px 10px rgba(0,0,0,0.02); }
        .section-title { font-weight: 600; font-size: 1.25rem; margin-bottom: 20px; }
        .header-dark { background-color: #000; color: #d4af37; padding: 15px 20px; font-weight: 600; border-radius: 8px 8px 0 0; display: flex; align-items: center; }
        .horario-btn { border: 1px solid #198754; color: #198754; background-color: white; padding: 10px 20px; border-radius: 5px; font-weight: 500; width: 100px; transition: all 0.2s; cursor: pointer; }
        .horario-btn:hover:not(:disabled), .horario-btn.seleccionado { background-color: #198754; color: white; box-shadow: 0 4px 8px rgba(25, 135, 84, 0.2); transform: translateY(-2px); }
        .horario-btn:disabled { background-color: #e9ecef; border-color: #dee2e6; color: #adb5bd; cursor: not-allowed; opacity: 0.6; }
        #cronometro-container { background: #fff3cd; border: 1px solid #ffe69c; padding: 10px; text-align: center; margin-bottom: 20px; border-radius: 8px; display: none; }
        #cronometro { font-weight: bold; color: #dc3545; font-size: 1.2rem; }
        .checkmark-circle { width: 80px; height: 80px; position: relative; display: inline-block; margin: 0 auto 20px auto; }
        .checkmark-circle .background { width: 80px; height: 80px; border-radius: 50%; background: #4caf50; position: absolute; top: 0; left: 0; }
        .checkmark { border-radius: 5px; }
        .checkmark:after { position: absolute; display: block; content: ""; left: 30px; top: 16px; width: 20px; height: 40px; border: solid white; border-width: 0 6px 6px 0; transform: rotate(45deg); }
        #loading-overlay { position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(255, 255, 255, 0.85); z-index: 9999; display: none; justify-content: center; align-items: center; flex-direction: column; }
        .loading-content { background: white; padding: 30px; border-radius: 10px; box-shadow: 0 5px 15px rgba(0,0,0,0.1); text-align: center; }
        footer { background-color: #212529; color: white; text-align: center; padding: 30px 0; margin-top: 80px; font-size: 0.9rem; }
    </style>
</head>

<body data-tipo-servicio="<?php echo $tipo_servicio_db; ?>">

    <?php include 'navbarcusquena.php'; ?>
    
    <div class="aviso-container">
        <div class="aviso-content" data-aos="fade-down">
            <p class="aviso-text">Estimados clientes, nuestro horario de atención es de:</p>
            <span class="horario-highlight">Lunes a Sábados: 8:00 a.m. a 6:00 p.m.</span>
            <span class="horario-highlight">Domingos: 8:00 a.m. a 1:00 p.m.</span>
            <p class="aviso-text mt-4 mb-0">Recuerda que contamos con un tiempo de tolerancia de 10 minutos.</p>
        </div>
    </div>

    <div class="encabezado" data-aos="fade-up">
        <h1 class="main-title"><?php echo $titulo_pagina; ?></h1>
        <div class="breadcrumb-custom"><?php echo $breadcrumb; ?></div>
    </div>

    <div class="container mb-5" style="max-width: 900px;">
        <div class="card-clean" data-aos="fade-up">
            <h4 class="section-title">Selecciona un día y un horario</h4>
            <div class="mb-3">
                <label class="form-label fw-bold">Elige una fecha:</label>
                <input type="date" id="fecha" class="form-control form-control-lg">
            </div>
            <div id="contenedorHorarios" style="display:none;" class="mt-4">
                <h5 class="fw-bold mb-3" style="font-size: 1rem;">Horarios disponibles</h5>
                <div class="d-flex flex-wrap gap-2" id="listaHorarios"></div>
            </div>
        </div>

        <div id="cronometro-container">Tiempo restante: <span id="cronometro">05:00</span></div>

        <div id="paso2-datos-cliente" style="display: none;">
            
            <div class="header-dark">Resumen de su cita</div>
            <div class="card-clean" style="border-radius: 0 0 8px 8px; margin-top: -1px; background: #fff; padding: 20px; border-top: 0;">
                <div class="row">
                    <div class="col-md-6"><small class="text-muted">Día seleccionado</small><h4 id="resumenDia" class="fw-bold">--</h4></div>
                    <div class="col-md-6"><small class="text-muted">Hora seleccionada</small><h4 id="resumenHora" class="fw-bold">--</h4></div>
                </div>
            </div>

            <div class="header-dark mt-4"><i class="fas fa-user-edit me-2"></i> Confirmar Reserva</div>
            <div class="card-clean" style="border-radius: 0 0 8px 8px; margin-top: -1px; border-top: 0;">
                
                <form id="formConfirmarCita">
                    <input type="hidden" name="session_id" value="<?= htmlspecialchars($session_id) ?>">
                    <input type="hidden" name="fecha">
                    <input type="hidden" name="hora">

                    <?php if ($usuario_logueado): ?>
                        <div class="alert alert-dark border-warning d-flex align-items-center mb-4" role="alert" style="background-color: #212529; color: white;">
                            <i class="fas fa-user-check fa-2x text-warning me-3"></i>
                            <div>
                                <h5 class="mb-0 text-warning">Reservando como: <?php echo $c_nombre . ' ' . $c_apellido; ?></h5>
                                <small class="text-light opacity-75"><?php echo $c_email; ?> | DNI: <?php echo $c_dni; ?></small>
                            </div>
                        </div>

                        <input type="hidden" name="nombre_cliente" value="<?php echo $c_nombre; ?>">
                        <input type="hidden" name="apellido_cliente" value="<?php echo $c_apellido; ?>">
                        <input type="hidden" name="telefono_cliente" value="<?php echo $c_telefono; ?>">
                        <input type="hidden" name="dni_cliente" value="<?php echo $c_dni; ?>">
                        <input type="hidden" name="email_cliente" id="txtCorreo" value="<?php echo $c_email; ?>"> 

                    <?php else: ?>
                        <div class="alert alert-light border-secondary mb-4 small text-muted">
                            <i class="fas fa-info-circle"></i> ¿Ya tienes cuenta? <a href="#" data-bs-toggle="modal" data-bs-target="#authModal">Inicia sesión</a> para reservar más rápido.
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">Nombres</label>
                                <input type="text" name="nombre_cliente" class="form-control" required placeholder="Ej: Juan Carlos">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">Apellidos</label>
                                <input type="text" name="apellido_cliente" class="form-control" required placeholder="Ej: Pérez Lopez">
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">DNI / RUC</label>
                                <input type="text" name="dni_cliente" class="form-control" required placeholder="Ingrese su documento" maxlength="11" oninput="this.value = this.value.replace(/[^0-9]/g, '');">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">Teléfono</label>
                                <input type="tel" name="telefono_cliente" class="form-control" required placeholder="Ej: 987654321" maxlength="9" oninput="this.value = this.value.replace(/[^0-9]/g, '');">
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-12 mb-3">
                                <label class="form-label fw-bold">Correo electrónico</label>
                                <input type="email" name="email_cliente" id="txtCorreo" class="form-control" required placeholder="ejemplo@correo.com">
                                <div class="form-text mt-1 text-warning small">
                                    <i class="fas fa-exclamation-triangle me-1"></i> Asegúrese de colocar un correo electrónico válido y activo.
                                </div>
                                <div id="feedbackCorreo" class="invalid-feedback text-start"></div>
                            </div>
                        </div>
                    <?php endif; ?>

                   <div class="mb-4">
                        <label class="form-label fw-bold">Seleccione los servicios:</label>
                        
                        <div id="contenedor-servicios" class="p-3 border rounded bg-white" style="max-height: 200px; overflow-y: auto;">
                            <p class="text-muted small mb-0">Cargando servicios...</p>
                        </div>

                        <div id="precio-estimado" class="alert alert-warning mt-2 p-2 text-center fw-bold" style="display: none; font-size: 1rem; border-left: 4px solid #d4af37; background-color: #fff3cd; color: #856404;">
                            Total estimado: S/. 0.00
                        </div>
                    </div>

                    <div class="text-center">
                        <button type="submit" class="btn btn-dark btn-lg px-5" style="border: 1px solid #d4af37;">Confirmar mi reserva</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="modal fade" id="confirmModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content text-center p-4">
                <div class="checkmark-circle"><div class="background"></div><div class="checkmark"></div></div>
                <h3 class="mt-3 fw-bold">¡Registro Confirmado!</h3>
                <p class="text-muted" id="msgModalExito">Tu servicio fue registrado correctamente.</p>
                <button type="button" class="btn btn-dark mt-3 w-100" onclick="location.reload()">Aceptar</button>
            </div>
        </div>
    </div>

    <div id="loading-overlay">
        <div class="loading-content">
            <div class="spinner-border text-primary mb-3" role="status" style="width: 3rem; height: 3rem;"></div>
            <h5 class="fw-bold">Procesando su reserva...</h5>
            <p class="text-muted mb-0">Por favor, espere un momento.</p>
        </div>
    </div>

    <footer><div class="container">© 2025 Lubricentro La Cusqueña — Todos los derechos reservados</div></footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
    <script> AOS.init(); </script>
    <script src="../js/functions/reservar_cita.js"></script>

</body>
</html>