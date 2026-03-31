@extends('layouts.Usuarios')
@section('Titulo', __('Reservations'))

@section('css-vistaReservaciones')
    <link rel="stylesheet" href="{{ asset('css/reservaciones.css') }}">
@endsection
<script>
(function() {
  const navEntries = performance.getEntriesByType("navigation");
  const isReload = navEntries.length > 0 && navEntries[0].type === "reload";

  if (isReload) {
    const url = new URL(window.location.href);
    url.search = '';
    url.searchParams.set('step', '1');
    url.searchParams.set('reset', '1');

    document.write('');

    window.location.replace(url.toString());
  }
})();
</script>
@section('contenidoReservaciones')
    @php
        // ====== Estado recibido por GET (sin sesión) ======
        $f = $filters ?? [];

        $pickupSucursalId = $f['pickup_sucursal_id'] ?? request('pickup_sucursal_id');
        $dropoffSucursalId = $f['dropoff_sucursal_id'] ?? request('dropoff_sucursal_id');

        // =========================
        // ✅ Fechas robustas (ISO para lógica, DMY para UI)
        // =========================
        $reset = request('reset') == '1';
        $pickupDateRaw = $reset ? null : ($f['pickup_date'] ?? request('pickup_date'));
        $dropoffDateRaw = $reset ? null : ($f['dropoff_date'] ?? request('dropoff_date'));
        $pickupTime = $reset ? null : ($f['pickup_time'] ?? request('pickup_time'));
        $dropoffTime = $reset ? null : ($f['dropoff_time'] ?? request('dropoff_time'));

        // 🔹 Conversor a ISO SIN fechas por defecto
        $toIso = function ($val) {
            $val = is_string($val) ? trim($val) : '';
            if ($val === '') {
                return null;
            }

            // d-m-Y
            if (preg_match('/^\d{2}-\d{2}-\d{4}$/', $val)) {
                try {
                    return \Illuminate\Support\Carbon::createFromFormat('d-m-Y', $val)->format('Y-m-d');
                } catch (\Throwable $e) {
                }
            }

            // Y-m-d
            if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $val)) {
                return $val;
            }

            // fallback
            try {
                return \Illuminate\Support\Carbon::parse($val)->format('Y-m-d');
            } catch (\Throwable $e) {
            }

            return null;
        };

        // ✅ ISO real (para cálculos y Step 4) — solo si el cliente mandó algo
        $pickupDateISO = $toIso($pickupDateRaw);
        $dropoffDateISO = $toIso($dropoffDateRaw);

        // ✅ FIX: Si pickup_date viene como rango "YYYY-MM-DD a YYYY-MM-DD"
        if (is_string($pickupDateRaw) && str_contains($pickupDateRaw, ' a ')) {
            [$start, $end] = array_map('trim', explode(' a ', $pickupDateRaw, 2));
            $pickupDateISO = $toIso($start);
            $dropoffDateISO = $toIso($end);
        }

        // ✅ UI (para inputs flatpickr dd-mm-YYYY) — si no hay fecha, se queda vacío
        $pickupDate = $pickupDateISO ? \Illuminate\Support\Carbon::parse($pickupDateISO)->format('d-m-Y') : '';
        $dropoffDate = $dropoffDateISO ? \Illuminate\Support\Carbon::parse($dropoffDateISO)->format('d-m-Y') : '';

        // ✅ flujo por categoría
        $categoriaId = $f['categoria_id'] ?? request('categoria_id');
        $plan = $f['plan'] ?? request('plan'); // mostrador / linea

        // ✅ addons (Step 3) via GET (persistencia)
        $addonsParam = $f['addons'] ?? request('addons', '');

        // ==========================================================
        // ✅ STEP RESOLVER (FIX REAL)
        // ==========================================================
        $requestedStep = (int) request('step', 1);
            $controllerStep = isset($step) ? (int) $step : null;

            $pickupSucursalId = $f['pickup_sucursal_id'] ?? request('pickup_sucursal_id');
            $dropoffSucursalId = $f['dropoff_sucursal_id'] ?? request('dropoff_sucursal_id');

            if (empty($dropoffSucursalId) && !empty($pickupSucursalId)) {
                $dropoffSucursalId = $pickupSucursalId;
            }

            $step1DataComplete =!empty($pickupSucursalId) &&
                                !empty($dropoffSucursalId) &&
                                !empty($pickupDateISO) &&
                                !empty($dropoffDateISO) &&
                                !empty($pickupTime) &&
                                !empty($dropoffTime);

            if ($reset) {
                $stepCurrent = 1;
            }else if (!$step1DataComplete) {
                $stepCurrent = 1;
            } else {
                $stepCurrent = $controllerStep ?? $requestedStep;

                if ($stepCurrent >= 3 && (empty($categoriaId) || empty($plan))) {
                    $stepCurrent = 2;
                }
                if ($stepCurrent >= 4 && (empty($categoriaId) || empty($plan))) {
                    $stepCurrent = 2;
                }
            }
            $stepCurrent = max(1, min(4, $stepCurrent));

            if (request()->get('from') === 'welcome') {
                session(['from_welcome' => true]);
            }
            if ($step1DataComplete) {
                session()->forget('from_welcome');
            }

            $fromWelcome = request()->get('from') === 'welcome' || session('from_welcome');

            if ($stepCurrent == 1 && $step1DataComplete) {
                $fromWelcome = false;
            }

            $pickupName = null;
            $dropoffName = null;

        if (!empty($sucursales)) {
            $sucById = collect($sucursales)->keyBy('id_sucursal');
            $pickupName = $pickupSucursalId ? optional($sucById->get((int) $pickupSucursalId))->nombre : null;
            $dropoffName = $dropoffSucursalId ? optional($sucById->get((int) $dropoffSucursalId))->nombre : null;
        }

        if ((!$pickupName || !$dropoffName) && !empty($ciudades)) {
            $map = collect($ciudades)->flatMap(fn($c) => collect($c->sucursalesActivas ?? []))->keyBy('id_sucursal');
            $pickupName =
                $pickupName ?: ($pickupSucursalId ? optional($map->get((int) $pickupSucursalId))->nombre : null);
            $dropoffName =
                $dropoffName ?: ($dropoffSucursalId ? optional($map->get((int) $dropoffSucursalId))->nombre : null);
        }

        // Base params (incluye addons para NO perderlos)
        $baseParams = array_filter(
            [
                'pickup_sucursal_id' => $pickupSucursalId,
                'dropoff_sucursal_id' => $dropoffSucursalId,
                'pickup_date' => $pickupDate,
                'pickup_time' => $pickupTime,
                'dropoff_date' => $dropoffDate,
                'dropoff_time' => $dropoffTime,
                'categoria_id' => $categoriaId,
                'plan' => $plan,
                'addons' => $addonsParam,
            ],
            fn($v) => $v !== null && $v !== '',
        );

        $toStep = function (int $to, array $extra = []) use ($baseParams) {
            return route('rutaReservasIniciar', array_merge($baseParams, ['step' => $to], $extra));
        };

        // ✅ días (mínimo 1)abc
        $days = 1;

