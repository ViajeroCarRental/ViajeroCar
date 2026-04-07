/* ====================
   Polyfills
==================== */
(function(){
  "use strict";

  // Polyfill seguro para closest en algunos webviews viejos
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
})();

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

/* ====================
   Icono de cuenta
==================== */
(function(){
  "use strict";
  function syncAccountIcon(){
    const link = document.getElementById('accountLink');
    if(!link) return;

    const name  = link.getAttribute('data-auth-name')  || '';
    const email = link.getAttribute('data-auth-email') || '';
    const initial = (name?.trim()[0] || email?.trim()[0] || '').toUpperCase();

    if(initial){
      const locale = getCurrentLocale();
      link.title = locale === 'en' ? 'My profile' : 'Mi perfil';
      link.innerHTML = `<span class="avatar-mini">${initial}</span>`;
    } else {
      const locale = getCurrentLocale();
      link.title = locale === 'en' ? 'Sign in' : 'Iniciar sesión';
      link.innerHTML = '<i class="fa-regular fa-user"></i>';
    }
  }
  document.addEventListener('DOMContentLoaded', syncAccountIcon);
})();

/* =====================================================================
   FLEET: SOLO FLECHAS (SIN LOOP / SIN AUTOPLAY) + TOPES + BOTONES GRIS
===================================================================== */
(function(){
  "use strict";

  function initFleetControlled(fleet){
    const track = fleet.querySelector('.fleet-track');
    const prev  = fleet.querySelector('.fleet-btn.prev');
    const next  = fleet.querySelector('.fleet-btn.next');
    if(!track || !prev || !next) return;

    if(track.dataset.fleetReady === "1") return;
    track.dataset.fleetReady = "1";

    const GAP_FALLBACK = 18;
    let lock = false;

    function getGapPx(){
      const st = getComputedStyle(track);
      const gap = parseFloat(st.columnGap || st.gap) || 0;
      return gap || GAP_FALLBACK;
    }

    function getStepPx(){
      const card = track.querySelector('.car-card');
      if(!card) return 340;
      const rect = card.getBoundingClientRect();
      const cs = getComputedStyle(card);
      const ml = parseFloat(cs.marginLeft) || 0;
      const mr = parseFloat(cs.marginRight) || 0;
      return rect.width + ml + mr + getGapPx();
    }

    function getMaxScroll(){
      return Math.max(0, track.scrollWidth - track.clientWidth);
    }

    function updateBtns(){
      const max = getMaxScroll();
      const current = track.scrollLeft;

      const atStart = current <= 10;
      const atEnd   = current >= (max - 10);

      prev.disabled = atStart;
      next.disabled = atEnd;

      prev.classList.toggle('is-disabled', atStart);
      next.classList.toggle('is-disabled', atEnd);
    }

    function pulseLimit(btn){
      btn.classList.add('animating');
      setTimeout(() => btn.classList.remove('animating'), 300);
    }

    function moveBy(dir){
      if(lock) return;

      const maxScroll = getMaxScroll();
      const from = track.scrollLeft;
      const step = getStepPx();

      if(dir > 0 && from >= (maxScroll - 10)){
        pulseLimit(next);
        return;
      }
      if(dir < 0 && from <= 10){
        pulseLimit(prev);
        return;
      }

      const to = Math.max(0, Math.min(from + (dir * step), maxScroll));

      lock = true;
      track.scrollTo({ left: to, behavior: 'smooth' });

      window.setTimeout(()=>{
        lock = false;
        updateBtns();
      }, 420);
    }

    next.addEventListener('click', (e)=>{ e.preventDefault(); moveBy(1); });
    prev.addEventListener('click', (e)=>{ e.preventDefault(); moveBy(-1); });

    track.addEventListener('scroll', ()=>{
      if(lock) return;
      updateBtns();
    }, { passive:true });

    function forceStart(){
      track.scrollLeft = 0;
      updateBtns();
    }

    requestAnimationFrame(()=> requestAnimationFrame(forceStart));
    window.addEventListener('load', forceStart, { once:true });
    setTimeout(forceStart, 100);
  }

  document.addEventListener('DOMContentLoaded', ()=>{
    document.querySelectorAll('.fleet').forEach(initFleetControlled);
  });
})();

/* ====================
   Año en footer
==================== */
(function(){
  "use strict";
  const y = document.getElementById('year');
  if(y) y.textContent = new Date().getFullYear();
})();

/* ====================
   Modal de bienvenida
==================== */
(function(){
  "use strict";
  const modal  = document.getElementById('welcomeModal');
  if(!modal) return;

  const nameEl   = document.getElementById('wmName');
  const closeBtn = document.getElementById('wmClose');
  const okBtn    = document.getElementById('wmOk');

  const open  = ()=> modal.classList.add('show');
  const close = ()=> modal.classList.remove('show');

  closeBtn?.addEventListener('click', close);
  okBtn?.addEventListener('click', close);
  modal.querySelector('.modal-backdrop')?.addEventListener('click', close);

  const shouldShow = modal.getAttribute('data-auto-show') === '1';
  const whoData    = modal.getAttribute('data-name');

  if(shouldShow){
    if(nameEl && whoData) nameEl.textContent = whoData;
    open();
  }
})();

