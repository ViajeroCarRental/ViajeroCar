(function() {
    "use strict";

    /* =========================================
    01. FUNCIONES DE VALIDACIÓN
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
        } else if (elemento.id === 'fecha_inicio_ui' || elemento.id === 'fecha_fin_ui') {
            elemento.classList.add('field-error');
            if (elemento._flatpickr && elemento._flatpickr.altInput) {
                elemento._flatpickr.altInput.classList.add('field-error');
            }
        } else if (elemento.id === 'hora_retiro_ui' || elemento.id === 'hora_entrega_ui') {
            elemento.classList.add('field-error');
            const container = elemento.closest('.dt-field-admin');
            if (container) {
                const selectHora = container.querySelector('.tp-hour');
                if (selectHora) {
                    selectHora.classList.add('field-error');
                }
            }
        } else if (elemento.classList && elemento.classList.contains('tp-hour')) {
            elemento.classList.add('field-error');
            const container = elemento.closest('.dt-field-admin');
            if (container) {
                const inputUI = container.querySelector('.input-buscador-admin');
                if (inputUI) {
                    inputUI.classList.add('field-error');
                }
            }
        } else if (elemento.tagName === 'BUTTON') {
            elemento.classList.add('field-error');
            elemento.style.border = '2px solid #e53935';
            elemento.style.backgroundColor = '#fee2e2';
        } else {
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
        } else if (elemento.id === 'hora_retiro_ui' || elemento.id === 'hora_entrega_ui') {
            elemento.classList.add('field-success');
            const container = elemento.closest('.dt-field-admin');
            if (container) {
                const selectHora = container.querySelector('.tp-hour');
                if (selectHora) {
                    selectHora.classList.add('field-success');
                }
            }
        } else if (elemento.classList && elemento.classList.contains('tp-hour')) {
            elemento.classList.add('field-success');
            const container = elemento.closest('.dt-field-admin');
            if (container) {
                const inputUI = container.querySelector('.input-buscador-admin');
                if (inputUI) {
                    inputUI.classList.add('field-success');
                }
            }
        } else if (elemento.id === 'nombre_cliente' ||
                   elemento.id === 'apellidos_cliente' ||
                   elemento.id === 'email_cliente' ||
                   elemento.id === 'telefono_ui' ||
                   elemento.id === 'no_vuelo') {
            elemento.classList.add('field-success');
        } else {
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
        } else if (elemento.id === 'fecha_inicio_ui' || elemento.id === 'fecha_fin_ui') {
            elemento.classList.remove('field-error', 'field-success');
            if (elemento._flatpickr && elemento._flatpickr.altInput) {
                elemento._flatpickr.altInput.classList.remove('field-error', 'field-success');
            }
        } else if (elemento.id === 'hora_retiro_ui' || elemento.id === 'hora_entrega_ui') {
            elemento.classList.remove('field-error', 'field-success');
            const container = elemento.closest('.dt-field-admin');
            if (container) {
                const selectHora = container.querySelector('.tp-hour');
                if (selectHora) {
                    selectHora.classList.remove('field-error', 'field-success');
                }
            }
        } else if (elemento.classList && elemento.classList.contains('tp-hour')) {
            elemento.classList.remove('field-error', 'field-success');
            const container = elemento.closest('.dt-field-admin');
            if (container) {
                const inputUI = container.querySelector('.input-buscador-admin');
                if (inputUI) {
                    inputUI.classList.remove('field-error', 'field-success');
                }
            }
        } else {
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

    function validarNombreCompleto() {
        const nombreCompletoInput = document.getElementById("nombre_cliente");
        if (!nombreCompletoInput) return true;

        const valor = nombreCompletoInput.value.trim();
        let todoOk = true;

        if (valor === "") {
            mostrarError(nombreCompletoInput, 'El nombre completo es obligatorio');
            todoOk = false;
        } else if (valor.split(/\s+/).length < 2) {
            mostrarError(nombreCompletoInput, 'Ingresa tu nombre y apellido(s) completos');
            todoOk = false;
        } else {
            mostrarExito(nombreCompletoInput);
        }
        return todoOk;
    }

    function validarClienteVisual() {
        let todoOk = true;

        if (!validarNombreCompleto()) {
            todoOk = false;
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

    function mostrarToast(mensaje, tipo = 'warning') {
        console.log(`🔇 Toast silenciado: [${tipo}] ${mensaje}`);
        return;
    }

    /* =========================================
    02. BLOQUEO DE ACORDEONES
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

    function abrirSeccion(seccion, evitarScroll = false) {
        if (!seccion) return;
        const body = seccion.querySelector('.stack-body');
        const indicator = seccion.querySelector('.stack-indicator');
        if (body && !body.classList.contains('expanded')) {
            body.classList.add('expanded');
        }
        if (indicator && !indicator.classList.contains('expanded')) {
            indicator.classList.add('expanded');
        }

        const seccionId = seccion.getAttribute('data-seccion');
        if (!evitarScroll && seccionId !== 'cliente') {
            setTimeout(() => {
                seccion.scrollIntoView({ behavior: 'smooth', block: 'start' });
            }, 100);
        }
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

        const body = seccionCliente?.querySelector('.stack-body');
        const indicator = seccionCliente?.querySelector('.stack-indicator');
        if (body && !body.classList.contains('expanded')) {
            body.classList.add('expanded');
        }
        if (indicator && !indicator.classList.contains('expanded')) {
            indicator.classList.add('expanded');
        }

        console.log('📂 Sección cliente expandida (sin scroll automático)');
    }

    function desbloquearProteccionesSinExpandir() {
        if (proteccionesDesbloqueada) return;
        proteccionesDesbloqueada = true;
        actualizarTodasSecciones();
    }

    function desbloquearClienteSinExpandir() {
        if (clienteDesbloqueada) return;
        clienteDesbloqueada = true;
        actualizarTodasSecciones();
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
    03. EVENTO PRINCIPAL DEL BOTÓN
    ========================================= */
function configurarBotonPrincipal() {
    const btn = document.getElementById('btnBuscarReservacion');
    if (!btn) {
        console.error('❌ Botón btnBuscarReservacion no encontrado');
        return;
    }

    // Ocultar navbar en móvil al iniciar
    const navbar = document.getElementById('resNavbar');
    if (navbar && window.innerWidth <= 860) {
        navbar.classList.add('hidden-mobile');
    }

    const newBtn = btn.cloneNode(true);
    btn.parentNode.replaceChild(newBtn, btn);

    newBtn.addEventListener('click', function(e) {
        e.preventDefault();
        e.stopPropagation();

        console.log('🔍 Validando campos de ubicación...');

        const esValido = validarCamposUbicacion();

        if (esValido) {
            console.log('✅ Validación exitosa');

             document.body.classList.add('buscar-realizado');

            // Mostrar navbar en móvil
            if (navbar && navbar.classList.contains('hidden-mobile')) {
                navbar.classList.remove('hidden-mobile');
            }

            if (window.innerWidth <= 860) {
                const seccionCategoria = document.querySelector('.acordeon-item[data-seccion="categoria"]');
                if (seccionCategoria && !seccionCategoria.classList.contains('unlocked')) {
                    seccionCategoria.classList.add('unlocked');
                    console.log('📱 Sección de categoría visible en móvil');
                }
            }

            // Abrir modal de categorías
            const modalCategorias = document.getElementById('catPop');
            if (modalCategorias) {
                modalCategorias.style.display = 'flex';
            }

            if (typeof desbloquearCategoria === 'function') {
                desbloquearCategoria();
            }
        } else {
            console.log('❌ Validación fallida - Corrige los campos en rojo');
        }
    });
}
    /* =========================================
    04. OBSERVADORES Y CONFIGURACIÓN
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

                setTimeout(() => {
                    if (typeof desbloquearProteccionesSinExpandir === 'function') {
                        desbloquearProteccionesSinExpandir();
                        console.log("🔓 Protecciones desbloqueadas (sin expandir)");
                    }
                    if (typeof desbloquearClienteSinExpandir === 'function') {
                        desbloquearClienteSinExpandir();
                        console.log("🔓 Cliente desbloqueado (sin expandir)");
                    }
                }, 150);
            }
        }, 500);
    }

    function observarClienteCompleto() {
        setInterval(() => {
            const nombreCompleto = document.getElementById('nombre_cliente')?.value?.trim();
            const email = document.getElementById('email_cliente')?.value?.trim();
            const telefono = document.getElementById('telefono_ui')?.value?.trim();
            const clienteCompleto = nombreCompleto && nombreCompleto.includes(' ') && email && telefono;

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
function init() {
        console.log('🚀 Sistema unificado iniciado...');
        initSelect2Sucursales();
         initEditBaseTotal();
        /* =========================================================================
           NOTA: La lógica de sincronización DropOff ↔ sucursal_entrega y la
           exclusión de sucursales de Querétaro se maneja de forma centralizada
           en la sección 41 (SINCRONIZACIÓN BIDIRECCIONAL). Bloque duplicado
           eliminado para evitar bucles de eventos.
        ========================================================================= */
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

    /* =========================================
    05. HELPERS Y UTILIDADES
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

    // Convierte **texto** en <strong>texto</strong>.
    const aplicarNegritas = (str) => {
        return String(str || "").replace(/\*\*(.+?)\*\*/g, "<strong>$1</strong>");
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
    06. ESTADO GLOBAL
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
            activo: false,
            restaurado: false
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
    07. HIDDEN INPUTS (BACKEND)
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

            // DINÁMICO: it.id ya es el id_servicio real (número) de la card.
            // Ya no hace falta mapear silla_bebe→7 ni conductor_extra→4.
            const idServicio = it.id;

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
    08. SERVICIOS (SWITCHES)
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
    09. DELIVERY (Switch + Campos + Total)
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

    function setDeliveryActive(on) {
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

        setDeliveryActive(activoServer);

        els.toggle?.addEventListener("change", () => {
            setDeliveryActive(!!els.toggle.checked);
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
    10. DROPOFF
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
        };
    }

    function syncDropoffGroups(els) {
        if (!els) return;
        const val = String(els.ubicacion?.value || "");
        const isManual = (val === "0");

        if (els.groupDir) els.groupDir.style.display = isManual ? "block" : "none";
        if (els.groupKm) els.groupKm.style.display = isManual ? "block" : "none";
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

        // MODO EDICIÓN: si el dropoff se restauró con un total guardado y todavía
        // no se eligió una ubicación (val vacío), respetar ese total en vez de 0.
        if (state.dropoff.restaurado && val === "") {
            const totalRestaurado = parseFloat(state.dropoff.total) || 0;
            if (els.totalTxt) els.totalTxt.textContent = money(totalRestaurado);
            qs("#dropoff_activo").value = state.servicios.dropoff ? "1" : "0";
            qs("#dropoff_total").value = totalRestaurado.toFixed(2);
            syncTotalsHidden();
            refreshSummary();
            return totalRestaurado;
        }

        const total = km * precioKm;

        state.dropoff.km = km;
        state.dropoff.total = total;
        state.dropoff.ubicacion = val;
        state.dropoff.direccion = (val === "0") ? String(els.dir?.value || "") : "";

        if (els.totalTxt) els.totalTxt.textContent = money(total);

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

        function syncDropoffLocationFromForm() {
            if (!state.servicios.dropoff) return;

            const differentDropoffCheckbox = document.getElementById('differentDropoffAdmin');
            const isDifferent = differentDropoffCheckbox && differentDropoffCheckbox.checked;

            let selectedBranchText = null;

            if (isDifferent) {
                const entregaSelect = document.getElementById('sucursal_entrega');
                if (entregaSelect && entregaSelect.selectedIndex >= 0) {
                    selectedBranchText = entregaSelect.options[entregaSelect.selectedIndex]?.text?.trim() || "";
                }
            } else {
                const retiroSelect = document.getElementById('sucursal_retiro');
                if (retiroSelect && retiroSelect.selectedIndex >= 0) {
                    selectedBranchText = retiroSelect.options[retiroSelect.selectedIndex]?.text?.trim() || "";
                }
            }

            if (!selectedBranchText) return;

            const dropoffSelect = document.getElementById('dropUbicacion');
            if (!dropoffSelect) return;

            let matchedOption = null;
            for (let i = 0; i < dropoffSelect.options.length; i++) {
                const option = dropoffSelect.options[i];
                const optionText = option.text?.trim() || "";
                if (optionText.toLowerCase().includes(selectedBranchText.toLowerCase()) ||
                    selectedBranchText.toLowerCase().includes(optionText.toLowerCase())) {
                    matchedOption = option;
                    break;
                }
            }

            if (matchedOption && dropoffSelect.value !== matchedOption.value) {
                dropoffSelect.value = matchedOption.value;
                const changeEvent = new Event('change', { bubbles: true });
                dropoffSelect.dispatchEvent(changeEvent);

                setTimeout(() => {
                    if (state.servicios.dropoff) {
                        computeDropoff(els);
                        syncTotalsHidden();
                        refreshSummary();
                    }
                }, 100);
            }
        }

        if (els.toggle) {
            const newToggle = els.toggle.cloneNode(true);
            els.toggle.parentNode.replaceChild(newToggle, els.toggle);
            els.toggle = newToggle;

            els.toggle.addEventListener("change", () => {
                const isActive = !!els.toggle.checked;

                if (isActive) {
                    syncDropoffLocationFromForm();
                    if (els.fields) els.fields.style.display = "block";
                    syncDropoffGroups(els);
                    computeDropoff(els);
                    state.servicios.dropoff = true;
                    state.dropoff.activo = true;
                } else {
                    if (els.fields) els.fields.style.display = "none";
                    state.servicios.dropoff = false;
                    state.dropoff.activo = false;
                    state.dropoff.total = 0;
                    state.dropoff.km = 0;
                    state.dropoff.ubicacion = "";
                    state.dropoff.direccion = "";
                    if (els.totalTxt) els.totalTxt.textContent = money(0);
                }

                syncServiciosHidden();
                syncDropoffHidden();
                syncTotalsHidden();
                refreshSummary();
            });
        }

        if (els.ubicacion) {
            const newUbicacion = els.ubicacion.cloneNode(true);
            els.ubicacion.parentNode.replaceChild(newUbicacion, els.ubicacion);
            els.ubicacion = newUbicacion;

            els.ubicacion.addEventListener("change", () => {
                // El usuario eligió una ubicación: dejar de respetar el total
                // restaurado y volver a calcular normal por km.
                state.dropoff.restaurado = false;
                syncDropoffGroups(els);
                if (state.servicios.dropoff) {
                    computeDropoff(els);
                    syncTotalsHidden();
                    refreshSummary();
                }
            });
        }

        if (els.km) {
            const newKm = els.km.cloneNode(true);
            els.km.parentNode.replaceChild(newKm, els.km);
            els.km = newKm;

            els.km.addEventListener("input", () => {
                if (state.servicios.dropoff) {
                    computeDropoff(els);
                    syncTotalsHidden();
                    refreshSummary();
                }
            });
        }

        if (els.dir) {
            const newDir = els.dir.cloneNode(true);
            els.dir.parentNode.replaceChild(newDir, els.dir);
            els.dir = newDir;

            els.dir.addEventListener("input", () => {
                state.dropoff.direccion = String(els.dir.value || "");
                const hid = qs("#dropoff_direccion");
                if (hid) hid.value = state.dropoff.direccion;
            });
        }
    }

    /* =========================================
    11. GASOLINA
    ========================================= */
    function getGasolinaEls() {
        // DINÁMICO (Opción 1): la card de Gasolina ahora sale del @foreach
        // como card de tanque. Buscamos sus elementos por clase relativa a
        // la card .svc-card--tanque, en vez de por IDs fijos.
        // El cálculo (computeGasolina) NO cambia; solo cambia de dónde lee.
        const card = document.querySelector('.svc-card--tanque[data-tanque="1"]');
        if (!card) {
            return { toggle: null, fields: null, totalTxt: null, totalHid: null, litrosLabel: null, card: null };
        }
        return {
            toggle: card.querySelector('.addon-toggle-tanque'),
            fields: card.querySelector('.svc-tanque-fields'),
            totalTxt: card.querySelector('.tanque-total'),
            totalHid: card.querySelector('.addon-qty-hidden'),
            litrosLabel: card.querySelector('.tanque-litros-label'),
            card: card,
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

        const label = els.litrosLabel;
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
    }

    function bindGasolinaUI() {
        const toggle = qs("#gasolinaToggle");
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
    12. FECHAS/HORAS: UI + HIDDEN
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
    13. DÍAS
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

        let dias = Math.ceil((d2 - d1) / (1000 * 60 * 60 * 24));
        if (!Number.isFinite(dias)) dias = 0;

        const horaRetiro = parseInt(qs("#hora_retiro")?.value?.split(":")[0] ?? "", 10);
        const horaEntrega = parseInt(qs("#hora_entrega")?.value?.split(":")[0] ?? "", 10);

        if (!Number.isNaN(horaRetiro) && !Number.isNaN(horaEntrega)) {
            if (horaEntrega > horaRetiro + 1) {
                dias += 1;
            }
        }

        return Math.max(1, dias);
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

        const diasTxtNav = document.getElementById('diasTxtNav');
        if (diasTxtNav) diasTxtNav.textContent = String(state.days || 0);

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

        actualizarTotalNavbar();
    }

    /* =========================================
    14. AEROPUERTO (No. vuelo)
    ========================================= */
    function normalizarTexto(txt) {
        return String(txt || "")
            .toLowerCase()
            .normalize("NFD")
            .replace(/[\u0300-\u036f]/g, "")
            .trim();
    }

    function getTextoSucursalRetiro() {
        const sel = document.getElementById("sucursal_retiro");
        if (!sel) return "";

        if (typeof $ !== "undefined" && $(sel).data("select2")) {
            const data = $(sel).select2("data");
            if (data && data.length) {
                return data[0].text || "";
            }
        }

        return sel.options[sel.selectedIndex]?.textContent || "";
    }

    function isAirportSelected() {
        const texto = normalizarTexto(getTextoSucursalRetiro());
        return texto.includes("aeropuerto");
    }

    function syncVueloField() {
        const wrap = document.getElementById("vueloWrap");
        const vuelo = document.getElementById("no_vuelo");

        if (!wrap || !vuelo) return;

        const show = isAirportSelected();

        wrap.style.setProperty("display", show ? "flex" : "none", "important");

        if (show) {
            vuelo.setAttribute("required", "required");
        } else {
            vuelo.removeAttribute("required");
            vuelo.value = "";

            if (typeof limpiarError === "function") {
                limpiarError(vuelo);
            }
        }
    }

    /* =========================================
    15. CATEGORÍA
    ========================================= */
    function setCategoria(cat) {
        state.categoria = cat;

        const hid = qs("#categoria_id");
        if (hid) hid.value = cat ? String(cat.id) : "";

        const container = qs("#categoriaSelectedContainer");
        const miniPreview = qs("#catMiniPreview");

        if (!cat) {
            if (container) container.style.display = "none";
            if (miniPreview) miniPreview.style.display = "none";

            const inputPrecioKm = qs("#deliveryPrecioKm");
            if (inputPrecioKm) inputPrecioKm.value = "0";

            syncTotalsHidden();
            refreshSummary();
            return;
        }

        if (container) {
            container.style.display = "block";
            if (miniPreview && miniPreview.parentNode !== container) {
                container.innerHTML = '';
                container.appendChild(miniPreview);
            }
            if (miniPreview) miniPreview.style.display = "block";
        }

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
            const container = qs("#categoriaSelectedContainer");
            if (container) container.style.display = "none";
            return;
        }

        mini.style.display = "block";

        const container = qs("#categoriaSelectedContainer");
        if (container) container.style.display = "block";

        const imgEl = document.getElementById("catMiniImg");
        if (imgEl && cat.img) {
            imgEl.src = cat.img;
            imgEl.alt = cat.nombre || "Auto";
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
    16. PROTECCIONES (PAQUETE)
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
    17. INDIVIDUALES
    ========================================= */
    function getGrupoLabelFromTrack(trackId) {
        const map = {
            "insColisionTrack": "Colisión y robo",
            "insMedicosTrack": "Gastos médicos",
            "insCaminoTrack": "Asistencia para el camino",
            "insTercerosTrack": "Daños a terceros",
            "insAutoTrack": "Protecciones automáticas",
        };
        return map[trackId] || "";
    }

    function toggleIndividualFromCard(card) {
        if (!card) return;
        if (state.proteccion) setProteccion(null);

        const id = String(card.dataset.id || "");
        const precio = Number(card.dataset.precio || 0);
        const nombre = card.querySelector("h4")?.textContent?.trim() || "Seguro individual";
        const desc = card.querySelector(".tooltip-text")?.textContent?.trim() || "";
        let parentTrack = card.closest(".grid-vertical-individuales")?.id || "";
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

        const yaSeleccionado = state.individuales.has(id);

        let otroSeleccionadoId = null;
        for (const [existingId, item] of state.individuales.entries()) {
            if (item.grupo === grupo && existingId !== id) {
                otroSeleccionadoId = existingId;
                break;
            }
        }

        if (yaSeleccionado) {
            state.individuales.delete(id);
        } else if (otroSeleccionadoId !== null) {
            state.individuales.delete(otroSeleccionadoId);
            state.individuales.set(id, { id, nombre, desc, precio, charge: "por_dia", grupo });
        } else {
            state.individuales.set(id, { id, nombre, desc, precio, charge: "por_dia", grupo });
        }

        function obtenerIdsProteccionesAutomaticas() {
            const autoTrack = document.getElementById("insAutoTrack");
            if (!autoTrack) return [];
            const autoCards = autoTrack.querySelectorAll(".individual-item");
            const ids = [];
            autoCards.forEach(card => {
                const cardId = card.dataset.id;
                if (cardId) ids.push(String(cardId));
            });
            return ids;
        }

        function contarSeleccionesActivas() {
            let count = 0;
            for (const item of state.individuales.values()) {
                if (item.grupo !== "Protecciones automáticas") {
                    count++;
                }
            }
            return count;
        }

        const autoIds = obtenerIdsProteccionesAutomaticas();
        const totalActivas = contarSeleccionesActivas();

        if (totalActivas > 0) {
            autoIds.forEach(autoId => {
                if (!state.individuales.has(autoId)) {
                    const autoCard = document.querySelector(`.individual-item[data-id="${autoId}"]`);
                    if (autoCard) {
                        const autoNombre = autoCard.querySelector("h4")?.textContent?.trim() || "Protección Auto";
                        const autoPrecio = Number(autoCard.dataset.precio || 0);
                        const autoDesc = autoCard.dataset.descripcion || "";

                        state.individuales.set(autoId, {
                            id: autoId,
                            nombre: autoNombre,
                            desc: autoDesc,
                            precio: autoPrecio,
                            charge: "por_dia",
                            grupo: "Protecciones automáticas"
                        });

                        autoCard.classList.add("is-selected");
                        const autoSwitch = autoCard.querySelector(".switch-individual");
                        if (autoSwitch) autoSwitch.classList.add("is-on");
                    }
                }
            });
        } else {
            autoIds.forEach(autoId => {
                if (state.individuales.has(autoId)) {
                    state.individuales.delete(autoId);

                    const autoCard = document.querySelector(`.individual-item[data-id="${autoId}"]`);
                    if (autoCard) {
                        autoCard.classList.remove("is-selected");
                        const autoSwitch = autoCard.querySelector(".switch-individual");
                        if (autoSwitch) autoSwitch.classList.remove("is-on");
                    }
                }
            });
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

    function preseleccionarProteccionesIndividuales() {
        console.log("🎯 Preseleccionando protecciones individuales...");

        state.individuales.clear();

        const todasLasTarjetas = document.querySelectorAll('.individual-item');

        let idLIDaniosTerceros = null;
        let datosLIDaniosTerceros = null;
        let idDeclineCDW = null;
        let datosDeclineCDW = null;
        const idsProteccionesAuto = [];

        todasLasTarjetas.forEach(card => {
            const nombre = card.querySelector("h4")?.textContent?.trim() || "";
            const nombreUpper = nombre.toUpperCase();

            let parentTrack = card.closest(".grid-vertical-individuales")?.id || "";
            const grupo = getGrupoLabelFromTrack(parentTrack);

            const id = String(card.dataset.id || "");
            const precio = Number(card.dataset.precio || 0);
            const desc = card.dataset.descripcion || "";

            const esLI = nombreUpper === "LI" ||
                         (nombreUpper.includes("LI") &&
                          !nombreUpper.includes("EXT") &&
                          !nombreUpper.includes("ALI"));

            if (esLI && grupo === "Daños a terceros") {
                idLIDaniosTerceros = id;
                datosLIDaniosTerceros = { id, nombre, desc, precio, grupo };
                console.log(`🎯 LI (Daños a terceros) identificado para preselección: ${nombre}`);
            }

            const esDeclineCDW = (nombreUpper.includes("DECLINE") || nombreUpper.includes("RECHAZAR")) &&
                                  grupo === "Colisión y robo";

            if (esDeclineCDW) {
                idDeclineCDW = id;
                datosDeclineCDW = { id, nombre, desc, precio, grupo };
                console.log(`🎯 DECLINE CDW identificado para preselección: ${nombre}`);
            }

            if (grupo === "Protecciones automáticas") {
                idsProteccionesAuto.push({ id, nombre, desc, precio, grupo });
                console.log(`🛡️ Protección automática: ${nombre}`);
            }
        });

        if (idLIDaniosTerceros && datosLIDaniosTerceros) {
            state.individuales.set(idLIDaniosTerceros, {
                id: datosLIDaniosTerceros.id,
                nombre: datosLIDaniosTerceros.nombre,
                desc: datosLIDaniosTerceros.desc,
                precio: datosLIDaniosTerceros.precio,
                charge: "por_dia",
                grupo: "Daños a terceros"
            });
            console.log(`✅ LI (Daños a terceros) preseleccionado por defecto: ${datosLIDaniosTerceros.nombre}`);
        }

        if (idDeclineCDW && datosDeclineCDW) {
            state.individuales.set(idDeclineCDW, {
                id: datosDeclineCDW.id,
                nombre: datosDeclineCDW.nombre,
                desc: datosDeclineCDW.desc,
                precio: datosDeclineCDW.precio,
                charge: "por_dia",
                grupo: "Colisión y robo"
            });
            console.log(`✅ DECLINE CDW preseleccionado por defecto: ${datosDeclineCDW.nombre}`);
        }

        idsProteccionesAuto.forEach(auto => {
            if (!state.individuales.has(auto.id)) {
                state.individuales.set(auto.id, {
                    id: auto.id,
                    nombre: auto.nombre,
                    desc: auto.desc,
                    precio: auto.precio,
                    charge: "por_dia",
                    grupo: "Protecciones automáticas"
                });
                console.log(`✅ Protección automática seleccionada: ${auto.nombre}`);
            }
        });

        repaintIndividualesUI();
        refreshProteccionUIHeader();
        syncIndividualesHidden();
        syncTotalsHidden();
        refreshSummary();

        console.log("🎯 Preselección completada. Total en state.individuales:", state.individuales.size);
    }

    /* =========================================
    18. ADDONS
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
        // DINÁMICO: la config se lee de los data-* de la card en el DOM.
        // addonId aquí es el id_servicio real (número), que es el data-addon
        // del toggle y el data-id-servicio de la card. Ya no hay mapeos fijos.
        const card = document.querySelector(`.svc-card--servicio[data-id-servicio="${addonId}"]`);
        if (!card) return null;

        // Las cards de tanque (Gasolina) NO usan este motor de cantidad;
        // tienen su propio cálculo especial (computeGasolina).
        if (card.dataset.tanque === '1') return null;

        const expanded = card.querySelector('.svc-addon-expanded-dyn');

        return {
            id: addonId,
            name: card.dataset.name || '',
            price: Number(card.dataset.price || 0),
            charge: card.dataset.charge || 'por_evento',
            maxQty: 3,
            defaultQty: 1,
            card: card,
            toggleSelector: `.addon-toggle[data-addon="${addonId}"]`,
            expanded: expanded,
            qtyEl: expanded ? expanded.querySelector('.qty-value') : null,
            totalEl: expanded ? expanded.querySelector('.addon-total') : null,
            hiddenEl: card.querySelector('.addon-qty-hidden'),
        };
    }

    function getCurrentDays() {
        const days = Number(state.days || 0);
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

        if (config.totalEl) {
            config.totalEl.textContent = money(total);
        }

        if (config.hiddenEl) {
            config.hiddenEl.value = qty;
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
    }

    function handleAddonQtyChange(addonId, change) {
        const config = getAddonConfig(addonId);
        if (!config) return;

        let currentQty = 0;

        const stateAddon = state.addons.get(String(addonId));
        if (stateAddon && stateAddon.qty) {
            currentQty = Number(stateAddon.qty);
        } else if (config.qtyEl) {
            if (config.qtyEl.dataset.qty) {
                currentQty = Number(config.qtyEl.dataset.qty);
            } else {
                currentQty = Number(config.qtyEl.textContent);
            }
        }

        let newQty = currentQty + change;

        if (newQty < 0) newQty = 0;
        if (newQty > config.maxQty) newQty = config.maxQty;

        if (newQty === 0) {
            const toggle = document.querySelector(config.toggleSelector);
            if (toggle) {
                toggle.checked = false;
                const event = new Event('change', { bubbles: true });
                toggle.dispatchEvent(event);
            }
            if (config.expanded) {
                config.expanded.style.display = 'none';
            }
        }

        if (config.qtyEl) {
            config.qtyEl.textContent = newQty;
            config.qtyEl.dataset.qty = newQty;
        }

        updateAddonTotal(addonId, newQty);
    }

    function setAddonActive(addonId, active) {
        const config = getAddonConfig(addonId);
        if (!config) return;

        const toggle = document.querySelector(config.toggleSelector);

        if (config.expanded) {
            config.expanded.style.display = active ? 'block' : 'none';
        }

        if (toggle) {
            toggle.checked = active;
        }

        if (active) {
            if (config.qtyEl) {
                config.qtyEl.textContent = config.defaultQty;
                config.qtyEl.dataset.qty = config.defaultQty;
            }
            updateAddonTotal(addonId, config.defaultQty);
        } else {
            updateAddonTotal(addonId, 0);
        }
    }

    function initAddonsWithSwitch() {
        // DINÁMICO: recorremos TODAS las cards de servicio con cantidad
        // (excluye tanque/Gasolina, que tiene su propio binder).
        // El addonId es el data-id-servicio (número real).
        const cards = document.querySelectorAll('.svc-card--servicio:not(.svc-card--tanque)');

        cards.forEach(card => {
            const addonId = card.dataset.idServicio;
            if (!addonId) return;

            const config = getAddonConfig(addonId);
            if (!config) return;

            const toggle = card.querySelector('.addon-toggle');
            if (toggle && !toggle.dataset.initialized) {
                toggle.dataset.initialized = 'true';

                toggle.addEventListener('change', (e) => {
                    const isActive = e.target.checked;
                    setAddonActive(addonId, isActive);
                });
            }

            const expanded = config.expanded;
            if (expanded && !expanded.dataset.quantityInitialized) {
                expanded.dataset.quantityInitialized = 'true';

                const minusBtn = expanded.querySelector('.qty-btn.minus');
                const plusBtn = expanded.querySelector('.qty-btn.plus');

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
            }

            const hiddenInput = card.querySelector('.addon-qty-hidden');
            if (hiddenInput && Number(hiddenInput.value) > 0) {
                const savedQty = Number(hiddenInput.value);

                if (toggle && !toggle.checked) {
                    setAddonActive(addonId, true);

                    if (config.qtyEl) {
                        config.qtyEl.textContent = savedQty;
                        config.qtyEl.dataset.qty = savedQty;
                    }
                    updateAddonTotal(addonId, savedQty);
                }
            }
        });
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
    19. TOTALES + HIDDEN
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

function actualizarTotalNavbar() {
    const btnTotal = document.getElementById('btnTotalNav');
    if (!btnTotal) return;

    const totals = calcTotals();
    const totalValido = isNaN(totals.total) ? 0 : totals.total;

    btnTotal.innerHTML = `Total: ${money(totalValido)}`;
}

function syncTotalsHidden() {
    ensureTotalsHidden();

    const totals = calcTotals();
    qs("#precio_base_dia").value = String(totals.baseDia || 0);
    qs("#subtotal").value = String(totals.subtotal || 0);
    qs("#impuestos").value = String(totals.iva || 0);
    qs("#total").value = String(totals.total || 0);

    actualizarTotalNavbar();
}

/* =========================================
    20. RESUMEN (VERSIÓN CORREGIDA)
========================================= */
function refreshSummary() {
    const days = Number(state.days || 0);

    const selR = qs("#sucursal_retiro");
    const selE = qs("#sucursal_entrega");

    const getText = (sel) =>
        sel?.options?.[sel.selectedIndex]?.textContent?.trim() || "—";

    const fi = qs("#fecha_inicio_ui")?.value || "—";
    const hi = qs("#hora_retiro_ui")?.value || "—";
    const ff = qs("#fecha_fin_ui")?.value || "—";
    const hf = qs("#hora_entrega_ui")?.value || "—";

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
        let featuresHTML = '';

        featuresHTML += `<span><i class="fas fa-car"></i> ${tipoTransmision}</span>`;
        featuresHTML += `<span><i class="fas fa-wind"></i> A/C</span>`;
        featuresHTML += `<span><i class="fas fa-users"></i> ${capacidadPasajeros} pasajeros</span>`;
        featuresHTML += `<span><i class="fab fa-apple"></i> CarPlay</span>`;
        featuresHTML += `<span><i class="fab fa-android"></i> Android Auto</span>`;

        featuresContainer.innerHTML = featuresHTML;
    }

        const precioPorDia = totals.baseDia;
        const totalBase = totals.baseTotal;

        const resBaseAmount = document.getElementById("resBaseAmount");
        const resBaseNote = document.getElementById("resBaseNote");
        const resBaseTotalEstilo = document.getElementById("resBaseTotalEstilo");

        if (resBaseAmount) {
            if (!resBaseAmount.querySelector("input")) {
                resBaseAmount.textContent = money(precioPorDia);
            }
        }

        if (resBaseNote && !resBaseNote.querySelector("input")) {
            resBaseNote.innerHTML = `${days} día(s) – precio por día ${money(precioPorDia).replace(" MXN", "")} MXN`;
        }

        if (resBaseTotalEstilo) {
            resBaseTotalEstilo.textContent = money(totalBase);
        }

    setTextV2("resIvaEstilo", money(totals.iva));
    setTextV2("resTotalEstilo", money(totals.total));

    const optionsContainer = document.getElementById("rv2OptionsList");
    const proteccionesContainer = document.getElementById("rv2ProteccionesList");
    const proteccionesSection = document.getElementById("proteccionesSection");

    if (optionsContainer) {
        let optionsHtml = "";

        if (state.servicios.delivery && state.delivery.total > 0) {
            optionsHtml += `<div class="rv2-option-item"><span class="rv2-option-name"><i class="fas fa-truck"></i> Delivery</span><span class="rv2-option-price">${money(state.delivery.total)}</span></div>`;
        }

        if (state.servicios.dropoff && state.dropoff.total > 0) {
            optionsHtml += `<div class="rv2-option-item"><span class="rv2-option-name"><i class="fas fa-flag-checkered"></i> Drop Off</span><span class="rv2-option-price">${money(state.dropoff.total)}</span></div>`;
        }

        if (state.servicios.gasolina && state.gasolina.total > 0) {
            optionsHtml += `<div class="rv2-option-item"><span class="rv2-option-name"><i class="fas fa-gas-pump"></i> Gasolina Prepago</span><span class="rv2-option-price">${money(state.gasolina.total)}</span></div>`;
        }

        if (sillaBebe && sillaBebe.qty > 0) {
            optionsHtml += `<div class="rv2-option-item"><span class="rv2-option-name"><i class="fas fa-baby-carriage"></i> Silla de bebé ×${sillaBebe.qty}</span><span class="rv2-option-price">${money(sillaBebe.total)}</span></div>`;
        }

        if (conductorExtra && conductorExtra.qty > 0) {
            optionsHtml += `<div class="rv2-option-item"><span class="rv2-option-name"><i class="fas fa-user-plus"></i> Conductor adicional ×${conductorExtra.qty}</span><span class="rv2-option-price">${money(conductorExtra.total)}</span></div>`;
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

            if (protTotal >= 0) {
                proteccionesHtml += `<div class="rv2-option-item"><span class="rv2-option-name"><i class="fas fa-shield-alt"></i> ${prot.nombre}</span><span class="rv2-option-price">${money(protTotal)} ${prot.charge === "por_dia" ? "/día" : ""}</span></div>`;
                hasProtecciones = true;
            }
        }

        const individualesList = Array.from(state.individuales.values());

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

                    proteccionesHtml += `<div class="rv2-option-item"><span class="rv2-option-name"><i class="fas ${icono}"></i> ${ind.nombre}</span><span class="rv2-option-price">${money(indTotal)} <span style="font-size: 10px; color: #888;">/día</span></span></div>`;
                    hasProtecciones = true;
                }
            });
        }

        if (hasProtecciones) {
            proteccionesContainer.innerHTML = proteccionesHtml;
            proteccionesSection.style.display = "block";
        } else {
            proteccionesSection.style.display = "none";
        }
    }
}

/* =========================================
   EDITAR TARIFA BASE EN RESUMEN
========================================= */
function initEditBaseTotal() {
    const btn = document.getElementById("btnEditBase");

    if (!btn) {
        setTimeout(initEditBaseTotal, 500);
        return;
    }

    const container = document.getElementById("resBaseAmount");
    const noteContainer = document.getElementById("resBaseNote");

    if (!container) return;

    const newBtn = btn.cloneNode(true);
    btn.parentNode.replaceChild(newBtn, btn);

    newBtn.addEventListener("click", (e) => {
        e.stopPropagation();
        e.preventDefault();

        if (!state.categoria) {
            mostrarToast('⚠️ Primero debes seleccionar una categoría', 'warning');
            return;
        }

        if (container.querySelector("input")) return;

        const totals = calcTotals();
        const precioPorDiaActual = state.base_editable !== null
            ? state.base_editable / (state.days || 1)
            : totals.baseDia;

        const originalAmountText = container.textContent;
        const originalNoteText = noteContainer ? noteContainer.innerHTML : "";

        const input = document.createElement("input");
        input.type = "number";
        input.value = precioPorDiaActual.toFixed(2);
        input.min = 0;
        input.step = 0.01;

        Object.assign(input.style, {
            width: "120px",
            padding: "6px 10px",
            border: "2px solid #2563eb",
            borderRadius: "8px",
            fontWeight: "700",
            fontSize: "18px",
            color: "#1e293b",
            outline: "none",
            textAlign: "center",
            backgroundColor: "#ffffff"
        });

        container.innerHTML = "";
        container.appendChild(input);
        input.focus();
        input.select();

        const guardar = () => {
            let nuevoPrecioPorDia = parseFloat(input.value);

            if (isNaN(nuevoPrecioPorDia) || nuevoPrecioPorDia < 0) {
                nuevoPrecioPorDia = precioPorDiaActual;
            }

            const days = Number(state.days || 1);
            const nuevoTotal = nuevoPrecioPorDia * days;

            state.base_editable = nuevoTotal;

            if (state.categoria) {
                state.categoria.precio_dia = nuevoPrecioPorDia;
            }

            container.innerHTML = "";
            container.textContent = money(nuevoPrecioPorDia);

            if (noteContainer) {
                noteContainer.innerHTML = `${days} día(s) – precio por día ${money(nuevoPrecioPorDia).replace(" MXN", "")} MXN`;
            }

            const catMiniRate = document.getElementById("catMiniRate");
            if (catMiniRate && !catMiniRate.querySelector("input")) {
                catMiniRate.textContent = `${money(nuevoPrecioPorDia).replace(" MXN", "")} MXN / día`;
            }

            const catMiniCalc = document.getElementById("catMiniCalc");
            if (catMiniCalc) {
                catMiniCalc.textContent = money(nuevoTotal);
            }

            const sub = document.getElementById("catSelSub");
            if (sub && state.categoria) {
                sub.textContent = `${money(nuevoPrecioPorDia)} / día · ${days} día(s)`;
            }

            syncTotalsHidden();
            refreshSummary();

            mostrarToast(`✅ Tarifa actualizada a ${money(nuevoPrecioPorDia)}/día`, 'success');
        };

        const cancelar = () => {
            container.innerHTML = "";
            container.textContent = originalAmountText;
            if (noteContainer) {
                noteContainer.innerHTML = originalNoteText;
            }
        };

        input.addEventListener("blur", guardar);
        input.addEventListener("keydown", (ev) => {
            if (ev.key === "Enter") {
                ev.preventDefault();
                guardar();
            }
            if (ev.key === "Escape") {
                ev.preventDefault();
                cancelar();
            }
        });
    });
}

    /* =========================================
    21. VALIDACIÓN
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

        if (fechaInicio && fechaFin && fechaFin < fechaInicio) {
            mostrarError(fechaFinUI, 'La fecha de devolución debe ser posterior a la fecha de salida');
            allValid = false;
        }

        const horaRetiroUI = document.getElementById("hora_retiro_ui");
        const horaRetiroContainer = horaRetiroUI?.closest('.dt-field-admin');

        if (horaRetiroContainer) {
            const selectHora = horaRetiroContainer.querySelector('.tp-hour');
            if (!selectHora || !selectHora.value) {
                mostrarError(horaRetiroUI, 'Selecciona una hora de retiro');
                allValid = false;
            } else {
                mostrarExito(horaRetiroUI);
            }
        }

        const horaEntregaUI = document.getElementById("hora_entrega_ui");
        const horaEntregaContainer = horaEntregaUI?.closest('.dt-field-admin');

        if (horaEntregaContainer) {
            const selectHora = horaEntregaContainer.querySelector('.tp-hour');
            if (!selectHora || !selectHora.value) {
                mostrarError(horaEntregaUI, 'Selecciona una hora de entrega');
                allValid = false;
            } else {
                mostrarExito(horaEntregaUI);
            }
        }

        const categoria = window.state?.categoria || state?.categoria;
        const catInput = document.getElementById("categoria_id");

        if (!categoria || !catInput?.value) {
            mostrarError(document.getElementById("btnCategorias"), 'Selecciona una categoría de vehículo');
            allValid = false;
        }

        if (!validarNombreCompleto()) {
            allValid = false;
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
                }
            }
        }

        if (!allValid) {
            const totalErrores = document.querySelectorAll('.field-error').length;
            mostrarToast(`⚠️ Faltan ${totalErrores} campos por completar correctamente`, 'warning');
        }

        return allValid;
    }

    /* =========================================
    22. FLATPICKR (CALENDARIO)
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
                if (finInstance) finInstance.set("minDate", "today");
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

    // ========== PROTECCIÓN ANTI-TECLADO (móvil) ==========
    function createProtectedPicker(inputElement, additionalConfig = {}) {
        if (!inputElement) return null;
        let picker;
        try {
            picker = flatpickr(inputElement, {
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
                        instance.calendarContainer.appendChild(makeActions(instance, additionalConfig.labelText || "Fecha"));
                        instance._actionsAdded = true;
                    }
                },
                onClose: () => closeModal(),
                ...additionalConfig,
                onReady(selectedDates, dateStr, instance) {
                    if (instance.altInput) {
                        instance.altInput.setAttribute('readonly', 'readonly');
                        instance.altInput.setAttribute('inputmode', 'none');
                        instance.altInput.style.cursor = 'pointer';

                        instance.altInput.addEventListener('focus', (e) => {
                            e.preventDefault();
                            instance.altInput.blur();
                        });
                        instance.altInput.addEventListener('touchstart', (e) => {
                            e.preventDefault();
                            instance.open();
                        });
                        instance.altInput.addEventListener('mousedown', (e) => {
                            e.preventDefault();
                            instance.open();
                        });
                    }
                }
            });
        } catch (e) {  }
        return picker;
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
        onReady(selectedDates, dateStr, instance) {
            if (instance.altInput) {
                instance.altInput.setAttribute('readonly', 'readonly');
                instance.altInput.setAttribute('inputmode', 'none');
                instance.altInput.style.cursor = 'pointer';

                instance.altInput.addEventListener('focus', (e) => {
                    e.preventDefault();
                    instance.altInput.blur();
                });
                instance.altInput.addEventListener('touchstart', (e) => {
                    e.preventDefault();
                    instance.open();
                });
                instance.altInput.addEventListener('mousedown', (e) => {
                    e.preventDefault();
                    instance.open();
                });
            }
        },
        onChange: (selectedDates) => {
            const d = selectedDates?.[0];
            qs("#fecha_inicio").value = d ? toISODate(d) : "";

            const finInstance = document.getElementById("fecha_fin_ui")._flatpickr;
            if (finInstance) {
                if (d) finInstance.set("minDate", new Date(d));
                else finInstance.set("minDate", "today");
            }
            syncDays();
            filtrarHorasPasadas();
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
        onReady(selectedDates, dateStr, instance) {
            if (instance.altInput) {
                instance.altInput.setAttribute('readonly', 'readonly');
                instance.altInput.setAttribute('inputmode', 'none');
                instance.altInput.style.cursor = 'pointer';

                instance.altInput.addEventListener('focus', (e) => {
                    e.preventDefault();
                    instance.altInput.blur();
                });
                instance.altInput.addEventListener('touchstart', (e) => {
                    e.preventDefault();
                    instance.open();
                });
                instance.altInput.addEventListener('mousedown', (e) => {
                    e.preventDefault();
                    instance.open();
                });
            }
        },
        onChange: (selectedDates) => {
            const d = selectedDates?.[0];
            qs("#fecha_fin").value = d ? toISODate(d) : "";
            syncDays();
        }
    });
}

    // =========================================
    // 23. SELECCIONADORES DE HORA Y VALIDACIONES
    // =========================================

function initTimeSelectors() {
    function createTimeSelectsBelow(input, hiddenInput, placeholderText) {
        const wrap = input.closest(".time-field") || input.parentElement;
        if (!wrap || wrap.querySelector(".tp-selects")) return;

        const box = document.createElement("div");
        box.className = "tp-selects";

        const selH = document.createElement("select");
        selH.className = "tp-hour";
        selH.innerHTML = '<option value="" disabled selected>' + placeholderText + '</option>';
        for (let h = 0; h < 24; h++) {
            const opt = document.createElement("option");
            opt.value = String(h).padStart(2, "0");
            opt.textContent = `${String(h).padStart(2, "0")}:00`;
            selH.appendChild(opt);
        }

        box.appendChild(selH);
        wrap.appendChild(box);

        if (hiddenInput && hiddenInput.value) {
            const existingHour = hiddenInput.value.split(":")[0];
            if (existingHour && Array.from(selH.options).some(opt => opt.value === existingHour)) {
                selH.value = existingHour;
                input.value = hiddenInput.value;
            }
        }

        selH.addEventListener("change", () => {
            if (!selH.value) {
                if (hiddenInput) hiddenInput.value = "";
                input.value = "";
            } else {
                const timeValue = `${String(selH.value).padStart(2, "0")}:00`;
                if (hiddenInput) hiddenInput.value = timeValue;
                input.value = timeValue;
            }
            syncDays();
        });
    }

    const horaRetiroInput = document.getElementById("hora_retiro_ui");
    const horaRetiroHidden = document.getElementById("hora_retiro");

    if (horaRetiroInput && !horaRetiroInput.dataset.tpReady) {
        horaRetiroInput.dataset.tpReady = "1";
        horaRetiroInput.setAttribute("readonly", "readonly");
        createTimeSelectsBelow(horaRetiroInput, horaRetiroHidden, "Hora");
    }

    const horaEntregaInput = document.getElementById("hora_entrega_ui");
    const horaEntregaHidden = document.getElementById("hora_entrega");

    if (horaEntregaInput && !horaEntregaInput.dataset.tpReady) {
        horaEntregaInput.dataset.tpReady = "1";
        horaEntregaInput.setAttribute("readonly", "readonly");
        createTimeSelectsBelow(horaEntregaInput, horaEntregaHidden, "Hora");
    }
}

// =========================================
// 23.1 FILTRO DE HORAS PASADAS
// =========================================
function filtrarHorasPasadas() {
    const selRetiro = document.querySelector('#hora_retiro_ui')
        ?.closest('.dt-field-admin, .time-field-admin')
        ?.querySelector('.tp-hour');

    if (!selRetiro) return;

    const fechaInicioVal = (qs("#fecha_inicio")?.value || "").trim();

    Array.from(selRetiro.options).forEach(opt => {
        if (opt.value === "") return;
        opt.disabled = false;
        opt.hidden = false;
    });

    if (!fechaInicioVal) return;

    const hoy = new Date();
    const y = hoy.getFullYear();
    const m = String(hoy.getMonth() + 1).padStart(2, "0");
    const d = String(hoy.getDate()).padStart(2, "0");
    const hoyISO = `${y}-${m}-${d}`;

    if (fechaInicioVal !== hoyISO) return;

    const horaActual = hoy.getHours();

    Array.from(selRetiro.options).forEach(opt => {
        if (opt.value === "") return;
        const horaOpt = parseInt(opt.value, 10);
        if (!Number.isNaN(horaOpt) && horaOpt <= horaActual) {
            opt.hidden = true;
            opt.disabled = true;
        }
    });

    const seleccionada = selRetiro.options[selRetiro.selectedIndex];
    if (seleccionada && (seleccionada.hidden || seleccionada.disabled)) {
        selRetiro.value = "";
        const inputHoraUI = document.getElementById("hora_retiro_ui");
        const hiddenHora = document.getElementById("hora_retiro");
        if (inputHoraUI) inputHoraUI.value = "";
        if (hiddenHora) hiddenHora.value = "";
        if (typeof refreshSummary === 'function') refreshSummary();
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

            if (this.value && this.value !== "") {
                if (inputHoraUI) mostrarExito(inputHoraUI);
                mostrarExito(this);
            } else {
                if (inputHoraUI) limpiarError(inputHoraUI);
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
            if (this.value && this.value.trim() !== "") {
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
    24. SUBMIT POR AJAX
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
                mostrarToast("Ocurrió un error al registrar la reservación.", 'error');
                setLoading(false);
                return;
            }

            const data = await res.json().catch(() => ({}));
            if (data?.redirect_url) form.dataset.redirect = data.redirect_url;

            const confirmPop = qs("#confirmPop");
            if (confirmPop && !confirmPop.dataset.bound) {
                confirmPop.dataset.bound = "1";

                const confirmOk = qs("#confirmOk");
                if (confirmOk) {
                    confirmOk.addEventListener("click", () => {
                        window.location.href = "/admin/reservaciones-activas";
                    });
                }

                const confirmClose = qs("#confirmClose");
                if (confirmClose) {
                    confirmClose.addEventListener("click", () => {
                        window.location.href = "/ventas/menu";
                    });
                }
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
    25. TABS EN MODAL PROTECCIONES
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
    26. LOAD PROTECCIONES (PAQUETES)
    ========================================= */
    async function loadProtecciones() {
        const track = qs("#protePacksTrack");
        if (!track) return;

        track.innerHTML = `<div class="loading">Cargando paquetes...</div>`;

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
                const charge = "por_dia"; // 🔧 el paquete usa precio_por_dia → siempre por día
                return { id, nombre, desc, precio, charge };
            }).sort((a, b) => Number(b.precio || 0) - Number(a.precio || 0));

            if (!arr.length) {
                track.innerHTML = `<div class="loading">No hay protecciones disponibles.</div>`;
                return;
            }

            track.innerHTML = "";

            function obtenerMontoGarantia(categoriaId, nombreProteccion) {
                const garantiaPorCategoria = {
                    1: { 'LDW': 5000, 'PDW': 8000, 'CDW 10%': 15000, 'CDW 20%': 25000, 'CDW declinado': 330000 },
                    2: { 'LDW': 5000, 'PDW': 8000, 'CDW 10%': 18000, 'CDW 20%': 25000, 'CDW declinado': 380000 },
                    3: { 'LDW': 5000, 'PDW': 8000, 'CDW 10%': 20000, 'CDW 20%': 30000, 'CDW declinado': 500000 },
                    4: { 'LDW': 5000, 'PDW': 15000, 'CDW 10%': 30000, 'CDW 20%': 40000, 'CDW declinado': 650000 },
                    5: { 'LDW': 5000, 'PDW': 8000, 'CDW 10%': 20000, 'CDW 20%': 30000, 'CDW declinado': 500000 },
                    6: { 'LDW': 5000, 'PDW': 10000, 'CDW 10%': 30000, 'CDW 20%': 40000, 'CDW declinado': 600000 },
                    7: { 'LDW': 5000, 'PDW': 8000, 'CDW 10%': 18000, 'CDW 20%': 25000, 'CDW declinado': 400000 },
                    8: { 'LDW': 10000, 'PDW': 20000, 'CDW 10%': 30000, 'CDW 20%': 40000, 'CDW declinado': 800000 },
                    9: { 'LDW': 10000, 'PDW': 20000, 'CDW 10%': 30000, 'CDW 20%': 40000, 'CDW declinado': 800000 },
                    10: { 'LDW': 10000, 'PDW': 20000, 'CDW 10%': 30000, 'CDW 20%': 40000, 'CDW declinado': 600000 },
                    11: { 'LDW': 10000, 'PDW': 20000, 'CDW 10%': 30000, 'CDW 20%': 40000, 'CDW declinado': 900000 }
                };

                const catId = parseInt(categoriaId);
                const garantias = garantiaPorCategoria[catId];
                if (!garantias) return null;

                const nombreUpper = nombreProteccion.toUpperCase();

                if (nombreUpper.includes('LDW')) return garantias['LDW'];
                if (nombreUpper.includes('PDW')) return garantias['PDW'];
                if (nombreUpper.includes('10%') || nombreUpper.includes('CDW PACK 1')) return garantias['CDW 10%'];
                if (nombreUpper.includes('20%') || nombreUpper.includes('CDW PACK 2')) return garantias['CDW 20%'];
                if (nombreUpper.includes('DECLINE') || nombreUpper.includes('RECHAZAR')) return garantias['CDW declinado'];

                return garantias['CDW 20%'];
            }

            arr.forEach((p) => {
                const isSelected = state.proteccion?.id == p.id;

                const categoriaActual = window.state?.categoria;
                let textoGarantia = '';

                if (categoriaActual && categoriaActual.id) {
                    const montoGarantia = obtenerMontoGarantia(categoriaActual.id, p.nombre);
                    if (montoGarantia !== null) {
                        const montoFormateado = new Intl.NumberFormat('es-MX', {
                            style: 'currency',
                            currency: 'MXN',
                            minimumFractionDigits: 0,
                            maximumFractionDigits: 0
                        }).format(montoGarantia);
                        textoGarantia = `<i class="fas fa-shield-alt" style="margin-right: 8px;"></i> <strong>GARANTÍA:</strong> ${montoFormateado}`;
                    }
                }

                const card = document.createElement("article");
                card.className = `pack-card${isSelected ? " is-selected" : ""}`;
                card.dataset.id = p.id;
                card.dataset.nombre = p.nombre;
                card.dataset.precio = p.precio;
                card.dataset.charge = p.charge;

                let descHtml = '';
                if (p.desc) {
                    let items = p.desc.split(/[-–—·•\n]+/).filter(item => item.trim().length > 0);
                    items = items.map(item => item.trim().replace(/^\s*[-–—·•]\s*/, '').trim());
                    if (items.length === 0) items = [p.desc];

                    const listItems = items.map(item => `<li>${aplicarNegritas(escapeHtml(item))}</li>`).join('');
                    descHtml = `<ul class="desc-list">${listItems}`;
                    if (textoGarantia) descHtml += `<li class="garantia-item">${textoGarantia}</li>`;
                    descHtml += `</ul>`;
                } else {
                    descHtml = `<ul class="desc-list"><li>Sin descripción disponible</li>${textoGarantia ? `<li class="garantia-item">${textoGarantia}</li>` : ''}</ul>`;
                }

                card.innerHTML = `
                    <div class="body">
                        <h4>${escapeHtml(p.nombre)}</h4>
                        ${descHtml}
                        <div class="precio">
                            <strong>${money(p.precio).replace(" MXN", "")}</strong>
                            <span>MXN ${p.charge === "por_dia" ? "/ día" : ""}</span>
                        </div>
                        <div class="actions">
                            <div class="btn-proteccion-wrapper">
                                <button class="btn primary btn-proteccion-dividido ${isSelected ? 'activado' : 'desactivado'}" type="button" data-id="${p.id}" data-nombre="${escapeHtml(p.nombre)}" data-precio="${p.precio}" data-charge="${p.charge}">
                                    <span class="btn-texto">${isSelected ? 'Seleccionado' : ''}</span>
                                </button>
                            </div>
                        </div>
                    </div>
                `;

                const btn = card.querySelector('.btn-proteccion-dividido');
                if (btn) {
                    btn.addEventListener('click', (e) => {
                        e.stopPropagation();

                        const isCurrentlySelected = btn.classList.contains('activado');

                        if (!isCurrentlySelected) {
                            qsa('.pack-card').forEach(c => {
                                c.classList.remove('is-selected');
                                const b = c.querySelector('.btn-proteccion-dividido');
                                if (b) {
                                    b.classList.remove('activado');
                                    b.classList.add('desactivado');
                                    const span = b.querySelector('.btn-texto');
                                    if (span) span.textContent = '';
                                }
                            });

                            btn.classList.remove('desactivado');
                            btn.classList.add('activado');
                            const spanTexto = btn.querySelector('.btn-texto');
                            if (spanTexto) spanTexto.textContent = 'Seleccionado';
                            card.classList.add('is-selected');

                            setProteccion({
                                id: p.id,
                                nombre: p.nombre,
                                precio: p.precio,
                                charge: p.charge,
                                desc: p.desc
                            });
                        } else {
                            btn.classList.remove('activado');
                            btn.classList.add('desactivado');
                            const spanTexto = btn.querySelector('.btn-texto');
                            if (spanTexto) spanTexto.textContent = '';
                            card.classList.remove('is-selected');
                            setProteccion(null);
                        }

                        refreshProteccionUIHeader();
                        syncTotalsHidden();
                        refreshSummary();
                    });
                }

                track.appendChild(card);
            });

        } catch (e) {
            console.error("Protecciones error:", e);
            track.innerHTML = `<div class="loading">Error cargando protecciones.</div>`;
        }
    }

    /* =========================================
    27. PAISES + LADA + ISO2
    ========================================= */
    const COUNTRY_DATA = [
        { name: "MÉXICO", iso2: "MX", dial: "+52" },
        { name: "ESTADOS UNIDOS", iso2: "US", dial: "+1" },
        { name: "CANADÁ", iso2: "CA", dial: "+1" },
        { name: "ESPAÑA", iso2: "ES", dial: "+34" },
        { name: "COLOMBIA", iso2: "CO", dial: "+57" },
        { name: "ARGENTINA", iso2: "AR", dial: "+54" },
        { name: "PERÚ", iso2: "PE", dial: "+51" },
        { name: "CHILE", iso2: "CL", dial: "+56" },
        { name: "GUATEMALA", iso2: "GT", dial: "+502" },
        { name: "EL SALVADOR", iso2: "SV", dial: "+503" },
        { name: "HONDURAS", iso2: "HN", dial: "+504" },
        { name: "NICARAGUA", iso2: "NI", dial: "+505" },
        { name: "COSTA RICA", iso2: "CR", dial: "+506" },
        { name: "PANAMÁ", iso2: "PA", dial: "+507" },
        { name: "REPÚBLICA DOMINICANA", iso2: "DO", dial: "+1" },
        { name: "BRASIL", iso2: "BR", dial: "+55" },
        { name: "URUGUAY", iso2: "UY", dial: "+598" },
        { name: "PARAGUAY", iso2: "PY", dial: "+595" },
        { name: "BOLIVIA", iso2: "BO", dial: "+591" },
        { name: "ECUADOR", iso2: "EC", dial: "+593" },
        { name: "VENEZUELA", iso2: "VE", dial: "+58" },
    ];

    const TOP = ["MÉXICO", "ESTADOS UNIDOS"];
    const REST = COUNTRY_DATA.filter(x => !TOP.includes(x.name)).sort((a, b) => norm(a.name).localeCompare(norm(b.name)));

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
        const initial = COUNTRIES.find(c => norm(c.name) === initialName) || COUNTRIES.find(c => c.name === "MÉXICO") || COUNTRIES[0];

        setPhoneCountry(initial);
    }

    /* =========================================
    28. EVENTOS UI
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
                syncDays();
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

        getGasolinaEls().toggle?.addEventListener("change", (e) => {
            setGasolinaActive(!!e.target.checked);
        });

        qs("#dropoffToggle")?.addEventListener("change", (e) => {
            setDropoffActive(!!e.target.checked);
        });

        qs("#deliveryToggle")?.addEventListener("change", (e) => {
            setDeliveryActive(!!e.target.checked);
        });

        const catPop = qs("#catPop");
        qs("#btnCategorias")?.addEventListener("click", () => {
            repaintCategoriaModalEstimados();
            openPop(catPop);
        });

        qs("#btnEditarCategoriaPreview")?.addEventListener("click", () => {
            repaintCategoriaModalEstimados();
            openPop(catPop);
        });

        qs("#catClose")?.addEventListener("click", () => closePop(catPop));
        qs("#catCancel")?.addEventListener("click", () => closePop(catPop));

        catPop?.addEventListener("click", (e) => {
            const card = e.target.closest(".card-pick");
            if (!card) return;

            setCategoria({
                id: card.dataset.id,
                nombre: card.dataset.nombre || "",
                desc: card.dataset.desc || "",
                precio_dia: Number(card.dataset.precio || 0),
                precio_km: Number(card.dataset.precioKm || 0),
                img: card.dataset.img || "",
                capacidad_tanque: parseFloat(card.dataset.litros || 0)
            });
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
            if (card && !e.target.closest("button,a,input,textarea,select")) {
                toggleIndividualFromCard(card);
            }
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

        const forceDropoffSync = () => {
            const dropoffToggle = document.getElementById('dropoffToggle');
            if (dropoffToggle && dropoffToggle.checked) {
                const els = getDropoffEls();
                if (els && els.toggle && els.toggle.checked) {
                    const changeEvent = new Event('change', { bubbles: true });
                    els.toggle.dispatchEvent(changeEvent);
                }
            }
        };

        const differentDropoffCheckbox = document.getElementById('differentDropoffAdmin');
        if (differentDropoffCheckbox) {
            differentDropoffCheckbox.removeEventListener('change', forceDropoffSync);
            differentDropoffCheckbox.addEventListener('change', forceDropoffSync);
        }

        const sucursalRetiro = document.getElementById('sucursal_retiro');
        if (sucursalRetiro) {
            $(sucursalRetiro).off('change change.select2', forceDropoffSync);
            $(sucursalRetiro).on('change change.select2', forceDropoffSync);
        }

        const sucursalEntrega = document.getElementById('sucursal_entrega');
        if (sucursalEntrega) {
            $(sucursalEntrega).off('change change.select2', forceDropoffSync);
            $(sucursalEntrega).on('change change.select2', forceDropoffSync);
        }

        document.getElementById("rv2OptionsList")?.addEventListener("click", (e) => {
            const btnEditar = e.target.closest(".btn-edit-mini") || e.target.closest(".btn-editar-cantidad");
            if (!btnEditar) return;

            closePop(resPop);

            if (protPop) {
                openPop(protPop);
                setProteTab("tab-individuales");
            }
        });
    }

    /* =========================================
    29. BOOT (INICIALIZACIÓN)
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

        const gasT = getGasolinaEls().toggle;
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
        filtrarHorasPasadas();

        initPhoneCombo();
        syncTelefonoFinal();

        bindUI();
        initAddonsWithSwitch();

        setTimeout(() => {
            initSelect2EnAdicionales();
        }, 500);

        setTimeout(() => {
            preseleccionarProteccionesIndividuales();
            console.log("🎯 Preselección de protecciones ejecutada");
        }, 500);
    });

    /* =========================================
    30. SELECT2 CON ICONOS
    ========================================= */
    function initSelect2Sucursales() {
        function formatOption(option) {
            let iconClass = 'fa-location-dot';
            if (option.id && window.iconosPorId && window.iconosPorId[option.id]) {
                iconClass = window.iconosPorId[option.id];
            }
            return $('<span><i class="fa-solid ' + iconClass + '"></i>' + option.text + '</span>');
        }

        const select2Config = {
            templateResult: formatOption,
            templateSelection: formatOption,
            escapeMarkup: (m) => m,
            width: '100%',
            minimumResultsForSearch: Infinity
        };

        $('#sucursal_retiro').select2(select2Config);
        $('#sucursal_entrega').select2(select2Config);

        $('#sucursal_retiro, #sucursal_entrega').on('change select2:select', function () {
            syncVueloField();
            refreshSummary();
        });
    }

    function initSelect2EnAdicionales() {
        const select2Config = {
            placeholder: "Buscar ubicación...",
            allowClear: false,
            width: '100%',
            minimumInputLength: 0
        };

        const deliverySelect = document.getElementById('deliveryUbicacion');
        if (deliverySelect) {
            if ($(deliverySelect).data('select2')) $(deliverySelect).select2('destroy');
            $(deliverySelect).select2(select2Config);
            // Listener de cálculo real está en bindDeliveryUI (sección 09).
            // Se elimina el dispatchEvent que se auto-disparaba y causaba bucle infinito.
        }

        const dropoffSelect = document.getElementById('dropUbicacion');
        if (dropoffSelect) {
            if ($(dropoffSelect).data('select2')) $(dropoffSelect).select2('destroy');
            $(dropoffSelect).select2(select2Config);
            // Listener de cálculo real está en bindDropoffUI (sección 10) y en la
            // sincronización de la sección 41. Se elimina el dispatchEvent que se
            // auto-disparaba y causaba el bucle infinito (Maximum call stack size).
        }
    }

    /* =========================================
    31. CHECKBOX "DEVOLVER EN OTRO DESTINO"
    ========================================= */
    document.addEventListener('DOMContentLoaded', function() {
        const checkbox = document.getElementById('differentDropoffAdmin');
        const dropoffWrapper = document.getElementById('dropoffWrapperAdmin');
        const dropoffSelect = document.getElementById('sucursal_entrega');
        const pickupSelect = document.getElementById('sucursal_retiro');

        if (checkbox && dropoffWrapper && dropoffSelect) {
            function updateUI() {
                const isChecked = checkbox.checked;
                dropoffWrapper.style.display = isChecked ? 'block' : 'none';
                dropoffSelect.disabled = !isChecked;
                if (typeof refreshSummary === 'function') refreshSummary();
            }

            updateUI();
            checkbox.addEventListener('change', updateUI);

            if (pickupSelect) {
                pickupSelect.addEventListener('change', function() {
                    if (!checkbox.checked && this.value) {
                        dropoffSelect.value = this.value;
                        if (typeof $ !== 'undefined' && $(dropoffSelect).data('select2')) {
                            $(dropoffSelect).val(this.value).trigger('change.select2');
                        }
                        syncVueloField();
                        if (typeof refreshSummary === 'function') refreshSummary();
                    }
                });
            }

            dropoffSelect.addEventListener('change', function() {
                if (typeof refreshSummary === 'function') refreshSummary();
            });
        }
    });

    /* =========================================
    32. NAVBAR RESERVACIONES
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
                                <div class="days-pill-admin" id="daysPillNav">
                                    <i class="fa-regular fa-clock"></i>
                                    <span id="diasTxtNav">0</span> día(s)
                                </div>
                                <button class="btn-resumen-minimal" id="btnResumenNav">
                                    <i class="fa-solid fa-cart-shopping"></i>
                                    <span id="btnTotalNav">Total: $0.00 MXN</span>
                                </button>
                                <button class="btn-salir-minimal" id="btnSalirNav" title="Salir">✕</button>
                            </div>
                        </div>
                    </div>
                </div>
            `;

            document.body.insertAdjacentHTML('afterbegin', navbarHTML);

            const btnResumenOriginal = document.getElementById('btnResumen');
            document.getElementById('btnResumenNav')?.addEventListener('click', () => btnResumenOriginal?.click());

            document.getElementById('btnSalirNav')?.addEventListener('click', () => {
                window.location.href = '/ventas/menu';
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
    33. EDITAR TARIFA DESDE LA VISTA PREVIA DE CATEGORÍA
    ========================================= */
  function initEditarCategoriaPreview() {
    const btnEditar = document.getElementById('btnEditarCategoriaPreview');
    const container = document.getElementById('catMiniRate');

    if (!btnEditar || !container) return;

    const newBtn = btnEditar.cloneNode(true);
    btnEditar.parentNode.replaceChild(newBtn, btnEditar);

    newBtn.addEventListener('click', (e) => {
        e.stopPropagation();

        if (!state.categoria) {
            mostrarToast('Primero debes seleccionar una categoría', 'warning');
            return;
        }

        if (container.querySelector('input')) return;

        const precioActual = state.base_editable !== null
            ? state.base_editable / (state.days || 1)
            : parseFloat(state.categoria.precio_dia || 0);

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

        container.textContent = '';
        container.appendChild(input);
        input.focus();
        input.select();

        const guardar = () => {
            let nuevoValor = parseFloat(input.value);
            if (isNaN(nuevoValor) || nuevoValor < 0) nuevoValor = precioActual;

            const days = state.days || 1;
            const nuevoTotal = nuevoValor * days;

            state.base_editable = nuevoTotal;
            state.categoria.precio_dia = nuevoValor;

            // Actualizar el precio por día en la vista previa
            container.textContent = `${money(nuevoValor).replace(" MXN", "")} MXN / día`;

            // ACTUALIZAR TAMBIÉN EL CÁLCULO TOTAL (catMiniCalc)
            const calcElement = document.getElementById('catMiniCalc');
            if (calcElement) {
                const nuevoCalculo = nuevoValor * days;
                calcElement.textContent = money(nuevoCalculo);
            }

            // Actualizar el subtítulo de la categoría si existe
            const sub = document.getElementById('catSelSub');
            if (sub) sub.textContent = `${money(nuevoValor)} / día · ${days} día(s)`;

            // Forzar actualización de totales y resumen
            syncTotalsHidden();
            refreshSummary();

            mostrarToast(`✅ Tarifa actualizada a ${money(nuevoValor)}/día`, 'success');
        };

        input.addEventListener('blur', guardar);
        input.addEventListener('keydown', (ev) => {
            if (ev.key === 'Enter') {
                ev.preventDefault();
                guardar();
            }
            if (ev.key === 'Escape') {
                ev.preventDefault();
                container.textContent = `${money(precioActual).replace(" MXN", "")} MXN / día`;
                const calcElement = document.getElementById('catMiniCalc');
                if (calcElement) {
                    const calculoOriginal = precioActual * (state.days || 1);
                    calcElement.textContent = money(calculoOriginal);
                }
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
    34. MODAL DE CARACTERÍSTICAS DE CATEGORÍAS
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
            if (existingModal) existingModal.remove();

            featuresModal = document.createElement('div');
            featuresModal.id = 'featuresModal';
            featuresModal.className = 'modal-features';
            featuresModal.style.cssText = 'position:fixed;top:0;left:0;width:100%;height:100%;background:rgba(0,0,0,0.65);display:none;align-items:center;justify-content:center;z-index:10001;backdrop-filter:blur(2px);';
            featuresModal.innerHTML = `
                <div class="modal-box" style="background:white;width:min(500px,90vw);border-radius:24px;overflow:hidden;box-shadow:0 30px 70px rgba(0,0,0,0.3);">
                    <div class="header" style="background:linear-gradient(180deg, var(--brand), var(--brand-dark)) !important;border-bottom:1px solid rgba(227,0,0,0.3);color:white;padding:18px 20px;display:flex;justify-content:space-between;align-items:center;">
                        <h3 style="margin:0;font-size:20px;font-weight:900;display:flex;align-items:center;gap:10px;color:white !important;">
                            <i class='bx bx-car' style="color:white !important;"></i>
                            <span id="featuresCatName" style="color:white !important;">Categoría</span>
                        </h3>
                        <button id="closeFeaturesModalBtn" style="background:rgba(255,255,255,0.2);border:none;color:white;width:36px;height:36px;border-radius:50%;cursor:pointer;font-size:18px;">✖</button>
                    </div>
                    <div class="body" style="padding:20px;max-height:60vh;overflow-y:auto;">
                        <div class="features-list" id="featuresListContainer" style="display:flex;flex-direction:column;gap:12px;"></div>
                    </div>
                </div>
            `;

            document.body.appendChild(featuresModal);

            const closeBtn = featuresModal.querySelector('#closeFeaturesModalBtn');
            if (closeBtn) {
                closeBtn.addEventListener('click', (e) => {
                    e.preventDefault();
                    e.stopPropagation();
                    closeFeaturesModal();
                });
            }

            featuresModal.addEventListener('click', (e) => {
                if (e.target === featuresModal) closeFeaturesModal();
            });

            document.addEventListener('keydown', (e) => {
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
                    item.style.cssText = 'display:flex;align-items:center;gap:14px;padding:12px 16px;background:#f8fafc;border-radius:14px;border:1px solid #e2e8f0;transition:all 0.2s;';
                    item.innerHTML = `
                        <i class="${f.icon || 'bx bx-check'}" style="font-size:24px;width:32px;text-align:center;"></i>
                        <span class="feature-text" style="font-weight:700;color:#1e293b;font-size:15px;">${escapeHtml(f.text)}</span>
                    `;
                    container.appendChild(item);
                });
            }

            modal.style.display = 'flex';
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
        if (window._reservaAPI && window._reservaAPI.setCategoria) {
            window._reservaAPI.setCategoria({
                id: cardElement.dataset.id,
                nombre: cardElement.dataset.nombre || "",
                desc: cardElement.dataset.desc || "",
                precio_dia: Number(cardElement.dataset.precio || 0),
                precio_km: Number(cardElement.dataset.precioKm || 0),
                img: cardElement.dataset.img || "",
                capacidad_tanque: parseFloat(cardElement.dataset.litros || 0)
            });
        }
        const catPop = document.getElementById('catPop');
        if (catPop) catPop.style.display = 'none';
    }

    /* =========================================
    35. ACORDEÓN Y FLUJO SECUENCIAL
    ========================================= */
    (function() {
        let seccionesCompletadas = { categoria: false };

        function expandirSeccion(seccionId) {
            const seccion = document.querySelector(`.acordeon-item[data-seccion="${seccionId}"]`);
            if (!seccion) return;

            seccion.style.display = 'block';

            const body = seccion.querySelector('.stack-body');
            const indicator = seccion.querySelector('.stack-indicator');

            if (body && !body.classList.contains('expanded')) {
                body.classList.add('expanded');
            }
            if (indicator && !indicator.classList.contains('expanded')) {
                indicator.classList.add('expanded');
            }

            if (seccionId !== 'cliente') {
                setTimeout(() => {
                    seccion.scrollIntoView({ behavior: 'smooth', block: 'start' });
                }, 100);
            }
        }

        function colapsarSeccion(seccionId) {
            const seccion = document.querySelector(`.acordeon-item[data-seccion="${seccionId}"]`);
            if (!seccion) return;

            const body = seccion.querySelector('.stack-body');
            const indicator = seccion.querySelector('.stack-indicator');

            if (body && body.classList.contains('expanded')) {
                body.classList.remove('expanded');
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
                    const body = seccion?.querySelector('.stack-body');
                    const indicator = seccion?.querySelector('.stack-indicator');

                    if (body) body.classList.toggle('expanded');
                    if (indicator) indicator.classList.toggle('expanded');
                });
            });
        }

        function validarSucursalesLocal() {
            const sucRetiro = document.getElementById("sucursal_retiro")?.value;
            if (!sucRetiro) {
                mostrarToast('⚠️ Primero debes seleccionar las sucursales de RETIRO y ENTREGA', 'warning');
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
                mostrarToast('⚠️ Completa FECHA y HORA de salida y llegada', 'warning');
                return false;
            }
            return true;
        }

        function initBotonBuscar() {
            const btnBuscar = document.getElementById('btnBuscarReservacion');
            if (!btnBuscar) return;

            const nuevoBtn = btnBuscar.cloneNode(true);
            btnBuscar.parentNode.replaceChild(nuevoBtn, btnBuscar);

            nuevoBtn.addEventListener('click', async (e) => {
                e.preventDefault();
                e.stopPropagation();

                if (!validarFechasHorasLocal()) return;

                expandirSeccion('categoria');

                setTimeout(() => {
                    const btnCategorias = document.getElementById('btnCategorias');
                    if (btnCategorias) btnCategorias.click();
                }, 300);
            });
        }

        function initSeleccionCategoria() {
            const seleccionarCategoriaOriginal = window.seleccionarCategoriaReservacion;

            window.seleccionarCategoriaReservacion = async function(element) {
                if (seleccionarCategoriaOriginal && typeof seleccionarCategoriaOriginal === 'function') {
                    seleccionarCategoriaOriginal(element);
                }

                await new Promise(resolve => setTimeout(resolve, 200));

                const categoriaSeleccionada = window.state?.categoria || state?.categoria;

                if (categoriaSeleccionada) {
                    marcarCompletada('categoria');
                    expandirSeccion('adicionales');

                    setTimeout(() => {
                        if (typeof desbloquearProtecciones === 'function') desbloquearProtecciones();
                        if (typeof desbloquearCliente === 'function') desbloquearCliente();
                        if (typeof actualizarTodasSecciones === 'function') actualizarTodasSecciones();
                        mostrarToast(`✅ Categoría ${categoriaSeleccionada.nombre} seleccionada`, 'success');
                    }, 200);
                }
            };
        }

        function initCarousel() {
            const container = document.querySelector(".carousel-container");
            const btnPrev = document.querySelector(".carousel-arrow.prev");
            const btnNext = document.querySelector(".carousel-arrow.next");

            if (!container || !btnPrev || !btnNext) return;

            const card = document.querySelector(".carousel-item");
            const scrollAmount = card ? card.offsetWidth + 20 : 320;

            btnNext.addEventListener("click", () => {
                container.scrollBy({ left: scrollAmount, behavior: "smooth" });
            });

            btnPrev.addEventListener("click", () => {
                container.scrollBy({ left: -scrollAmount, behavior: "smooth" });
            });
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

    /* =========================================
    36. TOOLTIPS PARA ADICIONALES
    ========================================= */
    (function() {
        const descripciones = {
            'Conductor adicional': 'Agregar un conductor extra.',
            'Gasolina prepago': 'Tanque completo preferencial.',
            'Drop Off': 'Entrega en sucursal distinta.',
            'Delivery': 'Entrega a domicilio.',
            'Silla de bebé': 'Silla de seguridad para bebé.'
        };

        let activeTooltip = null;
        let tooltipTimeout = null;
        let isMobile = /Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent);
        let modal = document.getElementById('modalInfoAdicional');

        if (!modal) {
            modal = document.createElement('div');
            modal.id = 'modalInfoAdicional';
            modal.className = 'modal-info-overlay';
            modal.innerHTML = `
                <div class="modal-info-container">
                    <div class="modal-info-header">
                        <i id="modalInfoIcon" class="fas fa-info-circle"></i>
                        <h3 id="modalInfoTitulo"></h3>
                        <button class="modal-info-close">&times;</button>
                    </div>
                    <div class="modal-info-body">
                        <p id="modalInfoDescripcion" class="modal-info-desc"></p>
                    </div>
                </div>
            `;
            document.body.appendChild(modal);
        }

        const modalTitulo = document.getElementById('modalInfoTitulo');
        const modalDescripcion = document.getElementById('modalInfoDescripcion');
        const modalIcon = document.getElementById('modalInfoIcon');
        const closeBtn = document.querySelector('.modal-info-close');

        function abrirModal(titulo, descripcion, iconClass) {
            modalTitulo.textContent = titulo;
            modalDescripcion.textContent = descripcion;
            if (iconClass) modalIcon.className = iconClass;
            modal.style.display = 'flex';
        }

        function cerrarModal() {
            modal.style.display = 'none';
        }

        if (closeBtn) closeBtn.addEventListener('click', cerrarModal);
        modal.addEventListener('click', (e) => { if (e.target === modal) cerrarModal(); });
        document.addEventListener('keydown', (e) => { if (e.key === 'Escape' && modal.style.display === 'flex') cerrarModal(); });

        function showTooltip(element, text) {
            if (activeTooltip) activeTooltip.remove();
            if (tooltipTimeout) clearTimeout(tooltipTimeout);

            const tooltip = document.createElement('div');
            tooltip.className = 'info-tooltip';
            if (text.length > 40) tooltip.classList.add('multiline');
            tooltip.textContent = text;
            document.body.appendChild(tooltip);

            const rect = element.getBoundingClientRect();
            let top = rect.bottom + 8;
            let left = rect.left + (rect.width / 2) - (tooltip.offsetWidth / 2);

            if (left < 10) left = 10;
            if (left + tooltip.offsetWidth > window.innerWidth - 10) {
                left = window.innerWidth - tooltip.offsetWidth - 10;
            }

            tooltip.style.top = `${top}px`;
            tooltip.style.left = `${left}px`;
            activeTooltip = tooltip;
        }

        function hideTooltip() {
            if (tooltipTimeout) clearTimeout(tooltipTimeout);
            tooltipTimeout = setTimeout(() => {
                if (activeTooltip) activeTooltip.remove();
                activeTooltip = null;
            }, 150);
        }

        function hideTooltipImmediately() {
            if (tooltipTimeout) clearTimeout(tooltipTimeout);
            if (activeTooltip) {
                activeTooltip.remove();
                activeTooltip = null;
            }
        }

        document.querySelectorAll('.svc-card').forEach(card => {
            const iconContainer = card.querySelector('.svc-ico');
            const titulo = card.querySelector('.svc-name')?.textContent || '';
            const descripcion = descripciones[titulo] || 'Sin información disponible';

            let iconClass = '';
            if (iconContainer?.querySelector('i')) {
                const classes = iconContainer.querySelector('i').className;
                if (classes.includes('fa-user-plus')) iconClass = 'fas fa-user-plus';
                else if (classes.includes('fa-gas-pump')) iconClass = 'fas fa-gas-pump';
                else if (classes.includes('fa-flag-checkered')) iconClass = 'fas fa-flag-checkered';
                else if (classes.includes('fa-truck')) iconClass = 'fas fa-truck';
                else if (classes.includes('fa-baby-carriage')) iconClass = 'fas fa-baby-carriage';
                else iconClass = 'fas fa-info-circle';
            }

            if (iconContainer) {
                if (!isMobile) {
                    iconContainer.addEventListener('mouseenter', (e) => {
                        e.stopPropagation();
                        showTooltip(iconContainer, descripcion);
                    });
                    iconContainer.addEventListener('mouseleave', () => hideTooltip());
                }

                iconContainer.addEventListener('click', (e) => {
                    e.stopPropagation();
                    hideTooltipImmediately();
                    abrirModal(titulo, descripcion, iconClass);
                });
            }
        });

        window.addEventListener('scroll', hideTooltipImmediately);
        window.addEventListener('resize', hideTooltipImmediately);
    })();

    /* =========================================
    37. PERMITIR AUTOCOMPLETADO PERO PREVENIR SCROLL
    ========================================= */
    (function() {
        let clienteSectionTop = null;
        let preventScroll = true;

        function saveClienteSectionPosition() {
            const clienteSection = document.querySelector('.acordeon-item[data-seccion="cliente"]');
            if (clienteSection && clienteSection.querySelector('.stack-body')?.classList.contains('expanded')) {
                const rect = clienteSection.getBoundingClientRect();
                clienteSectionTop = rect.top + window.scrollY;
            }
        }

        function restoreClientePosition() {
            if (preventScroll && clienteSectionTop !== null) {
                const currentScroll = window.scrollY;
                if (Math.abs(currentScroll - clienteSectionTop) > 50) {
                    window.scrollTo({ top: clienteSectionTop, behavior: 'instant' });
                }
            }
        }

        const clienteSection = document.querySelector('.acordeon-item[data-seccion="cliente"]');
        if (clienteSection) {
            const observer = new MutationObserver(() => {
                const body = clienteSection.querySelector('.stack-body');
                if (body && body.classList.contains('expanded')) {
                    setTimeout(saveClienteSectionPosition, 50);
                }
            });
            observer.observe(clienteSection, { attributes: true });
        }

        const clienteInputs = ['nombre_cliente', 'apellidos_cliente', 'email_cliente', 'telefono_ui', 'no_vuelo'];

        clienteInputs.forEach(inputId => {
            const input = document.getElementById(inputId);
            if (input) {
                input.addEventListener('focus', saveClienteSectionPosition);
                input.addEventListener('change', () => setTimeout(restoreClientePosition, 10));
                input.addEventListener('blur', () => setTimeout(restoreClientePosition, 20));
                input.addEventListener('input', saveClienteSectionPosition);
            }
        });

        document.addEventListener('focusin', (e) => {
            if (e.target.closest('.acordeon-item[data-seccion="cliente"]')) {
                saveClienteSectionPosition();
                e.preventDefault();
            }
        });

        setTimeout(saveClienteSectionPosition, 500);
    })();

 /* =========================================
    38. ACORDEÓN PARA DECLINE PROTECTIONS
    ========================================= */
    (function() {
        function aplicarAcordeonADecline() {
            const proteccionesTrack = document.getElementById('protePacksTrack');
            if (!proteccionesTrack) return;

            const cards = proteccionesTrack.querySelectorAll('.pack-card');

            cards.forEach((card) => {
                const titleElement = card.querySelector('h4');
                if (!titleElement) return;

                const titulo = titleElement.textContent.trim();

                if (titulo.toUpperCase().includes('DECLINE')) {
                    const bodyDiv = card.querySelector('.body');
                    if (!bodyDiv) return;

                    let extraInfo = card.querySelector('.decline-extra-info');
                    if (!extraInfo) {
                        extraInfo = document.createElement('div');
                        extraInfo.className = 'decline-extra-info';
                        extraInfo.style.cssText = 'transition: max-height 0.3s ease-out; max-height: 0; overflow: hidden;';

                        const descList = card.querySelector('.desc-list');
                        if (descList) {
                            bodyDiv.insertBefore(extraInfo, descList);
                            extraInfo.appendChild(descList);
                        }
                    }

                    const precio = card.querySelector('.precio');
                    if (precio) {
                        precio.style.display = 'flex';
                        precio.style.visibility = 'visible';
                        precio.style.opacity = '1';
                    }

                    const actions = card.querySelector('.actions');
                    if (actions) {
                        actions.style.marginTop = '0';
                        actions.style.paddingTop = '0';
                    }

                    card.style.cursor = 'pointer';

                    if (card._dblclickHandler) {
                        card.removeEventListener('dblclick', card._dblclickHandler);
                    }

                    card._dblclickHandler = function(e) {
                        e.stopPropagation();
                        const extra = this.querySelector('.decline-extra-info');
                        if (!extra) return;

                        if (extra.style.maxHeight !== '0px') {
                            extra.style.maxHeight = '0';
                        } else {
                            extra.style.maxHeight = extra.scrollHeight + 'px';
                        }
                    };

                    card.addEventListener('dblclick', card._dblclickHandler);

                    const btn = card.querySelector('.actions .btn');
                    if (btn && !btn._clickPrevented) {
                        btn._clickPrevented = true;
                        btn.addEventListener('click', (e) => {
                            e.stopPropagation();
                        });
                    }
                }
            });
        }

        const observador = new MutationObserver(() => {
            const track = document.getElementById('protePacksTrack');
            if (track && track.children.length > 0) {
                aplicarAcordeonADecline();
            }
        });

        observador.observe(document.body, { childList: true, subtree: true });

        const btnProtecciones = document.getElementById('btnProtecciones');
        if (btnProtecciones) {
            btnProtecciones.addEventListener('click', () => {
                setTimeout(aplicarAcordeonADecline, 800);
            });
        }

        setTimeout(aplicarAcordeonADecline, 1000);


    })();
    /* =========================================
    39. FIX: POSICIÓN EN INDIVIDUALES
    ========================================= */
    (function() {
        let proteccionModalAbierto = false;
        let posicionOriginal = null;
        let intervaloRestauracion = null;

        const modal = document.getElementById('proteccionPop');
        if (!modal) return;

        const btnProtecciones = document.getElementById('btnProtecciones');
        if (btnProtecciones) {
            btnProtecciones.addEventListener('click', () => {
                setTimeout(() => {
                    const seccion = document.querySelector('.acordeon-item[data-seccion="protecciones"]');
                    if (seccion) {
                        posicionOriginal = seccion.getBoundingClientRect().top + window.scrollY;
                    }
                }, 100);
            });
        }

        const observer = new MutationObserver(() => {
            if (modal.style.display === 'flex') {
                proteccionModalAbierto = true;

                if (intervaloRestauracion) clearInterval(intervaloRestauracion);
                intervaloRestauracion = setInterval(() => {
                    if (proteccionModalAbierto && posicionOriginal) {
                        const seccion = document.querySelector('.acordeon-item[data-seccion="protecciones"]');
                        if (seccion) {
                            const posActual = seccion.getBoundingClientRect().top + window.scrollY;
                            if (Math.abs(posActual - posicionOriginal) > 5) {
                                window.scrollTo({ top: posicionOriginal, behavior: 'instant' });
                            }
                        }
                    }
                }, 50);
            } else {
                proteccionModalAbierto = false;
                if (intervaloRestauracion) {
                    clearInterval(intervaloRestauracion);
                    intervaloRestauracion = null;
                }
                if (posicionOriginal) {
                    setTimeout(() => {
                        window.scrollTo({ top: posicionOriginal, behavior: 'instant' });
                    }, 10);
                }
            }
        });

        observer.observe(modal, { attributes: true, attributeFilter: ['style'] });
    })();

    /* =========================================
    40. RESUMEN CARRITO DINÁMICO PARA PROTECCIONES
    ========================================= */
    (function() {
        function crearOCarritoEnModal() {
            const modalHeader = document.querySelector('#proteccionPop .modal-head');
            if (!modalHeader) return null;

            let cartHeaderBtn = document.getElementById('cartHeaderBtn');
            if (cartHeaderBtn) return cartHeaderBtn;

            cartHeaderBtn = document.createElement('button');
            cartHeaderBtn.id = 'cartHeaderBtn';
            cartHeaderBtn.className = 'btn-cart-header';
            cartHeaderBtn.innerHTML = `
                <i class="fas fa-shopping-cart"></i>
                <span class="cart-header-total">$0.00 MXN</span>
                <span class="cart-header-badge">0</span>
            `;
            cartHeaderBtn.setAttribute('aria-label', 'Ver resumen completo');
            cartHeaderBtn.setAttribute('type', 'button');

            const closeBtn = modalHeader.querySelector('#proteClose');
            if (closeBtn) {
                modalHeader.insertBefore(cartHeaderBtn, closeBtn);
            } else {
                modalHeader.appendChild(cartHeaderBtn);
            }

            cartHeaderBtn.addEventListener('click', (e) => {
                e.stopPropagation();
                if (typeof refreshSummary === 'function') refreshSummary();
                const resumenPop = document.getElementById('resumenPop');
                if (resumenPop) resumenPop.style.display = 'flex';
            });

            return cartHeaderBtn;
        }

        function obtenerTotalGeneral() {
            if (typeof calcTotals === 'function') {
                return calcTotals().total || 0;
            }
            return 0;
        }

        function contarItemsSeleccionados() {
            let count = 0;
            if (state.proteccion !== null) count++;
            count += state.individuales.size;
            return count;
        }

        function tieneItemsConCosto() {
            if (!state.categoria) return false;
            const totalGeneral = obtenerTotalGeneral();
            if (totalGeneral <= 0) return false;
            return (state.proteccion !== null) || (state.individuales.size > 0);
        }

        function actualizarCarritoEnModal() {
            const cartHeaderBtn = document.getElementById('cartHeaderBtn');
            if (!cartHeaderBtn) return;

            const totalGeneral = obtenerTotalGeneral();
            const count = contarItemsSeleccionados();

            const totalSpan = cartHeaderBtn.querySelector('.cart-header-total');
            const badge = cartHeaderBtn.querySelector('.cart-header-badge');

            if (totalSpan) {
                totalSpan.textContent = money(totalGeneral);
            }

            if (badge) {
                badge.textContent = count;
                badge.style.display = count > 0 ? 'inline-flex' : 'none';
            }

            cartHeaderBtn.style.display = tieneItemsConCosto() ? 'inline-flex' : 'none';
        }

        function forzarActualizacionTotal() {
            if (typeof syncTotalsHidden === 'function') syncTotalsHidden();
            if (typeof refreshSummary === 'function') refreshSummary();
            setTimeout(actualizarCarritoEnModal, 50);
        }

        window.actualizarCarritoEnModal = actualizarCarritoEnModal;

        function initCarritoEnModal() {
            const checkModal = setInterval(() => {
                const modal = document.getElementById('proteccionPop');
                if (modal) {
                    clearInterval(checkModal);
                    crearOCarritoEnModal();
                    setTimeout(forzarActualizacionTotal, 200);
                }
            }, 100);

            const btnProtecciones = document.getElementById('btnProtecciones');
            if (btnProtecciones) {
                btnProtecciones.addEventListener('click', () => {
                    setTimeout(() => {
                        crearOCarritoEnModal();
                        forzarActualizacionTotal();
                    }, 200);
                });
            }

            setInterval(forzarActualizacionTotal, 1000);
        }

        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', initCarritoEnModal);
        } else {
            initCarritoEnModal();
        }
    })();

        /* =========================================
            41. SINCRONIZACIÓN BIDIRECCIONAL (Formulario ↔ DropOff)
        ========================================= */
(function() {
    let syncInProgress = false;
    let select2Initialized = false;

    const sucursalesExcluidas = [
        'Querétaro Aeropuerto',
        'Querétaro Central de Autobuses',
        'Querétaro Oficina Plaza Central Park'
    ];

    function limpiarTexto(texto) {
        return (texto || '').replace(/\s*\([^)]*\)/, '').trim();
    }

    function getSelectedBranchText() {
        const select = document.getElementById('sucursal_entrega');
        if (!select) return '';
        if (typeof $ !== 'undefined' && $(select).data('select2')) {
            const data = $(select).select2('data');
            if (data && data.length) return data[0].text || '';
        }
        return select.options[select.selectedIndex]?.text?.trim() || '';
    }

    function isExcluded() {
        const text = limpiarTexto(getSelectedBranchText());
        return sucursalesExcluidas.includes(text);
    }

    function limpiarDropoffCompleto() {
        const dropoffSelect = document.getElementById('dropUbicacion');
        if (dropoffSelect) {
            dropoffSelect.value = '';
            if (typeof $ !== 'undefined' && $(dropoffSelect).data('select2')) {
                $(dropoffSelect).val(null).trigger('change');
            }
            dropoffSelect.dispatchEvent(new Event('change', { bubbles: true }));
        }
        const dropDireccion = document.getElementById('dropDireccion');
        if (dropDireccion) dropDireccion.value = '';
        const dropKm = document.getElementById('dropKm');
        if (dropKm) dropKm.value = '';
        if (typeof state !== 'undefined') {
            state.dropoff.total = 0;
            state.dropoff.km = 0;
            state.dropoff.ubicacion = '';
            state.dropoff.direccion = '';
        }
        if (typeof syncDropoffHidden === 'function') syncDropoffHidden();
        if (typeof syncTotalsHidden === 'function') syncTotalsHidden();
        if (typeof refreshSummary === 'function') refreshSummary();
    }

    // ========== FORMULARIO → DROPOFF ==========
    function syncFormToDropoff() {
    if (syncInProgress) return;
    syncInProgress = true;

    const checkbox = document.getElementById('differentDropoffAdmin');
    const dropoffToggle = document.getElementById('dropoffToggle');

    if (!checkbox || !dropoffToggle) {
        syncInProgress = false;
        return;
    }

    // Solo si el checkbox está marcado
    if (checkbox.checked) {
        if (!dropoffToggle.checked) {
            dropoffToggle.checked = true;
            dropoffToggle.dispatchEvent(new Event('change', { bubbles: true }));
            console.log('✅ DropOff activado siempre (modo siempre activo)');
        }
    } else {
        if (dropoffToggle.checked) {
            dropoffToggle.checked = false;
            dropoffToggle.dispatchEvent(new Event('change', { bubbles: true }));
        }
    }

    syncInProgress = false;
}

    // ========== DROPOFF → FORMULARIO ==========
    function syncDropoffToForm() {
        // Solo refrescar resumen/totales; no modificar el valor de sucursal_entrega.
        if (typeof refreshSummary === 'function') refreshSummary();
        if (typeof syncTotalsHidden === 'function') syncTotalsHidden();
    }

    // ========== CONFIGURAR EVENTOS ==========
    function initSync() {
        const checkbox = document.getElementById('differentDropoffAdmin');
        const sucursalEntrega = document.getElementById('sucursal_entrega');
        const dropoffSelect = document.getElementById('dropUbicacion');
        const dropoffToggle = document.getElementById('dropoffToggle');

        if (!checkbox || !sucursalEntrega || !dropoffSelect) {
            setTimeout(initSync, 300);
            return;
        }

        function updateSelectState() {
            const wrapper = document.getElementById('dropoffWrapperAdmin');
            if (checkbox.checked) {
                if (wrapper) wrapper.style.display = 'block';
                sucursalEntrega.disabled = false;
                setTimeout(syncFormToDropoff, 100);
            } else {
                if (wrapper) wrapper.style.display = 'none';
                sucursalEntrega.disabled = true;
                if (dropoffToggle && dropoffToggle.checked) {
                    dropoffToggle.checked = false;
                    dropoffToggle.dispatchEvent(new Event('change', { bubbles: true }));
                }
            }
        }

        checkbox.addEventListener('change', updateSelectState);
        updateSelectState();

        sucursalEntrega.addEventListener('change', () => {
            if (!sucursalEntrega.disabled && checkbox.checked) {
                setTimeout(syncFormToDropoff, 50);
            }
        });

        dropoffSelect.addEventListener('change', () => {
            if (checkbox.checked) {
                setTimeout(syncDropoffToForm, 50);
            }
        });

        if (dropoffToggle) {
            dropoffToggle.addEventListener('change', () => {
                if (dropoffToggle.checked && checkbox.checked) {
                    setTimeout(syncFormToDropoff, 200);
                }
            });
        }

        console.log('✅ Sincronización bidireccional inicializada (corregida)');
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initSync);
    } else {
        initSync();
    }
})();
/* =========================================
42. ACTIVAR/DESACTIVAR DROPOFF SEGÚN CHECKBOX
========================================= */
(function() {
    function actualizarUI() {
        const checkbox = document.getElementById('differentDropoffAdmin');
        const dropoffWrapper = document.getElementById('dropoffWrapperAdmin');
        const sucursalEntrega = document.getElementById('sucursal_entrega');

        if (!checkbox) return;
        const isChecked = checkbox.checked;

        if (dropoffWrapper) dropoffWrapper.style.display = isChecked ? 'block' : 'none';
        if (sucursalEntrega) sucursalEntrega.disabled = !isChecked;
    }

    const checkbox = document.getElementById('differentDropoffAdmin');
    if (checkbox) {
        checkbox.addEventListener('change', actualizarUI);
    }
    actualizarUI();
})();
    /* =========================================
    43. EXPONER API GLOBAL
    ========================================= */
    window._reservaAPI = {
        setGasolinaActive: setGasolinaActive,
        setDropoffActive: setDropoffActive,
        setDeliveryActive: setDeliveryActive,
        setAddonQty: setAddonQty,
        setProteccion: setProteccion,
        setCategoria: setCategoria,
        syncTotalsHidden: syncTotalsHidden,
        refreshSummary: refreshSummary,
        forceRecalc: function() {
            syncDays();
            if (state.servicios.delivery) computeDelivery(getDeliveryEls());
            if (state.servicios.dropoff) computeDropoff(getDropoffEls());
            if (state.servicios.gasolina) computeGasolina();
            syncTotalsHidden();
            refreshSummary();
        },
        getState: () => state,

        // ---- Helpers para MODO EDICIÓN (no afectan el flujo de creación) ----

        // Activa una card de addon dinámica (enciende switch + cantidad + total),
        // leyendo el charge/precio REAL desde la card del DOM.
        activarAddonEdicion: function(idServicio, cantidad) {
            const qty = Math.max(1, Number(cantidad || 1));
            const cfg = getAddonConfig(idServicio);
            if (!cfg) return false;          // no existe o es tanque (gasolina)
            setAddonActive(idServicio, true);
            if (cfg.qtyEl) {
                cfg.qtyEl.textContent = qty;
                cfg.qtyEl.dataset.qty = qty;
            }
            updateAddonTotal(idServicio, qty);
            return true;
        },

        // Coloca fecha en el input UI (Flatpickr) y sincroniza el hidden + días.
        setFechaEdicion: function(uiId, hiddenId, valorISO) {
            const ui = qs(uiId);
            const hid = qs(hiddenId);
            if (!ui || !valorISO) return;

            const iso = String(valorISO).slice(0, 10); // "YYYY-MM-DD"
            const partes = iso.split("-").map(Number);
            // Construir Date local sin desfase de zona horaria (año, mes-1, día)
            const fechaObj = new Date(partes[0], (partes[1] || 1) - 1, partes[2] || 1, 0, 0, 0);

            const fp = ui._flatpickr;
            if (fp) {
                // Quitar el límite minDate para permitir fechas de reservas ya creadas
                fp.set("minDate", null);
                fp.setDate(fechaObj, true);   // objeto Date + dispara onChange
            } else {
                ui.value = iso;
            }

            // Asegurar el hidden en formato ISO (por si onChange no alcanzó)
            if (hid) hid.value = iso;
            syncDays();
        },

        // Coloca hora en el time-picker custom (.tp-hour) y sincroniza el hidden.
        setHoraEdicion: function(uiId, hiddenId, valorHora) {
            const ui = qs(uiId);
            const hid = qs(hiddenId);
            if (!ui || !valorHora) return;
            const hh = String(valorHora).split(":")[0].padStart(2, "0");
            const sel = ui.closest('.dt-field-admin, .time-field-admin')?.querySelector('.tp-hour');
            if (sel) {
                sel.value = hh;
                sel.dispatchEvent(new Event("change"));  // sincroniza hidden + syncDays
            } else {
                ui.value = `${hh}:00`;
                if (hid) hid.value = `${hh}:00`;
            }
        },

        // Desbloquea todas las secciones de golpe (reserva ya completa).
        desbloquearTodoEdicion: function() {
            if (typeof completarFlujo === 'function') completarFlujo();
        },

        // Opción B: abre (expande) las 4 secciones a la vez, sin scroll brusco.
        expandirTodasEdicion: function() {
            const secciones = ['categoria', 'adicionales', 'protecciones', 'cliente'];
            secciones.forEach(sec => {
                const el = document.querySelector(`.acordeon-item[data-seccion="${sec}"]`);
                if (el && typeof abrirSeccion === 'function') {
                    abrirSeccion(el, true);   // true = evitar scroll
                }
            });
        },

        // Opción B: abre (expande) las 4 secciones a la vez, sin scroll brusco.
        expandirTodasEdicion: function() {
            const secciones = ['categoria', 'adicionales', 'protecciones', 'cliente'];
            secciones.forEach(sec => {
                const el = document.querySelector(`.acordeon-item[data-seccion="${sec}"]`);
                if (el && typeof abrirSeccion === 'function') {
                    abrirSeccion(el, true);   // true = evitar scroll
                }
            });
        },

        // Restaura el Drop Off en edición con el TOTAL guardado tal cual (Opción A),
        // sin recalcular por ubicación (que se perdería y daría 0).
        restaurarDropoffEdicion: function(totalGuardado) {
            const els = getDropoffEls();
            if (!els) return;

            const total = parseFloat(totalGuardado) || 0;

            // Encender el switch y mostrar los campos, sin recalcular.
            state.servicios.dropoff = true;
            state.dropoff.activo = true;
            state.dropoff.total = total;   // ← total fijo restaurado
            state.dropoff.restaurado = true;   // ← respetar este total hasta que elijan ubicación

            if (els.toggle) els.toggle.checked = true;
            if (els.fields) els.fields.style.display = "block";
            if (els.totalTxt) els.totalTxt.textContent = money(total);

            // Volcar a hidden + resumen usando el total ya fijado.
            syncServiciosHidden();
            syncDropoffHidden();
            syncTotalsHidden();
            refreshSummary();
        },
    };

    /* =========================================
    44. CARGA DE EDICIÓN (RESERVACIÓN EXISTENTE)
    ========================================= */
    async function __cargarEdicionReserva() {
        const API = window._reservaAPI;

        // Solo corre en EDICIÓN real: debe existir un id_reservacion válido.
        if (!API) return;
        if (!window.reservacionEditar || !window.reservacionEditar.id_reservacion) return;

        const r = window.reservacionEditar;

        // ============================================================
        // 1) DESBLOQUEAR todas las secciones (la reserva ya está completa,
        //    no hay que forzar el flujo secuencial del botón "Buscar").
        // ============================================================
        if (typeof API.desbloquearTodoEdicion === 'function') {
            API.desbloquearTodoEdicion();
        }
        document.body.classList.add('buscar-realizado');

        // Opción B: abrir las 4 secciones expandidas de una vez.
        if (typeof API.expandirTodasEdicion === 'function') {
            API.expandirTodasEdicion();
        }

        // ============================================================
        // 2) SUCURSALES (retiro y entrega) + checkbox "devolver en otro destino"
        // ============================================================
        const selRetiro = document.getElementById("sucursal_retiro");
        if (selRetiro && r.sucursal_retiro != null) {
            selRetiro.value = String(r.sucursal_retiro);
            selRetiro.dispatchEvent(new Event("change"));
        }

        // Si la entrega es distinta al retiro, activar el dropoff diferente
        const hayDropoffDistinto = r.sucursal_entrega != null &&
                                   String(r.sucursal_entrega) !== String(r.sucursal_retiro);

        if (hayDropoffDistinto) {
            const chkDiff = document.getElementById("differentDropoffAdmin");
            const wrapEntrega = document.getElementById("dropoffWrapperAdmin");
            const selEntrega = document.getElementById("sucursal_entrega");

            if (chkDiff && !chkDiff.checked) {
                chkDiff.checked = true;
                chkDiff.dispatchEvent(new Event("change"));
            }
            if (wrapEntrega) wrapEntrega.style.display = "";
            if (selEntrega) {
                selEntrega.disabled = false;
                selEntrega.value = String(r.sucursal_entrega);
                selEntrega.dispatchEvent(new Event("change"));
            }
        }

        // ============================================================
        // 3) FECHAS y HORAS (se perdían: ahora se cargan vía Flatpickr / tp-hour)
        // ============================================================
        if (r.fecha_inicio) API.setFechaEdicion("#fecha_inicio_ui", "#fecha_inicio", r.fecha_inicio);
        if (r.fecha_fin)    API.setFechaEdicion("#fecha_fin_ui", "#fecha_fin", r.fecha_fin);
        if (r.hora_retiro)  API.setHoraEdicion("#hora_retiro_ui", "#hora_retiro", r.hora_retiro);
        if (r.hora_entrega) API.setHoraEdicion("#hora_entrega_ui", "#hora_entrega", r.hora_entrega);

        // ============================================================
        // 4) DATOS DEL CLIENTE
        // ============================================================
        const setVal = (id, val) => {
            const el = document.getElementById(id);
            if (el && val != null) el.value = val;
        };
        setVal("nombre_cliente", r.nombre_cliente);
        setVal("email_cliente", r.email_cliente);
        setVal("telefono_ui", r.telefono_cliente);
        setVal("telefono_cliente", r.telefono_cliente);
        setVal("comentarios", r.comentarios);
        setVal("no_vuelo", r.no_vuelo);

        // ============================================================
        // 5) CATEGORÍA (igual que antes, con su espera para que calcule)
        // ============================================================
        if (r.id_categoria) {
            const card = document.querySelector(`.card-pick[data-id="${r.id_categoria}"]`);
            if (card) {
                API.setCategoria({
                    id: card.dataset.id,
                    nombre: card.dataset.nombre,
                    desc: card.dataset.desc,
                    precio_dia: parseFloat(card.dataset.precio || 0),
                    precio_km: parseFloat(card.dataset.precioKm || 0),
                    capacidad_tanque: parseFloat(card.dataset.litros || 0)
                });
                await new Promise(res => setTimeout(res, 150));
            }
        }

        // ============================================================
        // 6) DELIVERY (igual que antes)
        // ============================================================
        if (r.delivery_activo == 1) {
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
            if (dir && r.delivery_direccion) dir.value = r.delivery_direccion;
        }

        // ============================================================
        // 7) SERVICIOS / ADICIONALES guardados
        //    - Gasolina (por_tanque, id 1): switch especial.
        //    - Drop Off (id 11): switch de ubicación.
        //    - El resto: cards dinámicas con su CHARGE REAL (leído del DOM).
        // ============================================================
        if (window.serviciosEditar?.length) {
            for (const s of window.serviciosEditar) {
                const idServ = Number(s.id_servicio);

                // Gasolina: card de tanque (id 1). Su cálculo es especial.
                if (idServ === 1) {
                    API.setGasolinaActive(true);
                    continue;
                }

                // Drop Off: card de ubicación (id 11).
                // Opción A: restaurar el total guardado (precio_unitario) tal cual,
                // sin recalcular por ubicación (no se guardó cuál era).
                if (idServ === 11) {
                    API.restaurarDropoffEdicion(s.precio_unitario || 0);
                    continue;
                }

                // Resto: intentar activarlo como card dinámica de addon.
                // activarAddonEdicion lee el charge/precio REAL desde la card,
                // así que por_dia se cobra por día y por_evento por evento.
                const ok = API.activarAddonEdicion(idServ, s.cantidad || 1);

                // Si por alguna razón no existe la card (servicio deshabilitado
                // en admin), lo metemos al estado con su precio guardado para no
                // perder el cobro, usando el charge que traiga (fallback evento).
                if (!ok) {
                    API.setAddonQty({
                        id: idServ,
                        nombre: s.nombre,
                        precio: s.precio_unitario,
                        charge: "por_evento"
                    }, s.cantidad || 1);
                }
            }
        }

        // ============================================================
        // 8) PROTECCIÓN (paquete)
        // ============================================================
        if (window.seguroEditar) {
            API.setProteccion({
                id: window.seguroEditar.id_paquete,
                nombre: window.seguroEditar.nombre,
                precio: window.seguroEditar.precio_por_dia,
                charge: "por_dia"
            });
        }

        // ============================================================
        // 9) Recalcular todo al final
        // ============================================================
        API.forceRecalc();
    }

    // La inicialización general corre con setTimeout(init, 500) tras DOMContentLoaded.
    // La carga de edición DEBE correr DESPUÉS de esa init (Flatpickr, time-pickers,
    // API y cards ya listos). Por eso esperamos un poco más (700ms > 500ms).
    document.addEventListener("DOMContentLoaded", () => {
        setTimeout(() => { __cargarEdicionReserva(); }, 700);
    });

})();
