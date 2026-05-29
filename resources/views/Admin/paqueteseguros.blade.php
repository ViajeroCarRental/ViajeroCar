@extends('layouts.Admin')

@section('Titulo', 'Seguros')

@section('css-vistaRoles')
    <link rel="stylesheet" href="{{ asset('css/paqueteseguro.css') }}">
    <style>
        /* =========================================
               NUEVOS ESTILOS PARA MEJORAR LA UI/UX
            ========================================= */

        /* 1. Botones de acción siempre lado a lado en la tabla */
        #tbodySeguros td:last-child {
            display: flex;
            gap: 8px;
            align-items: center;
            flex-wrap: nowrap;
        }

        /* 2. Modal Mucho más ancho */
        .modal-content {
            width: 95% !important;
            max-width: 900px !important;
            /* Más ancho para las 2 columnas principales */
            padding: 20px 30px;
        }

        /* 3. Layout Maestro a 2 Columnas (Izquierda inputs, Derecha listas) */
        .modal-layout {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 30px;
            /* Separación entre la columna izquierda y derecha */
        }

        /* 4. Grid interno para la columna izquierda (Campos) */
        .form-grid {
            display: grid;
            grid-template-columns: 1fr;
            /* Una sola columna dentro de la parte izquierda */
            gap: 15px;
        }

        /* Hacemos que la descripción ocupe más espacio natural */
        textarea {
            resize: vertical;
        }

        /* Hacemos que todos los inputs tomen el 100% de su contenedor */
        .modal-content input[type="text"],
        .modal-content input[type="number"],
        .modal-content select,
        .modal-content textarea {
            width: 100%;
            box-sizing: border-box;
            padding: 8px;
            border: 1px solid #cbd5e1;
            border-radius: 4px;
            margin-top: 5px;
        }

        /* 5. Contenedores de la columna Derecha (Autos y Protecciones) */
        .contenedor-porcentajes,
        .contenedor-protecciones {
            display: grid;
            grid-template-columns: 1fr;
            /* Una columna para las listas, puedes cambiarlo a 1fr 1fr si las quieres más apretadas */
            gap: 10px;
            max-height: 220px;
            /* Un poco más alto para que quepan bien */
            overflow-y: auto;
            border: 1px solid #cbd5e1;
            padding: 12px;
            border-radius: 6px;
            background: #f8fafc;
            margin-bottom: 15px;
        }

        .fila-porcentaje {
            display: flex;
            justify-content: space-between;
            align-items: center;
            background: white;
            padding: 5px 10px;
            border-radius: 4px;
            border: 1px solid #e2e8f0;
            font-size: 13px;
        }

        .input-pct {
            width: 60px !important;
            text-align: center;
            margin-top: 0 !important;
        }

        .mono {
            font-family: monospace;
            font-size: 14px;
        }
    </style>
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

    <div class="modal" id="modalNuevo">
        <div class="modal-content">
            <h3 style="margin-bottom: 20px; color: #1e3a8a; border-bottom: 2px solid #e2e8f0; padding-bottom: 10px;">Nuevo
                Paquete de Seguros</h3>

            <div class="modal-layout">
                <div class="form-grid">
                    <div>
                        <label>Nombre del Paquete</label>
                        <input type="text" id="newNombre" placeholder="Ej: Paquete Básico">
                    </div>

                    <div>
                        <label>Precio por día ($)</label>
                        <input type="number" step="0.01" id="newPrecio" value="0.00">
                    </div>

                    <div>
                        <label>Descripción</label>
                        <textarea id="newDescripcion" rows="2" placeholder="Breve descripción del paquete..."></textarea>
                    </div>

                    <div style="display: flex; gap: 10px;">
                        <div style="flex: 1;">
                            <label>Deducible Colisión (%)</label>
                            <input type="number" step="0.01" min="0" max="100" id="newDeducibleColision"
                                value="0.00">
                        </div>
                        <div style="flex: 1;">
                            <label>Deducible Robo (%)</label>
                            <input type="number" step="0.01" min="0" max="100" id="newDeducibleRobo"
                                value="0.00">
                        </div>
                    </div>

                    <div style="display: flex; align-items: center; justify-content: space-between; gap: 10px;">
                        {{-- <div style="flex: 1;">
                            <label>Orden de Lista (Prioridad)</label>
                            <input type="number" id="newOrden" value="0" placeholder="Ej: 1">
                        </div> --}}
                        <div style="padding-top: 25px;">
                            <label style="cursor: pointer; font-weight: bold;">
                                <input type="checkbox" id="newActivo" checked> Paquete Activo
                            </label>
                        </div>
                    </div>
                </div>

                <div>
                    {{-- Porcentajes por Categoría --}}
                    <label style="font-weight: bold; color: #1e3a8a; display: block; margin-bottom: 5px;">% de Garantía por
                        Categoría</label>
                    <div class="contenedor-porcentajes">
                        @foreach ($categorias as $cat)
                            <div class="fila-porcentaje">
                                <span>🚗 <strong>{{ $cat->nombre }}</strong> (Base:
                                    ${{ number_format($cat->garantia_base, 0) }})</span>
                                <div style="display: flex; align-items: center; gap: 5px;">
                                    <input type="number" class="input-pct new-porcentaje"
                                        data-id="{{ $cat->id_categoria }}" min="0" max="100" value="0"> %
                                </div>
                            </div>
                        @endforeach
                    </div>

                    {{-- Protecciones Incluidas --}}
                    <label style="font-weight: bold; color: #1e3a8a; display: block; margin-bottom: 5px;">Protecciones
                        Incluidas</label>
                    <div class="contenedor-protecciones">
                        @if (isset($protecciones))
                            @foreach ($protecciones as $prot)
                                <label
                                    style="display: flex; align-items: center; gap: 8px; font-size: 13px; cursor: pointer; background: white; padding: 5px; border-radius: 4px; border: 1px solid #e2e8f0;">
                                    <input type="checkbox" class="new-prot" value="{{ $prot->id_individual }}">
                                    {{ $prot->nombre }}
                                </label>
                            @endforeach
                        @endif
                    </div>
                </div>
            </div>
            <div class="modal-footer"
                style="margin-top:20px; border-top: 1px solid #e2e8f0; padding-top: 15px; text-align: right;">
                <button onclick="closeModal('modalNuevo')" class="btn btn-secondary">Cancelar</button>
                <button id="btnGuardarNuevo" class="btn btn-primary">Guardar Paquete</button>
            </div>
        </div>
    </div>

    <div class="modal" id="modalEditar">
        <div class="modal-content">
            <h3 style="margin-bottom: 20px; color: #1e3a8a; border-bottom: 2px solid #e2e8f0; padding-bottom: 10px;">Editar
                Paquete</h3>
            <input type="hidden" id="editId">

            <div class="modal-layout">
                <div class="form-grid">
                    <div>
                        <label>Nombre del Paquete</label>
                        <input type="text" id="editNombre">
                    </div>

                    <div>
                        <label>Precio por día ($)</label>
                        <input type="number" step="0.01" id="editPrecio">
                    </div>

                    <div>
                        <label>Descripción</label>
                        <textarea id="editDescripcion" rows="2"></textarea>
                    </div>

                    <div style="display: flex; gap: 10px;">
                        <div style="flex: 1;">
                            <label>Deducible Colisión (%)</label>
                            <input type="number" step="0.01" min="0" max="100" id="editDeducibleColision">
                        </div>
                        <div style="flex: 1;">
                            <label>Deducible Robo (%)</label>
                            <input type="number" step="0.01" min="0" max="100" id="editDeducibleRobo">
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

                <div>
                    {{-- Porcentajes por Categoría --}}
                    <label style="font-weight: bold; color: #1e3a8a; display: block; margin-bottom: 5px;">% de Garantía por
                        Categoría</label>
                    <div class="contenedor-porcentajes">
                        @foreach ($categorias as $cat)
                            <div class="fila-porcentaje">
                                <span>🚗 <strong>{{ $cat->nombre }}</strong></span>
                                <div style="display: flex; align-items: center; gap: 5px;">
                                    <input type="number" class="input-pct edit-porcentaje"
                                        data-id="{{ $cat->id_categoria }}" min="0" max="100"> %
                                </div>
                            </div>
                        @endforeach
                    </div>

                    {{-- Protecciones Incluidas --}}
                    <label style="font-weight: bold; color: #1e3a8a; display: block; margin-bottom: 5px;">Protecciones
                        Incluidas</label>
                    <div class="contenedor-protecciones">
                        @if (isset($protecciones))
                            @foreach ($protecciones as $prot)
                                <label
                                    style="display: flex; align-items: center; gap: 8px; font-size: 13px; cursor: pointer; background: white; padding: 5px; border-radius: 4px; border: 1px solid #e2e8f0;">
                                    <input type="checkbox" class="edit-prot" value="{{ $prot->id_individual }}">
                                    {{ $prot->nombre }}
                                </label>
                            @endforeach
                        @endif
                    </div>
                </div>
            </div>
            <div class="modal-footer"
                style="margin-top:20px; border-top: 1px solid #e2e8f0; padding-top: 15px; text-align: right;">
                <button onclick="closeModal('modalEditar')" class="btn btn-secondary">Cancelar</button>
                <button id="btnGuardarEdit" class="btn btn-primary">Actualizar Paquete</button>
            </div>
        </div>
    </div>

@endsection

@section('js-vistaRoles')
    <script src="{{ asset('js/segurosAdmin.js') }}"></script>
@endsection
