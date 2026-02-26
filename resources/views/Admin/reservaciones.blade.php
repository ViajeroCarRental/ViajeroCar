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

    <form
      id="formReserva"
      action="{{ route('reservaciones.guardar') }}"
      method="POST"
      novalidate
      data-redirect="{{ route('rutaReservacionesActivas') }}"
    >
      @csrf

      {{-- Hidden “state” --}}
      <input type="hidden" id="categoria_id" name="categoria_id" value="">
      <input type="hidden" id="proteccion_id" name="proteccion_id" value="">
      <div id="addonsHidden"></div>

      {{-- ✅ FIX: ciudades (FK -> id_ciudad) --}}
      <input type="hidden" id="ciudad_retiro"  name="ciudad_retiro"  value="">
      <input type="hidden" id="ciudad_entrega" name="ciudad_entrega" value="">

      {{-- ✅ Servicios (switch) --}}
      <input type="hidden" id="svc_dropoff"  name="svc_dropoff"  value="0">
      <input type="hidden" id="svc_delivery" name="svc_delivery" value="0">
      <input type="hidden" id="svc_gasolina" name="svc_gasolina" value="0">

      {{-- ✅ Precio por litro (Gasolina prepago) -> lo usa el JS --}}
      <input type="hidden" id="gasolinaPrecioLitro" value="24">

      {{-- Hidden wrap para individuales --}}
      <div id="insHidden"></div>

      {{-- ✅ Teléfono final (backend) --}}
      <input type="hidden" id="telefono_cliente" name="telefono_cliente" value="">
      <input type="hidden" id="telefono_lada" name="telefono_lada" value="+52">

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

      {{-- ======================
           RETIRO
      ======================= --}}
      <div>
        <label>Sucursal de retiro</label>
        <select id="sucursal_retiro" name="sucursal_retiro" class="input" required>
          <option value="">Selecciona punto de entrega</option>

          @foreach($sucursales as $ciudad => $grupo)
            <optgroup label="{{ $ciudad }} — {{ $ciudad }}">
              @foreach($grupo as $s)
                <option value="{{ $s->id_sucursal }}"
                        data-ciudad-id="{{ $s->id_ciudad }}"
                        data-nombre="{{ $s->sucursal }}">
                  {{ $s->sucursal }}
                </option>
              @endforeach
            </optgroup>
          @endforeach

        </select>

        {{-- CAMPO VUELO --}}
        <div id="campo_vuelo" style="display:none; margin-top:10px;">
          <label>Número de vuelo</label>
          <input type="text"
                 name="numero_vuelo"
                 id="numero_vuelo"
                 class="input"
                 placeholder="Ej. AA1234">
        </div>
      </div>

      {{-- ======================
           ENTREGA
      ======================= --}}
      <div>
        <label>Sucursal de entrega</label>
        <select id="sucursal_entrega" name="sucursal_entrega" class="input" required>
          <option value="">Selecciona punto de devolución</option>

          @foreach($sucursales as $ciudad => $grupo)
            <optgroup label="{{ $ciudad }} — {{ $ciudad }}">
              @foreach($grupo as $s)
                <option value="{{ $s->id_sucursal }}"
                        data-ciudad-id="{{ $s->id_ciudad }}"
                        data-nombre="{{ $s->sucursal }}">
                  {{ $s->sucursal }}
                </option>
              @endforeach
            </optgroup>
          @endforeach

        </select>
      </div>

    </div>
  </div>
