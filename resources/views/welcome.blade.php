@extends('layouts.Usuarios')  

@section('Titulo','Home')

@section('css-vistaHome')
  {{-- Flatpickr CSS (necesario para que se vea el calendario) --}}
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">

  {{-- Tu CSS --}}
  <link rel="stylesheet" href="{{ asset('css/navbarUsuarios.css') }}">

  <style>
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

    /* ===== Texto debajo de cada carrusel ===== */
    .fleet-meta{
      width:min(1200px,94%);
      margin:10px auto 24px;
      display:flex;
      align-items:center;
      justify-content:center;
      gap:14px;
      font-size:clamp(12px, 1.8vw, 15px);
      color:var(--ink);
      letter-spacing:.2px;
      line-height:1.3;
    }
    .fleet-meta .sep{ opacity:.5; font-style:normal; }
    @media (max-width:560px){ .fleet-meta{ gap:10px } }

    /* ===== Layout para los iconos del hero (5 items responsivos) ===== */
    .hero-icons{
      display:flex;
      flex-wrap:wrap;
      gap:28px;
      justify-content:center;
    }
    .hero-icons .icon-item{
      display:flex;
      align-items:center;
      gap:12px;
      min-width:240px; /* ayuda a que el wrap sea parejo */
    }
    @media (min-width:900px){
      .hero-icons{
        display:grid;
        grid-template-columns: repeat(3, minmax(220px,1fr));
        gap:28px;
      }
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
      <h2 class="kicker">RENTA TU AUTO CON VIAJERO</h2>

      <div class="hero-icons">
        <div class="icon-item">
          <i class="fa-regular fa-clock"></i>
          <span>Activos las 24 horas y los 7 días de la semana</span>
        </div>

        <div class="icon-item">
          <i class="fa-regular fa-credit-card"></i>
          <span>Aceptamos tarjetas de débito y crédito</span>
        </div>
      
        <div class="icon-item">
          <i class="fa-solid fa-shield-halved"></i>
          <span>Contamos con verificación 00</span>
        </div>

        <!-- NUEVO: Atención en aeropuerto 24/7 -->
        <div class="icon-item">
          <i class="fa-solid fa-plane-departure"></i>
          <span>Atención en aeropuerto 24/7</span>
        </div>

        <!-- NUEVO: Autos con modelos recientes -->
        <div class="icon-item">
          <i class="fa-solid fa-car-side"></i>
          <span>Autos con modelos recientes</span>
        </div>
      </div>
    

    <!-- Form flotante -->
    <div class="search-card">
      <form id="rentalForm" class="search-form" method="POST" action="{{ route('rutaBuscar') }}">
        @csrf

        {{-- LUGAR DE RENTA --}}
        <div class="field col-12">
          <label for="pickupPlace">Lugar de renta</label>
          <select id="pickupPlace" name="pickup_sucursal_id" aria-describedby="pickupHelp" required>
            <option value="" disabled selected>-- Selecciona sucursal --</option>
            @foreach($ciudades as $ciudad)
              <optgroup label="{{ $ciudad->nombre }}{{ $ciudad->estado ? ' — '.$ciudad->estado : '' }}">
                @foreach($ciudad->sucursalesActivas as $suc)
                  <option value="{{ $suc->id_sucursal }}" @selected(request('pickup_sucursal_id') == $suc->id_sucursal)>
                    {{ $suc->nombre }}
                  </option>
                @endforeach
              </optgroup>
            @endforeach
          </select>
          <div id="pickupHelp" class="small">Elige la sucursal donde inicias tu renta.</div>
        </div>

        {{-- LUGAR DE DEVOLUCIÓN --}}
        <div class="field col-12">
          <label for="dropoffPlace">Lugar de devolución</label>
          <select id="dropoffPlace" name="dropoff_sucursal_id" aria-describedby="dropoffHelp" required>
            <option value="" disabled selected>-- Selecciona sucursal --</option>
            @foreach($ciudades as $ciudad)
              <optgroup label="{{ $ciudad->nombre }}{{ $ciudad->estado ? ' — '.$ciudad->estado : '' }}">
                @foreach($ciudad->sucursalesActivas as $suc)
                  <option value="{{ $suc->id_sucursal }}" @selected(request('dropoff_sucursal_id') == $suc->id_sucursal)>
                    {{ $suc->nombre }}
                  </option>
                @endforeach
              </optgroup>
            @endforeach
          </select>
          <div id="dropoffHelp" class="small">Elige la sucursal donde terminará tu renta.</div>
        </div>

        {{-- ENTREGA: FECHA + HORA --}}
        <div class="field">
          <label>Entrega</label>
          <div class="datetime-row">
            <input id="pickupDate"
                   name="pickup_date"
                   type="text"
                   placeholder="Selecciona fecha"
                   value="{{ request('pickup_date') }}"
                   data-min="{{ now()->toDateString() }}"
                   required>
            <input id="pickupTime"
                   name="pickup_time"
                   type="text"
                   placeholder="Hora"
                   value="{{ request('pickup_time') }}"
                   required>
          </div>
        </div>

        {{-- DEVOLUCIÓN: FECHA + HORA --}}
        <div class="field">
          <label>Devolución</label>
          <div class="datetime-row">
            <input id="dropoffDate"
                   name="dropoff_date"
                   type="text"
                   placeholder="Selecciona fecha"
                   value="{{ request('dropoff_date') }}"
                   data-min="{{ now()->toDateString() }}"
                   required>
            <input id="dropoffTime"
                   name="dropoff_time"
                   type="text"
                   placeholder="Hora"
                   value="{{ request('dropoff_time') }}"
                   required>
          </div>
        </div>

        {{-- CATEGORÍA (opcional) --}}
        <div class="field">
          <label for="carType">Tipo de auto</label>
          <select id="carType" name="categoria_id">
            <option value="">-- Cualquiera --</option>
            @foreach($categorias as $cat)
              <option value="{{ $cat->id_categoria }}" @selected(request('categoria_id') == $cat->id_categoria)>
                {{ $cat->nombre }}
              </option>
            @endforeach
          </select>
        </div>

        <div class="actions">
          <button type="submit"><i class="fa-solid fa-magnifying-glass"></i> BUSCAR</button>
        </div>
      </form>

      <div id="rangeSummary" class="range-summary" aria-live="polite"></div>
    </div>

  </section>

  <section id="fleet-carousel" class="fleet">
    <div class="fleet-viewport" id="fleetViewport">
      <button class="fleet-btn prev" aria-label="Anterior"><i class="fa-solid fa-chevron-left"></i></button>

      <div class="fleet-track">

        <article class="car-card">
          <header class="car-title">
            <h3>COMPACTO</h3>
            <p>Chevrolet Aveo o similar | C</p>
          </header>
          <div class="car-media">
            <img src="{{ asset('img/aveo.png') }}" alt="Chevrolet Aveo o similar">
          </div>

          <div class="offer">
            <span class="offer-badge" aria-label="Oferta">-48%</span>
            <div class="price-line">
              <span class="price-now">$467</span><span class="per">/día</span>
              <span class="price-old">$899</span>
            </div>
          </div>

          <ul class="car-specs">
            <li><i class="fa-solid fa-user-large"></i> 5</li>
            <li><i class="fa-solid fa-suitcase-rolling"></i> 2</li>
            <li><i class="fa-solid fa-briefcase"></i> 1</li>
          </ul>

          {{-- Conectividad: CarPlay + Android Auto --}}
          <div class="car-connect">
            <span class="badge-chip badge-apple" title="Apple CarPlay">
              <svg viewBox="0 0 24 24" aria-hidden="true"><rect x="2" y="2" width="20" height="20" rx="5"></rect><polygon points="10,8 16,12 10,16"></polygon></svg>
              CarPlay
            </span>
            <span class="badge-chip badge-android" title="Android Auto">
              <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 3 L20 19 H16.8 L12 10.2 L7.2 19 H4 L12 3 Z"></path></svg>
              Android Auto
            </span>
          </div>

          <a href="{{ route('rutaReservaciones') }}" class="car-cta">Reservar</a>
        </article>

        <article class="car-card">
          <header class="car-title">
            <h3>INTERMEDIO</h3>
            <p>Volkswagen Virtus o similar | D</p>
          </header>
          <div class="car-media">
            <img src="{{ asset('img/virtus.png') }}" alt="Volkswagen Virtus o similar">
          </div>

          <div class="offer">
            <span class="offer-badge">-43%</span>
            <div class="price-line">
              <span class="price-now">$600</span><span class="per">/día</span>
              <span class="price-old">$1,049</span>
            </div>
          </div>

          <ul class="car-specs">
            <li><i class="fa-solid fa-user-large"></i> 5</li>
            <li><i class="fa-solid fa-suitcase-rolling"></i> 2</li>
            <li><i class="fa-solid fa-briefcase"></i> 2</li>
          </ul>

          <div class="car-connect">
            <span class="badge-chip badge-apple" title="Apple CarPlay">
              <svg viewBox="0 0 24 24"><rect x="2" y="2" width="20" height="20" rx="5"></rect><polygon points="10,8 16,12 10,16"></polygon></svg>
              CarPlay
            </span>
            <span class="badge-chip badge-android" title="Android Auto">
              <svg viewBox="0 0 24 24"><path d="M12 3 L20 19 H16.8 L12 10.2 L7.2 19 H4 L12 3 Z"></path></svg>
              Android Auto
            </span>
          </div>

          <a href="{{ route('rutaReservaciones') }}" class="car-cta">Reservar</a>
        </article>

        <article class="car-card">
          <header class="car-title">
            <h3>GRANDE</h3>
            <p>Volkswagen Jetta o similar | E</p>
          </header>
          <div class="car-media">
            <img src="{{ asset('img/jetta.png') }}" alt="Volkswagen Jetta o similar">
          </div>

          <div class="offer">
            <span class="offer-badge">-33%</span>
            <div class="price-line">
              <span class="price-now">$800</span><span class="per">/día</span>
              <span class="price-old">$1,199</span>
            </div>
          </div>

          <ul class="car-specs">
            <li><i class="fa-solid fa-user-large"></i> 5</li>
            <li><i class="fa-solid fa-suitcase-rolling"></i> 3</li>
            <li><i class="fa-solid fa-briefcase"></i> 3</li>
          </ul>

          <div class="car-connect">
            <span class="badge-chip badge-apple" title="Apple CarPlay">
              <svg viewBox="0 0 24 24"><rect x="2" y="2" width="20" height="20" rx="5"></rect><polygon points="10,8 16,12 10,16"></polygon></svg>
              CarPlay
            </span>
            <span class="badge-chip badge-android" title="Android Auto">
              <svg viewBox="0 0 24 24"><path d="M12 3 L20 19 H16.8 L12 10.2 L7.2 19 H4 L12 3 Z"></path></svg>
              Android Auto
            </span>
          </div>

          <a href="{{ route('rutaReservaciones') }}" class="car-cta">Reservar</a>
        </article>

        <article class="car-card">
          <header class="car-title">
            <h3>FULL SIZE</h3>
            <p>Toyota Camry o similar | F</p>
          </header>
          <div class="car-media">
            <img src="{{ asset('img/camry.png') }}" alt="Toyota Camry  o similar">
          </div>

          <div class="offer">
            <span class="offer-badge">-22%</span>
            <div class="price-line">
              <span class="price-now">$1,550</span><span class="per">/día</span>
              <span class="price-old">$1,999</span>
            </div>
          </div>

          <ul class="car-specs">
            <li><i class="fa-solid fa-user-large"></i> 5</li>
            <li><i class="fa-solid fa-suitcase-rolling"></i> 3</li>
            <li><i class="fa-solid fa-briefcase"></i> 3</li>
          </ul>

          <div class="car-connect">
            <span class="badge-chip badge-apple" title="Apple CarPlay">
              <svg viewBox="0 0 24 24"><rect x="2" y="2" width="20" height="20" rx="5"></rect><polygon points="10,8 16,12 10,16"></polygon></svg>
              CarPlay
            </span>
            <span class="badge-chip badge-android" title="Android Auto">
              <svg viewBox="0 0 24 24"><path d="M12 3 L20 19 H16.8 L12 10.2 L7.2 19 H4 L12 3 Z"></path></svg>
              Android Auto
            </span>
          </div>

          <a href="{{ route('rutaReservaciones') }}" class="car-cta">Reservar</a>
        </article>

        <article class="car-card">
          <header class="car-title">
            <h3>SUV COMPACTA</h3>
            <p> Jeep Renegade o similar | IC</p>
          </header>
          <div class="car-media">
            <img src="{{ asset('img/renegade.png') }}" alt=" Jeep Renegade o similar">
          </div>

          <div class="offer">
            <span class="offer-badge">-24%</span>
            <div class="price-line">
              <span class="price-now">$1,600</span><span class="per">/día</span>
              <span class="price-old">$2,100</span>
            </div>
          </div>

          <ul class="car-specs">
            <li><i class="fa-solid fa-user-large"></i> 5</li>
            <li><i class="fa-solid fa-suitcase-rolling"></i> 2</li>
            <li><i class="fa-solid fa-briefcase"></i> 3</li>
          </ul>

          <div class="car-connect">
            <span class="badge-chip badge-apple" title="Apple CarPlay">
              <svg viewBox="0 0 24 24"><rect x="2" y="2" width="20" height="20" rx="5"></rect><polygon points="10,8 16,12 10,16"></polygon></svg>
              CarPlay
            </span>
            <span class="badge-chip badge-android" title="Android Auto">
              <svg viewBox="0 0 24 24"><path d="M12 3 L20 19 H16.8 L12 10.2 L7.2 19 H4 L12 3 Z"></path></svg>
              Android Auto
            </span>
          </div>

          <a href="{{ route('rutaReservaciones') }}" class="car-cta">Reservar</a>
        </article>

        <article class="car-card">
          <header class="car-title">
            <h3>SUV MEDIANA</h3>
            <p>Chevrolet captiva o similar | I</p>
          </header>
          <div class="car-media">
            <img src="{{ asset('img/captiva.png') }}" alt="Chevrolet captiva o similar">
          </div>

          <div class="offer">
            <span class="offer-badge">-25%</span>
            <div class="price-line">
              <span class="price-now">$1,800</span><span class="per">/día</span>
              <span class="price-old">$2,400</span>
            </div>
          </div>

          <ul class="car-specs">
            <li><i class="fa-solid fa-user-large"></i> 5</li>
            <li><i class="fa-solid fa-suitcase-rolling"></i> 2</li>
            <li><i class="fa-solid fa-briefcase"></i> 3</li>
          </ul>

          <div class="car-connect">
            <span class="badge-chip badge-apple" title="Apple CarPlay">
              <svg viewBox="0 0 24 24"><rect x="2" y="2" width="20" height="20" rx="5"></rect><polygon points="10,8 16,12 10,16"></polygon></svg>
              CarPlay
            </span>
            <span class="badge-chip badge-android" title="Android Auto">
              <svg viewBox="0 0 24 24"><path d="M12 3 L20 19 H16.8 L12 10.2 L7.2 19 H4 L12 3 Z"></path></svg>
              Android Auto
            </span>
          </div>

          <a href="{{ route('rutaReservaciones') }}" class="car-cta">Reservar</a>
        </article>

      </div>

      <button class="fleet-btn next" aria-label="Siguiente"><i class="fa-solid fa-chevron-right"></i></button>
    </div>
  </section>

  <!-- Texto debajo del CARRUSEL 1 -->
  <div class="fleet-meta" aria-label="Beneficios">
    <span>KM ilimitados</span>
    <i class="sep" aria-hidden="true">|</i>
    <span>Transmisión Automática</span>
  </div>

  <!-- ===== /CARRUSEL DE AUTOS ===== -->

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
          <a href="{{ route('auth.login') }}" class="btn btn-primary"><i class="fa-solid fa-id-card"></i> Obtén tu membresía</a>
        </div>
      </div>
    </div>

    {{-- ===== NUEVO CARRUSEL DE AUTOS BAJO "Viajero frecuente" ===== --}}
    <section id="fleet-carousel-2" class="fleet">
      <div class="fleet-viewport">
        <button class="fleet-btn prev" aria-label="Anterior"><i class="fa-solid fa-chevron-left"></i></button>

        <div class="fleet-track">
          <article class="car-card">
            <header class="car-title">
              <h3>SUV FAMILIAR COMPACTA</h3>
              <p>Toyota avanza o similar | IB</p>
            </header>
            <div class="car-media">
              <img src="{{ asset('img/avanza.png') }}" alt="Toyota avanza o similar">
            </div>

            <div class="offer">
              <span class="offer-badge">-23%</span>
              <div class="price-line">
                <span class="price-now">$1,700</span><span class="per">/día</span>
                <span class="price-old">$2,200</span>
              </div>
            </div>

            <ul class="car-specs">
              <li><i class="fa-solid fa-user-large"></i> 7</li>
              <li><i class="fa-solid fa-suitcase-rolling"></i> 2</li>
              <li><i class="fa-solid fa-briefcase"></i> 2</li>
            </ul>

            <div class="car-connect">
              <span class="badge-chip badge-apple" title="Apple CarPlay">
                <svg viewBox="0 0 24 24"><rect x="2" y="2" width="20" height="20" rx="5"></rect><polygon points="10,8 16,12 10,16"></polygon></svg>
                CarPlay
              </span>
              <span class="badge-chip badge-android" title="Android Auto">
                <svg viewBox="0 0 24 24"><path d="M12 3 L20 19 H16.8 L12 10.2 L7.2 19 H4 L12 3 Z"></path></svg>
                Android Auto
              </span>
            </div>

            <a href="{{ route('rutaReservaciones') }}" class="car-cta">Reservar</a>
          </article>

          <article class="car-card">
            <header class="car-title">
              <h3>MINIVAN</h3>
              <p> Honda Odyssey o similar | M</p>
            </header>
            <div class="car-media">
              <img src="{{ asset('img/Odyssey.png') }}" alt=" Honda Odyssey o similar">
            </div>

            <div class="offer">
              <span class="offer-badge">-13%</span>
              <div class="price-line">
                <span class="price-now">$2,600</span><span class="per">/día</span>
                <span class="price-old">$3,000</span>
              </div>
            </div>

            <ul class="car-specs">
              <li><i class="fa-solid fa-user-large"></i> 8</li>
              <li><i class="fa-solid fa-suitcase-rolling"></i> 8</li>
              <li><i class="fa-solid fa-briefcase"></i>4</li>
            </ul>

            <div class="car-connect">
              <span class="badge-chip badge-apple" title="Apple CarPlay">
                <svg viewBox="0 0 24 24"><rect x="2" y="2" width="20" height="20" rx="5"></rect><polygon points="10,8 16,12 10,16"></polygon></svg>
                CarPlay
              </span>
              <span class="badge-chip badge-android" title="Android Auto">
                <svg viewBox="0 0 24 24"><path d="M12 3 L20 19 H16.8 L12 10.2 L7.2 19 H4 L12 3 Z"></path></svg>
                Android Auto
              </span>
            </div>

            <a href="{{ route('rutaReservaciones') }}" class="car-cta">Reservar</a>
          </article>

          <article class="car-card">
            <header class="car-title">
              <h3>VAN FAMILIAR</h3>
              <p>Nissan Urvan o similar | L | TM </p>
            </header>
            <div class="car-media">
              <img src="{{ asset('img/Urvan.png') }}" alt="Nissan Urvan o similar">
            </div>

            <div class="offer">
              <span class="offer-badge">-15%</span>
              <div class="price-line">
                <span class="price-now">$2,900</span><span class="per">/día</span>
                <span class="price-old">$3,400</span>
              </div>
            </div>

            <ul class="car-specs">
              <li><i class="fa-solid fa-user-large"></i> 13</li>
              <li><i class="fa-solid fa-suitcase-rolling"></i> 5</li>
              <li><i class="fa-solid fa-briefcase"></i> 5</li>
            </ul>

            <div class="car-connect">
              <span class="badge-chip badge-apple" title="Apple CarPlay">
                <svg viewBox="0 0 24 24"><rect x="2" y="2" width="20" height="20" rx="5"></rect><polygon points="10,8 16,12 10,16"></polygon></svg>
                CarPlay
              </span>
              <span class="badge-chip badge-android" title="Android Auto">
                <svg viewBox="0 0 24 24"><path d="M12 3 L20 19 H16.8 L12 10.2 L7.2 19 H4 L12 3 Z"></path></svg>
                Android Auto
              </span>
            </div>

            <a href="{{ route('rutaReservaciones') }}" class="car-cta">Reservar</a>
          </article>

          <article class="car-card">
            <header class="car-title">
              <h3>VAN</h3>
              <p>Toyota Hiace o similar | L</p>
            </header>
            <div class="car-media">
              <img src="{{ asset('img/Hiace.png') }}" alt="Toyota Hiace o similar">
            </div>

            <div class="offer">
              <span class="offer-badge">-15%</span>
              <div class="price-line">
                <span class="price-now">$2,900</span><span class="per">/día</span>
                <span class="price-old">$9,400</span>
              </div>
            </div>

            <ul class="car-specs">
              <li><i class="fa-solid fa-user-large"></i> 5</li>
              <li><i class="fa-solid fa-suitcase-rolling"></i> 3</li>
              <li><i class="fa-solid fa-briefcase"></i> 3</li>
            </ul>

            <div class="car-connect">
              <span class="badge-chip badge-apple" title="Apple CarPlay">
                <svg viewBox="0 0 24 24"><rect x="2" y="2" width="20" height="20" rx="5"></rect><polygon points="10,8 16,12 10,16"></polygon></svg>
                CarPlay
              </span>
              <span class="badge-chip badge-android" title="Android Auto">
                <svg viewBox="0 0 24 24"><path d="M12 3 L20 19 H16.8 L12 10.2 L7.2 19 H4 L12 3 Z"></path></svg>
                Android Auto
              </span>
            </div>

            <a href="{{ route('rutaReservaciones') }}" class="car-cta">Reservar</a>
          </article>

          <article class="car-card">
            <header class="car-title">
              <h3>PICK UP DOBLE CABINA</h3>
              <p> Nissan Frontier o similar | H</p>
            </header>
            <div class="car-media">
              <img src="{{ asset('img/Frontier.png') }}" alt="Nissan Frontier similar">
            </div>

            <div class="offer">
              <span class="offer-badge">-24%</span>
              <div class="price-line">
                <span class="price-now">$1,950</span><span class="per">/día</span>
                <span class="price-old">$2,550</span>
              </div>
            </div>

            <ul class="car-specs">
              <li><i class="fa-solid fa-user-large"></i> 5</li>
              <li><i class="fa-solid fa-suitcase-rolling"></i> 3</li>
              <li><i class="fa-solid fa-briefcase"></i> 3</li>
            </ul>

            <div class="car-connect">
              <span class="badge-chip badge-apple" title="Apple CarPlay">
                <svg viewBox="0 0 24 24"><rect x="2" y="2" width="20" height="20" rx="5"></rect><polygon points="10,8 16,12 10,16"></polygon></svg>
                CarPlay
              </span>
              <span class="badge-chip badge-android" title="Android Auto">
                <svg viewBox="0 0 24 24"><path d="M12 3 L20 19 H16.8 L12 10.2 L7.2 19 H4 L12 3 Z"></path></svg>
                Android Auto
              </span>
            </div>

            <a href="{{ route('rutaReservaciones') }}" class="car-cta">Reservar</a>
          </article>

          <article class="car-card">
            <header class="car-title">
              <h3>PICK UP 4X4 DOBLE CABINA</h3>
              <p>Toyota Tacoma o similar | HI</p>
            </header>
            <div class="car-media">
              <img src="{{ asset('img/Tacoma.png') }}" alt="Toyota Tacoma o similar">
            </div>

            <div class="offer">
              <span class="offer-badge">-10%</span>
              <div class="price-line">
                <span class="price-now">$2,600</span><span class="per">/día</span>
                <span class="price-old">$2,900</span>
              </div>
            </div>

            <ul class="car-specs">
              <li><i class="fa-solid fa-user-large"></i> 5</li>
              <li><i class="fa-solid fa-suitcase-rolling"></i> 3</li>
              <li><i class="fa-solid fa-briefcase"></i> 3</li>
            </ul>

            <div class="car-connect">
              <span class="badge-chip badge-apple" title="Apple CarPlay">
                <svg viewBox="0 0 24 24"><rect x="2" y="2" width="20" height="20" rx="5"></rect><polygon points="10,8 16,12 10,16"></polygon></svg>
                CarPlay
              </span>
              <span class="badge-chip badge-android" title="Android Auto">
                <svg viewBox="0 0 24 24"><path d="M12 3 L20 19 H16.8 L12 10.2 L7.2 19 H4 L12 3 Z"></path></svg>
                Android Auto
              </span>
            </div>

            <a href="{{ route('rutaReservaciones') }}" class="car-cta">Reservar</a>
          </article>
        </div>

        <button class="fleet-btn next" aria-label="Siguiente"><i class="fa-solid fa-chevron-right"></i></button>
      </div>
    </section>

    <!-- Texto debajo del CARRUSEL 2 -->
    <div class="fleet-meta" aria-label="Beneficios">
      <span>KM ilimitados</span>
      <i class="sep" aria-hidden="true">|</i>
      <span>Transmisión Automática</span>
    </div>

    {{-- ===== /NUEVO CARRUSEL ===== --}}

    <div class="info-row reverse">
      <div class="info-content">
        <h2>Soluciones empresariales</h2>
        <p>Gestionamos tus viajes corporativos de punta a punta para que tu equipo se concentre en lo importante.</p>
        <p>Optimiza costos, confort y seguridad con nuestros planes para empresas.</p>
        <div class="cta-group">
          <a href="{{ route('rutaReservaciones') }}" class="btn btn-primary"><i class="fa-regular fa-calendar-check"></i> Reserva ahora</a>
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

@section('js-vistaHome')
  {{-- Flatpickr core + locale ES + rangePlugin (¡en este orden!) --}}
  <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
  <script src="https://cdn.jsdelivr.net/npm/flatpickr/dist/l10n/es.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/flatpickr/dist/plugins/rangePlugin.js"></script>

  {{-- Tu JS --}}
  <script src="{{ asset('js/home.js') }}"></script>

  {{-- Carruseles de flota (loop infinito, flechas bidireccionales, autoslide 10s) --}}
  <script>
  (function(){
    const fleets = document.querySelectorAll('.fleet');
    if(!fleets.length) return;

    fleets.forEach(fleet => {
      const track = fleet.querySelector('.fleet-track');
      const prev  = fleet.querySelector('.fleet-btn.prev');
      const next  = fleet.querySelector('.fleet-btn.next');
      if(!track || !prev || !next) return;

      let autoSlide;
      const stepCount = 3; 

      const step = () => {
        const card = track.querySelector('.car-card');
        return card ? (card.offsetWidth + 18) : 340; 
      };

      const toStart = () => track.scrollTo({ left: 0, behavior: 'smooth' });
      const toEnd   = () => track.scrollTo({ left: track.scrollWidth, behavior: 'smooth' });

      const nextSlide = () => {
        if (track.scrollLeft + track.offsetWidth >= track.scrollWidth - step()) {
          toStart();
        } else {
          track.scrollBy({ left: step() * stepCount, behavior: 'smooth' });
        }
      };

      const prevSlide = () => {
        if (track.scrollLeft <= 0) {
          toEnd();
        } else {
          track.scrollBy({ left: -step() * stepCount, behavior: 'smooth' });
        }
      };

      next.addEventListener('click', nextSlide);
      prev.addEventListener('click', prevSlide);

      function startAuto(){
        stopAuto();
        autoSlide = setInterval(nextSlide, 10000); 
      }
      function stopAuto(){
        if(autoSlide) clearInterval(autoSlide);
      }

      track.addEventListener('mouseenter', stopAuto);
      track.addEventListener('mouseleave', startAuto);

      startAuto();
    });
  })();
  </script>

  <!-- ===== Aviso animado "carrito" (entra 5s cada 2min) ===== -->
  <script>
  (function(){
    const MENSAJE_FIJO = "HAY 5 PERSONAS MÁS RESERVANDO";
    const MOSTRAR_MS   = 5000;    // visible 5s
    const INTERVALO_MS = 120000;  // cada 2 minutos
    let mostrando = false;

    function crearNodo(msg){
      const el = document.createElement('div');
      el.className = 'alert-reservas';
      el.setAttribute('role','status');
      el.setAttribute('aria-live','polite');
      el.innerHTML = `
        <div class="row">
          <!-- Ícono de auto (inline SVG, libre) -->
          <svg class="icon-car" viewBox="0 0 24 24" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
            <path d="M14 16H9m10 0h3v-3.15a1 1 0 00-.84-.99L16 11l-2.7-3.6a1 1 0 00-.8-.4H5.24a2 2 0 00-1.8 1.1l-.8 1.63A6 6 0 002 12.42V16h2"/>
            <circle cx="6.5" cy="16.5" r="2.5"/>
            <circle cx="16.5" cy="16.5" r="2.5"/>
          </svg>
          <span class="badge">En vivo</span>
          <span class="msg">` + msg + `</span>
        </div>
      `;
      document.body.appendChild(el);
      return el;
    }

    function mostrarAlerta(msg = MENSAJE_FIJO){
      if (mostrando) return;
      mostrando = true;

      const el = crearNodo(msg);
      // forzar siguiente frame para transiciones CSS
      requestAnimationFrame(()=> el.classList.add('in'));

      setTimeout(()=>{
        el.classList.remove('in');
        setTimeout(()=>{
          el.remove();
          mostrando = false;
        }, 500); // coincide con transición CSS
      }, MOSTRAR_MS);
    }

    document.addEventListener('DOMContentLoaded', () => {
      mostrarAlerta();                       // una de inmediato
      setInterval(mostrarAlerta, INTERVALO_MS); // cada 2 min
    });

    // Exponer si quieres dispararla manualmente
    window.mostrarAlertaReservas = mostrarAlerta;
  })();
  </script>
  <!-- ===== /Aviso animado ===== -->
@endsection

@endsection
