@extends('layouts.Admin')

@section('Titulo', 'Administración de Tarifas y Rutas')

@section('css')
<link rel="stylesheet" href="{{ asset('css/Categorias.css') }}">
<link rel="stylesheet" href="{{ asset('css/Dropoff.css') }}">
@endsection

@section('contenido')
<main class="main">

  <div class="head" style="display: flex; justify-content: space-between; align-items: center;">
    <div>
        <h1 class="h1" style="margin-bottom: 5px;">Dropoff y Delivery</h1>
    </div>
    <div style="display: flex; gap: 12px;">
        <button type="button" class="btn-top btn-top-azul" id="btnNuevaRuta">+ Nueva Ruta</button>
    </div>
  </div>

  <div class="tabs-nav">
      <button class="tab-btn active" onclick="switchTab('tab-categorias', this)">1. Categorías (Costo x KM)</button>
      <button class="tab-btn" onclick="switchTab('tab-viajes', this)">2. Matriz de Rutas y Viajes</button>
  </div>

  {{-- ===========================================================
       TAB 1: CATEGORÍAS (NO SE TOCA)
  =========================================================== --}}
  <div id="tab-categorias" class="tab-content active">
      <section class="card" style="border-radius: 12px; overflow: hidden; box-shadow: 0 4px 15px rgba(0,0,0,0.03);">
        <table class="table">
          <thead style="background: #f8fafc;">
            <tr>
              <th>Código</th>
              <th>Categoría de Auto</th>
              <th>Costo Base x KM</th>
              <th style="text-align: center;">Acción</th>
            </tr>
          </thead>
          <tbody>
            @foreach($categorias as $cat)
                <tr>
                    <td><strong style="color: #475569;">{{ $cat->codigo ?? 'N/A' }}</strong></td>
                    <td>{{ $cat->nombre }}</td>
                    <td class="mono" style="color: #166534; font-weight: bold; font-size: 15px;">${{ number_format($cat->costo_km ?? 0, 2) }}</td>
                    <td style="text-align: center;">
                        <button class="btn-icon btn-edit" onclick="openEditCategoria({{ $cat->id_categoria }}, @js($cat->nombre), {{ $cat->costo_km ?? 0 }})">
                            ✎ Ajustar Costo
                        </button>
                    </td>
                </tr>
            @endforeach
          </tbody>
        </table>
      </section>
  </div>

  {{-- ===========================================================
       TAB 2: MATRIZ DE RUTAS (agrupada por ciudad de origen)
  =========================================================== --}}
  <div id="tab-viajes" class="tab-content">

      <div class="filtros-container">
          <div class="filtro-item">
              <label>📍 Ciudad de origen</label>
              <select id="filtroOrigen" onchange="aplicarFiltros()">
                  <option value="todos">Todos los orígenes</option>
              </select>
          </div>

          <div class="filtro-item">
              <label>🚩 Destino</label>
              <select id="filtroDestino" onchange="aplicarFiltros()">
                  <option value="todos">Todos los destinos</option>
              </select>
          </div>

          <div class="divisor-vertical"></div>

          <div class="filtro-item" style="flex: 1.5;">
              <label>🚗 Simular Precio (Categoría de Auto)</label>
              <select id="filtroCategoria" onchange="aplicarFiltros()">
                  <option value="0" data-costokm="0">-- Seleccione una categoría --</option>
                  @foreach($categorias as $cat)
                      <option value="{{ $cat->id_categoria }}" data-costokm="{{ $cat->costo_km ?? 0 }}">
                          {{ $cat->nombre }} (${{ number_format($cat->costo_km ?? 0, 2) }}/km)
                      </option>
                  @endforeach
              </select>
          </div>
      </div>

      @forelse($ubicacionesPorCiudad as $ciudadNombre => $rutas)
        <section class="card ciudad-bloque"
                 data-ciudad="{{ strtolower($ciudadNombre) }}"
                 style="border-radius: 12px; overflow: hidden; box-shadow: 0 4px 15px rgba(0,0,0,0.03); margin-bottom: 22px;">
          <h2 class="ciudad-titulo">{{ $ciudadNombre }}</h2>
          <table class="table">
            <thead style="background: #f8fafc;">
              <tr>
                <th>🚩 Destino</th>
                <th>Distancia</th>
                <th style="text-align: right; padding-right: 20px;">Costo Estimado</th>
                <th>Visibilidad</th>
                <th style="text-align: center;">Acciones</th>
              </tr>
            </thead>
            <tbody>
              @foreach($rutas as $u)
                <tr class="fila-viaje"
                    data-origen="{{ strtolower($ciudadNombre) }}"
                    data-destino="{{ strtolower($u->destino) }}"
                    data-km="{{ $u->km }}">
                    <td>
                        <strong style="color: #0f172a;">{{ $u->destino }}</strong>
                        <br><small style="color: #94a3b8;">{{ $u->estado ?? '' }}</small>
                    </td>
                    <td>{{ $u->km }} km</td>
                    <td class="celda-costo" style="text-align: right; padding-right: 20px; color: #94a3b8; font-weight: normal;">
                        Seleccione auto...
                    </td>
                    <td>
                        @if($u->ver_usuario)
                            <span class="vis-pill vis-web">Web</span>
                        @endif
                        @if($u->ver_admin)
                            <span class="vis-pill vis-panel">Panel</span>
                        @endif
                        @if(!$u->ver_usuario && !$u->ver_admin)
                            <span class="vis-pill vis-none">Oculta</span>
                        @endif
                    </td>
                    <td>
                        <div class="btn-grupo">
                            <button type="button" class="btn-icon btn-edit"
                                onclick="abrirEditarKm({{ $u->id_ubicacion }}, @js($u->destino), @js($u->estado), {{ $u->km }}, {{ $u->id_ciudad_origen ?? 'null' }}, {{ $u->ver_usuario ? 1 : 0 }}, {{ $u->ver_admin ? 1 : 0 }})">✎ Editar</button>
                            <button type="button" class="btn-icon btn-del"
                                onclick="confirmarEliminar({{ $u->id_ubicacion }}, @js($u->destino))">🗑️</button>
                        </div>
                    </td>
                </tr>
              @endforeach
            </tbody>
          </table>
        </section>
      @empty
        <section class="card">
          <p class="sin-rutas">No hay rutas registradas.</p>
        </section>
      @endforelse

      <p id="sinRutas" class="sin-rutas" style="display:none;">No se encontraron rutas con ese criterio.</p>

  </div>

