document.addEventListener('DOMContentLoaded', () => {
    loadData();
});

let globalData = {};

function loadData() {
    fetch('/admin/dropoff/data')
        .then(res => res.json())
        .then(data => {
            globalData = data;
            renderTable(data);
            renderTarifas(data);
            populateSelects(data);
        });
}

function showTab(tab) {
    document.querySelectorAll('.tab').forEach(t => t.classList.remove('active'));
    document.querySelectorAll('.admin-section').forEach(s => s.style.display = 'none');
    
    if (tab === 'delivery') {
        document.querySelector('.tab:nth-child(1)').classList.add('active');
        document.getElementById('tab-delivery').style.display = 'block';
    } else {
        document.querySelector('.tab:nth-child(2)').classList.add('active');
        document.getElementById('tab-dropoff').style.display = 'block';
    }
}

function renderTable(data) {
    const tbody = document.getElementById('tbodyDropoff');
    tbody.innerHTML = '';

    data.categorias.forEach(cat => {
        const row = `
            <tr class="cat-row" onclick="toggle(${cat.id_categoria})">
                <td style="cursor:pointer; font-weight:bold; color:#b22222;">+</td>
                <td>${cat.codigo}</td>
                <td>${cat.nombre}</td>
                <td>
                    <input type="number" class="form-control" style="width:100px" value="${cat.costo_km}"
                        onchange="updateCosto(${cat.id_categoria}, this.value)">
                </td>
                <td><span class="badge ${cat.activo ? 'bg-success' : 'bg-secondary'}">${cat.activo ? 'Activo' : 'Inactivo'}</span></td>
            </tr>

            <tr id="child-${cat.id_categoria}" class="child-row" style="display:none;">
                <td colspan="5">
                    <table class="table sub-table" style="background:#f9f9f9; margin-left:20px; width:95%;">
                        <thead>
                            <tr>
                                <th>Estado</th>
                                <th>Destino</th>
                                <th>KM</th>
                                <th>Costo Sugerido</th>
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
                    <input type="number" class="form-control" style="width:80px" value="${u.km}"
                        onchange="updateKm(${u.id_ubicacion}, this.value)">
                </td>
                <td style="font-weight:bold; color:#b22222;">$${costo}</td>
            </tr>
        `;
    }).join('');
}

function renderTarifas(data) {
    const tbody = document.getElementById('tbodyTarifas');
    tbody.innerHTML = '';

    data.tarifas_dropoff.forEach(t => {
        const origen = t.sucursal_origen || t.ciudad_origen || 'N/A';
        const destino = t.sucursal_destino || t.ciudad_destino || 'N/A';
        const row = `
            <tr>
                <td>${origen}</td>
                <td>${destino}</td>
                <td>${t.tipo_cobro}</td>
                <td>$${t.monto_base}</td>
                <td>${t.activo ? 'Activo' : 'Inactivo'}</td>
            </tr>
        `;
        tbody.innerHTML += row;
    });
}

function populateSelects(data) {
    const sOrigen = document.getElementById('t_origen');
    const sDestino = document.getElementById('t_destino');
    
    sOrigen.innerHTML = '<option value="">Seleccione Sucursal...</option>';
    sDestino.innerHTML = '<option value="">Seleccione Sucursal...</option>';

    data.sucursales.forEach(s => {
        const opt = `<option value="${s.id_sucursal}">${s.nombre}</option>`;
        sOrigen.innerHTML += opt;
        sDestino.innerHTML += opt;
    });
}

function toggle(id) {
    const row = document.getElementById(`child-${id}`);
    row.style.display = row.style.display === 'none' ? '' : 'none';
}

function saveUbicacion(e) {
    e.preventDefault();
    const data = {
        estado: document.getElementById('u_estado').value,
        destino: document.getElementById('u_destino').value,
        km: document.getElementById('u_km').value
    };

    fetch('/admin/dropoff/ubicacion', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        },
        body: JSON.stringify(data)
    }).then(res => res.json()).then(() => {
        alertify.success('Ubicación guardada');
        document.getElementById('formUbicacion').reset();
        loadData();
    });
}

function saveTarifa(e) {
    e.preventDefault();
    const data = {
        id_sucursal_origen: document.getElementById('t_origen').value,
        id_sucursal_destino: document.getElementById('t_destino').value,
        monto_base: document.getElementById('t_monto').value,
        tipo_cobro: 'fijo'
    };

    if (data.id_sucursal_origen === data.id_sucursal_destino) {
        alertify.error('Origen y destino no pueden ser iguales');
        return;
    }

    fetch('/admin/dropoff/tarifa', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        },
        body: JSON.stringify(data)
    }).then(res => res.json()).then(() => {
        alertify.success('Tarifa guardada');
        document.getElementById('formTarifa').reset();
        loadData();
    });
}

function updateKm(id, km) {
    fetch('/admin/dropoff/update-km', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        },
        body: JSON.stringify({ id, km })
    }).then(() => {
        alertify.success('KM actualizado');
        loadData();
    });
}

function updateCosto(id_categoria, costo_km) {
    fetch('/admin/dropoff/update-costo', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        },
        body: JSON.stringify({ id_categoria, costo_km })
    }).then(() => {
        alertify.success('Costo por KM actualizado');
        loadData();
    });
}
