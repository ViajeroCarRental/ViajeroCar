<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <link rel="stylesheet" href="{{asset('css/sidebar.css')}}">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" rel="stylesheet">
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
    <title>@yield('Titulo')</title>
</head>
<body>
    <!-- SIDEBAR: Reservaciones -->
<aside class="sidebar sidebar--reservas">
  <div class="logo">
    <img src="{{ asset('img/Logo3.jpg') }}" alt="Viajero Car Rental">
  </div>

  <ul class="menu">
    <li class="menu-section">Reservaciones</li>
    <li><a href="{{ route('rutaReservacionesAdmin') }}"><i class="fas fa-file-invoice"></i> Reservaciones</a></li>
    <li><a href="{{ route('rutaCotizaciones') }}"><i class="fas fa-briefcase"></i> Cotizaciones</a></li>
    <li><a href="{{ route('rutaReservacionesActivas') }}"><i class="fas fa-check-square"></i> Reservaciones activas</a></li>
    <li><a href="{{ route('rutaVisorReservaciones') }}"><i class="fas fa-eye"></i> Visor de reservaciones</a></li>
    <li><a href="{{ route('rutaAdministracionReservaciones') }}"><i class="fas fa-cogs"></i> Administración de reservaciones</a></li>
    <li><a href="{{ route('rutaHistorialCompleto') }}"><i class="fas fa-folder-open"></i> Historial completo</a></li>
    <li><a href="{{ route('rutaAltaCliente') }}"><i class="fas fa-user-plus"></i> Alta Cliente</a></li>
    <li><a href="{{ route('rutaFacturar') }}"><i class="fas fa-user-plus"></i> Facturar</a></li>

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
</div>

<div class="containerJS">
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
</div>
</body>
</html>
