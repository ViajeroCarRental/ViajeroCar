// ============================================================
// === BOOK BUTTON: Shows payment modal and executes flow ===
// ✅ Adjusted version: the "Book" logic per plan
//    (online / counter) is now controlled by the Blade view.
//    This file focuses on:
//      - Counter payment -> inserts reservation
//      - Online payment -> (handled in BtnReservaLinea.js)
// ============================================================

document.addEventListener("DOMContentLoaded", () => {
  const btnCounterPayment = document.getElementById("btnPagoMostrador");
  const paymentModal      = document.getElementById("modalMetodoPago");

  // ============================================================
  // ⚙️ YOUNG DRIVER – CONFIG AND HELPERS ONLY FOR THIS FILE
  // ============================================================
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
    const pickupInput = document.querySelector("#start") || document.querySelector('input[name="pickup_date"]');
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
      console.warn("Could not apply young driver rule to addons:", e);
      return addonsStr || "";
    }
  }

  // ============================================================
  // === OPTION: COUNTER PAYMENT -> Insert reservation ==========
  // ============================================================
  if (btnCounterPayment) {
    btnCounterPayment.addEventListener("click", async () => {

      const nameInput    = document.querySelector("#nombreCliente");
      const emailInput   = document.querySelector("#correoCliente");
      const phoneInput   = document.querySelector("#telefonoCliente");
      const termsCheck   = document.querySelector("#acepto");

      const name     = nameInput?.value?.trim() || "";
      const email    = emailInput?.value?.trim() || "";
      const phone    = phoneInput?.value?.trim() || "";
      const flight   = document.querySelector("#vuelo")?.value?.trim() || "";
      const accepted = termsCheck?.checked;

      let missingFields = [];

      if (!name) missingFields.push(window.translations?.full_name || "Full name");
      if (!email) missingFields.push(window.translations?.email || "Email");
      if (!phone) missingFields.push(window.translations?.phone || "Phone");
      if (!accepted) missingFields.push(window.translations?.acceptance_policies || "Acceptance of policies");

      if (missingFields.length > 0) {
        if (paymentModal) paymentModal.style.display = "none";

        const msg =
          "<b>" + (window.translations?.cannot_proceed || "We cannot proceed.") + "</b><br>" +
          (window.translations?.please_complete || "Please complete:") + "<br>• " +
          missingFields.join("<br>• ");

        if (window.alertify) {
          alertify.error(msg);
        } else {
          alert(window.translations?.required_missing || "Required information missing.");
        }

        if (!name && nameInput) nameInput.focus();
        else if (!email && emailInput) emailInput.focus();
        else if (!phone && phoneInput) phoneInput.focus();

        return;
      }

      if (paymentModal) paymentModal.style.display = "none";

      try {
        const form = document.querySelector("#formCotizacion");
        if (!form) {
          if (window.alertify) alertify.error(window.translations?.reservation_form_not_found || "Reservation form not found.");
          else alert(window.translations?.reservation_form_not_found || "Reservation form not found.");
          return;
        }

        const val = (sel) => document.querySelector(sel)?.value?.trim() || "";

        function toIsoDate(dmy) {
          const s = String(dmy || "").trim();
          const m = s.match(/^(\d{2})-(\d{2})-(\d{4})$/);
          if (m) {
            return `${m[3]}-${m[2]}-${m[1]}`;
          }
          return s;
        }

        const urlParams = new URLSearchParams(window.location.search);

        const startRaw =
          val("#start") ||
          urlParams.get("pickup_date") ||
          urlParams.get("start") ||
          "";

        const endRaw =
          val("#end") ||
          urlParams.get("dropoff_date") ||
          urlParams.get("end") ||
          "";

        const pickup_date  = toIsoDate(startRaw);
        const dropoff_date = toIsoDate(endRaw);

        let pickup_time  = val("#pickup_time_hidden");
        let dropoff_time = val("#dropoff_time_hidden");

        function getTimeFromSummary(selector, index = 0) {
  const el = document.querySelectorAll(selector)[index];
  if (!el) return "";

  const txt = el.textContent.trim(); // "10:00 HRS"
  const match = txt.match(/(\d{2}:\d{2})/);

  return match ? match[1] : "";
}

if (!pickup_time) {
  pickup_time = getTimeFromSummary(".dt-time", 0) || "00:00";
}

if (!dropoff_time) {
  pickup_time = pickup_time || "00:00"; // fallback
  dropoff_time = getTimeFromSummary(".dt-time", 1) || "00:00";
}

        const category_id         = val("#categoria_id") || urlParams.get("categoria_id") || "";
        const pickup_branch_id    = urlParams.get("pickup_sucursal_id") || "";
        const dropoff_branch_id   = urlParams.get("dropoff_sucursal_id") || "";

        console.log("🕐 FINAL TIMES:", {
  pickup_time,
  dropoff_time
});

        // =====================================================
        // === ID CATEGORÍA / SUCURSALES / ADDONS =============
        // =====================================================
        const categoria_id        = val("#categoria_id") || urlParams.get("categoria_id") || "";
        const pickup_sucursal_id  = urlParams.get("pickup_sucursal_id") || "";
        const dropoff_sucursal_id = urlParams.get("dropoff_sucursal_id") || "";

                const hiddenAddons = document.querySelector("#addonsHidden");
        let addons = "";

        if (hiddenAddons && hiddenAddons.value.trim() !== "") {
          addons = hiddenAddons.value.trim();
        } else {
          const fromUrl = urlParams.get("addons");
          if (fromUrl && fromUrl.trim() !== "") {
            addons = fromUrl.trim();
          }
        }

        addons = applyYoungDriverAddonOnString(addons);

        const table = document.querySelector("#cotizacionDoc");
        let dropoff_total = 0;

        if (table) {
          const pickup  = table.dataset.pickup;
          const dropoff = table.dataset.dropoff;
          const km      = parseFloat(table.dataset.km || 0);
          const costokm = parseFloat(table.dataset.costokm || 0);

          if (pickup && dropoff && pickup !== dropoff && km > 0 && costokm > 0) {
            const DROP_SERVICE_ID = 11;
            const map = parseAddonsStringToMapLocal(addons);
            map.set(String(DROP_SERVICE_ID), 1);
            addons = serializeAddonsMapLocal(map);
            dropoff_total = km * costokm;
          }
        }

        console.log("🕐 HORAS REALES:", {
pickup_h: val("#pickup_h"),
  dropoff_h: val("#dropoff_h"),
  pickup_hidden: val("#pickup_time_hidden"),
  dropoff_hidden: val("#dropoff_time_hidden"),
});

        const payload = {
          categoria_id: category_id,
          pickup_date,
          pickup_time,
          dropoff_date,
          dropoff_time,
          pickup_sucursal_id: pickup_branch_id,
          dropoff_sucursal_id: dropoff_branch_id,
          nombre: name,
          email,
          telefono: phone,
          vuelo: flight,
          addons,

           dropoff_total
        };
        console.log("🚀 PAYLOAD ENVIADO:", payload);

        const url = "/reservas";

        const token =
          document.querySelector('meta[name="csrf-token"]')?.getAttribute("content") ||
          form.querySelector('input[name="_token"]')?.value ||
          "";

        if (!token) {
          if (window.alertify) {
            alertify.error(window.translations?.security_token_not_found || "Security token not found. Please refresh the page and try again.");
          } else {
            alert(window.translations?.security_token_not_found || "Security token not found. Please refresh the page and try again.");
          }
          console.error("CSRF token not found for counter payment.");
          return;
        }

        const res = await fetch(url, {
          method: "POST",
          headers: {
            "Content-Type": "application/json",
            "X-CSRF-TOKEN": token,
            "X-Requested-With": "XMLHttpRequest",
            Accept: "application/json",
          },
          body: JSON.stringify(payload),
        });

        const data = await res.json().catch(() => ({}));

        if (!res.ok || data.ok === false) {
          console.error("Error registering reservation (counter):", data);
          if (window.alertify) alertify.error(window.translations?.could_not_register || "Could not register the reservation.");
          else alert(window.translations?.could_not_register || "Could not register the reservation.");
          return;
        }

        const subtotal  = data.subtotal || 0;
        const taxes     = data.impuestos || 0;
        const total     = data.total || 0;

        const fmt = new Intl.NumberFormat("es-MX", {
          style: "currency",
          currency: "MXN",
        });

        const subtotalFallback  = fmt.format(subtotal);
        const taxesFallback     = fmt.format(taxes);
        const totalFallback     = fmt.format(total);

        const baseLabelEl   = document.querySelector("#qBase");
        const extrasLabelEl = document.querySelector("#qExtras");
        const taxLabelEl    = document.querySelector("#qIva");
        const totalLabelEl  = document.querySelector("#qTotal");

        const baseText   = baseLabelEl   ? baseLabelEl.textContent.trim()   : subtotalFallback;
        const extrasText = extrasLabelEl ? extrasLabelEl.textContent.trim() : fmt.format(0);
        const taxText    = taxLabelEl    ? taxLabelEl.textContent.trim()    : taxesFallback;
        const totalText  = totalLabelEl  ? totalLabelEl.textContent.trim()  : totalFallback;

        const folio = data.folio?.replace(/^COT/, "RES") || "RES-PENDING";

        function formatPrettyDate(dateISO) {
          try {
            const months = window.translations?.months || ["Jan", "Feb", "Mar", "Apr", "May", "Jun", "Jul", "Aug", "Sep", "Oct", "Nov", "Dec"];

            const f = String(dateISO || "").trim().match(/^(\d{4})-(\d{2})-(\d{2})$/);
            if (!f) return dateISO;

            const yyyy = f[1];
            const mm   = parseInt(f[2], 10);
            const dd   = f[3];

            return `${dd}-${months[mm - 1]}-${yyyy}`;
          } catch (e) {
            return dateISO;
          }
        }

        const pickupPretty  = formatPrettyDate(pickup_date);
        const dropoffPretty = formatPrettyDate(dropoff_date);

        const successMsg = `
          <div class="resv-alert-success">
            <div class="resv-alert-check-card">
              <div class="resv-alert-check-icon-wrap">
                <span class="resv-alert-check-icon">✓</span>
              </div>
              <div class="resv-alert-check-text">
                ${window.translations?.reservation_registered || "Your reservation has been successfully registered."}
              </div>
            </div>

            <div class="resv-alert-card resv-alert-itinerary">
              <div class="resv-alert-card-title">${window.translations?.itinerary || "Itinerary"}</div>
              <div class="resv-alert-item">
                <span class="resv-alert-label">${window.translations?.folio || "Folio"}</span>
                <b>${folio}</b>
              </div>
              <div class="resv-alert-item">
                <span class="resv-alert-label">${window.translations?.pickup_label || "Pick-up"}</span>
                <b>${pickupPretty}</b>
              </div>
              <div class="resv-alert-item">
                <span class="resv-alert-label">${window.translations?.return_label || "Return"}</span>
                <b>${dropoffPretty}</b>
              </div>
            </div>

            <div class="resv-alert-card">
              <div class="resv-alert-card-title">${window.translations?.payment_summary || "Payment Summary"}</div>
              <div class="resv-alert-item">
                <span class="resv-alert-label">${window.translations?.base_rate || "Base rate"}</span>
                <span>${baseText}</span>
              </div>
              <div class="resv-alert-item">
                <span class="resv-alert-label">${window.translations?.rental_options || "Rental options"}</span>
                <span>${extrasText}</span>
              </div>
              <div class="resv-alert-item">
                <span class="resv-alert-label">${window.translations?.charges_vat || "Charges and VAT (16%)"}</span>
                <span>${taxText}</span>
              </div>
              <div class="resv-alert-item resv-alert-total">
                <span class="resv-alert-label">${window.translations?.total_label || "Total"}</span>
                <b>${totalText}</b>
              </div>
            </div>

            <div class="resv-alert-mail-glass">
              <span class="resv-alert-mail-icon">✉</span>
              <span>${window.translations?.confirmation_email || "You will receive a confirmation by email."}</span>
            </div>
          </div>
        `;

        if (window.alertify) {
          const alert = alertify.alert("", successMsg, function () {
            try {
              localStorage.removeItem("viajero_resv_filters_v1");
            } catch (e) {
              console.warn("Could not clear localStorage:", e);
            }
            try {
              sessionStorage.clear();
            } catch (e) {
              console.warn("Could not clear sessionStorage:", e);
            }
            try {
              window.location.href = "/";
            } catch (e) {
              window.location.href = "/";
            }
          });

          alert.set("labels", { ok: window.translations?.go_to_homepage || "Go to homepage" });
          alert.set("closable", true);
          alert.set("movable", false);
          alert.set("resizable", false);
          alert.set("pinnable", false);
          alert.set("transition", "zoom");

          alert.set("onshow", function () {
            this.elements.dialog.classList.add("resv-alertify-success");
          });

          alert.set("onclose", function () {
            this.elements.dialog.classList.remove("resv-alertify-success");
          });

        } else {
          alert(window.translations?.reservation_success_fallback || "Reservation registered successfully. Check your confirmation email.");

          try {
            localStorage.removeItem("viajero_resv_filters_v1");
          } catch (e) {
            console.warn("Could not clear localStorage:", e);
          }
          try {
            sessionStorage.clear();
          } catch (e) {
            console.warn("Could not clear sessionStorage:", e);
          }

          window.location.href = "/";
        }

      } catch (error) {
        console.error("Error in counter payment:", error);
        if (window.alertify) {
          alertify.error(window.translations?.error_occurred || "An error occurred while registering the reservation.");
        } else {
          alert(window.translations?.error_occurred || "An error occurred while registering the reservation.");
        }
      }

    });
  }
});
