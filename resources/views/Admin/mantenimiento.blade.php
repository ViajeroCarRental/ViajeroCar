@extends('layouts.Admin')
@section('Titulo', 'Mantenimiento')

@section('css-vistaMantenimiento')
    <link rel="stylesheet" href="{{ asset('css/mantenimiento.css') }}">
@endsection
@section('contenidoMantenimiento')
    <main>
    <div class="topbar">
      <div><strong>Autos · Mantenimiento</strong></div>
    </div>

    <div class="content">
      <h1 class="title">Mantenimiento</h1>
      <p class="sub">Control por kilometraje (aceite y rotación). Edita al cierre de cada renta.</p>

      <div class="toolbar">
        <div class="search">🔧
          <input id="qMaint" type="text" placeholder="Buscar por coche, placa o rin">
        </div>
        <button class="btn" id="newService">🗓️ Acciones rápidas (demo)</button>
      </div>

      <div class="mgrid" id="mGrid">
        <!-- Aquí se renderizan las tarjetas de mantenimiento -->
      </div>
    </div>
  </main>

@section('js-vistaMantenimiento')
    <script src="{{ asset('js/mantenimiento.js') }}"></script>

@endsection
@endsection
