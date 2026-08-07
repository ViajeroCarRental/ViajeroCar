document.addEventListener("DOMContentLoaded", () => {

    const tbody = document.getElementById("tbody");
    const search = document.getElementById("txtSearch");
    const size = document.getElementById("selSize");
    const prev = document.getElementById("prev");
    const next = document.getElementById("next");
    const pgInfo = document.getElementById("pgInfo");
    const filtroFechaCierre = document.getElementById("filtroFechaCierre");
    const filtroOficina = document.getElementById("filtroOficina");
    const filtroEstatus = document.getElementById("filtroEstatus");
    const btnLimpiarFiltros = document.getElementById("btnLimpiarFiltros");
    const categoryFilters = document.getElementById("categoryFilters");


    let page = 1;
    let lastPage = 1;
    let selectedCategory = "";

    // ============================================================
    // Cargar datos principales
    // ============================================================
    function formatDate(value) {
        if (!value) return "—";

        const datePart = String(value).split("T")[0];
        const match = datePart.match(/^(\d{4})-(\d{2})-(\d{2})$/);
        if (!match) return value;

        const meses = ["ene", "feb", "mar", "abr", "may", "jun", "jul", "ago", "sep", "oct", "nov", "dic"];
        return `${match[3]}-${meses[Number(match[2]) - 1]}-${match[1]}`;
    }

    function formatTime(value) {
        if (!value) return "—";
        const match = String(value).match(/^(\d{1,2}):(\d{2})/);
        return match ? `${String(match[1]).padStart(2, "0")}:${match[2]} hrs` : value;
    }

    function paymentStatus(value) {
        const normalized = String(value ?? "").trim().toLowerCase();
        return normalized === "pagado" || normalized === "paid" || normalized === "1"
            ? { text: "Pagado", css: "payment-paid" }
            : { text: "Pendiente", css: "payment-pending" };
    }

    function escapeHtml(value) {
        return String(value ?? "")
            .replaceAll("&", "&amp;")
            .replaceAll("<", "&lt;")
            .replaceAll(">", "&gt;")
            .replaceAll('"', "&quot;")
            .replaceAll("'", "&#039;");
    }

    function renderCategoryFilters(counts = [], total = 0) {
        if (!categoryFilters) return;
        const options = [
            { categoria: "", total, label: "Todas" },
            ...counts.map(item => ({
                categoria: String(item.categoria ?? ""),
                total: Number(item.total ?? 0),
                label: String(item.categoria ?? "Sin categoría")
            }))
        ];
        categoryFilters.innerHTML = options.map(item => {
            const active = item.categoria === selectedCategory ? " is-active" : "";
            return `<button class="category-chip${active}" type="button" data-category="${escapeHtml(item.categoria)}"><span>${escapeHtml(item.label)}</span><strong>${item.total}</strong></button>`;
        }).join("");
    }

    function renderSelectOptions(select, items, emptyLabel, valueKey, labelKey) {
        if (!select) return;

        const currentValue = select.value;
        select.innerHTML = [
            `<option value="">${escapeHtml(emptyLabel)}</option>`,
            ...items.map(item => `
                <option value="${escapeHtml(item[valueKey])}">
                    ${escapeHtml(item[labelKey])}
                </option>
            `)
        ].join("");

        if ([...select.options].some(option => option.value === currentValue)) {
            select.value = currentValue;
        }
    }

    function loadData() {

        tbody.innerHTML = `
            <tr><td colspan="10" style="text-align:center;padding:20px">Cargando…</td></tr>
        `;
 
        const params = new URLSearchParams({
            q: search.value,
            size: size.value,
            page: page,
            fecha_cierre: filtroFechaCierre?.value ?? "",
            categoria: selectedCategory,
            oficina: filtroOficina?.value ?? "",
            estatus: filtroEstatus?.value ?? ""
        });

        fetch(`/api/contratos-abiertos?${params.toString()}`)
            .then(r => r.json())
            .then(json => {

                lastPage = json.last_page;
                pgInfo.textContent = `Página ${page} de ${lastPage}`;
                tbody.innerHTML = "";
                renderCategoryFilters(json.conteo_categorias ?? [], Number(json.total_filtrado ?? 0));
                renderSelectOptions(
                    filtroOficina,
                    json.filtros?.oficinas ?? [],
                    "Todas",
                    "id",
                    "nombre"
                );
                renderSelectOptions(
                    filtroEstatus,
                    json.filtros?.estatus ?? [],
                    "Todos",
                    "valor",
                    "etiqueta"
                );

                json.data.forEach(r => {

    const tr = document.createElement("tr");
    const esVencido = Boolean(r.es_vencido);

    // 🔒 Guardamos el id_contrato real en la fila (oculto)
    tr.dataset.id = r.id_contrato;

    if (esVencido) {
        tr.classList.add("contract-expired");
    }

    const nombreUsuario = `${r.nombre ?? ""} ${r.apellidos ?? ""}`.trim() || "—";

    let dropoff = "—";
    if (Number(r.tiene_dropoff) === 1) {
        if (Number(r.delivery_ubicacion) === 0) {
            dropoff = r.delivery_direccion || "Sin dirección";
        } else {
            dropoff = [r.ubic_estado, r.ubic_destino]
                .filter(Boolean)
                .join(" - ") || "Sin ubicación";
        }
    }

    tr.innerHTML = `
        <td>
            <button class="btnToggle" type="button" aria-label="Ver detalles">+</button>
        </td>

        <td>
            <div class="checkout-date-cell">
                <span>${formatDate(r.fecha_fin)}</span>
                ${esVencido ? '<span class="expired-badge">Vencido</span>' : ''}
            </div>
        </td>
        <td>${formatTime(r.hora_entrega)}</td>
        <td>${escapeHtml(r.numero_contrato ?? "—")}</td>
        <td>${escapeHtml(dropoff)}</td>
        <td><strong class="category-code">${escapeHtml(r.categoria ?? "—")}</strong></td>
        <td>${escapeHtml(nombreUsuario)}</td>
        <td>$${Number(r.tarifa_diaria ?? 0).toFixed(2)}</td>
        <td>${Number(r.dias_renta ?? 1)}</td>
        <td>$${Number(r.total_renta ?? 0).toFixed(2)}</td>
    `;

    tbody.appendChild(tr);
});

            })
            .catch(err => {
                console.error("❌ ERROR FETCH:", err);
                tbody.innerHTML = `
                    <tr><td colspan="10" style="text-align:center;padding:20px;color:red">
                        Error al cargar datos
                    </td></tr>`;
            });
    }




    // ============================================================
    // Expandir detalles del contrato
    // ============================================================
    document.addEventListener("click", async (e) => {
        if (!e.target.classList.contains("btnToggle")) return;

const btn = e.target;
const tr  = btn.closest("tr");

// 🔑 Tomamos SIEMPRE el id_contrato guardado en la fila
const id  = tr.dataset.id;


        const nextRow = tr.nextElementSibling;
        if (nextRow && nextRow.classList.contains("detail")) {
            nextRow.remove();
            btn.textContent = "+";
            btn.classList.remove("is-open");
            btn.setAttribute("aria-label", "Ver detalles");
            return;
        }

        try {

            const res = await fetch(`/api/contratos-abiertos/${id}`);
            if (!res.ok) throw new Error(`HTTP ${res.status}`);
            const json = await res.json();
            if (!json.ok) throw new Error("Respuesta ok = false");

            const d = json.data;

            //Seguros
            const segurosPaqueteHTML = (json.segurosPaquete || [])
                .map(s => `• ${s.nombre} ($${Number(s.precio_por_dia).toFixed(2)}/día)`)
                .join("<br>");

            const segurosIndividualesHTML = (json.segurosIndividuales || [])
                .map(s => `• ${s.nombre} ($${Number(s.precio_por_dia).toFixed(2)}/día)`)
                .join("<br>");

            //Gasolina faltante
            const comb = json.combustible || {};


            //Daños nuevos.
            const danosNuevos = json.danos_nuevos || [];

            const danosHTML = danosNuevos.length
                ? danosNuevos.map(d => `• ${d.nombre_zona}: ${d.comentario}`).join("<br>")
                : "Sin daños nuevos";


            const disabled = !esSuperAdmin
            ? 'disabled style="opacity:0.5;cursor:not-allowed;"'
            : '';

            const row = document.createElement("tr");
            row.classList.add("detail");

            const nombreCliente = `${d.nombre_cliente ?? ""} ${d.apellidos_cliente ?? ""}`.trim() || "—";
            const vehiculo = `${d.marca ?? ""} ${d.modelo ?? ""}`.trim() || "—";
            const paquete = segurosPaqueteHTML || "Sin paquete adicional";
            const individuales = segurosIndividualesHTML || "Sin seguros individuales";

            const icon = (name) => {
                const icons = {
                    location: '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M20 10c0 5-8 12-8 12S4 15 4 10a8 8 0 1 1 16 0Z"/><circle cx="12" cy="10" r="2.5"/></svg>',
                    car: '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="m5 11 1.8-5h10.4l1.8 5"/><path d="M3 13a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2v5H3v-5Z"/><path d="M5 18v2M19 18v2M7 14h.01M17 14h.01"/></svg>',
                    user: '<svg viewBox="0 0 24 24" aria-hidden="true"><circle cx="12" cy="8" r="4"/><path d="M4 21a8 8 0 0 1 16 0"/></svg>',
                    file: '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M6 3h9l4 4v14H6Z"/><path d="M15 3v5h4M9 13h6M9 17h6"/></svg>',
                    shield: '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 3 20 6v6c0 5-3.4 8-8 9-4.6-1-8-4-8-9V6l8-3Z"/><path d="m9 12 2 2 4-4"/></svg>',
                    gauge: '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M4 16a8 8 0 1 1 16 0"/><path d="m12 14 4-4M7 18h10"/></svg>',
                    alert: '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 3 2 21h20L12 3Z"/><path d="M12 9v5M12 18h.01"/></svg>'
                };
                return icons[name] || "";
            };

            row.innerHTML = `
    <td colspan="10">
        <div class="reserva-summary contrato-summary">

            <div class="summary-contract-head summary-full">
                <div>
                    <span class="summary-eyebrow">Contrato</span>
                    <strong>No. ${escapeHtml(d.numero_contrato || "—")}</strong>
                    <small>Reservación ${escapeHtml(d.clave || "—")}</small>
                </div>
                <span class="status-tag status-${String(d.estado ?? "").toLowerCase()}">${escapeHtml(d.estado || "—")}</span>
            </div>

            <div class="summary-route-card">
                <div class="summary-section-title">Entrega</div>
                <div class="summary-route-layout">
                    <span class="summary-main-icon">${icon("location")}</span>
                    <div>
                        <div class="summary-office">${escapeHtml(d.entrega_lugar || "—")}</div>
                        <div class="summary-datetime">${formatDate(d.entrega_fecha)} · ${formatTime(d.entrega_hora)}</div>
                    </div>
                </div>
            </div>

            <div class="summary-route-card">
                <div class="summary-section-title">Devolución</div>
                <div class="summary-route-layout">
                    <span class="summary-main-icon">${icon("location")}</span>
                    <div>
                        <div class="summary-office">${escapeHtml(d.dev_lugar || d.sucursal_entrega_nombre || "—")}</div>
                        <div class="summary-datetime">${formatDate(d.dev_fecha)} · ${formatTime(d.dev_hora)}</div>
                    </div>
                </div>
            </div>

            <div class="summary-vehicle">
                <div class="summary-vehicle-data">
                    <div class="summary-section-title">Vehículo</div>
                    <span class="summary-car-icon">${icon("car")}</span>
                    <div>
                        <div class="summary-vehicle-name">${escapeHtml(vehiculo)}</div>
                        <div class="summary-vehicle-meta">${escapeHtml(d.categoria || "—")} · ${escapeHtml(d.categoria_codigo || "—")}</div>
                    </div>
                </div>
            </div>

            <div class="summary-rate-card">
                <div class="summary-section-title">Tarifa</div>
                <div class="summary-rate-row"><span>Tarifa base</span><strong>$${Number(d.tarifa_base ?? 0).toFixed(2)}</strong></div>
                <div class="summary-rate-row"><span>Dropoff</span><strong>$${Number(d.delivery_total ?? 0).toFixed(2)}</strong></div>
            </div>

            <div class="summary-info-card">
                <div class="summary-card-heading"><span>${icon("user")}</span><div class="summary-section-title">Cliente y contacto</div></div>
                <div class="summary-detail-line"><strong>${escapeHtml(nombreCliente)}</strong></div>
                <div class="summary-detail-line">${escapeHtml([d.pais, d.telefono].filter(Boolean).join(" · ") || "—")}</div>
                <div class="summary-detail-line">${escapeHtml(d.email_cliente || "—")}</div>
            </div>

            <div class="summary-info-card">
                <div class="summary-card-heading"><span>${icon("file")}</span><div class="summary-section-title">Datos del contrato</div></div>
                <div class="summary-detail-line"><strong>Oficina de regreso:</strong> ${escapeHtml(d.sucursal_entrega_nombre || "—")}</div>
                <div class="summary-detail-line"><strong>Método de pago:</strong> ${escapeHtml(d.metodo_pago || "N/A")}</div>
                <div class="summary-detail-line"><strong>Adicionales:</strong> ${escapeHtml(d.adicionales || "—")}</div>
            </div>

            <div class="summary-info-card">
                <div class="summary-card-heading"><span>${icon("shield")}</span><div class="summary-section-title">Seguros</div></div>
                <div class="summary-detail-line"><strong>Paquete:</strong> ${paquete}</div>
                <div class="summary-detail-line"><strong>Seguros:</strong> ${individuales}</div>
            </div>

            <div class="summary-info-card summary-full">
                <div class="summary-section-title">Revisión de devolución</div>
                <div class="summary-review-grid">
                    <div><i>${icon("gauge")}</i><strong>Combustible</strong><span>Salida: ${comb.salida ?? 0} L · Regreso: ${comb.entrada ?? 0} L · Faltante: ${comb.faltante ?? 0} L</span></div>
                    <div><i>${icon("gauge")}</i><strong>Cargo de combustible</strong><span>${comb.faltante > 0 ? `$${Number(comb.total ?? 0).toFixed(2)}` : "Sin cargo"}</span></div>
                    <div><i>${icon("alert")}</i><strong>Nuevos daños</strong><span>${danosHTML}</span></div>
                </div>
            </div>

            <div class="summary-payment summary-full">
                <div><b>Total del contrato</b><span>${escapeHtml(d.metodo_pago || "N/A")}</span></div>
                <strong>$${Number(d.total ?? 0).toFixed(2)} <small>MXN</small></strong>
            </div>

            <div class="summary-actions">
                <div class="summary-actions-left">
                    <a class="btn b-primary" href="/admin/reservacion/${d.id_contrato}/checklist?modo=regreso">CHECK</a>
                    <button class="btn b-warning btnExtension" data-id="${d.id_contrato}">EXTENSIÓN</button>
                    <input type="date" class="inputExtension" data-id="${d.id_contrato}" style="display:none;">
                    <button class="btn b-primary btnEditarContrato" data-id="${d.id_contrato}">CIERRE PENDIENTE</button>
                </div>
                <div class="summary-actions-right">
                    <button class="btn b-primary btnEditarContrato" data-id="${d.id_contrato}" ${disabled}>EDITAR</button>
                    <button class="btn b-red btnFinalizarContrato" data-id="${d.id_contrato}">CIERRE</button>
                </div>
            </div>

        </div>
    </td>
`;

/* Diseño anterior eliminado: se conserva un solo resumen para evitar estilos duplicados.
        <div class="card">

            <div class="card-hd">
                <div class="card-title">
                    Contrato ID · ${d.id_contrato} <span style="opacity:.6">/ No. ${d.numero_contrato ?? "—"}</span>
                </div>
                <div class="card-meta">
                    <span class="badge st-pend">${d.estado}</span>
                    <span class="badge">Web</span>
                </div>
            </div>

            <div style="padding:0 16px 10px; color:#667085; font-weight:700;">
                Reserva · ${d.clave}
            </div>

            <div class="card-bd">
                <div class="block">

                    <div class="kv">
                        <div class="k">Cliente</div>
                        <div class="v">
                            ${d.nombre_cliente ?? ""} ${d.apellidos_cliente ?? ""}
                        </div>
                    </div>

                    <div class="kv">
                        <div class="k">Oficina de regreso contraída</div>
                        <div class="v">
                            ${d.sucursal_entrega_nombre ?? "—"}
                        </div>
                    </div>

                    <div class="kv">
                        <div class="k">Lugar de estancia</div>
                        <div class="v">
                        </div>
                    </div>

                    <div class="kv">
                        <div class="k">Contacto</div>
                        <div class="v">${d.pais ?? ""} · ${d.telefono ?? ""}<br>
                            ${d.email_cliente ?? ""}

                        </div>
                    </div>

                    <div class="kv">
                        <div class="k">Vehículo</div>
                        <div class="v">${d.categoria ?? ""} · ${d.marca ?? ""} ${d.modelo ?? ""}</div>
                    </div>

                    <div class="kv">
                        <div class="k">Tarifa</div>
                        <div class="v">

                            <!-- Categoría -->
                            <b>${d.categoria ?? ""} (${d.categoria_codigo ?? ""})</b><br>
                            Tarifa base: $${Number(d.tarifa_base ?? 0).toFixed(2)}<br><br>

                            <!-- Seguro paquete -->
                            ${
                                segurosPaqueteHTML
                                    ? `<b>Paquete:</b><br>${segurosPaqueteHTML}<br><br>`
                                    : ""
                            }

                            <!-- Seguros individuales -->
                            ${
                                segurosIndividualesHTML
                                    ? `<b>Seguros:</b><br>${segurosIndividualesHTML}`
                                    : ""
                            }

                        </div>
                    </div>

                    <div class="kv">
                        <div class="k">Combustible</div>
                        <div class="v">

                            Salida: ${comb.salida ?? 0} L<br>
                            Regreso: ${comb.entrada ?? 0} L<br>
                            Faltante: <b>${comb.faltante ?? 0} L</b><br>

                            ${
                                comb.faltante > 0
                                    ? `Costo: $${Number(comb.total ?? 0).toFixed(2)}`
                                    : "Sin cargo"
                            }
                        </div>
                    </div>

                    <div class="kv">
                        <div class="k">Nuevos daños</div>
                        <div class="v">
                            ${danosHTML}
                        </div>
                    </div>

                    <div class="kv">
                        <div class="k">Adicionales</div>
                        <div class="v">${d.adicionales ?? "—"}</div>
                    </div>
                </div>

                <div class="timeline">
                    <div class="tl-item">
                        <div class="tl-dot"></div>
                        <div class="tl-body">
                            <div class="tl-title">Entrega</div>
                            <div class="tl-sub">
                                ${d.entrega_lugar}<br>
                                ${formatDate(d.entrega_fecha)} · ${formatTime(d.entrega_hora)}
                            </div>
                        </div>
                    </div>

                    <div class="tl-item">
                        <div class="tl-dot" style="background:#0EA5E9"></div>
                        <div class="tl-body">
                            <div class="tl-title">Devolución</div>
                            <div class="tl-sub">
                                ${d.dev_lugar}<br>
                                ${formatDate(d.dev_fecha)} · ${formatTime(d.dev_hora)}
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card-ft">
                <div class="total">
                    <img src="/img/wallet.svg" style="width:18px">
                    $${Number(d.total ?? 0).toFixed(2)}
                    <small>· ${d.metodo_pago ?? "N/A"}</small>


                    <a href="{{ route('checklist2', ['id' => $contrato->id_contrato]) }}" class="btn-checklist"
                            style="
                    margin-right: auto;
                    padding: 10px 16px;
                    border-radius: 10px;
                    font-size: 13px;
                    font-weight: 700;
                    text-decoration: none;
                    border: 1px solid transparent;
                    background: #FF1E2D;
                    color: #ffffff;
                    box-shadow: 0 2px 8px rgba(16,24,40,.06);
                ">
                            Cambio de Vehículo
                        </a>
            </div>


            <div>
                <a class="btn b-primary"
                href="/admin/reservacion/${d.id_contrato}/checklist?modo=regreso">
                    CHECK
                </a>
            </div>


                <div style="display:flex; gap:10px;">

                    <button class="btn b-warning btnExtension"
                        data-id="${d.id_contrato}">
                        EXTENSIÓN
                    </button>

                    <input type="date"

                    class="inputExtension"
                    data-id="${d.id_contrato}"
                    style="display:none;">

                    <button class="btn b-primary btnEditarContrato"
                        data-id="${d.id_contrato}">
                        CIERRE PENDIENTE
                    </button>

                    <button class="btn b-primary btnEditarContrato"
                        data-id="${d.id_contrato}"
                        ${disabled}>
                        EDITAR
                    </button>

                    <button class="btn b-red btnFinalizarContrato"
                        data-id="${d.id_contrato}">
                        CIERRE
                    </button>
                </div>
            </div>

        </div>
    </td>
`;
*/


            tr.insertAdjacentElement("afterend", row);
            btn.textContent = "−";
            btn.classList.add("is-open");
            btn.setAttribute("aria-label", "Ocultar detalles");

        } catch (err) {
            console.error("❌ Error al cargar detalle:", err);
            alert("Error al cargar los detalles del contrato");
        }
    });






    // ============================================================
    // Buscador + paginación
    // ============================================================
    search.addEventListener("input", () => {
        page = 1;
        loadData();
    });

    size.addEventListener("change", () => {
        page = 1;
        loadData();
    });

    filtroFechaCierre?.addEventListener("change", () => {
        page = 1;
        loadData();
    });

    filtroOficina?.addEventListener("change", () => {
        page = 1;
        loadData();
    });

    filtroEstatus?.addEventListener("change", () => {
        page = 1;
        loadData();
    });

    btnLimpiarFiltros?.addEventListener("click", () => {
        search.value = "";
        filtroFechaCierre.value = "";
        filtroOficina.value = "";
        filtroEstatus.value = "";
        selectedCategory = "";
        page = 1;
        loadData();
    });

    categoryFilters?.addEventListener("click", (event) => {
        const chip = event.target.closest(".category-chip");
        if (!chip) return;
        selectedCategory = chip.dataset.category ?? "";
        page = 1;
        loadData();
    });

    prev.addEventListener("click", () => {
        if (page > 1) {
            page--;
            loadData();
        }
    });

    next.addEventListener("click", () => {
        if (page < lastPage) {
            page++;
            loadData();
        }
    });

    // Inicial
    loadData();

 // ============================================================
 // Botón Extension.
 // ============================================================
document.addEventListener("click", async function(e){

    // Abrir calendario
    if(e.target.classList.contains("btnExtension")){

        const id = e.target.dataset.id;
        const input = document.querySelector(`.inputExtension[data-id="${id}"]`);

        // bloquear hoy
        const hoy = new Date();
        hoy.setDate(hoy.getDate() + 1);

        const yyyy = hoy.getFullYear();
        const mm = String(hoy.getMonth()+1).padStart(2,'0');
        const dd = String(hoy.getDate()).padStart(2,'0');

        input.min = `${yyyy}-${mm}-${dd}`;

        input.style.display = "block";
        input.showPicker(); // Chrome moderno
    }

});


document.addEventListener("change", async function(e){

    if(e.target.classList.contains("inputExtension")){

        const id = e.target.dataset.id;
        const fecha = e.target.value;

        if(!fecha) return;

        const res = await fetch(`/admin/contrato/${id}/extension`,{
            method:'POST',
            headers:{
                'Content-Type':'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            },
            body: JSON.stringify({
                fecha_fin: fecha
            })
        });

        const data = await res.json();

        if(data.ok){
            alert("Fecha extendida correctamente");
            location.reload();
        }else{
            alert("Error al guardar");
        }
    }

});


    // ============================================================
    // Botón EDITAR
    // ============================================================
    document.addEventListener("click", (e) => {
    if (!e.target.classList.contains("btnEditarContrato")) return;

    // Primero intentamos leer data-id del botón
    let idContrato = e.target.dataset.id;

    // Si no trae, usamos el de la fila
    if (!idContrato) {
        const tr = e.target.closest("tr");
        idContrato = tr?.dataset.id;
    }

    if (!idContrato) {
        console.error("No se pudo obtener id_contrato para EDITAR");
        return;
    }

    window.location.href = `/admin/contrato/${idContrato}`;
});







    // ============================================================
// Botón FINALIZAR
// ============================================================
document.addEventListener("click", async (e) => {
    if (!e.target.classList.contains("btnFinalizarContrato")) return;

    // 1) Sacar el id_contrato
    let idContrato = e.target.dataset.id;

    // Si el botón no trae data-id, lo tomamos de la fila
    if (!idContrato) {
        const tr = e.target.closest("tr");
        idContrato = tr ? tr.dataset.id : null;
    }

    if (!idContrato) {
        console.error("No se pudo obtener id_contrato para FINALIZAR");
        alertify.error("No se pudo identificar el contrato.");
        return;
    }

    try {
        // 2) Consultar saldo pendiente
        const respSaldo = await fetch(`/admin/contrato/${idContrato}/saldo`);
        const jsonSaldo = await respSaldo.json();

        if (!jsonSaldo.ok) {
            alertify.error("Error consultando saldo.");
            return;
        }

        const saldo = jsonSaldo.saldo;

        // 3) Si hay saldo pendiente → mostrar alerta
        if (saldo > 0) {
            mostrarModalFinalizar({
                titulo: "Pago pendiente",
                mensaje: `Este contrato tiene un saldo pendiente de <b>$${saldo.toFixed(2)}</b>.<br><br>
                          Debes liquidarlo antes de finalizar.`,
                textoOK: "Ir a pagar",
                onOK: () => window.location.href = `/admin/contrato/${idContrato}`,
            });
            return;
        }

        // 4) Si NO hay saldo pendiente → confirmar cierre
        mostrarModalFinalizar({
            titulo: "Finalizar contrato",
            mensaje: `
                ¿Deseas finalizar el contrato?<br><br>
                Esto generará:<br>
                • Ticket PDF<br>
                • PDF de pagos<br>
                • Enviado por correo al cliente.
            `,
            textoOK: "Finalizar",
            onOK: async () => {
                try {
                    const respCerrar = await fetch(`/admin/contrato/${idContrato}/cerrar`, {
                        method: "POST",
                        headers: {
                            "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').content,
                            "Content-Type": "application/json"
                        }
                    });

                    const jsonCerrar = await respCerrar.json();

                    if (!jsonCerrar.ok) {
                        alertify.error(jsonCerrar.msg || "Error al finalizar.");
                        return;
                    }

                    alertify.success("Contrato finalizado y correo enviado.");
                    // 🔥 REDIRECCIÓN AUTOMÁTICA AL CHECKLIST (REGRESO)
                    setTimeout(() => {
                    window.location.href = `/admin/reservacion/${idContrato}/checklist?modo=regreso`;
                    }, 800);
                } catch (err) {
                    console.error(err);
                    alertify.error("Error al procesar finalización.");
                }
            }
        });

    } catch (err) {
        console.error(err);
        alertify.error("Error al consultar saldo.");
    }
});




    // ============================================================
    // Modal genérico para finalización
    // ============================================================
    function mostrarModalFinalizar({ titulo, mensaje, textoOK, onOK }) {

        let modal = document.getElementById("modalFinalizar");
        if (!modal) {
            // Crear modal si no existe
            modal = document.createElement("div");
            modal.id = "modalFinalizar";
            modal.className = "modal-fin";
            modal.style.display = "none";
            modal.innerHTML = `
                <div class="modal-fin-box">
                    <h2 id="mf_titulo"></h2>
                    <p id="mf_msg"></p>

                    <div class="mf-btns">
                        <button id="mf_cancel" class="btn gray">Cancelar</button>
                        <button id="mf_ok" class="btn b-primary">Aceptar</button>
                    </div>
                </div>
            `;
            document.body.appendChild(modal);
        }

        const mt = document.getElementById("mf_titulo");
        const mm = document.getElementById("mf_msg");
        const ok = document.getElementById("mf_ok");
        const cancel = document.getElementById("mf_cancel");

        mt.innerHTML = titulo;
        mm.innerHTML = mensaje;
        ok.textContent = textoOK;

        modal.style.display = "flex";

        const cerrar = () => modal.style.display = "none";

        cancel.onclick = () => cerrar();

        ok.onclick = () => {
            cerrar();
            if (typeof onOK === "function") onOK();
        };
    }

});
