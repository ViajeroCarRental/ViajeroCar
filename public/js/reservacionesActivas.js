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
    🗓️ FILTRO FECHA CON FLATPICKR
  ========================================================== */
  function initFiltroFechaFlatpickr() {
    if (!window.flatpickr) return;

    const inputUI = document.getElementById("filtro_fecha_ui");
    const inputHidden = document.getElementById("filtro_fecha");
    const form = document.querySelector("form.toolbar");

    if (!inputUI || !inputHidden || !form) return;

    let backdrop = document.querySelector(".fp-backdrop");

    if (!backdrop) {
      backdrop = document.createElement("div");
      backdrop.className = "fp-backdrop";
      document.body.appendChild(backdrop);
    }

    function openModal(instance) {
      backdrop.classList.add("is-open");
      document.body.classList.add("no-scroll");
      backdrop.onclick = () => instance.close();
    }

    function closeModal() {
      backdrop.classList.remove("is-open");
      document.body.classList.remove("no-scroll");
      backdrop.onclick = null;
    }

    function makeActions(instance) {
      const actions = document.createElement("div");
      actions.className = "fp-actions";

      actions.innerHTML = `
        <button type="button" class="fp-today">Hoy</button>
        <button type="button" class="fp-clear">Limpiar</button>
        <button type="button" class="fp-label">✖ Fecha</button>
      `;

      actions.querySelector(".fp-today").addEventListener("click", () => {
        instance.setDate(new Date(), true);
      });

      actions.querySelector(".fp-clear").addEventListener("click", () => {
        instance.clear();
        inputHidden.value = "";
        form.submit();
      });

      return actions;
    }

    flatpickr(inputUI, {
      locale: "es",
      dateFormat: "d-M-Y",
      allowInput: false,
      clickOpens: true,
      minDate: "today",

      onOpen: (selectedDates, dateStr, instance) => {
        openModal(instance);

        if (!instance._actionsAdded) {
          instance.calendarContainer.appendChild(makeActions(instance));
          instance._actionsAdded = true;
        }
      },

      onClose: () => closeModal(),

      onChange: (selectedDates) => {
        const d = selectedDates?.[0];

        if (d) {
          const year = d.getFullYear();
          const month = String(d.getMonth() + 1).padStart(2, "0");
          const day = String(d.getDate()).padStart(2, "0");

          inputHidden.value = `${year}-${month}-${day}`;
        } else {
          inputHidden.value = "";
        }

        form.submit();
      }
    });
  }

  initFiltroFechaFlatpickr();


  /* ==========================================================
     🗓️ Lista: +
  =========================================================== */
  document.addEventListener("click", function (e) {
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

  document.addEventListener("click", function (e) {
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

  document.addEventListener("click", function (e) {
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
  window.reenviarCorreo = function (id, btn) {

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
        const fraccion = v.gasolina_fraccion ?? 0;   // nivel 0-16 (lo calcula el controlador)
        const litros = v.gasolina_actual ?? 0;     // litros reales
        const km = v.kilometraje ?? 0;

        tablaVehiculos.innerHTML += `
          <tr data-id-vehiculo="${v.id_vehiculo}"
              data-placa="${v.placa ?? '-'}"
              data-color="${v.color ?? '-'}"
              data-categoria="${v.tamano ?? v.categoria ?? '-'}"
              data-gas-original="${fraccion}"
              data-km-original="${km}">
            <td>${v.placa ?? '-'}</td>
            <td>${v.categoria ?? '-'}</td>
            <td>${v.tamano ?? '-'}</td>
            <td>${v.modelo ?? '-'}</td>
            <td>${v.transmision ?? '-'}</td>
            <td>${v.color ?? '-'}</td>
            <td class="celda-editable" data-tipo="gas">
              <span class="celda-valor">${fraccion}/16</span>
              <button type="button" class="btn-edit-inline" style="background:none;border:none;color:#D6121F;cursor:pointer;margin-left:4px;font-size:14px;">✏️</button>
            </td>
            <td class="celda-litros">${litros}</td>
            <td class="celda-editable" data-tipo="km">
              <span class="celda-valor">${Number(km).toLocaleString()}</span>
              <button type="button" class="btn-edit-inline" style="background:none;border:none;color:#D6121F;cursor:pointer;margin-left:4px;font-size:14px;">✏️</button>
            </td>
            <td>${v.vigencia_verificacion ?? '-'}</td>
            <td>${v.intervalo_km ?? '-'}</td>
            <td>${v.fin_vigencia_poliza ?? '-'}</td>
            <td>
              <button class="btn success btn-select-auto" data-id="${v.id_vehiculo}">Seleccionar</button>
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
   ✏️ EDICIÓN INLINE INVENTARIO (gas / km) — Modal Apartar
=========================================================== */
  (function initEdicionInventarioRA() {
    const tbody = document.getElementById("tablaVehiculos");
    const modalConf = document.getElementById("modalConfirmInv");
    if (!tbody || !modalConf) return;

    const csrf = document.querySelector('meta[name="csrf-token"]')?.content;
    let pendiente = null;

    const activar = (celda) => {
      if (celda.querySelector("input")) return;
      const fila = celda.closest("tr");
      const span = celda.querySelector(".celda-valor");
      const tipo = celda.dataset.tipo;
      const original = tipo === "gas"
        ? (fila.dataset.gasOriginal || "0")
        : (fila.dataset.kmOriginal || "0");

      const input = document.createElement("input");
      input.type = "number"; input.value = original; input.min = "0";
      if (tipo === "gas") input.max = "16";
      Object.assign(input.style, { width: "70px", border: "1px solid #D6121F", borderRadius: "6px", padding: "3px 6px", fontWeight: "bold", textAlign: "center" });

      span.style.display = "none";
      celda.querySelector(".btn-edit-inline").style.display = "none";
      celda.appendChild(input);
      input.focus(); input.select();

      const cancelar = () => {
        input.remove();
        span.style.display = "";
        celda.querySelector(".btn-edit-inline").style.display = "";
      };

      const confirmar = () => {
        const nuevo = parseFloat(input.value);
        const orig = parseFloat(original);
        if (isNaN(nuevo) || nuevo === orig) { cancelar(); return; }
        if (tipo === "gas" && (nuevo < 0 || nuevo > 16)) { alert("La gasolina debe estar entre 0 y 16."); cancelar(); return; }
        if (nuevo < 0) { cancelar(); return; }

        pendiente = {
          fila, celda, tipo, span,
          idVehiculo: fila.dataset.idVehiculo,
          valorNuevo: nuevo,           // para km
          fraccionNueva: nuevo,        // para gas (nivel 0-16)
          restaurar: cancelar,
        };

        document.getElementById("ciCategoria").textContent = fila.dataset.categoria;
        document.getElementById("ciColor").textContent = fila.dataset.color;
        document.getElementById("ciPlacas").textContent = fila.dataset.placa;
        document.getElementById("ciCampoLabel").textContent = tipo === "gas" ? "Gasolina" : "Kilometraje";
        document.getElementById("ciAnterior").textContent = tipo === "gas" ? `${orig}/16` : orig.toLocaleString();
        document.getElementById("ciNuevo").textContent = tipo === "gas" ? `${nuevo}/16` : nuevo.toLocaleString();

        modalConf.classList.add("show");
      };

      input.addEventListener("keydown", (e) => { if (e.key === "Enter") confirmar(); if (e.key === "Escape") cancelar(); });
      input.addEventListener("blur", confirmar);
    };

    tbody.addEventListener("click", (e) => {
      const btn = e.target.closest(".btn-edit-inline");
      if (!btn) return;
      e.stopPropagation();
      activar(btn.closest(".celda-editable"));
    });

    const cerrar = (restaurar = true) => {
      modalConf.classList.remove("show");
      if (restaurar && pendiente) pendiente.restaurar();
      pendiente = null;
    };
    document.getElementById("ciCancel")?.addEventListener("click", () => cerrar(true));
    document.getElementById("ciClose")?.addEventListener("click", () => cerrar(true));
    modalConf.addEventListener("click", (e) => { if (e.target === modalConf) cerrar(true); });

    document.getElementById("ciConfirm")?.addEventListener("click", async () => {
      if (!pendiente) return;
      const p = pendiente;
      const btn = document.getElementById("ciConfirm");
      btn.disabled = true; const txt = btn.innerHTML; btn.innerHTML = "Guardando...";
      try {
        const res = await fetch("/admin/vehiculo/actualizar-inventario", {
          method: "POST",
          headers: { "Content-Type": "application/json", "X-CSRF-TOKEN": csrf },
          body: JSON.stringify({
            id_vehiculo: p.idVehiculo,
            campo: p.tipo === "gas" ? "gasolina" : "kilometraje",
            valor: p.tipo === "gas" ? p.fraccionNueva : p.valorNuevo,  // nivel 0-16 para gas
          }),
        });
        const data = await res.json();
        if (res.ok && (data.success || data.ok)) {
          if (p.tipo === "gas") {
            p.span.textContent = `${p.fraccionNueva}/16`;
            p.fila.dataset.gasOriginal = p.fraccionNueva;
            const cl = p.fila.querySelector(".celda-litros");
            if (cl) cl.textContent = data.litros ?? cl.textContent;   // litros del servidor
          } else {
            p.span.textContent = p.valorNuevo.toLocaleString();
            p.fila.dataset.kmOriginal = p.valorNuevo;
          }
          p.celda.querySelector("input")?.remove();
          p.span.style.display = "";
          p.celda.querySelector(".btn-edit-inline").style.display = "";
          window.alertify?.success?.("Inventario actualizado.");
          modalConf.classList.remove("show");
          pendiente = null;
        } else {
          throw new Error(data.error || "Error backend");
        }
      } catch (err) {
        console.error(err);
        alert("No se pudo guardar: " + err.message);
        cerrar(true);
      } finally {
        btn.disabled = false; btn.innerHTML = txt;
      }
    });
  })();

  /* ==========================================================
     🗓️ MODAL: RESERVACIONES ANTERIORES
  =========================================================== */
  const modalPrev = $("#modalPrev");
  const btnPrev = $("#btnPrevBookings");
  const pClose = $("#pClose");
  const pCancel = $("#pCancel");

  function openPrevModal() {
    if (!modalPrev) return;
    modalPrev.classList.add("show");
    modalPrev.setAttribute("aria-hidden", "false");
  }
  function closePrevModal() {
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
     🔍 BÚSQUEDA EN VIVO (AJAX) - nombre, correo y código
     Busca en TODAS las fechas (trae reservaciones pasadas/futuras).
     Con búsqueda vacía, restaura las filas originales del servidor.
  =========================================================== */
  (function initBusquedaAjax() {
    const inputQ = $("#q");
    const tbody = $("#tablaActivas .tbody");
    const count = $("#count");
    if (!inputQ || !tbody) return;

    const esAeropuerto = new URLSearchParams(location.search).get("sucursal") === "1";

    let debounceTimer = null;
    let ultimoController = null;
    const filasOriginales = tbody.innerHTML; // respaldo de lo que carga el servidor

    const escapeHtml = (s) =>
      String(s ?? "")
        .replace(/&/g, "&amp;")
        .replace(/</g, "&lt;")
        .replace(/>/g, "&gt;")
        .replace(/"/g, "&quot;")
        .replace(/'/g, "&#039;");

    const fmtFecha = (f) => {
      if (!f) return "—";
      const d = new Date(String(f).includes("T") ? f : f + "T00:00:00");
      if (isNaN(d)) return f;
      const meses = ["ene", "feb", "mar", "abr", "may", "jun", "jul", "ago", "sep", "oct", "nov", "dic"];
      return `${String(d.getDate()).padStart(2, "0")}-${meses[d.getMonth()]}-${d.getFullYear()}`;
    };

    // Fecha CON hora, minutos y segundos (para la fecha de creación)
    const fmtFechaHora = (f) => {
      if (!f) return "—";
      const d = new Date(String(f).replace(" ", "T"));
      if (isNaN(d)) return f;
      const meses = ["ene", "feb", "mar", "abr", "may", "jun", "jul", "ago", "sep", "oct", "nov", "dic"];
      const fecha = `${String(d.getDate()).padStart(2, "0")}-${meses[d.getMonth()]}-${d.getFullYear()}`;
      const hora = `${String(d.getHours()).padStart(2, "0")}:${String(d.getMinutes()).padStart(2, "0")}:${String(d.getSeconds()).padStart(2, "0")}`;
      return `${fecha} ${hora}`;
    };

    const fmtHora = (h) => (h ? String(h).slice(0, 5) : "—");

    const colorEstado = (estado) => {
      switch (estado) {
        case "confirmada": return "ok";
        case "pendiente_pago": return "warn";
        case "hold": return "gray";
        case "cancelada": return "danger";
        default: return "gray";
      }
    };

    const oficinaHtml = (of) => {
      if (of === "AIQ") return `<span class="oficina-icon"><i class="fa-solid fa-plane"></i> AIQ</span>`;
      if (of === "TAQ") return `<span class="oficina-icon"><i class="fa-solid fa-bus" style="color:black;"></i> TAQ</span>`;
      if (of === "OCP") return `<span class="oficina-icon"><i class="fa-solid fa-building"></i> OCP</span>`;
      return "—";
    };

    function construirFila(r) {
      const nombre = r.nombre_completo && r.nombre_completo !== ""
        ? r.nombre_completo
        : (r.nombre_cliente || "—");

      const vueloCol = esAeropuerto ? `<div>${escapeHtml(r.no_vuelo || "—")}</div>` : "";

      const totalFmt = "$" + Number(r.total || 0).toLocaleString("es-MX", {
        minimumFractionDigits: 2, maximumFractionDigits: 2,
      }) + " MXN";

      const costoOnline = "$" + Number(r.precio_dia || 0).toLocaleString("es-MX", {
        minimumFractionDigits: 2, maximumFractionDigits: 2,
      });
      const costoOficina = "$" + Number((r.precio_dia || 0) * 1.15).toLocaleString("es-MX", {
        minimumFractionDigits: 2, maximumFractionDigits: 2,
      });

      let extrasHtml = `<span style="color:#999;">Ninguno</span>`;
      if (r.extras && r.extras.length) {
        extrasHtml = r.extras.map(e => `<div>- ${escapeHtml(e.nombre)} (x${escapeHtml(e.cantidad)})</div>`).join("");
      }

      const estadoTxt = (r.estado || "").charAt(0).toUpperCase() + (r.estado || "").slice(1);

      return `
        <div class="row"
          data-codigo="${escapeHtml(r.codigo)}"
          data-cliente="${escapeHtml(nombre)}"
          data-email="${escapeHtml(r.email_cliente || "")}"
          data-numero="${escapeHtml(r.telefono_cliente || "")}"
          data-categoria="${escapeHtml(r.categoria || "")}"
          data-fecha-salida="${escapeHtml(r.fecha_inicio_ymd || "")}"
          data-estado="${escapeHtml(r.estado || "")}"
          data-sucursal="${escapeHtml(r.sucursal_retiro || "")}"
          data-hora_retiro="${escapeHtml(r.hora_retiro || "")}"
          data-fecha_fin="${escapeHtml(r.fecha_fin_ymd || "")}"
          data-hora_entrega="${escapeHtml(r.hora_entrega || "")}"
        >
          <div><button type="button" class="btn-more" data-toggle-detail>+</button></div>
          <div>${escapeHtml(r.codigo)}</div>
          <div>${oficinaHtml(r.oficina_compacta)}</div>
          <div>${fmtFecha(r.fecha_inicio)}</div>
          <div>${fmtHora(r.hora_retiro)}</div>
          ${vueloCol}
          <div>${escapeHtml(r.categoria || "")}</div>
          <div>${escapeHtml(r.dias)}</div>
          <div>${escapeHtml(nombre)}</div>
          <div>${escapeHtml(r.telefono_cliente || "—")}</div>
          <div>${escapeHtml(r.email_cliente || "—")}</div>
          <div><span class="state ${colorEstado(r.estado)}">${escapeHtml(estadoTxt)}</span></div>
          <div>${totalFmt}</div>
        </div>

        <div class="row-detail" style="display:none;">
          <div class="reserva-summary">
            <div class="summary-title">Reservación Confirmada el: ${fmtFechaHora(r.created_at)}</div>
            <div class="reserva-summary-line"><b>Datos de Contacto:</b> MEXICO (MX) ${escapeHtml(r.telefono_cliente || "—")}</div>
            <div class="reserva-summary-line"><b>Entrega:</b> ${fmtFecha(r.fecha_inicio)} a las ${fmtHora(r.hora_retiro)} HRS</div>
            <div class="reserva-summary-line"><b>Devolución:</b> ${fmtFecha(r.fecha_fin)} a las ${fmtHora(r.hora_entrega)} HRS</div>
            <div class="reserva-summary-line"><b>Total(MXN):</b> ${totalFmt} - Forma de pago: (${escapeHtml(r.metodo_pago || "mostrador")})</div>
            <div class="reserva-summary-line summary-full">
              <b>Vehículo Requerido:</b> ${escapeHtml(r.categoria || "")} | ${escapeHtml(r.categoria_nombre || "Sin asignar")} ${escapeHtml(r.transmision || "Sin transmisión")} ${escapeHtml(r.categoria_descripcion || "")} | Costo online: ${costoOnline} | Costo oficina: ${costoOficina}
            </div>
            <div class="reserva-summary-line"><b>Número de vuelo:</b> ${escapeHtml(r.no_vuelo || "—")}</div>
            <div class="reserva-summary-line"><b>Adicionales Requeridos:</b> ${extrasHtml}</div>
            <div class="reserva-summary-line"><b>Seguros:</b><br>${r.seguro ? escapeHtml(r.seguro) : "—"}</div>

            <div class="summary-actions">
              <div class="summary-actions-left">
                <button type="button" class="btn btn-edit" onclick="window.location.href='/admin/reservaciones/${r.id_reservacion}/editar'">
                  <i class="fa-solid fa-pen"></i> Editar Reservación
                </button>
                <button type="button" class="btn btn-cancel" title="Cancelar reservación" data-open-actions data-id="${r.id_reservacion}" data-codigo="${escapeHtml(r.codigo)}" data-delete-url="${r.delete_url}">
                  <i class="fa-solid fa-trash"></i> Cancelar Reservación
                </button>
              </div>
              <div class="summary-actions-right">
                <button type="button" class="btn btn-mail" onclick="reenviarCorreo(${r.id_reservacion}, this)">
                  <i class="fa-solid fa-envelope"></i> Reenviar correo
                </button>
                <button type="button" class="btn btn-car btn-apartar-auto" data-id="${r.id_reservacion}">
                  <i class="fa-solid fa-car-side"></i> Apartar auto
                </button>
              </div>
            </div>
          </div>
        </div>
      `;
    }

    async function buscar(q) {
      // Cancelar petición anterior si sigue en curso
      if (ultimoController) ultimoController.abort();
      ultimoController = new AbortController();

      const params = new URLSearchParams(location.search);
      params.set("q", q);

      try {
        const res = await fetch(`${location.pathname}?${params.toString()}`, {
          headers: {
            "X-Requested-With": "XMLHttpRequest",
            "Accept": "application/json",
          },
          signal: ultimoController.signal,
        });

        const data = await res.json();

        if (!data.success) throw new Error("Respuesta inválida");

        if (!data.data.length) {
          tbody.innerHTML = `<div class="row"><div style="grid-column: 1 / -1; text-align:center;">No se encontraron reservaciones.</div></div>`;
        } else {
          tbody.innerHTML = data.data.map(construirFila).join("");
        }

        if (count) count.textContent = data.total;
      } catch (err) {
        if (err.name === "AbortError") return; // petición cancelada, ignorar
        console.error("Error en búsqueda:", err);
      }
    }

    inputQ.addEventListener("input", () => {
      const q = inputQ.value.trim();

      clearTimeout(debounceTimer);

      // Si el campo queda vacío, restauramos las filas originales del servidor
      if (q === "") {
        if (ultimoController) ultimoController.abort();
        tbody.innerHTML = filasOriginales;
        if (count) count.textContent = $$("#tablaActivas .tbody .row").length;
        return;
      }

      // Esperar 300ms tras dejar de escribir antes de consultar (debounce)
      debounceTimer = setTimeout(() => buscar(q), 300);
    });
  })();

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
