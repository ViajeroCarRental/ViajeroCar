@extends('layouts.Ventas')
@section('Titulo', 'reservacionesAdmin')

@section('css-vistaHomeVentas')
    <link rel="stylesheet" href="{{ asset('css/reservacionesAdmin.css') }}">
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
@endsection

@section('contenidoreservacionesAdmin')

@php
  $edit = isset($reservacion) && !empty($reservacion->id_reservacion);
@endphp

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
  action="{{ $edit
      ? route('reservaciones.update', $reservacion->id_reservacion)
      : route('reservaciones.guardar') }}"
  method="POST"
   novalidate
>
  @csrf

  @if($edit)
    @method('PUT')
  @endif

      {{-- Hidden “state” --}}
      <input type="hidden" id="categoria_id" name="categoria_id"
      value="{{ $reservacion->id_categoria ?? '' }}">
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
      <input type="hidden" id="telefono_cliente" name="telefono_cliente" value="{{ $reservacion->telefono_cliente ?? '' }}">
      <input type="hidden" id="telefono_lada" name="telefono_lada" value="+52">
<section class="stack-card">
  <div class="stack-head">
    <div class="stack-title">📍 Ubicación</div>
    <div class="stack-sub">Selecciona dónde se recoge y se entrega el vehículo.</div>
  </div>

  <div class="stack-body">
    <div class="form-2">
      <div class="dt-field">
        <label>SUCURSAL DE RETIRO</label>
        <select id="sucursal_retiro" name="sucursal_retiro" class="input" required>
          <option value="">Selecciona punto de entrega</option>
          @foreach($sucursales as $ciudad => $grupo)
            @if($ciudad === 'Querétaro')
              <optgroup label="{{ $ciudad }} — {{ $ciudad }}">
                @foreach($grupo as $s)
                  <option value="{{ $s->id_sucursal }}"
                          data-ciudad-id="{{ $s->id_ciudad }}"
                          data-nombre="{{ $s->sucursal }}">
                    {{ $s->sucursal }}
                  </option>
                @endforeach
              </optgroup>
            @endif
          @endforeach
        </select>

        <div id="campo_vuelo" style="display:none; margin-top:10px;">
          <label>Número de vuelo</label>
          <input type="text" name="numero_vuelo" id="numero_vuelo" class="input" placeholder="Ej. AA1234">
        </div>
      </div>

      <div class="dt-field">
        <label>SUCURSAL DE ENTREGA</label>
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

/* =========================================
   7️⃣ ICONOS UNIFICADOS (FECHA Y HORA)
========================================= */
.icon-field, .dt-field {
  position: relative !important;
  display: flex !important;
  flex-direction: column !important;
}

.icon-field .field-icon,
.dt-field .dt-ico {
  position: absolute !important;
  right: 14px !important;
  bottom: 24px !important;
  transform: translateY(50%) !important;
  display: flex !important;
  align-items: center !important;
  justify-content: center !important;
  color: #333 !important;
  font-size: 18px !important;
  z-index: 10 !important;
  pointer-events: none !important;
  width: 24px !important;
  height: 24px !important;
  top: auto !important;
}

.icon-field input,
.dt-field input,
input.flatpickr-input {
  height: 48px !important;
  padding-right: 44px !important;
  padding-left: 14px !important;
  border: 1px solid #ccc !important;
  border-radius: 8px !important;
  width: 100% !important;
  background: white !important;
  font-size: 15px !important;
  box-sizing: border-box !important;
}

.icon-field label, .dt-field label {
  margin-bottom: 5px !important;
  font-weight: bold !important;
  font-size: 13px !important;
  color: #444 !important;
  display: block !important;
}

.time-field::before,
.date-field::before,
.time-field .field-icon {
  top: auto;
}

.tp-selects select {
  height: 48px !important;
  border: 1px solid #ccc !important;
  border-radius: 8px !important;
}

/* =========================================
   8️⃣ SELECTOR DE HORA (DROPDOWN)
========================================= */
.time-field {
  position: relative;
}

.time-field::before {
  display: none !important;
}

