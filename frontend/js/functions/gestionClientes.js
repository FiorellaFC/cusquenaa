document.addEventListener('DOMContentLoaded', () => {
    console.log("Script Gestión Clientes v4 Cargado");

    const API_CLIENTES_URL = '../../backend/api/controllers/vista_clientes/gestionClientes.php';
    const API_CITAS_URL = '../../backend/api/controllers/vista_citas/gestionCitasAdmin.php';

    // DOM References
    const tbodyClientes = document.querySelector('#tblClientes tbody');
    const paginationControls = document.getElementById('paginationControls');
    const buscarDNI = document.getElementById('buscarDNI');
    const buscarNombre = document.getElementById('buscarNombre');
    const btnBuscar = document.getElementById('btnBuscar');
    const btnLimpiar = document.getElementById('btnLimpiar');

    // Modales CRUD
    const modalAgregar = new bootstrap.Modal(document.getElementById('modalAgregarCliente'));
    const formAgregar = document.getElementById('formAgregarCliente');
    const modalEditar = new bootstrap.Modal(document.getElementById('modalEditarCliente'));
    const formEditar = document.getElementById('formEditarCliente');
    const modalEliminar = new bootstrap.Modal(document.getElementById('modalEliminarCliente'));
    const btnConfirmarEliminar = document.getElementById('btnConfirmarEliminar');

    // Modal Historial
    const modalHistorial = new bootstrap.Modal(document.getElementById('modalHistorialCliente'));
    const tbodyHistorial = document.getElementById('tbodyHistorial');
    const historialNombreCliente = document.getElementById('historialNombreCliente');
    const filtroHistorial = document.getElementById('filtroHistorial');
    const paginacionHistorial = document.getElementById('paginacionHistorial');
    const btnLimpiarHistorial = document.getElementById('btnLimpiarHistorial');

    // Modal Detalle Servicios
    const modalDetalleEl = document.getElementById('modalDetalleServicios');
    const modalDetalle = modalDetalleEl ? new bootstrap.Modal(modalDetalleEl) : null;

    let clientesData = []; 
    let clienteIdParaEliminar = null;
    let currentPage = 1; 

    // Variables Historial
    let historialActual = [];
    let historialPaginacionCache = null; 
    let currentClienteIdHistorial = null;
    let currentClienteNombreHistorial = '';

    async function api(url, options = {}) {
        try {
            const response = await fetch(url, options);
            if (!response.ok) throw new Error(`Error: ${response.statusText}`);
            return response.status === 204 ? { success: true } : await response.json();
        } catch (error) { console.error(error); alert('Error: ' + error.message); return null; }
    }

    // ======================================================
    // GESTIÓN CLIENTES
    // ======================================================
    async function cargarClientes(page = 1) {
        currentPage = page;
        const dni = buscarDNI.value;
        const nombre = buscarNombre.value;
        
        const params = new URLSearchParams();
        params.append('page', page);
        if (dni) params.append('dni', dni);
        if (nombre) params.append('nombre', nombre);

        const result = await api(`${API_CLIENTES_URL}?${params.toString()}`);
        if (result && result.data) {
            clientesData = result.data;
            renderizarTabla();
            renderizarPaginacion(result.pagination);
        }
    }

    function renderizarTabla() {
        if(!tbodyClientes) return;
        tbodyClientes.innerHTML = '';
        if (clientesData.length > 0) {
            clientesData.forEach(cliente => {
                const tr = document.createElement('tr');
                tr.dataset.cliente = JSON.stringify(cliente);
                
                let iconUser = '<i class="fas fa-user text-secondary" title="Invitado"></i>';
                if (cliente.tiene_cuenta == 1) iconUser = '<i class="fas fa-user-check text-success" title="Registrado"></i>';

                tr.innerHTML = `
                    <td>${iconUser}</td>
                    <td>${cliente.nombre}</td>
                    <td>${cliente.apellido || ''}</td>
                    <td>${cliente.dni_ruc || '-'}</td>
                    <td>${cliente.telefono || '-'}</td>
                    <td>${cliente.email || '-'}</td>
                    <td>
                        <button class="btn btn-sm btn-info text-white btn-historial me-1" data-id="${cliente.id}"><i class="fas fa-history"></i></button>
                        <button class="btn btn-sm btn-warning text-white btn-edit me-1"><i class="fas fa-pencil-alt"></i></button>
                        <button class="btn btn-sm btn-danger btn-eliminar" data-id="${cliente.id}"><i class="fas fa-trash-alt"></i></button>
                    </td>
                `;
                tbodyClientes.appendChild(tr);
            });
        } else {
            tbodyClientes.innerHTML = '<tr><td colspan="7" class="text-center py-4 text-muted">No se encontraron clientes.</td></tr>';
        }
    }

    function renderizarPaginacion(pg) {
        if(!paginationControls) return;
        paginationControls.innerHTML = '';
        if (pg.total_pages <= 1) return;

        const prev = pg.current_page === 1 ? 'disabled' : '';
        const next = pg.current_page === pg.total_pages ? 'disabled' : '';

        paginationControls.innerHTML += `<li class="page-item ${prev}"><a class="page-link" href="#" onclick="window.cambiarPagina(${pg.current_page - 1})">Anterior</a></li>`;
        for(let i=1; i<=pg.total_pages; i++){
            const active = i === pg.current_page ? 'active' : '';
            paginationControls.innerHTML += `<li class="page-item ${active}"><a class="page-link" href="#" onclick="window.cambiarPagina(${i})">${i}</a></li>`;
        }
        paginationControls.innerHTML += `<li class="page-item ${next}"><a class="page-link" href="#" onclick="window.cambiarPagina(${pg.current_page + 1})">Siguiente</a></li>`;
    }
    window.cambiarPagina = (p) => { if(p>0) cargarClientes(p); };

    // ======================================================
    // HISTORIAL (CON SERVICIOS MÚLTIPLES)
    // ======================================================
    async function cargarHistorial(clienteId, nombre, page = 1) {
        currentClienteIdHistorial = clienteId;
        currentClienteNombreHistorial = nombre;
        historialNombreCliente.textContent = nombre;
        tbodyHistorial.innerHTML = '<tr><td colspan="5" class="text-center py-3"><div class="spinner-border text-primary"></div></td></tr>';
        paginacionHistorial.innerHTML = ''; 
        filtroHistorial.value = ''; 

        if (page === 1) modalHistorial.show();

        const result = await api(`${API_CITAS_URL}?cliente_id=${clienteId}&page=${page}`);
        
        if (result && result.data) {
            historialActual = result.data;
            historialPaginacionCache = result.pagination;
            renderizarHistorial(historialActual);
            renderizarPaginacionHistorial(historialPaginacionCache);
        } else {
            tbodyHistorial.innerHTML = '<tr><td colspan="5" class="text-center text-muted py-3">Sin historial.</td></tr>';
        }
    }

    function renderizarHistorial(lista) {
        tbodyHistorial.innerHTML = '';
        if (lista.length > 0) {
            lista.forEach(cita => {
                let bg = 'bg-secondary';
                if(cita.estado === 'confirmada') bg = 'bg-success';
                else if(cita.estado === 'completada') bg = 'bg-primary';
                else if(cita.estado === 'cancelada') bg = 'bg-danger';
                else if(cita.estado === 'pendiente') bg = 'bg-warning text-dark';

                const precio = cita.servicio_precio ? `S/. ${parseFloat(cita.servicio_precio).toFixed(2)}` : '-';

                // LÓGICA SERVICIOS MÚLTIPLES
                let servicioDisplay = cita.servicio_solicitado || 'No especificado';
                const serviciosArray = servicioDisplay.split(', ');
                
                if (serviciosArray.length > 1) {
                    servicioDisplay = `
                        <button class="btn btn-sm btn-outline-info fw-bold btn-ver-servicios" 
                                data-servicios="${servicioDisplay}"
                                title="Ver todos">
                            <i class="fas fa-list-ul me-1"></i> Ver (${serviciosArray.length})
                        </button>
                    `;
                } else {
                    servicioDisplay = `<span class="fw-bold text-dark small">${servicioDisplay}</span>`;
                }

                const tr = document.createElement('tr');
                tr.innerHTML = `
                    <td class="fw-bold">${cita.fecha}</td>
                    <td>${cita.hora.substring(0,5)}</td>
                    <td class="text-center">${servicioDisplay}</td>
                    <td class="fw-bold text-success">${precio}</td>
                    <td><span class="badge ${bg} px-3">${cita.estado.toUpperCase()}</span></td>
                `;
                tbodyHistorial.appendChild(tr);
            });
        } else {
            tbodyHistorial.innerHTML = '<tr><td colspan="5" class="text-center text-muted small">No se encontraron datos.</td></tr>';
        }
    }

    // LISTENER PARA VER DETALLES DE SERVICIOS EN HISTORIAL
    tbodyHistorial.addEventListener('click', (e) => {
        const btnServicios = e.target.closest('.btn-ver-servicios');
        if (btnServicios) {
            const listaTexto = btnServicios.dataset.servicios;
            const listaHTML = listaTexto.split(', ').map(s => `<li class="list-group-item px-0 py-1 border-0"><i class="fas fa-check text-success me-2 small"></i>${s}</li>`).join('');
            
            document.getElementById('listaServiciosDetalle').innerHTML = `<ul class="list-group list-group-flush text-start small">${listaHTML}</ul>`;
            if(modalDetalle) modalDetalle.show();
        }
    });

    // BUSCADOR HISTORIAL
    filtroHistorial.addEventListener('input', (e) => {
        const termino = e.target.value.toLowerCase().trim();
        if (termino === '') {
            renderizarHistorial(historialActual);
            if (historialPaginacionCache) renderizarPaginacionHistorial(historialPaginacionCache);
        } else {
            const filtrados = historialActual.filter(c => 
                `${c.fecha} ${c.hora} ${c.servicio_solicitado} ${c.estado}`.toLowerCase().includes(termino)
            );
            renderizarHistorial(filtrados);
            paginacionHistorial.innerHTML = ''; 
        }
    });

    if(btnLimpiarHistorial){
        btnLimpiarHistorial.addEventListener('click', () => {
            filtroHistorial.value = '';
            filtroHistorial.dispatchEvent(new Event('input'));
        });
    }

    function renderizarPaginacionHistorial(pg) {
        if(!paginacionHistorial) return;
        paginacionHistorial.innerHTML = '';
        if (pg.total_pages <= 1) return;
        
        const prev = pg.current_page === 1 ? 'disabled' : '';
        const next = pg.current_page === pg.total_pages ? 'disabled' : '';

        paginacionHistorial.innerHTML += `<li class="page-item ${prev}"><a class="page-link" href="#" onclick="window.cambiarPaginaHistorial(${pg.current_page - 1})">&laquo;</a></li>`;
        for(let i=1; i<=pg.total_pages; i++){
            const active = i === pg.current_page ? 'active' : '';
            paginacionHistorial.innerHTML += `<li class="page-item ${active}"><a class="page-link" href="#" onclick="window.cambiarPaginaHistorial(${i})">${i}</a></li>`;
        }
        paginacionHistorial.innerHTML += `<li class="page-item ${next}"><a class="page-link" href="#" onclick="window.cambiarPaginaHistorial(${pg.current_page + 1})">&raquo;</a></li>`;
    }
    window.cambiarPaginaHistorial = (p) => { if(p>0) cargarHistorial(currentClienteIdHistorial, currentClienteNombreHistorial, p); };

    // EVENT LISTENERS CRUD
    btnBuscar.addEventListener('click', () => cargarClientes(1));
    btnLimpiar.addEventListener('click', () => { buscarDNI.value=''; buscarNombre.value=''; cargarClientes(1); });

    formAgregar.addEventListener('submit', async (e) => {
        e.preventDefault();
        const res = await api(API_CLIENTES_URL, { method:'POST', headers:{'Content-Type':'application/json'}, body:JSON.stringify(Object.fromEntries(new FormData(e.target))) });
        if(res && res.id) { modalAgregar.hide(); formAgregar.reset(); cargarClientes(1); }
    });
    formEditar.addEventListener('submit', async (e) => {
        e.preventDefault();
        const res = await api(API_CLIENTES_URL, { method:'PUT', headers:{'Content-Type':'application/json'}, body:JSON.stringify(Object.fromEntries(new FormData(e.target))) });
        if(res) { modalEditar.hide(); cargarClientes(currentPage); }
    });
    tbodyClientes.addEventListener('click', (e) => {
        const btn = e.target.closest('button');
        if(!btn) return;
        const tr = btn.closest('tr');
        const cli = JSON.parse(tr.dataset.cliente);
        if(btn.classList.contains('btn-historial')) cargarHistorial(cli.id, `${cli.nombre} ${cli.apellido||''}`, 1);
        if(btn.classList.contains('btn-edit')) {
            const f = formEditar;
            f.id.value = cli.id; f.nombre.value = cli.nombre; f.apellido.value = cli.apellido||'';
            f.dni_ruc.value = cli.dni_ruc; f.telefono.value = cli.telefono; f.email.value = cli.email;
            modalEditar.show();
        }
        if(btn.classList.contains('btn-eliminar')) { clienteIdParaEliminar = btn.dataset.id; modalEliminar.show(); }
    });
    btnConfirmarEliminar.addEventListener('click', async () => {
        if(clienteIdParaEliminar) {
            const res = await api(`${API_CLIENTES_URL}?id=${clienteIdParaEliminar}`, { method:'DELETE' });
            if(res) { modalEliminar.hide(); cargarClientes(currentPage); }
            clienteIdParaEliminar = null;
        }
    });

    cargarClientes();
});