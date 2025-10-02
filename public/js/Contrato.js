const $=s=>document.querySelector(s);
const esc = s => (s??'').toString().replace(/[&<>"]/g,m=>({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;'}[m]));
const Fmx=v=>'$'+Number(v||0).toLocaleString('es-MX',{minimumFractionDigits:2,maximumFractionDigits:2})+' MXN';

/* === datos del contrato en proceso === */
const obj = JSON.parse(localStorage.getItem('vc_contrato_en_proceso')||'null');
if(!obj){ alert('No hay reservación seleccionada.'); location.href='reservaciones-activas.html'; }

/* Mes abreviado (MAYÚS) */
const MES = ['ENE','FEB','MAR','ABR','MAY','JUN','JUL','AGO','SEP','OCT','NOV','DIC'];
function ymd(s){ // 'YYYY-MM-DD' -> {y,m,d}
  if(!s) return null;
  const [Y,M,D] = s.split('-').map(n=>parseInt(n,10));
  return { y:Y, m:M, d:D };
}
function siteTag(txt){ return esc(txt||'—'); }

/* Catálogo de autos (para imagen/especificación) */
const catalog = JSON.parse(localStorage.getItem('vc_autos')||'[]');
function findAutoInfo(text){
  if(!text) return null;
  return catalog.find(a => text.toLowerCase().includes((a.nombre||'').toLowerCase())) || null;
}
const car = findAutoInfo(obj.vehiculo);
const imgSrc = car?.img ? (car.img.startsWith('../')? car.img : `../assets/media/${car.img}`) : '../assets/media/nissan.gif';

/* Precios (mantenemos) */
const days = obj.days || 1;
const pDay = obj.precioDia || 0;
const base = days * pDay;
const prot = obj.prot?.price ? (obj.prot.price * days) : 0;
const extras = Object.entries(obj.adds||{}).reduce((s,[k,v])=>{
  const cat = (obj.addCatalog||[]).find(a=>a.id===k);
  if(!cat || !v) return s;
  const add = (cat.per==='day' ? cat.price*days*v : cat.price*v);
  return s+add;
},0);
const subtotal = base + prot + extras;
const iva = subtotal * 0.16;
const total = subtotal + iva;

/* Fechas para tiles */
const pi = ymd(obj.fIni), pd = ymd(obj.fFin);
const mmI = MES[(pi?.m||1)-1]||'', mmD = MES[(pd?.m||1)-1]||'';
const yyI = (pi?.y||'')+'', yyD = (pd?.y||'')+'';

/* LEFT: encabezado y tiles */
$('#left').innerHTML = `
  <div class="kv"><div>Código Reservación</div><div style="font-weight:900">${esc(obj.id)}</div></div>
  <div class="kv"><div>Titular de la Reservación</div><div>${esc((obj.cliente?.nombre||'')+' '+(obj.cliente?.apellidos||''))}</div></div>

  <div class="flex-tiles">
    <!-- ENTREGa -->
    <div class="tile">
      <div class="bar">ENTREGA</div>
      <div class="body">
        <div class="site">${siteTag(obj.sedePick)}</div>
        <div class="datebox">
          <div class="bigday">${pi?.d??'–'}</div>
          <div class="mmy">
            <div class="mm">${mmI}</div>
            <div class="yy">${yyI}</div>
          </div>
        </div>
        <div class="hr">${esc(obj.hIni||'--:--')} HRS</div>
      </div>
    </div>

    <!-- DEVOLUCIÓN -->
    <div class="tile">
      <div class="bar">DEVOLUCIÓN</div>
      <div class="body">
        <div class="site">${siteTag(obj.sedeDrop)}</div>
        <div class="datebox">
          <div class="bigday">${pd?.d??'–'}</div>
          <div class="mmy">
            <div class="mm">${mmD}</div>
            <div class="yy">${yyD}</div>
          </div>
        </div>
        <div class="hr">${esc(obj.hFin||'--:--')} HRS</div>
      </div>
    </div>
  </div>

  <div class="totalBox">
    <div class="kv"><div>Días</div><div>${days}</div></div>
    <div class="kv"><div>Tarifa base</div><div>${days} × ${Fmx(pDay)} = <b>${Fmx(base)}</b></div></div>
    <div class="kv"><div>Protección</div><div>${obj.prot?.code||'—'} ${obj.prot?.price?('× '+days+' = '+Fmx(prot)):' '}</div></div>
    <div class="kv"><div>Adicionales</div><div>${Fmx(extras)}</div></div>
    <div class="kv"><div>IVA (16%)</div><div>${Fmx(iva)}</div></div>
    <div class="kv"><div>Total</div><div class="total">${Fmx(total)}</div></div>
  </div>
`;

/* RIGHT: auto + bullets + botón continuar */
$('#right').innerHTML = `
  <div class="titleCar">${esc(obj.vehiculo||'Vehículo')}</div>
  <img class="imgCar" src="${imgSrc}" onerror="this.onerror=null;this.src='../assets/media/nissan.gif'">
  <div class="bullets">
    <div> <span>🚪</span> <span>${esc(car?.puertas||'5')} Puertas</span></div>
    <div> <span>👥</span> <span>${esc(car?.pax||'5')} Pasajeros</span></div>
    <div> <span>⚙️</span> <span>Transmisión ${esc(car?.transm||'AUTOMÁTICO')}</span></div>
  </div>

  <div style="margin-top:18px">
    <button class="btn primary" id="go">CONTINUAR CONTRATO <span id="folioSpan"></span></button>
  </div>
`;

/* Folio VRA */
function nextFolio(){
  const k='vc_contrato_seq'; const n=(Number(localStorage.getItem(k)||'0')+1);
  localStorage.setItem(k,n);
  return 'VRA'+String(n).padStart(4,'0');
}
const folio=nextFolio();
$('#folioSpan').textContent = folio;

/* Continuar: marca estado y listo para siguiente paso */
$('#go').onclick = ()=>{
  try{
    const all = JSON.parse(localStorage.getItem('vc_reservas')||'[]');
    const i = all.findIndex(r=>r.id===obj.id);
    if(i>=0){ all[i].status='En contrato'; localStorage.setItem('vc_reservas', JSON.stringify(all)); }
    alert('Contrato iniciado ('+folio+').');
    // Aquí podrías redirigir a la siguiente pantalla del flujo:
    location.href = 'continuarcontrato.html';
  }catch(e){ console.warn(e); }
};