.time-field .field-icon {
  display: flex !important;
  position: absolute;
  right: 12px;
  left: auto !important;
  top: 38px;
  transform: translateY(-50%);
  color: #333;
  font-size: 16px;
}

.time-field.icon-field {
  position: relative;
  display: flex;
  flex-direction: column;
}

.time-field label {
  margin-bottom: 5px;
  font-weight: bold;
  font-size: 13px;
}

.tp-selects {
  display: flex !important;
  gap: 5px !important;
  width: 100% !important;
}

.tp-selects select {
  width: 100%;
  height: 48px;
  border: 1px solid #ccc;
  border-radius: 8px;
  font-size: 15px;
  color: #333;
  background: white;
  padding: 0 36px 0 12px !important;
  appearance: none;
  -webkit-appearance: none;
  background-image: url("data:image/svg+xml;utf8,<svg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 24 24' fill='none' stroke='%23999' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'><polyline points='6 9 12 15 18 9'/></svg>");
  background-repeat: no-repeat;
  background-position: right 35px center;
  background-size: 12px;
}

.tp-selects select:focus {
  border-color: var(--brand);
  box-shadow: 0 0 0 3px rgba(178, 34, 34, 0.15);
  outline: none;
}

.tp-hidden-input {
  display: none !important;
}

/* =========================================
   9️⃣ INPUTS DE FECHA Y HORA (FLATPICKR)
========================================= */
input.flatpickr-input,
input#fecha_inicio_ui,
input#fecha_fin_ui {
  height: 48px !important;
  border: 1px solid #ccc !important;
  border-radius: 8px !important;
  font-size: 15px !important;
  padding-left: 36px !important;
  background: white !important;
  width: 100% !important;
}

input.flatpickr-input:focus,
input#fecha_inicio_ui:focus,
input#fecha_fin_ui:focus {
  border-color: var(--brand) !important;
  box-shadow: 0 0 0 3px rgba(178, 34, 34, 0.15) !important;
  outline: none !important;
}

.date-field {
  position: relative;
}

.date-field::before {
  content: "\f073";
  font-family: "Font Awesome 6 Free";
  font-weight: 400;
  position: absolute;
  left: 12px;
  top: 50%;
  transform: translateY(-50%);
  width: 16px;
  height: 16px;
  font-size: 14px;
  color: #333;
  z-index: 10;
  pointer-events: none;
}

/* =========================================
   1️⃣0️⃣ DÍAS PÍLDORA
========================================= */
.days-row {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 10px;
  margin-top: 12px;
}

.days-pill {
  display: inline-flex;
  gap: 8px;
  align-items: center;
  padding: 10px 12px;
  border-radius: 999px;
  border: 1px solid rgba(34,197,94,.25);
  background: rgba(34,197,94,.10);
  color: #065f46;
  font-weight: 900;
}

/* =========================================
   1️⃣1️⃣ BOTONES
========================================= */
.btn {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  gap: 8px;
  padding: 10px 16px;
  border-radius: 12px;
  border: 1px solid transparent;
  cursor: pointer;
  font-weight: 900;
  transition: transform .12s, filter .12s, box-shadow .12s;
}

.btn:hover {
  transform: translateY(-1px);
  filter: brightness(.98);
  box-shadow: 0 10px 18px rgba(0,0,0,.08);
}

.btn.primary {
  background: var(--brand);
  border-color: #a31818;
  color: #fff;
}

.btn.gray {
  background: #f1f5f9;
  border-color: #e5e7eb;
  color: #0f172a;
}

.btn.ghost {
  background: #ffffff80;
  border: 1px solid var(--stroke);
  backdrop-filter: blur(8px);
}

.btn.success {
  background: var(--ok);
  color: #fff;
  border-color: #027A48;
}

.btn.danger {
  background-color: #ef4444;
  color: white;
}

.btn.danger:hover {
  background-color: #b91c1c;
}

.acciones {
  display: flex;
  gap: 12px;
  justify-content: flex-end;
  flex-wrap: wrap;
}

