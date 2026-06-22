@extends('layouts.Admin')
@section('Titulo', 'Adicionales')

@section('css')
<link rel="stylesheet" href="{{ asset('css/Categorias.css') }}">
@endsection

@section('contenido')
<main class="main">

  <div class="head">
    <h1 class="h1">Adicionales</h1>

    <button class="btn-add" onclick="document.getElementById('modalCrear').showModal()">
      + Nuevo servicio
    </button>
  </div>

  <section class="card">
    <table class="table">
      <thead>
        <tr>
          <th>Nombre</th>
          <th>Tipo de Cobro</th>
          <th>Precio</th>
          <th>Activo</th>
          <th>Acceso</th>
          <th>Acciones</th>
        </tr>
      </thead>

      <tbody>
        @forelse($servicios as $s)
          <tr>
            <td>{{ $s->nombre }}</td>
            <td class="mono">{{ str_replace('_', ' ', $s->tipo_cobro) }}</td>
            <td class="mono">${{ number_format($s->precio, 2) }}</td>
            <td>{{ $s->activo ? 'Sí' : 'No' }}</td>
            <td>
              @if($s->usuario && $s->administrador)
                Ambos
              @elseif($s->usuario)
                Usuario
              @elseif($s->administrador)
                Administrador
              @else
                Ninguno
              @endif
            </td>

            <td class="actions">
              <button class="btn-edit"
                onclick="openEdit(
                  {{ $s->id_servicio }},
                  @js($s->nombre),
                  @js($s->descripcion),
                  @js($s->tipo_cobro),
                  {{ $s->precio }},
                  {{ $s->activo }},
                  {{ $s->usuario }},
                  {{ $s->administrador }}
                )">
                Editar
              </button>

              <form method="POST"
                    action="{{ route('servicios.destroy', $s->id_servicio) }}"
                    class="form-eliminar"
                    data-nombre="{{ $s->nombre }}">
                @csrf
                @method('DELETE')
                <button class="btn-del" type="submit">
                  Eliminar
                </button>
              </form>
            </td>
          </tr>
        @empty
          <tr>
            <td colspan="6" class="empty">
              No hay servicios registrados.
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
  <form method="POST" action="{{ route('servicios.store') }}" class="modal-box" id="formCrear">
    @csrf

    <div class="modal-head">
      <h2>Nuevo servicio</h2>
      <button type="button" class="x" onclick="modalCrear.close()">✕</button>
    </div>

    <label class="label">Nombre</label>
    <input class="input" name="nombre" maxlength="120" required>

    <label class="label">Descripción</label>
    <input class="input" name="descripcion" maxlength="255">

    <label class="label">Tipo de cobro</label>
    <select class="input" name="tipo_cobro" required>
      <option value="por_dia">Por día</option>
      <option value="por_evento">Por evento</option>
      <option value="por_tanque">Por tanque</option>
    </select>

    <label class="label">Precio</label>
    <input class="input"
           name="precio"
           type="number"
           step="0.01"
           min="0"
           required>

    <label class="check">
      <input type="checkbox" name="activo" value="1" checked>
      Activo
    </label>

    <label class="label" style="margin-top:10px;">Acceso permitido</label>

    <label class="check">
      <input type="checkbox" name="usuario" value="1" checked>
      Usuario
    </label>

    <label class="check">
      <input type="checkbox" name="administrador" value="1" checked>
      Administrador
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
        data-action="{{ route('servicios.update', '__ID__') }}">
    @csrf
    @method('PUT')

    <div class="modal-head">
      <h2>Editar servicio</h2>
      <button type="button" class="x" onclick="modalEditar.close()">✕</button>
    </div>

    <label class="label">Nombre</label>
    <input class="input" id="e_nombre" name="nombre" maxlength="120" required>

    <label class="label">Descripción</label>
    <input class="input" id="e_descripcion" name="descripcion" maxlength="255">

    <label class="label">Tipo de cobro</label>
    <select class="input" id="e_tipo_cobro" name="tipo_cobro" required>
      <option value="por_dia">Por día</option>
      <option value="por_evento">Por evento</option>
      <option value="por_tanque">Por tanque</option>
    </select>

    <label class="label">Precio</label>
    <input class="input"
           id="e_precio"
           name="precio"
           type="number"
           step="0.01"
           min="0"
           required>

    <label class="label">Activo</label>
    <select class="input" id="e_activo" name="activo" required>
      <option value="1">Sí</option>
      <option value="0">No</option>
    </select>

    <label class="label">Permitir en pagina web</label>
    <select class="input" id="e_usuario" name="usuario" required>
      <option value="1">Permitido</option>
      <option value="0">No permitido</option>
    </select>

    <label class="label">Permitir en panel</label>
    <select class="input" id="e_administrador" name="administrador" required>
      <option value="1">Permitido</option>
      <option value="0">No permitido</option>
    </select>

    <div class="modal-actions">
      <button class="btn-add" type="submit">Actualizar</button>
      <button class="btn-ghost" type="button" onclick="modalEditar.close()">Cancelar</button>
    </div>
  </form>
