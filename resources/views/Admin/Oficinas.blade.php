@extends('layouts.Admin')
@section('Titulo', 'Administración de Oficinas')

@section('css')
<link rel="stylesheet" href="{{ asset('css/Categorias.css') }}">
<link rel="stylesheet" href="{{ asset('css/Oficinas.css') }}">
@endsection

@section('contenido')
<main class="main">

  <div class="head" style="display: flex; justify-content: space-between; align-items: center;">
    <h1 class="h1">Gestión de Oficinas</h1>
    <button type="button" class="btn-action-top btn-red" id="btnNuevaOficina">+ Nueva Oficina</button>
  </div>

  {{-- Filtro de búsqueda --}}
  <div class="oficinas-filtro">
    <input type="text" id="buscadorOficina" class="input" placeholder="🔍 Buscar por nombre o ciudad...">
  </div>

  {{-- Listado agrupado por ciudad --}}
  @forelse($sucursalesPorCiudad as $ciudad => $oficinas)
    <section class="card ciudad-bloque" data-ciudad="{{ \Illuminate\Support\Str::lower($ciudad) }}">
      <h2 class="ciudad-titulo">{{ $ciudad ?? 'Sin ciudad' }}</h2>
      <table class="table">
        <thead>
          <tr>
            <th style="width: 9%;">Estado</th>
            <th style="width: 17%;">Sucursal</th>
            <th style="width: 19%;">Dirección</th>
            <th style="width: 10%;">Teléfono</th>
            <th style="width: 12%;">Horarios</th>
            <th style="width: 9%; text-align:center;">Imágenes</th>
            <th style="width: 11%;">Visibilidad</th>
            <th style="width: 13%; text-align:center;">Acciones</th>
          </tr>
        </thead>
        <tbody>
          @foreach($oficinas as $sucursal)
            @php
                $horarioData = is_string($sucursal->horario_json) ? json_decode($sucursal->horario_json) : null;
                $textoHorario = $horarioData->horario ?? 'No definido';
                $parts = explode(' ', $textoHorario);
            @endphp
            <tr class="fila-oficina"
                data-nombre="{{ \Illuminate\Support\Str::lower($sucursal->nombre) }}"
                data-ciudad="{{ \Illuminate\Support\Str::lower($ciudad) }}">
                <td><span class="badge-estado">{{ $sucursal->ciudad_estado ?? 'N/A' }}</span></td>
                <td><strong>{{ $sucursal->nombre }}</strong></td>
                <td><span style="color:#64748b; font-size:13px;">{{ $sucursal->direccion }}</span></td>
                <td>{{ $sucursal->telefono ?? 'N/A' }}</td>
                <td>
                    @if($textoHorario === '24/7')
                        <span class="horario-pill"><span class="horario-dias">24/7</span></span>
                    @elseif(count($parts) >= 4)
                        <div class="horario-pill">
                            <span class="horario-dias">{{ $parts[0] }} A {{ $parts[2] }}</span>
                            <span class="horario-horas">{{ implode(' ', array_slice($parts, 3)) }}</span>
                        </div>
                    @else
                        <span style="font-size:12px; font-weight:700; color:#475569;">{{ $textoHorario }}</span>
                    @endif
                </td>
                <td style="text-align:center;">
                    <button type="button" class="btn-mini btn-ver-img"
                        onclick='verImagenes(@json($sucursal->id_sucursal), @json($sucursal->nombre), @json((bool)$sucursal->tiene_imagen_1), @json((bool)$sucursal->tiene_imagen_2))'>
                        🖼️ Ver
                    </button>
                </td>
                <td>
                    @if($sucursal->ver_usuario)
                        <span class="vis-pill vis-web">Web</span>
                    @endif
                    @if($sucursal->ver_admin)
                        <span class="vis-pill vis-panel">Panel</span>
                    @endif
                    @if(!$sucursal->ver_usuario && !$sucursal->ver_admin)
                        <span class="vis-pill vis-none">Oculta</span>
                    @endif
                </td>
                <td style="text-align:center; white-space:nowrap;">
                    <button type="button" class="btn-mini btn-mapa"
                        onclick="verMapa(@js($sucursal->url_direccion))"
                        @if(empty($sucursal->url_direccion)) disabled @endif>
                        📍 Mapa
                    </button>
                    <button type="button" class="btn-mini btn-edit"
                        onclick='abrirEditar(@json($sucursal), @json($textoHorario))'>✏️ Editar</button>
                    <button type="button" class="btn-mini btn-del"
                        onclick="confirmarEliminar({{ $sucursal->id_sucursal }}, @js($sucursal->nombre))">🗑️</button>
                </td>
            </tr>
          @endforeach
        </tbody>
      </table>
    </section>
  @empty
    <section class="card">
      <p style="text-align:center; padding:30px; color:#94a3b8;">No hay sucursales registradas.</p>
    </section>
  @endforelse

  {{-- Mensaje cuando el filtro no encuentra nada --}}
  <p id="sinResultados" style="display:none; text-align:center; padding:30px; color:#94a3b8;">
    No se encontraron oficinas con ese criterio.
  </p>

