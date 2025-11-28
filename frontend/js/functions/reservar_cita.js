document.addEventListener('DOMContentLoaded', () => {
    const API_URL = "http://localhost/cusquena/backend/api/controllers/vista_reservas/gestionCitas.php"; 
    const TIEMPO_BLOQUEO = 300; 

    // REFERENCIAS DOM
    const fechaInput = document.getElementById('fecha');
    const contenedorHorarios = document.getElementById('contenedorHorarios');
    const listaHorarios = document.getElementById('listaHorarios');
    const paso2Formulario = document.getElementById('paso2-datos-cliente');
    const cronometroContainer = document.getElementById('cronometro-container');
    const cronometroDisplay = document.getElementById('cronometro');
    const formConfirmarCita = document.getElementById('formConfirmarCita');
    const loadingOverlay = document.getElementById('loading-overlay'); // <-- NUEVA REFERENCIA
    const sessionIdInput = document.querySelector('[name="session_id"]');

    // Nuevas funciones para el overlay
function showLoading() {
    if(loadingOverlay) {
        loadingOverlay.style.display = 'flex'; // Usar 'flex' para centrar
    }
}

function hideLoading() {
    if(loadingOverlay) {
        loadingOverlay.style.display = 'none';
    }
}
    
    // NUEVO: Referencias para servicios y precios
    const selectServicios = document.querySelector('[name="servicio_solicitado"]');
    const TIPO_SERVICIO = document.body.getAttribute('data-tipo-servicio') || 'Mantenimiento';

    let SESSION_ID = sessionIdInput ? sessionIdInput.value : null;
    let cronometroIntervalo;
    let tiempoRestante = TIEMPO_BLOQUEO;
    let horarioSeleccionado = null; 
    let datosSemanaCache = []; 

    // 1. CARGAR DATOS INICIALES (HORARIOS Y SERVICIOS)
    async function cargarDatosIniciales() {
        const hoy = new Date().toISOString().split("T")[0];
        if(fechaInput) fechaInput.min = hoy;

        try {
            // Cargar Horarios
            const response = await fetch(API_URL, { method: 'GET' });
            if (!response.ok) throw new Error('Error API Horarios');
            datosSemanaCache = await response.json();
            
            // Cargar Servicios (NUEVO)
            await cargarServicios();
            
            console.log("Sistema iniciado correctamente.");
        } catch (error) {
            console.error(error);
            alert("Error de conexión con el servidor.");
        }
    }

    // --- FUNCIÓN PARA CARGAR SERVICIOS DESDE BD ---
    async function cargarServicios() {
        if (!selectServicios) return;
        try {
            const url = `${API_URL}?accion=obtener_servicios&tipo=${TIPO_SERVICIO}`;
            const response = await fetch(url);
            const servicios = await response.json();

            selectServicios.innerHTML = '<option value="" data-precio="0">Seleccione un servicio</option>';

            if(Array.isArray(servicios)) {
                servicios.forEach(s => {
                const option = document.createElement('option');
                option.value = s.id;               
                option.textContent = s.nombre;
                option.dataset.precio = s.precio; 
                selectServicios.appendChild(option);
            });
            }
        } catch (e) {
            console.error("Error cargando servicios:", e);
            selectServicios.innerHTML = '<option value="">Error cargando lista</option>';
        }
    }

    // --- EVENTO: MOSTRAR PRECIO AL CAMBIAR SELECT ---
    if(selectServicios) {
        selectServicios.addEventListener('change', function() {
            const precio = this.options[this.selectedIndex].dataset.precio;
            const divPrecio = document.getElementById('precio-estimado');
            
            if (divPrecio) {
                if (precio > 0) {
                    divPrecio.style.display = 'block';
                    divPrecio.innerHTML = `<i class="fas fa-tag me-2"></i>Precio base estimado: <strong>S/. ${parseFloat(precio).toFixed(2)}</strong>`;
                } else if (this.value !== "") {
                    divPrecio.style.display = 'block';
                    divPrecio.innerHTML = `<i class="fas fa-info-circle me-2"></i>Precio sujeto a evaluación en taller.`;
                } else {
                    divPrecio.style.display = 'none';
                }
            }
        });
    }

    // 2. DETECTAR CAMBIO EN EL INPUT FECHA
    if(fechaInput) {
        fechaInput.addEventListener('change', function() {
            renderizarBotonesParaFecha(this.value);
        });
    }

    // 3. RENDERIZAR BOTONES
    function renderizarBotonesParaFecha(fecha) {
        listaHorarios.innerHTML = ''; 
        contenedorHorarios.style.display = 'none';
        paso2Formulario.style.display = 'none';
        detenerCronometro();
        horarioSeleccionado = null;

        const diaData = datosSemanaCache.find(d => d.fecha_iso === fecha);

        if (!diaData) {
            contenedorHorarios.style.display = 'block';
            listaHorarios.innerHTML = '<div class="w-100 text-center text-muted">No hay horarios disponibles.</div>';
            return;
        }

        if (diaData.horarios && diaData.horarios.length > 0) {
            diaData.horarios.forEach(h => {
                const btn = document.createElement('button');
                btn.className = 'horario-btn'; 
                btn.textContent = h.hora;
                btn.type = 'button';
                
                btn.dataset.hora = h.hora;
                btn.dataset.fecha = diaData.fecha_iso;

                if (h.estado !== 'disponible') {
                    btn.disabled = true;
                    btn.title = "No disponible";
                } else {
                    btn.addEventListener('click', () => procesarSeleccion(btn));
                }
                
                listaHorarios.appendChild(btn);
            });
            contenedorHorarios.style.display = 'block';
        }
    }

    // 4. PROCESAR SELECCIÓN
    async function procesarSeleccion(btn) {
        if (horarioSeleccionado && horarioSeleccionado !== btn) {
            await liberarHorario(horarioSeleccionado);
            horarioSeleccionado.classList.remove('seleccionado');
        }

        const fecha = btn.dataset.fecha;
        const hora = btn.dataset.hora + ":00"; 

        const result = await fetch(API_URL, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ accion: 'bloquear', fecha, hora, session_id: SESSION_ID })
        }).then(r => r.json());

        if (result.success) {
            document.querySelectorAll('.horario-btn').forEach(b => b.classList.remove('seleccionado'));
            btn.classList.add('seleccionado');
            horarioSeleccionado = btn;

            document.getElementById('resumenDia').textContent = fecha;
            document.getElementById('resumenHora').textContent = btn.dataset.hora;
            document.querySelector('[name="fecha"]').value = fecha;
            document.querySelector('[name="hora"]').value = hora;
            
            paso2Formulario.style.display = 'block';
            paso2Formulario.scrollIntoView({ behavior: 'smooth', block: 'start' });
            iniciarCronometro();
        } else {
            alert("Ese horario ya no está disponible.");
            cargarDatosIniciales().then(() => renderizarBotonesParaFecha(fecha));
        }
    }

    async function liberarHorario(btn) {
        if(!btn) return;
        const fecha = btn.dataset.fecha;
        const hora = btn.dataset.hora + ":00";
        await fetch(API_URL, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ accion: 'liberar', fecha, hora, session_id: SESSION_ID })
        });
    }

        // 5. CONFIRMAR CITA (VERSIÓN CORREGIDA PARA VALIDACIÓN VISUAL)
            formConfirmarCita.addEventListener('submit', async (e) => {
                e.preventDefault();
                if (!horarioSeleccionado) return alert("Selecciona un horario.");

                // Limpiar errores visuales previos
                const inputCorreo = document.getElementById('txtCorreo'); // Asegúrate que tu input tenga este ID
                const feedbackCorreo = document.getElementById('feedbackCorreo');
                if(inputCorreo) {
                    inputCorreo.classList.remove('is-invalid');
                    if(feedbackCorreo) feedbackCorreo.textContent = '';
                }

                showLoading(); // Mostrar overlay

                const formData = Object.fromEntries(new FormData(e.target));
                formData.accion = 'confirmar';
                formData.session_id = SESSION_ID;

                try {
                    const response = await fetch(API_URL, {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify(formData)
                    });

                    // Importante: Ahora el PHP siempre devuelve JSON, incluso en error controlado
                    const result = await response.json();

                    if (result.success) {
                        detenerCronometro();
                        
                        // 1. OBTENER EL CORREO QUE SE USÓ (Sea del input hidden o visible)
                        const emailUsado = document.querySelector('[name="email_cliente"]').value;

                        // 2. ACTUALIZAR EL MENSAJE DEL MODAL CON EL CORREO                 
                        const modalBody = document.querySelector('#confirmModal .modal-content p');
                        if(modalBody) {
                            modalBody.innerHTML = `
                                Tu servicio fue registrado correctamente.<br>
                                <span class="text-muted small">Se ha enviado un enlace de confirmación a:</span><br>
                                <strong class="text-danger">${emailUsado}</strong>
                            `;
                        }

                        const modalEl = document.getElementById('confirmModal');
                        const modal = new bootstrap.Modal(modalEl);
                        modal.show();
                    } else {
                        // MANEJO DE ERRORES ESPECÍFICO (IGUAL QUE ANTES)
                        if (result.tipo_error === 'email' && inputCorreo) {
                            inputCorreo.classList.add('is-invalid');
                            if(feedbackCorreo) feedbackCorreo.textContent = result.error;
                            inputCorreo.focus();
                        } else {
                            alert("Aviso: " + (result.error || "Ocurrió un error inesperado."));
                        }
                    }
                } catch (error) {
                    console.error("Error JS:", error);
                    alert("Error de conexión. Verifica tu internet.");
                } finally {
                    hideLoading(); // Ocultar overlay siempre
                }
            });
            
            // Utils Cronómetro
            function iniciarCronometro() {
                detenerCronometro();
                tiempoRestante = TIEMPO_BLOQUEO;
                cronometroContainer.style.display = 'block';
                cronometroDisplay.textContent = fmtTime(tiempoRestante);
                cronometroIntervalo = setInterval(() => {
                    tiempoRestante--;
                    cronometroDisplay.textContent = fmtTime(tiempoRestante);
                    if(tiempoRestante <= 0) {
                        alert("Tiempo expirado.");
                        liberarHorario(horarioSeleccionado).then(() => location.reload());
                    }
                }, 1000);
            }

            function detenerCronometro() {
                if(cronometroIntervalo) clearInterval(cronometroIntervalo);
                cronometroContainer.style.display = 'none';
            }

            function fmtTime(s) {
                return `${Math.floor(s/60).toString().padStart(2,'0')}:${(s%60).toString().padStart(2,'0')}`;
            }

            // Init
            cargarDatosIniciales();
        });