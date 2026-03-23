// ============================================================
// === BOTÓN RESERVAR: Muestra modal de pago y ejecuta flujo ===
// ✅ Versión ajustada: la lógica de "Reservar" por plan
//    (línea / mostrador) ahora la controla la vista Blade.
//    Este archivo se enfoca en:
//      - Pago en mostrador  -> inserta reservación
//      - Pago en línea      -> (se maneja en BtnReservaLinea.js)
// ============================================================

document.addEventListener("DOMContentLoaded", () => {
  const btnPagoMostrador = document.getElementById("btnPagoMostrador");
  const modalMetodoPago  = document.getElementById("modalMetodoPago");

    // ============================================================
  // ⚙️ MENOR DE EDAD – CONFIG Y HELPERS SOLO PARA ESTE ARCHIVO
  // ============================================================
  // 👇 Usa el MISMO id_servicio que configuraste en reservaciones.js
  const YOUNG_DRIVER_SERVICE_ID = '5'; // <-- cámbialo por el id real
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
          // Soporte viejo tipo "15"
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

    // dd-mm-YYYY
    let m = s.match(/^(\d{2})-(\d{2})-(\d{4})$/);
    if (m) return new Date(+m[3], +m[2] - 1, +m[1]);

    // YYYY-mm-dd
    m = s.match(/^(\d{4})-(\d{2})-(\d{2})$/);
    if (m) return new Date(+m[1], +m[2] - 1, +m[3]);

    const d = new Date(s);
    return isNaN(d.getTime()) ? new Date() : d;
  }

  // 💡 Esta función recibe el string de addons y regresa el string ajustado
  //    con el cargo de menor de edad si aplica.
  function applyYoungDriverAddonOnString(addonsStr) {
    try {
      if (!YOUNG_DRIVER_SERVICE_ID) return addonsStr || "";

      const dobHidden = document.querySelector("#dob"); // hidden con YYYY-MM-DD
      if (!dobHidden) return addonsStr || "";

      const dobStr = (dobHidden.value || "").trim();
      const age = computeAgeFromDobLocal(dobStr, getPickupDateForAgeLocal());

      const map = parseAddonsStringToMapLocal(addonsStr || "");

      if (age != null && age < YOUNG_DRIVER_MIN_AGE) {
        // Forzamos cantidad 1 del servicio de menor de edad
        map.set(String(YOUNG_DRIVER_SERVICE_ID), 1);
      } else {
        // Mayor de la edad límite o fecha inválida: se quita el cargo auto
        map.delete(String(YOUNG_DRIVER_SERVICE_ID));
      }

      return serializeAddonsMapLocal(map);
    } catch (e) {
      console.warn("No se pudo aplicar la regla de menor de edad en addons:", e);
      return addonsStr || "";
    }
  }
  // OJO:
  // 🔹 Ya NO manejamos aquí el click de #btnReservar.
  //     => Esa decisión (abrir modal o ir directo a pago en línea)
  //        se hace en el script inline de la vista según data-plan.
  //
  // 🔹 Aquí solo se atienden las acciones DENTRO del modal:
  //     - PAGO EN MOSTRADOR
  //     - PAGO EN LÍNEA (cerrar modal; PayPal lo maneja BtnReservaLinea.js)

  // ============================================================
  // === OPCIÓN: PAGO EN MOSTRADOR -> Insertar reserva ==========
  // ============================================================
  if (btnPagoMostrador) {
    btnPagoMostrador.addEventListener("click", async () => {

      const nombreInput   = document.querySelector("#nombreCliente");
      const emailInput    = document.querySelector("#correoCliente");
      const telefonoInput = document.querySelector("#telefonoCliente");
      const checkAcepto   = document.querySelector("#acepto");

      const nombre   = nombreInput?.value?.trim() || "";
      const email    = emailInput?.value?.trim() || "";
      const telefono = telefonoInput?.value?.trim() || "";
      const vuelo    = document.querySelector("#vuelo")?.value?.trim() || "";
      const acepta   = checkAcepto?.checked;

      let faltantes = [];

      if (faltantes.length > 0) {
        if (modalMetodoPago) modalMetodoPago.style.display = "none";

        const msg =
          "<b>No podemos continuar.</b><br>Por favor completa:<br>• " +
          faltantes.join("<br>• ");

        if (window.alertify) {
          alertify.error(msg);
        } else {
          alert("Faltan datos obligatorios.");
        }

        if (!nombre && nombreInput) nombreInput.focus();
        else if (!email && emailInput) emailInput.focus();
        else if (!telefono && telefonoInput) telefonoInput.focus();

        return;
      }

      // Cerramos el modal antes de continuar
      if (modalMetodoPago) modalMetodoPago.style.display = "none";

      try {
        // 🧾 Recolectar datos del formulario
        const form = document.querySelector("#formCotizacion");
        if (!form) {
          if (window.alertify) alertify.error("No se encontró el formulario de reservación.");
          else alert("No se encontró el formulario de reservación.");
          return;
        }

        // =====================================================
        // ==== FECHAS / HORAS ================================
        // =====================================================

        const val = (sel) => document.querySelector(sel)?.value?.trim() || "";

        function toIsoDate(dmy) {
          const s = String(dmy || "").trim();
          const m = s.match(/^(\d{2})-(\d{2})-(\d{4})$/);
          if (m) {
            return `${m[3]}-${m[2]}-${m[1]}`; // YYYY-mm-dd
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

        if (!pickup_time) {
          const h = val("#pickup_h") || "00";
          const m = val("#pickup_m") || "00";
          pickup_time = h.padStart(2, "0") + ":" + m.padStart(2, "0");
        }

        if (!dropoff_time) {
          const h = val("#dropoff_h") || "00";
          const m = val("#dropoff_m") || "00";
          dropoff_time = h.padStart(2, "0") + ":" + m.padStart(2, "0");
        }

        console.log("PAYLOAD FECHAS/HORAS (MOSTRADOR):", {
          pickup_date,
          dropoff_date,
          pickup_time,
          dropoff_time,
        });

        // =====================================================
        // === ID CATEGORÍA / SUCURSALES / ADDONS =============
        // =====================================================
        const categoria_id        = val("#categoria_id") || urlParams.get("categoria_id") || "";
        const pickup_sucursal_id  = urlParams.get("pickup_sucursal_id") || "";
        const dropoff_sucursal_id = urlParams.get("dropoff_sucursal_id") || "";

                const hiddenAddons = document.querySelector("#addonsHidden");
        let addons = "";

        // 1) Base: lo que venga del hidden o de la URL
        if (hiddenAddons && hiddenAddons.value.trim() !== "") {
          addons = hiddenAddons.value.trim(); // "3:1,8:2"
        } else {
          const fromUrl = urlParams.get("addons");
          if (fromUrl && fromUrl.trim() !== "") {
            addons = fromUrl.trim();
          }
        }

        // 2) Aplicar la regla de conductor menor de edad
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

    // 👇 calcular el total
    dropoff_total = km * costokm;

  }
}

        console.log("ADDONS ENVIADOS AL BACKEND (MOSTRADOR):", addons);

        const payload = {
          categoria_id,
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

        const url = "/reservas";

        const token =
          document.querySelector('meta[name="csrf-token"]')?.getAttribute("content") ||
          form.querySelector('input[name="_token"]')?.value ||
          "";

        if (!token) {
          if (window.alertify) {
            alertify.error("No se encontró el token de seguridad. Actualiza la página e inténtalo de nuevo.");
          } else {
            alert("No se encontró el token de seguridad. Actualiza la página e inténtalo de nuevo.");
          }
          console.error("CSRF token no encontrado para pago en mostrador.");
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
          console.error("Error al registrar la reservación (mostrador):", data);
          if (window.alertify) alertify.error("No se pudo registrar la reservación.");
          else alert("No se pudo registrar la reservación.");
          return;
        }

        // ================================
        // === ARMAR MENSAJE DE ÉXITO  ===
        // ================================
        const pickup  = `${pickup_date} `;
        const dropoff = `${dropoff_date} `;

        const subtotal  = data.subtotal || 0;
        const impuestos = data.impuestos || 0;
        const total     = data.total || 0;

        const fmt = new Intl.NumberFormat("es-MX", {
          style: "currency",
          currency: "MXN",
        });

        const subtotalFmtFallback  = fmt.format(subtotal);
        const impuestosFmtFallback = fmt.format(impuestos);
        const totalFmtFallback     = fmt.format(total);

        const baseLabelEl   = document.querySelector("#qBase");
        const extrasLabelEl = document.querySelector("#qExtras");
        const ivaLabelEl    = document.querySelector("#qIva");
        const totalLabelEl  = document.querySelector("#qTotal");

        const baseTxt   = baseLabelEl   ? baseLabelEl.textContent.trim()   : subtotalFmtFallback;
        const extrasTxt = extrasLabelEl ? extrasLabelEl.textContent.trim() : fmt.format(0);
        const ivaTxt    = ivaLabelEl    ? ivaLabelEl.textContent.trim()    : impuestosFmtFallback;
        const totalTxt  = totalLabelEl  ? totalLabelEl.textContent.trim()  : totalFmtFallback;

        const folio = data.folio?.replace(/^COT/, "RES") || "RES-PENDIENTE";

        function formatFechaBonita(fechaISO) {
          try {
            const meses = ["ene", "feb", "mar", "abr", "may", "jun", "jul", "ago", "sep", "oct", "nov", "dic"];

            const f = String(fechaISO || "").trim().match(/^(\d{4})-(\d{2})-(\d{2})$/);
            if (!f) return fechaISO;

            const yyyy = f[1];
            const mm   = parseInt(f[2], 10);
            const dd   = f[3];

            return `${dd}-${meses[mm - 1]}-${yyyy}`;
          } catch (e) {
            return fechaISO;
          }
        }

        const pickupPretty  = formatFechaBonita(pickup_date);
        const dropoffPretty = formatFechaBonita(dropoff_date);

        const msgExito = `
          <div class="resv-alert-success">

            <div class="resv-alert-check-card">
              <div class="resv-alert-check-icon-wrap">
                <span class="resv-alert-check-icon">✓</span>
              </div>
              <div class="resv-alert-check-text">
                Su reservación fue registrada correctamente.
              </div>
            </div>

            <div class="resv-alert-card resv-alert-itinerary">
              <div class="resv-alert-card-title">Itinerario</div>

              <div class="resv-alert-item">
                <span class="resv-alert-label">Folio</span>
                <b>${folio}</b>
              </div>

              <div class="resv-alert-item">
                <span class="resv-alert-label">Entrega</span>
                <b>${pickupPretty}</b>
              </div>

              <div class="resv-alert-item">
                <span class="resv-alert-label">Devolución</span>
                <b>${dropoffPretty}</b>
              </div>
            </div>

            <div class="resv-alert-card">
              <div class="resv-alert-card-title">Resumen de pago</div>

              <div class="resv-alert-item">
                <span class="resv-alert-label">Tarifa base</span>
                <span>${baseTxt}</span>
              </div>

              <div class="resv-alert-item">
                <span class="resv-alert-label">Opciones de renta</span>
                <span>${extrasTxt}</span>
              </div>

              <div class="resv-alert-item">
                <span class="resv-alert-label">Cargos e IVA (16%)</span>
                <span>${ivaTxt}</span>
              </div>

              <div class="resv-alert-item resv-alert-total">
                <span class="resv-alert-label">Total</span>
                <b>${totalTxt}</b>
              </div>
            </div>

            <div class="resv-alert-mail-glass">
              <span class="resv-alert-mail-icon">✉</span>
              <span>Recibirá confirmación por correo electrónico.</span>
            </div>

          </div>
        `;

        if (window.alertify) {
          const alerta = alertify.alert("", msgExito, function () {
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
              window.location.href = "/";
            } catch (e) {
              window.location.href = "/";
            }
          });

          alerta.set("labels", { ok: "Ir al inicio" });
          alerta.set("closable", true);
          alerta.set("movable", false);
          alerta.set("resizable", false);
          alerta.set("pinnable", false);
          alerta.set("transition", "zoom");

          alerta.set("onshow", function () {
            this.elements.dialog.classList.add("resv-alertify-success");
          });

          alerta.set("onclose", function () {
            this.elements.dialog.classList.remove("resv-alertify-success");
          });

        } else {
          // Fallback sin alertify: alert nativa + reset inmediato
          alert("Reservación registrada correctamente. Revisa tu correo de confirmación.");

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

          window.location.href = "/";
        }

      } catch (error) {
        console.error("Error en pago mostrador:", error);
        if (window.alertify) {
          alertify.error("Ocurrió un error al registrar la reservación.");
        } else {
          alert("Ocurrió un error al registrar la reservación.");
        }
      }

    });
  }


});
