document.addEventListener('DOMContentLoaded', () => {
    loadData();
});

function loadData() {
    fetch('/admin/dropoff/data')
        .then(res => res.json())
        .then(data => renderTable(data));
}

function renderTable(data) {
    const tbody = document.getElementById('tbodyDropoff');
    tbody.innerHTML = '';

    data.categorias.forEach(cat => {

        const row = `
            <tr class="cat-row" onclick="toggle(${cat.id_categoria})">
                <td>+</td>
                <td>${cat.codigo}</td>
                <td>${cat.nombre}</td>
                <td>
                    <input type="number" value="${cat.costo_km}"
                        onchange="updateCosto(${cat.id_categoria}, this.value)">
                </td>
                <td>${cat.activo ? 'Activo' : 'Inactivo'}</td>
            </tr>

            <tr id="child-${cat.id_categoria}" class="child-row" style="display:none;">
                <td colspan="5">
                    <table class="table sub-table">
                        <thead>
                            <tr>
                                <th>Estado</th>
                                <th>Destino</th>
                                <th>KM</th>
                                <th>Costo</th>
                            </tr>
                        </thead>
                        <tbody>
                            ${renderUbicaciones(data.ubicaciones, cat)}
                        </tbody>
                    </table>
                </td>
            </tr>
        `;

        tbody.innerHTML += row;
    });
}

function renderUbicaciones(ubicaciones, cat) {
    return ubicaciones.map(u => {

        const costo = (u.km * cat.costo_km).toFixed(2);

        return `
            <tr>
                <td>${u.estado}</td>
                <td>${u.destino}</td>
                <td>
                    <input type="number" value="${u.km}"
                        onchange="updateKm(${u.id_ubicacion}, this.value)">
                </td>
                <td>$${costo}</td>
            </tr>
        `;
    }).join('');
}

function toggle(id) {
    const row = document.getElementById(`child-${id}`);
    row.style.display = row.style.display === 'none' ? '' : 'none';
}

function updateKm(id, km) {
    fetch('/admin/dropoff/update-km', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        },
        body: JSON.stringify({ id, km })
    }).then(() => loadData());
}

function updateCosto(id_categoria, costo_km) {
    fetch('/admin/dropoff/update-costo', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        },
        body: JSON.stringify({ id_categoria, costo_km })
    }).then(() => loadData());
}
