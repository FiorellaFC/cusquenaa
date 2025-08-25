const tablaProductos = document.getElementById("tablaProductos");
const formAgregar = document.getElementById("formAgregarProducto");
const formEditar = document.getElementById("formEditarProducto");
const formVender = document.getElementById("formVenderProducto");
const buscarProducto = document.getElementById("buscarProducto");
const btnBuscar = document.getElementById("btnBuscar");

// Instancias de los modales para poder controlarlos
const modalAgregar = new bootstrap.Modal(document.getElementById('modalAgregarProducto'));
const modalEditar = new bootstrap.Modal(document.getElementById('modalEditarProducto'));
const modalVender = new bootstrap.Modal(document.getElementById('modalVenderProducto'));
const modalVerVentas = new bootstrap.Modal(document.getElementById('modalVerVentas'));

let productos = [];
// NUEVO: Variable para guardar el historial completo del producto seleccionado
let historialActual = [];

function cargarProductos() {
    fetch("../../backend/api/controllers/gestionProducto.php?action=listar")
        .then((res) => res.json())
        .then((data) => {
            productos = data.productos;
            renderizarTabla(productos);

            // Monto Total de Ventas
            const montoTotalGeneralEl = document.getElementById('montoTotalGeneral');
            const totalMonto = data.total_monto ? parseFloat(data.total_monto) : 0;
            montoTotalGeneralEl.textContent = `S/. ${totalMonto.toFixed(2)}`;

            // NUEVO: Obtener y mostrar el Monto Total Gastado
            const montoTotalGastadoEl = document.getElementById('montoTotalGastado');
            const totalGastado = data.total_gastado ? parseFloat(data.total_gastado) : 0;
            montoTotalGastadoEl.textContent = `S/. ${totalGastado.toFixed(2)}`;
        })
        .catch((error) => {
            console.error("Error cargando productos:", error);
            alert("Error al cargar productos. Revisa la consola para más detalles.");
        });
}

function renderizarTabla(lista) {
    tablaProductos.innerHTML = "";
    if (!lista || lista.length === 0) {
        tablaProductos.innerHTML = '<tr><td colspan="11">No se encontraron productos.</td></tr>';
        return;
    }
    lista.forEach((producto) => {
        const tr = document.createElement("tr");
        const precioCompra = parseFloat(producto.precio_compra).toFixed(2);
        const precioVenta = parseFloat(producto.precio_venta).toFixed(2);
        const monto = parseFloat(producto.monto).toFixed(2);

        // Determinar qué botones mostrar según el rol
        const esAdmin = '<?php echo $_SESSION["rol"] === "Administrador"; ?>';
        let botonesAdmin = '';
        if (esAdmin) {
            botonesAdmin = `
                <button class="btn btn-sm btn-warning" onclick="abrirModalEditar(${producto.id})" title="Editar"><i class="fas fa-edit"></i></button>
                <button class="btn btn-sm btn-danger" onclick="eliminarProducto(${producto.id})" title="Eliminar"><i class="fas fa-trash"></i></button>
            `;
        }

        tr.innerHTML = `
            <td>${producto.id}</td>
            <td>${producto.descripcion}</td>
            <td>S/. ${precioCompra}</td>
            <td>S/. ${precioVenta}</td>
            <td>${producto.inicial}</td>
            <td>${producto.ingreso}</td>
            <td>${producto.queda}</td>
            <td>${producto.venta}</td>
            <td>S/. ${monto}</td>
            <td>${producto.categoria}</td>
            <td class="d-flex justify-content-center flex-wrap">
                <button class="btn btn-sm btn-info m-1" onclick="verHistorial(${producto.id}, '${producto.descripcion.replace(/'/g, "\\'")}')" title="Ver Historial"><i class="fas fa-eye"></i></button>
                <button class="btn btn-sm btn-primary m-1" onclick="abrirModalVenta(${producto.id})" title="Vender"><i class="fas fa-cart-plus"></i></button>
                ${botonesAdmin}
            </td>`;
        tablaProductos.appendChild(tr);
    });
}

// --- Funciones CRUD y Búsqueda (sin cambios) ---
formAgregar.addEventListener("submit", (e) => {
    e.preventDefault();
    const datos = new FormData(formAgregar);
    fetch("../../backend/api/controllers/gestionProducto.php?action=agregar", {
        method: "POST",
        body: datos,
    })
    .then((res) => res.json())
    .then((response) => {
        if (response.success) {
            cargarProductos();
            formAgregar.reset();
            modalAgregar.hide();
        } else {
            alert("Error al agregar: " + response.message);
        }
    })
    .catch((error) => console.error("Error:", error));
});

