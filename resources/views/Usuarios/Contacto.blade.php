@extends('layouts.Usuarios')

@section('Titulo', __('messages.contacto_titulo'))

@section('css-vistaContacto')
  <link rel="stylesheet" href="{{ asset('css/contacto.css') }}">
@endsection

@section('contenidoContacto')
<main class="page">

  {{-- ===== HERO ===== --}}
  <section class="hero" aria-label="cabecera">
    <div class="hero-bg">
      <img src="{{ asset('img/contacto.png') }}" alt="{{ __('messages.contacto_titulo') }}">
    </div>
    <div class="hero-overlay"></div>
    <div class="hero-content">
      <h1>{{ __('messages.hablemos') }} <span>{{ __('messages.contactanos') }}</span></h1>
      <p>{{ __('messages.estamos_listos') }}</p>
    </div>

    <div class="hero-chips" aria-label="sucursales">
      <span class="chip"><i class="fa-solid fa-location-dot"></i> {{ __('messages.pickup_oficina_central') }}</span>
      <span class="chip"><i class="fa-solid fa-location-dot"></i> {{ __('messages.pickup_aeropuerto') }}</span>
      <span class="chip"><i class="fa-solid fa-location-dot"></i> {{ __('messages.pickup_central_autobuses') }}</span>
    </div>
  </section>

  {{-- ===== MAPAS ===== --}}
  <section class="maps" aria-label="mapas de ubicación">
    {{-- Central Park --}}
    <div class="map-card">
      <div class="map-head">
        <i class="fa-solid fa-map-location-dot"></i>
        {{ __('messages.oficina_central_park') }}
      </div>
      <div class="map-body">
         <a href="https://www.google.com/maps?q=20.57334,-100.36168&z=15&output=embed" target="_blank">
        <iframe
          src="https://www.google.com/maps?q=20.57334,-100.36168&z=15&output=embed"
          style="border:0;"
          allowfullscreen=""
          loading="lazy"
          referrerpolicy="no-referrer-when-downgrade">
        </iframe>
        </a>
      </div>
    </div>

    {{-- Aeropuerto Internacional de Querétaro --}}
    <div class="map-card">
      <div class="map-head">
        <i class="fa-solid fa-plane-departure"></i>
        {{ __('messages.aeropuerto_queretaro') }}
      </div>
      <div class="map-body">
        <iframe
        src="https://www.google.com/maps?q=20.62280,-100.18758&z=15&output=embed"
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
        {{ __('messages.central_autobuses') }}
      </div>
      <div class="map-body">
        <iframe
          src="https://www.google.com/maps?q=20.57820,-100.35934&z=15&output=embed"
          style="border:0;"
          allowfullscreen=""
          loading="lazy"
          referrerpolicy="no-referrer-when-downgrade">
        </iframe>
      </div>
    </div>
  </section>

  {{-- ===== FORMULARIO DE CONTACTO ===== --}}
  <section class="contact-grid" aria-label="formulario de contacto">
    <div class="contact-left">
      <div class="form-card">
        <h2>{{ __('messages.contactanos_form') }}</h2>

        {{-- === ALERTAS === --}}
        @if (session('ok'))
          <div class="alert alert-success" role="alert">
            <i class="fa-solid fa-check"></i> {{ session('ok') }}
          </div>
        @endif

        @if ($errors->any())
          <div class="alert alert-danger" role="alert">
            <i class="fa-solid fa-triangle-exclamation"></i> {{ __('messages.revisa_campos') }}
          </div>
        @endif

        {{-- === FORMULARIO === --}}
        <form id="contactForm" class="contact-form" method="POST" action="{{ route('contacto.store') }}" novalidate>
          @csrf

          {{-- Honeypot invisible --}}
          <input type="text" name="company" tabindex="-1" autocomplete="off"
                 style="position:absolute;left:-10000px;opacity:0;height:0;width:0" aria-hidden="true">

          <div class="row">
            <div class="field">
              <label for="fName">{{ __('messages.nombre_completo') }}</label>
              <input id="fName" name="name" required placeholder="{{ __('messages.nombre_placeholder') }}"
                     value="{{ old('name') }}" class="@error('name') is-invalid @enderror">
              @error('name') <small class="error">{{ $message }}</small> @enderror
            </div>
          </div>

          <div class="row grid-2">
            <div class="field">
              <label for="fPhone">{{ __('messages.movil') }}</label>
              <input id="fPhone" name="phone" type="tel" required placeholder="{{ __('messages.telefono_placeholder') }}"
                     value="{{ old('phone') }}" class="@error('phone') is-invalid @enderror">
              <span class="hint">{{ __('messages.hint_telefono') }}</span>
              @error('phone') <small class="error">{{ $message }}</small> @enderror
            </div>
            <div class="field">
              <label for="fEmail">{{ __('messages.correo_electronico') }}</label>
              <input id="fEmail" name="email" type="email" required placeholder="{{ __('messages.email_placeholder') }}"
                     value="{{ old('email') }}" class="@error('email') is-invalid @enderror">
              @error('email') <small class="error">{{ $message }}</small> @enderror
            </div>
          </div>

          <div class="row">
            <div class="field">
              <label for="fSubject">{{ __('messages.asunto') }}</label>
              <input id="fSubject" name="subject" placeholder="{{ __('messages.asunto_placeholder') }}"
                     value="{{ old('subject') }}" class="@error('subject') is-invalid @enderror">
              @error('subject') <small class="error">{{ $message }}</small> @enderror
            </div>
          </div>

          <div class="row">
            <div class="field">
              <label for="fMessage">{{ __('messages.mensaje') }}</label>
              <textarea id="fMessage" name="message" rows="5" required maxlength="800"
                        placeholder="{{ __('messages.mensaje_placeholder') }}"
                        class="@error('message') is-invalid @enderror">{{ old('message') }}</textarea>
                        <span class="hint">
                <span id="charCount">{{ strlen(old('message','')) }}</span>/800 {{ __('messages.caracteres') }}
            </span>
            @error('message') <small class="error">{{ $message }}</small> @enderror
            {{-- Script para el contador de caracteres --}}
            <script>
                const mensaje = document.getElementById('fMessage');
                const contador = document.getElementById('charCount');

                // Inicializa el contador en caso de que haya valor previo
                contador.textContent = mensaje.value.length;

                mensaje.addEventListener('input', () => {
                    contador.textContent = mensaje.value.length;
                });
            </script>
            </div>
          </div>

          <label class="cbox">
            <input type="checkbox" id="promo" name="promociones" value="1" {{ old('promociones') ? 'checked' : '' }}>
            <span class="checkmark"></span>
            <span>{{ __('messages.recibir_promociones') }}</span>
          </label>

          <div class="form-actions">
            {{-- WhatsApp funcional --}}
            <a class="btn btn-ghost" id="btnWhatsapp" href="https://wa.me/524427169793" target="_blank">
              <i class="fa-brands fa-whatsapp"></i> {{ __('messages.whatsapp') }}
            </a>
            {{-- Enviar formulario --}}
            <button type="submit" class="btn btn-primary">
              {{ __('messages.enviar_mensaje') }}
            </button>
          </div>
        </form>
      </div>

      <div class="disclaimer">
        {{ __('messages.acepto_aviso') }}
        <a href="{{ route('rutaPoliticas') }}" target="_blank">{{ __('messages.aviso_privacidad') }}</a> {{ __('messages.y_nuestras') }}
        <a href="{{ route('rutaPoliticas') }}" target="_blank">{{ __('messages.politica_uso') }}</a>
      </div>
    </div>

    <div class="contact-right">
      <div class="card-cta" aria-label="soporte">
        <div class="cta-icon"><i class="fa-solid fa-headset"></i></div>
        <h3>{{ __('messages.soporte_atencion') }}</h3>
        <p>{{ __('messages.soporte_texto') }}</p>
        <div class="cta-actions">
           {{-- Llamada funcional --}}
          <a class="btn btn-secondary" href="tel:+524421234567">
            <i class="fa-solid fa-phone"></i> {{ __('messages.llamar_ahora') }}
          </a>
          {{-- WhatsApp funcional --}}
          <a class="btn btn-primary" id="ctaWhats" href="https://wa.me/524427169793" target="_blank">
            <i class="fa-brands fa-whatsapp"></i> {{ __('messages.whatsapp') }}
          </a>
        </div>
        <div class="cta-hours">
          <i class="fa-regular fa-clock"></i> {{ __('messages.horario_atencion') }}
        </div>
      </div>
      <img class="illus" alt="{{ __('messages.soporte_viajero') }}" src="{{ asset('img/contacto2.png') }}">
    </div>
  </section>
</main>
@endsection

@section('js-vistaContacto')
  <script src="{{ asset('js/contacto.js') }}"></script>
@endsection
