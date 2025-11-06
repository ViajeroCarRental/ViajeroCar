@extends('layouts.Flotillas')
@section('Titulo', 'Carrocería')

@section('css-vistaCarroceria')
<link rel="stylesheet" href="{{ asset('css/carroceria.css') }}">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

@endsection

@section('contenidoCarroceria')
<main>
  <div class="topbar">
    <div><strong>Autos · Carrocería</strong></div>
  </div>

  <div class="content">
    <h1 class="title">Carrocería</h1>
    <p class="sub">Historial de daños, reparaciones y reportes visuales de cada vehículo.</p>

<!-- 🔍 Buscador + Botón alineados -->
<div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
  <div class="buscador-flotilla mb-0">
    <i class="fas fa-search icono-buscar"></i>
    <input 
      type="text" 
      id="filtroCarroceria" 
      placeholder="Buscar por placa, modelo, marca, zona o taller...">
  </div>

  <button class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#modalNuevoReporte">
    <i class="bi bi-plus-lg"></i> Nuevo reporte
  </button>
</div>

    <!-- 📋 Tabla -->
    <div class="table-wrapper">
      <table class="table table-hover align-middle" id="tblCarroceria">
        <thead class="table-danger">
          <tr>
            <th>Placa</th>
            <th>Vehículo</th>
            <th>Zona</th>
            <th>Daño</th>
            <th>Severidad</th>
            <th>Taller</th>
            <th>Costo</th>
            <th>Estatus</th>
            <th>Acciones</th>
          </tr>
        </thead>
        <tbody>
          @forelse ($carrocerias as $c)
            @php
              $estatusClass = strtolower($c->estatus ?? '');
            @endphp
            <tr>
              <td>{{ $c->placa }}</td>
              <td>{{ $c->marca }} {{ $c->modelo }}</td>
              <td>{{ $c->zona_afectada }}</td>
              <td>{{ $c->tipo_danio }}</td>
              <td>{{ $c->severidad }}</td>
              <td>{{ $c->taller }}</td>
              <td>${{ number_format($c->costo_estimado, 2) }}</td>
              <td><span class="status {{ $estatusClass }}">{{ $c->estatus }}</span></td>
              <td class="text-center">
                <button class="btn btn-sm btn-outline-danger me-1 btnEditar"
                        data-id="{{ $c->id_carroceria }}"
                        data-zona="{{ $c->zona_afectada }}"
                        data-danio="{{ $c->tipo_danio }}"
                        data-severidad="{{ $c->severidad }}"
                        data-taller="{{ $c->taller }}"
                        data-costo="{{ $c->costo_estimado }}"
                        data-estatus="{{ $c->estatus }}"
                        data-bs-toggle="modal" data-bs-target="#modalEditarReporte">
                  <i class="bi bi-pencil-square"></i>
                </button>
              </td>
            </tr>
          @empty
            <tr><td colspan="9" class="text-center text-muted">No hay reportes registrados.</td></tr>
          @endforelse
        </tbody>
      </table>
    </div>
  </div>
</main>

