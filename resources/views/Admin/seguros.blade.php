@extends('layouts.Flotillas')
@section('Titulo', 'Seguros · Siniestros')

@section('css-vistaSeguros')
<link rel="stylesheet" href="{{ asset('css/seguros.css') }}">
@endsection

@section('contenidoSeguros')
<main>
  <div class="topbar"><div><strong>Autos · Seguros / Siniestros</strong></div></div>

  <div class="content">
    <h1 class="title">Siniestros</h1>
    <p class="sub">Registro y seguimiento de siniestros asociados a cada vehículo.</p>

<!-- 🔍 Buscador + Botón nuevo -->
<div class="encabezado-siniestros">
  
  <!-- Buscador -->
  <div class="buscador-flotilla">
    <i class="fas fa-search icono-buscar"></i>
    <input id="filtroSiniestros" type="text" placeholder="Buscar por vehículo o tipo...">
  </div>

  <!-- Botón nuevo -->
  <button class="btn-nuevo-siniestro" onclick="abrirModalNuevo()">
    <i class="bi bi-plus-lg"></i> ➕ Nuevo siniestro
  </button>

</div>

    <!-- 📋 Tabla -->
    <div style="overflow:auto">
      <table class="table" id="tblSiniestros">
        <thead>
          <tr>
            <th>Vehículo</th>
            <th>Fecha</th>
            <th>Tipo</th>
            <th>Deducible</th>
            <th>Archivo</th>
            <th>Acciones</th>
          </tr>
        </thead>
        <tbody>
          @forelse($siniestros as $s)
          <tr>
            <td><strong>{{ $s->nombre_publico }}</strong><br><small>{{ $s->placa }}</small></td>
            <td>{{ \Carbon\Carbon::parse($s->fecha)->format('d/m/Y') }}</td>
            <td>{{ $s->tipo }}</td>
            <td>${{ number_format($s->deducible, 2) }}</td>
            <td>
              @if($s->archivo)
                <a href="{{ route('verSiniestro', $s->id_siniestro) }}" target="_blank" class="btn small blue">Ver</a>
                <a href="{{ route('descargarSiniestro', $s->id_siniestro) }}" class="btn small green">Descargar</a>
              @else
                <span>—</span>
              @endif
            </td>
            <td>
              <button class="btn small orange" 
                onclick="abrirModalEditar({{ $s->id_siniestro }}, '{{ $s->fecha }}', '{{ $s->tipo }}', '{{ $s->deducible }}', '{{ $s->descripcion }}')">
                Editar
              </button>
              <button class="btn small purple" onclick="abrirModalArchivo({{ $s->id_siniestro }})">Subir archivo</button>
            </td>
          </tr>
          @empty
            <tr><td colspan="6" class="text-center">No hay siniestros registrados</td></tr>
          @endforelse
        </tbody>
      </table>
    </div>
  </div>
</main>

