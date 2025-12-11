/* ==========================================================
   📑 Navegación entre pasos — Contrato
   ✅ Versión con Paso 1 mejorado (fechas + vehículos)
========================================================== */

document.addEventListener("DOMContentLoaded", () => {
  console.log("✅ DOM listo, iniciando navegación de pasos...");

  /* ==========================================================
     🧾 VARIABLES GLOBALES DEL CONTRATO
  ========================================================== */

  const contratoApp = document.getElementById("contratoInicial");
  const ID_CONTRATO = contratoApp?.dataset.idContrato || null;
  const NUM_CONTRATO = contratoApp?.dataset.codigo || "";
  const ID_RESERVACION = contratoApp?.dataset.idReservacion || null;

  // 🆕 AGREGADO: intervalo global para monitoreo
  let intervaloAprobacion = null;
/* ==========================================================
   🔝 ORDEN OFICIAL DE CATEGORÍAS + FUNCIÓN DE SUPERIORES
========================================================== */

const ORDEN_CATEGORIAS = [
  "C",
  "D",
  "E",
  "F",
  "IC",
  "I",
  "IB",
  "M",
  "L",
  "H",
  "HI",
];

/**
 * 📌 Devuelve TODAS las categorías superiores a la actual
 * (según el orden definido arriba)
 */
function obtenerCategoriasSuperiores(codigoActual) {
  const indexActual = ORDEN_CATEGORIAS.indexOf(codigoActual);
  if (indexActual === -1) return [];

  // Retorna todas las categorías después de la actual
  return ORDEN_CATEGORIAS.slice(indexActual + 1);
}

/* ==========================================================
   🎁 FUNCIÓN — ELEGIR UNA SOLA OFERTA RANDOM
========================================================== */

/**
 * @param {string[]} categoriasSuperiores  (ej: ["D","E","F"])
 * @returns {string|null}   código de categoría elegida
 */
function elegirCategoriaOferta(categoriasSuperiores) {
  if (!categoriasSuperiores || categoriasSuperiores.length === 0) {
    return null;
  }

  const randomIndex = Math.floor(Math.random() * categoriasSuperiores.length);
  return categoriasSuperiores[randomIndex];
}

/* ==========================================================
   🟦 BLOQUE 3 — Construcción de la oferta desde DB
========================================================== */

async function construirOfertaCategoria(codigoCategoria) {
  if (!codigoCategoria) return null;

  try {
    const resp = await fetch(`/admin/contrato/categoria-info/${codigoCategoria}`);
    const data = await resp.json();

    if (!data.success || !data.categoria) {
      console.warn("❌ Categoría no encontrada en DB");
      return null;
    }

    const cat = data.categoria;

    // =====================================================
    // 🔍 SEGUNDO FETCH — Vehículo random de esta categoría
    // =====================================================
    const respVeh = await fetch(`/admin/contrato/vehiculo-random/${cat.id_categoria}`);
    const dataVeh = await respVeh.json();

    let veh = null;
    if (dataVeh.success && dataVeh.vehiculo) {
      veh = dataVeh.vehiculo;
    }

    // =====================================================
    // 🔥 PRECIOS
    // =====================================================
    const precioReal = Number(cat.precio_dia);
    const precioInflado = Math.round(precioReal * 1.35);
    const descuento = Math.round(((precioInflado - precioReal) / precioInflado) * 100);

    // =====================================================
    // 🎁 ARMAR OBJETO OFERTA COMPLETO
    // =====================================================
    return {
      id_categoria: cat.id_categoria,
      codigo: cat.codigo,
      nombre: cat.nombre,
      descripcion: cat.descripcion,
      precioReal,
      precioInflado,
      descuento,

      // ============================
      // DATOS EXTRA DEL VEHÍCULO
      // ============================
      imagen: veh?.foto_url ?? "/img/default-car.jpg",
      nombre_vehiculo: veh?.nombre_publico ?? cat.nombre,
      transmision: veh?.transmision ?? null,
      asientos: veh?.asientos ?? null,
      puertas: veh?.puertas ?? null,
      color: veh?.color ?? null
    };

  } catch (err) {
    console.error("❌ Error obteniendo categoría/vehículo:", err);
    return null;
  }
}



/* ============================================================
   ⭐ BLOQUE 4 — MOSTRAR MODAL DE UPGRADES
============================================================ */

function mostrarModalOferta(oferta) {
  const modal = document.getElementById("modalUpgrade");

  // 🟥 Título de categoría
  document.getElementById("upgTitulo").textContent = oferta.nombre;

  // 💵 precios
  document.getElementById("upgPrecioInflado").textContent = `$${oferta.precioInflado}`;
  document.getElementById("upgPrecioReal").textContent = `$${oferta.precioReal}`;
  document.getElementById("upgDescuento").textContent = `${oferta.descuento}% de descuento`;

  // 📄 descripción
  document.getElementById("upgDescripcion").textContent = oferta.descripcion;

  // 🚗 imagen
  document.getElementById("upgImagenVehiculo").src =
    oferta.imagen || "/img/default-car.jpg";

  // 🟦 nombre del vehículo
  document.getElementById("upgNombreVehiculo").textContent =
    oferta.nombre_vehiculo ?? oferta.nombre;

  // 🟧 especificaciones
  document.getElementById("upgSpecs").innerHTML = `
      <div>${oferta.transmision ?? "—"}</div>
      <div>${oferta.asientos ?? "--"} asientos</div>
      <div>${oferta.puertas ?? "--"} puertas</div>
      <div>${oferta.color ?? "—"}</div>
  `;

  // 🟩 Guardar ID real para aplicar upgrade
  modal.dataset.idCategoriaUpgrade = oferta.id_categoria;

  modal.classList.add("show");
}




/* ============================================================
   ⭐ Aplicar upgrade
============================================================ */

async function aceptarUpgrade() {
  const modal = document.getElementById("modalUpgrade");
  const nuevaCategoria = modal.dataset.idCategoriaUpgrade;

  if (!nuevaCategoria) {
    alertify.error("No se pudo aplicar upgrade (ID vacío).");
    return;
  }

  try {
    const resp = await fetch(`/admin/contrato/${ID_RESERVACION}/actualizar-categoria`, {
      method: "POST",
      headers: {
        "Content-Type": "application/json",
        "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').content,
      },
      body: JSON.stringify({ id_categoria: nuevaCategoria }),
    });

    const data = await resp.json();

    if (!data.success) {
      alertify.error("Error aplicando upgrade.");
      return;
    }

    // ✔ Actualizar categoría actual en JS
    categoriaActual = nuevaCategoria;

    // ✔ Actualizar select exterior
    if (selectCategoriaOutside) {
      selectCategoriaOutside.value = nuevaCategoria;
    }

    alertify.success("Upgrade aplicado con éxito.");

    // Cerrar modal
    modal.classList.remove("show");

    // 🔄 Recalcular totales
    await actualizarFechasYRecalcular();
    await cargarResumenBasico();
    await cargarResumenBasico();


    // ➡️ Continuar al Paso 2
    showStep(2);

  } catch (e) {
    console.error(e);
    alertify.error("Error aplicando upgrade.");
  }
}



document.getElementById("btnAceptarUpgrade")
  .addEventListener("click", aceptarUpgrade);

document.getElementById("btnRechazarUpgrade")
  .addEventListener("click", () => {
    document.getElementById("modalUpgrade").classList.remove("show");
    showStep(2);
  });

document.getElementById("cerrarUpgrade")
  .addEventListener("click", () => {
    document.getElementById("modalUpgrade").classList.remove("show");
  });



    /* ============================================================
     🔗 SINCRONIZACIÓN DE CATEGORÍAS (modal ↔ afuera)
  ============================================================ */

  const selectCategoriaOutside = document.getElementById("selectCategoria");
  const selectCategoriaModal = document.getElementById("selectCategoriaModal");

  // Categoría inicial
  let categoriaActual = selectCategoriaOutside?.value || null;

  // 👉 Actualiza el valor dentro del modal al abrirlo
  function sincronizarCategoriaModal() {
      if (selectCategoriaModal) {
          selectCategoriaModal.value = categoriaActual;
      }
  }


  console.log(
    "📄 Contrato ID:",
    ID_CONTRATO,
    "| Reservación ID:",
    ID_RESERVACION,
    "| Código reserva:",
    NUM_CONTRATO
  );

  // Utilidades rápidas
  const $ = (s) => document.querySelector(s);
  const $$ = (s) => Array.from(document.querySelectorAll(s));

  /* ==========================================================
   🔢 FUNCIÓN GLOBAL PARA SUMA DE SERVICIOS + DELIVERY
========================================================== */
function actualizarTotal() {
  let total = 0;

  // Total servicios adicionales
  document.querySelectorAll(".card-servicio").forEach((card) => {
    const precio = parseFloat(card.dataset.precio || 0);
    const cantidad = parseInt(card.querySelector(".cantidad").textContent || 0);
    total += precio * cantidad;
  });

  // Delivery
  if (window.deliveryTotalActual) {
    total += window.deliveryTotalActual;
  }

  // Mostrar total
  const totalServicios = document.querySelector("#total_servicios");
  if (totalServicios) {
    totalServicios.textContent = `$${total.toFixed(2)} MXN`;
  }
}


  /* ==========================================================
     🔁 Mostrar paso
  ========================================================== */
  const showStep = (n) => {
    $$(".step").forEach((el) => {
      el.classList.toggle("active", Number(el.dataset.step) === n);
    });

    if (ID_RESERVACION) {
      localStorage.setItem(`contratoPasoActual_${ID_RESERVACION}`, n);
    }
    if (n === 6) cargarPaso6();
  };

  /* ==========================================================
     1️⃣ GUARDAR DATOS INTERNOS PASO 1
========================================================== */

function guardarDatosPaso1() {
  const datos = {
    codigo: $("#codigo")?.textContent.trim() || "",
    titular: $("#clienteNombre")?.textContent.trim() || "",
    sucursalEntrega: $(".bloque.entrega .lugar")?.textContent.trim() || "",
    sucursalDevolucion: $(".bloque.devolucion .lugar")?.textContent.trim() || "",
    fechaEntrega: $(".fecha-entrega-display")?.innerText.trim() || "",
    fechaDevolucion: $(".fecha-devolucion-display")?.innerText.trim() || "",
    horaEntrega: $(".bloque.entrega .hora")?.innerText.trim() || "",
    horaDevolucion: $(".bloque.devolucion .hora")?.innerText.trim() || "",
    telefono: $("#clienteTel")?.textContent.trim() || "",
    email: $("#clienteEmail")?.textContent.trim() || "",
    duracion: $("#diasBadge")?.textContent.trim() || "",
    total: $("#totalReserva")?.textContent.trim() || "",
  };

  sessionStorage.setItem("contratoPaso1", JSON.stringify(datos));
  console.log("📦 Datos Paso 1 guardados:", datos);
}

/* ==========================================================
   2️⃣ DETECTAR RESERVACIÓN NUEVA
========================================================== */

(function detectarReservaNueva() {
  const codigoActual = $("#codigo")?.textContent.trim();
  const datosGuardados = JSON.parse(sessionStorage.getItem("contratoPaso1") || "{}");

  if (!datosGuardados.codigo || datosGuardados.codigo !== codigoActual) {
    console.log("🔄 Nueva reservación detectada, sessionStorage limpiado.");
    sessionStorage.clear();

    const aviso = document.createElement("div");
    aviso.textContent = "🔄 Datos actualizados";
    aviso.style.cssText = `
      position: fixed; top: 20px; right: 20px;
      background: #10b981; color: white;
      padding: 10px 16px; border-radius: 8px;
      box-shadow: 0 4px 10px rgba(0,0,0,0.25);
      z-index: 9999; font-weight: bold;
      transition: opacity .6s;
    `;
    document.body.appendChild(aviso);
    setTimeout(() => (aviso.style.opacity = "0"), 1800);
    setTimeout(() => aviso.remove(), 2500);
  }

  setTimeout(guardarDatosPaso1, 300);
})();

/* ==========================================================
   3️⃣ EVENTOS DEL PASO 1
========================================================== */

function obtenerHoraActual() {
  const now = new Date();
  return now.toLocaleTimeString("en-GB", { hour: "2-digit", minute: "2-digit" });
}

(function inicializarPaso1() {
  console.log("🛠️ Inicializando Paso 1…");

  const lblHoraEntrega = $(".bloque.entrega .hora");
  if (lblHoraEntrega) lblHoraEntrega.textContent = obtenerHoraActual();

  /* ✏ Editar fecha de entrega */
  $$(".fecha-entrega-edit").forEach((btn) => {
    btn.addEventListener("click", () => {
      const cont = $(".fecha-edicion-entrega");
      if (!cont) return;

      cont.style.display = "block";

      $("#nuevaFechaEntrega").disabled = false;
      $("#nuevaFechaEntrega").value = contratoApp.dataset.inicio;

      $("#nuevaHoraEntrega").disabled = false;
      $("#nuevaHoraEntrega").value = obtenerHoraActual();

      $("#btnSolicitarCambioEntrega").style.display = "inline-flex";
    });
  });

  /* ✏ Editar fecha de devolución (NO requiere autorización) */
  $$(".fecha-devolucion-edit").forEach((btn) => {
    btn.addEventListener("click", () => {
      const cont = $(".fecha-edicion-devolucion");
      cont.style.display = "block";

      $("#nuevaFechaDevolucion").value = contratoApp.dataset.fin;
      $("#nuevaHoraDevolucion").value = contratoApp.dataset.horaEntrega;
    });
  });

  $("#btnGuardarFechaDevolucion")?.addEventListener("click", async () => {
    await actualizarFechasYRecalcular();
    await cargarResumenBasico();

    $(".fecha-edicion-devolucion").style.display = "none";
  });

    /* ============================================================
       🟧 CAMBIO DE CATEGORÍA DESDE EL SELECT DE AFUERA
  ============================================================ */
  selectCategoriaOutside?.addEventListener("change", async (e) => {
      const nuevaCat = e.target.value;
      categoriaActual = nuevaCat;

      // sincronizar modal
      if (selectCategoriaModal) {
          selectCategoriaModal.value = nuevaCat;
      }

      try {
          await fetch(`/admin/contrato/${ID_RESERVACION}/actualizar-categoria`, {
              method: "POST",
              headers: {
                  "Content-Type": "application/json",
                  "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').content,
              },
              body: JSON.stringify({ id_categoria: nuevaCat }),
          });

          alertify.success("Categoría actualizada.");

          // Recalcular totales del contrato
          await actualizarFechasYRecalcular();

      } catch (err) {
          console.error("❌ Error actualizando categoría desde afuera", err);
      }
  });



  $("#btnElegirVehiculo")?.addEventListener("click", abrirModalVehiculos);
  $("#cerrarModalVehiculos")?.addEventListener("click", cerrarModalVehiculos);
  $("#cerrarModalVehiculos2")?.addEventListener("click", cerrarModalVehiculos);
})();

/* ==========================================================
   🔧 FUNCIÓN GLOBAL — RECALCULAR Y ACTUALIZAR RESERVACIÓN
========================================================== */

async function actualizarFechasYRecalcular() {
  if (!contratoApp) return;

  const idReservacion = contratoApp.dataset.idReservacion;

  const fechaInicio = contratoApp.dataset.inicio;
  const horaInicio  = contratoApp.dataset.horaEntrega;


  const fechaFin = $("#nuevaFechaDevolucion")?.value || contratoApp.dataset.fin;
  const horaFin  = $("#nuevaHoraDevolucion")?.value || contratoApp.dataset.horaEntrega;

  try {
    const resp = await fetch(`/admin/contrato/${idReservacion}/recalcular-total`, {
      method: "POST",
      headers: {
        "Content-Type": "application/json",
        "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').content,
      },
      body: JSON.stringify({
        fecha_inicio: fechaInicio,
        hora_inicio: horaInicio,
        fecha_fin: fechaFin,
        hora_fin: horaFin,
        id_categoria: categoriaActual,
      }),
    });

    const data = await resp.json();

    // UI: Días
    $("#diasBadge").textContent = `${data.dias} días`;

    // UI: Total
    $("#totalReserva").textContent =
      `$${data.total_formateado} ${data.moneda}`;

    // Actualizar dataset del contrato
    contratoApp.dataset.fin = fechaFin;
    contratoApp.dataset.horaEntrega = horaFin;

    // Actualizar fecha en UI
    const partes = fechaFin.split("-");
    $(".fecha-devolucion-display .dia").textContent = partes[2];
    $(".fecha-devolucion-display .mes").textContent =
      new Date(fechaFin).toLocaleString("es-MX", { month: "short" }).toUpperCase();
    $(".fecha-devolucion-display .anio").textContent = partes[0];
    $(".bloque.devolucion .hora").textContent = horaFin;

    guardarDatosPaso1();

  } catch (err) {
    console.error("❌ Error recalculando:", err);
  }
}


async function guardarNuevaCategoriaEnDB(idCategoria) {
  try {
    const idReservacion = contratoApp.dataset.idReservacion;

    const resp = await fetch(`/admin/contrato/${idReservacion}/actualizar-categoria`, {
      method: "POST",
      headers: {
        "Content-Type": "application/json",
        "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').content,
      },
      body: JSON.stringify({ id_categoria: idCategoria }),
    });

    const data = await resp.json();

    if (!data.success) {
      alertify.error("Error actualizando categoría.");
      return;
    }

    // ✔ Mensaje base
    alertify.success("Categoría actualizada correctamente.");

    // ✔ Si el backend quitó el vehículo asignado
    if (data.vehiculo_removido) {
      alertify.warning("El vehículo asignado ha sido removido al cambiar la categoría.");
    }

    // ✔ Si había tarifa modificada y fue eliminada
    if (data.tarifa_limpiada) {
      alertify.message("Tarifa personalizada eliminada. Se aplicará la tarifa base de la nueva categoría.");
    }

    console.log("✔ Categoría guardada en BD:", idCategoria);

    // ⚠️ IMPORTANTE: limpio dataset de vehículo asignado en frontend
    contratoApp.dataset.idVehiculo = "";

    // ⚠️ Forzar recálculo después del cambio
    await actualizarFechasYRecalcular();


  } catch (err) {
    alertify.error("Error guardando categoría.");
    console.error("❌ Error en actualizar categoría:", err);
  }
}




/* ==========================================================
   4️⃣ ENVÍO DE SOLICITUD — Activación del monitoreo
========================================================== */

$("#btnSolicitarCambioEntrega")?.addEventListener("click", enviarSolicitudCambioEntrega);

async function enviarSolicitudCambioEntrega() {
  const nuevaFecha = $("#nuevaFechaEntrega")?.value;
  const nuevaHora = $("#nuevaHoraEntrega")?.value;

  if (!nuevaFecha || !nuevaHora) {
    alert("Debe seleccionar fecha y hora.");
    return;
  }

  try {
    const resp = await fetch("/admin/contrato/solicitar-cambio-fecha", {
      method: "POST",
      headers: {
        "Content-Type": "application/json",
        "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').content,
      },
      body: JSON.stringify({
        id_reservacion: contratoApp.dataset.idReservacion,
        nueva_fecha: nuevaFecha,
        nueva_hora: nuevaHora,
        motivo: "Cambio solicitado por asesor",
      }),
    });

    const data = await resp.json();
    alert(data.msg || "Solicitud enviada.");

    sessionStorage.setItem(
      "solicitudCambio",
      JSON.stringify({
        activa: true,
        id_reservacion: contratoApp.dataset.idReservacion,
      })
    );

    iniciarMonitoreoAprobacion();

    $(".fecha-edicion-entrega").style.display = "none";
    $("#btnSolicitarCambioEntrega").style.display = "none";

    $("#nuevaFechaEntrega").disabled = true;
    $("#nuevaHoraEntrega").disabled = true;
  } catch (err) {
    alert("Error enviando solicitud.");
  }
}

/* ==========================================================
   🔍 FUNCIÓN — MONITOREO INTELIGENTE SOLO CUANDO SE SOLICITA
========================================================== */

function iniciarMonitoreoAprobacion() {
  const solicitud = JSON.parse(sessionStorage.getItem("solicitudCambio") || "{}");

  if (!solicitud.activa) return;

  if (intervaloAprobacion) clearInterval(intervaloAprobacion);

  intervaloAprobacion = setInterval(async () => {
    try {
      const resp = await fetch(`/admin/contrato/cambio-fecha/estado/${solicitud.id_reservacion}`);
      const data = await resp.json();

      console.log("🔎 Estado actual:", data.estado);

      if (data.estado === "aprobado") {
        clearInterval(intervaloAprobacion);
        intervaloAprobacion = null;
        sessionStorage.removeItem("solicitudCambio");

        /* 🔥 SOLO SE ACTUALIZA LA FECHA DE ENTREGA */
        contratoApp.dataset.inicio = data.fecha_nueva;

        /* 🔥 YA NO SE IGUALA LA DEVOLUCIÓN */
        // contratoApp.dataset.fin = data.fecha_nueva;

        /* Actualizar UI de ENTREGA */
        const partes = data.fecha_nueva.split("-");
        $(".fecha-entrega-display .dia").textContent = partes[2];
        $(".fecha-entrega-display .mes").textContent =
          new Date(data.fecha_nueva).toLocaleString("es-MX", { month: "short" }).toUpperCase();
        $(".fecha-entrega-display .anio").textContent = partes[0];

        /* ❌ QUITADO: YA NO ACTUALIZAMOS DEVOLUCIÓN */
        // $(".fecha-devolucion-display .dia").textContent = partes[2];
        // $(".fecha-devolucion-display .mes").textContent =
        //   new Date(data.fecha_nueva).toLocaleString("es-MX", { month: "short" }).toUpperCase();
        // $(".fecha-devolucion-display .anio").textContent = partes[0];

        setTimeout(() => actualizarFechasYRecalcular(), 350);
        setTimeout(() => guardarDatosPaso1(), 600);

        alertify.success("Cambio de fecha aprobado por el administrador.");
      }

      if (data.estado === "rechazado") {
        clearInterval(intervaloAprobacion);
        intervaloAprobacion = null;
        sessionStorage.removeItem("solicitudCambio");
        alertify.error("El administrador rechazó la solicitud.");
      }
    } catch (err) {
      console.error("❌ Error monitoreando aprobación:", err);
    }
  }, 8000);
}

/* ==========================================================
   🔁 REANUDAR MONITOREO SI ESTABA PENDIENTE
========================================================== */

(function reanudarMonitoreoSiAplica() {
  const solicitud = JSON.parse(sessionStorage.getItem("solicitudCambio") || "{}");
  if (solicitud.activa) iniciarMonitoreoAprobacion();
})();

  /* ============================================================
       🟩 CAMBIO DE CATEGORÍA DESDE EL MODAL
  ============================================================ */
  selectCategoriaModal?.addEventListener("change", async (e) => {
      const nuevaCat = e.target.value;

      categoriaActual = nuevaCat;

      // sincronizar con el select de afuera
      if (selectCategoriaOutside) {
          selectCategoriaOutside.value = nuevaCat;
      }

      try {
          await fetch(`/admin/contrato/${ID_RESERVACION}/actualizar-categoria`, {
              method: "POST",
              headers: {
                  "Content-Type": "application/json",
                  "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').content,
              },
              body: JSON.stringify({ id_categoria: nuevaCat }),
          });

          alertify.success("Categoría actualizada.");

          // Recargar vehículos automáticamente
          cargarVehiculosCategoriaModal();

      } catch (err) {
          console.error("❌ Error actualizando categoría desde modal", err);
      }
  });

/* ==========================================================
   6️⃣ MODAL — VEHÍCULOS
========================================================== */

let listaVehiculosOriginal = [];

/* 🔹 ABRIR MODAL Y CARGAR VEHÍCULOS */
async function abrirModalVehiculos() {
  const modal = $("#modalVehiculos");
  modal.classList.add("show-modal");

  sincronizarCategoriaModal();
  categoriaActual = selectCategoriaOutside.value;

  try {
    const resp = await fetch(`/admin/contrato/vehiculos-por-categoria/${categoriaActual}`);
    const data = await resp.json();

    if (!data.success) {
      console.error("❌ Error en API:", data.error);
      return;
    }

    listaVehiculosOriginal = data.data;
    renderVehiculosEnModal(data.data);

  } catch (err) {
    console.error("❌ Error cargando vehículos:", err);
  }
}

  async function cargarVehiculosCategoriaModal() {
      try {
          const resp = await fetch(`/admin/contrato/vehiculos-por-categoria/${categoriaActual}`);
          const data = await resp.json();

          if (data.success) {
              listaVehiculosOriginal = data.data;
              renderVehiculosEnModal(data.data);
          }
      } catch (err) {
          console.error("❌ Error cargando vehículos", err);
      }
  }

/* 🔹 CERRAR MODAL */
function cerrarModalVehiculos() {
  $("#modalVehiculos").classList.remove("show-modal");
}

/* 🔹 RENDER DE VEHÍCULOS */
function renderVehiculosEnModal(lista) {
  const cont = $("#listaVehiculos");
  cont.innerHTML = "";

  if (!lista || lista.length === 0) {
    cont.innerHTML = `<p style="padding:20px; text-align:center; color:#555;">No hay vehículos disponibles.</p>`;
    return;
  }

  lista.forEach((v) => {

    // =========================
    // GASOLINA (DIECISEISAVOS)
    // =========================
    const g = v.gasolina_actual ?? 0;

    // barra visual
    const filled = "█".repeat(g);
    const empty = "░".repeat(16 - g);
    const barraGas = `${filled}${empty}`;

    // Fracciones comunes
    const comunes = {
      2: "1/8",
      4: "1/4",
      6: "3/8",
      8: "1/2",
      10: "5/8",
      12: "3/4",
      14: "7/8",
      16: "1"
    };

    const fraccionComun = comunes[g] ? ` – ${comunes[g]}` : "";


    // =========================
    // MANTENIMIENTO
    // =========================
    let iconMant = "⚪";
    if (v.color_mantenimiento === "verde") iconMant = "🟢";
    if (v.color_mantenimiento === "amarillo") iconMant = "🟡";
    if (v.color_mantenimiento === "rojo") iconMant = "🔴";

    const kmRest = v.km_restantes !== null
        ? `${v.km_restantes} km restantes`
        : "—";


    // =========================
    // HTML DEL VEHÍCULO
    // =========================
    cont.innerHTML += `
      <div class="vehiculo-card">
        <img src="${v.foto_url ?? "/img/default-car.jpg"}" class="vehiculo-img">

        <div class="vehiculo-info">
          <h4>${v.nombre_publico}</h4>
          <p>${v.transmision} · ${v.asientos} asientos · ${v.puertas} puertas</p>
          <p>Color: ${v.color ?? "—"}</p>

          <p><b>Gasolina:</b> ${barraGas} (${g}/16${fraccionComun})</p>

          <p><b>Placa:</b> ${v.placa ?? "—"}</p>
          <p><b>Kilometraje:</b> ${v.kilometraje?.toLocaleString() ?? "—"} km</p>

          <p><b>Póliza vence:</b> ${v.fin_vigencia_poliza ?? "—"}</p>

          <p><b>Mantenimiento:</b> ${iconMant} ${kmRest}</p>
        </div>

        <button class="btn-vehiculo" data-id="${v.id_vehiculo}">
          Seleccionar
        </button>
      </div>
    `;
  });

  $$(".btn-vehiculo").forEach((btn) =>
    btn.addEventListener("click", () => seleccionarVehiculo(btn.dataset.id))
  );
}


/* 🔹 LISTENERS DE LOS FILTROS */
["filtroColor", "filtroModelo", "filtroSerie"].forEach((id) => {
  const el = document.getElementById(id);
  if (el) el.addEventListener("input", filtrarVehiculos);
});

/* 🔹 FILTRADO */
function filtrarVehiculos() {
  const color = $("#filtroColor").value.toLowerCase();
  const modelo = $("#filtroModelo").value.toLowerCase();
  const serie = $("#filtroSerie").value.toLowerCase();

  const nuevaLista = listaVehiculosOriginal.filter(
    (v) =>
      (v.color ?? "").toLowerCase().includes(color) &&
      (v.modelo ?? "").toLowerCase().includes(modelo) &&
      (v.numero_serie ?? "").toLowerCase().includes(serie)
  );

  renderVehiculosEnModal(nuevaLista);
}

/* 🔹 ASIGNAR VEHÍCULO */
async function seleccionarVehiculo(idVehiculo) {
  try {
    await fetch("/admin/contrato/asignar-vehiculo", {
      method: "POST",
      headers: {
        "Content-Type": "application/json",
        "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').content,
      },
      body: JSON.stringify({
        id_reservacion: contratoApp.dataset.idReservacion,
        id_vehiculo: idVehiculo,
      }),
    });

    alertify.success("Vehículo asignado correctamente.");
    await cargarResumenBasico();

    cerrarModalVehiculos();

  } catch (err) {
    alert("Error asignando vehículo.");
  }
}

/* 🔹 BOTONES CERRAR */
$("#cerrarModalVehiculos")?.addEventListener("click", cerrarModalVehiculos);
$("#cerrarModalVehiculos2")?.addEventListener("click", cerrarModalVehiculos);



  /* ==========================================================
     ⚙️ PASO 2: Manejo de servicios adicionales
========================================================== */
const idReservacion = ID_RESERVACION;
const serviciosGrid = $("#serviciosGrid");
const totalServicios = $("#total_servicios");

if (serviciosGrid) {
  console.log("🧩 Iniciando gestión de servicios adicionales...");



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
      🚚 PASO 2 — MANEJO DE DELIVERY (CORREGIDO)
========================================================== */

// Elementos del HTML
const deliveryToggle = $("#deliveryToggle");
const deliveryFields = $("#deliveryFields");
const deliveryUbicacion = $("#deliveryUbicacion");
const deliveryDireccion = $("#deliveryDireccion");
const deliveryKm = $("#deliveryKm");
const deliveryTotal = $("#deliveryTotal");

// NUEVOS: grupos de inputs
const groupDireccion = $("#groupDireccion");
const groupKm = $("#groupKm");

// Costo por km según la categoría (solo una vez)
let costoCategoriaKM = parseFloat($("#deliveryPrecioKm")?.value || 0);

// Variable global del total de delivery
window.deliveryTotalActual = 0;





/* ==========================================================
   🔄 MOSTRAR/OCULTAR CAMPOS AL ACTIVAR SWITCH
========================================================== */
if (deliveryToggle) {
  deliveryToggle.addEventListener("change", () => {
    if (deliveryToggle.checked) {
      deliveryFields.style.display = "block";
    } else {
      deliveryFields.style.display = "none";

      // Reset total
      window.deliveryTotalActual = 0;
      deliveryTotal.textContent = "$0.00 MXN";

      // Limpiar campos
      deliveryUbicacion.value = "";
      deliveryDireccion.value = "";
      deliveryKm.value = "";

      groupDireccion.style.display = "none";
      groupKm.style.display = "none";

      actualizarTotal();
      guardarDelivery();   // ← AGREGA ESTA LÍNEA



    }
  });
}

/* ==========================================================
   🔁 FUNCIONES PARA MOSTRAR / OCULTAR PERSONALIZADA
========================================================== */
function actualizarVisibilidadCampos() {
  if (!deliveryUbicacion) return;

  if (deliveryUbicacion.value === "0") {
    groupDireccion.style.display = "block";
    groupKm.style.display = "block";
  } else {
    groupDireccion.style.display = "none";
    groupKm.style.display = "none";

    // Limpiar para evitar cálculos mezclados
    deliveryDireccion.value = "";
    deliveryKm.value = "";
  }
}

/* ==========================================================
   🔢 CALCULAR TOTAL DELIVERY
========================================================== */
const recalcularDelivery = () => {
  let kms = 0;

  // 1️⃣ Si eligió una ubicación del catálogo
  if (deliveryUbicacion.value && deliveryUbicacion.value !== "0") {
    kms = parseFloat(
      deliveryUbicacion.options[deliveryUbicacion.selectedIndex].dataset.km
    );
  }

  // 2️⃣ Si seleccionó personalizada → usar los KM escritos
  if (deliveryUbicacion.value === "0") {
    if (deliveryKm.value && parseFloat(deliveryKm.value) > 0) {
      kms = parseFloat(deliveryKm.value);
    }
  }

  // Calcular total
  const total = kms * costoCategoriaKM;
  window.deliveryTotalActual = total;

  deliveryTotal.textContent = `$${total.toFixed(2)} MXN`;

  actualizarTotal();
};

document.addEventListener("categoriaActualizada", () => {
    recalcularDelivery();
    actualizarTotal();
});


/* ==========================================================
   🎧 EVENTOS DELIVERY
========================================================== */

// Cargar total guardado en BD al entrar al paso
if ($("#deliveryTotalHidden")) {
    window.deliveryTotalActual = parseFloat($("#deliveryTotalHidden").value || 0);
    deliveryTotal.textContent = `$${window.deliveryTotalActual.toFixed(2)} MXN`;
}
actualizarTotal();
// ⚠️ AGREGA ESTO
actualizarVisibilidadCampos();
recalcularDelivery();


// Cambiar ubicación
if (deliveryUbicacion) {
  deliveryUbicacion.addEventListener("change", () => {
    actualizarVisibilidadCampos();
    recalcularDelivery();
    guardarDelivery();
  });
}

// KM personalizados
if (deliveryKm) {
  deliveryKm.addEventListener("input", () => {
    recalcularDelivery();
    guardarDelivery();
  });
}

// Dirección personalizada
if (deliveryDireccion) {
  deliveryDireccion.addEventListener("input", () => {
    guardarDelivery();
  });
}

/* ==========================================================
      💾 GUARDAR DELIVERY EN BACKEND (VERSIÓN CORRECTA)
========================================================== */
async function guardarDelivery() {

  // 🚨 Validar reservación (NO contrato)
  if (!idReservacion) {
      console.error("❌ No existe idReservacion");
      return;
  }

  let kms = 0;

  if (deliveryUbicacion.value && deliveryUbicacion.value !== "0") {
      kms = parseFloat(
          deliveryUbicacion.options[deliveryUbicacion.selectedIndex].dataset.km
      );
  }

  if (deliveryUbicacion.value === "0" && deliveryKm.value) {
      kms = parseFloat(deliveryKm.value);
  }

  try {
      const resp = await fetch(`/admin/reservacion/delivery/guardar`, {
          method: "POST",
          headers: {
              "Content-Type": "application/json",
              "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').content,
          },
          body: JSON.stringify({
                  id_reservacion: idReservacion,
              delivery_activo: deliveryToggle.checked ? 1 : 0,
              delivery_ubicacion:
                  deliveryUbicacion.value !== "0"
                      ? deliveryUbicacion.value
                      : "0",
              delivery_direccion:
                  deliveryUbicacion.value === "0"
                      ? deliveryDireccion.value
                      : null,
              delivery_km: parseFloat(kms) || 0,
              delivery_precio_km: parseFloat(costoCategoriaKM) || 0,
              delivery_total: parseFloat(window.deliveryTotalActual) || 0
          }),
      });

      const data = await resp.json();
      console.log("🚚 Delivery guardado:", data);

  } catch (err) {
      console.error("❌ Error al guardar Delivery:", err);
  }
  await cargarResumenBasico();

}





  /* ==========================================================
     🛡️ PASO 3: Manejo de seguros (paquetes + individuales)
========================================================== */
const packGrid         = $("#packGrid");
const individualesGrid = $$(".cards.scroll-h");
const totalSeguros     = $("#total_seguros");
const btnContinuarPaso3 = $("#go4");

/* 🧱 Modales de paquetes e individuales */
const modalPaquetes       = $("#modalPaquetes");
const modalIndividuales   = $("#modalIndividuales");
const btnPaquetesModal    = $("#btnVerPaquetes");
const btnIndividualesModal = $("#btnVerIndividuales");

/* ==========================================================
   🔓 Helpers para abrir/cerrar modales
========================================================== */
const abrirModal = (modal) => {
  if (!modal) return;
  modal.style.display = "flex";
};

const cerrarModal = (modal) => {
  if (!modal) return;
  modal.style.display = "none";
};

/* Botones para abrir modales */
if (btnPaquetesModal && modalPaquetes) {
  btnPaquetesModal.addEventListener("click", () => {
    abrirModal(modalPaquetes);
  });
}

if (btnIndividualesModal && modalIndividuales) {
  btnIndividualesModal.addEventListener("click", () => {
    abrirModal(modalIndividuales);
  });
}

/* Botones genéricos de cierre dentro de los modales (.modal-close) */
$$(".close-modal, .closeModal").forEach((btn) => {
  btn.addEventListener("click", () => {
    const t = btn.dataset.target;
    if (t === "paquetes")    cerrarModal(modalPaquetes);
    if (t === "individuales") cerrarModal(modalIndividuales);
  });
});


/* Cerrar modal haciendo click en el fondo si quieres (opcional) */
[modalPaquetes, modalIndividuales].forEach((modal) => {
  if (!modal) return;
  modal.addEventListener("click", (e) => {
    if (e.target === modal) {
      cerrarModal(modal);
    }
  });
});

/* ==========================================================
   🧮 Cálculo del total (paquete + individuales)
========================================================== */
function recalcularTotalProtecciones() {
  if (!totalSeguros || !btnContinuarPaso3) return;

  let total = 0;

  // 🔹 Paquete activo (si existe)
  const paqueteActivo = packGrid
    ? packGrid.querySelector(".switch.on")
    : null;

  if (paqueteActivo) {
    const card = paqueteActivo.closest(".card");
    const precio = parseFloat(card?.dataset.precio || 0);
    total += precio;
  }

  // 🔹 Individuales activos (si el modal existe)
  if (individualesGrid && individualesGrid.length > 0) {
  individualesGrid.forEach(grid => {
    const activos = grid.querySelectorAll(".switch-individual.on");
    activos.forEach(sw => {
      const card = sw.closest(".card") || sw.closest(".seguro-individual");
      const precio = parseFloat(card?.dataset.precio || 0);
      total += precio;
    });
  });
}

  totalSeguros.textContent = `$${total.toFixed(2)} MXN`;
  btnContinuarPaso3.disabled = total <= 0;
}

/* Helpers para limpiar UI */
function desactivarTodosLosPaquetesUI() {
  if (!packGrid) return;
  const switchesPaquetes = packGrid.querySelectorAll(".switch");
  switchesPaquetes.forEach((sw) => sw.classList.remove("on"));
}

function desactivarTodasLasIndividualesUI() {
  if (!individualesGrid || individualesGrid.length === 0) return;
  individuos = individualesGrid.forEach(grid => {
    const switches = grid.querySelectorAll(".switch-individual");
    switches.forEach(sw => sw.classList.remove("on"));
  });
}


/* ==========================================================
   🧱 LÓGICA DE PAQUETES (ya existente pero mejorada)
========================================================== */
if (packGrid) {
  console.log("🛡️ Iniciando gestión de seguros (paquetes)...");

  const switches = $$(".switch");

  // Actualiza visualmente los switches (SOLO paquetes) y recalcula total
  const actualizarEstadoVisualPaquetes =  async (activoId) => {
    switches.forEach((sw) => {
      const isActive = Number(sw.dataset.id) === Number(activoId);
      sw.classList.toggle("on", isActive);
    });

    // Recalcular total global (paquete + individuales)
    recalcularTotalProtecciones();
    await cargarResumenBasico();

  };

  // Detecta click sobre un switch de paquete
  packGrid.addEventListener("click", async (e) => {
    const sw = e.target.closest(".switch");
    if (!sw) return;

    const idPaquete = sw.dataset.id;
    const estabaActivo = sw.classList.contains("on");
    const card = sw.closest(".card");
    const precio = parseFloat(card.dataset.precio || 0);

    try {
      // Si estaba activo → eliminar paquete
      if (estabaActivo) {
        console.log("🗑️ Eliminando seguro (paquete) activo...", { idPaquete });

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
        console.log("🗑️ Respuesta DELETE paquete:", data);

        actualizarEstadoVisualPaquetes(null);
        if (window.alertify) {
          alertify.success("Paquete de seguro eliminado.");
        }
        return;
      }

      // Si NO estaba activo → activar paquete
      console.log("🟢 Activando nuevo paquete de seguro...", { idPaquete, precio });

      // ⚠️ Regla: al activar paquete, desactivamos individuales en UI
      // ❗ Primero borrar individuales en la BD
await fetch(`/admin/contrato/seguros-individuales/todos`, {
    method: "DELETE",
    headers: {
        "Content-Type": "application/json",
        "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').content,
    },
    body: JSON.stringify({
        id_reservacion: idReservacion
    })
});

// Luego limpiar UI
desactivarTodasLasIndividualesUI();


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
      console.log("📡 Respuesta POST paquete:", data);

      actualizarEstadoVisualPaquetes(idPaquete);

      if (window.alertify) {
        alertify.success("Paquete de seguro seleccionado.");
      }
    } catch (err) {
      console.error("❌ Error al actualizar seguro (paquete):", err);
      if (window.alertify) {
        alertify.error("Error al actualizar el paquete de seguro. Revisa la consola.");
      }
    }
  });
}

/* ==========================================================
   🧱 LÓGICA DE INDIVIDUALES (Arma tu paquete)
   - Necesita backend: /admin/contrato/seguros-individuales
========================================================== */
if (individualesGrid && individualesGrid.length > 0) {
  individualesGrid.forEach(grid => {
    grid.addEventListener("click", async (e) => {
      const sw = e.target.closest(".switch-individual");
      if (!sw) return;

      const idSeguro = sw.dataset.id;
      const estabaActivo = sw.classList.contains("on");
      const card = sw.closest(".card") || sw.closest(".seguro-individual");
      const precio = parseFloat(card?.dataset.precio || 0);

      try {
        // Eliminar individual
        if (estabaActivo) {
          const resp = await fetch(`/admin/contrato/seguros-individuales`, {
            method: "DELETE",
            headers: {
              "Content-Type": "application/json",
              "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').content,
            },
            body: JSON.stringify({
              id_reservacion: idReservacion,
              id_seguro: idSeguro,
            }),
          });

          sw.classList.remove("on");
          recalcularTotalProtecciones();
          await cargarResumenBasico();

          return;
        }

        // Activar individual
        desactivarTodosLosPaquetesUI();

        const resp = await fetch(`/admin/contrato/seguros-individuales`, {
          method: "POST",
          headers: {
            "Content-Type": "application/json",
            "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').content,
          },
          body: JSON.stringify({
            id_reservacion: idReservacion,
            id_seguro: idSeguro,
            precio_por_dia: precio,
          }),
        });

        sw.classList.add("on");
        recalcularTotalProtecciones();
        await cargarResumenBasico();


      } catch (err) {
        console.error("❌ Error individual:", err);
      }

    });
  });
}


/* ==========================================================
   ⚙️ Inicialización del paso 3
   (por si vienen datos ya marcados desde Blade)
========================================================== */
recalcularTotalProtecciones();
cargarResumenBasico();





/* ==========================================================
   💰 PASO 4 — MANEJO DE CARGOS + CAMBIAR VEHÍCULO + GASOLINA
========================================================== */

console.log("🚀 Paso 4 inicializado…");

// ----------------------------
// Elementos
// ----------------------------
const totalCargos  = document.querySelector("#total_cargos");
const cargosGrid   = document.querySelector("#cargosGrid");
const contratoID   = ID_CONTRATO;

// Vehículo
const btnEditarVeh     = $("#editVeh");
const selectVehAssign  = $("#vehAssign");
const lblVehInfo       = $("#vehInfo");

// Gasolina
const switchGasLit  = $("#switchGasLit");
const gasInputs     = $("#gasLitrosInputs");
const gasPrecio     = $("#gasPrecioL");
const gasCant       = $("#gasCantL");
const gasTotalHTML  = $("#gasTotalHTML");


/* ==========================================================
   💾 GUARDAR / ELIMINAR CARGO
========================================================== */
async function guardarCargoPaso4(idConcepto) {
  try {
    const resp = await fetch(`/admin/contrato/cargos`, {
      method: "POST",
      headers: {
        "Content-Type": "application/json",
        "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').content,
      },
      body: JSON.stringify({
        id_contrato: contratoID,
        id_concepto: idConcepto
      }),
    });

    const data = await resp.json();

    if (!data.success && !data.status) {
      alertify.error("Error al actualizar cargo");
    } else {
      alertify.success("Actualizado correctamente");
    }

  } catch (err) {
    console.error("❌ ERROR guardando cargo:", err);
    alertify.error("Error en servidor");
  }
}


/* ==========================================================
   🧮 RECALCULAR TOTAL
========================================================== */
function recalcularTotalPaso4() {
  let total = 0;

  document.querySelectorAll(".cargo-item").forEach(card => {
    const sw = card.querySelector(".switch");
    if (sw?.classList.contains("on")) {
      total += parseFloat(card.dataset.monto || 0);
    }
  });
total += window.dropoffTotal || 0;
total += parseFloat(gasCant.value || 0) * parseFloat(gasPrecio.value || 0);

  totalCargos.textContent = `$${total.toFixed(2)} MXN`;
}


/* ==========================================================
   🧾 ACTIVAR / DESACTIVAR CARGOS
========================================================== */
if (cargosGrid) {
  cargosGrid.addEventListener("click", async (e) => {
    const sw = e.target.closest(".switch");
    if (!sw) return;

    const card = sw.closest(".cargo-item");
    const conceptoID = card.dataset.id;

    sw.classList.toggle("on");

    guardarCargoPaso4(conceptoID);
    recalcularTotalPaso4();
    await cargarResumenBasico();

  });
}


/* ==========================================================
   🔥 ACTIVAR AUTOMÁTICAMENTE CAMBIO DE VEHÍCULO (id=3)
========================================================== */
 async function activarCargoCambioVehiculo() {

  const cardCambio = document.querySelector('.cargo-item[data-id="3"]');
  if (!cardCambio) return;

  const sw = cardCambio.querySelector(".switch");
  const conceptoID = cardCambio.dataset.id;

  if (!sw.classList.contains("on")) {
    sw.classList.add("on");
    guardarCargoPaso4(conceptoID);
    recalcularTotalPaso4();
    await cargarResumenBasico();

  }
}


/* ==========================================================
   🔥 ACTIVAR AUTOMÁTICAMENTE TANQUE INCOMPLETO (id=2)
========================================================== */
 async function activarCargoTanqueIncompleto() {

  const card = document.querySelector('.cargo-item[data-id="2"]');
  if (!card) return;

  const sw = card.querySelector(".switch");
  const conceptoID = card.dataset.id;

  if (!sw.classList.contains("on")) {
    sw.classList.add("on");
    guardarCargoPaso4(conceptoID);
    recalcularTotalPaso4();
    await cargarResumenBasico();
  }
}


/* ==========================================================
   ⛽ GASOLINA FALTANTE — SWITCH
========================================================== */
if (switchGasLit) {

  switchGasLit.addEventListener("click",  async () => {

    switchGasLit.classList.toggle("on");

    if (switchGasLit.classList.contains("on")) {

      // 1️⃣ Activar cargo Tanque incompleto
      activarCargoTanqueIncompleto();

      // 2️⃣ Activar cargo Gasolina faltante (id=5)
      guardarCargoPaso4(5);

      // 3️⃣ Mostrar inputs
      gasInputs.style.display = "block";

    } else {

      // Ocultar inputs
      gasInputs.style.display = "none";
      gasCant.value = "";
      gasTotalHTML.textContent = "$0.00 MXN";

      // Desactivar cargos 5 y 2
      guardarCargoPaso4(5);
      guardarCargoPaso4(2);
    }

    recalcularTotalPaso4();
    await cargarResumenBasico();
  });
}


/* ==========================================================
   ⛽ CALCULAR MONTO DE GAS
========================================================== */
if (gasCant && gasPrecio) {

  gasCant.addEventListener("input", async () => {

    const litros = parseFloat(gasCant.value || 0);
    const precio = parseFloat(gasPrecio.value || 0);
    const total  = litros * precio;

    gasTotalHTML.textContent = `$${total.toFixed(2)} MXN`;

    // Guardar el monto variable
    await fetch(`/admin/contrato/cargo-variable`, {
      method: "POST",
      headers: {
        "Content-Type": "application/json",
        "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').content,
      },
      body: JSON.stringify({
        id_contrato: contratoID,
        id_concepto: 5,       // gasolina faltante
        litros,
        precio_litro: precio,
        monto_variable: total
      }),
    });
    // Actualizar el monto del concepto 5 dentro del HTML
const cardGas = document.querySelector('.cargo-item[data-id="5"]');
if (cardGas) {
    cardGas.dataset.monto = total;
}

    recalcularTotalPaso4();
    await cargarResumenBasico();

  });
}





/* ==========================================================
   🚗 ***CAMBIAR VEHÍCULO*** — ABRIR MODAL
========================================================== */
if (btnEditarVeh) {
  btnEditarVeh.addEventListener("click", () => {
    activarCargoCambioVehiculo();
    abrirModalVehiculosPaso4();
});

}


/* ==========================================================
   🟦 ABRIR MODAL VEHÍCULOS
========================================================== */
async function abrirModalVehiculosPaso4() {
  const modal = $("#modalVehiculos");
  modal.classList.add("show-modal");

  const categoriaReservacion = $("#vehAssign")?.dataset.cat ?? null;

  if (categoriaReservacion) {
    $("#selectCategoriaModal").value = categoriaReservacion;
    categoriaActual = categoriaReservacion;
  }

  try {
    const resp = await fetch(`/admin/contrato/vehiculos-por-categoria/${categoriaActual}`);
    const data = await resp.json();

    if (data.success) {
      listaVehiculosOriginal = data.data;
      renderVehiculosEnModal(listaVehiculosOriginal);
    }

  } catch (err) {
    console.error("❌ Error al cargar vehículos:", err);
  }
}


/* ==========================================================
   🚗 ASIGNAR VEHÍCULO
========================================================== */
async function seleccionarVehiculo(idVehiculo) {
  try {
    const resp = await fetch("/admin/contrato/asignar-vehiculo", {
      method: "POST",
      headers: {
        "Content-Type": "application/json",
        "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').content,
      },
      body: JSON.stringify({
        id_reservacion: contratoApp.dataset.idReservacion,
        id_vehiculo: idVehiculo,
      }),
    });

    const data = await resp.json();

    if (!data.success) {
      alertify.error("Error asignando vehículo");
      return;
    }

    alertify.success("Vehículo asignado correctamente");
    await cargarResumenBasico();


    cerrarModalVehiculos();
    actualizarVehiculoUI(data.vehiculo);

  } catch (err) {
    alertify.error("Error en servidor");
  }
}


/* ==========================================================
   🔄 REFRESCAR VISTA DEL VEHÍCULO
========================================================== */
function actualizarVehiculoUI(v) {

  if (!v) return;

  selectVehAssign.innerHTML = `
    <option value="${v.id_vehiculo}">
      ${v.marca} ${v.modelo} (${v.placa})
    </option>
  `;

  lblVehInfo.textContent = "Unidad actualizada correctamente.";
}

function restaurarSwitchGasolina() {
    const cardGas = document.querySelector('.cargo-item[data-id="5"]');
    if (!cardGas) return;

    // Si el cargo está activo en DB
    if (cardGas.dataset.monto > 0) {
        switchGasLit.classList.add("on");
        gasInputs.style.display = "block";
    }
}

/* ==========================================================
   🚚 DROPOFF — VARIABLES
========================================================== */

const switchDropoff   = $("#switchDropoff");
const dropoffFields   = $("#dropoffFields");
const dropUbicacion   = $("#dropUbicacion");
const dropDireccion   = $("#dropDireccion");
const dropKm          = $("#dropKm");
const dropCostoKmHTML = $("#dropCostoKmHTML");
const dropCostoKmBox  = $("#dropCostoKm");
const dropGroupDir    = $("#dropGroupDireccion");
const dropGroupKm     = $("#dropGroupKm");
const dropTotalHTML   = $("#dropTotal");

// COSTO KM según categoría (igual que Delivery)
let dropPrecioKm = parseFloat($("#deliveryPrecioKm")?.value || 0);

// Total actual del dropoff
window.dropoffTotal = 0;



/* ==========================================================
   🔄 Mostrar/Ocultar campos según opción
========================================================== */
function actualizarCamposDropoff() {

    if (!dropUbicacion) return;

    let val = dropUbicacion.value;

    // Si NO eligió nada
    if (val === "") {
        dropGroupDir.style.display = "none";
        dropGroupKm.style.display = "none";
        dropCostoKmBox.style.display = "none";
        return;
    }

    // Si es personalizada (0)
    if (val === "0") {
        dropGroupDir.style.display = "block";
        dropGroupKm.style.display = "block";
        dropCostoKmBox.style.display = "block";
        dropCostoKmHTML.innerText = `$${dropPrecioKm.toFixed(2)}`;
    } else {
        // Ubicación predefinida
        dropGroupDir.style.display = "none";
        dropGroupKm.style.display = "none";
        dropCostoKmBox.style.display = "block";
        dropCostoKmHTML.innerText = `$${dropPrecioKm.toFixed(2)}`;
    }
}



/* ==========================================================
   🧮 Calcular total del Dropoff
========================================================== */
 async function recalcularDropoff() {

    if (!dropUbicacion) return;

    let kms = 0;

    if (dropUbicacion.value !== "" && dropUbicacion.value !== "0") {
        // Toma los km del option
        kms = parseFloat(dropUbicacion.options[dropUbicacion.selectedIndex].dataset.km);
    }

    if (dropUbicacion.value === "0" && dropKm.value) {
        kms = parseFloat(dropKm.value);
    }

    let total = kms * dropPrecioKm;

    window.dropoffTotal = total;

    dropTotalHTML.textContent = `$${total.toFixed(2)} MXN`;

    // Actualiza el monto de la tarjeta (id_concepto 6)
    const card = document.querySelector('.cargo-item[data-id="6"]');
    if (card) {
        card.dataset.monto = total;
    }

    recalcularTotalPaso4();
    await cargarResumenBasico();

}



/* ==========================================================
   💾 Guardar Dropoff en cargo_adicional
========================================================== */
async function guardarDropoff() {

    try {
        await fetch(`/admin/contrato/cargo-variable`, {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
                "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').content,
            },
            body: JSON.stringify({
                id_contrato: contratoID,
                id_concepto: 6,

                // Datos guardados en columna DETALLE (json)
                destino: dropUbicacion.value === "0" ? dropDireccion.value : null,
                km: dropUbicacion.value === "0"
                       ? dropKm.value
                       : dropUbicacion.options[dropUbicacion.selectedIndex].dataset.km,
                precio_litro: dropPrecioKm, // usamos precio por KM igual que delivery
                monto_variable: window.dropoffTotal
            }),
        });

    } catch (err) {
        console.error("❌ Error guardando dropoff:", err);
    }
}



/* ==========================================================
   🎛 Activar / Desactivar Switch Dropoff
========================================================== */
if (switchDropoff) {
    switchDropoff.addEventListener("click",  async () => {

        switchDropoff.classList.toggle("on");

        if (switchDropoff.classList.contains("on")) {
            dropoffFields.style.display = "block";

            // Activar cargo (similar a gasolina)
            guardarCargoPaso4(6);

        } else {
            dropoffFields.style.display = "none";

            // Limpiar
            dropUbicacion.value = "";
            dropDireccion.value = "";
            dropKm.value = "";
            dropTotalHTML.textContent = "$0.00 MXN";

            // Desactivar cargo
            guardarCargoPaso4(6);
        }

        recalcularTotalPaso4();
        await cargarResumenBasico();

    });
}

async function restaurarEstadoDropoff() {
    try {
        const resp = await fetch(`/admin/contrato/cargos/${contratoID}`);
        const data = await resp.json();
        if (!data.success) return;

        const cargos = data.cargos;

        const drop = cargos.find(c => c.id_concepto == 6);
        if (!drop) return;

        switchDropoff.classList.add("on");
        dropoffFields.style.display = "block";

        // 🔥 Si detalle viene NULL, usamos un objeto vacío
        const det = drop.detalle ?? {};

        dropUbicacion.value = det.km ? "0" : "";
        dropKm.value = det.km ?? "";
        dropDireccion.value = det.destino ?? "";

        dropTotalHTML.textContent = `$${(Number(drop.monto) || 0).toFixed(2)} MXN`;

        const card = document.querySelector('.cargo-item[data-id="6"]');
        if (card) card.dataset.monto = drop.monto || 0;

    } catch (e) {
        console.log("Error restaurando dropoff", e);
    }
}


/* ==========================================================
   🔄 Restaurar estado de Gasolina Faltante al recargar
========================================================== */
async function restaurarEstadoGasolina() {
  try {
    const resp = await fetch(`/admin/contrato/cargos/${contratoID}`);
    const data = await resp.json();

    if (!data.success) return;

    const cargos = data.cargos;

    // Buscar si existe el concepto 5 (Gasolina faltante)
    const gas = cargos.find(c => c.id_concepto == 5);

    if (gas) {
      // Activar switch visualmente
      switchGasLit.classList.add("on");

      // Mostrar inputs
      gasInputs.style.display = "block";

      // Rellenar valores
      gasCant.value   = gas.detalle.litros ?? "";
      gasPrecio.value = gas.detalle.precio_litro ?? "";

      // Mostrar total
      gasTotalHTML.textContent = `$${(Number(gas.monto) || 0).toFixed(2)} MXN`;

      // Activar también Tanque incompleto
      activarCargoTanqueIncompleto();

      // Actualizar monto en la card
      const cardGas = document.querySelector('.cargo-item[data-id="5"]');
      if (cardGas) {
        cardGas.dataset.monto = gas.monto;
      }
    }

  } catch (err) {
    console.error("❌ Error restaurando gasolina:", err);
  }
}


/* ==========================================================
   🎧 EVENTOS Dropoff
========================================================== */

if (dropUbicacion) {
    dropUbicacion.addEventListener("change",  async () => {
        actualizarCamposDropoff();
        recalcularDropoff();
        guardarDropoff();
    });
}

if (dropKm) {
    dropKm.addEventListener("input",  async () => {
        recalcularDropoff();
        guardarDropoff();
    });
}

if (dropDireccion) {
    dropDireccion.addEventListener("input", () => {
        guardarDropoff();
    });
}




/* ==========================================================
   ▶ Inicializar
========================================================== */
restaurarEstadoGasolina();
restaurarSwitchGasolina();

restaurarEstadoDropoff();


recalcularTotalPaso4();






console.log("✔ Paso 4 listo (cambio vehículo + gasolina faltante)");

 /* ==========================================================
   🧾 PASO 5: Subida de documentación (CORREGIDO + CARGA INICIAL)
========================================================== */
const formDoc = document.querySelector("#formDocumentacion");

if (formDoc && typeof ID_CONTRATO !== "undefined" && ID_CONTRATO) {
  console.log("🧾 Iniciando manejo de documentación (Paso 5)...");

  // ==========================================================
  // 🔧 ADICIÓN NECESARIA → Asegurar la ruta correcta
  // ==========================================================
  if (!formDoc.action || formDoc.action.trim() === "") {
    formDoc.action = "/admin/contrato/guardar-documentacion";
  }

  // ==========================================================
  // 🔧 Alertify config
  // ==========================================================
  alertify.set("notifier", "position", "top-right");
  alertify.set("notifier", "delay", 3);

  // ==========================================================
  // 🧠 ESTADO INTERNO DEL PASO 5
  // ==========================================================
  let documentosGuardados = {
    titular: null,
    adicionales: {}
  };

  let docsCargadosActual = false;

  let adicionalesTotal = parseInt(formDoc.dataset.adicionales || "0", 10) || 0;
  let indiceActual = parseInt(formDoc.dataset.actual || "0", 10) || 0;
  let conductoresAdicionales = [];

  try {
    conductoresAdicionales = JSON.parse(formDoc.dataset.conductores || "[]");
  } catch (e) {
    console.warn("⚠️ dataset.conductores no es JSON válido:", e);
    conductoresAdicionales = [];
  }

  const alertaLicencia = document.getElementById("alertaLicencia");
  const confirmacionLicencia = document.getElementById("confirmacionLicencia");
  const inputIdConductor = document.querySelector("#id_conductor");
  const tituloPersona = document.querySelector("#tituloPersona");

  // ==========================================================
  // 🔹 Helpers visuales
  // ==========================================================
  function actualizarEstadoLicenciaVisual(licenciaVencida) {
    if (!alertaLicencia || !confirmacionLicencia) return;

    if (licenciaVencida === true) {
      alertaLicencia.style.display = "block";
      confirmacionLicencia.style.display = "none";
    } else if (licenciaVencida === false) {
      alertaLicencia.style.display = "none";
      confirmacionLicencia.style.display = "block";
    } else {
      alertaLicencia.style.display = "none";
      confirmacionLicencia.style.display = "none";
    }
  }

  function setValorInput(id, valor) {
    const el = document.getElementById(id);
    if (el && valor !== null && valor !== undefined && valor !== "") {
      el.value = valor;
    }
  }

  function limpiarFormularioYPreviews() {
    formDoc
      .querySelectorAll("input[type='text'], input[type='date'], input[type='file']")
      .forEach((i) => (i.value = ""));

    document
      .querySelectorAll(".preview")
      .forEach((div) => {
        div.innerHTML = "";
        div.removeAttribute("data-has-server-file");
      });

    docsCargadosActual = false;
    actualizarEstadoLicenciaVisual(null);
  }

  function crearPreviewDesdeURL(nombreCampo, url) {
    if (!url) return;

    const previewDiv = document.getElementById(`prev-${nombreCampo}`);
    if (!previewDiv) return;

    previewDiv.innerHTML = "";

    const thumb = document.createElement("div");
    thumb.classList.add("thumb");
    thumb.innerHTML = `
      <img src="${url}" alt="Vista previa">
      <button type="button" class="rm" title="Quitar">×</button>
    `;
    previewDiv.appendChild(thumb);
previewDiv.dataset.hasServerFile = "1";
docsCargadosActual = true; // ← ESTA ES LA LÍNEA CLAVE


    const btnRm = thumb.querySelector(".rm");
    if (btnRm) {
      btnRm.addEventListener("click", () => {
        previewDiv.innerHTML = "";
        previewDiv.removeAttribute("data-has-server-file");
        docsCargadosActual = false;
      });
    }
  }

  // ==========================================================
  // 📥 Aplicar datos al formulario
  // ==========================================================
  function aplicarDocumentacionEnFormulario(esTitular, idConductor) {
    docsCargadosActual = false;

    let dataDoc =
      esTitular
        ? documentosGuardados.titular
        : documentosGuardados.adicionales?.[String(idConductor)] || null;

    if (!dataDoc) {
      actualizarEstadoLicenciaVisual(null);
      return;
    }

    const campos = dataDoc.campos || {};
    const archivos = dataDoc.archivos || {};

    setValorInput("tipo_identificacion", campos.tipo_identificacion);
    setValorInput("numero_identificacion", campos.numero_identificacion);
    setValorInput("nombre", campos.nombre);
    setValorInput("apellido_paterno", campos.apellido_paterno);
    setValorInput("apellido_materno", campos.apellido_materno);
    setValorInput("fecha_nacimiento", campos.fecha_nacimiento);
    setValorInput("fecha_vencimiento_id", campos.fecha_vencimiento_id);

    setValorInput("contacto_emergencia", campos.contacto_emergencia);

    setValorInput("numero_licencia", campos.numero_licencia);
    setValorInput("emite_licencia", campos.emite_licencia);
    setValorInput("fecha_emision_licencia", campos.fecha_emision_licencia);
    setValorInput("fecha_vencimiento_licencia", campos.fecha_vencimiento_licencia);

    crearPreviewDesdeURL("idFrente", archivos.idFrente_url);
    crearPreviewDesdeURL("idReverso", archivos.idReverso_url);
    crearPreviewDesdeURL("licFrente", archivos.licFrente_url);
    crearPreviewDesdeURL("licReverso", archivos.licReverso_url);

    const licVencida = dataDoc.licencia_vencida === true;
    actualizarEstadoLicenciaVisual(licVencida);

    // ==========================================================
    // 🔧 CORRECCIÓN IMPORTANTE:
    // SI HAY ARCHIVOS EN EL BACKEND, docsCargadosActual = true
    // (aunque la licencia esté vencida)
    // ==========================================================
    if (
        archivos.idFrente_url ||
        archivos.idReverso_url ||
        archivos.licFrente_url ||
        archivos.licReverso_url
    ) {
        docsCargadosActual = true;
    }
}


  // ==========================================================
  // 🚀 Carga inicial desde backend
  // ==========================================================
  async function cargarDocumentacionInicial() {
    try {
      const resp = await fetch(
        `/admin/contrato/documentacion/${encodeURIComponent(ID_CONTRATO)}`
      );

      if (!resp.ok) return;

      const data = await resp.json();
      if (!data?.success || !data?.documentos) return;

      documentosGuardados.titular = data.documentos.titular || null;
      documentosGuardados.adicionales = data.documentos.adicionales || {};

      let esTitular = indiceActual === 0;
      let idConductorActual = null;

      if (!esTitular) {
        const idx = indiceActual - 1;
        const conductor = conductoresAdicionales[idx];

        if (conductor) {
          idConductorActual = conductor.id_conductor || conductor.id || null;
        } else {
          esTitular = true;
          indiceActual = 0;
          formDoc.dataset.actual = "0";
        }
      }

      if (esTitular) {
        tituloPersona.textContent = "Documentación del titular";
        inputIdConductor.value = "";
      } else {
        const idx = indiceActual - 1;
        const c = conductoresAdicionales[idx];
        const nombre = c?.nombres || `Conductor adicional #${indiceActual}`;
        const apellidos = c?.apellidos || "";

        tituloPersona.textContent = `Documentación de ${nombre} ${apellidos}`.trim();
        inputIdConductor.value = idConductorActual || "";
      }

      limpiarFormularioYPreviews();
      aplicarDocumentacionEnFormulario(esTitular, idConductorActual);

    } catch (e) {
      console.error("❌ Error cargarDocumentacionInicial:", e);
    }
  }

  cargarDocumentacionInicial();

  // ==========================================================
  // 📨 SUBMIT DEL FORMULARIO
  // ==========================================================
  formDoc.addEventListener("submit", async (e) => {
    e.preventDefault();

    const archivos = formDoc.querySelectorAll("input[type='file']");
    let tieneArchivo = false;

    archivos.forEach((a) => {
      if (a.files && a.files.length > 0) tieneArchivo = true;
    });

    if (!tieneArchivo && !docsCargadosActual) {
      alertify.warning("📁 Debes seleccionar al menos un archivo.");
      return;
    }

    const btnSubmit = formDoc.querySelector("button[type='submit']");
    if (btnSubmit) {
      btnSubmit.disabled = true;
      btnSubmit.textContent = "Subiendo... ⏳";
    }

    const formData = new FormData(formDoc);

    // ==========================================================
    // 🔧 ADICIÓN NECESARIA → Asegurar que SIEMPRE se envía id_contrato
    // ==========================================================
    formData.set("id_contrato", ID_CONTRATO);

    try {
      const resp = await fetch(formDoc.action, {
        method: "POST",
        headers: {
          "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').content
        },
        body: formData
      });

      const data = await resp.json();
      console.log("📡 Respuesta servidor:", data);

      if (data.warning) {
        alertaLicencia.style.display = "block";
        confirmacionLicencia.style.display = "none";
        alertify.warning("⚠️ La licencia está vencida.");

        btnSubmit.disabled = false;
        btnSubmit.textContent = "Guardar documentación";
        docsCargadosActual = false;
        return;
      }

      alertaLicencia.style.display = "none";
      confirmacionLicencia.style.display = "block";
      alertify.success("📄 Documentación guardada correctamente.");

      docsCargadosActual = true;

      const adicionales = adicionalesTotal;
      let actual = indiceActual;
      const conductores = conductoresAdicionales;

      if (actual < adicionales && conductores.length > 0) {
        actual++;
        indiceActual = actual;
        formDoc.dataset.actual = String(actual);

        const siguiente = conductores[actual - 1];
        const idReal = siguiente?.id_conductor || "";
        const nombre = siguiente?.nombres || `Conductor adicional #${actual}`;
        const apellidos = siguiente?.apellidos || "";

        tituloPersona.textContent = `Documentación de ${nombre} ${apellidos}`.trim();
        inputIdConductor.value = idReal;

        limpiarFormularioYPreviews();
        aplicarDocumentacionEnFormulario(false, idReal);

        alertify.message(`🧍‍♂️ Continúa con la documentación de ${nombre}`);

        btnSubmit.disabled = false;
        btnSubmit.textContent = "Guardar documentación";
        return;
      }

      // ==================================================
      // ✅ Fin de conductores → Paso 6
      // ==================================================
      alertify.success("🎉 Documentación completada. Avanzando al paso final…");

      if (typeof showStep === "function") {
        showStep(6);
      }

    } catch (err) {
      console.error("❌ Error subir documentación:", err);
      alertify.error("Error al enviar documentos.");
    } finally {
      if (btnSubmit) {
        btnSubmit.disabled = false;
        btnSubmit.textContent = "Guardar documentación";
      }
    }
  });
}

/* ==========================================================
   📸 Vista previa de archivos (INE / Licencia)
========================================================== */
document.querySelectorAll('.uploader input[type="file"]').forEach((input) => {
  input.addEventListener("change", (e) => {
    const file = e.target.files[0];
    const contenedor = e.target.closest(".uploader");
    const previewId = contenedor.getAttribute("data-name");
    const previewDiv = document.getElementById(`prev-${previewId}`);

    if (!file || !previewDiv) return;

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
      previewDiv.removeAttribute("data-has-server-file");

      thumb.querySelector(".rm").addEventListener("click", () => {
        e.target.value = "";
        thumb.remove();
      });
    };

    reader.readAsDataURL(file);
  });
});



/* ==========================================================
      🧾 PASO 6 — TOTAL DE LA RESERVACIÓN + PAGOS + PAYPAL
   ========================================================== */

console.log("🚀 Paso 6 inicializado…");

// UI totales
const baseAmt       = $("#baseAmt");
const baseDescr     = $("#baseDescr");
const addsAmt       = $("#addsAmt");
const ivaAmt        = $("#ivaAmt");
const ivaOnly       = $("#ivaOnly");
const totalContrato = $("#totalContrato");
const saldoPend     = $("#saldoPendiente");

// Tabla pagos + modal
const payBody   = $("#payBody");
const btnAdd    = $("#btnAdd");
const modalBack = $("#mb");
const modal     = document.querySelector("#mb .modal");
const mx        = $("#mx");
const pSave     = $("#pSave");

// Campos del modal
const pTipo  = $("#pTipo");
const pMonto = $("#pMonto");
const pNotes = $("#pNotes");

// Métodos (tarjeta / transferencia / etc.)
const metodoRadios = document.querySelectorAll("input[name='m']");

// Tabs
const payTabs = $("#payTabs");
const panes   = document.querySelectorAll("[data-pane]");

// PAYPAL
let paypalLoaded   = false;
let paypalInstance = null;
const paypalContainer = document.querySelector("#paypal-button-container-modal");

/* ==========================================================
   🔹 1. Cargar datos del Paso 6
========================================================== */
async function cargarPaso6() {

    // 🔄 Recapturar elementos AHORA que el Paso 6 ya está en el DOM
    const baseAmt       = document.querySelector("#baseAmt");
    const baseDescr     = document.querySelector("#baseDescr");
    const addsAmt       = document.querySelector("#addsAmt");
    const ivaAmt        = document.querySelector("#ivaAmt");
    const ivaOnly       = document.querySelector("#ivaOnly");
    const totalContrato = document.querySelector("#totalContrato");
    const saldoPend     = document.querySelector("#saldoPendiente");
    const payBody       = document.querySelector("#payBody");

    try {
        const resp = await fetch(`/admin/contrato/${ID_RESERVACION}/resumen-paso6`);
        const data = await resp.json();
        if (!data.ok) return;

        const r = data.data;

        // Desglose TOT REAL
        baseDescr.textContent     = r.base.descripcion ?? "—";
        baseAmt.textContent       = money(r.base.total);
        addsAmt.textContent       = money(r.adicionales.total);
        ivaAmt.textContent        = money(r.totales.subtotal);
        ivaOnly.textContent       = money(r.totales.iva);
        totalContrato.textContent = money(r.totales.total_contrato);
        saldoPend.textContent     = money(r.totales.saldo_pendiente);

        // Renderizar pagos
        renderPagos(r.pagos);

    } catch (e) {
        console.error("❌ Error Paso 6:", e);
    }
}


/* ==========================================================
   🔹 2. Renderizar tabla pagos
========================================================== */
function renderPagos(lista) {
    payBody.innerHTML = "";

    if (!lista || lista.length === 0) {
        payBody.innerHTML = `
            <tr><td colspan="6" style="text-align:center;color:#667085">
                NO EXISTEN PAGOS REGISTRADOS
            </td></tr>
        `;
        return;
    }

    lista.forEach((pago, idx) => {
        const tr = document.createElement("tr");
        tr.innerHTML = `
            <td>${idx + 1}</td>
            <td>${pago.fecha}</td>
            <td>${pago.tipo}</td>
            <td>${pago.origen}</td>
            <td><b>${money(pago.monto)}</b></td>
            <td><button class="btn small gray" data-del="${pago.id_pago}">✕</button></td>
        `;
        payBody.appendChild(tr);
    });
}

/* ==========================================================
   🔹 3. Abrir modal
========================================================== */
btnAdd?.addEventListener("click", () => {
    if (!modalBack) return;

    // Mostrar backdrop
    modalBack.classList.add("show");
    document.body.classList.add("modal-open");

    // Precargar monto (saldo pendiente o total)
    precargarMonto();

    // Tab inicial → PayPal
    activarTab("paypal");
});

/* ==========================================================
   🔹 4. Cerrar modal
========================================================== */
mx?.addEventListener("click", cerrarModalPago);

function cerrarModalPago() {
    if (!modalBack) return;

    modalBack.classList.remove("show");
    document.body.classList.remove("modal-open");

    // Limpiar campos
    if (pMonto) pMonto.value = "";
    if (pNotes) pNotes.value = "";

    // Limpiar PayPal
    if (paypalContainer) {
        paypalContainer.innerHTML = "";
    }
}

/* ==========================================================
   🔹 5. Tabs del modal
========================================================== */
payTabs?.addEventListener("click", (e) => {
    const tab = e.target;
    if (!tab.dataset.tab) return;
    if (tab.classList.contains("disabled")) return;

    activarTab(tab.dataset.tab);
});

function activarTab(nombre) {
    // Activar botón
    document.querySelectorAll("#payTabs .tab").forEach(t => {
        t.classList.toggle("active", t.dataset.tab === nombre);
    });

    // Mostrar pane correspondiente
    panes.forEach(p => {
        p.style.display = (p.dataset.pane === nombre) ? "block" : "none";
    });

    // Solo cuando es PayPal, pintamos el botón
    if (nombre === "paypal") {
        prepararPayPal();
    } else {
        // En cualquier otro tab, limpiamos el contenedor
        if (paypalContainer) {
            paypalContainer.innerHTML = "";
        }
    }
}

/* ==========================================================
   🔹 6. Preparar PAYPAL dentro del modal (solo tab PayPal)
========================================================== */
async function prepararPayPal() {
    if (!paypalContainer) return;

    try {
        // Cargar SDK una sola vez
        if (!paypalLoaded) {
            await cargarPayPalSDK();
            paypalLoaded = true;
        }

        // Limpiar contenedor cada vez
        paypalContainer.innerHTML = "";

        const monto = obtenerMontoPago(); // saldo pendiente o total

        // Guardamos instancia por si luego quieres manipularla
        paypalInstance = paypal.Buttons({
            style: { color: "gold", shape: "pill", label: "pay", height: 40 },

            createOrder: (data, actions) => {
                return actions.order.create({
                    purchase_units: [{
                        amount: {
                            value: monto.toFixed(2),
                            currency_code: "MXN"
                        },
                        description: "Pago de contrato — Viajero Car Rental"
                    }]
                });
            },

            onApprove: async (data, actions) => {
                const order = await actions.order.capture();

                await registrarPagoPayPal(order.id, monto);

                cerrarModalPago();
                cargarPaso6();
                await cargarResumenBasico();

            },

            onError: (err) => {
                console.error("⚠️ Error PayPal:", err);
                alert("Error al procesar PayPal");
            },

            onCancel: () => {
                alert("Pago cancelado");
            }

        });

        paypalInstance.render("#paypal-button-container-modal");

    } catch (e) {
        console.error("❌ Error preparando PayPal:", e);
    }
}

/* ==========================================================
   🔹 Cargar SDK PayPal
========================================================== */
function cargarPayPalSDK() {
    return new Promise((resolve, reject) => {
        if (window.paypal) return resolve();

        const script = document.createElement("script");
        script.src =
            "https://www.paypal.com/sdk/js?client-id=ATzNpaAJlH7dFrWKu91xLmCzYVDQQF5DJ51b0OFICqchae6n8Pq7XkfsOOQNnElIJMt_Aj0GEZeIkFsp&currency=MXN";
        script.onload  = () => resolve();
        script.onerror = () => reject(new Error("No se pudo cargar PayPal SDK"));
        document.head.appendChild(script);
    });
}

/* ==========================================================
   🔹 Registrar pago PayPal en backend
========================================================== */
/* ==========================================================
   🔹 Registrar pago PayPal en backend (CORREGIDO)
========================================================== */
async function registrarPagoPayPal(order_id, monto) {
    try {

        // Tomar CSRF desde tu <meta> del layout
        const csrf = document.querySelector('meta[name="csrf-token"]').content;

        const resp = await fetch(`/admin/contrato/pagos/paypal`, {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
                "X-CSRF-TOKEN": csrf
            },
            body: JSON.stringify({
                id_reservacion: ID_RESERVACION,
                order_id,
                monto
            })
        });

        // Revisar si la respuesta realmente es JSON
        const text = await resp.text();
        console.log("RESPUESTA PAYPAL RAW:", text);

        let data;
        try {
            data = JSON.parse(text);
        } catch (e) {
            console.error("⚠️ El backend regresó HTML en lugar de JSON:", text);
            alert("Error inesperado procesando el pago. Revisa consola.");
            return;
        }

        if (!data.ok) {
            alert("Error registrando pago PayPal");
            console.error(data);
            return;
        }

        console.log("PAYPAL registrado correctamente:", data);

    } catch (e) {
        console.error("❌ Error registrando pago PayPal:", e);
        alert("Error al registrar pago PayPal");
    }
}


