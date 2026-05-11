(function () {
  "use strict";

/* =========================================
   01 FUNCIÓN DE ALERTA CON SWEETALERT2
========================================= */
function mostrarToast(mensaje, tipo = 'warning') {
    let iconColor = '#f59e0b';
    let bgColor = '#fffbeb';
    let borderColor = '#f59e0b';

    if (tipo === 'error') {
        iconColor = '#ef4444';
        bgColor = '#fef2f2';
        borderColor = '#ef4444';
    } else if (tipo === 'success') {
        iconColor = '#10b981';
        bgColor = '#ecfdf5';
        borderColor = '#10b981';
    } else if (tipo === 'info') {
        iconColor = '#3b82f6';
        bgColor = '#eff6ff';
        borderColor = '#3b82f6';
    }

    const Toast = Swal.mixin({
        toast: true,
        position: 'center',
        showConfirmButton: false,
        timer: 2500,
        timerProgressBar: true,
        didOpen: (toast) => {
            toast.addEventListener('mouseenter', Swal.stopTimer);
            toast.addEventListener('mouseleave', Swal.resumeTimer);
        },
        customClass: {
            popup: 'custom-toast-center'
        }
    });

    if (!document.querySelector('#toast-center-style')) {
        const style = document.createElement('style');
        style.id = 'toast-center-style';
        style.textContent = `
            .custom-toast-center {
                border-radius: 12px !important;
                border-left: 4px solid ${borderColor} !important;
                box-shadow: 0 10px 25px -5px rgba(0,0,0,0.1) !important;
                font-weight: 500 !important;
                min-width: 300px !important;
            }
        `;
        document.head.appendChild(style);
    }

    let icon = 'warning';
    if (tipo === 'error') icon = 'error';
    if (tipo === 'success') icon = 'success';
    if (tipo === 'info') icon = 'info';

    Toast.fire({
        icon: icon,
        title: mensaje,
        background: bgColor,
        iconColor: iconColor
    });
}

/* =========================================
   02 FUNCIONES DE VALIDACIÓN POR PASOS
========================================= */
function mostrarError(elemento, mensaje) {
    if (!elemento) return;

    limpiarError(elemento);

    if (elemento.tagName === 'SELECT' && typeof $ !== 'undefined' && $(elemento).data('select2')) {
        elemento.classList.add('field-error');
        const container = $(elemento).next('.select2-container');
        if (container.length) {
            const selection = container.find('.select2-selection');
            selection.addClass('field-error');
            selection.css({
                'border': '2px solid #e53935 !important',
                'border-radius': '14px'
            });
        }
    }

    else if (elemento.id === 'fecha_inicio_ui' || elemento.id === 'fecha_fin_ui') {
        elemento.classList.add('field-error');
        if (elemento._flatpickr && elemento._flatpickr.altInput) {
            elemento._flatpickr.altInput.classList.add('field-error');
        }
    }

    else if (elemento.id === 'hora_retiro_ui' || elemento.id === 'hora_entrega_ui') {
        elemento.classList.add('field-error');
        const container = elemento.closest('.dt-field-admin');
        if (container) {
            const selectHora = container.querySelector('.tp-hour');
            if (selectHora) {
                selectHora.classList.add('field-error');
            }
        }
    }

    else if (elemento.classList && elemento.classList.contains('tp-hour')) {
        elemento.classList.add('field-error');
        const container = elemento.closest('.dt-field-admin');
        if (container) {
            const inputUI = container.querySelector('.input-buscador-admin');
            if (inputUI) {
                inputUI.classList.add('field-error');
            }
        }
    }

    else if (elemento.tagName === 'BUTTON') {
        elemento.classList.add('field-error');
        elemento.style.border = '2px solid #e53935';
        elemento.style.backgroundColor = '#fee2e2';
    }

    else {
        elemento.classList.add('field-error');
    }

    agregarMensajeErrorUnico(elemento, mensaje);
}

function agregarMensajeErrorUnico(elemento, mensaje) {
    let contenedor = null;

    if (elemento.id === 'hora_retiro_ui' || elemento.id === 'hora_entrega_ui') {
        contenedor = elemento.closest('.dt-field-admin');
    } else if (elemento.classList && elemento.classList.contains('tp-hour')) {
        contenedor = elemento.closest('.dt-field-admin');
    } else {
        contenedor = elemento.closest('.dt-field-admin') ||
                     elemento.closest('.time-field-admin') ||
                     elemento.closest('.field-admin');
    }

    if (!contenedor) {
        contenedor = elemento.parentElement;
    }

    if (!contenedor) return;

    const msgExistente = contenedor.querySelector('.error-msg');
    if (msgExistente) msgExistente.remove();

    const errorMsg = document.createElement('span');
    errorMsg.className = 'error-msg';
    errorMsg.textContent = mensaje;
    contenedor.style.position = 'relative';
    contenedor.appendChild(errorMsg);
}

function mostrarExito(elemento) {
    if (!elemento) return;
    limpiarError(elemento);

    if (elemento.id === 'fecha_inicio_ui' || elemento.id === 'fecha_fin_ui') {
        elemento.classList.add('field-success');

        if (elemento._flatpickr && elemento._flatpickr.altInput) {
            elemento._flatpickr.altInput.classList.add('field-success');
        }
    }

    else if (elemento.id === 'hora_retiro_ui' || elemento.id === 'hora_entrega_ui') {
        elemento.classList.add('field-success');

        const container = elemento.closest('.dt-field-admin');
        if (container) {
            const selectHora = container.querySelector('.tp-hour');
            if (selectHora) {
                selectHora.classList.add('field-success');
            }
        }
    }

    else if (elemento.classList && elemento.classList.contains('tp-hour')) {
        elemento.classList.add('field-success');

        const container = elemento.closest('.dt-field-admin');
        if (container) {
            const inputUI = container.querySelector('.input-buscador-admin');
            if (inputUI) {
                inputUI.classList.add('field-success');
            }
        }
    }

    else if (elemento.id === 'nombre_cliente' ||
             elemento.id === 'apellidos_cliente' ||
             elemento.id === 'email_cliente' ||
             elemento.id === 'telefono_ui' ||
             elemento.id === 'no_vuelo') {
        elemento.classList.add('field-success');
    }

    else {
        elemento.classList.add('field-success');

        const nextEl = elemento.nextElementSibling;
        if (nextEl && nextEl.classList.contains('select2-container')) {
            const box = nextEl.querySelector('.select2-selection');
            if (box) box.classList.add('field-success');
        }
    }
}

function limpiarError(elemento) {
    if (!elemento) return;

    if (elemento.tagName === 'BUTTON') {
        elemento.style.border = '';
        elemento.style.backgroundColor = '';
    }

    if (elemento.tagName === 'SELECT' && typeof $ !== 'undefined' && $(elemento).data('select2')) {
        elemento.classList.remove('field-error', 'field-success');
        const container = $(elemento).next('.select2-container');
        if (container.length) {
            const selection = container.find('.select2-selection');
            selection.removeClass('field-error field-success');
            selection.css('border', '');
        }
    }

    else if (elemento.id === 'fecha_inicio_ui' || elemento.id === 'fecha_fin_ui') {
        elemento.classList.remove('field-error', 'field-success');
        if (elemento._flatpickr && elemento._flatpickr.altInput) {
            elemento._flatpickr.altInput.classList.remove('field-error', 'field-success');
        }
    }

    else if (elemento.id === 'hora_retiro_ui' || elemento.id === 'hora_entrega_ui') {
        elemento.classList.remove('field-error', 'field-success');
        const container = elemento.closest('.dt-field-admin');
        if (container) {
            const selectHora = container.querySelector('.tp-hour');
            if (selectHora) {
                selectHora.classList.remove('field-error', 'field-success');
            }
        }
    }

    else if (elemento.classList && elemento.classList.contains('tp-hour')) {
        elemento.classList.remove('field-error', 'field-success');
        const container = elemento.closest('.dt-field-admin');
        if (container) {
            const inputUI = container.querySelector('.input-buscador-admin');
            if (inputUI) {
                inputUI.classList.remove('field-error', 'field-success');
            }
        }
    }

    else {
        elemento.classList.remove('field-error', 'field-success');
    }

    let contenedor = elemento.closest('.dt-field-admin, .time-field-admin, .field-admin, .sg-col-location-admin, .picker-row');
    if (!contenedor && elemento.parentElement) {
        contenedor = elemento.parentElement;
    }

    if (contenedor) {
        const msg = contenedor.querySelector('.error-msg');
        if (msg) msg.remove();
    }
}

function validarClienteVisual() {
    let todoOk = true;

    const nombre = document.getElementById("nombre_cliente");
    if (nombre) {
        if (!nombre.value.trim()) {
            mostrarError(nombre, 'El nombre es obligatorio');
            todoOk = false;
        } else {
            mostrarExito(nombre);
        }
    }

    const apellidos = document.getElementById("apellidos_cliente");
    if (apellidos) {
        if (!apellidos.value.trim()) {
            mostrarError(apellidos, 'Los apellidos son obligatorios');
            todoOk = false;
        } else {
            mostrarExito(apellidos);
        }
    }

    const email = document.getElementById("email_cliente");
    if (email) {
        const emailVal = email.value.trim();
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (!emailVal) {
            mostrarError(email, 'El email es obligatorio');
            todoOk = false;
        } else if (!emailRegex.test(emailVal)) {
            mostrarError(email, 'El email no tiene un formato válido');
            todoOk = false;
        } else {
            mostrarExito(email);
        }
    }

    const telefono = document.getElementById("telefono_ui");
    if (telefono) {
        const telVal = telefono.value.trim().replace(/\s+/g, "");
        if (!telVal) {
            mostrarError(telefono, 'El teléfono es obligatorio');
            todoOk = false;
        } else if (telVal.length < 8) {
            mostrarError(telefono, 'Teléfono inválido (mínimo 8 dígitos)');
            todoOk = false;
        } else {
            mostrarExito(telefono);
        }
    }

    if (typeof isAirportSelected === 'function' && isAirportSelected()) {
        const vuelo = document.getElementById("no_vuelo");
        if (vuelo && !vuelo.value.trim()) {
            mostrarError(vuelo, 'Número de vuelo obligatorio para Aeropuerto');
            todoOk = false;
        } else if (vuelo && vuelo.value.trim()) {
            mostrarExito(vuelo);
        }
    }

    return todoOk;
}

function validarCamposUbicacion() {
    let todoOk = true;

    const sucursalRetiro = document.getElementById('sucursal_retiro');
    if (sucursalRetiro) {
        const valRetiro = (typeof $ !== 'undefined' && $(sucursalRetiro).data('select2'))
            ? $(sucursalRetiro).val()
            : sucursalRetiro.value;

        if (!valRetiro || valRetiro === "") {
            mostrarError(sucursalRetiro, 'Ubicación de recogida requerida');
            todoOk = false;
        } else {
            mostrarExito(sucursalRetiro);
        }
    }

    const checkbox = document.getElementById('differentDropoffAdmin');
    const isDifferentDropoff = checkbox && checkbox.checked;

    const sucursalEntrega = document.getElementById('sucursal_entrega');

    if (isDifferentDropoff) {
        if (sucursalEntrega) {
            const valEntrega = (typeof $ !== 'undefined' && $(sucursalEntrega).data('select2'))
                ? $(sucursalEntrega).val()
                : sucursalEntrega.value;

            if (!valEntrega || valEntrega === "") {
                mostrarError(sucursalEntrega, 'Ubicación de devolución requerida');
                todoOk = false;
            } else {
                mostrarExito(sucursalEntrega);
            }
        }
    } else {
        if (sucursalEntrega) {
            limpiarError(sucursalEntrega);
            mostrarExito(sucursalEntrega);
        }
    }

    const fechaInicio = document.getElementById('fecha_inicio_ui');
    if (fechaInicio) {
        const tieneFecha = fechaInicio.value && fechaInicio.value.trim() !== "";
        if (!tieneFecha) {
            mostrarError(fechaInicio, 'Fecha requerida');
            todoOk = false;
        } else {
            mostrarExito(fechaInicio);
        }
    }

    const fechaFin = document.getElementById('fecha_fin_ui');
    if (fechaFin) {
        const tieneFecha = fechaFin.value && fechaFin.value.trim() !== "";
        if (!tieneFecha) {
            mostrarError(fechaFin, 'Fecha requerida');
            todoOk = false;
        } else {
            mostrarExito(fechaFin);
        }
    }

    const horaRetiro = document.getElementById('hora_retiro_ui');
    if (horaRetiro) {
        const timeContainer = horaRetiro.closest('.time-field-admin');
        let horaValida = false;
        if (timeContainer) {
            const selectHora = timeContainer.querySelector('.tp-hour');
            if (selectHora && selectHora.value && selectHora.value !== "") {
                horaValida = true;
            }
        }
        if (!horaValida) {
            mostrarError(horaRetiro, 'Hora requerida');
            todoOk = false;
        } else {
            mostrarExito(horaRetiro);
        }
    }

    const horaEntrega = document.getElementById('hora_entrega_ui');
    if (horaEntrega) {
        const timeContainer = horaEntrega.closest('.time-field-admin');
        let horaValida = false;
        if (timeContainer) {
            const selectHora = timeContainer.querySelector('.tp-hour');
            if (selectHora && selectHora.value && selectHora.value !== "") {
                horaValida = true;
            }
        }
        if (!horaValida) {
            mostrarError(horaEntrega, 'Hora requerida');
            todoOk = false;
        } else {
            mostrarExito(horaEntrega);
        }
    }

    if (todoOk && !validarHoraDevolucionPosterior()) {
        todoOk = false;
    }

    return todoOk;
}

function validarHoraDevolucionPosterior() {
    const fechaInicioUI = document.getElementById("fecha_inicio_ui");
    const fechaFinUI = document.getElementById("fecha_fin_ui");

    if (!fechaInicioUI || !fechaFinUI) return true;

    const fechaInicio = fechaInicioUI.value;
    const fechaFin = fechaFinUI.value;

    if (!fechaInicio || !fechaFin) return true;

    const normalizarFecha = (f) => {
        if (!f) return null;
        if (f.includes('/')) {
            const [d, m, y] = f.split('/');
            return `${y}-${m}-${d}`;
        }
        return f;
    };

    if (normalizarFecha(fechaInicio) !== normalizarFecha(fechaFin)) {
        const horaEntregaUI = document.getElementById("hora_entrega_ui");
        if (horaEntregaUI) {
            limpiarError(horaEntregaUI);
            mostrarExito(horaEntregaUI);
        }
        return true;
    }

    const horaRetiroSelect = document.querySelector('#hora_retiro_ui')?.closest('.dt-field-admin')?.querySelector('.tp-hour');
    const horaEntregaSelect = document.querySelector('#hora_entrega_ui')?.closest('.dt-field-admin')?.querySelector('.tp-hour');

    if (!horaRetiroSelect || !horaEntregaSelect) return true;

    const horaRetiro = horaRetiroSelect.value;
    const horaEntrega = horaEntregaSelect.value;

    if (!horaRetiro || !horaEntrega) return true;

    if (parseInt(horaEntrega) <= parseInt(horaRetiro)) {
        const horaEntregaUI = document.getElementById("hora_entrega_ui");
        if (horaEntregaUI) {
            mostrarError(horaEntregaUI, 'La hora de devolución debe ser mayor');
        }
        if (horaEntregaSelect) {
            horaEntregaSelect.classList.add('field-error');
        }
        mostrarToast('En la misma fecha, la devolución debe ser después del retiro', 'warning');
        return false;
    }

    const horaEntregaUI = document.getElementById("hora_entrega_ui");
    if (horaEntregaUI) {
        limpiarError(horaEntregaUI);
        mostrarExito(horaEntregaUI);
    }

    if (horaEntregaSelect) {
        limpiarError(horaEntregaSelect);
        horaEntregaSelect.classList.add('field-success');
    }

    return true;
}
/* =========================================
   03 BLOQUEO DE ACORDEONES
========================================= */
let categoriaDesbloqueada = false;
let adicionalesDesbloqueada = false;
let proteccionesDesbloqueada = false;
let clienteDesbloqueada = false;
let flujoCompletado = false;

function actualizarEstadoSeccion(seccion, desbloqueada) {
    if (!seccion) return;
    const head = seccion.querySelector('.stack-head');

    if (desbloqueada) {
        seccion.classList.remove('locked');
        if (head) {
            head.style.opacity = '1';
            head.style.pointerEvents = 'auto';
            head.style.cursor = 'pointer';
        }
    } else {
        seccion.classList.add('locked');
        if (head) {
            head.style.opacity = '0.6';
            head.style.pointerEvents = 'none';
            head.style.cursor = 'not-allowed';
        }
        const body = seccion.querySelector('.stack-body');
        const indicator = seccion.querySelector('.stack-indicator');
        if (body && body.classList.contains('expanded')) {
            body.classList.remove('expanded');
        }
        if (indicator && indicator.classList.contains('expanded')) {
            indicator.classList.remove('expanded');
        }
    }
}

function actualizarTodasSecciones() {
    const seccionCategoria = document.querySelector('.acordeon-item[data-seccion="categoria"]');
    const seccionAdicionales = document.querySelector('.acordeon-item[data-seccion="adicionales"]');
    const seccionProtecciones = document.querySelector('.acordeon-item[data-seccion="protecciones"]');
    const seccionCliente = document.querySelector('.acordeon-item[data-seccion="cliente"]');

    if (flujoCompletado) {
        actualizarEstadoSeccion(seccionCategoria, true);
        actualizarEstadoSeccion(seccionAdicionales, true);
        actualizarEstadoSeccion(seccionProtecciones, true);
        actualizarEstadoSeccion(seccionCliente, true);
    } else {
        actualizarEstadoSeccion(seccionCategoria, categoriaDesbloqueada);
        actualizarEstadoSeccion(seccionAdicionales, adicionalesDesbloqueada);
        actualizarEstadoSeccion(seccionProtecciones, proteccionesDesbloqueada);
        actualizarEstadoSeccion(seccionCliente, clienteDesbloqueada);
    }
}

function abrirSeccion(seccion) {
    if (!seccion) return;
    const body = seccion.querySelector('.stack-body');
    const indicator = seccion.querySelector('.stack-indicator');
    if (body && !body.classList.contains('expanded')) {
        body.classList.add('expanded');
    }
    if (indicator && !indicator.classList.contains('expanded')) {
        indicator.classList.add('expanded');
    }
    setTimeout(() => {
        seccion.scrollIntoView({ behavior: 'smooth', block: 'start' });
    }, 100);
}

function desbloquearCategoria() {
    if (categoriaDesbloqueada) return;
    categoriaDesbloqueada = true;
    actualizarTodasSecciones();
    const seccionCategoria = document.querySelector('.acordeon-item[data-seccion="categoria"]');
    abrirSeccion(seccionCategoria);
}

function desbloquearAdicionales() {
    if (adicionalesDesbloqueada) return;
    adicionalesDesbloqueada = true;
    actualizarTodasSecciones();
    const seccionAdicionales = document.querySelector('.acordeon-item[data-seccion="adicionales"]');
    abrirSeccion(seccionAdicionales);
}

function desbloquearProtecciones() {
    if (proteccionesDesbloqueada) return;
    proteccionesDesbloqueada = true;
    actualizarTodasSecciones();
    const seccionProtecciones = document.querySelector('.acordeon-item[data-seccion="protecciones"]');
    abrirSeccion(seccionProtecciones);
}

function desbloquearCliente() {
    if (clienteDesbloqueada) return;
    clienteDesbloqueada = true;
    actualizarTodasSecciones();
    const seccionCliente = document.querySelector('.acordeon-item[data-seccion="cliente"]');
    abrirSeccion(seccionCliente);
}

function completarFlujo() {
    if (flujoCompletado) return;
    flujoCompletado = true;
    actualizarTodasSecciones();
    if (typeof mostrarToast === 'function') {
        mostrarToast('✅ ¡Formulario completo! Puedes editar cualquier sección', 'success');
    }
}

/* =========================================
   04 EVENTO PRINCIPAL DEL BOTÓN (UNIFICADO)
========================================= */
function configurarBotonPrincipal() {
    const btn = document.getElementById('btnBuscarReservacion');
    if (!btn) {
        console.error('❌ Botón btnBuscarReservacion no encontrado');
        return;
    }

    const newBtn = btn.cloneNode(true);
    btn.parentNode.replaceChild(newBtn, btn);

    newBtn.addEventListener('click', function(e) {
        e.preventDefault();
        e.stopPropagation();

        console.log('🔍 Validando campos de ubicación...');

        const esValido = validarCamposUbicacion();

        if (esValido) {
            console.log('✅ Validación exitosa - Desbloqueando categoría');

            desbloquearCategoria();

            setTimeout(() => {
                const btnCategorias = document.getElementById('btnCategorias');
                if (btnCategorias) {
                    btnCategorias.click();
                }
            }, 300);
        } else {
            console.log('❌ Validación fallida - Corrige los campos en rojo');
        }
    });
}

/* =========================================
   05 OBSERVADORES Y CONFIGURACIÓN ADICIONAL
========================================= */
function configurarBotonesSiguiente() {
    const btnSiguienteAdicionales = document.querySelector('.acordeon-item[data-seccion="adicionales"] .btn-siguiente');
    if (btnSiguienteAdicionales) {
        const newBtn = btnSiguienteAdicionales.cloneNode(true);
        btnSiguienteAdicionales.parentNode.replaceChild(newBtn, btnSiguienteAdicionales);
        newBtn.addEventListener('click', (e) => {
            e.preventDefault();
            e.stopPropagation();
            desbloquearProtecciones();
        });
    }

    const btnSiguienteProtecciones = document.querySelector('.acordeon-item[data-seccion="protecciones"] .btn-siguiente');
    if (btnSiguienteProtecciones) {
        const newBtn = btnSiguienteProtecciones.cloneNode(true);
        btnSiguienteProtecciones.parentNode.replaceChild(newBtn, btnSiguienteProtecciones);
        newBtn.addEventListener('click', (e) => {
            e.preventDefault();
            e.stopPropagation();
            desbloquearCliente();
        });
    }
}

function configurarClicEncabezados() {
    const secciones = ['categoria', 'adicionales', 'protecciones', 'cliente'];

    secciones.forEach(seccionKey => {
        const seccion = document.querySelector(`.acordeon-item[data-seccion="${seccionKey}"]`);
        if (!seccion) return;

        const head = seccion.querySelector('.stack-head');
        if (!head) return;

        const newHead = head.cloneNode(true);
        head.parentNode.replaceChild(newHead, head);

        newHead.addEventListener('click', (e) => {
            if (e.target.classList.contains('btn-siguiente')) return;

            let desbloqueada = false;
            if (flujoCompletado) {
                desbloqueada = true;
            } else {
                if (seccionKey === 'categoria') desbloqueada = categoriaDesbloqueada;
                else if (seccionKey === 'adicionales') desbloqueada = adicionalesDesbloqueada;
                else if (seccionKey === 'protecciones') desbloqueada = proteccionesDesbloqueada;
                else if (seccionKey === 'cliente') desbloqueada = clienteDesbloqueada;
            }

            if (!desbloqueada) {
                e.preventDefault();
                e.stopPropagation();
                let mensaje = '';
                if (seccionKey === 'categoria') mensaje = '⚠️ Primero completa la ubicación y haz clic en SIGUIENTE';
                else if (seccionKey === 'adicionales') mensaje = '⚠️ Primero selecciona una categoría de vehículo';
                else if (seccionKey === 'protecciones') mensaje = '⚠️ Primero revisa los adicionales (puedes saltarlos)';
                else if (seccionKey === 'cliente') mensaje = '⚠️ Primero revisa las protecciones (puedes saltarlas)';

                if (typeof mostrarToast === 'function') {
                    mostrarToast(mensaje, 'warning');
                }
                return;
            }

            const body = seccion.querySelector('.stack-body');
            const indicator = seccion.querySelector('.stack-indicator');
            if (body) body.classList.toggle('expanded');
            if (indicator) indicator.classList.toggle('expanded');
        });
    });
}

function observarCategoria() {
    let categoriaSeleccionada = false;
    setInterval(() => {
        const tieneCategoria = window.state && window.state.categoria !== null;
        if (tieneCategoria && !categoriaSeleccionada) {
            categoriaSeleccionada = true;
            desbloquearAdicionales();
        }
    }, 500);
}

function observarClienteCompleto() {
    setInterval(() => {
        const nombre = document.getElementById('nombre_cliente')?.value?.trim();
        const apellidos = document.getElementById('apellidos_cliente')?.value?.trim();
        const email = document.getElementById('email_cliente')?.value?.trim();
        const telefono = document.getElementById('telefono_ui')?.value?.trim();
        const clienteCompleto = nombre && apellidos && email && telefono;

        if (clienteCompleto && !flujoCompletado && clienteDesbloqueada) {
            completarFlujo();
        }
    }, 1000);
}

function initValidacionHorasTiempoReal() {
    const fechaInicio = document.getElementById("fecha_inicio_ui");
    const fechaFin = document.getElementById("fecha_fin_ui");

    if (!fechaInicio || !fechaFin) return;

    const validar = () => {
        const warningExistente = document.querySelector('.hora-warning');
        if (warningExistente) warningExistente.remove();

        const horaEntregaUI = document.getElementById("hora_entrega_ui");
        const horaEntregaSelect = document.querySelector('#hora_entrega_ui')?.closest('.dt-field-admin')?.querySelector('.tp-hour');

        if (!fechaInicio.value || !fechaFin.value) {
            if (horaEntregaUI) limpiarError(horaEntregaUI);
            if (horaEntregaSelect) limpiarError(horaEntregaSelect);
            return;
        }

        const normalizar = (f) => f.includes('/') ? f.split('/').reverse().join('-') : f;
        const mismaFecha = normalizar(fechaInicio.value) === normalizar(fechaFin.value);

        if (!mismaFecha) {
            if (horaEntregaUI && horaEntregaSelect && horaEntregaSelect.value) {
                limpiarError(horaEntregaUI);
                mostrarExito(horaEntregaUI);
                limpiarError(horaEntregaSelect);
                horaEntregaSelect.classList.add('field-success');
            }
            return;
        }

        const horaRetiro = document.querySelector('#hora_retiro_ui')?.closest('.dt-field-admin')?.querySelector('.tp-hour')?.value;
        const horaEntrega = horaEntregaSelect?.value;

        if (horaRetiro && horaEntrega) {
            if (parseInt(horaEntrega) <= parseInt(horaRetiro)) {
                if (horaEntregaUI) {
                    mostrarError(horaEntregaUI, 'La hora de devolución debe ser mayor');
                }
                if (horaEntregaSelect) {
                    horaEntregaSelect.classList.add('field-error');
                }

                const warning = document.createElement('small');
                warning.className = 'hora-warning';
                warning.style.cssText = 'display: block; color: #f59e0b; font-size: 11px; margin-top: 4px;';
                warning.textContent = 'La hora de devolución debe ser mayor';
                horaEntregaUI?.parentNode?.appendChild(warning);
            } else {
                if (horaEntregaUI) {
                    limpiarError(horaEntregaUI);
                    mostrarExito(horaEntregaUI);
                }
                if (horaEntregaSelect) {
                    limpiarError(horaEntregaSelect);
                    horaEntregaSelect.classList.add('field-success');
                }
            }
        } else if (horaEntrega && !horaRetiro) {
            if (horaEntregaUI) {
                limpiarError(horaEntregaUI);
                mostrarExito(horaEntregaUI);
            }
        }
    };

    fechaInicio.addEventListener('change', validar);
    fechaFin.addEventListener('change', validar);

    const horaRetiroSelect = document.querySelector('#hora_retiro_ui')?.closest('.dt-field-admin')?.querySelector('.tp-hour');
    const horaEntregaSelect = document.querySelector('#hora_entrega_ui')?.closest('.dt-field-admin')?.querySelector('.tp-hour');

    if (horaRetiroSelect) {
        horaRetiroSelect.addEventListener('change', validar);
    }
    if (horaEntregaSelect) {
        horaEntregaSelect.addEventListener('change', validar);
    }

    setTimeout(validar, 100);
}

/* =========================================
   06 INICIALIZACIÓN DEL SISTEMA UNIFICADO
========================================= */
function init() {
    console.log('🚀 Sistema unificado iniciado...');
    configurarBotonPrincipal();
    configurarBotonesSiguiente();
    configurarClicEncabezados();
    observarCategoria();
    observarClienteCompleto();

    setTimeout(() => {
        if (typeof initTimeValidation === 'function') {
            initTimeValidation();
            console.log('✅ Validación de horas inicializada');
        }

        if (typeof initDateValidation === 'function') {
            initDateValidation();
            console.log('✅ Validación de fechas inicializada');
        }

        if (typeof initValidacionHorasTiempoReal === 'function') {
            initValidacionHorasTiempoReal();
            console.log('✅ Validación de horas en misma fecha inicializada');
        }


        document.querySelectorAll('.tp-hour').forEach(select => {
            if (select.value && select.value !== "") {
                const inputHoraUI = select.closest('.dt-field-admin, .time-field-admin')?.querySelector('.input-buscador-admin');
                if (inputHoraUI) {
                    mostrarExito(inputHoraUI);
                }
                mostrarExito(select);
            }
        });

        ['#fecha_inicio_ui', '#fecha_fin_ui'].forEach(selector => {
            const input = document.querySelector(selector);
            if (input && input.value && input.value.trim() !== "") {
                mostrarExito(input);
            }
        });
    }, 100);

    if (window.state && window.state.categoria) {
        adicionalesDesbloqueada = true;
        categoriaDesbloqueada = true;
        actualizarTodasSecciones();
    }

    console.log('✅ Sistema listo');
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', () => setTimeout(init, 500));
} else {
    setTimeout(init, 500);
}

function validarSucursalesLocal() {
    const sucRetiro = document.getElementById("sucursal_retiro")?.value;

    if (!sucRetiro) {
        if (typeof mostrarToast === 'function') {
            mostrarToast('⚠️ Primero debes seleccionar las sucursales de RETIRO y ENTREGA', 'warning');
        } else {
            alert('⚠️ Primero debes seleccionar las sucursales de RETIRO y ENTREGA');
        }
        return false;
    }
    return true;
}

function validarFechasHoras() {
    if (!validarSucursales()) return false;

    const fechaInicio = document.getElementById("fecha_inicio")?.value;
    const fechaFin = document.getElementById("fecha_fin")?.value;
    const horaRetiro = document.getElementById("hora_retiro")?.value;
    const horaEntrega = document.getElementById("hora_entrega")?.value;

    if (!fechaInicio || !fechaFin || !horaRetiro || !horaEntrega) {
        mostrarToast('⚠️ Completa FECHA y HORA de salida y llegada', 'warning');
        return false;
    }
    return true;
}

function validarCategoria() {
    if (!validarFechasHoras()) return false;

    if (!state.categoria) {
        mostrarToast('⚠️ Selecciona una CATEGORÍA de vehículo', 'warning');
        return false;
    }
    return true;
}

function validarCliente() {
    const nombre = document.getElementById("nombre_cliente")?.value?.trim();
    const apellidos = document.getElementById("apellidos_cliente")?.value?.trim();
    const email = document.getElementById("email_cliente")?.value?.trim();
    const telefono = document.getElementById("telefono_ui")?.value?.trim();
    const pais = document.getElementById("pais")?.value;

    if (!nombre || !apellidos) {
        mostrarToast('⚠️ Completa NOMBRE y APELLIDOS del cliente', 'warning');
        return false;
    }

    if (!email) {
        mostrarToast('⚠️ Ingresa el EMAIL del cliente', 'warning');
        return false;
    }

    if (email && !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
        mostrarToast('❌ El email no tiene un formato válido', 'error');
        return false;
    }

    if (!telefono) {
        mostrarToast('⚠️ Ingresa el TELÉFONO del cliente', 'warning');
        return false;
    }

    if (!pais) {
        mostrarToast('⚠️ Selecciona el PAÍS del cliente', 'warning');
        return false;
    }

    if (typeof isAirportSelected === 'function' && isAirportSelected()) {
        const vuelo = document.getElementById("no_vuelo")?.value?.trim();
        if (!vuelo) {
            mostrarToast('✈️ Por ser AEROPUERTO, debes ingresar el número de VUELO', 'warning');
            const vueloInput = document.getElementById("no_vuelo");
            if (vueloInput && typeof mostrarError === 'function') {
                mostrarError(vueloInput, 'Número de vuelo obligatorio para Aeropuerto');
            }
            return false;
        } else {
            const vueloInput = document.getElementById("no_vuelo");
            if (vueloInput && typeof mostrarExito === 'function') {
                mostrarExito(vueloInput);
            }
        }
    } else {
        const vueloInput = document.getElementById("no_vuelo");
        if (vueloInput && typeof limpiarError === 'function') {
            limpiarError(vueloInput);
        }
    }

    return true;
}

/* =========================================
   07 HELPERS Y UTILIDADES
========================================= */
const qs = (s) => document.querySelector(s);
const qsa = (s) => Array.from(document.querySelectorAll(s));

const money = (n) => {
    const num = Number(n || 0);
    return `$${num.toLocaleString("es-MX", {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
    })} MXN`;
};

const openPop = (el) => { if (el) el.style.display = "flex"; };
const closePop = (el) => { if (el) el.style.display = "none"; };

const escapeHtml = (str) => {
    return String(str || "")
        .replaceAll("&", "&amp;")
        .replaceAll("<", "&lt;")
        .replaceAll(">", "&gt;")
        .replaceAll('"', "&quot;")
        .replaceAll("'", "&#039;");
};

const toISODate = (d) => {
    if (!(d instanceof Date) || isNaN(d)) return "";
    const y = d.getFullYear();
    const m = String(d.getMonth() + 1).padStart(2, "0");
    const da = String(d.getDate()).padStart(2, "0");
    return `${y}-${m}-${da}`;
};

const norm = (s) => String(s || "")
    .toUpperCase()
    .normalize("NFD")
    .replace(/[\u0300-\u036f]/g, "");

const isoToFlag = (iso2) => {
    const code = String(iso2 || "").toUpperCase();
    if (!/^[A-Z]{2}$/.test(code)) return "🏳️";
    const A = 0x1F1E6;
    return String.fromCodePoint(A + (code.charCodeAt(0) - 65)) +
        String.fromCodePoint(A + (code.charCodeAt(1) - 65));
};

const getCsrf = () => {
    const meta = document.querySelector('meta[name="csrf-token"]');
    if (meta) return meta.getAttribute("content") || "";
    const tok = qs('#formReserva input[name="_token"]');
    return tok ? tok.value : "";
};

const closeAllPops = () => {
    document.querySelectorAll(".pop.modal").forEach((m) => {
        m.style.display = "none";
    });
};

/* =========================================
   08 ESTADO GLOBAL
========================================= */
const state = {
    days: 0,
    categoria: null,
    proteccion: null,
    individuales: new Map(),
    addons: new Map(),
    servicios: {
        dropoff: false,
        delivery: false,
        gasolina: false
    },
    dropoff: {
        total: 0,
        km: 0,
        ubicacion: "",
        direccion: "",
        activo: false
    },
    delivery: {
        total: 0,
        km: 0,
        ubicacion: "",
        direccion: "",
        activo: false
    },
    gasolina: {
        total: 0,
        litros: 0,
        precioLitro: 20,
        activo: false
    },
    base_editable: null,
};
window.state = state;

/* =========================================
   09 HIDDEN INPUTS (BACKEND)
========================================= */
function ensureHidden(name, id) {
    let input = qs(`#${id}`);
    if (!input) {
        input = document.createElement("input");
        input.type = "hidden";
        input.id = id;
        input.name = name;
        qs("#formReserva")?.appendChild(input);
    } else {
        input.name = name;
    }
    return input;
}

function ensureTotalsHidden() {
    ensureHidden("precio_base_dia", "precio_base_dia");
    ensureHidden("subtotal", "subtotal");
    ensureHidden("impuestos", "impuestos");
    ensureHidden("total", "total");
}

function ensureCategoriaHiddenFix() {
    const catHid = qs("#categoria_id");
    if (catHid) catHid.name = "id_categoria";
    else ensureHidden("id_categoria", "categoria_id");
}

function ensureProteccionHidden() {
    ensureHidden("seguroSeleccionado[id]", "seguroSeleccionado_id");
    ensureHidden("seguroSeleccionado[precio]", "seguroSeleccionado_precio");
    ensureHidden("seguroSeleccionado[nombre]", "seguroSeleccionado_nombre");
    ensureHidden("seguroSeleccionado[charge]", "seguroSeleccionado_charge");
}

function ensureServiciosHidden() {
    ensureHidden("svc_dropoff", "svc_dropoff");
    ensureHidden("svc_delivery", "svc_delivery");
    ensureHidden("svc_gasolina", "svc_gasolina");
}

function ensureDeliveryHidden() {
    ensureHidden("delivery_activo", "delivery_activo");
    ensureHidden("delivery_total", "delivery_total");
    ensureHidden("delivery_km", "delivery_km");
    ensureHidden("delivery_direccion", "delivery_direccion");
    ensureHidden("delivery_ubicacion", "delivery_ubicacion");
}

function ensureDropoffHidden() {
    ensureHidden("dropoff_activo", "dropoff_activo");
    ensureHidden("dropoff_total", "dropoff_total");
    ensureHidden("dropoff_km", "dropoff_km");
    ensureHidden("dropoff_direccion", "dropoff_direccion");
    ensureHidden("dropoff_ubicacion", "dropoff_ubicacion");
}

function syncDropoffHidden() {
    ensureDropoffHidden();

    const act = qs("#dropoff_activo");
    const tot = qs("#dropoff_total");
    const kms = qs("#dropoff_km");
    const dir = qs("#dropoff_direccion");
    const ubi = qs("#dropoff_ubicacion");

    if (act) act.value = state.servicios.dropoff ? "1" : "0";
    if (tot) tot.value = (state.dropoff.total || 0).toFixed(2);
    if (kms) kms.value = (state.dropoff.km || 0).toString();
    if (dir) dir.value = state.dropoff.direccion || "";
    if (ubi) ubi.value = state.dropoff.ubicacion || "";
}

function syncProteccionHidden() {
    ensureProteccionHidden();
    const p = state.proteccion;

    qs("#seguroSeleccionado_id").value = p ? String(p.id ?? "") : "";
    qs("#seguroSeleccionado_precio").value = p ? String(Number(p.precio || 0)) : "";
    qs("#seguroSeleccionado_nombre").value = p ? String(p.nombre || "") : "";
    qs("#seguroSeleccionado_charge").value = p ? String(p.charge || "por_evento") : "";
}

function syncAddonsHidden() {
    const wrap = qs("#addonsHidden");
    if (!wrap) return;

    wrap.innerHTML = "";

    let i = 0;
    state.addons.forEach((it) => {
        const qty = Number(it.qty || 0);
        if (qty <= 0) return;

        let idServicio = null;

        if (it.id === 'silla_bebe') {
            idServicio = 7;
        } else if (it.id === 'conductor_extra') {
            idServicio = 4;
        } else {
            idServicio = it.id;
        }

        const fields = [
            ["id_servicio", idServicio],
            ["cantidad", qty],
            ["precio_unitario", Number(it.precio || 0)],
            ["nombre", it.nombre || ""],
            ["tipo_cobro", it.charge || "por_evento"]
        ];

        fields.forEach(([k, v]) => {
            const input = document.createElement("input");
            input.type = "hidden";
            input.name = `adicionalesSeleccionados[${i}][${k}]`;
            input.value = String(v ?? "");
            wrap.appendChild(input);
        });

        i++;
    });

    console.log("✅ Addons sincronizados:", state.addons.size);
}

function syncIndividualesHidden() {
    const wrap = qs("#insHidden");
    if (!wrap) return;

    wrap.innerHTML = "";

    let i = 0;
    const items = Array.from(state.individuales.values());
    items.forEach((it) => {
        const fields = [
            ["id", it.id],
            ["precio", Number(it.precio || 0)],
            ["nombre", it.nombre || ""],
            ["charge", it.charge || "por_dia"],
            ["grupo", it.grupo || ""],
        ];

        fields.forEach(([k, v]) => {
            const input = document.createElement("input");
            input.type = "hidden";
            input.name = `individualesSeleccionados[${i}][${k}]`;
            input.value = String(v ?? "");
            wrap.appendChild(input);
        });

        i++;
    });
}

/* =========================================
   10 SERVICIOS (SWITCHES)
========================================= */
function syncServiciosHidden() {
    ensureServiciosHidden();

    const d = qs("#svc_dropoff");
    const l = qs("#svc_delivery");
    const g = qs("#svc_gasolina");

    if (d) d.value = state.servicios.dropoff ? "1" : "0";
    if (l) l.value = state.servicios.delivery ? "1" : "0";
    if (g) g.value = state.servicios.gasolina ? "1" : "0";
}

/* =========================================
   11 DELIVERY (Switch + Campos + Total)
========================================= */
function getDeliveryEls() {
    const wrap = qs(".delivery-wrapper");
    if (!wrap) return null;

    return {
        wrap,
        toggle: qs("#deliveryToggle"),
        fields: qs("#deliveryFields"),
        ubicacion: qs("#deliveryUbicacion"),
        groupDir: qs("#groupDireccion"),
        groupKm: qs("#groupKm"),
        dir: qs("#deliveryDireccion"),
        km: qs("#deliveryKm"),
        totalTxt: qs("#deliveryTotal"),
        totalHid: qs("#deliveryTotalHidden"),
        precioKmHid: qs("#deliveryPrecioKm"),
    };
}

function getDeliveryPrecioKm(els) {
    const wrap = els?.wrap;
    const fromData = wrap ? Number(wrap.dataset.costoKm || 0) : 0;
    const fromHid = els?.precioKmHid ? Number(els.precioKmHid.value || 0) : 0;
    return Number.isFinite(fromData) && fromData > 0 ? fromData : fromHid;
}

function syncDeliveryGroups(els) {
    if (!els) return;
    const val = String(els.ubicacion?.value || "");
    const isManual = (val === "0");
    if (els.groupDir) els.groupDir.style.display = isManual ? "block" : "none";
    if (els.groupKm) els.groupKm.style.display = isManual ? "block" : "none";
}

function computeDelivery(els) {
    if (!els) return 0;

    if (!state.categoria || !state.categoria.precio_km) {
        console.warn("⚠️ No hay categoría o precio_km para delivery");
        return 0;
    }

    const precioKm = parseFloat(state.categoria.precio_km);
    let km = 0;
    const val = String(els.ubicacion?.value || "");

    if (val === "0") {
        km = parseFloat(els.km?.value) || 0;
    } else if (val !== "") {
        const opt = els.ubicacion.options[els.ubicacion.selectedIndex];
        km = opt ? parseFloat(opt.dataset.km) || 0 : 0;
    }

    const total = km * precioKm;

    state.delivery.km = km;
    state.delivery.total = total;
    state.delivery.ubicacion = val;
    state.delivery.direccion = (val === "0") ? String(els.dir?.value || "") : "";

    if (els.totalTxt) els.totalTxt.textContent = money(total);
    if (els.totalHid) els.totalHid.value = total.toFixed(2);

    ensureDeliveryHidden();
    const act = qs("#delivery_activo");
    if (act) act.value = state.servicios.delivery ? "1" : "0";

    qs("#delivery_total").value = total.toFixed(2);
    qs("#delivery_km").value = km.toString();
    qs("#delivery_direccion").value = state.delivery.direccion;
    qs("#delivery_ubicacion").value = val;

    syncTotalsHidden();
    refreshSummary();

    return total;
}

function resetDelivery(els) {
    state.delivery.total = 0;
    state.delivery.km = 0;
    state.delivery.ubicacion = "";
    state.delivery.direccion = "";

    if (els?.totalTxt) els.totalTxt.textContent = money(0);
    if (els?.totalHid) els.totalHid.value = "0";

    if (els?.ubicacion) els.ubicacion.value = "";
    if (els?.dir) els.dir.value = "";
    if (els?.km) els.km.value = "";

    ensureDeliveryHidden();
    qs("#delivery_activo").value = "0";
    qs("#delivery_total").value = "0";
    qs("#delivery_km").value = "0";
    qs("#delivery_direccion").value = "";
    qs("#delivery_ubicacion").value = "";
}

function setDeliveryActive(on, source = "") {
    const els = getDeliveryEls();
    state.servicios.delivery = !!on;
    state.delivery.activo = !!on;

    syncServiciosHidden();
    ensureDeliveryHidden();
    qs("#delivery_activo").value = on ? "1" : "0";

    if (els?.toggle) els.toggle.checked = !!on;
    if (els?.fields) els.fields.style.display = on ? "block" : "none";

    if (!on) {
        resetDelivery(els);
    } else {
        syncDeliveryGroups(els);
        computeDelivery(els);
    }

    syncTotalsHidden();
    refreshSummary();
}

function bindDeliveryUI() {
    const els = getDeliveryEls();
    if (!els) return;

    const activoServer = String(els.wrap.dataset.deliveryActivo || "0") === "1";

    const ubServer = els.wrap.dataset.deliveryUbicacion;
    if (els.ubicacion && ubServer !== undefined && ubServer !== null && String(ubServer) !== "") {
        els.ubicacion.value = String(ubServer);
    }
    const kmServer = els.wrap.dataset.deliveryKm;
    if (els.km && kmServer) els.km.value = String(kmServer);
    const dirServer = els.wrap.dataset.deliveryDireccion;
    if (els.dir && dirServer) els.dir.value = String(dirServer);

    setDeliveryActive(activoServer, "init");

    els.toggle?.addEventListener("change", () => {
        setDeliveryActive(!!els.toggle.checked, "switch");
    });

    els.ubicacion?.addEventListener("change", () => {
        if (String(els.ubicacion.value) !== "0") {
            if (els.dir) els.dir.value = "";
            if (els.km) els.km.value = "";
        }
        syncDeliveryGroups(els);

        if (state.servicios.delivery) {
            computeDelivery(els);
            syncTotalsHidden();
            refreshSummary();
        }
    });

    els.km?.addEventListener("input", () => {
        if (state.servicios.delivery) {
            computeDelivery(els);
            syncTotalsHidden();
            refreshSummary();
        }
    });

    els.dir?.addEventListener("input", () => {
        state.delivery.direccion = String(els.dir.value || "");
        ensureDeliveryHidden();
        qs("#delivery_direccion").value = state.delivery.direccion;
    });
}

/* =========================================
   12 DROPOFF
========================================= */
function getDropoffEls() {
    const wrap = qs(".dropoff-wrapper");
    if (!wrap) return null;

    return {
        wrap,
        toggle: qs("#dropoffToggle"),
        fields: qs("#dropoffFields"),
        ubicacion: qs("#dropUbicacion"),
        groupDir: qs("#dropGroupDireccion"),
        groupKm: qs("#dropGroupKm"),
        dir: qs("#dropDireccion"),
        km: qs("#dropKm"),
        totalTxt: qs("#dropTotal"),
        costoKmHTML: qs("#dropCostoKmHTML"),
    };
}

function syncDropoffGroups(els) {
    if (!els) return;
    const val = String(els.ubicacion?.value || "");
    const isManual = (val === "0");

    if (els.groupDir) els.groupDir.style.display = isManual ? "block" : "none";
    if (els.groupKm) els.groupKm.style.display = isManual ? "block" : "none";

    const costoBox = qs("#dropCostoKm");
    if (costoBox) costoBox.style.display = val !== "" ? "block" : "none";
}

function computeDropoff(els) {
    if (!els) return 0;

    ensureDropoffHidden();

    if (!state.categoria || !state.categoria.precio_km) {
        console.warn("⚠️ No hay categoría o precio_km para dropoff");
        return 0;
    }

    const precioKm = parseFloat(state.categoria.precio_km);
    let km = 0;
    const val = String(els.ubicacion?.value || "");

    if (val === "0") {
        km = parseFloat(els.km?.value) || 0;
    } else if (val !== "") {
        const opt = els.ubicacion.options[els.ubicacion.selectedIndex];
        km = opt ? parseFloat(opt.dataset.km) || 0 : 0;
    }

    const total = km * precioKm;

    state.dropoff.km = km;
    state.dropoff.total = total;
    state.dropoff.ubicacion = val;
    state.dropoff.direccion = (val === "0") ? String(els.dir?.value || "") : "";

    if (els.totalTxt) els.totalTxt.textContent = money(total);
    if (els.costoKmHTML) els.costoKmHTML.textContent = money(precioKm).replace(" MXN", "");

    qs("#dropoff_activo").value = state.servicios.dropoff ? "1" : "0";
    qs("#dropoff_total").value = total.toFixed(2);
    qs("#dropoff_km").value = km.toString();
    qs("#dropoff_direccion").value = state.dropoff.direccion;
    qs("#dropoff_ubicacion").value = val;

    syncTotalsHidden();
    refreshSummary();

    return total;
}

function setDropoffActive(on) {
    const els = getDropoffEls();
    state.servicios.dropoff = !!on;
    state.dropoff.activo = !!on;

    if (els?.toggle) els.toggle.checked = !!on;
    if (els?.fields) els.fields.style.display = on ? "block" : "none";

    if (!on) {
        state.dropoff.total = 0;
        state.dropoff.km = 0;
        state.dropoff.ubicacion = "";
        state.dropoff.direccion = "";
        if (els?.ubicacion) els.ubicacion.value = "";
        if (els?.totalTxt) els.totalTxt.textContent = money(0);
    } else {
        syncDropoffGroups(els);
        computeDropoff(els);
    }

    syncServiciosHidden();
    syncDropoffHidden();
    syncTotalsHidden();
    refreshSummary();
}

function bindDropoffUI() {
    const els = getDropoffEls();
    if (!els) return;

    els.toggle?.addEventListener("change", () => {
        setDropoffActive(!!els.toggle.checked);
    });

    els.ubicacion?.addEventListener("change", () => {
        syncDropoffGroups(els);
        if (state.servicios.dropoff) {
            computeDropoff(els);
        }
    });

    els.km?.addEventListener("input", () => {
        if (state.servicios.dropoff) {
            computeDropoff(els);
            syncTotalsHidden();
            refreshSummary();
        }
    });

    els.dir?.addEventListener("input", () => {
        state.dropoff.direccion = String(els.dir.value || "");
        const hid = qs("#dropoff_direccion");
        if (hid) hid.value = state.dropoff.direccion;
    });
}

/* =========================================
   13 GASOLINA
========================================= */
function getGasolinaEls() {
    return {
        toggle: qs("#gasolinaToggle"),
        fields: qs("#gasolinaFields"),
        totalTxt: qs("#gasolinaTotal"),
        totalHid: qs("#gasolinaTotalHidden"),
    };
}

function computeGasolina() {
    const els = getGasolinaEls();
    if (!els) return 0;

    if (!state.categoria || !state.categoria.capacidad_tanque) {
        console.warn("⚠️ No hay categoría o capacidad_tanque");
        return 0;
    }

    const litros = parseFloat(state.categoria.capacidad_tanque);
    const precio = state.gasolina.precioLitro;
    const total = litros * precio;

    const label = document.getElementById("litrosLabel");
    if (label) {
        label.textContent = litros;
    }

    state.gasolina.litros = litros;
    state.gasolina.total = total;

    if (els.totalTxt) els.totalTxt.textContent = money(total);
    if (els.totalHid) els.totalHid.value = total.toFixed(2);

    syncTotalsHidden();
    refreshSummary();

    return total;
}

function setGasolinaActive(on) {
    const els = getGasolinaEls();
    state.servicios.gasolina = !!on;
    state.gasolina.activo = !!on;

    syncServiciosHidden();

    if (els?.toggle) els.toggle.checked = !!on;
    if (els?.fields) els.fields.style.display = on ? "block" : "none";

    if (!on) {
        state.gasolina.total = 0;
        if (els?.totalHid) els.totalHid.value = "0";
    } else {
        computeGasolina();
    }

    syncTotalsHidden();
    refreshSummary();
    console.log("⛽ GASOLINA STATE:", state.servicios.gasolina);
    console.log("⛽ LITROS:", state.categoria?.capacidad_tanque);
}

function bindGasolinaUI() {
    const toggle = qs("#gasolinaToggle");
    const inputLitros = qs("#gasolinaLitros");

    if (!toggle) return;

    toggle.addEventListener("change", () => {
        const active = !!toggle.checked;
        state.servicios.gasolina = active;

        const fields = qs("#gasolinaFields");
        if (fields) fields.style.display = active ? "block" : "none";

        if (active) {
            computeGasolina();
        } else {
            state.gasolina.total = 0;
            if (qs("#gasolinaTotalTxt")) qs("#gasolinaTotalTxt").textContent = money(0);
        }

        syncTotalsHidden();
        refreshSummary();
    });

    inputLitros?.addEventListener("input", () => {
        if (state.servicios.gasolina) {
            computeGasolina();
            syncTotalsHidden();
            refreshSummary();
        }
    });
}

function getServiciosLabelList() {
    const labels = [];
    if (state.servicios.dropoff) labels.push("🚩 Drop Off");
    if (state.servicios.delivery) labels.push("🚚 Delivery");
    if (state.servicios.gasolina) labels.push("⛽ Gasolina prepago");
    const addons = Array.from(state.addons.values())
        .filter(x => Number(x.qty || 0) > 0);

    addons.forEach(a => {
        let icon = "➕";

        if (a.id === "silla_bebe") icon = "👶";
        if (a.id === "conductor_extra") icon = "👤";

        labels.push(`${icon} ${a.nombre} ×${a.qty}`);
    });

    return labels;
}

/* =========================================
   14 FECHAS/HORAS: UI + HIDDEN
========================================= */
function syncDateHiddenFromUI(uiId, hiddenId) {
    const ui = qs(uiId);
    const hid = qs(hiddenId);
    if (!ui || !hid) return;

    const val = String(ui.value || "").trim();
    if (!val) { hid.value = ""; return; }

    if (/^\d{2}\/\d{2}\/\d{4}$/.test(val)) {
        const [d, m, y] = val.split("/").map(Number);
        const date = new Date(y, m - 1, d, 0, 0, 0);
        hid.value = toISODate(date);
        return;
    }

    if (/^\d{2}-\d{2}-\d{4}$/.test(val)) {
        const [d, m, y] = val.split("-").map(Number);
        const date = new Date(y, m - 1, d, 0, 0, 0);
        hid.value = toISODate(date);
        return;
    }

    if (/^\d{4}-\d{2}-\d{2}$/.test(val)) {
        hid.value = val;
        return;
    }

    hid.value = "";
}

function syncTimeHiddenFromUI(uiId, hiddenId) {
    const ui = qs(uiId);
    const hid = qs(hiddenId);
    if (!ui || !hid) return;
    const val = String(ui.value || "").trim();
    hid.value = val || "";
}

/* =========================================
   15 DÍAS
========================================= */
function computeDays() {
    const fi = qs("#fecha_inicio")?.value || "";
    const ff = qs("#fecha_fin")?.value || "";
    if (!fi || !ff) return 0;

    const parseDate = (val) => {
        if (/^\d{2}\/\d{2}\/\d{4}$/.test(val)) {
            const [d, m, y] = val.split("/").map(Number);
            return new Date(y, m - 1, d, 0, 0, 0);
        }
        if (/^\d{2}-\d{2}-\d{4}$/.test(val)) {
            const [d, m, y] = val.split("-").map(Number);
            return new Date(y, m - 1, d, 0, 0, 0);
        }
        if (/^\d{4}-\d{2}-\d{2}$/.test(val)) return new Date(val + "T00:00:00");
        return new Date(val);
    };

    const d1 = parseDate(fi);
    const d2 = parseDate(ff);
    const diff = Math.ceil((d2 - d1) / (1000 * 60 * 60 * 24));
    return Math.max(1, Number.isFinite(diff) ? diff : 0);
}

function repaintCategoriaModalEstimados() {
    const dias = Number(state.days || 0);
    const cards = Array.from(document.querySelectorAll("#catPop .card-pick[data-precio]"));
    if (!cards.length) return;

    cards.forEach((card) => {
        const precio = Number(card.dataset.precio || 0);
        const est = precio * Math.max(dias, 0);
        const el = card.querySelector(".cat-estimado");
        if (el) el.textContent = money(est).replace(" MXN", "");
    });
}

function syncDays() {
    state.days = computeDays();
    const diasTxt = qs("#diasTxt");
    if (diasTxt) diasTxt.textContent = String(state.days || 0);

    refreshCategoriaPreview();
    repaintCategoriaModalEstimados();
    refreshAddonsBadge();

    if (state.servicios.delivery) {
        const els = getDeliveryEls();
        if (els) computeDelivery(els);
    }

    refreshSummary();
    syncTotalsHidden();
    state.base_editable = null;
}

/* =========================================
   16 AEROPUERTO (No. vuelo)
========================================= */
function isAirportSelected() {
    const selR = document.getElementById("sucursal_retiro");
    const selE = document.getElementById("sucursal_entrega");

    const check = (sel) => {
        if (!sel || sel.selectedIndex < 0) return false;
        const opt = sel.options[sel.selectedIndex];
        if (!opt) return false;

        const nombre = (opt.textContent || "").toLowerCase();
        return nombre.includes("aeropuerto");
    };

    return check(selR) || check(selE);
}

function syncVueloField() {
    const wrap = qs("#vueloWrap");
    const vuelo = qs("#no_vuelo");
    const show = isAirportSelected();

    if (wrap) wrap.style.display = show ? "block" : "none";

    if (vuelo) {
        if (show) {
            vuelo.setAttribute("required", "required");
        } else {
            vuelo.removeAttribute("required");
            vuelo.value = "";
        }
    }
}

/* =========================================
   17 CATEGORÍA
========================================= */
function setCategoria(cat) {
    state.categoria = cat;

    const hid = qs("#categoria_id");
    if (hid) hid.value = cat ? String(cat.id) : "";

    const txt = qs("#catSelTxt");
    const sub = qs("#catSelSub");
    const rem = qs("#catRemove");
    const mini = qs("#catMiniPreview");

    if (!cat) {
        if (txt) txt.textContent = "— Ninguna categoría —";
        if (sub) sub.textContent = "Tarifa base por día y cálculo previo aparecerán aquí.";
        if (rem) rem.style.display = "none";
        if (mini) mini.style.display = "none";

        const inputPrecioKm = qs("#deliveryPrecioKm");
        if (inputPrecioKm) inputPrecioKm.value = "0";

        syncTotalsHidden();
        refreshSummary();
        return;
    }

    if (txt) txt.textContent = cat.nombre;
    if (sub) sub.textContent = `${money(cat.precio_dia)} / día · ${state.days || 0} día(s)`;
    if (rem) rem.style.display = "";

    refreshCategoriaPreview();

    const inputPrecioKm = qs("#deliveryPrecioKm");
    if (inputPrecioKm) {
        const precioCoche = cat.precio_km || 0;
        inputPrecioKm.value = precioCoche;
    }

    if (state.servicios.delivery) {
        const els = getDeliveryEls();
        if (els) computeDelivery(els);
    }

    if (state.servicios.dropoff) {
        const els = getDropoffEls();
        if (els) computeDropoff(els);
    }

    if (state.servicios.gasolina) {
        computeGasolina();
    }

    syncTotalsHidden();
    refreshSummary();
    state.base_editable = null;
}

function refreshCategoriaPreview() {
    const cat = state.categoria;
    const mini = qs("#catMiniPreview");
    if (!mini) return;

    if (!cat) {
        mini.style.display = "none";
        return;
    }

    mini.style.display = "block";

    const imgEl = document.getElementById("catMiniImg");
    if (imgEl && cat.img) {
        imgEl.src = cat.img;
        imgEl.alt = cat.nombre || "Auto";
    } else if (imgEl && !cat.img) {
        imgEl.src = "{{ asset('img/Logotipo.png') }}";
    }

    const n = document.getElementById("catMiniName");
    const d = document.getElementById("catMiniDesc");
    const rate = document.getElementById("catMiniRate");
    const calc = document.getElementById("catMiniCalc");

    if (n) n.textContent = cat.nombre || "—";

    let descripcion = cat.desc || "";
    if (!descripcion && cat.id) {
        const descripciones = {
            1: "Chevrolet Aveo o similar",
            2: "Volkswagen Virtus o similar",
            3: "Volkswagen Jetta o similar",
            4: "Toyota Camry o similar",
            5: "Jeep Renegade o similar",
            6: "Volkswagen Taos o similar",
            7: "Toyota Avanza o similar",
            8: "Honda Odyssey o similar",
            9: "Toyota Hiace o similar",
            10: "Nissan Frontier o similar",
            11: "Toyota Tacoma o similar"
        };
        descripcion = descripciones[cat.id] || "";
    }
    if (d) d.textContent = descripcion || "—";

    if (rate) rate.textContent = `${money(cat.precio_dia).replace(" MXN", "")} MXN / día`;

    const pre = Number(cat.precio_dia || 0) * Number(state.days || 0);
    if (calc) calc.textContent = money(pre);

    setTimeout(() => {
        if (typeof initEditarCategoriaPreview === 'function') {
            initEditarCategoriaPreview();
        }
    }, 100);
}

/* =========================================
   18 PROTECCIONES (PAQUETE)
========================================= */
function clearIndividuales() {
    state.individuales.clear();
    syncIndividualesHidden();
    repaintIndividualesUI();
}

function setProteccion(p) {
    if (p) clearIndividuales();

    state.proteccion = p;

    const hid = qs("#proteccion_id");
    if (hid) hid.value = p ? String(p.id) : "";

    const txt = qs("#proteSelTxt");
    const sub = qs("#proteSelSub");
    const rem = qs("#proteRemove");

    if (!p) {
        if (txt) txt.textContent = "— Ninguna protección —";
        if (sub) sub.textContent = "Costo se refleja en el resumen.";
        if (rem) rem.style.display = "none";
        syncProteccionHidden();
        syncTotalsHidden();
        refreshSummary();
        return;
    }

    if (txt) txt.textContent = p.nombre || "Protección";
    const pPrice = Number(p.precio || 0);
    if (sub) sub.textContent = `${money(pPrice)} ${p.charge === "por_dia" ? "/ día" : ""}`;
    if (rem) rem.style.display = "";

    syncProteccionHidden();
    syncTotalsHidden();
    refreshSummary();
}

/* =========================================
   19 INDIVIDUALES
========================================= */
function getGrupoLabelFromTrack(trackId) {
    const map = {
        insColisionTrack: "Colisión y robo",
        insMedicosTrack: "Gastos médicos",
        insCaminoTrack: "Asistencia para el camino",
        insTercerosTrack: "Daños a terceros",
        insAutoTrack: "Protecciones automáticas",
    };
    return map[trackId] || "";
}

function toggleIndividualFromCard(card) {
    if (!card) return;

    if (state.proteccion) setProteccion(null);

    const id = String(card.dataset.id || "");
    const precio = Number(card.dataset.precio || 0);
    const nombre = card.querySelector("h4")?.textContent?.trim() || "Seguro individual";
    const desc = card.querySelector("p")?.textContent?.trim() || "";

    const parentTrack = card.closest(".scroll-h")?.id || "";
    const grupo = getGrupoLabelFromTrack(parentTrack);

    if (!grupo) {
        const exists = state.individuales.has(id);
        if (exists) state.individuales.delete(id);
        else state.individuales.set(id, { id, nombre, desc, precio, charge: "por_dia", grupo });

        syncIndividualesHidden();
        repaintIndividualesUI();
        syncTotalsHidden();
        refreshSummary();
        refreshProteccionUIHeader();
        return;
    }

    let existingIdInGroup = null;
    for (const [existingId, item] of state.individuales.entries()) {
        if (item.grupo === grupo) {
            existingIdInGroup = existingId;
            break;
        }
    }

    if (existingIdInGroup) {
        if (existingIdInGroup === id) {
            state.individuales.delete(id);
        } else {
            state.individuales.delete(existingIdInGroup);
            state.individuales.set(id, { id, nombre, desc, precio, charge: "por_dia", grupo });
        }
    } else {
        state.individuales.set(id, { id, nombre, desc, precio, charge: "por_dia", grupo });
    }

    syncIndividualesHidden();
    repaintIndividualesUI();
    syncTotalsHidden();
    refreshSummary();
    refreshProteccionUIHeader();
}

function repaintIndividualesUI() {
    qsa(".individual-item").forEach((card) => {
        const id = String(card.dataset.id || "");
        const on = state.individuales.has(id);
        card.classList.toggle("is-selected", on);
        const sw = card.querySelector(".switch-individual");
        if (sw) sw.classList.toggle("is-on", on);
    });
}

function refreshProteccionUIHeader() {
    const txt = qs("#proteSelTxt");
    const sub = qs("#proteSelSub");
    const rem = qs("#proteRemove");

    const inds = Array.from(state.individuales.values());

    if (state.proteccion) {
        if (txt) txt.textContent = state.proteccion.nombre || "Protección";
        const pPrice = Number(state.proteccion.precio || 0);
        if (sub) sub.textContent = `${money(pPrice)} ${state.proteccion.charge === "por_dia" ? "/ día" : ""}`;
        if (rem) rem.style.display = "";
        return;
    }

    if (!inds.length) {
        if (txt) txt.textContent = "— Ninguna protección —";
        if (sub) sub.textContent = "Costo se refleja en el resumen.";
        if (rem) rem.style.display = "none";
        return;
    }

    if (rem) rem.style.display = "";

    const listaContainer = document.createElement("div");
    listaContainer.className = "protecciones-lista-individuales";
    listaContainer.style.cssText = "display: flex; flex-direction: column; gap: 8px; margin-top: 4px;";

    const getIconoByGrupo = (grupo) => {
        const iconos = {
            'Colisión y robo': 'fa-car-crash',
            'Gastos médicos': 'fa-ambulance',
            'Asistencia para el camino': 'fa-road',
            'Daños a terceros': 'fa-handshake',
            'Protecciones automáticas': 'fa-microchip'
        };
        return iconos[grupo] || 'fa-shield-alt';
    };

    inds.forEach(ind => {
        const item = document.createElement("div");
        item.className = "proteccion-item-individual";

        const icono = getIconoByGrupo(ind.grupo);
        const precioTotal = (ind.precio || 0) * (state.days || 1);

        item.innerHTML = `
            <div style="display: flex; align-items: center; gap: 10px;">
                <i class="fas ${icono}" style="width: 20px; font-size: 14px;"></i>
                <span style="font-weight: 600; font-size: 13px; color: #1e293b;">${escapeHtml(ind.nombre)}</span>
            </div>
            <div style="display: flex; align-items: center; gap: 8px;">
                <span style="font-weight: 700; font-size: 13px; color: #0f172a;">${money(precioTotal)}</span>
                <span style="font-size: 10px; color: #64748b;">/día</span>
            </div>
        `;
        listaContainer.appendChild(item);
    });

    const subTot = calcIndividualesSubtotal();
    const totalItem = document.createElement("div");
    totalItem.className = "proteccion-total-individual";
    totalItem.innerHTML = `
        <span style="font-weight: 800; font-size: 14px; color: #b22222;">TOTAL</span>
        <span style="font-weight: 900; font-size: 18px; color: #b22222;">${money(subTot)}</span>
    `;
    listaContainer.appendChild(totalItem);

    if (txt) {
        txt.innerHTML = "";
        txt.appendChild(listaContainer);
    }

    if (sub) sub.innerHTML = `${inds.length} individual(es) seleccionados · ${state.days || 0} día(s)`;
}

function calcIndividualesSubtotal() {
    const days = Number(state.days || 0);
    let sum = 0;
    state.individuales.forEach((it) => {
        sum += Number(it.precio || 0) * days;
    });
    return sum;
}

/* =========================================
   20 ADDONS
========================================= */
function setAddonQty(item, qty) {
    const q = Math.max(0, Number(qty || 0));
    if (q <= 0) state.addons.delete(String(item.id));
    else state.addons.set(String(item.id), { ...item, qty: q });

    syncAddonsHidden();
    refreshAddonsBadge();
    syncTotalsHidden();
    refreshSummary();
}

function getAddonConfig(addonId) {
    const configs = {
        'silla_bebe': {
            id: 7,
            name: 'Silla de bebé',
            price: 150,
            charge: 'por_dia',
            maxQty: 3,
            defaultQty: 1,
            cardSelector: '.svc-card[data-id="silla_bebe"]',
            toggleSelector: '.addon-toggle[data-addon="silla_bebe"]',
            expandedSelector: '#sillaBebeExpanded',
            qtySelector: '#sillaBebeExpanded .qty-value',
            totalSelector: '#sillaBebeExpanded .addon-total',
            hiddenSelector: 'input[name="adicionales[silla_bebe]"]'
        },
        'conductor_extra': {
            id: 4,
            name: 'Conductor adicional',
            price: 150,
            charge: 'por_dia',
            maxQty: 3,
            defaultQty: 1,
            cardSelector: '.svc-card[data-id="conductor_extra"]',
            toggleSelector: '.addon-toggle[data-addon="conductor_extra"]',
            expandedSelector: '#conductorExtraExpanded',
            qtySelector: '#conductorExtraExpanded .qty-value',
            totalSelector: '#conductorExtraExpanded .addon-total',
            hiddenSelector: 'input[name="adicionales[conductor_extra]"]'
        }
    };
    return configs[addonId];
}

function getCurrentDays() {
    const days = Number(state.days || 0);
    console.log(`📆 Días actuales para cálculo: ${days}`);
    return days > 0 ? days : 1;
}

function updateAddonTotal(addonId, qty) {
    const config = getAddonConfig(addonId);
    if (!config) return;

    const days = getCurrentDays();
    const pricePerUnit = config.price;
    let total = 0;

    if (config.charge === 'por_dia') {
        total = pricePerUnit * qty * days;
    } else {
        total = pricePerUnit * qty;
    }

    console.log(`💰 ${config.name}: ${qty} unidad(es) x ${days} día(s) x $${pricePerUnit} = $${total}`);

    const totalElement = document.querySelector(config.totalSelector);
    if (totalElement) {
        totalElement.textContent = money(total);
    }

    const hiddenInput = document.querySelector(config.hiddenSelector);
    if (hiddenInput) {
        hiddenInput.value = qty;
    }

    const addonState = {
        id: addonId,
        nombre: config.name,
        precio: pricePerUnit,
        charge: config.charge,
        qty: qty,
        total: total
    };

    if (qty > 0) {
        state.addons.set(String(addonId), addonState);
    } else {
        state.addons.delete(String(addonId));
    }

    syncAddonsHidden();
    refreshAddonsBadge();
    syncTotalsHidden();
    refreshSummary();

    if (typeof refreshSummary === 'function') {
        refreshSummary();
    }
}

function handleAddonQtyChange(addonId, change) {
    const config = getAddonConfig(addonId);
    if (!config) return;

    let currentQty = 0;

    const stateAddon = state.addons.get(String(addonId));
    if (stateAddon && stateAddon.qty) {
        currentQty = Number(stateAddon.qty);
    } else {
        const qtySpan = document.querySelector(config.qtySelector);
        if (qtySpan && qtySpan.dataset.qty) {
            currentQty = Number(qtySpan.dataset.qty);
        } else if (qtySpan) {
            currentQty = Number(qtySpan.textContent);
        }
    }

    let newQty = currentQty + change;

    if (newQty < 0) newQty = 0;
    if (newQty > config.maxQty) newQty = config.maxQty;

    console.log(`🔢 ${config.name}: cantidad de ${currentQty} → ${newQty}`);

    if (newQty === 0) {
        const toggle = document.querySelector(config.toggleSelector);
        if (toggle) {
            toggle.checked = false;
            const event = new Event('change', { bubbles: true });
            toggle.dispatchEvent(event);
        }
        const expanded = document.querySelector(config.expandedSelector);
        if (expanded) {
            expanded.style.display = 'none';
        }
    }

    const qtySpan = document.querySelector(config.qtySelector);
    if (qtySpan) {
        qtySpan.textContent = newQty;
        qtySpan.dataset.qty = newQty;
    }

    updateAddonTotal(addonId, newQty);
}

function setAddonActive(addonId, active) {
    const config = getAddonConfig(addonId);
    if (!config) return;

    const expanded = document.querySelector(config.expandedSelector);
    const toggle = document.querySelector(config.toggleSelector);

    console.log(`🔄 ${config.name}: activado = ${active}`);

    if (expanded) {
        expanded.style.display = active ? 'block' : 'none';
    }

    if (toggle) {
        toggle.checked = active;
    }

    if (active) {
        const qtySpan = document.querySelector(config.qtySelector);
        if (qtySpan) {
            qtySpan.textContent = config.defaultQty;
            qtySpan.dataset.qty = config.defaultQty;
        }
        updateAddonTotal(addonId, config.defaultQty);
    } else {
        updateAddonTotal(addonId, 0);
    }
}

function initAddonsWithSwitch() {
    const addonIds = ['silla_bebe', 'conductor_extra'];

    addonIds.forEach(addonId => {
        const config = getAddonConfig(addonId);
        if (!config) return;

        const toggle = document.querySelector(config.toggleSelector);
        if (toggle && !toggle.dataset.initialized) {
            toggle.dataset.initialized = 'true';

            toggle.addEventListener('change', (e) => {
                const isActive = e.target.checked;
                setAddonActive(addonId, isActive);
            });
        }

        const expanded = document.querySelector(config.expandedSelector);
        if (expanded && !expanded.dataset.quantityInitialized) {
            expanded.dataset.quantityInitialized = 'true';

            const minusBtn = expanded.querySelector('.qty-btn.minus');
            const plusBtn = expanded.querySelector('.qty-btn.plus');
            const qtySpan = expanded.querySelector('.qty-value');

            if (minusBtn) {
                const newMinusBtn = minusBtn.cloneNode(true);
                minusBtn.parentNode.replaceChild(newMinusBtn, minusBtn);

                newMinusBtn.addEventListener('click', (e) => {
                    e.stopPropagation();
                    e.preventDefault();
                    handleAddonQtyChange(addonId, -1);
                });
            }

            if (plusBtn) {
                const newPlusBtn = plusBtn.cloneNode(true);
                plusBtn.parentNode.replaceChild(newPlusBtn, plusBtn);

                newPlusBtn.addEventListener('click', (e) => {
                    e.stopPropagation();
                    e.preventDefault();
                    handleAddonQtyChange(addonId, 1);
                });
            }

            if (qtySpan && !qtySpan.dataset.qty) {
                const currentQty = Number(qtySpan.textContent) || 0;
                qtySpan.dataset.qty = currentQty;
            }
        }

        const hiddenInput = document.querySelector(config.hiddenSelector);
        if (hiddenInput && Number(hiddenInput.value) > 0) {
            const savedQty = Number(hiddenInput.value);
            console.log(`♻️ Restaurando ${config.name} con cantidad: ${savedQty}`);

            const toggle = document.querySelector(config.toggleSelector);
            if (toggle && !toggle.checked) {
                setAddonActive(addonId, true);

                const qtySpan = document.querySelector(config.qtySelector);
                if (qtySpan) {
                    qtySpan.textContent = savedQty;
                    qtySpan.dataset.qty = savedQty;
                }
                updateAddonTotal(addonId, savedQty);
            }
        }
    });
}

function refreshSummaryWithAddons() {
    if (typeof refreshSummary === 'function') {
        refreshSummary();
    }

    const items = Array.from(state.addons.values()).filter(x => Number(x.qty || 0) > 0);
    const resAdds = document.querySelector("#resAdds");
    if (resAdds) {
        if (items.length) {
            resAdds.textContent = items.map(x => `${x.nombre} ×${x.qty}`).join(", ");
        } else {
            resAdds.textContent = "—";
        }
    }
}

function refreshAddonsBadge() {
    const txt = qs("#addonsSelTxt");
    const sub = qs("#addonsSelSub");
    const clear = qs("#addonsClear");

    const items = Array.from(state.addons.values()).filter(x => Number(x.qty || 0) > 0);

    if (!items.length) {
        if (txt) txt.textContent = "— Ninguno —";
        if (sub) sub.textContent = "Subtotal estimado aparecerá aquí.";
        if (clear) clear.style.display = "none";
        return;
    }

    const names = items.slice(0, 2).map(x => `${x.nombre} ×${x.qty}`);
    const rest = items.length > 2 ? ` +${items.length - 2} más` : "";
    if (txt) txt.textContent = names.join(", ") + rest;

    const extrasSub = calcExtrasSubtotal();
    if (sub) sub.textContent = `Subtotal extras: ${money(extrasSub)}`;
    if (clear) clear.style.display = "";
}

function calcExtrasSubtotal() {
    const days = Number(state.days || 0);
    let sum = 0;
    state.addons.forEach((it) => {
        const price = Number(it.precio || 0);
        const qty = Number(it.qty || 0);
        const perDay = String(it.charge || "por_evento") === "por_dia";
        sum += price * qty * (perDay ? days : 1);
    });
    return sum;
}

/* =========================================
   21 TOTALES + HIDDEN
========================================= */
function calcTotals() {
    const days = Number(state.days || 0);

    const baseDiaOriginal = state.categoria ? Number(state.categoria.precio_dia || 0) : 0;
    const baseTotalOriginal = baseDiaOriginal * days;

    const baseTotal = state.base_editable !== null
        ? Number(state.base_editable)
        : baseTotalOriginal;

    const baseDia = days > 0 ? (baseTotal / days) : baseDiaOriginal;

    const prot = state.proteccion;
    const protPrice = prot ? Number(prot.precio || 0) : 0;
    const protTotal = prot
        ? (String(prot.charge || "por_evento") === "por_dia" ? protPrice * days : protPrice)
        : 0;

    const indTotal = (!prot) ? calcIndividualesSubtotal() : 0;
    const extrasSub = calcExtrasSubtotal();

    const deliveryTotal = state.servicios.delivery ? (state.delivery.total || 0) : 0;
    const dropoffTotal = state.servicios.dropoff ? (state.dropoff.total || 0) : 0;
    const gasolinaTotal = state.servicios.gasolina ? (state.gasolina.total || 0) : 0;

    const subtotal = baseTotal + protTotal + indTotal + extrasSub + deliveryTotal + dropoffTotal + gasolinaTotal;
    const iva = Math.round(subtotal * 0.16 * 100) / 100;
    const total = subtotal + iva;

    return { baseDia, baseTotal, protTotal, indTotal, extrasSub, deliveryTotal, gasolinaTotal, dropoffTotal, subtotal, iva, total };
}

function syncTotalsHidden() {
    ensureTotalsHidden();

    const totals = calcTotals();
    qs("#precio_base_dia").value = String(totals.baseDia || 0);
    qs("#subtotal").value = String(totals.subtotal || 0);
    qs("#impuestos").value = String(totals.iva || 0);
    qs("#total").value = String(totals.total || 0);
}

function initTarifaEdit() {
    const btn = qs("#btnEditarTarifa");
    const container = qs("#resBaseDia");

    if (!btn || !container) return;

    btn.addEventListener("click", (e) => {
        e.stopPropagation();

        if (!state.categoria) return;
        if (container.querySelector("input")) return;

        const precioActual = parseFloat(state.categoria.precio_dia || 0);

        const input = document.createElement("input");
        input.type = "number";
        input.value = precioActual.toFixed(2);
        input.min = 0;
        input.step = 0.01;

        Object.assign(input.style, {
            width: "90px",
            padding: "4px",
            border: "1px solid #2563eb",
            borderRadius: "6px",
            fontWeight: "600",
            fontSize: "14px",
            color: "#333",
            outline: "none"
        });

        container.innerHTML = "";
        container.appendChild(input);
        input.focus();
        input.select();

        const guardar = () => {
            let nuevoValor = parseFloat(input.value);

            if (isNaN(nuevoValor) || nuevoValor < 0) {
                nuevoValor = precioActual;
            }

            state.categoria.precio_dia = nuevoValor;

            container.innerHTML = "";

            syncTotalsHidden();
            refreshTotalsOnly();
            refreshSummary();

            const sub = qs("#catSelSub");
            if (sub) {
                sub.textContent = `${money(nuevoValor)} / día · ${state.days || 0} día(s)`;
            }

            refreshCategoriaPreview();
        };

        input.addEventListener("blur", guardar);
        input.addEventListener("keydown", (ev) => {
            if (ev.key === "Enter") {
                ev.preventDefault();
                input.blur();
            }
        });
    });
}

function initEditBaseTotal() {
    const btn = document.getElementById("btnEditBase");
    const container = document.getElementById("resBaseAmount");
    const noteContainer = document.getElementById("resBaseNote");

    if (!btn || !container) return;

    const newBtn = btn.cloneNode(true);
    btn.parentNode.replaceChild(newBtn, btn);

    newBtn.addEventListener("click", (e) => {
        e.stopPropagation();

        if (!state.categoria) {
            if (typeof mostrarToast === 'function') {
                mostrarToast('⚠️ Primero debes seleccionar una categoría', 'warning');
            }
            return;
        }

        if (container.querySelector("input")) return;

        const totals = calcTotals();
        const precioActual = state.base_editable !== null
            ? state.base_editable
            : totals.baseTotal;

        const input = document.createElement("input");
        input.type = "number";
        input.value = precioActual.toFixed(2);
        input.min = 0;
        input.step = 0.01;

        Object.assign(input.style, {
            width: "140px",
            padding: "6px 10px",
            border: "2px solid #2563eb",
            borderRadius: "8px",
            fontWeight: "700",
            fontSize: "20px",
            color: "#1e293b",
            outline: "none",
            textAlign: "center",
            backgroundColor: "#ffffff"
        });

        const originalAmount = container.textContent;
        const originalNote = noteContainer ? noteContainer.textContent : "";

        container.innerHTML = "";
        container.appendChild(input);
        input.focus();
        input.select();

        const guardar = () => {
            let nuevoValor = parseFloat(input.value);

            if (isNaN(nuevoValor) || nuevoValor < 0) {
                nuevoValor = precioActual;
            }

            state.base_editable = nuevoValor;

            const days = Number(state.days || 1);
            const nuevoPrecioDia = nuevoValor / days;

            if (state.categoria) {
                state.categoria.precio_dia = nuevoPrecioDia;
            }

            container.innerHTML = "";
            container.textContent = money(nuevoValor);

            if (noteContainer) {
                noteContainer.innerHTML = `${days} día(s) – precio por día ${money(nuevoPrecioDia).replace(" MXN", "")} MXN`;
            }

            const sub = document.getElementById("catSelSub");
            if (sub && state.categoria) {
                sub.textContent = `${money(nuevoPrecioDia)} / día · ${days} día(s)`;
            }

            if (typeof syncTotalsHidden === 'function') syncTotalsHidden();
            if (typeof refreshSummary === 'function') refreshSummary();

            if (typeof mostrarToast === 'function') {
                mostrarToast(`✅ Tarifa base actualizada a ${money(nuevoValor)}`, 'success');
            }
        };

        const cancelar = () => {
            container.innerHTML = "";
            container.textContent = money(precioActual);
            if (noteContainer && originalNote) {
                const days = Number(state.days || 1);
                const precioDia = state.categoria ? state.categoria.precio_dia : 0;
                noteContainer.innerHTML = `${days} día(s) – precio por día ${money(precioDia).replace(" MXN", "")} MXN`;
            }
        };

        input.addEventListener("blur", guardar);
        input.addEventListener("keydown", (ev) => {
            if (ev.key === "Enter") {
                ev.preventDefault();
                input.blur();
            }
            if (ev.key === "Escape") {
                ev.preventDefault();
                cancelar();
            }
        });
    });
}

function refreshTotalsOnly() {
    const totals = calcTotals();
    const cat = state.categoria;

    const setText = (id, val) => { const el = qs(id); if (el) el.textContent = val; };

    setText("#resBaseTotal", cat ? money(totals.baseTotal) : "—");
    setText("#resSub", money(totals.subtotal));
    setText("#resIva", money(totals.iva));
    setText("#resTotal", money(totals.total));
}

/* =========================================
   22 RESUMEN
========================================= */
function refreshSummary() {
    const days = Number(state.days || 0);

    const selR = qs("#sucursal_retiro");
    const selE = qs("#sucursal_entrega");

    const getText = (sel) =>
        sel?.options?.[sel.selectedIndex]?.textContent?.trim() || "—";

    const fi = qs("#fecha_inicio_ui")?.value || qs("#fecha_inicio")?.value || "—";
    const hi = qs("#hora_retiro_ui")?.value || qs("#hora_retiro")?.value || "—";
    const ff = qs("#fecha_fin_ui")?.value || qs("#fecha_fin")?.value || "—";
    const hf = qs("#hora_entrega_ui")?.value || qs("#hora_entrega")?.value || "—";

    const setText = (id, val) => { const el = qs(id); if (el) el.textContent = val; };

    const checkbox = document.getElementById('differentDropoffAdmin');
    let textoEntrega = getText(selE);

    if (checkbox && !checkbox.checked) {
        const textoRetiro = getText(selR);
        if (textoRetiro !== "—") {
            textoEntrega = textoRetiro;
        }
    }

    setText("#resSucursalRetiro", getText(selR));
    setText("#resSucursalEntrega", textoEntrega);
    setText("#resFechaInicio", fi);
    setText("#resHoraInicio", hi);
    setText("#resFechaFin", ff);
    setText("#resHoraFin", hf);
    setText("#resDias", days ? `${days} día(s)` : "—");

    const cat = state.categoria;
    const totals = calcTotals();

    setText("#resCat", cat ? cat.nombre : "—");

    const baseEl = qs("#resBaseDia");
    if (baseEl && !baseEl.querySelector("input")) {
        baseEl.textContent = cat ? `${money(totals.baseDia)} / día` : "—";
    }

    setText("#resBaseTotal", cat ? money(totals.baseTotal) : "—");
    setText("#resDelivery", state.servicios.delivery ? money(totals.deliveryTotal) : money(0));
    setText("#resDropoff", state.servicios.dropoff ? money(totals.dropoffTotal) : money(0));
    setText("#resGasolina", state.servicios.gasolina ? money(totals.gasolinaTotal) : money(0));

    const sillaBebe = state.addons.get('silla_bebe');
    const sillaTotal = sillaBebe ? sillaBebe.total : 0;
    setText("#resSillaBebe", sillaBebe && sillaBebe.qty > 0 ? `${money(sillaTotal)} (${sillaBebe.qty})` : money(0));

    const conductorExtra = state.addons.get('conductor_extra');
    const conductorTotal = conductorExtra ? conductorExtra.total : 0;
    setText("#resConductorExtra", conductorExtra && conductorExtra.qty > 0 ? `${money(conductorTotal)} (${conductorExtra.qty})` : money(0));

    const svcList = getServiciosLabelList();
    setText("#resServicios", svcList.length ? svcList.join(", ") : "—");

    if (state.proteccion) {
        const prot = state.proteccion;
        const protPrice = Number(prot.precio || 0);
        setText("#resProte", prot ? `${prot.nombre} (${money(protPrice)}${prot.charge === "por_dia" ? " / día" : ""})` : "—");
    } else {
        const inds = Array.from(state.individuales.values());
        if (!inds.length) {
            setText("#resProte", "—");
            const proteccionesSection = document.getElementById("proteccionesSection");
            if (proteccionesSection) proteccionesSection.style.display = "none";
        } else {
            const preview = inds.slice(0, 3).map(x => x.nombre).join(", ");
            const rest = inds.length > 3 ? ` +${inds.length - 3} más` : "";
            setText("#resProte", `🧩 Individuales: ${preview}${rest}`);
            const proteccionesSection = document.getElementById("proteccionesSection");
            if (proteccionesSection) proteccionesSection.style.display = "block";
        }
    }

    let adicionales = [];

    if (state.delivery.total > 0) adicionales.push("🚚 Delivery");
    if (state.dropoff.total > 0) adicionales.push("🚩 Drop Off");
    if (state.gasolina.total > 0) adicionales.push("⛽ Gasolina");

    const items = Array.from(state.addons.values()).filter(x => Number(x.qty || 0) > 0);
    items.forEach(x => {
        adicionales.push(`${x.nombre} ×${x.qty}`);
    });

    setText("#resAdds", adicionales.length ? adicionales.join(", ") : "—");

    setText("#resSub", money(totals.subtotal));
    setText("#resIva", money(totals.iva));
    setText("#resTotal", money(totals.total));

    const setTextV2 = (id, val) => {
        const el = document.getElementById(id);
        if (el) el.textContent = val;
    };

    function formatearFechaResumen(fechaStr) {
        if (!fechaStr || fechaStr === "—") return "—";

        let partes;
        if (fechaStr.includes('-')) {
            partes = fechaStr.split('-');
        } else if (fechaStr.includes('/')) {
            partes = fechaStr.split('/');
        } else {
            return fechaStr;
        }

        if (partes.length === 3) {
            const dia = partes[0].padStart(2, '0');
            const mesNum = parseInt(partes[1]);
            const año = partes[2];

            const meses = ['Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun',
                'Jul', 'Ago', 'Sep', 'Oct', 'Nov', 'Dic'];

            const mesLetras = meses[mesNum - 1] || '???';
            return `${dia} ${mesLetras} ${año}`;
        }

        return fechaStr;
    }

    function formatearHoraResumen(horaStr) {
        if (!horaStr || horaStr === "—") return "—";

        if (horaStr.includes(':')) {
            const partes = horaStr.split(':');
            if (partes.length >= 2) {
                const hora = partes[0].padStart(2, '0');
                const minuto = partes[1].padStart(2, '0');
                return `${hora}:${minuto} hrs`;
            }
        }

        return `${horaStr} hrs`;
    }

    setTextV2("resFechaInicioDetail", formatearFechaResumen(document.getElementById("fecha_inicio_ui")?.value || "—"));
    setTextV2("resHoraInicioDetail", formatearHoraResumen(document.getElementById("hora_retiro_ui")?.value || "—"));
    setTextV2("resFechaFinDetail", formatearFechaResumen(document.getElementById("fecha_fin_ui")?.value || "—"));
    setTextV2("resHoraFinDetail", formatearHoraResumen(document.getElementById("hora_entrega_ui")?.value || "—"));

    let tipoVehiculo = "MEDIANOS";
    let capacidadPasajeros = 5;
    let tipoTransmision = "Automático";
    let tieneCarPlay = true;
    let tieneAndroidAuto = true;
    let tieneAire = true;

    if (cat) {
        const descripcionesModelos = {
            "1": "Chevrolet Aveo o similar",
            "2": "Volkswagen Virtus o similar",
            "3": "Volkswagen Jetta o similar",
            "4": "Toyota Camry o similar",
            "5": "Jeep Renegade o similar",
            "6": "Volkswagen Taos o similar",
            "7": "Toyota Avanza o similar",
            "8": "Honda Odyssey o similar",
            "9": "Toyota Hiace o similar",
            "10": "Nissan Frontier o similar",
            "11": "Toyota Tacoma o similar"
        };

        const codigosCategoria = {
            "1": "C",
            "2": "D",
            "3": "E",
            "4": "F",
            "5": "IC",
            "6": "I",
            "7": "IB",
            "8": "M",
            "9": "L",
            "10": "H",
            "11": "HI"
        };

        let descripcionMostrar = descripcionesModelos[cat.id] || cat.desc || "";
        let codigoMostrar = codigosCategoria[cat.id] || "";

        const resCatNameEl = document.getElementById("resCatName");
        if (resCatNameEl) {
            resCatNameEl.textContent = cat.nombre;
        }

        const resCatDescEl = document.getElementById("resCatDesc");
        if (resCatDescEl) {
            resCatDescEl.textContent = descripcionMostrar;
        }

        const resCatCodigoEl = document.getElementById("resCatCodigo");
        if (resCatCodigoEl && codigoMostrar) {
            resCatCodigoEl.textContent = `Código: ${codigoMostrar}`;
        }

        switch(parseInt(cat.id)) {
            case 1:
                tipoVehiculo = "COMPACTO";
                capacidadPasajeros = 5;
                tipoTransmision = "Automático";
                break;
            case 2:
                tipoVehiculo = "MEDIANOS";
                capacidadPasajeros = 5;
                tipoTransmision = "Automático";
                break;
            case 3:
                tipoVehiculo = "GRANDES";
                capacidadPasajeros = 5;
                tipoTransmision = "Automático";
                break;
            case 4:
                tipoVehiculo = "FULL SIZE";
                capacidadPasajeros = 5;
                tipoTransmision = "Automático";
                break;
            case 5:
                tipoVehiculo = "SUV COMPACTA";
                capacidadPasajeros = 5;
                tipoTransmision = "Automático";
                break;
            case 6:
                tipoVehiculo = "SUV MEDIANA";
                capacidadPasajeros = 5;
                tipoTransmision = "Automático";
                break;
            case 7:
                tipoVehiculo = "SUV FAMILIAR COMPACTA";
                capacidadPasajeros = 7;
                tipoTransmision = "Automático";
                break;
            case 8:
                tipoVehiculo = "MINIVAN";
                capacidadPasajeros = 8;
                tipoTransmision = "Automático";
                break;
            case 9:
                tipoVehiculo = "VAN PASAJEROS 13";
                capacidadPasajeros = 13;
                tipoTransmision = "Manual";
                break;
            case 10:
                tipoVehiculo = "PICKUP DOBLE CABINA";
                capacidadPasajeros = 5;
                tipoTransmision = "Automático";
                break;
            case 11:
                tipoVehiculo = "PICKUP 4X4 DOBLE CABINA";
                capacidadPasajeros = 5;
                tipoTransmision = "Automático";
                break;
        }

        setTextV2(
            "resCatBadge",
            `${tipoVehiculo} · ${capacidadPasajeros} pasajeros · ${tipoTransmision}`
        );

        const imgEl = document.getElementById("resCatImage");
        if (imgEl) {
            if (cat.img) {
                imgEl.src = cat.img;
            } else {
                const card = document.querySelector(`.card-pick[data-id="${cat.id}"]`);
                const img = card?.querySelector(".cp-img img");
                if (img) {
                    imgEl.src = img.src;
                    cat.img = img.src;
                }
            }
            imgEl.alt = cat.nombre;
        }

    } else {
        const resCatNameEl = document.getElementById("resCatName");
        if (resCatNameEl) resCatNameEl.textContent = "—";
        const resCatDescEl = document.getElementById("resCatDesc");
        if (resCatDescEl) resCatDescEl.textContent = "—";
        const resCatCodigoEl = document.getElementById("resCatCodigo");
        if (resCatCodigoEl) resCatCodigoEl.textContent = "—";
        setTextV2("resCatBadge", "—");
        const imgEl = document.getElementById("resCatImage");
        if (imgEl) imgEl.src = "";
    }

    const featuresContainer = document.getElementById("resCatFeatures");
    if (featuresContainer && cat) {
        let tieneCarPlayLocal = true;
        let tieneAndroidAutoLocal = true;

        let featuresHTML = '';

        featuresHTML += `<span><i class="fas fa-car"></i> ${tipoTransmision}</span>`;
        featuresHTML += `<span><i class="fas fa-wind"></i> A/C</span>`;
        featuresHTML += `<span><i class="fas fa-users"></i> ${capacidadPasajeros} pasajeros</span>`;
        featuresHTML += `<span><i class="fab fa-apple"></i> CarPlay</span>`;
        featuresHTML += `<span><i class="fab fa-android"></i> Android Auto</span>`;

        featuresContainer.innerHTML = featuresHTML;
    }

    const baseDia = cat ? cat.precio_dia : 0;
    setTextV2("resBaseAmount", `$${baseDia.toLocaleString("es-MX", {minimumFractionDigits: 2})} MXN`);
    setTextV2("resBaseNote", `${days} día(s) – precio por día $${baseDia.toLocaleString("es-MX", {minimumFractionDigits: 2})} MXN`);
    setTextV2("resBaseTotalEstilo", money(totals.baseTotal));
    setTextV2("resIvaEstilo", money(totals.iva));
    setTextV2("resTotalEstilo", money(totals.total));

    const optionsContainer = document.getElementById("rv2OptionsList");
    const proteccionesContainer = document.getElementById("rv2ProteccionesList");
    const proteccionesSection = document.getElementById("proteccionesSection");
    if (optionsContainer) {
        let optionsHtml = "";

        if (state.servicios.delivery && state.delivery.total > 0) {
            optionsHtml += `
                <div class="rv2-option-item">
                    <span class="rv2-option-name"><i class="fas fa-truck"></i> Delivery</span>
                    <span class="rv2-option-price">${money(state.delivery.total)}</span>
                </div>
            `;
        }

        if (state.servicios.dropoff && state.dropoff.total > 0) {
            optionsHtml += `
                <div class="rv2-option-item">
                    <span class="rv2-option-name"><i class="fas fa-flag-checkered"></i> Drop Off</span>
                    <span class="rv2-option-price">${money(state.dropoff.total)}</span>
                </div>
            `;
        }

        if (state.servicios.gasolina && state.gasolina.total > 0) {
            optionsHtml += `
                <div class="rv2-option-item">
                    <span class="rv2-option-name"><i class="fas fa-gas-pump"></i> Gasolina Prepago</span>
                    <span class="rv2-option-price">${money(state.gasolina.total)}</span>
                </div>
            `;
        }

        const silla = state.addons.get('silla_bebe');
        if (silla && silla.qty > 0) {
            optionsHtml += `
                <div class="rv2-option-item">
                    <span class="rv2-option-name"><i class="fas fa-baby-carriage"></i> Silla de bebé ×${silla.qty}</span>
                    <span class="rv2-option-price">${money(silla.total)}</span>
                </div>
            `;
        }

        const conductor = state.addons.get('conductor_extra');
        if (conductor && conductor.qty > 0) {
            optionsHtml += `
                <div class="rv2-option-item">
                    <span class="rv2-option-name"><i class="fas fa-user-plus"></i> Conductor adicional ×${conductor.qty}</span>
                    <span class="rv2-option-price">${money(conductor.total)}</span>
                </div>
            `;
        }

        if (optionsHtml === "") {
            optionsHtml = '<div class="rv2-option-item" style="color:#94a3b8;">Ninguna opción seleccionada</div>';
        }

        optionsContainer.innerHTML = optionsHtml;
    }

    if (proteccionesContainer && proteccionesSection) {
        let proteccionesHtml = "";
        let hasProtecciones = false;

        if (state.proteccion) {
            const prot = state.proteccion;
            const protPrecio = Number(prot.precio || 0);
            const protTotal = prot.charge === "por_dia" ? protPrecio * days : protPrecio;

            if (protTotal > 0) {
                proteccionesHtml += `
                    <div class="rv2-option-item">
                        <span class="rv2-option-name"><i class="fas fa-shield-alt"></i> ${prot.nombre}</span>
                        <span class="rv2-option-price">${money(protTotal)} ${prot.charge === "por_dia" ? "/día" : ""}</span>
                    </div>
                `;
                hasProtecciones = true;
            }
        }

        const individualesList = Array.from(state.individuales.values());
        console.log("📋 Individuales encontrados:", individualesList.length);

        if (individualesList.length > 0) {
            individualesList.forEach(ind => {
                const indPrecio = Number(ind.precio || 0);
                const indTotal = indPrecio * days;

                if (indTotal >= 0) {
                    let icono = 'fa-shield-alt';
                    if (ind.grupo === 'Colisión y robo') icono = 'fa-car-crash';
                    if (ind.grupo === 'Gastos médicos') icono = 'fa-ambulance';
                    if (ind.grupo === 'Asistencia para el camino') icono = 'fa-road';
                    if (ind.grupo === 'Daños a terceros') icono = 'fa-handshake';
                    if (ind.grupo === 'Protecciones automáticas') icono = 'fa-microchip';

                    proteccionesHtml += `
                        <div class="rv2-option-item">
                            <span class="rv2-option-name"><i class="fas ${icono}"></i> ${ind.nombre}</span>
                            <span class="rv2-option-price">${money(indTotal)} <span style="font-size: 10px; color: #888;">/día</span></span>
                        </div>
                    `;
                    hasProtecciones = true;
                }
            });
        }

        if (hasProtecciones) {
            proteccionesContainer.innerHTML = proteccionesHtml;
            proteccionesSection.style.display = "block";
            console.log("✅ Sección de protecciones VISIBLE");
        } else {
            proteccionesSection.style.display = "none";
            console.log("❌ Sección de protecciones OCULTA");
        }
    }

    initEditBaseTotal();
}

/* =========================================
   23 VALIDACIÓN
========================================= */
function syncTelefonoFinal() {
    const lada = (qs("#telefono_lada")?.value || "+52").trim();
    const num = String(qs("#telefono_ui")?.value || "").trim().replace(/\s+/g, "");
    const out = qs("#telefono_cliente");

    if (out) out.value = num ? `${lada}${num}` : "";
}

function validateBeforeSubmit() {
    let allValid = true;

    const sucRetiro = document.getElementById("sucursal_retiro");
    const sucRetiroVal = sucRetiro ? (typeof $ !== 'undefined' && $(sucRetiro).data('select2') ? $(sucRetiro).val() : sucRetiro.value) : "";

    if (!sucRetiroVal || sucRetiroVal === "") {
        mostrarError(sucRetiro, 'Selecciona una sucursal de retiro');
        allValid = false;
    } else {
        mostrarExito(sucRetiro);
    }

    const checkbox = document.getElementById('differentDropoffAdmin');
    const isDifferentDropoff = checkbox && checkbox.checked;
    const sucEntrega = document.getElementById("sucursal_entrega");

    if (isDifferentDropoff) {
        const sucEntregaVal = sucEntrega ? (typeof $ !== 'undefined' && $(sucEntrega).data('select2') ? $(sucEntrega).val() : sucEntrega.value) : "";
        if (!sucEntregaVal || sucEntregaVal === "") {
            mostrarError(sucEntrega, 'Selecciona una sucursal de entrega');
            allValid = false;
        } else {
            mostrarExito(sucEntrega);
        }
    } else if (sucEntrega) {
        mostrarExito(sucEntrega);
    }

    const fechaInicioUI = document.getElementById("fecha_inicio_ui");
    const fechaInicio = document.getElementById("fecha_inicio")?.value;

    if (!fechaInicio || fechaInicio === "") {
        mostrarError(fechaInicioUI, 'Selecciona una fecha de inicio');
        allValid = false;
    } else {
        mostrarExito(fechaInicioUI);
    }

    const fechaFinUI = document.getElementById("fecha_fin_ui");
    const fechaFin = document.getElementById("fecha_fin")?.value;

    if (!fechaFin || fechaFin === "") {
        mostrarError(fechaFinUI, 'Selecciona una fecha de fin');
        allValid = false;
    } else {
        mostrarExito(fechaFinUI);
    }

    if (fechaInicio && fechaFin && fechaFin <= fechaInicio) {
        mostrarError(fechaFinUI, 'La fecha de devolución debe ser posterior a la fecha de salida');
        allValid = false;
    }

    const horaRetiroUI = document.getElementById("hora_retiro_ui");
    const horaRetiroContainer = horaRetiroUI?.closest('.dt-field-admin');
    let horaRetiroValida = false;

    if (horaRetiroContainer) {
        const selectHora = horaRetiroContainer.querySelector('.tp-hour');
        if (selectHora && selectHora.value && selectHora.value !== "") {
            horaRetiroValida = true;
            mostrarExito(selectHora);
            mostrarExito(horaRetiroUI);
        } else {
            mostrarError(horaRetiroUI, 'Selecciona una hora de retiro');
            if (selectHora) mostrarError(selectHora, 'Selecciona una hora');
            allValid = false;
        }
    }

    const horaEntregaUI = document.getElementById("hora_entrega_ui");
    const horaEntregaContainer = horaEntregaUI?.closest('.dt-field-admin');
    let horaEntregaValida = false;

    if (horaEntregaContainer) {
        const selectHora = horaEntregaContainer.querySelector('.tp-hour');
        if (selectHora && selectHora.value && selectHora.value !== "") {
            horaEntregaValida = true;
            mostrarExito(selectHora);
            mostrarExito(horaEntregaUI);
        } else {
            mostrarError(horaEntregaUI, 'Selecciona una hora de entrega');
            if (selectHora) mostrarError(selectHora, 'Selecciona una hora');
            allValid = false;
        }
    }

    const categoria = window.state?.categoria || state?.categoria;
    const catInput = document.getElementById("categoria_id");
    const btnCategorias = document.getElementById("btnCategorias");

    if (!categoria || !catInput?.value) {
        if (btnCategorias) {
            mostrarError(btnCategorias, 'Selecciona una categoría de vehículo');
        }
        allValid = false;
    } else if (btnCategorias) {
        mostrarExito(btnCategorias);
    }

    const nombre = document.getElementById("nombre_cliente");
    if (!nombre?.value?.trim()) {
        mostrarError(nombre, 'El nombre es obligatorio');
        allValid = false;
    } else {
        mostrarExito(nombre);
    }

    const apellidos = document.getElementById("apellidos_cliente");
    if (!apellidos?.value?.trim()) {
        mostrarError(apellidos, 'Los apellidos son obligatorios');
        allValid = false;
    } else {
        mostrarExito(apellidos);
    }

    const email = document.getElementById("email_cliente");
    const emailVal = email?.value?.trim() || "";
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

    if (!emailVal) {
        mostrarError(email, 'El email es obligatorio');
        allValid = false;
    } else if (!emailRegex.test(emailVal)) {
        mostrarError(email, 'Formato de email inválido');
        allValid = false;
    } else {
        mostrarExito(email);
    }

    const telefono = document.getElementById("telefono_ui");
    const telVal = telefono?.value?.trim().replace(/\s+/g, "") || "";

    if (!telVal) {
        mostrarError(telefono, 'El teléfono es obligatorio');
        allValid = false;
    } else if (telVal.length < 8) {
        mostrarError(telefono, 'Mínimo 8 dígitos');
        allValid = false;
    } else {
        mostrarExito(telefono);
    }

    const paisInput = document.getElementById("pais");
    if (paisInput && !paisInput.value) {
        mostrarError(document.querySelector(".readonly-country"), 'El país es obligatorio');
        allValid = false;
    } else if (document.querySelector(".readonly-country")) {
        mostrarExito(document.querySelector(".readonly-country"));
    }

    if (typeof isAirportSelected === 'function' && isAirportSelected()) {
        const vuelo = document.getElementById("no_vuelo");
        if (!vuelo?.value?.trim()) {
            mostrarError(vuelo, 'Número de vuelo obligatorio para Aeropuerto');
            allValid = false;
        } else if (vuelo) {
            mostrarExito(vuelo);
        }
    } else {
        const vuelo = document.getElementById("no_vuelo");
        if (vuelo && typeof limpiarError === 'function') {
            limpiarError(vuelo);
        }
    }

    const deliveryToggle = document.getElementById("deliveryToggle");
    if (deliveryToggle && deliveryToggle.checked) {
        const ubicacion = document.getElementById("deliveryUbicacion");
        const ubVal = ubicacion?.value || "";

        if (!ubVal || ubVal === "") {
            mostrarError(ubicacion, 'Selecciona una ubicación para delivery');
            allValid = false;
        } else if (ubVal === "0") {
            const km = document.getElementById("deliveryKm");
            const kmVal = parseFloat(km?.value || 0);
            if (!kmVal || kmVal <= 0) {
                mostrarError(km, 'Ingresa los kilómetros para delivery');
                allValid = false;
            } else if (km) {
                mostrarExito(km);
            }
        } else if (ubicacion) {
            mostrarExito(ubicacion);
        }
    }

    if (!allValid) {
        const totalErrores = document.querySelectorAll('.field-error').length;

        if (totalErrores === 1) {
            const primerError = document.querySelector('.error-msg');
            const mensaje = primerError?.textContent || 'Completa los campos marcados en rojo';
            mostrarToast(`⚠️ ${mensaje}`, 'warning');
        } else {
            mostrarToast(`⚠️ Faltan ${totalErrores} campos por completar correctamente`, 'warning');
        }

        const primerCampoError = document.querySelector('.field-error');
        if (primerCampoError) {
            primerCampoError.scrollIntoView({ behavior: 'smooth', block: 'center' });
            primerCampoError.style.transition = 'box-shadow 0.3s ease';
            primerCampoError.style.boxShadow = '0 0 0 3px rgba(239, 68, 68, 0.3)';
            setTimeout(() => {
                primerCampoError.style.boxShadow = '';
            }, 1000);
        }
    }

    return allValid;
}

/* =========================================
   24 FLATPICKR (CALENDARIO)
========================================= */
function initFlatpickrModalCalendar() {
    if (!window.flatpickr) return;

    let backdrop = document.querySelector(".fp-backdrop");
    if (!backdrop) {
        backdrop = document.createElement("div");
        backdrop.className = "fp-backdrop";
        document.body.appendChild(backdrop);
    }

    function makeActions(instance, labelText) {
        const actions = document.createElement("div");
        actions.className = "fp-actions";
        actions.innerHTML = `
            <button type="button" class="fp-today">Hoy</button>
            <button type="button" class="fp-clear">Limpiar</button>
            <button type="button" class="fp-label">✖ ${labelText}</button>
        `;

        actions.querySelector(".fp-today").addEventListener("click", () => instance.setDate(new Date(), true));
        actions.querySelector(".fp-clear").addEventListener("click", () => {
            instance.clear();
            if (instance.input?.id === "fecha_inicio_ui") {
                qs("#fecha_inicio").value = "";
                const finInstance = document.getElementById("fecha_fin_ui")._flatpickr;
                if (finInstance) {
                    finInstance.set("minDate", "today");
                }
            }
            if (instance.input?.id === "fecha_fin_ui") qs("#fecha_fin").value = "";
            syncDays();
        });
        return actions;
    }

    function openModal(instance) {
        backdrop.classList.add("is-open");
        document.body.classList.add("no-scroll");
        backdrop.onclick = () => instance.close();
    }

    function closeModal() {
        backdrop.classList.remove("is-open");
        document.body.classList.remove("no-scroll");
        backdrop.onclick = null;
    }

    window.flatpickr("#fecha_inicio_ui", {
        locale: "es",
        dateFormat: "d-m-Y",
        altInput: true,
        altFormat: "d-M-y",
        allowInput: false,
        clickOpens: true,
        minDate: "today",
        onOpen: (sel, str, instance) => {
            openModal(instance);
            if (!instance._actionsAdded) {
                instance.calendarContainer.appendChild(makeActions(instance, "Fecha PickUp"));
                instance._actionsAdded = true;
            }
        },
        onClose: () => closeModal(),
        onChange: (selectedDates, dateStr, instance) => {
            const d = selectedDates?.[0];
            qs("#fecha_inicio").value = d ? toISODate(d) : "";

            const finInstance = document.getElementById("fecha_fin_ui")._flatpickr;
            if (finInstance) {
                if (d) {
                    const minDateForReturn = new Date(d);
                    finInstance.set("minDate", minDateForReturn);

                    const fechaFinActual = finInstance.selectedDates[0];
                    if (fechaFinActual && fechaFinActual < d) {
                        finInstance.clear();
                        qs("#fecha_fin").value = "";
                        qs("#fecha_fin_ui").value = "";
                        if (typeof mostrarToast === 'function') {
                            mostrarToast("⚠️ La fecha de devolución no puede ser anterior a la fecha de salida", 'warning');
                        }
                    }
                } else {
                    finInstance.set("minDate", "today");
                }
            }

            syncDays();
        }
    });

    window.flatpickr("#fecha_fin_ui", {
        locale: "es",
        dateFormat: "d-m-Y",
        altInput: true,
        altFormat: "d-M-y",
        allowInput: false,
        clickOpens: true,
        minDate: "today",
        onOpen: (sel, str, instance) => {
            openModal(instance);
            if (!instance._actionsAdded) {
                instance.calendarContainer.appendChild(makeActions(instance, "Fecha Devolución"));
                instance._actionsAdded = true;
            }
        },
        onClose: () => closeModal(),
        onChange: (selectedDates) => {
            const d = selectedDates?.[0];
            qs("#fecha_fin").value = d ? toISODate(d) : "";

            const ini = qs("#fecha_inicio")?.value;
            const fin = qs("#fecha_fin")?.value;
            if (ini && fin && fin < ini) {
                qs("#fecha_fin").value = "";
                qs("#fecha_fin_ui").value = "";
                if (typeof mostrarToast === 'function') {
                    mostrarToast("⚠️ La fecha de devolución no puede ser anterior a la fecha de salida", 'warning');
                }
            }
            syncDays();
        }
    });
}

/* =========================================
   25 SELECTOR DE HORA CON <SELECT>
========================================= */
function initTimeSelectors() {
    const horaRetiroInput = document.getElementById("hora_retiro_ui");
    const horaRetiroHidden = document.getElementById("hora_retiro");

    if (horaRetiroInput && !horaRetiroInput.dataset.tpReady) {
        horaRetiroInput.dataset.tpReady = "1";
        horaRetiroInput.setAttribute("readonly", "readonly");
        horaRetiroInput.classList.add("tp-hidden-input");
        createTimeSelectsBelow(horaRetiroInput, horaRetiroHidden, "Hora ");
    }

    const horaEntregaInput = document.getElementById("hora_entrega_ui");
    const horaEntregaHidden = document.getElementById("hora_entrega");

    if (horaEntregaInput && !horaEntregaInput.dataset.tpReady) {
        horaEntregaInput.dataset.tpReady = "1";
        horaEntregaInput.setAttribute("readonly", "readonly");
        horaEntregaInput.classList.add("tp-hidden-input");
        createTimeSelectsBelow(horaEntregaInput, horaEntregaHidden, "Hora");
    }
}

function initTimeValidation() {
    const timeSelectors = document.querySelectorAll('.tp-hour');

    timeSelectors.forEach(select => {
        if (select._validationListener) {
            select.removeEventListener('change', select._validationListener);
        }

        const handler = function() {
            const inputHoraUI = this.closest('.dt-field-admin, .time-field-admin')?.querySelector('.input-buscador-admin');
            const tieneValor = this.value && this.value !== "";

            if (tieneValor) {
                if (inputHoraUI) {
                    mostrarExito(inputHoraUI);
                }
                mostrarExito(this);
            } else {
                if (inputHoraUI) {
                    limpiarError(inputHoraUI);
                }
                limpiarError(this);
            }
        };

        select._validationListener = handler;
        select.addEventListener('change', handler);
    });
}

function initDateValidation() {
    const dateInputs = ['#fecha_inicio_ui', '#fecha_fin_ui'];

    dateInputs.forEach(selector => {
        const input = document.querySelector(selector);
        if (!input) return;

        if (input._validationListener) {
            input.removeEventListener('change', input._validationListener);
        }

        const handler = function() {
            const tieneValor = this.value && this.value.trim() !== "";

            if (tieneValor) {
                mostrarExito(this);
            } else {
                limpiarError(this);
            }
        };

        input._validationListener = handler;
        input.addEventListener('change', handler);

        if (input._flatpickr) {
            input._flatpickr.config.onChange.push(() => {
                handler.call(input);
            });
        }
    });
}

function createTimeSelectsBelow(input, hiddenInput, placeholderText) {
    const wrap = input.closest(".time-field") || input.parentElement;
    if (!wrap) return;
    if (wrap.querySelector(".tp-selects")) return;

    const box = document.createElement("div");
    box.className = "tp-selects";

    const selH = document.createElement("select");
    selH.className = "tp-hour";
    selH.setAttribute("aria-label", placeholderText);

    selH.innerHTML = '<option value="" disabled selected>' + placeholderText + '</option>';
    for (let h = 0; h < 24; h++) {
        const hour = String(h).padStart(2, "0");
        const option = document.createElement("option");
        option.value = hour;
        option.textContent = `${hour}:00`;
        selH.appendChild(option);
    }

    box.appendChild(selH);
    wrap.appendChild(box);

    if (!hiddenInput || !hiddenInput.value) {
        selH.value = "";
        if (hiddenInput) hiddenInput.value = "";
        input.value = "";
        input.placeholder = "Hora";
    } else {
        const existingHour = hiddenInput.value.split(":")[0];
        if (existingHour && Array.from(selH.options).some(opt => opt.value === existingHour)) {
            selH.value = existingHour;
            input.value = hiddenInput.value;
        } else {
            selH.value = "";
            if (hiddenInput) hiddenInput.value = "";
            input.value = "";
            input.placeholder = "Hora";
        }
    }

    function sync() {
        if (!selH.value) {
            if (hiddenInput) hiddenInput.value = "";
            input.value = "";
            if (typeof refreshSummary === 'function') refreshSummary();
            return;
        }
        const finalHour = String(selH.value).padStart(2, "0");
        const timeValue = `${finalHour}:00`;
        if (hiddenInput) hiddenInput.value = timeValue;
        input.value = timeValue;
        if (typeof refreshSummary === 'function') refreshSummary();
    }

    selH.addEventListener("change", sync);
}

/* =========================================
   26 SUBMIT POR AJAX (CORREGIDO)
========================================= */
async function submitReservaAjax(e) {
    e.preventDefault();

    const form = qs("#formReserva");
    if (!form) return;

    if (!state.categoria) {
        mostrarToast('⚠️ Debes seleccionar una categoría de vehículo', 'warning');
        return;
    }

    const catInput = qs("#categoria_id");
    if (catInput) {
        catInput.value = state.categoria.id;
        catInput.name = "id_categoria";
    }

    console.log("📦 Categoría a enviar:", state.categoria.id);
    console.log("📦 Input categoria_id value:", qs("#categoria_id")?.value);

    ensureCategoriaHiddenFix();
    ensureTotalsHidden();
    ensureProteccionHidden();
    ensureServiciosHidden();
    ensureDeliveryHidden();

    syncDateHiddenFromUI("#fecha_inicio_ui", "#fecha_inicio");
    syncDateHiddenFromUI("#fecha_fin_ui", "#fecha_fin");
    syncTimeHiddenFromUI("#hora_retiro_ui", "#hora_retiro");
    syncTimeHiddenFromUI("#hora_entrega_ui", "#hora_entrega");

    syncVueloField();
    syncDays();
    repaintCategoriaModalEstimados();
    refreshSummary();

    if (state.servicios.delivery) {
        const els = getDeliveryEls();
        if (els) computeDelivery(els);
    } else {
        qs("#delivery_activo").value = "0";
        qs("#delivery_total").value = "0";
        qs("#delivery_km").value = "0";
        qs("#delivery_direccion").value = "";
        qs("#delivery_ubicacion").value = "";
    }

    syncProteccionHidden();
    syncIndividualesHidden();
    syncAddonsHidden();
    syncTotalsHidden();

    syncTelefonoFinal();

    const checkbox = document.getElementById('differentDropoffAdmin');
    const sucursalRetiro = document.getElementById('sucursal_retiro');
    const sucursalEntrega = document.getElementById('sucursal_entrega');

    if (sucursalEntrega) {
        if (checkbox && !checkbox.checked) {
            sucursalEntrega.disabled = false;
            if (sucursalRetiro && sucursalRetiro.value) {
                sucursalEntrega.value = sucursalRetiro.value;
            }
        } else {
            sucursalEntrega.disabled = false;
        }
    }

    if (!validateBeforeSubmit()) return;

    const btn = qs("#btnReservar");
    const setLoading = (on) => {
        if (!btn) return;
        btn.disabled = on;
        btn.style.opacity = on ? "0.85" : "1";
        btn.style.cursor = on ? "not-allowed" : "pointer";
        btn.textContent = on ? "⏳ Registrando..." : "✅ Registrar reservación";
    };

    try {
        setLoading(true);

        const action = form.getAttribute("action");
        const fd = new FormData(form);

        if (state.categoria) {
            fd.set("id_categoria", String(state.categoria.id));
            const precioFinal = parseFloat(state.categoria.precio_dia || 0);
            fd.set("tarifa_base", String(precioFinal));
            fd.set("tarifa_modificada", String(precioFinal));
        }

        fd.set("telefono_cliente", qs("#telefono_cliente")?.value || "");

        fd.set("svc_dropoff", state.servicios.dropoff ? "1" : "0");
        fd.set("svc_delivery", state.servicios.delivery ? "1" : "0");
        fd.set("svc_gasolina", state.servicios.gasolina ? "1" : "0");

        if (state.servicios.delivery) {
            const els = getDeliveryEls();
            if (els) computeDelivery(els);

            fd.set("delivery_activo", "1");
            fd.set("delivery_total", String(state.delivery.total || 0));
            fd.set("delivery_km", String(state.delivery.km || 0));
            fd.set("delivery_direccion", String(state.delivery.direccion || ""));
            fd.set("delivery_ubicacion", String(state.delivery.ubicacion || "0"));

            const precioKm = qs("#deliveryPrecioKm")?.value || "0";
            fd.set("delivery_precio_km", precioKm);
        } else {
            fd.set("delivery_activo", "0");
            fd.set("delivery_total", "0");
            fd.set("delivery_km", "0");
            fd.set("delivery_direccion", "");
            fd.set("delivery_ubicacion", "");
            fd.set("delivery_precio_km", "0");
        }

        console.log("📤 Enviando FormData con id_categoria:", fd.get("id_categoria"));

        const res = await fetch(action, {
            method: "POST",
            headers: {
                "X-CSRF-TOKEN": getCsrf(),
                "Accept": "application/json"
            },
            body: fd
        });

        if (res.status === 422) {
            const data = await res.json().catch(() => null);
            const errors = data?.errors || {};

            let errorMsg = "Revisa los campos: falta información o hay datos inválidos.";
            if (errors.id_categoria) {
                errorMsg = "❌ Categoría: " + errors.id_categoria[0];
            } else if (errors.sucursal_retiro) {
                errorMsg = "❌ Sucursal de retiro: " + errors.sucursal_retiro[0];
            } else if (errors.sucursal_entrega) {
                errorMsg = "❌ Sucursal de entrega: " + errors.sucursal_entrega[0];
            } else if (errors.fecha_inicio) {
                errorMsg = "❌ Fecha de salida: " + errors.fecha_inicio[0];
            } else if (errors.fecha_fin) {
                errorMsg = "❌ Fecha de llegada: " + errors.fecha_fin[0];
            } else {
                const first = Object.values(errors)[0]?.[0];
                if (first) errorMsg = first;
            }

            mostrarToast(errorMsg, 'error');
            setLoading(false);
            return;
        }

        if (!res.ok) {
            const txt = await res.text().catch(() => "");
            console.error("Error al registrar:", res.status, txt);
            mostrarToast("Ocurrió un error al registrar la reservación. Revisa la consola.", 'error');
            setLoading(false);
            return;
        }

        const data = await res.json().catch(() => ({}));
        if (data?.redirect_url) form.dataset.redirect = data.redirect_url;

        const confirmPop = qs("#confirmPop");
        const redirectToActivas = () => {
            window.location.href = "/admin/reservaciones-activas";
        };

        if (confirmPop && !confirmPop.dataset.bound) {
            confirmPop.dataset.bound = "1";

            const confirmOk = qs("#confirmOk");
            if (confirmOk) {
                const newConfirmOk = confirmOk.cloneNode(true);
                confirmOk.parentNode.replaceChild(newConfirmOk, confirmOk);
                newConfirmOk.addEventListener("click", redirectToActivas);
            }

            const confirmClose = qs("#confirmClose");
            if (confirmClose) {
                const newConfirmClose = confirmClose.cloneNode(true);
                confirmClose.parentNode.replaceChild(newConfirmClose, confirmClose);
                newConfirmClose.addEventListener("click", redirectToActivas);
            }

            confirmPop.addEventListener("click", (ev) => {
                if (ev.target === confirmPop) redirectToActivas();
            });
        }

        closeAllPops();
        openPop(confirmPop);

    } catch (err) {
        console.error(err);
        mostrarToast("Error de conexión. Intenta de nuevo.", 'error');
    } finally {
        setLoading(false);
    }
}

/* =========================================
   27 TABS EN MODAL PROTECCIONES
========================================= */
function setProteTab(tabId) {
    const btns = qsa("#proteccionPop .tab-btn[data-tab]");
    const panels = qsa("#proteccionPop .tab-panel");

    btns.forEach(b => b.classList.toggle("is-active", b.dataset.tab === tabId));
    panels.forEach(p => p.classList.toggle("is-active", p.id === tabId));
}

function bindProteTabs() {
    const pop = qs("#proteccionPop");
    if (!pop || pop.dataset.boundTabs === "1") return;
    pop.dataset.boundTabs = "1";

    qsa("#proteccionPop .tab-btn[data-tab]").forEach((b) => {
        b.addEventListener("click", () => setProteTab(b.dataset.tab));
    });
}

/* =========================================
   28 LOAD PROTECCIONES (PAQUETES)
========================================= */
async function loadProtecciones() {
    const track = qs("#protePacksTrack");
    if (!track) return;

    track.innerHTML = `<div class="loading" style="padding:12px;font-weight:900;color:rgba(255,255,255,.9);">Cargando paquetes...</div>`;

    try {
        const res = await fetch("/admin/reservaciones/seguros", {
            headers: { "X-Requested-With": "XMLHttpRequest", "Accept": "application/json" }
        });

        const data = await res.json().catch(() => []);
        const arrRaw = Array.isArray(data) ? data : (data?.data || []);

        const arr = arrRaw.map((raw) => {
            const id = raw.id_paquete ?? raw.id ?? raw.idPaquete;
            const nombre = raw.nombre ?? "Protección";
            const desc = raw.descripcion ?? "";
            const precio = Number(raw.precio_por_dia ?? raw.precio_dia ?? raw.precio ?? 0);
            const charge = raw.tipo_cobro ?? raw.charge ?? "por_evento";
            return { id, nombre, desc, precio, charge };
        });

        arr.sort((a, b) => Number(b.precio || 0) - Number(a.precio || 0));

        if (!arr.length) {
            track.innerHTML = `<div class="loading" style="padding:12px;font-weight:900;color:rgba(255,255,255,.9);">No hay protecciones disponibles.</div>`;
            return;
        }

        track.innerHTML = "";

        function formatDescriptionAsList(desc) {
            if (!desc || desc === "") {
                return '<ul class="desc-list"><li>Sin descripción disponible</li></ul>';
            }

            let items = desc.split(/[-–—·•\n]+/).filter(item => item.trim().length > 0);

            items = items.map(item => item.trim().replace(/^\s*[-–—·•]\s*/, '').trim());

            if (items.length === 0) {
                items = [desc];
            }

            function aplicarNegritas(texto) {
                texto = texto.replace(/(\d+%)(?:\s*(deducible|Deducible|Perdida))?/gi, '<strong>$1</strong>');
                texto = texto.replace(/(\d{1,3}(?:,\d{3})*)\s*MXN/g, '<strong>$1 MXN</strong>');

                const palabrasClave = [
                    'El cliente es Responsable por el',
                    'de lado a lado',
                    'bumper a bumper',
                    'Gastos médicos',
                    'Asistencia en carretera Premium',
                    'Asistencia Premium',
                    'Tiempo perdido en taller, cubierto',
                    'Asistencia Legal, Cubierta',
                    'Responsabilidad civil',
                    'Cubierta toda la carrosería',
                    'NO CUBRE',
                    'Perdida total',
                    'Robo',
                    'No cubre',
                    'Incluye:'
                ];

                palabrasClave.forEach(palabra => {
                    const regex = new RegExp(`(${palabra})`, 'gi');
                    texto = texto.replace(regex, '<strong>$1</strong>');
                });

                return texto;
            }
            const listItems = items.map(item => {
                const textoOriginal = escapeHtml(item);
                const textoConNegritas = aplicarNegritas(textoOriginal);
                return `<li>${textoConNegritas}</li>`;
            }).join('');

            return `<ul class="desc-list">${listItems}</ul>`;
        }

        arr.forEach((p) => {
            const isFree = Number(p.precio || 0) <= 0;

            const card = document.createElement("article");
            card.className = "pack-card" + (isFree ? " pack-card--free" : "");
            card.style.minWidth = "320px";

            const descHtml = formatDescriptionAsList(p.desc);

            card.innerHTML = `
                <div class="body">
                    <h4>${escapeHtml(p.nombre)}</h4>
                    ${descHtml}
                    <div class="precio">
                        <strong>${money(p.precio).replace(" MXN", "")}</strong>
                        <span>MXN ${p.charge === "por_dia" ? "/ día" : ""}</span>
                    </div>
                    <div class="actions">
                        <button class="btn primary" type="button">Elegir</button>
                    </div>
                </div>
            `;

            card.addEventListener("click", (e) => {
                const btn = e.target.closest("button");
                if (!btn) return;

                setProteccion({
                    id: p.id,
                    nombre: p.nombre,
                    precio: p.precio,
                    charge: p.charge,
                    desc: p.desc
                });

                refreshProteccionUIHeader();
                closePop(qs("#proteccionPop"));
            });

            track.appendChild(card);
        });

    } catch (e) {
        console.error("Protecciones error:", e);
        track.innerHTML = `<div class="loading" style="padding:12px;font-weight:900;color:rgba(255,255,255,.9);">Error cargando protecciones.</div>`;
    }
}

/* =========================================
   29 LOAD ADDONS
========================================= */
async function loadAddons() {
    const list = qs("#addonsList");
    if (!list) return;

    list.innerHTML = `<div class="loading">Cargando adicionales...</div>`;

    try {
        const res = await fetch("/admin/reservaciones/servicios", {
            headers: { "X-Requested-With": "XMLHttpRequest", "Accept": "application/json" }
        });

        const data = await res.json().catch(() => []);
        const arrRaw = Array.isArray(data) ? data : (data?.data || []);

        if (!arrRaw.length) {
            list.innerHTML = `<div class="loading">No hay adicionales disponibles.</div>`;
            return;
        }

        list.innerHTML = "";

        arrRaw.forEach((raw) => {
            const id = raw.id_servicio ?? raw.id ?? raw.idServicio;
            const nombre = raw.nombre ?? "Adicional";
            const desc = raw.descripcion ?? "";
            const precio = Number(raw.precio ?? raw.costo ?? raw.monto ?? 0);
            const charge = raw.tipo_cobro ?? raw.charge ?? "por_evento";

            const current = state.addons.get(String(id));
            const qty = current ? Number(current.qty || 0) : 0;

            const card = document.createElement("article");
            card.className = "card-addon";
            card.dataset.id = String(id);

            card.innerHTML = `
                <div class="ad-left">
                    <div class="cp-title">${escapeHtml(nombre)}</div>
                    <div class="cp-sub">${escapeHtml(desc || "Servicio adicional.")}</div>
                    <div class="cp-meta">
                        <span class="pill">Cobro: ${charge === "por_dia" ? "Por día" : "Por evento"}</span>
                    </div>
                </div>
                <div class="ad-right">
                    <div class="cp-price">
                        <div class="muted small">Costo</div>
                        <div class="price-big">${money(precio).replace(" MXN", "")} <span>MXN${charge === "por_dia" ? " / día" : ""}</span></div>
                    </div>
                    <div class="qty-row">
                        <button class="qty-btn minus" type="button" aria-label="menos">−</button>
                        <div class="qty" data-qty>${qty}</div>
                        <button class="qty-btn plus" type="button" aria-label="más">+</button>
                    </div>
                </div>
            `;

            card.addEventListener("click", (e) => {
                const plus = e.target.closest(".plus");
                const minus = e.target.closest(".minus");
                if (!plus && !minus) return;

                const item = { id, nombre, precio, charge, desc };
                const cur = state.addons.get(String(id))?.qty || 0;
                const next = Math.max(0, Number(cur) + (plus ? 1 : -1));

                setAddonQty(item, next);

                const qtyEl = card.querySelector("[data-qty]");
                if (qtyEl) qtyEl.textContent = String(next);
            });

            list.appendChild(card);
        });

    } catch (e) {
        console.error("Addons error:", e);
        list.innerHTML = `<div class="loading">Error cargando adicionales...</div>`;
    }
}

/* =========================================
   30 PAISES + LADA + ISO2
========================================= */
const COUNTRY_DATA = [
    { name: "MÉXICO", iso2: "MX", dial: "+52" },
    { name: "ESTADOS UNIDOS", iso2: "US", dial: "+1" },
    { name: "AFGANISTÁN", iso2: "AF", dial: "+93" },
    { name: "ALBANIA", iso2: "AL", dial: "+355" },
    { name: "ALEMANIA", iso2: "DE", dial: "+49" },
    { name: "ANDORRA", iso2: "AD", dial: "+376" },
    { name: "ANGOLA", iso2: "AO", dial: "+244" },
    { name: "ANTIGUA Y BARBUDA", iso2: "AG", dial: "+1" },
    { name: "ARABIA SAUDITA", iso2: "SA", dial: "+966" },
    { name: "ARGELIA", iso2: "DZ", dial: "+213" },
    { name: "ARGENTINA", iso2: "AR", dial: "+54" },
    { name: "ARMENIA", iso2: "AM", dial: "+374" },
    { name: "AUSTRALIA", iso2: "AU", dial: "+61" },
    { name: "AUSTRIA", iso2: "AT", dial: "+43" },
    { name: "AZERBAIYÁN", iso2: "AZ", dial: "+994" },
    { name: "BAHAMAS", iso2: "BS", dial: "+1" },
    { name: "BANGLADESH", iso2: "BD", dial: "+880" },
    { name: "BARBADOS", iso2: "BB", dial: "+1" },
    { name: "BARÉIN", iso2: "BH", dial: "+973" },
    { name: "BÉLGICA", iso2: "BE", dial: "+32" },
    { name: "BELICE", iso2: "BZ", dial: "+501" },
    { name: "BENÍN", iso2: "BJ", dial: "+229" },
    { name: "BIELORRUSIA", iso2: "BY", dial: "+375" },
    { name: "BOLIVIA", iso2: "BO", dial: "+591" },
    { name: "BOSNIA Y HERZEGOVINA", iso2: "BA", dial: "+387" },
    { name: "BOTSUANA", iso2: "BW", dial: "+267" },
    { name: "BRASIL", iso2: "BR", dial: "+55" },
    { name: "BRUNÉI", iso2: "BN", dial: "+673" },
    { name: "BULGARIA", iso2: "BG", dial: "+359" },
    { name: "BURKINA FASO", iso2: "BF", dial: "+226" },
    { name: "BURUNDI", iso2: "BI", dial: "+257" },
    { name: "BUTÁN", iso2: "BT", dial: "+975" },
    { name: "CABO VERDE", iso2: "CV", dial: "+238" },
    { name: "CAMBOYA", iso2: "KH", dial: "+855" },
    { name: "CAMERÚN", iso2: "CM", dial: "+237" },
    { name: "CANADÁ", iso2: "CA", dial: "+1" },
    { name: "CATAR", iso2: "QA", dial: "+974" },
    { name: "CHAD", iso2: "TD", dial: "+235" },
    { name: "CHILE", iso2: "CL", dial: "+56" },
    { name: "CHINA", iso2: "CN", dial: "+86" },
    { name: "CHIPRE", iso2: "CY", dial: "+357" },
    { name: "CIUDAD DEL VATICANO", iso2: "VA", dial: "+379" },
    { name: "COLOMBIA", iso2: "CO", dial: "+57" },
    { name: "COMORAS", iso2: "KM", dial: "+269" },
    { name: "CONGO", iso2: "CG", dial: "+242" },
    { name: "COREA DEL NORTE", iso2: "KP", dial: "+850" },
    { name: "COREA DEL SUR", iso2: "KR", dial: "+82" },
    { name: "COSTA DE MARFIL", iso2: "CI", dial: "+225" },
    { name: "COSTA RICA", iso2: "CR", dial: "+506" },
    { name: "CROACIA", iso2: "HR", dial: "+385" },
    { name: "CUBA", iso2: "CU", dial: "+53" },
    { name: "DINAMARCA", iso2: "DK", dial: "+45" },
    { name: "DOMINICA", iso2: "DM", dial: "+1" },
    { name: "ECUADOR", iso2: "EC", dial: "+593" },
    { name: "EGIPTO", iso2: "EG", dial: "+20" },
    { name: "EL SALVADOR", iso2: "SV", dial: "+503" },
    { name: "EMIRATOS ÁRABES UNIDOS", iso2: "AE", dial: "+971" },
    { name: "ERITREA", iso2: "ER", dial: "+291" },
    { name: "ESLOVAQUIA", iso2: "SK", dial: "+421" },
    { name: "ESLOVENIA", iso2: "SI", dial: "+386" },
    { name: "ESPAÑA", iso2: "ES", dial: "+34" },
    { name: "ESTONIA", iso2: "EE", dial: "+372" },
    { name: "ESWATINI", iso2: "SZ", dial: "+268" },
    { name: "ETIOPÍA", iso2: "ET", dial: "+251" },
    { name: "FIJI", iso2: "FJ", dial: "+679" },
    { name: "FILIPINAS", iso2: "PH", dial: "+63" },
    { name: "FINLANDIA", iso2: "FI", dial: "+358" },
    { name: "FRANCIA", iso2: "FR", dial: "+33" },
    { name: "GABÓN", iso2: "GA", dial: "+241" },
    { name: "GAMBIA", iso2: "GM", dial: "+220" },
    { name: "GEORGIA", iso2: "GE", dial: "+995" },
    { name: "GHANA", iso2: "GH", dial: "+233" },
    { name: "GRANADA", iso2: "GD", dial: "+1" },
    { name: "GRECIA", iso2: "GR", dial: "+30" },
    { name: "GUATEMALA", iso2: "GT", dial: "+502" },
    { name: "GUINEA", iso2: "GN", dial: "+224" },
    { name: "GUINEA BISÁU", iso2: "GW", dial: "+245" },
    { name: "GUINEA ECUATORIAL", iso2: "GQ", dial: "+240" },
    { name: "GUYANA", iso2: "GY", dial: "+592" },
    { name: "HAITÍ", iso2: "HT", dial: "+509" },
    { name: "HONDURAS", iso2: "HN", dial: "+504" },
    { name: "HUNGRÍA", iso2: "HU", dial: "+36" },
    { name: "INDIA", iso2: "IN", dial: "+91" },
    { name: "INDONESIA", iso2: "ID", dial: "+62" },
    { name: "IRAK", iso2: "IQ", dial: "+964" },
    { name: "IRÁN", iso2: "IR", dial: "+98" },
    { name: "IRLANDA", iso2: "IE", dial: "+353" },
    { name: "ISLANDIA", iso2: "IS", dial: "+354" },
    { name: "ISRAEL", iso2: "IL", dial: "+972" },
    { name: "ITALIA", iso2: "IT", dial: "+39" },
    { name: "JAMAICA", iso2: "JM", dial: "+1" },
    { name: "JAPÓN", iso2: "JP", dial: "+81" },
    { name: "JORDANIA", iso2: "JO", dial: "+962" },
    { name: "KAZAJISTÁN", iso2: "KZ", dial: "+7" },
    { name: "KENIA", iso2: "KE", dial: "+254" },
    { name: "KIRGUISTÁN", iso2: "KG", dial: "+996" },
    { name: "KUWAIT", iso2: "KW", dial: "+965" },
    { name: "LAOS", iso2: "LA", dial: "+856" },
    { name: "LETONIA", iso2: "LV", dial: "+371" },
    { name: "LÍBANO", iso2: "LB", dial: "+961" },
    { name: "LIBERIA", iso2: "LR", dial: "+231" },
    { name: "LIBIA", iso2: "LY", dial: "+218" },
    { name: "LIECHTENSTEIN", iso2: "LI", dial: "+423" },
    { name: "LITUANIA", iso2: "LT", dial: "+370" },
    { name: "LUXEMBURGO", iso2: "LU", dial: "+352" },
    { name: "MADAGASCAR", iso2: "MG", dial: "+261" },
    { name: "MALASIA", iso2: "MY", dial: "+60" },
    { name: "MALAWI", iso2: "MW", dial: "+265" },
    { name: "MALDIVAS", iso2: "MV", dial: "+960" },
    { name: "MALÍ", iso2: "ML", dial: "+223" },
    { name: "MALTA", iso2: "MT", dial: "+356" },
    { name: "MARRUECOS", iso2: "MA", dial: "+212" },
    { name: "MAURICIO", iso2: "MU", dial: "+230" },
    { name: "MAURITANIA", iso2: "MR", dial: "+222" },
    { name: "MOLDAVIA", iso2: "MD", dial: "+373" },
    { name: "MÓNACO", iso2: "MC", dial: "+377" },
    { name: "MONGOLIA", iso2: "MN", dial: "+976" },
    { name: "MONTENEGRO", iso2: "ME", dial: "+382" },
    { name: "MOZAMBIQUE", iso2: "MZ", dial: "+258" },
    { name: "MYANMAR", iso2: "MM", dial: "+95" },
    { name: "NAMIBIA", iso2: "NA", dial: "+264" },
    { name: "NEPAL", iso2: "NP", dial: "+977" },
    { name: "NICARAGUA", iso2: "NI", dial: "+505" },
    { name: "NÍGER", iso2: "NE", dial: "+227" },
    { name: "NIGERIA", iso2: "NG", dial: "+234" },
    { name: "NORUEGA", iso2: "NO", dial: "+47" },
    { name: "NUEVA ZELANDA", iso2: "NZ", dial: "+64" },
    { name: "OMÁN", iso2: "OM", dial: "+968" },
    { name: "PAÍSES BAJOS", iso2: "NL", dial: "+31" },
    { name: "PAKISTÁN", iso2: "PK", dial: "+92" },
    { name: "PANAMÁ", iso2: "PA", dial: "+507" },
    { name: "PARAGUAY", iso2: "PY", dial: "+595" },
    { name: "PERÚ", iso2: "PE", dial: "+51" },
    { name: "POLONIA", iso2: "PL", dial: "+48" },
    { name: "PORTUGAL", iso2: "PT", dial: "+351" },
    { name: "REINO UNIDO", iso2: "GB", dial: "+44" },
    { name: "REPÚBLICA CHECA", iso2: "CZ", dial: "+420" },
    { name: "REPÚBLICA DOMINICANA", iso2: "DO", dial: "+1" },
    { name: "RUMANIA", iso2: "RO", dial: "+40" },
    { name: "RUSIA", iso2: "RU", dial: "+7" },
    { name: "SENEGAL", iso2: "SN", dial: "+221" },
    { name: "SERBIA", iso2: "RS", dial: "+381" },
    { name: "SINGAPUR", iso2: "SG", dial: "+65" },
    { name: "SUDÁFRICA", iso2: "ZA", dial: "+27" },
    { name: "SUECIA", iso2: "SE", dial: "+46" },
    { name: "SUIZA", iso2: "CH", dial: "+41" },
    { name: "TAILANDIA", iso2: "TH", dial: "+66" },
    { name: "TÚNEZ", iso2: "TN", dial: "+216" },
    { name: "TURQUÍA", iso2: "TR", dial: "+90" },
    { name: "UCRANIA", iso2: "UA", dial: "+380" },
    { name: "URUGUAY", iso2: "UY", dial: "+598" },
    { name: "VENEZUELA", iso2: "VE", dial: "+58" },
];

const TOP = ["MÉXICO", "ESTADOS UNIDOS"];
const REST = COUNTRY_DATA
    .filter(x => !TOP.includes(x.name))
    .sort((a, b) => norm(a.name).localeCompare(norm(b.name)));

const COUNTRIES = [
    COUNTRY_DATA.find(x => x.name === "MÉXICO"),
    COUNTRY_DATA.find(x => x.name === "ESTADOS UNIDOS"),
    ...REST
].filter(Boolean);

function titleCaseEs(s) {
    const str = String(s || "").toLowerCase();
    return str.replace(/(^|[\s-])([a-záéíóúñü])/gi, (m, p1, p2) => p1 + p2.toUpperCase());
}

function setPaisUIFromCountry(c) {
    if (!c) return;

    const paisHidden = qs("#pais");
    const flagUI = qs("#pais_flag_ui");
    const textUI = qs("#pais_text_ui");

    if (paisHidden) paisHidden.value = c.name;
    if (flagUI) flagUI.textContent = isoToFlag(c.iso2);
    if (textUI) textUI.textContent = titleCaseEs(c.name);
}

function setPhoneCountry(c) {
    if (!c) return;

    const ladaHid = qs("#telefono_lada");
    const flag = qs("#phone_flag");
    const code = qs("#phone_code");

    if (ladaHid) ladaHid.value = c.dial || "+52";
    if (flag) flag.textContent = isoToFlag(c.iso2);
    if (code) code.textContent = c.dial || "+52";

    setPaisUIFromCountry(c);
    syncTelefonoFinal();
}

function initPhoneCombo() {
    const root = qs("#phoneCombo");
    if (!root) return;

    const dd = qs("#phone_dd");
    const toggle = qs("#phone_toggle");
    const search = qs("#phone_search");
    const list = qs("#phone_list");

    function openDD() {
        dd.classList.add("is-open");
        render(search?.value || "");
        search?.focus();
    }
    function closeDD() {
        dd.classList.remove("is-open");
        if (search) search.value = "";
    }

    function render(q = "") {
        const qq = norm(q);
        const items = COUNTRIES.filter(c =>
            norm(c.name).includes(qq) || norm(c.dial).includes(qq)
        );

        list.innerHTML = "";
        if (!items.length) {
            list.innerHTML = `<div class="empty">Sin resultados</div>`;
            return;
        }

        items.forEach(c => {
            const row = document.createElement("div");
            row.className = "row";
            row.innerHTML = `
                <div class="l">
                    <span class="flag">${isoToFlag(c.iso2)}</span>
                    <span class="name">${c.name}</span>
                </div>
                <span class="dial">${c.dial}</span>
            `;
            row.addEventListener("click", () => {
                setPhoneCountry(c);
                closeDD();
            });
            list.appendChild(row);
        });
    }

    toggle?.addEventListener("click", () => {
        dd.classList.contains("is-open") ? closeDD() : openDD();
    });

    search?.addEventListener("input", () => render(search.value));
    search?.addEventListener("keydown", (e) => {
        if (e.key === "Escape") closeDD();
    });

    document.addEventListener("click", (e) => {
        if (!root.contains(e.target)) closeDD();
    });

    const initialName = norm(qs("#pais")?.value || "MÉXICO");
    const initial =
        COUNTRIES.find(c => norm(c.name) === initialName) ||
        COUNTRIES.find(c => c.name === "MÉXICO") ||
        COUNTRIES[0];

    setPhoneCountry(initial);
}

/* =========================================
   31 EVENTOS UI
========================================= */
function bindUI() {
    ["#fecha_inicio_ui", "#fecha_fin_ui"].forEach((id) => {
        qs(id)?.addEventListener("change", () => {
            syncDateHiddenFromUI(id, id.replace("_ui", ""));
            syncDays();
        });
    });

    ["#hora_retiro_ui", "#hora_entrega_ui"].forEach((id) => {
        qs(id)?.addEventListener("change", () => {
            syncTimeHiddenFromUI(id, id.replace("_ui", ""));
            refreshSummary();
        });
    });

    qs("#sucursal_retiro")?.addEventListener("change", () => {
        syncVueloField();
        refreshSummary();
    });
    qs("#sucursal_entrega")?.addEventListener("change", () => {
        syncVueloField();
        refreshSummary();
    });

    qs("#gasolinaToggle")?.addEventListener("change", (e) => {
        const active = !!e.target.checked;
        setGasolinaActive(active);
    });

    qs("#dropoffToggle")?.addEventListener("change", (e) => {
        const active = !!e.target.checked;
        setDropoffActive(active);
    });

    qs("#deliveryToggle")?.addEventListener("change", (e) => {
        const active = !!e.target.checked;
        setDeliveryActive(active);
    });

    const catPop = qs("#catPop");
    qs("#btnCategorias")?.addEventListener("click", () => {
        repaintCategoriaModalEstimados();
        openPop(catPop);
    });
    qs("#catClose")?.addEventListener("click", () => closePop(catPop));
    qs("#catCancel")?.addEventListener("click", () => closePop(catPop));

    catPop?.addEventListener("click", (e) => {
        const card = e.target.closest(".card-pick");
        if (!card) return;

        const id = card.dataset.id;
        const nombre = card.dataset.nombre || "";
        const desc = card.dataset.desc || "";
        const precio = Number(card.dataset.precio || 0);
        const precioKm = Number(card.dataset.precioKm || 0);
        const img = card.dataset.img || "";
        const capacidad = parseFloat(card.dataset.litros || 0);

        setCategoria({ id, nombre, desc, precio_dia: precio, precio_km: precioKm, img, capacidad_tanque: capacidad });
        closePop(catPop);
    });

    qs("#catRemove")?.addEventListener("click", () => setCategoria(null));

    const protPop = qs("#proteccionPop");
    qs("#btnProtecciones")?.addEventListener("click", async () => {
        openPop(protPop);
        setProteTab("tab-paquetes");
        await loadProtecciones();
        repaintIndividualesUI();
        refreshProteccionUIHeader();
    });

    qs("#proteClose")?.addEventListener("click", () => closePop(protPop));
    qs("#proteCancel")?.addEventListener("click", () => closePop(protPop));

    qs("#proteRemove")?.addEventListener("click", () => {
        setProteccion(null);
        clearIndividuales();
        refreshProteccionUIHeader();
        syncTotalsHidden();
        refreshSummary();
    });

    qs("#proteApply")?.addEventListener("click", () => {
        syncProteccionHidden();
        syncIndividualesHidden();
        refreshProteccionUIHeader();
        syncTotalsHidden();
        refreshSummary();
        closePop(protPop);
    });

    document.addEventListener("click", (e) => {
        const card = e.target.closest(".individual-item");
        if (!card) return;

        const isBtn = e.target.closest("button,a,input,textarea,select");
        if (isBtn) return;

        toggleIndividualFromCard(card);
    });

    const addPop = qs("#addonsPop");
    qs("#btnAddons")?.addEventListener("click", async () => {
        openPop(addPop);
        await loadAddons();
    });

    qs("#addonsClose")?.addEventListener("click", () => closePop(addPop));
    qs("#addonsCancel")?.addEventListener("click", () => closePop(addPop));
    qs("#addonsApply")?.addEventListener("click", () => {
        closePop(addPop);
        refreshAddonsBadge();
        refreshSummary();
        syncTotalsHidden();
    });
    qs("#addonsClear")?.addEventListener("click", () => {
        state.addons.clear();
        syncAddonsHidden();
        refreshAddonsBadge();
        syncTotalsHidden();
        refreshSummary();
    });

    const resPop = qs("#resumenPop");
    qs("#btnResumen")?.addEventListener("click", () => {
        syncDays();
        repaintCategoriaModalEstimados();
        refreshProteccionUIHeader();
        refreshSummary();
        openPop(resPop);
    });

    qs("#resumenClose")?.addEventListener("click", () => closePop(resPop));
    qs("#resumenOk")?.addEventListener("click", () => closePop(resPop));

    qsa(".pop.modal").forEach((pop) => {
        pop.addEventListener("click", (e) => {
            if (e.target !== pop) return;
            if (pop.id === "confirmPop") return;
            closePop(pop);
        });
    });

    qs("#telefono_ui")?.addEventListener("input", syncTelefonoFinal);

    qs("#formReserva")?.addEventListener("submit", submitReservaAjax);

    bindProteTabs();

    initTarifaEdit();
}

/* =========================================
   32 BOOT (INICIALIZACIÓN)
========================================= */
document.addEventListener("DOMContentLoaded", () => {
    ensureCategoriaHiddenFix();
    ensureTotalsHidden();
    ensureProteccionHidden();
    ensureServiciosHidden();
    ensureDeliveryHidden();

    state.servicios.dropoff = String(qs("#svc_dropoff")?.value || "0") === "1";
    state.servicios.gasolina = String(qs("#svc_gasolina")?.value || "0") === "1";

    const dropT = qs("#dropoffToggle");
    if (dropT) dropT.checked = state.servicios.dropoff;

    const gasT = qs("#gasolinaToggle");
    if (gasT) gasT.checked = state.servicios.gasolina;

    syncVueloField();

    bindDeliveryUI();
    bindDropoffUI();

    syncDays();
    repaintCategoriaModalEstimados();

    syncProteccionHidden();
    syncIndividualesHidden();
    repaintIndividualesUI();
    refreshProteccionUIHeader();

    syncAddonsHidden();
    syncTotalsHidden();
    refreshSummary();

    initFlatpickrModalCalendar();
    initTimeSelectors();

    initPhoneCombo();
    syncTelefonoFinal();

    bindUI();
    initAddonsWithSwitch();

    setTimeout(() => {
        initSelect2EnAdicionales();
    }, 500);
});

/* =========================================
   33 FUERZA RECÁLCULO
========================================= */
function forceRecalc() {
    console.log("🔥 FORZANDO RECÁLCULO TOTAL");

    syncDays();

    if (state.servicios.delivery) {
        const els = getDeliveryEls();
        if (els) computeDelivery(els);
    }

    if (state.servicios.dropoff) {
        const els = getDropoffEls();
        if (els) computeDropoff(els);
    }

    if (state.servicios.gasolina) {
        computeGasolina();
    }

    syncTotalsHidden();
    refreshSummary();
}

/* =========================================
   34 EXPONER API GLOBAL
========================================= */
window._reservaAPI = {
    setGasolinaActive,
    setDropoffActive,
    setDeliveryActive,
    setAddonQty,
    setProteccion,
    setCategoria,
    syncTotalsHidden,
    refreshSummary,
    forceRecalc,
    getState: () => state,
    loadAddons
};

/* =========================================
   35 CARGA DE EDICIÓN (RESERVACIÓN EXISTENTE)
========================================= */
window.addEventListener("DOMContentLoaded", async () => {

    const API = window._reservaAPI;

    if (!window.reservacionEditar || !API) {
        console.warn("⚠️ No hay reservación o API");
        return;
    }

    const r = window.reservacionEditar;
    console.log("🟢 EDITANDO:", r);

    if (r.id_categoria) {
        const card = document.querySelector(`.card-pick[data-id="${r.id_categoria}"]`);
        if (card) {
            const categoria = {
                id: card.dataset.id,
                nombre: card.dataset.nombre,
                desc: card.dataset.desc,
                precio_dia: parseFloat(card.dataset.precio || 0),
                precio_km: parseFloat(card.dataset.precioKm || 0),
                capacidad_tanque: parseFloat(card.dataset.litros || 0)
            };
            console.log("🚗 SET CATEGORIA:", categoria);
            API.setCategoria(categoria);
            await new Promise(res => setTimeout(res, 150));
        } else {
            console.warn("❌ No encontró categoría en DOM");
        }
    }

    if (r.delivery_activo == 1) {
        console.log("🚚 Activando delivery");
        API.setDeliveryActive(true);
        await new Promise(res => setTimeout(res, 150));

        const sel = document.getElementById("deliveryUbicacion");
        const km = document.getElementById("deliveryKm");
        const dir = document.getElementById("deliveryDireccion");

        if (sel && r.delivery_ubicacion != null) {
            sel.value = String(r.delivery_ubicacion);
            sel.dispatchEvent(new Event("change"));
        }
        if (km && r.delivery_km) {
            km.value = r.delivery_km;
            km.dispatchEvent(new Event("input"));
        }
        if (dir && r.delivery_direccion) {
            dir.value = r.delivery_direccion;
        }
    }

    if (window.serviciosEditar?.length) {
        for (const s of window.serviciosEditar) {
            console.log("➡️ Servicio:", s);

            if (s.id_servicio == 1) {
                API.setGasolinaActive(true);
            }

            if (s.id_servicio == 11) {
                API.setDropoffActive(true);
                await new Promise(res => setTimeout(res, 150));

                const dSel = document.getElementById("deliveryUbicacion");
                const dKm = document.getElementById("deliveryKm");
                const dDir = document.getElementById("deliveryDireccion");

                const sel = document.getElementById("dropUbicacion");
                const km = document.getElementById("dropKm");
                const dir = document.getElementById("dropDireccion");

                if (dSel && sel) {
                    sel.value = dSel.value;
                    sel.dispatchEvent(new Event("change"));
                }
                if (dKm && km) {
                    km.value = dKm.value;
                    km.dispatchEvent(new Event("input"));
                }
                if (dDir && dir) {
                    dir.value = dDir.value;
                }
            }

            if (s.id_servicio == 12) {
                API.setDeliveryActive(true);
            }

            if (![1, 11, 12].includes(s.id_servicio)) {
                const item = {
                    id: s.id_servicio,
                    nombre: s.nombre,
                    precio: s.precio_unitario,
                    charge: "por_evento"
                };
                API.setAddonQty(item, s.cantidad || 1);
            }
        }
    }

    if (window.seguroEditar) {
        console.log("🔒 Cargando protección");
        API.setProteccion({
            id: window.seguroEditar.id_paquete,
            nombre: window.seguroEditar.nombre,
            precio: window.seguroEditar.precio_por_dia,
            charge: "por_dia"
        });
    }

    API.forceRecalc();
    console.log("🧠 STATE FINAL:", API.getState());
    console.log("✅ EDICIÓN CARGADA COMPLETA");

});

/* =========================================
   36 SELECT2 CON ICONOS
========================================= */
$(document).ready(function() {
    initSelect2Sucursales();
});

function initSelect2Sucursales() {
    function formatOption(option) {
        let iconClass = 'fa-location-dot';

        if (option.id && window.iconosPorId && window.iconosPorId[option.id]) {
            iconClass = window.iconosPorId[option.id];
        }

        return $(
            '<span><i class="fa-solid ' + iconClass + '"></i>' + option.text + '</span>'
        );
    }

    const select2Config = {
        templateResult: formatOption,
        templateSelection: formatOption,
        escapeMarkup: function(m) { return m; },
        width: '100%',
        minimumResultsForSearch: Infinity,
        dropdownCssClass: "animated--fade-in"
    };

    $('#sucursal_retiro').select2(select2Config);
    $('#sucursal_entrega').select2(select2Config);
}

/* =========================================
   37 SELECT2 EN DELIVERY Y DROPOFF
========================================= */
function initSelect2EnAdicionales() {
    const select2Config = {
        placeholder: "Buscar ubicación...",
        allowClear: false,
        width: '100%',
        dropdownCssClass: "select2-dropdown-custom",
        minimumInputLength: 0,
        language: {
            noResults: function() {
                return "No se encontraron ubicaciones";
            },
            searching: function() {
                return "Buscando...";
            }
        }
    };

    // Delivery
    const deliverySelect = document.getElementById('deliveryUbicacion');
    if (deliverySelect) {
        // Destruir instancia anterior si existe
        if ($(deliverySelect).data('select2')) {
            $(deliverySelect).select2('destroy');
        }

        // Reinicializar
        $(deliverySelect).select2(select2Config);

        // Sincronizar el valor actual del select original con Select2
        const currentValue = deliverySelect.value;
        if (currentValue) {
            $(deliverySelect).val(currentValue).trigger('change');
        }

        // Evento change - disparar evento nativo para que JS lo detecte
        $(deliverySelect).off('change.delivery').on('change.delivery', function(e) {
            const nativeEvent = new Event('change', { bubbles: true });
            deliverySelect.dispatchEvent(nativeEvent);
        });
    }

    // Dropoff
    const dropoffSelect = document.getElementById('dropUbicacion');
    if (dropoffSelect) {
        if ($(dropoffSelect).data('select2')) {
            $(dropoffSelect).select2('destroy');
        }

        $(dropoffSelect).select2(select2Config);

        const currentValue = dropoffSelect.value;
        if (currentValue) {
            $(dropoffSelect).val(currentValue).trigger('change');
        }

        $(dropoffSelect).off('change.dropoff').on('change.dropoff', function(e) {
            const nativeEvent = new Event('change', { bubbles: true });
            dropoffSelect.dispatchEvent(nativeEvent);
        });
    }
}
function refreshSelect2OnShow() {
    const deliveryFields = document.getElementById('deliveryFields');
    if (deliveryFields && deliveryFields.style.display === 'block') {
        setTimeout(() => {
            const select = document.getElementById('deliveryUbicacion');
            if (select && $(select).data('select2')) {
                $(select).select2('open');
            }
        }, 100);
    }

    const dropoffFields = document.getElementById('dropoffFields');
    if (dropoffFields && dropoffFields.style.display === 'block') {
        setTimeout(() => {
            const select = document.getElementById('dropUbicacion');
            if (select && $(select).data('select2')) {
                $(select).select2('open');
            }
        }, 100);
    }
}

$(document).ready(function() {
    initSelect2Sucursales();
});

/* =========================================
   38 CHECKBOX "DEVOLVER EN OTRO DESTINO"
========================================= */
document.addEventListener('DOMContentLoaded', function() {
    const checkbox = document.getElementById('differentDropoffAdmin');
    const dropoffWrapper = document.getElementById('dropoffWrapperAdmin');
    const dropoffSelect = document.getElementById('sucursal_entrega');
    const pickupSelect = document.getElementById('sucursal_retiro');
    const resSucursalEntrega = document.getElementById('resSucursalEntrega');

    if (checkbox && dropoffWrapper && dropoffSelect) {

        dropoffWrapper.style.display = 'none';
        dropoffSelect.disabled = true;

        checkbox.addEventListener('change', function() {
            if (this.checked) {
                dropoffWrapper.style.display = 'block';
                dropoffSelect.disabled = false;

                if (pickupSelect && pickupSelect.value && !dropoffSelect.value) {
                    dropoffSelect.value = pickupSelect.value;
                }
            } else {
                dropoffWrapper.style.display = 'none';
                dropoffSelect.disabled = true;

                if (pickupSelect && pickupSelect.value) {
                    dropoffSelect.value = pickupSelect.value;
                }
            }

            if (typeof refreshSummary === 'function') {
                refreshSummary();
            }
        });

        if (pickupSelect) {
            pickupSelect.addEventListener('change', function() {
                if (!checkbox.checked && this.value) {
                    dropoffSelect.value = this.value;

                    if (typeof refreshSummary === 'function') {
                        refreshSummary();
                    }
                }
            });
        }

        dropoffSelect.addEventListener('change', function() {
            if (typeof refreshSummary === 'function') {
                refreshSummary();
            }
        });
    }
});

/* =========================================
   39 NAVBAR RESERVACIONES
========================================= */
(function() {
    function crearNavbarReservaciones() {
        if (document.getElementById('resNavbar')) return;

        const navbarHTML = `
            <div class="res-navbar" id="resNavbar">
                <div class="container-res">
                    <div class="nav-content-wrapper">
                        <div class="left-group">
                            <a href="/" class="logo">VIAJERO</a>
                            <span class="page-title">Nueva reservación</span>
                        </div>
                        <div class="nav-actions">
                            <button class="btn-resumen-minimal" id="btnResumenNav">
                                <i class="fa-solid fa-file-invoice"></i>
                                <span>VER RESUMEN</span>
                            </button>
                            <button class="btn-salir-minimal" id="btnSalirNav" title="Salir">
                                ✕
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        `;

        document.body.insertAdjacentHTML('afterbegin', navbarHTML);

        const btnResumenOriginal = document.getElementById('btnResumen');
        const dispararResumen = () => btnResumenOriginal && btnResumenOriginal.click();
        document.getElementById('btnResumenNav')?.addEventListener('click', dispararResumen);

        const salirUrl = '/admin/reservaciones-activas';
        document.getElementById('btnSalirNav')?.addEventListener('click', () => {
            window.location.href = salirUrl;
        });
    }

    function initScrollEffect() {
        const navbar = document.getElementById('resNavbar');
        if (!navbar) return;
        window.addEventListener('scroll', () => {
            if (window.scrollY > 40) navbar.classList.add('scrolled');
            else navbar.classList.remove('scrolled');
        });
    }

    function prepararPagina() {
        const topOriginal = document.querySelector('.top');
        if (topOriginal) topOriginal.style.setProperty('display', 'none', 'important');
        document.body.style.paddingTop = "115px";
    }

    document.addEventListener('DOMContentLoaded', () => {
        crearNavbarReservaciones();
        initScrollEffect();
        prepararPagina();
    });
})();

/* =========================================
   40 EDITAR TARIFA DESDE LA VISTA PREVIA DE CATEGORÍA
========================================= */
function initEditarCategoriaPreview() {
    const btnEditar = document.getElementById('btnEditarCategoriaPreview');
    const container = document.getElementById('catMiniRate');

    if (!btnEditar || !container) return;

    const newBtn = btnEditar.cloneNode(true);
    btnEditar.parentNode.replaceChild(newBtn, btnEditar);

    newBtn.addEventListener('click', (e) => {
        e.stopPropagation();

        if (!window._reservaAPI || !window._reservaAPI.getState().categoria) {
            mostrarToast('Primero debes seleccionar una categoría', 'warning');
            return;
        }

        const state = window._reservaAPI.getState();
        if (!state.categoria) return;

        if (container.querySelector('input')) return;

        const precioActual = parseFloat(state.categoria.precio_dia || 0);

        const input = document.createElement('input');
        input.type = 'number';
        input.value = precioActual.toFixed(2);
        input.min = 0;
        input.step = 0.01;

        Object.assign(input.style, {
            width: '100px',
            padding: '4px 8px',
            border: '1px solid #2563eb',
            borderRadius: '8px',
            fontWeight: '600',
            fontSize: '14px',
            color: '#333',
            outline: 'none'
        });

        const originalText = container.textContent;
        container.textContent = '';
        container.appendChild(input);
        input.focus();
        input.select();

        const guardar = () => {
            let nuevoValor = parseFloat(input.value);

            if (isNaN(nuevoValor) || nuevoValor < 0) {
                nuevoValor = precioActual;
            }

            state.categoria.precio_dia = nuevoValor;

            const money = (n) => {
                const num = Number(n || 0);
                return `$${num.toLocaleString("es-MX", {
                    minimumFractionDigits: 2,
                    maximumFractionDigits: 2
                })} MXN`;
            };

            container.textContent = `${money(nuevoValor).replace(" MXN", "")} MXN / día`;

            const sub = document.getElementById('catSelSub');
            if (sub) {
                sub.textContent = `${money(nuevoValor)} / día · ${state.days || 0} día(s)`;
            }

            if (window._reservaAPI) {
                window._reservaAPI.syncTotalsHidden();
                window._reservaAPI.refreshSummary();
            }
        };

        input.addEventListener('blur', guardar);
        input.addEventListener('keydown', (ev) => {
            if (ev.key === 'Enter') {
                ev.preventDefault();
                input.blur();
            }
        });
    });
}

document.addEventListener('DOMContentLoaded', () => {
    setTimeout(() => {
        initEditarCategoriaPreview();
    }, 500);
});

const observerPreview = new MutationObserver(() => {
    const btn = document.getElementById('btnEditarCategoriaPreview');
    if (btn && !btn.hasAttribute('data-initialized')) {
        btn.setAttribute('data-initialized', 'true');
        initEditarCategoriaPreview();
    }
});
observerPreview.observe(document.body, { childList: true, subtree: true });

/* =========================================
   41 MODAL DE CARACTERÍSTICAS DE CATEGORÍAS
========================================= */
(function() {
    let featuresModal = null;
    function closeFeaturesModal() {
        if (featuresModal) {
            featuresModal.style.display = 'none';
            const catPop = document.getElementById('catPop');
            if (catPop && catPop.style.display !== 'flex') {
                catPop.style.display = 'flex';
            }
        }
    }
    function initFeaturesModal() {
        if (featuresModal && document.body.contains(featuresModal)) {
            return featuresModal;
        }

        const existingModal = document.getElementById('featuresModal');
        if (existingModal) {
            existingModal.remove();
        }
        featuresModal = document.createElement('div');
        featuresModal.id = 'featuresModal';
        featuresModal.className = 'modal-features';
        featuresModal.style.cssText = 'position:fixed;top:0;left:0;width:100%;height:100%;background:rgba(0,0,0,0.65);display:none;align-items:center;justify-content:center;z-index:10001;backdrop-filter:blur(2px);';
        featuresModal.innerHTML = `
            <div class="modal-box" style="background:white;width:min(500px,90vw);border-radius:24px;overflow:hidden;box-shadow:0 30px 70px rgba(0,0,0,0.3);animation:modalFadeIn 0.2s ease;">
                <div class="header" style="background:linear-gradient(180deg, var(--brand), var(--brand-dark)) !important;border-bottom:1px solid rgba(227,0,0,0.3);color:white;padding:18px 20px;display:flex;justify-content:space-between;align-items:center;">
                    <h3 style="margin:0;font-size:20px;font-weight:900;display:flex;align-items:center;gap:10px;color:white !important;">
                        <i class='bx bx-car' style="color:white !important;"></i>
                        <span id="featuresCatName" style="color:white !important;">Categoría</span>
                    </h3>
                    <button id="closeFeaturesModalBtn" style="background:rgba(255,255,255,0.2);border:none;color:white;width:36px;height:36px;border-radius:50%;cursor:pointer;font-size:18px;display:flex;align-items:center;justify-content:center;transition:all 0.2s ease;">✖</button>
                </div>
                <div class="body" style="padding:20px;max-height:60vh;overflow-y:auto;">
                    <div class="features-list" id="featuresListContainer" style="display:flex;flex-direction:column;gap:12px;"></div>
                </div>
            </div>
        `;

        document.body.appendChild(featuresModal);

        const closeBtn = featuresModal.querySelector('#closeFeaturesModalBtn');
        if (closeBtn) {
            closeBtn.addEventListener('click', function(e) {
                e.preventDefault();
                e.stopPropagation();
                closeFeaturesModal();
            });
        }
        featuresModal.addEventListener('click', function(e) {
            if (e.target === featuresModal) {
                closeFeaturesModal();
            }
        });

        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape' && featuresModal && featuresModal.style.display === 'flex') {
                closeFeaturesModal();
            }
        });

        return featuresModal;
    }
    function openFeaturesModal(catName, features) {
        const modal = initFeaturesModal();
        const nameSpan = document.getElementById('featuresCatName');
        const container = document.getElementById('featuresListContainer');

        if (nameSpan) nameSpan.textContent = catName || 'Categoría';

        if (container && features && Array.isArray(features)) {
            container.innerHTML = '';
            features.forEach(f => {
                const item = document.createElement('div');
                item.className = 'feature-item';
                item.setAttribute('style', 'display:flex;align-items:center;gap:14px;padding:12px 16px;background:#f8fafc;border-radius:14px;border:1px solid #e2e8f0;transition:all 0.2s;');
                item.innerHTML = `
                    <i class="${f.icon || 'bx bx-check'}" style="font-size:24px;width:32px;text-align:center;"></i>
                    <span class="feature-text" style="font-weight:700;color:#1e293b;font-size:15px;">${escapeHtml(f.text)}</span>
                `;
                container.appendChild(item);
            });
        }

        modal.style.display = 'flex';
    }
    function escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }
    window.openFeaturesModal = openFeaturesModal;
    window.closeFeaturesModal = closeFeaturesModal;
    document.addEventListener('click', function(e) {
        const infoBtn = e.target.closest('.info-categoria-btn');
        if (infoBtn) {
            e.preventDefault();
            e.stopPropagation();

            const card = infoBtn.closest('.card-pick');
            if (card) {
                const catName = card.querySelector('.cp-title')?.textContent || 'Categoría';
                let features = [];
                const featuresData = card.dataset.caracteristicas;
                if (featuresData) {
                    try {
                        features = JSON.parse(featuresData);
                    } catch(e) {
                        console.error('Error parsing features:', e);
                    }
                }
                if (!features || !features.length) {
                    features = [
                        { icon: 'bx bx-infinite', text: 'Km ilimitados' },
                        { icon: 'bx bx-shield-quarter', text: 'Relevo responsabilidad' },
                        { icon: 'bx bx-user', text: '5 pasajeros' },
                        { icon: 'bx bxl-apple', text: 'Apple CarPlay' },
                        { icon: 'bx bxl-android', text: 'Android Auto' },
                        { icon: 'bx bx-wind', text: 'Aire Acondicionado' },
                        { icon: 'bx bx-cog', text: 'Automático' }
                    ];
                }

                openFeaturesModal(catName, features);
            }
        }
    });
})();

