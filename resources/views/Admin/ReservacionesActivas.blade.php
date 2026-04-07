@extends('layouts.Ventas')
@section('Titulo', 'Reservaciones Activas')

@section('css-vistaReservacionesActivas')
  <link rel="stylesheet" href="{{ asset('css/reservacionesActivas.css') }}">
@endsection

@section('contenidoReservacionesActivas')
<main class="main">
  <h1 class="h1">Bookings</h1>

  @php
    // ¿El filtro actual es Aeropuerto?
    $esAeropuerto = request('sucursal') === '1';
    // Número de columnas según si mostramos No. Vuelo o no
    $cols = $esAeropuerto ? 12 : 11;

    // Por si no viene del controller (evita error)
    $reservaciones_anteriores = $reservaciones_anteriores ?? [];
  @endphp

  {{-- ===================== 🔍 FILTROS ===================== --}}
  <form method="GET" class="toolbar">

    {{-- 🔵 Buscador por nombre o correo --}}
    <input
      id="q"
      name="q"
      class="input"
      type="search"
      placeholder="Buscar por nombre o correo…"
      value="{{ request('q') }}"
    >

    {{-- Select desplegable para sucursal (ubicación) --}}
    <select
      id="fSucursal"
      name="sucursal"
      class="input select-ubicacion"
      style="max-width: 220px;"
      onchange="this.form.submit()"
    >
      <option value=""  {{ request('sucursal') == '' ? 'selected' : '' }}>Todas las ubicaciones</option>
      <option value="1" {{ request('sucursal') == '1' ? 'selected' : '' }}>Aeropuerto de Querétaro</option>
      <option value="2" {{ request('sucursal') == '2' ? 'selected' : '' }}>Central de autobuses</option>
      <option value="3" {{ request('sucursal') == '3' ? 'selected' : '' }}>Central Park</option>
    </select>

    <select
  id="fSucursal2"
  name="sucursal2"
  class="input select-ubicacion"
  style="max-width: 220px;"
  onchange="this.form.submit()"
>
      <option value="0"  {{ request('sucursal2') == '' ? 'selected' : '' }}>Segundo filtro</option>
      <option value="1" {{ request('sucursal2') == '1' ? 'selected' : '' }}>Aeropuerto de Querétaro</option>
      <option value="2" {{ request('sucursal2') == '2' ? 'selected' : '' }}>Central de autobuses</option>
      <option value="3" {{ request('sucursal2') == '3' ? 'selected' : '' }}>Central Park</option>
    </select>

    <select
  name="per_page"
  class="input"
  style="max-width:120px;"
  onchange="this.form.submit()"
>
  <option value="10"  {{ request('per_page') == 10 ? 'selected' : '' }}>10</option>
  <option value="20"  {{ request('per_page') == 20 ? 'selected' : '' }}>20</option>
  <option value="50"  {{ request('per_page') == 50 ? 'selected' : '' }}>50</option>
  <option value="100" {{ request('per_page') == 100 ? 'selected' : '' }}>100</option>
</select>

<input
  type="date"
  name="fecha_inicio"
  class="input"
  value="{{ request('fecha_inicio') }}"
  onchange="this.form.submit()"
>

<input
  type="date"
  name="fecha_fin"
  class="input"
  value="{{ request('fecha_fin') }}"
  onchange="this.form.submit()"
