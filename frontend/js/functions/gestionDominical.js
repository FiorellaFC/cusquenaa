// gestionDominical.js

// Espera a que el DOM esté completamente cargado
document.addEventListener('DOMContentLoaded', () => {
    const API_BASE_URL = 'http://localhost/cusquena/backend/api/controllers/vista_gestion_dominical/';

    // Elementos de la tabla
    const tablaDominical = document.getElementById('tablaDominical');
    // 'tablaPagosHistorial' se obtiene dentro de cargarPagosDominical ya que está en el modal

    // Elementos de formularios y modales
    const formAgregar = document.getElementById('formAgregarDominical');
    const formEditar = document.getElementById('formEditarDominical');
    const formAgregarPago = document.getElementById('formAgregarPago');
    
    // Referencias para el modal de edición de pago
    const modalEditarPago = new bootstrap.Modal(document.getElementById('modalEditarPago'));
    const formEditarPago = document.getElementById('formEditarPago');

    const modalAgregar = new bootstrap.Modal(document.getElementById('modalAgregarDominical'));
    const modalEditar = new bootstrap.Modal(document.getElementById('modalEditarDominical'));
    const modalEliminarConfirmacion = new bootstrap.Modal(document.getElementById('modalEliminarDominicalConfirmacion')); 
    const modalVerPagos = new bootstrap.Modal(document.getElementById('modalVerPagos'));

    // NUEVAS referencias para el modal de eliminación de PAGOS
    const modalEliminarPagoConfirmacion = new bootstrap.Modal(document.getElementById('modalEliminarPagoConfirmacion'));
    const btnConfirmarEliminarPago = document.getElementById('btnConfirmarEliminarPago'); 

    const btnConfirmarEliminarDominical = document.getElementById('btnConfirmarEliminarDominical');

    const buscarDominicalInput = document.getElementById('buscarDominical');
    const semanaInicioFiltroInput = document.getElementById('semanaInicioFiltro');
    const semanaFinFiltroInput = document.getElementById('semanaFinFiltro');
    const filterEstadoDominicalInput = document.getElementById('filterEstadoDominical');
    const btnBuscar = document.getElementById('btnBuscar');

    // Elementos para los Toasts
    const toastSuccess = new bootstrap.Toast(document.getElementById('toastSuccess'));
    const toastError = new bootstrap.Toast(document.getElementById('toastError'));
    const toastSuccessBody = document.getElementById('toastSuccessBody');
    const toastErrorBody = document.getElementById('toastErrorBody');

    // Elementos para mostrar los montos totales
    const totalGeneralMontoDisplay = document.getElementById('totalGeneralMontoDisplay');
    // const totalDiferenciaDisplay = document.getElementById('totalDiferenciaDisplay');  // Se eliminó la referencia

    let dominicalIdAEliminar = null; 

    // Referencia al contenedor de paginación
    const paginationContainer = document.querySelector('.pagination.justify-content-end');
    let currentPage = 1;
    const itemsPerPage = 10; // Define cuántos elementos por página quieres mostrar

    // --- Funciones de Utilidad ---

    /**
     * Muestra un mensaje Toast de éxito o error.
     * @param {string} type 
     * @param {string} message 
     */
    function showToast(type, message) {
        if (type === 'success') {
            toastSuccessBody.textContent = message;
            toastSuccess.show();
        } else {
            toastErrorBody.textContent = message;
            toastError.show();
        }
    }

    /**
     * Resetea los campos de un formulario.
     * @param {HTMLFormElement} form - El formulario a resetear.
     */
    function resetForm(form) {
        form.reset();
        form.querySelectorAll('select').forEach(select => {
            if (select.options.length > 0) {
                select.selectedIndex = 0; 
            }
        });
    }

    // Función para obtener el badge de estado
    function getBadgeEstado(estado) {
        const estadoNormalizado = estado.toLowerCase().trim();
        switch (estadoNormalizado) {
            case 'pagado':
                return `<span class="badge bg-success">Pagado</span>`;
            case 'pendiente':
                return `<span class="badge bg-warning text-dark">Pendiente</span>`;
            case 'exento':
                return `<span class="badge bg-danger">Exento</span>`;
            default:
                return `<span class="badge bg-secondary">${estado}</span>`;
        }
    }

      /**
     * Genera y actualiza los botones de paginación.
     * @param {number} totalPages - Número total de páginas.
     */
    function renderPagination(totalPages) {
        paginationContainer.innerHTML = '';
        if (totalPages < 1) return;

        // Botón "Anterior"
        const prevLi = document.createElement('li');
        prevLi.className = `page-item ${currentPage === 1 ? 'disabled' : ''}`;
        prevLi.innerHTML = `<a class="page-link" href="#" aria-label="Previous"><span aria-hidden="true">«</span></a>`;
        prevLi.addEventListener('click', (e) => {
            e.preventDefault();
            if (currentPage > 1) {
                currentPage--;
                cargarDominicales(getFilters());
            }
        });
        paginationContainer.appendChild(prevLi);

        // Lógica de paginación para los números
        for (let i = 1; i <= totalPages; i++) {
            const li = document.createElement('li');
            li.className = `page-item ${i === currentPage ? 'active' : ''}`;
            li.innerHTML = `<a class="page-link" href="#">${i}</a>`;
            li.addEventListener('click', (e) => {
                e.preventDefault();
                if (i !== currentPage) {
                    currentPage = i;
                    cargarDominicales(getFilters());
                }
            });
            paginationContainer.appendChild(li);
        }

        // Botón "Siguiente"
        const nextLi = document.createElement('li');
        nextLi.className = `page-item ${currentPage === totalPages ? 'disabled' : ''}`;
        nextLi.innerHTML = `<a class="page-link" href="#" aria-label="Next"><span aria-hidden="true">»</span></a>`;
        nextLi.addEventListener('click', (e) => {
            e.preventDefault();
            if (currentPage < totalPages) {
                currentPage++;
                cargarDominicales(getFilters());
            }
        });
        paginationContainer.appendChild(nextLi);
    }
    
    /**
     * Crea un elemento de lista (li) para un número de página.
     * @param {number} pageNum
     * @param {number} currentPage
     * @returns {HTMLLIElement}
     */
    function createPageItem(pageNum, currentPage) {
        const li = document.createElement('li');
        li.className = `page-item ${pageNum === currentPage ? 'active' : ''}`;
        li.setAttribute('data-page', pageNum); // Nuevo atributo para facilitar la selección
        li.innerHTML = `<a class="page-link" href="#">${pageNum}</a>`;
        li.addEventListener('click', (e) => {
            e.preventDefault();
            if (pageNum !== currentPage) {
                // AQUÍ ESTÁ LA LÍNEA CLAVE QUE FALTABA O ESTABA MAL
                currentPage = pageNum;
                cargarDominicales(getFilters());
            }
        });
        return li;
    }

    /**
     * Crea un elemento de elipsis para la paginación.
     * @returns {HTMLLIElement}
     */
    function createEllipsis() {
        const ellipsisLi = document.createElement('li');
        ellipsisLi.className = 'page-item disabled';
        ellipsisLi.innerHTML = '<span class="page-link">...</span>';
        return ellipsisLi;
    }

    // --- Carga y Renderizado de la Tabla Principal (Dominicales) ---

    async function cargarDominicales(filtros = {}) {
        try {
            const params = new URLSearchParams({
                ...filtros,
                pagina: currentPage,
                limite: itemsPerPage
            });
            const response = await fetch(`${API_BASE_URL}listar.php?${params.toString()}`);
            const data = await response.json();
            
            renderizarTabla(data.dominicales || []);
            
            const totalGeneralMonto = data.total_general_monto || 0.00;
            totalGeneralMontoDisplay.textContent = `S/. ${parseFloat(totalGeneralMonto).toFixed(2)}`;

            // Se eliminó la línea que intentaba acceder a totalDiferenciaDisplay
            // const totalDiferencia = data.total_diferencia || 0.00;
            // totalDiferenciaDisplay.textContent = `S/. ${parseFloat(totalDiferencia).toFixed(2)}`;

            const totalPages = Math.ceil((data.total_registros || 0) / itemsPerPage);
            renderPagination(totalPages);
            
        } catch (error) {
            console.error('Error al cargar datos dominicales:', error);
            showToast('error', '❌ Error al cargar los dominicales. Intenta de nuevo.');
            renderizarTabla([]);
            totalGeneralMontoDisplay.textContent = 'S/. 0.00'; 
            // Se eliminó la línea que intentaba acceder a totalDiferenciaDisplay
            // totalDiferenciaDisplay.textContent = 'S/. 0.00'; 
            renderPagination(0);
        }
    }

    function renderizarTabla(lista) {
        tablaDominical.innerHTML = ''; 

        if (!lista || lista.length === 0) {
            tablaDominical.innerHTML = '<tr><td colspan="7" class="text-center">No hay datos registrados.</td></tr>';
            return;
        }

        lista.forEach(d => {
            const row = tablaDominical.insertRow();
            row.insertCell().textContent = d.nombre;
            row.insertCell().textContent = d.apellidos;
            row.insertCell().textContent = new Date(d.fecha_domingo).toLocaleDateString('es-ES');
            row.insertCell().textContent = new Date(d.semana_inicio).toLocaleDateString('es-ES');
            row.insertCell().textContent = new Date(d.semana_fin).toLocaleDateString('es-ES');
            row.insertCell().textContent = `S/. ${parseFloat(d.monto_dominical).toFixed(2)}`;
            const estadoCell = row.insertCell();
            estadoCell.innerHTML = getBadgeEstado(d.estado);
            const accionesCell = row.insertCell();

            const btnEditar = document.createElement('button');
            btnEditar.className = 'btn btn-warning btn-sm me-1';
            btnEditar.innerHTML = '<i class="fas fa-edit"></i>';
            btnEditar.title = 'Editar';
            btnEditar.addEventListener('click', () => llenarModalEditar(d));
            accionesCell.appendChild(btnEditar);

            const btnEliminar = document.createElement('button');
            btnEliminar.className = 'btn btn-danger btn-sm me-1';
            btnEliminar.innerHTML = '<i class="fas fa-trash-alt"></i>';
            btnEliminar.title = 'Eliminar';
            btnEliminar.addEventListener('click', () => {
                dominicalIdAEliminar = d.id;
                document.getElementById('dominicalIdParaConfirmarEliminar').value = d.id; 
                modalEliminarConfirmacion.show(); 
            });
            accionesCell.appendChild(btnEliminar);

            const btnVerPagos = document.createElement('button');
            btnVerPagos.className = 'btn btn-info btn-sm';
            btnVerPagos.innerHTML = '<i class="fas fa-eye"></i>';
            btnVerPagos.title = 'Ver Pagos';
            btnVerPagos.addEventListener('click', () => {
                dominicalIdAEliminar = d.id; 
                document.getElementById('nombreDominical').textContent = `${d.nombre} ${d.apellidos}`;
                document.getElementById('pagoDominicalId').value = d.id;
                cargarPagosDominical(d.id); 
                modalVerPagos.show();
            });
            accionesCell.appendChild(btnVerPagos);
        });
    }

    // Manejo del formulario de agregar dominical
    formAgregar.addEventListener('submit', async e => {
        e.preventDefault();
        const formData = new FormData(formAgregar);
        const datos = Object.fromEntries(formData.entries());

        // Se eliminó la propiedad "diferencia" de los datos a enviar
        delete datos.diferencia;

        try {
            const response = await fetch(`${API_BASE_URL}registrar.php`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(datos)
            });
            const data = await response.json();

            if (data.success) {
                modalAgregar.hide();
                resetForm(formAgregar);
                showToast('success', '✅ Dominical registrado exitosamente.');
                currentPage = 1; 
                cargarDominicales(getFilters());
            } else {
                showToast('error', data.error || '❌ Error al registrar el dominical.');
            }
        } catch (error) {
            console.error('Error al registrar:', error);
            showToast('error', '❌ Error de conexión al registrar el dominical.');
        }
    });

    // Función para llenar el modal de edición de Dominical
    function llenarModalEditar(d) {
        document.getElementById('editDominicalId').value = d.id;
        document.getElementById('edit_nombre').value = d.nombre;
        document.getElementById('edit_apellidos').value = d.apellidos;
        document.getElementById('edit_fechaDomingo').value = d.fecha_domingo;
        document.getElementById('edit_semanaInicio').value = d.semana_inicio;
        document.getElementById('edit_semanaFin').value = d.semana_fin;
        document.getElementById('edit_montoDominical').value = parseFloat(d.monto_dominical).toFixed(2);
        document.getElementById('edit_estado').value = d.estado;
        modalEditar.show();
    }

    // Manejo del formulario de editar Dominical
    formEditar.addEventListener('submit', async e => {
        e.preventDefault();
        const formData = new FormData(formEditar);
        const datos = Object.fromEntries(formData.entries());
        datos.id = document.getElementById('editDominicalId').value; 

        // Se eliminó la propiedad "diferencia" de los datos a enviar
        delete datos.diferencia;

        try {
            const response = await fetch(`${API_BASE_URL}actualizar.php`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(datos)
            });
            const data = await response.json();

            if (data.success) {
                modalEditar.hide();
                showToast('success', '✅ Dominical actualizado exitosamente.');
                cargarDominicales(getFilters());
            } else {
                showToast('error', data.error || '❌ Error al actualizar el dominical.');
            }
        } catch (error) {
            console.error('Error al actualizar:', error);
            showToast('error', '❌ Error de conexión al actualizar el dominical.');
        }
    });

    // Evento para el botón de Confirmar Eliminación del Dominical principal (en su propio modal)
    btnConfirmarEliminarDominical.addEventListener('click', async () => {
        const idToDelete = document.getElementById('dominicalIdParaConfirmarEliminar').value; 
        if (!idToDelete) return;

        try {
            const response = await fetch(`${API_BASE_URL}eliminar.php`, { 
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ id: idToDelete })
            });

            const data = await response.json();
            modalEliminarConfirmacion.hide(); 

            if (data.success) {
                showToast('success', '✅ Dominical eliminado exitosamente!');
                dominicalIdAEliminar = null;
                cargarDominicales(getFilters());
            } else {
                showToast('error', data.error || '❌ Error al eliminar el dominical.');
            }
        } catch (error) {
            console.error('Error al eliminar dominical:', error);
            showToast('error', '❌ Hubo un problema de conexión al intentar eliminar el dominical.');
        }
    });

    // --- Funciones de Gestión de Pagos Individuales ---

    // Función para cargar los pagos de un dominical específico en el modal
    async function cargarPagosDominical(dominicalId) {
        const tablaPagosHistorial = document.getElementById('tablaPagosHistorial');
        tablaPagosHistorial.innerHTML = ''; 

        try {
            const response = await fetch(`${API_BASE_URL}listar_pagos.php?dominical_id=${dominicalId}`);
            const data = await response.json();

            if (data.success && data.pagos.length > 0) {
                data.pagos.forEach(pago => {
                    const row = tablaPagosHistorial.insertRow();
                    row.insertCell().textContent = new Date(pago.fecha_pago).toLocaleDateString('es-ES');
                    row.insertCell().textContent = `S/. ${parseFloat(pago.monto_pagado).toFixed(2)}`;

                    const accionesCell = row.insertCell();

                    const btnEditarPago = document.createElement('button');
                    btnEditarPago.className = 'btn btn-warning btn-sm me-1'; 
                    btnEditarPago.innerHTML = '<i class="fas fa-edit"></i>';
                    btnEditarPago.title = 'Editar Pago';
                    btnEditarPago.addEventListener('click', () => llenarModalEditarPago(pago, dominicalId)); 
                    accionesCell.appendChild(btnEditarPago);

                    const btnEliminarPago = document.createElement('button');
                    btnEliminarPago.className = 'btn btn-danger btn-sm';
                    btnEliminarPago.innerHTML = '<i class="fas fa-trash-alt"></i>';
                    btnEliminarPago.title = 'Eliminar Pago';
                    btnEliminarPago.addEventListener('click', () => {
                        document.getElementById('pagoIdParaEliminarConfirmacion').value = pago.id;
                        document.getElementById('dominicalIdParaPagoEliminarConfirmacion').value = dominicalId;
                        modalEliminarPagoConfirmacion.show(); 
                    });
                    accionesCell.appendChild(btnEliminarPago);
                });
            } else {
                tablaPagosHistorial.innerHTML = '<tr><td colspan="2" class="text-center">No hay pagos registrados para este dominical.</td></tr>';
            }
        } catch (error) {
            console.error('Error al cargar pagos del dominical:', error);
            showToast('error', '❌ Error al cargar el historial de pagos.');
        }
    }

    // Función para llenar el modal de edición de Pago Individual
    function llenarModalEditarPago(pago, dominicalId) {
        modalVerPagos.hide(); 
        document.getElementById('editPagoId').value = pago.id;
        document.getElementById('editPagoDominicalId').value = dominicalId; 
        document.getElementById('editFechaPago').value = pago.fecha_pago;
        document.getElementById('editMontoPago').value = parseFloat(pago.monto_pagado).toFixed(2);
        modalEditarPago.show(); 
    }

    // Manejo del formulario para agregar un nuevo pago
    formAgregarPago.addEventListener('submit', async e => {
        e.preventDefault();
        const dominicalId = document.getElementById('pagoDominicalId').value;
        const fechaPago = document.getElementById('fechaPago').value;
        const montoPago = document.getElementById('montoPago').value;

        try {
            const response = await fetch(`${API_BASE_URL}registrar_pago.php`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ dominical_id: dominicalId, fecha_pago: fechaPago, monto_pagado: montoPago })
            });
            const data = await response.json();

            if (data.success) {
                resetForm(formAgregarPago);
                showToast('success', '✅ Pago registrado exitosamente.');
                cargarPagosDominical(dominicalId); 
                cargarDominicales(getFilters()); 
            } else {
                showToast('error', data.error || '❌ Error al registrar el pago.');
            }
        } catch (error) {
            console.error('Error al registrar pago:', error);
            showToast('error', '❌ Error de conexión al registrar el pago.');
        }
    });

    // Manejo del formulario de editar Pago Individual
    formEditarPago.addEventListener('submit', async e => {
        e.preventDefault();
        const formData = new FormData(formEditarPago);
        const datos = Object.fromEntries(formData.entries());

        try {
            const response = await fetch(`${API_BASE_URL}actualizar_pago.php`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(datos)
            });
            const data = await response.json();

            if (data.success) {
                modalEditarPago.hide();
                showToast('success', '✅ Pago actualizado exitosamente.');
                cargarPagosDominical(datos.dominical_id); 
                cargarDominicales(getFilters()); 
                modalVerPagos.show(); 
            } else {
                showToast('error', data.error || '❌ Error al actualizar el pago.');
            }
        } catch (error) {
            console.error('Error al actualizar pago:', error);
            showToast('error', '❌ Error de conexión al actualizar el pago.');
        }
    });

    modalEditarPago._element.addEventListener('hidden.bs.modal', () => {
        modalVerPagos.show();
    });
        
    async function eliminarPago(pagoId, dominicalId) {
        try {
            const response = await fetch(`${API_BASE_URL}eliminar_pago.php`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ id: pagoId })
            });
            const data = await response.json();

            if (data.success) {
                showToast('success', '✅ Pago eliminado exitosamente.');
                cargarPagosDominical(dominicalId); 
                cargarDominicales(getFilters()); 
            } else {
                showToast('error', data.error || '❌ Error al eliminar el pago.');
            }
        } catch (error) {
            console.error('Error al eliminar pago:', error);
            showToast('error', '❌ Error de conexión al eliminar el pago.');
        }
    }

    // Evento para el botón de Confirmar Eliminación del PAGO (en el modal de pago)
    btnConfirmarEliminarPago.addEventListener('click', async () => {
        const pagoIdToDelete = document.getElementById('pagoIdParaEliminarConfirmacion').value;
        const dominicalIdAssociated = document.getElementById('dominicalIdParaPagoEliminarConfirmacion').value;

        if (!pagoIdToDelete || !dominicalIdAssociated) {
            showToast('error', '❌ No se encontró el ID del pago o dominical asociado para eliminar.');
            modalEliminarPagoConfirmacion.hide();
            return;
        }

        await eliminarPago(pagoIdToDelete, dominicalIdAssociated);
        modalEliminarPagoConfirmacion.hide(); 
    });


    // --- Manejo de Filtros ---
    function getFilters() {
        return {
            nombre: buscarDominicalInput.value.trim(),
            semana_inicio: semanaInicioFiltroInput.value,
            semana_fin: semanaFinFiltroInput.value,
            estado: filterEstadoDominicalInput.value
        };
    }
    
    function aplicarFiltros() {
        currentPage = 1;
        cargarDominicales(getFilters());
    }
    
    // Evento para el botón de buscar
    btnBuscar.addEventListener('click', aplicarFiltros);

    // Evento para el cambio en el select de estado
    filterEstadoDominicalInput.addEventListener('change', aplicarFiltros);
    
    // Carga inicial de datos
    cargarDominicales(getFilters());

    // Inicializar el sidebar toggle (Bootstrap SB Admin template related)
    if (document.body.classList.contains('sb-nav-fixed')) {
        const sidebarToggle = document.body.querySelector('#sidebarToggle');
        if (sidebarToggle) {
            sidebarToggle.addEventListener('click', event => {
                event.preventDefault();
                document.body.classList.toggle('sb-sidenav-toggled');
            });
        }
    }
});