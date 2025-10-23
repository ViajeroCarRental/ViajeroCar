// ============================================================
// 💳 PAGO EN LÍNEA (PayPal Sandbox Oficial)
// ============================================================

document.addEventListener("DOMContentLoaded", async () => {
  const paypalContainer = document.getElementById("paypal-button-container");
  const btnPagoLinea = document.getElementById("btnPagoLinea");
  const modalMetodoPago = document.getElementById("modalMetodoPago");

  // ============================
  // 🔹 SDK de PayPal (sandbox)
  // ============================
  async function loadPayPalSDK() {
    return new Promise((resolve, reject) => {
      if (window.paypal) return resolve();

      const script = document.createElement("script");
      // ⚠️ Client ID actualizado con tus credenciales reales sandbox
      script.src =
        "https://www.paypal.com/sdk/js?client-id=ATzNpaAJlH7dFrWKu91xLmCzYVDQQF5DJ51b0OFICqchae6n8Pq7XkfsOOQNnElIJMt_Aj0GEZeIkFsp&currency=MXN";
      script.onload = resolve;
      script.onerror = reject;
      document.head.appendChild(script);
    });
  }

  // ===============================================
  // 🧾 Función para recolectar datos del formulario
  // ===============================================
  function getFormData() {
    const nombre = document.querySelector("#nombreCliente")?.value?.trim() || "";
    const email = document.querySelector("#correoCliente")?.value?.trim() || "";
    const telefono = document.querySelector("#telefonoCliente")?.value?.trim() || "";
    const vuelo = document.querySelector("#vuelo")?.value?.trim() || "";

    const pickup_date = document.querySelector("#pickup_date")?.value || "";
    const pickup_time = document.querySelector("#pickup_time")?.value || "";
    const dropoff_date = document.querySelector("#dropoff_date")?.value || "";
    const dropoff_time = document.querySelector("#dropoff_time")?.value || "";

    const urlParams = new URLSearchParams(window.location.search);
    const vehiculo_id = urlParams.get("vehiculo_id") || "";
    const pickup_sucursal_id = urlParams.get("pickup_sucursal_id") || "";
    const dropoff_sucursal_id = urlParams.get("dropoff_sucursal_id") || "";

    // Complementos seleccionados (extras)
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

  // ======================================================
  // 🧭 Evento principal: Mostrar botón PayPal en el modal
  // ======================================================
  if (btnPagoLinea) {
    btnPagoLinea.addEventListener("click", async () => {
      // Cierra modal de método de pago
      if (modalMetodoPago) modalMetodoPago.style.display = "none";

      try {
        // Cargar SDK si no existe
        await loadPayPalSDK();

        // Mostrar contenedor del botón
        paypalContainer.style.display = "block";
        paypalContainer.innerHTML = ""; // limpia anteriores

        // Crear botón PayPal
        paypal.Buttons({
          style: {
            color: "gold",
            shape: "pill",
            label: "pay",
            height: 40,
          },

          // =========================================================
          // 💰 Crear orden con el monto total real de la vista
          // =========================================================
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

          // =========================================================
          // ✅ Cuando el pago es aprobado
          // =========================================================
          onApprove: async (data, actions) => {
            const order = await actions.order.capture();
            console.log("🟢 Pago aprobado:", order);

            alert("✅ Pago completado correctamente. Generando ticket...");

            const payload = getFormData();

            // Token CSRF (Laravel)
            const form = document.querySelector("#formCotizacion");
            const token =
              document.querySelector('meta[name="csrf-token"]')?.getAttribute("content") ||
              form.querySelector('input[name="_token"]')?.value ||
              "";

            // Enviar datos al backend
            const res = await fetch("/reservas/linea", {
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

            const dataRes = await res.json();
            if (!res.ok || dataRes.ok === false) {
              console.error("❌ Error backend:", dataRes);
              alert("⚠️ Hubo un error al registrar la reservación.");
              return;
            }

            alert("🎉 Reservación registrada con éxito. Ticket enviado por correo.");
            sessionStorage.clear();
          },


          // =========================================================
          // ❌ Cuando el usuario cancela el pago
          // =========================================================
          onCancel: () => {
            alert("⚠️ Pago cancelado por el usuario.");
          },

          // =========================================================
          // ⚠️ Error general del botón
          // =========================================================
          onError: (err) => {
            console.error("Error en PayPal:", err);
            alert("Error al procesar el pago. Intente nuevamente.");
          },
        }).render("#paypal-button-container");
      } catch (error) {
        console.error("Error al cargar PayPal:", error);
        alert("No se pudo cargar la pasarela de pago.");
      }
    });
  }
});
