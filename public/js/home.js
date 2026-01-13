// ====================
// UI / PRESENTACIÓN SOLAMENTE
// ====================

// ===== Icono de cuenta (solo presentación) =====
// Muestra avatar con inicial si el backend inyecta data-auth-name/email en #accountLink.
// No cambia href ni consulta localStorage (el servidor decide si hay sesión).
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

  // Menú móvil (hamburguesa)
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


// ===== Carrusel principal (slides ya renderizados por Blade) =====
(function(){
  "use strict";
  const slides = [...document.querySelectorAll('.slide')];
  if(!slides.length) return;
  let i = 0;
  const show = x => slides.forEach((s,k)=> s.classList.toggle('active', k===x));
  setInterval(()=>{ i = (i+1) % slides.length; show(i); }, 5000);
  show(i);
})();


// ===== Carruseles de secciones (.media-carousel) =====
// Usa data-interval en el contenedor si deseas personalizar.
(function(){
  "use strict";
  document.querySelectorAll('.media-carousel').forEach((wrap, idx)=>{
    const items = [...wrap.querySelectorAll('.media-slide')];
    if(items.length <= 1) return;
    const base = Number(wrap.dataset.interval || 5000);
    let i = 0;
    const show = x => items.forEach((el,k)=> el.classList.toggle('active', k===x));
    setInterval(()=>{ i = (i+1) % items.length; show(i); }, base + (idx * 300));
    show(i);
  });
})();


// ===== Año en footer =====
(function(){
  "use strict";
  const y = document.getElementById('year');
  if(y) y.textContent = new Date().getFullYear();
})();


// ===== Modal de bienvenida (solo visual) =====
// El backend puede activar con data-auto-show="1" y data-name en #welcomeModal.
// Compatibilidad opcional con sessionStorage (si ya lo usabas).
(function(){
  "use strict";
  const modal  = document.getElementById('welcomeModal');
  if(!modal) return;

  const nameEl   = document.getElementById('wmName');
  const closeBtn = document.getElementById('wmClose');
  const okBtn    = document.getElementById('wmOk');

  function open(){ modal.classList.add('show'); }
  function close(){ modal.classList.remove('show'); }

  closeBtn?.addEventListener('click', close);
  modal.querySelector('.modal-backdrop')?.addEventListener('click', close);
  okBtn?.addEventListener('click', close);

  const shouldShow = modal.getAttribute('data-auto-show') === '1';
  const whoData    = modal.getAttribute('data-name');

  const ssShould   = sessionStorage.getItem('vj_welcome_home') === '1';
  const ssWho      = sessionStorage.getItem('vj_welcome_name');

  const should = shouldShow || ssShould;
  const who    = (whoData || ssWho || '').trim();

  if(should){
    if(nameEl && who) nameEl.textContent = who;
    open();
    if(ssShould){
      sessionStorage.removeItem('vj_welcome_home');
      sessionStorage.removeItem('vj_welcome_name');
    }
  }
})();


