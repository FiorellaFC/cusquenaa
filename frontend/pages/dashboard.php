<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>
    <link href="../css/bootstrap.css" rel="stylesheet" />
    <script src="https://use.fontawesome.com/releases/v6.3.0/js/all.js" crossorigin="anonymous"></script>
    <style>
        body {
            padding-top: 50px;
        }
        .container-fluid {
            padding-top: 0rem;
        }

        .card {
            border-radius: 0.75rem;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        .btn-primary {
            background-color: #00695c;
            border: none;
        }

        .btn-primary:hover {
            background-color: #004d40;
        }
    </style>
</head>

<body>
    <?php require 'navbar.php'; ?>

    <div id="layoutSidenav"> <!-- PARA QUE NO SE SUPERPONGA EL SIDEBAR-->
        <!-- Sidebar -->
        <div id="layoutSidenav_nav">
            <?php require 'sidebear_admin.php'; ?>
        </div>

        <!-- Contenido principal -->
        <div id="layoutSidenav_content">
            <main class="container mt-5">
                <div class="card shadow-sm">
                    <div class="card-body">
                        <form class="row g-3 align-items-center">
                            <div class="col-md-3">
                                <label for="desde" class="form-label">Desde</label>
                                <input type="date" class="form-control" id="from" name="desde">
                            </div>
                            <div class="col-md-3">
                                <label for="hasta" class="form-label">Hasta</label>
                                <input type="date" class="form-control" id="to" name="hasta">
                            </div>
                            <div class="col-md-6 d-flex align-items-end">
                                <button id="btnLoad" type="button" class="btn btn-primary me-2">Actualizar</button>
                                <button type="button" class="btn btn-success" onclick="window.print()">Imprimir</button>
                            </div>
                        </form>
                    </div>
                </div>

                <div class="row mt-4 text-center">
                    <div class="col-md-4">
                        <div class="card bg-light shadow-sm">
                            <div class="card-body">
                                <h5 class="card-title fw-bold">Ingresos</h5>
                                <p id="ingresos" class="fs-4 mb-0">0</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card bg-light shadow-sm">
                            <div class="card-body">
                                <h5 class="card-title fw-bold">Egresos</h5>
                                <p id="egresos" class="fs-4 mb-0">0</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card bg-light shadow-sm">
                            <div class="card-body">
                                <h5 class="card-title fw-bold">Balance</h5>
                                <p id="balance" class="fs-4 mb-0">0</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Aquí un canvas para tu chart -->
                <div class="mt-4">
                    <canvas id="chart1" height="100"></canvas>
                </div>
            </main>
        </div>
    </div>

    <script src="../js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        async function load(){
            const from = document.getElementById('from').value || null;
            const to = document.getElementById('to').value || null;
            const q = new URLSearchParams({from, to});
            const data = await api('/api/balance/general?' + q.toString());

            document.getElementById('ingresos').textContent = data.ingresos.toFixed(2);
            document.getElementById('egresos').textContent = data.egresos.toFixed(2);
            document.getElementById('balance').textContent = data.balance.toFixed(2);

            const ctx = document.getElementById('chart1').getContext('2d');
            if(window._chart1) window._chart1.destroy();
            window._chart1 = new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: ['Ingresos','Egresos'],
                    datasets: [{
                        label: 'S/',
                        data: [data.ingresos, data.egresos],
                        backgroundColor: ['#28a745','#dc3545']
                    }]
                }
            });
        }
        document.getElementById('btnLoad').addEventListener('click', load);
        load();
    </script>
</body>

</html>