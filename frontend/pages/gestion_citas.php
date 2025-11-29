<?php
//require_once '../../backend/includes/auth.php';
//verificarPermiso(['Administrador']);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Gestión de Citas</title>
    
    <link rel="stylesheet" href="../css/dashboard.css">
    <link rel="stylesheet" href="../css/sidebar.css">
    <link rel="stylesheet" href="../css/navbar.css">
    <link href="../css/bootstrap.css" rel="stylesheet" />
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://use.fontawesome.com/releases/v6.3.0/js/all.js" crossorigin="anonymous"></script>
    <style>
        .table th, .table td { text-align: center; vertical-align: middle; }
    </style>
</head>

<body class="sb-nav-fixed">
  <nav class="sb-topnav navbar navbar-expand navbar-dark bg-dark fixed-top">
    <a class="navbar-brand ps-3" href="base.php">La Cusqueña</a>
    <button class="btn btn-link btn-sm me-4" id="sidebarToggle"><i class="fas fa-bars"></i></button>
    <ul class="navbar-nav ms-auto me-3">
      <li class="nav-item dropdown">
        <a class="nav-link dropdown-toggle" id="navbarDropdown" href="#" role="button" data-bs-toggle="dropdown"><i class="fas fa-user fa-fw"></i></a>
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
                <h1 class="mb-4 text-center">Gestión de Citas</h1>

                <div class="card mb-4 shadow-sm">
                    <div class="card-body">
                        <div class="row g-3 align-items-end">
                            <div class="col-md-3">
                                <label class="form-label fw-bold">Fecha:</label>
                                <input type="date" class="form-control" id="buscarFecha">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label fw-bold">Estado:</label>
                                <select id="buscarEstado" class="form-select">
                                    <option value="todas" selected>Todas</option>
                                    <option value="pendiente">Pendiente</option>
                                    <option value="confirmada">Confirmada</option>
                                    <option value="completada">Completada</option>
                                    <option value="cancelada">Cancelada</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label fw-bold">DNI / RUC:</label>
                                <input type="text" class="form-control" id="buscarDNI" placeholder="Buscar...">
                            </div>
                            <div class="col-md-3 d-flex gap-2">
                                <button class="btn btn-primary w-100" id="btnBuscar"><i class="fas fa-search"></i> Buscar</button>
                                <button class="btn btn-secondary w-100" id="btnLimpiar"><i class="fas fa-sync"></i></button>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="table-responsive">
                    <table id="tblCitas" class="table table-bordered table-hover text-center align-middle shadow-sm">
                        <thead class="table-dark">
                        <tr>
                    <th>Cliente</th>
                    <th>Apellido</th>
                    <th>DNI/RUC</th>
                    <th>Teléfono</th>
                    <th>Fecha</th>
                    <th>Hora</th>
                    <th>Servicio</th>
                    <th>Precio</th> <th>Estado</th>
                    <th>Acciones</th>
                </tr>
                        </thead>
                        <tbody>
                            </tbody>
                    </table>
                </div>

                <nav aria-label="Paginación de citas" class="mt-4">
                    <ul class="pagination justify-content-center" id="paginationControls"></ul>
                </nav>
            </div>
        </main>
    </div>
  </div>

