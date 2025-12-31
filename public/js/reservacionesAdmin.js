(function () {
  "use strict";

  /* =========================
     Helpers
  ========================= */
  const qs = (s) => document.querySelector(s);

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

  /* =========================
     Estado global
  ========================= */
  const state = {
    days: 0,
    categoria: null,   // {id,nombre,desc,precio_dia,img}
    proteccion: null,  // {id,nombre,precio,charge,desc}
    addons: new Map(), // id -> {id,nombre,precio,charge,desc,qty}
  };

  /* =========================
     Hidden inputs (backend)
  ========================= */
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

  /* =========================
     Fechas/Horas: UI + Hidden
     - UI:  #fecha_inicio_ui, #fecha_fin_ui, #hora_retiro_ui, #hora_entrega_ui
     - Hidden reales (backend): #fecha_inicio, #fecha_fin, #hora_retiro, #hora_entrega
  ========================= */
  function syncDateHiddenFromUI(uiId, hiddenId) {
    const ui = qs(uiId);
    const hid = qs(hiddenId);
    if (!ui || !hid) return;

    // Si UI trae dd/mm/YYYY -> lo convertimos a ISO
    const val = String(ui.value || "").trim();
    if (!val) { hid.value = ""; return; }

    if (/^\d{2}\/\d{2}\/\d{4}$/.test(val)) {
      const [d, m, y] = val.split("/").map(Number);
      const date = new Date(y, m - 1, d, 0, 0, 0);
      hid.value = toISODate(date);
      return;
    }

    // si ya viniera ISO por algo
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

  /* =========================
     Días
  ========================= */
  function computeDays() {
    const fi = qs("#fecha_inicio")?.value || "";
    const ff = qs("#fecha_fin")?.value || "";
    if (!fi || !ff) return 0;

    const parseDate = (val) => {
      if (/^\d{2}\/\d{2}\/\d{4}$/.test(val)) {
        const [d, m, y] = val.split("/").map(Number);
        return new Date(y, m - 1, d, 0, 0, 0);
      }
      // ISO
      if (/^\d{4}-\d{2}-\d{2}$/.test(val)) return new Date(val + "T00:00:00");
      // fallback
      return new Date(val);
    };

    const d1 = parseDate(fi);
    const d2 = parseDate(ff);
    const diff = Math.ceil((d2 - d1) / (1000 * 60 * 60 * 24));
    return Math.max(1, Number.isFinite(diff) ? diff : 0);
  }

  function syncDays() {
    state.days = computeDays();
    const diasTxt = qs("#diasTxt");
    if (diasTxt) diasTxt.textContent = String(state.days || 0);

    refreshCategoriaPreview();
    refreshAddonsBadge();
    refreshSummary();
    syncTotalsHidden();
  }

  /* =========================
     Aeropuerto (No. vuelo)
  ========================= */
  function isAirportSelected() {
    const r = qs("#sucursal_retiro")?.value || "";
    const e = qs("#sucursal_entrega")?.value || "";
    return (String(r) === "1" || String(e) === "1");
  }

  function syncVueloField() {
    const wrap = qs("#vueloWrap");
    const vuelo = qs("#no_vuelo");
    const show = isAirportSelected();

    if (wrap) wrap.style.display = show ? "" : "none";
    if (vuelo) {
      if (show) vuelo.setAttribute("required", "required");
      else {
        vuelo.removeAttribute("required");
        vuelo.value = "";
      }
    }
  }

  /* =========================
     Categoría
  ========================= */
  function setCategoria(cat) {
    state.categoria = cat;

    const hid = qs("#categoria_id");
    if (hid) hid.value = cat ? String(cat.id) : "";

    const txt = qs("#catSelTxt");
    const sub = qs("#catSelSub");
    const rem = qs("#catRemove");

    if (!cat) {
      if (txt) txt.textContent = "— Ninguna categoría —";
      if (sub) sub.textContent = "Tarifa base por día y cálculo previo aparecerán aquí.";
      if (rem) rem.style.display = "none";
      const mini = qs("#catMiniPreview");
      if (mini) mini.style.display = "none";
      syncTotalsHidden();
      refreshSummary();
      return;
    }

    if (txt) txt.textContent = cat.nombre;
    if (sub) sub.textContent = `${money(cat.precio_dia)} / día · ${state.days || 0} día(s)`;
    if (rem) rem.style.display = "";

    refreshCategoriaPreview();
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

  /* =========================
     Protecciones
  ========================= */
  function setProteccion(p) {
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

  /* =========================
     Addons
  ========================= */
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

  /* =========================
     Totales + hidden
  ========================= */
  function calcTotals() {
    const days = Number(state.days || 0);

    const baseDia = state.categoria ? Number(state.categoria.precio_dia || 0) : 0;
    const baseTotal = baseDia * days;

    const prot = state.proteccion;
    const protPrice = prot ? Number(prot.precio || 0) : 0;
    const protTotal = prot
      ? (String(prot.charge || "por_evento") === "por_dia" ? protPrice * days : protPrice)
      : 0;

    const extrasSub = calcExtrasSubtotal();

    const subtotal = baseTotal + protTotal + extrasSub;
    const iva = Math.round(subtotal * 0.16 * 100) / 100;
    const total = subtotal + iva;

    return { baseDia, baseTotal, protTotal, extrasSub, subtotal, iva, total };
  }

  function syncTotalsHidden() {
    ensureTotalsHidden();

    const totals = calcTotals();
    const baseDia = totals.baseDia;

    qs("#precio_base_dia").value = String(baseDia || 0);
    qs("#subtotal").value = String(totals.subtotal || 0);
    qs("#impuestos").value = String(totals.iva || 0);
    qs("#total").value = String(totals.total || 0);
  }

  /* =========================
     Resumen
  ========================= */
  function refreshSummary() {
    const days = Number(state.days || 0);

    const selR = qs("#sucursal_retiro");
    const selE = qs("#sucursal_entrega");

    const getText = (sel) =>
      sel?.options?.[sel.selectedIndex]?.textContent?.trim() || "—";

    // ✅ mostrar en resumen lo que ve el usuario (UI), pero si no existe, fallback al hidden
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
    setText("#resBaseDia", cat ? `${money(totals.baseDia)} / día` : "—");
    setText("#resBaseTotal", cat ? money(totals.baseTotal) : "—");

    const prot = state.proteccion;
    const protPrice = prot ? Number(prot.precio || 0) : 0;
    setText(
      "#resProte",
      prot ? `${prot.nombre} (${money(protPrice)}${prot.charge === "por_dia" ? " / día" : ""})` : "—"
    );

    const items = Array.from(state.addons.values()).filter(x => Number(x.qty || 0) > 0);
    setText("#resAdds", items.length ? items.map(x => `${x.nombre} ×${x.qty}`).join(", ") : "—");

    setText("#resSub", money(totals.subtotal));
    setText("#resIva", money(totals.iva));
    setText("#resTotal", money(totals.total));
  }

  /* =========================
     Load Protecciones (DESC)
  ========================= */
  async function loadProtecciones() {
    const list = qs("#proteList");
    if (!list) return;

    list.innerHTML = `<div class="loading">Cargando paquetes...</div>`;

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

      // ✅ ordenar caro -> barato (como lo traías)
      arr.sort((a, b) => Number(b.precio || 0) - Number(a.precio || 0));

      if (!arr.length) {
        list.innerHTML = `<div class="loading">No hay protecciones disponibles.</div>`;
        return;
      }

      list.innerHTML = "";

      arr.forEach((p) => {
        const isFree = Number(p.precio || 0) <= 0;

        const card = document.createElement("article");
        card.className = "card-pick" + (isFree ? " card-pick--free" : "");

        if (isFree) {
          card.style.gridTemplateColumns = "1fr";
          card.style.opacity = "0.9";
          card.style.borderStyle = "dashed";
        }

        card.innerHTML = `
          <div class="cp-left">
            <div class="cp-title">${escapeHtml(p.nombre)}</div>
            <div class="cp-sub">${escapeHtml(p.desc || (isFree ? "Sin protección adicional." : "Protección para tu viaje."))}</div>
            <div class="cp-meta">
              <span class="pill">Tipo: ${p.charge === "por_dia" ? "Por día" : "Por evento"}</span>
              ${isFree ? `<span class="pill">Opción básica</span>` : ``}
            </div>
          </div>

          ${isFree ? `
            <div style="margin-top:10px; display:flex; justify-content:flex-end;">
              <button class="btn gray" style="padding:8px 12px; border-radius:12px;" type="button">Elegir</button>
            </div>
          ` : `
            <div class="cp-right">
              <div class="cp-price">
                <div class="muted small">Costo</div>
                <div class="price-big">${money(p.precio).replace(" MXN","")} <span>MXN${p.charge==="por_dia" ? " / día" : ""}</span></div>
              </div>
              <button class="btn primary btn-block" type="button">Elegir</button>
            </div>
          `}
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

          closePop(qs("#proteccionPop"));
        });

        list.appendChild(card);
      });

    } catch (e) {
      console.error("Protecciones error:", e);
      list.innerHTML = `<div class="loading">Error cargando protecciones.</div>`;
    }
  }

  /* =========================
     Load Addons
  ========================= */
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
              <div class="price-big">${money(precio).replace(" MXN","")} <span>MXN${charge==="por_dia" ? " / día" : ""}</span></div>
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
      list.innerHTML = `<div class="loading">Error cargando adicionales.</div>`;
    }
  }

  /* =========================
     Validación
  ========================= */
  function validateBeforeSubmit() {
    const missing = [];

    const req = (id, label) => {
      const el = qs(id);
      const val = el ? String(el.value || "").trim() : "";
      if (!val) missing.push(label);
    };

    req("#sucursal_retiro", "Sucursal de retiro");
    req("#sucursal_entrega", "Sucursal de entrega");

    // ✅ validamos los hidden reales (backend)
    req("#fecha_inicio", "Fecha de salida");
    req("#hora_retiro", "Hora de salida");
    req("#fecha_fin", "Fecha de llegada");
    req("#hora_entrega", "Hora de llegada");

    if (!qs("#categoria_id")?.value) missing.push("Categoría");

    req("#nombre_cliente", "Nombre");
    req("#apellido_paterno", "Apellido paterno");
    req("#apellido_materno", "Apellido materno");
    req("#email_cliente", "Email");
    req("#telefono_cliente", "Teléfono");
    req("#pais", "País");

    if (isAirportSelected()) {
      const vuelo = qs("#no_vuelo")?.value?.trim() || "";
      if (!vuelo) missing.push("No. vuelo (Aeropuerto)");
    }

    if (missing.length) {
      alert("Falta completar:\n• " + missing.join("\n• "));
      return false;
    }
    return true;
  }

  /* =========================
     Flatpickr: calendario modal + time 10min
     (usa inputs _ui y sincroniza a hidden reales)
  ========================= */
  function initFlatpickrModalCalendar() {
    if (!window.flatpickr) return;

    // backdrop
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
        // al limpiar, reflejamos hidden
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

    // ✅ Fecha inicio UI -> hidden ISO
    window.flatpickr("#fecha_inicio_ui", {
      locale: "es",
      dateFormat: "d/m/Y",
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

    // ✅ Fecha fin UI -> hidden ISO
    window.flatpickr("#fecha_fin_ui", {
      locale: "es",
      dateFormat: "d/m/Y",
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
      onChange: (selectedDates) => {
        const d = selectedDates?.[0];
        qs("#fecha_fin").value = d ? toISODate(d) : "";

        const ini = qs("#fecha_inicio")?.value;
        const fin = qs("#fecha_fin")?.value;
        if (ini && fin && fin < ini) {
          qs("#fecha_fin").value = "";
          qs("#fecha_fin_ui").value = "";
          alert("La fecha de devolución no puede ser antes de la fecha de salida.");
        }
        syncDays();
      }
    });
  }

  function initFlatpickrTime10() {
    if (!window.flatpickr) return;

    const baseCfg = {
      enableTime: true,
      noCalendar: true,
      dateFormat: "H:i",
      time_24hr: true,
      minuteIncrement: 10,
      allowInput: false,
    };

    window.flatpickr("#hora_retiro_ui", {
      ...baseCfg,
      onChange: (sel, timeStr) => {
        qs("#hora_retiro").value = timeStr || "";
        refreshSummary();
      },
      onClose: () => refreshSummary(),
    });

    window.flatpickr("#hora_entrega_ui", {
      ...baseCfg,
      onChange: (sel, timeStr) => {
        qs("#hora_entrega").value = timeStr || "";
        refreshSummary();
      },
      onClose: () => refreshSummary(),
    });
  }

  /* =========================
     Eventos UI
  ========================= */
  function bindUI() {
    // ✅ si no carga flatpickr, sincroniza por cambios en UI
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

    // Categorías modal
    const catPop = qs("#catPop");
    qs("#btnCategorias")?.addEventListener("click", () => openPop(catPop));
    qs("#catClose")?.addEventListener("click", () => closePop(catPop));
    qs("#catCancel")?.addEventListener("click", () => closePop(catPop));

    catPop?.addEventListener("click", (e) => {
      const card = e.target.closest(".card-pick");
      const btn = e.target.closest("button");
      if (!card || !btn) return;

      const id = card.dataset.id;
      const nombre = card.dataset.nombre || "";
      const desc = card.dataset.desc || "";
      const precio = Number(card.dataset.precio || 0);
      const img = card.dataset.img || "";

      setCategoria({ id, nombre, desc, precio_dia: precio, img });
      closePop(catPop);
    });

    qs("#catRemove")?.addEventListener("click", () => setCategoria(null));

    // Protecciones modal
    const protPop = qs("#proteccionPop");
    qs("#btnProtecciones")?.addEventListener("click", async () => {
      openPop(protPop);
      await loadProtecciones();
    });
    qs("#proteClose")?.addEventListener("click", () => closePop(protPop));
    qs("#proteCancel")?.addEventListener("click", () => closePop(protPop));
    qs("#proteRemove")?.addEventListener("click", () => setProteccion(null));

    // Addons modal
    const addPop = qs("#addonsPop");
    qs("#btnAddons")?.addEventListener("click", async () => {
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

    // Resumen modal
    const resPop = qs("#resumenPop");
    qs("#btnResumen")?.addEventListener("click", () => {
      syncDays();
      refreshSummary();
      openPop(resPop);
    });
    qs("#resumenClose")?.addEventListener("click", () => closePop(resPop));
    qs("#resumenOk")?.addEventListener("click", () => closePop(resPop));

    // cerrar modal al tocar afuera
    document.querySelectorAll(".pop.modal").forEach((pop) => {
      pop.addEventListener("click", (e) => {
        if (e.target === pop) closePop(pop);
      });
    });

    // submit
    qs("#formReserva")?.addEventListener("submit", (e) => {
      ensureCategoriaHiddenFix();
      ensureTotalsHidden();
      ensureProteccionHidden();

      // ✅ asegúrate que hidden reales estén sincronizados antes de validar
      syncDateHiddenFromUI("#fecha_inicio_ui", "#fecha_inicio");
      syncDateHiddenFromUI("#fecha_fin_ui", "#fecha_fin");
      syncTimeHiddenFromUI("#hora_retiro_ui", "#hora_retiro");
      syncTimeHiddenFromUI("#hora_entrega_ui", "#hora_entrega");

      syncVueloField();
      syncDays();
      refreshSummary();
      syncProteccionHidden();
      syncAddonsHidden();
      syncTotalsHidden();

      if (!validateBeforeSubmit()) {
        e.preventDefault();
        return;
      }
    });
  }

  /* =========================
     Boot
  ========================= */
  document.addEventListener("DOMContentLoaded", () => {
    ensureCategoriaHiddenFix();
    ensureTotalsHidden();
    ensureProteccionHidden();

    syncVueloField();

    // ✅ si ya traen valores (editar), refresca UI (si existe)
    // (cuando me pases el blade, ajustamos estos IDs si hace falta)

    syncDays();
    refreshAddonsBadge();
    syncProteccionHidden();
    syncAddonsHidden();
    syncTotalsHidden();
    refreshSummary();

    initFlatpickrModalCalendar();
    initFlatpickrTime10();

    bindUI();
  });

})();
