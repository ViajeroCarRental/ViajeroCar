// public/js/politicas.js
(function () {
  "use strict";

  const AUTH_KEY = "vj_auth";

  function onReady(fn) {
    if (document.readyState === "loading") {
      document.addEventListener("DOMContentLoaded", fn, { once: true });
    } else {
      fn();
    }
  }

  // Helpers
  const qs  = (s, r = document) => r.querySelector(s);
  const qsa = (s, r = document) => Array.from(r.querySelectorAll(s));

  function safeJsonParse(str) {
    try { return JSON.parse(str); } catch (_) { return null; }
  }

  // Auth helper (si lo usas)
  if (!window.VJ_AUTH) {
    function getAuth() {
      return safeJsonParse(localStorage.getItem(AUTH_KEY) || "null");
    }
    function isLogged() {
      return !!localStorage.getItem(AUTH_KEY);
    }
    window.VJ_AUTH = { getAuth, isLogged };
  }

  // =========================
  // ✅ Modal
  // =========================
  function setupPolicyModal() {
    const modal = qs("#policyModal");
    const modalBody = qs("#policyModalBody");
    const modalTitle = qs("#policyModalTitle");

    if (!modal || !modalBody || !modalTitle) return;

    let lastFocus = null;

    function openModal(title, tplId) {
      const tpl = qs(`#${tplId}`);
      if (!tpl) return;

      lastFocus = document.activeElement;

      modalTitle.textContent = title || "Política";
      modalBody.innerHTML = tpl.innerHTML;

      modal.classList.add("open");
      modal.setAttribute("aria-hidden", "false");
      document.body.classList.add("modal-open");

      // focus al botón cerrar
      const closeBtn = qs('[data-close="1"]', modal);
      if (closeBtn) closeBtn.focus({ preventScroll: true });
    }

    function closeModal() {
      modal.classList.remove("open");
      modal.setAttribute("aria-hidden", "true");
      document.body.classList.remove("modal-open");

      // limpiar contenido (para no mantener tablas/images pesadas en DOM)
      modalBody.innerHTML = "";

      if (lastFocus && typeof lastFocus.focus === "function") {
        lastFocus.focus({ preventScroll: true });
      }
      lastFocus = null;
    }

    // Click en cards
    qsa(".policy-card").forEach((btn) => {
      btn.addEventListener("click", () => {
        const tplId = btn.getAttribute("data-modal");
        const title = btn.getAttribute("data-title") || btn.textContent.trim();
        if (!tplId) return;
        openModal(title, tplId);
      });
    });

    // cerrar (X o backdrop)
    qsa('[data-close="1"]', modal).forEach((el) => {
      el.addEventListener("click", closeModal);
    });

    // click en backdrop
    modal.addEventListener("click", (e) => {
      if (e.target && e.target.matches(".vj-modal__backdrop")) closeModal();
    });

    // ESC
    window.addEventListener("keydown", (e) => {
      if (e.key === "Escape" && modal.classList.contains("open")) {
        e.preventDefault();
        closeModal();
      }
    });

    // Exponer por si lo ocupas
    window.VJ_POLICIES_MODAL = { openModal, closeModal };
  }

  // =========================
  // Init
  // =========================
  onReady(() => {

    // Navbar (opcional / seguro)
    const topbar = qs("#topbar");
    function toggleTopbar() {
      if (!topbar) return;
      window.scrollY > 40 ? topbar.classList.add("solid") : topbar.classList.remove("solid");
    }
    toggleTopbar();
    window.addEventListener("scroll", toggleTopbar, { passive: true });

    const hamburger = qs("#hamburger");
    const mainMenu = qs("#mainMenu");
    if (hamburger && mainMenu) {
      hamburger.addEventListener("click", () => {
        const hidden = getComputedStyle(mainMenu).display === "none";
        mainMenu.style.display = hidden ? "flex" : "none";
        if (hidden) {
          mainMenu.style.flexDirection = "column";
          mainMenu.style.gap = "12px";
        }
      });
    }

    // Icono de cuenta (si existe)
    const accountLink = qs("#accountLink");
    if (accountLink) {
      const logged = window.VJ_AUTH?.isLogged?.() || false;
      const auth = window.VJ_AUTH?.getAuth?.() || {};

      const loginUrl = accountLink.getAttribute("data-login-url") || "/login";
      const profileUrl = accountLink.getAttribute("data-profile-url") || "/perfil";

      if (logged) {
        accountLink.href = profileUrl;
        accountLink.title = "Mi perfil";
        const letter = (auth.name?.[0] || auth.email?.[0] || "U").toUpperCase();
        accountLink.innerHTML = `<span class="avatar-mini">${letter}</span>`;
      } else {
        accountLink.href = loginUrl;
        accountLink.title = "Iniciar sesión";
        accountLink.innerHTML = `<i class="fa-regular fa-user"></i>`;
      }

      window.addEventListener("storage", (e) => {
        if (e.key !== AUTH_KEY) return;
        const logged2 = window.VJ_AUTH?.isLogged?.() || false;
        const auth2 = window.VJ_AUTH?.getAuth?.() || {};
        if (logged2) {
          accountLink.href = profileUrl;
          accountLink.title = "Mi perfil";
          const letter2 = (auth2.name?.[0] || auth2.email?.[0] || "U").toUpperCase();
          accountLink.innerHTML = `<span class="avatar-mini">${letter2}</span>`;
        } else {
          accountLink.href = loginUrl;
          accountLink.title = "Iniciar sesión";
          accountLink.innerHTML = `<i class="fa-regular fa-user"></i>`;
        }
      });
    }

    // Año footer (si existe)
    const yearEl = qs("#year");
    if (yearEl) yearEl.textContent = new Date().getFullYear();

    // ✅ Modal policies
    setupPolicyModal();
  });
})();