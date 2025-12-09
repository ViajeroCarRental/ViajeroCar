@extends('layouts.Ventas')

@section('Titulo', 'Historial')

@section('css-vistaHistorial')
  <link rel="stylesheet" href="{{ asset('css/Historial.css') }}">
@endsection

@section('contenidoHistorial')
  <main class="main">
    <h1 class="h1">Historial de Rentas</h1>

    <!-- Filtros -->
    <section class="section">
      <div class="head">Filtros</div>
      <div class="cnt">
        <div class="flex">

          <input type="text" id="q" placeholder="Buscar por cliente, folio, vehículo…" style="min-width:260px">

          <label>Desde
            <input type="date" id="fini">
          </label>

          <label>Hasta
            <input type="date" id="ffin">
          </label>

          <select id="fstatus">
            <option value="">Estatus</option>
            <option>Reservada</option>
            <option>En contrato</option>
            <option>En curso</option>
            <option>Finalizada</option>
            <option>Cancelada</option>
            <option>No show</option>
          </select>

          <select id="fpago">
            <option value="">Pago</option>
            <option>Pagada</option>
            <option>Pendiente</option>
            <option>Saldo a favor</option>
          </select>

          <select id="fsucursal">
            <option value="">Sucursal</option>
            <option>Centro</option>
            <option>Aeropuerto</option>
            <option>Bernardo Quintana</option>
          </select>

          <select id="fvehiculo">
            <option value="">Categoría</option>
            <option>Economico</option>
            <option>Compacto</option>
            <option>Intermedio</option>
            <option>SUV</option>
          </select>

          <button id="btnFiltrar" class="btn primary">Aplicar</button>
          <button id="btnClear" class="btn gray">Limpiar</button>
        </div>
      </div>
    </section>

    <!-- Resumen -->
    <section class="section">
      <div class="head">Resumen</div>
      <div class="cnt summary" id="sumCards">
        <div class="card"><div class="t">Accciones</div><div class="v" id="sumCount">0</div></div>
        <div class="card"><div class="t">Ingresos</div><div class="v" id="sumTotal">$0</div></div>
        <div class="card"><div class="t">Pagado</div><div class="v" id="sumPagado">$0</div></div>
        <div class="card"><div class="t">Saldo Pendiente</div><div class="v" id="sumSaldo">$0</div></div>
      </div>
    </section>

    <!-- Tabla -->
    <section class="section">
      <div class="head">Resultados</div>
      <div class="cnt">

        <div class="table-wrap">
          <table class="table" id="tbl">
            <thead>
              <tr>
                <th>Folio</th>
                <th>Fecha</th>
                <th>Cliente</th>
                <th>Vehículo / Categoría</th>
                <th>Días</th>
                <th>Sucursal</th>
                <th>Estatus</th>
                <th>Total</th>
                <th>Pagado</th>
                <th>Saldo</th>
                <th></th>
              </tr>
            </thead>

            <!-- 🔥 Tbody que el JS necesita -->
            <tbody id="tbody">
              <tr>
                <td colspan="11" style="text-align:center;color:#667085">Cargando…</td>
              </tr>
            </tbody>
          </table>
        </div>

        <div class="pager">
          <div class="badge" id="range">0–0 de 0</div>
          <button id="prev">‹</button>
          <button id="next">›</button>
          <select id="pp">
            <option>10</option>
            <option selected>20</option>
            <option>50</option>
            <option>100</option>
          </select>
        </div>

      </div>
    </section>

  </main>
@endsection

@section('js-vistaHistorial')
  <script src="{{ asset('js/Historial.js') }}" defer></script>
@endsection
