(function () {
  const AUTH_KEY = 'vj_auth';
  const URLS = { LOGIN: 'login.html', PROFILE: 'perfil.html' };

  if (!window.VJ_AUTH) {
    function getAuth() {
      try { return JSON.parse(localStorage.getItem(AUTH_KEY) || 'null'); }
      catch (e) { return null; }
    }
    function isLogged() { return !!localStorage.getItem(AUTH_KEY); }
    window.VJ_AUTH = { getAuth, isLogged, URLS };
  }

  function onReady(fn) {
    if (document.readyState === 'loading') {
      document.addEventListener('DOMContentLoaded', fn, { once: true });
    } else {
      fn();
    }
  }

  onReady(() => {
    // --- Navbar / usuario ---
    function syncAccountIcon() {
      const link = document.getElementById('accountLink');
      if (!link) return;
      if (window.VJ_AUTH.isLogged()) {
        const u = window.VJ_AUTH.getAuth() || {};
        link.href = URLS.PROFILE;
        link.title = 'Mi perfil';
        link.innerHTML = `<span class="avatar-mini">${(u.name?.[0] || u.email?.[0] || 'U').toUpperCase()}</span>`;
      } else {
        link.href = URLS.LOGIN;
        link.title = 'Iniciar sesión';
        link.innerHTML = '<i class="fa-regular fa-user"></i>';
      }
    }
    syncAccountIcon();
    window.addEventListener('storage', e => { if (e.key === AUTH_KEY) syncAccountIcon(); });

    const topbar = document.getElementById('topbar');
    function toggleTopbar() {
      if (!topbar) return;
      window.scrollY > 40 ? topbar.classList.add('solid') : topbar.classList.remove('solid');
    }
    toggleTopbar();
    window.addEventListener('scroll', toggleTopbar, { passive: true });

    const hamburger = document.getElementById('hamburger');
    const mainMenu = document.getElementById('mainMenu');
    if (hamburger && mainMenu) {
      hamburger.addEventListener('click', () => {
        const open = getComputedStyle(mainMenu).display === 'none';
        mainMenu.style.display = open ? 'flex' : 'none';
        if (open) { mainMenu.style.flexDirection = 'column'; mainMenu.style.gap = '12px'; }
      });
    }

    (function markActive() {
      const current = (location.pathname.split('/').pop() || 'inicio.html').toLowerCase();
      document.querySelectorAll('.menu a').forEach(a => {
        const href = (a.getAttribute('href') || '').toLowerCase();
        a.classList.toggle('active', href === current);
      });
    })();

    const yearEl = document.getElementById('year');
    if (yearEl) yearEl.textContent = new Date().getFullYear();

    // --- Acordeones ---
    function setupAccordion(containerSelector, itemSelector, headSelector, bodySelector, contentSelector) {
      const root = document.querySelector(containerSelector) || document;
      const items = root.querySelectorAll(itemSelector);
      if (!items.length) return; // Nada que hacer

      items.forEach(item => {
        const head = item.querySelector(headSelector);
        const body = item.querySelector(bodySelector);
        const content = contentSelector ? item.querySelector(contentSelector) : body?.firstElementChild;

        if (body) {
          body.style.overflow = 'hidden';
          body.style.maxHeight = '0px';
          body.setAttribute('aria-hidden', 'true');
        }
        if (head) head.setAttribute('aria-expanded', 'false');

        head && head.addEventListener('click', () => {
          const isOpen = item.classList.contains('open');

          // Cerrar otras
          items.forEach(it => {
            if (it !== item) {
              it.classList.remove('open');
              const b = it.querySelector(bodySelector);
              const h = it.querySelector(headSelector);
              if (b) { b.style.maxHeight = '0px'; b.setAttribute('aria-hidden', 'true'); }
              if (h) { h.setAttribute('aria-expanded', 'false'); }
            }
          });

          // Abrir/cerrar la actual
          if (!isOpen) {
            item.classList.add('open');
            if (body && content) body.style.maxHeight = content.scrollHeight + 'px';
            head?.setAttribute('aria-expanded', 'true');
            body?.setAttribute('aria-hidden', 'false');
          } else {
            item.classList.remove('open');
            if (body) body.style.maxHeight = '0px';
            head?.setAttribute('aria-expanded', 'false');
            body?.setAttribute('aria-hidden', 'true');
          }
        });

        // Ajuste dinámico si el contenido cambia
        if (content && body && 'ResizeObserver' in window) {
          const ro = new ResizeObserver(() => {
            if (item.classList.contains('open')) body.style.maxHeight = content.scrollHeight + 'px';
          });
          ro.observe(content);
        }
      });
    }

    // Importante: estos selectores existen en TU HTML
    setupAccordion('.policies-wrap', '.policy-item', '.policy-head', '.policy-body', '.policy-content');
    setupAccordion('#renta-accordion', '.sub-item', '.sub-head', '.sub-body', '.sub-content');
  });
})();
