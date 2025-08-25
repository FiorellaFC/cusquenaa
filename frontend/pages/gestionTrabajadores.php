<?php
// Asegura que solo los usuarios autorizados puedan ver esta página.
require_once '../../backend/includes/auth.php';
verificarPermiso(['Administrador']);
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Trabajadores</title>
    <!-- Enlaces a los archivos CSS de Bootstrap y Font Awesome -->
    <link href="../css/bootstrap.css" rel="stylesheet" />
    <script src="https://use.fontawesome.com/releases/v6.3.0/js/all.js" crossorigin="anonymous"></script>
    <style>
        /* Estilo para que la tabla no se salga del contenedor en pantallas pequeñas */
        .table-responsive {
            overflow-x: auto;
        }
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
        <!-- Sidebar -->
        <div id="layoutSidenav_nav">
            <script>
                // Carga el contenido del sidebar desde un archivo externo
                fetch('sidebear_Admin.php')
                    .then(r => r.text())
                    .then(html => document.getElementById('layoutSidenav_nav').innerHTML = html)
                    .catch(e => console.error('Error cargando sidebar:', e));
            </script>
        </div>

        <!-- Contenido principal -->
        <div id="layoutSidenav_content">
            <main class="container-xl my-2 col-12 mx-auto">
                <div class="container-fluid px-4">
                    <h1 class="mb-4 text-center">Gestión de Trabajadores y Cotizaciones</h1>

                    <!-- Contenedor para el buscador y el botón de agregar trabajador -->
                    <div class="d-flex justify-content-between flex-wrap align-items-center gap-3 mb-4">
                        <!-- Buscador de trabajador alineado a la izquierda -->
                        <div class="d-flex align-items-center gap-2">
                            <input type="text" class="form-control form-control-sm w-auto" id="search-input" placeholder="Buscar trabajador">
                            <button class="btn btn-info text-white btn-sm" id="search-btn">Buscar</button>
                        </div>
                        
                        <!-- Botón para agregar trabajador alineado a la derecha -->
                        <a href="#" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#modalAgregarTrabajador">
                            <i class="fas fa-plus-circle me-2"></i> Agregar Trabajador
                        </a>
                    </div>
                    
                    <!-- Contenedor para la fecha de cotizaciones diarias -->
                    <div class="cotizaciones-container my-4 p-3 border rounded">
                        <h2 class="text-start">Cotizaciones Diarias</h2>
                        <div class="d-flex align-items-center gap-2">
                            <label for="fecha" class="form-label mb-0">Fecha:</label>
                            <!-- El campo de fecha ahora está vacío por defecto -->
                            <input type="date" id="fecha" class="form-control form-control-sm w-auto">
                        </div>
                    </div>

                    <!-- Modal para el registro de un nuevo trabajador -->
                    <div class="modal fade" id="modalAgregarTrabajador" tabindex="-1" aria-labelledby="modalAgregarTrabajadorLabel" aria-hidden="true">
                        <div class="modal-dialog modal-dialog-centered">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h3 class="modal-title" id="modalAgregarTrabajadorLabel">Registro de Trabajador</h3>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                                </div>
                                <div class="modal-body">
                                    <!-- Formulario para agregar un trabajador -->
                                    <form id="frmT" novalidate>
                                        <div class="mb-3">
                                            <label for="nombre" class="form-label fw-bold">Nombre:</label>
                                            <input type="text" class="form-control" id="nombre" name="nombre" placeholder="Nombre" required />
                                        </div>
                                        <div class="mb-3">
                                            <label for="cargo" class="form-label fw-bold">Cargo:</label>
                                            <input type="text" class="form-control" id="cargo" name="cargo" placeholder="Cargo" />
                                        </div>
                                        <div class="mb-3">
                                            <label for="turno" class="form-label fw-bold">Turno:</label>
                                            <select class="form-select" id="turno" name="turno">
                                                <option value="mañana">Mañana</option>
                                                <option value="tarde">Tarde</option>
                                                <option value="noche">Noche</option>
                                                <option value="ninguno">Sin turno</option>
                                            </select>
                                        </div>
                                        <div class="modal-footer d-flex justify-content-center">
                                            <button type="submit" class="btn btn-primary">Crear</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Tabla para mostrar las cotizaciones -->
                    <div class="table-responsive my-4">
                        <table id="tbl" class="table table-bordered table-hover text-center">
                            <thead>
                                <tr class="table-dark align-middle">
                                    <th>Trabajador</th>
                                    <th>Turno</th>
                                    <th>Pagó</th>
                                    <th>Monto</th>
                                    <th>Acción</th>
                                </tr>
                            </thead>
                            <tbody>
                                <!-- Los datos se insertarán dinámicamente con JavaScript -->
                            </tbody>
                        </table>
                    </div>

                    <!-- Paginación (funcionalidad no implementada en este código, solo diseño) -->
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
                </div>
            </main>
        </div>
    </div>

    <!-- Script de Bootstrap -->
    <script src="../js/bootstrap.bundle.min.js"></script>
    <script>
        // Funcionalidad para el formulario y la tabla
        // URL base para las llamadas a la API
        const BASE_API_URL = '/api';

        // Función para manejar las llamadas a la API
        async function api(path, options = {}) {
            try {
                const response = await fetch(BASE_API_URL + path, options);
                if (!response.ok) {
                    throw new Error(`Error en la API: ${response.statusText}`);
                }
                // Si la respuesta es 204 No Content, no devuelve JSON
                if (response.status === 204) {
                    return null;
                }
                return await response.json();
            } catch (error) {
                console.error("Error en la llamada a la API:", error);
                // Reemplazado alert() con una función de modal de mensaje
                // Por ahora, solo logeamos el error
                return null;
            }
        }

        // Maneja el envío del formulario para agregar un nuevo trabajador
        document.getElementById('frmT').addEventListener('submit', async e => {
            e.preventDefault();
            const form = e.target;
            const data = Object.fromEntries(new FormData(form).entries());
            const res = await api('/trabajadores', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify(data)
            });
            if (res && res.id) {
                form.reset();
                // Cierra el modal después de un registro exitoso
                const modal = bootstrap.Modal.getInstance(document.getElementById('modalAgregarTrabajador'));
                modal.hide();
                // Opcional: recarga la tabla de cotizaciones si es necesario
                load();
            }
        });

        // Agrega el evento de clic al botón de buscar
        document.getElementById('search-btn').addEventListener('click', load);
        // Agrega un evento 'change' al campo de fecha para que también recargue la tabla
        document.getElementById('fecha').addEventListener('change', load);


        // Función para cargar la lista de cotizaciones desde la API
        async function load() {
            // Se obtiene la fecha del campo. Si está vacío, no se envía el parámetro de fecha.
            const fecha = document.getElementById('fecha').value;
            // Se obtiene el valor del buscador
            const searchTerm = document.getElementById('search-input').value;
            
            // Construye la URL de la API con los parámetros
            let apiURL = `/cotizaciones`;
            const params = new URLSearchParams();
            if (fecha) {
                params.append('fecha', fecha);
            }
            if (searchTerm) {
                params.append('search', searchTerm);
            }
            
            if (params.toString()) {
                apiURL += `?${params.toString()}`;
            }

            const list = await api(apiURL);
            const tb = document.querySelector('#tbl tbody');
            tb.innerHTML = '';

            if (list && list.length > 0) {
                list.forEach(r => {
                    const tr = document.createElement('tr');
                    tr.innerHTML = `
                        <td>${r.nombre}</td>
                        <td>${r.turno}</td>
                        <td>${r.pago ? 'Sí' : 'No'}</td>
                        <td>${r.monto || 0}</td>
                        <td><button data-id="${r.trabajador_id}" data-fecha="${r.fecha}" class="btn btn-sm btn-outline-info">Toggle Pago</button></td>
                    `;
                    tb.appendChild(tr);
                });
            } else {
                const tr = document.createElement('tr');
                tr.innerHTML = `<td colspan="5" class="text-center">No hay cotizaciones para la fecha seleccionada.</td>`;
                tb.appendChild(tr);
            }

            // Agrega el evento de clic a los botones de la tabla
            tb.querySelectorAll('button').forEach(btn => btn.addEventListener('click', async (ev) => {
                const trabajador_id = ev.target.dataset.id;
                const fecha = ev.target.dataset.fecha;
                await api('/cotizaciones', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        trabajador_id,
                        fecha,
                        pago: 1
                    })
                });
                load(); // Recarga la tabla para reflejar el cambio
            }));
        }

        // Ya no se llama a 'load()' automáticamente al cargar la página
        // La tabla estará vacía hasta que el usuario seleccione una fecha o busque algo.
    </script>
</body>

</html>
