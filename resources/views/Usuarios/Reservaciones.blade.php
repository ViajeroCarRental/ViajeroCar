@extends('layouts.Usuarios')

@section('Titulo','Reservaciones')

@section('css-vistaReservaciones')
  <link rel="stylesheet" href="{{ asset('css/reservaciones.css') }}">
@endsection

@section('contenidoReservaciones')
@php
  // ====== Estado recibido por GET (sin sesión) ======
  $f = $filters ?? [];

  $pickupSucursalId  = $f['pickup_sucursal_id']  ?? request('pickup_sucursal_id');
  $dropoffSucursalId = $f['dropoff_sucursal_id'] ?? request('dropoff_sucursal_id');

  // =========================
  // ✅ Fechas robustas (ISO para lógica, DMY para UI)
  // =========================
  $pickupDateRaw  = $f['pickup_date']  ?? request('pickup_date');
  $dropoffDateRaw = $f['dropoff_date'] ?? request('dropoff_date');

  $pickupTime  = $f['pickup_time']  ?? request('pickup_time')  ?? '12:00';
  $dropoffTime = $f['dropoff_time'] ?? request('dropoff_time') ?? '12:00';

  $pickupIsoDefault  = now()->toDateString();
  $dropoffIsoDefault = now()->addDays(3)->toDateString();

  $toIso = function($val, $defaultIso){
    $val = is_string($val) ? trim($val) : '';
    if ($val === '') return $defaultIso;

    // d-m-Y
    if (preg_match('/^\d{2}-\d{2}-\d{4}$/', $val)) {
      try { return \Illuminate\Support\Carbon::createFromFormat('d-m-Y', $val)->format('Y-m-d'); }
      catch(\Throwable $e) {}
    }

    // Y-m-d
    if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $val)) return $val;

    // fallback
    try { return \Illuminate\Support\Carbon::parse($val)->format('Y-m-d'); }
    catch(\Throwable $e) {}

    return $defaultIso;
  };

  // ✅ ISO real (para cálculos y Step 4)
  $pickupDateISO  = $toIso($pickupDateRaw,  $pickupIsoDefault);
  $dropoffDateISO = $toIso($dropoffDateRaw, $dropoffIsoDefault);

  // ✅ UI (para inputs flatpickr dd-mm-YYYY)
  $pickupDate  = \Illuminate\Support\Carbon::parse($pickupDateISO)->format('d-m-Y');
  $dropoffDate = \Illuminate\Support\Carbon::parse($dropoffDateISO)->format('d-m-Y');

  // ✅ FIX: Si pickup_date viene como rango "YYYY-MM-DD a YYYY-MM-DD"
  if (is_string($pickupDateRaw) && str_contains($pickupDateRaw, ' a ')) {
    [$start, $end] = array_map('trim', explode(' a ', $pickupDateRaw, 2));
    $pickupDateISO  = $toIso($start, $pickupIsoDefault);
    $dropoffDateISO = $toIso($end,   $dropoffIsoDefault);

    $pickupDate  = \Illuminate\Support\Carbon::parse($pickupDateISO)->format('d-m-Y');
    $dropoffDate = \Illuminate\Support\Carbon::parse($dropoffDateISO)->format('d-m-Y');
  }

  // ✅ flujo por categoría
  $categoriaId       = $f['categoria_id']        ?? request('categoria_id');
  $plan              = $f['plan']                ?? request('plan'); // mostrador / linea

  // ✅ addons (Step 3) via GET (persistencia)
  $addonsParam       = $f['addons']              ?? request('addons', '');

  // ==========================================================
  // ✅ STEP RESOLVER (FIX REAL)
  // ==========================================================
  $requestedStep  = (int) request('step', 1);
  $controllerStep = isset($step) ? (int)$step : null;

  $isFreshEntry = empty($pickupSucursalId) || empty($dropoffSucursalId);
  $stepCurrent  = $isFreshEntry ? 1 : ($controllerStep ?? $requestedStep);

  if ($stepCurrent >= 2 && $isFreshEntry) {
    $stepCurrent = 1;
  }
  if ($stepCurrent >= 3 && (empty($categoriaId) || empty($plan))) {
    $stepCurrent = 2;
  }
  if ($stepCurrent >= 4 && (empty($categoriaId) || empty($plan))) {
    $stepCurrent = 2;
  }

  // Nombres de sucursales para el encabezado (fallback a $ciudades)
  $pickupName  = null;
  $dropoffName = null;

  if (!empty($sucursales)) {
    $sucById = collect($sucursales)->keyBy('id_sucursal');
    $pickupName  = $pickupSucursalId  ? optional($sucById->get((int)$pickupSucursalId))->nombre  : null;
    $dropoffName = $dropoffSucursalId ? optional($sucById->get((int)$dropoffSucursalId))->nombre : null;
  }

  if ((!$pickupName || !$dropoffName) && !empty($ciudades)) {
    $map = collect($ciudades)
      ->flatMap(fn($c)=>collect($c->sucursalesActivas ?? []))
      ->keyBy('id_sucursal');
    $pickupName  = $pickupName  ?: ($pickupSucursalId  ? optional($map->get((int)$pickupSucursalId))->nombre  : null);
    $dropoffName = $dropoffName ?: ($dropoffSucursalId ? optional($map->get((int)$dropoffSucursalId))->nombre : null);
  }

  // Base params (incluye addons para NO perderlos)
  $baseParams = array_filter([
    'pickup_sucursal_id'  => $pickupSucursalId,
    'dropoff_sucursal_id' => $dropoffSucursalId,
    'pickup_date'  => $pickupDate,
    'pickup_time'  => $pickupTime,
    'dropoff_date' => $dropoffDate,
    'dropoff_time' => $dropoffTime,
    'categoria_id' => $categoriaId,
    'plan'         => $plan,
    'addons'       => $addonsParam,
  ], fn($v)=>$v!==null && $v!=='');

  $toStep = function(int $to, array $extra = []) use ($baseParams) {
    return route('rutaReservasIniciar', array_merge($baseParams, ['step'=>$to], $extra));
  };

  // ✅ días (mínimo 1)
  $d1 = \Illuminate\Support\Carbon::parse($pickupDateISO);
  $d2 = \Illuminate\Support\Carbon::parse($dropoffDateISO);
  $days = max(1, $d1->diffInDays($d2));

  // ✅ categoría seleccionada
  $categoriaSel = null;
  if (!empty($categorias) && $categoriaId) {
    $categoriaSel = collect($categorias)->firstWhere('id_categoria', (int)$categoriaId);
  }

  // ✅ IMÁGENES
  $catImages = [
    1  => asset('img/aveo.png'),
    2  => asset('img/virtus.png'),
    3  => asset('img/jetta.png'),
    4  => asset('img/camry.png'),
    5  => asset('img/renegade.png'),
    6  => asset('img/seltos.png'),
    7  => asset('img/avanza.png'),
    8  => asset('img/Odyssey.png'),
    9  => asset('img/Hiace.png'),
    10 => asset('img/Frontier.png'),
    11 => asset('img/Tacoma.png'),
  ];

  $placeholder = asset('img/Logotipo.png');

  $categoriaImg = $categoriaSel
    ? ($catImages[$categoriaSel->id_categoria] ?? $placeholder)
    : $placeholder;

  $precioDiaCategoria = (float)($categoriaSel->precio_dia ?? 0);
  $tarifaBase = $precioDiaCategoria > 0 ? ($precioDiaCategoria * $days) : 0.0;

  // split hora/min para Step 1 (UI)
  [$ph, $pm] = array_pad(explode(':', $pickupTime ?? '12:00'), 2, '00');
  [$dh, $dm] = array_pad(explode(':', $dropoffTime ?? '12:00'), 2, '00');
