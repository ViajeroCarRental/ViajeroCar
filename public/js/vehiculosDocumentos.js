document.addEventListener("DOMContentLoaded", () => {
    cargarTabla();
});

function cargarTabla() {
    fetch('/admin/vehiculos/documentos/list')
        .then(res => {
            if (!res.ok) throw new Error("Error en respuesta");
            return res.json();
        })
        .then(data => {
            const tbody = document.getElementById('tbodyDocs');
            tbody.innerHTML = '';

            data.forEach(v => {
                tbody.innerHTML += `
                    <tr>
                        <td>${v.marca ?? ''}</td>
                        <td>${v.modelo ?? ''}</td>
                        ${col(v, 'archivo_cartafactura')}
                        ${col(v, 'archivo_poliza')}
                        ${col(v, 'archivo_verificacion')}
                        ${col(v, 'archivo_tarjetacirculacion')}
                    </tr>
                `;
            });
        })
        .catch(err => {
            console.error("ERROR TABLA:", err);
        });
}

function col(v, tipo) {
    if (!v[tipo]) {
        return `
            <td>
                <span>No hay documentos</span><br>
                <button onclick="abrirModal(${v.id_vehiculo}, '${tipo}', false)">Subir</button>
            </td>
        `;
    }

    return `
        <td>
            <button onclick="verArchivo(${v.id_vehiculo}, '${tipo}')">Ver</button>
            <button onclick="abrirModal(${v.id_vehiculo}, '${tipo}', true)">Editar</button>
            <button onclick="eliminarArchivo(${v.id_vehiculo}, '${tipo}')">Eliminar</button>
        </td>
    `;
}

// ================= MODAL =================
function abrirModal(id, tipo, tieneArchivo) {
    document.getElementById('docVehiculo').value = id;
    document.getElementById('docTipo').value = tipo;

     document.getElementById('inputArchivo').value = '';

    document.getElementById('previewContainer').innerHTML = '';

    document.getElementById('modalArchivo').style.display = 'block';
}
function closeModal(id) {
    document.getElementById(id).style.display = 'none';
}

// ================= GUARDAR (🔥 CSRF FIX) =================
document.getElementById('btnGuardarArchivo').addEventListener('click', () => {

    const id = document.getElementById('docVehiculo').value;
    const tipo = document.getElementById('docTipo').value;
    const file = document.getElementById('inputArchivo').files[0];

    if (!file) {
        alert('Selecciona un archivo');
        return;
    }

    let formData = new FormData();
    formData.append('archivo', file);

    fetch(`/admin/vehiculos/documentos/${id}/${tipo}`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: formData
    })
    .then(res => {
        if (!res.ok) throw new Error("Error al guardar");
        return res.json();
    })
    .then(() => {
        closeModal('modalArchivo');
        cargarTabla();
    })
    .catch(err => console.error("ERROR GUARDAR:", err));
});

// ================= VER =================
function verArchivo(id, tipo) {
    window.open(`/admin/vehiculos/documentos/${id}/${tipo}`, '_blank');
}

// ================= ELIMINAR =================
function eliminarArchivo(id, tipo) {

    if (!confirm('¿Eliminar documento?')) return;

    fetch(`/admin/vehiculos/documentos/${id}/${tipo}`, {
        method: 'DELETE',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        }
    })
    .then(res => {
        if (!res.ok) throw new Error("Error al eliminar");
        return res.json();
    })
    .then(() => cargarTabla())
    .catch(err => console.error("ERROR ELIMINAR:", err));
}
