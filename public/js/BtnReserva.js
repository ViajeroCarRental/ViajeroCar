/* =====================================================================
 *  BtnReserva.js — Versión limpia y optimizada (mayo 2026)
 *
 *  Encargado del flujo de PAGO EN MOSTRADOR:
 *   - Valida campos visibles
 *   - Envía la reservación al endpoint Laravel
 *   - Muestra modal de éxito con folio + itinerario
 *
 *  CAMBIOS vs versión anterior:
 *   - NO sobrescribe window.translations (vienen del Blade Laravel)
 *   - Elimina 5 funciones duplicadas con reservaciones.js
 *     (los addons ya están listos en #addonsHidden / #addons_payload)
 *   - Usa window.APP_URL_RESERVA_MOSTRADOR (sin URL hardcoded)
 *   - Lee #nombreCompleto (visible) con fallback a #nombreCliente (hidden)
 *   - Valida formato de email con regex
 *   - Emite eventos custom: reserva:completada / reserva:cancelada
 *   - Sin "12:00" hardcoded: si falta la hora, alerta al usuario
 * ===================================================================== */
(function () {
  "use strict";

  /* =================================================================
     HELPER: lectura segura de campos
     ================================================================= */
  const qs = (s, r = document) => r.querySelector(s);
  const val = (sel) => qs(sel)?.value?.trim() || "";

  /* =================================================================
     HELPER: obtener traducción del Blade (con fallback)
     ================================================================= */
  function t(key, fallback) {
    return (window.translations && window.translations[key]) || fallback;
  }

  /* =================================================================
     HELPER: obtener locale actual
     ================================================================= */
  function getCurrentLocale() {
    const htmlLang = document.documentElement.lang || 'es';
    return htmlLang === 'en' ? 'en' : 'es';
  }

  /* =================================================================
     HELPER: convertir dd-mm-yyyy → yyyy-mm-dd
     ================================================================= */
  function toIsoDate(dmy) {
    const s = String(dmy || "").trim();
    const m = s.match(/^(\d{2})-(\d{2})-(\d{4})$/);
    return m ? `${m[3]}-${m[2]}-${m[1]}` : s;
  }

  /* =================================================================
     HELPER: formato bonito de fechas para el modal de éxito
     ================================================================= */
  function formatPrettyDate(dateISO) {
    try {
      const months = (window.translations && window.translations.months) ||
        (getCurrentLocale() === 'es'
          ? ['Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun', 'Jul', 'Ago', 'Sep', 'Oct', 'Nov', 'Dic']
          : ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec']);

      const f = String(dateISO || "").trim().match(/^(\d{4})-(\d{2})-(\d{2})$/);
      if (!f) return dateISO;
      return `${f[3]}-${months[parseInt(f[2], 10) - 1]}-${f[1]}`;
    } catch (_) {
      return dateISO;
    }
  }

  /* =================================================================
     HELPER: limpiar storage al finalizar reserva
     ================================================================= */
  function clearReservationStorage() {
    try { localStorage.removeItem("viajero_resv_filters_v1"); } catch (_) {}
    try { sessionStorage.clear(); } catch (_) {}
  }

  /* =================================================================
     HELPER: parse / serialize de addons (solo para agregar Drop Off)
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
     HELPER: agregar Drop Off (servicio ID 11) cuando aplique
     - Si pickup ≠ dropoff Y hay km × costoKm > 0
     - Esto es CRÍTICO: si no se agrega aquí, el backend no guarda
       el Drop Off en reservacion_servicio
     ================================================================= */
  const DROP_SERVICE_ID = "11";

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
     BOOT
     ================================================================= */
  document.addEventListener("DOMContentLoaded", () => {
    const btnCounterPayment = qs("#btnPagoMostrador");
    const paymentModal      = qs("#modalMetodoPago");

    if (!btnCounterPayment) return;

    btnCounterPayment.addEventListener("click", async () => {
      // ============================================================
      // 1) VALIDACIÓN
      // ============================================================
      // Leer del campo visible primero, hidden como fallback
      const nameInput  = qs("#nombreCompleto") || qs("#nombreCliente");
      const emailInput = qs("#correoCliente");
      const phoneInput = qs("#telefonoCliente");
      const termsCheck = qs("#acepto");

      const name     = nameInput?.value?.trim()  || "";
      const email    = emailInput?.value?.trim() || "";
      const phone    = phoneInput?.value?.trim() || "";
      const flight   = qs("#vuelo")?.value?.trim() || "";
      const accepted = termsCheck?.checked;

      const missingFields = [];
      const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

      if (!name)                     missingFields.push(t('full_name', 'Full name'));
      if (!email)                    missingFields.push(t('email', 'Email'));
      else if (!emailRegex.test(email)) missingFields.push(t('email_invalid', 'Invalid email'));
      if (!phone)                    missingFields.push(t('phone', 'Phone'));
      if (!accepted)                 missingFields.push(t('acceptance_policies', 'Acceptance of policies'));

      if (missingFields.length > 0) {
        if (paymentModal) paymentModal.style.display = "none";

        const msg =
          "<b>" + t('cannot_proceed', 'We cannot proceed.') + "</b><br>" +
          t('please_complete', 'Please complete:') + "<br>• " +
          missingFields.join("<br>• ");

        if (window.alertify) alertify.error(msg);
        else alert(t('required_missing', 'Required information missing.'));

        if (!name && nameInput)        nameInput.focus();
        else if (!email && emailInput) emailInput.focus();
        else if (!phone && phoneInput) phoneInput.focus();

        document.dispatchEvent(new CustomEvent('reserva:cancelada'));
        return;
      }

      // ============================================================
      // 2) PREPARAR PAYLOAD
      // ============================================================
      if (paymentModal) paymentModal.style.display = "none";

      const form = qs("#formCotizacion");
      if (!form) {
        if (window.alertify) alertify.error(t('reservation_form_not_found', 'Reservation form not found.'));
        else alert(t('reservation_form_not_found', 'Reservation form not found.'));
        document.dispatchEvent(new CustomEvent('reserva:cancelada'));
        return;
      }

      try {
        const urlParams = new URLSearchParams(window.location.search);

        // Fechas
        const startRaw =
          val("#start") || urlParams.get("pickup_date") || urlParams.get("start") || "";
        const endRaw =
          val("#end")   || urlParams.get("dropoff_date") || urlParams.get("end") || "";

        const pickup_date  = toIsoDate(startRaw);
        const dropoff_date = toIsoDate(endRaw);

        // Horas (con validación, sin hardcoded "12:00")
        let pickup_time = val("#pickup_time") || val("#pickup_time_hidden");
        if (!pickup_time) {
          const ph = val("#pickup_h") || val('[name="pickup_h"]');
          pickup_time = ph ? ph.padStart(2, "0") + ":00" : "";
        }

        let dropoff_time = val("#dropoff_time") || val("#dropoff_time_hidden");
        if (!dropoff_time) {
          const dh = val("#dropoff_h") || val('[name="dropoff_h"]');
          dropoff_time = dh ? dh.padStart(2, "0") + ":00" : "";
        }

        if (!pickup_date || !dropoff_date || !pickup_time || !dropoff_time) {
          if (window.alertify) alertify.error(t('required_missing', 'Required information missing.'));
          else alert(t('required_missing', 'Required information missing.'));
          document.dispatchEvent(new CustomEvent('reserva:cancelada'));
          return;
        }

        // IDs y addons
        const category_id       = val("#categoria_id")       || urlParams.get("categoria_id")       || "";
        const pickup_branch_id  = val("#pickup_sucursal_id") || urlParams.get("pickup_sucursal_id") || "";
        const dropoff_branch_id = val("#dropoff_sucursal_id")|| urlParams.get("dropoff_sucursal_id")|| "";

        // Los addons ya vienen procesados desde reservaciones.js
        // (incluido el conductor joven si aplica).
        // PERO el Drop Off (servicio ID 11) solo se inyecta al enviar
        // cuando pickup ≠ dropoff Y hay km × costoKm > 0
        let addons =
          val("#addons_payload") ||
          val("#addonsHidden") ||
          urlParams.get("addons") ||
          "";

        addons = injectDropOffIfNeeded(addons);

        // ============================================================
        // 3) CSRF Y ENVÍO
        // ============================================================
        const token =
          document.querySelector('meta[name="csrf-token"]')?.getAttribute("content") ||
          form.querySelector('input[name="_token"]')?.value || "";

        if (!token) {
          console.error("CSRF token not found for counter payment.");
          if (window.alertify) alertify.error(t('security_token_not_found', 'Security token not found. Please refresh the page.'));
          else alert(t('security_token_not_found', 'Security token not found.'));
          document.dispatchEvent(new CustomEvent('reserva:cancelada'));
          return;
        }

        const payload = {
          categoria_id:        category_id,
          pickup_date,
          pickup_time,
          dropoff_date,
          dropoff_time,
          pickup_sucursal_id:  pickup_branch_id,
          dropoff_sucursal_id: dropoff_branch_id,
          nombre:              name,
          email,
          telefono:            phone,
          vuelo:               flight,
          addons,
        };

        const url = window.APP_URL_RESERVA_MOSTRADOR || "/reservas";

        const res = await fetch(url, {
          method: "POST",
          headers: {
            "Content-Type":     "application/json",
            "X-CSRF-TOKEN":     token,
            "X-Requested-With": "XMLHttpRequest",
            Accept:             "application/json",
          },
          body: JSON.stringify(payload),
        });

        const data = await res.json().catch(() => ({}));

        if (!res.ok || data.ok === false) {
          console.error("Error registering reservation (counter):", data);
          if (window.alertify) alertify.error(t('could_not_register', 'Could not register the reservation.'));
          else alert(t('could_not_register', 'Could not register the reservation.'));
          document.dispatchEvent(new CustomEvent('reserva:cancelada'));
          return;
        }

        // ============================================================
        // 4) MODAL DE ÉXITO
        // ============================================================
        const totalLabelEl = qs("#qTotal");
        const totalText    = totalLabelEl ? totalLabelEl.textContent.trim() :
          new Intl.NumberFormat("es-MX", { style: "currency", currency: "MXN" }).format(data.total || 0);

        const folio = data.folio?.replace(/^COT/, "RES") || "RES-PENDING";

        const places = document.querySelectorAll('.sum-place');
        const pickupBranchName  = places[0] ? places[0].innerText.trim() : '—';
        const dropoffBranchName = places[1] ? places[1].innerText.trim() : pickupBranchName;

        const pickupPretty  = formatPrettyDate(pickup_date);
        const dropoffPretty = formatPrettyDate(dropoff_date);

        const successMsg = `
          <div class="resv-alert-success">
            <div class="resv-alert-check-card">
              <div class="resv-alert-check-icon-wrap">
                <span class="resv-alert-check-icon">✓</span>
              </div>
              <div class="resv-alert-check-text">
                ${t('reservation_registered', 'Your reservation has been successfully registered.')}
              </div>
            </div>

            <div class="resv-alert-card resv-alert-itinerary">
              <div class="resv-alert-card-title">${t('itinerary', 'ITINERARY')}</div>

              <div class="resv-alert-item">
                <span class="resv-alert-label">${t('folio', 'Folio')}:</span>
                <b>${folio}</b>
              </div>

              <div class="resv-section-title">${t('pickup_label', 'Pick-up')}:</div>

              <div class="resv-alert-item">
                <span class="resv-alert-label">${t('place_label', 'Location')}:</span>
                <b>${pickupBranchName}</b>
              </div>

              <div class="resv-alert-item">
                <span class="resv-alert-label">${t('date_label', 'Date')}:</span>
                <b>${pickupPretty}</b>
              </div>

              <div class="resv-alert-item">
                <span class="resv-alert-label">${t('time_label', 'Time')}:</span>
                <b>${pickup_time} HRS</b>
              </div>

              <div class="resv-section-title">${t('return_label', 'Return')}:</div>

              <div class="resv-alert-item">
                <span class="resv-alert-label">${t('place_label', 'Location')}:</span>
                <b>${dropoffBranchName}</b>
              </div>

              <div class="resv-alert-item">
                <span class="resv-alert-label">${t('date_label', 'Date')}:</span>
                <b>${dropoffPretty}</b>
              </div>

              <div class="resv-alert-item">
                <span class="resv-alert-label">${t('time_label', 'Time')}:</span>
                <b>${dropoff_time} HRS</b>
              </div>
            </div>

            <div class="resv-alert-card">
              <div class="resv-alert-item resv-alert-total">
                <span class="resv-alert-label">${t('total_label', 'Total')}:</span>
                <b>${totalText}</b>
              </div>
            </div>

            <div class="resv-alert-mail-glass">
              <span class="resv-alert-mail-icon">✉</span>
              <span>${t('confirmation_email', 'You will receive a confirmation by email.')}</span>
            </div>
          </div>
        `;

        // Emitir evento ANTES de mostrar el modal de éxito
        document.dispatchEvent(new CustomEvent('reserva:completada', {
          detail: { folio, total: data.total, plan: 'mostrador' }
        }));

        if (window.alertify) {
          const alertInstance = alertify.alert("", successMsg, function () {
            clearReservationStorage();
            window.location.href = "/";
          });

          alertInstance.set("labels",    { ok: t('go_to_homepage', 'Go to homepage') });
          alertInstance.set("closable",  true);
          alertInstance.set("movable",   false);
          alertInstance.set("resizable", false);
          alertInstance.set("pinnable",  false);
          alertInstance.set("transition", "zoom");

          alertInstance.set("onshow", function () {
            this.elements.dialog.classList.add("resv-alertify-success");
          });
          alertInstance.set("onclose", function () {
            this.elements.dialog.classList.remove("resv-alertify-success");
          });
        } else {
          alert(t('reservation_success_fallback', 'Reservation registered successfully. Check your confirmation email.'));
          clearReservationStorage();
          window.location.href = "/";
        }

      } catch (error) {
        console.error("Error in counter payment:", error);
        if (window.alertify) alertify.error(t('error_occurred', 'An error occurred while registering the reservation.'));
        else alert(t('error_occurred', 'An error occurred while registering the reservation.'));
        document.dispatchEvent(new CustomEvent('reserva:cancelada'));
      }
    });
  });
})();