/* =====================================================================
   FLATPICKR + SELECTS DE HORA + RESUMEN
===================================================================== */
(function(){
  "use strict";

  const rangeSummary = document.getElementById('rangeSummary');

  /* ==========================================================
     ✅ Inyectar CSS para ocultar el input visible de hora
  ========================================================== */
  (function injectTimeCss(){
    const id = "tpHideInputStyle";
    if(document.getElementById(id)) return;
    const st = document.createElement("style");
    st.id = id;
    st.textContent = `
      .tp-hidden-input{ display:none !important; }
      .tp-selects{ display:flex; gap:10px; margin-top:10px; }
      .tp-selects select{
        width:100%;
        height:48px;
        border-radius:12px;
        border:1px solid rgba(0,0,0,.12);
        padding:10px 12px;
        outline:none;
      }
    `;
    document.head.appendChild(st);
  })();


  /* ==========================
       SELECTS de hora
    ========================== */
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

  // SOLO pickupTime debe bloquear horas pasadas si la fecha es hoy
  if (input.id === "pickupTime") {
    const pickupDateValue = document.getElementById("pickupDate")?.value || "";
    if (isSameLocalDate(pickupDateValue, new Date())) {
      startHour = getMinPickupHour();
    }
  }

  selH.innerHTML = "";
  selH.insertAdjacentHTML("afterbegin", `<option value="" disabled selected>${hourPlaceholder}</option>`);

  for (let h = startHour; h < hourMax; h++) {
    const op = document.createElement("option");
    op.value = pad2(h);
    op.textContent = `${pad2(h)}:00`;
    selH.appendChild(op);
  }

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
  selH.className = "tp-hour custom-select-clean";

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
      return;
    }

    const finalH = pad2(Number(selH.value || 0));
    input.value = `${finalH}:00`;
    input.dispatchEvent(new Event("input", { bubbles: true }));
  }

  selH.addEventListener("change", sync);

  if (input.value && input.value !== "12:00") {
    const defaultHour = input.value.split(':')[0];
    const option = Array.from(selH.options).find(opt => opt.value === defaultHour);
    if (option) {
      selH.value = defaultHour;
      sync();
    }
  } else if (defaultValue && defaultValue !== "12:00") {
    const defaultHour = defaultValue.split(':')[0];
    const option = Array.from(selH.options).find(opt => opt.value === defaultHour);
    if (option) {
      selH.value = defaultHour;
      sync();
    } else {
      selH.selectedIndex = 0;
      input.value = "";
    }
  } else {
    selH.selectedIndex = 0;
    input.value = "";
  }
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

  // Cambiado: valor por defecto a "13:00"
  createTimeSelectsBelow(input, {
    hourMax: 24,
    defaultValue: input.value || "13:00"
  });

  input.addEventListener("change", updateSummary);
  input.addEventListener("input", updateSummary);

  // SOLO pickupTime reacciona al cambio de fecha
  if (id === "pickupTime") {
    const pickupDate = document.getElementById("pickupDate");
    if (pickupDate) {
      pickupDate.addEventListener("change", function() {
        rebuildHourOptions(input, { hourMax: 24 });
      });
    }
  }
}

