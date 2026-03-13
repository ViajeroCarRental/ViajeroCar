// Contrato
// Pasos 1 al 3

document.addEventListener("DOMContentLoaded", () => {
    console.log("✅ DOM listo, iniciando navegación de pasos (1-3)...");

    let intervaloAprobacion = null;
    const ORDEN_CATEGORIAS = ["C", "D", "E", "F", "IC", "I", "IB", "M", "L", "H", "HI"];

    const selectCategoriaOutside = document.getElementById("selectCategoria");
    const selectCategoriaModal = document.getElementById("selectCategoriaModal");
    let categoriaActual = selectCategoriaOutside?.value || null;

    // ================================ EVENTOS DE EDICIÓN Y CATEGORÍA (PASO 1) ===============================

    function obtenerHoraActual() { return new Date().toLocaleTimeString("en-GB", { hour: "2-digit", minute: "2-digit" }); }

    (function inicializarPaso1() {
        const lblHoraEntrega = window.$(".bloque.entrega .hora");
        if (lblHoraEntrega) lblHoraEntrega.textContent = obtenerHoraActual();

        // Edicion Salida
        window.$$(".fecha-entrega-edit").forEach(btn => btn.addEventListener("click", () => {
            const cont = window.$(".fecha-edicion-entrega");
            if (cont) {
                if (cont.style.display === "none" || cont.style.display === "") {
                    cont.style.display = "block";
                    window.$("#nuevaFechaEntrega").disabled = false;
                    window.$("#nuevaFechaEntrega").value = document.getElementById("contratoInicial")?.dataset.inicio || "";
                    window.$("#nuevaHoraEntrega").disabled = false;
                    window.$("#nuevaHoraEntrega").value = obtenerHoraActual();

                    if (window.$("#btnSolicitarCambioEntrega")) {
                        window.$("#btnSolicitarCambioEntrega").style.display = "inline-flex";
                    }
                } else {
                    cont.style.display = "none";
                }
            }
        }));

        // Edicion Devolucion
        window.$$(".fecha-devolucion-edit").forEach(btn => btn.addEventListener("click", () => {
            const cont = window.$(".fecha-edicion-devolucion");
            if (cont) {
                if (cont.style.display === "none" || cont.style.display === "") {
                    cont.style.display = "block";
                    window.$("#nuevaFechaDevolucion").value = document.getElementById("contratoInicial")?.dataset.fin || "";
                    window.$("#nuevaHoraDevolucion").value = document.getElementById("contratoInicial")?.dataset.horaEntrega || "";
                } else {
                    cont.style.display = "none";
                }
            }
        }));

        // Guardar Devolucion
        window.$("#btnGuardarFechaDevolucion")?.addEventListener("click", async () => {
            const btn = window.$("#btnGuardarFechaDevolucion");
            const textoOriginal = btn.innerHTML;
            btn.innerHTML = "⏳...";

            if (typeof window.actualizarFechasYRecalcular === 'function') {
                await window.actualizarFechasYRecalcular();
            }

            const cont = window.$(".fecha-edicion-devolucion");
            if (cont) cont.style.display = "none";

            btn.innerHTML = textoOriginal;
        });

        // Cambio Categoria
        selectCategoriaOutside?.addEventListener("change", async (e) => {
            categoriaActual = e.target.value;
            if (window.ContratoStore) window.ContratoStore.set('categoriaElegida', categoriaActual);

            if (window.$("#totalReserva")) window.$("#totalReserva").textContent = "...";
            const IDsResumen = ["r_base_precio", "r_subtotal", "r_iva", "r_total_final"];
            IDsResumen.forEach(id => { const el = document.getElementById(id); if (el) el.style.opacity = "0.5"; });

            try {
                if (typeof window.actualizarFechasYRecalcular === 'function') {
                    await window.actualizarFechasYRecalcular(null);
                }
                alertify.success("Categoría actualizada.");
            } catch (err) {
                alertify.error("Error al actualizar categoría.");
            } finally {
                IDsResumen.forEach(id => { const el = document.getElementById(id); if (el) el.style.opacity = "1"; });
            }
        });

        // Modales
        window.$("#btnElegirVehiculo")?.addEventListener("click", abrirModalVehiculos);
        window.$("#cerrarModalVehiculos")?.addEventListener("click", cerrarModalVehiculos);
        window.$("#cerrarModalVehiculos2")?.addEventListener("click", cerrarModalVehiculos);
    })();

    // ================================ VEHÍCULOS Y UPGRADES ===============================

    function sincronizarCategoriaModal() { if (selectCategoriaModal) selectCategoriaModal.value = categoriaActual; }

    async function construirOfertaCategoria(codigoCategoria) {
        if (!codigoCategoria) return null;
        try {
            const resp = await fetch(`/admin/contrato/categoria-info/${codigoCategoria}`);
            const data = await resp.json();
            if (!data.success || !data.categoria) return null;

            const cat = data.categoria;
            const respVeh = await fetch(`/admin/contrato/vehiculo-random/${cat.id_categoria}`);
            const dataVeh = await respVeh.json();
            const veh = (dataVeh.success && dataVeh.vehiculo) ? dataVeh.vehiculo : null;

            const precioReal = Number(cat.precio_dia);
            const precioInflado = Math.round(precioReal * 1.35);
            return {
                id_categoria: cat.id_categoria, codigo: cat.codigo, nombre: cat.nombre, descripcion: cat.descripcion,
                precioReal, precioInflado, descuento: Math.round(((precioInflado - precioReal) / precioInflado) * 100),
                imagen: veh?.foto_url ?? "/img/default-car.jpg", nombre_vehiculo: veh?.nombre_publico ?? cat.nombre,
                transmision: veh?.transmision ?? null, asientos: veh?.asientos ?? null, puertas: veh?.puertas ?? null, color: veh?.color ?? null
            };
        } catch (err) { return null; }
    }

    function mostrarModalOferta(oferta) {
        const modal = document.getElementById("modalUpgrade");
        document.getElementById("upgTitulo").textContent = oferta.nombre;
        document.getElementById("upgPrecioInflado").textContent = `$${oferta.precioInflado}`;
        document.getElementById("upgPrecioReal").textContent = `$${oferta.precioReal}`;
        document.getElementById("upgDescuento").textContent = `${oferta.descuento}%`;
        document.getElementById("upgDescripcion").textContent = oferta.descripcion;
        document.getElementById("upgImagenVehiculo").src = oferta.imagen;
        document.getElementById("upgNombreVehiculo").textContent = oferta.nombre_vehiculo;
        document.getElementById("upgSpecs").innerHTML = `<div>${oferta.transmision ?? "—"}</div><div>${oferta.asientos ?? "--"} asientos</div><div>${oferta.puertas ?? "--"} puertas</div><div>${oferta.color ?? "—"}</div>`;
        modal.dataset.idCategoriaUpgrade = oferta.id_categoria;
        modal.classList.add("show");
    }

    document.getElementById("btnAceptarUpgrade")?.addEventListener("click", async () => {
        const modal = document.getElementById("modalUpgrade");
        const btn = document.getElementById("btnAceptarUpgrade");
        btn.disabled = true; btn.innerHTML = "Aplicando...";
        try {
            const resp = await fetch(`/admin/contrato/${window.ID_RESERVACION}/actualizar-categoria`, {
                method: "POST", headers: { "Content-Type": "application/json", "X-CSRF-TOKEN": window.csrfToken },
                body: JSON.stringify({ id_categoria: modal.dataset.idCategoriaUpgrade }),
            });
            const result = await resp.json();
            if (result.success) {
                if (typeof window.cargarResumenBasico === 'function') await window.cargarResumenBasico();
                alertify.success("Upgrade aplicado.");
                modal.classList.remove("show");
                window.showStep(2);
            }
        } catch (e) { alertify.error("Error de conexión."); btn.disabled = false; btn.innerHTML = "Aceptar upgrade"; }
    });

    document.getElementById("btnRechazarUpgrade")?.addEventListener("click", () => { document.getElementById("modalUpgrade").classList.remove("show"); window.showStep(2); });
    document.getElementById("cerrarUpgrade")?.addEventListener("click", () => document.getElementById("modalUpgrade").classList.remove("show"));

    // Modal Cambio de Categoria
    selectCategoriaModal?.addEventListener("change", async (e) => {
        categoriaActual = e.target.value;
        if (selectCategoriaOutside) selectCategoriaOutside.value = categoriaActual;
        try {
            await fetch(`/admin/contrato/${window.ID_RESERVACION}/actualizar-categoria`, {
                method: "POST", headers: { "Content-Type": "application/json", "X-CSRF-TOKEN": window.csrfToken },
                body: JSON.stringify({ id_categoria: categoriaActual }),
            });
            alertify.success("Categoría actualizada.");
            cargarVehiculosCategoriaModal();

            if (typeof window.actualizarFechasYRecalcular === 'function') {
                await window.actualizarFechasYRecalcular();
            }
        } catch (err) { console.error("❌ Error modal", err); }
    });

    let listaVehiculosOriginal = [];

    async function abrirModalVehiculos() {
        const modal = window.$("#modalVehiculos");
        if (modal) modal.classList.add("show-modal");
        sincronizarCategoriaModal();
        try {
            const resp = await fetch(`/admin/contrato/vehiculos-por-categoria/${categoriaActual}`);
            const data = await resp.json();
            if (data.success) { listaVehiculosOriginal = data.data; renderVehiculosEnModal(data.data); }
        } catch (err) { console.error(err); }
    }

    async function cargarVehiculosCategoriaModal() {
        try {
            const resp = await fetch(`/admin/contrato/vehiculos-por-categoria/${categoriaActual}`);
            const data = await resp.json();
            if (data.success) { listaVehiculosOriginal = data.data; renderVehiculosEnModal(data.data); }
        } catch (err) { console.error(err); }
    }

    function cerrarModalVehiculos() { window.$("#modalVehiculos")?.classList.remove("show-modal"); }

    function renderVehiculosEnModal(lista) {
        const cont = window.$("#listaVehiculos");
        if (!cont) return;
        cont.innerHTML = "";

        if (!lista || lista.length === 0) {
            cont.innerHTML = `<p style="padding:20px; text-align:center; color:#555;">No hay vehículos disponibles en esta categoría.</p>`;
            return;
        }

        lista.forEach((v) => {
            // Gasolina vissual
            const g = v.gasolina_actual ?? 0;
            const filled = "█".repeat(g);
            const empty = "░".repeat(16 - g);
            const barraGas = `${filled}${empty}`;

            const comunes = { 2: "1/8", 4: "1/4", 6: "3/8", 8: "1/2", 10: "5/8", 12: "3/4", 14: "7/8", 16: "1" };
            const fraccionComun = comunes[g] ? ` – ${comunes[g]}` : "";

            // Mantenimiento
            let iconMant = "⚪";
            if (v.color_mantenimiento === "verde") iconMant = "🟢";
            if (v.color_mantenimiento === "amarillo") iconMant = "🟡";
            if (v.color_mantenimiento === "rojo") iconMant = "🔴";

            const kmRest = v.km_restantes !== null ? `${v.km_restantes} km restantes` : "—";

            // Placa y poliza
            const placaVehiculo = v.placa ? v.placa : "Sin Placa";
            const vigenciaPoliza = v.fin_vigencia_poliza ? v.fin_vigencia_poliza : "No registrada";

            cont.innerHTML += `
              <div class="vehiculo-card" style="display:flex; gap:15px; margin-bottom:12px; padding:15px; border:1px solid var(--stroke); border-radius:8px; align-items: center;">
                <img src="${v.foto_url ?? '/img/default-car.png'}" style="width:120px; height:85px; object-fit:cover; border-radius:6px;">
                
                <div class="vehiculo-info" style="flex:1;">
                  <h4 style="margin:0 0 4px; font-size:16px;">${v.nombre_publico || (v.marca + ' ' + v.modelo)}</h4>
                  
                  <p style="margin:0 0 6px 0; font-size:13px; color:#666;">
                    ${v.transmision} · ${v.asientos} asientos · ${v.puertas} puertas <br>
                    Color: ${v.color ?? "—"} | Placa: <b style="background:#f1f5f9; border:1px solid #cbd5e1; padding:2px 6px; border-radius:4px; color:#0f172a; letter-spacing: 0.5px;">${placaVehiculo}</b>
                  </p>
                  
                  <p style="margin:2px 0; font-size:13px; font-family: monospace;"><b>Gasolina:</b> ${barraGas} <span style="font-family: sans-serif;">(${g}/16${fraccionComun})</span></p>
                  <p style="margin:2px 0; font-size:13px;"><b>Kilometraje:</b> ${v.kilometraje?.toLocaleString() ?? "—"} km</p>
                  <p style="margin:2px 0; font-size:13px;"><b>Mantenimiento:</b> ${iconMant} ${kmRest} <span style="color:#ccc;">|</span> <b>Vigencia póliza:</b> ${vigenciaPoliza}</p>
                </div>

                <div>
                  <button type="button" class="btn primary btn-vehiculo" data-id="${v.id_vehiculo}" style="padding: 8px 16px;">
                    Seleccionar
                  </button>
                </div>
              </div>
            `;
        });

        // Evento para seleccionar
        window.$$(".btn-vehiculo").forEach(btn => btn.addEventListener("click", () => seleccionarVehiculo(btn.dataset.id)));
    }

    ["filtroColor", "filtroModelo", "filtroSerie"].forEach(id => {
        const el = document.getElementById(id);
        if (el) el.addEventListener("input", () => {
            const c = window.$("#filtroColor")?.value.toLowerCase() || "";
            const m = window.$("#filtroModelo")?.value.toLowerCase() || "";
            const s = window.$("#filtroSerie")?.value.toLowerCase() || "";
            renderVehiculosEnModal(listaVehiculosOriginal.filter(v => (v.color ?? "").toLowerCase().includes(c) && (v.modelo ?? "").toLowerCase().includes(m) && (v.numero_serie ?? "").toLowerCase().includes(s)));
        });
    });

    async function seleccionarVehiculo(idVehiculo) {
        try {
            await fetch("/admin/contrato/asignar-vehiculo", {
                method: "POST", headers: { "Content-Type": "application/json", "X-CSRF-TOKEN": window.csrfToken },
                body: JSON.stringify({ id_reservacion: window.ID_RESERVACION, id_vehiculo: idVehiculo }),
            });
            alertify.success("Vehículo asignado.");
            setTimeout(() => window.cargarResumenBasico(), 150);
            cerrarModalVehiculos();
        } catch (err) { alert("Error asignando vehículo."); }
    }

    // ================================ SOLICITUDES DE CAMBIO ===============================

    window.$("#btnSolicitarCambioEntrega")?.addEventListener("click", async () => {
        const f = window.$("#nuevaFechaEntrega")?.value;
        const h = window.$("#nuevaHoraEntrega")?.value;
        if (!f || !h) return alert("Seleccione fecha y hora.");
        try {
            const resp = await fetch("/admin/contrato/solicitar-cambio-fecha", {
                method: "POST", headers: { "Content-Type": "application/json", "X-CSRF-TOKEN": window.csrfToken },
                body: JSON.stringify({ id_reservacion: window.ID_RESERVACION, nueva_fecha: f, nueva_hora: h, motivo: "Asesor" }),
            });
            const data = await resp.json();
            alert(data.msg || "Solicitud enviada.");
            sessionStorage.setItem("solicitudCambio", JSON.stringify({ activa: true, id_reservacion: window.ID_RESERVACION }));
            iniciarMonitoreoAprobacion();
            window.$(".fecha-edicion-entrega").style.display = "none";
        } catch (err) { alert("Error enviando solicitud."); }
    });

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
                    sessionStorage.removeItem("solicitudCambio");
                    document.getElementById("contratoInicial").dataset.inicio = data.fecha_nueva;
                    if (typeof window.actualizarFechasYRecalcular === 'function') {
                        await window.actualizarFechasYRecalcular();
                    }
                    alertify.success("Cambio aprobado.");
                } else if (data.estado === "rechazado") {
                    clearInterval(intervaloAprobacion);
                    sessionStorage.removeItem("solicitudCambio");
                    alertify.error("Solicitud rechazada.");
                }
            } catch (err) { console.error(err); }
        }, 8000);
    }

    if (JSON.parse(sessionStorage.getItem("solicitudCambio") || "{}").activa) iniciarMonitoreoAprobacion();

    // ================================ LÓGICA DEL DELIVERY ===============================
    const deliveryToggle = window.$("#deliveryToggle");
    const deliveryFields = window.$("#deliveryFields");
    const deliveryUbicacion = window.$("#deliveryUbicacion");
    const deliveryDireccion = window.$("#deliveryDireccion");
    const deliveryKm = window.$("#deliveryKm");

    // Función segura para obtener el costo
    const getCostoKm = () => parseFloat(window.$("#deliveryPrecioKm")?.value || 0);

    const recalcularDelivery = () => {
        let kms = 0;

        if (deliveryToggle?.checked) {
            if (deliveryUbicacion?.value && deliveryUbicacion.value !== "0") {
                kms = parseFloat(deliveryUbicacion.options[deliveryUbicacion.selectedIndex].dataset.km || 0);
            } else if (deliveryUbicacion?.value === "0") {
                kms = parseFloat(deliveryKm?.value || 0);
            }
        }

        window.deliveryTotalActual = kms * getCostoKm();

        const textTotal = window.$("#deliveryTotal");
        if (textTotal) textTotal.textContent = window.money ? window.money(window.deliveryTotalActual) : `$${window.deliveryTotalActual.toFixed(2)} MXN`;

        actualizarTotalServicios();
    };

    let timerDelivery = null;

    // Guardad Delivery
    function guardarDeliverySeguro(inmediato = false) {
        if (!window.ID_RESERVACION) return;

        clearTimeout(timerDelivery);

        const ejecutarGuardado = async () => {
            let kms = 0;
            let ubicacionVal = "0";

            if (deliveryToggle?.checked) {
                if (deliveryUbicacion?.value && deliveryUbicacion?.value !== "0") {
                    kms = parseFloat(deliveryUbicacion.options[deliveryUbicacion.selectedIndex].dataset.km || 0);
                    ubicacionVal = deliveryUbicacion.value;
                } else {
                    kms = parseFloat(deliveryKm?.value || 0);
                }
            }

            try {
                await fetch(`/admin/reservacion/delivery/guardar`, {
                    method: "POST",
                    headers: { "Content-Type": "application/json", "X-CSRF-TOKEN": window.csrfToken },
                    body: JSON.stringify({
                        id_reservacion: window.ID_RESERVACION,
                        delivery_activo: deliveryToggle?.checked ? 1 : 0,
                        delivery_ubicacion: ubicacionVal,
                        delivery_direccion: deliveryToggle?.checked ? (deliveryDireccion?.value || null) : null,
                        delivery_km: kms,
                        delivery_precio_km: getCostoKm(),
                        delivery_total: window.deliveryTotalActual || 0
                    }),
                });
                if (typeof window.cargarResumenBasico === 'function') window.cargarResumenBasico();
            } catch (err) { console.error("Error guardando delivery:", err); }
        };

        if (inmediato) {
            ejecutarGuardado();
        } else {
            timerDelivery = setTimeout(ejecutarGuardado, 600);
        }
    }

    deliveryToggle?.addEventListener("change", () => {
        deliveryFields.style.display = deliveryToggle.checked ? "block" : "none";

        if (!deliveryToggle.checked) {
            // Si se apaga, limpiar variables globales e inputs visuales
            window.deliveryTotalActual = 0;
            if (deliveryUbicacion) deliveryUbicacion.value = "";
            if (deliveryKm) deliveryKm.value = "";
            if (window.$("#groupDireccion")) window.$("#groupDireccion").style.display = "none";
            if (window.$("#groupKm")) window.$("#groupKm").style.display = "none";

            recalcularDelivery();
            guardarDeliverySeguro(true); // Guardar inmediato al apagar
        } else {
            recalcularDelivery();
            guardarDeliverySeguro(); // Guardar con debounce al encender
        }
    });

    deliveryUbicacion?.addEventListener("change", () => {
        const esManual = deliveryUbicacion.value === "0";
        if (window.$("#groupDireccion")) window.$("#groupDireccion").style.display = esManual ? "block" : "none";
        if (window.$("#groupKm")) window.$("#groupKm").style.display = esManual ? "block" : "none";
        recalcularDelivery();
        guardarDeliverySeguro();
    });

    deliveryKm?.addEventListener("input", () => { recalcularDelivery(); guardarDeliverySeguro(); });
    deliveryDireccion?.addEventListener("input", () => { guardarDeliverySeguro(); });

    if (window.$("#deliveryTotalHidden")) {
        window.deliveryTotalActual = parseFloat(window.$("#deliveryTotalHidden").value || 0);
        actualizarTotalServicios();
    }

    // ================================ PASO 2: SERVICIOS ===============================

    function actualizarTotalServicios() {
        let total = 0;
        // Obtenemos cuántos días dura la renta desde el Aside
        let diasRenta = parseInt(window.$("#detDiasRenta")?.textContent || 1);

        document.querySelectorAll(".card-servicio").forEach(card => {
            const precio = parseFloat(card.dataset.precio || 0);
            const cantidad = parseInt(card.querySelector(".cantidad").textContent || 0);
            const tipoCobro = card.dataset.tipo;

            if (tipoCobro === 'por_dia') {
                total += (precio * cantidad) * diasRenta;
            } else {
                total += (precio * cantidad);
            }
        });

        if (window.deliveryTotalActual) total += window.deliveryTotalActual;

        const el = document.querySelector("#total_servicios");
        if (el) el.textContent = window.money ? window.money(total) : `$${total.toFixed(2)} MXN`;
    }

    let timerServicios = null;

    window.$("#serviciosGrid")?.addEventListener("click", (e) => {
        const btn = e.target;
        if (!btn.classList.contains("mas") && !btn.classList.contains("menos")) return;

        const card = btn.closest(".card-servicio");
        const cantEl = card.querySelector(".cantidad");
        let cant = parseInt(cantEl.textContent);

        if (btn.classList.contains("mas")) cant++;
        else if (cant > 0) cant--;

        cantEl.textContent = cant;

        actualizarTotalServicios();

        clearTimeout(timerServicios);
        timerServicios = setTimeout(async () => {
            try {
                await fetch(`/admin/contrato/servicios`, {
                    method: "POST",
                    headers: { "Content-Type": "application/json", "X-CSRF-TOKEN": window.csrfToken },
                    body: JSON.stringify({
                        id_reservacion: window.ID_RESERVACION,
                        id_servicio: card.dataset.id,
                        cantidad: cant,
                        precio_unitario: card.dataset.precio
                    }),
                });
                if (typeof window.cargarResumenBasico === 'function') window.cargarResumenBasico();
            } catch (err) { console.error(err); }
        }, 500);
    });

    // ================================ PASO 3: SEGUROS ===============================

    function recalcularTotalProtecciones() {
        const display = document.getElementById("total_seguros");
        const btnGo = document.getElementById("go4");
        if (!display) return;

        let subtotalPorDia = 0;

        const packActive = document.querySelector("#packGrid .switch.on");
        if (packActive) {
            subtotalPorDia = parseFloat(packActive.closest(".seguro-item").dataset.precio || 0);
        } else {
            document.querySelectorAll(".switch-individual.on").forEach(sw => {
                subtotalPorDia += parseFloat(sw.closest(".individual-item").dataset.precio || 0);
            });
        }

        const diasRenta = parseInt(window.$("#detDiasRenta")?.textContent || 1);
        const totalReal = subtotalPorDia * diasRenta;

        display.textContent = window.money ? window.money(totalReal) : `$${totalReal.toFixed(2)}`;

        if (btnGo) {
            btnGo.style.opacity = "1";
            btnGo.style.pointerEvents = "auto";
        }
    }

    // Modales de seguro
    window.$("#btnVerPaquetes")?.addEventListener("click", () => window.$("#modalPaquetes").style.display = "flex");
    window.$("#btnVerIndividuales")?.addEventListener("click", () => window.$("#modalIndividuales").style.display = "flex");

    // Cerrar con botones 'X'
    document.querySelectorAll(".close-modal").forEach(btn => btn.addEventListener("click", () => {
        if (window.$("#modalPaquetes")) window.$("#modalPaquetes").style.display = "none";
        if (window.$("#modalIndividuales")) window.$("#modalIndividuales").style.display = "none";
    }));

    window.$("#btnListoIndividuales")?.addEventListener("click", () => {
        if (window.$("#modalIndividuales")) window.$("#modalIndividuales").style.display = "none";
    });

    // click a fuera para modal
    window.addEventListener("click", (event) => {
        const modalPaquetes = window.$("#modalPaquetes");
        const modalIndividuales = window.$("#modalIndividuales");
        if (event.target === modalPaquetes) modalPaquetes.style.display = "none";
        if (event.target === modalIndividuales) modalIndividuales.style.display = "none";
    });

    // ================================ PAQUETES ===============================

    // Paquetes completos
    document.getElementById("packGrid")?.addEventListener("click", async (e) => {
        const label = e.target.closest(".seguro-item");
        if (!label) return;
        const sw = label.querySelector(".switch");
        if (!sw || sw.classList.contains("on")) return;

        // Magia visual: Apagamos TODO (Paquetes e Individuales) y prendemos el seleccionado
        document.querySelectorAll(".switch, .switch-individual").forEach(s => s.classList.remove("on"));
        sw.classList.add("on");

        // Recalculamos la pantalla
        recalcularTotalProtecciones();

        try {
            await fetch(`/admin/contrato/seguros`, {
                method: "POST", headers: { "Content-Type": "application/json", "X-CSRF-TOKEN": window.csrfToken },
                body: JSON.stringify({ id_reservacion: window.ID_RESERVACION, id_paquete: sw.dataset.id, precio_por_dia: label.dataset.precio })
            });
            if (typeof window.cargarResumenBasico === 'function') window.cargarResumenBasico();
        } catch (err) { console.error(err); }
    });

    // Paquetes individuales
    document.getElementById("modalIndividuales")?.addEventListener("click", async (e) => {
        const label = e.target.closest(".individual-item");
        if (!label) return;
        const sw = label.querySelector(".switch-individual");
        if (!sw) return;

        const estaPrendido = sw.classList.contains("on");

        // Magia visual: Si prendo un individual (!estaPrendido), aseguro que todos los paquetes se apaguen
        if (!estaPrendido) {
            document.querySelectorAll("#packGrid .switch").forEach(s => s.classList.remove("on"));
        }

        // Si intenta apagar la única que queda encendida, lo bloqueamos
        if (estaPrendido && document.querySelectorAll(".switch-individual.on").length <= 1) {
            return typeof alertify !== 'undefined'
                ? alertify.warning("Debes tener al menos una protección.")
                : alert("Debes tener al menos una protección.");
        }

        // Toggle visual
        if (estaPrendido) sw.classList.remove("on");
        else sw.classList.add("on");

        // Recalculamos la pantalla INSTANTÁNEAMENTE
        recalcularTotalProtecciones();

        try {
            if (estaPrendido) {
                // Lo está apagando -> DELETE
                await fetch(`/admin/contrato/seguros-individuales`, {
                    method: "DELETE", headers: { "Content-Type": "application/json", "X-CSRF-TOKEN": window.csrfToken },
                    body: JSON.stringify({ id_reservacion: window.ID_RESERVACION, id_seguro: sw.dataset.id })
                });
            } else {
                // Lo está prendiendo -> POST
                await fetch(`/admin/contrato/seguros-individuales`, {
                    method: "POST", headers: { "Content-Type": "application/json", "X-CSRF-TOKEN": window.csrfToken },
                    body: JSON.stringify({ id_reservacion: window.ID_RESERVACION, id_seguro: sw.dataset.id, precio_por_dia: label.dataset.precio })
                });
            }
            if (typeof window.cargarResumenBasico === 'function') window.cargarResumenBasico();
        } catch (err) { console.error(err); }
    });

    setTimeout(recalcularTotalProtecciones, 800);

    // ================================ NAVEGACIÓN Y GUARDADO ===============================

    function guardarDatosPaso1() {
        sessionStorage.setItem("contratoPaso1", JSON.stringify({
            codigo: window.$("#codigo")?.textContent.trim() || "",
            duracion: window.$("#diasBadge")?.textContent.trim() || "",
            total: window.$("#totalReserva")?.textContent.trim() || ""
        }));
    }

    window.$("#go2")?.addEventListener("click", async () => {
        try {
            const resp = await fetch(`/admin/contrato/${window.ID_RESERVACION}/oferta-upgrade`);
            const data = await resp.json();
            if (!data.success || !data.categoria) return window.showStep(2);
            mostrarModalOferta(await construirOfertaCategoria(data.categoria.codigo));
        } catch (e) { window.showStep(2); }
    });

    window.$("#go3")?.addEventListener("click", () => {
        if (typeof guardarDeliverySeguro === 'function') {
            guardarDeliverySeguro(true);
        }
        setTimeout(() => {
            if (typeof window.cargarResumenBasico === 'function') window.cargarResumenBasico();
        }, 150);
        window.showStep(3);
    });

    window.$("#go4")?.addEventListener("click", (e) => {
        e.preventDefault();

        const elInicial = document.getElementById("contratoInicial");
        const idReservacion = window.ID_RESERVACION || (elInicial ? elInicial.dataset.idReservacion : null);

        if (!idReservacion) {
            if (window.alertify) return alertify.error("Error: ID de reservación perdido.");
            return alert("Error: ID de reservación perdido.");
        }

        const seguro = document.querySelector("#packGrid .switch.on") || document.querySelector(".switch-individual.on");
        if (!seguro) {
            if (window.alertify) return alertify.warning("Selecciona una protección.");
            return alert("Selecciona una protección.");
        }

        localStorage.setItem(`contratoPasoActual_${idReservacion}`, '4');

        const btn = e.currentTarget;
        btn.innerHTML = "Cargando Paso 4...";
        btn.style.pointerEvents = "none";

        window.location.href = `/admin/contrato2/${idReservacion}`;
    });

    window.$("#back1")?.addEventListener("click", () => window.showStep(1));
    window.$("#back2")?.addEventListener("click", () => window.showStep(2));
    window.$("#back3")?.addEventListener("click", () => window.showStep(3));

    let pasoGuardado = localStorage.getItem(`contratoPasoActual_${window.ID_RESERVACION}`);
    pasoGuardado = [1, 2, 3].includes(Number(pasoGuardado)) ? Number(pasoGuardado) : 1;

    setTimeout(() => {
        window.showStep(pasoGuardado);
    }, 50);

});