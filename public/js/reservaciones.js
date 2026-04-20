(function () {
  "use strict";

  // ============================================================
  // FUNCIÓN PARA OBTENER EL IDIOMA ACTUAL
  // ============================================================
  function getCurrentLocale() {
    const htmlLang = document.documentElement.lang || 'es';
    return htmlLang === 'en' ? 'en' : 'es';
  }
  // ============================================================
  // ACTUALIZAR MESES DEL SELECT DE FECHA DE NACIMIENTO
  // ============================================================
  function updateDobMonthsShort() {
    const monthSelect = document.getElementById('dob_month');
    if (!monthSelect) return;

    const locale = getCurrentLocale();

    const monthsShort = {
      es: ['Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun', 'Jul', 'Ago', 'Sep', 'Oct', 'Nov', 'Dic'],
      en: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec']
    };

    const months = monthsShort[locale] || monthsShort.es;
    const currentValue = monthSelect.value;

    for (let i = 0; i < months.length; i++) {
      const option = monthSelect.options[i + 1];
      if (option) {
        const monthName = months[i];
        option.textContent = monthName.charAt(0).toUpperCase() + monthName.slice(1).toLowerCase();
      }
    }

    if (currentValue) monthSelect.value = currentValue;
  }

  // ============================================================
  // OBSERVAR CAMBIOS DE IDIOMA PARA ACTUALIZAR MESES
  // ============================================================
  function initDobMonthsShortObserver() {
    const observer = new MutationObserver(() => updateDobMonthsShort());
    observer.observe(document.documentElement, {
      attributes: true,
      attributeFilter: ['lang']
    });
    updateDobMonthsShort();
  }

  // ============================================================
  // LOCALE PARA FLATPICKR (ESPAÑOL E INGLÉS)
  // ============================================================
  function getFlatpickrLocale() {
    const locale = getCurrentLocale();
    if (locale === 'en') {
      return {
        firstDayOfWeek: 0,
        weekdays: {
          shorthand: ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'],
          longhand: ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday']
        },
        months: {
          shorthand: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
          longhand: ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December']
        }
      };
    }
    return {
      firstDayOfWeek: 1,
      weekdays: {
        shorthand: ['Dom', 'Lun', 'Mar', 'Mié', 'Jue', 'Vie', 'Sáb'],
        longhand: ['Domingo', 'Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado']
      },
      months: {
        shorthand: ['Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun', 'Jul', 'Ago', 'Sep', 'Oct', 'Nov', 'Dic'],
        longhand: ['Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre']
      }
    };
  }

  // ============================================================
  // FUNCIÓN PARA TEXTO DE ERRORES
  // ============================================================
  function getErrorMessage(fieldType) {
    const locale = getCurrentLocale();
    const messages = {
      location: { es: 'Ubicación requerida', en: 'Location required' },
      date: { es: 'Fecha requerida', en: 'Date required' },
      time: { es: 'Hora requerida', en: 'Time required' },
      fullname: { es: 'Nombre completo requerido', en: 'Full name required' },
      phone: { es: 'Teléfono requerido', en: 'Phone required' },
      phone_invalid: { es: 'El teléfono debe tener 10 dígitos', en: 'Phone must have 10 digits' },
      email: { es: 'Correo requerido', en: 'Email required' },
      email_invalid: { es: 'Correo electrónico no válido', en: 'Invalid email address' },
      country: { es: 'Selecciona un país', en: 'Select a country' },
      dob_incomplete: { es: 'Fecha de nacimiento incompleta', en: 'Incomplete date of birth' },
      dob_invalid: { es: 'Fecha de nacimiento no válida', en: 'Invalid date of birth' },
      policies: { es: 'Debes aceptar las políticas', en: 'You must accept the policies' }
    };
    return messages[fieldType]?.[locale] || messages[fieldType]?.es || 'Field required';
  }

  // ============================================================
  // FUNCIÓN PARA MENSAJE DE CONDUCTOR JOVEN
  // ============================================================
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
  // ============================================================
  // FUNCIÓN PARA MENSAJE DE ALERTA DE SELECCIÓN
  // ============================================================
  function getSelectVehicleMessage() {
    const locale = getCurrentLocale();
    if (locale === 'en') {
      return "You must select a vehicle and a rental plan.";
    }
    return "Debes seleccionar un vehículo y un plan de renta.";
  }

  document.addEventListener('DOMContentLoaded', () => {
    document.addEventListener('refreshMovilCard', function () {
      console.log('🔄 Refrescando tarjeta móvil');

      try {
        initStep4AddonsSummary();
      } catch (e) {
        console.log('Error al refrescar resumen:', e);
      }
    });

    const navEntries = performance.getEntriesByType("navigation");
    const isReload = navEntries.length > 0 && navEntries[0].type === "reload";

    if (isReload) {

      console.log("Recarga → reset real");

      // borrar storage
      localStorage.removeItem("viajero_resv_filters_v1");

      const url = new URL(window.location.href);

      // marcar reset para backend
      url.search = '';
      url.searchParams.set('step', '1');
      url.searchParams.set('reset', '1');

      // REDIRECCIÓN REAL (NO replaceState)
      window.location.replace(url.toString());
    }

  });

  const qs = (s, r = document) => r.querySelector(s);
  const qsa = (s, r = document) => Array.from(r.querySelectorAll(s));
  const YOUNG_DRIVER_SERVICE_ID = '5';
  const YOUNG_DRIVER_MIN_AGE = 25;
  let youngDriverAlertShown = false;

  function parseAddonsStringToMap(str) {
    const map = new Map();
    String(str || '')
      .split(',')
      .map(s => s.trim())
      .filter(Boolean)
      .forEach(pair => {
        const m = pair.match(/^(\d+)\s*:\s*(\d+)$/);
        if (m) {
          const id = m[1];
          const qty = Math.max(0, parseInt(m[2], 10) || 0);
          if (qty > 0) map.set(id, qty);
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

  function computeAgeFromDob(dobStr, refDate) {
    if (!dobStr) return null;
    const m = String(dobStr).trim().match(/^(\d{4})-(\d{2})-(\d{2})$/);
    if (!m) return null;

    const birth = new Date(+m[1], +m[2] - 1, +m[3]);
    if (isNaN(birth.getTime())) return null;

    const ref = (refDate instanceof Date && !isNaN(refDate)) ? refDate : new Date();

    let age = ref.getFullYear() - birth.getFullYear();
    const mm = ref.getMonth() - birth.getMonth();
    if (mm < 0 || (mm === 0 && ref.getDate() < birth.getDate())) {
      age--;
    }

    if (age < 0 || age > 120) return null;
    return age;
  }

  function getPickupDateForAge() {
    const pickupInput = qs("#start") || qs('input[name="pickup_date"]');
    if (!pickupInput || !pickupInput.value) return new Date();

    const s = String(pickupInput.value).trim();

    let m = s.match(/^(\d{2})-(\d{2})-(\d{4})$/);
    if (m) return new Date(+m[3], +m[2] - 1, +m[1]);

    m = s.match(/^(\d{4})-(\d{2})-(\d{2})$/);
    if (m) return new Date(+m[1], +m[2] - 1, +m[3]);

    const d = new Date(s);
    return isNaN(d.getTime()) ? new Date() : d;
  }

  function applyYoungDriverAddon() {
    const hiddenAlt =
      qs('#addonsHidden') ||
      qs('input[name="addons"]') ||
      qs('input[name="addons_ids"]') ||
      qs('input[name="addonsHidden"]');

    const payloadHidden = qs('#addons_payload');
    const dobHidden = qs('#dob');

    if ((!hiddenAlt && !payloadHidden) || !dobHidden || !YOUNG_DRIVER_SERVICE_ID) return;

    const baseValue =
      (hiddenAlt && hiddenAlt.value ? hiddenAlt.value.trim() : '') ||
      (payloadHidden && payloadHidden.value ? payloadHidden.value.trim() : '');

    const dobStr = String(dobHidden.value || '').trim();
    const refDate = getPickupDateForAge();
    const age = computeAgeFromDob(dobStr, refDate);

    const map = parseAddonsStringToMap(baseValue);
    const hadBefore = map.has(String(YOUNG_DRIVER_SERVICE_ID));

    if (age != null && age < YOUNG_DRIVER_MIN_AGE) {
      map.set(String(YOUNG_DRIVER_SERVICE_ID), 1);

      if (!hadBefore && !youngDriverAlertShown && window.alertify) {
        youngDriverAlertShown = true;

        let montoPorDia = null;
        try {
          const script = document.getElementById('addonsCatalog');
          if (script) {
            const catalog = JSON.parse(script.textContent || '{}') || {};
            const srv = catalog[String(YOUNG_DRIVER_SERVICE_ID)];
            if (srv) {
              montoPorDia = parseFloat(srv.precio ?? srv.price ?? 0) || 0;
            }
          }
        } catch (_) { }

        const montoStr = (montoPorDia != null)
          ? Math.round(montoPorDia).toLocaleString(getCurrentLocale() === 'en' ? 'en-US' : 'es-MX')
          : 'X';

        const msg = getYoungDriverMessage(montoPorDia || 0);

        alertify.alert(
          getCurrentLocale() === 'en' ? "Young driver" : "Conductor menor de 25 años",
          msg,
          function () {
            console.log('🔔 Alerta cerrada');

            const refreshEvent = new CustomEvent('refreshMovilCard', {
              detail: { fromAlert: true }
            });
            document.dispatchEvent(refreshEvent);

            setTimeout(() => {

              window.dispatchEvent(new Event('scroll'));

              const movilCardState = window.__movilCardState;
              if (movilCardState) {

                if (!movilCardState.isStep4DataComplete) {
                  movilCardState.isReserving = false;
                  movilCardState.isConfirming = false;
                  console.log('🔄 Alerta cerrada con datos incompletos - restaurando modo scroll');
                }
              }
            }, 100);
          }
        );
      }
    } else {
      map.delete(String(YOUNG_DRIVER_SERVICE_ID));
    }

    const newValue = serializeAddonsMap(map);

    if (hiddenAlt) {
      hiddenAlt.value = newValue;
      try {
        hiddenAlt.dispatchEvent(new Event('change', { bubbles: true }));
      } catch (_) { }
    }

    if (payloadHidden) {
      payloadHidden.value = newValue;
      try {
        payloadHidden.dispatchEvent(new Event('change', { bubbles: true }));
      } catch (_) { }
    }

    try {
      const url = new URL(window.location.href);
      if (newValue) {
        url.searchParams.set('addons', newValue);
      } else {
        url.searchParams.delete('addons');
      }
      window.history.replaceState({}, document.title, url.toString());
    } catch (_) { }

    try {
      initStep4AddonsSummary();
    } catch (_) { }

    setTimeout(() => {
      try {
        initStep4AddonsSummary();
      } catch (_) { }
    }, 0);
  }

  function initSectionValidators() {

    const step1Form = document.getElementById('step1Form');
    if (step1Form) {
      const configCampos = [
        { sel: '#pickupPlace', type: 'location' },
        { sel: '#start', type: 'date' },
        { sel: '#end', type: 'date' },
        { sel: '[name="pickup_h"]', type: 'time' },
        { sel: '[name="dropoff_h"]', type: 'time' }
      ];

      const validarCampo = (el, type) => {
        if (!el) return false;

        const contenedor = el.closest('.dt-field') || el.closest('.field') || el.parentNode;
        const valor = el.value ? el.value.trim() : "";
        const esValido = valor !== "" && valor !== null;

        el.classList.remove('field-error', 'field-success');
        contenedor.classList.remove('has-error', 'has-success');

        if (el._flatpickr && el._flatpickr.altInput) {
          el._flatpickr.altInput.classList.remove('field-error', 'field-success');
        }

        const msgPrevio = contenedor.querySelector('.error-msg');
        if (msgPrevio) msgPrevio.remove();

        if (esValido) {
          el.classList.add('field-success');
          if (el._flatpickr && el._flatpickr.altInput) el._flatpickr.altInput.classList.add('field-success');
        } else {
          el.classList.add('field-error');
          contenedor.classList.add('has-error');
          if (el._flatpickr && el._flatpickr.altInput) el._flatpickr.altInput.classList.add('field-error');

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
        el.addEventListener('input', () => validarCampo(el, type));

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

      const checkDiff = document.getElementById('differentDropoff');
      const dropoffSelect = document.getElementById('dropoffPlace');

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
        if (checkDiff && checkDiff.checked) {
          camposAValidar.push({ sel: '#dropoffPlace', type: 'location' });
        }

        camposAValidar.forEach(conf => {
          const el = step1Form.querySelector(conf.sel);
          if (!validarCampo(el, conf.type)) {
            formValido = false;
            if (!primerInvalido) {
              primerInvalido = (el._flatpickr && el._flatpickr.altInput) ? el._flatpickr.altInput : el;
            }
          }
        });

        if (!formValido) {
          e.preventDefault();
          if (primerInvalido) {
            setTimeout(() => {
              primerInvalido.focus();
              primerInvalido.scrollIntoView({ behavior: 'smooth', block: 'center' });
            }, 50);
          }
        }
      });
    }

    const checkbox = document.getElementById("differentDropoff");
    const row = document.getElementById("locationInputsWrapper");
    const dropoffWrapper = document.getElementById("dropoffWrapper");
    const pickup = document.getElementById("pickupPlace");
    const dropoff = document.getElementById("dropoffPlace");

    if (checkbox && row && dropoffWrapper) {
      const toggleDropoff = (isChecked) => {
        if (isChecked) {
          row.style.display = "grid";
          row.style.gridTemplateColumns = "1fr 1fr";
          dropoffWrapper.style.display = "block";
          if (dropoff) {
            dropoff.required = true;
            dropoff.disabled = false;
          }
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

      checkbox.addEventListener("change", function () {
        toggleDropoff(this.checked);
      });

      if (pickup) {
        pickup.addEventListener("change", function () {
          if (!checkbox.checked && dropoff) {
            dropoff.value = this.value;
          }
        });
      }
    }

    const btnNextStep2 = document.querySelector('.wizard-nav a[href*="step=3"]');

    if (btnNextStep2) {
      btnNextStep2.addEventListener('click', function (e) {
        const carSelected = document.querySelector('.car-card.active');
        const urlParams = new URLSearchParams(window.location.search);
        const hasCat = urlParams.has('categoria_id');

        if (!carSelected && !hasCat) {
          e.preventDefault();
          if (window.alertify) alertify.error(getSelectVehicleMessage());
          const carsContainer = document.querySelector('.cars');
          if (carsContainer) carsContainer.scrollIntoView({ behavior: "smooth" });
        }
      });
    }

    // ===== VALIDACIÓN CORREGIDA DEL PASO 4 =====
    const btnReservar = document.getElementById('btnReservar');

    if (btnReservar) {
      btnReservar.addEventListener('click', function (e) {
        e.preventDefault();
        let hayErrores = false;

        document.querySelectorAll('.field-error').forEach(el => el.classList.remove('field-error'));
        document.querySelectorAll('.has-error').forEach(el => el.classList.remove('has-error'));
        document.querySelectorAll('.error-msg').forEach(el => el.remove());

        const marcarError = (elementoId, mensaje, esFecha = false, esCheckbox = false) => {
          const el = document.getElementById(elementoId);
          if (!el) return;

          let contenedor;

          if (esFecha) {
            contenedor = el.closest('.field-dob-container');
            ['dob_day', 'dob_month', 'dob_year'].forEach(id => {
              const select = document.getElementById(id);
              if (select) select.classList.add('field-error');
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
            errorSpan.style.cssText = 'color: #b22222; font-size: 11px; font-weight: 700; margin-top: 4px; display: block;';
            errorSpan.textContent = mensaje;
            contenedor.appendChild(errorSpan);
          }
          hayErrores = true;

          if (!esFecha && !esCheckbox) {
            el.addEventListener('input', function () {
              el.classList.remove('field-error');
              if (contenedor) {
                contenedor.classList.remove('has-error');
                const msg = contenedor.querySelector('.error-msg');
                if (msg) msg.remove();
              }
            }, { once: true });
          }
        };

        const nombre = document.getElementById('nombreCompleto');
        if (!nombre || !nombre.value.trim()) marcarError('nombreCompleto', getErrorMessage('fullname'));

        const tel = document.getElementById('telefonoCliente');
        if (!tel || !tel.value.trim()) {
          marcarError('telefonoCliente', getErrorMessage('phone'));
        } else if (tel.value.replace(/\D/g, '').length < 10) {
          marcarError('telefonoCliente', getErrorMessage('phone_invalid'));
        }

        const correo = document.getElementById('correoCliente');
        const regexCorreo = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (!correo || !correo.value.trim()) {
          marcarError('correoCliente', getErrorMessage('email'));
        } else if (!regexCorreo.test(correo.value.trim())) {
          marcarError('correoCliente', getErrorMessage('email_invalid'));
        }

        const pais = document.getElementById('pais');
        if (!pais || !pais.value) marcarError('pais', getErrorMessage('country'));

        const dia = document.getElementById('dob_day');
        const mes = document.getElementById('dob_month');
        const anio = document.getElementById('dob_year');
        if (!dia || !dia.value || !mes || !mes.value || !anio || !anio.value) {
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

        const acepto = document.getElementById('acepto');
        if (!acepto || !acepto.checked) {
          marcarError('acepto', getErrorMessage('policies'), false, true);
          acepto.addEventListener('change', function () {
            const cbox = this.closest('.cbox');
            if (cbox) {
              cbox.classList.remove('has-error');
              const msg = cbox.querySelector('.error-msg');
              if (msg) msg.remove();
            }
          }, { once: true });
        }

        if (hayErrores) {
          e.stopPropagation();
          document.dispatchEvent(new CustomEvent('refreshMovilCard'));

          const primerError = document.querySelector('.has-error, .field-error');
          if (primerError) {
            primerError.scrollIntoView({ behavior: 'smooth', block: 'center' });
          }
          return false;
        }

        console.log('Validación exitosa');
        document.dispatchEvent(new CustomEvent('refreshMovilCard'));

        const mainEl = document.querySelector('main.page');
        const currentPlan = mainEl ? mainEl.dataset.plan : '';

        document.dispatchEvent(new CustomEvent('reserva:validacionExitosa', {
          detail: { plan: currentPlan }
        }));
      });
    }

    // Sincronizar botón móvil
    const btnMovil = document.getElementById('btnReservarMovil');
    if (btnMovil && btnReservar) {
      btnMovil.addEventListener('click', function (e) {
        e.preventDefault();
        btnReservar.click();
      });
    }

    // FUNCIÓN PARA MARCAR ERROR EN CAMPOS NORMALES
    function marcarError(elemento, mensaje) {
      if (!elemento) return;

      const contenedor = elemento.closest('.field-floating') ||
        elemento.closest('.field-floating-sub') ||
        elemento.closest('.ctl') ||
        elemento.parentNode;

      const msgExistente = contenedor.querySelector('.error-msg');
      if (msgExistente) msgExistente.remove();

      elemento.classList.add('field-error');
      contenedor.classList.add('has-error');

      const errorMsg = document.createElement('span');
      errorMsg.className = 'error-msg';
      errorMsg.style.cssText = 'color: #b22222; font-size: 11px; font-weight: 700; margin-top: 4px; display: block;';
      errorMsg.textContent = mensaje;
      contenedor.appendChild(errorMsg);

      const limpiar = function () {
        elemento.classList.remove('field-error');
        contenedor.classList.remove('has-error');
        const msg = contenedor.querySelector('.error-msg');
        if (msg) msg.remove();
      };

      elemento.addEventListener('input', limpiar, { once: true });
      elemento.addEventListener('change', limpiar, { once: true });
    }

    // FUNCIÓN PARA FECHA
    function marcarErrorFecha(mensaje) {
      const container = document.querySelector('.field-dob-container');
      const dia = document.getElementById('dob_day');
      const mes = document.getElementById('dob_month');
      const año = document.getElementById('dob_year');

      if (!container) return;

      const msgExistente = container.querySelector('.error-msg');
      if (msgExistente) msgExistente.remove();

      container.classList.add('has-error');

      const errorMsg = document.createElement('span');
      errorMsg.className = 'error-msg';
      errorMsg.style.cssText = 'color: #b22222; font-size: 11px; font-weight: 700; margin-top: 4px; display: block;';
      errorMsg.textContent = mensaje;
      container.appendChild(errorMsg);

      const limpiar = function () {
        container.classList.remove('has-error');
        const msg = container.querySelector('.error-msg');
        if (msg) msg.remove();
      };

      [dia, mes, año].forEach(el => {
        if (el) {
          el.addEventListener('change', limpiar, { once: true });
        }
      });
    }

    // FUNCIÓN PARA CHECKBOX
    function marcarErrorCheckbox(mensaje) {
      const checkbox = document.getElementById('acepto');
      const container = checkbox ? checkbox.closest('.cbox') : null;

      if (!checkbox || !container) return;

      const msgExistente = container.querySelector('.error-msg');
      if (msgExistente) msgExistente.remove();

      container.classList.add('has-error');

      const errorMsg = document.createElement('span');
      errorMsg.className = 'error-msg';
      errorMsg.style.cssText = 'color: #b22222; font-size: 11px; font-weight: 700; margin-top: 4px; display: block;';
      errorMsg.textContent = mensaje;
      container.appendChild(errorMsg);

      const limpiar = function () {
        container.classList.remove('has-error');
        const msg = container.querySelector('.error-msg');
        if (msg) msg.remove();
      };

      checkbox.addEventListener('change', limpiar, { once: true });
    }
  }

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

  function initWizardStatePersistence() {
    const url = new URL(window.location.href);
    const isReset = url.searchParams.get('reset') === '1';

    if (isReset) {
      console.log("Reset activo → no persistir nada");
    }
    const LS_KEY = "viajero_resv_filters_v1";
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
      pickup_h: qs('#pickup_h') || qs('[name="pickup_h"]') || qs('[name="pickup_hora"]') || qs('[name="pickup_h"]'),
      dropoff_h: qs('#dropoff_h') || qs('[name="dropoff_h"]') || qs('[name="dropoff_hora"]') || qs('[name="dropoff_h"]'),
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
      const dh = safeVal(map.dropoff_h);

      if (map.pickup_time_hidden && ph) {
        const hh = ph.padStart(2, '0');
        map.pickup_time_hidden.value = `${hh}:00`;
      }

      if (map.dropoff_time_hidden && dh) {
        const hh = dh.padStart(2, '0');
        map.dropoff_time_hidden.value = `${hh}:00`;
      }
    }

    function readFromQS() {
      const p = new URLSearchParams(window.location.search);
      const obj = {};
      [
        'pickup_sucursal_id', 'dropoff_sucursal_id',
        'pickup_date', 'dropoff_date',
        'pickup_time', 'dropoff_time',
        'pickup_h', 'dropoff_h',
        'addons', 'step', 'categoria_id', 'plan'
      ].forEach(k => {
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
        dropoff_h: safeVal(map.dropoff_h),
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
      if (map.dropoff_h && !safeVal(map.dropoff_h) && state.dropoff_h) setVal(map.dropoff_h, state.dropoff_h);
      if (state.pickup_time && !safeVal(map.pickup_time_hidden) && map.pickup_time_hidden) setVal(map.pickup_time_hidden, state.pickup_time);
      if (state.dropoff_time && !safeVal(map.dropoff_time_hidden) && map.dropoff_time_hidden) setVal(map.dropoff_time_hidden, state.dropoff_time);

      function splitToHM(t) {
        const m = String(t || "").match(/^(\d{1,2}):(\d{2})$/);
        if (!m) return null;
        return { h: m[1].padStart(2, '0'), m: m[2].padStart(2, '0') };
      }
      const pHM = splitToHM(state.pickup_time);
      if (pHM && map.pickup_h && !safeVal(map.pickup_h)) setVal(map.pickup_h, pHM.h);

      const dHM = splitToHM(state.dropoff_time);
      if (dHM && map.dropoff_h && !safeVal(map.dropoff_h)) setVal(map.dropoff_h, dHM.h);

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
        setIf('dropoff_h', state.dropoff_h);

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
      if (isResetMode) {
        writeToLS({});
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
      map.pickup_h, map.dropoff_h,
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

  function initStep4AddonsSummary() {
    const table = qs('#cotizacionDoc');
    if (!table) return;

    const qBaseEl = qs('#qBase');
    const qExtrasEl = qs('#qExtras');
    const qIvaEl = qs('#qIva');
    const qTotalEl = qs('#qTotal');
    const extrasList = qs('#extrasList');
    const ivaList = qs('#ivaList');

    if (!qBaseEl || !qExtrasEl || !qIvaEl || !qTotalEl) return;

    const currentLocale = getCurrentLocale(); // 'es' o 'en'

    const serviceTranslations = {
      'Silla de bebé': { es: 'Silla de bebé', en: 'Baby seat' },
      'Gasolina Prepago': { es: 'Gasolina Prepago', en: 'Prepaid fuel' },
      'Conductor adicional': { es: 'Conductor adicional', en: 'Additional driver' },
      'Conductor menor de 25 años': { es: 'Conductor menor de 25 años', en: 'Young driver (under 25)' }
    };

    function translateServiceName(spanishName) {
      if (!spanishName) return spanishName;
      for (const [key, value] of Object.entries(serviceTranslations)) {
        if (spanishName === key || spanishName === value.en || spanishName === value.es) {
          return value[currentLocale] || key;
        }
      }
      return spanishName;
    }

    // ========== DETECTAR PLAN SELECCIONADO ==========
    const main = document.querySelector('main.page');
    const plan = main ? main.dataset.plan : 'linea';
    let planFromURL = 'linea';
    try {
      const urlParams = new URLSearchParams(window.location.search);
      planFromURL = urlParams.get('plan') || 'linea';
    } catch (e) { }

    const planSeleccionado = plan || planFromURL;
    console.log('Plan seleccionado:', planSeleccionado);

    const days = parseInt(table.dataset.days || '1', 10) || 1;

    // ========== PRECIO BASE ==========
    let base = parseFloat(table.dataset.base || '0') || 0;

    if (base === 0) {
      const activeCard = document.querySelector('.car-card.active');
      if (activeCard) {
        if (planSeleccionado === 'linea') {
          const prepagoDia = parseFloat(activeCard.getAttribute('data-prepago-dia') || '0');
          base = prepagoDia * days;
        } else {
          const mostradorDia = parseFloat(activeCard.getAttribute('data-mostrador-dia') || '0');
          if (mostradorDia > 0) {
            base = mostradorDia * days;
          } else {
            const precioLinea = parseFloat(table.dataset.base || '0') || 0;
            base = precioLinea * 1.15;
          }
        }
      }
    }
    base = Math.max(0, base);
    console.log('Precio base calculado:', base);

    const hiddenAlt = qs('#addonsHidden');
    const hiddenPayload = qs('#addons_payload');
    const rawAddons =
      (hiddenPayload && hiddenPayload.value ? hiddenPayload.value.trim() : '') ||
      (hiddenAlt && hiddenAlt.value ? hiddenAlt.value.trim() : '') ||
      '';

    const catalogScript = document.getElementById('addonsCatalog');
    let catalog = {};
    if (catalogScript) {
      try {
        catalog = JSON.parse(catalogScript.textContent || '{}') || {};
      } catch (_) {
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
      const locale = getCurrentLocale();
      const isUSD = locale === 'en';
      const EXCHANGE_RATE = 20;

      let amount = isUSD ? n / EXCHANGE_RATE : n;

      return isUSD
        ? '$' + amount.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 }) + ' USD'
        : '$' + Math.round(amount).toLocaleString('es-MX') + ' MXN';
    }

    const addonsMap = parseAddons(rawAddons);
    let extrasTotal = 0;
    let renderedRows = 0;

    if (extrasList) extrasList.innerHTML = '';
    if (ivaList) ivaList.innerHTML = '';

    // Drop Off
    const pickupId = table.dataset.pickup;
    const dropoffId = table.dataset.dropoff;
    const km = parseFloat(table.dataset.km || 0);
    const costoKm = parseFloat(table.dataset.costokm || 0);
    const tanque = parseFloat(table.dataset.tanque || 0);
    const SERVICE_GASOLINA_ID = '1';

    if (pickupId && dropoffId && pickupId !== dropoffId && km > 0 && costoKm > 0) {
      const dropoffTotal = km * costoKm;
      extrasTotal += dropoffTotal;

      if (extrasList) {
        const row = document.createElement('div');
        row.className = 'row row-dropoff';
        const dropoffLabel = currentLocale === 'en' ? 'Drop Off' : 'Drop Off';
        row.innerHTML = `
        <span>${dropoffLabel} (${km} km)</span>
        <strong>${fmtMoney(dropoffTotal)}</strong>
      `;
        extrasList.appendChild(row);
        renderedRows++;
      }
    }

    addonsMap.forEach((qty, id) => {
      const srv = catalog[id];
      if (!srv) return;

      const price = parseFloat(srv.precio ?? srv.price ?? 0) || 0;
      const tipo = String(srv.tipo || srv.tipo_cobro || '').toLowerCase();

      const spanishName = srv.nombre || '';
      let translatedName = translateServiceName(spanishName);

      let lineTotal = 0;
      let detalleLabel = '';

      if (String(id) === YOUNG_DRIVER_SERVICE_ID) {
        translatedName = currentLocale === 'en' ? 'Young driver (under 25)' : 'Conductor menor de 25 años';
      }

      if (String(id) === SERVICE_GASOLINA_ID) {
        const litros = Math.max(0, tanque);
        lineTotal = price * litros;
        const porLitroLabel = currentLocale === 'en' ? 'per liter' : 'por litro';
        detalleLabel = `${translatedName} | ${litros} L x ${fmtMoney(price)} ${porLitroLabel}`;
      }
      else if (tipo === 'por_tanque') {
        const litros = Math.max(0, tanque);
        lineTotal = price * litros * qty;
        const porLitroLabel = currentLocale === 'en' ? 'per liter' : 'por litro';
        detalleLabel = `${qty} | ${translatedName} | ${litros} L x ${fmtMoney(price)} ${porLitroLabel}`;
      }
      else if (tipo === 'por_evento') {
        lineTotal = price * qty;
        const porEventoLabel = currentLocale === 'en' ? 'per event' : 'por evento';
        detalleLabel = `${qty} | ${translatedName} | ${fmtMoney(price)} ${porEventoLabel}`;
      }
      else {
        lineTotal = price * qty * days;
        const porDiaLabel = currentLocale === 'en' ? 'per day' : 'por día';
        detalleLabel = `${qty} | ${translatedName} | ${fmtMoney(price)} ${porDiaLabel}`;
      }

      extrasTotal += lineTotal;

      if (extrasList) {
        const row = document.createElement('div');
        row.className = 'row row-addon';
        row.innerHTML = `
        <span style="flex:1;">${detalleLabel}</span>
        <strong style="flex:0 0 110px; text-align:right;">${fmtMoney(lineTotal)}</strong>
      `;
        extrasList.appendChild(row);
        renderedRows++;
      }
    });

    if (renderedRows === 0 && extrasList) {
      const row = document.createElement('div');
      row.className = 'row row-empty';
      const noAddonsText = currentLocale === 'en' ? 'No add-ons selected' : 'Sin complementos seleccionados';
      row.innerHTML = `
      <span class="muted">${noAddonsText}</span>
      <strong>$0 ${currentLocale === 'en' ? 'USD' : 'MXN'}</strong>
    `;
      extrasList.appendChild(row);
    }

    const subtotal = base + extrasTotal;
    const iva = subtotal * 0.16;
    const total = subtotal + iva;

    qBaseEl.textContent = fmtMoney(base);
    qExtrasEl.textContent = fmtMoney(extrasTotal);
    qIvaEl.textContent = fmtMoney(iva);
    qTotalEl.textContent = fmtMoney(total);

    if (ivaList) {
      const row = document.createElement('div');
      row.className = 'row row-iva';
      const taxesLabel = currentLocale === 'en' ? 'TAXES (16%)' : 'IVA (16%)';
      row.innerHTML = `
      <span>${taxesLabel}</span>
      <strong>${fmtMoney(iva)}</strong>
    `;
      ivaList.appendChild(row);
    }

    const totalMovil = document.getElementById('qTotalMovil');
    if (totalMovil) {
      totalMovil.textContent = fmtMoney(total);
    }
  }

  function initFullNameSync() {
    const full = qs('#nombreCompleto');
    const nombre = qs('#nombreCliente');
    const apellido = qs('#apellidoCliente');

    if (!full || !nombre || !apellido) return;

    const norm = (s) => String(s || '').trim().replace(/\s+/g, ' ');

    function splitFullName(v) {
      const s = norm(v);
      if (!s) return { nombre: "", apellido: "" };
      return { nombre: s, apellido: "" };
    }

    function syncToHidden() {
      const { nombre: n, apellido: a } = splitFullName(full.value);
      nombre.value = n;
      apellido.value = a;
      try { nombre.dispatchEvent(new Event('change', { bubbles: true })); } catch (_) { }
      try { apellido.dispatchEvent(new Event('change', { bubbles: true })); } catch (_) { }
    }

    function hydrateFullFromHidden() {
      const n = norm(nombre.value);
      const a = norm(apellido.value);
      if (!norm(full.value) && (n || a)) {
        full.value = norm([n, a].filter(Boolean).join(' '));
      }
    }

    hydrateFullFromHidden();
    syncToHidden();

    full.addEventListener('input', syncToHidden);
    full.addEventListener('blur', syncToHidden);

    nombre.addEventListener('change', hydrateFullFromHidden);
    apellido.addEventListener('change', hydrateFullFromHidden);
  }

  function initDobSelects() {
    const day = qs('#dob_day');
    const month = qs('#dob_month');
    const year = qs('#dob_year');
    const hidden = qs('#dob');

    if (!day || !month || !year || !hidden) return;

    function pad2(n) { return String(n).padStart(2, '0'); }

    function daysInMonth(y, m) {
      if (!y || !m) return 31;
      return new Date(Number(y), Number(m), 0).getDate();
    }

    function clampDay() {
      const y = year.value;
      const m = month.value;
      const d = day.value;

      if (!m) return;

      const maxD = daysInMonth(y || 2000, m);
      if (d && Number(d) > maxD) {
        day.value = pad2(maxD);
      }
    }

    function updateHidden() {
      clampDay();

      const d = day.value;
      const m = month.value;
      const y = year.value;

      if (d && m && y) {
        hidden.value = `${y}-${m}-${d}`;
      } else {
        hidden.value = '';
      }

      try { hidden.dispatchEvent(new Event('change', { bubbles: true })); } catch (_) { }
      try { applyYoungDriverAddon(); } catch (_) { }
      
      try { initStep4AddonsSummary(); } catch (_) { }

      setTimeout(() => {
        try { initStep4AddonsSummary(); } catch (_) { }
      }, 0);
    }


    function hydrateFromHidden() {
      const v = String(hidden.value || '').trim();
      const m = v.match(/^(\d{4})-(\d{2})-(\d{2})$/);
      if (!m) return;

      if (!year.value) year.value = m[1];
      if (!month.value) month.value = m[2];
      if (!day.value) day.value = m[3];

      updateHidden();
    }

    day.addEventListener('change', updateHidden);
    month.addEventListener('change', updateHidden);
    year.addEventListener('change', updateHidden);

    hydrateFromHidden();
    updateHidden();
  }

  function initDaysAndPricesSync() {
    const pickupDate = qs("#start") || qs('input[name="pickup_date"]');
    const dropoffDate = qs("#end") || qs('input[name="dropoff_date"]');
    const pickupHour = qs('select[name="pickup_h"]');
    const dropoffHour = qs('select[name="dropoff_h"]');
    const pickupHidden = qs('#pickup_time_hidden');
    const dropoffHidden = qs('#dropoff_time_hidden');

    if (!pickupDate || !dropoffDate) {
      console.log("No encontró inputs de fecha");
      return;
    }

    function pad2(n) {
      return String(n).padStart(2, '0');
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

    function isSameLocalDate(a, b) {
      if (!(a instanceof Date) || isNaN(a) || !(b instanceof Date) || isNaN(b)) return false;
      return (
        a.getFullYear() === b.getFullYear() &&
        a.getMonth() === b.getMonth() &&
        a.getDate() === b.getDate()
      );
    }

    function rebuildPickupHours() {
      const pickupHourEl = document.querySelector('select[name="pickup_h"]');
      const pickupDateEl = document.querySelector('#start') || document.querySelector('input[name="pickup_date"]');
      const pickupHiddenEl = document.querySelector('#pickup_time_hidden') || document.querySelector('input[name="pickup_time"]');

      const dropoffHourEl = document.querySelector('select[name="dropoff_h"]');
      const dropoffHiddenEl = document.querySelector('#dropoff_time_hidden') || document.querySelector('input[name="dropoff_time"]');

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
      const locale = getCurrentLocale();
      const placeholderText = locale === 'en' ? 'Time' : 'Hora';

      pickupHourEl.innerHTML = '';
      pickupHourEl.insertAdjacentHTML('afterbegin', `<option value="" disabled selected>${placeholderText}</option>`);

      for (let i = minHour; i <= 23; i++) {
        const hh = pad2(i);
        const option = document.createElement('option');
        option.value = hh;
        option.textContent = `${hh}:00`;
        pickupHourEl.appendChild(option);
      }

      let newValue = '';
      if (isToday) {
        if (previousValue && parseInt(previousValue, 10) >= minHour) {
          newValue = previousValue;
        } else {
          newValue = pad2(minHour);
        }
      } else {
        newValue = '13'; // Forzar 13:00 para fechas futuras
      }

      if (newValue) {
        pickupHourEl.value = newValue;
        if (pickupHiddenEl) pickupHiddenEl.value = `${newValue}:00:00`;
      }

      if (pickupHourEl.value !== previousValue) {
        pickupHourEl.classList.remove('field-error');
        const ctl = pickupHourEl.closest('.field, [data-float]');
        if (ctl) {
          ctl.classList.remove('has-error');
          ctl.classList.add('filled');
          const msg = ctl.querySelector('.error-msg');
          if (msg) msg.remove();
        }
      }

      if (dropoffHourEl) {
        if (!dropoffHourEl.value) {
          dropoffHourEl.value = '13';
          if (dropoffHiddenEl) dropoffHiddenEl.value = '13:00:00';

          const dCtl = dropoffHourEl.closest('[data-float]');
          if (dCtl) dCtl.classList.add('filled');
        }
      }
    }

    function getDateTime(which) {
      const d = parseDateAny(which === "pickup" ? pickupDate.value : dropoffDate.value);
      if (!d) return null;
      const h = which === "pickup" ? pickupHour?.value : dropoffHour?.value;
      d.setHours(+h || 0, 0, 0, 0);
      return d;
    }

    function calcDays() {
      const a = getDateTime("pickup");
      const b = getDateTime("dropoff");

      if (!a || !b) return 1;

      const diff = b - a;
      if (diff <= 0) return 1;

      const horasTotales = Math.floor(diff / (1000 * 60 * 60));
      const diasBase = Math.floor(horasTotales / 24);
      const horasExtra = horasTotales % 24;

      if (horasExtra > 1) {
        return diasBase + 1;
      } else {
        return Math.max(1, diasBase);
      }
    }

    function runUpdate() {
      rebuildPickupHours();

      const days = calcDays();

      const daysLabel = qs('#daysLabel');
      if (daysLabel) daysLabel.textContent = days;

      qsa(".js-days").forEach(el => el.textContent = days);

      qsa('.car-card').forEach(card => {
        const prepagoDia = parseFloat(card.getAttribute('data-prepago-dia') || '0') || 0;
        const mostradorDia = parseFloat(card.getAttribute('data-mostrador-dia') || '0') || 0;

        const prepagoTotal = prepagoDia * days;
        const mostradorTotal = mostradorDia * days;

        const fmt = (n) => Math.round(n).toLocaleString(getCurrentLocale() === 'en' ? 'en-US' : 'es-MX');

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

    [pickupDate, dropoffDate, pickupHour, dropoffHour]
      .filter(Boolean)
      .forEach(el => el.addEventListener("change", runUpdate));

    rebuildPickupHours();
    runUpdate();
  }

  function initAddonsSync() {
    const hidden =
      qs('#addonsHidden') ||
      qs('input[name="addons"]') ||
      qs('input[name="addons_ids"]') ||
      qs('input[name="addonsHidden"]');

    const payloadHidden = qs('#addons_payload');
    const cards = qsa('.addon-card');

    if (!cards.length || !hidden) return;

    const SERVICE_GASOLINA_ID = "1";

    function parseMap(str) {
      const map = new Map();
      String(str || '')
        .split(',')
        .map(s => s.trim())
        .filter(Boolean)
        .forEach(pair => {
          const m = pair.match(/^(\d+)\s*:\s*(\d+)$/);
          if (m) {
            map.set(m[1], Math.max(0, parseInt(m[2], 10) || 0));
          } else {
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

    function getGasolinaSwitch(card) {
      return qs('.gasolina-switch', card);
    }

    function setQty(card, qty) {
      const max = parseInt(card.dataset.max, 10) || Infinity;
      qty = Math.min(Math.max(0, qty | 0), max);

      const id = String(card.getAttribute('data-id') || '').trim();
      const qtyEl = qs('.qty', card);

      if (id === SERVICE_GASOLINA_ID) {
        const sw = getGasolinaSwitch(card);
        if (sw) sw.checked = qty > 0;
        card.classList.toggle('selected', qty > 0);
        return;
      }

      if (qtyEl) qtyEl.textContent = String(qty);
      card.classList.toggle('selected', qty > 0);
    }

    function readQty(card) {
      const id = String(card.getAttribute('data-id') || '').trim();

      if (id === SERVICE_GASOLINA_ID) {
        const sw = getGasolinaSwitch(card);
        return sw && sw.checked ? 1 : 0;
      }

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
        if (qty > 0) {
          map.set(id, qty);
        }
      });

      return map;
    }

    function writeHiddenAndURL() {
      const map = buildFromUI();
      const value = serializeMap(map);

      hidden.value = value;
      if (payloadHidden) {
        payloadHidden.value = value;
      }

      try { hidden.dispatchEvent(new Event('change', { bubbles: true })); } catch (_) { }
      try {
        if (payloadHidden) {
          payloadHidden.dispatchEvent(new Event('change', { bubbles: true }));
        }
      } catch (_) { }

      try {
        const url = new URL(window.location.href);
        if (value) {
          url.searchParams.set('addons', value);
        } else {
          url.searchParams.delete('addons');
        }
        window.history.replaceState({}, document.title, url.toString());
      } catch (_) { }

      try { applyYoungDriverAddon(); } catch (_) { }
      try { initStep4AddonsSummary(); } catch (_) { }
    }

    function hydrate() {
      const fromQS = (() => {
        try {
          return new URLSearchParams(location.search).get('addons') || '';
        } catch (_) {
          return '';
        }
      })();

      const base =
        fromQS ||
        (hidden.value || '').trim() ||
        (payloadHidden && payloadHidden.value ? payloadHidden.value.trim() : '');

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
      const sw = getGasolinaSwitch(card);

      const id = String(card.getAttribute('data-id') || '').trim();
      const isGasolina = id === SERVICE_GASOLINA_ID;

      if (isGasolina) {
        if (sw) {
          sw.addEventListener('change', () => {
            writeHiddenAndURL();
          });
        }
        return;
      }

      if (plus) {
        plus.addEventListener('click', () => {
          const max = parseInt(card.dataset.max, 10) || Infinity;
          const current = readQty(card);
          const newQty = Math.min(current + 1, max);
          setQty(card, newQty);
          writeHiddenAndURL();
        });
      }

      if (minus) {
        minus.addEventListener('click', () => {
          setQty(card, Math.max(0, readQty(card) - 1));
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

  function initFloatingLabels() {
    const floats = qsa('[data-float]');
    if (!floats.length) return;

    const hasValue = (el) => {
      if (!el) return false;

      if (el.tagName === 'SELECT') {
        const val = el.value;
        return val && val !== "" && val !== "H" && val !== "0" && val !== "00" && val !== "Hora" && val !== "Time" && val !== "Hour";
      }

      const v = (el.value ?? "").toString().trim();
      return v !== "";
    };

    function setState(ctl, filled) {
      ctl.classList.toggle('filled', !!filled);
      ctl.classList.toggle('pristine', !filled);
    }

    floats.forEach(ctl => {
      const input = ctl.querySelector('input:not([type="hidden"])');
      const select = ctl.querySelector('select');
      const element = input || select;

      if (!element) return;

      setState(ctl, hasValue(element));

      if (input) {
        input.addEventListener('focus', () => {
          ctl.classList.remove('pristine');
        });

        input.addEventListener('input', () => {
          setState(ctl, hasValue(input));
        });

        input.addEventListener('change', () => {
          setState(ctl, hasValue(input));
        });

        input.addEventListener('blur', () => {
          setState(ctl, hasValue(input));
        });
      }

      if (select) {
        select.addEventListener('focus', () => {
          ctl.classList.remove('pristine');
        });

        select.addEventListener('change', () => {
          setState(ctl, hasValue(select));
        });

        select.addEventListener('blur', () => {
          setState(ctl, hasValue(select));
        });
      }
    });
  }

  function refreshFloatLabels() {
    const floats = qsa('[data-float]');
    floats.forEach(ctl => {
      const input = ctl.querySelector('input:not([type="hidden"])');
      const select = ctl.querySelector('select');
      const element = input || select;

      if (!element) return;

      const hasVal = (() => {
        if (!element.value) return false;
        if (element.tagName === 'SELECT') {
          const val = element.value;
          return val !== "" && val !== "H" && val !== "0" && val !== "00" && val !== "Hora" && val !== "Time" && val !== "Hour";
        }
        return element.value.trim() !== "";
      })();

      ctl.classList.toggle('filled', hasVal);
      ctl.classList.toggle('pristine', !hasVal);
    });
  }

  function initFlatpickrRules() {
    if (!window.flatpickr) return;

    try {
      if (window.flatpickr?.l10ns?.es) window.flatpickr.localize(window.flatpickr.l10ns.es);
    } catch (_) { }

    const start = qs("#start");
    const end = qs("#end");

    const today = new Date();
    today.setHours(0, 0, 0, 0);

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

    // Destruir instancias anteriores si existen
    try { if (start._flatpickr) start._flatpickr.destroy(); } catch (_) { }
    try { if (end._flatpickr) end._flatpickr.destroy(); } catch (_) { }

    const localeData = getFlatpickrLocale();

    const baseCfg = {
      dateFormat: "Y-m-d",
      altInput: true,
      altFormat: "d M Y",
      allowInput: true,
      disableMobile: true,
      locale: localeData
    };

    const startFp = flatpickr(start, { ...baseCfg });
    const endFp = flatpickr(end, { ...baseCfg });

    // Sincronizar valores existentes
    const startInit = parseAnyToDate(start.value);
    const endInit = parseAnyToDate(end.value);

    if (startInit) {
      startFp.setDate(startInit, false);
      setTimeout(() => {
        if (startFp.altInput) {
          startFp.altInput.value = startFp.formatDate(startInit, startFp.config.altFormat);
        }
      }, 0);
    }

    if (endInit) {
      endFp.setDate(endInit, false);
      setTimeout(() => {
        if (endFp.altInput) {
          endFp.altInput.value = endFp.formatDate(endInit, endFp.config.altFormat);
        }
      }, 0);
    }

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

      if (s2 && e2 && s2.getTime() > e2.getTime()) {
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

    setTimeout(() => {
      const startVal = start.value;
      const endVal = end.value;

      if (startVal && startVal !== startFp.selectedDates?.[0]?.toISOString().split('T')[0]) {
        const parsed = parseAnyToDate(startVal);
        if (parsed) startFp.setDate(parsed, true);
      }

      if (endVal && endVal !== endFp.selectedDates?.[0]?.toISOString().split('T')[0]) {
        const parsed = parseAnyToDate(endVal);
        if (parsed) endFp.setDate(parsed, true);
      }
    }, 200);

    // Escuchar cambios de idioma para actualizar Flatpickr
    const observer = new MutationObserver(() => {
      const newLocale = getFlatpickrLocale();
      if (startFp) startFp.set('locale', newLocale);
      if (endFp) endFp.set('locale', newLocale);
    });
    observer.observe(document.documentElement, { attributes: true, attributeFilter: ['lang'] });
  }

  function bootWhenFlatpickrReady() {
    let tries = 0;
    const maxTries = 240;
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
        (select && val(select) !== "" && val(select) !== "H" && val(select) !== "Hour" && val(select) !== "Time");

      ctl.classList.toggle('filled', filled);
      ctl.classList.toggle('pristine', !filled);
    });
  }

  function initStep3ProteccionesModal() {
    const openId = 'info-protecciones-step3';
    const modalId = 'modalProteccionesStep3';
    const closeId = 'closeProteccionesStep3';

    // Función interna para bloquear/desbloquear
    const toggleScroll = (block) => {
      if (block) {
        document.body.style.overflow = 'hidden';
        document.documentElement.style.overflow = 'hidden'; // Bloquea el HTML también
        document.body.style.touchAction = 'none';           // Bloquea gestos en móviles
      } else {
        document.body.style.overflow = '';
        document.documentElement.style.overflow = '';
        document.body.style.touchAction = '';
      }
    };

    document.addEventListener('click', (e) => {
      const modal = document.getElementById(modalId);
      if (!modal) return;

      // ABRIR
      if (e.target.closest('#' + openId)) {
        modal.style.display = 'flex';
        modal.setAttribute('aria-hidden', 'false');
        toggleScroll(true); // <--- CONGELAR
        return;
      }

      // CERRAR (con X o clic fuera)
      if (e.target.closest('#' + closeId) || e.target === modal) {
        modal.style.display = 'none';
        modal.setAttribute('aria-hidden', 'true');
        toggleScroll(false); // <--- LIBERAR
        return;
      }
    });

    document.addEventListener('keydown', (e) => {
      if (e.key !== 'Escape') return;
      const modal = document.getElementById(modalId);
      if (modal && modal.style.display === 'flex') {
        modal.style.display = 'none';
        modal.setAttribute('aria-hidden', 'true');
        toggleScroll(false); // <--- LIBERAR
      }
    });
  }

  // ===== BOOT =====
  document.addEventListener("DOMContentLoaded", () => {
    forceStep1WhenOnlyStepParam();
    initWizardStatePersistence();
    initSectionValidators();
    initFloatingLabels();
    bootWhenFlatpickrReady();
    initDaysAndPricesSync();
    initAddonsSync();
    initStep4DatePretty();
    initStep4AddonsSummary();
    initFullNameSync();
    initDobSelects();
    initDobMonthsShortObserver();
    applyYoungDriverAddon();
    refreshFloatStates();
    initStep3ProteccionesModal();

    setTimeout(refreshFloatLabels, 100);
    setTimeout(refreshFloatLabels, 300);
    setTimeout(refreshFloatLabels, 500);
  });



  // modal Step 4 - MODIFICADO para usar evedocument.addEventListener('DOMContentLoaded', function()to personalizado
  document.addEventListener('DOMContentLoaded', function () {
    const modal = document.getElementById('modalProtecciones');
    const btnInfo = document.getElementById('info-protecciones');
    const closeX = modal ? modal.querySelector('.cerrar-modal-v') : null;

    if (!modal || !btnInfo) return;

    const openModal = () => {
      modal.style.display = 'flex';
      document.body.style.overflow = 'hidden';
    };

    const closeModal = () => {
      modal.style.display = 'none';
      document.body.style.overflow = '';
    };

    btnInfo.addEventListener('click', function (e) {
      e.preventDefault();
      openModal();
    });

    if (closeX) {
      closeX.addEventListener('click', closeModal);
    }

    modal.addEventListener('click', function (e) {
      if (e.target === modal) closeModal();
    });

    document.addEventListener('keydown', function (e) {
      if (e.key === 'Escape') closeModal();
    });
  });

  // ===== MODAL DE MÉTODO DE PAGO MEJORADO =====
  document.addEventListener('DOMContentLoaded', function () {
    const main = document.querySelector('main.page');

    const modalMetodoPago = document.getElementById('modalMetodoPago');
    const cerrarModalMetodo = document.getElementById('cerrarModalMetodo');
    const cerrarModalMetodoX = document.getElementById('cerrarModalMetodoX');
    const btnPagoLinea = document.getElementById('btnPagoLinea');
    const btnPagoMostrador = document.getElementById('btnPagoMostrador');

    const mpCategoriaNombre = document.getElementById('mpCategoriaNombre');
    const mpCategoriaResumen = document.getElementById('mpCategoriaResumen');
    const mpAhorro = document.getElementById('mpAhorro');
    const mpPrecioLinea = document.getElementById('mpPrecioLinea');
    const mpPrecioMostrador = document.getElementById('mpPrecioMostrador');
    const mpPrecioMostradorTachado = document.getElementById('mpPrecioMostradorTachado');
    const mpTextoAhorro = document.getElementById('mpTextoAhorro');

    function fmtMoney(n) {
      const locale = getCurrentLocale();
      return '$' + Math.round(Number(n || 0)).toLocaleString(locale === 'en' ? 'en-US' : 'es-MX') +
        (locale === 'en' ? ' USD' : ' MXN');
    }

    function getCategoriaSeleccionada() {
      // 1. intenta tomar el nombre desde la tarjeta activa del step 2
      const activeCard = document.querySelector('.car-card.active');
      if (activeCard) {
        const name =
          activeCard.getAttribute('data-name') ||
          activeCard.dataset.name ||
          document.querySelector('.car-card.active .car-name, .car-card.active h3, .car-card.active h4')?.textContent ||
          '';
        if (name) return name.trim();
      }

      // 2. intenta tomarla del resumen del paso 4
      const sumTitle = document.querySelector('.sum-car h3, .sum-car-title, #tuAutoSection h3');
      if (sumTitle && sumTitle.textContent.trim()) {
        return sumTitle.textContent.trim();
      }

      return getCurrentLocale() === 'en' ? 'Selected category' : 'Categoría seleccionada';
    }

    function getPreciosSeleccionados() {

      const qTotal = document.getElementById('qTotal');

      const getNum = (txt) => {
        if (!txt) return 0;
        return parseFloat(String(txt).replace(/[^\d.]/g, '')) || 0;
      };

      let totalActual = qTotal ? getNum(qTotal.textContent) : 0;

      // Detectar qué plan está activo actualmente
      const main = document.querySelector('main.page');
      const currentPlan = main ? main.dataset.plan : '';

      let precioLinea = 0;
      let precioMostrador = 0;

      if (currentPlan === 'linea') {
        precioLinea = totalActual;

        // calcular cuánto sería mostrador sumando diferencia base
        precioMostrador = totalActual + 70; // ← usa tu diferencia real
      } else {
        precioMostrador = totalActual;
        precioLinea = totalActual - 70; // ← misma diferencia
      }

      return { precioLinea, precioMostrador };
    }
    function fillMetodoPagoModal() {
      const categoria = getCategoriaSeleccionada();
      const { precioLinea, precioMostrador } = getPreciosSeleccionados();

      let ahorroPct = 0;
      if (precioMostrador > 0 && precioLinea > 0 && precioLinea < precioMostrador) {
        ahorroPct = Math.round(((precioMostrador - precioLinea) / precioMostrador) * 100);
      }

      if (mpCategoriaNombre) mpCategoriaNombre.textContent = categoria;
      if (mpCategoriaResumen) mpCategoriaResumen.textContent = categoria;
      if (mpAhorro) mpAhorro.textContent = ahorroPct + '%';
      if (mpPrecioLinea) mpPrecioLinea.textContent = fmtMoney(precioLinea);
      if (mpPrecioMostrador) mpPrecioMostrador.textContent = fmtMoney(precioMostrador);
      if (mpPrecioMostradorTachado) mpPrecioMostradorTachado.textContent = fmtMoney(precioMostrador);
      if (mpTextoAhorro) mpTextoAhorro.textContent = ahorroPct > 0 ? (getCurrentLocale() === 'en' ? `Save ${ahorroPct}%` : `Ahorra ${ahorroPct}%`) : (getCurrentLocale() === 'en' ? 'Same price' : 'Mismo precio');
    }

    function openMetodoPagoModal() {
      if (!modalMetodoPago) return;
      fillMetodoPagoModal();
      modalMetodoPago.style.display = 'flex';
      document.body.style.overflow = 'hidden';
    }

    function closeMetodoPagoModal() {
      if (!modalMetodoPago) return;
      modalMetodoPago.style.display = 'none';
      document.body.style.overflow = '';
    }

    // Escuchar evento de validación exitosa
    document.addEventListener('reserva:validacionExitosa', function (e) {
      const currentPlan = (main && main.dataset.plan) ? main.dataset.plan : '';
      console.log('Evento recibido, plan:', currentPlan);

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

    if (cerrarModalMetodo) {
      cerrarModalMetodo.addEventListener('click', closeMetodoPagoModal);
    }

    if (cerrarModalMetodoX) {
      cerrarModalMetodoX.addEventListener('click', closeMetodoPagoModal);
    }

    if (modalMetodoPago) {
      modalMetodoPago.addEventListener('click', function (e) {
        if (e.target === modalMetodoPago) {
          closeMetodoPagoModal();
        }
      });
    }

    document.addEventListener('keydown', function (e) {
      if (e.key === 'Escape') {
        closeMetodoPagoModal();
      }
    });

    const tarifa = document.querySelector('.sum-table details.sum-acc');
    if (tarifa && tarifa.hasAttribute('open')) tarifa.removeAttribute('open');
  });

  function initMovilTotalSync() {
    const btnOriginal = document.getElementById('btnReservar');
    const btnMovil = document.getElementById('btnReservarMovil');
    const totalOriginal = document.getElementById('qTotal');
    const totalMovil = document.getElementById('qTotalMovil');

    if (btnMovil && btnOriginal) {
      btnMovil.addEventListener('click', function (e) {
        e.preventDefault();

        // Pequeño retraso para asegurar que todo esté listo
        setTimeout(() => {
          btnOriginal.click();
        }, 50);
      });
    }

    if (totalOriginal && totalMovil) {
      function syncTotal() {
        totalMovil.innerText = totalOriginal.innerText;
      }
      syncTotal();

      const observer = new MutationObserver(syncTotal);
      observer.observe(totalOriginal, {
        childList: true,
        subtree: true,
        characterData: true
      });
    }
  }

  // Inicializar cuando el DOM esté listo
  document.addEventListener('DOMContentLoaded', function () {
    // Solo inicializar en Step 4
    const mainEl = document.querySelector('main.page');
    const currentStep = mainEl ? mainEl.dataset.currentStep : '';

    if (currentStep === '4') {
      // Pequeño retraso para asegurar que todo el DOM esté renderizado
      setTimeout(() => {
        initMovilCardVisibility();
        initMovilTotalSync();
      }, 100);
    }
  });

  // También cuando se actualice el summary de addons, mantener la sincronización
  const originalInitStep4AddonsSummary = window.initStep4AddonsSummary || function () { };
  window.initStep4AddonsSummary = function () {
    originalInitStep4AddonsSummary();

    // Re-sincronizar el total móvil después de actualizar
    const totalOriginal = document.getElementById('qTotal');
    const totalMovil = document.getElementById('qTotalMovil');

    if (totalOriginal && totalMovil) {
      totalMovil.innerText = totalOriginal.innerText;
    }
  };

  // ===== CONTROL DE VISIBILIDAD DE LA TARJETA MÓVIL (SOLO MÓVIL/TABLET) =====

  // Función para verificar si los datos REQUERIDOS están completos
  function isStep4DataFilled() {
    const nombre = document.getElementById('nombreCompleto');
    const telefono = document.getElementById('telefonoCliente');
    const correo = document.getElementById('correoCliente');
    const pais = document.getElementById('pais');
    const dia = document.getElementById('dob_day');
    const mes = document.getElementById('dob_month');
    const año = document.getElementById('dob_year');
    const acepto = document.getElementById('acepto');

    if (!nombre || !telefono || !correo || !pais || !dia || !mes || !año || !acepto) {
      return false;
    }

    return nombre.value.trim() !== "" &&
      telefono.value.trim() !== "" &&
      correo.value.trim() !== "" &&
      pais.value.trim() !== "" &&
      dia.value !== "" &&
      mes.value !== "" &&
      año.value !== "" &&
      acepto.checked === true;
  }

  // ===== CONTROL DE VISIBILIDAD DE LA TARJETA MÓVIL (CORREGIDO CON DETECCIÓN DE ALERTA DE CONFIRMACIÓN) =====
  function initMovilCardVisibility() {
    const mainEl = document.querySelector('main.page');
    const currentStep = mainEl ? mainEl.dataset.currentStep : '';

    // Solo ejecutar en Step 4
    if (currentStep !== '4') return;

    const tuAutoSection = document.getElementById('tuAutoSection');
    const movilCard = document.querySelector('.movil-footer-sticky');
    const modalMetodoPago = document.getElementById('modalMetodoPago');
    const modalPagoOnline = document.getElementById('modalPagoOnline');

    // CORRECCIÓN: Buscar el modal de confirmación (puede tener otro ID)
    const modalConfirmacion = document.getElementById('modalConfirmacion') ||
      document.querySelector('.modal-confirmacion, [id*="confirm"]');

    if (!tuAutoSection || !movilCard) {
      console.warn('Elementos necesarios no encontrados');
      return;
    }

    // Estado de la tarjeta
    let movilCardState = {
      hasShownCard: false,
      isModalOpen: false,
      isStep4DataComplete: false,
      isReserving: false,
      isConfirming: false
    };

    // EXPONER EL ESTADO GLOBALMENTE PARA QUE OTRAS FUNCIONES PUEDAN ACCEDER
    window.__movilCardState = movilCardState;

    // Función para verificar si los datos REQUERIDOS están completos
    function isStep4DataFilled() {
      const nombre = document.getElementById('nombreCompleto');
      const telefono = document.getElementById('telefonoCliente');
      const correo = document.getElementById('correoCliente');
      const pais = document.getElementById('pais');
      const dia = document.getElementById('dob_day');
      const mes = document.getElementById('dob_month');
      const año = document.getElementById('dob_year');
      const acepto = document.getElementById('acepto');

      if (!nombre || !telefono || !correo || !pais || !dia || !mes || !año || !acepto) {
        return false;
      }

      const nombreValido = nombre.value && nombre.value.trim() !== "";
      const telefonoValido = telefono.value && telefono.value.trim() !== "";
      const correoValido = correo.value && correo.value.trim() !== "";
      const paisValido = pais.value && pais.value.trim() !== "";
      const fechaValida = dia.value !== "" && mes.value !== "" && año.value !== "";

      let fechaCompletaValida = true;
      if (fechaValida) {
        const day = parseInt(dia.value, 10);
        const month = parseInt(mes.value, 10);
        const year = parseInt(año.value, 10);
        const date = new Date(year, month - 1, day);
        fechaCompletaValida = date.getDate() === day && date.getMonth() === month - 1;
      }

      return nombreValido &&
        telefonoValido &&
        correoValido &&
        paisValido &&
        fechaValida &&
        fechaCompletaValida &&
        acepto.checked === true;
    }

    // Función para mostrar la tarjeta
    function showMovilCard() {
      if (movilCardState.isReserving || movilCardState.isConfirming) {
        console.log('⏸️ En proceso de reserva/confirmación - tarjeta no se muestra');
        return;
      }

      if (!movilCardState.hasShownCard && !movilCardState.isModalOpen) {
        movilCard.classList.add('visible');
        movilCardState.hasShownCard = true;
        console.log('✅ Tarjeta visible');
      }
    }

    // Función para ocultar la tarjeta
    function hideMovilCard() {
      if (movilCardState.hasShownCard) {
        movilCard.classList.remove('visible');
        movilCardState.hasShownCard = false;
        console.log('👻 Tarjeta oculta');
      }
    }

    // Función para actualizar la visibilidad según el estado
    function updateCardVisibility() {
      const datosCompletos = isStep4DataFilled();
      const anyModalOpen = isAnyModalOpen();

      // Actualizar estado
      movilCardState.isStep4DataComplete = datosCompletos;
      movilCardState.isModalOpen = anyModalOpen;

      console.log('🔍 Actualizando visibilidad - Datos:', datosCompletos, 'Modal:', anyModalOpen, 'Reservando:', movilCardState.isReserving, 'Confirmando:', movilCardState.isConfirming);

      // Si estamos en proceso de reserva o confirmación, ocultar siempre
      if (movilCardState.isReserving || movilCardState.isConfirming) {
        hideMovilCard();
        return;
      }

      // Lógica principal: SI DATOS COMPLETOS Y SIN MODAL -> MOSTRAR
      if (datosCompletos && !anyModalOpen) {
        showMovilCard();
      }
      // SI DATOS NO COMPLETOS O MODAL ABIERTO -> OCULTAR
      else {
        hideMovilCard();
      }
    }

    // Verificar si hay alguna alerta de Alertify visible
    function isAlertifyOpen() {
      const alertifyDialogs = document.querySelectorAll('.ajs-dialog, .ajs-modal, .alertify');
      for (let dialog of alertifyDialogs) {
        const style = window.getComputedStyle(dialog);
        if (style.display !== 'none' && style.visibility !== 'hidden') {
          if (!dialog.classList.contains('ajs-hidden') && !dialog.classList.contains('ajs-out')) {
            return true;
          }
        }
      }
      return false;
    }

    // Detectar alertas de éxito (confirmación de reserva)
    function isSuccessAlertOpen() {
      const successDialogs = document.querySelectorAll('.resv-alertify-success, .ajs-dialog.resv-alertify-success');
      for (let dialog of successDialogs) {
        const style = window.getComputedStyle(dialog);
        if (style.display !== 'none' && style.visibility !== 'hidden') {
          console.log('🎉 Alerta de éxito detectada');
          return true;
        }
      }

      const allDialogs = document.querySelectorAll('.ajs-dialog, .ajs-modal');
      for (let dialog of allDialogs) {
        if (dialog.textContent && (
          dialog.textContent.includes('reservación fue registrada correctamente') ||
          dialog.textContent.includes('Your reservation has been successfully registered') ||
          dialog.textContent.includes('reservation was successfully registered')
        )) {
          console.log('🎉 Alerta de éxito detectada por texto');
          return true;
        }
      }

      return false;
    }

    // Verificar si algún modal está abierto
    function isAnyModalOpen() {
      const isMetodoPagoOpen = modalMetodoPago && modalMetodoPago.style.display === 'flex';
      const isPagoOnlineOpen = modalPagoOnline && modalPagoOnline.style.display === 'flex';
      const isConfirmacionOpen = modalConfirmacion && modalConfirmacion.style.display === 'flex';
      const isAlertOpen = isAlertifyOpen();
      const isSuccessAlert = isSuccessAlertOpen();

      return isMetodoPagoOpen || isPagoOnlineOpen || isConfirmacionOpen || isAlertOpen || isSuccessAlert;
    }

    // Manejar el clic en reservar
    function initReservationHandler() {
      const btnReservar = document.getElementById('btnReservar');
      const btnReservarMovil = document.getElementById('btnReservarMovil');

      if (!btnReservar) return;

      const handleReservationStart = () => {
        if (isStep4DataFilled()) {
          console.log('🔒 Iniciando proceso de reserva - ocultando tarjeta');
          movilCardState.isReserving = true;
          hideMovilCard();
        }
      };

      btnReservar.addEventListener('click', handleReservationStart);
      if (btnReservarMovil) {
        btnReservarMovil.addEventListener('click', handleReservationStart);
      }

      document.addEventListener('reserva:completada', function () {
        console.log('✅ Reserva completada - activando modo confirmación');
        movilCardState.isReserving = false;
        movilCardState.isConfirming = true;
        hideMovilCard();

        const checkSuccessAlert = setInterval(() => {
          if (!isSuccessAlertOpen()) {
            console.log('🔓 Alerta de éxito cerrada - restaurando estado');
            movilCardState.isConfirming = false;
            clearInterval(checkSuccessAlert);
            setTimeout(() => updateCardVisibility(), 200);
          }
        }, 200);

        setTimeout(() => {
          if (movilCardState.isConfirming) {
            console.log('⏰ Timeout - restaurando estado');
            movilCardState.isConfirming = false;
            updateCardVisibility();
          }
        }, 10000);
      });

      document.addEventListener('reserva:cancelada', function () {
        console.log('❌ Reserva cancelada - restaurando estado');
        movilCardState.isReserving = false;
        movilCardState.isConfirming = false;
        setTimeout(() => updateCardVisibility(), 100);
      });
    }

    // Manejar scroll SOLO cuando los datos NO están completos
    function handleScroll() {
      // 🔥 CORRECCIÓN: Si estamos en modo confirmación, no hacer nada
      if (movilCardState.isConfirming) {
        console.log('⏸️ Modo confirmación - scroll ignorado');
        return;
      }

      if (movilCardState.isReserving) return;
      if (movilCardState.isStep4DataComplete) return;

      const rect = tuAutoSection.getBoundingClientRect();
      const windowHeight = window.innerHeight;
      const isVisible = rect.top < windowHeight * 0.9 && rect.bottom > 0;

      console.log('📜 Scroll check - Visible:', isVisible, 'Modal:', movilCardState.isModalOpen);

      if (isVisible && !movilCardState.isModalOpen) {
        showMovilCard();
      } else if (!isVisible && !movilCardState.isModalOpen) {
        hideMovilCard();
      }
    }

    // Observar cambios en los campos del formulario
    function initFormObserver() {
      const fields = ['#nombreCompleto', '#telefonoCliente', '#correoCliente', '#pais', '#dob_day', '#dob_month', '#dob_year', '#acepto'];

      let timeoutId = null;
      const checkDataComplete = () => {
        if (timeoutId) clearTimeout(timeoutId);
        timeoutId = setTimeout(() => {
          const isNowComplete = isStep4DataFilled();

          if (movilCardState.isReserving && !isNowComplete) {
            console.log('🔄 Datos cambiaron a incompletos durante reserva - saliendo modo reserva');
            movilCardState.isReserving = false;
          }

          updateCardVisibility();
        }, 50);
      };

      fields.forEach(selector => {
        const field = document.querySelector(selector);
        if (field) {
          field.addEventListener('input', checkDataComplete);
          field.addEventListener('change', checkDataComplete);
        }
      });

      setTimeout(checkDataComplete, 100);
    }

    // Observar cambios en modales
    function initModalObserver() {
      function observeModal(modal, modalName) {
        if (!modal) return;

        const observer = new MutationObserver(() => {
          const isOpen = modal.style.display === 'flex';
          console.log(`📱 Modal ${modalName} ${isOpen ? 'abierto' : 'cerrado'}`);

          if (!isOpen && (modalName === 'MetodoPago' || modalName === 'PagoOnline')) {
            console.log('🔓 Modal de pago cerrado - restaurando estado normal');
            movilCardState.isReserving = false;
          }

          updateCardVisibility();
        });

        observer.observe(modal, { attributes: true, attributeFilter: ['style'] });
      }

      observeModal(modalMetodoPago, 'MetodoPago');
      observeModal(modalPagoOnline, 'PagoOnline');
      if (modalConfirmacion) observeModal(modalConfirmacion, 'Confirmacion');
    }

    // Observar cambios en Alertify
    function initAlertifyObserver() {
      let lastAlertState = false;
      let lastSuccessState = false;

      const checkAlertState = () => {
        const isOpen = isAlertifyOpen();
        const isSuccess = isSuccessAlertOpen();

        // DETECTAR CUANDO SE CIERRA UNA ALERTA DE ÉXITO
        if (lastSuccessState && !isSuccess) {
          console.log('🎉 Alerta de éxito cerrada');
          movilCardState.isConfirming = false;
          setTimeout(() => updateCardVisibility(), 150);
        }

        // DETECTAR CUANDO SE CIERRA UNA ALERTA NORMAL (como la de menor de 25 años)
        if (lastAlertState && !isOpen && !isSuccess) {
          console.log('🔔 Alerta normal cerrada - verificando estado de datos');

          // Limpiar cualquier flag que pueda estar bloqueando la tarjeta
          movilCardState.isReserving = false;
          movilCardState.isConfirming = false;

          setTimeout(() => {
            const datosCompletos = isStep4DataFilled();

            if (!datosCompletos) {
              console.log('⚠️ Alerta cerrada con datos incompletos - restaurando scroll');

              // que isModalOpen sea false
              movilCardState.isModalOpen = false;


              setTimeout(() => {
                handleScroll();

                window.dispatchEvent(new Event('scroll'));
              }, 50);
            } else {
              // Si los datos están completos, actualizar visibilidad
              updateCardVisibility();
            }
          }, 100);
        }

        lastAlertState = isOpen;
        lastSuccessState = isSuccess;
      };

      // Observar cambios en el DOM para detectar apertura/cierre de alertas
      const bodyObserver = new MutationObserver(() => {
        checkAlertState();
      });

      bodyObserver.observe(document.body, { childList: true, subtree: true });

      // Intervalo de respaldo para asegurar que detectamos cambios
      setInterval(checkAlertState, 200);
    }

    // Observar cambios en alertas de éxito
    function initSuccessAlertObserver() {
      const bodyObserver = new MutationObserver(() => {
        const successAlertOpen = isSuccessAlertOpen();

        if (successAlertOpen && !movilCardState.isConfirming) {
          console.log('🎉 Alerta de éxito detectada - activando modo confirmación');
          movilCardState.isConfirming = true;
          hideMovilCard();
        } else if (!successAlertOpen && movilCardState.isConfirming) {
          console.log('🔓 Alerta de éxito cerrada - restaurando');
          movilCardState.isConfirming = false;
          setTimeout(() => updateCardVisibility(), 200);
        }
      });

      bodyObserver.observe(document.body, { childList: true, subtree: true });
    }

    // Verificar si es móvil/tablet
    function isMobileView() {
      return window.innerWidth <= 1024;
    }

    // Evento de scroll con throttle
    let scrollTimeout;
    window.addEventListener('scroll', function () {
      if (!isMobileView()) return;
      if (scrollTimeout) clearTimeout(scrollTimeout);
      scrollTimeout = setTimeout(handleScroll, 50);
    });

    // Evento de resize
    window.addEventListener('resize', function () {
      if (!isMobileView()) {
        hideMovilCard();
      } else {
        setTimeout(updateCardVisibility, 100);
      }
    });

    // Evento personalizado para refrescar la tarjeta
    document.addEventListener('refreshMovilCard', function (e) {
      console.log('🔄 Evento refreshMovilCard recibido', e?.detail);
      setTimeout(() => {
        // Si viene de una alerta, asegurar que los flags estén correctos
        if (e?.detail?.fromAlert) {
          if (!movilCardState.isStep4DataComplete) {
            movilCardState.isReserving = false;
            movilCardState.isConfirming = false;
            console.log('🔄 Refresh desde alerta - restaurando modo scroll');
          }
        }
        if (!movilCardState.isReserving && !movilCardState.isConfirming) {
          updateCardVisibility();
        }
      }, 100);
    });

    // Inicializar todo
    initReservationHandler();
    initFormObserver();
    initModalObserver();
    initAlertifyObserver();
    initSuccessAlertObserver();

    // Verificación inicial
    setTimeout(() => {
      if (isMobileView()) {
        updateCardVisibility();
      }
    }, 300);

    console.log('🚀 Control de tarjeta inicializado correctamente');
  }

  // Inicializar cuando el DOM esté listo
  document.addEventListener('DOMContentLoaded', function () {
    const mainEl = document.querySelector('main.page');
    const currentStep = mainEl ? mainEl.dataset.currentStep : '';

    if (currentStep === '4') {
      setTimeout(initMovilCardVisibility, 500);
    }
  });

  // Sincronizar total móvil
  function initMovilTotalSync() {
    const btnOriginal = document.getElementById('btnReservar');
    const btnMovil = document.getElementById('btnReservarMovil');
    const totalOriginal = document.getElementById('qTotal');
    const totalMovil = document.getElementById('qTotalMovil');

    if (btnMovil && btnOriginal) {
      btnMovil.addEventListener('click', function (e) {
        e.preventDefault();
        setTimeout(() => btnOriginal.click(), 50);
      });
    }

    if (totalOriginal && totalMovil) {
      const syncTotal = () => { totalMovil.innerText = totalOriginal.innerText; };
      syncTotal();
      const observer = new MutationObserver(syncTotal);
      observer.observe(totalOriginal, { childList: true, subtree: true, characterData: true });
    }
  }

  document.addEventListener('DOMContentLoaded', function () {
    const mainEl = document.querySelector('main.page');
    if (mainEl && mainEl.dataset.currentStep === '4') {
      setTimeout(initMovilTotalSync, 100);
    }
  });

  // Función para manejar el toggle del destino
  function initLocationToggle() {
    const checkbox = document.getElementById('differentDropoff');
    const locationWrapper = document.querySelector('.location-wrapper');
    const dropoffField = document.querySelector('.dropoff-field');
    const dropoffSelect = document.getElementById('dropoffPlace');
    const pickupSelect = document.getElementById('pickupPlace');

    if (!checkbox || !locationWrapper || !dropoffField || !dropoffSelect) return;

    function toggleDropoffLocation() {
      if (checkbox.checked) {
        // Mostrar campo de devolución
        locationWrapper.classList.add('dropoff-visible');
        dropoffSelect.required = true;
        dropoffSelect.disabled = false;

        // Si pickup tiene valor y dropoff está vacío, copiar el valor
        if (pickupSelect && pickupSelect.value && !dropoffSelect.value) {
          dropoffSelect.value = pickupSelect.value;
          dropoffSelect.dispatchEvent(new Event('change', { bubbles: true }));
        }
      } else {
        // Ocultar campo de devolución
        locationWrapper.classList.remove('dropoff-visible');
        dropoffSelect.required = false;
        dropoffSelect.disabled = true;
        dropoffSelect.value = '';
        dropoffSelect.dispatchEvent(new Event('change', { bubbles: true }));
      }

      // Actualizar floating labels
      setTimeout(() => {
        document.querySelectorAll('[data-float]').forEach(container => {
          const input = container.querySelector('input, select');
          if (input && input.value && input.value !== '') {
            container.classList.add('filled');
          } else {
            container.classList.remove('filled');
          }
        });
      }, 50);
    }

    // Ejecutar al cargar según el estado guardado
    setTimeout(toggleDropoffLocation, 100);

    // Escuchar cambios en el checkbox
    checkbox.addEventListener('change', toggleDropoffLocation);

    // Sincronizar dropoff cuando cambie pickup (solo si checkbox activo y dropoff vacío)
    if (pickupSelect) {
      pickupSelect.addEventListener('change', function () {
        if (checkbox.checked && !dropoffSelect.value) {
          dropoffSelect.value = this.value;
          dropoffSelect.dispatchEvent(new Event('change', { bubbles: true }));
        }
      });
    }
  }

  // Inicializar cuando el DOM esté listo
  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initLocationToggle);
  } else {
    initLocationToggle();
  }


})();

document.addEventListener('DOMContentLoaded', function () {
  // ===== FUNCIÓN PARA COMBINAR HORA (simplificada) =====
  function combineTime() {
    const pickupH = document.getElementById('pickup_h');
    const pickupHidden = document.getElementById('pickup_time_hidden');
    const dropoffH = document.getElementById('dropoff_h');
    const dropoffHidden = document.getElementById('dropoff_time_hidden');

    if (pickupH && pickupHidden) {
      pickupHidden.value = pickupH.value ? pickupH.value + ':00' : '';
    }
    if (dropoffH && dropoffHidden) {
      dropoffHidden.value = dropoffH.value ? dropoffH.value + ':00' : '';
    }
  }

  // Event listeners para combinar tiempo
  ['pickup_h', 'dropoff_h'].forEach(id => {
    const el = document.getElementById(id);
    if (el) el.addEventListener('change', combineTime);
  });

  // ===== MANEJO DEL CHECKBOX =====
  const differentDropoff = document.getElementById('differentDropoff');
  const dropoffWrapper = document.getElementById('dropoffWrapper');

  if (differentDropoff && dropoffWrapper) {
    differentDropoff.addEventListener('change', function () {
      dropoffWrapper.style.display = this.checked ? 'block' : 'none';
      if (!this.checked) {
        document.getElementById('dropoffPlace').value = '';

        // Actualizar Select2 si existe
        if (typeof $ !== 'undefined' && $.fn.select2) {
          $('#dropoffPlace').val('').trigger('change');
        }
      }
    });
  }

  // ============================================================
  // INICIALIZAR FLATPICKR CON SOPORTE PARA PERSISTENCIA
  // ============================================================
  if (typeof flatpickr !== 'undefined') {
    const startInput = document.getElementById('start');
    const endInput = document.getElementById('end');

    // Función para obtener el locale actual
    function getFlatpickrLocale() {
      const htmlLang = document.documentElement.lang || 'es';
      if (htmlLang === 'en') {
        return {
          firstDayOfWeek: 0,
          weekdays: {
            shorthand: ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'],
            longhand: ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday']
          },
          months: {
            shorthand: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
            longhand: ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December']
          }
        };
      }
      return {
        firstDayOfWeek: 1,
        weekdays: {
          shorthand: ['Dom', 'Lun', 'Mar', 'Mié', 'Jue', 'Vie', 'Sáb'],
          longhand: ['Domingo', 'Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado']
        },
        months: {
          shorthand: ['Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun', 'Jul', 'Ago', 'Sep', 'Oct', 'Nov', 'Dic'],
          longhand: ['Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre']
        }
      };
    }

    // Función para parsear fecha en múltiples formatos
    function parseDateAny(val) {
      if (!val) return null;
      const s = String(val).trim();

      // Formato dd-mm-yyyy
      let m = s.match(/^(\d{2})-(\d{2})-(\d{4})$/);
      if (m) return new Date(+m[3], +m[2] - 1, +m[1]);

      // Formato yyyy-mm-dd
      m = s.match(/^(\d{4})-(\d{2})-(\d{2})$/);
      if (m) return new Date(+m[1], +m[2] - 1, +m[3]);

      // Intento con Date nativo
      const d = new Date(s);
      return isNaN(d.getTime()) ? null : d;
    }

    // Función para obtener valor actual respetando el estado persistido
    function getCurrentDateValue(inputEl) {
      let rawValue = inputEl.value;
      if (rawValue && rawValue.trim() !== '') {
        return rawValue;
      }


      const hiddenDateInput = inputEl.id === 'start' ?
        document.querySelector('input[name="pickup_date"]') :
        document.querySelector('input[name="dropoff_date"]');

      if (hiddenDateInput && hiddenDateInput.value) {
        return hiddenDateInput.value;
      }

      return null;
    }

    // Configuración común para ambos datepickers
    const commonConfig = {
      dateFormat: "Y-m-d",
      altInput: true,
      altFormat: "d-M-y",
      allowInput: false,
      locale: getFlatpickrLocale(),
      minDate: "today",
      disableMobile: true,

      // --- NUEVA LÓGICA DE FONDO OSCURO ---
      onOpen: function (selectedDates, dateStr, instance) {
        const overlay = document.getElementById('fp-view-overlay');
        if (overlay) overlay.classList.add('active');
      },
      onClose: function (selectedDates, dateStr, instance) {
        const overlay = document.getElementById('fp-view-overlay');
        if (overlay) overlay.classList.remove('active');
      },


      onReady: function (selectedDates, dateStr, instance) {
        if (dateStr && dateStr !== '') {
          instance.altInput.classList.add('field-success');
          instance.altInput.classList.remove('field-error');
        }
      },
      // --- LÓGICA DE CAMBIO Y BLOQUEO DE FECHAS ---
      onChange: function (selectedDates, dateStr, instance) {
        if (dateStr && dateStr !== '') {
          instance.altInput.classList.add('field-success');
          instance.altInput.classList.remove('field-error');
        } else {
          instance.altInput.classList.remove('field-success', 'field-error');
        }

        if (instance.element.id === 'start' && selectedDates[0]) {
          const endDatePicker = document.getElementById('end')._flatpickr;
          if (endDatePicker) {
            const minDropoffDate = new Date(selectedDates[0]);
            minDropoffDate.setDate(minDropoffDate.getDate() + 1);
            endDatePicker.set('minDate', minDropoffDate);
          }
        }

        instance.input.dispatchEvent(new Event('change', { bubbles: true }));
      }
    };

    // Inicializar pickup date
    if (startInput) {
      if (startInput._flatpickr) {
        startInput._flatpickr.destroy();
      }
      const currentValue = getCurrentDateValue(startInput);
      if (currentValue) {
        const parsedDate = parseDateAny(currentValue);
        if (parsedDate) {
          startInput.value = parsedDate.toISOString().split('T')[0];
        }
      }

      // Inicializar Flatpickr
      startInput._flatpickr = flatpickr(startInput, commonConfig);
      setTimeout(() => {
        if (startInput._flatpickr && startInput.value) {
          const parsed = parseDateAny(startInput.value);
          if (parsed) {
            startInput._flatpickr.setDate(parsed, true);
          }
        }
      }, 50);
    }

    // Inicializar dropoff date
    if (endInput) {
      if (endInput._flatpickr) {
        endInput._flatpickr.destroy();
      }
      const currentValue = getCurrentDateValue(endInput);
      if (currentValue) {
        const parsedDate = parseDateAny(currentValue);
        if (parsedDate) {
          endInput.value = parsedDate.toISOString().split('T')[0];
        }
      }

      // Inicializar Flatpickr
      endInput._flatpickr = flatpickr(endInput, commonConfig);
      setTimeout(() => {
        if (endInput._flatpickr && endInput.value) {
          const parsed = parseDateAny(endInput.value);
          if (parsed) {
            endInput._flatpickr.setDate(parsed, true);
          }
        }
      }, 50);
    }
    const observer = new MutationObserver(() => {
      const newLocale = getFlatpickrLocale();
      if (startInput && startInput._flatpickr) startInput._flatpickr.set('locale', newLocale);
      if (endInput && endInput._flatpickr) endInput._flatpickr.set('locale', newLocale);
    });
    observer.observe(document.documentElement, { attributes: true, attributeFilter: ['lang'] });
  }

  // ===== ACTUALIZAR ICONOS =====
  function updateSelectIcon(select, iconElement) {
    const selectedOption = select.options[select.selectedIndex];
    if (selectedOption && selectedOption.dataset.icon) {
      iconElement.className = selectedOption.dataset.icon;
    } else {
      iconElement.className = 'fa-solid fa-location-dot';
    }
  }

  const pickupSelect = document.getElementById('pickupPlace');
  const pickupIcon = document.getElementById('pickupIcon');
  const dropoffSelect = document.getElementById('dropoffPlace');
  const dropoffIcon = document.getElementById('dropoffIcon');

  if (pickupSelect && pickupIcon) {
    pickupSelect.addEventListener('change', function () {
      updateSelectIcon(this, pickupIcon);
    });
    updateSelectIcon(pickupSelect, pickupIcon);
  }

  if (dropoffSelect && dropoffIcon) {
    dropoffSelect.addEventListener('change', function () {
      updateSelectIcon(this, dropoffIcon);
    });
    updateSelectIcon(dropoffSelect, dropoffIcon);
  }


  //nuevo agregadi
  function syncFlatpickrAfterStepChange() {
    // Observar cambios en el atributo data-current-step
    const main = document.querySelector('main.page');
    if (!main) return;

    const observer = new MutationObserver((mutations) => {
      mutations.forEach((mutation) => {
        if (mutation.attributeName === 'data-current-step') {
          setTimeout(() => {
            const start = document.getElementById('start');
            const end = document.getElementById('end');

            if (start && start._flatpickr && start.value) {
              const parsed = parseAnyToDate(start.value);
              if (parsed) start._flatpickr.setDate(parsed, true);
            }

            if (end && end._flatpickr && end.value) {
              const parsed = parseAnyToDate(end.value);
              if (parsed) end._flatpickr.setDate(parsed, true);
            }
          }, 100);
        }
      });
    });

    observer.observe(main, { attributes: true });
  }

  // Función auxiliar para parsear fechas
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

  // Inicializar el observador
  document.addEventListener('DOMContentLoaded', () => {
    syncFlatpickrAfterStepChange();
  });

  // inyectamos el Overlay
  (function injectFpOverlay() {
    if (document.getElementById("fp-view-overlay")) return;
    const overlay = document.createElement("div");
    overlay.id = "fp-view-overlay";
    overlay.className = "fp-view-overlay";
    document.body.appendChild(overlay);
  })();

});

// ===== FUNCIÓN PARA LIMPIAR EL FORMULARIO =====
function limpiarTodoYReiniciar() {
  const form = document.getElementById('step1Form');
  if (form) {
    form.reset();
    document.getElementById('pickup_time_hidden').value = '';
    document.getElementById('dropoff_time_hidden').value = '';

    const dropoffWrapper = document.getElementById('dropoffWrapper');
    const differentDropoff = document.getElementById('differentDropoff');
    if (dropoffWrapper && differentDropoff) {
      dropoffWrapper.style.display = 'none';
      differentDropoff.checked = false;
    }

    // Limpiar Select2 si existe
    if (typeof $ !== 'undefined' && $.fn.select2) {
      $('#pickupPlace').val('').trigger('change');
      $('#dropoffPlace').val('').trigger('change');
    }
  }
}

// ===== Select2 UNIFICADO (funciona en todos los dispositivos) =====
document.addEventListener("DOMContentLoaded", function () {
  if (typeof $ === 'undefined' || typeof $.fn.select2 === 'undefined') {
    console.warn('Select2 no disponible');
    return;
  }

  function formatOption(option) {
    if (!option.id) {
      return $('<span><i class="fa-solid fa-location-dot" style="margin-right: 8px; color: #333;"></i> ' + option.text + '</span>');
    }

    // Usar data-icon del elemento HTML
    let icon = $(option.element).data('icon');
    if (!icon) {
      icon = 'fa-solid fa-location-dot';
    }
    return $('<span><i class="' + icon + '" style="margin-right: 8px; color: #333;"></i> ' + option.text + '</span>');
  }

  const select2Config = {
    width: '100%',
    dropdownParent: $('body'),
    templateResult: formatOption,
    templateSelection: formatOption,
    escapeMarkup: function (m) { return m; },
    minimumResultsForSearch: Infinity,
    allowClear: false
  };

  // Inicializar pickup
  $('#pickupPlace').select2({
    ...select2Config,
    placeholder: $('#pickupPlace option:first').text()
  });

  // Inicializar dropoff
  const isChecked = document.getElementById('differentDropoff')?.checked || false;
  $('#dropoffPlace').select2({
    ...select2Config,
    placeholder: $('#dropoffPlace option:first').text(),
    disabled: !isChecked
  });

  // Actualizar icono flotante cuando cambia la selección
  function updateFloatingIcon(selectId, iconId) {
    const selectEl = document.getElementById(selectId);
    const iconEl = document.getElementById(iconId);
    if (!selectEl || !iconEl) return;

    const selectedOption = selectEl.options[selectEl.selectedIndex];
    let iconClass = 'fa-solid fa-location-dot';
    if (selectedOption && selectedOption.dataset && selectedOption.dataset.icon) {
      iconClass = selectedOption.dataset.icon;
    }
    iconEl.className = iconClass;
  }

  updateFloatingIcon('pickupPlace', 'pickupIcon');
  updateFloatingIcon('dropoffPlace', 'dropoffIcon');

  $('#pickupPlace').on('change', function () {
    updateFloatingIcon('pickupPlace', 'pickupIcon');
  });

  $('#dropoffPlace').on('change', function () {
    updateFloatingIcon('dropoffPlace', 'dropoffIcon');
  });

  console.log('Select2 unificado inicializado correctamente');
});


/* ============================================================
   CONVERSIÓN DE MONEDA PARA STEP 2 - RESERVACIONES
============================================================ */
(function () {
  "use strict";

  const EXCHANGE_RATE = 20;

  function getCurrentLanguage() {
    const htmlLang = document.documentElement.lang || 'es';
    return htmlLang === 'en' ? 'en' : 'es';
  }
  function getCurrencyCode(language) {
    return language === 'en' ? 'USD' : 'MXN';
  }

  function formatAmount(amount, currencyCode) {
    if (currencyCode === 'USD') {
      return amount.toLocaleString('en-US', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
      });
    } else {
      return amount.toLocaleString('es-MX', {
        minimumFractionDigits: 0,
        maximumFractionDigits: 0
      });
    }
  }

  function convertPricesStep2() {
    const language = getCurrentLanguage();
    const currencyCode = getCurrencyCode(language);

    const main = document.querySelector('main.page');
    const currentStep = main ? main.dataset.currentStep : '';
    if (currentStep !== '2') return;

    console.log('🔄 Convertiendo precios STEP 2 a:', currencyCode);

    const carCards = document.querySelectorAll('.car-card--v2');

    carCards.forEach((card) => {
      const priceMXN = parseFloat(card.dataset.priceMxn);
      const oldPriceMXN = parseFloat(card.dataset.oldPriceMxn);

      if (isNaN(priceMXN)) return;

      let displayPrice = currencyCode === 'USD'
        ? priceMXN / EXCHANGE_RATE
        : priceMXN;

      let displayOldPrice = !isNaN(oldPriceMXN)
        ? (currencyCode === 'USD' ? oldPriceMXN / EXCHANGE_RATE : oldPriceMXN)
        : null;

      const formattedPrice = formatAmount(displayPrice, currencyCode);
      const formattedOldPrice = displayOldPrice !== null
        ? formatAmount(displayOldPrice, currencyCode)
        : null;

      // ✅ ACTUALIZAR SOLO LOS NÚMEROS
      const priceNowSpan = card.querySelector('.js-prepago-total');
      const priceOldSpan = card.querySelector('.js-mostrador-total');

      if (priceNowSpan) {
        priceNowSpan.textContent = formattedPrice;
      }

      if (priceOldSpan && formattedOldPrice) {
        priceOldSpan.textContent = formattedOldPrice;
      }

      // ✅ CAMBIAR TEXTO MXN ↔ USD SIN ROMPER HTML
      const containers = card.querySelectorAll('.price-new, .price-old, .office-price');

      containers.forEach(el => {
        el.innerHTML = el.innerHTML.replace(/MXN|USD/g, currencyCode);
      });
    });

    console.log('💰 Moneda actual STEP 2:', currencyCode);
  }

  function listenForLanguageChangesStep2() {
    // ⚠️ storage NO funciona en misma pestaña, pero lo dejamos por si acaso
    window.addEventListener('storage', function (e) {
      if (e.key === 'idiomaPreferido') {
        setTimeout(convertPricesStep2, 100);
      }
    });

    // Detectar cambio de step
    const main = document.querySelector('main.page');
    if (main) {
      const observer = new MutationObserver(function (mutations) {
        mutations.forEach(function (mutation) {
          if (mutation.attributeName === 'data-current-step') {
            if (main.dataset.currentStep === '2') {
              setTimeout(convertPricesStep2, 150);
            }
          }
        });
      });
      observer.observe(main, { attributes: true });
    }

    // Al cargar
    if (document.readyState === 'loading') {
      document.addEventListener('DOMContentLoaded', function () {
        setTimeout(convertPricesStep2, 200);
      });
    } else {
      setTimeout(convertPricesStep2, 200);
    }

    // Cuando cambias idioma manualmente
    document.addEventListener('click', function (e) {
      if (e.target.closest('[data-language-selector], .language-selector, #languageSelect')) {
        setTimeout(convertPricesStep2, 150);
      }
    });
  }

  // 🚀 INICIO
  convertPricesStep2();
  listenForLanguageChangesStep2();

  window.convertPricesStep2 = convertPricesStep2;

})();