
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

// Helpers
window.$ = (s) => document.querySelector(s);
window.$$ = (s) => Array.from(document.querySelectorAll(s));

// Formateador de moneda
window.money = (num) => {
    let cleanNum = typeof num === 'string' ? num.replace(/[^\d.-]/g, '') : num;
    return parseFloat(cleanNum || 0).toLocaleString("es-MX", { style: "currency", currency: "MXN", minimumFractionDigits: 2 });
};

document.addEventListener("DOMContentLoaded", () => {
    const contratoApp = document.getElementById("contratoApp") || document.getElementById("contratoInicial");

    window.ID_CONTRATO = contratoApp?.dataset.idContrato || null;
    window.ID_RESERVACION = contratoApp?.dataset.idReservacion || null;
    window.csrfToken = document.querySelector('meta[name="csrf-token"]')?.content || "";

    // Navegación Global de Pasos
    window.showStep = (n) => {
        window.$$(".step").forEach(el => el.classList.toggle("active", Number(el.dataset.step) === n));

        if (window.ID_RESERVACION) {
            localStorage.setItem(`contratoPasoActual_${window.ID_RESERVACION}`, n);
        }

        // Si vamos al paso 6, y la función cargarPaso6 existe (porque estamos cargando Contrato2.js), la ejecuta
        if (n === 6 && typeof window.cargarPaso6 === 'function') {
            window.cargarPaso6();
        }
    };

    // Carga y actualización del Resumen Básico (Panel Lateral)
    window.cargarResumenBasico = async () => {
        if (!window.ID_RESERVACION) return;

        // Helper interno para ahorrar código
        const setTxt = (sel, val) => {
            const el = window.$(sel);
            if (el) el.textContent = val ?? "—";
        };

        try {
            const resp = await fetch(`/admin/contrato/${window.ID_RESERVACION}/resumen`);
            if (!resp.ok) return;
            const { success, data: r } = await resp.json();
            if (!success) return;

            // 1. DATOS DEL CLIENTE
            setTxt("#detCodigo", r.codigo);
            if (r.cliente) {
                setTxt("#detCliente", r.cliente.nombre);
                setTxt("#detTelefono", r.cliente.telefono);
                setTxt("#detEmail", r.cliente.email);
            }

            // 2. DATOS DEL VEHÍCULO E IMAGEN
            if (r.vehiculo) {
                setTxt("#detModelo", r.vehiculo.modelo);
                setTxt("#detMarca", r.vehiculo.marca);
                setTxt("#detCategoria", r.vehiculo.categoria);
                setTxt("#detTransmision", r.vehiculo.transmision);
                setTxt("#detPasajeros", r.vehiculo.pasajeros);
                setTxt("#detPuertas", r.vehiculo.puertas);
                setTxt("#detKm", r.vehiculo.km);

                // Actualización de imagen en el resumen
                const img = window.$("#resumenImgVeh");
                if (img) {
                    if (r.vehiculo.imagen) {
                        img.src = r.vehiculo.imagen;
                        img.style.display = "block";
                    } else {
                        img.style.display = "none";
                    }
                }

                if (r.vehiculo.precio_km_dropoff) {
                    window.ContratoStore.set('precioDropoff', r.vehiculo.precio_km_dropoff);
                }
            }

            // 3. FECHAS Y RESUMEN COMPACTO
            if (r.fechas) {
                setTxt("#detFechaSalida", r.fechas.inicio);
                setTxt("#detHoraSalida", r.fechas.hora_inicio);
                setTxt("#detFechaEntrega", r.fechas.fin);
                setTxt("#detHoraEntrega", r.fechas.hora_fin);
                setTxt("#detDiasRenta", r.fechas.dias);

                setTxt("#resumenVehCompacto", `${r.vehiculo?.marca ?? ''} ${r.vehiculo?.modelo ?? ''}`.trim());
                setTxt("#resumenCategoriaCompacto", `Categoría: ${r.vehiculo?.categoria ?? "—"}`);
                setTxt("#resumenDiasCompacto", `Días de renta: ${r.fechas.dias ?? "—"}`);
                setTxt("#resumenFechasCompacto", `${r.fechas.inicio ?? "—"} / ${r.fechas.fin ?? "—"}`);
            }

            // 4. TOTALES (Panel Lateral + Etiquetas en los Pasos)
            if (r.totales) {
                const granTotal = window.money(r.totales.total);

                // Panel Lateral
                setTxt("#resumenTotalCompacto", granTotal);
                setTxt("#r_total_final", granTotal);
                setTxt("#r_subtotal", window.money(r.totales.subtotal));
                setTxt("#r_iva", window.money(r.totales.iva));
                setTxt("#r_base_precio", window.money(r.totales.tarifa_modificada ?? r.totales.tarifa_base));

                // Totales dentro de los pasos (Paso 2, 3 y 4)
                setTxt("#total_seguros", window.money(r.seguros?.total || 0));
                setTxt("#total_servicios", window.money(r.totales.servicios_total || 0));
                setTxt("#total_cargos", window.money(r.totales.cargos_adicionales_total || 0));
            }

            // 5. DESGLOSE DE SEGUROS (Lista detallada)
            const listaSeguros = window.$("#r_seguros_lista");
            if (listaSeguros) {
                listaSeguros.innerHTML = "";
                if (r.seguros && r.seguros.lista && r.seguros.lista.length > 0) {
                    listaSeguros.innerHTML = r.seguros.lista.map(s =>
                        `<li>${s.nombre} — ${window.money(s.precio)}</li>`
                    ).join("");
                    setTxt("#r_seguros_total", window.money(r.seguros.total));
                } else {
                    listaSeguros.innerHTML = `<li class="empty">—</li>`;
                    setTxt("#r_seguros_total", "—");
                }
            }

            // 6. SERVICIOS ADICIONALES
            const ulServicios = window.$("#r_servicios_lista");
            if (ulServicios && r.servicios) {
                ulServicios.innerHTML = r.servicios.length > 0
                    ? r.servicios.map(s => `<li>${s.nombre} (x${s.cantidad}) — ${window.money(s.total)}</li>`).join("")
                    : `<li class="empty">—</li>`;
                setTxt("#r_servicios_total", window.money(r.totales?.servicios_total || 0));
            }

            // 7. CARGOS EXTRA (Paso 4)
            const ulCargos = window.$("#r_cargos_lista");
            if (ulCargos && r.cargos) {
                ulCargos.innerHTML = r.cargos.length > 0
                    ? r.cargos.map(c => {
                        let txt = `${c.nombre} — ${window.money(c.total)}`;
                        if (c.km) txt += ` (${c.km} km)`;
                        if (c.litros) txt += ` (${c.litros} L)`;
                        return `<li>${txt}</li>`;
                    }).join("")
                    : `<li class="empty">—</li>`;
            }

            // 8. PAGOS Y SALDO
            if (r.pagos) {
                setTxt("#detPagos", window.money(r.pagos.realizados));
                setTxt("#detSaldo", window.money(r.pagos.saldo));
            }

        } catch (e) {
            console.error("❌ Error cargando resumen global:", e);
        }
    };

    // Logica de Panel

    window.$("#btnVerDetalle")?.addEventListener("click", (e) => {
        e.preventDefault();
        window.$("#resumenCompacto").style.display = "none";
        window.$("#resumenDetalle").style.display = "block";
        window.ContratoStore.set('sidebarDetallada', true);
    });

    window.$("#btnOcultarDetalle")?.addEventListener("click", (e) => {
        e.preventDefault();
        window.$("#resumenDetalle").style.display = "none";
        window.$("#resumenCompacto").style.display = "block";
        window.ContratoStore.set('sidebarDetallada', false);
    });

    const mostrarDetalle = window.ContratoStore.get('sidebarDetallada', false);

    if (mostrarDetalle) {
        if (window.$("#resumenCompacto")) window.$("#resumenCompacto").style.display = "none";
        if (window.$("#resumenDetalle"))  window.$("#resumenDetalle").style.display = "block";
    } else {
        if (window.$("#resumenDetalle"))  window.$("#resumenDetalle").style.display = "none";
        if (window.$("#resumenCompacto")) window.$("#resumenCompacto").style.display = "block";
    }

    // Cargar el resumen automáticamente al entrar si hay reservación
    if (window.ID_RESERVACION) {
        window.cargarResumenBasico();
    }
});