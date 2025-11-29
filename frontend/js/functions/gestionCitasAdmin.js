document.addEventListener('DOMContentLoaded', () => {
    console.log("Gestión Citas Admin v6 - Final");

    const API_URL = '../../backend/api/controllers/vista_citas/gestionCitasAdmin.php';
    const API_SERVICIOS = '../../backend/api/controllers/vista_reservas/gestionCitas.php?accion=obtener_servicios'; 

    // DOM
    const tbodyCitas = document.querySelector('#tblCitas tbody');
    const paginationControls = document.getElementById('paginationControls');
    
    // Filtros
    const buscarFecha = document.getElementById('buscarFecha');
    const buscarEstado = document.getElementById('buscarEstado');
    const buscarDNI = document.getElementById('buscarDNI');
    const btnBuscar = document.getElementById('btnBuscar');
    const btnLimpiar = document.getElementById('btnLimpiar');

    // Modales
    const modalEditarEl = document.getElementById('modalEditarCita');
    const modalEditar = new bootstrap.Modal(modalEditarEl);
    const formEditar = document.getElementById('formEditarCita');
    const containerServicios = document.getElementById('containerServiciosEditar');
    const inputPrecio = document.getElementById('inputPrecioFinal');
    
    const modalCancelarEl = document.getElementById('modalCancelar');
    const modalCancelar = new bootstrap.Modal(modalCancelarEl);
    const btnConfirmarCancelar = document.getElementById('btnConfirmarCancelar');

    // Modal Detalle
    const modalDetalleEl = document.getElementById('modalDetalleServicios');
    const modalDetalle = modalDetalleEl ? new bootstrap.Modal(modalDetalleEl) : null;

    let citasData = [];
    let citaIdParaCancelar = null;
    let listaServiciosGlobal = []; 

    // 1. CARGAR SERVICIOS (CACHE)
    async function cargarServiciosGlobal() {
        try {
            const [resM, resL] = await Promise.all([
                fetch(API_SERVICIOS + '&tipo=Mantenimiento'),
                fetch(API_SERVICIOS + '&tipo=Lavado')
            ]);
            const sM = await resM.json();
            const sL = await resL.json();
            listaServiciosGlobal = [...sM, ...sL];
        } catch (e) { console.error("Error servicios", e); }
    }

    // 2. CARGAR CITAS
    async function cargarCitas(page = 1) {
        const params = new URLSearchParams();
        params.append('page', page);
        
        if(buscarFecha.value) params.append('fecha', buscarFecha.value);
        if(buscarEstado.value && buscarEstado.value !== 'todas') params.append('estado', buscarEstado.value);
        if(buscarDNI.value.trim()) params.append('dni', buscarDNI.value.trim());

        try {
            const response = await fetch(`${API_URL}?${params}`);
            const result = await response.json();
            
            if (result.data) {
                citasData = result.data;
                renderizarTabla();
                renderizarPaginacion(result.pagination);
            }
        } catch (error) {
            console.error(error);
            tbodyCitas.innerHTML = '<tr><td colspan="10" class="text-danger">Error de conexión</td></tr>';
        }
    }

    function renderizarTabla() {
        tbodyCitas.innerHTML = '';
        if (citasData.length > 0) {
            citasData.forEach(cita => {
                let badge = 'bg-secondary';
                if(cita.estado === 'confirmada') badge = 'bg-success';
                else if(cita.estado === 'completada') badge = 'bg-primary';
                else if(cita.estado === 'cancelada') badge = 'bg-danger';
                else if(cita.estado === 'pendiente') badge = 'bg-warning text-dark';

                const precio = `S/. ${parseFloat(cita.precio_mostrar).toFixed(2)}`;

                // LÓGICA DE SERVICIOS MÚLTIPLES
                let servicioDisplay = cita.servicio_solicitado || 'No especificado';
                const serviciosArray = servicioDisplay.split(', ');
                
                if (serviciosArray.length > 1) {
                    // Botón azul si hay muchos
                    servicioDisplay = `
                        <button class="btn btn-sm btn-outline-info fw-bold btn-ver-servicios" 
                                data-servicios="${servicioDisplay}"
                                type="button"
                                title="Ver todos">
                            <i class="fas fa-list-ul me-1"></i> Ver (${serviciosArray.length})
                        </button>
                    `;
                } else {
                    servicioDisplay = `<span class="fw-bold text-dark small">${servicioDisplay}</span>`;
                }

                const tr = document.createElement('tr');
                tr.dataset.cita = JSON.stringify(cita);
                tr.innerHTML = `
                    <td>${cita.nombre_cliente}</td>
                    <td>${cita.apellido_cliente || ''}</td>
                    <td>${cita.dni_ruc || '-'}</td>
                    <td>${cita.telefono_cliente || '-'}</td>
                    <td>${cita.fecha}</td>
                    <td>${cita.hora.substring(0,5)}</td>
                    <td>${servicioDisplay}</td>
                    <td class="fw-bold text-success">${precio}</td>
                    <td><span class="badge ${badge}">${cita.estado.toUpperCase()}</span></td>
                    <td>
                        <button class="btn btn-sm btn-warning text-white btn-edit" title="Editar"><i class="fas fa-pencil-alt"></i></button>
                        <button class="btn btn-sm btn-danger btn-cancelar" data-id="${cita.id}" title="Cancelar"><i class="fas fa-ban"></i></button>
                    </td>
                `;
                tbodyCitas.appendChild(tr);
            });
        } else {
            tbodyCitas.innerHTML = '<tr><td colspan="10" class="text-center py-4 text-muted">No se encontraron citas.</td></tr>';
        }
    }

    // 3. RENDERIZAR CHECKBOXES EN MODAL EDITAR
    function renderizarCheckboxesEditar(idsSeleccionados) {
        containerServicios.innerHTML = '';
        
        listaServiciosGlobal.forEach(s => {
            // Verificar si el ID está en la lista de la cita
            const isChecked = idsSeleccionados.includes(String(s.id)) ? 'checked' : '';
            
            const div = document.createElement('div');
            div.className = 'form-check form-check-sm mb-1 border-bottom pb-1';
            div.innerHTML = `
                <input class="form-check-input chk-servicio-edit" type="checkbox" value="${s.id}" id="ed-s-${s.id}" data-precio="${s.precio}" ${isChecked}>
                <label class="form-check-label w-100 d-flex justify-content-between ps-1" for="ed-s-${s.id}" style="cursor:pointer">
                    <span class="small">${s.nombre}</span>
                    <span class="text-muted small fw-bold">S/. ${parseFloat(s.precio).toFixed(2)}</span>
                </label>
            `;
            containerServicios.appendChild(div);
        });
        
        // Listener para recalcular precio al marcar/desmarcar
        containerServicios.querySelectorAll('input').forEach(chk => {
            chk.addEventListener('change', recalcularPrecioTotal);
        });
    }

    function recalcularPrecioTotal() {
        let total = 0;
        containerServicios.querySelectorAll('.chk-servicio-edit:checked').forEach(chk => {
            total += parseFloat(chk.dataset.precio);
        });
        inputPrecio.value = total.toFixed(2);
    }

    // LISTENERS TABLA
    tbodyCitas.addEventListener('click', (e) => {
        // A. VER DETALLE SERVICIOS
        const btnServ = e.target.closest('.btn-ver-servicios');
        if (btnServ) {
            e.stopPropagation(); 
            const lista = btnServ.dataset.servicios.split(', ').map(s => `<li class="list-group-item small py-1 border-0"><i class="fas fa-check text-success me-2"></i>${s}</li>`).join('');
            document.getElementById('listaServiciosDetalle').innerHTML = `<ul class="list-group list-group-flush">${lista}</ul>`;
            modalDetalle.show();
            return;
        }

        // B. EDITAR
        const btnEdit = e.target.closest('.btn-edit');
        if(btnEdit) {
            const cita = JSON.parse(btnEdit.closest('tr').dataset.cita);
            const f = formEditar;
            
            f.id.value = cita.id;
            f.nombre_cliente.value = cita.nombre_cliente;
            f.apellido_cliente.value = cita.apellido_cliente;
            f.dni_ruc.value = cita.dni_ruc || '';
            f.telefono_cliente.value = cita.telefono_cliente;
            f.fecha.value = cita.fecha;
            f.hora.value = cita.hora.substring(0,5);
            f.estado.value = cita.estado;
            
            // Cargar Precio
            f.precio_final.value = parseFloat(cita.precio_mostrar).toFixed(2);

            // Cargar Servicios (IDs)
            const ids = cita.servicios_ids ? String(cita.servicios_ids).split(',') : [];
            renderizarCheckboxesEditar(ids);

            modalEditar.show();
        }

        // C. CANCELAR
        if(e.target.closest('.btn-cancelar')) {
            citaIdParaCancelar = e.target.closest('.btn-cancelar').dataset.id;
            modalCancelar.show();
        }
    });

    // GUARDAR EDICIÓN (PUT)
    formEditar.addEventListener('submit', async (e) => {
        e.preventDefault();
        
        // Recolectar IDs de servicios marcados
        const servicios = [];
        containerServicios.querySelectorAll('.chk-servicio-edit:checked').forEach(chk => servicios.push(chk.value));
        
        const formData = new FormData(e.target);
        const payload = Object.fromEntries(formData);
        payload.servicios = servicios; // Añadir array al JSON
        if(payload.hora.length === 5) payload.hora += ':00';

        try {
            const res = await fetch(API_URL, {
                method: 'PUT',
                headers: {'Content-Type': 'application/json'},
                body: JSON.stringify(payload)
            });
            const result = await res.json();
            
            if (result.message) {
                modalEditar.hide();
                Swal.fire({ icon: 'success', title: '¡Actualizado!', text: 'La cita se modificó correctamente.', timer: 1500, showConfirmButton: false });
                cargarCitas(1);
            } else {
                Swal.fire({ icon: 'error', title: 'Error', text: result.error });
            }
        } catch (err) { console.error(err); alert("Error de conexión"); }
    });

    // ELIMINAR (DELETE)
    btnConfirmarCancelar.addEventListener('click', async () => {
        if (!citaIdParaCancelar) return;
        try {
            const res = await fetch(`${API_URL}?id=${citaIdParaCancelar}`, { method: 'DELETE' });
            const result = await res.json();
            if (result.message) {
                modalCancelar.hide();
                cargarCitas(1);
            } else {
                alert(result.error);
            }
        } catch (err) { console.error(err); }
    });

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
    window.cambiarPagina = (p) => { if(p>0) cargarCitas(p); };

    btnBuscar.addEventListener('click', () => cargarCitas(1));
    btnLimpiar.addEventListener('click', () => {
        buscarFecha.value = ''; buscarEstado.value = 'todas'; buscarDNI.value = '';
        cargarCitas(1); 
    });

    // INICIO
    cargarServiciosGlobal().then(() => cargarCitas());
});