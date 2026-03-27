// public/js/Login.js
document.addEventListener('DOMContentLoaded', () => {
  /* ================================
     🧭 TOPBAR SCROLL EFFECT
  ================================= */
  const topbar = document.getElementById('topbar');
  function toggleTopbar() {
    if (!topbar) return;
    if (window.scrollY > 40) topbar.classList.add('solid');
    else topbar.classList.remove('solid');
  }
  if (topbar) {
    toggleTopbar();
    window.addEventListener('scroll', toggleTopbar, { passive: true });
  }

  /* ================================
     📆 FOOTER YEAR AUTO
  ================================= */
  const yearEl = document.getElementById('year');
  if (yearEl) yearEl.textContent = new Date().getFullYear();

  /* ================================
     🍔 MENÚ HAMBURGUESA
  ================================= */
  const hamburger = document.getElementById('hamburger');
  const mainMenu = document.getElementById('mainMenu');
  if (hamburger && mainMenu) {
    hamburger.addEventListener('click', () => {
      const open = getComputedStyle(mainMenu).display === 'none';
      mainMenu.style.display = open ? 'flex' : 'none';
      if (open) {
        mainMenu.style.flexDirection = 'column';
        mainMenu.style.gap = '12px';
      }
    });
  }

  /* ================================
     🔄 PESTAÑAS LOGIN / REGISTRO
  ================================= */
  const seg = document.getElementById('tabs');
  if (seg) {
    const buttons = seg.querySelectorAll('.seg-btn');
    const slider = seg.querySelector('.seg-slider');
    const panels = document.querySelectorAll('.auth-panel');

    function setActive(idx) {
      buttons.forEach((b, i) => b.classList.toggle('active', i === idx));
      panels.forEach(p => p.classList.remove('show'));
      const targetSel = buttons[idx]?.dataset?.target;
      const target = targetSel ? document.querySelector(targetSel) : null;
      if (target) target.classList.add('show');
      if (slider) slider.style.transform = `translateX(${idx * 100}%)`;
    }

    buttons.forEach((b, i) => b.addEventListener('click', () => setActive(i)));
    setActive(0);
  }

  /* ================================
     👁️ MOSTRAR / OCULTAR CONTRASEÑA
  ================================= */
  document.querySelectorAll('.eye').forEach(btn => {
    btn.addEventListener('click', () => {
      const sel = btn.getAttribute('data-target');
      const inp = sel ? document.querySelector(sel) : null;
      if (!inp) return;
      const newType = inp.type === 'password' ? 'text' : 'password';
      inp.type = newType;
      btn.innerHTML =
        newType === 'password'
          ? '<i class="fa-regular fa-eye"></i>'
          : '<i class="fa-regular fa-eye-slash"></i>';
    });
  });

  /* ================================
     ✨ PLACEHOLDERS FLOTANTES
  ================================= */
  document.querySelectorAll('.field input').forEach(inp => {
    if (!inp.hasAttribute('placeholder')) inp.setAttribute('placeholder', ' ');
  });

  /* ================================
     📧 MODAL DE VERIFICACIÓN
  ================================= */
  const verifyModal = document.getElementById('verifyModal');
  const vClose = document.getElementById('vClose');
  const verifyEmail = document.getElementById('verifyEmail');
  const verifyEmailHidden = document.getElementById('verifyEmailHidden');
  const btnResend = document.getElementById('btnResend');
  const resendTimer = document.getElementById('resendTimer');

  // 🔹 Abrir modal (backend controlado)
  function openVerifyModal(email = '') {
    if (!verifyModal) return;
    if (email) {
      verifyEmail.textContent = email;
      verifyEmailHidden.value = email;
    }
    verifyModal.style.display = 'flex';
    verifyModal.setAttribute('aria-hidden', 'false');
    document.body.style.overflow = 'hidden';
    startTimer(); // Inicia contador de 30s
  }

  // 🔹 Cerrar modal
  function closeVerifyModal() {
    if (!verifyModal) return;
    verifyModal.style.display = 'none';
    verifyModal.setAttribute('aria-hidden', 'true');
    document.body.style.overflow = '';
  }

  // 🔹 Botones de cerrar
  vClose?.addEventListener('click', closeVerifyModal);
  verifyModal?.querySelector('.modal-backdrop')?.addEventListener('click', closeVerifyModal);

  /* ================================
     ⏳ TEMPORIZADOR REENVIAR CÓDIGO
  ================================= */
  let seconds = 30;
  function startTimer() {
    seconds = 30;
    if (btnResend) btnResend.disabled = true;
    if (resendTimer) resendTimer.textContent = `(${seconds}s)`;
    const timer = setInterval(() => {
      seconds--;
      if (resendTimer) resendTimer.textContent = `(${seconds}s)`;
      if (seconds <= 0) {
        clearInterval(timer);
        if (btnResend) btnResend.disabled = false;
        if (resendTimer) resendTimer.textContent = '';
      }
    }, 1000);
  }

  /* ================================
     🔢 INPUTS DE CÓDIGO (UX)
  ================================= */
  const codeInputs = Array.from(document.querySelectorAll('#codeInputs input'));
  codeInputs.forEach((inp, idx) => {
    inp.addEventListener('input', () => {
      inp.value = inp.value.replace(/\D/g, '').slice(0, 1);
      if (inp.value && idx < codeInputs.length - 1) codeInputs[idx + 1].focus();
    });
    inp.addEventListener('keydown', e => {
      if (e.key === 'Backspace' && !inp.value && idx > 0) codeInputs[idx - 1].focus();
    });
  });

  /* ================================
   💪 BARRA DE FORTALEZA DE CONTRASEÑA
================================= */
const passField = document.getElementById('rPass');
const passStrength = document.getElementById('passStrength');
const strengthLabel = document.getElementById('strengthLabel');
if (passField && passStrength && strengthLabel) {
  passField.addEventListener('input', () => {
    const val = passField.value;
    let lvl = 0;
    if (val.length >= 8) lvl++;
    if (/[A-Z]/.test(val)) lvl++;
    if (/[a-z]/.test(val)) lvl++;
    if (/\d/.test(val) || /[^\w\s]/.test(val)) lvl++;
    passStrength.querySelectorAll('span').forEach((sp, i) =>
      sp.classList.toggle('active', i < lvl)
    );

    // Traducciones según idioma
    const locale = document.documentElement.lang || 'es';
    const strengthText = locale === 'en' ? 'Strength: ' : 'Fortaleza: ';
    const labels = locale === 'en'
      ? ['—', 'Weak', 'Medium', 'Good', 'Strong']
      : ['—', 'Débil', 'Media', 'Buena', 'Fuerte'];

    strengthLabel.textContent = strengthText + labels[lvl];
  });
}

/* ================================
   🔔 ALERTIFY MENSAJES (BILINGÜE)
================================= */
if (typeof alertify !== 'undefined') {
  const locale = document.documentElement.lang || 'es';
  alertify.defaults.glossary.title = locale === 'en' ? 'Notification' : 'Notificación';
  alertify.defaults.glossary.ok = locale === 'en' ? 'OK' : 'Aceptar';
  alertify.defaults.glossary.cancel = locale === 'en' ? 'Cancel' : 'Cancelar';
  alertify.defaults.glossary.close = locale === 'en' ? 'Close' : 'Cerrar';
  alertify.defaults.notifier.position = 'top-center';
}
  /* ================================
     🎯 MOSTRAR MODAL AUTOMÁTICAMENTE
     SI EL BACKEND LO INDICA
  ================================= */
  const modalData = document.querySelector('meta[name="show-modal"]');
  if (modalData && modalData.content === 'true') {
    const emailData = document.querySelector('meta[name="correo-modal"]');
    const email = emailData ? emailData.content : '';
    openVerifyModal(email);
  }
});
