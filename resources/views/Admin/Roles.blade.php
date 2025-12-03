@extends('layouts.admin')

@section('content')

<meta name="csrf-token" content="{{ csrf_token() }}">

<div class="container mt-4">

    <div class="d-flex justify-content-between mb-3">
        <h3>Roles y permisos</h3>
        <button id="btnNuevo" class="btn btn-danger">+ Nuevo rol</button>
    </div>

    <table class="table table-bordered shadow-sm">
        <thead class="table-danger">
            <tr>
                <th>Rol</th>
                <th>Descripción</th>
                <th>Usuarios</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody id="rolesBody"></tbody>
    </table>
</div>

{{-- MODAL --}}
<div class="pop" id="rolesPop">
    <div class="box">

        <header>
            <h4 id="tituloModal">Nuevo rol</h4>
            <button id="closePop" class="btn-close"></button>
        </header>

        <div>
            <label>Nombre del rol</label>
            <input class="input" id="rolNombre" placeholder="Ej. Operador">

            <label class="mt-3">Descripción</label>
            <input class="input" id="rolDesc" placeholder="Descripción corta">

            <div class="mt-4">
                <h5>Permisos</h5>
                <div id="permBox"></div>
            </div>

            <div class="mt-4" id="usuariosAsignados" style="display:none">
                <h5>Usuarios con este rol</h5>
                <ul id="listaUsuarios"></ul>
            </div>
        </div>

        <footer>
            <button id="btnCancelar" class="btn btn-secondary">Cancelar</button>
            <button id="btnGuardar" class="btn btn-danger">Guardar rol</button>
        </footer>

    </div>
</div>

<script src="{{ asset('js/roles.js') }}"></script>

@endsection
