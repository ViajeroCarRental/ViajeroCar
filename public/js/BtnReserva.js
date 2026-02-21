// ============================================================
// === BOTÓN RESERVAR: Muestra modal de pago y ejecuta flujo ===
// ============================================================

document.addEventListener("DOMContentLoaded", () => {
  const btnReservar = document.getElementById("btnReservar");
  const modalMetodoPago = document.getElementById("modalMetodoPago");
  const cerrarModal = document.getElementById("cerrarModalMetodo");
  const btnPagoMostrador = document.getElementById("btnPagoMostrador");
  const btnPagoLinea = document.getElementById("btnPagoLinea");

  // =============================
  // 🪟 Abrir / Cerrar Modal
  // =============================
  if (btnReservar && modalMetodoPago) {
    btnReservar.addEventListener("click", () => {
      modalMetodoPago.style.display = "flex";
    });
  }

  if (cerrarModal && modalMetodoPago) {
    cerrarModal.addEventListener("click", () => {
      modalMetodoPago.style.display = "none";
    });
  }

  // ============================================================
  // === OPCIÓN: PAGO EN MOSTRADOR -> Insertar reserva + PDF ====
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
        modalMetodoPago.style.display = "none";
        
        const msg = "<b>No podemos continuar.</b><br>Por favor completa:<br>• " + faltantes.join("<br>• ");
        if(window.alertify) alertify.error(msg);
        else alert("Faltan datos obligatorios.");

        if (!nombre) nombreInput.focus();
        else if (!email) emailInput.focus();
        else if (!telefono) telefonoInput.focus();
        
        return; 
      }

      modalMetodoPago.style.display = "none";

      try {
        // 🧾 Recolectar datos del formulario
        const form = document.querySelector("#formCotizacion");
        if (!form) {
          alertify.error("No se encontró el formulario de reservación.");
          return;
        }
        
        const pickup_date = document.querySelector("#pickup_date")?.value || "";
        const pickup_time = document.querySelector("#pickup_time")?.value || "";
        const dropoff_date = document.querySelector("#dropoff_date")?.value || "";
        const dropoff_time = document.querySelector("#dropoff_time")?.value || "";

        const urlParams = new URLSearchParams(window.location.search);
        const categoria_id = urlParams.get("categoria_id") || "";
        const pickup_sucursal_id = urlParams.get("pickup_sucursal_id") || "";
        const dropoff_sucursal_id = urlParams.get("dropoff_sucursal_id") || "";

        // Complementos (addons)
        let addons = {};
        try {
          const raw = JSON.parse(sessionStorage.getItem("addons_selection") || "{}");
          Object.values(raw).forEach((it) => {
            if (it.qty > 0) addons[it.id] = it.qty;
          });
        } catch (_) { }

        // Payload
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

        // Endpoint
        const url = "/reservas";

        // Token CSRF
        const token =
          document.querySelector('meta[name="csrf-token"]')?.getAttribute("content") ||
          form.querySelector('input[name="_token"]')?.value ||
          "";

        // Enviar datos
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

        const data = await res.json();
        if (!res.ok || data.ok === false) {
          alertify.error("No se pudo registrar la reservación.");
          return;
        }


        const folio = data.folio?.replace(/^COT/, "RES") || "RES-PENDIENTE";
        const pickup = `${pickup_date} ${pickup_time}`;
        const dropoff = `${dropoff_date} ${dropoff_time}`;

        // 💲 Montos formateados
        const subtotal = data.subtotal || 0;
        const impuestos = data.impuestos || 0;
        const total = data.total || 0;

        const fmt = new Intl.NumberFormat("es-MX", {
          style: "currency",
          currency: "MXN",
        });
        const subtotalFmt = fmt.format(subtotal);
        const impuestosFmt = fmt.format(impuestos);
        const totalFmt = fmt.format(total);

        // =====================================================
        // === GENERAR PDF DEL TICKET CON LOGO ================
        // =====================================================
        async function ensureLibs() {
          if (!window.jspdf) {
            await new Promise((res) => {
              const s = document.createElement("script");
              s.src = "https://cdn.jsdelivr.net/npm/jspdf@2.5.1/dist/jspdf.umd.min.js";
              s.onload = res;
              document.head.appendChild(s);
            });
          }
        }

        await ensureLibs();
        const { jsPDF } = window.jspdf;

        async function loadImageAsBase64(url) {
          const response = await fetch(url);
          const blob = await response.blob();
          return new Promise((resolve) => {
            const reader = new FileReader();
            reader.onloadend = () => resolve(reader.result);
            reader.readAsDataURL(blob);
          });
        }

        const logoUrl = `${window.location.origin}/img/Logo3.jpg`;
        const logoBase64 = await loadImageAsBase64(logoUrl);

        const pdf = new jsPDF({
          orientation: "portrait",
          unit: "mm",
          format: [80, 120],
        });

        const pageWidth = 80;
        const logoWidth = 30;
        const logoHeight = 30;
        const logoX = (pageWidth - logoWidth) / 2;

        pdf.addImage(logoBase64, "JPEG", logoX, 6, logoWidth, logoHeight);
        pdf.setFont("helvetica", "bold");
        pdf.setFontSize(13);
        pdf.text("VIAJERO CAR RENTAL", pageWidth / 2, 42, { align: "center" });
        pdf.setFont("helvetica", "normal");
        pdf.setFontSize(11);
        pdf.text("Ticket de Pago en Mostrador", pageWidth / 2, 48, { align: "center" });

        let y = 58;
        pdf.setFont("helvetica", "normal");
        pdf.setFontSize(9);
        pdf.text(`Folio: ${folio}`, 10, y); y += 6;
        pdf.text(`Cliente: ${nombre || "No especificado"}`, 10, y); y += 6;
        pdf.text(`Tel: ${telefono || "-"}`, 10, y); y += 6;
        pdf.text(`Correo: ${email || "-"}`, 10, y); y += 6;
        pdf.text(`Entrega: ${pickup}`, 10, y); y += 6;
        pdf.text(`Devolución: ${dropoff}`, 10, y); y += 6;
        pdf.text(`Método de pago: MOSTRADOR`, 10, y);
        y += 6;

        // 💰 Desglose de pago
        pdf.setFont("helvetica", "bold");
        pdf.text("---- Detalle de pago ----", pageWidth / 2, y, { align: "center" });
        y += 6;

        pdf.setFont("helvetica", "normal");
        pdf.text(`Subtotal: ${subtotalFmt}`, 10, y); y += 6;
        pdf.text(`Impuestos: ${impuestosFmt}`, 10, y); y += 6;
        pdf.setFont("helvetica", "bold");
        pdf.text(`TOTAL A PAGAR: ${totalFmt}`, 10, y); y += 10;
        pdf.setFont("helvetica", "normal");

        // === PIE DE PÁGINA ===
        pdf.setFontSize(8);
        pdf.text("Gracias por elegir VIAJERO CAR RENTAL", pageWidth / 2, y, { align: "center" });
        y += 6;
        pdf.text("Presente este ticket al recoger su vehículo.", pageWidth / 2, y, { align: "center" });
        y += 6;
        pdf.text(new Date().toLocaleString("es-MX"), pageWidth / 2, y, { align: "center" });

        const fileName = `ticket-${folio}.pdf`;

        // ✅ Confirmación visual antes de descargar el PDF
        alertify.alert(
          "Reservación registrada",
          "✅ Su reservación fue registrada correctamente y se ha env...ar Rental.\n\n📩 Recibirá confirmación por correo electrónico."
        );
        sessionStorage.clear(); // limpia datos temporales

        // pequeña pausa antes de descargar
        setTimeout(() => pdf.save(fileName), 600);


      } catch (error) {
        console.error("Error en pago mostrador:", error);
        alertify.error("Ocurrió un error al generar el ticket con logo.");
      }

    });
  }

  // ============================================================
  // === OPCIÓN: PAGO EN LÍNEA (solo visible por ahora) =========
  // ============================================================
  if (btnPagoLinea) {
    btnPagoLinea.addEventListener("click", () => {
      modalMetodoPago.style.display = "none";
      // 🚧 Aquí conectaremos PayPal real o simulación más adelante
      alertify.message("💳 Próximamente podrás realizar tu pago en línea con PayPal.");
    });
  }

});
