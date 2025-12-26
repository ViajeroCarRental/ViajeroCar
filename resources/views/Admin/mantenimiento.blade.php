@extends('layouts.Flotillas')
@section('Titulo', 'Mantenimiento')

@section('css-vistaMantenimiento')
<link rel="stylesheet" href="{{ asset('css/mantenimiento.css') }}">
<style>
</style>
@endsection

@section('contenidoMantenimiento')
<main>
  <div class="content">
    <h1 class="title">Mantenimiento de Flotilla</h1>
    <p class="small-muted">Actualiza el kilometraje o registra mantenimientos. Los colores indican el estado actual.</p>

    <!-- 🔍 Buscador -->
    <div class="buscador-flotilla">
      <i class="fas fa-search icono-buscar"></i>
      <input type="text" id="filtroMantenimiento" placeholder="Buscar por modelo, marca o placa...">
    </div>

    <div class="mgrid">
      @foreach($vehiculos as $v)
        <div id="card-{{ $v->id_vehiculo }}" class="mcard {{ $v->estado_mantenimiento }}">
          <span class="badge">{{ ucfirst($v->estatus ?? 'Desconocido') }}</span>

          <div class="mhead">
            <div>
              <h3>{{ $v->marca }} {{ $v->modelo }}
                <small style="color:#6b7280;font-weight:600;">({{ $v->anio }})</small>
              </h3>
              <div class="small-muted">Placa: <b>{{ !empty($v->placa) ? $v->placa : '—' }}</b></div>
            </div>
          </div>

          <div class="row"><div>Kilometraje:</div><div id="km-{{ $v->id_vehiculo }}">{{ number_format($v->kilometraje) }} km</div></div>
          <div class="row">
  <div>Aceite:</div>
  <div id="oil-{{ $v->id_vehiculo }}">
    {{ !empty($v->aceite) ? $v->aceite : '—' }}
  </div>
