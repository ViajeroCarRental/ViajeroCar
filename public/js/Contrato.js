// Contrato
// Pasos 1 al 3

document.addEventListener("DOMContentLoaded", () => {
    console.log("✅ DOM listo, iniciando navegación de pasos (1-3)...");

    // ================================ UTILIDADES ===============================
    const debounce = (func, delay = 300) => {
        let timer;
        return (...args) => {
            clearTimeout(timer);
            timer = setTimeout(() => func.apply(this, args), delay);
        };
    };

    // Helper para formatear fechas de input (YYYY-MM-DDTHH:mm)
    const formatFechaInput = (dateObj) => {
        const yyyy = dateObj.getFullYear();
        const mm = String(dateObj.getMonth() + 1).padStart(2, '0');
        const dd = String(dateObj.getDate()).padStart(2, '0');
        const hh = String(dateObj.getHours()).padStart(2, '0');
        const min = String(dateObj.getMinutes()).padStart(2, '0');
        return `${yyyy}-${mm}-${dd}T${hh}:${min}`;
    };

    // Helper estandarizado para seleccionar elementos del DOM
    const $el = (selector) => document.querySelector(selector);
    const $elId = (id) => document.getElementById(id);

    // ================================ ESTADO Y MÓDULOS ===============================

    const mesesArr = ['ENE', 'FEB', 'MAR', 'ABR', 'MAY', 'JUN', 'JUL', 'AGO', 'SEP', 'OCT', 'NOV', 'DIC'];

    const ContratoState = {
        intervaloAprobacion: null,
        deliveryTotalActual: 0,
        dropoffTotal: 0,
        gasolinaTotal: 0
    };

    const setState = (key, value) => {
        ContratoState[key] = value;
        return value;
    };

    const ContratoUI = {
        money(value) {
            const amount = parseFloat(value || 0);
            return window.money ? window.money(amount) : `$${amount.toFixed(2)} MXN`;
        },
        notify(type, message) {
            // NOTIFICACIONES DESACTIVADAS - solo console
            console.log(`[${type}]`, message);
            return;
        },
        setText(element, value) {
            if (element) element.textContent = value;
        },
        toggle(element, visible) {
            if (element) element.style.display = visible ? "block" : "none";
        }
    };

    const ContratoAPI = {
        async request(url, options = {}) {
            const isFormData = options.body instanceof FormData;
            const headers = {
                Accept: "application/json",
                "X-CSRF-TOKEN": window.csrfToken,
                ...(isFormData ? {} : options.body !== undefined ? { "Content-Type": "application/json" } : {}),
                ...(options.headers || {})
            };

            try {
                const response = await fetch(url, {
                    ...options,
                    headers,
                    body: isFormData ? options.body : (options.body !== undefined ? JSON.stringify(options.body) : undefined)
                });

                if (!response.ok) throw new Error(`HTTP ${response.status} en ${url}`);

                const contentType = response.headers.get("content-type") || "";
                return contentType.includes("application/json") ? response.json() : response.text();
            } catch (error) {
                if (error instanceof TypeError && error.message === "Failed to fetch") {
                    throw new Error("Servidor no disponible (Conexión rechazada)");
                }
                throw error;
            }
        },
        getJSON(url) { return this.request(url); },
        postJSON(url, body) { return this.request(url, { method: "POST", body }); },
        deleteJSON(url, body) { return this.request(url, { method: "DELETE", body }); }
    };

    // ================================ PASO 1: FECHAS ===============================

    (function inicializarPaso1() {
        function validarYPintarFechas() {
            const inputE = $elId('inputOcultoEntrega');
            const inputD = $elId('inputOcultoDevolucion');

            if (!inputE || !inputD || !inputE.value || !inputD.value) return;

            let dateE = new Date(inputE.value);
            let dateD = new Date(inputD.value);
            const hoy = new Date();

            const esHoy = (dateE.getFullYear() === hoy.getFullYear() &&
                dateE.getMonth() === hoy.getMonth() &&
                dateE.getDate() === hoy.getDate());

            dateE.setHours(esHoy ? hoy.getHours() : 12);
            dateE.setMinutes(esHoy ? hoy.getMinutes() : 0);

            let warning = false;

            const soloFechaE = new Date(dateE.getFullYear(), dateE.getMonth(), dateE.getDate());
            const soloFechaD = new Date(dateD.getFullYear(), dateD.getMonth(), dateD.getDate());

            if (soloFechaD < soloFechaE) {
                dateD = new Date(dateE);
                dateD.setDate(dateE.getDate() + 1);
                warning = true;
            } else if (soloFechaD.getTime() === soloFechaE.getTime() && dateD <= dateE) {
                dateD = new Date(dateE);
                dateD.setHours(dateE.getHours() + 1);
                warning = true;
            }

            if (dateD.toDateString() !== dateE.toDateString()) {
                dateD.setHours(12, 0, 0, 0);
            }

            const ui = {
                valE: formatFechaInput(dateE),
                valD: formatFechaInput(dateD),
                entrega: {
                    dia: String(dateE.getDate()).padStart(2, '0'),
                    mes: mesesArr[dateE.getMonth()],
                    anio: dateE.getFullYear(),
                    hora: `${String(dateE.getHours() % 12 || 12).padStart(2, '0')}:${String(dateE.getMinutes()).padStart(2, '0')} ${dateE.getHours() >= 12 ? 'PM' : 'AM'}`
                },
                devolucion: {
                    dia: String(dateD.getDate()).padStart(2, '0'),
                    mes: mesesArr[dateD.getMonth()],
                    anio: dateD.getFullYear(),
                    hora: `${String(dateD.getHours() % 12 || 12).padStart(2, '0')}:${String(dateD.getMinutes()).padStart(2, '0')} ${dateD.getHours() >= 12 ? 'PM' : 'AM'}`
                }
            };

            inputE.value = ui.valE;
            inputD.value = ui.valD;

            window.requestAnimationFrame(() => {
                if (warning) {
                    console.warn("Fecha ajustada: La devolución debe ser posterior a la entrega.");
                }

                ContratoUI.setText($elId('txtDiaEntrega'), ui.entrega.dia);
                ContratoUI.setText($elId('txtMesEntrega'), ui.entrega.mes);
                ContratoUI.setText($elId('txtAnioEntrega'), ui.entrega.anio);
                ContratoUI.setText($elId('txtHoraEntrega'), ui.entrega.hora);

                ContratoUI.setText($elId('txtDiaDevolucion'), ui.devolucion.dia);
                ContratoUI.setText($elId('txtMesDevolucion'), ui.devolucion.mes);
                ContratoUI.setText($elId('txtAnioDevolucion'), ui.devolucion.anio);
                ContratoUI.setText($elId('txtHoraDevolucion'), ui.devolucion.hora);
            });
        }

        function iniciarMonitoreoAprobacion() {
            const solicitud = JSON.parse(sessionStorage.getItem("solicitudCambio") || "{}");
            if (!solicitud.activa) return;

            if (ContratoState.intervaloAprobacion) clearInterval(ContratoState.intervaloAprobacion);

            ContratoState.intervaloAprobacion = setInterval(async () => {
                try {
                    const data = await ContratoAPI.getJSON(`/admin/contrato/cambio-fecha/estado/${solicitud.id_reservacion}`);

                    if (data.estado === "aprobado" || data.estado === "rechazado") {
                        clearInterval(ContratoState.intervaloAprobacion);
                        ContratoState.intervaloAprobacion = null;
                        sessionStorage.removeItem("solicitudCambio");

                        if (data.estado === "aprobado") {
                            const inputE = $elId("inputOcultoEntrega");
                            if (inputE) inputE.value = `${solicitud.f}T${solicitud.h}`;
                            validarYPintarFechas();
                            if (typeof window.actualizarFechasYRecalcular === 'function') await window.actualizarFechasYRecalcular();
                            console.log("✅ Cambio de fecha aprobado.");
                        } else {
                            console.log("❌ Solicitud de cambio rechazada.");
                        }
                    }
                } catch (err) {
                    if (err.message.includes('Failed to fetch') || err.name === 'TypeError') {
                        console.warn("⚠️ El servidor no responde. Reintentando monitoreo en 8 segundos...");
                    } else {
                        console.error("Error en monitoreo:", err);
                    }
                }
            }, 8000);
        }

        if (JSON.parse(sessionStorage.getItem("solicitudCambio") || "{}").activa) iniciarMonitoreoAprobacion();

        function vincularCalendario(inputId) {
            const input = $elId(inputId);
            if (!input) return;

            let fechaOriginal = input.value;

            input.addEventListener('click', (e) => {
                e.preventDefault();
                fechaOriginal = input.value;
                try { input.showPicker(); } catch (error) { input.focus(); }
            });

            input.addEventListener('change', (e) => {
                const nuevaFechaFull = e.target.value;

                if (!nuevaFechaFull || nuevaFechaFull === fechaOriginal) return;

                if (inputId === 'inputOcultoEntrega') {
                    const [f, h] = nuevaFechaFull.split('T');

                    // ✅ MODAL DE CONFIRMACIÓN RESTAURADO
                    if (typeof alertify !== 'undefined') {
                        alertify.confirm(
                            "⚠️ Requiere Autorización",
                            "Cambiar la fecha de Entrega requiere autorización de un supervisor. ¿Deseas enviar la solicitud de cambio?",
                            async () => {
                                try {
                                    const data = await ContratoAPI.postJSON("/admin/contrato/solicitar-cambio-fecha", {
                                        id_reservacion: window.ID_RESERVACION,
                                        nueva_fecha: f,
                                        nueva_hora: h,
                                        motivo: "Modificación en mostrador"
                                    });

                                    console.log("Solicitud enviada:", data.msg || "Esperando aprobación...");
                                    sessionStorage.setItem("solicitudCambio", JSON.stringify({
                                        activa: true,
                                        id_reservacion: window.ID_RESERVACION,
                                        f, h
                                    }));

                                    if (typeof iniciarMonitoreoAprobacion === 'function') {
                                        iniciarMonitoreoAprobacion();
                                    }
                                } catch (err) {
                                    console.error("Error enviando solicitud:", err);
                                }
                            },
                            () => {
                                input.value = fechaOriginal;
                            }
                        ).set('labels', { ok: 'Enviar Solicitud', cancel: 'Cancelar' });
                    } else {
                        // Fallback si alertify no está disponible
                        console.log("⚠️ Cambio de fecha requiere autorización");
                        try {
                            ContratoAPI.postJSON("/admin/contrato/solicitar-cambio-fecha", {
                                id_reservacion: window.ID_RESERVACION,
                                nueva_fecha: f,
                                nueva_hora: h,
                                motivo: "Modificación en mostrador"
                            }).then(data => {
                                console.log("Solicitud enviada:", data.msg || "Esperando aprobación...");
                                sessionStorage.setItem("solicitudCambio", JSON.stringify({
                                    activa: true,
                                    id_reservacion: window.ID_RESERVACION,
                                    f, h
                                }));

                                if (typeof iniciarMonitoreoAprobacion === 'function') {
                                    iniciarMonitoreoAprobacion();
                                }
                            }).catch(err => {
                                console.error("Error enviando solicitud:", err);
                            });
                        } catch (err) {
                            console.error("Error enviando solicitud:", err);
                        }
                    }

                    e.target.value = fechaOriginal;
                    return;
                }

                validarYPintarFechas();
                if (typeof window.actualizarFechasYRecalcular === 'function') {
                    window.actualizarFechasYRecalcular();
                }
            });
        }

        vincularCalendario('inputOcultoEntrega');
        vincularCalendario('inputOcultoDevolucion');
        validarYPintarFechas();

        $el("#btnElegirVehiculo")?.addEventListener("click", () => {
            if (typeof window.abrirModalVehiculos === 'function') window.abrirModalVehiculos();
        });
    })();

  // ================================ CALENDARIOS FLATPICKR ===============================

    const FP_MESES_P1 = ["Enero", "Febrero", "Marzo", "Abril", "Mayo", "Junio",
        "Julio", "Agosto", "Septiembre", "Octubre", "Noviembre", "Diciembre"];

    function crearSelectAniosP1(fp, desde, hasta) {
        const wrapper = fp.calendarContainer.querySelector(".numInputWrapper");
        if (!wrapper || fp.calendarContainer.querySelector(".fp-year-select")) return;

        const select = document.createElement("select");
        select.className = "fp-year-select";
        for (let a = hasta; a >= desde; a--) {
            const op = document.createElement("option");
            op.value = a;
            op.textContent = a;
            select.appendChild(op);
        }
        select.value = fp.currentYear;
        select.addEventListener("change", (e) => {
            e.stopPropagation();
            fp.changeYear(parseInt(e.target.value, 10));
        });
        wrapper.parentNode.insertBefore(select, wrapper);
        wrapper.remove();
    }

    function crearPanelMesesP1(fp) {
        if (fp.calendarContainer.querySelector(".fp-meses-panel")) return;

        const panel = document.createElement("div");
        panel.className = "fp-meses-panel";

        FP_MESES_P1.forEach((nombre, i) => {
            const btn = document.createElement("button");
            btn.type = "button";
            btn.className = "fp-mes-btn";
            btn.dataset.mes = i;
            btn.textContent = nombre.substring(0, 3);
            btn.title = nombre;
            btn.addEventListener("click", (e) => {
                e.preventDefault();
                e.stopPropagation();
                fp.changeMonth(i, false);
                cerrarPanelMesesP1(fp);
            });
            panel.appendChild(btn);
        });

        fp.calendarContainer.appendChild(panel);

        const dd = fp.calendarContainer.querySelector(".flatpickr-monthDropdown-months");
        if (dd) {
            const trigger = document.createElement("button");
            trigger.type = "button";
            trigger.className = "fp-mes-trigger";
            trigger.innerHTML = '<span></span> <i>&#9662;</i>';
            dd.parentNode.insertBefore(trigger, dd);
            dd.style.display = "none";
            trigger.addEventListener("click", (e) => {
                e.preventDefault();
                e.stopPropagation();
                if (panel.classList.contains("abierto")) {
                    cerrarPanelMesesP1(fp);
                } else {
                    abrirPanelMesesP1(fp);
                }
            });
        }

        actualizarTriggerMesP1(fp);
    }

    function abrirPanelMesesP1(fp) {
        const panel = fp.calendarContainer.querySelector(".fp-meses-panel");
        if (!panel) return;
        panel.querySelectorAll(".fp-mes-btn").forEach(b => {
            b.classList.toggle("activo", parseInt(b.dataset.mes, 10) === fp.currentMonth);
        });
        panel.classList.add("abierto");
    }

    function cerrarPanelMesesP1(fp) {
        const panel = fp.calendarContainer.querySelector(".fp-meses-panel");
        if (panel) panel.classList.remove("abierto");
    }

    function actualizarTriggerMesP1(fp) {
        const span = fp.calendarContainer.querySelector(".fp-mes-trigger span");
        if (span) span.textContent = FP_MESES_P1[fp.currentMonth];
    }

    (function inicializarCalendarios() {
        const pickerE = document.getElementById('pickerEntrega');
        const pickerD = document.getElementById('pickerDevolucion');

        if (!pickerE || !pickerD) {
            console.warn('⚠️ No se encontraron los inputs de flatpickr');
            return;
        }

        console.log('✅ Inicializando calendarios Flatpickr...');

        function horaDesdeValor(valor, fallback) {
            const m = String(valor || '').match(/(\d{1,2}):(\d{2})/);
            if (!m) return fallback;
            return String(m[1]).padStart(2, '0') + ':' + String(m[2]).padStart(2, '0');
        }

        function horaInicial(divId, picker) {
            const div = document.getElementById(divId);
            const delDiv = div ? horaDesdeValor(div.textContent, null) : null;
            if (delDiv) return delDiv;
            return horaDesdeValor(picker.value, '08:00');
        }

        let horaEntrega = horaInicial('txtHoraEntrega', pickerE);
        let horaDevolucion = horaInicial('txtHoraDevolucion', pickerD);

        function combinar(fecha, hora) {
            const partes = hora.split(':');
            const d = new Date(fecha);
            d.setHours(parseInt(partes[0], 10), parseInt(partes[1], 10) || 0, 0, 0);
            return d;
        }

        // Configuración común
        const commonOptions = {
            enableTime: false,
            dateFormat: "Y-m-d",
            locale: 'es',
            disableMobile: true,
            static: false,
            allowInput: false,
            monthSelectorType: "dropdown",

            onReady: function (sel, str, fp) {
                fp.calendarContainer.classList.add("fp-centrado");
                crearSelectAniosP1(fp, new Date().getFullYear() - 1, new Date().getFullYear() + 5);
                crearPanelMesesP1(fp);
            },

            onOpen: function (sel, str, fp) {
                document.body.classList.add("fp-modal-abierto");
                const cal = fp.calendarContainer;
                cal.style.top = "";
                cal.style.left = "";
                cal.style.width = "";
                cerrarPanelMesesP1(fp);
                const s = cal.querySelector(".fp-year-select");
                if (s) s.value = fp.currentYear;
                actualizarTriggerMesP1(fp);
            },

            onClose: function (sel, str, fp) {
                document.body.classList.remove("fp-modal-abierto");
                cerrarPanelMesesP1(fp);
            },

            onMonthChange: function (sel, str, fp) {
                actualizarTriggerMesP1(fp);
            },

            onYearChange: function (sel, str, fp) {
                const s = fp.calendarContainer.querySelector(".fp-year-select");
                if (s) s.value = fp.currentYear;
                actualizarTriggerMesP1(fp);
            }
        };

        let originalValueE = pickerE.value;
        let originalValueD = pickerD.value;

        // =============================================
        // PICKER ENTREGA
        // =============================================
        const fpEntrega = flatpickr(pickerE, {
            ...commonOptions,
            minDate: "today",
            onChange: function (selectedDates, dateStr, instance) {
                if (!selectedDates.length) return;

                const fechaSeleccionada = combinar(selectedDates[0], horaEntrega);
                const fechaOriginal = new Date(originalValueE.replace(' ', 'T'));

                if (fechaSeleccionada.toDateString() !== fechaOriginal.toDateString()) {
                    // ✅ MODAL DE CONFIRMACIÓN RESTAURADO
                    if (typeof alertify !== 'undefined') {
                        alertify.confirm(
                            "⚠️ Requiere Autorización",
                            "Cambiar la fecha de Entrega requiere autorización de un supervisor. ¿Deseas enviar la solicitud de cambio?",
                            async () => {
                                try {
                                    const f = fechaSeleccionada.toISOString().split('T')[0];
                                    const h = `${String(fechaSeleccionada.getHours()).padStart(2, '0')}:${String(fechaSeleccionada.getMinutes()).padStart(2, '0')}`;

                                    await fetch('/admin/contrato/solicitar-cambio-fecha', {
                                        method: 'POST',
                                        headers: {
                                            'Content-Type': 'application/json',
                                            'X-CSRF-TOKEN': window.csrfToken
                                        },
                                        body: JSON.stringify({
                                            id_reservacion: window.ID_RESERVACION,
                                            nueva_fecha: f,
                                            nueva_hora: h
                                        })
                                    });
                                    console.log("Solicitud enviada.");
                                } catch (e) {
                                    instance.setDate(fechaOriginal, false);
                                }
                            },
                            () => {
                                instance.setDate(fechaOriginal, false);
                            }
                        );
                    } else {
                        console.log("⚠️ Cambio de fecha requiere autorización");
                        try {
                            const f = fechaSeleccionada.toISOString().split('T')[0];
                            const h = `${String(fechaSeleccionada.getHours()).padStart(2, '0')}:${String(fechaSeleccionada.getMinutes()).padStart(2, '0')}`;

                            fetch('/admin/contrato/solicitar-cambio-fecha', {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'X-CSRF-TOKEN': window.csrfToken
                                },
                                body: JSON.stringify({
                                    id_reservacion: window.ID_RESERVACION,
                                    nueva_fecha: f,
                                    nueva_hora: h
                                })
                            }).then(() => console.log("Solicitud enviada."));
                        } catch (e) {
                            instance.setDate(fechaOriginal, false);
                        }
                    }
                } else {
                    originalValueE = instance.formatDate(fechaSeleccionada, "Y-m-d H:i");
                    pickerE.value = originalValueE;
                    actualizarUIFechas(fechaSeleccionada, 'entrega');

                    sincronizarMinimoDevolucion(fechaSeleccionada);

                    if (window.actualizarFechasYRecalcular) {
                        window.actualizarFechasYRecalcular();
                    }
                }
            }
        });

        // =============================================
        // PICKER DEVOLUCIÓN
        // =============================================
        const fpDevolucion = flatpickr(pickerD, {
            ...commonOptions,
            minDate: "today",
            onChange: function (selectedDates, dateStr, instance) {
                if (!selectedDates.length) return;

                const fechaEntrega = fpEntrega.selectedDates[0];
                let fechaDevolucion = combinar(selectedDates[0], horaDevolucion);

                if (fechaEntrega && fechaDevolucion < fechaEntrega) {
                    fechaDevolucion = new Date(fechaEntrega);
                    fechaDevolucion.setDate(fechaEntrega.getDate() + 1);
                    instance.setDate(fechaDevolucion, false);
                    console.warn('Fecha de devolución ajustada automáticamente.');
                }

                actualizarUIFechas(fechaDevolucion, 'devolucion');
                originalValueD = instance.formatDate(fechaDevolucion, "Y-m-d H:i");
                pickerD.value = originalValueD;

                if (window.actualizarFechasYRecalcular) {
                    window.actualizarFechasYRecalcular();
                }
            }
        });

        // =============================================
        // MÍNIMO DE LA DEVOLUCIÓN
        // Bloquea (en gris) el día de la entrega y todos los anteriores.
        // =============================================
        function sincronizarMinimoDevolucion(fechaEntrega) {
            if (!fechaEntrega) return;
            const minDev = new Date(fechaEntrega);
            minDev.setHours(0, 0, 0, 0);
            minDev.setDate(minDev.getDate() + 1);
            fpDevolucion.set('minDate', minDev);

            const actual = fpDevolucion.selectedDates[0];
            if (actual && actual < minDev) {
                fpDevolucion.setDate(minDev, false);
                actualizarUIFechas(minDev, 'devolucion');
                originalValueD = fpDevolucion.formatDate(minDev, "Y-m-d H:i");
                pickerD.value = originalValueD;
            }
        }

        sincronizarMinimoDevolucion(fpEntrega.selectedDates[0]);

        // =============================================
        // HELPER: inserta una opción en el select si no existe
        // (para horas con minutos fuera del intervalo de 5)
        // =============================================
        function asegurarOpcion(select, val) {
            if (!select || !val) return;
            if ([...select.options].some(o => o.value === val)) return;
            const op = document.createElement('option');
            op.value = val;
            op.textContent = `${val} HRS`;
            select.appendChild(op);
            [...select.options]
                .sort((a, b) => a.value.localeCompare(b.value))
                .forEach(o => select.appendChild(o));
        }

        // =============================================
        // FUNCIÓN PARA ACTUALIZAR LA UI DE FECHAS
        // =============================================
        function actualizarUIFechas(date, tipo) {
            if (!date) return;

            const prefix = tipo === 'entrega' ? 'Entrega' : 'Devolucion';

            document.getElementById(`txtDia${prefix}`).textContent = String(date.getDate()).padStart(2, '0');
            document.getElementById(`txtMes${prefix}`).textContent = date.toLocaleString('es', { month: 'short' }).toUpperCase();
            document.getElementById(`txtAnio${prefix}`).textContent = date.getFullYear();

            const cont = document.getElementById(`txtHora${prefix}`);
            const sel = cont ? cont.querySelector('.hora-select') : null;
            if (sel) {
                const val = `${String(date.getHours()).padStart(2, '0')}:${String(date.getMinutes()).padStart(2, '0')}`;
                asegurarOpcion(sel, val);
                sel.value = val;
            }
        }

        // =============================================
        // SELECT DE HORA (00:00 a 23:00)
        // =============================================
        function montarSelectHora(tipo) {
            const prefix = tipo === 'entrega' ? 'Entrega' : 'Devolucion';
            const div = document.getElementById(`txtHora${prefix}`);
            if (!div || div.querySelector('.hora-select')) return;

            const select = document.createElement('select');
            select.className = 'hora-select';

            for (let h = 0; h < 24; h++) {
                for (let m = 0; m < 60; m += 5) {
                    const val = `${String(h).padStart(2, '0')}:${String(m).padStart(2, '0')}`;
                    const op = document.createElement('option');
                    op.value = val;
                    op.textContent = `${val} HRS`;
                    select.appendChild(op);
                }
            }

            const horaActual = tipo === 'entrega' ? horaEntrega : horaDevolucion;
            asegurarOpcion(select, horaActual);
            select.value = horaActual;

            select.addEventListener('click', e => e.stopPropagation());
            select.addEventListener('mousedown', e => e.stopPropagation());

            select.addEventListener('change', (e) => {
                e.stopPropagation();
                const nueva = e.target.value;
                const fp = tipo === 'entrega' ? fpEntrega : fpDevolucion;
                const base = fp.selectedDates[0] || new Date();

                if (tipo === 'entrega') horaEntrega = nueva;
                else horaDevolucion = nueva;

                const combinada = combinar(base, nueva);
                fp.setDate(combinada, false);

                const picker = tipo === 'entrega' ? pickerE : pickerD;
                picker.value = fp.formatDate(combinada, "Y-m-d H:i");

                if (tipo === 'entrega') originalValueE = picker.value;
                else originalValueD = picker.value;

                if (window.actualizarFechasYRecalcular) {
                    window.actualizarFechasYRecalcular();
                }
            });

            div.textContent = '';
            div.appendChild(select);
        }

        // =============================================
        // EVENTOS PARA ABRIR EL CALENDARIO AL HACER CLICK EN LAS TARJETAS
        // =============================================
        const tarjetaEntrega = document.getElementById('tarjetaEntrega');
        const tarjetaDevolucion = document.getElementById('tarjetaDevolucion');

        if (tarjetaEntrega) {
            tarjetaEntrega.addEventListener('click', function (e) {
                if (e.target.closest('.flatpickr-input')) return;
                if (e.target.closest('.hora-select')) return;

                const ahora = new Date();
                const fechaSeleccionada = fpEntrega.selectedDates[0];

                if (fechaSeleccionada && fechaSeleccionada.toDateString() === ahora.toDateString()) {
                    horaEntrega = `${String(ahora.getHours()).padStart(2, '0')}:${String(ahora.getMinutes()).padStart(2, '0')}`;
                    const combinada = combinar(ahora, horaEntrega);
                    fpEntrega.setDate(combinada, false);
                    pickerE.value = fpEntrega.formatDate(combinada, "Y-m-d H:i");
                    originalValueE = pickerE.value;
                    actualizarUIFechas(combinada, 'entrega');
                }

                fpEntrega.open();
            });
        }

        if (tarjetaDevolucion) {
            tarjetaDevolucion.addEventListener('click', function (e) {
                if (e.target.closest('.flatpickr-input')) return;
                if (e.target.closest('.hora-select')) return;
                fpDevolucion.open();
            });
        }

        // =============================================
        // MONTAR SELECTS Y ACTUALIZAR UI INICIAL
        // =============================================
        montarSelectHora('entrega');
        montarSelectHora('devolucion');

        if (fpEntrega.selectedDates.length > 0) {
            const iniE = combinar(fpEntrega.selectedDates[0], horaEntrega);
            pickerE.value = fpEntrega.formatDate(iniE, "Y-m-d H:i");
            originalValueE = pickerE.value;
            actualizarUIFechas(iniE, 'entrega');
        }
        if (fpDevolucion.selectedDates.length > 0) {
            const iniD = combinar(fpDevolucion.selectedDates[0], horaDevolucion);
            pickerD.value = fpDevolucion.formatDate(iniD, "Y-m-d H:i");
            originalValueD = pickerD.value;
            actualizarUIFechas(iniD, 'devolucion');
        }

        window.fpEntrega = fpEntrega;
        window.fpDevolucion = fpDevolucion;

        console.log('✅ Calendarios Flatpickr configurados correctamente');
    })();

    // ================================ VEHÍCULOS Y UPGRADES ===============================

    function mostrarModalOferta(oferta) {
        const modal = $elId("modalUpgrade");
        if (!modal) return;
        ContratoUI.setText($elId("upgTitulo"), oferta.nombre);
        ContratoUI.setText($elId("upgPrecioInflado"), `$${oferta.precioInflado}`);
        ContratoUI.setText($elId("upgPrecioReal"), `$${oferta.precioReal}`);
        ContratoUI.setText($elId("upgDescuento"), `${oferta.descuento}%`);
        ContratoUI.setText($elId("upgDescripcion"), oferta.descripcion);
        $elId("upgImagenVehiculo").src = oferta.imagen;
        ContratoUI.setText($elId("upgNombreVehiculo"), oferta.nombre_vehiculo);
        $elId("upgSpecs").innerHTML = `<div>${oferta.transmision ?? "—"}</div><div>${oferta.asientos ?? "--"} asientos</div><div>${oferta.puertas ?? "--"} puertas</div><div>${oferta.color ?? "—"}</div>`;
        modal.dataset.idCategoriaUpgrade = oferta.id_categoria;
        modal.classList.add("show");
    }

    $elId("btnAceptarUpgrade")?.addEventListener("click", async () => {
        const modal = $elId("modalUpgrade");
        const btn = $elId("btnAceptarUpgrade");
        btn.disabled = true; btn.innerHTML = "Aplicando...";
        try {
            const result = await ContratoAPI.postJSON(`/admin/contrato/${window.ID_RESERVACION}/actualizar-categoria`, {
                id_categoria: modal.dataset.idCategoriaUpgrade
            });
            if (result.success) {
                if (typeof window.cargarResumenBasico === 'function') await window.cargarResumenBasico();
                console.log("Upgrade aplicado.");
                modal.classList.remove("show");
                window.showStep(2);
            }
        } catch (e) {
            console.error("Error de conexión:", e);
            btn.disabled = false; btn.innerHTML = "Aceptar upgrade";
        }
    });

    $elId("btnRechazarUpgrade")?.addEventListener("click", () => { $elId("modalUpgrade").classList.remove("show"); window.showStep(2); });
    $elId("cerrarUpgrade")?.addEventListener("click", () => $elId("modalUpgrade").classList.remove("show"));

    // ================================ CAMBIO DE CATEGORÍA ===============================

    (function inicializarCambioCategoria() {
        const modalCat = $elId("modalCategorias");
        const btnAbrir = $elId("btnCambiarCategoria");
        const btnCerrar = $elId("cerrarModalCategorias");
        const btnCerrar2 = $elId("cerrarModalCategorias2");
        const contenedorCategorias = $elId("contenedorCategoriasJS");
        const elInicial = $elId("contratoInicial");

        if (!modalCat || !btnAbrir) return;

        const abrirModal = () => {
            modalCat.classList.add("show-modal");
            document.body.style.overflow = "hidden";
        };
        const cerrarModal = () => {
            modalCat.classList.remove("show-modal");
            document.body.style.overflow = "auto";
        };

        btnCerrar?.addEventListener("click", cerrarModal);
        btnCerrar2?.addEventListener("click", cerrarModal);

        modalCat.addEventListener("click", (e) => {
            if (e.target === modalCat) cerrarModal();
        });

        btnAbrir.addEventListener("click", async (e) => {
            e.preventDefault();
            abrirModal();

            if (!contenedorCategorias) return;

            contenedorCategorias.innerHTML = '<div style="width: 100%; text-align: center; padding: 40px; color: #64748b;">⏳ Cargando catálogo de categorías...</div>';

            try {
                const data = await ContratoAPI.getJSON('/admin/contrato/categorias-dinamicas');

                if (data.success && data.categorias && data.categorias.length > 0) {
                    renderizarCategorias(data.categorias);
                } else {
                    contenedorCategorias.innerHTML = '<p style="text-align:center; color:#64748b; padding: 20px;">No hay categorías disponibles en este momento.</p>';
                }
            } catch (err) {
                console.error("Error cargando categorías dinámicas:", err);
                contenedorCategorias.innerHTML = '<p style="text-align:center;">Hubo un error de conexión al cargar el catálogo.</p>';
            }
        });

        function renderizarCategorias(categorias) {
            contenedorCategorias.innerHTML = '';
            const cIni = document.getElementById('contratoInicial');
            const categoriaActualId = cIni ? cIni.dataset.idCategoria : null;

            categorias.forEach(cat => {
                const isActive = (cat.id_categoria == categoriaActualId);
                const precioFormateado = ContratoUI.money(cat.precio_dia || 0);

                const cardHtml = `
                    <div class="card-categoria ${isActive ? 'activa' : ''}"
                        data-id-categoria="${cat.id_categoria}"
                        data-codigo="${cat.codigo}"
                        data-precio="${cat.precio_dia || 0}"
                        data-nombre="${cat.nombre}">

                        <div class="cat-img-wrapper">
                            <img src="${cat.imagen}" alt="${cat.nombre}" onerror="this.src='/img/Logotipo.png';">
                        </div>

                        <div class="cat-info">
                            <div class="cat-codigo">${cat.codigo}</div>
                            <div class="cat-nombre">${cat.nombre}</div>
                            <div class="cat-precio">${precioFormateado} /día</div>
                        </div>

                        ${isActive ? '<span class="cat-badge-actual">Actual</span>' : ''}
                    </div>
                `;

                contenedorCategorias.insertAdjacentHTML('beforeend', cardHtml);
            });
        }

        let categoriaEnProceso = false;

        contenedorCategorias?.addEventListener("click", async (e) => {
            const card = e.target.closest(".card-categoria");
            if (!card) return;

            if (categoriaEnProceso) return;

            const idCategoria = card.dataset.idCategoria;
            const nombreCat = card.dataset.nombre || "";
            const codigoCat = card.dataset.codigo || "";

            if (card.classList.contains("activa")) {
                console.warn("Esa ya es la categoría actual.");
                return;
            }

            const inputE = $elId("pickerEntrega");
            const inputD = $elId("pickerDevolucion");

            if (!inputE?.value || !inputD?.value) {
                console.error("No se pudieron leer las fechas de la reservación.");
                return;
            }

            const [fechaInicio, horaInicio] = inputE.value.split(" ");
            const [fechaFin, horaFin] = inputD.value.split(" ");

            categoriaEnProceso = true;
            card.style.opacity = "0.5";
            card.style.pointerEvents = "none";
            contenedorCategorias.style.pointerEvents = "none";

            try {
                const result = await ContratoAPI.postJSON(
                    `/admin/contrato/${window.ID_RESERVACION}/recalcular-total`,
                    {
                        id_categoria: idCategoria,
                        fecha_inicio: fechaInicio,
                        hora_inicio: horaInicio,
                        fecha_fin: fechaFin,
                        hora_fin: horaFin
                    }
                );

                if (result.success) {
                    if (elInicial) elInicial.dataset.idCategoria = idCategoria;

                    contenedorCategorias.querySelectorAll(".card-categoria").forEach(c => {
                        c.classList.toggle("activa", c.dataset.idCategoria === idCategoria);
                        const badge = c.querySelector(".cat-badge-actual");
                        if (badge) badge.remove();
                    });

                    if (elInicial) {
                        elInicial.dataset.idCategoria = idCategoria;
                        elInicial.dataset.codigoCategoria = codigoCat;
                    }
                    document.dispatchEvent(new CustomEvent('categoriaCambiada', {
                        detail: { categoria: codigoCat, id: idCategoria, nombre: nombreCat }
                    }));

                    if (typeof actualizarTodasLasGarantias === "function") {
                        actualizarTodasLasGarantias();
                    }

                    const badge = document.createElement("span");
                    badge.className = "cat-badge-actual";
                    badge.textContent = "Actual";
                    card.appendChild(badge);

                    if (typeof window.cargarResumenBasico === "function") {
                        await window.cargarResumenBasico();
                    }

                    console.log(`Categoría cambiada a ${nombreCat}.`);
                    cerrarModal();
                } else {
                    console.error(result.error || "No se pudo cambiar la categoría.");
                }
            } catch (err) {
                console.error("Error al cambiar categoría:", err);
            } finally {
                categoriaEnProceso = false;
                card.style.opacity = "1";
                card.style.pointerEvents = "";
                contenedorCategorias.style.pointerEvents = "";
            }
        });
    })();

    // ================================ LÓGICA DEL DELIVERY ===============================

    const deliveryToggle = $el("#deliveryToggle");
    const deliveryFields = $el("#deliveryFields");
    const deliveryUbicacion = $el("#deliveryUbicacion");
    const deliveryDireccion = $el("#deliveryDireccion");
    const deliveryKm = $el("#deliveryKm");

    const getCostoKm = () => parseFloat($el("#deliveryPrecioKm")?.value || 0);

    const recalcularDelivery = () => {
        let kms = 0;
        if (deliveryToggle?.checked) {
            if (deliveryUbicacion?.value && deliveryUbicacion.value !== "0") {
                kms = parseFloat(deliveryUbicacion.options[deliveryUbicacion.selectedIndex].dataset.km || 0);
            } else if (deliveryUbicacion?.value === "0") {
                kms = parseFloat(deliveryKm?.value || 0);
            }
        }

        setState("deliveryTotalActual", kms * getCostoKm());
        ContratoUI.setText($el("#deliveryTotal"), ContratoUI.money(ContratoState.deliveryTotalActual));
        actualizarTotalServicios();
    };

    const enviarDeliveryAPI = async () => {
        if (!window.ID_RESERVACION) return;
        let kms = 0, ubicacionVal = "0";

        if (deliveryToggle?.checked) {
            if (deliveryUbicacion?.value && deliveryUbicacion?.value !== "0") {
                kms = parseFloat(deliveryUbicacion.options[deliveryUbicacion.selectedIndex].dataset.km || 0);
                ubicacionVal = deliveryUbicacion.value;
            } else {
                kms = parseFloat(deliveryKm?.value || 0);
            }
        }

        try {
            await ContratoAPI.postJSON(`/admin/reservacion/delivery/guardar`, {
                id_reservacion: window.ID_RESERVACION,
                delivery_activo: deliveryToggle?.checked ? 1 : 0,
                delivery_ubicacion: ubicacionVal,
                delivery_direccion: deliveryToggle?.checked ? (deliveryDireccion?.value || null) : null,
                delivery_km: kms,
                delivery_precio_km: getCostoKm(),
                delivery_total: ContratoState.deliveryTotalActual || 0
            });
            if (typeof window.cargarResumenBasico === 'function') await window.cargarResumenBasico();
        } catch (err) { console.error("Error guardando delivery:", err); }
    };

    const guardarDeliveryDebounced = debounce(enviarDeliveryAPI, 300);

    function guardarDeliverySeguro(inmediato = false) {
        inmediato ? enviarDeliveryAPI() : guardarDeliveryDebounced();
    }

    const syncDeliveryUI = () => {
        const isOn = !!deliveryToggle?.checked;
        if (deliveryFields) deliveryFields.style.display = isOn ? "block" : "none";
        const card = document.querySelector(".delivery-wrapper");
        if (card) card.classList.toggle("active", isOn);
    };

    deliveryToggle?.addEventListener("change", () => {
        syncDeliveryUI();
        if (!deliveryToggle.checked) {
            setState("deliveryTotalActual", 0);
            if (deliveryUbicacion) deliveryUbicacion.value = "";
            if (deliveryKm) deliveryKm.value = "";
            if ($el("#groupDireccion")) $el("#groupDireccion").style.display = "none";
            if ($el("#groupKm")) $el("#groupKm").style.display = "none";
            ContratoUI.setText($el("#deliveryTotal"), ContratoUI.money(0));
            actualizarTotalServicios();
            guardarDeliverySeguro(true);
        } else {
            recalcularDelivery();
            guardarDeliverySeguro();
        }
    });

    deliveryUbicacion?.addEventListener("change", () => {
        const esManual = deliveryUbicacion.value === "0";
        if ($el("#groupDireccion")) $el("#groupDireccion").style.display = esManual ? "block" : "none";
        if ($el("#groupKm")) $el("#groupKm").style.display = esManual ? "block" : "none";
        recalcularDelivery();
        guardarDeliverySeguro();
    });

    deliveryKm?.addEventListener("input", () => { recalcularDelivery(); guardarDeliverySeguro(); });
    deliveryDireccion?.addEventListener("input", () => guardarDeliverySeguro());

    if ($el("#deliveryTotalHidden")) {
        setState("deliveryTotalActual", parseFloat($el("#deliveryTotalHidden").value || 0));
        actualizarTotalServicios();
    }

    syncDeliveryUI();
    if (deliveryToggle?.checked) recalcularDelivery();

    // ================================ LÓGICA DEL DROPOFF ===============================

    const dropSwitch = $elId("switchDropoffCheckbox");
    const dropoffFields = $elId("dropoffFields");
    const dropUbicacion = $elId("dropUbicacion");

    const getCostoKmDropoff = () => parseFloat($elId("deliveryPrecioKm")?.value || 15);

    const enviarDropoffAPI = async (kms, precioKmActual) => {
        try {
            await ContratoAPI.postJSON('/admin/contrato/cargo-variable', {
                id_reservacion: window.ID_RESERVACION,
                id_contrato: window.ID_CONTRATO,
                id_concepto: 6,
                destino: dropUbicacion.options[dropUbicacion.selectedIndex]?.text || "",
                km: kms,
                precio_km: precioKmActual,
                monto_variable: ContratoState.dropoffTotal
            });
            if (typeof window.cargarResumenBasico === 'function') window.cargarResumenBasico();
        } catch (e) { console.error("Error guardando dropoff", e); }
    };

    const enviarDropoffDebounced = debounce(enviarDropoffAPI, 300);

    /**
     * Dropoff
     */
    function handleDropoffUpdate(inmediato = false, soloUI = false) {
        if (!dropUbicacion) return;

        const precioKmActual = getCostoKmDropoff();
        const opt = dropUbicacion.options[dropUbicacion.selectedIndex];
        const kms = parseFloat(opt?.dataset.km || 0);

        if (dropUbicacion.value !== "") {
            ContratoUI.setText($elId("dropCostoKmHTML"), ContratoUI.money(precioKmActual));
        }

        if (!soloUI) {
            setState("dropoffTotal", kms * precioKmActual);
        }

        ContratoUI.setText($elId("dropTotalHTML"), ContratoUI.money(ContratoState.dropoffTotal));

        const card = document.querySelector('.cargo-item[data-id="6"]');
        if (card) card.dataset.monto = ContratoState.dropoffTotal;

        actualizarTotalServicios();

        if (soloUI) return;

        if (inmediato) {
            enviarDropoffAPI(kms, precioKmActual);
        } else {
            enviarDropoffDebounced(kms, precioKmActual);
        }
    }

    dropSwitch?.addEventListener("change", async () => {
        const isOn = dropSwitch.checked;
        if (dropoffFields) dropoffFields.style.display = isOn ? "block" : "none";

        const card = document.querySelector('.cargo-item[data-id="6"]');
        if (card) card.classList.toggle("active", isOn);

        if (!isOn) {
            setState("dropoffTotal", 0);
            if (card) card.dataset.monto = "0";
            if (dropUbicacion) dropUbicacion.value = "";
            ContratoUI.setText($elId("dropTotalHTML"), ContratoUI.money(0));
            actualizarTotalServicios();

            try {
                await ContratoAPI.postJSON('/admin/contrato/cargo-variable', {
                    id_reservacion: window.ID_RESERVACION,
                    id_contrato: window.ID_CONTRATO,
                    id_concepto: 6,
                    monto_variable: 0
                });

                setTimeout(() => {
                    if (typeof window.cargarResumenBasico === 'function') {
                        window.cargarResumenBasico();
                    }
                }, 150);

            } catch (e) { console.error("Error al borrar dropoff:", e); }
        } else {
            handleDropoffUpdate(true);
        }
    });
    dropUbicacion?.addEventListener("change", () => handleDropoffUpdate(true));

    if (dropSwitch && dropSwitch.checked) {
        const card = document.querySelector('.cargo-item[data-id="6"]');
        setState("dropoffTotal", parseFloat(card?.dataset.monto || 0));
        handleDropoffUpdate(false, true);
    } else {
        setState("dropoffTotal", 0);
    }

    // ================================ LÓGICA DE GASOLINA PREPAGO ===============================

    const gasSwitch = $elId("switchGasolinaCheckbox");
    const gasolinaFields = $elId("gasolinaFields");

    const syncGasolinaUI = () => {
        const isOn = !!gasSwitch?.checked;
        if (gasolinaFields) gasolinaFields.style.display = isOn ? "block" : "none";
        const card = document.querySelector('.cargo-item[data-id="5"]');
        if (card) card.classList.toggle("active", isOn);
    };

    gasSwitch?.addEventListener("change", async () => {
        const isOn = gasSwitch.checked;
        syncGasolinaUI();

        if (!isOn) {
            setState("gasolinaTotal", 0);
            const card = document.querySelector('.cargo-item[data-id="5"]');
            if (card) card.dataset.monto = "0";
            ContratoUI.setText($elId("gasTotalHTML"), ContratoUI.money(0));
            actualizarTotalServicios();

            try {
                await ContratoAPI.postJSON('/admin/contrato/cargo-variable', {
                    id_reservacion: window.ID_RESERVACION,
                    id_contrato: window.ID_CONTRATO,
                    id_concepto: 5,
                    monto_variable: 0
                });

                setTimeout(() => {
                    if (typeof window.cargarResumenBasico === 'function') {
                        window.cargarResumenBasico();
                    }
                }, 150);
            } catch (e) { console.error("Error al borrar gasolina:", e); }
        } else {
            window.handleGasolinaUpdate();
        }
    });

    window.handleGasolinaUpdate = function () {
        const gasSwitch = $elId("switchGasolinaCheckbox");
        if (!gasSwitch || !gasSwitch.checked) return;

        const inputGasNivelActual = $elId("gasNivelActual");
        const inputGasPrecioLitro = $elId("gasPrecioLitro");

        let valorCrudo = inputGasNivelActual?.value || "16";
        let coincidencia = String(valorCrudo).match(/\d+/);
        let nivelActual = coincidencia ? parseFloat(coincidencia[0]) : 16;
        let capacidadTanque = 16;
        let precioLitro = parseFloat(inputGasPrecioLitro?.value || 20);

        if (nivelActual > capacidadTanque) nivelActual = capacidadTanque;

        let faltante = capacidadTanque - nivelActual;
        setState("gasolinaTotal", faltante > 0 ? (faltante * precioLitro) : 0);

        ContratoUI.setText($elId("gasNivelTexto"), `${nivelActual}/${capacidadTanque}`);
        ContratoUI.setText($elId("gasLitrosTexto"), `${faltante} L`);
        ContratoUI.setText($elId("gasTotalHTML"), ContratoUI.money(ContratoState.gasolinaTotal));

        const card = document.querySelector('.cargo-item[data-id="5"]');
        if (card) card.dataset.monto = ContratoState.gasolinaTotal;

        if (typeof actualizarTotalServicios === 'function') actualizarTotalServicios();

        ContratoAPI.postJSON('/admin/contrato/cargo-variable', {
            id_reservacion: window.ID_RESERVACION, id_contrato: window.ID_CONTRATO, id_concepto: 5,
            litros: faltante, precio_litro: precioLitro, monto_variable: ContratoState.gasolinaTotal
        }).then(async () => {
            if (typeof window.cargarResumenBasico === 'function') window.cargarResumenBasico();
        }).catch(e => console.error("Error gasolina:", e));
    };

    syncGasolinaUI();

    if (gasSwitch && gasSwitch.checked) {
        const card = document.querySelector('.cargo-item[data-id="5"]');
        setState("gasolinaTotal", parseFloat(card?.dataset.monto || 0));
        window.handleGasolinaUpdate();
    } else {
        setState("gasolinaTotal", 0);
    }

    // ================================ TOGGLE DEL RESUMEN ===============================

    const btnToggleDetalle = document.getElementById('btnToggleDetalle');
    const resumenContainer = document.getElementById('resumenDetalleContainer');
    const iconoFlecha = document.getElementById('iconoFlechaResumen');

    if (btnToggleDetalle && resumenContainer) {
        btnToggleDetalle.addEventListener('click', function (e) {
            e.stopPropagation();

            const estaAbierto = resumenContainer.classList.contains('abierto');

            if (estaAbierto) {
                resumenContainer.classList.remove('abierto');
                resumenContainer.style.display = 'none';
                if (iconoFlecha) iconoFlecha.style.transform = 'rotate(0deg)';
            } else {
                resumenContainer.classList.add('abierto');
                resumenContainer.style.display = 'block';
                if (iconoFlecha) iconoFlecha.style.transform = 'rotate(180deg)';
            }
        });

        document.addEventListener('click', function (e) {
            if (resumenContainer.classList.contains('abierto')) {
                const isClickInside = btnToggleDetalle.contains(e.target) || resumenContainer.contains(e.target);
                if (!isClickInside) {
                    resumenContainer.classList.remove('abierto');
                    resumenContainer.style.display = 'none';
                    if (iconoFlecha) iconoFlecha.style.transform = 'rotate(0deg)';
                }
            }
        });
    }

    // ================================ PASO 2: SERVICIOS ===============================

    function actualizarTotalServicios() {
        const elDiasRenta = document.getElementById("detDiasRenta");
        const diasRenta = parseInt(elDiasRenta?.textContent || 1);
        const cards = document.querySelectorAll(".card-servicio");
        const displayTotal = document.querySelector("#total_servicios");

        let subtotalServicios = 0;

        cards.forEach(card => {
            const precio = parseFloat(card.dataset.precio || 0);
            const cantidad = parseInt(card.querySelector(".cantidad")?.textContent || 0);
            const tipoCobro = card.dataset.tipo;

            if (tipoCobro === 'por_dia') {
                subtotalServicios += (precio * cantidad) * diasRenta;
            } else {
                subtotalServicios += (precio * cantidad);
            }
        });

        const totalFinal = subtotalServicios +
            parseFloat(ContratoState.deliveryTotalActual || 0) +
            parseFloat(ContratoState.dropoffTotal || 0) +
            parseFloat(ContratoState.gasolinaTotal || 0);

        window.requestAnimationFrame(() => {
            if (displayTotal) {
                ContratoUI.setText(displayTotal, ContratoUI.money(totalFinal));
            }
        });
    }

    const timersServicios = {};

    // =============================================
    // TOOLTIPS DEL PASO 2
    // =============================================
    (function posicionarTooltipsPaso2() {
        const MARGEN = 8;
        const SEP = 12;
        const ALTO_ESTIMADO = 76;
        const ANCHO_ESTIMADO = 240;

        function colocar(icono) {
            const r = icono.getBoundingClientRect();
            const vw = window.innerWidth;
            const vh = window.innerHeight;

            const anchoMax = Math.min(ANCHO_ESTIMADO, vw * 0.86, vw - MARGEN * 2);

            const espacioAbajo = vh - r.bottom;
            const arriba = espacioAbajo < (ALTO_ESTIMADO + SEP + MARGEN);

            let top, arrowTop;
            if (arriba) {
                top = r.top - SEP - ALTO_ESTIMADO;
                arrowTop = r.top - SEP + 1;
                icono.classList.add('tt-arriba');
            } else {
                top = r.bottom + SEP;
                arrowTop = r.bottom + SEP - 13;
                icono.classList.remove('tt-arriba');
            }
            top = Math.max(MARGEN, top);

            let left = r.left;
            if (left + anchoMax > vw - MARGEN) left = vw - MARGEN - anchoMax;
            left = Math.max(MARGEN, left);

            const arrowLeft = Math.min(
                Math.max(r.left + r.width / 2 - 7, left + 8),
                left + anchoMax - 22
            );

            icono.style.setProperty('--tt-top', top + 'px');
            icono.style.setProperty('--tt-left', left + 'px');
            icono.style.setProperty('--tt-arrow-top', arrowTop + 'px');
            icono.style.setProperty('--tt-arrow-left', arrowLeft + 'px');
        }

        function esDelPaso2(el) {
            return el.closest('#serviciosGrid, #especialesGrid');
        }

        document.addEventListener('mouseover', e => {
            const icono = e.target.closest('.servicio-numero[data-tooltip]');
            if (icono && esDelPaso2(icono)) colocar(icono);
        });

        document.addEventListener('touchstart', e => {
            const icono = e.target.closest('.servicio-numero[data-tooltip]');
            if (icono && esDelPaso2(icono)) colocar(icono);
        }, { passive: true });

        ['scroll', 'resize'].forEach(evt => {
            window.addEventListener(evt, () => {
                const icono = document.querySelector(
                    '#serviciosGrid .servicio-numero:hover, #especialesGrid .servicio-numero:hover'
                );
                if (icono) colocar(icono);
            }, true);
        });
    })();

    const gridServicios = document.querySelector("#serviciosGrid");
    const gridEspeciales = document.querySelector("#especialesGrid");

    function sincronizarEstadoAdicionales() {
        const MAX_ADICIONALES = 3;
        document.querySelectorAll("#serviciosGrid .card-servicio").forEach(card => {
            const cantEl = card.querySelector(".cantidad");
            if (!cantEl) return;
            const cant = parseInt(cantEl.textContent) || 0;
            card.classList.toggle("servicio-activo", cant > 0);
            const btnMas = card.querySelector(".btn-contador.mas");
            if (btnMas) {
                btnMas.classList.toggle("tope", cant >= MAX_ADICIONALES);
                btnMas.setAttribute("aria-disabled", cant >= MAX_ADICIONALES);
            }
        });
    }

    sincronizarEstadoAdicionales();
    window.sincronizarEstadoAdicionales = sincronizarEstadoAdicionales;

    [gridServicios, gridEspeciales].filter(Boolean).forEach((grid) => {
        grid.addEventListener("click", (e) => {
            const btn = e.target;
            if (!btn.classList.contains("mas") && !btn.classList.contains("menos")) return;

            const card = btn.closest(".card-servicio");
            const idServicio = card.dataset.id;
            const cantEl = card.querySelector(".cantidad");
            let cant = parseInt(cantEl.textContent);

            const MAX_ADICIONALES = 3;

            if (btn.classList.contains("mas")) {
                if (cant >= MAX_ADICIONALES) {
                    card.classList.add("tope-alcanzado");
                    setTimeout(() => card.classList.remove("tope-alcanzado"), 700);
                    return;
                }
                cant++;
                card.classList.remove("pulso-add");
                void card.offsetWidth;
                card.classList.add("pulso-add");
            } else if (cant > 0) {
                cant--;
            } else {
                return;
            }

            cantEl.textContent = cant;

            card.classList.toggle("servicio-activo", cant > 0);

            const btnMas = card.querySelector(".btn-contador.mas");
            if (btnMas) {
                btnMas.classList.toggle("tope", cant >= MAX_ADICIONALES);
                btnMas.setAttribute("aria-disabled", cant >= MAX_ADICIONALES);
            }

            actualizarTotalServicios();

            if (timersServicios[idServicio]) {
                clearTimeout(timersServicios[idServicio]);
            }

            timersServicios[idServicio] = setTimeout(async () => {
                try {
                    await ContratoAPI.postJSON(`/admin/contrato/servicios`, {
                        id_reservacion: window.ID_RESERVACION,
                        id_servicio: idServicio,
                        cantidad: cant,
                        precio_unitario: card.dataset.precio
                    });

                    if (typeof window.cargarResumenBasico === 'function') {
                        window.cargarResumenBasico();
                    }
                } catch (err) {
                    console.error("Error al guardar el servicio:", err);
                    if (typeof window.cargarResumenBasico === 'function') {
                        window.cargarResumenBasico();
                    }
                }
            }, 250);
        });
    });

    // ================================ PASO 3: SEGUROS ===============================

    function recalcularTotalProtecciones() {
        const displays = [
            $elId("total_seguros"),
            $elId("total_seguros_modal"),
            $elId("total_seguros_resumen")
        ].filter(Boolean);

        const btnGo = $elId("go4");

        let subtotalPorDia = 0;
        let haySeleccion = false;

        const packActive = document.querySelector(
            '#modal-vista-paquetes .hidden-radio:checked, .input-paquete:checked'
        );

        if (packActive) {
            const seguroItem = packActive.closest(".pack-card, .seguro-item");
            if (seguroItem) {
                subtotalPorDia = parseFloat(
                    seguroItem.dataset.precio ||
                    seguroItem.dataset.precioPorDia || 0
                ) || 0;
                haySeleccion = true;
            }
        } else {
            const individualesActivos = document.querySelectorAll(
                '#modal-vista-individuales .individual-card input[type="checkbox"]:checked, .switch-individual:checked'
            );

            individualesActivos.forEach(checkbox => {
                const item = checkbox.closest(".individual-card");
                if (!item) return;
                const p = parseFloat(
                    item.dataset.precio ||
                    item.dataset.precioPorDia || 0
                );
                if (!isNaN(p)) {
                    subtotalPorDia += p;
                    haySeleccion = true;
                }
            });
        }

        const diasRenta = parseInt($elId("detDiasRenta")?.textContent || 1) || 1;
        const totalProtecciones = subtotalPorDia * diasRenta;

        displays.forEach(el => {
            el.textContent = ContratoUI.money(totalProtecciones);
        });

        window.TOTAL_PROTECCIONES = totalProtecciones;
        window.SUBTOTAL_PROTECCIONES_DIA = subtotalPorDia;

        if (btnGo) {
            if (haySeleccion) {
                btnGo.classList.remove("disabled");
                btnGo.style.opacity = "1";
                btnGo.style.pointerEvents = "auto";
            } else {
                btnGo.classList.add("disabled");
                btnGo.style.opacity = "0.5";
                btnGo.style.pointerEvents = "none";
            }
        }

        if (typeof window.recalcularCarritoNavbar === "function") {
            window.recalcularCarritoNavbar();
        }

        return totalProtecciones;
    }

    window.recalcularTotalProtecciones = recalcularTotalProtecciones;

    // =========================================================
    // CARRITO DE LA NAVBAR — CÁLCULO INSTANTÁNEO EN EL NAVEGADOR
    // =========================================================
    const IVA_TASA = 0.16;

    function _num(txt) {
        if (txt === null || txt === undefined) return 0;
        const limpio = String(txt).replace(/[^0-9.,-]/g, "").replace(/,/g, "");
        const n = parseFloat(limpio);
        return isNaN(n) ? 0 : n;
    }

    function recalcularCarritoNavbar() {
        const dias = parseInt($elId("detDiasRenta")?.textContent || 1) || 1;

        const tarifaBase = _num($elId("r_base_precio")?.textContent);
        const subtotalRenta = tarifaBase * dias;

        const totalServicios = _num($elId("r_servicios_total")?.textContent);

        const totalProtecciones = (typeof window.TOTAL_PROTECCIONES === "number")
            ? window.TOTAL_PROTECCIONES
            : _num($elId("r_seguros_total")?.textContent);

        // --- Totales ---
        const subtotal = subtotalRenta + totalServicios + totalProtecciones;
        const iva = subtotal * IVA_TASA;
        const total = subtotal + iva;

        const escribir = (id, valor) => {
            const el = $elId(id);
            if (el) el.textContent = ContratoUI.money(valor);
        };

        escribir("r_subtotal", subtotal);
        escribir("r_subtotalModal", subtotal);
        escribir("r_iva", iva);
        escribir("r_ivaModal", iva);
        escribir("r_total_final", total);
        escribir("r_total_finalModal", total);
        escribir("resumenTotalCompacto", total);
        escribir("resumenTotalCompactoModal", total);

        const btnMXN = $elId("btnTotalTextContrato");
        const btnMXNModal = $elId("btnTotalTextContratoModal");
        if (btnMXN) btnMXN.textContent = ContratoUI.money(total) + " MXN";
        if (btnMXNModal) btnMXNModal.textContent = ContratoUI.money(total) + " MXN";

        const tipoCambio = window.TIPO_CAMBIO_USD || 20;
        const usd = total / tipoCambio;
        const btnUSD = $elId("btnTotalUsdContrato");
        const btnUSDModal = $elId("btnTotalUsdContratoModal");
        if (btnUSD) btnUSD.textContent = ContratoUI.money(usd) + " USD";
        if (btnUSDModal) btnUSDModal.textContent = ContratoUI.money(usd) + " USD";

        const pagos = _num($elId("detPagos")?.textContent);
        escribir("detSaldo", Math.max(0, total - pagos));
        escribir("detSaldoModal", Math.max(0, total - pagos));

        window.TOTAL_CONTRATO = total;
        return { subtotalRenta, totalServicios, totalProtecciones, subtotal, iva, total };
    }

    window.recalcularCarritoNavbar = recalcularCarritoNavbar;

    // =========================================================
    // CONFIRMACIÓN DEL SERVIDOR
    // =========================================================
    function aplicarTotalesDelServidor(data) {
        if (!data) return;

        const escribir = (id, valor) => {
            if (valor === undefined || valor === null) return;
            const el = $elId(id);
            if (el) el.textContent = ContratoUI.money(valor);
        };

        escribir("r_subtotal", data.subtotal);
        escribir("r_subtotalModal", data.subtotal);
        escribir("r_iva", data.iva ?? data.impuestos);
        escribir("r_ivaModal", data.iva ?? data.impuestos);
        escribir("r_total_final", data.total);
        escribir("r_total_finalModal", data.total);
        escribir("resumenTotalCompacto", data.total);
        escribir("resumenTotalCompactoModal", data.total);
        escribir("r_seguros_total", data.seguros_total);
        escribir("r_seguros_totalModal", data.seguros_total);
        escribir("r_servicios_total", data.servicios_total);
        escribir("r_servicios_totalModal", data.servicios_total);

        if (data.seguros_total !== undefined) {
            window.TOTAL_PROTECCIONES = Number(data.seguros_total) || 0;
        }

        if (data.total !== undefined) {
            const btnMXN = $elId("btnTotalTextContrato");
            const btnMXNModal = $elId("btnTotalTextContratoModal");
            if (btnMXN) btnMXN.textContent = ContratoUI.money(data.total) + " MXN";
            if (btnMXNModal) btnMXNModal.textContent = ContratoUI.money(data.total) + " MXN";

            const tc = window.TIPO_CAMBIO_USD || 20;
            const btnUSD = $elId("btnTotalUsdContrato");
            const btnUSDModal = $elId("btnTotalUsdContratoModal");
            if (btnUSD) btnUSD.textContent = ContratoUI.money(data.total / tc) + " USD";
            if (btnUSDModal) btnUSDModal.textContent = ContratoUI.money(data.total / tc) + " USD";

            window.TOTAL_CONTRATO = data.total;
        }

        if (data.pagos_realizados !== undefined) {
            escribir("detPagos", data.pagos_realizados);
            escribir("detPagosModal", data.pagos_realizados);
        }
        const saldo = data.saldo ?? data.saldo_pendiente;
        if (saldo !== undefined) {
            escribir("detSaldo", saldo);
            escribir("detSaldoModal", saldo);
        }
    }

    window.aplicarTotalesDelServidor = aplicarTotalesDelServidor;

    const btnVerPaquetes = $elId('btnVerPaquetes');
    const btnVerIndividuales = $elId('btnVerIndividuales');
    const btnToggleVista = $elId('btnToggleVista');
    const btnContinuar = $elId('go4');

    const vistaPaquetes = $elId('vista-paquetes');
    const vistaIndividuales = $elId('vista-individuales');
    function cambiarVistaProtecciones(vistaDestino) {
        if (vistaDestino === 'paquetes') {
            if (vistaIndividuales) vistaIndividuales.style.display = 'none';
            if (vistaPaquetes) vistaPaquetes.style.display = 'block';

            if (btnToggleVista) btnToggleVista.innerText = 'SELECCIÓN INDIVIDUAL';

            if (btnVerPaquetes) {
                btnVerPaquetes.classList.add('primary');
                btnVerPaquetes.classList.remove('gray');
            }
            if (btnVerIndividuales) {
                btnVerIndividuales.classList.add('gray');
                btnVerIndividuales.classList.remove('primary');
            }
        } else {
            if (vistaPaquetes) vistaPaquetes.style.display = 'none';
            if (vistaIndividuales) vistaIndividuales.style.display = 'block';

            if (btnToggleVista) btnToggleVista.innerText = 'VER PAQUETES';

            if (btnVerIndividuales) {
                btnVerIndividuales.classList.add('primary');
                btnVerIndividuales.classList.remove('gray');
            }
            if (btnVerPaquetes) {
                btnVerPaquetes.classList.add('gray');
                btnVerPaquetes.classList.remove('primary');
            }
        }
    }

    if (btnVerPaquetes) btnVerPaquetes.addEventListener('click', () => cambiarVistaProtecciones('paquetes'));
    if (btnVerIndividuales) btnVerIndividuales.addEventListener('click', () => cambiarVistaProtecciones('individuales'));

    if (btnToggleVista) {
        btnToggleVista.addEventListener('click', function () {
            const vistaActual = (vistaPaquetes && vistaPaquetes.style.display !== 'none') ? 'individuales' : 'paquetes';
            cambiarVistaProtecciones(vistaActual);
        });
    }

    setTimeout(recalcularTotalProtecciones, 300);

    // ================================ NAVEGACIÓN Y GUARDADO ===============================

    function precargarPaso4() {
        const idReservacion = window.ID_RESERVACION;
        if (!idReservacion) return;

        if (!document.querySelector(`link[data-contrato-prefetch="${idReservacion}"]`)) {
            const link = document.createElement("link");
            link.rel = "prefetch";
            link.as = "document";
            link.href = `/admin/contrato2/${idReservacion}`;
            link.dataset.contratoPrefetch = idReservacion;
            document.head.appendChild(link);
        }
    }

    function obtenerVehiculoSeleccionadoId() {
        return $elId("contratoInicial")?.dataset?.idVehiculo?.trim() || "";
    }

    // =============================================
    // BOTÓN CONTINUAR - PASO 1 → PASO 2
    // =============================================
    $el("#go2")?.addEventListener("click", async () => {
        if (!obtenerVehiculoSeleccionadoId()) {
            console.error("Debes seleccionar un vehículo antes de continuar.");
            return;
        }

        window.showStep(2);
    });

    // =============================================
    // BOTÓN CONTINUAR - PASO 2 → PASO 3
    // =============================================
    $el("#go3")?.addEventListener("click", () => {
        if (typeof guardarDeliverySeguro === 'function') guardarDeliverySeguro(true);
        setTimeout(() => {
            if (typeof window.cargarResumenBasico === 'function') window.cargarResumenBasico();
        }, 150);
        precargarPaso4();
        window.showStep(3);
    });

    // =============================================
    // BOTÓN CONTINUAR - PASO 3 → PASO 4
    // =============================================
    $el("#go4")?.addEventListener("click", (e) => {
        e.preventDefault();

        const elInicial = $elId("contratoInicial");
        const idReservacion = window.ID_RESERVACION || (elInicial ? elInicial.dataset.idReservacion : null);

        if (!idReservacion) {
            console.error("Error: ID de reservación perdido.");
            return;
        }

        const paqueteSeleccionado = document.querySelector(".input-paquete:checked");
        const individualSeleccionado = document.querySelector(".switch-individual:checked");

        if (!paqueteSeleccionado && !individualSeleccionado) {
            console.warn("Selecciona al menos un paquete o una protección para continuar.");
            return;
        }

        localStorage.removeItem(`contratoPasoActual_${idReservacion}`);

        const btn = e.currentTarget;
        btn.innerHTML = "Cargando Paso 4...";
        btn.style.pointerEvents = "none";

        precargarPaso4();
        window.location.href = `/admin/contrato2/${idReservacion}`;
    });

    // =============================================
    // BOTONES ATRÁS
    // =============================================
    $el("#back1")?.addEventListener("click", () => window.showStep(1));
    $el("#back2")?.addEventListener("click", () => window.showStep(2));
    $el("#back3")?.addEventListener("click", () => window.showStep(3));

    // =============================================
    // SALTO AUTOMÁTICO DE PASO (desde localStorage)
    // =============================================
    const ejecutarSaltoDePaso = () => {
        const idRes = window.ID_RESERVACION || $elId("contratoInicial")?.dataset.idReservacion;
        if (!idRes) return;

        const storageKey = `contratoPasoActual_${idRes}`;
        const pasoSolicitado = localStorage.getItem(storageKey);

        if (pasoSolicitado) {
            const pasoN = Number(pasoSolicitado);
            if ([1, 2, 3].includes(pasoN)) {
                console.log(`🚀 Saltando automáticamente al Paso: ${pasoN}`);
                if (typeof window.showStep === 'function') window.showStep(pasoN);
                if (typeof window.actualizarStepper === 'function') window.actualizarStepper(pasoN);
            }
            localStorage.removeItem(storageKey);
        }
    };

    setTimeout(ejecutarSaltoDePaso, 50);

    // =============================================
    // INICIALIZACIÓN DE GASOLINA (carga de datos guardados)
    // =============================================
    setTimeout(async () => {
        const gasSwitch = $elId("switchGasolinaCheckbox");
        if (gasSwitch && gasSwitch.checked) {
            const cardGas = document.querySelector('.cargo-item[data-id="5"]');
            const montoGuardado = parseFloat(cardGas?.dataset.monto || 0);

            if (montoGuardado > 0) {
                setState("gasolinaTotal", montoGuardado);
                ContratoUI.setText($elId("gasTotalHTML"), ContratoUI.money(montoGuardado));

                const inputNivel = $elId("gasNivelActual")?.value || "16/16";
                let nivel = parseInt(inputNivel.split('/')[0]) || 16;
                ContratoUI.setText($elId("gasLitrosTexto"), `${16 - nivel} L`);
            } else {
                if (typeof window.handleGasolinaUpdate === 'function') window.handleGasolinaUpdate();
            }
        }
    }, 800);

    // ================================ MODAL DE PROTECCIONES ===============================
    (function () {
        console.log("🟢 Inicializando Modal de Protecciones...");

        let modal, btnAbrir, btnAplicar;
        let totalModal, totalResumen, resumenNombre;

        const FORZADOS = [];   // antes ["LOU","LA"]
        const DEFAULT_COLISION = ["DECLINE CDW", "DECLINE"];
        const DEFAULT_TERCEROS = ["LI"];

        const SECCIONES_UNICAS = [
            "Colisión y robo",
            "Daños a terceros",
            "Gastos médicos",
            "Asistencia para el camino"
        ];

        let paqueteSeleccionado = null;
        let individualesSeleccionados = new Map();

        function getElements() {
            modal = document.getElementById("modalProtecciones");
            btnAbrir = document.getElementById("btnAbrirModalProtecciones");
            btnAplicar = document.getElementById("btnAplicarProtecciones");
            totalModal = document.getElementById("total_seguros_modal");
            totalResumen = document.getElementById("total_seguros_resumen");
            resumenNombre = document.getElementById("resumen_nombre_proteccion");

            return modal && btnAbrir;
        }

        function money(value) {
            const n = parseFloat(value || 0);
            return window.money ? window.money(n) : `$${n.toFixed(2)} MXN`;
        }

        function getNombreIndividual(card) {
            return card?.querySelector(".individual-nombre")?.textContent?.trim() || "";
        }

        function normalizarTexto(txt) {
            return String(txt || "").trim().toUpperCase();
        }

        function getGrupoDeCard(card) {
            if (!card) return '';

            let section = card.closest('.individuales-grid');
            if (section) {
                let prevElement = section.previousElementSibling;
                while (prevElement) {
                    if (prevElement.classList && prevElement.classList.contains('categoria-titulo-individual')) {
                        return prevElement.textContent.trim();
                    }
                    prevElement = prevElement.previousElementSibling;
                }
            }

            const titulo = card.closest('.modal-view')?.querySelector('.categoria-titulo-individual');
            return titulo ? titulo.textContent.trim() : '';
        }

        function getIdCard(card) {
            return String(card?.dataset?.id || card?.querySelector(".switch-individual")?.value || "");
        }

        function getPrecioCard(card) {
            return parseFloat(card?.dataset?.precio || 0);
        }

        // --- PROTECCIONES AUTOMÁTICAS (gratis, se encienden al activar Colisión y robo) ---
        function getCardsAutomaticas() {
            return modal.querySelectorAll(
                '.individuales-grid[data-categoria="automaticas"] .individual-card'
            );
        }

        function hayColisionActiva() {
            for (const [, item] of individualesSeleccionados) {
                if (normalizarSeccion(item.grupo) === "COLISIÓN Y ROBO") return true;
            }
            return false;
        }

        function aplicarEstadoAutomaticas() {
            const encender = hayColisionActiva();

            getCardsAutomaticas().forEach(card => {
                const checkbox = card.querySelector(".switch-individual");
                const pill = card.querySelector(".switch-pill");

                // Estado visual (SIN sumar al total ni al Map de seleccionados)
                card.classList.toggle("selected", encender);
                if (checkbox) checkbox.checked = encender;

                // Bloqueo permanente: nunca se tocan a mano
                card.classList.add("card-automatica-bloqueada");
                if (checkbox) checkbox.disabled = true;
                if (pill) pill.classList.add("pill-bloqueada");
            });
        }

        // Nombres de las automáticas que quedaron encendidas (para mostrarlas en los resúmenes).
        // No cuestan: solo se listan como incluidas cuando hay colisión activa.
        function getNombresAutomaticasActivas() {
            if (!hayColisionActiva()) return [];
            const nombres = [];
            getCardsAutomaticas().forEach(card => {
                const n = getNombreIndividual(card);
                if (n) nombres.push(n);
            });
            return nombres;
        }

        // Automáticas activas como {id, precio:0} para persistirlas en backend (gratis).
        function getAutomaticasParaGuardar() {
            if (!hayColisionActiva()) return [];
            const filas = [];
            getCardsAutomaticas().forEach(card => {
                const id = getIdCard(card);
                if (id) filas.push({ id: id, precio: 0 });
            });
            return filas;
        }

        function esForzado(nombre) {
            return FORZADOS.includes(normalizarTexto(nombre));
        }

        function esDefault(nombre, grupo) {
            const n = normalizarTexto(nombre);
            const g = normalizarTexto(grupo);
            if (g.includes("TERCEROS") || g.includes("DAÑOS")) {
                return n === "LI" || n.startsWith("LI ") || n.includes("LI -");
            }

            return false;
        }

        function normalizarSeccion(texto) {
            const t = normalizarTexto(texto);
            if (t.includes("COLISIÓN") || t.includes("COLISION") || t.includes("ROBO")) return "COLISIÓN Y ROBO";
            if (t.includes("TERCEROS") || t.includes("DAÑOS")) return "DAÑOS A TERCEROS";
            if (t.includes("MÉDICOS") || t.includes("MEDICOS")) return "GASTOS MÉDICOS";
            if (t.includes("ASISTENCIA") || t.includes("CAMINO")) return "ASISTENCIA PARA EL CAMINO";
            return t;
        }

        function actualizarUICardPaquete(card, seleccionado) {
            if (!card) return;

            const radio = card.querySelector(".input-paquete");
            const label = card.querySelector(".btn-proteccion-dividido");
            const texto = label?.querySelector(".btn-texto");

            card.classList.toggle("is-selected", seleccionado);

            if (radio) radio.checked = seleccionado;

            if (label) {
                label.classList.toggle("activado", seleccionado);
                label.classList.toggle("desactivado", !seleccionado);
            }

            if (texto) {
                texto.textContent = seleccionado ? "Seleccionado ✓" : "Seleccionar";
            }
        }

        function actualizarUICardIndividual(card, checked) {
            if (!card) return;

            const checkbox = card.querySelector(".switch-individual");
            const nombre = getNombreIndividual(card);

            if (checkbox) {
                checkbox.checked = checked;
                checkbox.disabled = false;
                checkbox.dataset.forzado = "false";
            }

            card.classList.toggle("selected", checked);

            const pill = card.querySelector(".switch-pill");
            if (pill) {
                const badge = pill.querySelector(".lock-badge");
                if (badge) badge.remove();
            }
        }

        function limpiarPaquetes() {
            paqueteSeleccionado = null;

            modal.querySelectorAll(".input-paquete").forEach(radio => {
                radio.checked = false;
                actualizarUICardPaquete(radio.closest(".pack-card"), false);
            });
        }

        function limpiarIndividuales() {
            individualesSeleccionados.clear();

            modal.querySelectorAll(".individual-card").forEach(card => {
                actualizarUICardIndividual(card, false);
            });
        }

        function agregarIndividual(card, forzado = false) {
            const id = getIdCard(card);
            const nombre = getNombreIndividual(card);
            const grupo = getGrupoDeCard(card);
            const precio = getPrecioCard(card);

            if (!id) return;

            individualesSeleccionados.set(id, {
                id,
                nombre,
                grupo,
                precio,
                forzado: false
            });

            actualizarUICardIndividual(card, true);
        }

        function quitarIndividual(card) {
            const id = getIdCard(card);
            const nombre = getNombreIndividual(card);

            if (esForzado(nombre)) {
                actualizarUICardIndividual(card, true);
                return;
            }

            individualesSeleccionados.delete(id);
            actualizarUICardIndividual(card, false);
        }

        function aplicarDefaultsIndividuales() {
            limpiarPaquetes();
            limpiarIndividuales();

            modal.querySelectorAll(".individual-card").forEach(card => {
                const nombre = getNombreIndividual(card);
                const grupo = getGrupoDeCard(card);

                // SOLO seleccionar si es exactamente LI
                // Nada de DECLINE, LOU, LA, CDW, etc.
                if (esDefault(nombre, grupo)) {
                    agregarIndividual(card, false);
                }
                // Si no es LI, aseguramos que NO esté seleccionado
                else {
                    const checkbox = card.querySelector(".switch-individual");
                    if (checkbox) {
                        checkbox.checked = false;
                        card.classList.remove("selected");
                    }
                }
            });

            actualizarTotales();
        }

        function seleccionarPaquete(radio) {
            limpiarIndividuales();

            modal.querySelectorAll(".input-paquete").forEach(r => {
                actualizarUICardPaquete(r.closest(".pack-card"), r === radio);
            });

            const card = radio.closest(".pack-card");
            paqueteSeleccionado = {
                id: radio.value,
                nombre: card?.querySelector("h4")?.textContent?.trim() || "Paquete",
                precio: parseFloat(card?.dataset?.precio || card?.closest(".seguro-item")?.dataset?.precio || 0)
            };

            actualizarTotales();
        }

        function seleccionarIndividual(card) {
                // Las automáticas están bloqueadas: no responden al clic manual
                if (card.closest('.individuales-grid[data-categoria="automaticas"]')) {
                    return;
                }

                limpiarPaquetes();

                const id = getIdCard(card);
                const nombre = getNombreIndividual(card);
                const grupo = getGrupoDeCard(card);
                const checkbox = card.querySelector(".switch-individual");

                console.log("🔄 Seleccionando individual:", { id, nombre, grupo });

                if (checkbox?.checked) {
                    quitarIndividual(card);
                    actualizarTotales();
                    return;
                }

                const grupoNormalizado = normalizarSeccion(grupo);
                const esSeccionUnica = SECCIONES_UNICAS.some(s => normalizarSeccion(s) === grupoNormalizado);

                console.log("📌 Grupo normalizado:", grupoNormalizado, "¿Es sección única?", esSeccionUnica);

                if (esSeccionUnica) {
                    modal.querySelectorAll(".individual-card").forEach(otraCard => {
                        if (otraCard === card) return;

                        const otroGrupo = getGrupoDeCard(otraCard);
                        const otroGrupoNormalizado = normalizarSeccion(otroGrupo);

                        if (otroGrupoNormalizado === grupoNormalizado) {
                            quitarIndividual(otraCard);
                        }
                    });
                }

                agregarIndividual(card, false);
                actualizarTotales();
            }

        function pintarProteccionesEnCarrito() {
            const dias = parseInt(document.getElementById("detDiasRenta")?.textContent || 1);

            const listas = [
                document.getElementById("r_seguros_lista"),
                document.getElementById("r_seguros_listaModal")
            ];

            const totales = [
                document.getElementById("r_seguros_total"),
                document.getElementById("r_seguros_totalModal")
            ];

            let html = "";
            let total = 0;

            if (paqueteSeleccionado) {
                total = paqueteSeleccionado.precio * dias;

                html = `
            <li>
                <span>${paqueteSeleccionado.nombre}</span>
                <b>${money(total)}</b>
            </li>
        `;
            } else {
                const items = Array.from(individualesSeleccionados.values());

                items.forEach(item => {
                    const importe = item.precio * dias;
                    total += importe;

                    html += `
                <li>
                    <span>${item.nombre}</span>
                    <b>${money(importe)}</b>
                </li>
            `;
                });
            }

            // Protecciones automáticas (gratis): se listan como incluidas, sin sumar al total
            getNombresAutomaticasActivas().forEach(nombre => {
                html += `
                <li>
                    <span>${nombre}</span>
                    <b>${money(0)}</b>
                </li>
            `;
            });

            listas.forEach(lista => {
                if (lista) {
                    lista.innerHTML = html || `<li class="empty">—</li>`;
                }
            });

            totales.forEach(totalEl => {
                if (totalEl) {
                    totalEl.textContent = money(total);
                }
            });

            const compacto = document.getElementById("resumenProteccionesCompacto");
            const compactoModal = document.getElementById("resumenProteccionesCompactoModal");

            let textoCompacto = "—";

            if (paqueteSeleccionado) {
                textoCompacto = paqueteSeleccionado.nombre;
            } else {
                const items = Array.from(individualesSeleccionados.values());

                if (items.length > 0) {
                    textoCompacto = items.map(i => i.nombre).join(", ");
                }
            }

            // Sumar las automáticas (gratis) al texto compacto
            const autoCompacto = getNombresAutomaticasActivas();
            if (autoCompacto.length) {
                textoCompacto = (textoCompacto === "—")
                    ? autoCompacto.join(", ")
                    : `${textoCompacto}, ${autoCompacto.join(", ")}`;
            }

            if (compacto) compacto.textContent = textoCompacto;
            if (compactoModal) compactoModal.textContent = textoCompacto;
        }

        function actualizarBadgeCarrito() {
            const btn = document.getElementById("btnToggleDetalleModal");
            if (!btn) return;
            const badge = btn.querySelector(".cart-badge");
            if (badge) badge.remove();
        }

        function actualizarTotales() {
            aplicarEstadoAutomaticas();   // refresca estado de las automáticas (gratis/bloqueadas)
            const dias = parseInt(document.getElementById("detDiasRenta")?.textContent || 1);
            let subtotal = 0;
            let textoResumen = "";

            if (paqueteSeleccionado) {
                subtotal = paqueteSeleccionado.precio * dias;
                textoResumen = `${paqueteSeleccionado.nombre}`;
            } else {
                const items = Array.from(individualesSeleccionados.values());

                items.forEach(item => {
                    subtotal += item.precio * dias;
                });

                textoResumen = ` ${items.map(i => i.nombre).join(", ")}`;
            }

            // Agregar las protecciones automáticas (gratis) al texto, si están activas
            const autoActivas = getNombresAutomaticasActivas();
            if (autoActivas.length) {
                const base = textoResumen.trim();
                textoResumen = base
                    ? `${base}, ${autoActivas.join(", ")}`
                    : autoActivas.join(", ");
            }

            if (totalModal) totalModal.textContent = money(subtotal);
            if (totalResumen) totalResumen.textContent = money(subtotal);

            if (resumenNombre) {
                resumenNombre.textContent = textoResumen || " DECLINE CDW, LI, LOU, LA";
                resumenNombre.style.color = "#16a34a";
                resumenNombre.style.background = "#dcfce7";
            }

            pintarProteccionesEnCarrito();

            if (typeof window.recalcularTotalProtecciones === "function") {
                window.recalcularTotalProtecciones();
            } else if (typeof window.recalcularCarritoNavbar === "function") {
                window.recalcularCarritoNavbar();
            }

            if (typeof copiarResumenNavbarAlModal === "function") {
                copiarResumenNavbarAlModal();
            }

            if (btnAplicar) {
                btnAplicar.disabled = false;
                btnAplicar.style.opacity = "1";
            }

            actualizarBadgeCarrito();

            if (typeof window.marcarChipsConSeleccion === "function") {
                window.marcarChipsConSeleccion();
            }
        }

        function sincronizarModalAPaso() {
            const hayPaquete = !!paqueteSeleccionado;

            document.querySelectorAll(".input-paquete").forEach(radio => {
                radio.checked = hayPaquete && String(radio.value) === String(paqueteSeleccionado.id);
            });

            document.querySelectorAll(".switch-individual").forEach(cb => {
                const card = cb.closest(".individual-card");
                const id = getIdCard(card);

                cb.checked = !hayPaquete && individualesSeleccionados.has(id);
                card?.classList.toggle("selected", cb.checked);
            });

            if (typeof window.recalcularTotalProtecciones === "function") {
                window.recalcularTotalProtecciones();
            }

            if (typeof window.marcarChipsConSeleccion === "function") {
                window.marcarChipsConSeleccion();
            }
        }


        // --- Campanita: mover la MISMA instancia al header del modal y devolverla ---
        let _campanitaHome = null;
        let _campanitaNext = null;

        function moverCampanitaAlModal() {
            const wrapper = document.querySelector('.campanita-wrapper');
            const destino = modal?.querySelector('.modal-resumen-wrapper');
            if (!wrapper || !destino) return;

            if (!_campanitaHome) {
                _campanitaHome = wrapper.parentNode;
                _campanitaNext = wrapper.nextElementSibling;
            }

            // Insertar ANTES del carrito → queda a la izquierda
            destino.parentNode.insertBefore(wrapper, destino);
            wrapper.classList.add('campanita-en-modal');
        }

        function devolverCampanita() {
            const wrapper = document.querySelector('.campanita-wrapper');
            if (!wrapper || !_campanitaHome) return;

            wrapper.classList.remove('campanita-en-modal');

            if (_campanitaNext && _campanitaNext.parentNode === _campanitaHome) {
                _campanitaHome.insertBefore(wrapper, _campanitaNext);
            } else {
                _campanitaHome.appendChild(wrapper);
            }
        }

        function abrirModal() {

            window.categoriaActual = obtenerCategoriaActual();
            console.log('📢 Abriendo modal, categoría forzada:', window.categoriaActual);

            modal.classList.add("active");
            modal.style.display = "flex";
            document.body.style.overflow = "hidden";

            const vistaPaquetes = document.getElementById("modal-vista-paquetes");
            const vistaIndividuales = document.getElementById("modal-vista-individuales");
            const tabPaquetes = document.getElementById("tabPaquetes");
            const tabIndividuales = document.getElementById("tabIndividuales");

            if (!paqueteSeleccionado && individualesSeleccionados.size === 0) {
                limpiarPaquetes();
                limpiarIndividuales();
            }

            if (vistaPaquetes)     vistaPaquetes.style.display     = "block";
            if (vistaIndividuales) vistaIndividuales.style.display = "none";

            tabPaquetes?.classList.add("active");
            tabIndividuales?.classList.remove("active");

            setTimeout(actualizarTodasLasGarantias, 200);
            actualizarTotales();
            moverCampanitaAlModal();
        }

        function cerrarModal() {
            devolverCampanita();
            modal.classList.remove("active");
            modal.style.display = "none";
            document.body.style.overflow = "";

            if (!paqueteSeleccionado && individualesSeleccionados.size === 0) {
                aplicarDefaultsIndividuales();
                sincronizarModalAPaso();
            }

            actualizarTotales();
        }

        async function guardarProteccionesEnBackend() {
            const idRes = window.ID_RESERVACION;
            if (!idRes) return;

            // Individuales elegidas por el usuario + automáticas gratis (precio 0) si hay colisión.
            let individualesPayload = [];
            if (!paqueteSeleccionado) {
                individualesPayload = Array.from(individualesSeleccionados.values()).map(i => ({
                    id: i.id,
                    precio: i.precio
                }));

                // Agregar automáticas (gratis) evitando duplicados por id
                const yaIds = new Set(individualesPayload.map(i => String(i.id)));
                getAutomaticasParaGuardar().forEach(a => {
                    if (!yaIds.has(String(a.id))) {
                        individualesPayload.push(a);
                        yaIds.add(String(a.id));
                    }
                });
            }

            const payload = {
                id_reservacion: idRes,
                id_paquete: paqueteSeleccionado?.id ?? null,
                precio_por_dia: paqueteSeleccionado?.precio ?? null,
                individuales: individualesPayload
            };

            try {
                const resp = await ContratoAPI.postJSON('/admin/contrato/protecciones/sync', payload);

                if (typeof window.aplicarTotalesDelServidor === "function") {
                    window.aplicarTotalesDelServidor(resp?.detalles || resp?.resumen || resp);
                }

                if (typeof window.cargarResumenBasico === "function") {
                    await window.cargarResumenBasico();
                }

                if (typeof copiarResumenNavbarAlModal === "function") {
                    copiarResumenNavbarAlModal();
                }

            } catch (err) {
                console.error("Error guardando protecciones:", err);
                throw err;
            }
        }

        async function hidratarDesdeBackend() {
            if (!window.ID_RESERVACION) return false;

            try {
                const resp = await fetch(`/admin/contrato/${window.ID_RESERVACION}/resumen?t=${Date.now()}`);
                if (!resp.ok) return false;

                const { success, data: r } = await resp.json();
                if (!success || !r?.seguros?.lista?.length) return false;

                console.log("Protecciones recuperadas:", r.seguros);

                limpiarPaquetes();
                limpiarIndividuales();

                let huboAlgo = false;

                r.seguros.lista.forEach(item => {
                    // ── Paquete ──
                    if (item.id_paquete) {
                        const radio = modal.querySelector(`.input-paquete[value="${item.id_paquete}"]`);
                        if (radio) {
                            seleccionarPaquete(radio);
                            huboAlgo = true;
                        }
                        return;
                    }

                    // ── Individual ──
                    if (item.id_individual) {
                        const card = modal.querySelector(`.individual-card[data-id="${item.id_individual}"]`);
                        if (!card) return;

                        // Las automáticas NO se meten al Map (son visuales/gratis).
                        // Se encenderán solas si la colisión guardada las activa.
                        const esAutomatica = !!card.closest('.individuales-grid[data-categoria="automaticas"]');
                        if (esAutomatica) return;

                        // Restaurar TODA individual guardada tal cual (colisión, LI, etc.)
                        agregarIndividual(card, false);
                        huboAlgo = true;
                    }
                });

                if (huboAlgo) {
                    actualizarTotales();
                    sincronizarModalAPaso();
                    console.log("✅ Selección de protecciones restaurada desde backend");
                } else {
                    aplicarDefaultsIndividuales();
                }

                return huboAlgo;

            } catch (e) {
                console.error("Error hidratando protecciones:", e);
                aplicarDefaultsIndividuales();
                return false;
            }
        }

        function setupEvents() {
            btnAbrir.addEventListener("click", e => {
                e.preventDefault();
                abrirModal();
            });

            document.getElementById("btnCerrarModalProtecciones")?.addEventListener("click", cerrarModal);
            document.getElementById("btnCerrarModalFooter")?.addEventListener("click", cerrarModal);

            modal.addEventListener("click", e => {
                if (e.target === modal) return cerrarModal();

                const packCard = e.target.closest(".pack-card");
                if (packCard) {
                    e.preventDefault();
                    const radio = packCard.querySelector(".input-paquete");
                    if (!radio) return;

                    const yaActivo = paqueteSeleccionado &&
                                     String(paqueteSeleccionado.id) === String(radio.value);

                    if (yaActivo) {
                        limpiarPaquetes();
                        actualizarTotales();
                    } else {
                        seleccionarPaquete(radio);
                    }
                    return;
                }

                const individualCard = e.target.closest(".individual-card");
                if (individualCard) {
                    e.preventDefault();
                    seleccionarIndividual(individualCard);
                }
            });

            document.getElementById("tabPaquetes")?.addEventListener("click", e => {
                e.preventDefault();

                document.getElementById("modal-vista-paquetes").style.display = "block";
                document.getElementById("modal-vista-individuales").style.display = "none";

                document.getElementById("tabPaquetes")?.classList.add("active");
                document.getElementById("tabIndividuales")?.classList.remove("active");

                actualizarBadgeCarrito();
            });

            document.getElementById("tabIndividuales")?.addEventListener("click", e => {
                e.preventDefault();

                document.getElementById("modal-vista-paquetes").style.display = "none";
                document.getElementById("modal-vista-individuales").style.display = "block";

                document.getElementById("tabIndividuales")?.classList.add("active");
                document.getElementById("tabPaquetes")?.classList.remove("active");

                if (individualesSeleccionados.size === 0) {
                    aplicarDefaultsIndividuales();
                } else {
                    actualizarBadgeCarrito();
                }

                if (typeof window.reiniciarFiltroIndividuales === 'function') {
                    window.reiniciarFiltroIndividuales();
                }
            });

            if (!window.__chipsListenerListo) {
                window.__chipsListenerListo = true;

                const repintarChips = () => {
                    if (typeof window.marcarChipsConSeleccion === 'function') {
                        window.marcarChipsConSeleccion();
                    }
                };

                document.addEventListener('change', e => {
                    if (e.target.closest('#modal-vista-individuales')) {
                        setTimeout(repintarChips, 0);
                    }
                });

                document.addEventListener('click', e => {
                    if (e.target.closest('#modal-vista-individuales .individual-card')) {
                        setTimeout(repintarChips, 0);
                    }
                });
            }

            // =============================================
            // FILTRO POR CATEGORÍA — PROTECCIONES INDIVIDUALES
            // =============================================
            (function inicializarFiltroIndividuales() {
                const barra = document.getElementById('filtroIndividuales');
                if (!barra) return;

                const vista = document.getElementById('modal-vista-individuales');
                if (!vista) return;

                function aplicarFiltro(categoria) {
                    const titulos = vista.querySelectorAll('.categoria-titulo-individual[data-categoria]');
                    const grids = vista.querySelectorAll('.individuales-grid[data-categoria]');

                    [...titulos, ...grids].forEach(el => {
                        const suya = el.getAttribute('data-categoria');
                        const visible = (categoria === 'todas' || suya === categoria);
                        el.classList.toggle('filtrado-oculto', !visible);
                    });

                    barra.querySelectorAll('.filtro-chip').forEach(chip => {
                        chip.classList.toggle('activo', chip.dataset.filtro === categoria);
                    });

                    marcarChipsConSeleccion();
                }

                barra.addEventListener('click', e => {
                    const chip = e.target.closest('.filtro-chip');
                    if (!chip) return;
                    e.preventDefault();
                    aplicarFiltro(chip.dataset.filtro || 'todas');
                });


                function marcarChipsConSeleccion() {

                    const vistaAct = document.getElementById('modal-vista-individuales');
                    const barraAct = document.getElementById('filtroIndividuales');
                    if (!vistaAct || !barraAct) return;

                    const conteo = {};

                    vistaAct.querySelectorAll('.individuales-grid[data-categoria]').forEach(grid => {
                        const cat = grid.getAttribute('data-categoria');
                        const elegidas = grid.querySelectorAll(
                            '.individual-card.selected, .individual-card .switch-individual:checked'
                        );
                        const tarjetas = new Set();
                        elegidas.forEach(el => {
                            const card = el.closest('.individual-card');
                            if (card) tarjetas.add(card);
                        });
                        conteo[cat] = tarjetas.size;
                    });

                    barraAct.querySelectorAll('.filtro-chip').forEach(chip => {
                        const filtro = chip.dataset.filtro;

                        if (filtro === 'todas') {
                            chip.classList.remove('con-seleccion');
                            return;
                        }

                        chip.classList.toggle('con-seleccion', (conteo[filtro] || 0) > 0);
                    });
                }

                window.reiniciarFiltroIndividuales = () => aplicarFiltro('todas');
                window.filtrarIndividualesPor = aplicarFiltro;
                window.marcarChipsConSeleccion = marcarChipsConSeleccion;
                marcarChipsConSeleccion();
            })();

            btnAplicar?.addEventListener("click", async () => {
                btnAplicar.disabled = true;
                btnAplicar.textContent = "Guardando...";

                try {
                    await guardarProteccionesEnBackend();

                    sincronizarModalAPaso();
                    pintarProteccionesEnCarrito();
                    cerrarModal();

                    console.log("Protecciones aplicadas");
                } catch (err) {
                    console.error(err);
                } finally {
                    btnAplicar.disabled = false;
                    btnAplicar.textContent = "Aplicar";
                }
            });
        }

        function init() {
            let intentos = 0;

            const timer = setInterval(async () => {
                intentos++;

                if (getElements()) {
                    clearInterval(timer);
                    setupEvents();

                    modal.style.display = "none";
                    modal.classList.remove("active");

                    aplicarDefaultsIndividuales();
                    sincronizarModalAPaso();

                    try {
                        await hidratarDesdeBackend();
                    } catch (e) {
                        console.warn("Error en hidratación, usando defaults:", e);
                        aplicarDefaultsIndividuales();
                    }

                    if (typeof window.marcarChipsConSeleccion === "function") {
                        window.marcarChipsConSeleccion();
                    }

                    aplicarEstadoAutomaticas();   // deja las automáticas bloqueadas desde el arranque

                    window.abrirModalProtecciones = abrirModal;
                    window.cerrarModalProtecciones = cerrarModal;
                    window.aplicarDefaultsIndividuales = aplicarDefaultsIndividuales;
                    window.hidratarProtecciones = hidratarDesdeBackend;

                    console.log("✅ Modal de protecciones listo - Solo LI por defecto");
                    return;
                }

                if (intentos >= 20) {
                    clearInterval(timer);
                    console.warn("No se encontró el modal de protecciones");
                }
            }, 200);
}

        init();
    })();

    // ================================ SISTEMA DE GARANTÍAS ===============================

    const GARANTIAS_POR_CATEGORIA = {
        'C': { // Compacto Chevrolet Aveo o similar
            'LDW': 5000,
            'PDW': 8000,
            'CDW 10%': 15000,
            'CDW 20%': 25000,
            'CDW declinado': 330000
        },
        'D': { // Medianos Nissan Virtus o similar
            'LDW': 5000,
            'PDW': 8000,
            'CDW 10%': 18000,
            'CDW 20%': 25000,
            'CDW declinado': 380000
        },
        'E': { // Grandes Volkswagen Jetta o similar
            'LDW': 5000,
            'PDW': 8000,
            'CDW 10%': 20000,
            'CDW 20%': 30000,
            'CDW declinado': 500000
        },
        'F': { // Full size Camry o similar
            'LDW': 5000,
            'PDW': 15000,
            'CDW 10%': 30000,
            'CDW 20%': 40000,
            'CDW declinado': 650000
        },
        'IC': { // Suv compacta Jeep Renegade o similar
            'LDW': 5000,
            'PDW': 8000,
            'CDW 10%': 20000,
            'CDW 20%': 30000,
            'CDW declinado': 500000
        },
        'I': { // Suv Mediana Volkswagen Taos o similar
            'LDW': 5000,
            'PDW': 10000,
            'CDW 10%': 30000,
            'CDW 20%': 40000,
            'CDW declinado': 600000
        },
        'IB': { // Suv Familiar compacta Toyota avanza o similar
            'LDW': 5000,
            'PDW': 8000,
            'CDW 10%': 18000,
            'CDW 20%': 25000,
            'CDW declinado': 400000
        },
        'M': { // Minivan Honda Odyssey o similar
            'LDW': 10000,
            'PDW': 20000,
            'CDW 10%': 30000,
            'CDW 20%': 40000,
            'CDW declinado': 800000
        },
        'L': { // Pasajeros de 12 usuarios Toyota Hiace o similar
            'LDW': 10000,
            'PDW': 20000,
            'CDW 10%': 30000,
            'CDW 20%': 40000,
            'CDW declinado': 800000
        },
        'H': { // Pick up Doble Cabina Nissan Frontier o similar
            'LDW': 10000,
            'PDW': 20000,
            'CDW 10%': 30000,
            'CDW 20%': 40000,
            'CDW declinado': 600000
        },
        'HI': { // Pick up 4x4 Doble Cabina Toyota Tacoma o similar
            'LDW': 10000,
            'PDW': 20000,
            'CDW 10%': 30000,
            'CDW 20%': 40000,
            'CDW declinado': 900000
        }
    };

    // Mapeo de nombres de seguros a tipos de protección
    const MAPEO_TIPO_PROTECCION = {
        'CDW 10%': 'CDW 10%',
        'CDW 20%': 'CDW 20%',
        'CDW declinado': 'CDW declinado',
        'LDW': 'LDW',
        'PDW': 'PDW',
        'PROTECCIÓN TOTAL 10%': 'CDW 10%',
        'PROTECCIÓN TOTAL 20%': 'CDW 20%',
        'PROTECCIÓN BÁSICA': 'CDW declinado',
        'ROBO TOTAL': 'CDW declinado',
        'DAÑOS A TERCEROS': 'PDW',
        'CDW PACK 1': 'CDW 10%',
        'CDW PACK 2': 'CDW 20%',
        'CDW PACK 3': 'CDW 20%',
        'DECLINE PROTECTIONS': 'CDW declinado',
        'DECLINE CDW': 'CDW declinado',
        'DECLINE': 'CDW declinado',
    };

    window.categoriaActual = null;

    function obtenerCategoriaActual() {
        const contratoInicial = document.getElementById('contratoInicial');
        if (contratoInicial) {
            const codigoCategoria = contratoInicial.dataset.codigoCategoria || contratoInicial.dataset.idCategoria;
            if (codigoCategoria && GARANTIAS_POR_CATEGORIA[codigoCategoria]) {
                window.categoriaActual = codigoCategoria;
                return codigoCategoria;
            }
        }

        const cardActiva = document.querySelector('.card-categoria.activa');
        if (cardActiva) {
            const codigo = cardActiva.dataset.codigo;
            if (codigo && GARANTIAS_POR_CATEGORIA[codigo]) {
                window.categoriaActual = codigo;
                return codigo;
            }
        }

        const cards = document.querySelectorAll('.card-categoria');
        for (let card of cards) {
            if (card.classList.contains('activa') || card.querySelector('.cat-badge-actual')) {
                const codigo = card.dataset.codigo;
                if (codigo && GARANTIAS_POR_CATEGORIA[codigo]) {
                    window.categoriaActual = codigo;
                    return codigo;
                }
            }
        }

        if (window.categoriaActual && GARANTIAS_POR_CATEGORIA[window.categoriaActual]) {
            return window.categoriaActual;
        }

        const categoriaText = document.querySelector('.categoria-actual-texto');
        if (categoriaText) {
            const texto = categoriaText.textContent.trim();
            for (let key of Object.keys(GARANTIAS_POR_CATEGORIA)) {
                if (texto.includes(key)) {
                    window.categoriaActual = key;
                    return key;
                }
            }
        }

        console.warn('⚠️ No se pudo determinar la categoría actual');
        return null;
    }

    function obtenerTipoProteccionSeleccionado() {
        const paqueteSeleccionado = document.querySelector('.input-paquete:checked');
        if (paqueteSeleccionado) {
            const card = paqueteSeleccionado.closest('.pack-card');
            if (card) {
                const nombre = card.querySelector('h4')?.textContent?.trim() || '';
                for (let [key, value] of Object.entries(MAPEO_TIPO_PROTECCION)) {
                    if (nombre.includes(key) || key.includes(nombre)) {
                        return value;
                    }
                }
            }
        }

        const individualesActivos = document.querySelectorAll('.switch-individual:checked');
        if (individualesActivos.length > 0) {
            for (let cb of individualesActivos) {
                const card = cb.closest('.individual-card');
                if (card) {
                    const nombre = card.querySelector('.individual-nombre')?.textContent?.trim() || '';
                    for (let [key, value] of Object.entries(MAPEO_TIPO_PROTECCION)) {
                        if (nombre.includes(key) || key.includes(nombre)) {
                            return value;
                        }
                    }
                }
            }
        }

        if (typeof paqueteSeleccionado !== 'undefined' && window.paqueteSeleccionado) {
            const nombre = window.paqueteSeleccionado.nombre || '';
            for (let [key, value] of Object.entries(MAPEO_TIPO_PROTECCION)) {
                if (nombre.includes(key) || key.includes(nombre)) {
                    return value;
                }
            }
        }

        return 'CDW declinado';
    }

    function actualizarGarantia(seguroId, categoria, tipoProteccion) {
        const elementoGarantia = document.getElementById(`garantia-${seguroId}`);
        if (!elementoGarantia) return;

        if (!categoria || !GARANTIAS_POR_CATEGORIA[categoria]) {
            elementoGarantia.textContent = '$0 MXN';
            return;
        }

        const garantias = GARANTIAS_POR_CATEGORIA[categoria];
        const valor = garantias[tipoProteccion];

        if (valor !== undefined) {
            const valorFormateado = new Intl.NumberFormat('es-MX').format(valor);
            elementoGarantia.textContent = `$${valorFormateado} MXN`;
            elementoGarantia.style.color = '#16a34a';
            elementoGarantia.style.fontWeight = 'bold';
        } else {
            elementoGarantia.textContent = '$0 MXN';
        }
    }

    function actualizarTodasLasGarantias() {
        const categoria = obtenerCategoriaActual();

        console.log('🔍 Categoría actual:', categoria);

        document.querySelectorAll('.pack-card').forEach(card => {
            let tipoProteccion = card.dataset.tipo;

            if (!tipoProteccion) {
                const nombre = card.querySelector('h4')?.textContent?.trim() || '';
                const nombreUpper = nombre.toUpperCase();
                if (nombreUpper.includes('LDW')) {
                    tipoProteccion = 'LDW';
                } else if (nombreUpper.includes('PDW')) {
                    tipoProteccion = 'PDW';
                } else if (nombreUpper.includes('CDW 10%') || nombreUpper.includes('10%') || nombreUpper.includes('CDW PACK 1')) {
                    tipoProteccion = 'CDW 10%';
                } else if (nombreUpper.includes('CDW 20%') || nombreUpper.includes('20%') || nombreUpper.includes('CDW PACK 2') || nombreUpper.includes('CDW PACK 3')) {
                    tipoProteccion = 'CDW 20%';
                } else if (nombreUpper.includes('DECLINE') || nombreUpper.includes('BÁSICA') || nombreUpper.includes('BASICA') || nombreUpper.includes('PROTECCIONES')) {
                    tipoProteccion = 'CDW declinado';
                } else {
                    tipoProteccion = 'CDW declinado';
                }
            }

            const seguroId = card.dataset.id;

            const elementoGarantia = document.getElementById(`garantia-${seguroId}`);
            if (!elementoGarantia) {
                console.warn(`⚠️ No se encontró elemento garantia-${seguroId}`);
                return;
            }

            if (!categoria || !GARANTIAS_POR_CATEGORIA[categoria]) {
                elementoGarantia.textContent = '$0 MXN';
                return;
            }

            const garantias = GARANTIAS_POR_CATEGORIA[categoria];
            const valor = garantias[tipoProteccion];

            if (valor !== undefined) {
                const valorFormateado = new Intl.NumberFormat('es-MX').format(valor);
                elementoGarantia.textContent = `$${valorFormateado} MXN`;
                elementoGarantia.style.color = '#16a34a';
                elementoGarantia.style.fontWeight = 'bold';
            } else {
                elementoGarantia.textContent = '$0 MXN';
                console.warn(`⚠️ No se encontró garantía para ${tipoProteccion} en categoría ${categoria}`);
            }
        });
    }

    // ================================ ESCUCHAR CAMBIOS DE CATEGORÍA ===============================
    function onCategoriaCambiada(nuevaCategoria) {
        console.log('🔄 Categoría cambiada a:', nuevaCategoria);

        window.categoriaActual = nuevaCategoria;

        const modal = document.getElementById('modalProtecciones');
        if (modal && modal.classList.contains('active')) {
            setTimeout(actualizarTodasLasGarantias, 300);
        }
    }

    document.addEventListener('categoriaCambiada', function (e) {
        if (e.detail && e.detail.categoria) {
            onCategoriaCambiada(e.detail.categoria);
        }
    });

    const contratoInicialObs = document.getElementById('contratoInicial');
    if (contratoInicialObs) {
        const observer = new MutationObserver(function (mutations) {
            for (let mutation of mutations) {
                if (mutation.type === 'attributes' &&
                    (mutation.attributeName === 'data-id-categoria' ||
                        mutation.attributeName === 'data-codigo-categoria')) {

                    const nuevaCategoria = contratoInicialObs.dataset.codigoCategoria || contratoInicialObs.dataset.idCategoria;
                    console.log('🔄 Cambio detectado en contratoInicial:', nuevaCategoria);

                    if (nuevaCategoria) {
                        onCategoriaCambiada(nuevaCategoria);
                    }
                }
            }
        });
        observer.observe(contratoInicialObs, { attributes: true });
    }

    // ================================ INTEGRACIÓN CON EL MODAL ===============================
    function inicializarSistemaGarantias() {
        console.log('🟢 Inicializando sistema de garantías...');
        const btnAbrirModal = document.getElementById('btnAbrirModalProtecciones');
        if (btnAbrirModal) {
            btnAbrirModal.addEventListener('click', function () {
                obtenerCategoriaActual();
                setTimeout(actualizarTodasLasGarantias, 300);
            });
        }

        document.addEventListener('categoriaCambiada', function (e) {
            const modal = document.getElementById('modalProtecciones');
            if (modal && modal.classList.contains('active')) {
                setTimeout(actualizarTodasLasGarantias, 200);
            }
        });

        document.addEventListener('change', function (e) {
            if (e.target.classList.contains('input-paquete') ||
                e.target.classList.contains('switch-individual')) {
                setTimeout(actualizarTodasLasGarantias, 200);
            }
        });

        const observer = new MutationObserver(function (mutations) {
            for (let mutation of mutations) {
                if (mutation.type === 'attributes' && mutation.attributeName === 'data-id-categoria') {
                    setTimeout(actualizarTodasLasGarantias, 200);
                    break;
                }
            }
        });

        const contratoInicial = document.getElementById('contratoInicial');
        if (contratoInicial) {
            observer.observe(contratoInicial, { attributes: true });
        }

        const btnAplicar = document.getElementById('btnAplicarProtecciones');
        if (btnAplicar) {
            btnAplicar.addEventListener('click', function () {
                setTimeout(actualizarTodasLasGarantias, 200);
            });
        }
        const tabPaquetes = document.getElementById('tabPaquetes');
        const tabIndividuales = document.getElementById('tabIndividuales');

        if (tabPaquetes) {
            tabPaquetes.addEventListener('click', function () {
                setTimeout(actualizarTodasLasGarantias, 300);
            });
        }
        if (tabIndividuales) {
            tabIndividuales.addEventListener('click', function () {
                setTimeout(actualizarTodasLasGarantias, 300);
            });
        }

        setTimeout(actualizarTodasLasGarantias, 600);

        console.log('✅ Sistema de garantías inicializado');
    }

    (function () {
        let intentos = 0;
        const checkModal = setInterval(function () {
            intentos++;
            if (document.getElementById('modalProtecciones')) {
                clearInterval(checkModal);
                inicializarSistemaGarantias();
                return;
            }
            if (intentos >= 20) {
                clearInterval(checkModal);
                inicializarSistemaGarantias();
            }
        }, 200);
    })();

    window.actualizarGarantia = actualizarGarantia;
    window.actualizarTodasLasGarantias = actualizarTodasLasGarantias;
    window.obtenerCategoriaActual = obtenerCategoriaActual;
    window.obtenerTipoProteccionSeleccionado = obtenerTipoProteccionSeleccionado;

    // ================================ CARRITO DEL MODAL DE PROTECCIONES ===============================
    const MESES_CORTOS = ['Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun',
                          'Jul', 'Ago', 'Sep', 'Oct', 'Nov', 'Dic'];

    function fechaCorta(iso) {
        const m = String(iso).match(/^(\d{4})-(\d{2})-(\d{2})$/);
        if (!m) return null;
        const mes = MESES_CORTOS[parseInt(m[2], 10) - 1];
        if (!mes) return null;
        return `${m[3]}-${mes}-${m[1].slice(2)}`;
    }

    function aplicarFormatoFechasResumen() {
        const ids = [
            'resumenFechasCompacto', 'resumenFechasCompactoModal',
            'detFechaSalida', 'detFechaSalidaModal',
            'detFechaRegreso', 'detFechaRegresoModal'
        ];
        ids.forEach(id => {
            const el = document.getElementById(id);
            if (!el) return;
            const txt = el.textContent;
            const nuevo = txt.replace(/\d{4}-\d{2}-\d{2}/g, d => fechaCorta(d) || d);
            if (nuevo !== txt) el.textContent = nuevo;
        });
    }

    window.aplicarFormatoFechasResumen = aplicarFormatoFechasResumen;
    function copiarResumenNavbarAlModal() {
        aplicarFormatoFechasResumen();

        const copiarTexto = (origenId, destinoId) => {
            const origen = document.getElementById(origenId);
            const destino = document.getElementById(destinoId);

            if (origen && destino) {
                destino.textContent = origen.textContent.trim();
            }
        };

        const copiarHTML = (origenId, destinoId) => {
            const origen = document.getElementById(origenId);
            const destino = document.getElementById(destinoId);

            if (origen && destino) {
                destino.innerHTML = origen.innerHTML;
            }
        };

        copiarTexto('btnTotalTextContrato', 'btnTotalTextContratoModal');
        copiarTexto('btnTotalUsdContrato', 'btnTotalUsdContratoModal');
        copiarTexto('resumenTotalCompacto', 'resumenTotalCompactoModal');

        copiarTexto('resumenVehCompacto', 'resumenVehCompactoModal');
        copiarTexto('resumenCategoriaCompacto', 'resumenCategoriaCompactoModal');
        copiarTexto('resumenDiasCompacto', 'resumenDiasCompactoModal');
        copiarTexto('resumenFechasCompacto', 'resumenFechasCompactoModal');

        copiarTexto('detCodigo', 'detCodigoModal');
        copiarTexto('detCliente', 'detClienteModal');
        copiarTexto('detTelefono', 'detTelefonoModal');
        copiarTexto('detEmail', 'detEmailModal');

        copiarTexto('detModelo', 'detModeloModal');
        copiarTexto('detMarca', 'detMarcaModal');
        copiarTexto('detCategoria', 'detCategoriaModal');
        copiarTexto('detTransmision', 'detTransmisionModal');
        copiarTexto('detPasajeros', 'detPasajerosModal');
        copiarTexto('detPuertas', 'detPuertasModal');
        copiarTexto('detKm', 'detKmModal');

        copiarTexto('detFechaSalida', 'detFechaSalidaModal');
        copiarTexto('detHoraSalida', 'detHoraSalidaModal');
        copiarTexto('detFechaEntrega', 'detFechaEntregaModal');
        copiarTexto('detHoraEntrega', 'detHoraEntregaModal');
        copiarTexto('detDiasRenta', 'detDiasRentaModal');

        copiarHTML('r_seguros_lista', 'r_seguros_listaModal');
        copiarTexto('r_seguros_total', 'r_seguros_totalModal');

        copiarHTML('r_servicios_lista', 'r_servicios_listaModal');
        copiarTexto('r_servicios_total', 'r_servicios_totalModal');

        copiarTexto('r_base_precio', 'r_base_precioModal');
        copiarTexto('r_cortesia', 'r_cortesiaModal');
        copiarTexto('r_subtotal', 'r_subtotalModal');
        copiarTexto('r_iva', 'r_ivaModal');
        copiarTexto('r_total_final', 'r_total_finalModal');

        copiarTexto('detPagos', 'detPagosModal');
        copiarTexto('detSaldo', 'detSaldoModal');

        const imgOrigen = document.getElementById('resumenImgVeh');
        const imgDestino = document.getElementById('resumenImgVehModal');

        if (imgOrigen && imgDestino) {
            imgDestino.src = imgOrigen.src;
        }

        copiarTexto('resumenProteccionesCompacto', 'resumenProteccionesCompactoModal');
    }

    window.copiarResumenAlModal = copiarResumenNavbarAlModal;
    window.copiarResumenNavbarAlModal = copiarResumenNavbarAlModal;

    function cerrarCarritoModalProtecciones() {
        const resumenModal = document.getElementById('resumenDetalleContainerModal');
        const iconoModal = document.getElementById('iconoFlechaResumenModal');

        if (!resumenModal) return;

        resumenModal.classList.remove('abierto');
        resumenModal.style.display = 'none';

        if (iconoModal) {
            iconoModal.style.transform = 'rotate(0deg)';
        }
    }

    function inicializarCarritoModalProtecciones() {
        const modal = document.getElementById('modalProtecciones');

        const btnToggleModal = document.getElementById('btnToggleDetalleModal');
        const resumenModal = document.getElementById('resumenDetalleContainerModal');
        const iconoModal = document.getElementById('iconoFlechaResumenModal');

        const btnVerDetalleModal = document.getElementById('btnVerDetalleModal');
        const btnOcultarDetalleModal = document.getElementById('btnOcultarDetalleModal');

        const resumenCompactoModal = document.getElementById('resumenCompactoModal');
        const resumenDetalleModal = document.getElementById('resumenDetalleModal');

        if (!btnToggleModal || !resumenModal) {
            console.warn('⚠️ No se encontró el carrito del modal de protecciones.');
            return;
        }

        copiarResumenNavbarAlModal();

        btnToggleModal.addEventListener('click', function (e) {
            e.preventDefault();
            e.stopPropagation();

            copiarResumenNavbarAlModal();

            const abierto = resumenModal.classList.contains('abierto');

            if (abierto) {
                cerrarCarritoModalProtecciones();
            } else {
                resumenModal.classList.add('abierto');
                resumenModal.style.display = 'block';

                if (iconoModal) {
                    iconoModal.style.transform = 'rotate(180deg)';
                }
            }
        });

        btnVerDetalleModal?.addEventListener('click', function (e) {
            e.preventDefault();

            copiarResumenNavbarAlModal();

            if (resumenCompactoModal) resumenCompactoModal.style.display = 'none';
            if (resumenDetalleModal) resumenDetalleModal.style.display = 'block';
        });

        btnOcultarDetalleModal?.addEventListener('click', function (e) {
            e.preventDefault();

            if (resumenDetalleModal) resumenDetalleModal.style.display = 'none';
            if (resumenCompactoModal) resumenCompactoModal.style.display = 'block';
        });

        document.addEventListener('click', function (e) {
            if (!resumenModal.classList.contains('abierto')) return;

            const clicDentro =
                btnToggleModal.contains(e.target) ||
                resumenModal.contains(e.target);

            if (!clicDentro) {
                cerrarCarritoModalProtecciones();
            }
        });

        document.addEventListener('change', function () {
            if (modal && modal.classList.contains('active')) {
                setTimeout(copiarResumenNavbarAlModal, 250);
            }
        });

        document.addEventListener('click', function (e) {
            if (!modal || !modal.classList.contains('active')) return;

            const cambioResumen =
                e.target.closest('.card-servicio') ||
                e.target.closest('.cargo-item') ||
                e.target.closest('.proteccion-card') ||
                e.target.closest('.coverage-card') ||
                e.target.closest('.card-paquete') ||
                e.target.closest('.btn-contador') ||
                e.target.closest('.btn-elegir-paquete') ||
                e.target.closest('.btn-proteccion');

            if (cambioResumen) {
                setTimeout(copiarResumenNavbarAlModal, 350);
            }
        });

        console.log('🛒 Carrito del modal de protecciones inicializado.');
    }


    inicializarCarritoModalProtecciones();

    /* ============================================================================
       ANEXO — LAYOUT FIJO + CARRUSEL DEL PASO 2
       ============================================================================ */
    const ANCHO_LAYOUT_FIJO = 1025;
    function esTabletPortrait() {
        return window.matchMedia('(max-width: 1024px) and (orientation: portrait)').matches;
    }

    function esPaginaLayoutFijo() {
        const tienePasos13 = !!document.querySelector('.step[data-step="1"], .step[data-step="2"], .step[data-step="3"]');
        if (!tienePasos13) return false;
        if (esTabletPortrait()) return false;
        return window.innerWidth >= ANCHO_LAYOUT_FIJO;
    }

    function liberarScrollSiNoAplica() {
        if (esPaginaLayoutFijo()) return false;
        const main = document.querySelector('.main');
        document.documentElement.style.overflow = '';
        document.documentElement.style.height = '';
        document.body.style.overflow = '';
        document.body.style.height = '';
        document.documentElement.style.overflowX = 'hidden';
        document.body.style.overflowX = 'hidden';
        if (main) {
            main.style.height = '';
            main.style.maxHeight = '';
            main.style.minHeight = '';
            main.classList.remove('altura-calculada');
        }

        document.documentElement.style.setProperty('--alto-disponible', 'auto');
        return true;
    }

    (function bloquearScrollVertical() {
        if (!esPaginaLayoutFijo()) {
            console.log('📜 Pagina con scroll vertical (pasos 4-6): no se bloquea.');
            return;
        }
        document.documentElement.style.overflow = 'hidden';
        document.documentElement.style.height = '100%';
        document.body.style.overflow = 'hidden';
        document.body.style.height = '100vh';
        document.body.style.margin = '0';
        console.log('🔒 Layout fijo activo (sin scroll vertical).');
    })();

    function ajustarAlturaMain() {
        const main = document.querySelector('.main');
        if (!main) return;
        if (!esPaginaLayoutFijo()) return;

        const alturaVentana = window.innerHeight;

        let alturaHermanos = 0;
        const padre = main.parentElement;

        if (padre) {
            Array.from(padre.children).forEach(hijo => {
                if (hijo === main) return;
                const est = window.getComputedStyle(hijo);
                if (est.display === 'none' || est.visibility === 'hidden') return;
                if (est.position === 'fixed' || est.position === 'absolute') return;
                const r = hijo.getBoundingClientRect();
                if (r.height > 0) {
                    alturaHermanos += r.height +
                        parseFloat(est.marginTop || 0) +
                        parseFloat(est.marginBottom || 0);
                }
            });

            const estPadre = window.getComputedStyle(padre);
            alturaHermanos += parseFloat(estPadre.paddingTop || 0) +
                parseFloat(estPadre.paddingBottom || 0);
        }

        const offsetTop = main.getBoundingClientRect().top + window.scrollY;
        const descuento = Math.max(alturaHermanos, offsetTop);

        const disponible = Math.max(320, alturaVentana - descuento);

        document.documentElement.style.setProperty('--alto-disponible', disponible + 'px');
        main.classList.add('altura-calculada');

        console.log('📐 Altura disponible para .main:', disponible + 'px',
            '(ventana:', alturaVentana + 'px, descuento:', Math.round(descuento) + 'px)');
    }

    ajustarAlturaMain();
    setTimeout(ajustarAlturaMain, 120);
    setTimeout(ajustarAlturaMain, 600);
    window.addEventListener('load', ajustarAlturaMain);
    window.addEventListener('resize', () => {
        if (liberarScrollSiNoAplica()) return;
        document.documentElement.style.overflow = 'hidden';
        document.documentElement.style.height = '100%';
        document.body.style.overflow = 'hidden';
        document.body.style.height = '100vh';
        ajustarAlturaMain();
    });

    window.addEventListener('orientationchange', () => {
        liberarScrollSiNoAplica();
        setTimeout(liberarScrollSiNoAplica, 250);
        setTimeout(liberarScrollSiNoAplica, 600);
    });

    if (window.matchMedia) {
        const mqPortrait = window.matchMedia('(orientation: portrait)');
        const onCambioOrientacion = () => {
            liberarScrollSiNoAplica();
            setTimeout(liberarScrollSiNoAplica, 250);
        };
        if (mqPortrait.addEventListener) {
            mqPortrait.addEventListener('change', onCambioOrientacion);
        } else if (mqPortrait.addListener) {
            mqPortrait.addListener(onCambioOrientacion);
        }
    }

    liberarScrollSiNoAplica();

    let timerResize;
    window.addEventListener('resize', () => {
        clearTimeout(timerResize);
        timerResize = setTimeout(() => {
            if (liberarScrollSiNoAplica()) {
                refrescarTodosLosCarruseles();
                return;
            }
            ajustarAlturaMain();
            refrescarTodosLosCarruseles();
        }, 120);
    });

    window.ajustarAlturaMain = ajustarAlturaMain;

    function inicializarCarruselesPaso2() {
        const carruseles = [
            document.getElementById('serviciosGrid'),
            document.getElementById('especialesGrid')
        ].filter(Boolean);

        carruseles.forEach(carrusel => {
            if (carrusel.dataset.carruselListo === '1') return;
            carrusel.dataset.carruselListo = '1';
            carrusel.addEventListener('wheel', (e) => {
                if (e.deltaY === 0) return;
                if (carrusel.scrollWidth <= carrusel.clientWidth) return;
                e.preventDefault();
                carrusel.scrollLeft += e.deltaY;
            }, { passive: false });

            let arrastrando = false, inicioX = 0, inicioScroll = 0, seMovio = false;

            carrusel.addEventListener('mousedown', (e) => {
                if (e.target.closest('button, input, select, label, .switch, .switch-toggle, .switch-pill')) return;
                arrastrando = true;
                seMovio = false;
                inicioX = e.pageX - carrusel.offsetLeft;
                inicioScroll = carrusel.scrollLeft;
                carrusel.style.cursor = 'grabbing';
            });

            const soltar = () => {
                arrastrando = false;
                carrusel.style.cursor = '';
            };

            carrusel.addEventListener('mouseleave', soltar);
            carrusel.addEventListener('mouseup', soltar);

            carrusel.addEventListener('mousemove', (e) => {
                if (!arrastrando) return;
                e.preventDefault();
                seMovio = true;
                const x = e.pageX - carrusel.offsetLeft;
                carrusel.scrollLeft = inicioScroll - (x - inicioX) * 1.4;
            });

            carrusel.addEventListener('click', (e) => {
                if (seMovio) {
                    e.stopPropagation();
                    seMovio = false;
                }
            }, true);

            const contenedor = carrusel.parentElement;
            if (!contenedor) return;
            contenedor.style.position = 'relative';

            const crearFlecha = (dir) => {
                const b = document.createElement('button');
                b.type = 'button';
                b.className = 'carrusel-flecha carrusel-flecha-' + dir;
                b.setAttribute('aria-label', dir === 'izq' ? 'Anterior' : 'Siguiente');
                b.innerHTML = dir === 'izq' ? '&#10094;' : '&#10095;';
                b.addEventListener('click', (ev) => {
                    ev.preventDefault();
                    ev.stopPropagation();
                    const paso = Math.max(260, Math.round(carrusel.clientWidth * 0.7));
                    carrusel.scrollBy({ left: dir === 'izq' ? -paso : paso, behavior: 'smooth' });
                });
                contenedor.appendChild(b);
                return b;
            };

            const flechaIzq = crearFlecha('izq');
            const flechaDer = crearFlecha('der');

            const refrescarFlechas = () => {
                const hayDesborde = carrusel.scrollWidth > carrusel.clientWidth + 4;
                flechaIzq.style.display = (hayDesborde && carrusel.scrollLeft > 6) ? 'flex' : 'none';
                flechaDer.style.display = (hayDesborde &&
                    carrusel.scrollLeft + carrusel.clientWidth < carrusel.scrollWidth - 6) ? 'flex' : 'none';
            };

            carrusel.addEventListener('scroll', refrescarFlechas);
            window.addEventListener('resize', refrescarFlechas);
            carrusel._refrescarFlechas = refrescarFlechas;

            setTimeout(refrescarFlechas, 300);
            setTimeout(refrescarFlechas, 900);
            setTimeout(refrescarFlechas, 1800);
        });

        console.log('🎠 Carruseles del paso 2 listos.');
    }

    inicializarCarruselesPaso2();

    function refrescarTodosLosCarruseles() {
        document.querySelectorAll('#serviciosGrid.add-grid, #especialesGrid.add-grid').forEach(c => {
            if (typeof c._refrescarFlechas === 'function') c._refrescarFlechas();
        });
    }

    window.refrescarCarruseles = refrescarTodosLosCarruseles;
    document.addEventListener('change', (e) => {
        if (e.target.matches('#deliveryToggle, #switchDropoffCheckbox, #switchGasolinaCheckbox, #deliveryUbicacion, #dropUbicacion')) {
            setTimeout(refrescarTodosLosCarruseles, 120);
        }
    });

    (function engancharShowStep() {
        let intentos = 0;
        const timer = setInterval(() => {
            intentos++;
            if (typeof window.showStep === 'function' && !window.showStep.__parcheado) {
                const original = window.showStep;
                const nueva = function () {
                    original.apply(this, arguments);
                    setTimeout(() => {
                        window.dispatchEvent(new Event('resize'));
                        refrescarTodosLosCarruseles();
                    }, 60);
                };
                nueva.__parcheado = true;
                window.showStep = nueva;
                clearInterval(timer);
                console.log('🔗 showStep enganchado.');
                return;
            }
            if (intentos >= 25) clearInterval(timer);
        }, 200);
    })();

    (function mantenerBodyBloqueado() {
        if (!esPaginaLayoutFijo()) return;

        const obs = new MutationObserver(() => {
            if (document.body.style.overflow === 'auto' || document.body.style.overflow === '') {
                document.body.style.overflow = 'hidden';
            }
        });
        obs.observe(document.body, { attributes: true, attributeFilter: ['style'] });
    })();

    setTimeout(() => {
        if (!esPaginaLayoutFijo()) return;
        document.documentElement.style.overflow = 'hidden';
        document.body.style.overflow = 'hidden';
        ajustarAlturaMain();
        refrescarTodosLosCarruseles();
        console.log('✅ Vista de contrato sin scroll vertical.');
    }, 1200);

    (function mantenerFormatoFechas() {
        const aplicar = () => {
            if (typeof window.aplicarFormatoFechasResumen === 'function') {
                window.aplicarFormatoFechasResumen();
            }
        };

        aplicar();

        const contenedores = [
            document.getElementById('resumenDetalleContainer'),
            document.getElementById('resumenDetalleContainerModal')
        ].filter(Boolean);

        if (contenedores.length) {
            const obs = new MutationObserver(aplicar);
            contenedores.forEach(n => obs.observe(n, {
                childList: true, subtree: true, characterData: true
            }));
        }

        document.addEventListener('click', (e) => {
            if (e.target.closest('#btnToggleDetalle, #btnToggleDetalleModal')) {
                setTimeout(aplicar, 60);
            }
        });

        setTimeout(aplicar, 400);
        setTimeout(aplicar, 1400);
    })();

    // ================================================================
    // DESPLEGABLE PERSONALIZADO DE GASOLINA  (modal Confirmar vehículo)
    // ================================================================
    (function construirDropdownGasolina() {
        const CAPACIDAD_LITROS = 60;
        const DIECISEISAVOS = 16;
        const PRINCIPALES = { 0: '0', 4: '1/4', 8: '1/2', 12: '3/4', 16: '1' };

        function etiqueta(n) {
            return PRINCIPALES[n] || `${n}/16`;
        }

        function enOctavos(n) {
            if (n === 0) return 'Vacío · 0/8';
            if (n === 16) return 'Lleno · 8/8';

            if (n % 2 === 0) {
                return `${n / 2}/8`;
            }

            const bajo = Math.floor(n / 2);
            const alto = Math.ceil(n / 2);
            return `entre ${bajo}/8 y ${alto}/8`;
        }

        function litros(n) {
            return Math.round((n / DIECISEISAVOS) * CAPACIDAD_LITROS);
        }

        function inicializar() {
            const select = document.getElementById('confGasolinaSelect');
            if (!select || select.dataset.dropdownListo === '1') return;

            const wrapper = select.closest('.gasolina-select-wrapper');
            if (!wrapper) return;

            select.dataset.dropdownListo = '1';
            select.style.display = 'none';

            const dd = document.createElement('div');
            dd.className = 'gas-dropdown';

            const trigger = document.createElement('button');
            trigger.type = 'button';
            trigger.className = 'gas-dropdown-trigger';
            trigger.innerHTML = `<span class="gas-dropdown-valor"></span>
                                 <i class="fas fa-chevron-down gas-dropdown-flecha"></i>`;

            const lista = document.createElement('ul');
            lista.className = 'gas-dropdown-lista';

            for (let n = 0; n <= DIECISEISAVOS; n++) {
                const li = document.createElement('li');
                li.className = 'gas-dropdown-opcion';
                if (PRINCIPALES[n] !== undefined) li.classList.add('es-principal');
                li.dataset.valor = String(n);
                li.textContent = etiqueta(n);
                lista.appendChild(li);
            }

            dd.appendChild(trigger);
            dd.appendChild(lista);
            select.parentNode.insertBefore(dd, select);

            let hint = wrapper.parentNode.querySelector('.gasolina-hint');
            if (!hint) {
                hint = document.createElement('div');
                hint.className = 'gasolina-hint';
                hint.innerHTML = '<i class="fas fa-info-circle"></i><span></span>';
                wrapper.parentNode.insertBefore(hint, wrapper.nextSibling);
            }
            const hintTexto = hint.querySelector('span');

            const textoLitros = document.getElementById('confLitrosTexto');

            function pintarHint(n, resaltado) {
                if (hintTexto) hintTexto.textContent = `Equivale a ${enOctavos(n)} · ~${litros(n)} L`;
                hint.classList.toggle('activo', !!resaltado);
            }

            function seleccionar(n) {
                select.value = String(n);
                trigger.querySelector('.gas-dropdown-valor').textContent = `${n}/16`;

                lista.querySelectorAll('.gas-dropdown-opcion').forEach(o => {
                    o.classList.toggle('seleccionada', o.dataset.valor === String(n));
                });

                if (textoLitros) textoLitros.textContent = `~${litros(n)} L`;
                pintarHint(n, false);

                select.dispatchEvent(new Event('change', { bubbles: true }));
            }

            function abrir() {
                dd.classList.add('abierto');
                const sel = lista.querySelector('.seleccionada');
                if (sel) sel.scrollIntoView({ block: 'nearest' });
            }

            function cerrar() {
                dd.classList.remove('abierto');
                lista.querySelectorAll('.resaltada').forEach(o => o.classList.remove('resaltada'));
                pintarHint(parseInt(select.value, 10) || 0, false);
            }

            trigger.addEventListener('click', (e) => {
                e.preventDefault();
                e.stopPropagation();
                dd.classList.contains('abierto') ? cerrar() : abrir();
            });

            lista.addEventListener('mouseover', (e) => {
                const op = e.target.closest('.gas-dropdown-opcion');
                if (!op) return;
                lista.querySelectorAll('.resaltada').forEach(o => o.classList.remove('resaltada'));
                op.classList.add('resaltada');
                pintarHint(parseInt(op.dataset.valor, 10), true);
            });

            lista.addEventListener('mouseleave', () => {
                lista.querySelectorAll('.resaltada').forEach(o => o.classList.remove('resaltada'));
                pintarHint(parseInt(select.value, 10) || 0, false);
            });

            lista.addEventListener('click', (e) => {
                const op = e.target.closest('.gas-dropdown-opcion');
                if (!op) return;
                e.preventDefault();
                e.stopPropagation();
                seleccionar(parseInt(op.dataset.valor, 10));
                cerrar();
            });

            document.addEventListener('click', (e) => {
                if (!dd.contains(e.target)) cerrar();
            });

            trigger.addEventListener('keydown', (e) => {
                const actual = parseInt(select.value, 10) || 0;
                if (e.key === 'ArrowDown') {
                    e.preventDefault();
                    seleccionar(Math.min(DIECISEISAVOS, actual + 1));
                } else if (e.key === 'ArrowUp') {
                    e.preventDefault();
                    seleccionar(Math.max(0, actual - 1));
                } else if (e.key === 'Escape') {
                    cerrar();
                }
            });

            seleccionar(parseInt(select.value, 10) || DIECISEISAVOS);
        }

        document.addEventListener('click', (e) => {
            if (e.target.closest('[data-abrir-confirmar-vehiculo], .btn-elegir-vehiculo, #btnConfirmarVehiculo')) {
                setTimeout(inicializar, 120);
            }
        });

        const modal = document.getElementById('modalConfirmarVehiculo');
        if (modal) {
            new MutationObserver(() => inicializar()).observe(modal, {
                childList: true, subtree: true
            });
        }

        inicializar();
        setTimeout(inicializar, 500);
        setTimeout(inicializar, 1500);

        window.inicializarDropdownGasolina = inicializar;
    })();

    (function engancharRecalculoCarrito() {
        const recalcular = () => {
            if (typeof window.recalcularCarritoNavbar === 'function') {
                window.recalcularCarritoNavbar();
            }
        };

        document.addEventListener('click', (e) => {
            if (e.target.closest('.btn-contador, .cargo-item-toggle')) {
                setTimeout(recalcular, 180);
            }
        });

        document.addEventListener('change', (e) => {
            if (e.target.matches(
                '#deliveryToggle, #switchDropoffCheckbox, #switchGasolinaCheckbox, ' +
                '#deliveryUbicacion, #dropUbicacion, #deliveryKm'
            )) {
                setTimeout(recalcular, 180);
            }
        });

        ['r_base_precio', 'detDiasRenta', 'r_servicios_total'].forEach(id => {
            const el = document.getElementById(id);
            if (!el) return;
            new MutationObserver(recalcular).observe(el, {
                childList: true, subtree: true, characterData: true
            });
        });

        setTimeout(recalcular, 600);
        setTimeout(recalcular, 1600);
    })();

});

