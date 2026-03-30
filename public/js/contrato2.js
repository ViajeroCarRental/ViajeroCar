// Pasos de 4 al 6
/**
 * MÓDULO 0: UTILIDADES GLOBALES Y CACHÉ DOM
 */
const ContratoUI = {
    DOM: {},
    cacheDOM: function () {
        this.DOM = {
            app: document.getElementById("contratoApp"),
            totalCargos: document.getElementById("total_cargos"),
            cargosGrid: document.getElementById("cargosGrid"),
            formDoc: document.getElementById("formDocumentacion"),
            payBody: document.getElementById("payBody"),
            modalPagos: document.getElementById("mb"),
            payTabs: document.getElementById("payTabs"),
            panes: document.querySelectorAll("[data-pane]")
        };
    },
    initGlobalEvents: function () {
        // Botón Info Licencia
        document.getElementById("btnInfoLicencia")?.addEventListener("click", () => {
            const msg = `
                <div style="text-align: left; font-size: 14px; line-height: 1.6; color: #333;">
                    <p style="margin-top: 0;"><b>Este verificador es solo una herramienta de apoyo.</b></p>
                    <p>No cuenta con acceso a bases oficiales del gobierno.</p>
                    <p style="margin-bottom: 5px;">Para confirmar la validez de tu licencia, consulta siempre:</p>
                    <ul style="margin-top: 0; padding-left: 20px; color: #4b5563;">
                        <li>Los portales oficiales de gobierno de tu país.</li>
                        <li>O instituciones externas autorizadas.</li>
                    </ul>
                </div>`;
            if (window.alertify) {
                alertify.alert("⚠️ Aviso Importante", msg).set('label', 'Entendido');
            } else {
                alert("Este verificador es solo una herramienta de apoyo.\nNo cuenta con acceso a bases oficiales.");
            }
        });
    },
    mostrarNotificacion: function (tipo, htmlMsg) {
        if (window.lastAlert === htmlMsg) return;
        window.lastAlert = htmlMsg;
        setTimeout(() => { window.lastAlert = null; }, 3000);

        if (typeof alertify !== 'undefined') {
            if (tipo === 'error') alertify.error(htmlMsg);
            else if (tipo === 'warning') alertify.warning(htmlMsg);
            else alertify.success(htmlMsg);
        } else {
            alert(htmlMsg.replace(/<[^>]*>?/gm, ''));
        }
    }
};

/**
 * MÓDULO 1: PASO 4 - VEHÍCULOS, CARGOS Y DROPOFF
 */
