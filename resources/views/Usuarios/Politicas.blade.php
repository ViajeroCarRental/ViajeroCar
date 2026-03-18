@extends('layouts.Usuarios')

@section('Titulo', __('messages.politicas_titulo'))

@section('css-vistaPoliticas')
    <link rel="stylesheet" href="{{ asset('css/politicas.css') }}">
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
@endsection

@section('contenidoHome')
<main class="page">
    <section class="hero hero-mini">
        <div class="hero-bg">
            <img src="{{ asset('img/politicas.png') }}" alt="{{ __('messages.politicas_titulo') }}">
        </div>
        <div class="hero-overlay"></div>

        <div class="hero-content-politicas">
            {{-- TEXTO ARRIBA A LA IZQUIERDA --}}
            <div class="hero-texto-superior">
                <h1>{{ __('messages.politicas_viajero') }} <span>{{ __('messages.viajero') }}</span></h1>
                <p>{{ __('messages.aviso_privacidad_limpieza') }}</p>
            </div>

      <!-- BOTÓN PARA ABRIR BUSCADOR EN MÓVIL/TABLET -->
<div class="d-block d-xl-none" style="width: 100%; max-width: 100%; margin: 15px 0; padding: 0 15px;">
    <div style="background: white;
                padding: 15px;
                border-radius: 12px;
                box-shadow: 0 4px 12px rgba(0,0,0,0.1);
                text-align: center;
                width: 100%;
                margin: 0 auto;">

        <p style="margin-bottom: 12px;
                  font-weight: 700;
                  color: #333;
                  font-size: 16px;">
            {{ __('messages.encuentra_tu_aqui') }}
        </p>

        <button type="button" id="btn-abrir-buscador-politicas"
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
            <i class="fa-solid fa-magnifying-glass"></i> {{ __('messages.buscar') }}
        </button>
    </div>
