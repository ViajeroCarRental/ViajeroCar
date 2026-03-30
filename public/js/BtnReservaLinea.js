// ============================================================
// 💳 PAGO EN LÍNEA CON PAYPAL (LIVE / SANDBOX según .env)
// ============================================================

document.addEventListener("DOMContentLoaded", () => {
  // Elementos del DOM
  const modalMetodoPago   = document.getElementById("modalMetodoPago");
  const modalPagoOnline   = document.getElementById("modalPagoOnline");
  const btnPagoLinea      = document.getElementById("btnPagoLinea");
  const btnReservar       = document.getElementById("btnReservar");
  const btnReservarMovil  = document.getElementById("btnReservarMovil");
  const form              = document.getElementById("formCotizacion");
  const paypalContainer   = document.getElementById("paypal-button-container");

  // Botones de cierre
  const cerrarModalMetodoX   = document.getElementById("cerrarModalMetodoX");
  const cerrarModalMetodo    = document.getElementById("cerrarModalMetodo");
  const cerrarModalPagoOnline = document.getElementById("cerrarModalPagoOnline");

  // Variables de control
  let paypalSDKLoaded = false;
  let scrollPosition = 0;
  let modalLineaAbierto = false; // Bandera para saber si el modal de línea está abierto

  // Si no existen elementos clave, salimos
  if (!form || !paypalContainer) {
    console.error("Elementos necesarios no encontrados");
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
  // 👶 MENOR DE EDAD – helpers locales para ajustar addons
  // ==========================================================
  const YOUNG_DRIVER_SERVICE_ID = '5';
  const YOUNG_DRIVER_MIN_AGE    = 25;

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
    const pickupInput = document.getElementById("pickup_date") || document.querySelector('input[name="pickup_date"]');
    if (!pickupInput || !pickupInput.value) return new Date();
    const s = String(pickupInput.value).trim();
    let m = s.match(/^(\d{2})-(\d{2})-(\d{4})$/);
    if (m) return new Date(+m[3], +m[2] - 1, +m[1]);
    m = s.match(/^(\d{4})-(\d{2})-(\d{2})$/);
    if (m) return new Date(+m[1], +m[2] - 1, +m[3]);
    const d = new Date(s);
    return isNaN(d.getTime()) ? new Date() : d;
  }

  function applyYoungDriverAddonOnString(addonsStr) {
    try {
      if (!YOUNG_DRIVER_SERVICE_ID) return addonsStr || "";
      const dobHidden = document.querySelector("#dob");
      if (!dobHidden) return addonsStr || "";
      const dobStr = (dobHidden.value || "").trim();
      const age = computeAgeFromDobLocal(dobStr, getPickupDateForAgeLocal());
      const map = parseAddonsStringToMapLocal(addonsStr || "");
      if (age != null && age < YOUNG_DRIVER_MIN_AGE) {
        map.set(String(YOUNG_DRIVER_SERVICE_ID), 1);
      } else {
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
    const nombreCompleto     = (nombreCompletoEl?.value || "").trim();
    const telefono           = (telefonoEl?.value || "").trim();
    const email              = (correoEl?.value || "").trim();
    const vuelo              = (vueloEl?.value || "").trim();

    if (!categoriaId) {
      throw new Error("Falta la categoría seleccionada de la renta.");
    }
    if (!pickupDate || !dropoffDate || !pickupTime || !dropoffTime) {
      throw new Error("Faltan las fechas u horas de la reservación.");
    }
    if (plan !== "linea") {
      throw new Error("Para utilizar pago en línea, selecciona el plan de pago en línea.");
    }

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
      nombre:              nombreCompleto,
      telefono:            telefono,
      email:               email,
      vuelo:               vuelo
    };
  }

  // ==========================================================
  // 🧮 Helper: calcular TOTAL local
  // ==========================================================
  function calcularTotales() {
    const cotDoc = document.getElementById("cotizacionDoc");
    if (!cotDoc) {
      return { base: 0, extras: 0, iva: 0, total: 0 };
    }

    const base = parseFloat(cotDoc.dataset.base || "0");
    const days = parseInt(cotDoc.dataset.days || "1", 10) || 1;
    const addonsRawEl = document.getElementById("addons_payload");
    const addonsRaw   = (addonsRawEl?.value || "").trim();
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
  // 📥 Cargar SDK de PayPal dinámicamente con Idioma Dinámico
  // ==========================================================
  function loadPayPalSDK() {
    return new Promise((resolve, reject) => {
      if (window.paypal && window.paypalSDKLoaded) {
        resolve();
        return;
      }

      const clientId = window.PAYPAL_CLIENT_ID;
      if (!clientId) {
        const msg = "PAYPAL_CLIENT_ID no está definido. Revisa tu .env.";
        console.error(msg);
        if (window.alertify) alertify.error(msg); else alert(msg);
        reject(new Error(msg));
        return;
      }

      // --- DETECTAR IDIOMA ACTUAL ---
      // Obtenemos el idioma de <html lang="..."> que definiste en tu layout
      const currentLang = document.documentElement.lang || 'es';

      // Mapeamos el idioma al formato de PayPal
      // Si es 'es' usamos es_MX, si no, por defecto en_US
      const paypalLocale = (currentLang === 'es') ? 'es_MX' : 'en_US';

      const script = document.createElement("script");
      // Usamos la variable paypalLocale en el parámetro &locale=
      script.src = `https://www.paypal.com/sdk/js?client-id=${encodeURIComponent(clientId)}&currency=MXN&intent=capture&locale=${paypalLocale}`;

      script.async = true;
      script.onload = () => {
        window.paypalSDKLoaded = true;
        resolve();
      };
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

    console.log("💰 Total a pagar:", amount, "MXN");

    if (!window.paypal || typeof window.paypal.Buttons !== "function") {
      const msg = "PayPal SDK no está disponible tras la carga.";
      console.error(msg);
      if (window.alertify) alertify.error(msg); else alert(msg);
      return;
    }

    if (paypalContainer) {
      paypalContainer.innerHTML = "";
      paypalContainer.style.display = "block";
    }

    try {
      paypal.Buttons({
        style: {
          layout: "vertical",
          color:  "gold",
          shape:  "rect",
          label:  "paypal"
        },

        createOrder: function (data, actions) {
          return actions.order.create({
            purchase_units: [{
              amount: {
                value: amount,
                currency_code: "MXN"
              }
            }]
          });
        },

        onApprove: function (data, actions) {
          return actions.order.capture().then(async function (orderData) {
            console.log("✅ PayPal orderData:", orderData);

            try {
              const overallStatus = orderData?.status;
              const firstPU       = orderData?.purchase_units?.[0];
              const firstCapture  = firstPU?.payments?.captures?.[0];
              const captureStatus = firstCapture?.status;
              const reason        = firstCapture?.status_details?.reason || "";

              if (overallStatus !== "COMPLETED" || captureStatus !== "COMPLETED") {
                console.warn("⚠️ Pago no completado:", { overallStatus, captureStatus, reason });

                let msgUser = "Tu banco o PayPal no autorizó el cargo. No se realizó ningún cobro.";

                if (reason === "INSUFFICIENT_FUNDS") {
                  msgUser = "Tu banco reportó fondos insuficientes. No se realizó el cobro.";
                } else if (reason === "PAYER_CANNOT_PAY") {
                  msgUser = "PayPal indicó que el medio de pago no puede procesar el cargo.";
                } else if (reason === "RISK_DECLINE") {
                  msgUser = "PayPal rechazó el pago por revisión de seguridad.";
                }

                if (window.alertify) alertify.error(msgUser);
                else alert(msgUser);
                return;
              }

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
                  try { localStorage.removeItem("viajero_resv_filters_v1"); } catch (e) {}
                  try { sessionStorage.clear(); } catch (e) {}
                  window.location.href = window.location.pathname + "?step=1&reset=1";
                });
              } else {
                alert("Reservación en línea confirmada.");
                window.location.href = window.location.pathname + "?step=1&reset=1";
              }

            } catch (err) {
              console.error("❌ Error:", err);
              const msg = err.message || "Error al confirmar tu reserva.";
              if (window.alertify) alertify.error(msg); else alert(msg);
            }
          });
        },

        onCancel: function (data) {
          console.log("ℹ️ Pago cancelado");
          const msg = "Cancelaste el pago en PayPal.";
          if (window.alertify) alertify.message(msg);
          else alert(msg);
        },

        onError: function (err) {
          console.error("❌ Error en PayPal:", err);
          let msg = "Error al procesar el pago.";
          if (window.alertify) alertify.error(msg);
          else alert(msg);
        }
      }).render("#paypal-button-container");

      console.log("✅ Botones de PayPal renderizados");

    } catch (err) {
      console.error("❌ Error:", err);
      if (paypalContainer) {
        paypalContainer.innerHTML = '<div style="text-align:center; color:red;">Error al cargar PayPal.</div>';
      }
    }
  }

  // ==========================================================
  // 🔒 Funciones para controlar el scroll del body
  // ==========================================================
  function bloquearScrollBody() {
    scrollPosition = window.pageYOffset || document.documentElement.scrollTop;
    document.body.style.position = 'fixed';
    document.body.style.top = `-${scrollPosition}px`;
    document.body.style.width = '100%';
    document.body.style.overflow = 'hidden';
    document.body.style.paddingRight = '15px';
  }

  function restaurarScrollBody() {
    document.body.style.position = '';
    document.body.style.top = '';
    document.body.style.width = '';
    document.body.style.overflow = '';
    document.body.style.paddingRight = '';
    window.scrollTo(0, scrollPosition);
  }

  // ==========================================================
  // 🎬 Iniciar pago en línea
  // ==========================================================
  async function iniciarPagoEnLinea() {
    try {
      console.log("🚀 Iniciando pago en línea");

      // Marcar que el modal de línea está abierto
      modalLineaAbierto = true;

      // Cerrar el modal de selección de método si está abierto
      if (modalMetodoPago) {
        modalMetodoPago.style.display = "none";
      }

      // Validar datos del formulario
      try {
        getFormPayload();
      } catch (validationError) {
        modalLineaAbierto = false;
        if (window.alertify) alertify.error(validationError.message);
        else alert(validationError.message);
        return;
      }

      // Bloquear scroll y mostrar modal
      bloquearScrollBody();

      if (modalPagoOnline) {
        modalPagoOnline.style.display = "flex";
      }

      if (paypalContainer) {
        paypalContainer.innerHTML = '<div style="text-align:center; padding:40px;">🔄 Cargando PayPal...</div>';
        paypalContainer.style.display = "block";
      }

      await loadPayPalSDK();
      initPaypalButtons();

      console.log("✅ Pago en línea iniciado");

    } catch (err) {
      console.error("❌ ERROR:", err);
      modalLineaAbierto = false;
      if (window.alertify) alertify.error("Error al cargar PayPal.");
      else alert("Error al cargar PayPal.");

      if (modalPagoOnline) {
        modalPagoOnline.style.display = "none";
        restaurarScrollBody();
      }
    }
  }

  // ==========================================================
  // 🔗 Funciones para cerrar modales
  // ==========================================================
  function cerrarModalSeleccion() {
    if (modalMetodoPago && !modalLineaAbierto) {
      modalMetodoPago.style.display = "none";
    }
  }

  function cerrarModalPago() {
    if (modalPagoOnline) {
      modalPagoOnline.style.display = "none";
      restaurarScrollBody();
      modalLineaAbierto = false; // Resetear la bandera
    }
  }

// ==========================================================
// 📌 Configuración de eventos
// ==========================================================

// Abrir modal de selección de método de pago (botón Reservar)
document.addEventListener('reserva:validacionExitosa', function(e) {
    // Verificar que no haya un modal de línea abierto
    if (modalLineaAbierto) return;

    const currentPlan = e.detail?.plan;
    console.log('🎯 Evento recibido en BtnReservaLinea, plan:', currentPlan);

    // Si el plan es "mostrador", abrir el modal de selección
    if (currentPlan === 'mostrador' && modalMetodoPago) {
        modalMetodoPago.style.display = "flex";
        console.log('📱 Modal de método de pago abierto (mostrador)');
    }
    // Si el plan es "linea", iniciar pago en línea directamente
    else if (currentPlan === 'linea') {
        iniciarPagoEnLinea();
        console.log('💳 Iniciando pago en línea directamente');
    }
});

// Cerrar modal de selección de método (botones X y Cancelar)
if (cerrarModalMetodoX) {
    cerrarModalMetodoX.addEventListener("click", cerrarModalSeleccion);
}
if (cerrarModalMetodo) {
    cerrarModalMetodo.addEventListener("click", cerrarModalSeleccion);
}

// Cerrar modal de pago en línea (SOLO con la X)
if (cerrarModalPagoOnline) {
    cerrarModalPagoOnline.addEventListener("click", function(e) {
        e.preventDefault();
        e.stopPropagation();
        cerrarModalPago();
    });
}

// ✅ Mantener: cerrar modal de selección al hacer clic fuera
if (modalMetodoPago) {
    modalMetodoPago.addEventListener("click", function(e) {
        if (e.target === modalMetodoPago && !modalLineaAbierto) {
            modalMetodoPago.style.display = "none";
        }
    });
}

// Botón "PREPAGAR EN LÍNEA" en el modal de selección
if (btnPagoLinea) {
    btnPagoLinea.addEventListener("click", function(e) {
        e.preventDefault();
        e.stopPropagation();

        const planInput = document.getElementById("plan");
        if (planInput) planInput.value = "linea";

        iniciarPagoEnLinea();
    });
}

window.handleReservaPagoEnLinea = iniciarPagoEnLinea;

console.log("✅ Módulo de pago en línea inicializado (event-driven)");

// Cerrar modal de selección de método (botones X y Cancelar)
if (cerrarModalMetodoX) {
    cerrarModalMetodoX.addEventListener("click", cerrarModalSeleccion);
}
if (cerrarModalMetodo) {
    cerrarModalMetodo.addEventListener("click", cerrarModalSeleccion);
}

// Cerrar modal de pago en línea (SOLO con la X)
if (cerrarModalPagoOnline) {
    cerrarModalPagoOnline.addEventListener("click", function(e) {
        e.preventDefault();
        e.stopPropagation();
        cerrarModalPago();
    });
}

// ❌ NO AGREGAR event listener para cerrar al hacer clic fuera del modal de línea
// ✅ Solo permitir cerrar el modal de selección al hacer clic fuera
if (modalMetodoPago) {
    modalMetodoPago.addEventListener("click", function(e) {
        if (e.target === modalMetodoPago && !modalLineaAbierto) {
            modalMetodoPago.style.display = "none";
        }
    });
}

// Botón "PREPAGAR EN LÍNEA" en el modal de selección
if (btnPagoLinea) {
    btnPagoLinea.addEventListener("click", function(e) {
        e.preventDefault();
        e.stopPropagation();

        const planInput = document.getElementById("plan");
        if (planInput) planInput.value = "linea";

        iniciarPagoEnLinea();
    });
}

  window.handleReservaPagoEnLinea = iniciarPagoEnLinea;

  console.log("✅ Módulo de pago en línea inicializado");
});