document.addEventListener("DOMContentLoaded", () => {
  initAnalogTime("pickupTime");
  initAnalogTime("dropoffTime");
  updateSummary();
});

  function parseTimeTo24h(str) {
    const raw = String(str || '').trim();
    if (!raw) return { hh: 0, mm: 0 };

    const m = raw.match(/^(\d{1,2})/);
    if (!m) return { hh: 0, mm: 0 };

    let hh = Number(m[1] || 0);
    const mm = 0;

    if (Number.isFinite(hh)) {
      hh = Math.max(0, Math.min(23, hh));
    } else {
      hh = 0;
    }

    return { hh, mm };
  }

  function buildDT(dateId, timeId) {
    const d = document.getElementById(dateId)?.value;
    const t = document.getElementById(timeId)?.value || '00:00';
    if (!d) return null;

    const parts = d.split('-').map(Number);
    if (parts.length !== 3) return null;
    const [y, m, day] = parts;
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
    if (!s || !e) {
      rangeSummary.textContent = '';
      return;
    }

    const h = Math.round((e - s) / 36e5);
    const d = Math.ceil(h / 24);
    if (!Number.isFinite(h) || h <= 0) {
      rangeSummary.textContent = '';
      return;
    }

    const locale = getCurrentLocale();
    const daysText = locale === 'en' ? 'day(s)' : 'día(s)';
    const hoursText = locale === 'en' ? 'hour(s)' : 'hora(s)';
    rangeSummary.textContent = `Rental for ${d} ${daysText} · ~${h} ${hoursText}`;
  }

  (function bindFormFixes(){
    const form = document.getElementById("rentalForm");
    if(!form) return;
    if(form.dataset.bindFixes === "1") return;
    form.dataset.bindFixes = "1";

    const chk = document.getElementById("differentDropoff");
    const dropWrap  = document.getElementById("dropoffWrapper");
    const dropSel   = document.getElementById("dropoffPlace");
    const pickSel   = document.getElementById("pickupPlace");

    const pickTime  = document.getElementById("pickupTime");
    const dropTime  = document.getElementById("dropoffTime");

    const pickDate  = document.getElementById("pickupDate");
    const dropDate  = document.getElementById("dropoffDate");

    function syncHiddenFromSelects(hiddenId){
        const hidden = document.getElementById(hiddenId);
        if(!hidden) return;

        const wrap = hidden.closest(".time-field") || hidden.parentElement;
        const selH = wrap ? wrap.querySelector(".tp-selects .tp-hour") : null;

        if(selH && selH.value){
            const hh = String(selH.value).padStart(2,"0");
            hidden.value = `${hh}:00`;
        } else {
            if(!hidden.value) hidden.value = "12:00";
        }
    }

    function normalizeDateInput(input){
      if(!input) return;
      const v = String(input.value || "").trim();
      if(/^\d{4}-\d{2}-\d{2}$/.test(v)) return;

      const m = v.match(/^(\d{1,2})[\/\-](\d{1,2})[\/\-](\d{4})$/);
      if(m){
        const dd = String(m[1]).padStart(2,"0");
        const mm = String(m[2]).padStart(2,"0");
        const yy = m[3];
        input.value = `${yy}-${mm}-${dd}`;
      }
    }

    form.addEventListener("submit", ()=>{
      syncHiddenFromSelects("pickupTime");
      syncHiddenFromSelects("dropoffTime");

      normalizeDateInput(pickDate);
      normalizeDateInput(dropDate);

      if(chk && !chk.checked && dropSel && pickSel && pickSel.value){
        dropSel.value = pickSel.value;
      }

      updateSummary();

      if(pickTime && !pickTime.value) pickTime.value = "12:00";
      if(dropTime && !dropTime.value) dropTime.value = "12:00";
    }, { capture:true });

    const inputsToWatch = [pickSel, dropSel];

    inputsToWatch.forEach(el => {
      if (!el) return;

      const toggleHasValue = () => {
        if (el.value && el.value !== "") {
          el.classList.add('has-value');
          if (typeof $ !== 'undefined') {
            $(el).next('.select2-container').find('.select2-selection').addClass('has-value');
          }
        } else {
          el.classList.remove('has-value');
          if (typeof $ !== 'undefined') {
            $(el).next('.select2-container').find('.select2-selection').removeClass('has-value');
          }
        }
      };

      if (typeof $ !== 'undefined') {
        $(el).on('change', toggleHasValue);
      } else {
        el.addEventListener('change', toggleHasValue);
      }

      setTimeout(toggleHasValue, 500);
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

  function openFab(){
    fab.classList.add("open");
    btn.setAttribute("aria-expanded", "true");
  }
  function closeFab(){
    fab.classList.remove("open");
    btn.setAttribute("aria-expanded", "false");
  }

  btn.addEventListener("click", (e)=>{
    e.preventDefault();
    fab.classList.contains("open") ? closeFab() : openFab();
  });

  document.addEventListener("click", (e)=>{
    if(!fab.classList.contains("open")) return;
    if(!fab.contains(e.target)) closeFab();
  });

  document.addEventListener("keydown", (e)=>{
    if(e.key === "Escape") closeFab();
  });
})();

/* =====================================================================
   Swiper tiles (tarjetas)
===================================================================== */
(function(){
  "use strict";

  function initTilesSwiper(){
    if(typeof window.Swiper !== "function") return;

    const allSwipers = document.querySelectorAll('.vj-tiles-swiper');

    allSwipers.forEach((el) => {
      if(el.swiper){
        try {
          if(el.swiper.autoplay) el.swiper.autoplay.stop();
          el.swiper.destroy(true, true);
        } catch(_){}
      }

      if(el.dataset.swReady === "1") return;
      el.dataset.swReady = "1";

      new Swiper(el, {
        loop: false,
        autoplay: false,
        allowTouchMove: true,
        speed: 650,
        spaceBetween: 18,
        slidesPerView: 1.06,
        centeredSlides: false,
        grabCursor: true,
        navigation: {
          nextEl: el.querySelector('.swiper-button-next'),
          prevEl: el.querySelector('.swiper-button-prev'),
        },
        pagination: {
          el: el.querySelector('.swiper-pagination'),
          clickable: true
        },
        breakpoints: {
          560:  { slidesPerView: 1.4, spaceBetween: 18 },
          768:  { slidesPerView: 2,   spaceBetween: 20 },
          1024: { slidesPerView: 3,   spaceBetween: 22 },
          1280: { slidesPerView: 3.3, spaceBetween: 24 }
        }
      });
    });
  }

  document.addEventListener('DOMContentLoaded', initTilesSwiper);
})();

/* =====================================================================
   Toast reservas (rvBanner)
===================================================================== */
(function(){
  "use strict";

  const SEQ = [5,7,10,5,12];
  const SHOW_MS = 7000;
  const HIDE_MS = 25000;

  const banner = document.getElementById('rvBanner');
  const bar    = document.getElementById('rvBar');
  const count  = document.getElementById('rvCount');
  const close  = document.getElementById('rvClose');

  if(!banner || !bar || !count) return;
  if(banner.dataset.rvReady === "1") return;
  banner.dataset.rvReady = "1";

  let idx = 0, loop = true, hideT = null, nextT = null;
  let paused = false, startTs = 0, remaining = SHOW_MS;

  function setBar(ms){
    bar.style.transition = 'none';
    bar.style.width = '0%';
    requestAnimationFrame(()=>{ requestAnimationFrame(()=>{
      bar.style.transition = `width ${ms}ms linear`;
      bar.style.width = '100%';
    });});
  }

  function showOnce(){
    count.textContent = SEQ[idx];
    idx = (idx + 1) % SEQ.length;

    banner.style.display = 'block';
    banner.classList.remove('rv-out');
    banner.classList.add('rv-in');

    remaining = SHOW_MS;
    startTs = performance.now();
    setBar(SHOW_MS);

    hideT = setTimeout(hide, SHOW_MS);
  }

  function hide(){
    banner.classList.remove('rv-in');
    banner.classList.add('rv-out');
    setTimeout(()=>{
      banner.style.display = 'none';
      if(loop){ nextT = setTimeout(showOnce, HIDE_MS); }
    }, 260);
  }

  banner.addEventListener('mouseenter', ()=>{
    paused = true;
    const elapsed = performance.now() - startTs;
    remaining = Math.max(0, SHOW_MS - elapsed);
    if(hideT){ clearTimeout(hideT); hideT = null; }
    bar.style.transition = 'none';
  });

  banner.addEventListener('mouseleave', ()=>{
    if(!paused) return;
    paused = false;
    setTimeout(()=>{
      setBar(remaining);
      hideT = setTimeout(hide, remaining);
      startTs = performance.now() - (SHOW_MS - remaining);
    }, 30);
  });

})();

/* ====================
   Control del checkbox
==================== */
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

/* ====================
   Sincronizar valores
==================== */
document.addEventListener('DOMContentLoaded', function() {
    const pickSel = document.getElementById('pickupPlace');
    const dropSel = document.getElementById('dropoffPlace');
    const chk = document.getElementById('differentDropoff');

    if (pickSel && dropSel && chk) {
        pickSel.addEventListener('change', function() {
            if (!chk.checked && dropSel) {
                dropSel.value = this.value;
            }
        });
    }

    setDropoffState();

    if (chk) {
        chk.addEventListener('change', setDropoffState);
    }

    window.addEventListener('resize', function() {
        setDropoffState();
    });
});

/* ============================================================
    VALIDACIONES DEL FORMULARIO - VERSIÓN CORREGIDA CON TRADUCCIÓN
============================================================ */
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById("rentalForm");
    if (!form) return;

    form.addEventListener("submit", function(e) {
        e.preventDefault();
        console.log('=== VALIDANDO FORMULARIO ===');

        let valid = true;

        document.querySelectorAll('.error-msg').forEach(el => el.remove());
        document.querySelectorAll('.field-error, .field-success').forEach(el => {
            el.classList.remove('field-error', 'field-success');
        });

        /* ===== 1. VALIDAR UBICACIONES ===== */
        const selects = [
            { id: 'pickupPlace', msgKey: 'location' },
            { id: 'dropoffPlace', msgKey: 'location' }
        ];

        selects.forEach(campo => {
            const select = document.getElementById(campo.id);
            if (!select) return;

            if (!select.value || select.value === '') {
                valid = false;
                select.classList.add('field-error');

                const container = select.closest('.field');
                if (container) {
                    const msg = document.createElement('span');
                    msg.className = 'error-msg';
                    msg.textContent = getErrorMessage(campo.msgKey);
                    container.appendChild(msg);
                }
                console.log(`Error: ${campo.id}`);
            } else {
                select.classList.add('field-success');
            }
        });

/* ===== 2. VALIDAR FECHAS  ===== */
const fechas = [
    { id: 'pickupDate', msgKey: 'date' },
    { id: 'dropoffDate', msgKey: 'date' }
];

fechas.forEach(campo => {
    const hiddenInput = document.getElementById(campo.id);
    if (!hiddenInput) return;

    const container = hiddenInput.closest('.dt-field');
    if (!container) return;


    container.querySelectorAll('.error-msg').forEach(el => el.remove());

    const todosLosInputs = container.querySelectorAll('input');

    if (!hiddenInput.value || hiddenInput.value.trim() === '') {
        valid = false;

        todosLosInputs.forEach(inp => inp.classList.add('field-error'));

        const msg = document.createElement('span');
        msg.className = 'error-msg';
        msg.textContent = getErrorMessage(campo.msgKey);
        container.appendChild(msg);
    } else {
        todosLosInputs.forEach(inp => {
            inp.classList.remove('field-error');
            inp.classList.add('field-success');
        });
    }
});
        /* ===== 3. VALIDAR HORAS - CORREGIDO PARA SELECTS ===== */
        const horas = [
            { id: 'pickupTime', msgKey: 'time' },
            { id: 'dropoffTime', msgKey: 'time' }
        ];

        horas.forEach(campo => {
            const hiddenInput = document.getElementById(campo.id);
            if (!hiddenInput) return;

            const timeField = hiddenInput.closest('.time-field');
            if (!timeField) return;

            const hourSelect = timeField.querySelector('.tp-selects .tp-hour');

            const hasValue = hourSelect && hourSelect.value && hourSelect.value !== '';

            if (!hasValue) {
                valid = false;

                if (hourSelect) {
                    hourSelect.classList.add('field-error');
                }

                hiddenInput.classList.add('field-error');

                const msg = document.createElement('span');
                msg.className = 'error-msg';
                msg.textContent = getErrorMessage(campo.msgKey);
                timeField.appendChild(msg);

                console.log(`Error: ${campo.id} - SIN HORA`);
            } else {
                if (hourSelect) {
                    hourSelect.classList.add('field-success');
                }
                hiddenInput.classList.add('field-success');
                console.log(`OK: ${campo.id} - HORA: ${hourSelect.value}`);
            }
        });

        console.log('=== RESULTADO FINAL ===', valid ? 'FORMULARIO VÁLIDO' : 'FORMULARIO INVÁLIDO');

        if (valid) {
            console.log('Enviando formulario...');
            form.submit();
        }
    });
});

