@extends('layouts.Flotillas')
@section('Titulo', 'Pólizas')

@section('css-vistaPolizas')
<link rel="stylesheet" href="{{ asset('css/polizas.css') }}">
<style>
  .badge.green { background: #27ae60; color: white; padding: 4px 8px; border-radius: 6px; }
  .badge.red { background: #e74c3c; color: white; padding: 4px 8px; border-radius: 6px; }
  .badge.gray { background: #95a5a6; color: white; padding: 4px 8px; border-radius: 6px; }

  .btn.small {
    padding: 4px 8px;
    font-size: 13px;
    border-radius: 6px;
    text-decoration: none;
    color: white;
  }
  .btn.small.blue { background: #3498db; }
  .btn.small.green { background: #27ae60; }
  .btn.small.orange { background: #e67e22; }
  .btn.small.purple { background: #8e44ad; }
  .btn.small.gray { background: #7f8c8d; }
  .btn.small:hover { opacity: 0.85; }

  /* Modal */
  .modal {
    display: none;
    position: fixed;
    top: 0; left: 0;
    width: 100%; height: 100%;
    background: rgba(0,0,0,0.6);
    justify-content: center;
    align-items: center;
    z-index: 9999;
  }
  .modal-content {
    background: #fff;
    padding: 20px 25px;
    border-radius: 10px;
    width: 500px;
    max-width: 90%;
    box-shadow: 0 4px 15px rgba(0,0,0,0.2);
  }
  .modal-header {
    display: flex; justify-content: space-between; align-items: center;
    margin-bottom: 10px;
  }
  .modal-header h2 { font-size: 1.3rem; }
  .close { cursor: pointer; font-size: 22px; color: #555; }
  .form-group { margin-bottom: 12px; }
  .form-group label { font-weight: 600; display: block; margin-bottom: 4px; }
  .form-group input { width: 100%; padding: 8px; border-radius: 6px; border: 1px solid #ccc; }
</style>
@endsection

@section('contenidoPolizas')
<main>
  <div class="topbar"><div><strong>Autos · Pólizas</strong></div></div>

  <div class="content">
    <h1 class="title">Pólizas</h1>
    <p class="sub">Control de vigencias de pólizas de seguro por vehículo.</p>

    <div class="toolbar">
      <div class="search"><input id="qPolizas" type="text" placeholder="Buscar por coche, placa o póliza"></div>
      <button class="btn ghost" id="exportPolizas">⬇️ Exportar CSV</button>
    </div>

    <div style="overflow:auto">
      <table class="table" id="tblPolizas">
        <thead>
          <tr>
            <th>Carro</th><th>Póliza</th><th>Aseguradora</th><th>Vigencia</th><th>Estatus</th><th>Archivo</th><th>Acciones</th>
          </tr>
        </thead>
        <tbody>
          @forelse($polizas as $p)
            @php
              $fin = $p->fin_vigencia_poliza ? \Carbon\Carbon::parse($p->fin_vigencia_poliza) : null;
              $vigente = $fin && $fin->isFuture();
              $archivo = $p->archivo_poliza ? basename($p->archivo_poliza) : null;
              $url = $archivo ? asset('storage/polizas/'.$archivo) : null;
            @endphp
            <tr>
              <td><strong>{{ $p->nombre_publico }}</strong><br><small>{{ $p->placa }}</small></td>
              <td>{{ $p->no_poliza ?? 'Sin póliza' }}</td>
              <td>{{ $p->aseguradora ?? '—' }}</td>
              <td>
                {{ $p->inicio_vigencia_poliza ? \Carbon\Carbon::parse($p->inicio_vigencia_poliza)->format('d/m/Y') : '—' }}
                –
                {{ $p->fin_vigencia_poliza ? \Carbon\Carbon::parse($p->fin_vigencia_poliza)->format('d/m/Y') : '—' }}
              </td>
              <td>
                @if(!$fin)
                  <span class="badge gray">Sin fecha</span>
                @elseif($vigente)
                  <span class="badge green">Vigente</span>
                @else
                  <span class="badge red">Vencida</span>
                @endif
              </td>
              <td>
                @if($p->archivo_poliza)
                  <div style="display:flex; gap:8px;">
                    <a href="{{ route('verPoliza', $p->id_vehiculo) }}" target="_blank" class="btn small blue">Ver</a>
                    <a href="{{ route('descargarPoliza', $p->id_vehiculo) }}" class="btn small green">Descargar</a>
                  </div>
                @else
                  <span class="text-gray">—</span>
                @endif
              </td>
              <td>
                <div style="display:flex; gap:8px;">
                  <button class="btn small orange" onclick="abrirModalEditar({{ $p->id_vehiculo }}, '{{ $p->no_poliza }}', '{{ $p->aseguradora }}', '{{ $p->plan_seguro }}', '{{ $p->inicio_vigencia_poliza }}', '{{ $p->fin_vigencia_poliza }}')">Editar</button>
                  <button class="btn small purple" onclick="abrirModalArchivo({{ $p->id_vehiculo }})">Subir archivo</button>
                </div>
              </td>
            </tr>
          @empty
            <tr><td colspan="7" class="text-center">No hay pólizas registradas</td></tr>
          @endforelse
        </tbody>
      </table>
    </div>
  </div>
</main>

<!-- Modal Editar -->
<div class="modal" id="modalEditar">
  <div class="modal-content">
    <div class="modal-header">
      <h2>Editar Póliza</h2>
      <span class="close" onclick="cerrarModal('modalEditar')">&times;</span>
    </div>
    <form id="formEditar" method="POST">
      @csrf
      <div class="form-group"><label>Número de póliza</label><input type="text" id="edit_no_poliza" name="no_poliza" required></div>
      <div class="form-group"><label>Aseguradora</label><input type="text" id="edit_aseguradora" name="aseguradora" required></div>
      <div class="form-group"><label>Plan o cobertura</label><input type="text" id="edit_plan_seguro" name="plan_seguro"></div>
      <div class="form-group"><label>Inicio vigencia</label><input type="date" id="edit_inicio" name="inicio_vigencia_poliza"></div>
      <div class="form-group"><label>Fin vigencia</label><input type="date" id="edit_fin" name="fin_vigencia_poliza"></div>
      <div style="text-align:right;">
        <button type="submit" class="btn small green">Guardar</button>
        <button type="button" class="btn small gray" onclick="cerrarModal('modalEditar')">Cancelar</button>
      </div>
    </form>
  </div>
</div>

<!-- Modal Subir -->
<div class="modal" id="modalArchivo">
  <div class="modal-content">
    <div class="modal-header">
      <h2>Subir nuevo archivo</h2>
      <span class="close" onclick="cerrarModal('modalArchivo')">&times;</span>
    </div>
    <form id="formArchivo" method="POST" enctype="multipart/form-data">
      @csrf
      <div class="form-group">
        <label>Seleccionar archivo (PDF o imagen)</label>
        <input type="file" name="archivo_poliza" accept=".pdf,.jpg,.jpeg,.png" required>
      </div>
      <div style="text-align:right;">
        <button type="submit" class="btn small green">Subir</button>
        <button type="button" class="btn small gray" onclick="cerrarModal('modalArchivo')">Cancelar</button>
      </div>
    </form>
  </div>
</div>
@endsection

@section('js-vistaPolizas')
<script>
  function abrirModalEditar(id, no_poliza, aseguradora, plan, inicio, fin) {
    document.getElementById('modalEditar').style.display = 'flex';
    document.getElementById('edit_no_poliza').value = no_poliza || '';
    document.getElementById('edit_aseguradora').value = aseguradora || '';
    document.getElementById('edit_plan_seguro').value = plan || '';
    document.getElementById('edit_inicio').value = inicio || '';
    document.getElementById('edit_fin').value = fin || '';
    document.getElementById('formEditar').action = `/admin/polizas/actualizar/${id}`;
  }

  function abrirModalArchivo(id) {
    document.getElementById('modalArchivo').style.display = 'flex';
    document.getElementById('formArchivo').action = `/admin/polizas/subir/${id}`;
  }

  function cerrarModal(id) {
    document.getElementById(id).style.display = 'none';
  }
</script>
@endsection