try {
    if ($pickupDateISO && $pickupTime && $dropoffDateISO && $dropoffTime) {

        $d1 = \Illuminate\Support\Carbon::createFromFormat(
            'Y-m-d H:i',
            "{$pickupDateISO} {$pickupTime}"
        );

        $d2 = \Illuminate\Support\Carbon::createFromFormat(
            'Y-m-d H:i',
            "{$dropoffDateISO} {$dropoffTime}"
        );

        $horasTotales = $d1->diffInHours($d2);

        $diasBase = intdiv($horasTotales, 24);
        $horasExtra = $horasTotales % 24;

        // ✅ misma lógica que controller y JS
        if ($horasExtra > 1) {
            $days = $diasBase + 1;
        } else {
            $days = max(1, $diasBase);
        }
    }
} catch (\Throwable $e) {
    $days = 1;
}

        // ✅ categoría seleccionada
        $categoriaSel = null;
        if (!empty($categorias) && $categoriaId) {
            $categoriaSel = collect($categorias)->firstWhere('id_categoria', (int) $categoriaId);
        }

        // ✅ Texto para "Tu auto"
        $autoTitulo =
            $categoriaSel && isset($categoriaSel->descripcion) && trim((string) $categoriaSel->descripcion) !== ''
                ? (string) $categoriaSel->descripcion
                : __('Car or similar');

        // ✅ Línea secundaria
        $catNombreUpper =
            $categoriaSel && isset($categoriaSel->nombre) ? strtoupper((string) $categoriaSel->nombre) : 'CATEGORY';

        $catCodigoUpper =
            $categoriaSel && isset($categoriaSel->codigo) ? strtoupper((string) $categoriaSel->codigo) : '';

        $autoSubtitulo = $catCodigoUpper ? $catNombreUpper . ' | CATEGORY ' . $catCodigoUpper : $catNombreUpper;

        // ✅ IMÁGENES
        $catImages = [
            1 => asset('img/aveo.png'),
            2 => asset('img/virtus.png'),
            3 => asset('img/jetta.png'),
            4 => asset('img/camry.png'),
            5 => asset('img/renegade.png'),
            6 => asset('img/taos.png'),
            7 => asset('img/avanza.png'),
            8 => asset('img/Odyssey.png'),
            9 => asset('img/Hiace.png'),
            10 => asset('img/Frontier.png'),
            11 => asset('img/Tacoma.png'),
        ];

        $placeholder = asset('img/Logotipo.png');

        $categoriaImg = $categoriaSel ? $catImages[$categoriaSel->id_categoria] ?? $placeholder : $placeholder;

        $precioDiaCategoria = (float) ($categoriaSel->precio_dia ?? 0);
        if ($plan === 'mostrador') {
            $precioDiaCategoria = round($precioDiaCategoria * 1.15);
        }
        $tarifaBase = $precioDiaCategoria > 0 ? $precioDiaCategoria * $days : 0.0;

        // split hora/min para Step 1 (UI) — sin defaults si no hay hora
        if (!empty($pickupTime)) {
            [$ph, $pm] = array_pad(explode(':', $pickupTime), 2, '00');
        } else {
            $ph = '';
            $pm = '';
        }

        if (!empty($dropoffTime)) {
            [$dh, $dm] = array_pad(explode(':', $dropoffTime), 2, '00');
        } else {
            $dh = '';
            $dm = '';
        }

        // DOB range: 100 años atrás, hasta (hoy-18)
        $currentYear = now()->year;
        $minYear = $currentYear - 100;
        $maxYear = $currentYear - 18;

        // ✅ features para Step 4 (si existen en BD)
        $featPassengers = (int) ($categoriaSel->pasajeros ?? 0);
        $featCarplay = (int) ($categoriaSel->apple_carplay ?? 0);
        $featAndroidAuto = (int) ($categoriaSel->android_auto ?? 0);
        $featAc = (int) ($categoriaSel->aire_acondicionado ?? ($categoriaSel->aire_ac ?? 0));

        // ✅ Fecha abreviada (3 letras) como calendario: "Mié 18 Feb 2026"
        \Carbon\Carbon::setLocale('es');
        $pickupFechaLarga = $pickupDateISO
            ? strtoupper(\Carbon\Carbon::parse($pickupDateISO)->translatedFormat('D d M Y'))
            : null;
        $dropoffFechaLarga = $dropoffDateISO
            ? strtoupper(\Carbon\Carbon::parse($dropoffDateISO)->translatedFormat('D d M Y'))
            : null;

        // ✅ Burbuja roja (YA NO SE USA en UI, pero lo dejamos por si lo ocupas después)
        $tagCategoria =
            $categoriaSel && isset($categoriaSel->nombre) ? strtoupper((string) $categoriaSel->nombre) : 'COMPACT';

        // ✅ SOLO estos extras (Step 3) — máximo 3 por cada uno (lo limita tu JS)
        $serviciosFiltrados = collect($servicios ?? [])
            ->filter(function ($s) {

                $name = mb_strtolower(trim((string) ($s->nombre ?? '')));

                return str_contains($name, 'silla')
                    || str_contains($name, 'gasolina prepago')
                    || str_contains($name, 'conductor adicional');
            })
            ->sortBy(function ($s) {

                $name = mb_strtolower(trim((string) ($s->nombre ?? '')));

                if (str_contains($name, 'silla')) return 1;
                if (str_contains($name, 'gasolina')) return 2;
                if (str_contains($name, 'conductor')) return 3;

                return 99;

            })
            ->values();
    @endphp

    <main class="page wizard-page {{ $fromWelcome ? 'modo-welcome' : '' }}" data-current-step="{{ $stepCurrent }}" data-plan="{{ $plan ?? '' }}"
        style="position:relative; overflow:visible;">

        {{-- ✅ Fondo SOLO dentro del main (NO footer) --}}
        <div class="fondos-reservaciones"
            style="background-image: url('../img/banner/banner-reservaciones.webp'); background-attachment: fixed; background-size: cover; background-position: center; min-height: 100vh;">

            <style>
                .reservas-bg {
                    position: absolute;
                    inset: 0;
                    z-index: 0;
                    pointer-events: none;
                    background:
                        linear-gradient(180deg, rgba(15, 23, 42, .70), rgba(15, 23, 42, .62)),
                        url("{{ asset('img/4x4.png') }}");
                    background-size: cover;
                    background-position: center;
                    background-repeat: no-repeat;
                }

                .wizard-steps,
                .wizard-card {
                    position: relative;
                    z-index: 1;
                }

                .page.wizard-page:not([data-current-step="1"]) .ctl input,
                .page.wizard-page:not([data-current-step="1"]) .ctl select {
                    padding-left: 46px;
                }

                .ctl input:focus,
                .ctl select:focus {
                    border-color: #b22222;
                    box-shadow: 0 0 0 4px rgba(178, 34, 34, .12);
                }

                .page.wizard-page:not([data-current-step="1"]) .ctl select {
                    appearance: none;
                    -webkit-appearance: none;
                    -moz-appearance: none;
                    background-image:
                        linear-gradient(45deg, transparent 50%, #9ca3af 50%),
                        linear-gradient(135deg, #9ca3af 50%, transparent 50%);
                    background-position:
                        calc(100% - 18px) 50%,
                        calc(100% - 12px) 50%;
                    background-size: 6px 6px, 6px 6px;
                    background-repeat: no-repeat;
                    padding-right: 34px;
                }

                .page.wizard-page:not([data-current-step="1"]) .wizard-form .field label{
                    display: block;
                    text-align: center;
                    font-size: 12px;
                    color: #6b7280;
                    font-weight: 900;
                    text-transform: uppercase;
                    letter-spacing: .45px;
                    margin-bottom: 8px;
                }

                .page.wizard-page:not([data-current-step="1"]) .wizard-form .group-title {
                    font-weight: 900;
                    text-transform: uppercase;
                    letter-spacing: .45px;
                    font-size: 13px;
                    color: #6b7280;
                }

                @media (min-width:981px) {
                    .search-grid>.group-card {
                        padding-right: 16px;
                        border-right: 1px solid #eef2f7;
                    }

                    .search-grid>.group-card:last-child {
                        border-right: 0;
                        padding-right: 0;
                    }

                    .group-head {
                        margin: 0 0 10px;
                        padding-bottom: 8px;
                        border-bottom: 1px dashed rgba(178, 34, 34, .22);
                    }
                }

                /* imagen de fondo  fija*/
                .fondo-fijo-layout {
                    position: fixed;
                    top: 0;
                    left: 0;
                    width: 100vw;
                    height: 100vh;
                    z-index: -1;
                    background-image: linear-gradient(rgba(0, 0, 0, 0.5), rgba(0, 0, 0, 0.5)), url("{{ asset('img/4x4.png') }}");
                    background-size: cover;
                    background-position: center center;
                    background-repeat: no-repeat;
                }

                .fondos-reservaciones {
                    background: transparent !important;
                }
            </style>
            <div class="fondo-fijo-layout" style="pointer-events:none;"></div>

            {{-- ===================== PASOS ARRIBA ===================== --}}
            <nav class="wizard-steps" aria-label="{{ __('Steps') }}">
                <a class="wizard-step {{ $stepCurrent > 1 ? 'done' : '' }} {{ $stepCurrent === 1 ? 'active' : '' }}"
                    href="{{ $toStep(1) }}">
                    <span class="n">1</span> {{ __('General') }}
                </a>
                <a class="wizard-step {{ $stepCurrent > 2 || request('auto') ? 'done' : '' }} {{ $stepCurrent === 2 ? 'active' : '' }}"
                    href="{{ $toStep(2) }}">
                    <span class="n">2</span> {{ __('Category') }}
                </a>
                <a class="wizard-step {{ $stepCurrent > 3 ? 'done' : '' }} {{ $stepCurrent === 3 ? 'active' : '' }}"
                    href="{{ $toStep(3) }}">
                    <span class="n">3</span> {{ __('Extras') }}
                </a>
                <a class="wizard-step {{ $stepCurrent === 4 ? 'active' : '' }}" href="{{ $toStep(4) }}">
                    <span class="n">4</span> {{ __('Confirmation') }}
                </a>
            </nav>

            <section class="wizard-card">
{{-- ===================== STEP 1 ===================== --}}
@if ($stepCurrent === 1)
    {{-- BUSCADOR PRINCIPAL --}}
    <div class="search-card" id="miBuscador">
        <header class="wizard-head">
            <h2>{{ __('About your reservation') }}</h2>
        </header>

        <form method="GET" action="{{ route('rutaReservasIniciar') }}" class="search-form" id="step1Form" novalidate>
            <input type="hidden" name="step" value="2">

            @if (!empty($addonsParam))
                <input type="hidden" name="addons" value="{{ $addonsParam }}">
            @endif

            <div class="search-grid">

                {{-- =========================
                   COLUMNA 1: LUGAR DE RENTA
                ========================= --}}
                <div class="sg-col sg-col-location">
                    <div class="location-head">
                        <span class="field-title">{{ __('Pick-up location') }}</span>
                        <label class="inline-check" for="differentDropoff">
                            <input type="checkbox" id="differentDropoff" name="different_dropoff" value="1"
                                {{ request('different_dropoff') ? 'checked' : '' }}>
                           <span class="checkbox-text">{{ __('Different return location') }}</span>
                        </label>
                    </div>

                    <div class="location-inputs-wrapper" id="locationInputsWrapper">
                        {{-- SELECT PICKUP --}}
                        <div class="field icon-field" id="pickupField">
                            <span class="field-icon"><i id="pickupIcon" class="fa-solid fa-location-dot"></i></span>
                            <select id="pickupPlace" name="pickup_sucursal_id" required>
                                <option value="" disabled {{ $pickupSucursalId ? '' : 'selected' }}>{{ __('Where does your trip begin?') }}</option>
                                @foreach ($ciudades->where('nombre', 'Querétaro') as $ciudad)
                                    <optgroup label="{{ $ciudad->nombre }}{{ $ciudad->estado ? ' — ' . $ciudad->estado : '' }}">
                                        @foreach ($ciudad->sucursalesActivas ?? [] as $suc)
                                            @php
                                                $name = strtolower($suc->nombre);
                                                $icon = 'fa-solid fa-location-dot';
                                                if (str_contains($name, 'aeropuerto')) {
                                                    $icon = 'fa-solid fa-plane-departure';
                                                } elseif (str_contains($name, 'central de autobuses')) {
                                                    $icon = 'fa-solid fa-bus';
                                                } elseif (str_contains($name, 'oficina') || str_contains($name, 'central park')) {
                                                    $icon = 'fa-solid fa-building';
                                                }
                                            @endphp
                                            <option value="{{ $suc->id_sucursal }}"
                                                data-icon="{{ $icon }}"
                                                {{ (string) $pickupSucursalId === (string) $suc->id_sucursal ? 'selected' : '' }}>
                                                {{ $suc->nombre }}
                                            </option>
                                        @endforeach
                                    </optgroup>
                                @endforeach
                            </select>
                        </div>

                        {{-- SELECT DROPOFF --}}
                        <div class="field icon-field" id="dropoffWrapper" style="display: {{ request('different_dropoff') ? 'block' : 'none' }};">
                            <span class="field-icon"><i id="dropoffIcon" class="fa-solid fa-location-dot"></i></span>
                            <select id="dropoffPlace" name="dropoff_sucursal_id">
                                <option value="" disabled {{ $dropoffSucursalId ? '' : 'selected' }}>{{ __('Where does your trip end?') }}</option>
                               @foreach ($ciudades->sortByDesc(function($c) { return $c->nombre === 'Querétaro';}) as $ciudad)
                                    <optgroup label="{{ $ciudad->nombre }}{{ $ciudad->estado ? ' — ' . $ciudad->estado : '' }}">
                                        @foreach ($ciudad->sucursalesActivas ?? [] as $suc)
                                            @php
                                                $name = strtolower($suc->nombre);
                                                $icon = 'fa-solid fa-location-dot';
                                                if (str_contains($name, 'aeropuerto')) {
                                                    $icon = 'fa-solid fa-plane-departure';
                                                } elseif (str_contains($name, 'central de autobuses')) {
                                                    $icon = 'fa-solid fa-bus';
                                                } elseif (str_contains($name, 'oficina') || str_contains($name, 'central park')) {
                                                    $icon = 'fa-solid fa-building';
                                                }
                                            @endphp
                                            <option value="{{ $suc->id_sucursal }}"
                                                data-icon="{{ $icon }}"
                                                {{ (string) $dropoffSucursalId === (string) $suc->id_sucursal ? 'selected' : '' }}>
                                                {{ $suc->nombre }}
                                            </option>
                                        @endforeach
                                    </optgroup>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>

                {{-- =========================
                   COLUMNA 2: FECHAS Y HORAS (PICKUP)
                ========================= --}}
                <div class="sg-col sg-col-datetime">
                    <div class="field">
                        <span class="field-title solo-responsivo-izq">{{ __('Pick-up') }}</span>
                        <div class="datetime-row">
                            <div class="dt-field icon-field">
                                <span class="field-icon"><i class="fa-regular fa-calendar-days"></i></span>
                                <input id="start" name="pickup_date" type="text"
                                    placeholder="{{ __('Date') }}" class="flatpickr-input"
                                    value="{{ $pickupDate }}" required>
                            </div>
                            <div class="dt-field icon-field time-field">
                                <span class="field-icon"><i class="fa-regular fa-clock"></i></span>
                                <input type="hidden" name="pickup_time" id="pickup_time_hidden" value="{{ $pickupTime }}">
                                <select id="pickup_h" name="pickup_h" required class="time-select">
                                    <option value="" disabled {{ empty($ph) ? 'selected' : '' }}>{{ __('Time') }}</option>
                                    @for ($i = 0; $i <= 23; $i++)
                                        @php $hh = str_pad((string)$i,2,'0',STR_PAD_LEFT); @endphp
                                        <option value="{{ $hh }}" {{ $hh === $ph ? 'selected' : '' }}>{{ $hh }}:00</option>
                                    @endfor
                                </select>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- =========================
                   COLUMNA 3: FECHAS Y HORAS (DEVOLUCIÓN)
                ========================= --}}
                <div class="sg-col sg-col-datetime">
                    <div class="field">
                        <span class="field-title solo-responsivo-izq">{{ __('Return') }}</span>
                        <div class="datetime-row">
                            <div class="dt-field icon-field">
                                <span class="field-icon"><i class="fa-regular fa-calendar-days"></i></span>
                                <input id="end" name="dropoff_date" type="text"
                                    placeholder="{{ __('Date') }}" class="flatpickr-input"
                                    value="{{ $dropoffDate }}" required>
                            </div>
                            <div class="dt-field icon-field time-field">
                                <span class="field-icon"><i class="fa-regular fa-clock"></i></span>
                                <input type="hidden" name="dropoff_time" id="dropoff_time_hidden" value="{{ $dropoffTime }}">
                                <select id="dropoff_h" name="dropoff_h" required class="time-select">
                                    <option value="" disabled {{ empty($dh) ? 'selected' : '' }}>{{ __('Time') }}</option>
                                    @for ($i = 0; $i <= 23; $i++)
                                        @php $hh = str_pad((string)$i,2,'0',STR_PAD_LEFT); @endphp
                                        <option value="{{ $hh }}" {{ $hh === $dh ? 'selected' : '' }}>{{ $hh }}:00</option>
                                    @endfor
                                </select>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- =========================
                   COLUMNA 4: BOTÓN SIGUIENTE
                ========================= --}}
                <div class="sg-col sg-col-submit">
                    <div class="actions">
                        <button type="submit" class="btn btn-primary">{{ __('Next') }}</button>
                    </div>
                </div>

            </div>
        </form>
    </div>
