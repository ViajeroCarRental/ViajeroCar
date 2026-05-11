/* ====================
   Polyfills
==================== */
(function () {
  "use strict";
  if (!Element.prototype.closest) {
    Element.prototype.closest = function (s) {
      let el = this;
      do {
        if (el.matches(s)) return el;
        el = el.parentElement || el.parentNode;
      } while (el !== null && el.nodeType === 1);
      return null;
    };
  }
})();

// ============================================================
// LOCALE
// ============================================================
function getCurrentLocale() {
  return (document.documentElement.lang || 'es') === 'en' ? 'en' : 'es';
}

function getFlatpickrLocale() {
  if (getCurrentLocale() === 'en') {
    return {
      firstDayOfWeek: 0,
      weekdays: {
        shorthand: ['Sun','Mon','Tue','Wed','Thu','Fri','Sat'],
        longhand:  ['Sunday','Monday','Tuesday','Wednesday','Thursday','Friday','Saturday']
      },
      months: {
        shorthand: ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'],
        longhand:  ['January','February','March','April','May','June','July','August','September','October','November','December']
      }
    };
  }
  return {
    firstDayOfWeek: 1,
    weekdays: {
      shorthand: ['Dom','Lun','Mar','Mié','Jue','Vie','Sáb'],
      longhand:  ['Domingo','Lunes','Martes','Miércoles','Jueves','Viernes','Sábado']
    },
    months: {
      shorthand: ['Ene','Feb','Mar','Abr','May','Jun','Jul','Ago','Sep','Oct','Nov','Dic'],
      longhand:  ['Enero','Febrero','Marzo','Abril','Mayo','Junio','Julio','Agosto','Septiembre','Octubre','Noviembre','Diciembre']
    }
  };
}

function getErrorMessage(fieldType) {
  const locale = getCurrentLocale();
  const messages = {
    location: { es: 'Ubicación requerida', en: 'Location required' },
    date:     { es: 'Fecha requerida',     en: 'Date required' },
    time:     { es: 'Hora requerida',      en: 'Time required' }
  };
  return messages[fieldType]?.[locale] ?? 'Campo requerido';
}

/* ====================
   Icono de cuenta
==================== */
(function () {
  "use strict";
  function syncAccountIcon() {
    const link = document.getElementById('accountLink');
    if (!link) return;
    const name    = link.getAttribute('data-auth-name')  || '';
    const email   = link.getAttribute('data-auth-email') || '';
    const initial = (name.trim()[0] || email.trim()[0] || '').toUpperCase();
    const locale  = getCurrentLocale();
    if (initial) {
      link.title     = locale === 'en' ? 'My profile' : 'Mi perfil';
      link.innerHTML = `<span class="avatar-mini">${initial}</span>`;
    } else {
      link.title     = locale === 'en' ? 'Sign in' : 'Iniciar sesión';
      link.innerHTML = '<i class="fa-regular fa-user"></i>';
    }
  }
  document.addEventListener('DOMContentLoaded', syncAccountIcon);
})();

/* =====================================================================
   FLEET: FLECHAS CON TOPES
===================================================================== */
(function () {
  "use strict";

  function initFleetControlled(fleet) {
    const track = fleet.querySelector('.fleet-track');
    const prev  = fleet.querySelector('.fleet-btn.prev');
    const next  = fleet.querySelector('.fleet-btn.next');
    if (!track || !prev || !next) return;
    if (track.dataset.fleetReady === "1") return;
    track.dataset.fleetReady = "1";

    const GAP_FALLBACK = 18;
    let lock = false;

    const getGapPx     = () => parseFloat(getComputedStyle(track).columnGap) || GAP_FALLBACK;
    const getMaxScroll = () => Math.max(0, track.scrollWidth - track.clientWidth);
    const getStepPx    = () => {
      const card = track.querySelector('.car-card');
      if (!card) return 340;
      const rect = card.getBoundingClientRect();
      const cs   = getComputedStyle(card);
      return rect.width + (parseFloat(cs.marginLeft) || 0) + (parseFloat(cs.marginRight) || 0) + getGapPx();
    };

    function updateBtns() {
      const max     = getMaxScroll();
      const current = track.scrollLeft;
      const atStart = current <= 10;
      const atEnd   = current >= max - 10;
      prev.disabled = atStart; prev.classList.toggle('is-disabled', atStart);
      next.disabled = atEnd;   next.classList.toggle('is-disabled', atEnd);
    }

    function pulseLimit(btn) {
      btn.classList.add('animating');
      setTimeout(() => btn.classList.remove('animating'), 300);
    }

    function moveBy(dir) {
      if (lock) return;
      const maxScroll = getMaxScroll();
      const from      = track.scrollLeft;
      const step      = getStepPx();
      if (dir > 0 && from >= maxScroll - 10) { pulseLimit(next); return; }
      if (dir < 0 && from <= 10)             { pulseLimit(prev); return; }
      const to = Math.max(0, Math.min(from + dir * step, maxScroll));
      lock = true;
      track.scrollTo({ left: to, behavior: 'smooth' });
      setTimeout(() => { lock = false; updateBtns(); }, 420);
    }

    next.addEventListener('click', e => { e.preventDefault(); moveBy(1);  });
    prev.addEventListener('click', e => { e.preventDefault(); moveBy(-1); });
    track.addEventListener('scroll', () => { if (!lock) updateBtns(); }, { passive: true });

    function forceStart() { track.scrollLeft = 0; updateBtns(); }
    requestAnimationFrame(() => requestAnimationFrame(forceStart));
    window.addEventListener('load', forceStart, { once: true });
    setTimeout(forceStart, 100);
  }

  document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('.fleet').forEach(initFleetControlled);
  });
})();

