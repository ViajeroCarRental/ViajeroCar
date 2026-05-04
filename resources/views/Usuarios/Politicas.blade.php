@extends('layouts.Usuarios')

@section('Titulo','Privacy Policy and Terms of Service')

@section('css-vistaPoliticas')
    <link rel="stylesheet" href="{{ asset('css/politicas.css') }}">
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
@endsection

@section('contenidoHome')
<main class="page">
    <section class="hero hero-mini">
        <div class="hero-bg">
            <img src="{{ asset('img/politicas.png') }}" alt="Policies">
        </div>
        <div class="hero-overlay"></div>

        <div class="hero-content-politicas">
            {{-- TEXT TOP LEFT --}}
            <div class="hero-texto-superior">
                <h1>{{ __('Policies') }} <span>{{ __('Viajero') }}</span></h1>
                <p>{{ __('Privacy notice, cleaning, rental policy and terms') }}</p>
            </div>

      <!-- MOBILE/TABLET SEARCH BUTTON -->
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
            {{ __('Find your car here') }}
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
            <i class="fa-solid fa-magnifying-glass"></i> {{ __('SEARCH') }}
        </button>
    </div>
</div>
<div class="hero-buscador-wrapper">
    <div class="search-card" id="miBuscadorPoliticas">
        <!-- ✅ CLOSE BUTTON -->
        <button type="button" id="btn-cerrar-buscador-politicas" class="btn-close-politicas" aria-label="{{ __('Close') }}">
            <span>{{ __('Close') }}</span>
        </button>

        <form id="rentalFormPoliticas" class="search-form" method="GET" action="{{ route('rutaReservasIniciar') }}" novalidate>
    @csrf

    <input type="hidden" name="step" value="2">

                        <div class="search-grid">
                            {{-- COLUMN 1: PICK-UP LOCATION --}}
                            <div class="sg-col sg-col-location">
                                <div class="location-head">
                                    <span class="field-title">{{ __('Pick-up location') }}</span>
                                    <label class="inline-check" for="differentDropoffPoliticas">
                                        <input type="checkbox" id="differentDropoffPoliticas" name="different_dropoff" value="1">
                                        <span>{{ __('Different return location') }}</span>
                                    </label>
                                </div>

                                <div class="location-inputs-wrapper">
                                    {{-- SELECT PICKUP --}}
                                    <div class="field icon-field">
                                        <span class="field-icon"></i></span>
                                        <select id="pickupPlacePoliticas" name="pickup_sucursal_id">
                                            <option value="" disabled selected>{{ __('Where does your trip begin?') }}</option>
                                            @foreach($ciudades->where('nombre','Querétaro') as $ciudad)
                                                <optgroup label="{{ $ciudad->nombre }}{{ $ciudad->estado ? ' — ' . $ciudad->estado : '' }}">
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
                                        <span class="field-icon"></i></span>
                                        <select id="dropoffPlacePoliticas" name="dropoff_sucursal_id">
                                            <option value="" disabled selected>{{ __('Where does your trip end?') }}</option>
                                           @foreach ($ciudades->sortByDesc(function($c) { return $c->nombre === 'Querétaro';}) as $ciudad)
                                               <optgroup label="{{ $ciudad->nombre }}{{ $ciudad->estado ? ' — ' . $ciudad->estado : '' }}">
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

                            {{-- COLUMN 2: DATES --}}
                            <div class="sg-col sg-col-datetime">
                                {{-- PICKUP --}}
                                <div class="field">
                                    <span class="field-title solo-responsivo-izq">{{ __('Pick-up') }}</span>
                                    <div class="datetime-row">
                                        <div class="dt-field icon-field">
                                            <span class="field-icon"><i class="fa-regular fa-calendar-days"></i></span>
                                            <input id="pickupDatePoliticas" name="pickup_date" type="text" placeholder="{{ __('Date') }}"
                                                   value="{{ request('pickup_date') }}" data-min="{{ now()->toDateString() }}">
                                        </div>
                                        <div class="dt-field icon-field time-field">
                                            <span class="field-icon"><i class="fa-regular fa-clock"></i></span>
                                            <input type="text" id="pickupTimePoliticas" name="pickup_time" placeholder="{{ __('Time') }}"
                                                   value="{{ request('pickup_time') }}">
                                        </div>
                                    </div>
                                </div>

                                {{-- DROPOFF --}}
                                <div class="field">
                                    <span class="field-title solo-responsivo-izq">{{ __('Return') }}</span>
                                    <div class="datetime-row">
                                        <div class="dt-field icon-field">
                                            <span class="field-icon"><i class="fa-regular fa-calendar-days"></i></span>
                                            <input id="dropoffDatePoliticas" name="dropoff_date" type="text" placeholder="{{ __('Date') }}"
                                                   value="{{ request('dropoff_date') }}" data-min="{{ now()->toDateString() }}">
                                        </div>
                                        <div class="dt-field icon-field time-field">
                                            <span class="field-icon"><i class="fa-regular fa-clock"></i></span>
                                            <input type="text" id="dropoffTimePoliticas" name="dropoff_time" placeholder="{{ __('Time') }}"
                                                   value="{{ request('dropoff_time') }}">
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- COLUMN 3: BUTTON -->
                            <div class="sg-col sg-col-submit">
                                <div class="actions">
                                    <button type="submit">
                                        <i class="fa-solid fa-magnifying-glass"></i> {{ __('SEARCH') }}
                                    </button>
                                </div>
                            </div>
                        </div>  <!-- End search-grid -->

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


    {{-- ✅ POLICY CARDS GRID (REPLACES ACCORDION) --}}
    <section class="policies-wrap">
      <div class="policies-grid">

        {{-- Rental requirements --}}
        <button type="button" class="policy-card" data-modal="tpl-requisitos" data-title="{{ __('Rental requirements') }}">
          <span class="pc-icon"><i class="fa-solid fa-clipboard-check"></i></span>
          <span class="pc-title">{{ __('Rental requirements') }}</span>
        </button>

        {{-- Payment methods --}}
        <button type="button" class="policy-card" data-modal="tpl-pago" data-title="{{ __('Payment methods') }}">
          <span class="pc-icon"><i class="fa-solid fa-wallet"></i></span>
          <span class="pc-title">{{ __('Payment methods') }}</span>
        </button>

        {{-- Deposits --}}
        <button type="button" class="policy-card" data-modal="tpl-depositos" data-title="{{ __('Security deposits') }}">
          <span class="pc-icon"><i class="fa-solid fa-mobile-screen-button"></i></span>
          <span class="pc-title">{{ __('Security deposits') }}</span>
        </button>

        {{-- ✅ NEW: Pre Check-In --}}
        <button type="button" class="policy-card" data-modal="tpl-precheckin" data-title="{{ __('Pre Check-In') }}">
          <span class="pc-icon"><i class="fa-solid fa-circle-check"></i></span>
          <span class="pc-title">{{ __('Pre Check-In') }}</span>
        </button>

        {{-- Cancellations and refunds --}}
        <button type="button" class="policy-card" data-modal="tpl-cancelaciones" data-title="{{ __('Cancellations and refunds') }}">
          <span class="pc-icon"><i class="fa-solid fa-rotate-left"></i></span>
          <span class="pc-title">{{ __('Cancellations and refunds') }}</span>
        </button>

        {{-- Cancellation insurance --}}
        <button type="button" class="policy-card" data-modal="tpl-seguro-cancelacion" data-title="{{ __('Cancellation insurance') }}">
          <span class="pc-icon"><i class="fa-solid fa-shield-heart"></i></span>
          <span class="pc-title">{{ __('Cancellation insurance') }}</span>
        </button>

        {{-- Cleaning policy --}}
        <button type="button" class="policy-card" data-modal="tpl-limpieza" data-title="{{ __('Cleaning policy') }}">
          <span class="pc-icon"><i class="fa-solid fa-spray-can-sparkles"></i></span>
          <span class="pc-title">{{ __('Cleaning policy') }}</span>
        </button>

        {{-- Infractions policy --}}
        <button type="button" class="policy-card" data-modal="tpl-infracciones" data-title="{{ __('Infractions policy') }}">
          <span class="pc-icon"><i class="fa-solid fa-file-lines"></i></span>
          <span class="pc-title">{{ __('Infractions policy') }}</span>
        </button>

        {{-- Privacy notice --}}
        <button type="button" class="policy-card" data-modal="tpl-privacidad" data-title="{{ __('Privacy notice') }}">
          <span class="pc-icon"><i class="fa-solid fa-shield-halved"></i></span>
          <span class="pc-title">{{ __('Privacy notice') }}</span>
        </button>

        {{-- Terms and conditions --}}
        <button type="button" class="policy-card" data-modal="tpl-terminos" data-title="{{ __('Terms and conditions') }}">
          <span class="pc-icon"><i class="fa-solid fa-scale-balanced"></i></span>
          <span class="pc-title">{{ __('Terms and conditions') }}</span>
        </button>

        {{-- Charges and taxes definitions --}}
        <button type="button" class="policy-card" data-modal="tpl-cargos" data-title="{{ __('Charges and taxes definitions') }}">
          <span class="pc-icon"><i class="fa-solid fa-receipt"></i></span>
          <span class="pc-title">{{ __('Charges and taxes definitions') }}</span>
        </button>

      </div>
    </section>

    {{-- ✅ REUSABLE MODAL --}}
    <div class="vj-modal" id="policyModal" aria-hidden="true">
      <div class="vj-modal__backdrop" data-close="1"></div>

      <div class="vj-modal__dialog" role="dialog" aria-modal="true" aria-labelledby="policyModalTitle">
        <div class="vj-modal__header">
          <h3 class="vj-modal__title" id="policyModalTitle">{{ __('Title') }}</h3>
          <button type="button" class="vj-modal__close" aria-label="{{ __('Close') }}" data-close="1">
            <i class="fa-solid fa-xmark"></i>
          </button>
        </div>

        <div class="vj-modal__body" id="policyModalBody">
          {{-- content injected here --}}
        </div>
      </div>
    </div>

    {{-- ✅ CONTENT TEMPLATES (HIDDEN) --}}

    {{-- PRIVACY NOTICE --}}
    <template id="tpl-privacidad">
      <div class="policy-content">
        <p><strong>{{ __('PREPARED BY VIAJERO CAR RENTAL, FOR THE PROTECTION OF ITS CLIENTS\' DATA') }}</strong></p>
        <p>{{ __('Individual with Business Activity, with fiscal address at Blvd. Bernardo Quintana 8300, Centro Sur, 76090 Santiago de Querétaro, Qro., will process your personal data collected for identification, operation, administration and marketing purposes related to vehicle rental. If you do not express your opposition for your personal data to be processed, it will be understood that you have given your consent for it.') }}</p>
        <p>{{ __('Personal data may be collected directly —such as when provided personally—, or indirectly —through telephone directories, services or employment directories—, and include, among others: First and last names, gender, date of birth, address, landline and/or mobile phone, email, etc. Regarding financial data, in accordance with the exceptions set forth in articles 8, 10 and 37 of the Law, they are not considered to require express consent to be used.') }}</p>
        <p>{{ __('The data will be used strictly for activities arising from providing you with a good or service, which in an illustrative but not limited manner are described below: Car rental, as well as the provision of services inherent to your leisure or business trips, updating and confirmation, for promotional, advertising, contracting and credit purposes, conducting studies on consumption habits and preferences, preparing financial options, collection and payment enforcement, and contacting you for any matter related to the services we provide or to this privacy policy. In accordance with the provisions of Article 37, Section III of the Law, personal data will not be transferred to third parties without consent, except for those companies that are part of our group or business partners.') }}</p>
        <p>{{ __('You may exercise your ARCO rights (Access, Rectification, Cancellation and Opposition) starting July 6, 2018, through the procedure we have implemented. Simply contact Mr. Juan de Dios Hernandez Resendiz, head of customer service, by phone at') }} <strong>(442) 716 9793</strong>, {{ __('or by email at') }} <a href="mailto:reservaciones@viajerocar-rental.mx">reservaciones@viajerocar-rental.mx</a>, {{ __('drafting a document in Spanish, including your full name, a simple copy of your official ID or, in electronic media, a digital version (scan), an indication of the email or physical address designated for notifications and a contact phone number. Once done, within a maximum of 20 business days you will be informed of the validity of said request; if a response letter is sent to the indicated physical address, the twenty days will be considered fulfilled at the time the document is handed over to the postal service.') }}</p>
        <p>{{ __('Lack of opposition for personal data to be transferred as mentioned will be interpreted as having given your consent for it.') }}</p>
        <p>{{ __('We reserve the right to make modifications or updates to this privacy notice at any time, in response to legislative developments, internal policies or new requirements for the provision or offering of our services or products. These modifications will be available to the public through our website, email or in writing.') }}</p>
      </div>
    </template>

    {{-- CLEANING POLICY --}}
    <template id="tpl-limpieza">
      <div class="policy-content">
        <h4>{{ __('Smoke-free, dirt and unpleasant odors policy – Viajero Car Rental') }}</h4>
        <p>
          <strong>{{ __('Viajero Car Rental') }}</strong> {{ __('delivers all our vehicles clean, ventilated and in optimal hygiene conditions for your comfort and for the next customers. Therefore, we have a') }} <strong>{{ __('strict smoke-free, excessive dirt and unpleasant odors policy') }}</strong>.
        </p>

        <p>
          <strong>{{ __('Remember:') }}</strong> {{ __('Returning the vehicle with tobacco smoke, excessive dirt or strong odors requiring deep cleaning will result in a') }} <strong>$4,000 MXN (approx. $250 USD)</strong> {{ __('charge, which will be applied to the payment method registered in your rental contract.') }}
        </p>

        <h5>{{ __('What is considered dirt or odor that violates this policy?') }}</h5>
        <p>{{ __('Among others, the following are reasons to apply the special cleaning charge:') }}</p>
        <ul>
          <li>{{ __('Strong smell of') }} <strong>{{ __('cigarette, tobacco, vape or marijuana') }}</strong> {{ __('inside the vehicle.') }}</li>
          <li>{{ __('Remains of') }} <strong>{{ __('ash, cigarette butts') }}</strong> {{ __('or burns on upholstery.') }}</li>
          <li>{{ __('Excessive stains from food, drinks, grease, mud, vomit or other difficult-to-remove residues.') }}</li>
          <li>{{ __('Strong odors of chemicals, chlorine, moisture, garbage or decomposing food.') }}</li>
          <li>{{ __('Accumulation of pet hair, organic residues or insects due to lack of basic cleaning.') }}</li>
        </ul>

        <h5>{{ __('How is this charge validated?') }}</h5>
        <ul>
          <li>{{ __('At the time of return, the vehicle is inspected by') }} <strong>{{ __('Viajero Car Rental') }}</strong> {{ __('staff.') }}</li>
          <li>{{ __('If dirt or odor requiring deep cleaning is detected,') }} <strong>{{ __('photographs will be taken') }}</strong> {{ __('and the condition of the vehicle will be documented.') }}</li>
          <li>{{ __('The') }} <strong>{{ __('fixed fee of $4,000 MXN') }}</strong> {{ __('will be applied for deep cleaning and deodorization.') }}</li>
        </ul>

        <h5>{{ __('What can you do to avoid this charge?') }}</h5>
        <ul>
          <li>{{ __('Do not smoke inside the vehicle under any circumstances.') }}</li>
          <li>{{ __('Avoid consuming food or beverages that may spill or leave strong odors.') }}</li>
          <li>{{ __('Remove all trash and personal belongings before returning the vehicle.') }}</li>
          <li>{{ __('If traveling with pets, use covers, blankets or accessories that protect the upholstery.') }}</li>
        </ul>

        <p>
          {{ __('This policy aims to ensure that every vehicle from') }} <strong>{{ __('Viajero Car Rental') }}</strong>
          {{ __('is always delivered in the best possible condition for all our customers.') }}
        </p>
      </div>
    </template>

    {{-- RENTAL REQUIREMENTS --}}
    <template id="tpl-requisitos">
      <div class="policy-content">
        <h4>{{ __('Minimum age') }}</h4>
        <p>
          {{ __('The minimum age to rent a vehicle with') }} <strong>{{ __('Viajero Car Rental') }}</strong> {{ __('is 21 years. A young driver fee applies for customers between 21 and 24 years old, with a cost of') }}
          <strong>$241 MXN {{ __('per day') }}</strong>. {{ __('This fee may be subject to airport fees and other applicable charges plus VAT.') }}
        </p>

        <h4>{{ __('Valid driver\'s license') }}</h4>
        <p>
          {{ __('The primary customer must present a valid driver\'s license from their country of residence with a minimum of one year of seniority. Viajero Car Rental reserves the right to verify the license and deny the rental if it does not meet the guidelines. For additional drivers, only a valid license is required, without a minimum seniority.') }}
        </p>

        <h4>{{ __('Valid official ID') }}</h4>
        <p>
          {{ __('A valid official photo ID must be presented. For Mexican citizens, an INE/IFE with at least one year of seniority is required. For foreigners, a passport, Passport Card, FM2, FM3 or Permanent Residence is accepted.') }}
        </p>

        <h4>{{ __('Credit card in the renter\'s name') }}</h4>
        <p>
          {{ __('It is essential to present a bank credit card in the renter\'s name to start the rental. The final payment can be made with a debit card. American Express, Visa and MasterCard are accepted. The card must have a minimum of one year of seniority. Viajero Car Rental may reject damaged, illegible cards or special program cards that are not compatible.') }}
        </p>

        <h4>{{ __('Right to deny service') }}</h4>
        <p>
          {{ __('Viajero Car Rental reserves the right not to complete the rental if any of the requirements are not met or if any irregularity is detected in the documentation presented.') }}
        </p>
      </div>
    </template>

    {{-- PRE CHECK-IN --}}
    <template id="tpl-precheckin">
      <div class="policy-content">
        <h4>{{ __('Why activate Pre Check-In with Viajero?') }}</h4>
        <p><strong>{{ __('Save time and avoid lines') }}</strong> {{ __('by completing your online registration before you arrive.') }}</p>

        <p><strong>{{ __('You only need to provide:') }}</strong></p>
        <ul>
          <li>{{ __('Official ID') }}</li>
          <li>{{ __('Valid driver\'s license') }}</li>
          <li>{{ __('Contact information') }}</li>
          <li>{{ __('Payment method') }}</li>
        </ul>

        <p>{{ __('When you arrive, your contract will be ready and your vehicle prepared.') }} <strong>{{ __('Start your trip without waiting.') }}</strong></p>

        <h4>{{ __('Ready to start?') }}</h4>
        <ul>
          <li>{{ __('If you send your information in advance, we expedite your delivery process.') }}</li>
          <li>{{ __('Present your valid ID and driver\'s license when picking up the vehicle.') }}</li>
          <li>{{ __('Our team will assist you at the counter for final signature and key delivery.') }}</li>
          <li>{{ __('Inspect your vehicle and begin your Viajero experience.') }}</li>
        </ul>
      </div>
    </template>

    {{-- CANCELLATION INSURANCE --}}
    <template id="tpl-seguro-cancelacion">
      <div class="policy-content">
        <h4>{{ __('Cancellation insurance terms and conditions – Viajero Car Rental') }}</h4>
        <p>
          {{ __('To cancel a prepaid reservation and begin the refund process for the total prepaid amount, the customer must contact') }} <strong>{{ __('Viajero Car Rental') }}</strong>
          {{ __('by phone at') }} <strong>01 (442) 716 9793</strong> {{ __('or email') }} <a href="mailto:reservaciones@viajerocarental.com">reservaciones@viajerocarental.com</a>.
        </p>
        <p>{{ __('If the') }} <strong>{{ __('cancellation insurance') }}</strong> {{ __('was purchased, it will refund') }} <strong>{{ __('100% of the total prepaid rental amount') }}</strong>.</p>
        <p>{{ __('The customer may request their refund up to') }} <strong>{{ __('24 hours before') }}</strong> {{ __('the scheduled start date and time of the rental.') }}</p>
        <p>{{ __('The') }} <strong>$200 MXN</strong> {{ __('payment for cancellation insurance') }} <strong>{{ __('is non-refundable') }}</strong>.</p>
        <p>
          {{ __('The refund will be processed and reflected on the card used for the prepayment, within a period of up to') }} <strong>{{ __('8 business days') }}</strong>.
        </p>
      </div>
    </template>

    {{-- CHARGES AND TAXES --}}
    <template id="tpl-cargos">
      <div class="policy-content">
        <h4>{{ __('Tax (VAT)') }}</h4>
        <p>{{ __('All charges related to the vehicle rental are subject to the applicable') }} <strong>{{ __('VAT') }}</strong>.</p>

        <h4>{{ __('Airport recovery fee') }}</h4>
        <p>{{ __('Applies only to branches located within an airport.') }}</p>

        <h4>{{ __('Telemetry fee') }}</h4>
        <p>{{ __('Corresponds to') }} <strong>{{ __('7.5% on all rental concepts') }}</strong>.</p>

        <h4>{{ __('Drop-off / One-Way fee') }}</h4>
        <p>{{ __('Applies when the customer picks up the vehicle in one city and returns it in a different city.') }}</p>

        <h4>{{ __('Fuel charge') }}</h4>
        <p>{{ __('Fuel is not included in the rate. The vehicle must be returned with a full tank.') }}</p>
      </div>
    </template>

    {{-- PAYMENT METHODS --}}
    <template id="tpl-pago">
      <div class="policy-content">
        <h4>{{ __('Payment methods accepted by Viajero Car Rental') }}</h4>
        <h5>{{ __('Bank cards (credit and debit)') }}</h5>
        <ul>
          <li><strong>{{ __('American Express') }}</strong></li>
          <li><strong>{{ __('Visa') }}</strong></li>
          <li><strong>{{ __('Mastercard') }}</strong></li>
        </ul>

        <h5>{{ __('PayPal') }}</h5>
        <p>{{ __('You can make your payment via') }} <strong>{{ __('PayPal') }}</strong> {{ __('when enabled or through a secure link.') }}</p>

        <h5>{{ __('Cash payment') }}</h5>
        <p><strong>{{ __('Yes, we accept cash') }}</strong> {{ __('for the total rental amount, but the guarantee is made with a card.') }}</p>

        <h5>{{ __('OXXO deposits') }}</h5>
        <p>{{ __('Requires coordination with an advisor for reference and confirmation.') }}</p>

        <h5>{{ __('Mercado Pago') }}</h5>
        <p>{{ __('Payment via link or QR generated by an advisor.') }}</p>
      </div>
    </template>

    {{-- CANCELLATIONS --}}
    <template id="tpl-cancelaciones">
      <div class="policy-content">
        <h4>{{ __('Cancellation, no-show and refund policy – Viajero Car Rental') }}</h4>

        <h5>{{ __('100% refund') }}</h5>
        <p>{{ __('Cancellation') }} <strong>{{ __('21 days or more') }}</strong> {{ __('before the rental date (prepayment).') }}</p>

        <h5>{{ __('50% refund') }}</h5>
        <p>{{ __('Cancellation between') }} <strong>{{ __('20 and 15 days') }}</strong> {{ __('in advance (prepayment).') }}</p>

        <h5>{{ __('25% refund') }}</h5>
        <p>{{ __('Cancellation between') }} <strong>{{ __('14 and 7 days') }}</strong> {{ __('in advance (prepayment).') }}</p>

        <h5>{{ __('No refund') }}</h5>
        <p>{{ __('Cancellation') }} <strong>{{ __('6 days or less') }}</strong> {{ __('and') }} <strong>{{ __('No-show') }}</strong> {{ __('cases.') }}</p>

        <h5>{{ __('Procedure') }}</h5>
        <p>{{ __('Phone:') }} <strong>01 (442) 303 2668</strong> · {{ __('Email:') }} <a href="mailto:reservaciones@viajerocar-rental.mx">reservaciones@viajerocar-rental.mx</a></p>
      </div>
    </template>

    {{-- DEPOSITS (GUARANTEE / PRE-AUTHORIZATION) --}}
    <template id="tpl-depositos">
      <div class="policy-content">
        <h4>{{ __('Rental guarantee or pre-authorizations – Viajero Car Rental') }}</h4>
        <p>
          {{ __('The') }} <strong>{{ __('guarantee') }}</strong> {{ __('or') }} <strong>{{ __('pre-authorization') }}</strong> {{ __('is an amount temporarily held on the customer\'s card as a backup for the rental.') }} <strong>{{ __('It is not a final charge') }}</strong> {{ __('when opening the contract.') }}
        </p>

        <h5>{{ __('Release') }}</h5>
        <p>
          {{ __('It is generally released within') }} <strong>{{ __('48 business hours') }}</strong> {{ __('after return, or within the period indicated by the issuing bank, provided the vehicle is returned in proper condition.') }}
        </p>

        <h5>{{ __('Reference guarantee table') }}</h5>
        <div class="tabla-garantias">
          <table class="tabla-viajero">
            <thead>
              <tr>
                <th>{{ __('Category') }}</th>
                <th>{{ __('Size') }}</th>
                <th>{{ __('LDW') }}</th>
                <th>{{ __('PDW') }}</th>
                <th>{{ __('CDW 10%') }}</th>
                <th>{{ __('CDW 20%') }}</th>
                <th>{{ __('CDW declined') }}</th>
              </tr>
            </thead>
            <tbody>
              <tr><td>{{ __('C') }}</td><td>{{ __('Compact Chevrolet Aveo or similar') }}</td><td>$5,000 MXN</td><td>$8,000 MXN</td><td>$15,000 MXN</td><td>$25,000 MXN</td><td>$330,000 MXN</td></tr>
              <tr><td>{{ __('D') }}</td><td>{{ __('Intermediate Nissan Virtus or similar') }}</td><td>$5,000 MXN</td><td>$8,000 MXN</td><td>$18,000 MXN</td><td>$25,000 MXN</td><td>$380,000 MXN</td></tr>
              <tr><td>{{ __('E') }}</td><td>{{ __('Full Size Volkswagen Jetta or similar') }}</td><td>$5,000 MXN</td><td>$8,000 MXN</td><td>$20,000 MXN</td><td>$30,000 MXN</td><td>$500,000 MXN</td></tr>
              <tr><td>{{ __('F') }}</td><td>{{ __('Full size Camry or similar') }}</td><td>$5,000 MXN</td><td>$15,000 MXN</td><td>$30,000 MXN</td><td>$40,000 MXN</td><td>$650,000 MXN</td></tr>
              <tr><td>{{ __('IC') }}</td><td>{{ __('Compact SUV Jeep Renegade or similar') }}</td><td>$5,000 MXN</td><td>$8,000 MXN</td><td>$20,000 MXN</td><td>$30,000 MXN</td><td>$500,000 MXN</td></tr>
              <tr><td>{{ __('I') }}</td><td>{{ __('Midsize SUV Volkswagen Taos or similar') }}</td><td>$5,000 MXN</td><td>$10,000 MXN</td><td>$30,000 MXN</td><td>$40,000 MXN</td><td>$600,000 MXN</td></tr>
              <tr><td>{{ __('IB') }}</td><td>{{ __('Compact Family SUV Toyota Avanza or similar') }}</td><td>$5,000 MXN</td><td>$8,000 MXN</td><td>$18,000 MXN</td><td>$25,000 MXN</td><td>$400,000 MXN</td></tr>
              <tr><td>{{ __('M') }}</td><td>{{ __('Minivan Honda Odyssey or similar') }}</td><td>$10,000 MXN</td><td>$20,000 MXN</td><td>$30,000 MXN</td><td>$40,000 MXN</td><td>$800,000 MXN</td></tr>
              <tr><td>{{ __('L') }}</td><td>{{ __('12-Passenger Van Toyota Hiace or similar') }}</td><td>$10,000 MXN</td><td>$20,000 MXN</td><td>$30,000 MXN</td><td>$40,000 MXN</td><td>$800,000 MXN</td></tr>
              <tr><td>{{ __('H') }}</td><td>{{ __('Double Cab Pickup Nissan Frontier or similar') }}</td><td>$10,000 MXN</td><td>$20,000 MXN</td><td>$30,000 MXN</td><td>$40,000 MXN</td><td>$600,000 MXN</td></tr>
              <tr><td>{{ __('HI') }}</td><td>{{ __('4x4 Double Cab Pickup Toyota Tacoma or similar') }}</td><td>$10,000 MXN</td><td>$20,000 MXN</td><td>$30,000 MXN</td><td>$40,000 MXN</td><td>$900,000 MXN</td></tr>
            </tbody>
          </table>
        </div>

        <p style="margin-top: 1rem;"><em>{{ __('Amounts subject to change without prior notice.') }}</em></p>
      </div>
    </template>

   {{-- INFRACTIONS POLICY --}}
