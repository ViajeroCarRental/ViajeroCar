@extends('layouts.Flotillas')
@section('Titulo', 'Flotilla')

@section('css-vistaFlotilla')
<link rel="stylesheet" href="{{ asset('css/flotilla.css') }}">
@endsection

@section('contenidoMantenimiento')
<main>
  <div class="topbar">
    <div><strong>Autos · Flotilla</strong></div>
  </div>

  <div class="content">
    <h1 class="title">Flotilla</h1>

    <!-- 🔍 Buscador + Botón juntos -->
    <div class="buscador-contenedor">
      <div class="buscador-flotilla">
        <i class="fas fa-search icono-buscar"></i>
        <input
          type="text"
          id="filtroVehiculo"
          placeholder="Buscar por modelo, placa, color o año...">
      </div>

      <button id="btnAddAuto" class="btn-red">➕ Agregar Auto</button>
    </div>

    <div style="overflow:auto">
      <table class="table" id="tblFleet">
        <thead>
          <tr>
            <th>Modelo</th>
            <th>Marca</th>
            <th>Año</th>
            <th>Color</th>
            <th>Placa</th>
            <th>Número de Serie</th>
            <th>Número de Rin</th>
            <th>Categoría</th>
            <th>Kilometraje</th>
            <th>Tanque (L)</th>
            <th>Estatus</th>
            <th>Acciones</th>
          </tr>
        </thead>

        <tbody>
          @foreach($vehiculos as $v)
          <tr data-id="{{ $v->id_vehiculo }}"
              data-modelo="{{ $v->modelo }}"
              data-marca="{{ $v->marca }}"
              data-anio="{{ $v->anio }}"
              data-color="{{ $v->color }}"
              data-categoria="{{ $v->categoria }}"
              data-kilometraje="{{ $v->kilometraje }}">

            <td>{{ $v->modelo }}</td>
            <td>{{ $v->marca }}</td>
            <td>{{ $v->anio }}</td>
            <td>{{ $v->color }}</td>
            <td>{{ $v->placa }}</td>
            <td>{{ $v->numero_serie }}</td>
            <td>{{ $v->numero_rin ?? '—' }}</td>
            <td>{{ $v->categoria }}</td>
            <td>{{ number_format($v->kilometraje) }} km</td>
            <td>{{ $v->capacidad_tanque ? $v->capacidad_tanque . ' L' : '—' }}</td>
            <td>{{ $v->estatus ?? 'Disponible' }}</td>
            <td>
              <!-- Fondo rojo debajo, oculto hasta swipe -->
              <div class="delete-bg">
                <form action="{{ route('flotilla.eliminar', $v->id_vehiculo) }}" method="POST" class="delete-form">
                  @csrf @method('DELETE')
                  <button type="button" class="btnDelete" title="Eliminar">🗑️</button>
                </form>
              </div>

              <!-- Botón Editar siempre visible -->
              <button
  type="button"
  class="btn-sm"
  title="Editar"
  onclick="abrirEditarVehiculo({{ $v->id_vehiculo }})"
>✏️</button>
            </td>
          </tr>
          @endforeach
        </tbody>
      </table>
    </div>
  </div>

  <!-- 🟢 MODAL EDITAR VEHÍCULO -->
  <div id="editModal" class="modal">
    <div class="modal-content glass">
      <div class="modal-header">
        <span>Editar Vehículo 🚗</span>
        <button id="closeModal">&times;</button>
      </div>
      <form id="editForm"
          method="POST"
          action=""
          enctype="multipart/form-data"
          class="form-grid">
      @csrf

      {{-- ================= DATOS GENERALES ================= --}}
      <h3>Datos Generales</h3>

      <label>Marca<input type="text" name="marca" id="e_marca" required></label>
      <label>Modelo<input type="text" name="modelo" id="e_modelo" required></label>
      <label>Año<input type="number" name="anio" id="e_anio" min="2000" max="{{ date('Y')+1 }}" required></label>
      <label>Nombre Público<input type="text" name="nombre_publico" id="e_nombre_publico"></label>
      <label>Color<input type="text" name="color" id="e_color"></label>
      <label>Transmisión
      <select name="transmision" id="e_transmision">
  <option value="">Seleccione...</option>
  <option value="Automático">Automático</option>
  <option value="Manual">Manual</option>
  <option value="CVT">CVT</option>
  <option value="Tiptronic">Tiptronic</option>
</select>
</label>
<label>Combustible
        <select name="combustible" id="e_combustible">
  <option value="">Seleccione...</option>
  <option value="Gasolina">Gasolina</option>
  <option value="Gasolina Premium">Gasolina Premium</option>
  <option value="Diésel">Diésel</option>
  <option value="Híbrido">Híbrido</option>
  <option value="Eléctrico">Eléctrico</option>
