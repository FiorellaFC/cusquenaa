const API_REPORTE = '../../backend/api/controllers/reporte_citas.php';

document.addEventListener('DOMContentLoaded', () => {
    
    const date = new Date();
    const year = date.getFullYear();
    const month = String(date.getMonth() + 1).padStart(2, '0'); 
    
    const primerDiaMes = `${year}-${month}-01`;
    const ultimoDiaDate = new Date(year, date.getMonth() + 1, 0);
    const ultimoDiaMes = `${year}-${month}-${ultimoDiaDate.getDate()}`;

    const inputInicio = document.getElementById('fechaInicio');
    const inputFin = document.getElementById('fechaFin');

    if(inputInicio && inputFin) {
        inputInicio.value = primerDiaMes;
        inputFin.value = ultimoDiaMes;
        cargarReporte(); 
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
        
        // Si tienes un elemento para ganancia, úsalo
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

        tbody.innerHTML = data.citas.map(c => `
            <tr>
                <td><strong>${c.fecha}</strong></td>
                <td>${c.nombre_completo}</td>
                <td>${c.telefono_cliente || '-'}</td>
                <td class="text-start small">${c.servicio_nombre || 'Sin servicios'}</td>
                <td>${c.hora.substring(0,5)}</td>
                <td class="fw-bold text-success">S/. ${parseFloat(c.precio_total).toFixed(2)}</td>
                <td><span class="badge bg-info text-dark">Completada</span></td>
            </tr>
        `).join('');

    } catch (err) {
        console.error("Error cargando reporte:", err);
        document.getElementById('tablaCitas').innerHTML = 
            `<tr><td colspan="7" class="text-center text-danger py-4">Error al cargar datos.</td></tr>`;
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

    const filas = Array.from(document.querySelectorAll('#tablaCitas tr')).map(tr =>
        Array.from(tr.cells).map(td => td.innerText)
    );

    if (filas.length === 0 || (filas.length === 1 && filas[0][0].includes("No se encontraron"))) {
        alert("No hay datos para exportar.");
        return;
    }

    doc.autoTable({
        head: [['Fecha', 'Cliente', 'Teléfono', 'Servicio', 'Hora', 'Estado']],
        body: filas,
        startY: 40,
        theme: 'grid',
        headStyles: { fillColor: [40, 40, 40] }
    });

    doc.save(`Reporte_Citas_${hoy.replace(/\//g, '-')}.pdf`);
}

// Función Exportar Excel
function exportarExcel() {
    const tabla = document.getElementById('tablaCitas');
    if (!tabla || tabla.rows.length === 0 || tabla.innerText.includes("No se encontraron")) {
        alert("No hay datos para exportar.");
        return;
    }

    const filas = Array.from(document.querySelectorAll('#tablaCitas tr')).map(tr =>
        Array.from(tr.cells).map(td => td.innerText)
    );

    const wb = XLSX.utils.book_new();
    const ws = XLSX.utils.aoa_to_sheet([
        ["REPORTE DE CITAS COMPLETADAS - LA CUSQUEÑA"],
        [`Fecha: ${new Date().toLocaleDateString('es-PE')}`],
        [],
        ["Fecha", "Cliente", "Teléfono", "Servicio", "Hora", "Estado"],
        ...filas
    ]);

    ws['!cols'] = [{wch:12}, {wch:30}, {wch:15}, {wch:25}, {wch:10}, {wch:15}];

    XLSX.utils.book_append_sheet(wb, ws, "Reporte");
    XLSX.writeFile(wb, "Reporte_Citas.xlsx");
}