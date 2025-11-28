<?php
// contacto_interno.php
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Mensajes de Contacto</title>

    <!-- ESTILOS -->
    <link rel="stylesheet" href="../css/dashboard.css">
    <link rel="stylesheet" href="../css/sidebar.css">
    <link rel="stylesheet" href="../css/navbar.css">
    <link href="../css/bootstrap.css" rel="stylesheet" />
    <script src="https://use.fontawesome.com/releases/v6.3.0/js/all.js" crossorigin="anonymous"></script>

    <style>
        body { background: #eef1f5; }
        .card { border-radius: 15px; border: none; }
        .table-container {
            max-height: 600px;
            overflow-y: auto;
            overflow-x: hidden;
        }
        .table-container thead th {
            position: sticky;
            top: 0;
            z-index: 10;
            background: #212529;
            color: #fff;
        }
        .td-mensaje {
            max-width: 350px;
            min-width: 200px;
            vertical-align: middle;
        }
        .badge-mensaje {
            display: inline-block;
            background: #0d6efd;
            color: white;
            padding: 10px 14px;
            border-radius: 12px;
            font-size: 0.92rem;
            line-height: 1.5;
            max-width: 100%;
            overflow-wrap: break-word;
            word-break: break-word;
            white-space: normal;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }
        @media (max-width: 768px) {
            .td-mensaje { max-width: 250px; }
        }
    </style>
</head>

<body class="sb-nav-fixed">

<!-- NAVBAR -->
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
    <!-- SIDEBAR -->
    <div id="layoutSidenav_nav">
        <script>
            fetch('sidebear_Admin.php')
                .then(r => r.text())
                .then(html => document.getElementById('layoutSidenav_nav').innerHTML = html)
                .catch(e => console.error('Error cargando sidebar:', e));
        </script>
    </div>

    <!-- CONTENIDO -->
    <div id="layoutSidenav_content">
        <main class="container-xl my-4">
            <div class="container-fluid px-4">

                <h1 class="mb-4 text-center fw-bold">
                    <i class="fas fa-envelope-open-text me-2"></i> Bandeja de Contacto
                </h1>

                <!-- FILTROS -->
                <div class="row g-3 mb-4">
                    <div class="col-md-4">
                        <label class="form-label">Nombre:</label>
                        <input id="filtroNombre" type="text" class="form-control" placeholder="Buscar por nombre">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Correo:</label>
                        <input id="filtroCorreo" type="text" class="form-control" placeholder="Buscar por correo">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Fecha:</label>
                        <input id="filtroFecha" type="date" class="form-control">
                    </div>
                    <div class="col-12 d-flex justify-content-end gap-2">
                        <button type="button" class="btn btn-dark" onclick="cargarMensajes()">
                            <i class="fas fa-search"></i> Buscar
                        </button>
                        <button type="button" class="btn btn-secondary" onclick="limpiarFiltros()">
                            <i class="fas fa-times"></i> Limpiar
                        </button>
                    </div>
                </div>

                <!-- TABLA -->
                <div class="card shadow-lg">
                    <div class="card-body p-3">
                        <div class="table-container">
                            <table class="table table-striped table-hover text-center align-middle">
                                <thead class="table-dark">
                                    <tr>
                                        <th>ID</th>
                                        <th>Nombre Completo</th>
                                        <th>Correo</th>
                                        <th>Mensaje</th>
                                        <th>Fecha</th>
                                    </tr>
                                </thead>
                                <tbody id="tablaContacto">
                                    <!-- JS -->
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

            </div>
        </main>
    </div>
</div>

<script src="../js/bootstrap.bundle.min.js"></script>

<script>
// ==== FUNCIONES ====
function limpiarFiltros() {
    document.getElementById("filtroNombre").value = "";
    document.getElementById("filtroCorreo").value = "";
    document.getElementById("filtroFecha").value = "";
    cargarMensajes();
}

// Formato bonito: 2025-11-27 → 27-11-2025
function formatoFechaBonito(fecha) {
    if (!fecha) return '';
    const soloFecha = fecha.split(' ')[0];
    const [y, m, d] = soloFecha.split('-');
    return `${d}-${m}-${y}`;
}

async function cargarMensajes() {
    const nombre = document.getElementById("filtroNombre").value.trim();
    const correo = document.getElementById("filtroCorreo").value.trim();
    const fecha  = document.getElementById("filtroFecha").value; // ← yyyy-mm-dd

    const params = new URLSearchParams();
    if (nombre) params.append('nombre', nombre);
    if (correo) params.append('correo', correo);
    if (fecha)  params.append('fecha', fecha);   // ← ESTE ES EL FORMATO CORRECTO

    const url = `../../backend/api/controllers/vista_contacto/contactoInterno.php?${params.toString()}`;

    // ¡¡ESTO ES CLAVE PARA DEBUG!!
    console.log("URL enviada al backend →", url);

    try {
        const response = await fetch(url);
        if (!response.ok) throw new Error(`HTTP ${response.status}`);

        const data = await response.json();
        let html = "";

        if (!data || data.length === 0) {
            html = `<tr><td colspan="5" class="text-muted py-5">No se encontraron mensajes con esos filtros</td></tr>`;
        } else {
            data.forEach(msg => {
                const mensajeLimpio = msg.mensaje
                    .replace(/&/g, "&amp;")
                    .replace(/</g, "&lt;")
                    .replace(/>/g, "&gt;")
                    .replace(/\n/g, "<br>");

                const fechaBonita = formatoFechaBonito(msg.fecha_envio);

                html += `
                <tr>
                    <td><strong>${msg.id}</strong></td>
                    <td>${msg.nombre_completo}</td>
                    <td>${msg.correo}</td>
                    <td class="td-mensaje"><span class="badge-mensaje">${mensajeLimpio}</span></td>
                    <td><strong>${fechaBonita}</strong></td>
                </tr>`;
            });
        }

        document.getElementById("tablaContacto").innerHTML = html;

    } catch (err) {
        console.error("Error completo:", err);
        document.getElementById("tablaContacto").innerHTML = 
            `<tr><td colspan="5" class="text-danger py-4">Error de conexión o servidor</td></tr>`;
    }
}

// Cargar al iniciar
cargarMensajes();
</script>

</body>
</html>