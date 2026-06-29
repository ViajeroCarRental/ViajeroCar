@extends('layouts.Admin')
@section('Titulo', 'Control de Garantías')

@section('css')
<link rel="stylesheet" href="{{ asset('css/Categorias.css') }}">
<style>
  /* =========================================
     RESTAURACIÓN DE ESTILOS Y CLASES NUEVAS
  ========================================= */
  .head-container {
    display: flex;
    justify-content: space-between;
    align-items: center;
  }
  .btn-primary {
    background: #dc2626;
    color: white;
    padding: 10px 20px;
    border: none;
    border-radius: 5px;
    cursor: pointer;
    font-weight: bold;
    transition: background 0.2s ease;
  }
  .btn-primary:hover {
    background: #b91c1c;
  }
  .text-left { text-align: left; }
  .text-center { text-align: center; }
  .font-bold { font-weight: bold; }

  /* Estilos originales de la tabla y enlaces */
  .monto-link {
    cursor: pointer;
    padding: 6px 12px;
    border-radius: 4px;
    transition: all 0.2s ease;
    display: inline-block;
    font-weight: bold;
    font-size: 15px;
  }
  .monto-link:hover {
    background-color: #e0f2fe;
    color: #0284c7;
  }
  .monto-vacio {
    color: #94a3b8;
    font-style: italic;
    cursor: pointer;
    font-size: 13px;
    padding: 6px;
  }
  .monto-vacio:hover {
    color: #64748b;
    text-decoration: underline;
  }

  /* Headers clickeables */
  .header-click {
    cursor: pointer;
  }
  .header-click:hover {
    text-decoration: underline;
  }

  /* Botones de acción */
  .btn-danger {
    background: transparent;
    border: 1px solid #ef4444;
    color: #ef4444;
    padding: 8px 16px;
    border-radius: 4px;
    cursor: pointer;
    transition: 0.2s;
  }
  .btn-danger:hover {
    background: #ef4444;
    color: white;
  }

  /* Campos del modal masivo */
  .bulk-container {
    max-height: 450px;
    overflow-y: auto;
    margin-top: 15px;
  }
  .bulk-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 8px;
    padding: 8px;
    border-bottom: 1px solid #f1f5f9;
  }
  .bulk-row span { font-weight: 500; font-size: 14px; }
  .bulk-row input { width: 120px; }

  /* =========================================
     PAGINACIÓN (SOLO PREVIOUS Y NEXT EN LÍNEA)
  ========================================= */
  .pagination-box {
    margin-top: 25px;
    display: flex;
    justify-content: center;
    width: 100%;
  }

  /* Ocultar el bloque de escritorio que contiene textos y números */
  .pagination-box nav > div:last-child {
    display: none !important;
  }

  /* Mostrar el bloque móvil que tiene solo Prev/Next y ponerlo en línea */
  .pagination-box nav > div:first-child {
    display: flex !important;
    justify-content: center !important;
    gap: 15px; /* Espacio entre los dos botones */
    width: 100%;
  }

  /* Estilos tipo botón para Previous y Next */
  .pagination-box nav > div:first-child > * {
    display: inline-flex;
    align-items: center;
    padding: 10px 20px;
    background: white;
    border: 1px solid #e2e8f0;
    border-radius: 6px;
    color: #475569;
    text-decoration: none;
    font-size: 14px;
    font-weight: 600;
    transition: all 0.2s ease;
    box-shadow: 0 2px 4px rgba(0,0,0,0.05);
  }

  /* Efecto Hover para botones activos */
  .pagination-box nav > div:first-child > a:hover {
    background-color: #f8fafc;
    color: #ef4444;
    border-color: #ef4444;
  }

  /* Estilo para cuando estás en la primera o última página (Botón deshabilitado) */
  .pagination-box nav > div:first-child > span {
    color: #94a3b8;
    cursor: not-allowed;
    background-color: #f1f5f9;
    box-shadow: none;
  }
</style>
@endsection

