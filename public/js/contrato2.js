// Contrato2
// Pasos 4 al 6

document.addEventListener("DOMContentLoaded", () => {
    console.log("✅ DOM listo, iniciando navegación de pasos (4-6)...");

    // ======================================= PASO 4: VEHICULO ===========================
    
    const btnEditarVeh = window.$("#editVeh");
    const modalVeh = window.$("#modalVehiculos");
    const listVeh = window.$("#listaVehiculos");
    const selectVehAssign = window.$("#vehAssign");
    const selectCatModal = window.$("#selectCategoriaModal");

    const abrirModalVehiculo = (e) => {
        if (e) e.preventDefault();
        if (!modalVeh) return;

        modalVeh.classList.add("show-modal");

        const selectCat = document.getElementById("selectCategoriaModal");

        if (selectCat && (!selectCat.value || selectCat.value === "")) {
            const app = document.getElementById("contratoApp");
            const catActual = window.ContratoStore?.get('categoriaElegida') || app?.dataset.idCategoria;
            selectCat.value = catActual;
        }

        const catId = selectCat ? selectCat.value : null;
        if (catId) cargarVehiculosParaModal(catId);
    };

    // Solo un listener para el botón, eliminando duplicados
    btnEditarVeh?.addEventListener("click", abrirModalVehiculo);

    selectCatModal?.addEventListener("change", async (e) => {
        const idCat = e.target.value;
        if (!idCat) return;

        const uiVehiculo = document.getElementById("vehiculoAsignadoUI");

        if (uiVehiculo) {
            uiVehiculo.innerHTML = `
                <div style="
                    padding:15px;
                    background:#f8fafc;
                    border:1px dashed #cbd5e1;
                    border-radius:8px;
                    text-align:center;
                    color:#475569;
                    font-size:13px;">
                    🚗 Selecciona un vehículo para esta categoría
                </div>`;
        }

        if (selectVehAssign) {
            selectVehAssign.innerHTML = `<option value="">Seleccione un vehículo</option>`;
        }

        if (window.ContratoStore) {
            window.ContratoStore.set('vehiculoAsignado', null);
        }

        ["#detModelo", "#detMarca", "#detTransmision", "#detPasajeros", "#detPuertas", "#detKm", "#resumenVehCompacto"].forEach(id => {
            const el = document.querySelector(id);
            if (el) el.innerText = "—";
        });
        const imgLateral = document.getElementById("resumenImgVeh");
        if (imgLateral) imgLateral.src = "/img/default-car.png";

        try {
            const resp = await fetch(`/admin/contrato/${window.ID_RESERVACION}/actualizar-categoria`, {
                method: "POST",
                headers: { "Content-Type": "application/json", "X-CSRF-TOKEN": window.csrfToken },
                body: JSON.stringify({ id_categoria: idCat })
            });

            if (resp.ok) {
                const app = document.getElementById("contratoApp");
                if (app) app.dataset.idCategoria = idCat;

                if (window.cargarResumenBasico) await window.cargarResumenBasico();
                cargarVehiculosParaModal(idCat);
            }
        } catch (err) { console.error("Error actualizando categoría:", err); }
    });

    window.$("#cerrarModalVehiculos")?.addEventListener("click", () => modalVeh.classList.remove("show-modal"));
    window.$("#cerrarModalVehiculos2")?.addEventListener("click", () => modalVeh.classList.remove("show-modal"));

    async function cargarVehiculosParaModal(idCategoria) {
        if (!listVeh) return;

        let finalId = (typeof idCategoria === 'object') ? idCategoria.target?.value : idCategoria;
        if (!finalId) finalId = document.getElementById("selectCategoriaModal")?.value;

        if (!finalId || finalId == 0) {
            listVeh.innerHTML = "<p style='padding:20px; text-align:center;'>⚠️ Por favor selecciona una categoría válida.</p>";
            return;
        }

        listVeh.innerHTML = "<p style='padding:20px; text-align:center;'>🔍 Buscando vehículos...</p>";

        try {
            const resp = await fetch(`/admin/contrato/vehiculos-por-categoria/${finalId}`);
            const data = await resp.json();

            if (data.success && data.data.length > 0) {
                renderVehiculosEnModal(data.data);
            } else {
                listVeh.innerHTML = `<div style="padding:40px; text-align:center; color:#666;"><p>No hay unidades disponibles en esta categoría.</p></div>`;
            }
        } catch (e) {
            console.error("Error Fetch Vehículos:", e);
            listVeh.innerHTML = "<p style='padding:20px; color:red; text-align:center;'>Error de conexión con la base de datos.</p>";
        }
    }

    function renderVehiculosEnModal(lista) {
        const cont = document.getElementById("listaVehiculos");
        if (!cont) return;

        cont.innerHTML = "";

        if (!lista || lista.length === 0) {
            cont.innerHTML = `<p style="padding:20px; text-align:center; color:#555;">No hay vehículos disponibles en esta categoría.</p>`;
            return;
        }

        let htmlContent = "";

        lista.forEach((v) => {
            const g = v.gasolina_actual ?? 0;
            const filled = "█".repeat(g);
            const empty = "░".repeat(16 - g);
            const barraGas = `${filled}${empty}`;
            const comunes = { 2: "1/8", 4: "1/4", 6: "3/8", 8: "1/2", 10: "5/8", 12: "3/4", 14: "7/8", 16: "1" };
            const fraccionComun = comunes[g] ? ` – ${comunes[g]}` : "";

            let iconMant = "⚪";
            if (v.color_mantenimiento === "verde") iconMant = "🟢";
            if (v.color_mantenimiento === "amarillo") iconMant = "🟡";
            if (v.color_mantenimiento === "rojo") iconMant = "🔴";
            const kmRest = v.km_restantes !== null ? `${v.km_restantes} km restantes` : "—";

            const placaVehiculo = v.placa ? v.placa : "Sin Placa";
            const vigenciaPoliza = v.fin_vigencia_poliza ? v.fin_vigencia_poliza : "No registrada";

            htmlContent += `
            <div class="vehiculo-card" style="display:flex; gap:15px; margin-bottom:12px; padding:15px; border:1px solid var(--stroke); border-radius:8px; align-items: center; background:#fff;">
                <img src="${v.foto_url ?? '/img/default-car.png'}" style="width:120px; height:85px; object-fit:cover; border-radius:6px; border:1px solid #eee;">
                
                <div class="vehiculo-info" style="flex:1;">
                    <h4 style="margin:0 0 4px; font-size:16px; color:#333;">${v.nombre_publico || (v.marca + ' ' + v.modelo)}</h4>
                    
                    <p style="margin:0 0 6px 0; font-size:13px; color:#666;">
                        ${v.transmision} · ${v.asientos} asientos · ${v.puertas} puertas <br>
                        Color: ${v.color ?? "—"} | Placa: <b style="background:#f1f5f9; border:1px solid #cbd5e1; padding:2px 6px; border-radius:4px; color:#0f172a;">${placaVehiculo}</b>
                    </p>
                    
                    <p style="margin:2px 0; font-size:12px; font-family: monospace; color:#059669;"><b>Gasolina:</b> ${barraGas} <span>(${g}/16${fraccionComun})</span></p>
                    <p style="margin:2px 0; font-size:12px; color:#444;"><b>Kilometraje:</b> ${v.kilometraje?.toLocaleString() ?? "—"} km</p>
                    <p style="margin:2px 0; font-size:11px; color:#777;"><b>Mant:</b> ${iconMant} ${kmRest} | <b>Póliza:</b> ${vigenciaPoliza}</p>
                </div>

                <div>
                    <button type="button" class="btn primary btn-seleccionar-unidad" data-id="${v.id_vehiculo}" style="padding: 8px 16px; cursor:pointer;">
                        Seleccionar
                    </button>
                </div>
            </div>`;
        });

        cont.innerHTML = htmlContent;

        cont.querySelectorAll(".btn-seleccionar-unidad").forEach(btn => {
            btn.onclick = function () {
                const id = this.getAttribute("data-id");
                if (typeof asignarVehiculo === 'function') {
                    asignarVehiculo(id);
                }
            };
        });
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

                // Actualiza select oculto
                if (selectVehAssign) {
                    selectVehAssign.innerHTML = `<option value="${data.vehiculo.id_vehiculo}" selected>${data.vehiculo.placa}</option>`;
                }

                // Actualiza Tarjeta Visual en el paso 4
                const uiVehiculo = document.getElementById("vehiculoAsignadoUI");
                if (uiVehiculo) {
                    uiVehiculo.innerHTML = `
                        <div style="display:flex; align-items:center; gap:15px; padding:12px; background:#f0fdf4; border:1px solid #bbf7d0; border-radius:8px;">
                            <div style="font-size:24px; background:#fff; padding:8px; border-radius:50%; box-shadow:0 2px 4px rgba(0,0,0,0.05);">✅</div>
                            <div>
                                <h4 style="margin:0; font-size:15px; color:#166534;">${data.vehiculo.marca} ${data.vehiculo.modelo}</h4>
                                <p style="margin:4px 0 0 0; font-size:12px; color:#166534;">Placa: <b>${data.vehiculo.placa}</b> | Color: ${data.vehiculo.color}</p>
                            </div>
                        </div>`;
                }

                // Refresca todo el resumen lateral mágicamente
                if (window.cargarResumenBasico) {
                    await window.cargarResumenBasico();
                }
            } else {
                alertify.error("Error: " + (data.error || "No se pudo asignar"));
            }
        } catch (e) { alertify.error("Error de conexión"); }
    }

    // ======================================= DETECTAR CONDUCTOR MENOR (DINÁMICO) ===========================

    const EDAD_MINIMA = 24;
    let ultimoEstadoMenor = null;

    function calcularEdad(fecha) {
        if (!fecha || fecha.length !== 10) return 99;
        const hoy = new Date();
        const nacimiento = new Date(fecha);
        if (isNaN(nacimiento.getTime())) return 99;
        let edad = hoy.getFullYear() - nacimiento.getFullYear();
        const m = hoy.getMonth() - nacimiento.getMonth();
        if (m < 0 || (m === 0 && hoy.getDate() < nacimiento.getDate())) { edad--; }
        return edad;
    }

    async function apiGuardarServicioExtra(idServicio, bodyData = {}) {
        if (!idServicio || idServicio === 0) return;
        try {
            const resp = await fetch('/admin/contrato/servicios-extra', {
                method: "POST",
                headers: { "Content-Type": "application/json", "X-CSRF-TOKEN": window.csrfToken },
                body: JSON.stringify({
                    id_reservacion: window.ID_RESERVACION,
                    id_servicio: idServicio,
                    forzar: bodyData.forzar
                }),
            });

            if (window.cargarResumenBasico) await window.cargarResumenBasico();
            if (typeof recalcularTotalPaso4 === 'function') recalcularTotalPaso4();

        } catch (err) { console.error(" Error de red:", err); }
    }

    async function verificarCualquierConductorMenor() {
        const inputsNacimiento = document.querySelectorAll('input[name*="[fecha_nacimiento]"]');
        let contadorMenores = 0;
        let nombresMenores = [];

        inputsNacimiento.forEach(input => {
            const edad = calcularEdad(input.value);
            const bloque = input.closest('.bloque-conductor-individual') || input.closest('.bloque-conductor-registro') || input.closest('.body');
            const inputNombre = bloque ? bloque.querySelector('input[name*="[nombre]"]') : null;
            const nombrePersona = inputNombre ? inputNombre.value : "Conductor";

            if (edad < EDAD_MINIMA) {
                contadorMenores++;
                nombresMenores.push(nombrePersona || "Adicional");
                input.style.border = "2px solid #ef4444";
                input.style.backgroundColor = "#fef2f2";
            } else {
                input.style.border = "";
                input.style.backgroundColor = "";
            }
        });

        if (window.ultimaCantidadMenores !== contadorMenores) {
            window.ultimaCantidadMenores = contadorMenores;

            const idServicio = window.ID_SERVICIO_MENOR || 0;
            const accion = contadorMenores > 0 ? "on" : "off";

            try {
                const resp = await fetch('/admin/contrato/servicios-extra', {
                    method: "POST",
                    headers: { "Content-Type": "application/json", "X-CSRF-TOKEN": window.csrfToken },
                    body: JSON.stringify({
                        id_reservacion: window.ID_RESERVACION,
                        id_servicio: idServicio,
                        forzar: accion,
                        cantidad: contadorMenores // <--- Nueva variable
                    }),
                });

                if (resp.ok) {
                    if (window.cargarResumenBasico) await window.cargarResumenBasico();

                    if (contadorMenores > 0) {
                        alertify.warning(`🔞 <b>Cargo Actualizado</b><br>${contadorMenores} menores detectados: ${nombresMenores.join(", ")}`);
                    } else {
                        alertify.success("✅ <b>Cargo Removido</b><br>Ya no hay conductores menores.");
                    }
                }
            } catch (err) {
                console.error("Error en fetch menores:", err);
            }
        }
    }
    
    let cazadorDeFechas = setInterval(() => {
        const inputsNacimiento = document.querySelectorAll('input[name*="[fecha_nacimiento]"]');
        if (inputsNacimiento.length > 0) {
            inputsNacimiento.forEach(input => {
                if (!input.dataset.listenerActive) {
                    input.addEventListener("change", verificarCualquierConductorMenor);
                    input.addEventListener("blur", verificarCualquierConductorMenor);
                    input.dataset.listenerActive = "true";
                }
            });
        }
    }, 1000);

    // ======================================= PASO 4: CARGOS Y DROPOFF ===========================

    const totalCargos = window.$("#total_cargos"), cargosGrid = window.$("#cargosGrid");
    const switchGasLit = window.$("#switchGasLit"), gasInputs = window.$("#gasLitrosInputs");
    const gasPrecio = window.$("#gasPrecioL"), gasCant = window.$("#gasCantL"), gasTotalHTML = window.$("#gasTotalHTML");

    const dropFields = {
        switch: window.$("#switchDropoff"), wrap: window.$("#dropoffFields"),
        ub: window.$("#dropUbicacion"), dir: window.$("#dropDireccion"),
        km: window.$("#dropKm"), costoHTML: window.$("#dropCostoKmHTML"),
        costoBox: window.$("#dropCostoKm"), grpDir: window.$("#dropGroupDireccion"),
        grpKm: window.$("#dropGroupKm"), totalHTML: window.$("#dropTotalHTML")
    };

    const card6 = document.querySelector('.cargo-item[data-id="6"]');
    window.dropoffTotal = parseFloat(card6?.dataset.monto || 0);

    document.querySelectorAll(".cargo-item").forEach(card => {
        const sw = card.querySelector(".switch");
        if (sw && sw.classList.contains("on")) card.classList.add("active");
    });

    const toggleCargoState = (id) => document.querySelector(`.cargo-item[data-id="${id}"] .switch`)?.classList.add("on");
    const updateCargoMonto = (id, monto) => {
        const card = document.querySelector(`.cargo-item[data-id="${id}"]`);
        if (card) card.dataset.monto = monto;
    };

    async function apiGuardarCargo(idConcepto, bodyData = {}) {
        const payload = {
            id_contrato: window.ID_CONTRATO || null,
            id_reservacion: window.ID_RESERVACION,
            id_concepto: idConcepto,
            ...bodyData
        };
        const url = bodyData.monto_variable !== undefined ? '/admin/contrato/cargo-variable' : '/admin/contrato/cargos';
        try {
            const resp = await fetch(url, {
                method: "POST",
                headers: { "Content-Type": "application/json", "X-CSRF-TOKEN": window.csrfToken },
                body: JSON.stringify(payload),
            });
            const data = await resp.json();
            if (!data.success && !data.status) {
                console.error("Error actualizando cargo:", data);
            }
        } catch (err) { console.error("❌ Error guardando cargo:", err); }
    }

    function recalcularTotalPaso4() {

        if (!totalCargos) return;

        totalCargos.style.opacity = "0.5";
        let t = window.dropoffTotal + (parseFloat(gasCant?.value || 0) * parseFloat(gasPrecio?.value || 0));

        document.querySelectorAll(".cargo-item .switch.on").forEach(sw => {
            const card = sw.closest(".cargo-item");
            const id = card.dataset.id;
            if (!["5", "6"].includes(id)) {
                t += parseFloat(card.dataset.monto || 0);
            }
        });

        totalCargos.textContent = window.money ? window.money(t) : `$${t.toFixed(2)} MXN`;
        totalCargos.style.opacity = "1";

        setTimeout(() => {
            if (typeof window.cargarResumenBasico === 'function') {
                window.cargarResumenBasico();
            }
        }, 150);
    }

    cargosGrid?.addEventListener("click", (e) => {
        const sw = e.target.closest(".switch");
        if (!sw) return;

        const card = sw.closest(".cargo-item");
        const isOn = sw.classList.toggle("on");

        if (card) card.classList.toggle("active", isOn);
        apiGuardarCargo(card.dataset.id).then(recalcularTotalPaso4);
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
                apiGuardarCargo(5, { litros: 0, precio_litro: 0, monto_variable: 0 }).then(() => apiGuardarCargo(2));
            }
            gasInputs.style.display = isOn ? "block" : "none";
            recalcularTotalPaso4();
        });
    }

    if (gasCant && gasPrecio) {
        const updateGas = () => {
            const total = (parseFloat(gasCant.value || 0) * parseFloat(gasPrecio.value || 0));
            gasTotalHTML.textContent = window.money ? window.money(total) : `$${total.toFixed(2)} MXN`;
            updateCargoMonto(5, total);
            apiGuardarCargo(5, { litros: parseFloat(gasCant.value || 0), precio_litro: parseFloat(gasPrecio.value || 0), monto_variable: total })
                .then(recalcularTotalPaso4);
        };
        gasCant.addEventListener("input", updateGas);
        gasPrecio.addEventListener("input", updateGas);
    }

    const handleDropoffUpdate = () => {
        if (!dropFields.ub) return;

        let precioKmActual = parseFloat(document.getElementById("deliveryPrecioKm")?.value || 15);

        const val = dropFields.ub.value, isCustom = val === "0";

        dropFields.grpDir.style.display = isCustom ? "block" : "none";
        dropFields.grpKm.style.display = isCustom ? "block" : "none";
        dropFields.costoBox.style.display = val === "" ? "none" : "block";

        if (val !== "") {
            dropFields.costoHTML.innerText = window.money ? window.money(precioKmActual) : `$${precioKmActual.toFixed(2)}`;
        }

        let kms = isCustom
            ? parseFloat(dropFields.km.value || 0)
            : parseFloat(dropFields.ub.options[dropFields.ub.selectedIndex]?.dataset.km || 0);

        window.dropoffTotal = kms * precioKmActual;
        if (dropFields.totalHTML) {
            dropFields.totalHTML.textContent = window.money ? window.money(window.dropoffTotal) : `$${window.dropoffTotal.toFixed(2)} MXN`;
        }
        updateCargoMonto(6, window.dropoffTotal);

        apiGuardarCargo(6, {
            destino: isCustom ? dropFields.dir.value : dropFields.ub.options[dropFields.ub.selectedIndex]?.text,
            km: kms,
            precio_km: precioKmActual,
            monto_variable: window.dropoffTotal
        }).then(recalcularTotalPaso4);
    };

    dropFields.switch?.addEventListener("click", () => {
        const isOn = dropFields.switch.classList.toggle("on");
        dropFields.wrap.style.display = isOn ? "block" : "none";
        if (!isOn) {
            dropFields.ub.value = ""; dropFields.dir.value = ""; dropFields.km.value = "";
            if (dropFields.totalHTML) dropFields.totalHTML.textContent = "$0.00 MXN";
            window.dropoffTotal = 0; updateCargoMonto(6, 0);
        }
        apiGuardarCargo(6).then(recalcularTotalPaso4);
    });

    dropFields.wrap?.addEventListener("input", (e) => { if (['dropKm', 'dropDireccion'].includes(e.target.id)) handleDropoffUpdate(); });
    dropFields.ub?.addEventListener("change", handleDropoffUpdate);

    // ======================================= PREVIEW DE IMÁGENES (DINÁMICO) ===========================
    document.querySelectorAll(".uploader input[type='file']").forEach(input => {
        input.addEventListener("change", (e) => {
            const file = e.target.files[0];
            if (!file) return;

            const uploader = e.target.closest(".uploader");
            // Buscamos el div 'preview' que está justo después del contenedor uploader
            const previewDiv = uploader.nextElementSibling;

            if (!previewDiv || !previewDiv.classList.contains("preview")) return;

            if (!file.type.startsWith("image/")) {
                previewDiv.innerHTML = `<p style="font-size:12px;color:#666;">Archivo seleccionado</p>`;
                return;
            }

            const reader = new FileReader();
            reader.onload = function (ev) {
                previewDiv.innerHTML = `
                    <div style="position:relative;display:inline-block;margin-top:10px;">
                        <img src="${ev.target.result}" style="width:120px;height:120px;object-fit:cover;border-radius:8px;border:1px solid #e5e7eb;">
                        <button type="button" style="position:absolute;top:-8px;right:-8px;background:#ef4444;color:white;border:none;border-radius:50%;width:22px;height:22px;cursor:pointer;" 
                            onclick="this.parentElement.remove();">×</button>
                    </div>`;
            };
            reader.readAsDataURL(file);
        });
    });

    // ======================================= PASO 5: GUARDADO MASIVO ===========================

    const formDoc = document.getElementById("formDocumentacion");

    formDoc?.addEventListener("submit", async (e) => {
        e.preventDefault();
        const btnContinuar = document.getElementById("btnContinuarDoc");

        const allFiles = formDoc.querySelectorAll('input[type="file"]');
        let faltantes = 0;

        allFiles.forEach(inp => {
            if (inp.files.length === 0) {
                faltantes++;
                inp.closest('.uploader').style.border = "1px solid #ef4444";
            } else {
                inp.closest('.uploader').style.border = "none";
            }
        });

        if (faltantes > 0) {
            alertify.error(`<b>⚠️ Faltan Documentos</b><br>Debes subir las ${faltantes} fotos de todos los conductores.`);
            return;
        }

        btnContinuar.disabled = true;
        btnContinuar.innerText = "Subiendo archivos de todos los conductores...";

        try {
            const fd = new FormData(formDoc);
            const resp = await fetch(formDoc.action, {
                method: "POST",
                body: fd,
                headers: { 'X-CSRF-TOKEN': window.csrfToken }
            });

            const res = await resp.json();

            if (res.success) {
                alertify.success("¡Documentación completa guardada!");
                // Avanzamos al Paso 6
                window.showStep(6);
                if (typeof window.cargarPaso6 === 'function') window.cargarPaso6();
                if (window.cargarResumenBasico) window.cargarResumenBasico();
            } else {
                alertify.error(res.error || "Error al guardar");
                btnContinuar.disabled = false;
                btnContinuar.innerText = "Guardar y Continuar →";
            }
        } catch (err) {
            console.error(err);
            alertify.error("Error de conexión");
            btnContinuar.disabled = false;
        }
    });

    // ======================================= PASO 6: PAGOS ===========================

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
            setTxt("#baseAmt", window.money ? window.money(r.base.total) : r.base.total);
            setTxt("#addsAmt", window.money ? window.money(r.adicionales.total) : r.adicionales.total);
            setTxt("#ivaAmt", window.money ? window.money(r.totales.subtotal) : r.totales.subtotal);
            setTxt("#ivaOnly", window.money ? window.money(r.totales.iva) : r.totales.iva);
            setTxt("#totalContrato", window.money ? window.money(r.totales.total_contrato) : r.totales.total_contrato);
            setTxt("#saldoPendiente", window.money ? window.money(r.totales.saldo_pendiente) : r.totales.saldo_pendiente);

            payBody.innerHTML = "";
            if (!r.pagos || r.pagos.length === 0) {
                payBody.innerHTML = `<tr><td colspan="6" style="text-align:center;color:#667085">NO EXISTEN PAGOS REGISTRADOS</td></tr>`;
            } else {
                r.pagos.forEach((p, idx) => {
                    const montoFormat = window.money ? window.money(p.monto) : p.monto;
                    payBody.innerHTML += `
                        <tr>
                            <td>${idx + 1}</td>
                            <td>${p.fecha}</td>
                            <td>${p.tipo}</td>
                            <td>${p.origen}</td>
                            <td><b>${montoFormat}</b></td>
                            <td><button class="btn small gray btn-del-pago" data-del="${p.id_pago}">✕</button></td>
                        </tr>`;
                });
                window.$$(".btn-del-pago").forEach(btn => {
                    btn.addEventListener("click", () => {
                        alertify.confirm(
                            "Eliminar Pago",
                            "¿Estás seguro de que deseas eliminar este pago? Esta acción no se puede deshacer.",
                            async function () {
                                btn.disabled = true;
                                btn.innerText = "...";

                                try {
                                    const res = await fetch(`/admin/contrato/pagos/${btn.dataset.del}/eliminar`, {
                                        method: "DELETE",
                                        headers: {
                                            "Content-Type": "application/json",
                                            "Accept": "application/json", // VITAL para Laravel
                                            "X-CSRF-TOKEN": window.csrfToken
                                        }
                                    });

                                    const data = await res.json();

                                    if (res.ok && data.success !== false) {
                                        alertify.success("Pago eliminado correctamente.");
                                        window.cargarPaso6();
                                        window.cargarResumenBasico();
                                    } else {
                                        console.error("Rechazo del servidor:", data);
                                        alertify.error(data.msg || "El servidor rechazó la eliminación.");
                                        btn.disabled = false;
                                        btn.innerText = "✕";
                                    }
                                } catch (e) {
                                    console.error("Error Fetch Eliminar Pago:", e);
                                    alertify.error("Error de conexión al intentar eliminar.");
                                    btn.disabled = false;
                                    btn.innerText = "✕";
                                }
                            },
                            function () {
                            }
                        ).set('labels', { ok: 'Sí, eliminar', cancel: 'Cancelar' });
                    });
                });
            }
        } catch (e) { console.error("❌ Error P6:", e); }
    };

    const obtenerMontoPendiente = () => parseFloat((window.$("#saldoPendiente")?.textContent || "").replace(/[^\d.]/g, "")) || parseFloat((window.$("#totalContrato")?.textContent || "").replace(/[^\d.]/g, "")) || 0;

    window.$("#btnAdd")?.addEventListener("click", () => {
        mb.classList.add("show");
        window.$("#pMonto").value = obtenerMontoPendiente().toFixed(2);
        activarTab("paypal");
    });

    window.$("#mx")?.addEventListener("click", cerrarModalPago);
    function cerrarModalPago() {
        mb.classList.remove("show");
        if (window.$("#pMonto")) window.$("#pMonto").value = "";
        if (window.$("#pNotes")) window.$("#pNotes").value = "";
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

                    const payload = {
                        id_reservacion: window.ID_RESERVACION,
                        order_id: order.id,
                        monto: monto,
                        origen: "en linea",
                        metodo: "PAYPAL"
                    };

                    await fetch(`/admin/contrato/pagos/paypal`, {
                        method: "POST",
                        headers: { "Content-Type": "application/json", "X-CSRF-TOKEN": window.csrfToken },
                        body: JSON.stringify(payload)
                    });

                    cerrarModalPago(); window.cargarPaso6(); window.cargarResumenBasico();
                    if (window.alertify) alertify.success("Pago con PayPal exitoso.");
                },
                onError: () => { if (window.alertify) alertify.error("Error al procesar PayPal") },
            }).render("#paypal-button-container-modal");
        } catch (e) { console.error("❌ Error PayPal:", e); }
    }

    // Guardar Pago Manual
    window.$("#pSave")?.addEventListener("click", async () => {
        const tabActiva = window.$("#payTabs .tab.active")?.dataset.tab || "";
        let metodo = window.document.querySelector("[name='m']:checked")?.value || tabActiva || "EFECTIVO";
        metodo = metodo.toUpperCase();

        let origenDePago = "mostrador";

        if (metodo.includes("PAYPAL") || tabActiva === "paypal") {
            origenDePago = "en linea";
            metodo = "PAYPAL";
        } else if (metodo.includes("TERMINAL") || metodo.includes("TARJETA") || tabActiva === "terminal" || tabActiva === "tarjeta") {
            origenDePago = "terminal";
            metodo = "TERMINAL";
        } else if (metodo.includes("TRANSFERENCIA") || metodo.includes("DEPÓSITO") || tabActiva === "transferencia") {
            origenDePago = "transferencia";
            metodo = "TRANSFERENCIA";
        } else {
            origenDePago = "mostrador";
            metodo = "EFECTIVO";
        }

        // Validación del input de archivo dependiendo de la pestaña
        const fileInput = (origenDePago === "terminal" || tabActiva === "tarjeta" || tabActiva === "terminal") ? window.$("#fileTerminal") :
            (origenDePago === "transferencia") ? window.$("#fileTransfer") : null;

        if ((origenDePago === "terminal" || origenDePago === "transferencia") && !fileInput?.files[0]) {
            const errBox = window.$("#pErr");
            if (errBox) errBox.innerText = "Debes subir el comprobante.";
            return;
        }

        const fd = new FormData();
        fd.append("id_reservacion", window.ID_RESERVACION);
        fd.append("tipo_pago", window.$("#pTipo")?.value || "PAGO RESERVACIÓN");
        fd.append("monto", window.$("#pMonto")?.value || 0);
        fd.append("notas", window.$("#pNotes")?.value || "");

        fd.append("metodo", metodo);
        fd.append("origen", origenDePago);
        fd.append("_token", window.csrfToken);
        if (fileInput?.files[0]) fd.append("comprobante", fileInput.files[0]);

        try {
            const btnSave = window.$("#pSave");
            btnSave.disabled = true;
            btnSave.innerText = "Guardando...";

            const res = await fetch(`/admin/contrato/pagos/agregar`, { method: "POST", body: fd });
            const data = await res.json();

            if (data.ok) {
                cerrarModalPago();
                window.cargarPaso6();
                window.cargarResumenBasico();
                if (window.alertify) alertify.success("Pago guardado exitosamente.");
            } else {
                const errBox = window.$("#pErr");
                if (errBox) errBox.innerText = data.msg || "Error al guardar.";
            }
        } catch (e) {
            const errBox = window.$("#pErr");
            if (errBox) errBox.innerText = "Error de conexión al guardar el pago.";
        } finally {
            const btnSave = window.$("#pSave");
            btnSave.disabled = false;
            btnSave.innerText = "GUARDAR PAGO";
        }
    });

    // ======================================= NAVEGACIÓN ===========================

    window.$("#btnSaltarDoc")?.addEventListener("click", () => {
        window.showStep(6);
        setTimeout(() => { if (typeof window.cargarPaso6 === 'function') window.cargarPaso6(); }, 150);
    });

    window.$("#go5")?.addEventListener("click", () => window.showStep(5));

    window.$("#go6")?.addEventListener("click", () => {
        window.showStep(6);
        setTimeout(() => {
            if (typeof window.cargarPaso6 === 'function') window.cargarPaso6();
            if (typeof window.cargarResumenBasico === 'function') window.cargarResumenBasico();
        }, 150);
    });

    window.$("#back4")?.addEventListener("click", () => window.showStep(4));
    window.$("#back5")?.addEventListener("click", () => window.showStep(5));

    setTimeout(() => {
        window.showStep(4);

        if (dropFields.switch?.classList.contains("on")) {
            handleDropoffUpdate();
        }

        recalcularTotalPaso4();

        if (typeof window.cargarResumenBasico === 'function') {
            window.cargarResumenBasico();
        }
    }, 200);
});