>


    <span class="badge gray">Total <b id="count">{{ $reservaciones->total() }}</b></span>

    {{-- ✅ Botón exportar --}}
    <button
      type="button"
      class="btn primary"
      id="btnExportExcel"
      style="margin-left:auto; display:flex; align-items:center; gap:8px;"
    >
      ⬇️ Exportar Excel
    </button>

    {{-- ✅ NUEVO: Reservaciones anteriores --}}
    <button
      type="button"
      class="btn gray"
      id="btnPrevBookings"
      style="display:flex; align-items:center; gap:8px;"
      title="Ver reservaciones del día anterior"
    >
      🗓️ Reservaciones anteriores
    </button>

  </form>

  <!-- ======================= 📋 TABLA ACTUAL ======================= -->
  {{-- ✅ ID para que exporte solo esta tabla --}}
  <section id="tablaActivas" class="table {{ $esAeropuerto ? 'is-airport' : '' }}" data-cols="{{ $cols }}">
    <div class="thead">
      <div></div>
      <div>No. de Reservacion</div>
      <div>Check in</div>
      <div>Hora (IN)</div>

      {{-- Solo mostramos la columna No. Vuelo si es Aeropuerto --}}
      @if($esAeropuerto)
        <div>No. Vuelo</div>
      @endif

      <div>Categoría</div>
      <div>Días</div>
      <div>Nombre Completo</div>
      <div>Celular</div>
      <div>Correo</div>
      <div>Estatus de pago</div>
      <div>Total</div>
      {{--<div>Acciones</div> --}}
    </div>

    <div class="tbody">
      @forelse ($reservaciones as $r)
        @php
          $nombreCompleto = trim((string)($r->nombre_completo ?? ''));

          if ($nombreCompleto === '') {
            $nombreCompleto = trim((string)($r->nombre_cliente ?? '') . ' ' . (string)($r->apellidos_cliente ?? ''));
          }

          if ($nombreCompleto === '') {
            $nombreCompleto = trim((string)($r->nombre_cliente ?? ''));
          }

          if ($nombreCompleto === '') $nombreCompleto = '—';

          $inicio = \Carbon\Carbon::parse($r->fecha_inicio);
          $fin    = \Carbon\Carbon::parse($r->fecha_fin);
          $dias   = $inicio->diffInDays($fin);

          $horaIn = $r->hora_retiro
            ? \Carbon\Carbon::parse($r->hora_retiro)->format('H:i')
            : '—';
        @endphp

        <div class="row"
             data-codigo="{{ $r->codigo }}"
             data-cliente="{{ $nombreCompleto }}"
             data-email="{{ $r->email_cliente }}"
             data-numero="{{ $r->telefono_cliente }}"
             data-categoria="{{ $r->categoria }}"
             data-fecha-salida="{{ \Carbon\Carbon::parse($r->fecha_inicio)->format('Y-m-d') }}"
             data-estado="{{ $r->estado }}"
             data-sucursal="{{ $r->sucursal_retiro }}"

            data-hora_retiro="{{ $r->hora_retiro }}"
            data-fecha_fin="{{ \Carbon\Carbon::parse($r->fecha_fin)->format('Y-m-d') }}"
            data-hora_entrega="{{ $r->hora_entrega }}"
             >

        <div>
            <button type="button" class="btn-plus">+</button>
        </div>

          <div>{{ $r->codigo }}</div>
          <div>{{ \Carbon\Carbon::parse($r->fecha_inicio)->format('d/m/Y') }}</div>
          <div>{{ $horaIn }}</div>

          @if($esAeropuerto)
            <div>{{ $r->no_vuelo ?? '—' }}</div>
          @endif

          <div>{{ $r->categoria }}</div>
          <div>{{ $dias }}</div>
          <div>{{ $nombreCompleto }}</div>
          <div>{{ $r->telefono_cliente ?? '—' }}</div>
          <div>{{ $r->email_cliente ?? '—' }}</div>

          <div>
            @php
              $estado = $r->estado;
              $color = match($estado) {
                'confirmada' => 'ok',
                'pendiente_pago' => 'warn',
                'hold' => 'gray',
                'cancelada' => 'danger',
                default => 'gray'
              };
            @endphp
            <span class="state {{ $color }}">{{ ucfirst($estado) }}</span>
          </div>

          <div>${{ number_format($r->total, 2) }} MXN</div>

        </div>

<div class="row-detail" style="display:none;">
  <div style="grid-column: 1 / -1; padding:15px; background:#f9f9f9;">

    <b>Reservación Confirmada el:</b> {{ \Carbon\Carbon::parse($r->created_at)->format('d/m/Y') }} <br>
    <b>Datos de Contacto:</b> MEXICO (MX) {{ $r->telefono_cliente }} <br>
    <b>Entrega:</b> {{ \Carbon\Carbon::parse($r->fecha_inicio)->format('d/m/Y') }} a las {{ $r->hora_retiro ? \Carbon\Carbon::parse($r->hora_retiro)->format('H:i') : '—' }} HRS<br>
    <b>Devolución:</b> {{ \Carbon\Carbon::parse($r->fecha_fin)->format('d/m/Y') }} a las {{ $r->hora_entrega ? \Carbon\Carbon::parse($r->hora_entrega)->format('H:i') : '—' }} HRS<br>
    <b>Total(MXN):</b> ${{ number_format($r->total,2)}} - Forma de pago: ({{ $r->metodo_pago}}) <br>
    <b>Vehículo Requerido:</b> {{ $r->categoria }} | {{ $r->categoria_nombre ?? 'Sin asignar' }} {{ $r->transmision ?? 'Sin transmisión' }} {{ $r->categoria_descripcion ?? '' }} |
Costo online: ${{ number_format($r->precio_dia, 2) }} | Costo oficina: ${{ number_format($r->precio_dia * 1.15, 2) }} <br>
    <b>Número de vuelo:</b> {{ $r->no_vuelo ?? '—' }} <br>
    <b>Adicionales Requeridos:</b><br>