/* =========================================
   1️⃣2️⃣ PICKER ROW (CATEGORÍAS, PROTECCIONES, ADICIONALES)
========================================= */
.picker-row {
  display: flex;
  gap: 10px;
  align-items: stretch;
}

.picker-selected {
  flex: 1;
  background: #fff;
  border: 1px solid var(--stroke);
  border-radius: 14px;
  padding: 10px 12px;
  min-height: 46px;
  display: flex;
  flex-direction: column;
  justify-content: center;
}

.picker-label {
  font-size: 11px;
  text-transform: uppercase;
  letter-spacing: .35px;
  color: var(--muted);
  font-weight: 900;
}

.picker-value {
  font-weight: 900;
  margin-top: 2px;
}

.picker-sub {
  font-size: 12px;
  color: #475569;
  margin-top: 2px;
}

@media (max-width: 720px) {
  .picker-row { flex-direction: column; }
}

.mini-preview {
  margin-top: 12px;
  border: 1px solid var(--stroke);
  border-radius: 16px;
  background: #fff;
  overflow: hidden;
}

.mini-right { padding: 14px; }
.mini-title { font-weight: 900; font-size: 16px; }
.mini-sub { margin-top: 4px; color: #475569; font-size: 13px; }
.mini-price {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 18px;
  margin-top: 12px;
}

.price-big {
  font-size: 18px;
  font-weight: 900;
}

.muted { color: var(--muted); }
.small { font-size: 12px; }

/* =========================================
   1️⃣3️⃣ BACKDROP
========================================= */
.fp-backdrop {
  position: fixed;
  inset: 0;
  background: rgba(0, 0, 0, 0.55);
  backdrop-filter: blur(3px);
  opacity: 0;
  pointer-events: none;
  transition: opacity 0.2s ease;
  z-index: 99998;
}

.fp-backdrop.is-open {
  opacity: 1;
  pointer-events: auto;
}

/* =========================================
   1️⃣4️⃣ FLATPICKR
========================================= */
.flatpickr-calendar {
  z-index: 99999 !important;
  border: 0 !important;
  border-radius: 18px !important;
  overflow: hidden !important;
  box-shadow: 0 26px 70px rgba(0, 0, 0, 0.25) !important;
  font-family: 'Poppins', system-ui, Arial, sans-serif !important;
  position: fixed !important;
  top: 50% !important;
  left: 50% !important;
  transform: translate(-50%, -50%) !important;
  margin: 0 !important;
  width: auto !important;
}

.flatpickr-calendar.open {
  width: min(860px, 94vw) !important;
  display: block !important;
}

.flatpickr-months {
  background: #f44336 !important;
  color: white !important;
}

.flatpickr-months * {
  color: white !important;
}

.flatpickr-months .flatpickr-month {
  height: 64px !important;
  color: white !important;
}

.flatpickr-current-month {
  color: white !important;
  font-weight: 900 !important;
  font-size: 22px !important;
  padding-top: 14px !important;
}

.flatpickr-current-month .cur-month,
.flatpickr-current-month .cur-year,
.flatpickr-current-month .numInputWrapper span {
  color: white !important;
}

.flatpickr-months .flatpickr-prev-month,
.flatpickr-months .flatpickr-next-month {
  fill: white !important;
  opacity: 1 !important;
}

.flatpickr-months .flatpickr-prev-month svg,
.flatpickr-months .flatpickr-next-month svg {
  fill: white !important;
}

.flatpickr-weekdays {
  background: white !important;
  border-bottom: 6px solid #f44336 !important;
}

span.flatpickr-weekday {
  color: #f44336 !important;
  font-weight: 900 !important;
  background: white !important;
}

.flatpickr-rContainer,
.flatpickr-days {
  width: 100% !important;
}

.dayContainer {
  width: 100% !important;
  min-width: 0 !important;
  max-width: none !important;
  display: flex !important;
  flex-wrap: wrap !important;
}

.flatpickr-day {
  border-radius: 12px !important;
  font-weight: 900 !important;
  height: 52px !important;
  line-height: 52px !important;
  max-width: none !important;
  color: #333 !important;
  width: calc(100% / 7) !important;
  flex: 0 0 calc(100% / 7) !important;
}

.flatpickr-day:hover {
  background: #ffe5e5 !important;
  color: #f44336 !important;
}

.flatpickr-day.today:not(.selected):not(.startRange):not(.endRange) {
  border: 2px solid #f44336 !important;
  color: #f44336 !important;
  background: white !important;
}

.flatpickr-day.selected,
.flatpickr-day.startRange,
.flatpickr-day.endRange {
  background: #f44336 !important;
  border-color: #f44336 !important;
  color: white !important;
}

.flatpickr-day.inRange {
  background: #ffe5e5 !important;
  color: #f44336 !important;
}

.flatpickr-day.startRange {
  border-radius: 999px 0 0 999px !important;
}

.flatpickr-day.endRange {
  border-radius: 0 999px 999px 0 !important;
}

.flatpickr-day.startRange.endRange {
  border-radius: 999px !important;
}

.flatpickr-day.prevMonthDay {
  opacity: 0.4 !important;
  color: #999 !important;
  background: #f5f5f5 !important;
  cursor: default !important;
}

.flatpickr-day.disabled,
.flatpickr-day.flatpickr-disabled {
  opacity: 0.4 !important;
  color: #999 !important;
  background: #f5f5f5 !important;
  cursor: not-allowed !important;
  pointer-events: none !important;
}

.flatpickr-day.nextMonthDay {
  opacity: 1 !important;
  color: #333 !important;
  background: white !important;
  cursor: pointer !important;
}

.fp-actions {
  display: flex;
  justify-content: space-between;
  align-items: center;
  gap: 10px;
  padding: 12px 14px;
  border-top: 1px solid var(--stroke);
  background: #fff;
}

.fp-actions button {
  border: 1px solid var(--stroke);
  background: #fff;
  padding: 10px 14px;
  border-radius: 12px;
  font-weight: 900;
  cursor: pointer;
  transition: all 0.2s ease;
}

.fp-actions .fp-today {
  color: #111827;
}

.fp-actions .fp-clear {
  color: #dc2626;
  border-color: rgba(220, 38, 38, 0.25);
}

.fp-actions .fp-label {
  color: #334155;
  background: #f8fafc;
}

.fp-actions button:hover {
  box-shadow: 0 10px 22px rgba(15, 23, 42, 0.10);
  transform: translateY(-1px);
}

.flatpickr-monthDropdown-months {
  color: white !important;
}

.flatpickr-monthDropdown-month {
  background-color: #fff !important;
  color: #333 !important;
}

/* =========================================
   1️⃣5️⃣ RESPONSIVE FLATPICKR
========================================= */
@media (max-width: 768px) {
  .flatpickr-calendar.open {
    width: min(700px, 92vw) !important;
    position: fixed !important;
    top: 50% !important;
    left: 50% !important;
    transform: translate(-50%, -50%) !important;
  }

  .flatpickr-day {
    height: 45px !important;
    line-height: 45px !important;
    font-size: 14px !important;
  }

  .flatpickr-current-month {
    font-size: 18px !important;
    padding-top: 10px !important;
  }

  .flatpickr-months .flatpickr-month {
    height: 55px !important;
  }
}

@media (max-width: 560px) {
  .flatpickr-calendar.open {
    width: min(420px, 90vw) !important;
    position: fixed !important;
    top: 50% !important;
    left: 50% !important;
    transform: translate(-50%, -50%) !important;
  }

  .flatpickr-day {
    height: 42px !important;
    line-height: 42px !important;
    font-size: 13px !important;
  }

  .tp-selects select,
  input.flatpickr-input,
  input#fecha_inicio_ui,
  input#fecha_fin_ui {
    height: 45px !important;
    font-size: 14px !important;
  }

  .field-icon {
    top: 36px;
    right: 10px;
    font-size: 14px;
  }

  .time-field .field-icon {
    top: 36px;
    right: 10px;
  }

  .icon-field input.input {
    height: 44px;
    font-size: 14px;
    padding: 0 32px 0 12px !important;
  }

  input.flatpickr-input,
  input#fecha_inicio_ui,
  input#fecha_fin_ui {
    padding: 0 32px 0 12px !important;
  }

  .tp-selects select {
    height: 44px;
    padding: 0 32px 0 12px !important;
  }
}