</section>

      {{-- ======================
           2) FECHAS Y HORAS
      ======================= --}}
      <section class="stack-card">
        <div class="stack-head">
          <div class="stack-title">🗓️ Fechas y horas</div>
          <div class="stack-sub">Define inicio/fin. Se calculan los días automáticamente.</div>
        </div>

        <div class="stack-body">
          <datalist id="time10"></datalist>

          <style>
            .fp-backdrop{
              position:fixed; inset:0;
              background:rgba(0,0,0,.55);
              display:none; z-index:9998;
            }
            .fp-backdrop.is-open{ display:block; }

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

            .fp-actions{
              display:flex; justify-content:space-between; align-items:center;
              gap:10px;
              padding:10px 12px;
              border-top:1px solid #eef2f7;
              background:#fff;
            }
            .fp-actions button{
              border:0; background:transparent;
              font-weight:900; cursor:pointer;
            }
            .fp-actions .fp-today{ color:#2563eb; }
            .fp-actions .fp-clear{ color:#ef4444; }
            .fp-actions .fp-label{ color:#111827; opacity:.85; }

            .dt-field{ position:relative; }
            .dt-ico{
              position:absolute; right:12px; top:50%;
              transform:translateY(-50%);
              color:#9ca3af; pointer-events:none;
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
              <input id="fecha_inicio_ui" class="input input-lg fp-ui" type="text" placeholder="dd/mm/aaaa" autocomplete="off" required>
              <span class="dt-ico">📅</span>
              <input id="fecha_inicio" name="fecha_inicio" type="hidden">
            </div>

            <div class="dt-field">
              <label>Hora de salida</label>
              <input id="hora_retiro_ui" class="input input-lg fp-ui" type="text" placeholder="hh:mm" autocomplete="off" required>
              <span class="dt-ico">🕒</span>
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
          </div>
        </div>
      </section>

      {{-- ======================
           3) CATEGORÍA
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
        </div>
      </section>

      {{-- ======================
           4) SERVICIOS (SWITCHES)
      ======================= --}}

      @php
        $deliverySafe = $delivery ?? null;
        $ubicacionesSafe = $ubicaciones ?? [];
        $costoKmCategoriaSafe = $costoKmCategoria ?? 0;
        $idReservacionSafe = $reservacion->id_reservacion ?? null;
      @endphp

      <section class="stack-card">

        <div class="stack-head">
          <div class="stack-title">🧰 Servicios</div>
          <div class="stack-sub">Servicios adicionales.</div>
        </div>

        <div class="stack-body">

          <div class="svc-grid">

            {{-- 🚩 DROP OFF --}}
            <div class="svc-card svc-card--accent dropoff-wrapper">
              <div class="svc-top">
                <div class="svc-ico">🚩</div>
                <div class="svc-meta">
                  <div class="svc-name">Drop Off</div>
                  <div class="svc-desc">Entrega en sucursal distinta.</div>
                </div>
              </div>

              <div class="svc-bottom">
                <div class="svc-hint">Activar</div>
                <label class="switch switch-soft">
                  <input type="checkbox" id="dropoffToggle">
                  <span class="slider"></span>
                </label>
              </div>

              <div class="svc-fields" id="dropoffFields" style="display: none;">
                <div class="svc-field">
                  <label class="svc-label">Ubicación de devolución</label>
                  <select id="dropUbicacion" class="input">
                    <option value="">Seleccione...</option>
                    @foreach($ubicaciones as $u)
                      <option value="{{ $u->id_ubicacion }}" data-km="{{ $u->km ?? 0 }}">
                        {{ $u->estado }} - {{ $u->destino }} ({{ $u->km ?? 0 }} km)
                      </option>
                    @endforeach
                    <option value="0">Dirección personalizada</option>
                  </select>
                </div>

                <div class="svc-field" id="dropGroupDireccion" style="display: none;">
                  <label class="svc-label">Dirección</label>
                  <input type="text" id="dropDireccion" class="input" placeholder="Calle, No, Colonia...">
                </div>

                <div class="svc-field" id="dropGroupKm" style="display: none;">
                  <label class="svc-label">Kilómetros</label>
                  <input type="number" id="dropKm" class="input" placeholder="0">
                </div>

                <div class="svc-total">
                  <span>Total Drop Off</span>
                  <b id="dropTotal">$0.00 MXN</b>
                </div>

              </div>
              <input type="hidden" id="dropoffTotalHidden" value="0">
            </div>

            {{-- 🚚 DELIVERY --}}
            <div class="svc-card svc-card--accent delivery-wrapper"
              data-delivery-total="{{ $deliverySafe->total ?? 0 }}"
              data-costo-km="{{ $costoKmCategoriaSafe }}">
            
              <div class="svc-top">
                <div class="svc-ico">🚚</div>
                <div class="svc-meta">
                  <div class="svc-name">Delivery</div>
                  <div class="svc-desc">Entrega a domicilio.</div>
                </div>
              </div>

              <div class="svc-bottom">
                <div class="svc-hint">Activar</div>
                <label class="switch switch-soft">
                  <input type="checkbox" id="deliveryToggle" {{ !empty($deliverySafe->activo) ? 'checked' : '' }}>
                  <span class="slider"></span>
                </label>
              </div>

              <div class="svc-fields" id="deliveryFields" style="display: {{ !empty($deliverySafe->activo) ? 'block' : 'none' }};">
                
                <div class="svc-field">
                  <label class="svc-label">Seleccionar ubicación</label>
                  <select id="deliveryUbicacion" class="input">
                    <option value="">Seleccione...</option>
                    @foreach($ubicacionesSafe as $u)
                      <option value="{{ $u->id_ubicacion }}" data-km="{{ $u->km ?? 0 }}"
                              {{ (!empty($deliverySafe->id_ubicacion) && $deliverySafe->id_ubicacion == $u->id_ubicacion) ? 'selected' : '' }}>
                        {{ $u->estado }} - {{ $u->destino }} ({{ $u->km ?? 0 }} km)
                      </option>
                    @endforeach
                    <option value="0" {{ (isset($deliverySafe->id_ubicacion) && (int)$deliverySafe->id_ubicacion === 0) ? 'selected' : '' }}>Dirección personalizada</option>
                  </select>
                </div>

                <div class="svc-field" id="groupDireccion" style="display: none;">
                  <label class="svc-label">Dirección</label>
                  <input type="text" id="deliveryDireccion" class="input" placeholder="Calle, No, Colonia..." value="{{ $deliverySafe->direccion ?? '' }}">
                </div>

                <div class="svc-field" id="groupKm" style="display: none;">
                  <label class="svc-label">Kilómetros</label>
                  <input type="number" id="deliveryKm" class="input" placeholder="0" value="{{ $deliverySafe->km ?? 0 }}">
                </div>

                <div class="svc-total">
                  <span>Total Delivery</span>
                  <b id="deliveryTotal">${{ number_format($deliverySafe->total ?? 0, 2) }} MXN</b>
                </div>
              </div>

              <input type="hidden" id="deliveryTotalHidden" value="{{ $deliverySafe->total ?? 0 }}">
            </div>

            {{-- ⛽ GASOLINA PREPAGO --}}
            <div class="svc-card svc-card--accent">
              <div class="svc-top">
                <div class="svc-ico">⛽</div>
                <div class="svc-meta">
                  <div class="svc-name">Gasolina prepago</div>
                  <div class="svc-desc">Tanque completo preferencial.</div>
                </div>
              </div>

              <div class="svc-bottom">
                <div class="svc-hint">Activar</div>
                <label class="switch switch-soft">
                  <input type="checkbox" id="gasolinaToggle" data-litros="0" data-costo-litro="20">
                  <span class="slider"></span>
                </label>
              </div>

              <div class="svc-fields" id="gasolinaFields" style="display:none;">
                <div class="svc-total">
                  <span>Total Gasolina (<span id="litrosLabel">0</span>L)</span>
                  <b id="gasolinaTotal">$0.00 MXN</b>
                </div>
              </div>
              <input type="hidden" name="gasolina_prepago_valor" id="gasolinaTotalHidden" value="0">
            </div>

          </div>
          <input type="hidden" id="deliveryPrecioKm" value="0">
          <input type="hidden" name="svc_gasolina" id="svc_gasolina" value="0">
        </div>
        
      </section>

      {{-- ======================
           5) PROTECCIONES
      ======================= --}}
      <section class="stack-card">
        <div class="stack-head">
          <div class="stack-title">🔒 Protecciones</div>
          <div class="stack-sub">Elige paquete o arma tu combinación con protecciones individuales.</div>
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
           6) ADICIONALES
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
           7) CLIENTE
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
              <label>Apellidos</label>
              <input id="apellidos_cliente" name="apellidos_cliente" class="input" type="text" required>
            </div>

            <div>
              <label>Email</label>
              <input id="email_cliente" name="email_cliente" class="input" type="email" required>
            </div>

            <div>
              <label>Teléfono</label>
              <div class="phone-grid" id="phoneCombo">
                <button class="phone-prefix" type="button" id="phone_toggle" aria-label="Elegir país">
                  <span class="flag" id="phone_flag">🇲🇽</span>
                  <span class="code" id="phone_code">+52</span>
                  <span class="chev">▾</span>
                </button>

                <input id="telefono_ui" class="input" type="tel" inputmode="tel" placeholder="4421234567" required>

                <div class="combo-dd phone-dd" id="phone_dd" role="listbox" aria-label="Lista de ladas">
                  <div class="dd-head">
                    <input id="phone_search" class="dd-search" type="text" placeholder="Buscar país o lada…">
                  </div>
                  <div class="dd-list" id="phone_list"></div>
                </div>
              </div>
            </div>

            <div>
              <label>País</label>
              <input type="hidden" id="pais" name="pais" value="MÉXICO">
              <div class="input readonly-country">
                <span id="pais_flag_ui">🇲🇽</span>
                <span id="pais_text_ui">México</span>
              </div>
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
          color:#111827;
        }
        #catPop .cp-right .cp-price{ padding:10px 0; border-bottom:1px dashed rgba(0,0,0,.10); }
        #catPop .cp-right .cp-price:last-of-type{ border-bottom:0; }
        #catPop .cat-estimado{ font-weight:900; }
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
            11 => asset('img/Tacoma.png'),
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
            $img = $imgCategorias[$cat->id_categoria] ?? asset('img/Logotipo.png');
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
            data-precio-km="{{ $cat->costo_km ?? 0 }}"
            data-img="{{ $img }}"
            data-litros="{{ $cat->litros_maximos ?? 0 }}"
          >
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

              <div class="cp-price" style="margin-top:10px;">
                <div class="muted small">Estimado (base × días)</div>
                <div class="price-big">
                  <span class="cat-estimado">$0.00</span> <span>MXN</span>
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

