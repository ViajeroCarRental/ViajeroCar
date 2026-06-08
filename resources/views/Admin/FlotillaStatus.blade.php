@extends('layouts.Ventas')

@section('Titulo', 'Estado de Flotilla')

@section('css')
    <link rel="stylesheet" href="{{ asset('css/FlotillaStatus.css') }}">
@endsection

@section('contenido')
<meta name="csrf-token" content="{{ csrf_token() }}">
<main class="flotilla-container" data-list-url="{{ url('/ventas/flotilla-status/list') }}">

    <h1>Status de Flotilla</h1>

    <div class="table-wrap">
        <table class="flotilla-table">
            <thead>
                <tr>
                    <th>Estatus</th>
                    <th>Placas</th>
                    <th>Categoría</th>
                    <th>Tamaño</th>
                    <th>Modelo</th>
                    <th>Transmisión</th>
                    <th>Color</th>
                    <th>Gasolina</th>
                    <th>Litros</th>
                    <th>KM</th>
                    <th>Verificación</th>
                    <th>Mantenimiento</th>
                    <th>Seguro</th>
                    {{-- <th>Acciones</th> --}}
                </tr>
            </thead>
            <tbody id="tbodyFlotilla">
                <tr><td colspan="13" class="empty-row">Cargando...</td></tr>
            </tbody>
        </table>
    </div>

    <!-- ===== MODAL CAMBIAR ESTATUS ===== -->
<div class="modal-overlay" id="modalEstatus" style="display:none;">
    <div class="modal-card">

        <header class="modal-head">
            <h3>Cambiar estatus del vehículo</h3>
            <button type="button" class="modal-close" id="modalClose">✕</button>
        </header>

        <div class="modal-body">
            <div class="info-row"><span>Placas:</span> <strong id="infoPlaca">—</strong></div>
            <div class="info-row"><span>Categoría:</span> <strong id="infoCategoria">—</strong></div>
            <div class="info-row"><span>Modelo:</span> <strong id="infoModelo">—</strong></div>

            <label class="label">Nuevo estatus</label>
            <select id="selectEstatus" class="select-estatus">
                @foreach($estatusList as $est)
                    <option value="{{ $est->id_estatus }}">{{ $est->nombre }}</option>
                @endforeach
            </select>

            <input type="hidden" id="vehiculoId">
        </div>

        <footer class="modal-actions">
            <button type="button" class="btn-secondary" id="modalCancel">Cancelar</button>
            <button type="button" class="btn-primary" id="btnGuardarEstatus">Guardar</button>
        </footer>

    </div>
</div>

</main>
@endsection

@section('js')
    <script src="{{ asset('js/flotillaStatus.js') }}"></script>
@endsection
