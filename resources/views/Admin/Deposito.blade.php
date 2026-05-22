@extends('layouts.Admin')
@section('Titulo', 'Control de Garantías')

@section('css')
<link rel="stylesheet" href="{{ asset('css/Categorias.css') }}">
<style>
  /* Efecto visual para saber que el número se puede clickear */
  .monto-link {
    cursor: pointer;
    padding: 6px 12px;
    border-radius: 4px;
    transition: all 0.2s ease;
    display: inline-block;
  }
  /* Al pasar el cursor, se ilumina con un fondo azul claro estético */
  .monto-link:hover {
    background-color: #e0f2fe;
    color: #0284c7;
  }
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
</style>
@endsection

@section('contenido')
<main class="main">

  <div class="head" style="display: flex; justify-content: space-between; align-items: center;">
    <h1 class="h1">Matriz de Garantías (Depósitos)</h1>
    <button onclick="document.getElementById('modalNuevo').showModal()" style="background: #dc2626; color: white; padding: 10px 20px; border: none; border-radius: 5px; cursor: pointer; font-weight: bold;">+ Nueva Garantía</button>
  </div>

  @if(session('success'))
    <div class="toast" style="background: #10b981; color: white; padding: 10px; margin-bottom: 15px; border-radius: 5px;">{{ session('success') }}</div>
  @endif

  <section class="card">
    <table class="table">
      <thead>
        <tr>
          <th style="text-align: left;">Categoría de Auto</th>
          {{-- Columnas dinámicas para cada paquete de seguro --}}
          @if($depositos->count() > 0)
            @foreach($depositos->unique('seguro_nombre')->sortBy('id_paquete') as $p)
              <th style="text-align: center;">{{ $p->seguro_nombre }}</th>
            @endforeach
          @endif
        </tr>
      </thead>

      <tbody>
        {{-- Agrupamos las filas para armar la cuadrícula compacta --}}
        @foreach($depositos->groupBy('categoria_nombre') as $categoriaNombre => $grupoDepositos)
          <tr>
            <td><strong>{{ $categoriaNombre }}</strong></td>
            
            {{-- Mostramos los montos directamente como elementos interactivos --}}
            @foreach($grupoDepositos->sortBy('id_paquete') as $dep)
              <td style="text-align: center;">
                <span class="mono monto-link" 
                      style="font-weight: bold; font-size: 15px;"
                      onclick="openEdit(
                        {{ $dep->id_deposito }}, 
                        @js($categoriaNombre), 
                        @js($dep->seguro_nombre), 
                        {{ $dep->monto }}
                      )">
                  ${{ number_format($dep->monto, 0) }}
                </span>
              </td>
            @endforeach
          </tr>
        @endforeach
      </tbody>
    </table>
  </section>

</main>

{{-- =========================
    MODAL CREAR GARANTÍA
========================= --}}
<dialog id="modalNuevo" class="modal">
  <form method="POST" action="{{ route('depositos.store') }}" class="modal-box">
    @csrf
    
    <div class="modal-head">
      <h2>Agregar Garantía Manual</h2>
      <button type="button" class="x" onclick="document.getElementById('modalNuevo').close()">✕</button>
    </div>

    <label class="label">Categoría de Auto</label>
    <select name="id_categoria" class="input" required>
        <option value="">-- Seleccione una categoría --</option>
        @foreach($todasCategorias as $cat)
            <option value="{{ $cat->id_categoria }}">{{ $cat->nombre }}</option>
        @endforeach
    </select>

    <label class="label">Paquete de Seguro</label>
    <select name="id_paquete" class="input" required>
        <option value="">-- Seleccione un paquete --</option>
        @foreach($todosPaquetes as $paq)
            <option value="{{ $paq->id_paquete }}">{{ $paq->nombre }}</option>
        @endforeach
    </select>

    <label class="label">Monto de Depósito ($)</label>
    <input class="input mono" name="monto" type="number" step="0.01" min="0" required>

    <div class="modal-actions" style="margin-top: 15px;">
      <button class="btn-add" type="submit">Guardar</button>
      <button class="btn-ghost" type="button" onclick="document.getElementById('modalNuevo').close()">Cancelar</button>
    </div>
  </form>
</dialog>

{{-- =========================
    MODAL EDITAR GARANTÍA
========================= --}}
<dialog id="modalEditar" class="modal">
  <form method="POST" 
        id="formEditar" 
        class="modal-box" 
        data-action="{{ url('admin/depositos') }}/__ID__">
    @csrf
    @method('PUT')
    
    <div class="modal-head">
      <h2>Modificar Garantía</h2>
      <button type="button" class="x" onclick="modalEditar.close()">✕</button>
    </div>

    <input type="hidden" id="delete_id">

    <label class="label">Categoría de Auto</label>
    <input class="input" id="view_categoria" readonly style="background: #f1f5f9; cursor: not-allowed;">

    <label class="label">Paquete de Seguro</label>
    <input class="input" id="view_seguro" readonly style="background: #f1f5f9; cursor: not-allowed; color: #0369a1; font-weight: bold;">

    <label class="label">Monto de Depósito ($)</label>
    <input class="input mono" 
           id="e_monto" 
           name="monto" 
           type="number" 
           step="1" 
           min="0" 
           required>

    <div class="modal-actions" style="display: flex; gap: 10px; margin-top: 15px;">
      <button class="btn-add" type="submit">Actualizar</button>
      <button type="button" class="btn-danger" onclick="eliminarDeposito()">Eliminar</button>
      <button class="btn-ghost" type="button" onclick="modalEditar.close()">Cancelar</button>
    </div>
  </form>

  <form id="formEliminar" method="POST" style="display: none;">
      @csrf
      @method('DELETE')
  </form>
</dialog>

{{-- =========================
    JS INLINE MATCHING
========================= --}}
<script>
function openEdit(id, categoria, seguro, monto) {
  const form = document.getElementById('formEditar');
  
  // Modifica la acción dinámica con el ID de la celda cliqueada
  form.action = form.dataset.action.replace('__ID__', id);
  
  // Guardamos el ID por si el usuario decide eliminar
  document.getElementById('delete_id').value = id;

  // Carga los datos informativos en el modal
  document.getElementById('view_categoria').value = categoria;
  document.getElementById('view_seguro').value = seguro;
  document.getElementById('e_monto').value = monto;

  // Abre el modal nativo
  document.getElementById('modalEditar').showModal();
}

function eliminarDeposito() {
    if(confirm('¿Estás seguro de que deseas eliminar este depósito de la matriz?')) {
        let id = document.getElementById('delete_id').value;
        let formDelete = document.getElementById('formEliminar');
        formDelete.action = "{{ url('admin/depositos') }}/" + id;
        formDelete.submit();
    }
}
</script>
@endsection