<script>
if (window.location.search.includes('from=welcome')) {
    const url = new URL(window.location.href);
    url.searchParams.delete('from');
    window.history.replaceState({}, document.title, url.pathname + '?' + url.searchParams.toString());
}

        document.addEventListener('DOMContentLoaded', function() {
            // Función para combinar hora
            function combineTime() {
                const pickupH = document.getElementById('pickup_h');
                const pickupHidden = document.getElementById('pickup_time_hidden');
                const dropoffH = document.getElementById('dropoff_h');
                const dropoffHidden = document.getElementById('dropoff_time_hidden');

                if (pickupH && pickupHidden) {
                    pickupHidden.value = pickupH.value ? pickupH.value + ':00:00' : '';
                }

                if (dropoffH && dropoffHidden) {
                    dropoffHidden.value = dropoffH.value ? dropoffH.value + ':00:00' : '';
                }
            }

            // Event listeners para combinar tiempo
            ['pickup_h', 'dropoff_h'].forEach(id => {
                const el = document.getElementById(id);
                if (el) el.addEventListener('change', combineTime);
            });

            // ============================================================
            // MANEJAR CHECKBOX DE DIFERENTE DESTINO - VERSIÓN MEJORADA
            // ============================================================
            const differentDropoff = document.getElementById('differentDropoff');
            const dropoffWrapper = document.getElementById('dropoffWrapper');
            const pickupSelect = document.getElementById('pickupPlace');
            const dropoffSelect = document.getElementById('dropoffPlace');

            if (differentDropoff && dropoffWrapper) {
                // Función para actualizar el estado del dropoff
                function actualizarDropoff() {
                    const isChecked = differentDropoff.checked;

                    // Mostrar/ocultar el wrapper
                    dropoffWrapper.style.display = isChecked ? 'block' : 'none';

                    // Habilitar/deshabilitar el select
                    if (dropoffSelect) {
                        dropoffSelect.disabled = !isChecked;
                        dropoffSelect.required = isChecked;

                        // Si está deshabilitado, copiar valor de pickup
                        if (!isChecked && pickupSelect && pickupSelect.value) {
                            dropoffSelect.value = pickupSelect.value;
                        } else if (!isChecked) {
                            dropoffSelect.value = '';
                        }
                    }

                    console.log('Checkbox cambiado:', isChecked ? 'Mostrar dropoff' : 'Ocultar dropoff');
                }

                // Ejecutar al cargar la página (para mantener estado)
                actualizarDropoff();

                // Escuchar cambios en el checkbox
                differentDropoff.addEventListener('change', function() {
                    actualizarDropoff();
                });

                // Si pickup cambia y dropoff está deshabilitado, actualizar valor
                if (pickupSelect && dropoffSelect) {
                    pickupSelect.addEventListener('change', function() {
                        if (!differentDropoff.checked) {
                            dropoffSelect.value = this.value;
                            console.log('Pickup cambiado, dropoff actualizado:', this.value);
                        }
                    });
                }
            }

            // ============================================================
            // INICIALIZAR FLATPICKR CON SOPORTE PARA ALTINPUT
            // ============================================================
            if (typeof flatpickr !== 'undefined') {
                // Para pickup date
                const startInput = document.getElementById('start');
                if (startInput) {
                    startInput._flatpickr = flatpickr(startInput, {
                        dateFormat: "Y-m-d",
                        altInput: true,
                        altFormat: "d M Y",
                        minDate: "today",
                        locale: {
                            firstDayOfWeek: 1,
                            months: {
                                shorthand: ['Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun', 'Jul', 'Ago', 'Sep', 'Oct', 'Nov', 'Dic'],
                                longhand: ['Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre']
                            }
                        },
                        onChange: function(selectedDates, dateStr, instance) {
                            // Sincronizar clases entre el input original y el altInput
                            if (dateStr) {
                                instance.altInput.classList.add('field-success');
                                instance.altInput.classList.remove('field-error');
                            } else {
                                instance.altInput.classList.remove('field-success', 'field-error');
                            }
                        }
                    });
                    setTimeout(() => {
                    if (startInput && startInput._flatpickr) {
                        startInput._flatpickr.setDate(startInput.value, true);
                    }
                }, 50);
                }

                // Para dropoff date
                const endInput = document.getElementById('end');
                if (endInput) {
                    endInput._flatpickr = flatpickr(endInput, {
                        dateFormat: "Y-m-d",
                        altInput: true,
                        altFormat: "d M Y",
                        minDate: "today",
                        locale: {
                            firstDayOfWeek: 1,
                            months: {
                                shorthand: ['Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun', 'Jul', 'Ago', 'Sep', 'Oct', 'Nov', 'Dic'],
                                longhand: ['Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre']
                            }
                        },
                        onChange: function(selectedDates, dateStr, instance) {
                            // Sincronizar clases entre el input original y el altInput
                            if (dateStr) {
                                instance.altInput.classList.add('field-success');
                                instance.altInput.classList.remove('field-error');
                            } else {
                                instance.altInput.classList.remove('field-success', 'field-error');
                            }
                        }
                    });
                    setTimeout(() => {
                    if (endInput && endInput._flatpickr) {
                        endInput._flatpickr.setDate(endInput.value, true);
                    }
                }, 50);
                }
            }

            // Inicializar iconos en selects
            function updateSelectIcon(select, iconElement) {
                const selectedOption = select.options[select.selectedIndex];
                if (selectedOption && selectedOption.dataset.icon) {
                    iconElement.className = selectedOption.dataset.icon;
                } else {
                    iconElement.className = 'fa-solid fa-location-dot';
                }
            }

            const pickupIcon = document.getElementById('pickupIcon');
            const dropoffIcon = document.getElementById('dropoffIcon');

            if (pickupSelect && pickupIcon) {
                pickupSelect.addEventListener('change', function() {
                    updateSelectIcon(this, pickupIcon);
                });
                updateSelectIcon(pickupSelect, pickupIcon);
            }

            if (dropoffSelect && dropoffIcon) {
                dropoffSelect.addEventListener('change', function() {
                    updateSelectIcon(this, dropoffIcon);
                });
                updateSelectIcon(dropoffSelect, dropoffIcon);
            }

            // ============================================================
            // CONTROL DEL BUSCADOR RESPONSIVO - CON BOTÓN DE CERRAR
            // ============================================================
            const btnAbrir = document.getElementById('btn-abrir-buscador-reservas');
            const btnCerrar = document.getElementById('btn-cerrar-buscador-politicas');
            const buscador = document.getElementById('miBuscador');

            if (btnAbrir && btnCerrar && buscador) {
                function bloquearScroll() {
                    const scrollY = window.scrollY;
                    document.body.style.top = `-${scrollY}px`;
                    document.body.style.left = '0';
                    document.body.style.right = '0';
                    document.body.style.width = '100%';
                    document.body.dataset.scrollY = scrollY;
                }

                function restaurarScroll() {
                }

                btnAbrir.addEventListener('click', function(e) {
                    e.preventDefault();
                    buscador.classList.add('active');

                    // Forzar actualización del checkbox cuando se abre el buscador
                    setTimeout(function() {
                        const differentDropoff = document.getElementById('differentDropoff');
                        const dropoffWrapper = document.getElementById('dropoffWrapper');
                        const dropoffSelect = document.getElementById('dropoffPlace');

                        if (differentDropoff && dropoffWrapper) {
                            // Aplicar el estado actual del checkbox
                            const isChecked = differentDropoff.checked;
                            dropoffWrapper.style.display = isChecked ? 'block' : 'none';

                            if (dropoffSelect) {
                                dropoffSelect.disabled = !isChecked;
                                dropoffSelect.required = isChecked;
                            }
                        }
                    }, 100);
                });

                btnCerrar.addEventListener('click', function(e) {
                    e.preventDefault();
                    buscador.classList.remove('active');
                });

                // Cerrar con Escape
                window.addEventListener('keydown', function(e) {
                    if (e.key === 'Escape' && buscador.classList.contains('active')) {
                        buscador.classList.remove('active');
                        restaurarScroll();
                    }
                });

                // Prevenir scroll cuando el buscador está abierto
                document.body.addEventListener('touchmove', function(e) {
                    if (buscador.classList.contains('active')) {
                        e.preventDefault();
                    }
                }, { passive: false });
            }

            // ============================================================
            // FUNCIÓN MEJORADA PARA MOSTRAR ERRORES (SOPORTA FLATPICKR)
            // ============================================================
            function mostrarError(element, mensaje) {
                // Limpiar error anterior
                const existingError = element.parentElement?.querySelector('.error-msg');
                if (existingError) existingError.remove();

                // Verificar si es un input de flatpickr y tiene altInput
                if (element._flatpickr && element._flatpickr.altInput) {
                    const altInput = element._flatpickr.altInput;
                    altInput.classList.remove('field-success');
                    altInput.classList.add('field-error');

                    const errorDiv = document.createElement('div');
                    errorDiv.className = 'error-msg';
                    errorDiv.textContent = mensaje;
                    altInput.parentElement?.appendChild(errorDiv);
                } else {
                    // Para elementos normales
                    element.classList.remove('field-success');
                    element.classList.add('field-error');

                    const errorDiv = document.createElement('div');
                    errorDiv.className = 'error-msg';
                    errorDiv.textContent = mensaje;
                    element.parentElement?.appendChild(errorDiv);
                }
            }

            function limpiarErrores() {
                // Limpiar mensajes de error
                document.querySelectorAll('.error-msg').forEach(el => el.remove());

                // Limpiar clases de todos los elementos
                document.querySelectorAll('.field-error, .field-success').forEach(el => {
                    el.classList.remove('field-error', 'field-success');
                });

                // Limpiar también los altInputs de flatpickr
                const start = document.getElementById('start');
                const end = document.getElementById('end');

                if (start && start._flatpickr && start._flatpickr.altInput) {
                    start._flatpickr.altInput.classList.remove('field-error', 'field-success');
                }

                if (end && end._flatpickr && end._flatpickr.altInput) {
                    end._flatpickr.altInput.classList.remove('field-error', 'field-success');
                }
            }

            // ============================================================
            // VALIDACIÓN DEL FORMULARIO
            // ============================================================
            const form = document.getElementById('step1Form');

            if (form) {
                form.addEventListener('submit', function(e) {
                    e.preventDefault();

                     console.log("SUBMIT DETECTADO");

                    limpiarErrores();

                    let valido = true;

                    // Validar pickup
                    const pickup = document.getElementById('pickupPlace');
                    if (!pickup.value) {
                        valido = false;
                        mostrarError(pickup, '{{ __("Location required") }}');
                    } else {
                        pickup.classList.add('field-success');
                    }

                    // Validar dropoff si está visible (usando differentDropoff.checked)
                    const differentDropoff = document.getElementById('differentDropoff');
                    const dropoff = document.getElementById('dropoffPlace');
                    if (differentDropoff.checked && !dropoff.value) {
                        valido = false;
                        mostrarError(dropoff, '{{ __("Location required") }}');
                    } else if (differentDropoff.checked) {
                        dropoff.classList.add('field-success');
                    }

                    // Validar fechas (con soporte para flatpickr)
                    const start = document.getElementById('start');
                    if (!start.value) {
                        valido = false;
                        mostrarError(start, '{{ __("Date required") }}');
                    } else {
                        if (start._flatpickr && start._flatpickr.altInput) {
                            start._flatpickr.altInput.classList.add('field-success');
                        } else {
                            start.classList.add('field-success');
                        }
                    }

                    const end = document.getElementById('end');
                    if (!end.value) {
                        valido = false;
                        mostrarError(end, '{{ __("Date required") }}');
                    } else {
                        if (end._flatpickr && end._flatpickr.altInput) {
                            end._flatpickr.altInput.classList.add('field-success');
                        } else {
                            end.classList.add('field-success');
                        }
                    }

                    // Validar horas
                    const pickupH = document.getElementById('pickup_h');
                    if (!pickupH.value) {
                        valido = false;
                        mostrarError(pickupH, '{{ __("Time required") }}');
                    } else {
                        pickupH.classList.add('field-success');
                    }

                    const dropoffH = document.getElementById('dropoff_h');
                    if (!dropoffH.value) {
                        valido = false;
                        mostrarError(dropoffH, '{{ __("Time required") }}');
                    } else {
                        dropoffH.classList.add('field-success');
                    }

                    if (valido) {
                        form.submit();
                    }
                });
            }
        });

        // Función para limpiar el formulario
        function limpiarTodoYReiniciar() {
            const form = document.getElementById('step1Form');
            if (form) {
                form.reset();
                document.getElementById('pickup_time_hidden').value = '';
                document.getElementById('dropoff_time_hidden').value = '';

                const dropoffWrapper = document.getElementById('dropoffWrapper');
                const differentDropoff = document.getElementById('differentDropoff');
                const dropoffSelect = document.getElementById('dropoffPlace');

                if (dropoffWrapper && differentDropoff) {
                    dropoffWrapper.style.display = 'none';
                    differentDropoff.checked = false;

                    // Resetear estado del dropoff
                    if (dropoffSelect) {
                        dropoffSelect.disabled = true;
                        dropoffSelect.required = false;
                        dropoffSelect.value = '';
                    }
                }

                // Limpiar errores
                document.querySelectorAll('.error-msg').forEach(el => el.remove());
                document.querySelectorAll('.field-error, .field-success').forEach(el => {
                    el.classList.remove('field-error', 'field-success');
                });

                // Limpiar altInputs de flatpickr
                const start = document.getElementById('start');
                const end = document.getElementById('end');

                if (start && start._flatpickr && start._flatpickr.altInput) {
                    start._flatpickr.altInput.classList.remove('field-error', 'field-success');
                }

                if (end && end._flatpickr && end._flatpickr.altInput) {
                    end._flatpickr.altInput.classList.remove('field-error', 'field-success');
                }
            }
        }
        console.log("Formulario valido:", valido);
    </script>