/* ====================
   Año en footer
==================== */
(function () {
  "use strict";
  const y = document.getElementById('year');
  if (y) y.textContent = new Date().getFullYear();
})();

/* ====================
   Modal bienvenida
==================== */
(function () {
  "use strict";
  const modal = document.getElementById('welcomeModal');
  if (!modal) return;
  const nameEl   = document.getElementById('wmName');
  const closeBtn = document.getElementById('wmClose');
  const okBtn    = document.getElementById('wmOk');
  const open  = () => modal.classList.add('show');
  const close = () => modal.classList.remove('show');
  closeBtn?.addEventListener('click', close);
  okBtn?.addEventListener('click', close);
  modal.querySelector('.modal-backdrop')?.addEventListener('click', close);
  if (modal.getAttribute('data-auto-show') === '1') {
    const who = modal.getAttribute('data-name');
    if (nameEl && who) nameEl.textContent = who;
    open();
  }
})();

/* =====================================================================
   FLATPICKR + SELECTS DE HORA + RESUMEN
===================================================================== */
(function () {
  "use strict";

  (function injectTimeCss() {
    if (document.getElementById("tpHideInputStyle")) return;
    const st = document.createElement("style");
    st.id = "tpHideInputStyle";
    st.textContent = `
      .tp-hidden-input{ display:none !important; }
      .tp-selects{ display:flex; gap:10px; margin-top:10px; }
      .tp-selects select{ width:100%; height:48px; border-radius:12px; border:1px solid rgba(0,0,0,.12); padding:10px 12px; outline:none; }
    `;
    document.head.appendChild(st);
  })();

  const pad2 = n => String(n).padStart(2, "0");

  function isSameLocalDate(dateStr, dateObj) {
    if (!dateStr || !dateObj) return false;
    return dateStr === `${dateObj.getFullYear()}-${pad2(dateObj.getMonth()+1)}-${pad2(dateObj.getDate())}`;
  }

  function rebuildHourOptions(input, opts = {}) {
    const { hourMax = 24 } = opts;
    const wrap = input.closest(".time-field") || input.parentElement;
    const selH = wrap?.querySelector(".tp-selects .tp-hour");
    if (!selH) return;

    const previousValue = selH.value;
    const placeholder   = getCurrentLocale() === 'en' ? 'Time' : 'Hora';

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
      op.value       = pad2(h);
      op.textContent = `${pad2(h)}:00`;
      selH.appendChild(op);
    }

    const stillExists = Array.from(selH.options).some(o => o.value === previousValue);
    if (stillExists && previousValue !== "") {
      selH.value  = previousValue;
      input.value = `${previousValue}:00`;
    } else {
      selH.selectedIndex = 0;
      input.value = "";
    }
    input.dispatchEvent(new Event("input",  { bubbles: true }));
    input.dispatchEvent(new Event("change", { bubbles: true }));
  }

  function createTimeSelectsBelow(input, opts) {
  const { hourMax = 24, defaultValue = "13:00" } = opts || {};
  const wrap = input.closest(".time-field") || input.parentElement;
  if (wrap?.querySelector(".tp-selects")) return;

  const box  = document.createElement("div");
  box.className = "tp-selects w-100";
  const selH = document.createElement("select");
  selH.className = "tp-hour custom-select-clean";
  selH.setAttribute("aria-label", getCurrentLocale() === 'en' ? 'Time' : 'Hora');
  box.appendChild(selH);
  if (wrap) wrap.appendChild(box); else input.insertAdjacentElement("afterend", box);

  rebuildHourOptions(input, { hourMax });

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

  selH.addEventListener("focus", function() {
    if (!this.value || this.value === "") {
      const option13 = Array.from(this.options).find(opt => opt.value === "13");
      if (option13) {
        this.value = "13";
        sync();
      } else if (this.options.length > 1) {
        this.selectedIndex = 1;
        sync();
      }
    }
  });

  if (input.value && input.value !== "") {
    const h = input.value.split(':')[0];
    if (Array.from(selH.options).some(o => o.value === h)) {
      selH.value = h;
      sync();
    }
  } else {
    selH.selectedIndex = 0;
    input.value = "";
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
    createTimeSelectsBelow(input, { hourMax: 24, defaultValue: input.value || "13:00" });
    input.addEventListener("change", updateSummary);
    input.addEventListener("input",  updateSummary);
    if (id === "pickupTime") {
      document.getElementById("pickupDate")?.addEventListener("change", () => {
        rebuildHourOptions(input, { hourMax: 24 });
      });
    }
  }

  document.addEventListener("DOMContentLoaded", () => {
    initAnalogTime("pickupTime");
    initAnalogTime("dropoffTime");
    updateSummary();
  });

  function parseTimeTo24h(str) {
    const m = String(str || '').trim().match(/^(\d{1,2})/);
    if (!m) return { hh: 0, mm: 0 };
    return { hh: Math.min(23, Math.max(0, Number(m[1]))), mm: 0 };
  }

  function buildDT(dateId, timeId) {
    const d = document.getElementById(dateId)?.value;
    const t = document.getElementById(timeId)?.value || '00:00';
    if (!d) return null;
    const [y, m, day] = d.split('-').map(Number);
    if (!y || !m || !day) return null;
    const { hh, mm } = parseTimeTo24h(t);
    if (hh === 24) {
      const dt = new Date(y, m-1, day, 0, 0);
      dt.setDate(dt.getDate() + 1);
      return dt;
    }
    return new Date(y, m-1, day, hh, mm);
  }

  function updateSummary() {
    const rangeSummary = document.getElementById('rangeSummary');
    if (!rangeSummary) return;
    const s = buildDT('pickupDate',  'pickupTime');
    const e = buildDT('dropoffDate', 'dropoffTime');
    if (!s || !e) { rangeSummary.textContent = ''; return; }
    const h = Math.round((e - s) / 36e5);
    const d = Math.ceil(h / 24);
    if (!Number.isFinite(h) || h <= 0) { rangeSummary.textContent = ''; return; }
    const locale    = getCurrentLocale();
    const daysText  = locale === 'en' ? 'day(s)'  : 'día(s)';
    const hoursText = locale === 'en' ? 'hour(s)' : 'hora(s)';
    rangeSummary.textContent = `Rental for ${d} ${daysText} · ~${h} ${hoursText}`;
  }

  window.updateSummary = updateSummary;

  (function bindFormFixes() {
    const form = document.getElementById("rentalForm");
    if (!form || form.dataset.bindFixes === "1") return;
    form.dataset.bindFixes = "1";

    const chk      = document.getElementById("differentDropoff");
    const dropSel  = document.getElementById("dropoffPlace");
    const pickSel  = document.getElementById("pickupPlace");
    const pickTime = document.getElementById("pickupTime");
    const dropTime = document.getElementById("dropoffTime");
    const pickDate = document.getElementById("pickupDate");
    const dropDate = document.getElementById("dropoffDate");

    function syncHiddenFromSelects(hiddenId) {
      const hidden = document.getElementById(hiddenId);
      if (!hidden) return;
      const wrap = hidden.closest(".time-field") || hidden.parentElement;
      const selH = wrap?.querySelector(".tp-selects .tp-hour");
      hidden.value = selH?.value
        ? `${String(selH.value).padStart(2,"0")}:00`
        : (hidden.value || "12:00");
    }

    function normalizeDateInput(input) {
      if (!input) return;
      const v = String(input.value || "").trim();
      if (/^\d{4}-\d{2}-\d{2}$/.test(v)) return;
      const m = v.match(/^(\d{1,2})[\/\-](\d{1,2})[\/\-](\d{4})$/);
      if (m) input.value = `${m[3]}-${String(m[2]).padStart(2,"0")}-${String(m[1]).padStart(2,"0")}`;
    }

    form.addEventListener("submit", () => {
      syncHiddenFromSelects("pickupTime");
      syncHiddenFromSelects("dropoffTime");
      normalizeDateInput(pickDate);
      normalizeDateInput(dropDate);
      if (chk && !chk.checked && dropSel && pickSel?.value) dropSel.value = pickSel.value;
      updateSummary();
      if (pickTime && !pickTime.value) pickTime.value = "12:00";
      if (dropTime && !dropTime.value) dropTime.value = "12:00";
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
  })();
})();

/* ====================
   Burbuja radial redes
==================== */
(function () {
  "use strict";
  const fab = document.getElementById("socialFab");
  const btn = document.getElementById("fabMain");
  if (!fab || !btn) return;
  const openFab  = () => { fab.classList.add("open");    btn.setAttribute("aria-expanded","true");  };
  const closeFab = () => { fab.classList.remove("open"); btn.setAttribute("aria-expanded","false"); };
  btn.addEventListener("click", e => { e.preventDefault(); fab.classList.contains("open") ? closeFab() : openFab(); });
  document.addEventListener("click",   e => { if (fab.classList.contains("open") && !fab.contains(e.target)) closeFab(); });
  document.addEventListener("keydown", e => { if (e.key === "Escape") closeFab(); });
})();

/* =====================================================================
   Swiper tiles
===================================================================== */
(function () {
  "use strict";
  function initTilesSwiper() {
    if (typeof window.Swiper !== "function") return;
    document.querySelectorAll('.vj-tiles-swiper').forEach(el => {
      if (el.swiper) { try { el.swiper.destroy(true, true); } catch (_) {} }
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
          560:  { slidesPerView: 1.4,  spaceBetween: 18 },
          768:  { slidesPerView: 2,    spaceBetween: 20 },
          1024: { slidesPerView: 3,    spaceBetween: 22 },
          1280: { slidesPerView: 3.3,  spaceBetween: 24 }
        }
      });
    });
  }
  document.addEventListener('DOMContentLoaded', initTilesSwiper);
})();

