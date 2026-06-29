(function() {
    "use strict";

// =========================================
// 02 FUNCIONES DE VALIDACIÓN VISUAL
// =========================================

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
        if (!contenedor) contenedor = elemento.parentElement;
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
                if (selectHora) selectHora.classList.add('field-success');
            }
        } else if (elemento.classList && elemento.classList.contains('tp-hour')) {
            elemento.classList.add('field-success');
            const container = elemento.closest('.dt-field-admin');
            if (container) {
                const inputUI = container.querySelector('.input-buscador-admin');
                if (inputUI) inputUI.classList.add('field-success');
            }
        } else if (elemento.id === 'nombre_cliente' || elemento.id === 'apellidos_cliente' ||
                   elemento.id === 'email_cliente' || elemento.id === 'telefono_ui' ||
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
                if (selectHora) selectHora.classList.remove('field-error', 'field-success');
            }
        } else if (elemento.classList && elemento.classList.contains('tp-hour')) {
            elemento.classList.remove('field-error', 'field-success');
            const container = elemento.closest('.dt-field-admin');
            if (container) {
                const inputUI = container.querySelector('.input-buscador-admin');
                if (inputUI) inputUI.classList.remove('field-error', 'field-success');
            }
        } else {
            elemento.classList.remove('field-error', 'field-success');
        }

        let contenedor = elemento.closest('.dt-field-admin, .time-field-admin, .field-admin, .sg-col-location-admin, .picker-row');
        if (!contenedor && elemento.parentElement) contenedor = elemento.parentElement;
        if (contenedor) {
            const msg = contenedor.querySelector('.error-msg');
            if (msg) msg.remove();
        }
    }

// =========================================
// 03 HELPERS Y UTILIDADES
// =========================================

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
    const closeAllPops = () => {
        document.querySelectorAll(".pop.modal").forEach((m) => {
            m.style.display = "none";
        });
    };

    const toISODate = (d) => {
        if (!(d instanceof Date) || isNaN(d)) return "";
        const y = d.getFullYear();
        const m = String(d.getMonth() + 1).padStart(2, "0");
        const da = String(d.getDate()).padStart(2, "0");
        return `${y}-${m}-${da}`;
    };

    const getCsrf = () => {
        const meta = document.querySelector('meta[name="csrf-token"]');
        if (meta) return meta.getAttribute("content") || "";
        const tok = qs('#formCotizacion input[name="_token"]');
        return tok ? tok.value : "";
    };

    const escapeHtml = (str) => {
        return String(str || "")
            .replaceAll("&", "&amp;")
            .replaceAll("<", "&lt;")
            .replaceAll(">", "&gt;")
            .replaceAll('"', "&quot;")
            .replaceAll("'", "&#039;");
    };

// =========================================
// 04 ESTADO GLOBAL
// =========================================

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
            precioLitro: 24,
            activo: false
        },
        base_editable: null,
    };
    window.state = state;
    window._cotizacionAPI = {
        getState: () => state,
        setCategoria: setCategoria,
        setProteccion: setProteccion,
        syncTotalsHidden: syncTotalsHidden,
        refreshSummary: refreshSummary,
        forceRecalc: forceRecalc,
        setGasolinaActive: setGasolinaActive,
        setDropoffActive: setDropoffActive,
        setDeliveryActive: setDeliveryActive,
        setAddonQty: setAddonQty,
        loadAddons: loadAddons
    };

// =========================================
// 05 HIDDEN INPUTS (BACKEND)
// =========================================

    function ensureHidden(name, id) {
        let input = qs(`#${id}`);
        if (!input) {
            input = document.createElement("input");
            input.type = "hidden";
            input.id = id;
            input.name = name;
            qs("#formCotizacion")?.appendChild(input);
        } else {
            input.name = name;
        }
        return input;
    }

    function ensureTotalsHidden() {
        ensureHidden("tarifa_base", "tarifa_base");
        ensureHidden("tarifa_modificada", "tarifa_modificada");
        ensureHidden("tarifa_ajustada", "tarifa_ajustada");
    }

    function syncProteccionHidden() {
        const hid = qs("#proteccion_id");
        if (hid) hid.value = state.proteccion ? String(state.proteccion.id ?? "") : "";
    }

    function syncIndividualesHidden() {
        const wrap = qs("#insHidden");
        if (!wrap) return;
        wrap.innerHTML = "";
        let i = 0;
        state.individuales.forEach((it) => {
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

    function syncAddonsHidden() {
        const wrap = qs("#addonsHidden");
        if (!wrap) return;
        wrap.innerHTML = "";
        let i = 0;
        state.addons.forEach((it) => {
            const qty = Number(it.qty || 0);
            if (qty <= 0) return;
            const fields = [
                ["id", it.id],
                ["cantidad", qty],
                ["precio", Number(it.precio || 0)],
                ["nombre", it.nombre || ""],
                ["charge", it.charge || "por_evento"],
            ];
            fields.forEach(([k, v]) => {
                const input = document.createElement("input");
                input.type = "hidden";
                input.name = `extrasSeleccionados[${i}][${k}]`;
                input.value = String(v ?? "");
                wrap.appendChild(input);
            });
            i++;
        });
    }

    function syncServiciosHidden() {
        ensureHidden("svc_dropoff", "svc_dropoff");
        ensureHidden("svc_delivery", "svc_delivery");
        ensureHidden("svc_gasolina", "svc_gasolina");
        const d = qs("#svc_dropoff"), l = qs("#svc_delivery"), g = qs("#svc_gasolina");
        if (d) d.value = state.servicios.dropoff ? "1" : "0";
        if (l) l.value = state.servicios.delivery ? "1" : "0";
        if (g) g.value = state.servicios.gasolina ? "1" : "0";
    }

    function syncDropoffHidden() {
        ensureHidden("dropoff_activo", "dropoff_activo");
        ensureHidden("dropoff_total", "dropoff_total");
        ensureHidden("dropoff_km", "dropoff_km");
        ensureHidden("dropoff_direccion", "dropoff_direccion");
        ensureHidden("dropoff_ubicacion", "dropoff_ubicacion");

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

// =========================================
// 06 DÍAS
// =========================================

function computeDays() {
    const fiUI = document.getElementById("fecha_inicio_ui")?.value || "";
    const ffUI = document.getElementById("fecha_fin_ui")?.value || "";

    console.log("📅 Fechas desde UI:", { fecha_inicio_ui: fiUI, fecha_fin_ui: ffUI });

    if (!fiUI || !ffUI) {
        console.log("⚠️ Fechas incompletas en UI");
        return 0;
    }

    const parseDateFromUI = (val) => {
        if (!val) return null;

        if (/^\d{4}-\d{2}-\d{2}$/.test(val)) {
            return new Date(val + "T00:00:00");
        }

        if (/^\d{2}-\d{2}-\d{4}$/.test(val)) {
            const [d, m, y] = val.split("-").map(Number);
            return new Date(y, m - 1, d, 0, 0, 0);
        }

        if (/^\d{2}\/\d{2}\/\d{4}$/.test(val)) {
            const [d, m, y] = val.split("/").map(Number);
            return new Date(y, m - 1, d, 0, 0, 0);
        }

        const d = new Date(val);
        if (!isNaN(d)) return d;

        console.error("❌ No se pudo parsear la fecha desde UI:", val);
        return null;
    };

    const d1 = parseDateFromUI(fiUI);
    const d2 = parseDateFromUI(ffUI);

    console.log("📅 Fechas parseadas desde UI:", { d1, d2 });

    if (!d1 || !d2) {
        console.log("⚠️ Fechas inválidas desde UI");
        return 0;
    }

    const diffTime = d2.getTime() - d1.getTime();
    const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));

    console.log("📊 Diferencia calculada:", diffDays);

    return Math.max(1, diffDays);
}

function syncDays() {
    console.log("🔍 === INICIO syncDays ===");

    const fiInput = qs("#fecha_inicio");
    const ffInput = qs("#fecha_fin");
    const fiUI = qs("#fecha_inicio_ui");
    const ffUI = qs("#fecha_fin_ui");

    console.log("📅 Valores de inputs ocultos:", {
        fecha_inicio: fiInput?.value,
        fecha_fin: ffInput?.value
    });

    console.log("📅 Valores de inputs UI:", {
        fecha_inicio_ui: fiUI?.value,
        fecha_fin_ui: ffUI?.value
    });

    state.days = computeDays();
    console.log("📊 Días calculados:", state.days);

    const diasTxt = document.getElementById("diasTxt");
    if (diasTxt) {
        diasTxt.textContent = String(state.days || 0);
        console.log("📝 Actualizado #diasTxt:", diasTxt.textContent);
    }

    const diasNavbar = document.getElementById("diasTxtNav");
    if (diasNavbar) {
        diasNavbar.textContent = String(state.days || 0);
        console.log("📝 Actualizado #diasTxtNav:", diasNavbar.textContent);
    }

    const diasNavbarCount = document.getElementById("diasNavbarCount");
    if (diasNavbarCount) {
        diasNavbarCount.textContent = String(state.days || 0);
        console.log("📝 Actualizado #diasNavbarCount:", diasNavbarCount.textContent);
    }

    document.querySelectorAll('.dias-count, .days-counter, [data-days-display]').forEach(el => {
        el.textContent = String(state.days || 0);
    });

    document.dispatchEvent(new CustomEvent('diasActualizados', {
        detail: { dias: state.days || 0 }
    }));

    refreshCategoriaPreview();
    repaintCategoriaModalEstimados();

    if (state.servicios.delivery) {
        const els = getDeliveryEls();
        if (els) computeDelivery(els);
    }
    if (state.servicios.dropoff) {
        const els = getDropoffEls();
        if (els) computeDropoff(els);
    }
    if (state.servicios.gasolina) computeGasolina();

    refreshSummary();
    syncTotalsHidden();

    actualizarTotalBoton();
    actualizarTotalNavbar();

    console.log("🔍 === FIN syncDays ===");
}

// =========================================
// 07 CATEGORÍA
// =========================================

    function setCategoria(cat) {
    state.categoria = cat;

    const hid = qs("#categoria_id");
    if (hid) hid.value = cat ? String(cat.id) : "";
    const txt = qs("#catSelTxt");
    const sub = qs("#catSelSub");

    if (!cat) {
        if (txt) txt.textContent = "— Ninguna categoría —";
        if (sub) sub.textContent = "Tarifa base por día y cálculo previo aparecerán aquí.";
        const inputPrecioKm = qs("#deliveryPrecioKm");
        if (inputPrecioKm) inputPrecioKm.value = "0";
        const container = document.getElementById("categoriaContainer");
        if (container) container.innerHTML = '';

        syncTotalsHidden();
        refreshSummary();
        return;
    }

    if (txt) txt.textContent = cat.nombre;
    if (sub) sub.textContent = `${money(cat.precio_dia)} / día · ${state.days || 0} día(s)`;

    refreshCategoriaPreview();

    const inputPrecioKm = qs("#deliveryPrecioKm");
    if (inputPrecioKm) inputPrecioKm.value = cat.precio_km || 0;

    if (state.servicios.delivery) {
        const els = getDeliveryEls();
        if (els) computeDelivery(els);
    }
    if (state.servicios.dropoff) {
        const els = getDropoffEls();
        if (els) computeDropoff(els);
    }
    if (state.servicios.gasolina) computeGasolina();

    syncTotalsHidden();
    refreshSummary();
    state.base_editable = null;

    console.log("🚗 Categoría seleccionada:", cat);
    console.log("💰 precio_km:", cat?.precio_km);
}

    function refreshCategoriaPreview() {
    const cat = state.categoria;
    const container = document.getElementById("categoriaContainer");

    if (!container) return;
    if (!cat) {
        container.innerHTML = '';
        return;
    }
    const descripcionesPorId = {
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

    const descripcionAuto = cat.desc || descripcionesPorId[cat.id] || "";
    container.innerHTML = `
        <div class="preview-wrapper" style="display:block;">
            <div class="mini-preview">
                <div class="mini-container">
                    <div class="mini-imagen">
                        <img id="catMiniImg" src="${cat.img || '/img/Logotipo.png'}" alt="Auto">
                    </div>
                    <div class="mini-info">
                        <div class="mini-header">
                            <div class="mini-title" id="catMiniName">${escapeHtml(cat.nombre || '—')}</div>
                            <button type="button" id="btnEditarCategoriaPreview" class="btn-edit-mini">
                                <i class='bx bx-edit-alt'></i>
                            </button>
                        </div>
                        <div class="mini-sub" id="catMiniDesc">${escapeHtml(descripcionAuto)}</div>
                        <div class="mini-precios">
                            <div class="precio-item">
                                <div class="precio-label">TARIFA BASE</div>
                                <div class="precio-valor" id="catMiniRate">${money(cat.precio_dia)} / día</div>
                            </div>
                            <div class="precio-item">
                                <div class="precio-label">CÁLCULO PREVIO</div>
                                <div class="precio-valor precio-rojo" id="catMiniCalc">${money(cat.precio_dia * state.days)}</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <button class="btn gray" type="button" id="catRemove">✖</button>
        </div>
    `;

const removeBtn = document.getElementById("catRemove");
if (removeBtn) {
    const newRemoveBtn = removeBtn.cloneNode(true);
    removeBtn.parentNode.replaceChild(newRemoveBtn, removeBtn);
    newRemoveBtn.onclick = function(e) {
        e.preventDefault();
        e.stopPropagation();
        console.log("🗑️ Eliminando categoría (onclick)");
        setCategoria(null);
    };
}
    const editBtn = document.getElementById("btnEditarCategoriaPreview");
    if (editBtn) {
        const newEditBtn = editBtn.cloneNode(true);
        editBtn.parentNode.replaceChild(newEditBtn, editBtn);
        newEditBtn.addEventListener("click", (e) => {
            e.stopPropagation();
            if (!state.categoria) return;
            const rateContainer = document.getElementById("catMiniRate");
            if (!rateContainer || rateContainer.querySelector('input')) return;
            const precioActual = parseFloat(state.categoria.precio_dia || 0);
            const input = document.createElement('input');
            input.type = 'number';
            input.value = precioActual.toFixed(2);
            input.min = 0;
            input.step = 0.01;
            Object.assign(input.style, {
                width: '100px', padding: '4px 8px', border: '1px solid #2563eb',
                borderRadius: '8px', fontWeight: '600', fontSize: '14px',
                color: '#333', outline: 'none'
            });
            rateContainer.textContent = '';
            rateContainer.appendChild(input);
            input.focus();
            input.select();
            const guardar = () => {
                let nuevoValor = parseFloat(input.value);
                if (isNaN(nuevoValor) || nuevoValor < 0) nuevoValor = precioActual;
                state.categoria.precio_dia = nuevoValor;
                rateContainer.textContent = `${money(nuevoValor)} / día`;
                const calcContainer = document.getElementById("catMiniCalc");
                if (calcContainer) calcContainer.textContent = money(nuevoValor * state.days);
                const sub = document.getElementById('catSelSub');
                if (sub) sub.textContent = `${money(nuevoValor)} / día · ${state.days || 0} día(s)`;
                if (window._cotizacionAPI) {
                    window._cotizacionAPI.syncTotalsHidden();
                    window._cotizacionAPI.refreshSummary();
                }
            };
            input.addEventListener('blur', guardar);
            input.addEventListener('keydown', (ev) => {
                if (ev.key === 'Enter') { ev.preventDefault(); input.blur(); }
            });
        });
    }
}

    function repaintCategoriaModalEstimados() {
        const dias = Number(state.days || 0);
        const cards = Array.from(document.querySelectorAll("#catPop .card-pick[data-precio]"));
        cards.forEach((card) => {
            const precio = Number(card.dataset.precio || 0);
            const est = precio * Math.max(dias, 0);
            const el = card.querySelector(".cat-estimado");
            if (el) el.textContent = money(est).replace(" MXN", "");
        });
    }

// =========================================
// 08 SERVICIOS (DELIVERY, DROP OFF, GASOLINA)
// =========================================

    function getDeliveryEls() {
        const wrap = qs(".delivery-wrapper");
        if (!wrap) return null;
        return {
            wrap, toggle: qs("#deliveryToggle"), fields: qs("#deliveryFields"),
            ubicacion: qs("#deliveryUbicacion"), groupDir: qs("#groupDireccion"),
            groupKm: qs("#groupKm"), dir: qs("#deliveryDireccion"),
            km: qs("#deliveryKm"), totalTxt: qs("#deliveryTotal"),
            totalHid: qs("#deliveryTotalHidden"), precioKmHid: qs("#deliveryPrecioKm"),
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

        syncTotalsHidden();
        refreshSummary();

        console.log(`🚚 Delivery: ${km} km x $${precioKm}/km = $${total}`);

        forceUpdateUI();

        return total;
    }

    function resetDelivery(els) {
        state.delivery.total = 0; state.delivery.km = 0; state.delivery.ubicacion = ""; state.delivery.direccion = "";
        if (els?.totalTxt) els.totalTxt.textContent = money(0);
        if (els?.totalHid) els.totalHid.value = "0";
        if (els?.ubicacion) els.ubicacion.value = "";
        if (els?.dir) els.dir.value = "";
        if (els?.km) els.km.value = "";
    }

    function setDeliveryActive(on) {
        const els = getDeliveryEls();
        state.servicios.delivery = !!on;
        state.delivery.activo = !!on;
        syncServiciosHidden();
        if (els?.toggle) els.toggle.checked = !!on;
        if (els?.fields) els.fields.style.display = on ? "block" : "none";
        if (!on) resetDelivery(els);
        else { syncDeliveryGroups(els); computeDelivery(els); }
        syncTotalsHidden();
        refreshSummary();
        forceUpdateUI();
    }

    function bindDeliveryUI() {
        const els = getDeliveryEls();
        if (!els) return;

        els.toggle?.addEventListener("change", () => {
            setDeliveryActive(!!els.toggle.checked);
            if (els.toggle.checked) setTimeout(() => initSelect2EnAdicionales(), 100);
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
        });
    }

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
            totalHid: ensureHidden("dropoff_total", "dropoff_total_hidden")
        };
    }

    function syncDropoffGroups(els) {
        if (!els) return;
        const select = els.ubicacion;
        if (!select) return;

        const selectedOption = select.options[select.selectedIndex];
        const isManual = selectedOption && selectedOption.value === "0";

        if (els.groupDir) els.groupDir.style.display = isManual ? "block" : "none";
        if (els.groupKm) els.groupKm.style.display = isManual ? "block" : "none";

        if (!isManual) {
            if (els.dir) els.dir.value = "";
            if (els.km) els.km.value = "";
        }
    }

    function computeDropoff(els) {
        if (!els) return 0;

        console.log("🔍 computeDropoff - categoria:", state.categoria);
        console.log("🔍 computeDropoff - precio_km:", state.categoria?.precio_km);

        if (!state.categoria || !state.categoria.precio_km) {
            console.warn("⚠️ No hay categoría o precio_km para dropoff");
            return 0;
        }

        const precioKm = parseFloat(state.categoria.precio_km);
        let km = 0;
        let ubicacionTexto = "";
        let direccionTexto = "";

        const select = els.ubicacion;
        console.log("🔍 Select element:", select);
        console.log("🔍 Select value:", select?.value);
        console.log("🔍 Select selectedIndex:", select?.selectedIndex);

        if (select && select.selectedIndex >= 0) {
            const selectedOption = select.options[select.selectedIndex];
            const value = select.value;
            console.log("🔍 Selected option value:", value);
            console.log("🔍 Selected option text:", selectedOption?.text);
            console.log("🔍 Selected option dataset:", selectedOption?.dataset);

            if (value === "0") {
                km = parseFloat(els.km?.value) || 0;
                direccionTexto = String(els.dir?.value || "");
                ubicacionTexto = "Dirección personalizada";
            } else if (value && value !== "") {
                km = parseFloat(selectedOption?.dataset?.km) || 0;
                const estado = selectedOption?.dataset?.estado || "";
                const destino = selectedOption?.dataset?.destino || "";
                ubicacionTexto = `${estado} - ${destino}`.trim();
                direccionTexto = "";
            }
        }

        const total = km * precioKm;
        console.log(`🚩 Resultado: km=${km}, precioKm=${precioKm}, total=${total}`);

        state.dropoff.km = km;
        state.dropoff.total = total;
        state.dropoff.ubicacion = ubicacionTexto;
        state.dropoff.direccion = direccionTexto;

        if (els.totalTxt) els.totalTxt.textContent = money(total);
        if (els.totalHid) els.totalHid.value = total.toFixed(2);

        syncDropoffHidden();
        syncTotalsHidden();
        refreshSummary();

        console.log(`🚩 DropOff FINAL: ${km} km x $${precioKm}/km = $${total}`);

        forceUpdateUI();

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
            if (els?.totalHid) els.totalHid.value = "0";
            if (els?.groupDir) els.groupDir.style.display = "none";
            if (els?.groupKm) els.groupKm.style.display = "none";
            if (els?.dir) els.dir.value = "";
            if (els?.km) els.km.value = "";
        } else {
            syncDropoffGroups(els);
            if (els?.ubicacion && els.ubicacion.value) {
                computeDropoff(els);
            }
        }

        syncServiciosHidden();
        syncDropoffHidden();
        syncTotalsHidden();
        refreshSummary();
        forceUpdateUI();
    }

function bindDropoffUI() {
    const els = getDropoffEls();
    if (!els) return;

    console.log("🔄 Inicializando Sincronización Bidireccional Estricta de Drop-Off");

    const sucursalEntregaForm = document.getElementById('sucursal_entrega');
    const dropoffSelect = els.ubicacion;

    if (!sucursalEntregaForm || !dropoffSelect) return;

    function limpiarTexto(txt) {
        if (!txt) return "";
        return txt.toLowerCase()
            .normalize("NFD").replace(/[\u0300-\u036f]/g, "")
            .replace(/[^a-z0-9 ]/g, "")
            .replace(/\b(de|la|los|del|el|internacional|oficina)\b/g, "")
            .replace(/\s+/g, " ")
            .trim();
    }

    function sincronizarFormHaciaDropoff() {
        const selectedOpt = sucursalEntregaForm.options[sucursalEntregaForm.selectedIndex];
        if (!selectedOpt || !selectedOpt.value) return;

        const textoForm = limpiarTexto(selectedOpt.text);

        if (textoForm.includes("queretaro")) {
            dropoffSelect.value = "";
            if (typeof $ !== 'undefined' && $(dropoffSelect).data('select2')) {
                $(dropoffSelect).val("").trigger('change.select2');
            }
            state.dropoff.km = 0;
            state.dropoff.costo = 0;
            return;
        }

        let encontrado = false;
        for (let i = 0; i < dropoffSelect.options.length; i++) {
            const optDrop = dropoffSelect.options[i];
            const textoDrop = limpiarTexto(optDrop.text);

            if (textoDrop.includes(textoForm) || textoForm.includes(textoDrop) || optDrop.value === selectedOpt.value) {
                dropoffSelect.value = optDrop.value;
                encontrado = true;
                break;
            }
        }

        if (!encontrado) {
            dropoffSelect.value = "";
        }

        if (typeof $ !== 'undefined' && $(dropoffSelect).data('select2')) {
            $(dropoffSelect).trigger('change.select2');
        }
    }

    function sincronizarDropoffHaciaForm() {
        const selectedOptDrop = dropoffSelect.options[dropoffSelect.selectedIndex];
        if (!selectedOptDrop || !selectedOptDrop.value || selectedOptDrop.value === "0") return;

        const textoDrop = limpiarTexto(selectedOptDrop.text);
        let encontrado = false;

        for (let i = 0; i < sucursalEntregaForm.options.length; i++) {
            const optForm = sucursalEntregaForm.options[i];
            const textoForm = limpiarTexto(optForm.text);

            if (textoForm.includes(textoDrop) || textoDrop.includes(textoForm)) {
                if (sucursalEntregaForm.value !== optForm.value) {
                    sucursalEntregaForm.value = optForm.value;
                    if (typeof $ !== 'undefined' && $(sucursalEntregaForm).data('select2')) {
                        $(sucursalEntregaForm).trigger('change');
                    }
                }
                encontrado = true;
                break;
            }
        }

        if (!encontrado) {
            let opcionDinamica = Array.from(sucursalEntregaForm.options).find(o => o.text === selectedOptDrop.text);

            if (!opcionDinamica) {
                opcionDinamica = document.createElement('option');
                opcionDinamica.value = "dyn_" + selectedOptDrop.value;
                opcionDinamica.text = selectedOptDrop.text;
                sucursalEntregaForm.appendChild(opcionDinamica);
            }

            sucursalEntregaForm.value = opcionDinamica.value;
            if (typeof $ !== 'undefined' && $(sucursalEntregaForm).data('select2')) {
                $(sucursalEntregaForm).trigger('change');
            }
            console.log("➕ Sucursal foránea añadida dinámicamente al formulario principal:", selectedOptDrop.text);
        }
    }

    sucursalEntregaForm.addEventListener("change", () => {
        if (state.servicios.dropoff) {
            sincronizarFormHaciaDropoff();
            computeDropoff(els);
        }
    });

    dropoffSelect.addEventListener("change", () => {
        syncDropoffGroups(els);
        sincronizarDropoffHaciaForm();

        if (state.servicios.dropoff) {
            computeDropoff(els);
        }
    });

    els.km?.addEventListener("input", () => {
        if (state.servicios.dropoff && dropoffSelect.value !== "0") {
            dropoffSelect.value = "0";
            if (typeof $ !== 'undefined' && $(dropoffSelect).data('select2')) {
                $(dropoffSelect).val("0").trigger('change.select2');
            }
            computeDropoff(els);
        }
    });

    els.dir?.addEventListener("input", () => {
        if (state.servicios.dropoff && dropoffSelect.value !== "0") {
            dropoffSelect.value = "0";
            if (typeof $ !== 'undefined' && $(dropoffSelect).data('select2')) {
                $(dropoffSelect).val("0").trigger('change.select2');
            }
            state.dropoff.direccion = String(els.dir.value || "");
            computeDropoff(els);
        }
    });

    els.toggle?.addEventListener("change", (e) => {
        setDropoffActive(!!e.target.checked);
        if (e.target.checked) {
            if (typeof $ !== 'undefined' && $.fn.select2 && !$(dropoffSelect).data('select2')) {
                $(dropoffSelect).select2({ width: '100%' });
            }
            sincronizarFormHaciaDropoff();
            computeDropoff(els);
        }
    });
}

    function initSelect2EnAdicionales() {
        if (typeof $ === 'undefined' || typeof $.fn.select2 === 'undefined') {
            console.warn('⚠️ Select2 no está disponible');
            return;
        }

        const select2Config = {
            placeholder: "Buscar ubicación...",
            allowClear: false,
            width: '100%',
            dropdownCssClass: "select2-dropdown-custom",
            minimumResultsForSearch: 0,
            language: {
                noResults: function() {
                    return "❌ No se encontraron ubicaciones";
                },
                searching: function() {
                    return "🔍 Buscando...";
                }
            },
            matcher: function(params, data) {
                if ($.trim(params.term) === '') {
                    return data;
                }
                var term = params.term.toLowerCase().normalize("NFD").replace(/[\u0300-\u036f]/g, "");
                var text = data.text.toLowerCase().normalize("NFD").replace(/[\u0300-\u036f]/g, "");
                if (text.indexOf(term) > -1) {
                    return data;
                }
                return null;
            },
            escapeMarkup: function(markup) {
                return markup;
            }
        };

        const deliverySelect = document.getElementById('deliveryUbicacion');
        if (deliverySelect) {
            if ($(deliverySelect).data('select2')) {
                $(deliverySelect).select2('destroy');
            }
            $(deliverySelect).select2(select2Config);

            deliverySelect.style.display = 'none';
            deliverySelect.style.visibility = 'hidden';
            deliverySelect.style.opacity = '0';
            deliverySelect.style.position = 'absolute';
            deliverySelect.style.width = '1px';
            deliverySelect.style.height = '1px';

            $(deliverySelect).on('change', function() {
                const state = window._cotizacionAPI?.getState();
                if (state && state.servicios.delivery) {
                    const els = getDeliveryEls();
                    if (els) computeDelivery(els);
                }
            });
        }

        const dropoffSelect = document.getElementById('dropUbicacion');
        if (dropoffSelect) {
            if ($(dropoffSelect).data('select2')) {
                $(dropoffSelect).select2('destroy');
            }
            $(dropoffSelect).select2(select2Config);

            dropoffSelect.style.display = 'none';
            dropoffSelect.style.visibility = 'hidden';
            dropoffSelect.style.opacity = '0';
            dropoffSelect.style.position = 'absolute';
            dropoffSelect.style.width = '1px';
            dropoffSelect.style.height = '1px';

            $(dropoffSelect).on('change', function() {
                const state = window._cotizacionAPI?.getState();
                if (state && state.servicios.dropoff) {
                    const els = getDropoffEls();
                    if (els) computeDropoff(els);
                }
            });
        }

        console.log('✅ Select2 inicializado con buscador en Delivery y DropOff');
    }

    function getGasolinaEls() {
        return {
            toggle: qs("#gasolinaToggle"), fields: qs("#gasolinaFields"),
            totalTxt: qs("#gasolinaTotal"), totalHid: qs("#gasolinaTotalHidden"),
        };
    }

    function computeGasolina() {
        const els = getGasolinaEls();
        if (!els) return 0;

        console.log("⛽ Calculando gasolina prepago...");
        console.log("⛽ Categoría completa:", state.categoria);
        console.log("⛽ Capacidad tanque:", state.categoria?.capacidad_tanque);
        console.log("⛽ Tipo de capacidad:", typeof state.categoria?.capacidad_tanque);

        if (!state.categoria) {
            console.warn("⚠️ No hay categoría seleccionada");
            return 0;
        }

        let litros = 0;

        if (state.categoria.capacidad_tanque) {
            litros = parseFloat(state.categoria.capacidad_tanque);
        } else if (state.categoria.capacidad) {
            litros = parseFloat(state.categoria.capacidad);
        } else if (state.categoria.litros) {
            litros = parseFloat(state.categoria.litros);
        }

        if (!litros || isNaN(litros) || litros === 0) {
            console.warn("⚠️ No se encontró capacidad_tanque, usando valores por defecto");
            const CAPACIDAD_POR_ID = {
                1: 50, 2: 60, 3: 70, 4: 62, 5: 62, 6: 66, 7: 68, 8: 70, 9: 74, 10: 90, 11: 70
            };
            const categoriaId = parseInt(state.categoria.id);
            litros = CAPACIDAD_POR_ID[categoriaId] || 50;
            console.log(`📊 Usando capacidad por defecto ID ${categoriaId}: ${litros} litros`);
        }

        const PRECIO_POR_LITRO = 20;
        const total = litros * PRECIO_POR_LITRO;

        console.log(`⛽ RESULTADO: ${litros} litros × $${PRECIO_POR_LITRO} = $${total}`);

        state.gasolina.litros = litros;
        state.gasolina.total = total;
        state.gasolina.precioLitro = PRECIO_POR_LITRO;

        const label = document.getElementById("litrosLabel");
        if (label) label.textContent = litros;

        if (els.totalTxt) els.totalTxt.textContent = money(total);
        if (els.totalHid) els.totalHid.value = total.toFixed(2);

        syncTotalsHidden();
        refreshSummary();
        forceUpdateUI();

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
            state.gasolina.litros = 0;
            if (els?.totalTxt) els.totalTxt.textContent = money(0);
            if (els?.totalHid) els.totalHid.value = "0";
        } else {
            computeGasolina();
        }

        syncTotalsHidden();
        refreshSummary();
        forceUpdateUI();
    }

    function bindGasolinaUI() {
        const toggle = qs("#gasolinaToggle");
        if (!toggle) return;
        toggle.addEventListener("change", () => setGasolinaActive(!!toggle.checked));
    }

