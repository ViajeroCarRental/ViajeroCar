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

    <span class="badge gray">Total <b id="count">{{ count($reservaciones) }}</b></span>
  </form>

  <!-- ======================= 📋 TABLA ======================= -->
  <section class="table">
    <div class="thead">
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
      <div>Acciones</div>
    </div>

    <div class="tbody">
      @forelse ($reservaciones as $r)
        <div class="row"
            
             data-codigo="{{ $r->codigo }}"
             data-cliente="{{ $r->nombre_cliente }}"
             data-email="{{ $r->email_cliente }}"
             data-numero="{{ $r->telefono_cliente }}"
             data-categoria="{{ $r->categoria }}"
             data-fecha-salida="{{ \Carbon\Carbon::parse($r->fecha_inicio)->format('Y-m-d') }}"
             data-estado="{{ $r->estado }}"
             data-sucursal="{{ $r->sucursal_retiro }}">

          {{-- 1. No. de reservación --}}
          <div>{{ $r->codigo }}</div>

          {{-- 2. Check in --}}
          <div>{{ \Carbon\Carbon::parse($r->fecha_inicio)->format('Y-m-d') }}</div>

          {{-- 3. Hora --}}
          <div>{{ \Carbon\Carbon::parse($r->hora_retiro)->format('H:i') }}</div>

          {{-- 4. No. Vuelo (solo si estamos filtrando Aeropuerto) --}}
          @if($esAeropuerto)
            <div>{{ $r->no_vuelo ?? '—' }}</div>
          @endif

          {{-- 5. Categoría (CÓDIGO) --}}
          <div>{{ $r->categoria }}</div>

          {{-- 6. Días --}}
          @php
            $inicio = \Carbon\Carbon::parse($r->fecha_inicio);
            $fin    = \Carbon\Carbon::parse($r->fecha_fin);
            $dias   = $inicio->diffInDays($fin);
          @endphp
          <div>{{ $dias }}</div>

          {{-- 7. Nombre --}}
          <div>{{ $r->nombre_cliente ?? '—' }}</div>

          {{-- 8. Celular --}}
          <div>{{ $r->telefono_cliente ?? '—' }}</div>

          {{-- 9. Correo --}}
          <div>{{ $r->email_cliente ?? '—' }}</div>

          {{-- 10. Estado --}}
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

          {{-- 11. Total --}}
          <div>${{ number_format($r->total, 2) }} MXN</div>

          {{-- 12. Acciones (⋯ abre modal) --}}
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
        <div class="row" >
          <div style="grid-column: 1 / -1; text-align:center;">No hay reservaciones activas.</div>
        </div>
      @endforelse
    </div>
  </section>

  {{-- ============================
       🪟 MODAL DETALLE RESERVACIÓN
  ============================ --}}
  <div class="pop" id="modal">
    <div class="box">
      {{-- HEADER --}}
      <header>
        <div>
          <div id="mTitle">Detalle reservación</div>
          <span>Resumen del booking</span>
        </div>
        <button type="button" id="mClose">&times;</button>
      </header>

      {{-- CONTENIDO --}}
      <div class="cnt">
        <div class="kv"><strong>Código</strong><span id="mCodigo">—</span></div>
        <div class="kv"><strong>Cliente</strong><span id="mCliente">—</span></div>
        <div class="kv"><strong>Correo</strong><span id="mEmail">—</span></div>
        <div class="kv"><strong>Teléfono</strong><span id="mNumero">—</span></div>
        <div class="kv"><strong>Categoría</strong><span id="mCategoria">—</span></div>
        <div class="kv"><strong>Estado</strong><span id="mEstado">—</span></div>
        <div class="kv"><strong>Salida</strong><span id="mSalida">—</span></div>
        <div class="kv"><strong>Entrega</strong><span id="mEntrega">—</span></div>
        <div class="kv"><strong>Forma de pago</strong><span id="mFormaPago">—</span></div>
        <div class="kv"><strong>Total</strong><span id="mTotal">—</span></div>
        <div class="kv"><strong>Tarifa modificada</strong><span id="mTarifaModificada">—</span></div>
      </div>

      {{-- FOOTER --}}
      <div class="actions">
        <button type="button" class="btn gray" id="mCancel">Cerrar</button>
        <button type="button" class="btn gray" id="mEdit">Editar</button>
        <button type="button" class="btn primary" id="mGo">Ir a contrato</button>
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
        <button type="button" class="btn warn" id="aNoShow">
          No Show
        </button>

        <button type="button" class="btn gray" id="aCancelar">
          Cancelar
        </button>

        {{-- ✅ Eliminar (ya existe en tu sistema) --}}
        <form id="aDeleteForm" method="POST">
          @csrf
          @method('DELETE')
          <button type="submit" class="btn danger" id="aEliminar">
            Eliminar
          </button>
        </form>
      </div>

      {{-- hidden: id actual (para historial en el siguiente paso) --}}
      <input type="hidden" id="aIdReservacion" value="">
    </div>

    <div class="actions">
      <button type="button" class="btn gray" id="aCancel">Cerrar</button>
    </div>
  </div>
</div>

</main>
@endsection

@section('js-vistaReservacionesActivas')
  <script src="{{ asset('js/reservacionesActivas.js') }}"></script>
@endsection