/* ============================================================
    LIMPIAR ERRORES AL INTERACTUAR
============================================================ */
document.addEventListener('DOMContentLoaded', function() {
    const inputs = document.querySelectorAll('input, select');
    const hourSelects = document.querySelectorAll('.tp-selects .tp-hour');
    const flatpickrInputs = document.querySelectorAll('.flatpickr-input');

    inputs.forEach(input => {
        input.addEventListener('input', function() {
            if (this.classList.contains('field-error')) {
                this.classList.remove('field-error');
                const container = this.closest('.field, .dt-field, .time-field');
                if (container) {
                    const msg = container.querySelector('.error-msg');
                    if (msg) msg.remove();
                }
            }
        });

        input.addEventListener('change', function() {
            if (this.classList.contains('field-error')) {
                this.classList.remove('field-error');
                const container = this.closest('.field, .dt-field, .time-field');
                if (container) {
                    const msg = container.querySelector('.error-msg');
                    if (msg) msg.remove();
                }
            }
        });
    });

    hourSelects.forEach(select => {
        select.addEventListener('change', function() {
            if (this.classList.contains('field-error')) {
                this.classList.remove('field-error');
                const timeField = this.closest('.time-field');
                if (timeField) {
                    const msg = timeField.querySelector('.error-msg');
                    if (msg) msg.remove();
                }
            }
        });
    });

    flatpickrInputs.forEach(input => {
        if (input.type === 'hidden') return;

        input.addEventListener('change', function() {
            if (this.classList.contains('field-error')) {
                this.classList.remove('field-error');
                const container = this.closest('.dt-field');
                if (container) {
                    const msg = container.querySelector('.error-msg');
                    if (msg) msg.remove();
                }
            }
        });

        input.addEventListener('click', function() {
            if (this.classList.contains('field-error')) {
                this.classList.remove('field-error');
                const container = this.closest('.dt-field');
                if (container) {
                    const msg = container.querySelector('.error-msg');
                    if (msg) msg.remove();
                }
            }
        });
    });
});
/* ============================================================
    INICIALIZAR FLATPICKR - CALENDARIOS CON IDIOMA DINÁMICO
============================================================ */
(function() {
    "use strict";

    // --- FUNCIÓN PARA INYECTAR EL OVERLAY ---
    function injectOverlay() {
        if (document.getElementById("fp-view-overlay-3")) return;
        const overlay = document.createElement("div");
        overlay.id = "fp-view-overlay-3";
        overlay.className = "fp-view-overlay-3";
        document.body.appendChild(overlay);
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initFlatpickr);
    } else {
        initFlatpickr();
    }

    function initFlatpickr() {
        if (typeof flatpickr === 'undefined') {
            console.error('❌ Flatpickr no está cargado');
            return;
        }

        injectOverlay(); // Inyectamos el fondo gris al inicio

        const pickupElement = document.getElementById('pickupDate');
        const dropoffElement = document.getElementById('dropoffDate');
        const overlay = document.getElementById('fp-view-overlay-3');

        if (!pickupElement || !dropoffElement) return;

        let pickupPicker;
        let dropoffPicker;

        function initPickers() {
            const localeData = getFlatpickrLocale();

            // CONFIGURACIÓN COMÚN CON EVENTOS DE OVERLAY
            const commonConfig = {
                dateFormat: "Y-m-d",
                altInput: true,
                altFormat: "d-M-y",
                locale: localeData,
                allowInput: false,
                disableMobile: true,
                onOpen: function() {
                    if (overlay) overlay.classList.add('active');
                },
                onClose: function() {
                    if (overlay) overlay.classList.remove('active');
                }
            };

            // Inicializar Pickup Date
            try {
                if (pickupPicker) pickupPicker.destroy();
                pickupPicker = flatpickr(pickupElement, {
                    ...commonConfig,
                    minDate: "today",
                    onChange: function(selectedDates, dateStr, instance) {
                        pickupElement.value = dateStr;
                        pickupElement.dispatchEvent(new Event('change', { bubbles: true }));
                        if (dropoffPicker && selectedDates[0]) {
                            const minDate = new Date(selectedDates[0]);
                            minDate.setDate(minDate.getDate() + 1);
                            dropoffPicker.set('minDate', minDate);
                        }
                    }
                });
            } catch(e) { console.error('Error en pickup:', e); }

            // Inicializar Dropoff Date
            try {
                let minDate = "today";
                if (pickupElement.value) {
                    const pickupDate = new Date(pickupElement.value);
                    if (!isNaN(pickupDate)) {
                        pickupDate.setDate(pickupDate.getDate() + 1);
                        minDate = pickupDate;
                    }
                }

                if (dropoffPicker) dropoffPicker.destroy();
                dropoffPicker = flatpickr(dropoffElement, {
                    ...commonConfig,
                    minDate: minDate,
                    onChange: function(selectedDates, dateStr, instance) {
                        dropoffElement.value = dateStr;
                        dropoffElement.dispatchEvent(new Event('change', { bubbles: true }));
                    }
                });
            } catch(e) { console.error('Error en dropoff:', e); }
        }


        initPickers();

        // --- OBSERVER PARA CAMBIOS DE IDIOMA ---
        const observer = new MutationObserver(() => {
            initPickers();
            const locale = (typeof getCurrentLocale === 'function') ? getCurrentLocale() : 'es';
            const hourPlaceholder = locale === 'en' ? 'Hour' : 'Hora';

            document.querySelectorAll('.tp-hour').forEach(select => {
                if (select.options[0]) select.options[0].textContent = hourPlaceholder;
            });

            if (typeof updateSummary === 'function') updateSummary();
        });

        observer.observe(document.documentElement, { attributes: true, attributeFilter: ['lang'] });

        // --- PLACEHOLDERS INICIALES ---
        setTimeout(() => {
            const flatpickrInputs = document.querySelectorAll('.flatpickr-input');
            flatpickrInputs.forEach(input => {
                if (!input.value) {
                    input.placeholder = 'dd-Mmm-yy';
                }
            });
        }, 100);
    }
})();