@endif
                {{-- ===================== STEP 2 ===================== --}}

                @if ($stepCurrent === 2)
                    <header class="wizard-head">
                        <h2>{{ __('Select your category') }}</h2>
                        <p>{{ __('Rate for') }} <strong id="daysLabel">{{ $days }}</strong> {{ __('day(s) of your rental.') }}</p>
                    </header>

                    <div class="cars">
                        @forelse(($categorias ?? []) as $cat)
                            @php
                                $imgCat = $catImages[$cat->id_categoria] ?? $placeholder;
                                $prepagoDia = (float) ($cat->precio_dia ?? 0);
                                $mostradorDia = round($prepagoDia * 1.15);
                                $prepagoTotal = $prepagoDia * $days;
                                $mostradorTotal = $mostradorDia * $days;

                                $predeterminadosPorId = [
                                    1 => ['pax' => 5, 'small' => 2, 'big' => 1], // ID de C
                                    2 => ['pax' => 5, 'small' => 2, 'big' => 1], // ID de D
                                    3 => ['pax' => 5, 'small' => 2, 'big' => 2], // ID de E
                                    4 => ['pax' => 5, 'small' => 2, 'big' => 2], // ID de F
                                    5 => ['pax' => 5, 'small' => 2, 'big' => 2], // ID de IC
                                    6 => ['pax' => 5, 'small' => 3, 'big' => 2], // ID de I
                                    7 => ['pax' => 7, 'small' => 3, 'big' => 2], // ID de IB
                                    8 => ['pax' => 7, 'small' => 4, 'big' => 2], // ID de M
                                    9 => ['pax' => 13, 'small' => 4, 'big' => 3], // ID de L
                                    10 => ['pax' => 5, 'small' => 3, 'big' => 2], // ID de H
                                    11 => ['pax' => 5, 'small' => 3, 'big' => 2], // ID de HI
                                ];

                                $idActual = $cat->id_categoria;
                                $paxFinal = 5;
                                $sFinal = 2;
                                $bFinal = 1; // Valores por defecto

                                if (isset($predeterminadosPorId[$idActual])) {
                                    $paxFinal = $predeterminadosPorId[$idActual]['pax'];
                                    $sFinal = $predeterminadosPorId[$idActual]['small'];
                                    $bFinal = $predeterminadosPorId[$idActual]['big'];
                                }

                                $appleCarplay = (int) ($cat->apple_carplay ?? 0);
                                $androidAuto = (int) ($cat->android_auto ?? 0);
                                $codigoCat = trim(strtoupper((string) ($cat->codigo ?? '')));
                                $transmision = $cat->id_categoria == 9 ? __('Manual') : __('Automatic');
                                $tieneACCat = (int) ($cat->aire_acondicionado ?? ($cat->aire_ac ?? 1));

                                // *** SOLO CAMBIAMOS ESTA LÍNEA ***
                                // Antes: $desc = $cat->ejemplo ?? ($cat->descripcion ?? 'Auto o similar.');
                                // Ahora: Usamos directamente la descripción de la categoría
                                $desc = $cat->descripcion ?? 'Car or similar.';

                                $ahorroPct =
                                    $mostradorTotal > 0
                                        ? round((($mostradorTotal - $prepagoTotal) / $mostradorTotal) * 100)
                                        : 0;
                            @endphp

                            <article
                                class="car-card car-card--v2 {{ (string) $categoriaId === (string) $cat->id_categoria ? 'active' : '' }}"
                                data-prepago-dia="{{ $prepagoDia }}" data-mostrador-dia="{{ $mostradorDia }}">

                                <div class="car-body">
                                    {{-- 1. Agrupamos el Título y el Badge en una nueva fila --}}
                                    <div class="car-header-row">
                                        <div class="car-top">{{ strtoupper($cat->nombre) }}</div>

                                        {{-- El badge ahora vive aquí arriba, al lado del título --}}
                                        <div class="car-days-badge car-days-badge--v2">
                                            <i class="fa-regular fa-calendar-days"></i>
                                            <span class="js-days-badge">{{ $days }}</span> {{ __('day(s)') }}
                                        </div>
                                    </div>

                                    {{-- SOLO CAMBIAMOS ESTA PARTE: mostramos la descripción completa --}}
                                    <div class="car-sub">{{ $desc }}</div>

                                    {{-- 2. El HERO ahora solo contiene la imagen --}}
                                    <div class="car-hero">
                                        <img class="car-hero-img" src="{{ $imgCat }}" alt="{{ $cat->nombre }}"
                                            onerror="this.onerror=null;this.src='{{ $placeholder }}';">
                                    </div>

                                    {{-- FEATURES --}}
                                    <div class="car-features">
                                        <ul class="car-mini-specs">
                                            <li title="{{ __('Passengers') }}">
                                                <i class="fa-solid fa-user-large"></i> {{ $paxFinal }}
                                            </li>
                                            <li title="{{ __('Small suitcases') }}">
                                                <i class="fa-solid fa-suitcase-rolling"></i> {{ $sFinal }}
                                            </li>
                                            <li title="{{ __('Large suitcases') }}">
                                                <i class="fa-solid fa-briefcase"></i> {{ $bFinal }}
                                            </li>
                                            <li title="{{ __('Transmission') }}">
                                                <span class="spec-letter">T | {{ $transmision }}</span>
                                            </li>
                                            @if ($tieneACCat)
                                                <li title="{{ __('Air conditioning') }}">
                                                    <i class="fa-regular fa-snowflake"></i>
                                                    <span class="spec-letter">A/C</span>
                                                </li>
                                            @endif
                                        </ul>

                                        <div class="car-connect">
                                            @if ($appleCarplay)
                                                <span class="badge-chip badge-apple" title="Apple CarPlay">
                                                    <span class="icon-badge"><i class="fa-brands fa-apple"></i></span>
                                                    CarPlay
                                                </span>
                                            @endif
                                            @if ($androidAuto)
                                                <span class="badge-chip badge-android" title="Android Auto">
                                                    <span class="icon-badge"><i class="fa-brands fa-android"></i></span>
                                                    Android Auto
                                                </span>
                                            @endif
                                        </div>
                                    </div>

                                    {{-- PRECIOS --}}
                                    <div class="car-price car-price--v2">
                                        <div class="price-old">
                                            ${{ number_format($mostradorTotal, 0) }} MXN
                                        </div>
                                        <div class="price-new">
                                            $<span class="js-prepago-total">{{ number_format($prepagoTotal, 0) }}</span>
                                            MXN
                                        </div>
                                        @if ($ahorroPct > 0)
                                            <div class="price-save">
                                                {{ __('Save') }} <strong class="js-ahorro">{{ $ahorroPct }}</strong>%
                                            </div>
                                        @endif
                                        <a class="btn-pay primary"
                                            href="{{ $toStep(3, ['categoria_id' => $cat->id_categoria, 'plan' => 'linea']) }}">
                                            {{ __('PREPAY ONLINE') }}
                                        </a>
                                        <div class="office-wrap">
                                            <div class="office-price">
                                                $<span
                                                    class="js-mostrador-total">{{ number_format($mostradorTotal, 0) }}</span>
                                                MXN
                                            </div>
                                            <a class="btn-pay gray"
                                                href="{{ $toStep(3, ['categoria_id' => $cat->id_categoria, 'plan' => 'mostrador']) }}">
                                                {{ __('PAY AT OFFICE') }}
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </article>
                        @empty
                            <p>{{ __('No categories available.') }}</p>
                        @endforelse
                    </div>

                    <div class="wizard-nav">
                        <a class="btn btn-ghost" href="{{ $toStep(1) }}">{{ __('Previous') }}</a>
                        <a class="btn btn-primary" href="{{ $toStep(3) }}">{{ __('Next') }}</a>
                    </div>
                @endif

                <script>
                console.log("STEP ACTUAL:", "{{ $stepCurrent }}");
                </script>
                {{-- ===================== STEP 3 ===================== --}}
                @if ($stepCurrent === 3)
                    <header class="wizard-head">
                        <h2>{{ __('Select the additional options you want') }}</h2>
                        <p>{{ __('Review included protections and add extra equipment/services.') }}</p>
                    </header>

                    <input type="hidden" id="addonsHidden" value="{{ $addonsParam }}">

                    <style>
                        .step3-wrap {
                            display: grid;
                            gap: 18px;
                        }

                        .step3-section {
                            background: #fff;
                            border: 1px solid #eef2f7;
                            border-radius: 18px;
                            padding: 16px;
                            box-shadow: 0 18px 40px rgba(15, 23, 42, .08);
                        }

                        .step3-title {
                            display: flex;
                            align-items: center;
                            gap: 10px;
                            font-weight: 900;
                            font-size: 18px;
                            margin: 0 0 14px;
                            color: #0f172a;
                        }

                        .step3-title small {
                            font-weight: 800;
                            font-size: 12px;
                            color: #6b7280;
                            letter-spacing: .4px;
                            text-transform: uppercase;
                        }

                        .step3-info {
                            margin-left: auto;
                            width: 30px;
                            height: 30px;
                            border-radius: 999px;
                            display: grid;
                            place-items: center;
                            background: rgba(178, 34, 34, .08);
                            color: var(--brand);
                            border: 1px solid rgba(178, 34, 34, .22);
                            cursor: pointer;
                        }

                        .prot-grid {
                            display: grid;
                            grid-template-columns: repeat(2, minmax(0, 1fr));
                            gap: 14px;
                            align-items: stretch;
                        }

                        @media (max-width:840px) {
                            .prot-grid {
                                grid-template-columns: 1fr;
                            }
                        }

                        .prot-card {
                            border: 1px dashed rgba(178, 34, 34, .20);
                            border-radius: 18px;
                            padding: 14px;
                            display: grid;
                            gap: 10px;
                            align-content: start;
                        }

                        .prot-top {
                            display: flex;
                            align-items: flex-start;
                            gap: 12px;
                        }

                        .prot-icon {
                            width: 70px;
                            height: 70px;
                            border-radius: 999px;
                            display: grid;
                            place-items: center;
                            border: 3px solid #d1d5db;
                            color: #9ca3af;
                            flex: 0 0 auto;
                        }

                        .prot-icon.is-on {
                            border-color: #16a34a;
                            color: #16a34a;
                        }

                        .prot-name {
                            font-weight: 900;
                            letter-spacing: .25px;
                            text-transform: uppercase;
                            font-size: 13px;
                            color: #0f172a;
                            margin: 0 0 6px;
                        }

                        .prot-desc {
                            margin: 0;
                            color: #475569;
                            font-weight: 700;
                            font-size: 13px;
                            line-height: 1.45;
                        }

                        .prot-badge {
                            display: inline-flex;
                            align-items: center;
                            gap: 8px;
                            font-weight: 900;
                            letter-spacing: .4px;
                            text-transform: uppercase;
                            font-size: 12px;
                            color: #0f172a;
                            margin-top: 10px;
                        }

                        .prot-badge .dot {
                            width: 10px;
                            height: 10px;
                            border-radius: 999px;
                            background: #16a34a;
                            box-shadow: 0 0 0 4px rgba(22, 163, 74, .12);
                        }

                        .equip-grid {
                            display: grid;
                            grid-template-columns: repeat(3, minmax(0, 1fr));
                            gap: 14px;
                        }

                        @media (max-width:980px) {
                            .equip-grid {
                                grid-template-columns: repeat(2, minmax(0, 1fr));
                            }
                        }

                        @media (max-width:620px) {
                            .equip-grid {
                                grid-template-columns: 1fr;
                            }
                        }

                        .addon-card {
                            border: 1px solid #eef2f7;
                            border-radius: 18px;
                            padding: 14px;
                            background: #fff;
                            box-shadow: 0 18px 40px rgba(15, 23, 42, .06);
                            display: grid;
                            gap: 10px;
                        }

                        .addon-top {
                            display: flex;
                            gap: 12px;
                            align-items: flex-start;
                        }

                        .addon-ico {
                            width: 70px;
                            height: 70px;
                            border-radius: 999px;
                            display: grid;
                            place-items: center;
                            border: 3px solid #d1d5db;
                            color: #6b7280;
                            flex: 0 0 auto;
                        }

                        .addon-name {
                            margin: 0;
                            font-weight: 900;
                            letter-spacing: .25px;
                            text-transform: uppercase;
                            font-size: 13px;
                            color: #0f172a;
                        }

                        .addon-card p {
                            margin: 6px 0 0;
                            color: #475569;
                            font-weight: 700;
                            font-size: 13px;
                            line-height: 1.45;
                        }

                        .addon-price {
                            font-weight: 900;
                            color: #0f172a;
                            font-size: 13px;
                        }

                        .addon-price strong {
                            color: var(--brand);
                        }

                        .addon-qty {
                            display: flex;
                            gap: 10px;
                            align-items: center;
                            justify-content: flex-start;
                            margin-top: 4px;
                        }

                        .qty-btn {
                            width: 42px;
                            height: 42px;
                            border-radius: 12px;
                            border: 1px solid #e5e7eb;
                            background: #fff;
                            font-weight: 900;
                            cursor: pointer;
                        }

                        .qty {
                            min-width: 34px;
                            text-align: center;
                            font-weight: 900;
                            color: #0f172a;
                        }

                        .qty-hint {
                            font-size: 12px;
                            font-weight: 800;
                            color: #6b7280;
                            margin-left: auto;
                        }

                        /* modal simple (step 3) */
                        .modal-s3 {
                            position: fixed;
                            inset: 0;
                            display: none;
                            align-items: center;
                            justify-content: center;
                            background: rgba(15, 23, 42, .55);
                            z-index: 999999;
                            padding: 18px;
                        }

                        .modal-s3 .card {
                            width: min(720px, 100%);
                            background: #fff;
                            border-radius: 18px;
                            border: 1px solid #eef2f7;
                            box-shadow: 0 25px 60px rgba(0, 0, 0, .25);
                            padding: 18px;
                        }

                        .modal-s3 .x {
                            width: 40px;
                            height: 40px;
                            border-radius: 12px;
                            border: 1px solid #e5e7eb;
                            background: #fff;
                            cursor: pointer;
                            display: grid;
                            place-items: center;
                            margin-left: auto;
                        }
                    </style>

                    <div class="step3-wrap">

                        {{-- Protecciones --}}
                        <section class="step3-section">
                            <div class="step3-title">
                                {{ __('Liability waivers (Protections)') }}
                                <button type="button" class="step3-info" id="info-protecciones-step3"
                                    title="{{ __('More information') }}">
                                    <i class="fa-solid fa-circle-info"></i>
                                </button>
                            </div>

                            <div class="prot-grid">
                                <div class="prot-card">
                                    <div class="prot-top">
                                        <div class="prot-icon is-on">
                                            <i class="fa-solid fa-shield"></i>
                                        </div>
                                        <div>
                                            <p class="prot-name">{{ __('Limited third-party liability protection (LI)') }}
                                            </p>
                                            <p class="prot-desc">
                                                {{ __('Protects third parties for damages and injuries caused in an accident and covers the minimum amount required by law.') }}
                                            </p>
                                            <div class="prot-badge"><span class="dot"></span> {{ __('Included') }}</div>
                                        </div>
                                    </div>
                                </div>

                                <div class="prot-card">
                                    <div class="prot-top">
                                        <div class="prot-icon">
                                            <i class="fa-solid fa-shield-halved"></i>
                                        </div>
                                        <div>
                                            <p class="prot-name">{{ __('Additional protections') }}</p>
                                            <p class="prot-desc">
                                                {{ __('You choose the level of liability for the vehicle that best fits your needs and budget. Ask about our waivers (optional) when you arrive at any of our branches.') }}
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div id="modalProteccionesStep3" class="modal-s3" aria-hidden="true">
                                <div class="card">

                                    <button type="button" class="x" id="closeProteccionesStep3"
                                        aria-label="{{ __('Close') }}">
                                        <i class="fa-solid fa-xmark"></i>
                                    </button>

                                    <h2 class="s3-modal-title">{{ __('Liability waivers (Protections)') }}</h2>
                                    <p class="s3-modal-sub">{{ __('Check the details of each package and what it includes.') }}</p>

                                    <div class="s3-body-scroll">
                                        <div class="s3-info-top">
                                            <p>
                                                <strong>{{ __('VIAJERO') }}</strong> {{ __('offers different types of optional Liability Waivers (Protections) available for an additional daily fee, which can be purchased when booking or on the rental day.') }}
                                            </p>

                                            <p>
                                                {{ __('The customer is responsible for any damage or theft of the VIAJERO vehicle subject to certain exclusions contained in the rental agreement. VIAJERO will waive or limit the customer\'s liability by purchasing any of these.') }}
                                            </p>

                                            <p style="margin-bottom:0;">
                                                {{ __('Customers booking using their Wizard Number will see the coverage and insurance preferences selected in their profile. You can also visit a branch or call') }} <strong>01 (442) 303 2668</strong> {{ __('for assistance.') }}
                                            </p>
                                        </div>

                                        {{-- LDW PACK --}}
                                        <details class="s3-acc-item">
                                            <summary class="s3-acc-sum">
                                                <span class="s3-acc-left">
                                                    <span class="s3-acc-badge">LDW</span>
                                                    <span class="s3-acc-name">LDW PACK</span>
                                                </span>
                                                <i class="fa-solid fa-chevron-down s3-acc-caret" aria-hidden="true"></i>
                                            </summary>

                                            <div class="s3-acc-body">
                                                <ul class="s3-list">
                                                    <li><strong>LDW:</strong> {{ __('The customer is responsible for 0% deductible, bumper to bumper coverage no matter what happens to the car.') }}</li>
                                                    <li><strong>PAI:</strong> {{ __('Medical expenses covered') }} <strong>$250,000 MXN</strong> {{ __('per event.') }}</li>
                                                    <li><strong>PRA:</strong> {{ __('Premium roadside assistance. Includes: key or fuel delivery, car unlocking, flat tire change and jump start. Does not include key or fuel cost.') }}</li>
                                                    <li><strong>LOU:</strong> {{ __('Loss of use, covered.') }}</li>
                                                    <li><strong>LA:</strong> {{ __('Legal assistance, covered.') }}</li>
                                                    <li><strong>LI:</strong> {{ __('Liability insurance up to') }} <strong>$3,000,000 MXN</strong>.</li>
                                                </ul>
                                            </div>
                                        </details>

                                        {{-- PDW PACK --}}
                                        <details class="s3-acc-item">
                                            <summary class="s3-acc-sum">
                                                <span class="s3-acc-left">
                                                    <span class="s3-acc-badge">PDW</span>
                                                    <span class="s3-acc-name">PDW PACK</span>
                                                </span>
                                                <i class="fa-solid fa-chevron-down s3-acc-caret" aria-hidden="true"></i>
                                            </summary>

                                            <div class="s3-acc-body">
                                                <ul class="s3-list">
                                                    <li><strong>PDW:</strong> {{ __('Covers the entire bodywork at 5%, 10% for total loss or theft. Does not cover tires, accessories, rims or windows.') }}</li>
                                                    <li><strong>PAI:</strong> {{ __('Medical expenses covered') }} <strong>$250,000 MXN</strong> {{ __('per event.') }}</li>
                                                    <li><strong>PRA (DECLINED):</strong> {{ __('Premium Assistance: the customer is responsible for costs of: tow truck (if needed), impound lot, key or fuel delivery, car unlocking, flat tire change and jump start.') }}</li>
                                                    <li><strong>LOU:</strong> {{ __('Loss of use, covered.') }}</li>
                                                    <li><strong>LA:</strong> {{ __('Legal assistance, covered.') }}</li>
                                                    <li><strong>ALI:</strong> {{ __('Liability insurance up to') }} <strong>$1,000,000 MXN</strong>.</li>
                                                </ul>
                                            </div>
                                        </details>

                                        {{-- CDW PACK 1 --}}
                                        <details class="s3-acc-item">
                                            <summary class="s3-acc-sum">
                                                <span class="s3-acc-left">
                                                    <span class="s3-acc-badge">CDW</span>
                                                    <span class="s3-acc-name">CDW PACK 1</span>
                                                </span>
                                                <i class="fa-solid fa-chevron-down s3-acc-caret" aria-hidden="true"></i>
                                            </summary>

                                            <div class="s3-acc-body">
                                                <ul class="s3-list">
                                                    <li><strong>CDW 10%:</strong> {{ __('The customer is responsible for 10% deductible on damages, 20% for total loss or theft based on invoice value.') }}</li>
                                                    <li><strong>PAI:</strong> {{ __('Medical expenses covered') }} <strong>$250,000 MXN</strong> {{ __('per event.') }}</li>
                                                    <li><strong>PRA (DECLINED):</strong> {{ __('Premium Assistance: the customer is responsible for costs of: tow truck (if needed), impound lot, key or fuel delivery, car unlocking, flat tire change and jump start.') }}</li>
                                                    <li><strong>LOU:</strong> {{ __('Loss of use, covered.') }}</li>
                                                    <li><strong>LA:</strong> {{ __('Legal assistance, covered.') }}</li>
                                                    <li><strong>ALI:</strong> {{ __('Liability insurance up to') }} <strong>$1,000,000 MXN</strong>.</li>
                                                </ul>
                                            </div>
                                        </details>

                                        {{-- CDW PACK 2 --}}
                                        <details class="s3-acc-item">
                                            <summary class="s3-acc-sum">
                                                <span class="s3-acc-left">
                                                    <span class="s3-acc-badge">CDW</span>
                                                    <span class="s3-acc-name">CDW PACK 2</span>
                                                </span>
                                                <i class="fa-solid fa-chevron-down s3-acc-caret" aria-hidden="true"></i>
                                            </summary>

                                            <div class="s3-acc-body">
                                                <ul class="s3-list">
                                                    <li><strong>CDW 20%:</strong> {{ __('The customer is responsible for 20% deductible on damages, 30% for total loss or theft based on invoice value.') }}</li>
                                                    <li><strong>PAI:</strong> {{ __('Medical expenses covered') }} <strong>$250,000 MXN</strong> {{ __('per event.') }}</li>
                                                    <li><strong>PRA (DECLINED):</strong> {{ __('Premium Assistance: the customer is responsible for costs of: tow truck (if needed), impound lot, key or fuel delivery, car unlocking, flat tire change and jump start.') }}</li>
                                                    <li><strong>LOU:</strong> {{ __('Loss of use, covered.') }}</li>
                                                    <li><strong>LA:</strong> {{ __('Legal assistance, covered.') }}</li>
                                                    <li><strong>LI:</strong> {{ __('Liability insurance up to') }} <strong>$350,000 MXN</strong>.</li>
                                                </ul>
                                            </div>
                                        </details>
                                    </div>
                                </div>
                            </div>

                            <script>
                                document.addEventListener('DOMContentLoaded', () => {
                                    const openBtn = document.getElementById('info-protecciones-step3');
                                    const modal = document.getElementById('modalProteccionesStep3');
                                    const closeBtn = document.getElementById('closeProteccionesStep3');

                                    if (openBtn && modal) {
                                        openBtn.addEventListener('click', () => {
                                            modal.style.display = 'flex';
                                        });
                                    }
                                    if (closeBtn && modal) {
                                        closeBtn.addEventListener('click', () => {
                                            modal.style.display = 'none';
                                        });
                                    }
                                    if (modal) {
                                        modal.addEventListener('click', (e) => {
                                            if (e.target === modal) modal.style.display = 'none';
                                        });
                                    }
                                });
                            </script>
                        </section>