@php
  $extras = $servicios[$r->id_reservacion] ?? [];
@endphp

@if(count($extras))
  @foreach($extras as $e)
    - {{ $e->nombre }} (x{{ $e->cantidad }}) <br>
  @endforeach
@else
  <span style="color:#999;">Ninguno</span><br>
@endif

    <b>Acciones:</b><br>

<div style="margin-top:8px; display:flex; gap:10px; flex-wrap:wrap;">

  {{-- ✏️ Editar --}}
  <button
  type="button"
  class="btn gray"
  onclick="window.location.href='/admin/reservaciones/{{ $r->id_reservacion }}/editar'"
>
  ✏️ Editar Reservación
</button>

  {{-- 🗑️ Eliminar --}}
 <button
  type="button"
  class="iconbtn more"
  title="Más acciones"
  data-open-actions
  data-id="{{ $r->id_reservacion }}"
  data-codigo="{{ $r->codigo }}"
  data-delete-url="{{ route('rutaEliminarReservacionActiva', $r->id_reservacion) }}"
>
  🗑️ Cancelar Reservación
</button>

  {{-- 📧 Correo --}}
  <button
  type="button"
  class="btn primary"
  onclick="reenviarCorreo({{ $r->id_reservacion }}, this)"
>
  📧 Reenviar correo de reservación
</button>

  {{-- 🚗 Apartar auto --}}
<button
  type="button"
  class="btn success btn-apartar-auto"
  data-id="{{ $r->id_reservacion }}"
>
  🚗 Apartar auto
</button>

</div>

  </div>
