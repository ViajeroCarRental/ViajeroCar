/* ==========================================================
   🎨 UTILIDADES BÁSICAS
========================================================== */
const $ = (s) => document.querySelector(s);
const $$ = (s) => Array.from(document.querySelectorAll(s));
const Fmx = (v) =>
  "$" +
  Number(v || 0).toLocaleString("es-MX", {
    minimumFractionDigits: 2,
    maximumFractionDigits: 2,
  }) +
  " MXN";
const esc = (s) =>
  (s ?? "").toString().replace(/[&<>"]/g, (m) => ({
    "&": "&amp;",
    "<": "&lt;",
    ">": "&gt;",
    '"': "&quot;",
  }[m]));

/* ==========================================================
   🚀 ESPERAR A QUE EL DOM ESTÉ LISTO
========================================================== */
window.addEventListener("DOMContentLoaded", () => {
  console.log("✅ JS cargado correctamente - Reservaciones Activas");

  /* ==========================================================
     🔍 FILTRO DE BÚSQUEDA (nombre, correo o estado)
  =========================================================== */
  $("#q")?.addEventListener("input", () => {
    const q = $("#q").value.trim().toLowerCase();
    const rows = $$(".tbody .row");
    let visible = 0;

    rows.forEach((row) => {
      const nombre = row.children[2]?.textContent?.toLowerCase() || "";
      const email = row.children[3]?.textContent?.toLowerCase() || "";
      const estado = row.children[4]?.textContent?.toLowerCase() || "";
      const show = !q || nombre.includes(q) || email.includes(q) || estado.includes(q);
      row.style.display = show ? "grid" : "none";
      if (show) visible++;
    });

    const count = $("#count");
    if (count) count.textContent = visible;
  });

  /* ==========================================================
     🧾 MODAL DE DETALLE (versión extendida)
  =========================================================== */
  let current = null;

  function openModal(row) {
    const codigo = row.children[0]?.textContent || "—";
    const fecha = row.children[1]?.textContent || "—";
    const cliente = row.children[2]?.textContent || "—";
    const email = row.children[3]?.textContent || "—";
    const estado = row.children[4]?.textContent || "—";
    const total = row.children[5]?.textContent || "—";

    // 🔹 Datos adicionales (simulados por ahora)
    const fechas = `${fecha} 08:00 HRS al ${fecha} 11:00 HRS`;
    const vehiculo = "C | COMPACTO AUTOMÁTICO - CHEVROLET Aveo";
    const formaPago = "OFICINA";

    current = { codigo, cliente, email, estado, total, fecha, fechas, vehiculo, formaPago };

    $("#mTitle").textContent = `Contrato Reservación ${codigo}`;
    $("#mBody").innerHTML = `
      <div class="kv"><div>Fechas</div><div>${esc(fechas)}</div></div>
      <div class="kv"><div>Vehículo</div><div>${esc(vehiculo)}</div></div>
      <div class="kv"><div>Forma Pago</div><div>${esc(formaPago)}</div></div>
      <div class="kv"><div>Total</div><div>${esc(total)}</div></div>
    `;

    $("#modal").classList.add("show");
    console.log("🪟 Modal abierto:", current);
  }

  /* ==========================================================
     ❌ Cerrar modal
  =========================================================== */
  function closeModal() {
    $("#modal").classList.remove("show");
    console.log("❎ Modal cerrado");
  }

  $("#mClose")?.addEventListener("click", closeModal);
  $("#mCancel")?.addEventListener("click", closeModal);

  /* ==========================================================
     🪟 Abrir modal al hacer clic en una fila
  =========================================================== */
  $$(".tbody .row").forEach((row) => {
    row.addEventListener("click", (ev) => {
      if (["A", "BUTTON", "FORM"].includes(ev.target.tagName)) return;
      openModal(row);
    });
  });

  /* ==========================================================
     🚪 CAPTURAR CONTRATO (redirige visualmente)
  =========================================================== */
  $("#mGo")?.addEventListener("click", () => {
    if (!current) return;
    const url = `/admin/contrato?codigo=${encodeURIComponent(current.codigo)}`;
    console.log("➡️ Redirigiendo a:", url);
    window.location.href = url;
  });

  /* ==========================================================
     🗑️ ELIMINAR (solo mensaje visual)
  =========================================================== */
  $("#mDel")?.addEventListener("click", () => {
    if (!current) return;
    alert(`🗑️ Reservación ${current.codigo} eliminada (solo vista, sin acción real).`);
    closeModal();
  });
});
