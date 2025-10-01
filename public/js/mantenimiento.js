/* ===== Sesión / navegación ===== */
guardModule('autos');
const user = getUser();
document.getElementById('hello').textContent = `Hola, ${user?.name || ''}`;
document.getElementById('logoutBtn').addEventListener('click', ()=>{ localStorage.removeItem('vc_user'); location.href='../index.html'; });

const sb=document.getElementById('sidebar'), burger=document.getElementById('burger'), scrim=document.getElementById('scrim');
burger?.addEventListener('click', ()=>{ sb.classList.add('open'); scrim.classList.add('show'); });
scrim?.addEventListener('click', ()=>{ sb.classList.remove('open'); scrim.classList.remove('show'); });

const links=document.querySelectorAll('.sb-link[data-screen]');
const screens=Object.fromEntries(['flotilla','mantenimiento','polizas','carroceria','seguros','gastos'].map(id=>[id,document.getElementById('screen-'+id)]));
const show=id=>{ Object.values(screens).forEach(s=>s.classList.remove('show')); screens[id].classList.add('show'); links.forEach(a=>a.classList.toggle('active',a.dataset.screen===id)); sb.classList.remove('open'); scrim.classList.remove('show'); };
links.forEach(a=>a.addEventListener('click',e=>{ e.preventDefault(); show(a.dataset.screen); }));
show('flotilla');

/* ===== Utilidades ===== */
const DMY=s=>{const [d,m,y]=s.split('/').map(Number); return new Date(y,m-1,d);};
const daysUntil=s=>Math.floor((DMY(s)-new Date())/(24*60*60*1000));
const money=n=>n.toLocaleString('es-MX',{style:'currency',currency:'MXN'});
const fmtKm=n=>n.toLocaleString('es-MX')+' km';
const oilIntervalDefault=10000, rotIntervalDefault=8000;

/* ===== Dataset (16 autos) con mantenimiento por KM + RIN =====
   mant: oilLastKm, oilIntervalKm, rotLastKm, rotIntervalKm, pads, notes
   img: ruta relativa (guárdala en assets/media/cars/) */
