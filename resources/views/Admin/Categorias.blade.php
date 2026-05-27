@extends('layouts.Admin')
@section('Titulo', 'Categorías')

@section('css')
    <link rel="stylesheet" href="{{ asset('css/Categorias.css') }}">
    <style>
        /* =========================
       DISEÑO DEL MODAL (ANCHO Y COLUMNAS)
    ========================= */
        dialog.modal {
            max-width: 750px;
            /* Lo hace más ancho */
            width: 90%;
            border-radius: 12px;
            border: none;
            padding: 0;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.2);
        }

        .modal-box {
            padding: 25px;
            max-height: 85vh;
            /* Evita que sea más largo que tu pantalla */
            overflow-y: auto;
            /* Activa el scroll internamente solo si es necesario */
        }

        /* Sistema de 2 columnas */
        .form-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 15px;
            margin-bottom: 20px;
        }

        .input-group {
            display: flex;
            flex-direction: column;
            gap: 5px;
        }

        .full-width {
            grid-column: span 2;
        }
        
        .modal-box::-webkit-scrollbar {
            width: 8px;
            /* Más delgado */
        }

        .modal-box::-webkit-scrollbar-track {
            background: #f0f0f0;
            border-radius: 10px;
        }

        .modal-box::-webkit-scrollbar-thumb {
            background: #bbbbbb;
            border-radius: 10px;
        }

        .modal-box::-webkit-scrollbar-thumb:hover {
            background: #999999;
        }
    </style>
@endsection

