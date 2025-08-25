<?php
require_once '../../backend/includes/auth.php';
verificarPermiso(['Administrador']);
?>

<!DOCTYPE html>
<html lang="es">

<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Lubricentro Cusqueña</title>
  <link href="../css/bootstrap.css" rel="stylesheet" />
  <script src="https://use.fontawesome.com/releases/v6.3.0/js/all.js" crossorigin="anonymous"></script>

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


    <div id="layoutSidenav_content">
      <main class="container-xl my-2 col-12 mx-auto">
        <div class="container-fluid px-4 ">
          <h1 class="mb-4 text-center">Gestión de Usuarios</h1>

          <div class="row">
            <div class="col-12 d-flex justify-content-between align-items-center">
              <div class="d-flex">
                <input type="text" class="form-control me-2" placeholder="Buscar usuario">
                <a href="#" class="btn btn-primary">Buscar</a>
              </div>
              <a href="#" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalAgregar">Agregar</a>
            </div>
          </div>
          <!-- Modal Agregar -->
          <div class="modal fade " id="modalAgregar">
            <div class="modal-dialog modal-dialog-centered">
              <div class="modal-content">
                <div class="modal-header ">
                  <h3 class="modal-title" id="miModalLabel">Registro de Usuario</h3>
                  <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>
                <div class="modal-body">
                  <form action="" method="" novalidate>
                    <div class="mb-3">
                      <label for="usuario" class="form-label fw-bold">Usuario:</label>
                      <input type="text" class="form-control" id="usuario" name="usuario" required>
                    </div>
                    <div class="mb-3">
                      <label for="contrasena" class="form-label fw-bold">Contraseña:</label>
                      <input type="password" class="form-control" id="contrasena" name="contrasena" required>
                    </div>
                    <div class="mb-3">
                      <label for="correo" class="form-label fw-bold">Correo Electrónico:</label>
                      <input type="email" class="form-control" id="correo" name="correo" required>
                    </div>
                    <div class="mb-3">
                      <label for="rol" class="form-label fw-bold">Rol:</label>
                      <select class="form-select" id="rol" name="rol" required>
                        <option value="">--SELECCIONAR--</option>
                        <option value="Administrador">Administrador</option>
                        <option value="Secretaria">Secretaria</option>
                      </select>
                    </div>
                    <div class="mb-3 d-flex align-items-center">
                      <label class="form-label me-3 fw-bold">Estado:</label>
                      <div class="form-check form-check-inline">
                        <input type="radio" class="form-check-input" name="estado" value="activo" id="activo" required
                          checked>
                        <label class="form-check-label" for="activo">Activo</label>
                      </div>
                      <div class="form-check form-check-inline">
                        <input type="radio" class="form-check-input" name="estado" value="inactivo" id="inactivo">
                        <label class="form-check-label" for="inactivo">Inactivo</label>
                      </div>
                    </div>
                    <div class="modal-footer d-flex justify-content-center">
                      <button type="submit" class="btn btn-primary">Agregar</button>
                    </div>
                  </form>
                </div>
              </div>
            </div>
          </div>
          <!-- Fin Modal Agregar -->

          <!-- Modal Editar -->
          <div class="modal fade " id="modalEditar">
            <div class="modal-dialog modal-dialog-centered">
              <div class="modal-content">
                <div class="modal-header ">
                  <h3 class="modal-title" id="miModalLabel">Actualización de Usuario</h3>
                  <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>
                <div class="modal-body">
                  <form action="" method="" novalidate>
                    <input type="hidden" id="editarId">
                    <div class="mb-3">
                      <label for="usuario" class="form-label fw-bold">Usuario:</label>
                      <input type="text" class="form-control" id="editarUsuario" name="usuario" required>
                    </div>
                    <div class="mb-3">
                      <label for="contrasena" class="form-label fw-bold">Contraseña:</label>
                      <input type="password" class="form-control disabled" id="editarContrasena" name="contrasena"
                        required disabled>
                    </div>
                    <div class="mb-3">
                      <label for="correo" class="form-label fw-bold">Correo Electrónico:</label>
                      <input type="email" class="form-control" id="editarCorreo" name="correo" required>
                    </div>
                    <div class="mb-3">
                      <label for="rol" class="form-label fw-bold">Rol:</label>
                      <select class="form-select" id="editarRol" name="rol" required>
                        <option value="">--SELECCIONAR--</option>
                        <option value="Administrador">Administrador</option>
                        <option value="Secretaria">Secretaria</option>
                      </select>
                    </div>
                    <div class="mb-3 d-flex align-items-center">
                      <label class="form-label me-3 fw-bold">Estado:</label>
                      <div class="form-check form-check-inline">
                        <input type="radio" class="form-check-input" name="editarEstado" value="activo"
                          id="editarEstadoActivo" required>
                        <label class="form-check-label" for="editarEstadoActivo">Activo</label>
                      </div>
                      <div class="form-check form-check-inline">
                        <input type="radio" class="form-check-input" name="editarEstado" value="inactivo"
                          id="editarEstadoInactivo">
                        <label class="form-check-label" for="editarEstadoInactivo">Inactivo</label>
                      </div>
                    </div>
                    <div class="modal-footer d-flex justify-content-center">
                      <button type="submit" class="btn btn-success">Modificar</button>
                    </div>
                  </form>
                </div>
              </div>
            </div>
          </div>
          <!-- Fin Modal Editar -->


          <!-- Tabla -->
          <div class="table-responsive my-4">
            <table class="table  table-bordered table-hover text-center">
              <thead>
                <tr class="table-dark align-middle">
                  <th>ID</th>
                  <th>Usuario</th>
                  <th>Contraseña</th>
                  <th>Correo Electronico</th>
                  <th>Rol</th>
                  <th>Estado</th>
                  <th>Acciones</th>
                </tr>
              </thead>
              <tbody class="align-middle">
                <!-- insertar datos de manera dinamica -->
              </tbody>
            </table>
          </div>
          <!-- Fin Tabla -->

          <!-- Paginación -->
          <nav aria-label="Page navigation example" class="d-flex justify-content-end">
            <ul class="pagination" id="pagination">
              <li class="page-item">
                <a class="page-link" href="#" aria-label="Previous" id="prev-page">
                  <span aria-hidden="true">&laquo;</span>
                </a>
              </li>
              <!-- Los números de página se generarán dinámicamente -->
              <li class="page-item">
                <a class="page-link" href="#" aria-label="Next" id="next-page">
                  <span aria-hidden="true">&raquo;</span>
                </a>
              </li>
            </ul>
          </nav>
          <!-- Fin Paginación -->
        </div>
      </main>

    </div>
  </div>
  <!-- Toast Bootstrap Personalizado -->
  <div id="toastAgregar" class="toast align-items-center border-0 position-fixed bottom-0 end-0 mb-3 me-3 z-3"
    role="alert" aria-live="assertive" aria-atomic="true" style="min-width: 250px;">
    <div id="toastHeaderAgregar" class="toast-header bg-success text-white d-flex justify-content-between w-100">
      <strong id="toastTitleAgregar" class="me-auto"></strong>
      <button type="button" class="btn-close btn-close-white" data-bs-dismiss="toast" aria-label="Close"></button>
    </div>
    <div class="toast-body bg-white text-dark" id="toastMessageAgregar"></div>
  </div>

  <div id="toastEditar" class="toast align-items-center border-0 position-fixed bottom-0 end-0 mb-3 me-3 z-3"
    role="alert" aria-live="assertive" aria-atomic="true" style="min-width: 250px; ">
    <div id="toastHeaderEditar" class="toast-header bg-success text-white">
      <strong id="toastTitleEditar" class="me-auto"></strong>
      <button type="button" class="btn-close btn-close-white" data-bs-dismiss="toast" aria-label="Close"></button>
    </div>
    <div class="toast-body bg-white text-dark" id="toastMessageEditar"></div>
  </div>

  <div id="toastEliminar" class="toast align-items-center border-0 position-fixed bottom-0 end-0 mb-3 me-3 z-3"
    role="alert" aria-live="assertive" aria-atomic="true" style="min-width: 250px;">
    <div class="toast-header bg-danger text-white">
      <strong class="me-auto">Eliminación Exitosa</strong>
      <button type="button" class="btn-close btn-close-white" data-bs-dismiss="toast" aria-label="Close"></button>
    </div>
    <div class="toast-body bg-white text-dark" id="toastMessageEliminar">

    </div>
  </div>


  <div class="modal fade" id="modalEliminarConfirmacion" tabindex="-1" aria-labelledby="modalEliminarLabel"
    aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content">
        <div class="modal-header bg-danger text-white">
          <h5 class="modal-title" id="modalEliminarLabel">¿Confirmar Eliminación?</h5>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
        </div>
        <div class="modal-body">
          ¿Estás seguro de que deseas eliminar este usuario?
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">No</button>
          <button type="button" class="btn btn-danger" id="btnConfirmarEliminar">Sí, eliminar</button>
        </div>
      </div>
    </div>
  </div>


  <script src="../js/bootstrap.bundle.min.js"></script>
  <script src="../js/functions/gestionUsuario.js"></script>
</body>

</html>