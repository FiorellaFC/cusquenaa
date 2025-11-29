// frontend/js/functions/reporte_citas.js

const API_REPORTE = '../../backend/api/controllers/reporte_citas.php';

document.addEventListener('DOMContentLoaded', () => {
    
    // --- CÁLCULO DE FECHAS (MES ACTUAL) ---
    const date = new Date();
    const year = date.getFullYear();
    const month = String(date.getMonth() + 1).padStart(2, '0'); 
    
    const primerDiaMes = `${year}-${month}-01`;
    const ultimoDiaDate = new Date(year, date.getMonth() + 1, 0);
    const ultimoDiaMes = `${year}-${month}-${ultimoDiaDate.getDate()}`;

    const inputInicio = document.getElementById('fechaInicio');
    const inputFin = document.getElementById('fechaFin');

    // Inicializar Modal de Servicios
    const modalDetalleEl = document.getElementById('modalDetalleServicios');
    // Verificamos si existe el modal antes de inicializarlo para evitar errores en otras vistas
    const modalDetalle = modalDetalleEl ? new bootstrap.Modal(modalDetalleEl) : null;

    if(inputInicio && inputFin) {
        inputInicio.value = primerDiaMes;
        inputFin.value = ultimoDiaMes;
        cargarReporte(); 
    }

    // --- LISTENER PARA VER SERVICIOS (MODAL) ---
    const tbody = document.getElementById('tablaCitas');
    if(tbody) {
        tbody.addEventListener('click', (e) => {
            const btn = e.target.closest('.btn-ver-servicios');
            if (btn && modalDetalle) {
                const texto = btn.dataset.servicios;
                // Convertir texto "A, B" en lista
                const lista = texto.split(', ').map(s => 
                    `<li class="list-group-item px-3 py-2 border-bottom small"><i class="fas fa-check text-success me-2"></i>${s}</li>`
                ).join('');
                
                document.getElementById('listaServiciosDetalle').innerHTML = `<ul class="list-group list-group-flush p-0 m-0">${lista}</ul>`;
                modalDetalle.show();
            }
        });
    }
});

async function cargarReporte() {
    const inicio = document.getElementById('fechaInicio').value;
    const fin = document.getElementById('fechaFin').value;

    try {
        const res = await fetch(`${API_REPORTE}?inicio=${inicio}&fin=${fin}`);
        if (!res.ok) throw new Error(`Error ${res.status}`);
        const data = await res.json();

        // Actualizar Tarjetas
        document.getElementById('totalCitas').textContent = data.total || 0;
        
        const elGanancia = document.getElementById('totalGanancia');
        if(elGanancia) elGanancia.textContent = "S/. " + (data.ganancia_total || "0.00");

        document.getElementById('servicioTop').textContent = data.servicio_top || 'N/A';
        document.getElementById('clienteTop').textContent = data.cliente_top || 'N/A';

        // Actualizar Tabla
        const tbody = document.getElementById('tablaCitas');
        if (!data.citas || data.citas.length === 0) {
            tbody.innerHTML = `<tr><td colspan="7" class="text-center py-5 text-muted">No hay citas completadas en este rango.</td></tr>`;
            return;
        }

        tbody.innerHTML = data.citas.map(c => {
            // Lógica Servicios Múltiples
            let serviciosDisplay = c.servicio_nombre || 'Sin servicios';
            if (serviciosDisplay.includes(',')) {
                const count = serviciosDisplay.split(',').length;
                serviciosDisplay = `
                    <button class="btn btn-sm btn-outline-primary fw-bold btn-ver-servicios" 
                            data-servicios="${serviciosDisplay}">
                        <i class="fas fa-list-ul me-1"></i> Ver (${count})
                    </button>
                `;
            } else {
                serviciosDisplay = `<span class="small text-dark fw-bold">${serviciosDisplay}</span>`;
            }

            return `
                <tr>
                    <td><strong>${c.fecha}</strong></td>
                    <td>${c.nombre_completo}</td>
                    <td>${c.telefono_cliente || '-'}</td>
                    <td class="text-center">${serviciosDisplay}</td>
                    <td>${c.hora.substring(0,5)}</td>
                    <td class="fw-bold text-success">S/. ${parseFloat(c.precio_total).toFixed(2)}</td>
                    <td><span class="badge bg-info text-dark">Completada</span></td>
                </tr>
            `;
        }).join('');

    } catch (err) {
        console.error("Error cargando reporte:", err);
        document.getElementById('tablaCitas').innerHTML = 
            `<tr><td colspan="7" class="text-center text-danger py-4">Error al cargar datos. Verifica la consola.</td></tr>`;
    }
}

