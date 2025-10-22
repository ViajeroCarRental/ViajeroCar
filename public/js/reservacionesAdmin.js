/* ================================
   🎨 Helpers visuales y básicos
================================ */
const $ = s => document.querySelector(s);
const $$ = s => Array.from(document.querySelectorAll(s));

/* Escapar HTML */
const esc = s => (s ?? '').toString()
  .replace(/[&<>"]/g, m => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;' }[m]));

/* Mostrar/Ocultar menú lateral */
$('#burger')?.addEventListener('click', () => $('#side').classList.toggle('show'));

/* ================================
   📑 Manejo de pasos visuales
================================ */
window.addEventListener('load', () => localStorage.removeItem('vc_reserva_tmp'));
const showStep = n => {
  $$('[data-step]').forEach(el => el.style.display = (Number(el.dataset.step) === n ? 'block' : 'none'));
};
$('#go2')?.addEventListener('click', () => showStep(2));
$('#back1')?.addEventListener('click', () => showStep(1));
$('#go3')?.addEventListener('click', () => showStep(3));
$('#back2')?.addEventListener('click', () => showStep(2));
showStep(1);

/* ================================
   📅 DatePicker
================================ */
const rangeDP = { start: null, end: null, month: new Date(), dragging: false };

function openRangeDP() {
  $('#dpPop').classList.add('show');
  buildRangeDP();
}

function buildRangeDP() {
  const cont = $('#dpGrid');
  cont.innerHTML = '';

  const m0 = new Date(rangeDP.month.getFullYear(), rangeDP.month.getMonth(), 1);
  $('#dpMonth').textContent = m0.toLocaleString('es-MX', { month: 'long', year: 'numeric' })
    .replace(/^./, c => c.toUpperCase());

  const days = ['L', 'M', 'M', 'J', 'V', 'S', 'D'];
  days.forEach(d => {
    const el = document.createElement('div');
    el.className = 'dow';
    el.textContent = d;
    cont.appendChild(el);
  });

  const pad = (m0.getDay() + 6) % 7;
  for (let i = 0; i < pad; i++) cont.insertAdjacentHTML('beforeend', '<div></div>');

  const last = new Date(m0.getFullYear(), m0.getMonth() + 1, 0).getDate();

  for (let d = 1; d <= last; d++) {
    const el = document.createElement('div');
    const current = new Date(m0.getFullYear(), m0.getMonth(), d);
    const today = new Date();
    today.setHours(0, 0, 0, 0);

    if (current < today) {
      el.className = 'cell disabled';
      el.textContent = d;
      el.style.opacity = '0.4';
      el.style.pointerEvents = 'none';
    } else {
      el.className = 'cell';
      el.textContent = d;
      el.addEventListener('click', () => selectDate(d, m0));
    }
    cont.appendChild(el);
  }

  refreshDPUI();
}

function selectDate(day, baseMonth) {
  const selected = new Date(baseMonth.getFullYear(), baseMonth.getMonth(), day);
  if (!rangeDP.start || (rangeDP.start && rangeDP.end)) {
    rangeDP.start = selected;
    rangeDP.end = null;
  } else {
    if (selected >= rangeDP.start) {
      rangeDP.end = selected;
    } else {
      rangeDP.end = rangeDP.start;
      rangeDP.start = selected;
    }
  }
  refreshDPUI();
}

function refreshDPUI() {
  $$('#dpGrid .cell').forEach(c => {
    c.classList.remove('range-start', 'range-end', 'in-range');
    const d = Number(c.textContent);
    const current = new Date(rangeDP.month.getFullYear(), rangeDP.month.getMonth(), d);
    if (rangeDP.start && sameDay(current, rangeDP.start)) c.classList.add('range-start');
    if (rangeDP.end && sameDay(current, rangeDP.end)) c.classList.add('range-end');
    if (rangeDP.start && rangeDP.end && current > rangeDP.start && current < rangeDP.end)
      c.classList.add('in-range');
  });
}

function sameDay(a, b) {
  return a.getFullYear() === b.getFullYear() &&
         a.getMonth() === b.getMonth() &&
         a.getDate() === b.getDate();
}

$('#dpPrev')?.addEventListener('click', () => { rangeDP.month.setMonth(rangeDP.month.getMonth() - 1); buildRangeDP(); });
$('#dpNext')?.addEventListener('click', () => { rangeDP.month.setMonth(rangeDP.month.getMonth() + 1); buildRangeDP(); });
$('#dpApply')?.addEventListener('click', () => {
  if (rangeDP.start) $('#fIniBox').textContent = rangeDP.start.toISOString().slice(0, 10);
  if (rangeDP.end) $('#fFinBox').textContent = rangeDP.end.toISOString().slice(0, 10);

  if (rangeDP.start && rangeDP.end) {
    const diffTime = Math.abs(rangeDP.end - rangeDP.start);
    let diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));
    if (diffDays === 0) diffDays = 1;
    $('#diasBadge').textContent = `${diffDays} día(s)`;
    updateResumen(null, diffDays);
  }

  $('#dpPop').classList.remove('show');
});
$('#dpToday')?.addEventListener('click', () => { rangeDP.start = new Date(); rangeDP.end = null; buildRangeDP(); });
$('#dpClear')?.addEventListener('click', () => { rangeDP.start = rangeDP.end = null; refreshDPUI(); });
$('#dpPop')?.addEventListener('click', e => { if (e.target.id === 'dpPop') $('#dpPop').classList.remove('show'); });
$$('[data-dp]').forEach(el => el.addEventListener('click', openRangeDP));