// =========================================
// 09 PROTECCIONES (PAQUETE)
// =========================================

    function clearIndividuales() {
        state.individuales.clear();
        syncIndividualesHidden();
        repaintIndividualesUI();
    }

    function setProteccion(p) {
        if (p) clearIndividuales();
        state.proteccion = p;
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
        if (sub) sub.textContent = `${money(p.precio)} ${p.charge === "por_dia" ? "/ día" : ""}`;
        if (rem) rem.style.display = "";
        syncProteccionHidden();
        syncTotalsHidden();
        refreshSummary();

    }

// =========================================
// 10 PROTECCIONES INDIVIDUALES
// =========================================

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

function inicializarEstadoProteccionesIndividuales() {
    if (!window.state || !window.state.individuales) return;

    const tarjetasDOM = Array.from(document.querySelectorAll(".individual-item"));

    if (tarjetasDOM.length === 0) {
        if (window.protecciones_data && !window._proteccionesInicializadas) {
            window.protecciones_data.forEach(prot => {
                const nombre = (prot.nombre || "").toUpperCase();
                const grupo = prot.grupo || "";
                const esLI = nombre === "LI" || (nombre.includes("LI") && !nombre.includes("EXT") && !nombre.includes("ALI"));
                const esAutomatica = (grupo === "Protecciones automáticas" || grupo === "automaticas");
                const esDecline = nombre.includes("DECLINE") || nombre.includes("CDW DECLINE");

                if (esAutomatica || esLI || esDecline) {
                    state.individuales.set(String(prot.id), {
                        id: String(prot.id),
                        nombre: prot.nombre,
                        desc: prot.descripcion || "",
                        precio: Number(prot.precio || 0),
                        charge: "por_dia",
                        grupo: grupo
                    });
                }
            });
            window._proteccionesInicializadas = true;
            if (typeof ejecutarRepaintYRefresh === 'function') ejecutarRepaintYRefresh();
        }
        return;
    }

    let cdwDeclineItem = null;
    let tieneOtraProteccionColisionActiva = false;
    let tieneOtraProteccionTercerosActiva = false;
    let liItem = null;

    tarjetasDOM.forEach(card => {
        const id = String(card.dataset.id || "");
        const parentTrack = card.closest(".scroll-h")?.id || "";
        const grupo = getGrupoLabelFromTrack(parentTrack);
        const nombre = (card.querySelector("h4")?.textContent?.trim() || "").toUpperCase();

        const esLI = nombre === "LI" || (nombre.includes("LI") && !nombre.includes("EXT") && !nombre.includes("ALI"));
        const esAutomatica = (grupo === "Protecciones automáticas");

        if (esAutomatica) {
            if (!state.individuales.has(id)) {
                const precio = Number(card.dataset.precio || 0);
                const desc = card.dataset.descripcion || card.querySelector("p")?.textContent?.trim() || "";
                state.individuales.set(id, { id, nombre, desc, precio, charge: "por_dia", grupo });
            }
        }

        if (grupo === "Daños a terceros") {
            if (esLI) liItem = { card, id, nombre, grupo };
            else if (state.individuales.has(id)) tieneOtraProteccionTercerosActiva = true;
        }

        if (grupo === "Colisión y robo") {
            if (nombre.includes("DECLINE") || nombre.includes("CDW DECLINE")) cdwDeclineItem = { card, id, nombre, grupo };
            else if (state.individuales.has(id)) tieneOtraProteccionColisionActiva = true;
        }
    });

    if (!window._proteccionesInicializadas) {
        if (liItem && !tieneOtraProteccionTercerosActiva && !state.individuales.has(liItem.id)) {
            const precio = Number(liItem.card.dataset.precio || 0);
            const desc = liItem.card.dataset.descripcion || liItem.card.querySelector("p")?.textContent?.trim() || "";
            state.individuales.set(liItem.id, { id: liItem.id, nombre: liItem.nombre, desc, precio, charge: "por_dia", grupo: liItem.grupo });
        }

        if (cdwDeclineItem && !tieneOtraProteccionColisionActiva && !state.individuales.has(cdwDeclineItem.id)) {
            const precio = Number(cdwDeclineItem.card.dataset.precio || 0);
            const desc = cdwDeclineItem.card.dataset.descripcion || cdwDeclineItem.card.querySelector("p")?.textContent?.trim() || "";
            state.individuales.set(cdwDeclineItem.id, { id: cdwDeclineItem.id, nombre: cdwDeclineItem.nombre, desc, precio, charge: "por_dia", grupo: cdwDeclineItem.grupo });
        }
        window._proteccionesInicializadas = true;
    } else {
        if (tieneOtraProteccionTercerosActiva && liItem) state.individuales.delete(liItem.id);
        if (tieneOtraProteccionColisionActiva && cdwDeclineItem) state.individuales.delete(cdwDeclineItem.id);
    }
}

function toggleIndividualFromCard(card) {
    if (!card) return;
    if (state.proteccion) setProteccion(null);

    if (typeof _ultimaInteraccion !== 'undefined') _ultimaInteraccion = Date.now();

    const id = String(card.dataset.id || "");
    const precio = Number(card.dataset.precio || 0);
    const nombre = card.querySelector("h4")?.textContent?.trim() || "Seguro individual";
    const desc = card.dataset.descripcion || "";
    const parentTrack = card.closest(".scroll-h")?.id || "";
    const grupo = getGrupoLabelFromTrack(parentTrack);

    const nombreUpper = nombre.toUpperCase();
    const esAutomatica = (grupo === "Protecciones automáticas");

    if (esAutomatica) {
        console.log("🔒 Esta protección automática está bloqueada.");
        return;
    }

    if (state.individuales.has(id)) {
        state.individuales.delete(id);
        console.log(`🔘 Desactivada manualmente: ${nombre}`);
    } else {
        if (grupo === "Colisión y robo" || grupo === "Daños a terceros") {
            for (const [existingId, item] of state.individuales.entries()) {
                if (item.grupo === grupo) {
                    state.individuales.delete(existingId);
                    console.log(`🔄 Exclusividad en ${grupo}: Se removió automáticamente ${item.nombre}`);
                }
            }
        }
        state.individuales.set(id, { id, nombre, desc, precio, charge: "por_dia", grupo });
        console.log(`✅ Activada: ${nombre}`);
    }

    ejecutarRepaintYRefresh();
}

function ejecutarRepaintYRefresh() {
    syncIndividualesHidden();
    repaintIndividualesUI();
    syncTotalsHidden();
    refreshSummary();
    refreshProteccionUIHeader();
}

function repaintIndividualesUI() {
    qsa(".individual-item").forEach((card) => {
        const id = String(card.dataset.id || "");
        const parentTrack = card.closest(".scroll-h")?.id || "";
        const grupo = getGrupoLabelFromTrack(parentTrack);
        const on = state.individuales.has(id);

        card.classList.toggle("is-selected", on);

        const sw = card.querySelector(".switch-individual");
        if (sw) sw.classList.toggle("is-on", on);

        if (grupo === "Protecciones automáticas") {
            card.classList.add("locked-protection");
            card.style.cursor = "not-allowed";
            if (sw) sw.style.opacity = "0.6";
        } else {
            card.classList.remove("locked-protection");
            card.style.cursor = "pointer";
            if (sw) sw.style.opacity = "1";
        }
    });
}

function ejecutarRepaintYRefresh() {
    syncIndividualesHidden();
    repaintIndividualesUI();
    syncTotalsHidden();
    refreshSummary();
    refreshProteccionUIHeader();
}

