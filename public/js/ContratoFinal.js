/* =======================================
   CONTRATO FINAL
   Acordeón + revisiones + firmas + correo
======================================= */

document.addEventListener("DOMContentLoaded", () => {
  const contratoApp =
    document.getElementById("contratoApp") ||
    document.querySelector("[data-id-contrato]");

  const id =
    contratoApp?.dataset.idContrato ||
    contratoApp?.getAttribute("data-id-contrato") ||
    null;

  const CSRF =
    document.querySelector('meta[name="csrf-token"]')?.content || "";

  async function postJSON(url, payload) {
    const resp = await fetch(url, {
      method: "POST",
      headers: {
        "Content-Type": "application/json",
        Accept: "application/json",
        "X-CSRF-TOKEN": CSRF,
      },
      body: JSON.stringify(payload),
    });

    const raw = await resp.text();
    let data;

    try {
      data = JSON.parse(raw);
    } catch {
      data = { raw };
    }

    if (!resp.ok) {
      console.error("Error HTTP:", resp.status, data);

      throw new Error(
        data?.msg ||
        data?.message ||
        `Error ${resp.status}`
      );
    }

    return data;
  }

  function notificar(tipo, mensaje) {
    if (
      window.alertify &&
      typeof alertify[tipo] === "function"
    ) {
      alertify[tipo](mensaje);
      return;
    }

    if (tipo === "error" || tipo === "warning") {
      alert(mensaje);
    }
  }

  /* =======================================
     1) ACORDEÓN DE DOCUMENTOS
  ======================================= */

  const secciones = Array.from(
    document.querySelectorAll(".documento-acordeon")
  );

  function abrirSeccion(seccion) {
    secciones.forEach((item) => {
      const encabezado = item.querySelector(
        ".documento-encabezado"
      );

      const debeAbrirse = item === seccion;

      item.classList.toggle("abierto", debeAbrirse);

      encabezado?.setAttribute(
        "aria-expanded",
        debeAbrirse ? "true" : "false"
      );
    });

    if (seccion) {
      setTimeout(() => ajustarFrame(seccion), 80);
    }
  }

  secciones.forEach((seccion) => {
    const encabezado = seccion.querySelector(
      ".documento-encabezado"
    );

    encabezado?.addEventListener("click", () => {
      const yaEstaAbierta =
        seccion.classList.contains("abierto");

      if (yaEstaAbierta) {
        seccion.classList.remove("abierto");
        encabezado.setAttribute("aria-expanded", "false");
      } else {
        abrirSeccion(seccion);
      }
    });
  });

  /* =======================================
     2) ALTURA DE VISTAS INTERNAS
  ======================================= */

  function ajustarFrame(contenedor) {
    const frames = contenedor
      ? contenedor.querySelectorAll(".documento-frame")
      : document.querySelectorAll(".documento-frame");

    frames.forEach((frame) => {
      try {
        const documento =
          frame.contentDocument ||
          frame.contentWindow?.document;

        if (!documento) return;

        const alto = Math.max(
          documento.body?.scrollHeight || 0,
          documento.documentElement?.scrollHeight || 0,
          850
        );

        frame.style.height = `${alto + 30}px`;
      } catch (error) {
        console.warn(
          "No se pudo ajustar la altura de la vista interna.",
          error
        );
      }
    });
  }

  document
    .querySelectorAll(".documento-frame")
    .forEach((frame) => {
      frame.addEventListener("load", () => {
        ajustarFrame(
          frame.closest(".documento-acordeon")
        );
      });
    });

  /* =======================================
     3) GUARDAR REVISIONES
  ======================================= */

  const btnCorreo = document.getElementById(
    "btnAbrirModalAviso"
  );

  const contadorRevisiones = document.getElementById(
    "contadorRevisiones"
  );

  const textosRevisados = {
    contrato: "Contrato revisado",
    clausulas: "Cláusulas revisadas",
    checklist: "Checklist revisado",
    conductor_adicional:
      "Conductor adicional revisado",
  };

  function actualizarProgreso() {
    const total = secciones.length;

    const revisadas = secciones.filter(
      (seccion) => seccion.dataset.revisado === "1"
    ).length;

    if (contadorRevisiones) {
      contadorRevisiones.textContent =
        `${revisadas} de ${total} documentos revisados`;
    }

    if (btnCorreo) {
      btnCorreo.disabled = revisadas !== total;
    }

    return { total, revisadas };
  }

  function marcarVisualmente(
    seccion,
    nombreSeccion
  ) {
    seccion.dataset.revisado = "1";
    seccion.classList.add("revisado");

    const estadoTexto = seccion.querySelector(
      ".documento-titulo small"
    );

    const estadoIcono = seccion.querySelector(
      ".documento-estado i"
    );

    const boton = seccion.querySelector(
      "[data-marcar-revision]"
    );

    if (estadoTexto) {
      estadoTexto.textContent =
        nombreSeccion === "clausulas"
          ? "Revisadas"
          : "Revisado";
    }

    if (estadoIcono) {
      estadoIcono.classList.remove("fa-circle");
      estadoIcono.classList.add("fa-circle-check");
    }

    if (boton) {
      boton.disabled = true;

      const textoBoton =
        boton.querySelector("span");

      if (textoBoton) {
        textoBoton.textContent =
          textosRevisados[nombreSeccion] ||
          "Documento revisado";
      }
    }
  }

  function abrirSiguienteSeccion(seccionActual) {
    const indice = secciones.indexOf(seccionActual);
    const siguiente = secciones[indice + 1];

    if (siguiente) {
      abrirSeccion(siguiente);

      setTimeout(() => {
        siguiente.scrollIntoView({
          behavior: "smooth",
          block: "start",
        });
      }, 120);
    }
  }

  document
    .querySelectorAll("[data-marcar-revision]")
    .forEach((boton) => {
      boton.addEventListener("click", async () => {
        const nombreSeccion =
          boton.dataset.marcarRevision;

        const seccion = boton.closest(
          ".documento-acordeon"
        );

        if (
          !id ||
          !nombreSeccion ||
          !seccion ||
          boton.disabled
        ) {
          return;
        }

        const textoOriginal =
          boton.querySelector("span")?.textContent || "";

        try {
          boton.disabled = true;

          const textoBoton =
            boton.querySelector("span");

          if (textoBoton) {
            textoBoton.textContent =
              "Guardando revisión...";
          }

          const data = await postJSON(
            `/contrato/${id}/revision`,
            {
              seccion: nombreSeccion,
            }
          );

          if (!data.ok) {
            throw new Error(
              data.msg ||
              "No se pudo guardar la revisión."
            );
          }

          marcarVisualmente(
            seccion,
            nombreSeccion
          );

          const progreso = actualizarProgreso();

          notificar(
            "success",
            data.msg ||
            "Documento marcado como revisado."
          );

          if (
            progreso.revisadas === progreso.total
          ) {
            // Cerrar el último apartado
            seccion.classList.remove("abierto");

            const encabezadoActual = seccion.querySelector(
              ".documento-encabezado"
            );

            encabezadoActual?.setAttribute(
              "aria-expanded",
              "false"
            );

            notificar(
              "success",
              "Todos los documentos fueron revisados. Ya puedes enviar el correo."
            );

            // Subir automáticamente hasta el botón de correo
            setTimeout(() => {
              document
                .querySelector(".acciones-contrato")
                ?.scrollIntoView({
                  behavior: "smooth",
                  block: "start",
                });
            }, 200);
          } else {
            abrirSiguienteSeccion(seccion);
          }
              
        } catch (error) {
          console.error(error);

          boton.disabled = false;

          const textoBoton =
            boton.querySelector("span");

          if (textoBoton) {
            textoBoton.textContent =
              textoOriginal;
          }

          notificar(
            "error",
            `No se pudo guardar la revisión: ${error.message}`
          );
        }
      });
    });

  actualizarProgreso();
    /* =======================================
     4) FIRMA DEL ARRENDADOR
  ======================================= */

  const modalArr =
    document.getElementById("modalArrendador");

  const btnArr =
    document.getElementById("btnFirmaArrendador");

  const canvasA =
    document.getElementById("padArrendador");

  const clearArr =
    document.getElementById("clearArr");

  const saveArr =
    document.getElementById("saveArr");

  let padArr = null;

  if (canvasA && window.SignaturePad) {
    padArr = new SignaturePad(canvasA, {
      minWidth: 1,
      maxWidth: 2,
    });
  }

  btnArr?.addEventListener("click", () => {
    if (!modalArr || !padArr) return;

    modalArr.style.display = "flex";
    padArr.clear();
  });

  clearArr?.addEventListener("click", () => {
    padArr?.clear();
  });

  saveArr?.addEventListener("click", async () => {
    try {
      if (!padArr) return;

      if (!id) {
        alert("No se detectó el ID del contrato.");
        return;
      }

      if (padArr.isEmpty()) {
        alert("Realiza la firma.");
        return;
      }

      saveArr.disabled = true;
      saveArr.textContent = "Guardando...";

      await postJSON(
        "/contrato/firma-arrendador",
        {
          id_contrato: id,
          firma: padArr.toDataURL("image/png"),
        }
      );

      notificar(
        "success",
        "Firma del arrendador guardada."
      );

      modalArr.style.display = "none";
      location.reload();
    } catch (error) {
      notificar(
        "error",
        `No se pudo guardar la firma: ${error.message}`
      );
    } finally {
      saveArr.disabled = false;
      saveArr.textContent = "Guardar firma";
    }
  });

  /* =======================================
     5) AVISO LEGAL Y ENVÍO DE CORREO
  ======================================= */

  const modalAviso =
    document.getElementById("modalAviso");

  const cancelarAviso =
    document.getElementById("cancelarAviso");

  const confirmarAviso =
    document.getElementById("confirmarAviso");

  const canvasAviso =
    document.getElementById("padAviso");

  const clearAviso =
    document.getElementById("clearAviso");

  const textoOriginalAviso =
    document.getElementById("textoOriginal");

  let padAviso = null;

  if (canvasAviso && window.SignaturePad) {
    padAviso = new SignaturePad(canvasAviso, {
      minWidth: 1,
      maxWidth: 2,
    });
  }

  btnCorreo?.addEventListener("click", () => {
    if (
      btnCorreo.disabled ||
      !modalAviso
    ) {
      return;
    }

    modalAviso.style.display = "flex";
    padAviso?.clear();
  });

  cancelarAviso?.addEventListener("click", () => {
    if (modalAviso) {
      modalAviso.style.display = "none";
    }
  });

  clearAviso?.addEventListener("click", () => {
    padAviso?.clear();
  });

  confirmarAviso?.addEventListener(
    "click",
    async () => {
      try {
        if (!id) {
          notificar(
            "error",
            "No se detectó el ID del contrato."
          );

          return;
        }

        if (!padAviso) return;

        if (padAviso.isEmpty()) {
          notificar(
            "warning",
            "Por favor realiza la firma para confirmar el aviso."
          );

          return;
        }

        const avisoTexto =
          textoOriginalAviso?.innerText?.trim() || "";

        confirmarAviso.disabled = true;
        confirmarAviso.textContent = "Enviando...";

        notificar(
          "message",
          "Guardando firma del aviso y enviando contrato..."
        );

        const data = await postJSON(
          `/contrato/${id}/enviar-correo`,
          {
            aviso: avisoTexto,
            firma_aviso:
              padAviso.toDataURL("image/png"),
          }
        );

        if (!data.ok) {
          throw new Error(
            data.msg ||
            "No se pudo enviar el contrato."
          );
        }

        notificar(
          "success",
          data.msg ||
          "Contrato enviado correctamente."
        );

        modalAviso.style.display = "none";
      } catch (error) {
        console.error(error);

        notificar(
          "error",
          `Error al enviar correo: ${error.message}`
        );
      } finally {
        confirmarAviso.disabled = false;
        confirmarAviso.textContent =
          "Firmar y Enviar";
      }
    }
  );

  /* =======================================
     6) IMPRIMIR
  ======================================= */

  const btnPDF =
    document.querySelector(".btn-pdf");

  btnPDF?.addEventListener("click", () => {
    window.print();
  });
});