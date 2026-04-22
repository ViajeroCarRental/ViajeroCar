(function () {
  "use strict";

// ============================================================
// 🚨 FUNCIÓN DE ALERTA CON SWEETALERT2 (CENTRADA - IGUAL COTIZACIONES)
// ============================================================
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

  // ============================================================
  // 📍 FUNCIONES DE VALIDACIÓN POR PASOS (IGUAL COTIZACIONES)
  // ============================================================
  function validarSucursales() {
    const sucRetiro = document.getElementById("sucursal_retiro")?.value;
    const sucEntrega = document.getElementById("sucursal_entrega")?.value;

    if (!sucRetiro || !sucEntrega) {
      mostrarToast('⚠️ Primero debes seleccionar las sucursales de RETIRO y ENTREGA', 'warning');
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
            mostrarToast('⚠️ Por ser AEROPUERTO, debes ingresar el número de VUELO', 'warning');
            return false;
        }
    }

    return true;
}


  // ============================================================
  // 1️⃣ HELPERS Y UTILIDADES
  // ============================================================
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

  // CSRF
  const getCsrf = () => {
    const meta = document.querySelector('meta[name="csrf-token"]');
    if (meta) return meta.getAttribute("content") || "";
    const tok = qs('#formReserva input[name="_token"]');
    return tok ? tok.value : "";
  };

  // Cierra todos los modales (para que sea SOLO confirmPop)
  const closeAllPops = () => {
    document.querySelectorAll(".pop.modal").forEach((m) => {
      m.style.display = "none";
    });
  };

  // ============================================================
  // 2️⃣ ESTADO GLOBAL
  // ============================================================
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

    // DROPOFF
    dropoff: {
      total: 0,
      km: 0,
      ubicacion: "",
      direccion: "",
      activo: false
    },

    // DELIVERY (estado interno)
    delivery: {
      total: 0,
      km: 0,
      ubicacion: "",
      direccion: "",
      activo: false
    },

    // GASOLINA
    gasolina: {
      total: 0,
      litros: 0,
      precioLitro: 20,
      activo: false
    }
  };

  // ============================================================
  // 3️⃣ HIDDEN INPUTS (BACKEND)
  // ============================================================
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
        input.name = `adicionalesSeleccionados[${i}][${k}]`;
        input.value = String(v ?? "");
        wrap.appendChild(input);
      });

      i++;
    });
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

  // ============================================================
  // 4️⃣ SERVICIOS (SWITCHES)
  // ============================================================
  function syncServiciosHidden() {
    ensureServiciosHidden();

    const d = qs("#svc_dropoff");
    const l = qs("#svc_delivery");
    const g = qs("#svc_gasolina");

    if (d) d.value = state.servicios.dropoff ? "1" : "0";
    if (l) l.value = state.servicios.delivery ? "1" : "0";
    if (g) g.value = state.servicios.gasolina ? "1" : "0";
  }

  // ============================================================
  // 5️⃣ DELIVERY (Switch + Campos + Total)
  // ============================================================
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

    const precioKm = parseFloat(state.categoria?.precio_km || 0);

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

  // ============================================================
  // 6️⃣ DROPOFF
  // ============================================================
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

    const precioKm = parseFloat(state.categoria?.precio_km || 0);

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

  // ============================================================
  // 7️⃣ GASOLINA
  // ============================================================
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

    const litros = parseFloat(state.categoria?.capacidad_tanque || 0);
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
    return labels;
  }

  // ============================================================
  // 8️⃣ FECHAS/HORAS: UI + HIDDEN
  // ============================================================
  function syncDateHiddenFromUI(uiId, hiddenId) {
    const ui = qs(uiId);
    const hid = qs(hiddenId);
    if (!ui || !hid) return;

    const val = String(ui.value || "").trim();
    if (!val) { hid.value = ""; return; }

    // Soporta dd/mm/aaaa
    if (/^\d{2}\/\d{2}\/\d{4}$/.test(val)) {
      const [d, m, y] = val.split("/").map(Number);
      const date = new Date(y, m - 1, d, 0, 0, 0);
      hid.value = toISODate(date);
      return;
    }

    // Soporta dd-mm-aaaa
    if (/^\d{2}-\d{2}-\d{4}$/.test(val)) {
      const [d, m, y] = val.split("-").map(Number);
      const date = new Date(y, m - 1, d, 0, 0, 0);
      hid.value = toISODate(date);
      return;
    }

    // Soporta yyyy-mm-dd
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

  // ============================================================
  // 9️⃣ DÍAS
  // ============================================================
  function computeDays() {
    const fi = qs("#fecha_inicio")?.value || "";
    const ff = qs("#fecha_fin")?.value || "";
    if (!fi || !ff) return 0;

    const parseDate = (val) => {
      // dd/mm/aaaa
      if (/^\d{2}\/\d{2}\/\d{4}$/.test(val)) {
        const [d, m, y] = val.split("/").map(Number);
        return new Date(y, m - 1, d, 0, 0, 0);
      }
      // dd-mm-aaaa
      if (/^\d{2}-\d{2}-\d{4}$/.test(val)) {
        const [d, m, y] = val.split("-").map(Number);
        return new Date(y, m - 1, d, 0, 0, 0);
      }
      // yyyy-mm-dd
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
  }

  // ============================================================
  // 1️⃣0️⃣ AEROPUERTO (No. vuelo)
  // ============================================================
  function isAirportSelected() {
    const selR = qs("#sucursal_retiro");
    const selE = qs("#sucursal_entrega");

    const check = (sel) => {
      if (!sel || sel.selectedIndex < 0) return false;
      const opt = sel.options[sel.selectedIndex];
      if (!opt) return false;

      const nombre = (opt.dataset.nombre || "").toLowerCase();
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

  // ============================================================
  // 1️⃣1️⃣ CATEGORÍA
  // ============================================================
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
  }

  function refreshCategoriaPreview() {
    const cat = state.categoria;
    const mini = qs("#catMiniPreview");
    if (!mini) return;

    if (!cat) {
      mini.style.display = "none";
      return;
    }

    mini.style.display = "";

    const n = qs("#catMiniName");
    const d = qs("#catMiniDesc");
    const rate = qs("#catMiniRate");
    const calc = qs("#catMiniCalc");

    if (n) n.textContent = cat.nombre || "—";
    if (d) d.textContent = cat.desc || "—";
    if (rate) rate.textContent = `${money(cat.precio_dia).replace(" MXN", "")} MXN / día`;

    const pre = Number(cat.precio_dia || 0) * Number(state.days || 0);
    if (calc) calc.textContent = money(pre);
  }

  // ============================================================
  // 1️⃣2️⃣ PROTECCIONES (PAQUETE)
  // ============================================================
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

  // ============================================================
  // 1️⃣3️⃣ INDIVIDUALES
  // ============================================================
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

    const exists = state.individuales.has(id);
    if (exists) state.individuales.delete(id);
    else state.individuales.set(id, { id, nombre, desc, precio, charge: "por_dia", grupo });

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
    if (state.proteccion) return;

    if (!inds.length) {
      if (txt) txt.textContent = "— Ninguna protección —";
      if (sub) sub.textContent = "Costo se refleja en el resumen.";
      if (rem) rem.style.display = "none";
      return;
    }

    if (rem) rem.style.display = "";
    if (txt) txt.textContent = `🧩 ${inds.length} individual(es)`;
    const subTot = calcIndividualesSubtotal();
    if (sub) sub.textContent = `Estimado individuales: ${money(subTot)} (${state.days || 0} día(s))`;
  }

  function calcIndividualesSubtotal() {
    const days = Number(state.days || 0);
    let sum = 0;
    state.individuales.forEach((it) => {
      const price = Number(it.precio || 0);
      sum += price * days;
    });
    return sum;
  }

  // ============================================================
  // 1️⃣4️⃣ ADDONS
  // ============================================================
  function setAddonQty(item, qty) {
    const q = Math.max(0, Number(qty || 0));
    if (q <= 0) state.addons.delete(String(item.id));
    else state.addons.set(String(item.id), { ...item, qty: q });

    syncAddonsHidden();
    refreshAddonsBadge();
    syncTotalsHidden();
    refreshSummary();
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

  // ============================================================
  // 1️⃣5️⃣ TOTALES + HIDDEN
  // ============================================================
  function calcTotals() {
    const days = Number(state.days || 0);

    const baseDia = state.categoria ? Number(state.categoria.precio_dia || 0) : 0;
    const baseTotal = baseDia * days;

    const prot = state.proteccion;
    const protPrice = prot ? Number(prot.precio || 0) : 0;
    const protTotal = prot
      ? (String(prot.charge || "por_evento") === "por_dia" ? protPrice * days : protPrice)
      : 0;

    const indTotal = (!prot) ? calcIndividualesSubtotal() : 0;
    const extrasSub = calcExtrasSubtotal();

    const deliveryTotal = state.servicios.delivery ? (state.delivery.total || 0) : 0;
    const dropoffTotal  = state.servicios.dropoff  ? (state.dropoff.total  || 0) : 0;
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

  // Funcion de editar tarifa base
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

  function refreshTotalsOnly() {
    const totals = calcTotals();
    const cat = state.categoria;

    const setText = (id, val) => { const el = qs(id); if (el) el.textContent = val; };

    setText("#resBaseTotal", cat ? money(totals.baseTotal) : "—");
    setText("#resSub", money(totals.subtotal));
    setText("#resIva", money(totals.iva));
    setText("#resTotal", money(totals.total));
  }

  // ============================================================
  // 1️⃣6️⃣ RESUMEN
  // ============================================================
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

    setText("#resSucursalRetiro", getText(selR));
    setText("#resSucursalEntrega", getText(selE));
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

    const svcList = getServiciosLabelList();
    setText("#resServicios", svcList.length ? svcList.join(", ") : "—");

    if (state.proteccion) {
      const prot = state.proteccion;
      const protPrice = Number(prot.precio || 0);
      setText("#resProte", prot ? `${prot.nombre} (${money(protPrice)}${prot.charge === "por_dia" ? " / día" : ""})` : "—");
    } else {
      const inds = Array.from(state.individuales.values());
      if (!inds.length) setText("#resProte", "—");
      else {
        const preview = inds.slice(0, 3).map(x => x.nombre).join(", ");
        const rest = inds.length > 3 ? ` +${inds.length - 3} más` : "";
        setText("#resProte", `🧩 Individuales: ${preview}${rest}`);
      }
    }

    const items = Array.from(state.addons.values()).filter(x => Number(x.qty || 0) > 0);
    setText("#resAdds", items.length ? items.map(x => `${x.nombre} ×${x.qty}`).join(", ") : "—");

    setText("#resSub", money(totals.subtotal));
    setText("#resIva", money(totals.iva));
    setText("#resTotal", money(totals.total));
  }

  // ============================================================
  // 1️⃣7️⃣ VALIDACIÓN
  // ============================================================
  function syncTelefonoFinal() {
    const lada = (qs("#telefono_lada")?.value || "+52").trim();
    const num = String(qs("#telefono_ui")?.value || "").trim().replace(/\s+/g, "");
    const out = qs("#telefono_cliente");

    if (out) out.value = num ? `${lada}${num}` : "";
  }

function validateBeforeSubmit() {
    const missing = [];

    const req = (id, label) => {
        const el = qs(id);
        const val = el ? String(el.value || "").trim() : "";
        if (!val) missing.push(label);
    };

    req("#sucursal_retiro", "Sucursal de retiro");
    req("#sucursal_entrega", "Sucursal de entrega");

    req("#fecha_inicio", "Fecha de salida");
    req("#hora_retiro", "Hora de salida");
    req("#fecha_fin", "Fecha de llegada");
    req("#hora_entrega", "Hora de llegada");

    if (!qs("#categoria_id")?.value) missing.push("Categoría");

    req("#nombre_cliente", "Nombre");
    req("#apellidos_cliente", "Apellidos");
    req("#email_cliente", "Email");
    req("#telefono_ui", "Teléfono");
    req("#pais", "País");

    if (isAirportSelected()) {
        const vuelo = qs("#no_vuelo")?.value?.trim() || "";
        if (!vuelo) missing.push("No. vuelo (Aeropuerto)");
    }

    if (state.servicios.delivery) {
        const els = getDeliveryEls();
        if (els) {
            const ub = String(els.ubicacion?.value || "");
            if (!ub) missing.push("Delivery: seleccionar ubicación");
            if (ub === "0") {
                const km = Number(els.km?.value || 0);
                if (!(km > 0)) missing.push("Delivery: kilómetros personalizados");
            }
        }
    }

    if (missing.length > 0) {
        if (missing.length === 1) {
            mostrarToast(`⚠️ Te faltó completar: ${missing[0]}`, 'warning');
        } else {
            mostrarToast(`⚠️ Faltan ${missing.length} campos por completar`, 'warning');
            console.log("Campos faltantes:", missing);
        }
        return false;
    }

    return true;
}

  // ============================================================
  // 1️⃣8️⃣ FLATPICKR (CALENDARIO)
  // ============================================================
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
        if (instance.input?.id === "fecha_inicio_ui") qs("#fecha_inicio").value = "";
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
        // 🚨 VALIDACIÓN: Primero sucursales
        const sucRetiro = document.getElementById("sucursal_retiro")?.value;
        const sucEntrega = document.getElementById("sucursal_entrega")?.value;

        if (!sucRetiro || !sucEntrega) {
          mostrarToast('⚠️ Primero debes seleccionar las sucursales de RETIRO y ENTREGA', 'warning');
          instance.close();
          return false;
        }

        openModal(instance);
        if (!instance._actionsAdded) {
          instance.calendarContainer.appendChild(makeActions(instance, "Fecha PickUp"));
          instance._actionsAdded = true;
        }
      },
      onClose: () => closeModal(),
      onChange: (selectedDates) => {
        const d = selectedDates?.[0];
        qs("#fecha_inicio").value = d ? toISODate(d) : "";

        const fin = qs("#fecha_fin")?.value;
        if (fin && qs("#fecha_inicio")?.value && fin < qs("#fecha_inicio").value) {
          qs("#fecha_fin").value = "";
          qs("#fecha_fin_ui").value = "";
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
        // 🚨 VALIDACIÓN: Primero sucursales y fecha de salida
        const sucRetiro = document.getElementById("sucursal_retiro")?.value;
        const sucEntrega = document.getElementById("sucursal_entrega")?.value;
        const fechaInicio = document.getElementById("fecha_inicio")?.value;

        if (!sucRetiro || !sucEntrega) {
          mostrarToast('⚠️ Primero debes seleccionar las sucursales de RETIRO y ENTREGA', 'warning');
          instance.close();
          return false;
        }

        if (!fechaInicio) {
          mostrarToast('⚠️ Primero debes seleccionar la FECHA DE SALIDA', 'warning');
          instance.close();
          return false;
        }

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
          // ✅ CORREGIDO: usar mostrarToast en lugar de alertify
          mostrarToast("⚠️ La fecha de devolución no puede ser antes de la fecha de salida", 'warning');
        }
        syncDays();
      }
    });
  }
  // ============================================================
  // 1️⃣9️⃣ SELECTOR DE HORA CON <SELECT> (desde cotización)
  // ============================================================
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

    // 🚨 VALIDACIÓN: Mostrar toast si intenta abrir el select sin sucursales o fecha
    selH.addEventListener("click", function(e) {
      const sucRetiro = document.getElementById("sucursal_retiro")?.value;
      const sucEntrega = document.getElementById("sucursal_entrega")?.value;
      const fechaInicio = document.getElementById("fecha_inicio")?.value;

      if (!sucRetiro || !sucEntrega) {
        e.stopPropagation();
        mostrarToast('⚠️ Primero debes seleccionar las sucursales de RETIRO y ENTREGA', 'warning');
        this.blur();
        return false;
      }

      if (!fechaInicio) {
        e.stopPropagation();
        mostrarToast('⚠️ Primero debes seleccionar la FECHA DE SALIDA', 'warning');
        this.blur();
        return false;
      }
    });
  }

  /* =========================
     SUBMIT POR AJAX (CORREGIDO)
  ========================= */
  async function submitReservaAjax(e) {
    e.preventDefault();

    const form = qs("#formReserva");
    if (!form) return;

    // 🔥 VALIDACIÓN: Verificar que haya una categoría seleccionada
    if (!state.categoria) {
        mostrarToast('⚠️ Debes seleccionar una categoría de vehículo', 'warning');
        return;
    }

    // 🔥 Asegurar que el ID de categoría se envíe correctamente
    const catInput = qs("#categoria_id");
    if (catInput) {
        catInput.value = state.categoria.id;
        catInput.name = "id_categoria";
    }

    // 🔥 Verificación de que el campo está listo (para depuración)
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

    // ✅ delivery: si está activo recalcula
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

    // ✅ arma teléfono final
    syncTelefonoFinal();

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

      // 🔥 Asegurar que id_categoria esté en FormData
      if (state.categoria) {
        fd.set("id_categoria", String(state.categoria.id));
        const precioFinal = parseFloat(state.categoria.precio_dia || 0);
        fd.set("tarifa_base", String(precioFinal));
        fd.set("tarifa_modificada", String(precioFinal));
      }

      // asegurar teléfono final
      fd.set("telefono_cliente", qs("#telefono_cliente")?.value || "");

      // ✅ asegurar servicios
      fd.set("svc_dropoff", state.servicios.dropoff ? "1" : "0");
      fd.set("svc_delivery", state.servicios.delivery ? "1" : "0");
      fd.set("svc_gasolina", state.servicios.gasolina ? "1" : "0");

      // ✅ delivery (backend)
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

      // 🔥 Depuración: ver qué se envía
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

        // 🔥 Mostrar errores específicos
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

        // Limpiar y reasignar eventos del botón "Ir a Bookings"
        const confirmOk = qs("#confirmOk");
        if (confirmOk) {
            const newConfirmOk = confirmOk.cloneNode(true);
            confirmOk.parentNode.replaceChild(newConfirmOk, confirmOk);
            newConfirmOk.addEventListener("click", redirectToActivas);
        }

        // Limpiar y reasignar eventos del botón "NO" o cerrar
        const confirmClose = qs("#confirmClose");
        if (confirmClose) {
            const newConfirmClose = confirmClose.cloneNode(true);
            confirmClose.parentNode.replaceChild(newConfirmClose, confirmClose);
            newConfirmClose.addEventListener("click", redirectToActivas);
        }

        // Clic fuera del modal
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

  // ============================================================
  // 2️⃣1️⃣ TABS EN MODAL PROTECCIONES
  // ============================================================
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

  // ============================================================
  // 2️⃣2️⃣ LOAD PROTECCIONES (PAQUETES)
  // ============================================================
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

      arr.forEach((p) => {
        const isFree = Number(p.precio || 0) <= 0;

        const card = document.createElement("article");
        card.className = "pack-card" + (isFree ? " pack-card--free" : "");
        card.style.minWidth = "280px";

        card.innerHTML = `
          <div class="body">
            <h4>${escapeHtml(p.nombre)}</h4>
            <p>${escapeHtml(p.desc || (isFree ? "Sin protección adicional." : "Protección para tu viaje."))}</p>
            <div class="precio">
              ${money(p.precio).replace(" MXN", "")} <span>MXN${p.charge === "por_dia" ? " / día" : ""}</span>
            </div>
            <div style="display:flex; justify-content:space-between; align-items:center; gap:10px; margin-top:12px;">
              <span class="pill" style="display:inline-flex; padding:6px 10px; border-radius:999px; border:1px solid rgba(255,255,255,.18); font-weight:900; font-size:12px;">
                ${p.charge === "por_dia" ? "Por día" : "Por evento"}
              </span>
              <button class="btn primary" type="button" style="padding:10px 12px; border-radius:12px; font-weight:1000;">
                Elegir
              </button>
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

  // ============================================================
  // 2️⃣3️⃣ LOAD ADDONS
  // ============================================================
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

  // ============================================================
  // 2️⃣4️⃣ PAISES + LADA + ISO2
  // ============================================================
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

  // ============================================================
  // 2️⃣5️⃣ EVENTOS UI (CON VALIDACIONES EN BOTONES)
  // ============================================================
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

    // Switches Servicios
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

    // Categorías modal (CON VALIDACIÓN)
    const catPop = qs("#catPop");
    qs("#btnCategorias")?.addEventListener("click", () => {
      // ✅ VALIDACIÓN: Verificar que fechas y horas estén completas
      if (!validarFechasHoras()) return;

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

    // Protecciones modal (CON VALIDACIÓN)
    const protPop = qs("#proteccionPop");
    qs("#btnProtecciones")?.addEventListener("click", async () => {
      // ✅ VALIDACIÓN: Verificar que haya categoría seleccionada
      if (!validarCategoria()) return;

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

    // EVENT DELEGATION: individuales
    document.addEventListener("click", (e) => {
      const card = e.target.closest(".individual-item");
      if (!card) return;

      const isBtn = e.target.closest("button,a,input,textarea,select");
      if (isBtn) return;

      toggleIndividualFromCard(card);
    });

   // Addons modal (SIN VALIDACIÓN - SOLO ABRE)
const addPop = qs("#addonsPop");
qs("#btnAddons")?.addEventListener("click", async () => {
  // ✅ SIN VALIDACIÓN - Solo abre el modal de adicionales
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

  // Resumen modal (SIN VALIDACIÓN - SOLO ABRE)
const resPop = qs("#resumenPop");
qs("#btnResumen")?.addEventListener("click", () => {
  // ✅ SIN VALIDACIÓN - Solo abre el resumen
  syncDays();
  repaintCategoriaModalEstimados();
  refreshProteccionUIHeader();
  refreshSummary();
  openPop(resPop);
});
state
    qs("#resumenClose")?.addEventListener("click", () => closePop(resPop));
    qs("#resumenOk")?.addEventListener("click", () => closePop(resPop));

    // cerrar modal al tocar afuera (menos confirm)
    qsa(".pop.modal").forEach((pop) => {
      pop.addEventListener("click", (e) => {
        if (e.target !== pop) return;
        if (pop.id === "confirmPop") return;
        closePop(pop);
      });
    });

    // Teléfono: sincroniza hidden al escribir
    qs("#telefono_ui")?.addEventListener("input", syncTelefonoFinal);

    // submit -> AJAX (CON VALIDACIÓN DE CLIENTE DENTRO DE submitReservaAjax)
    qs("#formReserva")?.addEventListener("submit", submitReservaAjax);

    // tabs
    bindProteTabs();

    // funcion de editar tarifa base
    initTarifaEdit();
  }

  // ============================================================
  // 2️⃣6️⃣ BOOT (INICIALIZACIÓN)
  // ============================================================
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
  });

  // ============================================================
  // 2️⃣7️⃣ FUERZA RECÁLCULO
  // ============================================================
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

  // ============================================================
  // 2️⃣8️⃣ EXPONER API GLOBAL
  // ============================================================
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

})();

// ============================================================
// 2️⃣9️⃣ CARGA DE EDICIÓN (RESERVACIÓN EXISTENTE)
// ============================================================
window.addEventListener("DOMContentLoaded", async () => {

  const API = window._reservaAPI;

  if (!window.reservacionEditar || !API) {
    console.warn("⚠️ No hay reservación o API");
    return;
  }

  const r = window.reservacionEditar;
  console.log("🟢 EDITANDO:", r);

  // 🚗 CATEGORÍA
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

  // 🚚 DELIVERY
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

  // 🧰 SERVICIOS
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
        const dKm  = document.getElementById("deliveryKm");
        const dDir = document.getElementById("deliveryDireccion");

        const sel = document.getElementById("dropUbicacion");
        const km  = document.getElementById("dropKm");
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

  // 🔒 PROTECCIÓN
  if (window.seguroEditar) {
    console.log("🔒 Cargando protección");
    API.setProteccion({
      id: window.seguroEditar.id_paquete,
      nombre: window.seguroEditar.nombre,
      precio: window.seguroEditar.precio_por_dia,
      charge: "por_dia"
    });
  }

  // 🔄 REFRESH FINAL
  API.forceRecalc();
  console.log("🧠 STATE FINAL:", API.getState());
  console.log("✅ EDICIÓN CARGADA COMPLETA");

});

// ============================================================
// 3️⃣0️⃣ SELECT2 CON ICONOS
// ============================================================
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

$(document).ready(function() {
    initSelect2Sucursales();
});