function verificarProteccionesAutomaticas() {
    const tarjetasAutomaticas = document.querySelectorAll("#insAutoTrack .individual-item");
    if (tarjetasAutomaticas.length === 0) return;
    let otrasCategoriasActivas = 0;
    for (const [_, item] of state.individuales.entries()) {
        if (item.grupo && item.grupo !== "Protecciones automáticas") {
            otrasCategoriasActivas++;
        }
    }
    if (otrasCategoriasActivas > 0) {
        tarjetasAutomaticas.forEach(card => {
            const autoId = String(card.dataset.id || "");
            if (!state.individuales.has(autoId)) {
                const autoPrecio = Number(card.dataset.precio || 0);
                const autoNombre = card.querySelector("h4")?.textContent?.trim() || "Protección automática";
                const autoDesc = card.querySelector("p")?.textContent?.trim() || "";

                state.individuales.set(autoId, {
                    id: autoId,
                    nombre: autoNombre,
                    desc: autoDesc,
                    precio: autoPrecio,
                    charge: "por_dia",
                    grupo: "Protecciones automáticas"
                });
            }
        });
    } else {
        tarjetasAutomaticas.forEach(card => {
            const autoId = String(card.dataset.id || "");
            state.individuales.delete(autoId);
        });
    }
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

// =========================================
// SISTEMA DE PROTECCIONES INDIVIDUALES
// =========================================

function inicializarYReglarProtecciones() {
    const itemsIndividuales = Array.from(document.querySelectorAll('.individual-item'));

    if (!itemsIndividuales.length) return;

    let cdwDeclineCard = null;
    let tieneOtraProteccionColisionActiva = false;

    itemsIndividuales.forEach(card => {
        const grupo = card.dataset.grupo || "";
        const nombre = (card.dataset.nombre || "").toLowerCase();
        const inputSwitch = card.querySelector('input[type="checkbox"]');

        if (!inputSwitch) return;

        if (grupo === "daños_terceros" && (nombre.includes("li") || nombre.includes("responsabilidad civil")) || grupo === "automaticas") {
            inputSwitch.checked = true;
            inputSwitch.disabled = true;
            card.classList.add('selected', 'locked');

            guardarProteccionEnState(card, true);
        }

        if (grupo === "colision_robo") {
            if (nombre.includes("cdw decline") || nombre.includes("decline")) {
                cdwDeclineCard = card;
            } else {
                if (inputSwitch.checked) {
                    tieneOtraProteccionColisionActiva = true;
                }
            }
        }
    });

    if (cdwDeclineCard) {
        const switchDecline = cdwDeclineCard.querySelector('input[type="checkbox"]');
        if (switchDecline) {
            if (!tieneOtraProteccionColisionActiva) {
                if (!switchDecline.dataset.inicializado) {
                    switchDecline.checked = true;
                    cdwDeclineCard.classList.add('selected');
                    guardarProteccionEnState(cdwDeclineCard, true);
                    switchDecline.dataset.inicializado = "true";
                }
            } else {
                switchDecline.checked = false;
                cdwDeclineCard.classList.remove('selected');
                guardarProteccionEnState(cdwDeclineCard, false);
            }
        }
    }

    itemsIndividuales.forEach(card => {
        const inputSwitch = card.querySelector('input[type="checkbox"]');
        if (!inputSwitch) return;

        inputSwitch.onchange = function(e) {
            const grupoActual = card.dataset.grupo || "";
            const nombreActual = (card.dataset.nombre || "").toLowerCase();
            const estaActivo = this.checked;

            if (grupoActual === "colision_robo") {
                if (estaActivo) {
                    itemsIndividuales.forEach(otraCard => {
                        if (otraCard !== card && otraCard.dataset.grupo === "colision_robo") {
                            const otroSwitch = otraCard.querySelector('input[type="checkbox"]');
                            if (otroSwitch && otroSwitch.checked) {
                                otroSwitch.checked = false;
                                otraCard.classList.remove('selected');
                                guardarProteccionEnState(otraCard, false);
                            }
                        }
                    });
                    card.classList.add('selected');
                    guardarProteccionEnState(card, true);
                } else {
                    card.classList.remove('selected');
                    guardarProteccionEnState(card, false);
                }
            } else {
                if (estaActivo) {
                    card.classList.add('selected');
                    guardarProteccionEnState(card, true);
                } else {
                    card.classList.remove('selected');
                    guardarProteccionEnState(card, false);
                }
            }

            if (typeof syncIndividualesHidden === 'function') syncIndividualesHidden();
            if (typeof syncTotalsHidden === 'function') syncTotalsHidden();
            if (typeof refreshSummary === 'function') refreshSummary();
        };
    });
}

function guardarProteccionEnState(card, agregar) {
    if (!window.state || !window.state.individuales) return;

    const id = card.dataset.id;
    if (!id) return;

    if (agregar) {
        window.state.individuales.set(id, {
            id: id,
            nombre: card.dataset.nombre || "",
            precio: Number(card.dataset.precio || 0),
            charge: card.dataset.charge || "por_dia",
            grupo: card.dataset.grupo || ""
        });
    } else {
        window.state.individuales.delete(id);
    }
}

let _ejecutandoPreseleccion = false;
let _ultimaInteraccion = 0;

function asegurarCDWDecline() {
    if (_ejecutandoPreseleccion) return;
    _ejecutandoPreseleccion = true;

    try {
        console.log('🔄 [asegurarCDWDecline] Iniciando...');

        const tabPanel = document.getElementById('tab-individuales');
        if (!tabPanel || !tabPanel.classList.contains('is-active')) {
            console.log('⏭️ Pestaña Individuales no activa, omitir preselección');
            return;
        }

        const now = Date.now();
        if (now - _ultimaInteraccion < 200) {
            console.log('⏳ Esperando a que termine la interacción del usuario...');
            return;
        }

        const grupo = 'Colisión y robo';

        let haySeleccion = false;
        for (const [_, item] of state.individuales.entries()) {
            if (item.grupo === grupo) {
                haySeleccion = true;
                break;
            }
        }

        if (haySeleccion) {
            console.log(`ℹ️ Ya hay selección en "${grupo}", no se modifica.`);
            return;
        }

        const track = document.getElementById('insColisionTrack');
        if (!track) {
            console.warn('⚠️ No se encontró el track de Colisión y robo');
            return;
        }

        const cards = track.querySelectorAll('.individual-item');
        let cdwCard = null;
        cards.forEach(card => {
            const nombre = card.querySelector('h4')?.textContent?.trim() || '';
            if (/cdw\s*(decline|declinado)|decline\s*cdw|sin\s*proteccion/i.test(nombre)) {
                cdwCard = card;
                console.log(`🔍 Encontrada tarjeta CDW: "${nombre}"`);
            }
        });

        if (!cdwCard) {
            console.warn('⚠️ No se encontró CDW decline en el grupo Colisión y robo');
            return;
        }

        const id = String(cdwCard.dataset.id || '');
        const nombre = cdwCard.querySelector('h4')?.textContent?.trim() || 'CDW decline';
        const precio = Number(cdwCard.dataset.precio || 0);
        const desc = cdwCard.dataset.descripcion || '';

        state.individuales.set(id, {
            id,
            nombre,
            desc,
            precio,
            charge: 'por_dia',
            grupo
        });

        console.log(`✅ CDW decline seleccionado automáticamente en "${grupo}"`);

        repaintIndividualesUI();
        refreshProteccionUIHeader();
        syncIndividualesHidden();
        syncTotalsHidden();
        refreshSummary();

    } catch (e) {
        console.error('Error en asegurarCDWDecline:', e);
    } finally {
        _ejecutandoPreseleccion = false;
    }
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

function preseleccionarProteccionesIndividuales() {
    console.log("🎯 Preseleccionando protecciones individuales en COTIZACIONES...");
    const todasLasTarjetas = document.querySelectorAll('.individual-item');

    let idLI = null;
    let datosLI = null;
    const idsProteccionesAuto = [];

    todasLasTarjetas.forEach(card => {
        const nombre = card.querySelector('h4')?.textContent?.trim() || '';
        const nombreUpper = nombre.toUpperCase();
        const parentTrack = card.closest('.scroll-h')?.id || '';
        const grupo = getGrupoLabelFromTrack(parentTrack);
        const id = String(card.dataset.id || '');
        const precio = Number(card.dataset.precio || 0);
        const desc = card.dataset.descripcion || '';

        const esLI = nombreUpper === 'LI' ||
                     (nombreUpper.includes('LI') &&
                      !nombreUpper.includes('EXT') &&
                      !nombreUpper.includes('ALI'));

        if (esLI && grupo === 'Daños a terceros') {
            idLI = id;
            datosLI = { id, nombre, desc, precio, grupo };
        }

        if (grupo === 'Protecciones automáticas') {
            idsProteccionesAuto.push({ id, nombre, desc, precio, grupo });
        }
    });

    if (idLI && datosLI && !state.individuales.has(idLI)) {
        state.individuales.set(idLI, {
            id: datosLI.id,
            nombre: datosLI.nombre,
            desc: datosLI.desc,
            precio: datosLI.precio,
            charge: 'por_dia',
            grupo: 'Daños a terceros'
        });
        console.log(`✅ LI fijo agregado: ${datosLI.nombre}`);
    }

    idsProteccionesAuto.forEach(auto => {
        if (!state.individuales.has(auto.id)) {
            state.individuales.set(auto.id, {
                id: auto.id,
                nombre: auto.nombre,
                desc: auto.desc,
                precio: auto.precio,
                charge: 'por_dia',
                grupo: 'Protecciones automáticas'
            });
            console.log(`✅ Protección automática agregada: ${auto.nombre}`);
        }
    });

    repaintIndividualesUI();
    refreshProteccionUIHeader();
    syncIndividualesHidden();
    syncTotalsHidden();
    refreshSummary();

    console.log('🎯 Preselección de LI y automáticas completada.');
}

// =========================================
// 11 ADDONS CON SWITCH
// =========================================

    function getAddonConfig(addonId) {
        const configs = {
            'silla_bebe': { id: 'silla_bebe', name: 'Silla de bebé', price: 150, charge: 'por_dia', maxQty: 3, defaultQty: 1,
                toggleSelector: '.addon-toggle[data-addon="silla_bebe"]', expandedSelector: '#sillaBebeExpanded',
                qtySelector: '#sillaBebeExpanded .qty-value', totalSelector: '#sillaBebeExpanded .addon-total',
                hiddenSelector: 'input[name="adicionales[silla_bebe]"]' },
            'conductor_extra': { id: 'conductor_extra', name: 'Conductor adicional', price: 150, charge: 'por_dia', maxQty: 3, defaultQty: 1,
                toggleSelector: '.addon-toggle[data-addon="conductor_extra"]', expandedSelector: '#conductorExtraExpanded',
                qtySelector: '#conductorExtraExpanded .qty-value', totalSelector: '#conductorExtraExpanded .addon-total',
                hiddenSelector: 'input[name="adicionales[conductor_extra]"]' }
        };
        return configs[addonId];
    }

    function getCurrentDays() { return Number(state.days || 0) || 1; }

    function updateAddonTotal(addonId, qty) {
        const config = getAddonConfig(addonId);
        if (!config) return;
        const days = getCurrentDays();
        const total = config.price * qty * days;
        const totalElement = document.querySelector(config.totalSelector);
        if (totalElement) totalElement.textContent = money(total);
        const hiddenInput = document.querySelector(config.hiddenSelector);
        if (hiddenInput) hiddenInput.value = qty;
        const addonState = { id: addonId, nombre: config.name, precio: config.price, charge: config.charge, qty: qty, total: total };
        if (qty > 0) state.addons.set(String(addonId), addonState);
        else state.addons.delete(String(addonId));
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
        if (stateAddon && stateAddon.qty) currentQty = Number(stateAddon.qty);
        else {
            const qtySpan = document.querySelector(config.qtySelector);
            if (qtySpan && qtySpan.dataset.qty) currentQty = Number(qtySpan.dataset.qty);
            else if (qtySpan) currentQty = Number(qtySpan.textContent);
        }
        let newQty = currentQty + change;
        if (newQty < 0) newQty = 0;
        if (newQty > config.maxQty) newQty = config.maxQty;
        if (newQty === 0) {
            const toggle = document.querySelector(config.toggleSelector);
            if (toggle) { toggle.checked = false; const event = new Event('change', { bubbles: true }); toggle.dispatchEvent(event); }
            const expanded = document.querySelector(config.expandedSelector);
            if (expanded) expanded.style.display = 'none';
        }
        const qtySpan = document.querySelector(config.qtySelector);
        if (qtySpan) { qtySpan.textContent = newQty; qtySpan.dataset.qty = newQty; }
        updateAddonTotal(addonId, newQty);
    }

    function setAddonActive(addonId, active) {
        const config = getAddonConfig(addonId);
        if (!config) return;
        const expanded = document.querySelector(config.expandedSelector);
        const toggle = document.querySelector(config.toggleSelector);
        if (expanded) expanded.style.display = active ? 'block' : 'none';
        if (toggle) toggle.checked = active;
        if (active) {
            const qtySpan = document.querySelector(config.qtySelector);
            if (qtySpan) { qtySpan.textContent = config.defaultQty; qtySpan.dataset.qty = config.defaultQty; }
            updateAddonTotal(addonId, config.defaultQty);
        } else updateAddonTotal(addonId, 0);
    }

    function initAddonsWithSwitch() {
        const addonIds = ['silla_bebe', 'conductor_extra'];
        addonIds.forEach(addonId => {
            const config = getAddonConfig(addonId);
            if (!config) return;
            const toggle = document.querySelector(config.toggleSelector);
            if (toggle && !toggle.dataset.initialized) {
                toggle.dataset.initialized = 'true';
                toggle.addEventListener('change', (e) => setAddonActive(addonId, e.target.checked));
            }
            const expanded = document.querySelector(config.expandedSelector);
            if (expanded && !expanded.dataset.quantityInitialized) {
                expanded.dataset.quantityInitialized = 'true';
                const minusBtn = expanded.querySelector('.qty-btn.minus');
                const plusBtn = expanded.querySelector('.qty-btn.plus');
                if (minusBtn) { const newMinusBtn = minusBtn.cloneNode(true); minusBtn.parentNode.replaceChild(newMinusBtn, minusBtn); newMinusBtn.addEventListener('click', (e) => { e.stopPropagation(); e.preventDefault(); handleAddonQtyChange(addonId, -1); }); }
                if (plusBtn) { const newPlusBtn = plusBtn.cloneNode(true); plusBtn.parentNode.replaceChild(newPlusBtn, plusBtn); newPlusBtn.addEventListener('click', (e) => { e.stopPropagation(); e.preventDefault(); handleAddonQtyChange(addonId, 1); }); }
                const qtySpan = expanded.querySelector('.qty-value');
                if (qtySpan && !qtySpan.dataset.qty) qtySpan.dataset.qty = Number(qtySpan.textContent) || 0;
            }
            const hiddenInput = document.querySelector(config.hiddenSelector);
            if (hiddenInput && Number(hiddenInput.value) > 0) {
                const savedQty = Number(hiddenInput.value);
                const toggle = document.querySelector(config.toggleSelector);
                if (toggle && !toggle.checked) {
                    setAddonActive(addonId, true);
                    const qtySpan = document.querySelector(config.qtySelector);
                    if (qtySpan) { qtySpan.textContent = savedQty; qtySpan.dataset.qty = savedQty; }
                    updateAddonTotal(addonId, savedQty);
                }
            }
        });
    }

    function refreshAddonsBadge() {
        const txt = qs("#addonsSelTxt"), sub = qs("#addonsSelSub"), clear = qs("#addonsClear");
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
            const price = Number(it.precio || 0), qty = Number(it.qty || 0);
            const perDay = String(it.charge || "por_evento") === "por_dia";
            sum += price * qty * (perDay ? days : 1);
        });
        return sum;
    }

// =========================================
// 12 TOTALES
// =========================================

    function calcTotals() {
        const days = Number(state.days || 0);
        const baseDiaOriginal = state.categoria ? Number(state.categoria.precio_dia || 0) : 0;
        const baseTotalOriginal = baseDiaOriginal * days;
        const baseTotal = state.base_editable !== null ? Number(state.base_editable) : baseTotalOriginal;
        const baseDia = days > 0 ? (baseTotal / days) : baseDiaOriginal;
        const prot = state.proteccion;
        const protPrice = prot ? Number(prot.precio || 0) : 0;
        const protTotal = prot ? (String(prot.charge || "por_evento") === "por_dia" ? protPrice * days : protPrice) : 0;
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

    function actualizarTotalBoton() {
        const btnTotal = document.getElementById('btnTotalText');
        if (!btnTotal) return;

        const totals = calcTotals();
        const totalValido = isNaN(totals.total) ? 0 : totals.total;

        btnTotal.innerHTML = `Total: ${money(totalValido)}`;
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
        qs("#tarifa_base").value = String(totals.baseDia || 0);
        qs("#tarifa_modificada").value = String(totals.subtotal || 0);
        qs("#tarifa_ajustada").value = String(totals.total || 0);

        actualizarTotalBoton();
        actualizarTotalNavbar();
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
            if (!state.categoria) {  return; }
            if (container.querySelector("input")) return;
            const totals = calcTotals();
            const precioActual = state.base_editable !== null ? state.base_editable : totals.baseTotal;
            const input = document.createElement("input");
            input.type = "number";
            input.value = precioActual.toFixed(2);
            input.min = 0;
            input.step = 0.01;
            Object.assign(input.style, { width: "140px", padding: "6px 10px", border: "2px solid #2563eb", borderRadius: "8px", fontWeight: "700", fontSize: "20px", color: "#1e293b", outline: "none", textAlign: "center", backgroundColor: "#ffffff" });
            container.innerHTML = "";
            container.appendChild(input);
            input.focus();
            input.select();
            const guardar = () => {
                let nuevoValor = parseFloat(input.value);
                if (isNaN(nuevoValor) || nuevoValor < 0) nuevoValor = precioActual;
                state.base_editable = nuevoValor;
                const days = Number(state.days || 1);
                const nuevoPrecioDia = nuevoValor / days;
                if (state.categoria) state.categoria.precio_dia = nuevoPrecioDia;
                container.innerHTML = "";
                container.textContent = money(nuevoValor);
                if (noteContainer) noteContainer.innerHTML = `${days} día(s) – precio por día ${money(nuevoPrecioDia).replace(" MXN", "")} MXN`;
                const sub = document.getElementById("catSelSub");
                if (sub && state.categoria) sub.textContent = `${money(nuevoPrecioDia)} / día · ${days} día(s)`;
                syncTotalsHidden();
                refreshSummary();
            };
            const cancelar = () => {
                container.innerHTML = "";
                container.textContent = money(precioActual);
                if (noteContainer) { const days = Number(state.days || 1); const precioDia = state.categoria ? state.categoria.precio_dia : 0; noteContainer.innerHTML = `${days} día(s) – precio por día ${money(precioDia).replace(" MXN", "")} MXN`; }
            };
            input.addEventListener("blur", guardar);
            input.addEventListener("keydown", (ev) => { if (ev.key === "Enter") { ev.preventDefault(); input.blur(); } if (ev.key === "Escape") { ev.preventDefault(); cancelar(); } });
        });
    }

// =========================================
// 13 RESUMEN
// =========================================

    function refreshSummary() {
        const days = Number(state.days || 0);
        const selR = qs("#sucursal_retiro"), selE = qs("#sucursal_entrega");
        const getText = (sel) => sel?.options?.[sel.selectedIndex]?.textContent?.trim() || "—";
        const fi = qs("#fecha_inicio_ui")?.value || qs("#fecha_inicio")?.value || "—";
        const hi = qs("#hora_retiro_ui")?.value || qs("#hora_retiro")?.value || "—";
        const ff = qs("#fecha_fin_ui")?.value || qs("#fecha_fin")?.value || "—";
        const hf = qs("#hora_entrega_ui")?.value || qs("#hora_entrega")?.value || "—";
        const setText = (id, val) => { const el = qs(id); if (el) el.textContent = val; };
        const checkbox = document.getElementById('differentDropoffAdmin');
        let textoEntrega = getText(selE);
        if (checkbox && !checkbox.checked) { const textoRetiro = getText(selR); if (textoRetiro !== "—") textoEntrega = textoRetiro; }
        setText("#resSucursalRetiro", getText(selR));
        setText("#resSucursalEntrega", textoEntrega);
        setText("#resFechaInicio", fi); setText("#resHoraInicio", hi);
        setText("#resFechaFin", ff); setText("#resHoraFin", hf);
        setText("#resDias", days ? `${days} día(s)` : "—");
        const cat = state.categoria;
        const totals = calcTotals();
        setText("#resCat", cat ? cat.nombre : "—");
        const baseEl = qs("#resBaseDia");
        if (baseEl && !baseEl.querySelector("input")) baseEl.textContent = cat ? `${money(totals.baseDia)} / día` : "—";
        setText("#resBaseTotal", cat ? money(totals.baseTotal) : "—");
        setText("#resDelivery", state.servicios.delivery ? money(totals.deliveryTotal) : money(0));
        setText("#resDropoff", state.servicios.dropoff ? money(totals.dropoffTotal) : money(0));
        setText("#resGasolina", state.servicios.gasolina ? money(totals.gasolinaTotal) : money(0));
        const sillaBebe = state.addons.get('silla_bebe'); setText("#resSillaBebe", sillaBebe && sillaBebe.qty > 0 ? `${money(sillaBebe.total)} (${sillaBebe.qty})` : money(0));
        const conductorExtra = state.addons.get('conductor_extra'); setText("#resConductorExtra", conductorExtra && conductorExtra.qty > 0 ? `${money(conductorExtra.total)} (${conductorExtra.qty})` : money(0));
        const svcList = [];
        if (state.servicios.dropoff) svcList.push("🚩 Drop Off");
        if (state.servicios.delivery) svcList.push("🚚 Delivery");
        if (state.servicios.gasolina) svcList.push("⛽ Gasolina prepago");
        const items = Array.from(state.addons.values()).filter(x => Number(x.qty || 0) > 0);
        items.forEach(a => { let icon = "➕"; if (a.id === "silla_bebe") icon = "👶"; if (a.id === "conductor_extra") icon = "👤"; svcList.push(`${icon} ${a.nombre} ×${a.qty}`); });
        setText("#resServicios", svcList.length ? svcList.join(", ") : "—");
        if (state.proteccion) setText("#resProte", `${state.proteccion.nombre} (${money(state.proteccion.precio)}${state.proteccion.charge === "por_dia" ? " / día" : ""})`);
        else {
            const inds = Array.from(state.individuales.values());
            if (!inds.length) setText("#resProte", "—");
            else { const preview = inds.slice(0, 3).map(x => x.nombre).join(", "); const rest = inds.length > 3 ? ` +${inds.length - 3} más` : ""; setText("#resProte", `🧩 Individuales: ${preview}${rest}`); }
        }
        setText("#resSub", money(totals.subtotal));
        setText("#resIva", money(totals.iva));
        setText("#resTotal", money(totals.total));

        const setTextV2 = (id, val) => { const el = document.getElementById(id); if (el) el.textContent = val; };

        function formatearFechaResumen(fechaStr) {
            if (!fechaStr || fechaStr === "—") return "—";
            let partes; if (fechaStr.includes('-')) partes = fechaStr.split('-'); else if (fechaStr.includes('/')) partes = fechaStr.split('/'); else return fechaStr;
            if (partes.length === 3) { const dia = partes[0].padStart(2, '0'); const mesNum = parseInt(partes[1]); const año = partes[2]; const meses = ['Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun', 'Jul', 'Ago', 'Sep', 'Oct', 'Nov', 'Dic']; const mesLetras = meses[mesNum - 1] || '???'; return `${dia} ${mesLetras} ${año}`; }
            return fechaStr;
        }
        function formatearHoraResumen(horaStr) { if (!horaStr || horaStr === "—") return "—"; if (horaStr.includes(':')) { const partes = horaStr.split(':'); if (partes.length >= 2) { const hora = partes[0].padStart(2, '0'); const minuto = partes[1].padStart(2, '0'); return `${hora}:${minuto} hrs`; } } return `${horaStr} hrs`; }

        setTextV2("resFechaInicioDetail", formatearFechaResumen(qs("#fecha_inicio_ui")?.value || "—"));
        setTextV2("resHoraInicioDetail", formatearHoraResumen(qs("#hora_retiro_ui")?.value || "—"));
        setTextV2("resFechaFinDetail", formatearFechaResumen(qs("#fecha_fin_ui")?.value || "—"));
        setTextV2("resHoraFinDetail", formatearHoraResumen(qs("#hora_entrega_ui")?.value || "—"));

        const resCatNameEl = document.getElementById("resCatName"), resCatDescEl = document.getElementById("resCatDesc"), resCatCodigoEl = document.getElementById("resCatCodigo");
        if (cat) {
            if (resCatNameEl) resCatNameEl.textContent = cat.nombre;
            if (resCatDescEl) { const descModelos = { 1: "Chevrolet Aveo o similar", 2: "Volkswagen Virtus o similar", 3: "Volkswagen Jetta o similar", 4: "Toyota Camry o similar", 5: "Jeep Renegade o similar", 6: "Volkswagen Taos o similar", 7: "Toyota Avanza o similar", 8: "Honda Odyssey o similar", 9: "Toyota Hiace o similar", 10: "Nissan Frontier o similar", 11: "Toyota Tacoma o similar" }; resCatDescEl.textContent = descModelos[cat.id] || cat.desc || ""; }
            if (resCatCodigoEl) { const codigos = { 1: "C", 2: "D", 3: "E", 4: "F", 5: "IC", 6: "I", 7: "IB", 8: "M", 9: "L", 10: "H", 11: "HI" }; resCatCodigoEl.textContent = `Código: ${codigos[cat.id] || ""}`; }
            let tipoVehiculo = "MEDIANOS", capacidadPasajeros = 5, tipoTransmision = "Automático";
            switch(parseInt(cat.id)) {
                case 1: tipoVehiculo = "COMPACTO"; capacidadPasajeros = 5; tipoTransmision = "Automático"; break;
                case 2: tipoVehiculo = "MEDIANOS"; capacidadPasajeros = 5; tipoTransmision = "Automático"; break;
                case 3: tipoVehiculo = "GRANDES"; capacidadPasajeros = 5; tipoTransmision = "Automático"; break;
                case 4: tipoVehiculo = "FULL SIZE"; capacidadPasajeros = 5; tipoTransmision = "Automático"; break;
                case 5: tipoVehiculo = "SUV COMPACTA"; capacidadPasajeros = 5; tipoTransmision = "Automático"; break;
                case 6: tipoVehiculo = "SUV MEDIANA"; capacidadPasajeros = 5; tipoTransmision = "Automático"; break;
                case 7: tipoVehiculo = "SUV FAMILIAR COMPACTA"; capacidadPasajeros = 7; tipoTransmision = "Automático"; break;
                case 8: tipoVehiculo = "MINIVAN"; capacidadPasajeros = 8; tipoTransmision = "Automático"; break;
                case 9: tipoVehiculo = "VAN PASAJEROS 13"; capacidadPasajeros = 13; tipoTransmision = "Manual"; break;
                case 10: tipoVehiculo = "PICKUP DOBLE CABINA"; capacidadPasajeros = 5; tipoTransmision = "Automático"; break;
                case 11: tipoVehiculo = "PICKUP 4X4 DOBLE CABINA"; capacidadPasajeros = 5; tipoTransmision = "Automático"; break;
            }
            setTextV2("resCatBadge", `${tipoVehiculo} · ${capacidadPasajeros} pasajeros · ${tipoTransmision}`);
            const imgEl = document.getElementById("resCatImage");
            if (imgEl) { if (cat.img) imgEl.src = cat.img; else { const card = document.querySelector(`.card-pick[data-id="${cat.id}"]`); const img = card?.querySelector(".cp-img img"); if (img) { imgEl.src = img.src; cat.img = img.src; } } imgEl.alt = cat.nombre; }
            const featuresContainer = document.getElementById("resCatFeatures");
            if (featuresContainer) featuresContainer.innerHTML = `<span><i class="fas fa-car"></i> ${tipoTransmision}</span><span><i class="fas fa-wind"></i> A/C</span><span><i class="fas fa-users"></i> ${capacidadPasajeros} pasajeros</span><span><i class="fab fa-apple"></i> CarPlay</span><span><i class="fab fa-android"></i> Android Auto</span>`;
        } else {
            if (resCatNameEl) resCatNameEl.textContent = "—"; if (resCatDescEl) resCatDescEl.textContent = "—"; if (resCatCodigoEl) resCatCodigoEl.textContent = "—";
            setTextV2("resCatBadge", "—"); const imgEl = document.getElementById("resCatImage"); if (imgEl) imgEl.src = "";
        }
        setTextV2("resBaseAmount", `$${(cat ? cat.precio_dia : 0).toLocaleString("es-MX", {minimumFractionDigits: 2})} MXN`);
        setTextV2("resBaseNote", `${days} día(s) – precio por día $${(cat ? cat.precio_dia : 0).toLocaleString("es-MX", {minimumFractionDigits: 2})} MXN`);
        setTextV2("resBaseTotalEstilo", money(totals.baseTotal));
        setTextV2("resIvaEstilo", money(totals.iva));
        setTextV2("resTotalEstilo", money(totals.total));

        const optionsContainer = document.getElementById("rv2OptionsList");
        if (optionsContainer) {
            let optionsHtml = "";
            if (state.servicios.delivery && state.delivery.total > 0) optionsHtml += `<div class="rv2-option-item"><span class="rv2-option-name"><i class="fas fa-truck"></i> Delivery</span><span class="rv2-option-price">${money(state.delivery.total)}</span></div>`;
            if (state.servicios.dropoff && state.dropoff.total > 0) optionsHtml += `<div class="rv2-option-item"><span class="rv2-option-name"><i class="fas fa-flag-checkered"></i> Drop Off</span><span class="rv2-option-price">${money(state.dropoff.total)}</span></div>`;
            if (state.servicios.gasolina && state.gasolina.total > 0) optionsHtml += `<div class="rv2-option-item"><span class="rv2-option-name"><i class="fas fa-gas-pump"></i> Gasolina Prepago</span><span class="rv2-option-price">${money(state.gasolina.total)}</span></div>`;
            const silla = state.addons.get('silla_bebe'); if (silla && silla.qty > 0) optionsHtml += `<div class="rv2-option-item"><span class="rv2-option-name"><i class="fas fa-baby-carriage"></i> Silla de bebé ×${silla.qty}</span><span class="rv2-option-price">${money(silla.total)}</span></div>`;
            const conductor = state.addons.get('conductor_extra'); if (conductor && conductor.qty > 0) optionsHtml += `<div class="rv2-option-item"><span class="rv2-option-name"><i class="fas fa-user-plus"></i> Conductor adicional ×${conductor.qty}</span><span class="rv2-option-price">${money(conductor.total)}</span></div>`;

            if (optionsHtml === "") optionsHtml = '<div class="rv2-option-item" style="color:#94a3b8;">Ninguna opción seleccionada</div>';
            optionsContainer.innerHTML = optionsHtml;
        }

        const proteccionesContainer = document.getElementById("rv2ProteccionesList"), proteccionesSection = document.getElementById("proteccionesSection");
        if (proteccionesContainer && proteccionesSection) {
            let proteccionesHtml = "", hasProtecciones = false;
            if (state.proteccion) {
                const prot = state.proteccion, protPrecio = Number(prot.precio || 0), protTotal = prot.charge === "por_dia" ? protPrecio * days : protPrecio;
                if (protTotal >= 0) { proteccionesHtml += `<div class="rv2-option-item"><span class="rv2-option-name"><i class="fas fa-shield-alt"></i> ${prot.nombre}</span><span class="rv2-option-price">${money(protTotal)} ${prot.charge === "por_dia" ? "/día" : ""}</span></div>`; hasProtecciones = true; }
            }
            const individualesList = Array.from(state.individuales.values());
            if (individualesList.length > 0) {
                individualesList.forEach(ind => {
                    const indPrecio = Number(ind.precio || 0), indTotal = indPrecio * days;
                    if (indTotal >= 0) { let icono = 'fa-shield-alt'; if (ind.grupo === 'Colisión y robo') icono = 'fa-car-crash'; if (ind.grupo === 'Gastos médicos') icono = 'fa-ambulance'; if (ind.grupo === 'Asistencia para el camino') icono = 'fa-road'; if (ind.grupo === 'Daños a terceros') icono = 'fa-handshake'; if (ind.grupo === 'Protecciones automáticas') icono = 'fa-microchip'; proteccionesHtml += `<div class="rv2-option-item"><span class="rv2-option-name"><i class="fas ${icono}"></i> ${ind.nombre}</span><span class="rv2-option-price">${money(indTotal)} <span style="font-size: 10px; color: #888;">/día</span></span></div>`; hasProtecciones = true; }
                });
            }
            if (hasProtecciones) { proteccionesContainer.innerHTML = proteccionesHtml; proteccionesSection.style.display = "block"; }
            else { proteccionesSection.style.display = "none"; }
        }
        initEditBaseTotal();
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

        const paisInput = document.getElementById("pais");
        if (paisInput && !paisInput.value) {
            mostrarError(document.querySelector(".readonly-country"), 'El país es obligatorio');
            todoOk = false;
        } else if (document.querySelector(".readonly-country")) {
            mostrarExito(document.querySelector(".readonly-country"));
        }

        if (typeof isAirportSelected === 'function' && isAirportSelected()) {
            const vuelo = document.getElementById("no_vuelo");
            if (vuelo && !vuelo.value.trim()) {
                mostrarError(vuelo, 'Número de vuelo obligatorio para Aeropuerto');
                todoOk = false;
            } else if (vuelo && vuelo.value.trim()) {
                mostrarExito(vuelo);
            }
        } else {
            const vuelo = document.getElementById("no_vuelo");
            if (vuelo && typeof limpiarError === 'function') {
                limpiarError(vuelo);
            }
        }

        return todoOk;
    }

