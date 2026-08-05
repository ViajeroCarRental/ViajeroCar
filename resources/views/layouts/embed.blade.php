<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('Titulo', 'Documento')</title>

    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" rel="stylesheet">
    <link rel="icon" href="{{ asset('img/Icono.ico') }}" type="image/x-icon">
    <link rel="shortcut icon" href="{{ asset('img/Icono.ico') }}" type="image/x-icon">

    <!-- Alertify -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/alertifyjs@1.13.1/build/css/alertify.min.css"/>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/alertifyjs@1.13.1/build/css/themes/default.min.css"/>

    <!-- CSS VISTAS (solo las que usan checklist / anexo embebidos) -->
    @yield('css-vistareservacionesAdmin')
    @yield('css-vistaFacturar')

    @yield('css')

    <style>
        html, body {
            margin: 0;
            padding: 0;
            background: #ffffff;
        }
        /* Sin barra lateral ni botón "regresar" en modo embebido */
        .embed-content-wrap {
            display: block;
            width: 100%;
        }
    </style>
</head>

<body>

<div class="embed-content-wrap">
    @yield('contenidoreservacionesAdmin')
    @yield('contenidoFacturar')

    @yield('contenido')
</div>

<div class="containerJS">
    <script src="https://cdn.jsdelivr.net/npm/alertifyjs@1.13.1/build/alertify.min.js"></script>

    @yield('js-vistareservacionesAdmin')
    @yield('js-vistaFacturar')

    @yield('js')
</div>

</body>
</html>