/* =====================================================================
   Toast reservas en vivo (rvBanner)
===================================================================== */
(function () {
  "use strict";

  const SEQ     = [5, 7, 10, 5, 12];
  const SHOW_MS = 7000;
  const HIDE_MS = 25000;

  const banner = document.getElementById('rvBanner');
  const bar    = document.getElementById('rvBar');
  const count  = document.getElementById('rvCount');
  const close  = document.getElementById('rvClose');

  if (!banner || !bar || !count) return;
  if (banner.dataset.rvReady === "1") return;
  banner.dataset.rvReady = "1";

  let idx = 0, hideT = null, nextT = null;
  let paused = false, startTs = 0, remaining = SHOW_MS;

  function setBar(ms) {
    bar.style.transition = 'none';
    bar.style.width = '0%';
    requestAnimationFrame(() => requestAnimationFrame(() => {
      bar.style.transition = `width ${ms}ms linear`;
      bar.style.width = '100%';
    }));
  }

  function hide() {
    banner.classList.remove('rv-in');
    banner.classList.add('rv-out');
    setTimeout(() => {
      banner.style.display = 'none';
      nextT = setTimeout(showOnce, HIDE_MS);
    }, 260);
  }

  function showOnce() {
    count.textContent = SEQ[idx];
    idx = (idx + 1) % SEQ.length;
    banner.style.display = 'block';
    banner.classList.remove('rv-out');
    banner.classList.add('rv-in');
    remaining = SHOW_MS;
    startTs   = performance.now();
    setBar(SHOW_MS);
    hideT = setTimeout(hide, SHOW_MS);
  }

  banner.addEventListener('mouseenter', () => {
    paused = true;
    const elapsed = performance.now() - startTs;
    remaining = Math.max(0, SHOW_MS - elapsed);
    if (hideT) { clearTimeout(hideT); hideT = null; }
    bar.style.transition = 'none';
  });

  banner.addEventListener('mouseleave', () => {
    if (!paused) return;
    paused = false;
    setTimeout(() => {
      setBar(remaining);
      hideT   = setTimeout(hide, remaining);
      startTs = performance.now() - (SHOW_MS - remaining);
    }, 30);
  });

  if (close) {
    close.addEventListener('click', () => {
      clearTimeout(hideT);
      clearTimeout(nextT);
      banner.style.display = 'none';
    });
  }
})();