@media (max-width: 400px) {
  .flatpickr-calendar.open {
    width: min(380px, 95vw) !important;
  }

  .flatpickr-day {
    height: 38px !important;
    line-height: 38px !important;
    font-size: 12px !important;
  }
}
          </style>

         <div class="stack-body">
          <div class="form-2">
            <!-- FECHA DE SALIDA -->
            <div class="dt-field icon-field">
              <label>Fecha de salida</label>
              <span class="field-icon"><i class="fa-regular fa-calendar-days"></i></span>
              <input id="fecha_inicio_ui" class="input input-lg" type="text" placeholder="Fecha" autocomplete="off">
              <input id="fecha_inicio" name="fecha_inicio" type="hidden">
            </div>

            <!-- HORA DE SALIDA -->
            <div class="dt-field icon-field time-field">
              <label>Hora de salida</label>
              <span class="field-icon"><i class="fa-regular fa-clock"></i></span>
              <input id="hora_retiro_ui" class="input input-lg" type="text" placeholder="Hora" autocomplete="off">
              <input id="hora_retiro" name="hora_retiro" type="hidden">
            </div>

            <!-- FECHA DE LLEGADA -->
            <div class="dt-field icon-field">
              <label>Fecha de llegada</label>
              <span class="field-icon"><i class="fa-regular fa-calendar-days"></i></span>
              <input id="fecha_fin_ui" class="input input-lg" type="text" placeholder="Fecha" autocomplete="off">
              <input id="fecha_fin" name="fecha_fin" type="hidden">
            </div>

            <!-- HORA DE LLEGADA -->
            <div class="dt-field icon-field time-field">
              <label>Hora de llegada</label>
              <span class="field-icon"><i class="fa-regular fa-clock"></i></span>
              <input id="hora_entrega_ui" class="input input-lg" type="text" placeholder="" autocomplete="off">
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
              <input id="nombre_cliente" name="nombre_cliente" class="input" type="text" required  value="{{ $reservacion->nombre_cliente ?? '' }}">
            </div>

            <div>
              <label>Apellidos</label>
              <input id="apellidos_cliente" name="apellidos_cliente" class="input" type="text" required  value="{{ $reservacion->apellidos_cliente ?? '' }}">
            </div>

            <div>
              <label>Email</label>
              <input id="email_cliente" name="email_cliente" class="input" type="email" required  value="{{ $reservacion->email_cliente ?? '' }}" >
            </div>

            <div>
              <label>Teléfono</label>
              <div class="phone-grid" id="phoneCombo">
                <button class="phone-prefix" type="button" id="phone_toggle" aria-label="Elegir país">
                  <span class="flag" id="phone_flag">🇲🇽</span>
                  <span class="code" id="phone_code">+52</span>
                  <span class="chev">▾</span>
                </button>

                <input id="telefono_ui" class="input" type="tel" inputmode="tel" placeholder="4421234567" required value="{{ $reservacion->telefono_cliente ?? '' }}">

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