</select>
      </label>

      <label>Categoría
        <select name="id_categoria" id="e_id_categoria" required>
          <option value="" disabled>Seleccione una categoría...</option>
          @foreach($categorias as $cat)
            <option value="{{ $cat->id_categoria }}">{{ $cat->nombre }}</option>
          @endforeach
        </select>
      </label>

      <label>Número de Serie
        <input type="text" name="numero_serie" id="e_numero_serie">
      </label>

      <label>Número de Rin
        <input type="text" name="numero_rin" id="e_numero_rin">
      </label>

      <label>Placa
        <input type="text" name="placa" id="e_placa">
      </label>

      {{-- ================= DATOS TÉCNICOS ================= --}}
      <h3>Datos Técnicos</h3>

      <label>Cilindros
        <input type="number" name="cilindros" id="e_cilindros" min="1" max="16">
      </label>

      <label>Número de Motor
        <input type="text" name="numero_motor" id="e_numero_motor">
      </label>

      <label>Holograma
        <input type="text" name="holograma" id="e_holograma">
      </label>

      <label>Vigencia de Verificación
        <input type="date" name="vigencia_verificacion" id="e_vigencia_verificacion">
      </label>

      <label>Archivo de Verificación (opcional)
        <input type="file" name="archivo_verificacion" accept=".pdf,.jpg,.jpeg,.png">
      </label>

      <label>Kilometraje
        <input type="number" name="kilometraje" id="e_kilometraje" min="0">
      </label>

      <label>Asientos
        <input type="number" name="asientos" id="e_asientos" min="2" max="10">
      </label>

      <label>Puertas
        <input type="number" name="puertas" id="e_puertas" min="2" max="6">
      </label>

      <label>Capacidad de Tanque (L)
        <input type="number" step="0.1" name="capacidad_tanque" id="e_capacidad_tanque">
      </label>

      {{-- ================= ACEITE ================= --}}
      <label>Tipo de Aceite
        <select id="e_aceite_select">
          <option value="" disabled>Seleccione tipo de aceite...</option>
          <option value="Cvtec">CVT</option>
          <option value="Atf">ATF</option>
          <option value="otro">Otro...</option>
        </select>

        <input
          type="text"
          name="aceite"
          id="e_aceite"
          placeholder="Ej. 5W30 sintético"
          style="margin-top:6px;"
        >
      </label>

      {{-- ================= PROPIETARIO ================= --}}
      <h3>Datos del Propietario</h3>

      <select id="e_propietarioSelect">
        <option value="" disabled>Seleccione...</option>
        <option value="Juan de Dios">Juan de Dios</option>
        <option value="José Antonio">José Antonio</option>
        <option value="otro">Otro...</option>
    </select>

        <input
        type="text"
        name="propietario"
        id="e_propietarioInput"
        style="display:none"
        />

      <label>Carta Factura (opcional)
        <input type="file" name="archivo_cartafactura" accept=".pdf,.jpg,.jpeg,.png">
      </label>

      {{-- ================= SEGURO ================= --}}
      <h3>Póliza de Seguro</h3>

      <label>Número de Póliza
        <input type="text" name="no_poliza" id="e_no_poliza">
      </label>

      <label>Aseguradora
        <input type="text" name="aseguradora" id="e_aseguradora">
      </label>

      <label>Inicio de Vigencia
        <input type="date" name="inicio_vigencia_poliza" id="e_inicio_vigencia_poliza">
      </label>

      <label>Fin de Vigencia
        <input type="date" name="fin_vigencia_poliza" id="e_fin_vigencia_poliza">
      </label>

      <label>Archivo de Póliza (opcional)
        <input type="file" name="archivo_poliza" accept=".pdf,.jpg,.jpeg,.png">
      </label>

      {{-- ================= TARJETA ================= --}}
      <h3>Tarjeta de Circulación</h3>

      <label>Folio Tarjeta
        <input type="text" name="folio_tarjeta" id="e_folio_tarjeta">
      </label>

      <label>Movimiento
    <select id="e_movimientoSelect">
        <option value="" disabled>Seleccione...</option>
        <option value="Alta">Alta</option>
        <option value="Baja">Baja</option>
        <option value="otro">Otro...</option>
    </select>

    <input
        type="text"
        name="movimiento_tarjeta"
        id="e_movimientoInput"
        placeholder="Escribe el movimiento..."
        style="margin-top:6px; display:none;">
    </label>


      <label>Fecha de Expedición
        <input type="date" name="fecha_expedicion_tarjeta" id="e_fecha_expedicion_tarjeta">
      </label>

      <label>Tarjeta de Circulación (opcional)
        <input type="file" name="archivo_tarjetacirculacion" accept=".pdf,.jpg,.jpeg,.png">
      </label>






     <h3>Documentos</h3>
<div id="archivosVehiculo"></div>






      <div class="actions" style="margin-top:15px;">
        <button type="submit" class="btn">💾 Actualizar Vehículo</button>
        <button type="button" class="btn ghost" id="cancelModal">Cancelar</button>
      </div>
    </form>
  </div>