/* ==========================================================
   🔹 Obtener monto a pagar
   - Si hay saldo pendiente → se cobra eso.
   - Si saldo = 0 → se toma el total del contrato.
========================================================== */
function obtenerMontoPago() {
    const saldo = parseFloat(
        (saldoPend?.textContent || "").replace(/[^\d.]/g, "")
    ) || 0;

    if (saldo > 0) return saldo;

    const total = parseFloat(
        (totalContrato?.textContent || "").replace(/[^\d.]/g, "")
    ) || 0;

    return total;
}

/* ==========================================================
   🔹 Precargar input pMonto con el monto a cobrar
========================================================== */
function precargarMonto() {
    if (!pMonto) return;
    const monto = obtenerMontoPago();
    pMonto.value = monto.toFixed(2);
}

/* ==========================================================
   🔹 7. Guardar pago manual (terminal, efectivo, transferencia)
========================================================== */
pSave?.addEventListener("click", async () => {
    try {
        const metodo = document.querySelector("[name='m']:checked")?.value || null;

        // VALIDAR MÉTODO PARA LOS TABS QUE LO REQUIEREN
        const tabActiva = document.querySelector("#payTabs .tab.active")?.dataset.tab;

        // Campos de archivo según pestaña
        let fileInput = null;
        if (tabActiva === "tarjeta") fileInput = document.getElementById("fileTerminal");
        if (tabActiva === "transferencia") fileInput = document.getElementById("fileTransfer");

        // VALIDAR COMPROBANTE SI ES NECESARIO
        if ((tabActiva === "tarjeta" || tabActiva === "transferencia") && !fileInput?.files[0]) {
            $("#pErr").text("Debes subir el comprobante.");
            return;
        }

        // Construir FormData (admite archivos)
        const fd = new FormData();
        fd.append("id_reservacion", ID_RESERVACION);
        fd.append("tipo_pago", pTipo?.value || "PAGO RESERVACIÓN");
        fd.append("monto", pMonto?.value || 0);
        fd.append("notas", pNotes?.value || "");
        fd.append("metodo", metodo);

        if (fileInput?.files[0]) {
            fd.append("comprobante", fileInput.files[0]);
        }

        // AGREGAR CSRF TOKEN
        fd.append("_token", document.querySelector('meta[name="csrf-token"]').content);

        const resp = await fetch(`/admin/contrato/pagos/agregar`, {
            method: "POST",
            body: fd
        });

        // Intentar leer JSON, pero sin explotar si devuelve HTML
        let data = {};
        try {
            data = await resp.json();
        } catch (err) {
            console.error("⚠️ La respuesta NO es JSON. Probablemente error 419/500 HTML.");
            $("#pErr").text("Error inesperado al guardar el pago.");
            return;
        }

        if (!data.ok) {
            $("#pErr").text(data.msg || "Error al guardar el pago.");
            return;
        }

        cerrarModalPago();
        cargarPaso6();
        await cargarResumenBasico();

    } catch (e) {
        console.error("❌ Error guardando pago manual:", e);
        $("#pErr").text("Error al guardar el pago.");
    }
});


