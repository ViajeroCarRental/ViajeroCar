document.addEventListener("DOMContentLoaded", () => {

    // ==========================================================
    // 1. CONVERTIR A MAYÚSCULAS EN TIEMPO REAL
    // ==========================================================
    document.querySelectorAll('.uppercase-input').forEach(input => {
        input.addEventListener('input', function () {
            this.value = this.value.toUpperCase();
        });
    });

    // ==========================================================
    // 2. FORMATEO DINÁMICO DE DINERO Y PORCENTAJE
    // ==========================================================
    const limpiarNumero = (val) => {
        if (!val) return 0;
        const limpio = String(val).replace(/[^0-9.]/g, '');
        return parseFloat(limpio) || 0;
    };

    // Al hacer clic dentro del input (quita los signos)
    document.addEventListener("focusin", (e) => {
        if (e.target.classList.contains("input-money") || e.target.classList.contains("input-percent")) {
            let valorLimpio = e.target.value.replace(/[^0-9.]/g, '');
            if (parseFloat(valorLimpio) === 0) valorLimpio = "";
            e.target.value = valorLimpio;
        }
    });

    // Al salir del input (agrega los signos)
    document.addEventListener("focusout", (e) => {
        if (e.target.classList.contains("input-money")) {
            const num = parseFloat(e.target.value) || 0;
            e.target.value = "$" + num.toFixed(2);
        } else if (e.target.classList.contains("input-percent")) {
            const num = parseFloat(e.target.value) || 0;
            e.target.value = num.toFixed(2) + " %";
        }
    });

    // ==========================================================
    // 3. LIMPIAR DATOS JUSTO ANTES DE ENVIAR EL FORMULARIO
    // ==========================================================
    const interceptarFormulario = (formId) => {
        const form = document.getElementById(formId);
        if (form) {
            form.addEventListener('submit', function () {
                // Quitamos el formato visual para que Laravel reciba números puros
                form.querySelectorAll('.input-money, .input-percent').forEach(input => {
                    input.value = limpiarNumero(input.value);
                });
            });
        }
    };

    interceptarFormulario('formCrear');
    interceptarFormulario('formEditar');
});

// ==========================================================
// ABRIR MODAL EDITAR CATEGORÍA
// ==========================================================
window.openEdit = function (id, codigo, nombre, precioDia, precioSemana, precioMes, descuento, garantiaBase, activo, paquetesAsignados) {
    const form = document.getElementById('formEditar');

    if (form && form.dataset.action) {
        form.action = form.dataset.action.replace('__ID__', id);
    }

    // Llenar inputs de texto
    document.getElementById('e_codigo').value = codigo;
    document.getElementById('e_nombre').value = nombre;
    document.getElementById('e_activo').value = activo;

    // Llenar inputs formateados con los signos iniciales
    document.getElementById('e_precio').value = "$" + parseFloat(precioDia).toFixed(2);
    document.getElementById('e_precio_semana').value = "$" + parseFloat(precioSemana).toFixed(2);
    document.getElementById('e_precio_mes').value = "$" + parseFloat(precioMes).toFixed(2);
    document.getElementById('e_descuento').value = parseFloat(descuento).toFixed(2) + " %";
    document.getElementById('e_garantia_base').value = "$" + parseFloat(garantiaBase).toFixed(2);

    // Lógica para checar los paquetes
    document.querySelectorAll('.checkbox-paquete-edit').forEach(checkbox => {
        checkbox.checked = paquetesAsignados && paquetesAsignados.map(String).includes(String(checkbox.value));
    });

    const modal = document.getElementById('modalEditar');
    if (modal) {
        modal.showModal();
    }
};

// ==========================================================
// CERRAR MODAL
// ==========================================================
window.closeModal = function (idModal) {
    const modal = document.getElementById(idModal);
    if (modal) {
        modal.close();
    }
};

// ==========================================================
// CONFIRMACIÓN PREMIUM AL ELIMINAR (SweetAlert2)
// ==========================================================
window.confirmarEliminacion = function (event, formId) {
    event.preventDefault();

    Swal.fire({
        title: '¿Eliminar Categoría?',
        text: "¡No podrás revertir esto y afectará a los autos vinculados!",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#ff1e2d',
        cancelButtonColor: '#e5e7eb',
        confirmButtonText: 'Sí, eliminar',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            document.getElementById(formId).submit();
        }
    });
};