{{-- =========================================
     MODAL: CATEGORÍAS (ESTILO UNIFICADO V2)
========================================= --}}
<div class="pop modal" id="catPop">
  <div class="box modal-box" style="max-width: 950px;">
    <header class="modal-head" style="background: var(--brand); color: #fff;">
      <div class="modal-title" style="color:#fff;">🚗 Selecciona una categoría</div>
      <button class="btn" id="catClose" type="button" onclick="closePop('catPop')" style="background: rgba(255,255,255,0.2); border: none; color: white; border-radius: 8px; width: 32px; height: 32px; display: flex; align-items: center; justify-content: center;">✖</button>
    </header>

    <div class="modal-body" style="background: #f1f5f9;">

      <style>
        /* 1. Grid de Tarjetas */
        #catPop .grid-cards {
          display: grid !important;
          grid-template-columns: 1fr !important;
          gap: 16px !important;
          padding: 1s0px 0;
        }

        /* 2. Tarjeta Estilo Horizontal */
        #catPop .card-pick {
          display: grid !important;
          grid-template-columns: 220px 1fr 220px !important;
          gap: 20px !important;
          background: #fff !important;
          border: 1px solid rgba(0,0,0,0.08) !important;
          border-radius: 16px !important;
          padding: 20px !important;
          align-items: center !important;
          cursor: pointer !important;
          transition: all 0.25s ease;
          position: relative;
        }

        #catPop .card-pick:hover {
          border-color: var(--brand) !important;
          box-shadow: 0 12px 30px rgba(0,0,0,0.06) !important;
          transform: translateY(-3px);
        }

        /* 3. Imagen del Auto */
        #catPop .cp-img {
          height: 140px !important;
          background: #f8fafc;
          border-radius: 14px;
          display: flex;
          align-items: center;
          justify-content: center;
          border: 1px solid #f1f5f9;
        }
        #catPop .cp-img img { max-width: 90%; max-height: 85%; object-fit: contain; }

        /* 4. Títulos e Info */
        #catPop .cp-title { font-size: 20px; font-weight: 900; color: #1e293b; margin-bottom: 2px; }
        #catPop .cp-sub { font-size: 14px; color: #64748b; margin-bottom: 12px; }

        /* 5. CHIPS (IGUALES A IMAGEN 2) */
        #catPop .cp-features { display: flex; flex-wrap: wrap; gap: 8px; margin-top: 12px; }

        #catPop .cp-chip {
          display: inline-flex !important;
          align-items: center !important;
          gap: 6px !important;
          padding: 6px 12px !important;
          border-radius: 999px !important;
          /* Estilo Imagen 2: Blanco translúcido, borde fino y texto oscuro */
          background: rgba(255, 255, 255, 0.8) !important;
          border: 1px solid rgba(0, 0, 0, 0.1) !important;
          font-size: 12px !important;
          font-weight: 600 !important;
          color: #111827 !important;
          box-shadow: 0 1px 2px rgba(0,0,0,0.02);
        }

        #catPop .cp-chip i { font-size: 16px !important; color: #1a1a1a !important; }

        /* 6. Columna Derecha (Precios) */
        #catPop .cp-right {
          border-left: 1px solid #f1f5f9;
          padding-left: 20px;
          display: flex;
          flex-direction: column;
          gap: 12px;
          text-align: right;
        }
        #catPop .price-label { font-size: 11px; font-weight: 800; color: #94a3b8; text-transform: uppercase; letter-spacing: 0.5px; }
        #catPop .price-value { font-size: 22px; font-weight: 900; color: #1e293b; line-height: 1; }
        #catPop .price-value span { font-size: 13px; color: #64748b; font-weight: 400; }

        /* 7. Pills inferiores */
        #catPop .cp-meta { display: flex; gap: 8px; margin-top: 15px; }
        #catPop .pill {
            padding: 4px 10px; border-radius: 6px; font-size: 11px; font-weight: 800;
            background: #f1f5f9; color: #475569; border: 1px solid #e2e8f0;
        }
        #catPop .pill-ok { background: #dcfce7 !important; color: #166534 !important; border-color: #bbf7d0 !important; }

        @media (max-width: 850px) {
          #catPop .card-pick { grid-template-columns: 1fr !important; }
          #catPop .cp-right { border-left: 0; padding-left: 0; text-align: left; }
        }
      </style>

      <div class="grid-cards">
        @php
          $imgCategorias = [
            1 => asset('img/aveo.png'), 2 => asset('img/virtus.png'), 3 => asset('img/jetta.png'),
            4 => asset('img/camry.png'), 5 => asset('img/renegade.png'), 6 => asset('img/taos.png'),
            7 => asset('img/avanza.png'), 8 => asset('img/Odyssey.png'), 9 => asset('img/Urvan.png'),
            10 => asset('img/Frontier.png'), 11 => asset('img/Tacoma.png'),
          ];
          $pasajeros = [ 1=>5, 2=>5, 3=>5, 4=>5, 5=>5, 6=>5, 7=>7, 8=>8, 9=>13, 10=>5, 11=>5 ];
          $transmision = [ 9 => 'Manual' ];

          $categoriasOrdenadas = $categorias->sortBy(fn($c) => (float)($c->precio_dia ?? 0))->values();
        @endphp

        @foreach($categoriasOrdenadas as $cat)
          @php
            $img = $imgCategorias[$cat->id_categoria] ?? asset('img/Logotipo.png');
            $cap = $pasajeros[$cat->id_categoria] ?? 5;
            $tran = $transmision[$cat->id_categoria] ?? 'Automático';

            // Arreglo de características con Boxicons
            $features = [
                ['icon' => 'bx-infinite', 'text' => "Km ilimitados"],
                ['icon' => 'bx-shield-quarter', 'text' => "Relevo responsabilidad"],
                ['icon' => 'bx-user', 'text' => "{$cap} pasajeros"],
                ['icon' => 'bxl-apple', 'text' => "Apple CarPlay"],
                ['icon' => 'bxl-android', 'text' => "Android Auto"],
                ['icon' => 'bx-wind', 'text' => "Aire Acondicionado"],
                ['icon' => ($tran === 'Manual' ? 'bx-joystick' : 'bx-cog'), 'text' => $tran],
            ];
          @endphp

          <article class="card-pick"
            onclick="seleccionarCategoriaReservacion(this)"
            data-id="{{ $cat->id_categoria }}"
            data-nombre="{{ $cat->nombre }}"
            data-precio="{{ $cat->precio_dia }}"
            data-img="{{ $img }}"
          >
            <div class="cp-img">
              <img src="{{ $img }}" alt="{{ $cat->nombre }}">
            </div>

            <div class="cp-left">
              <div class="cp-title">{{ $cat->nombre }}</div>
              <div class="cp-sub">{{ $cat->descripcion ?? 'Chevrolet Aveo o similar' }}</div>

              <div class="cp-features">
                @foreach($features as $f)
                  <span class="cp-chip">
                    <i class='bx {{ $f['icon'] }}'></i>
                    <span>{{ $f['text'] }}</span>
                  </span>
                @endforeach
              </div>

              <div class="cp-meta">
                <span class="pill">Código: {{ $cat->codigo }}</span>
                @if(isset($cat->activo) && (int)$cat->activo === 1)
                  <span class="pill pill-ok">Activo</span>
                @endif
              </div>
            </div>

            <div class="cp-right">
              <div class="price-block">
                <div class="price-label">Tarifa base</div>
                <div class="price-value">${{ number_format((float)$cat->precio_dia, 2) }} <span>/ día</span></div>
              </div>

              <div class="price-block">
                <div class="price-label">Estimado</div>
                <div class="price-value" style="color:var(--brand)">
                    <span class="cat-estimado">$0.00</span> <span>MXN</span>
                </div>
              </div>

              <button class="btn primary btn-block" type="button" style="margin-top:10px; border-radius: 12px; font-weight: 800; height: 45px;">Seleccionar</button>
            </div>
          </article>
        @endforeach
      </div>
    </div>

    <footer class="modal-foot">
      <button class="btn gray" id="catCancel" type="button" onclick="closePop('catPop')">Cerrar</button>
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

