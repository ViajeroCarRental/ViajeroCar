(function () {
  "use strict";

  const qs  = (s) => document.querySelector(s);
  const qsa = (s) => Array.from(document.querySelectorAll(s));

  // --- Utilidades de fecha
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

  // Espera al DOM listo
  document.addEventListener("DOMContentLoaded", () => {
    // ===== Topbar: estilo sólido al hacer scroll
    const topbar = qs(".topbar");
    function toggleTopbar() {
      if (!topbar) return;
      if (window.scrollY > 40) topbar.classList.add("solid");
      else topbar.classList.remove("solid");
    }
    toggleTopbar();
    window.addEventListener("scroll", toggleTopbar, { passive: true });

    // ===== Menú hamburguesa (mostrar/ocultar)
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

    // ===== Marcar link activo por ruta
    (function markActive() {
      // En Laravel usamos routeIs en el Blade, pero por si abres estático:
      const current = (location.pathname.split("/").pop() || "").toLowerCase();
      qsa(".menu a").forEach((a) => {
        const href = (a.getAttribute("href") || "").toLowerCase();
        if (href && current && href === current) a.classList.add("active");
      });
    })();

    // ===== Footer: año actual
    const y = qs("#year");
    if (y) y.textContent = new Date().getFullYear();

    // ====== FECHAS Catálogo (Entrega/Devolución)
    // Inputs esperados en la vista:
    //  - #date-start (Entrega)
    //  - #date-end   (Devolución)
    const startInput = qs("#date-start");
    const endInput = qs("#date-end");
    if (!startInput || !endInput) return;

    // Asegura placeholders amigables
    startInput.setAttribute("placeholder", "dd/mm/aaaa");
    endInput.setAttribute("placeholder", "dd/mm/aaaa");

    // Si Flatpickr está disponible, usamos rango + minDate hoy
    if (window.flatpickr) {
      // Localiza a español si está el módulo
      try {
        if (flatpickr.l10ns?.es) flatpickr.localize(flatpickr.l10ns.es);
      } catch (_) {}

      // Preferimos rangePlugin si existe (un solo calendario para ambos)
      if (typeof rangePlugin !== "undefined") {
        const fpStart = flatpickr("#date-start", {
          altInput: true,
          altFormat: "d/m/Y",
          dateFormat: "Y-m-d",
          minDate: "today",        // ⛔ nada antes de hoy
          allowInput: false,
          clickOpens: true,
          plugins: [new rangePlugin({ input: "#date-end" })],
          onChange(selectedDates) {
            // Garantiza que fin >= inicio
            const s = selectedDates?.[0] || parseYMD(startInput.value);
            const e = parseYMD(endInput.value);
            if (s && e && e < s) endInput.value = formatYMD(s);
          },
        });

        // Abrir el mismo calendario cuando se focus/click en devolución
        const openRange = () => {
          try {
            fpStart.open();
          } catch (_) {}
        };
        endInput.addEventListener("focus", openRange);
        endInput.addEventListener("click", openRange);
      } else {
        // Sin rangePlugin: dos instancias encadenadas
        const fpInicio = flatpickr("#date-start", {
          altInput: true,
          altFormat: "d/m/Y",
          dateFormat: "Y-m-d",
          minDate: "today",
          allowInput: false,
          clickOpens: true,
        });
        const fpFin = flatpickr("#date-end", {
          altInput: true,
          altFormat: "d/m/Y",
          dateFormat: "Y-m-d",
          minDate: "today",
          allowInput: false,
          clickOpens: true,
        });

        // Si cambia inicio, sube el minDate de fin
        startInput.addEventListener("change", (e) => {
          fpFin.set("minDate", e.target.value || "today");
          // Si fin quedó antes, corrígelo
          const s = parseYMD(e.target.value);
          const eDate = parseYMD(endInput.value);
          if (s && eDate && eDate < s) endInput.value = formatYMD(s);
        });
      }
    } else {
      // ===== Fallback sin Flatpickr (CDN caído, modo offline, etc.)
      startInput.removeAttribute("readonly");
      endInput.removeAttribute("readonly");

      startInput.setAttribute("min", todayISO());
      endInput.setAttribute("min", todayISO());

      startInput.addEventListener("change", () => {
        const s = parseYMD(startInput.value);
        if (s) {
          endInput.setAttribute("min", formatYMD(s));
          const e = parseYMD(endInput.value);
          if (e && e < s) endInput.value = formatYMD(s);
        }
      });
    }

    // ===== BOTÓN FILTRAR (conexión al backend Laravel) =====
    const btnFilter = qs("#btn-filter");
    if (btnFilter) {
      btnFilter.addEventListener("click", () => {
        const loc = qs("#f-location")?.value || "";
        const type = qs("#f-type")?.value || "";
        const start = qs("#date-start")?.value || "";
        const end = qs("#date-end")?.value || "";

        // Validación simple
        if (!start || !end) {
          alert("Por favor selecciona las fechas de entrega y devolución.");
          return;
        }

        // Construye URL con parámetros para el controlador
        const params = new URLSearchParams({
          location: loc,
          type: type,
          start: start,
          end: end,
        });

        // Efecto visual breve y redirección al backend
        btnFilter.disabled = true;
        btnFilter.classList.add("loading");
        setTimeout(() => {
          window.location.href = `/catalogo/filtrar?${params.toString()}`;
        }, 250);
      });
    }
  });
})();
