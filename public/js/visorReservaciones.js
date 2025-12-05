document.addEventListener("DOMContentLoaded", () => {

    const tbody = document.getElementById("tbody");
    const q = document.getElementById("q");
    const pp = document.getElementById("pp");
    const prev = document.getElementById("prev");
    const next = document.getElementById("next");
    const range = document.getElementById("range");

    let page = 1;
    let lastPage = 1;

    function loadData() {
        tbody.innerHTML = `
            <tr>
                <td colspan="8" style="text-align:center;color:#667085">Cargando…</td>
            </tr>`;

        fetch(`/api/visor-reservaciones?q=${q.value}&pp=${pp.value}&page=${page}`)
            .then(r => r.json())
            .then(json => {

                lastPage = json.last_page;

                // 📌 Rango visual
                const start = (page - 1) * pp.value + 1;
                const end = start + json.data.length - 1;
                range.textContent = `${start}–${end} de ${json.total}`;

                // 🔎 Pintar filas
                tbody.innerHTML = "";
                json.data.forEach(r => {

                    const tr = document.createElement("tr");
                    tr.innerHTML = `
                        <td></td>
                        <td>${r.codigo}</td>
                        <td>${r.fecha_fin}</td>
                        <td>${r.hora_entrega ?? '—'}</td>
                        <td>${r.dias}</td>
                        <td>${r.categoria ?? '—'}</td>
                        <td>${r.nombre ?? '—'}</td>
                        <td>${r.telefono ?? '—'}</td>
                    `;
                    tbody.appendChild(tr);
                });
            });
    }

    // 🔎 Búsqueda en tiempo real
    q.addEventListener("input", () => {
        page = 1;
        loadData();
    });

    // 📄 Cambiar cantidad
    pp.addEventListener("change", () => {
        page = 1;
        loadData();
    });

    // ◀ Anterior
    prev.addEventListener("click", () => {
        if (page > 1) {
            page--;
            loadData();
        }
    });

    // ▶ Siguiente
    next.addEventListener("click", () => {
        if (page < lastPage) {
            page++;
            loadData();
        }
    });

    // 🔥 Primera carga
    loadData();
});
