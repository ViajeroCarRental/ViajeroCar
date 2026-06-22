/* =====================================================================
 *  contacto.js — UI-only (sin lógica de negocio)
 *
 *  - Topbar: clase .solid al hacer scroll
 *  - Menú hamburguesa: toggle accesible
 *  - Link activo en el menú
 *  - Año en el footer
 *  - Contador de caracteres del textarea
 * ===================================================================== */
(function () {
  "use strict";

  const qs  = (s, r = document) => r.querySelector(s);
  const qsa = (s, r = document) => [...r.querySelectorAll(s)];

  /* =================================================================
     TOPBAR: clase .solid al hacer scroll
     - Cachea el estado para evitar add/remove en cada pixel de scroll
     ================================================================= */
  (function initTopbar() {
    const topbar = qs('.topbar');
    if (!topbar) return;

    let isSolid = false;
    function update() {
      const shouldBeSolid = window.scrollY > 40;
      if (shouldBeSolid !== isSolid) {
        topbar.classList.toggle('solid', shouldBeSolid);
        isSolid = shouldBeSolid;
      }
    }
    update();
    window.addEventListener('scroll', update, { passive: true });
  })();

  /* =================================================================
     MENÚ HAMBURGUESA: toggle accesible
     - Usa classList en lugar de styles inline
     - Cierra al hacer click fuera o presionar ESC
     - Cierra al hacer click en cualquier link del menú
     ================================================================= */
  (function initHamburger() {
    const hamburger = qs('.hamburger');
    const menu      = qs('.menu');
    if (!hamburger || !menu) return;

    hamburger.setAttribute('aria-expanded', 'false');
    hamburger.setAttribute('aria-controls', menu.id || 'mainMenu');
    if (!menu.id) menu.id = 'mainMenu';

    function setOpen(open) {
      menu.classList.toggle('is-open', open);
      hamburger.setAttribute('aria-expanded', open ? 'true' : 'false');
    }

    hamburger.addEventListener('click', (e) => {
      e.stopPropagation();
      setOpen(!menu.classList.contains('is-open'));
    });

    // Cerrar al hacer click en cualquier link del menú
    qsa('a', menu).forEach(a => a.addEventListener('click', () => setOpen(false)));

    // Cerrar al hacer click fuera del menú
    document.addEventListener('click', (e) => {
      if (!menu.classList.contains('is-open')) return;
      if (!menu.contains(e.target) && !hamburger.contains(e.target)) setOpen(false);
    });

    // Cerrar con ESC
    document.addEventListener('keydown', (e) => {
      if (e.key === 'Escape' && menu.classList.contains('is-open')) setOpen(false);
    });
  })();

  /* =================================================================
     LINK ACTIVO en el menú
     - Comparación robusta usando pathname normalizado de Laravel
     - Soporta rutas con o sin trailing slash
     ================================================================= */
  (function markActive() {
    const links = qsa('.menu a');
    if (!links.length) return;

    const currentPath = location.pathname.replace(/\/+$/, '').toLowerCase() || '/';

    links.forEach(a => {
      const href = a.getAttribute('href') || '';
      // Extraer solo el pathname del href (puede ser absoluto o relativo)
      let linkPath;
      try {
        linkPath = new URL(href, location.origin).pathname.replace(/\/+$/, '').toLowerCase() || '/';
      } catch {
        linkPath = href.toLowerCase();
      }
      a.classList.toggle('active', linkPath === currentPath);
    });
  })();

  /* =================================================================
     AÑO EN EL FOOTER
     ================================================================= */
  (function initYear() {
    const yearEl = qs('#year');
    if (yearEl) yearEl.textContent = new Date().getFullYear();
  })();

  /* =================================================================
     CONTADOR DE CARACTERES del textarea
     ================================================================= */
  (function initCharCounter() {
    const messageEl = qs('#fMessage');
    const counterEl = qs('#charCount');
    if (!messageEl || !counterEl) return;

    const syncCount = () => {
      counterEl.textContent = (messageEl.value || '').length;
    };

    messageEl.addEventListener('input', syncCount);
    syncCount();
  })();
})();