<template id="tpl-infracciones">
  <div class="policy-content">
    <h4>{{ __('Infractions policy') }}</h4>

    <p><strong>{{ __('What if I get a ticket?') }}</strong></p>

    <p>{{ __('You must report it immediately through the following channels:') }}</p>
    <ul>
      <li>
        <strong>{{ __('Phone:') }}</strong>
        <a href="tel:524427169793">(+52) 442 716 9793</a>
      </li>
      <li>
        <strong>{{ __('Email:') }}</strong>
        <a href="mailto:reservaciones@viajerocarental.com">reservaciones@viajerocarental.com</a>
      </li>
      <li>
        <strong>{{ __('Technical Support:') }}</strong>
        <a href="mailto:soportetecnico@viajerocarental.com">soportetecnico@viajerocarental.com</a>
      </li>
    </ul>

    <p>
      {{ __('The customer will be responsible for all fines incurred during the rental period. These may amount up to') }} <strong>$25,000.00 MXN</strong>, {{ __('depending on the violation and local traffic regulations.') }}
    </p>

    <p>
      <em>{{ __('Additionally, an administrative fee of') }} <strong>$1,000.00 MXN</strong> {{ __('(plus fees and taxes) will apply.') }}</em>
    </p>
  </div>
</template>

    {{-- TERMS AND CONDITIONS --}}
    <template id="tpl-terminos">
      <div class="policy-content">
        <h4>{{ __('Policies and Procedures') }}</h4>
        <p>
          {{ __('The approximate total rental amount is based on the information provided at the time of booking. The driver must present a valid credit card with sufficient available balance, a valid driver\'s license and an official ID when opening the contract...') }}
        </p>
        <p>
          {{ __('Customer service and inquiries:') }}
          {{ __('Phone:') }} <a href="tel:+524423032668">01 (442) 303 2668</a> ·
          {{ __('Email:') }} <a href="mailto:reservaciones@viajerocar-rental.mx">reservaciones@viajerocar-rental.mx</a>
        </p>
      </div>
    </template>

