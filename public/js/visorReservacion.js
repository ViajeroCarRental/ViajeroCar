document.addEventListener('DOMContentLoaded', () => {

    alertify.set('notifier', 'position', 'top-right');
    alertify.defaults.glossary.ok = 'Aceptar';
    alertify.defaults.glossary.cancel = 'Cancelar';

    let cambiosDetectados = false;

    function mostrarAlertaCard(idAlerta, mensaje = null) {
        const alerta = document.getElementById(idAlerta);
        if (!alerta) return;

        if (mensaje) {
            const textElement = alerta.querySelector('.alert-text strong');
            if (textElement) textElement.textContent = mensaje;
        }

        alerta.classList.remove('hidden');
        alerta.style.animation = 'none';
        void alerta.offsetWidth;
        alerta.style.animation = 'slideAlert 0.5s cubic-bezier(0.34, 1.56, 0.64, 1) forwards';

        clearTimeout(alerta._timeout);
        alerta._timeout = setTimeout(function() {
            alerta.style.animation = 'slideAlert 0.3s cubic-bezier(0.34, 1.56, 0.64, 1) reverse forwards';
            setTimeout(function() {
                alerta.classList.add('hidden');
                alerta.style.animation = '';
            }, 300);
        }, 10000);
    }

    function marcarCambios() {
        if (!cambiosDetectados) {
            cambiosDetectados = true;

            const btnConfirmarCambios = document.getElementById('btnConfirmarCambios');
            if (btnConfirmarCambios) {
                btnConfirmarCambios.disabled = false;
                btnConfirmarCambios.classList.remove('btn-secondary');
                btnConfirmarCambios.classList.add('btn-success');
            }

            mostrarAlertaCard('alertaCambiosDetectados');
        }
    }

    function recalcularTotales() {
        const tarifaBaseInput = document.getElementById('tarifaBaseReserva');
        const tarifaBase = tarifaBaseInput ? (parseFloat(tarifaBaseInput.value) || 0) : 0;

        let subtotalServicios = 0;

        document.querySelectorAll('#tablaServicios tr').forEach(fila => {
            const cantidadInput = fila.querySelector('input[name*="[cantidad]"]');
            const precioInput = fila.querySelector('input[name*="[precio]"]');

            if (!cantidadInput || !precioInput) return;

            const cantidad = parseFloat(cantidadInput.value) || 0;
            const precio = parseFloat(precioInput.value) || 0;

            const total = cantidad * precio;

            if (fila.children[3]) {
                fila.children[3].innerText = "$" + total.toFixed(2);
            }

            subtotalServicios += total;
        });

        const subtotal = tarifaBase + subtotalServicios;
        const iva = subtotal * 0.16;
        const total = subtotal + iva;

        document.querySelectorAll('.visor-total-item').forEach(item => {
            const texto = item.innerText || '';

            if (texto.includes('Subtotal:')) {
                item.innerHTML = "<strong>Subtotal:</strong> $" + subtotal.toFixed(2);
            } else if (texto.includes('IVA:')) {
                item.innerHTML = "<strong>IVA:</strong> $" + iva.toFixed(2);
            } else if (texto.includes('Total:')) {
                item.innerHTML = "<strong>Total:</strong> $" + total.toFixed(2);
            }
        });
    }

    document.querySelectorAll('input, select, textarea').forEach(el => {
        el.addEventListener('change', () => {
            marcarCambios();
            recalcularTotales();
        });
    });

    // CARD 1 – VEHÍCULO / SERVICIOS
    const btnEditarServicios = document.getElementById('btnEditarServicios');
    const btnGuardarCard1 = document.getElementById('btnGuardarCard1');
    const btnCambiarCategoria = document.getElementById('btnCambiarCategoria');
    const contenedorAgregarServicio = document.getElementById('contenedorAgregarServicio');
    const selectServicio = document.getElementById('selectServicio');
    const btnConfirmarAgregar = document.getElementById('btnConfirmarAgregar');
    const tablaServicios = document.getElementById('tablaServicios');

    let servicioIndex = tablaServicios ? tablaServicios.rows.length : 0;

    function actualizarFilaVacia() {
        if (!tablaServicios) return;

        const filaVacia = tablaServicios.querySelector('.fila-sin-servicios');
        const filasReales = tablaServicios.querySelectorAll('tr:not(.fila-sin-servicios)');

        if (filasReales.length === 0) {
            if (!filaVacia) {
                const tr = document.createElement('tr');
                tr.className = 'fila-sin-servicios';
                tr.innerHTML = '<td>—</td><td>—</td><td>—</td><td>—</td><td>—</td>';
                tablaServicios.appendChild(tr);
            }
        } else {
            if (filaVacia) filaVacia.remove();
        }
    }

    if (btnEditarServicios) {
        btnEditarServicios.addEventListener('click', () => {
            document.querySelectorAll('.editable-servicio').forEach(input => {
                input.removeAttribute('readonly');
                input.classList.add('border-warning');
            });

            btnGuardarCard1.classList.remove('d-none');
            contenedorAgregarServicio.classList.remove('d-none');
            btnCambiarCategoria.classList.remove('d-none');

            document.querySelectorAll('.btnEliminarServicio').forEach(btn => btn.classList.remove('d-none'));

            btnEditarServicios.classList.add('d-none');
        });
    }

    if (btnConfirmarAgregar) {
        btnConfirmarAgregar.addEventListener('click', () => {
            if (!selectServicio.value) {
                alertify.alert('Selecciona un servicio');
                return;
            }

            const option = selectServicio.selectedOptions[0];
            const id = option.value;
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
            actualizarFilaVacia();
            servicioIndex++;
            selectServicio.value = '';

            marcarCambios();
            recalcularTotales();
            mostrarAlertaCard('alertaServicioAgregado');
        });
    }

    document.addEventListener('click', e => {
        if (e.target.classList.contains('btnEliminarServicio')) {
            const fila = e.target.closest('tr');

            alertify.confirm(
                '¿Quitar servicio?',
                function() {
                    fila.remove();
                    actualizarFilaVacia();
                    marcarCambios();
                    recalcularTotales();
                    mostrarAlertaCard('alertaServicioEliminado', 'Servicio eliminado correctamente');
                },
                function() {}
            );
        }
    });

    // CATEGORÍA
    const modalCategoriaEl = document.getElementById('modalCategoria');

    if (btnCambiarCategoria && modalCategoriaEl) {
        btnCambiarCategoria.addEventListener('click', () => {
            new bootstrap.Modal(modalCategoriaEl).show();
        });
    }

    document.querySelectorAll('.elegirCategoria').forEach(btn => {
        btn.addEventListener('click', () => {
            const id = btn.dataset.id;
            const texto = btn.dataset.texto;
            const img = btn.dataset.img;

            document.getElementById('inputCategoria').value = id;
            document.getElementById('imgVehiculo').src = img;
            document.getElementById('textoCategoria').innerText = texto;

            bootstrap.Modal.getInstance(modalCategoriaEl).hide();

            marcarCambios();
            mostrarAlertaCard('alertaCategoriaCambiada');
        });
    });

    // CARD 2 – CLIENTE
    const btnEditarCliente = document.getElementById('btnEditarCliente');
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

    // CARD 3 – ITINERARIO
    const btnEditarItinerario = document.getElementById('btnEditarItinerario');
    const btnGuardarItinerario = document.getElementById('btnGuardarItinerario');

    document.querySelectorAll('.editable-itinerario').forEach(el => {
        if (el.tagName !== 'SELECT') el.setAttribute('readonly', true);
        if (el.tagName === 'SELECT') el.classList.add('bloqueado');
    });

    if (btnEditarItinerario) {
        btnEditarItinerario.addEventListener('click', () => {
            document.querySelectorAll('.editable-itinerario').forEach(el => {
                if (el.tagName !== 'SELECT') el.removeAttribute('readonly');
                if (el.tagName === 'SELECT') el.classList.remove('bloqueado');
                el.classList.add('border-warning');
            });

            btnGuardarItinerario.classList.remove('d-none');
            btnEditarItinerario.classList.add('d-none');
        });
    }

    // VALIDACIÓN FECHAS
    const formCard3 = document.querySelector('input[name="card"][value="card3"]')?.closest('form');

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
            const fin = new Date(`${ff}T${he}`);

            if (fin <= inicio) {
                e.preventDefault();
                alertBox.innerText = 'La fecha y hora de entrega deben ser posteriores a la de retiro.';
                alertBox.classList.remove('d-none');
            }
        });
    }

    // CONFIRMAR CAMBIOS – REENVIAR CORREO
    const btnConfirmarCambios = document.getElementById('btnConfirmarCambios');

    if (btnConfirmarCambios) {
        btnConfirmarCambios.addEventListener('click', function(e) {
            e.preventDefault();

            const form = this.closest('form');

            alertify.confirm(
                '¿Reenviar correo?',
                'Se te va a enviar nuevamente el correo con los datos actualizados de tu reservación. Revisa tu bandeja de entrada.',
                function() {
                    form.submit();
                },
                function() {}
            );
        });
    }

    // FLATPICKR – CALENDARIO / HORA
    if (window.flatpickr) {
        if (flatpickr.l10ns && flatpickr.l10ns.es) {
            flatpickr.localize(flatpickr.l10ns.es);
        }

        const inputFechaInicio = document.querySelector('.editable-itinerario[name="fecha_inicio"]');
        const inputFechaFin = document.querySelector('.editable-itinerario[name="fecha_fin"]');

        const vrPickersFecha = [];
        let fpInicio = null;
        let fpFin = null;

        const opcionesFecha = {
            dateFormat: 'Y-m-d',
            altInput: true,
            altFormat: 'd/M/Y',
            allowInput: false,
            disableMobile: true,
            onReady: (selectedDates, dateStr, instance) => {
                if (instance.altInput) {
                    instance.altInput.setAttribute('readonly', 'readonly');
                    instance.altInput.setAttribute('inputmode', 'none');
                    instance.altInput.setAttribute('tabindex', '-1');

                    instance.altInput.addEventListener('focus', function (e) {
                        e.target.blur();
                    });

                    instance.altInput.addEventListener('touchstart', function (e) {
                        e.target.blur();
                    });
                }
            },
            onOpen: (selectedDates, dateStr, instance) => {
                if (instance.altInput) instance.altInput.blur();
                if (document.activeElement) document.activeElement.blur();
            }
        };

        // Fecha de RETIRO: no permite días pasados
        if (inputFechaInicio) {
            fpInicio = flatpickr(inputFechaInicio, {
                ...opcionesFecha,
                minDate: 'today',
                clickOpens: !inputFechaInicio.hasAttribute('readonly'),
                onChange: (selectedDates, dateStr) => {
                    // La entrega no puede ser anterior al retiro
                    if (fpFin) {
                        fpFin.set('minDate', dateStr);

                        const fechaFin = fpFin.selectedDates[0];
                        const fechaInicio = selectedDates[0];

                        // Si la entrega quedó antes del nuevo retiro, la ajusta
                        if (fechaFin && fechaInicio && fechaFin < fechaInicio) {
                            fpFin.setDate(dateStr, true);
                        }
                    }

                    inputFechaInicio.dispatchEvent(new Event('change', { bubbles: true }));
                }
            });
            vrPickersFecha.push(fpInicio);
        }

        // Fecha de ENTREGA: no permite días anteriores al retiro
        if (inputFechaFin) {
            fpFin = flatpickr(inputFechaFin, {
                ...opcionesFecha,
                minDate: (inputFechaInicio && inputFechaInicio.value) ? inputFechaInicio.value : 'today',
                clickOpens: !inputFechaFin.hasAttribute('readonly'),
                onChange: () => {
                    inputFechaFin.dispatchEvent(new Event('change', { bubbles: true }));
                }
            });
            vrPickersFecha.push(fpFin);
        }

        const vrHoraInputs = document.querySelectorAll(
            '.editable-itinerario[name="hora_retiro"], .editable-itinerario[name="hora_entrega"]'
        );

        const vrPickersHora = [];

        vrHoraInputs.forEach(input => {
            const fp = flatpickr(input, {
                enableTime: true,
                noCalendar: true,
                dateFormat: 'H:i',
                time_24hr: true,
                hourIncrement: 1,
                minuteIncrement: 1,
                allowInput: false,
                disableMobile: true,
                clickOpens: !input.hasAttribute('readonly'),
                onChange: () => {
                    input.dispatchEvent(new Event('change', { bubbles: true }));
                }
            });
            vrPickersHora.push(fp);
        });

        const btnEditarItin = document.getElementById('btnEditarItinerario');

        if (btnEditarItin) {
            btnEditarItin.addEventListener('click', () => {
                vrPickersFecha.forEach(fp => fp.set('clickOpens', true));
            });
        }
    }

    // HORAS – DROPDOWN TIPO LISTA
    (function() {
        const horaInputs = document.querySelectorAll(
            '.editable-itinerario[name="hora_retiro"], .editable-itinerario[name="hora_entrega"]'
        );

        horaInputs.forEach(input => {
            if (!input || !input.parentNode) return;

            const name = input.getAttribute('name');
            const valor = (input.value || '').slice(0, 5);

            const select = document.createElement('select');
            select.name = name;
            select.className = 'form-select editable-itinerario';

            const ph = document.createElement('option');
            ph.value = '';
            ph.textContent = 'Hora';
            ph.disabled = true;
            select.appendChild(ph);

            for (let h = 0; h < 24; h++) {
                const hora = String(h).padStart(2, '0') + ':00';
                const opt = document.createElement('option');
                opt.value = hora;
                opt.textContent = hora;
                if (hora === valor) opt.selected = true;
                select.appendChild(opt);
            }

            if (valor && !select.querySelector(`option[value="${valor}"]`)) {
                const opt = document.createElement('option');
                opt.value = valor;
                opt.textContent = valor;
                opt.selected = true;
                select.appendChild(opt);
            }

            if (!valor) ph.selected = true;

            select.classList.add('bloqueado');

            select.addEventListener('change', () => {
                select.dispatchEvent(new Event('input', { bubbles: true }));
            });

            input.parentNode.replaceChild(select, input);

            const wrap = select.closest('.vr-float');
            if (wrap) {
                wrap.classList.add('vr-float-select', 'vr-always');
                wrap.classList.add('vr-float-icon');
            }
        });
    })();

    // DROPDOWN CUSTOM DE SUCURSALES
    (function() {
        const VR_ICONS = {
            avion: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17.8 19.2 16 11l3.5-3.5C21 6 21.5 4 21 3c-1-.5-3 0-4.5 1.5L13 8 4.8 6.2c-.5-.1-.9.1-1.1.5l-.3.5c-.2.5-.1 1 .3 1.3L9 12l-2 3H4l-1 1 3 2 2 3 1-1v-3l3-2 3.5 5.3c.3.4.8.5 1.3.3l.5-.2c.4-.3.6-.7.5-1.2z"/></svg>',
            bus: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M8 6v6"/><path d="M15 6v6"/><path d="M2 12h19.6"/><path d="M18 18h3s.5-1.7.8-2.8c.1-.4.2-.8.2-1.2V6a2 2 0 0 0-2-2H4a2 2 0 0 0-2 2v8c0 .4.1.8.2 1.2C2.5 16.3 3 18 3 18h3"/><circle cx="7" cy="18" r="2"/><circle cx="17" cy="18" r="2"/></svg>',
            edif: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="4" y="2" width="16" height="20" rx="2"/><path d="M9 22v-4h6v4"/><path d="M8 6h.01"/><path d="M16 6h.01"/><path d="M12 6h.01"/><path d="M8 10h.01"/><path d="M16 10h.01"/><path d="M12 10h.01"/><path d="M8 14h.01"/><path d="M16 14h.01"/><path d="M12 14h.01"/></svg>',
            pin: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 10c0 6-8 12-8 12s-8-6-8-12a8 8 0 0 1 16 0z"/><circle cx="12" cy="10" r="3"/></svg>',
            chevron: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 12 15 18 9"/></svg>'
        };

        function vrIconoPara(texto) {
            const n = (texto || '').toLowerCase();
            if (n.includes('aeropuerto')) return VR_ICONS.avion;
            if (n.includes('autobuses') || n.includes('terminal') || n.includes('central de autobuses')) return VR_ICONS.bus;
            if (n.includes('oficina') || n.includes('plaza')) return VR_ICONS.edif;
            return VR_ICONS.pin;
        }

        const selects = document.querySelectorAll(
            '.editable-itinerario[name="sucursal_retiro"], .editable-itinerario[name="sucursal_entrega"]'
        );

        selects.forEach(select => {
            if (!select || !select.parentNode) return;
            if (select.classList.contains('vr-select-hidden')) return;

            select.classList.add('vr-select-hidden');

            const box = document.createElement('div');
            box.className = 'vr-drop';

            const trigger = document.createElement('button');
            trigger.type = 'button';
            trigger.className = 'vr-drop-trigger';

            const triggerIcon = document.createElement('span');
            triggerIcon.className = 'vr-drop-ic';

            const triggerText = document.createElement('span');
            triggerText.className = 'vr-drop-text';

            const triggerChev = document.createElement('span');
            triggerChev.className = 'vr-drop-chev';
            triggerChev.innerHTML = VR_ICONS.chevron;

            trigger.appendChild(triggerIcon);
            trigger.appendChild(triggerText);
            trigger.appendChild(triggerChev);

            const panel = document.createElement('div');
            panel.className = 'vr-drop-panel';

            function construirPanel() {
                panel.innerHTML = '';

                const grupos = select.querySelectorAll('optgroup');

                const armarOpcion = (opt) => {
                    const item = document.createElement('div');
                    item.className = 'vr-drop-item';
                    item.dataset.value = opt.value;

                    const ic = document.createElement('span');
                    ic.className = 'vr-drop-item-ic';
                    ic.innerHTML = vrIconoPara(opt.textContent);

                    const tx = document.createElement('span');
                    tx.textContent = opt.textContent.trim();

                    item.appendChild(ic);
                    item.appendChild(tx);

                    if (opt.value === select.value) item.classList.add('is-selected');

                    item.addEventListener('click', () => {
                        select.value = opt.value;
                        select.dispatchEvent(new Event('change', { bubbles: true }));
                        sincronizarTrigger();
                        panel.querySelectorAll('.vr-drop-item').forEach(i => i.classList.remove('is-selected'));
                        item.classList.add('is-selected');
                        cerrar();
                    });

                    return item;
                };

                if (grupos.length) {
                    grupos.forEach(g => {
                        const head = document.createElement('div');
                        head.className = 'vr-drop-group';
                        head.innerHTML = `${VR_ICONS.pin}<span>${g.label}</span>`;
                        panel.appendChild(head);

                        g.querySelectorAll('option').forEach(opt => {
                            panel.appendChild(armarOpcion(opt));
                        });
                    });
                } else {
                    select.querySelectorAll('option').forEach(opt => {
                        if (opt.value === '') return;
                        panel.appendChild(armarOpcion(opt));
                    });
                }
            }

            function sincronizarTrigger() {
                const opt = select.options[select.selectedIndex];
                const texto = opt ? opt.textContent.trim() : 'Selecciona';
                triggerText.textContent = texto;
                triggerIcon.innerHTML = vrIconoPara(texto);
            }

            function estaBloqueado() {
                return select.classList.contains('bloqueado');
            }

            function posicionarPanel() {
                const r = trigger.getBoundingClientRect();
                panel.style.top = (r.bottom + 6) + 'px';
                panel.style.left = r.left + 'px';
                panel.style.width = r.width + 'px';

                const espacioAbajo = window.innerHeight - r.bottom;
                const alto = panel.offsetHeight || 300;
                if (espacioAbajo < alto + 12 && r.top > alto) {
                    panel.style.top = (r.top - alto - 6) + 'px';
                }
            }

            function abrir() {
                if (estaBloqueado()) return;
                construirPanel();
                box.classList.add('open');
                panel.classList.add('open');
                posicionarPanel();
            }

            function cerrar() {
                box.classList.remove('open');
                panel.classList.remove('open');
            }

            function toggle() {
                box.classList.contains('open') ? cerrar() : abrir();
            }

            trigger.addEventListener('click', (e) => {
                e.stopPropagation();
                toggle();
            });

            document.addEventListener('click', (e) => {
                if (!box.contains(e.target) && !panel.contains(e.target)) cerrar();
            });

            select.parentNode.insertBefore(box, select.nextSibling);
            box.appendChild(trigger);

            document.body.appendChild(panel);

            sincronizarTrigger();

            select.addEventListener('change', sincronizarTrigger);

            window.addEventListener('scroll', () => {
                if (box.classList.contains('open')) posicionarPanel();
            }, true);

            window.addEventListener('resize', () => {
                if (box.classList.contains('open')) posicionarPanel();
            });
        });
    })();

    // OVERLAY DE ACTUALIZACIÓN
    const vrOverlay = document.getElementById('vrUpdateOverlay');
    const vrMsgEl = document.getElementById('vrUpdateMsg');

    const vrMensajes = [
        'Procesando tu información…',
        'Preparando los cambios…',
        'Generando tu actualización…'
    ];
    let vrMsgTimer = null;

    function vrRotarMensajes() {
        let i = 0;
        if (vrMsgEl) vrMsgEl.textContent = vrMensajes[0];
        clearInterval(vrMsgTimer);
        vrMsgTimer = setInterval(() => {
            i = (i + 1) % vrMensajes.length;
            if (vrMsgEl) {
                vrMsgEl.textContent = vrMensajes[i];
                vrMsgEl.style.animation = 'none';
                void vrMsgEl.offsetWidth;
                vrMsgEl.style.animation = '';
            }
        }, 1500);
    }

    function vrShowOverlay(state) {
        if (!vrOverlay) return;
        vrOverlay.classList.remove('is-loading', 'is-success');
        vrOverlay.classList.add('show', state === 'success' ? 'is-success' : 'is-loading');
        vrOverlay.setAttribute('aria-hidden', 'false');
        document.body.style.overflow = 'hidden';

        if (state === 'success') {
            clearInterval(vrMsgTimer);
        } else {
            vrRotarMensajes();
        }
    }

    function vrHideOverlay() {
        if (!vrOverlay) return;
        clearInterval(vrMsgTimer);
        vrOverlay.classList.remove('show');
        vrOverlay.setAttribute('aria-hidden', 'true');
        document.body.style.overflow = '';
    }

    if (vrOverlay) {
        vrOverlay.addEventListener('click', () => {
            if (vrOverlay.classList.contains('is-success')) vrHideOverlay();
        });
    }

    ['card1', 'card2', 'card3'].forEach(cardVal => {
        const form = document.querySelector(`input[name="card"][value="${cardVal}"]`)?.closest('form');

        if (!form) return;

        form.addEventListener('submit', function(e) {
            if (e.defaultPrevented) return;
            if (form.dataset.vrSending) return;

            e.preventDefault();
            form.dataset.vrSending = '1';

            try { sessionStorage.setItem('vrUpdated', '1'); } catch (_) {}

            vrShowOverlay('loading');

            setTimeout(() => form.submit(), 800);

            setTimeout(() => {
                if (vrOverlay && vrOverlay.classList.contains('is-loading')) vrHideOverlay();
            }, 12000);
        });
    });

    const vrServerSuccess = !!document.querySelector('.visor-alert.alert-success');
    let vrWasUpdating = false;
    try {
        vrWasUpdating = sessionStorage.getItem('vrUpdated') === '1';
        sessionStorage.removeItem('vrUpdated');
    } catch (_) {}

    if (vrServerSuccess && vrWasUpdating) {
        vrShowOverlay('success');
        setTimeout(vrHideOverlay, 1200);
    }

    // AUTO-OCULTAR ALERTAS
    document.querySelectorAll('.visor-alert:not(.hidden)').forEach(function(alerta) {
        setTimeout(function() {
            alerta.style.animation = 'slideAlert 0.3s cubic-bezier(0.34, 1.56, 0.64, 1) reverse forwards';
            setTimeout(function() {
                alerta.classList.add('hidden');
                alerta.style.animation = '';
            }, 300);
        }, 10000);
    });

});
