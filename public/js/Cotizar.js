/* ==========================================================
   ⚙️ UTILIDADES BÁSICAS
========================================================== */
const $ = (s) => document.querySelector(s);
const $$ = (s) => Array.from(document.querySelectorAll(s));
const esc = (s) =>
  (s ?? "").toString().replace(/[&<>"]/g, (m) => ({
    "&": "&amp;",
    "<": "&lt;",
    ">": "&gt;",
    '"': "&quot;"
  }[m]));

/* Mostrar / ocultar menú lateral */
$('#burger')?.addEventListener('click', () => $('#side').classList.toggle('show'));

/* ==========================================================
   🧭 NAVEGACIÓN ENTRE PASOS
========================================================== */
const showStep = (n) => {
  $$("[data-step]").forEach((el) => (el.style.display = Number(el.dataset.step) === n ? "block" : "none"));
};
$("#go2")?.addEventListener("click", () => showStep(2));
$("#back1")?.addEventListener("click", () => showStep(1));
$("#go3")?.addEventListener("click", () => showStep(3));
$("#back2")?.addEventListener("click", () => showStep(2));
showStep(1);

/* ==========================================================
   📅 CÁLCULO DE DÍAS
========================================================== */
$("#fecha_inicio")?.addEventListener("change", calcularDias);
$("#fecha_fin")?.addEventListener("change", calcularDias);

function calcularDias() {
  const f1 = new Date($("#fecha_inicio")?.value);
  const f2 = new Date($("#fecha_fin")?.value);
  if (!f1 || !f2 || isNaN(f1) || isNaN(f2)) return;
  const diff = Math.ceil((f2 - f1) / (1000 * 60 * 60 * 24));
  const days = diff > 0 ? diff : 1;
  $("#diasBadge").textContent = `${days} día(s)`;
  updateResumen(null, days);
  return days;
}

/* ==========================================================
   🚗 CATEGORÍA → IMAGEN Y TARIFA BASE
========================================================== */
const categoriaSelect = $("#categoriaSelect");
const vehImageWrap = $('#vehImageWrap');
const vehImage = $('#vehImage');
const vehName = $('#vehName');

categoriaSelect?.addEventListener('change', async () => {
  const idCat = categoriaSelect.value;

  if (!idCat || idCat === '0') {
    vehImageWrap.style.display = 'none';
    $('#baseLine').textContent = '—';
    updateResumen(0);
    return;
  }

  try {
    const res = await fetch(`/admin/cotizaciones/categoria/${idCat}`);
    const cat = await res.json();
    if (cat?.error) throw new Error(cat.message || 'Categoría no encontrada.');

    // 🔹 Mostrar imagen y nombre de la categoría
    vehImage.src = cat.imagen || '/assets/placeholder-car.jpg';
    vehName.textContent = cat.nombre || 'Ejemplo de la categoría seleccionada';
    vehImageWrap.style.display = 'block';

    // 🔹 Calcular y mostrar tarifa base
    const tarifa = parseFloat(cat.tarifa_base ?? cat.precio_dia ?? 0);
    $('#baseLine').textContent = `$${tarifa.toFixed(2)} MXN/día`;

    // 🔹 Restaurar estilo y control de tarifa base
    tarifaOriginal = tarifa;
    tarifaEditadaManualmente = false;
    $('#baseLine').style.color = '#000';
    $('#baseLine').style.fontWeight = '400';

    // 🔹 Actualizar resumen con la nueva tarifa
    updateResumen(tarifa, calcularDias());

  } catch (err) {
    console.error('Error cargando categoría:', err);
    alertify.error('No se pudo cargar la categoría.');
    vehImageWrap.style.display = 'none';
    $('#baseLine').textContent = '—';
    updateResumen(0);
  }
});

/* ==========================================================
   🔒 PROTECCIONES (SEGUROS)
========================================================== */
const btnProtecciones = $("#btnProtecciones");
const proteModal = $("#proteccionPop");
const proteList = $("#proteList");
const proteInput = $("#proteccionSel");
const proteRemove = $("#proteRemove");
let seguroSeleccionado = null;

