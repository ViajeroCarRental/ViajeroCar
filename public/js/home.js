// ====================
// UI / PRESENTACIÓN SOLAMENTE
// ====================

// ===== Icono de cuenta (solo presentación) =====
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


// ===== Navbar: glass -> sólida + hamburguesa (MOBILE LIMPIO) =====
(function(){
  "use strict";

  const topbar = document.querySelector(".topbar");
  if(!topbar) return;

  function onScroll(){
    if(window.scrollY > 40) topbar.classList.add("solid");
    else topbar.classList.remove("solid");
  }
  onScroll();
  window.addEventListener("scroll", onScroll, { passive:true });

  const btn      = document.getElementById("navHamburger") || document.querySelector(".hamburger");
  const backdrop = document.getElementById("navBackdrop")  || document.querySelector(".nav-backdrop");
  const menu     = document.getElementById("mainMenu")     || topbar.querySelector(".menu");
  if(!btn || !menu) return;

  const MQ = window.matchMedia("(max-width: 940px)");
  const isMobile = ()=> MQ.matches;

  function openNav(){
    if(!isMobile()) return;
    document.body.classList.add("nav-open");
    topbar.classList.add("nav-open");
    btn.setAttribute("aria-expanded", "true");
  }

  function closeNav(){
    document.body.classList.remove("nav-open");
    topbar.classList.remove("nav-open");
    btn.setAttribute("aria-expanded", "false");
  }

  function toggleNav(e){
    if(e) e.preventDefault();
    document.body.classList.contains("nav-open") ? closeNav() : openNav();
  }

  btn.setAttribute("type", "button");
  if(!btn.getAttribute("aria-label")) btn.setAttribute("aria-label", "Abrir menú");
  btn.setAttribute("aria-expanded", btn.getAttribute("aria-expanded") || "false");

  btn.addEventListener("click", toggleNav);

  if(backdrop) backdrop.addEventListener("click", closeNav);

  menu.addEventListener("click", (e)=>{
    const a = e.target.closest("a");
    if(!a) return;
    closeNav();
  });

  document.addEventListener("keydown", (e)=>{
    if(e.key === "Escape") closeNav();
  });

  if(MQ.addEventListener){
    MQ.addEventListener("change", ()=>{ if(!isMobile()) closeNav(); });
  } else {
    window.addEventListener("resize", ()=>{ if(!isMobile()) closeNav(); }, { passive:true });
  }
})();


// ===== Carrusel principal HERO (infinito) =====
(function(){
  "use strict";
  const slides = [...document.querySelectorAll('.slide')];
  if(!slides.length) return;

  let i = 0;
  const show = x => slides.forEach((s,k)=> s.classList.toggle('active', k===x));
  show(i);

  setInterval(()=>{
    i = (i+1) % slides.length;
    show(i);
  }, 5000);
})();


// =====================================================================
// ✅ Carruseles FLEET: INFINITO + AVANZA 1 CARD (scroll horizontal)
// =====================================================================
(function(){
  "use strict";

  function initFleetInfinite(fleet){
    const track = fleet.querySelector('.fleet-track');
    const prev  = fleet.querySelector('.fleet-btn.prev');
    const next  = fleet.querySelector('.fleet-btn.next');
    if(!track || !prev || !next) return;

    if(track.dataset.infiniteReady === "1") return;
    track.dataset.infiniteReady = "1";

    const GAP_FALLBACK = 18;
    let autoSlide = null;
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

    const originalHTML = track.innerHTML;
    track.innerHTML = originalHTML + originalHTML;

    const cards = Array.from(track.querySelectorAll('.car-card'));
    const half  = Math.floor(cards.length / 2);

    function jumpToMiddle(){
      const step = getStepPx();
      track.scrollLeft = step * half;
    }
    requestAnimationFrame(()=> requestAnimationFrame(jumpToMiddle));

    function normalizeHard(){
      const step = getStepPx();
      const maxScroll = track.scrollWidth - track.clientWidth;

      if(track.scrollLeft <= step){
        track.scrollLeft += step * half;
        return;
      }
      if(track.scrollLeft >= (maxScroll - step)){
        track.scrollLeft -= step * half;
        return;
      }
    }

    function moveBy(dir){
      if(lock) return;
      lock = true;

      normalizeHard();

      const step = getStepPx();
      const before = track.scrollLeft;

      track.scrollBy({ left: dir * step, behavior: 'smooth' });

      window.setTimeout(()=>{
        normalizeHard();

        if(track.scrollLeft === before){
          normalizeHard();
          track.scrollBy({ left: dir * step, behavior: 'auto' });
          normalizeHard();
        }

        lock = false;
      }, 420);
    }

    function startAuto(){
      stopAuto();
      autoSlide = setInterval(()=> moveBy(1), 10000);
    }
    function stopAuto(){
      if(autoSlide) clearInterval(autoSlide);
      autoSlide = null;
    }

    next.addEventListener('click', (e)=>{ e.preventDefault(); stopAuto(); moveBy(1); startAuto(); });
    prev.addEventListener('click', (e)=>{ e.preventDefault(); stopAuto(); moveBy(-1); startAuto(); });

    track.addEventListener('scroll', ()=>{
      if(lock) return;
      normalizeHard();
    }, { passive:true });

    track.addEventListener('mouseenter', stopAuto);
    track.addEventListener('mouseleave', startAuto);

    window.addEventListener('resize', ()=>{
      requestAnimationFrame(()=> requestAnimationFrame(jumpToMiddle));
    }, { passive:true });

    startAuto();
  }

  document.addEventListener('DOMContentLoaded', ()=>{
    document.querySelectorAll('.fleet').forEach(initFleetInfinite);
  });
})();