/* ==========================================================
   🔹 8. Eliminar pago
========================================================== */
payBody?.addEventListener("click", async (e) => {
    const btn = e.target.closest("[data-del]");
    if (!btn) return;

    const id_pago = btn.dataset.del;
    if (!confirm("¿Eliminar este pago?")) return;

    await fetch(`/admin/contrato/pagos/${id_pago}/eliminar`, { method: "DELETE" });
    cargarPaso6();
    await cargarResumenBasico();



});

/* ==========================================================
   🔹 Helper de formato
========================================================== */
function money(num) {
    num = parseFloat(num || 0);
    return num.toLocaleString("es-MX", {
        style: "currency",
        currency: "MXN",
        minimumFractionDigits: 2
    });
}
/* ==========================================================
   ✏️ EDICIÓN DE TARIFA BASE
========================================================== */

const btnEditarTarifa = document.getElementById("btnEditarTarifa");
const rBasePrecio     = document.getElementById("r_base_precio");

btnEditarTarifa?.addEventListener("click", () => {
    if (!rBasePrecio) return;

    // Si ya existe input, evitar duplicarlo
    if (rBasePrecio.querySelector("input")) return;

    const valorActual = parseFloat(rBasePrecio.textContent.replace(/[^\d.]/g, "")) || 0;

    const input = document.createElement("input");
    input.type = "number";
    input.value = valorActual.toFixed(2);
    input.min = 0;
    input.step = 0.01;
    input.style.width = "90px";
    input.style.padding = "4px";
    input.style.border = "1px solid #ccc";
    input.style.borderRadius = "6px";

    rBasePrecio.textContent = "";
    rBasePrecio.appendChild(input);
    input.focus();

    input.addEventListener("blur", () => guardarTarifaModificada(input.value));
    input.addEventListener("keydown", e => {
        if (e.key === "Enter") input.blur();
    });
});


