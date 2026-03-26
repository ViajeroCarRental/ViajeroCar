<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover, maximum-scale=1, user-scalable=no">
  <meta name="csrf-token" content="{{ csrf_token() }}">

  <!-- Bootstrap / Fuentes / Iconos -->
  <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
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
  @yield('css-visorReservaciones')

  <title>@yield('Titulo')</title>

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

    .brand-logo-img{height:28px; display:block}
    .footer-logo{height:42px; display:block; margin:0 auto}
    .brand a.brand-link{display:inline-flex; align-items:center; text-decoration:none}

    /* ===== ICONO PERSONA ===== */
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

    /* ===== HAMBURGER ===== */
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
   .nav-backdrop {
    position: fixed;
    inset: 0;
    background: rgba(0,0,0,.45);
    opacity: 0;
    pointer-events: none;
    transition: opacity .2s ease;
    z-index: 10;
}
   body.nav-open .nav-backdrop {
    opacity: 1;
    pointer-events: auto;
}
body.nav-open .language-selector {
    opacity: 0;
    pointer-events: none;
}

    header.topbar{
      transition:
        background .35s ease,
        box-shadow .35s ease,
        backdrop-filter .35s ease,
        -webkit-backdrop-filter .35s ease;
    }
    .search-card input,
    .search-card select,
    .search-card textarea{
      font-size:16px !important;
    }
    .language-selector {
        display: flex;
        align-items: center;
        gap: 5px;
        margin-right: 10px;
        z-index: 10000;
    }

    .lang-btn {
        background: rgba(255, 255, 255, 0.15);
        backdrop-filter: blur(5px);
        -webkit-backdrop-filter: blur(5px);
        border: 1px solid rgba(255, 255, 255, 0.25);
        border-radius: 30px;
        padding: 5px 12px;
        cursor: pointer;
        display: flex;
        align-items: center;
        gap: 6px;
        transition: all 0.3s ease;
        color: white;
        font-weight: 500;
        font-size: 13px;
    }

    .lang-btn img {
        width: 20px;
        height: 15px;
        border-radius: 2px;
        object-fit: cover;
        box-shadow: 0 2px 4px rgba(0,0,0,0.2);
    }

    .lang-btn:hover {
        background: rgba(255, 255, 255, 0.25);
        transform: translateY(-1px);
        border-color: rgba(255, 255, 255, 0.5);
    }

    .lang-btn.active {
        background: rgba(255, 255, 255, 0.3);
        border-color: rgba(255, 255, 255, 0.8);
    }
    .goog-te-banner-frame {
        display: none !important;
    }
    .goog-te-gadget-simple {
        display: none !important;
    }
    body {
        top: 0px !important;
    }
    .skiptranslate {
        display: none !important;
    }
    iframe.goog-te-banner-frame {
        display: none !important;
    }
/* ===== ESTILOS PARA DROPDOWN DE IDIOMAS ===== */
.language-selector.dropdown {
    position: relative;
}

.language-selector .dropdown-menu {
    min-width: 100px;
    background: white;
    border: none;
    box-shadow: 0 4px 20px rgba(0,0,0,0.15);
    border-radius: 10px;
    padding: 8px 0;
    margin-top: 5px;
}

.language-selector .dropdown-item {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 10px 15px;
    cursor: pointer;
    color: #333;
    font-weight: 500;
    transition: background 0.2s ease;
}

.language-selector .dropdown-item:hover {
    background: #f8f9fa;
}

.language-selector .dropdown-item img {
    width: 24px;
    height: 18px;
    object-fit: cover;
    border-radius: 3px;
}

/* Mejorar el botón principal */
.lang-btn.dropdown-toggle::after {
    margin-left: 2px;
    color: white;
    
}

.lang-btn.dropdown-toggle {
    display: flex;
    align-items: center;
    background: rgba(255, 255, 255, 0.15);
    backdrop-filter: blur(5px);
    border: 1px solid rgba(255, 255, 255, 0.25);
    padding: 2px 8px;   /* antes seguro está más grande */
    font-size: 11px;     /* reduce el texto */
    border-radius: 8px;  /* opcional, más compacto */
    height: 30px; 

}

  </style>
</head>

<body>

<!-- 🔹 BACKDROP AGREGADO  -->
<div class="nav-backdrop"></div>

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
      <li><a href="{{ route('rutaContacto') }}" class="{{ request()->routeIs('rutaContacto') ? 'active' : '' }}">Contacto</a></li>
      <li><a href="{{ route('rutaPoliticas') }}" class="{{ request()->routeIs('rutaPoliticas') ? 'active' : '' }}">Políticas</a></li>
      <li><a href="{{ route('rutaFAQ') }}" class="{{ request()->routeIs('rutaFAQ') ? 'active' : '' }}">F.A.Q</a></li>
    </ul>

    <div class="nav-actions">
    <div class="language-selector dropdown">
  <button class="lang-btn dropdown-toggle" data-bs-toggle="dropdown">
    <img id="currentFlag" src="https://flagcdn.com/w40/mx.png">
    <span id="currentLang">ES</span>
  </button>

  <ul class="dropdown-menu dropdown-menu-end shadow">
    <li>
      <a class="dropdown-item lang-option" data-lang="en">
        <img src="https://flagcdn.com/w40/us.png"> ENG
      </a>
    </li>
    <li>
      <a class="dropdown-item lang-option" data-lang="es">
        <img src="https://flagcdn.com/w40/mx.png"> ESP
      </a>
    </li>
  </ul>
</div>
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

