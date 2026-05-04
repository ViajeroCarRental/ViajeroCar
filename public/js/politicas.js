// public/js/politicas.js
(function () {
  "use strict";

  const AUTH_KEY = "vj_auth";

  function onReady(fn) {
    if (document.readyState === "loading") {
      document.addEventListener("DOMContentLoaded", fn, { once: true });
    } else {
      fn();
    }
  }

  // ============================================================
  // FUNCIÓN PARA OBTENER EL IDIOMA ACTUAL
  // ============================================================
  function getCurrentLocale() {
    const htmlLang = document.documentElement.lang || 'es';
    return htmlLang === 'en' ? 'en' : 'es';
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
  // FUNCIÓN PARA ACTUALIZAR FLATPICKR CON IDIOMA ACTUAL
  // ============================================================
  function updateFlatpickrLocale() {
    const localeData = getFlatpickrLocale();
    const pickupInput = document.getElementById('pickupDatePoliticas');
    const dropoffInput = document.getElementById('dropoffDatePoliticas');

    if (pickupInput && pickupInput._flatpickr) {
      pickupInput._flatpickr.set('locale', localeData);
    }
    if (dropoffInput && dropoffInput._flatpickr) {
      dropoffInput._flatpickr.set('locale', localeData);
    }
  }

  // ============================================================
  // FUNCIÓN PARA TEXTO DE ERRORES (VALIDACIÓN)
  // ============================================================
  function getErrorMessage(fieldType) {
    const locale = getCurrentLocale();
    const messages = {
      location: { es: 'Ubicación requerida', en: 'Location required' },
      date: { es: 'Fecha requerida', en: 'Date required' },
      time: { es: 'Hora requerida', en: 'Time required' }
    };
    return messages[fieldType]?.[locale] || messages[fieldType]?.es || 'Campo requerido';
  }

  // Helpers
  const qs = (s, r = document) => r.querySelector(s);
  const qsa = (s, r = document) => Array.from(r.querySelectorAll(s));

  function safeJsonParse(str) {
    try { return JSON.parse(str); } catch (_) { return null; }
  }

  if (!window.VJ_AUTH) {
    function getAuth() {
      return safeJsonParse(localStorage.getItem(AUTH_KEY) || "null");
    }
    function isLogged() {
      return !!localStorage.getItem(AUTH_KEY);
    }
    window.VJ_AUTH = { getAuth, isLogged };
  }

  // =========================
  //  Polyfills
  // =========================
  if (!Element.prototype.closest) {
    Element.prototype.closest = function(s) {
      let el = this;
      do {
        if (el.matches(s)) return el;
        el = el.parentElement || el.parentNode;
      } while (el !== null && el.nodeType === 1);
      return null;
    };
  }

  // =========================
  //  Función para inyectar CSS de selects de hora
  // =========================
  (function injectTimeCss() {
    const id = "tpHideInputStyle";
    if (document.getElementById(id)) return;
    const st = document.createElement("style");
    st.id = id;
    st.textContent = `
      .tp-hidden-input { display: none !important; }
      .tp-selects { display: flex; gap: 10px; margin-top: 10px; }
      .tp-selects select {
        width: 100%;
        height: 48px;
        border-radius: 8px;
        border: 1px solid #ccc;
        padding: 0 8px 0 36px;
        font-size: 14px;
        color: #666;
        background: white;
        outline: none;
        appearance: none;
        background-image: url("data:image/svg+xml;utf8,<svg xmlns='http://www.w3.org/2000/svg' width='16' height='16' viewBox='0 0 24 24' fill='none' stroke='%23999' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'><polyline points='6 9 12 15 18 9'/></svg>");
        background-repeat: no-repeat;
        background-position: right 10px center;
        background-size: 12px;
      }
      .tp-selects select:focus {
        border-color: var(--brand);
        box-shadow: 0 0 0 3px rgba(178,34,34,.15);
      }
      .time-field { position: relative; }
      .time-field::before {
        content: "\\f017";
        font-family: "Font Awesome 6 Free";
        font-weight: 400;
        position: absolute;
        left: 12px;
        top: 50%;
        transform: translateY(-50%);
        width: 18px;
        height: 18px;
        font-size: 16px;
        color: #9ca3af;
        display: flex;
        align-items: center;
        justify-content: center;
        z-index: 10;
        pointer-events: none;
      }
      .time-field .field-icon { display: none !important; }
    `;
    document.head.appendChild(st);
  })();
  // Inyectar el div del overlay si no existe
(function injectOverlay() {
    if (document.getElementById("fp-overlay")) return;
    const overlay = document.createElement("div");
    overlay.id = "fp-overlay";
    overlay.className = "fp-overlay";
    document.body.appendChild(overlay);
})();

  // =========================
  //  SELECTS DE HORA
  // =========================
  function pad2(n) {
    return String(n).padStart(2, "0");
  }

  function isSameLocalDate(dateStr, dateObj) {
  if (!dateStr || !dateObj) return false;

  const y = dateObj.getFullYear();
  const m = pad2(dateObj.getMonth() + 1);
  const d = pad2(dateObj.getDate());

  return dateStr === `${y}-${m}-${d}`;
}

function getMinPickupHour() {
  const now = new Date();
  return now.getHours() + 1;
}

function rebuildHourOptions(input, opts = {}) {
  const { hourMax = 24 } = opts;

  const wrap = input.closest(".time-field") || input.parentElement;
  if (!wrap) return;

  const selH = wrap.querySelector(".tp-selects .tp-hour");
  if (!selH) return;

  const previousValue = selH.value;
  const locale = getCurrentLocale();
  const hourPlaceholder = locale === 'en' ? 'Time' : 'Hora';

  let startHour = 0;

  // SOLO pickupTimePoliticas debe bloquear horas pasadas si la fecha es hoy
  if (input.id === "pickupTimePoliticas") {
    const pickupDateValue = document.getElementById("pickupDatePoliticas")?.value || "";
    if (isSameLocalDate(pickupDateValue, new Date())) {
      startHour = getMinPickupHour();
    }
  }
  selH.innerHTML = "";
  selH.insertAdjacentHTML("afterbegin", `<option value="" disabled>${hourPlaceholder}</option>`);

  for (let h = startHour; h < hourMax; h++) {
    const val24 = pad2(h);
    const op = document.createElement("option");
    op.value = val24;
    op.textContent = `${val24}:00`;

    selH.appendChild(op);
  }

  input.value = `${selH.value}:00`;

  const stillExists = Array.from(selH.options).some(opt => opt.value === previousValue);

  if (stillExists && previousValue !== "") {
    selH.value = previousValue;
    input.value = `${previousValue}:00`;
  } else {
    selH.selectedIndex = 0;
    input.value = "";
  }

  input.dispatchEvent(new Event("input", { bubbles: true }));
  input.dispatchEvent(new Event("change", { bubbles: true }));
}

function createTimeSelectsBelow(input, opts) {
  const { hourMax = 24, defaultValue = "12:00" } = (opts || {});
  const wrap = input.closest(".time-field") || input.parentElement;
  if (wrap && wrap.querySelector(".tp-selects")) return;

  const box = document.createElement("div");
  box.className = "tp-selects w-100";

  const selH = document.createElement("select");
  selH.className = "tp-hour";

  const locale = getCurrentLocale();
  const hourPlaceholder = locale === 'en' ? 'Time' : 'Hora';
  selH.setAttribute("aria-label", hourPlaceholder);

  box.appendChild(selH);

  if (wrap) {
    wrap.appendChild(box);
  } else {
    input.insertAdjacentElement("afterend", box);
  }

  rebuildHourOptions(input, { hourMax });

  function sync() {
    if (!selH.value) {
      input.value = "";
      input.dispatchEvent(new Event("input", { bubbles: true }));
      input.dispatchEvent(new Event("change", { bubbles: true }));
      return;
    }

    const finalH = pad2(Number(selH.value || 0));
    input.value = `${finalH}:00`;
    input.dispatchEvent(new Event("input", { bubbles: true }));
    input.dispatchEvent(new Event("change", { bubbles: true }));
  }

    selH.addEventListener("change", sync);

  // Marcar que el usuario ha interactuado con este select
  selH.addEventListener("focus", function() {
    this.setAttribute('data-user-interacted', 'true');
  });

  selH.addEventListener("change", function() {
    this.setAttribute('data-user-interacted', 'true');
  });

}

function initAnalogTime(id) {
  const input = document.getElementById(id);
  if (!input) return;
  if (input.dataset.tpReady === "1") return;
  input.dataset.tpReady = "1";

  input.setAttribute("readonly", "readonly");
  input.setAttribute("inputmode", "none");
  input.classList.add("tp-hidden-input");
  input.setAttribute("aria-hidden", "true");

  createTimeSelectsBelow(input, {
    hourMax: 24,
    defaultValue: input.value || "13:00"
  });

  // SOLO pickupTimePoliticas reacciona al cambio de fecha
  if (id === "pickupTimePoliticas") {
    const pickupDate = document.getElementById("pickupDatePoliticas");
    if (pickupDate) {
      pickupDate.addEventListener("change", function() {
        rebuildHourOptions(input, { hourMax: 24 });
      });
    }
  }
}

  function force24h() {
    document.querySelectorAll('.tp-hour option').forEach(opt => {
      if (opt.value !== "") {
        opt.textContent = String(opt.value).padStart(2, '0') + ':00';
      }
    });
  }
  setTimeout(force24h, 0);
  setTimeout(force24h, 100);
  setTimeout(force24h, 500);
  setTimeout(force24h, 1000);

 // =========================
//  FUNCIÓN PARA HORAS POR DEFECTO A LAS 13:00
// =========================
function setDefaultDates() {
    const pickupTime = document.getElementById('pickupTimePoliticas');
    const dropoffTime = document.getElementById('dropoffTimePoliticas');

    const setDefaults = (input) => {
        if (!input) return;

        input.value = "13:00";

        const field = input.closest('.time-field');
        if (field) {
            const hourSelect = field.querySelector('.tp-selects .tp-hour');
            if (hourSelect) {
                hourSelect.value = "13";

                if (hourSelect.options[0].value === "") {
                    hourSelect.options[0].selected = true;
                }

                hourSelect.addEventListener('mousedown', () => {
                    if (hourSelect.value === "") {
                        hourSelect.value = "13";
                    }
                }, { once: true });
            }
        }
    };

    setDefaults(pickupTime);
    setDefaults(dropoffTime);
}

  // =========================
  //  SELECT2 CON ICONOS (AHORA FUNCIONA EN TODOS LOS DISPOSITIVOS)
  // =========================
  function setupSelect2Iconos() {
    if (typeof $ === 'undefined' || typeof $.fn.select2 === 'undefined') {
      console.warn('Select2 no está disponible');
      return;
    }

    console.log('Inicializando Select2 con iconos en todos los dispositivos...');

    function formatOption(option) {
      if (!option.id) {
        return $('<span><i class="fa-solid fa-location-dot" style="margin-right: 8px; color: #333;"></i> ' + option.text + '</span>');
      }
      let iconClass = window.iconosPorId ? (window.iconosPorId[option.id] || 'fa-building') : 'fa-building';
      return $('<span><i class="fa-solid ' + iconClass + '" style="margin-right: 8px; color: #333;"></i> ' + option.text + '</span>');
    }

    const modal = document.getElementById('miBuscadorPoliticas');
    const dropoffWrapper = document.getElementById('dropoffWrapperPoliticas');
    const dropoffSelect = document.getElementById('dropoffPlacePoliticas');

    let originalDisplay = dropoffWrapper ? dropoffWrapper.style.display : null;
    let originalDisabled = dropoffSelect ? dropoffSelect.disabled : null;

    if (dropoffWrapper && dropoffSelect) {
      dropoffWrapper.style.display = 'block';
      dropoffSelect.disabled = false;
    }

    const select2Config = {
      templateResult: formatOption,
      templateSelection: formatOption,
      escapeMarkup: function(m) { return m; },
      width: '100%',
      minimumResultsForSearch: Infinity,
      allowClear: false,
      dropdownParent: modal ? $(modal) : $('body')
    };

    try {
      ['#pickupPlacePoliticas', '#dropoffPlacePoliticas'].forEach(selector => {
        if ($(selector).data('select2')) {
          $(selector).select2('destroy');
        }
      });
    } catch(e) {
      console.log('Error destruyendo instancias:', e);
    }

    $('#pickupPlacePoliticas').select2({
      ...select2Config,
      placeholder: $('#pickupPlacePoliticas option:first').text()
    });

    $('#dropoffPlacePoliticas').select2({
      ...select2Config,
      placeholder: $('#dropoffPlacePoliticas option:first').text()
    });

    setTimeout(() => {
      $('#pickupPlacePoliticas').val(null).trigger('change.select2');
      $('#dropoffPlacePoliticas').val(null).trigger('change.select2');
    }, 300);

    setTimeout(() => {
      if (dropoffWrapper && dropoffSelect) {
        dropoffWrapper.style.display = originalDisplay === 'none' ? 'none' : originalDisplay;
        dropoffSelect.disabled = originalDisabled;
        if (originalDisabled) {
          $('#dropoffPlacePoliticas')
            .prop('disabled', true)
            .trigger('change.select2');
        }
      }
    }, 150);

    console.log('Select2 listo y funcionando correctamente en todos los dispositivos 🔥');
  }

  // =========================
  //  FUNCIÓN PARA CAMBIAR ICONOS SEGÚN SELECCIÓN
  // =========================
  function setupIconosDinamicos() {
    const pickupSelect = document.getElementById('pickupPlacePoliticas');
    const dropoffSelect = document.getElementById('dropoffPlacePoliticas');
    const pickupIcon = document.getElementById('pickupIcon');
    const dropoffIcon = document.getElementById('dropoffIcon');

    function getIconClass(text) {
      const textoLower = text.toLowerCase();
      if (textoLower.includes('aeropuerto') || textoLower.includes('airport')) {
        return 'fa-plane-departure';
      } else if (textoLower.includes('central') || textoLower.includes('terminal') || textoLower.includes('bus')) {
        return 'fa-bus';
      }
      return 'fa-building';
    }

    function updateIcon(select, iconElement) {
      if (!select || !iconElement) return;
      if (select.value && select.value !== '') {
        const selectedOption = select.options[select.selectedIndex];
        const iconClass = getIconClass(selectedOption.text);
        iconElement.innerHTML = `<i class="fa-solid ${iconClass}"></i>`;
      } else {
        iconElement.innerHTML = '<i class="fa-solid fa-location-dot"></i>';
      }
    }

    if (pickupSelect && pickupIcon) {
      updateIcon(pickupSelect, pickupIcon);
      pickupSelect.addEventListener('change', () => updateIcon(pickupSelect, pickupIcon));
    }
    if (dropoffSelect && dropoffIcon) {
      updateIcon(dropoffSelect, dropoffIcon);
      dropoffSelect.addEventListener('change', () => updateIcon(dropoffSelect, dropoffIcon));
    }
  }

  // =========================
  //  CHECKBOX - CONTROL DROPOFF (VERSIÓN MEJORADA)
  // =========================
  function setupCheckbox() {
    const chk = document.getElementById('differentDropoffPoliticas');
    const dropWrap = document.getElementById('dropoffWrapperPoliticas');
    const pickSel = document.getElementById('pickupPlacePoliticas');
    const dropSel = document.getElementById('dropoffPlacePoliticas');

    if (!chk || !dropWrap) return;

    function updateDropoffState() {
      const isChecked = chk.checked;
      dropWrap.style.display = isChecked ? 'block' : 'none';
      if (dropSel) {
        dropSel.disabled = !isChecked;
        dropSel.required = isChecked;
        if (typeof $ !== 'undefined' && $.fn.select2) {
          $('#dropoffPlacePoliticas').prop('disabled', !isChecked).trigger('change');
        }
        if (!isChecked && pickSel && pickSel.value) {
          dropSel.value = pickSel.value;
          if (typeof $ !== 'undefined' && $.fn.select2) {
            $('#dropoffPlacePoliticas').val(pickSel.value).trigger('change');
          }
        }
      }
    }
    updateDropoffState();
    chk.addEventListener('change', updateDropoffState);
    if (pickSel && dropSel) {
      pickSel.addEventListener('change', function() {
        if (!chk.checked) {
          dropSel.value = this.value;
          if (typeof $ !== 'undefined' && $.fn.select2) {
            $('#dropoffPlacePoliticas').val(this.value).trigger('change');
          }
        }
      });
    }
  }

  // =========================
  //  ERRORES (CON TRADUCCIÓN)
  // =========================
  function showError(element, message) {
    if (!element) return;
    element.classList.remove('field-success');
    element.classList.remove('field-error');
    const oldError = element.parentElement?.querySelector('.error-msg');
    if (oldError) oldError.remove();
    element.classList.add('field-error');
    if (typeof $ !== 'undefined' && $.fn.select2) {
      $(element).next('.select2-container').find('.select2-selection')
        .removeClass('field-success')
        .addClass('field-error');
    }
    const errorDiv = document.createElement('div');
    errorDiv.className = 'error-msg';
    errorDiv.textContent = message;
    element.parentElement?.appendChild(errorDiv);
    console.log('Error mostrado:', message, 'en', element);
  }

  // =========================
  //  VALIDACIÓN DEL FORMULARIO - POLÍTICAS (CON TRADUCCIÓN)
  // =========================
  function setupValidation() {
    const form = document.getElementById('rentalFormPoliticas');
    if (!form) return;

    form.addEventListener('submit', function (e) {
      e.preventDefault();
      console.log('=== VALIDANDO FORMULARIO POLÍTICAS ===');
      let valid = true;
      form.querySelectorAll('.error-msg').forEach(el => el.remove());
      form.querySelectorAll('.field-error, .field-success').forEach(el => {
        el.classList.remove('field-error', 'field-success');
      });
      if (typeof $ !== 'undefined' && $.fn.select2) {
        form.querySelectorAll('.select2-selection').forEach(el => {
          el.classList.remove('field-error', 'field-success');
        });
      }

      const checkbox = document.getElementById('differentDropoffPoliticas');
      const selects = [
        { id: 'pickupPlacePoliticas', msgKey: 'location' }
      ];
      if (checkbox && checkbox.checked) {
        selects.push({ id: 'dropoffPlacePoliticas', msgKey: 'location' });
      }

      selects.forEach(campo => {
        const select = document.getElementById(campo.id);
        if (!select) return;
        const container = select.closest('.field');
        if (!select.value) {
          valid = false;
          select.classList.add('field-error');
          if (typeof $ !== 'undefined' && $.fn.select2) {
            $(select).next('.select2-container').find('.select2-selection').addClass('field-error');
          }
          if (container) {
            const msg = document.createElement('span');
            msg.className = 'error-msg';
            msg.textContent = getErrorMessage(campo.msgKey);
            container.appendChild(msg);
          }
        } else {
          select.classList.add('field-success');
          if (typeof $ !== 'undefined' && $.fn.select2) {
            $(select).next('.select2-container').find('.select2-selection').addClass('field-success');
          }
        }
      });

      const fechas = [
        { id: 'pickupDatePoliticas', msgKey: 'date' },
        { id: 'dropoffDatePoliticas', msgKey: 'date' }
      ];
      fechas.forEach(campo => {
        const hiddenInput = document.getElementById(campo.id);
        if (!hiddenInput) return;
        const picker = hiddenInput._flatpickr;
        const altInput = picker ? picker.altInput : null;
        const container = hiddenInput.closest('.dt-field');
        const hasValue = hiddenInput.value && hiddenInput.value.trim() !== '';
        if (!hasValue) {
          valid = false;
          if (altInput) {
            altInput.classList.add('field-error');
            altInput.classList.remove('field-success');
          }
          if (container) {
            const msg = document.createElement('span');
            msg.className = 'error-msg';
            msg.textContent = getErrorMessage(campo.msgKey);
            container.appendChild(msg);
          }
        } else {
          if (altInput) {
            altInput.classList.add('field-success');
            altInput.classList.remove('field-error');
          }
        }
      });

            // ===== 3. VALIDAR HORAS - SOLO SI EL USUARIO HA INTERACTUADO =====
      const horas = [
        { id: 'pickupTimePoliticas', msgKey: 'time' },
        { id: 'dropoffTimePoliticas', msgKey: 'time' }
      ];

      horas.forEach(campo => {
        const hiddenInput = document.getElementById(campo.id);
        if (!hiddenInput) return;
        const timeField = hiddenInput.closest('.time-field');
        if (!timeField) return;
        const hourSelect = timeField.querySelector('.tp-selects .tp-hour');

        const userInteracted = hourSelect && hourSelect.hasAttribute('data-user-interacted');
        const hasRealValue = hiddenInput.value && hiddenInput.value !== "";
        const isValid = userInteracted && hasRealValue;


        if (!isValid) {
          valid = false;
          if (hourSelect) {
            hourSelect.classList.add('field-error');
            hourSelect.classList.remove('field-success');
          }
          hiddenInput.classList.add('field-error');
          hiddenInput.classList.remove('field-success');
          const msg = document.createElement('span');
          msg.className = 'error-msg';
          msg.textContent = getErrorMessage(campo.msgKey);
          timeField.appendChild(msg);
        } else {
          if (hourSelect) {
            hourSelect.classList.remove('field-error');
            hourSelect.classList.add('field-success');
          }
          hiddenInput.classList.remove('field-error');
          hiddenInput.classList.add('field-success');
        }
      });

      console.log('Resultado:', valid ? ' VÁLIDO' : ' INVÁLIDO');
      if (valid) {
        const pickup = document.getElementById('pickupPlacePoliticas');
        const dropoff = document.getElementById('dropoffPlacePoliticas');
        const checkbox = document.getElementById('differentDropoffPoliticas');
        if (checkbox && !checkbox.checked) {
          dropoff.value = pickup.value;
        }
        form.submit();
      }
    });
  }

   // =========================
  // FLATPICKR - CON IDIOMA DINÁMICO (VISTA POLÍTICAS)
  // =========================

  function setupFlatpickr() {
    if (typeof flatpickr === 'undefined') {
        console.error('Flatpickr no está cargado');
        return;
    }

    const pickupInput = document.getElementById('pickupDatePoliticas');
    const dropoffInput = document.getElementById('dropoffDatePoliticas');
    const overlay = document.getElementById('fp-overlay');

    function initPicker(input) {
        if (!input) return;
        const localeData = getFlatpickrLocale();

        const picker = flatpickr(input, {
            dateFormat: "Y-m-d",
            altInput: true,
            altFormat: "d-M-y",
            minDate: "today",
            allowInput: false,
            disableMobile: true,
            locale: localeData,

            // EVENTOS PARA EL FONDO
            onOpen: function() {
                if (overlay) overlay.classList.add('active');
            },
            onClose: function() {
                if (overlay) overlay.classList.remove('active');
            },

            onChange: function(selectedDates, dateStr, instance) {

                if (input.id === 'pickupDatePoliticas' && selectedDates[0]) {
                    const dropoffPicker = document.getElementById('dropoffDatePoliticas')._flatpickr;

                    if (dropoffPicker) {
                        const minDropoffDate = new Date(selectedDates[0]);

                        minDropoffDate.setDate(minDropoffDate.getDate() + 1);


                        dropoffPicker.set('minDate', minDropoffDate);
                    }
                }
            }
        });
        return picker;
    }

    if (pickupInput) initPicker(pickupInput);
    if (dropoffInput) initPicker(dropoffInput);
}
  // =========================
  //  MODAL
  // =========================
  function setupPolicyModal() {
    const modal = qs("#policyModal");
    const modalBody = qs("#policyModalBody");
    const modalTitle = qs("#policyModalTitle");
    if (!modal || !modalBody || !modalTitle) return;

    let lastFocus = null;

    function openModal(title, tplId) {
      const tpl = qs(`#${tplId}`);
      if (!tpl) return;
      lastFocus = document.activeElement;
      modalTitle.textContent = title || "Política";
      modalBody.innerHTML = tpl.innerHTML;
      modal.classList.add("open");
      document.body.classList.add("modal-open");
    }

    function closeModal() {
      modal.classList.remove("open");
      document.body.classList.remove("modal-open");
      modalBody.innerHTML = "";
      if (lastFocus && typeof lastFocus.focus === "function") {
        lastFocus.focus();
      }
    }

    qsa(".policy-card").forEach((btn) => {
      btn.addEventListener("click", () => {
        const tplId = btn.getAttribute("data-modal");
        const title = btn.getAttribute("data-title") || btn.textContent.trim();
        if (tplId) openModal(title, tplId);
      });
    });

    qsa('[data-close="1"]', modal).forEach((el) => el.addEventListener("click", closeModal));
    modal.addEventListener("click", (e) => {
      if (e.target.matches(".vj-modal__backdrop")) closeModal();
    });
    window.addEventListener("keydown", (e) => {
      if (e.key === "Escape" && modal.classList.contains("open")) closeModal();
    });
  }

  // ========================
  //  NAVBAR
  // =========================
  function setupNavbar() {
    const topbar = qs("#topbar");
    function toggleTopbar() {
      if (topbar) {
        window.scrollY > 40 ? topbar.classList.add("solid") : topbar.classList.remove("solid");
      }
    }
    toggleTopbar();
    window.addEventListener("scroll", toggleTopbar, { passive: true });
  }

  // =========================
  //  ACCOUNT LINK
  // =========================
  function setupAccountLink() {
    const accountLink = qs("#accountLink");
    if (!accountLink) return;

    const logged = window.VJ_AUTH?.isLogged?.() || false;
    const auth = window.VJ_AUTH?.getAuth?.() || {};
    const loginUrl = accountLink.getAttribute("data-login-url") || "/login";
    const profileUrl = accountLink.getAttribute("data-profile-url") || "/perfil";
    const locale = getCurrentLocale();

    if (logged) {
      accountLink.href = profileUrl;
      accountLink.title = locale === 'en' ? 'My profile' : 'Mi perfil';
      const letter = (auth.name?.[0] || auth.email?.[0] || "U").toUpperCase();
      accountLink.innerHTML = `<span class="avatar-mini">${letter}</span>`;
    } else {
      accountLink.href = loginUrl;
      accountLink.title = locale === 'en' ? 'Sign in' : 'Iniciar sesión';
      accountLink.innerHTML = `<i class="fa-regular fa-user"></i>`;
    }
  }

  // =========================
  // FOOTER YEAR
  // =========================
  function setupFooterYear() {
    const yearEl = qs("#year");
    if (yearEl) yearEl.textContent = new Date().getFullYear();
  }

  // ============================================================
  // CONTROL DE SCROLL PARA FORMULARIO POLÍTICAS
  // ============================================================
  function initBuscadorPoliticas() {
    const btnAbrir = document.getElementById('btn-abrir-buscador-politicas');
    const btnCerrar = document.getElementById('btn-cerrar-buscador-politicas');
    const buscador = document.getElementById('miBuscadorPoliticas');

    if (!btnAbrir || !btnCerrar || !buscador) {
      console.log('Elementos del buscador no encontrados');
      return;
    }

    function bloquearScroll() {
      const scrollY = window.scrollY;
      document.body.style.position = 'fixed';
      document.body.style.top = `-${scrollY}px`;
      document.body.style.left = '0';
      document.body.style.right = '0';
      document.body.style.overflow = 'hidden';
      document.body.style.width = '100%';
      document.body.dataset.scrollY = scrollY;
    }

    function restaurarScroll() {
      document.body.style.position = '';
      document.body.style.top = '';
      document.body.style.left = '';
      document.body.style.right = '';
      document.body.style.overflow = '';
      document.body.style.width = '';
      const scrollY = document.body.dataset.scrollY || 0;
      window.scrollTo(0, parseInt(scrollY));
      delete document.body.dataset.scrollY;
    }

    btnAbrir.addEventListener('click', function(e) {
      e.preventDefault();
      buscador.classList.add('active');
      bloquearScroll();
    });

    btnCerrar.addEventListener('click', function(e) {
      e.preventDefault();
      buscador.classList.remove('active');
      restaurarScroll();
    });

    window.addEventListener('keydown', function(e) {
      if (e.key === 'Escape' && buscador.classList.contains('active')) {
        buscador.classList.remove('active');
        restaurarScroll();
      }
    });

    document.body.addEventListener('touchmove', function(e) {
        if (buscador.classList.contains('active')) {
            const target = e.target;
            const isScrollable = target.closest('.select2-results__options, .select2-dropdown, select, .vj-modal__body');
            if (!isScrollable) {
                e.preventDefault();
            }
        }
    }, { passive: false });
}

  // =========================
  //  INICIALIZACIÓN PRINCIPAL
  // =========================
  onReady(() => {
    console.log('Inicializando políticas.js');

    setupNavbar();
    setupAccountLink();
    setupFooterYear();
    setupPolicyModal();

    initAnalogTime("pickupTimePoliticas");
    initAnalogTime("dropoffTimePoliticas");

    setupFlatpickr();
    setDefaultDates();

    initBuscadorPoliticas();

    setTimeout(() => {
      setupSelect2Iconos();
      setupIconosDinamicos();
      setupCheckbox();
      setupValidation();
    }, 800);

    const pickupTime = document.getElementById("pickupTimePoliticas");
  const dropoffTime = document.getElementById("dropoffTimePoliticas");
  const form = document.getElementById("rentalFormPoliticas");

  if (pickupTime) {
    pickupTime.addEventListener("focus", function () {
      if (!this.value) {
        this.value = "13:00";
      }
    });
  }

  if (dropoffTime) {
    dropoffTime.addEventListener("focus", function () {
      if (!this.value) {
        this.value = "13:00";
      }
    });
  }

  if (form) {
    form.addEventListener("submit", function () {
      if (pickupTime && !pickupTime.value) pickupTime.value = "13:00";
      if (dropoffTime && !dropoffTime.value) dropoffTime.value = "13:00";
    });
  }


    // Escuchar cambios de idioma para actualizar Flatpickr y placeholders
    const observer = new MutationObserver(() => {
      updateFlatpickrLocale();
      // Actualizar placeholder de horas
      const locale = getCurrentLocale();
      const hourPlaceholder = locale === 'en' ? 'Hour' : 'Hora';
      document.querySelectorAll('.tp-hour').forEach(select => {
        if (select.options[0] && select.options[0].textContent !== hourPlaceholder) {
          select.options[0].textContent = hourPlaceholder;
        }
      });
    });
    observer.observe(document.documentElement, { attributes: true, attributeFilter: ['lang'] });

    console.log(' Formulario de políticas listo');
  });
})();