const FLEET_BASE = [
  { id:1,  name:'Nissan Versa',       year:2022, plate:'XYZ-123', service:'Diario',    km:45000, status:'Disponible',        in:'12/08/2025', out:'25/08/2025',
    vin:'3N1CN7AD2LK123456', color:'Rojo', trans:'Automática', fuel:'Gasolina', class:'Sedán', seats:5, doors:4, rim:15,
    img:'../assets/media/image.png', location:'QRO · Centro',
    policy:{ num:'POL-99231', insurer:'AXA', cov:'Amplia', start:'10/09/2024', end:'09/09/2025' },
    maint:{ oilLastKm:36000, oilIntervalKm:10000, rotLastKm:37000, rotIntervalKm:8000, pads:'Buenas', notes:'' },
    verify:{ last:'01/03/2025', next:'01/09/2025' }, notes:'Unidad limpia, sin golpes. Llantas 70%.' },
  { id:2,  name:'Toyota Corolla',     year:2021, plate:'ABC-456', service:'Semanal',   km:65000, status:'En mantenimiento',  in:'10/07/2025', out:'15/07/2025',
    vin:'JTDEPRAE7MJ456789', color:'Blanco', trans:'CVT', fuel:'Gasolina', class:'Sedán', seats:5, doors:4, rim:16,
    img:'../assets/media/2.jpg', location:'QRO · Aeropuerto',
    policy:{ num:'POL-87210', insurer:'GNP', cov:'Limitada', start:'01/02/2025', end:'31/01/2026' },
    maint:{ oilLastKm:56000, oilIntervalKm:10000, rotLastKm:60000, rotIntervalKm:8000, pads:'50%', notes:'Balatas por revisar' },
    verify:{ last:'01/04/2025', next:'01/10/2025' }, notes:'Cambio de balatas pendiente.' },
  { id:3,  name:'Chevrolet Aveo',     year:2023, plate:'JKL-789', service:'Mensual',   km:22500, status:'Rentado',           in:'01/08/2025', out:'20/08/2025',
    vin:'3G1BC6SM3KS345678', color:'Gris', trans:'Manual', fuel:'Gasolina', class:'Sedán', seats:5, doors:4, rim:15,
    img:'../assets/media/cars/aveo.jpg', location:'CDMX · Norte',
    policy:{ num:'POL-77881', insurer:'Quálitas', cov:'Amplia', start:'01/08/2024', end:'31/07/2025' }, /* vencida */
    maint:{ oilLastKm:16000, oilIntervalKm:10000, rotLastKm:17000, rotIntervalKm:8000, pads:'Buenas', notes:'' },
    verify:{ last:'01/02/2025', next:'01/08/2025' }, notes:'Rayón leve en defensa.' },
  { id:4,  name:'Kia Rio',            year:2020, plate:'MNO-321', service:'Ejecutivo', km:80000, status:'Fuera de servicio',  in:'05/06/2025', out:'10/06/2025',
    vin:'KNADB4A38F612345', color:'Negro', trans:'Automática', fuel:'Gasolina', class:'Sedán', seats:5, doors:4, rim:15,
    img:'../assets/media/cars/rio.jpg', location:'GDL · Minerva',
    policy:{ num:'POL-55102', insurer:'Quálitas', cov:'RC', start:'01/03/2024', end:'28/02/2025' }, /* vencida */
    maint:{ oilLastKm:71000, oilIntervalKm:10000, rotLastKm:72000, rotIntervalKm:8000, pads:'Gastadas', notes:'Bomba combustible reemplazo' },
    verify:{ last:'01/01/2025', next:'01/07/2025' }, notes:'En reparación.' },
  { id:5,  name:'Mazda 3',            year:2022, plate:'QWE-555', service:'Diario',    km:31000, status:'Disponible',
    vin:'JM1BP2MM3L1239999', color:'Rojo', trans:'Automática', fuel:'Gasolina', class:'Hatchback', seats:5, doors:5, rim:18,
    img:'../assets/media/cars/mazda3.jpg', location:'QRO · Centro',
    policy:{ num:'POL-12345', insurer:'AXA', cov:'Amplia', start:'05/10/2024', end:'04/10/2025' },
    maint:{ oilLastKm:22000, oilIntervalKm:10000, rotLastKm:25000, rotIntervalKm:8000, pads:'Buenas', notes:'' },
    verify:{ last:'01/04/2025', next:'01/10/2025' }, notes:'Película de seguridad.' },
  { id:6,  name:'VW Jetta',           year:2019, plate:'JET-201', service:'Semanal',   km:92000, status:'Disponible',
    vin:'3VW2B7AJ9KM123456', color:'Plata', trans:'Automática', fuel:'Gasolina', class:'Sedán', seats:5, doors:4, rim:16,
    img:'../assets/media/cars/jetta.jpg', location:'CDMX · Norte',
    policy:{ num:'POL-99887', insurer:'GNP', cov:'Limitada', start:'15/03/2025', end:'14/03/2026' },
    maint:{ oilLastKm:83000, oilIntervalKm:10000, rotLastKm:86000, rotIntervalKm:8000, pads:'50%', notes:'' },
    verify:{ last:'01/05/2025', next:'01/11/2025' }, notes:'Antiguo pero confiable.' },
  { id:7,  name:'Hyundai Accent',     year:2021, plate:'ACC-777', service:'Mensual',   km:54000, status:'Disponible',
    vin:'KMHCT4AE1EU123456', color:'Blanco', trans:'Automática', fuel:'Gasolina', class:'Sedán', seats:5, doors:4, rim:15,
    img:'../assets/media/cars/accent.jpg', location:'QRO · Aeropuerto',
    policy:{ num:'POL-45678', insurer:'Quálitas', cov:'Amplia', start:'20/09/2024', end:'19/09/2025' },
    maint:{ oilLastKm:45000, oilIntervalKm:10000, rotLastKm:47000, rotIntervalKm:8000, pads:'Buenas', notes:'' },
    verify:{ last:'01/03/2025', next:'01/09/2025' }, notes:'Buen rendimiento.' },
  { id:8,  name:'Chevrolet Onix',     year:2022, plate:'ONX-222', service:'Diario',    km:27000, status:'Disponible',
    vin:'9BGKS48P0CG123456', color:'Azul', trans:'Automática', fuel:'Gasolina', class:'Sedán', seats:5, doors:4, rim:15,
    img:'../assets/media/cars/onix.jpg', location:'GDL · Minerva',
    policy:{ num:'POL-66778', insurer:'AXA', cov:'Amplia', start:'01/11/2024', end:'31/10/2025' },
    maint:{ oilLastKm:19000, oilIntervalKm:10000, rotLastKm:21000, rotIntervalKm:8000, pads:'Buenas', notes:'' },
    verify:{ last:'01/05/2025', next:'01/11/2025' }, notes:'TPMS sensible.' },
  { id:9,  name:'Nissan Sentra',      year:2020, plate:'SEN-909', service:'Semanal',   km:74000, status:'Rentado',
    vin:'3N1AB7AP3KY123456', color:'Gris', trans:'CVT', fuel:'Gasolina', class:'Sedán', seats:5, doors:4, rim:16,
    img:'../assets/media/cars/sentra.jpg', location:'CDMX · Norte',
    policy:{ num:'POL-33322', insurer:'GNP', cov:'Amplia', start:'01/04/2025', end:'31/03/2026' },
    maint:{ oilLastKm:65000, oilIntervalKm:10000, rotLastKm:69000, rotIntervalKm:8000, pads:'50%', notes:'' },
    verify:{ last:'01/03/2025', next:'01/09/2025' }, notes:'Cliente corporativo.' },
  { id:10, name:'Kia Forte',          year:2023, plate:'FOR-808', service:'Ejecutivo', km:18000, status:'Disponible',
    vin:'3KPF34AD3JE123456', color:'Rojo', trans:'Automática', fuel:'Gasolina', class:'Sedán', seats:5, doors:4, rim:17,
    img:'../assets/media/cars/forte.jpg', location:'QRO · Centro',
    policy:{ num:'POL-90909', insurer:'AXA', cov:'Amplia', start:'10/12/2024', end:'09/12/2025' },
    maint:{ oilLastKm:10000, oilIntervalKm:10000, rotLastKm:11000, rotIntervalKm:8000, pads:'Buenas', notes:'' },
    verify:{ last:'01/06/2025', next:'01/12/2025' }, notes:'Premium ejecutivos.' },
  { id:11, name:'Honda City',         year:2022, plate:'CTY-432', service:'Diario',    km:36000, status:'Disponible',
    vin:'MRHGM2640NT123456', color:'Gris', trans:'CVT', fuel:'Gasolina', class:'Sedán', seats:5, doors:4, rim:15,
    img:'../assets/media/cars/city.jpg', location:'QRO · Centro',
    policy:{ num:'POL-11111', insurer:'AXA', cov:'Amplia', start:'01/09/2024', end:'31/08/2025' },
    maint:{ oilLastKm:27000, oilIntervalKm:10000, rotLastKm:30000, rotIntervalKm:8000, pads:'Buenas', notes:'' },
    verify:{ last:'01/03/2025', next:'01/09/2025' }, notes:'Muy buen consumo.' },
  { id:12, name:'Renault Kwid',       year:2022, plate:'KWD-120', service:'Mensual',   km:29000, status:'Disponible',
    vin:'MA1XXXXX202212345', color:'Naranja', trans:'Manual', fuel:'Gasolina', class:'Hatchback', seats:5, doors:5, rim:14,
    img:'../assets/media/cars/kwid.jpg', location:'CDMX · Norte',
    policy:{ num:'POL-44444', insurer:'Quálitas', cov:'Limitada', start:'22/07/2024', end:'21/07/2025' }, /* vencida */
    maint:{ oilLastKm:19000, oilIntervalKm:10000, rotLastKm:21000, rotIntervalKm:8000, pads:'Gastadas', notes:'Ruido tablero' },
    verify:{ last:'22/01/2025', next:'22/07/2025' }, notes:'Revisar ruido.' },
  { id:13, name:'Toyota Yaris',       year:2023, plate:'YRS-730', service:'Diario',    km:20000, status:'Disponible',
    vin:'MHKXXXXXXXX123456', color:'Blanco', trans:'CVT', fuel:'Gasolina', class:'Sedán', seats:5, doors:4, rim:15,
    img:'../assets/media/cars/yaris.jpg', location:'GDL · Minerva',
    policy:{ num:'POL-56565', insurer:'GNP', cov:'Amplia', start:'10/10/2024', end:'09/10/2025' },
    maint:{ oilLastKm:12000, oilIntervalKm:10000, rotLastKm:14000, rotIntervalKm:8000, pads:'Buenas', notes:'' },
    verify:{ last:'10/04/2025', next:'10/10/2025' }, notes:'Nuevo ingreso.' },
  { id:14, name:'Seat Ibiza',         year:2022, plate:'IBZ-888', service:'Semanal',   km:33000, status:'Disponible',
    vin:'VSSZZZ6JZNR123456', color:'Rojo', trans:'Manual', fuel:'Gasolina', class:'Hatchback', seats:5, doors:5, rim:17,
    img:'../assets/media/cars/ibiza.jpg', location:'QRO · Centro',
    policy:{ num:'POL-78787', insurer:'AXA', cov:'Limitada', start:'02/06/2024', end:'01/06/2025' }, /* vencida */
    maint:{ oilLastKm:23000, oilIntervalKm:10000, rotLastKm:25000, rotIntervalKm:8000, pads:'Requieren cambio', notes:'Parabrisas pendiente' },
    verify:{ last:'02/03/2025', next:'02/09/2025' }, notes:'Parabrisas pendiente.' },
  { id:15, name:'Chevrolet Spark',    year:2018, plate:'SPK-101', service:'Diario',    km:110000, status:'Disponible',
    vin:'3G1MJ7E34JS123456', color:'Blanco', trans:'Manual', fuel:'Gasolina', class:'Hatchback', seats:5, doors:5, rim:14,
    img:'../assets/media/cars/spark.jpg', location:'CDMX · Norte',
    policy:{ num:'POL-22222', insurer:'GNP', cov:'RC', start:'01/02/2025', end:'31/01/2026' },
    maint:{ oilLastKm:101000, oilIntervalKm:10000, rotLastKm:103000, rotIntervalKm:8000, pads:'50%', notes:'' },
    verify:{ last:'01/03/2025', next:'01/09/2025' }, notes:'Alto kilometraje.' },
  { id:16, name:'VW Vento',           year:2020, plate:'VNT-550', service:'Mensual',   km:68000, status:'Disponible',
    vin:'MA3XXXXX0LT123456', color:'Azul', trans:'Automática', fuel:'Gasolina', class:'Sedán', seats:5, doors:4, rim:15,
    img:'../assets/media/cars/vento.jpg', location:'GDL · Minerva',
    policy:{ num:'POL-67676', insurer:'Quálitas', cov:'Amplia', start:'05/05/2025', end:'04/05/2026' },
    maint:{ oilLastKm:60000, oilIntervalKm:10000, rotLastKm:61000, rotIntervalKm:8000, pads:'Buenas', notes:'A/C revisar' },
    verify:{ last:'05/04/2025', next:'05/10/2025' }, notes:'Detalle aire acondicionado.' },
];

