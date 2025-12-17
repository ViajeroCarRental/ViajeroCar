@extends('layouts.admin')

@section('Titulo', 'Seguros')

@section('css-vistaRoles')
<link rel="stylesheet" href="{{ asset('css/paqueteseguro.css') }}">
@endsection

@section('contenidoRoles')

<div class="roles-container">
    <div class="header-flex">
        <h3>Paquetes de Seguros</h3>
        <button id="btnNuevo" class="btn btn-danger shadow-sm">+ Nuevo paquete</button>
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
            <tbody id="tbodySeguros"></tbody>
        </table>
    </div>
</div>

<!-- ================= MODAL CREAR ================= -->
<div class="modal" id="modalNuevo">
  <div class="modal-content">
    <h3>Nuevo Paquete</h3>

    <label>Nombre</label>
    <input type="text" id="newNombre">

    <label>Descripción</label>
    <textarea id="newDescripcion"></textarea>

    <label>Precio por día</label>
    <input type="number" step="0.01" id="newPrecio">

    <label>
      <input type="checkbox" id="newActivo" checked> Activo
    </label>

    <button id="btnGuardarNuevo" class="btn btn-primary">Guardar</button>
    <button onclick="closeModal('modalNuevo')" class="btn btn-secondary">Cancelar</button>
  </div>
</div>

<!-- ================= MODAL EDITAR ================= -->
<div class="modal" id="modalEditar">
  <div class="modal-content">

    <h3>Editar Paquete</h3>

    <input type="hidden" id="editId">

    <label>Nombre</label>
    <input type="text" id="editNombre">

    <label>Descripción</label>
    <textarea id="editDescripcion"></textarea>

    <label>Precio por día</label>
    <input type="number" step="0.01" id="editPrecio">

    <label style="margin-bottom:15px;">
      <input type="checkbox" id="editActivo"> Activo
    </label>

    <div class="modal-footer">
        <button onclick="closeModal('modalEditar')" class="btn btn-secondary">Cancelar</button>
        <button id="btnGuardarEdit" class="btn btn-primary">Actualizar</button>
    </div>

  </div>
</div>


@endsection

@section('js-vistaRoles')
<script src="{{ asset('js/segurosAdmin.js') }}"></script>
@endsection
