const CSRF = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

document.addEventListener("DOMContentLoaded", () => {
    cargarVehiculos();

    // Listeners del modal
    document.getElementById("modalClose").addEventListener("click", cerrarModal);
    document.getElementById("modalCancel").addEventListener("click", cerrarModal);
    document.getElementById("btnGuardarEstatus").addEventListener("click", guardarEstatus);
});

async function cargarVehiculos() {
    const tbody = document.getElementById("tbodyFlotilla");
    const listUrl = document.querySelector(".flotilla-container").dataset.listUrl;

    try {
        const res = await fetch(listUrl);
        const data = await res.json();

        if (!data.length) {
            // Ajustado a colspan="13" para que coincida con tus columnas
            tbody.innerHTML = `<tr><td colspan="13" class="empty-row">No hay vehículos registrados.</td></tr>`;
            return;
        }

        tbody.innerHTML = data.map(v => {
            const estatusRaw = v.estatus ?? "";
            const estatusKey = estatusRaw
                .toLowerCase()
                .normalize("NFD").replace(/[\u0300-\u036f]/g, "");

            let clase = "desconocido";
            if (estatusKey === "disponible")         clase = "disponible";
            else if (estatusKey === "mantenimiento") clase = "mantenimiento";
            else if (estatusKey === "rentado")       clase = "rentado";
            else if (estatusKey === "baja")          clase = "baja";

            const estatusLabel = estatusRaw || "Sin estatus";

            return `
                <tr data-id="${v.id_vehiculo}"
                    data-placa="${v.placa ?? '-'}"
                    data-categoria="${v.categoria ?? '-'}"
                    data-modelo="${v.modelo ?? '-'}">
                    
                    <td onclick="abrirModal(this)" style="cursor: pointer;" title="Clic para cambiar estatus">
                        <span class="status-pill">
                            <span class="status-dot ${clase}"></span>
                            ${estatusLabel}
                        </span>
                    </td>
                    
                    <td>${v.placa ?? '-'}</td>
                    <td>${v.categoria ?? '-'}</td>
                    <td>${v.tamano ?? '-'}</td>
                    <td>${v.modelo ?? '-'}</td>
                    <td>${v.transmision ?? '-'}</td>
                    <td>${v.color ?? '-'}</td>
                    <td>${v.gasolina_fraccion ?? 0}/16</td>
                    <td>${v.gasolina_actual ?? '-'}</td>
                    <td>${v.kilometraje ?? '-'}</td>
                    <td>${v.vigencia_verificacion ?? '-'}</td>
                    <td>${v.intervalo_km ?? '-'}</td>
                    <td>${v.fin_vigencia_poliza ?? '-'}</td>
                </tr>
            `;
        }).join('');

    } catch (err) {
        console.error("Error cargando flotilla:", err);
        // Ajustado a colspan="13"
        tbody.innerHTML = `<tr><td colspan="13" class="empty-row">Error al cargar los datos.</td></tr>`;
    }
}

// La función abrirModal funciona perfectamente igual, ya que busca el <tr> más cercano.
function abrirModal(btn) {
    const tr = btn.closest("tr");

    document.getElementById("vehiculoId").value          = tr.dataset.id;
    document.getElementById("infoPlaca").textContent     = tr.dataset.placa;
    document.getElementById("infoCategoria").textContent = tr.dataset.categoria;
    document.getElementById("infoModelo").textContent    = tr.dataset.modelo;

    document.getElementById("modalEstatus").style.display = "flex";
}

function cerrarModal() {
    document.getElementById("modalEstatus").style.display = "none";
}

async function guardarEstatus() {
    const id         = document.getElementById("vehiculoId").value;
    const idEstatus  = document.getElementById("selectEstatus").value;

    if (!id || !idEstatus) {
        alert("Faltan datos.");
        return;
    }

    try {
        const res = await fetch(`/ventas/flotilla-status/${id}`, {
            method: "PUT",
            headers: {
                "Content-Type": "application/json",
                "Accept": "application/json",
                "X-CSRF-TOKEN": CSRF
            },
            body: JSON.stringify({ id_estatus: idEstatus })
        });

        const data = await res.json();

        if (data.ok) {
            cerrarModal();
            cargarVehiculos();
        } else {
            alert(data.message ?? "No se pudo actualizar.");
        }
    } catch (err) {
        console.error("Error guardando estatus:", err);
        alert("Error de conexión.");
    }
}