// =====================================================================
// ✅ Carruseles de secciones: INFINITO + AVANZA 1 CARD
// (solo si NO son slides absolute/fade)
// =====================================================================
(function(){
  "use strict";

  const qsa = (s, root=document) => Array.from(root.querySelectorAll(s));

  function getStepPx(firstSlide, container){
    if(!firstSlide) return 0;
    const r = firstSlide.getBoundingClientRect();
    const s = getComputedStyle(firstSlide);

    const ml = parseFloat(s.marginLeft)  || 0;
    const mr = parseFloat(s.marginRight) || 0;

    const cs = getComputedStyle(container);
    const gap = parseFloat(cs.columnGap || cs.gap) || 0;

    return r.width + ml + mr + gap;
  }

  function initInfiniteOneByOne(wrap, idx){
    const viewport = wrap.querySelector('.media-viewport') || wrap;

    const slides = qsa('.media-slide', viewport);
    if(slides.length <= 1) return;

    const first = slides[0];
    const isAbsoluteFade = first && getComputedStyle(first).position === "absolute";
    if(isAbsoluteFade) return;

    if(wrap.dataset.infiniteReady === "1") return;
    wrap.dataset.infiniteReady = "1";

    const btnNext = wrap.querySelector('[data-mc="next"], .mc-next, .next, .btn-next');
    const btnPrev = wrap.querySelector('[data-mc="prev"], .mc-prev, .prev, .btn-prev');

    const base = Number(wrap.dataset.interval || 5000);
    const interval = base + (idx * 300);

    const originalHTML = viewport.innerHTML;
    viewport.innerHTML = originalHTML + originalHTML;

    const allSlides = qsa('.media-slide', viewport);
    const half = allSlides.length / 2;

    viewport.style.overflowX = 'auto';
    viewport.style.scrollBehavior = 'auto';
    viewport.style.webkitOverflowScrolling = 'touch';

    const step0 = getStepPx(allSlides[0], viewport);
    viewport.scrollLeft = step0 * half;

    let lock = false;
    let timer = null;

    function normalizeIfNeeded(){
      const step = getStepPx(allSlides[0], viewport);
      if(!step) return;

      const pos = viewport.scrollLeft;
      const leftLimit  = step * (half * 0.4);
      const rightLimit = step * (half * 1.6);

      if(pos < leftLimit){
        viewport.scrollBehavior = 'auto';
        viewport.scrollLeft = pos + (step * half);
      }
      if(pos > rightLimit){
        viewport.scrollBehavior = 'auto';
        viewport.scrollLeft = pos - (step * half);
      }
    }

    function moveBy(delta){
      if(lock) return;
      lock = true;

      const step = getStepPx(allSlides[0], viewport);
      if(!step){
        lock = false;
        return;
      }

      viewport.scrollBehavior = 'smooth';
      viewport.scrollLeft += (delta * step);

      window.setTimeout(()=>{
        viewport.scrollBehavior = 'auto';
        normalizeIfNeeded();
        lock = false;
      }, 420);
    }

    function stop(){
      if(timer) clearInterval(timer);
      timer = null;
    }

    function start(){
      stop();
      timer = setInterval(()=> moveBy(1), interval);
    }

    viewport.addEventListener('scroll', ()=>{
      if(lock) return;
      normalizeIfNeeded();
    }, { passive:true });

    if(btnNext){
      btnNext.addEventListener('click', (e)=>{
        e.preventDefault();
        stop();
        moveBy(1);
        start();
      });
    }
    if(btnPrev){
      btnPrev.addEventListener('click', (e)=>{
        e.preventDefault();
        stop();
        moveBy(-1);
        start();
      });
    }

    wrap.addEventListener('mouseenter', stop);
    wrap.addEventListener('mouseleave', start);

    window.addEventListener('resize', ()=>{
      const step = getStepPx(allSlides[0], viewport);
      if(!step) return;
      viewport.scrollBehavior = 'auto';
      viewport.scrollLeft = step * half;
    }, { passive:true });

    start();
  }

  document.addEventListener('DOMContentLoaded', ()=>{
    qsa('.media-carousel').forEach((wrap, idx)=> initInfiniteOneByOne(wrap, idx));
  });
})();


// ===== Año en footer =====
(function(){
  "use strict";
  const y = document.getElementById('year');
  if(y) y.textContent = new Date().getFullYear();
})();


// ===== Modal de bienvenida =====
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


