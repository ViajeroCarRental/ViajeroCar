(function () {
  "use strict";

  /* =========================
     Helpers
  ========================= */
  const qs  = (s) => document.querySelector(s);
  const qsa = (s) => Array.from(document.querySelectorAll(s));

  /* =========================
     Utilidades de fecha
  ========================= */
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

  /* =========================
     DOM Ready
  ========================= */
  document.addEventListener("DOMContentLoaded", () => {

    /* ===== Topbar sólido al hacer scroll ===== */
    const topbar = qs(".topbar");

    function toggleTopbar() {
      if (!topbar) return;
      window.scrollY > 40
        ? topbar.classList.add("solid")
        : topbar.classList.remove("solid");
    }

    toggleTopbar();
    window.addEventListener("scroll", toggleTopbar, { passive: true });

    /* ======================================================
       MENÚ HAMBURGUESA
    ====================================================== */
    (function navHamburger() {
      const btn = qs("#navHamburger") || qs(".hamburger");
      const menu = qs("#mainMenu") || qs(".menu");
      const backdrop = qs("#navBackdrop") || qs(".nav-backdrop");
      if (!btn || !menu) return;

      const MQ = window.matchMedia("(max-width: 940px)");

      const isMobile = () => MQ.matches;

      function openNav() {
        if (!isMobile()) return;
        document.body.classList.add("nav-open");
        if (topbar) topbar.classList.add("nav-open");
        btn.setAttribute("aria-expanded", "true");
      }

      function closeNav() {
        document.body.classList.remove("nav-open");
        if (topbar) topbar.classList.remove("nav-open");
        btn.setAttribute("aria-expanded", "false");
      }

      function toggleNav(e) {
        if (e) e.preventDefault();
        document.body.classList.contains("nav-open")
          ? closeNav()
          : openNav();
      }

      btn.setAttribute("type", "button");
      btn.setAttribute("aria-label", "Abrir menú");
      btn.setAttribute("aria-expanded", "false");

      btn.addEventListener("click", toggleNav);

      if (backdrop) backdrop.addEventListener("click", closeNav);

      menu.addEventListener("click", (e) => {
        if (e.target.closest("a")) closeNav();
      });

      document.addEventListener("keydown", (e) => {
        if (e.key === "Escape") closeNav();
      });

      if (MQ.addEventListener) {
        MQ.addEventListener("change", () => {
          if (!isMobile()) closeNav();
        });
      } else {
        window.addEventListener("resize", () => {
          if (!isMobile()) closeNav();
        }, { passive: true });
      }
    })();

    /* ===== Marcar link activo ===== */
    (function markActive() {
      const current = (location.pathname.split("/").pop() || "").toLowerCase();
      qsa(".menu a").forEach((a) => {
        const href = (a.getAttribute("href") || "").toLowerCase();
        if (href && current && href === current) {
          a.classList.add("active");
        }
      });
    })();

    /* ===== Footer: año actual ===== */
    const year = qs("#year");
    if (year) year.textContent = new Date().getFullYear();

    /* ======================================================
       FECHAS (solo si existen inputs)
    ====================================================== */
    const startInput = qs("#date-start");
    const endInput   = qs("#date-end");

    if (startInput && endInput) {

      startInput.setAttribute("placeholder", "dd/mm/aaaa");
      endInput.setAttribute("placeholder", "dd/mm/aaaa");

      if (window.flatpickr) {
        try {
          if (flatpickr.l10ns?.es) flatpickr.localize(flatpickr.l10ns.es);
        } catch (_) {}

        if (typeof rangePlugin !== "undefined") {
          const fpStart = flatpickr("#date-start", {
            altInput: true,
            altFormat: "d/m/Y",
            dateFormat: "Y-m-d",
            minDate: "today",
            allowInput: false,
            clickOpens: true,
            plugins: [new rangePlugin({ input: "#date-end" })],
            onChange(selectedDates) {
              const s = selectedDates?.[0] || parseYMD(startInput.value);
              const e = parseYMD(endInput.value);
              if (s && e && e < s) endInput.value = formatYMD(s);
            },
          });

          const openRange = () => {
            try { fpStart.open(); } catch (_) {}
          };
          endInput.addEventListener("focus", openRange);
          endInput.addEventListener("click", openRange);

        } else {
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

          startInput.addEventListener("change", (e) => {
            fpFin.set("minDate", e.target.value || "today");
            const s = parseYMD(e.target.value);
            const eDate = parseYMD(endInput.value);
            if (s && eDate && eDate < s) {
              endInput.value = formatYMD(s);
            }
          });
        }
      } else {
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
    }
  });
})();