/* ====================
   Control del checkbox dropoff
==================== */
function setDropoffState() {
  const chk      = document.getElementById('differentDropoff');
  const dropWrap = document.getElementById('dropoffWrapper');
  const wrapper  = document.getElementById('locationInputsWrapper');
  const pickSel  = document.getElementById('pickupPlace');
  const dropSel  = document.getElementById('dropoffPlace');
  if (!chk || !dropWrap || !wrapper) return;

  const isMobile  = window.innerWidth <= 1124;
  const isChecked = chk.checked;

  if (isMobile) {
    if (isChecked) {
      dropWrap.style.display    = 'flex';
      dropWrap.style.visibility = 'visible';
      dropWrap.style.opacity    = '1';
      if (dropSel) dropSel.required = true;
    } else {
      dropWrap.style.display = 'none';
      if (dropSel) { dropSel.required = false; if (pickSel) dropSel.value = pickSel.value; }
    }
    dropWrap.classList.remove('show-dropoff','hidden-dropoff');
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
      if (dropSel) { dropSel.required = false; if (pickSel) dropSel.value = pickSel.value; }
    }
    dropWrap.style.display    = '';
    dropWrap.style.visibility = '';
    dropWrap.style.opacity    = '';
  }
}

/* ====================
   Sincronizar pickup → dropoff
==================== */
document.addEventListener('DOMContentLoaded', function () {
  const pickSel = document.getElementById('pickupPlace');
  const dropSel = document.getElementById('dropoffPlace');
  const chk     = document.getElementById('differentDropoff');

  if (pickSel && dropSel && chk) {
    pickSel.addEventListener('change', function () {
      if (!chk.checked) dropSel.value = this.value;
    });
  }

  setDropoffState();
  chk?.addEventListener('change', setDropoffState);
  window.addEventListener('resize', setDropoffState);
});

