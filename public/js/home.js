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
      if(!link.title) link.title = 'Mi perfil';
      link.innerHTML = `<span class="avatar-mini">${initial}</span>`;
    } else {
      if(!link.title) link.title = 'Iniciar sesión';
      link.innerHTML = '<i class="fa-regular fa-user"></i>';
    }
  }
  document.addEventListener('DOMContentLoaded', syncAccountIcon);
})();

/* =====================================================================
   FLEET: SOLO FLECHAS (SIN LOOP / SIN AUTOPLAY) + TOPES + BOTONES GRIS
   - avanza 1 card por click
   - NO duplica HTML
   - cuando llega al inicio o al final, se queda ahí (NO se mueve)
   - fuerza inicio real (scrollLeft=0) para que PREV se vea gris desde el arranque
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

    // ✅ Función de Gris/Rojo con tolerancia de 10px
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

    // ✅ Función de Animación corregida
    function pulseLimit(btn){
      btn.classList.add('animating');
      setTimeout(() => btn.classList.remove('animating'), 300);
    }

    function moveBy(dir){
      if(lock) return;

      const maxScroll = getMaxScroll();
      const from = track.scrollLeft;
      const step = getStepPx();

      // ✅ Si intentas ir a la derecha y ya es gris (final)
      if(dir > 0 && from >= (maxScroll - 10)){
        pulseLimit(next);
        return;
      }
      // ✅ Si intentas ir a la izquierda y ya es gris (inicio)
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
/* =====================================================================
   Media carousels: SOLO manual (SIN LOOP / SIN AUTOPLAY)
   Nota: tus .media-carousel son "fade" (position:absolute), aquí no tocamos nada.
===================================================================== */
(function(){
  "use strict";
  // Sin autoplay, sin loop, sin timers aquí.
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
   Flatpickr FECHAS + SELECTS + Resumen
===================================================================== */
(function(){
  "use strict";

  const rangeSummary = document.getElementById('rangeSummary');

  /* ==========================================================
     ✅ Inyectar CSS para ocultar el input visible de hora
     (sin tocar tu CSS externo)
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

  // 1) FECHAS (Flatpickr + rangePlugin)
  if(window.flatpickr){
    const pickup = document.getElementById('pickupDate');
    const min = pickup?.dataset?.min || 'today';

    window.flatpickr('#pickupDate', {
      locale: 'es',
      altInput: true,
      altFormat: 'd/m/Y',
      dateFormat: 'Y-m-d',
      minDate: min,
      disableMobile: true,
      plugins: (typeof window.rangePlugin !== "undefined")
        ? [ new window.rangePlugin({ input: '#dropoffDate' }) ]
        : [],
      onChange: updateSummary
    });
  }

  /* ==========================
     SELECTS de hora/minuto
  ========================== */
  function pad2(n){ return String(n).padStart(2,"0"); }

  function createTimeSelectsBelow(input, opts){
    const { hourMax=24, minuteStep=15, defaultValue="12:00" } = (opts || {});

    const wrap = input.closest(".time-field") || input.parentElement;
    if(wrap && wrap.querySelector(".tp-selects")) return;

    const box = document.createElement("div");
    box.className = "tp-selects";

    const selH = document.createElement("select");
    selH.className = "tp-hour";
    selH.setAttribute("aria-label", "Hora");

    const selM = document.createElement("select");
    selM.className = "tp-min";
    selM.setAttribute("aria-label", "Minutos");

    for(let h=1; h<=hourMax; h++){
      const op = document.createElement("option");
      op.value = String(h);
      op.textContent = pad2(h);
      selH.appendChild(op);
    }

    for(let m=0; m<60; m+=minuteStep){
      const op = document.createElement("option");
      op.value = String(m);
      op.textContent = pad2(m);
      selM.appendChild(op);
    }

    const m = String(defaultValue).trim().match(/^(\d{1,2})(?::(\d{2}))?/);
    let hh = m ? Number(m[1]) : 12;
    let mm = m ? Number(m[2] || 0) : 0;

    if(hh > hourMax) hh = hourMax;
    if(hh === 24 && mm !== 0) mm = 0;

    mm = Math.round(mm / minuteStep) * minuteStep;
    if(mm >= 60) mm = 0;

/* Para visualizar H y Min en los input de Pick-Up y Devolucion */
    selH.selectedIndex = -1;
    selM.selectedIndex = -1;

    selH.insertAdjacentHTML("afterbegin", `<option value="" disabled selected>H</option>`);
    selM.insertAdjacentHTML("afterbegin", `<option value="" disabled selected>Min</option>`);


    function sync(){
      const h = Number(selH.value || 0);
      const mi = Number(selM.value || 0);

      if(h === 24 && mi !== 0){
        selM.value = "0";
      }

      const finalH = pad2(Number(selH.value || 0));
      const finalM = pad2(Number(selM.value || 0));
      input.value = `${finalH}:${finalM}`;

      input.dispatchEvent(new Event("input", { bubbles:true }));
    }

    selH.addEventListener("change", sync);
    selM.addEventListener("change", sync);

    box.appendChild(selH);
    box.appendChild(selM);

    if(wrap){
      wrap.appendChild(box);
    }else{
      input.insertAdjacentElement("afterend", box);
    }

    sync();
  }

  function initAnalogTime(id){
    const input = document.getElementById(id);
    if(!input) return;

    if(input.dataset.tpReady === "1") return;
    input.dataset.tpReady = "1";

    input.setAttribute("readonly", "readonly");
    input.setAttribute("inputmode", "none");

    input.classList.add("tp-hidden-input");
    input.setAttribute("aria-hidden", "true");

    createTimeSelectsBelow(input, {
      hourMax: 24,
      minuteStep: 15,
      defaultValue: input.value || "12:00"
    });

    if(!input.value) input.value = "12:00";

    input.addEventListener("change", updateSummary);
    input.addEventListener("input", updateSummary);
  }

  document.addEventListener("DOMContentLoaded", ()=>{
    initAnalogTime("pickupTime");
    initAnalogTime("dropoffTime");
    updateSummary();
  });

  function parseTimeTo24h(str){
    const raw = String(str || '').trim();
    if(!raw) return { hh:0, mm:0 };

    const m = raw.match(/^(\d{1,2})(?::(\d{2}))?\s*(AM|PM)?$/i);
    if(!m) return { hh:0, mm:0 };

    let hh = Number(m[1] || 0);
    let mm = Number(m[2] || 0);
    const ap = (m[3] || '').toUpperCase();

    if(ap === 'PM' && hh < 12) hh += 12;
    if(ap === 'AM' && hh === 12) hh = 0;

    if(Number.isFinite(hh)){
      hh = Math.max(0, Math.min(24, hh));
    } else {
      hh = 0;
    }
    mm = Number.isFinite(mm) ? Math.max(0, Math.min(59, mm)) : 0;

    if(hh === 24 && mm !== 0){
      mm = 0;
    }

    return { hh, mm };
  }

  function buildDT(dateId, timeId){
    const d = document.getElementById(dateId)?.value;
    const t = document.getElementById(timeId)?.value || '00:00';
    if(!d) return null;

    const parts = d.split('-').map(Number);
    if(parts.length !== 3) return null;
    const [y, m, day] = parts;
    if(!y || !m || !day) return null;

    const { hh, mm } = parseTimeTo24h(t);

    if(hh === 24){
      const dt = new Date(y, m - 1, day, 0, 0);
      dt.setDate(dt.getDate() + 1);
      return dt;
    }

    return new Date(y, m - 1, day, hh, mm);
  }

  function updateSummary(){
    if(!rangeSummary) return;

    const s = buildDT('pickupDate','pickupTime');
    const e = buildDT('dropoffDate','dropoffTime');
    if(!s || !e){ rangeSummary.textContent=''; return; }

    const h = Math.round((e - s) / 36e5);
    const d = Math.ceil(h / 24);
    if(!Number.isFinite(h) || h <= 0){ rangeSummary.textContent=''; return; }

    rangeSummary.textContent = `Renta por ${d} día(s) · ~${h} hora(s)`;
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

    function setDropoffState(){
      const on = !!(chk && chk.checked);

      if(dropWrap) dropWrap.style.display = on ? "" : "none";

      if(dropSel){
        if(on){
          dropSel.setAttribute("required", "required");
        }else{
          dropSel.removeAttribute("required");
          if(pickSel && pickSel.value) dropSel.value = pickSel.value;
        }
      }
    }

    pickSel && pickSel.addEventListener("change", ()=>{
      if(chk && !chk.checked && dropSel){
        dropSel.value = pickSel.value;
      }
    });

    chk && chk.addEventListener("change", setDropoffState);
    setDropoffState();

    function syncHiddenFromSelects(hiddenId){
      const hidden = document.getElementById(hiddenId);
      if(!hidden) return;

      const wrap = hidden.closest(".time-field") || hidden.parentElement;
      const selH = wrap ? wrap.querySelector(".tp-selects .tp-hour") : null;
      const selM = wrap ? wrap.querySelector(".tp-selects .tp-min")  : null;

      if(selH && selM){
        const hh = String(selH.value || "0").padStart(2,"0");
        const mm = String(selM.value || "0").padStart(2,"0");
        hidden.value = `${hh}:${mm}`;
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
   Swiper tiles (tarjetas) - CERO AUTOPLAY + SOLO FLECHAS + SIN LOOP
   ✅ si ya existía un swiper con autoplay de otro script, lo detiene y lo destruye
===================================================================== */
(function(){
  "use strict";

  function initTilesSwiper(){
    if(typeof window.Swiper !== "function") return;

    const el = document.querySelector('.vj-tiles-swiper');
    if(!el) return;

    // ✅ si alguien lo inicializó antes (y trae autoplay), lo apagamos
    if(el.swiper){
      try{
        if(el.swiper.autoplay) el.swiper.autoplay.stop();
      }catch(_){}
      try{
        el.swiper.destroy(true, true);
      }catch(_){}
    }

    if(el.dataset.swReady === "1") return;
    el.dataset.swReady = "1";

    // eslint-disable-next-line no-new
    new Swiper('.vj-tiles-swiper', {
      loop: false,
      autoplay: false,
      allowTouchMove: false, // ✅ solo flechas
      speed: 650,
      spaceBetween: 18,
      slidesPerView: 1.06,
      centeredSlides: false,
      grabCursor: false,
      navigation: {
        nextEl: '.vj-tiles-swiper .swiper-button-next',
        prevEl: '.vj-tiles-swiper .swiper-button-prev',
      },
      pagination: {
        el: '.vj-tiles-swiper .swiper-pagination',
        clickable: true
      },
      breakpoints: {
        560:  { slidesPerView: 1.4, spaceBetween: 18 },
        768:  { slidesPerView: 2,   spaceBetween: 20 },
        1024: { slidesPerView: 3,   spaceBetween: 22 },
        1280: { slidesPerView: 3.3, spaceBetween: 24 }
      }
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

  close && close.addEventListener('click', ()=>{
    loop = false;
    if(hideT) clearTimeout(hideT);
    if(nextT) clearTimeout(nextT);
    banner.style.display = 'none';
  });

  document.addEventListener('DOMContentLoaded', showOnce);

})();
