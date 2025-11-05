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

    <div class="toolbar" style="margin-bottom:20px; text-align:right;">
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
            <th>Categoría</th>
            <th>Kilometraje</th>
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
              <td>{{ $v->categoria }}</td>
              <td>{{ number_format($v->kilometraje) }} km</td>
              <td>{{ $v->estatus ?? 'Disponible' }}</td>
              <td>
                <button class="btn-sm editBtn" title="Editar">✏️</button>
                <form action="{{ route('flotilla.eliminar', $v->id_vehiculo) }}" method="POST" style="display:inline">
                  @csrf @method('DELETE')
                  <button type="submit" class="btn-sm" onclick="return confirm('¿Seguro que deseas eliminar este vehículo?')" title="Eliminar">🗑️</button>
                </form>
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
        <span>Editar Vehículo</span>
        <button id="closeModal">&times;</button>
      </div>
      <form id="editForm" method="POST">
        @csrf
        <div class="form-grid">
          <label>Modelo<input type="text" id="m_modelo" readonly></label>
          <label>Marca<input type="text" id="m_marca" readonly></label>
          <label>Año<input type="text" id="m_anio" readonly></label>
          <label>Color<input type="text" id="m_color" name="color"></label>
          <label>Categoría<input type="text" id="m_categoria" name="categoria"></label>
          <label>Kilometraje<input type="number" id="m_kilometraje" name="kilometraje"></label>
        </div>
        <div class="actions">
          <button type="submit" class="btn">💾 Guardar</button>
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
     <form action="{{ route('flotilla.agregar') }}" method="POST" enctype="multipart/form-data" class="form-grid">
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
      <select name="categoria">
        <option>C Compacto</option>
        <option>D Medianos</option>
        <option>E Grandes</option>
        <option>F Full size</option>
        <option>IC Suv compacta</option>
        <option>I Suv mediana</option>
        <option>IB Suv familiar compacta</option>
        <option>M Minivan</option>
        <option>L Pasajeros de 12 a 15 usuarios</option>
        <option>H Pick up doble cabina</option>
        <option>HI Pick up 4x4 doble cabina</option>
      </select>
      </label>
      <label>Número de Serie<input type="text" name="numero_serie" placeholder="Ej. 3VWEP6BU0SM005037"></label>
      <label>VIN<input type="text" name="vin" placeholder="Ej. 3VWEP6BU0SM005037"></label>
      <label>Placa<input type="text" name="placa" placeholder="Ej. UNS639J"></label>

      <h3>Datos Técnicos</h3>
      <label>Cilindros<input type="number" name="cilindros" min="1" max="16" value="4"></label>
      <label>Número de Motor<input type="text" name="numero_motor" placeholder="Ej. DSJ137414"></label>
      <label>Holograma<input type="text" name="holograma" placeholder="Ej. 00"></label>
      <label>Vigencia de Verificación<input type="date" name="vigencia_verificacion"></label>
      <label>No. Centro de Verificación<input type="text" name="no_centro_verificacion" placeholder="Ej. QRO-123"></label>
      <label>Tipo de Verificación
        <select name="tipo_verificacion">
          <option>Ordinaria</option>
          <option>Extraordinaria</option>
          <option>Complementaria</option>
        </select>
      </label>
      <label>Kilometraje<input type="number" name="kilometraje" min="0" value="0"></label>
      <label>Asientos<input type="number" name="asientos" min="2" max="10" value="5"></label>
      <label>Puertas<input type="number" name="puertas" min="2" max="6" value="4"></label>

      <h3>Datos del Propietario</h3>
      <label>Propietario<input type="text" name="propietario" placeholder="Ej. José Juan de Dios Hernández Reséndiz"></label>
      <label>RFC del Propietario<input type="text" name="rfc_propietario" placeholder="Ej. HERJ900308QM1"></label>
      <label>Domicilio<input type="text" name="domicilio" placeholder="Ej. Bugambilias 7, Col. Los Benitos"></label>
      <label>Municipio<input type="text" name="municipio" placeholder="Ej. Colón"></label>
      <label>Estado<input type="text" name="estado" placeholder="Ej. Querétaro"></label>
      <label>País<input type="text" name="pais" value="México"></label>

      <h3>Póliza de Seguro</h3>
      <label>Número de Póliza<input type="text" name="no_poliza"></label>
      <label>Aseguradora<input type="text" name="aseguradora" placeholder="Ej. BBVA"></label>
      <label>Inicio de Vigencia<input type="date" name="inicio_vigencia_poliza"></label>
      <label>Fin de Vigencia<input type="date" name="fin_vigencia_poliza"></label>
      <label>Tipo de Cobertura<input type="text" name="tipo_cobertura" placeholder="Ej. Responsabilidad Civil"></label>
      <label>Plan de Seguro<input type="text" name="plan_seguro" placeholder="Ej. Anual"></label>
      <label>Archivo de Póliza (PDF o Imagen)
      <input type="file" name="archivo_poliza" accept=".pdf,.jpg,.jpeg,.png">
      </label>

      <h3>Tarjeta de Circulación</h3>
      <label>Folio Tarjeta<input type="text" name="folio_tarjeta" placeholder="Ej. 12345678"></label>
      <label>Movimiento<input type="text" name="movimiento_tarjeta" placeholder="Ej. Alta"></label>
      <label>Fecha de Expedición<input type="date" name="fecha_expedicion_tarjeta"></label>
      <label>Oficina Expedidora<input type="text" name="oficina_expedidora" placeholder="Ej. Querétaro Centro"></label>
      <label>Archivo de Verificación (PDF o Imagen)
      <input type="file" name="archivo_verificacion" accept=".pdf,.jpg,.jpeg,.png">
      </label>

      <div class="actions" style="margin-top:15px;">
        <button type="submit" class="btn">💾 Guardar Vehículo</button>
        <button type="button" id="cancelAdd" class="btn ghost">Cancelar</button>
      </div>
      </form>
    </div>
  </div>
</main>

@section('js-vistaFlotilla')
<script>
// === MODAL EDITAR ===
const modal = document.getElementById('editModal');
const closeModal = document.getElementById('closeModal');
const cancelModal = document.getElementById('cancelModal');
const form = document.getElementById('editForm');
let currentId = null;

document.querySelectorAll('.editBtn').forEach(btn => {
  btn.addEventListener('click', e => {
    const tr = e.target.closest('tr');
    currentId = tr.dataset.id;
    document.getElementById('m_modelo').value = tr.dataset.modelo;
    document.getElementById('m_marca').value = tr.dataset.marca;
    document.getElementById('m_anio').value = tr.dataset.anio;
    document.getElementById('m_color').value = tr.dataset.color;
    document.getElementById('m_categoria').value = tr.dataset.categoria;
    document.getElementById('m_kilometraje').value = tr.dataset.kilometraje;
    form.action = `/admin/flotilla/${currentId}/actualizar`;
    modal.classList.add('active');
  });
});
closeModal.onclick = cancelModal.onclick = () => modal.classList.remove('active');

// === MODAL AGREGAR AUTO ===
const addModal = document.getElementById('addModal');
const btnAddAuto = document.getElementById('btnAddAuto');
const closeAdd = document.getElementById('closeAdd');
const cancelAdd = document.getElementById('cancelAdd');

btnAddAuto.onclick = () => addModal.classList.add('active');
closeAdd.onclick = cancelAdd.onclick = () => addModal.classList.remove('active');
</script>
@endsection
@endsection