/* ============================================================
   VALIDACIONES DEL FORMULARIO
============================================================ */
document.addEventListener('DOMContentLoaded', function () {
  const form = document.getElementById("rentalForm");
  if (!form) return;

  form.addEventListener("submit", function (e) {
    e.preventDefault();
    let valid = true;

    document.querySelectorAll('.error-msg').forEach(el => el.remove());
    document.querySelectorAll('.field-error,.field-success').forEach(el => {
      el.classList.remove('field-error','field-success');
    });

    // 1. Ubicaciones
    [
      { id: 'pickupPlace',  msgKey: 'location' },
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
          msg.className   = 'error-msg';
          msg.textContent = getErrorMessage(campo.msgKey);
          container.appendChild(msg);
        }
      } else {
        select.classList.add('field-success');
      }
    });

    // 2. Fechas
    [
      { id: 'pickupDate',  msgKey: 'date' },
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
        msg.className   = 'error-msg';
        msg.textContent = getErrorMessage(campo.msgKey);
        container.appendChild(msg);
      } else {
        allInputs.forEach(inp => { inp.classList.remove('field-error'); inp.classList.add('field-success'); });
      }
    });

    // 3. Horas
    [
      { id: 'pickupTime',  msgKey: 'time' },
      { id: 'dropoffTime', msgKey: 'time' }
    ].forEach(campo => {
      const hiddenInput = document.getElementById(campo.id);
      if (!hiddenInput) return;
      const timeField  = hiddenInput.closest('.time-field');
      if (!timeField) return;
      const hourSelect = timeField.querySelector('.tp-selects .tp-hour');
      const hasValue   = hourSelect?.value && hourSelect.value !== '';
      if (!hasValue) {
        valid = false;
        hourSelect?.classList.add('field-error');
        hiddenInput.classList.add('field-error');
        const msg = document.createElement('span');
        msg.className   = 'error-msg';
        msg.textContent = getErrorMessage(campo.msgKey);
        timeField.appendChild(msg);
      } else {
        hourSelect?.classList.add('field-success');
        hiddenInput.classList.add('field-success');
      }
    });
if (valid) form.submit();
  });
});