</div>

      @empty
        <div class="row">
          <div style="grid-column: 1 / -1; text-align:center;">No hay reservaciones activas.</div>
        </div>
      @endforelse
    </div>
  </section>

  {{-- ==========================================================
     🗓️ MODAL: RESERVACIONES ANTERIORES (AYER)
     ✅ TABLA IDÉNTICA + MISMAS ACCIONES
  =========================================================== --}}
  <div class="pop" id="modalPrev" aria-hidden="true">
    <div class="box box-xl">
      <header>
        <div>
          <div id="pTitle">Reservaciones anteriores</div>
          <span>Bookings del día anterior · Total: <b id="countPrev">{{ count($reservaciones_anteriores) }}</b></span>
        </div>
        <button type="button" id="pClose">&times;</button>
      </header>

      <div class="cnt table-cnt">
        <section id="tablaPrevias" class="table {{ $esAeropuerto ? 'is-airport' : '' }}" data-cols="{{ $cols }}">
          <div class="thead">
            <div>No. de Reservacion</div>
            <div>Check in</div>
            <div>Hora (IN)</div>

            @if($esAeropuerto)
              <div>No. Vuelo</div>
            @endif

            <div>Categoría</div>
            <div>Días</div>
            <div>Nombre Completo</div>
            <div>Celular</div>
            <div>Correo</div>
            <div>Estatus de pago</div>
            <div>Total</div>
            <div>Acciones</div>
          </div>

          <div class="tbody">
            @forelse ($reservaciones_anteriores as $r)
              @php
                $nombreCompleto = trim((string)($r->nombre_completo ?? ''));

                if ($nombreCompleto === '') {
                  $nombreCompleto = trim((string)($r->nombre_cliente ?? '') . ' ' . (string)($r->apellidos_cliente ?? ''));
                }

                if ($nombreCompleto === '') {
                  $nombreCompleto = trim((string)($r->nombre_cliente ?? ''));
                }

                if ($nombreCompleto === '') $nombreCompleto = '—';

                $inicio = \Carbon\Carbon::parse($r->fecha_inicio);
                $fin    = \Carbon\Carbon::parse($r->fecha_fin);
                $dias   = $inicio->diffInDays($fin);

                $horaIn = $r->hora_retiro
                  ? \Carbon\Carbon::parse($r->hora_retiro)->format('H:i')
                  : '—';
              @endphp

              <div class="row"
                   data-id="{{ $r->id_reservacion }}"
                   data-codigo="{{ $r->codigo }}"
                   data-cliente="{{ $nombreCompleto }}"
                   data-email="{{ $r->email_cliente }}"
                   data-numero="{{ $r->telefono_cliente }}"
                   data-categoria="{{ $r->categoria }}"
                   data-fecha-salida="{{ \Carbon\Carbon::parse($r->fecha_inicio)->format('Y-m-d') }}"
                   data-estado="{{ $r->estado }}"
                   data-sucursal="{{ $r->sucursal_retiro }}">

                <div>{{ $r->codigo }}</div>
                <div>{{ \Carbon\Carbon::parse($r->fecha_inicio)->format('d/m/Y') }}</div>
                <div>{{ $horaIn }}</div>

                @if($esAeropuerto)
                  <div>{{ $r->no_vuelo ?? '—' }}</div>
                @endif

                <div>{{ $r->categoria }}</div>
                <div>{{ $dias }}</div>
                <div>{{ $nombreCompleto }}</div>
                <div>{{ $r->telefono_cliente ?? '—' }}</div>
                <div>{{ $r->email_cliente ?? '—' }}</div>

                <div>
                  @php
                    $estado = $r->estado;
                    $color = match($estado) {
                      'confirmada' => 'ok',
                      'pendiente_pago' => 'warn',
                      'hold' => 'gray',
                      'cancelada' => 'danger',
                      default => 'gray'
                    };
                  @endphp
                  <span class="state {{ $color }}">{{ ucfirst($estado) }}</span>
                </div>

                <div>${{ number_format($r->total, 2) }} MXN</div>

                <div class="actions-wrap">
                  <button
                    type="button"
                    class="iconbtn more"
                    title="Más acciones"
                    data-open-actions
                    data-id="{{ $r->id_reservacion }}"
                    data-codigo="{{ $r->codigo }}"
                    data-delete-url="{{ route('rutaEliminarReservacionActiva', $r->id_reservacion) }}"
                  >
                    ⋯
                  </button>
                </div>

              </div>
            @empty
              <div class="row">
                <div style="grid-column: 1 / -1; text-align:center;">No hay reservaciones anteriores.</div>
              </div>
            @endforelse
          </div>
        </section>
      </div>

      <div class="actions">
        <button type="button" class="btn gray" id="pCancel">Cerrar</button>
      </div>
    </div>
  </div>

  {{-- ============================
       🪟 MODAL DETALLE RESERVACIÓN
  ============================ --}}
  <div class="pop" id="modal">
    <div class="box">
      <header>
        <div>
          <div id="mTitle">Contrato reservación</div>
        </div>
        <button type="button" id="mClose">&times;</button>
      </header>

      <div class="cnt">

        <div class="kv"><strong>Fechas -</strong><span id="mFechas">—</span></div>
        <div class="kv"><strong>Vehículo -</strong><span id="mVehiculo">—</span></div>
        <div class="kv"><strong>Forma de pago -</strong><span id="mFormaPago">—</span></div>
      </div>

      <div class="actions">
        <button type="button" class="btn gray" id="mCancel">Cerrar</button>
        {{-- <button type="button" class="btn gray" id="mEdit">Editar</button> --}}
        <button type="button" class="btn primary" id="mGo">Capturar contrato</button>
      </div>
    </div>
  </div>

  {{-- ============================
     🪟 MODAL EDICIÓN RESERVACIÓN
  ============================ --}}
  <div class="pop" id="modalEdit">
    <div class="box">
      <header>
        <div>
          <div id="eTitle">Editar datos</div>
          <span>Solo se actualizan datos del cliente y fechas</span>
        </div>
        <button type="button" id="eClose">&times;</button>
      </header>

      <div class="cnt">
        <div class="kv"><strong>Nombre</strong>
          <input class="input" id="eNombre" type="text" />
        </div>

        <div class="kv"><strong>Correo</strong>
          <input class="input" id="eCorreo" type="email" />
        </div>

        <div class="kv"><strong>Teléfono</strong>
          <input class="input" id="eTelefono" type="text" />
        </div>

        <div class="kv"><strong>Salida (fecha)</strong>
          <input class="input" id="eFechaInicio" type="date" />
        </div>

        <div class="kv"><strong>Salida (hora)</strong>
          <input class="input" id="eHoraRetiro" type="time" />
        </div>

        <div class="kv"><strong>Entrega (fecha)</strong>
          <input class="input" id="eFechaFin" type="date" />
        </div>

        <div class="kv"><strong>Entrega (hora)</strong>
          <input class="input" id="eHoraEntrega" type="time" />
        </div>
      </div>

      <div class="actions">
        <button type="button" class="btn gray" id="eCancel">Cancelar</button>
        <button type="button" class="btn primary" id="eSave">Guardar cambios</button>
      </div>
    </div>
  </div>

  {{-- ============================
     🪟 MODAL ACCIONES (No Show / Cancelar / Eliminar)
  ============================= --}}
  <div class="pop" id="modalActions" aria-hidden="true">
    <div class="box box-sm">
      <header>
        <div>
          <div id="aTitle">Acciones</div>
          <span>Booking: <b id="aCodigo">—</b></span>
        </div>
        <button type="button" id="aClose">&times;</button>
      </header>

      <div class="cnt">
        <p class="muted" style="margin:0 0 10px;">
          Elige qué deseas hacer con esta reservación.
        </p>

        <div class="actions-grid">
          <button type="button" class="btn warn" id="aNoShow">No Show</button>
          <button type="button" class="btn gray" id="aCancelar">Cancelar</button>

          <form id="aDeleteForm" method="POST">
            @csrf
            @method('DELETE')
            <button type="submit" class="btn danger" id="aEliminar">Eliminar</button>
          </form>
        </div>

        {{-- ✅ SOLO PARA NO SHOW / CANCELAR --}}
        <div id="aExtraFields" class="a-extra" style="display:none;">
          <div class="a-field">
            <label for="aComentarios">Comentarios</label>
            <textarea id="aComentarios" class="a-textarea" rows="3" placeholder="Escribe el motivo..."></textarea>
          </div>

          <div class="a-field">
            <label for="aEliminadoPor">¿Quién lo eliminó?</label>
            <select id="aEliminadoPor" class="a-select">
              <option value="">Selecciona…</option>
              <option value="Javier">Javier</option>
              <option value="Ventas">Ventas</option>
              <option value="Recepción">Recepción</option>
              <option value="Sistema">Sistema</option>
            </select>
          </div>
        </div>

        <input type="hidden" id="aAccion" value="">
        <input type="hidden" id="aIdReservacion" value="">
      </div>

      <div class="actions">
        <button type="button" class="btn gray" id="aCancel">Cerrar</button>
      </div>
    </div>
  </div>


  {{-- ============================
     🪟 MODAL apartar vehiculo
  ============================= --}}
  <div class="pop" id="modalVehiculos">
  <div class="box box-xl">
    <header>
      <div>
        <div>Seleccionar vehículo</div>
      </div>
      <button type="button" id="vClose">&times;</button>
    </header>

    <div class="cnt table-cnt">
      <table style="width:100%">
        <thead>
          <tr>
            <th>ID</th>
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
          </tr>
        </thead>
        <tbody id="tablaVehiculos"></tbody>
      </table>
    </div>

    <div class="actions">
      <button class="btn gray" id="vCancel">Cerrar</button>
    </div>
  </div>
