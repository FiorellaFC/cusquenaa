<?php
// require_once 'ruta/a/tu/auth.php'; // Asegúrate de que esta ruta sea correcta
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Gestión de Citas</title>
    
    <!-- TUS ESTILOS PERSONALIZADOS Y LIBRERÍAS -->
    <link rel="stylesheet" href="../css/dashboard.css">
    <link rel="stylesheet" href="../css/sidebar.css">
    <link rel="stylesheet" href="../css/navbar.css">
    <link href="../css/bootstrap.css" rel="stylesheet" />
    <script src="https://use.fontawesome.com/releases/v6.3.0/js/all.js" crossorigin="anonymous"></script>
    <style>
        .table { width: 100%; table-layout: auto; }
        .table th, .table td { text-align: center; vertical-align: middle; padding: 10px; }
        .table td button { margin: 0 5px; white-space: nowrap; }
        .table-responsive { overflow-x: auto; }
        .badge-confirmada { background-color: #198754; color: white; }
        .badge-cancelada { background-color: #dc3545; color: white; }
        .badge-completada { background-color: #0d6efd; color: white; }
    </style>
</head>

<body class="sb-nav-fixed">
  <!-- Navbar Superior (fijo) -->
  <nav class="sb-topnav navbar navbar-expand navbar-dark bg-dark fixed-top">
    <a class="navbar-brand ps-3" href="base.php">La Cusqueña</a>
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
      <script>
        fetch('sidebear_Admin.php')
          .then(r => r.text())
          .then(html => document.getElementById('layoutSidenav_nav').innerHTML = html)
          .catch(e => console.error('Error cargando sidebar:', e));
      </script>
    </div>

        <!-- Contenido principal -->
       <div id="layoutSidenav_content">
            <main class="container-xl my-4">
                <div class="container-fluid px-4">
                    <h1 class="mb-4 text-center">Gestión de Citas</h1>

                    <!-- Filtros de Búsqueda -->
                    <div class="row g-3 mb-4 align-items-end">
                        <div class="col-md-3">
                            <label for="buscarFecha" class="form-label">Buscar por Fecha:</label>
                            <input type="date" class="form-control" id="buscarFecha">
                        </div>
                        <div class="col-md-2">
                            <label for="buscarTelefono" class="form-label">Por Teléfono:</label>
                            <input type="text" class="form-control" id="buscarTelefono" placeholder="Teléfono...">
                        </div>
                        <!-- CAMPO DNI AÑADIDO -->
                        <div class="col-md-2">
                            <label for="buscarDNI" class="form-label">Por DNI/RUC:</label>
                            <input type="text" class="form-control" id="buscarDNI" placeholder="DNI o RUC...">
                        </div>
                        <div class="col-md-2">
                            <label for="buscarNombre" class="form-label">Por Nombre:</label>
                            <input type="text" class="form-control" id="buscarNombre" placeholder="Nombre cliente...">
                        </div>
                        <div class="col-md-3">
                            <label for="buscarEstado" class="form-label">Por Estado:</label>
                            <select id="buscarEstado" class="form-select">
                                <option value="todas" selected>Todas</option>
                                <option value="confirmada">Confirmada</option>
                                <option value="completada">Completada</option>
                                <option value="cancelada">Cancelada</option>
                            </select>
                        </div>
                        <div class="col-12 d-flex justify-content-end">
                            <button class="btn btn-primary me-2" id="btnBuscar"><i class="fas fa-search"></i> Buscar</button>
                            <button class="btn btn-secondary" id="btnLimpiar"><i class="fas fa-times"></i> Limpiar</button>
                        </div>
                    </div>
                    
                    <div class="table-responsive">
                        <table id="tblCitas" class="table table-bordered table-hover text-center">
                            <thead>
                                <tr class="table-dark align-middle">
                                    <th>Cliente</th>
                                    <th>DNI/RUC</th> <!-- COLUMNA AÑADIDA -->
                                    <th>Teléfono</th>
                                    <th>Fecha</th>
                                    <th>Hora</th>
                                    <th>Servicio Solicitado</th>
                                    <th>Estado</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <!-- Las citas se cargarán aquí dinámicamente -->
                            </tbody>
                        </table>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <!-- ========= MODALES ========= -->

    <div class="modal fade" id="modalEditarCita" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Editar Cita</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="formEditarCita" novalidate>
                        <input type="hidden" name="id">
                        
                        <!-- CAMPO DNI AÑADIDO (deshabilitado) -->
                        <div class="mb-3">
                            <label class="form-label fw-bold">DNI/RUC (Cliente):</label>
                            <input type="text" class="form-control" name="dni_ruc" disabled>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold">Nombre Cliente:</label>
                            <input type="text" class="form-control" name="nombre_cliente" required>
                        </div>
                         <div class="mb-3">
                            <label class="form-label fw-bold">Teléfono:</label>
                            <input type="tel" class="form-control" name="telefono_cliente">
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">Fecha:</label>
                                <input type="date" class="form-control" name="fecha" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">Hora:</label>
                                <input type="time" class="form-control" name="hora" required>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold">Servicio:</label>
                            <textarea class="form-control" name="servicio_solicitado" rows="2"></textarea>
                        </div>
                         <div class="mb-3">
                            <label class="form-label fw-bold">Estado:</label>
                            <select name="estado" class="form-select" required>
                                <option value="confirmada">Confirmada</option>
                                <option value="completada">Completada</option>
                                <option value="cancelada">Cancelada</option>
                            </select>
                        </div>
                        <div class="modal-footer d-flex justify-content-center">
                            <button type="submit" class="btn btn-primary">Actualizar Cita</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="modalCancelar" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title">Confirmar Cancelación</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>¿Estás seguro de que deseas cancelar esta cita?</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">No</button>
                    <button type="button" class="btn btn-danger" id="btnConfirmarCancelar">Sí, Cancelar</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Scripts de Bootstrap y el tuyo -->
    <script src="../js/bootstrap.bundle.min.js"></script> <!-- Ajusta esta ruta -->
    <script src="../js/functions/gestionCitasAdmin.js"></script> <!-- Ruta que me diste -->
</body>
</html>