const ContratoPaso4 = {
    dropoffTotal: parseFloat(document.querySelector('.cargo-item[data-id="6"]')?.dataset.monto || 0),

    init: function () {
        window.dropoffTotal = this.dropoffTotal; // Sincronizar variable global legada

        // Vehículos
        document.getElementById("editVeh")?.addEventListener("click", (e) => {
            e.preventDefault();
            const idCat = ContratoUI.DOM.app?.dataset.idCategoria || window.$("#detCategoria").textContent;
            window.abrirModalVehiculos(idCat);
        });
        document.getElementById("cerrarModalVehiculos")?.addEventListener("click", () => document.getElementById("modalVehiculos").classList.remove("show-modal"));
        document.getElementById("cerrarModalVehiculos2")?.addEventListener("click", () => document.getElementById("modalVehiculos").classList.remove("show-modal"));
        document.getElementById("selectCategoriaModal")?.addEventListener("change", (e) => this.cambiarCategoria(e));

        // Cargos Grid
        ContratoUI.DOM.cargosGrid?.addEventListener("click", (e) => {
            const sw = e.target.closest(".switch");
            if (!sw) return;
            const card = sw.closest(".cargo-item");
            const isOn = sw.classList.toggle("on");
            if (card) card.classList.toggle("active", isOn);
            this.apiGuardarCargo(card.dataset.id).then(() => this.recalcularTotal());
        });

        // Gasolina
        const switchGasLit = document.getElementById("switchGasLit");
        if (switchGasLit) {
            switchGasLit.addEventListener("click", () => {
                const isOn = switchGasLit.classList.toggle("on");
                const gasCant = document.getElementById("gasCantL");
                if (isOn) {
                    this.toggleCargoState(2);
                    this.apiGuardarCargo(2).then(() => this.apiGuardarCargo(5));
                } else {
                    if (gasCant) gasCant.value = "";
                    const htmlTotal = document.getElementById("gasTotalHTML");
                    if (htmlTotal) htmlTotal.textContent = "$0.00 MXN";
                    this.updateCargoMonto(5, 0);
                    this.apiGuardarCargo(5, { litros: 0, precio_litro: 0, monto_variable: 0 }).then(() => this.apiGuardarCargo(2));
                }
                const gasInputs = document.getElementById("gasLitrosInputs");
                if (gasInputs) gasInputs.style.display = isOn ? "block" : "none";
                this.recalcularTotal();
            });

            document.getElementById("gasCantL")?.addEventListener("input", () => this.updateGas());
            document.getElementById("gasPrecioL")?.addEventListener("input", () => this.updateGas());
        }

        // Dropoff
        // const dropSwitch = document.getElementById("switchDropoff");
        // dropSwitch?.addEventListener("click", async () => {
        //     const isOn = dropSwitch.classList.toggle("on");
        //     document.getElementById("dropoffFields").style.display = isOn ? "block" : "none";

        //     if (!isOn) {
        //         document.getElementById("dropUbicacion").value = "";
        //         document.getElementById("dropDireccion").value = "";
        //         document.getElementById("dropKm").value = "";
        //         const totHTML = document.getElementById("dropTotalHTML");
        //         if (totHTML) totHTML.textContent = "$0.00 MXN";

        //         window.dropoffTotal = 0;
        //         this.updateCargoMonto(6, 0);

        //         try {
        //             await fetch('/admin/reservacion/delivery/guardar', {
        //                 method: "POST",
        //                 headers: { "Content-Type": "application/json", "X-CSRF-TOKEN": window.csrfToken },
        //                 body: JSON.stringify({
        //                     id_reservacion: window.ID_RESERVACION, delivery_activo: 0, delivery_ubicacion: null,
        //                     delivery_direccion: null, delivery_km: 0, delivery_precio_km: 0, delivery_total: 0
        //                 })
        //             });
        //         } catch (e) { console.error("Error apagando delivery", e); }
        //     } else {
        //         this.handleDropoffUpdate();
        //         return;
        //     }
        //     this.recalcularTotal();
        // });

        // Dropoff
        const dropSwitch = document.getElementById("switchDropoff");
        dropSwitch?.addEventListener("click", async () => {
            const isOn = dropSwitch.classList.toggle("on");
            document.getElementById("dropoffFields").style.display = isOn ? "block" : "none";

            if (!isOn) {
                document.getElementById("dropUbicacion").value = "";
                document.getElementById("dropDireccion").value = "";
                document.getElementById("dropKm").value = "";
                const totHTML = document.getElementById("dropTotalHTML");
                if (totHTML) totHTML.textContent = "$0.00 MXN";

                window.dropoffTotal = 0;
                this.updateCargoMonto(6, 0);

                try {
                    await fetch('/admin/reservacion/delivery/guardar', {
                        method: "POST",
                        headers: { "Content-Type": "application/json", "X-CSRF-TOKEN": window.csrfToken },
                        body: JSON.stringify({
                            id_reservacion: window.ID_RESERVACION,
                            delivery_activo: 0,
                            delivery_ubicacion: null,
                            delivery_direccion: null,
                            delivery_km: 0,
                            delivery_precio_km: 0,
                            delivery_total: 0
                        })
                    });

                    await this.apiGuardarCargo(6, {
                        id_reservacion: window.ID_RESERVACION,
                        monto_variable: 0,
                        km: 0,
                        destino: null,
                        precio_km: 0
                    });

                } catch (e) { console.error("Error apagando delivery", e); }

                this.recalcularTotal();
                await new Promise(r => setTimeout(r, 150));
                if (typeof window.cargarResumenBasico === 'function') window.cargarResumenBasico();

            } else {
                this.handleDropoffUpdate();
            }
        });

        document.getElementById("dropoffFields")?.addEventListener("input", (e) => {
            if (['dropKm', 'dropDireccion'].includes(e.target.id)) this.handleDropoffUpdate();
        });
        document.getElementById("dropUbicacion")?.addEventListener("change", () => this.handleDropoffUpdate());

        // Asegurar estado activo visual de cards
        document.querySelectorAll(".cargo-item").forEach(card => {
            const sw = card.querySelector(".switch");
            if (sw && sw.classList.contains("on")) card.classList.add("active");
        });
    },

    // --- Funciones Vehículos ---
    abrirModalVehiculo: function (e) {
        if (e) e.preventDefault();

        const idCat = ContratoUI.DOM.app?.dataset.idCategoria || document.getElementById("detCategoria")?.textContent;

        if (typeof window.abrirModalVehiculos === 'function') {
            window.abrirModalVehiculos(idCat);
        } else {
            console.error("No se encontró la función global abrirModalVehiculos");
        }
    },

    cambiarCategoria: async function (e) {
        const idCat = e.target.value;
        if (!idCat) return;

        const uiVehiculo = document.getElementById("vehiculoAsignadoUI");
        if (uiVehiculo) uiVehiculo.innerHTML = `<div style="padding:15px;background:#f8fafc;border:1px dashed #cbd5e1;border-radius:8px;text-align:center;color:#475569;font-size:13px;">🚗 Selecciona un vehículo para esta categoría</div>`;

        const selectAssign = document.getElementById("vehAssign");
        if (selectAssign) selectAssign.innerHTML = `<option value="">Seleccione un vehículo</option>`;
        if (window.ContratoStore) window.ContratoStore.set('vehiculoAsignado', null);

        ["#detModelo", "#detMarca", "#detTransmision", "#detPasajeros", "#detPuertas", "#detKm", "#resumenVehCompacto"].forEach(id => {
            const el = document.querySelector(id);
            if (el) el.innerText = "—";
        });
        const imgLateral = document.getElementById("resumenImgVeh");
        if (imgLateral) imgLateral.src = "/img/default-car.png";

        try {
            const resp = await fetch(`/admin/contrato/${window.ID_RESERVACION}/actualizar-categoria`, {
                method: "POST",
                headers: { "Content-Type": "application/json", "X-CSRF-TOKEN": window.csrfToken },
                body: JSON.stringify({ id_categoria: idCat })
            });

            if (resp.ok) {
                if (ContratoUI.DOM.app) ContratoUI.DOM.app.dataset.idCategoria = idCat;

                const switchDropoff = document.getElementById("switchDropoff");
                if (switchDropoff && switchDropoff.classList.contains("on")) {
                    switchDropoff.classList.remove("on");
                    document.getElementById("dropoffFields").style.display = "none";
                    ['dropUbicacion', 'dropDireccion', 'dropKm'].forEach(id => {
                        const el = document.getElementById(id);
                        if (el) el.value = "";
                    });

                    const dropTotalHTML = document.getElementById("dropTotalHTML");
                    if (dropTotalHTML) dropTotalHTML.textContent = "$0.00 MXN";

                    window.dropoffTotal = 0;
                    this.updateCargoMonto(6, 0);
                    document.querySelector('.cargo-item[data-id="6"]')?.classList.remove("active");

                    try {
                        await fetch('/admin/reservacion/delivery/guardar', {
                            method: "POST",
                            headers: { "Content-Type": "application/json", "X-CSRF-TOKEN": window.csrfToken },
                            body: JSON.stringify({
                                id_reservacion: window.ID_RESERVACION, delivery_activo: 0, delivery_km: 0, delivery_precio_km: 0, delivery_total: 0
                            })
                        });
                        this.recalcularTotal();
                    } catch (err) { console.error("Error BD Dropoff", err); }

                    if (window.alertify) {
                        alertify.warning("🚚 <b>Delivery Removido</b><br>Al cambiar la categoría, el costo por Km cambia. Vuelve a configurar el servicio de entrega.");
                    }
                }

                if (typeof window.cargarResumenBasico === 'function') {
                    await window.cargarResumenBasico();
                }

                if (typeof window.cargarVehiculosCategoriaModal === 'function') {
                    await window.cargarVehiculosCategoriaModal(idCat);
                }
            }
        } catch (err) { console.error("Error actualizando categoría", err); }
    },

    // --- Utilidades Paso 4 ---
    toggleCargoState: (id) => document.querySelector(`.cargo-item[data-id="${id}"] .switch`)?.classList.add("on"),
    updateCargoMonto: (id, monto) => { const card = document.querySelector(`.cargo-item[data-id="${id}"]`); if (card) card.dataset.monto = monto; },

    apiGuardarCargo: async function (idConcepto, bodyData = {}) {
        const payload = { id_contrato: window.ID_CONTRATO || null, id_reservacion: window.ID_RESERVACION, id_concepto: idConcepto, ...bodyData };
        const url = bodyData.monto_variable !== undefined ? '/admin/contrato/cargo-variable' : '/admin/contrato/cargos';
        try {
            return await fetch(url, {
                method: "POST",
                headers: { "Content-Type": "application/json", "X-CSRF-TOKEN": window.csrfToken },
                body: JSON.stringify(payload)
            });
        } catch (err) {
            console.error("Error guardando cargo", err);
        }
    },

    updateGas: function () {
        const cant = document.getElementById("gasCantL")?.value || 0;
        const prec = document.getElementById("gasPrecioL")?.value || 0;
        const total = parseFloat(cant) * parseFloat(prec);
        const elTotal = document.getElementById("gasTotalHTML");
        if (elTotal) elTotal.textContent = window.money ? window.money(total) : `$${total.toFixed(2)} MXN`;

        this.updateCargoMonto(5, total);
        this.apiGuardarCargo(5, { litros: parseFloat(cant), precio_litro: parseFloat(prec), monto_variable: total }).then(() => this.recalcularTotal());
    },

    // handleDropoffUpdate: function () {
    //     const dropUb = document.getElementById("dropUbicacion");
    //     if (!dropUb) return;

    //     const val = dropUb.value;
    //     const isCustom = (val === "0"); 

    //     const dropKm = document.getElementById("dropKm");
    //     const dropDir = document.getElementById("dropDireccion");

    //     document.getElementById("dropGroupDireccion").style.display = isCustom ? "block" : "none";
    //     document.getElementById("dropGroupKm").style.display = isCustom ? "block" : "none";
    //     document.getElementById("dropCostoKm").style.display = val === "" ? "none" : "block";

    //     let precioKmActual = parseFloat(document.getElementById("deliveryPrecioKm")?.value || 15);
    //     if (val !== "") {
    //         const elCostoHTML = document.getElementById("dropCostoKmHTML");
    //         if (elCostoHTML) elCostoHTML.innerText = window.money ? window.money(precioKmActual) : `$${precioKmActual.toFixed(2)}`;
    //     }

    //     let kms = isCustom ? parseFloat(dropKm.value || 0) : parseFloat(dropUb.options[dropUb.selectedIndex]?.dataset.km || 0);
    //     window.dropoffTotal = kms * precioKmActual;

    //     const htmlTot = document.getElementById("dropTotalHTML");
    //     if (htmlTot) htmlTot.textContent = window.money ? window.money(window.dropoffTotal) : `$${window.dropoffTotal.toFixed(2)} MXN`;

    //     this.updateCargoMonto(6, window.dropoffTotal);

    //     this.apiGuardarCargo(6, {
    //         id_reservacion: window.ID_RESERVACION,
    //         destino: isCustom ? dropDir.value : dropUb.options[dropUb.selectedIndex]?.text,
    //         km: kms, 
    //         precio_km: precioKmActual, 
    //         monto_variable: window.dropoffTotal
    //     }).then(() => this.recalcularTotal());
    // },

    handleDropoffUpdate: function () {
        const dropUb = document.getElementById("dropUbicacion");
        if (!dropUb) return;

        const val = dropUb.value;
        const isCustom = (val === "0");

        const dropKm = document.getElementById("dropKm");
        const dropDir = document.getElementById("dropDireccion");

        document.getElementById("dropGroupDireccion").style.display = isCustom ? "block" : "none";
        document.getElementById("dropGroupKm").style.display = isCustom ? "block" : "none";
        document.getElementById("dropCostoKm").style.display = val === "" ? "none" : "block";

        let precioKmActual = parseFloat(document.getElementById("deliveryPrecioKm")?.value || 15);
        if (val !== "") {
            const elCostoHTML = document.getElementById("dropCostoKmHTML");
            if (elCostoHTML) elCostoHTML.innerText = window.money ? window.money(precioKmActual) : `$${precioKmActual.toFixed(2)}`;
        }

        let kms = isCustom ? parseFloat(dropKm?.value || 0) : parseFloat(dropUb.options[dropUb.selectedIndex]?.dataset.km || 0);
        window.dropoffTotal = kms * precioKmActual;

        const htmlTot = document.getElementById("dropTotalHTML");
        if (htmlTot) htmlTot.textContent = window.money ? window.money(window.dropoffTotal) : `$${window.dropoffTotal.toFixed(2)} MXN`;

        this.updateCargoMonto(6, window.dropoffTotal);

        if (window.dropoffTotal <= 0) return;

        this.apiGuardarCargo(6, {
            id_reservacion: window.ID_RESERVACION,
            destino: isCustom ? dropDir?.value : dropUb.options[dropUb.selectedIndex]?.text,
            km: kms,
            precio_km: precioKmActual,
            monto_variable: window.dropoffTotal
        }).then(async () => {
            this.recalcularTotal();
            await new Promise(r => setTimeout(r, 150));
            if (typeof window.cargarResumenBasico === 'function') window.cargarResumenBasico();
        });
    },

    // recalcularTotal: function () {
    //     const tc = ContratoUI.DOM.totalCargos;
    //     if (!tc) return;
    //     tc.style.opacity = "0.5";

    //     const gasC = parseFloat(document.getElementById("gasCantL")?.value || 0);
    //     const gasP = parseFloat(document.getElementById("gasPrecioL")?.value || 0);
    //     let t = window.dropoffTotal + (gasC * gasP);

    //     document.querySelectorAll(".cargo-item .switch.on").forEach(sw => {
    //         const card = sw.closest(".cargo-item");
    //         if (!["5", "6"].includes(card.dataset.id)) t += parseFloat(card.dataset.monto || 0);
    //     });

    //     tc.textContent = window.money ? window.money(t) : `$${t.toFixed(2)} MXN`;
    //     tc.style.opacity = "1";
    //     setTimeout(() => { if (typeof window.cargarResumenBasico === 'function') window.cargarResumenBasico(); }, 400);
    // }

    recalcularTotal: function () {
        const tc = ContratoUI.DOM.totalCargos;
        if (!tc) return;
        tc.style.opacity = "0.5";

        const gasC = parseFloat(document.getElementById("gasCantL")?.value || 0);
        const gasP = parseFloat(document.getElementById("gasPrecioL")?.value || 0);
        let t = window.dropoffTotal + (gasC * gasP);

        document.querySelectorAll(".cargo-item .switch.on").forEach(sw => {
            const card = sw.closest(".cargo-item");
            if (!["5", "6"].includes(card.dataset.id)) t += parseFloat(card.dataset.monto || 0);
        });

        tc.textContent = window.money ? window.money(t) : `$${t.toFixed(2)} MXN`;
        tc.style.opacity = "1";
    },
};

