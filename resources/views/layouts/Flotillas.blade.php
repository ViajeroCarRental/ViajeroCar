<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" rel="stylesheet">
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
<!-- Botón para mostrar/ocultar sidebar -->
<button id="toggleSidebar" class="btn-toggle">
  <i class="fas fa-bars"></i>
</button>

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
<script>
document.addEventListener("DOMContentLoaded", function(){
  const sidebar = document.querySelector(".sidebar");
  const btnToggle = document.getElementById("toggleSidebar");

  btnToggle.addEventListener("click", () => {
    sidebar.classList.toggle("sidebar-hidden");
  });
});
</script>

</html>