</div>



  <!-- 🔴 MODAL AGREGAR AUTO -->
  <div id="addModal" class="modal">
    <div class="modal-content glass">
      <div class="modal-header">
        <span>Agregar Nuevo Vehículo 🚗</span>
        <button id="closeAdd">&times;</button>
      </div>

      <form id="formAddAuto" action="{{ route('flotilla.agregar') }}" method="POST" enctype="multipart/form-data" class="form-grid">
        @csrf

        <h3>Datos Generales</h3>
        <label>Marca<input type="text" name="marca" required></label>
        <label>Modelo<input type="text" name="modelo" required></label>
        <label>Año<input type="number" name="anio" required min="2000" max="{{ date('Y')+1 }}"></label>
        <label>Nombre Público<input type="text" name="nombre_publico" required placeholder="Ej. VW Jetta 1.4 TSI 2025"></label>
        <label>Color<input type="text" name="color" placeholder="Ej. Blanco Perla"></label>

        <label>Transmisión
          <select name="transmision">
            <option>Automática</option>
            <option>Manual</option>
            <option>CVT</option>
            <option>Tiptronic</option>
          </select>
        </label>

        <label>Combustible
          <select name="combustible">
            <option>Gasolina</option>
            <option>Diésel</option>
            <option>Híbrido</option>
            <option>Eléctrico</option>
          </select>
        </label>

        <label>Categoría
          <select name="id_categoria" required>
            <option value="" disabled selected>Seleccione una categoría...</option>
            @foreach($categorias as $cat)
              <option value="{{ $cat->id_categoria }}">{{ $cat->nombre }}</option>
            @endforeach
          </select>
        </label>

        <label>Número de Serie<input type="text" name="numero_serie" placeholder="Ej. 3VWEP6BU0SM005037"></label>
        <label>Número de Rin<input type="text" name="numero_rin" placeholder="Ej. 17x7J o similar"></label>
        <label>Placa<input type="text" name="placa" placeholder="Ej. UNS639J"></label>

        <h3>Datos Técnicos</h3>
        <label>Cilindros<input type="number" name="cilindros" min="1" max="16" value="4"></label>
        <label>Número de Motor<input type="text" name="numero_motor" placeholder="Ej. DSJ137414"></label>
        <label>Holograma<input type="text" name="holograma" placeholder="Ej. 00"></label>
        <label>Vigencia de Verificación<input type="date" name="vigencia_verificacion"></label>

        <!-- ✅ NUEVO: Archivo de verificación en Datos Técnicos -->
        <label>Archivo de Verificación (PDF o Imagen)
          <input
            type="file"
            name="archivo_verificacion"
            accept=".pdf,.jpg,.jpeg,.png"
            capture="environment"
          >
        </label>

        <label>Kilometraje<input type="number" name="kilometraje" min="0" value="0"></label>
        <label>Asientos<input type="number" name="asientos" min="2" max="10" value="5"></label>
        <label>Puertas<input type="number" name="puertas" min="2" max="6" value="4"></label>
        <label>Capacidad de Tanque (L)<input type="number" step="0.1" name="capacidad_tanque" placeholder="Ej. 55.0"></label>

        <!-- Tipo de Aceite -->
        <label>Tipo de Aceite
          <select id="aceiteSelect">
            <option value="" selected disabled>Seleccione tipo de aceite...</option>
            <option value="Cvtec">CVT</option>
            <option value="Atf">ATF</option>
            <option value="otro">Otro...</option>
          </select>

          <input
            type="text"
            name="aceite"
            id="aceiteInput"
            placeholder="Ej. 5W30 sintético"
            style="margin-top:6px; display:none;"
          >
        </label>

        <!-- ✅ NUEVO: DATOS DEL PROPIETARIO -->
        <h3>Datos del Propietario</h3>

        <label class="inline-2">
          <span>Propietario</span>
          <select id="propietarioSelect">
            <option value="" disabled selected>Seleccione...</option>
            <option value="Juan de Dios">Juan de Dios</option>
            <option value="José Antonio">José Antonio</option>
            <option value="otro">Otro...</option>
          </select>

          <input
            type="text"
            name="propietario"
            id="propietarioInput"
            placeholder="Escribe el propietario..."
            style="margin-top:6px; display:none;"
          >
        </label>

        <label class="inline-2">
          <span>Carta factura (PDF/Imagen o cámara)</span>
          <input
            type="file"
            name="archivo_cartafactura"
            accept=".pdf,.jpg,.jpeg,.png"
            capture="environment"
          >
        </label>

        <h3>Póliza de Seguro</h3>
        <label>Número de Póliza<input type="text" name="no_poliza"></label>
        <label>Aseguradora<input type="text" name="aseguradora" placeholder="Ej. BBVA"></label>
        <label>Inicio de Vigencia<input type="date" name="inicio_vigencia_poliza"></label>
        <label>Fin de Vigencia<input type="date" name="fin_vigencia_poliza"></label>

        <label>Archivo de Póliza (PDF o Imagen)
          <input type="file" name="archivo_poliza" accept=".pdf,.jpg,.jpeg,.png">
        </label>

        <h3>Tarjeta de Circulación</h3>
        <label>Folio Tarjeta<input type="text" name="folio_tarjeta" placeholder="Ej. 12345678"></label>

        <label>Movimiento
          <select id="movimientoSelect">
            <option value="" disabled selected>Seleccione...</option>
            <option value="Alta">Alta</option>
            <option value="Baja">Baja</option>
            <option value="otro">Otro...</option>
          </select>

          <input
            type="text"
            name="movimiento_tarjeta"
            id="movimientoInput"
            placeholder="Escribe el movimiento..."
            style="margin-top:6px; display:none;"
          >
        </label>

        <label>Fecha de Expedición<input type="date" name="fecha_expedicion_tarjeta"></label>

        <label>Tarjeta de Circulación (PDF o Imagen)
          <input
            type="file"
            name="archivo_tarjetacirculacion"
            accept=".pdf,.jpg,.jpeg,.png"
            capture="environment"
          >
        </label>

        <div class="actions" style="margin-top:15px;">
          <button type="submit" class="btn">💾 Guardar Vehículo</button>
          <button type="button" id="cancelAdd" class="btn ghost">Cancelar</button>
        </div>
      </form>
    </div>
  </div>

  <!-- Modal: Confirmar eliminación -->
  <div id="confirmDeleteModal" aria-hidden="true">
    <div class="modal-content" role="dialog" aria-modal="true">
      <h3>¿Eliminar vehículo?</h3>
      <p>Esta acción no se puede deshacer.</p>
      <div class="actions">
        <button type="button" class="btn-cancel" id="cancelDelete">Cancelar</button>
        <button type="button" class="btn-delete" id="confirmDelete">Eliminar</button>
      </div>
    </div>
  </div>