// =========================================
// 14 VALIDACIÓN PRE-SUBMIT
// =========================================

    function validateBeforeSubmit() {
        let allValid = true;
        const sucRetiro = document.getElementById("sucursal_retiro");
        const sucRetiroVal = sucRetiro ? (typeof $ !== 'undefined' && $(sucRetiro).data('select2') ? $(sucRetiro).val() : sucRetiro.value) : "";
        if (!sucRetiroVal || sucRetiroVal === "") { mostrarError(sucRetiro, 'Selecciona una sucursal de retiro'); allValid = false; } else { mostrarExito(sucRetiro); }
        const checkbox = document.getElementById('differentDropoffAdmin');
        const isDifferentDropoff = checkbox && checkbox.checked;
        const sucEntrega = document.getElementById("sucursal_entrega");
        if (isDifferentDropoff) {
            const sucEntregaVal = sucEntrega ? (typeof $ !== 'undefined' && $(sucEntrega).data('select2') ? $(sucEntrega).val() : sucEntrega.value) : "";
            if (!sucEntregaVal || sucEntregaVal === "") { mostrarError(sucEntrega, 'Selecciona una sucursal de entrega'); allValid = false; } else { mostrarExito(sucEntrega); }
        } else if (sucEntrega) { mostrarExito(sucEntrega); }
        const fechaInicioUI = document.getElementById("fecha_inicio_ui"), fechaInicio = document.getElementById("fecha_inicio")?.value;
        if (!fechaInicio || fechaInicio === "") { mostrarError(fechaInicioUI, 'Selecciona una fecha de inicio'); allValid = false; } else { mostrarExito(fechaInicioUI); }
        const fechaFinUI = document.getElementById("fecha_fin_ui"), fechaFin = document.getElementById("fecha_fin")?.value;
        if (!fechaFin || fechaFin === "") { mostrarError(fechaFinUI, 'Selecciona una fecha de fin'); allValid = false; } else { mostrarExito(fechaFinUI); }
        if (fechaInicio && fechaFin && fechaFin <= fechaInicio) { mostrarError(fechaFinUI, 'La fecha de devolución debe ser posterior a la fecha de salida'); allValid = false; }
        const horaRetiroUI = document.getElementById("hora_retiro_ui"), horaRetiroContainer = horaRetiroUI?.closest('.dt-field-admin');
        if (horaRetiroContainer) { const selectHora = horaRetiroContainer.querySelector('.tp-hour'); if (selectHora && selectHora.value && selectHora.value !== "") { mostrarExito(selectHora); mostrarExito(horaRetiroUI); } else { mostrarError(horaRetiroUI, 'Selecciona una hora de retiro'); if (selectHora) mostrarError(selectHora, 'Selecciona una hora'); allValid = false; } }
        const horaEntregaUI = document.getElementById("hora_entrega_ui"), horaEntregaContainer = horaEntregaUI?.closest('.dt-field-admin');
        if (horaEntregaContainer) { const selectHora = horaEntregaContainer.querySelector('.tp-hour'); if (selectHora && selectHora.value && selectHora.value !== "") { mostrarExito(selectHora); mostrarExito(horaEntregaUI); } else { mostrarError(horaEntregaUI, 'Selecciona una hora de entrega'); if (selectHora) mostrarError(selectHora, 'Selecciona una hora'); allValid = false; } }
        const categoria = window.state?.categoria || state?.categoria, catInput = document.getElementById("categoria_id"), btnCategorias = document.getElementById("btnCategorias");
        if (!categoria || !catInput?.value) { if (btnCategorias) mostrarError(btnCategorias, 'Selecciona una categoría de vehículo'); allValid = false; } else if (btnCategorias) { mostrarExito(btnCategorias); }
        const nombre = document.getElementById("nombre_cliente"); if (!nombre?.value?.trim()) { mostrarError(nombre, 'El nombre es obligatorio'); allValid = false; } else { mostrarExito(nombre); }
        const apellidos = document.getElementById("apellidos_cliente"); if (!apellidos?.value?.trim()) { mostrarError(apellidos, 'Los apellidos son obligatorios'); allValid = false; } else { mostrarExito(apellidos); }
        const email = document.getElementById("email_cliente"), emailVal = email?.value?.trim() || "", emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (!emailVal) { mostrarError(email, 'El email es obligatorio'); allValid = false; } else if (!emailRegex.test(emailVal)) { mostrarError(email, 'Formato de email inválido'); allValid = false; } else { mostrarExito(email); }
        const telefono = document.getElementById("telefono_ui"), telVal = telefono?.value?.trim().replace(/\s+/g, "") || "";
        if (!telVal) { mostrarError(telefono, 'El teléfono es obligatorio'); allValid = false; } else if (telVal.length < 8) { mostrarError(telefono, 'Mínimo 8 dígitos'); allValid = false; } else { mostrarExito(telefono); }
        const paisInput = document.getElementById("pais");
        if (paisInput && !paisInput.value) { mostrarError(document.querySelector(".readonly-country"), 'El país es obligatorio'); allValid = false; } else if (document.querySelector(".readonly-country")) { mostrarExito(document.querySelector(".readonly-country")); }
        if (typeof isAirportSelected === 'function' && isAirportSelected()) {
            const vuelo = document.getElementById("no_vuelo");
            if (!vuelo?.value?.trim()) { mostrarError(vuelo, 'Número de vuelo obligatorio para Aeropuerto'); allValid = false; } else if (vuelo) { mostrarExito(vuelo); }
        } else { const vuelo = document.getElementById("no_vuelo"); if (vuelo && typeof limpiarError === 'function') limpiarError(vuelo); }
        if (!allValid) {
            const totalErrores = document.querySelectorAll('.field-error').length;
            if (totalErrores === 1) { const primerError = document.querySelector('.error-msg'); const mensaje = primerError?.textContent || 'Completa los campos marcados en rojo'; }
            const primerCampoError = document.querySelector('.field-error');
            if (primerCampoError) { primerCampoError.scrollIntoView({ behavior: 'smooth', block: 'center' }); primerCampoError.style.transition = 'box-shadow 0.3s ease'; primerCampoError.style.boxShadow = '0 0 0 3px rgba(239, 68, 68, 0.3)'; setTimeout(() => { primerCampoError.style.boxShadow = ''; }, 1000); }
        }
        return allValid;
    }

// =========================================
// 15 FLATPICKR (CALENDARIO)
// =========================================

    function initFlatpickrModalCalendar() {
        if (!window.flatpickr) return;
        let backdrop = document.querySelector(".fp-backdrop");
        if (!backdrop) { backdrop = document.createElement("div"); backdrop.className = "fp-backdrop"; document.body.appendChild(backdrop); }
        function makeActions(instance, labelText) {
            const actions = document.createElement("div");
            actions.className = "fp-actions";
            actions.innerHTML = `<button type="button" class="fp-today">Hoy</button><button type="button" class="fp-clear">Limpiar</button><button type="button" class="fp-label">✖ ${labelText}</button>`;
            actions.querySelector(".fp-today").addEventListener("click", () => instance.setDate(new Date(), true));
            actions.querySelector(".fp-clear").addEventListener("click", () => { instance.clear(); if (instance.input?.id === "fecha_inicio_ui") { qs("#fecha_inicio").value = ""; const finInstance = document.getElementById("fecha_fin_ui")._flatpickr; if (finInstance) finInstance.set("minDate", "today"); } if (instance.input?.id === "fecha_fin_ui") qs("#fecha_fin").value = ""; syncDays(); });
            return actions;
        }
        function openModal(instance) { backdrop.classList.add("is-open"); document.body.classList.add("no-scroll"); backdrop.onclick = () => instance.close(); }
        function closeModal() { backdrop.classList.remove("is-open"); document.body.classList.remove("no-scroll"); backdrop.onclick = null; }

       window.flatpickr("#fecha_inicio_ui", {
            locale: "es", dateFormat: "d-m-Y", altInput: true, altFormat: "d-M-y", allowInput: false, clickOpens: true, minDate: "today",
            disableMobile: true,
            onReady: (sel, str, instance) => {
                if (instance.altInput) {
                    instance.altInput.setAttribute("readonly", "readonly");
                    instance.altInput.setAttribute("inputmode", "none");

                    instance.altInput.addEventListener("focus", (e) => {
                        e.preventDefault();
                        instance.altInput.blur();
                    });
                }
            },
            onOpen: (sel, str, instance) => {
                openModal(instance);
                if (instance.altInput) instance.altInput.blur();
            },
            onClose: (sel, str, instance) => {
                closeModal();
                if (instance.altInput) instance.altInput.blur();
                instance.element.blur();
            },
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
                        }
                    } else {
                        finInstance.set("minDate", "today");
                    }
                }

                if (instance.altInput) instance.altInput.blur();
                instance.element.blur();

                syncDays();

                setTimeout(() => {
                    syncDays();
                    if (instance.altInput) instance.altInput.blur();
                    instance.element.blur();
                }, 150);
            }
        });

      window.flatpickr("#fecha_fin_ui", {
            locale: "es", dateFormat: "d-m-Y", altInput: true, altFormat: "d-M-y", allowInput: false, clickOpens: true, minDate: "today",
            disableMobile: true,
            onReady: (sel, str, instance) => {
                if (instance.altInput) {
                    instance.altInput.setAttribute("readonly", "readonly");
                    instance.altInput.setAttribute("inputmode", "none");

                    instance.altInput.addEventListener("focus", (e) => {
                        e.preventDefault();
                        instance.altInput.blur();
                    });
                }
            },
            onOpen: (sel, str, instance) => {
                openModal(instance);
                if (instance.altInput) instance.altInput.blur();
            },
            onClose: (sel, str, instance) => {
                closeModal();
                if (instance.altInput) instance.altInput.blur();
                instance.element.blur();
            },
            onChange: (selectedDates, dateStr, instance) => {
                const d = selectedDates?.[0];
                qs("#fecha_fin").value = d ? toISODate(d) : "";

                const ini = qs("#fecha_inicio")?.value;
                const fin = qs("#fecha_fin")?.value;
                if (ini && fin && fin < ini) {
                    qs("#fecha_fin").value = "";
                    qs("#fecha_fin_ui").value = "";
                }

                if (d) {
                    syncDays();
                }

                if (instance.altInput) instance.altInput.blur();
                instance.element.blur();

                setTimeout(() => {
                    syncDays();
                    if (instance.altInput) instance.altInput.blur();
                    instance.element.blur();
                }, 100);
            }
        });
    }

    function initTimeSelectors() {
        const horaRetiroInput = document.getElementById("hora_retiro_ui"), horaRetiroHidden = document.getElementById("hora_retiro");
        if (horaRetiroInput && !horaRetiroInput.dataset.tpReady) { horaRetiroInput.dataset.tpReady = "1"; horaRetiroInput.setAttribute("readonly", "readonly"); horaRetiroInput.classList.add("tp-hidden-input"); createTimeSelectsBelow(horaRetiroInput, horaRetiroHidden, "Hora "); }
        const horaEntregaInput = document.getElementById("hora_entrega_ui"), horaEntregaHidden = document.getElementById("hora_entrega");
        if (horaEntregaInput && !horaEntregaInput.dataset.tpReady) { horaEntregaInput.dataset.tpReady = "1"; horaEntregaInput.setAttribute("readonly", "readonly"); horaEntregaInput.classList.add("tp-hidden-input"); createTimeSelectsBelow(horaEntregaInput, horaEntregaHidden, "Hora"); }
    }

    function initTimeValidation() {
        const timeSelectors = document.querySelectorAll('.tp-hour');
        timeSelectors.forEach(select => {
            if (select._validationListener) select.removeEventListener('change', select._validationListener);
            const handler = function() { const inputHoraUI = this.closest('.dt-field-admin, .time-field-admin')?.querySelector('.input-buscador-admin'); const tieneValor = this.value && this.value !== ""; if (tieneValor) { if (inputHoraUI) mostrarExito(inputHoraUI); mostrarExito(this); } else { if (inputHoraUI) limpiarError(inputHoraUI); limpiarError(this); } };
            select._validationListener = handler; select.addEventListener('change', handler);
        });
    }

    function initDateValidation() {
        ['#fecha_inicio_ui', '#fecha_fin_ui'].forEach(selector => {
            const input = document.querySelector(selector);
            if (!input) return;
            if (input._validationListener) input.removeEventListener('change', input._validationListener);
            const handler = function() { const tieneValor = this.value && this.value.trim() !== ""; if (tieneValor) mostrarExito(this); else limpiarError(this); };
            input._validationListener = handler; input.addEventListener('change', handler);
            if (input._flatpickr) input._flatpickr.config.onChange.push(() => handler.call(input));
        });
    }

    function createTimeSelectsBelow(input, hiddenInput, placeholderText) {
        const wrap = input.closest(".time-field") || input.parentElement;
        if (!wrap) return;
        if (wrap.querySelector(".tp-selects")) return;
        const box = document.createElement("div"); box.className = "tp-selects";
        const selH = document.createElement("select"); selH.className = "tp-hour"; selH.setAttribute("aria-label", placeholderText);
        selH.innerHTML = '<option value="" disabled selected>' + placeholderText + '</option>';
        for (let h = 0; h < 24; h++) { const hour = String(h).padStart(2, "0"); const option = document.createElement("option"); option.value = hour; option.textContent = `${hour}:00`; selH.appendChild(option); }
        box.appendChild(selH); wrap.appendChild(box);
        if (!hiddenInput || !hiddenInput.value) { selH.value = ""; if (hiddenInput) hiddenInput.value = ""; input.value = ""; input.placeholder = "Hora"; }
        else { const existingHour = hiddenInput.value.split(":")[0]; if (existingHour && Array.from(selH.options).some(opt => opt.value === existingHour)) { selH.value = existingHour; input.value = hiddenInput.value; } else { selH.value = ""; if (hiddenInput) hiddenInput.value = ""; input.value = ""; input.placeholder = "Hora"; } }
        function sync() { if (!selH.value) { if (hiddenInput) hiddenInput.value = ""; input.value = ""; if (typeof refreshSummary === 'function') refreshSummary(); return; } const finalHour = String(selH.value).padStart(2, "0"); const timeValue = `${finalHour}:00`; if (hiddenInput) hiddenInput.value = timeValue; input.value = timeValue; if (typeof refreshSummary === 'function') refreshSummary(); }
        selH.addEventListener("change", sync);
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

    if (horaRetiroSelect) horaRetiroSelect.addEventListener('change', validar);
    if (horaEntregaSelect) horaEntregaSelect.addEventListener('change', validar);

    setTimeout(validar, 100);
}

