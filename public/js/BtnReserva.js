// ============================================================
// === BOTÓN RESERVAR: Muestra modal de pago y ejecuta flujo ===
// ✅ Versión ajustada: la lógica de "Reservar" por plan
//    (línea / mostrador) ahora la controla la vista Blade.
//    Este archivo se enfoca en:
//      - Pago en mostrador  -> inserta reservación
//      - Mensaje "Próximamente" para pago en línea
// ============================================================

document.addEventListener("DOMContentLoaded", () => {
  const btnPagoMostrador = document.getElementById("btnPagoMostrador");
  const btnPagoLinea     = document.getElementById("btnPagoLinea");
  const modalMetodoPago  = document.getElementById("modalMetodoPago");

  // OJO:
  // 🔹 Ya NO manejamos aquí el click de #btnReservar.
  //     => Esa decisión (abrir modal o ir directo a pago en línea)
  //        se hace en el script inline de la vista según data-plan.
  //
  // 🔹 Aquí solo se atienden las acciones DENTRO del modal:
  //     - PAGO EN MOSTRADOR
  //     - PAGO EN LÍNEA (por ahora: mensaje de "Próximamente")

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

      if (!nombre)   faltantes.push("Nombre completo");
      if (!email)    faltantes.push("Correo electrónico");
      if (!telefono) faltantes.push("Teléfono móvil");
      if (!acepta)   faltantes.push("Aceptar términos y condiciones");

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

        if (hiddenAddons && hiddenAddons.value.trim() !== "") {
          addons = hiddenAddons.value.trim(); // "3:1,8:2"
        } else {
          const fromUrl = urlParams.get("addons");
          if (fromUrl && fromUrl.trim() !== "") {
            addons = fromUrl.trim();
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
        const pickup  = `${pickup_date} ${pickup_time}`;
        const dropoff = `${dropoff_date} ${dropoff_time}`;

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

        const msgExito = `
          ✅ Su reservación fue registrada correctamente.<br><br>
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
          alertify.alert("Reservación registrada", msgExito);
        } else {
          alert("Reservación registrada correctamente. Revisa tu correo de confirmación.");
        }

        // Limpia datos temporales del flujo
        sessionStorage.clear();

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

  // ============================================================
  // === OPCIÓN: PAGO EN LÍNEA (placeholder: "Próximamente") ===
  // ============================================================
  if (btnPagoLinea) {
    btnPagoLinea.addEventListener("click", () => {
      if (modalMetodoPago) modalMetodoPago.style.display = "none";

      if (window.alertify) {
        alertify.message("💳 Próximamente podrás realizar tu pago en línea con PayPal.");
      } else {
        alert("Próximamente podrás realizar tu pago en línea con PayPal.");
      }
    });
  }
});
