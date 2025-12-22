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

      // Validar que tengamos el CLIENT_ID disponible
      if (!window.PAYPAL_CLIENT_ID) {
        console.error("PAYPAL_CLIENT_ID no está definido en window.");
        alert("Error al configurar la pasarela de pago. Intenta más tarde.");
        return reject(new Error("Falta PAYPAL_CLIENT_ID"));
      }

      const script = document.createElement("script");
      script.src =
        "https://www.paypal.com/sdk/js?client-id=" +
        encodeURIComponent(window.PAYPAL_CLIENT_ID) +
        "&currency=MXN";

      script.onload = () => resolve();
      script.onerror = (err) => {
        console.error("Error al cargar el SDK de PayPal:", err);
        reject(err);
      };

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

    // 🔍 Validar datos obligatorios antes de llamar a PayPal
    const payload = getFormData();
    const aceptaTerminos = document.querySelector("#acepto")?.checked || false;

    const camposFaltantes = [];

    if (!payload.nombre) camposFaltantes.push("Nombre completo");
    if (!payload.telefono) camposFaltantes.push("Móvil");
    if (!payload.email) camposFaltantes.push("Correo electrónico");
    if (!payload.pickup_date || !payload.pickup_time) {
      camposFaltantes.push("Fecha y hora de entrega");
    }
    if (!payload.dropoff_date || !payload.dropoff_time) {
      camposFaltantes.push("Fecha y hora de devolución");
    }
    if (!payload.vehiculo_id) camposFaltantes.push("Vehículo seleccionado");
    if (!payload.pickup_sucursal_id) camposFaltantes.push("Sucursal de entrega");
    if (!payload.dropoff_sucursal_id) camposFaltantes.push("Sucursal de devolución");

    if (camposFaltantes.length > 0) {
      alert(
        "Por favor completa los siguientes campos antes de continuar con el pago:\n\n- " +
        camposFaltantes.join("\n- ")
      );
      return;
    }

    if (!aceptaTerminos) {
      alert("Debes aceptar las políticas y procedimientos para continuar con el pago.");
      return;
    }

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
          const totalText =
            document.querySelector("#qTotal")?.textContent || "";

          const totalNumber = parseFloat(
            totalText.replace(/[^\d.]/g, "")
          );

          if (!totalText || isNaN(totalNumber) || totalNumber <= 0) {
            alert("El total de la reservación no es válido. Actualiza la página e inténtalo de nuevo.");
            // Lanzamos error para que PayPal no continúe
            throw new Error("Total inválido para crear la orden de PayPal.");
          }

          const total = totalNumber.toFixed(2);

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
          alert("✅ Pago completado en PayPal. Registrando reservación...");

          const payload = getFormData();

          const form  = document.querySelector("#formCotizacion");
          const token =
            document.querySelector('meta[name="csrf-token"]')?.getAttribute("content") ||
            form?.querySelector('input[name="_token"]')?.value ||
            "";

          if (!token) {
            alert("No se encontró el token de seguridad. Actualiza la página e inténtalo de nuevo.");
            console.error("CSRF token no encontrado en la página.");
            return;
          }

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
              // 👇 El backend decidirá el status del pago; aquí ya no lo fingimos
            }),
          });

          const dataRes = await res.json().catch(() => ({}));

          if (!res.ok || dataRes.ok === false) {
            console.error("Error al registrar la reservación en backend:", dataRes);
            alert("⚠️ Error al registrar la reservación. Por favor contacta a soporte con tu comprobante de pago.");
            return;
          }

          alert("🎉 Reservación registrada con éxito. Ticket enviado por correo.");
          // Limpiamos solo lo relacionado a esta reserva
          sessionStorage.removeItem("addons_selection");
        },

        onCancel: () => {
          alert("⚠️ Pago cancelado.");
        },

        onError: (err) => {
          console.error("Error PayPal:", err);
          alert("Error al procesar el pago. Intenta más tarde o usa otro método.");
        },
      }).render("#paypal-button-container");
    } catch (error) {
      console.error("Error al cargar PayPal:", error);
      alert("No se pudo cargar la pasarela de pago. Intenta más tarde.");
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