async function guardarTarifaModificada(nuevoValor) {
    try {
        const resp = await fetch(`/admin/contrato/${ID_RESERVACION}/editar-tarifa`, {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
                "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').content
            },
            body: JSON.stringify({ tarifa_modificada: nuevoValor })
        });

        if (!resp.ok) throw new Error("Error al guardar tarifa modificada");

        console.log("Tarifa modificada guardada correctamente.");
        cargarResumenBasico();
        cargarPaso6();

    } catch (e) {
        console.error("❌ Error al actualizar tarifa:", e);
    }
}




/* ==========================================================
   ✏️ EDICIÓN DE HORAS DE CORTESÍA
========================================================== */

const btnEditarCortesia   = document.getElementById("btnEditarCortesia");
const editorCortesia      = document.getElementById("editorCortesia");
const inputCortesia       = document.getElementById("inputCortesia");
const btnGuardarCortesia  = document.getElementById("btnGuardarCortesia");
const btnCancelarCortesia = document.getElementById("btnCancelarCortesia");
const rCortesia           = document.getElementById("r_cortesia");


btnEditarCortesia?.addEventListener("click", () => {
    editorCortesia.style.display = "block";
    inputCortesia.value = rCortesia.textContent || "1";
});

btnCancelarCortesia?.addEventListener("click", () => {
    editorCortesia.style.display = "none";
});


