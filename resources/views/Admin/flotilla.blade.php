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
              <button class="btn-sm editBtn" title="Editar"></button>
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

      {{-- 💡 AQUÍ SOLO SE LE AGREGA EL ID AL FORM --}}
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
            name="archivo_verificacion_tecnica"
            accept=".pdf,.jpg,.jpeg,.png"
            capture="environment"
          >
        </label>

        <!-- ❌ QUITADOS: No. Centro de Verificación, Tipo de Verificación -->
        <label>Kilometraje<input type="number" name="kilometraje" min="0" value="0"></label>
        <label>Asientos<input type="number" name="asientos" min="2" max="10" value="5"></label>
        <label>Puertas<input type="number" name="puertas" min="2" max="6" value="4"></label>
        <label>Capacidad de Tanque (L)<input type="number" step="0.1" name="capacidad_tanque" placeholder="Ej. 55.0"></label>

        <!-- 👇 NUEVO CAMPO -->
        <label>Tipo de Aceite
          <select id="aceiteSelect">
            <option value="" selected disabled>Seleccione tipo de aceite...</option>
            <option value="Cvtec">CVT</option>
            <option value="Atf">ATF</option>
            <option value="otro">Otro...</option>
          </select>

          <!-- Este es el que realmente se envía al backend -->
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

          <!-- Este es el que se envía al backend -->
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
            name="carta_factura"
            accept=".pdf,.jpg,.jpeg,.png"
            capture="environment"
          >
        </label>

        <h3>Póliza de Seguro</h3>
        <label>Número de Póliza<input type="text" name="no_poliza"></label>
        <label>Aseguradora<input type="text" name="aseguradora" placeholder="Ej. BBVA"></label>
        <label>Inicio de Vigencia<input type="date" name="inicio_vigencia_poliza"></label>
        <label>Fin de Vigencia<input type="date" name="fin_vigencia_poliza"></label>

        <!-- ❌ QUITADOS: Tipo de Cobertura, Plan de Seguro -->
        <label>Archivo de Póliza (PDF o Imagen)
          <input type="file" name="archivo_poliza" accept=".pdf,.jpg,.jpeg,.png">
        </label>

        <h3>Tarjeta de Circulación</h3>
        <label>Folio Tarjeta<input type="text" name="folio_tarjeta" placeholder="Ej. 12345678"></label>

        <!-- ✅ Movimiento ahora es select (Alta/Baja/Otro) -->
        <label>Movimiento
          <select id="movimientoSelect">
            <option value="" disabled selected>Seleccione...</option>
            <option value="Alta">Alta</option>
            <option value="Baja">Baja</option>
            <option value="otro">Otro...</option>
          </select>

          <!-- Este es el que se envía al backend -->
          <input
            type="text"
            name="movimiento_tarjeta"
            id="movimientoInput"
            placeholder="Escribe el movimiento..."
            style="margin-top:6px; display:none;"
          >
        </label>

        <label>Fecha de Expedición<input type="date" name="fecha_expedicion_tarjeta"></label>

        <!-- ❌ QUITADO: Oficina Expedidora -->

        <!-- ✅ RENOMBRADO: ya no dice Archivo de Verificación -->
        <label>Tarjeta de Circulación (PDF o Imagen)
          <input
            type="file"
            name="archivo_verificacion"
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
   🔔 CONFIGURACIÓN ALERTIFY (NUEVO)
========================================================== */
if (window.alertify) {
  alertify.set('notifier', 'position', 'top-right');
  alertify.set('notifier', 'delay', 4);
}

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


// =====================================
// ✅ SELECT + INPUT PARA TIPO DE ACEITE
// =====================================
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

// =====================================
// ✅ PROPIETARIO (select + input)
// =====================================
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
      propietarioInput.value = val; // se envía en name="propietario"
    } else {
      propietarioInput.style.display = 'none';
      propietarioInput.value = '';
    }
  });
}

// =====================================
// ✅ Movimiento tarjeta (Alta/Baja/Otro)
// =====================================
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
      movimientoInput.value = val; // se envía en name="movimiento_tarjeta"
    } else {
      movimientoInput.style.display = 'none';
      movimientoInput.value = '';
    }
  });
}


/* ==========================================================
   📦 FUNCIÓN PARA COMPRIMIR IMÁGENES (Flotilla)
   - Compatible iOS / Safari
   - SOLO IMÁGENES, NO PDF
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
   🧾 SUBMIT DEL FORMULARIO "AGREGAR AUTO" CON COMPRESIÓN
========================================================== */
const formAddAuto = document.getElementById('formAddAuto');

if (formAddAuto) {
  formAddAuto.addEventListener('submit', async (e) => {
    e.preventDefault();

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

    // 📄 Póliza
    if (inputPoliza && inputPoliza.files && inputPoliza.files[0]) {
      const original = inputPoliza.files[0];
      let archivoFinal = original;

      if (original.type.startsWith("image/") && original.size > 1024 * 1024) {
        if (window.alertify) alertify.message("📸 Póliza original: " + (original.size / 1024).toFixed(1) + " KB");
        archivoFinal = await comprimirImagen(original);
        if (window.alertify) alertify.message("✅ Póliza comprimida: " + (archivoFinal.size / 1024).toFixed(1) + " KB");
      }

      formData.delete("archivo_poliza");
      formData.append("archivo_poliza", archivoFinal, archivoFinal.name || original.name || "poliza.jpg");
    }

    // 📄 Verificación técnica
    if (inputVerifTC && inputVerifTC.files && inputVerifTC.files[0]) {
      const original = inputVerifTC.files[0];
      let archivoFinal = original;

      if (original.type.startsWith("image/") && original.size > 1024 * 1024) {
        if (window.alertify) alertify.message("📸 Verificación (técnica) original: " + (original.size / 1024).toFixed(1) + " KB");
        archivoFinal = await comprimirImagen(original);
        if (window.alertify) alertify.message("✅ Verificación (técnica) comprimida: " + (archivoFinal.size / 1024).toFixed(1) + " KB");
      }

      formData.delete("archivo_verificacion_tecnica");
      formData.append("archivo_verificacion_tecnica", archivoFinal, archivoFinal.name || original.name || "verificacion_tecnica.jpg");
    }

    // 🪪 Tarjeta de circulación (archivo_verificacion)
    if (inputTarjeta && inputTarjeta.files && inputTarjeta.files[0]) {
      const original = inputTarjeta.files[0];
      let archivoFinal = original;

      if (original.type.startsWith("image/") && original.size > 1024 * 1024) {
        if (window.alertify) alertify.message("📸 Tarjeta de circulación original: " + (original.size / 1024).toFixed(1) + " KB");
        archivoFinal = await comprimirImagen(original);
        if (window.alertify) alertify.message("✅ Tarjeta de circulación comprimida: " + (archivoFinal.size / 1024).toFixed(1) + " KB");
      }

      formData.delete("archivo_verificacion");
      formData.append("archivo_verificacion", archivoFinal, archivoFinal.name || original.name || "tarjeta_circulacion.jpg");
    }

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


// === Confirmación de eliminación ===
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


// === SWIPE PARA ELIMINAR ===
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

  // Desktop
  tr.addEventListener('mousedown', e => startX = e.clientX);
  tr.addEventListener('mousemove', e => {
    if (e.buttons === 1) {
      const diff = e.clientX - startX;
      handleSwipe(diff);
    }
  });

  // Mobile
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

// === 🔎 FILTRO DE BÚSQUEDA ===
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
