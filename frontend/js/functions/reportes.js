document.getElementById('btnGenerarReporte').addEventListener('click', function () {
    const tipo = document.getElementById('tipoReporte').value;
    const fechaInicio = document.getElementById('fechaInicio').value;
    const fechaFin = document.getElementById('fechaFin').value;

    if (!fechaInicio || !fechaFin) {
        alert("Por favor selecciona un rango de fechas");
        return;
    }

    fetch('reportes.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: `tipo=${tipo}&fechaInicio=${fechaInicio}&fechaFin=${fechaFin}`
    })
    .then(res => res.json())
    .then(data => {
        let html = `<h4>Reporte ${tipo.toUpperCase()}</h4><pre>${JSON.stringify(data, null, 2)}</pre>`;
        document.getElementById('resultadoReporte').innerHTML = html;

        // Si quieres exportar directo a PDF con jsPDF
        const { jsPDF } = window.jspdf;
        const doc = new jsPDF();
        doc.text(`Reporte ${tipo.toUpperCase()} (${fechaInicio} - ${fechaFin})`, 10, 10);
        doc.text(JSON.stringify(data, null, 2), 10, 20);
        doc.save(`reporte_${tipo}_${fechaInicio}_${fechaFin}.pdf`);
    })
    .catch(err => console.error(err));
});
