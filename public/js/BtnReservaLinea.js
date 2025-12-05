// ============================================================
// 💳 PAGO EN LÍNEA (PayPal Sandbox Oficial)
// ============================================================

document.addEventListener("DOMContentLoaded", () => {
  const paypalContainer   = document.getElementById("paypal-button-container");
  const btnPagoLinea      = document.getElementById("btnPagoLinea");
  const modalMetodoPago   = document.getElementById("modalMetodoPago");

  // ============================
  // 🔹 SDK de PayPal (sandbox)
  // ============================
  function loadPayPalSDK() {
    return new Promise((resolve, reject) => {
      // Si ya está cargado, no volver a cargar
      if (window.paypal) return resolve();

      const script = document.createElement("script");
      script.src =
        "https://www.paypal.com/sdk/js?client-id=ATzNpaAJlH7dFrWKu91xLmCzYVDQQF5DJ51b0OFICqchae6n8Pq7XkfsOOQNnElIJMt_Aj0GEZeIkFsp&currency=MXN";
      script.onload = resolve;
      script.onerror = reject;
      document.head.appendChild(script);
    });
  }

  // ===============================================
  // 🧾 Recolectar datos del formulario
  // ===============================================
  function getFormData() {
    const nombre   = document.querySelector("#nombreCliente")?.value?.trim() || "";
    const email    = document.querySelector("#correoCliente")?.value?.trim() || "";
    const telefono = document.querySelector("#telefonoCliente")?.value?.trim() || "";
    const vuelo    = document.querySelector("#vuelo")?.value?.trim() || "";

    const pickup_date  = document.querySelector("#pickup_date")?.value || "";
    const pickup_time  = document.querySelector("#pickup_time")?.value || "";
    const dropoff_date = document.querySelector("#dropoff_date")?.value || "";
    const dropoff_time = document.querySelector("#dropoff_time")?.value || "";

    const urlParams            = new URLSearchParams(window.location.search);
    const vehiculo_id          = urlParams.get("vehiculo_id") || "";
    const pickup_sucursal_id   = urlParams.get("pickup_sucursal_id") || "";
    const dropoff_sucursal_id  = urlParams.get("dropoff_sucursal_id") || "";

    let addons = {};
    try {
      const raw = JSON.parse(sessionStorage.getItem("addons_selection") || "{}");
      Object.values(raw).forEach((it) => {
        if (it.qty > 0) addons[it.id] = it.qty;
      });
    } catch (_) {}

    return {
      vehiculo_id,
      pickup_date,
      pickup_time,
      dropoff_date,
      dropoff_time,
      pickup_sucursal_id,
      dropoff_sucursal_id,
      nombre,
      email,
      telefono,
      vuelo,
      addons,
    };
  }

  // ==========================================================
  // 🧭 Función principal: iniciar flujo de pago en línea
  // ==========================================================
  async function iniciarPagoEnLinea() {
    if (modalMetodoPago) modalMetodoPago.style.display = "none";

    try {
      await loadPayPalSDK();

      if (!paypalContainer) {
        alert("No se pudo mostrar el botón de pago.");
        return;
      }

      paypalContainer.style.display = "block";
      paypalContainer.innerHTML = "";

      window.paypal.Buttons({
        style: {
          color: "gold",
          shape: "pill",
          label: "pay",
          height: 40,
        },

        createOrder: (data, actions) => {
          const total =
            document.querySelector("#qTotal")?.textContent?.replace(/[^\d.]/g, "") || "5000";

          return actions.order.create({
            purchase_units: [
              {
                amount: {
                  value: total,
                  currency_code: "MXN",
                },
                description: "Pago de reservación en línea - Viajero Car Rental",
              },
            ],
          });
        },

        onApprove: async (data, actions) => {
          const order = await actions.order.capture();
          alert("✅ Pago completado. Registrando reservación...");

          const payload = getFormData();

          const form  = document.querySelector("#formCotizacion");
          const token =
            document.querySelector('meta[name="csrf-token"]')?.getAttribute("content") ||
            form?.querySelector('input[name="_token"]')?.value ||
            "";

          const urlLinea = window.APP_URL_RESERVA_LINEA || "/reservas/linea";

          const res = await fetch(urlLinea, {
            method: "POST",
            headers: {
              "Content-Type": "application/json",
              "X-CSRF-TOKEN": token,
              "X-Requested-With": "XMLHttpRequest",
              Accept: "application/json",
            },
            body: JSON.stringify({
              ...payload,
              paypal_order_id: order.id,
              status_pago: "Pagado",
            }),
          });

          const dataRes = await res.json().catch(() => ({}));

          if (!res.ok || dataRes.ok === false) {
            alert("⚠️ Error al registrar la reservación.");
            return;
          }

          alert("🎉 Reservación registrada con éxito. Ticket enviado por correo.");
          sessionStorage.clear();
        },

        onCancel: () => {
          alert("⚠️ Pago cancelado.");
        },

        onError: (err) => {
          console.error("Error PayPal:", err);
          alert("Error al procesar el pago.");
        },
      }).render("#paypal-button-container");
    } catch (error) {
      console.error("Error al cargar PayPal:", error);
      alert("No se pudo cargar la pasarela.");
    }
  }

  // ======================================================
  // 🧭 Evento: botón “Pago en línea” del modal
  // ======================================================
  if (btnPagoLinea) {
    btnPagoLinea.addEventListener("click", (e) => {
      e.preventDefault();
      iniciarPagoEnLinea();
    });
  }

  // ======================================================
  // 🌐 Exponer la función global para usarla desde Blade
  // ======================================================
  window.handleReservaPagoEnLinea = iniciarPagoEnLinea;
});