</main>

@section('js-vistaFlotilla')
<script>
/* ==========================================================
   🔔 CONFIGURACIÓN ALERTIFY
========================================================== */
if (window.alertify) {
  alertify.set('notifier', 'position', 'top-right');
  alertify.set('notifier', 'delay', 4);
}

/* ==========================================================
   ✅ VALIDACIÓN DEL FORM (ANTES DE ENVIAR)
========================================================== */
function valorVacio(v){
  return v === null || v === undefined || String(v).trim() === '';
}

function getFieldValue(form, selector){
  const el = form.querySelector(selector);
  if(!el) return '';
  if(el.type === 'file') return (el.files && el.files[0]) ? el.files[0] : null;
  return el.value;
}

function validarFormVehiculo(form){
  const faltantes = [];

  // 🔴 Campos que SÍ quieres obligatorios
  const rules = [
    { label: 'Marca', selector: 'input[name="marca"]' },
    { label: 'Modelo', selector: 'input[name="modelo"]' },
    { label: 'Año', selector: 'input[name="anio"]' },
    { label: 'Nombre público', selector: 'input[name="nombre_publico"]' },
    { label: 'Categoría', selector: 'select[name="id_categoria"]' },
    { label: 'Número de serie', selector: 'input[name="numero_serie"]' },

    { label: 'Kilometraje', selector: 'input[name="kilometraje"]' },

    // Propietario
    { label: 'Propietario', selector: '#propietarioInput' },
    { label: 'Carta factura', selector: 'input[name="archivo_cartafactura"]', type:'file' },

    // Seguro
    { label: 'Aseguradora', selector: 'input[name="aseguradora"]' },
    { label: 'Número de póliza', selector: 'input[name="no_poliza"]' },
    { label: 'Inicio de vigencia (póliza)', selector: 'input[name="inicio_vigencia_poliza"]' },
    { label: 'Fin de vigencia (póliza)', selector: 'input[name="fin_vigencia_poliza"]' },

    // Tarjeta
    { label: 'Folio tarjeta', selector: 'input[name="folio_tarjeta"]' },
    { label: 'Movimiento', selector: '#movimientoInput' },
    { label: 'Fecha de expedición', selector: 'input[name="fecha_expedicion_tarjeta"]' },
    { label: 'Tarjeta de circulación (archivo)', selector: 'input[name="archivo_tarjetacirculacion"]', type:'file' },
  ];

  for (const r of rules) {
    if (r.type === 'file') {
      const file = getFieldValue(form, r.selector);
      if (!file) faltantes.push(r.label);
    } else {
      const val = getFieldValue(form, r.selector);
      if (valorVacio(val)) faltantes.push(r.label);
    }
  }

  return faltantes;
}

