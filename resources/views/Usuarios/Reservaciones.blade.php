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

  $pickupDate        = $f['pickup_date']         ?? request('pickup_date')  ?? now()->toDateString();
  $pickupTime        = $f['pickup_time']         ?? request('pickup_time')  ?? '12:00';
  $dropoffDate       = $f['dropoff_date']        ?? request('dropoff_date') ?? now()->addDays(3)->toDateString();
  $dropoffTime       = $f['dropoff_time']        ?? request('dropoff_time') ?? '12:00';

  // ============================
  // ✅ FIX: Si pickup_date viene como rango "YYYY-MM-DD a YYYY-MM-DD"
  // (por flatpickr rangePlugin), separarlo para que Carbon NO truene.
  // ============================
  if (is_string($pickupDate) && str_contains($pickupDate, ' a ')) {
    [$start, $end] = array_map('trim', explode(' a ', $pickupDate, 2));

    // pickup_date = inicio
    $pickupDate = $start ?: now()->toDateString();

    // si dropoff_date venía vacío o igual al default, lo reemplazamos por el fin del rango
    if (empty($dropoffDate) || $dropoffDate === now()->addDays(3)->toDateString()) {
      $dropoffDate = $end ?: now()->addDays(3)->toDateString();
    }
  }

  // ✅ flujo por categoría
  $categoriaId       = $f['categoria_id']        ?? request('categoria_id');
  $plan              = $f['plan']                ?? request('plan'); // mostrador / linea

  $stepCurrent = isset($step) ? (int)$step : (int)request('step', 2);

  // Nombres de sucursales para el encabezado (con fallback a $ciudades)
  $pickupName = null;
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

  // Base params
  $baseParams = array_filter([
    'pickup_sucursal_id'  => $pickupSucursalId,
    'dropoff_sucursal_id' => $dropoffSucursalId,
    'pickup_date'  => $pickupDate,
    'pickup_time'  => $pickupTime,
    'dropoff_date' => $dropoffDate,
    'dropoff_time' => $dropoffTime,
    'categoria_id' => $categoriaId,
    'plan'         => $plan,
  ], fn($v)=>$v!==null && $v!=='');

  $toStep = function(int $to, array $extra = []) use ($baseParams) {
    return route('rutaReservasIniciar', array_merge($baseParams, ['step'=>$to], $extra));
  };

  // días
  $d1 = \Illuminate\Support\Carbon::parse(trim((string)$pickupDate));
  $d2 = \Illuminate\Support\Carbon::parse(trim((string)$dropoffDate));
  $days = max(1, $d1->diffInDays($d2));

  // ✅ categoría seleccionada
  $categoriaSel = null;
  if (!empty($categorias) && $categoriaId) {
    $categoriaSel = collect($categorias)->firstWhere('id_categoria', (int)$categoriaId);
  }

  /**
   * ✅ IMÁGENES DIRECTO EN public/img/
   */
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

  $folioCotizacion = $folio ?? ('COT-'.now()->format('Ymd').'-'.strtoupper(\Illuminate\Support\Str::random(5)));
  $fechaCotizacion = now()->isoFormat('DD MMM YYYY');
  $logoCotizacion  = url(asset('img/Logotipo.png'));
@endphp