/* ===== Persistencia simple: overrides en localStorage ===== */
const LS_KEY='vc_fleet_overrides';
function loadOverrides(){ try{ return JSON.parse(localStorage.getItem(LS_KEY)||'{}'); }catch{ return {}; } }
function saveOverrides(map){ localStorage.setItem(LS_KEY, JSON.stringify(map)); }
function mergeFleet(){
  const ov=loadOverrides();
  return FLEET_BASE.map(c=> (ov[c.id]? {...c, ...ov[c.id], maint:{...c.maint, ...(ov[c.id].maint||{})} } : c));
}
let FLEET = mergeFleet();

/* ===== Notificaciones ===== */
const btnBell=document.getElementById('btnBell'), bellCount=document.getElementById('bellCount'), notifPanel=document.getElementById('notifPanel'), notifList=document.getElementById('notifList');
function buildAlerts(){
  const alerts=[];
  FLEET.forEach(c=>{
    const dp=daysUntil(c.policy.end);
    if(dp<0) alerts.push({sev:'urgent',tag:'red',msg:`Póliza VENCIDA · ${c.name} ${c.year} (${c.plate})`,date:c.policy.end});
    else if(dp<=30) alerts.push({sev:'warn',tag:'orange',msg:`Póliza por vencer (${dp} días) · ${c.name} (${c.plate})`,date:c.policy.end});
    const oilLeft = (c.maint.oilLastKm + (c.maint.oilIntervalKm||oilIntervalDefault)) - c.km;
    if(oilLeft<=0) alerts.push({sev:'urgent',tag:'red',msg:`Aceite vencido · ${c.name} (${c.plate})`,date:''});
    else if(oilLeft<=500) alerts.push({sev:'warn',tag:'orange',msg:`Aceite por vencer (${oilLeft.toLocaleString()} km) · ${c.name} (${c.plate})`,date:''});
    const rotLeft = (c.maint.rotLastKm + (c.maint.rotIntervalKm||rotIntervalDefault)) - c.km;
    if(rotLeft<=0) alerts.push({sev:'warn',tag:'orange',msg:`Rotación de llantas pendiente · ${c.name} (${c.plate})`,date:''});
    if(c.maint.pads==='Requieren cambio' || c.maint.pads==='Gastadas') alerts.push({sev:'urgent',tag:'red',msg:`Balatas ${c.maint.pads} · ${c.name} (${c.plate})`,date:''});
    const dv=daysUntil(c.verify.next);
    if(dv<0) alerts.push({sev:'urgent',tag:'red',msg:`Verificación VENCIDA · ${c.name} (${c.plate})`,date:c.verify.next});
    else if(dv<=15) alerts.push({sev:'warn',tag:'orange',msg:`Verificación próxima (${dv} días) · ${c.name} (${c.plate})`,date:c.verify.next});
  });
  const order={urgent:0,warn:1,info:2}; alerts.sort((a,b)=>order[a.sev]-order[b.sev]);
  return alerts;
}
function renderAlerts(){
  const alerts=buildAlerts(); bellCount.textContent=alerts.length;
  notifList.innerHTML = alerts.length ? alerts.map(a=>`<div class="item"><span class="tag ${a.tag}">${a.sev==='urgent'?'Urgente':a.sev==='warn'?'Próximo':'Info'}</span><div><div>${a.msg}</div><small class="muted">${a.date||''}</small></div></div>`).join('') : `<div class="item"><div>No hay alertas.</div></div>`;
}
renderAlerts();
btnBell.addEventListener('click',()=>notifPanel.classList.toggle('show'));
document.addEventListener('click',e=>{ if(!btnBell.contains(e.target) && !notifPanel.contains(e.target)) notifPanel.classList.remove('show'); });