// =========================================
// 28 LOAD PROTECCIONES (PAQUETES)
// =========================================

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

        function actualizarMontoGarantiaEnCard(cardElement, nombreProteccion) {
            if (!cardElement) return;

            const categoriaActual = window.state?.categoria;
            if (!categoriaActual || !categoriaActual.id) return;

            const montoGarantia = obtenerMontoGarantia(categoriaActual.id, nombreProteccion);
            if (montoGarantia === null) return;

            const montoFormateado = new Intl.NumberFormat('es-MX', {
                style: 'currency',
                currency: 'MXN',
                minimumFractionDigits: 0,
                maximumFractionDigits: 0
            }).format(montoGarantia);

            let garantiaExistente = cardElement.querySelector('.garantia-item');
            if (garantiaExistente) {
                garantiaExistente.innerHTML = `<strong style="color: #16a34a !important;">GARANTÍA:</strong> <span style="color: #16a34a !important; font-weight: 700;">${montoFormateado}</span>`;
                garantiaExistente.style.color = '#16a34a';
                garantiaExistente.style.fontWeight = '700';
    }
        }

        function formatDescriptionAsList(desc, textoGarantia = '') {
            if (!desc || desc === "") {
                if (textoGarantia) {
            return `<ul class="desc-list"><li>Sin descripción disponible</li><li class="garantia-item" style="color: #16a34a !important; font-weight: 700 !important;">${textoGarantia}</li></ul>`;
                return '<ul class="desc-list"><li>Sin descripción disponible</li></ul>';
                }
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

            let listaCompleta = `<ul class="desc-list">${listItems}`;
            if (textoGarantia) {
                listaCompleta += `<li class="garantia-item" style="color: #16a34a !important; font-weight: 700 !important;">${textoGarantia}</li>`;
    }
            listaCompleta += `</ul>`;

            return listaCompleta;
        }

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
            const isFree = Number(p.precio || 0) <= 0;
            const isSelected = window.state?.proteccion?.id == p.id;

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
                     textoGarantia = `<strong style="color: #16a34a !important;">GARANTÍA:</strong> <span style="color: #16a34a !important; font-weight: 700 !important;">${montoFormateado}</span>`;
                }
            }

            const descHtml = formatDescriptionAsList(p.desc, textoGarantia);

            const card = document.createElement("article");
            card.className = "pack-card" + (isFree ? " pack-card--free" : "") + (isSelected ? " is-selected" : "");
            card.style.minWidth = "320px";
            card.dataset.id = p.id;
            card.dataset.nombre = p.nombre;
            card.dataset.precio = p.precio;
            card.dataset.charge = p.charge;

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
                            <button class="btn primary btn-proteccion-dividido ${isSelected ? 'activado' : 'desactivado'}"
                                    type="button"
                                    data-id="${p.id}"
                                    data-nombre="${escapeHtml(p.nombre)}"
                                    data-precio="${p.precio}"
                                    data-charge="${p.charge}">
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
                    const proteccionId = btn.dataset.id;
                    const proteccionNombre = btn.dataset.nombre;
                    const proteccionPrecio = parseFloat(btn.dataset.precio);
                    const proteccionCharge = btn.dataset.charge;

                    if (!isCurrentlySelected) {
                        document.querySelectorAll('.pack-card').forEach(cardItem => {
                            const boton = cardItem.querySelector('.btn-proteccion-dividido');
                            if (boton) {
                                boton.classList.remove('activado');
                                boton.classList.add('desactivado');
                                const spanTexto = boton.querySelector('.btn-texto');
                                if (spanTexto) spanTexto.textContent = '';
                            }
                            cardItem.classList.remove('is-selected');
                        });

                        btn.classList.remove('desactivado');
                        btn.classList.add('activado');
                        const spanTexto = btn.querySelector('.btn-texto');
                        if (spanTexto) spanTexto.textContent = 'Seleccionado';
                        card.classList.add('is-selected');

                        actualizarMontoGarantiaEnCard(card, proteccionNombre);

                        if (typeof setProteccion === 'function') {
                            setProteccion({
                                id: proteccionId,
                                nombre: proteccionNombre,
                                precio: proteccionPrecio,
                                charge: proteccionCharge,
                                desc: p.desc
                            });
                        }

                        if (typeof refreshProteccionUIHeader === 'function') {
                            refreshProteccionUIHeader();
                        }

                        if (typeof mostrarToast === 'function') {
                            mostrarToast(`✅ Protección "${proteccionNombre}" seleccionada`, 'success');
                        }

                    } else {
                        btn.classList.remove('activado');
                        btn.classList.add('desactivado');
                        const spanTexto = btn.querySelector('.btn-texto');
                        if (spanTexto) spanTexto.textContent = '';
                        card.classList.remove('is-selected');

                        if (typeof setProteccion === 'function') {
                            setProteccion(null);
                        }

                        if (typeof refreshProteccionUIHeader === 'function') {
                            refreshProteccionUIHeader();
                        }

                        if (typeof mostrarToast === 'function') {
                            mostrarToast(`⚠️ Protección "${proteccionNombre}" deseleccionada`, 'info');
                        }
                    }

                    if (typeof syncTotalsHidden === 'function') {
                        syncTotalsHidden();
                    }
                    if (typeof refreshSummary === 'function') {
                        refreshSummary();
                    }
                    if (typeof actualizarCarritoFlotante === 'function') {
                        setTimeout(actualizarCarritoFlotante, 10);
                    }
                });
            }

            track.appendChild(card);
        });

    } catch (e) {
        console.error("Protecciones error:", e);
        track.innerHTML = `<div class="loading" style="padding:12px;font-weight:900;color:rgba(255,255,255,.9);">Error cargando protecciones.</div>`;
    }
}

function actualizarGarantiaPorCambioDeAuto() {
    const categoriaActual = window.state?.categoria;
    if (!categoriaActual || !categoriaActual.id) return;

    const cardSeleccionada = document.querySelector('#protePacksTrack .pack-card.is-selected');
    if (!cardSeleccionada) return;

    const nombreProteccion = cardSeleccionada.dataset.nombre;
    if (!nombreProteccion) return;

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

    const catId = parseInt(categoriaActual.id);
    const garantias = garantiaPorCategoria[catId];
    if (!garantias) return;

    const nombreUpper = nombreProteccion.toUpperCase();
    let tipoGarantia = 'CDW 20%';
    if (nombreUpper.includes('LDW')) tipoGarantia = 'LDW';
    else if (nombreUpper.includes('PDW')) tipoGarantia = 'PDW';
    else if (nombreUpper.includes('10%') || nombreUpper.includes('CDW PACK 1')) tipoGarantia = 'CDW 10%';
    else if (nombreUpper.includes('20%') || nombreUpper.includes('CDW PACK 2')) tipoGarantia = 'CDW 20%';
    else if (nombreUpper.includes('DECLINE') || nombreUpper.includes('RECHAZAR')) tipoGarantia = 'CDW declinado';

    const monto = garantias[tipoGarantia];
    if (!monto) return;

    const montoFormateado = new Intl.NumberFormat('es-MX', {
        style: 'currency',
        currency: 'MXN',
        minimumFractionDigits: 0,
        maximumFractionDigits: 0
    }).format(monto);

    let garantiaItem = cardSeleccionada.querySelector('.garantia-item');
    if (garantiaItem) {
        garantiaItem.innerHTML = `<strong style="color: #16a34a !important;">GARANTÍA:</strong> <span style="color: #16a34a !important; font-weight: 700;">${montoFormateado}</span>`;
garantiaItem.style.color = '#16a34a';
garantiaItem.style.fontWeight = '700';
    }
}

const originalSetCategoria = window.setCategoria;
if (typeof originalSetCategoria === 'function') {
    window.setCategoria = function(cat) {
        originalSetCategoria(cat);
        setTimeout(() => {
            actualizarGarantiaPorCambioDeAuto();
        }, 150);
    };
}

// =========================================
// 17 LOAD ADDONS
// =========================================

    async function loadAddons() {
        const list = qs("#addonsList");
        if (!list) return;
        list.innerHTML = `<div class="loading">Cargando adicionales...</div>`;
        try {
            const res = await fetch("/admin/cotizaciones/servicios", { headers: { "X-Requested-With": "XMLHttpRequest", "Accept": "application/json" } });
            const data = await res.json().catch(() => []);
            const arrRaw = Array.isArray(data) ? data : (data?.data || []);
            if (!arrRaw.length) { list.innerHTML = `<div class="loading">No hay adicionales disponibles.</div>`; return; }
            list.innerHTML = "";
            arrRaw.forEach((raw) => {
                const id = raw.id_servicio ?? raw.id ?? raw.idServicio;
                const nombre = raw.nombre ?? "Adicional";
                const desc = raw.descripcion ?? "";
                const precio = Number(raw.precio ?? raw.costo ?? raw.monto ?? 0);
                const current = state.addons.get(String(id));
                const qty = current ? Number(current.qty || 0) : 0;
                const card = document.createElement("article");
                card.className = "card-addon";
                card.dataset.id = String(id);
                card.innerHTML = `<div class="ad-left"><div class="cp-title">${escapeHtml(nombre)}</div><div class="cp-sub">${escapeHtml(desc || "Servicio adicional.")}</div><div class="cp-meta"><span class="pill">Cobro: por día</span></div></div><div class="ad-right"><div class="cp-price"><div class="muted small">Costo</div><div class="price-big">${money(precio).replace(" MXN", "")} <span>MXN / día</span></div></div><div class="qty-row"><button class="qty-btn minus" type="button" aria-label="menos">−</button><div class="qty" data-qty>${qty}</div><button class="qty-btn plus" type="button" aria-label="más">+</button></div></div>`;
                card.addEventListener("click", (e) => { const plus = e.target.closest(".plus"), minus = e.target.closest(".minus"); if (!plus && !minus) return; const item = { id, nombre, precio, charge: "por_dia", desc }; const cur = state.addons.get(String(id))?.qty || 0; const next = Math.max(0, Number(cur) + (plus ? 1 : -1)); setAddonQty(item, next); const qtyEl = card.querySelector("[data-qty]"); if (qtyEl) qtyEl.textContent = String(next); });
                list.appendChild(card);
            });
        } catch (e) { console.error("Addons error:", e); list.innerHTML = `<div class="loading">Error cargando adicionales...</div>`; }
    }

    function setAddonQty(item, qty) { const q = Math.max(0, Number(qty || 0)); if (q <= 0) state.addons.delete(String(item.id)); else state.addons.set(String(item.id), { ...item, qty: q }); syncAddonsHidden(); refreshAddonsBadge(); syncTotalsHidden(); refreshSummary(); }

// =========================================
// 18 AEROPUERTO
// =========================================

    function isAirportSelected() {
        const selR = document.getElementById("sucursal_retiro"), selE = document.getElementById("sucursal_entrega");
        const check = (sel) => { if (!sel || sel.selectedIndex < 0) return false; const opt = sel.options[sel.selectedIndex]; if (!opt) return false; const nombre = (opt.textContent || "").toLowerCase(); return nombre.includes("aeropuerto"); };
        return check(selR) || check(selE);
    }

    function syncVueloField() {
        const vuelo = qs("#no_vuelo");
        const show = isAirportSelected();
        if (vuelo) { if (show) vuelo.setAttribute("required", "required"); else vuelo.removeAttribute("required"); }
    }

// =========================================
// 19 SUBMIT POR AJAX
// =========================================

    async function submitCotizacionAjax(e) {
        e.preventDefault();

        const form = qs("#formCotizacion");
        if (!form) return;

        if (!state.categoria) {
            mostrarToast('⚠️ Debes seleccionar una categoría de vehículo', 'warning');
            return;
        }

        if (!validateBeforeSubmit()) return;

        const fd = new FormData(form);

        const mapeo = {
            'sucursal_retiro': 'pickup_sucursal_id',
            'sucursal_entrega': 'dropoff_sucursal_id',
            'fecha_inicio': 'pickup_date',
            'hora_retiro': 'pickup_time',
            'fecha_fin': 'dropoff_date',
            'hora_entrega': 'dropoff_time',
        };

        Object.keys(mapeo).forEach(campoOriginal => {
            const valor = fd.get(campoOriginal);
            if (valor !== null && valor !== "") {
                fd.delete(campoOriginal);
                fd.append(mapeo[campoOriginal], valor);
            }
        });

        fd.set("categoria_id", String(state.categoria.id));
        fd.set("tarifa_base", String(state.categoria.precio_dia || 0));

        let dropoffId = fd.get('dropoff_sucursal_id');
        if (!dropoffId || dropoffId === "") {
            const pickupId = fd.get('pickup_sucursal_id');
            if (pickupId) {
                fd.set('dropoff_sucursal_id', pickupId);
                console.log("✅ dropoff_sucursal_id forzado a:", pickupId);
            }
        }

        const totals = calcTotals();
        fd.set("total", String(totals.total || 0));
        fd.set("subtotal", String(totals.subtotal || 0));

        const clienteData = {
            nombre: fd.get('cliente[nombre]') || '',
            apellidos: fd.get('cliente[apellidos]') || '',
            email: fd.get('cliente[email]') || '',
            telefono: fd.get('cliente[telefono]') || '',
            comentarios: fd.get('cliente[comentarios]') || '',
            vuelo: fd.get('cliente[vuelo]') || '',
        };

        const camposCliente = ['cliente[nombre]', 'cliente[apellidos]', 'cliente[email]',
                               'cliente[telefono]', 'cliente[comentarios]', 'cliente[vuelo]'];
        camposCliente.forEach(campo => fd.delete(campo));

        Object.keys(clienteData).forEach(key => {
            if (clienteData[key]) {
                fd.append(`cliente[${key}]`, clienteData[key]);
            }
        });

        const serviciosData = {
            dropoff: {
                activo: state.servicios.dropoff,
                total: state.dropoff.total || 0,
                km: state.dropoff.km || 0,
                ubicacion: state.dropoff.ubicacion || '',
                direccion: state.dropoff.direccion || '',
            },
            delivery: {
                activo: state.servicios.delivery,
                total: state.delivery.total || 0,
                km: state.delivery.km || 0,
                ubicacion: state.delivery.ubicacion || '',
                direccion: state.delivery.direccion || '',
                precio_km: parseFloat(qs("#deliveryPrecioKm")?.value || 0),
            },
            gasolina: {
                activo: state.servicios.gasolina,
                total: state.gasolina.total || 0,
            },
        };

        Object.keys(serviciosData).forEach(key => {
            fd.append(`servicios[${key}][activo]`, serviciosData[key].activo ? '1' : '0');
            fd.append(`servicios[${key}][total]`, String(serviciosData[key].total || 0));
            fd.append(`servicios[${key}][km]`, String(serviciosData[key].km || 0));
            fd.append(`servicios[${key}][ubicacion]`, serviciosData[key].ubicacion || '');
            fd.append(`servicios[${key}][direccion]`, serviciosData[key].direccion || '');
            if (key === 'delivery') {
                fd.append(`servicios[${key}][precio_km]`, String(serviciosData[key].precio_km || 0));
            }
        });

        if (fd.has('servicios')) {
            fd.delete('servicios');
        }

        const actionButton = document.activeElement;
        if (actionButton && actionButton.value === 'guardar_enviar') {
            fd.append('enviarCorreo', '1');
        }

        const btn = e.submitter || qs("#btnCotizar");
        const btnGuardar = qs("#btnGuardarYEnviar");

        const setLoading = (on) => {
            if (btn) {
                btn.disabled = on;
                btn.style.opacity = on ? "0.85" : "1";
                btn.style.cursor = on ? "not-allowed" : "pointer";
                btn.textContent = on ? "⏳ Registrando..." : "✅ Registrar cotización";
            }
            if (btnGuardar) {
                btnGuardar.disabled = on;
                btnGuardar.style.opacity = on ? "0.85" : "1";
                btnGuardar.style.cursor = on ? "not-allowed" : "pointer";
            }
        };

        try {
            setLoading(true);
            const action = form.getAttribute("action");

            console.log("📤 Enviando datos:");
            for (let pair of fd.entries()) {
                console.log(pair[0] + ': ' + pair[1]);
            }

            const res = await fetch(action, {
                method: "POST",
                headers: {
                    "X-CSRF-TOKEN": getCsrf(),
                    "Accept": "application/json",
                    "X-Requested-With": "XMLHttpRequest"
                },
                body: fd
            });

            if (res.status === 422) {
                const data = await res.json().catch(() => null);
                const errors = data?.errors || {};
                let errorMsg = "❌ Errores en el formulario:\n";
                Object.keys(errors).forEach(key => {
                    errorMsg += `- ${key}: ${errors[key][0]}\n`;
                });
                mostrarToast(errorMsg, 'error');
                setLoading(false);
                return;
            }

            if (!res.ok) {
                const text = await res.text();
                console.error("Error response:", text);
                throw new Error("Error en la petición");
            }

            const data = await res.json().catch(() => ({}));

            const confirmPop = qs("#confirmPop");
            const redirectToCotizaciones = () => { window.location.href = "/admin/cotizaciones/listado"; };

            if (confirmPop && !confirmPop.dataset.bound) {
                confirmPop.dataset.bound = "1";
                const confirmOk = qs("#confirmOk");
                if (confirmOk) {
                    const newConfirmOk = confirmOk.cloneNode(true);
                    confirmOk.parentNode.replaceChild(newConfirmOk, confirmOk);
                    newConfirmOk.addEventListener("click", redirectToCotizaciones);
                }
                const confirmClose = qs("#confirmClose");
                if (confirmClose) {
                    const newConfirmClose = confirmClose.cloneNode(true);
                    confirmClose.parentNode.replaceChild(newConfirmClose, confirmClose);
                    newConfirmClose.addEventListener("click", () => {
                        window.location.href = "/ventas/menu";
                    });
                }
                confirmPop.addEventListener("click", (ev) => {
                    if (ev.target === confirmPop) redirectToCotizaciones();
                });
            }

            document.querySelectorAll('.field-error').forEach(el => {
                if (typeof limpiarError === 'function') limpiarError(el);
            });

            closeAllPops();
            openPop(confirmPop);

        } catch (err) {
            console.error(err);
            mostrarToast("Error de conexión. Intenta de nuevo.", 'error');
        } finally {
            setLoading(false);
        }
    }

// =========================================
// 20 TABS EN MODAL PROTECCIONES
// =========================================

    function setProteTab(tabId) { const btns = qsa("#proteccionPop .tab-btn[data-tab]"); const panels = qsa("#proteccionPop .tab-panel"); btns.forEach(b => b.classList.toggle("is-active", b.dataset.tab === tabId)); panels.forEach(p => p.classList.toggle("is-active", p.id === tabId)); }
    function bindProteTabs() {
    const pop = qs('#proteccionPop');
    if (!pop || pop.dataset.boundTabs === '1') return;
    pop.dataset.boundTabs = '1';

    const tabBtns = qsa('#proteccionPop .tab-btn[data-tab]');
    tabBtns.forEach((b) => {
        b.addEventListener('click', () => {
            const tabId = b.dataset.tab;
            setProteTab(tabId);

            if (tabId === 'tab-individuales') {
                setTimeout(() => {
                    asegurarCDWDecline();
                }, 200);
            }
        });
    });
}

