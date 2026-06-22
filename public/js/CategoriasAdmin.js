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
    // 2. LÓGICA DE DINERO CON CAMPO OCULTO
    //    - El input visible muestra el formato bonito ($1,500.00)
    //    - El input hidden (name=...) lleva el número puro (1500)
    //    Así Laravel SIEMPRE recibe un número limpio.
    // ==========================================================

    // Devuelve el número limpio o null si está vacío
    const numeroLimpio = (val) => {
        if (val === null || val === undefined) return null;
        const limpio = String(val).replace(/[^0-9.]/g, '');
        if (limpio === '' || limpio === '.') return null;
        const num = parseFloat(limpio);
        return isNaN(num) ? null : num;
    };

    // Sincroniza un input visible con su hidden asociado
    const sincronizarMoney = (inputVisible) => {
        const target = inputVisible.dataset.target;
        if (!target) return;
        const form = inputVisible.closest('form');
        const hidden = form?.querySelector(`input[type="hidden"][name="${target}"]`);
        if (!hidden) return;

        const num = numeroLimpio(inputVisible.value);
        hidden.value = (num === null) ? '' : num;
    };

    // Mientras escribe: actualiza el hidden en tiempo real
    document.addEventListener("input", (e) => {
        if (e.target.classList.contains("input-money")) {
            sincronizarMoney(e.target);
        }
    });

    // Al entrar al campo: muestra el número crudo para editar
    document.addEventListener("focusin", (e) => {
        if (e.target.classList.contains("input-money")) {
            const num = numeroLimpio(e.target.value);
            e.target.value = (num === null) ? '' : num;
        }
    });

    // Al salir del campo: muestra el formato bonito y sincroniza el hidden
    document.addEventListener("focusout", (e) => {
        if (e.target.classList.contains("input-money")) {
            const num = numeroLimpio(e.target.value);
            e.target.value = (num === null) ? '' : ('$' + num.toFixed(2));
            sincronizarMoney(e.target);
        }
    });

    // ==========================================================
    // 3. AL ENVIAR: nos aseguramos de que los hidden estén al día
    // ==========================================================
    const interceptarFormulario = (formId) => {
        const form = document.getElementById(formId);
        if (!form) return;
        form.addEventListener('submit', function () {
            form.querySelectorAll('.input-money').forEach(inputVisible => {
                sincronizarMoney(inputVisible);
            });
        });
    };

    interceptarFormulario('formCrear');
    interceptarFormulario('formEditar');
});

// ==========================================================
// ABRIR MODAL EDITAR CATEGORÍA
// ==========================================================
window.openEdit = function (id, codigo, nombre, descripcion, precioDia, precioSemana, precioMes, garantiaBase, activo, paquetesAsignados) {
    const form = document.getElementById('formEditar');

    if (form && form.dataset.action) {
        form.action = form.dataset.action.replace('__ID__', id);
    }

    // Inputs de texto
    document.getElementById('e_codigo').value = codigo;
    document.getElementById('e_nombre').value = nombre;
    document.getElementById('e_descripcion').value = descripcion ?? '';
    document.getElementById('e_activo').value = activo;

    // Helper: llena el visible (formato) y el hidden (número puro)
    const llenarMoney = (idVisible, idHidden, valor) => {
        const visible = document.getElementById(idVisible);
        const hidden = document.getElementById(idHidden);
        const num = parseFloat(valor);
        const valido = !isNaN(num) && num > 0;

        if (visible) visible.value = valido ? ('$' + num.toFixed(2)) : '';
        if (hidden) hidden.value = valido ? num : '';
    };

    llenarMoney('e_precio', 'e_precio_hidden', precioDia);
    llenarMoney('e_precio_semana', 'e_precio_semana_hidden', precioSemana);
    llenarMoney('e_precio_mes', 'e_precio_mes_hidden', precioMes);
    llenarMoney('e_garantia_base', 'e_garantia_base_hidden', garantiaBase);

    // Checar paquetes asignados
    document.querySelectorAll('.checkbox-paquete-edit').forEach(checkbox => {
        checkbox.checked = paquetesAsignados && paquetesAsignados.map(String).includes(String(checkbox.value));
    });

    const modal = document.getElementById('modalEditar');
    if (modal) modal.showModal();
};

// ==========================================================
// CERRAR MODAL
// ==========================================================
window.closeModal = function (idModal) {
    const modal = document.getElementById(idModal);
    if (modal) modal.close();
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
