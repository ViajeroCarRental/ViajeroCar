@extends('layouts.Ventas')
@section('Titulo', 'Cotizaciones - Viajero Car')
    @section('css-vistaHomeCotizaciones')
        <link rel="stylesheet" href="{{ asset('css/Cotizaciones.css') }}">
    @endsection
@section('contenidoCotizaciones')
    <main class="main">
    <h1 class="h1">Cotizaciones</h1>
    <div class="grid2">
      <div class="card">
        <h3>🧾 Cotizar auto</h3>
        <p>Abre el flujo de 3 pasos para crear y enviar una nueva cotización.</p>
        <div><button class="btn primary" onclick="location.href='{{ route('rutaCotizar') }}'">Ir a cotizar</button></div>
      </div>
      <div class="card">
        <h3>🗂️ Cotizaciones recientes</h3>
        <p>Consulta, continúa, convierte a reservación o elimina cotizaciones guardadas.</p>
        <div><button class="btn ghost" onclick="location.href='{{ route('rutaCotizacionesRecientes') }}'">Ver recientes</button></div>
      </div>
    </div>
  </main>

    @section('js-vistaHomeCotizaciones')
        <script src="{{ asset('js/Cotizaciones.js') }}" defer></script>
    @endsection

@endsection
