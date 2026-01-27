<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="icon" href="{{ asset('img/Icono.ico') }}" type="image/x-icon">
    <link rel="shortcut icon" href="{{ asset('img/Icono.ico') }}" type="image/x-icon">
    <meta name="csrf-token" content="{{ csrf_token() }}">
        {{-- AlertifyJS CSS --}}
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/alertifyjs@1.13.1/build/css/alertify.min.css"/>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/alertifyjs@1.13.1/build/css/themes/default.min.css"/>



    @yield('css-vistaMantenimiento')
    @yield('css-vistaFlotilla')
    @yield('css-vistaPolizas')
    @yield('css-vistaCarroceria')
    @yield('css-vistaSeguros')
    @yield('css-vistaGastos')
    <link rel="stylesheet" href="{{asset('css/sidebar.css')}}">
    <title>@yield('Titulo')</title>
</head>
<body>

<button class="btn-toggle" id="btnToggleSidebar" type="button" aria-label="Mostrar/Ocultar menú">
  <i class="bi bi-list"></i>
</button>
<div class="sidebar-overlay" id="sidebarOverlay"></div>

    <!-- SIDEBAR: Flotilla -->
<aside class="sidebar sidebar--flotilla">
  <div class="logo">
    <img src="{{ asset('img/Logo3.jpg') }}" alt="Viajero Car Rental">

  </div>

  <ul class="menu">
    <li class="menu-section">Flotilla</li>
    <li><a href="{{ route('rutaFlotilla') }}"><i class="fas fa-truck"></i> Flotilla</a></li>
    <li><a href="{{ route('rutaMantenimiento') }}"><i class="fas fa-wrench"></i> Mantenimiento</a></li>
    <li><a href="{{ route('rutaPolizas') }}"><i class="fas fa-file-alt"></i> Pólizas</a></li>
    <li><a href="{{ route('rutaCarroceria') }}"><i class="fas fa-car-side"></i> Carrocería</a></li>
    <li><a href="{{ route('rutaSeguros') }}"><i class="fas fa-shield-alt"></i> Seguros</a></li>
    <li><a href="{{ route('rutaGastos') }}"><i class="fas fa-coins"></i> Gastos</a></li>

    <li class="menu-section">Navegación</li>
    <li><a href="{{ route('rutaDashboard') }}"><i class="fas fa-arrow-left"></i> Volver al panel</a></li>
  </ul>
</aside>

<div class="main-content">
    @yield('contenidoMantenimiento')
    @yield('contenidoFlotilla')
    @yield('contenidoPolizas')
    @yield('contenidoCarroceria')
    @yield('contenidoSeguros')
    @yield('contenidoGastos')
</div>


<div class="containerJS">
<div class="containerJS">
    {{-- AlertifyJS JS --}}
    <script src="https://cdn.jsdelivr.net/npm/alertifyjs@1.13.1/build/alertify.min.js"></script>

    @yield('js-vistaMantenimiento')
    @yield('js-vistaFlotilla')
    @yield('js-vistaPolizas')
    @yield('js-vistaCarroceria')
    @yield('js-vistaSeguros')
    @yield('js-vistaGastos')

    <script src="{{ asset('js/sidebar-toggle.js') }}"></script>

    {{-- ⚠️ Mensaje de sesión expirada --}}
    @if (session('session_expired'))
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                alertify.error(@json(session('session_expired')));
            });
        </script>
    @endif
</div>
</body>
</html>
