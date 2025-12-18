document.addEventListener("DOMContentLoaded", () => {
  const btn = document.getElementById("btnToggleSidebar");
  const sidebar = document.querySelector(".sidebar");
  const overlay = document.getElementById("sidebarOverlay");

  if (!btn || !sidebar) return;

  const isMobile = () => window.matchMedia("(max-width:768px)").matches;

  const open = () => {
    document.body.classList.add("sidebar-open");
    sidebar.classList.remove("sidebar-hidden");
    localStorage.setItem("sidebarOpen", "1");
  };

  const close = () => {
    document.body.classList.remove("sidebar-open");
    // En desktop sí lo ocultamos con la clase; en móvil lo controla sidebar-open
    if (!isMobile()) sidebar.classList.add("sidebar-hidden");
    localStorage.setItem("sidebarOpen", "0");
  };

  // Estado inicial
  const saved = localStorage.getItem("sidebarOpen");
  if (saved === "1") open();
  else close();

  btn.addEventListener("click", () => {
    const isOpen = document.body.classList.contains("sidebar-open");
    isOpen ? close() : open();
  });

  overlay?.addEventListener("click", close);

  // Re-aplicar al cambiar tamaño
  window.addEventListener("resize", () => {
    const saved2 = localStorage.getItem("sidebarOpen");
    if (saved2 === "1") open();
    else close();
  });
});