<!-- 🟢 Modal Nuevo -->
<div class="modal" id="modalNuevo">
  <div class="modal-content nuevo-siniestro-modal">
    <h3 style="margin-bottom:12px;">Nuevo Siniestro</h3>

    <form method="POST" action="{{ route('guardarSiniestro') }}">
      @csrf

      <!-- Vehículo (buscador) -->
      <div style="margin-bottom:10px;">
        <label style="font-weight:600;">Vehículo</label>
        <input type="hidden" name="id_vehiculo" id="nv_id_vehiculo" required>

        <div style="position:relative;">
          <input
            type="text"
            id="buscadorVehiculo"
            placeholder="Buscar por placa, color, número de serie o año..."
            autocomplete="off"
            style="width:100%;padding:10px 12px;border:1px solid #e0e0e0;border-radius:10px;outline:0;"
          />
          <div id="listaVehiculos"
               style="position:absolute;z-index:30;top:42px;left:0;right:0;background:#fff;border:1px solid #ececec;border-radius:10px;max-height:260px;overflow:auto;display:none;box-shadow:0 6px 18px rgba(0,0,0,.08);">
          </div>
        </div>

        <small id="vehiculoElegido" style="display:none;color:#2c3e50;margin-top:6px;"></small>
      </div>

      <!-- Fecha y Tipo -->
      <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;margin-top:6px;">
        <div>
          <label style="font-weight:600;">Fecha del siniestro</label>
          <input type="date" name="fecha" required
                 style="width:100%;padding:10px 12px;border:1px solid #e0e0e0;border-radius:10px;outline:0;">
        </div>
        <div>
          <label style="font-weight:600;">Tipo de siniestro</label>
          <select name="tipo" required
                  style="width:100%;padding:10px 12px;border:1px solid #e0e0e0;border-radius:10px;outline:0;">
            <option value="Recuperado">Recuperado</option>
            <option value="Robo">Robo</option>
            <option value="Robo de piezas">Robo de piezas</option>
            <option value="Pérdida total">Pérdida total</option>
            <option value="Temas legales">Temas legales</option>
          </select>
        </div>
      </div>

      <!-- Deducible -->
      <div style="margin-top:12px;">
        <label style="font-weight:600;">Deducible</label>
        <input type="number" step="0.01" name="deducible"
               style="width:100%;padding:10px 12px;border:1px solid #e0e0e0;border-radius:10px;outline:0;">
      </div>

      <!-- Descripción -->
      <div style="margin-top:12px;">
        <label style="font-weight:600;">Descripción del siniestro</label>
        <textarea name="descripcion" rows="4"
                  placeholder="Describe brevemente qué ocurrió (lugares, partes afectadas, participantes, etc.)"
                  style="width:100%;padding:10px 12px;border:1px solid #e0e0e0;border-radius:10px;outline:0;resize:vertical;"></textarea>
      </div>

      <!-- Botones -->
      <div style="display:flex;gap:8px;justify-content:flex-end;margin-top:16px;">
        <button class="btn small green" style="font-weight:600;">Guardar</button>
        <button type="button" class="btn small gray" onclick="cerrarModal('modalNuevo')">Cancelar</button>
      </div>
    </form>
  </div>
</div>

<!-- 🟠 Modal Editar -->
<div class="modal" id="modalEditar">
  <div class="modal-content" style="width:560px;max-width:92%;">
    <h3 style="margin-bottom:12px;">Editar Siniestro</h3>

    <form id="formEditar" method="POST">
      @csrf

      <!-- Fecha y Tipo -->
      <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;margin-bottom:12px;">
        <div>
          <label style="font-weight:600;">Fecha</label>
          <input type="date" id="edit_fecha" name="fecha" required
                 style="width:100%;padding:10px 12px;border:1px solid #e0e0e0;border-radius:10px;outline:0;">
        </div>
        <div>
          <label style="font-weight:600;">Tipo de siniestro</label>
          <select id="edit_tipo" name="tipo" required
                  style="width:100%;padding:10px 12px;border:1px solid #e0e0e0;border-radius:10px;outline:0;">
            <option value="Recuperado">Recuperado</option>
            <option value="Robo">Robo</option>
            <option value="Robo de piezas">Robo de piezas</option>
            <option value="Pérdida total">Pérdida total</option>
            <option value="Temas legales">Temas legales</option>
          </select>
        </div>
      </div>

      <!-- Deducible -->
      <div style="margin-bottom:12px;">
        <label style="font-weight:600;">Deducible</label>
        <input type="number" step="0.01" id="edit_deducible" name="deducible"
               style="width:100%;padding:10px 12px;border:1px solid #e0e0e0;border-radius:10px;outline:0;">
      </div>

      <!-- Descripción -->
      <div style="margin-bottom:16px;">
        <label style="font-weight:600;">Descripción</label>
        <textarea id="edit_descripcion" name="descripcion" rows="4"
                  placeholder="Actualiza la descripción del siniestro..."
                  style="width:100%;padding:10px 12px;border:1px solid #e0e0e0;border-radius:10px;outline:0;resize:vertical;"></textarea>
      </div>

      <!-- Botones -->
      <div style="display:flex;gap:8px;justify-content:flex-end;">
        <button class="btn small green" style="font-weight:600;">Guardar</button>
        <button type="button" class="btn small gray" onclick="cerrarModal('modalEditar')">Cancelar</button>
      </div>
    </form>
  </div>
