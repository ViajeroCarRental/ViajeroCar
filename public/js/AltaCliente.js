document.addEventListener("DOMContentLoaded", () => {
    const $ = (s) => document.querySelector(s);
    const $$ = (s) => Array.from(document.querySelectorAll(s));

    let selectedClientType = null;
    let currentStep = 1;
    let activeProtectionRow = null;
    let activeBaseDailyPrice = 0;
    let activeSignatureTarget = null;
    let modalDrawing = false;
    let modalHasDrawn = false;

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

    const getFileName = (input) => input?.files?.[0]?.name || "";

    function getActiveClauses() {
        if (!selectedClientType) return [];
        return clauses[selectedClientType] || [];
    }

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

    function showDocForm(type) {
        $$(".doc-form").forEach((form) => form.classList.remove("active"));

        const selectedForm = $(`.doc-form-${type}`);
        if (selectedForm) selectedForm.classList.add("active");

        const subtitle = $("#docSubtitle");
        if (subtitle) {
            subtitle.textContent = docSubtitles[type] || "Completa la información del cliente.";
        }
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

    function selectClientType(card) {
        selectedClientType = card.dataset.clientType;

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
                                <td>${driver.firmaConductor ? "Guardada" : "Sin firma"}</td>
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

    function updateProtectionCardTotals() {
        $$(".protection-card").forEach((card) => {
            const protectionPrice = Number(card.dataset.protectionPrice) || 0;
            const total = activeBaseDailyPrice + protectionPrice;

            const totalEl = card.querySelector(".protection-card-total");
            if (totalEl) totalEl.textContent = formatMoney(total);
        });

        const baseEl = $("#modalBasePrice");
        if (baseEl) baseEl.textContent = formatMoney(activeBaseDailyPrice);

        const selectedEl = $("#modalSelectedTotal");
        if (selectedEl) selectedEl.textContent = formatMoney(activeBaseDailyPrice);
    }

    function openProtectionsModal(button) {
        activeProtectionRow = button.dataset.rowIndex;

        const dailyInput = $(`.rate-daily-input[data-row-index="${activeProtectionRow}"]`);
        activeBaseDailyPrice = parseMoney(dailyInput?.value || 0);

        if (activeBaseDailyPrice <= 0) {
            showToast("Primero captura la tarifa diaria");
            return;
        }

        const categoryText = $("#protectionModalCategory");
        if (categoryText) {
            categoryText.textContent = `Selecciona un paquete para ${button.dataset.category || "categoría"}.`;
        }

        $$(".protection-card").forEach((card) => card.classList.remove("active"));
        updateProtectionCardTotals();

        $("#protectionsModal")?.classList.add("active");
    }

    function closeProtectionsModal() {
        $("#protectionsModal")?.classList.remove("active");
    }

    function selectProtection(card) {
        if (activeProtectionRow === null) {
            showToast("Selecciona una fila de tarifa primero");
            return;
        }

        const name = card.dataset.protectionName || "Protección";
        const price = Number(card.dataset.protectionPrice) || 0;
        const total = activeBaseDailyPrice + price;

        const selectedProtection = $(`#selectedProtection${activeProtectionRow}`);
        const finalDailyPrice = $(`#finalDailyPrice${activeProtectionRow}`);

        if (selectedProtection) {
            selectedProtection.dataset.protectionPrice = price;
            selectedProtection.innerHTML = `
                <strong>${name}</strong>
                <small>${formatMoney(price)}</small>
            `;
        }

        if (finalDailyPrice) {
            finalDailyPrice.textContent = formatMoney(total);
        }

        $$(".protection-card").forEach((item) => item.classList.remove("active"));
        card.classList.add("active");

        const selectedTotal = $("#modalSelectedTotal");
        if (selectedTotal) selectedTotal.textContent = formatMoney(total);

        showToast("Protección seleccionada");
        closeProtectionsModal();
    }

    function recalculateRowTotal(rowIndex) {
        const daily = parseMoney($(`.rate-daily-input[data-row-index="${rowIndex}"]`)?.value);
        const weekly = parseMoney($(`.rate-weekly-input[data-row-index="${rowIndex}"]`)?.value);
        const monthly = parseMoney($(`.rate-monthly-input[data-row-index="${rowIndex}"]`)?.value);

        const selectedProtection = $(`#selectedProtection${rowIndex}`);
        const finalDailyPrice = $(`#finalDailyPrice${rowIndex}`);

        const protection = Number(selectedProtection?.dataset.protectionPrice) || 0;
        const final = Math.max(daily, weekly, monthly) + protection;

        if (finalDailyPrice) {
            finalDailyPrice.textContent = formatMoney(final);
        }
    }

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
        if (parts.length > 2) {
            value = `${parts[0]}.${parts.slice(1).join("")}`;
        }

        input.value = value ? `$${value}` : "";
    }

    document.addEventListener("click", (event) => {
        const clientCard = event.target.closest(".client-type-card");
        if (clientCard) {
            selectClientType(clientCard);
            return;
        }

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

        const prevBtn = event.target.closest("[data-prev-step]");
        if (prevBtn) {
            goToStep(Number(prevBtn.dataset.prevStep));
            return;
        }

        const openProtectionBtn = event.target.closest(".btn-open-protections");
        if (openProtectionBtn) {
            openProtectionsModal(openProtectionBtn);
            return;
        }

        const protectionCard = event.target.closest(".protection-card");
        if (protectionCard && event.target.closest(".protection-toggle")) {
            selectProtection(protectionCard);
            return;
        }

        const removeClauseBtn = event.target.closest(".btn-remove-clause");
        if (removeClauseBtn) {
            const activeClauses = getActiveClauses();
            activeClauses.splice(Number(removeClauseBtn.dataset.clauseIndex), 1);
            renderClauses();
            showToast("Cláusula eliminada");
            return;
        }

        const removeDriverBtn = event.target.closest(".btn-remove-driver");
        if (removeDriverBtn) {
            drivers.splice(Number(removeDriverBtn.dataset.driverIndex), 1);
            renderDrivers();
            renderResponsivas();
            showToast("Conductor eliminado");
            return;
        }

        const addResponsivaClauseBtn = event.target.closest(".btn-add-responsiva-clause");
        if (addResponsivaClauseBtn) {
            addResponsivaClause(Number(addResponsivaClauseBtn.dataset.driverIndex));
            return;
        }

        const openSignatureBtn = event.target.closest(".btn-open-signature-modal");
        if (openSignatureBtn) {
            openSignatureModal(openSignatureBtn);
        }
    });

    document.addEventListener("input", (event) => {
        const input = event.target;

        if (input.classList.contains("money-input")) {
            formatMoneyInput(input);

            if (
                input.classList.contains("rate-daily-input") ||
                input.classList.contains("rate-weekly-input") ||
                input.classList.contains("rate-monthly-input")
            ) {
                recalculateRowTotal(input.dataset.rowIndex);
            }
        }
    });

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

    $("#protectionsModal")?.addEventListener("click", (event) => {
        if (event.target === $("#protectionsModal")) closeProtectionsModal();
    });

    $("#btnShowClause")?.addEventListener("click", () => $("#clausePanel")?.classList.add("active"));
    $("#btnCancelClause")?.addEventListener("click", () => $("#clausePanel")?.classList.remove("active"));
    $("#btnAddClause")?.addEventListener("click", addClause);

    $("#btnAddDriver")?.addEventListener("click", addDriver);
    $("#btnGenerateResponsivas")?.addEventListener("click", generateResponsivas);

    $("#btnViewPdf")?.addEventListener("click", () => {
        showToast("Vista previa del convenio pendiente de conectar");
    });

    $("#btnFinishVisual")?.addEventListener("click", () => {
        showToast("Registro visual finalizado");
    });

    $("#btnBack")?.addEventListener("click", () => window.history.back());

    renderClauses();
    renderDrivers();
    renderResponsivas();

    initBirthdateCalendars();
    initLicenseExpiryCalendars();
    initIdentificationDetection();
    initSignatureModal();

    markRequiredFields();
    goToStep(currentStep);
});