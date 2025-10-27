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
     🧾 MODAL DE DETALLE (ahora con datos reales desde backend)
  =========================================================== */
  let current = null;

  async function openModal(row) {
    const codigo = row.dataset.codigo?.trim();
    if (!codigo) {
      console.warn("⚠️ No se encontró código en la fila seleccionada");
      return;
    }

    console.log(`📦 Consultando reservación ${codigo}...`);

    try {
      const resp = await fetch(`/admin/reservaciones-activas/${encodeURIComponent(codigo)}`);
      if (!resp.ok) throw new Error(`Error ${resp.status}`);

      const data = await resp.json();
      console.log("🧾 Datos recibidos:", data);

      // Guardar la reservación actual
      current = data;

      // 🧩 Construcción de campos dinámicos
      $("#mTitle").textContent = `Contrato Reservación ${data.codigo || "—"}`;
      $("#mCodigo").textContent = data.codigo || "—";
      $("#mCliente").textContent = data.nombre_cliente || "—";
      $("#mEmail").textContent = data.email_cliente || "—";
      $("#mEstado").textContent = data.estado || "—";

      const fechaInicio = data.fecha_inicio ? `${data.fecha_inicio} ${data.hora_retiro || ""}` : "";
      const fechaFin = data.fecha_fin ? `${data.fecha_fin} ${data.hora_entrega || ""}` : "";
      $("#mFechas").textContent = fechaInicio && fechaFin ? `${fechaInicio} a ${fechaFin}` : "—";

      $("#mVehiculo").textContent = data.vehiculo || "—";
      $("#mFormaPago").textContent = data.metodo_pago || "—";
      $("#mTotal").textContent = Fmx(data.total);

      $("#modal").classList.add("show");
      console.log("🪟 Modal abierto con reservación:", current);

    } catch (err) {
      console.error("❌ Error al obtener detalles de la reservación:", err);
      alert("Error al obtener la información de la reservación. Intente nuevamente.");
    }
  }

  /* ==========================================================
     ❌ CERRAR MODAL
  =========================================================== */
  function closeModal() {
    $("#modal").classList.remove("show");
    console.log("❎ Modal cerrado");
  }

  $("#mClose")?.addEventListener("click", closeModal);
  $("#mCancel")?.addEventListener("click", closeModal);

  /* ==========================================================
     🪟 ABRIR MODAL AL HACER CLIC EN UNA FILA
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
     🗑️ ELIMINAR (solo mensaje visual por ahora)
  =========================================================== */
  $("#mDel")?.addEventListener("click", () => {
    if (!current) return;
    alert(`🗑️ Reservación ${current.codigo} eliminada (solo vista, sin acción real).`);
    closeModal();
  });
});