/* ============================================================
   LIMPIAR ERRORES AL INTERACTUAR
============================================================ */
document.addEventListener('DOMContentLoaded', function () {
  function clearError(el, containerSelector) {
    el.classList.remove('field-error');
    const container = el.closest(containerSelector);
    container?.querySelector('.error-msg')?.remove();
  }

  document.querySelectorAll('input, select').forEach(input => {
    ['input','change'].forEach(evt => {
      input.addEventListener(evt, function () {
        if (this.classList.contains('field-error')) clearError(this, '.field, .dt-field, .time-field');
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
    ['change','click'].forEach(evt => {
      input.addEventListener(evt, function () {
        if (this.classList.contains('field-error')) clearError(this, '.dt-field');
      });
    });
  });
});

/* ============================================================
   FLATPICKR - CALENDARIOS CON IDIOMA DINÁMICO
============================================================ */
(function () {
  "use strict";

  function injectOverlay() {
    if (document.getElementById("fp-view-overlay-3")) return;
    const overlay = document.createElement("div");
    overlay.id        = "fp-view-overlay-3";
    overlay.className = "fp-view-overlay-3";
    document.body.appendChild(overlay);
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initFlatpickr);
  } else {
    initFlatpickr();
  }

  function initFlatpickr() {
    if (typeof flatpickr === 'undefined') return;
    injectOverlay();

    const pickupEl  = document.getElementById('pickupDate');
    const dropoffEl = document.getElementById('dropoffDate');
    const overlay   = document.getElementById('fp-view-overlay-3');
    if (!pickupEl || !dropoffEl) return;

    let pickupPicker, dropoffPicker;

    function initPickers() {
      const localeData = getFlatpickrLocale();
      const commonConfig = {
        dateFormat: "Y-m-d",
        altInput:   true,
        altFormat:  "d-M-y",
        locale:     localeData,
        allowInput: false,
        disableMobile: true,
        onOpen:  () => overlay?.classList.add('active'),
        onClose: () => overlay?.classList.remove('active'),
      };

      try {
        pickupPicker?.destroy();
        pickupPicker = flatpickr(pickupEl, {
          ...commonConfig,
          minDate: "today",
          onChange(selectedDates, dateStr) {
            pickupEl.value = dateStr;
            pickupEl.dispatchEvent(new Event('change', { bubbles: true }));
            if (dropoffPicker && selectedDates[0]) {
              const minDate = new Date(selectedDates[0]);
              minDate.setDate(minDate.getDate() + 1);
              dropoffPicker.set('minDate', minDate);
            }
          }
        });
      } catch (e) { /* silent */ }

      try {
        let minDate = "today";
        if (pickupEl.value) {
          const d = new Date(pickupEl.value);
          if (!isNaN(d)) { d.setDate(d.getDate() + 1); minDate = d; }
        }
        dropoffPicker?.destroy();
        dropoffPicker = flatpickr(dropoffEl, {
          ...commonConfig,
          minDate,
          onChange(_, dateStr) {
            dropoffEl.value = dateStr;
            dropoffEl.dispatchEvent(new Event('change', { bubbles: true }));
          }
        });
      } catch (e) { /* silent */ }
    }

    initPickers();

    const observer = new MutationObserver(() => {
      initPickers();
      const placeholder = getCurrentLocale() === 'en' ? 'Hour' : 'Hora';
      document.querySelectorAll('.tp-hour').forEach(sel => {
        if (sel.options[0]) sel.options[0].textContent = placeholder;
      });
      if (typeof window.updateSummary === 'function') window.updateSummary();
    });
    observer.observe(document.documentElement, { attributes: true, attributeFilter: ['lang'] });

    setTimeout(() => {
      document.querySelectorAll('.flatpickr-input').forEach(input => {
        if (!input.value) input.placeholder = 'dd-Mmm-yy';
      });
    }, 100);
  }
})();

/* ============================================================
   CONTROL DE SCROLL PARA FORMULARIO MÓVIL/TABLET
============================================================ */
(function () {
  "use strict";

  function initScrollControl() {
    const btnAbrir  = document.getElementById('btn-abrir-buscador');
    const btnCerrar = document.getElementById('btn-cerrar-buscador');
    const buscador  = document.getElementById('miBuscador');
    if (!btnAbrir || !btnCerrar || !buscador) return;

    function bloquearScroll() {
      const scrollY = window.scrollY;
      Object.assign(document.body.style, { position:'fixed', top:`-${scrollY}px`, left:'0', right:'0', overflow:'hidden', width:'100%' });
      document.body.dataset.scrollY = scrollY;
    }

    function restaurarScroll() {
      const scrollY = document.body.dataset.scrollY || 0;
      Object.assign(document.body.style, { position:'', top:'', left:'', right:'', overflow:'', width:'' });
      window.scrollTo(0, parseInt(scrollY, 10));
      delete document.body.dataset.scrollY;
    }

    btnAbrir.addEventListener('click', e => { e.preventDefault(); buscador.classList.add('active'); bloquearScroll(); });
    btnCerrar.addEventListener('click', e => { e.preventDefault(); buscador.classList.remove('active'); restaurarScroll(); });

    window.addEventListener('keydown', e => {
      if (buscador.classList.contains('active') && ['ArrowDown','ArrowUp',' ','Spacebar'].includes(e.key)) {
        e.preventDefault();
      }
    }, { passive: false });

    const dropoffPlace = document.getElementById('dropoffPlace');
    if (dropoffPlace) {
      let touchStartY = 0, isDragging = false;
      dropoffPlace.addEventListener('touchstart', e => { touchStartY = e.touches[0].clientY; isDragging = false; }, { passive: true });
      dropoffPlace.addEventListener('touchmove',  e => { if (Math.abs(e.touches[0].clientY - touchStartY) > 8) isDragging = true; }, { passive: true });
      dropoffPlace.addEventListener('touchend',   () => { setTimeout(() => { isDragging = false; }, 50); }, { passive: true });
    }
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initScrollControl);
  } else {
    initScrollControl();
  }

  // Scroll manual sobre dropoffWrapper en móvil
  document.addEventListener('DOMContentLoaded', function () {
    const modal         = document.getElementById('miBuscador');
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
      if (Math.abs(diffY) > 6) { dragging = true; e.preventDefault(); modal.scrollTop = startScrollTop + diffY; }
    }, { passive: false });

    dropoffWrapper.addEventListener('touchend', () => { setTimeout(() => { dragging = false; }, 50); }, { passive: true });

    document.getElementById('dropoffPlace')?.addEventListener('click', e => {
      if (dragging) { e.preventDefault(); e.stopPropagation(); }
    });
  });
})();