@section('contenido')
<main class="main">

  <div class="head head-container">
    <h1 class="h1">Matriz de Garantías (Depósitos)</h1>
    <button onclick="document.getElementById('modalNuevo').showModal()" class="btn-primary">
      + Actualizar Garantía
    </button>
  </div>

  <section class="card">
    <table class="table">
      <thead>
        <tr>
          <th class="text-left">Categoría de Auto</th>
          @foreach($todosPaquetes as $paq)
            <th class="text-center">
                <span class="header-click" onclick="openBulkPackage({{ $paq->id_paquete }}, @js($paq->nombre))">
                    {{ $paq->nombre }}
                </span>
            </th>
          @endforeach
        </tr>
      </thead>

      <tbody>
        @foreach($todasCategorias as $cat)
          <tr>
            <td>
                <span class="header-click font-bold" onclick="openBulkCategory({{ $cat->id_categoria }}, @js($cat->nombre))">
                    {{ $cat->nombre }}
                </span>
            </td>
            @foreach($todosPaquetes as $paq)
              @php
                $dep = $matriz[$cat->id_categoria][$paq->id_paquete] ?? null;
              @endphp
              <td class="text-center">
                @if($dep)
                  <span class="mono monto-link"
                        onclick="openEdit(
                          {{ $dep->id_deposito }},
                          @js($cat->nombre),
                          @js($paq->nombre),
                          {{ $dep->monto }}
                        )">
                    ${{ number_format($dep->monto, 0) }}
                  </span>
                @else
                  <span class="monto-vacio" onclick="openCreate({{ $cat->id_categoria }}, {{ $paq->id_paquete }}, @js($cat->nombre), @js($paq->nombre))">
                    Sin asignar
                  </span>
                @endif
              </td>
            @endforeach
          </tr>
        @endforeach
      </tbody>
    </table>
  </section>

  {{-- Paginación --}}
  @if($todasCategorias->hasPages())
  <div class="pagination-box">
      {{ $todasCategorias->links() }}
  </div>
  @endif

</main>

{{-- =========================
    MODAL INDIVIDUAL
========================= --}}
<dialog id="modalNuevo" class="modal">
  <form method="POST" action="{{ route('depositos.store') }}" class="modal-box" id="formNuevo">
    @csrf
    <div class="modal-head">
      <h2>Actualizar Garantía</h2>
      <button type="button" class="x" onclick="document.getElementById('modalNuevo').close()">✕</button>
    </div>

    <label class="label">Categoría de Auto</label>
    <select name="id_categoria" id="n_id_categoria" class="input" required>
        <option value="">-- Seleccione una categoría --</option>
        @foreach($todasCategorias as $cat)
            <option value="{{ $cat->id_categoria }}">{{ $cat->nombre }}</option>
        @endforeach
    </select>

    <label class="label">Paquete de Seguro</label>
    <select name="id_paquete" id="n_id_paquete" class="input" required>
        <option value="">-- Seleccione un paquete --</option>
        @foreach($todosPaquetes as $paq)
            <option value="{{ $paq->id_paquete }}">{{ $paq->nombre }}</option>
        @endforeach
    </select>

    <label class="label">Monto de Depósito ($)</label>
    <input class="input mono" name="monto" type="number" step="1" min="0" required>

    <div class="modal-actions" style="margin-top: 15px;">
      <button class="btn-add" type="submit">Guardar Cambios</button>
      <button class="btn-ghost" type="button" onclick="document.getElementById('modalNuevo').close()">Cancelar</button>
    </div>
  </form>
</dialog>

{{-- =========================
    MODAL MASIVO (DINÁMICO)
========================= --}}
<dialog id="modalBulk" class="modal">
  <form method="POST" action="{{ route('depositos.store') }}" class="modal-box" id="formBulk" style="max-width: 500px;">
    @csrf
    <input type="hidden" name="bulk" value="1">

    <div class="modal-head">
      <h2 id="bulkTitle">Actualización Masiva</h2>
      <button type="button" class="x" onclick="document.getElementById('modalBulk').close()">✕</button>
    </div>

    <div id="bulkContainer" class="bulk-container">
        </div>

    <div class="modal-actions" style="margin-top: 20px;">
      <button class="btn-add" type="submit">Actualizar</button>
      <button class="btn-ghost" type="button" onclick="document.getElementById('modalBulk').close()">Cancelar</button>
    </div>
  </form>
</dialog>

{{-- =========================
    MODAL EDITAR EXISTENTE
========================= --}}
<dialog id="modalEditar" class="modal">
  <form method="POST" id="formEditar" class="modal-box" data-action="{{ url('admin/depositos') }}/__ID__">
    @csrf @method('PUT')
    <div class="modal-head">
      <h2>Modificar Garantía</h2>
      <button type="button" class="x" onclick="document.getElementById('modalEditar').close()">✕</button>
    </div>

    <input type="hidden" id="delete_id">

    <label class="label">Categoría de Auto</label>
    <input class="input" id="view_categoria" readonly style="background: #f1f5f9; cursor: not-allowed;">

    <label class="label">Paquete de Seguro</label>
    <input class="input" id="view_seguro" readonly style="background: #f1f5f9; cursor: not-allowed; color: #0369a1; font-weight: bold;">

    <label class="label">Monto de Depósito ($)</label>
    <input class="input mono" id="e_monto" name="monto" type="number" step="1" min="0" required>

    <div class="modal-actions" style="display: flex; gap: 10px; margin-top: 15px;">
      <button class="btn-add" type="submit">Actualizar</button>
      <button type="button" class="btn-danger" onclick="eliminarDeposito()">Eliminar</button>
      <button class="btn-ghost" type="button" onclick="document.getElementById('modalEditar').close()">Cancelar</button>
    </div>
  </form>

  <form id="formEliminar" method="POST" style="display: none;">
    @csrf @method('DELETE')
  </form>