@php
    // Mapeo de traducciones para nombres de servicios
    $serviciosNombres = [
        'Silla de bebé' => __('Baby seat'),
        'Gasolina Prepago' => __('Prepaid fuel'),
        'Conductor adicional' => __('Additional driver'),
    ];

    // Mapeo de traducciones para descripciones de servicios
    $serviciosDescripciones = [
        'Silla de seguridad para bebé.' => __('Baby safety seat.'),
        'Tanque completo de capacidad por categoria.' => __('Full tank based on vehicle category.'),
        'Agregar un conductor extra.' => __('Add an extra driver.'),
    ];
@endphp
                        {{-- Equipamiento & Servicios --}}
                        <section class="step3-section">
                            <div class="step3-title">
                                {{ __('Equipment & Services') }}
                                <small>{{ __('max 3 per option') }}</small>
                            </div>

                            <div class="equip-grid">
                                @forelse($serviciosFiltrados as $srv)
                                    @php
                                        $unidad = $srv->tipo_cobro === 'por_tanque' ? ' / tank' : ' / event';
                                        $precio = number_format((float) $srv->precio, 0);

                                        $n = mb_strtolower(trim((string) ($srv->nombre ?? '')));
                                        $icon = 'fa-solid fa-circle-plus';
                                        $tooltipText = __('Check more information about this add-on.');

                                        if (str_contains($n, 'silla')) {
                                            $icon = 'fa-solid fa-child-reaching';
                                            $tooltipText = __('Ideal for traveling with children. Subject to availability at the time of delivery.');
                                        } elseif (str_contains($n, 'conductor')) {
                                            $icon = 'fa-solid fa-user-plus';
                                            $tooltipText = __('Add an additional authorized driver to operate the vehicle during the rental.');
                                        } elseif (str_contains($n, 'gasolina')) {
                                            $icon = 'fa-solid fa-gas-pump';
                                            $tooltipText = __('Early flight? Don\'t waste time looking for a gas station. With Viajero Car Rental, you can prepay your fuel at a preferred rate per liter and return the vehicle directly. Simple, fast and stress-free.');
                                        }
                                    @endphp
                                    <div class="addon-card" data-id="{{ $srv->id_servicio }}"
                                        data-name="{{ $srv->nombre }}" data-price="{{ (float) $srv->precio }}"
                                        data-gasolina="{{ str_contains(strtolower($srv->nombre),'gasolina') ? 1 : 0 }}"
                                        data-charge="{{ $srv->tipo_cobro }}" data-max="3">
                                        <div class="addon-top">
                                            <div class="addon-ico">
                                                <i class="{{ $icon }}"></i>
                                            </div>

                                            <div style="flex:1;">
                                                <div class="addon-headline">
                                                  <h4 class="addon-name">{{ $serviciosNombres[$srv->nombre] ?? $srv->nombre }}</h4>

                                                    <span class="addon-help-wrap" tabindex="0">
                                                        <button type="button" class="addon-help-btn" aria-label="{{ __('More information') }}">
                                                            <i class="fa-solid fa-info"></i>
                                                        </button>
                                                        <span class="addon-tooltip">{{ $tooltipText }}</span>
                                                    </span>
                                                </div>

                                              @if (!empty($srv->descripcion))
    <p>{{ $serviciosDescripciones[$srv->descripcion] ?? $srv->descripcion }}</p>
@endif
                                            </div>
                                        </div>

                                        @php
    $unidad = match($srv->tipo_cobro) {
        'por_tanque' => __(' / tank'),
        'por_dia' => __(' / day'),
        default => __(' / event'),
    };
@endphp

<div class="addon-price">
    @if(str_contains(strtolower($srv->nombre), 'gasolina'))

        @php
            $totalGasolina = $capacidadTanque * $srv->precio;
        @endphp

        <strong>${{ number_format($totalGasolina, 0) }}</strong> MXN / {{ __('tank') }}

    @elseif(str_contains(strtolower($srv->nombre), 'conductor'))

        <strong>${{ $precio }}</strong> MXN / {{ __('driver per day') }}

    @else

        <strong>${{ $precio }}</strong> MXN {{ $unidad }}

    @endif
</div>


@if($srv->id_servicio == 1)

<div class="addon-qty gasolina-toggle">
    <label class="switch">
        <input type="checkbox" class="gasolina-switch">
        <span class="slider"></span>
    </label>
</div>

@else
<div class="addon-qty">
    <button class="qty-btn minus" type="button">−</button>
    <span class="qty">0</span>
    <button class="qty-btn plus" type="button">+</button>
    <span class="qty-hint">{{ __('Max 3') }}</span>
</div>

@endif

<style>
    .switch {
  position: relative;
  display: inline-block;
  width: 46px;
  height: 26px;
}

.switch input {
  opacity: 0;
  width: 0;
  height: 0;
}

.slider {
  position: absolute;
  cursor: pointer;
  inset: 0;
  background-color: #ccc;
  transition: .3s;
  border-radius: 26px;
}

.slider:before {
  position: absolute;
  content: "";
  height: 20px;
  width: 20px;
  left: 3px;
  bottom: 3px;
  background-color: white;
  transition: .3s;
  border-radius: 50%;
}

input:checked + .slider {
  background-color: #16a34a;
}

input:checked + .slider:before {
  transform: translateX(20px);
}

