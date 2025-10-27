/* ==========================================================
   ⚙️ CONTRATO.JS — Flujo unificado (pasos 1, 2 y 3)
   Autor: Ingeniero Bernal
========================================================== */

// ==========================
// 🧩 UTILIDADES
// ==========================
const $ = (s) => document.querySelector(s);
const $$ = (s) => Array.from(document.querySelectorAll(s));
const Fmx = (n, c = 'MXN') =>
  `${c === 'USD' ? '$' : '$'}${(n || 0).toFixed(2)} ${c}`;

// ==========================
// 🧠 ESTADO GLOBAL TEMPORAL
// ==========================
let contratoData = JSON.parse(localStorage.getItem('vc_contrato_en_proceso') || '{}');

function syncLocalStorage() {
  localStorage.setItem('vc_contrato_en_proceso', JSON.stringify(contratoData));
}

// ==========================
// 🧭 NAVEGACIÓN ENTRE PASOS
// ==========================
function showStep(n) {
  $$('[data-step]').forEach((el) => {
    el.style.display = Number(el.dataset.step) === n ? 'block' : 'none';
  });
  contratoData.currentStep = n;
  syncLocalStorage();
}

$('#go3')?.addEventListener('click', () => showStep(3));
$('#back1')?.addEventListener('click', () => showStep(1));
$('#back2')?.addEventListener('click', () => showStep(2));
showStep(contratoData.currentStep || 1);

// ==========================
// 🧾 PASO 1 — Datos de Reservación
// ==========================
function paso1() {
  const form = $('#formContratoPaso1');
  if (!form) return;

  form.addEventListener('submit', (e) => {
    e.preventDefault();
    const data = Object.fromEntries(new FormData(form).entries());
    contratoData = { ...contratoData, ...data };
    contratoData.base_total = parseFloat($('.total').textContent.replace(/[^0-9.]/g, '')) || 0;
    syncLocalStorage();
    showStep(2);
  });
}

// ==========================
// ⚙️ PASO 2 — Servicios Adicionales
// ==========================
function paso2() {
  const grid = $('#serviciosGrid');
  if (!grid) return;

  // Los servicios vienen renderizados desde Blade
  // Detectar los clics de selección
  grid.addEventListener('change', (e) => {
    const el = e.target.closest('[data-id]');
    if (!el) return;

    const id = Number(el.dataset.id);
    const nombre = el.dataset.nombre;
    const precio = Number(el.dataset.precio);

    if (!contratoData.servicios) contratoData.servicios = [];

    const idx = contratoData.servicios.findIndex((s) => s.id_servicio === id);
    if (idx >= 0) contratoData.servicios.splice(idx, 1);
    else contratoData.servicios.push({ id_servicio: id, nombre, precio });

    contratoData.total_servicios = contratoData.servicios.reduce((a, s) => a + s.precio, 0);
    syncLocalStorage();
    updateResumen();
  });
}

// ==========================
// 🛡️ PASO 3 — Protecciones
// ==========================
function paso3() {
  const grid = $('#packGrid');
  if (!grid) return;

  grid.addEventListener('change', (e) => {
    const el = e.target.closest('.seguro-item');
    if (!el) return;

    const radio = el.querySelector('input[type="radio"]');
    const id = Number(radio.value);
    const nombre = el.querySelector('h4')?.textContent.trim();
    const precio = Number(radio.dataset.precio);

    contratoData.seguros = [{ id_paquete: id, nombre, precio }];
    contratoData.total_seguros = precio;

    syncLocalStorage();
    updateResumen();
  });
}

// ==========================
// 💵 CONVERSIÓN MXN ↔ USD
// ==========================
function setMoneda(moneda, tipoCambio = 1) {
  contratoData.moneda = moneda;
  contratoData.tipoCambio = tipoCambio || 1;
  syncLocalStorage();
  updateResumen();
}

