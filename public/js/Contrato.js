/* ==========================================================
   📑 Navegación entre pasos — Contrato
   ✅ Versión final con Paso 3 (seguros incluidos)
   Autor: Ingeniero Bernal
========================================================== */

document.addEventListener("DOMContentLoaded", () => {
  console.log("✅ DOM listo, iniciando navegación de pasos...");
  /* ==========================================================
     🧾 VARIABLES GLOBALES DEL CONTRATO
  ========================================================== */
  const contratoApp = document.getElementById("contratoApp");
  const ID_CONTRATO = contratoApp?.dataset.idContrato || null;
  const NUM_CONTRATO = contratoApp?.dataset.numero || "";
  const ID_RESERVACION = contratoApp?.dataset.idReservacion || null;

  console.log("📄 Contrato ID:", ID_CONTRATO, "| Reservación ID:", ID_RESERVACION, "| No. Contrato:", NUM_CONTRATO);

  // Utilidades rápidas
  const $ = (s) => document.querySelector(s);
  const $$ = (s) => Array.from(document.querySelectorAll(s));

  /**
   * 🔁 Muestra el paso indicado y oculta los demás
   */
  const showStep = (n) => {
  $$(".step").forEach((el) => {
    const isActive = Number(el.dataset.step) === n;
    el.classList.toggle("active", isActive);
  });

  // 🧠 Guardar el paso por reservación específica
  if (ID_RESERVACION) {
    localStorage.setItem(`contratoPasoActual_${ID_RESERVACION}`, n);
  }
};


  /* ==========================================================
     🧾 PASO 1: Capturar y guardar datos de la reservación
  ========================================================== */
  function guardarDatosPaso1() {
    const datos = {
      codigo: $("#codigo")?.textContent.trim() || "",
      titular: $(".resumen-header p")?.textContent.trim() || "",
      sucursalEntrega: $(".bloque.entrega .lugar")?.textContent.trim() || "",
      sucursalDevolucion: $(".bloque.devolucion .lugar")?.textContent.trim() || "",
      fechaEntrega: $(".bloque.entrega .fecha")?.textContent.trim() || "",
      fechaDevolucion: $(".bloque.devolucion .fecha")?.textContent.trim() || "",
      horaEntrega: $(".bloque.entrega .hora")?.textContent.trim() || "",
      horaDevolucion: $(".bloque.devolucion .hora")?.textContent.trim() || "",
      telefono: $(".kv:nth-child(1) div:last-child")?.textContent.trim() || "",
      email: $(".kv:nth-child(2) div:last-child")?.textContent.trim() || "",
      duracion: $(".kv:nth-child(3) div:last-child")?.textContent.trim() || "",
      total: $(".kv.total div:last-child")?.textContent.trim() || ""
    };
    sessionStorage.setItem("contratoPaso1", JSON.stringify(datos));
    console.log("📦 Datos del Paso 1 guardados:", datos);
  }

  /* ==========================================================
     🧹 Detección y actualización automática al cambiar reservación
  ========================================================== */
  const codigoActual = $("#codigo")?.textContent.trim();
  const datosGuardados = JSON.parse(sessionStorage.getItem("contratoPaso1") || "{}");

  if (!datosGuardados.codigo || datosGuardados.codigo !== codigoActual) {
    console.log("🧽 Nueva reservación detectada, limpiando sessionStorage...");
    sessionStorage.clear();
    const aviso = document.createElement("div");
    aviso.textContent = "🔄 Datos de reservación actualizados";
    aviso.style.cssText = `
      position: fixed; top: 20px; right: 20px;
      background: #10b981; color: white;
      padding: 10px 16px; border-radius: 8px;
      box-shadow: 0 2px 8px rgba(0,0,0,0.2);
      z-index: 9999; font-weight: 600; transition: opacity 0.5s;
    `;
    document.body.appendChild(aviso);
    setTimeout(() => (aviso.style.opacity = "0"), 2000);
    setTimeout(() => aviso.remove(), 2500);
  }
  setTimeout(() => guardarDatosPaso1(), 300);

  /* ==========================================================
     ⚙️ PASO 2: Manejo de servicios adicionales
  ========================================================== */
  const idReservacion = ID_RESERVACION;
  const serviciosGrid = $("#serviciosGrid");
  const totalServicios = $("#total_servicios");

  if (serviciosGrid) {
    console.log("🧩 Iniciando gestión de servicios adicionales...");

    const actualizarTotal = () => {
      let total = 0;
      $$(".card-servicio").forEach((card) => {
        const precio = parseFloat(card.dataset.precio || 0);
        const cantidad = parseInt(card.querySelector(".cantidad").textContent || 0);
        total += precio * cantidad;
      });
      totalServicios.textContent = `$${total.toFixed(2)} MXN`;
    };

    serviciosGrid.addEventListener("click", async (e) => {
      const btn = e.target;
      if (!btn.classList.contains("mas") && !btn.classList.contains("menos")) return;

      const card = btn.closest(".card-servicio");
      const cantidadEl = card.querySelector(".cantidad");
      let cantidad = parseInt(cantidadEl.textContent);
      const precio = parseFloat(card.dataset.precio);
      const idServicio = card.dataset.id;

      if (btn.classList.contains("mas")) cantidad++;
      else if (btn.classList.contains("menos") && cantidad > 0) cantidad--;

      cantidadEl.textContent = cantidad;
      actualizarTotal();

      try {
        const resp = await fetch(`/admin/contrato/servicios`, {
          method: "POST",
          headers: {
            "Content-Type": "application/json",
            "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').content,
          },
          body: JSON.stringify({
            id_reservacion: idReservacion,
            id_servicio: idServicio,
            cantidad: cantidad,
            precio_unitario: precio,
          }),
        });
        const data = await resp.json();
        console.log("📡 Respuesta servidor:", data);
      } catch (err) {
        console.error("❌ Error al actualizar servicio:", err);
      }
    });

    actualizarTotal();
  }

  /* ==========================================================
     🛡️ PASO 3: Manejo de seguros (paquetes)
  ========================================================== */
  const packGrid = $("#packGrid");
  const totalSeguros = $("#total_seguros");
  const btnContinuarPaso3 = $("#go4");

  if (packGrid) {
    console.log("🛡️ Iniciando gestión de seguros...");

    const switches = $$(".switch");

    // Actualiza visualmente los switches y el total
    const actualizarEstadoVisual = (activoId, precio) => {
      switches.forEach((sw) => {
        const isActive = Number(sw.dataset.id) === Number(activoId);
        sw.classList.toggle("on", isActive);
      });
      totalSeguros.textContent = `$${Number(precio || 0).toFixed(2)} MXN`;
      btnContinuarPaso3.disabled = !activoId;
    };

    // Detecta click sobre un switch
    packGrid.addEventListener("click", async (e) => {
      const sw = e.target.closest(".switch");
      if (!sw) return;

      const idPaquete = sw.dataset.id;
      const activo = sw.classList.contains("on");
      const card = sw.closest(".card");
      const precio = parseFloat(card.dataset.precio || 0);

      try {
        // Si estaba activo → eliminar
        if (activo) {
          console.log("🗑️ Eliminando seguro activo...");
          const resp = await fetch(`/admin/contrato/seguros`, {
            method: "DELETE",
            headers: {
              "Content-Type": "application/json",
              "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').content,
            },
            body: JSON.stringify({
              id_reservacion: idReservacion,
            }),
          });
          const data = await resp.json();
          console.log("🗑️ Eliminado:", data);
          actualizarEstadoVisual(null, 0);
          return;
        }

        // Si no estaba activo → activar este y actualizar/insertar
        console.log("🟢 Activando nuevo seguro:", idPaquete);
        const resp = await fetch(`/admin/contrato/seguros`, {
          method: "POST",
          headers: {
            "Content-Type": "application/json",
            "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').content,
          },
          body: JSON.stringify({
            id_reservacion: idReservacion,
            id_paquete: idPaquete,
            precio_por_dia: precio,
          }),
        });
        const data = await resp.json();
        console.log("📡 Respuesta servidor:", data);

        actualizarEstadoVisual(idPaquete, precio);
      } catch (err) {
        console.error("❌ Error al actualizar seguro:", err);
      }
    });
  }
    /* ==========================================================
     💰 PASO 4: Manejo de cargos adicionales
  ========================================================== */
  const cargosGrid = document.querySelector("#cargosGrid");
  const totalCargos = document.querySelector("#total_cargos");

  if (cargosGrid && ID_CONTRATO) {
    console.log("💼 Iniciando gestión de cargos adicionales...");

    const calcularTotal = () => {
      let total = 0;
      document.querySelectorAll(".cargo-item .switch.on").forEach((sw) => {
        const card = sw.closest(".cargo-item");
        total += parseFloat(card.dataset.monto || 0);
      });
      totalCargos.textContent = `$${total.toFixed(2)} MXN`;
    };

    cargosGrid.addEventListener("click", async (e) => {
      const sw = e.target.closest(".switch");
      if (!sw) return;

      const idConcepto = sw.dataset.id;
      const activo = sw.classList.contains("on");
      const card = sw.closest(".cargo-item");
      const nombre = card.dataset.nombre;
      const monto = parseFloat(card.dataset.monto || 0);

      try {
        console.log(activo ? "🗑️ Eliminando cargo..." : "🟢 Activando cargo...");
        const resp = await fetch(`/admin/contrato/cargos`, {
          method: "POST",
          headers: {
            "Content-Type": "application/json",
            "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').content,
          },
          body: JSON.stringify({
            id_contrato: ID_CONTRATO,
            id_concepto: idConcepto,
          }),
        });

        const data = await resp.json();
        console.log("📡 Respuesta servidor:", data);

        if (data.status === "inserted") {
          sw.classList.add("on");
          sw.setAttribute("aria-checked", "true");
        } else if (data.status === "deleted") {
          sw.classList.remove("on");
          sw.setAttribute("aria-checked", "false");
        }

        calcularTotal();
      } catch (err) {
        console.error("❌ Error al actualizar cargo:", err);
      }
    });

    calcularTotal();
  }

  /* ==========================================================
   🧾 PASO 5: Subida de documentación
========================================================== */
const formDoc = document.querySelector("#formDocumentacion");
if (formDoc && ID_CONTRATO) {
  console.log("🧾 Iniciando manejo de documentación (Paso 5)...");

  formDoc.addEventListener("submit", async (e) => {
    e.preventDefault();

    const formData = new FormData(formDoc);
    formData.append("id_contrato", ID_CONTRATO);

    try {
      console.log("📤 Enviando documentación al servidor...");

      const resp = await fetch("/contrato/guardar-documentacion", {

        method: "POST",
        headers: {
          "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').content,
        },
        body: formData,
      });

      const data = await resp.json();
      console.log("📡 Respuesta servidor:", data);

      // 🧩 Mostrar estado visual de licencia
      const alerta = document.getElementById("alertaLicencia");
      const confirmacion = document.getElementById("confirmacionLicencia");

      if (data.warning) {
        alerta.style.display = "block";
        confirmacion.style.display = "none";
      } else {
        alerta.style.display = "none";
        confirmacion.style.display = "block";
      }

      // ✅ Notificación flotante
      const aviso = document.createElement("div");
      aviso.textContent = data.msg || "Documentación enviada correctamente.";
      aviso.style.cssText = `
        position: fixed; bottom: 20px; right: 20px;
        background: ${data.warning ? "#facc15" : "#16a34a"};
        color: #fff; padding: 12px 18px;
        border-radius: 10px; font-weight: 700;
        box-shadow: 0 3px 10px rgba(0,0,0,0.2);
        z-index: 9999; transition: opacity .4s;
      `;
      document.body.appendChild(aviso);
      setTimeout(() => (aviso.style.opacity = "0"), 2500);
      setTimeout(() => aviso.remove(), 3000);

      // ⚠️ Si licencia vencida → detener avance
      if (data.warning) return;

      // 🚀 Si éxito → avanzar al siguiente paso o formulario adicional
if (data.success) {
  console.log("✅ Documentación guardada, verificando conductores adicionales...");

  const adicionales = parseInt(formDoc.dataset.adicionales || "0");
  let actual = parseInt(formDoc.dataset.actual || "0");
  const conductores = JSON.parse(formDoc.dataset.conductores || "[]");

  console.log("📊 Detectados", adicionales, "conductores adicionales:", conductores);

  // Si aún hay conductores por procesar
  if (actual < adicionales && conductores.length > 0) {
    // Avanzar al siguiente
    actual++;
    formDoc.dataset.actual = actual;

    // Obtener datos del siguiente conductor real
    const siguiente = conductores[actual - 1]; // índice empieza en 0
    const idReal = siguiente?.id_conductor || null;
    const nombre = siguiente?.nombres || `Conductor adicional #${actual}`;
    const apellidos = siguiente?.apellidos || "";

    // Actualizar UI
    document.querySelector("#tituloPersona").textContent = `Documentación de ${nombre} ${apellidos}`.trim();
    document.querySelector("#id_conductor").value = idReal || "";

    // Resetear el formulario visual
    formDoc.reset();

    // Limpiar vistas previas
    document.querySelectorAll(".preview").forEach((div) => (div.innerHTML = ""));

    alert(`🧍‍♂️ Captura ahora la información del ${nombre}`);
  } else {
    // Si ya no hay más adicionales → Paso 6
    console.log("🎉 Todos los formularios completados, pasando al Paso 6...");
    showStep(6);
  }
}

    } catch (err) {
      console.error("❌ Error al enviar documentación:", err);
      alert("Error al enviar los documentos. Intenta nuevamente.");
    }
  });
}
/* ==========================================================
   📸 Vista previa instantánea de archivos (INE / Licencia)
========================================================== */
document.querySelectorAll('.uploader input[type="file"]').forEach((input) => {
  input.addEventListener('change', (e) => {
    const file = e.target.files[0];
    const contenedor = e.target.closest('.uploader');
    const previewId = contenedor.getAttribute('data-name');
    const previewDiv = document.getElementById(`prev-${previewId}`);

    if (!file || !previewDiv) return;

    // Limpia la vista previa anterior
    previewDiv.innerHTML = "";

    const reader = new FileReader();
    reader.onload = (ev) => {
      const thumb = document.createElement("div");
      thumb.classList.add("thumb");
      thumb.innerHTML = `
        <img src="${ev.target.result}" alt="Vista previa">
        <button type="button" class="rm" title="Quitar">×</button>
      `;
      previewDiv.appendChild(thumb);

      // Botón para eliminar la imagen seleccionada
      thumb.querySelector(".rm").addEventListener("click", () => {
        e.target.value = "";
        thumb.remove();
      });
    };

    reader.readAsDataURL(file);
  });
});




  /* ==========================================================
     🚀 Navegación entre pasos
  ========================================================== */
  $("#go2")?.addEventListener("click", () => {
    console.log("➡️ Paso 2");
    guardarDatosPaso1();
    showStep(2);
  });
  $("#go3")?.addEventListener("click", () => {
    console.log("➡️ Paso 3");
    showStep(3);
  });
  $("#go4")?.addEventListener("click", () => {
    console.log("➡️ Paso 4");
    // Verificación: no dejar pasar sin seguro activo
    const seguroActivo = document.querySelector(".switch.on");
    if (!seguroActivo) {
      alert("⚠️ Debes seleccionar al menos un seguro antes de continuar.");
      return;
    }
    showStep(4);
  });
  $("#go5")?.addEventListener("click", async () => {
  console.log("➡️ Paso 5");

  try {
    console.log("🔄 Consultando conductores adicionales reales desde el backend...");
    const resp = await fetch(`/admin/contrato/${ID_CONTRATO}/conductores`);
    const data = await resp.json();

    if (!resp.ok) throw new Error(data.error || "Error al obtener conductores");

    const formDoc = document.querySelector("#formDocumentacion");
    formDoc.dataset.adicionales = data.length || 0;
    formDoc.dataset.conductores = JSON.stringify(data);
    formDoc.dataset.actual = 0; // reiniciar por si acaso

    console.log(`✅ Datos actualizados: ${data.length} conductores detectados.`, data);
  } catch (err) {
    console.warn("⚠️ No se pudo actualizar la lista de conductores:", err);
  }

  showStep(5);
});

  $("#go6")?.addEventListener("click", () => {
    console.log("➡️ Paso 6");
    showStep(6);
  });
  $("#back1")?.addEventListener("click", () => {
    console.log("⬅️ Paso 1");
    showStep(1);
  });
  $("#back2")?.addEventListener("click", () => {
    console.log("⬅️ Paso 2");
    showStep(2);
  });
  $("#back3")?.addEventListener("click", () => {
    console.log("⬅️ Paso 3");
    showStep(3);
  });
  $("#back4")?.addEventListener("click", () => {
    console.log("⬅️ Paso 4");
    showStep(4);
  });
  $("#back5")?.addEventListener("click", () => {
    console.log("⬅️ Paso 5");
    showStep(5);
  });

  // 🧠 Recuperar el paso guardado para esta reservación específica
let pasoGuardado = 1;
if (ID_RESERVACION) {
  const guardado = localStorage.getItem(`contratoPasoActual_${ID_RESERVACION}`);
  pasoGuardado = guardado ? Number(guardado) : 1;
}

showStep(pasoGuardado);
});

