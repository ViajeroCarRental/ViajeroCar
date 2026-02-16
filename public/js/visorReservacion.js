document.addEventListener('DOMContentLoaded', function () {

    const btnEditar     = document.getElementById('btnEditar');
    const btnActualizar = document.getElementById('btnActualizar');
    const btnAgregar    = document.getElementById('btnAgregarServicio');
    const btnCambiarCategoria = document.getElementById('btnCambiarCategoria');

    const campos    = document.querySelectorAll('.editable');
    const servicios = document.querySelectorAll('.editable-servicio');
    const btnEliminarServicio = document.querySelectorAll('.btnEliminarServicio');

    if (btnEditar) {
        btnEditar.addEventListener('click', function () {

            campos.forEach(el => {
                if (el.tagName === 'SELECT') {
                    el.disabled = false;
                } else {
                    el.removeAttribute('readonly');
                }
                el.classList.add('border-warning');
            });

            servicios.forEach(input => {
                input.removeAttribute('readonly');
                input.classList.add('border-warning');
            });

            btnActualizar.classList.remove('d-none');

            if (btnAgregar) btnAgregar.classList.remove('d-none');
            if (btnCambiarCategoria) btnCambiarCategoria.classList.remove('d-none');

            btnEliminarServicio.forEach(btn => btn.classList.remove('d-none'));
            btnEditar.classList.add('d-none');
        });
    }

    /* CAMBIAR CATEGORÍA */
    if (btnCambiarCategoria) {
        btnCambiarCategoria.addEventListener('click', () => {
            new bootstrap.Modal(document.getElementById('modalCategoria')).show();
        });
    }

    document.querySelectorAll('.elegirCategoria').forEach(btn => {
        btn.addEventListener('click', () => {
            document.getElementById('inputCategoria').value = btn.dataset.id;
            document.getElementById('imgVehiculo').src = btn.dataset.img;
            document.getElementById('textoCategoria').innerText = btn.dataset.txt;

            bootstrap.Modal.getInstance(
                document.getElementById('modalCategoria')
            ).hide();
        });
    });




    /* ELIMINAR SERVICIO */
    btnEliminarServicio.forEach(btn => {
        btn.addEventListener('click', function () {
            if (confirm('¿Quitar servicio?')) {
                this.closest('tr').remove();
            }
        });
    });

    /* AGREGAR SERVICIO */
    if (btnAgregar) {
        btnAgregar.addEventListener('click', function () {

            const tabla = document.getElementById('tablaServicios');
            if (!tabla) return;

            const index = tabla.rows.length;
            const fila = document.createElement('tr');

            fila.innerHTML = `
                <td>
                    <input type="text" name="servicios[${index}][nombre]"
                           class="form-control form-control-sm"
                           placeholder="Servicio">
                    <input type="hidden" name="servicios[${index}][id]" value="0">
                </td>
                <td>
                    <input type="number" name="servicios[${index}][cantidad]"
                           value="1" class="form-control form-control-sm">
                </td>
                <td>
                    <input type="number" name="servicios[${index}][precio]"
                           step="0.01" value="0"
                           class="form-control form-control-sm">
                </td>
                <td>$0.00</td>
                <td>
                    <button type="button" class="btn btn-sm btn-danger"
                            onclick="this.closest('tr').remove()">✖</button>
                </td>
            `;

            tabla.appendChild(fila);
        });
    }

});


