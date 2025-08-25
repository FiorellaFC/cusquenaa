<?php
require_once '../../backend/includes/auth.php';
verificarPermiso(['Administrador', 'Secretaria']);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Lubricentro Cusqueña - Gestión de Préstamos</title>
    <link href="../css/bootstrap.css" rel="stylesheet" />
    <script src="https://use.fontawesome.com/releases/v6.3.0/js/all.js" crossorigin="anonymous"></script>
    <style>
        .table {
            width: 100%;
            table-layout: auto;
        }
        .table th,
        .table td {
            text-align: center;
            vertical-align: middle;
            overflow-wrap: break-word;
            max-width: 200px;
            padding: 10px;
        }
        .table td button {
            margin: 0 5px;
            white-space: nowrap;
        }
        .table-responsive {
            overflow-x: auto;
        }
        @media (max-width: 576px) {
            .table th,
            .table td {
                font-size: 14px;
                max-width: 150px;
            }
        }
        .total-container {
            text-align: right;
            margin-top: 1rem;
            font-size: 1.25rem;
            font-weight: bold;
        }
        .total-item {
            margin-left: 20px;
        }
        /* Estilos para achicar los formularios en modales */
        .modal-body .form-control-sm {
            padding: 0.25rem 0.5rem;
            font-size: 0.875rem;
            border-radius: 0.2rem;
        }
        .modal-body .btn-sm {
            padding: 0.25rem 0.5rem;
            font-size: 0.875rem;
            border-radius: 0.2rem;
        }
        .modal-header h5 {
            font-size: 1.25rem;
        }
    </style>
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
                    <h1 class="mb-4 text-center">Gestión de Préstamos</h1>

                    <div class="d-flex flex-wrap justify-content-end align-items-center gap-3 mb-4">
                        <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#modalRegistroPrestamo">
                            <i class="fas fa-plus-circle me-2"></i> Registrar Préstamo
                        </button>
                    </div>

                    <!-- Contenedor donde se mostrará la tabla del plan de pagos de forma permanente -->
                    <div id="planPagosContainer" class="my-4 p-3 border rounded">
                        <h2 class="text-start">Plan de Pagos</h2>
                        <form id="formPlan" class="d-flex align-items-center gap-2">
                            <label for="prestamo_id" class="form-label mb-0">ID Préstamo:</label>
                            <input id="prestamo_id" type="number" class="form-control form-control-sm w-auto" placeholder="ID Préstamo">
                            <button id="btnVerPlan" type="submit" class="btn btn-info text-white btn-sm">Ver plan</button>
                        </form>
                    </div>

                    <div class="table-responsive my-4">
                        <table class="table table-striped table-bordered" id="tbl">
                            <thead class="table-dark">
                                <tr>
                                    <th>Cuota</th>
                                    <th>Fecha</th>
                                    <th>Capital</th>
                                    <th>Interés (%)</th>
                                    <th>Pagado</th>
                                    <th>Pago</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                            </tbody>
                        </table>
                    </div>
                    
                    <div class="total-container">
                        <span>Monto Deuda: <span id="totalMontoDeuda">0.00</span></span>
                        <span class="total-item">Saldo Pendiente: <span id="totalSaldoPendiente">0.00</span></span>
                    </div>
                    <nav aria-label="Page navigation example" class="d-flex justify-content-end">
                        <ul class="pagination">
                            <li class="page-item">
                                <a class="page-link" href="#" aria-label="Previous">
                                    <span aria-hidden="true">«</span>
                                </a>
                            </li>
                            <li class="page-item"><a class="page-link" href="#">1</a></li>
                            <li class="page-item"><a class="page-link" href="#">2</a></li>
                            <li class="page-item"><a class="page-link" href="#">3</a></li>
                            <li class="page-item">
                                <a class="page-link" href="#" aria-label="Next">
                                    <span aria-hidden="true">»</span>
                                </a>
                            </li>
                        </ul>
                    </nav>
                </div>
            </main>
        </div>
    </div>
    
    <div class="modal fade" id="modalRegistroPrestamo" tabindex="-1" aria-labelledby="modalRegistroPrestamoLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalRegistroPrestamoLabel">Registro de Préstamo</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="formAgregar">
                        <div class="mb-3">
                            <label for="trabajador_nombre" class="form-label fw-bold">Nombre Trabajador:</label>
                            <input type="text" class="form-control form-control-sm" id="trabajador_nombre" name="trabajador_nombre" required>
                        </div>
                        <div class="mb-3">
                            <label for="monto" class="form-label fw-bold">Monto:</label>
                            <input type="number" step="0.01" class="form-control form-control-sm" id="monto" name="monto" required>
                        </div>
                        <div class="mb-3">
                            <label for="interes_porcentaje" class="form-label fw-bold">% Interés:</label>
                            <input type="number" step="0.01" class="form-control form-control-sm" id="interes_porcentaje" name="interes_porcentaje" required>
                        </div>
                        <div class="mb-3">
                            <label for="cuotas" class="form-label fw-bold">Cuotas:</label>
                            <input type="number" class="form-control form-control-sm" id="cuotas" name="cuotas" required>
                        </div>
                        <div class="mb-3">
                            <label for="fecha_inicio" class="form-label fw-bold">Fecha de Inicio:</label>
                            <input type="date" class="form-control form-control-sm" id="fecha_inicio" name="fecha_inicio" required>
                        </div>
                        <div class="d-grid mt-4">
                            <button type="submit" class="btn btn-primary btn-sm">Crear</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <div class="toast-container position-fixed bottom-0 end-0 p-3">
        <div id="toastSuccess" class="toast align-items-center border-0" role="alert" aria-live="assertive" aria-atomic="true">
            <div class="d-flex">
                <div class="toast-body text-white bg-success" id="toastSuccessBody">
                </div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
            </div>
        </div>
        <div id="toastError" class="toast align-items-center border-0" role="alert" aria-live="assertive" aria-atomic="true">
            <div class="d-flex">
                <div class="toast-body text-white bg-danger" id="toastErrorBody">
                </div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
            </div>
        </div>
    </div>

    <script src="../js/bootstrap.bundle.min.js"></script>
  
</body>
</html>
