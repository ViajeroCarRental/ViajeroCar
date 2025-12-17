@extends('layouts.Admin')

@section('Titulo', 'Seguros Individuales')

@section('css-vistaRoles')
<link rel="stylesheet" href="{{ asset('css/paquetesindividuales.css') }}">
@endsection

@section('contenidoRoles')

<div class="roles-container">
    <div class="header-flex">
        <h3>Seguros Individuales</h3>
        <button id="btnNuevo" class="btn btn-danger shadow-sm">+ Nuevo seguro</button>
    </div>

    <div class="table-wrap">
        <table class="table roles-table">
            <thead>
                <tr>
                    <th>Nombre</th>
                    <th>Precio x Día</th>
                    <th>Activo</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody id="tbodyIndividuales"></tbody>
        </table>
    </div>
</div>

<!-- ============================
     MODAL NUEVO
============================ -->
<div class="modal" id="modalNuevo">
  <div class="modal-content">

    <h3>Nuevo Seguro Individual</h3>

    <label>Nombre</label>
    <input type="text" id="newNombre">

    <label>Descripción</label>
    <textarea id="newDescripcion"></textarea>

    <label>Precio por día</label>
    <input type="number" step="0.01" id="newPrecio">

    <label><input type="checkbox" id="newActivo" checked> Activo</label>

    <div class="modal-footer">
        <button class="btn btn-secondary" onclick="closeModal('modalNuevo')">Cancelar</button>
        <button class="btn btn-primary" id="btnGuardarNuevo">Guardar</button>
    </div>

  </div>
</div>

<!-- ============================
     MODAL EDITAR
============================ -->
<div class="modal" id="modalEditar">
  <div class="modal-content">

    <h3>Editar Seguro Individual</h3>

    <input type="hidden" id="editId">

    <label>Nombre</label>
    <input type="text" id="editNombre">

    <label>Descripción</label>
    <textarea id="editDescripcion"></textarea>

    <label>Precio por día</label>
    <input type="number" step="0.01" id="editPrecio">

    <label><input type="checkbox" id="editActivo"> Activo</label>

    <div class="modal-footer">
        <button onclick="closeModal('modalEditar')" class="btn btn-secondary">Cancelar</button>
        <button id="btnGuardarEdit" class="btn btn-primary">Actualizar</button>
    </div>

  </div>
</div>

@endsection

@section('js-vistaRoles')
<script src="{{ asset('js/segurosIndividuales.js') }}"></script>
@endsection
