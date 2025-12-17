document.addEventListener("DOMContentLoaded", () => {

    // ==========================
    // Elementos del DOM
    // ==========================
    const tbody = document.getElementById("tbody");
    const q = document.getElementById("q");
    const fini = document.getElementById("fini");
    const ffin = document.getElementById("ffin");
    const fstatus = document.getElementById("fstatus");
    const fpago = document.getElementById("fpago");
    const fsucursal = document.getElementById("fsucursal");
    const fvehiculo = document.getElementById("fvehiculo");

    const btnFiltrar = document.getElementById("btnFiltrar");
    const btnClear = document.getElementById("btnClear");

    const prev = document.getElementById("prev");
    const next = document.getElementById("next");
    const range = document.getElementById("range");
    const pp = document.getElementById("pp");

    // Resumen
    const sumCount = document.getElementById("sumCount");
    const sumTotal = document.getElementById("sumTotal");
    const sumPagado = document.getElementById("sumPagado");
    const sumSaldo = document.getElementById("sumSaldo");

    let page = 1;
    let lastPage = 1;

    // ==========================
    // Cargar datos
    // ==========================
    function loadData() {

        tbody.innerHTML = `
            <tr>
              <td colspan="11" style="text-align:center;color:#667085">Cargando…</td>
            </tr>
        `;

        const params = new URLSearchParams({
            q: q.value,
            fini: fini.value,
            ffin: ffin.value,
            pp: pp.value,
            page: page
        });

        fetch(`/api/historial?${params}`)
            .then(r => r.json())
            .then(json => {

                // Paginas
                lastPage = json.last_page;

                const start = (page - 1) * Number(pp.value) + 1;
                const end = start + json.data.length - 1;
                range.textContent = `${start}–${end} de ${json.total}`;

                // Resumen superior
                let t_rentas = json.total;
                let t_total = 0;
                let t_pagado = 0;
                let t_saldo = 0;

                json.data.forEach(r => {
                    t_total += Number(r.total) || 0;
                    t_pagado += Number(r.pagado) || 0;
                    t_saldo += Number(r.saldo) || 0;
                });

                sumCount.textContent = t_rentas;
                sumTotal.textContent = "$" + t_total.toFixed(2);
                sumPagado.textContent = "$" + t_pagado.toFixed(2);
                sumSaldo.textContent = "$" + t_saldo.toFixed(2);

                // Dibujar filas
                tbody.innerHTML = "";
                json.data.forEach(r => {

                    // Convertir a número seguro
                    let total  = Number(r.total);
                    let pagado = Number(r.pagado);
                    let saldo  = Number(r.saldo);

                    total  = isNaN(total)  ? null : total;
                    pagado = isNaN(pagado) ? null : pagado;
                    saldo  = isNaN(saldo)  ? null : saldo;

                    const tr = document.createElement("tr");

                    tr.innerHTML = `
                        <td>${r.folio}</td>
                        <td>${r.fecha ?? "—"}</td>
                        <td>${r.cliente ?? "—"}</td>
                        <td>${r.vehiculo ?? "—"}</td>
                        <td>${r.dias ?? "—"}</td>
                        <td>${r.sucursal ?? "—"}</td>
                        <td>${r.estatus ?? "—"}</td>

                        <td>${total !== null ? "$" + total.toFixed(2) : "—"}</td>
                        <td>${pagado !== null ? "$" + pagado.toFixed(2) : "—"}</td>
                        <td>${saldo !== null ? "$" + saldo.toFixed(2) : "—"}</td>

                    `;

                    tbody.appendChild(tr);
                });
            });
    }

    // ==========================
    // Eventos
    // ==========================

    btnFiltrar.addEventListener("click", () => {
        page = 1;
        loadData();
    });

    btnClear.addEventListener("click", () => {
        q.value = "";
        fini.value = "";
        ffin.value = "";
        fstatus.value = "";
        fpago.value = "";
        fsucursal.value = "";
        fvehiculo.value = "";
        page = 1;
        loadData();
    });

    q.addEventListener("input", () => {
        page = 1;
        loadData();
    });

    pp.addEventListener("change", () => {
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

    // Cargar la primera vez
    loadData();
});
