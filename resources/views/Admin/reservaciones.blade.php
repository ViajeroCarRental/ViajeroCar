@extends('layouts.Ventas')
@section('Titulo', 'reservacionesAdmin')

@section('css-vistaHomeVentas')
<link rel="stylesheet" href="{{ asset('css/reservacionesAdmin.css') }}">
@endsection

@section('contenidoreservacionesAdmin')

<div class="wrap">
  <main class="main">

    <div class="top">
      <h1 class="h1">Nueva reservación</h1>

      <div class="top-actions">
        <button class="btn btn-resumen" id="btnResumen" type="button">
          <span class="pulse-dot"></span> 🧾 Ver resumen de reserva
        </button>
        <button class="btn ghost" onclick="location.href='{{ route('rutaInicioVentas') }}'">Salir</button>
      </div>
    </div>

    <form id="formReserva" action="{{ route('reservaciones.guardar') }}" method="POST" novalidate>
      @csrf

      {{-- Hidden “state” --}}
      <input type="hidden" id="categoria_id" name="categoria_id" value="">
      <input type="hidden" id="proteccion_id" name="proteccion_id" value="">
      <div id="addonsHidden"></div>

      {{-- ======================
           1) UBICACIÓN
      ======================= --}}
      <section class="stack-card">
        <div class="stack-head">
          <div class="stack-title">📍 Ubicación</div>
          <div class="stack-sub">Selecciona dónde se recoge y se entrega el vehículo.</div>
        </div>

        <div class="stack-body">
          <div class="form-2">
            <div>
              <label>Sucursal de retiro</label>
              <select id="sucursal_retiro" name="sucursal_retiro" class="input" required>
                <option value="">Selecciona punto de entrega</option>
                @foreach($sucursales as $s)
                  <option value="{{ $s->id_sucursal }}">{{ $s->nombre_mostrado }}</option>
                @endforeach
              </select>
            </div>

            <div>
              <label>Sucursal de entrega</label>
              <select id="sucursal_entrega" name="sucursal_entrega" class="input" required>
                <option value="">Selecciona punto de devolución</option>
                @foreach($sucursales as $s)
                  <option value="{{ $s->id_sucursal }}">{{ $s->nombre_mostrado }}</option>
                @endforeach
              </select>
            </div>
          </div>
          {{-- ❌ Eliminado: texto de Aeropuerto --}}
        </div>
      </section>

      {{-- ======================
           2) FECHAS Y HORAS
           ✅ UI (flatpickr modal) + ✅ Hidden ISO para backend
      ======================= --}}
      <section class="stack-card">
        <div class="stack-head">
          <div class="stack-title">🗓️ Fechas y horas</div>
          <div class="stack-sub">Define inicio/fin. Se calculan los días automáticamente.</div>
        </div>

        <div class="stack-body">
          <datalist id="time10"></datalist>

          {{-- ✅ estilos para que se vea como tu imagen (modal + barras) --}}
          <style>
            /* Backdrop para modal tipo imagen */
            .fp-backdrop{
              position:fixed;
              inset:0;
              background:rgba(0,0,0,.55);
              display:none;
              z-index:9998;
            }
            .fp-backdrop.is-open{ display:block; }

            /* Calendar container en modal (centrado) */
            .flatpickr-calendar.open{
              z-index:9999 !important;
              position:fixed !important;
              top:50% !important;
              left:50% !important;
              transform:translate(-50%,-50%) !important;
              margin:0 !important;
              box-shadow:0 28px 80px rgba(0,0,0,.30) !important;
              border-radius:12px !important;
              overflow:hidden !important;
            }

            /* Header rojo como tus tokens */
            .flatpickr-months{
              background:var(--brand) !important;
              color:#fff !important;
              padding:6px 6px !important;
            }
            .flatpickr-months .flatpickr-prev-month,
            .flatpickr-months .flatpickr-next-month{
              color:#fff !important;
              fill:#fff !important;
            }
            .flatpickr-current-month .cur-month,
            .flatpickr-current-month .numInputWrapper span,
            .flatpickr-current-month .numInput.cur-year{
              color:#fff !important;
              font-weight:900 !important;
            }

            .flatpickr-weekdays{
              background:#fff !important;
              border-bottom:1px solid #eef2f7 !important;
            }
            .flatpickr-weekday{
              color:#6b7280 !important;
              font-weight:900 !important;
            }

            .flatpickr-day{
              border-radius:10px !important;
              font-weight:800 !important;
            }
            .flatpickr-day.today{
              border-color:transparent !important;
              box-shadow:inset 0 0 0 2px rgba(178,34,34,.35) !important;
            }
            .flatpickr-day.selected,
            .flatpickr-day.startRange,
            .flatpickr-day.endRange{
              background:var(--brand) !important;
              border-color:var(--brand) !important;
              color:#fff !important;
            }

            /* Barra de acciones inferior (Hoy / Limpiar / etiqueta) */
            .fp-actions{
              display:flex;
              justify-content:space-between;
              align-items:center;
              gap:10px;
              padding:10px 12px;
              border-top:1px solid #eef2f7;
              background:#fff;
            }
            .fp-actions button{
              border:0;
              background:transparent;
              font-weight:900;
              cursor:pointer;
            }
            .fp-actions .fp-today{ color:#2563eb; }
            .fp-actions .fp-clear{ color:#ef4444; }
            .fp-actions .fp-label{
              color:#111827;
              opacity:.85;
            }

            /* Inputs UI con icono y look pro */
            .dt-field{ position:relative; }
            .dt-ico{
              position:absolute;
              right:12px;
              top:50%;
              transform:translateY(-50%);
              color:#9ca3af;
              pointer-events:none;
              font-size:16px;
            }
            input.fp-ui{
              cursor:pointer;
              background:#fff;
              padding-right:44px;
            }
          </style>

          <div class="form-2">
            <div class="dt-field">
              <label>Fecha de salida</label>

              {{-- ✅ UI para flatpickr (dd/mm/YYYY) --}}
              <input id="fecha_inicio_ui" class="input input-lg fp-ui" type="text" placeholder="dd/mm/aaaa" autocomplete="off" required>
              <span class="dt-ico">📅</span>

              {{-- ✅ Hidden real para backend (YYYY-mm-dd) --}}
              <input id="fecha_inicio" name="fecha_inicio" type="hidden">
            </div>

            <div class="dt-field">
              <label>Hora de salida</label>

              {{-- ✅ UI para flatpickr time --}}
              <input id="hora_retiro_ui" class="input input-lg fp-ui" type="text" placeholder="hh:mm" autocomplete="off" required>
              <span class="dt-ico">🕒</span>

              {{-- ✅ Hidden real para backend --}}
              <input id="hora_retiro" name="hora_retiro" type="hidden">
            </div>

            <div class="dt-field">
              <label>Fecha de llegada</label>

              <input id="fecha_fin_ui" class="input input-lg fp-ui" type="text" placeholder="dd/mm/aaaa" autocomplete="off" required>
              <span class="dt-ico">📅</span>

              <input id="fecha_fin" name="fecha_fin" type="hidden">
            </div>

            <div class="dt-field">
              <label>Hora de llegada</label>

              <input id="hora_entrega_ui" class="input input-lg fp-ui" type="text" placeholder="hh:mm" autocomplete="off" required>
              <span class="dt-ico">🕒</span>

              <input id="hora_entrega" name="hora_entrega" type="hidden">
            </div>
          </div>

          <div class="days-row">
            <span class="days-pill">⏱️ <b id="diasTxt">0</b> día(s)</span>
            {{-- ❌ Eliminado: "*El cálculo previo es estimado." --}}
          </div>
        </div>
      </section>

      {{-- ======================
           3) CATEGORÍA (MENÚ MODAL)
      ======================= --}}
      <section class="stack-card">
        <div class="stack-head">
          <div class="stack-title">🚗 Categoría</div>
          <div class="stack-sub">Selecciona una categoría. Mostramos tarifa base por día + cálculo previo.</div>
        </div>

        <div class="stack-body">
          <div class="picker-row">
            <button class="btn primary" type="button" id="btnCategorias">📦 Seleccionar categoría</button>

            <div class="picker-selected">
              <div class="picker-label">Seleccionado</div>
              <div class="picker-value" id="catSelTxt">— Ninguna categoría —</div>
              <div class="picker-sub" id="catSelSub">Tarifa base por día y cálculo previo aparecerán aquí.</div>
            </div>

            <button class="btn gray" type="button" id="catRemove" style="display:none;">✖</button>
          </div>

          <div class="mini-preview" id="catMiniPreview" style="display:none;">
            <div class="mini-right">
              <div class="mini-title" id="catMiniName">—</div>
              <div class="mini-sub" id="catMiniDesc">—</div>

              <div class="mini-price">
                <div>
                  <div class="muted small">Tarifa base</div>
                  <div class="price-big" id="catMiniRate">$0.00 MXN / día</div>
                </div>
                <div>
                  <div class="muted small">Cálculo previo</div>
                  <div class="price-big" id="catMiniCalc">$0.00 MXN</div>
                </div>
              </div>
            </div>
          </div>

          {{-- ❌ Eliminado: "*La tarifa base viene de categorias_carros.precio_dia." --}}
        </div>
      </section>

      {{-- ======================
           4) PROTECCIONES (MENÚ MODAL)
      ======================= --}}
      <section class="stack-card">
        <div class="stack-head">
          <div class="stack-title">🔒 Protecciones</div>
          <div class="stack-sub">Paquetes de seguro.</div>
        </div>

        <div class="stack-body">
          <div class="picker-row">
            <button class="btn primary" type="button" id="btnProtecciones">🛡️ Seleccionar protección</button>

            <div class="picker-selected">
              <div class="picker-label">Seleccionado</div>
              <div class="picker-value" id="proteSelTxt">— Ninguna protección —</div>
              <div class="picker-sub" id="proteSelSub">Costo se refleja en el resumen.</div>
            </div>

            <button class="btn gray" type="button" id="proteRemove" style="display:none;">✖</button>
          </div>
        </div>
      </section>

      {{-- ======================
           5) ADICIONALES (MENÚ MODAL)
      ======================= --}}
      <section class="stack-card">
        <div class="stack-head">
          <div class="stack-title">➕ Adicionales</div>
          <div class="stack-sub">Selecciona servicios extra.</div>
        </div>

        <div class="stack-body">
          <div class="picker-row">
            <button class="btn primary" type="button" id="btnAddons">🧩 Seleccionar adicionales</button>

            <div class="picker-selected">
              <div class="picker-label">Seleccionado</div>
              <div class="picker-value" id="addonsSelTxt">— Ninguno —</div>
              <div class="picker-sub" id="addonsSelSub">Subtotal estimado aparecerá aquí.</div>
            </div>

            <button class="btn gray" type="button" id="addonsClear" style="display:none;">✖</button>
          </div>
        </div>
      </section>

      {{-- ======================
           6) CLIENTE
      ======================= --}}
      <section class="stack-card">
        <div class="stack-head">
          <div class="stack-title">👤 Datos del cliente</div>
          <div class="stack-sub">Completa los datos para registrar la reservación.</div>
        </div>

        <div class="stack-body">
          <div class="form-2">
            <div>
              <label>Nombre</label>
              <input id="nombre_cliente" name="nombre_cliente" class="input" type="text" required>
            </div>

            <div>
              <label>Apellido paterno</label>
              <input id="apellido_paterno" name="apellido_paterno" class="input" type="text" required>
            </div>

            <div>
              <label>Apellido materno</label>
              <input id="apellido_materno" name="apellido_materno" class="input" type="text" required>
            </div>

            <div>
              <label>Email</label>
              <input id="email_cliente" name="email_cliente" class="input" type="email" required>
            </div>

            <div>
              <label>Teléfono</label>
              <input id="telefono_cliente" name="telefono_cliente" class="input" type="text" placeholder="+52..." required>
            </div>

            <div>
              <label>País</label>
              <input id="pais" name="pais" class="input" type="text" value="MÉXICO" required>
            </div>

            <div id="vueloWrap" style="display:none;">
              <label>No. Vuelo</label>
              <input id="no_vuelo" name="no_vuelo" class="input" type="text" placeholder="UA2068">
              <div class="small muted">Solo requerido si la sucursal es Aeropuerto.</div>
            </div>
          </div>

          <div class="acciones single">
            <button class="btn primary" id="btnReservar" type="submit">✅ Registrar reservación</button>
          </div>
        </div>
      </section>
    </form>

  </main>
