/**
 * MÓDULO 0: UTILIDADES GLOBALES Y CACHÉ DOM
 */

const ContratoUI = {
    DOM: {},
    cacheDOM: function () {
        this.DOM = {
            app: document.getElementById("contratoApp"),
            formDoc: document.getElementById("formDocumentacion"),
            payBody: document.getElementById("payBody"),
            modalPagos: document.getElementById("mb"),
            payTabs: document.getElementById("payTabs"),
            panes: document.querySelectorAll("[data-pane]")
        };
    },
    initGlobalEvents: function () {
        const btnInfo = document.getElementById("btnInfoLicencia");
        if (btnInfo) {
            btnInfo.addEventListener("click", () => {
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
        }
    },
    mostrarNotificacion: function (tipo, htmlMsg) {
        const now = Date.now();
        if (window.lastAlert === htmlMsg && window.lastAlertTime && (now - window.lastAlertTime) < 3000) return;
        window.lastAlert = htmlMsg;
        window.lastAlertTime = now;

        if (typeof alertify !== 'undefined') {
            if (tipo === 'error') alertify.error(htmlMsg);
            else if (tipo === 'warning') alertify.warning(htmlMsg);
            else alertify.success(htmlMsg);
        } else {
            alert(htmlMsg.replace(/<[^>]*>?/gm, ''));
        }
    }
};

const urlArchivo = (id) => id ? `/archivo/${id}` : null;

function initFlatpickrPaso4() {
    if (typeof flatpickr === "undefined") {
        console.error("Flatpickr no está cargado");
        return;
    }

    const inputs = document.querySelectorAll("#formDocumentacion .fecha-flatpickr");
    console.log("Calendarios encontrados:", inputs.length);

    inputs.forEach(input => {
        if (input._flatpickr) return;

        flatpickr(input, {
            locale: flatpickr.l10ns?.es || "default",
            dateFormat: "Y-m-d",
            altInput: false,
            allowInput: false,
            clickOpens: true,
            disableMobile: true,
            monthSelectorType: "static",
            onOpen: function(selectedDates, dateStr, instance) {
            const cal = instance.calendarContainer;
            const rect = input.getBoundingClientRect();
                cal.style.position = 'absolute';
                cal.style.top = (rect.bottom + window.scrollY + 4) + 'px';
                cal.style.left = (rect.left + window.scrollX) + 'px';
                cal.style.width = '300px';
                cal.style.zIndex = '99999';
            }
        });
    });
}

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
        this.initBusquedaPersonas();
        this.inicializarEstadoLicencia();
        setTimeout(() => {
    initFlatpickrPaso4();
}, 500);
        const form = ContratoUI.DOM.formDoc;
        if (form) {
            form.setAttribute("novalidate", "true");
            form.addEventListener("submit", (e) => this.enviarFormulario(e));
        }

        document.querySelectorAll('input[name$="[numero_licencia]"]').forEach(inp => {
            inp.addEventListener('input', (e) => this.filtrarLicencia(e));
        });
    },

    initBusquedaPersonas: function () {
        const buscador = document.getElementById('buscadorGlobalPersona');
        if (!buscador) return;
        const self = this;

        const wrapper = buscador.parentElement;
        if (!wrapper) return;
        const resultsDiv = wrapper.querySelector('.search-results');
        if (!resultsDiv) return;

        let timeout = null;

        buscador.addEventListener('input', function () {
            const query = this.value.trim();
            if (query.length < 2) {
                resultsDiv.style.display = 'none';
                return;
            }
            clearTimeout(timeout);
            timeout = setTimeout(() => {
                fetch(`/admin/contrato/buscar-persona?q=${encodeURIComponent(query)}`)
                    .then(r => r.json())
                    .then(data => {
                        resultsDiv.innerHTML = '';
                        if (data.length === 0) {
                            resultsDiv.style.display = 'none';
                            return;
                        }
                        data.forEach(persona => {
                            const div = document.createElement('div');
                            div.style.padding = '8px 12px';
                            div.style.cursor = 'pointer';
                            div.style.borderBottom = '1px solid #eee';
                            div.innerHTML = `<strong>${persona.nombre} ${persona.apellido_paterno} ${persona.apellido_materno}</strong><br>
                                          <small>${persona.tipo_identificacion}: ${persona.numero_identificacion}</small>`;
                            div.addEventListener('click', () => {
                                self.autocompletarDesdeBusqueda(persona, 0);
                                resultsDiv.style.display = 'none';
                                buscador.value = '';
                            });
                            resultsDiv.appendChild(div);
                        });
                        resultsDiv.style.display = 'block';
                    });
            }, 300);
        });

        document.addEventListener('click', (e) => {
            if (wrapper && resultsDiv && !wrapper.contains(e.target)) {
                resultsDiv.style.display = 'none';
            }
        });
    },

    autocompletarDesdeBusqueda: function (persona, idx) {
        const campos = {
            'nombre': persona.nombre,
            'apellido_paterno': persona.apellido_paterno,
            'apellido_materno': persona.apellido_materno,
            'tipo_identificacion': persona.tipo_identificacion,
            'numero_identificacion': persona.numero_identificacion,
            'fecha_nacimiento': persona.fecha_nacimiento,
            'fecha_vencimiento_id': persona.fecha_vencimiento_id,
            'contacto_emergencia': persona.contacto_emergencia ?? '',
        };

        for (const [key, value] of Object.entries(campos)) {
            const el = document.querySelector(`[name="conductores[${idx}][${key}]"]`);
            if (el) el.value = value ?? '';
        }

        if (persona.id_archivo_frente || persona.id_archivo_reverso) {
            this.mostrarPreviewDesdeUrl(idx, 'idFrente', persona.id_archivo_frente ? urlArchivo(persona.id_archivo_frente) : null);
            this.mostrarPreviewDesdeUrl(idx, 'idReverso', persona.id_archivo_reverso ? urlArchivo(persona.id_archivo_reverso) : null);
            const inputFrente = document.querySelector(`[name="conductores[${idx}][idFrente]"]`);
            const inputReverso = document.querySelector(`[name="conductores[${idx}][idReverso]"]`);
            if (inputFrente && persona.id_archivo_frente) inputFrente.removeAttribute('required');
            if (inputReverso && persona.id_archivo_reverso) inputReverso.removeAttribute('required');
        }

        ContratoUI.mostrarNotificacion('success', 'Persona cargada en el Titular.');
    },

    mostrarPreviewDesdeUrl: function (idx, tipo, url) {
        if (!url) return;
        const input = document.querySelector(`[name="conductores[${idx}][${tipo}]"]`);
        if (!input) return;

        const uploader = input.closest('.uploader');
        if (!uploader) return;

        let prev = uploader.querySelector('.preview');
        if (!prev) {
            prev = document.createElement('div');
            prev.className = 'preview';
            uploader.appendChild(prev);
        }

        prev.style.cssText = `
        position: absolute; top: 0; left: 0; width: 100%; height: 100%;
        background-color: #fff; z-index: 10;
        display: flex; justify-content: center; align-items: center;
        padding: 0; box-sizing: border-box;
    `;
        prev.innerHTML = `
        <img src="${url}" style="width:100%; height:100%; object-fit:contain; border-radius:inherit;" alt="Vista previa">
        <button type="button" title="Quitar imagen" style="
            position:absolute; top:8px; right:8px; background:#ef4444; color:white;
            border:none; border-radius:50%; width:26px; height:26px; cursor:pointer;
            font-weight:bold; box-shadow:0 2px 4px rgba(0,0,0,0.3); z-index:11;
            display:flex; justify-content:center; align-items:center; line-height:1;
        " onclick="
            event.preventDefault();
            const uploader = this.closest('.uploader');
            if(uploader) {
                const fileInput = uploader.querySelector('input[type=file]');
                if(fileInput) fileInput.value = '';
            }
            const prevDiv = this.closest('.preview');
            if(prevDiv) {
                prevDiv.style.cssText = '';
                prevDiv.innerHTML = '';
            }
        ">×</button>
    `;
    },

    cargarDocumentacionGuardada: function () {
        const idContrato = document.querySelector('input[name="id_contrato"]').value;
        if (!idContrato) return;

        fetch(`/admin/contrato/documentacion/${idContrato}`)
            .then(r => r.json())
            .then(data => {
                if (!data.success || !data.documentos) return;

                const docs = data.documentos;

                if (docs.titular) {
                    this.poblarBloqueConductor(0, docs.titular.campos, docs.titular.archivos);
                }

                for (const [idConductor, datos] of Object.entries(docs.adicionales || {})) {
                    const bloque = document.querySelector(`[data-id-conductor="${idConductor}"]`);
                    if (!bloque) continue;
                    const hidden = bloque.querySelector('input[name*="[id_conductor]"]');
                    if (!hidden) continue;
                    const idx = hidden.name.match(/conductores\[(\d+)\]/)[1];
                    this.poblarBloqueConductor(idx, datos.campos, datos.archivos);
                }
            });
    },

    poblarBloqueConductor: function (idx, campos, archivos) {
        for (const key of Object.keys(campos)) {
            const valor = campos[key];
            if (valor === null || valor === undefined) continue;
            const el = document.querySelector(`[name="conductores[${idx}][${key}]"]`);
            if (el) {
                el.value = valor;
            }
        }

        if (archivos) {
            const tipos = ['idFrente', 'idReverso', 'licFrente', 'licReverso'];

            tipos.forEach(tipo => {
                const url = archivos[tipo + '_url'];
                const input = document.querySelector(`[name="conductores[${idx}][${tipo}]"]`);
                if (!input || !url) return;
                input.removeAttribute('required');

                const uploader = input.closest('.uploader');
                if (!uploader) return;

                let prev = uploader.querySelector('.preview');
                if (!prev) {
                    prev = document.createElement('div');
                    prev.className = 'preview';
                    uploader.appendChild(prev);
                }

                prev.style.cssText = `
                position: absolute; top: 0; left: 0; width: 100%; height: 100%;
                background-color: #fff; z-index: 10;
                display: flex; justify-content: center; align-items: center;
                padding: 0; box-sizing: border-box;
            `;
                prev.innerHTML = `
                <img src="${url}" style="width:100%; height:100%; object-fit:contain; border-radius:inherit;" alt="Vista previa">
                <button type="button" title="Quitar imagen" style="
                    position:absolute; top:8px; right:8px; background:#ef4444; color:white;
                    border:none; border-radius:50%; width:26px; height:26px; cursor:pointer;
                    font-weight:bold; box-shadow:0 2px 4px rgba(0,0,0,0.3); z-index:11;
                    display:flex; justify-content:center; align-items:center; line-height:1;
                " onclick="
                    event.preventDefault();
                    const uploader = this.closest('.uploader');
                    if(uploader) {
                        const fileInput = uploader.querySelector('input[type=file]');
                        if(fileInput) fileInput.value = '';
                    }
                    const prevDiv = this.closest('.preview');
                    if(prevDiv) {
                        prevDiv.style.cssText = '';
                        prevDiv.innerHTML = '';
                    }
                ">×</button>
            `;
            });
        }
    },

    delegarEventosValidacion: function () {
        const form = ContratoUI.DOM.formDoc;
        if (!form) return;

        const selectoresInput = `input[name*="fecha_nacimiento"], input[name*="numero_licencia"], input[name*="numero_identificacion"], input[name*="fecha_emision"], input[name*="fecha_vencimiento"], input[name*="contacto_emergencia"]`;
        const selectoresCambio = `select[name*="emite_licencia"], select[name*="pais"], select[name*="id_pais"], select[name*="tipo_identificacion"]`;

        const quitarAlertaRoja = (t) => {
            if (!t.style) return;
            t.classList.remove('input-error');
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
            targets = ['numero_identificacion', 'fecha_vencimiento_id', 'idFrente', 'idReverso']
                .map(n => document.querySelector(`[name="${prefix}[${n}]"]`));

            const isLicencia = selectInput.value.toLowerCase() === 'licencia';
            targets.forEach(el => {
                if (el) {
                    el.disabled = isLicencia;
                    el.required = !isLicencia;
                    if (isLicencia) {
                        el.value = "";
                        if (el.type === 'file') {
                            const uploader = el.closest('.uploader');
                            if (uploader) uploader.style.border = "";
                        } else {
                            el.style.border = "";
                            el.style.backgroundColor = "";
                        }
                    }
                }
            });

            const idFrente = document.querySelector(`[name="${prefix}[idFrente]"]`);
            const idReverso = document.querySelector(`[name="${prefix}[idReverso]"]`);
            [idFrente, idReverso].forEach(el => {
                if (el) {
                    const uploader = el.closest('.uploader');
                    if (uploader) {
                        uploader.style.opacity = isLicencia ? '0.5' : '1';
                        uploader.style.pointerEvents = isLicencia ? 'none' : 'auto';
                    }
                }
            });
        }

        targets.forEach(el => {
            if (el && !nameAttr.includes('tipo_identificacion')) {
                el.value = "";
                el.style.border = "";
                el.style.backgroundColor = "";
            }
        });
    },

    filtrarLicencia: function (e) {
        const input = e.target;
        let valor = input.value;
        valor = valor.replace(/[^A-Za-z0-9\-]/g, '');
        valor = valor.toUpperCase();
        input.value = valor;
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
            input.classList.remove('input-error', 'input-warning', 'input-ok');
            if (tipo === 'ok') {
                input.style.border = "1px solid #10b981";
                input.style.backgroundColor = "#ecfdf5";
            } else if (tipo === 'error') {
                input.style.border = "2px solid #ef4444";
                input.style.backgroundColor = "#fef2f2";
                input.classList.add('input-error');
            } else if (tipo === 'warning') {
                input.style.border = "2px solid #eab308";
                input.style.backgroundColor = "#fefce8";
                input.classList.add('input-warning');
            } else {
                input.style.border = "";
                input.style.backgroundColor = "";
            }
        };

        if (nameAttr.includes('contacto_emergencia')) {
            const val = inputContacto ? inputContacto.value.replace(/\D/g, '') : '';
            if (!val) { setEstado(inputContacto, ''); return; }
            if (val.length !== 10) {
                setEstado(inputContacto, 'error');
                console.warn("⚠️ Teléfono Inválido - Debe tener exactamente 10 dígitos.");
            } else {
                setEstado(inputContacto, 'ok');
            }
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
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    "X-CSRF-TOKEN": window.csrfToken
                },
                body: JSON.stringify(payload),
                signal: this.controller.signal
            });

            const res = await resp.json();
            if (currentRequest !== this.requestId) return;

            const msgSrv = res.msg?.[0] || "Dato inválido";
            const borrarYTemblar = (inp, borrar = true) => {
                if (!inp) return;
                if (borrar) inp.value = "";
                try {
                    inp.animate([
                        { transform: 'translate3d(0,0,0)' },
                        { transform: 'translate3d(-4px,0,0)' },
                        { transform: 'translate3d(4px,0,0)' },
                        { transform: 'translate3d(-4px,0,0)' },
                        { transform: 'translate3d(4px,0,0)' },
                        { transform: 'translate3d(0,0,0)' }
                    ], { duration: 400 });
                } catch (e) { }
            };

            if (inputNac && inputNac.value && inputTrigger.name.includes('nacimiento')) {
                const b = new Date(inputNac.value + "T00:00:00"), h = new Date();
                let e = h.getFullYear() - b.getFullYear();
                if (h.getMonth() < b.getMonth() || (h.getMonth() === b.getMonth() && h.getDate() < b.getDate())) e--;

                if (e < 18) {
                    setEstado(inputNac, 'error');
                    borrarYTemblar(inputNac);
                    console.warn("🚫 Edad no permitida - El conductor debe tener al menos 18 años.");
                    this.gestionarCobroMenoresExtra();
                    return;
                } else if (e >= 18 && e <= 24) {
                    setEstado(inputNac, 'warning');
                } else {
                    setEstado(inputNac, 'ok');
                }
            }

            if (res.status === 'vencido') {
                setEstado(inputVen, 'error');
                borrarYTemblar(inputVen);
                console.warn(`🚫 Expirado - ${msgSrv}`);
            } else if (res.status === 'error_fecha') {
                setEstado(inputEmi, 'error');
                setEstado(inputVen, 'error');
                borrarYTemblar(inputEmi);
                borrarYTemblar(inputVen);
                console.warn(`🚫 Error Fechas - ${msgSrv}`);
            } else if (res.status === 'warning') {
                setEstado(inputVen, 'warning');
                console.warn(`⚠️ Revisión - ${msgSrv}`);
            }

            if (res.status === 'invalido' && payload.numero) {
                setEstado(inputNum, 'error');
                borrarYTemblar(inputNum, false);
                console.warn(`🚫 Formato Incorrecto - ${msgSrv}`);
            }

        } catch (err) {
            if (err.name !== 'AbortError') console.error("Validación", err);
        }
    },

    gestionarCobroMenoresExtra: async function () {
        const inputs = document.querySelectorAll('input[name*="fecha_nacimiento"]');
        let count = 0;

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

        try {
            const r = await fetch('/admin/contrato/servicios-extra', {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    "X-CSRF-TOKEN": window.csrfToken
                },
                body: JSON.stringify({
                    id_reservacion: window.ID_RESERVACION,
                    id_servicio: window.ID_SERVICIO_MENOR || 0,
                    forzar: count > 0 ? "on" : "off",
                    cantidad: count
                })
            });

            if (r.ok) {
                if (typeof window.cargarPaso6 === 'function') window.cargarPaso6();
                if (count > 0) {
                    console.log(`🔞 Cargo Aplicado - Tarifa extra por ${count} conductor(es) joven(es).`);
                } else {
                    console.log("✅ Cargo Removido - Ningún conductor requiere tarifa de menor.");
                }
                if (typeof window.cargarResumenBasico === 'function') window.cargarResumenBasico();
            }
        } catch (err) {
            console.error("Error Menores:", err);
        }
    },

    configurarPreviews: function () {
        const form = ContratoUI.DOM.formDoc;
        if (!form) return;

        form.querySelectorAll(".uploader").forEach(uploader => {
            uploader.style.position = 'relative';
            uploader.style.overflow = 'hidden';

            const previewSibling = uploader.parentElement?.querySelector(":scope > .preview");
            if (previewSibling && previewSibling.parentElement !== uploader) {
                uploader.appendChild(previewSibling);
            }
        });

        form.addEventListener("change", (e) => {
            if (e.target.type === "file" && e.target.closest(".uploader")) {
                const file = e.target.files[0];
                const uploader = e.target.closest(".uploader");
                let prev = uploader.querySelector(".preview");

                if (!prev) {
                    prev = uploader.parentElement?.querySelector(":scope > .preview");
                    if (prev) uploader.appendChild(prev);
                }

                if (!file || !prev?.classList.contains("preview")) return;

                const reader = new FileReader();
                reader.onload = ev => {
                    prev.style.cssText = `
                        position: absolute;
                        top: 0;
                        left: 0;
                        width: 100%;
                        height: 100%;
                        background-color: #ffffff;
                        z-index: 10;
                        display: flex;
                        justify-content: center;
                        align-items: center;
                        padding: 0;
                        margin: 0;
                        box-sizing: border-box;
                    `;

                    prev.innerHTML = `
                        <img src="${ev.target.result}" style="width:100%; height:100%; object-fit:contain; border-radius:inherit;" alt="Vista previa">

                        <button type="button" title="Quitar imagen" style="position:absolute; top:8px; right:8px; background:#ef4444; color:white; border:none; border-radius:50%; width:26px; height:26px; cursor:pointer; font-weight:bold; box-shadow:0 2px 4px rgba(0,0,0,0.3); z-index:11; display:flex; justify-content:center; align-items:center; line-height:1;"
                        onclick="
                            event.preventDefault();
                            const uploaderNode = this.closest('.uploader');
                            if(uploaderNode) {
                                const fileInput = uploaderNode.querySelector('input[type=file]');
                                if(fileInput) fileInput.value = '';
                            }
                            const prevNode = this.closest('.preview');
                            if(prevNode) {
                                prevNode.style.cssText = '';
                                prevNode.innerHTML = '';
                            }
                        ">×</button>
                    `;
                };
                reader.readAsDataURL(file);
            }
        });
    },

    limpiarErroresVisuales: function () {
        const form = ContratoUI.DOM.formDoc;
        if (!form) return;
        form.querySelectorAll('.input-error, .input-warning').forEach(el => {
            el.style.border = "";
            el.style.backgroundColor = "";
            el.classList.remove('input-error', 'input-warning');
        });
    },

    enviarFormulario: async function (e) {
        e.preventDefault();
        const form = ContratoUI.DOM.formDoc;
        const btn = document.getElementById("btnContinuarDoc");
        if (!form || !btn) return;

        form.querySelectorAll('.input-error').forEach(el => {
            el.classList.remove('input-error');
            el.style.border = '';
            el.style.backgroundColor = '';
        });

        let primerError = null;

        const requiredFields = form.querySelectorAll('input[required], select[required]');
        requiredFields.forEach(c => {
            const esArchivo = c.type === 'file';
            const estaVacio = esArchivo ? c.files.length === 0 : !c.value.trim();
            const elementoVisual = esArchivo ? c.closest('.uploader') : c;

            if (estaVacio) {
                if (elementoVisual) {
                    elementoVisual.style.border = "2px solid #ef4444";
                    if (!esArchivo) elementoVisual.style.backgroundColor = "#fef2f2";
                    elementoVisual.classList.add('input-error');
                }
                if (!primerError) {
                    primerError = elementoVisual || c;
                }
            }
        });

        if (primerError) {
            primerError.scrollIntoView({ behavior: 'smooth', block: 'center' });
            if (primerError.tagName !== 'DIV') primerError.focus();
            return;
        }

        const errores = form.querySelectorAll('.input-error');
        if (errores.length > 0) {
            errores[0].scrollIntoView({ behavior: 'smooth', block: 'center' });
            errores[0].focus();
            return;
        }

        btn.disabled = true;
        btn.innerText = "Subiendo archivos...";
        try {
            const res = await (await fetch(form.action, {
                method: "POST",
                body: new FormData(form),
                headers: { 'X-CSRF-TOKEN': window.csrfToken }
            })).json();

            if (res.success) {
                ContratoUI.mostrarNotificacion('success', "¡Guardado exitoso!");
                ContratoNav.irAlPaso5();
                if (typeof window.cargarPaso6 === 'function') window.cargarPaso6();
                if (window.cargarResumenBasico) window.cargarResumenBasico();
            } else {
                console.error("Error al guardar:", res.error);
            }
        } catch (err) {
            console.error("Error de conexión:", err);
        } finally {
            btn.disabled = false;
            btn.innerText = "Guardar y Continuar →";
        }
    },

    inicializarEstadoLicencia: function () {
        document.querySelectorAll('select[name$="[tipo_identificacion]"]').forEach(select => {
            this.limpiarDependientes(select);
        });
    }
};

