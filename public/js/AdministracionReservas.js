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




    // ============================================================
    // Expandir detalles del contrato
    // ============================================================
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
                                <button class="btn b-primary btnEditarContrato"
                                    data-id="${d.id_contrato}">
                                    EDITAR
                                </button>

                                <button class="btn b-red btnFinalizarContrato"
                                    data-id="${d.id_contrato}">
                                    FINALIZAR
                                </button>
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





    // ============================================================
    // Botón EDITAR
    // ============================================================
    document.addEventListener("click", (e) => {
        if (e.target.classList.contains("btnEditarContrato")) {
            const idContrato = e.target.dataset.id;
            window.location.href = `/admin/contrato/${idContrato}`;
        }
    });






    // ============================================================
    // Botón FINALIZAR
    // ============================================================
    document.addEventListener("click", async (e) => {
        if (!e.target.classList.contains("btnFinalizarContrato")) return;

        const idContrato = e.target.dataset.id;

        // 1) Consultar saldo pendiente
        const resp = await fetch(`/admin/contrato/${idContrato}/saldo`);
        const json = await resp.json();

        if (!json.ok) {
            alertify.error("Error consultando saldo.");
            return;
        }

        const saldo = json.saldo;

        // Si hay saldo pendiente → mostrar alerta
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

        // Si NO hay saldo pendiente → confirmar cierre
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
        const resp = await fetch(`/admin/contrato/${idContrato}/cerrar`, {
            method: "POST",
            headers: {
                "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').content,
                "Content-Type": "application/json"
            }
        });

        const json = await resp.json();

        if (!json.ok) {
            alertify.error(json.msg || "Error al finalizar.");
            return;
        }

        alertify.success("Contrato finalizado y correo enviado.");
    } catch (e) {
        console.error(e);
        alertify.error("Error al procesar finalización.");
    }
}

        });
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
