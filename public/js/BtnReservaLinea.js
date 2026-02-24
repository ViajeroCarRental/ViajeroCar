// ============================================================
// 💳 PAGO EN LÍNEA CON PAYPAL (LIVE / SANDBOX según .env)
// ============================================================

document.addEventListener("DOMContentLoaded", () => {
  const paypalContainer   = document.getElementById("paypal-button-container");
  const modalMetodoPago   = document.getElementById("modalMetodoPago");
  const btnPagoLinea      = document.getElementById("btnPagoLinea");
  const form              = document.getElementById("formCotizacion");

  // Si no existen elementos clave, dejamos un fallback simple
  if (!form || !paypalContainer) {
    window.handleReservaPagoEnLinea = function () {
      const msg = "No se encontró el formulario de reserva o el contenedor de PayPal.";
      if (window.alertify) alertify.error(msg); else alert(msg);
    };
    return;
  }

  // ==========================================================
  // 🔐 Helper: CSRF
  // ==========================================================
  function getCsrfToken() {
    const meta = document.querySelector('meta[name="csrf-token"]');
    return meta ? meta.getAttribute("content") : "";
  }

  // ==========================================================
  // 📦 Helper: leer y validar datos del formulario
  // ==========================================================
  function getFormPayload() {
    const categoriaId        = document.getElementById("categoria_id")?.value || "";
    const plan               = document.getElementById("plan")?.value || "";
    const addonsRaw          = document.getElementById("addons_payload")?.value || "";

    const pickupDate         = document.getElementById("pickup_date")?.value || "";
    const pickupTime         = document.getElementById("pickup_time")?.value || "";
    const dropoffDate        = document.getElementById("dropoff_date")?.value || "";
    const dropoffTime        = document.getElementById("dropoff_time")?.value || "";
    const pickupSucursalId   = document.getElementById("pickup_sucursal_id")?.value || "";
    const dropoffSucursalId  = document.getElementById("dropoff_sucursal_id")?.value || "";

    const nombreCompletoEl   = document.getElementById("nombreCompleto");
    const telefonoEl         = document.getElementById("telefonoCliente");
    const correoEl           = document.getElementById("correoCliente");
    const vueloEl            = document.getElementById("vuelo");

    const aceptaEl           = document.getElementById("acepto");

    const nombreCompleto     = (nombreCompletoEl?.value || "").trim();
    const telefono           = (telefonoEl?.value || "").trim();
    const email              = (correoEl?.value || "").trim();
    const vuelo              = (vueloEl?.value || "").trim();

    // ===== Validaciones mínimas en front =====
    if (!nombreCompleto) {
      throw new Error("Por favor ingresa tu nombre completo.");
    }
    if (!telefono) {
      throw new Error("Por favor ingresa tu número de teléfono.");
    }
    if (!email) {
      throw new Error("Por favor ingresa tu correo electrónico.");
    }
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    if (!emailRegex.test(email)) {
      throw new Error("El correo electrónico no parece válido.");
    }
    if (!aceptaEl || !aceptaEl.checked) {
      throw new Error("Debes aceptar las políticas y procedimientos para continuar.");
    }

    if (!categoriaId) {
      throw new Error("Falta la categoría seleccionada de la renta.");
    }
    if (!pickupDate || !dropoffDate || !pickupTime || !dropoffTime) {
      throw new Error("Faltan las fechas u horas de la reservación.");
    }
    if (!pickupSucursalId || !dropoffSucursalId) {
      throw new Error("Falta seleccionar sucursal de Pick-Up o Devolución.");
    }

        // 💡 Extra: validamos que efectivamente esté seleccionado plan en línea
    // En Blade el value es "linea" o "mostrador"
    if (plan !== "linea") {
      throw new Error("Para utilizar pago en línea, selecciona el plan de pago en línea.");
    }

    return {
      categoria_id:        categoriaId,
      plan:                plan,
      addons:              addonsRaw,
      pickup_date:         pickupDate,
      pickup_time:         pickupTime,
      dropoff_date:        dropoffDate,
      dropoff_time:        dropoffTime,
      pickup_sucursal_id:  pickupSucursalId,
      dropoff_sucursal_id: dropoffSucursalId,
      nombre:              nombreCompleto,   // se guarda completo
      telefono:            telefono,
      email:               email,
      vuelo:               vuelo
    };
  }

  // ==========================================================
  // 🧮 Helper: calcular TOTAL local (igual que en backend)
  // ==========================================================
  function calcularTotales() {
    const cotDoc = document.getElementById("cotizacionDoc");
    if (!cotDoc) {
      return { base: 0, extras: 0, iva: 0, total: 0 };
    }

    const base = parseFloat(cotDoc.dataset.base || "0");   // tarifa base por toda la renta
    const days = parseInt(cotDoc.dataset.days || "1", 10) || 1;

    const addonsRawEl = document.getElementById("addons_payload");
    const addonsRaw   = (addonsRawEl?.value || "").trim();

    let extrasSubtotal = 0;

    if (addonsRaw) {
      const catalogScript = document.getElementById("addonsCatalog");
      let catalog = {};

      if (catalogScript) {
        try {
          catalog = JSON.parse(catalogScript.textContent);
        } catch (err) {
          console.error("Error parseando addonsCatalog:", err);
        }
      }

      const pairs = addonsRaw.split(",");
      for (const pair of pairs) {
        const clean = pair.trim();
        if (!clean) continue;

        const [idStr, qtyStr] = clean.split(":");
        const id  = parseInt(idStr || "0", 10);
        const qty = parseInt(qtyStr || "0", 10);
        if (!id || !qty || !catalog[id]) continue;

        const srv   = catalog[id];
        const price = parseFloat(srv.precio || 0);
        const tipo  = srv.tipo || "por_evento";

        let lineTotal = 0;
        if (tipo === "por_evento") {
          lineTotal = price * qty;
        } else {
          lineTotal = price * qty * days;
        }

        extrasSubtotal += lineTotal;
      }
    }

    const subtotal = base + extrasSubtotal;
    const iva      = Math.round(subtotal * 0.16 * 100) / 100;
    const total    = subtotal + iva;

    return { base, extras: extrasSubtotal, iva, total };
  }

  // ==========================================================
  // 📥 Cargar SDK de PayPal dinámicamente
  // ==========================================================
  function loadPayPalSDK() {
    return new Promise((resolve, reject) => {
      if (window.paypal) return resolve();

      const clientId = window.PAYPAL_CLIENT_ID;
      if (!clientId) {
        const msg = "PAYPAL_CLIENT_ID no está definido. Revisa tu .env.";
        console.error(msg);
        if (window.alertify) alertify.error(msg); else alert(msg);
        return reject(new Error(msg));
      }

      const mode = (window.PAYPAL_MODE || "live") === "sandbox" ? "sandbox" : "live";

      const script = document.createElement("script");
      // Para sandbox / live el dominio es el mismo, cambia el client-id
      script.src = `https://www.paypal.com/sdk/js?client-id=${encodeURIComponent(
        clientId
      )}&currency=MXN&intent=capture`;
      script.async = true;
      script.onload = () => resolve();
      script.onerror = () => reject(new Error("No se pudo cargar el SDK de PayPal."));
      document.head.appendChild(script);
    });
  }

  // ==========================================================
  // 🚀 Enviar reserva a /reservas/linea después de onApprove
  // ==========================================================
  async function enviarReservaLinea(paypalOrderId) {
    const url = window.APP_URL_RESERVA_LINEA;
    if (!url) {
      throw new Error("No está definida APP_URL_RESERVA_LINEA.");
    }

    const csrf = getCsrfToken();
    const basePayload = getFormPayload();
    const { total }   = calcularTotales();

    const payload = {
      ...basePayload,
      paypal_order_id: paypalOrderId,
      // Solo informativo / logging; el backend recalcula y valida el total real
      total_local: total
    };

    const resp = await fetch(url, {
      method: "POST",
      headers: {
        "Content-Type": "application/json",
        "X-CSRF-TOKEN": csrf
      },
      body: JSON.stringify(payload)
    });

    if (!resp.ok) {
      const text = await resp.text();
      console.error("Error HTTP al crear la reserva en línea:", text);
      throw new Error("No se pudo crear la reservación en línea. Intenta nuevamente.");
    }

    const data = await resp.json();

    if (!data.ok) {
      console.error("Respuesta de reserva en línea con error:", data);
      throw new Error(data.message || "Error al confirmar la reservación en línea.");
    }

    return data;
  }

  // ==========================================================
  // 🧷 Inicializar botones de PayPal en el contenedor
  // ==========================================================
  function initPaypalButtons() {
    const { total } = calcularTotales();
    const amount    = (total > 0 ? total : 0).toFixed(2);

    if (!window.paypal || typeof window.paypal.Buttons !== "function") {
      const msg = "PayPal SDK no está disponible tras la carga.";
      console.error(msg);
      if (window.alertify) alertify.error(msg); else alert(msg);
      return;
    }

    // Limpiamos el contenedor antes de renderizar
    paypalContainer.innerHTML = "";

    paypal.Buttons({
      style: {
        layout: "vertical",
        color:  "gold",
        shape:  "rect",
        label:  "paypal"
      },

      createOrder: function (data, actions) {
        return actions.order.create({
          purchase_units: [
            {
              amount: {
                value: amount,
                currency_code: "MXN"
              }
            }
          ]
        });
      },

      onApprove: function (data, actions) {
        return actions.order.capture().then(async function () {
          try {
            const result = await enviarReservaLinea(data.orderID);

            const msg = `Reserva confirmada. Folio: ${result.folio || ""}`;
            if (window.alertify) alertify.success(msg); else alert(msg);

            // Aquí puedes redirigir al visor de reserva / página de gracias
            // window.location.href = `/ventas/reservacion/${result.id}`;
          } catch (err) {
            console.error(err);
            const msg = err.message || "Ocurrió un error al confirmar tu reserva.";
            if (window.alertify) alertify.error(msg); else alert(msg);
          }
        });
      },

      onError: function (err) {
        console.error("Error en PayPal Buttons:", err);
        const msg = "Ocurrió un problema al procesar el pago con PayPal.";
        if (window.alertify) alertify.error(msg); else alert(msg);
      }
    }).render("#paypal-button-container");
  }

  // ==========================================================
  // 🎬 Flujo principal: iniciar pago en línea
  // ==========================================================
  async function iniciarPagoEnLinea() {
    try {
      // Cerramos modal (si venía de ahí)
      if (modalMetodoPago) {
        modalMetodoPago.style.display = "none";
      }

      // Validar formulario antes de mostrar PayPal
      getFormPayload(); // si algo falta, lanza error

      // Mostrar contenedor de PayPal
      paypalContainer.style.display = "block";
      paypalContainer.innerHTML = "<div style='padding:10px;'>Cargando PayPal...</div>";

      await loadPayPalSDK();
      initPaypalButtons();
    } catch (err) {
      console.error(err);
      const msg = err.message || "No se pudo iniciar el pago en línea.";
      if (window.alertify) alertify.error(msg); else alert(msg);
    }
  }

  // ==========================================================
  // 🔗 Eventos y función global para Blade
  // ==========================================================
  if (btnPagoLinea) {
    btnPagoLinea.addEventListener("click", function (e) {
      e.preventDefault();

      // 👇 IMPORTANTE:
      // Si veníamos con plan="mostrador" pero el usuario eligió
      // "Pago en línea" en el modal, aquí lo convertimos a "linea"
      // para que la validación de getFormPayload no truene.
      const planInput = document.getElementById("plan");
      if (planInput) {
        planInput.value = "linea";
      }

      iniciarPagoEnLinea();
    });
  }

  // Para el script inline que hace:
  //   if (typeof window.handleReservaPagoEnLinea === 'function') ...
  window.handleReservaPagoEnLinea = iniciarPagoEnLinea;
});