/**
 * MÓDULO 3: PASO 6 - PAGOS Y ESTADO DE CUENTA
 */
const ContratoPaso6 = {
    paypalLoaded: false,
    paymentFlow: "reserva",
    ultimoResumen: null,

    init: function () {
        this.delegarEventosTabla();
        const btnAdd = document.getElementById("btnAdd");
        if (btnAdd) btnAdd.addEventListener("click", () => this.abrirModalPago());
        const mx = document.getElementById("mx");
        if (mx) mx.addEventListener("click", () => this.cerrarModalPago());
        const payTabs = ContratoUI.DOM.payTabs;
        if (payTabs) {
            payTabs.addEventListener("click", (e) => {
                if (e.target.dataset.tab) this.cambiarTab(e.target.dataset.tab);
            });
        }
        const pSave = document.getElementById("pSave");
        if (pSave) pSave.addEventListener("click", () => this.guardarPagoManual());
    },

    cargarResumen: async function () {
        try {
            const resp = await fetch(`/admin/contrato/${window.ID_RESERVACION}/resumen-paso6`);
            const res = await resp.json();
            if (!res.success && !res.ok) return;
            const r = res.data;
            this.ultimoResumen = r;

            const fM = (v) => window.money ? window.money(v) : `$${parseFloat(v).toFixed(2)} MXN`;

            if (document.getElementById("baseDescr")) document.getElementById("baseDescr").textContent = r.base.descripcion;
            if (document.getElementById("baseAmt")) document.getElementById("baseAmt").textContent = fM(r.base.total);
            if (document.getElementById("insDescr")) document.getElementById("insDescr").textContent = r.totales.nombre_seguro || "Protecciones";
            if (document.getElementById("insAmt")) document.getElementById("insAmt").textContent = fM(r.totales.monto_seguros);

            const containerExtras = document.getElementById("listaExtrasP6");
            if (containerExtras) {
                containerExtras.innerHTML = "";
                if (r.adicionales.lista && r.adicionales.lista.length > 0) {
                    r.adicionales.lista.forEach(item => {
                        const row = document.createElement("div");
                        row.className = "row";
                        row.style.padding = "10px 0";
                        row.style.borderBottom = "1px solid #f1f5f9";
                        row.innerHTML = `<div style="color: #475569;">+ ${item.nombre}</div><div style="font-weight: bold;">${fM(item.total)}</div>`;
                        containerExtras.appendChild(row);
                    });
                }
            }

            const totalTexto = fM(r.totales.total_contrato || r.totales.total);
            if (document.getElementById("subtotalAmt")) document.getElementById("subtotalAmt").textContent = fM(r.totales.subtotal);
            if (document.getElementById("ivaOnly")) document.getElementById("ivaOnly").textContent = fM(r.totales.iva);
            if (document.getElementById("totalContrato")) document.getElementById("totalContrato").textContent = totalTexto;
            if (document.getElementById("detTotalFinalCuenta")) document.getElementById("detTotalFinalCuenta").textContent = totalTexto;
            if (document.getElementById("saldoPendiente")) document.getElementById("saldoPendiente").textContent = fM(r.totales.saldo_pendiente);
            if (document.getElementById("detSaldo")) document.getElementById("detSaldo").textContent = fM(r.totales.saldo_pendiente);
            if (document.getElementById("detPagos")) document.getElementById("detPagos").textContent = fM(r.pagos.realizados || 0);

            const garantiaMonto = r.totales?.garantia?.monto;
            const garantiaPagada = r.pagos?.garantia?.realizados;
            const garantiaPendiente = r.pagos?.garantia?.pendiente;

            if (document.getElementById("detGarantiaSeguro")) {
                document.getElementById("detGarantiaSeguro").textContent = garantiaMonto !== undefined ? fM(garantiaMonto) : "—";
            }
            if (document.getElementById("detGarantiaSeguroMeta")) {
                const codigo = r.totales?.garantia?.codigo_categoria || "—";
                const nombre = r.totales?.garantia?.nombre_seguro || "Sin paquete";
                document.getElementById("detGarantiaSeguroMeta").textContent = `${codigo} · ${nombre}`;
            }
            if (document.getElementById("detGarantiaSeguroStatus")) {
                if (garantiaPagada !== undefined && garantiaPendiente !== undefined) {
                    document.getElementById("detGarantiaSeguroStatus").textContent = `Pagada: ${fM(garantiaPagada)} | Pendiente: ${fM(garantiaPendiente)}`;
                } else {
                    document.getElementById("detGarantiaSeguroStatus").textContent = "Información no disponible";
                }
            }

            this.renderizarPagos(r.pagos.lista || []);
            this.actualizarBotonPago();

        } catch (e) {
            console.error("Error cargando Paso 6:", e);
        }
    },

    renderizarPagos: function (pagos) {
        const body = ContratoUI.DOM.payBody;
        if (!body) return;
        body.innerHTML = "";

        if (!pagos || pagos.length === 0) {
            body.innerHTML = `<tr><td colspan="6" style="text-align:center;padding:30px;color:#94a3b8;">NO EXISTEN PAGOS REGISTRADOS</td></tr>`;
            return;
        }

        const fM = (v) => window.money ? window.money(v) : `$${parseFloat(v).toFixed(2)}`;

        body.innerHTML = pagos.map((p, index) => {
            const id = p.id_pago || p.id;
            const fecha = p.created_at ? p.created_at.substring(0, 10) : '—';
            const metodo = p.metodo || '—';
            const tipo = p.tipo_pago || '';
            const origen = p.origen_pago || 'Mostrador';
            return `
            <tr style="border-bottom: 1px solid #edf2f7;">
                <td style="padding: 10px 8px;">${index + 1}</td>
                <td style="padding: 10px 8px;">${fecha}</td>
                <td style="padding: 10px 8px;">
                    <b>${metodo}</b><br>
                    <small style="color:#64748b">${tipo}</small>
                </td>
                <td style="padding: 10px 8px;">
                    <span class="badge" style="background:#f1f5f9; color:#475569; font-size:11px; padding:2px 6px;">
                        ${origen.toUpperCase()}
                    </span>
                </td>
                <td style="padding: 10px 8px; text-align:right; font-weight:bold; color:#1e293b;">
                    ${fM(p.monto)}
                </td>
                <td style="padding: 10px 8px; text-align:right;">
                    <button class="btn-del-pago" data-del="${id}" style="background:transparent;border:none;color:#ef4444;cursor:pointer;font-size:16px;">
                        ✕
                    </button>
                </td>
            </tr>`;
        }).join("");
    },

    delegarEventosTabla: function () {
        const payBody = ContratoUI.DOM.payBody;
        if (!payBody) return;
        payBody.addEventListener("click", (e) => {
            const btn = e.target.closest(".btn-del-pago");
            if (!btn) return;
            alertify.confirm("Eliminar Pago", "¿Seguro que deseas eliminar este pago?",
                async () => {
                    btn.disabled = true; btn.innerText = "...";
                    try {
                        const res = await (await fetch(`/admin/contrato/pagos/${btn.dataset.del}/eliminar`, {
                            method: "DELETE",
                            headers: {
                                "Content-Type": "application/json",
                                "Accept": "application/json",
                                "X-CSRF-TOKEN": window.csrfToken
                            }
                        })).json();
                        if (res.success !== false) {
                            alertify.success("Pago eliminado.");
                            this.cargarResumen();
                            if (window.cargarResumenBasico) window.cargarResumenBasico();
                        } else {
                            alertify.error(res.msg || "Error");
                            btn.disabled = false;
                            btn.innerText = "✕";
                        }
                    } catch (e) {
                        alertify.error("Error de conexión");
                        btn.disabled = false;
                        btn.innerText = "✕";
                    }
                }, () => { }
            ).set('labels', { ok: 'Sí, eliminar', cancel: 'Cancelar' });
        });
    },

    obtenerMontoPendienteReserva: function () {
        return parseFloat(this.ultimoResumen?.pagos?.saldo || 0) || 0;
    },

    obtenerMontoPendienteGarantia: function () {
        return parseFloat(this.ultimoResumen?.pagos?.garantia?.pendiente || 0) || 0;
    },

    obtenerMontoPendiente: function () {
        return this.paymentFlow === "garantia"
            ? this.obtenerMontoPendienteGarantia()
            : this.obtenerMontoPendienteReserva();
    },

    actualizarBotonPago: function () {
        const btn = document.getElementById("btnAdd");
        if (!btn) return;

        const reservaPendiente = this.obtenerMontoPendienteReserva();
        const garantiaPendiente = this.obtenerMontoPendienteGarantia();

        if (reservaPendiente > 0.009 && garantiaPendiente > 0.009) {
            btn.textContent = " Elegir Pago";
        } else if (reservaPendiente > 0.009) {
            btn.textContent = "+ Registrar Pago (Reserva)";
        } else if (garantiaPendiente > 0.009) {
            btn.textContent = "+ Registrar Garantía";
        } else {
            btn.textContent = "+ Registrar Pago";
        }
    },

    configurarFlujoPago: function (flow) {
        this.paymentFlow = flow;
        const pMonto = document.getElementById("pMonto");
        const pTipo = document.getElementById("pTipo");
        const pHint = document.getElementById("pFlowHint");
        const monto = this.obtenerMontoPendiente();

        if (pMonto) pMonto.value = monto.toFixed(2);

        if (flow === "garantia") {
            if (pTipo) pTipo.value = "GARANTIA";
            if (pHint) pHint.textContent = "Procediendo con el pago de la garantía. Puedes ajustar el monto si es necesario.";
        } else {
            if (pTipo && pTipo.value === "GARANTIA") pTipo.value = "PAGO RESERVACIÓN";
            if (pHint) pHint.textContent = "Pago 1 de 2: registra primero el monto de la reservación.";
        }

        const tabPaypal = document.querySelector('#payTabs .tab[data-tab="paypal"]');
        if (tabPaypal) {
            tabPaypal.style.display = (flow === "garantia") ? "none" : "inline-block";
        }
    },

    abrirModalPago: function (flow = null) {
        const modal = ContratoUI.DOM.modalPagos;
        if (!modal) return;

        const saldoReserva = this.obtenerMontoPendienteReserva();
        const saldoGarantia = this.obtenerMontoPendienteGarantia();

        if (flow === "garantia") {
            modal.classList.add("show");
            this.configurarFlujoPago("garantia");
            this.cambiarTab("terminal");
            return;
        }
        if (flow === "reserva") {
            modal.classList.add("show");
            this.configurarFlujoPago("reserva");
            this.cambiarTab("paypal");
            return;
        }

        if (saldoReserva > 0.009 && saldoGarantia > 0.009) {
            modal.classList.add("show");
            this.configurarFlujoPago("garantia");
            this.cambiarTab("terminal");
            return;
        }

        const flujo = saldoReserva > 0.009 ? "reserva" : (saldoGarantia > 0.009 ? "garantia" : "reserva");
        modal.classList.add("show");
        this.configurarFlujoPago(flujo);
        if (flujo === "garantia") {
            this.cambiarTab("terminal");
        } else {
            this.cambiarTab("paypal");
        }
    },

    cerrarModalPago: function () {
        const modal = ContratoUI.DOM.modalPagos;
        if (modal) modal.classList.remove("show");
        ["pMonto", "pNotes", "fileTerminal", "fileTransfer"].forEach(id => {
            const el = document.getElementById(id);
            if (el) el.value = "";
        });
        const pp = document.getElementById("paypal-button-container-modal");
        if (pp) pp.innerHTML = "";
        const pHint = document.getElementById("pFlowHint");
        if (pHint) pHint.textContent = "Pago 1 de 2: registra primero el monto de la reservación.";
        this.paymentFlow = "reserva";
    },

    cambiarTab: function (nombre) {
        document.querySelectorAll("#payTabs .tab").forEach(t => t.classList.toggle("active", t.dataset.tab === nombre));
        ContratoUI.DOM.panes.forEach(p => p.style.display = (p.dataset.pane === nombre) ? "block" : "none");
        if (nombre === "paypal") this.prepararPayPal();
        else {
            const container = document.getElementById("paypal-button-container-modal");
            if (container) container.innerHTML = "";
        }
    },

    prepararPayPal: async function () {
        const container = document.getElementById("paypal-button-container-modal");
        if (!container) return;
        try {
            if (!this.paypalLoaded) {
                await new Promise((res, rej) => {
                    const s = document.createElement("script");
                    s.src = "https://www.paypal.com/sdk/js?client-id=ATzNpaAJlH7dFrWKu91xLmCzYVDQQF5DJ51b0OFICqchae6n8Pq7XkfsOOQNnElIJMt_Aj0GEZeIkFsp&currency=MXN";
                    s.onload = res;
                    s.onerror = rej;
                    document.head.appendChild(s);
                });
                this.paypalLoaded = true;
            }
            container.innerHTML = "";
            const monto = this.obtenerMontoPendiente();
            paypal.Buttons({
                style: { color: "gold", shape: "pill", label: "pay", height: 40 },
                createOrder: (d, a) => a.order.create({
                    purchase_units: [{ amount: { value: monto.toFixed(2), currency_code: "MXN" } }]
                }),
                onApprove: async (d, a) => {
                    const flujoAnterior = this.paymentFlow;
                    const tipoPago = document.getElementById("pTipo")?.value || "PAGO RESERVACIÓN";
                    const tipoPagoFinal = flujoAnterior === "garantia" ? "GARANTIA" : tipoPago;
                    const order = await a.order.capture();
                    await fetch(`/admin/contrato/pagos/paypal`, {
                        method: "POST",
                        headers: {
                            "Content-Type": "application/json",
                            "X-CSRF-TOKEN": window.csrfToken
                        },
                        body: JSON.stringify({
                            id_reservacion: window.ID_RESERVACION,
                            order_id: order.id,
                            monto: monto,
                            origen: "en linea",
                            metodo: "PAYPAL",
                            tipo_pago: tipoPagoFinal
                        })
                    });
                    this.cerrarModalPago();
                    await this.cargarResumen();
                    if (window.cargarResumenBasico) window.cargarResumenBasico();

                    if (flujoAnterior === "reserva" && this.obtenerMontoPendienteReserva() <= 0.009 && this.obtenerMontoPendienteGarantia() > 0.009) {
                        alertify.alert("Garantía", "Procediendo con el pago de la garantía. Puedes editar el monto antes de guardarlo.", () => {
                            this.abrirModalPago("garantia");
                        });
                    } else {
                        console.log(flujoAnterior === "garantia" ? "Garantía registrada." : "Pago PayPal exitoso.");
                    }
                },
                onError: () => {
                    console.error("Error PayPal");
                }
            }).render("#paypal-button-container-modal");
        } catch (e) {
            console.error("PayPal", e);
        }
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
            if (errBox) errBox.innerText = "Sube el comprobante.";
            return;
        }

        const fd = new FormData();
        fd.append("id_reservacion", window.ID_RESERVACION);
        fd.append("tipo_pago", this.paymentFlow === "garantia" ? "GARANTIA" : (document.getElementById("pTipo")?.value || "PAGO RESERVACIÓN"));
        fd.append("monto", document.getElementById("pMonto")?.value || 0);
        fd.append("notas", document.getElementById("pNotes")?.value || "");
        fd.append("metodo", m);
        fd.append("origen", o);
        fd.append("_token", window.csrfToken);
        if (file?.files[0]) fd.append("comprobante", file.files[0]);

        const btn = document.getElementById("pSave");
        if (btn) {
            btn.disabled = true;
            btn.innerText = "Guardando...";
        }
        try {
            const flujoAnterior = this.paymentFlow;
            const data = await (await fetch(`/admin/contrato/pagos/agregar`, {
                method: "POST",
                body: fd
            })).json();
            if (data.ok) {
                this.cerrarModalPago();
                await this.cargarResumen();
                if (window.cargarResumenBasico) window.cargarResumenBasico();

                if (flujoAnterior === "reserva" && this.obtenerMontoPendienteReserva() <= 0.009 && this.obtenerMontoPendienteGarantia() > 0.009) {
                    alertify.alert("Garantía", "Procediendo con el pago de la garantía. Puedes editar el monto antes de guardarlo.", () => {
                        this.abrirModalPago("garantia");
                    });
                } else {
                    console.log(flujoAnterior === "garantia" ? "Garantía registrada." : "Pago guardado.");
                }
            } else {
                if (errBox) errBox.innerText = data.msg || "Error";
            }
        } catch (e) {
            if (errBox) errBox.innerText = "Error conexión.";
        } finally {
            if (btn) {
                btn.disabled = false;
                btn.innerText = "GUARDAR PAGO";
            }
        }
    }
};

