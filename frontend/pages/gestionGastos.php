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
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
    <title>Lubricentro Cusqueña - Gestión de Gastos de Empresa</title>
    <link href="../css/bootstrap.css" rel="stylesheet" />
    <script src="https://use.fontawesome.com/releases/v6.3.0/js/all.js" crossorigin="anonymous"></script>
    <style>
        /* Estilos específicos para la tabla y modales */
        .table {
            width: 100%;
            table-layout: auto;
            word-wrap: break-word;
        }
        .table th,
        .table td {
            text-align: center;
            vertical-align: middle;
            padding: 10px;
        }
        .table td button {
            margin: 0 3px;
            white-space: nowrap;
        }
        .table-responsive {
            overflow-x: auto;
        }
        /* Ajustes para pantallas pequeñas */
        @media (max-width: 768px) {
            .table th,
            .table td {
                font-size: 13px;
                max-width: 140px; /* Limita el ancho de las celdas para evitar desbordamiento */
            }
            .form-control.me-2 {
                margin-right: 0.5rem !important;
                margin-bottom: 0.5rem; /* Espaciado adicional para mejor visualización en móvil */
            }
            .d-flex.align-items-center {
                flex-direction: column; /* Apila elementos de filtro en pantallas pequeñas */
                align-items: stretch !important;
            }
            .d-flex.align-items-center .form-control,
            .d-flex.align-items-center .btn {
                width: 100%;
            }
            .d-flex.justify-content-between {
                flex-direction: column;
                gap: 1rem; /* Espacio entre los bloques de filtro y el botón agregar */
            }
        }
        .form-label.fw-bold {
            margin-bottom: 0.5rem;
        }
        /* Style for the total general text */
        #totalGeneral {
            font-size: 1.25rem; /* Larger font size */
            font-weight: bold;  /* Bold text */
            color: #111312ff;    /* Green color for emphasis (Bootstrap's success green) */
            margin-top: 1rem;   /* Space above */
            text-align: right;  /* Align to the right */
            display: block;     /* Ensure it takes full width to align right */
            padding-right: 15px; /* Adjust padding if needed to align with table/container */
        }
        #btnResetSearch {
            display: none;
        }

    </style>
