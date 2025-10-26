/* ==========================================================
   ⚙️ UTILIDADES BÁSICAS
========================================================== */
const $ = (s) => document.querySelector(s);
const $$ = (s) => Array.from(document.querySelectorAll(s));
const esc = (s) =>
  (s ?? "").toString().replace(/[&<>"]/g, (m) => ({ "&": "&amp;", "<": "&lt;", ">": "&gt;", '"': "&quot;" }[m]));

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
   🚘 SELECCIÓN DE VEHÍCULO
========================================================== */
const vehList = $("#vehList");
const vehModal = $("#vehPop");
const vehInput = $("#id_vehiculo");
const categoriaSelect = $("#categoriaSelect");

$("#btnVeh")?.addEventListener("click", async () => {
  const idCat = categoriaSelect?.value || 0;
  if (!idCat) return alertify.warning("Selecciona una categoría primero.");
  vehModal.classList.add("show");
  vehList.innerHTML = "<div style='padding:12px;'>Cargando vehículos...</div>";

  try {
    const res = await fetch(`/admin/cotizaciones/vehiculos/${idCat}`);
    const data = await res.json();
    if (!data.length)
      return (vehList.innerHTML = "<div style='padding:12px;'>No hay vehículos disponibles.</div>");

    vehList.innerHTML = data
      .map(
        (v) => `
      <div class="veh-card" style="display:flex;gap:16px;align-items:center;justify-content:space-between;border:1px solid #ddd;border-radius:12px;padding:10px;margin-bottom:10px;">
        <div style="display:flex;gap:12px;align-items:center;">
          <img src="${v.imagen ?? "/assets/media/no-image.png"}" alt="auto" style="width:140px;height:90px;object-fit:cover;border-radius:8px;">
          <div>
            <div style="font-weight:700;">${esc(v.nombre_publico)}</div>
            <div style="font-size:13px;color:#666;">${esc(v.marca)} ${esc(v.modelo)} ${v.anio}</div>
            <div style="font-size:12px;color:#777;">${v.transmision || "—"} · ${v.asientos} asientos · ${v.puertas} puertas</div>
          </div>
        </div>
        <div style="text-align:right;">
          <div style="font-weight:700;font-size:15px;color:#d00;">$${Number(v.precio_dia).toLocaleString()} MXN</div>
          <button class="btn primary btnSelectVeh"
                  data-id="${v.id_vehiculo}"
                  data-nombre="${esc(v.nombre_publico)}"
                  data-precio="${v.precio_dia}"
                  data-img="${v.imagen ?? "/assets/media/no-image.png"}">Seleccionar</button>
        </div>
      </div>`
      )
      .join("");
  } catch (err) {
    console.error(err);
    vehList.innerHTML = "<div style='padding:12px;color:#d00;'>Error al cargar los vehículos.</div>";
  }
});

$("#vehClose")?.addEventListener("click", () => vehModal.classList.remove("show"));
vehModal?.addEventListener("click", (e) => {
  if (e.target.id === "vehPop") vehModal.classList.remove("show");
});

vehList?.addEventListener("click", (e) => {
  const btn = e.target.closest(".btnSelectVeh");
  if (!btn) return;

  const idVehiculo = btn.dataset.id;
  const nombre = btn.dataset.nombre;
  const precio = parseFloat(btn.dataset.precio || 0);
  const imgSrc = btn.dataset.img;

  vehInput.value = nombre;
  vehInput.dataset.idVehiculo = idVehiculo;
  vehInput.dataset.precio = precio;

  vehModal.classList.remove("show");
  updateResumen(precio);
  $("#vehImage").src = imgSrc;
  $("#vehName").textContent = nombre;
  $("#vehImageWrap").style.display = "block";
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
      return (proteList.innerHTML = "<div style='text-align:center;padding:12px;'>No hay seguros disponibles.</div>");

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

// 🔹 Controlar incremento/decremento
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
   💰 RESUMEN Y TOTALES (Versión mejorada y robusta)
========================================================== */
let precioSeleccionado = 0;
let diasSeleccionados = 1;

function updateResumen(precioDia = null, dias = null) {
  // Actualiza precio y días si vienen nuevos
  if (precioDia !== null) precioSeleccionado = parseFloat(precioDia) || 0;
  if (dias !== null) diasSeleccionados = parseInt(dias) || 1;

  // Cálculos base
  const base = precioSeleccionado * diasSeleccionados;
  const proteccion = seguroSeleccionado ? parseFloat(seguroSeleccionado.precio) * diasSeleccionados : 0;
  const extras = adicionalesSeleccionados.reduce(
    (sum, a) => sum + (parseFloat(a.precio) || 0) * (a.cantidad || 0) * (a.tipo_cobro === "por_dia" ? diasSeleccionados : 1),
    0
  );

  const subtotal = base + proteccion + extras;
  const iva = subtotal * 0.16;
  const total = subtotal + iva;

  // Moneda y tipo de cambio
  const moneda = $("#moneda")?.value || "MXN";
  let tc = parseFloat($("#tc")?.value);

  // ✅ Validación robusta de tipo de cambio
  if (isNaN(tc) || tc <= 0) tc = 17;

  // 🧮 Conversión correcta (divide entre tipo de cambio si está en USD)
  const conv = moneda === "USD" ? 1 / tc : 1;

  // Evita propagación de errores (NaN)
  if ([base, subtotal, iva, total, conv].some(isNaN)) {
    console.warn("⚠️ Cálculo interrumpido: valores inválidos detectados.");
    return;
  }

  // Actualización de resumen en pantalla
  $("#baseLine").textContent = `$${(base * conv).toFixed(2)} ${moneda}`;
  $("#proteName").textContent = seguroSeleccionado ? seguroSeleccionado.nombre : "—";
  $("#extrasName").textContent = adicionalesSeleccionados.length
    ? adicionalesSeleccionados.map((a) => `${a.cantidad}× ${a.nombre}`).join(", ")
    : "—";
  $("#subTot").textContent = `$${(subtotal * conv).toFixed(2)} ${moneda}`;
  $("#iva").textContent = `$${(iva * conv).toFixed(2)} ${moneda}`;
  $("#total").textContent = `$${(total * conv).toFixed(2)} ${moneda}`;
}

// 🔁 Recalcular en tiempo real
$("#moneda")?.addEventListener("change", () => updateResumen());
$("#tc")?.addEventListener("input", () => updateResumen());


/* ==========================================================
   💾 BOTONES DEL PASO 3
========================================================== */
$("#btnGuardarCotizacion")?.addEventListener("click", async (e) => {
  e.preventDefault();
  const payload = obtenerDatosCotizacion();
  await enviarCotizacion(payload, "guardada");
});

$("#btnEnviarCotizacion")?.addEventListener("click", async (e) => {
  e.preventDefault();
  const payload = obtenerDatosCotizacion();
  payload.enviarCorreo = true;
  await enviarCotizacion(payload, "enviada");
});

$("#btnConfirmarCotizacion")?.addEventListener("click", async (e) => {
  e.preventDefault();
  const payload = obtenerDatosCotizacion();
  payload.confirmar = true;
  await enviarCotizacion(payload, "confirmada");
});

/* ==========================================================
   📤 ENVÍO AL CONTROLADOR
========================================================== */
async function enviarCotizacion(data, accion = "guardada") {
  try {
    const res = await fetch("/admin/cotizaciones/guardar", {
      method: "POST",
      headers: {
        "Content-Type": "application/json",
        "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').content,
      },
      body: JSON.stringify(data),
    });

    const result = await res.json();
    if (res.ok && result.success) {
      alertify.success(`✅ Cotización ${accion} correctamente`);
      console.log("📦 Respuesta del servidor:", result);
      if (accion === "confirmada") {
        alertify.notify("Se redirigirá al módulo de reservaciones...", "custom", 6);
        setTimeout(() => (window.location.href = "/admin/reservaciones"), 1500);
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
   🧾 CAPTURA DE DATOS COMPLETA
========================================================== */
function obtenerDatosCotizacion() {
  const moneda = $("#moneda")?.value || "MXN";
  const tc = parseFloat($("#tc")?.value || 17);
  const subtotal = $("#subTot")?.textContent.replace(/[^\d.]/g, "") || "0";
  const iva = $("#iva")?.textContent.replace(/[^\d.]/g, "") || "0";
  const total = $("#total")?.textContent.replace(/[^\d.]/g, "") || "0";

  return {
  vehiculo_id: vehInput.dataset.idVehiculo || "",
  categoria_id: $("#categoriaSelect")?.value,
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
  updateResumen();
});
