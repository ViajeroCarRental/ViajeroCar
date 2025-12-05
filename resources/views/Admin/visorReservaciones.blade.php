@extends('layouts.Ventas')

@section('Titulo', 'Visor de Reservaciones')

@section('css-vistaVisorReservaciones')
<link rel="stylesheet" href="{{ asset('css/visorReservaciones.css') }}">
@endsection

@section('contenidoVisorReservaciones')

<main class="main">
    <h1 class="h1">Visor de Reservaciones</h1>

    <section class="section">
        <div class="head">Resultados (Solo lectura)</div>
        <div class="cnt">

            <!-- ================= Toolbar ================= -->
            <div class="toolbar">
                <div class="controls">
                    <label>Mostrar
                        <select id="pp">
                            <option>10</option>
                            <option selected>25</option>
                            <option>50</option>
                            <option>100</option>
                        </select>
                        registros
                    </label>
                </div>

                <div class="controls">
                    <label>Buscar:
                        <input type="text" id="q" placeholder="Folio, cliente, teléfono, vehículo…" />
                    </label>
                </div>
            </div>

            <!-- ================= Tabla ================= -->
            <table class="table" id="tbl">
                <thead>
                    <tr>
                        <th style="width:58px"></th>
                        <th>Clave Reservación</th>
                        <th>Fecha Checkout</th>
                        <th>Horario</th>
                        <th>Días</th>
                        <th>Categoría</th>
                        <th>Nombre Completo</th>
                        <th>Número Teléfono</th>
                    </tr>
                </thead>

                <tbody id="tbody">
                    <tr>
                        <td colspan="8" style="text-align:center;color:#667085">Cargando…</td>
                    </tr>
                </tbody>
            </table>

            <!-- ================= Paginador ================= -->
            <div class="pager">
                <div class="range" id="range">0–0 de 0</div>

                <div>
                    <button class="btn" id="prev">‹</button>
                    <button class="btn" id="next">›</button>
                </div>
            </div>

        </div>
    </section>
</main>

@endsection

@section('js-vistaVisorReservaciones')
<script src="{{ asset('js/visorReservaciones.js') }}"></script>
@endsection