function mostrarFaltantes(faltantes){
  const lista = faltantes.map(x => `• ${x}`).join('<br>');

  const html = `
    <div style="text-align:left">
      <b>Falta completar:</b><br><br>
      ${lista}
    </div>
  `;

  if (window.alertify) {
    alertify.alert('Campos obligatorios', html);
  } else {
    alert('Falta completar:\n\n' + faltantes.map(x => '- ' + x).join('\n'));
  }
}

/* ==========================================================
   🧾 MODAL EDITAR VEHÍCULO (CARGA COMPLETA DESDE BD)
========================================================== */

async function abrirEditarVehiculo(id) {
  try {
    const resp = await fetch(`/admin/flotilla/${id}/ver`);

    if (!resp.ok) {
      alert('No se pudo cargar el vehículo');
      return;
    }

    const v = await resp.json();

    const modal = document.getElementById('editModal');
    const form  = document.getElementById('editForm');

    // Acción del form
    form.action = `/admin/flotilla/${id}/actualizar`;

    /* ================= DATOS GENERALES ================= */
    document.getElementById('e_marca').value = v.marca ?? '';
    document.getElementById('e_modelo').value = v.modelo ?? '';
    document.getElementById('e_anio').value = v.anio ?? '';
    document.getElementById('e_nombre_publico').value = v.nombre_publico ?? '';
    document.getElementById('e_color').value = v.color ?? '';

    document.getElementById('e_transmision').value = v.transmision ?? '';
    document.getElementById('e_combustible').value = v.combustible ?? '';
    document.getElementById('e_id_categoria').value = v.id_categoria ?? '';

    document.getElementById('e_numero_serie').value = v.numero_serie ?? '';
    document.getElementById('e_numero_rin').value = v.numero_rin ?? '';
    document.getElementById('e_placa').value = v.placa ?? '';

    /* ================= DATOS TÉCNICOS ================= */
    document.getElementById('e_cilindros').value = v.cilindros ?? '';
    document.getElementById('e_numero_motor').value = v.numero_motor ?? '';
    document.getElementById('e_holograma').value = v.holograma ?? '';
    document.getElementById('e_vigencia_verificacion').value =
      v.vigencia_verificacion ? v.vigencia_verificacion.substring(0, 10) : '';

    document.getElementById('e_kilometraje').value = v.kilometraje ?? '';
    document.getElementById('e_asientos').value = v.asientos ?? '';
    document.getElementById('e_puertas').value = v.puertas ?? '';
    document.getElementById('e_capacidad_tanque').value = v.capacidad_tanque ?? '';

    /* ================= ACEITE ================= */
    const aceiteSelect = document.getElementById('e_aceite_select');
    const aceiteInput  = document.getElementById('e_aceite');

    if (['Cvtec', 'Atf'].includes(v.aceite)) {
      aceiteSelect.value = v.aceite;
      aceiteInput.value = v.aceite;
      aceiteInput.style.display = 'none';
    } else if (v.aceite) {
      aceiteSelect.value = 'otro';
      aceiteInput.value = v.aceite;
      aceiteInput.style.display = 'block';
    } else {
      aceiteSelect.value = '';
      aceiteInput.value = '';
      aceiteInput.style.display = 'none';
    }

 /* ================= PROPIETARIO ================= */
const selProp = document.getElementById('e_propietarioSelect');
const inpProp = document.getElementById('e_propietarioInput');

const opcionesProp = Array.from(selProp.options).map(o => o.value);

if (opcionesProp.includes(v.propietario)) {
  // Coincide con opción (Juan de Dios, José Antonio, etc)
  selProp.value = v.propietario;
  inpProp.style.display = 'none';
  inpProp.value = v.propietario;
} else if (v.propietario) {
  // No existe → usar "otro"
  selProp.value = 'otro';
  inpProp.style.display = 'block';
  inpProp.value = v.propietario;
} else {
  // Vacío
  selProp.value = '';
  inpProp.style.display = 'none';
  inpProp.value = '';
}
    /* ================= SEGURO ================= */
    document.getElementById('e_no_poliza').value = v.no_poliza ?? '';
    document.getElementById('e_aseguradora').value = v.aseguradora ?? '';
    document.getElementById('e_inicio_vigencia_poliza').value =
      v.inicio_vigencia_poliza ?? '';
    document.getElementById('e_fin_vigencia_poliza').value =
      v.fin_vigencia_poliza ?? '';

    /* ================= TARJETA ================= */
    document.getElementById('e_folio_tarjeta').value = v.folio_tarjeta ?? '';
    document.getElementById('e_fecha_expedicion_tarjeta').value =
      v.fecha_expedicion_tarjeta ?? '';


 // ===== MOVIMIENTO TARJETA (SELECT + INPUT) =====
const movSel = document.getElementById('e_movimientoSelect');
const movInp = document.getElementById('e_movimientoInput');

const opcionesMov = Array.from(movSel.options).map(o => o.value);

if (opcionesMov.includes(v.movimiento_tarjeta)) {
  movSel.value = v.movimiento_tarjeta;
  movInp.style.display = 'none';
  movInp.value = v.movimiento_tarjeta;
} else {
  movSel.value = 'otro';
  movInp.style.display = 'block';
  movInp.value = v.movimiento_tarjeta ?? '';
}

    modal.classList.add('active');

  } catch (err) {
    console.error('Error al abrir edición:', err);
    alert('Error al cargar el vehículo');
  }

}



