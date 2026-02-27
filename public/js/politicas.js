// public/js/politicas.js
(function () {
  "use strict";

  // ✅ Laravel friendly: NO .html, usa URLs reales si existen en tu layout
  // Si tienes rutas por nombre, lo ideal es inyectarlas desde Blade con data-attributes.
  // Aquí lo dejamos seguro (no rompe si no están).
  const AUTH_KEY = "vj_auth";

  function onReady(fn) {
    if (document.readyState === "loading") {
      document.addEventListener("DOMContentLoaded", fn, { once: true });
    } else {
      fn();
    }
  }

  // =========================
  // Helpers
  // =========================
  const qs = (s, r = document) => r.querySelector(s);
  const qsa = (s, r = document) => Array.from(r.querySelectorAll(s));

  function safeJsonParse(str) {
    try { return JSON.parse(str); } catch (_) { return null; }
  }

  // =========================
  // Auth helper (si lo usas)
  // =========================
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
  // Accordion (genérico)
  // =========================
  function setupAccordion(containerSelector, itemSelector, headSelector, bodySelector, contentSelector) {
    const root = qs(containerSelector) || document;
    const items = qsa(itemSelector, root);
    if (!items.length) return;

    items.forEach((item) => {
      const head = qs(headSelector, item);
      const body = qs(bodySelector, item);
      const content = contentSelector ? qs(contentSelector, item) : (body ? body.firstElementChild : null);

      if (!head || !body) return;

      // estado inicial (cerrado)
      item.classList.remove("open");
      head.setAttribute("aria-expanded", "false");
      body.setAttribute("aria-hidden", "true");
      body.style.overflow = "hidden";
      body.style.maxHeight = "0px";

      function contentOuterHeight() {
        if (!content) return 0;

        // ✅ incluye márgenes (tu .policy-content tiene margin:14px)
        const cs = window.getComputedStyle(content);
        const mt = parseFloat(cs.marginTop) || 0;
        const mb = parseFloat(cs.marginBottom) || 0;

        // scrollHeight no incluye márgenes
        return content.scrollHeight + mt + mb;
      }

  // CIERRA ACORDEON
      function closeItem(it) {
        it.classList.remove("open");
        const h = qs(headSelector, it);
        const b = qs(bodySelector, it);
        if (h) h.setAttribute("aria-expanded", "false");
        if (b) {
          b.style.maxHeight = "0px";
          b.setAttribute("aria-hidden", "true");
        }
      }
  // ABRE ACORDEON
      function openItem(it) {
        it.classList.add("open");
        const h = qs(headSelector, it);
        const b = qs(bodySelector, it);
        const c = contentSelector ? qs(contentSelector, it) : (b ? b.firstElementChild : null);
        if (h) h.setAttribute("aria-expanded", "true");
        if (b && c) {
          // recalcula con márgenes
          const cs = window.getComputedStyle(c);
          const mt = parseFloat(cs.marginTop) || 0;
          const mb = parseFloat(cs.marginBottom) || 0;
          b.style.maxHeight = (c.scrollHeight + mt + mb) + "px";
          b.setAttribute("aria-hidden", "false");
        }
      }

      head.addEventListener("click", () => {
        const isOpen = item.classList.contains("open");

        // cerrar otros dentro del mismo root
        items.forEach((it) => { if (it !== item) closeItem(it); });

        if (!isOpen) openItem(item);
        else closeItem(item);
      });

      // ✅ si cambia el contenido (responsive, fuentes, imágenes), recalcula
      if (content && "ResizeObserver" in window) {
        const ro = new ResizeObserver(() => {
          if (!item.classList.contains("open")) return;
          body.style.maxHeight = contentOuterHeight() + "px";
        });
        ro.observe(content);
      }

      // ✅ si se cambia el tamaño de la ventana, recalcula abiertos
      window.addEventListener("resize", () => {
        if (!item.classList.contains("open")) return;
        if (!content) return;
        body.style.maxHeight = contentOuterHeight() + "px";
      }, { passive: true });
    });
  }

  // =========================
  // Init
  // =========================
  onReady(() => {

    // ---------------------------------
    // Navbar (todo opcional / seguro)
    // ---------------------------------
    // Si tu layout no tiene estos ids, NO pasa nada.
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

      // ✅ toma rutas desde data-attributes si las pones en el layout
      // <a id="accountLink" data-login-url="{{ route('login') }}" data-profile-url="{{ route('perfil') }}">
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
        // re-sync rápido
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

    // ---------------------------------
    // ✅ Acordeones (los tuyos)
    // ---------------------------------
    setupAccordion(".policies-wrap", ".policy-item", ".policy-head", ".policy-body", ".policy-content");
    setupAccordion("#renta-accordion", ".sub-item", ".sub-head", ".sub-body", ".sub-content");
  });
})();
