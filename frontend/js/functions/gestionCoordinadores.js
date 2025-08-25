document.addEventListener('DOMContentLoaded', () => {
    const API_BASE_URL = 'http://localhost/cusquena/backend/api/controllers/vista_gestion_coordinadores/';
    
    const tablaCoordinadores = document.getElementById('tablaCoordinadores');
    const formAgregar = document.getElementById('formAgregarCoordinador');
    const formEditar = document.getElementById('formEditarCoordinador');
    const modalAgregar = new bootstrap.Modal(document.getElementById('modalAgregarCoordinador'));
    const modalEditar = new bootstrap.Modal(document.getElementById('modalEditarCoordinador'));
    const modalEliminar = new bootstrap.Modal(document.getElementById('modalEliminarConfirmacion'));
    const btnEliminarConfirmado = document.getElementById('btnEliminarConfirmado');

    const toastSuccess = new bootstrap.Toast(document.getElementById('toastSuccess'));
    const toastError = new bootstrap.Toast(document.getElementById('toastError'));
    const toastSuccessBody = document.getElementById('toastSuccessBody');
    const toastErrorBody = document.getElementById('toastErrorBody');

    let idAEliminar = null;

    // Elementos de filtro y paginación
    const buscarCoordinadorInput = document.getElementById('buscarCoordinador');
    const fechaFiltroInput = document.getElementById('fechaFiltro');
    const paraderoFiltroInput = document.getElementById('paraderoFiltro');
    const btnBuscar = document.getElementById('btnBuscar');
    const paginationContainer = document.querySelector('.pagination');
    const totalGeneralElement = document.getElementById('totalGeneral');

    let currentPage = 1;
    const recordsPerPage = 10;

    // --- Funciones de Utilidad ---
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
        form.querySelectorAll('select').forEach(select => {
            if (select.options.length > 0) {
                select.selectedIndex = 0;
            }
        });
    }

    /**
     * Devuelve el HTML para una etiqueta de estado con colores.
     * @param {string} estado
     * @returns {string}
     */
    function getBadgeEstado(estado) {
        const estadoNormalizado = estado.toLowerCase().trim();
        switch (estadoNormalizado) {
            case 'pagado':
                return `<span class="badge bg-success">Pagado</span>`;
            case 'pendiente':
                return `<span class="badge bg-warning text-dark">Pendiente</span>`;
            default:
                return `<span class="badge bg-secondary">${estado}</span>`;
        }
    }

    // --- Paginación ---
    function renderPagination(totalRecords, currentPage) {
        paginationContainer.innerHTML = '';
        const totalPages = Math.ceil(totalRecords / recordsPerPage);

        const liPrev = document.createElement('li');
        liPrev.className = `page-item ${currentPage === 1 ? 'disabled' : ''}`;
        liPrev.innerHTML = `<a class="page-link" href="#" aria-label="Previous"><span aria-hidden="true">«</span></a>`;
        liPrev.addEventListener('click', (e) => {
            e.preventDefault();
            if (currentPage > 1) {
                currentPage--;
                cargarCoordinadores(currentPage, getCurrentFilters());
            }
        });
        paginationContainer.appendChild(liPrev);

        for (let i = 1; i <= totalPages; i++) {
            const li = document.createElement('li');
            li.className = `page-item ${i === currentPage ? 'active' : ''}`;
            li.innerHTML = `<a class="page-link" href="#">${i}</a>`;
            li.addEventListener('click', (e) => {
                e.preventDefault();
                currentPage = i;
                cargarCoordinadores(currentPage, getCurrentFilters());
            });
            paginationContainer.appendChild(li);
        }

        const liNext = document.createElement('li');
        liNext.className = `page-item ${currentPage === totalPages ? 'disabled' : ''}`;
        liNext.innerHTML = `<a class="page-link" href="#" aria-label="Next"><span aria-hidden="true">»</span></a>`;
        liNext.addEventListener('click', (e) => {
            e.preventDefault();
            if (currentPage < totalPages) {
                currentPage++;
                cargarCoordinadores(currentPage, getCurrentFilters());
            }
        });
        paginationContainer.appendChild(liNext);
    }

    // --- Carga y Renderizado de la Tabla ---
    async function cargarCoordinadores(page = 1, filters = {}) {
        let queryString = `page=${page}&limit=${recordsPerPage}`;

        if (filters.nombre_apellido) {
            queryString += `&nombre=${encodeURIComponent(filters.nombre_apellido)}`;
        }
        if (filters.fecha) {
            queryString += `&fecha=${encodeURIComponent(filters.fecha)}`;
        }
        if (filters.paradero) {
            queryString += `&paradero=${encodeURIComponent(filters.paradero)}`;
        }

        try {
            const response = await fetch(`${API_BASE_URL}listar.php?${queryString}`);
            if (!response.ok) {
                throw new Error(`Error HTTP: ${response.status}`);
            }
            const data = await response.json();

            if (data.error) {
                console.error('Error del servidor al cargar coordinadores:', data.error);
                showToast('error', `❌ Error al cargar los coordinadores: ${data.error}`);
                renderizarTabla([]);
                renderPagination(0, 1);
                totalGeneralElement.textContent = 'S/. 0.00'; // Resetear el total en caso de error
            } else {
                renderizarTabla(data.coordinadores || []);
                renderPagination(data.total || 0, page);
                // ✅ MODIFICACIÓN: Leer el total del monto directamente de la respuesta del servidor
                totalGeneralElement.textContent = `S/. ${parseFloat(data.total_general_monto).toFixed(2)}`;
            }
        } catch (error) {
            console.error('Error de red o parsing al cargar coordinadores:', error);
            showToast('error', '❌ Error de conexión al cargar los coordinadores. Intenta de nuevo.');
            renderizarTabla([]);
            renderPagination(0, 1);
            totalGeneralElement.textContent = 'S/. 0.00'; // Resetear el total en caso de error de red
        }
    }

    function renderizarTabla(coordinadores) {
        tablaCoordinadores.innerHTML = '';
        // ✅ ELIMINADO: La variable totalMontoDiario ya no es necesaria aquí.
        
        if (!coordinadores || coordinadores.length === 0) {
            tablaCoordinadores.innerHTML = '<tr><td colspan="8" class="text-center">No hay coordinadores registrados que coincidan con la búsqueda.</td></tr>';
            // ✅ ELIMINADO: La actualización del total se hace en cargarCoordinadores.
            return;
        }

        coordinadores.forEach(c => {
            const row = tablaCoordinadores.insertRow();
            // ✅ ELIMINADO: La suma se hace en el backend.
            
            row.insertCell().textContent = c.nombre;
            row.insertCell().textContent = c.apellidos;
            row.insertCell().textContent = c.paradero;
            row.insertCell().textContent = `S/. ${parseFloat(c.monto_diario).toFixed(2)}`;
            row.insertCell().textContent = c.fecha;
            
            const estadoCell = row.insertCell();
            estadoCell.innerHTML = getBadgeEstado(c.estado);
            
            row.insertCell().textContent = c.contacto || '';

            const acciones = row.insertCell();

            // Botón Editar con icono
            const btnEditar = document.createElement('button');
            btnEditar.className = 'btn btn-warning btn-sm me-1';
            btnEditar.innerHTML = '<i class="fas fa-edit"></i>';
            btnEditar.title = 'Editar Coordinador';
            btnEditar.addEventListener('click', () => llenarModalEditar(c));
            acciones.appendChild(btnEditar);

            // Botón Eliminar con icono
            const btnEliminar = document.createElement('button');
            btnEliminar.className = 'btn btn-danger btn-sm';
            btnEliminar.innerHTML = '<i class="fas fa-trash-alt"></i>';
            btnEliminar.title = 'Eliminar Coordinador';
            btnEliminar.addEventListener('click', () => {
                idAEliminar = c.id;
                modalEliminar.show();
            });
            acciones.appendChild(btnEliminar);
        });

        // ✅ ELIMINADO: La actualización del total se hace en cargarCoordinadores.
    }

    // ... (rest of the code is unchanged) ...

    function llenarModalEditar(coordinador) {
        document.getElementById('editCoordinadorId').value = coordinador.id;
        document.getElementById('edit_nombre').value = coordinador.nombre;
        document.getElementById('edit_apellidos').value = coordinador.apellidos;
        document.getElementById('edit_paradero').value = coordinador.paradero;
        document.getElementById('edit_montoDiario').value = parseFloat(coordinador.monto_diario).toFixed(2);
        document.getElementById('edit_fecha').value = coordinador.fecha;
        document.getElementById('edit_estado').value = coordinador.estado;
        document.getElementById('edit_contacto').value = coordinador.contacto || '';

        modalEditar.show();
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
                showToast('success', '✅ Coordinador registrado exitosamente!');
                resetForm(formAgregar); 
                modalAgregar.hide();
                cargarCoordinadores(currentPage, getCurrentFilters());
            } else {
                showToast('error', `❌ Error al registrar: ${data.error || 'Mensaje de error desconocido.'}`);
            }
        } catch (error) {
            console.error('Error al registrar coordinador:', error);
            showToast('error', '❌ Hubo un problema de conexión al intentar registrar el coordinador.');
        }
    });

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
                showToast('success', '✅ Coordinador actualizado exitosamente!');
                modalEditar.hide();
                cargarCoordinadores(currentPage, getCurrentFilters());
            } else {
                showToast('error', `❌ Error al actualizar: ${data.error || 'Mensaje de error desconocido.'}`);
            }
        } catch (error) {
            console.error('Error al actualizar coordinador:', error);
            showToast('error', '❌ Hubo un problema de conexión al intentar actualizar el coordinador.');
        }
    });

    btnEliminarConfirmado.addEventListener('click', async () => {
        if (!idAEliminar) return; 

        try {
            const response = await fetch(`${API_BASE_URL}eliminar.php`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ id: idAEliminar }) 
            });

            const data = await response.json();
            modalEliminar.hide(); 

            if (data.success) {
                showToast('success', '✅ Coordinador eliminado exitosamente!');
                idAEliminar = null; 
                cargarCoordinadores(currentPage, getCurrentFilters());
            } else {
                showToast('error', `❌ Error al eliminar: ${data.error || 'Mensaje de error desconocido.'}`);
            }
        } catch (error) {
            console.error('Error al eliminar coordinador:', error);
            showToast('error', '❌ Hubo un problema de conexión al intentar eliminar el coordinador.');
        }
    });

    // --- Función para obtener los filtros actuales (simplificada) ---
    function getCurrentFilters() {
        return {
            nombre_apellido: buscarCoordinadorInput.value.trim(),
            fecha: fechaFiltroInput.value.trim(),
            paradero: paraderoFiltroInput.value.trim()
        };
    }

    // Evento para el botón de búsqueda
    btnBuscar.addEventListener('click', () => {
        currentPage = 1;
        const filters = getCurrentFilters();
        cargarCoordinadores(currentPage, filters);
    });

    // Opcional: Búsqueda al presionar Enter en los campos de filtro
    buscarCoordinadorInput.addEventListener('keypress', (e) => { if (e.key === 'Enter') btnBuscar.click(); });
    fechaFiltroInput.addEventListener('keypress', (e) => { if (e.key === 'Enter') btnBuscar.click(); });
    paraderoFiltroInput.addEventListener('keypress', (e) => { if (e.key === 'Enter') btnBuscar.click(); });

    // --- Inicialización ---
    cargarCoordinadores(currentPage, getCurrentFilters()); 
});