/* ==========================================================
   ❌ CERRAR MODAL EDITAR
========================================================== */
document.getElementById('closeModal').onclick =
document.getElementById('cancelModal').onclick = () => {
  document.getElementById('editModal').classList.remove('active');
};

/* ==========================================================
   🧠 SELECT ACEITE (EDITAR)
========================================================== */
document.getElementById('e_aceite_select').addEventListener('change', function () {
  const input = document.getElementById('e_aceite');

  if (this.value === 'otro') {
    input.style.display = 'block';
    input.value = '';
    input.focus();
  } else {
    input.style.display = 'none';
    input.value = this.value;
  }
});

/* ==========================================================
   SELECT MOVIMIENTO – EDITAR
========================================================== */
const movSelectEdit = document.getElementById('e_movimientoSelect');
const movInputEdit  = document.getElementById('e_movimientoInput');

if (movSelectEdit && movInputEdit) {
  movSelectEdit.addEventListener('change', () => {
    const val = movSelectEdit.value;

    if (val === 'otro') {
      movInputEdit.style.display = 'block';
      movInputEdit.value = '';
      movInputEdit.focus();
    } else {
      movInputEdit.style.display = 'none';
      movInputEdit.value = val;
    }
  });
}

/* ==========================================================
   🧠 SELECT PROPIETARIO (EDITAR) – LISTENER
========================================================== */
const propietarioSelectEdit = document.getElementById('e_propietarioSelect');
const propietarioInputEdit  = document.getElementById('e_propietarioInput');

if (propietarioSelectEdit && propietarioInputEdit) {
  propietarioSelectEdit.addEventListener('change', () => {
    const val = propietarioSelectEdit.value;

    if (val === 'otro') {
      propietarioInputEdit.style.display = 'block';
      propietarioInputEdit.value = '';
      propietarioInputEdit.focus();
    } else {
      propietarioInputEdit.style.display = 'none';
      propietarioInputEdit.value = val;
    }
  });
}








/* ==========================================================
   🧾 MODAL AGREGAR
========================================================== */
const addModal = document.getElementById('addModal');
const btnAddAuto = document.getElementById('btnAddAuto');
const closeAdd = document.getElementById('closeAdd');
const cancelAdd = document.getElementById('cancelAdd');
btnAddAuto.onclick = () => addModal.classList.add('active');
closeAdd.onclick = cancelAdd.onclick = () => addModal.classList.remove('active');

/* ==========================================================
   SELECTS: ACEITE / PROPIETARIO / MOVIMIENTO (OTRO...)
========================================================== */
const aceiteSelect = document.getElementById('aceiteSelect');
const aceiteInput  = document.getElementById('aceiteInput');
if (aceiteSelect && aceiteInput) {
  aceiteSelect.addEventListener('change', () => {
    const val = aceiteSelect.value;
    if (val === 'otro') {
      aceiteInput.style.display = 'block';
      aceiteInput.value = '';
      aceiteInput.focus();
    } else if (val) {
      aceiteInput.style.display = 'none';
      aceiteInput.value = val;
    } else {
      aceiteInput.style.display = 'none';
      aceiteInput.value = '';
    }
  });
}

const propietarioSelect = document.getElementById('propietarioSelect');
const propietarioInput  = document.getElementById('propietarioInput');
if (propietarioSelect && propietarioInput) {
  propietarioSelect.addEventListener('change', () => {
    const val = propietarioSelect.value;
    if (val === 'otro') {
      propietarioInput.style.display = 'block';
      propietarioInput.value = '';
      propietarioInput.focus();
    } else if (val) {
      propietarioInput.style.display = 'none';
      propietarioInput.value = val; // se envía
    } else {
      propietarioInput.style.display = 'none';
      propietarioInput.value = '';
    }
  });
}

const movimientoSelect = document.getElementById('movimientoSelect');
const movimientoInput  = document.getElementById('movimientoInput');
if (movimientoSelect && movimientoInput) {
  movimientoSelect.addEventListener('change', () => {
    const val = movimientoSelect.value;
    if (val === 'otro') {
      movimientoInput.style.display = 'block';
      movimientoInput.value = '';
      movimientoInput.focus();
    } else if (val) {
      movimientoInput.style.display = 'none';
      movimientoInput.value = val; // se envía
    } else {
      movimientoInput.style.display = 'none';
      movimientoInput.value = '';
    }
  });
}

