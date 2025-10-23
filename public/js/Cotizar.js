/* ============================
   🎨 Utilidades visuales
============================ */
const $  = (s) => document.querySelector(s);
const $$ = (s) => Array.from(document.querySelectorAll(s));

/* ============================
   🧭 Navegación entre pasos
============================ */
const showStep = (n) => {
  $$('[data-step]').forEach((el) => {
    el.style.display = Number(el.dataset.step) === n ? "block" : "none";
  });
};

// Botones de navegación
$("#go2")?.addEventListener("click", () => showStep(2));
$("#back1")?.addEventListener("click", () => showStep(1));
$("#go3")?.addEventListener("click", () => showStep(3));
$("#back2")?.addEventListener("click", () => showStep(2));
showStep(1);

/* ============================
   🚗 Selección de vehículo
============================ */
$("#btnVehiculo")?.addEventListener("click", () => {
  alert("Aquí se abriría el catálogo de vehículos disponibles.");
});

/* ============================
   💰 Simulación visual
============================ */
["tarifa_base", "extras_sub", "iva"].forEach((id) => {
  $(`#${id}`)?.addEventListener("input", () => {
    const base = Number($("#tarifa_base")?.value || 0);
    const extras = Number($("#extras_sub")?.value || 0);
    const iva = Number($("#iva")?.value || 0);
    const total = base + extras + iva;

    $("#total").value = total.toFixed(2);
    $("#resBase").textContent = `$${base.toFixed(2)} MXN`;
    $("#resExtras").textContent = `$${extras.toFixed(2)} MXN`;
    $("#resIva").textContent = `$${iva.toFixed(2)} MXN`;
    $("#resTotal").textContent = `$${total.toFixed(2)} MXN`;
  });
});

/* ============================
   📅 Actualización visual días
============================ */
$("#pickup_date")?.addEventListener("change", () => {
  const start = new Date($("#pickup_date").value);
  const end = new Date($("#dropoff_date").value);
  const days = Math.max(1, Math.ceil((end - start) / (1000 * 60 * 60 * 24)));
  $("#days").textContent = `${isNaN(days) ? 0 : days} día(s)`;
  $("#resDays").textContent = `${isNaN(days) ? 0 : days}`;
});

$("#dropoff_date")?.addEventListener("change", () => {
  const start = new Date($("#pickup_date").value);
  const end = new Date($("#dropoff_date").value);
  const days = Math.max(1, Math.ceil((end - start) / (1000 * 60 * 60 * 24)));
  $("#days").textContent = `${isNaN(days) ? 0 : days} día(s)`;
  $("#resDays").textContent = `${isNaN(days) ? 0 : days}`;
});

/* ============================
   💾 Envío de formulario
============================ */
$("#formPaso3")?.addEventListener("submit", async (e) => {
  e.preventDefault();

  // Captura visual (sin validaciones complejas)
  const data = {
    vehiculo_id: $("#vehiculo_id")?.value,
    pickup_name: $("#pickup_name")?.value,
    dropoff_name: $("#dropoff_name")?.value,
    pickup_date: $("#pickup_date")?.value,
    pickup_time: $("#pickup_time")?.value,
    dropoff_date: $("#dropoff_date")?.value,
    dropoff_time: $("#dropoff_time")?.value,
    days: $("#days")?.textContent.replace(/\D/g, "") || 1,
    tarifa_base: $("#tarifa_base")?.value,
    extras_sub: $("#extras_sub")?.value,
    iva: $("#iva")?.value,
    total: $("#total")?.value,
    cliente: {
      nombre: $("#cliente_nombre")?.value,
      apellidos: $("#cliente_apellidos")?.value,
      email: $("#cliente_email")?.value,
      telefono: $("#cliente_telefono")?.value,
      pais: $("#cliente_pais")?.value,
    },
  };

  console.log("Datos capturados visualmente:", data);
  alert("Cotización preparada visualmente. Aquí se enviaría al backend.");
});

/* ============================
   🧾 Inicialización del resumen
============================ */
window.addEventListener("DOMContentLoaded", () => {
  $("#resBase").textContent = "$0 MXN";
  $("#resExtras").textContent = "$0 MXN";
  $("#resIva").textContent = "$0 MXN";
  $("#resTotal").textContent = "$0 MXN";
  $("#resDays").textContent = "0";
});
