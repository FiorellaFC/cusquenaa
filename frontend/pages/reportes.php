<?php
require_once '../../backend/includes/auth.php';
verificarPermiso(['Administrador', 'Secretaria']);

// Determinar el sidebar a cargar basado en el rol del usuario
$sidebar_path = '';
if (isset($_SESSION['rol'])) {
    if ($_SESSION['rol'] === 'Administrador') {
        $sidebar_path = 'sidebear_Admin.php';
    } elseif ($_SESSION['rol'] === 'Secretaria') {
        $sidebar_path = 'sidebear_secre.php';
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
    <meta name="description" content="Gestión de Balance - Lubricentro La Cusqueña" />
    <meta name="author" content="La Cusqueña" />
    <title>Lubricentro Cusqueña - Reporte</title>
    <link href="../css/bootstrap.css" rel="stylesheet" />
    <script src="https://use.fontawesome.com/releases/v6.3.0/js/all.js" crossorigin="anonymous"></script>
    <style>
        .container-fluid {
            padding-top: 0rem;
        }
        .card {
            border-radius: 0.75rem;
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        }
        .btn-primary {
            background-color: #00695c;
            border: none;
        }
        .btn-primary:hover {
            background-color: #004d40;
        }
    </style>
</head>
<body class="sb-nav-fixed">
    <!-- Barra superior -->
    <nav class="sb-topnav navbar navbar-expand navbar-dark bg-dark fixed-top">
        <a class="navbar-brand ps-3" href="<?php echo ($_SESSION['rol'] === 'Administrador' ? 'base.php' : 'base2.php'); ?>">La Cusqueña</a>
        <button class="btn btn-link btn-sm order-1 order-lg-0 me-4 me-lg-0" id="sidebarToggle">
            <i class="fas fa-bars"></i>
        </button>
        <ul class="navbar-nav ms-auto me-3 me-lg-4 text-end">
            <li class="nav-item dropdown">
                <a class="nav-link dropdown-toggle" id="navbarDropdown" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                    <i class="fas fa-user fa-fw"></i>
                </a>
                <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="navbarDropdown">
                    <li><a class="dropdown-item" href="../../index.html">Cerrar Sesión</a></li>
                </ul>
            </li>
        </ul>
    </nav>

    <div id="layoutSidenav">
        <!-- Sidebar dinámico -->
        <div id="layoutSidenav_nav">
            <?php if (!empty($sidebar_path)): ?>
            <script>
                fetch('<?php echo $sidebar_path; ?>')
                    .then(response => response.text())
                    .then(html => document.getElementById('layoutSidenav_nav').innerHTML = html)
                    .catch(error => console.error('Error cargando sidebar:', error));
            </script>
            <?php endif; ?>
        </div>

        <!-- Contenido -->
        <div id="layoutSidenav_content">
            <main class="container-xl col-10 mx-auto">
                <div class="container-fluid px-4">
                    <h1 class="text-center mb-4">Reportes</h1>

                    <div class="card p-4">
                        <form id="balanceForm" class="d-flex flex-wrap gap-3 align-items-center">
                            <label class="mb-0 fw-bold">Descargar PDF de balance:</label>
                            <select id="tipoBalance" name="tipo" class="form-select w-auto">
                                <option value="general">General</option>
                                <option value="lubricentro">Lubricentro</option>
                            </select>
                            <input type="date" id="fechaInicio" name="desde" class="form-control w-auto" />
                            <input type="date" id="fechaFin" name="hasta" class="form-control w-auto" />
                            <button type="submit" class="btn btn-primary">Descargar PDF</button>
                        </form>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <script src="../js/bootstrap.bundle.min.js"></script>
    <script>
        // Envío del formulario para generar PDF
        document.getElementById('balanceForm').addEventListener('submit', function(e) {
            e.preventDefault();

            const tipo = document.getElementById('tipoBalance').value;
            const desde = document.getElementById('fechaInicio').value;
            const hasta = document.getElementById('fechaFin').value;

            // Redirige a un PHP que genere el PDF
            window.location.href = `../../backend/reportes/balance_pdf.php?tipo=${tipo}&desde=${desde}&hasta=${hasta}`;
        });
    </script>
</body>
</html>