</dialog>

{{-- =========================
    SWEETALERT2 CDN
========================= --}}
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

{{-- =========================
    JS INLINE
========================= --}}
<script>
/* ---------- PARCHE GLOBAL: cerrar dialogs antes de cualquier Swal ---------- */
/* Los <dialog> abiertos con showModal() viven en la top-layer del navegador,
   por encima de SweetAlert2. Esto fuerza a cerrarlos antes de mostrar cualquier
   alerta para que el Swal siempre quede visible. */
const _swalFire = Swal.fire.bind(Swal);
Swal.fire = function (...args) {
  document.querySelectorAll('dialog[open]').forEach(function (d) { d.close(); });
  return _swalFire(...args);
};

/* ---------- ABRIR MODAL EDITAR ---------- */
function openEdit(id, nombre, descripcion, tipoCobro, precio, activo, usuario, administrador) {
  const form = document.getElementById('formEditar');

  form.action = form.dataset.action.replace('__ID__', id);

  document.getElementById('e_nombre').value        = nombre;
  document.getElementById('e_descripcion').value   = descripcion ?? '';
  document.getElementById('e_tipo_cobro').value    = tipoCobro;
  document.getElementById('e_precio').value        = precio;
  document.getElementById('e_activo').value        = activo;
  document.getElementById('e_usuario').value       = usuario;
  document.getElementById('e_administrador').value = administrador;

  document.getElementById('modalEditar').showModal();
}

/* ---------- TOAST DE ÉXITO (después de redirect) ---------- */
@if(session('success'))
  Swal.fire({
    toast: true,
    position: 'top-end',
    icon: 'success',
    title: @js(session('success')),
    showConfirmButton: false,
    timer: 3000,
    timerProgressBar: true
  });
@endif

/* ---------- ERRORES DE VALIDACIÓN ---------- */
@if($errors->any())
  Swal.fire({
    icon: 'error',
    title: 'Revisa el formulario',
    html: `{!! implode('<br>', $errors->all()) !!}`,
    confirmButtonText: 'Entendido'
  });
@endif

/* ---------- CONFIRMAR ELIMINAR ---------- */
document.querySelectorAll('.form-eliminar').forEach(function (form) {
  form.addEventListener('submit', function (e) {
    e.preventDefault();

    Swal.fire({
      title: '¿Eliminar servicio?',
      text: 'Se eliminará "' + form.dataset.nombre + '". Esta acción no se puede deshacer.',
      icon: 'warning',
      showCancelButton: true,
      confirmButtonColor: '#d33',
      cancelButtonColor: '#6c757d',
      confirmButtonText: 'Sí, eliminar',
      cancelButtonText: 'Cancelar'
    }).then(function (result) {
      if (result.isConfirmed) {
        form.submit();
      }
    });
  });
});

/* ---------- CONFIRMAR EDITAR ---------- */
(function () {
  const formEditar = document.getElementById('formEditar');
  if (!formEditar) return;

  formEditar.addEventListener('submit', function (e) {
    e.preventDefault();

    Swal.fire({
      title: '¿Guardar cambios?',
      text: 'Se modificará el servicio "' + document.getElementById('e_nombre').value + '".',
      icon: 'question',
      showCancelButton: true,
      confirmButtonColor: '#3085d6',
      cancelButtonColor: '#6c757d',
      confirmButtonText: 'Sí, modificar',
      cancelButtonText: 'Cancelar'
    }).then(function (result) {
      if (result.isConfirmed) {
        formEditar.submit();
      }
    });
  });
})();
</script>
@endsection