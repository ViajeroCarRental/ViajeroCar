document.addEventListener("DOMContentLoaded", () => {

    const tbody = document.getElementById("tbody");
    const search = document.getElementById("txtSearch");
    const size = document.getElementById("selSize");
    const prev = document.getElementById("prev");
    const next = document.getElementById("next");
    const pgInfo = document.getElementById("pgInfo");

    let page = 1;
    let lastPage = 1;

    // ============================================================
    // Cargar datos principales
    // ============================================================
    function loadData() {

        tbody.innerHTML = `
            <tr><td colspan="9" style="text-align:center;padding:20px">Cargando…</td></tr>
        `;

        const params = new URLSearchParams({
            q: search.value,
            size: size.value,
            page: page
        });

        fetch(`/api/contratos-abiertos?${params.toString()}`)
            .then(r => r.json())
            .then(json => {

                lastPage = json.last_page;
                pgInfo.textContent = `Página ${page} de ${lastPage}`;

                tbody.innerHTML = "";

                json.data.forEach(r => {

                    const tr = document.createElement("tr");

                    tr.innerHTML = `
                        <td>
                            <button class="btnToggle"
                                data-id="${r.id_contrato}"
                                style="font-size:20px;border:none;background:none;cursor:pointer">+</button>
                        </td>

                        <td>${r.numero_contrato ?? "—"}</td>
                        <td>${r.fecha_fin ?? "—"}</td>
                        <td>${r.hora_entrega ?? "—"}</td>
                        <td>${r.nombre ?? "—"}</td>
                        <td>—</td>
                        <td>${r.email ?? "—"}</td>
                        <td>${r.estado ?? "—"}</td>
                        <td></td>
                    `;

                    tbody.appendChild(tr);
                });
            })
            .catch(err => {
                console.error("❌ ERROR FETCH:", err);
                tbody.innerHTML = `
                    <tr><td colspan="9" style="text-align:center;padding:20px;color:red">
                        Error al cargar datos
                    </td></tr>`;
            });
    }



document.addEventListener("click", async (e) => {
    if (!e.target.classList.contains("btnToggle")) return;

    const btn = e.target;
    const id = btn.dataset.id;
    const tr = btn.closest("tr");

    const nextRow = tr.nextElementSibling;
    if (nextRow && nextRow.classList.contains("detail")) {
        nextRow.remove();
        btn.textContent = "+";
        return;
    }

    try {

        const res = await fetch(`/api/contratos-abiertos/${id}`);
        if (!res.ok) throw new Error(`HTTP ${res.status}`);
console.log("Respuesta fetch:", res);
        const json = await res.json();
        if (!json.ok) throw new Error("Respuesta ok = false");

        const d = json.data;

        const row = document.createElement("tr");
        row.classList.add("detail");

        row.innerHTML = `
            <td colspan="9">
                <div class="card">

                    <div class="card-hd">
                        <div class="card-title">Reserva · ${d.clave}</div>
                        <div class="card-meta">
                            <span class="badge st-pend">${d.estado}</span>
                            <span class="badge">Web</span>
                        </div>
                    </div>

                    <div class="card-bd">
                        <div class="block">
                            <div class="kv">
                                <div class="k">Contacto</div>
                                <div class="v">${d.pais ?? ""} · ${d.telefono ?? ""}</div>
                            </div>

                            <div class="kv">
                                <div class="k">Vehículo</div>
                                <div class="v">${d.categoria ?? ""} · ${d.marca ?? ""} ${d.modelo ?? ""}</div>
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
                                        ${d.entrega_fecha} · ${d.entrega_hora} HRS
                                    </div>
                                </div>
                            </div>

                            <div class="tl-item">
                                <div class="tl-dot" style="background:#0EA5E9"></div>
                                <div class="tl-body">
                                    <div class="tl-title">Devolución</div>
                                    <div class="tl-sub">
                                        ${d.dev_lugar}<br>
                                        ${d.dev_fecha} · ${d.dev_hora} HRS
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
                        </div>

                        <div style="display:flex; gap:10px;">
                            <button class="btn b-primary">EDITAR</button>
                            <button class="btn b-red">FINALIZAR</button>
                        </div>
                    </div>

                </div>
            </td>
        `;

        tr.insertAdjacentElement("afterend", row);
        btn.textContent = "−";
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

});
