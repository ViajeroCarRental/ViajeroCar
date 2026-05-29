@extends('layouts.Admin')

@section('Titulo', 'Seguros Individuales')

@section('css-vistaRoles')
    <link rel="stylesheet" href="{{ asset('css/paquetesindividuales.css') }}">
@endsection

@section('contenidoRoles')

    <div class="roles-container">
        <div class="header-flex">
            <h3>Seguros Individuales</h3>
            <div style="display: flex; gap: 10px;">
                <button id="btnGestionSecciones" class="btn btn-warning shadow-sm btn-header-action" style="color: #452c00;">⚙️
                    Secciones</button>
                <button id="btnNuevo" class="btn btn-danger shadow-sm btn-header-action">+ Nuevo seguro</button>
            </div>
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
                    @foreach ($secciones as $sec)
                        <option value="{{ $sec->id_seccion }}" data-desglose="{{ $sec->requiere_desglose_autos }}">
                            {{ $sec->nombre }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div id="caja_precio_nuevo">
                <label>Precio general por día ($)</label>
                <input type="number" step="0.01" id="newPrecio" value="0.00">
            </div>

            <div id="caja_desglose_nuevo" class="contenedor-desglose">
                <label style="font-weight: bold; color: #1e3a8a; display: block; margin-bottom: 10px;">Desglose de precios
                    por categoría ($)</label>
                @foreach ($categorias as $cat)
                    <div class="fila-precio">
                        <span>🚗 <strong>{{ $cat->nombre }}</strong></span>
                        <div>
                            $<input type="number" class="input-precio new-precio-auto" data-id="{{ $cat->id_categoria }}"
                                min="0" value="0">
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
                    @foreach ($secciones as $sec)
                        <option value="{{ $sec->id_seccion }}" data-desglose="{{ $sec->requiere_desglose_autos }}">
                            {{ $sec->nombre }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div id="caja_precio_edit">
                <label>Precio general por día ($)</label>
                <input type="number" step="0.01" id="editPrecio">
            </div>

            <div id="caja_desglose_edit" class="contenedor-desglose">
                <label style="font-weight: bold; color: #1e3a8a; display: block; margin-bottom: 10px;">Desglose de precios
                    por categoría ($)</label>
                @foreach ($categorias as $cat)
                    <div class="fila-precio">
                        <span>🚗 <strong>{{ $cat->nombre }}</strong></span>
                        <div>
                            $<input type="number" class="input-precio edit-precio-auto" data-id="{{ $cat->id_categoria }}"
                                min="0" value="0">
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

    {{-- Modal de gestión de Secciones --}}
    <div class="modal" id="modalSeccion" style="z-index: 9999; background: rgba(0,0,0,0.6);">
        <div class="modal-content" style="max-width: 850px; width: 95%;">
            <h3 id="tituloModalSeccion" class="modal-title-custom">Gestión de Secciones</h3>

            <input type="hidden" id="secId">

            <div class="modal-body-grid">

                {{-- COLUMNA 1: FORMULARIO --}}
                <div class="form-seccion-container">
                    <label class="form-label-custom">Nombre de la Sección</label>
                    <input type="text" id="secNombre" class="form-input-custom" placeholder="Ej: Gastos Médicos">

                    <label class="form-checkbox-custom">
                        <input type="checkbox" id="secDesglose">
                        <strong>Requiere precio por vehículo (Ej: Robo)</strong>
                    </label>

                    <div style="margin-top: 20px; display: flex; justify-content: flex-end; gap: 10px;">
                        <button id="btnCancelarEdicionSec" class="btn btn-secondary"
                            style="display: none;">Cancelar</button>
                        <button id="btnGuardarSeccion" class="btn btn-danger">Guardar Sección</button>
                    </div>
                </div>
                
                <div>
                    <label class="form-label-custom">Secciones existentes</label>
                    <div id="listaSecciones" style="max-height: 280px;">
                        {{-- Se llena desde el JS --}}
                    </div>
                </div>

            </div>

            {{-- FOOTER DEL MODAL --}}
            <div style="margin-top: 20px; text-align: right; border-top: 1px solid #e2e8f0; padding-top: 15px;">
                <button onclick="closeModal('modalSeccion')" class="btn btn-secondary">Cerrar Ventana</button>
            </div>
        </div>
    </div>

@endsection

@section('js-vistaRoles')
    <script src="{{ asset('js/segurosIndividuales.js') }}"></script>
@endsection
