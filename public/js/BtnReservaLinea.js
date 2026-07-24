/* =====================================================================
 *  BtnReservaLinea.js — Versión limpia y optimizada (mayo 2026)
 *
 *  Encargado del flujo de PAGO EN LÍNEA con PayPal:
 *   - Valida campos visibles
 *   - Detecta moneda mostrada (MXN/USD) y convierte a MXN para PayPal
 *   - Carga SDK de PayPal dinámicamente
 *   - Inserta la reservación con idempotencia + reintentos
 *
 *  🔴 BUG CRÍTICO ARREGLADO:
 *   - Antes: si el cliente veía precio en USD ($61.73 USD), PayPal le
 *     cobraba 61.73 MXN (~$3.10 USD reales). Pérdida ~95% del cobro.
 *   - Ahora: detecta moneda mostrada y multiplica por EXCHANGE_RATE
 *     antes de mandar a PayPal en MXN.
 *
 *  OTROS CAMBIOS:
 *   - NO sobrescribe window.translations
 *   - Elimina 5 funciones duplicadas con reservaciones.js
 *   - Inyecta Drop Off (ID 11) cuando aplica
 *   - Idempotencia con flag reservaEnviada
 *   - Reintento automático con backoff si el POST falla post-cobro
 *   - Emite reserva:completada / reserva:cancelada
 *   - Usa window.APP_URL_RESERVA_LINEA (sin URL hardcoded)
 *   - Mensajes traducidos con window.translations (del Blade)
 * ===================================================================== */
