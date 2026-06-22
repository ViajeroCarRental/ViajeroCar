@extends('layouts.Ventas')

@section('Titulo', 'Visor de Reservaciones')

@section('css-vistaVisorReservaciones')
<link rel="stylesheet" href="{{ asset('css/visorReservaciones.css') }}">
@endsection

@section('contenidoVisorReservaciones')

<div class="visor-reservacion container">
    <div class="visor-panel visor-panel-wide">
        <div class="visor-card-header">
            <div class="visor-card-title">
                <h5>Visor de Reservaciones</h5>
                <span class="visor-pill">Resultados solo lectura</span>
            </div>
        </div>

        <div class="visor-card-body">

            <div class="toolbar visor-actions">
                <div class="visor-field-group">
                    <label>Mostrar</label>
                    <select id="pp" class="form-select">
                        <option>10</option>
                        <option selected>25</option>
                        <option>50</option>
                        <option>100</option>
                    </select>
                    <small>registros</small>
                </div>

                <div class="visor-field-group">
                    <label>Buscar</label>
                    <input type="text" id="q" class="form-control" placeholder="Folio, cliente, teléfono, vehículo…" />
                </div>
            </div>

            <div class="visor-table-wrap">
                <table class="visor-table" id="tbl">
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
            </div>

            <div class="visor-actions visor-confirm-wrap">
                <div class="visor-pill" id="range">0–0 de 0</div>

                <div>
                    <button class="btn btn-outline-primary" id="prev">‹</button>
                    <button class="btn btn-outline-primary" id="next">›</button>
                </div>
            </div>

        </div>
    </div>
</div>

@endsection

@section('js-vistaVisorReservaciones')
<script src="{{ asset('js/visorReservaciones.js') }}"></script>
@endsection
