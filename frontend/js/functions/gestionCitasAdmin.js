document.addEventListener('DOMContentLoaded', () => {
    console.log("Script de gestión de citas admin cargado.");

    const API_URL = 'http://localhost/cusquena/backend/api/controllers/gestionCitasAdmin.php';

    // --- REFERENCIAS AL DOM ---
    const tbodyCitas = document.querySelector('#tblCitas tbody');
    const buscarFecha = document.getElementById('buscarFecha');
    const buscarTelefono = document.getElementById('buscarTelefono');
    const buscarNombre = document.getElementById('buscarNombre');
    const buscarDNI = document.getElementById('buscarDNI'); // --- AÑADIDO ---
    const buscarEstado = document.getElementById('buscarEstado');
    const btnBuscar = document.getElementById('btnBuscar');
    const btnLimpiar = document.getElementById('btnLimpiar');

    // Modales
    const modalEditar = new bootstrap.Modal(document.getElementById('modalEditarCita'));
    const formEditar = document.getElementById('formEditarCita');
    const modalCancelar = new bootstrap.Modal(document.getElementById('modalCancelar'));
    const btnConfirmarCancelar = document.getElementById('btnConfirmarCancelar');

    let citasData = []; 
    let citaIdParaCancelar = null; 

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

    function renderizarTabla() {
        if (!tbodyCitas) return;
        tbodyCitas.innerHTML = '';

        if (citasData && citasData.length > 0) {
            citasData.forEach(cita => {
                const tr = document.createElement('tr');
                tr.dataset.cita = JSON.stringify(cita); 

                let badgeClass = '';
                switch(cita.estado) {
                    case 'confirmada': badgeClass = 'badge-confirmada'; break;
                    case 'cancelada': badgeClass = 'badge-cancelada'; break;
                    case 'completada': badgeClass = 'badge-completada'; break;
                    default: badgeClass = 'bg-secondary';
                }

                // --- HTML DE LA TABLA ACTUALIZADO ---
                tr.innerHTML = `
                    <td>${cita.nombre_cliente}</td>
                    <td>${cita.dni_ruc || 'N/A'}</td> <!-- DNI AÑADIDO -->
                    <td>${cita.telefono_cliente || 'N/A'}</td>
                    <td>${cita.fecha}</td>
                    <td>${cita.hora.substring(0, 5)}</td>
                    <td>${cita.servicio_solicitado || 'N/A'}</td>
                    <td><span class="badge ${badgeClass}">${cita.estado}</span></td>
                    <td>
                        <button class="btn btn-sm btn-warning text-white btn-edit" title="Editar Cita">
                            <i class="fas fa-pencil-alt"></i>
                        </button>
                        <button class="btn btn-sm btn-danger btn-cancelar" data-id="${cita.id}" title="Cancelar Cita">
                            <i class="fas fa-ban"></i>
                        </button>
                    </td>
                `;
                tbodyCitas.appendChild(tr);
            });
        } else {
            // --- COLSPAN ACTUALIZADO ---
            tbodyCitas.innerHTML = '<tr><td colspan="8" class="text-center">No se encontraron citas con esos filtros.</td></tr>';
        }
    }

    async function cargarCitas() {
        const fecha = buscarFecha.value;
        const telefono = buscarTelefono.value;
        const nombre = buscarNombre.value;
        const dni = buscarDNI.value; // --- AÑADIDO ---
        const estado = buscarEstado.value;

        const params = new URLSearchParams();
        if (fecha) params.append('fecha', fecha);
        if (telefono) params.append('telefono', telefono);
        if (nombre) params.append('nombre', nombre);
        if (dni) params.append('dni', dni); // --- AÑADIDO ---
        if (estado) params.append('estado', estado);

        citasData = await api(`${API_URL}?${params.toString()}`);
        renderizarTabla();
    }

    btnBuscar.addEventListener('click', cargarCitas);
    
    btnLimpiar.addEventListener('click', () => {
        buscarFecha.value = '';
        buscarTelefono.value = '';
        buscarNombre.value = '';
        buscarDNI.value = ''; // --- AÑADIDO ---
        buscarEstado.value = 'todas';
        cargarCitas();
    });

    formEditar.addEventListener('submit', async (e) => {
        e.preventDefault();
        const formData = Object.fromEntries(new FormData(e.target));
        formData.hora = `${formData.hora}:00`; 

        const result = await api(API_URL, {
            method: 'PUT',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(formData)
        });
        if (result) {
            modalEditar.hide();
            cargarCitas();
        }
    });

    tbodyCitas.addEventListener('click', (e) => {
        const button = e.target.closest('button');
        if (!button) return;

        if (button.classList.contains('btn-edit')) {
            const tr = button.closest('tr');
            const cita = JSON.parse(tr.dataset.cita);
            
            // --- POBLAR MODAL DE EDICIÓN (CON DNI) ---
            formEditar.querySelector('[name="id"]').value = cita.id;
            formEditar.querySelector('[name="dni_ruc"]').value = cita.dni_ruc || 'N/A'; // --- AÑADIDO ---
            formEditar.querySelector('[name="nombre_cliente"]').value = cita.nombre_cliente;
            formEditar.querySelector('[name="telefono_cliente"]').value = cita.telefono_cliente;
            formEditar.querySelector('[name="fecha"]').value = cita.fecha;
            formEditar.querySelector('[name="hora"]').value = cita.hora.substring(0, 5);
            formEditar.querySelector('[name="servicio_solicitado"]').value = cita.servicio_solicitado;
            formEditar.querySelector('[name="estado"]').value = cita.estado;
            modalEditar.show();
        }

        if (button.classList.contains('btn-cancelar')) {
            citaIdParaCancelar = button.dataset.id;
            modalCancelar.show();
        }
    });

    btnConfirmarCancelar.addEventListener('click', async () => {
        if (citaIdParaCancelar) {
            const result = await api(`${API_URL}?id=${citaIdParaCancelar}`, { method: 'DELETE' });
            if (result) {
                modalCancelar.hide();
                cargarCitas();
            }
            citaIdParaCancelar = null;
        }
    });

    cargarCitas();
});