/**
 * MÓDULO 4: NAVEGACIÓN ENTRE PASOS
 */
const ContratoNav = {
    padPaso5: null,
    firmaPrevia: null,
    firmaPreviaAdicional: null,

    init: function () {
        document.getElementById("back_to_step3")?.addEventListener("click", () => {
            localStorage.setItem(`contratoPasoActual_${window.ID_RESERVACION}`, '3');
            window.location.href = `/admin/contrato/${window.ID_RESERVACION}`;
        });

        document.getElementById("btnSaltarDoc")?.addEventListener("click", () => this.irAlPaso5());
        document.getElementById("go6")?.addEventListener("click", (e) => this.irAlPaso6(e));
        document.getElementById("back4")?.addEventListener("click", () => {
            if (typeof window.showStep === 'function') window.showStep(4);
            if (typeof window.actualizarStepper === 'function') window.actualizarStepper(4);
        });
        document.getElementById("back5")?.addEventListener("click", () => {
            if (typeof window.showStep === 'function') window.showStep(5);
            if (typeof window.actualizarStepper === 'function') window.actualizarStepper(5);
        });

    },
/**
 * CREAR CANVAS PEQUEÑO - SOLO VISUAL (NO SE PUEDE FIRMAR)
 * La firma se hace ÚNICAMENTE en el modal a pantalla completa
 * Los botones "Limpiar" se muestran FUERA del modal, en la vista previa
 */
inyectarYCrearPad: function () {
    // === CANVAS DEL TITULAR ===
    const contenedorTitular = document.querySelector("#firmaPreviewWrapper");
    if (contenedorTitular) {
        // Guardar firma previa desde el input oculto
        const firmaGuardada = document.getElementById('firma_cliente_paso5')?.value;
        if (firmaGuardada) {
            this.firmaPrevia = firmaGuardada;
        }

        // Eliminar canvas viejo si existe
        const canvasViejo = document.getElementById("padPaso5");
        if (canvasViejo) canvasViejo.remove();

        let anchoReal = contenedorTitular.clientWidth;
        let altoReal = contenedorTitular.clientHeight;
        if (anchoReal === 0 || altoReal === 0) {
            setTimeout(() => this.inyectarYCrearPad(), 100);
            return;
        }

        const ratio = Math.max(window.devicePixelRatio || 1, 1);
        const nuevoCanvas = document.createElement("canvas");
        nuevoCanvas.id = "padPaso5";
        nuevoCanvas.style.cssText = `
            display: block;
            width: 100%;
            height: 100%;
            touch-action: none;
            cursor: default !important;
            border-radius: 10px;
            background: white;
            pointer-events: none !important;
            user-select: none;
        `;
        nuevoCanvas.width = anchoReal * ratio;
        nuevoCanvas.height = altoReal * ratio;

        const ctx = nuevoCanvas.getContext("2d");
        ctx.scale(ratio, ratio);
        ctx.fillStyle = 'white';
        ctx.fillRect(0, 0, anchoReal, altoReal);

        contenedorTitular.appendChild(nuevoCanvas);

        // Si hay una firma guardada, mostrarla en el canvas pequeño
        if (this.firmaPrevia) {
            this.mostrarFirmaEnCanvasPequeno(this.firmaPrevia);
        }

        // === CONFIGURAR BOTÓN LIMPIAR DEL TITULAR (FUERA DEL MODAL) ===
        let btnClearTitular = document.getElementById("clearPaso5");

        // Si no existe, crearlo
        if (!btnClearTitular) {
            btnClearTitular = document.createElement("button");
            btnClearTitular.type = "button";
            btnClearTitular.id = "clearPaso5";
            btnClearTitular.textContent = "✕ Limpiar";
            contenedorTitular.appendChild(btnClearTitular);
        } else {
            // Si existe, asegurarse de que esté dentro del wrapper
            if (!contenedorTitular.contains(btnClearTitular)) {
                contenedorTitular.appendChild(btnClearTitular);
            }
            btnClearTitular.textContent = "✕ Limpiar";
        }

        // Aplicar estilos (ya están en CSS, pero aseguramos visibilidad)
        btnClearTitular.style.display = 'block';
        btnClearTitular.style.position = 'absolute';
        btnClearTitular.style.bottom = '12px';
        btnClearTitular.style.right = '12px';
        btnClearTitular.style.zIndex = '60';

        // Remover eventos anteriores y agregar nuevo
        const newBtnTitular = btnClearTitular.cloneNode(true);
        btnClearTitular.parentNode.replaceChild(newBtnTitular, btnClearTitular);

        newBtnTitular.addEventListener("click", function(e) {
            e.stopPropagation();
            e.preventDefault();

            // Limpiar canvas pequeño del TITULAR
            const c = document.getElementById("padPaso5");
            if (c) {
                const ct = c.getContext("2d");
                ct.fillStyle = 'white';
                ct.fillRect(0, 0, c.width, c.height);
                // Mostrar placeholder
                const placeholder = document.getElementById('firmaPlaceholderPequeno');
                if (placeholder) placeholder.style.display = 'block';
            }
            // Limpiar input oculto del TITULAR
            const inputOculto = document.getElementById('firma_cliente_paso5');
            if (inputOculto) inputOculto.value = '';
            // Ocultar badge del TITULAR
            const badge = document.getElementById('firmaCompletadaBadge');
            if (badge) {
                badge.style.display = 'none';
                badge.style.background = '#dcfce7';
                badge.style.color = '#166534';
                badge.textContent = '✅ Firma registrada';
            }
            // Cambiar texto del botón del TITULAR
            const btnAbrirTitular = document.getElementById('btnAbrirFirmaModal');
            if (btnAbrirTitular) {
                btnAbrirTitular.innerHTML = '✍️ Firmar como Titular';
                btnAbrirTitular.style.background = '#eab308';
                btnAbrirTitular.style.color = 'white';
            }
            // Limpiar variable
            this.firmaPrevia = null;

            console.log('🧹 Firma del TITULAR limpiada');
        }.bind(this));

        console.log('✅ Botón Limpiar del TITULAR configurado en vista previa');
    }

    // === CANVAS DEL CONDUCTOR ADICIONAL ===
    const contenedorAdicional = document.querySelector("#firmaPreviewWrapperAdicional");
    if (contenedorAdicional) {
        // Guardar firma previa del adicional
        const firmaGuardadaAdicional = document.getElementById('firma_cliente_paso5_adicional')?.value;
        if (firmaGuardadaAdicional) {
            this.firmaPreviaAdicional = firmaGuardadaAdicional;
        }

        const canvasViejoAdicional = document.getElementById("padPaso5Adicional");
        if (canvasViejoAdicional) canvasViejoAdicional.remove();

        let anchoRealAdicional = contenedorAdicional.clientWidth;
        let altoRealAdicional = contenedorAdicional.clientHeight;
        if (anchoRealAdicional === 0 || altoRealAdicional === 0) {
            setTimeout(() => this.inyectarYCrearPad(), 100);
            return;
        }

        const ratioAdicional = Math.max(window.devicePixelRatio || 1, 1);
        const nuevoCanvasAdicional = document.createElement("canvas");
        nuevoCanvasAdicional.id = "padPaso5Adicional";
        nuevoCanvasAdicional.style.cssText = `
            display: block;
            width: 100%;
            height: 100%;
            touch-action: none;
            cursor: default !important;
            border-radius: 10px;
            background: white;
            pointer-events: none !important;
            user-select: none;
        `;
        nuevoCanvasAdicional.width = anchoRealAdicional * ratioAdicional;
        nuevoCanvasAdicional.height = altoRealAdicional * ratioAdicional;

        const ctxAdicional = nuevoCanvasAdicional.getContext("2d");
        ctxAdicional.scale(ratioAdicional, ratioAdicional);
        ctxAdicional.fillStyle = 'white';
        ctxAdicional.fillRect(0, 0, anchoRealAdicional, altoRealAdicional);

        contenedorAdicional.appendChild(nuevoCanvasAdicional);

        // Si hay una firma guardada, mostrarla en el canvas pequeño
        if (this.firmaPreviaAdicional) {
            this.mostrarFirmaEnCanvasPequenoAdicional(this.firmaPreviaAdicional);
        }

        // === CONFIGURAR BOTÓN LIMPIAR DEL ADICIONAL (FUERA DEL MODAL) ===
        let btnClearAdicional = document.getElementById("clearPaso5Adicional");

        if (!btnClearAdicional) {
            btnClearAdicional = document.createElement("button");
            btnClearAdicional.type = "button";
            btnClearAdicional.id = "clearPaso5Adicional";
            btnClearAdicional.textContent = "✕ Limpiar";
            contenedorAdicional.appendChild(btnClearAdicional);
        } else {
            if (!contenedorAdicional.contains(btnClearAdicional)) {
                contenedorAdicional.appendChild(btnClearAdicional);
            }
            btnClearAdicional.textContent = "✕ Limpiar";
        }

        btnClearAdicional.style.display = 'block';
        btnClearAdicional.style.position = 'absolute';
        btnClearAdicional.style.bottom = '12px';
        btnClearAdicional.style.right = '12px';
        btnClearAdicional.style.zIndex = '60';

        const newBtnAdicional = btnClearAdicional.cloneNode(true);
        btnClearAdicional.parentNode.replaceChild(newBtnAdicional, btnClearAdicional);

        newBtnAdicional.addEventListener("click", function(e) {
            e.stopPropagation();
            e.preventDefault();

            const c = document.getElementById("padPaso5Adicional");
            if (c) {
                const ct = c.getContext("2d");
                ct.fillStyle = 'white';
                ct.fillRect(0, 0, c.width, c.height);
                const placeholder = document.getElementById('firmaPlaceholderAdicional');
                if (placeholder) placeholder.style.display = 'block';
            }
            const inputOculto = document.getElementById('firma_cliente_paso5_adicional');
            if (inputOculto) inputOculto.value = '';
            const badge = document.getElementById('firmaCompletadaBadgeAdicional');
            if (badge) {
                badge.style.display = 'none';
                badge.style.background = '#dbeafe';
                badge.style.color = '#1e40af';
                badge.textContent = '✅ Firma registrada';
            }
            const btnAbrirAdicional = document.getElementById('btnAbrirFirmaModalAdicional');
            if (btnAbrirAdicional) {
                btnAbrirAdicional.innerHTML = '✍️ Firmar como Conductor Adicional';
                btnAbrirAdicional.style.background = '#3b82f6';
                btnAbrirAdicional.style.color = 'white';
            }
            this.firmaPreviaAdicional = null;

            console.log('🧹 Firma del ADICIONAL limpiada');
        }.bind(this));

        console.log('✅ Botón Limpiar del ADICIONAL configurado en vista previa');
    }

    console.log('✅ Canvas pequeños creados con botones "Limpiar" en vista previa');
},
    /**
     * MOSTRAR FIRMA EN CANVAS PEQUEÑO DEL TITULAR (SOLO VISUAL)
     */
    mostrarFirmaEnCanvasPequeno: function(firmaDataURL) {
        const canvas = document.getElementById("padPaso5");
        if (!canvas) return;

        try {
            const ctx = canvas.getContext('2d');
            const rect = canvas.getBoundingClientRect();
            const width = rect.width || canvas.width || 300;
            const height = rect.height || canvas.height || 200;

            if (canvas.width === 0 || canvas.height === 0) {
                canvas.width = width;
                canvas.height = height;
            }

            ctx.clearRect(0, 0, canvas.width, canvas.height);
            ctx.fillStyle = '#ffffff';
            ctx.fillRect(0, 0, canvas.width, canvas.height);

            const img = new Image();
            img.onload = function() {
                const ratio = Math.min(
                    canvas.width / img.width,
                    canvas.height / img.height
                );
                const margen = 0.85;
                const finalRatio = ratio * margen;
                const newWidth = img.width * finalRatio;
                const newHeight = img.height * finalRatio;
                const x = (canvas.width - newWidth) / 2;
                const y = (canvas.height - newHeight) / 2;
                ctx.drawImage(img, x, y, newWidth, newHeight);

                const placeholder = document.getElementById('firmaPlaceholderPequeno');
                if (placeholder) placeholder.style.display = 'none';

                console.log('✅ Firma del titular mostrada en canvas pequeño');
            };
            img.onerror = function() {
                console.warn('⚠️ No se pudo cargar la firma del titular en el canvas pequeño');
            };
            img.src = firmaDataURL;
        } catch (e) {
            console.error('Error al mostrar firma del titular en canvas pequeño:', e);
        }
    },

    /**
     * MOSTRAR FIRMA EN CANVAS PEQUEÑO DEL ADICIONAL (SOLO VISUAL)
     */
    mostrarFirmaEnCanvasPequenoAdicional: function(firmaDataURL) {
        const canvas = document.getElementById("padPaso5Adicional");
        if (!canvas) return;

        try {
            const ctx = canvas.getContext('2d');
            const rect = canvas.getBoundingClientRect();
            const width = rect.width || canvas.width || 300;
            const height = rect.height || canvas.height || 160;

            if (canvas.width === 0 || canvas.height === 0) {
                canvas.width = width;
                canvas.height = height;
            }

            ctx.clearRect(0, 0, canvas.width, canvas.height);
            ctx.fillStyle = '#ffffff';
            ctx.fillRect(0, 0, canvas.width, canvas.height);

            const img = new Image();
            img.onload = function() {
                const ratio = Math.min(
                    canvas.width / img.width,
                    canvas.height / img.height
                );
                const margen = 0.85;
                const finalRatio = ratio * margen;
                const newWidth = img.width * finalRatio;
                const newHeight = img.height * finalRatio;
                const x = (canvas.width - newWidth) / 2;
                const y = (canvas.height - newHeight) / 2;
                ctx.drawImage(img, x, y, newWidth, newHeight);

                const placeholder = document.getElementById('firmaPlaceholderAdicional');
                if (placeholder) placeholder.style.display = 'none';

                console.log('✅ Firma del adicional mostrada en canvas pequeño');
            };
            img.onerror = function() {
                console.warn('⚠️ No se pudo cargar la firma del adicional en el canvas pequeño');
            };
            img.src = firmaDataURL;
        } catch (e) {
            console.error('Error al mostrar firma del adicional en canvas pequeño:', e);
        }
    },

    irAlPaso5: function () {
        const totalBarra = document.getElementById('resumenTotalBarra')?.innerText.trim();
        const totalCompacto = document.getElementById('resumenTotalCompacto')?.innerText.trim();
        const resTotalTablet = document.getElementById('res-total-final-p5');
        if (resTotalTablet) {
            resTotalTablet.innerText = totalBarra || totalCompacto || "$0.00 MXN";
        }
        if (typeof window.showStep === 'function') window.showStep(5);
        if (typeof window.actualizarStepper === 'function') window.actualizarStepper(5);
    },

    // ================================================================
    // irAlPaso6 - VERSIÓN CORREGIDA (FORZAR AVANCE)
    // ================================================================
   irAlPaso6: async function (e) {
    const btn = e.target || document.getElementById("go6");

    // Validar lugar de estancia
    const inputEstancia = document.getElementById('lugar_estancia');
    const errorEstancia = document.getElementById('error-estancia');

    if (inputEstancia && inputEstancia.value.trim() === '') {
        inputEstancia.style.border = "2px solid #ef4444";
        if (errorEstancia) errorEstancia.style.display = "block";
        inputEstancia.scrollIntoView({ behavior: 'smooth', block: 'center' });
        inputEstancia.focus();
        return;
    } else if (inputEstancia) {
        inputEstancia.style.border = "1px solid #cbd5e1";
        if (errorEstancia) errorEstancia.style.display = "none";
    }

    // NUEVO: persistir firma del titular + lugar de estancia
    const firmaTitular = document.getElementById('firma_cliente_paso5')?.value || '';
    if (firmaTitular) {
        try {
            if (btn) btn.disabled = true;
            const resp = await fetch('/contrato/firma-cliente', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': window.csrfToken
                },
                body: JSON.stringify({
                    id_contrato: window.ID_CONTRATO,
                    firma: firmaTitular,
                    lugar_estancia: inputEstancia ? inputEstancia.value.trim() : ''
                })
            });
            const data = await resp.json();
            if (!data.ok) {
                console.error('No se guardó la firma:', data.msg);
                ContratoUI.mostrarNotificacion('error', data.msg || 'No se pudo guardar la firma.');
                if (btn) btn.disabled = false;
                return; // no avanzar si el guardado falló
            }
        } catch (err) {
            console.error('Error guardando firma:', err);
            ContratoUI.mostrarNotificacion('error', 'Error de conexión al guardar la firma.');
            if (btn) btn.disabled = false;
            return;
        }
    }

    // Navegar al Paso 6
    if (btn) btn.disabled = false;
    if (typeof window.showStep === 'function') window.showStep(6);
    if (typeof window.cargarResumenBasico === 'function') window.cargarResumenBasico();
    if (typeof window.actualizarStepper === 'function') window.actualizarStepper(6);
    if (typeof window.cargarPaso6 === 'function') window.cargarPaso6();

    console.log('✅ Navegando al Paso 6');
},

    sincronizarDatosTablet: function () {
        const container = document.getElementById('res-lista-conductores');
        if (container) {
            let htmlCond = '';
            const inputs = document.querySelectorAll('input[name$="[nombre]"]');
            inputs.forEach((inp, i) => {
                const pre = inp.name.replace('[nombre]', '');
                const nom = inp.value.trim() || 'CONDUCTOR';
                const apePat = document.querySelector(`input[name="${pre}[apellido_paterno]"]`)?.value.trim() || '';
                const apeMat = document.querySelector(`input[name="${pre}[apellido_materno]"]`)?.value.trim() || '';
                const nombreCompleto = [nom, apePat, apeMat].filter(Boolean).join(' ');
                htmlCond += `<p style="font-size:13px; margin-bottom:2px;">${i === 0 ? '<b>' : ''}${nombreCompleto}${i === 0 ? ' (Titular)</b>' : ' (Adicional)'}</p>`;
            });
            container.innerHTML = htmlCond;
        }

        const nomTit = document.querySelector('input[name="conductores[0][nombre]"]')?.value.trim();
        const apePatTit = document.querySelector('input[name="conductores[0][apellido_paterno]"]')?.value.trim();
        const apeMatTit = document.querySelector('input[name="conductores[0][apellido_materno]"]')?.value.trim();
        const nombreFirma = [nomTit, apePatTit, apeMatTit].filter(Boolean).join(' ') || '—';
        const firmaParrafo = document.querySelector('.signature-section p.txt-primary');
        if (firmaParrafo) {
            firmaParrafo.textContent = nombreFirma;
        }

        const fuenteSeguros = document.getElementById('r_seguros_lista');
        const resCoberturas = document.getElementById('res-lista-coberturas');
        if (fuenteSeguros && resCoberturas) {
            const esVacio = fuenteSeguros.querySelector('.empty') || fuenteSeguros.innerText.trim() === '—';
            resCoberturas.innerHTML = esVacio
                ? '<li class="txt-muted">Protección Básica (TPL)</li>'
                : fuenteSeguros.innerHTML;
        }

        const fuenteExtras = document.getElementById('r_servicios_lista');
        const resExtras = document.getElementById('res-lista-extras');
        if (fuenteExtras && resExtras) {
            const esVacio = fuenteExtras.querySelector('.empty') || fuenteExtras.innerText.trim() === '—';
            resExtras.innerHTML = esVacio
                ? '<li class="txt-muted">Sin servicios adicionales</li>'
                : fuenteExtras.innerHTML;
        }
    }
};

