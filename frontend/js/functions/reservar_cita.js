document.addEventListener('DOMContentLoaded', () => {
    // --- CONFIGURACIÓN Y REFERENCIAS AL DOM ---
    const API_URL = "http://localhost/cusquena/backend/api/controllers/gestionCitas.php"; // ¡Verifica que esta ruta sea correcta!
    let SESSION_ID; // Se obtendrá después de cargar los horarios iniciales
    const TIEMPO_BLOQUEO = 300; // 300 segundos = 5 minutos

    const acordeonContainer = document.getElementById('acordeonSemana');
    const paso2Formulario = document.getElementById('paso2-datos-cliente');
    const cronometroContainer = document.getElementById('cronometro-container');
    const cronometroDisplay = document.getElementById('cronometro');
    const formConfirmarCita = document.getElementById('formConfirmarCita');
    const sessionIdInput = document.querySelector('[name="session_id"]'); // Referencia al input hidden

    let cronometroIntervalo;
    let tiempoRestante = TIEMPO_BLOQUEO;
    let horarioSeleccionado = null; // Guardará el botón del horario seleccionado
    let isLoading = false; // Flag para evitar cargas múltiples

    // --- FUNCIÓN GENÉRICA PARA LLAMADAS A LA API ---
    async function api(options = {}) {
        try {
            const response = await fetch(API_URL, options);
            if (!response.ok) {
                let errorData;
                try {
                    errorData = await response.json();
                } catch (e) {
                    // Si la respuesta no es JSON (ej. error 500 con HTML)
                    errorData = { error: `Error del servidor (${response.status})` };
                }
                throw new Error(errorData.error || `Error: ${response.statusText}`);
            }
            // Manejar respuesta 204 No Content (puede ocurrir en 'liberar')
             if (response.status === 204) {
                 return { success: true, message: 'Operación completada (No Content).' }; // Devuelve un objeto estándar
             }
            return await response.json();
        } catch (error) {
            console.error("Error detallado en la API:", error); // Log más detallado en consola
            alert('Error al comunicarse con el servidor: ' + error.message);
            // --- CORRECCIÓN CLAVE: NO RECARGAR AUTOMÁTICAMENTE ---
            // Simplemente retornamos null para indicar que hubo un error.
            return null;
        }
    }

    // --- LÓGICA DE LA APLICACIÓN ---

    /**
     * Carga los horarios de toda la semana desde la API y construye el acordeón.
     */
    async function cargarHorariosSemanales() {
        if (isLoading) return; // Evitar recargas simultáneas
        isLoading = true;
        console.log("Iniciando carga de horarios..."); // Log para depuración

        // Resetea el estado visual inmediato
        paso2Formulario.style.display = 'none';
        detenerCronometro(); // Asegura que el cronómetro se detenga
        horarioSeleccionado = null; // Resetea la selección

        // Muestra un indicador de carga
        acordeonContainer.innerHTML = '<div class="text-center p-3"><div class="spinner-border text-primary" role="status"><span class="visually-hidden">Cargando...</span></div></div>';

        const semana = await api({ method: 'GET' });
        acordeonContainer.innerHTML = ''; // Limpiar el indicador de carga

        if (semana && Array.isArray(semana) && semana.length > 0) {
             // Asignar el SESSION_ID si no está definido (importante hacerlo aquí)
             if (!SESSION_ID && sessionIdInput) {
                 SESSION_ID = sessionIdInput.value;
                 console.log("SESSION_ID asignado:", SESSION_ID);
             } else if (!sessionIdInput) {
                  console.error("No se encontró el input 'session_id'. El bloqueo no funcionará.");
                  alert("Error crítico: Falta el identificador de sesión. Contacte al administrador.");
                  isLoading = false;
                  return;
             }


            semana.forEach((dia, index) => {
                const itemAcordeon = document.createElement('div');
                itemAcordeon.className = 'accordion-item';

                itemAcordeon.innerHTML = `
                    <h2 class="accordion-header" id="heading-${index}">
                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapse-${index}">
                            ${dia.dia_nombre} - <small class="ms-2 fw-normal">${dia.fecha_completa}</small>
                        </button>
                    </h2>
                    <div id="collapse-${index}" class="accordion-collapse collapse" data-bs-parent="#acordeonSemana">
                        <div class="accordion-body">
                            <div class="horarios-grid" id="grid-${dia.fecha_iso}">
                                <!-- Horarios se insertarán aquí -->
                            </div>
                        </div>
                    </div>
                `;
                acordeonContainer.appendChild(itemAcordeon);

                const grid = document.getElementById(`grid-${dia.fecha_iso}`);
                if (dia.horarios && dia.horarios.length > 0) {
                    dia.horarios.forEach(h => {
                        const btn = document.createElement('button');
                        btn.className = `btn horario-btn ${h.estado}`;
                        btn.textContent = h.hora;
                        btn.dataset.hora = h.hora;
                        btn.dataset.fecha = dia.fecha_iso;
                        if (h.estado !== 'disponible') {
                            btn.disabled = true;
                        }
                        btn.addEventListener('click', () => seleccionarHorario(btn));
                        grid.appendChild(btn);
                    });
                } else {
                    grid.innerHTML = '<p class="text-center text-muted m-0">No hay citas disponibles para este día.</p>';
                }
            });
        } else if (semana) { // Si la API devolvió algo, pero no es el array esperado
             console.error("Respuesta inesperada de la API:", semana);
             acordeonContainer.innerHTML = '<p class="text-center text-danger">Error al cargar los horarios. Intente recargar la página.</p>';
        } else { // Si la API devolvió null (por el catch)
             acordeonContainer.innerHTML = '<p class="text-center text-danger">No se pudieron cargar los horarios. Verifique su conexión o intente más tarde.</p>';
        }
        isLoading = false;
        console.log("Carga de horarios finalizada.");
    }

    /**
     * Se ejecuta cuando un usuario hace clic en un botón de horario.
     * @param {HTMLElement} btn - El botón del horario que fue presionado.
     */
    async function seleccionarHorario(btn) {
         if (!SESSION_ID) {
             alert("Error: No se pudo identificar la sesión. Recargue la página.");
             return;
         }

        // Si ya hay un horario seleccionado, lo liberamos primero
        let liberacionExitosa = true;
        if (horarioSeleccionado && horarioSeleccionado !== btn) {
            liberacionExitosa = await liberarHorarioSeleccionado();
            // Si la liberación falla, no continuamos para evitar inconsistencias
             if (!liberacionExitosa) {
                 alert("Hubo un problema al liberar el horario anterior. Se recargarán los horarios.");
                 cargarHorariosSemanales();
                 return;
             }
             // Si la liberación fue exitosa, desmarcar visualmente el anterior
             horarioSeleccionado.classList.remove('seleccionado');
             horarioSeleccionado.classList.add('disponible'); // Asumimos que vuelve a estar disponible
             horarioSeleccionado = null; // Importante resetear aquí
        }

        // Si el usuario vuelve a hacer clic en el mismo horario (para deseleccionarlo)
        if (horarioSeleccionado === btn) {
            liberacionExitosa = await liberarHorarioSeleccionado();
            if(liberacionExitosa) {
                horarioSeleccionado = null;
                detenerCronometro();
                paso2Formulario.style.display = 'none';
                btn.classList.remove('seleccionado');
                btn.classList.add('disponible');
            } else {
                 alert("Hubo un problema al liberar el horario. Se recargarán los horarios.");
                 cargarHorariosSemanales();
            }
            return;
        }

        // Bloquear el nuevo horario seleccionado
        const hora = btn.dataset.hora;
        const fecha = btn.dataset.fecha;
        const horaConSegundos = `${hora}:00`; // Asegurarse de enviar con segundos

        const result = await api({
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ accion: 'bloquear', fecha, hora: horaConSegundos, session_id: SESSION_ID })
        });

        if (result && result.success) {
            // Desmarcar visualmente cualquier otra selección residual (por si acaso)
            document.querySelectorAll('.horario-btn.seleccionado').forEach(b => b.classList.remove('seleccionado'));

            horarioSeleccionado = btn;
            btn.classList.remove('disponible');
            btn.classList.add('seleccionado');

            // Llenar y mostrar el formulario
            paso2Formulario.style.display = 'block';
            formConfirmarCita.querySelector('[name="fecha"]').value = fecha;
            formConfirmarCita.querySelector('[name="hora"]').value = horaConSegundos;
            formConfirmarCita.querySelector('[name="session_id"]').value = SESSION_ID; // Asegurarse de que el form tenga el session_id

            iniciarCronometro();
        } else {
             // Si el bloqueo falla (ej. alguien lo tomó justo ahora), recargar horarios
             alert("Lo sentimos, este horario acaba de ser ocupado. Por favor, seleccione otro.");
             cargarHorariosSemanales();
        }
    }

    /**
     * Libera el horario que estaba seleccionado temporalmente.
     * @returns {Promise<boolean>} - True si la liberación fue exitosa o no necesaria, False si falló.
     */
    async function liberarHorarioSeleccionado() {
        if (!horarioSeleccionado) return true; // No hay nada que liberar

        const hora = horarioSeleccionado.dataset.hora;
        const fecha = horarioSeleccionado.dataset.fecha;
        const horaConSegundos = `${hora}:00`;

        console.log(`Intentando liberar: ${fecha} ${horaConSegundos} para ${SESSION_ID}`); // Log para depuración

        const result = await api({
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ accion: 'liberar', fecha, hora: horaConSegundos, session_id: SESSION_ID })
        });

         // Consideramos exitoso si la API responde con success: true o si no devuelve contenido (204)
         const exitoso = result && result.success;
         if (exitoso) {
             console.log(`Horario ${fecha} ${horaConSegundos} liberado.`);
             // Solo reseteamos horarioSeleccionado aquí si la liberación es exitosa
             const btnSeleccionado = horarioSeleccionado; // Guardar referencia temporal
             horarioSeleccionado = null; // Marcar como liberado lógicamente
             // Actualizar visualmente si el botón todavía existe en el DOM
             if (btnSeleccionado && document.body.contains(btnSeleccionado)) {
                btnSeleccionado.classList.remove('seleccionado');
                btnSeleccionado.classList.add('disponible');
             }

         } else {
            console.error("Falló la liberación del horario.");
            // No reseteamos horarioSeleccionado aquí, la recarga lo hará
         }
         return exitoso; // Devolver si fue exitoso o no
    }


    // --- FUNCIONES DEL CRONÓMETRO ---
    function iniciarCronometro() {
        detenerCronometro(); // Asegurarse de que no haya intervalos previos
        tiempoRestante = TIEMPO_BLOQUEO;
        cronometroContainer.style.display = 'block';
        actualizarDisplayCronometro(); // Mostrar tiempo inicial inmediatamente

        cronometroIntervalo = setInterval(() => {
            tiempoRestante--;
            actualizarDisplayCronometro();

            if (tiempoRestante <= 0) {
                console.log("Tiempo expirado."); // Log para depuración
                detenerCronometro(); // Detener el intervalo primero
                alert('Tu tiempo de reserva ha expirado. El horario será liberado.');
                // Intentar liberar antes de recargar
                liberarHorarioSeleccionado().finally(() => {
                    // Independientemente de si la liberación falló o no, recargamos la vista
                    cargarHorariosSemanales();
                });
            }
        }, 1000);
    }

     function actualizarDisplayCronometro() {
         const minutos = Math.floor(tiempoRestante / 60).toString().padStart(2, '0');
         const segundos = (tiempoRestante % 60).toString().padStart(2, '0');
         cronometroDisplay.textContent = `${minutos}:${segundos}`;
     }


    function detenerCronometro() {
        if (cronometroIntervalo) {
            clearInterval(cronometroIntervalo);
            cronometroIntervalo = null; // Importante resetear la variable del intervalo
        }
        cronometroContainer.style.display = 'none';
    }

    // --- MANEJADOR DEL FORMULARIO DE CONFIRMACIÓN ---
    formConfirmarCita.addEventListener('submit', async (e) => {
        e.preventDefault();
        if (!horarioSeleccionado) {
            alert("Por favor, selecciona un horario antes de confirmar.");
            return;
        }

        // Obtener datos del formulario, incluyendo los campos ocultos
        const formData = Object.fromEntries(new FormData(e.target));
        formData.accion = 'confirmar';
        // Asegurarse de que session_id está en los datos a enviar
        formData.session_id = SESSION_ID;

        console.log("Enviando confirmación:", formData); // Log para depuración

        const result = await api({
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(formData)
        });

        if (result && result.success) {
            detenerCronometro();
            // Ya no necesitamos liberar explícitamente, el backend lo hace al confirmar
            horarioSeleccionado = null; // Resetea la selección localmente
            alert('¡Tu cita ha sido confirmada con éxito!');
            // Recargar para ver el horario como 'ocupado'
            cargarHorariosSemanales(); // Opcional: podrías solo actualizar el botón afectado
            // window.location.reload(); // Evitar reload completo si no es necesario
        } else {
             // Si la confirmación falla (ej. el horario expiró o fue tomado)
             alert(result?.error || "No se pudo confirmar la cita. Es posible que el horario ya no esté disponible.");
             cargarHorariosSemanales(); // Recargar para ver el estado actual
        }
    });

    // --- MANEJO DE CIERRE DE PESTAÑA/NAVEGADOR ---
    window.addEventListener('beforeunload', (event) => {
        // La especificación moderna no permite llamadas async/await aquí.
        // Usamos navigator.sendBeacon para un intento de liberación "best-effort".
        if (horarioSeleccionado) {
            const hora = horarioSeleccionado.dataset.hora;
            const fecha = horarioSeleccionado.dataset.fecha;
            const data = JSON.stringify({
                accion: 'liberar',
                fecha,
                hora: `${hora}:00`,
                session_id: SESSION_ID
            });
            navigator.sendBeacon(API_URL, data);
             console.log("Intentando liberar horario (sendBeacon) al cerrar la página...");
        }
        // No se puede mostrar un mensaje personalizado de forma fiable aquí.
    });


    // --- INICIO DE LA APLICACIÓN ---
    console.log("Iniciando la aplicación de citas...");
    cargarHorariosSemanales();
});

