@extends('layouts.Usuarios')

@section('Titulo', __('Contact'))

@section('css-vistaContacto')
  <link rel="stylesheet" href="{{ asset('css/contacto.css') }}">
@endsection

@section('contenidoContacto')
<main class="page">

  {{-- ===== HERO ===== --}}
  <section class="hero" aria-label="{{ __('Header') }}">
    <div class="hero-bg">
      <img src="{{ asset('img/contacto.webp') }}"
           alt="{{ __('Contact') }}"
           width="1600" height="900"
           loading="eager"
           fetchpriority="high"
           decoding="async">
    </div>
    <div class="hero-overlay"></div>
    <div class="hero-content">
      <h1>{{ __('Let\'s talk!') }} <span>{{ __('Contact us') }}</span></h1>
      <p>{{ __('We are ready to help you with your next rental') }}</p>
    </div>

    <div class="hero-chips" aria-label="{{ __('Branches') }}">
      <span class="chip"><i class="fa-solid fa-location-dot"></i> {{ __('Pick-up Central Park Office, Querétaro') }}</span>
      <span class="chip"><i class="fa-solid fa-location-dot"></i> {{ __('Pick-up Querétaro International Airport') }}</span>
      <span class="chip"><i class="fa-solid fa-location-dot"></i> {{ __('Pick-up Querétaro Bus Station') }}</span>
    </div>
  </section>

  {{-- ===== MAPAS ===== --}}
  <section class="maps" aria-label="{{ __('Location maps') }}">
    {{-- Central Park --}}
    <div class="map-card">
      <div class="map-head">
        <i class="fa-solid fa-map-location-dot"></i>
        {{ __('Central Park Office, Querétaro') }}
      </div>
      <div class="map-body">
        <iframe
          src="https://www.google.com/maps?q=20.57334,-100.36168&z=15&output=embed"
          title="{{ __('Central Park Office, Querétaro') }}"
          style="border:0;"
          allowfullscreen=""
          loading="lazy"
          referrerpolicy="no-referrer-when-downgrade">
        </iframe>
      </div>
    </div>

    {{-- Aeropuerto Internacional de Querétaro --}}
    <div class="map-card">
      <div class="map-head">
        <i class="fa-solid fa-plane-departure"></i>
        {{ __('Querétaro International Airport') }}
      </div>
      <div class="map-body">
        <iframe
          src="https://www.google.com/maps?q=20.62280,-100.18758&z=15&output=embed"
          title="{{ __('Querétaro International Airport') }}"
          style="border:0;"
          allowfullscreen=""
          loading="lazy"
          referrerpolicy="no-referrer-when-downgrade">
        </iframe>
      </div>
    </div>

    {{-- Central de Autobuses de Querétaro --}}
    <div class="map-card">
      <div class="map-head">
        <i class="fa-solid fa-bus"></i>
        {{ __('Querétaro Bus Station') }}
      </div>
      <div class="map-body">
        <iframe
          src="https://www.google.com/maps?q=20.57820,-100.35934&z=15&output=embed"
          title="{{ __('Querétaro Bus Station') }}"
          style="border:0;"
          allowfullscreen=""
          loading="lazy"
          referrerpolicy="no-referrer-when-downgrade">
        </iframe>
      </div>
    </div>
  </section>

  {{-- ===== FORMULARIO DE CONTACTO ===== --}}
  <section class="contact-grid" aria-label="{{ __('Contact form') }}">
    <div class="contact-left">
      <div class="form-card">
        <h2>{{ __('Contact us') }}</h2>

        {{-- === ALERTAS === --}}
        @if (session('ok'))
          <div class="alert alert-success" role="alert">
            <i class="fa-solid fa-check"></i> {{ session('ok') }}
          </div>
        @endif

        @if ($errors->any())
          <div class="alert alert-danger" role="alert">
            <i class="fa-solid fa-triangle-exclamation"></i> {{ __('Please check the fields marked in red.') }}
          </div>
        @endif

        {{-- === FORMULARIO === --}}
        <form id="contactForm" class="contact-form" method="POST" action="{{ route('contacto.store') }}">
          @csrf

          {{-- Honeypot invisible --}}
          <input type="text" name="company" value="" tabindex="-1" autocomplete="off"
                 style="position:absolute;left:-10000px;opacity:0;height:0;width:0" aria-hidden="true">

          <div class="row">
            <div class="field">
              <label for="fName">{{ __('Full name') }}</label>
              <input id="fName" name="name" type="text" required autocomplete="name"
                     placeholder="{{ __('Your full name') }}"
                     value="{{ old('name') }}"
                     class="@error('name') is-invalid @enderror">
              @error('name') <small class="error">{{ $message }}</small> @enderror
            </div>
          </div>

          <div class="row grid-2">
            <div class="field">
              <label for="fPhone">{{ __('Mobile') }}</label>
              <input id="fPhone" name="phone" type="tel" required
                     autocomplete="tel" inputmode="tel"
                     placeholder="55 1234 5678"
                     value="{{ old('phone') }}"
                     class="@error('phone') is-invalid @enderror">
              <span class="hint">{{ __('Include area code. Ex.: 442 123 4567') }}</span>
              @error('phone') <small class="error">{{ $message }}</small> @enderror
            </div>
            <div class="field">
              <label for="fEmail">{{ __('Email address') }}</label>
              <input id="fEmail" name="email" type="email" required autocomplete="email"
                     placeholder="{{ __('youremail@domain.com') }}"
                     value="{{ old('email') }}"
                     class="@error('email') is-invalid @enderror">
              @error('email') <small class="error">{{ $message }}</small> @enderror
            </div>
          </div>

          <div class="row">
            <div class="field">
              <label for="fSubject">{{ __('Subject') }}</label>
              <input id="fSubject" name="subject" type="text"
                     placeholder="{{ __('How can we help you?') }}"
                     value="{{ old('subject') }}"
                     class="@error('subject') is-invalid @enderror">
              @error('subject') <small class="error">{{ $message }}</small> @enderror
            </div>
          </div>

          <div class="row">
            <div class="field">
              <label for="fMessage">{{ __('Message') }}</label>
              <textarea id="fMessage" name="message" rows="5" required maxlength="800"
                        placeholder="{{ __('Write your message here...') }}"
                        class="@error('message') is-invalid @enderror">{{ old('message') }}</textarea>
              <span class="hint">
                <span id="charCount">{{ strlen(old('message','')) }}</span>/800
              </span>
              @error('message') <small class="error">{{ $message }}</small> @enderror
            </div>
          </div>

          <label class="cbox">
            <input type="checkbox" id="promo" name="promociones" value="1" {{ old('promociones') ? 'checked' : '' }}>
            <span class="checkmark"></span>
            <span>{{ __('I wish to receive alerts, confirmations and promotions by email or phone.') }}</span>
          </label>

          <div class="form-actions">
            {{-- WhatsApp funcional --}}
            <a class="btn btn-ghost" id="btnWhatsapp"
               href="https://wa.me/524427169793"
               target="_blank" rel="noopener noreferrer">
              <i class="fa-brands fa-whatsapp"></i> WhatsApp
            </a>
            {{-- Enviar formulario --}}
            <button type="submit" class="btn btn-primary">
              {{ __('Send message') }}
            </button>
          </div>
        </form>
      </div>

      <div class="disclaimer">
        {{ __('By submitting this form you agree to our') }}
        <a href="{{ route('rutaPoliticas') }}" target="_blank" rel="noopener noreferrer">{{ __('Privacy Policy') }}</a>
        {{ __('and our') }}
        <a href="{{ route('rutaPoliticas') }}" target="_blank" rel="noopener noreferrer">{{ __('Terms of Use') }}</a>
      </div>
    </div>

    <div class="contact-right">
      <div class="card-cta" aria-label="{{ __('Support') }}">
        <div class="cta-icon"><i class="fa-solid fa-headset"></i></div>
        <h3>{{ __('Support & assistance') }}</h3>
        <p>{{ __('Our team is available to answer your questions, provide quotes and help you book.') }}</p>
        <div class="cta-actions">
          {{-- Llamada funcional --}}
          <a class="btn btn-secondary" href="tel:+524421234567">
            <i class="fa-solid fa-phone"></i> {{ __('Call now') }}
          </a>
          {{-- WhatsApp funcional --}}
          <a class="btn btn-primary" id="ctaWhats"
             href="https://wa.me/524427169793"
             target="_blank" rel="noopener noreferrer">
            <i class="fa-brands fa-whatsapp"></i> WhatsApp
          </a>
        </div>
        <div class="cta-hours">
          <i class="fa-regular fa-clock"></i> {{ __('Mon–Sun · 8:00 AM – 10:00 PM') }}
        </div>
      </div>
      <img class="illus"
           src="{{ asset('img/contacto2.webp') }}"
           alt="{{ __('Viajero Support') }}"
           width="800" height="600"
           loading="lazy"
           decoding="async">
    </div>
  </section>
</main>
@endsection

@section('js-vistaContacto')
  <script defer src="{{ asset('js/contacto.js') }}"></script>
@endsection
