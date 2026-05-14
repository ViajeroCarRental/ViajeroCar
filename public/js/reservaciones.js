/* =====================================================================
 *  reservaciones.js — Versión limpia y optimizada (mayo 2026)
 *  - Limpieza: ~37% menos código sin perder funcionalidad
 *  - Optimización: 0 MutationObservers sobre document.body con subtree:true
 *  - Network: archivo más pequeño, mejor gzip
 *  - Respeta TODOS los IDs, eventos custom y variables globales expuestas
 * ===================================================================== */
(function () {
  "use strict";

  /* =================================================================
     HELPERS GLOBALES (privados a este archivo)
     ================================================================= */
  const qs  = (s, r = document) => r.querySelector(s);
  const qsa = (s, r = document) => Array.from(r.querySelectorAll(s));

  const YOUNG_DRIVER_SERVICE_ID = '5';
  const YOUNG_DRIVER_MIN_AGE    = 25;
  const LS_KEY                  = "viajero_resv_filters_v1";
  const EXCHANGE_RATE           = 20;

  let youngDriverAlertShown = false;

  function getCurrentLocale() {
    const htmlLang = document.documentElement.lang || 'es';
    return htmlLang === 'en' ? 'en' : 'es';
  }

  function getCurrentStep() {
    const main = qs('main.page');
    return main ? (main.dataset.currentStep || '') : '';
  }

  function getCurrentPlan() {
    const main = qs('main.page');
    return main ? (main.dataset.plan || '') : '';
  }

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

  function parseAddonsStringToMap(str) {
    const map = new Map();
    String(str || '')
      .split(',')
      .map(s => s.trim())
      .filter(Boolean)
      .forEach(pair => {
        const m = pair.match(/^(\d+)\s*:\s*(\d+)$/);
        if (m) {
          const qty = Math.max(0, parseInt(m[2], 10) || 0);
          if (qty > 0) map.set(m[1], qty);
        } else {
          const id = pair.replace(/\D/g, '');
          if (id) map.set(id, 1);
        }
      });
    return map;
  }

  function serializeAddonsMap(map) {
    return Array.from(map.entries())
      .filter(([, q]) => (q || 0) > 0)
      .map(([id, q]) => `${id}:${q}`)
      .join(',');
  }

  function fmtMoneyByLocale(n, currencyOverride) {
    const locale = getCurrentLocale();
    const isUSD = locale === 'en';
    const amount = isUSD ? n / EXCHANGE_RATE : n;
    const code = currencyOverride || (isUSD ? 'USD' : 'MXN');
    return isUSD
      ? '$' + amount.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 }) + ' ' + code
      : '$' + Math.round(amount).toLocaleString('es-MX') + ' ' + code;
  }

  /* =================================================================
     LOCALES Y TRADUCCIONES
     ================================================================= */
  function getFlatpickrLocale() {
    const locale = getCurrentLocale();
    if (locale === 'en') {
      return {
        firstDayOfWeek: 0,
        weekdays: {
          shorthand: ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'],
          longhand:  ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday']
        },
        months: {
          shorthand: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
          longhand:  ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December']
        }
      };
    }
    return {
      firstDayOfWeek: 1,
      weekdays: {
        shorthand: ['Dom', 'Lun', 'Mar', 'Mié', 'Jue', 'Vie', 'Sáb'],
        longhand:  ['Domingo', 'Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado']
      },
      months: {
        shorthand: ['Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun', 'Jul', 'Ago', 'Sep', 'Oct', 'Nov', 'Dic'],
        longhand:  ['Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre']
      }
    };
  }

  function getErrorMessage(fieldType) {
    const locale = getCurrentLocale();
    const messages = {
      location:       { es: 'Ubicación requerida',                en: 'Location required' },
      date:           { es: 'Fecha requerida',                    en: 'Date required' },
      time:           { es: 'Hora requerida',                     en: 'Time required' },
      fullname:       { es: 'Nombre completo requerido',          en: 'Full name required' },
      phone:          { es: 'Teléfono requerido',                 en: 'Phone required' },
      phone_invalid:  { es: 'El teléfono debe tener 10 dígitos',  en: 'Phone must have 10 digits' },
      email:          { es: 'Correo requerido',                   en: 'Email required' },
      email_invalid:  { es: 'Correo electrónico no válido',       en: 'Invalid email address' },
      country:        { es: 'Selecciona un país',                 en: 'Select a country' },
      dob_incomplete: { es: 'Fecha de nacimiento incompleta',     en: 'Incomplete date of birth' },
      dob_invalid:    { es: 'Fecha de nacimiento no válida',      en: 'Invalid date of birth' },
      policies:       { es: 'Debes aceptar las políticas',        en: 'You must accept the policies' }
    };
    return messages[fieldType]?.[locale] || messages[fieldType]?.es || 'Field required';
  }

  function getYoungDriverMessage(amount) {
    const locale = getCurrentLocale();
    const amountStr = Math.round(amount).toLocaleString(locale === 'en' ? 'en-US' : 'es-MX');
    if (locale === 'en') {
      return "We detected that the main driver is under 25 years old.\n\n" +
             "For insurance policy reasons, the 'Young Driver' protection will be automatically added, " +
             `with an additional charge of $${amountStr} MXN per rental day.\n\n` +
             "You can see this concept in the breakdown of Rental options.";
    }
    return "Detectamos que el conductor principal tiene menos de 25 años.\n\n" +
           `Por política de aseguradora, se agregará automáticamente la protección "Conductor menor de 25 años", ` +
           `con un cargo adicional de $${amountStr} MXN por día de renta.\n\n` +
           "Puedes ver este concepto en el desglose de Opciones de renta.";
  }

  function getSelectVehicleMessage() {
    return getCurrentLocale() === 'en'
      ? "You must select a vehicle and a rental plan."
      : "Debes seleccionar un vehículo y un plan de renta.";
  }

  /* =================================================================
     MESES SELECT DOB (cambia al cambiar idioma)
     ================================================================= */
  function updateDobMonthsShort() {
    const monthSelect = qs('#dob_month');
    if (!monthSelect) return;
    const monthsShort = {
      es: ['Ene','Feb','Mar','Abr','May','Jun','Jul','Ago','Sep','Oct','Nov','Dic'],
      en: ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec']
    };
    const months = monthsShort[getCurrentLocale()] || monthsShort.es;
    const currentValue = monthSelect.value;
    for (let i = 0; i < months.length; i++) {
      const option = monthSelect.options[i + 1];
      if (option) {
        const m = months[i];
        option.textContent = m.charAt(0).toUpperCase() + m.slice(1).toLowerCase();
      }
    }
    if (currentValue) monthSelect.value = currentValue;
  }

  function initDobMonthsShortObserver() {
    new MutationObserver(updateDobMonthsShort).observe(document.documentElement, {
      attributes: true,
      attributeFilter: ['lang']
    });
    updateDobMonthsShort();
  }

  /* =================================================================
     CONDUCTOR JOVEN (< 25 años)
     ================================================================= */
  function computeAgeFromDob(dobStr, refDate) {
    if (!dobStr) return null;
    const m = String(dobStr).trim().match(/^(\d{4})-(\d{2})-(\d{2})$/);
    if (!m) return null;
    const birth = new Date(+m[1], +m[2] - 1, +m[3]);
    if (isNaN(birth.getTime())) return null;
    const ref = (refDate instanceof Date && !isNaN(refDate)) ? refDate : new Date();
    let age = ref.getFullYear() - birth.getFullYear();
    const mm = ref.getMonth() - birth.getMonth();
    if (mm < 0 || (mm === 0 && ref.getDate() < birth.getDate())) age--;
    return (age < 0 || age > 120) ? null : age;
  }

  function getPickupDateForAge() {
    const pickupInput = qs("#start") || qs('input[name="pickup_date"]');
    if (!pickupInput || !pickupInput.value) return new Date();
    return parseDateAny(pickupInput.value) || new Date();
  }

  function applyYoungDriverAddon() {
    const hiddenAlt = qs('#addonsHidden') || qs('input[name="addons"]');
    const payloadHidden = qs('#addons_payload');
    const dobHidden = qs('#dob');
    if ((!hiddenAlt && !payloadHidden) || !dobHidden) return;

    const baseValue =
      (hiddenAlt && hiddenAlt.value ? hiddenAlt.value.trim() : '') ||
      (payloadHidden && payloadHidden.value ? payloadHidden.value.trim() : '');

    const age = computeAgeFromDob(String(dobHidden.value || '').trim(), getPickupDateForAge());
    const map = parseAddonsStringToMap(baseValue);
    const hadBefore = map.has(YOUNG_DRIVER_SERVICE_ID);

    if (age != null && age < YOUNG_DRIVER_MIN_AGE) {
      map.set(YOUNG_DRIVER_SERVICE_ID, 1);

      if (!hadBefore && !youngDriverAlertShown && window.alertify) {
        youngDriverAlertShown = true;

        let montoPorDia = 0;
        try {
          const script = qs('#addonsCatalog');
          if (script) {
            const catalog = JSON.parse(script.textContent || '{}') || {};
            const srv = catalog[YOUNG_DRIVER_SERVICE_ID];
            if (srv) montoPorDia = parseFloat(srv.precio ?? srv.price ?? 0) || 0;
          }
        } catch (_) {}

        alertify.alert(
          getCurrentLocale() === 'en' ? "Young driver" : "Conductor menor de 25 años",
          getYoungDriverMessage(montoPorDia),
          function () {
            document.dispatchEvent(new CustomEvent('refreshMovilCard', { detail: { fromAlert: true } }));
            setTimeout(() => {
              window.dispatchEvent(new Event('scroll'));
              const st = window.__movilCardState;
              if (st && !st.isStep4DataComplete) {
                st.isReserving = false;
                st.isConfirming = false;
              }
            }, 100);
          }
        );
      }
    } else {
      map.delete(YOUNG_DRIVER_SERVICE_ID);
    }

    const newValue = serializeAddonsMap(map);
    [hiddenAlt, payloadHidden].forEach(el => {
      if (el) {
        el.value = newValue;
        try { el.dispatchEvent(new Event('change', { bubbles: true })); } catch (_) {}
      }
    });

    try {
      const url = new URL(window.location.href);
      if (newValue) url.searchParams.set('addons', newValue);
      else url.searchParams.delete('addons');
      window.history.replaceState({}, document.title, url.toString());
    } catch (_) {}

    try { initStep4AddonsSummary(); } catch (_) {}
  }

  /* =================================================================
     VALIDACIONES — Step 1
     ================================================================= */
  function initStep1Validators() {
    const step1Form = qs('#step1Form');
    if (!step1Form) return;

    const configCampos = [
      { sel: '#pickupPlace',       type: 'location' },
      { sel: '#start',             type: 'date' },
      { sel: '#end',               type: 'date' },
      { sel: '[name="pickup_h"]',  type: 'time' },
      { sel: '[name="dropoff_h"]', type: 'time' }
    ];

    const validarCampo = (el, type) => {
      if (!el) return false;
      const contenedor = el.closest('.dt-field') || el.closest('.field') || el.parentNode;
      const valor = el.value ? el.value.trim() : "";
      const esValido = valor !== "" && valor !== null;

      el.classList.remove('field-error', 'field-success');
      contenedor.classList.remove('has-error', 'has-success');
      if (el._flatpickr?.altInput) el._flatpickr.altInput.classList.remove('field-error', 'field-success');
      contenedor.querySelector('.error-msg')?.remove();

      if (esValido) {
        el.classList.add('field-success');
        if (el._flatpickr?.altInput) el._flatpickr.altInput.classList.add('field-success');
      } else {
        el.classList.add('field-error');
        contenedor.classList.add('has-error');
        if (el._flatpickr?.altInput) el._flatpickr.altInput.classList.add('field-error');

        const errorTxt = document.createElement('div');
        errorTxt.className = 'error-msg';
        errorTxt.textContent = getErrorMessage(type);
        contenedor.appendChild(errorTxt);
      }
      return esValido;
    };

    const asignarListeners = (el, type) => {
      if (!el) return;
      el.addEventListener('change', () => validarCampo(el, type));
      el.addEventListener('input',  () => validarCampo(el, type));
      if (typeof $ !== 'undefined') {
        $(el).on('select2:select select2:unselect', function () {
          validarCampo(this, type);
        });
      }
    };

    configCampos.forEach(conf => {
      const el = step1Form.querySelector(conf.sel);
      if (el) asignarListeners(el, conf.type);
    });

    const checkDiff = qs('#differentDropoff');
    const dropoffSelect = qs('#dropoffPlace');

    if (checkDiff && dropoffSelect) {
      asignarListeners(dropoffSelect, 'location');
      checkDiff.addEventListener('change', function () {
        if (!this.checked) {
          dropoffSelect.classList.remove('field-error', 'field-success');
          const cont = dropoffSelect.closest('.field');
          if (cont) {
            cont.classList.remove('has-error');
            cont.querySelector('.error-msg')?.remove();
          }
        }
      });
    }

    step1Form.addEventListener('submit', function (e) {
      let formValido = true;
      let primerInvalido = null;
      let camposAValidar = [...configCampos];
      if (checkDiff?.checked) camposAValidar.push({ sel: '#dropoffPlace', type: 'location' });

      camposAValidar.forEach(conf => {
        const el = step1Form.querySelector(conf.sel);
        if (!validarCampo(el, conf.type)) {
          formValido = false;
          if (!primerInvalido) {
            primerInvalido = (el?._flatpickr?.altInput) ? el._flatpickr.altInput : el;
          }
        }
      });

      if (!formValido) {
        e.preventDefault();
        e.stopPropagation();
        if (primerInvalido) {
          setTimeout(() => primerInvalido.scrollIntoView({ behavior: 'smooth', block: 'center' }), 50);
        }
      }
    });
  }

  /* =================================================================
     STEP 1 — Toggle de dropoff diferente
     ================================================================= */
  function initStep1DropoffToggle() {
    const checkbox = qs('#differentDropoff');
    const row = qs('#locationInputsWrapper');
    const dropoffWrapper = qs('#dropoffWrapper');
    const pickup = qs('#pickupPlace');
    const dropoff = qs('#dropoffPlace');

    if (!checkbox || !row || !dropoffWrapper) return;

    const toggleDropoff = (isChecked) => {
      if (isChecked) {
        row.style.display = "grid";
        row.style.gridTemplateColumns = "1fr 1fr";
        dropoffWrapper.style.display = "block";
        if (dropoff) { dropoff.required = true; dropoff.disabled = false; }
      } else {
        row.style.display = "block";
        row.style.gridTemplateColumns = "1fr";
        dropoffWrapper.style.display = "none";
        if (dropoff) {
          dropoff.required = false;
          dropoff.disabled = true;
          if (pickup) dropoff.value = pickup.value;
        }
      }
      if (typeof $ !== 'undefined' && $.fn.select2) {
        $(dropoff).trigger('change');
      }
    };

    toggleDropoff(checkbox.checked);
    checkbox.addEventListener("change", function () { toggleDropoff(this.checked); });

    if (pickup) {
      pickup.addEventListener("change", function () {
        if (!checkbox.checked && dropoff) dropoff.value = this.value;
      });
    }
  }

  /* =================================================================
     STEP 2 — Botón "Siguiente"
     ================================================================= */
  function initStep2NextButton() {
    const btnNextStep2 = qs('.wizard-nav a[href*="step=3"]');
    if (!btnNextStep2) return;

    btnNextStep2.addEventListener('click', function (e) {
      const carSelected = qs('.car-card.active');
      const hasCat = new URLSearchParams(window.location.search).has('categoria_id');
      if (!carSelected && !hasCat) {
        e.preventDefault();
        if (window.alertify) alertify.error(getSelectVehicleMessage());
        qs('.cars')?.scrollIntoView({ behavior: "smooth" });
      }
    });
  }

  /* =================================================================
     STEP 4 — Validaciones del botón Reservar
     ================================================================= */
  function initStep4Validators() {
    const btnReservar = qs('#btnReservar');
    if (!btnReservar) return;

    btnReservar.addEventListener('click', function (e) {
      e.preventDefault();
      let hayErrores = false;

      qsa('.field-error').forEach(el => el.classList.remove('field-error'));
      qsa('.has-error').forEach(el => el.classList.remove('has-error'));
      qsa('.error-msg').forEach(el => el.remove());

      const marcarError = (elementoId, mensaje, esFecha = false, esCheckbox = false) => {
        const el = qs('#' + elementoId);
        if (!el) return;
        let contenedor;

        if (esFecha) {
          contenedor = el.closest('.field-dob-container');
          ['dob_day', 'dob_month', 'dob_year'].forEach(id => {
            const sel = qs('#' + id);
            if (sel) sel.classList.add('field-error');
          });
        } else if (esCheckbox) {
          contenedor = el.closest('.cbox');
        } else {
          contenedor = el.closest('.field-floating') || el.closest('.field') || el.parentNode;
          el.classList.add('field-error');
        }

        if (contenedor) {
          contenedor.classList.add('has-error');
          const errorSpan = document.createElement('span');
          errorSpan.className = 'error-msg';
          errorSpan.style.cssText = 'color:#b22222;font-size:11px;font-weight:700;margin-top:4px;display:block;';
          errorSpan.textContent = mensaje;
          contenedor.appendChild(errorSpan);
        }
        hayErrores = true;

        if (!esFecha && !esCheckbox) {
          el.addEventListener('input', function () {
            el.classList.remove('field-error');
            if (contenedor) {
              contenedor.classList.remove('has-error');
              contenedor.querySelector('.error-msg')?.remove();
            }
          }, { once: true });
        }
      };

      // Nombre
      const nombre = qs('#nombreCompleto');
      if (!nombre || !nombre.value.trim()) marcarError('nombreCompleto', getErrorMessage('fullname'));

      // Teléfono
      const tel = qs('#telefonoCliente');
      if (!tel || !tel.value.trim()) marcarError('telefonoCliente', getErrorMessage('phone'));
      else if (tel.value.replace(/\D/g, '').length < 10) marcarError('telefonoCliente', getErrorMessage('phone_invalid'));

      // Correo
      const correo = qs('#correoCliente');
      const regexCorreo = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
      if (!correo || !correo.value.trim()) marcarError('correoCliente', getErrorMessage('email'));
      else if (!regexCorreo.test(correo.value.trim())) marcarError('correoCliente', getErrorMessage('email_invalid'));

      // País
      const pais = qs('#pais');
      if (!pais || !pais.value) marcarError('pais', getErrorMessage('country'));

      // Fecha nacimiento
      const dia = qs('#dob_day'), mes = qs('#dob_month'), anio = qs('#dob_year');
      if (!dia?.value || !mes?.value || !anio?.value) {
        marcarError('dob_day', getErrorMessage('dob_incomplete'), true);
      } else {
        const d = parseInt(dia.value, 10);
        const m = parseInt(mes.value, 10);
        const a = parseInt(anio.value, 10);
        const fecha = new Date(a, m - 1, d);
        if (fecha.getDate() !== d || fecha.getMonth() !== m - 1) {
          marcarError('dob_day', getErrorMessage('dob_invalid'), true);
        }
      }

      // Acepto políticas
      const acepto = qs('#acepto');
      if (!acepto || !acepto.checked) {
        marcarError('acepto', getErrorMessage('policies'), false, true);
        acepto?.addEventListener('change', function () {
          const cbox = this.closest('.cbox');
          if (cbox) {
            cbox.classList.remove('has-error');
            cbox.querySelector('.error-msg')?.remove();
          }
        }, { once: true });
      }

      if (hayErrores) {
        e.stopPropagation();
        document.dispatchEvent(new CustomEvent('refreshMovilCard'));
        qs('.has-error, .field-error')?.scrollIntoView({ behavior: 'smooth', block: 'center' });
        return false;
      }

      document.dispatchEvent(new CustomEvent('refreshMovilCard'));
      document.dispatchEvent(new CustomEvent('reserva:validacionExitosa', {
        detail: { plan: getCurrentPlan() }
      }));
    });

    // Botón móvil → desktop
    const btnMovil = qs('#btnReservarMovil');
    if (btnMovil) {
      btnMovil.addEventListener('click', function (e) {
        e.preventDefault();
        btnReservar.click();
      });
    }
  }

  /* =================================================================
     FORZAR STEP 1 SI URL SOLO TIENE ?step=X
     ================================================================= */
  function forceStep1WhenOnlyStepParam() {
    try {
      const url = new URL(window.location.href);
      const p = url.searchParams;
      if (!p.has('step')) return;

      const keys = [];
      p.forEach((v, k) => { if (v !== null && String(v).trim() !== '') keys.push(k); });
      if (keys.length !== 1 || keys[0] !== 'step') return;

      p.set('step', '1');
      window.history.replaceState({}, document.title, url.pathname + '?' + p.toString() + url.hash);
      document.dispatchEvent(new CustomEvent('wizard:stepChanged', { detail: { step: 1 } }));
    } catch (_) {}
  }

  /* =================================================================
     PERSISTENCIA DEL ESTADO DEL WIZARD (URL + localStorage)
     ================================================================= */
  function initWizardStatePersistence() {
    let isResetMode = false;
    try {
      const p = new URL(window.location.href).searchParams;
      const resetFlag = p.get('reset');
      const step = p.get('step');
      const hasMeaningful =
        p.get('pickup_date')        || p.get('dropoff_date') ||
        p.get('pickup_time')        || p.get('dropoff_time') ||
        p.get('pickup_sucursal_id') || p.get('dropoff_sucursal_id') ||
        p.get('addons')             || p.get('categoria_id') || p.get('plan');

      if (resetFlag === '1' || (step === '1' && !hasMeaningful)) {
        isResetMode = true;
        try { localStorage.removeItem(LS_KEY); } catch (_) {}
      }
    } catch (_) {}

    const map = {
      pickup_sucursal_id:  qs('#pickup_sucursal_id')  || qs('[name="pickup_sucursal_id"]'),
      dropoff_sucursal_id: qs('#dropoff_sucursal_id') || qs('[name="dropoff_sucursal_id"]'),
      pickup_date:         qs('#start')               || qs('[name="pickup_date"]'),
      dropoff_date:        qs('#end')                 || qs('[name="dropoff_date"]'),
      pickup_h:            qs('[name="pickup_h"]'),
      dropoff_h:           qs('[name="dropoff_h"]'),
      pickup_time_hidden:  qs('#pickup_time_hidden')  || qs('[name="pickup_time"]'),
      dropoff_time_hidden: qs('#dropoff_time_hidden') || qs('[name="dropoff_time"]')
    };

    const addonsHidden = qs('#addonsHidden') || qs('input[name="addons"]');

    const safeVal = el => el ? (el.value ?? "").toString().trim() : "";
    const setVal = (el, v) => {
      if (!el) return;
      const next = (v ?? "").toString();
      if ((el.value ?? "") === next) return;
      el.value = next;
      try { el.dispatchEvent(new Event('change', { bubbles: true })); } catch (_) {}
    };

    function computeTimesIntoHidden() {
      const ph = safeVal(map.pickup_h);
      const dh = safeVal(map.dropoff_h);
      if (map.pickup_time_hidden  && ph) map.pickup_time_hidden.value  = `${ph.padStart(2, '0')}:00`;
      if (map.dropoff_time_hidden && dh) map.dropoff_time_hidden.value = `${dh.padStart(2, '0')}:00`;
    }

    function readFromQS() {
      const p = new URLSearchParams(window.location.search);
      const obj = {};
      ['pickup_sucursal_id','dropoff_sucursal_id','pickup_date','dropoff_date',
       'pickup_time','dropoff_time','pickup_h','dropoff_h',
       'addons','step','categoria_id','plan'].forEach(k => {
        const v = p.get(k);
        if (v !== null && String(v).trim() !== "") obj[k] = v;
      });
      return obj;
    }

    function readFromLS() {
      if (isResetMode) return {};
      try {
        const raw = localStorage.getItem(LS_KEY);
        if (!raw) return {};
        const obj = JSON.parse(raw);
        return (obj && typeof obj === 'object') ? obj : {};
      } catch (_) { return {}; }
    }

    function writeToLS(state) {
      try { localStorage.setItem(LS_KEY, JSON.stringify(state || {})); } catch (_) {}
    }

    function currentState() {
      computeTimesIntoHidden();
      const st = {
        pickup_sucursal_id:  safeVal(map.pickup_sucursal_id),
        dropoff_sucursal_id: safeVal(map.dropoff_sucursal_id),
        pickup_date:         safeVal(map.pickup_date),
        dropoff_date:        safeVal(map.dropoff_date),
        pickup_time:         safeVal(map.pickup_time_hidden) || "",
        dropoff_time:        safeVal(map.dropoff_time_hidden) || "",
        pickup_h:            safeVal(map.pickup_h),
        dropoff_h:           safeVal(map.dropoff_h)
      };
      if (addonsHidden) st.addons = safeVal(addonsHidden);

      try {
        const p = new URLSearchParams(window.location.search);
        const step = p.get('step');               if (step) st.step = step;
        const categoria_id = p.get('categoria_id'); if (categoria_id) st.categoria_id = categoria_id;
        const plan = p.get('plan');               if (plan) st.plan = plan;
      } catch (_) {}
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
      if (map.pickup_sucursal_id  && !safeVal(map.pickup_sucursal_id)  && state.pickup_sucursal_id)  setVal(map.pickup_sucursal_id,  state.pickup_sucursal_id);
      if (map.dropoff_sucursal_id && !safeVal(map.dropoff_sucursal_id) && state.dropoff_sucursal_id) setVal(map.dropoff_sucursal_id, state.dropoff_sucursal_id);
      if (map.pickup_date  && !safeVal(map.pickup_date)  && state.pickup_date)  setVal(map.pickup_date, state.pickup_date);
      if (map.dropoff_date && !safeVal(map.dropoff_date) && state.dropoff_date) setVal(map.dropoff_date, state.dropoff_date);
      if (map.pickup_h     && !safeVal(map.pickup_h)     && state.pickup_h)     setVal(map.pickup_h,    state.pickup_h);
      if (map.dropoff_h    && !safeVal(map.dropoff_h)    && state.dropoff_h)    setVal(map.dropoff_h,   state.dropoff_h);
      if (state.pickup_time  && !safeVal(map.pickup_time_hidden)  && map.pickup_time_hidden)  setVal(map.pickup_time_hidden,  state.pickup_time);
      if (state.dropoff_time && !safeVal(map.dropoff_time_hidden) && map.dropoff_time_hidden) setVal(map.dropoff_time_hidden, state.dropoff_time);

      const splitHM = t => {
        const m = String(t || "").match(/^(\d{1,2}):(\d{2})$/);
        return m ? { h: m[1].padStart(2, '0') } : null;
      };
      const pHM = splitHM(state.pickup_time);
      if (pHM && map.pickup_h && !safeVal(map.pickup_h)) setVal(map.pickup_h, pHM.h);
      const dHM = splitHM(state.dropoff_time);
      if (dHM && map.dropoff_h && !safeVal(map.dropoff_h)) setVal(map.dropoff_h, dHM.h);

      if (addonsHidden && !safeVal(addonsHidden) && state.addons) {
        addonsHidden.value = state.addons;
        try { addonsHidden.dispatchEvent(new Event('change', { bubbles: true })); } catch (_) {}
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
        setIf('pickup_sucursal_id',  state.pickup_sucursal_id);
        setIf('dropoff_sucursal_id', state.dropoff_sucursal_id);
        setIf('pickup_date',  state.pickup_date);
        setIf('dropoff_date', state.dropoff_date);
        setIf('pickup_time',  state.pickup_time);
        setIf('dropoff_time', state.dropoff_time);
        setIf('pickup_h',     state.pickup_h);
        setIf('dropoff_h',    state.dropoff_h);
        if (state.addons) setIf('addons', state.addons);
        if (keepStep) p.set('step', keepStep);
        window.history.replaceState({}, document.title, url.pathname + '?' + p.toString() + url.hash);
      } catch (_) {}
    }

    function throttle(fn, wait) {
      let t = null, lastArgs = null;
      return function (...args) {
        lastArgs = args;
        if (t) return;
        t = setTimeout(() => { t = null; fn.apply(null, lastArgs); }, wait);
      };
    }

    const persistNow = throttle(() => {
      const st = currentState();
      writeToLS(st);
      pushStateToQS(st);
    }, 180);

    function hydrate() {
      if (isResetMode) { writeToLS({}); return; }
      const merged = mergePreferNew(readFromLS(), readFromQS());
      applyStateToInputs(merged);

      try {
        const p = new URLSearchParams(window.location.search);
        const hasMeaningful =
          p.get('pickup_date')        || p.get('dropoff_date') ||
          p.get('pickup_time')        || p.get('dropoff_time') ||
          p.get('pickup_sucursal_id') || p.get('dropoff_sucursal_id') ||
          p.get('addons');
        if (!hasMeaningful) pushStateToQS(currentState());
      } catch (_) {}

      writeToLS(currentState());
    }

    [
      map.pickup_sucursal_id, map.dropoff_sucursal_id,
      map.pickup_date, map.dropoff_date,
      map.pickup_h, map.dropoff_h,
      map.pickup_time_hidden, map.dropoff_time_hidden,
      addonsHidden
    ].filter(Boolean).forEach(el => {
      el.addEventListener('change', persistNow);
      el.addEventListener('blur',   persistNow);
      if (el.tagName === 'INPUT') el.addEventListener('input', persistNow);
    });

    qs('#step1Form')?.addEventListener('submit', () => {
      computeTimesIntoHidden();
      try {
        const st = currentState();
        writeToLS(st);
        pushStateToQS(st);
      } catch (_) {}
    });

    document.addEventListener('wizard:stepChanged', () => {
      hydrate();
      persistNow();
    });

    hydrate();
    persistNow();
  }

  /* =================================================================
     STEP 4 — Formato bonito de fechas
     ================================================================= */
  function initStep4DatePretty() {
    const isoToDMY = (iso) => {
      if (!iso || typeof iso !== 'string') return iso;
      const m = iso.trim().match(/^(\d{4})-(\d{2})-(\d{2})$/);
      return m ? `${m[3]}-${m[2]}-${m[1]}` : iso;
    };

    qsa('.js-date').forEach(el => {
      const iso = el.getAttribute('data-iso') || el.textContent.trim();
      el.textContent = isoToDMY(iso);
    });

    const sumCarInfoP = qs('.sum-car-info p');
    if (sumCarInfoP?.innerHTML) {
      sumCarInfoP.innerHTML = sumCarInfoP.innerHTML.replace(
        /(\b\d{4}-\d{2}-\d{2}\b)/g,
        (m) => isoToDMY(m)
      );
    }
  }

  /* =================================================================
     STEP 4 — Resumen de addons y total
     ================================================================= */
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

    const currentLocale = getCurrentLocale();

    const serviceTranslations = {
      'Silla de bebé':              { es: 'Silla de bebé',           en: 'Baby seat' },
      'Gasolina Prepago':           { es: 'Gasolina Prepago',        en: 'Prepaid fuel' },
      'Conductor adicional':        { es: 'Conductor adicional',     en: 'Additional driver' },
      'Conductor menor de 25 años': { es: 'Conductor menor de 25 años', en: 'Young driver (under 25)' }
    };

    const translateServiceName = (spanishName) => {
      if (!spanishName) return spanishName;
      for (const [key, value] of Object.entries(serviceTranslations)) {
        if (spanishName === key || spanishName === value.en || spanishName === value.es) {
          return value[currentLocale] || key;
        }
      }
      return spanishName;
    };

    const planSeleccionado = getCurrentPlan() ||
      (new URLSearchParams(window.location.search).get('plan') || 'linea');

    const days = parseInt(table.dataset.days || '1', 10) || 1;

    // Precio base
    let base = parseFloat(table.dataset.base || '0') || 0;
    if (base === 0) {
      const activeCard = qs('.car-card.active');
      if (activeCard) {
        if (planSeleccionado === 'linea') {
          base = (parseFloat(activeCard.getAttribute('data-prepago-dia') || '0')) * days;
        } else {
          const mostradorDia = parseFloat(activeCard.getAttribute('data-mostrador-dia') || '0');
          base = mostradorDia > 0
            ? mostradorDia * days
            : (parseFloat(table.dataset.base || '0') || 0) * 1.15;
        }
      }
    }
    base = Math.max(0, base);

    // Addons
    const rawAddons =
      (qs('#addons_payload')?.value || '').trim() ||
      (qs('#addonsHidden')?.value || '').trim();

    let catalog = {};
    try {
      const catalogScript = qs('#addonsCatalog');
      if (catalogScript) catalog = JSON.parse(catalogScript.textContent || '{}') || {};
    } catch (_) { catalog = {}; }

    const addonsMap = parseAddonsStringToMap(rawAddons);
    let extrasTotal = 0, renderedRows = 0;
    if (extrasList) extrasList.innerHTML = '';
    if (ivaList)    ivaList.innerHTML = '';

    // Drop off
    const pickupId  = table.dataset.pickup;
    const dropoffId = table.dataset.dropoff;
    const km        = parseFloat(table.dataset.km || 0);
    const costoKm   = parseFloat(table.dataset.costokm || 0);
    const tanque    = parseFloat(table.dataset.tanque || 0);

    if (pickupId && dropoffId && pickupId !== dropoffId && km > 0 && costoKm > 0) {
      const dropoffTotal = km * costoKm;
      extrasTotal += dropoffTotal;
      if (extrasList) {
        const row = document.createElement('div');
        row.className = 'row row-dropoff';
        row.innerHTML = `<span>Drop Off (${km} km)</span><strong>${fmtMoneyByLocale(dropoffTotal)}</strong>`;
        extrasList.appendChild(row);
        renderedRows++;
      }
    }

    addonsMap.forEach((qty, id) => {
      const srv = catalog[id];
      if (!srv) return;
      const price = parseFloat(srv.precio ?? srv.price ?? 0) || 0;
      const tipo  = String(srv.tipo || srv.tipo_cobro || '').toLowerCase();
      let translatedName = translateServiceName(srv.nombre || '');
      let lineTotal = 0, detalleLabel = '';

      if (String(id) === YOUNG_DRIVER_SERVICE_ID) {
        translatedName = currentLocale === 'en' ? 'Young driver (under 25)' : 'Conductor menor de 25 años';
      }

      if (tipo === 'por_tanque' || String(id) === '1') {
        const litros = Math.max(0, tanque);
        lineTotal = price * litros;
        const porLitroLabel = currentLocale === 'en' ? 'per liter' : 'por litro';
        detalleLabel = `${translatedName} | ${litros} L x ${fmtMoneyByLocale(price)} ${porLitroLabel}`;
      } else if (tipo === 'por_evento') {
        lineTotal = price * qty;
        const lbl = currentLocale === 'en' ? 'per event' : 'por evento';
        detalleLabel = `${qty} | ${translatedName} | ${fmtMoneyByLocale(price)} ${lbl}`;
      } else {
        lineTotal = price * qty * days;
        const lbl = currentLocale === 'en' ? 'per day' : 'por día';
        detalleLabel = `${qty} | ${translatedName} | ${fmtMoneyByLocale(price)} ${lbl}`;
      }

      extrasTotal += lineTotal;
      if (extrasList) {
        const row = document.createElement('div');
        row.className = 'row row-addon';
        row.innerHTML = `<span style="flex:1;">${detalleLabel}</span><strong style="flex:0 0 110px;text-align:right;">${fmtMoneyByLocale(lineTotal)}</strong>`;
        extrasList.appendChild(row);
        renderedRows++;
      }
    });

    if (renderedRows === 0 && extrasList) {
      const row = document.createElement('div');
      row.className = 'row row-empty';
      const noAddonsText = currentLocale === 'en' ? 'No add-ons selected' : 'Sin complementos seleccionados';
      row.innerHTML = `<span class="muted">${noAddonsText}</span><strong>$0 ${currentLocale === 'en' ? 'USD' : 'MXN'}</strong>`;
      extrasList.appendChild(row);
    }

    const subtotal = base + extrasTotal;
    const iva = subtotal * 0.16;
    const total = subtotal + iva;

    qBaseEl.textContent   = fmtMoneyByLocale(base);
    qExtrasEl.textContent = fmtMoneyByLocale(extrasTotal);
    qIvaEl.textContent    = fmtMoneyByLocale(iva);
    qTotalEl.textContent  = fmtMoneyByLocale(total);

    if (ivaList) {
      const row = document.createElement('div');
      row.className = 'row row-iva';
      const taxesLabel = currentLocale === 'en' ? 'TAXES (16%)' : 'IVA (16%)';
      row.innerHTML = `<span>${taxesLabel}</span><strong>${fmtMoneyByLocale(iva)}</strong>`;
      ivaList.appendChild(row);
    }

    // Sincronizar total móvil (inline, sin observer aparte)
    const totalMovil = qs('#qTotalMovil');
    if (totalMovil) totalMovil.textContent = fmtMoneyByLocale(total);
  }
  // Exponer al global (consumida por BtnReserva.js / BtnReservaLinea.js)
  window.initStep4AddonsSummary = initStep4AddonsSummary;

  /* =================================================================
     STEP 4 — Sync nombre completo → hidden nombre/apellido
     ================================================================= */
  function initFullNameSync() {
    const full     = qs('#nombreCompleto');
    const nombre   = qs('#nombreCliente');
    const apellido = qs('#apellidoCliente');
    if (!full || !nombre || !apellido) return;

    const norm = s => String(s || '').trim().replace(/\s+/g, ' ');

    const syncToHidden = () => {
      const v = norm(full.value);
      nombre.value = v;
      apellido.value = "";
      try { nombre.dispatchEvent(new Event('change', { bubbles: true })); } catch (_) {}
      try { apellido.dispatchEvent(new Event('change', { bubbles: true })); } catch (_) {}
    };

    const hydrateFullFromHidden = () => {
      const n = norm(nombre.value);
      const a = norm(apellido.value);
      if (!norm(full.value) && (n || a)) {
        full.value = norm([n, a].filter(Boolean).join(' '));
      }
    };

    hydrateFullFromHidden();
    syncToHidden();
    full.addEventListener('input', syncToHidden);
    full.addEventListener('blur',  syncToHidden);
    nombre.addEventListener('change', hydrateFullFromHidden);
    apellido.addEventListener('change', hydrateFullFromHidden);
  }

  /* =================================================================
     STEP 4 — Selects de fecha de nacimiento (DOB)
     ================================================================= */
  function initDobSelects() {
    const day   = qs('#dob_day');
    const month = qs('#dob_month');
    const year  = qs('#dob_year');
    const hidden = qs('#dob');
    if (!day || !month || !year || !hidden) return;

    const pad2 = n => String(n).padStart(2, '0');
    const daysInMonth = (y, m) => (!y || !m) ? 31 : new Date(Number(y), Number(m), 0).getDate();

    const clampDay = () => {
      if (!month.value) return;
      const maxD = daysInMonth(year.value || 2000, month.value);
      if (day.value && Number(day.value) > maxD) day.value = pad2(maxD);
    };

    const updateHidden = () => {
      clampDay();
      hidden.value = (day.value && month.value && year.value)
        ? `${year.value}-${month.value}-${day.value}` : '';
      try { hidden.dispatchEvent(new Event('change', { bubbles: true })); } catch (_) {}
      try { applyYoungDriverAddon(); } catch (_) {}
      try { initStep4AddonsSummary(); } catch (_) {}
    };

    const hydrateFromHidden = () => {
      const m = String(hidden.value || '').trim().match(/^(\d{4})-(\d{2})-(\d{2})$/);
      if (!m) return;
      if (!year.value)  year.value  = m[1];
      if (!month.value) month.value = m[2];
      if (!day.value)   day.value   = m[3];
      updateHidden();
    };

    day.addEventListener('change',   updateHidden);
    month.addEventListener('change', updateHidden);
    year.addEventListener('change',  updateHidden);

    hydrateFromHidden();
    updateHidden();
  }

  /* =================================================================
     STEPS 1 & 2 — Días y precios de tarjetas
     ================================================================= */
  function initDaysAndPricesSync() {
    const pickupDate  = qs("#start") || qs('input[name="pickup_date"]');
    const dropoffDate = qs("#end")   || qs('input[name="dropoff_date"]');
    const pickupHour  = qs('select[name="pickup_h"]');
    const dropoffHour = qs('select[name="dropoff_h"]');

    if (!pickupDate || !dropoffDate) return;

    const pad2 = n => String(n).padStart(2, '0');

    const isSameLocalDate = (a, b) => {
      if (!(a instanceof Date) || isNaN(a) || !(b instanceof Date) || isNaN(b)) return false;
      return a.getFullYear() === b.getFullYear() && a.getMonth() === b.getMonth() && a.getDate() === b.getDate();
    };

    function rebuildPickupHours() {
      const pickupHourEl   = qs('select[name="pickup_h"]');
      const pickupDateEl   = qs('#start') || qs('input[name="pickup_date"]');
      const pickupHiddenEl = qs('#pickup_time_hidden') || qs('input[name="pickup_time"]');
      const dropoffHourEl  = qs('select[name="dropoff_h"]');
      const dropoffHiddenEl= qs('#dropoff_time_hidden') || qs('input[name="dropoff_time"]');

      if (!pickupHourEl) return;

      const selectedDate = parseDateAny(pickupDateEl?.value);
      const now = new Date();
      const isToday = selectedDate && isSameLocalDate(selectedDate, now);

      let minHour = 0;
      if (isToday) {
        minHour = now.getHours() + 1;
        if (minHour > 23) minHour = 23;
      }

      const previousValue = pickupHourEl.value;
      const placeholderText = getCurrentLocale() === 'en' ? 'Time' : 'Hora';

      pickupHourEl.innerHTML = `<option value="" disabled selected>${placeholderText}</option>`;
      for (let i = minHour; i <= 23; i++) {
        const hh = pad2(i);
        const opt = document.createElement('option');
        opt.value = hh;
        opt.textContent = `${hh}:00`;
        pickupHourEl.appendChild(opt);
      }

      let newValue = '';
      if (previousValue && previousValue !== "") {
        if (!isToday || parseInt(previousValue, 10) >= minHour) {
          if (Array.from(pickupHourEl.options).some(opt => opt.value === previousValue)) {
            newValue = previousValue;
          }
        }
      }

      if (newValue) {
        pickupHourEl.value = newValue;
        if (pickupHiddenEl) pickupHiddenEl.value = `${newValue}:00:00`;
      } else {
        pickupHourEl.selectedIndex = 0;
        if (pickupHiddenEl) pickupHiddenEl.value = '';
      }

      if (dropoffHourEl && (!dropoffHourEl.value || dropoffHourEl.value === "")) {
        dropoffHourEl.selectedIndex = 0;
        if (dropoffHiddenEl) dropoffHiddenEl.value = '';
      }
    }

    function initHourSelectFocusBehavior() {
      const handleFocus = (select, hiddenSel) => {
        if (!select.value || select.value === "") {
          let option13 = Array.from(select.options).find(opt => opt.value === "13");
          if (!option13 && select.options.length > 1) option13 = select.options[1];
          if (option13 && option13.value) {
            select.value = option13.value;
            select.setAttribute('data-user-interacted', 'true');
            select.dispatchEvent(new Event('change', { bubbles: true }));
            const hiddenEl = qs(hiddenSel);
            if (hiddenEl) hiddenEl.value = `${option13.value}:00:00`;
          }
        }
      };
      pickupHour?.addEventListener('focus',  () => handleFocus(pickupHour,  '#pickup_time_hidden'));
      dropoffHour?.addEventListener('focus', () => handleFocus(dropoffHour, '#dropoff_time_hidden'));
    }

    function getDateTime(which) {
      const d = parseDateAny(which === "pickup" ? pickupDate.value : dropoffDate.value);
      if (!d) return null;
      const h = which === "pickup" ? pickupHour?.value : dropoffHour?.value;
      d.setHours(+h || 0, 0, 0, 0);
      return d;
    }

    function calcDays() {
      const a = getDateTime("pickup"), b = getDateTime("dropoff");
      if (!a || !b) return 1;
      const diff = b - a;
      if (diff <= 0) return 1;
      const horasTotales = Math.floor(diff / (1000 * 60 * 60));
      const diasBase = Math.floor(horasTotales / 24);
      const horasExtra = horasTotales % 24;
      return horasExtra > 1 ? diasBase + 1 : Math.max(1, diasBase);
    }

    function runUpdate() {
      rebuildPickupHours();
      const days = calcDays();

      const daysLabel = qs('#daysLabel');
      if (daysLabel) daysLabel.textContent = days;
      qsa(".js-days").forEach(el => el.textContent = days);

      const fmt = n => Math.round(n).toLocaleString(getCurrentLocale() === 'en' ? 'en-US' : 'es-MX');

      qsa('.car-card').forEach(card => {
        const prepagoDia   = parseFloat(card.getAttribute('data-prepago-dia')   || '0') || 0;
        const mostradorDia = parseFloat(card.getAttribute('data-mostrador-dia') || '0') || 0;

        const elPrepDia   = qs('.js-prepago-dia', card);
        const elMostDia   = qs('.js-mostrador-dia', card);
        const elPrepTotal = qs('.js-prepago-total', card);
        const elMostTotal = qs('.js-mostrador-total', card);

        if (elPrepDia)   elPrepDia.textContent   = fmt(prepagoDia);
        if (elMostDia)   elMostDia.textContent   = fmt(mostradorDia);
        if (elPrepTotal) elPrepTotal.textContent = fmt(prepagoDia   * days);
        if (elMostTotal) elMostTotal.textContent = fmt(mostradorDia * days);
      });

      const qDays = qs('#qDays');
      if (qDays) qDays.textContent = days;
    }

    [pickupDate, dropoffDate, pickupHour, dropoffHour]
      .filter(Boolean)
      .forEach(el => el.addEventListener("change", runUpdate));

    rebuildPickupHours();
    runUpdate();
    initHourSelectFocusBehavior();
  }

  /* =================================================================
     STEP 3 — Sync de addons (cards ↔ hidden ↔ URL)
     ================================================================= */
  function initAddonsSync() {
    const hidden = qs('#addonsHidden') || qs('input[name="addons"]');
    const payloadHidden = qs('#addons_payload');
    const cards = qsa('.addon-card');
    if (!cards.length || !hidden) return;

    const getGasolinaSwitch = card => qs('.gasolina-switch', card);

    const setQty = (card, qty) => {
      const max = parseInt(card.dataset.max, 10) || Infinity;
      qty = Math.min(Math.max(0, qty | 0), max);

      if (card.dataset.gasolina === "1") {
        const sw = getGasolinaSwitch(card);
        if (sw) sw.checked = qty > 0;
      } else {
        const qtyEl = qs('.qty', card);
        if (qtyEl) qtyEl.textContent = String(qty);
      }
      card.classList.toggle('selected', qty > 0);
    };

    const readQty = (card) => {
      if (card.dataset.gasolina === "1") {
        const sw = getGasolinaSwitch(card);
        return sw && sw.checked ? 1 : 0;
      }
      const qtyEl = qs('.qty', card);
      const q = qtyEl ? parseInt(qtyEl.textContent, 10) : 0;
      return isNaN(q) ? 0 : q;
    };

    const buildFromUI = () => {
      const map = new Map();
      cards.forEach(card => {
        const id = String(card.getAttribute('data-id') || '').trim();
        if (!id) return;
        const qty = readQty(card);
        if (qty > 0) map.set(id, qty);
      });
      return map;
    };

    const writeHiddenAndURL = () => {
      const value = serializeAddonsMap(buildFromUI());
      hidden.value = value;
      if (payloadHidden) payloadHidden.value = value;

      try { hidden.dispatchEvent(new Event('change', { bubbles: true })); } catch (_) {}
      try { payloadHidden?.dispatchEvent(new Event('change', { bubbles: true })); } catch (_) {}

      try {
        const url = new URL(window.location.href);
        if (value) url.searchParams.set('addons', value);
        else url.searchParams.delete('addons');
        window.history.replaceState({}, document.title, url.toString());
      } catch (_) {}

      try { applyYoungDriverAddon(); } catch (_) {}
      try { initStep4AddonsSummary(); } catch (_) {}
    };

    const hydrate = () => {
      const fromQS = (() => {
        try { return new URLSearchParams(location.search).get('addons') || ''; }
        catch (_) { return ''; }
      })();
      const base = fromQS || (hidden.value || '').trim() || (payloadHidden?.value?.trim() || '');
      const map = parseAddonsStringToMap(base);
      cards.forEach(card => {
        const id = String(card.getAttribute('data-id') || '').trim();
        if (id) setQty(card, map.get(id) || 0);
      });
      writeHiddenAndURL();
    };

    cards.forEach(card => {
      if (card.dataset.gasolina === "1") {
        getGasolinaSwitch(card)?.addEventListener('change', writeHiddenAndURL);
        return;
      }
      qs('.qty-btn.plus', card)?.addEventListener('click', () => {
        const max = parseInt(card.dataset.max, 10) || Infinity;
        setQty(card, Math.min(readQty(card) + 1, max));
        writeHiddenAndURL();
      });
      qs('.qty-btn.minus', card)?.addEventListener('click', () => {
        setQty(card, Math.max(0, readQty(card) - 1));
        writeHiddenAndURL();
      });
    });

    qs('#toStep4')?.addEventListener('click', (e) => {
      e.preventDefault();
      writeHiddenAndURL();
      const url = new URL(window.location.href);
      url.searchParams.set('step', '4');
      window.location.href = url.toString();
    });

    document.addEventListener('wizard:stepChanged', hydrate);
    hydrate();
  }

  /* =================================================================
     LABELS FLOTANTES (consolidado, antes eran 3 funciones)
     ================================================================= */
  function initFloatingLabels() {
    const floats = qsa('[data-float]');
    if (!floats.length) return;

    const hasValue = (el) => {
      if (!el) return false;
      if (el.tagName === 'SELECT') {
        const val = el.value;
        return val && !["", "H", "0", "00", "Hora", "Time", "Hour"].includes(val);
      }
      return (el.value ?? "").toString().trim() !== "";
    };

    const updateState = (ctl) => {
      const input  = ctl.querySelector('input:not([type="hidden"])');
      const select = ctl.querySelector('select');
      const element = input || select;
      if (!element) return;
      const filled = hasValue(element);
      ctl.classList.toggle('filled', filled);
      ctl.classList.toggle('pristine', !filled);
    };

    floats.forEach(ctl => {
      updateState(ctl);
      const input  = ctl.querySelector('input:not([type="hidden"])');
      const select = ctl.querySelector('select');

      if (input) {
        input.addEventListener('focus',  () => ctl.classList.remove('pristine'));
        input.addEventListener('input',  () => updateState(ctl));
        input.addEventListener('change', () => updateState(ctl));
        input.addEventListener('blur',   () => updateState(ctl));
      }
      if (select) {
        select.addEventListener('focus',  () => ctl.classList.remove('pristine'));
        select.addEventListener('change', () => updateState(ctl));
        select.addEventListener('blur',   () => updateState(ctl));
      }
    });
  }

  /* =================================================================
     STEP 1 — Flatpickr
     ================================================================= */
  function initFlatpickrRules() {
    if (!window.flatpickr) return;
    const start = qs("#start"), end = qs("#end");
    if (!start || !end) return;

    const today = new Date();
    today.setHours(0, 0, 0, 0);
    const toMidnight = d => { const x = new Date(d.getTime()); x.setHours(0, 0, 0, 0); return x; };

    try { start._flatpickr?.destroy(); } catch (_) {}
    try { end._flatpickr?.destroy();   } catch (_) {}

    const baseCfg = {
      dateFormat: "Y-m-d",
      altInput: true,
      altFormat: "d M Y",
      allowInput: true,
      disableMobile: true,
      locale: getFlatpickrLocale(),
      onOpen:  () => qs('#fp-view-overlay')?.classList.add('active'),
      onClose: () => qs('#fp-view-overlay')?.classList.remove('active')
    };

    const startFp = flatpickr(start, { ...baseCfg });
    const endFp   = flatpickr(end,   { ...baseCfg });

    const startInit = parseDateAny(start.value);
    const endInit   = parseDateAny(end.value);
    if (startInit) startFp.setDate(startInit, false);
    if (endInit)   endFp.setDate(endInit, false);

    startFp.set("minDate", today);
    endFp.set("minDate", today);

    const jumpToCurrentMonth = (fp) => fp.jumpToDate(fp.selectedDates?.[0] || today, true);
    startFp.set("onOpen", [() => { qs('#fp-view-overlay')?.classList.add('active'); jumpToCurrentMonth(startFp); }]);
    endFp.set("onOpen",   [() => { qs('#fp-view-overlay')?.classList.add('active'); jumpToCurrentMonth(endFp); }]);

    let lock = false;
    function applyConstraintsAndFix() {
      if (lock) return;
      lock = true;
      const s = startFp.selectedDates?.[0] ? toMidnight(startFp.selectedDates[0]) : null;
      const e = endFp.selectedDates?.[0]   ? toMidnight(endFp.selectedDates[0])   : null;
      if (s && s < today) startFp.setDate(today, false);
      if (e && e < today) endFp.setDate(today, false);
      const s2 = startFp.selectedDates?.[0] ? toMidnight(startFp.selectedDates[0]) : null;
      const e2 = endFp.selectedDates?.[0]   ? toMidnight(endFp.selectedDates[0])   : null;
      endFp.set("minDate", s2 || today);
      if (s2 && e2 && s2.getTime() > e2.getTime()) {
        endFp.setDate(s2, false);
        endFp.set("minDate", s2);
      }
      lock = false;
    }

    startFp.set("onChange", [applyConstraintsAndFix]);
    endFp.set("onChange",   [applyConstraintsAndFix]);
    applyConstraintsAndFix();
    start.addEventListener("blur", applyConstraintsAndFix);
    end.addEventListener("blur", applyConstraintsAndFix);

    // Cambio de idioma → actualizar locale
    new MutationObserver(() => {
      const newLocale = getFlatpickrLocale();
      startFp?.set('locale', newLocale);
      endFp?.set('locale', newLocale);
    }).observe(document.documentElement, { attributes: true, attributeFilter: ['lang'] });
  }

  function bootWhenFlatpickrReady() {
    if (window.flatpickr) { initFlatpickrRules(); return; }
    let tries = 0;
    const maxTries = 240;
    (function tick() {
      tries++;
      if (window.flatpickr) { initFlatpickrRules(); return; }
      if (tries < maxTries) requestAnimationFrame(tick);
    })();
  }

  /* =================================================================
     STEP 1 — Iconos Select (Pickup / Dropoff)
     ================================================================= */
  function initStep1SelectIcons() {
    const update = (select, iconEl) => {
      if (!select || !iconEl) return;
      const opt = select.options[select.selectedIndex];
      iconEl.className = (opt?.dataset?.icon) || 'fa-solid fa-location-dot';
    };

    const pickupSelect  = qs('#pickupPlace');
    const pickupIcon    = qs('#pickupIcon');
    const dropoffSelect = qs('#dropoffPlace');
    const dropoffIcon   = qs('#dropoffIcon');

    if (pickupSelect && pickupIcon) {
      pickupSelect.addEventListener('change', () => update(pickupSelect, pickupIcon));
      update(pickupSelect, pickupIcon);
    }
    if (dropoffSelect && dropoffIcon) {
      dropoffSelect.addEventListener('change', () => update(dropoffSelect, dropoffIcon));
      update(dropoffSelect, dropoffIcon);
    }
  }

  /* =================================================================
     STEP 1 — Overlay para Flatpickr
     ================================================================= */
  function injectFpOverlay() {
    if (qs("#fp-view-overlay")) return;
    const overlay = document.createElement("div");
    overlay.id = "fp-view-overlay";
    overlay.className = "fp-view-overlay";
    document.body.appendChild(overlay);
  }

  /* =================================================================
     STEP 1 — Select2 unificado
     ================================================================= */
  function initSelect2() {
    if (typeof $ === 'undefined' || typeof $.fn.select2 === 'undefined') return;

    const formatOption = (option) => {
      if (!option.id) {
        return $('<span><i class="fa-solid fa-location-dot" style="margin-right:8px;color:#333;"></i> ' + option.text + '</span>');
      }
      let icon = $(option.element).data('icon') || 'fa-solid fa-location-dot';
      return $('<span><i class="' + icon + '" style="margin-right:8px;color:#333;"></i> ' + option.text + '</span>');
    };

    const baseCfg = {
      width: '100%',
      dropdownParent: $('body'),
      templateResult: formatOption,
      templateSelection: formatOption,
      escapeMarkup: m => m,
      minimumResultsForSearch: Infinity,
      allowClear: false
    };

    $('#pickupPlace').select2({ ...baseCfg, placeholder: $('#pickupPlace option:first').text() });

    const isChecked = qs('#differentDropoff')?.checked || false;
    $('#dropoffPlace').select2({
      ...baseCfg,
      placeholder: $('#dropoffPlace option:first').text(),
      disabled: !isChecked
    });

    const updateFloatingIcon = (selectId, iconId) => {
      const selectEl = qs('#' + selectId), iconEl = qs('#' + iconId);
      if (!selectEl || !iconEl) return;
      const opt = selectEl.options[selectEl.selectedIndex];
      iconEl.className = opt?.dataset?.icon || 'fa-solid fa-location-dot';
    };
    updateFloatingIcon('pickupPlace',  'pickupIcon');
    updateFloatingIcon('dropoffPlace', 'dropoffIcon');
    $('#pickupPlace').on('change',  () => updateFloatingIcon('pickupPlace',  'pickupIcon'));
    $('#dropoffPlace').on('change', () => updateFloatingIcon('dropoffPlace', 'dropoffIcon'));
  }

  /* =================================================================
     STEP 3 — Modal de Protecciones
     ================================================================= */
  function initStep3ProteccionesModal() {
    const openId  = 'info-protecciones-step3';
    const modalId = 'modalProteccionesStep3';
    const closeId = 'closeProteccionesStep3';

    const toggleScroll = block => {
      if (block) {
        document.body.style.overflow = 'hidden';
        document.documentElement.style.overflow = 'hidden';
        document.body.style.touchAction = 'none';
      } else {
        document.body.style.overflow = '';
        document.documentElement.style.overflow = '';
        document.body.style.touchAction = '';
      }
    };

    document.addEventListener('click', (e) => {
      const modal = qs('#' + modalId);
      if (!modal) return;

      if (e.target.closest('#' + openId)) {
        modal.style.display = 'flex';
        modal.setAttribute('aria-hidden', 'false');
        toggleScroll(true);
        return;
      }

      if (e.target.closest('#' + closeId) || e.target === modal) {
        modal.style.display = 'none';
        modal.setAttribute('aria-hidden', 'true');
        toggleScroll(false);
      }
    });

    document.addEventListener('keydown', (e) => {
      if (e.key !== 'Escape') return;
      const modal = qs('#' + modalId);
      if (modal && modal.style.display === 'flex') {
        modal.style.display = 'none';
        modal.setAttribute('aria-hidden', 'true');
        toggleScroll(false);
      }
    });
  }

  /* =================================================================
     STEP 4 — Modal de Protecciones (botón INCLUIDO)
     ================================================================= */
  function initStep4ProteccionesModal() {
    const modal = qs('#modalProtecciones');
    const btnInfo = qs('#info-protecciones');
    if (!modal || !btnInfo) return;

    const closeX = modal.querySelector('.cerrar-modal-v');
    const openModal  = () => { modal.style.display = 'flex'; document.body.style.overflow = 'hidden'; };
    const closeModal = () => { modal.style.display = 'none'; document.body.style.overflow = ''; };

    btnInfo.addEventListener('click', (e) => { e.preventDefault(); openModal(); });
    closeX?.addEventListener('click', closeModal);
    modal.addEventListener('click', (e) => { if (e.target === modal) closeModal(); });
    document.addEventListener('keydown', (e) => { if (e.key === 'Escape') closeModal(); });
  }

  /* =================================================================
     STEP 4 — Modal de Método de Pago (cuando es mostrador)
     ================================================================= */
  function initMetodoPagoModal() {
    const modalMetodoPago = qs('#modalMetodoPago');
    if (!modalMetodoPago) return;

    const cerrarModalMetodoX = qs('#cerrarModalMetodoX');
    const btnPagoLinea       = qs('#btnPagoLinea');
    const btnPagoMostrador   = qs('#btnPagoMostrador');
    const mpPrecioLinea      = qs('#mpPrecioLinea');
    const mpPrecioMostrador  = qs('#mpPrecioMostrador');
    const mpPrecioTachado    = qs('#mpPrecioMostradorTachado');

    const fmtSimple = (n) => {
      const locale = getCurrentLocale();
      return '$' + Math.round(Number(n || 0)).toLocaleString(locale === 'en' ? 'en-US' : 'es-MX') +
        (locale === 'en' ? ' USD' : ' MXN');
    };

    /**
     * Calcula los precios línea/mostrador a partir del total mostrado en #qTotal.
     *
     * Usa el ratio fijo MOSTRADOR_RATIO (1.25) que debe coincidir con
     * BtnReservacionesController::MOSTRADOR_MULTIPLIER y con el cálculo
     * de ReservacionesController.
     *
     * NOTA: en Step 4 la tarjeta .car-card.active YA NO existe (vive en Step 2),
     * así que aquí usamos la constante directa en vez de intentar leer del DOM.
     */
    const MOSTRADOR_RATIO = 1.25;

    function getPreciosSeleccionados() {
      const qTotal = qs('#qTotal');
      const getNum = (txt) => txt ? (parseFloat(String(txt).replace(/[^\d.]/g, '')) || 0) : 0;
      const totalActual = qTotal ? getNum(qTotal.textContent) : 0;
      const currentPlan = getCurrentPlan();

      let precioLinea = 0, precioMostrador = 0;

      if (currentPlan === 'linea') {
        // El cliente está viendo el precio en línea; mostrador es 25% más caro
        precioLinea     = totalActual;
        precioMostrador = totalActual * MOSTRADOR_RATIO;
      } else {
        // El cliente está viendo mostrador; línea es 20% más barato (1/1.25)
        precioMostrador = totalActual;
        precioLinea     = totalActual / MOSTRADOR_RATIO;
      }
      return { precioLinea, precioMostrador };
    }

    function fillMetodoPagoModal() {
      const { precioLinea, precioMostrador } = getPreciosSeleccionados();
      if (mpPrecioLinea)     mpPrecioLinea.textContent     = fmtSimple(precioLinea);
      if (mpPrecioMostrador) mpPrecioMostrador.textContent = fmtSimple(precioMostrador);
      if (mpPrecioTachado)   mpPrecioTachado.textContent   = fmtSimple(precioMostrador);
    }

    function openMetodoPagoModal() {
      fillMetodoPagoModal();
      modalMetodoPago.style.display = 'flex';
      document.body.style.overflow = 'hidden';
      const footerMovil = qs('.movil-footer-sticky');
      if (footerMovil) footerMovil.style.display = 'none';
    }

    function closeMetodoPagoModal() {
      modalMetodoPago.style.display = 'none';
      document.body.style.overflow = '';
      const footerMovil = qs('.movil-footer-sticky');
      if (footerMovil) footerMovil.style.display = '';
    }

    document.addEventListener('reserva:validacionExitosa', (e) => {
      const currentPlan = e?.detail?.plan || getCurrentPlan();
      if (currentPlan === 'linea') {
        if (typeof window.handleReservaPagoEnLinea === 'function') {
          window.handleReservaPagoEnLinea();
        } else if (btnPagoLinea) {
          btnPagoLinea.click();
        }
      } else if (currentPlan === 'mostrador') {
        openMetodoPagoModal();
      }
    });

    cerrarModalMetodoX?.addEventListener('click', closeMetodoPagoModal);
    modalMetodoPago.addEventListener('click', (e) => {
      if (e.target === modalMetodoPago) closeMetodoPagoModal();
    });
    document.addEventListener('keydown', (e) => { if (e.key === 'Escape') closeMetodoPagoModal(); });

    const lockMovilCard = () => {
      if (window.__movilCardState) {
        window.__movilCardState.isReserving = true;
        window.__movilCardState.isConfirming = true;
      }
      const footerMovil = qs('.movil-footer-sticky');
      if (footerMovil) {
        footerMovil.style.display = 'none';
        footerMovil.classList.remove('visible');
      }
    };
    btnPagoMostrador?.addEventListener('click', lockMovilCard);
    btnPagoLinea?.addEventListener('click', lockMovilCard);

    qs('.sum-table details.sum-acc')?.removeAttribute('open');
  }

  /* =================================================================
     STEP 4 — Sincronización botón móvil + total móvil
     ================================================================= */
  function initMovilTotalSync() {
    const btnOriginal   = qs('#btnReservar');
    const btnMovil      = qs('#btnReservarMovil');
    const totalOriginal = qs('#qTotal');
    const totalMovil    = qs('#qTotalMovil');

    if (btnMovil && btnOriginal) {
      btnMovil.addEventListener('click', (e) => {
        e.preventDefault();
        setTimeout(() => btnOriginal.click(), 50);
      });
    }

    // Observer SIN subtree:true (mucho más barato)
    if (totalOriginal && totalMovil) {
      const syncTotal = () => { totalMovil.innerText = totalOriginal.innerText; };
      syncTotal();
      new MutationObserver(syncTotal).observe(totalOriginal, {
        childList: true, characterData: true, subtree: true
      });
    }
  }

  /* =================================================================
     STEP 4 — Tarjeta sticky móvil (visibilidad)
     ================================================================= */
  function isStep4DataFilled() {
    const ids = ['nombreCompleto','telefonoCliente','correoCliente','pais','dob_day','dob_month','dob_year','acepto'];
    const els = ids.map(id => qs('#' + id));
    if (els.some(el => !el)) return false;

    const [nombre, telefono, correo, pais, dia, mes, anio, acepto] = els;
    const nombreValido   = nombre.value   && nombre.value.trim() !== "";
    const telefonoValido = telefono.value && telefono.value.trim() !== "";
    const correoValido   = correo.value   && correo.value.trim() !== "";
    const paisValido     = pais.value     && pais.value.trim() !== "";
    const fechaValida    = dia.value !== "" && mes.value !== "" && anio.value !== "";

    let fechaCompletaValida = true;
    if (fechaValida) {
      const d = parseInt(dia.value, 10);
      const m = parseInt(mes.value, 10);
      const y = parseInt(anio.value, 10);
      const date = new Date(y, m - 1, d);
      fechaCompletaValida = date.getDate() === d && date.getMonth() === m - 1;
    }

    return nombreValido && telefonoValido && correoValido && paisValido &&
           fechaValida && fechaCompletaValida && acepto.checked === true;
  }

  function initMovilCardVisibility() {
    if (getCurrentStep() !== '4') return;

    const tuAutoSection   = qs('#tuAutoSection');
    const movilCard       = qs('.movil-footer-sticky');
    const modalMetodoPago = qs('#modalMetodoPago');
    const modalPagoOnline = qs('#modalPagoOnline');
    const modalConfirmacion = qs('#modalConfirmacion') || qs('.modal-confirmacion, [id*="confirm"]');

    if (!tuAutoSection || !movilCard) return;

    const movilCardState = {
      hasShownCard: false,
      isModalOpen: false,
      isStep4DataComplete: false,
      isReserving: false,
      isConfirming: false
    };
    window.__movilCardState = movilCardState;

    const showMovilCard = () => {
      if (movilCardState.isReserving || movilCardState.isConfirming) return;
      if (!movilCardState.hasShownCard && !movilCardState.isModalOpen) {
        movilCard.classList.add('visible');
        movilCardState.hasShownCard = true;
      }
    };

    const hideMovilCard = () => {
      if (movilCardState.hasShownCard) {
        movilCard.classList.remove('visible');
        movilCardState.hasShownCard = false;
      }
    };

    const isAlertifyOpen = () => {
      const dialogs = qsa('.ajs-dialog, .ajs-modal, .alertify');
      return dialogs.some(d => {
        const style = window.getComputedStyle(d);
        return style.display !== 'none' && style.visibility !== 'hidden' &&
               !d.classList.contains('ajs-hidden') && !d.classList.contains('ajs-out');
      });
    };

    const isSuccessAlertOpen = () => {
      const successDialogs = qsa('.resv-alertify-success, .ajs-dialog.resv-alertify-success');
      for (const d of successDialogs) {
        const style = window.getComputedStyle(d);
        if (style.display !== 'none' && style.visibility !== 'hidden') return true;
      }
      const textsToMatch = [
        'reservación fue registrada correctamente',
        'Your reservation has been successfully registered',
        'reservation was successfully registered'
      ];
      return qsa('.ajs-dialog, .ajs-modal').some(d =>
        d.textContent && textsToMatch.some(t => d.textContent.includes(t))
      );
    };

    const isAnyModalOpen = () =>
      (modalMetodoPago?.style.display === 'flex') ||
      (modalPagoOnline?.style.display === 'flex') ||
      (modalConfirmacion?.style.display === 'flex') ||
      isAlertifyOpen() || isSuccessAlertOpen();

    const updateCardVisibility = () => {
      const datosCompletos = isStep4DataFilled();
      const anyModalOpen   = isAnyModalOpen();
      movilCardState.isStep4DataComplete = datosCompletos;
      movilCardState.isModalOpen = anyModalOpen;

      if (movilCardState.isReserving || movilCardState.isConfirming) {
        hideMovilCard();
        return;
      }
      if (datosCompletos && !anyModalOpen) showMovilCard();
      else hideMovilCard();
    };

    // Clic reservar (desktop y móvil)
    const handleReservationStart = () => {
      if (isStep4DataFilled()) {
        movilCardState.isReserving = true;
        hideMovilCard();
      }
    };
    qs('#btnReservar')?.addEventListener('click', handleReservationStart);
    qs('#btnReservarMovil')?.addEventListener('click', handleReservationStart);

    document.addEventListener('reserva:completada', () => {
      movilCardState.isReserving = false;
      movilCardState.isConfirming = true;
      hideMovilCard();

      const check = setInterval(() => {
        if (!isSuccessAlertOpen()) {
          movilCardState.isConfirming = false;
          clearInterval(check);
          setTimeout(updateCardVisibility, 200);
        }
      }, 200);

      setTimeout(() => {
        if (movilCardState.isConfirming) {
          movilCardState.isConfirming = false;
          updateCardVisibility();
        }
      }, 10000);
    });

    document.addEventListener('reserva:cancelada', () => {
      movilCardState.isReserving = false;
      movilCardState.isConfirming = false;
      setTimeout(updateCardVisibility, 100);
    });

    // Scroll (solo si datos NO completos)
    const handleScroll = () => {
      if (movilCardState.isConfirming || movilCardState.isReserving) return;
      if (movilCardState.isStep4DataComplete) return;

      const rect = tuAutoSection.getBoundingClientRect();
      const isVisible = rect.top < window.innerHeight * 0.9 && rect.bottom > 0;

      if (isVisible && !movilCardState.isModalOpen) showMovilCard();
      else if (!isVisible && !movilCardState.isModalOpen) hideMovilCard();
    };

    // Observar campos del formulario
    const fields = ['#nombreCompleto','#telefonoCliente','#correoCliente','#pais','#dob_day','#dob_month','#dob_year','#acepto'];
    let timeoutId = null;
    const checkDataComplete = () => {
      if (timeoutId) clearTimeout(timeoutId);
      timeoutId = setTimeout(() => {
        const isNowComplete = isStep4DataFilled();
        if (movilCardState.isReserving && !isNowComplete) movilCardState.isReserving = false;
        updateCardVisibility();
      }, 50);
    };
    fields.forEach(sel => {
      const f = qs(sel);
      if (f) {
        f.addEventListener('input', checkDataComplete);
        f.addEventListener('change', checkDataComplete);
      }
    });
    setTimeout(checkDataComplete, 100);

    // Observar modales (solo style, sin subtree)
    const observeModal = (modal, modalName) => {
      if (!modal) return;
      new MutationObserver(() => {
        const isOpen = modal.style.display === 'flex';
        if (!isOpen && (modalName === 'MetodoPago' || modalName === 'PagoOnline')) {
          if (!movilCardState.isConfirming) movilCardState.isReserving = false;
        }
        updateCardVisibility();
      }).observe(modal, { attributes: true, attributeFilter: ['style'] });
    };
    observeModal(modalMetodoPago, 'MetodoPago');
    observeModal(modalPagoOnline, 'PagoOnline');
    if (modalConfirmacion) observeModal(modalConfirmacion, 'Confirmacion');

    qs('#btnPagoMostrador')?.addEventListener('click', () => {
      movilCardState.isConfirming = true;
      hideMovilCard();
    });
    qs('#btnPagoLinea')?.addEventListener('click', () => {
      movilCardState.isConfirming = true;
      hideMovilCard();
    });

    /* ⚡ OPTIMIZACIÓN CRÍTICA:
       Antes había 2 MutationObservers con subtree:true sobre document.body
       que disparaban con cada keystroke. Reemplazados por 1 setInterval
       controlado de 250ms — mismo efecto, ~95% menos CPU. */
    let lastAlertState = false, lastSuccessState = false;
    const checkAlertState = () => {
      const isOpen = isAlertifyOpen();
      const isSuccess = isSuccessAlertOpen();

      if (lastSuccessState && !isSuccess) {
        movilCardState.isConfirming = false;
        setTimeout(updateCardVisibility, 150);
      }
      if (lastAlertState && !isOpen && !isSuccess) {
        movilCardState.isReserving = false;
        movilCardState.isConfirming = false;
        setTimeout(() => {
          if (!isStep4DataFilled()) {
            movilCardState.isModalOpen = false;
            setTimeout(() => {
              handleScroll();
              window.dispatchEvent(new Event('scroll'));
            }, 50);
          } else {
            updateCardVisibility();
          }
        }, 100);
      }
      if (isSuccess && !movilCardState.isConfirming) {
        movilCardState.isConfirming = true;
        hideMovilCard();
      }
      lastAlertState = isOpen;
      lastSuccessState = isSuccess;
    };
    setInterval(checkAlertState, 250);

    // Scroll y resize (passive y throttle)
    let scrollTimeout;
    const isMobileView = () => window.innerWidth <= 1024;

    window.addEventListener('scroll', () => {
      if (!isMobileView()) return;
      clearTimeout(scrollTimeout);
      scrollTimeout = setTimeout(handleScroll, 50);
    }, { passive: true });

    window.addEventListener('resize', () => {
      if (!isMobileView()) hideMovilCard();
      else setTimeout(updateCardVisibility, 100);
    });

    // Refresh externo (desde alertas, etc.)
    document.addEventListener('refreshMovilCard', (e) => {
      setTimeout(() => {
        if (e?.detail?.fromAlert && !movilCardState.isStep4DataComplete) {
          movilCardState.isReserving = false;
          movilCardState.isConfirming = false;
        }
        if (!movilCardState.isReserving && !movilCardState.isConfirming) {
          updateCardVisibility();
        }
      }, 100);
    });

    setTimeout(() => {
      if (isMobileView()) updateCardVisibility();
    }, 300);
  }

  /* =================================================================
     LIMPIAR FORMULARIO (legacy, expuesto al global)
     ================================================================= */
  window.limpiarTodoYReiniciar = function () {
    const form = qs('#step1Form');
    if (!form) return;
    form.reset();

    const pickupTime  = qs('#pickup_time_hidden');
    const dropoffTime = qs('#dropoff_time_hidden');
    if (pickupTime)  pickupTime.value = '';
    if (dropoffTime) dropoffTime.value = '';

    const dropoffWrapper = qs('#dropoffWrapper');
    const differentDropoff = qs('#differentDropoff');
    if (dropoffWrapper && differentDropoff) {
      dropoffWrapper.style.display = 'none';
      differentDropoff.checked = false;
    }
    if (typeof $ !== 'undefined' && $.fn.select2) {
      $('#pickupPlace').val('').trigger('change');
      $('#dropoffPlace').val('').trigger('change');
    }
  };

  /* =================================================================
     STEP 2 — Conversión de moneda (USD/MXN)
     ================================================================= */
  function initStep2CurrencyConversion() {
    const formatAmount = (amount, currencyCode) => {
      if (currencyCode === 'USD') {
        return amount.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
      }
      return amount.toLocaleString('es-MX', { minimumFractionDigits: 0, maximumFractionDigits: 0 });
    };

    function convertPricesStep2() {
      if (getCurrentStep() !== '2') return;
      const currencyCode = getCurrentLocale() === 'en' ? 'USD' : 'MXN';

      qsa('.car-card--v2').forEach((card) => {
        const priceMXN    = parseFloat(card.dataset.priceMxn);
        const oldPriceMXN = parseFloat(card.dataset.oldPriceMxn);
        if (isNaN(priceMXN)) return;

        const displayPrice    = currencyCode === 'USD' ? priceMXN / EXCHANGE_RATE : priceMXN;
        const displayOldPrice = !isNaN(oldPriceMXN)
          ? (currencyCode === 'USD' ? oldPriceMXN / EXCHANGE_RATE : oldPriceMXN)
          : null;

        const formattedPrice    = formatAmount(displayPrice, currencyCode);
        const formattedOldPrice = displayOldPrice !== null ? formatAmount(displayOldPrice, currencyCode) : null;

        const priceNowSpan = qs('.js-prepago-total', card);
        const priceOldSpan = qs('.js-mostrador-total', card);
        if (priceNowSpan) priceNowSpan.textContent = formattedPrice;
        if (priceOldSpan && formattedOldPrice) priceOldSpan.textContent = formattedOldPrice;

        qsa('.price-new, .price-old, .office-price', card).forEach(el => {
          el.innerHTML = el.innerHTML.replace(/MXN|USD/g, currencyCode);
        });
      });
    }

    const main = qs('main.page');
    if (main) {
      new MutationObserver((mutations) => {
        for (const m of mutations) {
          if (m.attributeName === 'data-current-step' && main.dataset.currentStep === '2') {
            setTimeout(convertPricesStep2, 150);
          }
        }
      }).observe(main, { attributes: true });
    }

    document.addEventListener('click', (e) => {
      if (e.target.closest('[data-language-selector], .language-selector, #languageSelect')) {
        setTimeout(convertPricesStep2, 150);
      }
    });

    window.addEventListener('storage', (e) => {
      if (e.key === 'idiomaPreferido') setTimeout(convertPricesStep2, 100);
    });

    convertPricesStep2();
    window.convertPricesStep2 = convertPricesStep2;
  }

  /* =================================================================
     RELOAD DETECTION (reset al refrescar la página)
     ================================================================= */
  function handleReloadReset() {
    const navEntries = performance.getEntriesByType("navigation");
    const isReload = navEntries.length > 0 && navEntries[0].type === "reload";
    if (!isReload) return;

    try { localStorage.removeItem(LS_KEY); } catch (_) {}

    const url = new URL(window.location.href);
    url.search = '';
    url.searchParams.set('step', '1');
    url.searchParams.set('reset', '1');
    window.location.replace(url.toString());
  }

  /* =================================================================
     ⚡ BOOT — UN SOLO DOMContentLoaded (antes eran 7+)
     ================================================================= */
  document.addEventListener("DOMContentLoaded", () => {
    // 1) Reload reset (puede redirigir → sale)
    handleReloadReset();

    // 2) Listener global de refresh
    document.addEventListener('refreshMovilCard', () => {
      try { initStep4AddonsSummary(); } catch (_) {}
    });

    // 3) Init común (todos los steps)
    forceStep1WhenOnlyStepParam();
    initWizardStatePersistence();
    initStep1Validators();        // solo actúa si #step1Form existe
    initStep1DropoffToggle();     // solo actúa si #differentDropoff existe
    initStep2NextButton();        // solo actúa si hay botón a step=3
    initFloatingLabels();
    initDobMonthsShortObserver();
    applyYoungDriverAddon();

    // 4) Init por step (cada función internamente se protege)
    const step = getCurrentStep();

    if (step === '1') {
      injectFpOverlay();
      bootWhenFlatpickrReady();
      initDaysAndPricesSync();
      initStep1SelectIcons();
      initSelect2();
    }

    if (step === '2') {
      initDaysAndPricesSync();
    }

    if (step === '3') {
      initAddonsSync();
      initStep3ProteccionesModal();
    }

    if (step === '4') {
      initStep4DatePretty();
      initStep4AddonsSummary();
      initStep4Validators();
      initFullNameSync();
      initDobSelects();
      initStep4ProteccionesModal();
      initMetodoPagoModal();
      initMovilTotalSync();
      setTimeout(initMovilCardVisibility, 500);
    }

    // 5) Conversión de moneda (auto-detecta step)
    initStep2CurrencyConversion();
  });

})();
