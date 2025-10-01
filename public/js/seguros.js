// ========= Utilidades básicas =========
const money = n => Number(n||0).toLocaleString('es-MX',{style:'currency',currency:'MXN'});

// Si ya existe FLEET global, úsalo; si no, define fallback mínimo
const FLEET = window.FLEET || [
  { id:1, name:'Nissan Versa', year:2022, plate:'XYZ-123', rim:15 },
  { id:2, name:'Kia Rio', year:2020, plate:'MNO-321', rim:15 },
];

// Dataset de siniestros (puedes reemplazar por fetch/axios a tu API)
const CLAIMS = [
  { folio:'SIN-2025-014', car:1, date:'05/08/2025', type:'Colisión ligera', status:'En trámite', deductible:6000 },
  { folio:'SIN-2025-011', car:2, date:'12/06/2025', type:'Robo de espejos', status:'Pagado',      deductible:2500 },
];

// Mapeo de estatus → clase de chip
const statusClass = s => {
  const t = (s||'').toLowerCase();
  if (t.includes('pagad')) return 'ok';
  if (t.includes('trámite')) return 'warn';
  if (t.includes('rechaz') || t.includes('pendiente')) return 'bad';
  return 'off';
};

// Pintar tabla
const tbody = document.querySelector('#tblClaims tbody');
function paintClaims() {
  tbody.innerHTML = CLAIMS.map(x => {
    const car = FLEET.find(c => c.id === x.car) || {name:'—',year:'',plate:'—',rim:'—'};
    return `<tr>
      <td>${x.folio}</td>
      <td>${car.name} ${car.year} (${car.plate})</td>
      <td>${x.date}</td>
      <td>${x.type}</td>
      <td><span class="status ${statusClass(x.status)}">${x.status}</span></td>
      <td>${money(x.deductible)}</td>
      <td>R${car.rim}</td>
    </tr>`;
  }).join('');
}
paintClaims();

// Buscador
document.getElementById('qClaims').addEventListener('input', e => {
  const t = e.target.value.toLowerCase();
  [...tbody.rows].forEach(r => {
    r.style.display = r.innerText.toLowerCase().includes(t) ? '' : 'none';
  });
});

// Exportar CSV (helper local)
function exportCSV(tableId, filename){
  const rows=[...document.querySelectorAll(`#${tableId} tr`)]
    .map(tr=>[...tr.children].map(td=>`"${td.innerText.replace(/"/g,'""')}"`).join(',')).join('\n');
  const blob=new Blob([rows],{type:'text/csv;charset=utf-8;'}); const a=document.createElement('a');
  a.href=URL.createObjectURL(blob); a.download=filename; a.click(); URL.revokeObjectURL(a.href);
}
document.getElementById('exportClaims').addEventListener('click', ()=>exportCSV('tblClaims','seguros.csv'));
