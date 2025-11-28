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
                        Reporte de Citas Confirmadas
                    </h1>

                    <!-- ESTADÍSTICAS -->
                    <div class="row g-4 mb-5">
                        <div class="col-md-4">
                            <div class="stats-card bg-primary">
                                <h2 id="totalCitas">0</h2>
                                <p>Total Confirmadas</p>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="stats-card bg-success">
                                <h4 id="servicioTop">-</h4>
                                <p>Servicio Más Solicitado</p>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="stats-card bg-warning text-dark">
                                <h4 id="clienteTop">-</h4>
                                <p>Cliente Más Frecuente</p>
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
                                            <th>Servicio</th>
                                            <th>Hora</th>
                                            <th>Estado</th>
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
    <script>
        const API = '../../backend/api/controllers/reporte_citas.php';

        async function cargarReporte() {
            const inicio = document.getElementById('fechaInicio').value || '2020-01-01';
            const fin = document.getElementById('fechaFin').value || new Date().toISOString().split('T')[0];

            try {
                const res = await fetch(`${API}?inicio=${inicio}&fin=${fin}`);
                if (!res.ok) throw new Error(`Error ${res.status}`);
                const data = await res.json();

                document.getElementById('totalCitas').textContent = data.total || 0;
                document.getElementById('servicioTop').textContent = data.servicio_top || 'N/A';
                document.getElementById('clienteTop').textContent = data.cliente_top || 'N/A';

                const tbody = document.getElementById('tablaCitas');
                if (!data.citas || data.citas.length === 0) {
                    tbody.innerHTML = `<tr><td colspan="6" class="text-center py-5 text-muted">No hay citas confirmadas</td></tr>`;
                    return;
                }

                tbody.innerHTML = data.citas.map(c => `
                    <tr>
                        <td><strong>${c.fecha}</strong></td>
                        <td>${c.nombre_completo.trim()}</td>
                        <td>${c.telefono_cliente || '-'}</td>
                        <td><span class="badge bg-primary">${c.servicio_nombre}</span></td>
                        <td>${c.hora.substring(0,5)}</td>
                        <td><span class="badge bg-success">Confirmada</span></td>
                    </tr>
                `).join('');

            } catch (err) {
                console.error("Error:", err);
                document.getElementById('tablaCitas').innerHTML = 
                    `<tr><td colspan="6" class="text-center text-danger py-4">Error al cargar datos</td></tr>`;
            }
        }

        function exportarPDF() {
            const { jsPDF } = window.jspdf;
            const doc = new jsPDF('l', 'mm', 'a4');
            const hoy = new Date().toLocaleDateString('es-PE');

            doc.setFontSize(20);
            doc.text('REPORTE DE CITAS CONFIRMADAS - LA CUSQUEÑA', 148, 22, { align: 'center' });
            doc.setFontSize(11);
            doc.text(`Generado el: ${hoy}`, 20, 35);

            const filas = Array.from(document.querySelectorAll('#tablaCitas tr')).map(tr =>
                Array.from(tr.cells).map(td => td.innerText)
            );

            doc.autoTable({
                head: [['Fecha', 'Cliente', 'Teléfono', 'Servicio', 'Hora', 'Estado']],
                body: filas.length > 0 ? filas : [['No hay datos']],
                startY: 45,
                theme: 'grid',
                headStyles: { fillColor: [220, 53, 69] }
            });

            doc.save(`Reporte_Citas_${hoy.replace(/\//g, '-')}.pdf`);
        }

        // NUEVA FUNCIÓN EXCEL
        function exportarExcel() {
            const filas = Array.from(document.querySelectorAll('#tablaCitas tr')).map(tr =>
                Array.from(tr.cells).map(td => td.innerText)
            );

            // Si no hay datos, ponemos una fila vacía
            const datosParaExcel = filas.length > 0 ? filas : [["Sin datos","","","","",""]];

            const wb = XLSX.utils.book_new();
            const ws = XLSX.utils.aoa_to_sheet([
                ["REPORTE DE CITAS - LA CUSQUEÑA"],
                [`Generado el: ${new Date().toLocaleDateString('es-PE')}`],
                [`Total de citas: ${document.getElementById('totalCitas').textContent}`],
                [], // línea vacía
                ["Fecha", "Cliente", "Teléfono", "Servicio", "Hora", "Estado"],
                ...datosParaExcel
            ]);

            // Ajustar ancho de columnas
            ws['!cols'] = [{wch:15}, {wch:30}, {wch:15}, {wch:25}, {wch:10}, {wch:15}];

            XLSX.utils.book_append_sheet(wb, ws, "Citas");
            XLSX.writeFile(wb, `Reporte_LaCusquena_${new Date().toISOString().slice(0,10)}.xlsx`);
        }

        // Cargar al iniciar
        window.addEventListener('DOMContentLoaded', () => {
            const hoy = new Date().toISOString().split('T')[0];
            const hace30 = new Date(Date.now() - 30*24*60*60*1000).toISOString().split('T')[0];
            document.getElementById('fechaInicio').value = hace30;
            document.getElementById('fechaFin').value = hoy;
            cargarReporte();
        });
    </script>
</body>
</html>