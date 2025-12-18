/* ================================
   🎨 Helpers visuales y básicos
================================ */
const $ = s => document.querySelector(s);
const $$ = s => Array.from(document.querySelectorAll(s));

/* Escapar HTML para evitar inyecciones */
const esc = s => (s ?? '').toString()
  .replace(/[&<>"]/g, m => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;' }[m]));

/* Mostrar / ocultar menú lateral */

/* ================================
   📑 Navegación de pasos
================================ */
const showStep = n => {
  $$('[data-step]').forEach(el => el.style.display = (Number(el.dataset.step) === n ? 'block' : 'none'));
};
$('#go2')?.addEventListener('click', () => showStep(2));
$('#back1')?.addEventListener('click', () => showStep(1));
$('#go3')?.addEventListener('click', () => showStep(3));
$('#back2')?.addEventListener('click', () => showStep(2));
showStep(1);

/* ================================
   🚗 Imagen y tarifa por categoría
================================ */
const categoriaSelect = $('#categoriaSelect');
const vehImageWrap = $('#vehImageWrap');
const vehImage = $('#vehImage');
const vehName = $('#vehName');

categoriaSelect?.addEventListener('change', async () => {
  const cat = categoriaSelect.value;

  if (cat == 0) {
    vehImageWrap.style.display = 'none';
    $('#baseLine').textContent = '—';
    updateResumen(0);
    return;
  }

  try {
    // 🔹 Obtiene datos de la categoría desde el backend
    const res = await fetch(`/admin/reservaciones/categorias/${cat}`);
    const data = await res.json();

    // Mostrar imagen y nombre
    vehImage.src = data.imagen || '/assets/placeholder-car.jpg';
    vehName.textContent = data.nombre || 'Ejemplo de la categoría seleccionada';
    vehImageWrap.style.display = 'block';

    // ✅ Tarifa base real de la categoría
    const tarifa = parseFloat(data.tarifa_base || data.precio_dia || 0);

    // Guardar referencia base y resetear bandera de edición
    tarifaOriginal = tarifa;              // base real del catálogo
    precioSeleccionado = tarifa;          // inicia igual
    tarifaEditadaManualmente = false;     // aún no fue editada

    // Mostrar valor inicial
    $('#baseLine').innerHTML = `$${tarifa.toFixed(2)} MXN/día`;
    updateResumen(tarifa);

  } catch (err) {
    console.error('Error al cargar categoría:', err);
    vehImageWrap.style.display = 'none';
    $('#baseLine').textContent = '—';
    updateResumen(0);
  }
});

/* ================================
   💰 Resumen y totales
================================ */
let precioSeleccionado = 0;
let diasSeleccionados = 1;
let seguroSeleccionado = null;
let adicionalesSeleccionados = [];
let tarifaOriginal = 0;
let tarifaEditadaManualmente = false;

function updateResumen(precioDia = null, dias = null) {
  if (precioDia !== null) precioSeleccionado = precioDia;
  if (dias !== null) diasSeleccionados = dias;
  actualizarTotal();
}

const selectMoneda = $('#moneda');
const tcInput = $('#tc');

function actualizarTotal() {
  const base = precioSeleccionado * diasSeleccionados;
  const proteccion = seguroSeleccionado ? seguroSeleccionado.precio * diasSeleccionados : 0;
  const extras = adicionalesSeleccionados.reduce(
    (sum, a) => sum + (a.precio * a.cantidad * diasSeleccionados), 0
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
   ✏️ Edición inline de tarifa base
================================ */
const editTarifaBtn = $('#editTarifa');
const baseLine = $('#baseLine');

editTarifaBtn?.addEventListener('click', () => {
  if (!baseLine) return;

  // Si ya hay un input activo, no volver a crearlo
  if (baseLine.querySelector('input')) return;

  // Obtener valor actual numérico
  const valorActual = parseFloat(baseLine.textContent.replace(/[^\d.]/g, '')) || precioSeleccionado || 0;

  // Crear input temporal
  const input = document.createElement('input');
  input.type = 'number';
  input.value = valorActual.toFixed(2);
  input.min = 0;
  input.step = 0.01;
  input.style.width = '90px';
  input.style.padding = '4px';
  input.style.border = '1px solid #ccc';
  input.style.borderRadius = '6px';
  input.style.fontWeight = '600';

  // Reemplazar texto por input
  baseLine.textContent = '';
  baseLine.appendChild(input);
  input.focus();

  // Guardar cuando presione Enter o salga del campo
  input.addEventListener('blur', guardarTarifaEditada);
  input.addEventListener('keydown', e => {
    if (e.key === 'Enter') input.blur();
  });

  function guardarTarifaEditada() {
    const nuevoValor = parseFloat(input.value);
    if (isNaN(nuevoValor) || nuevoValor <= 0) {
      alertify.warning('⚠️ Ingresa una tarifa válida.');
      input.focus();
      return;
    }

    tarifaEditadaManualmente = true;
    precioSeleccionado = nuevoValor;
    tarifaOriginal = tarifaOriginal || valorActual;

    // Mostrar valor actualizado
    baseLine.innerHTML = `<span style="color:#ca8a04;font-weight:600;">$${nuevoValor.toFixed(2)} MXN/día*</span>`;
    actualizarTotal();
  }
});

/* ================================
   🔒 Protecciones (Seguros)
================================ */
const btnProtecciones = $('#btnProtecciones');
const proteModal = $('#proteccionPop');
const proteList = $('#proteList');
const proteInput = $('#proteccionSel');
const proteRemove = $('#proteRemove');

proteRemove?.addEventListener('click', () => {
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
      <div class="seg-card" style="border:1px solid #ddd;border-radius:12px;padding:16px;margin-bottom:10px;text-align:center;background:#f8fafc;">
        <div style="font-weight:700;">${s.nombre}</div>
        <div style="font-size:13px;color:#475467;min-height:40px;">${s.descripcion ?? ''}</div>
        <div style="font-weight:600;color:#d00;margin-top:6px;">$${Number(s.precio_por_dia).toFixed(2)} MXN/día</div>
        <button class="btn primary selectProteccion"
                data-id="${s.id_paquete}"
                data-nombre="${esc(s.nombre)}"
                data-precio="${s.precio_por_dia}">Seleccionar</button>
      </div>
    `).join('');
  } catch (err) {
    console.error('Error al cargar seguros:', err);
    proteList.innerHTML = '<div style="color:#d00;padding:10px;">Error al cargar los paquetes.</div>';
  }
});

proteList?.addEventListener('click', e => {
  const btn = e.target.closest('.selectProteccion');
  if (!btn) return;
  seguroSeleccionado = {
    id: btn.dataset.id,
    nombre: btn.dataset.nombre,
    precio: parseFloat(btn.dataset.precio)
  };
  proteInput.value = `${seguroSeleccionado.nombre} - $${seguroSeleccionado.precio}/día`;
  proteRemove.style.display = 'inline-block';
  proteModal.classList.remove('show');
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
   📅 Cálculo de días
================================ */
$('#fecha_inicio')?.addEventListener('change', calcularDias);
$('#fecha_fin')?.addEventListener('change', calcularDias);

function calcularDias() {
  const f1 = new Date($('#fecha_inicio').value);
  const f2 = new Date($('#fecha_fin').value);
  if (!f1 || !f2 || isNaN(f1) || isNaN(f2)) return;
  const diffTime = f2 - f1;
  let diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));
  if (diffDays <= 0) diffDays = 1;
  $('#diasBadge').textContent = `${diffDays} día(s)`;
  updateResumen(null, diffDays);
  actualizarResumenViaje();
}
/* ================================
   🕒 Formato de hora a 12h con AM/PM
================================ */
function formatoHora12h(hora) {
  if (!hora) return '—';
  let [h, m] = hora.split(':');
  h = parseInt(h);
  const sufijo = h >= 12 ? 'PM' : 'AM';
  h = h % 12 || 12;
  return `${h}:${m} ${sufijo}`;
}

/* ================================
   🧭 Mostrar resumen de viaje
================================ */
function actualizarResumenViaje() {
  $('#resSucursalRetiro').textContent = $('#sucursal_retiro').selectedOptions[0]?.text || '—';
  $('#resSucursalEntrega').textContent = $('#sucursal_entrega').selectedOptions[0]?.text || '—';
  $('#resFechaInicio').textContent = $('#fecha_inicio').value || '—';
  $('#resHoraInicio').textContent = formatoHora12h($('#hora_retiro').value);
  $('#resFechaFin').textContent = $('#fecha_fin').value || '—';
 $('#resHoraFin').textContent = formatoHora12h($('#hora_entrega').value);
  $('#resDias').textContent = `${diasSeleccionados} día(s)` || '—';
}

$('#sucursal_retiro')?.addEventListener('change', actualizarResumenViaje);
$('#sucursal_entrega')?.addEventListener('change', actualizarResumenViaje);
$('#hora_retiro')?.addEventListener('change', actualizarResumenViaje);
$('#hora_entrega')?.addEventListener('change', actualizarResumenViaje);

/* ================================
   📤 Envío con fetch + Alertify
================================ */
$('#formReserva')?.addEventListener('submit', async e => {
  e.preventDefault();

  const v = id => $(id)?.value?.trim();
  if (!v('#categoriaSelect') || $('#categoriaSelect').value == 0)
    return alertify.warning('⚠️ Selecciona una categoría de vehículo.');
  if (!v('#sucursal_retiro') || !v('#sucursal_entrega'))
    return alertify.warning('⚠️ Selecciona sucursal de retiro y entrega.');
  if (!v('#fecha_inicio') || !v('#fecha_fin'))
    return alertify.warning('⚠️ Completa las fechas de la reserva.');
  if (!v('#nombre_cliente') || !v('#email_cliente') || !v('#telefono_cliente'))
    return alertify.warning('⚠️ Completa los datos del cliente.');

  const btn = $('#btnReservar');
  btn.disabled = true;
  btn.textContent = 'Procesando...';

 // 📌 Antes de armar el payload
const tarifaModificada = tarifaEditadaManualmente ? precioSeleccionado : null;

const payload = {
  id_categoria: $('#categoriaSelect').value,
  sucursal_retiro: $('#sucursal_retiro').value,
  sucursal_entrega: $('#sucursal_entrega').value,
  fecha_inicio: $('#fecha_inicio').value,
  fecha_fin: $('#fecha_fin').value,
  hora_retiro: $('#hora_retiro')?.value || '',
  hora_entrega: $('#hora_entrega')?.value || '',
  subtotal: $('#subTot').textContent.replace(/[^\d.]/g, '') || '0',
  impuestos: $('#iva').textContent.replace(/[^\d.]/g, '') || '0',
  total: $('#total').textContent.replace(/[^\d.]/g, '') || '0',
  moneda: $('#moneda').value,
  nombre_cliente: $('#nombre_cliente').value,
  email_cliente: $('#email_cliente').value,
  telefono_cliente: $('#telefono_cliente').value,
  no_vuelo: $('#no_vuelo')?.value || '',

  // ✅ bandera de edición
  tarifa_ajustada: tarifaEditadaManualmente ? 1 : 0,

  // ✅ siempre base real (de la categoría)
  precio_base_dia: tarifaOriginal || precioSeleccionado
};

// ✅ solo incluir tarifa_modificada si realmente fue editada
if (tarifaEditadaManualmente) {
  payload.tarifa_modificada = tarifaModificada;
}

// 👇 el resto queda igual
payload.seguroSeleccionado = seguroSeleccionado;
payload.adicionalesSeleccionados = adicionalesSeleccionados;
payload.tarifa_ajustada = tarifaEditadaManualmente ? 1 : 0;


  try {
    const res = await fetch('/reservaciones/guardar', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
      },
      body: JSON.stringify(payload)
    });

    const data = await res.json();
    if (res.ok && data.success) {
  alertify.success('✅ Reservación registrada correctamente.');
  alertify.notify(`Código: <b>${data.codigo}</b>`, 'custom', 8);

  // 🧼 Limpiar formulario y resumen visualmente
  e.target.reset();
  $('#vehImageWrap').style.display = 'none';
  $('#baseLine').textContent = '—';
  updateResumen(0);

  // 🕓 Esperar un segundo y recargar la vista
  setTimeout(() => {
    window.location.href = '/admin/reservaciones';
    // 👆 Cambia 'rutaReservacionesAdmin' por el nombre real de tu ruta
  }, 1000);
} else {
  throw new Error(data.message || 'Error desconocido al guardar.');
}
  } catch (err) {
    console.error(err);
    alertify.error(`❌ No se pudo guardar la reservación: ${err.message}`);
  } finally {
    btn.disabled = false;
    btn.textContent = 'Registrar Reservación';
  }
});
