@extends('layouts.Admin')

@section('Titulo', 'Administración de Tarifas y Rutas')

@section('css')
<link rel="stylesheet" href="{{ asset('css/Categorias.css') }}">
<style>
  /* =======================================
     SISTEMA DE PESTAÑAS (TABS)
  ======================================= */
  .tabs-nav {
    display: flex;
    gap: 10px;
    margin-top: 30px;
    border-bottom: 2px solid #e2e8f0;
    padding-bottom: 0;
  }
  .tab-btn {
    background: transparent;
    border: none;
    padding: 12px 24px;
    font-size: 16px;
    font-weight: bold;
    color: #64748b;
    cursor: pointer;
    border-bottom: 3px solid transparent;
    margin-bottom: -2px;
    transition: all 0.3s;
  }
  .tab-btn:hover { color: #0f172a; }
  .tab-btn.active {
    color: #b22222;
    border-bottom-color: #b22222;
  }
  .tab-content {
    display: none;
    padding-top: 20px;
    animation: fadeIn 0.3s ease;
  }
  .tab-content.active { display: block; }
  @keyframes fadeIn { from { opacity: 0; transform: translateY(5px); } to { opacity: 1; transform: translateY(0); } }

  /* =======================================
     DISEÑO PREMIUM Y FILTROS
  ======================================= */
  .badge-tipo {
    padding: 6px 12px;
    border-radius: 20px;
    font-size: 12px;
    font-weight: 800;
    text-transform: uppercase;
    display: inline-flex;
    align-items: center;
    gap: 5px;
  }
  .badge-delivery { background: #e0f2fe; color: #0284c7; }
  .badge-dropoff { background: #f3e8ff; color: #9333ea; }

  .filtros-container {
    background: #ffffff;
    border: 1px solid #e2e8f0;
    padding: 15px 20px;
    border-radius: 10px;
    display: flex;
    align-items: center;
    flex-wrap: wrap;
    gap: 20px;
    box-shadow: 0 4px 6px rgba(0,0,0,0.02);
    margin-bottom: 20px;
  }
  .filtro-item {
    display: flex;
    flex-direction: column;
    gap: 5px;
    flex: 1;
    min-width: 200px;
  }
  .filtro-item label {
    font-weight: bold;
    color: #475569;
    font-size: 13px;
    margin: 0;
  }
  .filtro-item select {
    margin: 0;
    padding: 8px 10px;
    border-color: #cbd5e1;
    border-radius: 6px;
    outline: none;
    width: 100%;
  }
  .divisor-vertical {
    width: 2px;
    height: 40px;
    background: #e2e8f0;
  }

  /* Botones de Acción en Tabla */
  .btn-grupo { display: flex; gap: 8px; justify-content: center; }
  .btn-icon {
    padding: 6px 12px;
    border: none;
    border-radius: 6px;
    font-size: 13px;
    font-weight: bold;
    cursor: pointer;
    transition: all 0.2s;
  }
  .btn-edit { background: #fef08a; color: #854d0e; }
  .btn-edit:hover { background: #fde047; }
  .btn-del { background: #fecaca; color: #991b1b; }
  .btn-del:hover { background: #fca5a5; }
  .celda-costo {
    background: #f8fafc;
    font-weight: 900;
    color: #0f172a;
    font-size: 16px;
    border-left: 2px solid #e2e8f0;
  }
</style>
@endsection

@section('contenido')
<main class="main">

  <div class="head" style="display: flex; justify-content: space-between; align-items: center;">
    <div>
        <h1 class="h1" style="margin-bottom: 5px;">Dropoff y Delivery</h1>
    </div>

    <div style="display: flex; gap: 12px;">
        <button onclick="document.getElementById('modalUbicacion').showModal()" style="background: #0284c7; color: white; padding: 12px 20px; border: none; border-radius: 8px; cursor: pointer; font-weight: bold;">
            + Nuevo Destino (Delivery)
        </button>
        <button onclick="document.getElementById('modalTarifa').showModal()" style="background: #9333ea; color: white; padding: 12px 20px; border: none; border-radius: 8px; cursor: pointer; font-weight: bold;">
            + Nueva Ruta (Dropoff)
        </button>
    </div>
  </div>

  <div class="tabs-nav">
      <button class="tab-btn active" onclick="switchTab('tab-categorias', this)">1. Categorías (Costo x KM)</button>
      <button class="tab-btn" onclick="switchTab('tab-viajes', this)">2. Matriz de Rutas y Viajes</button>
  </div>

  <div id="tab-categorias" class="tab-content active">
      <section class="card" style="border-radius: 12px; overflow: hidden; box-shadow: 0 4px 15px rgba(0,0,0,0.03);">
        <table class="table">
          <thead style="background: #f8fafc;">
            <tr>
              <th>Código</th>
              <th>Categoría de Auto</th>
              <th>Costo Base x KM</th>
              <th style="text-align: center;">Acción</th>
            </tr>
          </thead>
          <tbody>
            @foreach($categorias as $cat)
                <tr>
                    <td><strong style="color: #475569;">{{ $cat->codigo ?? 'N/A' }}</strong></td>
                    <td>{{ $cat->nombre }}</td>
                    <td class="mono" style="color: #166534; font-weight: bold; font-size: 15px;">${{ number_format($cat->costo_km ?? 0, 2) }}</td>
                    <td style="text-align: center;">
                        <button class="btn-icon btn-edit" onclick="openEditCategoria({{ $cat->id_categoria }}, @js($cat->nombre), {{ $cat->costo_km ?? 0 }})">
                            ✎ Ajustar Costo
                        </button>
                    </td>
                </tr>
            @endforeach
          </tbody>
        </table>
      </section>
  </div>

  <div id="tab-viajes" class="tab-content">

      <div class="filtros-container">
          <div class="filtro-item">
              <label>📍 Origen</label>
              <select id="filtroOrigen" onchange="aplicarFiltros()">
                  <option value="todos">Todos los orígenes</option>
              </select>
          </div>

          <div class="filtro-item">
              <label>🚩 Destino</label>
              <select id="filtroDestino" onchange="aplicarFiltros()">
                  <option value="todos">Todos los destinos</option>
              </select>
          </div>

          <div class="divisor-vertical"></div>

          <div class="filtro-item" style="flex: 1.5;">
              <label>🚗 Simular Precio (Categoría de Auto)</label>
              <select id="filtroCategoria" onchange="aplicarFiltros()">
                  <option value="0" data-costokm="0">-- Seleccione una categoría --</option>
                  @foreach($categorias as $cat)
                      <option value="{{ $cat->id_categoria }}" data-costokm="{{ $cat->costo_km ?? 0 }}">
                          {{ $cat->nombre }} (${{ number_format($cat->costo_km ?? 0, 2) }}/km)
                      </option>
                  @endforeach
              </select>
          </div>
      </div>

      <section class="card" style="border-radius: 12px; overflow: hidden; box-shadow: 0 4px 15px rgba(0,0,0,0.03);">
        <table class="table">
          <thead style="background: #f8fafc;">
            <tr>
              <th>Modalidad</th>
              <th>📍 Origen</th>
              <th>🚩 Destino</th>
              <th>Distancia / Regla</th>
              <th style="text-align: right; padding-right: 20px;">Costo Estimado</th>
              <th style="text-align: center;">Acciones</th>
            </tr>
          </thead>
          <tbody>

            {{-- RUTAS DE DELIVERY --}}
            @if(isset($ubicaciones))
                @foreach($ubicaciones as $u)
                    <tr class="fila-viaje" data-origen="querétaro base" data-destino="{{ strtolower($u->destino) }}" data-tipo="delivery" data-km="{{ $u->km }}">
                        <td><span class="badge-tipo badge-delivery">🚚 Delivery</span></td>
                        <td style="color: #64748b; font-weight: bold;">Querétaro Base</td>
                        <td><strong style="color: #0f172a;">{{ $u->destino }}</strong> <br><small style="color: #94a3b8;">{{ $u->estado ?? '' }}</small></td>
                        <td>{{ $u->km }} km</td>
                        <td class="celda-costo" style="text-align: right; padding-right: 20px; color: #94a3b8; font-weight: normal;">
                            Seleccione auto...
                        </td>
                        <td>
                            <div class="btn-grupo">
                                <button type="button" class="btn-icon btn-del"
                                    onclick="confirmarEliminar('delivery', {{ $u->id_ubicacion }}, @js($u->destino))">🗑️</button>
                            </div>
                        </td>
                    </tr>
                @endforeach
            @endif

            {{-- RUTAS DE DROPOFF --}}
            @if(isset($tarifas))
                @foreach($tarifas as $t)
                    <tr class="fila-viaje" data-origen="{{ strtolower($t->origen_nombre ?? '') }}" data-destino="{{ strtolower($t->destino_nombre ?? '') }}" data-tipo="dropoff" data-cobro="{{ $t->tipo_cobro }}" data-fijo="{{ $t->monto_base ?? 0 }}" data-km="{{ $t->monto_por_km ?? 0 }}">
                        <td><span class="badge-tipo badge-dropoff">🏢 Dropoff</span></td>
                        <td style="color: #0f172a; font-weight: bold;">{{ $t->origen_nombre ?? 'N/A' }}</td>
                        <td><strong style="color: #0f172a;">{{ $t->destino_nombre ?? 'N/A' }}</strong></td>
                        <td>
                            @if($t->tipo_cobro == 'fijo')
                                <span style="color: #ea580c; font-weight: bold;">Tarifa Fija</span>
                            @else
                                {{ $t->monto_por_km }} km (Variable)
                            @endif
                        </td>
                        <td class="celda-costo" style="text-align: right; padding-right: 20px; color: #94a3b8; font-weight: normal;">
                            @if($t->tipo_cobro == 'fijo')
                                <span style="color: #b22222; font-weight: 900;">${{ number_format($t->monto_base, 2) }}</span>
                            @else
                                Seleccione auto...
                            @endif
                        </td>
                        <td>
                            <div class="btn-grupo">
                                <button type="button" class="btn-icon btn-del"
                                    onclick="confirmarEliminar('dropoff', {{ $t->id_tarifa ?? 0 }}, @js(($t->origen_nombre ?? '') . ' → ' . ($t->destino_nombre ?? '')))">🗑️</button>
                            </div>
                        </td>
                    </tr>
                @endforeach
            @endif

          </tbody>
        </table>
      </section>
  </div>

</main>

{{-- ==========================================
     MODALES PARA CREAR
=========================================== --}}

{{-- Modal Delivery --}}
<dialog id="modalUbicacion" class="modal">
  <form id="formUbicacion" class="modal-box">
    <div class="modal-head"><h2>Nuevo Punto (Delivery)</h2><button type="button" class="x" onclick="document.getElementById('modalUbicacion').close()">✕</button></div>

    <label class="label">Estado</label>
    <input type="text" id="ub_estado" name="estado" class="input" required placeholder="Ej: Querétaro">

    <label class="label">Destino Especial</label>
    <input type="text" id="ub_destino" name="destino" class="input" required placeholder="Ej: Hotel Real de Minas">

    <label class="label">Distancia Total (Kilómetros)</label>
    <input type="number" id="ub_km" name="km" class="input mono" step="0.1" min="0" required placeholder="Ej: 15.5">

    <div class="modal-actions" style="margin-top: 20px;"><button class="btn-add" style="background: #0284c7;" type="submit">Guardar Destino</button></div>
  </form>
</dialog>

{{-- Modal Dropoff --}}
<dialog id="modalTarifa" class="modal">
  <form id="formTarifa" class="modal-box">
    <div class="modal-head"><h2>Nueva Ruta (Dropoff)</h2><button type="button" class="x" onclick="document.getElementById('modalTarifa').close()">✕</button></div>

    <label class="label">Origen</label>
    <select id="tf_origen" name="id_sucursal_origen" class="input" required>
        <option value="">-- Seleccione Sucursal --</option>
        @if(isset($sucursales))
            @foreach($sucursales as $s) <option value="{{ $s->id_sucursal }}">{{ $s->nombre }}</option> @endforeach
        @endif
    </select>

    <label class="label">Destino</label>
    <select id="tf_destino" name="id_sucursal_destino" class="input" required>
        <option value="">-- Seleccione Sucursal --</option>
        @if(isset($sucursales))
            @foreach($sucursales as $s) <option value="{{ $s->id_sucursal }}">{{ $s->nombre }}</option> @endforeach
        @endif
    </select>

    <label class="label">Modalidad de Cobro</label>
    <select id="tf_tipo_cobro" name="tipo_cobro" class="input" required>
        <option value="variable">Cobro por Distancia (Km x Categoria)</option>
        <option value="fijo">Cobro Fijo (Sin importar el auto)</option>
    </select>

    <div style="display: flex; gap: 10px;">
        <div style="flex: 1;">
            <label class="label">Monto Fijo ($)</label>
            <input type="number" id="tf_monto_base" name="monto_base" class="input mono" step="0.01" min="0" value="0">
        </div>
        <div style="flex: 1;">
            <label class="label">Distancia (KM)</label>
            <input type="number" id="tf_monto_km" name="monto_por_km" class="input mono" step="0.1" min="0" value="0">
        </div>
    </div>

    <div class="modal-actions" style="margin-top: 20px;"><button class="btn-add" style="background: #9333ea;" type="submit">Guardar Ruta</button></div>
  </form>
</dialog>

{{-- Modal Editar Costo KM Categoría --}}
<dialog id="modalCosto" class="modal">
  <form id="formCosto" class="modal-box">
    <div class="modal-head"><h2>Actualizar Costo por Kilómetro</h2><button type="button" class="x" onclick="document.getElementById('modalCosto').close()">✕</button></div>
    <input type="hidden" id="c_id_categoria">
    <label class="label">Categoría</label>
    <input type="text" id="c_nombre" class="input" readonly style="background: #f1f5f9;">
    <label class="label">Costo por Kilómetro ($)</label>
    <input type="number" id="c_costo" name="costo_km" class="input mono" step="0.01" min="0" required>
    <div class="modal-actions" style="margin-top: 20px;"><button class="btn-add" style="background: #f59e0b;" type="submit">Actualizar Costo</button></div>
  </form>
</dialog>

@endsection

@section('js')
{{-- SweetAlert2 CDN --}}
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<meta name="csrf-token" content="{{ csrf_token() }}">
<script>
    const CSRF = document.querySelector('meta[name="csrf-token"]').content;

    // ==========================================
    // PARCHE GLOBAL: cerrar dialogs antes de cualquier Swal
    // ==========================================
    const _swalFire = Swal.fire.bind(Swal);
    Swal.fire = function (...args) {
        document.querySelectorAll('dialog[open]').forEach(function (d) { d.close(); });
        return _swalFire(...args);
    };

    // Helper fetch JSON con FormData
    function postForm(url, dataObj) {
        const form = new FormData();
        form.append('_token', CSRF);
        Object.keys(dataObj).forEach(k => form.append(k, dataObj[k]));
        return fetch(url, { method: 'POST', body: form }).then(r => r.json());
    }

    // ==========================================
    // NAVEGACIÓN ENTRE TABS
    // ==========================================
    function switchTab(tabId, btnElement) {
        document.querySelectorAll('.tab-content').forEach(tab => tab.classList.remove('active'));
        document.querySelectorAll('.tab-btn').forEach(btn => btn.classList.remove('active'));
        document.getElementById(tabId).classList.add('active');
        btnElement.classList.add('active');
    }

    // ==========================================
    // LLENAR SELECTS DE ORIGEN Y DESTINO
    // ==========================================
    document.addEventListener("DOMContentLoaded", () => {
        const selectOrigen = document.getElementById('filtroOrigen');
        const selectDestino = document.getElementById('filtroDestino');
        const filas = document.querySelectorAll('.fila-viaje');

        let origenes = new Set();
        let destinos = new Set();

        filas.forEach(fila => {
            if(fila.dataset.origen) origenes.add(fila.dataset.origen);
            if(fila.dataset.destino) destinos.add(fila.dataset.destino);
        });

        origenes.forEach(origen => {
            let option = document.createElement("option");
            option.value = origen;
            option.text = origen.split(' ').map(w => w.charAt(0).toUpperCase() + w.slice(1)).join(' ');
            selectOrigen.appendChild(option);
        });

        destinos.forEach(destino => {
            let option = document.createElement("option");
            option.value = destino;
            option.text = destino.split(' ').map(w => w.charAt(0).toUpperCase() + w.slice(1)).join(' ');
            selectDestino.appendChild(option);
        });
    });

    // ==========================================
    // FILTRO MULTIPLE + PRECIOS
    // ==========================================
    function aplicarFiltros() {
        const valOrigen = document.getElementById('filtroOrigen').value;
        const valDestino = document.getElementById('filtroDestino').value;
        const selectCat = document.getElementById('filtroCategoria');
        const costoPorKm = parseFloat(selectCat.options[selectCat.selectedIndex].dataset.costokm) || 0;

        document.querySelectorAll('.fila-viaje').forEach(fila => {
            const origenFila = fila.dataset.origen;
            const destinoFila = fila.dataset.destino;
            const tipo = fila.dataset.tipo;
            const celdaCosto = fila.querySelector('.celda-costo');

            let mostrarOrigen = (valOrigen === "todos" || origenFila === valOrigen);
            let mostrarDestino = (valDestino === "todos" || destinoFila === valDestino);

            fila.style.display = (mostrarOrigen && mostrarDestino) ? "table-row" : "none";

            if (costoPorKm === 0) {
                if (tipo === 'dropoff' && fila.dataset.cobro === 'fijo') {
                    celdaCosto.innerText = "$" + parseFloat(fila.dataset.fijo).toFixed(2);
                    celdaCosto.style.color = "#b22222";
                    celdaCosto.style.fontWeight = "900";
                } else {
                    celdaCosto.innerText = "Seleccione auto...";
                    celdaCosto.style.color = "#94a3b8";
                    celdaCosto.style.fontWeight = "normal";
                }
                return;
            }

            celdaCosto.style.color = "#b22222";
            celdaCosto.style.fontWeight = "900";

            if (tipo === 'delivery') {
                const total = (parseFloat(fila.dataset.km) || 0) * costoPorKm;
                celdaCosto.innerText = "$" + total.toFixed(2);
            } else if (tipo === 'dropoff') {
                if (fila.dataset.cobro === 'fijo') {
                    celdaCosto.innerText = "$" + parseFloat(fila.dataset.fijo).toFixed(2);
                } else {
                    const total = (parseFloat(fila.dataset.km) || 0) * costoPorKm;
                    celdaCosto.innerText = "$" + total.toFixed(2);
                }
            }
        });
    }

    // ==========================================
    // CREAR DESTINO (DELIVERY) — fetch
    // ==========================================
    document.getElementById('formUbicacion').addEventListener('submit', function (e) {
        e.preventDefault();
        postForm("{{ url('admin/dropoff/ubicacion') }}", {
            estado:  document.getElementById('ub_estado').value,
            destino: document.getElementById('ub_destino').value,
            km:      document.getElementById('ub_km').value
        })
        .then(() => {
            Swal.fire({
                icon: 'success', title: 'Destino creado',
                text: 'El destino de delivery se creó correctamente.',
                confirmButtonText: 'Aceptar', confirmButtonColor: '#10b981'
            }).then(() => location.reload());
        })
        .catch(() => errorSwal());
    });

    // ==========================================
    // CREAR RUTA (DROPOFF) — fetch
    // ==========================================
    document.getElementById('formTarifa').addEventListener('submit', function (e) {
        e.preventDefault();
        postForm("{{ url('admin/dropoff/tarifa') }}", {
            id_sucursal_origen:  document.getElementById('tf_origen').value,
            id_sucursal_destino: document.getElementById('tf_destino').value,
            tipo_cobro:          document.getElementById('tf_tipo_cobro').value,
            monto_base:          document.getElementById('tf_monto_base').value,
            monto_por_km:        document.getElementById('tf_monto_km').value
        })
        .then(() => {
            Swal.fire({
                icon: 'success', title: 'Ruta creada',
                text: 'La ruta de dropoff se creó correctamente.',
                confirmButtonText: 'Aceptar', confirmButtonColor: '#10b981'
            }).then(() => location.reload());
        })
        .catch(() => errorSwal());
    });

    // ==========================================
    // EDITAR COSTO POR CATEGORÍA
    // ==========================================
    function openEditCategoria(id, nombre, costoActual) {
        document.getElementById('c_id_categoria').value = id;
        document.getElementById('c_nombre').value = nombre;
        document.getElementById('c_costo').value = costoActual;
        document.getElementById('modalCosto').showModal();
    }

    document.getElementById('formCosto').addEventListener('submit', function (e) {
        e.preventDefault();
        const nombre = document.getElementById('c_nombre').value;

        // cerrar el dialog antes de confirmar para que el Swal no quede detrás
        document.getElementById('modalCosto').close();

        Swal.fire({
            title: '¿Guardar cambios?',
            text: 'Se modificará el costo por km de "' + nombre + '".',
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Sí, modificar',
            cancelButtonText: 'Cancelar'
        }).then(result => {
            if (!result.isConfirmed) {
                document.getElementById('modalCosto').showModal();
                return;
            }
            // tu updateCostoKm lee id_categoria y costo_km del body
            postForm("{{ url('admin/dropoff/update-costo') }}", {
                id_categoria: document.getElementById('c_id_categoria').value,
                costo_km:     document.getElementById('c_costo').value
            })
            .then(() => {
                Swal.fire({
                    icon: 'success', title: 'Costo actualizado',
                    text: 'El costo por km se modificó correctamente.',
                    confirmButtonText: 'Aceptar', confirmButtonColor: '#10b981'
                }).then(() => location.reload());
            })
            .catch(() => errorSwal());
        });
    });

    // ==========================================
    // ELIMINAR RUTA / DESTINO — fetch
    // ==========================================
    function confirmarEliminar(tipo, id, nombre) {
        const esDelivery = (tipo === 'delivery');
        Swal.fire({
            title: esDelivery ? '¿Eliminar destino?' : '¿Eliminar ruta?',
            text: 'Se eliminará "' + nombre + '". Esta acción no se puede deshacer.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Sí, eliminar',
            cancelButtonText: 'Cancelar'
        }).then(result => {
            if (!result.isConfirmed) return;

            const url = esDelivery
                ? "{{ url('admin/dropoff/ubicacion/eliminar') }}/" + id
                : "{{ url('admin/dropoff/tarifa/eliminar') }}/" + id;

            postForm(url, {})
            .then(() => {
                Swal.fire({
                    icon: 'success',
                    title: esDelivery ? 'Destino eliminado' : 'Ruta eliminada',
                    text: 'Se eliminó correctamente.',
                    confirmButtonText: 'Aceptar', confirmButtonColor: '#10b981'
                }).then(() => location.reload());
            })
            .catch(() => errorSwal());
        });
    }

    // ==========================================
    // ERROR GENÉRICO
    // ==========================================
    function errorSwal() {
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: 'Ocurrió un error al procesar la solicitud.',
            confirmButtonText: 'Entendido'
        });
    }
</script>
@endsection