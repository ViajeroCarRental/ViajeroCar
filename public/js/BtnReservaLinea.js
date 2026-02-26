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

    // ==========================================================
  // 👶 MENOR DE EDAD – helpers locales para ajustar addons
  // ==========================================================
  // 👉 Usa el MISMO ID de servicio que configuraste en los otros JS
  const YOUNG_DRIVER_SERVICE_ID = '123'; // <-- cámbialo por el id real del servicio "Conductor menor de edad"
  const YOUNG_DRIVER_MIN_AGE    = 25;    // menor de 25 años paga cargo

  function parseAddonsStringToMapLocal(str) {
    const map = new Map();
    String(str || "")
      .split(",")
      .map(s => s.trim())
      .filter(Boolean)
      .forEach(pair => {
        const m = pair.match(/^(\d+)\s*:\s*(\d+)$/);
        if (m) {
          const id  = m[1];
          const qty = Math.max(0, parseInt(m[2], 10) || 0);
          if (qty > 0) map.set(id, qty);
        } else {
          const id = pair.replace(/\D/g, "");
          if (id) map.set(id, 1);
        }
      });
    return map;
  }

  function serializeAddonsMapLocal(map) {
    return Array.from(map.entries())
      .filter(([, q]) => (q || 0) > 0)
      .map(([id, q]) => `${id}:${q}`)
      .join(",");
  }

  function computeAgeFromDobLocal(dobStr, refDate) {
    if (!dobStr) return null;

    const m = String(dobStr).trim().match(/^(\d{4})-(\d{2})-(\d{2})$/);
    if (!m) return null;

    const birth = new Date(+m[1], +m[2] - 1, +m[3]);
    if (isNaN(birth.getTime())) return null;

    const ref = (refDate instanceof Date && !isNaN(refDate)) ? refDate : new Date();

    let age = ref.getFullYear() - birth.getFullYear();
    const mm = ref.getMonth() - birth.getMonth();
    if (mm < 0 || (mm === 0 && ref.getDate() < birth.getDate())) {
      age--;
    }

    if (age < 0 || age > 120) return null;
    return age;
  }

  function getPickupDateForAgeLocal() {
    const pickupInput =
      document.getElementById("pickup_date") ||
      document.querySelector('input[name="pickup_date"]');

    if (!pickupInput || !pickupInput.value) return new Date();

    const s = String(pickupInput.value).trim();

    // dd-mm-YYYY
    let m = s.match(/^(\d{2})-(\d{2})-(\d{4})$/);
    if (m) return new Date(+m[3], +m[2] - 1, +m[1]);

    // YYYY-mm-dd
    m = s.match(/^(\d{4})-(\d{2})-(\d{2})$/);
    if (m) return new Date(+m[1], +m[2] - 1, +m[3]);

    const d = new Date(s);
    return isNaN(d.getTime()) ? new Date() : d;
  }

  // 💡 Recibe el string de addons y regresa una versión ajustada
  //    agregando/eliminando el servicio de menor de edad según la DOB.
  function applyYoungDriverAddonOnString(addonsStr) {
    try {
      if (!YOUNG_DRIVER_SERVICE_ID) return addonsStr || "";

      const dobHidden = document.querySelector("#dob"); // hidden YYYY-MM-DD
      if (!dobHidden) return addonsStr || "";

      const dobStr = (dobHidden.value || "").trim();
      const age = computeAgeFromDobLocal(dobStr, getPickupDateForAgeLocal());

      const map = parseAddonsStringToMapLocal(addonsStr || "");

      if (age != null && age < YOUNG_DRIVER_MIN_AGE) {
        // Menor de la edad límite → forzamos cantidad 1 del servicio
        map.set(String(YOUNG_DRIVER_SERVICE_ID), 1);
      } else {
        // Mayor o edad inválida → quitamos el cargo automático
        map.delete(String(YOUNG_DRIVER_SERVICE_ID));
      }

      return serializeAddonsMapLocal(map);
    } catch (e) {
      console.warn("No se pudo aplicar la regla de menor de edad en addons (PayPal):", e);
      return addonsStr || "";
    }
  }

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

        // 👉 Ajustar addons con la regla de menor de edad (si aplica)
    const addonsFinal = applyYoungDriverAddonOnString(addonsRaw);
    return {
      categoria_id:        categoriaId,
      plan:                plan,
      addons:              addonsFinal,
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

    // ✅ Aplicar también aquí la regla de menor de edad
    const addonsForCalc = applyYoungDriverAddonOnString(addonsRaw);

    let extrasSubtotal = 0;

    if (addonsForCalc) {
      const catalogScript = document.getElementById("addonsCatalog");
      let catalog = {};

      if (catalogScript) {
        try {
          catalog = JSON.parse(catalogScript.textContent);
        } catch (err) {
          console.error("Error parseando addonsCatalog:", err);
        }
      }

      const pairs = addonsForCalc.split(",");
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
        return actions.order.capture().then(async function (orderData) {
          console.log("✅ PayPal orderData:", orderData);

          try {
            // ==============================
            // 🔍 Revisar estado del pago
            // ==============================
            const overallStatus = orderData?.status;
            const firstPU       = orderData?.purchase_units?.[0];
            const firstCapture  = firstPU?.payments?.captures?.[0];

            const captureStatus = firstCapture?.status;
            const reason        = firstCapture?.status_details?.reason || "";

            // Si NO está COMPLETED, asumimos que el banco/PayPal rechazó el cobro
            if (overallStatus !== "COMPLETED" || captureStatus !== "COMPLETED") {
              console.warn("⚠️ Pago no completado:", {
                overallStatus,
                captureStatus,
                reason
              });

              let msgUser = "Tu banco o PayPal no autorizó el cargo. No se realizó ningún cobro.";

              // Detalles típicos que devuelve PayPal
              if (reason === "INSUFFICIENT_FUNDS") {
                msgUser = "Tu banco reportó fondos insuficientes. No se realizó el cobro. Por favor verifica tu saldo e inténtalo de nuevo.";
              } else if (reason === "PAYER_CANNOT_PAY") {
                msgUser = "PayPal indicó que el medio de pago no puede procesar el cargo. Intenta con otra tarjeta o forma de pago.";
              } else if (reason === "RISK_DECLINE") {
                msgUser = "PayPal rechazó el pago por revisión de seguridad. Intenta nuevamente o usa otro método de pago.";
              }

              if (window.alertify) {
                alertify.error(msgUser);
              } else {
                alert(msgUser);
              }

              // ❌ Ojo: NO creamos reservación si el pago no está COMPLETED
              return;
            }

            // ==============================
            // 💾 Aquí ya sabemos que el pago sí fue COMPLETED
            //    -> ahora sí creamos la reservación en nuestro backend
            // ==============================
            const result = await enviarReservaLinea(data.orderID);

            const payload = getFormPayload();
            const { base, extras, iva, total } = calcularTotales();

            const fmt = new Intl.NumberFormat("es-MX", {
              style: "currency",
              currency: "MXN",
            });

            const pickup  = `${payload.pickup_date} ${payload.pickup_time}`;
            const dropoff = `${payload.dropoff_date} ${payload.dropoff_time}`;

            const baseTxt   = fmt.format(base);
            const extrasTxt = fmt.format(extras);
            const ivaTxt    = fmt.format(iva);
            const totalTxt  = fmt.format(total);

            const folio = result.folio || "";

            const msgExito = `
  ✅ Su reservación en línea fue confirmada correctamente.<br><br>
  Folio: <b>${folio}</b><br>
  Entrega: <b>${pickup}</b><br>
  Devolución: <b>${dropoff}</b><br><br>

  <b>Tarifa base:</b> ${baseTxt}<br>
  <b>Opciones de renta:</b> ${extrasTxt}<br>
  <b>Cargos e IVA (16%):</b> ${ivaTxt}<br>
  <b>Total:</b> ${totalTxt}<br><br>

  📩 Recibirá confirmación por correo electrónico.
`;

            if (window.alertify) {
              alertify.alert("Reservación en línea confirmada", msgExito, function () {
                // 1) Limpiar persistencia del wizard (Paso 1)
                try {
                  localStorage.removeItem("viajero_resv_filters_v1");
                } catch (e) {
                  console.warn("No se pudo limpiar localStorage:", e);
                }

                // 2) Limpiar cualquier dato temporal en sesión
                try {
                  sessionStorage.clear();
                } catch (e) {
                  console.warn("No se pudo limpiar sessionStorage:", e);
                }

                // 3) Redirigir al Paso 1, limpio
                try {
                  const url = new URL(window.location.href);
                  url.search = "";
                  url.hash = "";

                  url.searchParams.set("step", "1");
                  url.searchParams.set("reset", "1");

                  window.location.href = url.pathname + "?" + url.searchParams.toString();
                } catch (e) {
                  // Fallback simple
                  window.location.href = window.location.pathname + "?step=1&reset=1";
                }
              });
            } else {
              // Fallback sin alertify: alert nativa + reset inmediato
              alert("Reservación en línea confirmada. Revisa tu correo de confirmación.");

              try {
                localStorage.removeItem("viajero_resv_filters_v1");
              } catch (e) {
                console.warn("No se pudo limpiar localStorage:", e);
              }
              try {
                sessionStorage.clear();
              } catch (e) {
                console.warn("No se pudo limpiar sessionStorage:", e);
              }

              window.location.href = window.location.pathname + "?step=1&reset=1";
            }

          } catch (err) {
            console.error("❌ Error en onApprove / enviarReservaLinea:", err);
            const msg = err.message || "Ocurrió un error al confirmar tu reserva. No se realizó ningún cargo adicional.";
            if (window.alertify) alertify.error(msg); else alert(msg);
          }
        });
      },

      onCancel: function (data) {
        console.log("ℹ️ Pago cancelado por el usuario:", data);
        const msg = "Cancelaste el pago en PayPal. No se realizó ningún cobro ni se generó la reservación.";
        if (window.alertify) {
          if (alertify.message) {
            alertify.message(msg);
          } else {
            alertify.error(msg);
          }
        } else {
          alert(msg);
        }
      },

      onError: function (err) {
        console.error("❌ Error en PayPal Buttons:", err);

        // Mensaje genérico al usuario
        let msg = "Ocurrió un problema al procesar el pago con PayPal. No se realizó ningún cobro.";

        // Si viene algo de red / conexión lo podemos suavizar un poco
        const errStr = (err && err.toString && err.toString()) || "";
        if (errStr.toLowerCase().includes("network") || errStr.toLowerCase().includes("fetch")) {
          msg = "Parece que hubo un problema de conexión al comunicarse con PayPal. No se realizó ningún cobro. Verifica tu internet e inténtalo de nuevo.";
        }

        if (window.alertify) {
          alertify.error(msg);
        } else {
          alert(msg);
        }
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
