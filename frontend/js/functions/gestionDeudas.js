document.addEventListener('DOMContentLoaded', function () {
    // --- ELEMENTOS DEL DOM ---
    const tablaDeudas = document.getElementById('tablaDeudas');
    const formAgregarDeuda = document.getElementById('formAgregarDeuda');
    const formEditarDeuda = document.getElementById('formEditarDeuda');
    const btnBuscar = document.getElementById('btnBuscar');

    // Modales
    const modalAgregar = new bootstrap.Modal(document.getElementById('modalAgregarDeuda'));
    const modalEditar = new bootstrap.Modal(document.getElementById('modalEditarDeuda'));
    const modalPagos = new bootstrap.Modal(document.getElementById('modalVerPagos'));

    // Elementos del Modal de Pagos
    const formAgregarPago = document.getElementById('formAgregarPago');
    const tablaPagosHistorial = document.getElementById('tablaPagosHistorial');
    const nombreDeudorSpan = document.getElementById('nombreDeudor');
    const pagoDeudaIdInput = document.getElementById('pagoDeudaId');
    const fechaPagoInput = document.getElementById('fechaPago'); 
    
    // Elementos de los totales
    const totalDeudaGeneral = document.getElementById('totalDeudaGeneral');
    const totalPendienteGeneral = document.getElementById('totalPendienteGeneral');

    //filtros de busqueda 

    const buscarDeudaInput = document.getElementById('buscarDeuda');
    const filtroTipoDeudaSelect = document.getElementById('filtroTipoDeuda');
    const filtroTipoPersonaSelect = document.getElementById('filtroTipoPersona');
    const filtroEstadoSelect = document.getElementById('filtroEstado');
    const btnLimpiar = document.getElementById('btnLimpiar');
   
    let deudas = []; 

 

    /**
   
     * @param {string} fechaISO 
     * @returns {string} 
     */
    function formatearFecha(fechaISO) {
        if (!fechaISO) return 'N/A';
        const [anio, mes, dia] = fechaISO.split("-");
        return `${dia}-${mes}-${anio}`;
    }
    function aplicarFiltros() {
    const buscar = buscarDeudaInput.value;
    const tipoDeuda = filtroTipoDeudaSelect.value;
    const tipoPersona = filtroTipoPersonaSelect.value;
    const estado = filtroEstadoSelect.value;

    // Construir la URL de forma dinámica
    let url = `../../backend/api/controllers/gestionDeudas.php?accion=listar`;
    if (buscar) url += `&buscar=${encodeURIComponent(buscar)}`;
    if (tipoDeuda) url += `&filtroTipoDeuda=${encodeURIComponent(tipoDeuda)}`;
    if (tipoPersona) url += `&filtroTipoPersona=${encodeURIComponent(tipoPersona)}`;
    if (estado) url += `&filtroEstado=${encodeURIComponent(estado)}`;

    cargarDeudas(url);
}
    /**
     * 
     * @param {number|string} valor 
     * @returns {string} 
     */
    function formatearMoneda(valor) {
        
        const num = parseFloat(valor);
        if (isNaN(num)) {
            return 'S/ 0.00'; 
        }
        return `S/. ${num.toFixed(2)}`;
    }
    
    /**
     * 
     * @param {string} estado
     * @returns {string} 
     */
    function getBadgeEstado(estado) {
        switch (estado) {
            case 'pendiente':
                return `<span class="badge bg-warning text-dark">Pendiente</span>`;
            case 'pagada':
                return `<span class="badge bg-success">Pagada</span>`;
            case 'en_atraso':
                return `<span class="badge bg-danger">En Atraso</span>`;
            default:
                return `<span class="badge bg-secondary">${estado}</span>`;
        }
    }

    // --- FUNCIONES PRINCIPALES (LÓGICA DE DEUDAS) ---

    /**
     * 
     * @param {string} filtro 
     */
        function cargarDeudas(url = `../../backend/api/controllers/gestionDeudas.php?accion=listar`) {
            tablaDeudas.innerHTML = `<tr><td colspan="9" class="text-center">Cargando deudas...</td></tr>`; 

            fetch(url)
                .then(res => res.json())
                .then(data => {
                    if (data.error) {
                        throw new Error(data.error);
                    }
                    deudas = data; 
                    renderTablaDeudas(deudas);
                    actualizarTotales();
                })
                .catch(err => {
                    console.error('Error al cargar deudas:', err);
                    tablaDeudas.innerHTML = `<tr><td colspan="9" class="text-center text-danger">Error al cargar deudas: ${err.message || err}. Recarga la página.</td></tr>`;
                });
        }

    /**
     * 
     * @param {Array} data 
     */
    function renderTablaDeudas(data) {
        tablaDeudas.innerHTML = '';
        if (data.length === 0) {
            tablaDeudas.innerHTML = `<tr><td colspan="9" class="text-center">No se encontraron deudas</td></tr>`;
            return;
        }

        data.forEach(deuda => {
            const fila = document.createElement('tr');
            fila.innerHTML = `
                <td>${deuda.nombre}</td>
                <td>${deuda.tipo_deuda.replace('_', ' ')}</td>
                <td>${deuda.tipo_persona}</td>
                <td>${formatearMoneda(deuda.monto_deuda)}</td>
                <td>${formatearMoneda(deuda.saldo_pendiente)}</td>
                <td>${formatearFecha(deuda.fecha_inicio)}</td>
                <td>${parseFloat(deuda.tasa_interes).toFixed(2)}%</td>
                <td>${getBadgeEstado(deuda.estado)}</td>
                <td>
                    <button class="btn btn-sm btn-info btn-ver-pagos" data-id="${deuda.id}" data-nombre="${deuda.nombre}" title="Ver Pagos"><i class="fas fa-eye"></i></button>
                    <button class="btn btn-sm btn-warning btn-editar" data-id="${deuda.id}" title="Editar"><i class="fas fa-edit"></i></button>
                    <button class="btn btn-sm btn-danger btn-eliminar" data-id="${deuda.id}" title="Eliminar"><i class="fas fa-trash"></i></button>
                </td>
            `;
            tablaDeudas.appendChild(fila);
        });
    }

    // --- MANEJADORES DE EVENTOS DE DEUDAS ---

    // 
    formAgregarDeuda.addEventListener('submit', function (event) {
        event.preventDefault();
        const formData = new FormData(formAgregarDeuda);
        formData.append('accion', 'registrar');

        fetch('../../backend/api/controllers/gestionDeudas.php', {
            method: 'POST',
            body: formData
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                alert('Deuda registrada con éxito.');
                modalAgregar.hide();
                formAgregarDeuda.reset();
                cargarDeudas();
                actualizarTotales();
            } else {
                alert('Error al registrar deuda: ' + data.error);
                console.error('Error al registrar deuda:', data.error);
            }
        })
        .catch(err => {
            alert('Error de conexión al registrar deuda.');
            console.error('Error de red al registrar deuda:', err);
        });
    });

    
    formEditarDeuda.addEventListener('submit', function (event) {
        event.preventDefault();
        const formData = new FormData(formEditarDeuda);
        formData.append('accion', 'modificar');
        
        fetch('../../backend/api/controllers/gestionDeudas.php', {
            method: 'POST',
            body: formData
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                alert('Deuda modificada con éxito.');
                modalEditar.hide();
                cargarDeudas();
                actualizarTotales();
            } else {
                alert('Error al modificar deuda: ' + data.error);
                console.error('Error al modificar deuda:', data.error);
            }
        })
        .catch(err => {
            alert('Error de conexión al modificar deuda.');
            console.error('Error de red al modificar deuda:', err);
        });
    });

    // Delegación de eventos para los botones en la tabla de deudas
    tablaDeudas.addEventListener('click', function (event) {
        // Botón Editar
        if (event.target.closest('.btn-editar')) {
            const button = event.target.closest('.btn-editar');
            const deudaId = button.dataset.id;
            const deuda = deudas.find(d => d.id == deudaId); 

            if (deuda) {
                document.getElementById('editDeudaId').value = deuda.id;
                document.getElementById('edit_nombre').value = deuda.nombre;
                document.getElementById('edit_tipoDeuda').value = deuda.tipo_deuda;
                document.getElementById('edit_tipoPersona').value = deuda.tipo_persona;
                document.getElementById('edit_montoDeuda').value = parseFloat(deuda.monto_deuda).toFixed(2);
                document.getElementById('edit_saldoPendiente').value = parseFloat(deuda.saldo_pendiente).toFixed(2);
                document.getElementById('edit_fechaInicioDeuda').value = deuda.fecha_inicio;
                document.getElementById('edit_tasaInteres').value = parseFloat(deuda.tasa_interes).toFixed(2);
                document.getElementById('edit_estado').value = deuda.estado;
                modalEditar.show();
            } else {
                alert('Deuda no encontrada.');
            }
        }

        // Botón Eliminar
        if (event.target.closest('.btn-eliminar')) {
            const button = event.target.closest('.btn-eliminar');
            const deudaId = button.dataset.id;
            if (confirm('¿Estás seguro de que deseas eliminar esta deuda? También se eliminarán todos los pagos asociados.')) {
                fetch(`../../backend/api/controllers/gestionDeudas.php?accion=eliminar&id=${deudaId}`, {
                    method: 'GET' 
                })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        alert('Deuda eliminada con éxito.');
                        cargarDeudas();
                        actualizarTotales();
                    } else {
                        alert('Error al eliminar deuda: ' + data.error);
                        console.error('Error al eliminar deuda:', data.error);
                    }
                })
                .catch(err => {
                    alert('Error de conexión al eliminar deuda.');
                    console.error('Error de red al eliminar deuda:', err);
                });
            }
        }

        // Botón Ver Pagos (NUEVO)
        if (event.target.closest('.btn-ver-pagos')) {
            const button = event.target.closest('.btn-ver-pagos');
            const deudaId = button.dataset.id;
            const nombreDeudor = button.dataset.nombre;

            nombreDeudorSpan.textContent = nombreDeudor; 
            pagoDeudaIdInput.value = deudaId; 
            
            
            const today = new Date().toISOString().split('T')[0];
            fechaPagoInput.value = today;

            cargarHistorialPagos(deudaId); 
            modalPagos.show(); 
        }
    });

    // --- FUNCIONES Y MANEJADORES DE EVENTOS DE PAGOS (NUEVOS) ---

    /**
     * 
     * @param {number} deudaId 
     */
    function cargarHistorialPagos(deudaId) {
        tablaPagosHistorial.innerHTML = `<tr><td colspan="3" class="text-center">Cargando historial de pagos...</td></tr>`;

        fetch(`../../backend/api/controllers/gestionDeudas.php?accion=listarPagos&deudaId=${deudaId}`)
            .then(res => res.json())
            .then(data => {
                if (data.error) {
                    console.error('Error al cargar historial de pagos:', data.error);
                    tablaPagosHistorial.innerHTML = `<tr><td colspan="3" class="text-center text-danger">Error al cargar historial: ${data.error}</td></tr>`;
                    return;
                }
                renderTablaPagos(data);
            })
            .catch(err => {
                console.error('Error de red o servidor al cargar historial:', err);
                tablaPagosHistorial.innerHTML = `<tr><td colspan="3" class="text-center text-danger">Error de conexión al cargar historial.</td></tr>`;
            });
    }

    /**
     * 
     * @param {Array} pagos 
     */
    function actualizarTotales() {
    fetch('../../backend/api/controllers/gestionDeudas.php?accion=obtenerTotales')
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                totalDeudaGeneral.textContent = formatearMoneda(data.total_prestado);
                totalPendienteGeneral.textContent = formatearMoneda(data.total_pendiente);
            } else {
                console.error('Error al obtener los totales:', data.error);
                totalDeudaGeneral.textContent = 'Error';
                totalPendienteGeneral.textContent = 'Error';
            }
        })
        .catch(err => {
            console.error('Error de red al obtener los totales:', err);
            totalDeudaGeneral.textContent = 'Error';
            totalPendienteGeneral.textContent = 'Error';
        });
}

    function renderTablaPagos(pagos) {
        tablaPagosHistorial.innerHTML = ''; 
        if (pagos.length === 0) {
            tablaPagosHistorial.innerHTML = `<tr><td colspan="3" class="text-center">No hay pagos registrados para esta deuda.</td></tr>`;
            return;
        }

        pagos.forEach(pago => {
            const fila = document.createElement('tr');
            fila.innerHTML = `
                <td>${formatearFecha(pago.fecha_pago)}</td>
                <td>${formatearMoneda(pago.monto_pagado)}</td>
                <td>
                    <button class="btn btn-xs btn-danger btn-eliminar-pago" data-id="${pago.id}" data-deuda-id="${pago.deuda_id}" data-monto="${pago.monto_pagado}" title="Eliminar Pago">Eliminar</button>
                </td>
            `;
            tablaPagosHistorial.appendChild(fila);
        });
    }

    // Manejar el submit del formulario de agregar pago
    formAgregarPago.addEventListener('submit', function(event) {
        event.preventDefault();

        const deudaId = pagoDeudaIdInput.value;
        const fechaPago = document.getElementById('fechaPago').value;
        const montoPago = document.getElementById('montoPago').value;

        if (!deudaId || parseFloat(montoPago) <= 0) {
            alert('Por favor, ingresa un monto válido para el pago.');
            return;
        }

        const formData = new FormData();
        formData.append('accion', 'registrarPago');
        formData.append('deudaId', deudaId);
        formData.append('fechaPago', fechaPago);
        formData.append('montoPago', montoPago);

        fetch('../../backend/api/controllers/gestionDeudas.php', {
            method: 'POST',
            body: formData
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                alert('Pago registrado con éxito.');
                formAgregarPago.reset(); 
              
                fechaPagoInput.value = new Date().toISOString().split('T')[0]; 
                cargarHistorialPagos(deudaId);
                cargarDeudas(); 
                actualizarTotales();
            } else {
                alert('Error al registrar pago: ' + data.error);
                console.error('Error al registrar pago:', data.error);
            }
        })
        .catch(err => {
            alert('Error de conexión al registrar pago.');
            console.error('Error de red al registrar pago:', err);
        });
    });

    // Delegación de eventos para los botones de eliminar pago dentro del modal
    tablaPagosHistorial.addEventListener('click', function(event) {
        if (event.target.closest('.btn-eliminar-pago')) {
            const button = event.target.closest('.btn-eliminar-pago');
            const pagoId = button.dataset.id;
            const deudaId = button.dataset.deudaId;
            const montoPago = button.dataset.monto;

            if (confirm(`¿Estás seguro de que deseas eliminar este pago de ${formatearMoneda(montoPago)}? Se revertirá el saldo de la deuda.`)) {
                fetch(`../../backend/api/controllers/gestionDeudas.php?accion=eliminarPago&id=${pagoId}`, {
                    method: 'GET'
                })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        alert('Pago eliminado con éxito. Saldo de deuda actualizado.');
                        cargarHistorialPagos(deudaId); 
                        cargarDeudas(); 
                        actualizarTotales();
                    } else {
                        alert('Error al eliminar pago: ' + data.error);
                        console.error('Error al eliminar pago:', data.error);
                    }
                })
                .catch(err => {
                    alert('Error de conexión al eliminar pago.');
                    console.error('Error de red al eliminar pago:', err);
                });
            }
        }
    });

    // Manejar botón de búsqueda
   btnBuscar.addEventListener('click', function() {
    aplicarFiltros();
});

// Añadir el manejador de btnLimpiar
btnLimpiar.addEventListener('click', function() {
    // Limpiar los campos de búsqueda y filtros
    buscarDeudaInput.value = '';
    filtroTipoDeudaSelect.value = '';
    filtroTipoPersonaSelect.value = '';
    filtroEstadoSelect.value = '';

    // Cargar todas las deudas de nuevo
    cargarDeudas();
});

    // Cargar deudas al inicio
    cargarDeudas();
    actualizarTotales();
});