/**
 * MÓDULO 2: PASO 5 - DOCUMENTACIÓN Y VALIDACIÓN
 */
const ContratoPaso5 = {
    ultimaCantidadMenores: -1,
    timeoutValidacion: null,
    controller: null,
    requestId: 0,

    init: function () {
        this.delegarEventosValidacion();
        this.configurarPreviews();
        ContratoUI.DOM.formDoc?.addEventListener("submit", (e) => this.enviarFormulario(e));

        if (ContratoUI.DOM.formDoc) {
            ContratoUI.DOM.formDoc.setAttribute("novalidate", "true");
            ContratoUI.DOM.formDoc.addEventListener("submit", (e) => this.enviarFormulario(e));
        }
    },

    // DELEGACIÓN DE EVENTOS
    delegarEventosValidacion: function () {
        const form = ContratoUI.DOM.formDoc;
        if (!form) return;

        const selectoresInput = `input[name*="fecha_nacimiento"], input[name*="numero_licencia"], input[name*="numero_identificacion"], input[name*="fecha_emision"], input[name*="fecha_vencimiento"], input[name*="contacto_emergencia"]`;
        const selectoresCambio = `select[name*="emite_licencia"], select[name*="pais"], select[name*="id_pais"], select[name*="tipo_identificacion"]`;

        const quitarAlertaRoja = (t) => {
            if (!t.style) return;

            const esRojo = t.style.border.includes("ef4444") || t.style.border.includes("239, 68, 68");

            if (esRojo && t.value.trim().length > 0) {
                t.style.border = "";
                t.style.backgroundColor = "";
            }

            if (t.type === 'file' && t.files.length > 0) {
                const uploader = t.closest('.uploader');
                if (uploader && (uploader.style.border.includes("ef4444") || uploader.style.border.includes("239, 68, 68"))) {
                    uploader.style.border = "";
                }
            }
        };

        form.addEventListener("input", (e) => {
            quitarAlertaRoja(e.target);

            if (e.target.name && e.target.name.includes("fecha_nacimiento")) {
                this.gestionarCobroMenoresExtra();
            }

            if (e.target.matches(selectoresInput)) this.dispararValidacion(e.target);
        });

        form.addEventListener("change", (e) => {
            const t = e.target;
            quitarAlertaRoja(t);

            if (t.name && t.name.includes("fecha_nacimiento")) {
                this.gestionarCobroMenoresExtra();
            }

            if (t.matches(selectoresCambio)) {
                this.limpiarDependientes(t);
                this.dispararValidacion(t);
            } else if (t.matches(selectoresInput)) {
                this.dispararValidacion(t);
            }
        });
    },

    dispararValidacion: function (target) {
        clearTimeout(this.timeoutValidacion);
        this.timeoutValidacion = setTimeout(() => this.procesarValidacionServidor(target), 400);
    },

    limpiarDependientes: function (selectInput) {
        const nameAttr = selectInput.name;
        const match = nameAttr.match(/^(conductores\[\d+\])/);
        const prefix = match ? match[1] : "";
        let targets = [];

        if (nameAttr.includes('id_pais') || nameAttr.includes('emite_licencia')) {
            targets = ['numero_licencia', 'fecha_emision', 'fecha_vencimiento'].map(n => document.querySelector(`[name="${prefix}[${n}]"]`));
        } else if (nameAttr.includes('tipo_identificacion')) {
            targets = ['numero_identificacion', 'fecha_vencimiento'].map(n => document.querySelector(`[name="${prefix}[${n}]"]`));
        }

        targets.forEach(el => {
            if (el) { el.value = ""; el.style.border = ""; el.style.backgroundColor = ""; }
        });
    },

    procesarValidacionServidor: async function (inputTrigger) {
        if (!inputTrigger || !inputTrigger.name) return;

        const currentRequest = ++this.requestId;
        const nameAttr = inputTrigger.name;
        const match = nameAttr.match(/^(conductores\[(\d+)\])/);
        const prefix = match ? match[1] : "conductores[0]";
        const getField = (baseName) => document.querySelector(`[name="${prefix}[${baseName}]"]`);

        const esContextoLicencia = nameAttr.includes('licencia') || nameAttr.includes('emision') || nameAttr.includes('id_pais') || (nameAttr.includes('fecha_vencimiento') && !nameAttr.includes('_id'));

        const inputNac = getField('fecha_nacimiento');
        const inputNum = esContextoLicencia ? getField('numero_licencia') : getField('numero_identificacion');
        const inputVen = esContextoLicencia ? getField('fecha_vencimiento') : getField('fecha_vencimiento_id');
        const inputEmi = getField('fecha_emision');
        const selectPais = getField('id_pais');
        const selectTipoID = getField('tipo_identificacion');
        const inputContacto = getField('contacto_emergencia');

        const setEstado = (input, tipo) => {
            if (!input) return;
            if (tipo === 'ok') { input.style.border = "1px solid #10b981"; input.style.backgroundColor = "#ecfdf5"; }
            else if (tipo === 'error') { input.style.border = "2px solid #ef4444"; input.style.backgroundColor = "#fef2f2"; }
            else if (tipo === 'warning') { input.style.border = "2px solid #eab308"; input.style.backgroundColor = "#fefce8"; }
            else { input.style.border = ""; input.style.backgroundColor = ""; }
        };

        // Regla rápida de contacto
        if (nameAttr.includes('contacto_emergencia')) {
            const val = inputContacto ? inputContacto.value.replace(/\D/g, '') : '';
            if (!val) { setEstado(inputContacto, ''); return; }
            if (val.length !== 10) {
                setEstado(inputContacto, 'error');
                ContratoUI.mostrarNotificacion('error', "<b>⚠️ Teléfono Inválido</b><br>Debe tener exactamente 10 dígitos.");
            } else setEstado(inputContacto, 'ok');
            return;
        }

        const payload = {
            tipo: esContextoLicencia ? 'licencia' : (selectTipoID?.value || 'ine'),
            numero: inputNum?.value.trim() || '',
            id_pais: esContextoLicencia ? (selectPais?.value || '') : 'MX',
            fecha_nacimiento: inputNac?.value || null,
            fecha_emision: inputEmi?.value || null,
            fecha_vencimiento: inputVen?.value || null
        };

        [inputNum, inputEmi, inputVen, inputNac].forEach(i => setEstado(i, ''));
        if (payload.numero) setEstado(inputNum, 'ok');
        if (payload.fecha_nacimiento) setEstado(inputNac, 'ok');
        if (payload.fecha_emision) setEstado(inputEmi, 'ok');
        if (payload.fecha_vencimiento) setEstado(inputVen, 'ok');

        if (!payload.numero && !payload.fecha_nacimiento && !payload.fecha_emision && !payload.fecha_vencimiento) return;

        try {
            if (this.controller) this.controller.abort();
            this.controller = new AbortController();

            const resp = await fetch('/admin/contrato/validar-documento-maestro', {
                method: "POST", headers: { "Content-Type": "application/json", "X-CSRF-TOKEN": window.csrfToken },
                body: JSON.stringify(payload), signal: this.controller.signal
            });

            const res = await resp.json();
            if (currentRequest !== this.requestId) return;

            const msgSrv = res.msg?.[0] || "Dato inválido";
            const borrarYTemblar = (inp, borrar = true) => {
                if (!inp) return;
                if (borrar) inp.value = "";
                try { inp.animate([{ transform: 'translate3d(0,0,0)' }, { transform: 'translate3d(-4px,0,0)' }, { transform: 'translate3d(4px,0,0)' }, { transform: 'translate3d(-4px,0,0)' }, { transform: 'translate3d(4px,0,0)' }, { transform: 'translate3d(0,0,0)' }], { duration: 400 }); } catch (e) { }
            };

            // Regla JS de Edad
            if (inputNac && inputNac.value && inputTrigger.name.includes('nacimiento')) {
                const b = new Date(inputNac.value + "T00:00:00"), h = new Date();
                let e = h.getFullYear() - b.getFullYear();
                if (h.getMonth() < b.getMonth() || (h.getMonth() === b.getMonth() && h.getDate() < b.getDate())) e--;

                if (e < 18) {
                    setEstado(inputNac, 'error');
                    borrarYTemblar(inputNac);
                    ContratoUI.mostrarNotificacion('error', "<b>🚫 Edad no permitida</b><br>El conductor debe tener al menos 18 años.");
                    this.gestionarCobroMenoresExtra(); // Recalcula porque acabamos de borrar la fecha prohibida
                    return; // Detenemos la ejecución aquí, no va al servidor
                } else if (e >= 18 && e <= 24) {
                    setEstado(inputNac, 'warning'); // Amarillo: Conductor Joven
                } else {
                    setEstado(inputNac, 'ok'); // Verde: Adulto estándar
                }
            }

            if (res.status === 'vencido') {
                setEstado(inputVen, 'error'); borrarYTemblar(inputVen); ContratoUI.mostrarNotificacion('error', `<b>🚫 Expirado</b><br>${msgSrv}`);
            } else if (res.status === 'error_fecha') {
                setEstado(inputEmi, 'error'); setEstado(inputVen, 'error'); borrarYTemblar(inputEmi); borrarYTemblar(inputVen);
                ContratoUI.mostrarNotificacion('error', `<b>🚫 Error Fechas</b><br>${msgSrv}`);
            } else if (res.status === 'warning') {
                setEstado(inputVen, 'warning'); ContratoUI.mostrarNotificacion('warning', `<b>⚠️ Revisión</b><br>${msgSrv}`);
            }

            if (res.status === 'invalido' && payload.numero) {
                setEstado(inputNum, 'error'); borrarYTemblar(inputNum, false);
                ContratoUI.mostrarNotificacion('error', `<b>🚫 Formato Incorrecto</b><br>${msgSrv}`);
            }

        } catch (err) { if (err.name !== 'AbortError') console.error("Validación", err); }
    },

    gestionarCobroMenoresExtra: async function () {
        const inputs = document.querySelectorAll('input[name*="fecha_nacimiento"]');
        let count = 0;

        // Calculamos la edad real leyendo directamente los inputs
        inputs.forEach(inp => {
            if (inp.value.trim() !== "") {
                const b = new Date(inp.value + "T00:00:00");
                const h = new Date();
                let edad = h.getFullYear() - b.getFullYear();
                if (h.getMonth() < b.getMonth() || (h.getMonth() === b.getMonth() && h.getDate() < b.getDate())) {
                    edad--;
                }

                if (edad >= 18 && edad <= 24) {
                    count++;
                }
            }
        });

        if (this.ultimaCantidadMenores === count) return;
        this.ultimaCantidadMenores = count;

        if (count === 0) {
            const listaServicios = document.getElementById("r_servicios_lista");
            const totalServicios = document.getElementById("r_servicios_total");
            if (listaServicios) listaServicios.innerHTML = '<li class="empty">—</li>';
            if (totalServicios) totalServicios.innerText = "—";
        }

        try {
            const r = await fetch('/admin/contrato/servicios-extra', {
                method: "POST", headers: { "Content-Type": "application/json", "X-CSRF-TOKEN": window.csrfToken },
                body: JSON.stringify({ id_reservacion: window.ID_RESERVACION, id_servicio: window.ID_SERVICIO_MENOR || 0, forzar: count > 0 ? "on" : "off", cantidad: count })
            });

            if (r.ok) {
                ContratoPaso4.recalcularTotal();
                if (typeof window.cargarPaso6 === 'function') window.cargarPaso6();

                if (count > 0) {
                    ContratoUI.mostrarNotificacion('warning', `🔞 <b>Cargo Aplicado</b><br>Aplica tarifa extra por ${count} conductor(es) joven(es).`);
                } else {
                    ContratoUI.mostrarNotificacion('success', "✅ <b>Cargo Removido</b><br>Ningún conductor requiere tarifa de menor.");
                }

                if (typeof window.cargarResumenBasico === 'function') window.cargarResumenBasico();
            }
        } catch (err) { console.error("Error Menores:", err); }
    },

    configurarPreviews: function () {
        ContratoUI.DOM.formDoc?.addEventListener("change", (e) => {
            if (e.target.type === "file" && e.target.closest(".uploader")) {
                const file = e.target.files[0];
                const prev = e.target.closest(".uploader").nextElementSibling;
                if (!file || !prev?.classList.contains("preview")) return;

                if (!file.type.startsWith("image/")) {
                    prev.innerHTML = `<p style="font-size:12px;color:#666;">Archivo seleccionado</p>`; return;
                }
                const reader = new FileReader();
                reader.onload = ev => prev.innerHTML = `<div style="position:relative;display:inline-block;margin-top:10px;"><img src="${ev.target.result}" style="width:120px;height:120px;object-fit:cover;border-radius:8px;"><button type="button" style="position:absolute;top:-8px;right:-8px;background:#ef4444;color:white;border:none;border-radius:50%;width:22px;height:22px;cursor:pointer;" onclick="this.parentElement.remove();">×</button></div>`;
                reader.readAsDataURL(file);
            }
        });
    },

    limpiarErroresVisuales: function () {
        const form = ContratoUI.DOM.formDoc;
        if (!form) return;

        // Busca TODO lo que esté pintado de rojo (ef4444) o amarillo (eab308)
        const elementosPintados = form.querySelectorAll('[style*="ef4444"], [style*="rgb(239, 68, 68)"], [style*="eab308"]');

        elementosPintados.forEach(el => {
            el.style.border = "";
            el.style.backgroundColor = "";
        });
    },

    enviarFormulario: async function (e) {
        e.preventDefault();
        const form = ContratoUI.DOM.formDoc;
        const btn = document.getElementById("btnContinuarDoc");

        let detallesFaltantes = [];

        const faltantes = Array.from(form.querySelectorAll('input[required], select[required]')).filter(c => {
            const esArchivo = c.type === 'file';
            const estaVacio = esArchivo ? c.files.length === 0 : !c.value.trim();
            const elementoVisual = esArchivo ? c.closest('.uploader') : c;

            if (estaVacio) {

                if (elementoVisual) {
                    elementoVisual.style.border = "2px solid #ef4444";
                    if (!esArchivo) elementoVisual.style.backgroundColor = "#fef2f2";
                }

                const n = c.name;
                const esTitular = n.includes("[0]") ? "del Titular" : "del Adicional";
                let nombreCampo = "Dato obligatorio";

                if (n.includes("idFrente")) nombreCampo = `Foto Identificación (Frente) ${esTitular}`;
                else if (n.includes("idReverso")) nombreCampo = `Foto Identificación (Reverso) ${esTitular}`;
                else if (n.includes("licFrente")) nombreCampo = `Foto Licencia (Frente) ${esTitular}`;
                else if (n.includes("licReverso")) nombreCampo = `Foto Licencia (Reverso) ${esTitular}`;
                else if (n.includes("emision")) nombreCampo = `Fecha de Emisión ${esTitular}`;
                else if (n.includes("identificacion")) nombreCampo = `Número de ID ${esTitular}`;
                else if (n.includes("licencia") && !esArchivo) nombreCampo = `Número de Licencia ${esTitular}`;
                else if (n.includes("nacimiento")) nombreCampo = `Fecha de Nacimiento ${esTitular}`;
                else if (n.includes("nombre")) nombreCampo = `Nombre ${esTitular}`;
                else if (n.includes("paterno")) nombreCampo = `Apellido Paterno ${esTitular}`;

                detallesFaltantes.push(`• ${nombreCampo}`);
                return true;
            } else {
                if (elementoVisual && (elementoVisual.style.border.includes("ef4444") || elementoVisual.style.border.includes("239, 68, 68"))) {
                    elementoVisual.style.border = "none";
                    if (!esArchivo) elementoVisual.style.backgroundColor = "";
                }
                return false;
            }
        }).length;

        if (faltantes > 0) {
            const unicos = [...new Set(detallesFaltantes)].join("<br>");
            ContratoUI.mostrarNotificacion('warning', `<b>⚠️ Faltan Datos Obligatorios</b><br>${unicos}`);

            const primerError = form.querySelector('[style*="ef4444"], [style*="239, 68, 68"]');
            if (primerError) {
                primerError.scrollIntoView({ behavior: 'smooth', block: 'center' });
                if (primerError.tagName !== 'DIV') primerError.focus();
            }
            return;
        }

        const errores = form.querySelectorAll('input[style*="border: 2px solid rgb(239, 68, 68)"], input[style*="border: 2px solid #ef4444"]');
        if (errores.length > 0) {
            errores[0].scrollIntoView({ behavior: 'smooth', block: 'center' }); errores[0].focus();
            ContratoUI.mostrarNotificacion('error', `<b>🚫 Datos Inválidos</b><br>Hay campos en rojo. Corrígelos.`); return;
        }

        btn.disabled = true; btn.innerText = "Subiendo archivos...";
        try {
            const res = await (await fetch(form.action, { method: "POST", body: new FormData(form), headers: { 'X-CSRF-TOKEN': window.csrfToken } })).json();
            if (res.success) {
                ContratoUI.mostrarNotificacion('success', "¡Guardado exitoso!");
                window.showStep(6);
                if (typeof window.cargarPaso6 === 'function') window.cargarPaso6();
                if (window.cargarResumenBasico) window.cargarResumenBasico();
            } else {
                ContratoUI.mostrarNotificacion('error', res.error || "Error al guardar");
            }
        } catch (err) { ContratoUI.mostrarNotificacion('error', "Error de conexión"); }
        finally { btn.disabled = false; btn.innerText = "Guardar y Continuar →"; }
    }
};

