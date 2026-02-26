document.addEventListener('DOMContentLoaded', () => {

    /* ======================================================
       CARD 1 – VEHÍCULO / SERVICIOS
    ====================================================== */

    const btnEditarServicios  = document.getElementById('btnEditarServicios');
    const btnGuardarCard1     = document.getElementById('btnGuardarCard1');
    const btnCambiarCategoria = document.getElementById('btnCambiarCategoria');

    const contenedorAgregarServicio = document.getElementById('contenedorAgregarServicio');
    const selectServicio      = document.getElementById('selectServicio');
    const btnConfirmarAgregar = document.getElementById('btnConfirmarAgregar');
    const tablaServicios      = document.getElementById('tablaServicios');

    let servicioIndex = tablaServicios ? tablaServicios.rows.length : 0;

    /* ====== EDITAR CARD 1 ====== */
    if (btnEditarServicios) {
        btnEditarServicios.addEventListener('click', () => {

            document.querySelectorAll('.editable-servicio').forEach(input => {
                input.removeAttribute('readonly');
                input.classList.add('border-warning');
            });

            btnGuardarCard1.classList.remove('d-none');
            contenedorAgregarServicio.classList.remove('d-none');
            btnCambiarCategoria.classList.remove('d-none');

            document.querySelectorAll('.btnEliminarServicio')
                .forEach(btn => btn.classList.remove('d-none'));

            btnEditarServicios.classList.add('d-none');
        });
    }

    /* ====== AGREGAR SERVICIO ====== */
    if (btnConfirmarAgregar) {
        btnConfirmarAgregar.addEventListener('click', () => {

            if (!selectServicio.value) {
                alert('Selecciona un servicio');
                return;
            }

            const option = selectServicio.selectedOptions[0];
            const id     = option.value;
            const nombre = option.dataset.nombre;
            const precio = option.dataset.precio;

            const fila = document.createElement('tr');
            fila.innerHTML = `
                <td>
                    ${nombre}
                    <input type="hidden" name="servicios[${servicioIndex}][id]" value="${id}">
                </td>
                <td>
                    <input type="number" min="1"
                           name="servicios[${servicioIndex}][cantidad]"
                           class="form-control editable-servicio border-warning"
                           value="1">
                </td>
                <td>
                    <input type="number" step="0.01" min="0"
                           name="servicios[${servicioIndex}][precio]"
                           class="form-control editable-servicio border-warning"
                           value="${precio}">
                </td>
                <td>$${parseFloat(precio).toFixed(2)}</td>
                <td>
                    <button type="button" class="btn btn-sm btn-danger btnEliminarServicio">✖</button>
                </td>
            `;

            tablaServicios.appendChild(fila);
            servicioIndex++;
            selectServicio.value = '';
        });
    }

    /* ====== ELIMINAR SERVICIO ====== */
    document.addEventListener('click', e => {
        if (e.target.classList.contains('btnEliminarServicio')) {
            if (confirm('¿Quitar servicio?')) {
                e.target.closest('tr').remove();
            }
        }
    });

    /* ======================================================
       CATEGORÍA – IMAGEN + TEXTO
    ====================================================== */

    const modalCategoriaEl = document.getElementById('modalCategoria');

    const categoriasVehiculo = {
        1: { texto: 'C | Compacto',   img: '/img/aveo.png' },
        2: { texto: 'D | Medianos',    img: '/img/virtus.png' },
        3: { texto: 'E | Grande',     img: '/img/jetta.png' },
        4: { texto: 'F | Full Size',  img: '/img/camry.png' },
        5: { texto: 'IC | SUV Compacta' , img: '/img/renegade.png' },
        6: { texto: 'I | SUV Mediana' , img: '/img/seltos.png' },
        7: { texto: 'IB | SUV Familiar Compacta' , img: '/img/avanza.png' },
        8: { texto: 'M | Minivan' , img: '/img/Odyssey.png' },
        9: { texto: 'L | Van Pasajeros 13' , img: '/img/Urvan.png' },
        10: { texto: 'H | Pickup Doble Cabina' , img: '/img/Frontier.png' },
        11: { texto: 'HI | Pickup 4x4 Doble Cabina' , img: '/img/Tacoma.png' }
    };

    if (btnCambiarCategoria && modalCategoriaEl) {
        btnCambiarCategoria.addEventListener('click', () => {
            new bootstrap.Modal(modalCategoriaEl).show();
        });
    }

    document.querySelectorAll('.elegirCategoria').forEach(btn => {
        btn.addEventListener('click', () => {

            const id = btn.dataset.id;
            document.getElementById('inputCategoria').value = id;

            document.getElementById('imgVehiculo').src = categoriasVehiculo[id].img;
            document.getElementById('textoCategoria').innerText =
                categoriasVehiculo[id].texto;

            bootstrap.Modal.getInstance(modalCategoriaEl).hide();
        });
    });

    /* ======================================================
       CARD 2 – CLIENTE
    ====================================================== */

    const btnEditarCliente  = document.getElementById('btnEditarCliente');
    const btnGuardarCliente = document.getElementById('btnGuardarCliente');

    if (btnEditarCliente) {
        btnEditarCliente.addEventListener('click', () => {

            document.querySelectorAll('.editable-cliente').forEach(input => {
                input.removeAttribute('readonly');
                input.classList.add('border-warning');
            });

            btnGuardarCliente.classList.remove('d-none');
            btnEditarCliente.classList.add('d-none');
        });
    }

    /* ======================================================
       CARD 3 – ITINERARIO
    ====================================================== */

    const btnEditarItinerario  = document.getElementById('btnEditarItinerario');
    const btnGuardarItinerario = document.getElementById('btnGuardarItinerario');

    // Estado inicial: BLOQUEADO
    document.querySelectorAll('.editable-itinerario').forEach(el => {
        if (el.tagName !== 'SELECT') el.setAttribute('readonly', true);
        if (el.tagName === 'SELECT') el.classList.add('bloqueado');
    });

    if (btnEditarItinerario) {
        btnEditarItinerario.addEventListener('click', () => {

            document.querySelectorAll('.editable-itinerario').forEach(el => {

                if (el.tagName !== 'SELECT') {
                    el.removeAttribute('readonly');
                }

                if (el.tagName === 'SELECT') {
                    el.classList.remove('bloqueado');
                }

                el.classList.add('border-warning');
            });

            btnGuardarItinerario.classList.remove('d-none');
            btnEditarItinerario.classList.add('d-none');
        });
    }

    /* ======================================================
       VALIDACIÓN CARD 3 – FECHAS Y HORAS
    ====================================================== */

    const formCard3 =
        document.querySelector('input[name="card"][value="card3"]')?.closest('form');

    if (formCard3) {
        formCard3.addEventListener('submit', e => {

            const alertBox = document.getElementById('alertCard3');
            alertBox.classList.add('d-none');
            alertBox.innerText = '';

            const fi = formCard3.querySelector('[name="fecha_inicio"]').value;
            const ff = formCard3.querySelector('[name="fecha_fin"]').value;
            const hr = formCard3.querySelector('[name="hora_retiro"]').value;
            const he = formCard3.querySelector('[name="hora_entrega"]').value;

            if (!fi || !ff || !hr || !he) return;

            const inicio = new Date(`${fi}T${hr}`);
            const fin    = new Date(`${ff}T${he}`);

            if (fin <= inicio) {
                e.preventDefault();
                alertBox.innerText =
                    'La fecha y hora de entrega deben ser posteriores a la de retiro.';
                alertBox.classList.remove('d-none');
            }
        });
    }

});
