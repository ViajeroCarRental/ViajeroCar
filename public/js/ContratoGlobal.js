/**
 * ContratoGlobal.js
 * Lógica compartida para la Gestión de Contratos (Pasos 1 al 6)
 */

window.ContratoStore = {
    set: function (key, value) {
        sessionStorage.setItem('global_contrato_' + key, JSON.stringify(value));
    },
    get: function (key, defaultValue = null) {
        const item = sessionStorage.getItem('global_contrato_' + key);
        return item ? JSON.parse(item) : defaultValue;
    },
    clear: function () {
        Object.keys(sessionStorage).forEach(key => {
            if (key.startsWith('global_contrato_')) sessionStorage.removeItem(key);
        });
    }
};

// Utilidades DOM
window.$ = (s) => document.querySelector(s);
window.$$ = (s) => Array.from(document.querySelectorAll(s));

window.formatPhone = (val) => {
    if (!val) return "—";
    let num = val.toString().replace(/\D/g, '');
    if (num.length === 12) return `+52 (${num.slice(2, 5)}) ${num.slice(5, 8)}-${num.slice(8)}`;
    else if (num.length === 10) return `(${num.slice(0, 3)}) ${num.slice(3, 6)}-${num.slice(6)}`;
    return val;
};

window.money = function (amount) {
    const number = parseFloat(amount) || 0;
    return '$' + number.toLocaleString('en-US', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
    }) + ' MXN';
};

window.listaVehiculosOriginal = [];

// Lógica de Pasos (Stepper)
window.actualizarStepper = function (pasoActual) {
    const items = window.$$('.stepper-item');
    const lines = window.$$('.stepper-line');

    items.forEach((item, index) => {
        const pasoItem = parseInt(item.getAttribute('data-step-indicator'));

        item.classList.remove('active', 'completed');
        if (lines[index]) lines[index].classList.remove('completed');

        if (pasoItem === pasoActual) {
            item.classList.add('active');
        } else if (pasoItem < pasoActual) {
            item.classList.add('completed');
            if (lines[index]) lines[index].classList.add('completed');
        }
    });
};