</head>
<body class="sb-nav-fixed">
    <nav class="sb-topnav navbar navbar-expand navbar-dark bg-dark fixed-top">
        <a class="navbar-brand ps-3" href="<?php echo ($_SESSION['rol'] === 'Administrador' ? 'base.php' : 'base2.php'); ?>">La Cusqueña</a>
        <button class="btn btn-link btn-sm order-1 order-lg-0 me-4 me-lg-0" id="sidebarToggle"><i class="fas fa-bars"></i></button>
        <ul class="navbar-nav ms-auto me-3 me-lg-4 text-end">
            <li class="nav-item dropdown">
                <a class="nav-link dropdown-toggle" id="navbarDropdown" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false"><i class="fas fa-user fa-fw"></i></a>
                <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="navbarDropdown">
                    <li><a class="dropdown-item" href="../../index.html">Cerrar Sesión</a></li>
                </ul>
            </li>
        </ul>
    </nav>
    <div id="layoutSidenav">
        <div id="layoutSidenav_nav">
            <?php if (!empty($sidebar_path)): ?>
            <script>
                // Carga dinámica del sidebar basada en el rol
                fetch('<?php echo $sidebar_path; ?>')
                    .then(response => response.text())
                    .then(html => document.getElementById('layoutSidenav_nav').innerHTML = html)
                    .catch(error => console.error('Error cargando sidebar:', error));
            </script>
            <?php endif; ?>
        </div>
        <div id="layoutSidenav_content">
            <main class="container-xl my-2 col-11 mx-auto">
                <div class="container-fluid px-4">
                    <h1 class="mt-4 text-center mb-4">Gestión de Gastos</h1>

                    <div class="row">
                        <div class="col-12 d-flex flex-column flex-md-row justify-content-between align-items-center">
                            <div class="d-flex flex-column flex-md-row align-items-center mb-3 mb-md-0">
                                <label for="filterFechaInicio" class="me-2">Desde:</label>
                                <input type="date" id="filterFechaInicio" class="form-control me-2">
                                <label for="filterFechaFin" class="me-2">Hasta:</label>
                                <input type="date" id="filterFechaFin" class="form-control me-2">
                                <input type="text" class="form-control me-2" id="filterConcepto" placeholder="Buscar por concepto">
                                <button class="btn btn-primary me-2" id="btnBuscarGastos">Listar</button>
                                <button class="btn btn-secondary" id="btnResetSearch">Restablecer</button>
                            </div>
                           <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalAgregar">
                            <i class="fas fa-plus-circle me-2"></i>Registrar Nuevo Gasto
                           </button>
                        </div>
                    </div>

                    <div class="modal fade" id="modalAgregar" tabindex="-1" aria-labelledby="modalAgregarLabel" aria-hidden="true">
                        <div class="modal-dialog modal-dialog-centered">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h3 class="modal-title w-100 text-center" id="modalAgregarLabel">Registro de Gasto</h3>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                                </div>
                                <div class="modal-body">
                                    <form id="formAgregarGasto">
                                        <div class="mb-3">
                                            <label for="addConcepto" class="form-label fw-bold">Concepto:</label>
                                            <input type="text" class="form-control" id="addConcepto" name="concepto" required>
                                        </div>
                                        <div class="mb-3">
                                            <label for="monto" class="form-label fw-bold">Monto:</label>
                                            <input type="number" step="0.01" class="form-control" id="addMonto" name="monto" required>
                                        </div>
                                        <div class="mb-3">
                                            <label for="fecha" class="form-label fw-bold">Fecha:</label>
                                            <input type="date" class="form-control" id="addFecha" name="fecha" required>
                                        </div>
                                        <div class="mb-3">
                                            <label for="addObservaciones" class="form-label fw-bold">Observaciones:</label>
                                            <textarea class="form-control" id="addObservaciones" name="observaciones" rows="3" required></textarea>
                                        </div>
                                        <div class="modal-footer d-flex justify-content-center">
                                            <button type="submit" class="btn btn-primary">Agregar</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal fade" id="modalEditar" tabindex="-1" aria-labelledby="modalEditarLabel" aria-hidden="true">
                        <div class="modal-dialog modal-dialog-centered">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h3 class="modal-title w-100 text-center" id="modalEditarLabel">Actualización de Gasto</h3>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                                </div>
                                <div class="modal-body">
                                    <form id="formEditarGasto">
                                        <input type="hidden" id="editGastoId" name="id">
                                        <div class="mb-3">
                                            <label for="editConcepto" class="form-label fw-bold">Concepto:</label>
                                            <input type="text" class="form-control" id="editConcepto" name="concepto" required>
                                        </div>
                                        <div class="mb-3">
                                            <label for="editMonto" class="form-label fw-bold">Monto:</label>
                                            <input type="number" step="0.01" class="form-control" id="editMonto" name="monto" required>
                                        </div>
                                        <div class="mb-3">
                                            <label for="editFecha" class="form-label fw-bold">Fecha:</label>
                                            <input type="date" class="form-control" id="editFecha" name="fecha" required>
                                        </div>
                                        <div class="mb-3">
                                            <label for="editObservaciones" class="form-label fw-bold">Observaciones:</label>
                                            <textarea class="form-control" id="editObservaciones" name="observaciones" rows="3" required></textarea>
                                        </div>
                                        <div class="modal-footer d-flex justify-content-center">
                                            <button type="submit" class="btn btn-success">Guardar Cambios</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="table-responsive my-4">
                        <table class="table table-striped table-bordered table-hover text-center">
                            <thead class="table-dark">
                                <tr>
                                    <th>ID</th>
                                    <th>Concepto</th>
                                    <th>Monto</th>
                                    <th>Fecha</th>
                                    <th>Observaciones</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody id="tablaGastos" class="align-middle">
                                </tbody>
                        </table>
                    </div>

                    <div class="d-flex justify-content-end mb-3">
                        <span id="totalGeneral"></span>
                    </div>
                    
                    <nav aria-label="Page navigation example" class="d-flex justify-content-end">
                        <ul class="pagination" id="pagination">
                            </ul>
                    </nav>
                </div>
            </main>
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

    <div class="modal fade" id="modalEliminarConfirmacion" tabindex="-1" aria-labelledby="modalEliminarLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title" id="modalEliminarLabel">¿Confirmar Eliminación?</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>
                <div class="modal-body">
                    ¿Estás seguro de que deseas eliminar este gasto? Esta acción no se puede deshacer.
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-danger" id="btnConfirmarEliminar">Eliminar</button>
                </div>
            </div>
        </div>
    </div>
        
    <script src="../js/bootstrap.bundle.min.js"></script>
    <script src="../js/scripts.js"></script> <script src="../js/functions/gestionGastosEmpresa.js"></script>
</body>
</html>