</main>

@endsection

@section('js-vistaPoliticas')
    <!-- jQuery (required for Select2) -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <!-- Select2 JS -->
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <!-- Flatpickr -->
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script src="https://cdn.jsdelivr.net/npm/flatpickr/dist/l10n/es.js"></script>
     <!-- Flatpickr CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <!-- Your custom JS -->
    <script src="{{ asset('js/politicas.js') }}"></script>
<script>
    // Generate icon map from PHP (executed before translation)
    window.iconosPorId = {
        @foreach($ciudades as $ciudad)
            @foreach($ciudad->sucursalesActivas as $suc)
                @php
                    $name = strtolower($suc->nombre);
                    $icon = 'fa-building';

                    // ✈️ Airport
                    if (str_contains($name, 'aeropuerto')) {
                        $icon = 'fa-plane-departure';
                    }
                    elseif (str_contains($name, 'central') && !str_contains($name, 'plaza central park')) {
                        $icon = 'fa-bus';
                    }
                    // 🚌 Bus Terminal
                    elseif (str_contains($name, 'terminal')) {
                        $icon = 'fa-bus';
                    }
                    // 🏢 Office / Plaza Central Park (building)
                    else {
                        $icon = 'fa-building';
                    }
                @endphp
                {{ $suc->id_sucursal }}: '{{ $icon }}',
            @endforeach
        @endforeach
    };
</script>

@endsection
