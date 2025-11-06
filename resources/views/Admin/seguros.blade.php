@extends('layouts.Flotillas')
@section('Titulo', 'Seguros · Siniestros')

@section('css-vistaSeguros')
<link rel="stylesheet" href="{{ asset('css/seguros.css') }}">
<style>
  .btn.small { padding:4px 8px; font-size:13px; border-radius:6px; }
  .modal { display:none; position:fixed; top:0; left:0; right:0; bottom:0; background:rgba(0,0,0,0.5); justify-content:center; align-items:center; }
  .modal-content { background:#fff; padding:20px; border-radius:12px; width:400px; max-width:90%; }
</style>
@endsection

@section('contenidoSeguros')
<main>
  <div class="topbar"><div><strong>Autos · Seguros / Siniestros</strong></div></div>

  <div class="content">
    <h1 class="title">Siniestros</h1>
    <p class="sub">Registro y seguimiento de siniestros asociados a cada vehículo.</p>

    <!-- 🔍 Buscador -->
    <div class="buscador-flotilla">
      <i class="fas fa-search icono-buscar"></i>
      <input id="filtroSiniestros" type="text" placeholder="Buscar por placa, folio o tipo...">
    </div>

    <!-- 🔴 Nuevo siniestro -->
    <div class="d-flex justify-content-end mb-3">
      <button class="btn btn-danger" onclick="abrirModalNuevo()">➕ Nuevo siniestro</button>
    </div>

    <!-- 📋 Tabla -->
    <div style="overflow:auto">
      <table class="table" id="tblSiniestros">
        <thead>
          <tr>
            <th>Folio</th>
            <th>Vehículo</th>
            <th>Fecha</th>
            <th>Tipo</th>
            <th>Estatus</th>
            <th>Deducible</th>
            <th>Archivo</th>
            <th>Acciones</th>
          </tr>
        </thead>
        <tbody>
          @forelse($siniestros as $s)
          <tr>
            <td>{{ $s->folio }}</td>
            <td><strong>{{ $s->nombre_publico }}</strong><br><small>{{ $s->placa }}</small></td>
            <td>{{ \Carbon\Carbon::parse($s->fecha)->format('d/m/Y') }}</td>
            <td>{{ $s->tipo }}</td>
            <td>{{ $s->estatus }}</td>
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
              <button class="btn small orange" onclick="abrirModalEditar({{ $s->id_siniestro }}, '{{ $s->folio }}', '{{ $s->fecha }}', '{{ $s->tipo }}', '{{ $s->estatus }}', '{{ $s->deducible }}', '{{ $s->rin }}')">Editar</button>
              <button class="btn small purple" onclick="abrirModalArchivo({{ $s->id_siniestro }})">Subir archivo</button>
            </td>
          </tr>
          @empty
            <tr><td colspan="8" class="text-center">No hay siniestros registrados</td></tr>
          @endforelse
        </tbody>
      </table>
    </div>
  </div>
</main>

<!-- 🟢 Modal Nuevo -->
<div class="modal" id="modalNuevo">
  <div class="modal-content">
    <h3>Nuevo Siniestro</h3>
    <form method="POST" action="{{ route('guardarSiniestro') }}">
      @csrf
      <label>ID Vehículo</label>
      <input type="number" name="id_vehiculo" required>
      <label>Folio</label>
      <input type="text" name="folio" required>
      <label>Fecha</label>
      <input type="date" name="fecha" required>
      <label>Tipo</label>
      <select name="tipo" required>
        <option value="Recuperado">Recuperado</option>
        <option value="Robo">Robo</option>
        <option value="Robo de piezas">Robo de piezas</option>
        <option value="Pérdida total">Pérdida total</option>
        <option value="Temas legales">Temas legales</option>
      </select>
      <label>Deducible</label>
      <input type="number" step="0.01" name="deducible">
      <label>Rin</label>
      <input type="text" name="rin">
      <div style="text-align:right;margin-top:10px;">
        <button class="btn small green">Guardar</button>
        <button type="button" class="btn small gray" onclick="cerrarModal('modalNuevo')">Cancelar</button>
      </div>
    </form>
  </div>
</div>

<!-- 🟠 Modal Editar -->
<div class="modal" id="modalEditar">
  <div class="modal-content">
    <h3>Editar Siniestro</h3>
    <form id="formEditar" method="POST">
      @csrf
      <label>Folio</label>
      <input type="text" id="edit_folio" name="folio" required>
      <label>Fecha</label>
      <input type="date" id="edit_fecha" name="fecha" required>
      <label>Tipo</label>
      <select id="edit_tipo" name="tipo" required>
        <option value="Recuperado">Recuperado</option>
        <option value="Robo">Robo</option>
        <option value="Robo de piezas">Robo de piezas</option>
        <option value="Pérdida total">Pérdida total</option>
        <option value="Temas legales">Temas legales</option>
      </select>
      <label>Estatus</label>
      <input type="text" id="edit_estatus" name="estatus" required>
      <label>Deducible</label>
      <input type="number" id="edit_deducible" name="deducible" step="0.01">
      <label>Rin</label>
      <input type="text" id="edit_rin" name="rin">
      <div style="text-align:right;margin-top:10px;">
        <button class="btn small green">Guardar</button>
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
function abrirModalEditar(id, folio, fecha, tipo, estatus, deducible, rin) {
  document.getElementById('modalEditar').style.display = 'flex';
  document.getElementById('edit_folio').value = folio;
  document.getElementById('edit_fecha').value = fecha;
  document.getElementById('edit_tipo').value = tipo;
  document.getElementById('edit_estatus').value = estatus;
  document.getElementById('edit_deducible').value = deducible;
  document.getElementById('edit_rin').value = rin;
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
</script>
@endsection
