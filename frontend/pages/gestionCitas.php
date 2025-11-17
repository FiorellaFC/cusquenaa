<?php
// Generamos un ID de sesión único para cada visita.
// En una aplicación real, usarías la sesión de PHP (session_start() y $_SESSION).
$session_id = uniqid('reserva_', true);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Reservar Cita - Lubricentro La Cusqueña</title>
    
    <link href="../css/bootstrap.css" rel="stylesheet" />
    <script src="https://use.fontawesome.com/releases/v6.3.0/js/all.js" crossorigin="anonymous"></script>
    <style>
        body { background-color: #e9ecef; }
        .container { max-width: 960px; }
        .card-header { background-color: #0d6efd; color: white; }
        
        /* Estilos del Acordeón */
        .accordion-button { font-weight: 600; font-size: 1.1rem; }
        .accordion-button:not(.collapsed) { color: #0c63e4; background-color: #e7f1ff; }
        
        /* Grid de Horarios */
        .horarios-grid { 
            display: grid; 
            grid-template-columns: repeat(auto-fill, minmax(120px, 1fr)); 
            gap: 1rem; 
            padding: 1rem;
        }
        
        /* Estilos de los Botones de Horario */
        .horario-btn {
            border: 2px solid;
            font-weight: 500;
            transition: all 0.2s ease-in-out;
            padding: 0.75rem;
        }
        .horario-btn.disponible { border-color: #198754; color: #198754; }
        .horario-btn.disponible:hover { background-color: #198754; color: white; transform: translateY(-2px); }
        
        .horario-btn.seleccionado { 
            background-color: #0d6efd; 
            color: white; 
            border-color: #0d6efd;
            box-shadow: 0 0 10px rgba(13, 110, 253, 0.5);
            transform: scale(1.05);
        }
        
        .horario-btn.ocupado, .horario-btn.bloqueado { 
            background-color: #6c757d;
            border-color: #6c757d;
            color: white; 
            cursor: not-allowed; 
            opacity: 0.6; 
        }

        #cronometro-container {
            background-color: #fff3cd;
            border: 1px solid #ffe69c;
            border-radius: .375rem;
        }
        #cronometro { font-size: 1.5rem; font-weight: bold; color: #dc3545; }
    </style>
</head>
<body>
    <div class="container my-5">
        <div class="card shadow-lg">
            <div class="card-header text-center">
                <h2>Reservar Cita en Lubricentro</h2>
            </div>
            <div class="card-body p-4">

                <!-- PASO 1: SELECCIONAR DÍA Y HORA -->
                <div class="mb-4">
                    <h4 class="border-bottom pb-2 mb-3"><i class="fas fa-calendar-alt me-2"></i>Paso 1: Selecciona un día y un horario</h4>
                    
                    <div class="accordion" id="acordeonSemana">
                        <!-- El JavaScript generará los días de la semana aquí -->
                        <div class="text-center p-5">
                            <div class="spinner-border text-primary" role="status">
                                <span class="visually-hidden">Cargando horarios de la semana...</span>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- CONTENEDOR DEL CRONÓMETRO -->
                <div id="cronometro-container" class="text-center p-3 my-4" style="display: none;">
                    <h5>Tiempo restante para confirmar</h5>
                    <p>Tu horario seleccionado se mantendrá reservado por: <span id="cronometro">05:00</span></p>
                </div>

                <!-- PASO 2: DATOS DEL CLIENTE (se muestra al seleccionar un horario) -->
                <div id="paso2-datos-cliente" style="display: none;">
                    <h4 class="border-bottom pb-2 mb-3"><i class="fas fa-user-edit me-2"></i>Paso 2: Completa tus datos</h4>
                    <form id="formConfirmarCita">
                        <input type="hidden" name="session_id" value="<?= htmlspecialchars($session_id) ?>">
                        <input type="hidden" name="fecha">
                        <input type="hidden" name="hora">
                        
                        <div class="mb-3">
                            <label for="nombre_cliente" class="form-label">Nombre Completo</label>
                            <input type="text" class="form-control" id="nombre_cliente" name="nombre_cliente" required>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="telefono_cliente" class="form-label">Teléfono</label>
                                <input type="tel" class="form-control" id="telefono_cliente" name="telefono_cliente">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="email_cliente" class="form-label">Correo Electrónico</label>
                                <input type="email" class="form-control" id="email_cliente" name="email_cliente">
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="servicio_solicitado" class="form-label">Servicio Requerido (Opcional)</label>
                            <input type="text" class="form-control" id="servicio_solicitado" name="servicio_solicitado" placeholder="Ej: Cambio de aceite, revisión de frenos...">
                        </div>
                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary btn-lg">Confirmar Mi Cita</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="../js/bootstrap.bundle.min.js"></script>
    <!-- ASEGÚRATE DE QUE ESTA RUTA ES CORRECTA -->
    <script src="../js/functions/reservar_cita.js"></script>
</body>
</html>

