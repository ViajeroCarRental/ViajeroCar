@extends('layouts.Admin')

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
                        <th>Nombre del Paquete</th>
                        <th>Precio x Día</th>
                        <th>Deducible Colisión</th>
                        <th>Deducible Robo</th>
                        <th>Activo</th>
                        <th style="width: 160px;">Acciones</th>
                    </tr>
                </thead>
                <tbody id="tbodySeguros">
                    {{-- Se llena desde el JS --}}
                </tbody>
            </table>
        </div>
    </div>

    {{-- MODAL DE NUEVO SEGURO --}}
    <div class="modal" id="modalNuevo">
        <div class="modal-content">
            <button class="modal-close-btn" onclick="closeModal('modalNuevo')">&times;</button>
            <h3 style="margin-bottom: 20px; color: #1e3a8a; border-bottom: 2px solid #e2e8f0; padding-bottom: 10px;">
                ➕ Nuevo Paquete de Seguros
            </h3>

            <div class="modal-layout">
                <div class="form-grid">
                    <div>
                        <label>Nombre del Paquete</label>
                        <input type="text" id="newNombre" placeholder="Ej: Paquete Básico">
                    </div>

                    <div>
                        <label>Precio por día</label>
                        <input type="text" id="newPrecio" value="$0.00">
                    </div>

                    <div>
                        <label>Descripción</label>
                        <textarea id="newDescripcion" rows="3" placeholder="Se llena sola al marcar protecciones. Puedes editarla."></textarea>
                        <small style="display:block; color:#64748b; font-size:11px; margin-top:4px;">
                            💡 Para poner texto en <strong>negritas</strong>, enciérralo entre dobles asteriscos. Ejemplo: <code>**texto importante**</code>
                        </small>
                    </div>

                    <div style="display: flex; gap: 10px;">
                        <div style="flex: 1;">
                            <label>Deducible Colisión</label>
                            <input type="text" id="newDeducibleColision" value="0.00 %">
                        </div>
                        <div style="flex: 1;">
                            <label>Deducible Robo</label>
                            <input type="text" id="newDeducibleRobo" value="0.00 %">
                        </div>
                    </div>

                    <div style="display: flex; align-items: center; justify-content: space-between; gap: 10px;">
                        <div style="padding-top: 25px;">
                            <label style="cursor: pointer; font-weight: bold;">
                                <input type="checkbox" id="newActivo" checked> Paquete Activo
                            </label>
                        </div>
                    </div>
                </div>

                <div class="grid-columnas-derecha">
                    {{-- Columna de Montos por Categoría --}}
                    <div>
                        <label style="font-weight: bold; color: #1e3a8a; display: block; margin-bottom: 5px;">
                            📊 Monto de Garantía por Categoría
                        </label>
                        <div class="contenedor-porcentajes">
                            @foreach ($categorias as $cat)
                                <div class="fila-porcentaje">
                                    <span>🚗 <strong>{{ $cat->nombre }}</strong></span>
                                    <div style="display: flex; align-items: center; gap: 5px;">
                                        <input type="text" class="input-pct new-monto"
                                            data-id="{{ $cat->id_categoria }}" value="$0.00">
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    {{-- Columna de Protecciones (AGRUPADAS POR SECCIÓN) --}}
                    <div>
                        <label style="font-weight: bold; color: #1e3a8a; display: block; margin-bottom: 5px;">
                            🛡️ Protecciones Incluidas
                        </label>
                        <div class="contenedor-protecciones">
                            @if (isset($proteccionesPorSeccion))
                                @foreach ($secciones as $idSeccion => $nombreSeccion)
                                    <div class="seccion-proteccion" style="margin-bottom:12px;">
                                        <div style="font-weight:bold; font-size:12px; color:#dc2626; border-left:3px solid #dc2626; padding-left:6px; margin-bottom:6px;">
                                            {{ $nombreSeccion }}
                                        </div>
                                        @foreach ($proteccionesPorSeccion[$idSeccion] as $prot)
                                            <label style="display: flex; align-items: center; gap: 8px; font-size: 13px; cursor: pointer; background: white; padding: 5px; border-radius: 4px; border: 1px solid #e2e8f0; margin-bottom:4px;">
                                                <input type="checkbox" class="new-prot"
                                                    value="{{ $prot->id_individual }}"
                                                    data-precio="{{ $prot->precio_por_dia }}"
                                                    data-desc="{{ $prot->descripcion }}"
                                                    data-suma="{{ $prot->requiere_desglose_autos ? 0 : 1 }}">
                                                {{ $prot->nombre }}
                                            </label>
                                        @endforeach
                                    </div>
                                @endforeach
                            @endif
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer" style="margin-top:20px; border-top: 1px solid #e2e8f0; padding-top: 15px; text-align: right;">
                <button onclick="closeModal('modalNuevo')" class="btn btn-secondary">Cancelar</button>
                <button id="btnGuardarNuevo" class="btn btn-primary">Guardar Paquete</button>
            </div>
        </div>
    </div>


    {{-- MODAL DE EDITAR SEGURO --}}
    <div class="modal" id="modalEditar">
        <div class="modal-content">
            <button class="modal-close-btn" onclick="closeModal('modalEditar')">&times;</button>
            <h3 style="margin-bottom: 20px; color: #1e3a8a; border-bottom: 2px solid #e2e8f0; padding-bottom: 10px;">
                ✏️ Editar Paquete
            </h3>
            <input type="hidden" id="editId">

            <div class="modal-layout">
                <div class="form-grid">
                    <div>
                        <label>Nombre del Paquete</label>
                        <input type="text" id="editNombre">
                    </div>

                    <div>
                        <label>Precio por día</label>
                        <input type="text" id="editPrecio">
                    </div>

                    <div>
                        <label>Descripción</label>
                        <textarea id="editDescripcion" rows="3"></textarea>
                        <small style="display:block; color:#64748b; font-size:11px; margin-top:4px;">
                            💡 Para poner texto en <strong>negritas</strong>, enciérralo entre dobles asteriscos. Ejemplo: <code>**texto importante**</code>
                        </small>
                    </div>

                    <div style="display: flex; gap: 10px;">
                        <div style="flex: 1;">
                            <label>Deducible Colisión</label>
                            <input type="text" id="editDeducibleColision">
                        </div>
                        <div style="flex: 1;">
                            <label>Deducible Robo</label>
                            <input type="text" id="editDeducibleRobo">
                        </div>
                    </div>

                    <div style="display: flex; align-items: center; justify-content: space-between; gap: 10px;">
                        <div style="padding-top: 25px;">
                            <label style="cursor: pointer; font-weight: bold;">
                                <input type="checkbox" id="editActivo"> Paquete Activo
                            </label>
                        </div>
                    </div>
                </div>

                <div class="grid-columnas-derecha">
                    {{-- Montos de Garantía por Categoría --}}
                    <div>
                        <label style="font-weight: bold; color: #1e3a8a; display: block; margin-bottom: 5px;">
                            📊 Monto de Garantía por Categoría
                        </label>
                        <div class="contenedor-porcentajes">
                            @foreach ($categorias as $cat)
                                <div class="fila-porcentaje">
                                    <span>🚗 <strong>{{ $cat->nombre }}</strong></span>
                                    <div style="display: flex; align-items: center; gap: 5px;">
                                        <input type="text" class="input-pct edit-monto"
                                            data-id="{{ $cat->id_categoria }}" value="$0.00">
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    {{-- Protecciones Incluidas (AGRUPADAS POR SECCIÓN) --}}
                    <div>
                        <label style="font-weight: bold; color: #1e3a8a; display: block; margin-bottom: 5px;">
                            🛡️ Protecciones Incluidas
                        </label>
                        <div class="contenedor-protecciones">
                            @if (isset($proteccionesPorSeccion))
                                @foreach ($secciones as $idSeccion => $nombreSeccion)
                                    <div class="seccion-proteccion" style="margin-bottom:12px;">
                                        <div style="font-weight:bold; font-size:12px; color:#dc2626; border-left:3px solid #dc2626; padding-left:6px; margin-bottom:6px;">
                                            {{ $nombreSeccion }}
                                        </div>
                                        @foreach ($proteccionesPorSeccion[$idSeccion] as $prot)
                                            <label style="display: flex; align-items: center; gap: 8px; font-size: 13px; cursor: pointer; background: white; padding: 5px; border-radius: 4px; border: 1px solid #e2e8f0; margin-bottom:4px;">
                                                <input type="checkbox" class="edit-prot"
                                                    value="{{ $prot->id_individual }}"
                                                    data-precio="{{ $prot->precio_por_dia }}"
                                                    data-desc="{{ $prot->descripcion }}"
                                                    data-suma="{{ $prot->requiere_desglose_autos ? 0 : 1 }}">
                                                {{ $prot->nombre }}
                                            </label>
                                        @endforeach
                                    </div>
                                @endforeach
                            @endif
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer" style="margin-top:20px; border-top: 1px solid #e2e8f0; padding-top: 15px; text-align: right;">
                <button onclick="closeModal('modalEditar')" class="btn btn-secondary">Cancelar</button>
                <button id="btnGuardarEdit" class="btn btn-primary">Actualizar Paquete</button>
            </div>
        </div>
    </div>

@endsection

@section('js-vistaRoles')
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="{{ asset('js/segurosAdmin.js') }}"></script>
@endsection
