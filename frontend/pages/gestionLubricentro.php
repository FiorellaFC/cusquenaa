<?php
require_once '../../backend/includes/auth.php';
verificarPermiso(['Administrador', 'Secretaria']);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Lubricentro Cusqueña - Gestión de Productos</title>
    <link href="../css/bootstrap.css" rel="stylesheet" />
    <script src="https://use.fontawesome.com/releases/v6.3.0/js/all.js" crossorigin="anonymous"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.8.2/jspdf.plugin.autotable.min.js"></script>
    <style>
        .table {
            width: 100%;
            table-layout: auto;
            border-collapse: collapse;
        }
        .table th,
        .table td {
            text-align: center;
            vertical-align: middle;
            overflow-wrap: break-word;
            max-width: 200px;
            padding: 10px;
            border: 1px solid #dee2e6;
        }
        .table th {
            background-color: #343a40;
            color: white;
        }
        .table td button {
            margin: 0 5px;
            white-space: nowrap;
        }
        .table-responsive {
            overflow-x: auto;
        }
        @media print {
            .no-exportar {
                display: none; /* Oculta la columna Acciones al imprimir */
            }
            .table th,
            .table td {
                border: 1px solid black;
                padding: 8px;
                text-align: center;
            }
            .table th {
                background-color: #343a40;
                color: white;
            }
        }
        @media (max-width: 576px) {
            .table th,
            .table td {
                font-size: 14px;
                max-width: 150px;
            }
        }
        .btn-custom {
            background-color: #007bff; /* Color azul de Bootstrap btn-primary */
            border: 1px solid #007bff;
            color: #fff; /* Texto blanco */
            padding: 6px 12px;
            font-size: 14px;
            border-radius: 4px;
            text-decoration: none;
        }
        .btn-custom:hover {
            background-color: #0056b3; /* Azul más oscuro para hover, similar a btn-primary */
            border-color: #0056b3;
            color: #fff;
        }
    </style>
