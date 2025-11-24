document.addEventListener('DOMContentLoaded', () => {
    console.log("Script de gestión de clientes cargado.");

    // --- RUTAS ACTUALIZADAS A SUBCARPETAS ---
    // API para clientes en: backend/api/controllers/vista_clientes/gestionClientes.php
    const API_CLIENTES_URL = 'http://localhost/cusquena/backend/api/controllers/vista_clientes/gestionClientes.php';
    
    // API para citas (historial) en: backend/api/controllers/vista_citas/gestionCitasAdmin.php
    const API_CITAS_URL = 'http://localhost/cusquena/backend/api/controllers/vista_citas/gestionCitasAdmin.php';

    // --- REFERENCIAS AL DOM ---
    const tbodyClientes = document.querySelector('#tblClientes tbody');
    const buscarDNI = document.getElementById('buscarDNI');
    const buscarNombre = document.getElementById('buscarNombre');
    const btnBuscar = document.getElementById('btnBuscar');
    const btnLimpiar = document.getElementById('btnLimpiar');

    // Modales principales
    const modalAgregar = new bootstrap.Modal(document.getElementById('modalAgregarCliente'));
    const formAgregar = document.getElementById('formAgregarCliente');
    const modalEditar = new bootstrap.Modal(document.getElementById('modalEditarCliente'));
    const formEditar = document.getElementById('formEditarCliente');
    const modalEliminar = new bootstrap.Modal(document.getElementById('modalEliminarCliente'));
    const btnConfirmarEliminar = document.getElementById('btnConfirmarEliminar');

    // Elementos del Modal de Historial
    const modalHistorial = new bootstrap.Modal(document.getElementById('modalHistorialCliente'));
    const tbodyHistorial = document.getElementById('tbodyHistorial');
    const historialNombreCliente = document.getElementById('historialNombreCliente');
    const filtroHistorial = document.getElementById('filtroHistorial');

    let clientesData = []; 
    let clienteIdParaEliminar = null; 
    let historialActual = []; 

    // --- API GENÉRICA ---
    async function api(url, options = {}) {
        try {
            const response = await fetch(url, options);
            if (!response.ok) {
                const errorData = await response.json().catch(() => ({}));
                throw new Error(errorData.error || `Error: ${response.statusText}`);
            }
            return response.status === 204 ? { success: true } : await response.json();
        } catch (error) {
            console.error("Error en la API:", error);
            alert('Error: ' + error.message);
            return null;
        }
    }

    // --- RENDERIZADO PRINCIPAL ---
    function renderizarTabla() {
        if (!tbodyClientes) return;
        tbodyClientes.innerHTML = '';

        if (clientesData && clientesData.length > 0) {
            clientesData.forEach(cliente => {
                const tr = document.createElement('tr');
                tr.dataset.cliente = JSON.stringify(cliente); 
                tr.innerHTML = `
                    <td>${cliente.nombre}</td>
                    <td>${cliente.dni_ruc || 'N/A'}</td>
                    <td>${cliente.telefono || 'N/A'}</td>
                    <td>${cliente.email || 'N/A'}</td>
                    <td>
                        <button class="btn btn-sm btn-info text-white btn-historial me-1" data-id="${cliente.id}" title="Ver Historial">
                            <i class="fas fa-history"></i>
                        </button>
                        <button class="btn btn-sm btn-warning text-white btn-edit me-1" title="Editar">
                            <i class="fas fa-pencil-alt"></i>
                        </button>
                        <button class="btn btn-sm btn-danger btn-eliminar" data-id="${cliente.id}" title="Eliminar">
                            <i class="fas fa-trash-alt"></i>
                        </button>
                    </td>
                `;
                tbodyClientes.appendChild(tr);
            });
        } else {
            tbodyClientes.innerHTML = '<tr><td colspan="5" class="text-center">No se encontraron clientes.</td></tr>';
        }
    }

    // --- LOGICA DE HISTORIAL ---
    async function cargarHistorial(clienteId, nombreCliente) {
        historialNombreCliente.textContent = nombreCliente;
        tbodyHistorial.innerHTML = '<tr><td colspan="4" class="text-center">Cargando historial...</td></tr>';
        modalHistorial.show();

        const citas = await api(`${API_CITAS_URL}?cliente_id=${clienteId}`);
        historialActual = citas || [];
        renderizarHistorial(historialActual);
    }

    function renderizarHistorial(listaCitas) {
        tbodyHistorial.innerHTML = '';
        if (listaCitas && listaCitas.length > 0) {
            listaCitas.forEach(cita => {
                let badgeClass = '';
                switch(cita.estado) {
                    case 'confirmada': badgeClass = 'bg-success'; break;
                    case 'cancelada': badgeClass = 'bg-danger'; break;
                    case 'completada': badgeClass = 'bg-primary'; break;
                    default: badgeClass = 'bg-secondary';
                }

                const tr = document.createElement('tr');
                tr.innerHTML = `
                    <td>${cita.fecha}</td>
                    <td>${cita.hora.substring(0, 5)}</td>
                    <td class="text-start">${cita.servicio_solicitado || 'N/A'}</td>
                    <td><span class="badge ${badgeClass}">${cita.estado}</span></td>
                `;
                tbodyHistorial.appendChild(tr);
            });
        } else {
            tbodyHistorial.innerHTML = '<tr><td colspan="4" class="text-center text-muted">Este cliente no tiene citas registradas.</td></tr>';
        }
    }

    filtroHistorial.addEventListener('input', (e) => {
        const termino = e.target.value.toLowerCase();
        const filtrados = historialActual.filter(c => 
            c.fecha.includes(termino) || 
            (c.servicio_solicitado && c.servicio_solicitado.toLowerCase().includes(termino)) ||
            c.estado.toLowerCase().includes(termino)
        );
        renderizarHistorial(filtrados);
    });

    // --- CARGA DE CLIENTES ---
    async function cargarClientes() {
        const dni = buscarDNI.value;
        const nombre = buscarNombre.value;
        const params = new URLSearchParams();
        if (dni) params.append('dni', dni);
        if (nombre) params.append('nombre', nombre);

        clientesData = await api(`${API_CLIENTES_URL}?${params.toString()}`);
        renderizarTabla();
    }

    // --- EVENT LISTENERS PRINCIPALES ---
    btnBuscar.addEventListener('click', cargarClientes);
    
    btnLimpiar.addEventListener('click', () => {
        buscarDNI.value = '';
        buscarNombre.value = '';
        cargarClientes();
    });

    formAgregar.addEventListener('submit', async (e) => {
        e.preventDefault();
        const result = await api(API_CLIENTES_URL, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(Object.fromEntries(new FormData(e.target)))
        });
        if (result && result.id) {
            modalAgregar.hide();
            formAgregar.reset();
            cargarClientes();
        }
    });

    formEditar.addEventListener('submit', async (e) => {
        e.preventDefault();
        const result = await api(API_CLIENTES_URL, {
            method: 'PUT',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(Object.fromEntries(new FormData(e.target)))
        });
        if (result) {
            modalEditar.hide();
            cargarClientes();
        }
    });

    tbodyClientes.addEventListener('click', (e) => {
        const button = e.target.closest('button');
        if (!button) return;

        const tr = button.closest('tr');
        const cliente = JSON.parse(tr.dataset.cliente);

        if (button.classList.contains('btn-historial')) {
            filtroHistorial.value = ''; 
            cargarHistorial(cliente.id, cliente.nombre);
        }

        if (button.classList.contains('btn-edit')) {
            formEditar.querySelector('[name="id"]').value = cliente.id;
            formEditar.querySelector('[name="nombre"]').value = cliente.nombre;
            formEditar.querySelector('[name="dni_ruc"]').value = cliente.dni_ruc;
            formEditar.querySelector('[name="telefono"]').value = cliente.telefono;
            formEditar.querySelector('[name="email"]').value = cliente.email;
            modalEditar.show();
        }

        if (button.classList.contains('btn-eliminar')) {
            clienteIdParaEliminar = button.dataset.id;
            modalEliminar.show();
        }
    });

    btnConfirmarEliminar.addEventListener('click', async () => {
        if (clienteIdParaEliminar) {
            const result = await api(`${API_CLIENTES_URL}?id=${clienteIdParaEliminar}`, { method: 'DELETE' });
            if (result) {
                modalEliminar.hide();
                cargarClientes();
            }
            clienteIdParaEliminar = null;
        }
    });

    cargarClientes();
});