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
    LIMPIAR ERRORES AL INTERACTUAR
============================================================ */
document.addEventListener('DOMContentLoaded', function() {
    const inputs = document.querySelectorAll('input, select');
    const hourSelects = document.querySelectorAll('.tp-selects .tp-hour');
    const flatpickrInputs = document.querySelectorAll('.flatpickr-input');

    // Limpiar errores de inputs normales
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

    // Limpiar errores de los selects de hora
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

    // Limpiar errores de los inputs de flatpickr (solo los visibles)
    flatpickrInputs.forEach(input => {
        // Solo procesar inputs que NO son hidden
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
    DEPURACIÓN - Ver qué clases se están aplicando
============================================================ */
document.addEventListener('DOMContentLoaded', function() {
    // Observar cambios en las clases
    const observer = new MutationObserver(function(mutations) {
        mutations.forEach(function(mutation) {
            if (mutation.attributeName === 'class') {
                console.log('Cambio de clase:', mutation.target, mutation.target.className);
            }
        });
    });

    // Observar los altInputs de flatpickr
    setTimeout(function() {
        document.querySelectorAll('.flatpickr-input').forEach(function(input) {
            if (input.type !== 'hidden') {
                observer.observe(input, { attributes: true });
                console.log('Observando:', input);
            }
        });
    }, 1000);
});

/* ============================================================
    CONTROL DE SCROLL PARA FORMULARIO MÓVIL/TABLET
============================================================ */
(function() {
    "use strict";

    function initScrollControl() {
        const btnAbrir = document.getElementById('btn-abrir-buscador');
        const btnCerrar = document.getElementById('btn-cerrar-buscador');
        const buscador = document.getElementById('miBuscador');

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
            window.scrollTo(0, parseInt(scrollY));

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

        // Opcional: Prevenir scroll con teclado
        window.addEventListener('keydown', function(e) {
            if (buscador.classList.contains('active')) {
                if (e.key === 'ArrowDown' || e.key === 'ArrowUp' || e.key === ' ' || e.key === 'Spacebar') {
                    e.preventDefault();
                }
            }
        }, { passive: false });


        document.body.addEventListener('touchmove', function(e) {
            if (buscador.classList.contains('active')) {
                e.preventDefault();
            }
        }, { passive: false });
    }
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initScrollControl);
    } else {
        initScrollControl();
    }
})();