input:checked + .slider:before {
  transform: translateX(20px);
}
</style>
                                    </div>
                                @empty
                                    <div style="grid-column:1/-1; text-align:center; padding:.75rem 0;">
                                        {{ __('No add-ons available at the moment.') }}
                                    </div>
                                @endforelse
                            </div>
                        </section>

                    </div>

                    <div class="wizard-nav">
                        <a class="btn btn-ghost" href="{{ $toStep(2) }}">{{ __('Previous') }}</a>
                        <a class="btn btn-primary" id="toStep4" href="{{ $toStep(4) }}">{{ __('Next') }}</a>
                    </div>
                @endif

                {{-- ===================== STEP 4 ===================== --}}
                @if ($stepCurrent === 4)
                    <input type="hidden" id="addonsHidden" value="{{ $addonsParam }}">

                    <style>
                        .sum-line-title {
                            position: relative;
                            display: flex;
                            align-items: center;
                            gap: 10px;
                            font-weight: 900;
                            letter-spacing: .35px;
                            text-transform: uppercase;
                            font-size: 13px;
                            color: #0f172a;
                            margin: 0 0 10px;
                        }

                        .sum-line-title:after {
                            content: "";
                            height: 3px;
                            flex: 1;
                            border-radius: 999px;
                            background: linear-gradient(90deg, rgba(178, 34, 34, 1), rgba(178, 34, 34, .15));
                        }

                        .sum-dt2 {
                            display: flex;
                            flex-direction: column;
                            gap: 4px;
                            margin-top: 4px;
                        }

                        .sum-dt2 .dt-row {
                            display: flex;
                            gap: 8px;
                            align-items: baseline;
                        }

                        .sum-dt2 .dt-lbl {
                            min-width: 58px;
                            font-size: 11px;
                            font-weight: 900;
                            letter-spacing: .55px;
                            text-transform: uppercase;
                            color: #6b7280;
                        }

                        .sum-dt2 .dt-val {
                            font-weight: 800;
                            color: #111827;
                            line-height: 1.15;
                        }

                        .sum-dt2 .dt-time {
                            font-weight: 900;
                            color: #111827;
                        }

                        /* DISEÑO RESPONSIVO: TARJETA DE RESERVACIÓN - SOLO MÓVIL/TABLET */
@media (max-width:1024px) {
    footer,
    .footer-elegant {
        position: relative;
        z-index: 10;
        background-color: #0b1120 !important;
    }

    .step4-pane .sum-total,
    .sum-form .wizard-nav,
    #btnReservar {
        display: none !important;
    }

    /* ESTILO BASE DE LA TARJETA - INICIALMENTE OCULTA */
    .movil-footer-sticky {
        display: flex !important;
        flex-direction: column;
        position: fixed;
        bottom: 0;
        left: 0;
        width: 100%;
        background: #ffffff;
        padding: 20px;
        box-shadow: 0 -10px 40px rgba(0, 0, 0, 0.20);
        border-radius: 25px 25px 0 0;
        gap: 15px;
        z-index: 9999999;
        transform: translateY(100%);
        transition: transform 0.3s ease-in-out;
    }

    /* CUANDO TIENE LA CLASE 'visible' - SE MUESTRA */
    .movil-footer-sticky.visible {
        transform: translateY(0);
    }

    .movil-total-wrapper {
        display: flex;
        justify-content: space-between;
        align-items: center;
        font-weight: 900;
        font-size: 20px;
    }

    .movil-total-label {
        color: #000;
        text-transform: uppercase;
    }

    .movil-total-amount {
        color: #b22222;
        font-size: 24px;
    }

    .btn-reservar-movil {
        background: #b22222;
        color: #fff;
        border: none;
        border-radius: 14px;
        padding: 20px;
        font-size: 18px;
        font-weight: 900;
        width: 100%;
        text-transform: uppercase;
        letter-spacing: 1px;
        cursor: pointer;
        transition: all .2s ease;
    }

    .btn-reservar-movil:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 20px rgba(178, 34, 34, .35);
    }

    /* AJUSTE PARA EL SCROLL */
    .step4-layout {
        padding-bottom: 250px !important;
    }
}

