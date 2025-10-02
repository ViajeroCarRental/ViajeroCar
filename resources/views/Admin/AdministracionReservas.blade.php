@extends('layouts.Ventas')
@section('Titulo', 'Administración De Reservas')
    @section('css-vistaAdministracionReservaciones')
        <link rel="stylesheet" href="{{asset('css/AdministracionReservas.css')}}">
    @endsection
@section('contenidoAdministracionReservaciones')
     <!-- Contenido -->
  <main class="main">
    <div class="page">
      <h1 class="h1">Administración de Reservaciones</h1>

      <div class="topbar">
        <label>Mostrar
          <select id="selSize" class="select">
            <option>10</option><option>25</option><option>50</option><option>100</option>
          </select>
          registros
        </label>
        <div class="search">
          <input id="txtSearch" class="input" type="search" placeholder="Buscar: clave, nombre, email, estado…" />
        </div>
      </div>

      <table class="table" id="tbl">
        <thead>
          <tr>
            <th style="width:40px"></th>
            <th>Clave</th>
            <th>Fecha Checkout</th>
            <th>Hora Checkout</th>
            <th>Nombre</th>
            <th>Apellidos</th>
            <th>Email</th>
            <th>Estatus</th>
            <th class="col-actions"> </th>
          </tr>
        </thead>
        <tbody id="tbody"></tbody>
      </table>

      <div class="pager">
        <button class="pbtn" id="prev">« Anterior</button>
        <div id="pgInfo" class="small"></div>
        <button class="pbtn" id="next">Siguiente »</button>
      </div>
    </div>
  </main>
    @section('js-vistaAdministracionReservaciones')
        <script src="{{asset('js/AdministracionReservas.js')}}"></script>
    @endsection
@endsection