/* ============================================================
   SELECT2
============================================================ */
$(document).ready(function () {
  function formatOption(option) {
    if (!option.id) return $('<span class="icon-item"><i class="fa-solid fa-location-dot"></i> ' + option.text + '</span>');
    const iconClass = window.iconosPorId ? (window.iconosPorId[option.id] || 'fa-building') : 'fa-building';
    return $('<span class="icon-item"><i class="fa-solid ' + iconClass + '"></i> ' + option.text + '</span>');
  }
  $('#pickupPlace, #dropoffPlace').select2({
    templateResult:    formatOption,
    templateSelection: formatOption,
    escapeMarkup:      markup => markup,
    width:             '100%',
    minimumResultsForSearch: Infinity
  });
});

/* ============================================================
   MODAL MEMBRESÍA
============================================================ */
document.addEventListener('DOMContentLoaded', function () {
  const modal        = document.getElementById('membershipModal');
  const openBtnCorner = document.getElementById('openMembershipModalBtn');
  const openBtnMain   = document.getElementById('openMembershipModalFromBtn');
  const closeBtn      = document.getElementById('closeMembershipModalBtn');
  if (!modal || !closeBtn) return;

  const openModal  = () => { modal.classList.add('show');    document.body.style.overflow = 'hidden'; };
  const closeModal = () => { modal.classList.remove('show'); document.body.style.overflow = ''; };

  openBtnCorner?.addEventListener('click', e => { e.preventDefault(); openModal(); });
  openBtnMain?.addEventListener('click',   e => { e.preventDefault(); openModal(); });
  closeBtn.addEventListener('click', closeModal);

  modal.addEventListener('click', e => {
    if (e.target === modal || e.target.classList.contains('modal-membership-backdrop')) closeModal();
  });
  document.addEventListener('keydown', e => {
    if (e.key === 'Escape' && modal.classList.contains('show')) closeModal();
  });
});

