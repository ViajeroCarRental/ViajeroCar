@extends('layouts.Flotillas') 
@section('Titulo', 'Pólizas')

@section('css-vistaPolizas')
<link rel="stylesheet" href="{{ asset('css/polizas.css') }}">

@endsection

@section('contenidoPolizas')
<main>
  <div class="topbar"><div><strong>Autos · Pólizas</strong></div></div>

  <div class="content">
    <h1 class="title">Pólizas</h1>
    <p class="sub">Control de vigencias de pólizas de seguro por vehículo.</p>

    <!-- 🔍 Buscador -->
    <div class="buscador-flotilla">
      <i class="fas fa-search icono-buscar"></i>
      <input 
        type="text" 
        id="filtroPolizas" 
        placeholder="Buscar por coche, placa, aseguradora o póliza...">
    </div>

    <div style="overflow:auto">
      <table class="table" id="tblPolizas">
        <thead>
          <tr>
            <th>Carro</th>
            <th>Póliza</th>
            <th>Aseguradora</th>
            <th>Vigencia</th>
            <th>Días restantes</th>
            <th>Estatus</th>
            <th>Archivo</th>
            <th>Acciones</th>
          </tr>
        </thead>
        <tbody>
          @forelse($polizas as $p)
            @php
              $fin = $p->fin_vigencia_poliza ? \Carbon\Carbon::parse($p->fin_vigencia_poliza) : null;
              $inicio = $p->inicio_vigencia_poliza ? \Carbon\Carbon::parse($p->inicio_vigencia_poliza) : null;
              $hoy = \Carbon\Carbon::now();

              $diasRestantes = $fin ? $hoy->floatDiffInDays($fin, false) : null;
              $diasRestantes = $diasRestantes !== null ? (int) round($diasRestantes) : null;

              if ($diasRestantes === null) {
                  $estatus = ['label' => 'Sin fecha', 'color' => 'gray'];
              } elseif ($diasRestantes < 0) {
                  $estatus = ['label' => 'Vencida', 'color' => 'red'];
              } elseif ($diasRestantes <= 9) {
                  $estatus = ['label' => 'Por vencer', 'color' => 'red'];
              } elseif ($diasRestantes >= 10 && $diasRestantes <= 20) {
                  $estatus = ['label' => 'Por vencer', 'color' => 'yellow'];
              } else {
                  $estatus = ['label' => 'Vigente', 'color' => 'green'];
              }

              $archivo = $p->archivo_poliza ? basename($p->archivo_poliza) : null;
              $url = $archivo ? asset('storage/polizas/'.$archivo) : null;
            @endphp

            <tr>
              <td><strong>{{ $p->nombre_publico }}</strong><br><small>{{ $p->placa }}</small></td>
              <td>{{ $p->no_poliza ?? 'Sin póliza' }}</td>
              <td>{{ $p->aseguradora ?? '—' }}</td>
              <td>
                {{ $inicio ? $inicio->format('d/m/Y') : '—' }}
                –
                {{ $fin ? $fin->format('d/m/Y') : '—' }}
              </td>
              <td>
                @if($diasRestantes !== null)
                  <span>{{ $diasRestantes }} {{ abs($diasRestantes) == 1 ? 'día' : 'días' }}</span>
                @else
                  <span>—</span>
                @endif
              </td>
              <td><span class="badge {{ $estatus['color'] }}">{{ $estatus['label'] }}</span></td>

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
            <tr><td colspan="8" class="text-center">No hay pólizas registradas</td></tr>
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
      <div class="form-group">
        <label>Número de póliza</label>
        <input type="text" id="edit_no_poliza" name="no_poliza" required>
      </div>
      <div class="form-group">
        <label>Aseguradora</label>
        <input type="text" id="edit_aseguradora" name="aseguradora" required>
      </div>
      <div class="form-group">
        <label>Plan o cobertura</label>
        <input type="text" id="edit_plan_seguro" name="plan_seguro">
      </div>
      <div class="form-group">
        <label>Inicio vigencia</label>
        <input type="date" id="edit_inicio" name="inicio_vigencia_poliza">
      </div>
      <div class="form-group">
        <label>Fin vigencia</label>
        <input type="date" id="edit_fin" name="fin_vigencia_poliza">
      </div>
      <div class="form-group">
        <label>Costo de póliza</label>
        <input type="number" id="edit_costo" name="costo_poliza" step="0.01" min="0" placeholder="Ej. 1200.50">
      </div>
      <div style="text-align:right;">
        <button type="submit" class="btn small green">Guardar</button>
        <button type="button" class="btn small gray" onclick="cerrarModal('modalEditar')">Cancelar</button>
      </div>
    </form>
  </div>
</div>

@endsection

@section('js-vistaPolizas')
<script>
  function abrirModalEditar(id, no_poliza, aseguradora, plan, inicio, fin, costo = 0) {
    document.getElementById('modalEditar').style.display = 'flex';
    document.getElementById('edit_no_poliza').value = no_poliza || '';
    document.getElementById('edit_aseguradora').value = aseguradora || '';
    document.getElementById('edit_plan_seguro').value = plan || '';
    document.getElementById('edit_inicio').value = inicio || '';
    document.getElementById('edit_fin').value = fin || '';
    document.getElementById('edit_costo').value = costo || '';
    document.getElementById('formEditar').action = `/admin/polizas/actualizar/${id}`;
  }

  function abrirModalArchivo(id) {
    document.getElementById('modalArchivo').style.display = 'flex';
    document.getElementById('formArchivo').action = `/admin/polizas/subir/${id}`;
  }

  function cerrarModal(id) {
    document.getElementById(id).style.display = 'none';
  }

  // === 🔎 FILTRO DE PÓLIZAS ===
  document.getElementById('filtroPolizas').addEventListener('keyup', function () {
    const filtro = this.value.toLowerCase();
    const filas = document.querySelectorAll('#tblPolizas tbody tr');

    filas.forEach(fila => {
      const texto = fila.textContent.toLowerCase();
      fila.style.display = texto.includes(filtro) ? '' : 'none';
    });
  });
</script>
@endsection