</div>

          <div class="row"><div>Último servicio:</div><div id="last-{{ $v->id_vehiculo }}">{{ $v->ultimo_km_servicio ? number_format($v->ultimo_km_servicio).' km' : '—' }}</div></div>
          <div class="row"><div>Próximo servicio:</div><div id="next-{{ $v->id_vehiculo }}">{{ number_format($v->proximo_servicio) }} km</div></div>
          <div class="row"><div>Faltan:</div><div id="left-{{ $v->id_vehiculo }}">{{ number_format($v->km_para_proximo) }} km</div></div>

          <div style="text-align:right;margin-top:10px;">
            <button class="btn" onclick="openModal({{ $v->id_vehiculo }})">🧾 Ver detalles</button>
          </div>
        </div>

        <!-- Modal -->
        <div id="modal-{{ $v->id_vehiculo }}" class="modal" aria-hidden="true">
          <div class="modal-content">
            <div class="modal-header">
              <div>
                <h3>Ficha de mantenimiento</h3>
                <div class="small-muted">
                  {{ $v->marca }} {{ $v->modelo }} • Placa: <b>{{ $v->placa ?? '—' }}</b>
                </div>
              </div>
              <div style="margin-left:auto; text-align:right;">
                <div class="small-muted">Estado</div>
                <div id="status-dot-{{ $v->id_vehiculo }}" class="status"
                     style="background:{{ $v->estado_mantenimiento == 'rojo' ? '#ef4444' : ($v->estado_mantenimiento=='amarillo' ? '#f59e0b' : '#16a34a') }};">
                </div>
              </div>
            </div>

            <div class="modal-body">
              <p><b>Último servicio:</b> <span id="m-last-{{ $v->id_vehiculo }}">{{ $v->fecha_servicio ?? '—' }}</span></p>
              <p><b>Kilometraje (vehículo):</b> <span id="m-km-{{ $v->id_vehiculo }}">{{ number_format($v->kilometraje) }} km</span></p>
              <p><b>Aceite:</b> <span id="m-oil-{{ $v->id_vehiculo }}">{{ !empty($v->aceite) ? $v->aceite : '—' }}</span></p>

              <div style="display:flex;gap:12px;margin-top:6px;">
                <div><b>Último servicio km</b><div id="m-lastkm-{{ $v->id_vehiculo }}">{{ $v->ultimo_km_servicio ? number_format($v->ultimo_km_servicio).' km' : '—' }}</div></div>
                <div style="margin-left:auto;text-align:right;"><b>Faltan</b><div id="m-left-{{ $v->id_vehiculo }}">{{ number_format($v->km_para_proximo) }} km</div></div>
              </div>

              <hr>

              <div><b>Estado técnico</b>
                <small style="color:#6b7280;">(Último servicio: <span id="m-servicioKm-{{ $v->id_vehiculo }}">{{ $v->ultimo_km_servicio ?? '—' }}</span> km)</small>
              </div>

              <div id="estadoTecnico-{{ $v->id_vehiculo }}" style="margin-top:6px;">
                @if($v->tipo_mantenimiento == 'menor')
                  <div><span class="icon-{{ $v->rellenar_aceite ? 'yes' : 'no' }}">{{ $v->rellenar_aceite ? '✔' : '✖' }}</span> Rellenar aceite</div>
                  <div><span class="icon-{{ $v->nivel_agua ? 'yes' : 'no' }}">{{ $v->nivel_agua ? '✔' : '✖' }}</span> Revisar nivel de agua</div>
                  <div><span class="icon-{{ $v->presion_llantas ? 'yes' : 'no' }}">{{ $v->presion_llantas ? '✔' : '✖' }}</span> Revisar presión de llantas</div>
                  <div><span class="icon-{{ $v->limpieza_general ? 'yes' : 'no' }}">{{ $v->limpieza_general ? '✔' : '✖' }}</span> Limpieza general</div>
                @elseif($v->tipo_mantenimiento == 'mayor')
                  <div><span class="icon-{{ $v->cambio_aceite ? 'yes' : 'no' }}">{{ $v->cambio_aceite ? '✔' : '✖' }}</span> Cambio de aceite</div>
                  <div><span class="icon-{{ $v->rotacion_llantas ? 'yes' : 'no' }}">{{ $v->rotacion_llantas ? '✔' : '✖' }}</span> Rotación de llantas</div>
                  <div><span class="icon-{{ $v->cambio_filtro ? 'yes' : 'no' }}">{{ $v->cambio_filtro ? '✔' : '✖' }}</span> Cambio de filtro</div>
                  <div><span class="icon-{{ $v->cambio_pastillas ? 'yes' : 'no' }}">{{ $v->cambio_pastillas ? '✔' : '✖' }}</span> Cambio de frenos</div>
                @else
                  <div class="small-muted">Sin información registrada</div>
                @endif
                <div style="margin-top:6px;"><b>Notas:</b><div id="m-notes-{{ $v->id_vehiculo }}">{{ $v->observaciones ?? '—' }}</div></div>
              </div>

              <hr>

              <!-- Formulario AJAX -->
              <form id="form-{{ $v->id_vehiculo }}" onsubmit="submitMaintenance(event, {{ $v->id_vehiculo }})">
                @csrf
                <label>Kilometraje</label>
                <input class="input" type="number" name="kilometraje_servicio" value="{{ $v->kilometraje }}" required>

                <label>Intervalo de mantenimiento (km)</label>
                <select name="intervalo_km" class="input">
                  <option value="10000" {{ ($v->intervalo_km ?? 10000) == 10000 ? 'selected' : '' }}>Cada 10,000 km</option>
                  <option value="15000" {{ ($v->intervalo_km ?? 10000) == 15000 ? 'selected' : '' }}>Cada 15,000 km</option>
                  <option value="20000" {{ ($v->intervalo_km ?? 10000) == 20000 ? 'selected' : '' }}>Cada 20,000 km</option>
                </select>

                <label>Costo</label>
                <input class="input" type="number" step="0.01" name="costo_servicio" value="{{ $v->costo_servicio ?? 0 }}">

                <!-- 🔹 Tipo de mantenimiento -->
                <label>Tipo de mantenimiento</label>
                <select class="input" name="tipo_mantenimiento" id="tipoMantenimiento-{{ $v->id_vehiculo }}" onchange="toggleOpciones({{ $v->id_vehiculo }})">
                  <option value="">Selecciona...</option>
                  <option value="menor" {{ $v->tipo_mantenimiento == 'menor' ? 'selected' : '' }}>Mantenimiento menor</option>
                  <option value="mayor" {{ $v->tipo_mantenimiento == 'mayor' ? 'selected' : '' }}>Mantenimiento mayor</option>
                </select>

                <div id="opcionesBloque-{{ $v->id_vehiculo }}" class="opciones-mantenimiento" style="display:none;">
                  <p id="tituloOpciones-{{ $v->id_vehiculo }}">Opciones de mantenimiento:</p>

                  <!-- 🔸 Menor -->
                  <div id="opcionesMenor-{{ $v->id_vehiculo }}" style="display:none;">
                    <label><input type="checkbox" name="rellenar_aceite" {{ $v->rellenar_aceite ? 'checked' : '' }}> Rellenar aceite</label><br>
                    <label><input type="checkbox" name="nivel_agua" {{ $v->nivel_agua ? 'checked' : '' }}> Revisar nivel de agua</label><br>
                    <label><input type="checkbox" name="presion_llantas" {{ $v->presion_llantas ? 'checked' : '' }}> Revisar presión de llantas</label><br>
                    <label><input type="checkbox" name="limpieza_general" {{ $v->limpieza_general ? 'checked' : '' }}> Limpieza general</label>
                  </div>

                  <!-- 🔸 Mayor -->
                  <div id="opcionesMayor-{{ $v->id_vehiculo }}" style="display:none;">
                    <label><input type="checkbox" name="cambio_frenos" {{ $v->cambio_pastillas ? 'checked' : '' }}> Cambio de frenos</label><br>
                    <label><input type="checkbox" name="cambio_aceite_motor" {{ $v->cambio_aceite ? 'checked' : '' }}> Cambio de aceite de motor</label><br>
                    <label><input type="checkbox" name="cambio_filtros_completo" {{ $v->cambio_filtro ? 'checked' : '' }}> Cambio de todos los filtros</label><br>
                    <label><input type="checkbox" name="revision_general" {{ $v->rotacion_llantas ? 'checked' : '' }}> Revisión general</label>
                  </div>
                </div>

                <label style="margin-top:8px;">Otro</label>
                <input class="input" type="text" name="otro" placeholder="Especifica otro tipo de trabajo..." value="{{ $v->otro ?? '' }}">

                <label style="margin-top:8px;">Observaciones</label>
                <textarea name="observaciones" rows="3" class="input">{{ $v->observaciones ?? '' }}</textarea>

                <div style="display:flex;gap:10px;justify-content:flex-end;margin-top:10px;">
                  <button class="btn" type="submit">Guardar</button>
                  <button type="button" class="btn gray" onclick="closeModal({{ $v->id_vehiculo }})">Cerrar</button>
                </div>
              </form>
            </div>
          </div>
        </div>
      @endforeach
    </div>
  </div>
