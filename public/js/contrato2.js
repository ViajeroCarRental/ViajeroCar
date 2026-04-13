// Pasos de 4 al 6
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

        try {
            const r = await fetch('/admin/contrato/servicios-extra', {
                method: "POST", headers: { "Content-Type": "application/json", "X-CSRF-TOKEN": window.csrfToken },
                body: JSON.stringify({ id_reservacion: window.ID_RESERVACION, id_servicio: window.ID_SERVICIO_MENOR || 0, forzar: count > 0 ? "on" : "off", cantidad: count })
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
        } catch (err) { console.error("Error Menores:", err); }
    },

    configurarPreviews: function () {
        ContratoUI.DOM.formDoc?.querySelectorAll(".uploader").forEach((uploader) => {
            const previewSibling = uploader.parentElement?.querySelector(":scope > .preview");
            if (previewSibling && previewSibling.parentElement !== uploader) {
                uploader.appendChild(previewSibling);
            }
        });

        ContratoUI.DOM.formDoc?.addEventListener("change", (e) => {
            if (e.target.type === "file" && e.target.closest(".uploader")) {
                const file = e.target.files[0];
                const uploader = e.target.closest(".uploader");
                let prev = uploader.querySelector(".preview");
                if (!prev) {
                    prev = uploader.parentElement?.querySelector(":scope > .preview");
                    if (prev) uploader.appendChild(prev);
                }
                if (!file || !prev?.classList.contains("preview")) return;

                if (!file.type.startsWith("image/")) {
                    prev.innerHTML = `<p class="preview-file-label">Archivo seleccionado</p>`;
                    return;
                }
                const reader = new FileReader();
                reader.onload = ev => prev.innerHTML = `<img src="${ev.target.result}" alt="Vista previa del documento" class="preview-image">`;
                reader.onload = ev => prev.innerHTML = `<div style="position:relative;display:inline-block;margin-top:10px;"><img src="${ev.target.result}" style="width:120px;height:120px;object-fit:cover;border-radius:8px;"><button type="button" style="position:absolute;top:-8px;right:-8px;background:#ef4444;color:white;border:none;border-radius:50%;width:22px;height:22px;cursor:pointer;" onclick="this.parentElement.remove();">×</button></div>`;
                reader.onload = ev => prev.innerHTML = `<img src="${ev.target.result}" alt="Vista previa del documento" class="preview-image">`;
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
                ContratoNav.irAlPaso5();
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
    paymentFlow: "reserva",
    ultimoResumen: null,

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
        try {
            // Esta ruta es la buena, trae los pagos
            const resp = await fetch(`/admin/contrato/${window.ID_RESERVACION}/resumen-paso6`);
            const res = await resp.json();

            // Soportamos success o ok
            if (!res.success && !res.ok) return;
            const r = res.data;
            this.ultimoResumen = r;

            const fM = (v) => window.money ? window.money(v) : `$${parseFloat(v).toFixed(2)} MXN`;

            // 1. Renta y Seguros
            if (document.getElementById("baseDescr")) document.getElementById("baseDescr").textContent = r.base.descripcion;
            if (document.getElementById("baseAmt")) document.getElementById("baseAmt").textContent = fM(r.base.total);
            if (document.getElementById("insDescr")) document.getElementById("insDescr").textContent = r.totales.nombre_seguro || "Protecciones";
            if (document.getElementById("insAmt")) document.getElementById("insAmt").textContent = fM(r.totales.monto_seguros);

            // 2. Extras dinámicos
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

            // 3. ESTADO DE CUENTA Y TOTALES
            const totalTexto = fM(r.totales.total_contrato || r.totales.total);
            if (document.getElementById("subtotalAmt")) document.getElementById("subtotalAmt").textContent = fM(r.totales.subtotal);
            if (document.getElementById("ivaOnly")) document.getElementById("ivaOnly").textContent = fM(r.totales.iva);

            // Llenamos todos los lugares donde dice "Total Contrato"
            if (document.getElementById("totalContrato")) document.getElementById("totalContrato").textContent = totalTexto;
            if (document.getElementById("detTotalFinalCuenta")) document.getElementById("detTotalFinalCuenta").textContent = totalTexto;

            // Llenamos saldos
            if (document.getElementById("saldoPendiente")) document.getElementById("saldoPendiente").textContent = fM(r.totales.saldo_pendiente);
            if (document.getElementById("detSaldo")) document.getElementById("detSaldo").textContent = fM(r.totales.saldo_pendiente);
            if (document.getElementById("detPagos")) document.getElementById("detPagos").textContent = fM(r.pagos.realizados || 0);

            if (document.getElementById("detGarantiaSeguro")) {
                document.getElementById("detGarantiaSeguro").textContent = fM(r.totales.garantia?.monto || 0);
            }
            if (document.getElementById("detGarantiaSeguroMeta")) {
                const codigo = r.totales.garantia?.codigo_categoria || "—";
                const nombre = r.totales.garantia?.nombre_seguro || "Sin paquete";
                document.getElementById("detGarantiaSeguroMeta").textContent = `${codigo} · ${nombre}`;
            }

            if (document.getElementById("detGarantiaSeguroStatus")) {
                const pagada = parseFloat(r.pagos?.garantia?.realizados || 0);
                const pendiente = parseFloat(r.pagos?.garantia?.pendiente || 0);
                document.getElementById("detGarantiaSeguroStatus").textContent = `Pagada: ${fM(pagada)} | Pendiente: ${fM(pendiente)}`;
            }

            // 4. Dibujar la tabla con el array de la BD
            this.renderizarPagos(r.pagos.lista || []);
            this.actualizarBotonPago();

        } catch (e) {
            console.error("Error cargando Paso 6:", e);
        }
    },

    renderizarPagos: function (pagos) {
        const body = document.getElementById("payBody");
        if (!body) return;

        body.innerHTML = ""; // Quitamos el "Cargando..."

        if (!pagos || pagos.length === 0) {
            body.innerHTML = `<tr><td colspan="6" style="text-align:center;padding:30px;color:#94a3b8;">NO EXISTEN PAGOS REGISTRADOS</td></tr>`;
            return;
        }

        const fM = (v) => window.money ? window.money(v) : `$${parseFloat(v).toFixed(2)}`;

        body.innerHTML = pagos.map((p, index) => {
            // Mapeo seguro con los nombres de tu tabla (id_pago, origen_pago)
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
        ContratoUI.DOM.payBody?.addEventListener("click", (e) => {
            const btn = e.target.closest(".btn-del-pago");
            if (!btn) return;
            alertify.confirm("Eliminar Pago", "¿Seguro que deseas eliminar este pago?",
                async () => {
                    btn.disabled = true; btn.innerText = "...";
                    try {
                        const res = await (await fetch(`/admin/contrato/pagos/${btn.dataset.del}/eliminar`, { method: "DELETE", headers: { "Content-Type": "application/json", "Accept": "application/json", "X-CSRF-TOKEN": window.csrfToken } })).json();
                        if (res.success !== false) {
                            alertify.success("Pago eliminado.");
                            this.cargarResumen();
                            if (window.cargarResumenBasico) window.cargarResumenBasico();
                        } else { alertify.error(res.msg || "Error"); btn.disabled = false; btn.innerText = "✕"; }
                    } catch (e) { alertify.error("Error de conexión"); btn.disabled = false; btn.innerText = "✕"; }
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

        if (this.obtenerMontoPendienteReserva() > 0.009) btn.textContent = "+ Registrar Pago";
        else if (this.obtenerMontoPendienteGarantia() > 0.009) btn.textContent = "+ Registrar Garantía";
        else btn.textContent = "+ Registrar Pago";
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
            if (pTipo && pTipo.value === "GARANTIA") pTipo.value = "PAGO RESERVACIÃ“N";
            if (pHint) pHint.textContent = "Pago 1 de 2: registra primero el monto de la reservación.";
        }
    },

    abrirModalPago: function (flow = null) {
        const saldoReserva = this.obtenerMontoPendienteReserva();
        const saldoGarantia = this.obtenerMontoPendienteGarantia();
        const flujo = flow || (saldoReserva > 0.009 ? "reserva" : (saldoGarantia > 0.009 ? "garantia" : "reserva"));

        ContratoUI.DOM.modalPagos.classList.add("show");
        this.configurarFlujoPago(flujo);
        this.cambiarTab("paypal");
    },

    cerrarModalPago: function () {
        ContratoUI.DOM.modalPagos.classList.remove("show");
        ["pMonto", "pNotes", "fileTerminal", "fileTransfer"].forEach(id => { const e = document.getElementById(id); if (e) e.value = ""; });
        const pp = document.getElementById("paypal-button-container-modal"); if (pp) pp.innerHTML = "";
        const pHint = document.getElementById("pFlowHint");
        if (pHint) pHint.textContent = "Pago 1 de 2: registra primero el monto de la reservación.";
        this.paymentFlow = "reserva";
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
                    const flujoAnterior = this.paymentFlow;
                    const tipoPago = document.getElementById("pTipo")?.value || "PAGO RESERVACIÃ“N";
                    const tipoPagoFinal = flujoAnterior === "garantia" ? "GARANTIA" : tipoPago;
                    const order = await a.order.capture();
                    await fetch(`/admin/contrato/pagos/paypal`, {
                        method: "POST", headers: { "Content-Type": "application/json", "X-CSRF-TOKEN": window.csrfToken },
                        body: JSON.stringify({ id_reservacion: window.ID_RESERVACION, order_id: order.id, monto: monto, origen: "en linea", metodo: "PAYPAL", tipo_pago: tipoPagoFinal })
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
        const tipoPagoManual = this.paymentFlow === "garantia"
            ? "GARANTIA"
            : (document.getElementById("pTipo")?.value || "PAGO RESERVACIÃ“N");
        fd.delete("tipo_pago");
        fd.append("tipo_pago", tipoPagoManual);
        fd.append("monto", document.getElementById("pMonto")?.value || 0);
        fd.append("notas", document.getElementById("pNotes")?.value || "");
        fd.append("metodo", m); fd.append("origen", o); fd.append("_token", window.csrfToken);
        if (file?.files[0]) fd.append("comprobante", file.files[0]);
        const btn = document.getElementById("pSave");
        btn.disabled = true; btn.innerText = "Guardando...";
        try {
            const flujoAnterior = this.paymentFlow;
            const data = await (await fetch(`/admin/contrato/pagos/agregar`, { method: "POST", body: fd })).json();
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
            } else if (errBox) errBox.innerText = data.msg || "Error";
        } catch (e) { if (errBox) errBox.innerText = "Error conexión."; }
        finally { btn.disabled = false; btn.innerText = "GUARDAR PAGO"; }
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
            window.showStep(4);
            window.actualizarStepper(4);
        });

        document.getElementById("back5")?.addEventListener("click", () => {
            window.showStep(5);
            window.actualizarStepper(5);
            // Nota: Ya no llamamos a inyectarYCrearPad() aquí porque el interceptor global lo hará.
        });

        // Mover el botón limpiar fuera del contenedor del canvas
        this.reubicarBotonLimpiar();
    },

    // Mueve el botón "Limpiar Firma" justo debajo del área de dibujo
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

    // Crea el canvas de firma con el tamaño correcto y trazo grueso
    inyectarYCrearPad: function () {
        const contenedor = document.querySelector(".signature-pad-wrapper");
        if (!contenedor || !window.SignaturePad) return;

        // Guardar firma previa si existe
        if (this.padPaso5 && !this.padPaso5.isEmpty()) {
            this.firmaPrevia = this.padPaso5.toData();
        }

        // Eliminar canvas viejo
        const canvasViejo = document.getElementById("padPaso5");
        if (canvasViejo) canvasViejo.remove();

        let anchoReal = contenedor.clientWidth;
        let altoReal = contenedor.clientHeight;

        // Si el contenedor aún no tiene tamaño, reintentar
        if (anchoReal === 0 || altoReal === 0) {
            setTimeout(() => this.inyectarYCrearPad(), 100);
            return;
        }

        const ratio = Math.max(window.devicePixelRatio || 1, 1);

        // Crear nuevo canvas
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
        ctx.fillRect(0, 0, anchoReal, altoReal); // fondo blanco

        contenedor.appendChild(nuevoCanvas);

        // Inicializar SignaturePad con trazo más grueso
        this.padPaso5 = new SignaturePad(nuevoCanvas, {
            minWidth: 2.5,
            maxWidth: 4.5,
            penColor: "#1e293b",
            velocityFilterWeight: 0.7
        });

        // Restaurar firma previa
        if (this.firmaPrevia) {
            this.padPaso5.fromData(this.firmaPrevia);
        }

        // Asegurar que el botón limpiar esté fuera
        this.reubicarBotonLimpiar();

        // Vincular botón limpiar con el nuevo pad
        const btnClear = document.getElementById("clearPaso5");
        if (btnClear) {
            const newBtn = btnClear.cloneNode(true);
            btnClear.parentNode.replaceChild(newBtn, btnClear);
            newBtn.addEventListener("click", () => {
                this.padPaso5?.clear();
                this.firmaPrevia = null;
                // Redibujar fondo blanco después de limpiar
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

        window.showStep(5);
        window.actualizarStepper(5);
    },

    irAlPaso6: async function (e) {
        const btn = e.target || document.getElementById("go6");

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
                    firma: firmaBase64
                })
            });

            const data = await res.json();

            if (data.ok) {
                window.showStep(6);
                if (typeof window.cargarResumenBasico === 'function') window.cargarResumenBasico();
                window.actualizarStepper(6);
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
        // Conductores
        const container = document.getElementById('res-lista-conductores');
        if (container) {
            let htmlCond = '';
            const inputs = document.querySelectorAll('input[name$="[nombre]"]');
            inputs.forEach((inp, i) => {
                const pre = inp.name.replace('[nombre]', '');
                const nom = inp.value.trim() || 'CONDUCTOR';
                const ape = document.querySelector(`input[name="${pre}[apellido_paterno]"]`)?.value.trim() || '';
                htmlCond += `<p style="font-size:13px; margin-bottom:2px;">${i === 0 ? '<b>' : ''}${nom} ${ape}${i === 0 ? ' (Titular)</b>' : ''}</p>`;
            });
            container.innerHTML = htmlCond;
        }

        // Seguros
        const fuenteSeguros = document.getElementById('r_seguros_lista');
        const resCoberturas = document.getElementById('res-lista-coberturas');
        if (fuenteSeguros && resCoberturas) {
            const esVacio = fuenteSeguros.querySelector('.empty') || fuenteSeguros.innerText.trim() === '—';
            resCoberturas.innerHTML = esVacio
                ? '<li class="txt-muted">Protección Básica (TPL)</li>'
                : fuenteSeguros.innerHTML;
        }

        // Extras
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
 * ==========================================
 * INICIALIZACIÓN GLOBAL
 * ==========================================
 */
document.addEventListener("DOMContentLoaded", () => {
    document.body.classList.add('sidebar-collapse');
    document.body.classList.remove('sidebar-open');

    const resumenDetalle = document.getElementById("resumenDetalleContainer");
    if (resumenDetalle) resumenDetalle.style.display = "none";

    if (typeof window.showStep === 'function' && !window.showStep.isPatched) {
        const originalShowStep = window.showStep;
        window.showStep = function (step, force) {
            originalShowStep(step, force);

            if (step === 5 || step === "5") {
                ContratoNav.sincronizarDatosTablet();
                setTimeout(() => ContratoNav.inyectarYCrearPad(), 200);
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
        console.log("🎯 SALTO PRIORITARIO AL PASO: " + pasoInicial);
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