/* ================================
   🕒 TimePicker
================================ */
let tpAnchor = null;
let mode12h = true;
let ampm = 'AM';

function openTP(anchor) {
  tpAnchor = anchor;
  $('#tpPop').classList.add('show');
  buildTPList();
}

function buildTPList() {
  const sel = $('#tpSelect');
  sel.innerHTML = '';
  const step = 15;
  for (let h = 0; h < 24; h++) {
    for (let m = 0; m < 60; m += step) {
      let label = '';
      if (mode12h) {
        const suffix = h >= 12 ? 'PM' : 'AM';
        const hh = ((h + 11) % 12 + 1).toString().padStart(2, '0');
        const mm = m.toString().padStart(2, '0');
        label = `${hh}:${mm} ${suffix}`;
      } else {
        label = `${h.toString().padStart(2, '0')}:${m.toString().padStart(2, '0')}`;
      }
      const opt = document.createElement('option');
      opt.value = label;
      opt.textContent = label;
      sel.appendChild(opt);
    }
  }
}

$('#tp12')?.addEventListener('click', () => { mode12h = true; buildTPList(); });
$('#tp24')?.addEventListener('click', () => { mode12h = false; buildTPList(); });
$('#tpAM')?.addEventListener('click', () => { ampm = 'AM'; });
$('#tpPM')?.addEventListener('click', () => { ampm = 'PM'; });
$('#tpClose')?.addEventListener('click', () => { $('#tpPop').classList.remove('show'); });

$('#tpApply')?.addEventListener('click', () => {
  const val = $('#tpSelect').value;
  if (tpAnchor && val) tpAnchor.textContent = mode12h ? val.replace(/(AM|PM)/, ampm) : val;
  $('#tpPop').classList.remove('show');
});
$('#tpClear')?.addEventListener('click', () => { if (tpAnchor) tpAnchor.textContent = '--:--'; $('#tpPop').classList.remove('show'); });
$('#tpPop')?.addEventListener('click', e => { if (e.target.id === 'tpPop') $('#tpPop').classList.remove('show'); });
$$('[data-tp]').forEach(el => el.addEventListener('click', () => openTP(el)));
$$('.tp-q').forEach(el => {
  el.addEventListener('click', () => {
    const val = el.dataset.q;
    const now = new Date();
    if (val === 'now') {
      let hh = now.getHours(), mm = now.getMinutes();
      if (mode12h) {
        ampm = hh >= 12 ? 'PM' : 'AM';
        hh = ((hh + 11) % 12 + 1);
      }
      tpAnchor.textContent = `${hh.toString().padStart(2, '0')}:${mm.toString().padStart(2, '0')} ${ampm}`;
    } else if (val.startsWith('+')) {
      now.setMinutes(now.getMinutes() + parseInt(val.slice(1)) || 0);
      const hh = ((now.getHours() + 11) % 12 + 1);
      const mm = now.getMinutes().toString().padStart(2, '0');
      ampm = now.getHours() >= 12 ? 'PM' : 'AM';
      tpAnchor.textContent = `${hh}:${mm} ${ampm}`;
    } else tpAnchor.textContent = val;
    $('#tpPop').classList.remove('show');
  });
});

