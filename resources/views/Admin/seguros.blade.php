@extends('layouts.Flotillas')
@section('Titulo', 'Seguros')
    @section('css-vistaSeguros')
        <link rel="stylesheet" href="{{ asset('css/seguros.css') }}">
    @endsection
    @section('contenidoSeguros')
    <main>
    <div class="topbar">
      <div><strong>Autos · Seguros / Siniestros</strong></div>
    </div>

    <div class="content">
      <h1 class="title">Seguros · Siniestros</h1>
      <p class="sub">Registro y seguimiento de siniestros.</p>

      <div class="toolbar">
        <div class="search">🛡️
          <input id="qClaims" type="text" placeholder="Buscar por coche o folio">
        </div>
        <button class="btn ghost" id="exportClaims">⬇️ Exportar CSV</button>
      </div>

      <div style="overflow:auto">
        <table class="table" id="tblClaims">
          <thead>
            <tr>
              <th>Folio</th>
              <th>Carro</th>
              <th>Fecha</th>
              <th>Tipo</th>
              <th>Estatus</th>
              <th>Deducible</th>
              <th>Rin</th>
            </tr>
          </thead>
          <tbody><!-- filas dinámicas --></tbody>
        </table>
      </div>
    </div>
  </main>
    @section('js-vistaSeguros')
        <script src="{{ asset('js/seguros.js') }}" defer></script>
    @endsection
@endsection