document.addEventListener("DOMContentLoaded", () => {
    const contratoApp = document.getElementById("contratoApp") || document.getElementById("contratoInicial");
    window.ID_CONTRATO = contratoApp?.dataset.idContrato || null;
    window.ID_RESERVACION = contratoApp?.dataset.idReservacion || null;
    window.csrfToken = document.querySelector('meta[name="csrf-token"]')?.content || "";

    // Setear hora por defecto si está vacío
    const inputHora = window.$("#nuevaHoraEntrega");
    if (inputHora && !inputHora.value) {
        const ahora = new Date();
        const hh = String(ahora.getHours()).padStart(2, '0');
        const mm = String(ahora.getMinutes()).padStart(2, '0');
        inputHora.value = `${hh}:${mm}`;

        if (window.ID_RESERVACION) {
            setTimeout(() => window.actualizarFechasYRecalcular(), 200);
        }
    }

    // ================================ MODAL DE VEHÍCULOS ===============================

    window.abrirModalVehiculos = async () => {
        const modal = window.$("#modalVehiculos");
        const idCategoria = document.getElementById("contratoInicial")?.dataset.idCategoria;

        if (!idCategoria) {
            console.error("❌ Error: No se encontró data-id-categoria");
            if (window.alertify) alertify.error("No se pudo identificar la categoría de la reservación.");
            return;
        }

        if (modal) {
            modal.style.display = "flex";
            modal.classList.add("show-modal", "active");
            document.body.style.overflow = "hidden";
        }

        await window.cargarVehiculosCategoriaModal(idCategoria);
    };

    window.cerrarModalVehiculos = () => {
        const modal = document.getElementById("modalVehiculos");
        if (modal) {
            modal.classList.remove("show", "show-modal", "active");
            modal.style.display = "none";
            document.body.style.overflow = "auto";
        }
    };

    document.getElementById("cerrarModalVehiculos")?.addEventListener("click", window.cerrarModalVehiculos);
    document.getElementById("cerrarModalVehiculos2")?.addEventListener("click", window.cerrarModalVehiculos);

    window.showStep = (n) => {
        window.$$(".step").forEach(el => el.classList.toggle("active", Number(el.dataset.step) === n));
        if (typeof window.actualizarStepper === 'function') window.actualizarStepper(n);
        if (n === 6 && typeof window.cargarPaso6 === 'function') window.cargarPaso6();
    };

    // ================================ CARGA DEL RESUMEN GLOBAL ===============================

    window.cargarResumenBasico = async () => {
        if (!window.ID_RESERVACION) return;

        const setTxt = (sel, val) => {
            window.$$(sel).forEach(el => el.textContent = val ?? "—");
        };

        const actualizarCalendarioVisual = (selector, fechaSql) => {
            if (!fechaSql) return;
            const partes = fechaSql.split("-");
            if (partes.length === 3) {
                const container = window.$(selector);
                if (container) {
                    if (container.querySelector(".dia")) container.querySelector(".dia").textContent = partes[2];
                    if (container.querySelector(".anio")) container.querySelector(".anio").textContent = partes[0];
                    const elMes = container.querySelector(".mes");
                    if (elMes) {
                        const dateObj = new Date(fechaSql + "T00:00:00");
                        elMes.textContent = dateObj.toLocaleString("es-MX", { month: "short" }).toUpperCase().replace('.', '');
                    }
                }
            }
        };

        const getLocalImg = (codigo) => {
            const images = {
                'C': '/img/aveo.png', 'D': '/img/virtus.png', 'E': '/img/jetta.png',
                'F': '/img/camry.png', 'IC': '/img/renegade.png', 'I': '/img/taos.png',
                'IB': '/img/avanza.png', 'M': '/img/Odyssey.png', 'L': '/img/Hiace.png',
                'H': '/img/Frontier.png', 'HI': '/img/Tacoma.png'
            };
            return images[codigo] || '/img/Logotipo.png';
        };

        try {
            const resp = await fetch(`/admin/contrato/${window.ID_RESERVACION}/resumen?t=${new Date().getTime()}`);
            if (!resp.ok) return;
            const { success, data: r } = await resp.json();
            if (!success) return;

            setTxt("#detCodigo", r.codigo);
            if (r.cliente) {
                setTxt("#detCliente", r.cliente.nombre?.toUpperCase());
                setTxt("#detTelefono", window.formatPhone ? window.formatPhone(r.cliente.telefono) : r.cliente.telefono);
                setTxt("#detEmail", r.cliente.email);
            }

            const fallbackImg = "data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAGQAAABkCAQAAADwXcorAAAAeUlEQVR42u3PAQ0AAAgDoJvYv7Y6uI0LAtI6S0hISEhISEhISEhISEhISEhISEhISEhISEhISEhISEhISEhISEhISEhISEhISEhISEhISEhISEhISEhISEhISEhISEhISEhISEhISEhISEhISEhISEhISEhISEhISGg7CiwA98X9m8YAAAAASUVORK5CYII=";

            if (r.vehiculo) {
                const cIni = document.getElementById("contratoInicial");
                if (cIni) cIni.dataset.idVehiculo = r.vehiculo.id_vehiculo || "";

                setTxt("#detModelo", r.vehiculo.modelo);
                setTxt("#detMarca", r.vehiculo.marca);
                setTxt("#detCategoria", r.vehiculo.categoria);
                setTxt("#detTransmision", r.vehiculo.transmision);
                setTxt("#detKm", r.vehiculo.km ? `${r.vehiculo.km.toLocaleString()} km` : "0 km");
                setTxt("#detPasajeros", r.vehiculo.asientos);
                setTxt("#detPuertas", r.vehiculo.puertas);
                setTxt("#step1Puertas", r.vehiculo.puertas);
                setTxt("#step1Pasajeros", r.vehiculo.asientos);
                setTxt("#step1Transmision", r.vehiculo.transmision);

                const nombreVehiculo = r.vehiculo.nombre_publico || `${r.vehiculo.marca} ${r.vehiculo.modelo}`;
                setTxt("#resumenVehCompacto", nombreVehiculo);
                setTxt("#detNombreVehiculoStep1", nombreVehiculo);
                setTxt("#resumenCategoriaCompacto", r.vehiculo.categoria);

                const codigoCat = r.vehiculo.codigo_categoria || cIni?.dataset?.idCategoria || 'C';
                setTxt("#detCategoriaCodigoStep1", codigoCat);
                setTxt("#detCategoriaNombreStep1", (r.vehiculo.categoria || "").toUpperCase());

                let urlFinal = r.vehiculo.imagen_render;
                if (!urlFinal || urlFinal.includes("base64,iVBORw0KGgoAAAANSUhEUgAAAGQAAABkCAQAAADwXcor")) {
                    urlFinal = getLocalImg(codigoCat);
                }

                window.$$(".resumenImgVeh, #resumenImgVeh, #mainImgVeh").forEach(el => {
                    if (el) { el.src = urlFinal; el.style.display = "block"; el.style.objectFit = "contain"; }
                });

            } else {
                const cIni = document.getElementById("contratoInicial");
                if (cIni) cIni.dataset.idVehiculo = "";
                ["#detModelo", "#detMarca", "#detCategoria", "#detTransmision", "#detKm", "#resumenVehCompacto", "#detPasajeros", "#detPuertas"].forEach(id => setTxt(id, "—"));
                setTxt("#detModelo", "Sin asignar");
                setTxt("#detNombreVehiculoStep1", "Vehículo sin asignar");
                window.$$(".resumenImgVeh, #resumenImgVeh, #mainImgVeh").forEach(el => { if (el) el.src = fallbackImg; });
            }

            if (r.fechas) {
                setTxt("#detFechaSalida", r.fechas.inicio);
                setTxt("#detHoraSalida", r.fechas.hora_inicio);
                setTxt("#detFechaEntrega", r.fechas.fin);
                setTxt("#detHoraEntrega", r.fechas.hora_fin);
                setTxt("#detDiasRenta", r.fechas.dias);
                setTxt("#resumenDiasCompacto", `Días de renta: ${r.fechas.dias}`);
                setTxt("#resumenFechasCompacto", `${r.fechas.inicio} / ${r.fechas.fin}`);
                setTxt(".bloque.entrega .hora", r.fechas.hora_inicio);
                setTxt(".bloque.devolucion .hora", r.fechas.hora_fin);
                setTxt("#diasBadge", `${r.fechas.dias} días`);
                actualizarCalendarioVisual(".fecha-entrega-display", r.fechas.inicio);
                actualizarCalendarioVisual(".fecha-devolucion-display", r.fechas.fin);
            }

            const mapListFallback = (id, list) => {
                const el = window.$(id);
                if (!el) return;
                if (Array.isArray(list) && list.length > 0) {
                    el.innerHTML = list.map(i => {
                        const valor = i.total ?? i.precio ?? i.monto ?? i.precio_total ?? 0;
                        return `<li style="display:flex; justify-content:space-between; margin-bottom:5px; font-size:13px;">
                        <span>${i.nombre || i.concepto || 'Adicional'}</span>
                        <b>${window.money(valor)}</b>
                    </li>`;
                    }).join("");
                } else {
                    el.innerHTML = `<li class="empty">—</li>`;
                }
            };

            // 1. Pintamos los Seguros
            mapListFallback("#r_seguros_lista", r.seguros?.lista);
            setTxt("#r_seguros_total", window.money(r.seguros?.total || 0));

            // --- Lógica de Reintento para Cargos/Delivery ---
            const dropActivo = document.getElementById('switchDropoffCheckbox')?.checked;
            const gasActivo = document.getElementById('switchGasolinaCheckbox')?.checked;
            const delivActivo = document.getElementById('deliveryToggle')?.checked;
            const haySwitch = dropActivo || gasActivo || delivActivo;

            if (haySwitch && (!r.cargos || r.cargos.length === 0)) {
                if (!window._cargosRetryPendiente) {
                    window._cargosRetryPendiente = true;
                    setTimeout(async () => {
                        window._cargosRetryPendiente = false;
                        await window.cargarResumenBasico();
                    }, 500);
                }
            } else {
                window._cargosRetryPendiente = false;
            }

            // ==========================================
            // LA SOLUCIÓN: UNIR SERVICIOS + CARGOS
            // ==========================================
            const listaServicios = r.servicios || [];
            const listaCargos = r.cargos || [];

            // Juntamos ambos arreglos en uno solo para que se pinten en la sección "Adicionales"
            const todosLosAdicionales = [...listaServicios, ...listaCargos];

            // Sumamos los totales de ambas categorías
            const totalAdicionales = (r.totales?.servicios_total || 0) + (r.totales?.cargos_total || 0);

            // Los pintamos en el ID que tienes en tu HTML
            mapListFallback("#r_servicios_lista", todosLosAdicionales);
            setTxt("#r_servicios_total", window.money(totalAdicionales));

            // OJO: Borré la línea mapListFallback("#r_cargos_lista", r.cargos) porque
            // esa lista no existe en tu HTML. Todo va ahora a "Adicionales".

            // ==========================================

            // Pintamos el resto de los totales
            if (r.totales) {
                const granTotal = parseFloat(r.totales.total || 0);
                setTxt("#resumenTotalBarra", window.money(granTotal));
                setTxt("#resumenTotalUsd", `$${(granTotal / 18.5).toFixed(2)} USD`);
                setTxt("#resumenTotalCompacto", window.money(granTotal));
                setTxt("#r_total_final", window.money(granTotal));
                setTxt("#r_subtotal", window.money(r.totales.subtotal));
                setTxt("#r_iva", window.money(r.totales.iva));

                const tarifa = (parseFloat(r.totales.tarifa_modificada) > 0) ? r.totales.tarifa_modificada : r.totales.tarifa_base;
                setTxt("#r_base_precio", window.money(tarifa));
                setTxt("#r_cortesia", r.totales.horas_cortesia ?? 0);
                const diasRenta = parseInt(r.fechas?.dias || 1);
                setTxt("#totalReserva", window.money(tarifa * diasRenta));
            }

            if (r.pagos) {
                setTxt("#detPagos", window.money(r.pagos.realizados));
                setTxt("#detSaldo", window.money(r.pagos.saldo));
            }

        } catch (e) {
            console.error("❌ Error cargarResumenBasico:", e);
        }
    };

    // ==========================================
    // 2. MENÚ DESPLEGABLE DEL RESUMEN (Barra Verde)
    // ==========================================
    const btnToggleDetalle = document.getElementById('btnToggleDetalle');
    const detalleContainer = document.getElementById('resumenDetalleContainer');
    const iconoFlecha = document.getElementById('iconoFlechaResumen');

    if (btnToggleDetalle && detalleContainer) {

        btnToggleDetalle.addEventListener('click', (e) => {
            e.stopPropagation();

            const estaAbierto = detalleContainer.classList.toggle('show');

            if (iconoFlecha) iconoFlecha.classList.toggle('rotada', estaAbierto);

            btnToggleDetalle.style.borderBottomLeftRadius = estaAbierto ? '0' : '12px';
            btnToggleDetalle.style.borderBottomRightRadius = estaAbierto ? '0' : '12px';
        });

        document.addEventListener('click', (event) => {
            if (!btnToggleDetalle.contains(event.target) && !detalleContainer.contains(event.target)) {
                detalleContainer.classList.remove('show');
                if (iconoFlecha) iconoFlecha.classList.remove('rotada');

                btnToggleDetalle.style.borderBottomLeftRadius = '12px';
                btnToggleDetalle.style.borderBottomRightRadius = '12px';
            }
        });
    }

    const originalTotalNode = document.getElementById('resumenTotalCompacto');
    const barraVerdeNode = document.getElementById('resumenTotalBarra');
    const usdNode = document.getElementById('resumenTotalUsd');

    if (originalTotalNode && barraVerdeNode) {
        const observer = new MutationObserver(() => {
            barraVerdeNode.innerText = originalTotalNode.innerText;

            let val = parseFloat(originalTotalNode.innerText.replace(/[^0-9.]/g, ''));
            if (!isNaN(val) && usdNode) {
                usdNode.innerText = '$' + (val / 18.5).toFixed(2) + ' USD';
            }
        });
        observer.observe(originalTotalNode, { childList: true, characterData: true, subtree: true });
    }

    // ================================ SELECCIÓN DE VEHÍCULO ===============================

    // OPTIMIZACIÓN: Recibir el botón como parámetro para no depender de `event.target` (rompe en Safari/Firefox)
    window.seleccionarVehiculo = async (idVehiculo, btnEl) => {
        try {
            if (btnEl) btnEl.innerHTML = "⌛...";

            const resp = await fetch("/admin/contrato/asignar-vehiculo", {
                method: "POST",
                headers: { "Content-Type": "application/json", "X-CSRF-TOKEN": window.csrfToken },
                body: JSON.stringify({ id_reservacion: window.ID_RESERVACION, id_vehiculo: idVehiculo }),
            });

            const data = await resp.json();

            if (data.success) {
                if (window.alertify) alertify.success("Vehículo asignado.");

                const cIni = document.getElementById("contratoInicial");
                if (cIni) cIni.dataset.idVehiculo = String(idVehiculo);

                window.cerrarModalVehiculos();
                setTimeout(() => { if (typeof window.cargarResumenBasico === 'function') window.cargarResumenBasico(); }, 150);
            } else {
                if (window.alertify) alertify.error(data.error || "Error al asignar.");
                if (btnEl) btnEl.innerHTML = "Seleccionar";
            }
        } catch (err) {
            console.error(err);
            if (window.alertify) alertify.error("Error de conexión.");
            if (btnEl) btnEl.innerHTML = "Seleccionar";
        }
    };

    window.cargarVehiculosCategoriaModal = async function (idCategoria) {
        if (!idCategoria || !window.ID_RESERVACION) return;
        try {
            const resp = await fetch(`/admin/contrato/vehiculos-por-categoria/${idCategoria}/${window.ID_RESERVACION}`);
            const data = await resp.json();
            if (data.success) {
                window.listaVehiculosOriginal = data.data;
                window.renderVehiculosEnModal(data.data);
            }
        } catch (err) { console.error("Error al cargar vehículos:", err); }
    };

    window.renderVehiculosEnModal = (lista) => {
        const cont = window.$("#listaVehiculosTabla");
        if (!cont) return;

        if (!lista || lista.length === 0) {
            cont.innerHTML = `<tr><td colspan="14" style="padding:20px; text-align:center; color:#555; font-weight:bold;">No hay vehículos disponibles en la categoría reservada.</td></tr>`;
            return;
        }

        let htmlFinal = "";

        lista.forEach((v, index) => {
            const g = v.gasolina_actual ?? 0;
            const fraccionComun = `${g}/16`;
            const gasLitros = v.gasolina_litros ?? (g * 3.75).toFixed(0);

            const mantKm = v.km_restantes !== null ? `${v.km_restantes} Km` : "—";
            const vigenciaPoliza = v.dias_seguro !== undefined ? `${v.dias_seguro} Días` : (v.fin_vigencia_poliza ?? "—");
            const diasVerificacion = v.dias_verificacion !== undefined ? `${v.dias_verificacion} Días` : "—";

            const placaVehiculo = v.placa || "Sin Placa";
            const categoriaNombre = v.categoria || "—";
            const tamano = v.nombre_publico || v.marca || "—";
            const modelo = v.modelo || "—";
            const transmision = v.transmision || "—";
            const color = v.color || "—";
            const km = v.kilometraje?.toLocaleString() || "—";

            let htmlAccion = "";
            let rowStyle = "";

            if (v.es_el_actual) {
                rowStyle = "background-color: #dcfce7; color: #166534;";
                htmlAccion = `<b style="font-size:11px;">ACTUAL</b>`;
            } else if (v.bloqueado_por_codigo) {
                rowStyle = "background-color: #fee2e2; color: #991b1b; opacity: 0.8;";
                htmlAccion = `<span style="font-size:10px; font-weight:bold; cursor:help;" title="Bloqueado por: ${v.bloqueado_por_codigo}">Ocupado</span>`;
            } else {
                htmlAccion = `<button type="button" class="btn primary btn-vehiculo" style="padding:4px 16px; font-size:12px;" data-id="${v.id_vehiculo}" data-gasolina="${fraccionComun}">Elegir</button>`;
            }

            htmlFinal += `
        <tr style="${rowStyle}">
            <td>${index + 1}</td>
            <td><b>${placaVehiculo}</b></td>
            <td>${categoriaNombre}</td>
            <td>${tamano}</td>
            <td>${modelo}</td>
            <td>${transmision}</td>
            <td>${color}</td>
            <td>${fraccionComun}</td>
            <td>${gasLitros}</td>
            <td>${km}</td>
            <td>${diasVerificacion}</td>
            <td>${mantKm}</td>
            <td>${vigenciaPoliza}</td>
            <td>${htmlAccion}</td>
        </tr>`;
        });

        cont.innerHTML = htmlFinal;

        window.$$(".btn-vehiculo").forEach(btn => {
            btn.onclick = async () => {
                await window.seleccionarVehiculo(btn.dataset.id, btn);

                const inputGas = document.getElementById("gasNivelActual");
                if (inputGas) inputGas.value = btn.dataset.gasolina;

                if (document.getElementById("switchGasolinaCheckbox")?.checked && typeof window.handleGasolinaUpdate === 'function') {
                    window.handleGasolinaUpdate();
                }
            };
        });
    };

    ["filtroPlacas", "filtroColor", "filtroModelo"].forEach(id => {
        const el = document.getElementById(id);
        if (el) {
            el.addEventListener("input", () => {
                const p = window.$("#filtroPlacas")?.value.toLowerCase() || "";
                const c = window.$("#filtroColor")?.value.toLowerCase() || "";
                const m = window.$("#filtroModelo")?.value.toLowerCase() || "";

                const filtrados = window.listaVehiculosOriginal.filter(v =>
                    (v.placa ?? "").toLowerCase().includes(p) &&
                    (v.color ?? "").toLowerCase().includes(c) &&
                    (v.modelo ?? "").toLowerCase().includes(m)
                );
                window.renderVehiculosEnModal(filtrados);
            });
        }
    });

    // ================================ EDICIONES (TARIFA Y CORTESÍA) ===============================

    window.actualizarFechasYRecalcular = async function (tarifaManual = null, cortesiaManual = null) {
        const cIni = document.getElementById("contratoInicial");
        const inputE = document.getElementById("inputOcultoEntrega");
        const inputD = document.getElementById("inputOcultoDevolucion");

        let fechaInicio = "", horaInicio = "", fechaFin = "", horaFin = "";

        if (inputE && inputE.value) {
            fechaInicio = inputE.value.split('T')[0];
            horaInicio = inputE.value.split('T')[1];
        }
        if (inputD && inputD.value) {
            fechaFin = inputD.value.split('T')[0];
            horaFin = inputD.value.split('T')[1];
        }

        const idCategoria = cIni ? cIni.dataset.idCategoria : null;
        if (!fechaInicio || !fechaFin || !idCategoria) return;

        try {
            const resp = await fetch(`/admin/contrato/${window.ID_RESERVACION}/recalcular-total`, {
                method: "POST",
                headers: { "Content-Type": "application/json", "X-CSRF-TOKEN": window.csrfToken },
                body: JSON.stringify({
                    fecha_inicio: fechaInicio, hora_inicio: horaInicio,
                    fecha_fin: fechaFin, hora_fin: horaFin,
                    id_categoria: idCategoria, tarifa_manual: tarifaManual, horas_cortesia: cortesiaManual
                }),
            });

            const data = await resp.json();

            if (data.success) {
                if (typeof window.cargarResumenBasico === 'function') await window.cargarResumenBasico();

                const set = (id, val) => { if (window.$(id)) window.$(id).textContent = val; };

                set("#detFechaSalida", data.fecha_inicio);
                set("#detHoraSalida", data.hora_inicio);
                set("#detFechaEntrega", data.fecha_fin);
                set("#detHoraEntrega", data.hora_fin);
                set("#detDiasRenta", data.dias);
                set("#diasBadge", `${data.dias} días`);
                set("#resumenDiasCompacto", `Días de renta: ${data.dias}`);
                set("#resumenFechasCompacto", `${data.fecha_inicio} / ${data.fecha_fin}`);

                if (cIni) {
                    cIni.dataset.inicio = fechaInicio; cIni.dataset.fin = fechaFin;
                    cIni.dataset.horaRetiro = horaInicio; cIni.dataset.horaEntrega = horaFin;
                }
            }
        } catch (err) {
            console.error("❌ Error recalcular:", err);
            if (typeof window.cargarResumenBasico === 'function') window.cargarResumenBasico();
        }
    };

    window.$("#btnEditarTarifa")?.addEventListener("click", () => {
        const contenedor = window.$("#r_base_precio");
        if (!contenedor) return;

        let precioActual = contenedor.textContent.replace(/[^\d.-]/g, '');
        const input = document.createElement("input");
        input.type = "number"; input.step = "0.01"; input.value = parseFloat(precioActual) || 0;
        Object.assign(input.style, { border: "1px solid #2563eb", background: "#fff", width: "100px", fontWeight: "bold", padding: "2px 4px", borderRadius: "4px" });

        contenedor.innerHTML = ""; contenedor.appendChild(input);
        input.focus(); input.select();

        input.addEventListener("blur", async () => {
            let nuevoPrecio = parseFloat(input.value);
            if (!isNaN(nuevoPrecio) && nuevoPrecio >= 0) {
                contenedor.textContent = "Guardando...";
                try {
                    const resp = await fetch(`/admin/contrato/${window.ID_RESERVACION}/editar-tarifa`, {
                        method: "POST", headers: { "Content-Type": "application/json", "X-CSRF-TOKEN": window.csrfToken },
                        body: JSON.stringify({ tarifa_modificada: nuevoPrecio })
                    });
                    const data = await resp.json();
                    if (data.ok) {
                        if (typeof window.cargarResumenBasico === 'function') await window.cargarResumenBasico();
                        if (typeof window.cargarPaso6 === 'function' && window.$("#baseAmt")) window.cargarPaso6();
                        if (window.alertify) alertify.success("Tarifa actualizada");
                    } else { throw new Error("Error Backend"); }
                } catch (e) {
                    contenedor.textContent = window.money(precioActual);
                    if (window.alertify) alertify.error("Error al actualizar");
                }
            } else { contenedor.textContent = window.money(precioActual); }
        });
        input.addEventListener("keydown", (e) => {
            if (e.key === "Enter") input.blur();
            if (e.key === "Escape") contenedor.textContent = window.money(precioActual);
        });
    });

    window.$("#btnEditarCortesia")?.addEventListener("click", () => {
        const contenedor = window.$("#r_cortesia");
        if (!contenedor) return;

        let valorActual = contenedor.textContent.trim();
        const input = document.createElement("input");
        input.type = "number"; input.min = 1; input.max = 3;

        let valParsed = parseInt(valorActual) || 1;
        input.value = Math.min(Math.max(valParsed, 1), 3);

        Object.assign(input.style, { width: "55px", border: "1px solid #2563eb", textAlign: "center", fontWeight: "bold", borderRadius: "4px" });

        contenedor.innerHTML = ""; contenedor.appendChild(input);
        input.focus(); input.select();

        input.addEventListener("blur", async () => {
            let nuevoValor = parseInt(input.value);
            if (nuevoValor > 3) {
                if (window.alertify) alertify.error("El límite máximo de cortesía es de 3 horas.");
                nuevoValor = 3;
            }
            if (isNaN(nuevoValor) || nuevoValor < 1) nuevoValor = 1;

            contenedor.textContent = "...";
            try {
                await window.actualizarFechasYRecalcular(null, nuevoValor);
                contenedor.textContent = nuevoValor;
            } catch (e) {
                contenedor.textContent = valorActual;
                if (window.alertify) alertify.error("Error al guardar la cortesía.");
            }
        });
        input.addEventListener("keydown", (e) => {
            if (e.key === "Enter") input.blur();
            if (e.key === "Escape") contenedor.textContent = valorActual;
        });
    });

    window.$("#selectCategoria")?.addEventListener("change", async (e) => {
        ["#detModelo", "#detMarca", "#detCategoria", "#detTransmision", "#detKm", "#resumenVehCompacto", "#detPasajeros", "#detPuertas"]
            .forEach(id => { if (window.$(id)) window.$(id).textContent = "Actualizando..."; });
        try {
            const resp = await fetch(`/admin/contrato/${window.ID_RESERVACION}/actualizar-categoria`, {
                method: "POST", headers: { "Content-Type": "application/json", "X-CSRF-TOKEN": window.csrfToken },
                body: JSON.stringify({ id_categoria: e.target.value })
            });
            if (resp.ok) await window.cargarResumenBasico();
        } catch (err) { console.error(err); }
    });

    // Control de Sidebar Detallada
    window.$("#btnVerDetalle")?.addEventListener("click", () => {
        if (window.$("#resumenCompacto")) window.$("#resumenCompacto").style.display = "none";
        if (window.$("#resumenDetalle")) window.$("#resumenDetalle").style.display = "block";
        window.ContratoStore.set('sidebarDetallada', true);
    });

    window.$("#btnOcultarDetalle")?.addEventListener("click", () => {
        if (window.$("#resumenDetalle")) window.$("#resumenDetalle").style.display = "none";
        if (window.$("#resumenCompacto")) window.$("#resumenCompacto").style.display = "block";
        window.ContratoStore.set('sidebarDetallada', false);
    });

    if (window.ContratoStore.get('sidebarDetallada', false)) {
        if (window.$("#resumenCompacto")) window.$("#resumenCompacto").style.display = "none";
        if (window.$("#resumenDetalle")) window.$("#resumenDetalle").style.display = "block";
    }

    if (window.ID_RESERVACION) window.cargarResumenBasico();

    document.querySelectorAll(".stepper-item").forEach(item => {
        item.addEventListener("click", function (e) {
            e.preventDefault();
            const targetStep = parseInt(this.getAttribute("data-step-indicator"));
            const estamosEnPasosIniciales = document.querySelector('.step[data-step="1"]') !== null;
            const idRes = window.ID_RESERVACION || document.getElementById("contratoInicial")?.dataset.idReservacion;

            if (estamosEnPasosIniciales) {
                if (targetStep >= 1 && targetStep <= 3) {
                    window.showStep(targetStep);
                } else {
                    if (idRes) {
                        const idVehiculo = document.getElementById("contratoInicial")?.dataset.idVehiculo;
                        if (!idVehiculo) return alertify.warning("⚠️ Selecciona un vehículo primero.");
                        localStorage.setItem(`contratoPasoActual_${idRes}`, targetStep.toString());
                        window.location.href = `/admin/contrato2/${idRes}`;
                    }
                }
            } else {
                if (targetStep >= 1 && targetStep <= 3) {
                    localStorage.setItem(`contratoPasoActual_${idRes}`, targetStep.toString());
                    window.location.href = `/admin/contrato/${idRes}`;
                } else {
                    window.showStep(targetStep);
                }
            }
        });
    });
});
