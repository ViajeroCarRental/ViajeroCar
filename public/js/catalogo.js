(function () {
  "use strict";
<<<<<<< Updated upstream

  // --- 1. Utilidades Globales ---
  const qs = (s, r = document) => r.querySelector(s);
  const qsa = (s, r = document) => Array.from(r.querySelectorAll(s));
=======
  const qs = (s) => document.querySelector(s);
  const qsa = (s) => Array.from(document.querySelectorAll(s));
>>>>>>> Stashed changes

  function todayISO() {
    const t = new Date();
    const y = t.getFullYear();
    const m = String(t.getMonth() + 1).padStart(2, "0");
    const d = String(t.getDate()).padStart(2, "0");
    return `${y}-${m}-${d}`;
  }
  function parseYMD(v) {
    if (!v) return null;
    const [y, m, d] = v.split("-").map(Number);
    if (!y || !m || !d) return null;
    return new Date(y, m - 1, d);
  }
  function formatYMD(dt) {
    const y = dt.getFullYear();
    const m = String(dt.getMonth() + 1).padStart(2, "0");
    const d = String(dt.getDate()).padStart(2, "0");
    return `${y}-${m}-${d}`;
  }

<<<<<<< Updated upstream
  // --- Helpers UI ---
  function smoothScrollIntoView(el) {
    if (!el) return;
    const topbar = qs(".topbar");
    const offset = (topbar ? topbar.offsetHeight : 0) + 16;

    const y = el.getBoundingClientRect().top + window.scrollY - offset;
    window.scrollTo({ top: Math.max(0, y), behavior: "smooth" });
  }

  // --- Helper acordeón: animación por altura real ---
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

  document.addEventListener("DOMContentLoaded", () => {
    // ===== Interfaz Básica (Topbar, Menú y Footer) =====
=======
  document.addEventListener("DOMContentLoaded", () => {
>>>>>>> Stashed changes
    const topbar = qs(".topbar");
    const toggleTopbar = () => {
      if (!topbar) return;
      window.scrollY > 40
        ? topbar.classList.add("solid")
        : topbar.classList.remove("solid");
    };
    window.addEventListener("scroll", toggleTopbar, { passive: true });
    toggleTopbar();

    const hamburger = qs(".hamburger");
    const menu = qs(".menu");
    if (hamburger && menu) {
      hamburger.addEventListener("click", () => {
        const visible = getComputedStyle(menu).display !== "none";
        menu.style.display = visible ? "none" : "flex";
        if (!visible) {
          menu.style.flexDirection = "column";
          menu.style.gap = "12px";
        }
      });
    }

    const yearEl = qs("#year");
    if (yearEl) yearEl.textContent = new Date().getFullYear();

<<<<<<< Updated upstream
    // ==========================================================
    // ✅ ACORDEÓN FILTRO (VANILLA) + CERRAR AL CLICK FUERA + AL SELECCIONAR
    // ==========================================================
    let closeFiltroAccordion = null; // <- función global local para cerrar desde otros bloques

    (function initFiltroAccordionVanilla() {
      const wrapper = qs(".filter-accordion");
      const btn = qs("#btn-filtro-autos");
      const panel = qs("#filtro-autos");
      if (!wrapper || !btn || !panel) return;

      const labelSpan = btn.querySelector(".acc-left span");
      const icon = btn.querySelector(".acc-icon");

      // Estado inicial cerrado
      btn.classList.add("collapsed");
      btn.setAttribute("aria-expanded", "false");
      if (labelSpan) labelSpan.textContent = "Filtrar categorías";
      if (icon) icon.style.transform = "rotate(0deg)";

      panel.classList.remove("show");
      panel.classList.remove("is-open");
      panel.style.maxHeight = "0px";

      const isOpenNow = () => btn.getAttribute("aria-expanded") === "true";

      const setState = (open) => {
        btn.setAttribute("aria-expanded", String(open));
        btn.classList.toggle("collapsed", !open);

        if (labelSpan) {
          labelSpan.textContent = open ? "Ocultar categorías" : "Filtrar categorías";
        }
        if (icon) icon.style.transform = open ? "rotate(180deg)" : "rotate(0deg)";

        setPanelOpen(panel, open);
        if (open) smoothScrollIntoView(btn);
      };

      // ✅ expone un "cerrar" para usarlo en el filtro
      closeFiltroAccordion = () => {
        if (isOpenNow()) setState(false);
      };

      // Toggle normal
      btn.addEventListener("click", (e) => {
        e.preventDefault();
        setState(!isOpenNow());
      });

      // ✅ Cerrar al hacer click fuera
      document.addEventListener("click", (e) => {
        if (!isOpenNow()) return;
        if (!wrapper.contains(e.target)) setState(false);
      });

      // ✅ Cerrar con tecla ESC
      document.addEventListener("keydown", (e) => {
        if (e.key === "Escape" && isOpenNow()) setState(false);
      });

      // Recalcular altura si está abierto al redimensionar
      window.addEventListener("resize", () => {
        if (isOpenNow()) panel.style.maxHeight = panel.scrollHeight + "px";
      });
    })();

    // ===== Filtro Visual de Autos (Categorías) =====
    const botonesFiltro = qsa(".filter-card");
=======
    const botonesFiltro = qsa('.filter-card');
>>>>>>> Stashed changes
    const autos = qsa(".catalog-group");

    botonesFiltro.forEach((btn) => {
      btn.addEventListener("click", () => {
        // activar estado visual
        botonesFiltro.forEach((b) => b.classList.remove("active"));
        btn.classList.add("active");

        // aplicar filtro
        const filtro = btn.dataset.filter;

        autos.forEach((auto) => {
          if (filtro === "all") {
            auto.style.display = "block";
          } else {
            auto.style.display = auto.dataset.categoria === filtro ? "block" : "none";
          }
        });

        // ✅ cerrar acordeón al seleccionar opción
        if (typeof closeFiltroAccordion === "function") {
          closeFiltroAccordion();
        }
      });
    });

    const startInput = qs("#date-start");
    const endInput = qs("#date-end");

    if (startInput && endInput) {
      startInput.setAttribute("placeholder", "dd/mm/aaaa");
      endInput.setAttribute("placeholder", "dd/mm/aaaa");

      if (window.flatpickr) {
        const fpConfig = {
          altInput: true,
          altFormat: "d/m/Y",
          dateFormat: "Y-m-d",
          minDate: "today",
          allowInput: false,
        };

        if (typeof rangePlugin !== "undefined") {
          flatpickr("#date-start", {
            ...fpConfig,
            plugins: [new rangePlugin({ input: "#date-end" })],
          });
        } else {
          flatpickr("#date-start", fpConfig);
          flatpickr("#date-end", fpConfig);
        }
      } else {
        startInput.setAttribute("min", todayISO());
        endInput.setAttribute("min", todayISO());
      }
    }

    const btnFilter = qs("#btn-filter");
    if (btnFilter) {
      btnFilter.addEventListener("click", () => {
        const params = new URLSearchParams({
          location: qs("#f-location")?.value || "",
          type: qs("#f-type")?.value || "",
          start: qs("#date-start")?.value || "",
          end: qs("#date-end")?.value || "",
        });

        if (!params.get("start") || !params.get("end")) {
          alert("Por favor selecciona las fechas de entrega y devolución.");
          return;
        }

        btnFilter.disabled = true;
        btnFilter.classList.add("loading");
        setTimeout(() => {
          window.location.href = `/catalogo/filtrar?${params.toString()}`;
        }, 250);
      });
    }
  });
})();