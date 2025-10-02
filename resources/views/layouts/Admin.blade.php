<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    @yield('css-vistaUsuariosAdmin')
    @yield('css-vistaRoles')
    <link rel="stylesheet" href="{{asset('css/sidebar.css')}}">
    <title>@yield('Titulo')</title>
</head>
<body>
    <!-- SIDEBAR: Administración -->
<aside class="sidebar sidebar--admin">
  <div class="logo">
    <img src="ruta_a_tu_logo.png" alt="Viajero Logo">
  </div>

  <ul class="menu">
    <li class="menu-section">Administración</li>
    <li><a href="{{ route('rutaUsuarios') }}"><i class="fas fa-users"></i> Usuarios</a></li>
    <li><a href="{{ route('rutaRoles') }}"><i class="fas fa-user-shield"></i> Roles y permisos</a></li>

    <li class="menu-section">Navegación</li>
    <li><a href="{{ route('rutaDashboard') }}"><i class="fas fa-arrow-left"></i> Volver a módulos</a></li>
  </ul>
</aside>


<div class="main-content">
    @yield('contenidoUsuariosAdmin')
    @yield('contenidoRoles')
</div>


<div class="containerJS">
    @yield('js-vistaUsuariosAdmin')
    @yield('js-vistaRoles')
</div>

</body>
</html>