</main>

{{-- ===========================================================
     MODAL: NUEVA RUTA
=========================================================== --}}
<dialog id="modalUbicacion" class="modal">
  <form id="formUbicacion" class="modal-box">
    <div class="modal-head"><h2>Nueva Ruta</h2><button type="button" class="x" onclick="document.getElementById('modalUbicacion').close()">✕</button></div>

    <label class="label">Ciudad de origen</label>
    <select id="ub_id_ciudad_origen" class="input" required>
        <option value="">-- Seleccione la ciudad de salida --</option>
        @foreach($ciudades as $ciudad)
            <option value="{{ $ciudad->id_ciudad }}">{{ $ciudad->nombre }}{{ $ciudad->estado ? ', ' . $ciudad->estado : '' }}</option>
        @endforeach
    </select>

    <label class="label">Destino</label>
    <select id="ub_destino_select" class="input">
        <option value="">-- Seleccione un destino existente --</option>
        @php $destinosUnicos = collect($ubicaciones)->unique('destino'); @endphp
        @foreach($destinosUnicos as $d)
            <option value="{{ $d->destino }}" data-destino="{{ $d->destino }}" data-estado="{{ $d->estado }}">{{ $d->destino }}</option>
        @endforeach
        <option value="__nuevo__">➕ Nuevo destino...</option>
    </select>

    <div id="ub_destino_nuevo_wrap" style="display:none; margin-top:10px;">
        <label class="label">Nombre del nuevo destino</label>
        <input type="text" id="ub_destino" name="destino" class="input" placeholder="Ej: Aeropuerto de Monterrey">
    </div>

    <label class="label">Estado del destino</label>
    <input type="text" id="ub_estado" name="estado" class="input" placeholder="Ej: Nuevo León">

    <label class="label">Distancia desde la ciudad de origen (Kilómetros)</label>
    <input type="number" id="ub_km" name="km" class="input mono" step="0.1" min="0" required placeholder="Ej: 320">

    {{-- Visibilidad --}}
    <div class="visibilidad-grid" style="margin-top:15px;">
        <label class="check-vis">
            <input type="checkbox" id="ub_ver_usuario" checked>
            <span>Permitir ver en página web (usuario)</span>
        </label>
        <label class="check-vis">
            <input type="checkbox" id="ub_ver_admin" checked>
            <span>Permitir ver en panel (admin)</span>
        </label>
    </div>

    <div class="modal-actions" style="margin-top: 20px;">
        <button class="btn-add" style="background: #0284c7;" type="submit">Guardar Ruta</button>
    </div>
  </form>