{{-- ✅ MODAL: PROTECCIONES --}}
<div class="pop modal" id="proteccionPop">
  <div class="box modal-box modal-prote-tabs">
    <header class="modal-head">
      <div class="modal-title">🔒 Protecciones</div>
      <button class="btn gray" id="proteClose" type="button">✖</button>
    </header>

    <style>
      #proteccionPop .tabs-bar{
        display:flex; gap:10px; align-items:center;
        padding:12px 14px;
        border-bottom:1px solid rgba(17,24,39,.08);
        background:#fff;
      }
      #proteccionPop .tab-btn{
        border:1px solid rgba(17,24,39,.12);
        background:#f8fafc;
        color:#111827;
        padding:10px 14px;
        border-radius:999px;
        font-weight:900;
        cursor:pointer;
        display:inline-flex;
        gap:8px;
        align-items:center;
      }
      #proteccionPop .tab-btn.is-active{
        background:rgba(178,34,34,.10);
        border-color:rgba(178,34,34,.30);
        color:#7a1414;
      }
      #proteccionPop .tab-panel{ display:none; }
      #proteccionPop .tab-panel.is-active{ display:block; }
      #proteccionPop .scroll-h{
        display:flex;
        gap:12px;
        overflow-x:auto;
        padding:10px 2px 14px;
        scroll-snap-type:x mandatory;
        -webkit-overflow-scrolling:touch;
      }
      #proteccionPop .scroll-h::-webkit-scrollbar{ height:10px; }
      #proteccionPop .scroll-h::-webkit-scrollbar-thumb{
        background:rgba(17,24,39,.18);
        border-radius:999px;
      }
      #proteccionPop .cat-title{
        margin:14px 0 8px;
        font-weight:1000;
        color:#111827;
        text-transform:uppercase;
        letter-spacing:.02em;
        font-size:14px;
      }
      #proteccionPop .ins-card{
        min-width:280px;
        max-width:320px;
        background:#fff;
        border:1px solid rgba(17,24,39,.10);
        border-radius:16px;
        box-shadow:0 10px 26px rgba(0,0,0,.08);
        scroll-snap-align:start;
        padding:14px;
        user-select:none;
      }
      #proteccionPop .ins-card h4{ margin:0 0 6px; color:#111827; font-weight:1000; }
      #proteccionPop .ins-card p{ margin:0 0 10px; color:#6b7280; font-weight:700; }
      #proteccionPop .ins-card .precio{ font-weight:1000; color:#111827; }
      #proteccionPop .ins-card .precio span{ font-weight:900; color:#6b7280; margin-left:6px; }
      #proteccionPop .ins-card .small{ margin-top:8px; font-weight:900; color:#6b7280; }
      #proteccionPop .ins-card.is-selected{
        border-color:rgba(178,34,34,.55);
        box-shadow:0 16px 40px rgba(178,34,34,.18);
      }
      #proteccionPop .switch-individual{
        width:44px; height:26px;
        border-radius:999px;
        background:rgba(17,24,39,.12);
        position:relative;
        margin-top:10px;
      }
      #proteccionPop .switch-individual::after{
        content:"";
        position:absolute;
        width:20px; height:20px;
        border-radius:999px;
        background:#fff;
        top:3px; left:3px;
        box-shadow:0 8px 20px rgba(0,0,0,.18);
        transition:.15s ease;
      }
      #proteccionPop .switch-individual.is-on{
        background:rgba(178,34,34,.85);
      }
      #proteccionPop .switch-individual.is-on::after{
        left:21px;
      }
      #proteccionPop .foot-split{
        display:flex;
        justify-content:space-between;
        gap:12px;
      }
    </style>

    <div class="tabs-bar">
      <button type="button" class="tab-btn is-active" data-tab="tab-paquetes">🛡️ Protecciones</button>
      <button type="button" class="tab-btn" data-tab="tab-individuales">🧩 Protecciones individuales</button>
    </div>

    <div class="modal-body">
      <section class="tab-panel is-active" id="tab-paquetes">
        <div class="note" style="margin-bottom:14px;">Elige un paquete de protección.</div>
        <div class="scroll-h" id="protePacksTrack" aria-label="Carrusel de protecciones">
          <div class="loading" style="padding:12px; font-weight:900; color:#111827;">Cargando paquetes...</div>
        </div>
      </section>

      <section class="tab-panel" id="tab-individuales">
        <div class="note" style="margin-bottom:14px;">
          Selecciona una o varias protecciones individuales.
        </div>

        <h4 class="cat-title">Colisión y robo</h4>
        <div class="scroll-h" id="insColisionTrack">
          @forelse(($grupo_colision ?? []) as $ind)
            <label class="ins-card individual-item" data-id="{{ $ind->id_individual }}" data-precio="{{ $ind->precio_por_dia }}" style="cursor:pointer;">
              <div class="body">
                <h4>{{ $ind->nombre }}</h4>
                <p>{{ $ind->descripcion }}</p>
                <div class="precio">${{ number_format($ind->precio_por_dia, 2) }} <span>MXN x Día</span></div>
                <div class="switch switch-individual" data-id="{{ $ind->id_individual }}"></div>
                <div class="small">Incluir</div>
              </div>
            </label>
          @empty
            <div class="muted" style="padding:10px 0; font-weight:800;">Sin opciones en esta categoría.</div>
          @endforelse
        </div>

        <h4 class="cat-title">Gastos médicos</h4>
        <div class="scroll-h" id="insMedicosTrack">
          @forelse(($grupo_medicos ?? []) as $ind)
            <label class="ins-card individual-item" data-id="{{ $ind->id_individual }}" data-precio="{{ $ind->precio_por_dia }}" style="cursor:pointer;">
              <div class="body">
                <h4>{{ $ind->nombre }}</h4>
                <p>{{ $ind->descripcion }}</p>
                <div class="precio">${{ number_format($ind->precio_por_dia, 2) }} <span>MXN x Día</span></div>
                <div class="switch switch-individual" data-id="{{ $ind->id_individual }}"></div>
                <div class="small">Incluir</div>
              </div>
            </label>
          @empty
            <div class="muted" style="padding:10px 0; font-weight:800;">Sin opciones en esta categoría.</div>
          @endforelse
        </div>

        <h4 class="cat-title">Asistencia para el camino</h4>
        <div class="scroll-h" id="insCaminoTrack">
          @forelse(($grupo_asistencia ?? []) as $ind)
            <label class="ins-card individual-item" data-id="{{ $ind->id_individual }}" data-precio="{{ $ind->precio_por_dia }}" style="cursor:pointer;">
              <div class="body">
                <h4>{{ $ind->nombre }}</h4>
                <p>{{ $ind->descripcion }}</p>
                <div class="precio">${{ number_format($ind->precio_por_dia, 2) }} <span>MXN x Día</span></div>
                <div class="switch switch-individual" data-id="{{ $ind->id_individual }}"></div>
                <div class="small">Incluir</div>
              </div>
            </label>
          @empty
            <div class="muted" style="padding:10px 0; font-weight:800;">Sin opciones en esta categoría.</div>
          @endforelse
        </div>

        <h4 class="cat-title">Daños a terceros</h4>
        <div class="scroll-h" id="insTercerosTrack">
          @forelse(($grupo_terceros ?? []) as $ind)
            <label class="ins-card individual-item" data-id="{{ $ind->id_individual }}" data-precio="{{ $ind->precio_por_dia }}" style="cursor:pointer;">
              <div class="body">
                <h4>{{ $ind->nombre }}</h4>
                <p>{{ $ind->descripcion }}</p>
                <div class="precio">${{ number_format($ind->precio_por_dia, 2) }} <span>MXN x Día</span></div>
                <div class="switch switch-individual" data-id="{{ $ind->id_individual }}"></div>
                <div class="small">Incluir</div>
              </div>
            </label>
          @empty
            <div class="muted" style="padding:10px 0; font-weight:800;">Sin opciones en esta categoría.</div>
          @endforelse
        </div>

        <h4 class="cat-title">Protecciones automáticas</h4>
        <div class="scroll-h" id="insAutoTrack">
          @forelse(($grupo_protecciones ?? []) as $ind)
            <label class="ins-card individual-item" data-id="{{ $ind->id_individual }}" data-precio="{{ $ind->precio_por_dia }}" style="cursor:pointer;">
              <div class="body">
                <h4>{{ $ind->nombre }}</h4>
                <p>{{ $ind->descripcion }}</p>
                <div class="precio">${{ number_format($ind->precio_por_dia, 2) }} <span>MXN x Día</span></div>
                <div class="switch switch-individual" data-id="{{ $ind->id_individual }}"></div>
                <div class="small">Incluir</div>
              </div>
            </label>
          @empty
            <div class="muted" style="padding:10px 0; font-weight:800;">Sin opciones en esta categoría.</div>
          @endforelse
        </div>
      </section>
    </div>

    <footer class="modal-foot foot-split">
      <button class="btn gray" id="proteCancel" type="button">Cerrar</button>
      <button class="btn primary" id="proteApply" type="button">Aplicar</button>
    </footer>
  </div>
