document.addEventListener('DOMContentLoaded', () => {
    const API_BASE_URL = 'http://localhost/cusquena/backend/api/controllers/vista_gestion_cotizaciones/';

    const tablaCotizaciones = document.getElementById('tablaCotizaciones');
    const formAgregar = document.getElementById('formAgregarCotizacion');
    const formEditar = document.getElementById('formEditarCotizacion');
    const modalAgregar = new bootstrap.Modal(document.getElementById('modalAgregar'));
    const modalEditar = new bootstrap.Modal(document.getElementById('modalEditar'));
    const modalEliminarConfirmacion = new bootstrap.Modal(document.getElementById('modalEliminarConfirmacion'));
    const btnConfirmarEliminar = document.getElementById('btnConfirmarEliminar');
    const totalGeneralPago = document.getElementById('totalGeneral');

    const toastSuccess = new bootstrap.Toast(document.getElementById('toastSuccess'));
    const toastError = new bootstrap.Toast(document.getElementById('toastError'));
    const toastSuccessBody = document.getElementById('toastSuccessBody');
    const toastErrorBody = document.getElementById('toastErrorBody');

    // Filtros
    const filterNombre = document.getElementById('filterNombre');
    const filterTipoCotizacion = document.getElementById('filterTipoCotizacion');
    const filterEstado = document.getElementById('filterEstado');
    const filterFechaInicio = document.getElementById('filterFechaInicio');
    const filterFechaFin = document.getElementById('filterFechaFin');
    const btnBuscarCotizaciones = document.getElementById('btnBuscarCotizaciones');

    let cotizacionIdToDelete = null;

    /**
     * Muestra una notificación tipo "toast".
     * @param {string} type - 'success' o 'error'.
     * @param {string} message - El mensaje a mostrar.
     */
    function showToast(type, message) {
        if (type === 'success') {
            toastSuccessBody.textContent = message;
            toastSuccess.show();
        } else if (type === 'error') {
            toastErrorBody.textContent = message;
            toastError.show();
        }
    }

    /**
     * Devuelve el HTML para una etiqueta de estado con colores.
     * @param {string} estado
     * @returns {string}
     */
    function getBadgeEstado(estado) {
        const estadoNormalizado = estado.toLowerCase().trim();
        switch (estadoNormalizado) {
            case 'aprobada':
            case 'pagada':
                return `<span class="badge bg-success">Pagada</span>`;
            case 'pendiente':
                return `<span class="badge bg-warning text-dark">Pendiente</span>`;
            case 'rechazada':
                return `<span class="badge bg-danger">Rechazada</span>`;
            default:
                return `<span class="badge bg-secondary">${estado}</span>`;
        }
    }

    /**
     * Carga y renderiza las cotizaciones desde la API, aplicando filtros si se especifican.
     * @param {object} params - Objeto con los parámetros de filtro.
     */
    async function fetchAndRenderCotizaciones(params = {}) {
        const query = new URLSearchParams(params).toString();
        const url = `${API_BASE_URL}listar.php?${query}`;

        try {
            const response = await fetch(url);
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            const data = await response.json();
            const cotizaciones = data.cotizaciones || [];

            renderizarTabla(cotizaciones);
            actualizarTotal(cotizaciones, params.estado);
        } catch (error) {
            console.error('Error al cargar cotizaciones:', error);
            showToast('error', 'Error al cargar las cotizaciones. Inténtalo de nuevo.');
        }
    }

    /**
     * Renderiza la tabla con los datos de las cotizaciones.
     * @param {Array} cotizaciones - Array de objetos de cotización.
     */
    function renderizarTabla(cotizaciones) {
        tablaCotizaciones.innerHTML = '';

        if (cotizaciones.length === 0) {
            tablaCotizaciones.innerHTML = '<tr><td colspan="8" class="text-center">No hay cotizaciones registradas.</td></tr>';
            return;
        }

        cotizaciones.forEach(c => {
            const row = tablaCotizaciones.insertRow();
            row.insertCell().textContent = c.nombre;
            row.insertCell().textContent = c.apellido;
            row.insertCell().textContent = c.tipo_cotizacion;
            row.insertCell().textContent = `S/. ${parseFloat(c.pago).toFixed(2)}`;

            row.insertCell().textContent = new Date(c.fecha_inicio).toLocaleDateString('es-ES');
            row.insertCell().textContent = new Date(c.fecha_fin).toLocaleDateString('es-ES');

            const estadoCell = row.insertCell();
            estadoCell.innerHTML = getBadgeEstado(c.estado);

            const acciones = row.insertCell();

            const btnEditar = document.createElement('button');
            btnEditar.className = 'btn btn-warning btn-sm me-1';
            btnEditar.innerHTML = '<i class="fas fa-edit"></i>';
            btnEditar.title = 'Editar Cotización';
            btnEditar.onclick = () => llenarModalEditar(c);
            acciones.appendChild(btnEditar);

            const btnEliminar = document.createElement('button');
            btnEliminar.className = 'btn btn-danger btn-sm';
            btnEliminar.innerHTML = '<i class="fas fa-trash-alt"></i>';
            btnEliminar.title = 'Eliminar Cotización';
            btnEliminar.onclick = () => {
                cotizacionIdToDelete = c.id;
                modalEliminarConfirmacion.show();
            };
            acciones.appendChild(btnEliminar);
        });
    }

    /**
     * Calcula y actualiza el total general de pagos de las cotizaciones.
     * @param {Array} cotizaciones - Array de objetos de cotización.
     * @param {string} estadoFiltrado - Estado por el cual se filtra (opcional).
     */
    function actualizarTotal(cotizaciones, estadoFiltrado) {
        let total = 0;

        cotizaciones.forEach(c => {
            if (!estadoFiltrado || c.estado.toLowerCase().trim() === estadoFiltrado) {
                total += parseFloat(c.pago);
            }
        });
        totalGeneralPago.textContent = `Total General: S/. ${total.toFixed(2)}`;
    }

    formAgregar.addEventListener('submit', async e => {
        e.preventDefault();
        const formData = new FormData(formAgregar);
        const datos = Object.fromEntries(formData.entries());

        try {
            const response = await fetch(`${API_BASE_URL}registrar.php`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(datos)
            });

            const data = await response.json();
            if (data.success) {
                modalAgregar.hide();
                formAgregar.reset();
                fetchAndRenderCotizaciones();
                showToast('success', '✅ Cotización registrada exitosamente.');
            } else {
                showToast('error', data.error || 'Error al registrar la cotización.');
            }
        } catch (error) {
            console.error('Error al registrar:', error);
            showToast('error', 'Error de conexión al intentar registrar la cotización.');
        }
    });

    function llenarModalEditar(c) {
        document.getElementById('editCotizacionId').value = c.id;
        document.getElementById('editNombre').value = c.nombre;
        document.getElementById('editApellido').value = c.apellido;
        document.getElementById('editTipoCotizacion').value = c.tipo_cotizacion;
        document.getElementById('editPago').value = parseFloat(c.pago).toFixed(2);
        document.getElementById('editFechaInicio').value = c.fecha_inicio;
        document.getElementById('editFechaFin').value = c.fecha_fin;
        document.getElementById('editEstado').value = c.estado;

        modalEditar.show();
    }

    formEditar.addEventListener('submit', async e => {
        e.preventDefault();
        const formData = new FormData(formEditar);
        const datos = Object.fromEntries(formData.entries());

        try {
            const response = await fetch(`${API_BASE_URL}actualizar.php`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(datos)
            });

            const data = await response.json();
            if (data.success) {
                modalEditar.hide();
                fetchAndRenderCotizaciones();
                showToast('success', '✅ Cotización actualizada exitosamente.');
            } else {
                showToast('error', data.error || 'Error al actualizar la cotización.');
            }
        } catch (error) {
            console.error('Error al actualizar:', error);
            showToast('error', 'Error de conexión al intentar actualizar la cotización.');
        }
    });

    btnConfirmarEliminar.addEventListener('click', async () => {
        if (cotizacionIdToDelete) {
            try {
                const response = await fetch(`${API_BASE_URL}eliminar.php`, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ id: cotizacionIdToDelete })
                });

                const data = await response.json();
                if (data.success) {
                    modalEliminarConfirmacion.hide();
                    fetchAndRenderCotizaciones();
                    showToast('success', '✅ Cotización eliminada exitosamente.');
                } else {
                    showToast('error', data.error || 'Error al eliminar la cotización.');
                }
            } catch (error) {
                console.error('Error al eliminar:', error);
                showToast('error', 'Error de conexión al intentar eliminar la cotización.');
            } finally {
                cotizacionIdToDelete = null;
            }
        }
    });

    // Carga inicial de las cotizaciones
    fetchAndRenderCotizaciones();

    // Event listener para el botón de búsqueda
    btnBuscarCotizaciones.addEventListener('click', () => {
        const params = {
            nombre_apellido: filterNombre.value.trim(),
            tipo_cotizacion: filterTipoCotizacion.value,
            estado: filterEstado.value,
            fecha_inicio: filterFechaInicio.value,
            fecha_fin: filterFechaFin.value
        };

        // Elimina los parámetros vacíos antes de enviar la solicitud
        for (const key in params) {
            if (!params[key]) {
                delete params[key];
            }
        }
        fetchAndRenderCotizaciones(params);
    });

    // Event listeners para los selectores de filtro que recargan la tabla
    filterTipoCotizacion.addEventListener('change', () => {
        btnBuscarCotizaciones.click();
    });

    filterEstado.addEventListener('change', () => {
        btnBuscarCotizaciones.click();
    });


    document.getElementById('btnPrintTable').addEventListener('click', () => {
        const tableToPrint = document.getElementById('cotizacionesTable').outerHTML;
        const printWindow = window.open('', '', 'height=600,width=800');
        printWindow.document.write('<html><head><title>Cotizaciones</title>');
        printWindow.document.write('<link href="../css/bootstrap.css" rel="stylesheet">');
        printWindow.document.write('<style>');
        printWindow.document.write(`
            body { font-family: sans-serif; }
            table { width: 100%; border-collapse: collapse; margin-top: 20px; }
            th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
            th { background-color: #f2f2f2; }
            .no-print { display: none; }
        `);
        printWindow.document.write('</style></head><body>');
        printWindow.document.write('<h1 style="text-align: center;">Reporte de Cotizaciones</h1>');
        printWindow.document.write(tableToPrint);
        printWindow.document.write(`<p style="text-align: right; margin-top: 20px;">${totalGeneralPago.textContent}</p>`);
        printWindow.document.write('</body></html>');
        printWindow.document.close();
        printWindow.print();
    });

    document.getElementById('btnExportPdf').addEventListener('click', () => {
        const { jsPDF } = window.jspdf;
        const doc = new jsPDF();

        doc.text("Reporte de Cotizaciones", 14, 16);

        const table = document.getElementById('cotizacionesTable');
        const rows = Array.from(table.querySelectorAll('tr'));
        const headers = Array.from(table.querySelectorAll('thead th')).map(th => th.innerText);

        const data = rows.slice(1).map(row => {
            const cells = Array.from(row.querySelectorAll('td'));
            return cells.slice(0, -1).map(cell => cell.innerText);
        });

        const columns = headers.slice(0, -1);

        doc.autoTable({
            head: [columns],
            body: data,
            startY: 25,
            theme: 'striped',
            styles: { fontSize: 8, cellPadding: 2, overflow: 'linebreak' },
            headStyles: { fillColor: [33, 37, 41] },
            columnStyles: {}
        });

        doc.text(totalGeneralPago.textContent, 14, doc.autoTable.previous.finalY + 10);
        doc.save("cotizaciones.pdf");
    });
});