@extends('layouts.Ventas')
@section('Titulo', 'Reservaciones Activas')

@section('css-vistaReservacionesActivas')
  <link rel="stylesheet" href="{{ asset('css/reservacionesActivas.css') }}">
@endsection

@section('contenidoReservacionesActivas')
<main class="main">
  <h1 class="h1">Bookings</h1>

  @php
    \Carbon\Carbon::setLocale('es');

    $esAeropuerto = request('sucursal') === '1';
    $cols = $esAeropuerto ? 13 : 12;
    $reservaciones_anteriores = $reservaciones_anteriores ?? [];
    $seguroPaquete = $seguroPaquete ?? collect();
    $seguroIndividual = $seguroIndividual ?? collect();

    $fmtFecha = function ($fecha) {
      if (!$fecha) return '—';

      return str_replace(
        '.',
        '',
        strtolower(\Carbon\Carbon::parse($fecha)->translatedFormat('d-M-Y'))
      );
    };

    $fmtHora = function ($hora) {
      return $hora
        ? \Carbon\Carbon::parse($hora)->format('H:i')
        : '—';
    };
  @endphp

  {{-- FILTROS --}}
  <form method="GET" class="toolbar">

    <input
      id="q"
      name="q"
      class="input search-input"
      type="search"
      placeholder="Buscar por nombre, correo o código…"
      value="{{ request('q') }}"
    >

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

    <input
      type="text"
      id="filtro_fecha_ui"
      class="input"
      placeholder="Fecha"
      value="{{ request('fecha_inicio') ? \Carbon\Carbon::parse(request('fecha_inicio'))->format('d-M-Y') : '' }}"
      autocomplete="off"
    >

    <input
      type="hidden"
      id="filtro_fecha"
      name="fecha_inicio"
      value="{{ request('fecha_inicio') }}"
    >

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

    <span class="badge gray">Total <b id="count">{{ $reservaciones->total() }}</b></span>

  </form>

  {{-- ACCIONES --}}
  <div class="toolbar-actions">

    <a
      href="{{ route('rutaExportarReservacionesRespaldo') }}"
      class="btn primary"
      id="btnExportExcel"
    >
      <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
        <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/>
        <polyline points="7 10 12 15 17 10"/>
        <line x1="12" y1="15" x2="12" y2="3"/>
      </svg>
      Exportar Excel
    </a>

    <button
      type="button"
      class="btn primary"
      id="btnImportExcel"
    >
      <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
        <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/>
        <polyline points="17 8 12 3 7 8"/>
        <line x1="12" y1="3" x2="12" y2="15"/>
      </svg>
      Importar Excel
    </button>

    <form
      id="formImportExcel"
      action="{{ route('rutaImportarReservacionesRespaldo') }}"
      method="POST"
      enctype="multipart/form-data"
      style="display:none;"
    >
      @csrf
      <input type="file" id="inputImportExcel" name="archivo" accept=".xlsx,.xls" style="display:none;">
    </form>

    <button
      type="button"
      class="btn gray"
      id="btnPrevBookings"
      title="Ver reservaciones del día anterior"
    >
      <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
        <rect x="3" y="4" width="18" height="18" rx="2" ry="2"/>
        <line x1="16" y1="2" x2="16" y2="6"/>
        <line x1="8" y1="2" x2="8" y2="6"/>
        <line x1="3" y1="10" x2="21" y2="10"/>
      </svg>
      Reservaciones anteriores
    </button>

  </div>

  {{-- TABLA ACTUAL --}}
  <section id="tablaActivas" class="table {{ $esAeropuerto ? 'is-airport' : '' }}" data-cols="{{ $cols }}">
    <div class="thead">
      <div></div>
      <div>No. de Reservacion</div>
      <div>oficina </div>
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

          $horaIn = $fmtHora($r->hora_retiro);

          $estado = $r->estado;
          $color = match($estado) {
            'confirmada' => 'ok',
            'pendiente_pago' => 'warn',
            'hold' => 'gray',
            'cancelada' => 'danger',
            default => 'gray'
          };

          $extras = $servicios[$r->id_reservacion] ?? [];

          $soloAbrev = fn ($n) => trim(preg_replace('/\s*\(.*?\)\s*/', '', (string) $n));

          $diasReserva = (int) ($r->dias ?? 0);
          if ($diasReserva <= 0 && !empty($r->fecha_inicio) && !empty($r->fecha_fin)) {
              $diasReserva = max(1, \Carbon\Carbon::parse($r->fecha_inicio)
                  ->diffInDays(\Carbon\Carbon::parse($r->fecha_fin)));
          }
          $diasReserva = max(1, $diasReserva);

          $listaSeguros = [];
          if (isset($seguroPaquete[$r->id_reservacion])) {
              $p = $seguroPaquete[$r->id_reservacion];
              $listaSeguros[] = [
                  'nombre' => $soloAbrev($p->nombre),
                  'total'  => (float) $p->precio_por_dia * $diasReserva,
              ];
          } elseif (isset($seguroIndividual[$r->id_reservacion])) {
              foreach ($seguroIndividual[$r->id_reservacion] as $s) {
                  $listaSeguros[] = [
                      'nombre' => $soloAbrev($s->nombre),
                      'total'  => (float) $s->precio_por_dia * $diasReserva,
                  ];
              }
          }

          $oficinaRetiro = collect([
            $r->oficina_retiro_completa ?? null,
          ])->first(fn ($valor) => $valor !== null && trim((string) $valor) !== '');

          $oficinaDevolucion = collect([
            $r->oficina_devolucion_completa ?? null,
            $oficinaRetiro,
          ])->first(fn ($valor) => $valor !== null && trim((string) $valor) !== '');
        @endphp

        <div
          class="row"
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
            <button type="button" class="btn-more" data-toggle-detail>+</button>
          </div>

          <div>{{ $r->codigo }}</div>

          <div>
            @if(!empty($r->oficina_compacta))

              @if($r->oficina_compacta === 'AIQ')
                <span class="oficina-icon">
                  <i class="fa-solid fa-plane"></i>
                  {{ $r->oficina_compacta }}
                </span>

              @elseif($r->oficina_compacta === 'TAQ')
                <span class="oficina-icon">
                  <i class="fa-solid fa-bus" style="color:black;"></i>
                  {{ $r->oficina_compacta }}
                </span>

              @elseif($r->oficina_compacta === 'OCP')
                <span class="oficina-icon">
                  <i class="fa-solid fa-building"></i>
                  {{ $r->oficina_compacta }}
                </span>

              @else
                —
              @endif

            @else
              —
            @endif
          </div>

          <div>{{ $fmtFecha($r->fecha_inicio) }}</div>
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
            <span class="state {{ $color }}">{{ ucfirst($estado) }}</span>
          </div>

          <div>${{ number_format($r->total, 2) }} MXN</div>
        </div>

        <div class="row-detail" style="display:none;">
          <div class="reserva-summary">

            <div class="summary-title">
              Reservación Confirmada el: {{ $r->created_at ? \Carbon\Carbon::parse($r->created_at)->format('d-M-Y H:i:s') : '—' }}
            </div>

           <div class="reserva-summary-line summary-full summary-contact">
             <b>Datos de Contacto:</b>
             <b style="font-weight: 900; font-size: 14px; color: #000000; background: #FFF9FA; padding: 4px 10px; border-radius: 6px; display: inline-block; border: 1px solid #FFD1D6;">
              {{ $r->pais_cliente ?? $r->pais ?? 'MEXICO (MX)' }}
               | {{ $r->telefono_cliente ?? '—' }}
               | {{ $nombreCompleto }}
               | {{ $r->email_cliente ?? '—' }}
            </b>
           </div>

            <div class="reserva-summary-line">
              <b>Entrega:</b>
              {{ $fmtFecha($r->fecha_inicio) }}
              a las {{ $fmtHora($r->hora_retiro) }} HRS
            </div>

            <div class="reserva-summary-line">
              <b>Devolución:</b>
              {{ $fmtFecha($r->fecha_fin) }}
              a las {{ $fmtHora($r->hora_entrega) }} HRS
            </div>

            <div class="reserva-summary-line">
              <b>Oficina de entrega:</b>
              {{ $oficinaRetiro ?? '—' }}
            </div>

            <div class="reserva-summary-line">
              <b>Oficina de devolución:</b>
              {{ $oficinaDevolucion ?? $oficinaRetiro ?? '—' }}
            </div>

            <div class="reserva-summary-line summary-full">
              <b>Vehículo Requerido:</b>
              {{ $r->categoria }}
              | {{ $r->categoria_nombre ?? 'Sin asignar' }}
              {{ $r->transmision ?? 'Sin transmisión' }}
              {{ $r->categoria_descripcion ?? '' }}
              | Costo online: ${{ number_format($r->precio_dia ?? 0, 2) }}
              | Costo oficina: ${{ number_format(($r->precio_dia ?? 0) * 1.15, 2) }}
            </div>

            <div class="reserva-summary-line">
              <b>Número de vuelo:</b>
              {{ $r->no_vuelo ?? '—' }}
            </div>

         <div class="reserva-summary-line">
              <b>Adicionales Requeridos:</b>
              @if(count($extras))
                @foreach($extras as $e)
                  @php
                    $precioBaseAd = ($e->precio ?? 0) * ($e->cantidad ?? 1);
                    $totalAd = ($e->tipo_cobro ?? 'por_dia') === 'por_dia'
                        ? $precioBaseAd * $diasReserva
                        : $precioBaseAd;
                  @endphp
                  <div>- {{ $e->nombre }} (x{{ $e->cantidad }}) — ${{ number_format($totalAd, 2) }} MXN</div>
                @endforeach
              @else
                <span style="color:#999;">Ninguno</span>
              @endif
            </div>

            <div class="reserva-summary-line">
              <b>Seguros:</b>
              @if(count($listaSeguros))
                @foreach($listaSeguros as $sg)
                  <div>- {{ $sg['nombre'] }} — ${{ number_format($sg['total'], 2) }} MXN</div>
                @endforeach
              @else
                <span style="color:#999;">—</span>
              @endif
            </div>
            <div class="reserva-summary-line summary-total"
                style="grid-column: 2 / 3 !important;">
                <b>Total(MXN):</b>
                ${{ number_format($r->total, 2) }} - Forma de pago: ({{ $r->metodo_pago ?? 'mostrador' }})
            </div>

            <div class="summary-actions">

              <div class="summary-actions-left">

                <button
                  type="button"
                  class="btn btn-edit"
                  onclick="window.location.href='/admin/reservaciones/{{ $r->id_reservacion }}/editar'"
                >
                  <i class="fa-solid fa-pen"></i>
                  Editar Reservación
                </button>

                <button
                  type="button"
                  class="btn btn-cancel"
                  title="Cancelar reservación"
                  data-open-actions
                  data-id="{{ $r->id_reservacion }}"
                  data-codigo="{{ $r->codigo }}"
                  data-delete-url="{{ route('rutaEliminarReservacionActiva', $r->id_reservacion) }}"
                >
                  <i class="fa-solid fa-trash"></i>
                  Cancelar Reservación
                </button>

              </div>

              <div class="summary-actions-right">

                <button
                  type="button"
                  class="btn btn-mail"
                  onclick="reenviarCorreo({{ $r->id_reservacion }}, this)"
                >
                  <i class="fa-solid fa-envelope"></i>
                  Reenviar correo
                </button>

                <button
                  type="button"
                  class="btn btn-car btn-apartar-auto"
                  data-id="{{ $r->id_reservacion }}"
                >
                  <i class="fa-solid fa-car-side"></i>
                  Apartar auto
                </button>

              </div>

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
  {{-- Modal de aviso reutilizable --}}