function seleccionarCategoriaReservacion(cardElement) {
    const id = cardElement.dataset.id;
    const nombre = cardElement.dataset.nombre || "";
    const desc = cardElement.dataset.desc || "";
    const precio = Number(cardElement.dataset.precio || 0);
    const precioKm = Number(cardElement.dataset.precioKm || 0);
    const img = cardElement.dataset.img || "";
    const capacidad = parseFloat(cardElement.dataset.litros || 0);
    if (window._reservaAPI && window._reservaAPI.setCategoria) {
        window._reservaAPI.setCategoria({
            id, nombre, desc,
            precio_dia: precio,
            precio_km: precioKm,
            img,
            capacidad_tanque: capacidad
        });
    }
    const catPop = document.getElementById('catPop');
    if (catPop) {
        catPop.style.display = 'none';
    }
}
})();

/* =========================================
   42 ACORDEÓN Y FLUJO SECUENCIAL
========================================= */
(function() {
    "use strict";

    let seccionesCompletadas = {
        categoria: false
    };

    function expandirSeccion(seccionId) {
        const seccion = document.querySelector(`.acordeon-item[data-seccion="${seccionId}"]`);
        if (!seccion) {
            console.error(`❌ Sección ${seccionId} no encontrada`);
            return;
        }

        seccion.style.display = 'block';

        const body = seccion.querySelector('.stack-body');
        const indicator = seccion.querySelector('.stack-indicator');

        if (body && !body.classList.contains('expanded')) {
            body.classList.add('expanded');
            console.log(`📂 Expandida sección: ${seccionId}`);
        }
        if (indicator && !indicator.classList.contains('expanded')) {
            indicator.classList.add('expanded');
        }

        setTimeout(() => {
            seccion.scrollIntoView({ behavior: 'smooth', block: 'start' });
        }, 100);
    }

    function colapsarSeccion(seccionId) {
        const seccion = document.querySelector(`.acordeon-item[data-seccion="${seccionId}"]`);
        if (!seccion) return;

        const body = seccion.querySelector('.stack-body');
        const indicator = seccion.querySelector('.stack-indicator');

        if (body && body.classList.contains('expanded')) {
            body.classList.remove('expanded');
            console.log(`📂 Colapsada sección: ${seccionId}`);
        }
        if (indicator && indicator.classList.contains('expanded')) {
            indicator.classList.remove('expanded');
        }
    }

    function marcarCompletada(seccionId) {
        const seccion = document.querySelector(`.acordeon-item[data-seccion="${seccionId}"]`);
        if (!seccion) return;

        const head = seccion.querySelector('.stack-head');
        if (head && !head.classList.contains('stack-completed')) {
            head.classList.add('stack-completed');
        }

        seccionesCompletadas[seccionId] = true;
    }

    function irASiguienteSeccion(seccionActualId) {
        const seccionActual = document.querySelector(`.acordeon-item[data-seccion="${seccionActualId}"]`);
        const siguienteId = seccionActual?.dataset.siguiente;

        if (!siguienteId || siguienteId === 'final') {
            const btnReservar = document.getElementById('btnReservar');
            if (btnReservar) {
                btnReservar.scrollIntoView({ behavior: 'smooth', block: 'center' });
                if (typeof mostrarToast === 'function') {
                    mostrarToast('✅ Formulario completo, ya puedes registrar la reservación', 'success');
                }
            }
            return;
        }

        let tieneSeleccion = false;

        if (seccionActualId === 'adicionales') {
            tieneSeleccion = tieneAdicionalesSeleccionados();
            console.log(`📦 Adicionales tiene selección: ${tieneSeleccion}`);
        } else if (seccionActualId === 'protecciones') {
            tieneSeleccion = tieneProteccionesSeleccionadas();
            console.log(`🔒 Protecciones tiene selección: ${tieneSeleccion}`);
        }

        if (!tieneSeleccion) {
            colapsarSeccion(seccionActualId);
            console.log(`📂 Sección ${seccionActualId} COLAPSADA (sin selección)`);
        } else {
            console.log(`📂 Sección ${seccionActualId} se MANTIENE ABIERTA (tiene selección)`);
        }

        expandirSeccion(siguienteId);
    }

    function initAcordeon() {
        document.querySelectorAll('.acordeon-item').forEach(seccion => {
            seccion.style.display = 'block';
        });

        colapsarSeccion('categoria');
        colapsarSeccion('adicionales');
        colapsarSeccion('protecciones');
        colapsarSeccion('cliente');

        document.querySelectorAll('.acordeon-item .stack-head').forEach(header => {
            const newHeader = header.cloneNode(true);
            header.parentNode.replaceChild(newHeader, header);

            newHeader.addEventListener('click', (e) => {
                if (e.target.classList.contains('btn-siguiente')) return;

                const seccion = newHeader.closest('.acordeon-item');
                const seccionId = seccion?.dataset.seccion;
                const body = seccion?.querySelector('.stack-body');
                const indicator = seccion?.querySelector('.stack-indicator');

                if (body) body.classList.toggle('expanded');
                if (indicator) indicator.classList.toggle('expanded');

                console.log(`🖱️ Click manual en ${seccionId}`);
            });
        });

        document.querySelectorAll('.btn-siguiente').forEach(btn => {
            const newBtn = btn.cloneNode(true);
            btn.parentNode.replaceChild(newBtn, btn);

            newBtn.addEventListener('click', (e) => {
                e.stopPropagation();
                const seccion = newBtn.closest('.acordeon-item');
                const seccionId = seccion?.dataset.seccion;

                if (seccionId === 'adicionales' || seccionId === 'protecciones') {
                    console.log(`➡️ Siguiente desde ${seccionId}`);
                    irASiguienteSeccion(seccionId);
                }
            });
        });
    }

    function validarSucursalesLocal() {
        const sucRetiro = document.getElementById("sucursal_retiro")?.value;
        const sucEntrega = document.getElementById("sucursal_entrega")?.value;

        if (!sucRetiro) {
            if (typeof mostrarToast === 'function') {
                mostrarToast('⚠️ Primero debes seleccionar las sucursales de RETIRO y ENTREGA', 'warning');
            } else {
                alert('⚠️ Primero debes seleccionar las sucursales de RETIRO y ENTREGA');
            }
            return false;
        }
        return true;
    }

    function validarFechasHorasLocal() {
        if (!validarSucursalesLocal()) return false;

        const fechaInicio = document.getElementById("fecha_inicio")?.value;
        const fechaFin = document.getElementById("fecha_fin")?.value;
        const horaRetiro = document.getElementById("hora_retiro")?.value;
        const horaEntrega = document.getElementById("hora_entrega")?.value;

        if (!fechaInicio || !fechaFin || !horaRetiro || !horaEntrega) {
            if (typeof mostrarToast === 'function') {
                mostrarToast('⚠️ Completa FECHA y HORA de salida y llegada', 'warning');
            } else {
                alert('⚠️ Completa FECHA y HORA de salida y llegada');
            }
            return false;
        }
        return true;
    }

    function initBotonBuscar() {
        const btnBuscar = document.getElementById('btnBuscarReservacion');
        if (!btnBuscar) {
            console.error("❌ Botón BUSCAR no encontrado");
            return;
        }

        const nuevoBtn = btnBuscar.cloneNode(true);
        btnBuscar.parentNode.replaceChild(nuevoBtn, btnBuscar);

        nuevoBtn.addEventListener('click', async (e) => {
            e.preventDefault();
            e.stopPropagation();

            console.log("🔍 Buscar clickeado");

            if (!validarFechasHorasLocal()) {
                console.log("❌ Validación fallida");
                return;
            }

            console.log("✅ Validación exitosa");

            expandirSeccion('categoria');

            setTimeout(() => {
                const btnCategorias = document.getElementById('btnCategorias');
                if (btnCategorias) {
                    console.log("📦 Abriendo modal de categorías");
                    btnCategorias.click();
                } else {
                    console.error("❌ Botón de categorías no encontrado");
                }
            }, 300);
        });
    }

    function initSeleccionCategoria() {
        const seleccionarCategoriaOriginal = window.seleccionarCategoriaReservacion;

        window.seleccionarCategoriaReservacion = function(element) {
            console.log("🚗 Seleccionando categoría...");

            if (seleccionarCategoriaOriginal && typeof seleccionarCategoriaOriginal === 'function') {
                seleccionarCategoriaOriginal(element);
            }

            setTimeout(() => {
                const categoriaSeleccionada = window.state?.categoria || state?.categoria;

                if (categoriaSeleccionada) {
                    console.log("✅ Categoría seleccionada:", categoriaSeleccionada.nombre);
                    marcarCompletada('categoria');

                    console.log("📂 Intentando expandir 'adicionales'...");
                    expandirSeccion('adicionales');

                    setTimeout(() => {
                        const seccionAdicionales = document.querySelector('.acordeon-item[data-seccion="adicionales"]');
                        const bodyExpandido = seccionAdicionales?.querySelector('.stack-body.expanded');
                        console.log("📂 Sección adicionales expandida?", !!bodyExpandido);
                    }, 100);

                    if (typeof mostrarToast === 'function') {
                        mostrarToast(`✅ Categoría ${categoriaSeleccionada.nombre} seleccionada`, 'success');
                    }
                } else {
                    console.log("❌ No se encontró categoría en state");
                }
            }, 200);
        };
    }

    function tieneAdicionalesSeleccionados() {
        const dropoffActivo = document.getElementById('dropoffToggle')?.checked || false;
        const deliveryActivo = document.getElementById('deliveryToggle')?.checked || false;
        const gasolinaActivo = document.getElementById('gasolinaToggle')?.checked || false;

        let sillaActivo = false;
        const sillaToggle = document.querySelector('.addon-toggle[data-addon="silla_bebe"]');
        if (sillaToggle && sillaToggle.checked) {
            sillaActivo = true;
        } else if (state.addons.has('silla_bebe') && state.addons.get('silla_bebe').qty > 0) {
            sillaActivo = true;
        }

        let conductorActivo = false;
        const conductorToggle = document.querySelector('.addon-toggle[data-addon="conductor_extra"]');
        if (conductorToggle && conductorToggle.checked) {
            conductorActivo = true;
        } else if (state.addons.has('conductor_extra') && state.addons.get('conductor_extra').qty > 0) {
            conductorActivo = true;
        }

        const resultado = dropoffActivo || deliveryActivo || gasolinaActivo || sillaActivo || conductorActivo;
        console.log(`🔍 Adicionales seleccionados: DropOff=${dropoffActivo}, Delivery=${deliveryActivo}, Gasolina=${gasolinaActivo}, Silla=${sillaActivo}, Conductor=${conductorActivo} → ${resultado}`);

        return resultado;
    }

    function tieneProteccionesSeleccionadas() {
        const proteccionId = document.getElementById('proteccion_id')?.value;
        if (proteccionId && proteccionId !== '') return true;

        const individualesInputs = document.querySelectorAll('#insHidden input[name*="[id]"]');
        return individualesInputs.length > 0;
    }

    function initCarousel() {
        const container = document.querySelector(".carousel-container");
        const btnPrev = document.querySelector(".carousel-arrow.prev");
        const btnNext = document.querySelector(".carousel-arrow.next");

        if (!container || !btnPrev || !btnNext) {
            console.log("❌ Carrusel no encontrado");
            return;
        }

        const card = document.querySelector(".carousel-item");
        const scrollAmount = card ? card.offsetWidth + 20 : 320;

        btnNext.addEventListener("click", () => {
            container.scrollBy({
                left: scrollAmount,
                behavior: "smooth"
            });
        });

        btnPrev.addEventListener("click", () => {
            container.scrollBy({
                left: -scrollAmount,
                behavior: "smooth"
            });
        });

        console.log("✅ Carrusel listo");
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', () => {
            initAcordeon();
            initBotonBuscar();
            initSeleccionCategoria();
            initCarousel();
        });
    } else {
        initAcordeon();
        initBotonBuscar();
        initSeleccionCategoria();
        initCarousel();
    }

})();
