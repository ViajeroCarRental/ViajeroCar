document.addEventListener('DOMContentLoaded', () => {

/* ======================================================
   CONFIG ALERTIFY
====================================================== */

alertify.set('notifier','position','top-right');
alertify.defaults.glossary.ok = 'Aceptar';
alertify.defaults.glossary.cancel = 'Cancelar';


/* ======================================================
   VARIABLES GLOBALES
====================================================== */

let cambiosDetectados = false;


/* ======================================================
   DETECTOR DE CAMBIOS
====================================================== */

function marcarCambios(){

    if(!cambiosDetectados){

        cambiosDetectados = true;

        const btnConfirmarCambios = document.getElementById('btnConfirmarCambios');

        if(btnConfirmarCambios){

            btnConfirmarCambios.disabled = false;

            btnConfirmarCambios.classList.remove('btn-secondary');

            btnConfirmarCambios.classList.add('btn-success');
        }

        alertify.success('Se detectaron cambios en la reservación');
    }
}


/* ======================================================
   RECALCULAR TOTALES
====================================================== */

function recalcularTotales(){

    let subtotal = 0;

    document.querySelectorAll('#tablaServicios tr').forEach(fila=>{

        const cantidadInput = fila.querySelector('input[name*="[cantidad]"]');
        const precioInput   = fila.querySelector('input[name*="[precio]"]');

        if(!cantidadInput || !precioInput) return;

        const cantidad = parseFloat(cantidadInput.value) || 0;
        const precio   = parseFloat(precioInput.value) || 0;

        const total = cantidad * precio;

        fila.children[3].innerText = "$" + total.toFixed(2);

        subtotal += total;
    });

    const iva = subtotal * 0.16;
    const total = subtotal + iva;

    const subtotalLabel = document.querySelector('p strong:contains("Subtotal")');

    const labels = document.querySelectorAll('p');

    labels.forEach(label=>{

        if(label.innerText.includes('Subtotal:')){

            label.innerHTML = "<strong>Subtotal:</strong> $" + subtotal.toFixed(2);

        }

        if(label.innerText.includes('IVA:')){

            label.innerHTML = "<strong>IVA:</strong> $" + iva.toFixed(2);

        }

        if(label.innerText.includes('Total:')){

            label.innerHTML = "<strong>Total:</strong> $" + total.toFixed(2);

        }

    });
}


/* ======================================================
   DETECTAR CAMBIOS EN INPUTS
====================================================== */

document.querySelectorAll('input,select,textarea').forEach(el=>{

    el.addEventListener('change',()=>{

        marcarCambios();
        recalcularTotales();

    });

});


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


/* ===== EDITAR CARD 1 ===== */

if(btnEditarServicios){

    btnEditarServicios.addEventListener('click',()=>{

        document.querySelectorAll('.editable-servicio').forEach(input=>{

            input.removeAttribute('readonly');
            input.classList.add('border-warning');

        });

        btnGuardarCard1.classList.remove('d-none');
        contenedorAgregarServicio.classList.remove('d-none');
        btnCambiarCategoria.classList.remove('d-none');

        document.querySelectorAll('.btnEliminarServicio')
        .forEach(btn=>btn.classList.remove('d-none'));

        btnEditarServicios.classList.add('d-none');
    });
}


/* ===== AGREGAR SERVICIO ===== */

if(btnConfirmarAgregar){

    btnConfirmarAgregar.addEventListener('click',()=>{

        if(!selectServicio.value){

            alertify.alert('Selecciona un servicio');
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
            <input type="number"
            min="1"
            name="servicios[${servicioIndex}][cantidad]"
            class="form-control editable-servicio border-warning"
            value="1">
        </td>

        <td>
            $${parseFloat(precio).toFixed(2)}
            <input type="hidden"
            name="servicios[${servicioIndex}][precio]"
            value="${precio}">
        </td>

        <td>$${parseFloat(precio).toFixed(2)}</td>

        <td>
            <button type="button"
            class="btn btn-sm btn-danger btnEliminarServicio">
            ✖
            </button>
        </td>
        `;

        tablaServicios.appendChild(fila);

        servicioIndex++;

        selectServicio.value='';

        marcarCambios();

        recalcularTotales();

        alertify.success('Servicio agregado');
    });
}


/* ===== ELIMINAR SERVICIO ===== */

document.addEventListener('click',e=>{

    if(e.target.classList.contains('btnEliminarServicio')){

        const fila = e.target.closest('tr');

        alertify.confirm(
        '¿Quitar servicio?',
        function(){

            fila.remove();

            marcarCambios();

            recalcularTotales();

            alertify.success('Servicio eliminado');

        },
        function(){}
        );
    }

});


/* ======================================================
   CATEGORÍA
====================================================== */

const modalCategoriaEl = document.getElementById('modalCategoria');

if(btnCambiarCategoria && modalCategoriaEl){

    btnCambiarCategoria.addEventListener('click',()=>{

        new bootstrap.Modal(modalCategoriaEl).show();

    });

}

document.querySelectorAll('.elegirCategoria').forEach(btn=>{

    btn.addEventListener('click',()=>{

        const id = btn.dataset.id;
        const texto = btn.dataset.texto;
        const img = btn.dataset.img;

        document.getElementById('inputCategoria').value = id;
        document.getElementById('imgVehiculo').src = img;
        document.getElementById('textoCategoria').innerText = texto;

        bootstrap.Modal.getInstance(modalCategoriaEl).hide();

        marcarCambios();

        alertify.success('Categoría cambiada');
    });

});


/* ======================================================
   CARD 2 – CLIENTE
====================================================== */

const btnEditarCliente  = document.getElementById('btnEditarCliente');
const btnGuardarCliente = document.getElementById('btnGuardarCliente');

if(btnEditarCliente){

    btnEditarCliente.addEventListener('click',()=>{

        document.querySelectorAll('.editable-cliente').forEach(input=>{

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

document.querySelectorAll('.editable-itinerario').forEach(el=>{

    if(el.tagName!=='SELECT') el.setAttribute('readonly',true);
    if(el.tagName==='SELECT') el.classList.add('bloqueado');

});


if(btnEditarItinerario){

    btnEditarItinerario.addEventListener('click',()=>{

        document.querySelectorAll('.editable-itinerario').forEach(el=>{

            if(el.tagName!=='SELECT') el.removeAttribute('readonly');
            if(el.tagName==='SELECT') el.classList.remove('bloqueado');

            el.classList.add('border-warning');

        });

        btnGuardarItinerario.classList.remove('d-none');
        btnEditarItinerario.classList.add('d-none');

    });

}


/* ======================================================
   VALIDACIÓN FECHAS
====================================================== */

const formCard3 =
document.querySelector('input[name="card"][value="card3"]')?.closest('form');

if(formCard3){

    formCard3.addEventListener('submit',e=>{

        const alertBox = document.getElementById('alertCard3');

        alertBox.classList.add('d-none');
        alertBox.innerText='';

        const fi = formCard3.querySelector('[name="fecha_inicio"]').value;
        const ff = formCard3.querySelector('[name="fecha_fin"]').value;
        const hr = formCard3.querySelector('[name="hora_retiro"]').value;
        const he = formCard3.querySelector('[name="hora_entrega"]').value;

        if(!fi || !ff || !hr || !he) return;

        const inicio = new Date(`${fi}T${hr}`);
        const fin    = new Date(`${ff}T${he}`);

        if(fin <= inicio){

            e.preventDefault();

            alertBox.innerText =
            'La fecha y hora de entrega deben ser posteriores a la de retiro.';

            alertBox.classList.remove('d-none');
        }
    });

}


/* ======================================================
   CONFIRMAR CAMBIOS – REENVIAR CORREO
====================================================== */

const btnConfirmarCambios = document.getElementById('btnConfirmarCambios');

if(btnConfirmarCambios){

    btnConfirmarCambios.disabled = true;

    btnConfirmarCambios.classList.add('btn-secondary');

    btnConfirmarCambios.addEventListener('click',function(e){

        e.preventDefault();

        const form = this.closest('form');

        if(!cambiosDetectados){

            alertify.alert(
            'No hay cambios',
            'No se detectaron modificaciones en la reservación.'
            );

            return;
        }

        alertify.confirm(
        '¿Enviar correo?',
        'Se enviará nuevamente el correo con los cambios realizados.',
        function(){

            form.submit();

        },
        function(){}
        );

    });

}

});