<div id="modalAviso" class="aviso-overlay" aria-hidden="true">
  <div class="aviso-box" role="dialog" aria-modal="true">
    <div class="aviso-icono" id="avisoIcono"></div>
    <h3 class="aviso-titulo" id="avisoTitulo">Aviso</h3>
    <p class="aviso-texto" id="avisoTexto"></p>
    <div class="aviso-acciones">
      <button type="button" class="aviso-btn aviso-btn-cancel" id="avisoCancelar" style="display:none;">Cancelar</button>
      <button type="button" class="aviso-btn" id="avisoAceptar">Aceptar</button>
    </div>
  </div>
</div>

  {{-- MODAL RESERVACIONES ANTERIORES --}}
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
            <div></div>
            <div>No. de Reservacion</div>
            <div>oficina</div>
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

                $horaIn = $fmtHora($r->hora_retiro);

                $estado = $r->estado;
                $color = match($estado) {
                  'confirmada' => 'ok',
                  'pendiente_pago' => 'warn',
                  'hold' => 'gray',
                  'cancelada' => 'danger',
                  default => 'gray'
                };
              @endphp

              <div
                class="row"
                data-id="{{ $r->id_reservacion }}"
                data-codigo="{{ $r->codigo }}"
                data-cliente="{{ $nombreCompleto }}"
                data-email="{{ $r->email_cliente }}"
                data-numero="{{ $r->telefono_cliente }}"
                data-categoria="{{ $r->categoria }}"
                data-fecha-salida="{{ \Carbon\Carbon::parse($r->fecha_inicio)->format('Y-m-d') }}"
                data-estado="{{ $r->estado }}"
                data-sucursal="{{ $r->sucursal_retiro }}"
              >
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

                <div>{{ $r->codigo }}</div>

                <div>
                  @if(!empty($r->oficina_compacta))

                    @if($r->oficina_compacta === 'AIQ')
                      <span class="oficina-icon">
                        <i class="fa-solid fa-plane"></i>
                        {{ $r->oficina_compacta }}
                      </span>

                    @elseif($r->oficina_compacta === 'TAQ')
                      <span class="oficina-icon">
                        <i class="fa-solid fa-bus" style="color:black;"></i>
                        {{ $r->oficina_compacta }}
                      </span>

                    @elseif($r->oficina_compacta === 'OCP')
                      <span class="oficina-icon">
                        <i class="fa-solid fa-building"></i>
                        {{ $r->oficina_compacta }}
                      </span>

                    @else
                      —
                    @endif

                  @else
                    —
                  @endif
                </div>

                <div>{{ $fmtFecha($r->fecha_inicio) }}</div>
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
                  <span class="state {{ $color }}">{{ ucfirst($estado) }}</span>
                </div>

                <div>${{ number_format($r->total, 2) }} MXN</div>
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

  {{-- MODAL DETALLE RESERVACIÓN --}}
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
       <div class="kv"><strong>Cliente -</strong><span id="mCliente">—</span></div>
        <div class="kv"><strong>Comentarios -</strong><span id="mComentarios">—</span></div>
      </div>

      <div class="actions">
        <button type="button" class="btn gray" id="mCancel">Cerrar</button>
        <button type="button" class="btn primary" id="mGo">Capturar contrato</button>
      </div>
    </div>
  </div>

  {{-- MODAL EDICIÓN RESERVACIÓN --}}
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

  {{-- MODAL ACCIONES --}}
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

  {{-- MODAL APARTAR VEHÍCULO --}}
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
              <th>Acciones</th>
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

  {{-- MODAL CONFIRMAR EDICIÓN INVENTARIO --}}
  <div class="pop" id="modalConfirmInv" aria-hidden="true" style="z-index:100001;">
    <div class="box box-sm">
      <header>
        <div>
          <div>Confirmar cambio</div>
        </div>
        <button type="button" id="ciClose">&times;</button>
      </header>
      <div class="cnt">
        <p class="muted" style="margin:0 0 12px;">Vas a modificar este vehículo:</p>
        <div class="kv"><strong>Categoría</strong><span id="ciCategoria">—</span></div>
        <div class="kv"><strong>Color</strong><span id="ciColor">—</span></div>
        <div class="kv"><strong>Placas</strong><span id="ciPlacas">—</span></div>
        <div class="kv"><strong id="ciCampoLabel">Campo</strong>
          <span><s id="ciAnterior" style="color:#94a3b8;">—</s> <b id="ciNuevo"
              style="color:#D6121F;margin-left:8px;">—</b></span>
        </div>
      </div>
      <div class="actions">
        <button type="button" class="btn gray" id="ciCancel">Cancelar</button>
        <button type="button" class="btn primary" id="ciConfirm">Confirmar</button>
      </div>
    </div>
  </div>

  {{-- MODAL CONFIRMAR SELECCIÓN DE VEHÍCULO --}}
  <div id="modalConfirmarVehiculo" class="modal-vehiculos">
    <div class="modal-vehiculos-content">

      <div class="modal-vehiculos-header">
        <span class="modal-vehiculos-titulo">
          <i class="fas fa-check-circle"></i> Confirmar selección
        </span>
        <button type="button" id="cerrarConfirmarVehiculo" class="modal-close-btn">
          <i class="fas fa-times"></i>
        </button>
      </div>

      <div class="modal-vehiculos-body">

        <div class="vehiculo-info-card">
          <div class="vehiculo-info-row">
            <span class="vehiculo-info-label"><i class="fas fa-id-card"></i> Placas</span>
            <span class="vehiculo-info-value" id="confPlacasVehiculo">—</span>
          </div>
          <div class="vehiculo-info-row">
            <span class="vehiculo-info-label"><i class="fas fa-car"></i> Modelo</span>
            <span class="vehiculo-info-value" id="confModeloVehiculo">—</span>
          </div>
          <div class="vehiculo-info-row">
            <span class="vehiculo-info-label"><i class="fas fa-tag"></i> Categoría</span>
            <span class="vehiculo-info-value" id="confCategoriaVehiculo">—</span>
          </div>
          <div class="vehiculo-info-row" style="border-bottom: none;">
            <span class="vehiculo-info-label"><i class="fas fa-palette"></i> Color</span>
            <span class="vehiculo-info-value" id="confColorVehiculo">—</span>
          </div>
        </div>

        <div class="campo-gasolina">
          <label><i class="fas fa-gas-pump"></i> Nivel de gasolina</label>
          <div class="gasolina-select-wrapper">
            <select id="confGasolinaSelect">
              <option value="0">0/16</option>
              <option value="1">1/16</option>
              <option value="2">2/16</option>
              <option value="3">3/16</option>
              <option value="4">4/16</option>
              <option value="5">5/16</option>
              <option value="6">6/16</option>
              <option value="7">7/16</option>
              <option value="8">8/16</option>
              <option value="9">9/16</option>
              <option value="10">10/16</option>
              <option value="11">11/16</option>
              <option value="12">12/16</option>
              <option value="13">13/16</option>
              <option value="14">14/16</option>
              <option value="15">15/16</option>
              <option value="16" selected>16/16</option>
            </select>
            <span class="gasolina-texto" id="confLitrosTexto">~0 L</span>
          </div>
        </div>

        <div class="campo-kilometraje">
          <label><i class="fas fa-tachometer-alt"></i> Kilometraje actual</label>
          <div class="kilometraje-input-wrapper">
            <input type="number" id="confKilometrajeInput" min="0" step="1" placeholder="Ej. 25000">
            <span class="kilometraje-unidad">km</span>
          </div>
          <div class="kilometraje-hint">
            <i class="fas fa-info-circle"></i> Ingresa el kilometraje actual del vehículo
          </div>
        </div>

        <div class="warning-box">
          <i class="fas fa-exclamation-triangle"></i>
          <span>Verifica que los datos sean correctos antes de confirmar la selección.</span>
        </div>
      </div>

      <div class="modal-footer">
        <button type="button" class="btn btn-cancelar" id="cancelarConfirmarVehiculo">
          <i class="fas fa-times"></i> Cancelar
        </button>
        <button type="button" class="btn btn-confirmar" id="confirmarSeleccionVehiculo">
          <i class="fas fa-check"></i> Confirmar
        </button>
      </div>

    </div>
  </div>

  {{ $reservaciones->links() }}
