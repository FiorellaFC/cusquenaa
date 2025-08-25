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
            max-width: 200px; /* Asegura un ancho máximo para el contenido */
            padding: 10px;
        }
        .table td button {
            margin: 0 5px;
            white-space: nowrap;
        }
        .table-responsive {
            overflow-x: auto; /* Permite scroll horizontal en tablas grandes */
        }
        @media (max-width: 576px) {
            .table th,
            .table td {
                font-size: 14px;
                max-width: 150px;
            }
        }
        .total-container {
            margin-top: 15px; /* Añadido un poco de margen superior */
            margin-bottom: 10px;
            font-weight: bold;
            text-align: right;
            padding-right: 15px; /* Espacio para que no se pegue al borde */
        }
        .btn-custom {
            background-color: #f0f0f0;
            border: 1px solid #ccc;
            color: #333;
            padding: 6px 12px;
            font-size: 14px;
            border-radius: 4px;
            text-decoration: none;
            display: inline-block; /* Para que margin-right funcione si los elementos no son botones */
        }
        .btn-custom:hover {
            background-color: #e0e0e0;
            border-color: #999;
            color: #000;
        }
        /* Estilos específicos para impresión */
        @media print {
            body {
                margin: 0;
                padding: 0;
                background-color: #fff;
            }
            /* Oculta elementos que no queremos imprimir */
            .sb-topnav,
            #layoutSidenav_nav,
            .btn,
            .form-control,
            .modal,
            .toast-container,
            .pagination,
            .col-12.d-flex.justify-content-between.align-items-center,
            h1
            {
                display: none !important;
            }

            /* Muestra solo el contenido principal de la tabla */
            #layoutSidenav_content {
                margin-left: 0 !important;
                width: 100% !important;
            }

            main.container-xl {
                margin: 0 !important;
                padding: 20px !important;
                width: 100% !important;
                max-width: none !important;
            }

            .table-responsive {
                overflow: visible !important;
            }

            .table {
                width: 100%;
                border-collapse: collapse;
                /* font-size: 12px; */ /* <-- Comentado o eliminado */
                font-size: 14px; /* <--- Aumentado el tamaño de la fuente para toda la tabla */
            }

            .table th,
            .table td {
                border: 1px solid #dee2e6;
                /* padding: 8px; */ /* <-- Comentado o eliminado */
                padding: 10px; /* <--- Aumentado el padding para más espacio en las celdas */
                text-align: left;
                max-width: none;
            }

            /* Asegura que las acciones de la tabla no aparezcan en la impresión */
            .table td:nth-child(5) {
                display: none !important;
            }

            /* Asegura que el contenedor del total se vea bien */
            .total-container {
                text-align: right !important;
                margin-top: 20px !important;
                padding-right: 0 !important;
                font-size: 18px; /* <--- También puedes hacer el total más grande */
            }
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
                    <h1 class="mb-4 text-center">Gestión Balance de Empresa</h1>
                    <div class="row">
                        <div class="col-12 d-flex justify-content-between align-items-center">
                            <div class="d-flex">
                                <input type="text" class="form-control me-2" id="buscarNombre" placeholder="Nombre">
                                <select class="form-control me-2" id="buscarMes" style="width: 120px;">
                                    <option value="">Mes</option>
                                    <option value="01">Enero</option>
                                    <option value="02">Febrero</option>
                                    <option value="03">Marzo</option>
                                    <option value="04">Abril</option>
                                    <option value="05">Mayo</option>
                                    <option value="06">Junio</option>
                                    <option value="07">Julio</option>
                                    <option value="08">Agosto</option>
                                    <option value="09">Septiembre</option>
                                    <option value="10">Octubre</option>
                                    <option value="11">Noviembre</option>
                                    <option value="12">Diciembre</option>
                                </select>
                                <input type="number" class="form-control me-2" id="buscarAnio" placeholder="Año" style="width: 100px;">
                                <select class="form-select form-select-sm" id="filterTipoBalance">
                                    <option value="">Tipo de Balance</option>
                                    <option value="Cotizaciones">Cotizaciones</option>
                                    <option value="Prestamos">Prestamos</option>
                                    <option value="Alquileres">Alquileres</option>
                                    <option value="Gastos">Gastos</option>
                                    <option value="Dominical">Dominical</option>
                                    <option value="Coordinadores">Coordinadores</option>
                                    </select>
                                <button class="btn btn-primary" id="btnBuscar">Buscar</button>
                            </div>
                            <div>
                                <button class="btn btn-secondary me-2" id="btnImprimir">Imprimir</button>
                                <button class="btn btn-info text-white me-2" id="btnExportarPDF">Exportar PDF</button>
                                <?php if ($_SESSION['rol'] === 'Administrador'): ?>
                                <a href="#" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalAgregarBalance">Agregar Balance</a>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <div class="modal fade" id="modalAgregarBalance" tabindex="-1" aria-labelledby="modalAgregarBalanceLabel" aria-hidden="true">
                        <div class="modal-dialog modal-dialog-centered">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h3 class="modal-title" id="modalAgregarBalanceLabel">Registro de Balance</h3>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                                </div>
                                <div class="modal-body">
                                    <form id="formAgregarBalance">
                                        <div class="mb-3">
                                            <label for="nombre" class="form-label fw-bold">Nombre / Descripción:</label>
                                            <input type="text" class="form-control" id="nombre" name="nombre" required placeholder="Ej: Balance Mensual, Cierre de Caja">
                                        </div>
                                        <div class="mb-3">
                                            <label for="tipoBalance" class="form-label fw-bold">Tipo de Balance:</label>
                                            <select class="form-select" id="tipoBalance" name="tipoBalance" required>
                                                <option value="" disabled selected>Seleccione un tipo</option>
                                                <option value="Cotizaciones">Cotizaciones</option>
                                                <option value="Prestamos">Prestamos</option>
                                                <option value="Alquileres">Alquileres</option>
                                                <option value="Gastos">Gastos</option>
                                                <option value="Dominical">Dominical</option>
                                                <option value="Coordinadores">Coordinadores</option>
                                                </select>
                                        </div>
                                        <div class="mb-3">
                                            <label for="mes" class="form-label fw-bold">Mes:</label>
                                            <input type="month" class="form-control" id="mes" name="mes" required>
                                        </div>
                                        <div class="mb-3">
                                            <label for="monto" class="form-label fw-bold">Monto:</label>
                                            <input type="number" step="0.01" class="form-control" id="monto" name="monto" required>
                                        </div>
                                        <div class="modal-footer d-flex justify-content-center">
                                            <button type="submit" class="btn btn-primary">Agregar Balance</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="modal fade" id="modalEditarBalance" tabindex="-1" aria-labelledby="modalEditarBalanceLabel" aria-hidden="true">
                        <div class="modal-dialog modal-dialog-centered">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h3 class="modal-title" id="modalEditarBalanceLabel">Editar Balance</h3>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                                </div>
                                <div class="modal-body">
                                    <form id="formEditarBalance">
                                        <input type="hidden" id="editBalanceId" name="id">
                                        <div class="mb-3">
                                            <label for="edit_nombre" class="form-label fw-bold">Nombre / Descripción:</label>
                                            <input type="text" class="form-control" id="edit_nombre" name="nombre" required>
                                        </div>
                                        <div class="mb-3">
                                            <label for="edit_tipoBalance" class="form-label fw-bold">Tipo de Balance:</label>
                                            <select class="form-select" id="edit_tipoBalance" name="tipoBalance" required>
                                                <option value="Cotizaciones">Cotizaciones</option>
                                                <option value="Prestamos">Prestamos</option>
                                                <option value="Alquileres">Alquileres</option>
                                                <option value="Gastos">Gastos</option>
                                                <option value="Dominical">Dominical</option>
                                                <option value="Coordinadores">Coordinadores</option>
                                                </select>
                                        </div>
                                        <div class="mb-3">
                                            <label for="edit_mes" class="form-label fw-bold">Mes:</label>
                                            <input type="month" class="form-control" id="edit_mes" name="mes" required>
                                        </div>
                                        <div class="mb-3">
                                            <label for="edit_monto" class="form-label fw-bold">Monto:</label>
                                            <input type="number" step="0.01" class="form-control" id="edit_monto" name="monto" required>
                                        </div>
                                        <div class="modal-footer d-flex justify-content-center">
                                            <button type="submit" class="btn btn-primary">Guardar Cambios</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="modal fade" id="eliminarModalBalance" tabindex="-1" aria-labelledby="eliminarModalBalanceLabel" aria-hidden="true">
                        <div class="modal-dialog modal-dialog-centered">
                            <div class="modal-content">
                                <div class="modal-header bg-danger text-white"> <h5 class="modal-title" id="eliminarModalBalanceLabel">¿Confirmar Eliminación?</h5> <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button> </div>
                                <div class="modal-body">
                                    ¿Estás seguro de que deseas eliminar este registro de balance? Esta acción no se puede deshacer.
                                    </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                                    <button type="button" class="btn btn-danger" id="confirmarEliminarBalance">Eliminar</button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="toast-container position-fixed bottom-0 end-0 p-3" style="z-index: 1050;"> <div id="toastSuccess" class="toast text-white bg-success" role="alert" aria-live="assertive" aria-atomic="true" data-bs-delay="3000">
                        <div class="d-flex">
                            <div class="toast-body" id="toastSuccessBody">
                                </div>
                            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
                        </div>
                    </div>

                    <div id="toastError" class="toast text-white bg-danger" role="alert" aria-live="assertive" aria-atomic="true" data-bs-delay="5000">
                        <div class="d-flex">
                            <div class="toast-body" id="toastErrorBody">
                                </div>
                            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
                        </div>
                    </div>
                    </div>

                    <div class="table-responsive my-4">
                        <table class="table table-striped table-bordered" id="tablaBalance"> <thead class="table-dark">
                                <tr>
                                    <th scope="col">Nombre / Descripción</th>
                                    <th scope="col">Tipo de Balance</th>
                                    <th scope="col">Mes</th>
                                    <th scope="col">Monto</th>
                                    <th scope="col">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                </tbody>
                        </table>
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
    <script src="../js/functions/balanceEmpresa.js"></script> </body>

</html>