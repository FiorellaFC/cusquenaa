<?php
session_start(); 
require_once '../../backend/includes/auth.php';
verificarPermiso(['Administrador', 'Secretaria']);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Lubricentro Cusqueña</title>
    <link href="../css/bootstrap.css" rel="stylesheet" />
    <script src="https://use.fontawesome.com/releases/v6.3.0/js/all.js" crossorigin="anonymous"></script>
    <style>
        th, td {
            vertical-align: middle !important;
            text-align: center;
            word-break: break-word;
        }
        main.container-xl {
            width: 100%;
        }
        .table th {
            background-color: #343a40;
            color: white;
            font-weight: bold;
            border: 1px solid #000;
        }
        .table td {
            border: 1px solid #000;
        }
        .table-responsive {
            overflow-x: auto;
        }
        .invalid-field {
            border: 1px solid red;
        }
        .table td button {
            margin: 0 3px;
            white-space: nowrap;
        }
    </style>
</head>
<body class="sb-nav-fixed">
    <nav class="sb-topnav navbar navbar-expand navbar-dark bg-dark fixed-top">
        <?php if (isset($_SESSION['rol']) && $_SESSION['rol'] === 'Administrador'): ?>
        <a class="navbar-brand ps-3" href="base.php">La Cusqueña</a>
        <?php endif; ?>
        <?php if (isset($_SESSION['rol']) && $_SESSION['rol'] === 'Secretaria'): ?>
        <a class="navbar-brand ps-3" href="base2.php">La Cusqueña</a>
        <?php endif; ?>
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
            <?php if (isset($_SESSION['rol']) && $_SESSION['rol'] === 'Administrador'): ?>
            <script>
                fetch('sidebear_Admin.php') 
                    .then(r => r.text())
                    .then(html => document.getElementById('layoutSidenav_nav').innerHTML = html);
            </script>
            <?php endif; ?>
            <?php if (isset($_SESSION['rol']) && $_SESSION['rol'] === 'Secretaria'): ?>
            <script>
                fetch('sidebear_secre.php')
                    .then(r => r.text())
                    .then(html => document.getElementById('layoutSidenav_nav').innerHTML = html);
            </script>
            <?php endif; ?>
        </div>
        <div id="layoutSidenav_content">
            <main class="container-xl my-4">
                <div class="container-fluid px-4">
                    <h1 class="mb-4 text-center fw-bold">Gestión de Productos</h1>

                    <div class="row mb-3">
                        <div class="col-md-6 d-flex">
                            <input type="text" class="form-control me-2" id="buscarProducto" placeholder="Buscar producto por descripción o categoría">
                            <button class="btn btn-primary" id="btnBuscar">Buscar</button>
                        </div>
                        <div class="col-md-6 text-end">
                            <?php if (isset($_SESSION['rol']) && $_SESSION['rol'] === 'Administrador'): ?>
                            <a href="#" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalAgregarProducto">Agregar</a>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="modal fade" id="modalAgregarProducto" tabindex="-1" aria-labelledby="modalAgregarProductoLabel" aria-hidden="true">
                        <div class="modal-dialog modal-lg">
                            <form id="formAgregarProducto">
                                <div class="modal-content">
                                    <div class="modal-header bg-success text-white">
                                        <h5 class="modal-title" id="modalAgregarProductoLabel">Agregar Producto</h5>
                                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                                    </div>
                                    <div class="modal-body row g-3">
                                        <div class="col-md-6">
                                            <label class="form-label">Descripción</label>
                                            <input type="text" class="form-control" name="descripcion" required>
                                        </div>
                                        <div class="col-md-3">
                                            <label class="form-label">P. Compra</label>
                                            <input type="number" step="0.01" class="form-control" name="precio_compra" required>
                                        </div>
                                        <div class="col-md-3">
                                            <label class="form-label">P. Venta</label>
                                            <input type="number" step="0.01" class="form-control" name="precio_venta" required>
                                        </div>
                                        <div class="col-md-3">
                                            <label class="form-label">Inicial</label>
                                            <input type="number" class="form-control" name="inicial" required>
                                        </div>
                                        <div class="col-md-3">
                                            <label class="form-label">Ingreso</label>
                                            <input type="number" class="form-control" name="ingreso" required>
                                        </div>
                                        <div class="col-md-3">
                                            <label class="form-label">Queda</label>
                                            <input type="number" class="form-control" name="queda" readonly>
                                        </div>
                                        <div class="col-md-3">
                                            <label class="form-label">Venta</label>
                                            <input type="number" class="form-control" name="venta" required>
                                        </div>
                                        <div class="col-md-3">
                                            <label class="form-label">Monto</label>
                                            <input type="number" step="0.01" class="form-control" name="monto" required>
                                        </div>
                                        <div class="col-md-3">
                                        <label class="form-label">Categoría</label>
                                        <select class="form-select" name="categoria" id="agregar_categoria" required>
                                            <option value="">Seleccione</option>
                                            <option value="Aceite">Aceite</option>
                                            <option value="Aditivo">Aditivo</option>
                                            <option value="Bujía">Bujía</option>
                                            <option value="Filtro">Filtro</option>
                                            <option value="Foco">Foco</option>
                                            <option value="Grasa">Grasa</option>
                                            <option value="Limpieza">Limpieza</option>
                                            <option value="Refrigerante">Refrigerante</option>
                                            <option value="Repuesto">Repuesto</option>
                                            <option value="Otro">Otro</option>
                                        </select>
                                    </div>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="submit" class="btn btn-success">Guardar</button>
                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>

                    <div class="modal fade" id="modalEditarProducto" tabindex="-1" aria-labelledby="modalEditarProductoLabel" aria-hidden="true">
                        <div class="modal-dialog modal-lg">
                            <form id="formEditarProducto">
                                <div class="modal-content">
                                    <div class="modal-header bg-warning text-dark">
                                        <h5 class="modal-title" id="modalEditarProductoLabel">Editar Producto</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                    </div>
                                    <div class="modal-body row g-3">
                                        <input type="hidden" name="idProducto" id="editar_idProducto">
                                        <div class="col-md-6">
                                            <label class="form-label">Descripción</label>
                                            <input type="text" class="form-control" name="descripcion" id="editar_descripcion" required>
                                        </div>
                                        <div class="col-md-3">
                                            <label class="form-label">P. Compra</label>
                                            <input type="number" step="0.01" class="form-control" name="precio_compra" id="editar_precio_compra" required>
                                        </div>
                                        <div class="col-md-3">
                                            <label class="form-label">P. Venta</label>
                                            <input type="number" step="0.01" class="form-control" name="precio_venta" id="editar_precio_venta" required>
                                        </div>
                                        <div class="col-md-3">
                                            <label class="form-label">Inicial</label>
                                            <input type="number" class="form-control" name="inicial" id="editar_inicial" required>
                                        </div>
                                        <div class="col-md-3">
                                            <label class="form-label">Ingreso</label>
                                            <input type="number" class="form-control" name="ingreso" id="editar_ingreso" required>
                                        </div>
                                        <div class="col-md-3">
                                            <label class="form-label">Queda</label>
                                            <input type="number" class="form-control" name="queda" id="editar_queda" readonly>
                                        </div>
                                        <div class="col-md-3">
                                            <label class="form-label">Venta</label>
                                            <input type="number" class="form-control" name="venta" id="editar_venta" required>
                                        </div>
                                        <div class="col-md-3">
                                            <label class="form-label">Monto</label>
                                            <input type="number" step="0.01" class="form-control" name="monto" id="editar_monto" required>
                                        </div>
                                        <div class="col-md-3">
                                        <label class="form-label">Categoría</label>
                                        <select class="form-select" name="categoria" id="editar_categoria" required>
                                            <option value="">Seleccione</option>
                                            <option value="Aceite">Aceite</option>
                                            <option value="Aditivo">Aditivo</option>
                                            <option value="Bujía">Bujía</option>
                                            <option value="Filtro">Filtro</option>
                                            <option value="Foco">Foco</option>
                                            <option value="Grasa">Grasa</option>
                                            <option value="Limpieza">Limpieza</option>
                                            <option value="Refrigerante">Refrigerante</option>
                                            <option value="Repuesto">Repuesto</option>
                                            <option value="Otro">Otro</option>
                                        </select>
                                    </div>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="submit" class="btn btn-warning">Actualizar</button>
                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>

                    <div class="modal fade" id="modalVenderProducto" tabindex="-1" aria-labelledby="modalVenderProductoLabel" aria-hidden="true">
                        <div class="modal-dialog">
                            <form id="formVenderProducto">
                                <div class="modal-content">
                                    <div class="modal-header bg-primary text-white">
                                        <h5 class="modal-title" id="modalVenderProductoLabel">Vender Producto</h5>
                                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                                    </div>
                                    <div class="modal-body">
                                        <input type="hidden" name="idProducto" id="vender_idProducto">
                                        <div class="mb-3">
                                            <label class="form-label">Descripción</label>
                                            <input type="text" class="form-control" name="descripcion" id="vender_descripcion" readonly>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label">Queda Disponible</label>
                                            <input type="number" class="form-control" name="queda" id="vender_queda" readonly>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label">Cantidad a Vender</label>
                                            <input type="number" class="form-control" name="cantidad" id="vender_cantidad" required min="1">
                                        </div>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="submit" class="btn btn-primary">Vender</button>
                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>

   <div class="modal fade" id="modalVerVentas" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Historial de Ventas de: <span id="nombreProducto"></span></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">

                <!-- INICIO: Filtros del Historial -->
                <div class="row g-3 align-items-end mb-3 p-3 border rounded bg-light">
                    <div class="col-md-4">
                        <label for="filtroFechaDesde" class="form-label">Desde</label>
                        <input type="date" class="form-control form-control-sm" id="filtroFechaDesde">
                    </div>
                    <div class="col-md-4">
                        <label for="filtroFechaHasta" class="form-label">Hasta</label>
                        <input type="date" class="form-control form-control-sm" id="filtroFechaHasta">
                    </div>
                    <div class="col-md-4">
                        <label for="filtroCantidad" class="form-label">Cantidad (mín.)</label>
                        <input type="number" class="form-control form-control-sm" id="filtroCantidad" placeholder="Ej: 5">
                    </div>
                    <div class="col-md-4">
                        <label for="filtroMonto" class="form-label">Monto (mín.)</label>
                        <input type="number" class="form-control form-control-sm" id="filtroMonto" placeholder="Ej: 100">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label invisible">Limpiar</label>
                        <button class="btn btn-secondary btn-sm w-100" id="btnLimpiarFiltrosHistorial">Limpiar Filtros</button>
                    </div>
                </div>
                <!-- FIN: Filtros del Historial -->

                <h6>Historial</h6>
                <div class="table-responsive">
                    <table class="table table-sm table-bordered">
                        <thead class="table-light">
                            <tr>
                                <th>Fecha de Venta</th>
                                <th>Cantidad Vendida</th>
                                <th>Monto Venta</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody id="tablaVentasHistorial">
                            <!-- El historial se cargará aquí -->
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>



                            </div>
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover table-sm w-100 text-center align-middle" style="table-layout: auto;">
                            <thead>
                            <tr class="table-dark">
                                <th>ID</th>
                                <th>Descripción</th>
                                <th>P. Compra</th>
                                <th>P. Venta</th>
                                <th>Inicial</th>
                                <th>Ingreso</th>
                                <th>Queda</th>
                                <th>Venta</th>
                                <th>Monto</th>
                                <th>Categoría</th>
                                <th>Acciones</th>
                            </tr>
                            </thead>
                            <tbody id="tablaProductos">
                                <!-- Sample data for preview -->
                                <tr>
                                    <td>1</td>
                                    <td>Aceite 5W-30</td>
                                    <td>S/. 20.00</td>
                                    <td>S/. 30.00</td>
                                    <td>100</td>
                                    <td>50</td>
                                    <td>120</td>
                                    <td>30</td>
                                    <td>S/. 900.00</td>
                                    <td>Aceite</td>
                                    <td>
                                        <button class="btn btn-sm btn-info" data-bs-toggle="modal" data-bs-target="#modalVerVentas" title="Ver Ventas"><i class="fas fa-eye"></i></button>
                                        <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#modalVenderProducto" title="Vender"><i class="fas fa-cart-plus"></i></button>
                                        <?php if (isset($_SESSION['rol']) && $_SESSION['rol'] === 'Administrador'): ?>
                                       
                                        <button class="btn btn-sm btn-danger" title="Eliminar"><i class="fas fa-trash"></i></button>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                          </tbody>
                    </table>
               <div class="d-flex flex-column align-items-end mt-3">
                    <h4 class="fw-bold mb-1">Monto Total de Ventas: 
                        <span class="text-success" id="montoTotalGeneral">S/. 0.00</span>
                    </h4>
                    <h4 class="fw-bold mb-0">Monto Total Gastado: 
                        <span class="text-danger" id="montoTotalGastado">S/. 0.00</span>
                    </h4>
                </div>
                <div class="d-flex justify-content-end">
                    <ul class="pagination" id="pagination">
                         </ul>
                </div>
                    </div>

                    <div class="d-flex justify-content-end">
                        <ul class="pagination" id="pagination">
                            <li class="page-item"><a class="page-link" href="#">«</a></li>
                            <li class="page-item"><a class="page-link" href="#">1</a></li>
                            <li class="page-item"><a class="page-link" href="#">2</a></li>
                            <li class="page-item"><a class="page-link" href="#">»</a></li>
                        </ul>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <script src="../js/bootstrap.bundle.min.js"></script>
    <script src="../js/functions/gestionProducto.js"></script>
