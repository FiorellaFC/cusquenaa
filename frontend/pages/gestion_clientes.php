<?php
//require_once '../../backend/includes/auth.php';
// verificarPermiso(['Administrador']); // Puedes descomentar esto si necesitas permisos específicos
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Gestión de Clientes - Lubricentro Cusqueña</title>
    
    <!-- Rutas de CSS (basadas en tu plantilla de Usuarios) -->
    <link href="../css/bootstrap.css" rel="stylesheet" />
    <link href="../css/dashboard.css" rel="stylesheet" />
    <link href="../css/sidebar.css" rel="stylesheet" />
    <link href="../css/navbar.css" rel="stylesheet" />
    <script src="https://use.fontawesome.com/releases/v6.3.0/js/all.js" crossorigin="anonymous"></script>
    <style>
        .table { width: 100%; table-layout: auto; }
        .table th, .table td { text-align: center; vertical-align: middle; padding: 10px; }
        .table td button { margin: 0 5px; white-space: nowrap; }
        .table-responsive { overflow-x: auto; }
    </style>
</head>

<body class="sb-nav-fixed">
    <!-- Navbar Superior (fijo) -->
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
        <!-- Sidebar (Cargado con Fetch, como en tu plantilla) -->
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

                    <!-- Buscadores y botón agregar -->
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

                    <!-- Tabla de clientes -->
                    <div class="table-responsive my-4">
                        <table id="tblClientes" class="table table-bordered table-hover text-center">
                            <thead>
                                <tr class="table-dark align-middle">
                                    <th>Nombre</th>
                                    <th>DNI/RUC</th>
                                    <th>Teléfono</th>
                                    <th>Correo Electrónico</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody class="align-middle">
                                <!-- Contenido dinámico desde JS -->
                            </tbody>
                        </table>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <!-- Modal Agregar Cliente -->
    <div class="modal fade" id="modalAgregarCliente" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h3 class="modal-title">Registrar Nuevo Cliente</h3>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="formAgregarCliente" novalidate>
                        <div class="mb-3">
                            <label class="form-label fw-bold">Nombre:</label>
                            <input type="text" class="form-control" name="nombre" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold">DNI/RUC:</label>
                            <input type="text" class="form-control" name="dni_ruc" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold">Teléfono:</label>
                            <input type="tel" class="form-control" name="telefono">
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold">Correo Electrónico:</label>
                            <input type="email" class="form-control" name="email">
                        </div>
                        <div class="modal-footer d-flex justify-content-center">
                            <button type="submit" class="btn btn-primary">Agregar</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Editar Cliente -->
    <div class="modal fade" id="modalEditarCliente" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h3 class="modal-title">Actualizar Cliente</h3>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="formEditarCliente" novalidate>
                        <input type="hidden" name="id">
                        <div class="mb-3">
                            <label class="form-label fw-bold">Nombre:</label>
                            <input type="text" class="form-control" name="nombre" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold">DNI/RUC:</label>
                            <input type="text" class="form-control" name="dni_ruc" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold">Teléfono:</label>
                            <input type="tel" class="form-control" name="telefono">
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold">Correo Electrónico:</label>
                            <input type="email" class="form-control" name="email">
                        </div>
                        <div class="modal-footer d-flex justify-content-center">
                            <button type="submit" class="btn btn-success">Modificar</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Eliminar Cliente -->
    <div class="modal fade" id="modalEliminarCliente" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title">¿Confirmar Eliminación?</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>¿Estás seguro de que deseas eliminar este cliente?</p>
                    <p><strong>Esta acción es irreversible y eliminará el cliente permanentemente.</strong></p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">No</button>
                    <button type="button" class="btn btn-danger" id="btnConfirmarEliminar">Sí, eliminar</button>
                </div>
            </div>
        </div>
    </div>

    <script src="../js/bootstrap.bundle.min.js"></script>
    <script src="../js/functions/gestionClientes.js"></script>
</body>
</html>