<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover, maximum-scale=1, user-scalable=no">
  <meta name="csrf-token" content="{{ csrf_token() }}">

  <!-- Bootstrap / Fuentes / Iconos -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@600;800&family=Poppins:wght@400;600;700&display=swap" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/@fortawesome/fontawesome-free@6.5.2/css/all.min.css" rel="stylesheet">

  {{-- AlertifyJS CSS --}}
  <link rel="stylesheet" href="//cdn.jsdelivr.net/npm/alertifyjs@1.13.1/build/css/alertify.min.css"/>
  <link rel="stylesheet" href="//cdn.jsdelivr.net/npm/alertifyjs@1.13.1/build/css/themes/default.min.css"/>

  <link rel="icon" href="{{ asset('img/Icono.ico') }}" type="image/x-icon">
  <link rel="shortcut icon" href="{{ asset('img/Icono.ico') }}" type="image/x-icon">

  <!-- Flatpickr CSS -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/themes/material_red.css">

    <!-- Estilos propios -->
    <link rel="stylesheet" href="{{ asset('css/navbarUsuarios.css') }}">
    <!-- CSS por vista -->
    @yield('css-vistaHome')
    @yield('css-VistaCatalogo')
    @yield('css-vistaReservaciones')
    @yield('css-vistaContacto')
    @yield('css-vistaPoliticas')
    @yield('css-vistaFAQ')
    @yield('css-vistaLogin')
    @yield('css-vistaPerfil')

  <style>
    /* ==========================
       FIX FRANJA SUPERIOR (TUYO)
    ========================== */
    html, body{
      margin: 0 !important;
      padding: 0 !important;
    }
    body{ overflow-x: hidden; }
    header.topbar{ margin-top: 0 !important; }

    .brand-logo-img{height:30px; display:block}
    .footer-logo{height:42px; display:block; margin:0 auto}
    .brand a.brand-link{display:inline-flex; align-items:center; text-decoration:none}

    /* ===== ICONO PERSONA ===== */
    .nav-actions{display:flex; align-items:center; gap:12px;}
    .nav-actions .icon-pill{
      display:flex; align-items:center; justify-content:center;
      width:42px; height:42px; border-radius:999px;
      background: rgba(255,255,255,.15);
      transition: all .3s ease;
      text-decoration:none;
    }
    .nav-actions .icon-pill:hover{
      background: rgba(255,255,255,.35);
      transform: scale(1.05);
    }
    .nav-actions .icon-pill i{ font-size:18px; color:#fff; }
    .nav-actions .icon-pill i.guest{ opacity:.85; }

    /* ===== HAMBURGER ===== */
    .hamburger{
      width:40px; height:40px;
      border-radius:14px;
      border:1px solid rgba(255,255,255,.25);
      background: rgba(255,255,255,.12);
      display:none;
      align-items:center;
      justify-content:center;
      cursor:pointer;
      transition: transform .18s ease, background .18s ease;
    }
    .hamburger:hover{ transform: translateY(-1px); background: rgba(255,255,255,.20); }
    .hamburger .hb{
      width:18px; height:2px; background:#fff; border-radius:99px; position:relative;
    }
    .hamburger .hb::before,
    .hamburger .hb::after{
      content:""; position:absolute; left:0;
      width:18px; height:2px; background:#fff; border-radius:99px;
    }
    .hamburger .hb::before{ top:-6px; }
    .hamburger .hb::after{ top: 6px; }

    /* Backdrop */
    .nav-backdrop{
      position:fixed; inset:0;
      background: rgba(0,0,0,.45);
      opacity:0; pointer-events:none;
      transition: opacity .2s ease;
      z-index: 9998;
    }
    body.nav-open .nav-backdrop{
      opacity:1; pointer-events:auto;
    }

    /* =================================================
       ✅ ÚNICO AGREGADO: TRANSICIÓN GLASS → SOLID
       (NO cambia estilos, solo anima el cambio)
    ================================================= */
    header.topbar{
      transition:
        background .35s ease,
        box-shadow .35s ease,
        backdrop-filter .35s ease,
        -webkit-backdrop-filter .35s ease;
    }
    /* anti auto-zoom iOS al enfocar (flatpickr incluido) */
    .search-card input,
    .search-card select,
    .search-card textarea{
      font-size:16px !important;
    }

  </style>
</head>

<body>

<header class="topbar glass">
  <nav class="nav">
    <div class="brand">
      <a href="{{ route('rutaHome') }}" class="brand-link" aria-label="Viajero">
        <img src="{{ asset('img/LogoB.png') }}" alt="Viajero" class="brand-logo-img">
      </a>
    </div>

    <ul class="menu" id="mainMenu">
      <li><a href="{{ route('rutaHome') }}" class="{{ request()->routeIs('rutaHome') ? 'active' : '' }}">Inicio</a></li>
      <li><a href="{{ route('rutaCatalogo') }}" class="{{ request()->routeIs('rutaCatalogo') ? 'active' : '' }}">Catálogo de autos</a></li>
      <li><a href="{{ route('rutaReservaciones') }}" class="{{ request()->routeIs('rutaReservaciones') ? 'active' : '' }}">Reservaciones</a></li>
      <li><a href="{{ route('rutaContacto') }}" class="{{ request()->routeIs('rutaContacto') ? 'active' : '' }}">Contacto</a></li>
      <li><a href="{{ route('rutaPoliticas') }}" class="{{ request()->routeIs('rutaPoliticas') ? 'active' : '' }}">Políticas</a></li>
      <li><a href="{{ route('rutaFAQ') }}" class="{{ request()->routeIs('rutaFAQ') ? 'active' : '' }}">F.A.Q</a></li>
    </ul>

    <div class="nav-actions">

      @if (session()->has('id_usuario'))
        <div class="dropdown">
          <a href="#" class="icon-pill dropdown-toggle" data-bs-toggle="dropdown">
            <i class="fa-solid fa-user"></i>
          </a>
          <ul class="dropdown-menu dropdown-menu-end shadow">
            <li>
              <a class="dropdown-item" href="{{ route('rutaPerfil') }}">Perfil</a>
            </li>
            <li><hr class="dropdown-divider"></li>
            <li>
              <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="dropdown-item">Cerrar sesión</button>
              </form>
            </li>
          </ul>
        </div>
      @else
        <a href="{{ route('auth.show') }}" class="icon-pill" title="Iniciar sesión">
          <i class="fa-regular fa-user guest"></i>
        </a>
      @endif

      <button class="hamburger" type="button" id="navHamburger" aria-label="Abrir menú">
        <span class="hb"></span>
      </button>
    </div>
  </nav>
</header>


<div class="containerVS">
  @yield('contenidoHome')
  @yield('contenidoCatalogo')
  @yield('contenidoReservaciones')
  @yield('contenidoContacto')
  @yield('contenidoPoliticas')
  @yield('contenidoFAQ')
  @yield('contenidoLogin')
  @yield('contenidoPerfil')

</div>

<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<script src="//cdn.jsdelivr.net/npm/alertifyjs@1.13.1/build/alertify.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

<script>
(function(){
  "use strict";

  const topbar   = document.querySelector(".topbar");
  const btn      = document.getElementById("navHamburger");
  const menu     = document.getElementById("mainMenu");


  if(!topbar || !btn || !menu) return;

  const MQ = window.matchMedia("(max-width: 940px)");
  const isMobile = ()=> MQ.matches;

  function openNav(){
    if(!isMobile()) return;
    document.body.classList.add("nav-open");
    btn.setAttribute("aria-expanded","true");
  }

  function closeNav(){
    document.body.classList.remove("nav-open");
    btn.setAttribute("aria-expanded","false");
  }

  btn.addEventListener("click", ()=>{
    document.body.classList.contains("nav-open") ? closeNav() : openNav();
  });


  menu.addEventListener("click", (e)=>{
    if(e.target.closest("a")) closeNav();
  });

  document.addEventListener("keydown", (e)=>{
    if(e.key === "Escape") closeNav();
  });

  if(MQ.addEventListener){
    MQ.addEventListener("change", ()=>{ if(!isMobile()) closeNav(); });
  }

  /* =================================================
     ✅ ÚNICO AGREGADO: GLASS ↔ SOLID POR SCROLL
  ================================================= */
  const SOLID_AT = 20;

  function syncTopbar(){
    if(window.scrollY > SOLID_AT){
      topbar.classList.add("solid");
      topbar.classList.remove("glass");
    }else{
      topbar.classList.add("glass");
      topbar.classList.remove("solid");
    }
  }

  syncTopbar();
  window.addEventListener("scroll", syncTopbar, { passive:true });

})();
</script>

<!-- FOOTER -->
<footer class="site-footer">
  <div class="footer-bg" style="background-image:url('https://images.unsplash.com/photo-1465447142348-e9952c393450?q=80&w=1600&auto=format&fit=crop');"></div>
  <div class="footer-overlay"></div>
  <div class="footer-content">
    <div class="footer-brandmark">
      <img src="{{ asset('img/LogoB.png') }}" alt="Viajero" class="footer-logo">
    </div>

    <div class="footer-row loc-row">
      <div class="loc-card">
        <div class="pin"><i class="fa-solid fa-location-dot"></i></div>
        <div class="loc-body"><h4>Plaza Central Park, Querétaro Centro</h4><p>Oficina principal</p></div>
      </div>
      <div class="loc-card">
        <div class="pin"><i class="fa-solid fa-location-dot"></i></div>
        <div class="loc-body"><h4>Aeropuerto Internacional de Querétaro (AIQ)</h4><p>Pick-up / Drop-off</p></div>
      </div>
      <div class="loc-card">
        <div class="pin"><i class="fa-solid fa-location-dot"></i></div>
        <div class="loc-body"><h4>Central de Autobuses de Querétaro (TAQ)</h4><p>Pick-up / Drop-off</p></div>
      </div>
    </div>

    <div class="footer-row pay-row">
      <div class="payments-logos">
        <img src="{{ asset('img/visa.jpg') }}" alt="Visa" />
        <img src="{{ asset('img/mastercard.png') }}" alt="Mastercard" />
        <img src="{{ asset('img/america.png') }}" alt="American Express" />
        <img class="oxxo" src="{{ asset('img/oxxo.png') }}" alt="OXXO" />
        <img class="mp" src="{{ asset('img/pago.png') }}" alt="Mercado Pago" />
        <img src="{{ asset('img/paypal.png') }}" alt="PayPal" />
      </div>
    </div>

    <div class="footer-row links-row">
      <ul>
        <li><a href="{{ route('rutaReservaciones') }}">Reserva ahora</a></li>
        <li><a href="{{ route('rutaCatalogo') }}">Autos Disponibles</a></li>
        <li><a href="{{ route('rutaPoliticas') }}">Términos y condiciones</a></li>
        <li><a href="{{ route('rutaContacto') }}">Contacto</a></li>
      </ul>
      <ul>
        <li><a href="{{ route('rutaFAQ') }}">F.A.Q.</a></li>
        <li><a href="{{ route('rutaPoliticas') }}">Aviso de privacidad</a></li>
        <li><a href="{{ route('rutaPoliticas') }}">Política de limpieza</a></li>
        <li><a href="{{ route('rutaPoliticas') }}">Política de renta</a></li>
      </ul>
    </div>

    <div class="footer-copy">© <span id="year"></span> Viajero. Todos los derechos reservados.</div>
  </div>
</footer>
{{-- 🔹 Scripts específicos por vista --}}
@yield('js-vistaHome')
@yield('js-vistaCatalogo')
@yield('js-vistaReservaciones')
@yield('js-vistaContacto')
@yield('js-vistaPoliticas')
@yield('js-vistaFAQ')
@yield('js-vistaLogin')
@yield('js-vistaPerfil')

<script>
  // iOS: bloquear zoom por doble tap / gesto
  (function(){
    document.addEventListener('gesturestart', e => e.preventDefault(), {passive:false});
    document.addEventListener('touchmove', e => {
      if (e.touches && e.touches.length > 1) e.preventDefault();
    }, {passive:false});

    let last = 0;
    document.addEventListener('touchend', e => {
      const now = Date.now();
      if (now - last <= 300) e.preventDefault();
      last = now;
    }, {passive:false});
  })();
</script>
<script>
(function(){
  // Bloquea pinch/gesture zoom (iOS)
  document.addEventListener('gesturestart', e => e.preventDefault(), {passive:false});
  document.addEventListener('gesturechange', e => e.preventDefault(), {passive:false});
  document.addEventListener('gestureend',   e => e.preventDefault(), {passive:false});

  // Bloquea zoom con 2 dedos
  document.addEventListener('touchmove', e => {
    if (e.touches && e.touches.length > 1) e.preventDefault();
  }, {passive:false});
})();
</script>

</body>
</html>

