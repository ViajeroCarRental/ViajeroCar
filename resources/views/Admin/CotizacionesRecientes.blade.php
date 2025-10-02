@extends('layouts.Ventas')

@section('Titulo', 'Cotizaciones Recientes')

{{-- CSS de la vista --}}
@section('css-vistaHCotizacionesRecientes')
<link rel="stylesheet" href="{{ asset('css/cotizacionesRecientes.css') }}">
@endsection

{{-- CONTENIDO --}}
@section('contenidoCotizacionesRecientes')
<main class="main">
  <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:6px">
    <h1 class="h1">Cotizaciones recientes</h1>
    <div class="tools">
      <label class="small" style="display:flex;align-items:center;gap:6px">
        <input type="checkbox" id="showAll"> Mostrar todas
      </label>
      <button class="btn gray" id="cleanup">🧹 Limpiar vencidas</button>
      <button class="btn primary" onclick="location.href='cotizar.html'">+ Nueva cotización</button>
      {{-- Si tienes ruta en Laravel, mejor:
      <button class="btn primary" onclick="location.href='{{ route('rutaCotizar') }}'">+ Nueva cotización</button>
      --}}
    </div>
  </div>

  <div class="card">
    <div class="head">Guardadas · <span id="countVig">0</span> vigentes</div>
    <div class="cnt">
      <table class="table">
        <thead>
          <tr>
            <th>ID</th>
            <th>Cliente</th>
            <th>Rango</th>
            <th>Vehículo</th>
            <th>Total</th>
            <th>Estado</th>
            <th>Vence</th>
            <th>Acciones</th>
          </tr>
        </thead>
        <tbody id="tbody"></tbody>
      </table>
    </div>
  </div>
</main>
@endsection  {{-- FIN contenido --}}

{{-- JS de la vista --}}
@section('js-vistaCotizacionesRecientes')
<script src="{{ asset('js/CotizacionesRecientes.js') }}"></script>
@endsection
