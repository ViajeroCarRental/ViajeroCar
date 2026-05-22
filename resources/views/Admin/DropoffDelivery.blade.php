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
    margin-bottom: -2px; /* Superpone el borde */
    transition: all 0.3s;
  }
  .tab-btn:hover {
    color: #0f172a;
  }
  .tab-btn.active {
    color: #b22222;
    border-bottom-color: #b22222;
  }
  .tab-content {
    display: none;
    padding-top: 20px;
    animation: fadeIn 0.3s ease;
  }
  .tab-content.active {
    display: block;
  }
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
        {{-- <p style="color: #64748b; margin-top:0; font-size: 14px;">Administra costos de Delivery (Puntos Especiales) y Dropoff (Sucursales).</p> --}}
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

  @if(session('success'))
    <div class="toast" style="background: #10b981; color: white; padding: 12px; margin-bottom: 15px; border-radius: 8px; font-weight: bold;">✓ {{ session('success') }}</div>
  @endif

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
                        <button class="btn-icon btn-edit" onclick="openEditCategoria({{ $cat->id_categoria }}, '{{ $cat->nombre }}', {{ $cat->costo_km ?? 0 }})">
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
                                <button class="btn-icon btn-del">🗑️</button>
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
                                <button class="btn-icon btn-del">🗑️</button>
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
     MODALES PARA CREAR (FORMULARIOS ACTIVOS)
=========================================== --}}

{{-- Modal Delivery --}}
<dialog id="modalUbicacion" class="modal">
  <form id="formUbicacion" class="modal-box" method="POST" action="{{ url('admin/ubicacion/store') }}">
    @csrf
    <div class="modal-head"><h2>Nuevo Punto (Delivery)</h2><button type="button" class="x" onclick="document.getElementById('modalUbicacion').close()">✕</button></div>
    
    <label class="label">Estado</label>
    <input type="text" name="estado" class="input" required placeholder="Ej: Querétaro">

    <label class="label">Destino Especial</label>
    <input type="text" name="destino" class="input" required placeholder="Ej: Hotel Real de Minas">

    <label class="label">Distancia Total (Kilómetros)</label>
    <input type="number" name="km" class="input mono" step="0.1" min="0" required placeholder="Ej: 15.5">

    <div class="modal-actions" style="margin-top: 20px;"><button class="btn-add" style="background: #0284c7;" type="submit">Guardar Destino</button></div>
  </form>
</dialog>

{{-- Modal Dropoff --}}
<dialog id="modalTarifa" class="modal">
  <form id="formTarifa" class="modal-box" method="POST" action="{{ url('admin/tarifa_dropoff/store') }}">
    @csrf
    <div class="modal-head"><h2>Nueva Ruta (Dropoff)</h2><button type="button" class="x" onclick="document.getElementById('modalTarifa').close()">✕</button></div>

    <label class="label">Origen</label>
    <select name="id_sucursal_origen" class="input" required>
        <option value="">-- Seleccione Sucursal --</option>
        @if(isset($sucursales))
            @foreach($sucursales as $s) <option value="{{ $s->id_sucursal }}">{{ $s->nombre }}</option> @endforeach
        @endif
    </select>

    <label class="label">Destino</label>
    <select name="id_sucursal_destino" class="input" required>
        <option value="">-- Seleccione Sucursal --</option>
        @if(isset($sucursales))
            @foreach($sucursales as $s) <option value="{{ $s->id_sucursal }}">{{ $s->nombre }}</option> @endforeach
        @endif
    </select>
    
    <label class="label">Modalidad de Cobro</label>
    <select name="tipo_cobro" class="input" required>
        <option value="variable">Cobro por Distancia (Km x Categoria)</option>
        <option value="fijo">Cobro Fijo (Sin importar el auto)</option>
    </select>

    <div style="display: flex; gap: 10px;">
        <div style="flex: 1;">
            <label class="label">Monto Fijo ($)</label>
            <input type="number" name="monto_base" class="input mono" step="0.01" min="0" value="0">
        </div>
        <div style="flex: 1;">
            <label class="label">Distancia (KM)</label>
            <input type="number" name="monto_por_km" class="input mono" step="0.1" min="0" value="0">
        </div>
    </div>

    <div class="modal-actions" style="margin-top: 20px;"><button class="btn-add" style="background: #9333ea;" type="submit">Guardar Ruta</button></div>
  </form>
</dialog>

{{-- Modal Editar Costo KM Categoría --}}
<dialog id="modalCosto" class="modal">
  <form id="formCosto" class="modal-box" method="POST" action="">
    @csrf @method('PUT')
    <div class="modal-head"><h2>Actualizar Costo por Kilómetro</h2><button type="button" class="x" onclick="document.getElementById('modalCosto').close()">✕</button></div>
    <label class="label">Categoría</label>
    <input type="text" id="c_nombre" class="input" readonly style="background: #f1f5f9;">
    <label class="label">Costo por Kilómetro ($)</label>
    <input type="number" id="c_costo" name="costo_km" class="input mono" step="0.01" min="0" required>
    <div class="modal-actions" style="margin-top: 20px;"><button class="btn-add" style="background: #f59e0b;" type="submit">Actualizar Costo</button></div>
  </form>
</dialog>

@endsection

@section('js')
<script>
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

        // Crear opciones para Origen
        origenes.forEach(origen => {
            let option = document.createElement("option");
            option.value = origen;
            option.text = origen.split(' ').map(w => w.charAt(0).toUpperCase() + w.slice(1)).join(' '); 
            selectOrigen.appendChild(option);
        });

        // Crear opciones para Destino
        destinos.forEach(destino => {
            let option = document.createElement("option");
            option.value = destino;
            option.text = destino.split(' ').map(w => w.charAt(0).toUpperCase() + w.slice(1)).join(' '); 
            selectDestino.appendChild(option);
        });
    });

    // ==========================================
    // MAGIA: FILTRO MULTIPLE + PRECIOS
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
            
            // 1. Filtrar visibilidad
            let mostrarOrigen = (valOrigen === "todos" || origenFila === valOrigen);
            let mostrarDestino = (valDestino === "todos" || destinoFila === valDestino);

            if (mostrarOrigen && mostrarDestino) {
                fila.style.display = "table-row";
            } else {
                fila.style.display = "none";
            }

            // 2. Calcular Precios
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
            } 
            else if (tipo === 'dropoff') {
                if (fila.dataset.cobro === 'fijo') {
                    celdaCosto.innerText = "$" + parseFloat(fila.dataset.fijo).toFixed(2);
                } else {
                    const total = (parseFloat(fila.dataset.km) || 0) * costoPorKm;
                    celdaCosto.innerText = "$" + total.toFixed(2);
                }
            }
        });
    }

    // Modal para Editar el costo por Categoría
    function openEditCategoria(id, nombre, costoActual) {
        document.getElementById('c_nombre').value = nombre;
        document.getElementById('c_costo').value = costoActual;
        // La URL de acción se arma aquí
        document.getElementById('formCosto').action = `{{ url('admin/categoria_costo_km/update') }}/${id}`; 
        document.getElementById('modalCosto').showModal();
    }
</script>

{{-- <script src="{{ asset('js/dropoff.js') }}"></script> --}}
@endsection