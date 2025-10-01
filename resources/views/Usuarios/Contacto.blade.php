@extends('layouts.Usuarios')

@section('Titulo','Contacto')

@section('css-vistaContacto')
    <link rel="stylesheet" href="{{ asset('css/contacto.css') }}">
@endsection

@section('contenidoContacto')
     <main class="page">
    <section class="hero" aria-label="cabecera">
      <div class="hero-bg" role="img" aria-label="Entrega de llaves">
        <img src="https://images.unsplash.com/photo-1483721310020-03333e577078?q=80&w=1600&auto=format&fit=crop" alt="">
      </div>
      <div class="hero-overlay"></div>
      <div class="hero-content">
        <h1>¡Hablemos! <span>Contáctanos</span></h1>
        <p>Estamos listos para ayudarte con tu próxima renta</p>
      </div>

      <div class="hero-chips" aria-label="sucursales">
        <span class="chip"><i class="fa-solid fa-location-dot"></i> Oficina Central Park, Querétaro</span>
        <span class="chip"><i class="fa-solid fa-location-dot"></i> Pick-up Aeropuerto de Querétaro</span>
        <span class="chip"><i class="fa-solid fa-location-dot"></i> Pick-up Aeropuerto de León</span>
      </div>
    </section>

    <section class="maps" aria-label="mapas de ubicación">
      <div class="map-card">
        <div class="map-head"><i class="fa-solid fa-map-location-dot"></i> Central Park, Querétaro</div>
        <div class="map-body">
          <iframe
            src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3732.320875076114!2d-100.40373!3d20.58863!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x85d35a232c0f6d8b%3A0x93a4f4d0b05c7b0!2sCentral%20Park%20Quer%C3%A9taro!5e0!3m2!1ses-419!2smx!4v1713800000000"
            loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>
        </div>
      </div>

      <div class="map-card">
        <div class="map-head"><i class="fa-solid fa-plane-departure"></i> Aeropuerto Intercontinental de Qro.</div>
        <div class="map-body">
          <iframe
            src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3719.870311799566!2d-100.15367!3d20.60267!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x85d355d8f7b9f2a1%3A0x9a66c7b4d3e49a26!2sAeropuerto%20Intercontinental%20de%20Quer%C3%A9taro!5e0!3m2!1ses-419!2smx!4v1713800001000"
            loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>
        </div>
      </div>

      <div class="map-card">
        <div class="map-head"><i class="fa-solid fa-plane-arrival"></i> Aeropuerto del Bajío (León)</div>
        <div class="map-body">
          <iframe
            src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3604.232674145455!2d-101.47641!3d21.24142!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x842bbf0cd5a8df2f%3A0x9a2c53a2ea0aab3!2sAeropuerto%20Internacional%20del%20Baj%C3%ADo!5e0!3m2!1ses-419!2smx!4v1713800002000"
            loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>
        </div>
      </div>
    </section>

    <section class="contact-grid" aria-label="formulario de contacto">
      <div class="contact-left">
        <div class="form-card">
          <h2>Contáctanos</h2>
          <form id="contactForm" class="contact-form" novalidate>
            <input type="text" name="company" tabindex="-1" autocomplete="off" style="position:absolute;left:-10000px;opacity:0;height:0;width:0" aria-hidden="true">

            <div class="row">
              <div class="field">
                <label for="fName">Nombre completo</label>
                <input id="fName" name="name" required placeholder="Tu nombre y apellidos" autocomplete="name">
              </div>
            </div>
            <div class="row grid-2">
              <div class="field">
                <label for="fPhone">Móvil</label>
                <input id="fPhone" name="phone" type="tel" required placeholder="55 1234 5678" autocomplete="tel" pattern="^[0-9 +()-]{8,}$">
                <span class="hint">Incluye lada. Ej.: 442 123 4567</span>
              </div>
              <div class="field">
                <label for="fEmail">Correo electrónico</label>
                <input id="fEmail" name="email" type="email" required placeholder="tucorreo@dominio.com" autocomplete="email">
              </div>
            </div>
            <div class="row">
              <div class="field">
                <label for="fSubject">Asunto</label>
                <input id="fSubject" name="subject" placeholder="¿En qué podemos ayudarte?">
              </div>
            </div>
            <div class="row">
              <div class="field">
                <label for="fMessage">Mensaje</label>
                <textarea id="fMessage" name="message" rows="5" required maxlength="800" placeholder="Escribe tu mensaje aquí…"></textarea>
                <span class="hint"><span id="charCount">0</span>/800</span>
              </div>
            </div>

            <label class="cbox">
              <input type="checkbox" id="promo">
              <span class="checkmark"></span>
              <span>Deseo recibir alertas, confirmaciones y promociones por correo o teléfono.</span>
            </label>

            <div class="form-actions">
              <button type="button" class="btn btn-ghost" id="btnWhatsapp">
                <i class="fa-brands fa-whatsapp"></i> WhatsApp
              </button>
              <button type="submit" class="btn btn-primary">
                Enviar mensaje
              </button>
            </div>
          </form>
        </div>
        <div class="disclaimer">
          Al enviar este formulario aceptas nuestro <a href="politicas.html">aviso de privacidad</a> y nuestras <a href="politicas.html">políticas de uso</a>.
        </div>
      </div>

      <div class="contact-right">
        <div class="card-cta" aria-label="soporte">
          <div class="cta-icon"><i class="fa-solid fa-headset"></i></div>
          <h3>Soporte y atención</h3>
          <p>Nuestro equipo está disponible para resolver tus dudas, cotizar y ayudarte a reservar.</p>
          <div class="cta-actions">
            <a class="btn btn-secondary" href="tel:+524421234567"><i class="fa-solid fa-phone"></i> Llamar ahora</a>
            <a class="btn btn-primary" id="ctaWhats"><i class="fa-brands fa-whatsapp"></i> WhatsApp</a>
          </div>
          <div class="cta-hours"><i class="fa-regular fa-clock"></i> Lun–Dom · 8:00 a 22:00 h</div>
        </div>
        <img class="illus" alt="Soporte Viajero" src="https://images.unsplash.com/photo-1525182008055-f88b95ff7980?q=80&w=1200&auto=format&fit=crop">
      </div>
    </section>
  </main>

@section('js-vistaContacto')
    <script src="{{ asset('js/contacto.js') }}"></script>
@endsection
@endsection
