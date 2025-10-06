@extends('layouts.Usuarios')

@section('Titulo','Home')

@section('css-vistaHome')
  <link rel="stylesheet" href="{{ asset('css/navbarUsuarios.css') }}">
  <style>
    /* Imagen bajo el texto de 3 MSI */
    .hero-badge-3msi{
      width: clamp(180px, 38vw, 520px);
      max-width: 100%;
      height: auto;
      margin-top: 10px;
      display: block;
      filter: drop-shadow(0 8px 24px rgba(0,0,0,.30));
      pointer-events: none;
    }
    @media (max-width:560px){
      .hero-badge-3msi{ width: clamp(180px, 60vw, 360px); }
    }
  </style>
@endsection

@section('contenidoHome')

<!-- ===== VISTA INICIO ===== -->
<section class="v-inicio" data-title="Inicio">
  <!-- HERO -->
  <section class="hero">
    <div class="carousel">
      <div class="slide active" style="background-image:url('{{ asset('img/inicio1.png') }}');"></div>
      <div class="slide" style="background-image:url('{{ asset('img/inicio2.png') }}');"></div>
      <div class="slide" style="background-image:url('{{ asset('img/inicio3.png') }}');"></div>
      <div class="overlay"></div>
    </div>

    <div class="hero-copy">
      <p class="kicker">RENTA TU AUTO CON VIAJERO</p>
      <h1 class="headline">3 MESES SIN</h1>
      <h1 class="headline">INTERESES</h1>

      <!-- NUEVA IMAGEN BAJO EL TEXTO -->
      <img
        class="hero-badge-3msi"
        src="{{ asset('img/pago.png') }}"
        alt="Promoción 3 meses sin intereses">
    </div>

    <!-- Form flotante -->
    <div class="search-card">
      <!-- form controlado por JS (validación de fechas) -->
      <form id="rentalForm" class="search-form">
        <div class="field col-12">
          <label for="pickupPlace">Lugar de renta</label>
          <select id="pickupPlace" aria-describedby="pickupHelp">
            <!-- opciones pobladas por JS -->
          </select>
          <div id="pickupHelp" class="small">Elige la ciudad donde inicias tu renta.</div>
        </div>

        <div class="field col-12">
          <label for="dropoffPlace">Lugar de devolución</label>
          <select id="dropoffPlace" aria-describedby="dropoffHelp">
            <!-- opciones pobladas por JS -->
          </select>
          <div id="dropoffHelp" class="small">Elige la ciudad donde terminará tu renta.</div>
        </div>

        <!-- Entrega: fecha + hora -->
        <div class="field">
          <label>Entrega</label>
          <div class="datetime-row">
            <input id="pickupDate" type="text" aria-label="Fecha de entrega" placeholder="Selecciona fecha">
            <input id="pickupTime" type="text" aria-label="Hora de entrega" placeholder="Hora">
          </div>
        </div>

        <!-- Devolución: fecha + hora -->
        <div class="field">
          <label>Devolución</label>
          <div class="datetime-row">
            <input id="dropoffDate" type="text" aria-label="Fecha de devolución" placeholder="Selecciona fecha">
            <input id="dropoffTime" type="text" aria-label="Hora de devolución" placeholder="Hora">
          </div>
        </div>

        <div class="field">
          <label>Tipo de auto</label>
          <select id="carType">
            <option>Económico</option>
            <option>Sedán</option>
            <option>SUV</option>
            <option>Pickup</option>
            <option>Mini-Van</option>
            <option>Lujo</option>
            <option>Deportivo</option>
          </select>
        </div>

        <div class="actions">
          <button type="submit"><i class="fa-solid fa-magnifying-glass"></i> BUSCAR</button>
        </div>
      </form>
      <div id="rangeSummary" class="range-summary" aria-live="polite"></div>
    </div>
  </section>

  <!-- SECCIONES -->
  <section class="info-sections">
    <div class="info-row">
      <div class="info-media media-carousel" data-interval="4500">
        <div class="media-slide active" style="background-image:url('{{ asset('img/inicio4.png') }}');"></div>
        <div class="media-slide" style="background-image:url('{{ asset('img/inicio5.png') }}');"></div>
        <div class="media-slide" style="background-image:url('{{ asset('img/inicio6.png') }}');"></div>
      </div>
      <div class="info-content">
        <h2>¿Viajero frecuente?</h2>
        <p>Al convertirte en miembro desbloqueas descuentos exclusivos en todas tus reservas para que cada viaje sea más accesible.</p>
        <p>Acumula puntos por cada reserva y canjea por más descuentos, upgrades o experiencias especiales.</p>
        <p>Activa hoy tu membresía y disfruta recompensas exclusivas.</p>
        <div class="cta-group">
          <a href="{{ route('rutaLogin') }}" class="btn btn-primary"><i class="fa-solid fa-id-card"></i> Obtén tu membresía</a>
        </div>
      </div>
    </div>

    <div class="info-row reverse">
      <div class="info-content">
        <h2>Soluciones empresariales</h2>
        <p>Gestionamos tus viajes corporativos de punta a punta para que tu equipo se concentre en lo importante.</p>
        <p>Optimiza costos, confort y seguridad con nuestros planes para empresas.</p>
        <div class="cta-group">
          <a href="{{ route('rutaReservaciones') }}" class="btn btn-primary"><i class="fa-regular fa-calendar-check"></i> Reserva ahora</a>
          <a href="#" class="btn btn-whatsapp"><i class="fa-brands fa-whatsapp"></i> WhatsApp</a>
        </div>
      </div>
      <div class="info-media media-carousel" data-interval="5200">
        <div class="media-slide active" style="background-image:url('{{ asset('img/inicio7.png') }}');"></div>
        <div class="media-slide" style="background-image:url('{{ asset('img/inicio8.png') }}');"></div>
        <div class="media-slide" style="background-image:url('{{ asset('img/inicio9.png') }}');"></div>
      </div>
    </div>
  </section>

  <!-- TARJETAS -->
  <section class="tiles">
    <div class="tiles-wrap">
      <article class="tile">
        <div class="tile-media" style="background-image:url('{{ asset('img/arcos.jpg') }}');"></div>
        <div class="tile-body">
          <h3>DESCUBRE QUERÉTARO:</h3>
          <p>Rutas escénicas y pueblos mágicos para disfrutar a tu ritmo. ¡Explóralo en auto!</p>
          <a href="#" class="tile-link">Leer más…</a>
        </div>
      </article>
      <article class="tile">
        <div class="tile-media" style="background-image:url('{{ asset('img/24.jpg') }}');"></div>
        <div class="tile-body">
          <h3>SERVICIO 24/7:</h3>
          <p>Asistencia en carretera y soporte siempre disponibles. Tu viaje nunca se detiene.</p>
          <a href="#" class="tile-link">Leer más…</a>
        </div>
      </article>
      <article class="tile">
        <div class="tile-media" style="background-image:url('{{ asset('img/leon.jpeg') }}');"></div>
        <div class="tile-body">
          <h3>DESCUBRE LEÓN:</h3>
          <p>Moda, negocios y gastronomía con la libertad de moverte en tu auto.</p>
          <a href="#" class="tile-link">Leer más…</a>
        </div>
      </article>
    </div>
  </section>

  <!-- CTA FINAL -->
  <section class="cta-hero">
    <div class="cta-bg" style="background-image:url('{{ asset('img/inicio10.png') }}');"></div>
    <div class="cta-overlay"></div>
    <div class="cta-inner">
      <h2>¡RENTA HOY, EXPLORA MAÑANA, VIAJA SIEMPRE!</h2>
      <a href="{{ route('rutaReservaciones') }}" class="btn btn-primary btn-lg">
        <i class="fa-regular fa-calendar-check"></i> ¡Reserva ahora!
      </a>
    </div>
  </section>
