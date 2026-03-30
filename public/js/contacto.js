// UI-only script (sin lógica de negocio / sin side-effects de datos)
(function () {
  // Helpers
  const qs  = s => document.querySelector(s);
  const qsa = s => [...document.querySelectorAll(s)];

  // ----- Topbar: estilo sólido al hacer scroll (puro visual)
  const topbar = qs('.topbar');
  function toggleTopbar() {
    if (!topbar) return;
    window.scrollY > 40 ? topbar.classList.add('solid') : topbar.classList.remove('solid');
  }
  toggleTopbar();
  window.addEventListener('scroll', toggleTopbar, { passive: true });

  // ----- Menú hamburguesa (mostrar/ocultar visual)
  const hamburger = qs('.hamburger');
  const menu = qs('.menu');
  if (hamburger && menu) {
    hamburger.addEventListener('click', () => {
      const isHidden = getComputedStyle(menu).display === 'none';
      menu.style.display = isHidden ? 'flex' : 'none';
      if (isHidden) {
        menu.style.flexDirection = 'column';
        menu.style.gap = '12px';
      }
    });
  }

  // ----- Marcar link activo según la página (estético)
  (function markActive() {
    const current = (location.pathname.split('/').pop() || 'inicio.html').toLowerCase();
    qsa('.menu a').forEach(a => {
      const href = (a.getAttribute('href') || '').toLowerCase();
      a.classList.toggle('active', href === current);
    });
  })();

  // ----- Año en el footer (visual)
  const yearEl = qs('#year');
  if (yearEl) yearEl.textContent = new Date().getFullYear();

  // ----- Contador de caracteres del mensaje (visual)
  const messageEl = qs('#fMessage');
  const counterEl = qs('#charCount');
  function syncCount() {
    if (!messageEl || !counterEl) return;
    counterEl.textContent = (messageEl.value || '').length;
  }
  if (messageEl && counterEl) {
    messageEl.addEventListener('input', syncCount);
    syncCount();
  }

  // ----- Toast (solo UI; con traducciones)
  window.showToast = function (msg) {
    // Si no se pasa mensaje, usar traducción por defecto
    const defaultMsg = document.documentElement.lang === 'en'
      ? 'Message sent! We will contact you shortly.'
      : '¡Mensaje enviado! Te contactaremos muy pronto.';

    const toast = qs('#toast');
    if (!toast) return;
    toast.innerHTML = `<i class="fa-solid fa-check"></i> ${msg || defaultMsg}`;
    toast.classList.add('show');
    setTimeout(() => toast.classList.remove('show'), 3200);
  };

  // 🚫 Removido:
  // - VJ_AUTH / localStorage / syncAccountIcon
  // - window.addEventListener('storage', ...)
  // - WhatsApp (buildWA, waFromForm, window.open)
  // - Prefill desde auth
  // - submit de #contactForm, validaciones y formToJSON
})();
