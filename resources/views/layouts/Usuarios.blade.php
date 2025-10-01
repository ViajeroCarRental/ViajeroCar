<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@600;800&family=Poppins:wght@400;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/@fortawesome/fontawesome-free@6.5.2/css/all.min.css" rel="stylesheet">
    <!-- Flatpickr (CSS) - lo dejo por si lo usas en otras vistas -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/themes/material_red.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@600;800&family=Poppins:wght@400;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/@fortawesome/fontawesome-free@6.5.2/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/navbar-top.css') }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@600;800&family=Poppins:wght@400;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/@fortawesome/fontawesome-free@6.5.2/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr@4.6.13/dist/flatpickr.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700;800&family=Poppins:wght@400;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/@fortawesome/fontawesome-free@6.5.2/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/navbarUsuarios.css') }}">
    <!--css vistas-->
    @yield('css-vistaHome')
    @yield('css-VistaCatalogo')
    @yield('css-vistaReservaciones')
    @yield('css-vistaContacto')
    @yield('css-vistaPoliticas')
    @yield('css-vistaFAQ')
    <title>@yield('Titulo')</title>
</head>
<body>

<header class="topbar glass">
    <nav class="nav">
        <div class="brand"><span class="brand-logo">VIAJERO</span></div>
            <ul class="menu">
                <li><a href="{{ route('rutaHome') }}" class="active">Inicio</a></li>
                <li><a href="{{ route('rutaCatalogo') }}">Catálogo de autos</a></li>
                <li><a href="{{ route('rutaReservaciones') }}">Reservaciones</a></li>
                <li><a href="{{ route('rutaContacto') }}">Contacto</a></li>
                <li><a href="{{ route('rutaFAQ') }}">F.A.Q</a></li>
            </ul>
        <div class="nav-actions">
            <!-- icono dinámico segun sesión -->
            <a id="accountLink" class="login" href="login.html" aria-label="Cuenta">
                <i class="fa-regular fa-user"></i>
            </a>
            <button class="hamburger" aria-label="Menú"><i class="fa-solid fa-bars"></i></button>
        </div>
    </nav>
</header>

    <!--Contenedor vistas -->
    <div class="containerVS">
        @yield('contenidoHome')
        @yield('contenidoCatalogo')
        @yield('contenidoReservaciones')
        @yield('contenidoContacto')
        @yield('contenidoPoliticas')
        @yield('contenidoFAQ')
    </div>

    <div class="containerJS">
        @yield('js-vistaHome')
        @yield('js-vistaCatalogo')
        @yield('js-vistaReservaciones')
        @yield('js-vistaContacto')
        @yield('js-vistaPoliticas')
        @yield('js-vistaFAQ')
    </div>

{{-- Bootstrap JS (necesario para el toggle móvil) --}}
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<!-- FOOTER -->
    <footer class="site-footer">
      <div class="footer-bg" style="background-image:url('https://images.unsplash.com/photo-1465447142348-e9952c393450?q=80&w=1600&auto=format&fit=crop');"></div>
      <div class="footer-overlay"></div>
      <div class="footer-content">
        <div class="footer-brandmark">VIAJERO</div>
        <div class="footer-row loc-row">
          <div class="loc-card"><div class="pin"><i class="fa-solid fa-location-dot"></i></div><div class="loc-body"><h4>Central Park, Querétaro</h4><p>Oficina principal</p></div></div>
          <div class="loc-card"><div class="pin"><i class="fa-solid fa-location-dot"></i></div><div class="loc-body"><h4>Aeropuerto de Querétaro</h4><p>Pick-up / Drop-off</p></div></div>
          <div class="loc-card"><div class="pin"><i class="fa-solid fa-location-dot"></i></div><div class="loc-body"><h4>Aeropuerto de León</h4><p>Pick-up / Drop-off</p></div></div>
        </div>
        <div class="footer-row pay-row">
          <div class="payments-logos">
            <img src="https://upload.wikimedia.org/wikipedia/commons/4/41/Visa_Logo.png" alt="Visa" />
            <img src="https://upload.wikimedia.org/wikipedia/commons/2/2a/Mastercard-logo.svg" alt="Mastercard" />
            <img src="https://upload.wikimedia.org/wikipedia/commons/3/30/American_Express_logo_%282018%29.svg" alt="American Express" />
            <img class="oxxo" src="https://upload.wikimedia.org/wikipedia/commons/0/06/Oxxo_Logo.svg" alt="OXXO" />
            <img class="mp" src="https://upload.wikimedia.org/wikipedia/commons/1/16/MercadoPago.svg" alt="Mercado Pago" />
            <img src="https://upload.wikimedia.org/wikipedia/commons/b/b5/PayPal.svg" alt="PayPal" />
          </div>
        </div>
        <div class="footer-row links-row">
          <ul>
            <li><a href="reserva.html">Mi reserva</a></li>
            <li><a href="catalogo.html">Autos</a></li>
            <li><a href="#">Empresas</a></li>
            <li><a href="politicas.html">Términos y condiciones</a></li>
            <li><a href="contacto.html">Contacto</a></li>
          </ul>
          <ul>
            <li><a href="#">Blog</a></li>
            <li><a href="faq.html">F.A.Q.</a></li>
            <li><a href="politicas.html">Aviso de privacidad</a></li>
            <li><a href="politicas.html">Política de limpieza</a></li>
            <li><a href="politicas.html">Política de renta</a></li>
          </ul>
        </div>
        <div class="footer-copy">© <span id="year"></span> Viajero. Todos los derechos reservados.</div>
      </div>
    </footer>
</body>
</html>