// ===== Flatpickr FECHAS + TimepickerUI (RELOJ BONITO) + resumen =====
(function(){
  "use strict";

  const rangeSummary = document.getElementById('rangeSummary');

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

  // 2) HORAS (TimepickerUI)
  // ✅ Arreglos aquí:
  // - Espera REAL a que cargue el CDN
  // - Inicializa con wrapper correcto (según docs)
  // - Fuerza 24h y evita "keyboard" raro en móvil
  function resolveCtor(){
    if (typeof window.TimepickerUI === "function") return window.TimepickerUI;
    if (window.TimepickerUI && typeof window.TimepickerUI.TimepickerUI === "function") return window.TimepickerUI.TimepickerUI;
    if (window.Timepicker && typeof window.Timepicker === "function") return window.Timepicker;
    if (window.timepickerUI && typeof window.timepickerUI === "function") return window.timepickerUI;
    return null;
  }

  function waitForCtor(maxMs = 9000){
    return new Promise((resolve)=>{
      const start = performance.now();
      (function tick(){
        const Ctor = resolveCtor();
        if(Ctor) return resolve(Ctor);
        if(performance.now() - start >= maxMs) return resolve(null);
        setTimeout(tick, 120);
      })();
    });
  }

  function wrapTimepickerInput(input){
    // TimepickerUI suele esperar wrapper con class="timepicker-ui"
    // Si ya está envuelto, no lo dupliques.
    const parent = input.parentElement;
    if(parent && parent.classList && parent.classList.contains('timepicker-ui')) return parent;

    const wrap = document.createElement('div');
    wrap.className = 'timepicker-ui';            // ✅ clave
    input.parentNode.insertBefore(wrap, input);
    wrap.appendChild(input);
    return wrap;
  }

  async function initAnalogTime(id){
    const input = document.getElementById(id);
    if(!input) return;

    if(input.dataset.tpReady === "1") return;
    input.dataset.tpReady = "1";

    // Evita teclado nativo feo
    input.setAttribute("readonly", "readonly");
    input.setAttribute("inputmode", "none");

    // Asegura wrapper esperado por la lib
    const wrapper = wrapTimepickerInput(input);

    const Ctor = await waitForCtor(9000);

    if(!Ctor){
      console.warn("[TimepickerUI] No se encontró el constructor. Esto NO es del JS: el CDN no cargó o el layout no imprime @yield('js-vistaHome').");
      console.warn("[TimepickerUI] Debug:", {
        TimepickerUI: window.TimepickerUI,
        hasTimepickerUI: !!window.TimepickerUI,
        hasTimepicker: !!window.Timepicker,
        hasLower: !!window.timepickerUI
      });
      // Fallback limpio: placeholder 24h
      if(!input.value) input.placeholder = "18:00";
      return;
    }

    try{
      // Configuración estable (bonita en móvil)
      const tp = new Ctor(wrapper, {
        clockType: "24h",
        incrementMinuteBy: 15,

        // ✅ Esto evita que salga la opción "keyboard" (la pantalla rara)
        // En algunas versiones se llama enableSwitchIcon; en otras showKeyboardIcon.
        enableSwitchIcon: false,
        showKeyboardIcon: false
      });

      // Distintas builds exponen create() o init()
      if(typeof tp.create === "function") tp.create();
      else if(typeof tp.init === "function") tp.init();

      // Forzar formato 24h simple si viene vacío
      if(!input.value) input.value = "12:00";

      input.addEventListener("change", updateSummary);
      input.addEventListener("input", updateSummary);

      // iOS: asegurar foco sin teclado
      input.addEventListener("click", ()=>{
        input.blur();
        // muchas builds abren al click, pero si no:
        if(typeof tp.open === "function") tp.open();
      });
    } catch(err){
      console.error("[TimepickerUI] Falló la inicialización:", err);
    }
  }

  document.addEventListener("DOMContentLoaded", ()=>{
    initAnalogTime("pickupTime");
    initAnalogTime("dropoffTime");
    updateSummary();
  });

  // 3) Resumen (soporta 24h y AM/PM por si alguna vez llega)
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

    hh = Number.isFinite(hh) ? Math.max(0, Math.min(23, hh)) : 0;
    mm = Number.isFinite(mm) ? Math.max(0, Math.min(59, mm)) : 0;

    return { hh, mm };
  }

  function buildDT(dateId, timeId){
    const d = document.getElementById(dateId)?.value;
    const t = document.getElementById(timeId)?.value || '00:00';
    if(!d) return null;

    const [y, m, day] = d.split('-').map(Number);
    if(!y || !m || !day) return null;

    const { hh, mm } = parseTimeTo24h(t);
    return new Date(y, m - 1, day, hh, mm);
  }

  function updateSummary(){
    if(!rangeSummary) return;

    const s = buildDT('pickupDate','pickupTime');
    const e = buildDT('dropoffDate','dropoffTime');
    if(!s || !e){ rangeSummary.textContent=''; return; }

    const h = Math.round((e - s) / 36e5);
    const d = Math.ceil(h / 24);
    rangeSummary.textContent = `Renta por ${d} día(s) · ~${h} hora(s)`;
  }
})();


// ====================
// ✅ BURBUJA DE REDES SOCIALES (RADIAL)
// ====================
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