</head>
<body class="sb-nav-fixed">
    <nav class="sb-topnav navbar navbar-expand navbar-dark bg-dark fixed-top">
        <?php if ($_SESSION['rol'] === 'Administrador'): ?>
        <a class="navbar-brand ps-3" href="base.php">La Cusqueña</a>
        <?php endif; ?>
        <?php if ($_SESSION['rol'] === 'Secretaria'): ?>
        <a class="navbar-brand ps-3" href="base2.php">La Cusqueña</a>
        <?php endif; ?>
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
        <div id="layoutSidenav_nav">
            <?php if ($_SESSION['rol'] === 'Administrador'): ?>
            <script>
                fetch('sidebear_Admin.php')
                    .then(r => r.text())
                    .then(html => document.getElementById('layoutSidenav_nav').innerHTML = html)
                    .catch(e => console.error('Error cargando sidebar:', e));
            </script>
            <?php endif; ?>
            <?php if ($_SESSION['rol'] === 'Secretaria'): ?>
            <script>
                fetch('sidebear_secre.php')
                    .then(r => r.text())
                    .then(html => document.getElementById('layoutSidenav_nav').innerHTML = html)
                    .catch(e => console.error('Error cargando sidebar:', e));
            </script>
            <?php endif; ?>
        </div>
        <div id="layoutSidenav_content">
            <main class="container-xl my-2 col-10 mx-auto">
                <div class="container-fluid px-4">
                    <h1 class="mb-4 text-center">Gestión de Lubricentro</h1>

                    <div class="row mb-4">
                        <div class="col-12 d-flex justify-content-end align-items-center">
                            <button class="btn btn-secondary me-2" id="btnImprimir">
                                <i class="fas fa-print"></i> Imprimir
                            </button>
                            <button class="btn btn-info text-white me-2" id="btnExportarPDF">
                                <i class="fas fa-file-pdf"></i> Exportar PDF
                            </button>
                            <a href="#" class="btn btn-primary me-2" data-bs-toggle="modal" data-bs-target="#modalAgregarProducto">
                                <i class="fas fa-plus"></i> Agregar Producto
                            </a>
                            <a href="#" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#modalRegistrarMovimiento">
                                <i class="fas fa-exchange-alt"></i> Registrar Movimiento
                            </a>
                        </div>
                    </div>

                    <!-- Modal para Agregar Producto -->
                    <div class="modal fade" id="modalAgregarProducto" tabindex="-1" aria-labelledby="modalAgregarProductoLabel" aria-hidden="true">
                        <div class="modal-dialog modal-dialog-centered">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h3 class="modal-title" id="modalAgregarProductoLabel">Agregar Producto</h3>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                </div>
                                <div class="modal-body">
                                    <form id="frmProd">
                                        <div class="mb-3">
                                            <label for="nombre" class="form-label">Nombre</label>
                                            <input name="nombre" class="form-control" placeholder="Nombre" required/>
                                        </div>
                                        <div class="mb-3">
                                            <label for="descripcion" class="form-label">Descripción</label>
                                            <input name="descripcion" class="form-control" placeholder="Descripción"/>
                                        </div>
                                        <div class="mb-3">
                                            <label for="sku" class="form-label">SKU</label>
                                            <input name="sku" class="form-control" placeholder="SKU"/>
                                        </div>
                                        <div class="mb-3">
                                            <label for="precio_compra" class="form-label">Precio de compra</label>
                                            <input name="precio_compra" class="form-control" placeholder="Precio compra" type="number" step="0.01"/>
                                        </div>
                                        <div class="mb-3">
                                            <label for="precio_venta" class="form-label">Precio de venta</label>
                                            <input name="precio_venta" class="form-control" placeholder="Precio venta" type="number" step="0.01"/>
                                        </div>
                                        <div class="mb-3">
                                            <label for="stock" class="form-label">Stock</label>
                                            <input name="stock" class="form-control" placeholder="Stock" type="number"/>
                                        </div>
                                        <div class="modal-footer d-flex justify-content-center">
                                            <button type="submit" class="btn btn-primary">Crear</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Modal para Registrar Movimiento -->
                    <div class="modal fade" id="modalRegistrarMovimiento" tabindex="-1" aria-labelledby="modalRegistrarMovimientoLabel" aria-hidden="true">
                        <div class="modal-dialog modal-dialog-centered">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h3 class="modal-title" id="modalRegistrarMovimientoLabel">Registrar Movimiento</h3>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                </div>
                                <div class="modal-body">
                                    <form id="frmMov">
                                        <div class="mb-3">
                                            <label for="tipo" class="form-label">Tipo de Movimiento</label>
                                            <select name="tipo" class="form-select">
                                                <option value="compra">Compra</option>
                                                <option value="venta">Venta</option>
                                            </select>
                                        </div>
                                        <div class="mb-3">
                                            <label for="producto_id" class="form-label">ID Producto</label>
                                            <input name="producto_id" class="form-control" placeholder="ID producto" type="number" required/>
                                        </div>
                                        <div class="mb-3">
                                            <label for="cantidad" class="form-label">Cantidad</label>
                                            <input name="cantidad" class="form-control" placeholder="Cantidad" type="number" required/>
                                        </div>
                                        <div class="mb-3">
                                            <label for="precio_unit" class="form-label">Precio Unitario</label>
                                            <input name="precio_unit" class="form-control" placeholder="Precio unit" type="number" step="0.01" required/>
                                        </div>
                                        <div class="mb-3">
                                            <label for="fecha" class="form-label">Fecha</label>
                                            <input name="fecha" class="form-control" type="date" required/>
                                        </div>
                                        <div class="modal-footer d-flex justify-content-center">
                                            <button type="submit" class="btn btn-success">Registrar</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div id="contenedorProductosImprimir">
                        <div class="table-responsive my-4">
                            <table class="table table-bordered table-hover text-center">
                                <thead class="table-dark">
                                    <tr>
                                        <th>ID</th>
                                        <th>Nombre</th>
                                        <th>Descripción</th>
                                        <th>SKU</th>
                                        <th>Stock</th>
                                        <th>Precio Compra</th>
                                        <th>Precio Venta</th>
                                        <th class="no-exportar">Acciones</th>
                                    </tr>
                                </thead>
                                <tbody id="tablaProductos" class="align-middle">
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>
    <script src="../js/bootstrap.bundle.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>
    <script>
        // Suponiendo que el archivo `api.js` está disponible en la misma ruta
        // Si no existe, deberás crear la función api() para manejar las llamadas a la API.
        async function api(url, options) {
            const res = await fetch(url, options);
            return res.json();
        }

        async function load() {
            try {
                const list = await api('/api/productos');
                const tableBody = document.querySelector('#tablaProductos');
                tableBody.innerHTML = '';
                list.forEach(p => {
                    const tr = document.createElement('tr');
                    tr.innerHTML = `
                        <td>${p.id}</td>
                        <td>${p.nombre}</td>
                        <td>${p.descripcion || ''}</td>
                        <td>${p.sku || ''}</td>
                        <td>${p.stock}</td>
                        <td>S/. ${p.precio_compra.toFixed(2)}</td>
                        <td>S/. ${p.precio_venta.toFixed(2)}</td>
                        <td>
                            <button class="btn btn-warning btn-sm" onclick="editProduct(${p.id})">
                                <i class="fas fa-edit"></i>
                            </button>
                            <button class="btn btn-danger btn-sm" onclick="deleteProduct(${p.id})">
                                <i class="fas fa-trash-alt"></i>
                            </button>
                        </td>
                    `;
                    tableBody.appendChild(tr);
                });
            } catch (error) {
                console.error('Error al cargar productos:', error);
            }
        }

        document.getElementById('frmProd').addEventListener('submit', async e => {
            e.preventDefault();
            const data = Object.fromEntries(new FormData(e.target).entries());
            data.precio_compra = parseFloat(data.precio_compra || 0);
            data.precio_venta = parseFloat(data.precio_venta || 0);
            data.stock = parseInt(data.stock || 0);
            const res = await api('/api/productos', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(data)
            });
            if (res.id) {
                e.target.reset();
                load();
                const modal = bootstrap.Modal.getInstance(document.getElementById('modalAgregarProducto'));
                modal.hide();
            } else {
                console.error('Error al agregar producto:', res.error);
            }
        });

        document.getElementById('frmMov').addEventListener('submit', async e => {
            e.preventDefault();
            const data = Object.fromEntries(new FormData(e.target).entries());
            const body = {
                producto_id: +data.producto_id,
                cantidad: +data.cantidad,
                precio_unit: +data.precio_unit,
                fecha: data.fecha
            };
            const url = data.tipo === 'compra' ? '/api/compra' : '/api/venta';
            const res = await api(url, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(body)
            });
            if (!res.error) {
                e.target.reset();
                load();
                const modal = bootstrap.Modal.getInstance(document.getElementById('modalRegistrarMovimiento'));
                modal.hide();
            } else {
                console.error('Error al registrar movimiento:', res.error);
            }
        });
        
        // Funciones de impresión y exportación a PDF
        document.addEventListener('DOMContentLoaded', function () {
            const btnImprimir = document.getElementById('btnImprimir');
            const btnExportarPDF = document.getElementById('btnExportarPDF');
            const { jsPDF } = window.jspdf;

            // Impresión
            btnImprimir.addEventListener('click', () => {
                const table = document.getElementById('tablaProductos');
                let tableHTML = '<table style="border-collapse: collapse; width: 100%;"><thead><tr>';

                // Construir cabecera con los nombres de las columnas, excluyendo "Acciones"
                tableHTML += `
                    <th style="background-color: #343a40; color: white; font-weight: bold; border: 1px solid #000; padding: 8px; text-align: center;">ID</th>
                    <th style="background-color: #343a40; color: white; font-weight: bold; border: 1px solid #000; padding: 8px; text-align: center;">Nombre</th>
                    <th style="background-color: #343a40; color: white; font-weight: bold; border: 1px solid #000; padding: 8px; text-align: center;">Descripción</th>
                    <th style="background-color: #343a40; color: white; font-weight: bold; border: 1px solid #000; padding: 8px; text-align: center;">SKU</th>
                    <th style="background-color: #343a40; color: white; font-weight: bold; border: 1px solid #000; padding: 8px; text-align: center;">Stock</th>
                    <th style="background-color: #343a40; color: white; font-weight: bold; border: 1px solid #000; padding: 8px; text-align: center;">Precio Compra</th>
                    <th style="background-color: #343a40; color: white; font-weight: bold; border: 1px solid #000; padding: 8px; text-align: center;">Precio Venta</th>
                `;
                tableHTML += '</tr></thead><tbody>';

                // Construir cuerpo sin "Acciones"
                table.querySelectorAll('tr').forEach(row => {
                    tableHTML += '<tr>';
                    const cells = row.querySelectorAll('td');
                    cells.forEach((cell, index) => {
                        if (index < 7) { // Excluir la columna de acciones (índice 7)
                            tableHTML += `<td style="border: 1px solid #000; padding: 8px; text-align: center;">${cell.textContent}</td>`;
                        }
                    });
                    tableHTML += '</tr>';
                });
                tableHTML += '</tbody></table>';

                const ventana = window.open('', '', 'height=700,width=900');
                ventana.document.write('<html><head><title>Imprimir Productos</title>');
                ventana.document.write(`
                    <style>
                        h1 {
                            text-align: center;
                            font-size: 24px;
                            font-weight: bold;
                            margin-bottom: 20px;
                        }
                    </style>
                `);
                ventana.document.write('</head><body>');
                ventana.document.write('<h1>Gestión de Productos - Lubricentro Cusqueña</h1>');
                ventana.document.write(tableHTML);
                ventana.document.write('</body></html>');
                ventana.document.close();
                ventana.print();
            });

            // Exportación a PDF
            btnExportarPDF.addEventListener('click', () => {
                const doc = new jsPDF();
                const table = document.getElementById('tablaProductos');

                // Obtener los datos de la tabla
                const rows = [];
                table.querySelectorAll('tr').forEach(row => {
                    const rowData = [];
                    row.querySelectorAll('td').forEach((cell, index) => {
                        if (index < 7) { // Excluir la columna de acciones (índice 7)
                            rowData.push(cell.textContent);
                        }
                    });
                    if (rowData.length > 0) {
                        rows.push(rowData);
                    }
                });

                // Generar la tabla en el PDF
                doc.autoTable({
                    head: [['ID', 'Nombre', 'Descripción', 'SKU', 'Stock', 'Precio Compra', 'Precio Venta']],
                    body: rows,
                    styles: {
                        fontSize: 10,
                        cellPadding: 2,
                        textColor: [0, 0, 0],
                        lineColor: [0, 0, 0],
                        lineWidth: 0.1
                    },
                    headStyles: {
                        fillColor: [52, 58, 64], // Color de fondo de la cabecera (#343a40)
                        textColor: [255, 255, 255], // Texto blanco
                        fontStyle: 'bold'
                    },
                    margin: { top: 30 },
                    didDrawPage: function (data) {
                        // Agregar título
                        doc.setFontSize(18);
                        doc.setFont('helvetica', 'bold');
                        doc.text('Gestión de Productos - Lubricentro Cusqueña', 105, 20, { align: 'center' });
                    }
                });

                // Guardar el PDF
                doc.save('ProductosLubricentro.pdf');
            });
        });

        // Cargar los productos al iniciar la página
        load();
    </script>
</body>
</html>
