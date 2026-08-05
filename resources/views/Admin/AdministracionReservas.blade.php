@extends('layouts.Ventas')

@section('Titulo', 'Administración De Reservas')

@section('css-vistaAdministracionReservaciones')
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Roboto+Condensed:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/AdministracionReservas.css') }}?v={{ @filemtime(public_path('css/AdministracionReservas.css')) ?: time() }}">
@endsection

@section('contenidoAdministracionReservaciones')
<main class="main">
    <div class="page">
        <h1 class="h1">Contratos Abiertos</h1>

        <div class="topbar">
            <div class="search">
                <input id="txtSearch" class="input" type="search"
                    placeholder="Buscar: contrato, reservación o nombre…">
            </div>

            <label class="date-filter">
                Cierre previsto
                <input id="filtroFechaCierre" class="input" type="date">
            </label>

            <label class="records-control">
                Mostrar
                <select id="selSize" class="select">
                    <option>10</option>
                    <option>25</option>
                    <option>50</option>
                    <option>100</option>
                </select>
                registros
            </label>
        </div>

        <div class="table-wrap">
            <table class="table" id="tbl">
                <thead>
                            <tr>
                                <th class="col-toggle"></th>
                                <th>Fecha Checkout</th>
                                <th>Hora Checkout</th>
                                <th>No. Contrato</th>
                                <th>Drop off</th>
                                <th>Usuario</th>
                                <th>Tarifa diaria</th>
                                <th>Días de renta</th>
                                <th>Total de la renta</th>
                            </tr>
                </thead>
                <tbody id="tbody"></tbody>
            </table>
        </div>

        <div class="pager">
            <button class="pbtn" id="prev">« Anterior</button>
            <div id="pgInfo" class="small"></div>
            <button class="pbtn" id="next">Siguiente »</button>
        </div>
    </div>
</main>

<div id="modalFinalizar" class="modal-fin" style="display:none;">
    <div class="modal-fin-box">
        <h2 id="mf_titulo">Finalizar contrato</h2>
        <p id="mf_msg">Mensaje aquí…</p>
        <div class="mf-btns">
            <button id="mf_cancel" class="btn b-gray">Cancelar</button>
            <button id="mf_ok" class="btn b-primary">Aceptar</button>
        </div>
    </div>
</div>

<script>
    const esSuperAdmin = @json($soloSuperAdmin);
</script>
@endsection

@section('js-vistaAdministracionReservaciones')
    <script src="{{ asset('js/AdministracionReservas.js') }}?v={{ @filemtime(public_path('js/AdministracionReservas.js')) ?: time() }}"></script>
@endsection
