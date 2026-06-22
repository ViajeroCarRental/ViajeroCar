/* =====================================================================
 *  Login.js — Vista de Login Viajero
 *
 *  - Topbar: clase .solid al hacer scroll
 *  - Pestañas Login/Register
 *  - Mostrar/ocultar contraseña
 *  - Barra de fortaleza de contraseña
 *  - Modal de verificación con código de 6 dígitos
 *  - Timer de reenvío
 * ===================================================================== */
(function () {
  "use strict";

  /* =================================================================
     HELPERS Y CONSTANTES (cacheados fuera de listeners)
     ================================================================= */
  const qs  = (s, r = document) => r.querySelector(s);
  const qsa = (s, r = document) => Array.from(r.querySelectorAll(s));

  const getLocale = () => (document.documentElement.lang || 'es') === 'en' ? 'en' : 'es';

  // Regex precompiladas (evita recompilación en cada keypress)
  const RX_UPPER   = /[A-Z]/;
  const RX_LOWER   = /[a-z]/;
  const RX_NUMERIC = /\d/;
  const RX_SPECIAL = /[^\w\s]/;
  const RX_DIGITS  = /\D/g;

  // Textos bilingües para la barra de fortaleza
  const STRENGTH_LABELS = {
    es: ['—', 'Débil', 'Media', 'Buena', 'Fuerte'],
    en: ['—', 'Weak', 'Medium', 'Good', 'Strong']
  };
  const STRENGTH_PREFIX = {
    es: 'Fortaleza: ',
    en: 'Strength: '
  };

  function onReady(fn) {
    if (document.readyState === "loading") {
      document.addEventListener("DOMContentLoaded", fn, { once: true });
    } else {
      fn();
    }
  }

  /* =================================================================
     TOPBAR: clase .solid al hacer scroll
     ================================================================= */
  function setupNavbar() {
    const topbar = qs(".topbar");
    if (!topbar) return;

    let isSolid = false;
    function update() {
      const shouldBeSolid = window.scrollY > 40;
      if (shouldBeSolid !== isSolid) {
        topbar.classList.toggle("solid", shouldBeSolid);
        isSolid = shouldBeSolid;
      }
    }
    update();
    window.addEventListener("scroll", update, { passive: true });
  }

  /* =================================================================
     AÑO EN EL FOOTER
     ================================================================= */
  function setupFooterYear() {
    const yearEl = qs("#year");
    if (yearEl) yearEl.textContent = new Date().getFullYear();
  }

  /* =================================================================
     PESTAÑAS LOGIN / REGISTRO
     - Respeta el panel marcado .active por el Blade (old('form_type'))
     ================================================================= */
  function setupTabs() {
    const seg = qs("#tabs");
    if (!seg) return;

    const buttons = qsa(".seg-btn", seg);
    const slider  = qs(".seg-slider", seg);
    const panels  = qsa(".auth-panel");

    function setActive(idx) {
      buttons.forEach((b, i) => b.classList.toggle("active", i === idx));
      panels.forEach(p => p.classList.remove("show"));
      const targetSel = buttons[idx]?.dataset?.target;
      const target = targetSel ? qs(targetSel) : null;
      if (target) target.classList.add("show");
      if (slider) slider.style.transform = `translateX(${idx * 100}%)`;
    }

    buttons.forEach((b, i) => b.addEventListener("click", () => setActive(i)));

    // Detectar panel activo desde el HTML (NO forzar 0)
    // Esto preserva el panel correcto tras un error de validación con old('form_type')
    const activeIdx = buttons.findIndex(b => b.classList.contains("active"));
    setActive(activeIdx >= 0 ? activeIdx : 0);
  }

  /* =================================================================
     MOSTRAR / OCULTAR CONTRASEÑA
     ================================================================= */
  function setupPasswordToggle() {
    qsa(".eye").forEach(btn => {
      btn.addEventListener("click", () => {
        const sel = btn.getAttribute("data-target");
        const inp = sel ? qs(sel) : null;
        if (!inp) return;

        const isPassword = inp.type === "password";
        inp.type = isPassword ? "text" : "password";

        const icon = btn.querySelector("i");
        if (icon) {
          icon.className = isPassword
            ? "fa-regular fa-eye-slash"
            : "fa-regular fa-eye";
        }
      });
    });
  }

  /* =================================================================
     BARRA DE FORTALEZA DE CONTRASEÑA
     - Regex precompiladas, locale cacheado
     ================================================================= */
  function setupPasswordStrength() {
    const passField     = qs("#rPass");
    const passStrength  = qs("#passStrength");
    const strengthLabel = qs("#strengthLabel");
    if (!passField || !passStrength || !strengthLabel) return;

    const bars = qsa("span", passStrength);
    const locale = getLocale();
    const labels = STRENGTH_LABELS[locale];
    const prefix = STRENGTH_PREFIX[locale];

    passField.addEventListener("input", () => {
      const val = passField.value;
      let lvl = 0;
      if (val.length >= 8) lvl++;
      if (RX_UPPER.test(val)) lvl++;
      if (RX_LOWER.test(val)) lvl++;
      if (RX_NUMERIC.test(val) || RX_SPECIAL.test(val)) lvl++;

      bars.forEach((sp, i) => sp.classList.toggle("active", i < lvl));
      strengthLabel.textContent = prefix + labels[lvl];
    });
  }

  /* =================================================================
     VALIDACIÓN EN VIVO: emails y passwords coincidentes
     ================================================================= */
  function setupLiveValidation() {
    // Confirmar email
    const email1 = qs("#rEmail");
    const email2 = qs("#rEmail2");
    if (email1 && email2) {
      const check = () => {
        if (email2.value && email1.value !== email2.value) {
          email2.setCustomValidity(
            getLocale() === "en" ? "Emails don't match" : "Los correos no coinciden"
          );
        } else {
          email2.setCustomValidity("");
        }
      };
      email1.addEventListener("input", check);
      email2.addEventListener("input", check);
    }

    // Confirmar password
    const pass1 = qs("#rPass");
    const pass2 = qs("#rPass2");
    if (pass1 && pass2) {
      const check = () => {
        if (pass2.value && pass1.value !== pass2.value) {
          pass2.setCustomValidity(
            getLocale() === "en" ? "Passwords don't match" : "Las contraseñas no coinciden"
          );
        } else {
          pass2.setCustomValidity("");
        }
      };
      pass1.addEventListener("input", check);
      pass2.addEventListener("input", check);
    }
  }

  /* =================================================================
     MODAL DE VERIFICACIÓN
     ================================================================= */
  function setupVerifyModal() {
    const modal       = qs("#verifyModal");
    const vClose      = qs("#vClose");
    const verifyEmail = qs("#verifyEmail");
    const hiddenInput = qs("#verifyEmailHidden");
    const codeInputs  = qsa("#codeInputs input");

    if (!modal) return;

    function openModal(email = "") {
      if (email) {
        if (verifyEmail) verifyEmail.textContent = email;
        if (hiddenInput) hiddenInput.value = email;
      }
      modal.classList.add("show");
      modal.setAttribute("aria-hidden", "false");
      document.body.classList.add("modal-open");

      // Focus al primer input para escribir el código de inmediato
      setTimeout(() => codeInputs[0]?.focus(), 100);

      startTimer();
    }

    function closeModal() {
      modal.classList.remove("show");
      modal.setAttribute("aria-hidden", "true");
      document.body.classList.remove("modal-open");

      // Limpiar inputs del código y detener timer
      codeInputs.forEach(inp => (inp.value = ""));
      stopTimer();
    }

    // Listeners de cerrar
    vClose?.addEventListener("click", closeModal);
    modal.querySelector(".modal-backdrop")?.addEventListener("click", closeModal);
    window.addEventListener("keydown", (e) => {
      if (e.key === "Escape" && modal.classList.contains("show")) closeModal();
    });

    // Exponer para activación automática desde meta tag
    modal._openModal = openModal;
  }

  /* =================================================================
     TIMER DE REENVÍO (30s)
     - Se cancela al cerrar modal para no consumir CPU
     ================================================================= */
  let timerId = null;

  function startTimer() {
    const btnResend = qs("#btnResend");
    const resendTimer = qs("#resendTimer");
    if (!btnResend) return;

    stopTimer();
    let seconds = 30;

    btnResend.disabled = true;
    if (resendTimer) resendTimer.textContent = `(${seconds}s)`;

    timerId = setInterval(() => {
      seconds--;
      if (resendTimer) resendTimer.textContent = `(${seconds}s)`;
      if (seconds <= 0) {
        stopTimer();
        btnResend.disabled = false;
        if (resendTimer) resendTimer.textContent = "";
      }
    }, 1000);
  }

  function stopTimer() {
    if (timerId) {
      clearInterval(timerId);
      timerId = null;
    }
  }

  /* =================================================================
     INPUTS DE CÓDIGO (UX) — con soporte de paste
     ================================================================= */
  function setupCodeInputs() {
    const codeInputs = qsa("#codeInputs input");
    if (!codeInputs.length) return;

    codeInputs.forEach((inp, idx) => {
      // Input: avanzar al siguiente al escribir
      inp.addEventListener("input", () => {
        inp.value = inp.value.replace(RX_DIGITS, "").slice(0, 1);
        if (inp.value && idx < codeInputs.length - 1) {
          codeInputs[idx + 1].focus();
        }
      });

      // Backspace: ir al anterior si está vacío
      inp.addEventListener("keydown", (e) => {
        if (e.key === "Backspace" && !inp.value && idx > 0) {
          codeInputs[idx - 1].focus();
        }
      });

      // Paste: distribuir los 6 dígitos en todos los inputs
      inp.addEventListener("paste", (e) => {
        e.preventDefault();
        const pasted = (e.clipboardData || window.clipboardData).getData("text") || "";
        const digits = pasted.replace(RX_DIGITS, "").slice(0, codeInputs.length);
        if (!digits) return;

        codeInputs.forEach((input, i) => {
          input.value = digits[i] || "";
        });

        // Focus al último input lleno
        const lastFilledIdx = Math.min(digits.length, codeInputs.length) - 1;
        codeInputs[lastFilledIdx]?.focus();
      });
    });
  }

  /* =================================================================
     PREVENIR DOBLE SUBMIT
     - Deshabilita botones de submit al primer click
     - Se rehabilitan si la página se recarga (con form fallido)
     ================================================================= */
  function setupPreventDoubleSubmit() {
    ["formLogin", "formRegister", "formVerify"].forEach(id => {
      const form = qs(`#${id}`);
      if (!form) return;

      form.addEventListener("submit", (e) => {
        const btn = form.querySelector('button[type="submit"]:not([disabled])');
        if (btn && btn.dataset.submitting !== "1") {
          btn.dataset.submitting = "1";
          // Pequeño retraso para que el botón clickeado siga siendo el "trigger"
          setTimeout(() => { btn.disabled = true; }, 0);
        }
      });
    });
  }

  /* =================================================================
     FORGOT PASSWORD (placeholder — sin ruta backend)
     ================================================================= */
  function setupForgotLink() {
    const forgotLink = qs("#forgotLink");
    if (!forgotLink) return;

    forgotLink.addEventListener("click", () => {
      const msg = getLocale() === "en"
        ? "Password recovery is coming soon. Please contact support."
        : "La recuperación de contraseña estará disponible próximamente. Contacta soporte.";

      if (typeof alertify !== "undefined") {
        alertify.message(msg);
      } else {
        alert(msg);
      }
    });
  }

  /* =================================================================
     ABRIR MODAL AUTOMÁTICAMENTE SI EL BACKEND LO INDICA
     ================================================================= */
  function setupAutoOpenModal() {
    const modalData = qs('meta[name="show-modal"]');
    if (!modalData || modalData.content !== "true") return;

    const emailData = qs('meta[name="correo-modal"]');
    const email = emailData ? emailData.content : "";

    const modal = qs("#verifyModal");
    if (modal && typeof modal._openModal === "function") {
      modal._openModal(email);
    }
  }

  /* =================================================================
     INICIALIZACIÓN PRINCIPAL
     ================================================================= */
  onReady(() => {
    setupNavbar();
    setupFooterYear();
    setupTabs();
    setupPasswordToggle();
    setupPasswordStrength();
    setupLiveValidation();
    setupCodeInputs();
    setupVerifyModal();
    setupPreventDoubleSubmit();
    setupForgotLink();

    // Activación automática del modal (debe ir AL FINAL, después de setupVerifyModal)
    setupAutoOpenModal();
  });
})();
