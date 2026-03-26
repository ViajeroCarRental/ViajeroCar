@extends('layouts.Flotillas')

@section('Titulo', 'Documentos Vehículos')

@section('css-vistaFlotilla')
<link rel="stylesheet" href="{{ asset('css/flotilla.css') }}">
@endsection


@section('contenidoDocumentos')

<div class="roles-container">

    <div class="header-flex">
        <h3>Documentos de Vehículos</h3>
    </div>

    <div class="table-wrap">
        <table class="table roles-table">
            <thead>
                <tr>
                    <th>Marca</th>
                    <th>Modelo</th>
                    <th>Carta Factura</th>
                    <th>Póliza</th>
                    <th>Verificación</th>
                    <th>Tarjeta Circulación</th>
                </tr>
            </thead>
            <tbody id="tbodyDocs"></tbody>
        </table>
    </div>

</div>


<!-- ================= MODAL ================= -->
<div class="modal" id="modalArchivo">
    <div class="modal-content">

        <h3>Documento</h3>

        <!-- IDs ocultos -->
        <input type="hidden" id="docVehiculo">
        <input type="hidden" id="docTipo">

        <!-- PREVIEW -->
        <div id="previewContainer" style="margin-bottom:15px;"></div>

        <!-- INPUT FILE -->
        <div>
            <label>Seleccionar archivo</label>
            <input type="file" id="inputArchivo" accept=".pdf,.jpg,.jpeg,.png">
        </div>

        <!-- BOTONES -->
        <div style="margin-top:15px; display:flex; gap:10px;">
            <button id="btnGuardarArchivo" class="btn btn-primary">
                Guardar
            </button>

            <button onclick="closeModal('modalArchivo')" class="btn btn-secondary">
                Cancelar
            </button>
        </div>

    </div>
</div>


@endsection


@section('js-vistaDocumentacion')
<script src="{{ asset('js/vehiculosDocumentos.js') }}"></script>
@endsection
