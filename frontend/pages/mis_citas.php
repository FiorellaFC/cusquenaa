<?php
session_start();
if (!isset($_SESSION['cliente_id'])) {
    header("Location: index.php");
    exit();
}

require_once "../../backend/includes/db.php"; 

$cliente_id = $_SESSION['cliente_id'];

try {
    $sql = "SELECT c.*, s.nombre as nombre_servicio, s.precio 
            FROM citas c 
            LEFT JOIN servicios s ON c.servicio_id = s.id 
            WHERE c.cliente_id = ? 
            ORDER BY c.fecha DESC, c.hora DESC";
    $stmt = $conn->prepare($sql);
    $stmt->execute([$cliente_id]);
    $citas = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $citas = [];
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Mis Citas - Lubricentro La Cusqueña</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://use.fontawesome.com/releases/v6.3.0/js/all.js" crossorigin="anonymous"></script>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">

    <style>
        /* FONDO CON IMAGEN Y OVERLAY */
        body {
            font-family: 'Poppins', sans-serif;
            padding-top: 80px;
            background: linear-gradient(rgba(0, 0, 0, 0.6), rgba(0, 0, 0, 0.6)),
                        url('../css/imagenes/img1.jpg') center center / cover no-repeat fixed;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        /* CONTENEDOR PRINCIPAL */
        .main-container {
            flex: 1; /* Empuja el footer hacia abajo si hay poco contenido */
            margin-bottom: 50px;
        }

        .card-custom {
            border: none;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.5);
            overflow: hidden;
            background-color: rgba(255, 255, 255, 0.95); /* Fondo blanco semitransparente */
            backdrop-filter: blur(5px);
        }

        .card-header-custom {
            background-color: #1a1b1e;
            color: #d4af37;
            padding: 20px;
            border-bottom: 4px solid #d4af37;
        }
        
        .header-title {
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin: 0;
        }

        /* BADGES DE ESTADO */
        .badge-pendiente { background-color: #ffc107; color: #000; }
        .badge-confirmada { background-color: #198754; color: #fff; } /* Verde éxito para confirmada */
        .badge-completada { background-color: #0d6efd; color: #fff; } /* Azul para completada */
        .badge-cancelada { background-color: #dc3545; color: #fff; }

        /* FOOTER */
        footer {
            background-color: #1a1b1e;
            color: #fff;
            text-align: center;
            padding: 20px 0;
            margin-top: auto; /* Se pega al fondo */
        }
    </style>
</head>
<body>

    <?php include 'navbarcusquena.php'; ?>

    <div class="container main-container mt-5">
        <div class="card card-custom">
            
            <div class="card-header-custom d-flex justify-content-between align-items-center">
                <h3 class="header-title"><i class="fas fa-calendar-alt me-2"></i> Mis Citas</h3>
                <a href="gestionCitas.php" class="btn btn-outline-light btn-sm border-warning text-warning fw-bold">
                    <i class="fas fa-plus me-1"></i> Nueva Reserva
                </a>
            </div>

            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover table-striped mb-0 align-middle">
                        <thead class="table-dark text-center">
                            <tr>
                                <th class="py-3">Fecha</th>
                                <th class="py-3">Hora</th>
                                <th class="py-3">Servicio</th>
                                <th class="py-3">Precio Est.</th>
                                <th class="py-3">Estado</th>
                            </tr>
                        </thead>
                        <tbody class="text-center">
                            <?php if (count($citas) > 0): ?>
                                <?php foreach ($citas as $cita): ?>
                                    <tr>
                                        <td class="fw-bold"><?php echo date("d/m/Y", strtotime($cita['fecha'])); ?></td>
                                        <td><?php echo substr($cita['hora'], 0, 5); ?></td>
                                        <td><?php echo htmlspecialchars($cita['nombre_servicio'] ?? 'No especificado'); ?></td>
                                        <td>S/. <?php echo number_format($cita['precio'] ?? 0, 2); ?></td>
                                        <td>
                                            <?php 
                                                $estado = $cita['estado'];
                                                $badge = 'badge-secondary';
                                                if ($estado == 'pendiente') $badge = 'badge-pendiente';
                                                if ($estado == 'confirmada') $badge = 'badge-confirmada';
                                                if ($estado == 'completada') $badge = 'badge-completada';
                                                if ($estado == 'cancelada') $badge = 'badge-cancelada';
                                            ?>
                                            <span class="badge rounded-pill <?php echo $badge; ?> px-3 py-2 shadow-sm">
                                                <?php echo strtoupper($estado); ?>
                                            </span>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="5" class="py-5 text-muted bg-white">
                                        <div class="py-4">
                                            <i class="fas fa-folder-open fa-4x mb-3 text-secondary opacity-50"></i><br>
                                            <h5 class="fw-bold text-secondary">No tienes citas registradas</h5>
                                            <p class="small mb-0">Tus futuras reservas aparecerán aquí.</p>
                                        </div>
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
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