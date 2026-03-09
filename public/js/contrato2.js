// Contrato2
// Pasos 4 al 6

document.addEventListener("DOMContentLoaded", () => {
    // Cambio del vehiculo
    const btnEditarVeh = window.$("#editVeh");
    const modalVeh = window.$("#modalVehiculos");
    const listVeh = window.$("#listaVehiculos");
    const selectVehAssign = window.$("#vehAssign");
    const selectCatModal = window.$("#selectCategoriaModal");

    btnEditarVeh?.addEventListener("click", () => {
        modalVeh.classList.add("show-modal");
        
        let categoriaGuardada = window.ContratoStore.get('categoriaElegida');
        
        if (categoriaGuardada && selectCatModal) {
            selectCatModal.value = categoriaGuardada;
        }
        
        const catId = selectCatModal ? selectCatModal.value : null;
        if (catId) cargarVehiculosParaModal(catId);
    });

    selectCatModal?.addEventListener("change", (e) => cargarVehiculosParaModal(e.target.value));
    window.$("#cerrarModalVehiculos")?.addEventListener("click", () => modalVeh.classList.remove("show-modal"));
    window.$("#cerrarModalVehiculos2")?.addEventListener("click", () => modalVeh.classList.remove("show-modal"));

    async function cargarVehiculosParaModal(idCategoria) {
        listVeh.innerHTML = "<p style='padding:20px;'>Cargando vehículos...</p>";
        try {
            const resp = await fetch(`/admin/contrato/vehiculos-por-categoria/${idCategoria}`);
            const data = await resp.json();

            if (data.success) {
                renderVehiculosEnModal(data.data);
            } else {
                listVeh.innerHTML = "<p style='padding:20px; text-align:center;'>Error cargando vehículos.</p>";
            }
        } catch (e) {
            listVeh.innerHTML = "<p style='padding:20px;'>Error de conexión al cargar los vehículos.</p>";
        }
    }

    function renderVehiculosEnModal(lista) {
        listVeh.innerHTML = "";

        if (!lista || lista.length === 0) {
            listVeh.innerHTML = `<p style="padding:20px; text-align:center; color:#555;">No hay vehículos disponibles en esta categoría.</p>`;
            return;
        }

        lista.forEach((v) => {
            // GASOLINA VISUAL
            const g = v.gasolina_actual ?? 0;
            const filled = "█".repeat(g);
            const empty = "░".repeat(16 - g);
            const barraGas = `${filled}${empty}`;

            const comunes = { 2: "1/8", 4: "1/4", 6: "3/8", 8: "1/2", 10: "5/8", 12: "3/4", 14: "7/8", 16: "1" };
            const fraccionComun = comunes[g] ? ` – ${comunes[g]}` : "";

            // MANTENIMIENTO
            let iconMant = "⚪";
            if (v.color_mantenimiento === "verde") iconMant = "🟢";
            if (v.color_mantenimiento === "amarillo") iconMant = "🟡";
            if (v.color_mantenimiento === "rojo") iconMant = "🔴";

            const kmRest = v.km_restantes !== null ? `${v.km_restantes} km restantes` : "—";

            listVeh.innerHTML += `
              <div class="vehiculo-card" style="display:flex; gap:15px; margin-bottom:12px; padding:15px; border:1px solid var(--stroke); border-radius:8px; align-items: center;">
                <img src="${v.foto_url ?? '/img/default-car.png'}" style="width:120px; height:85px; object-fit:cover; border-radius:6px;">
                
                <div class="vehiculo-info" style="flex:1;">
                  <h4 style="margin:0 0 4px; font-size:16px;">${v.nombre_publico || (v.marca + ' ' + v.modelo)}</h4>
                  <p style="margin:0 0 6px 0; font-size:13px; color:#666;">
                    ${v.transmision} · ${v.asientos} asientos · ${v.puertas} puertas <br>
                    Color: ${v.color ?? "—"} | Placa: <b>${v.placa ?? "—"}</b>
                  </p>
                  
                  <p style="margin:2px 0; font-size:13px; font-family: monospace;"><b>Gasolina:</b> ${barraGas} <span style="font-family: sans-serif;">(${g}/16${fraccionComun})</span></p>
                  <p style="margin:2px 0; font-size:13px;"><b>Kilometraje:</b> ${v.kilometraje?.toLocaleString() ?? "—"} km</p>
                  <p style="margin:2px 0; font-size:13px;"><b>Mantenimiento:</b> ${iconMant} ${kmRest}</p>
                </div>

                <div>
                  <button type="button" class="btn primary btn-asignar-v" data-id="${v.id_vehiculo}" style="padding: 8px 16px;">
                    Seleccionar
                  </button>
                </div>
              </div>
            `;
        });

        window.$$(".btn-asignar-v").forEach((btn) =>
            btn.addEventListener("click", () => asignarVehiculo(btn.dataset.id))
        );
    }

    async function asignarVehiculo(idVehiculo) {
        try {
            const resp = await fetch("/admin/contrato/asignar-vehiculo", {
                method: "POST",
                headers: { "Content-Type": "application/json", "X-CSRF-TOKEN": window.csrfToken },
                body: JSON.stringify({ id_reservacion: window.ID_RESERVACION, id_vehiculo: idVehiculo })
            });
            const data = await resp.json();

            if (data.success) {
                alertify.success("Vehículo asignado correctamente");
                modalVeh.classList.remove("show-modal");

                if (selectVehAssign) {
                    selectVehAssign.innerHTML = `<option value="${data.vehiculo.id_vehiculo}">${data.vehiculo.marca} ${data.vehiculo.modelo} (${data.vehiculo.placa})</option>`;
                }

                // Actualizar el precio Dropoff global
                if (data.vehiculo.precio_km_dropoff) {
                    window.ContratoStore.set('precioDropoff', parseFloat(data.vehiculo.precio_km_dropoff));

                    if (dropFields.switch?.classList.contains("on")) {
                        handleDropoffUpdate();
                    }
                }

                window.cargarResumenBasico();
            } else {
                alertify.error("Error: " + (data.error || "No se pudo asignar"));
            }
        } catch (e) { alertify.error("Error de conexión al asignar vehículo"); }
    }

    // ======================================= PASO 4 ===========================
    const totalCargos = window.$("#total_cargos"), cargosGrid = window.$("#cargosGrid");
    const switchGasLit = window.$("#switchGasLit"), gasInputs = window.$("#gasLitrosInputs");
    const gasPrecio = window.$("#gasPrecioL"), gasCant = window.$("#gasCantL"), gasTotalHTML = window.$("#gasTotalHTML");

    const dropFields = {
        switch: window.$("#switchDropoff"), wrap: window.$("#dropoffFields"),
        ub: window.$("#dropUbicacion"), dir: window.$("#dropDireccion"),
        km: window.$("#dropKm"), costoHTML: window.$("#dropCostoKmHTML"),
        costoBox: window.$("#dropCostoKm"), grpDir: window.$("#dropGroupDireccion"),
        grpKm: window.$("#dropGroupKm"), totalHTML: window.$("#dropTotal")
    };

    window.dropoffTotal = 0;

    const toggleCargoState = (id) => document.querySelector(`.cargo-item[data-id="${id}"] .switch`)?.classList.add("on");
    const updateCargoMonto = (id, monto) => {
        const card = document.querySelector(`.cargo-item[data-id="${id}"]`);
        if (card) card.dataset.monto = monto;
    };

    async function apiGuardarCargo(idConcepto, bodyData = {}) {
        const payload = { id_contrato: window.ID_CONTRATO, id_concepto: idConcepto, ...bodyData };
        const url = bodyData.monto_variable !== undefined ? '/admin/contrato/cargo-variable' : '/admin/contrato/cargos';
        try {
            const resp = await fetch(url, {
                method: "POST",
                headers: { "Content-Type": "application/json", "X-CSRF-TOKEN": window.csrfToken },
                body: JSON.stringify(payload),
            });
            const data = await resp.json();
            if (!data.success && !data.status && url === '/admin/contrato/cargos') alertify.error("Error al actualizar cargo");
        } catch (err) { console.error("❌ Error guardando cargo:", err); }
    }

    function recalcularTotalPaso4() {
        if (!totalCargos) return;

        let t = window.dropoffTotal + (parseFloat(gasCant?.value || 0) * parseFloat(gasPrecio?.value || 0));
        document.querySelectorAll(".cargo-item .switch.on").forEach(sw => {
            if (!["5", "6"].includes(sw.dataset.id)) t += parseFloat(sw.closest(".cargo-item").dataset.monto || 0);
        });

        totalCargos.textContent = `$${t.toFixed(2)} MXN`;

        setTimeout(() => {
            if (typeof window.cargarResumenBasico === 'function') {
                window.cargarResumenBasico();
            }
        }, 150);
    }

    cargosGrid?.addEventListener("click", (e) => {
        const sw = e.target.closest(".switch");
        if (!sw) return;
        sw.classList.toggle("on");
        apiGuardarCargo(sw.closest(".cargo-item").dataset.id).then(recalcularTotalPaso4);
    });

    if (switchGasLit) {
        switchGasLit.addEventListener("click", () => {
            const isOn = switchGasLit.classList.toggle("on");
            if (isOn) {
                toggleCargoState(2);
                apiGuardarCargo(2).then(() => apiGuardarCargo(5));
            } else {
                gasCant.value = ""; gasTotalHTML.textContent = "$0.00 MXN";
                updateCargoMonto(5, 0);
                apiGuardarCargo(5, { litros: 0, precio_litro: 0, monto_variable: 0 })
                    .then(() => apiGuardarCargo(2));
            }
            gasInputs.style.display = isOn ? "block" : "none";
            recalcularTotalPaso4();
        });
    }

    if (gasCant && gasPrecio) {
        gasCant.addEventListener("input", () => {
            const total = (parseFloat(gasCant.value || 0) * parseFloat(gasPrecio.value || 0));
            gasTotalHTML.textContent = `$${total.toFixed(2)} MXN`;
            updateCargoMonto(5, total);
            apiGuardarCargo(5, { litros: parseFloat(gasCant.value || 0), precio_litro: parseFloat(gasPrecio.value || 0), monto_variable: total })
                .then(recalcularTotalPaso4);
        });
    }

    const handleDropoffUpdate = () => {
        if (!dropFields.ub) return;
        
        let precioBlade = parseFloat(window.$("#deliveryPrecioKm")?.value || 15);
        let precioKmActual = window.ContratoStore.get('precioDropoff', precioBlade);

        const val = dropFields.ub.value, isCustom = val === "0";

        dropFields.grpDir.style.display = isCustom ? "block" : "none";
        dropFields.grpKm.style.display = isCustom ? "block" : "none";
        dropFields.costoBox.style.display = val === "" ? "none" : "block";

        if (val !== "") {
            dropFields.costoHTML.innerText = window.money(precioKmActual);
        }

        let kms = isCustom
            ? parseFloat(dropFields.km.value || 0)
            : parseFloat(dropFields.ub.options[dropFields.ub.selectedIndex]?.dataset.km || 0);

        window.dropoffTotal = kms * precioKmActual;
        dropFields.totalHTML.textContent = window.money(window.dropoffTotal);
        updateCargoMonto(6, window.dropoffTotal);

        apiGuardarCargo(6, {
            destino: isCustom ? dropFields.dir.value : dropFields.ub.options[dropFields.ub.selectedIndex]?.text,
            km: kms,
            precio_litro: precioKmActual,
            monto_variable: window.dropoffTotal
        }).then(recalcularTotalPaso4);
    };

    dropFields.switch?.addEventListener("click", () => {
        const isOn = dropFields.switch.classList.toggle("on");
        dropFields.wrap.style.display = isOn ? "block" : "none";
        if (!isOn) {
            dropFields.ub.value = ""; dropFields.dir.value = ""; dropFields.km.value = "";
            dropFields.totalHTML.textContent = "$0.00 MXN";
            window.dropoffTotal = 0; updateCargoMonto(6, 0);
        }
        apiGuardarCargo(6).then(recalcularTotalPaso4);
    });

    dropFields.wrap?.addEventListener("input", (e) => { if (['dropKm', 'dropDireccion'].includes(e.target.id)) handleDropoffUpdate(); });
    dropFields.ub?.addEventListener("change", handleDropoffUpdate);

    // ======================================= PASO 5 ===========================
    const formDoc = window.$("#formDocumentacion");

    // Lógica de Previsualización de Imágenes / PDFs
    window.$$('.uploader input[type="file"]').forEach((input) => {
        input.addEventListener("change", (e) => {
            const file = e.target.files[0];
            const previewId = e.target.closest(".uploader").dataset.name;
            const previewDiv = document.getElementById(`prev-${previewId}`);

            if (!file || !previewDiv) return;

            if (!file.type.startsWith('image/')) {
                previewDiv.innerHTML = `
                    <div class="thumb" style="border:1px solid var(--stroke); padding:10px; border-radius:6px; display:inline-block; position:relative;">
                        <span style="font-size:12px; font-weight:bold; color:var(--primary);">📄 PDF Seleccionado</span>
                        <button type="button" class="rm" title="Quitar" style="position:absolute; top:-10px; right:-10px; background:red; color:white; border:none; border-radius:50%; width:20px; height:20px; cursor:pointer;">×</button>
                    </div>`;
            } else {
                const reader = new FileReader();
                reader.onload = (ev) => {
                    previewDiv.innerHTML = `
                        <div class="thumb" style="position:relative; display:inline-block; margin-top:10px;">
                            <img src="${ev.target.result}" style="max-width:100%; height:120px; object-fit:cover; border-radius:6px; border:1px solid var(--stroke);">
                            <button type="button" class="rm" title="Quitar" style="position:absolute; top:-10px; right:-10px; background:red; color:white; border:none; border-radius:50%; width:24px; height:24px; cursor:pointer; font-weight:bold;">×</button>
                        </div>`;
                    previewDiv.querySelector(".rm").addEventListener("click", () => { input.value = ""; previewDiv.innerHTML = ""; });
                };
                reader.readAsDataURL(file);
            }
            setTimeout(() => {
                const rmBtn = previewDiv.querySelector(".rm");
                if (rmBtn) rmBtn.addEventListener("click", () => { input.value = ""; previewDiv.innerHTML = ""; });
            }, 50);
        });
    });

    if (formDoc && window.ID_CONTRATO) {
        formDoc.action = formDoc.action.trim() || "/admin/contrato/guardar-documentacion";
        alertify.set("notifier", "position", "top-right");

        let docsCargados = false, adicionalesTot = parseInt(formDoc.dataset.adicionales || "0", 10), idxActual = parseInt(formDoc.dataset.actual || "0", 10);
        const condAdicionales = JSON.parse(formDoc.dataset.conductores || "[]");

        const comprimirImagen = (file) => new Promise((resolve) => {
            const img = new Image(), reader = new FileReader();
            reader.onload = e => img.src = e.target.result;
            img.onload = () => {
                const cvs = document.createElement("canvas"), sc = Math.min(1400 / img.width, 1);
                cvs.width = img.width * sc; cvs.height = img.height * sc;
                cvs.getContext("2d").drawImage(img, 0, 0, cvs.width, cvs.height);
                cvs.toBlob(b => resolve(new File([b], file.name.replace(/\.\w+$/, ".jpg"), { type: "image/jpeg" })), "image/jpeg", 0.7);
            };
            reader.readAsDataURL(file);
        });

        formDoc.addEventListener("submit", async (e) => {
            e.preventDefault();
            const filesInputs = Array.from(formDoc.querySelectorAll("input[type='file']"));
            if (!filesInputs.some(i => i.files.length > 0) && !docsCargados) return alertify.warning("📁 Selecciona al menos un archivo.");

            const btn = formDoc.querySelector("button[type='submit']");
            if (btn) { btn.disabled = true; btn.textContent = "Subiendo... ⏳"; }

            const fd = new FormData(formDoc);
            fd.set("id_contrato", window.ID_CONTRATO);

            for (let input of filesInputs) {
                if (input.files[0]) {
                    const file = input.files[0];
                    if (file.type.startsWith('image/')) {
                        fd.set(input.name, await comprimirImagen(file));
                    } else {
                        fd.set(input.name, file);
                    }
                }
            }

            try {
                const res = await fetch(formDoc.action, { method: "POST", headers: { "X-CSRF-TOKEN": window.csrfToken }, body: fd });
                const data = await res.json();
                if (!res.ok || data.error) throw new Error(data.error || "Backend error");

                alertify.success("📄 Documentación guardada.");
                docsCargados = true;

                if (idxActual < adicionalesTot && condAdicionales.length > 0) {
                    idxActual++; alertify.message(`🧍‍♂️ Documentación adicional`);
                } else {
                    alertify.success("🎉 Completado. Avanzando..."); window.showStep(6);
                }
            } catch (err) { alertify.error("Error al enviar."); }
            finally { if (btn) { btn.disabled = false; btn.textContent = "Guardar documentación"; } }
        });
    }

    // ======================================= PASO 6 ===========================
    const payBody = window.$("#payBody");
    const mb = window.$("#mb");
    const payTabs = window.$("#payTabs");
    const panes = window.$$("[data-pane]");
    let paypalLoaded = false;

    window.cargarPaso6 = async () => {
        if (!window.$("#baseAmt")) return;
        try {
            const res = await fetch(`/admin/contrato/${window.ID_RESERVACION}/resumen-paso6`);
            if (!res.ok) return;
            const { ok, data: r } = await res.json();
            if (!ok) return;

            const setTxt = (sel, val) => { const el = window.$(sel); if (el) el.textContent = val; };
            setTxt("#baseDescr", r.base.descripcion ?? "—");
            setTxt("#baseAmt", window.money(r.base.total));
            setTxt("#addsAmt", window.money(r.adicionales.total));
            setTxt("#ivaAmt", window.money(r.totales.subtotal));
            setTxt("#ivaOnly", window.money(r.totales.iva));
            setTxt("#totalContrato", window.money(r.totales.total_contrato));
            setTxt("#saldoPendiente", window.money(r.totales.saldo_pendiente));

            // Renderizar la tabla de pagos
            payBody.innerHTML = "";
            if (!r.pagos || r.pagos.length === 0) {
                payBody.innerHTML = `<tr><td colspan="6" style="text-align:center;color:#667085">NO EXISTEN PAGOS REGISTRADOS</td></tr>`;
            } else {
                r.pagos.forEach((p, idx) => {
                    payBody.innerHTML += `
                        <tr>
                            <td>${idx + 1}</td>
                            <td>${p.fecha}</td>
                            <td>${p.tipo}</td>
                            <td>${p.origen}</td>
                            <td><b>${window.money(p.monto)}</b></td>
                            <td><button class="btn small gray btn-del-pago" data-del="${p.id_pago}">✕</button></td>
                        </tr>`;
                });
                window.$$(".btn-del-pago").forEach(btn => {
                    btn.addEventListener("click", async () => {
                        if (!confirm("¿Eliminar este pago?")) return;
                        try {
                            const res = await fetch(`/admin/contrato/pagos/${btn.dataset.del}/eliminar`, { method: "DELETE", headers: { "X-CSRF-TOKEN": window.csrfToken } });
                            if (res.ok) { window.cargarPaso6(); window.cargarResumenBasico(); }
                        } catch (e) { alertify.error("Error al eliminar"); }
                    });
                });
            }
        } catch (e) { console.error("❌ Error P6:", e); }
    };

    // Modal Pagos
    const obtenerMontoPendiente = () => parseFloat((window.$("#saldoPendiente")?.textContent || "").replace(/[^\d.]/g, "")) || parseFloat((window.$("#totalContrato")?.textContent || "").replace(/[^\d.]/g, "")) || 0;

    window.$("#btnAdd")?.addEventListener("click", () => {
        mb.classList.add("show");
        window.$("#pMonto").value = obtenerMontoPendiente().toFixed(2);
        activarTab("paypal");
    });

    window.$("#mx")?.addEventListener("click", cerrarModalPago);
    function cerrarModalPago() {
        mb.classList.remove("show");
        window.$("#pMonto").value = ""; window.$("#pNotes").value = "";
        if (window.$("#fileTerminal")) window.$("#fileTerminal").value = "";
        if (window.$("#fileTransfer")) window.$("#fileTransfer").value = "";
        if (window.$("#paypal-button-container-modal")) window.$("#paypal-button-container-modal").innerHTML = "";
    }

    payTabs?.addEventListener("click", (e) => {
        if (e.target.dataset.tab) activarTab(e.target.dataset.tab);
    });

    function activarTab(nombre) {
        window.$$("#payTabs .tab").forEach(t => t.classList.toggle("active", t.dataset.tab === nombre));
        panes.forEach(p => p.style.display = (p.dataset.pane === nombre) ? "block" : "none");
        if (nombre === "paypal") prepararPayPal();
        else if (window.$("#paypal-button-container-modal")) window.$("#paypal-button-container-modal").innerHTML = "";
    }

    // PayPal
    async function prepararPayPal() {
        const container = window.$("#paypal-button-container-modal");
        if (!container) return;
        try {
            if (!paypalLoaded) {
                await new Promise((resolve, reject) => {
                    const script = document.createElement("script");
                    script.src = "https://www.paypal.com/sdk/js?client-id=ATzNpaAJlH7dFrWKu91xLmCzYVDQQF5DJ51b0OFICqchae6n8Pq7XkfsOOQNnElIJMt_Aj0GEZeIkFsp&currency=MXN";
                    script.onload = resolve; script.onerror = reject;
                    document.head.appendChild(script);
                });
                paypalLoaded = true;
            }
            container.innerHTML = "";
            const monto = obtenerMontoPendiente();
            paypal.Buttons({
                style: { color: "gold", shape: "pill", label: "pay", height: 40 },
                createOrder: (data, actions) => actions.order.create({ purchase_units: [{ amount: { value: monto.toFixed(2), currency_code: "MXN" } }] }),
                onApprove: async (data, actions) => {
                    const order = await actions.order.capture();
                    await fetch(`/admin/contrato/pagos/paypal`, { method: "POST", headers: { "Content-Type": "application/json", "X-CSRF-TOKEN": window.csrfToken }, body: JSON.stringify({ id_reservacion: window.ID_RESERVACION, order_id: order.id, monto }) });
                    cerrarModalPago(); window.cargarPaso6(); window.cargarResumenBasico(); alertify.success("Pago con PayPal exitoso.");
                },
                onError: () => alertify.error("Error al procesar PayPal"),
            }).render("#paypal-button-container-modal");
        } catch (e) { console.error("❌ Error PayPal:", e); }
    }

    // Guardar Pago Manual
    window.$("#pSave")?.addEventListener("click", async () => {
        const tabActiva = window.$("#payTabs .tab.active")?.dataset.tab;
        const metodo = window.document.querySelector("[name='m']:checked")?.value || "EFECTIVO";
        const fileInput = tabActiva === "tarjeta" ? window.$("#fileTerminal") : (tabActiva === "transferencia" ? window.$("#fileTransfer") : null);

        if ((tabActiva === "tarjeta" || tabActiva === "transferencia") && !fileInput?.files[0]) {
            return window.$("#pErr").innerText = "Debes subir el comprobante.";
        }

        const fd = new FormData();
        fd.append("id_reservacion", window.ID_RESERVACION);
        fd.append("tipo_pago", window.$("#pTipo")?.value || "PAGO RESERVACIÓN");
        fd.append("monto", window.$("#pMonto")?.value || 0);
        fd.append("notas", window.$("#pNotes")?.value || "");
        fd.append("metodo", metodo);
        fd.append("_token", window.csrfToken);
        if (fileInput?.files[0]) fd.append("comprobante", fileInput.files[0]);

        try {
            const res = await fetch(`/admin/contrato/pagos/agregar`, { method: "POST", body: fd });
            const data = await res.json();
            if (data.ok) {
                cerrarModalPago(); window.cargarPaso6(); window.cargarResumenBasico(); alertify.success("Pago guardado.");
            } else { window.$("#pErr").innerText = data.msg || "Error al guardar."; }
        } catch (e) { window.$("#pErr").innerText = "Error de conexión al guardar el pago."; }
    });

    // Navegacion del paso 4 al 6
    window.$("#btnSaltarDoc")?.addEventListener("click", () => window.showStep(6));
    window.$("#go5")?.addEventListener("click", () => window.showStep(5));
    window.$("#go6")?.addEventListener("click", () => {
        window.showStep(6);

        setTimeout(() => {
            window.cargarPaso6();
            window.cargarResumenBasico();
        }, 150);
    });
    window.$("#back4")?.addEventListener("click", () => window.showStep(4));
    window.$("#back5")?.addEventListener("click", () => window.showStep(5));

    let pasoGuardado = 4;
    if (window.ID_RESERVACION) {
        const guardado = localStorage.getItem(`contratoPasoActual_${window.ID_RESERVACION}`);
        if ([4, 5, 6].includes(Number(guardado))) pasoGuardado = Number(guardado);
    }
    window.showStep(pasoGuardado);
});