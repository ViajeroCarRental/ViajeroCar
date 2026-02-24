// ============================================================
// 💳 PAGO EN LÍNEA (Modo actual: solo aviso "Próximamente")
// ============================================================

document.addEventListener("DOMContentLoaded", () => {
  const paypalContainer = document.getElementById("paypal-button-container");
  const modalMetodoPago = document.getElementById("modalMetodoPago");
  const btnPagoLinea    = document.getElementById("btnPagoLinea");

  // ==========================================================
  // 🧭 Flujo actual de pago en línea
  //   - NO carga SDK de PayPal
  //   - NO crea orden real
  //   - Solo muestra mensaje "Próximamente"
  //
  //   Esta función se expone en window.handleReservaPagoEnLinea
  //   para que el script inline de Blade pueda usarla como
  //   respaldo si algún día no existe el botón #btnPagoLinea.
  // ==========================================================
  function iniciarPagoEnLineaPlaceholder() {
    // Cerramos modal si está abierto
    if (modalMetodoPago) {
      modalMetodoPago.style.display = "none";
    }

    // Ocultamos contenedor de PayPal por si quedó visible
    if (paypalContainer) {
      paypalContainer.style.display = "none";
      paypalContainer.innerHTML = "";
    }

    // Mensaje de "próximamente"
    const msg = "💳 Próximamente podrás realizar tu pago en línea con PayPal.";

    if (window.alertify) {
      alertify.message(msg);
    } else {
      alert(msg);
    }
  }

  // 🚫 IMPORTANTE:
  // NO agregamos aquí un addEventListener a btnPagoLinea,
  // porque ese botón YA está manejado en BtnReserva.js
  // para evitar mensajes duplicados.
  //
  // 👉 Solo dejamos la función global como respaldo.

  // ======================================================
  // 🌐 Exponer la función global para usarla desde Blade
  // ======================================================
  window.handleReservaPagoEnLinea = iniciarPagoEnLineaPlaceholder;
});