btnGuardarCortesia?.addEventListener("click", async () => {
    const horas = parseInt(inputCortesia.value);

    if (![1, 2, 3].includes(horas)) {
        alert("Horas permitidas: 1, 2 o 3");
        return;
    }

    try {
        const resp = await fetch(`/admin/contrato/${ID_RESERVACION}/editar-cortesia`, {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
                "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').content
            },
            body: JSON.stringify({ horas_cortesia: horas })
        });

        if (!resp.ok) throw new Error("Error al guardar horas de cortesía");

        rCortesia.textContent = horas;
        editorCortesia.style.display = "none";

        cargarPaso6();
        cargarResumenBasico();

        console.log("Horas de cortesía actualizadas correctamente.");

    } catch (e) {
        console.error("❌ Error al actualizar cortesía:", e);
        alert("Error al actualizar cortesía");
    }
});
/* ==========================================================
   🚨 ALERTA FINALIZAR CONTRATO
========================================================== */

const formFinalizar = document.getElementById("formFinalizar");

formFinalizar?.addEventListener("submit", (e) => {
    e.preventDefault(); // detenemos envío para mostrar alerta

    // Mensaje según estado
    fetch(`/admin/contrato/${ID_RESERVACION}/status`)
        .then(res => res.json())
        .then(data => {

            if (data.existe) {
                alertify.message("Continuando contrato existente");
            } else {
                alertify.success("Contrato abierto correctamente");
            }

            // Enviar formulario después de la alerta (0.8 segundos)
            setTimeout(() => formFinalizar.submit(), 800);
        })
        .catch(() => {
            // Si falla la consulta, solo enviamos el form
            formFinalizar.submit();
        });
});