</main>
@endsection

@section('js-vistaReservacionesActivas')
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">

  <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
  <script src="https://cdn.jsdelivr.net/npm/flatpickr/dist/l10n/es.js"></script>


  <script src="{{ asset('js/reservacionesActivas.js') }}"></script>

  {{-- IMPORTAR RESPALDO --}}
  <script>
    window.addEventListener("DOMContentLoaded", () => {
      const btnImport   = document.getElementById('btnImportExcel');
      const inputImport = document.getElementById('inputImportExcel');
      const formImport  = document.getElementById('formImportExcel');

      if (!btnImport || !inputImport || !formImport) return;

      btnImport.addEventListener('click', () => {
        inputImport.click();
      });

      inputImport.addEventListener('change', () => {
        if (!inputImport.files.length) return;

        const nombre = inputImport.files[0].name;

        Aviso.confirmar({
          titulo: 'Importar respaldo',
          mensaje: `¿Seguro que deseas IMPORTAR el archivo "${nombre}"? ` +
                   `Esto restaurará o actualizará las reservaciones comparando por ID; ` +
                   `las que ya existan se sobrescribirán con los datos del archivo.`,
          textoOk: 'Sí, importar'
        }).then((ok) => {
          if (ok) {
            formImport.submit();
          } else {
            inputImport.value = "";
            mostrarAviso('Importación cancelada', 'warn');
          }
        });
      });

      @if(session('success'))
        mostrarAviso(@json(session('success')), 'ok');
      @endif
      @if(session('error'))
        mostrarAviso(@json(session('error')), 'error');
      @endif
    });
  </script>

  {{-- TOGGLE RESUMEN --}}
  <script>
    document.addEventListener('click', function(e) {
      const btn = e.target.closest('[data-toggle-detail]');
      if (!btn) return;

      const row = btn.closest('.row');
      const detail = row ? row.nextElementSibling : null;

      if (!detail || !detail.classList.contains('row-detail')) return;

      const isOpen = detail.style.display !== 'none';

      detail.style.display = isOpen ? 'none' : 'block';
      btn.textContent = isOpen ? '+' : '−';
      btn.classList.toggle('is-open', !isOpen);
    });
  </script>
@endsection
