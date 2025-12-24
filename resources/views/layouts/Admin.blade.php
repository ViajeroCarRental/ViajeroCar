<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="icon" href="{{ asset('img/Icono.ico') }}" type="image/x-icon">
    <link rel="shortcut icon" href="{{ asset('img/Icono.ico') }}" type="image/x-icon">



    {{-- ✅ CSS específicos que ya usas --}}
    @yield('css-vistaUsuariosAdmin')
    @yield('css-vistaRoles')

    {{-- ✅ CSS genérico para vistas nuevas (Categorías, Seguros, etc.) --}}
    @yield('css')

    <link rel="stylesheet" href="{{ asset('css/sidebar.css') }}">

    <title>@yield('Titulo')</title>
</head>
<body>

<button class="btn-toggle" id="btnToggleSidebar" type="button" aria-label="Mostrar/Ocultar menú">
  <i class="bi bi-list"></i>
</button>
<div class="sidebar-overlay" id="sidebarOverlay"></div>

<!-- SIDEBAR: Administración -->
<aside class="sidebar sidebar--admin">
  <div class="logo">
    <img src="{{ asset('img/Logo3.jpg') }}" alt="Viajero Car Rental">
  </div>

  <ul class="menu">
    <li class="menu-section">Administración</li>

    <li>
      <a href="{{ route('admin.usuarios.index') }}">
        <i class="fas fa-users"></i> Usuarios
      </a>
    </li>

    <li>
      <a href="{{ route('roles.index') }}">
        <i class="fas fa-user-shield"></i> Roles y permisos
      </a>
    </li>

    <li>
      <a href="{{ route('categorias.index') }}">
        <i class="fas fa-tags"></i> Categorías
      </a>
    </li>

    <li>
      <a href="{{ route('paqueteseguros.index') }}">
        <i class="fas fa-shield-alt"></i> Seguros
      </a>
    </li>

    <li>
      <a href="{{ route('paquetesindividuales.index') }}">
        <i class="fas fa-layer-group"></i> Seguros Individuales
      </a>
    </li>

    <li class="menu-section">Navegación</li>

    <li>
      <a href="{{ route('rutaDashboard') }}">
        <i class="fas fa-arrow-left"></i> Volver a módulos
      </a>
    </li>
  </ul>
</aside>

<div class="main-content">
    @yield('contenidoUsuariosAdmin')
    @yield('contenidoRoles')

    @yield('contenido')
</div>

<div class="containerJS">
    @yield('js-vistaUsuariosAdmin')
    @yield('js-vistaRoles')

    @yield('js')
    <script src="{{ asset('js/sidebar-toggle.js') }}"></script>

</div>

</body>
</html>