(function () {
  "use strict";

  /* =================================================================
     CONSTANTES Y HELPERS
     ================================================================= */
  const qs = (s, r = document) => r.querySelector(s);

  let loaderReservaInterval = null;

  const mensajesLoader = [
    "Procesando tu reservación...",
    "Preparando tu vehículo...",
    "Generando tu folio...",
    "Confirmando disponibilidad..."
  ];

  const showLoaderReserva = () => {
    const loader = qs("#loaderReserva");
    const texto = qs("#loaderReservaTexto");

    if (loader) loader.style.display = "flex";

    let index = 0;

    if (texto) {
      texto.textContent = mensajesLoader[0];
    }

    clearInterval(loaderReservaInterval);

    loaderReservaInterval = setInterval(() => {
      index = (index + 1) % mensajesLoader.length;

      if (texto) {
        texto.textContent = mensajesLoader[index];
      }
    }, 2000);
  };

  const hideLoaderReserva = () => {
    const loader = qs("#loaderReserva");

    clearInterval(loaderReservaInterval);

    if (loader) loader.style.display = "none";
  };
  const EXCHANGE_RATE = 20; // ⚠️ Debe coincidir con reservaciones.js y backend
  const DROP_SERVICE_ID = "11";
  const MAX_RETRY_POST = 3;
  const RETRY_BACKOFF_MS = 2000;

  function getCurrentLocale() {
    const htmlLang = document.documentElement.lang || 'es';
    return htmlLang === 'en' ? 'en' : 'es';
  }

  function t(key, fallback) {
    return (window.translations && window.translations[key]) || fallback;
  }

  function getCsrfToken() {
    const meta = document.querySelector('meta[name="csrf-token"]');
    return meta ? meta.getAttribute("content") : "";
  }

  /* =================================================================
     HELPER: convertir fecha dd-mm-yyyy → yyyy-mm-dd
     ================================================================= */
  function toIsoDate(dateStr) {
    const s = String(dateStr || "").trim();
    if (!s) return "";
    if (/^\d{4}-\d{2}-\d{2}$/.test(s)) return s;

    let m = s.match(/^(\d{2})-(\d{2})-(\d{4})$/);
    if (m) return `${m[3]}-${m[2]}-${m[1]}`;
    m = s.match(/^(\d{2})\/(\d{2})\/(\d{4})$/);
    if (m) return `${m[3]}-${m[2]}-${m[1]}`;

    const d = new Date(s);
    if (!isNaN(d.getTime())) {
      const yyyy = d.getFullYear();
      const mm = String(d.getMonth() + 1).padStart(2, "0");
      const dd = String(d.getDate()).padStart(2, "0");
      return `${yyyy}-${mm}-${dd}`;
    }
    return s;
  }

  /* =================================================================
     HELPER: parse / serialize de addons (solo para inyectar Drop Off)
     ================================================================= */
  function parseAddonsStr(str) {
    const map = new Map();
    String(str || "").split(",").map(s => s.trim()).filter(Boolean).forEach(pair => {
      const m = pair.match(/^(\d+)\s*:\s*(\d+)$/);
      if (m) {
        const qty = Math.max(0, parseInt(m[2], 10) || 0);
        if (qty > 0) map.set(m[1], qty);
      } else {
        const id = pair.replace(/\D/g, "");
        if (id) map.set(id, 1);
      }
    });
    return map;
  }

  function serializeAddonsMap(map) {
    return Array.from(map.entries())
      .filter(([, q]) => (q || 0) > 0)
      .map(([id, q]) => `${id}:${q}`)
      .join(",");
  }

  /* =================================================================
     HELPER: inyectar Drop Off (servicio ID 11) cuando aplique
     (mismo patrón que en BtnReserva.js)
     ================================================================= */
  function injectDropOffIfNeeded(addonsStr) {
    const table = qs("#cotizacionDoc");
    if (!table) return addonsStr || "";

    const pickup  = table.dataset.pickup;
    const dropoff = table.dataset.dropoff;
    const km      = parseFloat(table.dataset.km || 0);
    const costoKm = parseFloat(table.dataset.costokm || 0);

    if (pickup && dropoff && pickup !== dropoff && km > 0 && costoKm > 0) {
      const map = parseAddonsStr(addonsStr || "");
      map.set(DROP_SERVICE_ID, 1);
      return serializeAddonsMap(map);
    }
    return addonsStr || "";
  }

  /* =================================================================
     🔴 HELPER CRÍTICO: leer total del DOM Y convertir a MXN
     - reservaciones.js renderiza #qTotal en MXN o USD según idioma.
     - PayPal siempre cobra en MXN (currency_code: "MXN").
     - Por tanto: si el texto contiene "USD", multiplicamos × EXCHANGE_RATE
       para convertir el monto a su equivalente real en MXN.
     ================================================================= */
  function obtenerTotalesDelDOM() {
    const parseAmount = (selector) => {
      const el = qs(selector);
      if (!el) return { amountMXN: 0, displayed: "" };

      const txt = String(el.textContent || "").trim();
      const isUSD = /USD/i.test(txt);

      // Extraer el número limpio
      const cleaned = txt.replace(/[^\d.,-]/g, "").replace(/,/g, "");
      const num = parseFloat(cleaned);
      if (isNaN(num)) return { amountMXN: 0, displayed: txt };

      // Si está mostrado en USD, convertir a MXN multiplicando por el tipo de cambio
      const amountMXN = isUSD ? num * EXCHANGE_RATE : num;

      return { amountMXN, displayed: txt, isUSD };
    };

    return {
      base:   parseAmount("#qBase"),
      extras: parseAmount("#qExtras"),
      iva:    parseAmount("#qIva"),
      total:  parseAmount("#qTotal")
    };
  }

  /* =================================================================
     HELPER: total a cobrar en PayPal (siempre en MXN)
     ================================================================= */
  function obtenerTotalParaPayPal() {
    const totales = obtenerTotalesDelDOM();
    return Number(totales.total.amountMXN || 0);
  }

  /* =================================================================
     BOOT
     ================================================================= */
  document.addEventListener("DOMContentLoaded", () => {
    const modalMetodoPago     = qs("#modalMetodoPago");
    const modalPagoOnline     = qs("#modalPagoOnline");
    const btnPagoLinea        = qs("#btnPagoLinea");
    const form                = qs("#formCotizacion");
    const paypalContainer     = qs("#paypal-button-container");
    const cerrarModalMetodoX  = qs("#cerrarModalMetodoX");
    const cerrarModalPagoOnline = qs("#cerrarModalPagoOnline");

    // Variables de control
    let scrollPosition = 0;
    let modalLineaAbierto = false;
    let reservaEnviada = false; // 🛡️ Idempotencia: evita doble POST

    if (!form || !paypalContainer) return;

    /* =================================================================
       PAYLOAD DEL FORMULARIO
       ================================================================= */
    function getFormPayload() {
      const categoriaId       = qs("#categoria_id")?.value || "";
      const plan              = qs("#plan")?.value || "";
      const addonsRaw         = qs("#addons_payload")?.value || qs("#addonsHidden")?.value || "";
      const pickupDate        = qs("#pickup_date")?.value || "";
      let   pickupTime        = qs("#pickup_time")?.value || qs("#pickup_time_hidden")?.value || "";
      const dropoffDate       = qs("#dropoff_date")?.value || "";
      let   dropoffTime       = qs("#dropoff_time")?.value || qs("#dropoff_time_hidden")?.value || "";
      const pickupSucursalId  = qs("#pickup_sucursal_id")?.value || "";
      const dropoffSucursalId = qs("#dropoff_sucursal_id")?.value || "";

      const nombreCompleto = (qs("#nombreCompleto")?.value || qs("#nombreCliente")?.value || "").trim();
      const telefono       = (qs("#telefonoCliente")?.value || "").trim();
      const email          = (qs("#correoCliente")?.value || "").trim();
      const vuelo          = (qs("#vuelo")?.value || "").trim();

      // Plan B para horas: leer del select por name, no por ID
      if (!pickupTime) {
        const ph = qs('select[name="pickup_h"]')?.value || "";
        if (ph) pickupTime = ph.padStart(2, "0") + ":00";
      }
      if (!dropoffTime) {
        const dh = qs('select[name="dropoff_h"]')?.value || "";
        if (dh) dropoffTime = dh.padStart(2, "0") + ":00";
      }

      // Asegurar formato H:i (por si vienen con segundos "13:00:00")
      if (pickupTime  && pickupTime.length  > 5) pickupTime  = pickupTime.substring(0, 5);
      if (dropoffTime && dropoffTime.length > 5) dropoffTime = dropoffTime.substring(0, 5);

      // Validaciones (mensajes traducidos por el Blade)
      if (!categoriaId) {
        throw new Error(getCurrentLocale() === 'en'
          ? "The rental category is missing."
          : "Falta la categoría seleccionada de la renta.");
      }
      if (!pickupDate || !dropoffDate || !pickupTime || !dropoffTime) {
        throw new Error(t('required_missing', getCurrentLocale() === 'en'
          ? "Required information missing."
          : "Falta información requerida."));
      }
      if (plan !== "linea") {
        throw new Error(getCurrentLocale() === 'en'
          ? "To use online payment, please select the online payment plan."
          : "Para utilizar pago en línea, selecciona el plan de pago en línea.");
      }

      // Inyectar Drop Off (ID 11) si pickup ≠ dropoff
      const addonsFinal = injectDropOffIfNeeded(addonsRaw);

      // Fecha de nacimiento (hidden #dob, formato AAAA-MM-DD)
      const dob = (qs("#dob")?.value || "").trim();

      return {
        categoria_id:        categoriaId,
        plan,
        addons:              addonsFinal,
        pickup_date:         toIsoDate(pickupDate),
        pickup_time:         pickupTime,
        dropoff_date:        toIsoDate(dropoffDate),
        dropoff_time:        dropoffTime,
        pickup_sucursal_id:  pickupSucursalId,
        dropoff_sucursal_id: dropoffSucursalId,
        nombre:              nombreCompleto,
        telefono,
        email,
        vuelo,
        fecha_nacimiento:    dob
      };
    }

    /* =================================================================
       CARGAR SDK DE PAYPAL
       ================================================================= */
    function loadPayPalSDK() {
      return new Promise((resolve, reject) => {
        if (window.paypal) { resolve(); return; }

        const clientId = window.PAYPAL_CLIENT_ID;
        if (!clientId) {
          const msg = getCurrentLocale() === 'en'
            ? "PAYPAL_CLIENT_ID is not defined. Check your .env."
            : "PAYPAL_CLIENT_ID no está definido. Revisa tu .env.";
          console.error(msg);
          if (window.alertify) alertify.error(msg);
          reject(new Error(msg));
          return;
        }

        const paypalLocale = getCurrentLocale() === 'es' ? 'es_MX' : 'en_US';

        const script = document.createElement("script");
        script.src = `https://www.paypal.com/sdk/js?client-id=${encodeURIComponent(clientId)}&currency=MXN&intent=capture&locale=${paypalLocale}`;
        script.async = true;
        script.onload  = () => resolve();
        script.onerror = () => reject(new Error(getCurrentLocale() === 'en'
          ? "Could not load PayPal SDK."
          : "No se pudo cargar el SDK de PayPal."));
        document.head.appendChild(script);
      });
    }

    /* =================================================================
       ENVIAR RESERVA AL BACKEND CON IDEMPOTENCIA + RETRY
       ================================================================= */
    async function enviarReservaLinea(paypalOrderId, attempt = 1) {
      // 🛡️ Idempotencia: si ya se envió correctamente, no repetir
      if (reservaEnviada) {
        console.warn("Reserva ya fue enviada, evitando duplicado.");
        return null;
      }

      const url = window.APP_URL_RESERVA_LINEA;
      if (!url) {
        throw new Error("No está definida APP_URL_RESERVA_LINEA.");
      }

      const csrf = getCsrfToken();
      const basePayload = getFormPayload();
      const totalCobrado = obtenerTotalParaPayPal(); // ← ya en MXN

      const payload = {
        ...basePayload,
        paypal_order_id: paypalOrderId,
        total_local: totalCobrado
      };

      try {
        const resp = await fetch(url, {
          method: "POST",
          headers: {
            "Content-Type":     "application/json",
            "X-CSRF-TOKEN":     csrf,
            "X-Requested-With": "XMLHttpRequest",
            "Accept":           "application/json"
          },
          body: JSON.stringify(payload)
        });

        const data = await resp.json().catch(() => ({}));

        if (!resp.ok || data.ok === false) {
          throw new Error(data.message || (getCurrentLocale() === 'en'
            ? "Could not create online reservation. Please try again."
            : "No se pudo crear la reservación en línea. Intenta nuevamente."));
        }

        reservaEnviada = true; // ✅ marcar como enviada para evitar duplicados
        return data;

      } catch (err) {
        // 🔄 Reintento automático con backoff
        if (attempt < MAX_RETRY_POST) {
          console.warn(`Reintentando POST (${attempt}/${MAX_RETRY_POST}) en ${RETRY_BACKOFF_MS}ms...`);
          await new Promise(r => setTimeout(r, RETRY_BACKOFF_MS * attempt));
          return enviarReservaLinea(paypalOrderId, attempt + 1);
        }
        throw err;
      }
    }

    /* =================================================================
       INICIALIZAR BOTONES DE PAYPAL
       ================================================================= */
    function initPaypalButtons() {
      const totalEnMXN = obtenerTotalParaPayPal();
      const amount = (totalEnMXN > 0 ? totalEnMXN : 0).toFixed(2);

      if (!window.paypal || typeof window.paypal.Buttons !== "function") {
        const msg = getCurrentLocale() === 'en'
          ? "PayPal SDK is not available."
          : "PayPal SDK no está disponible.";
        if (window.alertify) alertify.error(msg); else alert(msg);
        return;
      }

      paypalContainer.innerHTML = "";
      paypalContainer.style.display = "block";

      try {
        window.paypal.Buttons({
          style: { layout: "vertical", color: "gold", shape: "rect", label: "paypal" },

          createOrder: function (_data, actions) {
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
              try {
                const overallStatus = orderData?.status;
                const firstCapture  = orderData?.purchase_units?.[0]?.payments?.captures?.[0];
                const captureStatus = firstCapture?.status;
                const reason        = firstCapture?.status_details?.reason || "";

                if (overallStatus !== "COMPLETED" || captureStatus !== "COMPLETED") {
                  console.warn("Pago no completado:", { overallStatus, captureStatus, reason });

                  const locale = getCurrentLocale();
                  let msgUser = locale === 'en'
                    ? "Your bank or PayPal did not authorize the charge. No payment was made."
                    : "Tu banco o PayPal no autorizó el cargo. No se realizó ningún cobro.";

                  if (reason === "INSUFFICIENT_FUNDS") {
                    msgUser = locale === 'en'
                      ? "Your bank reported insufficient funds. No payment was made."
                      : "Tu banco reportó fondos insuficientes. No se realizó el cobro.";
                  } else if (reason === "PAYER_CANNOT_PAY") {
                    msgUser = locale === 'en'
                      ? "PayPal indicated the payment method cannot process the charge."
                      : "PayPal indicó que el medio de pago no puede procesar el cargo.";
                  } else if (reason === "RISK_DECLINE") {
                    msgUser = locale === 'en'
                      ? "PayPal declined the payment due to security review."
                      : "PayPal rechazó el pago por revisión de seguridad.";
                  }

                  if (window.alertify) alertify.error(msgUser);
                  else alert(msgUser);
                  document.dispatchEvent(new CustomEvent('reserva:cancelada'));
                  return;
                }

                // ⚠️ A PARTIR DE AQUÍ: PayPal YA COBRÓ
                // Si el POST falla, el dinero queda cobrado. Por eso hay retry.
                let result;
                try {
                  showLoaderReserva();
                  result = await enviarReservaLinea(data.orderID);
                  if (!result) return; // duplicado evitado por idempotencia
                } catch (postErr) {
                    hideLoaderReserva();
                  // 🚨 Caso peor: PayPal cobró pero después de N reintentos no pudimos guardar
                  console.error("PayPal cobró pero el POST falló después de reintentos:", postErr);

                  const locale = getCurrentLocale();
                  const msgCritico = locale === 'en'
                    ? `⚠️ IMPORTANT: Your payment was processed by PayPal but we couldn't complete the reservation in our system.\n\n` +
                      `Please save this PayPal Order ID and contact us:\n\n${data.orderID}\n\n` +
                      `Email: contacto@viajero.com.mx\nPhone: 01 (442) 303 2668`
                    : `⚠️ IMPORTANTE: Tu pago fue procesado en PayPal pero no pudimos completar la reservación en nuestro sistema.\n\n` +
                      `Por favor guarda este ID de PayPal y contáctanos:\n\n${data.orderID}\n\n` +
                      `Email: contacto@viajero.com.mx\nTel: 01 (442) 303 2668`;

                  if (window.alertify) alertify.alert(
                    locale === 'en' ? "Action required" : "Acción requerida",
                    msgCritico.replace(/\n/g, '<br>')
                  );
                  else alert(msgCritico);

                  document.dispatchEvent(new CustomEvent('reserva:cancelada'));
                  return;
                }

                // ✅ Éxito completo
                const payload = getFormPayload();
                const fromDom = obtenerTotalesDelDOM();

                // Preferir totales del backend (ya en MXN); fallback al DOM ya convertido
                const base   = Number(result.subtotal  ?? fromDom.base.amountMXN)   || 0;
                const iva    = Number(result.impuestos ?? fromDom.iva.amountMXN)    || 0;
                const total  = Number(result.total     ?? fromDom.total.amountMXN)  || 0;
                const extras = Number(result.extras    ?? fromDom.extras.amountMXN) || 0;

                const fmt = new Intl.NumberFormat("es-MX", { style: "currency", currency: "MXN" });

                const folio = result.folio || "";
                const pickup  = `${payload.pickup_date} ${payload.pickup_time}`;
                const dropoff = `${payload.dropoff_date} ${payload.dropoff_time}`;

                const locale = getCurrentLocale();
                const msgExito = locale === 'en'
                  ? `
                    ✅ Your online reservation has been confirmed.<br><br>
                    Folio: <b>${folio}</b><br>
                    Pick-up: <b>${pickup}</b><br>
                    Return: <b>${dropoff}</b><br><br>
                    <b>Base rate:</b> ${fmt.format(base)}<br>
                    <b>Rental options:</b> ${fmt.format(extras)}<br>
                    <b>Fees and TAXES (16%):</b> ${fmt.format(iva)}<br>
                    <b>Total:</b> ${fmt.format(total)}<br><br>
                    📩 You will receive a confirmation by email.
                  `
                  : `
                    ✅ Su reservación en línea fue confirmada correctamente.<br><br>
                    Folio: <b>${folio}</b><br>
                    Entrega: <b>${pickup}</b><br>
                    Devolución: <b>${dropoff}</b><br><br>
                    <b>Tarifa base:</b> ${fmt.format(base)}<br>
                    <b>Opciones de renta:</b> ${fmt.format(extras)}<br>
                    <b>Cargos e IVA (16%):</b> ${fmt.format(iva)}<br>
                    <b>Total:</b> ${fmt.format(total)}<br><br>
                    📩 Recibirá confirmación por correo electrónico.
                  `;

                // Emitir evento ANTES de mostrar alert
                document.dispatchEvent(new CustomEvent('reserva:completada', {
                  detail: { folio, total, plan: 'linea' }
                }));

                hideLoaderReserva();

                const tituloExito = locale === 'en'
                  ? "Online reservation confirmed"
                  : "Reservación en línea confirmada";

                if (window.alertify) {
                  alertify.alert(tituloExito, msgExito, function () {
                    try { localStorage.removeItem("viajero_resv_filters_v1"); } catch (_) {}
                    try { sessionStorage.clear(); } catch (_) {}
                    window.location.href = window.location.pathname + "?step=1&reset=1";
                  });
                } else {
                  hideLoaderReserva();
                  alert(tituloExito);
                  try { localStorage.removeItem("viajero_resv_filters_v1"); } catch (_) {}
                  try { sessionStorage.clear(); } catch (_) {}
                  window.location.href = window.location.pathname + "?step=1&reset=1";
                }

              } catch (err) {
                hideLoaderReserva();
                console.error("Error en onApprove:", err);
                const msg = err.message || (getCurrentLocale() === 'en'
                  ? "Error confirming your reservation."
                  : "Error al confirmar tu reserva.");
                if (window.alertify) alertify.error(msg); else alert(msg);
                document.dispatchEvent(new CustomEvent('reserva:cancelada'));
              }
            });
          },

          onCancel: function () {
            hideLoaderReserva();
            const msg = getCurrentLocale() === 'en'
              ? "You canceled the payment on PayPal."
              : "Cancelaste el pago en PayPal.";
            if (window.alertify) alertify.message(msg); else alert(msg);
            document.dispatchEvent(new CustomEvent('reserva:cancelada'));
          },

          onError: function (err) {
            hideLoaderReserva();
            console.error("Error en PayPal:", err);
            const msg = getCurrentLocale() === 'en'
              ? "Error processing the payment."
              : "Error al procesar el pago.";
            if (window.alertify) alertify.error(msg); else alert(msg);
            document.dispatchEvent(new CustomEvent('reserva:cancelada'));
          }
        }).render("#paypal-button-container");

      } catch (err) {
        console.error("Error al renderizar PayPal:", err);
        paypalContainer.innerHTML = `<div style="text-align:center; color:red;">${
          getCurrentLocale() === 'en' ? 'Error loading PayPal.' : 'Error al cargar PayPal.'
        }</div>`;
      }
    }

    /* =================================================================
       BLOQUEO DE SCROLL (sin paddingRight hardcoded)
       ================================================================= */
    function bloquearScrollBody() {
      scrollPosition = window.pageYOffset || document.documentElement.scrollTop;
      // Calcular ancho del scrollbar dinámicamente (evita salto visual)
      const scrollbarWidth = window.innerWidth - document.documentElement.clientWidth;
      document.body.style.position = 'fixed';
      document.body.style.top      = `-${scrollPosition}px`;
      document.body.style.width    = '100%';
      document.body.style.overflow = 'hidden';
      if (scrollbarWidth > 0) document.body.style.paddingRight = scrollbarWidth + 'px';
    }

    function restaurarScrollBody() {
      document.body.style.position     = '';
      document.body.style.top          = '';
      document.body.style.width        = '';
      document.body.style.overflow     = '';
      document.body.style.paddingRight = '';
      window.scrollTo(0, scrollPosition);
    }

    /* =================================================================
       INICIAR PAGO EN LÍNEA
       ================================================================= */
    async function iniciarPagoEnLinea() {
      try {
        modalLineaAbierto = true;
        reservaEnviada    = false; // reset por cada nuevo intento de pago

        if (modalMetodoPago) modalMetodoPago.style.display = "none";
        showLoaderReserva();

        // Validar datos antes de mostrar PayPal
        try {
          getFormPayload();
        } catch (validationError) {
          hideLoaderReserva();
          modalLineaAbierto = false;
          if (window.alertify) alertify.error(validationError.message);
          else alert(validationError.message);
          document.dispatchEvent(new CustomEvent('reserva:cancelada'));
          return;
        }

        bloquearScrollBody();
        hideLoaderReserva();
        if (modalPagoOnline) modalPagoOnline.style.display = "flex";

        if (paypalContainer) {
          const loadingMsg = getCurrentLocale() === 'en' ? 'Loading PayPal...' : 'Cargando PayPal...';
          paypalContainer.innerHTML = `<div style="text-align:center; padding:40px;">🔄 ${loadingMsg}</div>`;
          paypalContainer.style.display = "block";
        }

        await loadPayPalSDK();
        initPaypalButtons();

      } catch (err) {
        hideLoaderReserva();
        console.error("Error iniciando pago en línea:", err);
        modalLineaAbierto = false;
        const msg = getCurrentLocale() === 'en' ? 'Error loading PayPal.' : 'Error al cargar PayPal.';
        if (window.alertify) alertify.error(msg); else alert(msg);

        if (modalPagoOnline) {
          modalPagoOnline.style.display = "none";
          restaurarScrollBody();
        }
        document.dispatchEvent(new CustomEvent('reserva:cancelada'));
      }
    }

    /* =================================================================
       CERRAR MODALES
       ================================================================= */
    function cerrarModalSeleccion() {
      if (modalMetodoPago && !modalLineaAbierto) {
        modalMetodoPago.style.display = "none";
      }
    }

    function cerrarModalPago() {
      if (modalPagoOnline) {
        modalPagoOnline.style.display = "none";
        restaurarScrollBody();
        modalLineaAbierto = false;
      }
    }

    /* =================================================================
       EVENTOS
       ================================================================= */
    document.addEventListener('reserva:validacionExitosa', function (e) {
      if (modalLineaAbierto) return;
      const currentPlan = e.detail?.plan;

      if (currentPlan === 'mostrador' && modalMetodoPago) {
        modalMetodoPago.style.display = "flex";
      } else if (currentPlan === 'linea') {
        iniciarPagoEnLinea();
      }
    });

    cerrarModalMetodoX?.addEventListener("click", cerrarModalSeleccion);

    cerrarModalPagoOnline?.addEventListener("click", function (e) {
      e.preventDefault();
      e.stopPropagation();
      cerrarModalPago();
    });

    modalMetodoPago?.addEventListener("click", function (e) {
      if (e.target === modalMetodoPago && !modalLineaAbierto) {
        modalMetodoPago.style.display = "none";
      }
    });

    btnPagoLinea?.addEventListener("click", function (e) {
      e.preventDefault();
      e.stopPropagation();
      const planInput = qs("#plan");
      if (planInput) planInput.value = "linea";
      iniciarPagoEnLinea();
    });

    // Expuesta al global (consumida por reservaciones.js)
    window.handleReservaPagoEnLinea = iniciarPagoEnLinea;
  });
})();
