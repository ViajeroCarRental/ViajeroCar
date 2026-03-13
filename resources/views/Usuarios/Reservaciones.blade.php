@extends('layouts.Usuarios')
@section('Titulo', 'Reservaciones')

@section('css-vistaReservaciones')
    <link rel="stylesheet" href="{{ asset('css/reservaciones.css') }}">
@endsection

@section('contenidoReservaciones')
    @php
        // ====== Estado recibido por GET (sin sesión) ======
        $f = $filters ?? [];

        $pickupSucursalId = $f['pickup_sucursal_id'] ?? request('pickup_sucursal_id');
        $dropoffSucursalId = $f['dropoff_sucursal_id'] ?? request('dropoff_sucursal_id');

        // =========================
        // ✅ Fechas robustas (ISO para lógica, DMY para UI)
        // =========================
        $pickupDateRaw = $f['pickup_date'] ?? request('pickup_date');
        $dropoffDateRaw = $f['dropoff_date'] ?? request('dropoff_date');

        // ⛔ ANTES forzaba '12:00' aquí
        $pickupTime = $f['pickup_time'] ?? request('pickup_time');
        $dropoffTime = $f['dropoff_time'] ?? request('dropoff_time');

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

        $isFreshEntry = empty($pickupSucursalId) || empty($dropoffSucursalId);
        $stepCurrent = $isFreshEntry ? 1 : $controllerStep ?? $requestedStep;
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

        // ✅ días (mínimo 1)
        if ($pickupDateISO && $dropoffDateISO) {
            $d1 = \Illuminate\Support\Carbon::parse($pickupDateISO);
            $d2 = \Illuminate\Support\Carbon::parse($dropoffDateISO);
            $days = max(1, $d1->diffInDays($d2));
        } else {
            // Si no hay fechas aún (primer ingreso), dejamos 1 para no romper cálculos internos
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
                : 'Auto o similar';

        // ✅ Línea secundaria
        $catNombreUpper =
            $categoriaSel && isset($categoriaSel->nombre) ? strtoupper((string) $categoriaSel->nombre) : 'CATEGORÍA';

        $catCodigoUpper =
            $categoriaSel && isset($categoriaSel->codigo) ? strtoupper((string) $categoriaSel->codigo) : '';

        $autoSubtitulo = $catCodigoUpper ? $catNombreUpper . ' | CATEGORÍA ' . $catCodigoUpper : $catNombreUpper;

        // ✅ IMÁGENES
        $catImages = [
            1 => asset('img/aveo.png'),
            2 => asset('img/virtus.png'),
            3 => asset('img/jetta.png'),
            4 => asset('img/camry.png'),
            5 => asset('img/renegade.png'),
            6 => asset('img/seltos.png'),
            7 => asset('img/avanza.png'),
            8 => asset('img/Odyssey.png'),
            9 => asset('img/Hiace.png'),
            10 => asset('img/Frontier.png'),
            11 => asset('img/Tacoma.png'),
        ];

        $placeholder = asset('img/Logotipo.png');

        $categoriaImg = $categoriaSel ? $catImages[$categoriaSel->id_categoria] ?? $placeholder : $placeholder;

        $precioDiaCategoria = (float) ($categoriaSel->precio_dia ?? 0);
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
            $categoriaSel && isset($categoriaSel->nombre) ? strtoupper((string) $categoriaSel->nombre) : 'COMPACTO';

        // ✅ SOLO estos extras (Step 3) — máximo 3 por cada uno (lo limita tu JS)
        $allowedExtras = ['silla para bebé', 'conductor adicional', 'gasolina prepago'];

        $serviciosFiltrados = collect($servicios ?? [])
            ->filter(function ($s) use ($allowedExtras) {
                $name = mb_strtolower(trim((string) ($s->nombre ?? '')));
                return in_array($name, $allowedExtras, true);
            })
            ->values();
    @endphp

    <main class="page wizard-page" data-current-step="{{ $stepCurrent }}" data-plan="{{ $plan ?? '' }}"
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
            <div class="fondo-fijo-layout"></div>

            {{-- ===================== PASOS ARRIBA ===================== --}}
            <nav class="wizard-steps" aria-label="Pasos">
                <a class="wizard-step {{ $stepCurrent > 1 ? 'done' : '' }} {{ $stepCurrent === 1 ? 'active' : '' }}"
                    href="{{ $toStep(1) }}">
                    <span class="n">1</span> Generales
                </a>
                <a class="wizard-step {{ $stepCurrent > 2 || request('auto') ? 'done' : '' }} {{ $stepCurrent === 2 ? 'active' : '' }}"
                    href="{{ $toStep(2) }}">
                    <span class="n">2</span> Categoría
                </a>
                <a class="wizard-step {{ $stepCurrent > 3 ? 'done' : '' }} {{ $stepCurrent === 3 ? 'active' : '' }}"
                    href="{{ $toStep(3) }}">
                    <span class="n">3</span> Adicionales
                </a>
                <a class="wizard-step {{ $stepCurrent === 4 ? 'active' : '' }}" href="{{ $toStep(4) }}">
                    <span class="n">4</span> Confirmación
                </a>
            </nav>

            <section class="wizard-card">
{{-- ===================== STEP 1 ===================== --}}
@if ($stepCurrent === 1)

    {{-- BOTÓN PARA ABRIR BUSCADOR EN MÓVIL/TABLET --}}
    <div class="btn-buscador-movil">
        <div class="btn-container">
            <p style="margin-bottom: 12px; font-weight: 700; color: #333; font-size: 16px;">
                Encuentra tu auto aquí
            </p>
            <button type="button" id="btn-abrir-buscador-reservas"
                    style="background-color: #b22222;
                           border: none;
                           font-weight: 700;
                           height: 50px;
                           font-size: 18px;
                           display: flex;
                           align-items: center;
                           justify-content: center;
                           gap: 8px;
                           text-transform: uppercase;
                           border-radius: 8px;
                           width: 100%;
                           color: white;
                           cursor: pointer;">
                <i class="fa-solid fa-magnifying-glass"></i> BUSCAR
            </button>
        </div>
    </div>

    {{-- BUSCADOR PRINCIPAL --}}
    <div class="search-card" id="miBuscador">
        {{-- BOTÓN DE CERRAR - DENTRO DEL BUSCADOR --}}
        <button type="button" id="btn-cerrar-buscador-politicas" class="btn-close-politicas" aria-label="Cerrar">
            <span>Cerrar</span>
        </button>

        <header class="wizard-head">
            <h2>Sobre tu reservación</h2>
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
                        <span class="field-title">Lugar de renta</span>
                        <label class="inline-check" for="differentDropoff">
                            <input type="checkbox" id="differentDropoff" name="different_dropoff" value="1"
                                {{ request('different_dropoff') ? 'checked' : '' }}>
                            <span>Devolver en <br> otro destino</span>
                        </label>
                    </div>

                    <div class="location-inputs-wrapper" id="locationInputsWrapper">
                        {{-- SELECT PICKUP --}}
                        <div class="field icon-field" id="pickupField">
                            <span class="field-icon"><i id="pickupIcon" class="fa-solid fa-location-dot"></i></span>
                            <select id="pickupPlace" name="pickup_sucursal_id" required>
                                <option value="" disabled {{ $pickupSucursalId ? '' : 'selected' }}>¿Dónde inicia tu viaje?</option>
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
                                <option value="" disabled {{ $dropoffSucursalId ? '' : 'selected' }}>¿Dónde termina tu viaje?</option>
                                @foreach ($ciudades as $ciudad)
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
                        <span class="field-title solo-responsivo-izq">Pick-Up</span>
                        <div class="datetime-row">
                            <div class="dt-field icon-field">
                                <span class="field-icon"><i class="fa-regular fa-calendar-days"></i></span>
                                <input id="start" name="pickup_date" type="text"
                                    placeholder="Fecha" class="flatpickr-input"
                                    value="{{ $pickupDate }}" required>
                            </div>
                            <div class="dt-field icon-field time-field">
                                <span class="field-icon"><i class="fa-regular fa-clock"></i></span>
                                <input type="hidden" name="pickup_time" id="pickup_time_hidden" value="{{ $pickupTime }}">
                                <select id="pickup_h" name="pickup_h" required class="time-select">
                                    <option value="" disabled {{ empty($ph) ? 'selected' : '' }}>Hora</option>
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
                        <span class="field-title solo-responsivo-izq">Devolución</span>
                        <div class="datetime-row">
                            <div class="dt-field icon-field">
                                <span class="field-icon"><i class="fa-regular fa-calendar-days"></i></span>
                                <input id="end" name="dropoff_date" type="text"
                                    placeholder="Fecha" class="flatpickr-input"
                                    value="{{ $dropoffDate }}" required>
                            </div>
                            <div class="dt-field icon-field time-field">
                                <span class="field-icon"><i class="fa-regular fa-clock"></i></span>
                                <input type="hidden" name="dropoff_time" id="dropoff_time_hidden" value="{{ $dropoffTime }}">
                                <select id="dropoff_h" name="dropoff_h" required class="time-select">
                                    <option value="" disabled {{ empty($dh) ? 'selected' : '' }}>Hora</option>
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
                        <button type="button" class="btn btn-ghost" onclick="limpiarTodoYReiniciar()">Limpiar</button>
                        <button type="submit" class="btn btn-primary">Siguiente</button>
                    </div>
                </div>

            </div>
        </form>
    </div>
<script>
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
                    document.body.style.position = 'fixed';
                    document.body.style.top = `-${scrollY}px`;
                    document.body.style.left = '0';
                    document.body.style.right = '0';
                    document.body.style.overflow = 'hidden';
                    document.body.style.width = '100%';
                    document.body.dataset.scrollY = scrollY;
                }

                function restaurarScroll() {
                    document.body.style.position = '';
                    document.body.style.top = '';
                    document.body.style.left = '';
                    document.body.style.right = '';
                    document.body.style.overflow = '';
                    document.body.style.width = '';
                    const scrollY = document.body.dataset.scrollY || 0;
                    window.scrollTo(0, parseInt(scrollY));
                    delete document.body.dataset.scrollY;
                }

                btnAbrir.addEventListener('click', function(e) {
                    e.preventDefault();
                    buscador.classList.add('active');
                    bloquearScroll();

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
                    restaurarScroll();
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
                        mostrarError(pickup, 'Ubicacion Requerida');
                    } else {
                        pickup.classList.add('field-success');
                    }

                    // Validar dropoff si está visible (usando differentDropoff.checked)
                    const differentDropoff = document.getElementById('differentDropoff');
                    const dropoff = document.getElementById('dropoffPlace');
                    if (differentDropoff.checked && !dropoff.value) {
                        valido = false;
                        mostrarError(dropoff, 'Ubicacion Requerida');
                    } else if (differentDropoff.checked) {
                        dropoff.classList.add('field-success');
                    }

                    // Validar fechas (con soporte para flatpickr)
                    const start = document.getElementById('start');
                    if (!start.value) {
                        valido = false;
                        mostrarError(start, 'Fecha Requerida');
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
                        mostrarError(end, 'Fecha Requerida');
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
                        mostrarError(pickupH, 'Hora Requerida');
                    } else {
                        pickupH.classList.add('field-success');
                    }

                    const dropoffH = document.getElementById('dropoff_h');
                    if (!dropoffH.value) {
                        valido = false;
                        mostrarError(dropoffH, 'Hora Requerida');
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
    </script>
    console.log("Formulario valido:", valido);
@endif
                {{-- ===================== STEP 2 ===================== --}}

                @if ($stepCurrent === 2)
                    <header class="wizard-head">
                        <h2>Selecciona tu categoría</h2>
                        <p>Tarifa de <strong id="daysLabel">{{ $days }}</strong> día(s) de tu renta.</p>
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
                                $transmision = $cat->id_categoria == 9 ? 'Estándar' : 'Automática';
                                $tieneACCat = (int) ($cat->aire_acondicionado ?? ($cat->aire_ac ?? 1));

                                // *** SOLO CAMBIAMOS ESTA LÍNEA ***
                                // Antes: $desc = $cat->ejemplo ?? ($cat->descripcion ?? 'Auto o similar.');
                                // Ahora: Usamos directamente la descripción de la categoría
                                $desc = $cat->descripcion ?? 'Auto o similar.';

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
                                            <span class="js-days-badge">{{ $days }}</span> día(s)
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
                                            <li title="Pasajeros">
                                                <i class="fa-solid fa-user-large"></i> {{ $paxFinal }}
                                            </li>
                                            <li title="Maletas chicas">
                                                <i class="fa-solid fa-suitcase-rolling"></i> {{ $sFinal }}
                                            </li>
                                            <li title="Maletas grandes">
                                                <i class="fa-solid fa-briefcase"></i> {{ $bFinal }}
                                            </li>
                                            <li title="Transmisión">
                                                <span class="spec-letter">T | {{ $transmision }}</span>
                                            </li>
                                            @if ($tieneACCat)
                                                <li title="Aire acondicionado">
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
                                                Ahorra <strong class="js-ahorro">{{ $ahorroPct }}</strong>%
                                            </div>
                                        @endif
                                        <a class="btn-pay primary"
                                            href="{{ $toStep(3, ['categoria_id' => $cat->id_categoria, 'plan' => 'linea']) }}">
                                            PREPAGAR EN LÍNEA
                                        </a>
                                        <div class="office-wrap">
                                            <div class="office-price">
                                                $<span
                                                    class="js-mostrador-total">{{ number_format($mostradorTotal, 0) }}</span>
                                                MXN
                                            </div>
                                            <a class="btn-pay gray"
                                                href="{{ $toStep(3, ['categoria_id' => $cat->id_categoria, 'plan' => 'mostrador']) }}">
                                                PAGAR EN OFICINA
                                            </a>
                                        </div>
                                    </div>
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

                <script>
                console.log("STEP ACTUAL:", "{{ $stepCurrent }}");
                </script>
                {{-- ===================== STEP 3 ===================== --}}
                @if ($stepCurrent === 3)
                    <header class="wizard-head">
                        <h2>Selecciona las opciones adicionales que desees</h2>
                        <p>Revisa protecciones incluidas y agrega equipamiento/servicios extra.</p>
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
                                Relevos de responsabilidad (Protecciones)
                                <button type="button" class="step3-info" id="info-protecciones-step3"
                                    title="Más información">
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
                                            <p class="prot-name">Protección limitada de responsabilidad hacia terceros (LI)
                                            </p>
                                            <p class="prot-desc">
                                                Protege a terceros por daños y perjuicios ocasionados en un accidente y
                                                cubre la cantidad mínima requerida por ley.
                                            </p>
                                            <div class="prot-badge"><span class="dot"></span> Incluida</div>
                                        </div>
                                    </div>
                                </div>

                                <div class="prot-card">
                                    <div class="prot-top">
                                        <div class="prot-icon">
                                            <i class="fa-solid fa-shield-halved"></i>
                                        </div>
                                        <div>
                                            <p class="prot-name">Protecciones adicionales</p>
                                            <p class="prot-desc">
                                                Tú eliges el nivel de responsabilidad sobre el auto que más vaya acorde a
                                                tus necesidades y presupuesto.
                                                Pregunta por nuestros relevos (opcionales) al llegar al mostrador de
                                                cualquiera de nuestras oficinas.
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div id="modalProteccionesStep3" class="modal-s3" aria-hidden="true">
                                <div class="card">

                                    <button type="button" class="x" id="closeProteccionesStep3"
                                        aria-label="Cerrar">
                                        <i class="fa-solid fa-xmark"></i>
                                    </button>

                                    <h2 class="s3-modal-title">Relevos de responsabilidad (Protecciones)</h2>
                                    <p class="s3-modal-sub">Consulta el detalle de cada pack y lo que incluye.</p>

                                    <div class="s3-body-scroll">
                                        <div class="s3-info-top">
                                            <p>
                                                <strong>VIAJERO</strong> ofrece diferentes tipos de Relevos de
                                                responsabilidad (Protecciones) opcionales disponibles por
                                                un cargo adicional diario el cual se puede adquirir al reservar o el día de
                                                la renta.
                                            </p>

                                            <p>
                                                El cliente es responsable de todo daño o robo del vehículo
                                                <strong>VIAJERO</strong> sujeto a ciertas exclusiones
                                                contenidas en el contrato de alquiler. <strong>VIAJERO</strong> renunciará o
                                                limitará la responsabilidad del cliente
                                                a través de la adquisición de alguno de estos.
                                            </p>

                                            <p style="margin-bottom:0;">
                                                Los clientes que reserven utilizando su Número Wizard verán las preferencias
                                                de coberturas y seguros seleccionados en su perfil.
                                                También puede acudir a una oficina o llamar al <strong>01 (442) 303
                                                    2668</strong> para obtener ayuda.
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
                                                    <li><strong>LDW:</strong> El cliente es responsable por el <strong>0%
                                                            deducible</strong>, de lado a lado pase lo que pase con el auto,
                                                        está cubierto de bumper a bumper.</li>
                                                    <li><strong>PAI:</strong> Gastos médicos cubiertos <strong>$250,000
                                                            MXN</strong> por evento.</li>
                                                    <li><strong>PRA:</strong> Asistencia en carretera Premium. Incluye:
                                                        envío de llaves o gasolina, apertura de auto, cambio de neumático
                                                        ponchado y paso de corriente. <strong>No incluye</strong> costo de
                                                        llave ni gasolina.</li>
                                                    <li><strong>LOU:</strong> Tiempo perdido en taller, cubierto.</li>
                                                    <li><strong>LA:</strong> Asistencia legal, cubierta.</li>
                                                    <li><strong>LI:</strong> Responsabilidad civil hasta <strong>$3,000,000
                                                            MXN</strong>.</li>
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
                                                    <li><strong>PDW:</strong> Cubierta toda la carrocería al
                                                        <strong>5%</strong>, <strong>10%</strong> pérdida total o robo.
                                                        <strong>No cubre</strong> llantas, accesorios, rines ni cristales.
                                                    </li>
                                                    <li><strong>PAI:</strong> Gastos médicos cubiertos <strong>$250,000
                                                            MXN</strong> por evento.</li>
                                                    <li><strong>PRA (DECLINADO):</strong> Asistencia Premium: el cliente es
                                                        responsable por costos de: grúa (en caso de requerirla), corralón,
                                                        envío de llaves o gasolina, apertura de auto, cambio de neumático
                                                        ponchado y paso de corriente.</li>
                                                    <li><strong>LOU:</strong> Tiempo perdido en taller, cubierto.</li>
                                                    <li><strong>LA:</strong> Asistencia legal, cubierta.</li>
                                                    <li><strong>ALI:</strong> Responsabilidad civil hasta <strong>$1,000,000
                                                            MXN</strong>.</li>
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
                                                    <li><strong>CDW 10%:</strong> El cliente es responsable por el
                                                        <strong>10% deducible</strong> en daños, <strong>20%</strong>
                                                        pérdida total o robo sobre valor factura.</li>
                                                    <li><strong>PAI:</strong> Gastos médicos cubiertos <strong>$250,000
                                                            MXN</strong> por evento.</li>
                                                    <li><strong>PRA (DECLINADO):</strong> Asistencia Premium: el cliente es
                                                        responsable por costos de: grúa (en caso de requerirla), corralón,
                                                        envío de llaves o gasolina, apertura de auto, cambio de neumático
                                                        ponchado y paso de corriente.</li>
                                                    <li><strong>LOU:</strong> Tiempo perdido en taller, cubierto.</li>
                                                    <li><strong>LA:</strong> Asistencia legal, cubierta.</li>
                                                    <li><strong>ALI:</strong> Responsabilidad civil hasta <strong>$1,000,000
                                                            MXN</strong>.</li>
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
                                                    <li><strong>CDW 20%:</strong> El cliente es responsable por el
                                                        <strong>20% deducible</strong> en daños, <strong>30%</strong>
                                                        pérdida total o robo sobre valor factura.</li>
                                                    <li><strong>PAI:</strong> Gastos médicos cubiertos <strong>$250,000
                                                            MXN</strong> por evento.</li>
                                                    <li><strong>PRA (DECLINADO):</strong> Asistencia Premium: el cliente es
                                                        responsable por costos de: grúa (en caso de requerirla), corralón,
                                                        envío de llaves o gasolina, apertura de auto, cambio de neumático
                                                        ponchado y paso de corriente.</li>
                                                    <li><strong>LOU:</strong> Tiempo perdido en taller, cubierto.</li>
                                                    <li><strong>LA:</strong> Asistencia legal, cubierta.</li>
                                                    <li><strong>LI:</strong> Responsabilidad civil hasta <strong>$350,000
                                                            MXN</strong>.</li>
                                                </ul>
                                            </div>
                                        </details>

                                        {{-- DECLINE PROTECTIONS --}}
                                        <details class="s3-acc-item s3-acc-danger">
                                            <summary class="s3-acc-sum">
                                                <span class="s3-acc-left">
                                                    <span class="s3-acc-badge s3-badge-danger">DECLINE</span>
                                                    <span class="s3-acc-name">DECLINE PROTECTIONS</span>
                                                </span>
                                                <i class="fa-solid fa-chevron-down s3-acc-caret" aria-hidden="true"></i>
                                            </summary>

                                            <div class="s3-acc-body">
                                                <ul class="s3-list">
                                                    <li><strong>CDW (DECLINADO):</strong> El cliente es responsable por el
                                                        <strong>100% deducible</strong> sobre valor factura del auto.</li>
                                                    <li><strong>No</strong> cubre gastos médicos en caso de accidente.</li>
                                                    <li><strong>PRA (DECLINADO):</strong> Asistencia Premium: el cliente es
                                                        responsable por costos de: grúa (en caso de requerirla), corralón,
                                                        envío de llaves o gasolina, apertura de auto, cambio de neumático
                                                        ponchado y paso de corriente.</li>
                                                    <li><strong>LOU (DECLINADO):</strong> No cubre tiempo perdido en taller.
                                                    </li>
                                                    <li><strong>LA (DECLINADO):</strong> No cubre asistencia legal.</li>
                                                    <li><strong>LI:</strong> Responsabilidad civil hasta <strong>$350,000
                                                            MXN</strong>.</li>
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

                        {{-- Equipamiento & Servicios --}}
                        <section class="step3-section">
                            <div class="step3-title">
                                Equipamiento & Servicios
                                <small>máximo 3 por opción</small>
                            </div>

                            <div class="equip-grid">
                                @forelse($serviciosFiltrados as $srv)
                                    @php
                                        $unidad = $srv->tipo_cobro === 'por_tanque' ? '/ tanque' : '/ evento';;
                                        $precio = number_format((float) $srv->precio, 0);

                                        $n = mb_strtolower(trim((string) ($srv->nombre ?? '')));
                                        $icon = 'fa-solid fa-circle-plus';
                                        if (str_contains($n, 'silla')) {
                                            $icon = 'fa-solid fa-baby-carriage';
                                        } elseif (str_contains($n, 'conductor')) {
                                            $icon = 'fa-solid fa-user-plus';
                                        } elseif (str_contains($n, 'gasolina')) {
                                            $icon = 'fa-solid fa-gas-pump';
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
                                                <h4 class="addon-name">{{ $srv->nombre }}</h4>
                                                @if (!empty($srv->descripcion))
                                                    <p>{{ $srv->descripcion }}</p>
                                                @endif
                                            </div>
                                        </div>

                                        <div class="addon-price">
                                            @if($srv->tipo_cobro === 'por_tanque')
                                                <strong>Cantidad de un tanque</strong>
                                            @else
                                                <strong>${{ $precio }}</strong> MXN / evento
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
    <span class="qty-hint">Máx 3</span>
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
  background-color: #c22;
}

input:checked + .slider:before {
  transform: translateX(20px);
}
</style>
                                    </div>
                                @empty
                                    <div style="grid-column:1/-1; text-align:center; padding:.75rem 0;">
                                        No hay complementos disponibles por ahora.
                                    </div>
                                @endforelse
                            </div>
                        </section>

                    </div>

                    <div class="wizard-nav">
                        <a class="btn btn-ghost" href="{{ $toStep(2) }}">Anterior</a>
                        <a class="btn btn-primary" id="toStep4" href="{{ $toStep(4) }}">Siguiente</a>
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

                        /* DISEÑO RESPONSIVO: TARJETA DE RESERVACIÓN  */

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

                            .movil-footer-sticky {
                                display: flex;
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
                                color: var(--brand);
                                font-size: 24px;
                            }

                            .btn-reservar-movil {
                                background: var(--brand);
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

                            /* 2. EL AJUSTE PARA EL SCROLL*/
                            .step4-layout {
                                padding-bottom: 250px !important;
                            }

                            body::after {
                                content: "";
                                display: block;
                                height: 160px;
                                width: 100%;
                            }
                        }

                        /* ESCRITORIO */
                        @media (min-width:1025px) {
                            .movil-footer-sticky {
                                display: none !important;
                            }
                        }
                    </style>

                    <div class="step4-layout">
                        @php
                            $months3 = [
                                '01' => 'ENE',
                                '02' => 'FEB',
                                '03' => 'MAR',
                                '04' => 'ABR',
                                '05' => 'MAY',
                                '06' => 'JUN',
                                '07' => 'JUL',
                                '08' => 'AGO',
                                '09' => 'SEP',
                                '10' => 'OCT',
                                '11' => 'NOV',
                                '12' => 'DIC',
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

                                <h2 class="sum-section-title"> Datos personales</h2>

                                <div class="sum-personal-grid">

                                    {{-- Nombre Completo --}}
                                    <div class="field field-floating" style="grid-column: 1 / -1;">
                                        <input type="text" class="input-centered" name="nombre_completo"
                                            id="nombreCompleto" autocomplete="name" placeholder=" " required>
                                        <label for="nombreCompleto">Nombre Completo</label>
                                        <input type="hidden" name="nombre" id="nombreCliente">
                                        <input type="hidden" name="apellido" id="apellidoCliente">
                                    </div>

                                    {{-- Móvil --}}
                                    <div class="field field-floating" style="grid-column: 1 / -1;">
                                        <input type="text" name="telefono" id="telefonoCliente" placeholder=" "
                                            required>
                                        <label for="telefonoCliente">Móvil</label>
                                    </div>

                                    {{-- Correo electrónico --}}
                                    <div class="field field-floating" style="grid-column: 1 / -1;">
                                        <input type="email" name="email" id="correoCliente" placeholder=" "
                                            required>
                                        <label for="correoCliente">Correo electrónico</label>
                                    </div>

                                    {{-- País --}}
                                   @php
                                $paisesPrioritarios = [
                                    'México' => 'México',
                                    'Estados Unidos' => 'Estados Unidos',
                                    'Canadá' => 'Canadá'
                                ];

                                $todosPaises = [
                                    'Afganistán' => 'Afganistán',
                                    'Albania' => 'Albania',
                                    'Alemania' => 'Alemania',
                                    'Andorra' => 'Andorra',
                                    'Angola' => 'Angola',
                                    'Antigua y Barbuda' => 'Antigua y Barbuda',
                                    'Arabia Saudita' => 'Arabia Saudita',
                                    'Argelia' => 'Argelia',
                                    'Argentina' => 'Argentina',
                                    'Armenia' => 'Armenia',
                                    'Australia' => 'Australia',
                                    'Austria' => 'Austria',
                                    'Azerbaiyán' => 'Azerbaiyán',
                                    'Bahamas' => 'Bahamas',
                                    'Bangladés' => 'Bangladés',
                                    'Barbados' => 'Barbados',
                                    'Baréin' => 'Baréin',
                                    'Bélgica' => 'Bélgica',
                                    'Belice' => 'Belice',
                                    'Benín' => 'Benín',
                                    'Bielorrusia' => 'Bielorrusia',
                                    'Birmania' => 'Birmania',
                                    'Bolivia' => 'Bolivia',
                                    'Bosnia y Herzegovina' => 'Bosnia y Herzegovina',
                                    'Botsuana' => 'Botsuana',
                                    'Brasil' => 'Brasil',
                                    'Brunéi' => 'Brunéi',
                                    'Bulgaria' => 'Bulgaria',
                                    'Burkina Faso' => 'Burkina Faso',
                                    'Burundi' => 'Burundi',
                                    'Bután' => 'Bután',
                                    'Cabo Verde' => 'Cabo Verde',
                                    'Camboya' => 'Camboya',
                                    'Camerún' => 'Camerún',
                                    'Chad' => 'Chad',
                                    'Chile' => 'Chile',
                                    'China' => 'China',
                                    'Chipre' => 'Chipre',
                                    'Colombia' => 'Colombia',
                                    'Comoras' => 'Comoras',
                                    'Costa de Marfil' => 'Costa de Marfil',
                                    'Costa Rica' => 'Costa Rica',
                                    'Croacia' => 'Croacia',
                                    'Cuba' => 'Cuba',
                                    'Dinamarca' => 'Dinamarca',
                                    'Dominica' => 'Dominica',
                                    'Ecuador' => 'Ecuador',
                                    'Egipto' => 'Egipto',
                                    'El Salvador' => 'El Salvador',
                                    'Emiratos Árabes Unidos' => 'Emiratos Árabes Unidos',
                                    'Eritrea' => 'Eritrea',
                                    'Eslovaquia' => 'Eslovaquia',
                                    'Eslovenia' => 'Eslovenia',
                                    'España' => 'España',
                                    'Estonia' => 'Estonia',
                                    'Etiopía' => 'Etiopía',
                                    'Filipinas' => 'Filipinas',
                                    'Finlandia' => 'Finlandia',
                                    'Fiyi' => 'Fiyi',
                                    'Francia' => 'Francia',
                                    'Gabón' => 'Gabón',
                                    'Gambia' => 'Gambia',
                                    'Georgia' => 'Georgia',
                                    'Ghana' => 'Ghana',
                                    'Granada' => 'Granada',
                                    'Grecia' => 'Grecia',
                                    'Guatemala' => 'Guatemala',
                                    'Guyana' => 'Guyana',
                                    'Guinea' => 'Guinea',
                                    'Guinea-Bisáu' => 'Guinea-Bisáu',
                                    'Guinea Ecuatorial' => 'Guinea Ecuatorial',
                                    'Haití' => 'Haití',
                                    'Honduras' => 'Honduras',
                                    'Hungría' => 'Hungría',
                                    'India' => 'India',
                                    'Indonesia' => 'Indonesia',
                                    'Irak' => 'Irak',
                                    'Irán' => 'Irán',
                                    'Irlanda' => 'Irlanda',
                                    'Islandia' => 'Islandia',
                                    'Islas Marshall' => 'Islas Marshall',
                                    'Islas Salomón' => 'Islas Salomón',
                                    'Israel' => 'Israel',
                                    'Italia' => 'Italia',
                                    'Jamaica' => 'Jamaica',
                                    'Japón' => 'Japón',
                                    'Jordania' => 'Jordania',
                                    'Kazajistán' => 'Kazajistán',
                                    'Kenia' => 'Kenia',
                                    'Kirguistán' => 'Kirguistán',
                                    'Kiribati' => 'Kiribati',
                                    'Kuwait' => 'Kuwait',
                                    'Laos' => 'Laos',
                                    'Lesoto' => 'Lesoto',
                                    'Letonia' => 'Letonia',
                                    'Líbano' => 'Líbano',
                                    'Liberia' => 'Liberia',
                                    'Libia' => 'Libia',
                                    'Liechtenstein' => 'Liechtenstein',
                                    'Lituania' => 'Lituania',
                                    'Luxemburgo' => 'Luxemburgo',
                                    'Madagascar' => 'Madagascar',
                                    'Malasia' => 'Malasia',
                                    'Malaui' => 'Malaui',
                                    'Maldivas' => 'Maldivas',
                                    'Malí' => 'Malí',
                                    'Malta' => 'Malta',
                                    'Marruecos' => 'Marruecos',
                                    'Mauricio' => 'Mauricio',
                                    'Mauritania' => 'Mauritania',
                                    'Micronesia' => 'Micronesia',
                                    'Moldavia' => 'Moldavia',
                                    'Mónaco' => 'Mónaco',
                                    'Mongolia' => 'Mongolia',
                                    'Montenegro' => 'Montenegro',
                                    'Mozambique' => 'Mozambique',
                                    'Namibia' => 'Namibia',
                                    'Nauru' => 'Nauru',
                                    'Nepal' => 'Nepal',
                                    'Nicaragua' => 'Nicaragua',
                                    'Níger' => 'Níger',
                                    'Nigeria' => 'Nigeria',
                                    'Noruega' => 'Noruega',
                                    'Nueva Zelanda' => 'Nueva Zelanda',
                                    'Omán' => 'Omán',
                                    'Países Bajos' => 'Países Bajos',
                                    'Pakistán' => 'Pakistán',
                                    'Palaos' => 'Palaos',
                                    'Palestina' => 'Palestina',
                                    'Panamá' => 'Panamá',
                                    'Papúa Nueva Guinea' => 'Papúa Nueva Guinea',
                                    'Paraguay' => 'Paraguay',
                                    'Perú' => 'Perú',
                                    'Polonia' => 'Polonia',
                                    'Portugal' => 'Portugal',
                                    'Qatar' => 'Qatar',
                                    'Reino Unido' => 'Reino Unido',
                                    'República Centroafricana' => 'República Centroafricana',
                                    'República Checa' => 'República Checa',
                                    'República del Congo' => 'República del Congo',
                                    'República Democrática del Congo' => 'República Democrática del Congo',
                                    'República Dominicana' => 'República Dominicana',
                                    'Ruanda' => 'Ruanda',
                                    'Rumanía' => 'Rumanía',
                                    'Rusia' => 'Rusia',
                                    'Samoa' => 'Samoa',
                                    'San Cristóbal y Nieves' => 'San Cristóbal y Nieves',
                                    'San Marino' => 'San Marino',
                                    'San Vicente y las Granadinas' => 'San Vicente y las Granadinas',
                                    'Santa Lucía' => 'Santa Lucía',
                                    'Santo Tomé y Príncipe' => 'Santo Tomé y Príncipe',
                                    'Senegal' => 'Senegal',
                                    'Serbia' => 'Serbia',
                                    'Seychelles' => 'Seychelles',
                                    'Sierra Leona' => 'Sierra Leona',
                                    'Singapur' => 'Singapur',
                                    'Siria' => 'Siria',
                                    'Somalia' => 'Somalia',
                                    'Sri Lanka' => 'Sri Lanka',
                                    'Suazilandia' => 'Suazilandia',
                                    'Sudáfrica' => 'Sudáfrica',
                                    'Sudán' => 'Sudán',
                                    'Sudán del Sur' => 'Sudán del Sur',
                                    'Suecia' => 'Suecia',
                                    'Suiza' => 'Suiza',
                                    'Surinam' => 'Surinam',
                                    'Tailandia' => 'Tailandia',
                                    'Tanzania' => 'Tanzania',
                                    'Tayikistán' => 'Tayikistán',
                                    'Timor Oriental' => 'Timor Oriental',
                                    'Togo' => 'Togo',
                                    'Tonga' => 'Tonga',
                                    'Trinidad y Tobago' => 'Trinidad y Tobago',
                                    'Túnez' => 'Túnez',
                                    'Turkmenistán' => 'Turkmenistán',
                                    'Turquía' => 'Turquía',
                                    'Tuvalu' => 'Tuvalu',
                                    'Ucrania' => 'Ucrania',
                                    'Uganda' => 'Uganda',
                                    'Uruguay' => 'Uruguay',
                                    'Uzbekistán' => 'Uzbekistán',
                                    'Vanuatu' => 'Vanuatu',
                                    'Vaticano' => 'Vaticano',
                                    'Venezuela' => 'Venezuela',
                                    'Vietnam' => 'Vietnam',
                                    'Yemen' => 'Yemen',
                                    'Yibuti' => 'Yibuti',
                                    'Zambia' => 'Zambia',
                                    'Zimbabue' => 'Zimbabue'
                                ];

                                // Ordenar alfabéticamente
                                ksort($todosPaises);
                            @endphp

                            {{-- País --}}
                            <div class="field field-floating">
                                <select name="pais" id="pais" required>
                                    <option value="" disabled selected>Selecciona un país</option>

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
                                <label for="pais">País</label>
                            </div>

                                    {{-- Fecha de nacimiento --}}
                                    <div class="field field-dob-container">
                                        <label class="label-dob-main">Fecha de nacimiento</label> {{-- ESTE LABEL ES IMPORTANTE --}}
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
                                            <label for="vuelo">No. de vuelo</label>
                                        </div>
                                    @endif

                                </div>

                                <div class="sum-checks">
                                    <label class="cbox">
                                        <input type="checkbox" name="acepto" id="acepto">
                                        <span>
                                            ESTOY DE ACUERDO Y ACEPTO
                                            <a href="{{ route('rutaPoliticas') }}" class="link-politicas"
                                                target="_blank" rel="noopener">
                                                LAS POLÍTICAS
                                            </a>
                                            Y PROCEDIMIENTOS PARA LA RENTA.
                                        </span>
                                    </label>

                                    <label class="cbox">
                                        <input type="checkbox" name="promos" id="promos">
                                        <span>DESEO RECIBIR ALERTAS, CONFIRMACIONES, OFERTAS Y PROMOCIONES EN MI CORREO Y/O
                                            TELÉFONO CELULAR.</span>
                                    </label>
                                </div>

                                <div class="wizard-nav" style="margin-top:10px;">
                                    <button id="btnReservar" type="button" class="btn btn-primary">Reservar</button>
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
                                    <div class="modal-card">
                                        <h3>Selecciona tu método de pago</h3>
                                        <div class="options">
                                            <button id="btnPagoLinea" class="btn btn-primary" type="button">Pago en
                                                línea</button>
                                            <button id="btnPagoMostrador" class="btn btn-gray" type="button">Pago en
                                                mostrador</button>
                                        </div>
                                        <button id="cerrarModalMetodo" class="btn btn-secondary" type="button"
                                            style="margin-top:10px;">Cancelar</button>
                                    </div>
                                </div>

                                <div id="paypal-button-container"
                                    style="display:none; text-align:center; margin-top:20px;"></div>
                            </form>
                        </div>

                        {{-- ===================== PANE DERECHO ===================== --}}
                        <div class="step4-pane">

                            <div class="sum-compact" aria-label="Resumen compacto">
                                <div class="sum-compact-head">
                                    <h4 class="sum-title"><strong>Resumen de tu reserva</strong></h4>

                                    <span class="sum-days">
                                        <i class="fa-regular fa-calendar"></i>
                                        Días: <strong id="qDays">{{ $days }}</strong>
                                    </span>
                                </div>

                                <h4 class="sum-subtitle">Lugar y fecha</h4>

                                <div class="sum-compact-grid">

                                    {{-- PICKUP --}}
                                    <div class="sum-item">
                                        <div class="sum-item-label">
                                            <i class="fa-solid fa-location-dot"></i> Pick-Up
                                        </div>

                                        <div class="sum-item-value">
                                            <strong class="sum-place">{{ $pickupName ?? '—' }}</strong>

                                            <div class="sum-dt2">
                                                <div class="dt-row">
                                                    <span class="dt-lbl">Fecha</span>
                                                    <span class="dt-val">{{ $pickupFechaLarga ?? $pickupDate }}</span>
                                                </div>
                                                <div class="dt-row">
                                                    <span class="dt-lbl">Hora</span>
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
                                            <i class="fa-solid fa-location-dot"></i> Devolución
                                        </div>

                                        <div class="sum-item-value">
                                            <strong class="sum-place">{{ $dropoffName ?? '—' }}</strong>

                                            <div class="sum-dt2">
                                                <div class="dt-row">
                                                    <span class="dt-lbl">Fecha</span>
                                                    <span class="dt-val">{{ $dropoffFechaLarga ?? $dropoffDate }}</span>
                                                </div>
                                                <div class="dt-row">
                                                    <span class="dt-lbl">Hora</span>
                                                    <span class="dt-time">{{ $dropoffTime }} HRS</span>
                                                </div>
                                            </div>

                                            <span class="js-date" data-iso="{{ $dropoffDateISO }}"
                                                style="display:none;">{{ $dropoffDate }}</span>
                                        </div>
                                    </div>

                                </div>

                                <h4 class="sum-subtitle" style="margin-top:14px;" id="tuAutoSection">Tu auto</h4>

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
                                            $transmision = $categoriaSel->id_categoria == 9 ? 'Estándar' : 'Automática';
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

                                                <li title="Transmisión">
                                                    <span class="spec-letter">T | {{ $transmision }}</span>
                                                </li>

                                                @if ($tieneACCat)
                                                    <li title="Aire acondicionado">
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

                            <h4 class="sum-subtitle" style="margin-top:16px;">Detalles del precio</h4>

                            <div class="sum-table" id="cotizacionDoc"
                            data-base="{{ $tarifaBase }}"
                            data-days="{{ $days }}"
                            data-pickup="{{ $pickupSucursalId }}"
                            data-dropoff="{{ $dropoffSucursalId }}"
                            data-km="{{ $dropoffKm }}"
                            data-costokm="{{ $costoKmCategoria }}">

                                {{-- ===== TARIFA BASE (desplegable) ===== --}}
                                <details class="sum-acc" open="false">
                                    <summary class="sum-bar">
                                        <span>Tarifa base</span>
                                        <strong id="qBase">${{ number_format($tarifaBase, 0) }} MXN</strong>
                                        <i class="sum-caret fa-solid fa-chevron-down" aria-hidden="true"></i>
                                    </summary>

                                    <div class="sum-acc-body">
                                        <div class="row row-base">
                                            <span>{{ $days }} día(s) - precio por día
                                                ${{ number_format((float) ($categoriaSel->precio_dia ?? 0), 0) }}
                                                MXN</span>
                                        </div>

                                        <div class="row row-base-total">
                                            <span class="row-total-label">Total:</span>
                                            <strong>${{ number_format($tarifaBase, 0) }} MXN</strong>
                                        </div>

                                        {{-- Modal de protecciones --}}
                                        <div id="modalProtecciones" class="modal-global-viajero">
                                            <div class="modal-global-content">
                                                <span class="cerrar-modal-v">&times;</span>

                                                <h2 class="modal-v-header-title">Relevos de responsabilidad (Protecciones)
                                                </h2>
                                                <hr style="border: 0; border-top: 1px solid #f1f5f9; margin: 20px 0;">

                                                <div style="display: flex; gap: 20px; align-items: flex-start;">
                                                    <div class="modal-v-escudo-circulo">
                                                        <i class="fa-solid fa-shield" style="font-size: 28px;"></i>
                                                    </div>

                                                    <div>
                                                        <strong class="modal-v-titulo-negro">PROTECCIÓN LIMITADA DE
                                                            RESPONSABILIDAD HACIA TERCEROS (LI)</strong>
                                                        <p class="modal-v-texto-gris">
                                                            Protege a terceros por daños y perjuicios ocasionados en un
                                                            accidente y cubre la cantidad mínima requerida por ley.
                                                        </p>
                                                    </div>
                                                </div>

                                                <div
                                                    style="margin-top: 30px; padding-top: 20px; border-top: 1px solid #f1f5f9;">
                                                    <p
                                                        style="font-size: 13px; color: #64748b; line-height: 1.5; margin-bottom: 12px;">
                                                        Tú eliges el nivel de responsabilidad sobre el auto que más vaya
                                                        acorde a tus necesidades y presupuesto.
                                                    </p>
                                                    <p style="font-size: 13px; color: #1e293b; font-weight: 700;">
                                                        Pregunta por nuestros Relevos de responsabilidad (opcionales) al
                                                        llegar al mostrador de cualquiera de nuestras oficinas.
                                                    </p>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="col-12 mt-2">
                                            <div class="linea-incluido-box">
                                                <p class="incluido-text">
                                                    <strong>INCLUIDO</strong>
                                                    <i class="fa-solid fa-circle-question" id="info-protecciones"
                                                        style="cursor: pointer; color: #b22222; margin-left: 5px; font-size: 1.1rem; vertical-align: middle;"></i>
                                                </p>
                                            </div>
                                        </div>
                                        <div class="row row-included" style="border-top:0;">
                                            <span class="inc-items">
                                                <span class="inc-item"><span class="inc-check">✔</span> Kilometraje
                                                    ilimitado</span>
                                                <span class="inc-item"><span class="inc-check">✔</span> Reelevo de
                                                    Responsabilidad (LI)</span>
                                            </span>
                                        </div>
                                    </div>
                                </details>

                                {{-- ===== OPCIONES DE RENTA (desplegable) ===== --}}
                                <details class="sum-acc">
                                    <summary class="sum-bar">
                                        <span>Opciones de renta</span>
                                        <strong id="qExtras">$0 MXN</strong>
                                        <i class="sum-caret fa-solid fa-chevron-down" aria-hidden="true"></i>
                                    </summary>

                                    <div class="sum-acc-body" id="extrasList">
                                        <div class="row">
                                            <span class="muted">Sin complementos seleccionados</span>
                                            <strong>$0 MXN</strong>
                                        </div>
                                    </div>
                                </details>
 {{-- ===== Editar  ===== --}}
                                {{-- ===== CARGOS E IVA (desplegable) ===== --}}
                                <details class="sum-acc">
                                    <summary class="sum-bar">
                                        <span>Cargos e IVA (16%)</span>
                                        <strong id="qIva">$0 MXN</strong>
                                        <i class="sum-caret fa-solid fa-chevron-down" aria-hidden="true"></i>
                                    </summary>

                                    <div class="sum-acc-body" id="ivaList">
                                        <div class="row">
                                            <span class="muted">Sin cargos adicionales</span>
                                            <strong>$0 MXN</strong>
                                        </div>
                                    </div>
                                </details>

                                {{-- ===== TOTAL ===== --}}
                                <div class="sum-total">
                                    <span>Total</span>
                                    <strong id="qTotal">${{ number_format($tarifaBase, 0) }} MXN</strong>
                                </div>

                            </div>

                        </div>
                    </div>

                    @isset($servicios)
    <script id="addonsCatalog" type="application/json">
{!! json_encode(
  collect($servicios)->where('activo', true)->mapWithKeys(fn($s) => [
    $s->id_servicio => [
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
                <span class="movil-total-label">Total</span>
                <span id="qTotalMovil" class="movil-total-amount">
                    ${{ number_format($tarifaBase, 0) }} MXN
                </span>
            </div>
            <button type="button" id="btnReservarMovil" class="btn-reservar-movil">
                Reservar
            </button>
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

    {{-- modal: si plan=mostrador abre selector; si plan=linea fuerza pago línea --}}
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const main = document.querySelector('main.page');
            const currentPlan = (main && main.dataset.plan) ? main.dataset.plan : '';

            const modalMetodoPago = document.getElementById('modalMetodoPago');
            const cerrarModalMetodo = document.getElementById('cerrarModalMetodo');
            const btnPagoLinea = document.getElementById('btnPagoLinea');
            const btnPagoMostrador = document.getElementById('btnPagoMostrador');

            // Escuchar el evento de validación exitosa (disparado desde reservaciones.js)
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
                            'Selecciona un tipo de pago desde el paso de categoría (Mostrador o Prepago).'
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

            // ✅ Por si quieres FORZAR que "Tarifa base" inicie CERRADO:
            const tarifa = document.querySelector('.sum-table details.sum-acc');
            if (tarifa && tarifa.hasAttribute('open')) tarifa.removeAttribute('open');
        });
    </script>
