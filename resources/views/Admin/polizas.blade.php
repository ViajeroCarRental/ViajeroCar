@extends('layouts.Flotillas')
@section('Titulo', 'Polizas')
    @section('css-vistaPolizas')
        <link rel="stylesheet" href="{{ asset('css/polizas.css') }}">
    @endsection
@section('contenidoPolizas')


  <main>
    <div class="topbar">
      <div><strong>Autos · Pólizas</strong></div>
    </div>

    <div class="content">
      <h1 class="title">Pólizas</h1>
      <p class="sub">Control de vigencias de pólizas de seguro por vehículo.</p>

      <div class="toolbar">
        <div class="search">
          <input id="qPolizas" type="text" placeholder="Buscar por coche, placa o póliza">
        </div>
        <button class="btn ghost" id="exportPolizas">⬇️ Exportar CSV</button>
      </div>

      <div style="overflow:auto">
        <table class="table" id="tblPolizas">
          <thead>
            <tr>
              <th>Carro</th>
              <th>Póliza</th>
              <th>Aseguradora</th>
              <th>Vigencia</th>
              <th>Estatus</th>
            </tr>
          </thead>
          <tbody>
            <!-- Aquí se llenan dinámicamente las pólizas -->
          </tbody>
        </table>
      </div>
    </div>
  </main>



@section('js-vistaPolizas')
        <script src="{{ asset('js/polizas.js') }}"></script>
@endsection
@endsection