function eliminarProducto(id) {
    if (!confirm("¿Está seguro de que desea eliminar este producto? Esta acción no se puede deshacer.")) return;
    fetch(`../../backend/api/controllers/gestionProducto.php?action=eliminar&id=${id}`, {
        method: "DELETE", // Usar DELETE para eliminar
    })
    .then((res) => res.json())
    .then((response) => {
        if (response.success) {
            cargarProductos();
        } else {
            alert("Error al eliminar: " + (response.error || 'Error desconocido'));
        }
    })
    .catch((error) => console.error("Error:", error));
}

btnBuscar.addEventListener("click", () => {
    const termino = buscarProducto.value.toLowerCase();
    const filtrados = productos.filter((p) =>
        p.descripcion.toLowerCase().includes(termino) ||
        p.categoria.toLowerCase().includes(termino)
    );
    renderizarTabla(filtrados);
});

buscarProducto.addEventListener('keyup', (event) => {
    if (event.key === 'Enter') {
        btnBuscar.click();
    }
});


// --- Funciones para Modales (Editar y Vender) ---
function abrirModalEditar(id) {
    const producto = productos.find(p => p.id == id);
    if (producto) {
        document.getElementById('editar_idProducto').value = producto.id;
        document.getElementById('editar_descripcion').value = producto.descripcion;
        document.getElementById('editar_precio_compra').value = producto.precio_compra;
        document.getElementById('editar_precio_venta').value = producto.precio_venta;
        document.getElementById('editar_inicial').value = producto.inicial;
        document.getElementById('editar_ingreso').value = producto.ingreso;
        document.getElementById('editar_venta').value = producto.venta;
        document.getElementById('editar_monto').value = producto.monto;
        document.getElementById('editar_categoria').value = producto.categoria;
        // Disparar el evento input para que se calcule el 'queda' inicial
        document.getElementById('editar_inicial').dispatchEvent(new Event('input'));
        modalEditar.show();
    }
}

formEditar.addEventListener("submit", (e) => {
    e.preventDefault();
    const datos = new FormData(formEditar);
    fetch("../../backend/api/controllers/gestionProducto.php?action=editar", {
        method: "POST",
        body: datos,
    })
    .then(res => res.json())
    .then(response => {
        if (response.success) {
            cargarProductos();
            modalEditar.hide();
        } else {
            alert("Error al editar: " + response.message);
        }
    })
    .catch(error => console.error("Error:", error));
});

function abrirModalVenta(id) {
    const producto = productos.find(p => p.id == id);
    if (producto) {
        document.getElementById('vender_idProducto').value = producto.id;
        document.getElementById('vender_descripcion').value = producto.descripcion;
        document.getElementById('vender_queda').value = producto.queda;
        const cantidadInput = document.getElementById('vender_cantidad');
        cantidadInput.value = 1;
        cantidadInput.max = producto.queda; // Limitar la cantidad máxima a lo que queda
        modalVender.show();
    }
}

formVender.addEventListener("submit", (e) => {
    e.preventDefault();
    const datos = new FormData(formVender);
    fetch("../../backend/api/controllers/gestionProducto.php?action=vender", {
        method: "POST",
        body: datos,
    })
    .then(res => res.json())
    .then(response => {
        if (response.success) {
            cargarProductos();
            modalVender.hide();
        } else {
            alert("Error al vender: " + response.message);
        }
    })
    .catch(error => console.error("Error:", error));
});


// ===================================================================
// --- NUEVA LÓGICA PARA EL MODAL DE HISTORIAL Y SUS FILTROS ---
// ===================================================================

// MODIFICADO: Ahora guarda el historial y prepara los filtros
function verHistorial(idProducto, nombreProducto) {
    document.getElementById('nombreProducto').textContent = nombreProducto;

    // Limpiar filtros antes de mostrar
    limpiarFiltros();

    fetch(`../../backend/api/controllers/gestionProducto.php?action=historial&id=${idProducto}`)
        .then(res => res.json())
        .then(ventas => {
            historialActual = ventas; // Guardamos el historial completo
            renderizarHistorial(historialActual); // Renderizamos la tabla inicial
            modalVerVentas.show();
        })
        .catch(error => console.error('Error al cargar el historial:', error));
}

// NUEVO: Función para renderizar la tabla del historial
function renderizarHistorial(listaVentas) {
    const tablaVentasHistorial = document.getElementById('tablaVentasHistorial');
    tablaVentasHistorial.innerHTML = '';

    if (listaVentas.length === 0) {
        tablaVentasHistorial.innerHTML = '<tr><td colspan="4">No hay ventas para mostrar.</td></tr>';
        return;
    }

    listaVentas.forEach(venta => {
        const tr = document.createElement('tr');
        const fecha = new Date(venta.fecha_venta).toLocaleString('es-PE');
        const montoVenta = parseFloat(venta.monto_venta).toFixed(2);
        tr.innerHTML = `
            <td>${fecha}</td>
            <td>${venta.cantidad_vendida}</td>
            <td>S/. ${montoVenta}</td>
            <td>
                <button class="btn btn-sm btn-danger" onclick="eliminarVenta(${venta.id})" title="Eliminar Venta"><i class="fas fa-undo"></i></button>
            </td>
        `;
        tablaVentasHistorial.appendChild(tr);
    });
}

