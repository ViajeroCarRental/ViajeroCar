(function () {
  "use strict";

  const qs = (s) => document.querySelector(s);
  const qsa = (s) => Array.from(document.querySelectorAll(s));

  document.addEventListener("DOMContentLoaded", () => {

    // ===== Topbar scroll
    const topbar = qs(".topbar");
    function toggleTopbar() {
      if (!topbar) return;
      if (window.scrollY > 40) topbar.classList.add("solid");
      else topbar.classList.remove("solid");
    }
    toggleTopbar();
    window.addEventListener("scroll", toggleTopbar, { passive: true });

    // ===== Menu hamburguesa
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

    // ===== Año footer
    const y = qs("#year");
    if (y) y.textContent = new Date().getFullYear();

    // ===================================================
    // ================= FILTRO AUTOS ====================
    // ===================================================

    const botones = document.querySelectorAll('.filter-card')
    const autos = document.querySelectorAll(".catalog-group");

    botones.forEach(btn => {

      btn.addEventListener("click", () => {

        botones.forEach(b => b.classList.remove("active"));
        btn.classList.add("active");

        const filtro = btn.dataset.filter;

        autos.forEach(auto => {

          if (filtro === "all") {
            auto.style.display = "block";
          } else {
            auto.style.display =
              auto.dataset.categoria === filtro ? "block" : "none";
          }

        });

      });

    });

  });

})();
