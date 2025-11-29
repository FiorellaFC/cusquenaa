<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>Reporte de Citas - La Cusqueña</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- CSS -->
    <link rel="stylesheet" href="../css/bootstrap.css">
    <link rel="stylesheet" href="../css/dashboard.css">
    <link rel="stylesheet" href="../css/sidebar.css">
    <link rel="stylesheet" href="../css/navbar.css">

    <script src="https://use.fontawesome.com/releases/v6.3.0/js/all.js" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/jspdf@2.5.1/dist/jspdf.umd.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/jspdf-autotable@3.8.2/dist/jspdf.plugin.autotable.min.js"></script>

    <!-- LIBRERÍA PARA EXCEL (solo esta línea nueva) -->
    <script src="https://unpkg.com/xlsx@0.18.5/dist/xlsx.full.min.js"></script>

    <style>
        body { background: #eef1f5; font-family: 'Poppins', sans-serif; }
        .stats-card { border-radius: 20px; padding: 30px; text-align: center; color: white; box-shadow: 0 12px 30px rgba(0,0,0,0.25); height: 100%; }
        .table th { background: #212529; color: white; }
        h1 { color: #1a1a1a; font-weight: 800; }
    </style>
</head>
<body class="sb-nav-fixed">

    <!-- NAVBAR -->
    <nav class="sb-topnav navbar navbar-expand navbar-dark bg-dark fixed-top">
        <a class="navbar-brand ps-3" href="base2.php">La Cusqueña</a>
        <button class="btn btn-link btn-sm text-white me-4" id="sidebarToggle">
            <i class="fas fa-bars"></i>
        </button>
    </nav>

    <div id="layoutSidenav">
        <!-- SIDEBAR -->
        <div id="layoutSidenav_nav">
            <script>
                fetch('sidebear_Admin.php')
                    .then(r => r.text())
                    .then(html => document.getElementById('layoutSidenav_nav').innerHTML = html)
                    .catch(() => document.getElementById('layoutSidenav_nav').innerHTML = '<div class="p-4 text-center text-muted">Sidebar no disponible</div>');
            </script>
        </div>

        <div id="layoutSidenav_content">
            <main class="container-xl my-5">
                <div class="container-fluid px-4">

                    <h1 class="text-center mb-5 fw-bold" style="font-size: 2.6rem;">
                        Reporte de Citas Completadas
                    </h1>

                    <!-- ESTADÍSTICAS -->
                    <div class="row g-4 mb-5">
                        <div class="col-md-3">
                            <div class="stats-card bg-primary">
                                <h2 id="totalCitas">0</h2>
                                <p>Total Completadas</p>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="stats-card bg-success">
                                <h4 id="servicioTop">-</h4>
                                <p>Servicio Más Solicitado</p>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="stats-card bg-warning text-dark">
                                <h4 id="clienteTop">-</h4>
                                <p>Cliente Más Frecuente</p>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="stats-card bg-success">
                                <h2 id="totalGanancia">S/. 0.00</h2>
                                <p>Ganancias del Periodo</p>
                            </div>
                        </div>
                    </div>

                    <!-- FILTROS -->
                    <div class="card shadow mb-4">
                        <div class="card-body">
                            <div class="row g-3 align-items-end">
                                <div class="col-md-5">
                                    <label class="form-label fw-bold">Desde</label>
                                    <input type="date" id="fechaInicio" class="form-control">
                                </div>
                                <div class="col-md-5">
                                    <label class="form-label fw-bold">Hasta</label>
                                    <input type="date" id="fechaFin" class="form-control">
                                </div>
                                <div class="col-md-2">
                                    <button class="btn btn-dark w-100" onclick="cargarReporte()">Filtrar</button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- BOTONES PDF + EXCEL -->
                    <div class="text-end mb-4">
                        <button class="btn btn-danger btn-lg me-3" onclick="exportarPDF()">
                            <i class="fas fa-file-pdf"></i> Exportar PDF
                        </button>
                        <button class="btn btn-success btn-lg" onclick="exportarExcel()">
                            <i class="fas fa-file-excel"></i> Exportar Excel
                        </button>
                    </div>

                    <!-- TABLA -->
                    <div class="card shadow">
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-hover mb-0" id="tablaCompleta">
                                   <thead class="table-dark">
                                        <tr>
                                            <th>Fecha</th>
                                            <th>Cliente</th>
                                            <th>Teléfono</th>
                                            <th>Servicios</th>
                                            <th>Hora</th>
                                            <th>Monto</th> <th>Estado</th>
                                        </tr>
                                    </thead>
                                    <tbody id="tablaCitas"></tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                </div>
            </main>
        </div>
    </div>

    <script src="../js/bootstrap.bundle.min.js"></script>
    
    <script src="../js/functions/reporte_citas.js"></script>

</body>
</html>