</div>

<!-- 🟣 Modal Archivo -->
<div class="modal" id="modalArchivo">
  <div class="modal-content">
    <h3>Subir archivo</h3>
    <form id="formArchivo" method="POST" enctype="multipart/form-data">
      @csrf
      <input type="file" name="archivo" accept=".pdf,.jpg,.jpeg,.png" required>
      <div style="text-align:right;margin-top:10px;">
        <button class="btn small green">Subir</button>
        <button type="button" class="btn small gray" onclick="cerrarModal('modalArchivo')">Cancelar</button>
      </div>
    </form>
  </div>
</div>
@endsection

@section('js-vistaSeguros')
<script>
function abrirModalNuevo() { document.getElementById('modalNuevo').style.display = 'flex'; }
function abrirModalEditar(id, fecha, tipo, deducible, descripcion) {
  document.getElementById('modalEditar').style.display = 'flex';
  document.getElementById('edit_fecha').value = fecha;
  document.getElementById('edit_tipo').value = tipo;
  document.getElementById('edit_deducible').value = deducible;
  document.getElementById('edit_descripcion').value = descripcion || '';
  document.getElementById('formEditar').action = `/admin/siniestros/actualizar/${id}`;
}
function abrirModalArchivo(id) {
  document.getElementById('modalArchivo').style.display = 'flex';
  document.getElementById('formArchivo').action = `/admin/siniestros/subir/${id}`;
}
function cerrarModal(id) { document.getElementById(id).style.display = 'none'; }

// 🔍 Filtro
document.getElementById('filtroSiniestros').addEventListener('keyup', function() {
  const filtro = this.value.toLowerCase();
  document.querySelectorAll('#tblSiniestros tbody tr').forEach(fila => {
    fila.style.display = fila.textContent.toLowerCase().includes(filtro) ? '' : 'none';
  });
});

// ===== Buscador AJAX =====
function debounce(fn, delay = 250) {
  let t; return (...args) => { clearTimeout(t); t = setTimeout(() => fn(...args), delay); };
}
const inp = document.getElementById('buscadorVehiculo');
const list = document.getElementById('listaVehiculos');
const hiddenId = document.getElementById('nv_id_vehiculo');
const elegido = document.getElementById('vehiculoElegido');

function renderOpciones(items) {
  if (!items || items.length === 0) {
    list.innerHTML = `<div style="padding:10px 12px;color:#777;">Sin resultados</div>`;
    list.style.display = 'block';
    return;
  }
  list.innerHTML = items.map(it => `
    <div class="opc-vehiculo" data-id="${it.id}"
         style="padding:10px 12px;border-bottom:1px solid #f1f1f1;cursor:pointer;">
      <div style="font-weight:600">${it.label}</div>
      <div style="font-size:12px;color:#666">${it.sub ?? ''}</div>
    </div>
  `).join('');
  list.style.display = 'block';
  list.querySelectorAll('.opc-vehiculo').forEach(el => {
    el.addEventListener('click', () => {
      hiddenId.value = el.dataset.id;
      inp.value = el.querySelector('div').innerText;
      list.style.display = 'none';
      elegido.style.display = 'block';
      elegido.innerText = 'Seleccionado: ' + inp.value;
    });
  });
}

const buscar = debounce(async (q) => {
  const term = q.trim();
  if (term.length < 2) {
    list.style.display = 'none';
    return;
  }
  try {
    const url = `{{ route('vehiculos.buscar') }}?q=${encodeURIComponent(term)}`;
    const res = await fetch(url, { headers: { 'Accept': 'application/json' } });
    const data = await res.json();
    renderOpciones(data);
  } catch (e) {
    list.innerHTML = `<div style="padding:10px 12px;color:#c0392b;">Error al buscar</div>`;
    list.style.display = 'block';
  }
}, 300);

if (inp) {
  inp.addEventListener('input', () => {
    hiddenId.value = '';
    elegido.style.display = 'none';
    buscar(inp.value);
  });
  document.addEventListener('click', (ev) => {
    if (!list.contains(ev.target) && ev.target !== inp) {
      list.style.display = 'none';
    }
  });
}
</script>
@endsection
