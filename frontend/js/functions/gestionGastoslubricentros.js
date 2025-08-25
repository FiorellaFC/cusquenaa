document.addEventListener('DOMContentLoaded', () => {
    // Definimos la URL base de tu API para facilitar la gestión de rutas
    // Asegúrate de que esta URL coincida exactamente con la ubicación de tu proyecto en XAMPP
    const API_BASE_URL = 'http://localhost/cusquena/backend/api/controllers/vista_gastos_lubricentros/';

    // Referencias a elementos del DOM
    const tablaGastos = document.getElementById('tablaGastos');
    const formAgregarGasto = document.getElementById('formAgregarGasto');
    const formEditarGasto = document.getElementById('formEditarGasto');
    const modalAgregar = new bootstrap.Modal(document.getElementById('modalAgregar'));
    const modalEditar = new bootstrap.Modal(document.getElementById('modalEditar'));
    const modalEliminarConfirmacion = new bootstrap.Modal(document.getElementById('modalEliminarConfirmacion'));
    const btnConfirmarEliminar = document.getElementById('btnConfirmarEliminar');
    const filterFechaInicio = document.getElementById('filterFechaInicio');
    const filterFechaFin = document.getElementById('filterFechaFin');
    const filterDescripcion = document.getElementById('filterDescripcion');
    const btnBuscarGastos = document.getElementById('btnBuscarGastos');
    const paginationContainer = document.getElementById('pagination');

    // Toasts de Bootstrap para mensajes al usuario
    const toastSuccess = new bootstrap.Toast(document.getElementById('toastSuccess'));
    const toastError = new bootstrap.Toast(document.getElementById('toastError'));
    const toastSuccessBody = document.getElementById('toastSuccessBody');
    const toastErrorBody = document.getElementById('toastErrorBody');

    let gastoIdToDelete = null; // Variable para almacenar el ID del gasto a eliminar

    // --- Funciones de Utilidad ---

    /**
     * Muestra un toast de Bootstrap.
     * @param {string} type - Tipo de toast ('success' o 'error').
     * @param {string} message - Mensaje a mostrar.
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
     * Resetea un formulario.
     * @param {HTMLFormElement} form - El formulario a resetear.
     */
    function resetForm(form) {
        form.reset();
        // Asegurarse de que los select y textareas también se reseteen correctamente si no son afectados por form.reset()
        const selects = form.querySelectorAll('select');
        selects.forEach(select => select.value = '');
        const textareas = form.querySelectorAll('textarea');
        textareas.forEach(textarea => textarea.value = '');
    }

    /**
     * Calculates and displays the total general of expenses.
     * @param {Array<object>} gastos - Array of expense objects.
     */
    function updateTotalGeneral(gastos) {
        const total = gastos.reduce((sum, gasto) => sum + parseFloat(gasto.monto || 0), 0);
        document.getElementById('totalGeneral').textContent = `Total General: S/ ${total.toFixed(2)}`;
    }

    // --- Funciones CRUD (Interfaz con el Backend) ---

    /**
     * Fetches expenses data from the backend.
     * @param {object} filters - Object containing filter parameters (descripcion, fecha_inicio, fecha_fin, page, limit).
     * @returns {Promise<Array>} - A promise that resolves to an array of expense objects.
     */
    async function fetchGastos(filters = {}) {
        const queryParams = new URLSearchParams(filters).toString();
        try {
            // RUTA ABSOLUTA COMPLETA para listar.php
            const response = await fetch(`${API_BASE_URL}listar.php?${queryParams}`);
            if (!response.ok) {
                const errorText = await response.text();
                throw new Error(`HTTP error! status: ${response.status}, message: ${errorText}`);
            }
            const data = await response.json();
            if (data.error) {
                throw new Error(data.error);
            }
            return data;
        } catch (error) {
            console.error('Error al cargar gastos:', error);
            showToast('error', `Error al cargar gastos: ${error.message}`);
            return { gastos: [], total: 0 };
        }
    }

    /**
     * Adds a new expense to the backend.
     * @param {object} gastoData - Data of the expense to add.
     * @returns {Promise<object>} - A promise that resolves to the new expense object or an error.
     */
    async function addGasto(gastoData) {
        try {
            // RUTA ABSOLUTA COMPLETA para registrar.php
            const response = await fetch(`${API_BASE_URL}registrar.php`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(gastoData)
            });
            if (!response.ok) {
                const errorText = await response.text();
                throw new Error(`HTTP error! status: ${response.status}, message: ${errorText}`);
            }
            const data = await response.json();
            if (data.error) {
                throw new Error(data.error);
            }
            return data;
        } catch (error) {
            console.error('Error al agregar gasto:', error);
            showToast('error', `Error al agregar gasto: ${error.message}`);
            return { success: false, message: error.message };
        }
    }

    /**
     * Updates an existing expense in the backend.
     * @param {object} gastoData - Data of the expense to update (must include ID).
     * @returns {Promise<object>} - A promise that resolves to a success message or an error.
     */
    async function updateGasto(gastoData) {
        try {
            // RUTA ABSOLUTA COMPLETA para actualizar.php
            const response = await fetch(`${API_BASE_URL}actualizar.php`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(gastoData)
            });
            if (!response.ok) {
                const errorText = await response.text();
                throw new Error(`HTTP error! status: ${response.status}, message: ${errorText}`);
            }
            const data = await response.json();
            if (data.error) {
                throw new Error(data.error);
            }
            return data;
        } catch (error) {
            console.error('Error al actualizar gasto:', error);
            showToast('error', `Error al actualizar gasto: ${error.message}`);
            return { success: false, message: error.message };
        }
    }

    /**
     * Deletes an expense from the backend.
     * @param {number} id - ID of the expense to delete.
     * @returns {Promise<object>} - A promise that resolves to a success message or an error.
     */
    async function deleteGasto(id) {
        try {
            // RUTA ABSOLUTA COMPLETA para eliminar.php
            const response = await fetch(`${API_BASE_URL}eliminar.php`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ id: id })
            });
            if (!response.ok) {
                const errorText = await response.text();
                throw new Error(`HTTP error! status: ${response.status}, message: ${errorText}`);
            }
            const data = await response.json();
            if (data.error) {
                throw new Error(data.error);
            }
            return data;
        } catch (error) {
            console.error('Error al eliminar gasto:', error);
            showToast('error', `Error al eliminar gasto: ${error.message}`);
            return { success: false, message: error.message };
        }
    }

    // --- Renderizado de la Tabla ---

    /**
     * Renders the expenses data into the table.
     * @param {Array<object>} gastos - Array of expense objects to display.
     */
    function renderGastos(gastos) {
        tablaGastos.innerHTML = ''; // Limpiar tabla antes de renderizar
        if (gastos.length === 0) {
            tablaGastos.innerHTML = '<tr><td colspan="7" class="text-center">No hay gastos para mostrar.</td></tr>';
            updateTotalGeneral([]); // Reset total when no expenses
            return;
        }

        gastos.forEach(gasto => {
            const row = tablaGastos.insertRow();
            row.insertCell().textContent = gasto.id;
            row.insertCell().textContent = gasto.descripcion;
            row.insertCell().textContent = gasto.tipo_gasto.charAt(0).toUpperCase() + gasto.tipo_gasto.slice(1); // Capitalizar tipo
            row.insertCell().textContent = `S/. ${parseFloat(gasto.monto).toFixed(2)}`;
            row.insertCell().textContent = gasto.fecha;
            row.insertCell().textContent = gasto.detalle;

            const actionsCell = row.insertCell();
            const editButton = document.createElement('button');
            editButton.className = 'btn btn-sm btn-warning me-2';
            editButton.textContent = 'Editar';
            editButton.dataset.id = gasto.id;
            editButton.addEventListener('click', () => populateEditModal(gasto));
            actionsCell.appendChild(editButton);

            const deleteButton = document.createElement('button');
            deleteButton.className = 'btn btn-sm btn-danger';
            deleteButton.textContent = 'Eliminar';
            deleteButton.dataset.id = gasto.id;
            deleteButton.addEventListener('click', () => {
                gastoIdToDelete = gasto.id;
                modalEliminarConfirmacion.show();
            });
            actionsCell.appendChild(deleteButton);
        });

        // Update total general after rendering expenses
        updateTotalGeneral(gastos);
    }

    /**
     * Fills the edit modal with the selected expense's data.
     * @param {object} gasto - The expense object to edit.
     */
    function populateEditModal(gasto) {
        document.getElementById('editGastoId').value = gasto.id;
        document.getElementById('editDescripcion').value = gasto.descripcion;
        document.getElementById('editTipoGasto').value = gasto.tipo_gasto;
        document.getElementById('editMonto').value = parseFloat(gasto.monto).toFixed(2);
        document.getElementById('editFecha').value = gasto.fecha;
        document.getElementById('editDetalle').value = gasto.detalle;
        modalEditar.show();
    }

    // --- Paginación ---
    let currentPage = 1;
    const recordsPerPage = 10;

    /**
     * Sets up pagination links.
     * @param {number} totalRecords - Total number of records.
     * @param {number} currentPage - Current page number.
     * @param {number} recordsPerPage - Number of records per page.
     */
    function setupPagination(totalRecords, currentPage, recordsPerPage) {
        paginationContainer.innerHTML = '';
        const totalPages = Math.ceil(totalRecords / recordsPerPage);

        if (totalPages <= 1) {
            return;
        }

        // Previous button
        const prevLi = document.createElement('li');
        prevLi.className = `page-item ${currentPage === 1 ? 'disabled' : ''}`;
        prevLi.innerHTML = `<a class="page-link" href="#" aria-label="Previous"><span aria-hidden="true">«</span></a>`;
        prevLi.addEventListener('click', (e) => {
            e.preventDefault();
            if (currentPage > 1) {
                currentPage--;
                loadGastos();
            }
        });
        paginationContainer.appendChild(prevLi);

        // Page numbers
        for (let i = 1; i <= totalPages; i++) {
            const pageLi = document.createElement('li');
            pageLi.className = `page-item ${currentPage === i ? 'active' : ''}`;
            pageLi.innerHTML = `<a class="page-link" href="#">${i}</a>`;
            pageLi.addEventListener('click', (e) => {
                e.preventDefault();
                currentPage = i;
                loadGastos();
            });
            paginationContainer.appendChild(pageLi);
        }

        // Next button
        const nextLi = document.createElement('li');
        nextLi.className = `page-item ${currentPage === totalPages ? 'disabled' : ''}`;
        nextLi.innerHTML = `<a class="page-link" href="#" aria-label="Next"><span aria-hidden="true">»</span></a>`;
        nextLi.addEventListener('click', (e) => {
            e.preventDefault();
            if (currentPage < totalPages) {
                currentPage++;
                loadGastos();
            }
        });
        paginationContainer.appendChild(nextLi);
    }

    /**
     * Carga los gastos aplicando filtros y paginación.
     */
    async function loadGastos() {
        const filters = {
            descripcion: filterDescripcion.value,
            fecha_inicio: filterFechaInicio.value,
            fecha_fin: filterFechaFin.value,
            page: currentPage,
            limit: recordsPerPage
        };

        const result = await fetchGastos(filters);
        if (result && result.gastos) {
            renderGastos(result.gastos);
            setupPagination(result.total, currentPage, recordsPerPage);
        } else {
            updateTotalGeneral([]); // Reset total on error or no results
        }
    }

    // --- Event Listeners ---

    // Listener para el formulario de agregar gasto
    formAgregarGasto.addEventListener('submit', async (e) => {
        e.preventDefault();
        const formData = new FormData(formAgregarGasto);
        const gastoData = Object.fromEntries(formData.entries());

        gastoData.tipoGasto = document.getElementById('tipoGasto').value;

        const result = await addGasto(gastoData);
        if (result && result.success) {
            showToast('success', 'Gasto agregado exitosamente!');
            resetForm(formAgregarGasto);
            modalAgregar.hide();
            loadGastos();
        }
    });

    // Listener para el formulario de editar gasto
    formEditarGasto.addEventListener('submit', async (e) => {
        e.preventDefault();
        const formData = new FormData(formEditarGasto);
        const gastoData = Object.fromEntries(formData.entries());
        gastoData.id = document.getElementById('editGastoId').value;

        gastoData.tipoGasto = document.getElementById('editTipoGasto').value;

        const result = await updateGasto(gastoData);
        if (result && result.success) {
            showToast('success', 'Gasto actualizado exitosamente!');
            modalEditar.hide();
            loadGastos();
        }
    });

    // Listener para el botón de confirmar eliminación
    btnConfirmarEliminar.addEventListener('click', async () => {
        if (gastoIdToDelete) {
            const result = await deleteGasto(gastoIdToDelete);
            if (result && result.success) {
                showToast('success', 'Gasto eliminado exitosamente!');
                modalEliminarConfirmacion.hide();
                gastoIdToDelete = null;
                loadGastos();
            }
        }
    });

    // Listener para el botón de búsqueda
    btnBuscarGastos.addEventListener('click', () => {
        currentPage = 1;
        loadGastos();
    });

    // Carga inicial de gastos al cargar la página
    loadGastos();
});