</div>

  {{ $reservaciones->links() }}
</main>
@endsection

@section('js-vistaReservacionesActivas')
  <script src="{{ asset('js/reservacionesActivas.js') }}"></script>

  {{-- ✅ Exportar Excel SOLO para tabla principal --}}
  <script>
    window.addEventListener("DOMContentLoaded", () => {
      const btn = document.getElementById('btnExportExcel');
      if (!btn) return;

      const csvCell = (v) => {
        const s = (v ?? '').toString().replace(/\s+/g, ' ').trim();
        const escaped = s.replace(/"/g, '""');
        return /[",\n]/.test(escaped) ? `"${escaped}"` : escaped;
      };

      const downloadCSV = (filename, csvContent) => {
        const bom = "\uFEFF";
        const blob = new Blob([bom + csvContent], { type: "text/csv;charset=utf-8;" });
        const url = URL.createObjectURL(blob);

        const a = document.createElement("a");
        a.href = url;
        a.download = filename;
        document.body.appendChild(a);
        a.click();
        a.remove();

        URL.revokeObjectURL(url);
      };

      btn.addEventListener('click', () => {
        const thead = document.querySelector('#tablaActivas .thead');
        const rows  = Array.from(document.querySelectorAll('#tablaActivas .tbody .row'));

        if (!thead) {
          alert('No encontré el encabezado de la tabla.');
          return;
        }

        const dataRows = rows.filter(r => r.children.length > 2);

        if (dataRows.length === 0) {
          alert('No hay bookings para exportar.');
          return;
        }

        const headers = Array.from(thead.children).map(h => h.innerText.trim());
        const headersNoActions = headers.slice(0, -1);

        const SEP = ';';
        let csv = headersNoActions.map(csvCell).join(SEP) + '\n';

        dataRows.forEach(row => {
          const cells = Array.from(row.children);
          const dataCells = cells.slice(0, -1).map(cell => cell.innerText);
          csv += dataCells.map(csvCell).join(SEP) + '\n';
        });

        const now = new Date();
        const y = now.getFullYear();
        const m = String(now.getMonth()+1).padStart(2,'0');
        const d = String(now.getDate()).padStart(2,'0');

        downloadCSV(`bookings_${y}-${m}-${d}.csv`, csv);
      });
    });
  </script>
@endsection
