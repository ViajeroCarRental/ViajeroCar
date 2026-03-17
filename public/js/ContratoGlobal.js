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

document.addEventListener("DOMContentLoaded", () => {
    const contratoApp = document.getElementById("contratoApp") || document.getElementById("contratoInicial");
    window.ID_CONTRATO = contratoApp?.dataset.idContrato || null;
    window.ID_RESERVACION = contratoApp?.dataset.idReservacion || null;
    window.csrfToken = document.querySelector('meta[name="csrf-token"]')?.content || "";

    // Simplificación sugerida:
    const ahora = new Date();
    const horaActual = ahora.getHours().toString().padStart(2, '0') + ":" +
        ahora.getMinutes().toString().padStart(2, '0');

    const inputHora = window.$("#nuevaHoraEntrega");
    if (inputHora && !inputHora.value) {
        const ahora = new Date();
        const hh = ahora.getHours().toString().padStart(2, '0');
        const mm = ahora.getMinutes().toString().padStart(2, '0');
        inputHora.value = `${hh}:${mm}`;

        if (window.ID_RESERVACION) {
            setTimeout(() => window.actualizarFechasYRecalcular(), 200);
        }
    }

    // Navegación de pasos
    window.showStep = (n) => {
        window.$$(".step").forEach(el => el.classList.toggle("active", Number(el.dataset.step) === n));
        if (window.ID_RESERVACION) localStorage.setItem(`contratoPasoActual_${window.ID_RESERVACION}`, n);
        if (n === 6 && typeof window.cargarPaso6 === 'function') window.cargarPaso6();
    };

    // Resumen del contrato
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

        try {
            const resp = await fetch(`/admin/contrato/${window.ID_RESERVACION}/resumen?t=${new Date().getTime()}`);
            if (!resp.ok) return;
            const { success, data: r } = await resp.json();
            if (!success) return;

            // Datos Cliente
            setTxt("#detCodigo", r.codigo);
            if (r.cliente) {
                setTxt("#detCliente", r.cliente.nombre?.toUpperCase());
                setTxt("#detTelefono", window.formatPhone(r.cliente.telefono));
                setTxt("#detEmail", r.cliente.email);
            }

            // Vehículo
            const fallbackImg = "https://mambarentacar.com/img/default-car.jpg";

            if (r.vehiculo && r.vehiculo.id_vehiculo) {
                setTxt("#detModelo", r.vehiculo.modelo);
                setTxt("#detMarca", r.vehiculo.marca);
                setTxt("#detCategoria", r.vehiculo.categoria);
                setTxt("#detTransmision", r.vehiculo.transmision);
                setTxt("#detKm", r.vehiculo.km ? `${r.vehiculo.km.toLocaleString()} km` : "0 km");
                setTxt("#detPasajeros", r.vehiculo.asientos);
                setTxt("#detPuertas", r.vehiculo.puertas);
                setTxt("#resumenVehCompacto", `${r.vehiculo.marca} ${r.vehiculo.modelo}`);

                const imgVeh = window.$("#resumenImgVeh");
                if (imgVeh) {
                    imgVeh.src = r.vehiculo.imagen || fallbackImg;
                    imgVeh.style.display = "block";
                }

                const selectVeh = window.$("#vehAssign");
                if (selectVeh) {
                    selectVeh.innerHTML = `<option value="${r.vehiculo.id_vehiculo}">${r.vehiculo.marca} ${r.vehiculo.modelo} (${r.vehiculo.placa})</option>`;
                }
            } else {
                ["#detModelo", "#detMarca", "#detCategoria", "#detTransmision", "#detKm", "#resumenVehCompacto", "#detPasajeros", "#detPuertas"].forEach(id => setTxt(id, "—"));
                setTxt("#detModelo", "Sin asignar");

                if (window.$("#resumenImgVeh")) {
                    window.$("#resumenImgVeh").src = "https://mambarentacar.com/img/default-car.jpg";
                }

                if (window.$("#vehAssign")) {
                    window.$("#vehAssign").innerHTML = `<option value="">Seleccione un vehículo</option>`;
                }
            }

            // Fechas y Horas
            if (r.fechas) {
                setTxt("#detFechaSalida", r.fechas.inicio);
                setTxt("#detHoraSalida", r.fechas.hora_inicio);
                setTxt("#detFechaEntrega", r.fechas.fin);
                setTxt("#detHoraEntrega", r.fechas.hora_fin);

                setTxt("#detDiasRenta", r.fechas.dias);
                setTxt("#resumenDiasCompacto", `Días de renta: ${r.fechas.dias}`);
                setTxt("#resumenFechasCompacto", `${r.fechas.inicio} / ${r.fechas.fin}`);

                // Sincronizar Paso 1 (si existe en el DOM)
                setTxt(".bloque.entrega .hora", r.fechas.hora_inicio);
                setTxt(".bloque.devolucion .hora", r.fechas.hora_fin);
                setTxt("#diasBadge", `${r.fechas.dias} días`);
                actualizarCalendarioVisual(".fecha-entrega-display", r.fechas.inicio);
                actualizarCalendarioVisual(".fecha-devolucion-display", r.fechas.fin);

                if (r.totales.horas_cortesia !== undefined) setTxt("#r_cortesia", r.totales.horas_cortesia);
            }

            const secDelivery = window.$("#res_delivery_section");
            if (r.entrega) {
                if (secDelivery) secDelivery.style.display = "block";
                setTxt("#detDeliveryTipo", r.entrega.tipo);
                setTxt("#detDeliveryDireccion", r.entrega.direccion);
            } else {
                // Si no hay delivery, nos aseguramos de que la sección esté oculta
                if (secDelivery) secDelivery.style.display = "none";
            }

            // Listados (Seguros, Servicios, Cargos)
            const mapList = (id, list, keyPrecio) => {
                const el = window.$(id);
                if (el) el.innerHTML = (list && list.length > 0)
                    ? list.map(i => `<li>${i.nombre} ${i.cantidad ? '(x' + i.cantidad + ')' : ''} — ${window.money(i[keyPrecio])}</li>`).join("")
                    : `<li class="empty">—</li>`;
            };
            mapList("#r_seguros_lista", r.seguros?.lista, 'precio');
            mapList("#r_servicios_lista", r.servicios, 'total');

            let sumaCargos = 0;
            const ulCargos = window.$("#r_cargos_lista");

            if (ulCargos) {
                ulCargos.innerHTML = (r.cargos && r.cargos.length > 0)
                    ? r.cargos.map(c => {

                        // Detecta cualquier tipo de campo de monto
                        let valorCargo =
                            c.total ??
                            c.monto ??
                            c.monto_variable ??
                            c.precio ??
                            0;

                        valorCargo = parseFloat(valorCargo) || 0;

                        sumaCargos += valorCargo;

                        return `<li>${c.nombre} — ${window.money(valorCargo)}</li>`;
                    }).join("")
                    : `<li class="empty">—</li>`;
            }

            // Totales
            if (r.totales) {
                const granTotal = window.money(r.totales.total);
                setTxt("#resumenTotalCompacto", granTotal);
                setTxt("#r_total_final", granTotal);
                setTxt("#r_subtotal", window.money(r.totales.subtotal));
                setTxt("#r_iva", window.money(r.totales.iva));
                setTxt("#r_seguros_total", window.money(r.seguros?.total || 0));
                setTxt("#r_servicios_total", window.money(r.totales.servicios_total || 0));
                setTxt("#r_cargos_total", window.money(sumaCargos));

                const hCortesia = r.totales.horas_cortesia ?? 0;
                setTxt("#r_cortesia", hCortesia);

                const tarifa = (parseFloat(r.totales.tarifa_modificada) > 0) ? r.totales.tarifa_modificada : r.totales.tarifa_base;
                setTxt("#r_base_precio", window.money(tarifa));

                // Total Paso 1: Tarifa x Días (Lógica comercial)
                const diasRenta = parseInt(r.fechas?.dias || 1);
                setTxt("#totalReserva", window.money(tarifa * diasRenta));
            } else {
                setTxt("#r_cortesia", "1");
            }

            if (r.pagos) {
                setTxt("#detPagos", window.money(r.pagos.realizados));
                setTxt("#detSaldo", window.money(r.pagos.saldo));
            }

        } catch (e) { console.error("❌ Error cargarResumenBasico:", e); }
    };

    window.actualizarFechasYRecalcular = async function (tarifaManual = null, cortesiaManual = null) {
        const cIni = document.getElementById("contratoInicial");

        const fechaInicio = window.$("#nuevaFechaEntrega")?.value || cIni?.dataset.inicio || window.$("#detFechaSalida")?.textContent || "";
        const horaInicio = window.$("#nuevaHoraEntrega")?.value || cIni?.dataset.horaRetiro || window.$("#detHoraSalida")?.textContent || "";
        const fechaFin = window.$("#nuevaFechaDevolucion")?.value || cIni?.dataset.fin || window.$("#detFechaEntrega")?.textContent || "";
        const horaFin = window.$("#nuevaHoraDevolucion")?.value || cIni?.dataset.horaEntrega || window.$("#detHoraEntrega")?.textContent || "";

        const selectCat = document.getElementById("selectCategoria");
        const categoriaParaEnviar = selectCat ? selectCat.value : (window.ContratoStore ? window.ContratoStore.get('categoriaElegida') : null);

        if (!fechaInicio || !fechaFin || !categoriaParaEnviar) return;

        const payload = {
            fecha_inicio: fechaInicio, hora_inicio: horaInicio,
            fecha_fin: fechaFin, hora_fin: horaFin,
            id_categoria: categoriaParaEnviar,
            tarifa_manual: tarifaManual,
            horas_cortesia: cortesiaManual
        };

        try {
            const resp = await fetch(`/admin/contrato/${window.ID_RESERVACION}/recalcular-total`, {
                method: "POST",
                headers: { "Content-Type": "application/json", "X-CSRF-TOKEN": window.csrfToken },
                body: JSON.stringify(payload),
            });

            if (resp.ok) {
                window.$$(".fecha-edicion-entrega, .fecha-edicion-devolucion").forEach(el => el.style.display = "none");
                await window.cargarResumenBasico();
                if (cIni) {
                    Object.assign(cIni.dataset, { inicio: fechaInicio, fin: fechaFin, horaEntrega: horaFin, id_categoria: categoriaParaEnviar });
                }
            }
        } catch (err) {
            console.error("❌ Error recalcular:", err);
            window.cargarResumenBasico();
        }
    };

    window.$("#btnEditarTarifa")?.addEventListener("click", () => {
        const contenedor = window.$("#r_base_precio");
        if (!contenedor) return;
        let precioActual = contenedor.textContent.replace(/[^\d.-]/g, '');
        const input = document.createElement("input");
        input.type = "number"; input.value = parseFloat(precioActual) || 0; input.step = "0.01";
        Object.assign(input.style, { border: "none", background: "#f8fafc", width: "100px", fontWeight: "bold" });
        contenedor.innerHTML = ""; contenedor.appendChild(input);
        input.focus(); input.select();
        input.addEventListener("blur", async () => {
            if (!isNaN(input.value) && input.value >= 0) {
                contenedor.textContent = "...";
                await window.actualizarFechasYRecalcular(parseFloat(input.value));
            } else {
                contenedor.textContent = window.money(precioActual);
            }
        });
        input.addEventListener("keydown", (e) => { if (e.key === "Enter") input.blur(); });
    });

    window.$("#btnEditarCortesia")?.addEventListener("click", () => {
        const contenedor = window.$("#r_cortesia");
        if (!contenedor) return;

        let valorActual = contenedor.textContent.trim();
        const input = document.createElement("input");
        input.type = "number";

        input.min = 1;
        input.max = 3;

        // Si el valor actual está fuera de rango, lo reseteamos al límite
        let valParsed = parseInt(valorActual) || 1;
        input.value = Math.min(Math.max(valParsed, 1), 3);

        Object.assign(input.style, {
            width: "55px",
            border: "1px solid #2563eb",
            textAlign: "center",
            fontWeight: "bold",
            borderRadius: "4px"
        });

        contenedor.innerHTML = "";
        contenedor.appendChild(input);
        input.focus();
        input.select();

        input.addEventListener("blur", async () => {
            let nuevoValor = parseInt(input.value);

            if (nuevoValor > 3) {
                if (window.alertify) {
                    alertify.error("El límite máximo de cortesía es de 3 horas.");
                } else {
                    alert("Solo se permite un máximo de 3 horas de cortesía.");
                }
                nuevoValor = 3;
            }

            if (isNaN(nuevoValor) || nuevoValor < 1) {
                nuevoValor = 1;
            }

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

    // Cambio de Categoría
    window.$("#selectCategoria")?.addEventListener("change", async (e) => {
        const idsLimpiar = ["#detModelo", "#detMarca", "#detCategoria", "#detTransmision", "#detKm", "#resumenVehCompacto", "#detPasajeros", "#detPuertas"];
        idsLimpiar.forEach(id => {
            const el = window.$(id);
            if (el) el.textContent = "Actualizando...";
        });

        try {
            const resp = await fetch(`/admin/contrato/${window.ID_RESERVACION}/actualizar-categoria`, {
                method: "POST",
                headers: { "Content-Type": "application/json", "X-CSRF-TOKEN": window.csrfToken },
                body: JSON.stringify({ id_categoria: e.target.value })
            });
            if (resp.ok) await window.cargarResumenBasico();
        } catch (err) { console.error(err); }
    });

    // Control de Sidebar Detallada
    window.$("#btnVerDetalle")?.addEventListener("click", () => {
        const comp = window.$("#resumenCompacto");
        const det = window.$("#resumenDetalle");
        if (comp) comp.style.display = "none";
        if (det) det.style.display = "block";
        window.ContratoStore.set('sidebarDetallada', true);
    });

    window.$("#btnOcultarDetalle")?.addEventListener("click", () => {
        const comp = window.$("#resumenCompacto");
        const det = window.$("#resumenDetalle");
        if (det) det.style.display = "none";
        if (comp) comp.style.display = "block";
        window.ContratoStore.set('sidebarDetallada', false);
    });

    if (window.ContratoStore.get('sidebarDetallada', false)) {
        const comp = window.$("#resumenCompacto");
        const det = window.$("#resumenDetalle");
        if (comp) comp.style.display = "none";
        if (det) det.style.display = "block";
    }

    if (window.ID_RESERVACION) window.cargarResumenBasico();
});