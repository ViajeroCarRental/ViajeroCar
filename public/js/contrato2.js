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
        // Permite repetir la misma notificación si han pasado más de 3 segundos
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
        const self = this;                               // <-- referencia segura

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
                                self.autocompletarDesdeBusqueda(persona, 0);   // usa self
                                resultsDiv.style.display = 'none';
                                buscador.value = '';
                            });
                            resultsDiv.appendChild(div);
                        });
                        resultsDiv.style.display = 'block';
                    });
            }, 300);
        });

        // Cerrar al hacer clic fuera
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

        // Validación rápida de contacto
        if (nameAttr.includes('contacto_emergencia')) {
            const val = inputContacto ? inputContacto.value.replace(/\D/g, '') : '';
            if (!val) { setEstado(inputContacto, ''); return; }
            if (val.length !== 10) {
                setEstado(inputContacto, 'error');
                ContratoUI.mostrarNotificacion('error', "<b>⚠️ Teléfono Inválido</b><br>Debe tener exactamente 10 dígitos.");
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

            // Validación de edad local
            if (inputNac && inputNac.value && inputTrigger.name.includes('nacimiento')) {
                const b = new Date(inputNac.value + "T00:00:00"), h = new Date();
                let e = h.getFullYear() - b.getFullYear();
                if (h.getMonth() < b.getMonth() || (h.getMonth() === b.getMonth() && h.getDate() < b.getDate())) e--;

                if (e < 18) {
                    setEstado(inputNac, 'error');
                    borrarYTemblar(inputNac);
                    ContratoUI.mostrarNotificacion('error', "<b>🚫 Edad no permitida</b><br>El conductor debe tener al menos 18 años.");
                    this.gestionarCobroMenoresExtra();
                    return;
                } else if (e >= 18 && e <= 24) {
                    setEstado(inputNac, 'warning');
                } else {
                    setEstado(inputNac, 'ok');
                }
            }

            // Procesar respuestas del servidor
            if (res.status === 'vencido') {
                setEstado(inputVen, 'error');
                borrarYTemblar(inputVen);
                ContratoUI.mostrarNotificacion('error', `<b>🚫 Expirado</b><br>${msgSrv}`);
            } else if (res.status === 'error_fecha') {
                setEstado(inputEmi, 'error');
                setEstado(inputVen, 'error');
                borrarYTemblar(inputEmi);
                borrarYTemblar(inputVen);
                ContratoUI.mostrarNotificacion('error', `<b>🚫 Error Fechas</b><br>${msgSrv}`);
            } else if (res.status === 'warning') {
                setEstado(inputVen, 'warning');
                ContratoUI.mostrarNotificacion('warning', `<b>⚠️ Revisión</b><br>${msgSrv}`);
            }

            if (res.status === 'invalido' && payload.numero) {
                setEstado(inputNum, 'error');
                borrarYTemblar(inputNum, false);
                ContratoUI.mostrarNotificacion('error', `<b>🚫 Formato Incorrecto</b><br>${msgSrv}`);
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
                    ContratoUI.mostrarNotificacion('warning', `🔞 <b>Cargo Aplicado</b><br>Aplica tarifa extra por ${count} conductor(es) joven(es).`);
                } else {
                    ContratoUI.mostrarNotificacion('success', "✅ <b>Cargo Removido</b><br>Ningún conductor requiere tarifa de menor.");
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
                                // Quitamos los estilos para que vuelva a mostrarse el uploader original
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

        let detallesFaltantes = [];
        const faltantes = Array.from(form.querySelectorAll('input[required], select[required]')).filter(c => {
            const esArchivo = c.type === 'file';
            const estaVacio = esArchivo ? c.files.length === 0 : !c.value.trim();
            const elementoVisual = esArchivo ? c.closest('.uploader') : c;

            if (estaVacio) {
                if (elementoVisual) {
                    elementoVisual.style.border = "2px solid #ef4444";
                    if (!esArchivo) elementoVisual.style.backgroundColor = "#fef2f2";
                    elementoVisual.classList.add('input-error');
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
                if (elementoVisual) {
                    elementoVisual.style.border = "";
                    if (!esArchivo) elementoVisual.style.backgroundColor = "";
                    elementoVisual.classList.remove('input-error');
                }
                return false;
            }
        }).length;

        if (faltantes > 0) {
            const unicos = [...new Set(detallesFaltantes)].join("<br>");
            ContratoUI.mostrarNotificacion('warning', `<b>⚠️ Faltan Datos Obligatorios</b><br>${unicos}`);
            const primerError = form.querySelector('.input-error');
            if (primerError) {
                primerError.scrollIntoView({ behavior: 'smooth', block: 'center' });
                if (primerError.tagName !== 'DIV') primerError.focus();
            }
            return;
        }

        // Verificar campos con error visual (clase input-error)
        const errores = form.querySelectorAll('.input-error');
        if (errores.length > 0) {
            errores[0].scrollIntoView({ behavior: 'smooth', block: 'center' });
            errores[0].focus();
            ContratoUI.mostrarNotificacion('error', `<b>🚫 Datos Inválidos</b><br>Hay campos en rojo. Corrígelos.`);
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
                ContratoUI.mostrarNotificacion('error', res.error || "Error al guardar");
            }
        } catch (err) {
            ContratoUI.mostrarNotificacion('error', "Error de conexión");
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

            // Renta y Seguros
            if (document.getElementById("baseDescr")) document.getElementById("baseDescr").textContent = r.base.descripcion;
            if (document.getElementById("baseAmt")) document.getElementById("baseAmt").textContent = fM(r.base.total);
            if (document.getElementById("insDescr")) document.getElementById("insDescr").textContent = r.totales.nombre_seguro || "Protecciones";
            if (document.getElementById("insAmt")) document.getElementById("insAmt").textContent = fM(r.totales.monto_seguros);

            // Extras dinámicos
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

            // Totales
            const totalTexto = fM(r.totales.total_contrato || r.totales.total);
            if (document.getElementById("subtotalAmt")) document.getElementById("subtotalAmt").textContent = fM(r.totales.subtotal);
            if (document.getElementById("ivaOnly")) document.getElementById("ivaOnly").textContent = fM(r.totales.iva);
            if (document.getElementById("totalContrato")) document.getElementById("totalContrato").textContent = totalTexto;
            if (document.getElementById("detTotalFinalCuenta")) document.getElementById("detTotalFinalCuenta").textContent = totalTexto;
            if (document.getElementById("saldoPendiente")) document.getElementById("saldoPendiente").textContent = fM(r.totales.saldo_pendiente);
            if (document.getElementById("detSaldo")) document.getElementById("detSaldo").textContent = fM(r.totales.saldo_pendiente);
            if (document.getElementById("detPagos")) document.getElementById("detPagos").textContent = fM(r.pagos.realizados || 0);

            // Garantía: Si el backend no envía estos datos, se ocultan o muestran en blanco.
            // Para activar la funcionalidad completa, el backend debe incluir `totales.garantia` y `pagos.garantia`.
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
            btn.textContent = "+ Elegir Pago";
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

        // Ambos saldos pendientes: paga garantía primero
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
                        ContratoUI.mostrarNotificacion('success', flujoAnterior === "garantia" ? "Garantía registrada." : "Pago PayPal exitoso.");
                    }
                },
                onError: () => ContratoUI.mostrarNotificacion('error', "Error PayPal")
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
                    ContratoUI.mostrarNotificacion('success', flujoAnterior === "garantia" ? "Garantía registrada." : "Pago guardado.");
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

        this.reubicarBotonLimpiar();
    },

    reubicarBotonLimpiar: function () {
        const contenedor = document.querySelector(".signature-pad-wrapper");
        const btn = document.getElementById("clearPaso5");
        if (contenedor && btn && contenedor.contains(btn)) {
            contenedor.parentNode.insertBefore(btn, contenedor.nextSibling);
            btn.style.position = 'static';
            btn.style.marginTop = '10px';
            btn.style.display = 'inline-block';
            btn.style.backgroundColor = '#f8fafc';
            btn.style.border = '1px solid #cbd5e1';
            btn.style.borderRadius = '8px';
            btn.style.padding = '6px 16px';
            btn.style.fontSize = '14px';
            btn.style.cursor = 'pointer';
        }
    },

    inyectarYCrearPad: function () {
        if (typeof SignaturePad === 'undefined') {
            console.warn("SignaturePad library not loaded.");
            return;
        }
        const contenedor = document.querySelector(".signature-pad-wrapper");
        if (!contenedor) return;

        if (this.padPaso5 && !this.padPaso5.isEmpty()) {
            this.firmaPrevia = this.padPaso5.toData();
        }

        const canvasViejo = document.getElementById("padPaso5");
        if (canvasViejo) canvasViejo.remove();

        let anchoReal = contenedor.clientWidth;
        let altoReal = contenedor.clientHeight;
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
            cursor: crosshair;
            border-radius: 8px;
            background: white;
        `;
        nuevoCanvas.width = anchoReal * ratio;
        nuevoCanvas.height = altoReal * ratio;

        const ctx = nuevoCanvas.getContext("2d");
        ctx.scale(ratio, ratio);
        ctx.fillStyle = 'white';
        ctx.fillRect(0, 0, anchoReal, altoReal);

        contenedor.appendChild(nuevoCanvas);

        this.padPaso5 = new SignaturePad(nuevoCanvas, {
            minWidth: 2.5,
            maxWidth: 4.5,
            penColor: "#1e293b",
            velocityFilterWeight: 0.7
        });

        if (this.firmaPrevia) {
            this.padPaso5.fromData(this.firmaPrevia);
        }

        this.reubicarBotonLimpiar();

        const btnClear = document.getElementById("clearPaso5");
        if (btnClear) {
            const newBtn = btnClear.cloneNode(true);
            btnClear.parentNode.replaceChild(newBtn, btnClear);
            newBtn.addEventListener("click", () => {
                this.padPaso5?.clear();
                this.firmaPrevia = null;
                const c = document.getElementById("padPaso5");
                if (c) {
                    const ct = c.getContext("2d");
                    ct.fillStyle = 'white';
                    ct.fillRect(0, 0, c.width, c.height);
                }
            });
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

    irAlPaso6: async function (e) {
        const btn = e.target || document.getElementById("go6");
        const inputEstancia = document.getElementById('lugar_estancia');
        const errorEstancia = document.getElementById('error-estancia');

        if (inputEstancia && inputEstancia.value.trim() === '') {
            inputEstancia.style.border = "2px solid #ef4444";
            if (errorEstancia) errorEstancia.style.display = "block";
            ContratoUI.mostrarNotificacion('warning', '<b>⚠️ Dato Requerido</b><br>Por favor indica el lugar de estancia para continuar.');
            inputEstancia.scrollIntoView({ behavior: 'smooth', block: 'center' });
            inputEstancia.focus();
            return;
        } else if (inputEstancia) {
            inputEstancia.style.border = "1px solid #cbd5e1";
            if (errorEstancia) errorEstancia.style.display = "none";
        }

        if (!this.padPaso5 || this.padPaso5.isEmpty()) {
            ContratoUI.mostrarNotificacion('warning', '<b>⚠️ Firma Requerida</b><br>El cliente debe firmar el contrato para continuar.');
            return;
        }

        const idContrato = document.getElementById("contratoApp")?.dataset.idContrato || window.ID_CONTRATO;
        if (!idContrato) {
            ContratoUI.mostrarNotificacion('error', 'Error: No se encontró el ID del contrato para guardar la firma.');
            return;
        }

        const textoOriginal = btn.innerText;
        btn.disabled = true;
        btn.innerText = "Guardando Firma...";

        try {
            const firmaBase64 = this.padPaso5.toDataURL("image/png");
            const token = window.csrfToken || document.querySelector('meta[name="csrf-token"]')?.content;

            const res = await fetch("/contrato/firma-cliente", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    "X-CSRF-TOKEN": token
                },
                body: JSON.stringify({
                    id_contrato: idContrato,
                    firma: firmaBase64,
                    lugar_estancia: inputEstancia ? inputEstancia.value.trim() : null
                })
            });

            const data = await res.json();
            if (data.ok) {
                if (typeof window.showStep === 'function') window.showStep(6);
                if (typeof window.cargarResumenBasico === 'function') window.cargarResumenBasico();
                if (typeof window.actualizarStepper === 'function') window.actualizarStepper(6);
            } else {
                throw new Error(data.msg || "Error al guardar en el servidor");
            }
        } catch (error) {
            console.error("Error firma:", error);
            ContratoUI.mostrarNotificacion('error', 'Error al guardar la firma. Intenta de nuevo.');
        } finally {
            btn.disabled = false;
            btn.innerText = textoOriginal;
        }
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

/**
 * INICIALIZACIÓN GLOBAL
 */
document.addEventListener("DOMContentLoaded", () => {
    document.body.classList.add('sidebar-collapse');
    document.body.classList.remove('sidebar-open');

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
});