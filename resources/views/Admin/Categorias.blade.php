@extends('layouts.Admin')
@section('Titulo', 'Categorías')

@section('css')
    <link rel="stylesheet" href="{{ asset('css/Categorias.css') }}">
@endsection

@section('contenido')
    <main class="main">

        <div class="head">
            <h1 class="h1">Categorías</h1>

            <button class="btn-add" onclick="document.getElementById('modalCrear').showModal()">
                + Nueva categoría
            </button>
        </div>

        <section class="card">
            <table class="table">
                <thead>
                    <tr>
                        <th>Código</th>
                        <th>Nombre</th>
                        <th>Descripción</th>
                        <th>Precio x Día</th>
                        <th>Precio x Semana</th>
                        <th>Precio x Mes</th>
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
                            <td>{{ $c->descripcion ?: '—' }}</td>
                            <td class="mono">{{ $c->precio_dia > 0 ? '$' . number_format($c->precio_dia, 2) : '—' }}</td>
                            <td class="mono">{{ $c->precio_semana > 0 ? '$' . number_format($c->precio_semana, 2) : '—' }}</td>
                            <td class="mono">{{ $c->precio_mes > 0 ? '$' . number_format($c->precio_mes, 2) : '—' }}</td>
                            <td class="mono">{{ $c->garantia_base > 0 ? '$' . number_format($c->garantia_base, 2) : '—' }}</td>
                            <td>{{ $c->activo ? 'Sí' : 'No' }}</td>
                            <td>
                                <div style="display: flex; gap: 8px; align-items: center;">
                                    <button class="btn-edit"
                                        onclick="openEdit(
                                            {{ $c->id_categoria }},
                                            @js($c->codigo),
                                            @js($c->nombre),
                                            @js($c->descripcion),
                                            {{ $c->precio_dia }},
                                            {{ $c->precio_semana }},
                                            {{ $c->precio_mes }},
                                            {{ $c->garantia_base }},
                                            {{ $c->activo }},
                                            @js($c->paquetes_asignados)
                                        )">
                                        Editar
                                    </button>

                                    <form id="form-delete-{{ $c->id_categoria }}" method="POST" action="{{ route('categorias.destroy', $c->id_categoria) }}" style="margin: 0;">
                                        @csrf
                                        @method('DELETE')
                                        <button type="button" class="btn-del" onclick="confirmarEliminacion(event, 'form-delete-{{ $c->id_categoria }}')">
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
        <form method="POST" action="{{ route('categorias.store') }}" class="modal-box" id="formCrear">
            @csrf

            <div class="modal-head" style="display: flex; justify-content: space-between; margin-bottom: 20px;">
                <h2 style="margin: 0;">Nueva categoría</h2>
                <button type="button" class="x" onclick="document.getElementById('modalCrear').close()"
                    style="border:none; background:none; font-size:1.5rem; cursor:pointer;">✕</button>
            </div>

            <div class="form-grid">
                <div class="input-group">
                    <label class="label">Código</label>
                    <input class="input uppercase-input" name="codigo" maxlength="10" required placeholder="Ej: C, D, E">
                </div>

                <div class="input-group">
                    <label class="label">Nombre</label>
                    <input class="input uppercase-input" name="nombre" maxlength="100" required placeholder="Ej: COMPACTO">
                </div>

                <div class="input-group full-width">
                    <label class="label">Descripción</label>
                    <input class="input" name="descripcion" maxlength="255" required placeholder="Ej: Toyota Tacoma o similar">
                </div>

                <div class="input-group">
                    <label class="label">Precio por día</label>
                    <input class="input input-money" type="text" inputmode="decimal" placeholder="$0.00" data-target="precio_dia">
                    <input type="hidden" name="precio_dia" value="">
                </div>

                <div class="input-group">
                    <label class="label">Precio por semana</label>
                    <input class="input input-money" type="text" inputmode="decimal" placeholder="$0.00" data-target="precio_semana">
                    <input type="hidden" name="precio_semana" value="">
                </div>

                <div class="input-group">
                    <label class="label">Precio por mes</label>
                    <input class="input input-money" type="text" inputmode="decimal" placeholder="$0.00" data-target="precio_mes">
                    <input type="hidden" name="precio_mes" value="">
                </div>

                <div class="input-group">
                    <label class="label">Garantía Base</label>
                    <input class="input input-money" type="text" inputmode="decimal" placeholder="$0.00" data-target="garantia_base">
                    <input type="hidden" name="garantia_base" value="">
                </div>

                <div class="input-group" style="justify-content: center;">
                    <label class="check" style="cursor: pointer;">
                        <input type="checkbox" name="activo" value="1" checked>
                        <strong>Categoría Activa</strong>
                    </label>
                </div>

                <div class="full-width">
                    <label class="label" style="display: block; margin-bottom: 8px;">Paquetes Incluidos</label>
                    <div style="display: flex; gap: 15px; flex-wrap: wrap; background: #f9f9f9; padding: 15px; border-radius: 8px; border: 1px solid #eee;">
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
                <button class="btn-ghost" type="button" onclick="document.getElementById('modalCrear').close()">Cancelar</button>
                <button class="btn-add" type="submit">Guardar</button>
            </div>
        </form>
    </dialog>

    {{-- =========================
    MODAL EDITAR
    ========================= --}}
    <dialog id="modalEditar" class="modal">
        <form method="POST" id="formEditar" class="modal-box" data-action="{{ route('categorias.update', '__ID__') }}">
            @csrf
            @method('PUT')

            <div class="modal-head" style="display: flex; justify-content: space-between; margin-bottom: 20px;">
                <h2 style="margin: 0;">Editar categoría</h2>
                <button type="button" class="x" onclick="document.getElementById('modalEditar').close()"
                    style="border:none; background:none; font-size:1.5rem; cursor:pointer;">✕</button>
            </div>

            <div class="form-grid">
                <div class="input-group">
                    <label class="label">Código</label>
                    <input class="input uppercase-input" id="e_codigo" name="codigo" maxlength="10" required>
                </div>

                <div class="input-group">
                    <label class="label">Nombre</label>
                    <input class="input uppercase-input" id="e_nombre" name="nombre" maxlength="100" required>
                </div>

                <div class="input-group full-width">
                    <label class="label">Descripción</label>
                    <input class="input" id="e_descripcion" name="descripcion" maxlength="255" required placeholder="Ej: Toyota Tacoma o similar">
                </div>

                <div class="input-group">
                    <label class="label">Precio por día</label>
                    <input class="input input-money" id="e_precio" type="text" inputmode="decimal" placeholder="$0.00" data-target="precio_dia">
                    <input type="hidden" name="precio_dia" id="e_precio_hidden" value="">
                </div>

                <div class="input-group">
                    <label class="label">Precio por semana</label>
                    <input class="input input-money" id="e_precio_semana" type="text" inputmode="decimal" placeholder="$0.00" data-target="precio_semana">
                    <input type="hidden" name="precio_semana" id="e_precio_semana_hidden" value="">
                </div>

                <div class="input-group">
                    <label class="label">Precio por mes</label>
                    <input class="input input-money" id="e_precio_mes" type="text" inputmode="decimal" placeholder="$0.00" data-target="precio_mes">
                    <input type="hidden" name="precio_mes" id="e_precio_mes_hidden" value="">
                </div>

                <div class="input-group">
                    <label class="label">Garantía Base</label>
                    <input class="input input-money" id="e_garantia_base" type="text" inputmode="decimal" placeholder="$0.00" data-target="garantia_base">
                    <input type="hidden" name="garantia_base" id="e_garantia_base_hidden" value="">
                </div>

                <div class="input-group">
                    <label class="label">Activo</label>
                    <select class="input" id="e_activo" name="activo" required>
                        <option value="1">Sí</option>
                        <option value="0">No</option>
                    </select>
                </div>

                <div class="full-width">
                    <label class="label" style="display: block; margin-bottom: 8px;">Paquetes Incluidos (Desde BD)</label>
                    <div style="display: flex; gap: 15px; flex-wrap: wrap; background: #f9f9f9; padding: 15px; border-radius: 8px; border: 1px solid #eee;">
                        @foreach ($paquetes as $p)
                            <label class="check" style="cursor: pointer;">
                                <input type="checkbox" name="paquetes[]" value="{{ $p->id_paquete }}" class="checkbox-paquete-edit">
                                {{ $p->nombre }}
                            </label>
                        @endforeach
                    </div>
                </div>
            </div>

            <div class="modal-actions" style="display: flex; gap: 10px; justify-content: flex-end; margin-top: 10px;">
                <button class="btn-ghost" type="button" onclick="document.getElementById('modalEditar').close()">Cancelar</button>
                <button class="btn-add" type="submit">Actualizar</button>
            </div>
        </form>
    </dialog>

@endsection

@section('js-vistaRoles')
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="{{ asset('js/CategoriasAdmin.js') }}"></script>

    @if(session('success'))
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                Swal.fire({
                    title: '¡Éxito!',
                    text: '{{ session('success') }}',
                    icon: 'success',
                    timer: 2500,
                    showConfirmButton: false
                });
            });
        </script>
    @endif

    @if ($errors->any())
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                Swal.fire({
                    title: 'No se pudo guardar',
                    html: `{!! implode('<br>', $errors->all()) !!}`,
                    icon: 'error',
                    confirmButtonColor: '#ff1e2d'
                });
            });
        </script>
    @endif
@endsection
