@extends('layouts.Usuarios')
@section('Titulo', __('Reservations'))

@section('css-vistaReservaciones')
    <link rel="stylesheet" href="{{ asset('css/reservaciones.css') }}">

    <style>
        /* fondos de reservaciones */

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

        .page.wizard-page:not([data-current-step="1"]) .wizard-form .field label {
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


        /* step 3 */

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
            position: fixed !important;
            inset: 0 !important;
            display: none;
            align-items: center;
            justify-content: center;
            background: rgba(0, 0, 0, 0.8) !important;
            z-index: 99999999 !important;
            padding: 18px;
            backdrop-filter: blur(5px);
            overscroll-behavior: contain;
        }

        .modal-s3 .card {
            width: min(720px, 100%);
            background: #ffffff !important;
            border-radius: 18px;
            border: 1px solid #eef2f7;
            box-shadow: 0 25px 60px rgba(0, 0, 0, .5);
            padding: 18px;
            position: relative;
            z-index: 100000000 !important;
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


        /* addon-qty */

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

        input:checked+.slider {
            background-color: #16a34a;
        }

        input:checked+.slider:before {
            transform: translateX(20px);
        }

        input:checked+.slider:before {
            transform: translateX(20px);
        }


        /* step 4 */

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

        /*  letra mayúscula en los mesess */
        #dob_month option {
            text-transform: capitalize !important;
        }
    </style>

@endsection

