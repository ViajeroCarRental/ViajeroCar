<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="stylesheet" href="{{asset('css/sidebar.css')}}">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/alertifyjs@1.13.1/build/css/alertify.min.css"/>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/alertifyjs@1.13.1/build/css/themes/default.min.css"/>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" rel="stylesheet">
    <link rel="icon" href="{{ asset('img/Icono.ico') }}" type="image/x-icon">
    <link rel="shortcut icon" href="{{ asset('img/Icono.ico') }}" type="image/x-icon">

    <!-- Bootstrap Icons -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    @yield('css-vistaHomeVentas')
    @yield('css-vistareservacionesAdmin')
    @yield('css-vistaHomeCotizaciones')
    @yield('css-vistaCotizar')
    @yield('css-vistaHCotizacionesRecientes')
    @yield('css-vistaReservacionesActivas')
    @yield('css-vistaVisorReservaciones')
    @yield('css-vistaAdministracionReservaciones')
    @yield('css-vistaHistorial')
    @yield('css-vistaContrato')
    @yield('css-vistaAltaCliente')
    @yield('css-vistaLicencia')
    @yield('css-vistaRFC-Fiscal')
    @yield('css-vistaFacturar')
    @yield('css-VistaCotizacionesRecientes')
    @yield('css-vistaContratoFinal')
    @yield('css-vistaEditarContrato')
    <title>@yield('Titulo')</title>
</head>
<body>

<button class="btn-toggle" id="btnToggleSidebar" type="button" aria-label="Mostrar/Ocultar menú">
  <i class="bi bi-list"></i>
</button>
<div class="sidebar-overlay" id="sidebarOverlay"></div>

    <!-- SIDEBAR: Reservaciones -->
<aside class="sidebar sidebar--reservas">
  <div class="logo">
    <img src="{{ asset('img/Logo3.jpg') }}" alt="Viajero Car Rental">
  </div>

  <ul class="menu">
    <li class="menu-section">Reservaciones</li>
    <li><a href="{{ route('rutaReservacionesAdmin') }}"><i class="fas fa-file-invoice"></i> Reservaciones</a></li>
    <li><a href="{{ route('rutaCotizar') }}"><i class="fas fa-briefcase"></i> Cotizaciones</a></li>
    <li><a href="{{ route('rutaReservacionesActivas') }}"><i class="fas fa-check-square"></i>Bookings</a></li>
    <li><a href="{{ route('rutaVisorReservaciones') }}"><i class="fas fa-eye"></i> Visor de reservaciones</a></li>
    <li><a href="{{ route('rutaAdministracionReservaciones') }}"><i class="fas fa-cogs"></i> Contratos</a></li>
    <li><a href="{{ route('ventas.historial') }}"><i class="fas fa-folder-open"></i> Historial completo</a></li>
    <li><a href="{{ route('rutaAltaCliente') }}"><i class="fas fa-user-plus"></i> Alta Cliente</a></li>
    <li><a href="{{ route('rutaFacturar') }}"><i class="fas fa-file-invoice-dollar"></i> Facturar</a></li>

    <li class="menu-section">Navegación</li>
    <li><a href="{{route('rutaInicioVentas')}}"><i class="fas fa-arrow-left"></i> Volver al Panel</a></li>
    <li><a href="{{ route('rutaDashboard') }}"><i class="fas fa-arrow-left"></i> Volver a módulos</a></li>
  </ul>
</aside>
<div class="main-content">
    @yield('contenidoHomeVentas')
    @yield('contenidoreservacionesAdmin')
    @yield('contenidoCotizaciones')
    @yield('contenidoCotizar')
    @yield('contenidoCotizacionesRecientes')
    @yield('contenidoReservacionesActivas')
    @yield('contenidoVisorReservaciones')
    @yield('contenidoAdministracionReservaciones')
    @yield('contenidoHistorial')
    @yield('contenidoContrato')
    @yield('contenidoAltaCliente')
    @yield('contenidoLicencia')
    @yield('contenidoRFC-Fiscal')
    @yield('contenidoFacturar')
    @yield('contenido-VistaCotizacionesRecientes')
    @yield('contenidoContratoFinal')
    @yield('contenidoEditarContrato')
    @yield('contenidoChecklist2')
</div>

<div class="containerJS">
    <script src="https://cdn.jsdelivr.net/npm/alertifyjs@1.13.1/build/alertify.min.js"></script>
    @yield('js-vistaHomeVentas')
    @yield('js-vistareservacionesAdmin')
    @yield('js-vistaHomeCotizaciones')
    @yield('js-vistaCotizar')
    @yield('js-vistaCotizacionesRecientes')
    @yield('js-vistaReservacionesActivas')
    @yield('js-vistaVisorReservaciones')
    @yield('js-vistaAdministracionReservaciones')
    @yield('js-vistaHistorial')
    @yield('js-vistaContrato')
    @yield('js-vistaAltaCliente')
    @yield('js-vistaLicencia')
    @yield('js-vistaRFC-Fiscal')
    @yield('js-vistaFacturar')
    @yield('js-VistaCotizacionesRecientes')
    @yield('js-vistaContratoFinal')
    @yield('js-vistaEditarContrato')
    @yield('js-vistaChecklist2')
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

