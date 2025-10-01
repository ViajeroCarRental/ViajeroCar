// ========= Utilidades =========
const money = n => Number(n||0).toLocaleString('es-MX',{style:'currency',currency:'MXN'});
const DMY = s => { const [d,m,y]=s.split('/').map(Number); return new Date(y,m-1,d); };
const endOfDay = d => { const x = new Date(d); x.setHours(23,59,59,999); return x; };
const startOfWeek = d => { const x=new Date(d); const day=(x.getDay()+6)%7; x.setDate(x.getDate()-day); x.setHours(0,0,0,0); return x; };
const startOfMonth = d => { const x=new Date(d); x.setDate(1); x.setHours(0,0,0,0); return x; };

// FLEET: usa global o fallback
const FLEET = window.FLEET || [
  { id:1, name:'Nissan Versa', year:2022, plate:'XYZ-123', rim:15 },
  { id:3, name:'Chevrolet Aveo', year:2023, plate:'JKL-789', rim:15 },
  { id:4, name:'Kia Rio', year:2020, plate:'MNO-321', rim:15 },
  { id:5, name:'Mazda 3', year:2022, plate:'QWE-555', rim:18 },
  { id:6, name:'VW Jetta', year:2019, plate:'JET-201', rim:16 },
  { id:7, name:'Hyundai Accent', year:2021, plate:'ACC-777', rim:15 },
  { id:8, name:'Chevrolet Onix', year:2022, plate:'ONX-222', rim:15 },
  { id:11, name:'Honda City', year:2022, plate:'CTY-432', rim:15 },
  { id:12, name:'Renault Kwid', year:2022, plate:'KWD-120', rim:14 },
  { id:14, name:'Seat Ibiza', year:2022, plate:'IBZ-888', rim:17 },
  { id:15, name:'Chevrolet Spark', year:2018, plate:'SPK-101', rim:14 },
];

// Dataset de gastos (idéntico patrón al original)
const COSTS = [
  {date:'02/08/2025',car:3,type:'policy',desc:'Prima anual Quálitas',amt:9200},
  {date:'05/08/2025',car:1,type:'maint', desc:'Cambio de aceite/filtro',amt:1800},
  {date:'06/08/2025',car:2,type:'maint', desc:'Balatas + rectificado',amt:4200},
  {date:'10/08/2025',car:1,type:'body',  desc:'Rayón defensa',amt:1800},
  {date:'12/08/2025',car:4,type:'maint', desc:'Bomba de combustible',amt:9500},
  {date:'12/08/2025',car:4,type:'claim', desc:'Deducible siniestro',amt:6000},
  {date:'14/08/2025',car:5,type:'policy',desc:'Prima anual AXA',amt:10500},
  {date:'15/08/2025',car:7,type:'maint', desc:'Servicio A',amt:1900},
  {date:'20/08/2025',car:12,type:'body', desc:'Pintura fascia',amt:1500},
  {date:'22/08/2025',car:6,type:'other', desc:'Limpieza profunda',amt:600},
  {date:'01/09/2025',car:1,type:'maint', desc:'Afinación menor',amt:2300},
  {date:'03/09/2025',car:11,type:'policy',desc:'Prima anual AXA',amt:9800},
  {date:'05/09/2025',car:15,type:'maint',desc:'General B',amt:5200},
  {date:'06/09/2025',car:8, type:'other', desc:'Tenencia',amt:1800},
  {date:'06/09/2025',car:14,type:'body',  desc:'Parabrisas',amt:3500},
];

const labels = {maint:'Mantenimiento',policy:'Póliza',body:'Carrocería',claim:'Siniestro',other:'Otros'};

// Elementos
const costBody = document.querySelector('#tblCost tbody');
const fromEl = document.getElementById('from');
const toEl = document.getElementById('to');
const quickToday = document.getElementById('quickToday');
const quickWeek = document.getElementById('quickWeek');
const quickMonth = document.getElementById('quickMonth');