// ================================================================
// MÓDULO 5: NAVBAR Y RESUMEN DESPLEGABLE
// ================================================================

const ContratoNavbar = {
    init: function () {
        console.log('🚀 Inicializando Navbar...');
        this.initResumenToggle();
        this.initNavbarScrollEffect();
        this.sincronizarTotalNavbar();
    },

    initResumenToggle: function () {
        const btnToggle = document.getElementById('btnToggleDetalle');
        const detalleContainer = document.getElementById('resumenDetalleContainer');
        const flechaIcon = document.getElementById('iconoFlechaResumen');

        if (!btnToggle || !detalleContainer) {
            console.warn('⚠️ No se encontraron los elementos del resumen');
            return;
        }

        detalleContainer.classList.remove('visible');
        detalleContainer.style.display = 'none';

        const toggleResumen = (show) => {
            if (show) {
                detalleContainer.style.display = 'block';
                void detalleContainer.offsetHeight;
                detalleContainer.classList.add('visible');
                if (flechaIcon) flechaIcon.classList.add('rotada');
                console.log('✅ Resumen abierto');
            } else {
                detalleContainer.classList.remove('visible');
                if (flechaIcon) flechaIcon.classList.remove('rotada');
                setTimeout(() => {
                    detalleContainer.style.display = 'none';
                }, 300);
                console.log('✅ Resumen cerrado');
            }
        };

        btnToggle.addEventListener('click', function (e) {
            e.stopPropagation();
            e.preventDefault();
            const isVisible = detalleContainer.classList.contains('visible');
            toggleResumen(!isVisible);
        });

        document.addEventListener('click', function (e) {
            if (detalleContainer.classList.contains('visible')) {
                if (!btnToggle.contains(e.target) && !detalleContainer.contains(e.target)) {
                    toggleResumen(false);
                }
            }
        });

        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape' && detalleContainer.classList.contains('visible')) {
                toggleResumen(false);
            }
        });

        const btnVerDetalle = document.getElementById('btnVerDetalle');
        const btnOcultarDetalle = document.getElementById('btnOcultarDetalle');
        const resumenCompacto = document.getElementById('resumenCompacto');
        const resumenDetalle = document.getElementById('resumenDetalle');

        if (btnVerDetalle && resumenCompacto && resumenDetalle) {
            btnVerDetalle.addEventListener('click', function (e) {
                e.stopPropagation();
                resumenCompacto.style.display = 'none';
                resumenDetalle.style.display = 'block';
            });
        }

        if (btnOcultarDetalle && resumenCompacto && resumenDetalle) {
            btnOcultarDetalle.addEventListener('click', function (e) {
                e.stopPropagation();
                resumenCompacto.style.display = 'block';
                resumenDetalle.style.display = 'none';
            });
        }

        console.log('✅ Toggle del resumen inicializado correctamente');
    },

    initNavbarScrollEffect: function () {
        const navbar = document.querySelector('.topbar-contrato');
        if (!navbar) return;
        const scrollThreshold = 50;

        const handleScroll = () => {
            if (window.scrollY > scrollThreshold) {
                navbar.classList.add('scrolled');
            } else {
                navbar.classList.remove('scrolled');
            }
        };

        let ticking = false;
        window.addEventListener('scroll', function () {
            if (!ticking) {
                requestAnimationFrame(function () {
                    handleScroll();
                    ticking = false;
                });
                ticking = true;
            }
        });
        handleScroll();
    },

    sincronizarTotalNavbar: function () {
        const totalFinalEl = document.getElementById('r_total_final');
        const resumenTotalBarra = document.getElementById('resumenTotalBarra');
        const resumenTotalCompacto = document.getElementById('resumenTotalCompacto');
        const btnTotalText = document.getElementById('btnTotalTextContrato');
        const btnTotalUsd = document.getElementById('btnTotalUsdContrato');

        let totalTexto = null;
        let totalNumerico = 0;

        if (totalFinalEl && totalFinalEl.textContent) {
            totalTexto = totalFinalEl.textContent;
            totalNumerico = parseFloat(totalFinalEl.textContent.replace(/[^0-9.-]/g, '')) || 0;
        } else if (resumenTotalBarra && resumenTotalBarra.textContent) {
            totalTexto = resumenTotalBarra.textContent;
            totalNumerico = parseFloat(resumenTotalBarra.textContent.replace(/[^0-9.-]/g, '')) || 0;
        } else if (resumenTotalCompacto && resumenTotalCompacto.textContent) {
            totalTexto = resumenTotalCompacto.textContent;
            totalNumerico = parseFloat(resumenTotalCompacto.textContent.replace(/[^0-9.-]/g, '')) || 0;
        }

        if (btnTotalText && totalTexto) {
            btnTotalText.innerHTML = totalTexto;
        }

        if (btnTotalUsd && totalNumerico > 0) {
            btnTotalUsd.textContent = `$${(totalNumerico / 18.5).toFixed(2)} USD`;
        }
    }
};

