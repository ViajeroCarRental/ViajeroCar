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


// ===== Navbar: glass -> sólida + hamburguesa =====
(function(){
  "use strict";
  const topbar = document.querySelector('.topbar');

  function onScroll(){
    if(!topbar) return;
    if(window.scrollY > 40) topbar.classList.add('solid');
    else topbar.classList.remove('solid');
  }

  onScroll();
  window.addEventListener('scroll', onScroll, { passive: true });

  const btn  = document.querySelector('.hamburger');
  const menu = document.querySelector('.menu');
  if(btn && menu){
    btn.addEventListener('click', ()=>{
      const visible = getComputedStyle(menu).display !== 'none';
      menu.style.display = visible ? 'none' : 'flex';
      if(!visible){
        menu.style.flexDirection = 'column';
        menu.style.gap = '14px';
      }
    });
  }
})();


// ===== Carrusel principal HERO (tu versión, infinito) =====
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
// ✅ Carruseles de secciones: INFINITO + AVANZA 1 CARD (NO por página)
// - Funciona con overflow-x (scroll) duplicando contenido
// - Botones soportados:
//   Dentro de .media-carousel: [data-mc="next"] / [data-mc="prev"]
//   o clases: .mc-next .mc-prev .next .prev .btn-next .btn-prev
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

    // gap real si es flex
    const cs = getComputedStyle(container);
    const gap = parseFloat(cs.columnGap || cs.gap) || 0;

    return r.width + ml + mr + gap;
  }

  function initInfiniteOneByOne(wrap, idx){
    // El "viewport" donde se scrollea: si tu wrap ya es el scroll container, usamos wrap
    // Si tienes un inner tipo .media-viewport, también lo soporta:
    const viewport = wrap.querySelector('.media-viewport') || wrap;

    const slides = qsa('.media-slide', viewport);
    if(slides.length <= 1) return;

    // Evitar doble init
    if(wrap.dataset.infiniteReady === "1") return;
    wrap.dataset.infiniteReady = "1";

    // Botones (si existen)
    const btnNext = wrap.querySelector('[data-mc="next"], .mc-next, .next, .btn-next');
    const btnPrev = wrap.querySelector('[data-mc="prev"], .mc-prev, .prev, .btn-prev');

    // Intervalo
    const base = Number(wrap.dataset.interval || 5000);
    const interval = base + (idx * 300);

    // ✅ Duplicar contenido para loop infinito “seamless”
    // Guardamos HTML original (solo 1 vez)
    const originalHTML = viewport.innerHTML;

    // Para evitar que duplicar duplique botones o cosas raras:
    // asumimos que dentro del viewport SOLO van slides.
    // Si tu wrap contiene botones, está bien (porque duplicamos viewport, no wrap).
    viewport.innerHTML = originalHTML + originalHTML;

    // Re-leer slides ya duplicadas
    const allSlides = qsa('.media-slide', viewport);
    const half = allSlides.length / 2;

    // Asegurar que el viewport sea scroll horizontal (si tu CSS ya lo hace, no afecta)
    viewport.style.overflowX = viewport.style.overflowX || 'auto';
    viewport.style.scrollBehavior = 'auto'; // lo controlamos nosotros
    viewport.style.webkitOverflowScrolling = 'touch';

    // Posicionar al inicio de la “segunda mitad” para poder ir atrás/adelante infinito
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

    // Scroll manual (rueda/touch)
    viewport.addEventListener('scroll', ()=>{
      if(lock) return;
      normalizeIfNeeded();
    }, { passive:true });

    // Clicks
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

    // Hover pause
    wrap.addEventListener('mouseenter', stop);
    wrap.addEventListener('mouseleave', start);

    // Resize: re-centra
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


// ===== Flatpickr fechas / horas + resumen =====
(function(){
  "use strict";
  const rangeSummary = document.getElementById('rangeSummary');

  if(window.flatpickr){
    flatpickr('#pickupDate', {
      locale: 'es',
      altInput: true,
      altFormat: 'd/m/Y',
      dateFormat: 'Y-m-d',
      minDate: 'today',
      plugins: typeof rangePlugin !== 'undefined'
        ? [ new rangePlugin({ input: '#dropoffDate' }) ]
        : [],
      onChange: updateSummary
    });

    flatpickr('#pickupTime', {
      enableTime:true, noCalendar:true,
      dateFormat:'h:i K', minuteIncrement:5,
      onChange:updateSummary
    });

    flatpickr('#dropoffTime', {
      enableTime:true, noCalendar:true,
      dateFormat:'h:i K', minuteIncrement:5,
      onChange:updateSummary
    });
  }

  function buildDT(dateId, timeId){
    const d = document.getElementById(dateId)?.value;
    const t = document.getElementById(timeId)?.value || '00:00';
    if(!d) return null;

    const [y,m,day] = d.split('-').map(Number);
    let [hh,mm] = t.replace(/( am| pm)/i,'').split(':').map(Number);

    if(/pm/i.test(t) && hh !== 12) hh += 12;
    if(/am/i.test(t) && hh === 12) hh = 0;

    return new Date(y, m-1, day, hh, mm);
  }

  function updateSummary(){
    if(!rangeSummary) return;
    const s = buildDT('pickupDate','pickupTime');
    const e = buildDT('dropoffDate','dropoffTime');
    if(!s || !e){ rangeSummary.textContent=''; return; }

    const h = Math.round((e-s)/36e5);
    const d = Math.ceil(h/24);
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