/* ====== Flotilla ====== */
const fleetBody=document.querySelector('#tblFleet tbody');
const statusChip=txt=>{ const map={Disponible:'ok',Rentado:'bad','En mantenimiento':'warn','Fuera de servicio':'off'}; return `<span class="status ${map[txt]||'off'}">● ${txt}</span>`; };
function paintFleet(rows){ fleetBody.innerHTML=rows.map(c=>`
  <tr data-id="${c.id}"><td>${c.name} ${c.year}</td><td>${c.plate}</td><td>${c.service}</td><td>R${c.rim}</td><td>${fmtKm(c.km)}</td><td>${statusChip(c.status)}</td><td>${c.in||'-'}</td><td>${c.out||'-'}</td></tr>
`).join(''); }
paintFleet(FLEET);
fleetBody.addEventListener('click',e=>{ const tr=e.target.closest('tr'); if(!tr) return; openModal(FLEET.find(x=>x.id==tr.dataset.id)); });
document.getElementById('qFleet').addEventListener('input',e=>{ const t=e.target.value.toLowerCase(); paintFleet(FLEET.filter(c=>`${c.name} ${c.year} ${c.plate} ${c.status} R${c.rim}`.toLowerCase().includes(t))); });
document.getElementById('exportFleet').addEventListener('click',()=>exportCSV('tblFleet','flotilla.csv'));