// ===== Flatpickr en fechas/horas + resumen visual =====
// No hay validación de negocio ni submit custom; el form lo maneja Laravel.
(function(){
  "use strict";
  const rangeSummary = document.getElementById('rangeSummary');
  const pickupPlace  = document.getElementById('pickupPlace');
  const dropoffPlace = document.getElementById('dropoffPlace');

  // Conveniencia UI: copiar pickup → dropoff si existe la misma opción
  pickupPlace?.addEventListener('change', (e)=>{
    if(!dropoffPlace) return;
    const value = e.target.value;
    for(const opt of dropoffPlace.options){
      if(opt.value === value){ dropoffPlace.value = value; break; }
    }
  });

  // Inicializa Flatpickr si está cargado
  if(window.flatpickr){
    // 1) Picker de rango: #pickupDate controla también #dropoffDate (via rangePlugin)
    const rangePicker = flatpickr('#pickupDate', {
      locale: 'es',
      altInput: true,
      altFormat: 'd/m/Y',
      dateFormat: 'Y-m-d',
      // minDate visual; si depende del negocio, pásalo como data-* en el input
      minDate: document.getElementById('pickupDate')?.dataset.min || 'today',
      plugins: (typeof rangePlugin !== 'undefined') ? [ new rangePlugin({ input: '#dropoffDate' }) ] : [],
      onChange: updateSummary
    });

    // 2) Asegura que al clicar/enfocar "Devolución" se abra el mismo calendario
    const dropReal  = document.getElementById('dropoffDate');
    const dropAlt   = dropReal?.nextElementSibling?.classList.contains('flatpickr-input') ? dropReal.nextElementSibling : null;
    const openRange = ()=> { try{ rangePicker.open(); }catch(_){} };

    dropReal?.addEventListener('focus', openRange);
    dropReal?.addEventListener('click',  openRange);
    dropAlt?.addEventListener('focus',   openRange);
    dropAlt?.addEventListener('click',   openRange);

    // 3) Horas (solo UI)
    flatpickr('#pickupTime', {
      enableTime: true, noCalendar: true,
      dateFormat: 'h:i K', time_24hr: false, minuteIncrement: 5,
      onChange: updateSummary
    });
    flatpickr('#dropoffTime', {
      enableTime: true, noCalendar: true,
      dateFormat: 'h:i K', time_24hr: false, minuteIncrement: 5,
      onChange: updateSummary
    });
  }

  // —— Helpers visuales ——
  function parseTimeTo24(timeStr){
    if(!timeStr) return { hh:0, mm:0 };
    timeStr = timeStr.trim();
    const ampm = /(am|pm)$/i.test(timeStr) ? timeStr.slice(-2).toLowerCase() : null;
    const core = ampm ? timeStr.replace(/\s?(am|pm)$/i,'') : timeStr;
    const [hStr,mStr] = core.split(':');
    let hh = Number(hStr||0), mm = Number(mStr||0);
    if(ampm === 'am'){ if(hh === 12) hh = 0; }
    else if(ampm === 'pm'){ if(hh !== 12) hh += 12; }
    return { hh, mm };
  }

  function buildDateTime(dateInputId, timeInputId){
    const dateEl = document.getElementById(dateInputId);
    const timeEl = document.getElementById(timeInputId);
    if(!dateEl || !dateEl.value) return null;
    const [y,m,d] = dateEl.value.split('-').map(Number);
    const { hh, mm } = parseTimeTo24((timeEl?.value || '00:00').trim());
    return new Date(y, (m||1)-1, d||1, hh, mm, 0, 0);
  }

  function updateSummary(){
    if(!rangeSummary) return;
    const s = buildDateTime('pickupDate','pickupTime');
    const e = buildDateTime('dropoffDate','dropoffTime');
    if(!s || !e){ rangeSummary.textContent = ''; return; }
    const ms = e - s;
    if(ms < 0){ rangeSummary.textContent = 'La devolución no puede ser anterior a la entrega.'; return; }
    const h = Math.round(ms / 36e5);
    const d = Math.ceil(h / 24);
    rangeSummary.textContent = `Renta por ${d} día(s) · ~${h} hora(s)`;
  }

  // Reaccionar a cambios manuales del navegador
  ['pickupDate','pickupTime','dropoffDate','dropoffTime'].forEach(id=>{
    const el = document.getElementById(id);
    el?.addEventListener('change', updateSummary);
    el?.addEventListener('input',  updateSummary);
  });
})();
// ====================
// Social bar hide after HERO / show on FOOTER
// ====================
(function () {
  "use strict";

  const social = document.getElementById("socialBar");
  const hero = document.querySelector(".hero");
  const footer = document.querySelector(".site-footer"); // <-- ajusta si tu footer usa otro selector
  if (!social || !hero) return;

  // estados iniciales
  social.classList.add("is-show");

  // helper: saber si un elemento está "entrando" en viewport
  function isInViewport(el, offset = 0) {
    if (!el) return false;
    const r = el.getBoundingClientRect();
    return (r.top <= (window.innerHeight - offset)) && (r.bottom >= 0);
  }

  function updateSocial() {
    // Si el footer existe y ya está visible -> mostrar
    if (footer && isInViewport(footer, 120)) {
      social.classList.remove("is-hidden");
      social.classList.add("is-show");
      return;
    }

    // Si ya pasaste el final del hero -> esconder (meter al margen)
    const heroBottom = hero.getBoundingClientRect().bottom;
    const passedHero = heroBottom <= 80; // umbral
    if (passedHero) {
      social.classList.remove("is-show");
      social.classList.add("is-hidden");
    } else {
      // dentro del hero -> visible
      social.classList.remove("is-hidden");
      social.classList.add("is-show");
    }
  }

  updateSocial();
  window.addEventListener("scroll", updateSocial, { passive: true });
  window.addEventListener("resize", updateSocial);
})();