/**
 * INICIALIZACIÓN GLOBAL
 */
document.addEventListener("DOMContentLoaded", () => {
    document.body.classList.add('sidebar-collapse');
    document.body.classList.remove('sidebar-open');

    ContratoNavbar.init();

    if (typeof window.showStep === 'function' && !window.showStep.isPatched) {
        const originalShowStep = window.showStep;
        window.showStep = function (step, force) {
            originalShowStep(step, force);
            if (step === 5 || step === "5") {
                ContratoNav.sincronizarDatosTablet();
                setTimeout(() => ContratoNav.inyectarYCrearPad(), 200);
            } else if (step === 4 || step === "4") {
                ContratoPaso5.cargarDocumentacionGuardada();
            } else if (step === 6 || step === "6") {
                if (typeof window.cargarPaso6 === 'function') window.cargarPaso6();
            }
        };
        window.showStep.isPatched = true;
    }

    const idRes = window.ID_RESERVACION ||
        document.getElementById("contratoApp")?.dataset.idReservacion ||
        window.location.pathname.split('/').pop();

    const storageKey = `contratoPasoActual_${idRes}`;
    const pasoSolicitado = localStorage.getItem(storageKey);
    let pasoInicial = 4;

    if (pasoSolicitado && ["4", "5", "6"].includes(pasoSolicitado.toString())) {
        pasoInicial = parseInt(pasoSolicitado);
        localStorage.removeItem(storageKey);
    }

    ContratoUI.cacheDOM();
    ContratoUI.initGlobalEvents();
    ContratoPaso5.init();
    ContratoPaso6.init();
    ContratoNav.init();

    window.cargarPaso6 = ContratoPaso6.cargarResumen.bind(ContratoPaso6);

    if (typeof window.showStep === 'function') {
        window.showStep(pasoInicial, false);
    }
    if (typeof window.actualizarStepper === 'function') {
        window.actualizarStepper(pasoInicial);
    }

    // ================================================================
    // INICIALIZACIÓN DEL MODAL DE FIRMA
    // ================================================================
    setTimeout(function() {
        FirmaModal.init();
        // Inicializar firma adicional si existe
        if (document.querySelector('.signature-adicional')) {
            FirmaModalAdicional.init();
        }
    }, 500);
});