/* ==========================================================
   🚀 Inicializar
========================================================== */
cargarPaso6();




//resumen detalle toggle

/* ==========================================================
   📌 LEER ID_RESERVACION DESDE EL HTML
========================================================== */
const contratoInicial = document.getElementById('contratoInicial');

if (contratoInicial) {
    window.ID_RESERVACION = contratoInicial.dataset.idReservacion;
    console.log("📌 ID_RESERVACION cargado:", window.ID_RESERVACION);
} else {
    console.error("❌ ERROR: No existe #contratoInicial en el HTML");
}


/* ==========================================================
   📌 1) TOGGLE RESUMEN
========================================================== */

const btnVerDetalle     = document.querySelector("#btnVerDetalle");
const btnOcultarDetalle = document.querySelector("#btnOcultarDetalle");
const resumenCompacto   = document.querySelector("#resumenCompacto");
const resumenDetalle    = document.querySelector("#resumenDetalle");

console.log("🔎 Verificando elementos resumen:", {
    btnVerDetalle,
    btnOcultarDetalle,
    resumenCompacto,
    resumenDetalle
});

if (!btnVerDetalle || !btnOcultarDetalle || !resumenCompacto || !resumenDetalle) {
    console.error("❌ No se encontraron elementos del resumen.");
} else {

    btnVerDetalle.addEventListener("click", (e) => {
        e.preventDefault();
        resumenCompacto.style.display = "none";
        resumenDetalle.style.display = "block";
        console.log("📂 Resumen expandido.");
    });

    btnOcultarDetalle.addEventListener("click", (e) => {
        e.preventDefault();
        resumenDetalle.style.display = "none";
        resumenCompacto.style.display = "block";
        console.log("📁 Resumen colapsado.");
    });
}


