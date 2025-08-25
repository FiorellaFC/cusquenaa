document.addEventListener('DOMContentLoaded', function () {
    const tablaBalance = document.getElementById('tablaBalance');
    const formAgregar = document.getElementById('formAgregarBalance');
    const formEditar = document.getElementById('formEditarBalance');
    const buscarNombre = document.getElementById('buscarNombre');
    const buscarMes = document.getElementById('buscarMes');
    const buscarAnio = document.getElementById('buscarAnio');
    const btnBuscar = document.getElementById('btnBuscar');
    const totalGeneral = document.getElementById('totalGeneral');

    let balances = [];

    function cargarBalances() {
        const nombre = buscarNombre.value.trim();
        const mes = buscarMes.value;
        const anio = buscarAnio.value;

        const params = new URLSearchParams({
            accion: 'listar',
            buscarNombre: nombre,
            buscarMes: mes,
            buscarAnio: anio
        });

        fetch(`../../backend/api/controllers/balanceLubricentro.php?${params}`)
            .then(res => res.json())
            .then(data => {
                balances = data;
                renderTabla(balances);
                calcularTotal(balances);
            })
            .catch(err => console.error('Error al cargar balances:', err));
    }

    function renderTabla(data) {
        tablaBalance.innerHTML = '';
        if (data.length === 0) {
            tablaBalance.innerHTML = `<tr><td colspan="6">No se encontraron balances</td></tr>`;
            return;
        }

        data.forEach(balance => {
            const fila = document.createElement('tr');
            fila.innerHTML = `
                <td>${balance.nombre_descripcion}</td>
                <td>${balance.tipo_balance}</td>
                <td>${balance.mes}</td>
                <td>${balance.anio}</td>
                <td>S/ ${parseFloat(balance.monto).toFixed(2)}</td>
                <td>
                    <button class="btn btn-sm btn-warning me-1 btn-editar" data-id="${balance.id}"><i class="fas fa-edit"></i></button>
                    <button class="btn btn-sm btn-danger btn-eliminar" data-id="${balance.id}"><i class="fas fa-trash-alt"></i></button>
                </td>
            `;
            tablaBalance.appendChild(fila);
        });
    }

    function calcularTotal(data) {
        const total = data.reduce((sum, item) => sum + parseFloat(item.monto || 0), 0);
        totalGeneral.textContent = `Total General: S/. ${total.toFixed(2)}`;
    }

    formAgregar.addEventListener('submit', function (e) {
        e.preventDefault();
        const formData = new FormData(formAgregar);
        formData.append('accion', 'registrar');

        fetch('../../backend/api/controllers/balanceLubricentro.php', {
            method: 'POST',
            body: formData
        })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    formAgregar.reset();
                    bootstrap.Modal.getInstance(document.getElementById('modalAgregarBalance')).hide();
                    cargarBalances();
                } else {
                    alert('Error al registrar balance');
                }
            })
            .catch(err => console.error(err));
    });

    formEditar.addEventListener('submit', function (e) {
        e.preventDefault();
        const formData = new FormData(formEditar);
        formData.append('accion', 'modificar');

        fetch('../../backend/api/controllers/balanceLubricentro.php', {
            method: 'POST',
            body: formData
        })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    bootstrap.Modal.getInstance(document.getElementById('modalEditarBalance')).hide();
                    cargarBalances();
                } else {
                    alert('Error al modificar balance: ' + (data.message || ''));
                }
            })
            .catch(err => console.error(err));
    });

    tablaBalance.addEventListener('click', function (e) {
        if (e.target.closest('.btn-editar')) {
            const id = e.target.closest('.btn-editar').dataset.id;
            const balance = balances.find(b => b.id == id);

            if (balance) {
                document.getElementById('editBalanceId').value = balance.id;
                document.getElementById('edit_nombre').value = balance.nombre_descripcion;
                document.getElementById('editarTipoBalance').value = balance.tipo_balance;
                document.querySelector('#formEditarBalance select[name="mes"]').value = balance.mes;
                document.querySelector('#formEditarBalance input[name="anio"]').value = balance.anio;
                document.getElementById('edit_monto').value = balance.monto;

                new bootstrap.Modal(document.getElementById('modalEditarBalance')).show();
            }
        }

        if (e.target.closest('.btn-eliminar')) {
            const id = e.target.closest('.btn-eliminar').dataset.id;
            if (confirm('¿Está seguro de eliminar este balance?')) {
                fetch(`../../backend/api/controllers/balanceLubricentro.php?accion=eliminar&id=${id}`)
                    .then(res => res.json())
                    .then(data => {
                        if (data.success) {
                            cargarBalances();
                        } else {
                            alert('Error al eliminar balance');
                        }
                    })
                    .catch(err => console.error(err));
            }
        }
    });

    btnBuscar.addEventListener('click', cargarBalances);

    [buscarNombre, buscarMes, buscarAnio].forEach(input => {
        input.addEventListener('keyup', function (e) {
            if (e.key === 'Enter') {
                cargarBalances();
            }
        });
    });

    cargarBalances(); // carga inicial
});
