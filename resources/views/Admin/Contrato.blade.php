@extends('layouts.Ventas')
@section('Titulo', 'Contrato')
    @section('css-vistaContrato')
        <link rel="stylesheet" href="{{ asset('css/Contrato.css') }}">
    @endsection
    @section('contenidoContrato')
        <main class="main">
    <h1 class="h1">Datos del contrato</h1>

    <div class="grid">
      <!-- Izquierda -->
      <section class="card">
        <div class="head">Datos de Reservación</div>
        <div class="cnt" id="left">
          <!-- se llena con JS -->
        </div>
      </section>

      <!-- Derecha -->
      <section class="card">
        <div class="head">Datos del Auto</div>
        <div class="cnt" id="right">
          <!-- se llena con JS -->
        </div>
      </section>
    </div>
  </main>
        @section('js-vistaHistorial')
  {{-- Ruta de Laravel con placeholder __FOLIO__ --}}
  <script>
    window.CONTRATO_URL_TEMPLATE = @json(route('rutaContrato', ['folio' => '__FOLIO__']));
  </script>

  {{-- Tu JS principal --}}
  <script src="{{ asset('js/Historial.js') }}"></script>
@endsection
    @endsection