/* ============================================================
    CONTROL DE SCROLL PARA FORMULARIO MÓVIL/TABLET
============================================================ */
(function() {
    "use strict";

    function initScrollControl() {
        const btnAbrir = document.getElementById('btn-abrir-buscador');
        const btnCerrar = document.getElementById('btn-cerrar-buscador');
        const buscador = document.getElementById('miBuscador');
        const dropoffPlace = document.getElementById('dropoffPlace');

        if (!btnAbrir || !btnCerrar || !buscador) return;

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
            window.scrollTo(0, parseInt(scrollY, 10));

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
            if (buscador.classList.contains('active')) {
                if (e.key === 'ArrowDown' || e.key === 'ArrowUp' || e.key === ' ' || e.key === 'Spacebar') {
                    e.preventDefault();
                }
            }
        }, { passive: false });

        if (dropoffPlace) {
            dropoffPlace.addEventListener('touchstart', function(e) {
                this.dataset.touchStartY = e.touches[0].clientY;
                this.dataset.isDragging = '0';
            }, { passive: true });

            dropoffPlace.addEventListener('touchmove', function(e) {
                const startY = parseFloat(this.dataset.touchStartY || '0');
                const currentY = e.touches[0].clientY;
                const diffY = Math.abs(currentY - startY);

                if (diffY > 8) {
                    this.dataset.isDragging = '1';
                }
            }, { passive: true });

            dropoffPlace.addEventListener('touchend', function() {
                if (this.dataset.isDragging === '1') {
                    this.dataset.isDragging = '0';
                    return;
                }
            }, { passive: true });
        }
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initScrollControl);
    } else {
        initScrollControl();
    }

    /* ============================================================
   PARCHE RÁPIDO: SCROLL MANUAL SOBRE #dropoffWrapper EN MÓVIL
============================================================ */
document.addEventListener('DOMContentLoaded', function () {
    const modal = document.getElementById('miBuscador');
    const dropoffWrapper = document.getElementById('dropoffWrapper');

    if (!modal || !dropoffWrapper) return;

    let startY = 0;
    let startScrollTop = 0;
    let dragging = false;

    function isMobileFormOpen() {
        return window.innerWidth <= 1124 && modal.classList.contains('active');
    }

    dropoffWrapper.addEventListener('touchstart', function (e) {
        if (!isMobileFormOpen()) return;

        startY = e.touches[0].clientY;
        startScrollTop = modal.scrollTop;
        dragging = false;
    }, { passive: true });

    dropoffWrapper.addEventListener('touchmove', function (e) {
        if (!isMobileFormOpen()) return;

        const currentY = e.touches[0].clientY;
        const diffY = startY - currentY;

        if (Math.abs(diffY) > 6) {
            dragging = true;
            e.preventDefault();
            modal.scrollTop = startScrollTop + diffY;
        }
    }, { passive: false });

    dropoffWrapper.addEventListener('touchend', function () {
        setTimeout(() => {
            dragging = false;
        }, 50);
    }, { passive: true });

    const dropoffPlace = document.getElementById('dropoffPlace');
    if (dropoffPlace) {
        dropoffPlace.addEventListener('click', function (e) {
            if (dragging) {
                e.preventDefault();
                e.stopPropagation();
            }
        });
    }
});
})();

