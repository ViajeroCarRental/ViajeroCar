// Contrato
// Pasos 1 al 3

document.addEventListener("DOMContentLoaded", () => {
    console.log("✅ DOM listo, iniciando navegación de pasos (1-3)...");

    let intervaloAprobacion = null;

    const ORDEN_CATEGORIAS = ["C", "D", "E", "F", "IC", "I", "IB", "M", "L", "H", "HI"];

    // Construcción de la oferta desde DB
    async function construirOfertaCategoria(codigoCategoria) {
        if (!codigoCategoria) return null;
        try {
            const resp = await fetch(`/admin/contrato/categoria-info/${codigoCategoria}`);
            const data = await resp.json();
            if (!data.success || !data.categoria) return null;

            const cat = data.categoria;
            const respVeh = await fetch(`/admin/contrato/vehiculo-random/${cat.id_categoria}`);
            const dataVeh = await respVeh.json();

            let veh = null;
            if (dataVeh.success && dataVeh.vehiculo) veh = dataVeh.vehiculo;

            const precioReal = Number(cat.precio_dia);
            const precioInflado = Math.round(precioReal * 1.35);
            const descuento = Math.round(((precioInflado - precioReal) / precioInflado) * 100);

            return {
                id_categoria: cat.id_categoria,
                codigo: cat.codigo,
                nombre: cat.nombre,
                descripcion: cat.descripcion,
                precioReal,
                precioInflado,
                descuento,
                imagen: veh?.foto_url ?? "/img/default-car.jpg",
                nombre_vehiculo: veh?.nombre_publico ?? cat.nombre,
                transmision: veh?.transmision ?? null,
                asientos: veh?.asientos ?? null,
                puertas: veh?.puertas ?? null,
                color: veh?.color ?? null
            };
        } catch (err) { console.error("❌ Error obteniendo categoría/vehículo:", err); return null; }
    }

    // Modal de Ungrade
    function mostrarModalOferta(oferta) {
        const modal = document.getElementById("modalUpgrade");
        document.getElementById("upgTitulo").textContent = oferta.nombre;
        document.getElementById("upgPrecioInflado").textContent = `$${oferta.precioInflado}`;
        document.getElementById("upgPrecioReal").textContent = `$${oferta.precioReal}`;
        document.getElementById("upgDescuento").textContent = `${oferta.descuento}% de descuento`;
        document.getElementById("upgDescripcion").textContent = oferta.descripcion;
        document.getElementById("upgImagenVehiculo").src = oferta.imagen || "/img/default-car.jpg";
        document.getElementById("upgNombreVehiculo").textContent = oferta.nombre_vehiculo ?? oferta.nombre;

        document.getElementById("upgSpecs").innerHTML = `
            <div>${oferta.transmision ?? "—"}</div>
            <div>${oferta.asientos ?? "--"} asientos</div>
            <div>${oferta.puertas ?? "--"} puertas</div>
            <div>${oferta.color ?? "—"}</div>
        `;

        modal.dataset.idCategoriaUpgrade = oferta.id_categoria;
        modal.classList.add("show");
    }

    async function aceptarUpgrade() {
        const modal = document.getElementById("modalUpgrade");
        const nuevaCategoria = modal.dataset.idCategoriaUpgrade;

        const btn = document.getElementById("btnAceptarUpgrade");
        btn.disabled = true;
        btn.innerHTML = "Aplicando...";

        try {
            const resp = await fetch(`/admin/contrato/${window.ID_RESERVACION}/actualizar-categoria`, {
                method: "POST",
                headers: { "Content-Type": "application/json", "X-CSRF-TOKEN": window.csrfToken },
                body: JSON.stringify({ id_categoria: nuevaCategoria }),
            });

            const result = await resp.json();

            if (result.success) {
                if (typeof window.cargarResumenBasico === 'function') {
                    await window.cargarResumenBasico();
                }

                alertify.success("Upgrade aplicado.");
                modal.classList.remove("show");

                // Pasamos al Paso 2 de inmediato
                window.showStep(2);
            }
        } catch (e) {
            alertify.error("Error en la conexión.");
            btn.disabled = false;
            btn.innerHTML = "Aceptar upgrade";
        }
    }

    document.getElementById("btnAceptarUpgrade")?.addEventListener("click", aceptarUpgrade);
    document.getElementById("btnRechazarUpgrade")?.addEventListener("click", () => {
        document.getElementById("modalUpgrade").classList.remove("show");
        window.showStep(2);
    });
    document.getElementById("cerrarUpgrade")?.addEventListener("click", () => {
        document.getElementById("modalUpgrade").classList.remove("show");
    });

    // sincronización de catgeorias
    const selectCategoriaOutside = document.getElementById("selectCategoria");
    const selectCategoriaModal = document.getElementById("selectCategoriaModal");
    let categoriaActual = selectCategoriaOutside?.value || null;

    function sincronizarCategoriaModal() {
        if (selectCategoriaModal) selectCategoriaModal.value = categoriaActual;
    }

    // suma de servicio incluyendo el delivery
    function actualizarTotal() {
        let total = 0;
        document.querySelectorAll(".card-servicio").forEach((card) => {
            const precio = parseFloat(card.dataset.precio || 0);
            const cantidad = parseInt(card.querySelector(".cantidad").textContent || 0);
            total += precio * cantidad;
        });

        if (window.deliveryTotalActual) total += window.deliveryTotalActual;
        const totalServicios = document.querySelector("#total_servicios");
        if (totalServicios) totalServicios.textContent = `$${total.toFixed(2)} MXN`;
    }

    // Datos del paso 1
    function guardarDatosPaso1() {
        const datos = {
            codigo: window.$("#codigo")?.textContent.trim() || "",
            titular: window.$("#clienteNombre")?.textContent.trim() || "",
            sucursalEntrega: window.$(".bloque.entrega .lugar")?.textContent.trim() || "",
            sucursalDevolucion: window.$(".bloque.devolucion .lugar")?.textContent.trim() || "",
            fechaEntrega: window.$(".fecha-entrega-display")?.innerText.trim() || "",
            fechaDevolucion: window.$(".fecha-devolucion-display")?.innerText.trim() || "",
            horaEntrega: window.$(".bloque.entrega .hora")?.innerText.trim() || "",
            horaDevolucion: window.$(".bloque.devolucion .hora")?.innerText.trim() || "",
            telefono: window.$("#clienteTel")?.textContent.trim() || "",
            email: window.$("#clienteEmail")?.textContent.trim() || "",
            duracion: window.$("#diasBadge")?.textContent.trim() || "",
            total: window.$("#totalReserva")?.textContent.trim() || "",
        };
        sessionStorage.setItem("contratoPaso1", JSON.stringify(datos));
    }

    (function detectarReservaNueva() {
        const codigoActual = window.$("#codigo")?.textContent.trim();
        const datosGuardados = JSON.parse(sessionStorage.getItem("contratoPaso1") || "{}");

        if (!datosGuardados.codigo || datosGuardados.codigo !== codigoActual) {
            sessionStorage.clear();
            const aviso = document.createElement("div");
            aviso.textContent = "🔄 Datos actualizados";
            aviso.style.cssText = `position:fixed; top:20px; right:20px; background:#10b981; color:white; padding:10px 16px; border-radius:8px; box-shadow:0 4px 10px rgba(0,0,0,0.25); z-index:9999; font-weight:bold; transition:opacity .6s;`;
            document.body.appendChild(aviso);
            setTimeout(() => (aviso.style.opacity = "0"), 1800);
            setTimeout(() => aviso.remove(), 2500);
        }
        setTimeout(guardarDatosPaso1, 300);
    })();

    // Eventos del paso 1
    function obtenerHoraActual() {
        return new Date().toLocaleTimeString("en-GB", { hour: "2-digit", minute: "2-digit" });
    }

    (function inicializarPaso1() {
        const lblHoraEntrega = window.$(".bloque.entrega .hora");
        if (lblHoraEntrega) lblHoraEntrega.textContent = obtenerHoraActual();

        window.$$(".fecha-entrega-edit").forEach((btn) => {
            btn.addEventListener("click", () => {
                const cont = window.$(".fecha-edicion-entrega");
                if (!cont) return;
                cont.style.display = "block";
                window.$("#nuevaFechaEntrega").disabled = false;
                window.$("#nuevaFechaEntrega").value = document.getElementById("contratoInicial")?.dataset.inicio || "";
                window.$("#nuevaHoraEntrega").disabled = false;
                window.$("#nuevaHoraEntrega").value = obtenerHoraActual();
                window.$("#btnSolicitarCambioEntrega").style.display = "inline-flex";
            });
        });

        window.$$(".fecha-devolucion-edit").forEach((btn) => {
            btn.addEventListener("click", () => {
                const cont = window.$(".fecha-edicion-devolucion");
                if (cont) cont.style.display = "block";
                window.$("#nuevaFechaDevolucion").value = document.getElementById("contratoInicial")?.dataset.fin || "";
                window.$("#nuevaHoraDevolucion").value = document.getElementById("contratoInicial")?.dataset.horaEntrega || "";
            });
        });

        window.$("#btnGuardarFechaDevolucion")?.addEventListener("click", async () => {
            await actualizarFechasYRecalcular();
            window.$(".fecha-edicion-devolucion").style.display = "none";
            setTimeout(() => window.cargarResumenBasico(), 150);
        });

        // cambio de categoria
        selectCategoriaOutside?.addEventListener("change", async (e) => {
            const nuevaCat = e.target.value;
            categoriaActual = nuevaCat;
            if (selectCategoriaModal) selectCategoriaModal.value = nuevaCat;

            window.ContratoStore.set('categoriaElegida', nuevaCat);

            try {
                await fetch(`/admin/contrato/${window.ID_RESERVACION}/actualizar-categoria`, {
                    method: "POST",
                    headers: { "Content-Type": "application/json", "X-CSRF-TOKEN": window.csrfToken },
                    body: JSON.stringify({ id_categoria: nuevaCat }),
                });
                alertify.success("Categoría actualizada.");

                await actualizarFechasYRecalcular();
                setTimeout(() => window.cargarResumenBasico(), 150);
            } catch (err) { console.error("❌ Error actualizando categoría desde afuera", err); }
        });

        window.$("#btnElegirVehiculo")?.addEventListener("click", abrirModalVehiculos);
        window.$("#cerrarModalVehiculos")?.addEventListener("click", cerrarModalVehiculos);
        window.$("#cerrarModalVehiculos2")?.addEventListener("click", cerrarModalVehiculos);
    })();

    // Recalcular y Actualizar reservacion
    async function actualizarFechasYRecalcular() {
        const cIni = document.getElementById("contratoInicial");
        if (!cIni) return;

        const fechaInicio = window.$("#nuevaFechaEntrega")?.value || cIni.dataset.inicio || "";
        const horaInicio = window.$("#nuevaHoraEntrega")?.value || cIni.dataset.horaRetiro || "";
        const fechaFin = window.$("#nuevaFechaDevolucion")?.value || cIni.dataset.fin || "";
        const horaFin = window.$("#nuevaHoraDevolucion")?.value || cIni.dataset.horaEntrega || "";

        const payloadEnvio = {
            fecha_inicio: fechaInicio,
            hora_inicio: horaInicio,
            fecha_fin: fechaFin,
            hora_fin: horaFin,
            id_categoria: categoriaActual
        };

        console.log("🚀 Enviando payload a recalcular:", payloadEnvio);

        if (!fechaInicio || !fechaFin || !categoriaActual) return;

        try {
            const resp = await fetch(`/admin/contrato/${window.ID_RESERVACION}/recalcular-total`, {
                method: "POST",
                headers: { "Content-Type": "application/json", "X-CSRF-TOKEN": window.csrfToken },
                body: JSON.stringify(payloadEnvio),
            });

            if (!resp.ok) throw new Error(`Error de servidor: ${resp.status}`);

            const data = await resp.json();
            console.log("✅ Respuesta del backend (recalcular):", data);

            // Buscamos el total inteligente (por si Laravel le llama diferente)
            let totalReal = data.total_formateado || data.total || data.total_renta || data.monto || 0;

            // Actualizamos la tarjeta de Total Reservado en el Paso 1
            if (window.$("#diasBadge")) window.$("#diasBadge").textContent = `${data.dias ?? 0} días`;
            if (window.$("#totalReserva")) window.$("#totalReserva").textContent = window.money(totalReal);

            // Actualizar variables en el DOM
            cIni.dataset.fin = fechaFin;
            cIni.dataset.horaEntrega = horaFin;

            const partes = fechaFin.split("-");
            if (partes.length === 3) {
                if (window.$(".fecha-devolucion-display .dia")) window.$(".fecha-devolucion-display .dia").textContent = partes[2];
                if (window.$(".fecha-devolucion-display .mes")) window.$(".fecha-devolucion-display .mes").textContent = new Date(fechaFin + "T00:00:00").toLocaleString("es-MX", { month: "short" }).toUpperCase();
                if (window.$(".fecha-devolucion-display .anio")) window.$(".fecha-devolucion-display .anio").textContent = partes[0];
            }
            if (window.$(".bloque.devolucion .hora")) window.$(".bloque.devolucion .hora").textContent = horaFin;

            if (typeof guardarDatosPaso1 === 'function') guardarDatosPaso1();

        } catch (err) { console.error("❌ Error recalculando total reservado:", err); }
    }

    // Saber el cambio en el paso 4
    window.$("#btnSolicitarCambioEntrega")?.addEventListener("click", enviarSolicitudCambioEntrega);

    async function enviarSolicitudCambioEntrega() {
        const nuevaFecha = window.$("#nuevaFechaEntrega")?.value;
        const nuevaHora = window.$("#nuevaHoraEntrega")?.value;
        if (!nuevaFecha || !nuevaHora) return alert("Debe seleccionar fecha y hora.");

        try {
            const resp = await fetch("/admin/contrato/solicitar-cambio-fecha", {
                method: "POST",
                headers: { "Content-Type": "application/json", "X-CSRF-TOKEN": window.csrfToken },
                body: JSON.stringify({ id_reservacion: window.ID_RESERVACION, nueva_fecha: nuevaFecha, nueva_hora: nuevaHora, motivo: "Cambio solicitado por asesor" }),
            });
            const data = await resp.json();
            alert(data.msg || "Solicitud enviada.");

            sessionStorage.setItem("solicitudCambio", JSON.stringify({ activa: true, id_reservacion: window.ID_RESERVACION }));
            iniciarMonitoreoAprobacion();

            window.$(".fecha-edicion-entrega").style.display = "none";
            window.$("#btnSolicitarCambioEntrega").style.display = "none";
            window.$("#nuevaFechaEntrega").disabled = true;
            window.$("#nuevaHoraEntrega").disabled = true;
        } catch (err) { alert("Error enviando solicitud."); }
    }

    function iniciarMonitoreoAprobacion() {
        const solicitud = JSON.parse(sessionStorage.getItem("solicitudCambio") || "{}");
        if (!solicitud.activa) return;
        if (intervaloAprobacion) clearInterval(intervaloAprobacion);

        intervaloAprobacion = setInterval(async () => {
            try {
                const resp = await fetch(`/admin/contrato/cambio-fecha/estado/${solicitud.id_reservacion}`);
                const data = await resp.json();
                if (data.estado === "aprobado") {
                    clearInterval(intervaloAprobacion);
                    intervaloAprobacion = null;
                    sessionStorage.removeItem("solicitudCambio");

                    const cIni = document.getElementById("contratoInicial");
                    if (cIni) cIni.dataset.inicio = data.fecha_nueva;

                    const partes = data.fecha_nueva.split("-");
                    window.$(".fecha-entrega-display .dia").textContent = partes[2];
                    window.$(".fecha-entrega-display .mes").textContent = new Date(data.fecha_nueva + "T00:00:00").toLocaleString("es-MX", { month: "short" }).toUpperCase();
                    window.$(".fecha-entrega-display .anio").textContent = partes[0];

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
            } catch (err) { console.error("❌ Error monitoreando:", err); }
        }, 8000);
    }

    (function reanudarMonitoreoSiAplica() {
        const solicitud = JSON.parse(sessionStorage.getItem("solicitudCambio") || "{}");
        if (solicitud.activa) iniciarMonitoreoAprobacion();
    })();

    // Modal de cambio de categoria
    selectCategoriaModal?.addEventListener("change", async (e) => {
        const nuevaCat = e.target.value;
        categoriaActual = nuevaCat;
        if (selectCategoriaOutside) selectCategoriaOutside.value = nuevaCat;

        try {
            await fetch(`/admin/contrato/${window.ID_RESERVACION}/actualizar-categoria`, {
                method: "POST",
                headers: { "Content-Type": "application/json", "X-CSRF-TOKEN": window.csrfToken },
                body: JSON.stringify({ id_categoria: nuevaCat }),
            });
            alertify.success("Categoría actualizada.");
            cargarVehiculosCategoriaModal();
            await actualizarFechasYRecalcular();
            setTimeout(() => window.cargarResumenBasico(), 150);
        } catch (err) { console.error("❌ Error modal", err); }
    });

    // Modal de vehiculos
    let listaVehiculosOriginal = [];

    async function abrirModalVehiculos() {
        const modal = window.$("#modalVehiculos");
        if (modal) modal.classList.add("show-modal");
        sincronizarCategoriaModal();
        categoriaActual = selectCategoriaOutside?.value;
        try {
            const resp = await fetch(`/admin/contrato/vehiculos-por-categoria/${categoriaActual}`);
            const data = await resp.json();
            if (data.success) {
                listaVehiculosOriginal = data.data;
                renderVehiculosEnModal(data.data);
            }
        } catch (err) { console.error("❌ Error cargando vehículos:", err); }
    }

    async function cargarVehiculosCategoriaModal() {
        try {
            const resp = await fetch(`/admin/contrato/vehiculos-por-categoria/${categoriaActual}`);
            const data = await resp.json();
            if (data.success) {
                listaVehiculosOriginal = data.data;
                renderVehiculosEnModal(data.data);
            }
        } catch (err) { console.error("❌ Error cargando vehículos", err); }
    }

    function cerrarModalVehiculos() {
        const modal = window.$("#modalVehiculos");
        if (modal) modal.classList.remove("show-modal");
    }

    function renderVehiculosEnModal(lista) {
        const cont = window.$("#listaVehiculos");
        if (!cont) return;
        cont.innerHTML = "";

        if (!lista || lista.length === 0) {
            cont.innerHTML = `<p style="padding:20px; text-align:center; color:#555;">No hay vehículos disponibles.</p>`;
            return;
        }

        lista.forEach((v) => {
            const g = v.gasolina_actual ?? 0;
            const filled = "█".repeat(g);
            const empty = "░".repeat(16 - g);
            const barraGas = `${filled}${empty}`;
            const comunes = { 2: "1/8", 4: "1/4", 6: "3/8", 8: "1/2", 10: "5/8", 12: "3/4", 14: "7/8", 16: "1" };
            const fraccionComun = comunes[g] ? ` – ${comunes[g]}` : "";

            let iconMant = v.color_mantenimiento === "verde" ? "🟢" : (v.color_mantenimiento === "amarillo" ? "🟡" : (v.color_mantenimiento === "rojo" ? "🔴" : "⚪"));
            const kmRest = v.km_restantes !== null ? `${v.km_restantes} km restantes` : "—";

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
                <button class="btn-vehiculo" data-id="${v.id_vehiculo}">Seleccionar</button>
              </div>`;
        });

        window.$$(".btn-vehiculo").forEach((btn) => btn.addEventListener("click", () => seleccionarVehiculo(btn.dataset.id)));
    }

    ["filtroColor", "filtroModelo", "filtroSerie"].forEach((id) => {
        const el = document.getElementById(id);
        if (el) el.addEventListener("input", filtrarVehiculos);
    });

    function filtrarVehiculos() {
        const color = window.$("#filtroColor")?.value.toLowerCase() || "";
        const modelo = window.$("#filtroModelo")?.value.toLowerCase() || "";
        const serie = window.$("#filtroSerie")?.value.toLowerCase() || "";

        const nuevaLista = listaVehiculosOriginal.filter((v) =>
            (v.color ?? "").toLowerCase().includes(color) &&
            (v.modelo ?? "").toLowerCase().includes(modelo) &&
            (v.numero_serie ?? "").toLowerCase().includes(serie)
        );
        renderVehiculosEnModal(nuevaLista);
    }

    async function seleccionarVehiculo(idVehiculo) {
        try {
            await fetch("/admin/contrato/asignar-vehiculo", {
                method: "POST",
                headers: { "Content-Type": "application/json", "X-CSRF-TOKEN": window.csrfToken },
                body: JSON.stringify({ id_reservacion: window.ID_RESERVACION, id_vehiculo: idVehiculo }),
            });
            alertify.success("Vehículo asignado correctamente.");
            setTimeout(() => window.cargarResumenBasico(), 150);
            cerrarModalVehiculos();
        } catch (err) { alert("Error asignando vehículo."); }
    }

    // ================================ PASO 2 ===============================
    const serviciosGrid = window.$("#serviciosGrid");

    if (serviciosGrid) {
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
                await fetch(`/admin/contrato/servicios`, {
                    method: "POST",
                    headers: { "Content-Type": "application/json", "X-CSRF-TOKEN": window.csrfToken },
                    body: JSON.stringify({ id_reservacion: window.ID_RESERVACION, id_servicio: idServicio, cantidad: cantidad, precio_unitario: precio }),
                });
                setTimeout(() => window.cargarResumenBasico(), 150);
            } catch (err) { console.error("❌ Error al actualizar servicio:", err); }
        });
        actualizarTotal();
    }

    // Delivery
    const deliveryToggle = window.$("#deliveryToggle");
    const deliveryFields = window.$("#deliveryFields");
    const deliveryUbicacion = window.$("#deliveryUbicacion");
    const deliveryDireccion = window.$("#deliveryDireccion");
    const deliveryKm = window.$("#deliveryKm");
    const deliveryTotal = window.$("#deliveryTotal");
    const groupDireccion = window.$("#groupDireccion");
    const groupKm = window.$("#groupKm");

    let costoCategoriaKM = parseFloat(window.$("#deliveryPrecioKm")?.value || 0);
    window.deliveryTotalActual = 0;

    if (deliveryToggle) {
        deliveryToggle.addEventListener("change", () => {
            if (deliveryToggle.checked) {
                deliveryFields.style.display = "block";
            } else {
                deliveryFields.style.display = "none";
                window.deliveryTotalActual = 0;
                if (deliveryTotal) deliveryTotal.textContent = "$0.00 MXN";
                if (deliveryUbicacion) deliveryUbicacion.value = "";
                if (deliveryDireccion) deliveryDireccion.value = "";
                if (deliveryKm) deliveryKm.value = "";
                if (groupDireccion) groupDireccion.style.display = "none";
                if (groupKm) groupKm.style.display = "none";
                actualizarTotal();
                guardarDelivery();
            }
        });
    }

    function actualizarVisibilidadCampos() {
        if (!deliveryUbicacion) return;
        if (deliveryUbicacion.value === "0") {
            if (groupDireccion) groupDireccion.style.display = "block";
            if (groupKm) groupKm.style.display = "block";
        } else {
            if (groupDireccion) groupDireccion.style.display = "none";
            if (groupKm) groupKm.style.display = "none";
            if (deliveryDireccion) deliveryDireccion.value = "";
            if (deliveryKm) deliveryKm.value = "";
        }
    }

    const recalcularDelivery = () => {
        let kms = 0;
        if (deliveryUbicacion?.value && deliveryUbicacion?.value !== "0") {
            kms = parseFloat(deliveryUbicacion.options[deliveryUbicacion.selectedIndex].dataset.km);
        }
        if (deliveryUbicacion?.value === "0" && deliveryKm?.value && parseFloat(deliveryKm.value) > 0) {
            kms = parseFloat(deliveryKm.value);
        }
        window.deliveryTotalActual = kms * costoCategoriaKM;
        if (deliveryTotal) deliveryTotal.textContent = `$${window.deliveryTotalActual.toFixed(2)} MXN`;
        actualizarTotal();
    };

    document.addEventListener("categoriaActualizada", () => { recalcularDelivery(); actualizarTotal(); });

    if (window.$("#deliveryTotalHidden")) {
        window.deliveryTotalActual = parseFloat(window.$("#deliveryTotalHidden").value || 0);
        if (deliveryTotal) deliveryTotal.textContent = `$${window.deliveryTotalActual.toFixed(2)} MXN`;
    }

    actualizarTotal();
    actualizarVisibilidadCampos();
    recalcularDelivery();

    deliveryUbicacion?.addEventListener("change", () => { actualizarVisibilidadCampos(); recalcularDelivery(); guardarDelivery(); });
    deliveryKm?.addEventListener("input", () => { recalcularDelivery(); guardarDelivery(); });
    deliveryDireccion?.addEventListener("input", () => guardarDelivery());

    async function guardarDelivery() {
        if (!window.ID_RESERVACION) return;
        let kms = 0;
        if (deliveryUbicacion?.value && deliveryUbicacion?.value !== "0") kms = parseFloat(deliveryUbicacion.options[deliveryUbicacion.selectedIndex].dataset.km);
        if (deliveryUbicacion?.value === "0" && deliveryKm?.value) kms = parseFloat(deliveryKm.value);

        try {
            await fetch(`/admin/reservacion/delivery/guardar`, {
                method: "POST",
                headers: { "Content-Type": "application/json", "X-CSRF-TOKEN": window.csrfToken },
                body: JSON.stringify({
                    id_reservacion: window.ID_RESERVACION,
                    delivery_activo: deliveryToggle?.checked ? 1 : 0,
                    delivery_ubicacion: deliveryUbicacion?.value !== "0" ? deliveryUbicacion?.value : "0",
                    delivery_direccion: deliveryUbicacion?.value === "0" ? deliveryDireccion?.value : null,
                    delivery_km: parseFloat(kms) || 0,
                    delivery_precio_km: parseFloat(costoCategoriaKM) || 0,
                    delivery_total: parseFloat(window.deliveryTotalActual) || 0
                }),
            });
            setTimeout(() => window.cargarResumenBasico(), 150);
        } catch (err) { console.error("❌ Error al guardar Delivery:", err); }
    }

    // ================================ PASO 3 ===============================

    // APERTURA Y CIERRE DE MODALES ---
    const modalPaquetes = document.getElementById("modalPaquetes");
    const modalIndividuales = document.getElementById("modalIndividuales");

    // Botones de Apertura
    document.getElementById("btnVerPaquetes")?.addEventListener("click", () => {
        if (modalPaquetes) modalPaquetes.style.display = "flex";
    });

    document.getElementById("btnVerIndividuales")?.addEventListener("click", () => {
        if (modalIndividuales) modalIndividuales.style.display = "flex";
    });

    // Botones de Cierre (X)
    document.querySelectorAll(".close-modal, .closeModal").forEach((btn) => {
        btn.addEventListener("click", () => {
            if (modalPaquetes) modalPaquetes.style.display = "none";
            if (modalIndividuales) modalIndividuales.style.display = "none";
        });
    });

    // FUNCIÓN DE SUMA (Actualiza el Total protecciones) ---
    function recalcularTotalProtecciones() {
        const display = document.getElementById("total_seguros");
        const btnGo = document.getElementById("go4");
        if (!display) return;

        let total = 0;

        // Verificamos si hay un Paquete prendido
        const packActive = document.querySelector("#packGrid .switch.on");
        if (packActive) {
            total = parseFloat(packActive.closest(".seguro-item").dataset.precio || 0);
        } else {
            // Sumamos las individuales prendidas
            document.querySelectorAll(".switch-individual.on").forEach(sw => {
                total += parseFloat(sw.closest(".individual-item").dataset.precio || 0);
            });
        }

        display.textContent = typeof window.money === 'function' ? window.money(total) : `$${total.toFixed(2)} MXN`;

        if (btnGo) {
            const tieneSeleccion = total > 0;
            btnGo.style.opacity = tieneSeleccion ? "1" : "0.5";
            btnGo.style.pointerEvents = tieneSeleccion ? "auto" : "none";
        }
    }

    // CLIC EN PAQUETES (Modal Paquetes) ---
    document.getElementById("packGrid")?.addEventListener("click", async (e) => {
        const label = e.target.closest(".seguro-item");
        if (!label) return;

        e.preventDefault();

        const sw = label.querySelector(".switch");
        if (!sw || sw.classList.contains("on")) return;

        // REGLA: Apagamos todo lo demás
        document.querySelectorAll(".switch, .switch-individual").forEach(s => s.classList.remove("on"));
        sw.classList.add("on");

        try {
            await fetch(`/admin/contrato/seguros`, {
                method: "POST",
                headers: { "Content-Type": "application/json", "X-CSRF-TOKEN": window.csrfToken },
                body: JSON.stringify({
                    id_reservacion: window.ID_RESERVACION,
                    id_paquete: sw.dataset.id,
                    precio_por_dia: label.dataset.precio
                })
            });
            alertify.success("Paquete seleccionado.");
            recalcularTotalProtecciones();
            if (typeof window.cargarResumenBasico === 'function') window.cargarResumenBasico();
        } catch (err) { console.error("Error paquete:", err); }
    });

    // CLIC EN INDIVIDUALES (Modal Individuales) ---
    document.getElementById("modalIndividuales")?.addEventListener("click", async (e) => {
        const label = e.target.closest(".individual-item");
        if (!label) return;

        e.preventDefault();

        const sw = label.querySelector(".switch-individual");
        if (!sw) return;

        const estaPrendido = sw.classList.contains("on");

        // REGLA: Si prendemos una individual, apagamos el paquete
        if (!estaPrendido) {
            document.querySelectorAll("#packGrid .switch").forEach(s => s.classList.remove("on"));
        }

        try {
            if (estaPrendido) {
                // No dejar desactivar si es la última activa
                const activas = document.querySelectorAll(".switch-individual.on").length;
                if (activas <= 1) {
                    alertify.warning("Debes tener al menos una protección activa.");
                    return;
                }
                sw.classList.remove("on");
                await fetch(`/admin/contrato/seguros-individuales`, {
                    method: "DELETE",
                    headers: { "Content-Type": "application/json", "X-CSRF-TOKEN": window.csrfToken },
                    body: JSON.stringify({ id_reservacion: window.ID_RESERVACION, id_seguro: sw.dataset.id })
                });
            } else {
                sw.classList.add("on");
                await fetch(`/admin/contrato/seguros-individuales`, {
                    method: "POST",
                    headers: { "Content-Type": "application/json", "X-CSRF-TOKEN": window.csrfToken },
                    body: JSON.stringify({
                        id_reservacion: window.ID_RESERVACION,
                        id_seguro: sw.dataset.id,
                        precio_por_dia: label.dataset.precio
                    })
                });
            }
            recalcularTotalProtecciones();
            if (typeof window.cargarResumenBasico === 'function') window.cargarResumenBasico();
        } catch (err) { console.error("Error individual:", err); }
    });

    // Inicializar total al cargar
    setTimeout(recalcularTotalProtecciones, 800);

    // Navegacion del paso 1 al 3
    window.$("#go2")?.addEventListener("click", async () => {
        try {
            const resp = await fetch(`/admin/contrato/${window.ID_RESERVACION}/oferta-upgrade`);
            const data = await resp.json();

            if (!data.success || !data.categoria) return window.showStep(2);

            const oferta = await construirOfertaCategoria(data.categoria.codigo);
            if (!oferta) return window.showStep(2);

            mostrarModalOferta(oferta);
        } catch (e) { window.showStep(2); }
    });

    window.$("#go3")?.addEventListener("click", () => {
        guardarDelivery();
        setTimeout(() => window.cargarResumenBasico(), 150);
        window.showStep(3);
    });

    window.$("#go4")?.addEventListener("click", (e) => {
        e.preventDefault();

        const tienePaquete = document.querySelector("#packGrid .switch.on");
        const tieneIndividual = document.querySelector(".switch-individual.on");

        if (!tienePaquete && !tieneIndividual) {
            if (window.alertify) {
                alertify.error("Error: El contrato no puede ir sin ninguna protección.");
            } else {
                alert("Error: Debes seleccionar al menos un Paquete o un Seguro Individual.");
            }
            return;
        }

        const btn = e.currentTarget;
        btn.innerHTML = "Cargando...";
        btn.style.pointerEvents = "none";

        const url = `/admin/contrato2/${window.ID_RESERVACION}`;

        // precargar
        fetch(url, { method: "GET" })
            .then(() => {
                window.location.href = url;
            })
            .catch(() => {
                window.location.href = url;
            });
    });

    window.$("#back1")?.addEventListener("click", () => window.showStep(1));
    window.$("#back2")?.addEventListener("click", () => window.showStep(2));
    window.$("#back3")?.addEventListener("click", () => window.showStep(3));

    let pasoGuardado = 1;
    if (window.ID_RESERVACION) {
        const guardado = localStorage.getItem(`contratoPasoActual_${window.ID_RESERVACION}`);
        if ([1, 2, 3].includes(Number(guardado))) pasoGuardado = Number(guardado);
    }
    window.showStep(pasoGuardado);
});