/* ================================
   🚘 Vehículos dinámicos
================================ */
const vehList = $('#vehList');
const vehModal = $('#vehPop');
const vehInput = $('#vehiculoSel');
const categoriaSelect = $('#categoriaSelect');

$('#btnVeh')?.addEventListener('click', async () => {
  const idCat = categoriaSelect?.value;
  if (!idCat) return alert('Selecciona una categoría primero.');
  vehModal.classList.add('show');
  vehList.innerHTML = '<div style="padding:12px;">Cargando vehículos...</div>';
  try {
    const res = await fetch(`/admin/reservaciones/vehiculos/${idCat}`);
    const data = await res.json();
    if (!data.length) return vehList.innerHTML = '<div style="padding:12px;">No hay vehículos disponibles.</div>';
    vehList.innerHTML = data.map(v => `
      <div class="veh-card" style="display:flex;gap:16px;align-items:center;justify-content:space-between;border:1px solid #ddd;border-radius:12px;padding:10px;margin-bottom:10px;">
        <div style="display:flex;gap:12px;align-items:center;">
          <img src="${v.imagen ?? '/assets/media/no-image.png'}" alt="auto" style="width:140px;height:90px;object-fit:cover;border-radius:8px;">
          <div>
            <div style="font-weight:700;">${esc(v.nombre_publico)}</div>
            <div style="font-size:13px;color:#666;">${esc(v.marca)} ${esc(v.modelo)} ${v.anio}</div>
            <div style="font-size:12px;color:#777;">${v.transmision || '—'} · ${v.asientos} asientos · ${v.puertas} puertas</div>
          </div>
        </div>
        <div style="text-align:right;">
          <div style="font-weight:700;font-size:15px;color:#d00;">$${Number(v.precio_dia).toLocaleString()} MXN</div>
          <button class="btn primary btnSelectVeh" data-id="${v.id_vehiculo}" data-nombre="${esc(v.nombre_publico)}" data-precio="${v.precio_dia}">Seleccionar</button>
        </div>
      </div>
    `).join('');
  } catch (err) {
    console.error(err);
    vehList.innerHTML = '<div style="padding:12px;color:#d00;">Error al cargar los vehículos.</div>';
  }
});
$('#vehClose')?.addEventListener('click', () => vehModal.classList.remove('show'));
vehModal?.addEventListener('click', e => { if (e.target.id === 'vehPop') vehModal.classList.remove('show'); });
vehList?.addEventListener('click', e => {
  const btn = e.target.closest('.btnSelectVeh');
  if (!btn) return;

  const idVehiculo = btn.dataset.id;
  const nombre = btn.dataset.nombre;
  const precio = parseFloat(btn.dataset.precio || 0);
  const imgSrc = btn.closest('.veh-card')?.querySelector('img')?.src || '/assets/media/no-image.png';

  sessionStorage.setItem('id_vehiculo', idVehiculo);


  vehInput.value = nombre;
  vehModal.classList.remove('show');
  updateResumen(precio);
  $('#vehImage').src = imgSrc;
  $('#vehName').textContent = nombre;
  $('#vehImageWrap').style.display = 'block';
});

/* ================================
   💰 Resumen, totales y complementos
================================ */
let precioSeleccionado = 0;
let diasSeleccionados = 1;
let seguroSeleccionado = null;
let adicionalesSeleccionados = []; // 🔹 Guarda los servicios seleccionados con cantidad

function updateResumen(precioDia = null, dias = null) {
  if (precioDia !== null) precioSeleccionado = precioDia;
  if (dias !== null) diasSeleccionados = dias;
  actualizarTotal();
}

/* ================================
   💵 Cálculo total actualizado
================================ */
const selectMoneda = document.getElementById('moneda');
const tcInput = document.getElementById('tc');

