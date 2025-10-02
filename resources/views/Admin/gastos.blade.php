@extends('layouts.Flotillas')
@section('Titulo', 'Gastos')
    @section('css-vistaGastos')
        <link rel="stylesheet" href="{{asset('css/gastos.css')}}">
    @endsection

    @section('contenidoGastos')
    <main>
    <div class="topbar">
      <div><strong>Autos · Gastos</strong></div>
    </div>

    <div class="content">
      <h1 class="title">Gastos</h1>
      <p class="sub">Todos los gastos por categoría y vehículo (rango por día/semana/mes).</p>

      <div class="toolbar">
        <div class="search">💬
          <input id="qCost" type="text" placeholder="Buscar por coche, categoría o descripción">
        </div>
        <button class="btn ghost" id="exportCost">⬇️ Exportar CSV</button>
      </div>

      <div class="cards">
        <div class="card"><h4>Total</h4><div class="amt" id="gTot">$0</div><div class="small" id="gCount">0 movimientos</div></div>
        <div class="card"><h4>Mantenimiento</h4><div class="amt" id="gMaint">$0</div></div>
        <div class="card"><h4>Pólizas</h4><div class="amt" id="gPol">$0</div></div>
        <div class="card"><h4>Carrocería</h4><div class="amt" id="gBody">$0</div></div>
        <div class="card"><h4>Siniestros / Otros</h4><div class="amt" id="gOther">$0</div></div>
      </div>

      <div class="toolbar">
        <div class="dates">
          <input type="date" id="from"> —
          <input type="date" id="to">
          <button class="btn" id="applyRange">Aplicar</button>
          <button class="btn ghost" id="quickToday">Hoy</button>
          <button class="btn ghost" id="quickWeek">Semana</button>
          <button class="btn ghost" id="quickMonth">Mes</button>
        </div>
      </div>

      <div class="cards" id="cardsExtra" style="margin:8px 0">
        <div class="card"><h4>Top vehículo del periodo</h4><div class="amt" id="topCar">$0</div><div class="small" id="topCarName">—</div></div>
        <div class="card"><h4>Promedio por día</h4><div class="amt" id="avgPerDay">$0</div><div class="small" id="rangeLabel">—</div></div>
      </div>

      <div style="overflow:auto">
        <table class="table" id="tblCost">
          <thead>
            <tr>
              <th>Fecha</th><th>Carro</th><th>Rin</th><th>Categoría</th><th>Descripción</th><th>Importe</th>
            </tr>
          </thead>
          <tbody><!-- filas dinámicas --></tbody>
        </table>
      </div>
    </div>
  </main>
@section('js-vistaGastos')
    <script src="{{asset('js/gastos.js')}}"></script>
@endsection

@endsection
