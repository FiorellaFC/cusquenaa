<?php
// backend/api/controllers/loginclientes/crear_password.php

// Ajustamos la ruta para llegar a includes (subir 4 niveles)
require_once "../../../includes/db.php"; 

$token = $_GET['token'] ?? '';
$mensaje = '';
$tipo_alerta = '';
$mostrar_formulario = false;
$nombre_cliente = '';

// RUTA PARA REDIRIGIR AL FRONTEND (LOGIN)
// Ajusta esto si tu archivo de inicio se llama index.php o vistaCusquena.php
$ruta_login = "../../../../frontend/pages/vistaCusquena.php";

// 1. VERIFICAR EL TOKEN
if ($token) {
    try {
        // Buscamos si existe algún cliente con ese token de registro
        $stmt = $conn->prepare("SELECT id, nombre, apellido FROM clientes WHERE token_registro = ?");
        $stmt->execute([$token]);
        $cliente = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($cliente) {
            $mostrar_formulario = true;
            $nombre_cliente = $cliente['nombre'] . ' ' . $cliente['apellido'];

            // 2. PROCESAR EL FORMULARIO
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $pass1 = $_POST['password'];
                $pass2 = $_POST['confirm_password'];

                if (strlen($pass1) < 6) {
                    $mensaje = "La contraseña debe tener al menos 6 caracteres.";
                    $tipo_alerta = "warning";
                } elseif ($pass1 !== $pass2) {
                    $mensaje = "Las contraseñas no coinciden.";
                    $tipo_alerta = "danger";
                } else {
                    $pass_hash = password_hash($pass1, PASSWORD_DEFAULT);
                    
                    // Actualizamos y activamos cuenta
                    $update = $conn->prepare("UPDATE clientes SET password = ?, tiene_cuenta = 1, token_registro = NULL WHERE id = ?");
                    
                    if ($update->execute([$pass_hash, $cliente['id']])) {
                        $mensaje = "¡Felicidades! Tu cuenta ha sido activada correctamente.";
                        $tipo_alerta = "success";
                        $mostrar_formulario = false; 
                    } else {
                        $mensaje = "Error al guardar en la base de datos.";
                        $tipo_alerta = "danger";
                    }
                }
            }
        } else {
            $mensaje = "Este enlace de activación ya no es válido o la cuenta ya fue activada.";
            $tipo_alerta = "danger";
        }
    } catch (Exception $e) {
        $mensaje = "Error del sistema: " . $e->getMessage();
        $tipo_alerta = "danger";
    }
} else {
    // Si entran sin token, mandar al home
    header("Location: " . $ruta_login);
    exit();
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Activar Cuenta - Lubricentro La Cusqueña</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://use.fontawesome.com/releases/v6.3.0/js/all.js" crossorigin="anonymous"></script>
    
    <style>
        body {
            /* Fondo oscuro con imagen y overlay */
            background: linear-gradient(rgba(0, 0, 0, 0.7), rgba(0, 0, 0, 0.7)), url('../../css/imagenes/img1.jpg');
            background-size: cover;
            background-position: center;
            background-attachment: fixed;
            display: flex;
            align-items: center;
            justify-content: center;
            height: 100vh;
            font-family: 'Poppins', sans-serif;
            margin: 0;
        }
        .card-custom {
            background-color: rgba(255, 255, 255, 0.95);
            border: none;
            border-top: 5px solid #d4af37; /* Dorado */
            box-shadow: 0 15px 30px rgba(0,0,0,0.5);
            border-radius: 15px;
            width: 100%;
            max-width: 450px;
            backdrop-filter: blur(10px);
        }
        .card-title {
            color: #333;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        .btn-dorado {
            background-color: #000;
            color: #d4af37;
            border: 2px solid #d4af37;
            font-weight: bold;
            transition: all 0.3s;
            text-transform: uppercase;
            border-radius: 50px;
            padding: 10px;
        }
        .btn-dorado:hover {
            background-color: #d4af37;
            color: #000;
            box-shadow: 0 5px 15px rgba(212, 175, 55, 0.4);
            transform: translateY(-2px);
        }
        .input-group-text {
            background-color: #f8f9fa;
            border-right: none;
        }
        .form-control {
            border-left: none;
        }
        .form-control:focus {
            box-shadow: none;
            border-color: #ced4da;
        }
        .input-group:focus-within {
            box-shadow: 0 0 0 0.25rem rgba(212, 175, 55, 0.25);
            border-radius: 0.375rem;
        }
    </style>
</head>
<body>

    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-6 d-flex justify-content-center">
                <div class="card card-custom p-4 p-md-5 animate__animated animate__fadeInUp">
                    
                    <div class="text-center mb-4">
                        <i class="fas fa-user-shield fa-3x mb-3" style="color: #d4af37;"></i>
                        <h3 class="card-title">Activar Cuenta</h3>
                        <?php if ($mostrar_formulario): ?>
                            <p class="text-muted small">Hola, <strong><?php echo htmlspecialchars($nombre_cliente); ?></strong>.<br>Crea una contraseña segura para acceder a tus servicios.</p>
                        <?php endif; ?>
                    </div>

                    <?php if ($mensaje): ?>
                        <div class="alert alert-<?php echo $tipo_alerta; ?> text-center shadow-sm border-0" role="alert">
                            <?php if ($tipo_alerta == 'success'): ?>
                                <i class="fas fa-check-circle fa-2x mb-2 text-success"></i><br>
                            <?php else: ?>
                                <i class="fas fa-exclamation-circle fa-2x mb-2 text-danger"></i><br>
                            <?php endif; ?>
                            <strong><?php echo $mensaje; ?></strong>
                        </div>
                        
                        <?php if ($tipo_alerta == 'success'): ?>
                            <div class="d-grid mt-4">
                                <a href="http://localhost/cusquena/frontend/pages/vistaCusquena.php" class="btn btn-dorado">
                                    <i class="fas fa-sign-in-alt me-2"></i> Ir a Iniciar Sesión
                                </a>
                            </div>
                        <?php endif; ?>
                    <?php endif; ?>

                    <?php if ($mostrar_formulario): ?>
                        <form method="POST" action="">
                            <div class="mb-4">
                                <label class="form-label fw-bold small text-uppercase text-muted">Nueva Contraseña</label>
                                <div class="input-group input-group-lg">
                                    <span class="input-group-text"><i class="fas fa-lock text-muted"></i></span>
                                    <input type="password" name="password" class="form-control" required placeholder="Mínimo 6 caracteres" minlength="6">
                                </div>
                            </div>

                            <div class="mb-4">
                                <label class="form-label fw-bold small text-uppercase text-muted">Confirmar Contraseña</label>
                                <div class="input-group input-group-lg">
                                    <span class="input-group-text"><i class="fas fa-check-double text-muted"></i></span>
                                    <input type="password" name="confirm_password" class="form-control" required placeholder="Repite la contraseña">
                                </div>
                            </div>

                            <div class="d-grid mt-4">
                                <button type="submit" class="btn btn-dorado">
                                    GUARDAR Y ACTIVAR
                                </button>
                            </div>
                        </form>
                    <?php endif; ?>

                    <div class="text-center mt-4 text-muted small">
                        &copy; 2025 Lubricentro La Cusqueña
                    </div>
                </div>
            </div>
        </div>
    </div>

</body>
</html>