<!-- 🟥 MODAL: NUEVO REPORTE -->
<div class="modal fade" id="modalNuevoReporte" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-lg">
    <div class="modal-content">
      <div class="modal-header bg-danger text-white">
        <h5 class="modal-title">Nuevo reporte de carrocería</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>
      <form action="{{ route('carroceria.store') }}" method="POST">
        @csrf
        <div class="modal-body">
          <div class="row g-3">

            <!-- Vehículo -->
            <div class="col-12">
              <label class="form-label">Vehículo</label>
              <div class="input-group">
                <input type="text" id="vehiculoTexto" class="form-control" placeholder="Selecciona un vehículo…" readonly>
                <button class="btn btn-outline-secondary" type="button" data-bs-toggle="modal" data-bs-target="#modalElegirVehiculo">
                  <i class="bi bi-search"></i> Elegir
                </button>
              </div>
              <div class="form-text form-text-vehiculo">Busca por marca, modelo o placa.</div>
              <input type="hidden" name="id_vehiculo" id="id_vehiculo" required>
            </div>

            <div class="col-md-6">
              <label class="form-label">Zona afectada</label>
              <input type="text" name="zona_afectada" class="form-control" required>
            </div>

            <div class="col-md-6">
              <label class="form-label">Tipo de daño</label>
              <input type="text" name="tipo_danio" class="form-control" required>
            </div>

            <div class="col-md-6">
              <label class="form-label">Severidad</label>
              <select name="severidad" class="form-select" required>
                <option value="Leve">Leve</option>
                <option value="Media">Media</option>
                <option value="Alta">Alta</option>
              </select>
            </div>

            <div class="col-md-6">
              <label class="form-label">Taller</label>
              <input type="text" name="taller" class="form-control">
            </div>

            <div class="col-md-6">
              <label class="form-label">Costo estimado</label>
              <input type="number" step="0.01" min="0" name="costo_estimado" class="form-control">
            </div>

            <div class="col-md-6">
              <label class="form-label">Estatus</label>
              <select name="estatus" class="form-select" required>
                <option value="Pendiente">Pendiente</option>
                <option value="Cotizado">Cotizado</option>
                <option value="En proceso">En proceso</option>
                <option value="Terminado">Terminado</option>
              </select>
            </div>

          </div>
        </div>
        <div class="modal-footer">
          <button class="btn btn-secondary" data-bs-dismiss="modal" type="button">Cancelar</button>
          <button class="btn btn-danger" type="submit">Guardar</button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- 🟧 MODAL: EDITAR REPORTE -->
<div class="modal fade" id="modalEditarReporte" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-lg">
    <div class="modal-content">
      <div class="modal-header bg-warning">
        <h5 class="modal-title">Editar reporte de carrocería</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <form id="formEditarReporte" action="{{ route('carroceria.update', 0) }}" method="POST">
        @csrf
        @method('PUT')
        <div class="modal-body">
          <div class="row g-3">
            <div class="col-md-6">
              <label class="form-label">Zona afectada</label>
              <input type="text" name="zona_afectada" id="edit_zona" class="form-control" required>
            </div>
            <div class="col-md-6">
              <label class="form-label">Tipo de daño</label>
              <input type="text" name="tipo_danio" id="edit_danio" class="form-control" required>
            </div>
            <div class="col-md-6">
              <label class="form-label">Severidad</label>
              <select name="severidad" id="edit_severidad" class="form-select" required>
                <option value="Leve">Leve</option>
                <option value="Media">Media</option>
                <option value="Alta">Alta</option>
              </select>
            </div>
            <div class="col-md-6">
              <label class="form-label">Taller</label>
              <input type="text" name="taller" id="edit_taller" class="form-control">
            </div>
            <div class="col-md-6">
              <label class="form-label">Costo estimado</label>
              <input type="number" step="0.01" min="0" name="costo_estimado" id="edit_costo" class="form-control">
            </div>
            <div class="col-md-6">
              <label class="form-label">Estatus</label>
              <select name="estatus" id="edit_estatus" class="form-select" required>
                <option value="Pendiente">Pendiente</option>
                <option value="Cotizado">Cotizado</option>
                <option value="En proceso">En proceso</option>
                <option value="Terminado">Terminado</option>
              </select>
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button class="btn btn-secondary" data-bs-dismiss="modal" type="button">Cancelar</button>
          <button class="btn btn-warning" type="submit" id="btnGuardarCambios">Guardar cambios</button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- 🟦 MODAL: ELEGIR VEHÍCULO -->