function actualizarTotal() {
  const base = precioSeleccionado * diasSeleccionados;
  const proteccion = seguroSeleccionado ? seguroSeleccionado.precio * diasSeleccionados : 0;
  const extras = adicionalesSeleccionados.reduce(
    (sum, a) => sum + (a.precio * a.cantidad * diasSeleccionados),
    0
  );
  const subtotal = base + proteccion + extras;
  const iva = subtotal * 0.16;
  const total = subtotal + iva;

  const moneda = selectMoneda.value;
  const tc = parseFloat(tcInput.value || 17);
  const conv = moneda === 'USD' ? (1 / tc) : 1;

  $('#subTot').textContent = `$${(subtotal * conv).toFixed(2)} ${moneda}`;
  $('#iva').textContent = `$${(iva * conv).toFixed(2)} ${moneda}`;
  $('#total').textContent = `$${(total * conv).toFixed(2)} ${moneda}`;
  $('#proteName').textContent = seguroSeleccionado ? seguroSeleccionado.nombre : '—';
  $('#extrasName').textContent = adicionalesSeleccionados.length
    ? adicionalesSeleccionados.map(a => `${a.cantidad}× ${a.nombre}`).join(', ')
    : '—';
}
selectMoneda?.addEventListener('change', actualizarTotal);
tcInput?.addEventListener('input', actualizarTotal);

/* ================================
   🔒 Protecciones (seguros)
================================ */
const btnProtecciones = $('#btnProtecciones');
const proteModal = $('#proteccionPop');
const proteList = $('#proteList');
const proteInput = $('#proteccionSel');

// 🔹 Botón para limpiar selección
let proteRemove = document.createElement('button');
proteRemove.textContent = '✖';
proteRemove.className = 'btn gray';
proteRemove.style.marginLeft = '6px';
proteRemove.style.display = 'none';
proteInput.parentNode.appendChild(proteRemove);

proteRemove.addEventListener('click', () => {
  seguroSeleccionado = null;
  proteInput.value = 'Ninguna protección seleccionada';
  proteRemove.style.display = 'none';
  actualizarTotal();
});

$('#proteClose')?.addEventListener('click', () => proteModal.classList.remove('show'));
$('#proteCancel')?.addEventListener('click', () => proteModal.classList.remove('show'));
proteModal?.addEventListener('click', e => { if (e.target.id === 'proteccionPop') proteModal.classList.remove('show'); });

btnProtecciones?.addEventListener('click', async () => {
  proteModal.classList.add('show');
  proteList.innerHTML = '<div style="text-align:center;padding:12px;">Cargando protecciones...</div>';
  try {
    const res = await fetch('/admin/reservaciones/seguros');
    const data = await res.json();
    if (!data.length)
      return proteList.innerHTML = '<div style="text-align:center;padding:12px;">No hay seguros disponibles.</div>';

    proteList.innerHTML = data.map(s => `
      <div class="seg-card" data-id="${s.id_paquete}" data-nombre="${esc(s.nombre)}" data-precio="${s.precio_por_dia}"
           style="border:1px solid #ddd;border-radius:12px;padding:16px;margin-bottom:10px;text-align:center;background:#f8fafc;">
        <div style="font-weight:700;">${s.nombre}</div>
        <div style="font-size:13px;color:#475467;min-height:40px;">${s.descripcion ?? ''}</div>
        <div style="font-weight:600;color:#d00;margin-top:6px;">$${Number(s.precio_por_dia).toFixed(2)} MXN/día</div>
        <div class="confirm-btn-wrap">
  <label class="confirm-switch">
    <input type="checkbox" class="switch-seguro" data-id="${s.id_paquete}" data-nombre="${esc(s.nombre)}" data-precio="${s.precio_por_dia}">
    <span class="slider-round"></span>
    <span class="switch-label">Confirmar</span>
  </label>
</div>

      </div>
    `).join('');
  } catch (err) {
    console.error('Error al cargar seguros:', err);
    proteList.innerHTML = '<div style="color:#d00;padding:10px;">Error al cargar los paquetes.</div>';
  }
});

