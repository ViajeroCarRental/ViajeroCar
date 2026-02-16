/* =======================================
   CONTRATO FINAL — JS estable (firmas + correo)
======================================= */

document.addEventListener("DOMContentLoaded", () => {

  /* =======================================
     1) OBTENER ID DEL CONTRATO (seguro)
  ======================================= */
  const contratoApp =
    document.getElementById("contratoApp") ||
    document.querySelector("[data-id-contrato]");

  const CONTRATO_ID =
    contratoApp?.dataset.idContrato ||
    contratoApp?.dataset.idContratoFinal ||
    contratoApp?.dataset.idContratoId ||
    contratoApp?.dataset.idContrato ||
    contratoApp?.dataset.idContratoValue ||
    contratoApp?.dataset.idContrato;

  // fallback por si usas data-id-contrato (con guion)
  const CONTRATO_ID_2 = contratoApp?.dataset.idContrato || contratoApp?.getAttribute("data-id-contrato");

  const id = CONTRATO_ID || CONTRATO_ID_2 || null;

  console.log("🔎 ID CONTRATO:", id);

  const CSRF = document.querySelector('meta[name="csrf-token"]')?.content || "";

  async function postJSON(url, payload) {
    const resp = await fetch(url, {
      method: "POST",
      headers: {
        "Content-Type": "application/json",
        "Accept": "application/json",
        "X-CSRF-TOKEN": CSRF
      },
      body: JSON.stringify(payload)
    });

    const raw = await resp.text();
    let data = null;
    try { data = JSON.parse(raw); } catch { data = { raw }; }

    if (!resp.ok) {
      console.error("❌ Error HTTP:", resp.status, data);
      throw new Error(data?.msg || data?.message || `Error ${resp.status}`);
    }

    return data;
  }

  /* =======================================
     2) FIRMA CLIENTE
  ======================================= */
  const modalCliente = document.getElementById("modalCliente");
  const btnCliente   = document.getElementById("btnFirmaCliente");
  const canvasC      = document.getElementById("padCliente");
  const clearCliente = document.getElementById("clearCliente");
  const saveCliente  = document.getElementById("saveCliente");

  let padCliente = null;
  if (canvasC && window.SignaturePad) {
    padCliente = new SignaturePad(canvasC, { minWidth: 1, maxWidth: 2 });
  }

  btnCliente?.addEventListener("click", () => {
    if (!modalCliente || !padCliente) return;
    modalCliente.style.display = "flex";
    padCliente.clear();
  });

  clearCliente?.addEventListener("click", () => padCliente?.clear());

  saveCliente?.addEventListener("click", async () => {
    try {
      if (!padCliente) return;
      if (!id) return alert("No se detectó el ID del contrato.");
      if (padCliente.isEmpty()) return alert("Firma vacía");

      saveCliente.disabled = true;
      saveCliente.textContent = "Guardando...";

      await postJSON("/contrato/firma-cliente", {
        id_contrato: id,
        firma: padCliente.toDataURL("image/png")
      });

      alert("✅ Firma del cliente guardada");
      modalCliente.style.display = "none";
      location.reload();
    } catch (e) {
      alert("❌ No se pudo guardar la firma: " + e.message);
    } finally {
      saveCliente.disabled = false;
      saveCliente.textContent = "Guardar";
    }
  });

  /* =======================================
     3) FIRMA ARRENDADOR
  ======================================= */
  const modalArr = document.getElementById("modalArrendador");
  const btnArr   = document.getElementById("btnFirmaArrendador");
  const canvasA  = document.getElementById("padArrendador");
  const clearArr = document.getElementById("clearArr");
  const saveArr  = document.getElementById("saveArr");

  let padArr = null;
  if (canvasA && window.SignaturePad) {
    padArr = new SignaturePad(canvasA, { minWidth: 1, maxWidth: 2 });
  }

  btnArr?.addEventListener("click", () => {
    if (!modalArr || !padArr) return;
    modalArr.style.display = "flex";
    padArr.clear();
  });

  clearArr?.addEventListener("click", () => padArr?.clear());

  saveArr?.addEventListener("click", async () => {
    try {
      if (!padArr) return;
      if (!id) return alert("No se detectó el ID del contrato.");
      if (padArr.isEmpty()) return alert("Realiza la firma");

      saveArr.disabled = true;
      saveArr.textContent = "Guardando...";

      await postJSON("/contrato/firma-arrendador", {
        id_contrato: id,
        firma: padArr.toDataURL("image/png")
      });

      alert("✅ Firma del arrendador guardada");
      modalArr.style.display = "none";
      location.reload();
    } catch (e) {
      alert("❌ No se pudo guardar la firma: " + e.message);
    } finally {
      saveArr.disabled = false;
      saveArr.textContent = "Guardar";
    }
  });

 /* =======================================
   4) MODAL AVISO + ENVIAR CORREO (PDF)
======================================= */
const modalAviso         = document.getElementById("modalAviso");
const btnAbrirModalAviso = document.getElementById("btnAbrirModalAviso");
const cancelarAviso      = document.getElementById("cancelarAviso");
const confirmarAviso     = document.getElementById("confirmarAviso");

// Nuevo: elementos para la firma del aviso
const canvasAviso        = document.getElementById("padAviso");
const clearAviso         = document.getElementById("clearAviso");
const textoOriginalAviso = document.getElementById("textoOriginal");

let padAviso = null;
if (canvasAviso && window.SignaturePad) {
  padAviso = new SignaturePad(canvasAviso, { minWidth: 1, maxWidth: 2 });
}

// Abrir modal de aviso y limpiar firma
btnAbrirModalAviso?.addEventListener("click", () => {
  if (!modalAviso || !padAviso) return;
  modalAviso.style.display = "flex";
  padAviso.clear();
});

// Cerrar modal de aviso
cancelarAviso?.addEventListener("click", () => {
  if (!modalAviso) return;
  modalAviso.style.display = "none";
});

// Limpiar la firma del aviso
clearAviso?.addEventListener("click", () => {
  padAviso?.clear();
});

// Confirmar aviso: validar firma + enviar correo
confirmarAviso?.addEventListener("click", async () => {
  try {
    if (!id) {
      if (window.alertify) {
        alertify.error("No se detectó el ID del contrato.");
      } else {
        alert("No se detectó el ID del contrato.");
      }
      return;
    }

    if (!padAviso) return;

    // Validar que haya firma
    if (padAviso.isEmpty()) {
      if (window.alertify) {
        alertify.warning("Por favor realiza la firma para confirmar el aviso.");
      } else {
        alert("Por favor realiza la firma para confirmar el aviso.");
      }
      return;
    }

    // Texto del aviso (ya viene con el nombre completo desde Blade)
    const avisoTexto = textoOriginalAviso?.innerText?.trim() || "";

    confirmarAviso.disabled = true;
    confirmarAviso.textContent = "Enviando...";

    if (window.alertify) {
      alertify.message("Guardando firma del aviso y enviando contrato...");
    }

    const data = await postJSON(`/contrato/${id}/enviar-correo`, {
      aviso: avisoTexto,
      firma_aviso: padAviso.toDataURL("image/png")
    });

    if (window.alertify) {
      if (data.ok) {
        alertify.success(data.msg || "Contrato enviado correctamente.");
      } else {
        alertify.error(data.msg || "No se pudo enviar el contrato.");
      }
    } else {
      alert(data.msg || "Contrato enviado correctamente.");
    }

    modalAviso.style.display = "none";
  } catch (e) {
    console.error(e);
    if (window.alertify) {
      alertify.error("Error al enviar correo: " + (e.message || "Error desconocido"));
    } else {
      alert("❌ Error al enviar correo: " + e.message + "\nRevisa storage/logs/laravel.log");
    }
  } finally {
    confirmarAviso.disabled = false;
    confirmarAviso.textContent = "Firmar y Enviar";
  }
});



  /* =======================================
     5) IMPRIMIR (solo navegador)
  ======================================= */
  const btnPDF = document.querySelector(".btn-pdf");
  btnPDF?.addEventListener("click", () => window.print());

});