/* ==========================================================
   📦 FUNCIÓN PARA COMPRIMIR IMÁGENES (SOLO IMÁGENES)
========================================================== */
async function comprimirImagen(file, maxWidth = 1200, quality = 0.7) {
  if (!file || !file.type || !file.type.startsWith("image/")) return file;

  return new Promise((resolve) => {
    try {
      const img = new Image();
      const reader = new FileReader();

      reader.onload = (e) => { img.src = e.target.result; };

      img.onerror = () => {
        console.error("No se pudo cargar la imagen para comprimir");
        if (window.alertify) alertify.error("❌ No se pudo leer la imagen para comprimir.");
        resolve(file);
      };

      img.onload = () => {
        const canvas = document.createElement("canvas");
        const scale = Math.min(maxWidth / img.width, 1);

        canvas.width  = img.width * scale;
        canvas.height = img.height * scale;

        const ctx = canvas.getContext("2d");
        ctx.drawImage(img, 0, 0, canvas.width, canvas.height);

        const terminar = (blob) => {
          if (!blob) return resolve(file);

          const newName = (file.name || "imagen").replace(/\.\w+$/, ".jpg");
          let nuevoArchivo;

          try {
            nuevoArchivo = new File([blob], newName, { type: "image/jpeg" });
          } catch (e) {
            nuevoArchivo = blob;
            nuevoArchivo.name = newName;
          }
          resolve(nuevoArchivo);
        };

        if (canvas.toBlob) {
          canvas.toBlob((blob) => terminar(blob), "image/jpeg", quality);
        } else {
          const dataUrl = canvas.toDataURL("image/jpeg", quality);
          const bin  = atob(dataUrl.split(",")[1]);
          const len  = bin.length;
          const buf  = new Uint8Array(len);
          for (let i = 0; i < len; i++) buf[i] = bin.charCodeAt(i);
          terminar(new Blob([buf], { type: "image/jpeg" }));
        }
      };

      reader.readAsDataURL(file);
    } catch (err) {
      console.error("Error al comprimir imagen:", err);
      if (window.alertify) alertify.error("❌ Error interno al comprimir la imagen.");
      resolve(file);
    }
  });
}

/* ==========================================================
   🧾 SUBMIT FORM AGREGAR AUTO (VALIDA + COMPRIME + FETCH)
========================================================== */
const formAddAuto = document.getElementById('formAddAuto');

if (formAddAuto) {
  formAddAuto.addEventListener('submit', async (e) => {
    e.preventDefault();

    // ✅ VALIDAR ANTES DE ENVIAR
    const faltantes = validarFormVehiculo(formAddAuto);
    if (faltantes.length) {
      mostrarFaltantes(faltantes);
      return; // ⛔ no manda el fetch
    }

    const submitBtn = formAddAuto.querySelector('button[type="submit"]');
    const originalText = submitBtn ? submitBtn.textContent : '';

    if (submitBtn) {
      submitBtn.disabled = true;
      submitBtn.textContent = 'Guardando... ⏳';
    }

    const formData = new FormData(formAddAuto);

    // Campos de archivo
    const inputPoliza  = formAddAuto.querySelector('input[name="archivo_poliza"]');
    const inputVerifTC = formAddAuto.querySelector('input[name="archivo_verificacion_tecnica"]');
    const inputTarjeta = formAddAuto.querySelector('input[name="archivo_verificacion"]');
    const inputCarta   = formAddAuto.querySelector('input[name="carta_factura"]');

    // Helper: comprimir y reinsertar
    async function comprimirYReinsertar(input, fieldName, defaultName){
      if (!input || !input.files || !input.files[0]) return;

      const original = input.files[0];
      let archivoFinal = original;

      if (original.type.startsWith("image/") && original.size > 1024 * 1024) {
        archivoFinal = await comprimirImagen(original);
      }

      formData.delete(fieldName);
      formData.append(fieldName, archivoFinal, archivoFinal.name || original.name || defaultName);
    }

    // Comprimir imágenes si aplica
    await comprimirYReinsertar(inputPoliza,  "archivo_poliza",               "poliza.jpg");
    await comprimirYReinsertar(inputVerifTC, "archivo_verificacion_tecnica", "verificacion_tecnica.jpg");
    await comprimirYReinsertar(inputTarjeta, "archivo_verificacion",         "tarjeta_circulacion.jpg");
    await comprimirYReinsertar(inputCarta,   "carta_factura",                "carta_factura.jpg");

    try {
      const tokenMeta = document.querySelector('meta[name="csrf-token"]');
      const csrf = tokenMeta ? tokenMeta.content : '';

      const resp = await fetch(formAddAuto.action, {
        method: 'POST',
        headers: {
          'X-CSRF-TOKEN': csrf,
          'Accept': 'application/json'
        },
        body: formData
      });

      const rawText = await resp.text();
      let data;

      try {
        data = JSON.parse(rawText);
      } catch (err) {
        console.error('Respuesta no JSON del servidor:', rawText);

        let msg = "❌ Error del servidor.";
        if (resp.status === 413 || rawText.includes("PostTooLargeException") || rawText.includes("POST data is too large")) {
          msg += " El formulario o los archivos son demasiado grandes para el servidor (límite de subida).";
        } else {
          msg += " Respuesta no válida.";
        }

        if (window.alertify) alertify.error(msg);
        throw err;
      }

      if (!resp.ok || data.success === false) {
        let msg = data.message || 'Error al guardar el vehículo.';

        if (data.errors) {
          const erroresPlanos = [];
          Object.keys(data.errors).forEach(campo => {
            data.errors[campo].forEach(m => erroresPlanos.push(m));
          });
          if (erroresPlanos.length) msg = erroresPlanos.join('\n');
        }

        if (window.alertify) alertify.error('❌ ' + msg);
        return;
      }

      if (window.alertify) alertify.success(data.message || 'Vehículo agregado correctamente.');

      addModal.classList.remove('active');
      window.location.reload();

    } catch (err) {
      console.error('Error en envío de formulario flotilla:', err);
      if (window.alertify) alertify.error('❌ Error al enviar el formulario. Intenta nuevamente.');
    } finally {
      if (submitBtn) {
        submitBtn.disabled = false;
        submitBtn.textContent = originalText || '💾 Guardar Vehículo';
      }
    }
  });
}