@section('contenido')
    <main class="main">

        <div class="head">
            <h1 class="h1">Categorías</h1>

            <button class="btn-add" onclick="document.getElementById('modalCrear').showModal()">
                + Nueva categoría
            </button>
        </div>

        @if (session('success'))
            <div class="toast">{{ session('success') }}</div>
        @endif

        <section class="card">
            <table class="table">
                <thead>
                    <tr>
                        <th>Código</th>
                        <th>Nombre</th>
                        <th>Precio x Día</th>
                        <th>Precio x Semana</th>
                        <th>Precio x Mes</th>
                        <th>Descuento</th>
                        <th>Garantía</th>
                        <th>Activo</th>
                        <th>Acciones</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse($categorias as $c)
                        <tr>
                            <td class="mono">{{ $c->codigo }}</td>
                            <td>{{ $c->nombre }}</td>
                            <td class="mono">${{ number_format($c->precio_dia, 2) }}</td>
                            <td class="mono">${{ number_format($c->precio_semana, 2) }}</td>
                            <td class="mono">${{ number_format($c->precio_mes, 2) }}</td>
                            <td class="mono">{{ number_format($c->descuento_miembro, 2) }}%</td>
                            <td class="mono">${{ number_format($c->garantia_base, 2) }}</td>
                            <td>{{ $c->activo ? 'Sí' : 'No' }}</td>
                            <td>
                                <div style="display: flex; gap: 8px; align-items: center;">
                                    <button class="btn-edit"
                                        onclick="openEdit(
                    {{ $c->id_categoria }},
                    @js($c->codigo),
                    @js($c->nombre),
                    {{ $c->precio_dia }},
                    {{ $c->precio_semana }},
                    {{ $c->precio_mes }},
                    {{ $c->descuento_miembro }},
                    {{ $c->garantia_base }},
                    {{ $c->activo }},
                    @js($c->paquetes_asignados)
                  )">
                                        Editar
                                    </button>

                                    <form method="POST" action="{{ route('categorias.destroy', $c->id_categoria) }}"
                                        style="margin: 0;">
                                        @csrf
                                        @method('DELETE')
                                        <button class="btn-del" onclick="return confirm('¿Eliminar esta categoría?')">
                                            Eliminar
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="empty">
                                No hay categorías registradas.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </section>

    </main>

    {{-- =========================
    MODAL CREAR
========================= --}}
    <dialog id="modalCrear" class="modal">
        <form method="POST" action="{{ route('categorias.store') }}" class="modal-box">
            @csrf

            <div class="modal-head" style="display: flex; justify-content: space-between; margin-bottom: 20px;">
                <h2 style="margin: 0;">Nueva categoría</h2>
                <button type="button" class="x" onclick="document.getElementById('modalCrear').close()"
                    style="border:none; background:none; font-size:1.5rem; cursor:pointer;">✕</button>
            </div>

            {{-- Contenedor de 2 columnas --}}
            <div class="form-grid">
                <div class="input-group">
                    <label class="label">Código</label>
                    <input class="input" name="codigo" maxlength="10" required placeholder="Ej: C, D, E">
                </div>

                <div class="input-group">
                    <label class="label">Nombre</label>
                    <input class="input" name="nombre" maxlength="100" required placeholder="Ej: Compacto, Mediano">
                </div>

                <div class="input-group">
                    <label class="label">Precio por día</label>
                    <input class="input" name="precio_dia" type="number" step="0.01" min="0" required>
                </div>

                <div class="input-group">
                    <label class="label">Precio por semana</label>
                    <input class="input" name="precio_semana" type="number" step="0.01" min="0" value="0"
                        required>
                </div>

                <div class="input-group">
                    <label class="label">Precio por mes</label>
                    <input class="input" name="precio_mes" type="number" step="0.01" min="0" value="0"
                        required>
                </div>

                <div class="input-group">
                    <label class="label">Descuento miembro (%)</label>
                    <input class="input" name="descuento_miembro" type="number" step="0.01" min="0"
                        max="100" value="0" required>
                </div>

                <div class="input-group">
                    <label class="label">Garantía Base</label>
                    <input class="input" name="garantia_base" type="number" step="0.01" min="0" value="0.00"
                        required>
                </div>

                <div class="input-group" style="justify-content: center;">
                    <label class="check" style="cursor: pointer;">
                        <input type="checkbox" name="activo" value="1" checked>
                        <strong>Categoría Activa</strong>
                    </label>
                </div>

                {{-- Esta fila ocupa ambas columnas --}}
                <div class="full-width">
                    <label class="label" style="display: block; margin-bottom: 8px;">Paquetes Incluidos</label>
                    <div
                        style="display: flex; gap: 15px; flex-wrap: wrap; background: #f9f9f9; padding: 15px; border-radius: 8px; border: 1px solid #eee;">
                        @forelse($paquetes as $p)
                            <label class="check" style="cursor: pointer;">
                                <input type="checkbox" name="paquetes[]" value="{{ $p->id_paquete }}">
                                {{ $p->nombre }}
                            </label>
                        @empty
                            <span style="color: #888; font-size: 0.9em;">No hay paquetes de seguro registrados.</span>
                        @endforelse
                    </div>
                </div>
            </div>

            <div class="modal-actions" style="display: flex; gap: 10px; justify-content: flex-end; margin-top: 10px;">
                <button class="btn-ghost" type="button"
                    onclick="document.getElementById('modalCrear').close()">Cancelar</button>
                <button class="btn-add" type="submit">Guardar</button>
            </div>
        </form>
    </dialog>

    {{-- =========================
    MODAL EDITAR
========================= --}}
    <dialog id="modalEditar" class="modal">
        <form method="POST" id="formEditar" class="modal-box"
            data-action="{{ route('categorias.update', '__ID__') }}">
            @csrf
            @method('PUT')

            <div class="modal-head" style="display: flex; justify-content: space-between; margin-bottom: 20px;">
                <h2 style="margin: 0;">Editar categoría</h2>
                <button type="button" class="x" onclick="document.getElementById('modalEditar').close()"
                    style="border:none; background:none; font-size:1.5rem; cursor:pointer;">✕</button>
            </div>

            {{-- Contenedor de 2 columnas --}}
            <div class="form-grid">
                <div class="input-group">
                    <label class="label">Código</label>
                    <input class="input" id="e_codigo" name="codigo" maxlength="10" required>
                </div>

                <div class="input-group">
                    <label class="label">Nombre</label>
                    <input class="input" id="e_nombre" name="nombre" maxlength="100" required>
                </div>

                <div class="input-group">
                    <label class="label">Precio por día</label>
                    <input class="input" id="e_precio" name="precio_dia" type="number" step="0.01"
                        min="0" required>
                </div>

                <div class="input-group">
                    <label class="label">Precio por semana</label>
                    <input class="input" id="e_precio_semana" name="precio_semana" type="number" step="0.01"
                        min="0" required>
                </div>

                <div class="input-group">
                    <label class="label">Precio por mes</label>
                    <input class="input" id="e_precio_mes" name="precio_mes" type="number" step="0.01"
                        min="0" required>
                </div>

                <div class="input-group">
                    <label class="label">Descuento (%)</label>
                    <input class="input" id="e_descuento" name="descuento_miembro" type="number" step="0.01"
                        min="0" max="100" required>
                </div>

                <div class="input-group">
                    <label class="label">Garantía Base</label>
                    <input class="input" id="e_garantia_base" name="garantia_base" type="number" step="0.01"
                        min="0" required>
                </div>

                <div class="input-group">
                    <label class="label">Activo</label>
                    <select class="input" id="e_activo" name="activo" required>
                        <option value="1">Sí</option>
                        <option value="0">No</option>
                    </select>
                </div>

                {{-- Esta fila ocupa ambas columnas --}}
                <div class="full-width">
                    <label class="label" style="display: block; margin-bottom: 8px;">Paquetes Incluidos (Desde
                        BD)</label>
                    <div
                        style="display: flex; gap: 15px; flex-wrap: wrap; background: #f9f9f9; padding: 15px; border-radius: 8px; border: 1px solid #eee;">
                        @foreach ($paquetes as $p)
                            <label class="check" style="cursor: pointer;">
                                <input type="checkbox" name="paquetes[]" value="{{ $p->id_paquete }}"
                                    class="checkbox-paquete-edit">
                                {{ $p->nombre }}
                            </label>
                        @endforeach
                    </div>
                </div>
            </div>

            <div class="modal-actions" style="display: flex; gap: 10px; justify-content: flex-end; margin-top: 10px;">
                <button class="btn-ghost" type="button"
                    onclick="document.getElementById('modalEditar').close()">Cancelar</button>
                <button class="btn-add" type="submit">Actualizar</button>
            </div>
        </form>
    </dialog>

    {{-- =========================
    JS INLINE
========================= --}}
    <script>
        function openEdit(id, codigo, nombre, precioDia, precioSemana, precioMes, descuento, garantiaBase, activo,
            paquetesAsignados) {
            const form = document.getElementById('formEditar');

            form.action = form.dataset.action.replace('__ID__', id);

            document.getElementById('e_codigo').value = codigo;
            document.getElementById('e_nombre').value = nombre;
            document.getElementById('e_precio').value = precioDia;
            document.getElementById('e_precio_semana').value = precioSemana;
            document.getElementById('e_precio_mes').value = precioMes;
            document.getElementById('e_descuento').value = descuento;
            document.getElementById('e_garantia_base').value = garantiaBase;
            document.getElementById('e_activo').value = activo;

            // 🟢 Lógica para checar los paquetes que vienen guardados en editar
            document.querySelectorAll('.checkbox-paquete-edit').forEach(checkbox => {
                checkbox.checked = paquetesAsignados && paquetesAsignados.map(String).includes(String(checkbox
                    .value));
            });

            document.getElementById('modalEditar').showModal();
        }
    </script>
@endsection
