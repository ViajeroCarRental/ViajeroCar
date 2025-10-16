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
  $categoriaId       = $f['categoria_id']        ?? request('categoria_id');
  $vehiculoId        = isset($vehiculo)? ($vehiculo->id_vehiculo ?? null) : (request('vehiculo_id') ?: null);

  $stepCurrent = isset($step) ? (int)$step : (int)request('step', 2);

  // Nombres de sucursales para el encabezado (con fallback a $ciudades)
  $pickupName = null; $dropoffName = null;
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

  // Base de parámetros que SIEMPRE propagamos en los enlaces
  $baseParams = array_filter([
    'pickup_sucursal_id'  => $pickupSucursalId,
    'dropoff_sucursal_id' => $dropoffSucursalId,
    'pickup_date'  => $pickupDate,
    'pickup_time'  => $pickupTime,
    'dropoff_date' => $dropoffDate,
    'dropoff_time' => $dropoffTime,
    'categoria_id' => $categoriaId,
    'vehiculo_id'  => $vehiculoId,
  ], fn($v)=>$v!==null && $v!=='');

  // Helper para construir URLs de pasos
  $toStep = function(int $to, array $extra = []) use ($baseParams) {
    return route('rutaReservasIniciar', array_merge($baseParams, ['step'=>$to], $extra));
  };

  // ====== Calcular días y tarifa base para el resumen ======
  $d1 = \Illuminate\Support\Carbon::parse($pickupDate);
  $d2 = \Illuminate\Support\Carbon::parse($dropoffDate);
  $days = max(1, $d1->diffInDays($d2)); // mínimo 1 día
  $tarifaBase = isset($vehiculo) ? (float)$vehiculo->precio_dia * $days : 0.0;

  // ====== Datos para encabezado de cotización (PDF) ======
  $folioCotizacion = $folio ?? ('COT-'.now()->format('Ymd').'-'.strtoupper(\Illuminate\Support\Str::random(5)));
  $fechaCotizacion = now()->isoFormat('DD MMM YYYY'); // ej. "09 oct 2025"

  // Imagen absoluta para html2canvas/html2pdf
  $vehiculoImg = isset($vehiculo)
      ? (($vehiculo->img_url ?? '') ?: asset('img/placeholder-car.jpg'))
      : asset('img/placeholder-car.jpg');
  // forzar absoluta:
  $vehiculoImgAbs = url($vehiculoImg);
@endphp