{{-- MODAL: RESUMEN CON ICONOS --}}
<div class="pop modal" id="resumenPop">
  <div class="box modal-box resumen-box">
    <header class="modal-head">
      <div class="modal-title">
        <i class='bx bx-spreadsheet' style="vertical-align: middle; margin-right: 5px;"></i>
         Resumen de reservación
      </div>
      <button class="btn gray" id="resumenClose" type="button">✖</button>
    </header>

    <div class="modal-body">
      <div class="resumen-card">
        <!-- Ubicación con iconos -->
        <div class="res-row">
          <div><i class='bx bx-map-pin'></i> Retiro</div>
          <div id="resSucursalRetiro">—</div>
        </div>
        <div class="res-row">
          <div><i class='bx bx-flag'></i> Entrega</div>
          <div id="resSucursalEntrega">—</div>
        </div>

        <!-- Fechas y horas con iconos -->
        <div class="res-row">
          <div><i class='bx bx-calendar-event'></i> Salida</div>
          <div id="resFechaInicio">—</div>
        </div>
        <div class="res-row">
          <div><i class='bx bx-time-five'></i> Hora salida</div>
          <div id="resHoraInicio">—</div>
        </div>
        <div class="res-row">
          <div><i class='bx bx-calendar-check'></i> Llegada</div>
          <div id="resFechaFin">—</div>
        </div>
        <div class="res-row">
          <div><i class='bx bx-time'></i> Hora llegada</div>
          <div id="resHoraFin">—</div>
        </div>

        <!-- Días -->
        <div class="res-row">
          <div><i class='bx bx-timer'></i> Días</div>
          <div id="resDias">—</div>
        </div>

        <div class="divider"></div>

        <!-- Categoría y tarifa -->
        <div class="res-row">
          <div><i class='bx bx-car'></i> Categoría</div>
          <div id="resCat">—</div>
        </div>
        <div class="res-row">
          <div>
            <i class='bx bx-money'></i> Tarifa base
            <button type="button" id="btnEditarTarifa" style="background:none;border:none;color:#2563eb;cursor:pointer;font-size:16px;margin-left:6px;">
              <i class='bx bx-edit-alt'></i>
            </button>
          </div>
          <div id="resBaseDia">—</div>
        </div>
        <div class="res-row">
          <div><i class='bx bx-calculator'></i> Base × días</div>
          <div id="resBaseTotal">—</div>
        </div>

        <!-- Servicios, Protecciones, Adicionales -->
        <div class="res-row">
          <div><i class='bx bx-wrench'></i> Servicios</div>
          <div id="resServicios">—</div>
        </div>
        <div class="res-row">
          <div><i class='bx bx-shield-quarter'></i> Protección</div>
          <div id="resProte">—</div>
        </div>
        <div class="res-row">
          <div><i class='bx bx-plus-circle'></i> Adicionales</div>
          <div id="resAdds">—</div>
        </div>

        <div class="divider"></div>

        <!-- Totales -->
        <div class="res-row">
          <div><i class='bx bx-cart'></i> Subtotal</div>
          <div id="resSub">$0.00 MXN</div>
        </div>
        <div class="res-row">
          <div><i class='bx bx-percent'></i> IVA (16%)</div>
          <div id="resIva">$0.00 MXN</div>
        </div>
        <div class="res-row total">
          <div><i class='bx bx-dollar-circle'></i> Total</div>
          <div id="resTotal">$0.00 MXN</div>
        </div>
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
<script>
  window.reservacionEditar = @json($reservacion ?? null);
  window.serviciosEditar = @json($serviciosReserva ?? []);
  window.seguroEditar = @json($seguroReserva ?? null);
</script>
@section('js-vistareservacionesAdmin')
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script src="https://cdn.jsdelivr.net/npm/flatpickr/dist/l10n/es.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>


    <script>
        window.iconosPorId = {
            @foreach($sucursales as $ciudad => $grupo)
                @foreach($grupo as $s)
                    @php
                        $nombre = strtolower($s->sucursal);
                        $icono = 'fa-building';
                        if (str_contains($nombre, 'aeropuerto')) { $icono = 'fa-plane-departure'; }
                        elseif ((str_contains($nombre, 'central') || str_contains($nombre, 'autobuses')) && !str_contains($nombre, 'plaza central park')) { $icono = 'fa-bus'; }
                    @endphp
                    {{ $s->id_sucursal }}: '{{ $icono }}',
                @endforeach
            @endforeach
        };
    </script>

    <script src="{{ asset('js/reservacionesAdmin.js') }}"></script>
@endsection
