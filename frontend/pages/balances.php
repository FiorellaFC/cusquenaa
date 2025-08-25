<?php
require_once '../../backend/includes/auth.php';
verificarPermiso(['Administrador', 'Secretaria']);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Lubricentro Cusqueña - Gestión de Balance</title>
    <link href="../css/bootstrap.css" rel="stylesheet" />
    <script src="https://use.fontawesome.com/releases/v6.3.0/js/all.js" crossorigin="anonymous"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.8.2/jspdf.plugin.autotable.min.js"></script>
</head>
<body class="sb-nav-fixed">
    <nav class="sb-topnav navbar navbar-expand navbar-dark bg-dark fixed-top">
        <?php if ($_SESSION['rol'] === 'Administrador'): ?>
        <a class="navbar-brand ps-3" href="base.php">La Cusqueña</a>
        <?php endif; ?>
        <?php if ($_SESSION['rol'] === 'Secretaria'): ?>
        <a class="navbar-brand ps-3" href="base2.php">La Cusqueña</a>
        <?php endif; ?>
        <button class="btn btn-link btn-sm me-4" id="sidebarToggle">
            <i class="fas fa-bars"></i>
        </button>
        <ul class="navbar-nav ms-auto me-3">
            <li class="nav-item dropdown">
                <a class="nav-link dropdown-toggle" id="navbarDropdown" href="#" role="button" data-bs-toggle="dropdown">
                    <i class="fas fa-user fa-fw"></i>
                </a>
                <ul class="dropdown-menu dropdown-menu-end">
                    <li><a class="dropdown-item" href="../../index.html">Cerrar Sesión</a></li>
                </ul>
            </li>
        </ul>
    </nav>
    <div id="layoutSidenav">
        <div id="layoutSidenav_nav">
            <?php if ($_SESSION['rol'] === 'Administrador'): ?>
            <script>
                fetch('sidebear_Admin.php')
                    .then(r => r.text())
                    .then(html => document.getElementById('layoutSidenav_nav').innerHTML = html)
                    .catch(e => console.error('Error cargando sidebar:', e));
            </script>
            <?php endif; ?>
            <?php if ($_SESSION['rol'] === 'Secretaria'): ?>
            <script>
                fetch('sidebear_secre.php')
                    .then(r => r.text())
                    .then(html => document.getElementById('layoutSidenav_nav').innerHTML = html)
                    .catch(e => console.error('Error cargando sidebar:', e));
            </script>
            <?php endif; ?>
        </div>
        <div id="layoutSidenav_content">
            <main class="container-xl my-2 col-10 mx-auto">
                <div class="container-fluid px-4">
                    <h1 class="mb-4 text-center">Gestión Balance</h1>
                    <div id="balanceContainer" class="my-4 p-3 border rounded">

                <div class="d-flex flex-wrap align-items-center gap-2">
                    <!-- Filtros -->
                         <!-- 📅 Fecha desde -->
                        <div class="d-flex align-items-center gap-1">
                            <label for="fechaDesde" class="form-label mb-0">Desde:</label>
                            <input type="date" class="form-control w-auto" id="fechaDesde">
                        </div>

                    <!-- 📅 Fecha hasta -->
                        <div class="d-flex align-items-center gap-1">
                            <label for="fechaHasta" class="form-label mb-0">Hasta:</label>
                            <input type="date" class="form-control w-auto" id="fechaHasta">
                        </div>
                    <!-- Botones de acciones -->
                     <button class="btn btn-primary" id="btnGeneral">General</button>
                     <button class="btn btn-success" id="btnLubricentros">Lubricentros</button>
                     <button class="btn btn-secondary" id="btnImprimir">Imprimir</button>
                     <button class="btn btn-info text-white" id="btnExportarPDF">Exportar PDF</button>
                </div>
        </div>

        <div class="total-container" id="totalGeneral">Total General: S/. 0.00</div>
        <div id="pagination-container">
            <nav aria-label="Navegación de páginas" class="d-flex justify-content-end">
                <ul class="pagination" id="pagination-list">
                    <li class="page-item disabled" id="pagination-prev">
                        <a class="page-link" href="#" aria-label="Anterior">
                            <span aria-hidden="true">&laquo;</span>
                        </a>
                    </li>
                    <li class="page-item" id="pagination-next">
                        <a class="page-link" href="#" aria-label="Siguiente">
                            <span aria-hidden="true">&raquo;</span>
                        </a>
                    </li>
                </ul>
            </nav>
        </div>

        </div>
    </main>
        </div>
    </div>
    <script src="../js/bootstrap.bundle.min.js"></script>

</html>