@endphp

<main class="page wizard-page"
      data-current-step="{{ $stepCurrent }}"
      data-plan="{{ $plan ?? '' }}"
      style="position:relative; overflow:visible;">

  {{-- ✅ Fondo SOLO dentro del main (NO footer) --}}
  <div class="reservas-bg" aria-hidden="true"></div>

  <style>
    /* ✅ FIX: overlay NO bloquea inputs/clicks */
    .reservas-bg{
      position:absolute;
      inset:0;
      z-index:0;
      pointer-events:none;
      background:
        linear-gradient(180deg, rgba(15,23,42,.70), rgba(15,23,42,.62)),
        url("{{ asset('img/4x4.png') }}");
      background-size:cover;
      background-position:center;
      background-repeat:no-repeat;
    }
    .wizard-steps, .wizard-card{ position:relative; z-index:1; }

    .ctl{ position:relative; display:flex; align-items:center; width:100%; }
    .ctl .ico{
      position:absolute;
      left:14px; top:50%;
      transform:translateY(-50%);
      width:18px; height:18px;
      display:grid; place-items:center;
      color:#9ca3af;
      pointer-events:none;
      font-size:15px;
    }
    .sum-form .sum-personal-grid .ctl:has(#dob) > .ico{
      top: calc(50% + 14px);
      transform: translateY(-50%);
    }

    .ctl input, .ctl select{
      width:100%;
      height:54px;
      padding:0 14px;
      border-radius:14px;
      border:1px solid #e5e7eb;
      background:#fff;
      font-weight:800;
      color:#111827;
      outline:none;
      transition:border-color .15s ease, box-shadow .15s ease;
    }
    .ctl.has-ico input, .ctl.has-ico select{ padding-left:46px; }
    .ctl input:focus, .ctl select:focus{
      border-color: #b22222;
      box-shadow:0 0 0 4px rgba(178,34,34,.12);
    }
    .ctl select{
      appearance:none;
      -webkit-appearance:none;
      -moz-appearance:none;
      background-image:
        linear-gradient(45deg, transparent 50%, #9ca3af 50%),
        linear-gradient(135deg, #9ca3af 50%, transparent 50%);
      background-position:
        calc(100% - 18px) 50%,
        calc(100% - 12px) 50%;
      background-size:6px 6px, 6px 6px;
      background-repeat:no-repeat;
      padding-right:34px;
    }

    .wizard-form .field label{
      display:block;
      text-align:center;
      font-size:12px;
      color:#6b7280;
      font-weight:900;
      text-transform:uppercase;
      letter-spacing:.45px;
      margin-bottom:8px;
    }
    .wizard-form .group-title{
      font-weight:900;
      text-transform:uppercase;
      letter-spacing:.45px;
      font-size:13px;
      color:#6b7280;
    }

    @media (min-width:981px){
      .search-grid > .group-card{
        padding-right:16px;
        border-right:1px solid #eef2f7;
      }
      .search-grid > .group-card:last-child{
        border-right:0;
        padding-right:0;
      }
      .group-head{
        margin:0 0 10px;
        padding-bottom:8px;
        border-bottom:1px dashed rgba(178,34,34,.22);
      }
    }
  </style>

  {{-- ===================== PASOS ARRIBA ===================== --}}
  <nav class="wizard-steps" aria-label="Pasos">
    <a class="wizard-step {{ $stepCurrent>1?'done':'' }} {{ $stepCurrent===1?'active':'' }}" href="{{ $toStep(1) }}">
      <span class="n">1</span> Generales
    </a>
    <a class="wizard-step {{ $stepCurrent>2?'done':'' }} {{ $stepCurrent===2?'active':'' }}" href="{{ $toStep(2) }}">
      <span class="n">2</span> Categoría
    </a>
    <a class="wizard-step {{ $stepCurrent>3?'done':'' }} {{ $stepCurrent===3?'active':'' }}" href="{{ $toStep(3) }}">
      <span class="n">3</span> Adicionales
    </a>
    <a class="wizard-step {{ $stepCurrent===4?'active':'' }}" href="{{ $toStep(4) }}">
      <span class="n">4</span> Confirmación
    </a>
  </nav>

  <section class="wizard-card">

    {{-- ===================== STEP 1 ===================== --}}
    @if($stepCurrent===1)
      <header class="wizard-head">
        <h2>Sobre tu reservación</h2>
      </header>

      <form method="GET" action="{{ route('rutaReservasIniciar') }}" class="wizard-form" id="step1Form">
        <input type="hidden" name="step" value="2">

        @if(!empty($addonsParam))
          <input type="hidden" name="addons" value="{{ $addonsParam }}">
        @endif

        <div class="search-grid">

          {{-- Lugar --}}
          <div class="group-card">
            <div class="group-head"><div class="group-title">Lugar</div></div>

            <div class="field-row">
              <div class="field">
                <div class="ctl has-ico pristine" data-float>
                  <span class="ico"><i class="fa-solid fa-location-dot"></i></span>
                  <span class="flabel">Lugar de Pick-Up</span>
                  <select name="pickup_sucursal_id" required data-float-select>
                    <option value="" disabled {{ $pickupSucursalId ? '' : 'selected' }}></option>
                    @foreach(($ciudades ?? []) as $ciudad)
                      <optgroup label="{{ $ciudad->nombre }}{{ $ciudad->estado ? ' — '.$ciudad->estado : '' }}">
                        @foreach($ciudad->sucursalesActivas ?? [] as $suc)
                          <option value="{{ $suc->id_sucursal }}" {{ (string)$pickupSucursalId===(string)$suc->id_sucursal ? 'selected' : '' }}>
                            {{ $suc->nombre }}
                          </option>
                        @endforeach
                      </optgroup>
                    @endforeach
                  </select>
                </div>
              </div>

              <div class="field">
                <div class="ctl has-ico pristine" data-float>
                  <span class="ico"><i class="fa-solid fa-location-dot"></i></span>
                  <span class="flabel">Lugar de devolución</span>
                  <select name="dropoff_sucursal_id" required data-float-select>
                    <option value="" disabled {{ $dropoffSucursalId ? '' : 'selected' }}></option>
                    @foreach(($ciudades ?? []) as $ciudad)
                      <optgroup label="{{ $ciudad->nombre }}{{ $ciudad->estado ? ' — '.$ciudad->estado : '' }}">
                        @foreach($ciudad->sucursalesActivas ?? [] as $suc)
                          <option value="{{ $suc->id_sucursal }}" {{ (string)$dropoffSucursalId===(string)$suc->id_sucursal ? 'selected' : '' }}>
                            {{ $suc->nombre }}
                          </option>
                        @endforeach
                      </optgroup>
                    @endforeach
                  </select>
                </div>
              </div>
            </div>
          </div>

          {{-- Pick-Up --}}
          <div class="group-card">
            <div class="group-head"><div class="group-title">Pick-Up</div></div>

            <div class="field-row">
              <div class="field">
                <div class="ctl has-ico pristine" data-float>
                  <span class="ico"><i class="fa-solid fa-calendar-days"></i></span>
                  <span class="flabel">Fecha de Pick-Up</span>
                  <input id="start" name="pickup_date" type="text" value="{{ $pickupDate }}" required data-float-input>
                </div>
              </div>

              <input type="hidden" name="pickup_time" id="pickup_time_hidden" value="{{ $pickupTime }}">

              <div class="time-split">
                <div class="field">
                  <div class="ctl has-ico pristine" data-float>
                    <span class="ico"><i class="fa-regular fa-clock"></i></span>
                    <span class="flabel">Hora</span>
                    <select id="pickup_h" name="pickup_h" required data-float-select>
                      <option value="" disabled {{ empty($ph) ? 'selected' : '' }}>H</option>
                      @for($i=0;$i<=23;$i++)
                        @php $hh = str_pad((string)$i,2,'0',STR_PAD_LEFT); @endphp
                        <option value="{{ $hh }}" {{ $hh===$ph ? 'selected':'' }}>{{ $hh }}</option>
                      @endfor
                    </select>
                  </div>
                </div>

                <div class="field">
                  <div class="ctl pristine" data-float>
                    <span class="flabel">Min</span>
                    <select id="pickup_m" name="pickup_m" required data-float-select>
                      <option value="" disabled {{ empty($pm) ? 'selected' : '' }}>Min</option>
                      @foreach(['00','15','30','45'] as $mm)
                        <option value="{{ $mm }}" {{ $mm===$pm ? 'selected':'' }}>{{ $mm }}</option>
                      @endforeach
                    </select>
                  </div>
                </div>
              </div>

            </div>
          </div>

          {{-- Devolución --}}
          <div class="group-card">
            <div class="group-head"><div class="group-title">Devolución</div></div>

            <div class="field-row">
              <div class="field">
                <div class="ctl has-ico pristine" data-float>
                  <span class="ico"><i class="fa-solid fa-calendar-days"></i></span>
                  <span class="flabel">Fecha de devolución</span>
                  <input id="end" name="dropoff_date" type="text" value="{{ $dropoffDate }}" required data-float-input>
                </div>
              </div>

              <input type="hidden" name="dropoff_time" id="dropoff_time_hidden" value="{{ $dropoffTime }}">

              <div class="time-split">
                <div class="field">
                  <div class="ctl has-ico pristine" data-float>
                    <span class="ico"><i class="fa-regular fa-clock"></i></span>
                    <span class="flabel">Hora</span>
                    <select id="dropoff_h" name="dropoff_h" required data-float-select>
                      <option value="" disabled></option>
                      @for($i=0;$i<=23;$i++)
                        @php $hh = str_pad((string)$i,2,'0',STR_PAD_LEFT); @endphp
                        <option value="{{ $hh }}" {{ $hh===$dh ? 'selected':'' }}>{{ $hh }}</option>
                      @endfor
                    </select>
                  </div>
                </div>

                <div class="field">
                  <div class="ctl pristine" data-float>
                    <span class="flabel">Min</span>
                    <select id="dropoff_m" name="dropoff_m" required data-float-select>
                      <option value="" disabled></option>
                      @foreach(['00','15','30','45'] as $mm)
                        <option value="{{ $mm }}" {{ $mm===$dm ? 'selected':'' }}>{{ $mm }}</option>
                      @endforeach
                    </select>
                  </div>
                </div>
              </div>

            </div>
          </div>

        </div>

        <div class="wizard-nav">
          <a class="btn btn-ghost" href="{{ route('rutaReservasIniciar', ['step'=>1]) }}">Limpiar</a>
          <button class="btn btn-primary" type="submit">Siguiente</button>
        </div>
      </form>
    @endif

    {{-- ===================== STEP 2 ===================== --}}
    @if($stepCurrent===2)
      <header class="wizard-head">
        <h2>Selecciona tu categoría</h2>
        <p>Tarifa de <strong id="daysLabel">{{ $days }}</strong> día(s) de tu renta.</p>
      </header>

      <div class="cars">
        @forelse(($categorias ?? []) as $cat)
          @php
            $imgCat = $catImages[$cat->id_categoria] ?? $placeholder;

            $prepagoDia   = (float)($cat->precio_dia ?? 0);
            $mostradorDia = round($prepagoDia * 1.15);

            $prepagoTotal   = $prepagoDia * $days;
            $mostradorTotal = $mostradorDia * $days;

            $pasajeros    = (int)($cat->pasajeros ?? 5);
            $malChicas    = (int)($cat->maletas_chicas ?? 2);
            $malGrandes   = (int)($cat->maletas_grandes ?? 1);
            $appleCarplay = (int)($cat->apple_carplay ?? 0);
            $androidAuto  = (int)($cat->android_auto ?? 0);

            $desc = $cat->ejemplo ?? ($cat->descripcion ?? 'Auto o similar. Tarifas sujetas a disponibilidad y temporada.');

            $ahorroPct = ($mostradorTotal > 0)
              ? round((($mostradorTotal - $prepagoTotal) / $mostradorTotal) * 100)
              : 0;
            $ahorroPct = max(0, $ahorroPct);
          @endphp

          <article class="car-card {{ (string)$categoriaId===(string)$cat->id_categoria ? 'active' : '' }}"
                   data-prepago-dia="{{ $prepagoDia }}"
                   data-mostrador-dia="{{ $mostradorDia }}">

            <div class="car-days-badge">
              <i class="fa-solid fa-calendar-days"></i>
              <span class="js-days-badge">{{ $days }}</span> día(s)
            </div>

            <div class="car-avatar">
              <img src="{{ $imgCat }}" alt="{{ $cat->nombre }}" onerror="this.onerror=null;this.src='{{ $placeholder }}';">
            </div>

            <div class="car-meta">
              <div class="car-top">{{ strtoupper($cat->nombre) }}</div>
              <div class="car-sub">{{ $desc }}</div>

              <div class="r-specs" style="margin-top:10px;">
                <span class="chip"><i class="fa-solid fa-user"></i> {{ $pasajeros }} pasajeros</span>
                <span class="chip"><i class="fa-solid fa-suitcase-rolling"></i> {{ $malChicas }} maletas chicas</span>
                <span class="chip"><i class="fa-solid fa-suitcase"></i> {{ $malGrandes }} maletas grandes</span>

                @if($appleCarplay)
                  <span class="chip chip-ok"><i class="fa-brands fa-apple"></i> Apple CarPlay</span>
                @endif
                @if($androidAuto)
                  <span class="chip chip-ok"><i class="fa-brands fa-android"></i> Android Auto</span>
                @endif
              </div>
            </div>

            <div class="car-price">

              {{-- ✅ PREPAGO EN LÍNEA --}}
              <div class="price-pill price-pill--prepago">
                <div class="price-old">
                  ${{ number_format($mostradorTotal,0) }} MXN
                </div>

                <div class="price-new">
                  $<span class="js-prepago-total">{{ number_format($prepagoTotal,0) }}</span> MXN
                </div>

                @if($ahorroPct > 0)
                  <div class="price-save">
                    Ahorra <strong class="js-ahorro">{{ $ahorroPct }}</strong>%
                  </div>
                @endif
              </div>

              <a class="btn-pay primary" href="{{ $toStep(3, ['categoria_id'=>$cat->id_categoria, 'plan'=>'linea']) }}">
                PREPAGAR EN LÍNEA
              </a>

              {{-- ✅ PAGO EN OFICINA --}}
              <div class="price-pill">
                $<span class="js-mostrador-total">{{ number_format($mostradorTotal,0) }}</span> MXN<br>
              </div>

              <a class="btn-pay gray" href="{{ $toStep(3, ['categoria_id'=>$cat->id_categoria, 'plan'=>'mostrador']) }}">
                PAGAR EN OFICINA
              </a>
            </div>
          </article>
        @empty
          <p>No hay categorías disponibles.</p>
        @endforelse
      </div>

      <div class="wizard-nav">
        <a class="btn btn-ghost" href="{{ $toStep(1) }}">Anterior</a>
        <a class="btn btn-primary" href="{{ $toStep(3) }}">Siguiente</a>
      </div>
    @endif

    {{-- ===================== STEP 3 ===================== --}}
    @if($stepCurrent===3)
      <header class="wizard-head">
        <h2>Complementos y adicionales</h2>
        <p>Selecciona los extras que quieras agregar a tu renta.</p>
      </header>

      <input type="hidden" id="addonsHidden" value="{{ $addonsParam }}">

      <div class="addons-grid">
        @forelse(($servicios ?? []) as $srv)
          @php
            $unidad = $srv->tipo_cobro === 'por_evento' ? '/ evento' : '/ día';
            $precio = number_format((float)$srv->precio, 0);
          @endphp

          <div class="addon-card"
               data-id="{{ $srv->id_servicio }}"
               data-name="{{ $srv->nombre }}"
               data-price="{{ (float)$srv->precio }}"
               data-charge="{{ $srv->tipo_cobro }}">
            <h4 class="addon-name">{{ $srv->nombre }}</h4>
            @if(!empty($srv->descripcion))
              <p>{{ $srv->descripcion }}</p>
            @endif

            <div class="small"><strong>${{ $precio }}</strong> MXN {{ $unidad }}</div>

            <div class="addon-qty">
              <button class="qty-btn minus" type="button">−</button>
              <span class="qty">0</span>
              <button class="qty-btn plus" type="button">+</button>
            </div>
          </div>
        @empty
          <div style="grid-column:1/-1; text-align:center; padding:.75rem 0;">
            No hay complementos disponibles por ahora.
          </div>
        @endforelse
      </div>

      <div class="wizard-nav">
        <a class="btn btn-ghost" href="{{ $toStep(2) }}">Anterior</a>
        <a class="btn btn-primary" id="toStep4" href="{{ $toStep(4) }}">Siguiente</a>
      </div>
    @endif

    {{-- ===================== STEP 4 ===================== --}}
    @if($stepCurrent===4)
      <header class="wizard-head">
        <h2>Resumen de tu itinerario</h2>
        <p>Verifica tus datos antes de reservar.</p>
      </header>

      <input type="hidden" id="addonsHidden" value="{{ $addonsParam }}">

      {{-- ✅ DOS PANELES (2 FONDOS SEPARADOS):
          IZQUIERDA: Datos personales + pago
          DERECHA:   Resumen completo + desglose de pagos
      --}}
      <div class="step4-layout">

        {{-- ===================== PANE IZQUIERDO ===================== --}}
        <div class="step4-pane">
          <div class="card-left">

            <div class="card-block card-personal">
              <form class="sum-form" id="formCotizacion" onsubmit="return false;">
                <script>
                  window.APP_URL_RESERVA_MOSTRADOR = "{{ route('reservas.store') }}";
                  window.APP_URL_RESERVA_LINEA     = "{{ route('reservas.linea') }}";
                </script>

                @csrf

                <input type="hidden" name="categoria_id" id="categoria_id" value="{{ $categoriaId }}">
                <input type="hidden" name="plan" id="plan" value="{{ $plan }}">
                <input type="hidden" name="addons" id="addons_payload" value="{{ $addonsParam }}">

                <div class="sum-section-title">Datos personales</div>

                <div class="sum-personal-grid">
                  <div class="field">
                    <label>Nombre(s)</label>
                    <input type="text" name="nombre" id="nombreCliente" placeholder="Nombre">
                  </div>

                  <div class="field">
                    <label>Apellidos</label>
                    <input type="text" name="apellido" id="apellidoCliente" placeholder="Apellidos">
                  </div>

                  <div class="field">
                    <label>Móvil</label>
                    <input type="text" name="telefono" id="telefonoCliente" placeholder="442 123 4567">
                  </div>

                  <div class="field">
                    <label>Correo electrónico</label>
                    <input type="email" name="email" id="correoCliente" placeholder="ejemplo@gmail.com">
                  </div>

                  <div class="field">
                    <label>País</label>
                    <select name="pais" id="pais">
                      <option value="">Selecciona un país</option>
                      <option value="México">México</option>
                      <option value="Estados Unidos">Estados Unidos</option>
                      <option value="Canadá">Canadá</option>
                    </select>
                  </div>

                  <div class="field">
                    <div class="ctl has-ico pristine" data-float>
                      <span class="ico"><i class="fa-solid fa-calendar-days"></i></span>
                      <span class="flabel">Fecha de nacimiento</span>
                      <input
                        type="text"
                        name="nacimiento"
                        id="dob"
                        placeholder="dd-mm-aaaa"
                        required
                        data-float-input
                      >
                    </div>
                  </div>

                  @php
                    $isAirport =
                      (is_string($pickupName) && str_contains(mb_strtolower($pickupName), 'aeropuerto')) ||
                      (is_string($dropoffName) && str_contains(mb_strtolower($dropoffName), 'aeropuerto'));
                  @endphp

                  @if($isAirport)
                    <div class="field field-span-2">
                      <label>No. de vuelo (opcional)</label>
                      <input type="text" name="vuelo" id="vuelo" placeholder="Ej. AM1234">
                    </div>
                  @endif
                </div>

                <div class="sum-checks">
                  <label class="cbox">
                    <input type="checkbox" name="acepto" id="acepto">
                    <span>ESTOY DE ACUERDO Y ACEPTO LOS TÉRMINOS Y CONDICIONES.</span>
                  </label>

                  <label class="cbox">
                    <input type="checkbox" name="promos" id="promos">
                    <span>DESEO RECIBIR ALERTAS, CONFIRMACIONES Y PROMOCIONES.</span>
                  </label>
                </div>

                <div class="wizard-nav" style="margin-top:10px;">
                  <a class="btn btn-ghost" href="{{ $toStep(3) }}">Anterior</a>
                  <button id="btnReservar" type="button" class="btn btn-primary">Reservar</button>
                </div>

                <button class="btn btn-quote" id="btnCotizar" type="button">
                  <i class="fa-regular fa-file-pdf"></i> Cotizar (PDF)
                </button>

                <div class="pay-logos" style="display:flex;gap:16px;margin-top:10px;align-items:center;flex-wrap:wrap;">
                  <img src="{{ asset('img/america.png') }}" alt="Amex" onerror="this.style.display='none'" style="height:24px;background:#fff;padding:6px 10px;border-radius:6px;box-shadow:0 4px 10px rgba(0,0,0,.15)">
                  <img src="{{ asset('img/paypal.png') }}" alt="PayPal" onerror="this.style.display='none'" style="height:24px;background:#fff;padding:6px 10px;border-radius:6px;box-shadow:0 4px 10px rgba(0,0,0,.15)">
                  <img src="{{ asset('img/oxxo.png') }}" alt="Oxxo" onerror="this.style.display='none'" style="height:24px;background:#fff;padding:6px 10px;border-radius:6px;box-shadow:0 4px 10px rgba(0,0,0,.15)">
                </div>

                <div id="modalMetodoPago" class="modal-overlay" style="display:none;">
                  <div class="modal-card">
                    <h3>Selecciona tu método de pago</h3>
                    <div class="options">
                      <button id="btnPagoLinea" class="btn btn-primary" type="button">Pago en línea</button>
                      <button id="btnPagoMostrador" class="btn btn-gray" type="button">Pago en mostrador</button>
                    </div>
                    <button id="cerrarModalMetodo" class="btn btn-secondary" type="button" style="margin-top:10px;">Cancelar</button>
                  </div>
                </div>

                <div id="paypal-button-container" style="display:none; text-align:center; margin-top:20px;"></div>
              </form>
            </div>

          </div>
        </div>

        {{-- ===================== PANE DERECHO ===================== --}}
        <div class="step4-pane">
          <div class="card-right">

            <div class="card-block card-summary">
              <div class="sum-car">
                <img src="{{ $categoriaImg }}" alt="Auto" onerror="this.onerror=null;this.src='{{ $placeholder }}';">
                <div class="sum-car-info">
                  <h3>{{ $categoriaSel ? strtoupper($categoriaSel->nombre) : 'Sin categoría seleccionada' }}</h3>

                  <div class="sum-compact" aria-label="Resumen compacto">
                    <div class="sum-compact-head">
                      <span class="sum-tag">COMPACTO</span>
                      <span class="sum-days">
                        <i class="fa-regular fa-calendar"></i>
                        Días: <strong id="qDays">{{ $days }}</strong>
                      </span>
                    </div>

                    <div class="sum-compact-grid">
                      <div class="sum-item">
                        <div class="sum-item-label">
                          <i class="fa-solid fa-location-dot"></i> Pick-Up
                        </div>
                        <div class="sum-item-value">
                          <strong class="sum-place">{{ $pickupName ?? '—' }}</strong>
                          <span class="sum-dt">
                            <span class="js-date" data-iso="{{ $pickupDateISO }}">{{ $pickupDate }}</span>
                            <span class="sum-time">{{ $pickupTime }}</span>
                          </span>
                        </div>
                      </div>

                      <div class="sum-item">
                        <div class="sum-item-label">
                          <i class="fa-solid fa-location-dot"></i> Devolución
                        </div>
                        <div class="sum-item-value">
                          <strong class="sum-place">{{ $dropoffName ?? '—' }}</strong>
                          <span class="sum-dt">
                            <span class="js-date" data-iso="{{ $dropoffDateISO }}">{{ $dropoffDate }}</span>
                            <span class="sum-time">{{ $dropoffTime }}</span>
                          </span>
                        </div>
                      </div>
                    </div>
                  </div>

                </div>
              </div>
            </div>

            <div class="card-block card-totals" id="cotizacionDoc">
              <div class="sum-table">
                <div class="sum-block">
                  <h4>Tarifa Base</h4>
                  <div class="row">
                    <span>{{ $days }} día(s) - precio por día ${{ number_format((float)($categoriaSel->precio_dia ?? 0), 0) }} MXN</span>
                    <strong id="qBase">${{ number_format($tarifaBase, 0) }} MXN</strong>
                  </div>

                  {{-- ✅ Incluido: ✔ Kilometraje ilimitado ✔ Reelevo de Responsabilidad (LI) --}}
                  <div class="row row-included">
                    <span class="inc-label">Incluido:</span>
                    <span class="inc-items">
                      <span class="inc-item"><span class="inc-check">✔</span> Kilometraje ilimitado</span>
                      <span class="inc-item"><span class="inc-check">✔</span> Reelevo de Responsabilidad (LI)</span>
                    </span>
                  </div>
                </div>

                <div class="sum-block">
                  <h4>Opciones de Renta</h4>
                  <div class="row"><span>Complementos</span><strong id="qExtras">$0 MXN</strong></div>
                </div>

                <div class="sum-block">
                  <h4>Cargos e IVA</h4>
                  <div class="row"><span>IVA (16%)</span><strong id="qIva">$0 MXN</strong></div>
                </div>

                <div class="sum-total">
                  <span>Total</span>
                  <strong id="qTotal">${{ number_format($tarifaBase, 0) }} MXN</strong>
                </div>
              </div>
            </div>

          </div>
        </div>

      </div>
    @endif

  </section>
</main>
@endsection

@section('js-vistaReservaciones')
  @php
    $paypalMode = env('PAYPAL_MODE', 'live');
    $paypalClientId = $paypalMode === 'live'
      ? env('PAYPAL_CLIENT_ID_LIVE')
      : env('PAYPAL_CLIENT_ID_SANDBOX', env('PAYPAL_CLIENT_ID_LIVE'));
  @endphp

  <script>
    window.PAYPAL_MODE = "{{ $paypalMode }}";
    window.PAYPAL_CLIENT_ID = "{{ $paypalClientId }}";
  </script>

  {{-- libs (defer) --}}
  <script defer src="https://cdn.jsdelivr.net/npm/flatpickr@4.6.13/dist/flatpickr.min.js"></script>
  <script defer src="https://cdn.jsdelivr.net/npm/flatpickr@4.6.13/dist/l10n/es.js"></script>
  <script defer src="https://cdn.jsdelivr.net/npm/flatpickr@4.6.13/dist/plugins/rangePlugin.js"></script>
  <script defer src="https://cdn.jsdelivr.net/npm/html2pdf.js@0.10.1/dist/html2pdf.bundle.min.js"></script>

  {{-- tus JS locales (defer) --}}
  <script defer src="{{ asset('js/reservaciones.js') }}"></script>
  <script defer src="{{ asset('js/BtnReserva.js') }}"></script>
  <script defer src="{{ asset('js/BtnReservaLinea.js') }}"></script>

  {{-- modal: si plan=mostrador abre selector; si plan=linea fuerza pago línea --}}
  <script>
    document.addEventListener('DOMContentLoaded', function () {
      const main        = document.querySelector('main.page');
      const currentPlan = (main && main.dataset.plan) ? main.dataset.plan : '';

      const btnReservar       = document.getElementById('btnReservar');
      const modalMetodoPago   = document.getElementById('modalMetodoPago');
      const cerrarModalMetodo = document.getElementById('cerrarModalMetodo');
      const btnPagoLinea      = document.getElementById('btnPagoLinea');

      if (!btnReservar) return;

      btnReservar.addEventListener('click', function (e) {
        e.preventDefault();

        if (currentPlan === 'linea') {
          if (btnPagoLinea) btnPagoLinea.click();
          else if (typeof window.handleReservaPagoEnLinea === 'function') window.handleReservaPagoEnLinea();
          return;
        }

        if (currentPlan === 'mostrador') {
          if (modalMetodoPago) modalMetodoPago.style.display = 'flex';
          return;
        }

        if (window.alertify) alertify.warning('Selecciona un tipo de pago desde el paso de categoría (Mostrador o Prepago).');
        else alert('Selecciona un tipo de pago desde el paso de categoría (Mostrador o Prepago).');
      });

      if (cerrarModalMetodo && modalMetodoPago) {
        cerrarModalMetodo.addEventListener('click', function () {
          modalMetodoPago.style.display = 'none';
        });
      }
    });
  </script>
@endsection