/* ================================
   🟢 Evento de activación del seguro (nuevo switch verde)
================================ */
document.addEventListener('change', e => {
  if (!e.target.classList.contains('switch-seguro')) return;

  const id = e.target.dataset.id;
  const nombre = e.target.dataset.nombre;
  const precio = parseFloat(e.target.dataset.precio);

  // Desactivar todos los demás switches (solo uno activo a la vez)
  document.querySelectorAll('.switch-seguro').forEach(chk => {
    if (chk !== e.target) chk.checked = false;
  });

  // Si se activa el actual
  if (e.target.checked) {
    seguroSeleccionado = { id, nombre, precio };
    proteInput.value = `${nombre} - $${precio}/día`;
    proteRemove.style.display = 'inline-block';
    //proteModal.classList.remove('show'); // cerrar modal automáticamente
  } else {
    // Si se desactiva, limpiar selección
    seguroSeleccionado = null;
    proteInput.value = 'Ninguna protección seleccionada';
    proteRemove.style.display = 'none';
  }

  actualizarTotal();
});


/* ================================
   🧩 Cargar servicios adicionales
================================ */
async function cargarAdicionales() {
  const grid = $('#addGrid');
  grid.innerHTML = '<div class="loading">Cargando adicionales...</div>';
  try {
    const resp = await fetch('/admin/reservaciones/servicios');
    const data = await resp.json();
    if (!data.length)
      return grid.innerHTML = '<div class="empty">No hay servicios adicionales disponibles.</div>';

    grid.innerHTML = data.map(serv => `
      <div class="add-card" data-id="${serv.id_servicio}" data-nombre="${esc(serv.nombre)}" data-precio="${serv.precio}"
           style="border:1px solid #D0D5DD;border-radius:10px;padding:12px;margin-bottom:10px;background:#fff;">
        <div style="display:flex;justify-content:space-between;align-items:center;">
          <div>
            <div style="font-weight:700;">${serv.nombre}</div>
            <div style="font-size:13px;color:#475467;">${serv.descripcion || ''}</div>
            <div style="font-weight:600;color:#2563eb;margin-top:4px;">$${parseFloat(serv.precio).toFixed(2)} MXN/día</div>
          </div>
          <div style="display:flex;align-items:center;gap:6px;">
            <button class="btn gray menos" style="padding:4px 10px;">−</button>
            <span class="cantidad" style="min-width:20px;text-align:center;">0</span>
            <button class="btn gray mas" style="padding:4px 10px;">+</button>
          </div>
        </div>
      </div>
    `).join('');
  } catch (err) {
    console.error('Error cargando servicios:', err);
    grid.innerHTML = '<div class="error">No se pudieron cargar los servicios adicionales.</div>';
  }
}

$('#go2')?.addEventListener('click', cargarAdicionales);

// 🔹 Controlar incremento/decremento
document.addEventListener('click', e => {
  const card = e.target.closest('.add-card');
  if (!card) return;
  const id = card.dataset.id;
  const nombre = card.dataset.nombre;
  const precio = parseFloat(card.dataset.precio);
  const span = card.querySelector('.cantidad');

  if (e.target.classList.contains('mas')) {
    let actual = parseInt(span.textContent) || 0;
    actual++;
    span.textContent = actual;
    let existe = adicionalesSeleccionados.find(a => a.id === id);
    if (existe) existe.cantidad = actual;
    else adicionalesSeleccionados.push({ id, nombre, precio, cantidad: actual });
  }

  if (e.target.classList.contains('menos')) {
    let actual = parseInt(span.textContent) || 0;
    if (actual > 0) actual--;
    span.textContent = actual;
    let existe = adicionalesSeleccionados.find(a => a.id === id);
    if (existe) {
      existe.cantidad = actual;
      if (actual === 0)
        adicionalesSeleccionados = adicionalesSeleccionados.filter(a => a.id !== id);
    }
  }

  actualizarTotal();
});

/* ================================
   💾 Persistencia temporal
================================ */
function saveTempData() {
  const tmp = {
    entrega: $('#entregaSelect')?.value || '',
    devolucion: $('#devolucionSelect')?.value || '',
    categoria: $('#categoriaSelect')?.value || '',
    vehiculo: $('#vehiculoSel')?.value || '',
    fIni: $('#fIniBox')?.textContent.trim() || '',
    fFin: $('#fFinBox')?.textContent.trim() || '',
    hIni: $('#hIniBox')?.textContent.trim() || '',
    hFin: $('#hFinBox')?.textContent.trim() || '',
    seguro: seguroSeleccionado,
    adicionales: adicionalesSeleccionados
  };
  localStorage.setItem('vc_reserva_tmp', JSON.stringify(tmp));
}

