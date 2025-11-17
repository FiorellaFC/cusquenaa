<?php
require_once '../../backend/includes/auth.php';
verificarPermiso(['Administrador']);

// Conexión a la base de datos
$host = 'localhost';
$user = 'root';
$password = ''; // tu contraseña
$database = 'la_cusquena';

$conexion = new mysqli($host, $user, $password, $database);
if ($conexion->connect_error) {
    die("Error de conexión: " . $conexion->connect_error);
}

// Consulta para obtener todos los usuarios
$sql = "SELECT * FROM usuarios ORDER BY id ASC";
$result = $conexion->query($sql);
?>

<!DOCTYPE html>
<html lang="es">

<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Gestión de Usuarios - Lubricentro Cusqueña</title>
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
    <!-- Sidebar -->
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
        <div class="container-fluid px-4">
          <h1 class="mb-4 text-center">Gestión de Usuarios</h1>

          <!-- Buscador y botón agregar -->
          <div class="row mb-3">
            <div class="col-12 d-flex justify-content-between align-items-center">
              <div class="d-flex">
                <input type="text" class="form-control me-2" placeholder="Buscar usuario" id="buscarUsuario">
                <a href="#" class="btn btn-primary" id="btnBuscar">Buscar</a>
              </div>
              <a href="#" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalAgregar">Agregar</a>
            </div>
          </div>

          <!-- Tabla de usuarios -->
          <div class="table-responsive my-4">
            <table class="table table-bordered table-hover text-center">
              <thead>
                <tr class="table-dark align-middle">
                  <th>ID</th>
                  <th>Usuario</th>
                  <th>Contraseña</th>
                  <th>Correo Electrónico</th>
                  <th>Rol</th>
                  <th>Estado</th>
                  <th>Acciones</th>
                </tr>
              </thead>
              <tbody class="align-middle">
                <?php if ($result->num_rows > 0): ?>
                  <?php while($row = $result->fetch_assoc()): ?>
                    <tr>
                      <td><?php echo $row['id']; ?></td>
                      <td><?php echo htmlspecialchars($row['usuario']); ?></td>
                      <td>********</td>
                      <td><?php echo htmlspecialchars($row['correo']); ?></td>
                      <td><?php echo $row['rol']; ?></td>
                      <td><?php echo $row['estado']; ?></td>
                      <td>
                        <button class="btn btn-success btn-sm btnEditar" 
                                data-id="<?php echo $row['id']; ?>" 
                                data-usuario="<?php echo htmlspecialchars($row['usuario']); ?>"
                                data-correo="<?php echo htmlspecialchars($row['correo']); ?>"
                                data-rol="<?php echo $row['rol']; ?>"
                                data-estado="<?php echo $row['estado']; ?>"
                                data-bs-toggle="modal" data-bs-target="#modalEditar">
                          <i class="fas fa-edit"></i>
                        </button>
                        <button class="btn btn-danger btn-sm btnEliminar" 
                                data-id="<?php echo $row['id']; ?>" 
                                data-bs-toggle="modal" data-bs-target="#modalEliminarConfirmacion">
                          <i class="fas fa-trash"></i>
                        </button>
                      </td>
                    </tr>
                  <?php endwhile; ?>
                <?php else: ?>
                  <tr>
                    <td colspan="7">No hay usuarios registrados</td>
                  </tr>
                <?php endif; ?>
              </tbody>
            </table>
          </div>

          <!-- Modal Agregar -->
          <div class="modal fade" id="modalAgregar">
            <div class="modal-dialog modal-dialog-centered">
              <div class="modal-content">
                <div class="modal-header">
                  <h3 class="modal-title">Registro de Usuario</h3>
                  <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                  <form id="formAgregarUsuario" method="post">
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
                        <input type="radio" class="form-check-input" name="estado" value="activo" id="activo" checked>
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

          <!-- Modal Editar -->
          <div class="modal fade" id="modalEditar">
            <div class="modal-dialog modal-dialog-centered">
              <div class="modal-content">
                <div class="modal-header">
                  <h3 class="modal-title">Actualización de Usuario</h3>
                  <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                  <form id="formEditarUsuario" method="post">
                    <input type="hidden" id="editarId" name="id">
                    <div class="mb-3">
                      <label for="editarUsuario" class="form-label fw-bold">Usuario:</label>
                      <input type="text" class="form-control" id="editarUsuario" name="usuario" required>
                    </div>
                    <div class="mb-3">
                      <label for="editarContrasena" class="form-label fw-bold">Contraseña:</label>
                      <input type="password" class="form-control" id="editarContrasena" name="contrasena" disabled>
                    </div>
                    <div class="mb-3">
                      <label for="editarCorreo" class="form-label fw-bold">Correo Electrónico:</label>
                      <input type="email" class="form-control" id="editarCorreo" name="correo" required>
                    </div>
                    <div class="mb-3">
                      <label for="editarRol" class="form-label fw-bold">Rol:</label>
                      <select class="form-select" id="editarRol" name="rol" required>
                        <option value="">--SELECCIONAR--</option>
                        <option value="Administrador">Administrador</option>
                        <option value="Secretaria">Secretaria</option>
                      </select>
                    </div>
                    <div class="mb-3 d-flex align-items-center">
                      <label class="form-label me-3 fw-bold">Estado:</label>
                      <div class="form-check form-check-inline">
                        <input type="radio" class="form-check-input" name="editarEstado" value="activo" id="editarEstadoActivo">
                        <label class="form-check-label" for="editarEstadoActivo">Activo</label>
                      </div>
                      <div class="form-check form-check-inline">
                        <input type="radio" class="form-check-input" name="editarEstado" value="inactivo" id="editarEstadoInactivo">
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

          <!-- Modal Eliminar -->
          <div class="modal fade" id="modalEliminarConfirmacion" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
              <div class="modal-content">
                <div class="modal-header bg-danger text-white">
                  <h5 class="modal-title">¿Confirmar Eliminación?</h5>
                  <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
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

        </div>
      </main>
    </div>
  </div>

  <script src="../js/bootstrap.bundle.min.js"></script>
  <script src="../js/functions/gestionUsuario.js"></script>
</body>
</html>

<?php
$conexion->close();
?>