/**
 * MÓDULO 3: PASO 6 - PAGOS Y ESTADO DE CUENTA
 */
const ContratoPaso6 = {
    paypalLoaded: false,

    init: function () {
        this.delegarEventosTabla();

        document.getElementById("btnAdd")?.addEventListener("click", () => this.abrirModalPago());
        document.getElementById("mx")?.addEventListener("click", () => this.cerrarModalPago());

        ContratoUI.DOM.payTabs?.addEventListener("click", (e) => {
            if (e.target.dataset.tab) this.cambiarTab(e.target.dataset.tab);
        });

        document.getElementById("pSave")?.addEventListener("click", () => this.guardarPagoManual());
    },

    cargarResumen: async function () {
        if (!document.getElementById("baseAmt")) return;
        try {
            const res = await (await fetch(`/admin/contrato/${window.ID_RESERVACION}/resumen-paso6`)).json();
            if (!res.ok) return;
            const r = res.data;

            const setTxt = (id, val) => { const el = document.getElementById(id); if (el) el.textContent = val; };
            setTxt("baseDescr", r.base.descripcion ?? "—");
            setTxt("baseAmt", window.money ? window.money(r.base.total) : r.base.total);
            setTxt("addsAmt", window.money ? window.money(r.adicionales.total) : r.adicionales.total);
            setTxt("ivaAmt", window.money ? window.money(r.totales.subtotal) : r.totales.subtotal);
            setTxt("ivaOnly", window.money ? window.money(r.totales.iva) : r.totales.iva);
            setTxt("totalContrato", window.money ? window.money(r.totales.total_contrato) : r.totales.total_contrato);

            const saldoP = parseFloat(r.totales.saldo_pendiente) || 0;
            setTxt("saldoPendiente", window.money ? window.money(saldoP) : `$${saldoP.toFixed(2)}`);

            const btnAdd = document.getElementById("btnAdd");
            if (btnAdd) {
                const parent = btnAdd.parentElement;
                if (saldoP <= 0.01) {
                    btnAdd.style.display = "none";
                    if (!parent.querySelector(".badge-liquidado")) parent.insertAdjacentHTML('beforeend', '<div class="badge-liquidado" style="color:#166534; background:#dcfce7; padding:8px 16px; border:1px solid #bbf7d0; border-radius:6px; font-weight:bold; display:inline-block;"> Cuenta Liquidada</div>');
                } else {
                    btnAdd.style.display = "inline-block";
                    parent.querySelector(".badge-liquidado")?.remove();
                }
            }

            const body = ContratoUI.DOM.payBody;
            if (!body) return;
            body.innerHTML = (!r.pagos || r.pagos.length === 0)
                ? `<tr><td colspan="6" style="text-align:center;color:#667085">NO EXISTEN PAGOS</td></tr>`
                : r.pagos.map((p, i) => `<tr><td>${i + 1}</td><td>${p.fecha}</td><td>${p.tipo}</td><td>${p.origen}</td><td><b>${window.money ? window.money(p.monto) : p.monto}</b></td><td><button class="btn small gray btn-del-pago" data-del="${p.id_pago}">✕</button></td></tr>`).join("");
        } catch (e) { console.error("Error P6", e); }
    },

    delegarEventosTabla: function () {
        ContratoUI.DOM.payBody?.addEventListener("click", (e) => {
            const btn = e.target.closest(".btn-del-pago");
            if (!btn) return;
            alertify.confirm("Eliminar Pago", "¿Seguro que deseas eliminar este pago?",
                async () => {
                    btn.disabled = true; btn.innerText = "...";
                    try {
                        const res = await (await fetch(`/admin/contrato/pagos/${btn.dataset.del}/eliminar`, { method: "DELETE", headers: { "Content-Type": "application/json", "Accept": "application/json", "X-CSRF-TOKEN": window.csrfToken } })).json();
                        if (res.success !== false) {
                            alertify.success("Pago eliminado."); this.cargarResumen(); if (window.cargarResumenBasico) window.cargarResumenBasico();
                        } else { alertify.error(res.msg || "Error"); btn.disabled = false; btn.innerText = "✕"; }
                    } catch (e) { alertify.error("Error de conexión"); btn.disabled = false; btn.innerText = "✕"; }
                }, () => { }
            ).set('labels', { ok: 'Sí, eliminar', cancel: 'Cancelar' });
        });
    },

    obtenerMontoPendiente: () => parseFloat((document.getElementById("saldoPendiente")?.textContent || "").replace(/[^\d.]/g, "")) || 0,

    abrirModalPago: function () {
        ContratoUI.DOM.modalPagos.classList.add("show");
        document.getElementById("pMonto").value = this.obtenerMontoPendiente().toFixed(2);
        this.cambiarTab("paypal");
    },

    cerrarModalPago: function () {
        ContratoUI.DOM.modalPagos.classList.remove("show");
        ["pMonto", "pNotes", "fileTerminal", "fileTransfer"].forEach(id => { const e = document.getElementById(id); if (e) e.value = ""; });
        const pp = document.getElementById("paypal-button-container-modal"); if (pp) pp.innerHTML = "";
    },

    cambiarTab: function (nombre) {
        document.querySelectorAll("#payTabs .tab").forEach(t => t.classList.toggle("active", t.dataset.tab === nombre));
        ContratoUI.DOM.panes.forEach(p => p.style.display = (p.dataset.pane === nombre) ? "block" : "none");
        if (nombre === "paypal") this.prepararPayPal();
        else document.getElementById("paypal-button-container-modal").innerHTML = "";
    },

    prepararPayPal: async function () {
        const container = document.getElementById("paypal-button-container-modal");
        if (!container) return;
        try {
            if (!this.paypalLoaded) {
                await new Promise((res, rej) => {
                    const s = document.createElement("script");
                    s.src = "https://www.paypal.com/sdk/js?client-id=ATzNpaAJlH7dFrWKu91xLmCzYVDQQF5DJ51b0OFICqchae6n8Pq7XkfsOOQNnElIJMt_Aj0GEZeIkFsp&currency=MXN";
                    s.onload = res; s.onerror = rej; document.head.appendChild(s);
                });
                this.paypalLoaded = true;
            }
            container.innerHTML = "";
            const monto = this.obtenerMontoPendiente();
            paypal.Buttons({
                style: { color: "gold", shape: "pill", label: "pay", height: 40 },
                createOrder: (d, a) => a.order.create({ purchase_units: [{ amount: { value: monto.toFixed(2), currency_code: "MXN" } }] }),
                onApprove: async (d, a) => {
                    const order = await a.order.capture();
                    await fetch(`/admin/contrato/pagos/paypal`, {
                        method: "POST", headers: { "Content-Type": "application/json", "X-CSRF-TOKEN": window.csrfToken },
                        body: JSON.stringify({ id_reservacion: window.ID_RESERVACION, order_id: order.id, monto: monto, origen: "en linea", metodo: "PAYPAL" })
                    });
                    this.cerrarModalPago(); this.cargarResumen(); if (window.cargarResumenBasico) window.cargarResumenBasico();
                    ContratoUI.mostrarNotificacion('success', "Pago PayPal exitoso.");
                },
                onError: () => ContratoUI.mostrarNotificacion('error', "Error PayPal")
            }).render("#paypal-button-container-modal");
        } catch (e) { console.error("PayPal", e); }
    },

    guardarPagoManual: async function () {
        const tab = document.querySelector("#payTabs .tab.active")?.dataset.tab || "";
        let m = document.querySelector("[name='m']:checked")?.value || tab || "EFECTIVO";
        m = m.toUpperCase();

        let o = "mostrador";
        if (m.includes("PAYPAL") || tab === "paypal") { o = "en linea"; m = "PAYPAL"; }
        else if (m.includes("TERMINAL") || m.includes("TARJETA") || tab === "terminal" || tab === "tarjeta") { o = "terminal"; m = "TERMINAL"; }
        else if (m.includes("TRANSFERENCIA") || m.includes("DEPÓSITO") || tab === "transferencia") { o = "transferencia"; m = "TRANSFERENCIA"; }

        const file = (o === "terminal" || tab === "tarjeta") ? document.getElementById("fileTerminal") : (o === "transferencia" ? document.getElementById("fileTransfer") : null);
        const errBox = document.getElementById("pErr");

        if ((o === "terminal" || o === "transferencia") && !file?.files[0]) {
            if (errBox) errBox.innerText = "Sube el comprobante."; return;
        }

        const fd = new FormData();
        fd.append("id_reservacion", window.ID_RESERVACION);
        fd.append("tipo_pago", document.getElementById("pTipo")?.value || "PAGO RESERVACIÓN");
        fd.append("monto", document.getElementById("pMonto")?.value || 0);
        fd.append("notas", document.getElementById("pNotes")?.value || "");
        fd.append("metodo", m); fd.append("origen", o); fd.append("_token", window.csrfToken);
        if (file?.files[0]) fd.append("comprobante", file.files[0]);

        const btn = document.getElementById("pSave");
        btn.disabled = true; btn.innerText = "Guardando...";

        try {
            const data = await (await fetch(`/admin/contrato/pagos/agregar`, { method: "POST", body: fd })).json();
            if (data.ok) {
                this.cerrarModalPago(); this.cargarResumen(); if (window.cargarResumenBasico) window.cargarResumenBasico();
                ContratoUI.mostrarNotificacion('success', "Pago guardado.");
            } else if (errBox) errBox.innerText = data.msg || "Error";
        } catch (e) { if (errBox) errBox.innerText = "Error conexión."; }
        finally { btn.disabled = false; btn.innerText = "GUARDAR PAGO"; }
    }
};

