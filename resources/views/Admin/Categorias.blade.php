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

  @if(session('success'))
    <div class="toast">{{ session('success') }}</div>
  @endif

  <section class="card">
    <table class="table">
      <thead>
        <tr>
          <th>Orden</th>
          <th>Código</th>
          <th>Nombre</th>
          <th>Precio x Día</th>
          <th>Precio x Semana</th>
          <th>Precio x Mes</th>
          <th>Descuento Miembro</th>
          <th>Garantía Base</th>
          <th>Activo</th>
          <th>Acciones</th>
        </tr>
      </thead>

      <tbody>
        @forelse($categorias as $c)
          <tr>
            <td class="mono">{{ $c->orden }}</td>
            <td class="mono">{{ $c->codigo }}</td>
            <td>{{ $c->nombre }}</td>
            <td class="mono">${{ number_format($c->precio_dia, 2) }}</td>
            <td class="mono">${{ number_format($c->precio_semana, 2) }}</td>
            <td class="mono">${{ number_format($c->precio_mes, 2) }}</td>
            <td class="mono">${{ number_format($c->garantia_base, 2) }}</td>

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
                  {{ $c->orden }},
                  {{ $c->activo }}
                )">
                Editar
              </button>

              <form method="POST" action="{{ route('categorias.destroy', $c->id_categoria) }}">
                @csrf
                @method('DELETE')
                <button class="btn-del" onclick="return confirm('¿Eliminar esta categoría?')">
                  Eliminar
                </button>
              </form>
            </td>
          </tr>
        @empty
          <tr>
            <td colspan="8" class="empty">
            <td colspan="7" class="empty">
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

    <div class="modal-head">
      <h2>Nueva categoría</h2>
      <button type="button" class="x" onclick="modalCrear.close()">✕</button>
    </div>

    <label class="label">Orden de Visualización</label>
    <input class="input" name="orden" type="number" min="0" value="0" required placeholder="Ej: 1 (Aparece primero)">

    <label class="label">Código</label>
    <input class="input" name="codigo" maxlength="10" required placeholder="Ej: C, D, E">

    <label class="label">Nombre</label>
    <input class="input" name="nombre" maxlength="100" required placeholder="Ej: Compacto, Mediano">

    <label class="label">Precio por día</label>
    <input class="input"
           name="precio_dia"
           type="number"
           step="0.01"
           min="0"
           required>

<label class="label">Precio por semana</label>
<input class="input"
       name="precio_semana"
       type="number"
       step="0.01"
       min="0"
       value="0"
       required>

<label class="label">Precio por mes</label>
<input class="input"
       name="precio_mes"
       type="number"
       step="0.01"
       min="0"
       value="0"
       required>

<label class="label">Descuento miembro (%)</label>
<input class="input"
       name="descuento_miembro"
       type="number"
       step="0.01"
       min="0"
       max="100"
       value="0"
       required>
    <label class="label">Garantía Base (Sin Seguro)</label>
    <input class="input"
           name="garantia_base"
           type="number"
           step="0.01"
           min="0"
           value="0.00"
           required>

    <label class="check">
      <input type="checkbox" name="activo" value="1" checked>
      Activo
    </label>

    <div class="modal-actions">
      <button class="btn-add" type="submit">Guardar</button>
      <button class="btn-ghost" type="button" onclick="modalCrear.close()">Cancelar</button>
    </div>
  </form>
</dialog>

{{-- =========================
    MODAL EDITAR
========================= --}}
<dialog id="modalEditar" class="modal">
  <form method="POST"
        id="formEditar"
        class="modal-box"
        data-action="{{ route('categorias.update', '__ID__') }}">
    @csrf
    @method('PUT')

    <div class="modal-head">
      <h2>Editar categoría</h2>
      <button type="button" class="x" onclick="modalEditar.close()">✕</button>
    </div>

    <label class="label">Orden de Visualización</label>
    <input class="input" id="e_orden" name="orden" type="number" min="0" required>

    <label class="label">Código</label>
    <input class="input" id="e_codigo" name="codigo" maxlength="10" required>

    <label class="label">Nombre</label>
    <input class="input" id="e_nombre" name="nombre" maxlength="100" required>

    <label class="label">Precio por día</label>
    <input class="input"
           id="e_precio"
           name="precio_dia"
           type="number"
           step="0.01"
           min="0"
           required>

    <label class="label">Precio por semana</label>
    <input class="input"
        id="e_precio_semana"
        name="precio_semana"
        type="number"
        step="0.01"
        min="0"
        required>

    <label class="label">Precio por mes</label>
    <input class="input"
        id="e_precio_mes"
        name="precio_mes"
        type="number"
        step="0.01"
        min="0"
        required>

    <label class="label">Descuento miembro (%)</label>
    <input class="input"
        id="e_descuento"
        name="descuento_miembro"
        type="number"
        step="0.01"
        min="0"
        max="100"
        required>

    <label class="label">Garantía Base (Sin Seguro)</label>
    <input class="input"
           id="e_garantia_base"
           name="garantia_base"
           type="number"
           step="0.01"
           min="0"
           required>

    <label class="label">Activo</label>
    <select class="input" id="e_activo" name="activo" required>
      <option value="1">Sí</option>
      <option value="0">No</option>
    </select>

    <div class="modal-actions">
      <button class="btn-add" type="submit">Actualizar</button>
      <button class="btn-ghost" type="button" onclick="modalEditar.close()">Cancelar</button>
    </div>
  </form>
</dialog>

{{-- =========================
    JS INLINE
========================= --}}
<script>
function openEdit(id, codigo, nombre, precio, precioSemana, precioMes, descuento, activo) {
function openEdit(id, codigo, nombre, precio, garantia_base, orden, activo) {
  const form = document.getElementById('formEditar');

  form.action = form.dataset.action.replace('__ID__', id);

  document.getElementById('e_codigo').value = codigo;
  document.getElementById('e_nombre').value = nombre;
  document.getElementById('e_precio').value = precio;
  document.getElementById('e_precio_semana').value  = precioSemana;
  document.getElementById('e_precio_mes').value     = precioMes;
  document.getElementById('e_descuento').value      = descuento;
  document.getElementById('e_garantia_base').value = garantia_base;
  document.getElementById('e_orden').value = orden;
  document.getElementById('e_activo').value = activo;

  document.getElementById('modalEditar').showModal();
}
</script>
@endsection
