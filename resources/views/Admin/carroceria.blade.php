@extends('layouts.Flotillas')
@section('Titulo', 'Carrocerias')
    @section('css-vistaCarroceria')
        <link rel="stylesheet" href="{{ asset('css/carroceria.css') }}">
    @endsection
@section('contenidoCarroceria')
    <main>
    <div class="topbar">
      <div><strong>Autos · Carrocería</strong></div>
    </div>

    <div class="content">
      <h1 class="title">Carrocería</h1>
      <p class="sub">Historial de daños, reparaciones y reportes visuales de cada vehículo.</p>

      <div class="toolbar">
        <div class="search">
          <input id="qCarroceria" type="text" placeholder="Buscar por coche, placa o estatus">
        </div>
        <button class="btn" id="newReporte">➕ Nuevo reporte</button>
      </div>

      <div class="cards" id="carroceriaGrid">
        <!-- Aquí se renderizan dinámicamente las tarjetas de carrocería -->
      </div>
    </div>
  </main>
@section('js-vistaCarroceria')
        <script src="{{ asset('js/carroceria.js') }}"></script>
@endsection
@endsection
