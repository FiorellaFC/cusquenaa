document.addEventListener('DOMContentLoaded', () => {
    // Definimos la URL base de tu API para facilitar la gestión de rutas
    const API_BASE_URL = 'http://localhost/cusquena/backend/api/controllers/vista_gastos_empresa/';

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
    const btnResetSearch = document.getElementById('btnResetSearch');
    const paginationContainer = document.getElementById('pagination');
    const totalGeneralSpan = document.getElementById('totalGeneral');

    // Toasts de Bootstrap para mensajes al usuario
    const toastSuccess = new bootstrap.Toast(document.getElementById('toastSuccess'));
    const toastError = new bootstrap.Toast(document.getElementById('toastError'));
    const toastSuccessBody = document.getElementById('toastSuccessBody');
    const toastErrorBody = document.getElementById('toastErrorBody');

    let gastoIdToDelete = null;

    function showToast(type, message) {
        if (type === 'success') {
            toastSuccessBody.textContent = message;
            toastSuccess.show();
        } else if (type === 'error') {
            toastErrorBody.textContent = message;
            toastError.show();
        }
    }

    function resetForm(form) {
        form.reset();
        const selects = form.querySelectorAll('select');
        selects.forEach(select => select.value = '');
    }

    // --- Funciones CRUD (Interfaz con el Backend) ---

    async function fetchGastos(filters = {}) {
        // Filtramos los parámetros que no tienen valor para no enviarlos
        const cleanFilters = Object.fromEntries(
            Object.entries(filters).filter(([_, v]) => v != null && v !== '')
        );
        const queryParams = new URLSearchParams(cleanFilters).toString();
        
        try {
            const response = await fetch(`${API_BASE_URL}listar.php?${queryParams}`);
            if (!response.ok) {
                // Try to parse error as JSON first, then fall back to text
                const errorBody = await response.text();
                let errorMessage = `Error HTTP: ${response.status}`;
                try {
                    const errorJson = JSON.parse(errorBody);
                    if (errorJson.error) {
                        errorMessage += `, ${errorJson.error}`;
                    } else {
                        errorMessage += `, ${errorBody}`;
                    }
                } catch (jsonError) {
                    errorMessage += `, ${errorBody}`;
                }
                throw new Error(errorMessage);
            }
            const data = await response.json();
            if (data.error) {
                throw new Error(data.error);
            }
            return data;
        } catch (error) {
            console.error('Error al cargar gastos:', error);
            showToast('error', `Error al cargar gastos: ${error.message}`);
            return { gastos: [], total: 0, total_general_monto: 0 };
        }
    }

    async function addGasto(gastoData) {
        try {
            const response = await fetch(`${API_BASE_URL}registrar.php`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(gastoData)
            });
            // Improved error handling for non-2xx responses
            if (!response.ok) {
                const errorBody = await response.text(); // Get the raw error response
                let errorMessage = `Error HTTP: ${response.status}`;
                try {
                    const errorJson = JSON.parse(errorBody);
                    if (errorJson.error) {
                        errorMessage = errorJson.error; // Use the backend's error message if available
                    } else {
                        errorMessage += `: ${errorBody}`;
                    }
                } catch (jsonError) {
                    errorMessage += `: ${errorBody}`; // If not JSON, use raw text
                }
                throw new Error(errorMessage);
            }
            return await response.json();
        } catch (error) {
            showToast('error', `Error al agregar gasto: ${error.message}`);
            return { success: false, message: error.message };
        }
    }

    async function updateGasto(gastoData) {
        try {
            const response = await fetch(`${API_BASE_URL}actualizar.php`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(gastoData)
            });
            // Improved error handling for non-2xx responses
            if (!response.ok) {
                const errorBody = await response.text(); // Get the raw error response
                let errorMessage = `Error HTTP: ${response.status}`;
                try {
                    const errorJson = JSON.parse(errorBody);
                    if (errorJson.error) {
                        errorMessage = errorJson.error; // Use the backend's error message if available
                    } else {
                        errorMessage += `: ${errorBody}`;
                    }
                } catch (jsonError) {
                    errorMessage += `: ${errorBody}`; // If not JSON, use raw text
                }
                throw new Error(errorMessage);
            }
            return await response.json();
        } catch (error) {
            showToast('error', `Error al actualizar gasto: ${error.message}`);
            return { success: false, message: error.message };
        }
    }
    
    async function deleteGasto(id) {
        try {
            const response = await fetch(`${API_BASE_URL}eliminar.php`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ id: id })
            });
            if (!response.ok) {
                const errorBody = await response.text();
                let errorMessage = `Error HTTP: ${response.status}`;
                try {
                    const errorJson = JSON.parse(errorBody);
                    if (errorJson.error) {
                        errorMessage = errorJson.error;
                    } else {
                        errorMessage += `: ${errorBody}`;
                    }
                } catch (jsonError) {
                    errorMessage += `: ${errorBody}`;
                }
                throw new Error(errorMessage);
            }
            return await response.json();
        } catch (error) {
            showToast('error', `Error al eliminar gasto: ${error.message}`);
            return { success: false, message: error.message };
        }
    }
    
    // --- Renderizado y Paginación ---

    function renderGastos(gastos, totalGeneralMonto) {
    tablaGastos.innerHTML = '';
    if (gastos.length === 0) {
        tablaGastos.innerHTML = '<tr><td colspan="7" class="text-center">No hay gastos para mostrar.</td></tr>';
        totalGeneralSpan.textContent = `Total General: S/. 0.00`;
        return;
    }

    gastos.forEach(gasto => {
        const row = tablaGastos.insertRow();
        row.insertCell().textContent = gasto.id;
        row.insertCell().textContent = gasto.descripcion;
        // Capitaliza la primera letra del tipo de gasto
        row.insertCell().textContent = gasto.tipo_gasto.charAt(0).toUpperCase() + gasto.tipo_gasto.slice(1);
        row.insertCell().textContent = `S/. ${parseFloat(gasto.monto).toFixed(2)}`;
        row.insertCell().textContent = gasto.fecha;
        row.insertCell().textContent = gasto.detalle;

        const actionsCell = row.insertCell();

        // Botón Editar con icono
        const editButton = document.createElement('button');
        editButton.className = 'btn btn-sm btn-warning me-2';
        editButton.innerHTML = '<i class="fas fa-edit"></i>'; 
        editButton.title = 'Editar Gasto'; 
        editButton.addEventListener('click', () => populateEditModal(gasto));
        actionsCell.appendChild(editButton);

        // Botón Eliminar con icono
        const deleteButton = document.createElement('button');
        deleteButton.className = 'btn btn-sm btn-danger';
        deleteButton.innerHTML = '<i class="fas fa-trash-alt"></i>'; 
        deleteButton.title = 'Eliminar Gasto'; 
        deleteButton.addEventListener('click', () => {
            gastoIdToDelete = gasto.id;
            modalEliminarConfirmacion.show();
        });
        actionsCell.appendChild(deleteButton);
    });

    totalGeneralSpan.textContent = `Total General: S/. ${parseFloat(totalGeneralMonto || 0).toFixed(2)}`;
}

    function populateEditModal(gasto) {
        document.getElementById('editGastoId').value = gasto.id;
        document.getElementById('editDescripcion').value = gasto.descripcion;
        // Ensure the ID of your select element for tipo_gasto in the edit modal is 'editTipoGasto'
        document.getElementById('editTipoGasto').value = gasto.tipo_gasto; 
        document.getElementById('editMonto').value = parseFloat(gasto.monto).toFixed(2);
        document.getElementById('editFecha').value = gasto.fecha;
        document.getElementById('editDetalle').value = gasto.detalle;
        modalEditar.show();
    }
    
    let currentPage = 1;
    const recordsPerPage = 10; // This should ideally match the limit in your PHP listar.php

    function setupPagination(totalRecords, currentPageNum, recordsPerPageNum) {
        paginationContainer.innerHTML = '';
        const totalPages = Math.ceil(totalRecords / recordsPerPageNum);
        if (totalPages <= 1) return;

        const createPageItem = (text, pageNum, isDisabled = false, isActive = false) => {
            const li = document.createElement('li');
            li.className = `page-item ${isDisabled ? 'disabled' : ''} ${isActive ? 'active' : ''}`;
            const a = document.createElement('a');
            a.className = 'page-link';
            a.href = '#';
            a.innerHTML = text;
            a.addEventListener('click', (e) => {
                e.preventDefault();
                if (!isDisabled) {
                    currentPage = pageNum;
                    loadGastos();
                }
            });
            li.appendChild(a);
            return li;
        };

        paginationContainer.appendChild(createPageItem('«', currentPageNum - 1, currentPageNum === 1));
        // Logic to show a limited number of page buttons around the current page
        const maxPagesToShow = 5; // Example: show 5 page numbers
        let startPage = Math.max(1, currentPageNum - Math.floor(maxPagesToShow / 2));
        let endPage = Math.min(totalPages, startPage + maxPagesToShow - 1);

        if (endPage - startPage + 1 < maxPagesToShow) {
            startPage = Math.max(1, endPage - maxPagesToShow + 1);
        }

        if (startPage > 1) {
            paginationContainer.appendChild(createPageItem('1', 1));
            if (startPage > 2) {
                paginationContainer.appendChild(createPageItem('...', '...', true)); // Ellipsis
            }
        }

        for (let i = startPage; i <= endPage; i++) {
            paginationContainer.appendChild(createPageItem(i, i, false, currentPageNum === i));
        }

        if (endPage < totalPages) {
            if (endPage < totalPages - 1) {
                paginationContainer.appendChild(createPageItem('...', '...', true)); // Ellipsis
            }
            paginationContainer.appendChild(createPageItem(totalPages, totalPages));
        }
        
        paginationContainer.appendChild(createPageItem('»', currentPageNum + 1, currentPageNum === totalPages));
    }
    
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
            renderGastos(result.gastos, result.total_general_monto);
            setupPagination(result.total, currentPage, recordsPerPage);
        }
    }
    
    // --- Event Listeners ---

    formAgregarGasto.addEventListener('submit', async (e) => {
        e.preventDefault();
        const formData = new FormData(formAgregarGasto);
        const gastoData = Object.fromEntries(formData.entries());
        
        // ⭐ IMPORTANT: Rename 'tipoGasto' from the form to 'tipo_gasto' for the backend
        if (gastoData.tipoGasto) {
            gastoData.tipo_gasto = gastoData.tipoGasto;
            delete gastoData.tipoGasto; 
        }

        const result = await addGasto(gastoData);
        if (result && result.success) {
            showToast('success', 'Gasto agregado exitosamente!');
            resetForm(formAgregarGasto);
            modalAgregar.hide();
            loadGastos();
        } else {
            showToast('error', `Error: ${result.message || 'No se pudo agregar el gasto.'}`);
        }
    });

    formEditarGasto.addEventListener('submit', async (e) => {
        e.preventDefault();
        const formData = new FormData(formEditarGasto);
        const gastoData = Object.fromEntries(formData.entries());
        
        // ⭐ IMPORTANT: Rename 'tipoGasto' from the form to 'tipo_gasto' for the backend
        if (gastoData.tipoGasto) {
            gastoData.tipo_gasto = gastoData.tipoGasto;
            delete gastoData.tipoGasto;
        }

        const result = await updateGasto(gastoData);
        if (result && result.success) {
            showToast('success', 'Gasto actualizado exitosamente!');
            modalEditar.hide();
            loadGastos();
        } else {
            showToast('error', `Error: ${result.message || 'No se pudo actualizar el gasto.'}`);
        }
    });

    btnConfirmarEliminar.addEventListener('click', async () => {
        if (gastoIdToDelete) {
            const result = await deleteGasto(gastoIdToDelete);
            if (result && result.success) {
                showToast('success', 'Gasto eliminado exitosamente!');
                modalEliminarConfirmacion.hide();
                gastoIdToDelete = null;
                loadGastos();
            } else {
                showToast('error', `Error: ${result.message || 'No se pudo eliminar el gasto.'}`);
            }
        }
    });

    btnBuscarGastos.addEventListener('click', () => {
        currentPage = 1; // Reset to first page on new search
        loadGastos();
    });

    btnResetSearch.addEventListener('click', () => {
        filterFechaInicio.value = '';
        filterFechaFin.value = '';
        filterDescripcion.value = '';
        currentPage = 1; // Reset to first page on search reset
        loadGastos();
    });

    // Carga inicial de gastos
    loadGastos();
});