/* ==========================================================
   📌 2) CARGAR RESUMEN BÁSICO + VEHÍCULO
========================================================== */

async function cargarResumenBasico() {

    if (!window.ID_RESERVACION) {
        console.warn("❌ No existe ID_RESERVACION en el HTML");
        return;
    }

    try {
        const resp = await fetch(`/admin/contrato/${ID_RESERVACION}/resumen`);
        const json = await resp.json();

        if (!json.success) {
            console.warn("⚠ No se pudo obtener el resumen:", json.msg);
            return;
        }

        const r = json.data;

        // -------------------------------------
        // 🔹 CLIENTE (TU CÓDIGO ORIGINAL)
        // -------------------------------------
        document.querySelector("#detCodigo").textContent = r.codigo ?? "—";

        if (r.cliente) {
            document.querySelector("#detCliente").textContent  = r.cliente.nombre   ?? "—";
            document.querySelector("#detTelefono").textContent = r.cliente.telefono ?? "—";
            document.querySelector("#detEmail").textContent    = r.cliente.email    ?? "—";
        }

        // -------------------------------------------------
        // ⭐ AGREGADO: CARGAR DATOS DEL VEHÍCULO EN EL HTML
        // -------------------------------------------------
        if (r.vehiculo) {
            document.querySelector("#detModelo").textContent      = r.vehiculo.modelo ?? "—";
            document.querySelector("#detMarca").textContent       = r.vehiculo.marca ?? "—";
            document.querySelector("#detCategoria").textContent   = r.vehiculo.categoria ?? "—";
            document.querySelector("#detTransmision").textContent = r.vehiculo.transmision ?? "—";
            document.querySelector("#detPasajeros").textContent   = r.vehiculo.pasajeros ?? "—";
            document.querySelector("#detPuertas").textContent     = r.vehiculo.puertas ?? "—";
            document.querySelector("#detKm").textContent          = r.vehiculo.km ?? "—";
        }

        // ⭐ AGREGADO: FECHAS + HORARIOS
        if (r.fechas) {
            document.querySelector("#detFechaSalida").textContent  = r.fechas.inicio ?? "—";
            document.querySelector("#detHoraSalida").textContent   = r.fechas.hora_inicio ?? "—";

            document.querySelector("#detFechaEntrega").textContent = r.fechas.fin ?? "—";
            document.querySelector("#detHoraEntrega").textContent  = r.fechas.hora_fin ?? "—";

            document.querySelector("#detDiasRenta").textContent    = r.fechas.dias ?? "—";
        }

        /* ==========================================================
   📌 RESUMEN COMPACTO (Mini resumen)
========================================================== */

if (r.vehiculo) {
    // Vehículo compacto
    document.querySelector("#resumenVehCompacto").textContent =
        `${r.vehiculo.marca ?? ''} ${r.vehiculo.modelo ?? ''}`.trim() || "—";

    document.querySelector("#resumenCategoriaCompacto").textContent =
        `Categoría: ${r.vehiculo.categoria ?? "—"}`;
}

if (r.fechas) {
    document.querySelector("#resumenDiasCompacto").textContent =
        `Días de renta: ${r.fechas.dias ?? "—"}`;

    document.querySelector("#resumenFechasCompacto").textContent =
        `${r.fechas.inicio ?? "—"} / ${r.fechas.fin ?? "—"}`;
}

// TOTAL COMPACTO
if (r.totales) {
    document.querySelector("#resumenTotalCompacto").textContent =
        `$${r.totales.total ?? 0} MXN`;
}

// Mini–imagen del vehículo (si luego subes imagen)
const img = document.querySelector("#resumenImgVeh");
if (img && r.vehiculo && r.vehiculo.imagen) {
    img.src = r.vehiculo.imagen;
}

        /* ==========================================================
   🛡️  SEGUROS (PAQUETE O INDIVIDUALES)
========================================================== */

const listaSeguros = document.querySelector("#r_seguros_lista");
const totalSeguros = document.querySelector("#r_seguros_total");

// limpiar lista
listaSeguros.innerHTML = "";

if (r.seguros && r.seguros.tipo) {

    if (r.seguros.tipo === "paquete") {

        const item = document.createElement("li");
        const s = r.seguros.lista[0];
        item.textContent = `${s.nombre} — $${s.precio}`;
        listaSeguros.appendChild(item);

    } else if (r.seguros.tipo === "individuales") {

        r.seguros.lista.forEach(s => {
            const item = document.createElement("li");
            item.textContent = `${s.nombre} — $${s.precio}`;
            listaSeguros.appendChild(item);
        });
    }

    totalSeguros.textContent = `$${r.seguros.total} MXN`;

} else {
    // ninguno
    const vacio = document.createElement("li");
    vacio.classList.add("empty");
    vacio.textContent = "—";
    listaSeguros.appendChild(vacio);
    totalSeguros.textContent = "—";
}
if (r.servicios && r.servicios.length > 0) {
    const ul = document.querySelector("#r_servicios_lista");
    ul.innerHTML = "";

    r.servicios.forEach(s => {
        const li = document.createElement("li");
        li.textContent = `${s.nombre} (x${s.cantidad}) — $${s.precio} por día`;
        ul.appendChild(li);
    });
}
document.querySelector("#r_servicios_total").textContent =
    `$${r.servicios.reduce((sum, s) => sum + s.total, 0)} MXN`;

if (r.cargos && r.cargos.length > 0) {
    const ul = document.querySelector("#r_cargos_lista");
    ul.innerHTML = "";

    r.cargos.forEach(c => {
        let txt = `${c.nombre} — $${c.total}`;
        if (c.km) txt += ` (${c.km} km)`;
        ul.innerHTML += `<li>${txt}</li>`;
    });
    if (c.litros) txt += ` (${c.litros} L)`;

}

/* ==========================================================
   💵 TOTAL DESGLOSADO
========================================================== */
if (r.totales) {
    document.querySelector("#r_base_precio").textContent =
    `$${r.totales.tarifa_modificada ?? r.totales.tarifa_base}`;
    document.querySelector("#r_subtotal").textContent    = `$${r.totales.subtotal}`;
    document.querySelector("#r_iva").textContent         = `$${r.totales.iva}`;
    document.querySelector("#r_total_final").textContent = `$${r.totales.total}`;
}

/* ==========================================================
   💳 PAGOS Y SALDO
========================================================== */
if (r.pagos) {
    document.querySelector("#detPagos").textContent = `$${r.pagos.realizados}`;
    document.querySelector("#detSaldo").textContent = `$${r.pagos.saldo}`;
}




        console.log("✔ Resumen básico cargado.");

    } catch (error) {
        console.error("❌ Error cargando resumen básico:", error);
    }
}