<div class="modal fade" id="modalElegirVehiculo" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title"><i class="bi bi-car-front"></i> Elegir vehículo</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <input type="search" id="filtroVehiculos" class="form-control mb-3" placeholder="Filtrar por marca, modelo o placa…">
        <div class="table-responsive" style="max-height:50vh;overflow:auto">
          <table class="table table-sm table-hover">
            <thead>
              <tr><th>Marca</th><th>Modelo</th><th>Año</th><th>Placa</th><th></th></tr>
            </thead>
            <tbody id="tbodyVehiculos">
              @foreach($vehiculos ?? [] as $v)
              <tr data-busqueda="{{ strtolower($v->marca.' '.$v->modelo.' '.$v->placa) }}">
                <td>{{ $v->marca }}</td>
                <td>{{ $v->modelo }}</td>
                <td>{{ $v->anio ?? '-' }}</td>
                <td>{{ $v->placa }}</td>
                <td class="text-end">
                  <button type="button" class="btn btn-sm btn-outline-primary btnElegirVehiculo"
                          data-id="{{ $v->id_vehiculo }}"
                          data-texto="{{ $v->marca }} {{ $v->modelo }} ({{ $v->placa }})">
                    Elegir
                  </button>
                </td>
              </tr>
              @endforeach
              @if(empty($vehiculos) || count($vehiculos) === 0)
              <tr><td colspan="5" class="text-muted">No hay vehículos disponibles.</td></tr>
              @endif
            </tbody>
          </table>
        </div>
      </div>
      <div class="modal-footer">
        <button class="btn btn-secondary" data-bs-dismiss="modal" type="button">Cerrar</button>
      </div>
    </div>
  </div>
</div>
@endsection

@section('js-vistaCarroceria')
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
  // ==== FILTRO DE TABLA DE CARROCERÍA ====
  document.getElementById('filtroCarroceria').addEventListener('keyup', function() {
    const filtro = this.value.toLowerCase();
    document.querySelectorAll('#tblCarroceria tbody tr').forEach(tr => {
      const texto = tr.textContent.toLowerCase();
      tr.style.display = texto.includes(filtro) ? '' : 'none';
    });
  });

  // ==== FILTRO DE VEHÍCULOS ====
  const filtro = document.getElementById('filtroVehiculos');
  if (filtro) {
    filtro.addEventListener('input', () => {
      const q = filtro.value.trim().toLowerCase();
      document.querySelectorAll('#tbodyVehiculos tr').forEach(tr => {
        const hay = (tr.getAttribute('data-busqueda') || '').includes(q);
        tr.style.display = hay ? '' : 'none';
      });
    });
  }

  // Elegir vehículo
  document.querySelectorAll('.btnElegirVehiculo').forEach(btn => {
    btn.addEventListener('click', () => {
      const id = btn.dataset.id;
      const texto = btn.dataset.texto;
      document.getElementById('id_vehiculo').value = id;
      document.getElementById('vehiculoTexto').value = texto;
      const modalEl = document.getElementById('modalElegirVehiculo');
      bootstrap.Modal.getInstance(modalEl).hide();
    });
  });

  // EDITAR
  document.querySelectorAll('.btnEditar').forEach(btn => {
    btn.addEventListener('click', () => {
      const id = btn.dataset.id;
      document.getElementById('edit_zona').value = btn.dataset.zona || '';
      document.getElementById('edit_danio').value = btn.dataset.danio || '';
      document.getElementById('edit_severidad').value = btn.dataset.severidad || 'Leve';
      document.getElementById('edit_taller').value = btn.dataset.taller || '';
      document.getElementById('edit_costo').value = btn.dataset.costo || 0;
      document.getElementById('edit_estatus').value = btn.dataset.estatus || 'Pendiente';

      const form = document.getElementById('formEditarReporte');
      form.action = `{{ url('/admin/carroceria/update') }}/${id}`;
    });
  });

  // Evitar que se cierre modal principal al elegir vehículo
  const modalVehiculo = document.getElementById('modalElegirVehiculo');
  modalVehiculo.addEventListener('hidden.bs.modal', () => {
    const nuevo = document.getElementById('modalNuevoReporte');
    const modalNuevo = bootstrap.Modal.getInstance(nuevo);
    if (modalNuevo) modalNuevo.show();
  });
</script>
@endsection