</div>
<div class="hero-buscador-wrapper">
    <div class="search-card" id="miBuscadorPoliticas">
        <!-- ✅ BOTÓN DE CERRAR - SIN DIV EXTRA -->
        <button type="button" id="btn-cerrar-buscador-politicas" class="btn-close-politicas" aria-label="{{ __('messages.cerrar') }}">
            <span>{{ __('messages.cerrar') }}</span>
        </button>

        <form id="rentalFormPoliticas" class="search-form" method="GET" action="{{ route('rutaReservasIniciar') }}" novalidate>
            @csrf


                        <div class="search-grid">
                            {{-- COLUMNA 1: LUGAR DE RENTA --}}
                            <div class="sg-col sg-col-location">
                                <div class="location-head">
                                    <span class="field-title">{{ __('messages.lugar_de_renta') }}</span>
                                    <label class="inline-check" for="differentDropoffPoliticas">
                                        <input type="checkbox" id="differentDropoffPoliticas" name="different_dropoff" value="1">
                                        <span>{{ __('messages.devolver_en_otro') }}</span>
                                    </label>
                                </div>

                                <div class="location-inputs-wrapper">
                                    {{-- SELECT PICKUP --}}
                                    <div class="field icon-field">
                                        <span class="field-icon"></i></span>  <!-- ✅ VACÍO (como quieres) -->
                                        <select id="pickupPlacePoliticas" name="pickup_sucursal_id">
                                            <option value="" disabled selected>{{ __('messages.donde_inicia') }}</option>
                                            @foreach($ciudades->where('nombre','Querétaro') as $ciudad)
                                                <optgroup label="{{ $ciudad->nombre }}">
                                                    @foreach($ciudad->sucursalesActivas as $suc)
                                                        <option value="{{ $suc->id_sucursal }}" @selected(request('pickup_sucursal_id') == $suc->id_sucursal)>
                                                            {{ $suc->nombre }}
                                                        </option>
                                                    @endforeach
                                                </optgroup>
                                            @endforeach
                                        </select>
                                    </div>

                                    {{-- SELECT DROPOFF --}}
                                    <div class="field icon-field" id="dropoffWrapperPoliticas">
                                        <span class="field-icon"></i></span>  <!-- ✅ VACÍO (como quieres) -->
                                        <select id="dropoffPlacePoliticas" name="dropoff_sucursal_id">
                                            <option value="" disabled selected>{{ __('messages.donde_termina') }}</option>
                                            @foreach($ciudades as $ciudad)
                                                <optgroup label="{{ $ciudad->nombre }}">
                                                    @foreach($ciudad->sucursalesActivas as $suc)
                                                        <option value="{{ $suc->id_sucursal }}" @selected(request('dropoff_sucursal_id') == $suc->id_sucursal)>
                                                            {{ $suc->nombre }}
                                                        </option>
                                                    @endforeach
                                                </optgroup>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                            </div>

                            {{-- COLUMNA 2: FECHAS --}}
                            <div class="sg-col sg-col-datetime">
                                {{-- PICKUP --}}
                                <div class="field">
                                    <span class="field-title solo-responsivo-izq">{{ __('messages.pickup') }}</span>
                                    <div class="datetime-row">
                                        <div class="dt-field icon-field">
                                            <span class="field-icon"><i class="fa-regular fa-calendar-days"></i></span>
                                            <input id="pickupDatePoliticas" name="pickup_date" type="text" placeholder="{{ __('messages.fecha') }}"
                                                   value="{{ request('pickup_date') }}" data-min="{{ now()->toDateString() }}">
                                        </div>
                                        <div class="dt-field icon-field time-field">
                                            <span class="field-icon"><i class="fa-regular fa-clock"></i></span>
                                            <input type="text" id="pickupTimePoliticas" name="pickup_time" placeholder="{{ __('messages.hora') }}"
                                                   value="{{ request('pickup_time') }}">
                                        </div>
                                    </div>
                                </div>

                                {{-- DROPOFF --}}
                                <div class="field">
                                    <span class="field-title solo-responsivo-izq">{{ __('messages.devolucion') }}</span>
                                    <div class="datetime-row">
                                        <div class="dt-field icon-field">
                                            <span class="field-icon"><i class="fa-regular fa-calendar-days"></i></span>
                                            <input id="dropoffDatePoliticas" name="dropoff_date" type="text" placeholder="{{ __('messages.fecha') }}"
                                                   value="{{ request('dropoff_date') }}" data-min="{{ now()->toDateString() }}">
                                        </div>
                                        <div class="dt-field icon-field time-field">
                                            <span class="field-icon"><i class="fa-regular fa-clock"></i></span>
                                            <input type="text" id="dropoffTimePoliticas" name="dropoff_time" placeholder="{{ __('messages.hora') }}"
                                                   value="{{ request('dropoff_time') }}">
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- COLUMNA 3: BOTÓN -->
                            <div class="sg-col sg-col-submit">
                                <div class="actions">
                                    <button type="submit">
                                        <i class="fa-solid fa-magnifying-glass"></i> {{ __('messages.buscar') }}
                                    </button>
                                </div>
                            </div>
                        </div>  <!-- Cierre de search-grid -->

                        <div id="rangeSummary" class="range-summary" aria-live="polite">
                            @if(request('pickup_date') && request('dropoff_date'))
                                {{ request('pickup_date') }} - {{ request('dropoff_date') }}
                            @endif
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </section>


    {{-- ✅ GRID DE CARDS (REEMPLAZA ACORDEÓN) --}}
    <section class="policies-wrap">
      <div class="policies-grid">

        {{-- Requisitos de renta --}}
        <button type="button" class="policy-card" data-modal="tpl-requisitos" data-title="{{ __('messages.requisitos_renta') }}">
          <span class="pc-icon"><i class="fa-solid fa-clipboard-check"></i></span>
          <span class="pc-title">{{ __('messages.requisitos_renta') }}</span>
        </button>

        {{-- Formas de pago --}}
        <button type="button" class="policy-card" data-modal="tpl-pago" data-title="{{ __('messages.formas_de_pago') }}">
          <span class="pc-icon"><i class="fa-solid fa-wallet"></i></span>
          <span class="pc-title">{{ __('messages.formas_de_pago') }}</span>
        </button>

        {{-- Depósitos --}}
        <button type="button" class="policy-card" data-modal="tpl-depositos" data-title="{{ __('messages.depositos') }}">
          <span class="pc-icon"><i class="fa-solid fa-mobile-screen-button"></i></span>
          <span class="pc-title">{{ __('messages.depositos') }}</span>
        </button>

        {{-- ✅ NUEVA: Pre Check-In --}}
        <button type="button" class="policy-card" data-modal="tpl-precheckin" data-title="{{ __('messages.pre_checkin') }}">
          <span class="pc-icon"><i class="fa-solid fa-circle-check"></i></span>
          <span class="pc-title">{{ __('messages.pre_checkin') }}</span>
        </button>

        {{-- Cancelaciones y reembolsos --}}
        <button type="button" class="policy-card" data-modal="tpl-cancelaciones" data-title="{{ __('messages.cancelaciones_reembolsos') }}">
          <span class="pc-icon"><i class="fa-solid fa-rotate-left"></i></span>
          <span class="pc-title">{{ __('messages.cancelaciones_reembolsos') }}</span>
        </button>

        {{-- Seguro de cancelación --}}
        <button type="button" class="policy-card" data-modal="tpl-seguro-cancelacion" data-title="{{ __('messages.seguro_cancelacion') }}">
          <span class="pc-icon"><i class="fa-solid fa-shield-heart"></i></span>
          <span class="pc-title">{{ __('messages.seguro_cancelacion') }}</span>
        </button>

        {{-- Política de suciedad --}}
        <button type="button" class="policy-card" data-modal="tpl-limpieza" data-title="{{ __('messages.politica_limpieza') }}">
          <span class="pc-icon"><i class="fa-solid fa-spray-can-sparkles"></i></span>
          <span class="pc-title">{{ __('messages.politica_limpieza') }}</span>
        </button>

        {{-- Políticas de infracciones --}}
        <button type="button" class="policy-card" data-modal="tpl-infracciones" data-title="{{ __('messages.politicas_infracciones') }}">
          <span class="pc-icon"><i class="fa-solid fa-file-lines"></i></span>
          <span class="pc-title">{{ __('messages.politicas_infracciones') }}</span>
        </button>

        {{-- (Opcional) Aviso de privacidad --}}
        <button type="button" class="policy-card" data-modal="tpl-privacidad" data-title="{{ __('messages.aviso_privacidad') }}">
          <span class="pc-icon"><i class="fa-solid fa-shield-halved"></i></span>
          <span class="pc-title">{{ __('messages.aviso_privacidad') }}</span>
        </button>

        {{-- (Opcional) Términos y condiciones --}}
        <button type="button" class="policy-card" data-modal="tpl-terminos" data-title="{{ __('messages.terminos_condiciones') }}">
          <span class="pc-icon"><i class="fa-solid fa-scale-balanced"></i></span>
          <span class="pc-title">{{ __('messages.terminos_condiciones') }}</span>
        </button>

        {{-- (Opcional) Definiciones de cargos e impuestos --}}
        <button type="button" class="policy-card" data-modal="tpl-cargos" data-title="{{ __('messages.definiciones_cargos') }}">
          <span class="pc-icon"><i class="fa-solid fa-receipt"></i></span>
          <span class="pc-title">{{ __('messages.definiciones_cargos') }}</span>
        </button>

      </div>
    </section>

    {{-- ✅ MODAL REUTILIZABLE --}}
    <div class="vj-modal" id="policyModal" aria-hidden="true">
      <div class="vj-modal__backdrop" data-close="1"></div>

      <div class="vj-modal__dialog" role="dialog" aria-modal="true" aria-labelledby="policyModalTitle">
        <div class="vj-modal__header">
          <h3 class="vj-modal__title" id="policyModalTitle">{{ __('messages.politicas_titulo') }}</h3>
          <button type="button" class="vj-modal__close" aria-label="{{ __('messages.cerrar') }}" data-close="1">
            <i class="fa-solid fa-xmark"></i>
          </button>
        </div>

        <div class="vj-modal__body" id="policyModalBody">
          {{-- aquí se inyecta contenido --}}
        </div>
      </div>
    </div>

    {{-- ✅ TEMPLATES DE CONTENIDO TRADUCIDOS --}}

    {{-- AVISO PRIVACIDAD --}}
    <template id="tpl-privacidad">
      <div class="policy-content">
        <p><strong>{{ __('messages.privacidad_titulo') }}</strong></p>
        <p>{{ __('messages.privacidad_p1') }}</p>
        <p>{{ __('messages.privacidad_p2') }}</p>
        <p>{{ __('messages.privacidad_p3') }}</p>
        <p>{!! __('messages.privacidad_p4') !!}</p>
        <p>{{ __('messages.privacidad_p5') }}</p>
        <p>{{ __('messages.privacidad_p6') }}</p>
      </div>
    </template>

    {{-- POLÍTICA LIMPIEZA / SUCIEDAD --}}
    <template id="tpl-limpieza">
      <div class="policy-content">
        <h4>{{ __('messages.limpieza_titulo') }}</h4>
        <p>{!! __('messages.limpieza_intro') !!}</p>

        <p>{!! __('messages.limpieza_recordatorio') !!}</p>

        <h5>{{ __('messages.limpieza_que_es') }}</h5>
        <p>{{ __('messages.limpieza_que_es_p') }}</p>
        <ul>
          <li>{!! __('messages.limpieza_item1') !!}</li>
          <li>{!! __('messages.limpieza_item2') !!}</li>
          <li>{{ __('messages.limpieza_item3') }}</li>
          <li>{{ __('messages.limpieza_item4') }}</li>
          <li>{{ __('messages.limpieza_item5') }}</li>
        </ul>

        <h5>{{ __('messages.limpieza_como_valida') }}</h5>
        <ul>
          <li>{!! __('messages.limpieza_valida1') !!}</li>
          <li>{!! __('messages.limpieza_valida2') !!}</li>
          <li>{!! __('messages.limpieza_valida3') !!}</li>
        </ul>

        <h5>{{ __('messages.limpieza_evitar') }}</h5>
        <ul>
          <li>{{ __('messages.limpieza_evitar1') }}</li>
          <li>{{ __('messages.limpieza_evitar2') }}</li>
          <li>{{ __('messages.limpieza_evitar3') }}</li>
          <li>{{ __('messages.limpieza_evitar4') }}</li>
        </ul>

        <p>{!! __('messages.limpieza_objetivo') !!}</p>
      </div>
    </template>

    {{-- REQUISITOS RENTA --}}
    <template id="tpl-requisitos">
      <div class="policy-content">
        <h4>{{ __('messages.requisitos_edad_minima') }}</h4>
        <p>{!! __('messages.requisitos_edad_texto') !!}</p>

        <h4>{{ __('messages.requisitos_licencia') }}</h4>
        <p>{!! __('messages.requisitos_licencia_texto') !!}</p>

        <h4>{{ __('messages.requisitos_identificacion') }}</h4>
        <p>{!! __('messages.requisitos_identificacion_texto') !!}</p>

        <h4>{{ __('messages.requisitos_tarjeta') }}</h4>
        <p>{!! __('messages.requisitos_tarjeta_texto') !!}</p>

        <h4>{{ __('messages.requisitos_derecho') }}</h4>
        <p>{!! __('messages.requisitos_derecho_texto') !!}</p>
      </div>
    </template>

    {{-- PRE CHECK-IN --}}
    <template id="tpl-precheckin">
      <div class="policy-content">
        <h4>{{ __('messages.precheckin_porque') }}</h4>
        <p>{!! __('messages.precheckin_ahorra') !!}</p>

        <p>{!! __('messages.precheckin_necesitas') !!}</p>
        <ul>
          <li>{{ __('messages.precheckin_item1') }}</li>
          <li>{{ __('messages.precheckin_item2') }}</li>
          <li>{{ __('messages.precheckin_item3') }}</li>
          <li>{{ __('messages.precheckin_item4') }}</li>
        </ul>

        <p>{!! __('messages.precheckin_cuando_llegues') !!}</p>

        <h4>{{ __('messages.precheckin_listo') }}</h4>
        <ul>
          <li>{{ __('messages.precheckin_listo1') }}</li>
          <li>{{ __('messages.precheckin_listo2') }}</li>
          <li>{{ __('messages.precheckin_listo3') }}</li>
          <li>{{ __('messages.precheckin_listo4') }}</li>
        </ul>
      </div>
    </template>

    {{-- SEGURO CANCELACIÓN --}}
    <template id="tpl-seguro-cancelacion">
      <div class="policy-content">
        <h4>{{ __('messages.seguro_cancelacion_titulo') }}</h4>
        <p>{!! __('messages.seguro_cancelacion_p1') !!}</p>
        <p>{!! __('messages.seguro_cancelacion_p2') !!}</p>
        <p>{!! __('messages.seguro_cancelacion_p3') !!}</p>
        <p>{!! __('messages.seguro_cancelacion_p4') !!}</p>
        <p>{!! __('messages.seguro_cancelacion_p5') !!}</p>
      </div>
    </template>

    {{-- CARGOS E IMPUESTOS --}}
    <template id="tpl-cargos">
      <div class="policy-content">
        <h4>{{ __('messages.cargos_iva') }}</h4>
        <p>{!! __('messages.cargos_iva_texto') !!}</p>

        <h4>{{ __('messages.cargos_aeropuerto') }}</h4>
        <p>{!! __('messages.cargos_aeropuerto_texto') !!}</p>

        <h4>{{ __('messages.cargos_telemetria') }}</h4>
        <p>{!! __('messages.cargos_telemetria_texto') !!}</p>

        <h4>{{ __('messages.cargos_dropoff') }}</h4>
        <p>{!! __('messages.cargos_dropoff_texto') !!}</p>

        <h4>{{ __('messages.cargos_gasolina') }}</h4>
        <p>{!! __('messages.cargos_gasolina_texto') !!}</p>
      </div>
    </template>

    {{-- FORMAS DE PAGO --}}
    <template id="tpl-pago">
      <div class="policy-content">
        <h4>{{ __('messages.pago_titulo') }}</h4>
        <h5>{{ __('messages.pago_tarjetas') }}</h5>
        <ul>
          <li><strong>American Express</strong></li>
          <li><strong>Visa</strong></li>
          <li><strong>Mastercard</strong></li>
        </ul>

        <h5>{{ __('messages.pago_paypal') }}</h5>
        <p>{!! __('messages.pago_paypal_texto') !!}</p>

        <h5>{{ __('messages.pago_efectivo') }}</h5>
        <p>{!! __('messages.pago_efectivo_texto') !!}</p>

        <h5>{{ __('messages.pago_oxxo') }}</h5>
        <p>{{ __('messages.pago_oxxo_texto') }}</p>

        <h5>{{ __('messages.pago_mp') }}</h5>
        <p>{{ __('messages.pago_mp_texto') }}</p>
      </div>
    </template>

    {{-- CANCELACIONES --}}
    <template id="tpl-cancelaciones">
      <div class="policy-content">
        <h4>{{ __('messages.cancelaciones_titulo') }}</h4>

        <h5>{{ __('messages.cancelaciones_100') }}</h5>
        <p>{!! __('messages.cancelaciones_100_texto') !!}</p>

        <h5>{{ __('messages.cancelaciones_50') }}</h5>
        <p>{!! __('messages.cancelaciones_50_texto') !!}</p>

        <h5>{{ __('messages.cancelaciones_25') }}</h5>
        <p>{!! __('messages.cancelaciones_25_texto') !!}</p>

        <h5>{{ __('messages.cancelaciones_0') }}</h5>
        <p>{!! __('messages.cancelaciones_0_texto') !!}</p>

        <h5>{{ __('messages.cancelaciones_procedimiento') }}</h5>
        <p>{!! __('messages.cancelaciones_procedimiento_texto') !!}</p>
      </div>
    </template>

    {{-- DEPÓSITOS (GARANTÍA / PRE-AUTORIZACIÓN) --}}
    <template id="tpl-depositos">
      <div class="policy-content">
        <h4>{{ __('messages.depositos_titulo') }}</h4>
        <p>{!! __('messages.depositos_definicion') !!}</p>

        <h5>{{ __('messages.depositos_liberacion') }}</h5>
        <p>{!! __('messages.depositos_liberacion_texto') !!}</p>

        <h5>{{ __('messages.depositos_tabla_titulo') }}</h5>
        <div class="tabla-garantias">
          <table class="tabla-viajero">
            <thead>
              <tr>
                <th>{{ __('messages.depositos_tabla_categoria') }}</th>
                <th>{{ __('messages.depositos_tabla_tamano') }}</th>
                <th>LDW</th>
                <th>PDW</th>
                <th>CDW 10%</th>
                <th>CDW 20%</th>
                <th>CDW {{ __('messages.declinado') }}</th>
              </tr>
            </thead>
            <tbody>
              <tr><td>C</td><td>Compacto Chevrolet aveo {{ __('messages.o_similar') }}</td><td>$MXN5,000</td><td>$MXN8,000</td><td>$MXN15,000</td><td>$MXN25,000</td><td>$MXN330,000</td></tr>
              <tr><td>D</td><td>Medianos Nissan Virtus {{ __('messages.o_similar') }}</td><td>$MXN5,000</td><td>$MXN8,000</td><td>$MXN18,000</td><td>$MXN25,000</td><td>$MXN380,000</td></tr>
              <tr><td>E</td><td>Grandes Volkswagen Jetta {{ __('messages.o_similar') }}</td><td>$MXN5,000</td><td>$MXN8,000</td><td>$MXN20,000</td><td>$MXN30,000</td><td>$MXN500,000</td></tr>
              <tr><td>F</td><td>Full size Camry {{ __('messages.o_similar') }}</td><td>$MXN5,000</td><td>$MXN15,000</td><td>$MXN30,000</td><td>$MXN40,000</td><td>$MXN650,000</td></tr>
              <tr><td>IC</td><td>Suv compacta Jeep Renegade {{ __('messages.o_similar') }}</td><td>$MXN5,000</td><td>$MXN8,000</td><td>$MXN20,000</td><td>$MXN30,000</td><td>$MXN500,000</td></tr>
              <tr><td>I</td><td>Suv Mediana Volkswagen Taos {{ __('messages.o_similar') }}</td><td>$MXN5,000</td><td>$MXN10,000</td><td>$MXN30,000</td><td>$MXN40,000</td><td>$MXN600,000</td></tr>
              <tr><td>IB</td><td>Suv Familiar compacta Toyota avanza {{ __('messages.o_similar') }}</td><td>$MXN5,000</td><td>$MXN8,000</td><td>$MXN18,000</td><td>$MXN25,000</td><td>$MXN400,000</td></tr>
              <tr><td>M</td><td>Minivan Honda Odyssey {{ __('messages.o_similar') }}</td><td>$MXN10,000</td><td>$MXN20,000</td><td>$MXN30,000</td><td>$MXN40,000</td><td>$MXN800,000</td></tr>
              <tr><td>L</td><td>Pasajeros de 12 usuarios Toyota Hiace {{ __('messages.o_similar') }}</td><td>$MXN10,000</td><td>$MXN20,000</td><td>$MXN30,000</td><td>$MXN40,000</td><td>$MXN800,000</td></tr>
              <tr><td>H</td><td>Pick up Doble Cabina Nissan Frontier {{ __('messages.o_similar') }}</td><td>$MXN10,000</td><td>$MXN20,000</td><td>$MXN30,000</td><td>$MXN40,000</td><td>$MXN600,000</td></tr>
              <tr><td>HI</td><td>Pick up 4x4 Doble Cabina Toyota Tacoma {{ __('messages.o_similar') }}</td><td>$MXN10,000</td><td>$MXN20,000</td><td>$MXN30,000</td><td>$MXN40,000</td><td>$MXN900,000</td></tr>
            </tbody>
          </table>
        </div>

        <p style="margin-top: 1rem;">{!! __('messages.depositos_nota') !!}</p>
      </div>
    </template>

   {{-- INFRACCIONES --}}