/* ESCRITORIO - OCULTA COMPLETAMENTE LA TARJETA */
@media (min-width:1025px) {
    .movil-footer-sticky {
        display: none !important;
    }
}
                    </style>

                    <div class="step4-layout">
                        @php
                            $months3 = [
                                '01' => 'JAN',
                                '02' => 'FEB',
                                '03' => 'MAR',
                                '04' => 'APR',
                                '05' => 'MAY',
                                '06' => 'JUN',
                                '07' => 'JUL',
                                '08' => 'AUG',
                                '09' => 'SEP',
                                '10' => 'OCT',
                                '11' => 'NOV',
                                '12' => 'DEC',
                            ];

                            // Si no tienes definidas estas variables
                            $maxYear = date('Y') - 18;
                            $minYear = $maxYear - 80;
                        @endphp

                        {{-- ===================== PANE IZQUIERDO ===================== --}}
                        <div class="step4-pane">
                            <form class="sum-form" id="formCotizacion" onsubmit="return false;" novalidate>
                                <script>
                                    window.APP_URL_RESERVA_MOSTRADOR = "{{ route('reservas.store') }}";
                                    window.APP_URL_RESERVA_LINEA = "{{ route('reservas.linea') }}";
                                </script>

                                @csrf

                                <input type="hidden" name="categoria_id" id="categoria_id"
                                    value="{{ $categoriaId }}">
                                <input type="hidden" name="plan" id="plan" value="{{ $plan }}">
                                <input type="hidden" name="addons" id="addons_payload" value="{{ $addonsParam }}">

                                {{-- 🔹 Campos ocultos para que el JS pueda mandar todo a /reservas/linea --}}
                                <input type="hidden" name="pickup_date" id="pickup_date" value="{{ $pickupDateISO }}">
                                <input type="hidden" name="pickup_time" id="pickup_time" value="{{ $pickupTime }}">
                                <input type="hidden" name="dropoff_date" id="dropoff_date"
                                    value="{{ $dropoffDateISO }}">
                                <input type="hidden" name="dropoff_time" id="dropoff_time"
                                    value="{{ $dropoffTime }}">

                                <input type="hidden" name="pickup_sucursal_id" id="pickup_sucursal_id"
                                    value="{{ $pickupSucursalId }}">
                                <input type="hidden" name="dropoff_sucursal_id" id="dropoff_sucursal_id"
                                    value="{{ $dropoffSucursalId }}">

                                <h2 class="sum-section-title">{{ __('Personal information') }}</h2>

                                <div class="sum-personal-grid">

                                    {{-- Nombre Completo --}}
                                    <div class="field field-floating" style="grid-column: 1 / -1;">
                                        <input type="text" class="input-centered" name="nombre_completo"
                                            id="nombreCompleto" autocomplete="name" placeholder=" " required>
                                        <label for="nombreCompleto">{{ __('Full name') }}</label>
                                        <input type="hidden" name="nombre" id="nombreCliente">
                                        <input type="hidden" name="apellido" id="apellidoCliente">
                                    </div>

                                    {{-- Móvil --}}
                                    <div class="field field-floating" style="grid-column: 1 / -1;">
                                        <input type="text" name="telefono" id="telefonoCliente" placeholder=" "
                                            required>
                                        <label for="telefonoCliente">{{ __('Mobile') }}</label>
                                    </div>

                                    {{-- Correo electrónico --}}
                                    <div class="field field-floating" style="grid-column: 1 / -1;">
                                        <input type="email" name="email" id="correoCliente" placeholder=" "
                                            required>
                                        <label for="correoCliente">{{ __('Email address') }}</label>
                                    </div>

                                    {{-- País --}}
                                   @php
                                $paisesPrioritarios = [
                                    'México' => 'Mexico',
                                    'Estados Unidos' => 'United States',
                                    'Canadá' => 'Canada'
                                ];

                                $todosPaises = [
                                    'Afganistán' => 'Afghanistan',
                                    'Albania' => 'Albania',
                                    'Alemania' => 'Germany',
                                    'Andorra' => 'Andorra',
                                    'Angola' => 'Angola',
                                    'Antigua y Barbuda' => 'Antigua and Barbuda',
                                    'Arabia Saudita' => 'Saudi Arabia',
                                    'Argelia' => 'Algeria',
                                    'Argentina' => 'Argentina',
                                    'Armenia' => 'Armenia',
                                    'Australia' => 'Australia',
                                    'Austria' => 'Austria',
                                    'Azerbaiyán' => 'Azerbaijan',
                                    'Bahamas' => 'Bahamas',
                                    'Bangladés' => 'Bangladesh',
                                    'Barbados' => 'Barbados',
                                    'Baréin' => 'Bahrain',
                                    'Bélgica' => 'Belgium',
                                    'Belice' => 'Belize',
                                    'Benín' => 'Benin',
                                    'Bielorrusia' => 'Belarus',
                                    'Birmania' => 'Myanmar',
                                    'Bolivia' => 'Bolivia',
                                    'Bosnia y Herzegovina' => 'Bosnia and Herzegovina',
                                    'Botsuana' => 'Botswana',
                                    'Brasil' => 'Brazil',
                                    'Brunéi' => 'Brunei',
                                    'Bulgaria' => 'Bulgaria',
                                    'Burkina Faso' => 'Burkina Faso',
                                    'Burundi' => 'Burundi',
                                    'Bután' => 'Bhutan',
                                    'Cabo Verde' => 'Cape Verde',
                                    'Camboya' => 'Cambodia',
                                    'Camerún' => 'Cameroon',
                                    'Chad' => 'Chad',
                                    'Chile' => 'Chile',
                                    'China' => 'China',
                                    'Chipre' => 'Cyprus',
                                    'Colombia' => 'Colombia',
                                    'Comoras' => 'Comoros',
                                    'Costa de Marfil' => 'Ivory Coast',
                                    'Costa Rica' => 'Costa Rica',
                                    'Croacia' => 'Croatia',
                                    'Cuba' => 'Cuba',
                                    'Dinamarca' => 'Denmark',
                                    'Dominica' => 'Dominica',
                                    'Ecuador' => 'Ecuador',
                                    'Egipto' => 'Egypt',
                                    'El Salvador' => 'El Salvador',
                                    'Emiratos Árabes Unidos' => 'United Arab Emirates',
                                    'Eritrea' => 'Eritrea',
                                    'Eslovaquia' => 'Slovakia',
                                    'Eslovenia' => 'Slovenia',
                                    'España' => 'Spain',
                                    'Estonia' => 'Estonia',
                                    'Etiopía' => 'Ethiopia',
                                    'Filipinas' => 'Philippines',
                                    'Finlandia' => 'Finland',
                                    'Fiyi' => 'Fiji',
                                    'Francia' => 'France',
                                    'Gabón' => 'Gabon',
                                    'Gambia' => 'Gambia',
                                    'Georgia' => 'Georgia',
                                    'Ghana' => 'Ghana',
                                    'Granada' => 'Grenada',
                                    'Grecia' => 'Greece',
                                    'Guatemala' => 'Guatemala',
                                    'Guyana' => 'Guyana',
                                    'Guinea' => 'Guinea',
                                    'Guinea-Bisáu' => 'Guinea-Bissau',
                                    'Guinea Ecuatorial' => 'Equatorial Guinea',
                                    'Haití' => 'Haiti',
                                    'Honduras' => 'Honduras',
                                    'Hungría' => 'Hungary',
                                    'India' => 'India',
                                    'Indonesia' => 'Indonesia',
                                    'Irak' => 'Iraq',
                                    'Irán' => 'Iran',
                                    'Irlanda' => 'Ireland',
                                    'Islandia' => 'Iceland',
                                    'Islas Marshall' => 'Marshall Islands',
                                    'Islas Salomón' => 'Solomon Islands',
                                    'Israel' => 'Israel',
                                    'Italia' => 'Italy',
                                    'Jamaica' => 'Jamaica',
                                    'Japón' => 'Japan',
                                    'Jordania' => 'Jordan',
                                    'Kazajistán' => 'Kazakhstan',
                                    'Kenia' => 'Kenya',
                                    'Kirguistán' => 'Kyrgyzstan',
                                    'Kiribati' => 'Kiribati',
                                    'Kuwait' => 'Kuwait',
                                    'Laos' => 'Laos',
                                    'Lesoto' => 'Lesotho',
                                    'Letonia' => 'Latvia',
                                    'Líbano' => 'Lebanon',
                                    'Liberia' => 'Liberia',
                                    'Libia' => 'Libya',
                                    'Liechtenstein' => 'Liechtenstein',
                                    'Lituania' => 'Lithuania',
                                    'Luxemburgo' => 'Luxembourg',
                                    'Madagascar' => 'Madagascar',
                                    'Malasia' => 'Malaysia',
                                    'Malaui' => 'Malawi',
                                    'Maldivas' => 'Maldives',
                                    'Malí' => 'Mali',
                                    'Malta' => 'Malta',
                                    'Marruecos' => 'Morocco',
                                    'Mauricio' => 'Mauritius',
                                    'Mauritania' => 'Mauritania',
                                    'Micronesia' => 'Micronesia',
                                    'Moldavia' => 'Moldova',
                                    'Mónaco' => 'Monaco',
                                    'Mongolia' => 'Mongolia',
                                    'Montenegro' => 'Montenegro',
                                    'Mozambique' => 'Mozambique',
                                    'Namibia' => 'Namibia',
                                    'Nauru' => 'Nauru',
                                    'Nepal' => 'Nepal',
                                    'Nicaragua' => 'Nicaragua',
                                    'Níger' => 'Niger',
                                    'Nigeria' => 'Nigeria',
                                    'Noruega' => 'Norway',
                                    'Nueva Zelanda' => 'New Zealand',
                                    'Omán' => 'Oman',
                                    'Países Bajos' => 'Netherlands',
                                    'Pakistán' => 'Pakistan',
                                    'Palaos' => 'Palau',
                                    'Palestina' => 'Palestine',
                                    'Panamá' => 'Panama',
                                    'Papúa Nueva Guinea' => 'Papua New Guinea',
                                    'Paraguay' => 'Paraguay',
                                    'Perú' => 'Peru',
                                    'Polonia' => 'Poland',
                                    'Portugal' => 'Portugal',
                                    'Qatar' => 'Qatar',
                                    'Reino Unido' => 'United Kingdom',
                                    'República Centroafricana' => 'Central African Republic',
                                    'República Checa' => 'Czech Republic',
                                    'República del Congo' => 'Republic of the Congo',
                                    'República Democrática del Congo' => 'Democratic Republic of the Congo',
                                    'República Dominicana' => 'Dominican Republic',
                                    'Ruanda' => 'Rwanda',
                                    'Rumanía' => 'Romania',
                                    'Rusia' => 'Russia',
                                    'Samoa' => 'Samoa',
                                    'San Cristóbal y Nieves' => 'Saint Kitts and Nevis',
                                    'San Marino' => 'San Marino',
                                    'San Vicente y las Granadinas' => 'Saint Vincent and the Grenadines',
                                    'Santa Lucía' => 'Saint Lucia',
                                    'Santo Tomé y Príncipe' => 'Sao Tome and Principe',
                                    'Senegal' => 'Senegal',
                                    'Serbia' => 'Serbia',
                                    'Seychelles' => 'Seychelles',
                                    'Sierra Leona' => 'Sierra Leone',
                                    'Singapur' => 'Singapore',
                                    'Siria' => 'Syria',
                                    'Somalia' => 'Somalia',
                                    'Sri Lanka' => 'Sri Lanka',
                                    'Suazilandia' => 'Eswatini',
                                    'Sudáfrica' => 'South Africa',
                                    'Sudán' => 'Sudan',
                                    'Sudán del Sur' => 'South Sudan',
                                    'Suecia' => 'Sweden',
                                    'Suiza' => 'Switzerland',
                                    'Surinam' => 'Suriname',
                                    'Tailandia' => 'Thailand',
                                    'Tanzania' => 'Tanzania',
                                    'Tayikistán' => 'Tajikistan',
                                    'Timor Oriental' => 'East Timor',
                                    'Togo' => 'Togo',
                                    'Tonga' => 'Tonga',
                                    'Trinidad y Tobago' => 'Trinidad and Tobago',
                                    'Túnez' => 'Tunisia',
                                    'Turkmenistán' => 'Turkmenistan',
                                    'Turquía' => 'Turkey',
                                    'Tuvalu' => 'Tuvalu',
                                    'Ucrania' => 'Ukraine',
                                    'Uganda' => 'Uganda',
                                    'Uruguay' => 'Uruguay',
                                    'Uzbekistán' => 'Uzbekistan',
                                    'Vanuatu' => 'Vanuatu',
                                    'Vaticano' => 'Vatican City',
                                    'Venezuela' => 'Venezuela',
                                    'Vietnam' => 'Vietnam',
                                    'Yemen' => 'Yemen',
                                    'Yibuti' => 'Djibouti',
                                    'Zambia' => 'Zambia',
                                    'Zimbabue' => 'Zimbabwe'
                                ];

                                // Ordenar alfabéticamente
                                ksort($todosPaises);
                            @endphp

                            {{-- País --}}
                            <div class="field field-floating">
                                <select name="pais" id="pais" required>
                                    <option value="" disabled selected>{{ __('Select a country') }}</option>

                                    {{-- Países prioritarios --}}
                                    @foreach($paisesPrioritarios as $valor => $etiqueta)
                                        <option value="{{ $valor }}">{{ $etiqueta }}</option>
                                    @endforeach

                                    <option disabled>──────────</option>

                                    {{-- Resto de países --}}
                                    @foreach($todosPaises as $valor => $etiqueta)
                                        <option value="{{ $valor }}">{{ $etiqueta }}</option>
                                    @endforeach
                                </select>
                                <label for="pais">{{ __('Country') }}</label>
                            </div>

                                    {{-- Fecha de nacimiento --}}
                                    <div class="field field-dob-container">
                                        <label class="label-dob-main">{{ __('Date of birth') }}</label> {{-- ESTE LABEL ES IMPORTANTE --}}
                                        <div class="dob-inline">
                                            <div class="field-floating-sub">
                                                <select id="dob_day" class="select-dob" required>
                                                    <option value="" disabled selected hidden></option>
                                                    @for ($d = 1; $d <= 31; $d++)
                                                        <option value="{{ str_pad($d, 2, '0', STR_PAD_LEFT) }}">
                                                            {{ str_pad($d, 2, '0', STR_PAD_LEFT) }}</option>
                                                    @endfor
                                                </select>
                                                <label>DD</label>
                                            </div>
                                            <div class="field-floating-sub">
                                                <select id="dob_month" class="select-dob" required>
                                                    <option value="" disabled selected hidden></option>
                                                    @foreach ($months3 as $val => $label)
                                                        <option value="{{ $val }}">{{ $label }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                                <label>MM</label>
                                            </div>
                                            <div class="field-floating-sub">
                                                <select id="dob_year" class="select-dob" required>
                                                    <option value="" disabled selected hidden></option>
                                                    @for ($y = $maxYear; $y >= $minYear; $y--)
                                                        <option value="{{ $y }}">{{ $y }}
                                                        </option>
                                                    @endfor
                                                </select>
                                                <label>YYYY</label>
                                            </div>
                                        </div>
                                        <input type="hidden" id="dob" name="dob">
                                    </div>

                                    @php
                                        $isAirport =
                                            (is_string($pickupName) &&
                                                str_contains(mb_strtolower($pickupName), 'aeropuerto')) ||
                                            (is_string($dropoffName) &&
                                                str_contains(mb_strtolower($dropoffName), 'aeropuerto'));
                                    @endphp

                                    @if ($isAirport)
                                        <div class="field field-floating" style="grid-column: 1 / -1;">
                                            <input type="text" name="vuelo" id="vuelo" placeholder=" ">
                                            <label for="vuelo">{{ __('Flight number') }}</label>
                                        </div>
                                    @endif

                                </div>

                                <div class="sum-checks">
                                    <label class="cbox">
                                        <input type="checkbox" name="acepto" id="acepto" checked>
                                        <span>
                                            {{ __('I AGREE AND ACCEPT') }}
                                            <a href="{{ route('rutaPoliticas') }}" class="link-politicas"
                                                target="_blank" rel="noopener">
                                                {{ __('THE POLICIES') }}
                                            </a>
                                            {{ __('AND PROCEDURES FOR THE RENTAL.') }}
                                        </span>
                                    </label>

                                    <label class="cbox">
                                        <input type="checkbox" name="promos" id="promos">
                                        <span>{{ __('I WANT TO RECEIVE ALERTS, CONFIRMATIONS, OFFERS AND PROMOTIONS ON MY EMAIL AND/OR MOBILE PHONE.') }}</span>
                                    </label>
                                </div>

                                <div class="wizard-nav" style="margin-top:10px;">
                                    <button id="btnReservar" type="button" class="btn btn-primary">{{ __('Book') }}</button>
                                </div>

                                <div class="pay-logos"
                                    style="display: flex; justify-content: center; gap: 40px; align-items: center; flex-wrap: wrap; margin-top: 20px;">
                                    <img src="{{ asset('img/american.png') }}" alt="Amex"
                                        onerror="this.style.display='none'" style="height: 30px; object-fit: contain;">
                                    <img src="{{ asset('img/paypal.png') }}" alt="PayPal"
                                        onerror="this.style.display='none'" style="height: 30px; object-fit: contain;">
                                    <img src="{{ asset('img/oxxo.png') }}" alt="Oxxo"
                                        onerror="this.style.display='none'" style="height: 30px; object-fit: contain;">
                                </div>

                                <div id="modalMetodoPago" class="modal-overlay" style="display:none;">
                                    <div class="modal-card modal-metodo-pago">
                                        <button id="cerrarModalMetodoX" class="modal-close" type="button" aria-label="{{ __('Close') }}">×</button>

                                        <div class="mp-head">
                                            <span class="mp-badge">{{ __('Payment summary') }}</span>
                                            <h3>{{ __('Select your payment method') }}</h3>
                                        </div>

                                        <div class="mp-options">
                                            <button id="btnPagoLinea" class="mp-pay-card is-online" type="button">
                                                <span class="mp-old-price" id="mpPrecioMostradorTachado">$0 MXN</span>
                                                <strong class="mp-price" id="mpPrecioLinea">$0 MXN</strong>
                                                <span class="mp-action">{{ __('PREPAY ONLINE') }}</span>
                                            </button>

                                            <button id="btnPagoMostrador" class="mp-pay-card is-office" type="button">
                                                <strong class="mp-price" id="mpPrecioMostrador">$0 MXN</strong>
                                                <span class="mp-action">{{ __('PAY AT OFFICE') }}</span>
                                            </button>
                                        </div>
                                    </div>
                                </div>


                            </form>
                        </div>

                        {{-- ===================== PANE DERECHO ===================== --}}
                        <div class="step4-pane">

                            <div class="sum-compact" aria-label="{{ __('Compact summary') }}">
                                <div class="sum-compact-head">
                                    <h4 class="sum-title"><strong>{{ __('Booking summary') }}</strong></h4>

                                    <span class="sum-days">
                                        <i class="fa-regular fa-calendar"></i>
                                        Días: <strong>{{ $days }}</strong>
                                    </span>
                                </div>

                                <h4 class="sum-subtitle">{{ __('Location and date') }}</h4>

                                <div class="sum-compact-grid">

                                    {{-- PICKUP --}}
                                    <div class="sum-item">
                                        <div class="sum-item-label">
                                            <i class="fa-solid fa-location-dot"></i> {{ __('Pick-up') }}
                                        </div>

                                        <div class="sum-item-value">
                                            <strong class="sum-place">{{ $pickupName ?? '—' }}</strong>

                                            <div class="sum-dt2">
                                                <div class="dt-row">
                                                    <span class="dt-lbl">{{ __('Date') }}</span>
                                                    <span class="dt-val">{{ $pickupFechaLarga ?? $pickupDate }}</span>
                                                </div>
                                                <div class="dt-row">
                                                    <span class="dt-lbl">{{ __('Time') }}</span>
                                                    <span class="dt-time">{{ $pickupTime }} HRS</span>
                                                </div>
                                            </div>

                                            <span class="js-date" data-iso="{{ $pickupDateISO }}"
                                                style="display:none;">{{ $pickupDate }}</span>
                                        </div>
                                    </div>

                                    {{-- DROPOFF --}}
                                    <div class="sum-item">
                                        <div class="sum-item-label">
                                            <i class="fa-solid fa-location-dot"></i> {{ __('Return') }}
                                        </div>

                                        <div class="sum-item-value">
                                            <strong class="sum-place">{{ $dropoffName ?? '—' }}</strong>

                                            <div class="sum-dt2">
                                                <div class="dt-row">
                                                    <span class="dt-lbl">{{ __('Date') }}</span>
                                                    <span class="dt-val">{{ $dropoffFechaLarga ?? $dropoffDate }}</span>
                                                </div>
                                                <div class="dt-row">
                                                    <span class="dt-lbl">{{ __('Time') }}</span>
                                                    <span class="dt-time">{{ $dropoffTime }} HRS</span>
                                                </div>
                                            </div>

                                            <span class="js-date" data-iso="{{ $dropoffDateISO }}"
                                                style="display:none;">{{ $dropoffDate }}</span>
                                        </div>
                                    </div>

                                </div>

                                <h4 class="sum-subtitle" style="margin-top:14px;" id="tuAutoSection">{{ __('Your vehicle') }}</h4>

                                <div class="sum-car" style="margin-top:10px; display:flex; gap:20px; align-items:center;">
                                    <div class="sum-car-img">
                                        <img src="{{ $categoriaImg }}" alt="Auto"
                                            onerror="this.onerror=null;this.src='{{ $placeholder }}';"
                                            style="width:200px; border-radius:14px;">
                                    </div>

                                    <div class="sum-car-info" style="flex:1;">
                                        <div class="car-mini-name"
                                            style="font-weight:900; font-size:20px; color:#111827;">
                                            {{ $autoTitulo }}
                                        </div>

                                        <div class="car-mini-sub"
                                            style="margin-top:4px; font-weight:800; font-size:12px; letter-spacing:.6px; text-transform:uppercase; color:#111827;">
                                            {{ $autoSubtitulo }}
                                        </div>

                                        {{-- ✅ NUEVO (Step 4): iconos + T | ... + A/C + chips (2 bloques) --}}
                                        @php
                                            $codigo = strtoupper((string) ($categoriaSel->codigo ?? ''));
                                            $transmision = $categoriaSel->id_categoria == 9 ? __('Manual') : __('Automatic');
                                            $tieneACCat = (int) ($cat->aire_acondicionado ?? ($cat->aire_ac ?? 1));

                                            $predeterminadosPorId = [
                                                1 => ['pax' => 5, 'small' => 2, 'big' => 1],
                                                2 => ['pax' => 5, 'small' => 2, 'big' => 1],
                                                3 => ['pax' => 5, 'small' => 2, 'big' => 2],
                                                4 => ['pax' => 5, 'small' => 2, 'big' => 2],
                                                5 => ['pax' => 5, 'small' => 2, 'big' => 2],
                                                6 => ['pax' => 5, 'small' => 3, 'big' => 2],
                                                7 => ['pax' => 7, 'small' => 3, 'big' => 2],
                                                8 => ['pax' => 7, 'small' => 4, 'big' => 2],
                                                9 => ['pax' => 13, 'small' => 4, 'big' => 3],
                                                10 => ['pax' => 5, 'small' => 3, 'big' => 2],
                                                11 => ['pax' => 5, 'small' => 3, 'big' => 2],
                                            ];

                                            $idActual = $categoriaSel->id_categoria ?? null;

                                            if (isset($predeterminadosPorId[$idActual])) {
                                                $cap = $predeterminadosPorId[$idActual];
                                            } else {
                                                $cap = [
                                                    'pax' => (int) ($categoriaSel->pasajeros ?? 5),
                                                    'small' => (int) ($categoriaSel->maletas_chicas ?? 2),
                                                    'big' => (int) ($categoriaSel->maletas_grandes ?? 1),
                                                ];
                                            }
                                        @endphp

                                        <div class="car-features" style="margin-top:14px;">
                                            <ul class="car-mini-specs">
                                                <li><i class="fa-solid fa-user-large"></i> {{ $cap['pax'] }}</li>
                                                <li><i class="fa-solid fa-suitcase-rolling"></i> {{ $cap['small'] }}</li>
                                                <li><i class="fa-solid fa-briefcase"></i> {{ $cap['big'] ?? 1 }}</li>

                                                <li title="{{ __('Transmission') }}">
                                                    <span class="spec-letter">T | {{ $transmision }}</span>
                                                </li>

                                                @if ($tieneACCat)
                                                    <li title="{{ __('Air conditioning') }}">
                                                        <i class="fa-regular fa-snowflake"></i>
                                                        <span class="spec-letter">A/C</span>
                                                    </li>
                                                @endif
                                            </ul>

                                            <div class="car-connect">
                                                @if ($featCarplay)
                                                    <span class="badge-chip badge-apple" title="Apple CarPlay">
                                                        <span class="icon-badge"><i class="fa-brands fa-apple"></i></span>
                                                        CarPlay
                                                    </span>
                                                @endif

                                                @if ($featAndroidAuto)
                                                    <span class="badge-chip badge-android" title="Android Auto">
                                                        <span class="icon-badge"><i
                                                                class="fa-brands fa-android"></i></span>
                                                        Android Auto
                                                    </span>
                                                @endif
                                            </div>
                                        </div>

                                    </div>
                                </div>

                            </div>

                            <h4 class="sum-subtitle" style="margin-top:16px;">{{ __('Price details') }}</h4>

                            <div class="sum-table" id="cotizacionDoc"
                            data-base="{{ $tarifaBase }}"
                            data-days="{{ $days }}"
                            data-pickup="{{ $pickupSucursalId }}"
                            data-dropoff="{{ $dropoffSucursalId }}"
                            data-km="{{ $dropoffKm }}"
                            data-costokm="{{ $costoKmCategoria }}"
                            data-tanque="{{ $capacidadTanque ?? 0 }}">

                                {{-- ===== TARIFA BASE (desplegable) ===== --}}
                                <details class="sum-acc" open="false">
                                    <summary class="sum-bar">
                                        <span>{{ __('Base rate') }}</span>
                                        <strong id="qBase">${{ number_format($tarifaBase, 0) }} MXN</strong>
                                        <i class="sum-caret fa-solid fa-chevron-down" aria-hidden="true"></i>
                                    </summary>

                                    <div class="sum-acc-body">
                                        <div class="row row-base">
                                            <span>{{ $days }} {{ __('day(s) - price per day') }}
                                                ${{ number_format((float) ($categoriaSel->precio_dia ?? 0), 0) }}
                                                MXN</span>
                                        </div>

                                        <div class="row row-base-total">
                                            <span class="row-total-label">{{ __('Total:') }}</span>
                                            <strong>${{ number_format($tarifaBase, 0) }} MXN</strong>
                                        </div>

                                        {{-- Modal de protecciones --}}
                                        <div id="modalProtecciones" class="modal-global-viajero">
                                            <div class="modal-global-content">
                                                <span class="cerrar-modal-v">&times;</span>

                                                <h2 class="modal-v-header-title">{{ __('Liability waivers (Protections)') }}
                                                </h2>
                                                <hr style="border: 0; border-top: 1px solid #f1f5f9; margin: 20px 0;">

                                                <div style="display: flex; gap: 20px; align-items: flex-start;">
                                                    <div class="modal-v-escudo-circulo">
                                                        <i class="fa-solid fa-shield" style="font-size: 28px;"></i>
                                                    </div>

                                                    <div>
                                                        <strong class="modal-v-titulo-negro">{{ __('LIMITED THIRD-PARTY LIABILITY PROTECTION (LI)') }}</strong>
                                                        <p class="modal-v-texto-gris">
                                                            {{ __('Protects third parties for damages and injuries caused in an accident and covers the minimum amount required by law.') }}
                                                        </p>
                                                    </div>
                                                </div>

                                                <div
                                                    style="margin-top: 30px; padding-top: 20px; border-top: 1px solid #f1f5f9;">
                                                    <p
                                                        style="font-size: 13px; color: #64748b; line-height: 1.5; margin-bottom: 12px;">
                                                        {{ __('You choose the level of liability for the vehicle that best fits your needs and budget.') }}
                                                    </p>
                                                    <p style="font-size: 13px; color: #1e293b; font-weight: 700;">
                                                        {{ __('Ask about our Liability Waivers (optional) when you arrive at any of our branches.') }}
                                                    </p>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="col-12 mt-2">
                                            <div class="linea-incluido-box">
                                                <p class="incluido-text">
                                                    <strong>{{ __('INCLUDED') }}</strong>
                                                    <i class="fa-solid fa-circle-question" id="info-protecciones"
                                                        style="cursor: pointer; color: #b22222; margin-left: 5px; font-size: 1.1rem; vertical-align: middle;"></i>
                                                </p>
                                            </div>
                                        </div>
                                        <div class="row row-included" style="border-top:0;">
                                            <span class="inc-items">
                                                <span class="inc-item"><span class="inc-check">✔</span> {{ __('Unlimited mileage') }}</span>
                                                <span class="inc-item"><span class="inc-check">✔</span> {{ __('Liability Waiver (LI)') }}</span>
                                            </span>
                                        </div>
                                    </div>
                                </details>

                                {{-- ===== OPCIONES DE RENTA (desplegable) ===== --}}
                                <details class="sum-acc">
                                    <summary class="sum-bar">
                                        <span>{{ __('Rental options') }}</span>
                                        <strong id="qExtras">$0 MXN</strong>
                                        <i class="sum-caret fa-solid fa-chevron-down" aria-hidden="true"></i>
                                    </summary>

                                    <div class="sum-acc-body" id="extrasList">
                                        <div class="row">
                                            <span class="muted">{{ __('No add-ons selected') }}</span>
                                            <strong>$0 MXN</strong>
                                        </div>
                                    </div>
                                </details>
 {{-- ===== Editar  ===== --}}
                                {{-- ===== CARGOS E IVA (desplegable) ===== --}}
                                <details class="sum-acc">
                                    <summary class="sum-bar">
                                        <span>{{ __('Charges and VAT (16%)') }}</span>
                                        <strong id="qIva">$0 MXN</strong>
                                        <i class="sum-caret fa-solid fa-chevron-down" aria-hidden="true"></i>
                                    </summary>

                                    <div class="sum-acc-body" id="ivaList">
                                        <div class="row">
                                            <span class="muted">{{ __('No additional charges') }}</span>
                                            <strong>$0 MXN</strong>
                                        </div>
                                    </div>
                                </details>

                                {{-- ===== TOTAL ===== --}}
                                <div class="sum-total">
                                    <span>{{ __('Total') }}</span>
                                    <strong id="qTotal">${{ number_format($tarifaBase, 0) }} MXN</strong>
                                </div>

                            </div>

                        </div>
                    </div>

                    @isset($servicios)
                        <script id="addonsCatalog" type="application/json">
                        {!! json_encode(
                            collect($servicios)->mapWithKeys(fn($s) => [
                                (string) $s->id_servicio => [
                                    'nombre' => $s->nombre,
                                    'precio' => (float) $s->precio,
                                    'tipo'   => $s->tipo_cobro,
                                ],
                            ]),
                         JSON_UNESCAPED_UNICODE
                        ) !!}
                     </script>
                    @endisset


                @endif

            </section>

        </div>{{-- /fondos-reservaciones --}}
    </main>

    {{-- TARJETA RESPONSIVA --}}
@if ($stepCurrent === 4)
    <div class="movil-footer-sticky">
        <div class="movil-total-wrapper">
            <span class="movil-total-label">{{ __('Total') }}</span>
            <span id="qTotalMovil" class="movil-total-amount">
                ${{ number_format($tarifaBase, 0) }} MXN
            </span>
        </div>
        <button type="button" id="btnReservarMovil" class="btn-reservar-movil">
            {{ __('Book') }}
        </button>
    </div>

{{-- MODAL DE PAGO EN LÍNEA --}}
<div id="modalPagoOnline" class="modal-overlay" style="display:none;">
    <div class="modal-card">
        <button id="cerrarModalPagoOnline" class="modal-close" type="button">×</button>

        <!-- HEADER -->
        <div class="modal-linea-head">
            <span class="modal-linea-badge">
                <i class="fa-regular fa-credit-card"></i> {{ __('Secure payment') }}
            </span>
            <h3>{{ __('Online payment') }}</h3>
            <div class="modal-linea-sub">{{ __('Complete your reservation securely') }}</div>
        </div>

        <div class="modal-linea-scrollable">
            <div class="modal-linea-body">
                <!-- Contenedor de PayPal -->
                <div id="paypal-button-container">
                    <div class="modal-linea-loading">
                        <i class="fa-regular fa-credit-card"></i>
                        <p>{{ __('Loading payment options...') }}</p>
                    </div>
                </div>
            </div>
        </div>
        <div class="modal-linea-security">
            <i class="fa-regular fa-lock"></i>
            <span>{{ __('Secure payment processed by PayPal') }}</span>
            <i class="fa-regular fa-shield-haltered"></i>
        </div>
    </div>
</div>
@endif
@endsection

@section('js-vistaReservaciones')
    @php
        $paypalMode = env('PAYPAL_MODE', 'live');
        $paypalClientId =
            $paypalMode === 'live'
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
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const main = document.querySelector('main.page');
            const currentPlan = (main && main.dataset.plan) ? main.dataset.plan : '';

            const modalMetodoPago = document.getElementById('modalMetodoPago');
            const cerrarModalMetodo = document.getElementById('cerrarModalMetodo');
            const btnPagoLinea = document.getElementById('btnPagoLinea');
            const btnPagoMostrador = document.getElementById('btnPagoMostrador');
            document.addEventListener('reserva:validacionExitosa', function(e) {
                console.log('✅ Validación exitosa, plan:', currentPlan);

                if (currentPlan === 'linea') {
                    // Pago en línea directo
                    if (btnPagoLinea) {
                        btnPagoLinea.click();
                    } else if (typeof window.handleReservaPagoEnLinea === 'function') {
                        window.handleReservaPagoEnLinea();
                    }
                } else if (currentPlan === 'mostrador') {
                    // Mostrar modal de selección de método de pago
                    if (modalMetodoPago) {
                        modalMetodoPago.style.display = 'flex';
                    }
                } else {
                    if (window.alertify) {
                        alertify.warning(
                            '{{ __("Select a payment type from the category step (Counter or Prepay).") }}'
                            );
                    }
                }
            });

            // Cerrar modal
            if (cerrarModalMetodo && modalMetodoPago) {
                cerrarModalMetodo.addEventListener('click', function() {
                    modalMetodoPago.style.display = 'none';
                });

                modalMetodoPago.addEventListener('click', function(e) {
                    if (e.target === modalMetodoPago) {
                        modalMetodoPago.style.display = 'none';
                    }
                });
            }

            const tarifa = document.querySelector('.sum-table details.sum-acc');
            if (tarifa && tarifa.hasAttribute('open')) tarifa.removeAttribute('open');
        });
    </script>