<main class="page" data-current-step="{{ $stepCurrent }}" data-plan="{{ $plan ?? '' }}">
  <!-- PASOS -->
  <section class="steps-wrap">
    <div class="steps-card">
      <div class="steps-header">
        <div class="step-item {{ $stepCurrent>1?'done':'' }} {{ $stepCurrent===1?'active':'' }}" data-step="1">
          <span class="bubble">1</span><span class="label">Elige lugar y fecha</span>
        </div>
        <div class="step-item {{ $stepCurrent>2?'done':'' }} {{ $stepCurrent===2?'active':'' }}" data-step="2">
          <span class="bubble">2</span><span class="label">Selecciona tu categoría</span>
        </div>
        <div class="step-item {{ $stepCurrent>3?'done':'' }} {{ $stepCurrent===3?'active':'' }}" data-step="3">
          <span class="bubble">3</span><span class="label">Complementos</span>
        </div>
        <div class="step-item {{ $stepCurrent===4?'active':'' }}" data-step="4">
          <span class="bubble">4</span><span class="label">Resumen de tu reserva</span>
        </div>
        <div class="progress-line"><span class="progress-fill" id="progressFill"></span></div>
      </div>

      <!-- Brief -->
      <div class="booking-brief">
        <div class="brief-left">
          <div class="brief-title">
            <i class="fa-solid fa-location-dot"></i>
            <span id="briefLoc">{{ $pickupName ?? 'Selecciona sucursal' }}</span>
          </div>
          <div class="brief-dates">
            <div><strong>Entrega:</strong> <span id="briefStart">{{ $pickupDate }} {{ $pickupTime }}</span></div>
            <div><strong>Devolución:</strong> <span id="briefEnd">{{ $dropoffDate }} {{ $dropoffTime }}</span></div>
          </div>
        </div>
        <div class="brief-right">
          <a class="link" href="{{ $toStep(1) }}"><i class="fa-solid fa-pen-to-square"></i> Modificar</a>
        </div>
      </div>

      <!-- Panel edición (Paso 1) -->
      <form class="edit-panel" id="editPanel" method="GET" action="{{ route('rutaReservasIniciar') }}" style="{{ $stepCurrent===1 ? 'display:block' : '' }}">
        <input type="hidden" name="step" value="2" />
        <div class="grid">
          <div class="field">
            <label>Lugar de renta (entrega)</label>
            <select id="loc" name="pickup_sucursal_id" required>
              <option value="" disabled {{ $pickupSucursalId ? '' : 'selected' }}>-- Selecciona sucursal --</option>
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

          <div class="field">
            <label>Lugar de devolución</label>
            <select name="dropoff_sucursal_id" required>
              <option value="" disabled {{ $dropoffSucursalId ? '' : 'selected' }}>-- Selecciona sucursal --</option>
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

          <div class="field">
            <label>Entrega — Día</label>
            <input id="start" name="pickup_date" type="text" placeholder="YYYY-MM-DD" value="{{ $pickupDate }}" required>
          </div>
          <div class="field">
            <label>Entrega — Hora</label>
            <input name="pickup_time" type="text" placeholder="HH:MM" value="{{ $pickupTime }}" required>
          </div>

          <div class="field">
            <label>Devolución — Día</label>
            <input id="end" name="dropoff_date" type="text" placeholder="YYYY-MM-DD" value="{{ $dropoffDate }}" required>
          </div>
          <div class="field">
            <label>Devolución — Hora</label>
            <input name="dropoff_time" type="text" placeholder="HH:MM" value="{{ $dropoffTime }}" required>
          </div>

          <div class="field">
            <label>Tipo de auto (opcional)</label>
            <select name="categoria_id">
              <option value="">-- Cualquiera --</option>
              @foreach(($categorias ?? []) as $cat)
                <option value="{{ $cat->id_categoria }}" {{ (string)$categoriaId===(string)$cat->id_categoria ? 'selected' : '' }}>
                  {{ $cat->nombre }}
                </option>
              @endforeach
            </select>
          </div>

          <div class="field actions">
            <a class="btn btn-secondary" href="{{ $toStep(2) }}">Cancelar</a>
            <button class="btn btn-primary" type="submit">Aplicar cambios</button>
          </div>
        </div>
      </form>
    </div>

    <div class="section-divider"><span class="tag">
      @if($stepCurrent===1) Define tu búsqueda
      @elseif($stepCurrent===2) Categorías disponibles
      @elseif($stepCurrent===3) Complementos
      @else Resumen
      @endif
    </span></div>
  </section>

  {{-- ===================== PASO 2: CATEGORÍAS ===================== --}}
  @if($stepCurrent >= 2)
    <section class="results" id="step2" style="{{ $stepCurrent===2 ? '' : 'display:none' }}">
      <div class="step-back">
        <a class="btn btn-ghost" href="{{ $toStep(1) }}">← Regresar</a>
      </div>

      @forelse(($categorias ?? []) as $cat)
        @php
          // ✅ IMAGEN
          $imgCat = $catImages[$cat->id_categoria] ?? $placeholder;

          // ✅ PRECIOS
          // Prepago = BD
          $prepago = (float)($cat->precio_dia ?? 0);

          // Mostrador = BD + 15%
          $mostrador = round($prepago * 1.15);

          // Precio tachado (mismo para ambos)
          $old = round($mostrador * 1.30);

          // Ahorros
          $saveMostrador = max(0, $old - $mostrador);
          $savePrepago   = max(0, $old - $prepago);

          // ✅ chips
          $pasajeros      = (int)($cat->pasajeros ?? 5);
          $malChicas      = (int)($cat->maletas_chicas ?? 2);
          $malGrandes     = (int)($cat->maletas_grandes ?? 1);
          $appleCarplay   = (int)($cat->apple_carplay ?? 1);
          $androidAuto    = (int)($cat->android_auto ?? 1);
          $transmision    = strtoupper($cat->transmision ?? 'AUTOMÁTICA');
        @endphp

        <article class="r-card" data-cat="{{ $cat->nombre }}" data-type="{{ \Illuminate\Support\Str::slug($cat->nombre) }}">
          <div class="stock-pill">Disponible</div>

          <div class="r-media">
            <img
              src="{{ $imgCat }}"
              alt="Categoría {{ $cat->nombre }}"
              onerror="this.onerror=null;this.src='{{ $placeholder }}';"
            >
          </div>

          <div class="r-body">
            <div class="r-topline">{{ $transmision }}</div>

            <h3 class="r-title"><strong>{{ strtoupper($cat->nombre) }}</strong></h3>

            <div class="r-specs">
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

            <div class="r-note">
              {{ $cat->ejemplo ?? ($cat->descripcion ?? 'Auto o similar. Tarifas sujetas a disponibilidad y temporada.') }}
            </div>
          </div>

          <div class="r-price">
            {{-- MOSTRADOR (BD + 15%) --}}
            <div class="p-col">
              <div class="p-old">${{ number_format($old, 0) }} <small>MXN</small></div>
              <div class="p-new">${{ number_format($mostrador, 0) }} <small>MXN</small></div>
              <div class="p-save">Ahorra ${{ number_format($saveMostrador, 0) }} MXN</div>

              <a class="btn btn-gray"
                 href="{{ $toStep(3, ['categoria_id' => $cat->id_categoria, 'plan' => 'mostrador']) }}">
                En mostrador
              </a>
            </div>

            {{-- PREPAGO (BD) --}}
            <div class="p-col">
              <div class="p-old">${{ number_format($old, 0) }} <small>MXN</small></div>
              <div class="p-new p-accent">${{ number_format($prepago, 0) }} <small>MXN</small></div>
              <div class="p-save">Ahorra ${{ number_format($savePrepago, 0) }} MXN</div>

              <a class="btn btn-primary"
                 href="{{ $toStep(3, ['categoria_id' => $cat->id_categoria, 'plan' => 'linea']) }}">
                Prepago
              </a>
            </div>
          </div>
        </article>
      @empty
        <p style="padding:1rem 0">No hay categorías disponibles.</p>
      @endforelse
    </section>
  @endif

  {{-- ===================== PASO 3: Complementos ===================== --}}
  <section id="step3" class="addons {{ $stepCurrent===3 ? '' : 'hidden' }}">
    <div class="step-back">
      <a class="btn btn-ghost" href="{{ $toStep(2) }}">← Regresar</a>
    </div>

    <h3 class="addons-title">Elige tus complementos</h3>

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
          <div class="addon-icon"><i class="fa-solid fa-plus"></i></div>

          <h4 class="addon-head">{{ $srv->nombre }}</h4>

          @if(!empty($srv->descripcion))
            <p class="addon-desc">{{ $srv->descripcion }}</p>
          @endif

          <div class="addon-price">
            <strong>${{ $precio }}</strong>
            <span>MXN {{ $unidad }}</span>
          </div>

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

    <div class="addons-actions">
      <a class="btn btn-ghost" id="skipAddons" href="{{ $toStep(4) }}" title="Omitir complementos">Omitir</a>
      <a class="btn btn-primary" id="toStep4" href="{{ $toStep(4) }}">Continuar</a>
    </div>
  </section>

  {{-- ===================== PASO 4: Resumen ===================== --}}
  <section id="step4" class="summary {{ $stepCurrent===4 ? '' : 'hidden' }}">
    <div class="step-back">
      <a class="btn btn-ghost" href="{{ $toStep(3) }}">← Regresar</a>
    </div>

    <div class="summary-grid">
      <div class="form-card">
        <h3>Tu información</h3>

        <form class="user-form" id="formCotizacion" onsubmit="return false;">
          <script>
            window.APP_URL_RESERVA_MOSTRADOR = "{{ route('reservas.store') }}";
            window.APP_URL_RESERVA_LINEA     = "{{ route('reservas.linea') }}";
          </script>

          @csrf

          <input type="hidden" name="categoria_id" id="categoria_id" value="{{ $categoriaId }}">
          <input type="hidden" name="plan" id="plan" value="{{ $plan }}">

          <div class="form-row grid-2">
            <div class="field">
              <label>Entrega — Día</label>
              <input type="text" name="pickup_date" id="pickup_date" value="{{ $pickupDate }}" readonly>
            </div>
            <div class="field">
              <label>Entrega — Hora</label>
              <input type="text" name="pickup_time" id="pickup_time" value="{{ $pickupTime }}" readonly>
            </div>
          </div>

          <div class="form-row grid-2">
            <div class="field">
              <label>Devolución — Día</label>
              <input type="text" name="dropoff_date" id="dropoff_date" value="{{ $dropoffDate }}" readonly>
            </div>
            <div class="field">
              <label>Devolución — Hora</label>
              <input type="text" name="dropoff_time" id="dropoff_time" value="{{ $dropoffTime }}" readonly>
            </div>
          </div>

          <div class="form-row">
            <div class="field">
              <label>No. de vuelo (opcional)</label>
              <input type="text" name="vuelo" id="vuelo" placeholder="Ej. AM1234">
            </div>
          </div>

          <div class="form-row">
            <div class="field">
              <label>Nombre completo</label>
              <input type="text" name="nombre" id="nombreCliente" placeholder="Tu nombre y apellidos">
            </div>
          </div>

          <div class="form-row grid-2">
            <div class="field">
              <label>Móvil</label>
              <input type="text" name="telefono" id="telefonoCliente" placeholder="55 1234 5678">
            </div>
            <div class="field">
              <label>Correo electrónico</label>
              <input type="email" name="email" id="correoCliente" placeholder="tucorreo@dominio.com">
            </div>
          </div>

          <div class="form-row grid-2">
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
              <label>Fecha de nacimiento</label>
              <input type="text" name="nacimiento" id="dob" placeholder="dd/mm/aaaa">
            </div>
          </div>

          <label class="cbox">
            <input type="checkbox" name="acepto" id="acepto">
            <span class="checkmark"></span>
            <span>ESTOY DE ACUERDO Y ACEPTO LAS POLÍTICAS Y PROCEDIMIENTOS PARA LA RENTA.</span>
          </label>

          <label class="cbox">
            <input type="checkbox" name="promos" id="promos">
            <span class="checkmark"></span>
            <span>DESEO RECIBIR ALERTAS, CONFIRMACIONES Y PROMOCIONES.</span>
          </label>

          <div class="form-row">
            <button id="btnReservar" type="button" class="btn btn-primary">Reservar</button>

            <div id="modalMetodoPago" class="modal-overlay" style="display:none;">
              <div class="modal-card">
                <h3>Selecciona tu método de pago</h3>
                <div class="options">
                  <button id="btnPagoLinea" class="btn btn-primary">Pago en línea</button>
                  <button id="btnPagoMostrador" class="btn btn-gray">Pago en mostrador</button>
                </div>
                <button id="cerrarModalMetodo" class="btn btn-secondary" style="margin-top:10px;">Cancelar</button>
              </div>
            </div>

            <div id="paypal-button-container" style="display:none; text-align:center; margin-top:20px;"></div>
          </div>

          <button class="btn btn-quote" id="btnCotizar" type="button">
            <i class="fa-regular fa-file-pdf"></i> Cotizar (PDF)
          </button>

          {{-- ✅ SIN /pay/ porque NO existe carpeta --}}
          <div class="pay-logos">
            <img src="{{ asset('img/amex.png') }}" alt="Amex" onerror="this.style.display='none'">
            <img src="{{ asset('img/paypal.png') }}" alt="PayPal" onerror="this.style.display='none'">
            <img src="{{ asset('img/oxxo.png') }}" alt="Oxxo" onerror="this.style.display='none'">
          </div>
        </form>
      </div>

      <aside class="resume-card" id="cotizacionDoc">
        <div class="qd-head">
          <div class="qd-brand">
            <div class="qd-logo">
              <img src="{{ $logoCotizacion }}" alt="Logo cotización" crossorigin="anonymous" style="max-height:48px;width:auto;display:block;">
            </div>
            <div class="qd-sub">Renta de Autos</div>
          </div>
          <div class="qd-meta">
            <div>
              <div class="l">No. de cotización</div>
              <div class="v">{{ $folioCotizacion }}</div>
            </div>
            <div>
              <div class="l">Fecha</div>
              <div class="v">{{ $fechaCotizacion }}</div>
            </div>
          </div>
        </div>

        <div class="resume-block">
          <div class="block-head">
            <div>Resumen de tu reserva</div>
            <a href="{{ $toStep(1) }}" class="link small">Modificar</a>
          </div>
          <div class="block-body">
            <div class="item"><strong>Lugar y fecha</strong></div>
            <ul class="kv">
              <li>
                <span>Entrega:</span>
                <strong>{{ \Carbon\Carbon::parse($pickupDate.' '.$pickupTime)->isoFormat('ddd DD [de] MMM, h:mm a') }}</strong>
                — {{ $pickupName }}
              </li>
              <li>
                <span>Devolución:</span>
                <strong>{{ \Carbon\Carbon::parse($dropoffDate.' '.$dropoffTime)->isoFormat('ddd DD [de] MMM, h:mm a') }}</strong>
                — {{ $dropoffName }}
              </li>
              <li><span>Días</span> <strong id="qDays">{{ $days }}</strong></li>
            </ul>
          </div>
        </div>

        <div class="resume-block">
          <div class="block-head">
            <div>Tu Categoría</div>
            @if($categoriaId)<a href="{{ $toStep(2) }}" class="link small">Modificar</a>@endif
          </div>
          <div class="block-body">
            @if($categoriaSel)
              <div class="car-sum">
                <img
                  src="{{ $categoriaImg }}"
                  alt="Categoría"
                  crossorigin="anonymous"
                  onerror="this.onerror=null;this.src='{{ $placeholder }}';"
                >
                <div>
                  <div class="car-name"><strong>{{ strtoupper($categoriaSel->nombre) }}</strong></div>
                  <div class="car-sub">
                    Tarifa por día: ${{ number_format((float)($categoriaSel->precio_dia ?? 0), 0) }} MXN
                    @if(!empty($plan))
                      <span style="display:block; margin-top:4px;">
                        Plan: <strong>{{ $plan === 'linea' ? 'Prepago' : 'Mostrador' }}</strong>
                      </span>
                    @endif
                  </div>
                </div>
              </div>
            @else
              <p class="muted">No hay categoría seleccionada.</p>
            @endif
          </div>
        </div>

        <div class="resume-block">
          <div class="block-head"><div>Extras</div></div>
          <div class="block-body">
            <ul id="extrasList" class="kv"></ul>
            <div id="extrasEmpty" class="muted">Sin complementos.</div>
          </div>
        </div>

        <div class="resume-block">
          <div class="block-head"><div>Detalles del precio</div></div>
          <div class="block-body">
            <div class="price-row"><span>Tarifa base</span><strong id="qBase">${{ number_format($tarifaBase, 0) }} MXN</strong></div>
            <div class="price-row"><span>Opciones de renta</span><strong id="qExtras">$0 MXN</strong></div>
            <div class="price-row"><span>Cargos e IVA</span><strong id="qIva">$0 MXN</strong></div>
            <div class="price-row total"><span>TOTAL</span><strong id="qTotal">${{ number_format($tarifaBase, 0) }} MXN</strong></div>
          </div>
        </div>

        <div class="resume-block">
          <div class="block-body">
            <div class="qd-notes qd-muted">
              <div class="n-title">Notas importantes</div>
              <ul class="qd-list">
                <li>Los <strong>seguros obligatorios</strong> no están incluidos en este monto. Se cotizan y confirman con un <strong>agente de Viajero Car Rental</strong>.</li>
                <li>Tarifas y disponibilidad sujetas a cambio sin previo aviso.</li>
                <li>Se requiere tarjeta de crédito física del titular al recoger el vehículo.</li>
              </ul>
            </div>
          </div>
        </div>

      </aside>
    </div>
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

  <!-- Luego tus JS locales -->
  <script src="{{ asset('js/reservaciones.js') }}"></script>
  <script src="{{ asset('js/BtnReserva.js') }}"></script>
  <script src="{{ asset('js/BtnReservaLinea.js') }}"></script>

  <script defer src="https://cdn.jsdelivr.net/npm/flatpickr@4.6.13/dist/flatpickr.min.js"></script>
  <script defer src="https://cdn.jsdelivr.net/npm/flatpickr@4.6.13/dist/l10n/es.js"></script>
  <script defer src="https://cdn.jsdelivr.net/npm/flatpickr@4.6.13/dist/plugins/rangePlugin.js"></script>
  <script defer src="https://cdn.jsdelivr.net/npm/html2pdf.js@0.10.1/dist/html2pdf.bundle.min.js"></script>

  <script>
    document.addEventListener('DOMContentLoaded', function () {
      const main        = document.querySelector('main.page');
      const currentPlan = (main && main.dataset.plan) ? main.dataset.plan : '';

      const btnReservar       = document.getElementById('btnReservar');
      const modalMetodoPago   = document.getElementById('modalMetodoPago');
      const cerrarModalMetodo = document.getElementById('cerrarModalMetodo');
      const btnPagoLinea      = document.getElementById('btnPagoLinea');
      const btnPagoMostrador  = document.getElementById('btnPagoMostrador');

      if (!btnReservar) return;

      btnReservar.addEventListener('click', function (e) {
        e.preventDefault();

        if (currentPlan === 'linea') {
          if (btnPagoLinea) btnPagoLinea.click();
          else if (typeof window.handleReservaPagoEnLinea === 'function') window.handleReservaPagoEnLinea();
          else console.warn('No se encontró handler para pago en línea');
          return;
        }

        if (currentPlan === 'mostrador') {
          if (modalMetodoPago) modalMetodoPago.style.display = 'flex';
          return;
        }

        alertify.warning('Selecciona un tipo de pago desde el paso de categoría (Mostrador o Prepago).');
      });

      if (cerrarModalMetodo && modalMetodoPago) {
        cerrarModalMetodo.addEventListener('click', function () {
          modalMetodoPago.style.display = 'none';
        });
      }
    });
  </script>
@endsection