</dialog>

{{-- =========================
    SWEETALERT2 CDN
========================= --}}
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
/* ---------- PARCHE GLOBAL: cerrar dialogs antes de cualquier Swal ---------- */
/* Los <dialog> abiertos con showModal() viven en la top-layer del navegador,
   por encima de SweetAlert2. Esto los cierra antes de mostrar cualquier alerta. */
const _swalFire = Swal.fire.bind(Swal);
Swal.fire = function (...args) {
  document.querySelectorAll('dialog[open]').forEach(function (d) { d.close(); });
  return _swalFire(...args);
};

const matrizData = @js($matriz);
const categoriasData = @js($todasCategorias->items());
const paquetesData = @js($todosPaquetes);

function openBulkPackage(idPaq, nombrePaq) {
    document.getElementById('bulkTitle').innerText = "Editar Seguro: " + nombrePaq;
    const container = document.getElementById('bulkContainer');
    container.innerHTML = '';

    categoriasData.forEach((cat, index) => {
        const monto = (matrizData[cat.id_categoria] && matrizData[cat.id_categoria][idPaq]) ? matrizData[cat.id_categoria][idPaq].monto : 0;
        container.innerHTML += `
            <div class="bulk-row">
                <span>${cat.nombre}</span>
                <input type="hidden" name="items[${index}][id_categoria]" value="${cat.id_categoria}">
                <input type="hidden" name="items[${index}][id_paquete]" value="${idPaq}">
                <input class="input mono" name="items[${index}][monto]" type="number" value="${monto}" step="1">
            </div>
        `;
    });

    document.getElementById('modalBulk').showModal();
}

function openBulkCategory(idCat, nombreCat) {
    document.getElementById('bulkTitle').innerText = "Editar Categoría: " + nombreCat;
    const container = document.getElementById('bulkContainer');
    container.innerHTML = '';

    paquetesData.forEach((paq, index) => {
        const monto = (matrizData[idCat] && matrizData[idCat][paq.id_paquete]) ? matrizData[idCat][paq.id_paquete].monto : 0;
        container.innerHTML += `
            <div class="bulk-row">
                <span>${paq.nombre}</span>
                <input type="hidden" name="items[${index}][id_categoria]" value="${idCat}">
                <input type="hidden" name="items[${index}][id_paquete]" value="${paq.id_paquete}">
                <input class="input mono" name="items[${index}][monto]" type="number" value="${monto}" step="1">
            </div>
        `;
    });

    document.getElementById('modalBulk').showModal();
}

function openCreate(idCat, idPaq, catNombre, paqNombre) {
    document.getElementById('n_id_categoria').value = idCat;
    document.getElementById('n_id_paquete').value = idPaq;
    document.getElementById('modalNuevo').showModal();
}

function openEdit(id, categoria, seguro, monto) {
  const form = document.getElementById('formEditar');
  form.action = form.dataset.action.replace('__ID__', id);
  document.getElementById('delete_id').value = id;
  document.getElementById('view_categoria').value = categoria;
  document.getElementById('view_seguro').value = seguro;
  document.getElementById('e_monto').value = monto;
  document.getElementById('modalEditar').showModal();
}

/* ---------- ELIMINAR DEPÓSITO (con SweetAlert2) ---------- */
function eliminarDeposito() {
    const categoria = document.getElementById('view_categoria').value;
    const seguro    = document.getElementById('view_seguro').value;

    Swal.fire({
      title: '¿Eliminar garantía?',
      text: 'Se eliminará el monto de "' + categoria + ' / ' + seguro + '". Esta acción no se puede deshacer.',
      icon: 'warning',
      showCancelButton: true,
      confirmButtonColor: '#d33',
      cancelButtonColor: '#6c757d',
      confirmButtonText: 'Sí, eliminar',
      cancelButtonText: 'Cancelar'
    }).then(function (result) {
      if (result.isConfirmed) {
        const formDelete = document.getElementById('formEliminar');
        formDelete.action = "{{ url('admin/depositos') }}/" + document.getElementById('delete_id').value;
        formDelete.submit();
      }
    });
}

/* ---------- CONFIRMAR EDITAR ---------- */
(function () {
  const formEditar = document.getElementById('formEditar');
  if (!formEditar) return;

  formEditar.addEventListener('submit', function (e) {
    e.preventDefault();

    Swal.fire({
      title: '¿Guardar cambios?',
      text: 'Se modificará la garantía de "' + document.getElementById('view_categoria').value + '".',
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

/* ---------- TOAST DE ÉXITO (centrado, NO en la esquina) ---------- */
@if(session('success'))
  Swal.fire({
    icon: 'success',
    title: 'Listo',
    text: @js(session('success')),
    confirmButtonText: 'Aceptar',
    confirmButtonColor: '#10b981'
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
</script>
@endsection