<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
  <meta http-equiv="X-UA-Compatible" content="ie=edge">
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

  <!-- Flatpickr CSS (una sola vez) -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/themes/material_red.css">

  <!-- Estilos propios -->
  <link rel="stylesheet" href="{{ asset('css/navbarUsuarios.css') }}">
  <link rel="stylesheet" href="{{ asset('css/navbar-top.css') }}">

  <!-- CSS por vista -->
  @yield('css-vistaHome')
  @yield('css-VistaCatalogo')
  @yield('css-vistaReservaciones')
  @yield('css-vistaContacto')
  @yield('css-vistaPoliticas')
  @yield('css-vistaFAQ')
  @yield('css-vistaLogin')
  @yield('css-vistaPerfil')

  <title>@yield('Titulo')</title>

  <style>
    .brand-logo-img{height:35px; display:block}
    .footer-logo{height:42px; display:block; margin:0 auto}
    .brand a.brand-link{display:inline-flex; align-items:center; text-decoration:none}

    /* ===== ICONO PERSONA (NAV-ACTIONS) ===== */
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

    /* ===== HAMBURGER (solo se verá en móvil por tu CSS) ===== */
    .hamburger{
      width:44px; height:44px;
      border-radius:14px;
      border:1px solid rgba(255,255,255,.25);
      background: rgba(255,255,255,.12);
      display:none; /* tu CSS lo enciende en mobile */
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

    /* Backdrop (lo activas con JS agregando/removiendo body.nav-open) */
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

    {{-- ✅ MENÚ SOLO LINKS (en móvil se oculta y se abre como panel con JS/CSS) --}}
    <ul class="menu" id="mainMenu">
      <li><a href="{{ route('rutaHome') }}" class="{{ request()->routeIs('rutaHome') ? 'active' : '' }}">Inicio</a></li>
      <li><a href="{{ route('rutaCatalogo') }}" class="{{ request()->routeIs('rutaCatalogo') ? 'active' : '' }}">Catálogo de autos</a></li>
      <li><a href="{{ route('rutaReservaciones') }}" class="{{ request()->routeIs('rutaReservaciones') ? 'active' : '' }}">Reservaciones</a></li>
      <li><a href="{{ route('rutaContacto') }}" class="{{ request()->routeIs('rutaContacto') ? 'active' : '' }}">Contacto</a></li>
      <li><a href="{{ route('rutaPoliticas') }}" class="{{ request()->routeIs('rutaPoliticas') ? 'active' : '' }}">Políticas</a></li>
      <li><a href="{{ route('rutaFAQ') }}" class="{{ request()->routeIs('rutaFAQ') ? 'active' : '' }}">F.A.Q</a></li>
    </ul>

    {{-- ✅ ACCIONES (SIEMPRE VISIBLES): icono + hamburguesa --}}
    <div class="nav-actions">

      @if (session()->has('id_usuario'))
        {{-- ✅ Usuario logueado --}}
        <div class="dropdown">
          <a href="#"
             class="icon-pill dropdown-toggle"
             title="Mi perfil"
             data-bs-toggle="dropdown"
             aria-expanded="false">
            <i class="fa-solid fa-user"></i>
          </a>

          <ul class="dropdown-menu dropdown-menu-end shadow">
            <li>
              <a class="dropdown-item" href="{{ route('rutaPerfil') }}">
                <i class="fa-regular fa-id-card me-2"></i> Perfil
              </a>
            </li>
            <li><hr class="dropdown-divider"></li>
            <li>
              <form method="POST" action="{{ route('logout') }}" style="margin:0;">
                @csrf
                <button type="submit" class="dropdown-item">
                  <i class="fa-solid fa-right-from-bracket me-2"></i> Cerrar sesión
                </button>
              </form>
            </li>
          </ul>
        </div>
      @else
        {{-- ❌ Usuario sin sesión --}}
        <a href="{{ route('auth.show') }}" class="icon-pill" title="Iniciar sesión" id="accountLink">
          <i class="fa-regular fa-user guest"></i>
        </a>
      @endif

      {{-- ✅ Hamburguesa (solo móvil por CSS) --}}
      <button class="hamburger" type="button" aria-label="Abrir menú" aria-expanded="false" id="navHamburger">
        <span class="hb" aria-hidden="true"></span>
      </button>
    </div>
  </nav>
</header>

{{-- ✅ Backdrop (para cerrar al tocar fuera) --}}
<div class="nav-backdrop" id="navBackdrop" aria-hidden="true"></div>

<!-- Contenedor de vistas -->
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

<!-- ===== Librerías necesarias para Flatpickr (ANTES de los JS por vista) ===== -->
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<script src="https://cdn.jsdelivr.net/npm/flatpickr/dist/l10n/es.js"></script>
<script src="https://cdn.jsdelivr.net/npm/flatpickr/dist/plugins/rangePlugin.js"></script>

{{-- AlertifyJS --}}
<script src="//cdn.jsdelivr.net/npm/alertifyjs@1.13.1/build/alertify.min.js"></script>

<!-- JS por vista -->
<div class="containerJS">
  @yield('js-vistaHome')
  @yield('js-vistaCatalogo')
  @yield('js-vistaReservaciones')
  @yield('js-vistaContacto')
  @yield('js-vistaPoliticas')
  @yield('js-vistaFAQ')
  @yield('js-vistaLogin')
  @yield('js-vistaPerfil')
</div>

{{-- Bootstrap JS --}}
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

{{-- ✅ JS mínimo para hamburguesa (si ya lo tienes en home.js, puedes quitarlo de aquí) --}}
<script>
(function(){
  "use strict";
  const btn = document.getElementById('navHamburger');
  const menu = document.getElementById('mainMenu');
  const backdrop = document.getElementById('navBackdrop');

  if(!btn || !menu) return;

  function openNav(){
    document.body.classList.add('nav-open');
    btn.setAttribute('aria-expanded','true');
    // mostramos menú solo en móvil (tu CSS lo estiliza como panel)
    menu.style.display = 'flex';
    menu.style.flexDirection = 'column';
    menu.style.gap = '12px';
  }
  function closeNav(){
    document.body.classList.remove('nav-open');
    btn.setAttribute('aria-expanded','false');
    // ocultar en móvil
    menu.style.display = '';
  }
  function toggleNav(){
    document.body.classList.contains('nav-open') ? closeNav() : openNav();
  }

  btn.addEventListener('click', (e)=>{ e.preventDefault(); toggleNav(); });
  backdrop && backdrop.addEventListener('click', closeNav);

  // cerrar al dar click a un link
  menu.querySelectorAll('a').forEach(a => a.addEventListener('click', closeNav));

  // cerrar con ESC
  document.addEventListener('keydown', (e)=>{
    if(e.key === 'Escape') closeNav();
  });

  // si vuelves a desktop, resetea
  window.addEventListener('resize', ()=>{
    if(window.innerWidth > 860) closeNav();
  }, { passive:true });
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
        <div class="loc-body"><h4>Central Park, Querétaro</h4><p>Oficina principal</p></div>
      </div>
      <div class="loc-card">
        <div class="pin"><i class="fa-solid fa-location-dot"></i></div>
        <div class="loc-body"><h4>Aeropuerto de Querétaro</h4><p>Pick-up / Drop-off</p></div>
      </div>
      <div class="loc-card">
        <div class="pin"><i class="fa-solid fa-location-dot"></i></div>
        <div class="loc-body"><h4>Aeropuerto de León</h4><p>Pick-up / Drop-off</p></div>
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

</body>
</html>