// NUEVO: Función principal que filtra y vuelve a renderizar el historial
function filtrarYRenderizarHistorial() {
    const fechaDesde = document.getElementById('filtroFechaDesde').value;
    const fechaHasta = document.getElementById('filtroFechaHasta').value;
    const cantidad = document.getElementById('filtroCantidad').value;
    const monto = document.getElementById('filtroMonto').value;

    let historialFiltrado = [...historialActual]; // Copiamos para no modificar el original

    if (fechaDesde) {
        historialFiltrado = historialFiltrado.filter(v => v.fecha_venta.split(' ')[0] >= fechaDesde);
    }
    if (fechaHasta) {
        historialFiltrado = historialFiltrado.filter(v => v.fecha_venta.split(' ')[0] <= fechaHasta);
    }
    if (cantidad) {
        historialFiltrado = historialFiltrado.filter(v => parseInt(v.cantidad_vendida) >= parseInt(cantidad));
    }
    if (monto) {
        historialFiltrado = historialFiltrado.filter(v => parseFloat(v.monto_venta) >= parseFloat(monto));
    }

    renderizarHistorial(historialFiltrado);
}

// NUEVO: Función para limpiar los campos de filtro
function limpiarFiltros() {
    document.getElementById('filtroFechaDesde').value = '';
    document.getElementById('filtroFechaHasta').value = '';
    document.getElementById('filtroCantidad').value = '';
    document.getElementById('filtroMonto').value = '';
    renderizarHistorial(historialActual); // Mostramos de nuevo la tabla completa
}

function eliminarVenta(idVenta) {
    if (!confirm("¿Seguro que quieres eliminar esta venta? El stock del producto se restaurará.")) return;

    fetch(`../../backend/api/controllers/gestionProducto.php?action=eliminar_venta`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ id_venta: idVenta })
    })
    .then(res => res.json())
    .then(response => {
        if (response.success) {
            cargarProductos(); // Recargamos la tabla principal para ver el stock actualizado
            modalVerVentas.hide(); // Cerramos el modal de historial
            alert("Venta eliminada y stock restaurado.");
        } else {
            alert("Error al eliminar la venta: " + response.message);
        }
    })
    .catch(error => console.error('Error al eliminar venta:', error));
}


// --- Event Listeners ---
document.addEventListener("DOMContentLoaded", () => {
    cargarProductos();

    // NUEVO: Listeners para los filtros del historial
    document.getElementById('filtroFechaDesde').addEventListener('input', filtrarYRenderizarHistorial);
    document.getElementById('filtroFechaHasta').addEventListener('input', filtrarYRenderizarHistorial);
    document.getElementById('filtroCantidad').addEventListener('input', filtrarYRenderizarHistorial);
    document.getElementById('filtroMonto').addEventListener('input', filtrarYRenderizarHistorial);
    document.getElementById('btnLimpiarFiltrosHistorial').addEventListener('click', limpiarFiltros);

    // Cálculos automáticos en modales (sin cambios)
    const setupCalculos = (modalSelector, inputs) => {
        const form = document.querySelector(modalSelector);
        const inicial = form.querySelector(inputs.inicial);
        const ingreso = form.querySelector(inputs.ingreso);
        const venta = form.querySelector(inputs.venta);
        const queda = form.querySelector(inputs.queda);
        const precioVenta = form.querySelector(inputs.precio_venta);
        const monto = form.querySelector(inputs.monto);

        function actualizarQueda() {
            const valInicial = parseInt(inicial.value) || 0;
            const valIngreso = parseInt(ingreso.value) || 0;
            const valVenta = parseInt(venta.value) || 0;
            queda.value = valInicial + valIngreso - valVenta;
        }

        function actualizarMonto() {
            const valVenta = parseInt(venta.value) || 0;
            const valPrecio = parseFloat(precioVenta.value) || 0;
            monto.value = (valVenta * valPrecio).toFixed(2);
        }

        [inicial, ingreso, venta].forEach(input => input.addEventListener('input', actualizarQueda));
        [venta, precioVenta].forEach(input => input.addEventListener('input', actualizarMonto));
    };

    setupCalculos('#formAgregarProducto', { inicial: '[name="inicial"]', ingreso: '[name="ingreso"]', venta: '[name="venta"]', queda: '[name="queda"]', precio_venta: '[name="precio_venta"]', monto: '[name="monto"]' });
    setupCalculos('#formEditarProducto', { inicial: '#editar_inicial', ingreso: '#editar_ingreso', venta: '#editar_venta', queda: '#editar_queda', precio_venta: '#editar_precio_venta', monto: '#editar_monto' });
});
