document.addEventListener('DOMContentLoaded', () => {
    const API_BASE_URL = 'http://localhost/cusquena/backend/api/controllers/vista_gestion_alquileres/';

    const tablaAlquileres = document.getElementById('tablaAlquileres');
    const formAgregarAlquiler = document.getElementById('formAgregarAlquiler');
    const formEditarAlquiler = document.getElementById('formEditarAlquiler');
    const modalAgregar = new bootstrap.Modal(document.getElementById('modalAgregar'));
    const modalEditar = new bootstrap.Modal(document.getElementById('modalEditar'));
    const modalEliminarConfirmacion = new bootstrap.Modal(document.getElementById('modalEliminarConfirmacion'));
    const btnConfirmarEliminar = document.getElementById('btnConfirmarEliminar');

    const filterNombre = document.getElementById('filterNombre');
    const btnBuscarAlquileres = document.getElementById('btnBuscarAlquileres');
    const paginationContainer = document.getElementById('pagination');

    const toastSuccess = new bootstrap.Toast(document.getElementById('toastSuccess'));
    const toastError = new bootstrap.Toast(document.getElementById('toastError'));
    const toastSuccessBody = document.getElementById('toastSuccessBody');
    const toastErrorBody = document.getElementById('toastErrorBody');

    let alquilerIdToDelete = null;

    function showToast(type, message) {
        if (type === 'success') {
            toastSuccessBody.textContent = message;
            toastSuccess.show();
        } else {
            toastErrorBody.textContent = message;
            toastError.show();
        }
    }

    function resetForm(form) {
        form.reset();
        form.querySelectorAll('select').forEach(select => select.value = '');
        form.querySelectorAll('input[type="radio"]').forEach(radio => radio.checked = false);
    }

    async function fetchAlquileres(filters = {}) {
        try {
            const response = await fetch(`${API_BASE_URL}listar.php?${new URLSearchParams(filters)}`);
            const data = await response.json();
            if (data.error) throw new Error(data.error);
            return data;
        } catch (error) {
            showToast('error', `Error al cargar alquileres: ${error.message}`);
            return { alquileres: [], total: 0 };
        }
    }

    async function addAlquiler(alquilerData) {
        try {
            const response = await fetch(`${API_BASE_URL}registrar.php`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(alquilerData)
            });
            const data = await response.json();
            if (data.error) throw new Error(data.error);
            return data;
        } catch (error) {
            showToast('error', `Error al agregar alquiler: ${error.message}`);
            return { success: false };
        }
    }

    async function updateAlquiler(alquilerData) {
        try {
            const response = await fetch(`${API_BASE_URL}actualizar.php`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(alquilerData)
            });
            const data = await response.json();
            if (data.error) throw new Error(data.error);
            return data;
        } catch (error) {
            showToast('error', `Error al actualizar alquiler: ${error.message}`);
            return { success: false };
        }
    }

    async function deleteAlquiler(id) {
        try {
            const response = await fetch(`${API_BASE_URL}eliminar.php`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ id })
            });
            const data = await response.json();
            if (data.error) throw new Error(data.error);
            return data;
        } catch (error) {
            showToast('error', `Error al eliminar alquiler: ${error.message}`);
            return { success: false };
        }
    }

    function renderAlquileres(alquileres) {
    tablaAlquileres.innerHTML = '';
    if (!alquileres.length) {
        tablaAlquileres.innerHTML = '<tr><td colspan="8" class="text-center">No hay alquileres para mostrar.</td></tr>';
        document.getElementById('totalGeneral').textContent = 'Total General: S/ 0.00';
        return;
    }

    let totalPagos = 0;

    alquileres.forEach(alquiler => {
        const row = document.createElement('tr');
        const pago = parseFloat(alquiler.pago);

        // Solo sumar si el estado es "Activo"
        if (alquiler.estado === 'Activo') {
            totalPagos += pago;
        }

        row.innerHTML = `
            <td>${alquiler.id}</td>
            <td>${alquiler.nombre}</td>
            <td>${alquiler.tipo}</td>
            <td>${alquiler.fecha_inicio}</td>
            <td>${alquiler.periodicidad}</td>
            <td>S/. ${pago.toFixed(2)}</td>
            <td>
                <span class="badge ${alquiler.estado === 'Activo' ? 'bg-success' : 'bg-secondary'}">
                    ${alquiler.estado}
                </span>
            </td>
            <td>
                <button class="btn btn-sm btn-warning me-2" data-id="${alquiler.id}" title="Editar">
                    <i class="fas fa-edit"></i>
                </button>
                <button class="btn btn-sm btn-danger" data-id="${alquiler.id}" title="Eliminar">
                    <i class="fas fa-trash-alt"></i>
                </button>
            </td>`;

        const editBtn = row.querySelector('.btn-warning');
        const deleteBtn = row.querySelector('.btn-danger');

        editBtn.addEventListener('click', () => populateEditModal(alquiler));
        deleteBtn.addEventListener('click', () => {
            alquilerIdToDelete = alquiler.id;
            modalEliminarConfirmacion.show();
        });

        tablaAlquileres.appendChild(row);
    });

    // Mostrar total solo de los alquileres activos
    document.getElementById('totalGeneral').textContent = `Total General: S/ ${totalPagos.toFixed(2)}`;
}

    function populateEditModal(alquiler) {
        document.getElementById('editAlquilerId').value = alquiler.id;
        document.getElementById('editNombre').value = alquiler.nombre;
        document.getElementById('editTipo').value = alquiler.tipo;
        document.getElementById('editFechaInicio').value = alquiler.fecha_inicio;
        document.getElementById('editPeriodicidad').value = alquiler.periodicidad;
        document.getElementById('editPago').value = parseFloat(alquiler.pago).toFixed(2);

        if (alquiler.estado === 'Activo') {
            document.getElementById('editEstadoActivo').checked = true;
        } else {
            document.getElementById('editEstadoInactivo').checked = true;
        }

        modalEditar.show();
    }

    let currentPage = 1;
    const recordsPerPage = 10;

    function setupPagination(total, page, limit) {
        paginationContainer.innerHTML = '';
        const pages = Math.ceil(total / limit);

        if (pages <= 1) return;

        const createPageItem = (text, active = false, disabled = false, onClick = () => {}) => {
            const li = document.createElement('li');
            li.className = `page-item ${active ? 'active' : ''} ${disabled ? 'disabled' : ''}`;
            const a = document.createElement('a');
            a.className = 'page-link';
            a.href = '#';
            a.innerHTML = text;
            a.addEventListener('click', (e) => {
                e.preventDefault();
                onClick();
            });
            li.appendChild(a);
            return li;
        };

        paginationContainer.appendChild(createPageItem('«', false, page === 1, () => {
            if (page > 1) {
                currentPage--;
                loadAlquileres();
            }
        }));

        for (let i = 1; i <= pages; i++) {
            paginationContainer.appendChild(createPageItem(i, i === page, false, () => {
                currentPage = i;
                loadAlquileres();
            }));
        }

        paginationContainer.appendChild(createPageItem('»', false, page === pages, () => {
            if (page < pages) {
                currentPage++;
                loadAlquileres();
            }
        }));
    }

    async function loadAlquileres() {
        const filters = {
            nombre: filterNombre.value,
            page: currentPage,
            limit: recordsPerPage
        };
        const { alquileres, total } = await fetchAlquileres(filters);
        renderAlquileres(alquileres);
        setupPagination(total, currentPage, recordsPerPage);
    }

    formAgregarAlquiler.addEventListener('submit', async (e) => {
        e.preventDefault();
        const data = Object.fromEntries(new FormData(formAgregarAlquiler).entries());
        data.estado = document.querySelector('input[name="estado"]:checked').value;
        const result = await addAlquiler(data);
        if (result.success) {
            showToast('success', '✅ Alquiler agregado exitosamente!');
            resetForm(formAgregarAlquiler);
            modalAgregar.hide();
            loadAlquileres();
        }
    });

    formEditarAlquiler.addEventListener('submit', async (e) => {
        e.preventDefault();
        const data = Object.fromEntries(new FormData(formEditarAlquiler).entries());
        data.estado = document.querySelector('input[name="editEstado"]:checked').value;
        const result = await updateAlquiler(data);
        if (result.success) {
            showToast('success', '✅ Alquiler actualizado exitosamente!');
            modalEditar.hide();
            loadAlquileres();
        }
    });

    btnConfirmarEliminar.addEventListener('click', async () => {
        if (alquilerIdToDelete) {
            const result = await deleteAlquiler(alquilerIdToDelete);
            if (result.success) {
                showToast('success', '✅ Alquiler eliminado exitosamente!');
                modalEliminarConfirmacion.hide();
                alquilerIdToDelete = null;
                loadAlquileres();
            }
        }
    });

    btnBuscarAlquileres.addEventListener('click', () => {
        currentPage = 1;
        loadAlquileres();
    });

    loadAlquileres();
});