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

                    // 🔒 Guardamos el id_contrato real en la fila (oculto)
                    tr.dataset.id = r.id_contrato;

                    tr.innerHTML = `
        <td>
            <button class="btnToggle"
                style="font-size:20px;border:none;background:none;cursor:pointer">+</button>
        </td>

        <td>${r.numero_contrato ?? "—"}</td>
        <td>${r.fecha_fin ?? "—"}</td>
        <td>${r.categoria ?? "—"}</td>   <!-- ✅ categoría -->
        <td>${!r.tiene_dropoff ? "---" : r.delivery_ubicacion == 0 ? (r.delivery_direccion ?? "Sin dirección")
                            : `${r.ubic_estado ?? ""} - ${r.ubic_destino ?? ""}`}</td>
        <td>${r.hora_entrega ?? "—"}</td>
        <td>${r.estado ?? "—"}</td>
        <td class="text-center">
                ${r.metodo_pago === 'mostrador'
                            ? `
                        <i class="fas fa-money-bill-wave text-success" title="Pago en efectivo"></i>
                        ${r.status_pago === 'Pagado'
                                ? '<i class="fas fa-check text-primary ms-1" title="Pagado"></i>'
                                : ''
                            }
                    `
                            : ''
                        }
        </td>
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
        const tr = btn.closest("tr");

        // 🔑 Tomamos SIEMPRE el id_contrato guardado en la fila
        const id = tr.dataset.id;


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

            row.innerHTML = `
    <td colspan="9">
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
        ${d.nombre_completo_cliente ?? ""}
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
                            ${segurosPaqueteHTML
                    ? `<b>Paquete:</b><br>${segurosPaqueteHTML}<br><br>`
                    : ""
                }

                            <!-- Seguros individuales -->
                            ${segurosIndividualesHTML
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

                            ${comb.faltante > 0
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
    // Botón Extension.
    // ============================================================
    document.addEventListener("click", async function (e) {

        // Abrir calendario
        if (e.target.classList.contains("btnExtension")) {

            const id = e.target.dataset.id;
            const input = document.querySelector(`.inputExtension[data-id="${id}"]`);

            // bloquear hoy
            const hoy = new Date();
            hoy.setDate(hoy.getDate() + 1);

            const yyyy = hoy.getFullYear();
            const mm = String(hoy.getMonth() + 1).padStart(2, '0');
            const dd = String(hoy.getDate()).padStart(2, '0');

            input.min = `${yyyy}-${mm}-${dd}`;

            input.style.display = "block";
            input.showPicker(); // Chrome moderno
        }

    });


    document.addEventListener("change", async function (e) {

        if (e.target.classList.contains("inputExtension")) {

            const id = e.target.dataset.id;
            const fecha = e.target.value;

            if (!fecha) return;

            const res = await fetch(`/admin/contrato/${id}/extension`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: JSON.stringify({
                    fecha_fin: fecha
                })
            });

            const data = await res.json();

            if (data.ok) {
                alert("Fecha extendida correctamente");
                location.reload();
            } else {
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