</dialog>

{{-- ===========================================================
     MODAL: EDITAR (origen + km + visibilidad; destino y estado bloqueados)
=========================================================== --}}
<dialog id="modalEditarKm" class="modal">
  <form id="formEditarKm" class="modal-box">
    <div class="modal-head"><h2>Editar Ruta</h2><button type="button" class="x" onclick="document.getElementById('modalEditarKm').close()">✕</button></div>

    <input type="hidden" id="ek_id">

    <label class="label">Destino (no editable)</label>
    <input type="text" id="ek_destino" class="input" readonly style="background:#f1f5f9;">

    <label class="label">Estado del destino (no editable)</label>
    <input type="text" id="ek_estado" class="input" readonly style="background:#f1f5f9;">

    <label class="label">Ciudad de origen</label>
    <select id="ek_id_ciudad_origen" class="input" required>
        <option value="">-- Seleccione la ciudad de salida --</option>
        @foreach($ciudades as $ciudad)
            <option value="{{ $ciudad->id_ciudad }}">{{ $ciudad->nombre }}{{ $ciudad->estado ? ', ' . $ciudad->estado : '' }}</option>
        @endforeach
    </select>

    <label class="label">Kilómetros</label>
    <input type="number" id="ek_km" class="input mono" step="0.1" min="0" required>

    {{-- Visibilidad --}}
    <div class="visibilidad-grid" style="margin-top:15px;">
        <label class="check-vis">
            <input type="checkbox" id="ek_ver_usuario">
            <span>Permitir ver en página web (usuario)</span>
        </label>
        <label class="check-vis">
            <input type="checkbox" id="ek_ver_admin">
            <span>Permitir ver en panel (admin)</span>
        </label>
    </div>

    <div class="modal-actions" style="margin-top: 20px;">
        <button class="btn-add" style="background: #f59e0b;" type="submit">Actualizar Ruta</button>
    </div>
  </form>
</dialog>

{{-- ===========================================================
     MODAL: EDITAR COSTO KM CATEGORÍA (intacto)
=========================================================== --}}
<dialog id="modalCosto" class="modal">
  <form id="formCosto" class="modal-box">
    <div class="modal-head"><h2>Actualizar Costo por Kilómetro</h2><button type="button" class="x" onclick="document.getElementById('modalCosto').close()">✕</button></div>
    <input type="hidden" id="c_id_categoria">
    <label class="label">Categoría</label>
    <input type="text" id="c_nombre" class="input" readonly style="background: #f1f5f9;">
    <label class="label">Costo por Kilómetro ($)</label>
    <input type="number" id="c_costo" name="costo_km" class="input mono" step="0.01" min="0" required>
    <div class="modal-actions" style="margin-top: 20px;"><button class="btn-add" style="background: #f59e0b;" type="submit">Actualizar Costo</button></div>
  </form>
</dialog>

@endsection

@section('js')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<meta name="csrf-token" content="{{ csrf_token() }}">
<script>
  window.DROPOFF_BASE_URL = "{{ url('admin/dropoff') }}";
  document.getElementById('btnNuevaRuta').addEventListener('click', function () {
      document.getElementById('modalUbicacion').showModal();
  });
</script>
<script src="{{ asset('js/Dropoff2.js') }}"></script>
@endsection
