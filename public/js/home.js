/* ============================================================
   HOME.JS - Versión optimizada
   - Zombies eliminados: polyfill IE11, syncAccountIcon, welcomeModal,
     año footer, initCarouselLazyLoad
   - 11 DOMContentLoaded consolidados en 1 (vanilla) + 1 (jQuery)
   - TODA la lógica funcional se preserva idéntica
============================================================ */

(function () {
  "use strict";

  /* ========================================================
     HELPERS DE LOCALE
  ======================================================== */
  function getCurrentLocale() {
    return (document.documentElement.lang || 'es') === 'en' ? 'en' : 'es';
  }

  function getFlatpickrLocale() {
    if (getCurrentLocale() === 'en') {
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

  function getErrorMessage(fieldType) {
    const locale = getCurrentLocale();
    const messages = {
      location: { es: 'Ubicación requerida', en: 'Location required' },
      date: { es: 'Fecha requerida', en: 'Date required' },
      time: { es: 'Hora requerida', en: 'Time required' }
    };
    return messages[fieldType]?.[locale] ?? 'Campo requerido';
  }

  /* ========================================================
     MÓDULO: FLEET (flechas con topes)
  ======================================================== */
  function initFleetControlled(fleet) {
    const track = fleet.querySelector('.fleet-track');
    const prev = fleet.querySelector('.fleet-btn.prev');
    const next = fleet.querySelector('.fleet-btn.next');
    if (!track || !prev || !next) return;
    if (track.dataset.fleetReady === "1") return;
    track.dataset.fleetReady = "1";

    const GAP_FALLBACK = 18;
    let lock = false;

    const getGapPx = () => parseFloat(getComputedStyle(track).columnGap) || GAP_FALLBACK;
    const getMaxScroll = () => Math.max(0, track.scrollWidth - track.clientWidth);
    const getStepPx = () => {
      const card = track.querySelector('.car-card');
      if (!card) return 340;
      const rect = card.getBoundingClientRect();
      const cs = getComputedStyle(card);
      return rect.width + (parseFloat(cs.marginLeft) || 0) + (parseFloat(cs.marginRight) || 0) + getGapPx();
    };

    function updateBtns() {
      const max = getMaxScroll();
      const current = track.scrollLeft;
      const atStart = current <= 10;
      const atEnd = current >= max - 10;
      prev.disabled = atStart;
      prev.classList.toggle('is-disabled', atStart);
      next.disabled = atEnd;
      next.classList.toggle('is-disabled', atEnd);
    }

    function pulseLimit(btn) {
      btn.classList.add('animating');
      setTimeout(() => btn.classList.remove('animating'), 300);
    }

    function moveBy(dir) {
      if (lock) return;
      const maxScroll = getMaxScroll();
      const from = track.scrollLeft;
      const step = getStepPx();
      if (dir > 0 && from >= maxScroll - 10) { pulseLimit(next); return; }
      if (dir < 0 && from <= 10) { pulseLimit(prev); return; }
      const to = Math.max(0, Math.min(from + dir * step, maxScroll));
      lock = true;
      track.scrollTo({ left: to, behavior: 'smooth' });
      setTimeout(() => { lock = false; updateBtns(); }, 420);
    }

    next.addEventListener('click', e => { e.preventDefault(); moveBy(1); });
    prev.addEventListener('click', e => { e.preventDefault(); moveBy(-1); });
    track.addEventListener('scroll', () => { if (!lock) updateBtns(); }, { passive: true });

    function forceStart() { track.scrollLeft = 0; updateBtns(); }
    requestAnimationFrame(() => requestAnimationFrame(forceStart));
    window.addEventListener('load', forceStart, { once: true });
    setTimeout(forceStart, 100);
  }

  /* ========================================================
   MÓDULO: FLATPICKR + SELECTS DE HORA + RESUMEN
   con selección automática de 13:00 al primer clic
============================================================ */
const TimeModule = (function () {
  function injectTimeCss() {
    if (document.getElementById("tpHideInputStyle")) return;
    const st = document.createElement("style");
    st.id = "tpHideInputStyle";
    st.textContent = `
      .tp-hidden-input{ display:none !important; }
      .tp-selects{ display:flex; gap:10px; margin-top:10px; }
      .tp-selects select{ width:100%; height:48px; border-radius:12px; border:1px solid rgba(0,0,0,.12); padding:10px 12px; outline:none; }
    `;
    document.head.appendChild(st);
  }

  const pad2 = n => String(n).padStart(2, "0");

  function isSameLocalDate(dateStr, dateObj) {
    if (!dateStr || !dateObj) return false;
    return dateStr === `${dateObj.getFullYear()}-${pad2(dateObj.getMonth() + 1)}-${pad2(dateObj.getDate())}`;
  }

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

  function rebuildHourOptions(input, opts = {}) {
    const { hourMax = 24 } = opts;
    const wrap = input.closest(".time-field") || input.parentElement;
    const selH = wrap?.querySelector(".tp-selects .tp-hour");
    if (!selH) return;

    const previousValue = selH.value;
    const placeholder = getCurrentLocale() === 'en' ? 'Time' : 'Hora';

    let startHour = 0;
    if (input.id === "pickupTime") {
      const pickupVal = document.getElementById("pickupDate")?.value || "";
      if (isSameLocalDate(pickupVal, new Date())) {
        startHour = new Date().getHours() + 1;
      }
    }

    selH.innerHTML = `<option value="" disabled selected>${placeholder}</option>`;
    for (let h = startHour; h < hourMax; h++) {
      const op = document.createElement("option");
      op.value = pad2(h);
      op.textContent = `${pad2(h)}:00`;
      selH.appendChild(op);
    }

    if (previousValue && previousValue !== "" && Array.from(selH.options).some(o => o.value === previousValue)) {
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

  function createTimeSelectsBelow(input, opts) {
    const { hourMax = 24 } = opts || {};
    const wrap = input.closest(".time-field") || input.parentElement;
    if (wrap?.querySelector(".tp-selects")) return;

    const box = document.createElement("div");
    box.className = "tp-selects w-100";
    const selH = document.createElement("select");
    selH.className = "tp-hour custom-select-clean";
    selH.setAttribute("aria-label", getCurrentLocale() === 'en' ? 'Time' : 'Hora');
    box.appendChild(selH);
    if (wrap) wrap.appendChild(box); else input.insertAdjacentElement("afterend", box);

    rebuildHourOptions(input, { hourMax });

    setupDefaultTimeOnClick(selH, input);

    function sync() {
      if (!selH.value) {
        input.value = "";
        input.dispatchEvent(new Event("input", { bubbles: true }));
        return;
      }
      input.value = `${pad2(Number(selH.value))}:00`;
      input.dispatchEvent(new Event("input", { bubbles: true }));
    }
    selH.addEventListener("change", sync);

    if (input.value && input.value !== "") {
      const h = input.value.split(':')[0];
      if (Array.from(selH.options).some(o => o.value === h)) {
        selH.value = h;
        sync();
        selH.dataset.defaultApplied = "true";
      }
    }
  }

  function initAnalogTime(id) {
    const input = document.getElementById(id);
    if (!input || input.dataset.tpReady === "1") return;
    input.dataset.tpReady = "1";
    input.setAttribute("readonly", "readonly");
    input.setAttribute("inputmode", "none");
    input.classList.add("tp-hidden-input");
    input.setAttribute("aria-hidden", "true");
    createTimeSelectsBelow(input, { hourMax: 24 });
    input.addEventListener("change", updateSummary);
    input.addEventListener("input", updateSummary);
    if (id === "pickupTime") {
      document.getElementById("pickupDate")?.addEventListener("change", () => {
        rebuildHourOptions(input, { hourMax: 24 });
      });
    }
  }

  function parseTimeTo24h(str) {
    const m = String(str || '').trim().match(/^(\d{1,2})/);
    if (!m) return { hh: 0, mm: 0 };
    return { hh: Math.min(23, Math.max(0, Number(m[1]))), mm: 0 };
  }

  function buildDT(dateId, timeId) {
    const d = document.getElementById(dateId)?.value;
    const t = document.getElementById(timeId)?.value || '';
    if (!d || !t) return null;
    const [y, m, day] = d.split('-').map(Number);
    if (!y || !m || !day) return null;
    const { hh, mm } = parseTimeTo24h(t);
    if (hh === 24) {
      const dt = new Date(y, m - 1, day, 0, 0);
      dt.setDate(dt.getDate() + 1);
      return dt;
    }
    return new Date(y, m - 1, day, hh, mm);
  }

  function updateSummary() {
    const rangeSummary = document.getElementById('rangeSummary');
    if (!rangeSummary) return;
    const s = buildDT('pickupDate', 'pickupTime');
    const e = buildDT('dropoffDate', 'dropoffTime');
    if (!s || !e) { rangeSummary.textContent = ''; return; }
    const h = Math.round((e - s) / 36e5);
    const d = Math.ceil(h / 24);
    if (!Number.isFinite(h) || h <= 0) { rangeSummary.textContent = ''; return; }
    const locale = getCurrentLocale();
    const daysText = locale === 'en' ? 'day(s)' : 'día(s)';
    const hoursText = locale === 'en' ? 'hour(s)' : 'hora(s)';
    rangeSummary.textContent = `Rental for ${d} ${daysText} · ~${h} ${hoursText}`;
  }

  function bindFormFixes() {
    const form = document.getElementById("rentalForm");
    if (!form || form.dataset.bindFixes === "1") return;
    form.dataset.bindFixes = "1";

    const chk = document.getElementById("differentDropoff");
    const dropSel = document.getElementById("dropoffPlace");
    const pickSel = document.getElementById("pickupPlace");
    const pickDate = document.getElementById("pickupDate");
    const dropDate = document.getElementById("dropoffDate");

    function syncHiddenFromSelects(hiddenId) {
      const hidden = document.getElementById(hiddenId);
      if (!hidden) return;
      const wrap = hidden.closest(".time-field") || hidden.parentElement;
      const selH = wrap?.querySelector(".tp-selects .tp-hour");
      hidden.value = selH?.value ? `${String(selH.value).padStart(2, "0")}:00` : "";
    }

    function normalizeDateInput(input) {
      if (!input) return;
      const v = String(input.value || "").trim();
      if (/^\d{4}-\d{2}-\d{2}$/.test(v)) return;
      const m = v.match(/^(\d{1,2})[\/\-](\d{1,2})[\/\-](\d{4})$/);
      if (m) input.value = `${m[3]}-${String(m[2]).padStart(2, "0")}-${String(m[1]).padStart(2, "0")}`;
    }

    form.addEventListener("submit", () => {
      syncHiddenFromSelects("pickupTime");
      syncHiddenFromSelects("dropoffTime");
      normalizeDateInput(pickDate);
      normalizeDateInput(dropDate);
      if (chk && !chk.checked && dropSel && pickSel?.value) dropSel.value = pickSel.value;
      updateSummary();
    }, { capture: true });

    [pickSel, dropSel].forEach(el => {
      if (!el) return;
      const toggle = () => {
        el.classList.toggle('has-value', !!el.value);
        if (typeof $ !== 'undefined') {
          $(el).next('.select2-container').find('.select2-selection').toggleClass('has-value', !!el.value);
        }
      };
      (typeof $ !== 'undefined') ? $(el).on('change', toggle) : el.addEventListener('change', toggle);
      setTimeout(toggle, 500);
    });
  }

  return {
    init: function () {
      injectTimeCss();
      initAnalogTime("pickupTime");
      initAnalogTime("dropoffTime");
      updateSummary();
      bindFormFixes();
    },
    updateSummary: updateSummary
  };
})();

  // Exponer updateSummary globalmente (lo usa el MutationObserver del calendar)
  window.updateSummary = TimeModule.updateSummary;

  /* ========================================================
     MÓDULO: BURBUJA RADIAL REDES
  ======================================================== */
  function initSocialFab() {
    const fab = document.getElementById("socialFab");
    const btn = document.getElementById("fabMain");
    if (!fab || !btn) return;
    const openFab = () => { fab.classList.add("open"); btn.setAttribute("aria-expanded", "true"); };
    const closeFab = () => { fab.classList.remove("open"); btn.setAttribute("aria-expanded", "false"); };
    btn.addEventListener("click", e => {
      e.preventDefault();
      fab.classList.contains("open") ? closeFab() : openFab();
    });
    document.addEventListener("click", e => {
      if (fab.classList.contains("open") && !fab.contains(e.target)) closeFab();
    });
    document.addEventListener("keydown", e => { if (e.key === "Escape") closeFab(); });
  }

  /* ========================================================
     MÓDULO: SWIPER TILES
  ======================================================== */
  function initTilesSwiper() {
    if (typeof window.Swiper !== "function") return;
    document.querySelectorAll('.vj-tiles-swiper').forEach(el => {
      if (el.swiper) { try { el.swiper.destroy(true, true); } catch (_) { } }
      if (el.dataset.swReady === "1") return;
      el.dataset.swReady = "1";
      new Swiper(el, {
        loop: false, autoplay: false, allowTouchMove: true, speed: 650,
        spaceBetween: 18, slidesPerView: 1.06, grabCursor: true,
        navigation: {
          nextEl: el.querySelector('.swiper-button-next'),
          prevEl: el.querySelector('.swiper-button-prev'),
        },
        pagination: { el: el.querySelector('.swiper-pagination'), clickable: true },
        breakpoints: {
          560: { slidesPerView: 1.4, spaceBetween: 18 },
          768: { slidesPerView: 2, spaceBetween: 20 },
          1024: { slidesPerView: 3, spaceBetween: 22 },
          1280: { slidesPerView: 3.3, spaceBetween: 24 }
        }
      });
    });
  }

  /* ========================================================
     MÓDULO: CHECKBOX DROPOFF + SYNC
  ======================================================== */
  function setDropoffState() {
    const chk = document.getElementById('differentDropoff');
    const dropWrap = document.getElementById('dropoffWrapper');
    const wrapper = document.getElementById('locationInputsWrapper');
    const pickSel = document.getElementById('pickupPlace');
    const dropSel = document.getElementById('dropoffPlace');
    if (!chk || !dropWrap || !wrapper) return;

    const isMobile = window.innerWidth <= 1124;
    const isChecked = chk.checked;

    if (isMobile) {
      if (isChecked) {
        dropWrap.style.display = 'flex';
        dropWrap.style.visibility = 'visible';
        dropWrap.style.opacity = '1';
        if (dropSel) dropSel.required = true;
      } else {
        dropWrap.style.display = 'none';
        if (dropSel) {
          dropSel.required = false;
          if (pickSel) dropSel.value = pickSel.value;
        }
      }
      dropWrap.classList.remove('show-dropoff', 'hidden-dropoff');
    } else {
      if (isChecked) {
        dropWrap.classList.add('show-dropoff');
        dropWrap.classList.remove('hidden-dropoff');
        wrapper.classList.remove('dropoff-hidden');
        if (dropSel) dropSel.required = true;
      } else {
        dropWrap.classList.add('hidden-dropoff');
        dropWrap.classList.remove('show-dropoff');
        wrapper.classList.add('dropoff-hidden');
        if (dropSel) {
          dropSel.required = false;
          if (pickSel) dropSel.value = pickSel.value;
        }
      }
      dropWrap.style.display = '';
      dropWrap.style.visibility = '';
      dropWrap.style.opacity = '';
    }
  }
  // Exponer (por si algún otro código la usa)
  window.setDropoffState = setDropoffState;

  function initDropoffSync() {
    const pickSel = document.getElementById('pickupPlace');
    const dropSel = document.getElementById('dropoffPlace');
    const chk = document.getElementById('differentDropoff');

    if (pickSel && dropSel && chk) {
      pickSel.addEventListener('change', function () {
        if (!chk.checked) dropSel.value = this.value;
      });
    }

    setDropoffState();
    chk?.addEventListener('change', setDropoffState);
    window.addEventListener('resize', setDropoffState);
  }

  /* ========================================================
     MÓDULO: VALIDACIONES DEL FORMULARIO
  ======================================================== */
  function initFormValidation() {
    const form = document.getElementById("rentalForm");
    if (!form) return;

    form.addEventListener("submit", function (e) {
      e.preventDefault();
      let valid = true;

      document.querySelectorAll('.error-msg').forEach(el => el.remove());
      document.querySelectorAll('.field-error,.field-success').forEach(el => {
        el.classList.remove('field-error', 'field-success');
      });

      // 1. Ubicaciones
      [
        { id: 'pickupPlace', msgKey: 'location' },
        { id: 'dropoffPlace', msgKey: 'location' }
      ].forEach(campo => {
        const select = document.getElementById(campo.id);
        if (!select) return;
        if (!select.value) {
          valid = false;
          select.classList.add('field-error');
          const container = select.closest('.field');
          if (container) {
            const msg = document.createElement('span');
            msg.className = 'error-msg';
            msg.textContent = getErrorMessage(campo.msgKey);
            container.appendChild(msg);
          }
        } else {
          select.classList.add('field-success');
        }
      });

      // 2. Fechas
      [
        { id: 'pickupDate', msgKey: 'date' },
        { id: 'dropoffDate', msgKey: 'date' }
      ].forEach(campo => {
        const hiddenInput = document.getElementById(campo.id);
        if (!hiddenInput) return;
        const container = hiddenInput.closest('.dt-field');
        if (!container) return;
        container.querySelectorAll('.error-msg').forEach(el => el.remove());
        const allInputs = container.querySelectorAll('input');
        if (!hiddenInput.value?.trim()) {
          valid = false;
          allInputs.forEach(inp => inp.classList.add('field-error'));
          const msg = document.createElement('span');
          msg.className = 'error-msg';
          msg.textContent = getErrorMessage(campo.msgKey);
          container.appendChild(msg);
        } else {
          allInputs.forEach(inp => {
            inp.classList.remove('field-error');
            inp.classList.add('field-success');
          });
        }
      });

      // 3. Horas
      [
        { id: 'pickupTime', msgKey: 'time' },
        { id: 'dropoffTime', msgKey: 'time' }
      ].forEach(campo => {
        const hiddenInput = document.getElementById(campo.id);
        if (!hiddenInput) return;
        const timeField = hiddenInput.closest('.time-field');
        if (!timeField) return;
        const hourSelect = timeField.querySelector('.tp-selects .tp-hour');
        const hasValue = hourSelect?.value && hourSelect.value !== '';
        if (!hasValue) {
          valid = false;
          hourSelect?.classList.add('field-error');
          hiddenInput.classList.add('field-error');
          const msg = document.createElement('span');
          msg.className = 'error-msg';
          msg.textContent = getErrorMessage(campo.msgKey);
          timeField.appendChild(msg);
        } else {
          hourSelect?.classList.add('field-success');
          hiddenInput.classList.add('field-success');
        }
      });

      if (valid) form.submit();
    });
  }

  /* ========================================================
     MÓDULO: LIMPIAR ERRORES AL INTERACTUAR
  ======================================================== */
  function initErrorClearer() {
    function clearError(el, containerSelector) {
      el.classList.remove('field-error');
      const container = el.closest(containerSelector);
      container?.querySelector('.error-msg')?.remove();
    }

    document.querySelectorAll('input, select').forEach(input => {
      ['input', 'change'].forEach(evt => {
        input.addEventListener(evt, function () {
          if (this.classList.contains('field-error')) {
            clearError(this, '.field, .dt-field, .time-field');
          }
        });
      });
    });

    document.querySelectorAll('.tp-selects .tp-hour').forEach(select => {
      select.addEventListener('change', function () {
        if (this.classList.contains('field-error')) clearError(this, '.time-field');
      });
    });

    document.querySelectorAll('.flatpickr-input').forEach(input => {
      if (input.type === 'hidden') return;
      ['change', 'click'].forEach(evt => {
        input.addEventListener(evt, function () {
          if (this.classList.contains('field-error')) clearError(this, '.dt-field');
        });
      });
    });
  }

  /* ========================================================
     MÓDULO: FLATPICKR CALENDARIOS (idioma dinámico)
  ======================================================== */
  function injectOverlay() {
    if (document.getElementById("fp-view-overlay-3")) return;
    const overlay = document.createElement("div");
    overlay.id = "fp-view-overlay-3";
    overlay.className = "fp-view-overlay-3";
    document.body.appendChild(overlay);
  }


function initFlatpickrCalendars() {
  if (typeof flatpickr === 'undefined') return;
  injectOverlay();

  const pickupEl = document.getElementById('pickupDate');
  const dropoffEl = document.getElementById('dropoffDate');
  const overlay = document.getElementById('fp-view-overlay-3');
  if (!pickupEl || !dropoffEl) return;

  let pickupPicker = null;
  let dropoffPicker = null;

  function createProtectedPicker(inputElement, additionalConfig = {}) {
    if (!inputElement) return null;
    let picker;

    try {
      picker = flatpickr(inputElement, {
        dateFormat: "Y-m-d",
        altInput: true,
        altFormat: "d-M-y",
        locale: getFlatpickrLocale(),
        allowInput: false,
        disableMobile: true,
        onOpen: () => {
          overlay?.classList.add('active');
          if (picker && picker.altInput) picker.altInput.blur();
        },
        onClose: () => overlay?.classList.remove('active'),
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
        ...additionalConfig
      });
    } catch (e) {
      console.warn("Flatpickr error:", e);
    }
    return picker;
  }

  pickupPicker = createProtectedPicker(pickupEl, {
    minDate: "today",
    onChange(selectedDates, dateStr) {
      pickupEl.value = dateStr;
      pickupEl.dispatchEvent(new Event('change', { bubbles: true }));
      if (typeof window.updateSummary === 'function') window.updateSummary();
      if (dropoffPicker && selectedDates[0]) {
        const minDropoffDate = new Date(selectedDates[0]);
        minDropoffDate.setDate(minDropoffDate.getDate() + 1);
        dropoffPicker.set('minDate', minDropoffDate);
      }
    }
  });

  let initialMinDate = "today";
  if (pickupEl.value) {
    const d = new Date(pickupEl.value);
    if (!isNaN(d.getTime())) {
      d.setDate(d.getDate() + 1);
      initialMinDate = d;
    }
  }

  dropoffPicker = createProtectedPicker(dropoffEl, {
    minDate: initialMinDate,
    onChange(selectedDates, dateStr) {
      dropoffEl.value = dateStr;
      dropoffEl.dispatchEvent(new Event('change', { bubbles: true }));
      if (typeof window.updateSummary === 'function') window.updateSummary();
    }
  });

  const localeObserver = new MutationObserver(() => {
    const newLocale = getFlatpickrLocale();
    if (pickupPicker) pickupPicker.set('locale', newLocale);
    if (dropoffPicker) dropoffPicker.set('locale', newLocale);
    const hourPlaceholder = getCurrentLocale() === 'en' ? 'Time' : 'Hora';
    document.querySelectorAll('.tp-hour').forEach(sel => {
      if (sel.options[0] && sel.options[0].textContent !== hourPlaceholder) {
        sel.options[0].textContent = hourPlaceholder;
      }
    });
    if (typeof window.updateSummary === 'function') window.updateSummary();
  });
  localeObserver.observe(document.documentElement, { attributes: true, attributeFilter: ['lang'] });

  setTimeout(() => {
    document.querySelectorAll('.flatpickr-input').forEach(input => {
      if (!input.value) input.placeholder = 'dd-Mmm-yy';
    });
  }, 100);
}

  /* ========================================================
     MÓDULO: CONTROL SCROLL FORMULARIO MÓVIL/TABLET
  ======================================================== */
  function initScrollControl() {
    const btnAbrir = document.getElementById('btn-abrir-buscador');
    const btnCerrar = document.getElementById('btn-cerrar-buscador');
    const buscador = document.getElementById('miBuscador');
    if (!btnAbrir || !btnCerrar || !buscador) return;

    function bloquearScroll() {
      const scrollY = window.scrollY;
      Object.assign(document.body.style, {
        position: 'fixed', top: `-${scrollY}px`, left: '0', right: '0',
        overflow: 'hidden', width: '100%'
      });
      document.body.dataset.scrollY = scrollY;
    }

    function restaurarScroll() {
      const scrollY = document.body.dataset.scrollY || 0;
      Object.assign(document.body.style, {
        position: '', top: '', left: '', right: '', overflow: '', width: ''
      });
      window.scrollTo(0, parseInt(scrollY, 10));
      delete document.body.dataset.scrollY;
    }

    btnAbrir.addEventListener('click', e => {
      e.preventDefault();
      buscador.classList.add('active');
      bloquearScroll();
    });
    btnCerrar.addEventListener('click', e => {
      e.preventDefault();
      buscador.classList.remove('active');
      restaurarScroll();
    });

    window.addEventListener('keydown', e => {
      if (buscador.classList.contains('active') && ['ArrowDown', 'ArrowUp', ' ', 'Spacebar'].includes(e.key)) {
        e.preventDefault();
      }
    }, { passive: false });

    const dropoffPlace = document.getElementById('dropoffPlace');
    if (dropoffPlace) {
      let touchStartY = 0, isDragging = false;
      dropoffPlace.addEventListener('touchstart', e => {
        touchStartY = e.touches[0].clientY;
        isDragging = false;
      }, { passive: true });
      dropoffPlace.addEventListener('touchmove', e => {
        if (Math.abs(e.touches[0].clientY - touchStartY) > 8) isDragging = true;
      }, { passive: true });
      dropoffPlace.addEventListener('touchend', () => {
        setTimeout(() => { isDragging = false; }, 50);
      }, { passive: true });
    }
  }

  function initMobileScrollOnDropoff() {
    const modal = document.getElementById('miBuscador');
    const dropoffWrapper = document.getElementById('dropoffWrapper');
    if (!modal || !dropoffWrapper) return;

    let startY = 0, startScrollTop = 0, dragging = false;
    const isMobileFormOpen = () => window.innerWidth <= 1124 && modal.classList.contains('active');

    dropoffWrapper.addEventListener('touchstart', e => {
      if (!isMobileFormOpen()) return;
      startY = e.touches[0].clientY;
      startScrollTop = modal.scrollTop;
      dragging = false;
    }, { passive: true });

    dropoffWrapper.addEventListener('touchmove', e => {
      if (!isMobileFormOpen()) return;
      const diffY = startY - e.touches[0].clientY;
      if (Math.abs(diffY) > 6) {
        dragging = true;
        e.preventDefault();
        modal.scrollTop = startScrollTop + diffY;
      }
    }, { passive: false });

    dropoffWrapper.addEventListener('touchend', () => {
      setTimeout(() => { dragging = false; }, 50);
    }, { passive: true });

    document.getElementById('dropoffPlace')?.addEventListener('click', e => {
      if (dragging) { e.preventDefault(); e.stopPropagation(); }
    });
  }

  /* ========================================================
     ENTRY POINT - 1 solo DOMContentLoaded
  ======================================================== */
  function initAll() {
    // 1. Carruseles de coches
    document.querySelectorAll('.fleet').forEach(initFleetControlled);

    // 2. Inputs de hora + summary + fixes del form
    TimeModule.init();

    // 3. Burbuja redes
    initSocialFab();

    // 4. Swiper tiles
    initTilesSwiper();

    // 5. Checkbox dropoff y sync con pickup
    initDropoffSync();

    // 6. Validaciones del form
    initFormValidation();

    // 7. Limpiar errores al interactuar
    initErrorClearer();

    // 8. Flatpickr calendarios (con idioma dinámico)
    initFlatpickrCalendars();

    // 9. Control scroll del form móvil
    initScrollControl();

    // 10. Scroll manual dropoffWrapper en móvil
    initMobileScrollOnDropoff();
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initAll);
  } else {
    initAll();
  }
})();

/* ============================================================
   SELECT2 con íconos (requiere jQuery, va separado)
============================================================ */
$(document).ready(function () {
  function formatOption(option) {
    if (!option.id) {
      return $('<span class="icon-item"><i class="fa-solid fa-location-dot"></i> ' + option.text + '</span>');
    }
    const iconClass = window.iconosPorId ? (window.iconosPorId[option.id] || 'fa-building') : 'fa-building';
    return $('<span class="icon-item"><i class="fa-solid ' + iconClass + '"></i> ' + option.text + '</span>');
  }
  $('#pickupPlace, #dropoffPlace').select2({
    templateResult: formatOption,
    templateSelection: formatOption,
    escapeMarkup: markup => markup,
    width: '100%',
    minimumResultsForSearch: Infinity
  });
});
