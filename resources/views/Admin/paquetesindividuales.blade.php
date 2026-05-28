@extends('layouts.Admin')

@section('Titulo', 'Seguros Individuales')

@section('css-vistaRoles')
<link rel="stylesheet" href="{{ asset('css/paquetesindividuales.css') }}">
<style>
    /* 1. Forzar botones de acción en una sola fila (No escalón) */
    #tbodyIndividuales td:last-child {
        white-space: nowrap; /* Evita que se bajen de línea */
    }
    #tbodyIndividuales td:last-child button {
        display: inline-block;
        margin-right: 5px; /* Separación entre el botón amarillo y rojo */
    }

    /* 2. Estilos para el desglose de precios dinámico */
    .contenedor-desglose {
        max-height: 200px; 
        overflow-y: auto; 
        border: 1px solid #cbd5e1; 
        padding: 12px; 
        border-radius: 6px; 
        margin-bottom: 15px;
        background: #f8fafc;
        display: none; /* Oculto por defecto */
    }
    .fila-precio {
        display: flex; 
        justify-content: space-between; 
        align-items: center; 
        margin-bottom: 8px;
        font-size: 13px;
    }
    .input-precio {
        width: 100px; 
        text-align: right; 
        padding: 4px 8px; 
        border: 1px solid #cbd5e1; 
        border-radius: 4px;
        font-family: monospace;
    }
    
    /* 3. Acomodo del Select y el Botón rojo */
    .flex-input {
        display: flex;
        gap: 10px;
        align-items: stretch; 
        margin-bottom: 15px;
    }

    /* 🔥 Botón de Crear Sección en Color Rojo */
    .btn-add-seccion {
        background-color: #dc2626; /* Color Rojo */
        color: white;
        border: none;
        border-radius: 4px;
        padding: 0 15px;
        font-size: 14px;
        font-weight: bold;
        cursor: pointer;
        transition: background-color 0.2s ease, transform 0.1s ease;
        display: flex;
        align-items: center;
        justify-content: center;
        white-space: nowrap;
        box-shadow: 0 1px 3px rgba(0,0,0,0.1);
    }
    .btn-add-seccion:hover {
        background-color: #b91c1c; /* Rojo más oscuro al pasar el mouse */
    }
    .btn-add-seccion:active {
        transform: scale(0.96);
    }
</style>
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
                    <th>Sección</th>
                    <th>Precio Base</th>
                    <th>Activo</th>
                    <th style="width: 160px; text-align: center;">Acciones</th>
                </tr>
            </thead>
            <tbody id="tbodyIndividuales"></tbody>
        </table>
    </div>
</div>

<div class="modal" id="modalNuevo">
  <div class="modal-content">
    <h3>Nuevo Seguro Individual</h3>

    <label>Nombre</label>
    <input type="text" id="newNombre">

    <label>Descripción</label>
    <textarea id="newDescripcion"></textarea>

    <label>Sección a la que pertenece</label>
    <div class="flex-input">
        <select id="newSeccion" style="flex: 1; padding: 8px; border-radius: 4px; border: 1px solid #ccc;">
            <option value="">-- Selecciona --</option>
            @foreach($secciones as $sec)
                <option value="{{ $sec->id_seccion }}" data-desglose="{{ $sec->requiere_desglose_autos }}">
                    {{ $sec->nombre }}
                </option>
            @endforeach
        </select>
        <button type="button" class="btn-add-seccion" onclick="openModal('modalSeccion')" title="Crear nueva sección">+ Nueva Sección</button>
    </div>

    <div id="caja_precio_nuevo">
        <label>Precio general por día ($)</label>
        <input type="number" step="0.01" id="newPrecio" value="0.00">
    </div>

    <div id="caja_desglose_nuevo" class="contenedor-desglose">
        <label style="font-weight: bold; color: #1e3a8a; display: block; margin-bottom: 10px;">Desglose de precios por categoría ($)</label>
        @foreach($categorias as $cat)
            <div class="fila-precio">
                <span>🚗 <strong>{{ $cat->nombre }}</strong></span>
                <div>
                    $<input type="number" class="input-precio new-precio-auto" data-id="{{ $cat->id_categoria }}" min="0" value="0">
                </div>
            </div>
        @endforeach
    </div>

    <label style="margin-top:10px;"><input type="checkbox" id="newActivo" checked> Activo</label>

    <div class="modal-footer" style="margin-top:15px;">
        <button class="btn btn-secondary" onclick="closeModal('modalNuevo')">Cancelar</button>
        <button class="btn btn-primary" id="btnGuardarNuevo">Guardar</button>
    </div>
  </div>
</div>

<div class="modal" id="modalEditar">
  <div class="modal-content">
    <h3>Editar Seguro Individual</h3>
    <input type="hidden" id="editId">

    <label>Nombre</label>
    <input type="text" id="editNombre">

    <label>Descripción</label>
    <textarea id="editDescripcion"></textarea>

    <label>Sección a la que pertenece</label>
    <div class="flex-input">
        <select id="editSeccion" style="flex: 1; padding: 8px; border-radius: 4px; border: 1px solid #ccc;">
            <option value="">-- Selecciona --</option>
            @foreach($secciones as $sec)
                <option value="{{ $sec->id_seccion }}" data-desglose="{{ $sec->requiere_desglose_autos }}">
                    {{ $sec->nombre }}
                </option>
            @endforeach
        </select>
        <button type="button" class="btn-add-seccion" onclick="openModal('modalSeccion')" title="Crear nueva sección">+ Nueva Sección</button>
    </div>

    <div id="caja_precio_edit">
        <label>Precio general por día ($)</label>
        <input type="number" step="0.01" id="editPrecio">
    </div>

    <div id="caja_desglose_edit" class="contenedor-desglose">
        <label style="font-weight: bold; color: #1e3a8a; display: block; margin-bottom: 10px;">Desglose de precios por categoría ($)</label>
        @foreach($categorias as $cat)
            <div class="fila-precio">
                <span>🚗 <strong>{{ $cat->nombre }}</strong></span>
                <div>
                    $<input type="number" class="input-precio edit-precio-auto" data-id="{{ $cat->id_categoria }}" min="0" value="0">
                </div>
            </div>
        @endforeach
    </div>

    <label style="margin-top:10px;"><input type="checkbox" id="editActivo"> Activo</label>

    <div class="modal-footer" style="margin-top:15px;">
        <button onclick="closeModal('modalEditar')" class="btn btn-secondary">Cancelar</button>
        <button id="btnGuardarEdit" class="btn btn-primary">Actualizar</button>
    </div>
  </div>
</div>

<div class="modal" id="modalSeccion" style="z-index: 9999; background: rgba(0,0,0,0.6);">
  <div class="modal-content" style="max-width: 400px;">
    <h3>Nueva Sección</h3>
    
    <label>Nombre de la Sección</label>
    <input type="text" id="secNombre" placeholder="Ej: Gastos Médicos">

    <label style="margin-top: 15px; display: block; font-size: 13px; color: #b91c1c;">
      <input type="checkbox" id="secDesglose"> 
      <strong>Esta sección requiere pedir precios por cada vehículo (Ej: Robo y Colisión)</strong>
    </label>

    <div class="modal-footer" style="margin-top: 20px;">
        <button onclick="closeModal('modalSeccion')" class="btn btn-secondary">Cerrar</button>
        <button id="btnGuardarSeccion" class="btn btn-danger">Guardar Sección</button>
    </div>
  </div>
</div>

@endsection

@section('js-vistaRoles')
<script src="{{ asset('js/segurosIndividuales.js') }}"></script>
@endsection