</main>

{{-- =========================
    MODAL: NUEVA OFICINA
========================= --}}
<dialog id="modalNueva" class="modal">
  <form method="POST" action="{{ route('oficinas.store') }}" class="modal-box" id="formNueva">
    @csrf

    <div class="modal-head">
      <h2>Agregar Nueva Oficina</h2>
      <button type="button" class="x" onclick="document.getElementById('modalNueva').close()">✕</button>
    </div>

    <label class="label">Ciudad / Estado</label>
    <select name="id_ciudad" class="input select-ciudad" required>
        <option value="">-- Seleccione una ciudad --</option>
        @foreach($ciudades as $ciudad)
            <option value="{{ $ciudad->id_ciudad }}">{{ $ciudad->nombre }}, {{ $ciudad->estado }}</option>
        @endforeach
    </select>

    <label class="label">Nombre (SUCURSAL DE RETIRO)</label>
    <input type="text" name="nombre" class="input" placeholder="Ej: Querétaro Aeropuerto" required>

    <label class="label">Dirección Completa</label>
    <textarea name="direccion" class="input" rows="2" placeholder="Calle, Número, Colonia..." required></textarea>

    <label class="label">URL de la dirección (Google Maps)</label>
    <input type="url" name="url_direccion" class="input" placeholder="https://maps.google.com/...">

    <div style="margin-top: 10px;">
        <label class="label">Teléfono</label>
        <input type="tel" name="telefono" class="input only-numbers" placeholder="Ej: 4421234567" style="width:100%;">
    </div>

    <div style="margin-top: 15px;">
        <label class="label">Horario de Atención</label>

        <label class="check-vis" style="margin:6px 0;">
            <input type="checkbox" id="nueva_24h" checked>
            <span>Abierto 24 horas (24/7)</span>
        </label>

        <div class="schedule-container" id="nueva_schedule">
            <select id="nueva_dia_inicio" style="flex:1;">
                <option value="LUNES">LUNES</option><option value="MARTES">MARTES</option>
                <option value="MIÉRCOLES">MIÉRCOLES</option><option value="JUEVES">JUEVES</option>
                <option value="VIERNES">VIERNES</option><option value="SÁBADO">SÁBADO</option>
                <option value="DOMINGO">DOMINGO</option>
            </select>
            <span class="schedule-sep">A</span>
            <select id="nueva_dia_fin" style="flex:1;">
                <option value="VIERNES">VIERNES</option><option value="LUNES">LUNES</option>
                <option value="MARTES">MARTES</option><option value="MIÉRCOLES">MIÉRCOLES</option>
                <option value="JUEVES">JUEVES</option><option value="SÁBADO">SÁBADO</option>
                <option value="DOMINGO">DOMINGO</option>
            </select>
            <div class="schedule-divider"></div>
            <select id="nueva_hora_inicio" class="sel-hora" style="flex:1;"></select>
            <span class="schedule-sep">-</span>
            <select id="nueva_hora_fin" class="sel-hora" style="flex:1;"></select>
        </div>
        <input type="hidden" name="horario" id="nueva_horario_hidden">
    </div>

    {{-- Fotos --}}
    <div style="margin-top:15px;">
        <label class="label">Fotos de la oficina (máximo 2)</label>
        <div class="fotos-grid">
            <div class="foto-slot">
                <input type="file" accept="image/*" class="foto-input" data-target="nueva_imagen_1">
                <img class="foto-preview" id="nueva_preview_1" style="display:none;">
                <span class="foto-hint">Foto 1</span>
                <input type="hidden" name="imagen_1" id="nueva_imagen_1">
            </div>
            <div class="foto-slot">
                <input type="file" accept="image/*" class="foto-input" data-target="nueva_imagen_2">
                <img class="foto-preview" id="nueva_preview_2" style="display:none;">
                <span class="foto-hint">Foto 2</span>
                <input type="hidden" name="imagen_2" id="nueva_imagen_2">
            </div>
        </div>
    </div>

    {{-- Visibilidad --}}
    <div class="visibilidad-grid" style="margin-top:15px;">
        <label class="check-vis">
            <input type="checkbox" name="ver_usuario" value="1" checked>
            <span>Permitir ver en página web (usuario)</span>
        </label>
        <label class="check-vis">
            <input type="checkbox" name="ver_admin" value="1" checked>
            <span>Permitir ver en panel (admin)</span>
        </label>
    </div>

    <div class="modal-actions" style="margin-top:20px;">
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
    <select name="id_ciudad" id="edit_id_ciudad" class="input select-ciudad" required>
        <option value="">-- Seleccione una ciudad --</option>
        @foreach($ciudades as $ciudad)
            <option value="{{ $ciudad->id_ciudad }}">{{ $ciudad->nombre }}, {{ $ciudad->estado }}</option>
        @endforeach
    </select>

    <label class="label">Nombre (SUCURSAL DE RETIRO)</label>
    <input type="text" name="nombre" id="edit_nombre" class="input" required>

    <label class="label">Dirección Completa</label>
    <textarea name="direccion" id="edit_direccion" class="input" rows="2" required></textarea>

    <label class="label">URL de la dirección (Google Maps)</label>
    <input type="url" name="url_direccion" id="edit_url_direccion" class="input" placeholder="https://maps.google.com/...">

    <div style="margin-top:10px;">
        <label class="label">Teléfono</label>
        <input type="tel" name="telefono" id="edit_telefono" class="input only-numbers" style="width:100%;">
    </div>

    <div style="margin-top:15px;">
        <label class="label">Horario de Atención</label>

        <label class="check-vis" style="margin:6px 0;">
            <input type="checkbox" id="edit_24h">
            <span>Abierto 24 horas (24/7)</span>
        </label>

        <div class="schedule-container" id="edit_schedule">
            <select id="edit_dia_inicio" style="flex:1;">
                <option value="LUNES">LUNES</option><option value="MARTES">MARTES</option>
                <option value="MIÉRCOLES">MIÉRCOLES</option><option value="JUEVES">JUEVES</option>
                <option value="VIERNES">VIERNES</option><option value="SÁBADO">SÁBADO</option>
                <option value="DOMINGO">DOMINGO</option>
            </select>
            <span class="schedule-sep">A</span>
            <select id="edit_dia_fin" style="flex:1;">
                <option value="VIERNES">VIERNES</option><option value="LUNES">LUNES</option>
                <option value="MARTES">MARTES</option><option value="MIÉRCOLES">MIÉRCOLES</option>
                <option value="JUEVES">JUEVES</option><option value="SÁBADO">SÁBADO</option>
                <option value="DOMINGO">DOMINGO</option>
            </select>
            <div class="schedule-divider"></div>
            <select id="edit_hora_inicio" class="sel-hora" style="flex:1;"></select>
            <span class="schedule-sep">-</span>
            <select id="edit_hora_fin" class="sel-hora" style="flex:1;"></select>
        </div>
        <input type="hidden" name="horario" id="edit_horario_hidden">
    </div>

    {{-- Fotos --}}
    <div style="margin-top:15px;">
        <label class="label">Fotos de la oficina (máximo 2)</label>
        <p class="foto-nota">Si no subes una nueva, se conserva la actual.</p>
        <div class="fotos-grid">
            <div class="foto-slot">
                <input type="file" accept="image/*" class="foto-input" data-target="edit_imagen_1">
                <img class="foto-preview" id="edit_preview_1" style="display:none;">
                <span class="foto-hint">Foto 1</span>
                <input type="hidden" name="imagen_1" id="edit_imagen_1">
            </div>
            <div class="foto-slot">
                <input type="file" accept="image/*" class="foto-input" data-target="edit_imagen_2">
                <img class="foto-preview" id="edit_preview_2" style="display:none;">
                <span class="foto-hint">Foto 2</span>
                <input type="hidden" name="imagen_2" id="edit_imagen_2">
            </div>
        </div>
    </div>

    {{-- Visibilidad --}}
    <div class="visibilidad-grid" style="margin-top:15px;">
        <label class="check-vis">
            <input type="checkbox" name="ver_usuario" id="edit_ver_usuario" value="1">
            <span>Permitir ver en página web (usuario)</span>
        </label>
        <label class="check-vis">
            <input type="checkbox" name="ver_admin" id="edit_ver_admin" value="1">
            <span>Permitir ver en panel (admin)</span>
        </label>
    </div>

    <div class="modal-actions" style="margin-top:20px;">
      <button class="btn-add" type="submit">Actualizar Oficina</button>
      <button class="btn-ghost" type="button" onclick="document.getElementById('modalEditar').close()">Cancelar</button>
    </div>
  </form>