/* ====== Pólizas ====== */
const polBody=document.querySelector('#tblPol tbody');
function polState(c){ const d=daysUntil(c.policy.end); if(d<0) return ['Vencida','red']; if(d<=30) return [`Por vencer (${d} días)`,'orange']; return ['Vigente','green']; }
function paintPol(){ polBody.innerHTML=FLEET.map(c=>{ const [label,cls]=polState(c); return `<tr><td>${c.name} ${c.year} (${c.plate})</td><td>${c.policy.num}</td><td>${c.policy.insurer}</td><td>${c.policy.cov}</td><td>${c.policy.start}</td><td>${c.policy.end}</td><td><span class="tag ${cls==='red'?'red':cls==='orange'?'orange':''}">${label}</span></td><td>R${c.rim}</td></tr>`; }).join(''); }
paintPol();
document.getElementById('qPol').addEventListener('input',e=>{ const t=e.target.value.toLowerCase(); [...polBody.rows].forEach(r=>r.style.display=r.innerText.toLowerCase().includes(t)?'':'none'); });
document.getElementById('exportPol').addEventListener('click',()=>exportCSV('tblPol','polizas.csv'));

/* ====== Carrocería (dataset demo) ====== */
const BODY=[{folio:'CAR-001',car:1,date:'18/08/2025',zone:'Defensa trasera',damage:'Rayón',sev:'Leve',shop:'Taller Gómez',estimate:1800,status:'En proceso'},
{folio:'CAR-002',car:3,date:'02/08/2025',zone:'Puerta izquierda',damage:'Abolladura',sev:'Media',shop:'Pinturas Leo',estimate:5200,status:'Cotizado'},
{folio:'CAR-003',car:4,date:'12/06/2025',zone:'Cofre',damage:'Golpe',sev:'Alta',shop:'Autobody Pro',estimate:9800,status:'Refacciones'},
{folio:'CAR-004',car:12,date:'15/07/2025',zone:'Fascia delantera',damage:'Raspones',sev:'Leve',shop:'Pinturas Leo',estimate:1500,status:'Terminado'},
{folio:'CAR-005',car:14,date:'28/05/2025',zone:'Parabrisas',damage:'Estrellado',sev:'Alta',shop:'Cristales Max',estimate:3500,status:'Pendiente autorización'}];
const bodyBody=document.querySelector('#tblBody tbody');
function paintBody(){ bodyBody.innerHTML=BODY.map(b=>{ const car=FLEET.find(c=>c.id===b.car); return `<tr><td>${b.folio}</td><td>${car.name} ${car.year} (${car.plate})</td><td>${b.date}</td><td>${b.zone}</td><td>${b.damage}</td><td>${b.sev}</td><td>${b.shop}</td><td>${money(b.estimate)}</td><td>${b.status}</td><td>R${car.rim}</td></tr>`; }).join(''); }
paintBody();
document.getElementById('qBody').addEventListener('input',e=>{ const t=e.target.value.toLowerCase(); [...bodyBody.rows].forEach(r=>r.style.display=r.innerText.toLowerCase().includes(t)?'':'none'); });
document.getElementById('exportBody').addEventListener('click',()=>exportCSV('tblBody','carroceria.csv'));

