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
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
    <meta name="description" content="Gestión de Recaudos Lubricentro La Cusqueña" />
    <meta name="author" content="La Cusqueña" />
    <title>Lubricentro Cusqueña - Gestión de Recaudos</title>
    <link href="../css/bootstrap.css" rel="stylesheet" />
    <script src="https://use.fontawesome.com/releases/v6.3.0/js/all.js" crossorigin="anonymous"></script>
    <style>
        /* Estilos generales de la página */
        .container-fluid {
            padding-top: 0rem;
        }
        
        /* Estilos de la tabla de listado */
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
        
        /* Estilos para achicar los formularios en modales (copiado del diseño de préstamos) */
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
                fetch('<?php echo $sidebar_path; ?>')
                    .then(response => response.text())
                    .then(html => document.getElementById('layoutSidenav_nav').innerHTML = html)
                    .catch(error => console.error('Error cargando sidebar:', error));
            </script>
            <?php endif; ?>
        </div>
        <div id="layoutSidenav_content">
            <!-- REMOVED: my-4 from this class to move the content up -->
            <main class="container-xl col-10 mx-auto">
                <div class="container-fluid px-4">
                    <!-- REMOVED: mt-2 from this class to move the title up -->
                    <h1 class="text-center mb-4">Gestión de Recaudos</h1>
                    
                    <!-- Contenedor principal de filtros y botón, alineado horizontalmente -->
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <!-- Filtros de búsqueda a la izquierda, en línea -->
                        <div class="d-flex flex-wrap align-items-center gap-3">
                            <div class="d-flex align-items-center gap-2">
                                <label for="tipoF" class="form-label mb-0">Tipo:</label>
                                <select id="tipoF" class="form-select form-control-sm">
                                    <option value="">Todos</option>
                                    <option value="puesto">Puesto</option>
                                    <option value="cancha">Cancha</option>
                                </select>
                            </div>
                            <div class="d-flex align-items-center gap-2">
                                <label for="from" class="form-label mb-0">Desde:</label>
                                <input id="from" type="date" class="form-control form-control-sm">
                            </div>
                            <div class="d-flex align-items-center gap-2">
                                <label for="to" class="form-label mb-0">Hasta:</label>
                                <input id="to" type="date" class="form-control form-control-sm">
                            </div>
                            <!-- Botón de listado sin funcionalidad JS -->
                            <button id="btnLoad" class="btn btn-secondary btn-sm">Listar</button>
                        </div>

                        <!-- Botón de registro a la derecha -->
                        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#recaudoModal">
                            <i class="fas fa-plus-circle me-2"></i>Registrar Nuevo Recaudo
                        </button>
                    </div>
                    
                    <!-- Tabla de Listado -->
                    <div class="table-responsive">
                        <table id="tbl" class="table table-striped table-bordered table-hover">
                            <thead class="table-dark">
                                <tr>
                                    <th>Fecha</th>
                                    <th>Tipo</th>
                                    <th>Cliente</th>
                                    <th>Monto</th>
                                    <th>Pagado</th>
                                    <th>Obs</th>
                                </tr>
                            </thead>
                            <tbody>
                                <!-- Los datos ya no se cargan con JS -->
                            </tbody>
                        </table>
                    </div>
                    <!-- Paginación sin funcionalidad JS -->
                    <nav aria-label="Page navigation example" class="d-flex justify-content-end">
                        <ul class="pagination">
                            <li class="page-item disabled">
                                <a class="page-link" href="#" aria-label="Previous">
                                    <span aria-hidden="true">«</span>
                                </a>
                            </li>
                            <li class="page-item active"><a class="page-link" href="#">1</a></li>
                            <li class="page-item"><a class="page-link" href="#">2</a></li>
                            <li class="page-item"><a class="page-link" href="#">3</a></li>
                            <li class="page-item">
                                <a class="page-link" href="#" aria-label="Next">
                                    <span aria-hidden="true">»</span>
                                </a>
                            </li>
                        </ul>
                    </nav>

                    <!-- Modal de Registro de Recaudo con el nuevo diseño -->
                    <div class="modal fade" id="recaudoModal" tabindex="-1" aria-labelledby="recaudoModalLabel" aria-hidden="true">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <div class="modal-header text-black">
                                    <h4 class="modal-title" id="recaudoModalLabel">Registrar Nuevo Recaudo</h4>
                                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                                </div>
                                <div class="modal-body">
                                    <form id="frmR" class="row g-3">
                                        <div class="col-12">
                                            <label for="tipo" class="form-label fw-bold">Tipo</label>
                                            <select name="tipo" id="tipo" class="form-select form-control-sm" required>
                                                <option value="puesto">Puesto</option>
                                                <option value="cancha">Cancha</option>
                                            </select>
                                        </div>
                                        <div class="col-12">
                                            <label for="cliente" class="form-label fw-bold">Cliente</label>
                                            <input name="cliente" id="cliente" class="form-control form-control-sm" placeholder="Cliente" required />
                                        </div>
                                        <div class="col-12">
                                            <label for="fecha" class="form-label fw-bold">Fecha</label>
                                            <input name="fecha" id="fecha" type="date" class="form-control form-control-sm" required />
                                        </div>
                                        <div class="col-12">
                                            <label for="monto" class="form-label fw-bold">Monto</label>
                                            <input name="monto" id="monto" type="number" step="0.01" class="form-control form-control-sm" placeholder="Monto" required />
                                        </div>
                                        <div class="col-12">
                                            <div class="form-check">
                                                <input type="checkbox" class="form-check-input" name="pagado" id="pagado">
                                                <label class="form-check-label" for="pagado">Pagado</label>
                                            </div>
                                        </div>
                                    <div class="col-12">
                                        <!-- Etiqueta del campo de texto -->
                                        <label for="observacion" class="form-label fw-bold">Observación</label>
                                        <!-- Campo de texto grande y rectangular. El atributo 'rows' controla la altura inicial. -->
                                        <textarea name="observacion" id="observacion" class="form-control form-control-sm" placeholder="Observación" rows="4"></textarea>
                                        </div>
                                        <div class="col-12 text-center mt-4">
                                            <button type="submit" class="btn btn-primary btn-sm">Registrar Recaudo</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>
    
    <!-- Contenedor de Toasts (Notificaciones) -->
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
    <script>
        // Función de utilidad para las llamadas a la API
        async function api(url, options) {
            try {
                const response = await fetch(url, options);
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                return await response.json();
            } catch (error) {
                console.error("Error en la llamada a la API:", error);
                showToast("Error en la llamada a la API", "error");
                return null;
            }
        }
        
        // Función para mostrar notificaciones tipo Toast
        function showToast(message, type) {
            const toastElement = document.getElementById(type === 'success' ? 'toastSuccess' : 'toastError');
            const toastBody = document.getElementById(type === 'success' ? 'toastSuccessBody' : 'toastErrorBody');
            toastBody.textContent = message;
            const toast = new bootstrap.Toast(toastElement);
            toast.show();
        }

        // Manejador del formulario de registro del modal
        document.getElementById('frmR').addEventListener('submit', async (e) => {
            e.preventDefault();
            const fd = new FormData(e.target);
            const data = Object.fromEntries(fd.entries());
            data.pagado = fd.get('pagado') ? 1 : 0;
            data.monto = parseFloat(data.monto);
            
            // Llama a la API para registrar el nuevo recaudo
            const res = await api('/api/recaudos', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(data)
            });
            
            // Si el registro fue exitoso (el backend devuelve un 'id'), cierra el modal y muestra un toast
            if (res && res.id) {
                const modal = bootstrap.Modal.getInstance(document.getElementById('recaudoModal'));
                if (modal) modal.hide();
                e.target.reset();
                showToast("Recaudo registrado correctamente.", "success");
            } else {
                showToast("No se pudo registrar el recaudo.", "error");
            }
        });
        
    </script>
</body>
</html>