</div>

{{-- =========================
   MODAL: CATEGORÍAS
========================= --}}
<div class="pop modal" id="catPop">
  <div class="box modal-box">
    <header class="modal-head">
      <div class="modal-title">🚗 Selecciona una categoría</div>
      <button class="btn gray" id="catClose" type="button">✖</button>
    </header>

    <div class="modal-body">

      <style>
        #catPop .grid-cards{
          display:grid !important;
          grid-template-columns: 1fr !important;
          gap: 14px !important;
        }
        #catPop .card-pick{ width: 100% !important; }

        #catPop .cp-features{
          display:flex;
          flex-wrap:wrap;
          gap:8px;
          margin-top:10px;
        }
        #catPop .cp-chip{
          display:inline-flex;
          align-items:center;
          gap:6px;
          padding:6px 10px;
          border-radius:999px;
          border:1px solid rgba(0,0,0,.10);
          background:rgba(255,255,255,.7);
          font-size:12px;
          line-height:1;
          white-space:nowrap;
        }
      </style>

      <div class="grid-cards">
        @php
          $imgCategorias = [
            1 => asset('img/aveo.png'),
            2 => asset('img/virtus.png'),
            3 => asset('img/jetta.png'),
            4 => asset('img/camry.png'),
            5 => asset('img/renegade.png'),
            6 => asset('img/seltos.png'),
            7 => asset('img/avanza.png'),
            8 => asset('img/Odyssey.png'),
            9 => asset('img/Urvan.png'),
            10 => asset('img/Frontier.png'),
            11 => asset('img/tacoma.png'),
          ];

          $pasajeros = [
            1 => 5, 2 => 5, 3 => 5, 4 => 5, 5 => 5, 6 => 5,
            7 => 7, 8 => 8, 9 => 13, 10 => 5, 11 => 5,
          ];

          $transmision = [ 9 => 'Manual' ];

          $categoriasOrdenadas = $categorias->sortBy(function($c){
            return (float) ($c->precio_dia ?? 0);
          })->values();
        @endphp

        @foreach($categoriasOrdenadas as $cat)
          @php
            $img = $imgCategorias[$cat->id_categoria] ?? asset('img/placeholder-car.jpg');
            $cap = $pasajeros[$cat->id_categoria] ?? 5;
            $tran = $transmision[$cat->id_categoria] ?? 'Automático';

            $features = [
              "♾️ Km ilimitados",
              "🛡️ Relevo de responsabilidad",
              "👥 {$cap} pasajeros",
              "🍎 Apple CarPlay",
              "🤖 Android Auto",
              "❄️ Aire acondicionado",
              ($tran === 'Manual' ? "🕹️ Manual" : "🕹️ Automático"),
            ];
          @endphp

          <article class="card-pick cat-wide"
              data-id="{{ $cat->id_categoria }}"
              data-nombre="{{ $cat->nombre }}"
              data-desc="{{ $cat->descripcion }}"
              data-precio="{{ $cat->precio_dia }}"
              data-img="{{ $img }}">
            <div class="cp-img">
              <img src="{{ $img }}" alt="{{ $cat->nombre }}">
            </div>

            <div class="cp-left">
              <div class="cp-title">{{ $cat->nombre }}</div>
              <div class="cp-sub">{{ $cat->descripcion }}</div>

              <div class="cp-features">
                @foreach($features as $f)
                  <span class="cp-chip">{{ $f }}</span>
                @endforeach
              </div>

              <div class="cp-meta">
                <span class="pill">Código: {{ $cat->codigo }}</span>
                @if(isset($cat->activo))
                  <span class="pill {{ (int)$cat->activo===1 ? 'pill-ok' : '' }}">
                    {{ (int)$cat->activo===1 ? 'Activo' : 'Inactivo' }}
                  </span>
                @endif
              </div>
            </div>

            <div class="cp-right">
              <div class="cp-price">
                <div class="muted small">Tarifa base</div>
                <div class="price-big">
                  ${{ number_format((float)$cat->precio_dia, 2) }} <span>MXN / día</span>
                </div>
              </div>
              <button class="btn primary btn-block" type="button">Elegir</button>
            </div>
          </article>
        @endforeach
      </div>
    </div>

    <footer class="modal-foot">
      <button class="btn gray" id="catCancel" type="button">Cerrar</button>
    </footer>
  </div>