</div>

{{-- MODAL: ADICIONALES --}}
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

{{-- MODAL: RESUMEN --}}
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
        <div class="res-row">
          <div>
            Tarifa base 
            <button type="button" id="btnEditarTarifa" style="background:none;border:none;color:#2563eb;cursor:pointer;font-size:16px;margin-left:6px;">
              ✏️
            </button>
          </div>
            <div id="resBaseDia">—</div>
        </div>
        <div class="res-row"><div>Base × días</div><div id="resBaseTotal">—</div></div>

        <div class="res-row"><div>🧰 Servicios</div><div id="resServicios">—</div></div>

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

{{-- MODAL: CONFIRMACIÓN --}}
<div class="pop modal" id="confirmPop" style="display:none;">
  <div class="box modal-box">
    <header class="modal-head">
      <div class="modal-title">✅ Reservación registrada</div>
      <button class="btn gray" id="confirmClose" type="button">✖</button>
    </header>

    <div class="modal-body">
      <p style="margin:0; font-weight:800; color:#111827;">
        ¡Listo! La reservación se registró correctamente.
      </p>
      <p class="muted" style="margin:8px 0 0;">
        Te enviaremos a <b>Bookings</b>.
      </p>
    </div>

    <footer class="modal-foot">
      <button class="btn primary" id="confirmOk" type="button">Ir a Bookings</button>
    </footer>
  </div>
</div>

@section('js-vistareservacionesAdmin')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<script src="https://cdn.jsdelivr.net/npm/flatpickr/dist/l10n/es.js"></script>
<script>
document.addEventListener("DOMContentLoaded", function(){

    const retiro = document.getElementById("sucursal_retiro");
    const entrega = document.getElementById("sucursal_entrega");
    const vueloWrap = document.getElementById("vueloWrap");

    function esAeropuerto(select){
        if(!select) return false;
        const opt = select.options[select.selectedIndex];
        if(!opt) return false;

        const nombre = (opt.dataset.nombre || "").toLowerCase();
        return nombre.includes("aeropuerto");
    }

    function validarVuelo(){
        if(esAeropuerto(retiro) || esAeropuerto(entrega)){
            vueloWrap.style.display = "block";
        }else{
            vueloWrap.style.display = "none";
            document.getElementById("no_vuelo").value = "";
        }
    }

    retiro.addEventListener("change", validarVuelo);
    entrega.addEventListener("change", validarVuelo);

});
</script>
@endsection

@endsection