<!-- 🔹 SCRIPTS-->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<script src="//cdn.jsdelivr.net/npm/alertifyjs@1.13.1/build/alertify.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<script>
(function(){
    "use strict";

    // ==============================================
    // CÓDIGO DE TÚ MENÚ HAMBURGUESA (LIGERAMENTE MODIFICADO)
    // ==============================================
    const topbar = document.querySelector(".topbar");
    const btn = document.getElementById("navHamburger");
    const menu = document.getElementById("mainMenu");
    const backdrop = document.querySelector(".nav-backdrop");

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

    btn.addEventListener("click", (e)=>{
        e.stopPropagation();
        document.body.classList.contains("nav-open") ? closeNav() : openNav();
    });

    if (backdrop) {
        backdrop.addEventListener("click", closeNav);
    }

    // ⚠️⚠️⚠️ ATENCIÓN: ESTA ES LA PARTE MODIFICADA ⚠️⚠️⚠️
    // Antes tenías: if (menu.contains(event.target)) return;
    // El problema es que los botones de traducción están DENTRO del header, no del menu.
    // Por eso al hacer clic en ellos, el menú se cerraba.
    document.addEventListener("click", (event) => {
        if (!isMobile() || !document.body.classList.contains("nav-open")) return;
        if (menu.contains(event.target)) return;


        if (btn.contains(event.target)) return;
        if (backdrop && backdrop.contains(event.target)) return;


        const languageSelector = document.getElementById('languageSelector');
        if (languageSelector && languageSelector.contains(event.target)) return;

        // Si no fue en ninguna de las partes permitidas, cerramos el menú.
        closeNav();
    });
    // ===== FIN DE LA MODIFICACIÓN =====

    menu.addEventListener("click", (e)=>{
        if(e.target.closest("a") && isMobile()) closeNav();
    });

    document.addEventListener("keydown", (e)=>{
        if(e.key === "Escape" && isMobile()) closeNav();
    });

    if(MQ.addEventListener){
        MQ.addEventListener("change", ()=>{ if(!isMobile()) closeNav(); });
    }

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

/// ==============================================
// CÓDIGO DE GOOGLE TRANSLATE CON SELECTOR DROPDOWN
// ==============================================
function googleTranslateElementInit() {
    new google.translate.TranslateElement({
        pageLanguage: 'es',
        includedLanguages: 'es,en',
        layout: google.translate.TranslateElement.InlineLayout.SIMPLE,
        autoDisplay: false
    }, '');
}

document.addEventListener('DOMContentLoaded', function() {
    // Cargar script de Google Translate
    const script = document.createElement('script');
    script.src = '//translate.google.com/translate_a/element.js?cb=googleTranslateElementInit';
    document.head.appendChild(script);

    // Función para cambiar idioma
    window.cambiarIdioma = function(idioma) {
        // Guardar preferencia
        localStorage.setItem('idiomaPreferido', idioma);

        // Establecer cookie para Google Translate
        document.cookie = `googtrans=/es/${idioma}; path=/; max-age=31536000`;

        // Actualizar el botón visual inmediatamente
        actualizarBotonIdioma(idioma);

        // Intentar cambiar el select de Google Translate si ya existe
        cambiarSelectGoogle(idioma);

        // Recargar para aplicar traducción completa
        setTimeout(() => {
            location.reload();
        }, 500);
    };

    // Función para actualizar el botón visual
    function actualizarBotonIdioma(idioma) {
        const flagImg = document.getElementById('currentFlag');
        const langSpan = document.getElementById('currentLang');

        if (idioma === 'en') {
            flagImg.src = 'https://flagcdn.com/w40/us.png';
            langSpan.textContent = 'EN';
        } else {
            flagImg.src = 'https://flagcdn.com/w40/mx.png';
            langSpan.textContent = 'ES';
        }
    }

    // Función para cambiar el select de Google Translate
    function cambiarSelectGoogle(idioma) {
        const select = document.querySelector(".goog-te-combo");
        if (select) {
            select.value = idioma;
            select.dispatchEvent(new Event("change"));
        }
    }

    // Restaurar idioma guardado
    const idiomaGuardado = localStorage.getItem('idiomaPreferido');
    if (idiomaGuardado) {
        document.cookie = `googtrans=/es/${idiomaGuardado}; path=/; max-age=31536000`;
        actualizarBotonIdioma(idiomaGuardado);

        // Intentar cambiar el select después de que Google cargue
        setTimeout(() => {
            cambiarSelectGoogle(idiomaGuardado);
        }, 1000);
    }

    // Event listeners para las opciones del dropdown
    document.querySelectorAll('.lang-option').forEach(option => {
        option.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();

            const lang = this.dataset.lang;
            console.log('Cambiando a idioma:', lang); // Para depurar

            if (lang) {
                cambiarIdioma(lang);
            }
        });
    });

    // También permitir que el botón principal abra el dropdown (Bootstrap lo maneja)
});
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
@yield('js-visorReservacion')

<script>
  // iOS: bloquear zoom
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

  (function(){
    document.addEventListener('gesturestart', e => e.preventDefault(), {passive:false});
    document.addEventListener('gesturechange', e => e.preventDefault(), {passive:false});
    document.addEventListener('gestureend',   e => e.preventDefault(), {passive:false});
    document.addEventListener('touchmove', e => {
      if (e.touches && e.touches.length > 1) e.preventDefault();
    }, {passive:false});
  })();
</script>

<script>
  // Actualizar año en footer
  document.getElementById('year').textContent = new Date().getFullYear();
</script>

</body>
</html>
