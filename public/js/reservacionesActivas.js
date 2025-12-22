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
      const nombre = row.children[1]?.textContent?.toLowerCase() || "";
      const email = row.children[2]?.textContent?.toLowerCase() || "";
      const estado = row.children[7]?.textContent?.toLowerCase() || "";
      const show =
        !q || nombre.includes(q) || email.includes(q) || estado.includes(q);
      row.style.display = show ? "grid" : "none";
      if (show) visible++;
    });

    const count = $("#count");
    if (count) count.textContent = visible;
  });

  /* ==========================================================
     🧾 MODAL DE DETALLE
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
      const resp = await fetch(
        `/admin/reservaciones-activas/${encodeURIComponent(codigo)}`
      );
      if (!resp.ok) throw new Error(`Error ${resp.status}`);

      const data = await resp.json();
      console.log("🧾 Datos recibidos:", data);

      // Guardar la reservación actual
      current = data;

      /* ==========================================================
         🧩 RELLENAR CAMPOS DEL MODAL
      =========================================================== */
      $("#mTitle").textContent = `Detalle Reservación ${data.codigo || "—"}`;
      $("#mCodigo").textContent = data.codigo || "—";
      $("#mCliente").textContent = data.nombre_cliente || "—";
      $("#mEmail").textContent = data.email_cliente || "—";
      $("#mNumero").textContent = data.telefono_cliente || "—";
      $("#mCategoria").textContent = data.categoria || "—";
      $("#mEstado").textContent = data.estado || "—";

      const salida = data.fecha_inicio
        ? `${data.fecha_inicio} ${data.hora_retiro || ""}`
        : "—";
      const entrega = data.fecha_fin
        ? `${data.fecha_fin} ${data.hora_entrega || ""}`
        : "—";

      $("#mSalida").textContent = salida;
      $("#mEntrega").textContent = entrega;

      $("#mFormaPago").textContent = data.metodo_pago || "—";
      $("#mTotal").textContent = Fmx(data.total);

      $("#mTarifaModificada").textContent = data.tarifa_modificada
        ? Fmx(data.tarifa_modificada)
        : "—";

      // Mostrar modal
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

    const url = `/admin/contrato/${encodeURIComponent(
      current.id_reservacion
    )}`;
    console.log("➡️ Redirigiendo a vista Contrato:", url);
    window.location.href = url;
  });

  /* ==========================================================
     ✏️ MODAL EDICIÓN (solo datos permitidos)
  =========================================================== */
  function openEditModal() {
    if (!current) return;

    // Precargar inputs
    $("#eTitle").textContent = `Editar ${current.codigo || ""}`;
    $("#eNombre").value = current.nombre_cliente || "";
    $("#eCorreo").value = current.email_cliente || "";
    $("#eTelefono").value = current.telefono_cliente || "";

    $("#eFechaInicio").value = current.fecha_inicio || "";
    $("#eHoraRetiro").value = (current.hora_retiro || "").slice(0, 5);

    $("#eFechaFin").value = current.fecha_fin || "";
    $("#eHoraEntrega").value = (current.hora_entrega || "").slice(0, 5);

    $("#modalEdit").classList.add("show");
  }

  function closeEditModal() {
    $("#modalEdit").classList.remove("show");
  }

  $("#mEdit")?.addEventListener("click", openEditModal);
  $("#eClose")?.addEventListener("click", closeEditModal);
  $("#eCancel")?.addEventListener("click", closeEditModal);

  /* ==========================================================
     💾 GUARDAR CAMBIOS (PUT)
  =========================================================== */
  $("#eSave")?.addEventListener("click", async () => {
    if (!current) return;

    const payload = {
      nombre_cliente: $("#eNombre").value.trim(),
      email_cliente: $("#eCorreo").value.trim(),
      telefono_cliente: $("#eTelefono").value.trim(),
      fecha_inicio: $("#eFechaInicio").value,
      hora_retiro: $("#eHoraRetiro").value,
      fecha_fin: $("#eFechaFin").value,
      hora_entrega: $("#eHoraEntrega").value
    };

    if (!payload.nombre_cliente || !payload.email_cliente || !payload.telefono_cliente) {
      alertify.error("Completa nombre, correo y teléfono");
      return;
    }

    if (!payload.fecha_inicio || !payload.fecha_fin) {
      alertify.error("Completa fecha de salida y entrega");
      return;
    }

    if (payload.fecha_fin < payload.fecha_inicio) {
      alertify.warning("La fecha de entrega no puede ser menor que la de salida");
      return;
    }

    try {
      const res = await fetch(`/admin/reservaciones-activas/${current.id_reservacion}`, {
        method: "PUT",
        headers: {
          "Content-Type": "application/json",
          "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').content
        },
        body: JSON.stringify(payload)
      });

      const data = await res.json();
      if (!res.ok || !data.success) throw new Error(data.message);

      // Actualizar estado local
      Object.assign(current, payload);

      $("#mCliente").textContent = current.nombre_cliente;
      $("#mEmail").textContent = current.email_cliente;
      $("#mNumero").textContent = current.telefono_cliente;

      const salida = `${current.fecha_inicio} ${current.hora_retiro || ""}`;
      const entrega = `${current.fecha_fin} ${current.hora_entrega || ""}`;

      $("#mSalida").textContent = salida;
      $("#mEntrega").textContent = entrega;

      alertify.success("Reservación actualizada correctamente");
      closeEditModal();

    } catch (err) {
      console.error(err);
      alertify.error(err.message || "Error al guardar la reservación");
    }
  });

  /* ==========================================================
     🧩 MODAL ACCIONES (⋯) + CONFIRMACIONES + POST
  =========================================================== */
  const modalActions = $("#modalActions");
  const aClose = $("#aClose");
  const aCancel = $("#aCancel");
  const aCodigo = $("#aCodigo");
  const aIdReservacion = $("#aIdReservacion");
  const aDeleteForm = $("#aDeleteForm");

  function openActionsModal({ id, codigo, deleteUrl }) {
    if (!modalActions) return;

    if (aCodigo) aCodigo.textContent = codigo || "—";
    if (aIdReservacion) aIdReservacion.value = id || "";

    // ✅ Conecta tu DELETE existente a la reservación seleccionada
    if (aDeleteForm && deleteUrl) aDeleteForm.setAttribute("action", deleteUrl);

    modalActions.classList.add("show");
    modalActions.setAttribute("aria-hidden", "false");
  }

  function closeActionsModal() {
    if (!modalActions) return;
    modalActions.classList.remove("show");
    modalActions.setAttribute("aria-hidden", "true");
  }

  // Abrir modal desde cada ⋯
  $$("[data-open-actions]").forEach((btn) => {
    btn.addEventListener("click", (ev) => {
      ev.stopPropagation();
      openActionsModal({
        id: btn.dataset.id,
        codigo: btn.dataset.codigo,
        deleteUrl: btn.dataset.deleteUrl,
      });
    });
  });

  // Cerrar modal
  aClose?.addEventListener("click", closeActionsModal);
  aCancel?.addEventListener("click", closeActionsModal);

  // Cerrar al dar click fuera
  modalActions?.addEventListener("click", (e) => {
    if (e.target === modalActions) closeActionsModal();
  });

  // ✅ Confirmación (NO se quita) al eliminar
  aDeleteForm?.addEventListener("submit", (e) => {
    const codigo = aCodigo?.textContent || "esta reservación";
    if (!confirm(`¿Seguro que deseas ELIMINAR ${codigo}?`)) {
      e.preventDefault();
    }
  });

  // ✅ CSRF para POST
  const csrf = document.querySelector('meta[name="csrf-token"]')?.content;

  async function postAccion(url) {
    const res = await fetch(url, {
      method: "POST",
      headers: {
        "X-CSRF-TOKEN": csrf,
        "Accept": "application/json",
      },
    });

    const data = await res.json().catch(() => ({}));

    if (!res.ok || !data.success) {
      throw new Error(data.message || "Error al ejecutar la acción");
    }

    return data;
  }

  // 🚫 No Show (POST)
  $("#aNoShow")?.addEventListener("click", async (ev) => {
    ev.stopPropagation();
    const id = aIdReservacion?.value;
    const codigo = aCodigo?.textContent || "—";
    if (!id) return;

    if (!confirm(`¿Marcar ${codigo} como NO SHOW?`)) return;

    try {
      await postAccion(`/admin/reservaciones-activas/${id}/no-show`);
      alertify.success("Marcada como No Show");
      window.location.reload();
    } catch (e) {
      console.error(e);
      alertify.error(e.message);
    }
  });

  // ⚠️ Cancelar (POST)
  $("#aCancelar")?.addEventListener("click", async (ev) => {
    ev.stopPropagation();
    const id = aIdReservacion?.value;
    const codigo = aCodigo?.textContent || "—";
    if (!id) return;

    if (!confirm(`¿Cancelar ${codigo}?`)) return;

    try {
      await postAccion(`/admin/reservaciones-activas/${id}/cancelar`);
      alertify.success("Reservación cancelada");
      window.location.reload();
    } catch (e) {
      console.error(e);
      alertify.error(e.message);
    }
  });

});