function convertirMoneda(monto) {
  const { moneda = 'MXN', tipoCambio = 1 } = contratoData;
  return moneda === 'USD' ? monto / tipoCambio : monto;
}

// ==========================
// 📊 RESUMEN DINÁMICO
// ==========================
function updateResumen() {
  const base = contratoData.base_total || 0;
  const serv = contratoData.total_servicios || 0;
  const seg = contratoData.total_seguros || 0;
  const subtotal = base + serv + seg;
  const iva = subtotal * 0.16;
  const total = subtotal + iva;

  const moneda = contratoData.moneda || 'MXN';
  const cambio = contratoData.tipoCambio || 1;

  // Actualizar totales
  $('#total_servicios')?.textContent = Fmx(convertirMoneda(serv), moneda);
  $('#total_seguros')?.textContent = Fmx(convertirMoneda(seg), moneda);
  $('.totalBox .total')?.textContent = Fmx(convertirMoneda(total), moneda);

  // Mostrar servicios y seguros seleccionados
  const vehiculoInfo = $('#vehiculo_info');
  if (vehiculoInfo) {
    const serviciosTxt =
      contratoData.servicios?.map((s) => `• ${s.nombre} (${Fmx(convertirMoneda(s.precio), moneda)})`).join('<br>') ||
      '—';
    const segurosTxt =
      contratoData.seguros?.map((s) => `• ${s.nombre} (${Fmx(convertirMoneda(s.precio), moneda)})`).join('<br>') ||
      '—';

    vehiculoInfo.innerHTML = `
      <p><b>${contratoData.cliente?.nombre || 'Cliente'}</b></p>
      <p><b>Servicios:</b><br>${serviciosTxt}</p>
      <p><b>Seguros:</b><br>${segurosTxt}</p>
      <hr>
      <p><b>Subtotal:</b> ${Fmx(convertirMoneda(subtotal), moneda)}</p>
      <p><b>IVA (16%):</b> ${Fmx(convertirMoneda(iva), moneda)}</p>
      <p><b>Total:</b> <span class="total">${Fmx(convertirMoneda(total), moneda)}</span></p>
      <p style="font-size:12px;color:#666">Moneda actual: ${moneda}</p>
    `;
  }
}

// ==========================
// 🔁 RESTAURAR DATOS (si existen)
// ==========================
function restoreContrato() {
  if (!Object.keys(contratoData).length) return;

  // Paso 1
  $('#id_reservacion')?.value = contratoData.id_reservacion || '';
  $('#nombre_cliente')?.value = contratoData.nombre_cliente || '';
  $('#email_cliente')?.value = contratoData.email_cliente || '';
  $('#telefono_cliente')?.value = contratoData.telefono_cliente || '';
  $('#fecha_inicio')?.value = contratoData.fecha_inicio || '';
  $('#fecha_fin')?.value = contratoData.fecha_fin || '';
  $('#hora_retiro')?.value = contratoData.hora_retiro || '';
  $('#hora_entrega')?.value = contratoData.hora_entrega || '';

  // Paso 2: marcar servicios seleccionados
  (contratoData.servicios || []).forEach((s) => {
    const el = $(`[data-id="${s.id_servicio}"] input[type="checkbox"]`);
    if (el) el.checked = true;
  });

  // Paso 3: marcar seguro seleccionado
  if (contratoData.seguros?.length) {
    const idSel = contratoData.seguros[0].id_paquete;
    const el = $(`input[name="id_paquete"][value="${idSel}"]`);
    if (el) el.checked = true;
  }

  updateResumen();
}

// ==========================
// 🚀 INICIALIZACIÓN
// ==========================
document.addEventListener('DOMContentLoaded', () => {
  paso1();
  paso2();
  paso3();
  restoreContrato();
  updateResumen();

  // 💱 Ejemplo: selector de moneda (se agregará en pasos posteriores)
  // Simulación temporal:
  window.setMoneda = setMoneda; // Permite usarlo desde consola para probar
});
