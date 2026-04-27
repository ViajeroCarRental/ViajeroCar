<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('titulo', 'Flotilla')</title>

    <link rel="stylesheet" href="{{ asset('css/sidebar.css') }}">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" rel="stylesheet">

    @yield('css-vistaFlotilla')
    @yield('css-vistaMantenimiento')
    @yield('css-vistaPolizas')
    @yield('css-vistaCarroceria')
    @yield('css-vistaSeguros')
    @yield('css-vistaGastos')
    @yield('css-vistaDocumentacion')
</head>

<body>

<div class="main-content flotilla-internal-content">

    <div class="inner-back-wrap">
        <a href="{{ route('rutaMenuFlotilla') }}" class="inner-back-btn">
            <i class="fas fa-arrow-left"></i>
            Regresar 
        </a>
    </div>

    @yield('contenidoFlotilla')
    @yield('contenidoMantenimiento')
    @yield('contenidoPolizas')
    @yield('contenidoCarroceria')
    @yield('contenidoSeguros')
    @yield('contenidoGastos')
    @yield('contenidoDocumentos')

</div>

@yield('js-vistaFlotilla')
@yield('js-vistaMantenimiento')
@yield('js-vistaPolizas')
@yield('js-vistaCarroceria')
@yield('js-vistaSeguros')
@yield('js-vistaGastos')
@yield('js-vistaDocumentacion')

</body>
</html>