/**
 * MÓDULO 4: NAVEGACIÓN ENTRE PASOS
 */
const ContratoNav = {
    init: function () {
        document.getElementById("btnSaltarDoc")?.addEventListener("click", () => this.saltarDocumentacion());

        document.getElementById("go5")?.addEventListener("click", () => {
            ContratoPaso5.limpiarErroresVisuales();
            window.showStep(5);
        });

        document.getElementById("go6")?.addEventListener("click", () => {
            window.showStep(6);
            setTimeout(() => { if (typeof window.cargarPaso6 === 'function') window.cargarPaso6(); }, 150);
        });

        document.getElementById("back4")?.addEventListener("click", () => window.showStep(4));

        document.getElementById("back5")?.addEventListener("click", () => {
            ContratoPaso5.limpiarErroresVisuales();
            window.showStep(5);
        });
    },

    saltarDocumentacion: function () {
        const form = ContratoUI.DOM.formDoc;
        if (!form) return;

        const camposConRojo = form.querySelectorAll('[style*="border: 2px solid #ef4444"], [style*="border: 2px solid rgb(239, 68, 68)"]');
        camposConRojo.forEach(c => {
            const esArchivo = c.classList.contains('uploader') || c.type === 'file';
            const estaLleno = esArchivo ?
                (c.querySelector('input[type="file"]')?.files.length > 0 || c.files?.length > 0) :
                c.value?.trim() !== '';

            if (estaLleno) {
                c.style.border = "";
                if (!esArchivo) c.style.backgroundColor = "";
            }
        });

        const erroresReales = form.querySelectorAll('[style*="border: 2px solid rgb(239, 68, 68)"], [style*="border: 2px solid #ef4444"]');
        if (erroresReales.length > 0) {
            erroresReales[0].scrollIntoView({ behavior: 'smooth', block: 'center' });
            if (erroresReales[0].tagName !== 'DIV') erroresReales[0].focus();
            ContratoUI.mostrarNotificacion('error', `<b>🚫 Datos Inválidos</b><br>Corrige los campos en rojo antes de continuar.`);
            return;
        }

        const obligatorios = form.querySelectorAll('input[required], select[required], input[name*="fecha_emision"], input[name*="numero_identificacion"]');
        let detallesFaltantes = [];
        let pFal = null;

        obligatorios.forEach(c => {
            const esArchivo = c.type === 'file';
            const estaVacio = esArchivo ? c.files.length === 0 : !c.value.trim();
            const elementoVisual = esArchivo ? c.closest('.uploader') : c;

            if (estaVacio) {
                if (elementoVisual) {
                    elementoVisual.style.border = "2px solid #ef4444";
                    if (!esArchivo) elementoVisual.style.backgroundColor = "#fef2f2";
                }

                if (!pFal) pFal = c;

                const n = c.name;
                const esTitular = n.includes("[0]") ? "del <b>Titular</b>" : "del <b>Adicional</b>";
                let nombreCampo = "";

                if (n.includes("idFrente")) nombreCampo = `la <b>Fotografía Identificación — Frente</b> ${esTitular}`;
                else if (n.includes("idReverso")) nombreCampo = `la <b>Fotografía Identificación — Reverso</b> ${esTitular}`;
                else if (n.includes("licFrente")) nombreCampo = `la <b>Licencia — Frente</b> ${esTitular}`;
                else if (n.includes("licReverso")) nombreCampo = `la <b>Licencia — Reverso</b> ${esTitular}`;
                else if (n.includes("emision")) nombreCampo = `la <b>Fecha de Emisión</b> ${esTitular}`;
                else if (n.includes("identificacion")) nombreCampo = `el <b>Número de ID</b> ${esTitular}`;
                else if (n.includes("licencia") && !esArchivo) nombreCampo = `el <b>Número de Licencia</b> ${esTitular}`;
                else if (n.includes("nacimiento")) nombreCampo = `la <b>Fecha de Nacimiento</b> ${esTitular}`;
                else if (n.includes("nombre")) nombreCampo = `el <b>Nombre</b> ${esTitular}`;
                else if (n.includes("paterno")) nombreCampo = `el <b>Apellido Paterno</b> ${esTitular}`;
                else nombreCampo = "Campo obligatorio";

                detallesFaltantes.push(`• ${nombreCampo}`);
            }
        });

        if (detallesFaltantes.length > 0) {
            const listaErrores = [...new Set(detallesFaltantes)].join("<br>");
            const elementoScroll = pFal.type === 'file' ? pFal.closest('.uploader') : pFal;

            if (elementoScroll) {
                elementoScroll.scrollIntoView({ behavior: 'smooth', block: 'center' });
                if (pFal.type !== 'file') pFal.focus();
            }

            ContratoUI.mostrarNotificacion('warning', `<b>⚠️ Faltan Datos</b><br>${listaErrores}`);
            return;
        }

        window.showStep(6);
        setTimeout(() => { if (typeof window.cargarPaso6 === 'function') window.cargarPaso6(); }, 150);
    }
};