btnProtecciones?.addEventListener("click", async () => {
  proteModal.classList.add("show");
  proteList.innerHTML = "<div style='text-align:center;padding:12px;'>Cargando protecciones...</div>";

  try {
    const res = await fetch("/admin/cotizaciones/seguros");
    const data = await res.json();
    if (!data.length)
      return (proteList.innerHTML =
        "<div style='text-align:center;padding:12px;'>No hay seguros disponibles.</div>");

    proteList.innerHTML = data
      .map(
        (s) => `
        <div class="seg-card" style="border:1px solid #ddd;border-radius:12px;padding:16px;margin-bottom:10px;text-align:center;background:#f8fafc;">
          <div style="font-weight:700;">${s.nombre}</div>
          <div style="font-size:13px;color:#475467;">${s.descripcion || ""}</div>
          <div style="font-weight:600;color:#d00;margin-top:6px;">$${Number(s.precio_por_dia).toFixed(2)} MXN/día</div>
          <button class="btn primary selectProteccion"
                  data-id="${s.id_paquete}"
                  data-nombre="${esc(s.nombre)}"
                  data-precio="${s.precio_por_dia}">Seleccionar</button>
        </div>`
      )
      .join("");
  } catch (err) {
    console.error("Error al cargar seguros:", err);
    proteList.innerHTML = "<div style='color:#d00;padding:10px;'>Error al cargar los paquetes.</div>";
  }
});

proteList?.addEventListener("click", (e) => {
  const btn = e.target.closest(".selectProteccion");
  if (!btn) return;

  seguroSeleccionado = {
    id: btn.dataset.id,
    nombre: btn.dataset.nombre,
    precio: parseFloat(btn.dataset.precio),
  };
  proteInput.value = `${seguroSeleccionado.nombre} - $${seguroSeleccionado.precio}/día`;
  proteRemove.style.display = "inline-block";
  proteModal.classList.remove("show");
  updateResumen();
});

proteRemove?.addEventListener("click", () => {
  seguroSeleccionado = null;
  proteInput.value = "Ninguna protección seleccionada";
  proteRemove.style.display = "none";
  updateResumen();
});

$("#proteClose")?.addEventListener("click", () => proteModal.classList.remove("show"));
$("#proteCancel")?.addEventListener("click", () => proteModal.classList.remove("show"));
proteModal?.addEventListener("click", (e) => {
  if (e.target.id === "proteccionPop") proteModal.classList.remove("show");
});

/* ==========================================================
   🧩 SERVICIOS ADICIONALES
========================================================== */
let adicionalesSeleccionados = [];

async function cargarAdicionales() {
  const grid = $("#addGrid");
  grid.innerHTML = "<div class='loading'>Cargando adicionales...</div>";
  try {
    const resp = await fetch("/admin/cotizaciones/servicios");
    const data = await resp.json();
    if (!data.length)
      return (grid.innerHTML = "<div class='empty'>No hay servicios adicionales disponibles.</div>");

    grid.innerHTML = data
      .map(
        (serv) => `
      <div class="add-card" data-id="${serv.id_servicio}" data-nombre="${esc(serv.nombre)}" data-precio="${serv.precio}"
           style="border:1px solid #D0D5DD;border-radius:10px;padding:12px;margin-bottom:10px;background:#fff;">
        <div style="display:flex;justify-content:space-between;align-items:center;">
          <div>
            <div style="font-weight:700;">${serv.nombre}</div>
            <div style="font-size:13px;color:#475467;">${serv.descripcion || ""}</div>
            <div style="font-weight:600;color:#2563eb;margin-top:4px;">$${parseFloat(serv.precio).toFixed(2)} MXN/día</div>
          </div>
          <div style="display:flex;align-items:center;gap:6px;">
            <button class="btn gray menos" style="padding:4px 10px;">−</button>
            <span class="cantidad" style="min-width:20px;text-align:center;">0</span>
            <button class="btn gray mas" style="padding:4px 10px;">+</button>
          </div>
        </div>
      </div>`
      )
      .join("");
  } catch (err) {
    console.error("Error cargando servicios:", err);
    grid.innerHTML = "<div class='error'>No se pudieron cargar los servicios adicionales.</div>";
  }
}
$("#go2")?.addEventListener("click", cargarAdicionales);

// 🔹 Incremento/decremento
document.addEventListener("click", (e) => {
  const card = e.target.closest(".add-card");
  if (!card) return;
  const id = card.dataset.id;
  const nombre = card.dataset.nombre;
  const precio = parseFloat(card.dataset.precio);
  const span = card.querySelector(".cantidad");

  if (e.target.classList.contains("mas")) {
    let actual = parseInt(span.textContent) || 0;
    actual++;
    span.textContent = actual;
    let existe = adicionalesSeleccionados.find((a) => a.id === id);
    if (existe) existe.cantidad = actual;
    else adicionalesSeleccionados.push({ id, nombre, precio, cantidad: actual });
  }

  if (e.target.classList.contains("menos")) {
    let actual = parseInt(span.textContent) || 0;
    if (actual > 0) actual--;
    span.textContent = actual;
    let existe = adicionalesSeleccionados.find((a) => a.id === id);
    if (existe) {
      existe.cantidad = actual;
      if (actual === 0) adicionalesSeleccionados = adicionalesSeleccionados.filter((a) => a.id !== id);
    }
  }
  updateResumen();
});