<main class="page" data-current-step="{{ $stepCurrent }}">
  <!-- PASOS -->
  <section class="steps-wrap">
    <div class="steps-card">
      <div class="steps-header">
        <div class="step-item {{ $stepCurrent>1?'done':'' }} {{ $stepCurrent===1?'active':'' }}" data-step="1">
          <span class="bubble">1</span>
          <span class="label">Elige lugar y fecha</span>
        </div>
        <div class="step-item {{ $stepCurrent>2?'done':'' }} {{ $stepCurrent===2?'active':'' }}" data-step="2">
          <span class="bubble">2</span>
          <span class="label">Selecciona tu auto</span>
        </div>
        <div class="step-item {{ $stepCurrent>3?'done':'' }} {{ $stepCurrent===3?'active':'' }}" data-step="3">
          <span class="bubble">3</span>
          <span class="label">Complementos</span>
        </div>
        <div class="step-item {{ $stepCurrent===4?'active':'' }}" data-step="4">
          <span class="bubble">4</span>
          <span class="label">Resumen de tu reserva</span>
        </div>
        <div class="progress-line"><span class="progress-fill" id="progressFill"></span></div>
      </div>

      <!-- Brief superior -->
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
          <!-- Ir a PASO 1 con los mismos parámetros para editar -->
          <a class="link" href="{{ $toStep(1) }}"><i class="fa-solid fa-pen-to-square"></i> Modificar</a>
        </div>
      </div>

      <!-- Panel de edición (PASO 1): siempre navega por GET a step=2 -->
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
            <!-- Cancelar = volver al paso 2 (si ya había filtros), sin tocar nada -->
            <a class="btn btn-secondary" href="{{ $toStep(2) }}">Cancelar</a>
            <button class="btn btn-primary" type="submit">Aplicar cambios</button>
          </div>
        </div>
      </form>
    </div>

    <div class="section-divider"><span class="tag">
      @if($stepCurrent===1) Define tu búsqueda
      @elseif($stepCurrent===2) Autos disponibles
      @elseif($stepCurrent===3) Complementos
      @else Resumen
      @endif
    </span></div>
  </section>

  {{-- PASO 2: Resultados --}}
  @if($stepCurrent >= 2)
    <section class="results" id="step2" style="{{ $stepCurrent===2 ? '' : 'display:none' }}">
      <div class="step-back">
        <!-- Ir a PASO 1 preservando filtros -->
        <a class="btn btn-ghost" href="{{ $toStep(1) }}">← Regresar</a>
      </div>

      @forelse(($vehiculos ?? []) as $car)
        <article class="r-card"
          data-name="{{ $car->nombre_publico }}"
          data-cat="{{ $car->categoria_nombre }}"
          data-type="{{ \Illuminate\Support\Str::slug($car->categoria_nombre) }}"
          data-pay-counter="{{ (int)$car->precio_dia }}"
          data-pay-pre="{{ (int)round($car->precio_dia*0.55) }}"
        >
          <div class="stock-pill">Disponible</div>
          <div class="r-media">
            <img src="{{ $car->img_url ?: asset('img/placeholder-car.jpg') }}" alt="{{ $car->nombre_publico }}">
          </div>
          <div class="r-body">
            <h3>{{ $car->marca }} <strong>{{ $car->modelo }}</strong> o similar</h3>
            <div class="subtitle">{{ strtoupper($car->categoria_nombre) }} | {{ $car->sucursal_nombre }}</div>
          </div>
          <div class="r-price">
            <div class="p-col">
              <div class="p-old">${{ number_format($car->precio_dia*1.3, 0) }} <small>MXN</small></div>
              <div class="p-new" data-plan-label="En mostrador">${{ number_format($car->precio_dia, 0) }} <small>MXN</small></div>
              <div class="p-save">Ahorra ${{ number_format($car->precio_dia*0.3, 0) }} MXN</div>
              <!-- Elegir auto → PASO 3 con vehiculo_id -->
              <a class="btn btn-gray"
                 href="{{ $toStep(3, ['vehiculo_id' => $car->id_vehiculo]) }}">
                En mostrador
              </a>
            </div>
            <div class="p-col">
              <div class="p-old">${{ number_format($car->precio_dia*1.3, 0) }} <small>MXN</small></div>
              <div class="p-new p-accent" data-plan-label="Prepago">${{ number_format($car->precio_dia*0.55, 0) }} <small>MXN</small></div>
              <div class="p-save">Ahorra ${{ number_format($car->precio_dia*0.75, 0) }} MXN</div>
              <a class="btn btn-primary"
                 href="{{ $toStep(3, ['vehiculo_id' => $car->id_vehiculo]) }}">
                Prepago
              </a>
            </div>
          </div>
        </article>
      @empty
        <p style="padding:1rem 0">No hay autos disponibles con los filtros seleccionados.</p>
      @endforelse
    </section>
  @endif

  {{-- ===== PASO 3: Complementos (dinámicos desde BD) ===== --}}
  <section id="step3" class="addons {{ $stepCurrent===3 ? '' : 'hidden' }}">
    <div class="step-back">
      <!-- Volver a PASO 2 (descartando vehiculo_id pero conservando filtros) -->
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
          <div class="addon-icon">
            <i class="fa-solid fa-plus"></i>
          </div>

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

  {{-- ===== PASO 4: Dos columnas (Formulario + Resumen) ===== --}}
  <section id="step4" class="summary {{ $stepCurrent===4 ? '' : 'hidden' }}">
    <div class="step-back">
      <!-- Volver a PASO 3 conservando vehiculo_id si existe -->
      <a class="btn btn-ghost"
         href="{{ $vehiculoId ? $toStep(3, ['vehiculo_id'=>$vehiculoId]) : $toStep(3) }}">← Regresar</a>
    </div>

    <div class="summary-grid">
      {{-- IZQUIERDA: FORMULARIO --}}
      <div class="form-card">
        <h3>Tu información</h3>

        - <form class="user-form" id="formCotizacion" method="POST" action="{{ route('cotizaciones.store') }}">
        + <form class="user-form" id="formCotizacion" onsubmit="return false;">
            <script>
  // 📦 Rutas dinámicas para JS (Laravel → JavaScript)
  window.APP_URL_RESERVA_MOSTRADOR = "{{ route('reservas.store') }}";
  window.APP_URL_RESERVA_LINEA = "{{ route('reservas.linea') }}";
</script>

  @csrf

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
    <!-- ===== MODAL PRINCIPAL: Selección de método de pago ===== -->
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

