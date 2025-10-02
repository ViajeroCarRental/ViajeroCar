@extends('layouts.Ventas')
@section('Titulo', 'Home Ventas')
    @section('css-vistaHomeVentas')
        <link rel="stylesheet" href="{{ asset('css/homeVentas.css') }}">
    @endsection
@section('contenidoHomeVentas')

  <main>
    <div class="topbar">
      <button class="burger" id="burger">☰</button>
      <div class="hi">Rentas · Panel</div>

    </div>

    <div class="content">
      <p class="muted" id="hello">Hola</p>

      <div class="grid">
        <div class="card">
          <h3>Reservaciones</h3>
          <p>Crea, edita y cancela reservaciones.</p>
          <a href="reservaciones.html">Abrir</a>
        </div>
        <div class="card">
          <h3>Cotizaciones</h3>
          <p>Genera y envía cotizaciones a clientes.</p>
          <a href="cotizaciones.html">Abrir</a>
        </div>
        <div class="card">
          <h3>Visor de reservaciones</h3>
          <p>Calendario por día/semana/mes.</p>
          <a href="visor.html">Abrir</a>
        </div>
        <div class="card">
          <h3>Administración general</h3>
          <p>Reglas, cargos, horarios y plantillas.</p>
          <a href="administracionreservaciones.html">Abrir</a>
        </div>
        <div class="card">
          <h3>Reservaciones activas</h3>
          <p>Entregadas y en curso.</p>
          <a href="activas.html">Abrir</a>
        </div>
        <div class="card">
          <h3>Historial completo</h3>
          <p>Buscador con filtros avanzados.</p>
          <a href="historial.html">Abrir</a>
        </div>
      </div>
    </div>
  </main>
</div>
<div class="scrim" id="scrim"></div>


    @section('js-vistaHomeVentas')
        <script src="{{ asset('js/homeVentas.js') }}"></script>
    @endsection
@endsection