/* ====== Gastos (demo simple, igual que antes) ====== */
const COSTS=[{date:'02/08/2025',car:3,type:'policy',desc:'Prima anual Quálitas',amt:9200},{date:'05/08/2025',car:1,type:'maint',desc:'Cambio de aceite/filtro',amt:1800},{date:'06/08/2025',car:2,type:'maint',desc:'Balatas + rectificado',amt:4200},{date:'10/08/2025',car:1,type:'body',desc:'Rayón defensa',amt:1800},{date:'12/08/2025',car:4,type:'maint',desc:'Bomba de combustible',amt:9500},{date:'12/08/2025',car:4,type:'claim',desc:'Deducible siniestro',amt:6000},{date:'14/08/2025',car:5,type:'policy',desc:'Prima anual AXA',amt:10500},{date:'15/08/2025',car:7,type:'maint',desc:'Servicio A',amt:1900},{date:'20/08/2025',car:12,type:'body',desc:'Pintura fascia',amt:1500},{date:'22/08/2025',car:6,type:'other',desc:'Limpieza profunda',amt:600},{date:'01/09/2025',car:1,type:'maint',desc:'Afinación menor',amt:2300},{date:'03/09/2025',car:11,type:'policy',desc:'Prima anual AXA',amt:9800},{date:'05/09/2025',car:15,type:'maint',desc:'General B',amt:5200},{date:'06/09/2025',car:8,type:'other',desc:'Tenencia',amt:1800},{date:'06/09/2025',car:14,type:'body',desc:'Parabrisas',amt:3500}];
const costBody=document.querySelector('#tblCost tbody');
const fromEl=document.getElementById('from'), toEl=document.getElementById('to'), quickToday=document.getElementById('quickToday'), quickWeek=document.getElementById('quickWeek'), quickMonth=document.getElementById('quickMonth');
const startOfWeek=d=>{const x=new Date(d);const day=(x.getDay()+6)%7;x.setDate(x.getDate()-day);x.setHours(0,0,0,0);return x;};
const startOfMonth=d=>{const x=new Date(d);x.setDate(1);x.setHours(0,0,0,0);return x;}; const endOfDay=d=>{const x=new Date(d);x.setHours(23,59,59,999);return x;};
function setRange(type){const now=new Date();if(type==='day'){fromEl.valueAsDate=new Date(now.getFullYear(),now.getMonth(),now.getDate());toEl.valueAsDate=endOfDay(now);}if(type==='week'){const s=startOfWeek(now);const e=new Date(s);e.setDate(s.getDate()+6);fromEl.valueAsDate=s;toEl.valueAsDate=endOfDay(e);}if(type==='month'){const s=startOfMonth(now);const e=new Date(s.getFullYear(),s.getMonth()+1,0);fromEl.valueAsDate=s;toEl.valueAsDate=endOfDay(e);} paintCosts(); }
setRange('month'); quickToday.onclick=()=>setRange('day'); quickWeek.onclick=()=>setRange('week'); quickMonth.onclick=()=>setRange('month');
document.getElementById('applyRange').addEventListener('click',()=>paintCosts());
function paintCosts(){
  if(!fromEl.value||!toEl.value) return;
  const from=new Date(fromEl.value), to=endOfDay(new Date(toEl.value));
  const rows=COSTS.filter(x=>{const d=DMY(x.date);return d>=from&&d<=to;});
  const labels={maint:'Mantenimiento',policy:'Póliza',body:'Carrocería',claim:'Siniestro',other:'Otros'};
  costBody.innerHTML=rows.map(x=>{const car=FLEET.find(c=>c.id===x.car);return `<tr><td>${x.date}</td><td>${car.name} ${car.year} (${car.plate})</td><td>R${car.rim}</td><td>${labels[x.type]||x.type}</td><td>${x.desc}</td><td>${money(x.amt)}</td></tr>`;}).join('');
  const sum=t=>rows.filter(r=>r.type===t).reduce((a,b)=>a+b.amt,0), tot=rows.reduce((a,b)=>a+b.amt,0);
  document.getElementById('gTot').textContent=money(tot); document.getElementById('gMaint').textContent=money(sum('maint')); document.getElementById('gPol').textContent=money(sum('policy')); document.getElementById('gBody').textContent=money(sum('body')); document.getElementById('gOther').textContent=money(sum('claim')+sum('other'));
  // mejoras: conteo, top vehículo, promedio por día
  document.getElementById('gCount').textContent = `${rows.length} movimiento${rows.length!==1?'s':''}`;
  const byCar = {}; rows.forEach(r=>{ byCar[r.car]=(byCar[r.car]||0)+r.amt; });
  let topId=null, topVal=0; Object.entries(byCar).forEach(([id,val])=>{ if(val>topVal){topVal=val; topId=+id;} });
  const topCar = topId? FLEET.find(c=>c.id===topId): null;
  document.getElementById('topCar').textContent = money(topVal||0);
  document.getElementById('topCarName').textContent = topCar? `${topCar.name} ${topCar.year} (${topCar.plate}) · R${topCar.rim}` : '—';
  const days = Math.max(1, Math.ceil((to - from)/(24*60*60*1000))+1);
  document.getElementById('avgPerDay').textContent = money(days? (tot/days):0);
  document.getElementById('rangeLabel').textContent = `${from.toLocaleDateString('es-MX')} → ${to.toLocaleDateString('es-MX')} · ${days} día${days!==1?'s':''}`;
}
document.getElementById('qCost').addEventListener('input',e=>{ const t=e.target.value.toLowerCase(); [...costBody.rows].forEach(r=>r.style.display=r.innerText.toLowerCase().includes(t)?'':'none'); });
document.getElementById('exportCost').addEventListener('click',()=>exportCSV('tblCost','gastos.csv'));

