/* ============================================================
   CATALOGO.JS - Versión optimizada
   Solo contiene lo específico de la vista catálogo:
     - Acordeón del filtro (abrir/cerrar/ESC/click-fuera)
     - Sticky del filtro en desktop
     - Filtro visual por categoría (Sedan/SUV/Pickup/Van)

   ELIMINADO:
     - Zombies: Flatpickr, btn-filter, helpers de fecha
     - Duplicados con layout: topbar scroll, hamburguesa, año footer
   ============================================================ */

(function () {
  "use strict";

  /* ====================
     Helpers
  ==================== */
  const qs = (s, r = document) => r.querySelector(s);
  const qsa = (s, r = document) => Array.from(r.querySelectorAll(s));

  function smoothScrollIntoView(el) {
    if (!el) return;
    const topbar = qs(".topbar");
    const offset = (topbar ? topbar.offsetHeight : 0) + 16;
    const y = el.getBoundingClientRect().top + window.scrollY - offset;
    window.scrollTo({ top: Math.max(0, y), behavior: "smooth" });
  }

  // Animación del panel del acordeón por altura real
  function setPanelOpen(panel, open) {
    if (!panel) return;
    panel.style.overflow = "hidden";

    if (open) {
      panel.classList.add("is-open");
      panel.style.maxHeight = panel.scrollHeight + "px";

      setTimeout(() => {
        if (panel.classList.contains("is-open")) {
          panel.style.maxHeight = panel.scrollHeight + "px";
        }
      }, 160);
    } else {
      panel.style.maxHeight = panel.scrollHeight + "px";
      requestAnimationFrame(() => {
        panel.style.maxHeight = "0px";
        panel.classList.remove("is-open");
      });
    }
  }

  /* ====================
     ENTRY POINT
  ==================== */
  document.addEventListener("DOMContentLoaded", () => {

    // ========================================================
    // ACORDEÓN FILTRO + CERRAR AL CLICK FUERA + ESC + SELECCIÓN
    // ========================================================
    let closeFiltroAccordion = null;

    (function initFiltroAccordion() {
      const wrapper = qs(".filter-accordion");
      const btn = qs("#btn-filtro-autos");
      const panel = qs("#filtro-autos");
      if (!wrapper || !btn || !panel) return;

      const labelSpan = btn.querySelector(".acc-left span");
      const icon = btn.querySelector(".acc-icon");

      // Estado inicial cerrado
      btn.classList.add("collapsed");
      btn.setAttribute("aria-expanded", "false");

      const textClosed = btn.dataset.textClosed || "Filtrar categorías";
      if (labelSpan) labelSpan.textContent = textClosed;
      if (icon) icon.style.transform = "rotate(0deg)";

      panel.classList.remove("show");
      panel.classList.remove("is-open");
      panel.style.maxHeight = "0px";

      const isOpenNow = () => btn.getAttribute("aria-expanded") === "true";

      const setState = (open) => {
        btn.setAttribute("aria-expanded", String(open));
        btn.classList.toggle("collapsed", !open);

        if (labelSpan) {
          labelSpan.textContent = open ? btn.dataset.textOpen : btn.dataset.textClosed;
        }
        if (icon) icon.style.transform = open ? "rotate(180deg)" : "rotate(0deg)";

        setPanelOpen(panel, open);
        if (open) smoothScrollIntoView(btn);
      };

      closeFiltroAccordion = () => {
        if (isOpenNow()) setState(false);
      };

      // Toggle al hacer click en el botón
      btn.addEventListener("click", (e) => {
        e.preventDefault();
        setState(!isOpenNow());
      });

      // Cerrar al hacer click fuera del acordeón
      document.addEventListener("click", (e) => {
        if (!isOpenNow()) return;
        if (!wrapper.contains(e.target)) setState(false);
      });

      // Cerrar con tecla ESC
      document.addEventListener("keydown", (e) => {
        if (e.key === "Escape" && isOpenNow()) setState(false);
      });

      // Recalcular altura si está abierto al redimensionar
      window.addEventListener("resize", () => {
        if (isOpenNow()) panel.style.maxHeight = panel.scrollHeight + "px";
      });
    })();

    // ========================================================
    // STICKY DENTRO DEL HERO (solo desktop)
    // ========================================================
    (function stickyInsideHeroDesktopOnly() {
      const hero = qs(".hero");
      const heroInner = qs(".hero-inner");
      const filterCard = qs(".hero-filter-card");
      if (!hero || !heroInner || !filterCard) return;

      const mq = window.matchMedia("(max-width: 768px)");

      const apply = () => {
        // Reset
        filterCard.style.position = "";
        filterCard.style.top = "";
        filterCard.style.zIndex = "";

        if (mq.matches) return; // móvil: comportamiento normal

        const topbar = qs(".topbar");
        const offset = (topbar ? topbar.offsetHeight : 0) + 22;

        filterCard.style.position = "sticky";
        filterCard.style.top = offset + "px";
        filterCard.style.zIndex = "5";
      };

      apply();
      window.addEventListener("resize", apply);
      if (mq.addEventListener) mq.addEventListener("change", apply);
      else mq.addListener(apply);
    })();

    // ========================================================
    // FILTRO VISUAL DE AUTOS POR CATEGORÍA
    // ========================================================
    const botonesFiltro = qsa(".filter-card");
    const autos = qsa(".catalog-group");

    botonesFiltro.forEach((btn) => {
      btn.addEventListener("click", () => {
        botonesFiltro.forEach((b) => b.classList.remove("active"));
        btn.classList.add("active");

        const filtro = btn.dataset.filter;

        autos.forEach((auto) => {
          if (filtro === "all") {
            auto.style.display = "block";
          } else {
            auto.style.display = auto.dataset.categoria === filtro ? "block" : "none";
          }
        });

        // Cerrar acordeón al seleccionar opción (útil en móvil)
        if (typeof closeFiltroAccordion === "function") {
          closeFiltroAccordion();
        }
      });
    });
  });
})();
