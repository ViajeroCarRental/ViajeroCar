@extends('layouts.Admin')
@section('Titulo', 'Administración de Oficinas')

@section('css')
<link rel="stylesheet" href="{{ asset('css/Categorias.css') }}">
<style>
    /* Estilos extra para mantener la estética de tu sistema */
    .btn-action-top {
        padding: 10px 20px;
        border: none;
        border-radius: 5px;
        cursor: pointer;
        font-weight: bold;
        transition: 0.2s;
    }
    .btn-red {
        background: #dc2626;
        color: white;
    }
    .btn-red:hover { background: #b91c1c; }

    .btn-blue {
        background: #0284c7;
        color: white;
    }
    .btn-blue:hover { background: #0369a1; }

    .badge-estado {
        background: #f1f5f9;
        color: #475569;
        padding: 4px 10px;
        border-radius: 4px;
        font-size: 12px;
        font-weight: bold;
        border: 1px solid #cbd5e1;
    }

    /* Botones de acción en la tabla */
    .btn-mini {
        padding: 6px 10px;
        border: none;
        border-radius: 6px;
        cursor: pointer;
        font-size: 12px;
        font-weight: bold;
        margin: 0 2px;
        transition: 0.15s;
    }
    .btn-edit { background: #fef3c7; color: #92400e; }
    .btn-edit:hover { background: #fde68a; }
    .btn-del { background: #fee2e2; color: #991b1b; }
    .btn-del:hover { background: #fecaca; }

    /* Diseño mejorado para el grupo de horario */
    .schedule-container {
        display: flex;
        align-items: center;
        background: #fff;
        border: 1px solid #cbd5e1;
        border-radius: 10px;
        padding: 4px;
        gap: 2px;
        transition: 0.2s;
        margin-top: 4px;
    }
    .schedule-container:focus-within {
        border-color: #0284c7;
        box-shadow: 0 0 0 3px rgba(2, 132, 199, 0.1);
    }
    .schedule-container select,
    .schedule-container input {
        border: none !important;
        background: transparent !important;
        padding: 8px 8px !important;
        margin: 0 !important;
        font-size: 13px !important;
        box-shadow: none !important;
        outline: none !important;
        height: auto !important;
    }
    .schedule-sep {
        color: #94a3b8;
        font-weight: 800;
        font-size: 11px;
        padding: 0 4px;
    }
    .schedule-divider {
        width: 1px;
        height: 24px;
        background: #e2e8f0;
        margin: 0 4px;
    }

    /* Pill de horario en la tabla */
    .horario-pill {
        display: inline-flex;
        align-items: center;
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        border-radius: 20px;
        padding: 2px 4px;
        font-size: 11px;
        gap: 6px;
    }
    .horario-dias {
        background: #0284c7;
        color: white;
        padding: 2px 8px;
        border-radius: 15px;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: 0.3px;
    }
    .horario-horas {
        color: #475569;
        font-weight: 700;
        padding-right: 4px;
    }
</style>
@endsection

@section('contenido')
<main class="main">

  <div class="head" style="display: flex; justify-content: space-between; align-items: center;">
    <h1 class="h1">Gestión de Oficinas</h1>

    <div style="display: flex; gap: 10px;">
        <button onclick="document.getElementById('modalCalculadora').showModal()" class="btn-action-top btn-blue">
            🧮 Calculadora de Tarifas
        </button>
        <button onclick="document.getElementById('modalNueva').showModal()" class="btn-action-top btn-red">
            + Nueva Oficina
        </button>
    </div>
  </div>

  <section class="card">
    <table class="table">
      <thead>
        <tr>
          <th style="width: 12%;">Estado</th>
          <th style="width: 22%;">SUCURSAL DE RETIRO</th>
          <th style="width: 26%;">Dirección</th>
          <th style="width: 13%;">Teléfono</th>
          <th style="width: 15%;">Horarios</th>
          <th style="width: 12%; text-align:center;">Acciones</th>
        </tr>
      </thead>
      <tbody>
        @forelse($sucursales as $sucursal)
            @php
                $horarioData = is_string($sucursal->horario_json) ? json_decode($sucursal->horario_json) : null;
                $textoHorario = $horarioData->horario ?? 'No definido';
            @endphp
            <tr>
                <td><span class="badge-estado">{{ $sucursal->ciudad_estado ?? 'N/A' }}</span></td>
                <td><strong>{{ $sucursal->nombre }}</strong></td>
                <td><span style="color: #64748b; font-size: 13px;">{{ $sucursal->direccion }}</span></td>
                <td>{{ $sucursal->telefono ?? 'N/A' }}</td>
                <td>
                    @php
                        $parts = explode(' ', $textoHorario);
                    @endphp
                    @if(count($parts) >= 4)
                        <div class="horario-pill">
                            <span class="horario-dias">{{ $parts[0] }} A {{ $parts[2] }}</span>
                            <span class="horario-horas">{{ implode(' ', array_slice($parts, 3)) }}</span>
                        </div>
                    @else
                        <span style="font-size:12px; font-weight:700; color:#475569;">{{ $textoHorario }}</span>
                    @endif
                </td>
                <td style="text-align:center; white-space:nowrap;">
                    <button type="button" class="btn-mini btn-edit"
                        onclick='abrirEditar(@json($sucursal), @json($textoHorario))'>✏️ Editar</button>
                    <button type="button" class="btn-mini btn-del"
                        onclick="confirmarEliminar({{ $sucursal->id_sucursal }}, @js($sucursal->nombre))">🗑️</button>
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="6" style="text-align: center; padding: 30px; color: #94a3b8;">
                    No hay sucursales registradas.
                </td>
            </tr>
        @endforelse
      </tbody>
    </table>
  </section>

</main>

{{-- =========================
    MODAL: NUEVA OFICINA
========================= --}}
<dialog id="modalNueva" class="modal">
  <form method="POST" action="{{ route('oficinas.store') }}" class="modal-box">
    @csrf

    <div class="modal-head">
      <h2>Agregar Nueva Oficina</h2>
      <button type="button" class="x" onclick="document.getElementById('modalNueva').close()">✕</button>
    </div>

    <label class="label">Ciudad / Estado</label>
    <select name="id_ciudad" class="input" required>
        <option value="">-- Seleccione una ciudad --</option>
        @foreach($ciudades as $ciudad)
            <option value="{{ $ciudad->id_ciudad }}">{{ $ciudad->nombre }}, {{ $ciudad->estado }}</option>
        @endforeach
    </select>

    <label class="label">Nombre (SUCURSAL DE RETIRO)</label>
    <input type="text" name="nombre" class="input" placeholder="Ej: Querétaro Aeropuerto" required>

    <label class="label">Dirección Completa</label>
    <textarea name="direccion" class="input" rows="2" placeholder="Calle, Número, Colonia..." required></textarea>

    <div style="margin-top: 10px;">
        <label class="label">Teléfono</label>
        <input type="tel" name="telefono" class="input only-numbers" placeholder="Ej: 4421234567" style="width: 100%;">
    </div>

    <div style="margin-top: 15px;">
        <label class="label">Horario de Atención</label>
        <div class="schedule-container">
            <select id="nueva_dia_inicio" required style="flex: 1;">
                <option value="LUNES">LUNES</option>
                <option value="MARTES">MARTES</option>
                <option value="MIÉRCOLES">MIÉRCOLES</option>
                <option value="JUEVES">JUEVES</option>
                <option value="VIERNES">VIERNES</option>
                <option value="SÁBADO">SÁBADO</option>
                <option value="DOMINGO">DOMINGO</option>
            </select>
            <span class="schedule-sep">A</span>
            <select id="nueva_dia_fin" required style="flex: 1;">
                <option value="VIERNES">VIERNES</option>
                <option value="LUNES">LUNES</option>
                <option value="MARTES">MARTES</option>
                <option value="MIÉRCOLES">MIÉRCOLES</option>
                <option value="JUEVES">JUEVES</option>
                <option value="SÁBADO">SÁBADO</option>
                <option value="DOMINGO">DOMINGO</option>
            </select>
            <div class="schedule-divider"></div>
            <input type="text" id="nueva_horas" style="flex: 1.5;" placeholder="08:00 - 20:00" required>
        </div>
        <input type="hidden" name="horario" id="nueva_horario_hidden">
    </div>

    <div class="modal-actions" style="margin-top: 20px;">
      <button class="btn-add" type="submit">Guardar Oficina</button>
      <button class="btn-ghost" type="button" onclick="document.getElementById('modalNueva').close()">Cancelar</button>
    </div>
  </form>
</dialog>

{{-- =========================
    MODAL: EDITAR OFICINA
========================= --}}
<dialog id="modalEditar" class="modal">
  <form method="POST" id="formEditar" class="modal-box">
    @csrf
    @method('PUT')

    <div class="modal-head">
      <h2>Editar Oficina</h2>
      <button type="button" class="x" onclick="document.getElementById('modalEditar').close()">✕</button>
    </div>

    <label class="label">Ciudad / Estado</label>
    <select name="id_ciudad" id="edit_id_ciudad" class="input" required>
        <option value="">-- Seleccione una ciudad --</option>
        @foreach($ciudades as $ciudad)
            <option value="{{ $ciudad->id_ciudad }}">{{ $ciudad->nombre }}, {{ $ciudad->estado }}</option>
        @endforeach
    </select>

    <label class="label">Nombre (SUCURSAL DE RETIRO)</label>
    <input type="text" name="nombre" id="edit_nombre" class="input" required>

    <label class="label">Dirección Completa</label>
    <textarea name="direccion" id="edit_direccion" class="input" rows="2" required></textarea>

    <div style="margin-top: 10px;">
        <label class="label">Teléfono</label>
        <input type="tel" name="telefono" id="edit_telefono" class="input only-numbers" style="width: 100%;">
    </div>

    <div style="margin-top: 15px;">
        <label class="label">Horario de Atención</label>
        <div class="schedule-container">
            <select id="edit_dia_inicio" required style="flex: 1;">
                <option value="LUNES">LUNES</option>
                <option value="MARTES">MARTES</option>
                <option value="MIÉRCOLES">MIÉRCOLES</option>
                <option value="JUEVES">JUEVES</option>
                <option value="VIERNES">VIERNES</option>
                <option value="SÁBADO">SÁBADO</option>
                <option value="DOMINGO">DOMINGO</option>
            </select>
            <span class="schedule-sep">A</span>
            <select id="edit_dia_fin" required style="flex: 1;">
                <option value="VIERNES">VIERNES</option>
                <option value="LUNES">LUNES</option>
                <option value="MARTES">MARTES</option>
                <option value="MIÉRCOLES">MIÉRCOLES</option>
                <option value="JUEVES">JUEVES</option>
                <option value="SÁBADO">SÁBADO</option>
                <option value="DOMINGO">DOMINGO</option>
            </select>
            <div class="schedule-divider"></div>
            <input type="text" id="edit_horas" style="flex: 1.5;" placeholder="08:00 - 18:00" required>
        </div>
        <input type="hidden" name="horario" id="edit_horario_hidden">
    </div>

    <div class="modal-actions" style="margin-top: 20px;">
      <button class="btn-add" type="submit">Actualizar Oficina</button>
      <button class="btn-ghost" type="button" onclick="document.getElementById('modalEditar').close()">Cancelar</button>
    </div>
  </form>
</dialog>

{{-- Formulario oculto para eliminar --}}
<form id="formEliminar" method="POST" style="display:none;">
    @csrf
    @method('DELETE')
</form>

{{-- =========================
    MODAL: CALCULADORA
========================= --}}
<dialog id="modalCalculadora" class="modal">
  <form class="modal-box" onsubmit="event.preventDefault();">
    <div class="modal-head">
      <h2>Calculadora de Tarifas</h2>
      <button type="button" class="x" onclick="document.getElementById('modalCalculadora').close()">✕</button>
    </div>

    <label class="label">Tipo de Servicio</label>
    <select id="calc_tipo" class="input" onchange="toggleCalcFields()">
        <option value="delivery">Delivery (Punto Especial)</option>
        <option value="dropoff">Dropoff (Entre Sucursales)</option>
    </select>

    <label class="label">Categoría de Auto</label>
    <select id="calc_categoria" class="input">
        @foreach($categorias as $cat)
            <option value="{{ $cat->id_categoria }}">{{ $cat->nombre }} ({{ $cat->codigo ?? 'N/A' }})</option>
        @endforeach
    </select>

    <div id="div_delivery">
        <label class="label">Destino Especial (Delivery)</label>
        <select id="calc_ubicacion" class="input">
            @foreach($ubicaciones as $u)
                <option value="{{ $u->id_ubicacion }}">{{ $u->destino }} ({{ $u->km }} km)</option>
            @endforeach
        </select>
    </div>

    <div id="div_dropoff" style="display:none;">
        <label class="label">Sucursal Origen</label>
        <select id="calc_origen" class="input">
            @foreach($sucursales as $s)
                <option value="{{ $s->id_sucursal }}">{{ $s->nombre }}</option>
            @endforeach
        </select>

        <label class="label">Sucursal Destino</label>
        <select id="calc_destino" class="input">
            @foreach($sucursales as $s)
                <option value="{{ $s->id_sucursal }}">{{ $s->nombre }}</option>
            @endforeach
        </select>
    </div>

    <div class="modal-actions" style="margin-top: 15px;">
        <button type="button" class="btn-add" style="background: #0284c7; width: 100%;" onclick="performCalculation()">Calcular Costo</button>
    </div>

    <div id="calc_result" style="display:none; background:#f1f5f9; border-radius: 8px; padding: 15px; text-align: center; margin-top: 15px;">
        <h4 id="total_result" style="color:#b22222; font-size: 28px; margin: 0; font-weight: 800;">$0.00</h4>
        <p id="detail_result" style="margin: 5px 0 0 0; font-size: 13px; font-weight: bold; color: #475569;"></p>
    </div>

  </form>
</dialog>

@endsection

@section('js')
{{-- SweetAlert2 CDN --}}
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    // ==========================
    // PARCHE GLOBAL: cerrar dialogs antes de cualquier Swal
    // (los <dialog> showModal() viven en la top-layer, por encima de Swal)
    // ==========================
    const _swalFire = Swal.fire.bind(Swal);
    Swal.fire = function (...args) {
        document.querySelectorAll('dialog[open]').forEach(function (d) { d.close(); });
        return _swalFire(...args);
    };

    // ==========================
    // EDITAR OFICINA
    // ==========================
    function abrirEditar(sucursal, horario) {
        document.getElementById('edit_id_ciudad').value = sucursal.id_ciudad;
        document.getElementById('edit_nombre').value = sucursal.nombre;
        document.getElementById('edit_direccion').value = sucursal.direccion;
        document.getElementById('edit_telefono').value = sucursal.telefono ?? '';

        // Intentar parsear el horario "DIA A DIA HORA - HORA"
        if (horario) {
            const parts = horario.split(' ');
            if (parts.length >= 5) {
                document.getElementById('edit_dia_inicio').value = parts[0];
                document.getElementById('edit_dia_fin').value    = parts[2];
                document.getElementById('edit_horas').value      = parts.slice(3).join(' ');
            } else {
                document.getElementById('edit_horas').value = horario;
            }
        }

        const base = "{{ url('oficinas') }}";
        document.getElementById('formEditar').action = `${base}/${sucursal.id_sucursal}`;
        document.getElementById('modalEditar').showModal();
    }

    // Unir campos de horario antes de enviar
    function vincularHorario(prefijo) {
        const diaI = document.getElementById(`${prefijo}_dia_inicio`);
        const diaF = document.getElementById(`${prefijo}_dia_fin`);
        const hora = document.getElementById(`${prefijo}_horas`);
        const hidden = document.getElementById(`${prefijo}_horario_hidden`);

        const actualizar = () => {
            hidden.value = `${diaI.value} A ${diaF.value} ${hora.value}`.toUpperCase();
        };

        diaI.addEventListener('change', actualizar);
        diaF.addEventListener('change', actualizar);
        hora.addEventListener('input', actualizar);
        actualizar();
    }

    vincularHorario('nueva');
    vincularHorario('edit');

    // ==========================
    // CONFIRMAR EDITAR (SweetAlert2)
    // ==========================
    (function () {
        const formEditar = document.getElementById('formEditar');
        if (!formEditar) return;

        formEditar.addEventListener('submit', function (e) {
            e.preventDefault();

            // Asegura que el horario oculto esté actualizado antes de confirmar
            const diaI = document.getElementById('edit_dia_inicio');
            const diaF = document.getElementById('edit_dia_fin');
            const hora = document.getElementById('edit_horas');
            document.getElementById('edit_horario_hidden').value =
                `${diaI.value} A ${diaF.value} ${hora.value}`.toUpperCase();

            Swal.fire({
                title: '¿Guardar cambios?',
                text: 'Se modificará la oficina "' + document.getElementById('edit_nombre').value + '".',
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Sí, modificar',
                cancelButtonText: 'Cancelar'
            }).then(function (result) {
                if (result.isConfirmed) {
                    formEditar.submit();
                }
            });
        });
    })();

    // ==========================
    // ELIMINAR OFICINA (SweetAlert2)
    // ==========================
    function confirmarEliminar(id, nombre) {
        Swal.fire({
            title: '¿Eliminar oficina?',
            text: 'Se eliminará la sucursal "' + nombre + '". Esta acción no se puede deshacer.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Sí, eliminar',
            cancelButtonText: 'Cancelar'
        }).then(function (result) {
            if (result.isConfirmed) {
                const base = "{{ url('oficinas') }}";
                const form = document.getElementById('formEliminar');
                form.action = `${base}/${id}`;
                form.submit();
            }
        });
    }

    // ==========================
    // CALCULADORA
    // ==========================
    function toggleCalcFields() {
        const tipo = document.getElementById('calc_tipo').value;
        document.getElementById('div_delivery').style.display = tipo === 'delivery' ? 'block' : 'none';
        document.getElementById('div_dropoff').style.display = tipo === 'dropoff' ? 'block' : 'none';
        document.getElementById('calc_result').style.display = 'none';
    }

    function performCalculation() {
        const tipo = document.getElementById('calc_tipo').value;
        const id_categoria = document.getElementById('calc_categoria').value;

        let data = {
            tipo: tipo,
            id_categoria: id_categoria,
            _token: '{{ csrf_token() }}'
        };

        if (tipo === 'delivery') {
            data.id_ubicacion = document.getElementById('calc_ubicacion').value;
        } else {
            data.id_sucursal_origen = document.getElementById('calc_origen').value;
            data.id_sucursal_destino = document.getElementById('calc_destino').value;
        }

        fetch('{{ route("oficinas.calculate") }}', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(data)
        })
        .then(res => res.json())
        .then(res => {
            const resultDiv = document.getElementById('calc_result');
            const totalH = document.getElementById('total_result');
            const detailP = document.getElementById('detail_result');

            if (res.error) {
                totalH.innerText = "N/A";
                detailP.innerText = "⚠️ " + res.error;
                detailP.style.color = "#ef4444";
            } else {
                totalH.innerText = new Intl.NumberFormat('es-MX', { style: 'currency', currency: 'MXN' }).format(res.total);
                detailP.style.color = "#475569";

                if (tipo === 'delivery') {
                    detailP.innerText = `Distancia: ${res.km} km | Tarifa: $${res.costo_km}/km`;
                } else {
                    detailP.innerText = `Tarifa aplicada: ${res.tipo === 'fijo' ? 'Fija' : 'Por Kilómetro'}`;
                }
            }
            resultDiv.style.display = 'block';
        })
        .catch(error => {
            console.error("Error en el cálculo:", error);
            Swal.fire({
                icon: 'error',
                title: 'Error de conexión',
                text: 'Hubo un error al calcular la tarifa.',
                confirmButtonText: 'Entendido'
            });
        });
    }

    // ==========================
    // RESTRICCIONES DE INPUT
    // ==========================
    document.addEventListener('input', function (e) {
        // Solo números en teléfono
        if (e.target.classList.contains('only-numbers')) {
            e.target.value = e.target.value.replace(/\D/g, '');
        }
    });

    // ==========================
    // TOAST DE ÉXITO / ERROR / VALIDACIÓN (centrados, NO en la esquina)
    // ==========================
    @if(session('success'))
        Swal.fire({
            icon: 'success',
            title: 'Listo',
            text: @js(session('success')),
            confirmButtonText: 'Aceptar',
            confirmButtonColor: '#10b981'
        });
    @endif

    @if(session('error'))
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: @js(session('error')),
            confirmButtonText: 'Entendido'
        });
    @endif

    @if($errors->any())
        Swal.fire({
            icon: 'error',
            title: 'Revisa el formulario',
            html: `{!! implode('<br>', $errors->all()) !!}`,
            confirmButtonText: 'Entendido'
        });
    @endif
</script>
@endsection