/* ====== MANTENIMIENTO por KM ====== */
function kmProgress(current, last, interval){
  const used=Math.max(0,current-last), left=interval-used, pct=Math.max(0, Math.min(100, Math.round((used/interval)*100)));
  return {used,left,pct,overdue:left<=0};
}
function maintCard(c){
  const oilInt=c.maint.oilIntervalKm||oilIntervalDefault, rotInt=c.maint.rotIntervalKm||rotIntervalDefault;
  const oil=kmProgress(c.km, c.maint.oilLastKm, oilInt);
  const rot=kmProgress(c.km, c.maint.rotLastKm, rotInt);
  const alertOil = oil.overdue ? `<div class="note">⚠ Cambio de aceite vencido</div>` : (oil.left<=500 ? `<div class="note">⚠ Cambio de aceite en ${oil.left.toLocaleString()} km</div>` : '');
  const alertRot = rot.overdue ? `<div class="note">⚠ Rotación de llantas vencida</div>` : '';
  return `
  <article class="mcard" data-id="${c.id}">
    <span class="badge">${c.status}</span>
    <div class="mhead">
      <div class="avatar">${ c.img ? `<img src="${c.img}" alt="auto">` : '🚗' }</div>
      <div>
        <h3 style="margin:0">${c.name} ${c.year}</h3>
        <div class="sub">${c.plate} · ${fmtKm(c.km)} · R${c.rim}</div>
      </div>
    </div>

    <div class="row"><div class="k">Aceite</div><div style="flex:1">
      <div class="bar"><span style="width:${oil.pct}%"></span></div>
      <div class="sub" style="margin-top:4px">${oil.overdue?'Requiere cambio':'Restan '+oil.left.toLocaleString()+' km'}</div>
    </div></div>

    <div class="row"><div class="k">Rotación llantas</div><div style="flex:1">
      <div class="bar"><span style="width:${rot.pct}%"></span></div>
      <div class="sub" style="margin-top:4px">${rot.overdue?'Hacer rotación':'Restan '+rot.left.toLocaleString()+' km'}</div>
    </div></div>

    <div class="row"><div class="k">Balatas</div><div><strong>${c.maint.pads}</strong></div></div>

    ${alertOil}${alertRot}

    <div class="cardActions">
      <button class="btn" data-act="edit">Actualizar km / servicio</button>
      <button class="btn ghost" data-act="ficha">Ver ficha</button>
    </div>
  </article>`;
}
const mGrid=document.getElementById('mGrid');
function renderMaint(list){ mGrid.innerHTML=list.map(maintCard).join(''); }
renderMaint(FLEET);
document.getElementById('qMaint').addEventListener('input',e=>{
  const t=e.target.value.toLowerCase();
  renderMaint(FLEET.filter(c=>`${c.name} ${c.plate} R${c.rim}`.toLowerCase().includes(t)));
});
mGrid.addEventListener('click',e=>{
  const card=e.target.closest('.mcard'); if(!card) return;
  const car=FLEET.find(x=>x.id==card.dataset.id);
  if(e.target.dataset.act==='edit') openMaintEditor(car);
  if(e.target.dataset.act==='ficha') openModal(car);
});

/* ====== Modal FICHA ====== */
const modal=document.getElementById('modal'), mClose=document.getElementById('mClose');
const mTitle=document.getElementById('mTitle'), mChips=document.getElementById('mChips'), mSpecs=document.getElementById('mSpecs'), mDocs=document.getElementById('mDocs'), mNotes=document.getElementById('mNotes'), mImg=document.getElementById('mImg'), mName=document.getElementById('mName'), mPlate=document.getElementById('mPlate');
function chip(label,cls){ return `<span class="tag ${cls}">${label}</span>`; }
function openModal(c){
  mName.textContent=`${c.name} ${c.year}`; mPlate.textContent=c.plate; mTitle.textContent=`${c.name} ${c.year} · ${c.plate}`;
  mImg.src = c.img || ''; mImg.alt='auto';
  const oil=kmProgress(c.km,c.maint.oilLastKm,c.maint.oilIntervalKm||oilIntervalDefault);
  const rot=kmProgress(c.km,c.maint.rotLastKm,c.maint.rotIntervalKm||rotIntervalDefault);
  mChips.innerHTML=[
    chip(c.status, c.status==='Disponible'?'orange':'blue'),
    chip(`Aceite: ${oil.overdue?'Vencido':oil.left.toLocaleString()+' km'}`, oil.overdue?'red':'orange'),
    chip(`Rotación: ${rot.overdue?'Vencida':rot.left.toLocaleString()+' km'}`, rot.overdue?'red':'orange'),
    chip(`Póliza ${polState(c)[0]}`, 'blue'),
    chip(`Rin R${c.rim}`,'orange')
  ].join(' ');

  mSpecs.innerHTML=`
    <div class="k">VIN</div><div>${c.vin}</div>
    <div class="k">Color</div><div>${c.color}</div>
    <div class="k">Transmisión</div><div>${c.trans}</div>
    <div class="k">Combustible</div><div>${c.fuel}</div>
    <div class="k">Clase</div><div>${c.class}</div>
    <div class="k">Asientos/Puertas</div><div>${c.seats} / ${c.doors}</div>
    <div class="k">Servicio</div><div>${c.service}</div>
    <div class="k">Kilometraje</div><div>${fmtKm(c.km)}</div>
    <div class="k">Rin</div><div>R${c.rim}</div>
    <div class="k">Ubicación</div><div>${c.location}</div>
  `;
  mDocs.innerHTML=`
    <div class="k">Póliza</div><div>#${c.policy.num} · ${c.policy.insurer} · ${c.policy.cov} (${c.policy.start} → ${c.policy.end})</div>
    <div class="k">Verificación</div><div>Últ: ${c.verify.last} · Próx: ${c.verify.next}</div>
    <div class="k">Mantenimiento</div><div>Aceite cada ${(c.maint.oilIntervalKm||oilIntervalDefault).toLocaleString()} km · Rotación cada ${(c.maint.rotIntervalKm||rotIntervalDefault).toLocaleString()} km</div>
    <div class="k">Entrada / Salida</div><div>${c.in||'-'} / ${c.out||'-'}</div>
  `;
  mNotes.textContent=c.notes||'—';
  document.getElementById('btnOpenMaint').onclick=()=>openMaintEditor(c);
  modal.classList.add('show');
}
mClose.addEventListener('click',()=>modal.classList.remove('show'));
modal.addEventListener('click',e=>{ if(e.target===modal) modal.classList.remove('show'); });

