<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="{{ asset('css/navbar-top.css') }}">
    <title>@yield('Titulo')</title>
</head>
<body>

    <style>
    /* Barra arriba, estilo oscuro */
.navbar {
  background: #212529 !important;
  box-shadow: 0 2px 6px rgba(0,0,0,.3);
}

/* Quitar viñetas y ajustar spacing */
.navbar-nav { list-style: none; gap: .25rem; }
.navbar-nav .nav-item { list-style: none; }

/* Links */
.navbar-nav .nav-link {
  color: #f8f9fa !important;
  padding: .5rem .9rem;
  border-radius: .35rem;
  transition: background .2s, color .2s;
}
.navbar-nav .nav-link:hover {
  background: rgba(255,255,255,.08);
  color: #ffc107 !important;
}

/* Dropdown oscuro */
.dropdown-menu {
  background: #343a40;
  border: none;
  border-radius: .5rem;
  padding: .4rem;
}
.dropdown-item {
  color: #f8f9fa;
  border-radius: .35rem;
}
.dropdown-item:hover {
  background: #495057;
  color: #ffc107;
}

/* Mostrar submenús al pasar el mouse (en pantallas medianas en adelante) */
@media (min-width: 768px) {
  .nav-item.dropdown:hover > .dropdown-menu {
    display: block;
    margin-top: 0; /* evita salto */
  }
}

/* Si usas fixed-top, separa el contenido para que no tape */
body.has-fixed-navbar {
  padding-top: 64px; /* ajusta según altura real de la navbar */
}


    </style>
    {{-- La Navbar del usuario --}}
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark fixed-top">
  <div class="container-fluid">
    <a class="navbar-brand fw-bold" href="{{ route('rutahome') }}">Viajero Car</a>

    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navMain" aria-controls="navMain" aria-expanded="false" aria-label="Toggle navigation">
      <span class="navbar-toggler-icon"></span>
    </button>

    <div class="collapse navbar-collapse" id="navMain">
      <ul class="navbar-nav ms-auto align-items-lg-center">

        {{-- Vehículos --}}
        <li class="nav-item dropdown">
          <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">Vehículos</a>
          <ul class="dropdown-menu">
            <li><a class="dropdown-item" href="{{ route('rutacatalogovehiculos') }}">Catálogo</a></li>
            <li><a class="dropdown-item" href="{{ route('rutadetallevehiculo') }}">Detalle Vehículo</a></li>
          </ul>
        </li>

        {{-- Reservaciones --}}
        <li class="nav-item dropdown">
          <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">Reservaciones</a>
          <ul class="dropdown-menu">
            <li><a class="dropdown-item" href="{{ route('rutareservar') }}">Reservar</a></li>
            <li><a class="dropdown-item" href="{{ route('rutamisreservaciones') }}">Mis Reservaciones</a></li>
            <li><a class="dropdown-item" href="{{ route('rutamisfacturas') }}">Mis Facturas</a></li>
          </ul>
        </li>

        {{-- Membresías --}}
        <li class="nav-item dropdown">
          <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">Membresías</a>
          <ul class="dropdown-menu">
            <li><a class="dropdown-item" href="{{ route('rutamembresias') }}">Membresías</a></li>
            <li><a class="dropdown-item" href="{{ route('rutamimembresia') }}">Mi Membresía</a></li>
          </ul>
        </li>

        {{-- Usuario --}}
        <li class="nav-item dropdown">
          <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">Usuario</a>
          <ul class="dropdown-menu">
            <li><a class="dropdown-item" href="{{ route('rutaperfil') }}">Perfil</a></li>
            <li><a class="dropdown-item" href="{{ route('rutanotificaciones') }}">Notificaciones</a></li>
          </ul>
        </li>

        {{-- Políticas --}}
        <li class="nav-item dropdown">
          <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">Políticas</a>
          <ul class="dropdown-menu">
            <li><a class="dropdown-item" href="{{ route('rutapoliticarenta') }}">Política de Renta</a></li>
            <li><a class="dropdown-item" href="{{ route('rutapoliticaslimpieza') }}">Políticas de Limpieza</a></li>
            <li><a class="dropdown-item" href="{{ route('rutaavisoprivacidad') }}">Aviso de Privacidad</a></li>
            <li><a class="dropdown-item" href="{{ route('rutaterminoscondiciones') }}">Términos y Condiciones</a></li>
          </ul>
        </li>

        {{-- Información --}}
        <li class="nav-item dropdown">
          <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">Información</a>
          <ul class="dropdown-menu">
            <li><a class="dropdown-item" href="{{ route('rutacontactoubicaciones') }}">Contacto y Ubicaciones</a></li>
            <li><a class="dropdown-item" href="{{ route('rutaayuda') }}">Ayuda</a></li>
          </ul>
        </li>

        {{-- Cuenta --}}
        <li class="nav-item dropdown">
          <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">Cuenta</a>
          <ul class="dropdown-menu dropdown-menu-end">
            <li><a class="dropdown-item" href="{{ route('rutalogin') }}">Login</a></li>
            <li><a class="dropdown-item" href="{{ route('rutaregistro') }}">Registro</a></li>
            <li><a class="dropdown-item" href="{{ route('rutarecuperarcontrasena') }}">Recuperar Contraseña</a></li>
            <li><a class="dropdown-item" href="{{ route('rutaverificacioncorreo') }}">Verificación de Correo</a></li>
          </ul>
        </li>

      </ul>
    </div>
  </div>
</nav>





    {{--Contenidos de las vistas.seguir la misma estructura del div junto con su yield--}}
    <div class="container">
        @yield('contenidoHome')
    </div>

    <div class="container">
        @yield('contenidoVerificacionDeCorreo')
    </div>

    <div class="container">
        @yield('contenidoTerminosYCondiciones')
    </div>

    <div class="container">
        @yield('contenidoReservar')
    </div>

    <div class="container">
        @yield('contenidoRegistro')
    </div>

    <div class="container">
        @yield('contenidoRecuperarContraseña')
    </div>

    <div class="container">
        @yield('contenidoPoliticasDeLimpieza')
    </div>

    <div class="container">
        @yield('contenidoPoliticaDeRenta')
    </div>

    <div class="container">
        @yield('contenidoPerfil')
    </div>

    <div class="container">
        @yield('contenidoNotificaciones')
    </div>

    <div class="container">
        @yield('contenidoMisReservaciones')
    </div>

    <div class="container">
        @yield('contenidoMisFacturas')
    </div>

    <div class="container">
        @yield('contenidoMiMembresia')
    </div>

    <div class="container">
        @yield('contenidoMembresias')
    </div>
    <div class="container">
        @yield('contenidoLogin')
    </div>

    <div class="container">
        @yield('contenidoDetallesVehiculo')
    </div>

    <div class="container">
        @yield('contenidoContactoYUbicaciones')
    </div>

    <div class="container">
        @yield('contenidoCatalogoVehiculos')
    </div>

    <div class="container">
        @yield('contenidoAyuda')
    </div>

    <div class="container">
        @yield('contenidoAvisoDePrivacidad')
    </div>



{{-- Bootstrap JS (necesario para el toggle móvil) --}}
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