</dialog>

{{-- =========================
    MODAL: GALERÍA DE IMÁGENES
========================= --}}
<dialog id="modalGaleria" class="modal">
  <div class="modal-box">
    <div class="modal-head">
      <h2 id="galeria_titulo">Imágenes de la oficina</h2>
      <button type="button" class="x" onclick="document.getElementById('modalGaleria').close()">✕</button>
    </div>
    <div id="galeria_contenido" class="galeria-grid"></div>
    <div class="modal-actions" style="margin-top:16px;">
      <button class="btn-ghost" type="button" onclick="document.getElementById('modalGaleria').close()">Cerrar</button>
    </div>
  </div>
</dialog>

{{-- Formulario oculto para eliminar --}}
<form id="formEliminar" method="POST" style="display:none;">
    @csrf
    @method('DELETE')
</form>

@endsection

@section('js')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://cdn.jsdelivr.net/npm/browser-image-compression@2.0.2/dist/browser-image-compression.js"></script>
<script>
  window.OFICINAS_BASE_URL = "{{ url('oficinas') }}";
  window.OFICINAS_FLASH = {
      success: @json(session('success')),
      error: @json(session('error')),
      errors: @json($errors->all())
  };
  window.verMapa = function (url) {
      if (url) window.open(url, '_blank', 'noopener');
  };
</script>
<script src="{{ asset('js/Oficinas.js') }}"></script>
@endsection
