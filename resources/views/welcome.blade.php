@extends('layouts.Usuarios')  

@section('Titulo','Home')

@section('css-vistaHome')
  {{-- Flatpickr CSS (necesario para que se vea el calendario) --}}
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">

  {{-- Tu CSS --}}
  <link rel="stylesheet" href="{{ asset('css/navbarUsuarios.css') }}">

  {{-- Swiper CSS para el carrusel de tarjetas --}}
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css"/>

  <style>
    :root{ --brand:#b22222; --ink:#0f172a }

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

    /* ===== Banner Reservas (limpio, sin rayas) ===== */
    .rv-banner-wrap{
      position:fixed; top:10px; left:50%; transform:translateX(-50%);
      z-index: 9999; width:min(1100px,95%);
      pointer-events:none; /* no bloquea clics del hero */
    }
    .rv-banner{
      display:none; pointer-events:auto;
      background:#fff; color:var(--ink);
      border-radius:16px; box-shadow:0 14px 40px rgba(0,0,0,.18);
      border:1px solid rgba(0,0,0,.08); overflow:hidden;
    }
    .rv-row{ display:flex; align-items:center; gap:12px; padding:10px 14px; }
    .rv-car{
      width:34px; height:34px; border-radius:10px;
      background:rgba(178,34,34,.10); display:grid; place-items:center;
      animation:rv-car-move 3s.ease-in-out infinite;
    }
    .rv-car svg{ color:var(--brand) }
    @keyframes rv-car-move{ 0%,100%{ transform:translateX(0) } 50%{ transform:translateX(6px) } }

    .rv-live{ font-weight:600; color:var(--ink); font-size:13px; }
    .rv-live::before{
      content:""; display:inline-block; width:8px; height:8px; border-radius:999px; margin-right:6px;
      background:#10b981; box-shadow:0 0 0 0 rgba(16,185,129,.5); animation:rv-live 1.5s ease-out infinite;
    }
    @keyframes rv-live{
      0%{ box-shadow:0 0 0 0 rgba(16,185,129,.5) }
      70%{ box-shadow:0 0 0 10px rgba(16,185,129,0) }
      100%{ box-shadow:0 0 0 0 rgba(16,185,129,0) }
    }

    .rv-text{ font-size:13px; color:#3f3f46 }
    .rv-count{ font-weight:700; color:var(--ink) }

    .rv-cta{
      background:var(--brand); color:#fff; border:0; border-radius:10px;
      padding:8px 12px; font-size:13px; font-weight:600; cursor:pointer;
      box-shadow:0 8px 20px rgba(178,34,34,.25);
      transition:opacity .2s.ease;
    }
    .rv-cta:hover{ opacity:.95 }

    .rv-close{
      border:0; background:transparent; color:#9ca3af; font-size:18px; cursor:pointer;
    }
    .rv-close:hover{ color:#374151 }

    /* Barra de progreso */
    .rv-bar{ height:4px; width:100%; background:#f1f5f9 }
    .rv-bar i{
      display:block; height:100%; width:0%;
      background:linear-gradient(90deg,#b22222,#ef4444,#fb923c,#b22222);
      background-size:300% 100%; animation:rv-bar-move 1.6s linear infinite;
    }
    @keyframes rv-bar-move{ from{background-position:0% 50%} to{background-position:200% 50%} }

    /* Entradas / salidas */
    .rv-in{ animation:rv-drop .35s cubic-bezier(.2,.7,.2,1) forwards }
    .rv-out{ animation:rv-lift .28s ease forwards }
    @keyframes rv-drop{ from{ opacity:0; transform:translateY(-10px) scale(.98) } to{ opacity:1; transform:translateY(0) scale(1) } }
    @keyframes rv-lift{ from{ opacity:1; transform:translateY(0) scale(1) } to{ opacity:0; transform:translateY(-10px) scale(.98) } }

    @media (max-width:560px){
      .rv-text{ font-size:12px }
      .rv-cta{ display:none } /* CTA se oculta en móvil para no saturar */
    }

    /* ====== Swiper de TARJETAS (tiles) ====== */
    .vj-tiles-swiper{ padding: 6px 10px 42px; width:min(1200px,94%); margin:28px auto; }
    .vj-tiles-swiper .swiper-slide{ height:auto }

    .tile-card{
      background:#fff; border-radius:18px; overflow:hidden;
      box-shadow:0 18px 40px rgba(0,0,0,.18);
      display:flex; flex-direction:column; height:100%;
    }
    .tile-card .tile-media{
      width:100%; height:230px; background-size:cover; background-position:center; background-repeat:no-repeat;
    }
    .tile-card .tile-body{ padding:18px 20px 22px; display:flex; flex-direction:column; gap:10px; flex:1 }
    .tile-card h3{ margin:0; font-size:1.1rem; color:var(--brand); letter-spacing:.2px }
    .tile-card p{ margin:0; color:var(--ink); opacity:.85; line-height:1.45 }
    .tile-card .tile-link{ margin-top:auto; font-weight:600; color:var(--brand); text-decoration:none }
    .tile-card .tile-link:hover{ text-decoration:underline }

    .vj-tiles-swiper .swiper-button-prev,
    .vj-tiles-swiper .swiper-button-next{
      width:42px; height:42px; border-radius:50%;
      background:#fff; box-shadow:0 10px 26px rgba(0,0,0,.18);
    }
    .vj-tiles-swiper .swiper-button-prev:after,
    .vj-tiles-swiper .swiper-button-next:after{
      font-size:18px; color:var(--brand);
    }
    .vj-tiles-swiper .swiper-pagination-bullet{
      opacity:.35; background:var(--brand)
    }
    .vj-tiles-swiper .swiper-pagination-bullet-active{
      opacity:1; transform:scale(1.15)
    }

    /* ==== CARD DE RESEÑAS GOOGLE ==== */
    .tile-card.tile-reviews .tile-body{
      gap: 12px;
    }

    .reviews-summary{
      display:flex;
      align-items:baseline;
      gap:6px;
      font-size:0.95rem;
      font-weight:600;
      color:var(--ink);
    }

    .reviews-score{
      font-size:1.1rem;
      font-weight:800;
      color:#f59e0b;
    }

    .reviews-count{
      font-size:0.85rem;
      color:var(--muted);
    }

    .reviews-list{
      display:flex;
      flex-direction:column;
      gap:8px;
      max-height:180px;
      overflow-y:auto;
      padding-right:4px;
    }

    .review-item{
      background:#f9fafb;
      border-radius:10px;
      padding:8px 10px;
      box-shadow:0 4px 10px rgba(0,0,0,.04);
    }

    .review-head{
      display:flex;
      justify-content:space-between;
      align-items:center;
      font-size:0.85rem;
      margin-bottom:4px;
    }

    .review-head strong{
      font-weight:700;
      color:#111827;
    }

    .review-stars{
      font-size:0.8rem;
      color:#f59e0b;
    }

    .review-text{
      margin:0;
      font-size:0.85rem;
      color:#374151;
      line-height:1.4;
    }
  </style>
@endsection

@section('contenidoHome')

@php
    use Illuminate\Support\Str;
@endphp

<!-- ===== VISTA INICIO ===== -->
<section class="v-inicio" data-title="Inicio">

  <!-- ===== Banner Reservas (arriba, centrado) ===== -->
  <div class="rv-banner-wrap" id="rvWrap" aria-live="polite">
    <div class="rv-banner" id="rvBanner" role="status" aria-label="Reservas en vivo">
      <div class="rv-bar"><i id="rvBar"></i></div>

      <div class="rv-row">
        <div class="rv-car" aria-hidden="true">
          <svg width="18" height="18" viewBox="0 0 24 24">
            <path fill="currentColor" d="M5 11l1-3.2A2 2 0 0 1 7.9 6h8.2a2 2 0 0 1 1.9 1.8L20 11v5a1 1 0 0 1-1 1h-1a1.5 1.5 0 0 1 0-3h1v-1H5v1h1a1.5 1.5 0 1 1 0 3H5a1 1 0 0 1-1-1v-5Zm3.2-3a.8.8 0 0 0-.77.6L6.9 10h10.2l-.53-1.4a.8.8 0 0 0-.77-.6H8.2Z"/>
          </svg>
        </div>

        <div class="rv-copy" style="flex:1 1 auto">
          <div class="rv-live">Reservas en vivo</div>
          <div class="rv-text">
            Ahora mismo <span class="rv-count" id="rvCount">5</span> personas están reservando autos.
          </div>
        </div>

        <button class="rv-cta" onclick="location.href='{{ route('rutaReservaciones') }}'">Ver disponibilidad</button>
        <button class="rv-close" id="rvClose" aria-label="Cerrar">✕</button>
      </div>
    </div>
  </div>
  <!-- ===== /Banner Reservas ===== -->

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
    

    <!-- ===== NUEVO LAYOUT DEL CUESTIONARIO ===== -->
    <div class="search-card">
      <form id="rentalForm" class="search-form" method="POST" action="{{ route('rutaBuscar') }}">
        @csrf

        <div class="search-grid">

          {{-- COLUMNA: LUGAR DE RENTA (PICK-UP / DROP-OFF) --}}
          <div class="sg-col sg-col-location">

            {{-- Título + checkbox en la MISMA fila --}}
            <div class="location-head">
              <span class="field-title">Lugar de renta</span>

              <label class="inline-check" for="differentDropoff">
                <input type="checkbox" id="differentDropoff" checked>
                <span>Devolver en otro destino</span>
              </label>
            </div>

            {{-- Pick-up --}}
            <div class="field icon-field">
              <span class="field-icon"><i class="fa-solid fa-location-dot"></i></span>
              <select id="pickupPlace" name="pickup_sucursal_id" aria-describedby="pickupHelp" required>
                <option value="" disabled selected>¿Dónde inicia tu viaje? (Pick-up)</option>
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
            </div>

            {{-- Drop-off --}}
            <div class="field icon-field" id="dropoffWrapper">
              <span class="field-icon"><i class="fa-solid fa-location-dot"></i></span>
              <select id="dropoffPlace" name="dropoff_sucursal_id" aria-describedby="dropoffHelp" required>
                <option value="" disabled selected>¿Dónde termina tu viaje? (Drop-off)</option>
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
            </div>
          </div>

          {{-- COLUMNA: ENTREGA --}}
          <div class="sg-col sg-col-datetime">
            <div class="field">
              <label>Entrega</label>
              <div class="datetime-row">
                <div class="dt-field icon-field">
                  <span class="field-icon"><i class="fa-regular fa-calendar-days"></i></span>
                  <input id="pickupDate"
                         name="pickup_date"
                         type="text"
                         placeholder="12/Sep/2024"
                         value="{{ request('pickup_date') }}"
                         data-min="{{ now()->toDateString() }}"
                         required>
                </div>
                <div class="dt-field icon-field">
                  <span class="field-icon"><i class="fa-regular fa-clock"></i></span>
                  <input id="pickupTime"
                         name="pickup_time"
                         type="text"
                         placeholder="06:00 PM"
                         value="{{ request('pickup_time') }}"
                         required>
                </div>
              </div>
            </div>
          </div>

          {{-- COLUMNA: DEVOLUCIÓN --}}
          <div class="sg-col sg-col-datetime">
            <div class="field">
              <label>Devolución</label>
              <div class="datetime-row">
                <div class="dt-field icon-field">
                  <span class="field-icon"><i class="fa-regular fa-calendar-days"></i></span>
                  <input id="dropoffDate"
                         name="dropoff_date"
                         type="text"
                         placeholder="12/Sep/2024"
                         value="{{ request('dropoff_date') }}"
                         data-min="{{ now()->toDateString() }}"
                         required>
                </div>
                <div class="dt-field icon-field">
                  <span class="field-icon"><i class="fa-regular fa-clock"></i></span>
                  <input id="dropoffTime"
                         name="dropoff_time"
                         type="text"
                         placeholder="06:00 PM"
                         value="{{ request('dropoff_time') }}"
                         required>
                </div>
              </div>
            </div>
          </div>

          {{-- COLUMNA: BOTÓN BUSCAR --}}
          <div class="sg-col sg-col-submit">
            <div class="actions">
              <button type="submit">
                <i class="fa-solid fa-magnifying-glass"></i> BUSCAR
              </button>
            </div>
          </div>

        </div> {{-- /search-grid --}}

        <div id="rangeSummary" class="range-summary" aria-live="polite"></div>
      </form>
    </div>
    <!-- ===== /NUEVO LAYOUT DEL CUESTIONARIO ===== -->

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
            <li><i class="fa-solid.fa-user-large"></i> 5</li>
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
            <p>Kia Seltos o similar | I</p>
          </header>
          <div class="car-media">
            <img src="{{ asset('img/seltos.png') }}" alt="Kia Seltos o similar">
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

  <!-- TARJETAS (Swiper) -->
  <section aria-label="Explora destinos y servicios">
    <div class="swiper vj-tiles-swiper">
      <div class="swiper-wrapper">

        {{-- ==== 3 TARJETAS ORIGINALES ==== --}}
        <div class="swiper-slide">
          <article class="tile-card">
            <div class="tile-media" style="background-image:url('{{ asset('img/24.jpg') }}')"></div>
            <div class="tile-body">
              <h3>Activos 24/7:</h3>
              <p>Atención y soporte en todo momento. Nuestro equipo está disponible las 24 horas, los 7 días de la semana, para que viajes con total tranquilidad.</p>
              <a href="#" class="tile-link">Leer más…</a>
            </div>
          </article>
        </div>

        <div class="swiper-slide">
          <article class="tile-card">
            <div class="tile-media" style="background-image:url('{{ asset('img/4x4.png') }}')"></div>
            <div class="tile-body">
              <h3>AUTOS Y CAMIONETAS 4x4:</h3>
              <p>Viaja sin límites. Contamos con SUVs, autos todoterreno y camionetas 4x4 ideales para carretera, ciudad o aventura.</p>
              <a href="#" class="tile-link">Explora nuestra flota...</a>
            </div>
          </article>
        </div>

        <!-- ✅ Corregido: esta card ahora usa background-image -->
        <div class="swiper-slide">
          <article class="tile-card">
            <div class="tile-media" style="background-image:url('{{ asset('img/Urvancard.png') }}')"></div>
            <div class="tile-body">
              <h3>CAMIONETAS PARA 13 PASAJEROS:</h3>
              <p>Perfectas para viajes familiares o empresariales. Comodidad, espacio y seguridad para todos tus acompañantes.</p>
              <a href="#" class="tile-link">Reserva la tuya...</a>
            </div>
          </article>
        </div>

        {{-- ==== 6 NUEVAS TARJETAS ==== --}}
        <div class="swiper-slide">
          <article class="tile-card">
            <div class="tile-media" style="background-image:url('{{ asset('img/tarjeta.png') }}')"></div>
            <div class="tile-body">
              <h3>ACEPTAMOS TARJETAS:</h3>
              <p>Pagos con tarjeta de crédito o débito. Fácil, rápido y seguro. También puedes hacer tu pago final al devolver tu vehículo.</p>
              <a href="#" class="tile-link">Conoce nuestras opciones...</a>
            </div>
          </article>
        </div>

        <div class="swiper-slide">
          <article class="tile-card">
            <div class="tile-media" style="background-image:url('{{ asset('img/aeropuerto.png') }}')"></div>
            <div class="tile-body">
              <h3>ENTREGA EN AEROPUERTO 24/7:</h3>
              <p>Recibe o entrega tu auto directamente en el aeropuerto, sin filas ni esperas. Disponible las 24 horas del día.</p>
              <a href="#" class="tile-link">Agendar entrega...</a>
            </div>
          </article>
        </div>

        <div class="swiper-slide">
          <article class="tile-card">
            <div class="tile-media" style="background-image:url('{{ asset('img/Verificacion.png') }}')"></div>
            <div class="tile-body">
              <h3>VEHÍCULOS CON VERIFICACIÓN 00:</h3>
              <p>Todos nuestros autos cumplen con las normas ambientales y están verificados tipo 00 para garantizar su óptimo rendimiento.</p>
              <a href="#" class="tile-link">Descubre más...</a>
            </div>
          </article>
        </div>

        <div class="swiper-slide">
          <article class="tile-card">
            <div class="tile-media" style="background-image:url('{{ asset('img/Drop.png') }}')"></div>
            <div class="tile-body">
              <h3>DROP OFF NACIONAL:</h3>
              <p>Disfruta de tu viaje sin preocupaciones. Devuelve tu auto en otra ciudad con nuestro servicio Drop Off Nacional (con costo adicional).</p>
              <a href="#" class="tile-link">Consultar destinos...</a>
            </div>
          </article>
        </div>

        <div class="swiper-slide">
          <article class="tile-card">
            <div class="tile-media" style="background-image:url('{{ asset('img/nuevos.png') }}')"></div>
            <div class="tile-body">
              <h3>AUTOS NUEVOS Y MODERNOS:</h3>
              <p>Conduce con estilo y seguridad. Nuestra flota está compuesta por vehículos recientes, siempre en óptimas condiciones.</p>
              <a href="#" class="tile-link">Explora la flota...</a>
            </div>
          </article>
        </div>

        {{-- 🔹 TARJETA DE RESEÑAS GOOGLE (última slide, SIEMPRE esta card) --}}
        <div class="swiper-slide">
          <article class="tile-card tile-reviews">
            <div class="tile-media" style="background-image:url('{{ asset('img/Prioridad.png') }}')"></div>

            <div class="tile-body">
              <h3>RESEÑAS DE GOOGLE MAPS:</h3>

              @if(!empty($googleRating))
                <div class="reviews-summary">
                  <span class="reviews-score">⭐ {{ number_format($googleRating, 1) }}</span>
                  @if(!empty($googleTotal))
                    <span class="reviews-count">({{ $googleTotal }} opiniones)</span>
                  @endif
                </div>
              @endif

              <div class="reviews-list">
                @if(isset($googleReviews) && $googleReviews->isNotEmpty())
                  @foreach($googleReviews as $review)
                    <div class="review-item">
                      <div class="review-head">
                        <strong>{{ $review['author_name'] ?? 'Usuario de Google' }}</strong>
                        @if(!empty($review['rating']))
                          <span class="review-stars">
                            @for($i = 0; $i < (int)$review['rating']; $i++)
                              ★
                            @endfor
                          </span>
                        @endif
                      </div>
                      <p class="review-text">
                        {{ Str::limit($review['text'] ?? '', 120) }}
                      </p>
                    </div>
                  @endforeach
                @else
                  {{-- Mensaje cuando aún no hay reseñas o falló la API --}}
                  <div class="review-item">
                    <p class="review-text">
                      Pronto verás aquí las opiniones de nuestros clientes en Google Maps.
                    </p>
                  </div>
                @endif
              </div>

              <a href="https://www.google.com/maps/place/VIAJERO+CAR+RENTAL+Centro+Sur"
                 target="_blank"
                 rel="noopener"
                 class="tile-link">
                Ver más reseñas en Google…
              </a>
            </div>
          </article>
        </div>

      </div>

      {{-- Controles --}}
      <div class="swiper-button-prev"></div>
      <div class="swiper-button-next"></div>
      <div class="swiper-pagination"></div>
    </div>
  </section>
  <!-- /TARJETAS (Swiper) -->

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

@endsection

@section('js-vistaHome')
  {{-- Flatpickr core + locale ES + rangePlugin (¡en este orden!) --}}
  <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
  <script src="https://cdn.jsdelivr.net/npm/flatpickr/dist/l10n/es.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/flatpickr/dist/plugins/rangePlugin.js"></script>

  {{-- Tu JS --}}
  <script src="{{ asset('js/home.js') }}"></script>

  {{-- Swiper JS para el carrusel de tarjetas --}}
  <script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>

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

  <!-- ===== Inicialización Swiper de TARJETAS ===== -->
  <script>
    const tilesSwiper = new Swiper('.vj-tiles-swiper', {
      loop: true,
      speed: 650,
      autoplay: {
        delay: 3200,
        disableOnInteraction: false
      },
      spaceBetween: 18,
      slidesPerView: 1.06,
      centeredSlides: false,
      grabCursor: true,
      navigation: {
        nextEl: '.vj-tiles-swiper .swiper-button-next',
        prevEl: '.vj-tiles-swiper .swiper-button-prev',
      },
      pagination: {
        el: '.vj-tiles-swiper .swiper-pagination',
        clickable: true
      },
      breakpoints: {
        560:  { slidesPerView: 1.4, spaceBetween: 18 },
        768:  { slidesPerView: 2,   spaceBetween: 20 },
        1024: { slidesPerView: 3,   spaceBetween: 22 },
        1280: { slidesPerView: 3.3, spaceBetween: 24 }
      }
    });
  </script>

  <!-- ===== Toast de reservas (simple y limpio) ===== -->
  <script>
  (function(){
    // Números por aparición (puedes ajustar)
    const SEQ = [5,7,10,5,12];

    const SHOW_MS = 7000;  // tiempo visible
    const HIDE_MS = 25000;  // tiempo oculto entre apariciones

    const banner = document.getElementById('rvBanner');
    const bar    = document.getElementById('rvBar');
    const count  = document.getElementById('rvCount');
    const close  = document.getElementById('rvClose');

    let idx = 0, loop = true, hideT = null, nextT = null;
    let paused = false, startTs = 0, remaining = SHOW_MS;

    function setBar(ms){
      bar.style.transition = 'none'; bar.style.width = '0%';
      requestAnimationFrame(()=>{ requestAnimationFrame(()=>{
        bar.style.transition = `width ${ms}ms linear`;
        bar.style.width = '100%';
      });});
    }

    function showOnce(){
      count.textContent = SEQ[idx]; idx = (idx + 1) % SEQ.length;
      banner.style.display = 'block';
      banner.classList.remove('rv-out'); banner.classList.add('rv-in');

      remaining = SHOW_MS; startTs = performance.now();
      setBar(SHOW_MS);

      hideT = setTimeout(hide, SHOW_MS);
    }

    function hide(){
      banner.classList.remove('rv-in'); banner.classList.add('rv-out');
      setTimeout(()=>{
        banner.style.display = 'none';
        if(loop){ nextT = setTimeout(showOnce, HIDE_MS); }
      }, 260);
    }

    // Pausar al pasar el mouse (congela la barra)
    banner.addEventListener('mouseenter', ()=>{
      paused = true;
      const elapsed = performance.now() - startTs;
      remaining = Math.max(0, SHOW_MS - elapsed);
      if(hideT){ clearTimeout(hideT); hideT = null; }
      bar.style.transition = 'none';
    });

    banner.addEventListener('mouseleave', ()=>{
      if(!paused) return; paused = false;
      // reanudar
      setTimeout(()=>{
        setBar(remaining);
        hideT = setTimeout(hide, remaining);
        startTs = performance.now() - (SHOW_MS - remaining);
      }, 30);
    });

    close.addEventListener('click', ()=>{
      loop = false;
      if(hideT) clearTimeout(hideT);
      if(nextT) clearTimeout(nextT);
      banner.style.display = 'none';
    });

    document.addEventListener('DOMContentLoaded', showOnce);
  })();
  </script>
  <!-- ===== /Toast de reservas ===== -->
@endsection