function loadTempData() {
  try {
    const t = JSON.parse(localStorage.getItem('vc_reserva_tmp') || 'null');
    if (!t) return;

    $('#entregaSelect').value = t.entrega || '';
    $('#devolucionSelect').value = t.devolucion || '';
    $('#categoriaSelect').value = t.categoria || '';
    $('#vehiculoSel').value = t.vehiculo || '';
    if (t.fIni) $('#fIniBox').textContent = t.fIni;
    if (t.fFin) $('#fFinBox').textContent = t.fFin;
    if (t.hIni) $('#hIniBox').textContent = t.hIni;
    if (t.hFin) $('#hFinBox').textContent = t.hFin;

    // 🔹 Restaurar seguro
    if (t.seguro) {
      seguroSeleccionado = t.seguro;
      proteInput.value = `${t.seguro.nombre} - $${t.seguro.precio}/día`;
      proteRemove.style.display = 'inline-block';
    }

    // 🔹 Restaurar adicionales
    if (Array.isArray(t.adicionales)) {
      adicionalesSeleccionados = t.adicionales;
      adicionalesSeleccionados.forEach(a => {
        const card = document.querySelector(`.add-card[data-id="${a.id}"]`);
        if (card) card.querySelector('.cantidad').textContent = a.cantidad;
      });
    }

    actualizarTotal();
  } catch (err) {
    console.warn('Error restaurando datos temporales:', err);
  }
}

$$('input, select').forEach(el => el.addEventListener('change', saveTempData));
loadTempData();
$('#saveAll')?.addEventListener('click', () => { saveTempData(); alert('Datos listos para enviar al backend.'); });
$('#saveDraft')?.addEventListener('click', () => { saveTempData(); alert('Borrador guardado localmente.'); });

/* ================================
   📤 Envío del formulario FINAL
================================ */
document.addEventListener('DOMContentLoaded', () => {
  const btn = document.getElementById('btnReservar');
  if (!btn) return;

  btn.addEventListener('click', async (e) => {
    e.preventDefault();

    const payload = {
      id_vehiculo: sessionStorage.getItem('id_vehiculo') || '',
      sucursal_retiro: $('#entregaSelect')?.value || '',
      sucursal_entrega: $('#devolucionSelect')?.value || '',
      fecha_inicio: $('#fIniBox')?.textContent.trim() || '',
      fecha_fin: $('#fFinBox')?.textContent.trim() || '',
      hora_retiro: $('#hIniBox')?.textContent.trim() || '',
      hora_entrega: $('#hFinBox')?.textContent.trim() || '',
      subtotal: $('#subTot')?.textContent.replace(/[^\d.]/g, '') || '0',
      impuestos: $('#iva')?.textContent.replace(/[^\d.]/g, '') || '0',
      total: $('#total')?.textContent.replace(/[^\d.]/g, '') || '0',
      moneda: $('#moneda')?.value || 'MXN',
      nombre_cliente: $('#nombreCliente')?.value?.trim() || '',
      email_cliente: $('#correoCliente')?.value?.trim() || '',
      telefono_cliente: $('#telefonoCliente')?.value?.trim() || '',
      no_vuelo: $('#cVuelo')?.value?.trim() || ''
    };

    console.log('🧾 Datos enviados:', payload);

    if (!payload.id_vehiculo) return alert('⚠️ Falta seleccionar un vehículo.');
    if (!payload.sucursal_retiro || !payload.sucursal_entrega) return alert('⚠️ Selecciona sucursal de entrega y devolución.');
    if (!payload.fecha_inicio || !payload.fecha_fin) return alert('⚠️ Completa las fechas de la reserva.');

    try {
      const res = await fetch('/reservaciones/guardar', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
          'Accept': 'application/json'
        },
        body: JSON.stringify(payload)
      });

      const data = await res.json();
      if (!res.ok || !data.success) throw new Error(data.message || 'Error en backend');

      alertify.success('✅ Reservación registrada correctamente.');
      alertify.notify(`Código: <b>${data.codigo}</b>`, 'custom', 8);
      sessionStorage.clear();
    } catch (err) {
      console.error('❌ Error al enviar reservación:', err);
      alertify.error('Error al guardar la reservación.');
    }
  });
});