/* ==========================================================
   💰 RESUMEN Y TOTALES (con edición de tarifa y control completo)
========================================================== */
let precioSeleccionado = 0;
let diasSeleccionados = 1;
let tarifaOriginal = 0;
let tarifaEditadaManualmente = false;

/**
 * 🔹 Actualiza valores del resumen y totales
 * @param {number|null} precioDia - Tarifa base del día
 * @param {number|null} dias - Número de días calculados
 */
function updateResumen(precioDia = null, dias = null) {
  if (precioDia !== null) precioSeleccionado = parseFloat(precioDia) || 0;
  if (dias !== null) diasSeleccionados = parseInt(dias) || 1;
  actualizarTotal();
}

/**
 * 🔹 Recalcula totales (base, protecciones, extras, IVA, total)
 */
function actualizarTotal() {
  const base = precioSeleccionado * diasSeleccionados;
  const proteccion = seguroSeleccionado
    ? parseFloat(seguroSeleccionado.precio || 0) * diasSeleccionados
    : 0;
  const extras = adicionalesSeleccionados.reduce(
    (sum, a) => sum + (parseFloat(a.precio) || 0) * (a.cantidad || 0) * diasSeleccionados,
    0
  );

  const subtotal = base + proteccion + extras;
  const iva = subtotal * 0.16;
  const total = subtotal + iva;

  const moneda = $("#moneda")?.value || "MXN";
  let tc = parseFloat($("#tc")?.value);
  if (isNaN(tc) || tc <= 0) tc = 17;

  const conv = moneda === "USD" ? (1 / tc) : 1;

  $("#proteName").textContent = seguroSeleccionado ? seguroSeleccionado.nombre : "—";
  $("#extrasName").textContent = adicionalesSeleccionados.length
    ? adicionalesSeleccionados.map((a) => `${a.cantidad}× ${a.nombre}`).join(", ")
    : "—";
  $("#subTot").textContent = `$${(subtotal * conv).toFixed(2)} ${moneda}`;
  $("#iva").textContent = `$${(iva * conv).toFixed(2)} ${moneda}`;
  $("#total").textContent = `$${(total * conv).toFixed(2)} ${moneda}`;
}

$("#moneda")?.addEventListener("change", actualizarTotal);
$("#tc")?.addEventListener("input", actualizarTotal);

/* ==========================================================
   ✏️ EDICIÓN INLINE DE TARIFA BASE
========================================================== */
const editTarifaBtn = $('#editTarifa');
const baseLine = $('#baseLine');

editTarifaBtn?.addEventListener('click', () => {
  if (!baseLine) return;

  // Evitar múltiples inputs
  if (baseLine.querySelector('input')) return;

  const valorActual = parseFloat(baseLine.textContent.replace(/[^\d.]/g, '')) || precioSeleccionado || 0;

  const input = document.createElement('input');
  input.type = 'number';
  input.value = valorActual.toFixed(2);
  input.min = 0;
  input.step = 0.01;
  input.style.width = '90px';
  input.style.padding = '4px';
  input.style.border = '1px solid #ccc';
  input.style.borderRadius = '6px';
  input.style.fontWeight = '600';

  baseLine.textContent = '';
  baseLine.appendChild(input);
  input.focus();

  input.addEventListener('blur', guardarTarifaEditada);
  input.addEventListener('keydown', e => {
    if (e.key === 'Enter') input.blur();
  });

  function guardarTarifaEditada() {
    const nuevoValor = parseFloat(input.value);
    if (isNaN(nuevoValor) || nuevoValor <= 0) {
      alertify.warning('⚠️ Ingresa una tarifa válida.');
      input.focus();
      return;
    }

    tarifaEditadaManualmente = true;
    precioSeleccionado = nuevoValor;
    tarifaOriginal = tarifaOriginal || valorActual;

    baseLine.innerHTML = `<span style="color:#ca8a04;font-weight:600;">$${nuevoValor.toFixed(2)} MXN/día*</span>`;
    actualizarTotal();
  }
});
/* ================================
   🕒 Formato de hora a 12h con AM/PM
================================ */
function formatoHora12h(hora) {
  if (!hora) return '—';
  let [h, m] = hora.split(':');
  h = parseInt(h);
  const sufijo = h >= 12 ? 'PM' : 'AM';
  h = h % 12 || 12;
  return `${h}:${m} ${sufijo}`;
}