<!-- ===== MODAL SECUNDARIO: Pasarela de pago ===== -->
<div id="modalPasarelaPago" class="modal-overlay" style="display:none;">
  <div class="modal-card">
    <h3>Pasarela de Pago</h3>
    <p>Introduce los datos de tu tarjeta o selecciona un método de pago seguro.</p>

    <!-- 🧾 FORMULARIO DE PASARELA DE PAGO -->
    <form id="formPasarela" class="payment-form">
      <div class="form-row">
        <label>Nombre del titular</label>
        <input type="text" name="titular" placeholder="Ej. Mario Bernal" required>
      </div>

      <div class="form-row">
        <label>Número de tarjeta</label>
        <input type="text" name="numero" placeholder="XXXX XXXX XXXX XXXX" maxlength="19" required>
      </div>

      <div class="form-row grid-2">
        <div class="field">
          <label>Fecha de expiración</label>
          <input type="text" name="expiracion" placeholder="MM/AA" maxlength="5" required>
        </div>
        <div class="field">
          <label>CVV</label>
          <input type="text" name="cvv" placeholder="XXX" maxlength="4" required>
        </div>
      </div>

      <div class="form-row">
        <label>Método de pago</label>
        <select name="metodo" required>
          <option value="">Selecciona una opción</option>
          <option value="visa">Visa</option>
          <option value="mastercard">MasterCard</option>
          <option value="amex">American Express</option>
          <option value="paypal">PayPal</option>
        </select>
      </div>

      <div class="form-row grid-2" style="margin-top:1rem;">
        <button id="btnRealizarPago" type="submit" class="btn btn-primary">Realizar pago</button>
        <button id="btnCancelarPago" type="button" class="btn btn-secondary">Cancelar</button>
      </div>
    </form>
  </div>
</div>


    <button class="btn btn-quote" id="btnCotizar" type="button">
      <i class="fa-regular fa-file-pdf"></i> Cotizar (PDF)
    </button>
  </div>

  <div class="pay-logos">
    <img src="{{ asset('img/pay/amex.png') }}" alt="Amex" onerror="this.style.display='none'">
    <img src="{{ asset('img/pay/paypal.png') }}" alt="PayPal" onerror="this.style.display='none'">
    <img src="{{ asset('img/pay/oxxo.png') }}" alt="Oxxo" onerror="this.style.display='none'">
  </div>
</form>

      </div>

      {{-- DERECHA: RESUMEN (exportable) --}}
      <aside class="resume-card" id="cotizacionDoc">
        {{-- ===== Encabezado de la Cotización (para PDF) ===== --}}
        <div class="qd-head">
          <div class="qd-brand">
            <div class="qd-logo">VIAJERO</div>
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
            <div>Tu Auto</div>
            @if($vehiculoId)<a href="{{ $toStep(2) }}" class="link small">Modificar</a>@endif
          </div>
          <div class="block-body">
            @if(isset($vehiculo))
              <div class="car-sum">
                {{-- Imagen absoluta para PDF --}}
                <img src="{{ $vehiculoImgAbs }}" alt="Auto" crossorigin="anonymous">
                <div>
                  <div class="car-name">{{ $vehiculo->marca }} <strong>{{ $vehiculo->modelo }}</strong> o similar</div>
                  <div class="car-sub">Categoría {{ $vehiculo->categoria_nombre ?? '—' }}</div>
                </div>
              </div>
            @else
              <p class="muted">No hay vehículo seleccionado.</p>
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

        {{-- ===== Notas obligatorias ===== --}}
        <div class="resume-block">
          <div class="block-body">
            <div class="qd-notes qd-muted">
              <div class="n-title">Notas importantes</div>
              <ul class="qd-list">
                <li>Los <strong>seguros obligatorios</strong> no están incluidos en este monto. Se cotizan y confirman con un <strong>agente de Viajero Car Rental</strong>.</li>
                <li>Tarifas, disponibilidad y tipo de vehículo sujetos a cambio sin previo aviso.</li>
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
  <script src="{{ asset('js/reservaciones.js') }}"></script>
    <script src="{{ asset('js/BtnReserva.js') }}"></script>
  <script defer src="https://cdn.jsdelivr.net/npm/flatpickr@4.6.13/dist/flatpickr.min.js"></script>
  <script defer src="https://cdn.jsdelivr.net/npm/flatpickr@4.6.13/dist/l10n/es.js"></script>
  <script defer src="https://cdn.jsdelivr.net/npm/flatpickr@4.6.13/dist/plugins/rangePlugin.js"></script>
  <script defer src="https://cdn.jsdelivr.net/npm/html2pdf.js@0.10.1/dist/html2pdf.bundle.min.js"></script>


@endsection
