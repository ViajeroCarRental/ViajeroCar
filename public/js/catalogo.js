(function () {
  "use strict";

  // --- 1. Utilidades Globales ---
  const qs = (s) => document.querySelector(s);
  const qsa = (s) => Array.from(document.querySelectorAll(s));

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

  // --- 3. Ejecución al cargar el DOM ---
  document.addEventListener("DOMContentLoaded", () => {

    // ===== Interfaz Básica (Topbar, Menú y Footer) =====
    const topbar = qs(".topbar");
    const toggleTopbar = () => {
      if (!topbar) return;
      window.scrollY > 40 ? topbar.classList.add("solid") : topbar.classList.remove("solid");
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

    // ===== TFiltro Visual de Autos (Categorías) =====
    // Esto permite filtrar los autos que ya están cargados en pantalla
    const botonesFiltro = qsa('.filter-card');
    const autos = qsa(".catalog-group");

    botonesFiltro.forEach(btn => {
      btn.addEventListener("click", () => {
        botonesFiltro.forEach(b => b.classList.remove("active"));
        btn.classList.add("active");

        const filtro = btn.dataset.filter;
        autos.forEach(auto => {
          if (filtro === "all") {
            auto.style.display = "block";
          } else {
            auto.style.display = auto.dataset.categoria === filtro ? "block" : "none";
          }
        });
      });
    });

    // ===== Calendarios y Fechas (Flatpickr) =====
    const startInput = qs("#date-start");
    const endInput = qs("#date-end");

    if (startInput && endInput) {
      startInput.setAttribute("placeholder", "dd/mm/aaaa");
      endInput.setAttribute("placeholder", "dd/mm/aaaa");

      if (window.flatpickr) {
        // Configuración de Flatpickr con soporte de rango
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
          // Fallback si no hay plugin de rango
          flatpickr("#date-start", fpConfig);
          flatpickr("#date-end", fpConfig);
        }
      } else {
        // Fallback nativo si falla el CDN
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