/* ================================
   🧭 Mostrar resumen de viaje
================================ */
function actualizarResumenViaje() {
  $('#resSucursalRetiro').textContent = $('#sucursal_retiro').selectedOptions[0]?.text || '—';
  $('#resSucursalEntrega').textContent = $('#sucursal_entrega').selectedOptions[0]?.text || '—';
  $('#resFechaInicio').textContent = $('#fecha_inicio').value || '—';
  $('#resHoraInicio').textContent = formatoHora12h($('#hora_retiro').value);
  $('#resFechaFin').textContent = $('#fecha_fin').value || '—';
  $('#resHoraFin').textContent = formatoHora12h($('#hora_entrega').value);
  $('#resDias').textContent = `${diasSeleccionados} día(s)` || '—';
}

// 🔹 Detectar cambios en selects e inputs
$('#sucursal_retiro')?.addEventListener('change', actualizarResumenViaje);
$('#sucursal_entrega')?.addEventListener('change', actualizarResumenViaje);
$('#fecha_inicio')?.addEventListener('change', actualizarResumenViaje);
$('#fecha_fin')?.addEventListener('change', actualizarResumenViaje);
$('#hora_retiro')?.addEventListener('change', actualizarResumenViaje);
$('#hora_entrega')?.addEventListener('change', actualizarResumenViaje);


/* ==========================================================
   💾 BOTONES DEL PASO 3
========================================================== */
$("#btnGuardarYEnviar")?.addEventListener("click", async (e) => {
  e.preventDefault();

  const v = (s) => $(s)?.value?.trim();
  if (!v("#categoriaSelect") || $("#categoriaSelect").value === "0")
    return alertify.warning("⚠️ Selecciona una categoría de vehículo.");

  // 🔹 Ya no bloqueamos si faltan sucursales o fechas
  // (Se pueden dejar vacías para una simple cotización)

  if (!v("#nombre_cliente") || !v("#email_cliente") || !v("#telefono_cliente"))
    return alertify.warning("⚠️ Completa los datos del cliente.");

  const btn = $("#btnGuardarYEnviar");
  btn.disabled = true;
  btn.textContent = "Procesando...";

  const payload = obtenerDatosCotizacion();
  payload.enviarCorreo = true;

  await enviarCotizacion(payload, "guardada y enviada");

  btn.disabled = false;
  btn.textContent = "💾 Guardar y enviar cotización";
});


$("#btnConfirmarCotizacion")?.addEventListener("click", async (e) => {
  e.preventDefault();

  const v = (s) => $(s)?.value?.trim();
  if (!v("#categoriaSelect") || $("#categoriaSelect").value === "0")
    return alertify.warning("⚠️ Selecciona una categoría de vehículo.");
  if (!v("#sucursal_retiro") || !v("#sucursal_entrega"))
    return alertify.warning("⚠️ Selecciona sucursal de retiro y entrega.");
  if (!v("#fecha_inicio") || !v("#fecha_fin"))
    return alertify.warning("⚠️ Completa las fechas.");
  if (!v("#nombre_cliente") || !v("#email_cliente") || !v("#telefono_cliente"))
    return alertify.warning("⚠️ Completa los datos del cliente.");

  const btn = $("#btnConfirmarCotizacion");
  btn.disabled = true;
  btn.textContent = "Confirmando...";

  const payload = obtenerDatosCotizacion();
  payload.confirmar = true;

  await enviarCotizacion(payload, "confirmada");

  btn.disabled = false;
  btn.textContent = "✅ Confirmar y reservar";
});

