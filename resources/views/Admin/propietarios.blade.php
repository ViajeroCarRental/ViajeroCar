@extends('layouts.Admin')

@section('Titulo', 'Propietarios')

@section('css-vistaRoles')
<link rel="stylesheet" href="{{ asset('css/paqueteseguro.css') }}">
@endsection

@section('contenidoRoles')

<div class="roles-container">
    <div class="header-flex">
        <h3>Propietarios de Vehículos</h3>
        <!--<button id="btnNuevo" class="btn btn-danger shadow-sm">+ Nuevo propietario</button>-->
    </div>

    <div class="table-wrap">
        <table class="table roles-table">

            <thead>
                <tr>
                    <th>Marca</th>
                    <th>Modelo</th>
                    <th>Placa</th>
                    <th>Propietario</th>
                    <th>Firma</th>
                    <th>Acciones</th>
                </tr>
            </thead>

            <tbody id="tbodyPropietarios"></tbody>

        </table>
    </div>

</div>


<!-- ================= MODAL CREAR ================= -->

<div class="modal" id="modalNuevo">

<div class="modal-content">

<h3>Nuevo Propietario</h3>

<label>ID Vehículo</label>
<input type="number" id="newVehiculo" readonly>

<label>Nombre del propietario</label>
<input type="text" id="newNombre">

<label>Firma del propietario</label>

<canvas id="padFirmaNuevo" width="400" height="200"
style="border:1px solid #ccc;background:white;"></canvas>
<button type="button" id="clearFirmaNuevo" class="btn btn-secondary">
Limpiar firma
</button>
<input type="hidden" id="newFirma">

<button id="btnGuardarNuevo" class="btn btn-primary">Guardar</button>

<button onclick="closeModal('modalNuevo')" class="btn btn-secondary">Cancelar</button>

</div>
</div>
</div>



<!-- ================= MODAL EDITAR ================= -->

<div class="modal" id="modalEditar">

<div class="modal-content">

<h3>Editar Propietario</h3>

<input type="hidden" id="editId">

<label>Nombre</label>
<input type="text" id="editNombre">

<label>Firma del propietario</label>

<canvas id="padFirmaEditar" width="400" height="200"
style="border:1px solid #ccc;background:white;"></canvas>

<button type="button" id="clearFirmaEditar" class="btn btn-secondary">
Limpiar firma
</button>
<div class="modal-footer">

<button onclick="closeModal('modalEditar')" class="btn btn-secondary">
Cancelar
</button>

<button id="btnGuardarEdit" class="btn btn-primary">
Actualizar
</button>

</div>

</div>

</div>

@endsection



@section('js-vistaRoles')
<script src="https://cdn.jsdelivr.net/npm/signature_pad@2.3.2/dist/signature_pad.min.js"></script>
<script src="{{ asset('js/propietariosAdmin.js') }}"></script>

@endsection
