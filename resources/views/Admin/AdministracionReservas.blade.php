@extends('layouts.Ventas')

@section('Titulo', 'Administración De Reservas')

@section('css-vistaAdministracionReservaciones')
<link rel="stylesheet" href="{{ asset('css/AdministracionReservas.css') }}">
@endsection

@section('contenidoAdministracionReservaciones')
<main class="main">
    <div class="page">

        <h1 class="h1">Contratos Abiertos</h1>

        <!-- =====================
             TOP BAR (BUSQUEDA)
        ====================== -->
        <div class="topbar">

            <label>Mostrar
                <select id="selSize" class="select">
                    <option>10</option>
                    <option>25</option>
                    <option>50</option>
                    <option>100</option>
                </select>
                registros
            </label>

            <div class="search">
                <input id="txtSearch" class="input" type="search"
                    placeholder="Buscar: clave, nombre, email, estado…">
            </div>

        </div>

        <!-- =====================
                TABLA PRINCIPAL
        ====================== -->
        <table class="table" id="tbl">
            <thead>
    <tr>
        <th style="width:40px"></th>
        <th>No. Contrato</th>
        <th>Fecha Checkout</th>
        <th>Hora Checkout</th>
        <th>Nombre</th>
        <th>Apellidos</th>
        <th>Email</th>
        <th>Estatus</th>
        <th class="col-actions"></th>
    </tr>
</thead>



            <tbody id="tbody"></tbody>
        </table>

        <!-- =====================
                 PAGINACION
        ====================== -->
        <div class="pager">
            <button class="pbtn" id="prev">« Anterior</button>
            <div id="pgInfo" class="small"></div>
            <button class="pbtn" id="next">Siguiente »</button>
        </div>

    </div>
</main>
<!-- MODAL — CONFIRMAR FINALIZAR -->
<div id="modalFinalizar" class="modal-fin" style="display:none;">
    <div class="modal-fin-box">
        <h2 id="mf_titulo">Finalizar contrato</h2>
        <p id="mf_msg">Mensaje aquí…</p>

        <div class="mf-btns">
            <button id="mf_cancel" class="btn gray">Cancelar</button>
            <button id="mf_ok" class="btn b-primary">Aceptar</button>
        </div>
    </div>
</div>

<style>
.modal-fin {
    position: fixed;
    inset: 0;
    background: rgba(0,0,0,.45);
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 99999;
}
.modal-fin-box {
    background: white;
    padding: 25px;
    border-radius: 12px;
    width: 370px;
    text-align: center;
    box-shadow: 0 10px 25px rgba(0,0,0,.2);
}
.mf-btns {
    margin-top: 20px;
    display: flex;
    justify-content: space-evenly;
}
</style>

@endsection

@section('js-vistaAdministracionReservaciones')
<script src="{{ asset('js/AdministracionReservas.js') }}"></script>
@endsection
