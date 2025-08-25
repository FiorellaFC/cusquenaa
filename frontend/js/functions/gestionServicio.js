document.addEventListener('DOMContentLoaded', function () {
    const tablaServicios = document.getElementById('tablaServicios');
    const formAgregar = document.getElementById('formAgregar');
    const formEditar = document.getElementById('formEditar');
    const buscarServicio = document.getElementById('buscarServicio');
    const btnBuscar = document.getElementById('btnBuscar');

    let servicios = [];

    function formatearFecha(fechaISO) {
        if (!fechaISO) return '';
        const [anio, mes, dia] = fechaISO.split("-");
        return `${dia}-${mes}-${anio}`;
    }

    function cargarServicios(filtro = '') {
        fetch(`../../backend/api/controllers/gestionServicio.php?accion=listar&buscar=${encodeURIComponent(filtro)}`)
            .then(res => res.json())
            .then(data => {
                servicios = data;
                renderTabla(servicios);
            })
            .catch(err => console.error('Error al cargar servicios:', err));
    }

    function renderTabla(data) {
    tablaServicios.innerHTML = '';
    let total = 0;

    if (data.length === 0) {
        tablaServicios.innerHTML = `<tr><td colspan="8">No se encontraron servicios</td></tr>`;
        document.getElementById('totalGeneral').textContent = 'Total: S/ 0.00';
        return;
    }

    data.forEach(servicio => {
        const fila = document.createElement('tr');
        const subtotal = parseFloat(servicio.precio_unitario) * parseFloat(servicio.cantidad);
        total += subtotal;

        fila.innerHTML = `
            <td>${servicio.id_servicio}</td>
            <td>${servicio.nombre_servicio}</td>
            <td>${servicio.tipo_servicio}</td>
            <td>S/ ${parseFloat(servicio.precio_unitario).toFixed(2)}</td>
            <td>${servicio.cantidad}</td>
            <td>${formatearFecha(servicio.fecha_registro)}</td>
            <td>
                <span class="badge ${servicio.estado === 'Activo' ? 'bg-success' : 'bg-secondary'}">${servicio.estado}</span>
            </td>
            <td>
                <button class="btn btn-sm btn-warning me-1 btn-editar" data-id="${servicio.id_servicio}"><i class="fas fa-edit"></i></button>
                <button class="btn btn-sm btn-danger btn-eliminar" data-id="${servicio.id_servicio}"><i class="fas fa-trash-alt"></i></button>
            </td>
        `;
        tablaServicios.appendChild(fila);
    });

    // Mostrar total general
    const totalGeneralElement = document.getElementById('totalGeneral');
    if (totalGeneralElement) {
        totalGeneralElement.textContent = `Total: S/ ${total.toFixed(2)}`;
    }
}

    formAgregar.addEventListener('submit', function (e) {
        e.preventDefault();
        const formData = new FormData(formAgregar);
        formData.append('accion', 'registrar');

        fetch('../../backend/api/controllers/gestionServicio.php', {
            method: 'POST',
            body: formData
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                formAgregar.reset();
                bootstrap.Modal.getInstance(document.getElementById('modalAgregar')).hide();
                cargarServicios();
            } else {
                alert('Error al registrar servicio');
            }
        })
        .catch(err => console.error(err));
    });

    formEditar.addEventListener('submit', function (e) {
        e.preventDefault();
        const formData = new FormData(formEditar);
        formData.append('accion', 'modificar');

        fetch('../../backend/api/controllers/gestionServicio.php', {
            method: 'POST',
            body: formData
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                bootstrap.Modal.getInstance(document.getElementById('modalEditar')).hide();
                cargarServicios();
            } else {
                alert('Error al modificar servicio: ' + (data.message || ''));
            }
        })
        .catch(err => console.error(err));
    });

    tablaServicios.addEventListener('click', function (e) {
        if (e.target.closest('.btn-editar')) {
            const id = e.target.closest('.btn-editar').dataset.id;
            const servicio = servicios.find(s => s.id_servicio == id);

            if (servicio) {
                document.getElementById('editarId').value = servicio.id_servicio;
                document.getElementById('editarNombre').value = servicio.nombre_servicio;
                document.getElementById('editarTipoServicio').value = servicio.tipo_servicio;
                document.getElementById('editarPrecioUnitario').value = servicio.precio_unitario;
                document.getElementById('editarCantidad').value = servicio.cantidad;
                document.getElementById('editarFechaRegistro').value = servicio.fecha_registro;

                if (servicio.estado === 'Activo') {
                    document.getElementById('editarEstadoActivo').checked = true;
                } else {
                    document.getElementById('editarEstadoInactivo').checked = true;
                }

                new bootstrap.Modal(document.getElementById('modalEditar')).show();
            }
        }

        if (e.target.closest('.btn-eliminar')) {
            const id = e.target.closest('.btn-eliminar').dataset.id;
            if (confirm('¿Está seguro de eliminar este servicio?')) {
                fetch(`../../backend/api/controllers/gestionServicio.php?accion=eliminar&id=${id}`, {
                    method: 'GET'
                })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        cargarServicios();
                    } else {
                        alert('Error al eliminar servicio');
                    }
                })
                .catch(err => console.error(err));
            }
        }
    });

    btnBuscar.addEventListener('click', () => {
        const filtro = buscarServicio.value.trim();
        cargarServicios(filtro);
    });

    buscarServicio.addEventListener('keyup', function (e) {
        if (e.key === 'Enter') {
            btnBuscar.click();
        }
    });

    cargarServicios(); // carga inicial
});