// =========================================
// 21 PAÍSES + TELÉFONO
// =========================================

    const norm = (s) => String(s || "").toUpperCase().normalize("NFD").replace(/[\u0300-\u036f]/g, "");
    const isoToFlag = (iso2) => { const code = String(iso2 || "").toUpperCase(); if (!/^[A-Z]{2}$/.test(code)) return "🏳️"; const A = 0x1F1E6; return String.fromCodePoint(A + (code.charCodeAt(0) - 65)) + String.fromCodePoint(A + (code.charCodeAt(1) - 65)); };
    const COUNTRY_DATA = [
        { name: "MÉXICO", iso2: "MX", dial: "+52" }, { name: "ESTADOS UNIDOS", iso2: "US", dial: "+1" },
        { name: "AFGANISTÁN", iso2: "AF", dial: "+93" }, { name: "ALBANIA", iso2: "AL", dial: "+355" }, { name: "ALEMANIA", iso2: "DE", dial: "+49" }, { name: "ANDORRA", iso2: "AD", dial: "+376" }, { name: "ANGOLA", iso2: "AO", dial: "+244" }, { name: "ANTIGUA Y BARBUDA", iso2: "AG", dial: "+1" }, { name: "ARABIA SAUDITA", iso2: "SA", dial: "+966" }, { name: "ARGELIA", iso2: "DZ", dial: "+213" }, { name: "ARGENTINA", iso2: "AR", dial: "+54" }, { name: "ARMENIA", iso2: "AM", dial: "+374" }, { name: "AUSTRALIA", iso2: "AU", dial: "+61" }, { name: "AUSTRIA", iso2: "AT", dial: "+43" }, { name: "AZERBAIYÁN", iso2: "AZ", dial: "+994" }, { name: "BAHAMAS", iso2: "BS", dial: "+1" }, { name: "BANGLADESH", iso2: "BD", dial: "+880" }, { name: "BARBADOS", iso2: "BB", dial: "+1" }, { name: "BARÉIN", iso2: "BH", dial: "+973" }, { name: "BÉLGICA", iso2: "BE", dial: "+32" }, { name: "BELICE", iso2: "BZ", dial: "+501" }, { name: "BENÍN", iso2: "BJ", dial: "+229" }, { name: "BIELORRUSIA", iso2: "BY", dial: "+375" }, { name: "BOLIVIA", iso2: "BO", dial: "+591" }, { name: "BOSNIA Y HERZEGOVINA", iso2: "BA", dial: "+387" }, { name: "BOTSUANA", iso2: "BW", dial: "+267" }, { name: "BRASIL", iso2: "BR", dial: "+55" }, { name: "BRUNÉI", iso2: "BN", dial: "+673" }, { name: "BULGARIA", iso2: "BG", dial: "+359" }, { name: "BURKINA FASO", iso2: "BF", dial: "+226" }, { name: "BURUNDI", iso2: "BI", dial: "+257" }, { name: "BUTÁN", iso2: "BT", dial: "+975" }, { name: "CABO VERDE", iso2: "CV", dial: "+238" }, { name: "CAMBOYA", iso2: "KH", dial: "+855" }, { name: "CAMERÚN", iso2: "CM", dial: "+237" }, { name: "CANADÁ", iso2: "CA", dial: "+1" }, { name: "CATAR", iso2: "QA", dial: "+974" }, { name: "CHAD", iso2: "TD", dial: "+235" }, { name: "CHILE", iso2: "CL", dial: "+56" }, { name: "CHINA", iso2: "CN", dial: "+86" }, { name: "CHIPRE", iso2: "CY", dial: "+357" }, { name: "COLOMBIA", iso2: "CO", dial: "+57" }, { name: "COMORAS", iso2: "KM", dial: "+269" }, { name: "CONGO", iso2: "CG", dial: "+242" }, { name: "COREA DEL NORTE", iso2: "KP", dial: "+850" }, { name: "COREA DEL SUR", iso2: "KR", dial: "+82" }, { name: "COSTA DE MARFIL", iso2: "CI", dial: "+225" }, { name: "COSTA RICA", iso2: "CR", dial: "+506" }, { name: "CROACIA", iso2: "HR", dial: "+385" }, { name: "CUBA", iso2: "CU", dial: "+53" }, { name: "DINAMARCA", iso2: "DK", dial: "+45" }, { name: "DOMINICA", iso2: "DM", dial: "+1" }, { name: "ECUADOR", iso2: "EC", dial: "+593" }, { name: "EGIPTO", iso2: "EG", dial: "+20" }, { name: "EL SALVADOR", iso2: "SV", dial: "+503" }, { name: "EMIRATOS ÁRABES UNIDOS", iso2: "AE", dial: "+971" }, { name: "ERITREA", iso2: "ER", dial: "+291" }, { name: "ESLOVAQUIA", iso2: "SK", dial: "+421" }, { name: "ESLOVENIA", iso2: "SI", dial: "+386" }, { name: "ESPAÑA", iso2: "ES", dial: "+34" }, { name: "ESTONIA", iso2: "EE", dial: "+372" }, { name: "ESWATINI", iso2: "SZ", dial: "+268" }, { name: "ETIOPÍA", iso2: "ET", dial: "+251" }, { name: "FIJI", iso2: "FJ", dial: "+679" }, { name: "FILIPINAS", iso2: "PH", dial: "+63" }, { name: "FINLANDIA", iso2: "FI", dial: "+358" }, { name: "FRANCIA", iso2: "FR", dial: "+33" }, { name: "GABÓN", iso2: "GA", dial: "+241" }, { name: "GAMBIA", iso2: "GM", dial: "+220" }, { name: "GEORGIA", iso2: "GE", dial: "+995" }, { name: "GHANA", iso2: "GH", dial: "+233" }, { name: "GRANADA", iso2: "GD", dial: "+1" }, { name: "GRECIA", iso2: "GR", dial: "+30" }, { name: "GUATEMALA", iso2: "GT", dial: "+502" }, { name: "GUINEA", iso2: "GN", dial: "+224" }, { name: "GUINEA BISÁU", iso2: "GW", dial: "+245" }, { name: "GUINEA ECUATORIAL", iso2: "GQ", dial: "+240" }, { name: "GUYANA", iso2: "GY", dial: "+592" }, { name: "HAITÍ", iso2: "HT", dial: "+509" }, { name: "HONDURAS", iso2: "HN", dial: "+504" }, { name: "HUNGRÍA", iso2: "HU", dial: "+36" }, { name: "INDIA", iso2: "IN", dial: "+91" }, { name: "INDONESIA", iso2: "ID", dial: "+62" }, { name: "IRAK", iso2: "IQ", dial: "+964" }, { name: "IRÁN", iso2: "IR", dial: "+98" }, { name: "IRLANDA", iso2: "IE", dial: "+353" }, { name: "ISLANDIA", iso2: "IS", dial: "+354" }, { name: "ISRAEL", iso2: "IL", dial: "+972" }, { name: "ITALIA", iso2: "IT", dial: "+39" }, { name: "JAMAICA", iso2: "JM", dial: "+1" }, { name: "JAPÓN", iso2: "JP", dial: "+81" }, { name: "JORDANIA", iso2: "JO", dial: "+962" }, { name: "KAZAJISTÁN", iso2: "KZ", dial: "+7" }, { name: "KENIA", iso2: "KE", dial: "+254" }, { name: "KIRGUISTÁN", iso2: "KG", dial: "+996" }, { name: "KUWAIT", iso2: "KW", dial: "+965" }, { name: "LAOS", iso2: "LA", dial: "+856" }, { name: "LETONIA", iso2: "LV", dial: "+371" }, { name: "LÍBANO", iso2: "LB", dial: "+961" }, { name: "LIBERIA", iso2: "LR", dial: "+231" }, { name: "LIBIA", iso2: "LY", dial: "+218" }, { name: "LIECHTENSTEIN", iso2: "LI", dial: "+423" }, { name: "LITUANIA", iso2: "LT", dial: "+370" }, { name: "LUXEMBURGO", iso2: "LU", dial: "+352" }, { name: "MADAGASCAR", iso2: "MG", dial: "+261" }, { name: "MALASIA", iso2: "MY", dial: "+60" }, { name: "MALAWI", iso2: "MW", dial: "+265" }, { name: "MALDIVAS", iso2: "MV", dial: "+960" }, { name: "MALÍ", iso2: "ML", dial: "+223" }, { name: "MALTA", iso2: "MT", dial: "+356" }, { name: "MARRUECOS", iso2: "MA", dial: "+212" }, { name: "MAURICIO", iso2: "MU", dial: "+230" }, { name: "MAURITANIA", iso2: "MR", dial: "+222" }, { name: "MOLDAVIA", iso2: "MD", dial: "+373" }, { name: "MÓNACO", iso2: "MC", dial: "+377" }, { name: "MONGOLIA", iso2: "MN", dial: "+976" }, { name: "MONTENEGRO", iso2: "ME", dial: "+382" }, { name: "MOZAMBIQUE", iso2: "MZ", dial: "+258" }, { name: "MYANMAR", iso2: "MM", dial: "+95" }, { name: "NAMIBIA", iso2: "NA", dial: "+264" }, { name: "NEPAL", iso2: "NP", dial: "+977" }, { name: "NICARAGUA", iso2: "NI", dial: "+505" }, { name: "NÍGER", iso2: "NE", dial: "+227" }, { name: "NIGERIA", iso2: "NG", dial: "+234" }, { name: "NORUEGA", iso2: "NO", dial: "+47" }, { name: "NUEVA ZELANDA", iso2: "NZ", dial: "+64" }, { name: "OMÁN", iso2: "OM", dial: "+968" }, { name: "PAÍSES BAJOS", iso2: "NL", dial: "+31" }, { name: "PAKISTÁN", iso2: "PK", dial: "+92" }, { name: "PANAMÁ", iso2: "PA", dial: "+507" }, { name: "PARAGUAY", iso2: "PY", dial: "+595" }, { name: "PERÚ", iso2: "PE", dial: "+51" }, { name: "POLONIA", iso2: "PL", dial: "+48" }, { name: "PORTUGAL", iso2: "PT", dial: "+351" }, { name: "REINO UNIDO", iso2: "GB", dial: "+44" }, { name: "REPÚBLICA CHECA", iso2: "CZ", dial: "+420" }, { name: "REPÚBLICA DOMINICANA", iso2: "DO", dial: "+1" }, { name: "RUMANIA", iso2: "RO", dial: "+40" }, { name: "RUSIA", iso2: "RU", dial: "+7" }, { name: "SENEGAL", iso2: "SN", dial: "+221" }, { name: "SERBIA", iso2: "RS", dial: "+381" }, { name: "SINGAPUR", iso2: "SG", dial: "+65" }, { name: "SUDÁFRICA", iso2: "ZA", dial: "+27" }, { name: "SUECIA", iso2: "SE", dial: "+46" }, { name: "SUIZA", iso2: "CH", dial: "+41" }, { name: "TAILANDIA", iso2: "TH", dial: "+66" }, { name: "TÚNEZ", iso2: "TN", dial: "+216" }, { name: "TURQUÍA", iso2: "TR", dial: "+90" }, { name: "UCRANIA", iso2: "UA", dial: "+380" }, { name: "URUGUAY", iso2: "UY", dial: "+598" }, { name: "VENEZUELA", iso2: "VE", dial: "+58" }
    ];
    const COUNTRIES = [COUNTRY_DATA.find(x => x.name === "MÉXICO"), COUNTRY_DATA.find(x => x.name === "ESTADOS UNIDOS"), ...COUNTRY_DATA.filter(x => !["MÉXICO", "ESTADOS UNIDOS"].includes(x.name)).sort((a, b) => norm(a.name).localeCompare(norm(b.name)))].filter(Boolean);

    function titleCaseEs(s) {
        const str = String(s || "").toLowerCase();
        return str.replace(/(^|[\s-])([a-záéíóúñü])/gi, (m, p1, p2) => p1 + p2.toUpperCase());
    }

    function setPhoneCountry(c) {
        if (!c) return;

        const ladaHid = qs("#telefono_lada");
        const flag = qs("#phone_flag");
        const code = qs("#phone_code");

        if (ladaHid) ladaHid.value = c.dial || "+52";
        if (flag) flag.textContent = isoToFlag(c.iso2);
        if (code) code.textContent = c.dial || "+52";

        const paisHidden = qs("#pais");
        const paisFlagUI = qs("#pais_flag_ui");
        const paisTextUI = qs("#pais_text_ui");

        if (paisHidden) paisHidden.value = c.name;
        if (paisFlagUI) paisFlagUI.textContent = isoToFlag(c.iso2);
        if (paisTextUI) paisTextUI.textContent = titleCaseEs(c.name);

        syncTelefonoFinal();
    }

    function initPhoneCombo() {
        const root = qs("#phoneCombo"); if (!root) return;
        const dd = qs("#phone_dd"), toggle = qs("#phone_toggle"), search = qs("#phone_search"), list = qs("#phone_list");
        function openDD() { dd.classList.add("is-open"); render(search?.value || ""); search?.focus(); }
        function closeDD() { dd.classList.remove("is-open"); if (search) search.value = ""; }
        function render(q = "") { const qq = norm(q); const items = COUNTRIES.filter(c => norm(c.name).includes(qq) || norm(c.dial).includes(qq)); list.innerHTML = ""; if (!items.length) { list.innerHTML = `<div class="empty">Sin resultados</div>`; return; } items.forEach(c => { const row = document.createElement("div"); row.className = "row"; row.innerHTML = `<div class="l"><span class="flag">${isoToFlag(c.iso2)}</span><span class="name">${c.name}</span></div><span class="dial">${c.dial}</span>`; row.addEventListener("click", () => { setPhoneCountry(c); closeDD(); }); list.appendChild(row); }); }
        toggle?.addEventListener("click", () => { dd.classList.contains("is-open") ? closeDD() : openDD(); });
        search?.addEventListener("input", () => render(search.value)); search?.addEventListener("keydown", (e) => { if (e.key === "Escape") closeDD(); });
        document.addEventListener("click", (e) => { if (!root.contains(e.target)) closeDD(); });
        setPhoneCountry(COUNTRIES.find(c => c.name === "MÉXICO") || COUNTRIES[0]);
    }

    function syncTelefonoFinal() {
        const lada = (qs("#telefono_lada")?.value || "+52").trim();
        const num = String(qs("#telefono_ui")?.value || "").trim().replace(/\s+/g, "");
        const out = qs("#telefono_cliente");
        if (out) out.value = num ? `${lada}${num}` : "";
    }

// =========================================
// 22 EVENTOS UI
// =========================================

    function bindUI() {
        ["#fecha_inicio_ui", "#fecha_fin_ui"].forEach((id) => { qs(id)?.addEventListener("change", () => { syncDays(); }); });
        ["#hora_retiro_ui", "#hora_entrega_ui"].forEach((id) => { qs(id)?.addEventListener("change", () => { refreshSummary(); }); });
        qs("#sucursal_retiro")?.addEventListener("change", () => { syncVueloField(); refreshSummary(); });
        qs("#sucursal_entrega")?.addEventListener("change", () => { syncVueloField(); refreshSummary(); });
        qs("#gasolinaToggle")?.addEventListener("change", (e) => { setGasolinaActive(!!e.target.checked); });
        qs("#dropoffToggle")?.addEventListener("change", (e) => { setDropoffActive(!!e.target.checked); });
        bindDropoffUI();
        qs("#deliveryToggle")?.addEventListener("change", (e) => { setDeliveryActive(!!e.target.checked); });
        const catPop = qs("#catPop");
        qs("#btnCategorias")?.addEventListener("click", () => { repaintCategoriaModalEstimados(); openPop(catPop); });
        qs("#catClose")?.addEventListener("click", () => closePop(catPop));
        qs("#catCancel")?.addEventListener("click", () => closePop(catPop));
        catPop?.addEventListener("click", (e) => { const card = e.target.closest(".card-pick"); if (!card) return; const id = card.dataset.id, nombre = card.dataset.nombre || "", desc = card.dataset.desc || "", precio = Number(card.dataset.precio || 0), precioKm = Number(card.dataset.precioKm || 0), img = card.dataset.img || "", capacidad = parseFloat(card.dataset.litros || 0); setCategoria({ id, nombre, desc, precio_dia: precio, precio_km: precioKm, img, capacidad_tanque: capacidad }); closePop(catPop); });
        qs("#catRemove")?.addEventListener("click", () => setCategoria(null));
        const protPop = qs("#proteccionPop");
        qs("#btnProtecciones")?.addEventListener("click", async () => { openPop(protPop); setProteTab("tab-paquetes"); await loadProtecciones(); inicializarEstadoProteccionesIndividuales(); repaintIndividualesUI(); inicializarYReglarProtecciones(); refreshProteccionUIHeader(); });
        qs("#proteClose")?.addEventListener("click", () => closePop(protPop));
        qs("#proteCancel")?.addEventListener("click", () => closePop(protPop));
        qs("#proteRemove")?.addEventListener("click", () => { setProteccion(null); state.individuales.clear(); syncIndividualesHidden(); refreshProteccionUIHeader(); syncTotalsHidden(); refreshSummary(); });
        qs("#proteApply")?.addEventListener("click", () => { syncProteccionHidden(); syncIndividualesHidden(); refreshProteccionUIHeader(); syncTotalsHidden(); refreshSummary(); closePop(protPop); });
        document.addEventListener("click", (e) => { const card = e.target.closest(".individual-item"); if (!card) return; const isBtn = e.target.closest("button,a,input,textarea,select"); if (isBtn) return; toggleIndividualFromCard(card); });
        const addPop = qs("#addonsPop");
        qs("#btnAddons")?.addEventListener("click", async () => { openPop(addPop); await loadAddons(); });
        qs("#addonsClose")?.addEventListener("click", () => closePop(addPop));
        qs("#addonsCancel")?.addEventListener("click", () => closePop(addPop));
        qs("#addonsApply")?.addEventListener("click", () => { closePop(addPop); refreshAddonsBadge(); refreshSummary(); syncTotalsHidden(); });
        qs("#addonsClear")?.addEventListener("click", () => { state.addons.clear(); syncAddonsHidden(); refreshAddonsBadge(); syncTotalsHidden(); refreshSummary(); });
        const resPop = qs("#resumenPop");
        qs("#btnResumen")?.addEventListener("click", () => { syncDays(); repaintCategoriaModalEstimados(); refreshProteccionUIHeader(); refreshSummary(); openPop(resPop); });
        qs("#resumenClose")?.addEventListener("click", () => closePop(resPop));
        qs("#resumenOk")?.addEventListener("click", () => closePop(resPop));
        qsa(".pop.modal").forEach((pop) => { pop.addEventListener("click", (e) => { if (e.target !== pop) return; if (pop.id === "confirmPop") return; closePop(pop); }); });
        qs("#telefono_ui")?.addEventListener("input", syncTelefonoFinal);
        qs("#formCotizacion")?.addEventListener("submit", submitCotizacionAjax);
        bindProteTabs();
    }

// =========================================
// 23 FUERZA RECÁLCULO
// =========================================

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
        if (state.servicios.gasolina) computeGasolina();
        syncTotalsHidden();
        refreshSummary();

        actualizarTotalBoton();
        actualizarTotalNavbar();
    }

    function forceUpdateUI() {
        const dropTotal = document.getElementById("dropTotal");
        if (dropTotal && state.dropoff.total !== undefined) {
            dropTotal.textContent = money(state.dropoff.total);
        }

        const deliveryTotal = document.getElementById("deliveryTotal");
        if (deliveryTotal && state.delivery.total !== undefined) {
            deliveryTotal.textContent = money(state.delivery.total);
        }

        const gasolinaTotal = document.getElementById("gasolinaTotal");
        if (gasolinaTotal && state.gasolina.total !== undefined) {
            gasolinaTotal.textContent = money(state.gasolina.total);
        }

        const deliveryHidden = document.getElementById("deliveryTotalHidden");
        if (deliveryHidden) {
            deliveryHidden.value = state.delivery.total || 0;
        }

        const dropoffHidden = document.getElementById("dropoffTotalHidden");
        if (dropoffHidden) {
            dropoffHidden.value = state.dropoff.total || 0;
        }

        const gasolinaHidden = document.getElementById("gasolinaTotalHidden");
        if (gasolinaHidden) {
            gasolinaHidden.value = state.gasolina.total || 0;
        }

        console.log("🔄 UI Actualizada - Delivery:", money(state.delivery.total), "DropOff:", money(state.dropoff.total));

        const deliveryEl = document.getElementById("deliveryTotal");
        const dropEl = document.getElementById("dropTotal");

        console.log("🔍 Visibilidad Delivery:", {
            existe: !!deliveryEl,
            texto: deliveryEl?.textContent,
            visible: deliveryEl ? window.getComputedStyle(deliveryEl).display !== 'none' : false,
            padreVisible: deliveryEl?.parentElement ? window.getComputedStyle(deliveryEl.parentElement).display !== 'none' : false
        });

        console.log("🔍 Visibilidad DropOff:", {
            existe: !!dropEl,
            texto: dropEl?.textContent,
            visible: dropEl ? window.getComputedStyle(dropEl).display !== 'none' : false
        });
    }

// =========================================
// 24 SELECT2 CON ICONOS
// =========================================

    function initSelect2Sucursales() {
        if (typeof $ === 'undefined' || typeof $.fn.select2 === 'undefined') { console.warn('Select2 no está disponible'); return; }
        function formatOption(option) {
            if (!option.id) return $('<span><i class="fa-solid fa-location-dot" style="margin-right: 10px; color: #000000; font-size: 18px;"></i> ' + option.text + '</span>');
            let iconClass = window.iconosPorId?.[option.id] || 'fa-building';
            return $('<span><i class="fa-solid ' + iconClass + '" style="margin-right: 10px; color: #000000; font-size: 18px;"></i> ' + option.text + '</span>');
        }
        if ($('#sucursal_retiro').data('select2')) $('#sucursal_retiro').select2('destroy');
        if ($('#sucursal_entrega').data('select2')) $('#sucursal_entrega').select2('destroy');
        const select2Config = { templateResult: formatOption, templateSelection: formatOption, escapeMarkup: function (markup) { return markup; }, width: '100%', minimumResultsForSearch: Infinity, allowClear: false };
        $('#sucursal_retiro').select2(select2Config);
        $('#sucursal_entrega').select2(select2Config);
    }

// =========================================
// 25 CHECKBOX "DEVOLVER EN OTRO DESTINO"
// =========================================

function initDifferentDropoff() {
    const checkbox = document.getElementById('differentDropoffAdmin');
    const dropoffWrapper = document.getElementById('dropoffWrapperAdmin');
    const dropoffSelect = document.getElementById('sucursal_entrega');
    const dropoffToggle = document.getElementById('dropoffToggle');
    const pickupSelect = document.getElementById('sucursal_retiro');

    if (checkbox && dropoffWrapper && dropoffSelect) {
        dropoffWrapper.style.display = checkbox.checked ? 'block' : 'none';
        dropoffSelect.disabled = !checkbox.checked;

        checkbox.addEventListener('change', function() {
            if (this.checked) {
                dropoffWrapper.style.display = 'block';
                dropoffSelect.disabled = false;

                if (pickupSelect && pickupSelect.value && (!dropoffSelect.value || dropoffSelect.value === '')) {
                    const pickupText = pickupSelect.options[pickupSelect.selectedIndex]?.text || '';
                    for (let i = 0; i < dropoffSelect.options.length; i++) {
                        if (dropoffSelect.options[i].text === pickupText) {
                            dropoffSelect.value = dropoffSelect.options[i].value;
                            if (typeof $ !== 'undefined' && $(dropoffSelect).data('select2')) {
                                $(dropoffSelect).val(dropoffSelect.value).trigger('change');
                            }
                            break;
                        }
                    }
                }

                if (dropoffToggle && !dropoffToggle.checked) {
                    dropoffToggle.checked = true;
                    dropoffToggle.dispatchEvent(new Event('change', { bubbles: true }));
                    console.log("🔘 DropOff activado desde checkbox");
                }
            } else {
                dropoffWrapper.style.display = 'none';
                dropoffSelect.disabled = true;

                if (dropoffToggle && dropoffToggle.checked) {
                    dropoffToggle.checked = false;
                    dropoffToggle.dispatchEvent(new Event('change', { bubbles: true }));
                    console.log("🔘 DropOff desactivado desde checkbox");
                }
            }

            if (typeof refreshSummary === 'function') refreshSummary();
        });

        if (pickupSelect) {
            pickupSelect.addEventListener('change', function() {
                if (!checkbox.checked && this.value) {
                    const pickupText = this.options[this.selectedIndex]?.text || '';
                    for (let i = 0; i < dropoffSelect.options.length; i++) {
                        if (dropoffSelect.options[i].text === pickupText) {
                            dropoffSelect.value = dropoffSelect.options[i].value;
                            if (typeof $ !== 'undefined' && $(dropoffSelect).data('select2')) {
                                $(dropoffSelect).val(dropoffSelect.value).trigger('change');
                            }
                            break;
                        }
                    }
                }
            });
        }
    }
}