<script>
    window.PAYPAL_MODE = "{{ $paypalMode }}";
    window.PAYPAL_CLIENT_ID = "{{ $paypalClientId }}";

    // ============================================================
    // TRADUCCIONES PARA EL JS DE RESERVACIÓN
    // ============================================================
   window.translations = {
    // Mensajes de error/validación
    cannot_proceed: "{{ __('We cannot proceed.') }}",
    please_complete: "{{ __('Please complete:') }}",
    required_missing: "{{ __('Required information missing.') }}",
    full_name: "{{ __('Full name') }}",
    email: "{{ __('Email') }}",
    phone: "{{ __('Phone') }}",
    acceptance_policies: "{{ __('Acceptance of policies') }}",
    reservation_form_not_found: "{{ __('Reservation form not found.') }}",
    could_not_register: "{{ __('Could not register the reservation.') }}",
    security_token_not_found: "{{ __('Security token not found. Please refresh the page and try again.') }}",
    error_occurred: "{{ __('An error occurred while registering the reservation.') }}",

    // Mensajes de éxito
    reservation_registered: "{{ __('Your reservation has been successfully registered.') }}",
    itinerary: "{{ __('Itinerary') }}",
    folio: "{{ __('Folio') }}",
    pickup_label: "{{ __('Pick-up') }}",
    return_label: "{{ __('Return') }}",
    payment_summary: "{{ __('Payment Summary') }}",
    base_rate: "{{ __('Base rate') }}",
    rental_options: "{{ __('Rental options') }}",
    charges_vat: "{{ __('Charges and VAT (16%)') }}",
    total_label: "{{ __('Total') }}",
    confirmation_email: "{{ __('You will receive a confirmation by email.') }}",
    go_to_homepage: "{{ __('Go to homepage') }}",
    reservation_success_fallback: "{{ __('Reservation registered successfully. Check your confirmation email.') }}",


};
@endsection