/* ==========================================================
   ✅ Confirmación de eliminación
========================================================== */
(function () {
  const confirmModal = document.getElementById('confirmDeleteModal');
  const btnCancel = document.getElementById('cancelDelete');
  const btnConfirm = document.getElementById('confirmDelete');
  let formToDelete = null;

  document.querySelectorAll('.delete-bg .delete-form .btnDelete').forEach(btn => {
    btn.addEventListener('click', (e) => {
      formToDelete = e.target.closest('form');
      confirmModal.classList.add('active');
    });
  });

  btnCancel.addEventListener('click', () => {
    confirmModal.classList.remove('active');
    formToDelete = null;
  });

  btnConfirm.addEventListener('click', () => {
    if (formToDelete) formToDelete.submit();
  });

  confirmModal.addEventListener('click', (e) => {
    if (e.target === confirmModal) {
      confirmModal.classList.remove('active');
      formToDelete = null;
    }
  });

  document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape') {
      confirmModal.classList.remove('active');
      formToDelete = null;
    }
  });
})();

/* ==========================================================
   👆 SWIPE PARA ELIMINAR
========================================================== */
let startX = 0;

document.querySelectorAll('#tblFleet tbody tr').forEach(tr => {
  const resetAll = () => {
    document.querySelectorAll('#tblFleet tbody tr').forEach(r => {
      r.classList.remove('swiped');
      r.classList.remove('rebound');
    });
  };

  const handleSwipe = diff => {
    if (diff < -40 && !tr.classList.contains('swiped')) {
      resetAll();
      tr.classList.add('swiped');
      tr.classList.add('rebound');
    }
    if (diff > 40 && tr.classList.contains('swiped')) {
      tr.classList.remove('swiped');
    }
  };

  tr.addEventListener('mousedown', e => startX = e.clientX);
  tr.addEventListener('mousemove', e => {
    if (e.buttons === 1) {
      const diff = e.clientX - startX;
      handleSwipe(diff);
    }
  });

  tr.addEventListener('touchstart', e => startX = e.touches[0].clientX);
  tr.addEventListener('touchmove', e => {
    const diff = e.touches[0].clientX - startX;
    handleSwipe(diff);
  });
});

document.addEventListener('keydown', e => {
  if (e.key === 'Escape') {
    document.querySelectorAll('#tblFleet tbody tr').forEach(r => r.classList.remove('swiped'));
  }
});
document.addEventListener('click', e => {
  const tbl = document.getElementById('tblFleet');
  if (!tbl.contains(e.target)) {
    document.querySelectorAll('#tblFleet tbody tr').forEach(r => r.classList.remove('swiped'));
  }
});

/* ==========================================================
   🔎 FILTRO DE BÚSQUEDA
========================================================== */
document.getElementById('filtroVehiculo').addEventListener('keyup', function () {
  const filtro = this.value.toLowerCase();
  const filas = document.querySelectorAll('#tblFleet tbody tr');

  filas.forEach(fila => {
    const modelo = fila.dataset.modelo.toLowerCase();
    const placa = fila.querySelector('td:nth-child(5)').textContent.toLowerCase();
    const color = fila.dataset.color.toLowerCase();
    const anio = fila.dataset.anio.toLowerCase();

    if (modelo.includes(filtro) || placa.includes(filtro) || color.includes(filtro) || anio.includes(filtro)) {
      fila.style.display = '';
    } else {
      fila.style.display = 'none';
    }
  });
});
</script>
@endsection
@endsection