// ================================================================
// MÓDULO 6: MODAL DE FIRMA - PANTALLA COMPLETA
// ================================================================

const FirmaModal = {
    modal: null,
    canvas: null,
    pad: null,
    clienteNombre: '',
    onSaveCallback: null,
    isOpen: false,

    init: function() {
        console.log('🔧 Inicializando FirmaModal...');

        this.modal = document.getElementById('firmaModal');
        this.canvas = document.getElementById('firmaModalCanvas');

        if (!this.modal || !this.canvas) {
            console.error('❌ Modal o canvas no encontrados');
            return;
        }

        console.log('✅ Modal y canvas encontrados');

        const nombreEl = document.querySelector('.signature-section .txt-primary');
        this.clienteNombre = nombreEl ? nombreEl.textContent.trim() : 'Cliente';
        document.getElementById('firmaModalCliente').textContent = this.clienteNombre;

        document.getElementById('firmaModalClose')?.addEventListener('click', () => this.cerrar());
        document.getElementById('firmaModalLimpiar')?.addEventListener('click', () => this.limpiar());
        document.getElementById('firmaModalGuardar')?.addEventListener('click', () => this.guardar());

        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape' && this.isOpen) this.cerrar();
        });

        this.modal.addEventListener('click', (e) => {
            if (e.target === this.modal) this.cerrar();
        });

        this.configurarApertura();

        const canvasPequeno = document.getElementById('padPaso5');
        if (canvasPequeno) {
            canvasPequeno.style.pointerEvents = 'none';
            canvasPequeno.style.cursor = 'default';
        }

        console.log('✅ FirmaModal inicializado correctamente');
    },

    configurarApertura: function() {
        const btnAbrir = document.getElementById('btnAbrirFirmaModal');
        const wrapper = document.getElementById('firmaPreviewWrapper');

        const abrir = () => {
            console.log('🖱️ Abriendo modal de firma...');
            this.abrir();
        };

        if (btnAbrir) {
            btnAbrir.addEventListener('click', abrir);
        }

        if (wrapper) {
            wrapper.addEventListener('click', function(e) {
                if (!e.target.closest('#clearPaso5')) {
                    abrir();
                }
            });
        }

        const btnClear = document.getElementById('clearPaso5');
        if (btnClear) {
            btnClear.addEventListener('click', function(e) {
                e.stopPropagation();
                const canvasPequeno = document.getElementById('padPaso5');
                if (canvasPequeno) {
                    const ctx = canvasPequeno.getContext('2d');
                    ctx.clearRect(0, 0, canvasPequeno.width, canvasPequeno.height);
                    ctx.fillStyle = '#ffffff';
                    ctx.fillRect(0, 0, canvasPequeno.width, canvasPequeno.height);
                }
                document.getElementById('firma_cliente_paso5').value = '';
                document.getElementById('firmaCompletadaBadge').style.display = 'none';
                document.getElementById('firmaPlaceholderPequeno').style.display = 'block';
                const btnAbrir = document.getElementById('btnAbrirFirmaModal');
                if (btnAbrir) {
                    btnAbrir.innerHTML = '✍️ Firmar en Pantalla Completa';
                    btnAbrir.style.background = '#eab308';
                }
            });
        }
    },

    handleSave: function(firmaDataURL, firmaData) {
        const inputOculto = document.getElementById('firma_cliente_paso5');
        if (inputOculto) {
            inputOculto.value = firmaDataURL;
        }

        this.mostrarFirmaEnCanvasPequeno(firmaDataURL);

        const badge = document.getElementById('firmaCompletadaBadge');
        if (badge) badge.style.display = 'block';

        const btnAbrir = document.getElementById('btnAbrirFirmaModal');
        if (btnAbrir) {
            btnAbrir.innerHTML = '✅ Firma Registrada';
            btnAbrir.style.background = '#22c55e';
        }

        const placeholderPequeno = document.getElementById('firmaPlaceholderPequeno');
        if (placeholderPequeno) placeholderPequeno.style.display = 'none';

        console.log('✅ Firma guardada correctamente.');
        this.cerrar();
    },

    mostrarFirmaEnCanvasPequeno: function(firmaDataURL) {
        const canvasPequeno = document.getElementById('padPaso5');
        if (!canvasPequeno) return;

        try {
            const ctx = canvasPequeno.getContext('2d');
            const rect = canvasPequeno.getBoundingClientRect();
            const width = rect.width || canvasPequeno.width || 300;
            const height = rect.height || canvasPequeno.height || 200;

            if (canvasPequeno.width === 0 || canvasPequeno.height === 0) {
                canvasPequeno.width = width;
                canvasPequeno.height = height;
            }

            ctx.clearRect(0, 0, canvasPequeno.width, canvasPequeno.height);
            ctx.fillStyle = '#ffffff';
            ctx.fillRect(0, 0, canvasPequeno.width, canvasPequeno.height);

            const img = new Image();
            img.onload = function() {
                const ratio = Math.min(
                    canvasPequeno.width / img.width,
                    canvasPequeno.height / img.height
                );
                const margen = 0.90;
                const finalRatio = ratio * margen;
                const newWidth = img.width * finalRatio;
                const newHeight = img.height * finalRatio;
                const x = (canvasPequeno.width - newWidth) / 2;
                const y = (canvasPequeno.height - newHeight) / 2;
                ctx.drawImage(img, x, y, newWidth, newHeight);
                console.log('✅ Firma mostrada en canvas pequeño');
            };
            img.onerror = function() {
                console.warn('Error al cargar imagen de firma');
            };
            img.src = firmaDataURL;
        } catch (e) {
            console.error('Error al mostrar firma en canvas pequeño:', e);
        }
    },

    mostrarMensaje: function(tipo, texto) {
        console.log(`[${tipo}] ${texto}`);
        return;
    },

    abrir: function() {
        this.modal?.classList.add('active');
        this.isOpen = true;
        setTimeout(() => this.crearCanvas(), 100);
    },

    cerrar: function() {
        this.modal?.classList.remove('active');
        this.isOpen = false;
        const mensaje = document.getElementById('firmaMensajeEstado');
        if (mensaje) mensaje.style.display = 'none';
    },

    crearCanvas: function() {
        const wrapper = document.getElementById('firmaCanvasWrapper');
        if (!wrapper) return;

        const rect = wrapper.getBoundingClientRect();
        const ancho = rect.width || 600;
        const alto = rect.height || 400;

        const ratio = Math.max(window.devicePixelRatio || 1, 1);
        this.canvas.width = ancho * ratio;
        this.canvas.height = alto * ratio;
        this.canvas.style.width = ancho + 'px';
        this.canvas.style.height = alto + 'px';

        const ctx = this.canvas.getContext('2d');
        ctx.scale(ratio, ratio);
        ctx.fillStyle = '#ffffff';
        ctx.fillRect(0, 0, ancho, alto);

        if (this.pad) {
            this.pad.clear();
            this.pad = null;
        }

        this.pad = new SignaturePad(this.canvas, {
            minWidth: 2.5,
            maxWidth: 4.5,
            penColor: '#1e293b',
            backgroundColor: '#ffffff',
            velocityFilterWeight: 0.7
        });

        const placeholder = document.getElementById('firmaPlaceholder');
        this.pad.onBegin = () => {
            if (placeholder) placeholder.style.display = 'none';
        };

        const limpiarOriginal = this.pad.clear.bind(this.pad);
        this.pad.clear = function() {
            limpiarOriginal();
            if (placeholder) placeholder.style.display = 'block';
        };

        const actualizarPlaceholder = () => {
            if (this.pad && this.pad.isEmpty()) {
                if (placeholder) placeholder.style.display = 'block';
            } else {
                if (placeholder) placeholder.style.display = 'none';
            }
        };

        let resizeTimeout;
        const resizeHandler = () => {
            if (!this.isOpen) return;
            const datos = this.pad?.toData();
            const nuevoAncho = wrapper.clientWidth;
            const nuevoAlto = wrapper.clientHeight;
            if (nuevoAncho > 0 && nuevoAlto > 0) {
                const nuevoRatio = Math.max(window.devicePixelRatio || 1, 1);
                this.canvas.width = nuevoAncho * nuevoRatio;
                this.canvas.height = nuevoAlto * nuevoRatio;
                this.canvas.style.width = nuevoAncho + 'px';
                this.canvas.style.height = nuevoAlto + 'px';
                const nuevoCtx = this.canvas.getContext('2d');
                nuevoCtx.scale(nuevoRatio, nuevoRatio);
                nuevoCtx.fillStyle = '#ffffff';
                nuevoCtx.fillRect(0, 0, nuevoAncho, nuevoAlto);
                this.pad = new SignaturePad(this.canvas, {
                    minWidth: 2.5,
                    maxWidth: 4.5,
                    penColor: '#1e293b',
                    backgroundColor: '#ffffff',
                    velocityFilterWeight: 0.7
                });
                if (datos && datos.length > 0) {
                    this.pad.fromData(datos);
                }
                if (this.pad.isEmpty()) {
                    if (placeholder) placeholder.style.display = 'block';
                } else {
                    if (placeholder) placeholder.style.display = 'none';
                }
            }
        };

        const debouncedResize = () => {
            clearTimeout(resizeTimeout);
            resizeTimeout = setTimeout(resizeHandler, 250);
        };

        if (this._resizeListener) {
            window.removeEventListener('resize', this._resizeListener);
        }
        this._resizeListener = debouncedResize;
        window.addEventListener('resize', this._resizeListener);

        console.log('✅ Canvas de firma creado');
    },

    limpiar: function() {
        if (this.pad) {
            this.pad.clear();
            document.getElementById('firmaPlaceholder').style.display = 'block';
        }
        document.getElementById('firmaMensajeEstado').style.display = 'none';
    },

    guardar: function() {
        if (!this.pad) {
            console.error('Error: No se pudo capturar la firma.');
            return;
        }

        if (this.pad.isEmpty()) {
            console.warn('Por favor, firma en el área antes de guardar.');
            return;
        }

        const btn = document.getElementById('firmaModalGuardar');
        if (btn) {
            btn.disabled = true;
            btn.textContent = '⏳ Guardando...';
        }

        try {
            const firmaDataURL = this.pad.toDataURL('image/png');
            const firmaData = this.pad.toData();

            if (typeof this.onSaveCallback === 'function') {
                this.onSaveCallback(firmaDataURL, firmaData);
            } else {
                const inputOculto = document.getElementById('firma_cliente_paso5');
                if (inputOculto) {
                    inputOculto.value = firmaDataURL;
                }

                this.mostrarFirmaEnCanvasPequeno(firmaDataURL);

                const badge = document.getElementById('firmaCompletadaBadge');
                if (badge) badge.style.display = 'block';

                const btnAbrir = document.getElementById('btnAbrirFirmaModal');
                if (btnAbrir) {
                    btnAbrir.innerHTML = '✅ Firma Registrada';
                    btnAbrir.style.background = '#22c55e';
                }

                const placeholderPequeno = document.getElementById('firmaPlaceholderPequeno');
                if (placeholderPequeno) placeholderPequeno.style.display = 'none';

                console.log('✅ Firma guardada correctamente.');
                this.cerrar();
            }
        } catch (error) {
            console.error('Error al guardar la firma:', error);
        } finally {
            if (btn) {
                btn.disabled = false;
                btn.textContent = '💾 Guardar Firma';
            }
        }
    }
};

