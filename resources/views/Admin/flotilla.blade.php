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
if (window.alertify) { // 💡 NUEVO
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


// 🔴🔴🔴 AQUÍ EMPIEZA LO NUEVO DEL ACEITE 🔴🔴🔴
// === SELECT + INPUT PARA TIPO DE ACEITE ===
const aceiteSelect = document.getElementById('aceiteSelect');
const aceiteInput  = document.getElementById('aceiteInput');

if (aceiteSelect && aceiteInput) {
  aceiteSelect.addEventListener('change', () => {
    const val = aceiteSelect.value;

    if (val === 'otro') {
      // Mostrar input para escribir aceite libre
      aceiteInput.style.display = 'block';
      aceiteInput.value = '';
      aceiteInput.focus();
    } else if (val) {
      // Ocultar input y poner el valor seleccionado
      aceiteInput.style.display = 'none';
      aceiteInput.value = val;
    } else {
      aceiteInput.style.display = 'none';
      aceiteInput.value = '';
    }
  });
}
// 🔴🔴🔴 AQUÍ TERMINA LO NUEVO DEL ACEITE 🔴🔴🔴


/* ==========================================================
   📦 FUNCIÓN PARA COMPRIMIR IMÁGENES (NUEVO)
   - SOLO SE USARÁ PARA IMÁGENES, NO PDF
========================================================== */
async function comprimirImagen(file, maxWidth = 1400, quality = 0.7) { // 💡 NUEVO
  return new Promise((resolve, reject) => {
    try {
      const img = new Image();
      const reader = new FileReader();

      reader.onload = (e) => {
        img.src = e.target.result;
      };

      img.onload = () => {
        const canvas = document.createElement("canvas");
        const scale = Math.min(maxWidth / img.width, 1);

        canvas.width = img.width * scale;
        canvas.height = img.height * scale;

        const ctx = canvas.getContext("2d");
        ctx.drawImage(img, 0, 0, canvas.width, canvas.height);

        canvas.toBlob(
          (blob) => {
            if (!blob) {
              resolve(file); // fallback: devolver original si algo falla
              return;
            }
            const nuevoFile = new File([blob], file.name.replace(/\.\w+$/, ".jpg"), {
              type: "image/jpeg"
            });
            resolve(nuevoFile);
          },
          "image/jpeg",
          quality
        );
      };

      reader.readAsDataURL(file);
    } catch (err) {
      console.error("Error al comprimir imagen:", err);
      resolve(file); // si algo truena, se manda el original
    }
  });
}

/* ==========================================================
   🧾 SUBMIT DEL FORMULARIO "AGREGAR AUTO" CON COMPRESIÓN
========================================================== */
const formAddAuto = document.getElementById('formAddAuto'); // 💡 NUEVO

if (formAddAuto) { // 💡 NUEVO
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
    const inputPoliza = formAddAuto.querySelector('input[name="archivo_poliza"]');
    const inputVerif  = formAddAuto.querySelector('input[name="archivo_verificacion"]');

    // Comprimir solo si son imágenes
    if (inputPoliza && inputPoliza.files && inputPoliza.files[0]) {
      const file = inputPoliza.files[0];
      if (file.type.startsWith('image/')) {
        const comprimida = await comprimirImagen(file);
        formData.set('archivo_poliza', comprimida);
      }
    }

    if (inputVerif && inputVerif.files && inputVerif.files[0]) {
      const file = inputVerif.files[0];
      if (file.type.startsWith('image/')) {
        const comprimida = await comprimirImagen(file);
        formData.set('archivo_verificacion', comprimida);
      }
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
        if (window.alertify) {
          alertify.error('❌ Error del servidor (respuesta no válida). Revisa consola.');
        }
        throw err;
      }

      // Manejo de errores de validación (422) u otros
      if (!resp.ok || data.success === false) {
        let msg = data.message || 'Error al guardar el vehículo.';

        if (data.errors) {
          const erroresPlanos = [];
          Object.keys(data.errors).forEach(campo => {
            data.errors[campo].forEach(m => erroresPlanos.push(m));
          });
          if (erroresPlanos.length) {
            msg = erroresPlanos.join('\n');
          }
        }

        if (window.alertify) {
          alertify.error('❌ ' + msg);
        } else {
          console.error(msg);
        }

        return;
      }

      // Éxito
      if (window.alertify) {
        alertify.success(data.message || 'Vehículo agregado correctamente.');
      }

      // Cerrar modal y refrescar tabla (por simplicidad, recarga)
      addModal.classList.remove('active');
      window.location.reload();

    } catch (err) {
      console.error('Error en envío de formulario flotilla:', err);
      if (window.alertify) {
        alertify.error('❌ Error al enviar el formulario. Intenta nuevamente.');
      }
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

  // 1) Abrir modal al click del basurero
  document.querySelectorAll('.delete-bg .delete-form .btnDelete').forEach(btn => {
    btn.addEventListener('click', (e) => {
      formToDelete = e.target.closest('form');
      confirmModal.classList.add('active');
    });
  });

  // 2) Cerrar sin eliminar
  btnCancel.addEventListener('click', () => {
    confirmModal.classList.remove('active');
    formToDelete = null;
  });

  // 3) Confirmar eliminación
  btnConfirm.addEventListener('click', () => {
    if (formToDelete) formToDelete.submit();
  });

  // 4) Cerrar clic fuera
  confirmModal.addEventListener('click', (e) => {
    if (e.target === confirmModal) {
      confirmModal.classList.remove('active');
      formToDelete = null;
    }
  });

  // 5) Cerrar con ESC
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

// Cerrar swipe con ESC o clic fuera de la tabla
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
