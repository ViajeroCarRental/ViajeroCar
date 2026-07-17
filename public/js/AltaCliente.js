document.addEventListener("DOMContentLoaded", () => {
    const $ = (s) => document.querySelector(s);
    const $$ = (s) => Array.from(document.querySelectorAll(s));

    // =========================================
    // ESTADO GLOBAL
    // =========================================
    let selectedClientType = null;
    let currentStep = 1;
    let activeProtectionRow = null;
    let activeBaseDailyPrice = 0;
    let activeCategoryName = '';
    let activeSignatureTarget = null;
    let modalDrawing = false;
    let modalHasDrawn = false;

    // =========================================
    // ESTADO DE PROTECCIONES
    // =========================================
    const protectionState = {
        selectedId: null,
        selectedName: null,
        selectedPrice: 0,
        selectedGuarantee: 0,
        activeRowIndex: null,
        baseDailyPrice: 0,
        categoryName: '',
    };

    const clauses = {
        fisica: [],
        moral: [],
        general: [],
        responsivas: {},
    };

    const drivers = [];

    const clientTypeLabels = {
        fisica: "Persona física",
        moral: "Persona moral",
        general: "Público general",
    };

    const docSubtitles = {
        fisica: "Documentación e información para persona física.",
        moral: "Documentación e información para persona moral.",
        general: "Documentación e información para público general.",
    };

    const clauseTitles = {
        fisica: "Cláusulas del convenio Persona Física",
        moral: "Cláusulas del convenio Persona Moral",
        general: "Cláusulas del convenio Público General",
    };

    // =========================================
    // HELPERS
    // =========================================
    const showToast = (message) => {
        const toast = $("#toast");
        if (!toast) return;

        toast.textContent = message;
        toast.classList.add("show");

        setTimeout(() => toast.classList.remove("show"), 1800);
    };

    const parseMoney = (value) => Number(String(value).replace(/[^0-9.]/g, "")) || 0;

    const formatMoney = (value) => {
        return `$${Number(value).toLocaleString("es-MX", {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2,
        })} MXN`;
    };

    const formatMoneySimple = (value) => {
        return `$${Number(value).toLocaleString("es-MX", {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2,
        })}`;
    };

    const getFileName = (input) => input?.files?.[0]?.name || "";

    function escapeHtml(str) {
        return String(str || "")
            .replaceAll("&", "&amp;")
            .replaceAll("<", "&lt;")
            .replaceAll(">", "&gt;")
            .replaceAll('"', "&quot;")
            .replaceAll("'", "&#039;");
    }

    function getActiveClauses() {
        if (!selectedClientType) return [];
        return clauses[selectedClientType] || [];
    }

    // =========================================
    // NAVEGACIÓN DEL WIZARD
    // =========================================
    function goToStep(step) {
        currentStep = step;

        $$(".wizard-panel").forEach((panel) => {
            panel.classList.toggle("active", Number(panel.dataset.step) === step);
        });

        $$(".wizard-step").forEach((item) => {
            const itemStep = Number(item.dataset.stepTarget);
            item.classList.toggle("active", itemStep === step);
            item.classList.toggle("completed", itemStep < step);
        });

        if (step === 4 && selectedClientType) {
            showAgreementForm(selectedClientType);
            renderClauses();
        }

        window.scrollTo({ top: 0, behavior: "smooth" });
    }

    // =========================================
    // CLIENTE - SELECCIÓN Y DOCUMENTACIÓN
    // =========================================
    function showDocForm(type) {
        $$(".doc-form").forEach((form) => form.classList.remove("active"));

        const selectedForm = $(`.doc-form-${type}`);
        if (selectedForm) selectedForm.classList.add("active");

        const subtitle = $("#docSubtitle");
        if (subtitle) {
            subtitle.textContent = docSubtitles[type] || "Completa la información del cliente.";
        }

        // 🔧 Ajustar 'required' según el tipo elegido (evita que campos ocultos bloqueen el envío)
        syncRequiredByType(type);
    }

    function showAgreementForm(type) {
        $$(".agreement-mode").forEach((mode) => {
            mode.classList.remove("active");
        });

        $(`.agreement-mode-${type}`)?.classList.add("active");

        const title = $("#agreementTitle");
        const description = $("#agreementDescription");
        const subtitle = $("#agreementSubtitle");
        const clauseTitle = $("#clausesTitle");

        const texts = {
            fisica: {
                title: "Convenio Persona Física",
                subtitle: "Captura las firmas del cliente y del asesor de Viajero.",
                description: "Este convenio se genera individualmente para la persona física con sus propias cláusulas.",
            },
            moral: {
                title: "Convenio Persona Moral",
                subtitle: "Captura firmas del representante legal, conductor adicional y asesor Viajero.",
                description: "Este convenio se genera individualmente para la empresa con sus propias cláusulas y responsivas por conductor.",
            },
            general: {
                title: "Convenio Público General",
                subtitle: "Captura la firma del usuario y del asesor de Viajero.",
                description: "Este convenio simple se genera individualmente para el usuario con sus propias cláusulas.",
            },
        };

        if (title) title.textContent = texts[type]?.title || "Convenio Member Prefer";
        if (subtitle) subtitle.textContent = texts[type]?.subtitle || "Genera el convenio y captura firmas.";
        if (description) description.textContent = texts[type]?.description || "El convenio se generará según el tipo de cliente.";
        if (clauseTitle) clauseTitle.textContent = clauseTitles[type] || "Cláusulas del convenio";
    }

    /**
     * 🔧 NUEVO: activa 'required' SOLO en el formulario del tipo seleccionado
     * y lo quita de los otros dos (que están ocultos). Sin esto, el navegador
     * bloquea el submit por campos required vacíos en formularios ocultos.
     */
    function syncRequiredByType(type) {
        const tipos = ["fisica", "moral", "general"];

        tipos.forEach((t) => {
            const docForm = $(`.doc-form-${t}`);
            if (!docForm) return;

            const activo = (t === type);

            docForm.querySelectorAll("[data-orig-required]").forEach((el) => {
                // restaurar/aplicar según corresponda
                if (activo) {
                    el.setAttribute("required", "required");
                } else {
                    el.removeAttribute("required");
                }
            });
        });
    }

    /**
     * 🔧 NUEVO: marca con data-orig-required los campos que nacieron como required,
     * para poder activarlos/desactivarlos según el tipo. Se llama una vez al inicio.
     */
    function snapshotRequiredFields() {
        $$(".doc-form [required]").forEach((el) => {
            el.setAttribute("data-orig-required", "1");
        });
        // Empezamos quitando required a todos los doc-form (hasta que se elija tipo)
        $$(".doc-form [data-orig-required]").forEach((el) => el.removeAttribute("required"));
    }

    function selectClientType(card) {
        selectedClientType = card.dataset.clientType;

        // 🔧 Guardar el tipo en el hidden del form
        const hiddenTipo = $("#tipoPersonaInput");
        if (hiddenTipo) hiddenTipo.value = selectedClientType;

        $$(".client-type-card").forEach((item) => item.classList.remove("active"));
        card.classList.add("active");

        const btnGoDocs = $("#btnGoDocs");
        if (btnGoDocs) {
            btnGoDocs.disabled = false;
            btnGoDocs.textContent = `Continuar como ${clientTypeLabels[selectedClientType]}`;
        }

        showDocForm(selectedClientType);
        showAgreementForm(selectedClientType);
        renderClauses();
    }

    // =========================================
    // CLÁUSULAS
    // =========================================
    function renderClauses() {
        const list = $("#clausesList");
        if (!list) return;

        const activeClauses = getActiveClauses();

        if (!selectedClientType) {
            list.innerHTML = `<div class="empty-clauses">Selecciona un tipo de cliente para agregar cláusulas.</div>`;
            return;
        }

        if (!activeClauses.length) {
            list.innerHTML = `<div class="empty-clauses">Aún no hay cláusulas agregadas para este convenio.</div>`;
            return;
        }

        list.innerHTML = activeClauses.map((clause, index) => `
            <div class="clause-item">
                <div>
                    <strong>Cláusula ${index + 1}</strong>
                    <p>${clause}</p>
                </div>

                <button class="btn danger btn-remove-clause" type="button" data-clause-index="${index}">
                    Eliminar
                </button>
            </div>
        `).join("");
    }

    function addClause() {
        const textarea = $("#clauseText");
        if (!textarea) return;

        if (!selectedClientType) {
            showToast("Primero selecciona el tipo de cliente");
            return;
        }

        const text = textarea.value.trim();

        if (!text) {
            showToast("Escribe una cláusula antes de guardarla");
            return;
        }

        clauses[selectedClientType].push(text);

        textarea.value = "";
        $("#clausePanel")?.classList.remove("active");

        renderClauses();
        showToast("Cláusula agregada al convenio correspondiente");
    }

    // =========================================
    // CONDUCTORES
    // =========================================
        function renderDrivers() {
            const list = $("#driversList");
            if (!list) return;

            if (!drivers.length) {
                list.innerHTML = `<div class="empty-clauses">Aún no hay conductores adicionales.</div>`;
                return;
            }

            list.innerHTML = `
                <div class="drivers-table-wrap">
                    <table class="drivers-table">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Nombre</th>
                                <th>Nacimiento</th>
                                <th>Teléfono</th>
                                <th>Correo</th>
                                <th>Identificación</th>
                                <th>Licencia</th>
                                <th>Vigencia</th>
                                <th>Firma</th>
                                <th>Acción</th>
                            </tr>
                        </thead>

                        <tbody>
                            ${drivers.map((driver, index) => `
                                <tr>
                                    <td>${index + 1}</td>
                                    <td>${driver.nombre}</td>
                                    <td>${driver.nacimiento}</td>
                                    <td>${driver.telefono}</td>
                                    <td>${driver.correo}</td>
                                    <td>${driver.identificacion}</td>
                                    <td>${driver.licencia}</td>
                                    <td>${driver.vigenciaLicencia}</td>
                                    <td>${driver.firmaConductor
                                            ? `<img src="${driver.firmaConductor}" alt="Firma de ${escapeHtml(driver.nombre)}" class="driver-signature-thumb" data-signature-view="${driver.firmaConductor}" data-driver-name="${escapeHtml(driver.nombre)}" style="max-width:120px; max-height:50px; border:1px solid #e2e8f0; border-radius:6px; background:#fff; padding:2px; cursor:pointer;">`
                                            : `<span style="color:#94a3b8;">Sin firma</span>`}
                                    </td>
                                    <td>
                                        <button class="btn danger btn-remove-driver" type="button" data-driver-index="${index}">
                                            Eliminar
                                        </button>
                                    </td>
                                </tr>               
                            `).join("")}
                        </tbody>
                    </table>
                </div>
            `;
        }

    function addDriver() {
        const nombre = $("#driverNombre")?.value.trim();
        const nacimiento = $("#driverNacimiento")?.value.trim();
        const telefono = $("#driverTelefono")?.value.trim();
        const correo = $("#driverCorreo")?.value.trim();
        const identificacion = $("#driverIne")?.value.trim();
        const licencia = $("#driverLicencia")?.value.trim();
        const vigenciaLicencia = $("#driverVigenciaLicencia")?.value.trim();

        const identificacionFrontal = $("#driverIdentificacionFrontal");
        const identificacionTrasera = $("#driverIdentificacionTrasera");
        const licenciaFrontal = $("#driverFotoLicencia");
        const licenciaTrasera = $("#driverLicenciaTrasera");
        const firmaConductor = $("#driverFirma");

        if (!nombre || !nacimiento || !telefono || !correo || !identificacion || !licencia || !vigenciaLicencia) {
            showToast("Completa nombre, nacimiento, teléfono, correo, identificación, licencia y vigencia del conductor");
            return;
        }

        if (!identificacionFrontal?.files.length) {
            showToast("Sube la identificación frontal del conductor");
            return;
        }

        if (!identificacionTrasera?.files.length) {
            showToast("Sube la identificación trasera del conductor");
            return;
        }

        if (!licenciaFrontal?.files.length) {
            showToast("Sube la licencia frontal del conductor");
            return;
        }

        if (!licenciaTrasera?.files.length) {
            showToast("Sube la licencia trasera del conductor");
            return;
        }

        if (!firmaConductor?.value) {
            showToast("Guarda la firma del conductor");
            return;
        }

        const driverIndex = drivers.length;

        // 🔧 NUEVO: clonar los archivos del conductor a inputs file ocultos DENTRO del form,
        // para que viajen en el POST. (El nombre usa índice: driver_xxx_frontal[idx])
        moverArchivoConductor(identificacionFrontal, `driver_identificacion_frontal`, driverIndex);
        moverArchivoConductor(identificacionTrasera, `driver_identificacion_trasera`, driverIndex);
        moverArchivoConductor(licenciaFrontal, `driver_licencia_frontal`, driverIndex);
        moverArchivoConductor(licenciaTrasera, `driver_licencia_trasera`, driverIndex);

        drivers.push({
            nombre,
            nacimiento,
            telefono,
            correo,
            identificacion,
            licencia,
            vigenciaLicencia,
            identificacionFrontal: getFileName(identificacionFrontal),
            identificacionTrasera: getFileName(identificacionTrasera),
            licenciaFrontal: getFileName(licenciaFrontal),
            licenciaTrasera: getFileName(licenciaTrasera),
            firmaConductor: firmaConductor.value,
        });

        clauses.responsivas[driverIndex] = [];

        [
            "#driverNombre",
            "#driverNacimiento",
            "#driverTelefono",
            "#driverCorreo",
            "#driverIne",
            "#driverLicencia",
            "#driverVigenciaLicencia",
        ].forEach((selector) => {
            const input = $(selector);
            if (input) input.value = "";
        });

        [identificacionFrontal, identificacionTrasera, licenciaFrontal, licenciaTrasera].forEach((input) => {
            if (input) input.value = "";
        });

        clearSignaturePreview("driverFirma");

        renderDrivers();
        renderResponsivas();
        showToast("Conductor agregado");
    }

    /**
     * 🔧 NUEVO: mueve el archivo seleccionado de un input temporal a un input file
     * oculto dentro del form, conservando el File real para que se envíe en el POST.
     */
    function moverArchivoConductor(inputTemporal, baseName, index) {
        const cont = $("#hiddenDriverFilesContainer");
        if (!cont || !inputTemporal || !inputTemporal.files.length) return;

        const nuevo = document.createElement("input");
        nuevo.type = "file";
        nuevo.name = `${baseName}[${index}]`;
        nuevo.style.display = "none";

        // Transferir el archivo real usando DataTransfer
        const dt = new DataTransfer();
        dt.items.add(inputTemporal.files[0]);
        nuevo.files = dt.files;

        nuevo.setAttribute("data-driver-file-index", index);
        cont.appendChild(nuevo);
    }

    function renderResponsivas() {
        const list = $("#responsivasList");
        if (!list) return;

        if (!drivers.length) {
            list.innerHTML = `<div class="empty-clauses">Aún no hay responsivas generadas.</div>`;
            return;
        }

        list.innerHTML = drivers.map((driver, index) => {
            const driverClauses = clauses.responsivas[index] || [];

            return `
                <div class="responsiva-item" data-driver-index="${index}">
                    <div>
                        <strong>Responsiva ${index + 1}</strong>
                        <p><b>Conductor:</b> ${driver.nombre}</p>
                        <p><b>Identificación:</b> ${driver.identificacion}</p>
                        <p><b>Licencia:</b> ${driver.licencia}</p>
                        <p><b>Firma conductor:</b> ${driver.firmaConductor ? "Guardada" : "Sin firma"}</p>
                        <p><b>Cláusulas:</b> ${driverClauses.length}</p>
                    </div>

                    <div class="responsiva-actions">
                        <button class="btn ghost btn-add-responsiva-clause" type="button" data-driver-index="${index}">
                            Agregar cláusula
                        </button>

                        <button class="btn ghost btn-preview-responsiva" type="button" data-driver-index="${index}">
                            Visualizar responsiva
                        </button>
                    </div>
                </div>
            `;
        }).join("");
    }

    function addResponsivaClause(driverIndex) {
        const driver = drivers[driverIndex];

        if (!driver) {
            showToast("No se encontró el conductor");
            return;
        }

        const text = prompt(`Escribe la cláusula para la responsiva de ${driver.nombre}`);

        if (!text || !text.trim()) {
            showToast("No se agregó ninguna cláusula");
            return;
        }

        if (!clauses.responsivas[driverIndex]) {
            clauses.responsivas[driverIndex] = [];
        }

        clauses.responsivas[driverIndex].push(text.trim());
        renderResponsivas();
        showToast("Cláusula agregada a la responsiva");
    }

    function generateResponsivas() {
        if (!drivers.length) {
            showToast("Agrega al menos un conductor para generar responsivas");
            return;
        }

        renderResponsivas();
        showToast("Responsivas generadas individualmente");
    }

    // =========================================
    // PROTECCIONES - CARGA Y SELECCIÓN
    // =========================================

    async function loadProtectionPacks() {
        const track = document.getElementById('protectionPacksTrack');
        if (!track) return;

        track.innerHTML = `<div class="loading" style="padding:12px;font-weight:900;color:rgba(255,255,255,.9);">Cargando paquetes...</div>`;

        try {
            const res = await fetch("/admin/reservaciones/seguros", {
                headers: { "X-Requested-With": "XMLHttpRequest", "Accept": "application/json" }
            });

            const data = await res.json().catch(() => []);
            const arrRaw = Array.isArray(data) ? data : (data?.data || []);

            console.log("📦 Datos de protecciones recibidos:", arrRaw);
            console.log("🔍 Campos disponibles en el primer registro:", Object.keys(arrRaw[0] || {}));

            const arr = arrRaw.map((raw) => {
                const id = raw.id_paquete ?? raw.id ?? raw.idPaquete;
                const nombre = raw.nombre ?? "Protección";
                const desc = raw.descripcion ?? "";
                const precio = Number(raw.precio_por_dia ?? raw.precio_dia ?? raw.precio ?? 0);

                // =========================================
                // 🔍 BUSCAR GARANTÍA EN CUALQUIER CAMPO
                // =========================================
                let garantia = 0;

                // Lista de posibles nombres de campo para la garantía
                const camposGarantia = [
                    'garantia', 'monto_garantia', 'garantia_monto',
                    'garantia_seguro', 'garantia_paquete', 'deposito_garantia',
                    'fianza', 'monto_fianza', 'deposito', 'garantia_deposito'
                ];

                for (const campo of camposGarantia) {
                    if (raw[campo] !== undefined && raw[campo] !== null) {
                        const valor = Number(raw[campo]);
                        if (!isNaN(valor) && valor > 0) {
                            garantia = valor;
                            console.log(`✅ Garantía encontrada en campo "${campo}": ${garantia}`);
                            break;
                        }
                    }
                }

                // SI NO SE ENCONTRÓ GARANTÍA, USA UN VALOR DE PRUEBA
                if (garantia === 0) {
                    // Asignar garantía según el nombre de la protección (ejemplo)
                    const nombreUpper = nombre.toUpperCase();
                    if (nombreUpper.includes('LDW')) garantia = 5000;
                    else if (nombreUpper.includes('PDW')) garantia = 8000;
                    else if (nombreUpper.includes('CDW')) garantia = 15000;
                    else if (nombreUpper.includes('DECLINE')) garantia = 330000;
                    else garantia = 10000;
                    console.log(`⚠️ Garantía asignada por defecto para "${nombre}": ${garantia}`);
                }

                const charge = raw.tipo_cobro ?? raw.charge ?? "por_dia";

                console.log(`🔍 Protección: ${nombre}, Garantía: ${garantia}`);

                return { id, nombre, desc, precio, garantia, charge };
            });

            arr.sort((a, b) => Number(b.precio || 0) - Number(a.precio || 0));

            if (!arr.length) {
                track.innerHTML = `<div class="loading" style="padding:12px;font-weight:900;color:rgba(255,255,255,.9);">No hay protecciones disponibles.</div>`;
                return;
            }

            track.innerHTML = "";

            arr.forEach((p) => {
                const isSelected = protectionState.selectedId === p.id;

                // Procesar descripción - convertir en lista con viñetas
                let descItems = [];
                if (p.desc) {
                    descItems = p.desc.split(/[-–—·•\n]+/).filter(item => item.trim().length > 0);
                    descItems = descItems.map(item => item.trim().replace(/^\s*[-–—·•]\s*/, '').trim());
                }

                // ==========================================================================
                // AGREGAR GARANTÍA COMO UN PUNTO CON CLASE EXCLUSIVA PARA EL CONTROL DE COLOR
                // ==========================================================================
                if (p.garantia > 0) {
                    descItems.push(`<span class="garantia-text">GARANTÍA: ${formatMoney(p.garantia)}</span>`);
                }

                // Aplicar negritas a palabras clave en la descripción
                let descHtml = descItems.length > 0
                    ? descItems.map(item => {
                        // Si el renglón es el de la garantía, protegemos sus etiquetas HTML para que no se destruyan
                        let text = item.includes('class="garantia-text"') ? item : escapeHtml(item);

                        // Resaltar palabras clave
                        text = text.replace(/(\d+%|\d{1,3}(?:,\d{3})*\s*MXN|GARANTÍA|DEDUCIBLE|NO CUBRE|INCLUYE|RESPONSABILIDAD CIVIL|GASTOS MÉDICOS|ASISTENCIA|PERDIDA TOTAL|ROBO|EL CLIENTE ES RESPONSABLE|CUBIERTA|PRECIO PROTECCIÓN)/gi, '<strong>$1</strong>');
                        return `<li>${text}</li>`;
                    }).join('')
                    : '<li>Sin descripción disponible</li>';

                const totalConProteccion = activeBaseDailyPrice + p.precio;

                const card = document.createElement("article");
                card.className = `protection-card ${isSelected ? 'active' : ''}`;
                card.dataset.protectionId = p.id;
                card.dataset.protectionName = p.nombre;
                card.dataset.protectionPrice = p.precio;
                card.dataset.protectionGuarantee = p.garantia;
                card.dataset.protectionCharge = p.charge;

                card.innerHTML = `
                    <div class="protection-top-line"></div>

                    <div class="protection-card-head">
                        <h4>${escapeHtml(p.nombre)}</h4>
                    </div>

                    <ul>
                        ${descHtml}
                    </ul>

                    <div class="protection-price-box">
                        <span class="price-amount">${formatMoney(p.precio)}</span>
                        <span class="price-period">${p.charge === 'por_dia' ? 'MXN / día' : 'MXN'}</span>
                    </div>

                    <div class="protection-total-box">
                        <span class="total-label">TOTAL CON TARIFA DIARIA</span>
                        <span class="total-amount protection-card-total">${formatMoney(totalConProteccion)}</span>
                    </div>

                    <button class="protection-select-btn ${isSelected ? 'selected' : ''}"
                            data-protection-id="${p.id}"
                            data-protection-name="${escapeHtml(p.nombre)}"
                            data-protection-price="${p.precio}"
                            data-protection-guarantee="${p.garantia}"
                            data-protection-charge="${p.charge}"
                            type="button">
                        ${isSelected ? '✓ Seleccionado' : 'Seleccionar'}
                    </button>
                `;

                track.appendChild(card);
            });

            // Actualizar totales después de cargar
            updateProtectionSummary();

        } catch (e) {
            console.error("Protecciones error:", e);
            track.innerHTML = `<div class="loading" style="padding:12px;font-weight:900;color:rgba(255,255,255,.9);">Error cargando protecciones.</div>`;
        }
    }

    // =========================================
    // SELECCIONAR PROTECCIÓN DESDE BOTÓN
    // =========================================
    function selectProtectionFromButton(button) {
        if (activeProtectionRow === null) {
            showToast("Primero selecciona una fila de tarifa");
            return;
        }

        const card = button.closest('.protection-card');
        if (!card) return;

        const id = button.dataset.protectionId;
        const name = button.dataset.protectionName || "Protección";
        const price = Number(button.dataset.protectionPrice) || 0;
        const guarantee = Number(button.dataset.protectionGuarantee) || 0;
        const charge = button.dataset.protectionCharge || "por_dia";

        const dailyInput = document.querySelector(`.rate-daily-input[data-row-index="${activeProtectionRow}"]`);
        activeBaseDailyPrice = parseMoney(dailyInput?.value);

        const total = activeBaseDailyPrice + price;

        // Desactivar todas las tarjetas y botones
        document.querySelectorAll('.protection-card').forEach((item) => {
            item.classList.remove('active');
            const btn = item.querySelector('.protection-select-btn');
            if (btn) {
                btn.classList.remove('selected');
                btn.textContent = 'Seleccionar';
            }
        });

        // Activar la tarjeta seleccionada
        card.classList.add('active');
        button.classList.add('selected');
        button.textContent = '✓ Seleccionado';

        // Guardar en el estado de protecciones
        protectionState.selectedId = id;
        protectionState.selectedName = name;
        protectionState.selectedPrice = price;
        protectionState.selectedGuarantee = guarantee;
        protectionState.activeRowIndex = activeProtectionRow;
        protectionState.baseDailyPrice = activeBaseDailyPrice;
        protectionState.categoryName = activeCategoryName;

        // Si existe el estado global, actualizarlo
        if (window.state && window.state.setProteccion) {
            window.state.setProteccion({
                id: id,
                nombre: name,
                precio: price,
                charge: charge,
                garantia: guarantee
            });
        }

        // Actualizar la UI de la fila de tarifa
        const selectedProtection = document.getElementById(`selectedProtection${activeProtectionRow}`);
        const finalDailyPrice = document.getElementById(`finalDailyPrice${activeProtectionRow}`);

        if (selectedProtection) {
            selectedProtection.dataset.protectionPrice = price;
            selectedProtection.dataset.protectionId = id;   // 🔧 guardar id del paquete
            selectedProtection.innerHTML = `
                <strong>${escapeHtml(name)}</strong>
                <small>${formatMoney(price)}</small>
            `;
            selectedProtection.style.color = '#0f172a';
        }

        if (finalDailyPrice) {
            finalDailyPrice.textContent = formatMoney(total);
            finalDailyPrice.style.color = '#0f172a';
            finalDailyPrice.style.fontWeight = '900';
        }

        const tarifaTotalHidden = document.getElementById(`tarifaTotal${activeProtectionRow}`);
        if (tarifaTotalHidden) {
            tarifaTotalHidden.value = total.toFixed(2);
        }

        // Actualizar resumen del modal
        updateProtectionSummary();

        showToast(`✅ Protección "${name}" seleccionada`);
    }

    // =========================================
    // DESELECCIONAR PROTECCIÓN
    // =========================================
    function deselectProtection() {
        const rowIndex = activeProtectionRow;

        // Desactivar todas las tarjetas y botones
        document.querySelectorAll('.protection-card').forEach((item) => {
            item.classList.remove('active');
            const btn = item.querySelector('.protection-select-btn');
            if (btn) {
                btn.classList.remove('selected');
                btn.textContent = 'Seleccionar';
            }
        });

        // Limpiar estado de protecciones
        protectionState.selectedId = null;
        protectionState.selectedName = null;
        protectionState.selectedPrice = 0;
        protectionState.selectedGuarantee = 0;

        // Si existe el estado global, limpiarlo
        if (window.state && window.state.setProteccion) {
            window.state.setProteccion(null);
        }

        // Actualizar la UI de la fila de tarifa
        const selectedProtection = document.getElementById(`selectedProtection${rowIndex}`);
        const finalDailyPrice = document.getElementById(`finalDailyPrice${rowIndex}`);

        if (selectedProtection) {
            selectedProtection.innerHTML = `<span style="color: #94a3b8;">Sin protección</span>`;
            selectedProtection.dataset.protectionPrice = '0';
            delete selectedProtection.dataset.protectionId;
            selectedProtection.style.color = '#94a3b8';
        }

        if (finalDailyPrice) {
            finalDailyPrice.textContent = formatMoney(activeBaseDailyPrice);
            finalDailyPrice.style.color = '#0f172a';
            finalDailyPrice.style.fontWeight = '700';
        }

        const tarifaTotalHidden = document.getElementById(`tarifaTotal${rowIndex}`);
        if (tarifaTotalHidden) tarifaTotalHidden.value = "";

        // Actualizar resumen del modal
        updateProtectionSummary();

        showToast('⚠️ Protección deseleccionada');
    }

    function updateProtectionSummary() {
        // Actualizar tarifa base
        const baseEl = document.getElementById("modalBasePrice");
        if (baseEl) {
            baseEl.textContent = formatMoney(activeBaseDailyPrice);
        }

        // Actualizar precio de protección seleccionada
        const protectionPriceEl = document.getElementById("modalProtectionPrice");
        if (protectionPriceEl) {
            const price = protectionState.selectedPrice || 0;
            protectionPriceEl.textContent = price > 0 ? formatMoney(price) : '$0.00 MXN';
            protectionPriceEl.style.color = price > 0 ? '#eeeeee' : '#fdfdfd';
        }

        // Actualizar total seleccionado
        const selectedTotal = document.getElementById("modalSelectedTotal");
        if (selectedTotal) {
            const total = activeBaseDailyPrice + protectionState.selectedPrice;
            selectedTotal.textContent = formatMoney(total);
            selectedTotal.style.color = total > activeBaseDailyPrice ? '#e50914' : '#0f172a';
        }

        // =========================================
        // TEXTO DE CATEGORÍA - SIEMPRE ESTÁTICO
        // =========================================
        const categoryText = document.getElementById("protectionModalCategory");
        if (categoryText) {
            const catName = protectionState.categoryName || activeCategoryName || 'categoría';
            // Siempre mostrar el mismo mensaje sin importar si hay selección
            categoryText.innerHTML = ` Selecciona un paquete de protección para <strong>${catName}</strong>`;
            categoryText.style.color = '#ffffff';
            categoryText.style.fontWeight = '400';
        }

        // Actualizar todas las tarjetas con el total correcto
        document.querySelectorAll('.protection-card').forEach((card) => {
            const price = Number(card.dataset.protectionPrice) || 0;
            const total = activeBaseDailyPrice + price;
            const totalEl = card.querySelector('.protection-card-total');
            if (totalEl) {
                totalEl.textContent = formatMoney(total);
            }
        });
    }

    function openProtectionsModal(button) {
        activeProtectionRow = button.dataset.rowIndex;
        activeCategoryName = button.dataset.category || 'categoría';

        const dailyInput = document.querySelector(`.rate-daily-input[data-row-index="${activeProtectionRow}"]`);
        activeBaseDailyPrice = parseMoney(dailyInput?.value || 0);

        if (activeBaseDailyPrice <= 0) {
            showToast("⚠️ Primero captura la tarifa diaria");
            return;
        }

        // Limpiar selección anterior
        protectionState.selectedId = null;
        protectionState.selectedName = null;
        protectionState.selectedPrice = 0;
        protectionState.selectedGuarantee = 0;
        protectionState.activeRowIndex = activeProtectionRow;
        protectionState.baseDailyPrice = activeBaseDailyPrice;
        protectionState.categoryName = activeCategoryName;

        const categoryText = document.getElementById("protectionModalCategory");
        if (categoryText) {
            categoryText.innerHTML = `📌 Selecciona un paquete de protección para <strong>${activeCategoryName}</strong>`;
            categoryText.style.color = '#ffffff';
            categoryText.style.fontWeight = '400';
        }

        // Actualizar tarifa base en el resumen
        const baseEl = document.getElementById("modalBasePrice");
        if (baseEl) {
            baseEl.textContent = formatMoney(activeBaseDailyPrice);
        }

        const selectedTotal = document.getElementById("modalSelectedTotal");
        if (selectedTotal) {
            selectedTotal.textContent = formatMoney(activeBaseDailyPrice);
        }

        // Cargar protecciones
        loadProtectionPacks();

        const modal = document.getElementById("protectionsModal");
        if (modal) {
            modal.classList.add("active");
            document.body.style.overflow = 'hidden';
        }
    }

    function validarFirmasPaso4() {
        if (!selectedClientType) {
            showToast("Primero selecciona el tipo de cliente");
            return false;
        }

        const firmasRequeridas = {
            fisica: [
                { id: "firmaUsuarioFisica", label: "firma del cliente" },
                { id: "firmaAsesorFisica", label: "firma del asesor" },
            ],
            moral: [
                { id: "firmaRepresentanteLegal", label: "firma del representante legal" },
                { id: "firmaAsesorMoral", label: "firma del asesor" },
            ],
            general: [
                { id: "firmaUsuarioGeneral", label: "firma del usuario" },
                { id: "firmaAsesorGeneral", label: "firma del asesor" },
            ],
        };

        const requeridas = firmasRequeridas[selectedClientType] || [];

        for (const firma of requeridas) {
            const input = document.getElementById(firma.id);
            if (!input || !input.value.trim()) {
                showToast(`⚠️ Falta la ${firma.label}`);
                return false;
            }
        }

        return true;
    }

    function closeProtectionsModal() {
        const modal = document.getElementById("protectionsModal");
        if (modal) {
            modal.classList.remove("active");
            document.body.style.overflow = '';
        }
        activeProtectionRow = null;
    }

    // =========================================
    // APLICAR PROTECCIÓN SELECCIONADA
    // =========================================
    function applySelectedProtection() {
        if (activeProtectionRow === null) {
            showToast("⚠️ No hay una fila de tarifa seleccionada");
            return;
        }

        if (!protectionState.selectedId) {
            showToast("⚠️ Selecciona una protección primero");
            return;
        }

        // La protección ya está aplicada en la UI de la fila
        // Solo cerramos el modal y confirmamos
        closeProtectionsModal();
        showToast(`✅ Protección "${protectionState.selectedName}" aplicada correctamente`);

        // Si existe el estado global, sincronizar
        if (window.state && window.state.syncTotalsHidden) {
            window.state.syncTotalsHidden();
        }
        if (window.state && window.state.refreshSummary) {
            window.state.refreshSummary();
        }
    }

    function autoCalculateRates(rowIndex, changedInput) {
        const dailyInput = document.querySelector(`.rate-daily-input[data-row-index="${rowIndex}"]`);
        const weeklyInput = document.querySelector(`.rate-weekly-input[data-row-index="${rowIndex}"]`);
        const monthlyInput = document.querySelector(`.rate-monthly-input[data-row-index="${rowIndex}"]`);

        if (!dailyInput || !weeklyInput || !monthlyInput) return;

        if (changedInput.classList.contains("rate-weekly-input")) {
            weeklyInput.dataset.manual = weeklyInput.value.trim() ? "1" : "0";
            return;
        }

        if (changedInput.classList.contains("rate-monthly-input")) {
            monthlyInput.dataset.manual = monthlyInput.value.trim() ? "1" : "0";
            return;
        }

        if (!changedInput.classList.contains("rate-daily-input")) return;

        const daily = parseMoney(dailyInput.value);

        if (weeklyInput.dataset.manual !== "1") {
            weeklyInput.value = daily > 0 ? formatMoneySimple(daily * 7) : "";
        }

        if (monthlyInput.dataset.manual !== "1") {
            monthlyInput.value = daily > 0 ? formatMoneySimple(daily * 30) : "";
        }
    }

    function recalculateRowTotal(rowIndex) {
        const dailyInput = document.querySelector(`.rate-daily-input[data-row-index="${rowIndex}"]`);
        const selectedProtection = document.getElementById(`selectedProtection${rowIndex}`);
        const finalDailyPrice = document.getElementById(`finalDailyPrice${rowIndex}`);
        const tarifaTotalHidden = document.getElementById(`tarifaTotal${rowIndex}`);

        const daily = parseMoney(dailyInput?.value);
        const protectionId = selectedProtection?.dataset.protectionId;
        const protection = parseMoney(selectedProtection?.dataset.protectionPrice);

        let total = 0;
        if (protectionId) {
            total = daily + protection;
        }

        if (finalDailyPrice) {
            finalDailyPrice.textContent = total > 0 ? formatMoney(total) : "$0.00 MXN";
        }

        if (tarifaTotalHidden) {
            tarifaTotalHidden.value = total > 0 ? total.toFixed(2) : "";
        }

        if (activeProtectionRow == rowIndex) {
            activeBaseDailyPrice = daily;
            protectionState.baseDailyPrice = daily;
            updateProtectionSummary();
        }
    }

    // =========================================
    // FIRMA DIGITAL
    // =========================================
    function resizeSignatureCanvas(canvas) {
        const ratio = Math.max(window.devicePixelRatio || 1, 1);
        const rect = canvas.getBoundingClientRect();

        canvas.width = rect.width * ratio;
        canvas.height = rect.height * ratio;

        const ctx = canvas.getContext("2d");
        ctx.setTransform(ratio, 0, 0, ratio, 0, 0);
        ctx.lineWidth = 2.8;
        ctx.lineCap = "round";
        ctx.lineJoin = "round";
        ctx.strokeStyle = "#0f172a";
    }

    function clearModalCanvas() {
        const canvas = $("#signatureModalCanvas");
        if (!canvas) return;

        resizeSignatureCanvas(canvas);

        const ctx = canvas.getContext("2d");
        ctx.clearRect(0, 0, canvas.width, canvas.height);
        ctx.beginPath();

        modalDrawing = false;
        modalHasDrawn = false;
    }

    function openSignatureModal(button) {
        activeSignatureTarget = button.dataset.signatureTarget;

        const modal = $("#signatureModal");
        const title = $("#signatureModalTitle");
        const canvas = $("#signatureModalCanvas");

        if (!modal || !canvas || !activeSignatureTarget) return;

        if (title) title.textContent = button.dataset.signatureTitle || "Firma electrónica";

        modal.classList.add("active");

        requestAnimationFrame(() => {
            resizeSignatureCanvas(canvas);
            clearModalCanvas();
        });
    }

    function closeSignatureModal() {
        $("#signatureModal")?.classList.remove("active");
        activeSignatureTarget = null;
        modalDrawing = false;
        modalHasDrawn = false;
    }

    function saveModalSignature() {
        const canvas = $("#signatureModalCanvas");
        const targetInput = activeSignatureTarget ? $(`#${activeSignatureTarget}`) : null;

        if (!canvas || !targetInput) {
            showToast("No se encontró el destino de la firma");
            return;
        }

        if (!modalHasDrawn) {
            showToast("Dibuja una firma primero");
            return;
        }

        const signature = canvas.toDataURL("image/png", 1);
        targetInput.value = signature;

        const preview = document.querySelector(
            `.signature-preview-box[data-signature-target="${activeSignatureTarget}"]`
        );

        if (preview) {
            const img = preview.querySelector(".signature-preview-img");
            const empty = preview.querySelector(".signature-preview-empty");

            if (img) {
                img.src = signature;
                img.style.display = "block";
                img.classList.add("active");
            }

            if (empty) {
                empty.style.display = "none";
            }

            preview.classList.add("signature-saved");
        }

        showToast("Firma guardada");
        closeSignatureModal();
    }

    function clearSignaturePreview(id) {
        const input = $(`#${id}`);
        const box = document.querySelector(`.signature-preview-box[data-signature-target="${id}"]`);
        const img = box?.querySelector(".signature-preview-img");
        const empty = box?.querySelector(".signature-preview-empty");

        if (input) input.value = "";

        if (img) {
            img.removeAttribute("src");
            img.style.display = "none";
            img.classList.remove("active");
        }

        if (empty) empty.style.display = "block";

        box?.classList.remove("signature-saved");
    }

    function initSignatureModal() {
        const modal = $("#signatureModal");
        const canvas = $("#signatureModalCanvas");

        if (!modal || !canvas) return;

        const ctx = canvas.getContext("2d");

        function getPoint(event) {
            const rect = canvas.getBoundingClientRect();
            const touch = event.touches?.[0];

            return {
                x: (touch ? touch.clientX : event.clientX) - rect.left,
                y: (touch ? touch.clientY : event.clientY) - rect.top,
            };
        }

        function start(event) {
            event.preventDefault();
            modalDrawing = true;
            modalHasDrawn = true;

            const point = getPoint(event);
            ctx.beginPath();
            ctx.moveTo(point.x, point.y);
        }

        function move(event) {
            if (!modalDrawing) return;

            event.preventDefault();

            const point = getPoint(event);
            ctx.lineTo(point.x, point.y);
            ctx.stroke();
        }

        function end() {
            modalDrawing = false;
        }

        canvas.addEventListener("mousedown", start);
        canvas.addEventListener("mousemove", move);
        window.addEventListener("mouseup", end);

        canvas.addEventListener("touchstart", start, { passive: false });
        canvas.addEventListener("touchmove", move, { passive: false });
        canvas.addEventListener("touchend", end);

        $("#btnClearSignatureModal")?.addEventListener("click", (event) => {
            event.preventDefault();
            clearModalCanvas();
            showToast("Firma limpiada");
        });

        $("#btnSaveSignatureModal")?.addEventListener("click", saveModalSignature);
        $("#btnCloseSignatureModal")?.addEventListener("click", closeSignatureModal);

        modal.addEventListener("click", (event) => {
            if (event.target === modal) closeSignatureModal();
        });

        window.addEventListener("resize", () => {
            if (modal.classList.contains("active")) {
                setTimeout(() => resizeSignatureCanvas(canvas), 150);
            }
        });
    }

    // =========================================
    // CALENDARIOS Y VALIDACIONES
    // =========================================
    function initBirthdateCalendars() {
        if (typeof flatpickr === "undefined") return;

        const today = new Date();
        const maxAdultDate = new Date(today.getFullYear() - 18, today.getMonth(), today.getDate());

        flatpickr(".birthdate-picker", {
            locale: "es",
            dateFormat: "Y-m-d",
            altInput: true,
            altFormat: "d-M-Y",
            maxDate: maxAdultDate,
            disableMobile: true,
            allowInput: false,
            monthSelectorType: "dropdown",
            showMonths: 1,
            position: "auto center",
        });
    }

    function initLicenseExpiryCalendars() {
        if (typeof flatpickr === "undefined") return;

        flatpickr(".license-expiry-picker", {
            locale: "es",
            dateFormat: "Y-m-d",
            altInput: true,
            altFormat: "d-M-Y",
            minDate: "today",
            disableMobile: true,
            allowInput: false,
            monthSelectorType: "dropdown",
            showMonths: 1,
            position: "auto center",
        });
    }

    function detectIdentificationType(value) {
        const clean = value.trim().toUpperCase();

        if (!clean) return { type: "", label: "—" };
        if (/^[A-Z0-9]{13}$/.test(clean)) return { type: "ine", label: "INE" };
        if (/^\d{7,8}$/.test(clean)) return { type: "cedula", label: "Cédula profesional" };
        if (/^[A-Z0-9]{8,9}$/.test(clean)) return { type: "pasaporte", label: "Pasaporte" };

        return { type: "", label: "No identificado" };
    }

    function initIdentificationDetection() {
        const input = $("#fisicaNumeroIdentificacion");
        const helper = $("#fisicaTipoIdentificacionTexto");
        const hidden = $("#fisicaTipoIdentificacion");

        if (!input || !helper || !hidden) return;

        input.addEventListener("input", () => {
            input.value = input.value.toUpperCase();

            const result = detectIdentificationType(input.value);

            hidden.value = result.type;
            helper.textContent = `Tipo detectado: ${result.label}`;

            helper.classList.remove("is-valid", "is-invalid", "is-empty");
            helper.classList.add(result.type ? "is-valid" : result.label === "—" ? "is-empty" : "is-invalid");
        });
    }

    function markRequiredFields() {
        $$(".field").forEach((field) => {
            const label = field.querySelector("label");
            const control = field.querySelector("input, select, textarea");

            if (label && control?.required) {
                label.classList.add("required");
            }
        });
    }

    function formatMoneyInput(input) {
        let value = input.value.replace(/[^\d.]/g, "");

        const parts = value.split(".");
        let intPart = parts[0] || "";
        let decPart = parts.length > 1 ? parts.slice(1).join("") : null;

        if (decPart !== null) {
            decPart = decPart.slice(0, 2);
        }

        if (!intPart && decPart === null) {
            input.value = "";
            return;
        }

        const intFormatted = intPart ? Number(intPart).toLocaleString("en-US") : "0";

        input.value = decPart !== null
            ? `$${intFormatted}.${decPart}`
            : `$${intFormatted}`;
    }

    // =========================================
    // EVENTOS - CLICK
    // =========================================
    document.addEventListener("click", (event) => {
        // 1. SELECCIÓN DE TIPO DE CLIENTE
        const clientCard = event.target.closest(".client-type-card");
        if (clientCard) {
            selectClientType(clientCard);
            return;
        }

        // 2. NAVEGACIÓN DEL WIZARD (PASOS)
        const wizardStep = event.target.closest(".wizard-step");
        if (wizardStep) {
            const targetStep = Number(wizardStep.dataset.stepTarget);

            if (targetStep > 1 && !selectedClientType) {
                showToast("Primero selecciona el tipo de cliente");
                return;
            }

            goToStep(targetStep);
            return;
        }

        // 3. BOTÓN SIGUIENTE
        const nextBtn = event.target.closest("[data-next-step]");
        if (nextBtn) {
            const nextStep = Number(nextBtn.dataset.nextStep);

            if (nextStep > 1 && !selectedClientType) {
                showToast("Primero selecciona el tipo de cliente");
                return;
            }

            goToStep(nextStep);
            return;
        }

        // 4. BOTÓN ANTERIOR
        const prevBtn = event.target.closest("[data-prev-step]");
        if (prevBtn) {
            goToStep(Number(prevBtn.dataset.prevStep));
            return;
        }

        // 5. ABRIR MODAL DE PROTECCIONES
        const openProtectionBtn = event.target.closest(".btn-open-protections");
        if (openProtectionBtn) {
            openProtectionsModal(openProtectionBtn);
            return;
        }

        // 6. SELECCIÓN DE PROTECCIÓN (VÍA BOTÓN SELECCIONAR)
        const selectBtn = event.target.closest(".protection-select-btn");
        if (selectBtn) {
            // Si ya está seleccionado, deseleccionar
            if (selectBtn.classList.contains('selected')) {
                deselectProtection();
            } else {
                selectProtectionFromButton(selectBtn);
            }
            return;
        }

        // 7. ELIMINAR CLÁUSULA
        const removeClauseBtn = event.target.closest(".btn-remove-clause");
        if (removeClauseBtn) {
            const activeClauses = getActiveClauses();
            activeClauses.splice(Number(removeClauseBtn.dataset.clauseIndex), 1);
            renderClauses();
            showToast("Cláusula eliminada");
            return;
        }

        // 8. ELIMINAR CONDUCTOR
        const removeDriverBtn = event.target.closest(".btn-remove-driver");
        if (removeDriverBtn) {
            const idx = Number(removeDriverBtn.dataset.driverIndex);
            drivers.splice(idx, 1);
            // 🔧 quitar también los archivos ocultos de ese conductor
            $$(`#hiddenDriverFilesContainer [data-driver-file-index="${idx}"]`).forEach((el) => el.remove());
            renderDrivers();
            renderResponsivas();
            showToast("Conductor eliminado");
            return;
        }

        // 9. AGREGAR CLÁUSULA A RESPONSIVA
        const addResponsivaClauseBtn = event.target.closest(".btn-add-responsiva-clause");
        if (addResponsivaClauseBtn) {
            addResponsivaClause(Number(addResponsivaClauseBtn.dataset.driverIndex));
            return;
        }

        // 10. ABRIR MODAL DE FIRMA
        const openSignatureBtn = event.target.closest(".btn-open-signature-modal");
        if (openSignatureBtn) {
            openSignatureModal(openSignatureBtn);
            return;
        }

        // 11. CERRAR MODAL DE PROTECCIONES (CLICK FUERA)
        const protectionsModal = event.target.closest("#protectionsModal");
        if (protectionsModal && event.target === protectionsModal) {
            closeProtectionsModal();
            return;
        }

        // 12. CERRAR MODAL DE FIRMA (CLICK FUERA)
        const signatureModal = event.target.closest("#signatureModal");
        if (signatureModal && event.target === signatureModal) {
            closeSignatureModal();
            return;
        }
    });

    // =========================================
    // EVENTO 'input' PARA RECALCULAR TOTALES
    // =========================================
    document.addEventListener('input', function (event) {
        const input = event.target;

        if (input.classList.contains('money-input')) {
            formatMoneyInput(input);

            if (input.classList.contains('rate-daily-input') ||
                input.classList.contains('rate-weekly-input') ||
                input.classList.contains('rate-monthly-input')) {

                autoCalculateRates(input.dataset.rowIndex, input);
                recalculateRowTotal(input.dataset.rowIndex);
            }
        }
    });

    // =========================================
    // EVENTO 'keydown' PARA CERRAR MODALES CON ESCAPE
    // =========================================
    document.addEventListener('keydown', function (event) {
        if (event.key === 'Escape') {
            const protectionsModal = document.getElementById('protectionsModal');
            if (protectionsModal && protectionsModal.classList.contains('active')) {
                closeProtectionsModal();
                return;
            }

            const signatureModal = document.getElementById('signatureModal');
            if (signatureModal && signatureModal.classList.contains('active')) {
                closeSignatureModal();
                return;
            }
        }
    });

    // =========================================
    // INICIALIZACIÓN DE PROTECCIONES
    // =========================================
    function initProtectionsSystem() {
        console.log('🛡️ Sistema de protecciones inicializado');

        if (window.state && window.state.proteccion) {
            const prot = window.state.proteccion;
            if (prot && prot.id) {
                setTimeout(() => {
                    const btn = document.querySelector(`.protection-select-btn[data-protection-id="${prot.id}"]`);
                    if (btn && !btn.classList.contains('selected')) {
                        selectProtectionFromButton(btn);
                    }
                }, 800);
            }
        }
    }

    // =========================================
    // INICIALIZACIÓN DE EVENTOS UI
    // =========================================
    $("#btnGoDocs")?.addEventListener("click", () => {
        if (!selectedClientType) {
            showToast("Selecciona un tipo de cliente");
            return;
        }

        showDocForm(selectedClientType);
        showAgreementForm(selectedClientType);
        goToStep(2);
    });

    $("#btnCloseProtections")?.addEventListener("click", closeProtectionsModal);
    $("#btnCancelProtections")?.addEventListener("click", closeProtectionsModal);

    // =========================================
    // BOTÓN APLICAR PROTECCIÓN
    // =========================================
    $("#btnApplyProtection")?.addEventListener("click", applySelectedProtection);

    $("#protectionsModal")?.addEventListener("click", (event) => {
        if (event.target === $("#protectionsModal")) closeProtectionsModal();
    });

    $("#btnShowClause")?.addEventListener("click", () => $("#clausePanel")?.classList.add("active"));
    $("#btnCancelClause")?.addEventListener("click", () => $("#clausePanel")?.classList.remove("active"));
    $("#btnAddClause")?.addEventListener("click", addClause);

    $("#btnAddDriver")?.addEventListener("click", addDriver);
    $("#btnGenerateResponsivas")?.addEventListener("click", generateResponsivas);

    $("#btnViewPdf")?.addEventListener("click", () => {
        const form = $("#altaClienteForm");
        const actionInput = $("#accionPostSubmit");

        if (!form || !actionInput) {
            showToast("No se encontró el formulario");
            return;
        }

        if (!validarFirmasPaso4()) {
            return;
        }

        showToast("Generando convenio...");

        actionInput.value = "generar_pdf";
        form.submit();
    });

    $("#btnBack")?.addEventListener("click", () => window.history.back());

    // =========================================
    // RENDERIZADO INICIAL
    // =========================================
    renderClauses();
    renderDrivers();
    renderResponsivas();

    initBirthdateCalendars();
    initLicenseExpiryCalendars();
    initIdentificationDetection();
    initSignatureModal();

    snapshotRequiredFields();   // 🔧 guardar qué campos eran required
    markRequiredFields();

    // Inicializar sistema de protecciones
    setTimeout(initProtectionsSystem, 500);

    // Ir al paso 1
    goToStep(1);

    // =========================================
    // EXPONER API PARA USO EXTERNO
    // =========================================
    window.protectionAPI = {
        openModal: openProtectionsModal,
        closeModal: closeProtectionsModal,
        selectProtection: selectProtectionFromButton,
        deselectProtection: deselectProtection,
        loadPacks: loadProtectionPacks,
        updateSummary: updateProtectionSummary,
        getState: () => ({ ...protectionState }),
        setBasePrice: (price) => {
            activeBaseDailyPrice = Number(price) || 0;
            protectionState.baseDailyPrice = activeBaseDailyPrice;
            updateProtectionSummary();
        },
        recalculateRow: recalculateRowTotal
    };
});