/* ================================ CAMPANITA DE COMENTARIOS ================================ */
(function () {
    function init() {
        const btnCampanita = document.getElementById('btnToggleComentarios');
        const panel        = document.getElementById('comentariosDesplegable');
        const lista        = document.getElementById('comentariosLista');
        const input        = document.getElementById('comentarioInput');
        const btnAgregar   = document.getElementById('btnAgregarComentario');
        const badge        = document.getElementById('campanitaBadge');

        if (!btnCampanita || !panel || !lista) return;

        const idReservacion = window.ID_RESERVACION;
        const csrf = window.csrfToken;
        const MESES = ['Ene','Feb','Mar','Abr','May','Jun','Jul','Ago','Sep','Oct','Nov','Dic'];
        let comentariosActuales = '';

        const escapar = (t) => { const d = document.createElement('div'); d.textContent = t == null ? '' : String(t); return d.innerHTML; };

        const parsear = (txt) => !txt ? [] : String(txt).split('\n').map(l => l.trim()).filter(Boolean);

        const separarFecha = (linea) => {
            const m = linea.match(/^\[([^\]]+)\]\s*(.*)$/);
            return m ? { fecha: m[1], texto: m[2] } : { fecha: null, texto: linea };
        };

        function render(comentarios) {
            const items = parsear(comentarios);
            if (badge) { badge.textContent = items.length; badge.classList.toggle('is-empty', items.length === 0); }

            if (!items.length) { lista.innerHTML = '<div class="comentarios-vacio">Sin comentarios registrados.</div>'; return; }

            lista.innerHTML = items.map(l => {
                const { fecha, texto } = separarFecha(l);
                const f = fecha ? `<span class="comentario-fecha">${escapar(fecha)}</span>` : '';
                return `<div class="comentario-item">${f}${escapar(texto)}</div>`;
            }).join('');
            lista.scrollTop = lista.scrollHeight;
        }

        function marca() {
            const d = new Date();
            const p = (n) => String(n).padStart(2, '0');
            return `${p(d.getDate())}-${MESES[d.getMonth()]}-${d.getFullYear()} ${p(d.getHours())}:${p(d.getMinutes())}`;
        }

        async function cargar() {
            if (!idReservacion) return;
            try {
                const resp = await fetch(`/admin/contrato/comentarios/${idReservacion}`, { headers: { 'Accept': 'application/json' } });
                const data = await resp.json();
                comentariosActuales = data.comentarios || '';
                render(comentariosActuales);
                if (parsear(comentariosActuales).length) {
                    btnCampanita.classList.remove('tiene-comentarios'); void btnCampanita.offsetWidth;
                    btnCampanita.classList.add('tiene-comentarios');
                }
            } catch (e) {
                console.error('Error cargando comentarios:', e);
                lista.innerHTML = '<div class="comentarios-vacio">No se pudieron cargar los comentarios.</div>';
            }
        }

        async function agregar() {
            const texto = (input?.value || '').trim();
            if (!texto) { input?.focus(); return; }

            const nuevaLinea = `[${marca()}] ${texto}`;
            const nuevoTexto = comentariosActuales.trim() !== '' ? comentariosActuales.replace(/\s+$/, '') + '\n' + nuevaLinea : nuevaLinea;

            btnAgregar.disabled = true;
            const original = btnAgregar.innerHTML;
            btnAgregar.innerHTML = 'Guardando…';
            try {
                const resp = await fetch('/admin/contrato/comentarios', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': csrf },
                    body: JSON.stringify({ id_reservacion: idReservacion, comentarios: nuevoTexto })
                });
                const data = await resp.json();
                if (!data.success) throw new Error(data.message || 'Error al guardar');

                comentariosActuales = nuevoTexto;
                render(comentariosActuales);
                if (input) input.value = '';
                btnCampanita.classList.remove('tiene-comentarios'); void btnCampanita.offsetWidth;
                btnCampanita.classList.add('tiene-comentarios');
                console.log('Comentario agregado');
            } catch (e) {
                console.error('Error guardando comentario:', e);
            } finally {
                btnAgregar.disabled = false;
                btnAgregar.innerHTML = original;
            }
        }

        const abrir  = () => panel.style.display = 'block';
        const cerrar = () => panel.style.display = 'none';

        btnCampanita.addEventListener('click', (e) => { e.stopPropagation(); panel.style.display === 'block' ? cerrar() : abrir(); });
        panel.addEventListener('click', (e) => e.stopPropagation());
        btnAgregar?.addEventListener('click', agregar);
        document.addEventListener('click', (e) => {
            if (panel.style.display !== 'block') return;
            if (btnCampanita.contains(e.target) || panel.contains(e.target)) return;
            cerrar();
        });
        document.addEventListener('keydown', (e) => { if (e.key === 'Escape' && panel.style.display === 'block') cerrar(); });

        cargar();
    }

    if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', init);
    else init();
})();

