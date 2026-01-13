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


// ===== Carrusel principal HERO =====
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


// ===== Carruseles de secciones =====
(function(){
  "use strict";
  document.querySelectorAll('.media-carousel').forEach((wrap, idx)=>{
    const items = [...wrap.querySelectorAll('.media-slide')];
    if(items.length <= 1) return;

    const base = Number(wrap.dataset.interval || 5000);
    let i = 0;

    const show = x => items.forEach((el,k)=> el.classList.toggle('active', k===x));
    show(i);

    setInterval(()=>{
      i = (i+1) % items.length;
      show(i);
    }, base + (idx * 300));
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
    const rangePicker = flatpickr('#pickupDate', {
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
