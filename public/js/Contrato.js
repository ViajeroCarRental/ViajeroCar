// Contrato
// Pasos 1 al 3

document.addEventListener("DOMContentLoaded", () => {
    console.log("✅ DOM listo, iniciando navegación de pasos (1-3)...");

    // ================================ UTILIDADES ===============================

    // Función de Debounce para no saturar el servidor en eventos de "input"
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

    // Actualiza el estado local (sin contaminar todo el window innecesariamente)
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
            if (window.alertify) {
                if (type === "error") return alertify.error(message);
                if (type === "warning") return alertify.warning(message);
                return alertify.success(message);
            }
            if (type === "error") return alert(message);
            console[type === "warning" ? "warn" : "log"](message);
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
            if (dateD <= dateE) {
                dateD = new Date(dateE);
                dateD.setDate(dateE.getDate() + 1);
                dateD.setHours(dateE.getHours());
                dateD.setMinutes(dateE.getMinutes());
                warning = true;
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

            window.requestAnimationFrame(() => {
                inputE.value = ui.valE;
                inputD.value = ui.valD;

                if (warning) {
                    ContratoUI.notify("warning", "Fecha ajustada: La devolución debe ser posterior a la entrega.");
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
                            ContratoUI.notify("success", "✅ Cambio de fecha aprobado.");
                        } else {
                            ContratoUI.notify("error", "❌ Solicitud de cambio rechazada.");
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

                                ContratoUI.notify("success", data.msg || "Solicitud enviada. Esperando aprobación...");
                                sessionStorage.setItem("solicitudCambio", JSON.stringify({
                                    activa: true,
                                    id_reservacion: window.ID_RESERVACION,
                                    f, h
                                }));

                                if (typeof iniciarMonitoreoAprobacion === 'function') {
                                    iniciarMonitoreoAprobacion();
                                }
                            } catch (err) {
                                ContratoUI.notify("error", "Error enviando solicitud.");
                            }
                        },
                        () => {
                            input.value = fechaOriginal;
                        }
                    ).set('labels', { ok: 'Enviar Solicitud', cancel: 'Cancelar' });

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
                ContratoUI.notify("success", "Upgrade aplicado.");
                modal.classList.remove("show");
                window.showStep(2);
            }
        } catch (e) {
            ContratoUI.notify("error", "Error de conexión.");
            btn.disabled = false; btn.innerHTML = "Aceptar upgrade";
        }
    });

    $elId("btnRechazarUpgrade")?.addEventListener("click", () => { $elId("modalUpgrade").classList.remove("show"); window.showStep(2); });
    $elId("cerrarUpgrade")?.addEventListener("click", () => $elId("modalUpgrade").classList.remove("show"));

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
    const dropDireccion = $elId("dropDireccion");
    const dropKm = $elId("dropKm");

    const getCostoKmDropoff = () => parseFloat($elId("deliveryPrecioKm")?.value || 15);

    const enviarDropoffAPI = async (isCustom, kms, precioKmActual) => {
        try {
            await ContratoAPI.postJSON('/admin/contrato/cargo-variable', {
                id_reservacion: window.ID_RESERVACION,
                id_contrato: window.ID_CONTRATO,
                id_concepto: 6,
                destino: isCustom ? (dropDireccion?.value || "") : dropUbicacion.options[dropUbicacion.selectedIndex]?.text,
                km: kms,
                precio_km: precioKmActual,
                monto_variable: ContratoState.dropoffTotal
            });
            if (typeof window.cargarResumenBasico === 'function') window.cargarResumenBasico();
        } catch (e) { console.error("Error guardando dropoff", e); }
    };

    const enviarDropoffDebounced = debounce(enviarDropoffAPI, 300);

    function handleDropoffUpdate(inmediato = false) {
        if (!dropUbicacion) return;

        const val = dropUbicacion.value;
        const isCustom = (val === "0");

        if ($elId("dropGroupDireccion")) $elId("dropGroupDireccion").style.display = isCustom ? "block" : "none";
        if ($elId("dropGroupKm")) $elId("dropGroupKm").style.display = isCustom ? "block" : "none";

        let precioKmActual = getCostoKmDropoff();
        if (val !== "") ContratoUI.setText($elId("dropCostoKmHTML"), ContratoUI.money(precioKmActual));

        let kms = isCustom
            ? parseFloat(dropKm?.value || 0)
            : parseFloat(dropUbicacion.options[dropUbicacion.selectedIndex]?.dataset.km || 0);

        setState("dropoffTotal", kms * precioKmActual);
        ContratoUI.setText($elId("dropTotalHTML"), ContratoUI.money(ContratoState.dropoffTotal));

        const card = document.querySelector('.cargo-item[data-id="6"]');
        if (card) card.dataset.monto = ContratoState.dropoffTotal;

        actualizarTotalServicios();

        if (inmediato) enviarDropoffAPI(isCustom, kms, precioKmActual);
        else enviarDropoffDebounced(isCustom, kms, precioKmActual);
    }

    dropSwitch?.addEventListener("change", async () => {
        const isOn = dropSwitch.checked;
        if (dropoffFields) dropoffFields.style.display = isOn ? "block" : "none";

        const card = document.querySelector('.cargo-item[data-id="6"]');
        if (card) card.classList.toggle("active", isOn);

        if (!isOn) {
            setState("dropoffTotal", 0);
            if (card) card.dataset.monto = "0";
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

    $elId("dropoffFields")?.addEventListener("input", (e) => {
        if (['dropKm', 'dropDireccion'].includes(e.target.id)) handleDropoffUpdate(false);
    });

    if (dropSwitch && dropSwitch.checked) {
        const card = document.querySelector('.cargo-item[data-id="6"]');
        setState("dropoffTotal", parseFloat(card?.dataset.monto || 0));
        handleDropoffUpdate(true);
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

    const gridServicios = document.querySelector("#serviciosGrid");

    if (gridServicios) {
        gridServicios.addEventListener("click", (e) => {
            const btn = e.target;
            if (!btn.classList.contains("mas") && !btn.classList.contains("menos")) return;

            const card = btn.closest(".card-servicio");
            const idServicio = card.dataset.id;
            const cantEl = card.querySelector(".cantidad");
            let cant = parseInt(cantEl.textContent);

            if (btn.classList.contains("mas")) cant++;
            else if (cant > 0) cant--;

            cantEl.textContent = cant;

            actualizarTotalServicios();

            // 3. Limpiamos el temporizador SOLO de este servicio específico
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

                    setTimeout(() => {
                        if (typeof window.cargarResumenBasico === 'function') {
                            window.cargarResumenBasico();
                        }
                    }, 150);

                } catch (err) {
                    console.error("Error al guardar el servicio:", err);
                    if (typeof window.cargarResumenBasico === 'function') {
                        window.cargarResumenBasico();
                    }
                }
            }, 400);
        });
    }

    // ================================ PASO 3: SEGUROS ===============================

    function recalcularTotalProtecciones() {
        const display = $elId("total_seguros");
        const btnGo = $elId("go4");

        let subtotalPorDia = 0;
        let haySeleccion = false;

        // 1. Buscamos si hay un paquete completo seleccionado (radio button)
        const packActive = document.querySelector(".input-paquete:checked");

        if (packActive) {
            subtotalPorDia = parseFloat(packActive.closest(".seguro-item").dataset.precio || 0);
            haySeleccion = true;
        } else {
            // 2. Si no hay paquete, sumamos las protecciones individuales (checkboxes)
            const individualesActivos = document.querySelectorAll(".switch-individual:checked");
            if (individualesActivos.length > 0) {
                haySeleccion = true;
                individualesActivos.forEach(checkbox => {
                    subtotalPorDia += parseFloat(checkbox.closest(".individual-item").dataset.precio || 0);
                });
            }
        }

        if (display) {
            const diasRenta = parseInt($elId("detDiasRenta")?.textContent || 1);
            display.textContent = ContratoUI.money(subtotalPorDia * diasRenta);
        }

        // 3. Lógica para obligar a seleccionar (Habilitar/Deshabilitar botón de continuar)
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
    }

    // --- LÓGICA PARA CAMBIAR VISTAS (PAQUETES VS INDIVIDUALES) ---
    const btnVerPaquetes = $elId('btnVerPaquetes');
    const btnVerIndividuales = $elId('btnVerIndividuales');
    const btnToggleVista = $elId('btnToggleVista');
    const btnContinuar = $elId('go4');

    const vistaPaquetes = $elId('vista-paquetes');
    const vistaIndividuales = $elId('vista-individuales');

    // Función centralizada para alternar las vistas
    function cambiarVistaProtecciones(vistaDestino) {
        if (vistaDestino === 'paquetes') {
            if (vistaIndividuales) vistaIndividuales.style.display = 'none';
            if (vistaPaquetes) vistaPaquetes.style.display = 'block';

            // Actualizar textos y estilos
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

            // Actualizar textos y estilos
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

    // Conectar botones superiores
    if (btnVerPaquetes) btnVerPaquetes.addEventListener('click', () => cambiarVistaProtecciones('paquetes'));
    if (btnVerIndividuales) btnVerIndividuales.addEventListener('click', () => cambiarVistaProtecciones('individuales'));

    // Conectar botón inferior
    if (btnToggleVista) {
        btnToggleVista.addEventListener('click', function () {
            const vistaActual = (vistaPaquetes && vistaPaquetes.style.display !== 'none') ? 'individuales' : 'paquetes';
            cambiarVistaProtecciones(vistaActual);
        });
    }

    // --- LÓGICA DE SELECCIÓN PARA PAQUETES (Radio Buttons) ---
    if (vistaPaquetes) {
        vistaPaquetes.addEventListener("change", async (e) => {
            if (e.target.classList.contains("input-paquete")) {
                const inputPaquete = e.target;
                const label = inputPaquete.closest(".seguro-item");

                // Si selecciona un paquete, desmarcamos todos los individuales
                document.querySelectorAll(".switch-individual").forEach(checkbox => {
                    checkbox.checked = false;
                });

                recalcularTotalProtecciones();

                try {
                    await ContratoAPI.postJSON(`/admin/contrato/seguros`, {
                        id_reservacion: window.ID_RESERVACION,
                        id_paquete: inputPaquete.value,
                        precio_por_dia: label.dataset.precio
                    });
                    if (typeof window.cargarResumenBasico === 'function') window.cargarResumenBasico();
                } catch (err) { console.error(err); }
            }
        });
    }

    // --- LÓGICA DE SELECCIÓN PARA INDIVIDUALES (Checkboxes) ---
    if (vistaIndividuales) {
        vistaIndividuales.addEventListener("change", async (e) => {
            if (e.target.classList.contains("switch-individual")) {
                const checkbox = e.target;
                const label = checkbox.closest(".individual-item");
                const estaPrendido = checkbox.checked;

                // Si prende un individual, desmarcamos los paquetes completos
                if (estaPrendido) {
                    document.querySelectorAll(".input-paquete").forEach(radio => radio.checked = false);
                }

                recalcularTotalProtecciones();

                try {
                    if (!estaPrendido) {
                        // Si se apagó, lo borramos
                        await ContratoAPI.deleteJSON(`/admin/contrato/seguros-individuales`, {
                            id_reservacion: window.ID_RESERVACION,
                            id_seguro: checkbox.dataset.id || checkbox.value
                        });
                    } else {
                        // Si se prendió, lo agregamos
                        await ContratoAPI.postJSON(`/admin/contrato/seguros-individuales`, {
                            id_reservacion: window.ID_RESERVACION,
                            id_seguro: checkbox.dataset.id || checkbox.value,
                            precio_por_dia: label.dataset.precio
                        });
                    }
                    if (typeof window.cargarResumenBasico === 'function') window.cargarResumenBasico();
                } catch (err) { console.error(err); }
            }
        });
    }

    // Ejecutar al cargar para configurar estado inicial
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

    $el("#go2")?.addEventListener("click", async () => {
        if (!obtenerVehiculoSeleccionadoId()) {
            return ContratoUI.notify("error", "Debes seleccionar un vehiculo antes de continuar.");
        }

        const btn = $elId("go2");
        const textoOriginal = btn.innerHTML;
        btn.innerHTML = "Cargando oferta...";
        btn.style.pointerEvents = "none";

        try {
            const data = await ContratoAPI.getJSON(`/admin/contrato/${window.ID_RESERVACION}/oferta-upgrade`);
            btn.innerHTML = textoOriginal;
            btn.style.pointerEvents = "auto";

            if (!data.success || !data.categoria) return window.showStep(2);

            mostrarModalOferta(data.categoria);
        } catch (e) {
            console.error(e);
            btn.innerHTML = textoOriginal;
            btn.style.pointerEvents = "auto";
            window.showStep(2);
        }
    });

    $el("#go3")?.addEventListener("click", () => {
        if (typeof guardarDeliverySeguro === 'function') guardarDeliverySeguro(true);
        setTimeout(() => {
            if (typeof window.cargarResumenBasico === 'function') window.cargarResumenBasico();
        }, 150);
        precargarPaso4();
        window.showStep(3);
    });

    $el("#go4")?.addEventListener("click", (e) => {
        e.preventDefault();

        const elInicial = $elId("contratoInicial");
        const idReservacion = window.ID_RESERVACION || (elInicial ? elInicial.dataset.idReservacion : null);

        if (!idReservacion) return ContratoUI.notify("error", "Error: ID de reservación perdido.");

        const paqueteSeleccionado = document.querySelector(".input-paquete:checked");
        const individualSeleccionado = document.querySelector(".switch-individual:checked");

        if (!paqueteSeleccionado && !individualSeleccionado) {
            return ContratoUI.notify("warning", "Selecciona al menos un paquete o una protección para continuar.");
        }

        localStorage.setItem(`contratoPasoActual_${idReservacion}`, '4');

        const btn = e.currentTarget;
        btn.innerHTML = "Cargando Paso 4...";
        btn.style.pointerEvents = "none";

        precargarPaso4();
        window.location.href = `/admin/contrato2/${idReservacion}`;
    });

    $el("#back1")?.addEventListener("click", () => window.showStep(1));
    $el("#back2")?.addEventListener("click", () => window.showStep(2));
    $el("#back3")?.addEventListener("click", () => window.showStep(3));

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
});
