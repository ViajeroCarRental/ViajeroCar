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

/* ✅ Formatea YYYY-MM-DD (o YYYY-MM-DDTHH:mm) a dd/mm/aaaa */
const toDMY = (dateStr) => {
  if (!dateStr) return "";
  const s = String(dateStr).trim();
  const iso = s.includes("T") ? s.split("T")[0] : s;
  const parts = iso.split("-");
  if (parts.length !== 3) return s;
  const [y, m, d] = parts;
  if (!y || !m || !d) return s;
  return `${d.padStart(2, "0")}/${m.padStart(2, "0")}/${y}`;
};

/* ==========================================================
   🚀 DOM READY
========================================================== */
window.addEventListener("DOMContentLoaded", () => {
  console.log("✅ JS cargado correctamente - Reservaciones Activas");


  /* ==========================================================
     🗓️ Lista: +
  =========================================================== */
 document.addEventListener("click", function(e) {
  const btn = e.target.closest(".btn-plus");
  if (!btn) return;

  const row = btn.closest(".row");
  const detail = row.nextElementSibling;

  if (!detail || !detail.classList.contains("row-detail")) return;

  const isVisible = detail.style.display === "block";

  detail.style.display = isVisible ? "none" : "block";

  // cambiar + a -
  btn.textContent = isVisible ? "+" : "-";
});

/* ==========================================================
     🗓️ Lista: + // Editar
  =========================================================== */

document.addEventListener("click", function(e) {
  console.log("CLICK DETECTADO", e.target);

  const btn = e.target.closest(".btn-edit-direct");

  if (!btn) {
    console.log("NO es botón editar");
    return;
  }

  console.log("✅ BOTÓN EDITAR DETECTADO");

  const row = btn.closest(".row-detail")?.previousElementSibling;

  console.log("ROW:", row);

  current = {
    codigo: row.dataset.codigo,
    nombre_cliente: row.dataset.cliente,
    email_cliente: row.dataset.email,
    telefono_cliente: row.dataset.numero,
    fecha_inicio: row.dataset.fechaSalida,
    hora_retiro: row.dataset.hora_retiro,
    fecha_fin: row.dataset.fechaFin,
    hora_entrega: row.dataset.hora_entrega
  };

  console.log("🧾 CURRENT:", current);

  openEditModal();
});
/* ==========================================================
     🗓️ Lista: + // Eliminar
  =========================================================== */

  document.addEventListener("click", function(e) {
  const btn = e.target.closest(".btn-delete-direct");
  if (!btn) return;

  const url = btn.dataset.url;

  const form = document.getElementById("aDeleteForm");
  form.action = url;

  // 🔥 envía directamente
  form.submit();
});

/* ==========================================================
     🗓️ Lista: + // Reenviar correo.
  =========================================================== */
window.reenviarCorreo = function(id, btn) {

    if (!confirm("¿Reenviar correo al cliente?")) return;

    btn.disabled = true;
    const originalText = btn.innerHTML;
    btn.innerHTML = "Enviando... ⏳";

    fetch(`/reservaciones/${id}/reenviar-correo`, {
        method: "POST",
        headers: {
            "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').content
        }
    })
    .then(res => res.json())
    .then(data => {
        alert(data.message);
    })
    .catch(() => {
        alert("Error al enviar correo");
    })
    .finally(() => {
        btn.disabled = false;
        btn.innerHTML = originalText;
    });
};

/* ==========================================================
     🗓️ MODAL: Lista de vehiculos
  =========================================================== */
const modalVehiculos = document.getElementById("modalVehiculos");
const tablaVehiculos = document.getElementById("tablaVehiculos");

document.addEventListener("click", async (e) => {

  const btn = e.target.closest(".btn-apartar-auto");
  if (!btn) return;

  modalVehiculos.classList.add("show");

  tablaVehiculos.innerHTML = `<tr><td colspan="13">Cargando...</td></tr>`;

  try {
    const res = await fetch('/admin/vehiculos-disponibles');
    const data = await res.json();

    tablaVehiculos.innerHTML = "";

    data.forEach(v => {
      tablaVehiculos.innerHTML += `
        <tr>
          <td>${v.id_vehiculo}</td>
          <td>${v.placa ?? '-'}</td>
          <td>${v.categoria ?? '-'}</td>
          <td>${v.tamano ?? '-'}</td>
          <td>${v.modelo ?? '-'}</td>
          <td>${v.transmision ?? '-'}</td>
          <td>${v.color ?? '-'}</td>
          <td>${v.gasolina_fraccion ?? 0}/16</td>
          <td>${v.gasolina_actual ?? '-'}</td>
          <td>${v.kilometraje ?? '-'}</td>
          <td>${v.vigencia_verificacion ?? '-'}</td>
          <td>${v.intervalo_km ?? '-'}</td>
          <td>${v.fin_vigencia_poliza ?? '-'}</td>
          <td>
            <button class="btn success btn-select-auto" data-id="${v.id_vehiculo}">
            Seleccionar
            </button>
            </td>
        </tr>
      `;
    });

  } catch (err) {
    console.error(err);
    tablaVehiculos.innerHTML = `<tr><td colspan="13">Error</td></tr>`;
  }
});


document.getElementById("vClose")?.addEventListener("click", () => {
  modalVehiculos.classList.remove("show");
});

document.getElementById("vCancel")?.addEventListener("click", () => {
  modalVehiculos.classList.remove("show");
});


let reservacionSeleccionada = null;

document.addEventListener("click", async (e) => {

  const btn = e.target.closest(".btn-apartar-auto");
  if (!btn) return;

  reservacionSeleccionada = btn.dataset.id;

  modalVehiculos.classList.add("show");
});

document.addEventListener("click", async (e) => {

  const btn = e.target.closest(".btn-select-auto");
  if (!btn) return;

  const idVehiculo = btn.dataset.id;

  if (!reservacionSeleccionada) {
    alert("No hay reservación seleccionada");
    return;
  }

  try {
    const res = await fetch('/admin/crear-contrato', {
      method: "POST",
      headers: {
        "Content-Type": "application/json",
        "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').content
      },
      body: JSON.stringify({
        id_reservacion: reservacionSeleccionada,
        id_vehiculo: idVehiculo
      })
    });

    const data = await res.json();

    if (!data.success) throw new Error(data.message);

    // 🔥 REDIRECCIÓN
    window.location.href = `/admin/reservacion/${data.id_contrato}/checklist?modo=salida&from=apartar`;

  } catch (err) {
    console.error(err);
    alert("Error al crear contrato");
  }
});

  /* ==========================================================
     🗓️ MODAL: RESERVACIONES ANTERIORES
  =========================================================== */
  const modalPrev = $("#modalPrev");
  const btnPrev   = $("#btnPrevBookings");
  const pClose    = $("#pClose");
  const pCancel   = $("#pCancel");

  function openPrevModal(){
    if (!modalPrev) return;
    modalPrev.classList.add("show");
    modalPrev.setAttribute("aria-hidden", "false");
  }
  function closePrevModal(){
    if (!modalPrev) return;
    modalPrev.classList.remove("show");
    modalPrev.setAttribute("aria-hidden", "true");
  }

  btnPrev?.addEventListener("click", openPrevModal);
  pClose?.addEventListener("click", closePrevModal);
  pCancel?.addEventListener("click", closePrevModal);

  modalPrev?.addEventListener("click", (e) => {
    if (e.target === modalPrev) closePrevModal();
  });

  /* ==========================================================
     🔍 FILTRO DE BÚSQUEDA (SOLO TABLA PRINCIPAL)
  =========================================================== */
  $("#q")?.addEventListener("input", () => {
    const q = $("#q").value.trim().toLowerCase();
    const rows = $$("#tablaActivas .tbody .row");
    let visible = 0;

    rows.forEach((row) => {
      const nombre = (row.dataset.cliente || "").toLowerCase();
      const email = (row.dataset.email || "").toLowerCase();
      const estado = (row.dataset.estado || "").toLowerCase();
      const codigo = (row.dataset.codigo || "").toLowerCase();

      const show =
        !q ||
        nombre.includes(q) ||
        email.includes(q) ||
        estado.includes(q) ||
        codigo.includes(q);

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
      const resp = await fetch(`/admin/reservaciones-activas/${encodeURIComponent(codigo)}`);
      if (!resp.ok) throw new Error(`Error ${resp.status}`);

      const data = await resp.json();
      console.log("🧾 Datos recibidos:", data);

      current = data;

      $("#mTitle").textContent = `Detalle Reservación ${data.codigo || "—"}`;
      //$("#mCodigo").textContent = data.codigo || "—";

      const fullName = [data.nombre_cliente, data.apellidos_cliente]
        .filter(Boolean)
        .join(" ")
        .trim();

      //$("#mCliente").textContent = fullName || data.nombre_cliente || "—";
      //$("#mEmail").textContent = data.email_cliente || "—";
      //$("#mNumero").textContent = data.telefono_cliente || "—";
      //$("#mCategoria").textContent = data.categoria || "—";
      //$("#mEstado").textContent = data.estado || "—";

      const salida = data.fecha_inicio
        ? `${toDMY(data.fecha_inicio)} ${String(data.hora_retiro || "").slice(0, 5)}`
        : "—";
      const entrega = data.fecha_fin
        ? `${toDMY(data.fecha_fin)} ${String(data.hora_entrega || "").slice(0, 5)}`
        : "—";

      //$("#mSalida").textContent = salida;
      //$("#mEntrega").textContent = entrega;

      $("#mFechas").textContent = `${salida} al ${entrega}`;
      $("#mVehiculo").textContent = `${data.categoria || ""} ${data.categoria_nombre || ""} ${data.categoria_descripcion || ""}`;
      $("#mFormaPago").textContent = data.metodo_pago || "—";

      //$("#mTotal").textContent = Fmx(data.total);

      //$("#mTarifaModificada").textContent = data.tarifa_modificada
        //? Fmx(data.tarifa_modificada)
        //: "—";

      $("#modal").classList.add("show");
      console.log("🪟 Modal abierto con reservación:", current);
    } catch (err) {
      console.error("❌ Error al obtener detalles de la reservación:", err);
      alert("Error al obtener la información de la reservación. Intente nuevamente.");
    }
  }

  function closeModal() {
    $("#modal").classList.remove("show");
    console.log("❎ Modal cerrado");
  }

  $("#mClose")?.addEventListener("click", closeModal);
  $("#mCancel")?.addEventListener("click", closeModal);

  /* ==========================================================
     ✅ CLIC EN FILA (FUNCIONA PARA AMBAS TABLAS)
     - Tabla principal y tabla del modal
  =========================================================== */
  document.addEventListener("click", (ev) => {
    const row = ev.target.closest(".table .tbody .row");
    if (!row) return;

    // No abrir si se clickeó algo interactivo dentro de la fila
    if (ev.target.closest("button, a, form, input, select, textarea")) return;

    openModal(row);
  });

  /* ==========================================================
     🚪 CAPTURAR CONTRATO
  =========================================================== */
  $("#mGo")?.addEventListener("click", () => {
    if (!current) return;
    const url = `/admin/contrato/${encodeURIComponent(current.id_reservacion)}`;
    console.log("➡️ Redirigiendo a vista Contrato:", url);
    window.location.href = url;
  });

  /* ==========================================================
     ✏️ MODAL EDICIÓN
  =========================================================== */
  function openEditModal() {
    if (!current) return;

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

      Object.assign(current, payload);

      $("#mCliente").textContent = current.nombre_cliente;
      $("#mEmail").textContent = current.email_cliente;
      $("#mNumero").textContent = current.telefono_cliente;

      const salida = `${toDMY(current.fecha_inicio)} ${String(current.hora_retiro || "").slice(0, 5)}`;
      const entrega = `${toDMY(current.fecha_fin)} ${String(current.hora_entrega || "").slice(0, 5)}`;

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
     🧩 MODAL ACCIONES (⋯) - MISMO PARA AMBAS TABLAS
  =========================================================== */
  const modalActions = $("#modalActions");
  const aClose = $("#aClose");
  const aCancel = $("#aCancel");
  const aCodigo = $("#aCodigo");
  const aIdReservacion = $("#aIdReservacion");
  const aDeleteForm = $("#aDeleteForm");

  const aExtraFields = $("#aExtraFields");
  const aComentarios = $("#aComentarios");
  const aEliminadoPor = $("#aEliminadoPor");
  const aAccion = $("#aAccion");

  function openActionsModal({ id, codigo, deleteUrl }) {
    if (!modalActions) return;

    if (aCodigo) aCodigo.textContent = codigo || "—";
    if (aIdReservacion) aIdReservacion.value = id || "";

    if (aDeleteForm && deleteUrl) aDeleteForm.setAttribute("action", deleteUrl);

    if (aExtraFields) aExtraFields.style.display = "none";
    if (aComentarios) aComentarios.value = "";
    if (aEliminadoPor) aEliminadoPor.value = "";
    if (aAccion) aAccion.value = "";

    modalActions.classList.add("show");
    modalActions.setAttribute("aria-hidden", "false");
  }

  function closeActionsModal() {
    if (!modalActions) return;
    modalActions.classList.remove("show");
    modalActions.setAttribute("aria-hidden", "true");
  }

  // ✅ Delegación: funciona para los ⋯ de ambas tablas
  document.addEventListener("click", (ev) => {
    const btn = ev.target.closest("[data-open-actions]");
    if (!btn) return;

    ev.stopPropagation();
    openActionsModal({
      id: btn.dataset.id,
      codigo: btn.dataset.codigo,
      deleteUrl: btn.dataset.deleteUrl,
    });
  });

  aClose?.addEventListener("click", closeActionsModal);
  aCancel?.addEventListener("click", closeActionsModal);

  modalActions?.addEventListener("click", (e) => {
    if (e.target === modalActions) closeActionsModal();
  });

  aDeleteForm?.addEventListener("submit", (e) => {
    const codigo = aCodigo?.textContent || "esta reservación";
    if (!confirm(`¿Seguro que deseas ELIMINAR ${codigo}?`)) {
      e.preventDefault();
    }
  });

  const csrf = document.querySelector('meta[name="csrf-token"]')?.content;

  async function postAccion(url, payload = null) {
    const res = await fetch(url, {
      method: "POST",
      headers: {
        "X-CSRF-TOKEN": csrf,
        "Accept": "application/json",
        ...(payload ? { "Content-Type": "application/json" } : {}),
      },
      ...(payload ? { body: JSON.stringify(payload) } : {}),
    });

    const data = await res.json().catch(() => ({}));

    if (!res.ok || !data.success) {
      throw new Error(data.message || "Error al ejecutar la acción");
    }

    return data;
  }

  function showExtras(tipo) {
    if (aExtraFields) aExtraFields.style.display = "grid";
    if (aAccion) aAccion.value = tipo; // "no-show" | "cancelar"
  }

  function getExtrasOrStop() {
    const comentarios = (aComentarios?.value || "").trim();
    const eliminado_por = (aEliminadoPor?.value || "").trim();

    if (!comentarios) { alertify.error("Agrega comentarios"); return null; }
    if (!eliminado_por) { alertify.error("Selecciona quién lo eliminó"); return null; }

    return { comentarios, eliminado_por };
  }

  $("#aNoShow")?.addEventListener("click", async (ev) => {
    ev.stopPropagation();
    const id = aIdReservacion?.value;
    const codigo = aCodigo?.textContent || "—";
    if (!id) return;

    if (aAccion?.value !== "no-show") {
      showExtras("no-show");
      return;
    }

    const payload = getExtrasOrStop();
    if (!payload) return;

    if (!confirm(`¿Marcar ${codigo} como NO SHOW?`)) return;

    try {
      await postAccion(`/admin/reservaciones-activas/${id}/no-show`, payload);
      alertify.success("Marcada como No Show");
      window.location.reload();
    } catch (e) {
      console.error(e);
      alertify.error(e.message);
    }
  });

  $("#aCancelar")?.addEventListener("click", async (ev) => {
    ev.stopPropagation();
    const id = aIdReservacion?.value;
    const codigo = aCodigo?.textContent || "—";
    if (!id) return;

    if (aAccion?.value !== "cancelar") {
      showExtras("cancelar");
      return;
    }

    const payload = getExtrasOrStop();
    if (!payload) return;

    if (!confirm(`¿Cancelar ${codigo}?`)) return;

    try {
      await postAccion(`/admin/reservaciones-activas/${id}/cancelar`, payload);
      alertify.success("Reservación cancelada");
      window.location.reload();
    } catch (e) {
      console.error(e);
      alertify.error(e.message);
    }
  });

});
