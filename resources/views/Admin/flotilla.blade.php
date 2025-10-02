@extends('layouts.Flotillas')
@section('Titulo', 'Flotilla')
    @section('css-vistaFlotilla')
        <link rel="stylesheet" href="{{ asset('css/flotilla.css') }}">
    @endsection

@section('contenidoMantenimiento')
    <main>
    <div class="topbar">
      <div><strong>Autos · Flotilla</strong></div>
    </div>

    <div class="content">
      <h1 class="title">Flotilla</h1>
      <p class="sub">Inventario y disponibilidad. Clic en una fila para ver la ficha.</p>

      <div class="toolbar">
        <div class="search">🔎
          <input id="qFleet" type="text" placeholder="Buscar por coche, placa, rin o estatus">
        </div>
        <button class="btn ghost" id="exportFleet">⬇️ Exportar CSV</button>
      </div>

      <div style="overflow:auto">
        <table class="table" id="tblFleet">
          <thead>
            <tr>
              <th>Carro</th><th>Placa</th><th>Servicio</th><th>Rin</th>
              <th>Kilometraje</th><th>Estatus</th><th>Entrada</th><th>Salida</th>
            </tr>
          </thead>
          <tbody>
            <!-- Aquí se llenan dinámicamente los autos -->
          </tbody>
        </table>
      </div>
    </div>
  </main>



    @section('js-vistaFlotilla')
        <script src="{{ asset('js/flotilla.js') }}"></script>
    @endsection
@endsection