</div>

{{-- =========================
   MODAL: PROTECCIONES
========================= --}}
<div class="pop modal" id="proteccionPop">
  <div class="box modal-box">
    <header class="modal-head">
      <div class="modal-title">🔒 Seleccionar protección</div>
      <button class="btn gray" id="proteClose" type="button">✖</button>
    </header>

    <div class="modal-body">
      <div id="proteList" class="grid-cards">
        <div class="loading">Cargando paquetes...</div>
      </div>
    </div>

    <footer class="modal-foot">
      <button class="btn gray" id="proteCancel" type="button">Cerrar</button>
    </footer>
  </div>
</div>

{{-- =========================
   MODAL: ADICIONALES
========================= --}}
<div class="pop modal" id="addonsPop">
  <div class="box modal-box">
    <header class="modal-head">
      <div class="modal-title">➕ Seleccionar adicionales</div>
      <button class="btn gray" id="addonsClose" type="button">✖</button>
    </header>

    <div class="modal-body">
      <div id="addonsList" class="grid-cards">
        <div class="loading">Cargando adicionales...</div>
      </div>
    </div>

    <footer class="modal-foot foot-split">
      <button class="btn gray" id="addonsCancel" type="button">Cerrar</button>
      <button class="btn primary" id="addonsApply" type="button">Aplicar</button>
    </footer>
  </div>