// Rango inicial: Mes actual
function setRange(type){
  const now = new Date();
  if(type==='day'){
    fromEl.valueAsDate = new Date(now.getFullYear(),now.getMonth(),now.getDate());
    toEl.valueAsDate   = endOfDay(now);
  } else if(type==='week'){
    const s = startOfWeek(now), e = new Date(s); e.setDate(s.getDate()+6);
    fromEl.valueAsDate = s; toEl.valueAsDate = endOfDay(e);
  } else { // month
    const s = startOfMonth(now), e = new Date(s.getFullYear(),s.getMonth()+1,0);
    fromEl.valueAsDate = s; toEl.valueAsDate = endOfDay(e);
  }
  paintCosts();
}
setRange('month');
quickToday.onclick = ()=>setRange('day');
quickWeek.onclick  = ()=>setRange('week');
quickMonth.onclick = ()=>setRange('month');
document.getElementById('applyRange').addEventListener('click', paintCosts);

// Pintar tabla + KPIs
function paintCosts(){
  if(!fromEl.value || !toEl.value) return;
  const from = new Date(fromEl.value), to = endOfDay(new Date(toEl.value));

  const rows = COSTS.filter(x => {
    const d = DMY(x.date); return d >= from && d <= to;
  });

  costBody.innerHTML = rows.map(x => {
    const car = FLEET.find(c => c.id === x.car) || {name:'—',year:'',plate:'—',rim:'—'};
    return `<tr>
      <td>${x.date}</td>
      <td>${car.name} ${car.year} (${car.plate})</td>
      <td>R${car.rim}</td>
      <td>${labels[x.type]||x.type}</td>
      <td>${x.desc}</td>
      <td>${money(x.amt)}</td>
    </tr>`;
  }).join('');

  const sum = t => rows.filter(r=>r.type===t).reduce((a,b)=>a+b.amt,0);
  const tot = rows.reduce((a,b)=>a+b.amt,0);

  // Totales
  document.getElementById('gTot').textContent   = money(tot);
  document.getElementById('gMaint').textContent = money(sum('maint'));
  document.getElementById('gPol').textContent   = money(sum('policy'));
  document.getElementById('gBody').textContent  = money(sum('body'));
  document.getElementById('gOther').textContent = money(sum('claim')+sum('other'));

  // Extras: conteo, top vehículo, promedio por día, label de rango
  document.getElementById('gCount').textContent = `${rows.length} movimiento${rows.length!==1?'s':''}`;

  const byCar = {}; rows.forEach(r=>{ byCar[r.car]=(byCar[r.car]||0)+r.amt; });
  let topId=null, topVal=0;
  Object.entries(byCar).forEach(([id,val])=>{ if(val>topVal){topVal=val; topId=+id;} });
  const topCar = topId ? FLEET.find(c=>c.id===topId) : null;
  document.getElementById('topCar').textContent = money(topVal||0);
  document.getElementById('topCarName').textContent = topCar ? `${topCar.name} ${topCar.year} (${topCar.plate}) · R${topCar.rim}` : '—';

  const days = Math.max(1, Math.ceil((to - from)/(24*60*60*1000))+1);
  document.getElementById('avgPerDay').textContent = money(days ? (tot/days) : 0);
  document.getElementById('rangeLabel').textContent = `${from.toLocaleDateString('es-MX')} → ${to.toLocaleDateString('es-MX')} · ${days} día${days!==1?'s':''}`;
}
paintCosts();

// Buscador
document.getElementById('qCost').addEventListener('input', e => {
  const t=e.target.value.toLowerCase();
  [...costBody.rows].forEach(r => r.style.display = r.innerText.toLowerCase().includes(t) ? '' : 'none');
});

// Export CSV (helper local)
function exportCSV(tableId, filename){
  const rows=[...document.querySelectorAll(`#${tableId} tr`)]
    .map(tr=>[...tr.children].map(td=>`"${td.innerText.replace(/"/g,'""')}"`).join(',')).join('\n');
  const blob=new Blob([rows],{type:'text/csv;charset=utf-8;'}); const a=document.createElement('a');
  a.href=URL.createObjectURL(blob); a.download=filename; a.click(); URL.revokeObjectURL(a.href);
}
document.getElementById('exportCost').addEventListener('click', ()=>exportCSV('tblCost','gastos.csv'));
