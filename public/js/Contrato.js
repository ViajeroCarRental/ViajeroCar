// Contrato
// Pasos 1 al 3

document.addEventListener("DOMContentLoaded", () => {
    console.log("✅ DOM listo, iniciando navegación de pasos (1-3)...");

    // ================================ UTILIDADES ===============================

    // Función de Debounce para no saturar el servidor en eventos de "input"
    const debounce = (func, delay = 300) => {
        let timer;
        return (...args) => {
            clearTimeout(timer);
            timer = setTimeout(() => func.apply(this, args), delay);
        };
    };

    // Helper para formatear fechas de input (YYYY-MM-DDTHH:mm)
    const formatFechaInput = (dateObj) => {
        const yyyy = dateObj.getFullYear();
        const mm = String(dateObj.getMonth() + 1).padStart(2, '0');
        const dd = String(dateObj.getDate()).padStart(2, '0');
        const hh = String(dateObj.getHours()).padStart(2, '0');
        const min = String(dateObj.getMinutes()).padStart(2, '0');
        return `${yyyy}-${mm}-${dd}T${hh}:${min}`;
    };

    // Helper estandarizado para seleccionar elementos del DOM
    const $el = (selector) => document.querySelector(selector);
    const $elId = (id) => document.getElementById(id);

    // ================================ ESTADO Y MÓDULOS ===============================

    const mesesArr = ['ENE', 'FEB', 'MAR', 'ABR', 'MAY', 'JUN', 'JUL', 'AGO', 'SEP', 'OCT', 'NOV', 'DIC'];

    const ContratoState = {
        intervaloAprobacion: null,
        deliveryTotalActual: 0,
        dropoffTotal: 0,
        gasolinaTotal: 0
    };

    // Actualiza el estado local (sin contaminar todo el window innecesariamente)
    const setState = (key, value) => {
        ContratoState[key] = value;
        return value;
    };

    const ContratoUI = {
        money(value) {
            const amount = parseFloat(value || 0);
            return window.money ? window.money(amount) : `$${amount.toFixed(2)} MXN`;
        },
        notify(type, message) {
            if (window.alertify) {
                if (type === "error") return alertify.error(message);
                if (type === "warning") return alertify.warning(message);
                return alertify.success(message);
            }
            if (type === "error") return alert(message);
            console[type === "warning" ? "warn" : "log"](message);
        },
        setText(element, value) {
            if (element) element.textContent = value;
        },
        toggle(element, visible) {
            if (element) element.style.display = visible ? "block" : "none";
        }
    };

    const ContratoAPI = {
        async request(url, options = {}) {
            const isFormData = options.body instanceof FormData;
            const headers = {
                Accept: "application/json",
                "X-CSRF-TOKEN": window.csrfToken,
                ...(isFormData ? {} : options.body !== undefined ? { "Content-Type": "application/json" } : {}),
                ...(options.headers || {})
            };

            try {
                const response = await fetch(url, {
                    ...options,
                    headers,
                    body: isFormData ? options.body : (options.body !== undefined ? JSON.stringify(options.body) : undefined)
                });

                if (!response.ok) throw new Error(`HTTP ${response.status} en ${url}`);

                const contentType = response.headers.get("content-type") || "";
                return contentType.includes("application/json") ? response.json() : response.text();
            } catch (error) {
                if (error instanceof TypeError && error.message === "Failed to fetch") {
                    throw new Error("Servidor no disponible (Conexión rechazada)");
                }
                throw error;
            }
        },
        getJSON(url) { return this.request(url); },
        postJSON(url, body) { return this.request(url, { method: "POST", body }); },
        deleteJSON(url, body) { return this.request(url, { method: "DELETE", body }); }
    };

    // ================================ PASO 1: FECHAS ===============================

    (function inicializarPaso1() {
        function validarYPintarFechas() {
            const inputE = $elId('inputOcultoEntrega');
            const inputD = $elId('inputOcultoDevolucion');

            if (!inputE || !inputD || !inputE.value || !inputD.value) return;

            let dateE = new Date(inputE.value);
            let dateD = new Date(inputD.value);
            const hoy = new Date();

            const esHoy = (dateE.getFullYear() === hoy.getFullYear() &&
                dateE.getMonth() === hoy.getMonth() &&
                dateE.getDate() === hoy.getDate());

            dateE.setHours(esHoy ? hoy.getHours() : 12);
            dateE.setMinutes(esHoy ? hoy.getMinutes() : 0);

            let warning = false;

            const soloFechaE = new Date(dateE.getFullYear(), dateE.getMonth(), dateE.getDate());
            const soloFechaD = new Date(dateD.getFullYear(), dateD.getMonth(), dateD.getDate());

            if (soloFechaD < soloFechaE) {
                dateD = new Date(dateE);
                dateD.setDate(dateE.getDate() + 1);
                warning = true;
            } else if (soloFechaD.getTime() === soloFechaE.getTime() && dateD <= dateE) {
                dateD = new Date(dateE);
                dateD.setHours(dateE.getHours() + 1);
                warning = true;
            }

            if (dateD.toDateString() !== dateE.toDateString()) {
                dateD.setHours(12, 0, 0, 0);
            }

            const ui = {
                valE: formatFechaInput(dateE),
                valD: formatFechaInput(dateD),
                entrega: {
                    dia: String(dateE.getDate()).padStart(2, '0'),
                    mes: mesesArr[dateE.getMonth()],
                    anio: dateE.getFullYear(),
                    hora: `${String(dateE.getHours() % 12 || 12).padStart(2, '0')}:${String(dateE.getMinutes()).padStart(2, '0')} ${dateE.getHours() >= 12 ? 'PM' : 'AM'}`
                },
                devolucion: {
                    dia: String(dateD.getDate()).padStart(2, '0'),
                    mes: mesesArr[dateD.getMonth()],
                    anio: dateD.getFullYear(),
                    hora: `${String(dateD.getHours() % 12 || 12).padStart(2, '0')}:${String(dateD.getMinutes()).padStart(2, '0')} ${dateD.getHours() >= 12 ? 'PM' : 'AM'}`
                }
            };

            inputE.value = ui.valE;
            inputD.value = ui.valD;

            window.requestAnimationFrame(() => {
                if (warning) {
                    ContratoUI.notify("warning", "Fecha ajustada: La devolución debe ser posterior a la entrega.");
                }

                ContratoUI.setText($elId('txtDiaEntrega'), ui.entrega.dia);
                ContratoUI.setText($elId('txtMesEntrega'), ui.entrega.mes);
                ContratoUI.setText($elId('txtAnioEntrega'), ui.entrega.anio);
                ContratoUI.setText($elId('txtHoraEntrega'), ui.entrega.hora);

                ContratoUI.setText($elId('txtDiaDevolucion'), ui.devolucion.dia);
                ContratoUI.setText($elId('txtMesDevolucion'), ui.devolucion.mes);
                ContratoUI.setText($elId('txtAnioDevolucion'), ui.devolucion.anio);
                ContratoUI.setText($elId('txtHoraDevolucion'), ui.devolucion.hora);
            });
        }
        function iniciarMonitoreoAprobacion() {
            const solicitud = JSON.parse(sessionStorage.getItem("solicitudCambio") || "{}");
            if (!solicitud.activa) return;

            if (ContratoState.intervaloAprobacion) clearInterval(ContratoState.intervaloAprobacion);

            ContratoState.intervaloAprobacion = setInterval(async () => {
                try {
                    const data = await ContratoAPI.getJSON(`/admin/contrato/cambio-fecha/estado/${solicitud.id_reservacion}`);

                    if (data.estado === "aprobado" || data.estado === "rechazado") {
                        clearInterval(ContratoState.intervaloAprobacion);
                        ContratoState.intervaloAprobacion = null;
                        sessionStorage.removeItem("solicitudCambio");

                        if (data.estado === "aprobado") {
                            const inputE = $elId("inputOcultoEntrega");
                            if (inputE) inputE.value = `${solicitud.f}T${solicitud.h}`;
                            validarYPintarFechas();
                            if (typeof window.actualizarFechasYRecalcular === 'function') await window.actualizarFechasYRecalcular();
                            ContratoUI.notify("success", "✅ Cambio de fecha aprobado.");
                        } else {
                            ContratoUI.notify("error", "❌ Solicitud de cambio rechazada.");
                        }
                    }
                } catch (err) {
                    if (err.message.includes('Failed to fetch') || err.name === 'TypeError') {
                        console.warn("⚠️ El servidor no responde. Reintentando monitoreo en 8 segundos...");
                    } else {
                        console.error("Error en monitoreo:", err);
                    }
                }
            }, 8000);
        }

        if (JSON.parse(sessionStorage.getItem("solicitudCambio") || "{}").activa) iniciarMonitoreoAprobacion();

        function vincularCalendario(inputId) {
            const input = $elId(inputId);
            if (!input) return;

            let fechaOriginal = input.value;

            input.addEventListener('click', (e) => {
                e.preventDefault();
                fechaOriginal = input.value;
                try { input.showPicker(); } catch (error) { input.focus(); }
            });

            input.addEventListener('change', (e) => {
                const nuevaFechaFull = e.target.value;

                if (!nuevaFechaFull || nuevaFechaFull === fechaOriginal) return;

                if (inputId === 'inputOcultoEntrega') {
                    const [f, h] = nuevaFechaFull.split('T');

                    alertify.confirm(
                        "⚠️ Requiere Autorización",
                        "Cambiar la fecha de Entrega requiere autorización de un supervisor. ¿Deseas enviar la solicitud de cambio?",
                        async () => {
                            try {
                                const data = await ContratoAPI.postJSON("/admin/contrato/solicitar-cambio-fecha", {
                                    id_reservacion: window.ID_RESERVACION,
                                    nueva_fecha: f,
                                    nueva_hora: h,
                                    motivo: "Modificación en mostrador"
                                });

                                ContratoUI.notify("success", data.msg || "Solicitud enviada. Esperando aprobación...");
                                sessionStorage.setItem("solicitudCambio", JSON.stringify({
                                    activa: true,
                                    id_reservacion: window.ID_RESERVACION,
                                    f, h
                                }));

                                if (typeof iniciarMonitoreoAprobacion === 'function') {
                                    iniciarMonitoreoAprobacion();
                                }
                            } catch (err) {
                                ContratoUI.notify("error", "Error enviando solicitud.");
                            }
                        },
                        () => {
                            input.value = fechaOriginal;
                        }
                    ).set('labels', { ok: 'Enviar Solicitud', cancel: 'Cancelar' });

                    e.target.value = fechaOriginal;
                    return;
                }

                validarYPintarFechas();
                if (typeof window.actualizarFechasYRecalcular === 'function') {
                    window.actualizarFechasYRecalcular();
                }
            });
        }

        vincularCalendario('inputOcultoEntrega');
        vincularCalendario('inputOcultoDevolucion');
        validarYPintarFechas();

        $el("#btnElegirVehiculo")?.addEventListener("click", () => {
            if (typeof window.abrirModalVehiculos === 'function') window.abrirModalVehiculos();
        });
    })();

    // ================================ CALENDARIOS FLATPICKR ===============================

    (function inicializarCalendarios() {
        const pickerE = document.getElementById('pickerEntrega');
        const pickerD = document.getElementById('pickerDevolucion');

        if (!pickerE || !pickerD) {
            console.warn('⚠️ No se encontraron los inputs de flatpickr');
            return;
        }

        console.log('✅ Inicializando calendarios Flatpickr...');

        // Configuración común
        const commonOptions = {
            enableTime: true,
            dateFormat: "Y-m-d H:i",
            locale: 'es',
            disableMobile: true,
            static: false,
            allowInput: false,
        };

        // Guardar valor original para detectar cambios
        let originalValueE = pickerE.value;
        let originalValueD = pickerD.value;

        // =============================================
        // PICKER ENTREGA
        // =============================================
        const fpEntrega = flatpickr(pickerE, {
            ...commonOptions,
            positionElement: document.getElementById("tarjetaEntrega"),
            onChange: function (selectedDates, dateStr, instance) {
                if (!selectedDates.length) return;

                const fechaSeleccionada = selectedDates[0];
                const fechaOriginal = new Date(originalValueE.replace(' ', 'T'));

                // Si la fecha cambia, solicitar autorización
                if (fechaSeleccionada.toDateString() !== fechaOriginal.toDateString()) {
                    alertify.confirm(
                        "⚠️ Requiere Autorización",
                        "Cambiar la fecha requiere autorización. ¿Enviar solicitud?",
                        async () => {
                            try {
                                const f = fechaSeleccionada.toISOString().split('T')[0];
                                const h = `${String(fechaSeleccionada.getHours()).padStart(2, '0')}:${String(fechaSeleccionada.getMinutes()).padStart(2, '0')}`;

                                await fetch('/admin/contrato/solicitar-cambio-fecha', {
                                    method: 'POST',
                                    headers: {
                                        'Content-Type': 'application/json',
                                        'X-CSRF-TOKEN': window.csrfToken
                                    },
                                    body: JSON.stringify({
                                        id_reservacion: window.ID_RESERVACION,
                                        nueva_fecha: f,
                                        nueva_hora: h
                                    })
                                });
                                alertify.success("Solicitud enviada.");
                            } catch (e) {
                                instance.setDate(fechaOriginal);
                            }
                        },
                        () => instance.setDate(fechaOriginal)
                    );
                } else {
                    // Solo cambió la hora, actualizar UI y originalValue
                    originalValueE = dateStr;
                    actualizarUIFechas(fechaSeleccionada, 'entrega');
                    if (window.actualizarFechasYRecalcular) {
                        window.actualizarFechasYRecalcular();
                    }
                }
            }
        });

        // =============================================
        // PICKER DEVOLUCIÓN
        // =============================================
        const fpDevolucion = flatpickr(pickerD, {
            ...commonOptions,
            positionElement: document.getElementById("tarjetaDevolucion"),

            onChange: function (selectedDates, dateStr, instance) {
                if (!selectedDates.length) return;

                // Validar que la devolución sea después de la entrega
                const fechaEntrega = fpEntrega.selectedDates[0];
                const fechaDevolucion = selectedDates[0];

                if (fechaDevolucion < fechaEntrega) {
                    const nuevaFecha = new Date(fechaEntrega);
                    nuevaFecha.setDate(fechaEntrega.getDate() + 1);
                    instance.setDate(nuevaFecha);
                    alertify.warning('Fecha de devolución ajustada automáticamente.');
                }

                actualizarUIFechas(fechaDevolucion, 'devolucion');
                pickerD.value = instance.formatDate(fechaDevolucion, "Y-m-d H:i");
            }
        });

        // =============================================
        // FUNCIÓN PARA ACTUALIZAR LA UI DE FECHAS
        // =============================================
        function actualizarUIFechas(date, tipo) {
            if (!date) return;

            const prefix = tipo === 'entrega' ? 'Entrega' : 'Devolucion';

            // Actualizar día, mes y año
            document.getElementById(`txtDia${prefix}`).textContent = String(date.getDate()).padStart(2, '0');
            document.getElementById(`txtMes${prefix}`).textContent = date.toLocaleString('es', { month: 'short' }).toUpperCase();
            document.getElementById(`txtAnio${prefix}`).textContent = date.getFullYear();

            // Actualizar hora
            const horas = date.getHours();
            const ampm = horas >= 12 ? 'PM' : 'AM';
            const hora12 = horas % 12 || 12;
            document.getElementById(`txtHora${prefix}`).textContent =
                `${String(hora12).padStart(2, '0')}:${String(date.getMinutes()).padStart(2, '0')} ${ampm}`;
        }

        // =============================================
        // EVENTOS PARA ABRIR EL CALENDARIO AL HACER CLICK EN LAS TARJETAS
        // =============================================
        const tarjetaEntrega = document.getElementById('tarjetaEntrega');
        const tarjetaDevolucion = document.getElementById('tarjetaDevolucion');

        if (tarjetaEntrega) {
            tarjetaEntrega.addEventListener('click', function (e) {
                // No abrir si se hizo clic en el input directamente
                if (e.target.closest('.flatpickr-input')) return;

                const ahora = new Date();
                const fechaSeleccionada = fpEntrega.selectedDates[0];

                // Si la fecha seleccionada es hoy, actualizar a la hora actual
                if (fechaSeleccionada && fechaSeleccionada.toDateString() === ahora.toDateString()) {
                    fpEntrega.setDate(ahora);
                    actualizarUIFechas(ahora, 'entrega');
                }

                fpEntrega.open();
            });
        }

        if (tarjetaDevolucion) {
            tarjetaDevolucion.addEventListener('click', function (e) {
                if (e.target.closest('.flatpickr-input')) return;
                fpDevolucion.open();
            });
        }

        // =============================================
        // ACTUALIZAR UI INICIAL
        // =============================================
        if (fpEntrega.selectedDates.length > 0) {
            actualizarUIFechas(fpEntrega.selectedDates[0], 'entrega');
        }
        if (fpDevolucion.selectedDates.length > 0) {
            actualizarUIFechas(fpDevolucion.selectedDates[0], 'devolucion');
        }

        // Exponer funciones globalmente para usar desde otros lugares
        window.fpEntrega = fpEntrega;
        window.fpDevolucion = fpDevolucion;

        console.log('✅ Calendarios Flatpickr configurados correctamente');
    })();

    // ================================ VEHÍCULOS Y UPGRADES ===============================

    function mostrarModalOferta(oferta) {
        const modal = $elId("modalUpgrade");
        if (!modal) return;
        ContratoUI.setText($elId("upgTitulo"), oferta.nombre);
        ContratoUI.setText($elId("upgPrecioInflado"), `$${oferta.precioInflado}`);
        ContratoUI.setText($elId("upgPrecioReal"), `$${oferta.precioReal}`);
        ContratoUI.setText($elId("upgDescuento"), `${oferta.descuento}%`);
        ContratoUI.setText($elId("upgDescripcion"), oferta.descripcion);
        $elId("upgImagenVehiculo").src = oferta.imagen;
        ContratoUI.setText($elId("upgNombreVehiculo"), oferta.nombre_vehiculo);
        $elId("upgSpecs").innerHTML = `<div>${oferta.transmision ?? "—"}</div><div>${oferta.asientos ?? "--"} asientos</div><div>${oferta.puertas ?? "--"} puertas</div><div>${oferta.color ?? "—"}</div>`;
        modal.dataset.idCategoriaUpgrade = oferta.id_categoria;
        modal.classList.add("show");
    }

    $elId("btnAceptarUpgrade")?.addEventListener("click", async () => {
        const modal = $elId("modalUpgrade");
        const btn = $elId("btnAceptarUpgrade");
        btn.disabled = true; btn.innerHTML = "Aplicando...";
        try {
            const result = await ContratoAPI.postJSON(`/admin/contrato/${window.ID_RESERVACION}/actualizar-categoria`, {
                id_categoria: modal.dataset.idCategoriaUpgrade
            });
            if (result.success) {
                if (typeof window.cargarResumenBasico === 'function') await window.cargarResumenBasico();
                ContratoUI.notify("success", "Upgrade aplicado.");
                modal.classList.remove("show");
                window.showStep(2);
            }
        } catch (e) {
            ContratoUI.notify("error", "Error de conexión.");
            btn.disabled = false; btn.innerHTML = "Aceptar upgrade";
        }
    });

    $elId("btnRechazarUpgrade")?.addEventListener("click", () => { $elId("modalUpgrade").classList.remove("show"); window.showStep(2); });
    $elId("cerrarUpgrade")?.addEventListener("click", () => $elId("modalUpgrade").classList.remove("show"));

    // ================================ CAMBIO DE CATEGORÍA ===============================

    (function inicializarCambioCategoria() {
        const modalCat = $elId("modalCategorias");
        const btnAbrir = $elId("btnCambiarCategoria");
        const btnCerrar = $elId("cerrarModalCategorias");
        const btnCerrar2 = $elId("cerrarModalCategorias2");
        const contenedorCategorias = $elId("contenedorCategoriasJS");
        const elInicial = $elId("contratoInicial");

        if (!modalCat || !btnAbrir) return;

        // 🟢 Aseguramos que solo use la clase show-modal y bloquee el scroll de fondo
        const abrirModal = () => {
            modalCat.classList.add("show-modal");
            document.body.style.overflow = "hidden";
        };
        const cerrarModal = () => {
            modalCat.classList.remove("show-modal");
            document.body.style.overflow = "auto";
        };

        btnCerrar?.addEventListener("click", cerrarModal);
        btnCerrar2?.addEventListener("click", cerrarModal);

        modalCat.addEventListener("click", (e) => {
            if (e.target === modalCat) cerrarModal();
        });

        // Evento CLIC explícito para abrir y cargar datos
        btnAbrir.addEventListener("click", async (e) => {
            e.preventDefault(); // Evita comportamientos raros de botones en formularios
            abrirModal();

            if (!contenedorCategorias) return;

            contenedorCategorias.innerHTML = '<div style="width: 100%; text-align: center; padding: 40px; color: #64748b;">⏳ Cargando catálogo de categorías...</div>';

            try {
                // Fetch a la ruta que declaraste en tu web.php
                const data = await ContratoAPI.getJSON('/admin/contrato/categorias-dinamicas');

                if (data.success && data.categorias && data.categorias.length > 0) {
                    renderizarCategorias(data.categorias);
                } else {
                    contenedorCategorias.innerHTML = '<p style="text-align:center; color:#64748b; padding: 20px;">No hay categorías disponibles en este momento.</p>';
                }
            } catch (err) {
                console.error("Error cargando categorías dinámicas:", err);
                contenedorCategorias.innerHTML = '<p style="text-align:center;">Hubo un error de conexión al cargar el catálogo.</p>';
            }
        });

        function renderizarCategorias(categorias) {
            contenedorCategorias.innerHTML = '';
            // Buscamos el elemento de nuevo para asegurar que tenemos el dataset actualizado
            const cIni = document.getElementById('contratoInicial');
            const categoriaActualId = cIni ? cIni.dataset.idCategoria : null;

            categorias.forEach(cat => {
                const isActive = (cat.id_categoria == categoriaActualId);
                const precioFormateado = ContratoUI.money(cat.precio_dia || 0);

                // 🟢 Fíjate en el onerror="" de la imagen, eso salva las rutas rotas.
                const cardHtml = `
                    <div class="card-categoria ${isActive ? 'activa' : ''}"
                        data-id-categoria="${cat.id_categoria}"
                        data-codigo="${cat.codigo}"
                        data-precio="${cat.precio_dia || 0}"
                        data-nombre="${cat.nombre}">

                        <div class="cat-img-wrapper">
                            <img src="${cat.imagen}" alt="${cat.nombre}" onerror="this.src='/img/Logotipo.png';">
                        </div>

                        <div class="cat-info">
                            <div class="cat-codigo">${cat.codigo}</div>
                            <div class="cat-nombre">${cat.nombre}</div>
                            <div class="cat-precio">${precioFormateado} /día</div>
                        </div>

                        ${isActive ? '<span class="cat-badge-actual">Actual</span>' : ''}
                    </div>
                `;

                contenedorCategorias.insertAdjacentHTML('beforeend', cardHtml);
            });
        }

        let categoriaEnProceso = false;

        contenedorCategorias?.addEventListener("click", async (e) => {
            const card = e.target.closest(".card-categoria");
            if (!card) return;

            if (categoriaEnProceso) return;

            const idCategoria = card.dataset.idCategoria;
            const nombreCat = card.dataset.nombre || "";
            const codigoCat = card.dataset.codigo || "";

            if (card.classList.contains("activa")) {
                ContratoUI.notify("warning", "Esa ya es la categoría actual.");
                return;
            }

            const inputE = $elId("pickerEntrega");
            const inputD = $elId("pickerDevolucion");

            if (!inputE?.value || !inputD?.value) {
                return ContratoUI.notify("error", "No se pudieron leer las fechas de la reservación.");
            }

            const [fechaInicio, horaInicio] = inputE.value.split(" ");
            const [fechaFin, horaFin] = inputD.value.split(" ");

            categoriaEnProceso = true;
            card.style.opacity = "0.5";
            card.style.pointerEvents = "none";
            contenedorCategorias.style.pointerEvents = "none";

            try {
                const result = await ContratoAPI.postJSON(
                    `/admin/contrato/${window.ID_RESERVACION}/recalcular-total`,
                    {
                        id_categoria: idCategoria,
                        fecha_inicio: fechaInicio,
                        hora_inicio: horaInicio,
                        fecha_fin: fechaFin,
                        hora_fin: horaFin
                    }
                );

                if (result.success) {
                    if (elInicial) elInicial.dataset.idCategoria = idCategoria;

                    contenedorCategorias.querySelectorAll(".card-categoria").forEach(c => {
                        c.classList.toggle("activa", c.dataset.idCategoria === idCategoria);
                        const badge = c.querySelector(".cat-badge-actual");
                        if (badge) badge.remove();
                    });

                    if (elInicial) {
                        elInicial.dataset.idCategoria = idCategoria;
                        elInicial.dataset.codigoCategoria = codigoCat;
                    }
                    document.dispatchEvent(new CustomEvent('categoriaCambiada', {
                        detail: { categoria: codigoCat, id: idCategoria, nombre: nombreCat }
                    }));

                    if (typeof actualizarTodasLasGarantias === "function") {
                        actualizarTodasLasGarantias();
                    }

                    const badge = document.createElement("span");
                    badge.className = "cat-badge-actual";
                    badge.textContent = "Actual";
                    card.appendChild(badge);

                    if (typeof window.cargarResumenBasico === "function") {
                        await window.cargarResumenBasico();
                    }

                    ContratoUI.notify("success", `Categoría cambiada a ${nombreCat}.`);
                    cerrarModal();
                } else {
                    ContratoUI.notify("error", result.error || "No se pudo cambiar la categoría.");
                }
            } catch (err) {
                console.error("Error al cambiar categoría:", err);
                ContratoUI.notify("error", "Error de conexión al cambiar categoría.");
            } finally {
                categoriaEnProceso = false;
                card.style.opacity = "1";
                card.style.pointerEvents = "";
                contenedorCategorias.style.pointerEvents = "";
            }
        });
    })();

    // ================================ LÓGICA DEL DELIVERY ===============================

    const deliveryToggle = $el("#deliveryToggle");
    const deliveryFields = $el("#deliveryFields");
    const deliveryUbicacion = $el("#deliveryUbicacion");
    const deliveryDireccion = $el("#deliveryDireccion");
    const deliveryKm = $el("#deliveryKm");

    const getCostoKm = () => parseFloat($el("#deliveryPrecioKm")?.value || 0);

    const recalcularDelivery = () => {
        let kms = 0;
        if (deliveryToggle?.checked) {
            if (deliveryUbicacion?.value && deliveryUbicacion.value !== "0") {
                kms = parseFloat(deliveryUbicacion.options[deliveryUbicacion.selectedIndex].dataset.km || 0);
            } else if (deliveryUbicacion?.value === "0") {
                kms = parseFloat(deliveryKm?.value || 0);
            }
        }

        setState("deliveryTotalActual", kms * getCostoKm());
        ContratoUI.setText($el("#deliveryTotal"), ContratoUI.money(ContratoState.deliveryTotalActual));
        actualizarTotalServicios();
    };

    const enviarDeliveryAPI = async () => {
        if (!window.ID_RESERVACION) return;
        let kms = 0, ubicacionVal = "0";

        if (deliveryToggle?.checked) {
            if (deliveryUbicacion?.value && deliveryUbicacion?.value !== "0") {
                kms = parseFloat(deliveryUbicacion.options[deliveryUbicacion.selectedIndex].dataset.km || 0);
                ubicacionVal = deliveryUbicacion.value;
            } else {
                kms = parseFloat(deliveryKm?.value || 0);
            }
        }

        try {
            await ContratoAPI.postJSON(`/admin/reservacion/delivery/guardar`, {
                id_reservacion: window.ID_RESERVACION,
                delivery_activo: deliveryToggle?.checked ? 1 : 0,
                delivery_ubicacion: ubicacionVal,
                delivery_direccion: deliveryToggle?.checked ? (deliveryDireccion?.value || null) : null,
                delivery_km: kms,
                delivery_precio_km: getCostoKm(),
                delivery_total: ContratoState.deliveryTotalActual || 0
            });
            if (typeof window.cargarResumenBasico === 'function') await window.cargarResumenBasico();
        } catch (err) { console.error("Error guardando delivery:", err); }
    };

    const guardarDeliveryDebounced = debounce(enviarDeliveryAPI, 300);

    function guardarDeliverySeguro(inmediato = false) {
        inmediato ? enviarDeliveryAPI() : guardarDeliveryDebounced();
    }

    const syncDeliveryUI = () => {
        const isOn = !!deliveryToggle?.checked;
        if (deliveryFields) deliveryFields.style.display = isOn ? "block" : "none";
        const card = document.querySelector(".delivery-wrapper");
        if (card) card.classList.toggle("active", isOn);
    };

    deliveryToggle?.addEventListener("change", () => {
        syncDeliveryUI();
        if (!deliveryToggle.checked) {
            setState("deliveryTotalActual", 0);
            if (deliveryUbicacion) deliveryUbicacion.value = "";
            if (deliveryKm) deliveryKm.value = "";
            if ($el("#groupDireccion")) $el("#groupDireccion").style.display = "none";
            if ($el("#groupKm")) $el("#groupKm").style.display = "none";
            ContratoUI.setText($el("#deliveryTotal"), ContratoUI.money(0));
            actualizarTotalServicios();
            guardarDeliverySeguro(true);
        } else {
            recalcularDelivery();
            guardarDeliverySeguro();
        }
    });

    deliveryUbicacion?.addEventListener("change", () => {
        const esManual = deliveryUbicacion.value === "0";
        if ($el("#groupDireccion")) $el("#groupDireccion").style.display = esManual ? "block" : "none";
        if ($el("#groupKm")) $el("#groupKm").style.display = esManual ? "block" : "none";
        recalcularDelivery();
        guardarDeliverySeguro();
    });

    deliveryKm?.addEventListener("input", () => { recalcularDelivery(); guardarDeliverySeguro(); });
    deliveryDireccion?.addEventListener("input", () => guardarDeliverySeguro());

    if ($el("#deliveryTotalHidden")) {
        setState("deliveryTotalActual", parseFloat($el("#deliveryTotalHidden").value || 0));
        actualizarTotalServicios();
    }

    syncDeliveryUI();
    if (deliveryToggle?.checked) recalcularDelivery();

    // ================================ LÓGICA DEL DROPOFF ===============================

    const dropSwitch = $elId("switchDropoffCheckbox");
    const dropoffFields = $elId("dropoffFields");
    const dropUbicacion = $elId("dropUbicacion");
    const dropDireccion = $elId("dropDireccion");
    const dropKm = $elId("dropKm");

    const getCostoKmDropoff = () => parseFloat($elId("deliveryPrecioKm")?.value || 15);

    const enviarDropoffAPI = async (isCustom, kms, precioKmActual) => {
        try {
            await ContratoAPI.postJSON('/admin/contrato/cargo-variable', {
                id_reservacion: window.ID_RESERVACION,
                id_contrato: window.ID_CONTRATO,
                id_concepto: 6,
                destino: isCustom ? (dropDireccion?.value || "") : dropUbicacion.options[dropUbicacion.selectedIndex]?.text,
                km: kms,
                precio_km: precioKmActual,
                monto_variable: ContratoState.dropoffTotal
            });
            if (typeof window.cargarResumenBasico === 'function') window.cargarResumenBasico();
        } catch (e) { console.error("Error guardando dropoff", e); }
    };

    const enviarDropoffDebounced = debounce(enviarDropoffAPI, 300);

    function handleDropoffUpdate(inmediato = false) {
        if (!dropUbicacion) return;

        const val = dropUbicacion.value;
        const isCustom = (val === "0");

        if ($elId("dropGroupDireccion")) $elId("dropGroupDireccion").style.display = isCustom ? "block" : "none";
        if ($elId("dropGroupKm")) $elId("dropGroupKm").style.display = isCustom ? "block" : "none";

        let precioKmActual = getCostoKmDropoff();
        if (val !== "") ContratoUI.setText($elId("dropCostoKmHTML"), ContratoUI.money(precioKmActual));

        let kms = isCustom
            ? parseFloat(dropKm?.value || 0)
            : parseFloat(dropUbicacion.options[dropUbicacion.selectedIndex]?.dataset.km || 0);

        setState("dropoffTotal", kms * precioKmActual);
        ContratoUI.setText($elId("dropTotalHTML"), ContratoUI.money(ContratoState.dropoffTotal));

        const card = document.querySelector('.cargo-item[data-id="6"]');
        if (card) card.dataset.monto = ContratoState.dropoffTotal;

        actualizarTotalServicios();

        if (inmediato) enviarDropoffAPI(isCustom, kms, precioKmActual);
        else enviarDropoffDebounced(isCustom, kms, precioKmActual);
    }

    dropSwitch?.addEventListener("change", async () => {
        const isOn = dropSwitch.checked;
        if (dropoffFields) dropoffFields.style.display = isOn ? "block" : "none";

        const card = document.querySelector('.cargo-item[data-id="6"]');
        if (card) card.classList.toggle("active", isOn);

        if (!isOn) {
            setState("dropoffTotal", 0);
            if (card) card.dataset.monto = "0";
            ContratoUI.setText($elId("dropTotalHTML"), ContratoUI.money(0));
            actualizarTotalServicios();

            try {
                await ContratoAPI.postJSON('/admin/contrato/cargo-variable', {
                    id_reservacion: window.ID_RESERVACION,
                    id_contrato: window.ID_CONTRATO,
                    id_concepto: 6,
                    monto_variable: 0
                });

                setTimeout(() => {
                    if (typeof window.cargarResumenBasico === 'function') {
                        window.cargarResumenBasico();
                    }
                }, 150);

            } catch (e) { console.error("Error al borrar dropoff:", e); }
        } else {
            handleDropoffUpdate(true);
        }
    });
    dropUbicacion?.addEventListener("change", () => handleDropoffUpdate(true));

    $elId("dropoffFields")?.addEventListener("input", (e) => {
        if (['dropKm', 'dropDireccion'].includes(e.target.id)) handleDropoffUpdate(false);
    });

    if (dropSwitch && dropSwitch.checked) {
        const card = document.querySelector('.cargo-item[data-id="6"]');
        setState("dropoffTotal", parseFloat(card?.dataset.monto || 0));
        handleDropoffUpdate(true);
    } else {
        setState("dropoffTotal", 0);
    }

    // ================================ LÓGICA DE GASOLINA PREPAGO ===============================

    const gasSwitch = $elId("switchGasolinaCheckbox");
    const gasolinaFields = $elId("gasolinaFields");

    const syncGasolinaUI = () => {
        const isOn = !!gasSwitch?.checked;
        if (gasolinaFields) gasolinaFields.style.display = isOn ? "block" : "none";
        const card = document.querySelector('.cargo-item[data-id="5"]');
        if (card) card.classList.toggle("active", isOn);
    };

    gasSwitch?.addEventListener("change", async () => {
        const isOn = gasSwitch.checked;
        syncGasolinaUI();

        if (!isOn) {
            setState("gasolinaTotal", 0);
            const card = document.querySelector('.cargo-item[data-id="5"]');
            if (card) card.dataset.monto = "0";
            ContratoUI.setText($elId("gasTotalHTML"), ContratoUI.money(0));
            actualizarTotalServicios();

            try {
                await ContratoAPI.postJSON('/admin/contrato/cargo-variable', {
                    id_reservacion: window.ID_RESERVACION,
                    id_contrato: window.ID_CONTRATO,
                    id_concepto: 5,
                    monto_variable: 0
                });

                setTimeout(() => {
                    if (typeof window.cargarResumenBasico === 'function') {
                        window.cargarResumenBasico();
                    }
                }, 150);
            } catch (e) { console.error("Error al borrar gasolina:", e); }
        } else {
            window.handleGasolinaUpdate();
        }
    });

    window.handleGasolinaUpdate = function () {
        const gasSwitch = $elId("switchGasolinaCheckbox");
        if (!gasSwitch || !gasSwitch.checked) return;

        const inputGasNivelActual = $elId("gasNivelActual");
        const inputGasPrecioLitro = $elId("gasPrecioLitro");

        let valorCrudo = inputGasNivelActual?.value || "16";
        let coincidencia = String(valorCrudo).match(/\d+/);
        let nivelActual = coincidencia ? parseFloat(coincidencia[0]) : 16;
        let capacidadTanque = 16;
        let precioLitro = parseFloat(inputGasPrecioLitro?.value || 20);

        if (nivelActual > capacidadTanque) nivelActual = capacidadTanque;

        let faltante = capacidadTanque - nivelActual;
        setState("gasolinaTotal", faltante > 0 ? (faltante * precioLitro) : 0);

        ContratoUI.setText($elId("gasNivelTexto"), `${nivelActual}/${capacidadTanque}`);
        ContratoUI.setText($elId("gasLitrosTexto"), `${faltante} L`);
        ContratoUI.setText($elId("gasTotalHTML"), ContratoUI.money(ContratoState.gasolinaTotal));

        const card = document.querySelector('.cargo-item[data-id="5"]');
        if (card) card.dataset.monto = ContratoState.gasolinaTotal;

        if (typeof actualizarTotalServicios === 'function') actualizarTotalServicios();

        ContratoAPI.postJSON('/admin/contrato/cargo-variable', {
            id_reservacion: window.ID_RESERVACION, id_contrato: window.ID_CONTRATO, id_concepto: 5,
            litros: faltante, precio_litro: precioLitro, monto_variable: ContratoState.gasolinaTotal
        }).then(async () => {
            if (typeof window.cargarResumenBasico === 'function') window.cargarResumenBasico();
        }).catch(e => console.error("Error gasolina:", e));
    };

    syncGasolinaUI();

    if (gasSwitch && gasSwitch.checked) {
        const card = document.querySelector('.cargo-item[data-id="5"]');
        setState("gasolinaTotal", parseFloat(card?.dataset.monto || 0));
        window.handleGasolinaUpdate();
    } else {
        setState("gasolinaTotal", 0);
    }

    // ================================ TOGGLE DEL RESUMEN ===============================

    const btnToggleDetalle = document.getElementById('btnToggleDetalle');
    const resumenContainer = document.getElementById('resumenDetalleContainer');
    const iconoFlecha = document.getElementById('iconoFlechaResumen');

    if (btnToggleDetalle && resumenContainer) {
        btnToggleDetalle.addEventListener('click', function (e) {
            e.stopPropagation();

            // Alternar la clase 'abierto' para mostrar/ocultar
            const estaAbierto = resumenContainer.classList.contains('abierto');

            if (estaAbierto) {
                resumenContainer.classList.remove('abierto');
                resumenContainer.style.display = 'none';
                if (iconoFlecha) iconoFlecha.style.transform = 'rotate(0deg)';
            } else {
                resumenContainer.classList.add('abierto');
                resumenContainer.style.display = 'block';
                if (iconoFlecha) iconoFlecha.style.transform = 'rotate(180deg)';
            }
        });

        // Opcional: Cerrar el resumen al hacer clic fuera de él
        document.addEventListener('click', function (e) {
            if (resumenContainer.classList.contains('abierto')) {
                const isClickInside = btnToggleDetalle.contains(e.target) || resumenContainer.contains(e.target);
                if (!isClickInside) {
                    resumenContainer.classList.remove('abierto');
                    resumenContainer.style.display = 'none';
                    if (iconoFlecha) iconoFlecha.style.transform = 'rotate(0deg)';
                }
            }
        });
    }

    // ================================ PASO 2: SERVICIOS ===============================

    function actualizarTotalServicios() {
        const elDiasRenta = document.getElementById("detDiasRenta");
        const diasRenta = parseInt(elDiasRenta?.textContent || 1);
        const cards = document.querySelectorAll(".card-servicio");
        const displayTotal = document.querySelector("#total_servicios");

        let subtotalServicios = 0;

        cards.forEach(card => {
            const precio = parseFloat(card.dataset.precio || 0);
            const cantidad = parseInt(card.querySelector(".cantidad")?.textContent || 0);
            const tipoCobro = card.dataset.tipo;

            if (tipoCobro === 'por_dia') {
                subtotalServicios += (precio * cantidad) * diasRenta;
            } else {
                subtotalServicios += (precio * cantidad);
            }
        });

        const totalFinal = subtotalServicios +
            parseFloat(ContratoState.deliveryTotalActual || 0) +
            parseFloat(ContratoState.dropoffTotal || 0) +
            parseFloat(ContratoState.gasolinaTotal || 0);

        window.requestAnimationFrame(() => {
            if (displayTotal) {
                ContratoUI.setText(displayTotal, ContratoUI.money(totalFinal));
            }
        });
    }

    const timersServicios = {};

    const gridServicios = document.querySelector("#serviciosGrid");

    if (gridServicios) {
        gridServicios.addEventListener("click", (e) => {
            const btn = e.target;
            if (!btn.classList.contains("mas") && !btn.classList.contains("menos")) return;

            const card = btn.closest(".card-servicio");
            const idServicio = card.dataset.id;
            const cantEl = card.querySelector(".cantidad");
            let cant = parseInt(cantEl.textContent);

            if (btn.classList.contains("mas")) cant++;
            else if (cant > 0) cant--;

            cantEl.textContent = cant;

            actualizarTotalServicios();

            // 3. Limpiamos el temporizador SOLO de este servicio específico
            if (timersServicios[idServicio]) {
                clearTimeout(timersServicios[idServicio]);
            }

            timersServicios[idServicio] = setTimeout(async () => {
                try {
                    await ContratoAPI.postJSON(`/admin/contrato/servicios`, {
                        id_reservacion: window.ID_RESERVACION,
                        id_servicio: idServicio,
                        cantidad: cant,
                        precio_unitario: card.dataset.precio
                    });

                    if (typeof window.cargarResumenBasico === 'function') {
                        window.cargarResumenBasico();
                    }
                } catch (err) {
                    console.error("Error al guardar el servicio:", err);
                    if (typeof window.cargarResumenBasico === 'function') {
                        window.cargarResumenBasico();
                    }
                }
            }, 250);
        });
    }

    // ================================ PASO 3: SEGUROS ===============================

    function recalcularTotalProtecciones() {
        const display = $elId("total_seguros");
        const btnGo = $elId("go4");

        let subtotalPorDia = 0;
        let haySeleccion = false;

        const packActive = document.querySelector(".input-paquete:checked");
        if (packActive) {
            const seguroItem = packActive.closest(".pack-card");
            if (seguroItem) {
                subtotalPorDia = parseFloat(seguroItem.dataset.precio || 0);
                haySeleccion = true;
            }
        } else {
            const individualesActivos = document.querySelectorAll(".switch-individual:checked");
            if (individualesActivos.length > 0) {
                haySeleccion = true;
                individualesActivos.forEach(checkbox => {
                    const individualItem = checkbox.closest(".individual-card");
                    if (individualItem) {
                        subtotalPorDia += parseFloat(individualItem.dataset.precio || 0);
                    }
                });
            }
        }

        if (display) {
            const diasRenta = parseInt($elId("detDiasRenta")?.textContent || 1);
            display.textContent = ContratoUI.money(subtotalPorDia * diasRenta);
        }

        if (btnGo) {
            if (haySeleccion) {
                btnGo.classList.remove("disabled");
                btnGo.style.opacity = "1";
                btnGo.style.pointerEvents = "auto";
            } else {
                btnGo.classList.add("disabled");
                btnGo.style.opacity = "0.5";
                btnGo.style.pointerEvents = "none";
            }
        }

        window.recalcularTotalProtecciones = recalcularTotalProtecciones;
    }

    // --- LÓGICA PARA CAMBIAR VISTAS (PAQUETES VS INDIVIDUALES) ---
    const btnVerPaquetes = $elId('btnVerPaquetes');
    const btnVerIndividuales = $elId('btnVerIndividuales');
    const btnToggleVista = $elId('btnToggleVista');
    const btnContinuar = $elId('go4');

    const vistaPaquetes = $elId('vista-paquetes');
    const vistaIndividuales = $elId('vista-individuales');

    // Función centralizada para alternar las vistas
    function cambiarVistaProtecciones(vistaDestino) {
        if (vistaDestino === 'paquetes') {
            if (vistaIndividuales) vistaIndividuales.style.display = 'none';
            if (vistaPaquetes) vistaPaquetes.style.display = 'block';

            // Actualizar textos y estilos
            if (btnToggleVista) btnToggleVista.innerText = 'SELECCIÓN INDIVIDUAL';

            if (btnVerPaquetes) {
                btnVerPaquetes.classList.add('primary');
                btnVerPaquetes.classList.remove('gray');
            }
            if (btnVerIndividuales) {
                btnVerIndividuales.classList.add('gray');
                btnVerIndividuales.classList.remove('primary');
            }
        } else {
            if (vistaPaquetes) vistaPaquetes.style.display = 'none';
            if (vistaIndividuales) vistaIndividuales.style.display = 'block';

            // Actualizar textos y estilos
            if (btnToggleVista) btnToggleVista.innerText = 'VER PAQUETES';

            if (btnVerIndividuales) {
                btnVerIndividuales.classList.add('primary');
                btnVerIndividuales.classList.remove('gray');
            }
            if (btnVerPaquetes) {
                btnVerPaquetes.classList.add('gray');
                btnVerPaquetes.classList.remove('primary');
            }
        }
    }

    // Conectar botones superiores
    if (btnVerPaquetes) btnVerPaquetes.addEventListener('click', () => cambiarVistaProtecciones('paquetes'));
    if (btnVerIndividuales) btnVerIndividuales.addEventListener('click', () => cambiarVistaProtecciones('individuales'));

    // Conectar botón inferior
    if (btnToggleVista) {
        btnToggleVista.addEventListener('click', function () {
            const vistaActual = (vistaPaquetes && vistaPaquetes.style.display !== 'none') ? 'individuales' : 'paquetes';
            cambiarVistaProtecciones(vistaActual);
        });
    }

    // Ejecutar al cargar para configurar estado inicial
    setTimeout(recalcularTotalProtecciones, 300);

    // ================================ NAVEGACIÓN Y GUARDADO ===============================

    function precargarPaso4() {
        const idReservacion = window.ID_RESERVACION;
        if (!idReservacion) return;

        if (!document.querySelector(`link[data-contrato-prefetch="${idReservacion}"]`)) {
            const link = document.createElement("link");
            link.rel = "prefetch";
            link.as = "document";
            link.href = `/admin/contrato2/${idReservacion}`;
            link.dataset.contratoPrefetch = idReservacion;
            document.head.appendChild(link);
        }
    }

    function obtenerVehiculoSeleccionadoId() {
        return $elId("contratoInicial")?.dataset?.idVehiculo?.trim() || "";
    }

    // =============================================
    // BOTÓN CONTINUAR - PASO 1 → PASO 2
    // =============================================
    $el("#go2")?.addEventListener("click", async () => {
        // Validar que haya un vehículo seleccionado
        if (!obtenerVehiculoSeleccionadoId()) {
            return ContratoUI.notify("error", "Debes seleccionar un vehículo antes de continuar.");
        }

        // Ir directamente al Paso 2 (Servicios) - SIN MODAL DE UPGRADE
        window.showStep(2);
    });

    // =============================================
    // BOTÓN CONTINUAR - PASO 2 → PASO 3
    // =============================================
    $el("#go3")?.addEventListener("click", () => {
        if (typeof guardarDeliverySeguro === 'function') guardarDeliverySeguro(true);
        setTimeout(() => {
            if (typeof window.cargarResumenBasico === 'function') window.cargarResumenBasico();
        }, 150);
        precargarPaso4();
        window.showStep(3);
    });

    // =============================================
    // BOTÓN CONTINUAR - PASO 3 → PASO 4
    // =============================================
    $el("#go4")?.addEventListener("click", (e) => {
        e.preventDefault();

        const elInicial = $elId("contratoInicial");
        const idReservacion = window.ID_RESERVACION || (elInicial ? elInicial.dataset.idReservacion : null);

        if (!idReservacion) return ContratoUI.notify("error", "Error: ID de reservación perdido.");

        const paqueteSeleccionado = document.querySelector(".input-paquete:checked");
        const individualSeleccionado = document.querySelector(".switch-individual:checked");

        if (!paqueteSeleccionado && !individualSeleccionado) {
            return ContratoUI.notify("warning", "Selecciona al menos un paquete o una protección para continuar.");
        }

        localStorage.setItem(`contratoPasoActual_${idReservacion}`, '4');

        const btn = e.currentTarget;
        btn.innerHTML = "Cargando Paso 4...";
        btn.style.pointerEvents = "none";

        precargarPaso4();
        window.location.href = `/admin/contrato2/${idReservacion}`;
    });

    // =============================================
    // BOTONES ATRÁS
    // =============================================
    $el("#back1")?.addEventListener("click", () => window.showStep(1));
    $el("#back2")?.addEventListener("click", () => window.showStep(2));
    $el("#back3")?.addEventListener("click", () => window.showStep(3));

    // =============================================
    // SALTO AUTOMÁTICO DE PASO (desde localStorage)
    // =============================================
    const ejecutarSaltoDePaso = () => {
        const idRes = window.ID_RESERVACION || $elId("contratoInicial")?.dataset.idReservacion;
        if (!idRes) return;

        const storageKey = `contratoPasoActual_${idRes}`;
        const pasoSolicitado = localStorage.getItem(storageKey);

        if (pasoSolicitado) {
            const pasoN = Number(pasoSolicitado);
            if ([1, 2, 3].includes(pasoN)) {
                console.log(`🚀 Saltando automáticamente al Paso: ${pasoN}`);
                if (typeof window.showStep === 'function') window.showStep(pasoN);
                if (typeof window.actualizarStepper === 'function') window.actualizarStepper(pasoN);
            }
            localStorage.removeItem(storageKey);
        }
    };

    setTimeout(ejecutarSaltoDePaso, 50);

    // =============================================
    // INICIALIZACIÓN DE GASOLINA (carga de datos guardados)
    // =============================================
    setTimeout(async () => {
        const gasSwitch = $elId("switchGasolinaCheckbox");
        if (gasSwitch && gasSwitch.checked) {
            const cardGas = document.querySelector('.cargo-item[data-id="5"]');
            const montoGuardado = parseFloat(cardGas?.dataset.monto || 0);

            if (montoGuardado > 0) {
                setState("gasolinaTotal", montoGuardado);
                ContratoUI.setText($elId("gasTotalHTML"), ContratoUI.money(montoGuardado));

                const inputNivel = $elId("gasNivelActual")?.value || "16/16";
                let nivel = parseInt(inputNivel.split('/')[0]) || 16;
                ContratoUI.setText($elId("gasLitrosTexto"), `${16 - nivel} L`);
            } else {
                if (typeof window.handleGasolinaUpdate === 'function') window.handleGasolinaUpdate();
            }
        }
    }, 800);

    // ================================ MODAL DE PROTECCIONES ===============================
    (function () {
        console.log("🟢 Inicializando Modal de Protecciones...");

        let modal, btnAbrir, btnAplicar;
        let totalModal, totalResumen, resumenNombre;

        const FORZADOS = ["LOU", "LA"];
        const DEFAULT_COLISION = ["DECLINE CDW", "DECLINE"];
        const DEFAULT_TERCEROS = ["LI"];

        const SECCIONES_UNICAS = [
            "Colisión y robo",
            "Daños a terceros",
            "Gastos médicos",
            "Asistencia para el camino"
        ];

        let paqueteSeleccionado = null;
        let individualesSeleccionados = new Map();

        function getElements() {
            modal = document.getElementById("modalProtecciones");
            btnAbrir = document.getElementById("btnAbrirModalProtecciones");
            btnAplicar = document.getElementById("btnAplicarProtecciones");
            totalModal = document.getElementById("total_seguros_modal");
            totalResumen = document.getElementById("total_seguros_resumen");
            resumenNombre = document.getElementById("resumen_nombre_proteccion");

            return modal && btnAbrir;
        }

        function money(value) {
            const n = parseFloat(value || 0);
            return window.money ? window.money(n) : `$${n.toFixed(2)} MXN`;
        }

        function getNombreIndividual(card) {
            return card?.querySelector(".individual-nombre")?.textContent?.trim() || "";
        }

        function normalizarTexto(txt) {
            return String(txt || "").trim().toUpperCase();
        }

        function getGrupoDeCard(card) {
            if (!card) return '';

            // Buscar el contenedor de la sección
            let section = card.closest('.individuales-grid');
            if (section) {
                // Buscar el título que está ANTES de este grid
                let prevElement = section.previousElementSibling;
                while (prevElement) {
                    if (prevElement.classList && prevElement.classList.contains('categoria-titulo-individual')) {
                        return prevElement.textContent.trim();
                    }
                    prevElement = prevElement.previousElementSibling;
                }
            }

            // Fallback: buscar cualquier título de categoría cercano
            const titulo = card.closest('.modal-view')?.querySelector('.categoria-titulo-individual');
            return titulo ? titulo.textContent.trim() : '';
        }

        function getIdCard(card) {
            return String(card?.dataset?.id || card?.querySelector(".switch-individual")?.value || "");
        }

        function getPrecioCard(card) {
            return parseFloat(card?.dataset?.precio || 0);
        }

        function esForzado(nombre) {
            return FORZADOS.includes(normalizarTexto(nombre));
        }

        function esDefault(nombre, grupo) {
            const n = normalizarTexto(nombre);
            const g = normalizarTexto(grupo);

            if (g.includes("COLISIÓN") || g.includes("COLISION") || g.includes("ROBO")) {
                return n.includes("DECLINE");
            }

            if (g.includes("TERCEROS") || g.includes("DAÑOS")) {
                return n === "LI" || n.startsWith("LI ") || n.includes("LI -");
            }

            return false;
        }

        // NUEVA FUNCIÓN: Normalizar nombre de sección para comparación
        function normalizarSeccion(texto) {
            const t = normalizarTexto(texto);
            if (t.includes("COLISIÓN") || t.includes("COLISION") || t.includes("ROBO")) return "COLISIÓN Y ROBO";
            if (t.includes("TERCEROS") || t.includes("DAÑOS")) return "DAÑOS A TERCEROS";
            if (t.includes("MÉDICOS") || t.includes("MEDICOS")) return "GASTOS MÉDICOS";
            if (t.includes("ASISTENCIA") || t.includes("CAMINO")) return "ASISTENCIA PARA EL CAMINO";
            return t;
        }

        function actualizarUICardPaquete(card, seleccionado) {
            if (!card) return;

            const radio = card.querySelector(".input-paquete");
            const label = card.querySelector(".btn-proteccion-dividido");
            const texto = label?.querySelector(".btn-texto");

            card.classList.toggle("is-selected", seleccionado);

            if (radio) radio.checked = seleccionado;

            if (label) {
                label.classList.toggle("activado", seleccionado);
                label.classList.toggle("desactivado", !seleccionado);
            }

            if (texto) {
                texto.textContent = seleccionado ? "Seleccionado ✓" : "Seleccionar";
            }
        }

        function actualizarUICardIndividual(card, checked) {
            if (!card) return;

            const checkbox = card.querySelector(".switch-individual");
            const nombre = getNombreIndividual(card);
            const forzado = esForzado(nombre);

            if (checkbox) {
                checkbox.checked = checked;
                checkbox.dataset.forzado = forzado ? "true" : "false";
                checkbox.disabled = forzado;
            }

            card.classList.toggle("selected", checked);

            const pill = card.querySelector(".switch-pill");
            if (pill && forzado && !pill.querySelector(".lock-badge")) {
                const badge = document.createElement("span");
                badge.className = "lock-badge";
                badge.textContent = " 🔒";
                pill.appendChild(badge);
            }
        }

        function limpiarPaquetes() {
            paqueteSeleccionado = null;

            modal.querySelectorAll(".input-paquete").forEach(radio => {
                radio.checked = false;
                actualizarUICardPaquete(radio.closest(".pack-card"), false);
            });
        }

        function limpiarIndividuales() {
            individualesSeleccionados.clear();

            modal.querySelectorAll(".individual-card").forEach(card => {
                actualizarUICardIndividual(card, false);
            });
        }

        function agregarIndividual(card, forzado = false) {
            const id = getIdCard(card);
            const nombre = getNombreIndividual(card);
            const grupo = getGrupoDeCard(card);
            const precio = getPrecioCard(card);

            if (!id) return;

            individualesSeleccionados.set(id, {
                id,
                nombre,
                grupo,
                precio,
                forzado
            });

            actualizarUICardIndividual(card, true);
        }

        function quitarIndividual(card) {
            const id = getIdCard(card);
            const nombre = getNombreIndividual(card);

            if (esForzado(nombre)) {
                actualizarUICardIndividual(card, true);
                return;
            }

            individualesSeleccionados.delete(id);
            actualizarUICardIndividual(card, false);
        }

        function aplicarDefaultsIndividuales() {
            limpiarPaquetes();
            limpiarIndividuales();

            modal.querySelectorAll(".individual-card").forEach(card => {
                const nombre = getNombreIndividual(card);
                const grupo = getGrupoDeCard(card);

                if (esForzado(nombre) || esDefault(nombre, grupo)) {
                    agregarIndividual(card, esForzado(nombre));
                }
            });

            actualizarTotales();
        }

        function seleccionarPaquete(radio) {
            limpiarIndividuales();

            modal.querySelectorAll(".input-paquete").forEach(r => {
                actualizarUICardPaquete(r.closest(".pack-card"), r === radio);
            });

            const card = radio.closest(".pack-card");
            paqueteSeleccionado = {
                id: radio.value,
                nombre: card?.querySelector("h4")?.textContent?.trim() || "Paquete",
                precio: parseFloat(card?.dataset?.precio || card?.closest(".seguro-item")?.dataset?.precio || 0)
            };

            actualizarTotales();
        }

        function seleccionarIndividual(card) {
            limpiarPaquetes();

            const id = getIdCard(card);
            const nombre = getNombreIndividual(card);
            const grupo = getGrupoDeCard(card);
            const checkbox = card.querySelector(".switch-individual");

            // Log para depuración
            console.log("🔄 Seleccionando individual:", { id, nombre, grupo });

            if (esForzado(nombre)) {
                agregarIndividual(card, true);
                return;
            }

            if (checkbox?.checked) {
                quitarIndividual(card);
                actualizarTotales();
                return;
            }

            // VERIFICAR SI ES UNA SECCIÓN ÚNICA USANDO NORMALIZACIÓN
            const grupoNormalizado = normalizarSeccion(grupo);
            const esSeccionUnica = SECCIONES_UNICAS.some(s => normalizarSeccion(s) === grupoNormalizado);

            console.log("📌 Grupo normalizado:", grupoNormalizado, "¿Es sección única?", esSeccionUnica);

            if (esSeccionUnica) {
                modal.querySelectorAll(".individual-card").forEach(otraCard => {
                    if (otraCard === card) return;

                    const otroGrupo = getGrupoDeCard(otraCard);
                    const otroNombre = getNombreIndividual(otraCard);
                    const otroGrupoNormalizado = normalizarSeccion(otroGrupo);

                    // Solo desactivar si es la misma sección y no es forzado
                    if (otroGrupoNormalizado === grupoNormalizado && !esForzado(otroNombre)) {
                        quitarIndividual(otraCard);
                    }
                });
            }

            agregarIndividual(card, false);
            actualizarTotales();
        }

        function pintarProteccionesEnCarrito() {
            const dias = parseInt(document.getElementById("detDiasRenta")?.textContent || 1);

            const listas = [
                document.getElementById("r_seguros_lista"),
                document.getElementById("r_seguros_listaModal")
            ];

            const totales = [
                document.getElementById("r_seguros_total"),
                document.getElementById("r_seguros_totalModal")
            ];

            let html = "";
            let total = 0;

            if (paqueteSeleccionado) {
                total = paqueteSeleccionado.precio * dias;

                html = `
            <li>
                <span>${paqueteSeleccionado.nombre}</span>
                <b>${money(total)}</b>
            </li>
        `;
            } else {
                const items = Array.from(individualesSeleccionados.values());

                items.forEach(item => {
                    const importe = item.precio * dias;
                    total += importe;

                    html += `
                <li>
                    <span>${item.nombre}</span>
                    <b>${money(importe)}</b>
                </li>
            `;
                });
            }

            listas.forEach(lista => {
                if (lista) {
                    lista.innerHTML = html || `<li class="empty">—</li>`;
                }
            });

            totales.forEach(totalEl => {
                if (totalEl) {
                    totalEl.textContent = money(total);
                }
            });
            const compacto = document.getElementById("resumenProteccionesCompacto");
            const compactoModal = document.getElementById("resumenProteccionesCompactoModal");

            let textoCompacto = "—";

            if (paqueteSeleccionado) {
                textoCompacto = paqueteSeleccionado.nombre;
            } else {
                const items = Array.from(individualesSeleccionados.values());

                if (items.length > 0) {
                    textoCompacto = items.map(i => i.nombre).join(", ");
                }
            }

            if (compacto) compacto.textContent = textoCompacto;
            if (compactoModal) compactoModal.textContent = textoCompacto;
        }

        function actualizarTotales() {
            const dias = parseInt(document.getElementById("detDiasRenta")?.textContent || 1);
            let subtotal = 0;
            let textoResumen = "";

            if (paqueteSeleccionado) {
                subtotal = paqueteSeleccionado.precio * dias;
                textoResumen = `${paqueteSeleccionado.nombre}`;
            } else {
                const items = Array.from(individualesSeleccionados.values());

                items.forEach(item => {
                    subtotal += item.precio * dias;
                });

                textoResumen = ` ${items.map(i => i.nombre).join(", ")}`;
            }

            if (totalModal) totalModal.textContent = money(subtotal);
            if (totalResumen) totalResumen.textContent = money(subtotal);

            if (resumenNombre) {
                resumenNombre.textContent = textoResumen || " DECLINE CDW, LI, LOU, LA";
                resumenNombre.style.color = "#16a34a";
                resumenNombre.style.background = "#dcfce7";
            }

            pintarProteccionesEnCarrito();

            if (typeof copiarResumenNavbarAlModal === "function") {
                copiarResumenNavbarAlModal();
            }

            if (btnAplicar) {
                btnAplicar.disabled = false;
                btnAplicar.style.opacity = "1";
            }
        }

        function sincronizarModalAPaso() {
            const hayPaquete = !!paqueteSeleccionado;

            document.querySelectorAll(".input-paquete").forEach(radio => {
                radio.checked = hayPaquete && String(radio.value) === String(paqueteSeleccionado.id);
            });

            document.querySelectorAll(".switch-individual").forEach(cb => {
                const card = cb.closest(".individual-card");
                const id = getIdCard(card);

                cb.checked = !hayPaquete && individualesSeleccionados.has(id);
                card?.classList.toggle("selected", cb.checked);
            });

            if (typeof window.recalcularTotalProtecciones === "function") {
                window.recalcularTotalProtecciones();
            }
        }

        function abrirModal() {

            window.categoriaActual = obtenerCategoriaActual();
            console.log('📢 Abriendo modal, categoría forzada:', window.categoriaActual);

            modal.classList.add("active")
            modal.classList.add("active");
            modal.style.display = "flex";
            document.body.style.overflow = "hidden";

            const vistaPaquetes = document.getElementById("modal-vista-paquetes");
            const vistaIndividuales = document.getElementById("modal-vista-individuales");
            const tabPaquetes = document.getElementById("tabPaquetes");
            const tabIndividuales = document.getElementById("tabIndividuales");

            if (!paqueteSeleccionado && individualesSeleccionados.size === 0) {
                limpiarPaquetes();
                limpiarIndividuales();
            }

            if (vistaPaquetes) vistaPaquetes.style.display = "block";
            if (vistaIndividuales) vistaIndividuales.style.display = "none";

            tabPaquetes?.classList.add("active");
            tabIndividuales?.classList.remove("active");

            setTimeout(actualizarTodasLasGarantias, 200);
            actualizarTotales();
        }

        function cerrarModal() {
            modal.classList.remove("active");
            modal.style.display = "none";
            document.body.style.overflow = "";

            if (!paqueteSeleccionado && individualesSeleccionados.size === 0) {
                aplicarDefaultsIndividuales();
                sincronizarModalAPaso();
            }

            actualizarTotales();
        }

        async function guardarProteccionesEnBackend() {
            const idRes = window.ID_RESERVACION;
            if (!idRes) return;

            const payload = {
                id_reservacion: idRes,
                id_paquete: paqueteSeleccionado?.id ?? null,
                precio_por_dia: paqueteSeleccionado?.precio ?? null,
                individuales: paqueteSeleccionado
                    ? []
                    : Array.from(individualesSeleccionados.values()).map(i => ({
                        id: i.id,
                        precio: i.precio
                    }))
            };

            try {
                await ContratoAPI.postJSON('/admin/contrato/protecciones/sync', payload);

                if (typeof window.cargarResumenBasico === "function") {
                    await window.cargarResumenBasico();
                }

                if (typeof copiarResumenNavbarAlModal === "function") {
                    copiarResumenNavbarAlModal();
                }

            } catch (err) {
                console.error("Error guardando protecciones:", err);
                if (window.alertify) alertify.error("Error al guardar protecciones");
                throw err;
            }
        }

        async function hidratarDesdeBackend() {
            if (!window.ID_RESERVACION) return false;

            try {
                const resp = await fetch(`/admin/contrato/${window.ID_RESERVACION}/resumen?t=${Date.now()}`);
                if (!resp.ok) return false;

                const { success, data: r } = await resp.json();
                if (!success || !r?.seguros?.lista?.length) return false;

                console.log("Protecciones recuperadas:", r.seguros);

                limpiarPaquetes();
                limpiarIndividuales();

                let huboAlgo = false;

                r.seguros.lista.forEach(item => {

                    // ── Paquete ──
                    if (item.id_paquete) {
                        const radio = modal.querySelector(`.input-paquete[value="${item.id_paquete}"]`);
                        if (radio) {
                            seleccionarPaquete(radio);
                            huboAlgo = true;
                        }
                        return;
                    }

                    // ── Individual ──
                    if (item.id_individual) {
                        const card = modal.querySelector(`.individual-card[data-id="${item.id_individual}"]`);
                        if (card) {
                            agregarIndividual(card, esForzado(getNombreIndividual(card)));
                            huboAlgo = true;
                        }
                    }
                });

                if (huboAlgo) {
                    actualizarTotales();
                    sincronizarModalAPaso();
                    console.log("Protecciones hidratadas desde backend");
                }

                return huboAlgo;

            } catch (e) {
                console.error("Error hidratando protecciones:", e);
                return false;
            }
        }

        function setupEvents() {
            btnAbrir.addEventListener("click", e => {
                e.preventDefault();
                abrirModal();
            });

            document.getElementById("btnCerrarModalProtecciones")?.addEventListener("click", cerrarModal);
            document.getElementById("btnCerrarModalFooter")?.addEventListener("click", cerrarModal);

            modal.addEventListener("click", e => {
                if (e.target === modal) return cerrarModal();

                const packCard = e.target.closest(".pack-card");
                if (packCard) {
                    e.preventDefault();
                    const radio = packCard.querySelector(".input-paquete");
                    if (radio) seleccionarPaquete(radio);
                    return;
                }

                const individualCard = e.target.closest(".individual-card");
                if (individualCard) {
                    e.preventDefault();
                    seleccionarIndividual(individualCard);
                }
            });

            document.getElementById("tabPaquetes")?.addEventListener("click", e => {
                e.preventDefault();

                document.getElementById("modal-vista-paquetes").style.display = "block";
                document.getElementById("modal-vista-individuales").style.display = "none";

                document.getElementById("tabPaquetes")?.classList.add("active");
                document.getElementById("tabIndividuales")?.classList.remove("active");
            });

            document.getElementById("tabIndividuales")?.addEventListener("click", e => {
                e.preventDefault();

                document.getElementById("modal-vista-paquetes").style.display = "none";
                document.getElementById("modal-vista-individuales").style.display = "block";

                document.getElementById("tabIndividuales")?.classList.add("active");
                document.getElementById("tabPaquetes")?.classList.remove("active");

                // Solo aplica defaults si NO hay nada seleccionado
                if (!paqueteSeleccionado && individualesSeleccionados.size === 0) {
                    aplicarDefaultsIndividuales();
                }
            });

            btnAplicar?.addEventListener("click", async () => {   // ← async
                btnAplicar.disabled = true;
                btnAplicar.textContent = "Guardando...";

                try {
                    await guardarProteccionesEnBackend();

                    sincronizarModalAPaso();
                    pintarProteccionesEnCarrito();
                    cerrarModal();

                    if (window.alertify) alertify.success("Protecciones aplicadas");
                } catch (err) {
                    console.error(err);
                } finally {
                    btnAplicar.disabled = false;
                    btnAplicar.textContent = "Aplicar";
                }
            });
        }

        function init() {
            let intentos = 0;

            const timer = setInterval(async () => {
                intentos++;

                if (getElements()) {
                    clearInterval(timer);
                    setupEvents();

                    modal.style.display = "none";
                    modal.classList.remove("active");

                    const hidratado = await hidratarDesdeBackend();

                    if (!hidratado) {
                        aplicarDefaultsIndividuales();
                        sincronizarModalAPaso();
                    }

                    window.abrirModalProtecciones = abrirModal;
                    window.cerrarModalProtecciones = cerrarModal;
                    window.aplicarDefaultsIndividuales = aplicarDefaultsIndividuales;
                    window.hidratarProtecciones = hidratarDesdeBackend;

                    console.log("Modal de protecciones listo");
                    return;
                }

                if (intentos >= 20) {
                    clearInterval(timer);
                    console.warn("No se encontró el modal de protecciones");
                }
            }, 200);
        }

        init();
    })();

    // ================================ SISTEMA DE GARANTÍAS MEJORADO ===============================

    // Tabla de garantías por categoría y tipo de protección
    const GARANTIAS_POR_CATEGORIA = {
        'C': { // Compacto Chevrolet Aveo o similar
            'LDW': 5000,
            'PDW': 8000,
            'CDW 10%': 15000,
            'CDW 20%': 25000,
            'CDW declinado': 330000
        },
        'D': { // Medianos Nissan Virtus o similar
            'LDW': 5000,
            'PDW': 8000,
            'CDW 10%': 18000,
            'CDW 20%': 25000,
            'CDW declinado': 380000
        },
        'E': { // Grandes Volkswagen Jetta o similar
            'LDW': 5000,
            'PDW': 8000,
            'CDW 10%': 20000,
            'CDW 20%': 30000,
            'CDW declinado': 500000
        },
        'F': { // Full size Camry o similar
            'LDW': 5000,
            'PDW': 15000,
            'CDW 10%': 30000,
            'CDW 20%': 40000,
            'CDW declinado': 650000
        },
        'IC': { // Suv compacta Jeep Renegade o similar
            'LDW': 5000,
            'PDW': 8000,
            'CDW 10%': 20000,
            'CDW 20%': 30000,
            'CDW declinado': 500000
        },
        'I': { // Suv Mediana Volkswagen Taos o similar
            'LDW': 5000,
            'PDW': 10000,
            'CDW 10%': 30000,
            'CDW 20%': 40000,
            'CDW declinado': 600000
        },
        'IB': { // Suv Familiar compacta Toyota avanza o similar
            'LDW': 5000,
            'PDW': 8000,
            'CDW 10%': 18000,
            'CDW 20%': 25000,
            'CDW declinado': 400000
        },
        'M': { // Minivan Honda Odyssey o similar
            'LDW': 10000,
            'PDW': 20000,
            'CDW 10%': 30000,
            'CDW 20%': 40000,
            'CDW declinado': 800000
        },
        'L': { // Pasajeros de 12 usuarios Toyota Hiace o similar
            'LDW': 10000,
            'PDW': 20000,
            'CDW 10%': 30000,
            'CDW 20%': 40000,
            'CDW declinado': 800000
        },
        'H': { // Pick up Doble Cabina Nissan Frontier o similar
            'LDW': 10000,
            'PDW': 20000,
            'CDW 10%': 30000,
            'CDW 20%': 40000,
            'CDW declinado': 600000
        },
        'HI': { // Pick up 4x4 Doble Cabina Toyota Tacoma o similar
            'LDW': 10000,
            'PDW': 20000,
            'CDW 10%': 30000,
            'CDW 20%': 40000,
            'CDW declinado': 900000
        }
    };

    // Mapeo de nombres de seguros a tipos de protección
    // Mapeo de nombres de seguros a tipos de protección
    const MAPEO_TIPO_PROTECCION = {
        'CDW 10%': 'CDW 10%',
        'CDW 20%': 'CDW 20%',
        'CDW declinado': 'CDW declinado',
        'LDW': 'LDW',
        'PDW': 'PDW',
        'PROTECCIÓN TOTAL 10%': 'CDW 10%',
        'PROTECCIÓN TOTAL 20%': 'CDW 20%',
        'PROTECCIÓN BÁSICA': 'CDW declinado',
        'ROBO TOTAL': 'CDW declinado',
        'DAÑOS A TERCEROS': 'PDW',
        // NUEVOS NOMBRES
        'CDW PACK 1': 'CDW 10%',
        'CDW PACK 2': 'CDW 20%',
        'CDW PACK 3': 'CDW 20%',
        'DECLINE PROTECTIONS': 'CDW declinado',
        'DECLINE CDW': 'CDW declinado',
        'DECLINE': 'CDW declinado',
    };

    // Variable global para almacenar la categoría actual
    window.categoriaActual = null;

    /**
     * Obtiene la categoría del vehículo actual de manera confiable
     */
    function obtenerCategoriaActual() {
        // 1. Intentar desde el elemento contratoInicial
        const contratoInicial = document.getElementById('contratoInicial');
        if (contratoInicial) {
            const codigoCategoria = contratoInicial.dataset.codigoCategoria || contratoInicial.dataset.idCategoria;
            if (codigoCategoria && GARANTIAS_POR_CATEGORIA[codigoCategoria]) {
                window.categoriaActual = codigoCategoria;
                return codigoCategoria;
            }
        }

        // 2. Buscar en las tarjetas de categorías activas (modal de categorías)
        const cardActiva = document.querySelector('.card-categoria.activa');
        if (cardActiva) {
            const codigo = cardActiva.dataset.codigo;
            if (codigo && GARANTIAS_POR_CATEGORIA[codigo]) {
                window.categoriaActual = codigo;
                return codigo;
            }
        }

        // 3. Buscar en cualquier card-categoria (buscar la que tenga badge "Actual")
        const cards = document.querySelectorAll('.card-categoria');
        for (let card of cards) {
            if (card.classList.contains('activa') || card.querySelector('.cat-badge-actual')) {
                const codigo = card.dataset.codigo;
                if (codigo && GARANTIAS_POR_CATEGORIA[codigo]) {
                    window.categoriaActual = codigo;
                    return codigo;
                }
            }
        }

        // 4. Usar la categoría guardada en window
        if (window.categoriaActual && GARANTIAS_POR_CATEGORIA[window.categoriaActual]) {
            return window.categoriaActual;
        }

        // 5. Último recurso: intentar obtener del texto visible "Categoría X"
        const categoriaText = document.querySelector('.categoria-actual-texto');
        if (categoriaText) {
            const texto = categoriaText.textContent.trim();
            for (let key of Object.keys(GARANTIAS_POR_CATEGORIA)) {
                if (texto.includes(key)) {
                    window.categoriaActual = key;
                    return key;
                }
            }
        }

        console.warn('⚠️ No se pudo determinar la categoría actual');
        return null;
    }

    /**
     * Obtiene el tipo de protección seleccionado actualmente
     */
    function obtenerTipoProteccionSeleccionado() {
        // 1. Verificar paquetes (radio buttons)
        const paqueteSeleccionado = document.querySelector('.input-paquete:checked');
        if (paqueteSeleccionado) {
            const card = paqueteSeleccionado.closest('.pack-card');
            if (card) {
                const nombre = card.querySelector('h4')?.textContent?.trim() || '';
                // Buscar coincidencia exacta o parcial
                for (let [key, value] of Object.entries(MAPEO_TIPO_PROTECCION)) {
                    if (nombre.includes(key) || key.includes(nombre)) {
                        return value;
                    }
                }
            }
        }

        // 2. Verificar individuales (checkboxes)
        const individualesActivos = document.querySelectorAll('.switch-individual:checked');
        if (individualesActivos.length > 0) {
            for (let cb of individualesActivos) {
                const card = cb.closest('.individual-card');
                if (card) {
                    const nombre = card.querySelector('.individual-nombre')?.textContent?.trim() || '';
                    for (let [key, value] of Object.entries(MAPEO_TIPO_PROTECCION)) {
                        if (nombre.includes(key) || key.includes(nombre)) {
                            return value;
                        }
                    }
                }
            }
        }

        // 3. Si hay selección en el estado del modal
        if (typeof paqueteSeleccionado !== 'undefined' && window.paqueteSeleccionado) {
            const nombre = window.paqueteSeleccionado.nombre || '';
            for (let [key, value] of Object.entries(MAPEO_TIPO_PROTECCION)) {
                if (nombre.includes(key) || key.includes(nombre)) {
                    return value;
                }
            }
        }

        // 4. Default: CDW declinado
        return 'CDW declinado';
    }

    /**
     * Actualiza el valor de garantía para un seguro específico
     */
    function actualizarGarantia(seguroId, categoria, tipoProteccion) {
        const elementoGarantia = document.getElementById(`garantia-${seguroId}`);
        if (!elementoGarantia) return;

        // Si no hay categoría, mostrar 0
        if (!categoria || !GARANTIAS_POR_CATEGORIA[categoria]) {
            elementoGarantia.textContent = '$0 MXN';
            return;
        }

        // Obtener el valor de garantía según la categoría y tipo
        const garantias = GARANTIAS_POR_CATEGORIA[categoria];
        const valor = garantias[tipoProteccion];

        if (valor !== undefined) {
            const valorFormateado = new Intl.NumberFormat('es-MX').format(valor);
            elementoGarantia.textContent = `$${valorFormateado} MXN`;
            elementoGarantia.style.color = '#16a34a';
            elementoGarantia.style.fontWeight = 'bold';
        } else {
            elementoGarantia.textContent = '$0 MXN';
        }
    }

    /**
     * Actualiza todas las garantías en el modal
     * Detecta el tipo de protección por el nombre del seguro
     */
    function actualizarTodasLasGarantias() {
        const categoria = obtenerCategoriaActual();

        console.log('🔍 Categoría actual:', categoria);
        console.log('🔍 Garantías disponibles:', GARANTIAS_POR_CATEGORIA[categoria]);

        // Recorrer CADA paquete de seguro individualmente
        document.querySelectorAll('.pack-card').forEach(card => {
            // 1. Primero intentar con data-tipo
            let tipoProteccion = card.dataset.tipo;

            // 2. Si no tiene data-tipo, detectar por el nombre
            if (!tipoProteccion) {
                const nombre = card.querySelector('h4')?.textContent?.trim() || '';
                const nombreUpper = nombre.toUpperCase();

                console.log(`🔍 Detectando tipo para: "${nombre}"`);

                // Mapeo completo de tipos
                if (nombreUpper.includes('LDW')) {
                    tipoProteccion = 'LDW';
                } else if (nombreUpper.includes('PDW')) {
                    tipoProteccion = 'PDW';
                } else if (nombreUpper.includes('CDW 10%') || nombreUpper.includes('10%') || nombreUpper.includes('CDW PACK 1')) {
                    tipoProteccion = 'CDW 10%';
                } else if (nombreUpper.includes('CDW 20%') || nombreUpper.includes('20%') || nombreUpper.includes('CDW PACK 2') || nombreUpper.includes('CDW PACK 3')) {
                    tipoProteccion = 'CDW 20%';
                } else if (nombreUpper.includes('DECLINE') || nombreUpper.includes('BÁSICA') || nombreUpper.includes('BASICA') || nombreUpper.includes('PROTECCIONES')) {
                    tipoProteccion = 'CDW declinado';
                } else {
                    tipoProteccion = 'CDW declinado';
                }

                console.log(`🔍 Tipo detectado para "${nombre}": ${tipoProteccion}`);
            }

            const seguroId = card.dataset.id;

            // Buscar el elemento de garantía de ESTE paquete
            const elementoGarantia = document.getElementById(`garantia-${seguroId}`);
            if (!elementoGarantia) {
                console.warn(`⚠️ No se encontró elemento garantia-${seguroId}`);
                return;
            }

            // Si no hay categoría, mostrar 0
            if (!categoria || !GARANTIAS_POR_CATEGORIA[categoria]) {
                elementoGarantia.textContent = '$0 MXN';
                return;
            }

            // Obtener el valor según la categoría y el tipo ESPECÍFICO de este paquete
            const garantias = GARANTIAS_POR_CATEGORIA[categoria];
            const valor = garantias[tipoProteccion];

            if (valor !== undefined) {
                const valorFormateado = new Intl.NumberFormat('es-MX').format(valor);
                elementoGarantia.textContent = `$${valorFormateado} MXN`;
                elementoGarantia.style.color = '#16a34a';
                elementoGarantia.style.fontWeight = 'bold';

                console.log(`✅ Paquete ${seguroId} (${tipoProteccion}): $${valorFormateado} MXN`);
            } else {
                elementoGarantia.textContent = '$0 MXN';
                console.warn(`⚠️ No se encontró garantía para ${tipoProteccion} en categoría ${categoria}`);
            }
        });
    }

    // ================================ ESCUCHAR CAMBIOS DE CATEGORÍA ===============================

    /**
     * Esta función se ejecuta cuando la categoría cambia desde cualquier lugar
     * (Paso 1, modal de categorías, etc.)
     */
    function onCategoriaCambiada(nuevaCategoria) {
        console.log('🔄 Categoría cambiada a:', nuevaCategoria);

        // Actualizar la variable global
        window.categoriaActual = nuevaCategoria;

        // Si el modal de protecciones está abierto, actualizar las garantías
        const modal = document.getElementById('modalProtecciones');
        if (modal && modal.classList.contains('active')) {
            console.log('📢 Modal abierto, actualizando garantías...');
            setTimeout(actualizarTodasLasGarantias, 300);
        } else {
            console.log('📢 Modal cerrado, las garantías se actualizarán al abrirlo');
        }
    }

    // Escuchar el evento personalizado 'categoriaCambiada'
    document.addEventListener('categoriaCambiada', function (e) {
        if (e.detail && e.detail.categoria) {
            onCategoriaCambiada(e.detail.categoria);
        }
    });

    // También observar cambios en el elemento contratoInicial
    const contratoInicial = document.getElementById('contratoInicial');
    if (contratoInicial) {
        const observer = new MutationObserver(function (mutations) {
            for (let mutation of mutations) {
                if (mutation.type === 'attributes' &&
                    (mutation.attributeName === 'data-id-categoria' ||
                        mutation.attributeName === 'data-codigo-categoria')) {

                    const nuevaCategoria = contratoInicial.dataset.codigoCategoria || contratoInicial.dataset.idCategoria;
                    console.log('🔄 Cambio detectado en contratoInicial:', nuevaCategoria);

                    if (nuevaCategoria) {
                        onCategoriaCambiada(nuevaCategoria);
                    }
                }
            }
        });
        observer.observe(contratoInicial, { attributes: true });
    }

    // ================================ INTEGRACIÓN CON EL MODAL ===============================

    // Función para inicializar el sistema de garantías
    function inicializarSistemaGarantias() {
        console.log('🟢 Inicializando sistema de garantías...');

        // 1. Actualizar al abrir el modal de protecciones
        const btnAbrirModal = document.getElementById('btnAbrirModalProtecciones');
        if (btnAbrirModal) {
            btnAbrirModal.addEventListener('click', function () {
                // Obtener la categoría actual antes de actualizar
                const categoria = obtenerCategoriaActual();
                console.log('📢 Abriendo modal, categoría actual:', categoria);
                setTimeout(actualizarTodasLasGarantias, 300);
            });
        }

        // 2. Escuchar el evento personalizado de cambio de categoría
        document.addEventListener('categoriaCambiada', function (e) {
            console.log('📢 Evento categoriaCambiada recibido en inicializador:', e.detail);
            // Si el modal está abierto, actualizar inmediatamente
            const modal = document.getElementById('modalProtecciones');
            if (modal && modal.classList.contains('active')) {
                setTimeout(actualizarTodasLasGarantias, 200);
            }
        });

        // 3. Actualizar al seleccionar protección
        document.addEventListener('change', function (e) {
            if (e.target.classList.contains('input-paquete') ||
                e.target.classList.contains('switch-individual')) {
                setTimeout(actualizarTodasLasGarantias, 200);
            }
        });

        // 4. Interceptar el cambio de categoría desde el código existente
        const observer = new MutationObserver(function (mutations) {
            for (let mutation of mutations) {
                if (mutation.type === 'attributes' && mutation.attributeName === 'data-id-categoria') {
                    setTimeout(actualizarTodasLasGarantias, 200);
                    break;
                }
            }
        });

        const contratoInicial = document.getElementById('contratoInicial');
        if (contratoInicial) {
            observer.observe(contratoInicial, { attributes: true });
        }

        // 5. Actualizar al aplicar protecciones
        const btnAplicar = document.getElementById('btnAplicarProtecciones');
        if (btnAplicar) {
            btnAplicar.addEventListener('click', function () {
                setTimeout(actualizarTodasLasGarantias, 200);
            });
        }

        // 6. Actualizar al cambiar de vista (paquetes/individuales)
        const tabPaquetes = document.getElementById('tabPaquetes');
        const tabIndividuales = document.getElementById('tabIndividuales');

        if (tabPaquetes) {
            tabPaquetes.addEventListener('click', function () {
                setTimeout(actualizarTodasLasGarantias, 300);
            });
        }
        if (tabIndividuales) {
            tabIndividuales.addEventListener('click', function () {
                setTimeout(actualizarTodasLasGarantias, 300);
            });
        }

        // 7. Ejecutar inicialmente
        setTimeout(actualizarTodasLasGarantias, 600);

        console.log('✅ Sistema de garantías inicializado');
    }

    // ================================ SOBRESCRIBIR FUNCIÓN DE CAMBIO DE CATEGORÍA ===============================

    // Modificar la función de cambio de categoría para actualizar garantías
    (function patchCambioCategoria() {
        const originalClick = document.querySelector('#contenedorCategoriasJS')?.addEventListener;

        // Agregar listener adicional para capturar cambios de categoría
        document.addEventListener('categoriaCambiada', function (e) {
            console.log('📢 Evento categoriaCambiada detectado:', e.detail);
            if (e.detail && e.detail.categoria) {
                window.categoriaActual = e.detail.categoria;
                setTimeout(actualizarTodasLasGarantias, 200);
            }
        });

        // Observar cambios en el dataset de contratoInicial
        const contratoInicial = document.getElementById('contratoInicial');
        if (contratoInicial) {
            const observer = new MutationObserver(function (mutations) {
                for (let mutation of mutations) {
                    if (mutation.type === 'attributes' &&
                        (mutation.attributeName === 'data-id-categoria' ||
                            mutation.attributeName === 'data-codigo-categoria')) {
                        console.log('🔄 Categoría cambiada en contratoInicial');
                        setTimeout(actualizarTodasLasGarantias, 200);
                    }
                }
            });
            observer.observe(contratoInicial, { attributes: true });
        }
    })();

    // IIFE
    (function () {
        let intentos = 0;
        const checkModal = setInterval(function () {
            intentos++;
            if (document.getElementById('modalProtecciones')) {
                clearInterval(checkModal);
                inicializarSistemaGarantias();
                console.log('Sistema de garantías completamente inicializado');
                return;
            }
            if (intentos >= 20) {
                clearInterval(checkModal);
                console.warn('No se encontró el modal de protecciones, iniciando igual...');
                inicializarSistemaGarantias();
            }
        }, 200);
    })();

    // Exponer funciones globalmente
    window.actualizarGarantia = actualizarGarantia;
    window.actualizarTodasLasGarantias = actualizarTodasLasGarantias;
    window.obtenerCategoriaActual = obtenerCategoriaActual;
    window.obtenerTipoProteccionSeleccionado = obtenerTipoProteccionSeleccionado;

    // ================================ MODAL DE PROTECCIONES - TOGGLE DEL CARRITO ===============================

    document.addEventListener('DOMContentLoaded', function () {
        const modal = document.getElementById('modalProtecciones');
        const btnAbrir = document.getElementById('btnAbrirModalProtecciones');

        // === ABRIR MODAL ===
        if (btnAbrir && modal) {
            btnAbrir.addEventListener('click', function (e) {
                e.preventDefault();
                modal.classList.add('active');
                modal.style.display = 'flex';
                document.body.style.overflow = 'hidden';

                // Copiar datos del resumen principal al modal
                setTimeout(copiarResumenAlModal, 200);
            });
        }

        // === CERRAR MODAL ===
        const btnCerrar = document.getElementById('btnCerrarModalProtecciones');
        const btnCerrarFooter = document.getElementById('btnCerrarModalFooter');

        function cerrarModal() {
            modal.classList.remove('active');
            modal.style.display = 'none';
            document.body.style.overflow = '';
        }

        if (btnCerrar) btnCerrar.addEventListener('click', cerrarModal);
        if (btnCerrarFooter) btnCerrarFooter.addEventListener('click', cerrarModal);

        // Cerrar al hacer clic fuera
        modal.addEventListener('click', function (e) {
            if (e.target === modal) cerrarModal();
        });

        // === TOGGLE DEL CARRITO DENTRO DEL MODAL ===
        const btnToggleModal = document.getElementById('btnToggleDetalleModal');
        const resumenContainerModal = document.getElementById('resumenDetalleContainerModal');
        const iconoFlechaModal = document.getElementById('iconoFlechaResumenModal');

        if (btnToggleModal && resumenContainerModal) {
            btnToggleModal.addEventListener('click', function (e) {
                e.stopPropagation();

                const estaAbierto = resumenContainerModal.classList.contains('abierto');

                if (estaAbierto) {
                    resumenContainerModal.classList.remove('abierto');
                    resumenContainerModal.style.display = 'none';
                    if (iconoFlechaModal) iconoFlechaModal.style.transform = 'rotate(0deg)';
                } else {
                    // Copiar datos antes de abrir
                    copiarResumenAlModal();
                    resumenContainerModal.classList.add('abierto');
                    resumenContainerModal.style.display = 'block';
                    if (iconoFlechaModal) iconoFlechaModal.style.transform = 'rotate(180deg)';
                }
            });
        }

        // === VER DETALLE DENTRO DEL MODAL ===
        const btnVerDetalleModal = document.getElementById('btnVerDetalleModal');
        const btnOcultarDetalleModal = document.getElementById('btnOcultarDetalleModal');
        const resumenDetalleModal = document.getElementById('resumenDetalleModal');
        const resumenCompactoModal = document.getElementById('resumenCompactoModal');

        if (btnVerDetalleModal && resumenDetalleModal) {
            btnVerDetalleModal.addEventListener('click', function () {
                copiarResumenAlModal();
                resumenDetalleModal.style.display = 'block';
                if (resumenCompactoModal) resumenCompactoModal.style.display = 'none';
            });
        }

        if (btnOcultarDetalleModal && resumenDetalleModal) {
            btnOcultarDetalleModal.addEventListener('click', function () {
                resumenDetalleModal.style.display = 'none';
                if (resumenCompactoModal) resumenCompactoModal.style.display = 'block';
            });
        }
    });

    // === FUNCIÓN PARA COPIAR DATOS DEL RESUMEN PRINCIPAL AL MODAL ===
    function copiarResumenAlModal() {
        console.log('🔄 Copiando resumen al modal...');

        // Mapeo de elementos: [idPrincipal, idModal]
        const elementos = [
            ['btnTotalTextContrato', 'btnTotalTextContratoModal'],
            ['btnTotalUsdContrato', 'btnTotalUsdContratoModal'],
            ['resumenTotalCompacto', 'resumenTotalCompactoModal'],
            ['resumenVehCompacto', 'resumenVehCompactoModal'],
            ['resumenCategoriaCompacto', 'resumenCategoriaCompactoModal'],
            ['resumenDiasCompacto', 'resumenDiasCompactoModal'],
            ['resumenFechasCompacto', 'resumenFechasCompactoModal'],
            ['detCodigo', 'detCodigoModal'],
            ['detCliente', 'detClienteModal'],
            ['detTelefono', 'detTelefonoModal'],
            ['detEmail', 'detEmailModal'],
            ['detModelo', 'detModeloModal'],
            ['detMarca', 'detMarcaModal'],
            ['detCategoria', 'detCategoriaModal'],
            ['detTransmision', 'detTransmisionModal'],
            ['detPasajeros', 'detPasajerosModal'],
            ['detPuertas', 'detPuertasModal'],
            ['detKm', 'detKmModal'],
            ['detFechaSalida', 'detFechaSalidaModal'],
            ['detHoraSalida', 'detHoraSalidaModal'],
            ['detFechaEntrega', 'detFechaEntregaModal'],
            ['detHoraEntrega', 'detHoraEntregaModal'],
            ['detDiasRenta', 'detDiasRentaModal'],
            ['r_base_precio', 'r_base_precioModal'],
            ['r_cortesia', 'r_cortesiaModal'],
            ['r_subtotal', 'r_subtotalModal'],
            ['r_iva', 'r_ivaModal'],
            ['r_total_final', 'r_total_finalModal'],
            ['detPagos', 'detPagosModal'],
            ['detSaldo', 'detSaldoModal'],
        ];

        elementos.forEach(([origen, destino]) => {
            const elOrigen = document.getElementById(origen);
            const elDestino = document.getElementById(destino);

            if (elOrigen && elDestino) {
                elDestino.textContent = elOrigen.textContent;
            }
        });

        // Copiar imagen del vehículo
        const imgOrigen = document.getElementById('resumenImgVeh');
        const imgDestino = document.getElementById('resumenImgVehModal');
        if (imgOrigen && imgDestino) {
            imgDestino.src = imgOrigen.src;
        }

        // Copiar listas (seguros y servicios)
        const listas = [
            ['r_seguros_lista', 'r_seguros_listaModal'],
            ['r_servicios_lista', 'r_servicios_listaModal'],
        ];

        listas.forEach(([origen, destino]) => {
            const elOrigen = document.getElementById(origen);
            const elDestino = document.getElementById(destino);

            if (elOrigen && elDestino) {
                elDestino.innerHTML = elOrigen.innerHTML;
            }
        });

        // Copiar totales de seguros
        const totalSeguros = document.getElementById('total_seguros');
        const totalSegurosModal = document.getElementById('total_seguros_modal');
        if (totalSeguros && totalSegurosModal) {
            totalSegurosModal.textContent = totalSeguros.textContent;
        }

        console.log('✅ Resumen copiado al modal');
    }

    const modal = document.getElementById('modalProtecciones');
    if (modal && modal.classList.contains('active')) {
        setTimeout(copiarResumenAlModal, 300);
    }

    // =====================================================
    // CARRITO DEL MODAL DE PROTECCIONES
    // =====================================================

    function copiarResumenNavbarAlModal() {
        const copiarTexto = (origenId, destinoId) => {
            const origen = document.getElementById(origenId);
            const destino = document.getElementById(destinoId);

            if (origen && destino) {
                destino.textContent = origen.textContent.trim();
            }
        };

        const copiarHTML = (origenId, destinoId) => {
            const origen = document.getElementById(origenId);
            const destino = document.getElementById(destinoId);

            if (origen && destino) {
                destino.innerHTML = origen.innerHTML;
            }
        };

        copiarTexto('btnTotalTextContrato', 'btnTotalTextContratoModal');
        copiarTexto('btnTotalUsdContrato', 'btnTotalUsdContratoModal');
        copiarTexto('resumenTotalCompacto', 'resumenTotalCompactoModal');

        copiarTexto('resumenVehCompacto', 'resumenVehCompactoModal');
        copiarTexto('resumenCategoriaCompacto', 'resumenCategoriaCompactoModal');
        copiarTexto('resumenDiasCompacto', 'resumenDiasCompactoModal');
        copiarTexto('resumenFechasCompacto', 'resumenFechasCompactoModal');

        copiarTexto('detCodigo', 'detCodigoModal');
        copiarTexto('detCliente', 'detClienteModal');
        copiarTexto('detTelefono', 'detTelefonoModal');
        copiarTexto('detEmail', 'detEmailModal');

        copiarTexto('detModelo', 'detModeloModal');
        copiarTexto('detMarca', 'detMarcaModal');
        copiarTexto('detCategoria', 'detCategoriaModal');
        copiarTexto('detTransmision', 'detTransmisionModal');
        copiarTexto('detPasajeros', 'detPasajerosModal');
        copiarTexto('detPuertas', 'detPuertasModal');
        copiarTexto('detKm', 'detKmModal');

        copiarTexto('detFechaSalida', 'detFechaSalidaModal');
        copiarTexto('detHoraSalida', 'detHoraSalidaModal');
        copiarTexto('detFechaEntrega', 'detFechaEntregaModal');
        copiarTexto('detHoraEntrega', 'detHoraEntregaModal');
        copiarTexto('detDiasRenta', 'detDiasRentaModal');

        copiarHTML('r_seguros_lista', 'r_seguros_listaModal');
        copiarTexto('r_seguros_total', 'r_seguros_totalModal');

        copiarHTML('r_servicios_lista', 'r_servicios_listaModal');
        copiarTexto('r_servicios_total', 'r_servicios_totalModal');

        copiarTexto('r_base_precio', 'r_base_precioModal');
        copiarTexto('r_cortesia', 'r_cortesiaModal');
        copiarTexto('r_subtotal', 'r_subtotalModal');
        copiarTexto('r_iva', 'r_ivaModal');
        copiarTexto('r_total_final', 'r_total_finalModal');

        copiarTexto('detPagos', 'detPagosModal');
        copiarTexto('detSaldo', 'detSaldoModal');

        const imgOrigen = document.getElementById('resumenImgVeh');
        const imgDestino = document.getElementById('resumenImgVehModal');

        if (imgOrigen && imgDestino) {
            imgDestino.src = imgOrigen.src;
        }

        copiarTexto('resumenProteccionesCompacto', 'resumenProteccionesCompactoModal');
    }

    function cerrarCarritoModalProtecciones() {
        const resumenModal = document.getElementById('resumenDetalleContainerModal');
        const iconoModal = document.getElementById('iconoFlechaResumenModal');

        if (!resumenModal) return;

        resumenModal.classList.remove('abierto');
        resumenModal.style.display = 'none';

        if (iconoModal) {
            iconoModal.style.transform = 'rotate(0deg)';
        }
    }

    function inicializarCarritoModalProtecciones() {
        const modal = document.getElementById('modalProtecciones');

        const btnToggleModal = document.getElementById('btnToggleDetalleModal');
        const resumenModal = document.getElementById('resumenDetalleContainerModal');
        const iconoModal = document.getElementById('iconoFlechaResumenModal');

        const btnVerDetalleModal = document.getElementById('btnVerDetalleModal');
        const btnOcultarDetalleModal = document.getElementById('btnOcultarDetalleModal');

        const resumenCompactoModal = document.getElementById('resumenCompactoModal');
        const resumenDetalleModal = document.getElementById('resumenDetalleModal');

        if (!btnToggleModal || !resumenModal) {
            console.warn('⚠️ No se encontró el carrito del modal de protecciones.');
            return;
        }

        copiarResumenNavbarAlModal();

        btnToggleModal.addEventListener('click', function (e) {
            e.preventDefault();
            e.stopPropagation();

            copiarResumenNavbarAlModal();

            const abierto = resumenModal.classList.contains('abierto');

            if (abierto) {
                cerrarCarritoModalProtecciones();
            } else {
                resumenModal.classList.add('abierto');
                resumenModal.style.display = 'block';

                if (iconoModal) {
                    iconoModal.style.transform = 'rotate(180deg)';
                }
            }
        });

        btnVerDetalleModal?.addEventListener('click', function (e) {
            e.preventDefault();

            copiarResumenNavbarAlModal();

            if (resumenCompactoModal) resumenCompactoModal.style.display = 'none';
            if (resumenDetalleModal) resumenDetalleModal.style.display = 'block';
        });

        btnOcultarDetalleModal?.addEventListener('click', function (e) {
            e.preventDefault();

            if (resumenDetalleModal) resumenDetalleModal.style.display = 'none';
            if (resumenCompactoModal) resumenCompactoModal.style.display = 'block';
        });

        document.addEventListener('click', function (e) {
            if (!resumenModal.classList.contains('abierto')) return;

            const clicDentro =
                btnToggleModal.contains(e.target) ||
                resumenModal.contains(e.target);

            if (!clicDentro) {
                cerrarCarritoModalProtecciones();
            }
        });

        document.addEventListener('change', function () {
            if (modal && modal.classList.contains('active')) {
                setTimeout(copiarResumenNavbarAlModal, 250);
            }
        });

        document.addEventListener('click', function (e) {
            if (!modal || !modal.classList.contains('active')) return;

            const cambioResumen =
                e.target.closest('.card-servicio') ||
                e.target.closest('.cargo-item') ||
                e.target.closest('.proteccion-card') ||
                e.target.closest('.coverage-card') ||
                e.target.closest('.card-paquete') ||
                e.target.closest('.btn-contador') ||
                e.target.closest('.btn-elegir-paquete') ||
                e.target.closest('.btn-proteccion');

            if (cambioResumen) {
                setTimeout(copiarResumenNavbarAlModal, 350);
            }
        });
    }

    inicializarCarritoModalProtecciones();

});

