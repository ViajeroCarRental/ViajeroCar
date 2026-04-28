<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('Titulo', 'Administración')</title>

    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" rel="stylesheet">
    <link rel="icon" href="{{ asset('img/Icono.ico') }}" type="image/x-icon">
    <link rel="shortcut icon" href="{{ asset('img/Icono.ico') }}" type="image/x-icon">

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/alertifyjs@1.13.1/build/css/alertify.min.css"/>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/alertifyjs@1.13.1/build/css/themes/default.min.css"/>

    <link rel="stylesheet" href="{{ asset('css/sidebar.css') }}">

@yield('css-vistaUsuariosAdmin')
@yield('css-vistaRoles')
@yield('css')
</head>

<body>

<div class="main-content admin-internal-content">

    <div class="inner-back-wrap">
        <a href="{{ route('rutaMenuAdmin') }}" class="inner-back-btn">
            <i class="fas fa-arrow-left"></i>
            Regresar a menú admin
        </a>
    </div>

    @yield('contenidoUsuariosAdmin')
    @yield('contenidoRoles')
    @yield('contenido')

</div>

<div class="containerJS">
    <script src="https://cdn.jsdelivr.net/npm/alertifyjs@1.13.1/build/alertify.min.js"></script>

    @yield('js-vistaUsuariosAdmin')
    @yield('js-vistaRoles')
    @yield('js')

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
