<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8" />
    <title>Gestión de Promociones - La Cusqueña</title>
    <link rel="stylesheet" href="../css/dashboard.css">
    <link rel="stylesheet" href="../css/sidebar.css">
    <link rel="stylesheet" href="../css/navbar.css">
    <link href="../css/bootstrap.css" rel="stylesheet" />
    <script src="https://use.fontawesome.com/releases/v6.3.0/js/all.js" crossorigin="anonymous"></script>
    <style>
        body { background: #eef1f5; }
        .promo-card-admin { transition: all 0.3s; border-radius: 15px; box-shadow: 0 4px 15px rgba(0,0,0,0.1); }
        .promo-card-admin:hover { transform: translateY(-8px); box-shadow: 0 15px 30px rgba(0,0,0,0.2); }
        .badge-admin { position: absolute; top: 12px; right: 12px; font-size: 0.8rem; padding: 6px 12px; border-radius: 20px; }
    </style>
</head>
<body class="sb-nav-fixed">

    <!-- NAVBAR -->
    <nav class="sb-topnav navbar navbar-expand navbar-dark bg-dark fixed-top">
        <a class="navbar-brand ps-3" href="base.php">La Cusqueña</a>
        <button class="btn btn-link btn-sm me-4" id="sidebarToggle"><i class="fas fa-bars"></i></button>
        <ul class="navbar-nav ms-auto me-3">
            <li class="nav-item dropdown">
                <a class="nav-link dropdown-toggle" href="#" data-bs-toggle="dropdown"><i class="fas fa-user"></i></a>
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
                    .then(html => document.getElementById('layoutSidenav_nav').innerHTML = html);
            </script>
        </div>

        <!-- CONTENIDO -->
        <div id="layoutSidenav_content">
            <main class="container-xl my-4">
                <div class="container-fluid px-4">
                   <h1 class="mb-5 text-center fw-bold" style="color: #1a1a1a; font-size: 2.4rem;">
                        <i class="fas fa-tag me-3"></i> Gestión de Promociones
                    </h1>
                    <div class="text-end mb-4">
                        <button class="btn btn-success btn-lg" data-bs-toggle="modal" data-bs-target="#modalPromo">
                            <i class="fas fa-plus-circle me-2"></i>Nueva Promoción
                        </button>
                    </div>

                    <div class="row g-4" id="listaPromociones">
                        <div class="col-12 text-center py-5">
                            <div class="spinner-border text-danger" style="width:3rem;height:3rem;"></div>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <!-- MODAL -->
    <div class="modal fade" id="modalPromo" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content rounded-4">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title" id="modalTitulo">Nueva Promoción</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="formPromo">
                        <input type="hidden" id="promoId" value="0">
                        <div class="row g-3">
                            <div class="col-md-8">
                                <label class="form-label fw-bold">Título</label>
                                <input type="text" class="form-control form-control-lg" id="titulo" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-bold">Badge (opcional)</label>
                                <input type="text" class="form-control" id="badge" placeholder="-20%, Combo, Gratis...">
                            </div>
                        </div>

                        <div class="my-3">
                            <label class="form-label fw-bold">Descripción</label>
                            <textarea class="form-control" rows="3" id="descripcion" required></textarea>
                        </div>

                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Precio (S/)</label>
                                <input type="number" step="0.01" class="form-control form-control-lg text-success fw-bold" id="precio" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Ícono (fácil de elegir)</label>
                                <select class="form-select form-select-lg" id="icono" required>
                                    <option value="" disabled selected>→ Elige un ícono</option>
                                    <option value="fa-oil-can">Aceite / Cambio de aceite</option>
                                    <option value="fa-car-side">Lavado y detailing</option>
                                    <option value="fa-tools">Mantenimiento general</option>
                                    <option value="fa-wrench">Reparaciones mecánicas</option>
                                    <option value="fa-car-battery">Batería</option>
                                    <option value="fa-tire">Neumáticos y alineación</option>
                                    <option value="fa-brake-warning">Frenos</option>
                                    <option value="fa-fan">Aire acondicionado</option>
                                    <option value="fa-gas-pump">Combustible</option>
                                    <option value="fa-bolt">Sistema eléctrico</option>
                                    <option value="fa-percentage">Descuento especial</option>
                                    <option value="fa-gift">Combo / Regalo</option>
                                    <option value="fa-star">Servicio premium</option>
                                    <option value="fa-fire">Más vendido</option>
                                </select>
                                <div class="text-center mt-2">
                                    <i class="fas fa-3x text-danger" id="iconoPreview"></i>
                                </div>
                            </div>
                        </div>

                        <div class="form-check mt-4">
                            <input class="form-check-input" type="checkbox" id="activa" checked>
                            <label class="form-check-label fw-bold">Mostrar en la página web</label>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-danger" onclick="guardarPromocion()">Guardar Promoción</button>
                </div>
            </div>
        </div>
    </div>

    <script src="../js/bootstrap.bundle.min.js"></script>
    <script>
        const API = '../../backend/api/controllers/vista_promociones/promocionesInterno.php';

        function cargarPromociones() {
            fetch(API)
                .then(r => r.json())
                .then(promos => {
                    const cont = document.getElementById('listaPromociones');
                    if (promos.length === 0) {
                        cont.innerHTML = `<div class="col-12 text-center py-5"><h4 class="text-muted">No hay promociones aún</h4></div>`;
                        return;
                    }
                    cont.innerHTML = promos.map(p => `
                        <div class="col-md-6 col-lg-4">
                            <div class="card promo-card-admin position-relative h-100">
                                ${p.badge ? `<span class="badge-admin bg-danger text-white">${p.badge}</span>` : ''}
                                <div class="card-body text-center p-4">
                                    <i class="fas ${p.icono} fa-4x mb-3 text-danger"></i>
                                    <h5 class="card-title fw-bold">${p.titulo}</h5>
                                    <p class="text-muted small">${p.descripcion}</p>
                                    <h4 class="text-success fw-bold">S/ ${parseFloat(p.precio).toFixed(2)}</h4>
                                    <span class="badge ${p.activa == 1 ? 'bg-success' : 'bg-secondary'}">
                                        ${p.activa == 1 ? 'Visible' : 'Oculto'}
                                    </span>
                                    <div class="mt-3">
                                        <button class="btn btn-warning btn-sm" onclick="editar(${p.id})"><i class="fas fa-edit"></i></button>
                                        <button class="btn btn-danger btn-sm ms-2" onclick="eliminar(${p.id})"><i class="fas fa-trash"></i></button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    `).join('');
                });
        }

        function editar(id) {
            fetch(API).then(r=>r.json()).then(promos => {
                const p = promos.find(x => x.id == id);
                document.getElementById('modalTitulo').textContent = 'Editar Promoción';
                document.getElementById('promoId').value = p.id;
                document.getElementById('titulo').value = p.titulo;
                document.getElementById('descripcion').value = p.descripcion;
                document.getElementById('precio').value = p.precio;
                document.getElementById('badge').value = p.badge || '';
                document.getElementById('icono').value = p.icono;
                document.getElementById('activa').checked = p.activa == 1;
                document.getElementById('iconoPreview').className = 'fas ' + p.icono + ' fa-3x text-danger';
                new bootstrap.Modal(document.getElementById('modalPromo')).show();
            });
        }

        async function guardarPromocion() {
            const data = {
                id: document.getElementById('promoId').value,
                titulo: document.getElementById('titulo').value,
                descripcion: document.getElementById('descripcion').value,
                precio: document.getElementById('precio').value,
                badge: document.getElementById('badge').value,
                icono: document.getElementById('icono').value,
                activa: document.getElementById('activa').checked
            };

            await fetch(API, {
                method: 'POST',
                headers: {'Content-Type': 'application/json'},
                body: JSON.stringify(data)
            });

            bootstrap.Modal.getInstance(document.getElementById('modalPromo')).hide();
            cargarPromociones();
        }

        async function eliminar(id) {
            if (confirm('¿Seguro que quieres eliminar esta promoción?')) {
                await fetch(API, { method: 'DELETE', headers: {'Content-Type': 'application/json'}, body: JSON.stringify({id}) });
                cargarPromociones();
            }
        }

        // Vista previa del icono
        document.getElementById('icono').addEventListener('change', e => {
            document.getElementById('iconoPreview').className = 'fas ' + e.target.value + ' fa-3x text-danger';
        });

        // Limpiar modal al abrir nueva
        document.getElementById('modalPromo').addEventListener('hidden.bs.modal', () => {
            document.getElementById('formPromo').reset();
            document.getElementById('promoId').value = '0';
            document.getElementById('modalTitulo').textContent = 'Nueva Promoción';
            document.getElementById('iconoPreview').className = 'fas fa-tag fa-3x text-danger';
        });

        cargarPromociones();
    </script>
</body>
</html>