/* ============================================================
   CONVERSIÓN DE MONEDA
============================================================ */
(function () {
  "use strict";

  const EXCHANGE_RATE = 20;
  let conversionAttempts = 0;
  const MAX_ATTEMPTS     = 10;
  let _intervalId        = null;

  const getCurrencyFromHtml = () => document.documentElement.lang === 'en' ? 'USD' : 'MXN';

  function formatAmount(amount, currency) {
    return currency === 'USD'
      ? amount.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 })
      : amount.toLocaleString('es-MX', { minimumFractionDigits: 0, maximumFractionDigits: 0 });
  }

  function convertAllPrices(force = false) {
    const targetCurrency = getCurrencyFromHtml();
    const carCards       = document.querySelectorAll('.car-card');

    if (carCards.length === 0 && !force && conversionAttempts < MAX_ATTEMPTS) {
      conversionAttempts++;
      setTimeout(() => convertAllPrices(true), 300);
      return;
    }
    if (carCards.length > 0) conversionAttempts = 0;

    carCards.forEach(card => {
      const priceMXN    = parseFloat(card.dataset.priceMxn);
      const oldPriceMXN = parseFloat(card.dataset.oldPriceMxn);
      if (isNaN(priceMXN)) return;

      const displayPrice    = targetCurrency === 'USD' ? priceMXN / EXCHANGE_RATE : priceMXN;
      const displayOldPrice = !isNaN(oldPriceMXN) ? (targetCurrency === 'USD' ? oldPriceMXN / EXCHANGE_RATE : oldPriceMXN) : null;

      const priceNow     = card.querySelector('.price-now');
      const priceOld     = card.querySelector('.price-old');
      const currencyCode = card.querySelector('.currency-code');
      const oldCurrency  = card.querySelector('.currency-code-old');

      if (priceNow)     priceNow.textContent     = formatAmount(displayPrice, targetCurrency);
      if (priceOld && displayOldPrice) priceOld.textContent = formatAmount(displayOldPrice, targetCurrency);
      if (currencyCode) currencyCode.textContent = targetCurrency;
      if (oldCurrency)  oldCurrency.textContent  = targetCurrency;
    });

    if (typeof window.updateSummary === 'function') setTimeout(window.updateSummary, 50);
  }

  function observeLangChanges() {
    new MutationObserver(() => {
      conversionAttempts = 0;
      setTimeout(() => convertAllPrices(true), 100);
    }).observe(document.documentElement, { attributes: true, attributeFilter: ['lang'] });
  }

  function observeNewCards() {
    new MutationObserver(mutations => {
      const hasNew = mutations.some(m =>
        Array.from(m.addedNodes).some(n =>
          n.nodeType === 1 && (n.classList?.contains('car-card') || n.querySelectorAll?.('.car-card').length > 0)
        )
      );
      if (hasNew) setTimeout(() => convertAllPrices(true), 100);
    }).observe(document.body, { childList: true, subtree: true });
  }

  function listenLanguageButtons() {
    document.addEventListener('click', e => {
      if (e.target.closest('.lang-btn, .dropdown-item[href*="/lang/"]')) {
        conversionAttempts = 0;
        [50, 200, 500].forEach(ms => setTimeout(() => convertAllPrices(true), ms));
      }
    });
  }

  function observeLocalStorage() {
    window.addEventListener('storage', e => {
      if (e.key === 'idiomaPreferido') { conversionAttempts = 0; setTimeout(() => convertAllPrices(true), 100); }
    });
  }

  function startPolling() {
    if (_intervalId) clearInterval(_intervalId);
    let lastCurrency = getCurrencyFromHtml();
    _intervalId = setInterval(() => {
      if (document.visibilityState !== 'visible') return;
      const current = getCurrencyFromHtml();
      if (current !== lastCurrency) { lastCurrency = current; convertAllPrices(true); return; }
      const first = document.querySelector('.car-card .price-now');
      if (first && !first.textContent) convertAllPrices(true);
    }, 3000);
  }

  function init() {
    if (document.readyState === 'loading') {
      document.addEventListener('DOMContentLoaded', () => setTimeout(() => convertAllPrices(), 100));
    } else {
      setTimeout(() => convertAllPrices(), 100);
    }
    observeLangChanges();
    observeNewCards();
    listenLanguageButtons();
    observeLocalStorage();
    startPolling();
  }

  init();
  window.convertCarPrices  = convertAllPrices;
  window.getCurrentCurrency = getCurrencyFromHtml;

  // Lazy load slides del hero carousel
  (function () {
    function initCarouselLazyLoad() {
      document.querySelectorAll('.carousel .slide[data-src]').forEach(slide => {
        const src = slide.getAttribute('data-src');
        if (!src) return;
        const img = new Image();
        img.onload = () => { slide.style.backgroundImage = `url('${src}')`; slide.removeAttribute('data-src'); };
        img.src = src;
      });
    }
    window.addEventListener('load', initCarouselLazyLoad);
  })();


})();