</div>

{{-- =========================
   MODAL: RESUMEN (OVERLAY)
========================= --}}
<div class="pop modal" id="resumenPop">
  <div class="box modal-box resumen-box">
    <header class="modal-head">
      <div class="modal-title">🧾 Resumen de reservación</div>
      <button class="btn gray" id="resumenClose" type="button">✖</button>
    </header>

    <div class="modal-body">
      <div class="resumen-card">
        <div class="res-row"><div>📍 Retiro</div><div id="resSucursalRetiro">—</div></div>
        <div class="res-row"><div>🏁 Entrega</div><div id="resSucursalEntrega">—</div></div>

        <div class="res-row"><div>🗓️ Salida</div><div id="resFechaInicio">—</div></div>
        <div class="res-row"><div>🕑 Hora salida</div><div id="resHoraInicio">—</div></div>
        <div class="res-row"><div>🗓️ Llegada</div><div id="resFechaFin">—</div></div>
        <div class="res-row"><div>🕓 Hora llegada</div><div id="resHoraFin">—</div></div>

        <div class="res-row"><div>⏱️ Días</div><div id="resDias">—</div></div>

        <div class="divider"></div>

        <div class="res-row"><div>🚗 Categoría</div><div id="resCat">—</div></div>
        <div class="res-row"><div>Tarifa base</div><div id="resBaseDia">—</div></div>
        <div class="res-row"><div>Base × días</div><div id="resBaseTotal">—</div></div>

        <div class="res-row"><div>🔒 Protección</div><div id="resProte">—</div></div>
        <div class="res-row"><div>➕ Adicionales</div><div id="resAdds">—</div></div>

        <div class="divider"></div>

        <div class="res-row"><div>Subtotal</div><div id="resSub">$0.00 MXN</div></div>
        <div class="res-row"><div>IVA (16%)</div><div id="resIva">$0.00 MXN</div></div>
        <div class="res-row total"><div>Total</div><div id="resTotal">$0.00 MXN</div></div>
      </div>
    </div>

    <footer class="modal-foot">
      <button class="btn primary" type="button" id="resumenOk">Listo</button>
    </footer>
  </div>
</div>

@section('js-vistareservacionesAdmin')
{{-- ✅ IMPORTANTE: Flatpickr + locale ES antes de tu JS --}}
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<script src="https://cdn.jsdelivr.net/npm/flatpickr/dist/l10n/es.js"></script>

<script src="{{ asset('js/reservacionesAdmin.js') }}"></script>
@endsection

@endsection
