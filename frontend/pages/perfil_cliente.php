<?php
session_start();
// Verificar sesión
if (!isset($_SESSION['cliente_id'])) {
    header("Location: index.php");
    exit();
}

require_once "../../backend/includes/db.php"; 

$msg = "";
$msg_type = "";

// 1. PROCESAR ACTUALIZACIÓN DE DATOS
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = $_POST['nombre'];
    $apellido = $_POST['apellido'];
    $telefono = $_POST['telefono'];
    
    try {
        $stmt = $conn->prepare("UPDATE clientes SET nombre=?, apellido=?, telefono=? WHERE id=?");
        if ($stmt->execute([$nombre, $apellido, $telefono, $_SESSION['cliente_id']])) {
            $msg = "Datos actualizados correctamente.";
            $msg_type = "success";
            
            // Actualizar la sesión para que el Navbar cambie inmediatamente
            $_SESSION['cliente_nombre'] = $nombre . ' ' . $apellido;
            $_SESSION['cliente_data']['nombre'] = $nombre;
            $_SESSION['cliente_data']['apellido'] = $apellido;
            $_SESSION['cliente_data']['telefono'] = $telefono;
        }
    } catch (Exception $e) {
        $msg = "Error al actualizar los datos.";
        $msg_type = "danger";
    }
}

// Obtener datos actuales de la sesión
$u = $_SESSION['cliente_data'];
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Mi Perfil - Lubricentro La Cusqueña</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://use.fontawesome.com/releases/v6.3.0/js/all.js" crossorigin="anonymous"></script>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">

    <style>
        /* FONDO CON IMAGEN Y OVERLAY */
        body {
            font-family: 'Poppins', sans-serif;
            padding-top: 80px;
            background: linear-gradient(rgba(0, 0, 0, 0.7), rgba(0, 0, 0, 0.7)),
                        url('../css/imagenes/img1.jpg') center center / cover no-repeat fixed;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        /* CONTENEDOR PRINCIPAL */
        .main-container {
            flex: 1;
            display: flex;
            align-items: center; /* Centrar verticalmente */
            justify-content: center;
            margin-bottom: 50px;
        }

        /* TARJETA ESTILIZADA */
        .card-custom {
            border: none;
            border-radius: 15px;
            box-shadow: 0 15px 35px rgba(0,0,0,0.5);
            overflow: hidden;
            background-color: rgba(255, 255, 255, 0.95); /* Blanco semitransparente */
            backdrop-filter: blur(10px);
            width: 100%;
            max-width: 700px;
        }

        .card-header-custom {
            background-color: #1a1b1e;
            color: #d4af37;
            padding: 30px 20px;
            text-align: center;
            border-bottom: 4px solid #d4af37;
        }

        .profile-icon {
            font-size: 4rem;
            margin-bottom: 15px;
            color: #d4af37;
        }

        /* INPUTS */
        .form-label { font-weight: 600; color: #333; }
        .form-control:focus { border-color: #d4af37; box-shadow: 0 0 0 0.25rem rgba(212, 175, 55, 0.25); }
        
        /* Input de solo lectura (DNI/Email) */
        .form-control[readonly] {
            background-color: #e9ecef;
            opacity: 1;
            color: #6c757d;
            border-color: #ced4da;
        }

        /* BOTÓN GUARDAR */
        .btn-guardar {
            background-color: #1a1b1e;
            color: #d4af37;
            border: 2px solid #d4af37;
            padding: 10px 30px;
            font-weight: bold;
            text-transform: uppercase;
            transition: all 0.3s;
        }
        .btn-guardar:hover {
            background-color: #d4af37;
            color: #000;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(212, 175, 55, 0.3);
        }

        /* FOOTER */
        footer {
            background-color: #1a1b1e;
            color: #fff;
            text-align: center;
            padding: 20px 0;
            margin-top: auto;
        }
    </style>
</head>
<body>

    <?php include 'navbarcusquena.php'; ?>

    <div class="container main-container mt-4">
        
        <div class="card card-custom">
            
            <div class="card-header-custom">
                <i class="fas fa-user-circle profile-icon"></i>
                <h2 class="fw-bold text-uppercase mb-0">Mi Perfil</h2>
                <p class="text-white-50 mb-0 mt-2 small"><?php echo htmlspecialchars($u['email']); ?></p>
            </div>
            
            <div class="card-body p-4 p-md-5">
                
                <?php if ($msg): ?>
                    <div class="alert alert-<?php echo $msg_type; ?> alert-dismissible fade show shadow-sm mb-4" role="alert">
                        <i class="fas <?php echo ($msg_type == 'success') ? 'fa-check-circle' : 'fa-exclamation-circle'; ?> me-2"></i>
                        <?php echo $msg; ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <form method="POST">
                    
                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Nombres</label>
                            <input type="text" name="nombre" class="form-control form-control-lg" value="<?php echo htmlspecialchars($u['nombre']); ?>" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Apellidos</label>
                            <input type="text" name="apellido" class="form-control form-control-lg" value="<?php echo htmlspecialchars($u['apellido']); ?>" required>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Teléfono / Celular</label>
                        <div class="input-group">
                            <span class="input-group-text bg-white"><i class="fas fa-phone text-muted"></i></span>
                            <input type="tel" name="telefono" class="form-control form-control-lg" value="<?php echo htmlspecialchars($u['telefono']); ?>" required maxlength="9" oninput="this.value = this.value.replace(/[^0-9]/g, '');">
                        </div>
                    </div>

                    <div class="mb-4">
                        <label class="form-label">Documento de Identidad (DNI / RUC)</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light"><i class="fas fa-id-card text-muted"></i></span>
                            <input type="text" class="form-control form-control-lg" value="<?php echo htmlspecialchars($u['dni']); ?>" readonly>
                            <span class="input-group-text bg-light text-muted small" style="font-size: 0.8rem;"><i class="fas fa-lock me-1"></i> No editable</span>
                        </div>
                    </div>

                    <div class="d-grid">
                        <button type="submit" class="btn btn-guardar">
                            <i class="fas fa-save me-2"></i> Guardar Cambios
                        </button>
                    </div>

                </form>
            </div>
        </div>
    </div>

    <footer>
        <div class="container">
            <small>© 2025 Lubricentro La Cusqueña — Todos los derechos reservados</small>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>