<div class="modal fade" id="modalEditarCita" tabindex="-1" aria-hidden="true">
      <div class="modal-dialog modal-dialog-centered">
          <div class="modal-content">
              <div class="modal-header bg-warning text-dark">
                  <h5 class="modal-title fw-bold">Editar Cita</h5>
                  <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
              </div>
              <div class="modal-body">
                  <form id="formEditarCita">
                      <input type="hidden" name="id">
                      
                      <div class="row mb-2">
                          <div class="col-6">
                              <label class="form-label fw-bold small">Nombre</label>
                              <input type="text" class="form-control form-control-sm" name="nombre_cliente" required>
                          </div>
                          <div class="col-6">
                              <label class="form-label fw-bold small">Apellido</label>
                              <input type="text" class="form-control form-control-sm" name="apellido_cliente" required>
                          </div>
                      </div>
                      <div class="row mb-3">
                          <div class="col-6">
                              <label class="form-label fw-bold small">DNI</label>
                              <input type="text" class="form-control form-control-sm bg-light" name="dni_ruc" readonly>
                          </div>
                          <div class="col-6">
                              <label class="form-label fw-bold small">Teléfono</label>
                              <input type="text" class="form-control form-control-sm" name="telefono_cliente">
                          </div>
                      </div>

                      <div class="row mb-3">
                          <div class="col-6">
                              <label class="form-label fw-bold small">Fecha</label>
                              <input type="date" class="form-control form-control-sm" name="fecha" required>
                          </div>
                          <div class="col-6">
                              <label class="form-label fw-bold small">Hora</label>
                              <input type="time" class="form-control form-control-sm" name="hora" required>
                          </div>
                      </div>

                      <div class="mb-3">
                          <label class="form-label fw-bold small">Servicios:</label>
                          <div id="containerServiciosEditar" class="border rounded p-2 bg-light" style="max-height: 150px; overflow-y: auto;">
                              <div class="text-center small text-muted">Cargando...</div>
                          </div>
                      </div>

                      <div class="row align-items-end">
                          <div class="col-6 mb-3">
                              <label class="form-label fw-bold small">Estado</label>
                              <select name="estado" class="form-select form-select-sm fw-bold">
                                  <option value="pendiente" class="text-warning">Pendiente</option>
                                  <option value="confirmada" class="text-success">Confirmada</option>
                                  <option value="completada" class="text-primary">Completada</option>
                                  <option value="cancelada" class="text-danger">Cancelada</option>
                              </select>
                          </div>
                          <div class="col-6 mb-3">
                              <label class="form-label fw-bold small text-success">Precio Total (S/.)</label>
                              <input type="number" step="0.01" class="form-control form-control-sm fw-bold text-end border-success" name="precio_final" id="inputPrecioFinal">
                          </div>
                      </div>

                      <div class="modal-footer justify-content-center pb-0">
                          <button type="submit" class="btn btn-warning w-75 fw-bold">Guardar Cambios</button>
                      </div>
                  </form>
              </div>
          </div>
      </div>
  </div>

  <div class="modal fade" id="modalCancelar" tabindex="-1">
      <div class="modal-dialog modal-dialog-centered">
          <div class="modal-content">
              <div class="modal-header bg-danger text-white">
                  <h5 class="modal-title">Cancelar Cita</h5>
                  <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
              </div>
              <div class="modal-body text-center">
                  <p class="mb-0">¿Seguro que deseas cambiar el estado a <strong>Cancelada</strong>?</p>
              </div>
              <div class="modal-footer justify-content-center">
                  <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Volver</button>
                  <button type="button" class="btn btn-danger" id="btnConfirmarCancelar">Sí, Cancelar</button>
              </div>
          </div>
      </div>
  </div>
<div class="modal fade" id="modalDetalleServicios" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-sm modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg overflow-hidden">
                
                <div class="modal-header bg-dark text-white py-2 border-bottom border-secondary">
                    <h6 class="modal-title fw-bold small text-uppercase" style="letter-spacing: 1px;">
                        <i class="fas fa-clipboard-list me-2 text-warning"></i> Servicios
                    </h6>
                    <button type="button" class="btn-close btn-close-white small" data-bs-dismiss="modal"></button>
                </div>
                
                <div class="modal-body p-0 bg-white">
                    <div id="listaServiciosDetalle">
                        </div>
                </div>
                
                <div class="modal-footer py-1 justify-content-center bg-light border-top">
                    <button type="button" class="btn btn-sm btn-secondary w-50 fw-bold" data-bs-dismiss="modal">Cerrar</button>
                </div>

            </div>
        </div>
    </div>
  <script src="../js/bootstrap.bundle.min.js"></script>
  <script src="../js/functions/gestionCitasAdmin.js"></script> </body>
</html>