</main>

<div id="toast" class="toast">Guardado correctamente</div>

<script>
function openModal(id){
  document.getElementById('modal-'+id).classList.add('show');
  document.getElementById('modal-'+id).setAttribute('aria-hidden','false');
  toggleOpciones(id);
}
function closeModal(id){
  document.getElementById('modal-'+id).classList.remove('show');
  document.getElementById('modal-'+id).setAttribute('aria-hidden','true');
}

function toggleOpciones(id) {
  const tipo = document.getElementById('tipoMantenimiento-' + id).value;
  const bloque = document.getElementById('opcionesBloque-' + id);
  const menor = document.getElementById('opcionesMenor-' + id);
  const mayor = document.getElementById('opcionesMayor-' + id);
  const titulo = document.getElementById('tituloOpciones-' + id);

  bloque.style.display = tipo ? 'block' : 'none';
  menor.style.display = 'none';
  mayor.style.display = 'none';

  if (tipo === 'menor') {
    menor.style.display = 'block';
    titulo.textContent = 'Opciones de mantenimiento menor:';
  }
  if (tipo === 'mayor') {
    mayor.style.display = 'block';
    titulo.textContent = 'Opciones de mantenimiento mayor:';
  }
}

// Enviar por AJAX
async function submitMaintenance(e, id){
  e.preventDefault();
  const form = document.getElementById('form-'+id);
  const url = "{{ url('/admin/mantenimiento') }}/" + id + "/registrar";
  const token = '{{ csrf_token() }}';
  const formData = new FormData(form);

  ['cambio_aceite','rotacion_llantas','cambio_filtro','cambio_pastillas'].forEach(k=>{
    if(!formData.has(k)) formData.append(k, '0');
  });

  try {
    const res = await fetch(url, {
      method: 'POST',
      headers: { 'X-CSRF-TOKEN': token, 'Accept': 'application/json' },
      body: formData
    });

    if (!res.ok) {
      const err = await res.json().catch(()=>null);
      let msg = 'Error al guardar';
      if (err && err.errors) msg = Object.values(err.errors).flat().join(' - ');
      alert(msg);
      return;
    }

    const data = await res.json();

    const card = document.getElementById('card-'+id);
    if (card) {
      card.classList.remove('verde','amarillo','rojo');
      card.classList.add(data.estado);
      document.getElementById('km-'+id).textContent = Number(data.kilometraje).toLocaleString() + ' km';
      document.getElementById('last-'+id).textContent = Number(data.ultimo_km_servicio).toLocaleString() + ' km';
      document.getElementById('next-'+id).textContent = Number(data.proximo_servicio).toLocaleString() + ' km';
      document.getElementById('left-'+id).textContent = Number(data.falta).toLocaleString() + ' km';
      document.getElementById('status-dot-'+id).style.background = data.estado === 'rojo' ? '#ef4444' : (data.estado === 'amarillo' ? '#f59e0b' : '#16a34a');
    }

    const t = document.getElementById('toast');
    t.textContent = data.mensaje || 'Guardado correctamente';
    t.classList.add('show');
    setTimeout(()=> t.classList.remove('show'), 2500);
    // 🔄 Actualizar el estado técnico en tiempo real
const estadoDiv = document.getElementById('estadoTecnico-' + id);
if (estadoDiv) {
  let html = '';
  if (formData.get('tipo_mantenimiento') === 'menor') {
    html += `<div><span class="icon-${formData.has('rellenar_aceite') ? 'yes' : 'no'}">${formData.has('rellenar_aceite') ? '✔' : '✖'}</span> Rellenar aceite</div>`;
    html += `<div><span class="icon-${formData.has('nivel_agua') ? 'yes' : 'no'}">${formData.has('nivel_agua') ? '✔' : '✖'}</span> Revisar nivel de agua</div>`;
    html += `<div><span class="icon-${formData.has('presion_llantas') ? 'yes' : 'no'}">${formData.has('presion_llantas') ? '✔' : '✖'}</span> Revisar presión de llantas</div>`;
    html += `<div><span class="icon-${formData.has('limpieza_general') ? 'yes' : 'no'}">${formData.has('limpieza_general') ? '✔' : '✖'}</span> Limpieza general</div>`;
  } else if (formData.get('tipo_mantenimiento') === 'mayor') {
    html += `<div><span class="icon-${formData.has('cambio_aceite_motor') ? 'yes' : 'no'}">${formData.has('cambio_aceite_motor') ? '✔' : '✖'}</span> Cambio de aceite</div>`;
    html += `<div><span class="icon-${formData.has('revision_general') ? 'yes' : 'no'}">${formData.has('revision_general') ? '✔' : '✖'}</span> Revisión general</div>`;
    html += `<div><span class="icon-${formData.has('cambio_filtros_completo') ? 'yes' : 'no'}">${formData.has('cambio_filtros_completo') ? '✔' : '✖'}</span> Cambio de filtros</div>`;
    html += `<div><span class="icon-${formData.has('cambio_frenos') ? 'yes' : 'no'}">${formData.has('cambio_frenos') ? '✔' : '✖'}</span> Cambio de frenos</div>`;
  } else {
    html = `<div class="small-muted">Sin información registrada</div>`;
  }

  estadoDiv.innerHTML = html;
  document.getElementById('m-servicioKm-' + id).textContent = formData.get('kilometraje_servicio');
}


    closeModal(id);
  } catch (error) {
    console.error(error);
    alert('Error de conexión. Intente nuevamente.');
  }
}

// === 🔎 Filtro de búsqueda ===
document.getElementById('filtroMantenimiento').addEventListener('keyup', function() {
  const filtro = this.value.toLowerCase();
  const tarjetas = document.querySelectorAll('.mgrid .mcard');
  tarjetas.forEach(card => {
    const texto = card.textContent.toLowerCase();
    card.style.display = texto.includes(filtro) ? '' : 'none';
  });
});
</script>
@endsection
