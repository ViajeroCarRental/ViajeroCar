(function () {
  "use strict";

  // --- 1. Utilidades Globales ---
  const qs = (s, r = document) => r.querySelector(s);
  const qsa = (s, r = document) => Array.from(r.querySelectorAll(s));

  // --- 2. Herramientas de Fecha (Para los inputs de entrega/devolución) ---
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

    // ==========================================================
    // ✅ ACORDEÓN FILTRO (DESKTOP SIEMPRE ABIERTO / MÓVIL NORMAL)
    // ==========================================================
    let closeFiltroAccordion = null;

    (function initFiltroAccordionVanilla() {
      const wrapper = qs(".filter-accordion");
      const btn = qs("#btn-filtro-autos");
      const panel = qs("#filtro-autos");
      if (!wrapper || !btn || !panel) return;

      const labelSpan = btn.querySelector(".acc-left span");
      const icon = btn.querySelector(".acc-icon");

      // ✅ Breakpoint: móvil
      const mqMobile = window.matchMedia("(max-width: 768px)");
      const isMobile = () => mqMobile.matches;

      const isOpenNow = () => btn.getAttribute("aria-expanded") === "true";

      const setState = (open) => {
        btn.setAttribute("aria-expanded", String(open));
        btn.classList.toggle("collapsed", !open);

        if (labelSpan) {
          labelSpan.textContent = open ? "Ocultar categorías" : "Filtrar categorías";
        }
        if (icon) icon.style.transform = open ? "rotate(180deg)" : "rotate(0deg)";

        setPanelOpen(panel, open);
      };

      // Estado inicial (lo ajusta applyMode)
      btn.classList.add("collapsed");
      btn.setAttribute("aria-expanded", "false");
      if (labelSpan) labelSpan.textContent = "Filtrar categorías";
      if (icon) icon.style.transform = "rotate(0deg)";
      panel.classList.remove("show");
      panel.classList.remove("is-open");
      panel.style.maxHeight = "0px";

      // --- handlers (referencias para poder removerlos) ---
      const onBtnClick = (e) => {
        // En móvil: toggle normal
        e.preventDefault();
        setState(!isOpenNow());
        if (isOpenNow()) smoothScrollIntoView(btn);
      };

      const onDocClick = (e) => {
        // En móvil: cerrar al click fuera
        if (!isOpenNow()) return;
        if (!wrapper.contains(e.target)) setState(false);
      };

      const onKeyDown = (e) => {
        // En móvil: cerrar con ESC
        if (e.key === "Escape" && isOpenNow()) setState(false);
      };

      const onResizeOpenHeight = () => {
        if (isOpenNow()) panel.style.maxHeight = panel.scrollHeight + "px";
      };

      // ✅ Expone cerrar (pero lo usaremos SOLO en móvil)
      closeFiltroAccordion = () => {
        if (isOpenNow()) setState(false);
      };

      // ✅ Activa / desactiva comportamiento según modo
      const applyMode = () => {
        // limpia listeners siempre
        btn.removeEventListener("click", onBtnClick);
        document.removeEventListener("click", onDocClick);
        document.removeEventListener("keydown", onKeyDown);
        window.removeEventListener("resize", onResizeOpenHeight);

        if (isMobile()) {
          // ===== MÓVIL: como lo tenías =====
          setState(false); // cerrado por defecto
          btn.addEventListener("click", onBtnClick);
          document.addEventListener("click", onDocClick);
          document.addEventListener("keydown", onKeyDown);
          window.addEventListener("resize", onResizeOpenHeight);
        } else {
          // ===== DESKTOP/LAPTOP: SIEMPRE ABIERTO =====
          setState(true); // abierto siempre
          // No agregamos listeners de cierre/toggle
          // (queda fijo en el hero y no se cierra)
          window.addEventListener("resize", onResizeOpenHeight);
        }
      };

      // Inicializa modo
      applyMode();

      // Reaplicar al cambiar tamaño
      if (mqMobile.addEventListener) {
        mqMobile.addEventListener("change", applyMode);
      } else {
        mqMobile.addListener(applyMode); // fallback
      }
    })();

    // ===== Filtro Visual de Autos (Categorías) =====
    const botonesFiltro = qsa(".filter-card");
    const autos = qsa(".catalog-group");

    // breakpoint móvil (para cerrar solo ahí)
    const mqMobileFilter = window.matchMedia("(max-width: 768px)");

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

        // ✅ SOLO en móvil: cerrar acordeón al seleccionar opción
        if (mqMobileFilter.matches && typeof closeFiltroAccordion === "function") {
          closeFiltroAccordion();
        }
      });
    });

    // ===== Calendarios y Fechas (Flatpickr) =====
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

    // ===== Botón Filtrar (Envío a Laravel) =====
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