<template id="tpl-infracciones">
  <div class="policy-content">
    <h4>{{ __('messages.infracciones_titulo') }}</h4>

    <p><strong>{{ __('messages.infracciones_que_pasa') }}</strong></p>

    <p>{{ __('messages.infracciones_deberas') }}</p>
    <ul>
      <li>
        <strong>{{ __('messages.infracciones_telefono') }}</strong>
        <a href="tel:524427169793">(+52) 442 716 9793</a>
      </li>
      <li>
        <strong>{{ __('messages.infracciones_email') }}</strong>
        <a href="mailto:reservaciones@viajerocarental.com">reservaciones@viajerocarental.com</a>
      </li>
      <li>
        <strong>{{ __('messages.infracciones_soporte') }}</strong>
        <a href="mailto:soportetecnico@viajerocarental.com">soportetecnico@viajerocarental.com</a>
      </li>
    </ul>

    <p>{!! __('messages.infracciones_responsabilidad') !!}</p>

    <p>{!! __('messages.infracciones_administrativo') !!}</p>
  </div>
</template>

    {{-- TÉRMINOS --}}
    <template id="tpl-terminos">
      <div class="policy-content">
        <h4>{{ __('messages.terminos_titulo') }}</h4>
        <p>{{ __('messages.terminos_texto') }}</p>
        <p>
          {{ __('messages.terminos_atencion') }}
          {{ __('messages.terminos_telefono') }} <a href="tel:+524423032668">01 (442) 303 2668</a> ·
          {{ __('messages.terminos_correo') }} <a href="mailto:reservaciones@viajerocar-rental.mx">reservaciones@viajerocar-rental.mx</a>
        </p>
      </div>
    </template>

</main>
@endsection

@section('js-vistaPoliticas')
 <script>
        window.politicasTranslations = {
            ubicacion_requerida: "{{ __('messages.ubicacion_requerida') }}",
            fecha_requerida: "{{ __('messages.fecha_requerida') }}",
            hora_requerida: "{{ __('messages.hora_requerida') }}"
        };
    </script>
    <!-- jQuery (necesario para Select2) -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <!-- Select2 JS -->
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <!-- Flatpickr -->
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script src="https://cdn.jsdelivr.net/npm/flatpickr/dist/l10n/es.js"></script>
     <!-- Flatpickr CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <!-- Tu JS personalizado -->
    <script src="{{ asset('js/politicas.js') }}"></script>
@endsection