// =========================================
// 26 ACORDEÓN Y FLUJO SECUENCIAL
// =========================================

    let categoriaDesbloqueada = false, adicionalesDesbloqueada = false, proteccionesDesbloqueada = false, clienteDesbloqueada = false, flujoCompletado = false;

    function actualizarEstadoSeccion(seccion, desbloqueada) {
        if (!seccion) return;
        const head = seccion.querySelector('.stack-head');
        if (desbloqueada) {
            seccion.classList.remove('locked');
            if (head) { head.style.opacity = '1'; head.style.pointerEvents = 'auto'; head.style.cursor = 'pointer'; }
        } else {
            seccion.classList.add('locked');
            if (head) { head.style.opacity = '0.6'; head.style.pointerEvents = 'none'; head.style.cursor = 'not-allowed'; }
            const body = seccion.querySelector('.stack-body'), indicator = seccion.querySelector('.stack-indicator');
            if (body && body.classList.contains('expanded')) body.classList.remove('expanded');
            if (indicator && indicator.classList.contains('expanded')) indicator.classList.remove('expanded');
        }
    }

    function actualizarTodasSecciones() {
        const seccionCategoria = document.querySelector('.acordeon-item[data-seccion="categoria"]'),
              seccionAdicionales = document.querySelector('.acordeon-item[data-seccion="adicionales"]'),
              seccionProtecciones = document.querySelector('.acordeon-item[data-seccion="protecciones"]'),
              seccionCliente = document.querySelector('.acordeon-item[data-seccion="cliente"]');
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
        const body = seccion.querySelector('.stack-body'), indicator = seccion.querySelector('.stack-indicator');
        if (body && !body.classList.contains('expanded')) body.classList.add('expanded');
        if (indicator && !indicator.classList.contains('expanded')) indicator.classList.add('expanded');
        setTimeout(() => { seccion.scrollIntoView({ behavior: 'smooth', block: 'start' }); }, 100);
    }

    function desbloquearCategoria() { if (categoriaDesbloqueada) return; categoriaDesbloqueada = true; actualizarTodasSecciones(); abrirSeccion(document.querySelector('.acordeon-item[data-seccion="categoria"]')); }
    function desbloquearAdicionales() { if (adicionalesDesbloqueada) return; adicionalesDesbloqueada = true; actualizarTodasSecciones(); abrirSeccion(document.querySelector('.acordeon-item[data-seccion="adicionales"]')); }
    function desbloquearProtecciones() { if (proteccionesDesbloqueada) return; proteccionesDesbloqueada = true; actualizarTodasSecciones(); abrirSeccion(document.querySelector('.acordeon-item[data-seccion="protecciones"]')); }
    function desbloquearCliente() { if (clienteDesbloqueada) return; clienteDesbloqueada = true; actualizarTodasSecciones(); abrirSeccion(document.querySelector('.acordeon-item[data-seccion="cliente"]')); }
    function completarFlujo() { if (flujoCompletado) return; flujoCompletado = true; actualizarTodasSecciones(); }

   function configurarBotonPrincipal() {
    const btn = document.getElementById('btnBuscarCotizacion');
    if (!btn) {
        console.error('❌ Botón btnBuscarCotizacion no encontrado');
        return;
    }

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

        if (validarCamposUbicacion()) {
            console.log('✅ Validación exitosa');

            document.body.classList.add('buscar-realizado');

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

            if (typeof desbloquearCategoria === 'function') {
                desbloquearCategoria();
            }

            setTimeout(() => {
                const catPop = document.getElementById('catPop');
                if (catPop) {
                    if (typeof repaintCategoriaModalEstimados === 'function') {
                        repaintCategoriaModalEstimados();
                    }
                    openPop(catPop);
                }
            }, 300);
        } else {
            console.log('❌ Validación fallida - Corrige los campos en rojo');
        }
    });
}

    function configurarBotonesSiguiente() {
        const btnSiguienteAdicionales = document.querySelector('.acordeon-item[data-seccion="adicionales"] .btn-siguiente');
        if (btnSiguienteAdicionales) {
            const newBtn = btnSiguienteAdicionales.cloneNode(true);
            btnSiguienteAdicionales.parentNode.replaceChild(newBtn, btnSiguienteAdicionales);
            newBtn.addEventListener('click', (e) => { e.preventDefault(); e.stopPropagation(); desbloquearProtecciones(); });
        }
        const btnSiguienteProtecciones = document.querySelector('.acordeon-item[data-seccion="protecciones"] .btn-siguiente');
        if (btnSiguienteProtecciones) {
            const newBtn = btnSiguienteProtecciones.cloneNode(true);
            btnSiguienteProtecciones.parentNode.replaceChild(newBtn, btnSiguienteProtecciones);
            newBtn.addEventListener('click', (e) => { e.preventDefault(); e.stopPropagation(); desbloquearCliente(); });
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
                if (flujoCompletado) desbloqueada = true;
                else {
                    if (seccionKey === 'categoria') desbloqueada = categoriaDesbloqueada;
                    else if (seccionKey === 'adicionales') desbloqueada = adicionalesDesbloqueada;
                    else if (seccionKey === 'protecciones') desbloqueada = proteccionesDesbloqueada;
                    else if (seccionKey === 'cliente') desbloqueada = clienteDesbloqueada;
                }
                if (!desbloqueada) {
                    e.preventDefault();
                    e.stopPropagation();
                    let mensaje = '';
                    if (seccionKey === 'categoria') mensaje = '⚠️ Primero completa la ubicación y haz clic en BUSCAR';
                    else if (seccionKey === 'adicionales') mensaje = '⚠️ Primero selecciona una categoría de vehículo';
                    else if (seccionKey === 'protecciones') mensaje = '⚠️ Primero revisa los adicionales (puedes saltarlos)';
                    else if (seccionKey === 'cliente') mensaje = '⚠️ Primero revisa las protecciones (puedes saltarlas)';
                    return;
                }
                const body = seccion.querySelector('.stack-body'), indicator = seccion.querySelector('.stack-indicator');
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

    function observarClienteCompleto() { setInterval(() => { const nombre = document.getElementById('nombre_cliente')?.value?.trim(), apellidos = document.getElementById('apellidos_cliente')?.value?.trim(), email = document.getElementById('email_cliente')?.value?.trim(), telefono = document.getElementById('telefono_ui')?.value?.trim(); const clienteCompleto = nombre && apellidos && email && telefono; if (clienteCompleto && !flujoCompletado && clienteDesbloqueada) completarFlujo(); }, 1000); }

    function validarCamposUbicacion() {
        let todoOk = true;
        const sucursalRetiro = document.getElementById('sucursal_retiro');
        if (sucursalRetiro) {
            const valRetiro = (typeof $ !== 'undefined' && $(sucursalRetiro).data('select2')) ? $(sucursalRetiro).val() : sucursalRetiro.value;
            if (!valRetiro || valRetiro === "") { mostrarError(sucursalRetiro, 'Ubicación Requerida'); todoOk = false; } else { mostrarExito(sucursalRetiro); }
        }
        const checkbox = document.getElementById('differentDropoffAdmin'), isDifferentDropoff = checkbox && checkbox.checked, sucursalEntrega = document.getElementById('sucursal_entrega');
        if (isDifferentDropoff) {
            if (sucursalEntrega) {
                const valEntrega = (typeof $ !== 'undefined' && $(sucursalEntrega).data('select2')) ? $(sucursalEntrega).val() : sucursalEntrega.value;
                if (!valEntrega || valEntrega === "") { mostrarError(sucursalEntrega, 'Ubicación Requerida'); todoOk = false; } else { mostrarExito(sucursalEntrega); }
            }
        } else if (sucursalEntrega) { limpiarError(sucursalEntrega); mostrarExito(sucursalEntrega); }
        const fechaInicio = document.getElementById('fecha_inicio_ui');
        if (fechaInicio) { if (!fechaInicio.value || fechaInicio.value.trim() === "") { mostrarError(fechaInicio, 'Fecha Requerida'); todoOk = false; } else { mostrarExito(fechaInicio); } }
        const fechaFin = document.getElementById('fecha_fin_ui');
        if (fechaFin) { if (!fechaFin.value || fechaFin.value.trim() === "") { mostrarError(fechaFin, 'Fecha Requerida'); todoOk = false; } else { mostrarExito(fechaFin); } }
        const horaRetiro = document.getElementById('hora_retiro_ui');
        if (horaRetiro) {
            const timeContainer = horaRetiro.closest('.time-field-admin');
            let horaValida = false;
            if (timeContainer) { const selectHora = timeContainer.querySelector('.tp-hour'); if (selectHora && selectHora.value && selectHora.value !== "") horaValida = true; }
            if (!horaValida) { mostrarError(horaRetiro, 'Hora Requerida'); todoOk = false; } else { mostrarExito(horaRetiro); }
        }
        const horaEntrega = document.getElementById('hora_entrega_ui');
        if (horaEntrega) {
            const timeContainer = horaEntrega.closest('.time-field-admin');
            let horaValida = false;
            if (timeContainer) { const selectHora = timeContainer.querySelector('.tp-hour'); if (selectHora && selectHora.value && selectHora.value !== "") horaValida = true; }
            if (!horaValida) { mostrarError(horaEntrega, 'Hora Requerida'); todoOk = false; } else { mostrarExito(horaEntrega); }
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
            const horaSelect = document.querySelector('#hora_entrega_ui')?.closest('.dt-field-admin')?.querySelector('.tp-hour');
            if (horaSelect && horaSelect.value) {
                mostrarExito(horaEntregaUI);
            }
        }
        return true;
    }

    const horaRetiroSelect = document.querySelector('#hora_retiro_ui')?.closest('.dt-field-admin')?.querySelector('.tp-hour');
    const horaEntregaSelect = document.querySelector('#hora_entrega_ui')?.closest('.dt-field-admin')?.querySelector('.tp-hour');

    if (!horaRetiroSelect || !horaEntregaSelect) return true;

    const horaRetiro = horaRetiroSelect.value;
    const horaEntrega = horaEntregaSelect.value;

    if (!horaRetiro || !horaEntrega) return true;

    const horaEntregaUI = document.getElementById("hora_entrega_ui");

    if (parseInt(horaEntrega) <= parseInt(horaRetiro)) {
        if (horaEntregaUI) {
            horaEntregaUI.classList.add('field-error');
            horaEntregaUI.classList.remove('field-success');
            agregarMensajeErrorUnico(horaEntregaUI, 'La hora de devolución debe ser mayor');
        }
        if (horaEntregaSelect) {
            horaEntregaSelect.classList.add('field-error');
            horaEntregaSelect.classList.remove('field-success');
        }
        return false;
    }

    if (horaEntregaUI) {
        limpiarError(horaEntregaUI);
        horaEntregaUI.classList.add('field-success');
    }
    if (horaEntregaSelect) {
        limpiarError(horaEntregaSelect);
        horaEntregaSelect.classList.add('field-success');
    }

    return true;
}

// =========================================
// FUNCIONES PARA DESBLOQUEAR SIN EXPANDIR
// =========================================

function desbloquearProteccionesSinExpandir() {
    if (proteccionesDesbloqueada) return;
    proteccionesDesbloqueada = true;
    actualizarTodasSecciones();
    console.log("🔓 Protecciones desbloqueadas (sin expandir)");
}

function desbloquearClienteSinExpandir() {
    if (clienteDesbloqueada) return;
    clienteDesbloqueada = true;
    actualizarTodasSecciones();
    console.log("🔓 Cliente desbloqueado (sin expandir)");
}

// =========================================
// 27 EDITAR TARIFA DESDE VISTA PREVIA
// =========================================

    function initEditarCategoriaPreview() {
        const btnEditar = document.getElementById('btnEditarCategoriaPreview');
        const container = document.getElementById('catMiniRate');
        if (!btnEditar || !container) return;
        const newBtn = btnEditar.cloneNode(true);
        btnEditar.parentNode.replaceChild(newBtn, btnEditar);
        newBtn.addEventListener('click', (e) => {
            e.stopPropagation();
            if (!window.state?.categoria) { return; }
            if (container.querySelector('input')) return;
            const precioActual = parseFloat(window.state.categoria.precio_dia || 0);
            const input = document.createElement('input');
            input.type = 'number';
            input.value = precioActual.toFixed(2);
            input.min = 0;
            input.step = 0.01;
            Object.assign(input.style, { width: '100px', padding: '4px 8px', border: '1px solid #2563eb', borderRadius: '8px', fontWeight: '600', fontSize: '14px', color: '#333', outline: 'none' });
            container.textContent = '';
            container.appendChild(input);
            input.focus();
            input.select();
            const guardar = () => {
                let nuevoValor = parseFloat(input.value);
                if (isNaN(nuevoValor) || nuevoValor < 0) nuevoValor = precioActual;
                window.state.categoria.precio_dia = nuevoValor;
                container.textContent = `${money(nuevoValor).replace(" MXN", "")} MXN / día`;
                const sub = document.getElementById('catSelSub');
                if (sub) sub.textContent = `${money(nuevoValor)} / día · ${window.state.days || 0} día(s)`;
                if (window._cotizacionAPI) { window._cotizacionAPI.syncTotalsHidden(); window._cotizacionAPI.refreshSummary(); }
            };
            input.addEventListener('blur', guardar);
            input.addEventListener('keydown', (ev) => { if (ev.key === 'Enter') { ev.preventDefault(); input.blur(); } });
        });
    }

// =========================================
// 28 MODAL DE CARACTERÍSTICAS
// =========================================

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
        if (featuresModal && document.body.contains(featuresModal)) return featuresModal;
        const existingModal = document.getElementById('featuresModal');
        if (existingModal) existingModal.remove();
        featuresModal = document.createElement('div');
        featuresModal.id = 'featuresModal';
        featuresModal.className = 'modal-features';
        featuresModal.style.cssText = 'position:fixed;top:0;left:0;width:100%;height:100%;background:rgba(0,0,0,0.65);display:none;align-items:center;justify-content:center;z-index:10001;backdrop-filter:blur(2px);';
        featuresModal.innerHTML = `<div class="modal-box" style="background:white;width:min(500px,90vw);border-radius:24px;overflow:hidden;box-shadow:0 30px 70px rgba(0,0,0,0.3);animation:modalFadeIn 0.2s ease;"><div class="header" style="background:linear-gradient(180deg, var(--brand), var(--brand-dark)) !important;border-bottom:1px solid rgba(227,0,0,0.3);color:white;padding:18px 20px;display:flex;justify-content:space-between;align-items:center;"><h3 style="margin:0;font-size:20px;font-weight:900;display:flex;align-items:center;gap:10px;color:white !important;"><i class='bx bx-car' style="color:white !important;"></i><span id="featuresCatName" style="color:white !important;">Características</span></h3><button id="closeFeaturesModalBtn" style="background:rgba(255,255,255,0.2);border:none;color:white;width:36px;height:36px;border-radius:50%;cursor:pointer;font-size:18px;display:flex;align-items:center;justify-content:center;transition:all 0.2s ease;">✖</button></div><div class="body" style="padding:20px;max-height:60vh;overflow-y:auto;"><div class="features-list" id="featuresListContainer" style="display:flex;flex-direction:column;gap:12px;"></div></div></div>`;
        document.body.appendChild(featuresModal);
        document.getElementById('closeFeaturesModalBtn')?.addEventListener('click', (e) => { e.preventDefault(); e.stopPropagation(); closeFeaturesModal(); });
        featuresModal.addEventListener('click', (e) => { if (e.target === featuresModal) closeFeaturesModal(); });
        document.addEventListener('keydown', (e) => { if (e.key === 'Escape' && featuresModal && featuresModal.style.display === 'flex') closeFeaturesModal(); });
        return featuresModal;
    }
    function openFeaturesModal(catName, features) {
        const modal = initFeaturesModal();
        const nameSpan = document.getElementById('featuresCatName');
        const container = document.getElementById('featuresListContainer');
        if (nameSpan) nameSpan.textContent = catName || 'Características';
        if (container && features && Array.isArray(features)) {
            container.innerHTML = '';
            features.forEach(f => {
                const item = document.createElement('div');
                item.className = 'feature-item';
                item.setAttribute('style', 'display:flex;align-items:center;gap:14px;padding:12px 16px;background:#f8fafc;border-radius:14px;border:1px solid #e2e8f0;transition:all 0.2s;');
                item.innerHTML = `<i class="${f.icon || 'bx bx-check'}" style="font-size:24px;width:32px;text-align:center;"></i><span class="feature-text" style="font-weight:700;color:#1e293b;font-size:15px;">${escapeHtml(f.text)}</span>`;
                container.appendChild(item);
            });
        }
        modal.style.display = 'flex';
    }
    document.addEventListener('click', function(e) {
        const infoBtn = e.target.closest('.info-categoria-btn');

        if (infoBtn) {
            e.preventDefault();
            e.stopPropagation();
            e.stopImmediatePropagation();
            const card = infoBtn.closest('.card-pick');
            if (card) {
                const catName = card.querySelector('.cp-title')?.textContent || 'Categoría';
                let features = [];
                const featuresData = card.dataset.caracteristicas;
                if (featuresData) {
                    try {
                        features = JSON.parse(featuresData);
                    } catch(err) {
                        console.error('Error parsing features:', err);
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
            return;
        }


        const cardClick = e.target.closest('.card-pick');
        if (cardClick && (e.target.classList.contains('info-categoria-btn') || e.target.closest('.info-categoria-btn'))) {
            e.preventDefault();
            e.stopPropagation();
            return;
        }
    }, true);

// =========================================
// 29 INICIALIZACIÓN (BOOT)
// =========================================

    function init() {
        console.log('🚀 Cotizaciones - Sistema iniciado...');
        configurarBotonPrincipal();
        configurarBotonesSiguiente();
        configurarClicEncabezados();
        observarCategoria();
        observarClienteCompleto();

        setTimeout(() => {
            if (typeof initTimeValidation === 'function') initTimeValidation();
            if (typeof initDateValidation === 'function') initDateValidation();

            if (typeof initValidacionHorasTiempoReal === 'function') {
                initValidacionHorasTiempoReal();
                console.log('✅ Validación de horas en misma fecha inicializada');
            }

            document.querySelectorAll('.tp-hour').forEach(select => {
                if (select.value && select.value !== "") {
                    const inputHoraUI = select.closest('.dt-field-admin, .time-field-admin')?.querySelector('.input-buscador-admin');
                    if (inputHoraUI) mostrarExito(inputHoraUI);
                    mostrarExito(select);
                }
            });
            ['#fecha_inicio_ui', '#fecha_fin_ui'].forEach(selector => {
                const input = document.querySelector(selector);
                if (input && input.value && input.value.trim() !== "") mostrarExito(input);
            });
        }, 100);

        if (window.state && window.state.categoria) {
            adicionalesDesbloqueada = true;
            categoriaDesbloqueada = true;
            actualizarTodasSecciones();
        }


        if (typeof inicializarEstadoProteccionesIndividuales === 'function') {
            inicializarEstadoProteccionesIndividuales();
            console.log('🛡️ Protecciones por defecto inicializadas en el State externo');
        }

        setTimeout(() => { initEditarCategoriaPreview(); }, 500);
        console.log('✅ Sistema listo');
    }

// =========================================
// 30 OBSERVADOR PARA EDITAR TARIFA
// =========================================

    const observerPreview = new MutationObserver(() => {
        const btn = document.getElementById('btnEditarCategoriaPreview');
        if (btn && !btn.hasAttribute('data-initialized')) {
            btn.setAttribute('data-initialized', 'true');
            initEditarCategoriaPreview();
        }
    });
    observerPreview.observe(document.body, { childList: true, subtree: true });

// =========================================
// 31 NAVBAR COTIZACIONES CON PASTILLA A LA DERECHA
// =========================================

(function() {
    function crearNavbarCotizaciones() {
        if (document.getElementById('resNavbar')) return;

        const navbarHTML = `<div class="res-navbar" id="resNavbar">
            <div class="container-res">
                <div class="nav-content-wrapper">
                    <div class="left-group">
                        <a href="/" class="logo">VIAJERO</a>
                        <span class="page-title">Nueva cotización</span>
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
        </div>`;

        document.body.insertAdjacentHTML('afterbegin', navbarHTML);

        const btnResumenOriginal = document.getElementById('btnResumen');
        document.getElementById('btnResumenNav')?.addEventListener('click', () => btnResumenOriginal && btnResumenOriginal.click());

        document.getElementById('btnSalirNav')?.addEventListener('click', () => window.location.href = '/ventas/menu');

        sincronizarPastillaDias();
    }

    function sincronizarPastillaDias() {
        const diasNavbar = document.getElementById('diasNavbarCount');
        const diasOriginal = document.getElementById('diasTxt');

        if (diasNavbar && diasOriginal) {
            diasNavbar.textContent = diasOriginal.textContent || '0';

            const observer = new MutationObserver(function(mutations) {
                mutations.forEach(function(mutation) {
                    if (mutation.type === 'characterData' || mutation.type === 'childList') {
                        diasNavbar.textContent = diasOriginal.textContent || '0';
                    }
                });
            });
            observer.observe(diasOriginal, { childList: true, characterData: true, subtree: true });

            document.addEventListener('diasActualizados', function(e) {
                diasNavbar.textContent = e.detail?.dias || diasOriginal.textContent || '0';
            });
        }
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
        document.body.style.paddingTop = "100px";

        const daysPillOriginal = document.querySelector('.days-pill-admin:not(#daysPillNavbar)');
        if (daysPillOriginal) {
            daysPillOriginal.classList.add('original-pill');
        }
    }

    document.addEventListener('DOMContentLoaded', () => {
        crearNavbarCotizaciones();
        initScrollEffect();
        prepararPagina();
    });
})();

// =========================================
// 32 BOOT FINAL
// =========================================

    document.addEventListener("DOMContentLoaded", () => {
        ensureTotalsHidden();
        syncProteccionHidden();
        syncServiciosHidden();
        state.servicios.dropoff = String(qs("#svc_dropoff")?.value || "0") === "1";
        state.servicios.gasolina = String(qs("#svc_gasolina")?.value || "0") === "1";
        const dropT = qs("#dropoffToggle"); if (dropT) dropT.checked = state.servicios.dropoff;
        const gasT = qs("#gasolinaToggle"); if (gasT) gasT.checked = state.servicios.gasolina;
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
        setTimeout(() => { initSelect2EnAdicionales(); }, 500);
        initDifferentDropoff();
        setTimeout(init, 500);
        setTimeout(() => { initAdicionalesCarousel(); }, 300);

        setTimeout(() => {
            if (typeof preseleccionarProteccionesIndividuales === 'function') {
                preseleccionarProteccionesIndividuales();
                console.log("🎯 Preselección de protecciones ejecutada en COTIZACIONES");
            }
        }, 800);
    });

    $(document).ready(function() {
        setTimeout(initSelect2Sucursales, 300);
    });

// =========================================
// 33 CARRUSEL DE ADICIONALES
// =========================================

    function initAdicionalesCarousel() {
        const track = document.getElementById('adicionalesTrack');
        const container = document.querySelector('.carousel-container');
        const prevBtn = document.querySelector('.adicionales-carousel .carousel-arrow.prev');
        const nextBtn = document.querySelector('.adicionales-carousel .carousel-arrow.next');

        if (!track || !container) {
            console.warn('⚠️ No se encontró el carrusel de adicionales');
            return;
        }

        let isDragging = false;
        let startX = 0;
        let scrollLeft = 0;

        function moveCarousel(direction) {
            const containerWidth = container.clientWidth;
            const items = Array.from(track.children);
            if (items.length === 0) return;

            const currentScroll = container.scrollLeft;
            let targetIndex = -1;
            let minDistance = Infinity;

            items.forEach((item, index) => {
                const itemCenter = item.offsetLeft + (item.clientWidth / 2);
                const distance = Math.abs(currentScroll + (containerWidth / 2) - itemCenter);
                if (distance < minDistance) {
                    minDistance = distance;
                    targetIndex = index;
                }
            });

            if (targetIndex === -1) return;
            let newIndex = direction === 'next' ? targetIndex + 1 : targetIndex - 1;
            if (newIndex < 0) newIndex = 0;
            if (newIndex >= items.length) newIndex = items.length - 1;
            if (newIndex === targetIndex) return;

            const targetItem = items[newIndex];
            container.scrollTo({
                left: targetItem.offsetLeft - (containerWidth / 2) + (targetItem.clientWidth / 2),
                behavior: 'smooth'
            });
        }

        if (prevBtn) {
            const newPrevBtn = prevBtn.cloneNode(true);
            prevBtn.parentNode.replaceChild(newPrevBtn, prevBtn);
            newPrevBtn.addEventListener('click', (e) => {
                e.preventDefault();
                e.stopPropagation();
                moveCarousel('prev');
            });
        }

        if (nextBtn) {
            const newNextBtn = nextBtn.cloneNode(true);
            nextBtn.parentNode.replaceChild(newNextBtn, nextBtn);
            newNextBtn.addEventListener('click', (e) => {
                e.preventDefault();
                e.stopPropagation();
                moveCarousel('next');
            });
        }

        container.addEventListener('mousedown', (e) => {
            isDragging = true;
            startX = e.pageX - container.offsetLeft;
            scrollLeft = container.scrollLeft;
            container.style.cursor = 'grabbing';
            container.style.userSelect = 'none';
            e.preventDefault();
        });

        container.addEventListener('mousemove', (e) => {
            if (!isDragging) return;
            e.preventDefault();
            const x = e.pageX - container.offsetLeft;
            const walk = (x - startX) * 1.5;
            container.scrollLeft = scrollLeft - walk;
        });

        container.addEventListener('mouseup', () => {
            isDragging = false;
            container.style.cursor = 'grab';
            container.style.userSelect = '';
        });

        container.addEventListener('mouseleave', () => {
            if (isDragging) {
                isDragging = false;
                container.style.cursor = 'grab';
                container.style.userSelect = '';
            }
        });

        container.addEventListener('touchstart', (e) => {
            isDragging = true;
            startX = e.touches[0].pageX - container.offsetLeft;
            scrollLeft = container.scrollLeft;
            container.style.cursor = 'grabbing';
            e.preventDefault();
        });

        container.addEventListener('touchmove', (e) => {
            if (!isDragging) return;
            e.preventDefault();
            const x = e.touches[0].pageX - container.offsetLeft;
            const walk = (x - startX) * 1.5;
            container.scrollLeft = scrollLeft - walk;
        });

        container.addEventListener('touchend', () => {
            isDragging = false;
            container.style.cursor = 'grab';
        });

        container.style.cursor = 'grab';
        container.style.overflowX = 'auto';
        container.style.overflowY = 'hidden';

        if (container.scrollLeft === 0) {
            container.scrollLeft = 0;
        }

        console.log('✅ Carrusel de adicionales inicializado con navegación y soporte táctil');
    }

    window.initAdicionalesCarousel = initAdicionalesCarousel;

// =========================================
// 34 EXPONER API GLOBAL
// =========================================

    window._cotizacionAPI = {
        setGasolinaActive, setDropoffActive, setDeliveryActive, setAddonQty,
        setProteccion, setCategoria, syncTotalsHidden, refreshSummary,
        forceRecalc, getState: () => state, loadAddons
    };

// =========================================
// 35 CARGA DE EDICIÓN (COTIZACIÓN EXISTENTE)
// =========================================

    window.addEventListener("DOMContentLoaded", async () => {
        const API = window._cotizacionAPI;
        if (!window.cotizacionEditar || !API) { console.warn("⚠️ No hay cotización o API"); return; }
        const r = window.cotizacionEditar;
        console.log("🟢 EDITANDO COTIZACIÓN:", r);
        if (r.id_categoria) {
            const card = document.querySelector(`.card-pick[data-id="${r.id_categoria}"]`);
            if (card) {
                const categoria = { id: card.dataset.id, nombre: card.dataset.nombre, desc: card.dataset.desc, precio_dia: parseFloat(card.dataset.precio || 0), precio_km: parseFloat(card.dataset.precioKm || 0), capacidad_tanque: parseFloat(card.dataset.litros || 0) };
                API.setCategoria(categoria);
                await new Promise(res => setTimeout(res, 150));
            }
        }
        if (r.delivery_activo == 1) {
            API.setDeliveryActive(true);
            await new Promise(res => setTimeout(res, 150));
            const sel = document.getElementById("deliveryUbicacion"), km = document.getElementById("deliveryKm"), dir = document.getElementById("deliveryDireccion");
            if (sel && r.delivery_ubicacion != null) { sel.value = String(r.delivery_ubicacion); sel.dispatchEvent(new Event("change")); }
            if (km && r.delivery_km) { km.value = r.delivery_km; km.dispatchEvent(new Event("input")); }
            if (dir && r.delivery_direccion) dir.value = r.delivery_direccion;
        }
        if (window.serviciosEditar?.length) {
            for (const s of window.serviciosEditar) {
                if (s.id_servicio == 1) API.setGasolinaActive(true);
                if (s.id_servicio == 11) {
                    API.setDropoffActive(true);
                    await new Promise(res => setTimeout(res, 150));
                    const dSel = document.getElementById("deliveryUbicacion"), dKm = document.getElementById("deliveryKm"), dDir = document.getElementById("deliveryDireccion");
                    const sel = document.getElementById("dropUbicacion"), km = document.getElementById("dropKm"), dir = document.getElementById("dropDireccion");
                    if (dSel && sel) { sel.value = dSel.value; sel.dispatchEvent(new Event("change")); }
                    if (dKm && km) { km.value = dKm.value; km.dispatchEvent(new Event("input")); }
                    if (dDir && dir) dir.value = dDir.value;
                }
                if (s.id_servicio == 12) API.setDeliveryActive(true);
                if (![1, 11, 12].includes(s.id_servicio)) {
                    const item = { id: s.id_servicio, nombre: s.nombre, precio: s.precio_unitario, charge: "por_evento" };
                    API.setAddonQty(item, s.cantidad || 1);
                }
            }
        }
        if (window.seguroEditar) {
            API.setProteccion({ id: window.seguroEditar.id_paquete, nombre: window.seguroEditar.nombre, precio: window.seguroEditar.precio_por_dia, charge: "por_dia" });
        }
        API.forceRecalc();
        console.log("✅ EDICIÓN CARGADA COMPLETA");
    });

})();

function closePop(popId) { const pop = document.getElementById(popId); if (pop) pop.style.display = "none"; }

function seleccionarCategoriaCotizacion(cardElement) {
    if (!cardElement) return;
    const id = cardElement.dataset.id;
    const nombre = cardElement.dataset.nombre || "";
    const desc = cardElement.dataset.desc || "";
    const precio = Number(cardElement.dataset.precio || 0);
    const precioKm = Number(cardElement.dataset.precioKm || 0);
    const img = cardElement.dataset.img || "";
    const capacidad = parseFloat(cardElement.dataset.litros || 0);

    if (window._cotizacionAPI && window._cotizacionAPI.setCategoria) {
        window._cotizacionAPI.setCategoria({ id, nombre, desc, precio_dia: precio, precio_km: precioKm, img, capacidad_tanque: capacidad });
    }

    const catPop = document.getElementById('catPop');
    if (catPop) catPop.style.display = 'none';

    setTimeout(() => {
        if (typeof desbloquearAdicionales === 'function') {
            desbloquearAdicionales();
        }
        if (typeof desbloquearProteccionesSinExpandir === 'function') {
            desbloquearProteccionesSinExpandir();
        }
        if (typeof desbloquearClienteSinExpandir === 'function') {
            desbloquearClienteSinExpandir();
        }
    }, 200);
}

 //Tooltip para adicionales
(function() {
  "use strict";

  const textosTooltip = {
    'Conductor adicional': 'Agregar un conductor extra.',
    'Gasolina prepago': 'Tanque completo preferencial.',
    'Drop Off': 'Entrega en sucursal distinta.',
    'Delivery': 'Entrega a domicilio.',
    'Silla de bebé': 'Silla de seguridad para bebé.'
  };

  let activeTooltip = null;
  let tooltipTimeout = null;
  let isMobile = /Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent);

  function showTooltip(element, text) {
    if (activeTooltip) {
      activeTooltip.remove();
      activeTooltip = null;
    }
    if (tooltipTimeout) clearTimeout(tooltipTimeout);

    const tooltip = document.createElement('div');
    tooltip.className = 'svc-tooltip';
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
      if (activeTooltip) {
        activeTooltip.remove();
        activeTooltip = null;
      }
    }, 150);
  }

  function hideTooltipImmediately() {
    if (tooltipTimeout) clearTimeout(tooltipTimeout);
    if (activeTooltip) {
      activeTooltip.remove();
      activeTooltip = null;
    }
  }

  function initTooltips() {
    document.querySelectorAll('.svc-card .svc-ico').forEach(icon => {
      if (icon._tooltipInitialized) return;
      icon._tooltipInitialized = true;

      const card = icon.closest('.svc-card');
      const nombre = card?.querySelector('.svc-name')?.textContent || '';
      const texto = textosTooltip[nombre] || 'Sin información disponible';

      if (!isMobile) {
        icon.addEventListener('mouseenter', (e) => {
          e.stopPropagation();
          showTooltip(icon, texto);
        });
        icon.addEventListener('mouseleave', () => {
          hideTooltip();
        });
      }

      icon.addEventListener('click', (e) => {
        e.stopPropagation();
        if (isMobile) {
          if (activeTooltip && activeTooltip.parentNode) {
            hideTooltipImmediately();
          } else {
            showTooltip(icon, texto);
            setTimeout(() => hideTooltipImmediately(), 3000);
          }
        }
      });
    });
  }

  window.addEventListener('scroll', hideTooltipImmediately);
  window.addEventListener('resize', hideTooltipImmediately);

  function init() {
    initTooltips();
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', init);
  } else {
    init();
  }

  const observer = new MutationObserver(() => {
    initTooltips();
  });

  const track = document.getElementById('adicionalesTrack');
  if (track) {
    observer.observe(track, { childList: true, subtree: true });
  }
})();