// Función Exportar PDF
function exportarPDF() {
    const { jsPDF } = window.jspdf;
    const doc = new jsPDF('l', 'mm', 'a4');
    const hoy = new Date().toLocaleDateString('es-PE');

    doc.setFontSize(18);
    doc.text('REPORTE DE CITAS COMPLETADAS', 14, 22);
    doc.setFontSize(11);
    doc.text(`Generado el: ${hoy}`, 14, 30);

    // Obtenemos los datos limpios de la tabla (sin botones HTML)
    // Nota: Para el PDF, tomamos el atributo data-servicios si existe, o el texto normal
    const filas = Array.from(document.querySelectorAll('#tablaCitas tr')).map(tr => {
        const celdas = Array.from(tr.cells);
        return celdas.map((td, index) => {
            // Si es la columna de servicios (index 3) y tiene botón, sacamos el dato real
            if (index === 3 && td.querySelector('.btn-ver-servicios')) {
                return td.querySelector('.btn-ver-servicios').dataset.servicios;
            }
            return td.innerText;
        });
    });

    if (filas.length === 0 || (filas.length === 1 && filas[0][0].includes("No hay citas"))) {
        alert("No hay datos para exportar.");
        return;
    }

    doc.autoTable({
        head: [['Fecha', 'Cliente', 'Teléfono', 'Servicios', 'Hora', 'Monto', 'Estado']],
        body: filas,
        startY: 40,
        theme: 'grid',
        headStyles: { fillColor: [40, 40, 40] },
        styles: { fontSize: 8 } // Letra más pequeña para que quepan los servicios largos
    });

    doc.save(`Reporte_Citas_${hoy.replace(/\//g, '-')}.pdf`);
}

// Función Exportar Excel
function exportarExcel() {
    const tabla = document.getElementById('tablaCitas');
    if (!tabla || tabla.rows.length === 0 || tabla.innerText.includes("No hay citas")) {
        alert("No hay datos para exportar.");
        return;
    }

    // Misma lógica de limpieza que en PDF
    const filas = Array.from(document.querySelectorAll('#tablaCitas tr')).map(tr => {
        const celdas = Array.from(tr.cells);
        return celdas.map((td, index) => {
            if (index === 3 && td.querySelector('.btn-ver-servicios')) {
                return td.querySelector('.btn-ver-servicios').dataset.servicios;
            }
            return td.innerText;
        });
    });

    const wb = XLSX.utils.book_new();
    const ws = XLSX.utils.aoa_to_sheet([
        ["REPORTE DE CITAS COMPLETADAS - LA CUSQUEÑA"],
        [`Fecha: ${new Date().toLocaleDateString('es-PE')}`],
        [],
        ["Fecha", "Cliente", "Teléfono", "Servicios", "Hora", "Monto", "Estado"],
        ...filas
    ]);

    ws['!cols'] = [{wch:12}, {wch:30}, {wch:15}, {wch:40}, {wch:10}, {wch:15}, {wch:15}];

    XLSX.utils.book_append_sheet(wb, ws, "Reporte");
    XLSX.writeFile(wb, "Reporte_Citas.xlsx");
}