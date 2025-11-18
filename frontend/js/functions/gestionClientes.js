document.addEventListener('DOMContentLoaded', () => {
    console.log("Script de gestión de clientes cargado.");

    // URL de la API (usando la ruta que me confirmaste)
    const API_URL = 'http://localhost/cusquena/backend/api/controllers/gestionClientes.php';

    // --- REFERENCIAS AL DOM ---
    const tbodyClientes = document.querySelector('#tblClientes tbody');
    const buscarDNI = document.getElementById('buscarDNI');
    const buscarNombre = document.getElementById('buscarNombre');
    const btnBuscar = document.getElementById('btnBuscar');
    const btnLimpiar = document.getElementById('btnLimpiar');

    // Modales
    const modalAgregar = new bootstrap.Modal(document.getElementById('modalAgregarCliente'));
    const formAgregar = document.getElementById('formAgregarCliente');
    const modalEditar = new bootstrap.Modal(document.getElementById('modalEditarCliente'));
    const formEditar = document.getElementById('formEditarCliente');
    const modalEliminar = new bootstrap.Modal(document.getElementById('modalEliminarCliente'));
    const btnConfirmarEliminar = document.getElementById('btnConfirmarEliminar');

    let clientesData = []; 
    let clienteIdParaEliminar = null; 

    // --- FUNCIÓN GENÉRICA DE API ---
    async function api(url, options = {}) {
        try {
            const response = await fetch(url, options);
            if (!response.ok) {
                const errorData = await response.json();
                throw new Error(errorData.error || `Error: ${response.statusText}`);
            }
            return response.status === 204 ? { success: true } : await response.json();
        } catch (error) {
            console.error("Error en la API:", error);
            alert('Error: ' + error.message);
            return null;
        }
    }

    // --- LÓGICA DE RENDERIZADO Y CARGA ---
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
                        <button class="btn btn-sm btn-warning text-white btn-edit" title="Editar Cliente">
                            <i class="fas fa-pencil-alt"></i>
                        </button>
                        <button class="btn btn-sm btn-danger btn-eliminar" data-id="${cliente.id}" title="Eliminar Cliente">
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

    async function cargarClientes() {
        const dni = buscarDNI.value;
        const nombre = buscarNombre.value;

        const params = new URLSearchParams();
        if (dni) params.append('dni', dni);
        if (nombre) params.append('nombre', nombre);

        clientesData = await api(`${API_URL}?${params.toString()}`);
        renderizarTabla();
    }

    // --- MANEJADORES DE EVENTOS ---
    btnBuscar.addEventListener('click', cargarClientes);
    
    btnLimpiar.addEventListener('click', () => {
        buscarDNI.value = '';
        buscarNombre.value = '';
        cargarClientes();
    });

    formAgregar.addEventListener('submit', async (e) => {
        e.preventDefault();
        const formData = Object.fromEntries(new FormData(e.target));
        
        const result = await api(API_URL, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(formData)
        });
        if (result && result.id) {
            modalAgregar.hide();
            formAgregar.reset();
            cargarClientes();
        }
    });

    formEditar.addEventListener('submit', async (e) => {
        e.preventDefault();
        const formData = Object.fromEntries(new FormData(e.target));
        
        const result = await api(API_URL, {
            method: 'PUT',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(formData)
        });
        if (result) {
            modalEditar.hide();
            cargarClientes();
        }
    });

    tbodyClientes.addEventListener('click', (e) => {
        const button = e.target.closest('button');
        if (!button) return;

        if (button.classList.contains('btn-edit')) {
            const tr = button.closest('tr');
            const cliente = JSON.parse(tr.dataset.cliente);
            
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
            const result = await api(`${API_URL}?id=${clienteIdParaEliminar}`, { method: 'DELETE' });
            if (result) {
                modalEliminar.hide();
                cargarClientes();
            }
            clienteIdParaEliminar = null;
        }
    });

    // --- INICIALIZACIÓN ---
    cargarClientes();
});