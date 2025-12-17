@extends('layouts.Admin')
@section('titulo', 'Categorías')

@section('css')
<link rel="stylesheet" href="{{ asset('css/categorias.css') }}">
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
          <th>Código</th>
          <th>Nombre</th>
          <th>Precio x Día</th>
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
            <td>{{ $c->activo ? 'Sí' : 'No' }}</td>

            <td class="actions">
              <button class="btn-edit"
                onclick="openEdit(
                  '{{ $c->id_categoria }}',
                  @js($c->codigo),
                  @js($c->nombre),
                  '{{ $c->precio_dia }}',
                  '{{ $c->activo }}'
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
            <td colspan="5" class="empty">No hay categorías registradas.</td>
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

    <label class="label">Código</label>
    <input class="input" name="codigo" maxlength="10" required>

    <label class="label">Nombre</label>
    <input class="input" name="nombre" maxlength="100" required>

    <label class="label">Precio por día</label>
    <input class="input" name="precio_dia" type="number" step="0.01" min="0" required>

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
  <form method="POST" id="formEditar" class="modal-box">
    @csrf
    @method('PUT')

    <div class="modal-head">
      <h2>Editar categoría</h2>
      <button type="button" class="x" onclick="modalEditar.close()">✕</button>
    </div>

    <label class="label">Código</label>
    <input class="input" id="e_codigo" name="codigo" maxlength="10" required>

    <label class="label">Nombre</label>
    <input class="input" id="e_nombre" name="nombre" maxlength="100" required>

    <label class="label">Precio por día</label>
    <input class="input" id="e_precio" name="precio_dia" type="number" step="0.01" min="0" required>

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

<script>
function openEdit(id, codigo, nombre, precio, activo){
  const form = document.getElementById('formEditar');
  form.action = `/categorias/${id}`;

  document.getElementById('e_codigo').value = codigo;
  document.getElementById('e_nombre').value = nombre;
  document.getElementById('e_precio').value = precio;
  document.getElementById('e_activo').value = activo;

  document.getElementById('modalEditar').showModal();
}
</script>
@endsection
