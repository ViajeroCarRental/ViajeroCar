/* =====================================================================
 *  politicas.js — Vista de Políticas Viajero
 *
 *  - Topbar: clase .solid al hacer scroll
 *  - Modal de políticas (vj-modal)
 *  - Buscador de reservas en hero (Flatpickr + Select2 + selects de hora)
 *  - Hora: placeholder "Hora" y al primer clic → 13:00
 * ===================================================================== */
(function () {
  "use strict";

  /* =================================================================
     HELPERS
     ================================================================= */
  const qs  = (s, r = document) => r.querySelector(s);
  const qsa = (s, r = document) => Array.from(r.querySelectorAll(s));
  const pad2 = n => String(n).padStart(2, "0");

  function onReady(fn) {
    if (document.readyState === "loading") {
      document.addEventListener("DOMContentLoaded", fn, { once: true });
    } else {
      fn();
    }
  }

  function getCurrentLocale() {
    return (document.documentElement.lang || 'es') === 'en' ? 'en' : 'es';
  }

  function getErrorMessage(fieldType) {
    const locale = getCurrentLocale();
    const messages = {
      location: { es: 'Ubicación requerida', en: 'Location required' },
      date:     { es: 'Fecha requerida',     en: 'Date required' },
      time:     { es: 'Hora requerida',      en: 'Time required' }
    };
    return messages[fieldType]?.[locale] || 'Campo requerido';
  }

  /* =================================================================
     LOCALE PARA FLATPICKR (ES / EN)
     ================================================================= */
  function getFlatpickrLocale() {
    if (getCurrentLocale() === 'en') {
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

  function updateFlatpickrLocale() {
    const localeData = getFlatpickrLocale();
    ['pickupDatePoliticas', 'dropoffDatePoliticas'].forEach(id => {
      const input = document.getElementById(id);
      if (input?._flatpickr) input._flatpickr.set('locale', localeData);
    });
  }

  /* =================================================================
     TOPBAR: clase .solid al hacer scroll
     ================================================================= */
  function setupNavbar() {
    const topbar = qs(".topbar");
    if (!topbar) return;

    let isSolid = false;
    function update() {
      const shouldBeSolid = window.scrollY > 40;
      if (shouldBeSolid !== isSolid) {
        topbar.classList.toggle("solid", shouldBeSolid);
        isSolid = shouldBeSolid;
      }
    }
    update();
    window.addEventListener("scroll", update, { passive: true });
  }

  /* =================================================================
     AÑO EN EL FOOTER
     ================================================================= */
  function setupFooterYear() {
    const yearEl = qs("#year");
    if (yearEl) yearEl.textContent = new Date().getFullYear();
  }

  /* =================================================================
     MODAL DE POLÍTICAS
     ================================================================= */
  function setupPolicyModal() {
    const modal      = qs("#policyModal");
    const modalBody  = qs("#policyModalBody");
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
      modal.setAttribute("aria-hidden", "false");
      document.body.classList.add("modal-open");
    }

    function closeModal() {
      modal.classList.remove("open");
      modal.setAttribute("aria-hidden", "true");
      document.body.classList.remove("modal-open");
      modalBody.innerHTML = "";
      if (lastFocus && typeof lastFocus.focus === "function") lastFocus.focus();
    }

    qsa(".policy-card").forEach(btn => {
      btn.addEventListener("click", () => {
        const tplId = btn.getAttribute("data-modal");
        const title = btn.getAttribute("data-title") || btn.textContent.trim();
        if (tplId) openModal(title, tplId);
      });
    });

    qsa('[data-close="1"]', modal).forEach(el => el.addEventListener("click", closeModal));
    modal.addEventListener("click", (e) => {
      if (e.target.matches(".vj-modal__backdrop")) closeModal();
    });
    window.addEventListener("keydown", (e) => {
      if (e.key === "Escape" && modal.classList.contains("open")) closeModal();
    });
  }

  /* =================================================================
     SELECTS DE HORA (custom)
     ================================================================= */
  function isSameLocalDate(dateStr, dateObj) {
    if (!dateStr || !dateObj) return false;
    const y = dateObj.getFullYear();
    const m = pad2(dateObj.getMonth() + 1);
    const d = pad2(dateObj.getDate());
    return dateStr === `${y}-${m}-${d}`;
  }

  function getMinPickupHour() {
    return new Date().getHours() + 1;
  }

  // ========== Configuración del "clic para 13:00" ==========
  function setupDefaultTimeOnClick(selectElem, inputElem) {
    if (selectElem.dataset.defaultTimeSetup === "1") return;
    selectElem.dataset.defaultTimeSetup = "1";

    if (inputElem.value && inputElem.value.trim() !== "") return;

    function applyDefaultIfNeeded() {
      if (selectElem.dataset.defaultApplied === "true") return;
      if (selectElem.value && selectElem.value !== "") return;

      const defaultHour = "13";
      const option = Array.from(selectElem.options).find(opt => opt.value === defaultHour);
      if (option) {
        selectElem.value = defaultHour;
        inputElem.value = `${defaultHour}:00`;
        inputElem.dispatchEvent(new Event("input", { bubbles: true }));
        inputElem.dispatchEvent(new Event("change", { bubbles: true }));
        selectElem.dataset.defaultApplied = "true";
      }
    }

    selectElem.addEventListener("click", function onClick() {
      applyDefaultIfNeeded();
      selectElem.removeEventListener("click", onClick);
    }, { once: true });

    selectElem.addEventListener("focus", function onFocus() {
      applyDefaultIfNeeded();
      selectElem.removeEventListener("focus", onFocus);
    }, { once: true });
  }

  function rebuildHourOptions(input, hourMax = 24) {
    const wrap = input.closest(".time-field") || input.parentElement;
    if (!wrap) return;

    const selH = wrap.querySelector(".tp-selects .tp-hour");
    if (!selH) return;

    const previousValue = selH.value;
    const hourPlaceholder = getCurrentLocale() === 'en' ? 'Time' : 'Hora';

    let startHour = 0;
    if (input.id === "pickupTimePoliticas") {
      const pickupDateValue = document.getElementById("pickupDatePoliticas")?.value || "";
      if (isSameLocalDate(pickupDateValue, new Date())) {
        startHour = getMinPickupHour();
      }
    }

    selH.innerHTML = `<option value="" disabled selected>${hourPlaceholder}</option>`;
    for (let h = startHour; h < hourMax; h++) {
      const val24 = pad2(h);
      const op = document.createElement("option");
      op.value = val24;
      op.textContent = `${val24}:00`;
      selH.appendChild(op);
    }

    const stillExists = Array.from(selH.options).some(opt => opt.value === previousValue);
    if (stillExists && previousValue !== "") {
      selH.value = previousValue;
      input.value = `${previousValue}:00`;

      selH.dataset.defaultApplied = "true";
    } else {
      selH.selectedIndex = 0;
      input.value = "";
      delete selH.dataset.defaultApplied;
    }

    input.dispatchEvent(new Event("input", { bubbles: true }));
    input.dispatchEvent(new Event("change", { bubbles: true }));
  }

  function createTimeSelectsBelow(input, hourMax = 24) {
    const wrap = input.closest(".time-field") || input.parentElement;
    if (wrap?.querySelector(".tp-selects")) return;

    const box = document.createElement("div");
    box.className = "tp-selects w-100";

    const selH = document.createElement("select");
    selH.className = "tp-hour";
    selH.setAttribute("aria-label", getCurrentLocale() === 'en' ? 'Time' : 'Hora');

    box.appendChild(selH);
    if (wrap) wrap.appendChild(box);
    else input.insertAdjacentElement("afterend", box);

    rebuildHourOptions(input, hourMax);
    setupDefaultTimeOnClick(selH, input);

    function sync() {
      if (!selH.value) {
        input.value = "";
      } else {
        input.value = `${pad2(Number(selH.value || 0))}:00`;
      }
      input.dispatchEvent(new Event("input", { bubbles: true }));
      input.dispatchEvent(new Event("change", { bubbles: true }));
      selH.setAttribute('data-user-interacted', 'true');
    }

    selH.addEventListener("change", sync);
    selH.addEventListener("focus", () => selH.setAttribute('data-user-interacted', 'true'));
  }

  function initAnalogTime(id) {
    const input = document.getElementById(id);
    if (!input || input.dataset.tpReady === "1") return;
    input.dataset.tpReady = "1";

    input.setAttribute("readonly", "readonly");
    input.setAttribute("inputmode", "none");
    input.classList.add("tp-hidden-input");
    input.setAttribute("aria-hidden", "true");

    createTimeSelectsBelow(input, 24);

    if (id === "pickupTimePoliticas") {
      const pickupDate = document.getElementById("pickupDatePoliticas");
      pickupDate?.addEventListener("change", () => rebuildHourOptions(input, 24));
    }
  }

  /* =================================================================
     ESPERA HASTA QUE JQUERY+SELECT2 ESTÉN DISPONIBLES
     ================================================================= */
  function waitForJQuerySelect2(callback, maxTries = 50, interval = 100) {
    let tries = 0;
    const check = () => {
      if (typeof window.jQuery !== 'undefined' && typeof window.jQuery.fn?.select2 !== 'undefined') {
        callback();
      } else if (++tries < maxTries) {
        setTimeout(check, interval);
      }
    };
    check();
  }

  /* =================================================================
     SELECT2 CON ICONOS POR SUCURSAL
     ================================================================= */
  function setupSelect2Iconos() {
    const $ = window.jQuery;

    function formatOption(option) {
      if (!option.id) {
        return $('<span><i class="fa-solid fa-location-dot" style="margin-right:8px;color:#333"></i> ' + option.text + '</span>');
      }
      const iconClass = (window.iconosPorId && window.iconosPorId[option.id]) || 'fa-building';
      return $('<span><i class="fa-solid ' + iconClass + '" style="margin-right:8px;color:#333"></i> ' + option.text + '</span>');
    }

    const modal           = document.getElementById('miBuscadorPoliticas');
    const dropoffWrapper  = document.getElementById('dropoffWrapperPoliticas');
    const dropoffSelect   = document.getElementById('dropoffPlacePoliticas');

    const originalDisplay  = dropoffWrapper ? dropoffWrapper.style.display : null;
    const originalDisabled = dropoffSelect ? dropoffSelect.disabled : null;

    if (dropoffWrapper && dropoffSelect) {
      dropoffWrapper.style.display = 'block';
      dropoffSelect.disabled = false;
    }

    const select2Config = {
      templateResult: formatOption,
      templateSelection: formatOption,
      escapeMarkup: m => m,
      width: '100%',
      minimumResultsForSearch: Infinity,
      allowClear: false,
      dropdownParent: modal ? $(modal) : $('body')
    };

    ['#pickupPlacePoliticas', '#dropoffPlacePoliticas'].forEach(selector => {
      if ($(selector).data('select2')) $(selector).select2('destroy');
    });

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
          $('#dropoffPlacePoliticas').prop('disabled', true).trigger('change.select2');
        }
      }
    }, 150);
  }

  /* =================================================================
     CHECKBOX: control de dropoff
     ================================================================= */
  function setupCheckbox() {
    const $ = window.jQuery;
    const chk     = document.getElementById('differentDropoffPoliticas');
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
        if ($?.fn?.select2) {
          $('#dropoffPlacePoliticas').prop('disabled', !isChecked).trigger('change');
        }
        if (!isChecked && pickSel?.value) {
          dropSel.value = pickSel.value;
          if ($?.fn?.select2) {
            $('#dropoffPlacePoliticas').val(pickSel.value).trigger('change');
          }
        }
      }
    }

    updateDropoffState();
    chk.addEventListener('change', updateDropoffState);

    if (pickSel && dropSel) {
      pickSel.addEventListener('change', function () {
        if (!chk.checked) {
          dropSel.value = this.value;
          if ($?.fn?.select2) {
            $('#dropoffPlacePoliticas').val(this.value).trigger('change');
          }
        }
      });
    }
  }

  /* =================================================================
     VALIDACIÓN DEL FORMULARIO
     ================================================================= */
  function setupValidation() {
    const $ = window.jQuery;
    const form = document.getElementById('rentalFormPoliticas');
    if (!form) return;

    form.addEventListener('submit', function (e) {
      e.preventDefault();
      let valid = true;

      form.querySelectorAll('.error-msg').forEach(el => el.remove());
      form.querySelectorAll('.field-error, .field-success').forEach(el => {
        el.classList.remove('field-error', 'field-success');
      });
      if ($?.fn?.select2) {
        form.querySelectorAll('.select2-selection').forEach(el => {
          el.classList.remove('field-error', 'field-success');
        });
      }

      const checkbox = document.getElementById('differentDropoffPoliticas');
      const selects = [{ id: 'pickupPlacePoliticas', msgKey: 'location' }];
      if (checkbox?.checked) {
        selects.push({ id: 'dropoffPlacePoliticas', msgKey: 'location' });
      }

      selects.forEach(campo => {
        const select = document.getElementById(campo.id);
        if (!select) return;
        const container = select.closest('.field');

        if (!select.value) {
          valid = false;
          select.classList.add('field-error');
          if ($?.fn?.select2) {
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
          if ($?.fn?.select2) {
            $(select).next('.select2-container').find('.select2-selection').addClass('field-success');
          }
        }
      });

      [
        { id: 'pickupDatePoliticas', msgKey: 'date' },
        { id: 'dropoffDatePoliticas', msgKey: 'date' }
      ].forEach(campo => {
        const hiddenInput = document.getElementById(campo.id);
        if (!hiddenInput) return;
        const picker = hiddenInput._flatpickr;
        const altInput = picker?.altInput;
        const container = hiddenInput.closest('.dt-field');
        const hasValue = hiddenInput.value?.trim() !== '';

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
        } else if (altInput) {
          altInput.classList.add('field-success');
          altInput.classList.remove('field-error');
        }
      });

      [
        { id: 'pickupTimePoliticas', msgKey: 'time' },
        { id: 'dropoffTimePoliticas', msgKey: 'time' }
      ].forEach(campo => {
        const hiddenInput = document.getElementById(campo.id);
        if (!hiddenInput) return;
        const timeField = hiddenInput.closest('.time-field');
        if (!timeField) return;
        const hourSelect = timeField.querySelector('.tp-selects .tp-hour');

        const userInteracted = hourSelect?.hasAttribute('data-user-interacted');
        const hasRealValue   = hiddenInput.value && hiddenInput.value !== "";
        const isValid        = userInteracted && hasRealValue;

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

      if (valid) {
        const pickup  = document.getElementById('pickupPlacePoliticas');
        const dropoff = document.getElementById('dropoffPlacePoliticas');
        if (checkbox && !checkbox.checked && pickup && dropoff) {
          dropoff.value = pickup.value;
        }
        form.submit();
      }
    });
  }

  /* =================================================================
     FLATPICKR
     ================================================================= */
  function setupFlatpickr() {
    if (typeof flatpickr === 'undefined') {
      console.error('Flatpickr no está cargado');
      return;
    }

    const pickupInput  = document.getElementById('pickupDatePoliticas');
    const dropoffInput = document.getElementById('dropoffDatePoliticas');
    const overlay      = document.getElementById('fp-overlay');

    function initPicker(input) {
  if (!input) return;

  return flatpickr(input, {
    dateFormat: "Y-m-d",
    altInput: true,
    altFormat: "d-M-y",
    minDate: "today",
    allowInput: false,
    disableMobile: true,
    clickOpens: true,
    locale: getFlatpickrLocale(),

    onReady(selectedDates, dateStr, instance) {
      if (instance.altInput) {
        instance.altInput.setAttribute('readonly', 'readonly');
        instance.altInput.setAttribute('inputmode', 'none');
        instance.altInput.style.cursor = 'pointer';
        instance.altInput.addEventListener('focus', (e) => {
          e.preventDefault();
          instance.altInput.blur();
        });
        instance.altInput.addEventListener('touchstart', (e) => {
          e.preventDefault();
          instance.open();
        });
        instance.altInput.addEventListener('mousedown', (e) => {
          e.preventDefault();
          instance.open();
        });
      }
    },

    onOpen() {
      overlay?.classList.add('active');
      if (this.altInput) this.altInput.blur();
    },

    onClose() {
      overlay?.classList.remove('active');
    },

    onChange(selectedDates) {
      if (input.id === 'pickupDatePoliticas' && selectedDates[0]) {
        const dropoffPicker = document.getElementById('dropoffDatePoliticas')?._flatpickr;
        if (dropoffPicker) {
          const minDropoffDate = new Date(selectedDates[0]);
          minDropoffDate.setDate(minDropoffDate.getDate() + 1);
          dropoffPicker.set('minDate', minDropoffDate);
        }
      }
    }
  });
}

    if (pickupInput)  initPicker(pickupInput);
    if (dropoffInput) initPicker(dropoffInput);
  }

  /* =================================================================
     CONTROL DEL BUSCADOR EN MÓVIL/TABLET
     ================================================================= */
  function initBuscadorPoliticas() {
    const btnAbrir  = document.getElementById('btn-abrir-buscador-politicas');
    const btnCerrar = document.getElementById('btn-cerrar-buscador-politicas');
    const buscador  = document.getElementById('miBuscadorPoliticas');

    if (!btnAbrir || !btnCerrar || !buscador) return;

    let savedScrollY = 0;

    function bloquearScroll() {
      savedScrollY = window.scrollY;
      document.body.classList.add('buscador-scroll-lock');
      document.body.style.top = `-${savedScrollY}px`;
    }

    function restaurarScroll() {
      document.body.classList.remove('buscador-scroll-lock');
      document.body.style.top = '';
      window.scrollTo(0, savedScrollY);
    }

    function abrir() {
      buscador.classList.add('active');
      bloquearScroll();
    }

    function cerrar() {
      buscador.classList.remove('active');
      restaurarScroll();
    }

    btnAbrir.addEventListener('click', (e) => { e.preventDefault(); abrir(); });
    btnCerrar.addEventListener('click', (e) => { e.preventDefault(); cerrar(); });

    window.addEventListener('keydown', (e) => {
      if (e.key === 'Escape' && buscador.classList.contains('active')) cerrar();
    });

    document.body.addEventListener('touchmove', function (e) {
      if (!buscador.classList.contains('active')) return;
      const isScrollable = e.target.closest('.select2-results__options, .select2-dropdown, select, .vj-modal__body');
      if (!isScrollable) e.preventDefault();
    }, { passive: false });
  }

  /* =================================================================
     OBSERVER DE IDIOMA
     ================================================================= */
  function setupLocaleObserver() {
    const observer = new MutationObserver(() => {
      updateFlatpickrLocale();
      const hourPlaceholder = getCurrentLocale() === 'en' ? 'Time' : 'Hora';
      document.querySelectorAll('.tp-hour').forEach(select => {
        if (select.options[0] && select.options[0].textContent !== hourPlaceholder) {
          select.options[0].textContent = hourPlaceholder;
        }
      });
    });
    observer.observe(document.documentElement, { attributes: true, attributeFilter: ['lang'] });
  }

  /* =================================================================
     INICIALIZACIÓN PRINCIPAL
     ================================================================= */
  onReady(() => {
    setupNavbar();
    setupFooterYear();
    setupPolicyModal();

    initAnalogTime("pickupTimePoliticas");
    initAnalogTime("dropoffTimePoliticas");

    setupFlatpickr();
    initBuscadorPoliticas();
    setupLocaleObserver();

    waitForJQuerySelect2(() => {
      setupSelect2Iconos();
      setupCheckbox();
      setupValidation();
    });
  });
})();