if (document.readyState === "loading") {
    document.addEventListener("DOMContentLoaded", cargarResumenBasico);
} else {
    cargarResumenBasico();
}

const btnSaltarDoc = document.getElementById("btnSaltarDoc");

if (btnSaltarDoc) {

  btnSaltarDoc.addEventListener("click", async () => {

    if (!ID_CONTRATO) {
      alertify.error("ID del contrato no encontrado.");
      return;
    }

    alertify.message("Verificando documentación existente...");

    try {
      const resp = await fetch(`/admin/contrato/${ID_CONTRATO}/documentos-existen`);
      const data = await resp.json();

      if (!data.success) {
        alertify.error("Error verificando documentos.");
        return;
      }

      if (!data.existen) {
        alertify.warning("⚠️ No hay documentación guardada. Debes subir los documentos.");
        return;
      }

      // ===================================
      // 🎯 SI EXISTEN, AVANZAMOS AL PASO 6
      // ===================================
      alertify.success("✔ Documentación encontrada. Avanzando…");

      if (typeof showStep === "function") {
        showStep(6);
      }

    } catch (e) {
      console.error("Error:", e);
      alertify.error("No se pudo verificar.");
    }
  });

}









  /* ==========================================================
     🚀 Navegación entre pasos
  ========================================================== */
/* ==========================================================
   🚀 PASO 2: Verificar Upgrade y mostrar modal
========================================================== */
$("#go2")?.addEventListener("click", async () => {
  console.log("➡️ Verificando upgrade...");

  try {
      // 🔍 1. Pedimos al backend si hay upgrade disponible
      const resp = await fetch(`/admin/contrato/${ID_RESERVACION}/oferta-upgrade`);
      const data = await resp.json();

      // ❌ Si no hay upgrade → sigue normal al paso 2
      if (!data.success || !data.categoria) {
          showStep(2);
          return;
      }

      // 📌 2. Tomamos la categoría sugerida por backend
      const codigoCategoria = data.categoria.codigo;

      // 🔥 3. Usamos la función completa que arma TODO:
      //     - Foto
      //     - Specs
      //     - Precios inflado/real
      //     - Descuento
      //     - Nombre
      //     - Vehículo random
      const oferta = await construirOfertaCategoria(codigoCategoria);

      if (!oferta) {
          console.warn("⚠ No se pudo construir la oferta.");
          showStep(2);
          return;
      }

      // 🟦 4. Mostrar modal perfectamente armado
      mostrarModalOferta(oferta);

  } catch (e) {
      console.error("❌ Error verificando upgrade:", e);
      showStep(2);
  }
});




  $("#go3")?.addEventListener("click", () => {
    guardarDelivery();   // ← AGREGA ESTA LÍNEA
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
    cargarPaso6();
    cargarResumenBasico();
     setTimeout(() => {
        cargarPaso6();
        cargarResumenBasico();
    }, 150); // 150ms es perfecto
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