<script>
document.addEventListener('DOMContentLoaded', () => {
    // Modal Agregar
    const agregarInicial = document.querySelector('[name="inicial"]');
    const agregarIngreso = document.querySelector('[name="ingreso"]');
    const agregarVenta = document.querySelector('[name="venta"]');
    const agregarQueda = document.querySelector('[name="queda"]');
    const agregarPrecioVenta = document.querySelector('[name="precio_venta"]');
    const agregarMonto = document.querySelector('[name="monto"]');

    function actualizarQuedaAgregar() {
        const inicial = parseInt(agregarInicial.value) || 0;
        const ingreso = parseInt(agregarIngreso.value) || 0;
        const venta = parseInt(agregarVenta.value) || 0;
        const queda = inicial + ingreso - venta;
        agregarQueda.value = queda;
    }

    function actualizarMontoAgregar() {
        const venta = parseInt(agregarVenta.value) || 0;
        const precio = parseFloat(agregarPrecioVenta.value) || 0;
        agregarMonto.value = (venta * precio).toFixed(2);
    }

    [agregarInicial, agregarIngreso, agregarVenta].forEach(input =>
        input.addEventListener('input', actualizarQuedaAgregar)
    );
    [agregarVenta, agregarPrecioVenta].forEach(input =>
        input.addEventListener('input', actualizarMontoAgregar)
    );

    // Modal Editar
    const editarInicial = document.getElementById('editar_inicial');
    const editarIngreso = document.getElementById('editar_ingreso');
    const editarVenta = document.getElementById('editar_venta');
    const editarQueda = document.getElementById('editar_queda');
    const editarPrecioVenta = document.getElementById('editar_precio_venta');
    const editarMonto = document.getElementById('editar_monto');

    function actualizarQuedaEditar() {
        const inicial = parseInt(editarInicial.value) || 0;
        const ingreso = parseInt(editarIngreso.value) || 0;
        const venta = parseInt(editarVenta.value) || 0;
        const queda = inicial + ingreso - venta;
        editarQueda.value = queda;
    }

    function actualizarMontoEditar() {
        const venta = parseInt(editarVenta.value) || 0;
        const precio = parseFloat(editarPrecioVenta.value) || 0;
        editarMonto.value = (venta * precio).toFixed(2);
    }

    [editarInicial, editarIngreso, editarVenta].forEach(input =>
        input.addEventListener('input', actualizarQuedaEditar)
    );
    [editarVenta, editarPrecioVenta].forEach(input =>
        input.addEventListener('input', actualizarMontoEditar)
    );
});
</script>

</body>
</html>