/* ===== DROPDOWN OFICINA DEVOLUCIÓN (portal al body: escapa del transform de la card) ===== */
(function () {
    const ICONOS = {
        avion:  '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17.8 19.2 16 11l3.5-3.5C21 6 21.5 4 21 3c-1-.5-3 0-4.5 1.5L13 8 4.8 6.2c-.5-.1-.9.1-1.1.5l-.3.5c-.2.5-.1 1 .3 1.3L9 12l-2 3H4l-1 1 3 2 2 3 1-1v-3l3-2 3.5 5.3c.3.4.8.5 1.3.3l.5-.2c.4-.3.6-.7.5-1.2z"/></svg>',
        autobus:'<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M8 6v6"/><path d="M15 6v6"/><path d="M2 12h19.6"/><path d="M18 18h3s.5-1.7.8-2.8c.1-.4.2-.8.2-1.2V6c0-1.1-.9-2-2-2H4c-1.1 0-2 .9-2 2v8c0 .4.1.8.2 1.2C2.5 16.3 3 18 3 18h3"/><circle cx="7" cy="18" r="2"/><path d="M9 18h6"/><circle cx="17" cy="18" r="2"/></svg>',
        oficina:'<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="4" y="2" width="16" height="20" rx="2"/><path d="M9 22v-4h6v4"/><path d="M8 6h.01"/><path d="M16 6h.01"/><path d="M12 6h.01"/><path d="M12 10h.01"/><path d="M12 14h.01"/><path d="M16 10h.01"/><path d="M16 14h.01"/><path d="M8 10h.01"/><path d="M8 14h.01"/></svg>',
        pin:    '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 10c0 6-8 12-8 12s-8-6-8-12a8 8 0 0 1 16 0Z"/><circle cx="12" cy="10" r="3"/></svg>'
    };

    function pintarIconos() {
        document.querySelectorAll('.oficina-ico').forEach(el => {
            const tipo = el.getAttribute('data-tipo') || 'pin';
            el.innerHTML = ICONOS[tipo] || ICONOS.pin;
        });
    }

    function init() {
        const dd = document.getElementById('oficinaDropdown');
        if (!dd) return;

        const btn      = document.getElementById('oficinaDropdownBtn');
        const panel    = document.getElementById('oficinaDropdownPanel');
        const labelSel = document.getElementById('oficinaLabelSel');
        const hidden   = document.getElementById('sucursalDevolucionInput');
        const icoBtn   = btn.querySelector('.oficina-ico');
        const idReservacion = dd.getAttribute('data-id-reservacion');
        const csrf = window.csrfToken;

        // 🚪 PORTAL: sacar el panel de la card y colgarlo del body.
        // Así ningún transform de la card puede romper el position:fixed.
        document.body.appendChild(panel);

        pintarIconos();

        // 🔒 Evita que el click en el botón abra el calendario de la card
        ['click', 'mousedown', 'pointerdown', 'touchstart'].forEach(evt => {
            dd.addEventListener(evt, (e) => e.stopPropagation());
        });

        // 📌 Posiciona el panel pegado al botón (voltea hacia arriba si no cabe)
        function posicionarPanel() {
            const r = btn.getBoundingClientRect();
            panel.style.width = r.width + 'px';
            panel.style.left  = r.left + 'px';

            const alto = panel.offsetHeight;
            const espacioAbajo = window.innerHeight - r.bottom;
            if (espacioAbajo < alto + 12 && r.top > alto + 12) {
                panel.style.top = (r.top - alto - 6) + 'px';
            } else {
                panel.style.top = (r.bottom + 6) + 'px';
            }
        }

        const abrir = () => {
            dd.classList.add('is-open');
            panel.style.display = 'block';   // 👈 controlado desde JS (el panel ya no está dentro de .oficina-dropdown)
            posicionarPanel();
            window.addEventListener('scroll', posicionarPanel, true);
            window.addEventListener('resize', posicionarPanel);
        };
        const cerrar = () => {
            dd.classList.remove('is-open');
            panel.style.display = 'none';
            window.removeEventListener('scroll', posicionarPanel, true);
            window.removeEventListener('resize', posicionarPanel);
        };
        const toggle = () => dd.classList.contains('is-open') ? cerrar() : abrir();

        btn.addEventListener('click', toggle);
        document.addEventListener('click', (e) => {
            if (!dd.contains(e.target) && !panel.contains(e.target)) cerrar();
        });
        document.addEventListener('keydown', (e) => { if (e.key === 'Escape') cerrar(); });

        panel.querySelectorAll('.oficina-opcion').forEach(op => {
            op.addEventListener('click', async () => {
                const id     = op.getAttribute('data-id');
                const nombre = op.getAttribute('data-nombre');
                const tipo   = op.getAttribute('data-tipo');

                labelSel.textContent = nombre;
                hidden.value = id;
                icoBtn.setAttribute('data-tipo', tipo);
                icoBtn.innerHTML = ICONOS[tipo] || ICONOS.pin;
                panel.querySelectorAll('.oficina-opcion').forEach(o => o.classList.remove('is-selected'));
                op.classList.add('is-selected');
                cerrar();

                try {
                    const resp = await fetch('/admin/contrato/sucursal-devolucion', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': csrf },
                        body: JSON.stringify({ id_reservacion: idReservacion, sucursal_entrega: id })
                    });
                    const data = await resp.json();
                    if (!data.success) throw new Error(data.message || 'Error');
                    console.log('Oficina de devolución actualizada');
                } catch (e) {
                    console.error('Error guardando sucursal de devolución:', e);
                }
            });
        });
    }

    if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', init);
    else init();
})();

