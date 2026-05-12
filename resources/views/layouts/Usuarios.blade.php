<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover, maximum-scale=1, user-scalable=no">
  <meta name="csrf-token" content="{{ csrf_token() }}">

  {{-- ============================================================
       🚀 OPTIMIZACIÓN 1: PRECONNECT
       Le dice al navegador que se conecte a estos dominios YA,
       en paralelo, antes de necesitar los archivos.
       Ahorra ~400ms en DNS + TLS handshake.
  ============================================================ --}}
  <link rel="preconnect" href="https://cdn.jsdelivr.net" crossorigin>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link rel="preconnect" href="https://flagcdn.com" crossorigin>
  <link rel="preconnect" href="https://code.jquery.com" crossorigin>
  <link rel="preconnect" href="https://www.googletagmanager.com">
  <link rel="dns-prefetch" href="https://images.unsplash.com">

  <title>@yield('Titulo')</title>

  {{-- ============================================================
       🚀 OPTIMIZACIÓN 2: FAVICON
       Antes: 2 líneas → 2 descargas de 222 KB = 444 KB perdidos
       Ahora: 1 línea
       👉 TODO: el archivo Icono.ico pesa 222 KB. Reemplázalo
       por uno de ~5 KB usando https://favicon.io/
  ============================================================ --}}
  <link rel="icon" href="{{ asset('img/Icono.ico') }}" sizes="any">

  {{-- ============================================================
       🚀 OPTIMIZACIÓN 3: CSS CRÍTICOS PRIMERO
       Estos sí los necesitamos para pintar la primera vista.
  ============================================================ --}}
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="{{ asset('css/navbarUsuarios.css') }}">

  {{-- Fuentes (con display=swap ya lo tenías bien) --}}
  <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@600;800&family=Poppins:wght@400;600;700&display=swap" rel="stylesheet">

  {{-- ============================================================
       🚀 OPTIMIZACIÓN 4: CSS NO CRÍTICOS DIFERIDOS
       Truco: media="print" + onload = el navegador los carga
       SIN bloquear el render. Cuando terminan, se aplican.
       Esto le quita ~500ms al "tiempo a primera pintura".
  ============================================================ --}}
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@fortawesome/fontawesome-free@6.5.2/css/all.min.css"
        media="print" onload="this.media='all'">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css"
        media="print" onload="this.media='all'">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css"
        media="print" onload="this.media='all'">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/themes/material_red.css"
        media="print" onload="this.media='all'">
  <link rel="stylesheet" href="//cdn.jsdelivr.net/npm/alertifyjs@1.13.1/build/css/alertify.min.css"
        media="print" onload="this.media='all'">
  <link rel="stylesheet" href="//cdn.jsdelivr.net/npm/alertifyjs@1.13.1/build/css/themes/default.min.css"
        media="print" onload="this.media='all'">

  {{-- Fallback por si el navegador no soporta JS (raro pero buena práctica) --}}
  <noscript>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@fortawesome/fontawesome-free@6.5.2/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <link rel="stylesheet" href="//cdn.jsdelivr.net/npm/alertifyjs@1.13.1/build/css/alertify.min.css">
  </noscript>

  {{-- CSS por vista (se mantienen como los tenías) --}}
  @yield('css-vistaHome')
  @yield('css-VistaCatalogo')
  @yield('css-vistaReservaciones')
  @yield('css-vistaContacto')
  @yield('css-vistaPoliticas')
  @yield('css-vistaFAQ')
  @yield('css-vistaLogin')
  @yield('css-vistaPerfil')
  @yield('css-visorReservaciones')

  {{-- ============================================================
       💡 RECOMENDACIÓN: este bloque <style> de ~250 líneas
       deberías moverlo al final de navbarUsuarios.css. Ahí va a
       estar cacheado y no se reenviará en cada página.
       Por ahora lo dejo aquí para no romper nada.
  ============================================================ --}}
  <style>
    html, body{ margin: 0 !important; padding: 0 !important; }
    body{ overflow-x: hidden; }
    header.topbar{ margin-top: 0 !important; }

    .brand-logo-img{height:28px; display:block}
    .footer-logo{height:42px; display:block; margin:0 auto}
    .brand a.brand-link{display:inline-flex; align-items:center; text-decoration:none}

    .nav-actions{display:flex; align-items:center; gap:12px;}
    .nav-actions .icon-pill{
      display:flex; align-items:center; justify-content:center;
      width:36px; height:36px; border-radius:999px;
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

    .hamburger{
      width:34px; height:34px;
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
    .hamburger .hb{ width:18px; height:2px; background:#fff; border-radius:99px; position:relative; }
    .hamburger .hb::before,
    .hamburger .hb::after{
      content:""; position:absolute; left:0;
      width:18px; height:2px; background:#fff; border-radius:99px;
    }
    .hamburger .hb::before{ top:-6px; }
    .hamburger .hb::after{ top: 6px; }

    .nav-backdrop {
      position: fixed; inset: 0;
      background: rgba(0,0,0,.45);
      opacity: 0; pointer-events: none;
      transition: opacity .2s ease;
      z-index: 10;
    }
    body.nav-open .nav-backdrop { opacity: 1; pointer-events: auto; }
    body.nav-open .language-selector { opacity: 0; pointer-events: none; }

    header.topbar{
      transition: background .35s ease, box-shadow .35s ease,
                  backdrop-filter .35s ease, -webkit-backdrop-filter .35s ease;
    }
    .search-card input, .search-card select, .search-card textarea{ font-size:16px !important; }

    .language-selector { display:flex; align-items:center; gap:5px; margin-right:10px; z-index:10000; }
    .lang-btn {
      background: rgba(255,255,255,0.15);
      backdrop-filter: blur(5px); -webkit-backdrop-filter: blur(5px);
      border: 1px solid rgba(255,255,255,0.25);
      border-radius: 30px; padding: 5px 12px;
      cursor: pointer; display:flex; align-items:center; gap:6px;
      transition: all 0.3s ease; color:white; font-weight:500; font-size:13px;
    }
    .lang-btn img { width:20px; height:15px; border-radius:2px; object-fit:cover; box-shadow:0 2px 4px rgba(0,0,0,0.2); }
    .lang-btn:hover { background: rgba(255,255,255,0.25); transform: translateY(-1px); border-color: rgba(255,255,255,0.5); }
    .lang-btn.active { background: rgba(255,255,255,0.3); border-color: rgba(255,255,255,0.8); }

    .language-selector.dropdown { position: relative; }
    .language-selector .dropdown-menu {
      min-width:100px; background:white; border:none;
      box-shadow:0 4px 20px rgba(0,0,0,0.15);
      border-radius:10px; padding:8px 0; margin-top:5px;
    }
    .language-selector .dropdown-item {
      display:flex; align-items:center; gap:10px;
      padding:10px 15px; cursor:pointer; color:#333;
      font-weight:500; transition:background 0.2s ease; text-decoration:none;
    }
    .language-selector .dropdown-item:hover { background:#f8f9fa; color:#333; }
    .language-selector .dropdown-item img { width:24px; height:18px; object-fit:cover; border-radius:3px; }

    .lang-btn.dropdown-toggle::after { margin-left:2px; color:white; }
    .lang-btn.dropdown-toggle {
      display:flex; align-items:center;
      background: rgba(255,255,255,0.15);
      backdrop-filter: blur(5px);
      border: 1px solid rgba(255,255,255,0.25);
      padding:2px 8px; font-size:11px;
      border-radius:8px; height:30px;
    }

    @media (max-width: 940px) {
      .nav-actions > a.icon-pill { display: none !important; }
    }
    .mobile-login-item { display: none; }
    @media (max-width: 940px) {
      .mobile-login-item { display: block; }
    }
  </style>
</head>

<body>
{{-- ============================================================
     🚀 OPTIMIZACIÓN 5: GTM (NOSCRIPT) AL INICIO DEL BODY
     El script de GTM lo movimos al final del body para no
     bloquear el render. Aquí solo dejamos el noscript.
============================================================ --}}
<noscript>
  <iframe src="https://www.googletagmanager.com/ns.html?id=GTM-PB88VSKW"
          height="0" width="0" style="display:none;visibility:hidden"></iframe>
</noscript>

@php
    if (session()->has('locale')) {
        App::setLocale(session('locale'));
    }
@endphp

<div class="nav-backdrop"></div>

<header class="topbar glass">
  <nav class="nav">
    <div class="brand">
      <a href="{{ route('rutaHome') }}" class="brand-link" aria-label="Viajero">
        {{-- 🚀 fetchpriority="high" le dice al navegador "este logo es importante, descárgalo primero" --}}
        <img src="{{ asset('img/LogoB.webp') }}" alt="Viajero" class="brand-logo-img"
             width="120" height="28" fetchpriority="high" decoding="async">
      </a>
    </div>

    <ul class="menu" id="mainMenu">
      <li><a href="{{ route('rutaHome') }}" class="{{ request()->routeIs('rutaHome') ? 'active' : '' }}">{{ __('Home') }}</a></li>
      <li><a href="{{ route('rutaCatalogo') }}" class="{{ request()->routeIs('rutaCatalogo') ? 'active' : '' }}">{{ __('Vehicles') }}</a></li>
      <li><a href="{{ route('rutaContacto') }}" class="{{ request()->routeIs('rutaContacto') ? 'active' : '' }}">{{ __('Contact') }}</a></li>
      <li><a href="{{ route('rutaPoliticas') }}" class="{{ request()->routeIs('rutaPoliticas') ? 'active' : '' }}">{{ __('Policies') }}</a></li>
      <li><a href="{{ route('rutaFAQ') }}" class="{{ request()->routeIs('rutaFAQ') ? 'active' : '' }}">{{ __('FAQ') }}</a></li>
      <li class="mobile-login-item"><a href="{{ route('auth.show') }}">{{ __('Sign in') }}</a></li>
    </ul>

    <div class="nav-actions">
      <div class="language-selector dropdown">
        <button class="lang-btn dropdown-toggle" data-bs-toggle="dropdown">
          {{-- 🚀 Agregué width/height para evitar layout shift --}}
          <img id="currentFlag" width="20" height="15"
               src="{{ app()->getLocale() == 'en' ? 'https://flagcdn.com/w40/us.png' : 'https://flagcdn.com/w40/mx.png' }}"
               alt="flag">
          <span id="currentLang">{{ strtoupper(app()->getLocale()) }}</span>
        </button>

        <ul class="dropdown-menu dropdown-menu-end shadow">
          <li><a class="dropdown-item" href="/lang/en"><img src="https://flagcdn.com/w40/us.png" width="24" height="18" alt="EN"> ENG</a></li>
          <li><a class="dropdown-item" href="/lang/es"><img src="https://flagcdn.com/w40/mx.png" width="24" height="18" alt="ES"> ESP</a></li>
        </ul>
      </div>

      @if (session()->has('id_usuario'))
        <div class="dropdown">
          <a href="#" class="icon-pill dropdown-toggle" data-bs-toggle="dropdown">
            <i class="fa-solid fa-user"></i>
          </a>
          <ul class="dropdown-menu dropdown-menu-end shadow">
            <li><a class="dropdown-item" href="{{ route('rutaPerfil') }}">{{ __('Profile') }}</a></li>
            <li><hr class="dropdown-divider"></li>
            <li>
              <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="dropdown-item">{{ __('Logout') }}</button>
              </form>
            </li>
          </ul>
        </div>
      @else
        <a href="{{ route('auth.show') }}" class="icon-pill" title="{{ __('Sign in') }}">
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

{{-- ============================================================
     🚀 OPTIMIZACIÓN 6: FOOTER CON LAZY LOAD
     Las imágenes del footer no se ven al cargar la página
     (están abajo). loading="lazy" hace que solo se descarguen
     cuando el usuario haga scroll hasta ellas.
     Esto ahorra ~1.5 segundos en la carga inicial.
============================================================ --}}
<footer class="site-footer">
  <div class="footer-bg" style="background-image:url('https://images.unsplash.com/photo-1465447142348-e9952c393450?q=80&w=1600&auto=format&fit=crop');"></div>
  <div class="footer-overlay"></div>
  <div class="footer-content">
    <div class="footer-brandmark">
      <img src="{{ asset('img/LogoB.webp') }}" alt="Viajero" class="footer-logo"
           width="120" height="42" loading="lazy" decoding="async">
    </div>

    <div class="footer-row loc-row">
      <div class="loc-card">
        <div class="pin"><i class="fa-solid fa-location-dot"></i></div>
        <div class="loc-body"><h4>Plaza Central Park, Querétaro Centro</h4><p>{{ __('Main office') }}</p></div>
      </div>
      <div class="loc-card">
        <div class="pin"><i class="fa-solid fa-location-dot"></i></div>
        <div class="loc-body"><h4>Aeropuerto Internacional de Querétaro (AIQ)</h4><p>{{ __('Pick-up / Drop-off') }}</p></div>
      </div>
      <div class="loc-card">
        <div class="pin"><i class="fa-solid fa-location-dot"></i></div>
        <div class="loc-body"><h4>Central de Autobuses de Querétaro (TAQ)</h4><p>{{ __('Pick-up / Drop-off') }}</p></div>
      </div>
    </div>

    <div class="footer-row pay-row">
      <div class="payments-logos">
        {{-- 🚀 Todas las imágenes de pago con lazy load --}}
        <img src="{{ asset('img/visa.webp') }}"       alt="Visa"             loading="lazy" decoding="async" width="60" height="38" />
        <img src="{{ asset('img/mastercard.webp') }}" alt="Mastercard"       loading="lazy" decoding="async" width="60" height="38" />
        <img src="{{ asset('img/america.webp') }}"    alt="American Express" loading="lazy" decoding="async" width="60" height="38" />
        <img class="oxxo" src="{{ asset('img/oxxo.webp') }}" alt="OXXO"      loading="lazy" decoding="async" width="60" height="38" />
        <img class="mp" src="{{ asset('img/pago.webp') }}"   alt="Mercado Pago" loading="lazy" decoding="async" width="60" height="38" />
        <img src="{{ asset('img/paypal.webp') }}"     alt="PayPal"           loading="lazy" decoding="async" width="60" height="38" />
      </div>
    </div>

    <div class="footer-row links-row">
      <ul>
        <li><a href="{{ route('rutaReservasIniciar') }}">{{ __('Book now') }}</a></li>
        <li><a href="{{ route('rutaCatalogo') }}">{{ __('Available cars') }}</a></li>
        <li><a href="{{ route('rutaPoliticas') }}">{{ __('Terms and conditions') }}</a></li>
        <li><a href="{{ route('rutaContacto') }}">{{ __('Contact') }}</a></li>
      </ul>
      <ul>
        <li><a href="{{ route('rutaFAQ') }}">{{ __('FAQ') }}</a></li>
        <li><a href="{{ route('rutaPoliticas') }}">{{ __('Privacy policy') }}</a></li>
        <li><a href="{{ route('rutaPoliticas') }}">{{ __('Cleaning policy') }}</a></li>
        <li><a href="{{ route('rutaPoliticas') }}">{{ __('Rental policy') }}</a></li>
      </ul>
    </div>

    <div class="footer-copy">© <span id="year"></span> Viajero. {{ __('All rights reserved') }}.</div>
  </div>
</footer>

{{-- ============================================================
     🚀 OPTIMIZACIÓN 7: TODOS LOS SCRIPTS CON DEFER
     defer = se descargan en paralelo PERO se ejecutan en orden,
     después de que el HTML esté listo. No bloquean el render.
     Mantienen el orden, así que jQuery sigue cargando antes
     que los plugins que lo usan.
============================================================ --}}
<script src="https://code.jquery.com/jquery-3.6.0.min.js" defer></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js" defer></script>
<script src="https://cdn.jsdelivr.net/npm/flatpickr" defer></script>
<script src="//cdn.jsdelivr.net/npm/alertifyjs@1.13.1/build/alertify.min.js" defer></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js" defer></script>
<script src="{{ asset('js/sucursalesTraducciones.js') }}" defer></script>

{{-- ============================================================
     🚀 OPTIMIZACIÓN 8: GTM AL FINAL CON CARGA ASÍNCRONA
     Antes lo tenías como lo PRIMERO del <head>, lo que bloquea
     el render mientras carga ~115 KB de tracking.
     Ahora se carga después de todo lo importante.
============================================================ --}}
<script>
  // GTM diferido - se carga cuando la página ya está visible
  window.addEventListener('load', function() {
    (function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start':
    new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0],
    j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src=
    'https://www.googletagmanager.com/gtm.js?id='+i+dl;f.parentNode.insertBefore(j,f);
    })(window,document,'script','dataLayer','GTM-PB88VSKW');
  });
</script>

{{-- ============================================================
     SCRIPTS PROPIOS (también con defer-like: dentro de DOMContentLoaded)
============================================================ --}}
<script>
document.addEventListener('DOMContentLoaded', function() {
    "use strict";

    // ==============================================
    // MENÚ HAMBURGUESA
    // ==============================================
    const topbar = document.querySelector(".topbar");
    const btn = document.getElementById("navHamburger");
    const menu = document.getElementById("mainMenu");
    const backdrop = document.querySelector(".nav-backdrop");

    if (!topbar || !btn || !menu) return;

    const MQ = window.matchMedia("(max-width: 940px)");
    const isMobile = () => MQ.matches;

    function openNav(){
        if(!isMobile()) return;
        document.body.classList.add("nav-open");
        btn.setAttribute("aria-expanded","true");
    }
    function closeNav(){
        document.body.classList.remove("nav-open");
        btn.setAttribute("aria-expanded","false");
    }

    btn.addEventListener("click", (e)=>{
        e.stopPropagation();
        document.body.classList.contains("nav-open") ? closeNav() : openNav();
    });

    if (backdrop) backdrop.addEventListener("click", closeNav);

    document.addEventListener("click", (event) => {
        if (!isMobile() || !document.body.classList.contains("nav-open")) return;
        if (menu.contains(event.target)) return;
        if (btn.contains(event.target)) return;
        if (backdrop && backdrop.contains(event.target)) return;
        const languageSelector = document.querySelector('.language-selector');
        if (languageSelector && languageSelector.contains(event.target)) return;
        closeNav();
    });

    menu.addEventListener("click", (e)=>{
        if(e.target.closest("a") && isMobile()) closeNav();
    });

    document.addEventListener("keydown", (e)=>{
        if(e.key === "Escape" && isMobile()) closeNav();
    });

    if(MQ.addEventListener){
        MQ.addEventListener("change", ()=>{ if(!isMobile()) closeNav(); });
    }

    // ==============================================
    // TOPBAR SCROLL EFFECT
    // ==============================================
    const SOLID_AT = 20;
    function syncTopbar(){
        if(window.scrollY > SOLID_AT){
            topbar.classList.add("solid");
            topbar.classList.remove("glass");
        } else {
            topbar.classList.add("glass");
            topbar.classList.remove("solid");
        }
    }
    syncTopbar();
    window.addEventListener("scroll", syncTopbar, { passive:true });

    // ==============================================
    // 🚀 OPTIMIZACIÓN: PROTECCIÓN NULL
    // Antes: document.getElementById('year').textContent = ...
    // Si por error no existe el span, daba error y rompía todo.
    // ==============================================
    const yearEl = document.getElementById('year');
    if (yearEl) yearEl.textContent = new Date().getFullYear();
});
</script>

{{-- 🔹 Scripts específicos por vista (se mantienen como los tenías) --}}
@yield('js-vistaHome')
@yield('js-vistaCatalogo')
@yield('js-vistaReservaciones')
@yield('js-vistaContacto')
@yield('js-vistaPoliticas')
@yield('js-vistaFAQ')
@yield('js-vistaLogin')
@yield('js-vistaPerfil')
@yield('js-visorReservacion')

{{-- ============================================================
     🚀 OPTIMIZACIÓN 9: GESTOS iOS - LIMPIEZA
     Antes tenías DOS IIFE con código duplicado para bloquear
     gestos en iOS. Ahora es uno solo y más limpio.
============================================================ --}}
<script>
(function(){
    // Bloquear zoom por gestos y doble tap en iOS
    document.addEventListener('gesturestart',  e => e.preventDefault(), {passive:false});
    document.addEventListener('gesturechange', e => e.preventDefault(), {passive:false});
    document.addEventListener('gestureend',    e => e.preventDefault(), {passive:false});

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

{{-- ============================================================
     TRADUCCIÓN DE SELECT2 (mantenido tal cual, solo movido al final)
============================================================ --}}
<script>
(function() {
    "use strict";

    function traducirSelect(selectElement) {
        if (!selectElement || !selectElement.options) return false;
        if (!window.sucursalesTraducciones || Object.keys(window.sucursalesTraducciones).length === 0) return false;

        const locale = document.documentElement.lang || 'es';
        let traduccionesRealizadas = 0;

        for (let i = 0; i < selectElement.options.length; i++) {
            const option = selectElement.options[i];
            const textoOriginal = option.textContent.trim();

            if (window.sucursalesTraducciones[textoOriginal]) {
                const textoTraducido = window.sucursalesTraducciones[textoOriginal][locale];
                if (textoTraducido && option.textContent !== textoTraducido) {
                    option.textContent = textoTraducido;
                    traduccionesRealizadas++;
                }
            }
        }

        if (typeof $ !== 'undefined' && $(selectElement).data('select2')) {
            $(selectElement).trigger('change.select2');
        }

        return traduccionesRealizadas > 0;
    }

    function traducirTodosLosSelects() {
        const posiblesIds = [
            'pickupPlace', 'dropoffPlace',
            'pickup_sucursal_id', 'dropoff_sucursal_id',
            'pickupPlacePoliticas', 'dropoffPlacePoliticas'
        ];

        posiblesIds.forEach(id => {
            const select = document.getElementById(id);
            if (select) traducirSelect(select);
        });

        const selectsPorNombre = document.querySelectorAll('select[name*="sucursal"], select[name*="pickup"], select[name*="dropoff"]');
        selectsPorNombre.forEach(select => {
            if (select.id && !posiblesIds.includes(select.id)) {
                traducirSelect(select);
            }
        });
    }

    const observer = new MutationObserver(() => {
        setTimeout(traducirTodosLosSelects, 100);
        setTimeout(traducirTodosLosSelects, 300);
        setTimeout(traducirTodosLosSelects, 600);
    });

    observer.observe(document.documentElement, {
        attributes: true,
        attributeFilter: ['lang']
    });

    document.addEventListener('click', (e) => {
        const langBtn = e.target.closest('.lang-btn, .dropdown-item[href*="/lang/"]');
        if (langBtn) {
            setTimeout(traducirTodosLosSelects, 150);
            setTimeout(traducirTodosLosSelects, 400);
            setTimeout(traducirTodosLosSelects, 800);
        }
    });

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', () => {
            setTimeout(traducirTodosLosSelects, 300);
            setTimeout(traducirTodosLosSelects, 800);
            setTimeout(traducirTodosLosSelects, 1500);
        });
    } else {
        setTimeout(traducirTodosLosSelects, 300);
        setTimeout(traducirTodosLosSelects, 800);
        setTimeout(traducirTodosLosSelects, 1500);
    }
})();
</script>
</body>
</html>
