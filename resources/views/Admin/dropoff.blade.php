@extends('layouts.Admin')

@section('Titulo', 'Dropoff')

@section('css-vistaRoles')
<link rel="stylesheet" href="{{ asset('css/roles.css') }}">
@endsection

@section('contenidoDropoff')

<div class="roles-container">
    <h3>Dropoff por Categoría</h3>

    <div class="table-wrap">
        <table class="table">
            <thead>
                <tr>
                    <th></th>
                    <th>Código</th>
                    <th>Categoría</th>
                    <th>Costo KM</th>
                    <th>Estado</th>
                </tr>
            </thead>
            <tbody id="tbodyDropoff"></tbody>
        </table>
    </div>
</div>

@endsection

@section('js-vistaDropoff')
<script src="{{ asset('js/dropoff.js') }}"></script>
@endsection
