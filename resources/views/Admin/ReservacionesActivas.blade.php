@extends('layouts.Ventas')
@section('Titulo', 'Reservaciones Activas')
    @section('css-vistaReservacionesActivas')
        <link rel="stylesheet" href="{{ asset('css/reservacionesActivas.css') }}">
    @endsection
@section('contenidoReservacionesActivas')
        <main class="main">
    <h1 class="h1">Reservaciones activas</h1>

    <div class="toolbar">
      <input id="q" class="input" type="search" placeholder="Buscar por ID, cliente o email…">
      <span class="badge gray">Total <b id="count">0</b></span>
      <span class="badge ok">Confirmadas / En contrato <b id="countConf">0</b></span>
      <span class="badge warn">Borradores <b id="countBorr">0</b></span>
    </div>

    <section class="table">
      <div class="thead">
        <div>ID</div><div>Fecha</div><div>Nombre Cliente</div><div>Email</div><div>Estado</div><div>Total</div><div>Acciones</div>
      </div>
      <div id="tbody"></div>
    </section>
  </main>
</div>

<!-- Modal -->
<div class="pop" id="modal">
  <div class="box">
    <header>
      <div id="mTitle">Contrato Reservación</div>
      <button class="btn gray" id="mClose">✖</button>
    </header>
    <div class="cnt" id="mBody"></div>
    <div class="actions">
      <button class="btn danger" id="mDel">Eliminar reservación</button>
      <span style="flex:1"></span>
      <button class="btn gray" id="mCancel">Cerrar</button>
      <button class="btn primary" id="mGo">CAPTURAR CONTRATO</button>
    </div>
  </div>
</div>

        @section('js-vistaReservacionesActivas')
            <link rel="stylesheet" href="{{ asset('js/reservacionesActivas.js') }}">
        @endsection
@endsection