/* ===== DROPDOWN DELIVERY + LINK MAPS ===== */
(function () {
    const ICONOS = {
        avion:  '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17.8 19.2 16 11l3.5-3.5C21 6 21.5 4 21 3c-1-.5-3 0-4.5 1.5L13 8 4.8 6.2c-.5-.1-.9.1-1.1.5l-.3.5c-.2.5-.1 1 .3 1.3L9 12l-2 3H4l-1 1 3 2 2 3 1-1v-3l3-2 3.5 5.3c.3.4.8.5 1.3.3l.5-.2c.4-.3.6-.7.5-1.2z"/></svg>',
        autobus:'<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M8 6v6"/><path d="M15 6v6"/><path d="M2 12h19.6"/><path d="M18 18h3s.5-1.7.8-2.8c.1-.4.2-.8.2-1.2V6c0-1.1-.9-2-2-2H4c-1.1 0-2 .9-2 2v8c0 .4.1.8.2 1.2C2.5 16.3 3 18 3 18h3"/><circle cx="7" cy="18" r="2"/><path d="M9 18h6"/><circle cx="17" cy="18" r="2"/></svg>',
        pin:    '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 10c0 6-8 12-8 12s-8-6-8-12a8 8 0 0 1 16 0Z"/><circle cx="12" cy="10" r="3"/></svg>',
        edit:   '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 20h9"/><path d="M16.5 3.5a2.1 2.1 0 0 1 3 3L7 19l-4 1 1-4Z"/></svg>',
        maps:   '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 10c0 6-8 12-8 12s-8-6-8-12a8 8 0 0 1 16 0Z"/><circle cx="12" cy="10" r="3"/></svg>'
    };
    const pintar = (scope) => (scope || document).querySelectorAll('.deliv-ico').forEach(el => {
        el.innerHTML = ICONOS[el.getAttribute('data-tipo')] || ICONOS.pin;
    });

    function init() {
        const dd     = document.getElementById('delivUbicDropdown');
        const select = document.getElementById('deliveryUbicacion');
        if (!dd || !select) return;

        const btn   = document.getElementById('delivUbicBtn');
        const panel = document.getElementById('delivUbicPanel');
        const label = document.getElementById('delivUbicLabel');
        const ico   = btn.querySelector('.deliv-ico');

        pintar(dd);

        // Refleja el valor inicial del select en el botón
        const optSel = select.options[select.selectedIndex];
        if (optSel && optSel.value !== '') {
            label.textContent = optSel.text.trim();
            const opBtn = panel.querySelector(`.deliv-opcion[data-id="${optSel.value}"]`);
            const tipo = opBtn?.getAttribute('data-tipo') || 'pin';
            ico.setAttribute('data-tipo', tipo); ico.innerHTML = ICONOS[tipo] || ICONOS.pin;
            opBtn?.classList.add('is-selected');
        }

        function posicionar() {
            const r = btn.getBoundingClientRect();
            panel.style.width = r.width + 'px';
            panel.style.left  = r.left + 'px';
            const alto = panel.offsetHeight, espacio = window.innerHeight - r.bottom;
            panel.style.top = (espacio < alto + 12 && r.top > alto + 12) ? (r.top - alto - 6) + 'px' : (r.bottom + 6) + 'px';
        }
        const abrir  = () => { dd.classList.add('is-open'); panel.style.display = 'block'; posicionar();
            window.addEventListener('scroll', posicionar, true); window.addEventListener('resize', posicionar); };
        const cerrar = () => { dd.classList.remove('is-open'); panel.style.display = 'none';
            window.removeEventListener('scroll', posicionar, true); window.removeEventListener('resize', posicionar); };

        // Portal al body (evita recortes por overflow/transform)
        document.body.appendChild(panel);

        btn.addEventListener('click', (e) => { e.stopPropagation(); dd.classList.contains('is-open') ? cerrar() : abrir(); });
        document.addEventListener('click', (e) => { if (!dd.contains(e.target) && !panel.contains(e.target)) cerrar(); });
        document.addEventListener('keydown', (e) => { if (e.key === 'Escape') cerrar(); });

        panel.querySelectorAll('.deliv-opcion').forEach(op => {
            op.addEventListener('click', () => {
                const id   = op.getAttribute('data-id');
                const txt  = op.querySelector('.deliv-opcion-txt')?.textContent.trim() || '';
                const tipo = op.getAttribute('data-tipo') || 'pin';

                // Actualiza UI del botón
                label.textContent = txt;
                ico.setAttribute('data-tipo', tipo); ico.innerHTML = ICONOS[tipo] || ICONOS.pin;
                panel.querySelectorAll('.deliv-opcion').forEach(o => o.classList.remove('is-selected'));
                op.classList.add('is-selected');
                cerrar();

                // 🔑 Pone el valor en el SELECT OCULTO y dispara change → tu lógica actual corre igual
                select.value = id;
                select.dispatchEvent(new Event('change', { bubbles: true }));
            });
        });
    }

    // ── Link de Maps: indicaciones oficina → dirección ──
    function initMaps() {
        const link = document.getElementById('deliveryMapsLink');
        const dir  = document.getElementById('deliveryDireccion');
        if (!link || !dir) return;

        const actualizar = () => {
            const destino = (dir.value || '').trim();
            const origen  = (link.getAttribute('data-origen') || '').trim();
            if (!destino) { link.classList.add('is-disabled'); link.href = '#'; return; }
            link.classList.remove('is-disabled');
            const base = 'https://www.google.com/maps/dir/?api=1';
            link.href = origen
                ? `${base}&origin=${encodeURIComponent(origen)}&destination=${encodeURIComponent(destino)}`
                : `https://www.google.com/maps/search/?api=1&query=${encodeURIComponent(destino)}`;
        };
        dir.addEventListener('input', actualizar);
        actualizar();
    }

    function run() { init(); initMaps(); }
    if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', run);
    else run();
})();