// Re-inicializar al cambiar al Paso 5
document.addEventListener('stepChanged', function(e) {
    if (e.detail && (e.detail.step === 5 || e.detail.step === '5')) {
        setTimeout(() => FirmaModal.init(), 300);
        if (document.querySelector('.signature-adicional')) {
            setTimeout(() => FirmaModalAdicional.init(), 400);
        }
    }
});

// Si existe window.showStep, parchearlo
if (typeof window.showStep === 'function' && !window.showStep._firmaPatched) {
    const originalShowStep = window.showStep;
    window.showStep = function(step, force) {
        originalShowStep(step, force);
        if (step === 5 || step === '5') {
            setTimeout(() => FirmaModal.init(), 400);
            if (document.querySelector('.signature-adicional')) {
                setTimeout(() => FirmaModalAdicional.init(), 500);
            }
        }
    };
    window.showStep._firmaPatched = true;
}

// ================================================================
// MÓDULO 7: FIRMA DEL CONDUCTOR ADICIONAL
// ================================================================

const FirmaModalAdicional = {
    modal: null,
    canvas: null,
    pad: null,
    clienteNombre: '',
    isOpen: false,

    init: function() {
        // Verificar si existe el conductor adicional
        const signatureAdicional = document.querySelector('.signature-adicional');
        if (!signatureAdicional) {
            console.log('ℹ️ No hay conductor adicional, omitiendo firma adicional.');
            return;
        }

        console.log('🔧 Inicializando FirmaModalAdicional...');

        this.modal = document.getElementById('firmaModalAdicional');
        this.canvas = document.getElementById('firmaModalCanvasAdicional');

        if (!this.modal || !this.canvas) {
            console.error('❌ Modal o canvas adicional no encontrados');
            return;
        }

        // Obtener nombre del conductor adicional
        const nombreEl = document.querySelector('.signature-adicional .txt-primary');
        this.clienteNombre = nombreEl ? nombreEl.textContent.trim() : 'Conductor Adicional';
        document.getElementById('firmaModalClienteAdicional').textContent = this.clienteNombre;

        // Eventos
        document.getElementById('firmaModalCloseAdicional')?.addEventListener('click', () => this.cerrar());
        document.getElementById('firmaModalLimpiarAdicional')?.addEventListener('click', () => this.limpiar());
        document.getElementById('firmaModalGuardarAdicional')?.addEventListener('click', () => this.guardar());

        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape' && this.isOpen) this.cerrar();
        });

        this.modal.addEventListener('click', (e) => {
            if (e.target === this.modal) this.cerrar();
        });

        this.configurarApertura();

        console.log('✅ FirmaModalAdicional inicializado correctamente');
    },

    configurarApertura: function() {
        const btnAbrir = document.getElementById('btnAbrirFirmaModalAdicional');
        const wrapper = document.getElementById('firmaPreviewWrapperAdicional');

        const abrir = () => {
            console.log('🖱️ Abriendo modal de firma adicional...');
            this.abrir();
        };

        if (btnAbrir) {
            btnAbrir.addEventListener('click', abrir);
        }

        if (wrapper) {
            wrapper.addEventListener('click', function(e) {
                if (!e.target.closest('#clearPaso5Adicional')) {
                    abrir();
                }
            });
        }

        const btnClear = document.getElementById('clearPaso5Adicional');
        if (btnClear) {
            btnClear.addEventListener('click', function(e) {
                e.stopPropagation();
                const canvasPequeno = document.getElementById('padPaso5Adicional');
                if (canvasPequeno) {
                    const ctx = canvasPequeno.getContext('2d');
                    ctx.clearRect(0, 0, canvasPequeno.width, canvasPequeno.height);
                    ctx.fillStyle = '#ffffff';
                    ctx.fillRect(0, 0, canvasPequeno.width, canvasPequeno.height);
                }
                document.getElementById('firma_cliente_paso5_adicional').value = '';
                document.getElementById('firmaCompletadaBadgeAdicional').style.display = 'none';
                document.getElementById('firmaPlaceholderAdicional').style.display = 'block';
                const btnAbrir = document.getElementById('btnAbrirFirmaModalAdicional');
                if (btnAbrir) {
                    btnAbrir.innerHTML = '✍️ Firmar como Conductor Adicional';
                    btnAbrir.style.background = '#3b82f6';
                }
            });
        }
    },

    abrir: function() {
        this.modal?.classList.add('active');
        this.isOpen = true;
        setTimeout(() => this.crearCanvas(), 100);
    },

    cerrar: function() {
        this.modal?.classList.remove('active');
        this.isOpen = false;
        const mensaje = document.getElementById('firmaMensajeEstadoAdicional');
        if (mensaje) mensaje.style.display = 'none';
    },

    crearCanvas: function() {
        const wrapper = document.getElementById('firmaCanvasWrapperAdicional');
        if (!wrapper) return;

        const rect = wrapper.getBoundingClientRect();
        const ancho = rect.width || 600;
        const alto = rect.height || 400;

        const ratio = Math.max(window.devicePixelRatio || 1, 1);
        this.canvas.width = ancho * ratio;
        this.canvas.height = alto * ratio;
        this.canvas.style.width = ancho + 'px';
        this.canvas.style.height = alto + 'px';

        const ctx = this.canvas.getContext('2d');
        ctx.scale(ratio, ratio);
        ctx.fillStyle = '#ffffff';
        ctx.fillRect(0, 0, ancho, alto);

        if (this.pad) {
            this.pad.clear();
            this.pad = null;
        }

        this.pad = new SignaturePad(this.canvas, {
            minWidth: 2.5,
            maxWidth: 4.5,
            penColor: '#1e293b',
            backgroundColor: '#ffffff',
            velocityFilterWeight: 0.7
        });

        const placeholder = document.getElementById('firmaPlaceholderAdicionalModal');
        this.pad.onBegin = () => {
            if (placeholder) placeholder.style.display = 'none';
        };

        const limpiarOriginal = this.pad.clear.bind(this.pad);
        this.pad.clear = function() {
            limpiarOriginal();
            if (placeholder) placeholder.style.display = 'block';
        };

        const actualizarPlaceholder = () => {
            if (this.pad && this.pad.isEmpty()) {
                if (placeholder) placeholder.style.display = 'block';
            } else {
                if (placeholder) placeholder.style.display = 'none';
            }
        };

        let resizeTimeout;
        const resizeHandler = () => {
            if (!this.isOpen) return;
            const datos = this.pad?.toData();
            const nuevoAncho = wrapper.clientWidth;
            const nuevoAlto = wrapper.clientHeight;
            if (nuevoAncho > 0 && nuevoAlto > 0) {
                const nuevoRatio = Math.max(window.devicePixelRatio || 1, 1);
                this.canvas.width = nuevoAncho * nuevoRatio;
                this.canvas.height = nuevoAlto * nuevoRatio;
                this.canvas.style.width = nuevoAncho + 'px';
                this.canvas.style.height = nuevoAlto + 'px';
                const nuevoCtx = this.canvas.getContext('2d');
                nuevoCtx.scale(nuevoRatio, nuevoRatio);
                nuevoCtx.fillStyle = '#ffffff';
                nuevoCtx.fillRect(0, 0, nuevoAncho, nuevoAlto);
                this.pad = new SignaturePad(this.canvas, {
                    minWidth: 2.5,
                    maxWidth: 4.5,
                    penColor: '#1e293b',
                    backgroundColor: '#ffffff',
                    velocityFilterWeight: 0.7
                });
                if (datos && datos.length > 0) {
                    this.pad.fromData(datos);
                }
                if (this.pad.isEmpty()) {
                    if (placeholder) placeholder.style.display = 'block';
                } else {
                    if (placeholder) placeholder.style.display = 'none';
                }
            }
        };

        const debouncedResize = () => {
            clearTimeout(resizeTimeout);
            resizeTimeout = setTimeout(resizeHandler, 250);
        };

        if (this._resizeListener) {
            window.removeEventListener('resize', this._resizeListener);
        }
        this._resizeListener = debouncedResize;
        window.addEventListener('resize', this._resizeListener);

        console.log('✅ Canvas de firma adicional creado');
    },

    limpiar: function() {
        if (this.pad) {
            this.pad.clear();
            document.getElementById('firmaPlaceholderAdicionalModal').style.display = 'block';
        }
        document.getElementById('firmaMensajeEstadoAdicional').style.display = 'none';
    },

    guardar: function() {
        if (!this.pad) {
            console.error('Error: No se pudo capturar la firma adicional.');
            return;
        }

        if (this.pad.isEmpty()) {
            console.warn('Por favor, firma en el área antes de guardar.');
            return;
        }

        const btn = document.getElementById('firmaModalGuardarAdicional');
        if (btn) {
            btn.disabled = true;
            btn.textContent = '⏳ Guardando...';
        }

        try {
            const firmaDataURL = this.pad.toDataURL('image/png');

            // Guardar en el input oculto
            const inputOculto = document.getElementById('firma_cliente_paso5_adicional');
            if (inputOculto) {
                inputOculto.value = firmaDataURL;
            }

            // Mostrar en el canvas pequeño
            this.mostrarFirmaEnCanvasPequeno(firmaDataURL);

            // Mostrar badge de completado
            const badge = document.getElementById('firmaCompletadaBadgeAdicional');
            if (badge) badge.style.display = 'block';

            // Cambiar texto del botón
            const btnAbrir = document.getElementById('btnAbrirFirmaModalAdicional');
            if (btnAbrir) {
                btnAbrir.innerHTML = '✅ Firma Registrada';
                btnAbrir.style.background = '#22c55e';
            }

            // Ocultar placeholder
            const placeholder = document.getElementById('firmaPlaceholderAdicional');
            if (placeholder) placeholder.style.display = 'none';

            console.log('✅ Firma adicional guardada correctamente.');
            this.cerrar();
        } catch (error) {
            console.error('Error al guardar la firma adicional:', error);
        } finally {
            if (btn) {
                btn.disabled = false;
                btn.textContent = '💾 Guardar Firma';
            }
        }
    },

    mostrarFirmaEnCanvasPequeno: function(firmaDataURL) {
        const canvasPequeno = document.getElementById('padPaso5Adicional');
        if (!canvasPequeno) return;

        try {
            const ctx = canvasPequeno.getContext('2d');
            const rect = canvasPequeno.getBoundingClientRect();
            const width = rect.width || canvasPequeno.width || 300;
            const height = rect.height || canvasPequeno.height || 160;

            if (canvasPequeno.width === 0 || canvasPequeno.height === 0) {
                canvasPequeno.width = width;
                canvasPequeno.height = height;
            }

            ctx.clearRect(0, 0, canvasPequeno.width, canvasPequeno.height);
            ctx.fillStyle = '#ffffff';
            ctx.fillRect(0, 0, canvasPequeno.width, canvasPequeno.height);

            const img = new Image();
            img.onload = function() {
                const ratio = Math.min(
                    canvasPequeno.width / img.width,
                    canvasPequeno.height / img.height
                );
                const margen = 0.90;
                const finalRatio = ratio * margen;
                const newWidth = img.width * finalRatio;
                const newHeight = img.height * finalRatio;
                const x = (canvasPequeno.width - newWidth) / 2;
                const y = (canvasPequeno.height - newHeight) / 2;
                ctx.drawImage(img, x, y, newWidth, newHeight);
                console.log('✅ Firma adicional mostrada en canvas pequeño');
            };
            img.onerror = function() {
                console.warn('Error al cargar imagen de firma adicional');
            };
            img.src = firmaDataURL;
        } catch (e) {
            console.error('Error al mostrar firma adicional en canvas pequeño:', e);
        }
    }
};
