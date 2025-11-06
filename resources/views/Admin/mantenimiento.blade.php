@extends('layouts.Flotillas')
@section('Titulo', 'Mantenimiento')

@section('css-vistaMantenimiento')
<link rel="stylesheet" href="{{ asset('css/mantenimiento.css') }}">

@endsection

@section('contenidoMantenimiento')
<main>
  <div class="content">
    <h1 class="title">Mantenimiento de Flotilla</h1>
    <p class="small-muted">Actualiza el kilometraje o registra mantenimientos. Los colores indican el estado actual.</p>

    <!-- 🔍 Buscador -->
    <div class="buscador-flotilla">
      <i class="fas fa-search icono-buscar"></i>
      <input 
        type="text" 
        id="filtroMantenimiento" 
        placeholder="Buscar por modelo, marca o placa...">
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
              <div class="small-muted">Placa: <b>{{ $v->placa ?? '—' }}</b></div>
            </div>
          </div>

          <div class="row"><div>Kilometraje:</div><div id="km-{{ $v->id_vehiculo }}">{{ number_format($v->kilometraje) }} km</div></div>
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

              <div style="display:flex;gap:12px;margin-top:6px;">
                <div><b>Último servicio km</b><div id="m-lastkm-{{ $v->id_vehiculo }}">{{ $v->ultimo_km_servicio ? number_format($v->ultimo_km_servicio).' km' : '—' }}</div></div>
                <div style="margin-left:auto;text-align:right;"><b>Faltan</b><div id="m-left-{{ $v->id_vehiculo }}">{{ number_format($v->km_para_proximo) }} km</div></div>
              </div>

              <hr>

              <div><b>Estado técnico</b></div>
              <div style="margin-top:6px;">
                <div><span class="icon-{{ $v->cambio_aceite ? 'yes' : 'no' }}">{{ $v->cambio_aceite ? '✔' : '✖' }}</span> Cambio de aceite @if($v->tipo_aceite)<small class="small-muted">({{ $v->tipo_aceite }})</small>@endif</div>
                <div><span class="icon-{{ $v->rotacion_llantas ? 'yes' : 'no' }}">{{ $v->rotacion_llantas ? '✔' : '✖' }}</span> Rotación de llantas</div>
                <div><span class="icon-{{ $v->cambio_filtro ? 'yes' : 'no' }}">{{ $v->cambio_filtro ? '✔' : '✖' }}</span> Cambio de filtro</div>
                <div><span class="icon-{{ $v->cambio_pastillas ? 'yes' : 'no' }}">{{ $v->cambio_pastillas ? '✔' : '✖' }}</span> Cambio de frenos</div>
                <div style="margin-top:6px;"><b>Notas:</b><div id="m-notes-{{ $v->id_vehiculo }}">{{ $v->observaciones ?? '—' }}</div></div>
              </div>

              <hr>

              <!-- Formulario AJAX -->
              <form id="form-{{ $v->id_vehiculo }}" onsubmit="submitMaintenance(event, {{ $v->id_vehiculo }})">
                @csrf
                <label>Kilometraje actual</label>
                <input class="input" type="number" name="kilometraje_servicio" value="{{ $v->kilometraje }}" required>

                <label>Intervalo de mantenimiento (km)</label>
                <select name="intervalo_km" class="input">
                  <option value="10000" {{ ($v->intervalo_km ?? 10000) == 10000 ? 'selected' : '' }}>Cada 10,000 km</option>
                  <option value="15000" {{ ($v->intervalo_km ?? 10000) == 15000 ? 'selected' : '' }}>Cada 15,000 km</option>
                  <option value="20000" {{ ($v->intervalo_km ?? 10000) == 20000 ? 'selected' : '' }}>Cada 20,000 km</option>
                </select>

                <label>Costo</label>
                <input class="input" type="number" step="0.01" name="costo_servicio" value="{{ $v->costo_servicio ?? 0 }}">

                <div class="checkbox-row">
                  <label><input type="checkbox" name="cambio_aceite" {{ $v->cambio_aceite ? 'checked' : '' }}> Cambio de aceite</label>
                  <label><input type="checkbox" name="rotacion_llantas" {{ $v->rotacion_llantas ? 'checked' : '' }}> Rotación de llantas</label>
                  <label><input type="checkbox" name="cambio_filtro" {{ $v->cambio_filtro ? 'checked' : '' }}> Cambio de filtro</label>
                  <label><input type="checkbox" name="cambio_pastillas" {{ $v->cambio_pastillas ? 'checked' : '' }}> Cambio de frenos</label>
                </div>

                <label>Tipo aceite (opcional)</label>
                <input class="input" type="text" name="tipo_aceite" value="{{ $v->tipo_aceite ?? '' }}">

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
}
function closeModal(id){
  document.getElementById('modal-'+id).classList.remove('show');
  document.getElementById('modal-'+id).setAttribute('aria-hidden','true');
}

// Enviar por AJAX y actualizar todo dinámicamente
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

    // Actualizar datos visuales
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

    // Toast notification
    const t = document.getElementById('toast');
    t.textContent = data.mensaje || 'Guardado correctamente';
    t.classList.add('show');
    setTimeout(()=> t.classList.remove('show'), 2500);

    closeModal(id);
  } catch (error) {
    console.error(error);
    alert('Error de conexión. Intente nuevamente.');
  }
}

// === 🔎 FILTRO DE MANTENIMIENTO ===
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
