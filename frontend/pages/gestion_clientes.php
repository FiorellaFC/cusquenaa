<?php
// require_once '../../backend/includes/auth.php';
// verificarPermiso(['Administrador']); 
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Gestión de Clientes - Lubricentro Cusqueña</title>
    
    <link href="../css/bootstrap.css" rel="stylesheet" />
    <link href="../css/dashboard.css" rel="stylesheet" />
    <link href="../css/sidebar.css" rel="stylesheet" />
    <link href="../css/navbar.css" rel="stylesheet" />
    <script src="https://use.fontawesome.com/releases/v6.3.0/js/all.js" crossorigin="anonymous"></script>
    <style>
        .table th, .table td { text-align: center; vertical-align: middle; }
        .page-link { color: #333; }
        .page-item.active .page-link { background-color: #212529; border-color: #212529; color: white; }
    </style>
</head>

<body class="sb-nav-fixed">
    <nav class="sb-topnav navbar navbar-expand navbar-dark bg-dark fixed-top">
        <a class="navbar-brand ps-3" href="base.php">La Cusqueña</a>
        <button class="btn btn-link btn-sm me-4" id="sidebarToggle"><i class="fas fa-bars"></i></button>
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
            <script>
                fetch('sidebear_Admin.php')
                    .then(r => r.text())
                    .then(html => document.getElementById('layoutSidenav_nav').innerHTML = html)
                    .catch(e => console.error('Error cargando sidebar:', e));
            </script>
        </div>

        <div id="layoutSidenav_content">
            <main class="container-xl my-4">
                <div class="container-fluid px-4">
                    <h1 class="mb-4 text-center">Gestión de Clientes</h1>

                    <div class="row mb-3 align-items-end">
                        <div class="col-md-4">
                            <label for="buscarDNI" class="form-label">Buscar por DNI/RUC:</label>
                            <input type="text" class="form-control" placeholder="DNI o RUC..." id="buscarDNI">
                        </div>
                        <div class="col-md-4">
                            <label for="buscarNombre" class="form-label">Buscar por Nombre:</label>
                            <input type="text" class="form-control" placeholder="Nombre del cliente..." id="buscarNombre">
                        </div>
                        <div class="col-md-4 d-flex justify-content-end align-items-center pt-4">
                            <button class="btn btn-primary me-2" id="btnBuscar"><i class="fas fa-search"></i> Buscar</button>
                            <button class="btn btn-secondary me-2" id="btnLimpiar"><i class="fas fa-times"></i> Limpiar</button>
                            <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#modalAgregarCliente">
                                <i class="fas fa-plus"></i> Agregar
                            </button>
                        </div>
                    </div>

                    <div class="table-responsive my-4">
                        <table id="tblClientes" class="table table-bordered table-hover text-center">
                            <thead class="table-dark">
                                <tr>
                                    <th style="width: 50px;">Tipo</th>
                                    <th>Nombre</th>
                                    <th>Apellido</th>
                                    <th>DNI/RUC</th>
                                    <th>Teléfono</th>
                                    <th>Correo</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody class="align-middle">
                                </tbody>
                        </table>
                    </div>

                    <nav aria-label="Paginación de clientes">
                        <ul class="pagination justify-content-center" id="paginationControls">
                            </ul>
                    </nav>

                </div>
            </main>
        </div>
    </div>

    <div class="modal fade" id="modalAgregarCliente" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title">Registrar Nuevo Cliente</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="formAgregarCliente">
                        <div class="row">
                            <div class="col-6 mb-3">
                                <label class="form-label fw-bold">Nombre:</label>
                                <input type="text" class="form-control" name="nombre" required>
                            </div>
                            <div class="col-6 mb-3">
                                <label class="form-label fw-bold">Apellido:</label>
                                <input type="text" class="form-control" name="apellido" required>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold">DNI/RUC:</label>
                            <input type="text" class="form-control" name="dni_ruc" required maxlength="11">
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold">Teléfono:</label>
                            <input type="tel" class="form-control" name="telefono" maxlength="9">
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold">Correo:</label>
                            <input type="email" class="form-control" name="email">
                        </div>
                        <div class="modal-footer justify-content-center">
                            <button type="submit" class="btn btn-success w-50">Guardar</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="modalEditarCliente" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header bg-warning text-dark">
                    <h5 class="modal-title">Actualizar Cliente</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="formEditarCliente">
                        <input type="hidden" name="id">
                        <div class="row">
                            <div class="col-6 mb-3">
                                <label class="form-label fw-bold">Nombre:</label>
                                <input type="text" class="form-control" name="nombre" required>
                            </div>
                            <div class="col-6 mb-3">
                                <label class="form-label fw-bold">Apellido:</label>
                                <input type="text" class="form-control" name="apellido" required>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold">DNI/RUC:</label>
                            <input type="text" class="form-control" name="dni_ruc" required maxlength="11">
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold">Teléfono:</label>
                            <input type="tel" class="form-control" name="telefono" maxlength="9">
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold">Correo:</label>
                            <input type="email" class="form-control" name="email">
                        </div>
                        <div class="modal-footer justify-content-center">
                            <button type="submit" class="btn btn-warning w-50">Actualizar</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="modalEliminarCliente" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title">Eliminar Cliente</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body text-center">
                    <p>¿Estás seguro? Esta acción no se puede deshacer.</p>
                </div>
                <div class="modal-footer justify-content-center">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-danger" id="btnConfirmarEliminar">Sí, Eliminar</button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="modalHistorialCliente" tabindex="-1" data-bs-backdrop="static">
        <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content border-0 shadow-lg">
                
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title">
                        <i class="fas fa-clipboard-list me-2"></i>
                        Historial de Citas: <span id="historialNombreCliente" class="fw-bold text-warning"></span>
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body bg-light">
                    
                    <div class="card mb-3 border-0 shadow-sm">
                        <div class="card-body p-2">
                            <label class="form-label small text-muted fw-bold ms-1">BUSCAR EN ESTA PÁGINA:</label>
                            <div class="input-group">
                                <span class="input-group-text bg-white border-end-0">
                                    <i class="fas fa-search text-primary"></i>
                                </span>
                                <input type="text" id="filtroHistorial" class="form-control border-start-0" 
                                       placeholder="Escribe una fecha (2025-11-28) o servicio..." autocomplete="off">
                                <button class="btn btn-outline-secondary" type="button" id="btnLimpiarHistorial">
                                    <i class="fas fa-times"></i> Borrar
                                </button>
                            </div>
                        </div>
                    </div>

                    <div class="table-responsive rounded shadow-sm">
                        <table class="table table-hover bg-white text-center mb-0" style="border:1px solid #dee2e6;">
                            <thead class="bg-dark text-white">
                                <tr>
                                    <th class="py-3">Fecha</th>
                                    <th class="py-3">Hora</th>
                                    <th class="py-3">Servicio Realizado</th>
                                    <th class="py-3">Precio</th>
                                    <th class="py-3">Estado</th>
                                </tr>
                            </thead>
                            <tbody id="tbodyHistorial">
                                </tbody>
                        </table>
                    </div>

                    <div class="d-flex justify-content-center mt-3">
                        <nav aria-label="Paginación historial">
                            <ul class="pagination mb-0" id="paginacionHistorial"></ul>
                        </nav>
                    </div>

                </div>
                
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary px-4" data-bs-dismiss="modal">Cerrar Ventana</button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="modalDetalleServicios" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-sm modal-dialog-centered" style="z-index: 1060;"> <div class="modal-content">
                <div class="modal-header bg-info text-white py-2">
                    <h6 class="modal-title fw-bold">Servicios Solicitados</h6>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-0">
                    <div id="listaServiciosDetalle"></div>
                </div>
                <div class="modal-footer py-1 justify-content-center">
                    <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                </div>
            </div>
        </div>
    </div>

    <script src="../js/bootstrap.bundle.min.js"></script>
    <script src="../js/functions/gestionClientes.js"></script>
</body>
</html>