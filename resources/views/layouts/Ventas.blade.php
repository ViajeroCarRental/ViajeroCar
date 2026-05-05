<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('Titulo', 'Ventas')</title>

    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" rel="stylesheet">
    <link rel="icon" href="{{ asset('img/Icono.ico') }}" type="image/x-icon">
    <link rel="shortcut icon" href="{{ asset('img/Icono.ico') }}" type="image/x-icon">

    <!-- Alertify -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/alertifyjs@1.13.1/build/css/alertify.min.css"/>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/alertifyjs@1.13.1/build/css/themes/default.min.css"/>

    <!-- CSS VISTAS -->
    @yield('css-vistaHomeVentas')
    @yield('css-vistareservacionesAdmin')
    @yield('css-vistaReservacionesActivas')
    @yield('css-vistaCotizar')
    @yield('css-vistaVisorReservaciones')
    @yield('css-vistaAdministracionReservaciones')
    @yield('css-vistaHistorial')
    @yield('css-vistaContrato')
    @yield('css-vistaAltaCliente')
    @yield('css-vistaFacturar')

    @yield('css')

    <link rel="stylesheet" href="{{ asset('css/sidebar.css') }}">

    <!-- 🔥 FIX GLOBAL DEL CONTENEDOR -->
    <style>
        .ventas-internal-content{
            display:block !important;
            padding:28px 40px !important;
        }

        .ventas-internal-content .inner-back-wrap{
            width:100%;
            display:flex;
            justify-content:flex-start;
            align-items:center;
            margin:0 0 22px 0;
            padding:0;
            position:relative;
            z-index:50;
            order:-999;
        }

        .ventas-internal-content .inner-back-btn{
            display:inline-flex;
            align-items:center;
            gap:9px;
            padding:11px 18px;
            border-radius:12px;
            background:#fff;
            color:#b22222;
            border:1px solid rgba(178,34,34,.25);
            text-decoration:none;
            font-weight:800;
            font-size:14px;
            box-shadow:0 10px 25px rgba(0,0,0,.06);
            transition:.2s ease;
        }

        .ventas-internal-content .inner-back-btn:hover{
            background:#b22222;
            color:#fff;
        }

        @media(max-width:650px){
            .ventas-internal-content{
                padding:20px !important;
            }

            .ventas-internal-content .inner-back-btn{
                width:100%;
                justify-content:center;
            }
        }
    </style>
</head>

<body>

<!-- 🔥 CONTENEDOR PRINCIPAL -->
<div class="main-content ventas-internal-content">

    <!-- 🔙 BOTÓN -->
    <div class="inner-back-wrap">
        <a href="{{ route('rutaMenuVentas') }}" class="inner-back-btn">
            <i class="fas fa-arrow-left"></i>
            Regresar a menú ventas
        </a>
    </div>

    <!-- 🔥 CONTENEDOR DE CONTENIDO (CLAVE PARA QUE NO SE BAJE) -->
    <div class="ventas-content-wrap">

        @yield('contenidoHomeVentas')
        @yield('contenidoreservacionesAdmin')
        @yield('contenidoReservacionesActivas')
        @yield('contenidoCotizar')
        @yield('contenidoVisorReservaciones')
        @yield('contenidoAdministracionReservaciones')
        @yield('contenidoHistorial')
        @yield('contenidoContrato')
        @yield('contenidoAltaCliente')
        @yield('contenidoFacturar')

        @yield('contenido')

    </div>

</div>

<div class="containerJS">
    <script src="https://cdn.jsdelivr.net/npm/alertifyjs@1.13.1/build/alertify.min.js"></script>

    @yield('js-vistaHomeVentas')
    @yield('js-vistareservacionesAdmin')
    @yield('js-vistaReservacionesActivas')
    @yield('js-vistaCotizar')
    @yield('js-vistaVisorReservaciones')
    @yield('js-vistaAdministracionReservaciones')
    @yield('js-vistaHistorial')
    @yield('js-vistaContrato')
    @yield('js-vistaAltaCliente')
    @yield('js-vistaFacturar')

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