@section('contenidoReservaciones')

    <main class="page wizard-page {{ $fromWelcome ? 'modo-welcome' : '' }}" data-current-step="{{ $step }}"
        data-plan="{{ $plan ?? '' }}" style="position:relative; overflow:visible;">

        {{-- ✅ Fondo SOLO dentro del main (NO footer) --}}
        <div class="fondos-reservaciones"
            style="background-image: url('../img/banner/banner-reservaciones.webp'); background-attachment: fixed; background-size: cover; background-position: center; min-height: 100vh;">

            <div class="fondo-fijo-layout" style="pointer-events:none;"></div>

            {{-- ===================== PASOS ARRIBA ===================== --}}
            <nav class="wizard-steps" aria-label="{{ __('Steps') }}">
                <a class="wizard-step {{ $step > 1 ? 'done' : '' }} {{ $step === 1 ? 'active' : '' }}"
                    href="{{ route('rutaReservasIniciar', array_merge($baseParams, ['step' => 1])) }}">
                    <span class="n">1</span> {{ __('General') }}
                </a>
                <a class="wizard-step {{ $step > 2 || request('auto') ? 'done' : '' }} {{ $step === 2 ? 'active' : '' }}"
                    href="{{ route('rutaReservasIniciar', array_merge($baseParams, ['step' => 2])) }}">
                    <span class="n">2</span> {{ __('Category') }}
                </a>
                <a class="wizard-step {{ $step > 3 ? 'done' : '' }} {{ $step === 3 ? 'active' : '' }}"
                    href="{{ route('rutaReservasIniciar', array_merge($baseParams, ['step' => 3])) }}">
                    <span class="n">3</span> {{ __('Extras') }}
                </a>
                <a class="wizard-step {{ $step === 4 ? 'active' : '' }}"
                    href="{{ route('rutaReservasIniciar', array_merge($baseParams, ['step' => 4])) }}">
                    <span class="n">4</span> {{ __('Confirmation') }}
                </a>
            </nav>

            <section class="wizard-card">

                {{-- ===================== STEP 1 ===================== --}}

                @if ($stepCurrent === 1)
                    <div class="search-card" id="miBuscador">
                        <header class="wizard-head">
                            <h2>{{ __('About your reservation') }}</h2>
                        </header>

                        <form method="GET" action="{{ route('rutaReservasIniciar') }}" class="search-form" id="step1Form"
                            novalidate>
                            <input type="hidden" name="step" value="2">
                            @if (!empty($addonsParam))
                                <input type="hidden" name="addons" value="{{ $addonsParam }}">
                            @endif

                            <div class="search-grid">
                                {{-- COLUMNA 1: LUGAR DE RENTA --}}
                                <div class="sg-col sg-col-location">
                                    <div class="location-head">
                                        <span class="field-title">{{ __('Pick-up location') }}</span>
                                        <label class="inline-check" for="differentDropoff">
                                            <input type="checkbox" id="differentDropoff" name="different_dropoff"
                                                value="1" {{ $isDifferentDropoff ? 'checked' : '' }}>
                                            <span class="checkbox-text">{{ __('Different return location') }}</span>
                                        </label>
                                    </div>

                                    <div class="location-inputs-wrapper" id="locationInputsWrapper">
                                        {{-- SELECT PICKUP --}}
                                        <div class="field icon-field" id="pickupField">
                                            <span class="field-icon">
                                                <i id="pickupIcon" class="fa-solid fa-location-dot"></i>
                                            </span>
                                            <select id="pickupPlace" name="pickup_sucursal_id" required>
                                                <option value="" disabled {{ $pickupSucursalId ? '' : 'selected' }}>
                                                    {{ __('Where does your trip begin?') }}
                                                </option>

                                                {{-- Solo itera Querétaro --}}
                                                @foreach ($ciudadesPickup as $ciudad)
                                                    <optgroup
                                                        label="{{ $ciudad->nombre }}{{ $ciudad->estado ? ' — ' . $ciudad->estado : '' }}">
                                                        @foreach ($ciudad->sucursalesActivas as $suc)
                                                            <option value="{{ $suc->id_sucursal }}"
                                                                data-icon="{{ $suc->icon_class }}"
                                                                {{ (string) $pickupSucursalId === (string) $suc->id_sucursal ? 'selected' : '' }}>
                                                                {{ $suc->nombre }}
                                                            </option>
                                                        @endforeach
                                                    </optgroup>
                                                @endforeach
                                            </select>
                                        </div>

                                        {{-- SELECT DROPOFF --}}
                                        <div class="field icon-field" id="dropoffWrapper"
                                            style="display: {{ $isDifferentDropoff ? 'block' : 'none' }};">
                                            <span class="field-icon">
                                                <i id="dropoffIcon" class="fa-solid fa-location-dot"></i>
                                            </span>
                                            <select id="dropoffPlace" name="dropoff_sucursal_id">
                                                <option value="" disabled {{ $dropoffSucursalId ? '' : 'selected' }}>
                                                    {{ __('Where does your trip end?') }}
                                                </option>

                                                {{-- Itera todas las ciudades (con Querétaro al principio) --}}
                                                @foreach ($ciudadesDropoff as $ciudad)
                                                    <optgroup
                                                        label="{{ $ciudad->nombre }}{{ $ciudad->estado ? ' — ' . $ciudad->estado : '' }}">
                                                        @foreach ($ciudad->sucursalesActivas as $suc)
                                                            <option value="{{ $suc->id_sucursal }}"
                                                                data-icon="{{ $suc->icon_class }}"
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

                                {{-- COLUMNA 2: PICKUP DATETIME --}}
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
                                                <select name="pickup_h" required class="time-select">
                                                    <option value="" disabled {{ empty($ph) ? 'selected' : '' }}>
                                                        {{ __('Time') }}</option>
                                                    @foreach ($horasDropdown as $hh)
                                                        <option value="{{ $hh }}"
                                                            {{ $hh === $ph ? 'selected' : '' }}>{{ $hh }}:00
                                                        </option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                {{-- COLUMNA 3: DROPOFF DATETIME --}}
                                <div class="sg-col sg-col-datetime">
                                    <div class="field">
                                        <span class="field-title solo-responsivo-izq">{{ __('Return') }}</span>
                                        <div class="datetime-row">
                                            <div class="dt-field icon-field">
                                                <span class="field-icon"><i
                                                        class="fa-regular fa-calendar-days"></i></span>
                                                <input id="end" name="dropoff_date" type="text"
                                                    placeholder="{{ __('Date') }}" class="flatpickr-input"
                                                    value="{{ $dropoffDate }}" required>
                                            </div>
                                            <div class="dt-field icon-field time-field">
                                                <span class="field-icon"><i class="fa-regular fa-clock"></i></span>
                                                <select name="dropoff_h" required class="time-select">
                                                    <option value="" disabled {{ empty($dh) ? 'selected' : '' }}>
                                                        {{ __('Time') }}</option>
                                                    @foreach ($horasDropdown as $hh)
                                                        <option value="{{ $hh }}"
                                                            {{ $hh === $dh ? 'selected' : '' }}>{{ $hh }}:00
                                                        </option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="sg-col sg-col-submit">
                                    <div class="actions">
                                        <button type="submit" class="btn btn-primary">{{ __('Next') }}</button>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                @endif

                {{-- ===================== STEP 2 ===================== --}}

                @if ($step === 2)
                    <header class="wizard-head">
                        <h2>{{ __('Select your category') }}</h2>
                        <p>{{ __('Rate for') }} <strong id="daysLabel">{{ $days }}</strong>
                            {{ __('day(s) of your rental.') }}</p>
                    </header>

                    <div class="cars">
                        @forelse($categorias as $cat)
                            <article
                                class="car-card car-card--v2 {{ (string) request('categoria_id') === (string) $cat->id_categoria ? 'active' : '' }}"
                                data-prepago-dia="{{ $cat->precio_dia }}"
                                data-mostrador-dia="{{ round($cat->precio_dia * 1.25) }}"
                                data-price-mxn="{{ $cat->prepago_total }}"
                                data-old-price-mxn="{{ $cat->mostrador_total }}">

                                <div class="car-body">
                                    <div class="car-header-row">
                                        <div class="car-top">{{ strtoupper($cat->nombre) }}</div>
                                        <div class="car-days-badge car-days-badge--v2">
                                            <i class="fa-regular fa-calendar-days"></i>
                                            <span class="js-days-badge">{{ $days }}</span> {{ __('day(s)') }}
                                        </div>
                                    </div>

                                    <div class="car-sub">{{ $cat->descripcion ?? __('Car or similar.') }}</div>

                                    <div class="car-hero">
                                        <img class="car-hero-img" src="{{ $cat->img_url }}" alt="{{ $cat->nombre }}">
                                    </div>

                                    <div class="car-features">
                                        <ul class="car-mini-specs">
                                            <li title="{{ __('Passengers') }}"><i class="fa-solid fa-user-large"></i>
                                                {{ $cat->pax }}</li>
                                            <li title="{{ __('Small suitcases') }}"><i
                                                    class="fa-solid fa-suitcase-rolling"></i> {{ $cat->s_luggage }}</li>
                                            <li title="{{ __('Large suitcases') }}"><i class="fa-solid fa-briefcase"></i>
                                                {{ $cat->b_luggage }}</li>
                                            <li title="{{ __('Transmission') }}"><span class="spec-letter">T |
                                                    {{ $cat->transmision_txt }}</span></li>

                                            {{-- Aire acondicionado --}}
                                            @if ($cat->tiene_ac)
                                                <li title="{{ __('Air conditioning') }}"><i
                                                        class="fa-regular fa-snowflake"></i> <span
                                                        class="spec-letter">A/C</span></li>
                                            @endif
                                        </ul>

                                        {{-- Conectividad (Apple CarPlay y Android Auto) --}}
                                        <div class="car-connect">
                                            @if ($cat->tiene_carplay)
                                                <span class="badge-chip badge-apple" title="Apple CarPlay">
                                                    <span class="icon-badge"><i class="fa-brands fa-apple"></i></span>
                                                    CarPlay
                                                </span>
                                            @endif

                                            @if ($cat->tiene_android)
                                                <span class="badge-chip badge-android" title="Android Auto">
                                                    <span class="icon-badge"><i class="fa-brands fa-android"></i></span>
                                                    Android Auto
                                                </span>
                                            @endif
                                        </div>
                                    </div>

                                    <div class="car-price car-price--v2">
                                        <div class="price-old">${{ number_format($cat->mostrador_total, 0) }} MXN</div>
                                        <div class="price-new">$<span
                                                class="js-prepago-total">{{ number_format($cat->prepago_total, 0) }}</span>
                                            MXN</div>

                                        @if ($cat->ahorro_pct > 0)
                                            <div class="price-save">{{ __('Save') }} <strong
                                                    class="js-ahorro">{{ $cat->ahorro_pct }}</strong>%</div>
                                        @endif

                                        <a class="btn-pay primary"
                                            href="{{ route('rutaReservasIniciar', array_merge($baseParams, ['step' => 3, 'categoria_id' => $cat->id_categoria, 'plan' => 'linea'])) }}">
                                            {{ __('PREPAY ONLINE') }}
                                        </a>

                                        <div class="office-wrap">
                                            <div class="office-price">$<span
                                                    class="js-mostrador-total">{{ number_format($cat->mostrador_total, 0) }}</span>
                                                MXN</div>
                                            <a class="btn-pay gray"
                                                href="{{ route('rutaReservasIniciar', array_merge($baseParams, ['step' => 3, 'categoria_id' => $cat->id_categoria, 'plan' => 'mostrador'])) }}">
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
                        <a class="btn btn-ghost"
                            href="{{ route('rutaReservasIniciar', array_merge($baseParams, ['step' => 1])) }}">{{ __('Previous') }}</a>
                        <a class="btn btn-primary"
                            href="{{ route('rutaReservasIniciar', array_merge($baseParams, ['step' => 3])) }}">{{ __('Next') }}</a>
                    </div>
                @endif

                {{-- ===================== STEP 3 ===================== --}}

                @if ($step === 3)
                    <header class="wizard-head">
                        <h2>{{ __('Select the additional options you want') }}</h2>
                        <p>{{ __('Review included protections and add extra equipment/services.') }}</p>
                    </header>

                    <input type="hidden" id="addonsHidden" value="{{ $filters['addons'] ?? '' }}">

                    <div class="step3-wrap">

                        {{-- SECCIÓN PROTECCIONES --}}
                        <section class="step3-section">
                            <div class="step3-title">{{ __('Liability waivers (Protections)') }}
                                <button type="button" class="step3-info" id="info-protecciones-step3"
                                    title="{{ __('More information') }}">
                                    <i class="fa-solid fa-circle-info"></i>
                                </button>
                            </div>

                            <div class="prot-grid">
                                {{-- Protección 1: Básica --}}
                                <div class="prot-card">
                                    <div class="prot-top">
                                        <div class="prot-icon is-on"><i class="fa-solid fa-shield"></i></div>
                                        <div>
                                            <p class="prot-name">
                                                {{ __('Limited third-party liability protection (LI)') }}</p>
                                            <p class="prot-desc">
                                                {{ __('Protects third parties for damages and injuries caused in an accident and covers the minimum amount required by law.') }}
                                            </p>
                                            <div class="prot-badge"><span class="dot"></span> {{ __('Included') }}
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                {{-- Protección 2: ADICIONAL --}}
                                <div class="prot-card">
                                    <div class="prot-top">
                                        <div class="prot-icon"><i class="fa-solid fa-shield-halved"></i></div>
                                        <div>
                                            <p class="prot-name">{{ __('Additional protections') }}</p>
                                            <p class="prot-desc">
                                                {{ __('You choose the level of liability for the vehicle that best fits your needs and budget. Ask about our waivers (optional) when you arrive at any of our branches.') }}
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            {{-- MODAL DE PROTECCIONES REINTEGRADO --}}
                            <div id="modalProteccionesStep3" class="modal-s3" aria-hidden="true">
                                <div class="card">
                                    <button type="button" class="x" id="closeProteccionesStep3"
                                        aria-label="{{ __('Close') }}">
                                        <i class="fa-solid fa-xmark"></i>
                                    </button>

                                    <h2 class="s3-modal-title">{{ __('Liability waivers (Protections)') }}</h2>
                                    <p class="s3-modal-sub">
                                        {{ __('Check the details of each package and what it includes.') }}</p>

                                    <div class="s3-body-scroll">
                                        <div class="s3-info-top">
                                            <p>
                                                <strong>{{ __('VIAJERO') }}</strong>
                                                {{ __('offers different types of optional Liability Waivers (Protections) available for an additional daily fee, which can be purchased when booking or on the rental day.') }}
                                            </p>
                                            <p>
                                                {{ __('The customer is responsible for any damage or theft of the VIAJERO vehicle subject to certain exclusions contained in the rental agreement. VIAJERO will waive or limit the customer\'s liability by purchasing any of these.') }}
                                            </p>
                                            <p style="margin-bottom:0;">
                                                {{ __('Customers booking using their Wizard Number will see the coverage and insurance preferences selected in their profile. You can also visit a branch or call') }}
                                                <strong>01 (442) 303 2668</strong> {{ __('for assistance.') }}
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
                                                    <li><strong>LDW:</strong>
                                                        {{ __('The customer is responsible for 0% deductible, bumper to bumper coverage no matter what happens to the car.') }}
                                                    </li>
                                                    <li><strong>PAI:</strong> {{ __('Medical expenses covered') }}
                                                        <strong>$250,000 MXN</strong> {{ __('per event.') }}
                                                    </li>
                                                    <li><strong>PRA:</strong>
                                                        {{ __('Premium roadside assistance. Includes: key or fuel delivery, car unlocking, flat tire change and jump start. Does not include key or fuel cost.') }}
                                                    </li>
                                                    <li><strong>LOU:</strong> {{ __('Loss of use, covered.') }}</li>
                                                    <li><strong>LA:</strong> {{ __('Legal assistance, covered.') }}</li>
                                                    <li><strong>LI:</strong> {{ __('Liability insurance up to') }}
                                                        <strong>$3,000,000 MXN</strong>.
                                                    </li>
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
                                                    <li><strong>PDW:</strong>
                                                        {{ __('Covers the entire bodywork at 5%, 10% for total loss or theft. Does not cover tires, accessories, rims or windows.') }}
                                                    </li>
                                                    <li><strong>PAI:</strong> {{ __('Medical expenses covered') }}
                                                        <strong>$250,000 MXN</strong> {{ __('per event.') }}
                                                    </li>
                                                    <li><strong>PRA (DECLINED):</strong>
                                                        {{ __('Premium Assistance: the customer is responsible for costs of: tow truck (if needed), impound lot, key or fuel delivery, car unlocking, flat tire change and jump start.') }}
                                                    </li>
                                                    <li><strong>LOU:</strong> {{ __('Loss of use, covered.') }}</li>
                                                    <li><strong>LA:</strong> {{ __('Legal assistance, covered.') }}</li>
                                                    <li><strong>ALI:</strong> {{ __('Liability insurance up to') }}
                                                        <strong>$1,000,000 MXN</strong>.
                                                    </li>
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
                                                    <li><strong>CDW 10%:</strong>
                                                        {{ __('The customer is responsible for 10% deductible on damages, 20% for total loss or theft based on invoice value.') }}
                                                    </li>
                                                    <li><strong>PAI:</strong> {{ __('Medical expenses covered') }}
                                                        <strong>$250,000 MXN</strong> {{ __('per event.') }}
                                                    </li>
                                                    <li><strong>PRA (DECLINED):</strong>
                                                        {{ __('Premium Assistance: the customer is responsible for costs of: tow truck (if needed), impound lot, key or fuel delivery, car unlocking, flat tire change and jump start.') }}
                                                    </li>
                                                    <li><strong>LOU:</strong> {{ __('Loss of use, covered.') }}</li>
                                                    <li><strong>LA:</strong> {{ __('Legal assistance, covered.') }}</li>
                                                    <li><strong>ALI:</strong> {{ __('Liability insurance up to') }}
                                                        <strong>$1,000,000 MXN</strong>.
                                                    </li>
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
                                                    <li><strong>CDW 20%:</strong>
                                                        {{ __('The customer is responsible for 20% deductible on damages, 30% for total loss or theft based on invoice value.') }}
                                                    </li>
                                                    <li><strong>PAI:</strong> {{ __('Medical expenses covered') }}
                                                        <strong>$250,000 MXN</strong> {{ __('per event.') }}
                                                    </li>
                                                    <li><strong>PRA (DECLINED):</strong>
                                                        {{ __('Premium Assistance: the customer is responsible for costs of: tow truck (if needed), impound lot, key or fuel delivery, car unlocking, flat tire change and jump start.') }}
                                                    </li>
                                                    <li><strong>LOU:</strong> {{ __('Loss of use, covered.') }}</li>
                                                    <li><strong>LA:</strong> {{ __('Legal assistance, covered.') }}</li>
                                                    <li><strong>LI:</strong> {{ __('Liability insurance up to') }}
                                                        <strong>$350,000 MXN</strong>.
                                                    </li>
                                                </ul>
                                            </div>
                                        </details>
                                    </div>
                                </div>
                            </div>
                        </section>

                        {{-- SECCIÓN EQUIPAMIENTO (Solo Silla, Gasolina y Conductor) --}}
                        <section class="step3-section">
                            <div class="step3-title">
                                {{ __('Equipment & Services') }}
                                <small>{{ __('max 3 per option') }}</small>
                            </div>

                            <div class="equip-grid">
                                @forelse($serviciosFiltrados as $srv)
                                    <div class="addon-card" data-id="{{ $srv->id_servicio }}"
                                        data-name="{{ $srv->nombre }}" data-price="{{ (float) $srv->precio }}"
                                        data-gasolina="{{ str_contains(strtolower($srv->nombre), 'prepaid fuel') ? 1 : 0 }}"
                                        data-charge="{{ $srv->tipo_cobro ?? 'por_evento' }}" data-max="3">

                                        <div class="addon-top">
                                            <div class="addon-ico"><i class="{{ $srv->icon }}"></i></div>
                                            <div style="flex:1;">
                                                <div class="addon-headline">
                                                    <h4 class="addon-name">{{ __($srv->nombre) }}</h4>

                                                    <span class="addon-help-wrap" tabindex="0">
                                                        <button type="button" class="addon-help-btn"
                                                            aria-label="{{ __('More information') }}">
                                                            <i class="fa-solid fa-info"></i>
                                                        </button>
                                                        <span
                                                            class="addon-tooltip">{{ __($srv->tooltip ?? 'Check more information about this add-on.') }}</span>
                                                    </span>
                                                </div>
                                                <p>{{ __($srv->descripcion) }}</p>
                                            </div>
                                        </div>

                                        <div class="addon-price">
                                            @php
                                                $locale = app()->getLocale();
                                                $isUSD = $locale === 'en';
                                                $rate = 20;
                                                $nombreLower = strtolower($srv->nombre);

                                                $esGasolina =
                                                    str_contains($nombreLower, 'prepaid fuel') ||
                                                    str_contains($nombreLower, 'gasolina');

                                                if ($esGasolina) {
                                                    $precioMostrar =
                                                        $srv->precio_total_tanque ??
                                                        (float) $srv->precio * ($capacidadTanque ?? 50);
                                                    $unidad = '';
                                                } else {
                                                    $precioMostrar = (float) $srv->precio;
                                                    if (
                                                        str_contains($nombreLower, 'driver') ||
                                                        str_contains($nombreLower, 'conductor')
                                                    ) {
                                                        $unidad = __('/driver per day');
                                                    } else {
                                                        $unidad = match ($srv->tipo_cobro) {
                                                            'por_dia' => __('/day'),
                                                            default => __('/event'),
                                                        };
                                                    }
                                                }

                                                $monto = $isUSD ? $precioMostrar / $rate : $precioMostrar;
                                                $moneda = $isUSD ? 'USD' : 'MXN';
                                                $formato = $isUSD ? number_format($monto, 2) : number_format($monto, 0);
                                            @endphp
                                            <strong>${{ $formato }}</strong> {{ $moneda }}
                                            {{ $unidad }}
                                        </div>

                                        <div class="addon-qty">
                                            @if (str_contains($nombreLower, 'prepaid fuel') || str_contains($nombreLower, 'gasolina'))
                                                <label class="switch">
                                                    <input type="checkbox" class="gasolina-switch">
                                                    <span class="slider"></span>
                                                </label>
                                            @else
                                                <button class="qty-btn minus" type="button">−</button>
                                                <span class="qty">0</span>
                                                <button class="qty-btn plus" type="button">+</button>
                                                <span class="qty-hint">{{ __('Max 3') }}</span>
                                            @endif
                                        </div>
                                    </div>
                                @empty
                                    <div style="grid-column: 1/-1; text-align: center; padding: 20px;">
                                        {{ __('No add-ons available.') }}
                                    </div>
                                @endforelse
                            </div>
                        </section>
                    </div>

                    <div class="wizard-nav">
                        <a class="btn btn-ghost"
                            href="{{ route('rutaReservasIniciar', array_merge($baseParams, ['step' => 2])) }}">{{ __('Previous') }}</a>
                        <a class="btn btn-primary" id="toStep4"
                            href="{{ route('rutaReservasIniciar', array_merge($baseParams, ['step' => 4])) }}">{{ __('Next') }}</a>
                    </div>
                @endif

                {{-- ===================== STEP 4 ===================== --}}

                @if ($step === 4)
                    <input type="hidden" id="addonsHidden" value="{{ $filters['addons'] ?? '' }}">

                    <div class="step4-layout">

                        {{-- ===================== PANE IZQUIERDO (FORMULARIO) ===================== --}}
                        <div class="step4-pane">
                            <form class="sum-form" id="formCotizacion" onsubmit="return false;" novalidate>
                                @csrf

                                {{-- Campos ocultos para enviar a la BD --}}
                                <input type="hidden" name="categoria_id" id="categoria_id"
                                    value="{{ $categoriaId ?? '' }}">
                                <input type="hidden" name="plan" id="plan" value="{{ $plan ?? '' }}">
                                <input type="hidden" name="addons" id="addons_payload"
                                    value="{{ $filters['addons'] ?? '' }}">

                                <input type="hidden" name="pickup_date" id="pickup_date"
                                    value="{{ $pickupDate ?? '' }}">
                                <input type="hidden" name="pickup_time" id="pickup_time"
                                    value="{{ $pickupTime ?? '' }}">
                                <input type="hidden" name="dropoff_date" id="dropoff_date"
                                    value="{{ $dropoffDate ?? '' }}">
                                <input type="hidden" name="dropoff_time" id="dropoff_time"
                                    value="{{ $dropoffTime ?? '' }}">

                                <input type="hidden" name="pickup_sucursal_id" id="pickup_sucursal_id"
                                    value="{{ $pickupSucursalId ?? '' }}">
                                <input type="hidden" name="dropoff_sucursal_id" id="dropoff_sucursal_id"
                                    value="{{ $dropoffSucursalId ?? '' }}">

                                <h2 class="sum-section-title">{{ __('Personal information') }}</h2>

                                <div class="sum-personal-grid">
                                    <div class="field field-floating" style="grid-column: 1 / -1;">
                                        <input type="text" class="input-centered" name="nombre_completo"
                                            id="nombreCompleto" autocomplete="name" placeholder=" " required>
                                        <label for="nombreCompleto">{{ __('Full name') }}</label>
                                        <input type="hidden" name="nombre" id="nombreCliente">
                                        <input type="hidden" name="apellido" id="apellidoCliente">
                                    </div>

                                    <div class="field field-floating" style="grid-column: 1 / -1;">
                                        <input type="text" name="telefono" id="telefonoCliente" placeholder=" "
                                            required>
                                        <label for="telefonoCliente">{{ __('Mobile') }}</label>
                                    </div>

                                    <div class="field field-floating" style="grid-column: 1 / -1;">
                                        <input type="email" name="email" id="correoCliente" placeholder=" "
                                            required>
                                        <label for="correoCliente">{{ __('Email address') }}</label>
                                    </div>

                                    <div class="field field-floating">
                                        <select name="pais" id="pais" required
                                            style="width: 100%; height: 50px; border: 1px solid #d1d5db; border-radius: 8px; padding: 0 12px; background-color: #fff; font-size: 16px; outline: none; margin-top: 18px;">
                                            <option value="" disabled selected></option>

                                            @foreach ($paises->where('prioritario', true) as $pais)
                                                <option value="{{ $pais->nombre }}">
                                                    {{ $isUSD ? $pais->nombre_en ?? $pais->nombre : $pais->nombre }}
                                                </option>
                                            @endforeach

                                            <option disabled>──────────</option>

                                            @foreach ($paises->where('prioritario', false) as $pais)
                                                <option value="{{ $pais->nombre }}">
                                                    {{ $isUSD ? $pais->nombre_en ?? $pais->nombre : $pais->nombre }}
                                                </option>
                                            @endforeach
                                        </select>
                                        <label for="pais"
                                            style="top: 0; transform: translateY(-50%) scale(0.8); background: #fff; padding: 0 4px;">{{ __('Country') }}</label>
                                    </div>

                                    <div class="field field-dob-container">
                                        <label class="label-dob-main">{{ __('Date of birth') }}</label>
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

                                    {{-- Vuelo (Si es aeropuerto) --}}
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
                                                target="_blank" rel="noopener">{{ __('THE POLICIES') }}</a>
                                            {{ __('AND PROCEDURES FOR THE RENTAL.') }}
                                        </span>
                                    </label>

                                    <label class="cbox">
                                        <input type="checkbox" name="promos" id="promos">
                                        <span>{{ __('I WANT TO RECEIVE ALERTS, CONFIRMATIONS, OFFERS AND PROMOTIONS ON MY EMAIL AND/OR MOBILE PHONE.') }}</span>
                                    </label>
                                </div>

                                <div class="wizard-nav" style="margin-top:10px;">
                                    <button id="btnReservar" type="button"
                                        class="btn btn-primary">{{ __('Book') }}</button>
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

                                {{-- Modal Métodos de Pago --}}
                                <div id="modalMetodoPago" class="modal-overlay" style="display:none;">
                                    <div class="modal-card modal-metodo-pago">
                                        <button id="cerrarModalMetodoX" class="modal-close" type="button"
                                            aria-label="{{ __('Close') }}">×</button>
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

                        {{-- ===================== PANE DERECHO (RESUMEN) ===================== --}}
                        <div class="step4-pane">
                            <div class="sum-compact" aria-label="{{ __('Compact summary') }}">
                                <div class="sum-compact-head">
                                    <h4 class="sum-title"><strong>{{ __('Booking summary') }}</strong></h4>
                                    <span class="sum-days"><i class="fa-regular fa-calendar"></i> {{ __('Days:') }}
                                        <strong>{{ $days }}</strong></span>
                                </div>

                                <h4 class="sum-subtitle">{{ __('Location and date') }}</h4>
                                <div class="sum-compact-grid">
                                    <div class="sum-item">
                                        <div class="sum-item-label"><i class="fa-solid fa-location-dot"></i>
                                            {{ __('Pick-up') }}</div>
                                        <div class="sum-item-value">
                                            <strong class="sum-place">{{ $pickupName ?? '—' }}</strong>
                                            <div class="sum-dt2">
                                                <div class="dt-row"><span class="dt-lbl">{{ __('Date') }}</span><span
                                                        class="dt-val">{{ $pickupFechaLarga ?? ($pickupDate ?? '') }}</span>
                                                </div>
                                                <div class="dt-row"><span class="dt-lbl">{{ __('Time') }}</span><span
                                                        class="dt-time">{{ $pickupTime ?? '' }}
                                                        {{ __('HRS') }}</span></div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="sum-item">
                                        <div class="sum-item-label"><i class="fa-solid fa-location-dot"></i>
                                            {{ __('Return') }}</div>
                                        <div class="sum-item-value">
                                            <strong class="sum-place">{{ $dropoffName ?? '—' }}</strong>
                                            <div class="sum-dt2">
                                                <div class="dt-row"><span class="dt-lbl">{{ __('Date') }}</span><span
                                                        class="dt-val">{{ $dropoffFechaLarga ?? ($dropoffDate ?? '') }}</span>
                                                </div>
                                                <div class="dt-row"><span class="dt-lbl">{{ __('Time') }}</span><span
                                                        class="dt-time">{{ $dropoffTime ?? '' }}
                                                        {{ __('HRS') }}</span></div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <h4 class="sum-subtitle" id="tuAutoSection" style="margin-top:14px;">
                                    {{ __('Your vehicle') }}</h4>
                                <div class="sum-car" style="margin-top:10px; display:flex; gap:20px; align-items:center;">
                                    <div class="sum-car-img">
                                        <img src="{{ $categoriaImg ?? asset('img/Logotipo.png') }}"
                                            alt="{{ __('Car') }}"
                                            onerror="this.onerror=null;this.src='{{ asset('img/Logotipo.png') }}';"
                                            style="width:200px; border-radius:14px;">
                                    </div>

                                    <div class="sum-car-info" style="flex:1;">
                                        <div class="car-mini-name"
                                            style="font-weight:900; font-size:20px; color:#111827;">
                                            {{ $autoTitulo ?? '' }}</div>
                                        <div class="car-mini-sub"
                                            style="margin-top:4px; font-weight:800; font-size:12px; letter-spacing:.6px; text-transform:uppercase; color:#111827;">
                                            {{ $autoSubtitulo ?? '' }}</div>

                                        <div class="car-features" style="margin-top:14px;">
                                            <ul class="car-mini-specs">
                                                <li><i class="fa-solid fa-user-large"></i> {{ $categoriaSel->pax ?? 5 }}
                                                </li>
                                                <li><i class="fa-solid fa-suitcase-rolling"></i>
                                                    {{ $categoriaSel->s_luggage ?? 2 }}</li>
                                                <li><i class="fa-solid fa-briefcase"></i>
                                                    {{ $categoriaSel->b_luggage ?? 1 }}</li>
                                                <li title="{{ __('Transmission') }}"><span class="spec-letter">T |
                                                        {{ $categoriaSel->transmision_txt ?? __('Automatic') }}</span>
                                                </li>
                                                @if ($categoriaSel->tiene_ac ?? true)
                                                    <li title="{{ __('Air conditioning') }}"><i
                                                            class="fa-regular fa-snowflake"></i> <span
                                                            class="spec-letter">{{ __('A/C') }}</span></li>
                                                @endif
                                            </ul>

                                            <div class="car-connect">
                                                @if ($categoriaSel->tiene_carplay ?? true)
                                                    <span class="badge-chip badge-apple" title="Apple CarPlay">
                                                        <span class="icon-badge">
                                                            <i class="fa-brands fa-apple"></i>
                                                        </span>
                                                        CarPlay
                                                    </span>
                                                @endif

                                                @if ($categoriaSel->tiene_android ?? true)
                                                    <span class="badge-chip badge-android" title="Android Auto">
                                                        <span class="icon-badge">
                                                            <i class="fa-brands fa-android"></i>
                                                        </span>
                                                        Android Auto
                                                    </span>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <h4 class="sum-subtitle" style="margin-top:16px;">{{ __('Price details') }}</h4>
                                <div id="cotizacionDoc" data-base="{{ $tarifaBase }}"
                                    data-days="{{ $days }}" data-pickup="{{ $pickupSucursalId }}"
                                    data-dropoff="{{ $dropoffSucursalId }}" data-km="{{ $dropoffKm }}"
                                    data-costokm="{{ $costoKmCategoria }}"
                                    data-tanque="{{ $detallesAddons['capacidadTanque'] ?? 50 }}">

                                    <details class="sum-acc">
                                        <summary class="sum-bar">
                                            <span>{{ __('Base rate') }}</span>
                                            <strong id="qBase">{{ $tarifaBaseFormateada }}</strong>
                                            <i class="sum-caret fa-solid fa-chevron-down" aria-hidden="true"></i>
                                        </summary>

                                        <div class="sum-acc-body">
                                            {{-- Detalle de días --}}
                                            <div class="row row-base">
                                                <span>
                                                    {{ $days ?? 1 }} {{ __('day(s) - price per day') }}
                                                    {{ $precioDiaFormateado }}
                                                </span>
                                            </div>

                                            {{-- Fila del Total de Base --}}
                                            <div class="row row-base-total">
                                                <span class="row-total-label">{{ __('Total') }}:</span>
                                                <strong>{{ $tarifaBaseFormateada }}</strong>
                                            </div>

                                            {{-- Sección de Incluidos --}}
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
                                                    <span class="inc-item"><span class="inc-check">✔</span>
                                                        {{ __('Unlimited mileage') }}</span>
                                                    <span class="inc-item"><span class="inc-check">✔</span>
                                                        {{ __('Liability Waiver (LI)') }}</span>
                                                </span>
                                            </div>
                                        </div>
                                    </details>

                                    {{-- MODAL DE PROTECCIONES --}}
                                    <div id="modalProtecciones" class="modal-global-viajero" style="display:none;">
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
                                                    <strong
                                                        class="modal-v-titulo-negro">{{ __('LIMITED THIRD-PARTY LIABILITY PROTECTION (LI)') }}</strong>
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

                                    <details class="sum-acc">
                                        <summary class="sum-bar">
                                            <span>{{ __('Rental options') }}</span>
                                            <strong id="qExtras">$0 MXN</strong>
                                            <i class="sum-caret fa-solid fa-chevron-down" aria-hidden="true"></i>
                                        </summary>
                                        <div class="sum-acc-body" id="extrasList">
                                            <div class="row"><span
                                                    class="muted">{{ __('No add-ons selected') }}</span><strong>$0
                                                    MXN</strong></div>
                                        </div>
                                    </details>

                                    <details class="sum-acc">
                                        <summary class="sum-bar">
                                            <span>{{ __('Fees and TAXES (16%)') }}</span>
                                            <strong id="qIva">$0 MXN</strong>
                                            <i class="sum-caret fa-solid fa-chevron-down" aria-hidden="true"></i>
                                        </summary>
                                        <div class="sum-acc-body" id="ivaList">
                                            <div class="row"><span
                                                    class="muted">{{ __('No additional charges') }}</span><strong>$0
                                                    MXN</strong></div>
                                        </div>
                                    </details>

                                    <div class="sum-total">
                                        <span>{{ __('Total') }}</span>
                                        <strong id="qTotal">{{ $tarifaBaseFormateada }}</strong>
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
                    </div>
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
        window.PAYPAL_MODE = "{{ $paypalMode }}";
        window.PAYPAL_CLIENT_ID = "{{ $paypalClientId }}";

        window.APP_URL_RESERVA_MOSTRADOR = "{{ route('reservas.store') }}";
        window.APP_URL_RESERVA_LINEA = "{{ route('reservas.linea') }}";

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
            charges_vat: "{{ __('Fees and TAXES (16%)') }}",
            total_label: "{{ __('Total') }}",
            confirmation_email: "{{ __('You will receive a confirmation by email.') }}",
            go_to_homepage: "{{ __('Go to homepage') }}",
            reservation_success_fallback: "{{ __('Reservation registered successfully. Check your confirmation email.') }}",
        };
    </script>

@endsection