/* ====== Modal EDITAR MANTENIMIENTO ====== */
const mm=document.getElementById('maintModal'), mmClose=document.getElementById('mmClose'), mmCancel=document.getElementById('mmCancel'), mmForm=document.getElementById('mmForm');
const mmTitle=document.getElementById('mmTitle'), mmKm=document.getElementById('mmKm'), mmAddKm=document.getElementById('mmAddKm'), mmOilAction=document.getElementById('mmOilAction'), mmRotAction=document.getElementById('mmRotAction'), mmPads=document.getElementById('mmPads'), mmNotes=document.getElementById('mmNotes');
let editingCar=null;
function openMaintEditor(c){
  editingCar=c;
  mmTitle.textContent=`${c.name} ${c.year} · ${c.plate} · R${c.rim}`;
  mmKm.value = c.km;
  mmAddKm.value = '';
  mmOilAction.value='none';
  mmRotAction.value='none';
  mmPads.value = c.maint.pads || 'Buenas';
  mmNotes.value = c.maint.notes || '';
  mm.classList.add('show');
}
mmClose.addEventListener('click',()=>mm.classList.remove('show'));
mmCancel.addEventListener('click',()=>mm.classList.remove('show'));
mm.addEventListener('click',e=>{ if(e.target===mm) mm.classList.remove('show'); });

mmForm.addEventListener('submit', e=>{
  e.preventDefault(); if(!editingCar) return;
  const ov = loadOverrides();
  const id = editingCar.id;
  const base = ov[id] || {};
  const newKm = parseInt(mmKm.value||editingCar.km,10) + (parseInt(mmAddKm.value||0,10));
  base.km = newKm;

  base.maint = {...(base.maint||{})};
  if(mmOilAction.value==='done'){ base.maint.oilLastKm = newKm; }
  if(mmRotAction.value==='done'){ base.maint.rotLastKm = newKm; }
  base.maint.pads = mmPads.value;
  base.maint.notes = mmNotes.value;

  ov[id] = {...base};
  saveOverrides(ov);
  FLEET = mergeFleet();

  // re-render
  paintFleet(FLEET);
  renderMaint(FLEET);
  paintPol();
  paintBody();
  renderAlerts();
  paintCosts();
  mm.classList.remove('show');
  modal.classList.remove('show');
});

/* ====== helpers comunes ====== */
function exportCSV(tableId, filename){
  const rows=[...document.querySelectorAll(`#${tableId} tr`)]
    .map(tr=>[...tr.children].map(td=>`"${td.innerText.replace(/"/g,'""')}"`).join(',')).join('\n');
  const blob=new Blob([rows],{type:'text/csv;charset=utf-8;'}); const a=document.createElement('a');
  a.href=URL.createObjectURL(blob); a.download=filename; a.click(); URL.revokeObjectURL(a.href);
}

/* ====== Búsquedas restantes ====== */
document.getElementById('qClaims').addEventListener('input',e=>{ const t=e.target.value.toLowerCase(); document.querySelectorAll('#tblClaims tbody tr').forEach(r=>r.style.display=r.innerText.toLowerCase().includes(t)?'':'none'); });
document.getElementById('newService').addEventListener('click',()=>alert('Demo: aquí podrías abrir un planificador de servicios.'));
