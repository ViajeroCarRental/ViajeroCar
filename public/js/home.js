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
  (function() {
    "use strict";

    if (window.flatpickr) {
        const pickup = document.getElementById('pickupDate');
        const dropoffEl = document.getElementById('dropoffDate');
        const minDate = pickup?.dataset?.min || 'today';

        // Según tus CSS, el cambio de diseño ocurre en 1124px
        // Desktop/Tablet: >= 1125px | Móvil: <= 1124px
        const isMobile = window.innerWidth <= 1124;

        // Configuración Base
        const commonConfig = {
            locale: 'es',
            altInput: true,
            altFormat: 'd/m/Y',
            dateFormat: 'Y-m-d',
            minDate: minDate,
            disableMobile: true,
            onReady: function(selectedDates, dateStr, instance) {
                const altPickup = instance.altInput;
                const updateLabels = () => {
                    if (altPickup) {
                        altPickup.value !== "" ? altPickup.classList.add('has-value') : altPickup.classList.remove('has-value');
                    }
                    if (dropoffEl && dropoffEl.nextElementSibling) {
                        const altDropoff = dropoffEl.nextElementSibling;
                        if (altDropoff.classList.contains('flatpickr-mobile')) return;
                        altDropoff.value !== "" ? altDropoff.classList.add('has-value') : altDropoff.classList.remove('has-value');
                    }
                };

                if(altPickup) {
                    altPickup.addEventListener('focus', () => altPickup.classList.add('has-value'));
                    altPickup.addEventListener('blur', updateLabels);
                }

                instance.config.onChange.push(() => {
                    updateLabels();
                    if (typeof updateSummary === "function") updateSummary();
                });
                setTimeout(updateLabels, 200);
            }
        };

        if (!isMobile) {
            /* ==========================================
               MODO DESKTOP / TABLET (Rango Conectado)
            ========================================== */
            window.flatpickr('#pickupDate', {
                ...commonConfig,
                plugins: (typeof window.rangePlugin !== "undefined")
                    ? [ new window.rangePlugin({ input: '#dropoffDate' }) ]
                    : []
            });
        } else {
            /* ==========================================
               MODO MÓVIL (Selecciones Independientes)
            ========================================== */
            // Inicializamos el de salida
            const fpPickup = window.flatpickr('#pickupDate', {
                ...commonConfig,
                onChange: function(selectedDates) {
                    // Al elegir fecha de salida, la ponemos como mínima en la de llegada
                    if (selectedDates[0]) {
                        fpDropoff.set('minDate', selectedDates[0]);
                    }
                    if (typeof updateSummary === "function") updateSummary();
                }
            });

            // Inicializamos el de llegada por separado
            const fpDropoff = window.flatpickr('#dropoffDate', {
                ...commonConfig,
                onChange: function() {
                    if (typeof updateSummary === "function") updateSummary();
                }
            });
        }
    }
  })();

  /* ==========================
       SELECTS de hora (SOLO HORAS)
    ========================== */
  function pad2(n) {
    return String(n).padStart(2, "0");
  }

  function createTimeSelectsBelow(input, opts) {
    const { hourMax = 24, defaultValue = "12:00" } = (opts || {});

    const wrap = input.closest(".time-field") || input.parentElement;
    if (wrap && wrap.querySelector(".tp-selects")) return;

    const box = document.createElement("div");
    box.className = "tp-selects w-100";

    const selH = document.createElement("select");
    selH.className = "tp-hour custom-select-clean";
    selH.setAttribute("aria-label", "Hora");

    // Llenamos solo con horas: 01, 02, 03... hasta hourMax
    for (let h = 1; h <= hourMax; h++) {
      const op = document.createElement("option");
      op.value = String(h);
      op.textContent = pad2(h);
      selH.appendChild(op);
    }

    // Placeholder con el texto "Hora"
    selH.selectedIndex = -1;
    selH.insertAdjacentHTML("afterbegin", `<option value="" disabled selected>Hora</option>`);

    function sync() {
      const finalH = pad2(Number(selH.value || 0));
      // Guardamos SOLO la hora, siempre con :00
      input.value = `${finalH}:00`;
      input.dispatchEvent(new Event("input", { bubbles: true }));
    }

    selH.addEventListener("change", sync);
    box.appendChild(selH);

    if (wrap) {
      wrap.appendChild(box);
    } else {
      input.insertAdjacentElement("afterend", box);
    }

    // Establecer valor por defecto solo si existe y no es "12:00"
    if (input.value && input.value !== "12:00") {
      const defaultHour = input.value.split(':')[0];
      const option = Array.from(selH.options).find(opt => opt.value === defaultHour);
      if (option) {
        option.selected = true;
        sync();
      }
    } else {
      // Mantener placeholder "Seleccionar hora" y limpiar input oculto
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

    createTimeSelectsBelow(input, {
      hourMax: 24,
      defaultValue: input.value || "12:00"
    });

    // NO establecer valor por defecto
    // if (!input.value) input.value = "12:00";

    input.addEventListener("change", updateSummary);
    input.addEventListener("input", updateSummary);
  }

  document.addEventListener("DOMContentLoaded", () => {
    initAnalogTime("pickupTime");
    initAnalogTime("dropoffTime");
    updateSummary();
  });

  function parseTimeTo24h(str) {
    const raw = String(str || '').trim();
    if (!raw) return { hh: 0, mm: 0 };

    // Extraer SOLO la hora, ignorar minutos
    const m = raw.match(/^(\d{1,2})/);
    if (!m) return { hh: 0, mm: 0 };

    let hh = Number(m[1] || 0);

    // Los minutos siempre serán 0
    const mm = 0;

    if (Number.isFinite(hh)) {
      hh = Math.max(0, Math.min(24, hh));
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

    // Sincronizar hora oculta desde el selector (siempre con :00)
    function syncHiddenFromSelects(hiddenId){
        const hidden = document.getElementById(hiddenId);
        if(!hidden) return;

        const wrap = hidden.closest(".time-field") || hidden.parentElement;
        const selH = wrap ? wrap.querySelector(".tp-selects .tp-hour") : null;

        if(selH && selH.value){
            const hh = String(selH.value).padStart(2,"0");
            hidden.value = `${hh}:00`; // Siempre mandamos minutos en 00
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

    /* === NUEVO: Control de bordes negros para Select2 y otros === */
    const inputsToWatch = [pickSel, dropSel];

    inputsToWatch.forEach(el => {
      if (!el) return;

      // Función para evaluar si tiene valor
      const toggleHasValue = () => {
        if (el.value && el.value !== "") {
          el.classList.add('has-value');
          // Si usa Select2, aplicamos al contenedor visual que crea la librería
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

      // Escuchar cambios (Select2 dispara 'change')
      if (typeof $ !== 'undefined') {
        $(el).on('change', toggleHasValue);
      } else {
        el.addEventListener('change', toggleHasValue);
      }

      // Ejecutar al inicio por si ya vienen con datos
      setTimeout(toggleHasValue, 500);
    });

  })(); // ← Cierra bindFormFixes
})(); // ← Cierra el IIFE principal de flatpickr

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
   Swiper tiles (tarjetas) - MULTIPLE INSTANCE SUPPORT
   ✅ Ahora detecta todos los carruseles y los inicializa por separado
===================================================================== */
(function(){
  "use strict";

  function initTilesSwiper(){
    if(typeof window.Swiper !== "function") return;

    // 1. Buscamos TODOS los carruseles con esa clase
    const allSwipers = document.querySelectorAll('.vj-tiles-swiper');

    allSwipers.forEach((el) => {
      // ✅ Si ya fue inicializado (ej. por otro script con autoplay), lo limpiamos
      if(el.swiper){
        try {
          if(el.swiper.autoplay) el.swiper.autoplay.stop();
          el.swiper.destroy(true, true);
        } catch(_){}
      }

      // Evitar doble inicialización
      if(el.dataset.swReady === "1") return;
      el.dataset.swReady = "1";

      // 2. Inicializamos el Swiper usando el elemento actual (el) en lugar del selector de clase
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

  // Ejecutar al cargar
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

// Control del checkbox con comportamiento mejorado
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
        // MÓVIL
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
        // ESCRITORIO
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
        // Limpiar estilos inline
        dropWrap.style.display = '';
        dropWrap.style.visibility = '';
        dropWrap.style.opacity = '';
    }
}

// Sincronizar valores cuando cambia pickup
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

    // Inicializar estado
    setDropoffState();

    // Evento del checkbox
    if (chk) {
        chk.addEventListener('change', setDropoffState);
    }

    // Evento resize
    window.addEventListener('resize', function() {
        setDropoffState();
    });
});

document.getElementById("rentalForm").addEventListener("submit", function(e){

    let valid = true;

    const fields = [
        {id:"pickupPlace", msg:"Ubicación requerida"},
        {id:"dropoffPlace", msg:"Ubicación requerida"},
        {id:"pickupDate", msg:"Fecha requerida"},
        {id:"pickupTime", msg:"Hora requerida"},
        {id:"dropoffDate", msg:"Fecha requerida"},
        {id:"dropoffTime", msg:"Hora requerida"}
    ];

    fields.forEach(field =>{

        const input = document.getElementById(field.id);
        const container = input.closest(".icon-field");

        container.classList.remove("field-error","field-success");

        const oldError = container.querySelector(".error-msg");
        if(oldError) oldError.remove();

        if(!input.value){

            const error = document.createElement("span");
            error.className = "error-msg";
            error.textContent = field.msg;

            container.appendChild(error);
            container.classList.add("field-error");

            valid = false;

        }else{
            container.classList.add("field-success");
        }

    });

    if(!valid){
        e.preventDefault();
    }

});