/* ==========================================================
   📤 ENVÍO AL CONTROLADOR
========================================================== */
async function enviarCotizacion(data, accion = "guardada") {
  // 🧭 Validar fechas antes de enviar
  const fInicio = $("#fecha_inicio")?.value;
  const fFin = $("#fecha_fin")?.value;

  if (!fInicio || !fFin) {
    alertify.error("⚠️ Completa las fechas de salida y llegada antes de continuar.");
    return;
  }

  if (new Date(fFin) < new Date(fInicio)) {
    alertify.error("⚠️ La fecha de devolución no puede ser menor que la de salida.");
    return;
  }

  // 🟢 Validación superada
  console.log("✅ Fechas válidas:", fInicio, "→", fFin);

  try {
    const res = await fetch("/admin/cotizaciones/guardar", {
      method: "POST",
      headers: {
        "Content-Type": "application/json",
        "X-CSRF-TOKEN": document.querySelector('meta[name=\"csrf-token\"]').content,
      },
      body: JSON.stringify(data),
    });

    const result = await res.json();

    if (res.ok && result.success) {
  alertify.success(`✅ Cotización ${accion} correctamente`);
  console.log("📦 Respuesta del servidor:", result);

  if (accion.includes("confirmada")) {
    alertify.notify("Se redirigirá al módulo de reservaciones...", "custom", 6);
    setTimeout(() => (window.location.href = "/admin/reservaciones-activas"), 1500);
  } else {
    // 🧼 Limpieza visual completa
    $('#formCotizacion')?.reset?.();
    vehImageWrap.style.display = 'none';
    $('#baseLine').textContent = '—';
    updateResumen(0);
    $('#baseLine').style.color = '#000';
    $('#baseLine').style.fontWeight = '400';
    tarifaEditadaManualmente = false;

    // 🧭 Regresar al paso 1
    showStep(1);

    // 🕓 (Opcional) recargar tras 1 s para limpiar variables globales
    setTimeout(() => window.location.reload(), 1000);
  }
    } else {
      alertify.error(`⚠️ Error al ${accion} cotización`);
      console.error(result);
    }
  } catch (err) {
    console.error("🚨 Error de red o servidor:", err);
    alertify.error("Error de conexión con el servidor");
  }
}



/* ==========================================================
   🧾 CAPTURA DE DATOS COMPLETA (ACTUALIZADA)
========================================================== */
function obtenerDatosCotizacion() {
  const moneda = $("#moneda")?.value || "MXN";
  const tc = parseFloat($("#tc")?.value || 17);
  const subtotal = parseFloat($("#subTot")?.textContent.replace(/[^\d.]/g, "") || "0");
  const iva = parseFloat($("#iva")?.textContent.replace(/[^\d.]/g, "") || "0");
  const total = parseFloat($("#total")?.textContent.replace(/[^\d.]/g, "") || "0");

  // 🔹 Detectar si el usuario editó la tarifa manualmente
  const tarifaAjustada = tarifaEditadaManualmente ? 1 : 0;

  // 🔹 Calcular subtotal de extras (sin IVA)
  const extras_sub = adicionalesSeleccionados.reduce(
    (sum, a) => sum + (parseFloat(a.precio) || 0) * (a.cantidad || 0) * diasSeleccionados,
    0
  );

  // 🔹 Tarifa base (valor original del catálogo)
  const tarifaBase = tarifaOriginal || precioSeleccionado;

  // 🔹 Tarifa modificada (si fue ajustada manualmente)
  const tarifaModificada = tarifaEditadaManualmente ? precioSeleccionado : tarifaBase;

  // 📦 Datos completos para enviar al backend
  return {
    categoria_id: $("#categoriaSelect")?.value,
    tarifa_base: tarifaBase,           // 💰 valor original del catálogo
    tarifa_modificada: tarifaModificada, // 🟡 si fue modificada por el usuario
    tarifa_ajustada: tarifaAjustada,     // 1 o 0 (bandera)
    extras_sub, // 🟣 Subtotal de adicionales (sin IVA)
    pickup_sucursal_id: $("#sucursal_retiro")?.value,
    dropoff_sucursal_id: $("#sucursal_entrega")?.value,
    pickup_date: $("#fecha_inicio")?.value,
    pickup_time: $("#hora_retiro")?.value,
    dropoff_date: $("#fecha_fin")?.value,
    dropoff_time: $("#hora_entrega")?.value,
    days: diasSeleccionados,
    seguro: seguroSeleccionado,
    extras: adicionalesSeleccionados,
    moneda,
    tipo_cambio: tc,
    subtotal,
    iva,
    total,
    cliente: {
      nombre: $("#nombre_cliente")?.value,
      apellidos: $("#apellidos")?.value,
      email: $("#email_cliente")?.value,
      telefono: $("#telefono_cliente")?.value,
      pais: $("#pais")?.value,
      vuelo: $("#no_vuelo")?.value,
    },
  };
}

/* ==========================================================
   🧮 INICIALIZACIÓN
========================================================== */
window.addEventListener("DOMContentLoaded", () => {
  calcularDias();
  updateResumen(0, 1);
});