/* ============================================================
                                SELECT2
============================================================ */
$(document).ready(function() {

  function formatOption(option) {
    if (!option.id) {
      return $('<span class="icon-item"><i class="fa-solid fa-location-dot"></i> ' + option.text + '</span>');
    }

    let iconClass = window.iconosPorId ? (window.iconosPorId[option.id] || 'fa-building') : 'fa-building';

    return $(
        '<span class="icon-item"><i class="fa-solid ' + iconClass + '"></i> ' +
        option.text +
        '</span>');
  }

  $('#pickupPlace, #dropoffPlace').select2({
    templateResult: formatOption,
    templateSelection: formatOption,
    escapeMarkup: function(markup) { return markup; },
    width: '100%',
    minimumResultsForSearch: Infinity
  });

});
/* ============================================================
   MODAL MEMBRESÍA - CON DOS BOTONES QUE ABREN EL MODAL
============================================================ */
document.addEventListener('DOMContentLoaded', function() {
    const modal = document.getElementById('membershipModal');
    const openBtnCorner = document.getElementById('openMembershipModalBtn');
    const openBtnMain = document.getElementById('openMembershipModalFromBtn');
    const closeBtn = document.getElementById('closeMembershipModalBtn');

    if (!modal || !closeBtn) return;

    function openModal() {
        modal.classList.add('show');
        document.body.style.overflow = 'hidden';
    }

    function closeModal() {
        modal.classList.remove('show');
        document.body.style.overflow = '';
    }

    if (openBtnCorner) {
        openBtnCorner.addEventListener('click', function(e) {
            e.preventDefault();
            openModal();
        });
    }

    if (openBtnMain) {
        openBtnMain.addEventListener('click', function(e) {
            e.preventDefault();
            openModal();
        });
    }

    if (closeBtn) {
        closeBtn.addEventListener('click', closeModal);
    }

    modal.addEventListener('click', function(event) {
        if (event.target === modal || event.target.classList.contains('modal-membership-backdrop')) {
            closeModal();
        }
    });

    document.addEventListener('keydown', function(event) {
        if (event.key === 'Escape' && modal.classList.contains('show')) {
            closeModal();
        }
    });
});
/* ============================================================
   CONVERSIÓN DE MONEDA UNIFICADA

============================================================ */
(function() {
    "use strict";

    const EXCHANGE_RATE = 20;
    let conversionAttempts = 0;
    const MAX_ATTEMPTS = 10;

    function getCurrencyFromHtml() {
        const currentLang = document.documentElement.lang || 'es';
        return currentLang === 'en' ? 'USD' : 'MXN';
    }


    function formatAmount(amount, currency) {
        if (currency === 'USD') {
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
    function convertAllPrices(force = false) {
        const targetCurrency = getCurrencyFromHtml();
        const carCards = document.querySelectorAll('.car-card');

        console.log(`🔄 Convirtiendo precios a: ${targetCurrency} - Tarjetas: ${carCards.length}`);


        if (carCards.length === 0 && !force && conversionAttempts < MAX_ATTEMPTS) {
            conversionAttempts++;
            console.log(`⏳ Reintento ${conversionAttempts}/${MAX_ATTEMPTS} en 300ms...`);
            setTimeout(() => convertAllPrices(true), 300);
            return;
        }

        if (carCards.length > 0) {
            conversionAttempts = 0;
        }

        let convertedCount = 0;

        carCards.forEach((card) => {
            const priceMXN = parseFloat(card.dataset.priceMxn);
            const oldPriceMXN = parseFloat(card.dataset.oldPriceMxn);

            if (isNaN(priceMXN)) return;

            let displayPrice, displayOldPrice;

            if (targetCurrency === 'USD') {
                displayPrice = priceMXN / EXCHANGE_RATE;
                displayOldPrice = !isNaN(oldPriceMXN) ? oldPriceMXN / EXCHANGE_RATE : null;
            } else {
                displayPrice = priceMXN;
                displayOldPrice = oldPriceMXN;
            }

            const priceNowSpan = card.querySelector('.price-now');
            const priceOldSpan = card.querySelector('.price-old');
            const currencySpan = card.querySelector('.currency-code');
            const oldCurrencySpan = card.querySelector('.currency-code-old');

            if (priceNowSpan) {
                priceNowSpan.textContent = formatAmount(displayPrice, targetCurrency);
                convertedCount++;
            }
            if (priceOldSpan && displayOldPrice) {
                priceOldSpan.textContent = formatAmount(displayOldPrice, targetCurrency);
            }
            if (currencySpan) currencySpan.textContent = targetCurrency;
            if (oldCurrencySpan) oldCurrencySpan.textContent = targetCurrency;
        });

        console.log(`💰 Conversión completada: ${convertedCount} tarjetas a ${targetCurrency}`);

        if (typeof updateSummary === 'function') {
            setTimeout(updateSummary, 50);
        }
    }

    function observeLangChanges() {
        const observer = new MutationObserver(() => {
            console.log('🔔 Atributo lang cambiado a:', document.documentElement.lang);
            conversionAttempts = 0;
            setTimeout(() => convertAllPrices(true), 100);
        });

        observer.observe(document.documentElement, {
            attributes: true,
            attributeFilter: ['lang']
        });

        console.log('✅ Observador de idioma iniciado');
    }
    function observeNewCards() {
        const cardObserver = new MutationObserver((mutations) => {
            let hasNewCards = false;

            mutations.forEach(mutation => {
                mutation.addedNodes.forEach(node => {
                    if (node.nodeType === 1) {
                        if (node.classList && node.classList.contains('car-card')) {
                            hasNewCards = true;
                        }
                        if (node.querySelectorAll && node.querySelectorAll('.car-card').length > 0) {
                            hasNewCards = true;
                        }
                    }
                });
            });

            if (hasNewCards) {
                console.log('🆕 Nuevas tarjetas detectadas');
                setTimeout(() => convertAllPrices(true), 100);
            }
        });

        cardObserver.observe(document.body, { childList: true, subtree: true });
    }

    function listenLanguageButtons() {
        document.addEventListener('click', (e) => {
            const langButton = e.target.closest('.lang-btn, .dropdown-item[href*="/lang/"]');
            if (langButton) {
                console.log('🔘 Click en selector de idioma');
                conversionAttempts = 0;


                setTimeout(() => convertAllPrices(true), 50);
                setTimeout(() => convertAllPrices(true), 200);
                setTimeout(() => convertAllPrices(true), 500);
            }
        });
    }


    function observeLocalStorage() {
        window.addEventListener('storage', (e) => {
            if (e.key === 'idiomaPreferido') {
                console.log('📦 Storage: idioma cambiado a', e.newValue);
                conversionAttempts = 0;
                setTimeout(() => convertAllPrices(true), 100);
            }
        });
    }


    function init() {
        console.log('🚀 Iniciando sistema de conversión de moneda');


        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', () => {
                setTimeout(() => convertAllPrices(), 100);
            });
        } else {
            setTimeout(() => convertAllPrices(), 100);
        }


        observeLangChanges();
        observeNewCards();
        listenLanguageButtons();
        observeLocalStorage();

        let lastCurrency = getCurrencyFromHtml();
        setInterval(() => {
            const currentCurrency = getCurrencyFromHtml();
            if (currentCurrency !== lastCurrency) {
                lastCurrency = currentCurrency;
                convertAllPrices(true);
            } else {

                const carCards = document.querySelectorAll('.car-card');
                if (carCards.length > 0) {
                    const firstPrice = carCards[0].querySelector('.price-now')?.textContent;
                    if (!firstPrice || firstPrice === '') {
                        convertAllPrices(true);
                    }
                }
            }
        }, 3000);
    }

    init();


    window.convertCarPrices = convertAllPrices;
    window.getCurrentCurrency = getCurrencyFromHtml;
})();