</section>

<!-- Barra social lateral -->
<div class="social">
  <a href="#" class="whatsapp" aria-label="WhatsApp"><i class="fa-brands fa-whatsapp"></i></a>
  <a href="#" class="facebook" aria-label="Facebook"><i class="fa-brands fa-facebook-f"></i></a>
  <a href="#" class="instagram" aria-label="Instagram"><i class="fa-brands fa-instagram"></i></a>
  <a href="#" class="tiktok" aria-label="TikTok"><i class="fa-brands fa-tiktok"></i></a>
</div>

<!-- Modal de Bienvenida -->
<div class="modal" id="welcomeModal" aria-hidden="true">
  <div class="modal-backdrop"></div>
  <div class="modal-card" role="dialog" aria-modal="true" aria-labelledby="wmTitle">
    <button class="modal-close" id="wmClose" aria-label="Cerrar"><i class="fa-regular fa-circle-xmark"></i></button>
    <h3 id="wmTitle"><i class="fa-regular fa-hand-peace"></i> ¡Bienvenido, <span id="wmName">Viajero</span>!</h3>
    <p>Tu cuenta está lista. ¿Quieres ir directo a tu reserva?</p>
    <div class="modal-actions">
      <a href="{{ route('rutaReservaciones') }}" class="btn btn-primary"><i class="fa-regular fa-calendar-check"></i> Ir a mi reserva</a>
      <button class="btn btn-ghost" id="wmOk">Seguir en inicio</button>
    </div>
  </div>
</div>

{{-- ===== JS: Flatpickr y lógica ===== --}}
@section('js-vistaHome')
  <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
  <script src="{{ asset('js/home.js') }}"></script>
@endsection

@endsection
