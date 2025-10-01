<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
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
    <!-- ===== SIDEBAR UNIFICADO ===== -->
<aside class="sidebar">
    <!-- Logo -->
    <div class="logo">
        <img src="ruta_a_tu_logo.png" alt="Viajero Logo">
    </div>

    <ul class="menu">

        <!-- Sección: Flotilla -->
        <li class="menu-section">Flotilla</li>
        <li><a href="{{route('rutaFlotilla')}}"><i class="fas fa-truck"></i> Flotilla</a></li>
        <li><a href="{{route('rutaMantenimiento')}}"><i class="fas fa-wrench"></i> Mantenimiento</a></li>
        <li><a href="{{route('rutaPolizas')}}"><i class="fas fa-file-alt"></i> Pólizas</a></li>
        <li><a href="{{route('rutaCarroceria')}}"><i class="fas fa-car-side"></i> Carrocería</a></li>
        <li><a href="{{route('rutaSeguros')}}"><i class="fas fa-shield-alt"></i> Seguros</a></li>
        <li><a href="{{route('rutaGastos')}}"><i class="fas fa-coins"></i> Gastos</a></li>
        <li><a href="{{route('rutaDashboard')}}"><i class="fas fa-arrow-left"></i> Volver al panel</a></li>

        <!-- Sección: Reservaciones -->
        <li class="menu-section">Reservaciones</li>
        <li><a href="#"><i class="fas fa-file-invoice"></i> Reservaciones</a></li>
        <li><a href="#"><i class="fas fa-briefcase"></i> Cotizaciones</a></li>
        <li><a href="#"><i class="fas fa-check-square"></i> Reservaciones activas</a></li>
        <li><a href="#"><i class="fas fa-eye"></i> Visor de reservaciones</a></li>
        <li><a href="#"><i class="fas fa-cogs"></i> Administración de reservaciones</a></li>
        <li><a href="#"><i class="fas fa-folder-open"></i> Historial completo</a></li>
        <li><a href="#"><i class="fas fa-arrow-left"></i> Volver a módulos</a></li>
        <li><a href="#"><i class="fas fa-user-plus"></i> Alta Cliente</a></li>

        <!-- Sección: Administración -->
        <li class="menu-section">Administración</li>
        <li><a href="#"><i class="fas fa-users"></i> Usuarios</a></li>
        <li><a href="#"><i class="fas fa-user-shield"></i> Roles y permisos</a></li>
        <li><a href="#"><i class="fas fa-building"></i> Sedes / Equipos</a></li>
        <li><a href="#"><i class="fas fa-search"></i> Auditoría</a></li>
        <li><a href="#"><i class="fas fa-cog"></i> Configuración</a></li>
        <li><a href="#"><i class="fas fa-arrow-left"></i> Volver a módulos</a></li>

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
    @yield('js-vistaMantenimiento')
    @yield('js-vistaFlotilla')
    @yield('js-vistaPolizas')
    @yield('js-vistaCarroceria')
    @yield('js-vistaSeguros')
    @yield('js-vistaGastos')
</div>

</body>
</html>