// =========================================
// FIX: PREVENIR SCROLL EN AUTOCOMPLETADO DE CLIENTE
// =========================================
(function() {
    "use strict";

    let posicionClienteGuardada = null;
    let autocompletando = false;

    function guardarPosicionCliente() {
        const seccionCliente = document.querySelector('.acordeon-item[data-seccion="cliente"]');
        if (seccionCliente) {
            const rect = seccionCliente.getBoundingClientRect();
            posicionClienteGuardada = rect.top + window.scrollY;
            console.log('📌 Posición de cliente guardada:', posicionClienteGuardada);
        }
    }

    function restaurarPosicionCliente() {
        if (autocompletando && posicionClienteGuardada !== null) {
            const currentScroll = window.scrollY;
            const difference = Math.abs(currentScroll - posicionClienteGuardada);

            if (difference > 20) {
                window.scrollTo({
                    top: posicionClienteGuardada,
                    behavior: 'instant'
                });
                console.log('🔄 Scroll restaurado a posición de cliente');
            }
            autocompletando = false;
        }
    }

    const inputsCliente = [
        'nombre_cliente',
        'apellidos_cliente',
        'email_cliente',
        'telefono_ui',
        'no_vuelo'
    ];

    inputsCliente.forEach(inputId => {
        const input = document.getElementById(inputId);
        if (!input) return;

        input.addEventListener('focus', function(e) {
            guardarPosicionCliente();
        });

        input.addEventListener('animationstart', function(e) {
            if (e.animationName === 'onAutoFillStart') {
                autocompletando = true;
                guardarPosicionCliente();
                setTimeout(restaurarPosicionCliente, 5);
                setTimeout(restaurarPosicionCliente, 25);
                setTimeout(restaurarPosicionCliente, 50);
                setTimeout(restaurarPosicionCliente, 100);
                setTimeout(restaurarPosicionCliente, 200);
            }
        });

        input.addEventListener('input', function(e) {
            if (input.value && input.value.length > 3 && !autocompletando) {

                setTimeout(() => {
                    const currentScroll = window.scrollY;
                    if (posicionClienteGuardada && Math.abs(currentScroll - posicionClienteGuardada) > 30) {
                        window.scrollTo({ top: posicionClienteGuardada, behavior: 'instant' });
                        console.log('🔄 Scroll corregido después de input');
                    }
                }, 10);
            }
        });

        input.addEventListener('change', function() {
            setTimeout(restaurarPosicionCliente, 10);
        });
    });

    const telefonoSelect = document.getElementById('phone_toggle');
    if (telefonoSelect) {
        telefonoSelect.addEventListener('click', function() {
            guardarPosicionCliente();
            setTimeout(() => {
                if (posicionClienteGuardada && Math.abs(window.scrollY - posicionClienteGuardada) > 20) {
                    window.scrollTo({ top: posicionClienteGuardada, behavior: 'instant' });
                }
            }, 50);
        });
    }

    const observer = new MutationObserver(function(mutations) {
        if (autocompletando) {
            restaurarPosicionCliente();
        }
    });

    observer.observe(document.body, {
        childList: true,
        subtree: true,
        attributes: true,
        attributeFilter: ['class', 'style']
    });

    console.log('✅ Fix para autocompletado de cliente activado');
})();

// =========================================
// 42.5 RESUMEN CARRITO PARA PROTECCIONES
// =========================================
(function() {
    "use strict";

    let cartHeaderBtn = null;
    let intervalId = null;
    let actualizando = false;
    let ultimaActualizacion = 0;
    const MIN_INTERVALO_MS = 200;

    function tieneProteccionSeleccionada() {
        if (typeof state === 'undefined') return false;

        if (state.proteccion !== null) return true;
        if (state.individuales && state.individuales.size > 0) return true;
        return false;
    }

    function obtenerTotalGeneral() {
        if (typeof calcTotals === 'function') {
            const totals = calcTotals();
            return totals.total || 0;
        }

        const totalEstiloElement = document.getElementById('resTotalEstilo');
        if (totalEstiloElement && totalEstiloElement.textContent !== '—') {
            const totalNumero = parseFloat(totalEstiloElement.textContent.replace(/[^0-9.-]/g, ''));
            if (!isNaN(totalNumero)) return totalNumero;
        }

        const totalElement = document.getElementById('resTotal');
        if (totalElement && totalElement.textContent !== '—') {
            const totalNumero = parseFloat(totalElement.textContent.replace(/[^0-9.-]/g, ''));
            if (!isNaN(totalNumero)) return totalNumero;
        }

        return 0;
    }

    function contarProteccionesSeleccionadas() {
        if (typeof state === 'undefined') return 0;

        let count = 0;
        if (state.proteccion !== null) count++;
        if (state.individuales) count += state.individuales.size;
        return count;
    }

    function crearOCarritoEnModal() {
        const modalHeader = document.querySelector('#proteccionPop .modal-head');
        if (!modalHeader) return false;

        if (document.getElementById('cartHeaderBtn')) {
            cartHeaderBtn = document.getElementById('cartHeaderBtn');
            return true;
        }

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

        console.log('✅ Botón de carrito creado en el modal de cotizaciones');
        return true;
    }

    function actualizarCarritoEnModal() {
        const ahora = Date.now();
        if (actualizando) return;
        if (ahora - ultimaActualizacion < MIN_INTERVALO_MS) return;

        actualizando = true;
        ultimaActualizacion = ahora;

        try {
            if (!cartHeaderBtn) return;

            const tieneSeleccion = tieneProteccionSeleccionada();

            const totalGeneral = obtenerTotalGeneral();
            const count = contarProteccionesSeleccionadas();

            const totalSpan = cartHeaderBtn.querySelector('.cart-header-total');
            const badge = cartHeaderBtn.querySelector('.cart-header-badge');

            const totalAnterior = totalSpan?.dataset.total || '0';
            const countAnterior = badge?.dataset.count || '0';
            const nuevoTotal = totalGeneral.toFixed(2);
            const nuevoCount = String(count);

            if (totalAnterior !== nuevoTotal || countAnterior !== nuevoCount || cartHeaderBtn.style.display !== (tieneSeleccion ? 'inline-flex' : 'none')) {

                cartHeaderBtn.style.display = tieneSeleccion ? 'inline-flex' : 'none';

                if (tieneSeleccion) {
                    if (totalSpan) {
                        totalSpan.dataset.total = nuevoTotal;
                        totalSpan.textContent = `$${totalGeneral.toLocaleString("es-MX", {
                            minimumFractionDigits: 2,
                            maximumFractionDigits: 2
                        })} MXN`;
                    }

                    if (badge) {
                        badge.dataset.count = nuevoCount;
                        badge.textContent = count;
                        badge.style.display = count > 0 ? 'inline-flex' : 'none';
                    }
                }
            }
        } finally {
            setTimeout(() => {
                actualizando = false;
            }, 50);
        }
    }

    function observarCambiosEnProtecciones() {
        let timeoutId = null;

        const observer = new MutationObserver(() => {
            if (timeoutId) clearTimeout(timeoutId);
            timeoutId = setTimeout(() => {
                actualizarCarritoEnModal();
                timeoutId = null;
            }, 300);
        });

        const track = document.getElementById('protePacksTrack');
        if (track) {
            observer.observe(track, {
                childList: true,
                subtree: true,
                attributes: true,
                attributeFilter: ['class', 'data-selected']
            });
        }

        const individualesContainer = document.getElementById('tab-individuales');
        if (individualesContainer) {
            observer.observe(individualesContainer, {
                childList: true,
                subtree: true,
                attributes: true,
                attributeFilter: ['class', 'data-selected']
            });
        }

        return observer;
    }

    function escucharClicksEnProtecciones() {
        let timeoutId = null;

        document.addEventListener('click', (e) => {
            const btnPaquete = e.target.closest('.btn-proteccion-dividido');
            const switchIndividual = e.target.closest('.switch-individual');
            const cardIndividual = e.target.closest('.individual-item');

            if (btnPaquete || switchIndividual || cardIndividual) {
                if (timeoutId) clearTimeout(timeoutId);
                timeoutId = setTimeout(() => {
                    actualizarCarritoEnModal();
                    timeoutId = null;
                }, 200);
            }
        });
    }

    function init() {
        const checkModal = setInterval(() => {
            const modal = document.getElementById('proteccionPop');
            if (modal) {
                clearInterval(checkModal);
                crearOCarritoEnModal();

                const observer = observarCambiosEnProtecciones();
                escucharClicksEnProtecciones();

                if (intervalId) clearInterval(intervalId);
                intervalId = setInterval(actualizarCarritoEnModal, 2000);

                setTimeout(actualizarCarritoEnModal, 100);

                console.log('✅ Carrito de protecciones inicializado en COTIZACIONES');
            }
        }, 100);
    }

    window.addEventListener('beforeunload', function() {
        if (intervalId) {
            clearInterval(intervalId);
            intervalId = null;
        }
    });

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }

})();

// =========================================================
// MAESTRÍA DE SINCRONIZACIÓN DROPOFF
// =========================================================
(function() {
    console.log("🟢 Inicializando sincronización maestra (Formato completo y Querétaro flexible)...");

    let isSyncing = false;

    function limpiarParaSincro(txt) {
        if (!txt) return "";
        let limpio = txt.toLowerCase()
            .normalize("NFD").replace(/[\u0300-\u036f]/g, "");

        if (limpio.includes('-')) {
            limpio = limpio.split('-')[1];
        }
        if (limpio.includes('(')) {
            limpio = limpio.split('(')[0];
        }

        return limpio
            .replace(/[^a-z0-9 ]/g, "")
            .replace(/\b(de|la|los|del|el|internacional|oficina|aeropuerto|central|autobuses)\b/g, "")
            .replace(/\s+/g, " ")
            .trim();
    }

    function obtenerTextoCompletoSinKm(txt) {
        if (!txt) return "";
        let resultado = txt;
        if (resultado.includes('(')) {
            resultado = resultado.split('(')[0];
        }
        return resultado.trim();
    }

    function setupSync() {
        const sucursalEntrega = document.getElementById('sucursal_entrega');
        const dropoffToggle = document.getElementById('dropoffToggle');
        const dropoffSelect = document.getElementById('dropUbicacion');
        const checkbox = document.getElementById('differentDropoffAdmin');

        if (!sucursalEntrega || !dropoffToggle || !dropoffSelect || !checkbox) {
            setTimeout(setupSync, 300);
            return;
        }

        console.log("✅ Conexión bidireccional master enlazada con éxito.");

        $(sucursalEntrega).on('change', function() {
            if (isSyncing) return;
            if (!checkbox.checked || !dropoffToggle.checked) return;

            const textFormOriginal = sucursalEntrega.options[sucursalEntrega.selectedIndex]?.text || '';
            if (!textFormOriginal || textFormOriginal.includes('¿Dónde termina')) return;

            console.log("📢 Sincronizando: Formulario ➔ DropOff [" + textFormOriginal + "]");

            isSyncing = true;

            if (textFormOriginal.toLowerCase().includes('querétaro') || textFormOriginal.toLowerCase().includes('queretaro')) {
                $(dropoffSelect).val("").trigger('change.select2');
                if (typeof state !== 'undefined' && state.dropoff) {
                    state.dropoff.km = 0;
                    state.dropoff.costo = 0;
                }
                dropoffSelect.dispatchEvent(new Event('change', { bubbles: true }));
                isSyncing = false;
                return;
            }

            const txtFormLimpio = limpiarParaSincro(textFormOriginal);
            let encontrado = false;

            for (let i = 0; i < dropoffSelect.options.length; i++) {
                const optDrop = dropoffSelect.options[i];
                const txtDropLimpio = limpiarParaSincro(optDrop.text);

                if (txtDropLimpio.includes(txtFormLimpio) || txtFormLimpio.includes(txtDropLimpio)) {
                    if (dropoffSelect.value !== optDrop.value) {
                        $(dropoffSelect).val(optDrop.value).trigger('change.select2');
                        dropoffSelect.dispatchEvent(new Event('change', { bubbles: true }));
                    }
                    encontrado = true;
                    break;
                }
            }

            if (!encontrado) {
                $(dropoffSelect).val("").trigger('change.select2');
                dropoffSelect.dispatchEvent(new Event('change', { bubbles: true }));
            }

            setTimeout(() => { isSyncing = false; }, 50);
        });

        $(dropoffSelect).on('select2:select change', function(e) {
            if (isSyncing) return;
            if (!checkbox.checked || !dropoffSelect.value || dropoffSelect.value === "0") return;

            const textDropOriginal = dropoffSelect.options[dropoffSelect.selectedIndex]?.text || '';
            if (!textDropOriginal || textDropOriginal === 'Seleccione...') return;

            console.log("📢 Sincronizando: DropOff ➔ Formulario [" + textDropOriginal + "]");

            isSyncing = true;

            const txtDropLimpio = limpiarParaSincro(textDropOriginal);
            let encontrado = false;

            for (let i = 0; i < sucursalEntrega.options.length; i++) {
                const optForm = sucursalEntrega.options[i];
                const txtFormLimpio = limpiarParaSincro(optForm.text);

                if (txtFormLimpio.includes(txtDropLimpio) || txtDropLimpio.includes(txtFormLimpio)) {
                    if (sucursalEntrega.value !== optForm.value) {
                        $(sucursalEntrega).val(optForm.value).trigger('change');
                    }
                    encontrado = true;
                    break;
                }
            }

            if (!encontrado) {
                let nombreInyectar = obtenerTextoCompletoSinKm(textDropOriginal);

                let existeYa = Array.from(sucursalEntrega.options).find(o => o.text === nombreInyectar);

                if (!existeYa) {
                    const newOption = document.createElement('option');
                    newOption.value = 'dynamic_' + Date.now();
                    newOption.text = nombreInyectar;
                    sucursalEntrega.appendChild(newOption);
                    $(sucursalEntrega).val(newOption.value).trigger('change');
                } else {
                    $(sucursalEntrega).val(existeYa.value).trigger('change');
                }
                console.log("🆕 Opción completa inyectada dinámicamente arriba:", nombreInyectar);
            }

            setTimeout(() => { isSyncing = false; }, 50);
        });
    }

    setupSync();
})();