/* ===== DROPDOWN DROPOFF (mismo estilo/orden que delivery) ===== */
(function () {
    const ICONOS = {
        avion:  '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17.8 19.2 16 11l3.5-3.5C21 6 21.5 4 21 3c-1-.5-3 0-4.5 1.5L13 8 4.8 6.2c-.5-.1-.9.1-1.1.5l-.3.5c-.2.5-.1 1 .3 1.3L9 12l-2 3H4l-1 1 3 2 2 3 1-1v-3l3-2 3.5 5.3c.3.4.8.5 1.3.3l.5-.2c.4-.3.6-.7.5-1.2z"/></svg>',
        autobus:'<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M8 6v6"/><path d="M15 6v6"/><path d="M2 12h19.6"/><path d="M18 18h3s.5-1.7.8-2.8c.1-.4.2-.8.2-1.2V6c0-1.1-.9-2-2-2H4c-1.1 0-2 .9-2 2v8c0 .4.1.8.2 1.2C2.5 16.3 3 18 3 18h3"/><circle cx="7" cy="18" r="2"/><path d="M9 18h6"/><circle cx="17" cy="18" r="2"/></svg>',
        pin:    '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 10c0 6-8 12-8 12s-8-6-8-12a8 8 0 0 1 16 0Z"/><circle cx="12" cy="10" r="3"/></svg>'
    };
    const pintar = (scope) => scope.querySelectorAll('.deliv-ico').forEach(el => {
        el.innerHTML = ICONOS[el.getAttribute('data-tipo')] || ICONOS.pin;
    });

    function init() {
        const dd     = document.getElementById('dropUbicDropdown');
        const select = document.getElementById('dropUbicacion');
        if (!dd || !select) return;

        const btn   = document.getElementById('dropUbicBtn');
        const panel = document.getElementById('dropUbicPanel');
        const label = document.getElementById('dropUbicLabel');
        const ico   = btn.querySelector('.deliv-ico');

        pintar(dd);

        const optSel = select.options[select.selectedIndex];
        if (optSel && optSel.value !== '') {
            const opBtn = panel.querySelector('.deliv-opcion[data-id="' + optSel.value + '"]');
            if (opBtn) {
                label.textContent = opBtn.querySelector('.deliv-opcion-txt')?.textContent.trim() || optSel.text.trim();
                const tipo = opBtn.getAttribute('data-tipo') || 'pin';
                ico.setAttribute('data-tipo', tipo); ico.innerHTML = ICONOS[tipo] || ICONOS.pin;
                opBtn.classList.add('is-selected');
            }
        }

        function posicionar() {
            const r = btn.getBoundingClientRect();
            panel.style.width = r.width + 'px';
            panel.style.left  = r.left + 'px';
            const alto = panel.offsetHeight, espacio = window.innerHeight - r.bottom;
            panel.style.top = (espacio < alto + 12 && r.top > alto + 12) ? (r.top - alto - 6) + 'px' : (r.bottom + 6) + 'px';
        }
        const abrir  = () => { dd.classList.add('is-open'); panel.style.display = 'block'; posicionar();
            window.addEventListener('scroll', posicionar, true); window.addEventListener('resize', posicionar); };
        const cerrar = () => { dd.classList.remove('is-open'); panel.style.display = 'none';
            window.removeEventListener('scroll', posicionar, true); window.removeEventListener('resize', posicionar); };

        document.body.appendChild(panel);

        btn.addEventListener('click', (e) => { e.stopPropagation(); dd.classList.contains('is-open') ? cerrar() : abrir(); });
        document.addEventListener('click', (e) => { if (!dd.contains(e.target) && !panel.contains(e.target)) cerrar(); });
        document.addEventListener('keydown', (e) => { if (e.key === 'Escape') cerrar(); });

        panel.querySelectorAll('.deliv-opcion').forEach(op => {
            op.addEventListener('click', () => {
                const id   = op.getAttribute('data-id');
                const txt  = op.querySelector('.deliv-opcion-txt')?.textContent.trim() || '';
                const tipo = op.getAttribute('data-tipo') || 'pin';
                label.textContent = txt;
                ico.setAttribute('data-tipo', tipo); ico.innerHTML = ICONOS[tipo] || ICONOS.pin;
                panel.querySelectorAll('.deliv-opcion').forEach(o => o.classList.remove('is-selected'));
                op.classList.add('is-selected');
                cerrar();
                select.value = id;
                select.dispatchEvent(new Event('change', { bubbles: true }));
            });
        });
    }
    if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', init);
    else init();
})();
