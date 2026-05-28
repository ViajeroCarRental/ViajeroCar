@extends('layouts.Ventas')

@section('Titulo', 'Estado de Flotilla')

@section('css')
<style>
    .flotilla-container { padding: 20px; }
    .flotilla-container h1 { margin: 0 0 20px; font-size: 24px; }

    .table-wrap {
        background: #fff;
        border-radius: 10px;
        box-shadow: 0 1px 3px rgba(0,0,0,0.08);
        overflow-x: auto;
    }

    table.flotilla-table {
        width: 100%;
        border-collapse: collapse;
        font-size: 14px;
    }

    .flotilla-table thead {
        background: #f8f9fa;
    }

    .flotilla-table th,
    .flotilla-table td {
        padding: 10px 12px;
        text-align: left;
        border-bottom: 1px solid #eaecef;
        white-space: nowrap;
    }

    .flotilla-table th {
        font-weight: 600;
        color: #374151;
        font-size: 12px;
        text-transform: uppercase;
        letter-spacing: 0.4px;
    }

    .flotilla-table tbody tr:hover {
        background: #f9fafb;
    }

    /* === Indicador de estatus con punto de color === */
    .status-pill {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        font-weight: 600;
        font-size: 13px;
    }

    .status-dot {
        width: 10px;
        height: 10px;
        border-radius: 50%;
        display: inline-block;
        flex-shrink: 0;
    }

    .status-dot.disponible    { background: #22c55e; box-shadow: 0 0 0 3px rgba(34,197,94,0.18); }
    .status-dot.mantenimiento { background: #eab308; box-shadow: 0 0 0 3px rgba(234,179,8,0.18); }
    .status-dot.rentado       { background: #ef4444; box-shadow: 0 0 0 3px rgba(239,68,68,0.18); }
    .status-dot.baja          { background: #9ca3af; box-shadow: 0 0 0 3px rgba(156,163,175,0.18); }
    .status-dot.desconocido   { background: #d1d5db; }

    .empty-row {
        text-align: center;
        padding: 30px;
        color: #6b7280;
    }

    .modal-overlay {
    position: fixed;
    inset: 0;
    background: rgba(0,0,0,0.45);
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 1000;
}

.modal-card {
    background: #fff;
    border-radius: 12px;
    width: 92%;
    max-width: 420px;
    box-shadow: 0 10px 40px rgba(0,0,0,0.25);
    overflow: hidden;
}

.modal-head {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 16px 20px;
    border-bottom: 1px solid #eaecef;
    background: #f8f9fa;
}

.modal-head h3 { margin: 0; font-size: 16px; }

.modal-close {
    background: none;
    border: none;
    font-size: 18px;
    cursor: pointer;
    color: #6b7280;
}

.modal-body {
    padding: 20px;
}

.info-row {
    display: flex;
    justify-content: space-between;
    padding: 6px 0;
    font-size: 14px;
    color: #374151;
    border-bottom: 1px dashed #e5e7eb;
}
.info-row:last-of-type { border-bottom: none; margin-bottom: 12px; }

.label {
    display: block;
    margin: 14px 0 6px;
    font-size: 13px;
    font-weight: 600;
    color: #374151;
}

.select-estatus {
    width: 100%;
    padding: 9px 12px;
    border: 1px solid #d1d5db;
    border-radius: 8px;
    font-size: 14px;
    background: #fff;
}

.modal-actions {
    display: flex;
    justify-content: flex-end;
    gap: 8px;
    padding: 14px 20px;
    background: #f8f9fa;
    border-top: 1px solid #eaecef;
}

.btn-primary, .btn-secondary {
    padding: 8px 16px;
    border-radius: 8px;
    border: none;
    cursor: pointer;
    font-size: 14px;
    font-weight: 600;
}

.btn-primary {
    background: #2563eb;
    color: #fff;
}
.btn-primary:hover { background: #1d4ed8; }

.btn-secondary {
    background: #e5e7eb;
    color: #374151;
}
.btn-secondary:hover { background: #d1d5db; }

.btn-edit-status {
    padding: 5px 10px;
    background: #2563eb;
    color: #fff;
    border: none;
    border-radius: 6px;
    cursor: pointer;
    font-size: 12px;
    font-weight: 600;
}
.btn-edit-status:hover { background: #1d4ed8; }
</style>
@endsection

@section('contenido')
<meta name="csrf-token" content="{{ csrf_token() }}">
<main class="flotilla-container" data-list-url="{{ url('/ventas/flotilla-status/list') }}">

    <h1>Status de Flotilla</h1>

    <div class="table-wrap">
        <table class="flotilla-table">
            <thead>
                <tr>
                    <th>Placas</th>
                    <th>Categoría</th>
                    <th>Tamaño</th>
                    <th>Modelo</th>
                    <th>Transmisión</th>
                    <th>Color</th>
                    <th>Gasolina</th>
                    <th>Litros</th>
                    <th>KM</th>
                    <th>Verificación</th>
                    <th>Mantenimiento</th>
                    <th>Seguro</th>
                    <th>Estatus</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody id="tbodyFlotilla">
                <tr><td colspan="13" class="empty-row">Cargando...</td></tr>
            </tbody>
        </table>
    </div>

    <!-- ===== MODAL CAMBIAR ESTATUS ===== -->
<div class="modal-overlay" id="modalEstatus" style="display:none;">
    <div class="modal-card">

        <header class="modal-head">
            <h3>Cambiar estatus del vehículo</h3>
            <button type="button" class="modal-close" id="modalClose">✕</button>
        </header>

        <div class="modal-body">
            <div class="info-row"><span>Placas:</span> <strong id="infoPlaca">—</strong></div>
            <div class="info-row"><span>Categoría:</span> <strong id="infoCategoria">—</strong></div>
            <div class="info-row"><span>Modelo:</span> <strong id="infoModelo">—</strong></div>

            <label class="label">Nuevo estatus</label>
            <select id="selectEstatus" class="select-estatus">
                @foreach($estatusList as $est)
                    <option value="{{ $est->id_estatus }}">{{ $est->nombre }}</option>
                @endforeach
            </select>

            <input type="hidden" id="vehiculoId">
        </div>

        <footer class="modal-actions">
            <button type="button" class="btn-secondary" id="modalCancel">Cancelar</button>
            <button type="button" class="btn-primary" id="btnGuardarEstatus">Guardar</button>
        </footer>

    </div>
</div>

</main>
@endsection

@section('js')
<script>
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
            tbody.innerHTML = `<tr><td colspan="14" class="empty-row">No hay vehículos registrados.</td></tr>`;
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
                    <td>
                        <span class="status-pill">
                            <span class="status-dot ${clase}"></span>
                            ${estatusLabel}
                        </span>
                    </td>
                    <td>
                        <button type="button" class="btn-edit-status" onclick="abrirModal(this)">
                            Cambiar estatus
                        </button>
                    </td>
                </tr>
            `;
        }).join('');

    } catch (err) {
        console.error("Error cargando flotilla:", err);
        tbody.innerHTML = `<tr><td colspan="14" class="empty-row">Error al cargar los datos.</td></tr>`;
    }
}

function abrirModal(btn) {
    const tr = btn.closest("tr");

    document.getElementById("vehiculoId").value     = tr.dataset.id;
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
</script>
@endsection
