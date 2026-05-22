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

  @if(session('success'))
    <div class="toast" style="background: #10b981; color: white; padding: 10px; margin-bottom: 15px; border-radius: 5px;">
        {{ session('success') }}
    </div>
  @endif

  <section class="card">
    <table class="table">
      <thead>
        <tr>
          <th style="width: 15%;">Estado</th>
          <th style="width: 25%;">SUCURSAL DE RETIRO</th>
          <th style="width: 30%;">Dirección</th>
          <th style="width: 15%;">Teléfono</th>
          <th style="width: 15%;">Horarios</th>
        </tr>
      </thead>
      <tbody>
        @forelse($sucursales as $sucursal)
            @php 
                // Decodificamos el JSON del horario
                $horarioData = is_string($sucursal->horario_json) ? json_decode($sucursal->horario_json) : null;
                $textoHorario = $horarioData->horario ?? 'No definido';
            @endphp
            <tr>
                <td><span class="badge-estado">{{ $sucursal->ciudad_estado ?? 'N/A' }}</span></td>
                <td><strong>{{ $sucursal->nombre }}</strong></td>
                <td><span style="color: #64748b; font-size: 13px;">{{ $sucursal->direccion }}</span></td>
                {{-- Nota: Si agregaste la columna 'telefono' a tu BD, úsala aquí. Si no, ajusta este campo. --}}
                <td>{{ $sucursal->telefono ?? 'N/A' }}</td>
                <td>{{ $textoHorario }}</td>
            </tr>
        @empty
            <tr>
                <td colspan="5" style="text-align: center; padding: 30px; color: #94a3b8;">
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

    {{-- Separé Teléfono y Horario para que encaje con las columnas de tu tabla --}}
    <div style="display: flex; gap: 10px; margin-top: 10px;">
        <div style="flex: 1;">
            <label class="label">Teléfono</label>
            <input type="text" name="telefono" class="input" placeholder="Ej: 442 123 4567">
        </div>
        <div style="flex: 1;">
            <label class="label">Horarios</label>
            <input type="text" name="horario" class="input" placeholder="Ej: Lun-Dom 24hrs" required>
        </div>
    </div>

    <div class="modal-actions" style="margin-top: 20px;">
      <button class="btn-add" type="submit">Guardar Oficina</button>
      <button class="btn-ghost" type="button" onclick="document.getElementById('modalNueva').close()">Cancelar</button>
    </div>
  </form>
</dialog>

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
<script>
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
            alert("Hubo un error de conexión al calcular la tarifa.");
        });
    }
</script>
@endsection