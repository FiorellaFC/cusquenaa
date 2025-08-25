document.addEventListener('DOMContentLoaded', () => {
    // La URL base ahora maneja tanto préstamos como sus pagos
    const API_BASE_URL = 'http://localhost/cusquena/backend/api/controllers/vista_gestion_prestamos/';

    const tablaDeudas = document.getElementById('tablaDeudas');
    const formAgregar = document.getElementById('formAgregar');
    const formEditar = document.getElementById('formEditarDeuda');
    const formAgregarPagoPrestamo = document.getElementById('formAgregarPagoPrestamo');
    const tablaPagosHistorialPrestamo = document.getElementById('tablaPagosHistorialPrestamo');

    const modalAgregar = new bootstrap.Modal(document.getElementById('modalAgregar'));
    const modalEditar = new bootstrap.Modal(document.getElementById('modalEditarDeuda'));
    const modalEliminar = new bootstrap.Modal(document.getElementById('eliminarModal'));
    const modalVerPagosPrestamo = new bootstrap.Modal(document.getElementById('modalVerPagosPrestamo'));
    const modalEliminarPagoPrestamoConfirmacion = new bootstrap.Modal(document.getElementById('modalEliminarPagoPrestamoConfirmacion'));

    // Elementos para los Toasts
    const toastSuccess = new bootstrap.Toast(document.getElementById('toastSuccess'));
    const toastError = new bootstrap.Toast(document.getElementById('toastError'));
    const toastSuccessBody = document.getElementById('toastSuccessBody');
    const toastErrorBody = document.getElementById('toastErrorBody');

    let idAEliminar = null; // ID del préstamo a eliminar
    let idAEditar = null; // ID del préstamo a editar
    let idPrestamoParaPago = null; // ID del préstamo para el cual se gestionan los pagos
    let idPagoParaEliminar = null; // ID de un pago específico a eliminar

    // --- Funciones de Utilidad ---

    /**
     * Muestra un toast de éxito o error.
     * @param {string} type - 'success' o 'error'.
     * @param {string} message - El mensaje a mostrar.
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

    // --- Carga y Renderizado de la Tabla Principal de Préstamos ---

    /**
     * Carga todos los préstamos desde la API y los renderiza en la tabla.
     */
    async function cargarPrestamos() {
        try {
            const response = await fetch(`${API_BASE_URL}listar.php`);
            const data = await response.json();
            renderizarTabla(data.prestamos || []);
        } catch (error) {
            console.error('Error al cargar préstamos:', error);
            showToast('error', '❌ Error al cargar los préstamos. Intenta de nuevo.');
            renderizarTabla([]); // Renderiza una tabla vacía en caso de error
        }
    }

    /**
     * Renderiza los préstamos en la tabla principal.
     * @param {Array<Object>} prestamos - Array de objetos de préstamos.
     */
    function renderizarTabla(prestamos) {
        tablaDeudas.innerHTML = ''; // Limpia la tabla antes de renderizar

        if (prestamos.length === 0) {
            tablaDeudas.innerHTML = '<tr><td colspan="7" class="text-center">No hay préstamos registrados.</td></tr>';
            calcularTotales();
            return;
        }

        prestamos.forEach(p => {
            const row = tablaDeudas.insertRow();
            row.insertCell().textContent = p.nombre;
            row.insertCell().textContent = p.tipo_persona;
            row.insertCell().textContent = `S/. ${parseFloat(p.monto_deuda).toFixed(2)}`;
            row.insertCell().textContent = `S/. ${parseFloat(p.saldo_pendiente).toFixed(2)}`;
            row.insertCell().textContent = p.estado;
            row.insertCell().textContent = p.fecha_inicio_deuda;

            const acciones = row.insertCell();

            const btnEditar = document.createElement('button');
            btnEditar.className = 'btn btn-warning btn-sm me-1';
            btnEditar.innerHTML = '<i class="fas fa-edit"></i>';
            btnEditar.title = 'Editar Préstamo';
            btnEditar.onclick = () => llenarModalEditar(p);
            acciones.appendChild(btnEditar);

            const btnEliminar = document.createElement('button');
            btnEliminar.className = 'btn btn-danger btn-sm me-1';
            btnEliminar.innerHTML = '<i class="fas fa-trash-alt"></i>';
            btnEliminar.title = 'Eliminar Préstamo';
            btnEliminar.onclick = () => {
                idAEliminar = p.id;
                modalEliminar.show();
            };
            acciones.appendChild(btnEliminar);

            const btnVistaPago = document.createElement('button');
            btnVistaPago.className = 'btn btn-info btn-sm';
            btnVistaPago.innerHTML = '<i class="fas fa-eye"></i>';
            btnVistaPago.title = 'Ver/Registrar Pago';
            btnVistaPago.onclick = () => {
                idPrestamoParaPago = p.id;
                document.getElementById('nombrePrestamo').textContent = p.nombre;
                document.getElementById('pagoPrestamoId').value = p.id;
                cargarHistorialPagosPrestamo(p.id);
                modalVerPagosPrestamo.show();
            };
            acciones.appendChild(btnVistaPago);
        });

        calcularTotales();
    }
    
    // --- Función para Calcular y Mostrar los Totales (CORREGIDA) ---
    
    /**
     * Calcula la suma de los montos de deuda y saldos pendientes y los muestra.
     */
    function calcularTotales() {
        let totalMontoDeuda = 0;
        let totalSaldoPendiente = 0;

        // Selecciona todas las filas del tbody con ID 'tablaDeudas'
        const filas = tablaDeudas.querySelectorAll('tr');

        filas.forEach(fila => {
            const columnas = fila.querySelectorAll('td');

            // Verifica que la fila contenga datos (7 columnas) y no sea el mensaje de "No hay préstamos"
            if (columnas.length === 7) {
                const montoDeudaTexto = columnas[2].innerText;
                const saldoPendienteTexto = columnas[3].innerText;
                
                // Limpia el texto de la moneda "S/. " y convierte a un número flotante
                const montoDeudaValor = parseFloat(montoDeudaTexto.replace('S/. ', ''));
                const saldoPendienteValor = parseFloat(saldoPendienteTexto.replace('S/. ', ''));

                if (!isNaN(montoDeudaValor)) {
                    totalMontoDeuda += montoDeudaValor;
                }
                if (!isNaN(saldoPendienteValor)) {
                    totalSaldoPendiente += saldoPendienteValor;
                }
            }
        });

        // Actualiza los elementos HTML con los totales
        const totalMontoDeudaElemento = document.getElementById('totalMontoDeuda');
        const totalSaldoPendienteElemento = document.getElementById('totalSaldoPendiente');

        // Solo actualiza si los elementos existen para evitar errores
        if (totalMontoDeudaElemento) {
            totalMontoDeudaElemento.innerText = `S/. ${totalMontoDeuda.toFixed(2)}`;
        }

        if (totalSaldoPendienteElemento) {
            totalSaldoPendienteElemento.innerText = `S/. ${totalSaldoPendiente.toFixed(2)}`;
        }
    }
    
    // --------------------------------------------------------

    /**
     * Carga el historial de pagos para un préstamo específico desde la API.
     * @param {number} prestamoId - El ID del préstamo.
     */
    async function cargarHistorialPagosPrestamo(prestamoId) {
        try {
            const response = await fetch(`${API_BASE_URL}listar_pagos.php?id_prestamo=${prestamoId}`);
            const data = await response.json();
            renderizarHistorialPagos(data.pagos || []);
        } catch (error) {
            console.error('Error al cargar historial de pagos:', error);
            showToast('error', '❌ Error al cargar el historial de pagos. Intenta de nuevo.');
            renderizarHistorialPagos([]);
        }
    }

    /**
     * Renderiza el historial de pagos en la tabla dentro del modal.
     * @param {Array<Object>} pagos 
     */
    function renderizarHistorialPagos(pagos) {
        tablaPagosHistorialPrestamo.innerHTML = ''; 

        if (pagos.length === 0) {
            tablaPagosHistorialPrestamo.innerHTML = '<tr><td colspan="3" class="text-center">No hay pagos registrados para este préstamo.</td></tr>';
            return;
        }

        pagos.forEach(pago => {
            const row = tablaPagosHistorialPrestamo.insertRow();
            row.insertCell().textContent = pago.fecha_pago;
            row.insertCell().textContent = `S/. ${parseFloat(pago.monto_pagado).toFixed(2)}`;

            const acciones = row.insertCell();

            const btnEliminarPago = document.createElement('button');
            btnEliminarPago.className = 'btn btn-danger btn-sm';
            btnEliminarPago.innerHTML = '<i class="fas fa-trash-alt"></i>';
            btnEliminarPago.title = 'Eliminar Pago';
            btnEliminarPago.onclick = () => {
                document.getElementById('pagoIdParaEliminarPrestamoConfirmacion').value = pago.id;
                document.getElementById('prestamoIdParaPagoEliminarConfirmacion').value = pago.id_prestamo;
                
                modalEliminarPagoPrestamoConfirmacion.show();
            };
            acciones.appendChild(btnEliminarPago);
        });
    }

    // --- Llenar Modales ---

    /**
     * @param {Object} prestamo 
     */
    function llenarModalEditar(prestamo) {
        idAEditar = prestamo.id;
        document.getElementById('editNombre').value = prestamo.nombre;
        document.getElementById('editTipoPersona').value = prestamo.tipo_persona;
        document.getElementById('editMontoDeuda').value = parseFloat(prestamo.monto_deuda).toFixed(2);
        document.getElementById('editSaldoPendiente').value = parseFloat(prestamo.saldo_pendiente).toFixed(2);
        document.getElementById('editEstado').value = prestamo.estado;
        document.getElementById('editFechaInicioDeuda').value = prestamo.fecha_inicio_deuda;

        modalEditar.show();
    }

    // --- Event Listeners de Formularios y Botones ---

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
                showToast('success', '✅ Préstamo registrado exitosamente!');
                resetForm(formAgregar);
                modalAgregar.hide();
                cargarPrestamos(); 
            } else {
                showToast('error', `❌ Error al registrar: ${data.error || 'Mensaje de error desconocido.'}`);
            }
        } catch (error) {
            console.error('Error al registrar préstamo:', error);
            showToast('error', '❌ Hubo un problema de conexión al intentar registrar el préstamo.');
        }
    });

    formEditar.addEventListener('submit', async e => {
        e.preventDefault();
        const formData = new FormData(formEditar);
        const datos = Object.fromEntries(formData.entries());
        datos.id = idAEditar; 

        try {
            const response = await fetch(`${API_BASE_URL}actualizar.php`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(datos)
            });

            const data = await response.json();

            if (data.success) {
                showToast('success', '✅ Préstamo actualizado exitosamente!');
                modalEditar.hide();
                cargarPrestamos(); 
            } else {
                showToast('error', `❌ Error al actualizar: ${data.error || 'Mensaje de error desconocido.'}`);
            }
        } catch (error) {
            console.error('Error al actualizar préstamo:', error);
            showToast('error', '❌ Hubo un problema de conexión al intentar actualizar el préstamo.');
        }
    });

    formAgregarPagoPrestamo.addEventListener('submit', async e => {
        e.preventDefault();
        const fechaNuevoPago = document.getElementById('fechaNuevoPago').value;
        const montoNuevoPago = document.getElementById('montoNuevoPago').value;

        if (!idPrestamoParaPago) {
            showToast('error', '❌ No se ha seleccionado un préstamo para registrar el pago.');
            return;
        }
        if (!fechaNuevoPago || !montoNuevoPago) {
            showToast('error', '❌ Por favor, completa la fecha y el monto del pago.');
            return;
        }

        const datosPago = {
            id_prestamo: idPrestamoParaPago,
            fecha_pago: fechaNuevoPago,
            monto_pagado: montoNuevoPago
        };

        try {
            const response = await fetch(`${API_BASE_URL}registrar_pago.php`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(datosPago)
            });

            const data = await response.json();

            if (data.success) {
                showToast('success', '✅ Pago de préstamo registrado exitosamente!');
                resetForm(formAgregarPagoPrestamo);
                cargarHistorialPagosPrestamo(idPrestamoParaPago);
                cargarPrestamos();
            } else {
                showToast('error', `❌ Error al registrar pago: ${data.error || 'Mensaje de error desconocido.'}`);
            }
        } catch (error) {
            console.error('Error al registrar pago de préstamo:', error);
            showToast('error', '❌ Hubo un problema de conexión al intentar registrar el pago.');
        }
    });

    document.getElementById('confirmarEliminarPrestamo').addEventListener('click', async () => {
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
                showToast('success', '✅ Préstamo eliminado exitosamente!');
                idAEliminar = null; 
                cargarPrestamos(); 
            } else {
                showToast('error', `❌ Error al eliminar: ${data.error || 'Mensaje de error desconocido.'}`);
            }
        } catch (error) {
            console.error('Error al eliminar préstamo:', error);
            showToast('error', '❌ Hubo un problema de conexión al intentar eliminar el préstamo.');
        }
    });


    document.getElementById('btnConfirmarEliminarPagoPrestamo').addEventListener('click', async () => {
        const pagoId = document.getElementById('pagoIdParaEliminarPrestamoConfirmacion').value;
        const prestamoId = document.getElementById('prestamoIdParaPagoEliminarConfirmacion').value;

        if (!pagoId || !prestamoId) {
            showToast('error', '❌ No se pudo determinar el pago o préstamo a eliminar.');
            return;
        }

        try {
            const response = await fetch(`${API_BASE_URL}eliminar_pago.php`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ id: pagoId }) 
            });

            const data = await response.json();
            modalEliminarPagoPrestamoConfirmacion.hide(); 

            if (data.success) {
                showToast('success', '✅ Pago eliminado exitosamente! Saldo actualizado.');
                cargarHistorialPagosPrestamo(prestamoId);
                cargarPrestamos();
            } else {
                showToast('error', `❌ Error al eliminar pago: ${data.error || 'Mensaje de error desconocido.'}`);
            }
        } catch (error) {
            console.error('Error al eliminar pago de préstamo:', error);
            showToast('error', '❌ Hubo un problema de conexión al intentar eliminar el pago.');
        }
    });

    document.getElementById('btnBuscar').addEventListener('click', async () => {
        const searchTerm = document.getElementById('buscarPrestamo').value;
        try {
            const response = await fetch(`${API_BASE_URL}listar.php?nombre=${encodeURIComponent(searchTerm)}`);
            const data = await response.json();
            renderizarTabla(data.prestamos || []);
        } catch (error) {
            console.error('Error al buscar préstamos:', error);
            showToast('error', '❌ Error al realizar la búsqueda de préstamos.');
            renderizarTabla([]);
        }
    });

    document.addEventListener('DOMContentLoaded', () => {
    const btnBuscar = document.getElementById('btnBuscar');
    const btnLimpiar = document.getElementById('btnLimpiar');

    btnBuscar.addEventListener('click', () => {
        const filtros = {
            nombre: document.getElementById('buscarNombre').value,
            tipo_persona: document.getElementById('filtroTipoPersona').value,
            estado: document.getElementById('filtroEstado').value,
            fecha_inicio: document.getElementById('filtroFechaInicio').value
        };

        // Llamada a la API PHP con los filtros
        fetch(`backend/api/deudas/listar.php?nombre=${filtros.nombre}&tipo_persona=${filtros.tipo_persona}&estado=${filtros.estado}&fecha_inicio=${filtros.fecha_inicio}`)
            .then(res => res.json())
            .then(data => {
                // Aquí renderizas la tabla con los resultados
                console.log(data);
            });
    });

    btnLimpiar.addEventListener('click', () => {
        document.getElementById('buscarNombre').value = '';
        document.getElementById('filtroTipoPersona').value = '';
        document.getElementById('filtroEstado').value = '';
        document.getElementById('filtroFechaInicio').value = '';
        // Vuelves a cargar la lista completa
        fetch(`backend/api/deudas/listar.php`)
            .then(res => res.json())
            .then(data => {
                console.log(data);
            });
    });
});


    // Carga inicial de los datos
    cargarPrestamos();
});