/**
 * ==========================================
 * INICIALIZACIÓN GLOBAL
 * ==========================================
 */
document.addEventListener("DOMContentLoaded", () => {
    console.log("✅ DOM listo, iniciando Módulos (Paso 4-6)...");

    ContratoUI.cacheDOM();
    ContratoUI.initGlobalEvents();
    ContratoPaso4.init();
    ContratoPaso5.init();
    ContratoPaso6.init();
    ContratoNav.init();

    // Compatibilidad para funciones llamadas desde afuera (Blade o inline JS)
    window.cargarPaso6 = ContratoPaso6.cargarResumen.bind(ContratoPaso6);

    // Disparadores iniciales
    setTimeout(() => {
        window.showStep(4);
        if (document.getElementById("switchDropoff")?.classList.contains("on")) ContratoPaso4.handleDropoffUpdate();
        ContratoPaso4.recalcularTotal();
        if (typeof window.cargarResumenBasico === 'function') window.cargarResumenBasico();
    }, 200);

    const originalCargarResumen = window.cargarResumenBasico;

    window.cargarResumenBasico = async function () {
        await originalCargarResumen();

        const marca = document.getElementById("detMarca")?.textContent;
        const modelo = document.getElementById("detModelo")?.textContent;
        const placa = document.getElementById("detPlaca")?.textContent || "S/P";

        const uiVehiculo = document.getElementById("vehiculoAsignadoUI");
        if (uiVehiculo && marca !== "—") {
            uiVehiculo.innerHTML = `
            <div style="display:flex; align-items:center; gap:15px; padding:12px; background:#f0fdf4; border:1px solid #bbf7d0; border-radius:8px;">
                <div style="font-size:24px;">✅</div>
                <div>
                    <h4 style="margin:0; font-size:15px; color:#166534;">${marca} ${modelo}</h4>
                    <p style="margin:4px 0 0 0; font-size:12px; color:#166534;">Placa: <b>${placa}</b></p>
                </div>
            </div>`;
        }
    };
});