(function () {
  "use strict";

  // ===== Helpers =====
  const qs = (s, r = document) => r.querySelector(s);
  const qsa = (s, r = document) => Array.from(r.querySelectorAll(s));

  // Validacines
  function initSectionValidators() {

    // paso 1
    const formStep1 = document.getElementById('step1Form');
    if (formStep1) {
      formStep1.addEventListener('submit', function (e) {
        // Lista de IDs obligatorios en el Paso 1
        const requiredIds = [
          'pickup_sucursal_id', 'dropoff_sucursal_id',
          'start', 'end', // Inputs de fecha
          'pickup_h', 'pickup_m',
          'dropoff_h', 'dropoff_m'
        ];

        let error = false;

        // Revisamos uno por uno
        for (let id of requiredIds) {
          let el = document.getElementById(id);
          // Si no existe o está vacío o es nulo
          if (!el || el.value.trim() === "") {
            error = true;
            // Efecto visual de error
            el.style.borderColor = 'red';
            setTimeout(() => el.style.borderColor = '', 3000);
          }
        }

        if (error) {
          e.preventDefault(); // 🛑 ¡ALTO! No deja pasar al Paso 2
          e.stopImmediatePropagation();
          if (window.alertify) alertify.error("Completa todos los campos de lugar y fecha para continuar.");
        }
      });
    }

    // paso 2
    const btnNextStep2 = document.querySelector('.wizard-nav a[href*="step=3"]');

    if (btnNextStep2) {
      btnNextStep2.addEventListener('click', function (e) {
        // Verificamos si hay alguna tarjeta de carro activa
        const carSelected = document.querySelector('.car-card.active');

        // O verificamos si la URL ya tiene categoria_id (por si recargó)
        const urlParams = new URLSearchParams(window.location.search);
        const hasCat = urlParams.has('categoria_id');

        if (!carSelected && !hasCat) {
          e.preventDefault(); // 🛑 ¡ALTO! No deja pasar al Paso 3
          if (window.alertify) alertify.error("Debes seleccionar un vehículo y un plan de renta.");

          // Scroll hacia los autos para que vea que debe elegir
          const carsContainer = document.querySelector('.cars');
          if (carsContainer) carsContainer.scrollIntoView({ behavior: "smooth" });
        }
      });
    }

    // paso 4
    const btnReservar = document.getElementById('btnReservar');
    if (btnReservar) {
      btnReservar.addEventListener('click', function (e) {
        let faltantes = [];

        // Obtenemos los campos
        const nombre = document.getElementById('nombreCliente');
        const apellido = document.getElementById('apellidoCliente');
        const telefono = document.getElementById('telefonoCliente');
        const email = document.getElementById('correoCliente');
        const terminos = document.getElementById('acepto');
        const nacimiento = document.getElementById('dob');

        // Reglas
        if (!nombre || nombre.value.trim().length < 2) faltantes.push("Nombre");
        if (!telefono || telefono.value.trim().length < 10) faltantes.push("Teléfono (10 dígitos)");
        if (!nacimiento || nacimiento.value.trim() === "") faltantes.push("Fecha de nacimiento");

        // Validar Email con Regex simple
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (!email || !emailRegex.test(email.value.trim())) faltantes.push("Correo electrónico válido");

        // Validar Checkbox
        if (!terminos || !terminos.checked) faltantes.push("Aceptar términos y condiciones");

        if (faltantes.length > 0) {
          e.preventDefault();
          e.stopImmediatePropagation();

          // Mensaje detallado
          if (window.alertify) {
            alertify.error("<b>Faltan datos obligatorios:</b><br>" + faltantes.join("<br>"));
          } else {
            alert("Faltan datos:\n" + faltantes.join("\n"));
          }
        }
      });
    }
  }

  // ======================================================
  // ✅ SIN ALERTAS: bloquear SOLO la alerta de "Campos incompletos"
  // (Fecha y hora de entrega / devolución)
  // ======================================================
  (function disableMissingFieldsAlerts() {
    const shouldBlock = (msg) => {
      const s = String(msg || "");
      return (
        s.includes("Campos incompletos") ||
        s.includes("Por favor completa los siguientes campos") ||
        s.includes("Fecha y hora de entrega") ||
        s.includes("Fecha y hora de devolución")
      );
    };

    // 1) Native alert()
    try {
      const _alert = window.alert;
      window.alert = function (msg) {
        if (shouldBlock(msg)) return;
        return _alert.call(window, msg);
      };
    } catch (_) { }

    // 2) SweetAlert2 (Swal.fire)
    try {
      if (window.Swal && typeof window.Swal.fire === "function") {
        const _fire = window.Swal.fire.bind(window.Swal);
        window.Swal.fire = function (a, b, c) {
          const title = (a && typeof a === "object") ? (a.title || "") : (a || "");
          const text = (a && typeof a === "object") ? (a.text || "") : (b || "");
          if (shouldBlock(title) || shouldBlock(text)) return Promise.resolve();
          return _fire(a, b, c);
        };
      }
    } catch (_) { }

    // 3) Fallback: si algún modal/toast aparece con ese texto, lo ocultamos
    try {
      const mo = new MutationObserver(() => {
        const nodes = Array.from(document.querySelectorAll("body *"));
        nodes.forEach(el => {
          if (!el || !el.textContent) return;
          if (!shouldBlock(el.textContent)) return;
          const modal = el.closest(".modal,.swal2-container,.alert,.toast,[role='dialog']");
          (modal || el).style.display = "none";
        });
      });
      mo.observe(document.documentElement, { childList: true, subtree: true });
    } catch (_) { }
  })();

  // ======================================================
  // ✅ FORZAR SIEMPRE STEP 1 CUANDO SOLO VIENE ?step=2
  // (si la URL trae más params, NO toca nada)
  // ======================================================
  function forceStep1WhenOnlyStepParam() {
    try {
      const url = new URL(window.location.href);
      const p = url.searchParams;

      if (!p.has('step')) return;

      const keys = [];
      p.forEach((v, k) => { if (v !== null && String(v).trim() !== '') keys.push(k); });

      const onlyStep = (keys.length === 1 && keys[0] === 'step');
      if (!onlyStep) return;

      p.set('step', '1');
      window.history.replaceState({}, document.title, url.pathname + '?' + p.toString() + url.hash);

      try {
        document.dispatchEvent(new CustomEvent('wizard:stepChanged', { detail: { step: 1 } }));
      } catch (_) { }
    } catch (_) { }
  }

  // ======================================================
  // ✅ PERSISTENCIA (SIN SESIÓN)
  // ======================================================
  function initWizardStatePersistence() {
    const LS_KEY = "viajero_resv_filters_v1";
        // 🔁 MODO RESET:
    // Si llegamos con ?reset=1 o con step=1 sin parámetros de renta,
    // NO debemos rehidratar desde localStorage.
    let isResetMode = false;
    try {
      const url = new URL(window.location.href);
      const p = url.searchParams;

      const resetFlag = p.get('reset');
      const step = p.get('step');

      const hasMeaningful =
        p.get('pickup_date') || p.get('dropoff_date') ||
        p.get('pickup_time') || p.get('dropoff_time') ||
        p.get('pickup_sucursal_id') || p.get('dropoff_sucursal_id') ||
        p.get('addons') || p.get('categoria_id') || p.get('plan');

      // reset explícito o entrada “limpia” a step=1
      if (resetFlag === '1' || (step === '1' && !hasMeaningful)) {
        isResetMode = true;
        try {
          localStorage.removeItem(LS_KEY);
        } catch (_) { }
      }
    } catch (_) { }

    const map = {
      pickup_sucursal_id: qs('#pickup_sucursal_id') || qs('[name="pickup_sucursal_id"]'),
      dropoff_sucursal_id: qs('#dropoff_sucursal_id') || qs('[name="dropoff_sucursal_id"]'),

      pickup_date: qs('#start') || qs('[name="pickup_date"]'),
      dropoff_date: qs('#end') || qs('[name="dropoff_date"]'),

      pickup_h: qs('#pickup_h') || qs('[name="pickup_hour"]') || qs('[name="pickup_hora"]') || qs('[name="pickup_h"]'),
      pickup_m: qs('#pickup_m') || qs('[name="pickup_min"]') || qs('[name="pickup_minuto"]') || qs('[name="pickup_m"]'),
      dropoff_h: qs('#dropoff_h') || qs('[name="dropoff_hour"]') || qs('[name="dropoff_hora"]') || qs('[name="dropoff_h"]'),
      dropoff_m: qs('#dropoff_m') || qs('[name="dropoff_min"]') || qs('[name="dropoff_minuto"]') || qs('[name="dropoff_m"]'),

      pickup_time_hidden: qs('#pickup_time_hidden') || qs('[name="pickup_time"]'),
      dropoff_time_hidden: qs('#dropoff_time_hidden') || qs('[name="dropoff_time"]'),
    };

    const addonsHidden =
      qs('#addonsHidden') ||
      qs('input[name="addons"]') ||
      qs('input[name="addons_ids"]') ||
      qs('input[name="addonsHidden"]');

    function safeVal(el) {
      if (!el) return "";
      return (el.value ?? "").toString().trim();
    }

    function setVal(el, v) {
      if (!el) return;
      const next = (v ?? "").toString();
      if ((el.value ?? "") === next) return;
      el.value = next;
      try { el.dispatchEvent(new Event('change', { bubbles: true })); } catch (_) { }
    }

    function computeTimesIntoHidden() {
      const ph = safeVal(map.pickup_h);
      const pm = safeVal(map.pickup_m);
      const dh = safeVal(map.dropoff_h);
      const dm = safeVal(map.dropoff_m);

      if (map.pickup_time_hidden && (ph || pm)) {
        const hh = (ph || "00").padStart(2, '0');
        const mm = (pm || "00").padStart(2, '0');
        map.pickup_time_hidden.value = `${hh}:${mm}`;
      }
      if (map.dropoff_time_hidden && (dh || dm)) {
        const hh = (dh || "00").padStart(2, '0');
        const mm = (dm || "00").padStart(2, '0');
        map.dropoff_time_hidden.value = `${hh}:${mm}`;
      }
    }

    function readFromQS() {
      const p = new URLSearchParams(window.location.search);
      const obj = {};
      [
        'pickup_sucursal_id', 'dropoff_sucursal_id',
        'pickup_date', 'dropoff_date',
        'pickup_time', 'dropoff_time',
        'pickup_h', 'pickup_m', 'dropoff_h', 'dropoff_m',
        'addons', 'step', 'categoria_id', 'plan'
      ].forEach(k => {
        const v = p.get(k);
        if (v !== null && String(v).trim() !== "") obj[k] = v;
      });
      return obj;
    }

        function readFromLS() {
      // En modo reset, ignoramos totalmente lo que haya en localStorage
      if (isResetMode) return {};
      try {
        const raw = localStorage.getItem(LS_KEY);
        if (!raw) return {};
        const obj = JSON.parse(raw);
        return (obj && typeof obj === 'object') ? obj : {};
      } catch (_) { return {}; }
    }

    function writeToLS(state) {
      try { localStorage.setItem(LS_KEY, JSON.stringify(state || {})); } catch (_) { }
    }

    function currentState() {
      computeTimesIntoHidden();

      const st = {
        pickup_sucursal_id: safeVal(map.pickup_sucursal_id),
        dropoff_sucursal_id: safeVal(map.dropoff_sucursal_id),

        pickup_date: safeVal(map.pickup_date),
        dropoff_date: safeVal(map.dropoff_date),

        pickup_time: safeVal(map.pickup_time_hidden) || "",
        dropoff_time: safeVal(map.dropoff_time_hidden) || "",

        pickup_h: safeVal(map.pickup_h),
        pickup_m: safeVal(map.pickup_m),
        dropoff_h: safeVal(map.dropoff_h),
        dropoff_m: safeVal(map.dropoff_m),
      };

      if (addonsHidden) st.addons = safeVal(addonsHidden);

      try {
        const p = new URLSearchParams(window.location.search);
        const step = p.get('step'); if (step) st.step = step;
        const categoria_id = p.get('categoria_id'); if (categoria_id) st.categoria_id = categoria_id;
        const plan = p.get('plan'); if (plan) st.plan = plan;
      } catch (_) { }

      return st;
    }

    function mergePreferNew(base, incoming) {
      const out = { ...(base || {}) };
      Object.keys(incoming || {}).forEach(k => {
        const v = incoming[k];
        if (v !== null && String(v).trim() !== "") out[k] = v;
      });
      return out;
    }

    function applyStateToInputs(state) {
      if (!state) return;

      if (map.pickup_sucursal_id && !safeVal(map.pickup_sucursal_id) && state.pickup_sucursal_id) setVal(map.pickup_sucursal_id, state.pickup_sucursal_id);
      if (map.dropoff_sucursal_id && !safeVal(map.dropoff_sucursal_id) && state.dropoff_sucursal_id) setVal(map.dropoff_sucursal_id, state.dropoff_sucursal_id);

      if (map.pickup_date && !safeVal(map.pickup_date) && state.pickup_date) setVal(map.pickup_date, state.pickup_date);
      if (map.dropoff_date && !safeVal(map.dropoff_date) && state.dropoff_date) setVal(map.dropoff_date, state.dropoff_date);

      if (map.pickup_h && !safeVal(map.pickup_h) && state.pickup_h) setVal(map.pickup_h, state.pickup_h);
      if (map.pickup_m && !safeVal(map.pickup_m) && state.pickup_m) setVal(map.pickup_m, state.pickup_m);
      if (map.dropoff_h && !safeVal(map.dropoff_h) && state.dropoff_h) setVal(map.dropoff_h, state.dropoff_h);
      if (map.dropoff_m && !safeVal(map.dropoff_m) && state.dropoff_m) setVal(map.dropoff_m, state.dropoff_m);

      if (state.pickup_time && !safeVal(map.pickup_time_hidden) && map.pickup_time_hidden) setVal(map.pickup_time_hidden, state.pickup_time);
      if (state.dropoff_time && !safeVal(map.dropoff_time_hidden) && map.dropoff_time_hidden) setVal(map.dropoff_time_hidden, state.dropoff_time);

      function splitToHM(t) {
        const m = String(t || "").match(/^(\d{1,2}):(\d{2})$/);
        if (!m) return null;
        return { h: m[1].padStart(2, '0'), m: m[2].padStart(2, '0') };
      }
      const pHM = splitToHM(state.pickup_time);
      if (pHM) {
        if (map.pickup_h && !safeVal(map.pickup_h)) setVal(map.pickup_h, pHM.h);
        if (map.pickup_m && !safeVal(map.pickup_m)) setVal(map.pickup_m, pHM.m);
      }
      const dHM = splitToHM(state.dropoff_time);
      if (dHM) {
        if (map.dropoff_h && !safeVal(map.dropoff_h)) setVal(map.dropoff_h, dHM.h);
        if (map.dropoff_m && !safeVal(map.dropoff_m)) setVal(map.dropoff_m, dHM.m);
      }

      if (addonsHidden && !safeVal(addonsHidden) && state.addons) {
        addonsHidden.value = state.addons;
        try { addonsHidden.dispatchEvent(new Event('change', { bubbles: true })); } catch (_) { }
      }

      computeTimesIntoHidden();
    }

    function pushStateToQS(state) {
      try {
        const url = new URL(window.location.href);
        const p = url.searchParams;

        const keepStep = p.get('step');

        const setIf = (k, v) => {
          if (v !== null && String(v).trim() !== "") p.set(k, String(v).trim());
        };

        setIf('pickup_sucursal_id', state.pickup_sucursal_id);
        setIf('dropoff_sucursal_id', state.dropoff_sucursal_id);

        setIf('pickup_date', state.pickup_date);
        setIf('dropoff_date', state.dropoff_date);

        setIf('pickup_time', state.pickup_time);
        setIf('dropoff_time', state.dropoff_time);

        setIf('pickup_h', state.pickup_h);
        setIf('pickup_m', state.pickup_m);
        setIf('dropoff_h', state.dropoff_h);
        setIf('dropoff_m', state.dropoff_m);

        if (state.addons) setIf('addons', state.addons);

        if (keepStep) p.set('step', keepStep);

        window.history.replaceState({}, document.title, url.pathname + '?' + p.toString() + url.hash);
      } catch (_) { }
    }

    function throttle(fn, wait) {
      let t = null;
      let lastArgs = null;
      return function (...args) {
        lastArgs = args;
        if (t) return;
        t = setTimeout(() => {
          t = null;
          fn.apply(null, lastArgs);
        }, wait);
      };
    }

    const persistNow = throttle(() => {
      const st = currentState();
      writeToLS(st);
      pushStateToQS(st);
    }, 180);

        function hydrate() {
      // 🧼 En modo reset no rellenamos nada desde QS/LS,
      // dejamos los campos tal como vienen del Blade (en blanco)
      if (isResetMode) {
        writeToLS({}); // nos aseguramos de dejar limpio el LS
        return;
      }

      const fromQS = readFromQS();
      const fromLS = readFromLS();
      const merged = mergePreferNew(fromLS, fromQS);

      applyStateToInputs(merged);

      try {
        const p = new URLSearchParams(window.location.search);
        const hasMeaningful =
          p.get('pickup_date') || p.get('dropoff_date') ||
          p.get('pickup_time') || p.get('dropoff_time') ||
          p.get('pickup_sucursal_id') || p.get('dropoff_sucursal_id') ||
          p.get('addons');

        if (!hasMeaningful) pushStateToQS(currentState());
      } catch (_) { }

      writeToLS(currentState());
    }

    const watch = [
      map.pickup_sucursal_id, map.dropoff_sucursal_id,
      map.pickup_date, map.dropoff_date,
      map.pickup_h, map.pickup_m, map.dropoff_h, map.dropoff_m,
      map.pickup_time_hidden, map.dropoff_time_hidden,
      addonsHidden
    ].filter(Boolean);

    watch.forEach(el => {
      el.addEventListener('change', persistNow);
      el.addEventListener('blur', persistNow);
      if (el.tagName === 'INPUT') el.addEventListener('input', persistNow);
    });

    const step1Form = qs('#step1Form');
    if (step1Form) {
      step1Form.addEventListener('submit', () => {
        computeTimesIntoHidden();
        try {
          const st = currentState();
          writeToLS(st);
          pushStateToQS(st);
        } catch (_) { }
      });
    }

    document.addEventListener('wizard:stepChanged', () => {
      hydrate();
      persistNow();
    });

    hydrate();
    persistNow();
  }

  // ==============================
  // 🧽 Normalizador de layout PDF
  // ==============================
  function normalizePdfLayout(root) {
    if (!root) return;

    root.querySelectorAll(
      '.topbar,.wizard-steps,.hamburger,.link,.footer-elegant,.wizard-nav,button,a'
    ).forEach(n => n.remove());

    const widen = [
      '.wizard-page', '.wizard-card', '.sum-table', '.sum-car', '.sum-form',
      '.cats', '.addons'
    ];
    root.querySelectorAll(widen.join(',')).forEach(el => {
      el.style.display = 'block';
      el.style.width = '100%';
      el.style.maxWidth = '100%';
      el.style.margin = '0';
      el.style.borderRadius = '0';
    });

    root.querySelectorAll('.wizard-card,.sum-block,.cat-card,.addon-card').forEach(el => {
      el.style.boxShadow = 'none';
      el.style.border = '1px solid #e5e7eb';
    });

    root.querySelectorAll('img').forEach(img => {
      img.style.maxWidth = '100%';
      img.style.height = 'auto';
      img.style.display = 'block';
    });
  }

  // ==========================================================
  // ✅ Step 4 UI: ISO YYYY-MM-DD → dd-mm-aaaa (solo visual)
  // ==========================================================
  function initStep4DatePretty() {
    function isoToDMY(iso) {
      if (!iso || typeof iso !== 'string') return iso;
      const s = iso.trim();
      const m = s.match(/^(\d{4})-(\d{2})-(\d{2})$/);
      if (!m) return iso;
      return `${m[3]}-${m[2]}-${m[1]}`;
    }

    qsa('.js-date').forEach(el => {
      const iso = el.getAttribute('data-iso') || el.textContent.trim();
      el.textContent = isoToDMY(iso);
    });

    const sumCarInfoP = qs('.sum-car-info p');
    if (sumCarInfoP && sumCarInfoP.innerHTML) {
      sumCarInfoP.innerHTML = sumCarInfoP.innerHTML.replace(
        /(\b\d{4}-\d{2}-\d{2}\b)/g,
        (m) => isoToDMY(m)
      );
    }
  }

    // ==========================================================
// ✅ STEP 4: calcular complementos + IVA + total
//      y pintar tabla de opciones de renta + IVA (16%)
// ==========================================================
function initStep4AddonsSummary() {
  const table = qs('#cotizacionDoc');
  if (!table) return;

  const qBaseEl   = qs('#qBase');
  const qExtrasEl = qs('#qExtras');
  const qIvaEl    = qs('#qIva');
  const qTotalEl  = qs('#qTotal');
  const extrasList = qs('#extrasList');
  const ivaList    = qs('#ivaList');

  if (!qBaseEl || !qExtrasEl || !qIvaEl || !qTotalEl) return;

  // base y días desde los data-*
  const base = parseFloat(table.dataset.base || '0') || 0;
  const days = parseInt(table.dataset.days || '1', 10) || 1;

  // addons: string "id:cantidad,id2:cantidad..."
  const hiddenPayload = qs('#addons_payload');
  const hiddenAlt     = qs('#addonsHidden');
  const rawAddons =
    (hiddenPayload && hiddenPayload.value && hiddenPayload.value.trim()) ||
    (hiddenAlt && hiddenAlt.value && hiddenAlt.value.trim()) ||
    '';

  // catálogo de servicios desde el script JSON
  const catalogScript = document.getElementById('addonsCatalog');
  let catalog = {};
  if (catalogScript) {
    try {
      catalog = JSON.parse(catalogScript.textContent || '{}') || {};
    } catch (e) {
      catalog = {};
    }
  }

  function parseAddons(str) {
    const map = new Map();
    String(str || '')
      .split(',')
      .map(s => s.trim())
      .filter(Boolean)
      .forEach(pair => {
        const m = pair.match(/^(\d+)\s*:\s*(\d+)$/);
        if (!m) return;
        const id = m[1];
        const qty = Math.max(0, parseInt(m[2], 10) || 0);
        if (qty > 0) map.set(id, qty);
      });
    return map;
  }

  function fmtMoney(n) {
    return '$' + Math.round(n).toLocaleString('es-MX') + ' MXN';
  }

  const addonsMap = parseAddons(rawAddons);
  let extrasTotal = 0;

  // Limpiar contenedores de detalle
  if (extrasList) extrasList.innerHTML = '';
  if (ivaList) ivaList.innerHTML = '';

  // ==============================
  // 🧮 Construir tabla de opciones de renta
  // ==============================
  if (addonsMap.size === 0) {
    // Sin complementos → mensaje por defecto
    if (extrasList) {
      const row = document.createElement('div');
      row.className = 'row row-empty';
      row.innerHTML = `
        <span class="muted">Sin complementos seleccionados</span>
        <strong>$0 MXN</strong>
      `;
      extrasList.appendChild(row);
    }
    } else {
    // 👉 Sin encabezado tipo tabla: cada adicional en una sola línea de texto
    addonsMap.forEach((qty, id) => {
      const srv = catalog[id];
      if (!srv) return;

      const price = parseFloat(srv.precio ?? srv.price ?? 0) || 0;
      const tipo  = String(srv.tipo || srv.tipo_cobro || '').toLowerCase();

      let lineTotal = 0;
      if (tipo === 'por_evento') {
        lineTotal = price * qty;            // precio * cantidad
      } else {
        lineTotal = price * qty * days;     // por día → precio * cantidad * días
      }

      extrasTotal += lineTotal;

      if (extrasList) {
        const row = document.createElement('div');
        row.className = 'row row-addon';

        const unidadLabel = (tipo === 'por_evento') ? '/ evento' : 'por día';

        // 👇 Aquí va el formato que quieres: cantidad | descripción | precio por día |   …y el total a la derecha
        row.innerHTML = `
          <span style="flex:1;">
            ${qty} | ${srv.nombre} | ${fmtMoney(price)} ${unidadLabel}
          </span>
          <strong style="flex:0 0 110px; text-align:right;">
            ${fmtMoney(lineTotal)}
          </strong>
        `;
        extrasList.appendChild(row);
      }
    });
  }


  // ==============================
  // 🧮 Subtotal, IVA y Total
  // ==============================
  const subtotal = base + extrasTotal;
  const iva = subtotal * 0.16;
  const total = subtotal + iva;

  // Pintar totales en las barras de cada acordeón
  qBaseEl.textContent   = fmtMoney(base);
  qExtrasEl.textContent = fmtMoney(extrasTotal);
  qIvaEl.textContent    = fmtMoney(iva);
  qTotalEl.textContent  = fmtMoney(total);

  // ==============================
  // 🧾 Detalle de IVA (16%) dentro de "Cargos e IVA"
  // ==============================
  if (ivaList) {
    const row = document.createElement('div');
    row.className = 'row row-iva';
    row.innerHTML = `
      <span>IVA (16%)</span>
      <strong>${fmtMoney(iva)}</strong>
    `;
    ivaList.appendChild(row);
  }
}



  // ======================================================
  // ✅ Step 4: Nombre Completo → (nombre + apellido hidden)
  // ======================================================
  function initFullNameSync(){
    const full     = qs('#nombreCompleto');
    const nombre   = qs('#nombreCliente');
    const apellido = qs('#apellidoCliente');

    if (!full || !nombre || !apellido) return;

    const norm = (s)=> String(s || '').trim().replace(/\s+/g,' ');

    function splitFullName(v){
  const s = norm(v);
  if (!s) return { nombre:"", apellido:"" };

  // 👉 Mandamos TODO el texto al campo "nombre" y dejamos "apellido" vacío
  return {
    nombre: s,
    apellido: ""
  };
}


    function syncToHidden(){
      const { nombre: n, apellido: a } = splitFullName(full.value);
      nombre.value   = n;
      apellido.value = a;
      try{ nombre.dispatchEvent(new Event('change', { bubbles:true })); }catch(_){}
      try{ apellido.dispatchEvent(new Event('change', { bubbles:true })); }catch(_){}
    }

    function hydrateFullFromHidden(){
      const n = norm(nombre.value);
      const a = norm(apellido.value);

      if (!norm(full.value) && (n || a)){
        full.value = norm([n, a].filter(Boolean).join(' '));
      }
    }

    hydrateFullFromHidden();
    syncToHidden();

    full.addEventListener('input', syncToHidden);
    full.addEventListener('blur',  syncToHidden);

    nombre.addEventListener('change', hydrateFullFromHidden);
    apellido.addEventListener('change', hydrateFullFromHidden);
  }

  // ======================================================
  // ✅ DOB SELECTS (DD/MM/YYYY) → hidden #dob (YYYY-MM-DD)
  // - años: (hoy - 18) hacia atrás 100 años
  // - ajusta días por mes/año (febrero 28/29)
  // ======================================================
  function initDobSelects(){
    const day   = qs('#dob_day');
    const month = qs('#dob_month');
    const year  = qs('#dob_year');
    const hidden = qs('#dob'); // name="nacimiento" para backend

    // Si no están los selects, no hacemos nada (por si no es Step 4)
    if (!day || !month || !year || !hidden) return;

    function pad2(n){ return String(n).padStart(2,'0'); }

    function daysInMonth(y, m){
      // m: 1..12
      if (!y || !m) return 31;
      return new Date(Number(y), Number(m), 0).getDate();
    }

    function clampDay(){
      const y = year.value;
      const m = month.value;
      const d = day.value;

      if (!m) return;

      const maxD = daysInMonth(y || 2000, m);
      if (d && Number(d) > maxD){
        day.value = pad2(maxD);
      }
    }

    function updateHidden(){
      clampDay();

      const d = day.value;
      const m = month.value;
      const y = year.value;

      if (d && m && y){
        hidden.value = `${y}-${m}-${d}`; // ✅ formato backend
      } else {
        hidden.value = '';
      }

      try{ hidden.dispatchEvent(new Event('change', { bubbles:true })); }catch(_){}
    }

    // ✅ Si ya venía un valor (YYYY-MM-DD) del backend o rehidratado, lo re-partimos
    function hydrateFromHidden(){
      const v = String(hidden.value || '').trim();
      const m = v.match(/^(\d{4})-(\d{2})-(\d{2})$/);
      if (!m) return;

      // solo setea si aún están vacíos
      if (!year.value)  year.value  = m[1];
      if (!month.value) month.value = m[2];
      if (!day.value)   day.value   = m[3];

      updateHidden();
    }

    day.addEventListener('change', updateHidden);
    month.addEventListener('change', updateHidden);
    year.addEventListener('change', updateHidden);

    hydrateFromHidden();
    updateHidden();
  }

  // ==========================================================
  // ✅ DÍAS + ACTUALIZACIÓN DE PRECIOS EN PASO 2
  // ==========================================================
  function initDaysAndPricesSync() {
    const pickupDate = qs("#start") || qs('input[name="pickup_date"]');
    const dropoffDate = qs("#end") || qs('input[name="dropoff_date"]');
    const pickupHour = qs('#pickup_h');
    const pickupMin = qs('#pickup_m');
    const dropoffHour = qs('#dropoff_h');
    const dropoffMin = qs('#dropoff_m');

    if (!pickupDate || !dropoffDate) return;

    function parseDateAny(val) {
      if (!val) return null;
      const s = String(val).trim();

      let m = s.match(/^(\d{2})-(\d{2})-(\d{4})$/);
      if (m) return new Date(+m[3], +m[2] - 1, +m[1]);

      m = s.match(/^(\d{4})-(\d{2})-(\d{2})$/);
      if (m) return new Date(+m[1], +m[2] - 1, +m[3]);

      const d = new Date(s);
      return isNaN(d.getTime()) ? null : d;
    }

    function getDateTime(which) {
      const d = parseDateAny(which === "pickup" ? pickupDate.value : dropoffDate.value);
      if (!d) return null;
      const h = which === "pickup" ? pickupHour?.value : dropoffHour?.value;
      const mi = which === "pickup" ? pickupMin?.value : dropoffMin?.value;
      d.setHours(+h || 0, +mi || 0, 0, 0);
      return d;
    }

    function calcDays() {
      const a = getDateTime("pickup");
      const b = getDateTime("dropoff");
      if (!a || !b) return 1;
      const diff = b - a;
      if (diff <= 0) return 1;
      return Math.ceil(diff / (24 * 60 * 60 * 1000));
    }

    function runUpdate() {
      const days = calcDays();

      const daysLabel = qs('#daysLabel');
      if (daysLabel) daysLabel.textContent = days;

      qsa(".js-days").forEach(el => el.textContent = days);

      qsa('.car-card').forEach(card => {
        const prepagoDia = parseFloat(card.getAttribute('data-prepago-dia') || '0') || 0;
        const mostradorDia = parseFloat(card.getAttribute('data-mostrador-dia') || '0') || 0;

        const prepagoTotal = prepagoDia * days;
        const mostradorTotal = mostradorDia * days;

        const fmt = (n) => Math.round(n).toLocaleString('es-MX');

        const elPrepTotal = qs('.js-prepago-total', card);
        const elMostTotal = qs('.js-mostrador-total', card);
        const elPrepDia = qs('.js-prepago-dia', card);
        const elMostDia = qs('.js-mostrador-dia', card);

        if (elPrepDia) elPrepDia.textContent = fmt(prepagoDia);
        if (elMostDia) elMostDia.textContent = fmt(mostradorDia);
        if (elPrepTotal) elPrepTotal.textContent = fmt(prepagoTotal);
        if (elMostTotal) elMostTotal.textContent = fmt(mostradorTotal);
      });

      const qDays = qs('#qDays');
      if (qDays) qDays.textContent = days;
    }

    [pickupDate, dropoffDate, pickupHour, pickupMin, dropoffHour, dropoffMin]
      .filter(Boolean)
      .forEach(el => el.addEventListener("change", runUpdate));

    runUpdate();
  }

  // ==========================================================
  // ✅ ADICIONALES: guardar + rehidratar + enviar a backend
  // ==========================================================
  function initAddonsSync() {
    const hidden =
      qs('#addonsHidden') ||
      qs('input[name="addons"]') ||
      qs('input[name="addons_ids"]') ||
      qs('input[name="addonsHidden"]');

    const cards = qsa('.addon-card');
    if (!cards.length || !hidden) return;

    function parseMap(str) {
      const map = new Map();
      (String(str || '').split(',').map(s => s.trim()).filter(Boolean)).forEach(pair => {
        const m = pair.match(/^(\d+)\s*:\s*(\d+)$/);
        if (m) map.set(m[1], Math.max(0, parseInt(m[2], 10) || 0));
        else {
          const id = pair.replace(/\D/g, '');
          if (id) map.set(id, 1);
        }
      });
      return map;
    }

    function serializeMap(map) {
      return Array.from(map.entries())
        .filter(([, q]) => (q || 0) > 0)
        .map(([id, q]) => `${id}:${q}`)
        .join(',');
    }

    function setQty(card, qty) {
      qty = Math.max(0, qty | 0);
      const qtyEl = qs('.qty', card);
      if (qtyEl) qtyEl.textContent = String(qty);
      card.classList.toggle('selected', qty > 0);
    }

    function readQty(card) {
      const qtyEl = qs('.qty', card);
      const q = qtyEl ? parseInt(qtyEl.textContent, 10) : 0;
      return isNaN(q) ? 0 : q;
    }

    function buildFromUI() {
      const map = new Map();
      cards.forEach(card => {
        const id = String(card.getAttribute('data-id') || '').trim();
        if (!id) return;
        const qty = readQty(card);
        if (qty > 0) map.set(id, qty);
      });
      return map;
    }

    function writeHiddenAndURL() {
      const map = buildFromUI();
      const value = serializeMap(map);
      hidden.value = value;
      try { hidden.dispatchEvent(new Event('change', { bubbles: true })); } catch (_) { }

      try {
        const url = new URL(window.location.href);
        url.searchParams.set('addons', value);
        window.history.replaceState({}, document.title, url.toString());
      } catch (_) { }
    }

    function hydrate() {
      const fromQS = (() => { try { return new URLSearchParams(location.search).get('addons') || ''; } catch (_) { return ''; } })();
      const base = fromQS || hidden.value || '';
      const map = parseMap(base);

      cards.forEach(card => {
        const id = String(card.getAttribute('data-id') || '').trim();
        if (!id) return;
        setQty(card, map.get(id) || 0);
      });

      writeHiddenAndURL();
    }

    cards.forEach(card => {
      const plus = qs('.qty-btn.plus', card);
      const minus = qs('.qty-btn.minus', card);

      if (plus) {
        plus.addEventListener('click', () => {
          setQty(card, readQty(card) + 1);
          writeHiddenAndURL();
        });
      }
      if (minus) {
        minus.addEventListener('click', () => {
          setQty(card, readQty(card) - 1);
          writeHiddenAndURL();
        });
      }
    });

    const toStep4 = qs('#toStep4');
    if (toStep4) {
      toStep4.addEventListener('click', (e) => {
        e.preventDefault();
        writeHiddenAndURL();

        const url = new URL(window.location.href);
        url.searchParams.set('step', '4');
        window.location.href = url.toString();
      });
    }

    document.addEventListener('wizard:stepChanged', hydrate);
    hydrate();
  }

    // ==========================================================
  // 🧽 Botón LIMPIAR (Step 1)
  // - Limpia campos
  // - Borra estado persistido
  // - Recarga con ?step=1&reset=1
  // ==========================================================
  function initStep1ClearButton() {
    // ⚠️ Asegúrate que el botón de limpiar tenga este id en el Blade
    const btnClear = qs('#btnLimpiar');
    if (!btnClear) return;

    btnClear.addEventListener('click', function (e) {
      e.preventDefault();

      // 1) Limpiar localStorage del flujo de reservaciones
      try {
        localStorage.removeItem("viajero_resv_filters_v1");
      } catch (_) { }

      // 2) Limpiar parámetros de renta de la URL
      try {
        const url = new URL(window.location.href);
        const p = url.searchParams;

        [
          'pickup_sucursal_id',
          'dropoff_sucursal_id',
          'pickup_date',
          'dropoff_date',
          'pickup_time',
          'dropoff_time',
          'pickup_h',
          'pickup_m',
          'dropoff_h',
          'dropoff_m',
          'addons',
          'categoria_id',
          'plan'
        ].forEach(k => p.delete(k));

        // 3) Forzamos un inicio fresco en Step 1 con flag de reset
        p.set('step', '1');
        p.set('reset', '1');

        // Recargamos la página con la URL “limpia”
        window.location.href = url.pathname + '?' + p.toString() + url.hash;
      } catch (_) {
        // Si algo truena, al menos hacemos un reset del formulario
        const step1Form = document.getElementById('step1Form');
        if (step1Form) step1Form.reset();
      }
    });
  }

  // ======================================================
  // 🔥 Floating labels
  // ======================================================
  function initFloatingLabels() {
    const floats = qsa('[data-float]');
    if (!floats.length) return;

    const hasValue = (el) => {
      if (!el) return false;
      const v = (el.value ?? "").toString().trim();
      return v !== "" && v !== "H" && v !== "Min";
    };

    function setState(ctl, filled) {
      ctl.classList.toggle('filled', !!filled);
      ctl.classList.toggle('pristine', !filled);
    }

    floats.forEach(ctl => {
      const input = qs('[data-float-input]', ctl);
      const select = qs('[data-float-select]', ctl);

      if (input) setState(ctl, hasValue(input));
      if (select) setState(ctl, hasValue(select));

      if (input) {
        input.addEventListener('focus', () => ctl.classList.remove('pristine'));
        input.addEventListener('input', () => setState(ctl, hasValue(input)));
        input.addEventListener('change', () => setState(ctl, hasValue(input)));
        input.addEventListener('blur', () => setState(ctl, hasValue(input)));
      }

      if (select) {
        select.addEventListener('focus', () => ctl.classList.remove('pristine'));
        select.addEventListener('change', () => setState(ctl, hasValue(select)));
        select.addEventListener('blur', () => setState(ctl, hasValue(select)));
      }
    });
  }

  // ==========================================================
  // ✅ FLATPICKR: REGLAS 100% (anti doble-init)
  // - DOB removido porque ahora son selects DD/MM/YYYY
  // ==========================================================
  function initFlatpickrRules() {
    if (!window.flatpickr) return;

    try {
      if (window.flatpickr?.l10ns?.es) window.flatpickr.localize(window.flatpickr.l10ns.es);
    } catch (_) { }

    const start = qs("#start");
    const end = qs("#end");

    const today = new Date();
    today.setHours(0, 0, 0, 0);

    // ---------- Step 1 pickers ----------
    if (!start || !end) return;

    function toDateAtMidnight(d) {
      const x = new Date(d.getTime());
      x.setHours(0, 0, 0, 0);
      return x;
    }

    function parseAnyToDate(val) {
      if (!val) return null;
      const s = String(val).trim();

      let m = s.match(/^(\d{2})-(\d{2})-(\d{4})$/);
      if (m) return new Date(+m[3], +m[2] - 1, +m[1]);

      m = s.match(/^(\d{4})-(\d{2})-(\d{2})$/);
      if (m) return new Date(+m[1], +m[2] - 1, +m[3]);

      const d = new Date(s);
      return isNaN(d.getTime()) ? null : d;
    }

    try { if (start._flatpickr) start._flatpickr.destroy(); } catch (_) { }
    try { if (end._flatpickr) end._flatpickr.destroy(); } catch (_) { }

    const baseCfg = { dateFormat: "d-m-Y", allowInput: true, disableMobile: true };

    const startFp = window.flatpickr(start, { ...baseCfg });
    const endFp = window.flatpickr(end, { ...baseCfg });

    const startInit = parseAnyToDate(start.value);
    const endInit = parseAnyToDate(end.value);

    if (startInit) startFp.setDate(startInit, false);
    if (endInit) endFp.setDate(endInit, false);

    // ✅ solo pasado bloqueado (sin limitar futuro)
    startFp.set("minDate", today);
    endFp.set("minDate", today);

    const jumpToCurrentMonth = (fp) => {
      const d = fp.selectedDates?.[0] || today;
      fp.jumpToDate(d, true);
    };
    startFp.set("onOpen", [() => jumpToCurrentMonth(startFp)]);
    endFp.set("onOpen", [() => jumpToCurrentMonth(endFp)]);

    let lock = false;

    function applyConstraintsAndFix() {
      if (lock) return;
      lock = true;

      const sRaw = startFp.selectedDates?.[0] || null;
      const eRaw = endFp.selectedDates?.[0] || null;

      const s = sRaw ? toDateAtMidnight(sRaw) : null;
      const e = eRaw ? toDateAtMidnight(eRaw) : null;

      if (s && s < today) startFp.setDate(today, false);
      if (e && e < today) endFp.setDate(today, false);

      const s2 = startFp.selectedDates?.[0] ? toDateAtMidnight(startFp.selectedDates[0]) : null;
      const e2 = endFp.selectedDates?.[0] ? toDateAtMidnight(endFp.selectedDates[0]) : null;

      endFp.set("minDate", s2 || today);

      if (s2 && e2 && s2.getTime() > e2.getTime()){
        endFp.setDate(s2, false);
        endFp.set("minDate", s2);
      }

      jumpToCurrentMonth(startFp);
      jumpToCurrentMonth(endFp);

      lock = false;
    }

    startFp.set("onChange", [applyConstraintsAndFix]);
    endFp.set("onChange", [applyConstraintsAndFix]);

    applyConstraintsAndFix();
    start.addEventListener("blur", applyConstraintsAndFix);
    end.addEventListener("blur", applyConstraintsAndFix);
  }

  // ======================================================
  // ✅ BOOT (flatpickr se carga con defer → esperar)
  // ======================================================
  function bootWhenFlatpickrReady() {
    let tries = 0;
    const maxTries = 240; // ~4s
    function tick() {
      tries++;
      if (window.flatpickr) {
        initFlatpickrRules();
        return;
      }
      if (tries < maxTries) requestAnimationFrame(tick);
    }
    tick();
  }

  function refreshFloatStates() {
    qsa('[data-float]').forEach(ctl => {
      const input = qs('[data-float-input]', ctl);
      const select = qs('[data-float-select]', ctl);

      const val = (el) => (el && el.value != null) ? String(el.value).trim() : "";
      const filled =
        (input && val(input) !== "") ||
        (select && val(select) !== "" && val(select) !== "H" && val(select) !== "Min");

      ctl.classList.toggle('filled', filled);
      ctl.classList.toggle('pristine', !filled);
    });
  }

      document.addEventListener("DOMContentLoaded", () => {
    forceStep1WhenOnlyStepParam();

    // ✅ Persistencia
    initWizardStatePersistence();

    // 🧽 Botón LIMPIAR (Step 1)
    initStep1ClearButton();

    initSectionValidators();

    // UI
    initFloatingLabels();
    bootWhenFlatpickrReady();
    initDaysAndPricesSync();
    initAddonsSync();
    initStep4DatePretty();
    initStep4AddonsSummary();  // 👈 AQUÍ

    // ✅ Nombre Completo (Step 4)
    initFullNameSync();

    // ✅ DOB DD/MM/YYYY (Step 4)
    initDobSelects();

    refreshFloatStates();
    setTimeout(refreshFloatStates, 80);
    setTimeout(refreshFloatStates, 250);
  });


})();
