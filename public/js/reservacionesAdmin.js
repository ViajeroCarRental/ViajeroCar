/* Helpers */
const $=s=>document.querySelector(s);
const $$=s=>Array.from(document.querySelectorAll(s));
const esc=s=>(s??'').toString().replace(/[&<>"]/g,m=>({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;'}[m]));
const fmtMXN=v=>'$'+Number(v).toLocaleString('es-MX',{minimumFractionDigits:2,maximumFractionDigits:2})+' MXN';
const fmtUSD=v=>'$'+Number(v).toFixed(2)+' USD';
const F=v=>($('#moneda').value==='USD'? fmtUSD(v/Number($('#tc').value||17)) : fmtMXN(v));
const fmt12=(hh,mm)=>{ let h=Number(hh)||0, m=('0'+(Number(mm)||0)).slice(-2); const pm=h>=12; h=h%12; if(h===0)h=12; return `${('0'+h).slice(-2)}:${m} ${pm?'PM':'AM'}`; };
const fmtDateMX=(iso)=>/^\d{4}-\d{2}-\d{2}$/.test(iso||'')? `${iso.slice(8,10)}/${iso.slice(5,7)}/${iso.slice(0,4)}`:'—';
const safe=v=>(v===undefined||v===null||v==='')?'—':v;

$('#burger')?.addEventListener('click',()=>$('#side').classList.toggle('show'));

/* Seed mínima */
(function seed(){
  if(!localStorage.getItem('vc_sedes')){
    localStorage.setItem('vc_sedes', JSON.stringify([
      {id:'BJX',name:'Aeropuerto Intl del Bajío, León (BJX)'},
      {id:'AIQ',name:'Aeropuerto de Querétaro (AIQ)'},
      {id:'GDL',name:'Aeropuerto Intl (GDL)'},
      {id:'MTY',name:'Aeropuerto Intl Nuevo León (MTY)'},
      {id:'CENTRO-QRO',name:'Querétaro Centro'}
    ]));
  }
  if(!localStorage.getItem('vc_autos')){
    localStorage.setItem('vc_autos', JSON.stringify([
      {id:'C-AVE0', clase:'C | COMPACTO AUTOMÁTICO', nombre:'CHEVROLET® Aveo', puertas:5, pax:5, transm:'AUTOMÁTICO', img:'../assets/media/aveo.png', precioDia:1868},
      {id:'D-VIRT', clase:'D | INTERMEDIO', nombre:'VOLKSWAGEN® Virtus', puertas:5, pax:5, transm:'AUTOMÁTICO', img:'../assets/media/virtus.jpg', precioDia:2400},
      {id:'E-SENT', clase:'E | GRANDE', nombre:'NISSAN® Sentra', puertas:4, pax:5, transm:'AUTOMÁTICO', img:'../assets/media/sentra.jpg', precioDia:2650}
    ]));
  }
})();

/* Sedes */
(function fillSedes(){
  const sedes=JSON.parse(localStorage.getItem('vc_sedes')||'[]');
  $('#sedePick').innerHTML=sedes.map(s=>`<option>${esc(s.name)}</option>`).join('');
  $('#sedeDrop').innerHTML=sedes.map(s=>`<option>${esc(s.name)}</option>`).join('');
  $('#sedeDrop').value=$('#sedePick').value;
})();

/* Paso switching */
const showStep=n=>{ $$('[data-step]').forEach(el=>el.style.display = (Number(el.dataset.step)===n?'block':'none')); };
$('#go2')?.addEventListener('click',()=>showStep(2));
$('#back1')?.addEventListener('click',()=>showStep(1));
$('#go3')?.addEventListener('click',()=>{ showStep(3); buildTicket(liveObj()); prepareShare(liveObj()); });
$('#back2')?.addEventListener('click',()=>showStep(2));
showStep(1);

/* ===== Date-picker RANGO con arrastre (tipo Apple) ===== */
const rangeDP = {
  startEl: $('#fIniBox'),
  endEl:   $('#fFinBox'),
  month: new Date(),
  start: null,
  end: null,
  dragging: false
};
function iso(d){ return d.toISOString().slice(0,10); }
function ymd(Y,M,D){ return new Date(Y, M, D, 0,0,0,0); }
function fromBox(el){
  const v=(el?.textContent||'').trim();
  return /^\d{4}-\d{2}-\d{2}$/.test(v)? new Date(v+'T00:00:00') : null;
}
function openRangeDP(){
  const s=fromBox(rangeDP.startEl), e=fromBox(rangeDP.endEl);
  rangeDP.start = s; rangeDP.end = e && s ? (e<s? s : e) : e || s;
  rangeDP.month = new Date((rangeDP.start||new Date()).getFullYear(), (rangeDP.start||new Date()).getMonth(), 1);
  buildRangeDP();
  $('#dpPop').classList.add('show');
}
function buildRangeDP(){
  const cont = $('#dpGrid'); cont.innerHTML='';
  const m0 = new Date(rangeDP.month.getFullYear(), rangeDP.month.getMonth(), 1);
  $('#dpMonth').textContent = m0.toLocaleString('es-MX',{month:'long',year:'numeric'}).replace(/^./,c=>c.toUpperCase());
  'Dom Lun Mar Mié Jue Vie Sáb'.split(' ').forEach(n=> cont.insertAdjacentHTML('beforeend', `<div class="dow">${n}</div>`));
  const pad = (m0.getDay()+7)%7;
  for(let i=0;i<pad;i++) cont.insertAdjacentHTML('beforeend','<div></div>');
  const last = new Date(m0.getFullYear(), m0.getMonth()+1, 0).getDate();
  const todayISO = new Date().toISOString().slice(0,10);
  for(let d=1; d<=last; d++){
    const date = new Date(m0.getFullYear(), m0.getMonth(), d);
    const el = document.createElement('div');
    el.className='cell';
    el.dataset.iso = iso(date);
    el.textContent = d;
    if(iso(date)===todayISO) el.classList.add('today');
    applyCellState(el, date);

    el.addEventListener('mousedown', e=> { e.preventDefault(); startDrag(date); });
    el.addEventListener('mouseenter',  ()=> dragOver(date));
    document.addEventListener('mouseup', endDrag);

    el.addEventListener('touchstart',  e=> { e.preventDefault(); startDrag(date); }, {passive:false});
    el.addEventListener('touchmove',   e=> {
      const t = document.elementFromPoint(e.touches[0].clientX, e.touches[0].clientY);
      const isoAttr = t && t.dataset ? t.dataset.iso : null;
      if(isoAttr){ dragOver(new Date(isoAttr)); }
    }, {passive:false});
    document.addEventListener('touchend', endDrag, {passive:true});

    cont.appendChild(el);
  }
}
function applyCellState(el, date){
  el.classList.remove('range-start','range-end','in-range');
  if(!rangeDP.start && !rangeDP.end) return;
  const S = rangeDP.start ? ymd(rangeDP.start.getFullYear(), rangeDP.start.getMonth(), rangeDP.start.getDate()) : null;
  const E = rangeDP.end   ? ymd(rangeDP.end.getFullYear(),   rangeDP.end.getMonth(),   rangeDP.end.getDate())   : null;
  const D = ymd(date.getFullYear(), date.getMonth(), date.getDate());
  if(S && !E && D.getTime()===S.getTime()) el.classList.add('range-start','range-end');
  if(S && E){
    if(D.getTime()===S.getTime()) el.classList.add('range-start');
    if(D.getTime()===E.getTime()) el.classList.add('range-end');
    if(D>S && D<E) el.classList.add('in-range');
  }
}
function redrawStates(){ $$('#dpGrid .cell').forEach(cell=> applyCellState(cell, new Date(cell.dataset.iso))); }
function startDrag(date){
  rangeDP.dragging = true;
  if(!rangeDP.start || (rangeDP.start && rangeDP.end)){
    rangeDP.start = date; rangeDP.end = null;
  }else{
    if(date < rangeDP.start){ rangeDP.end = rangeDP.start; rangeDP.start = date; }
    else { rangeDP.end = date; }
  }
  redrawStates();
}
function dragOver(date){
  if(!rangeDP.dragging) return;
  if(!rangeDP.start){ rangeDP.start = date; redrawStates(); return; }
  if(date < rangeDP.start){ rangeDP.end = rangeDP.start; rangeDP.start = date; }
  else rangeDP.end = date;
  redrawStates();
}
function endDrag(){
  if(!rangeDP.dragging) return;
  rangeDP.dragging = false;
  if(rangeDP.start && !rangeDP.end) rangeDP.end = rangeDP.start;
  redrawStates();
}
$('#dpPrev')?.addEventListener('click',()=>{ rangeDP.month.setMonth(rangeDP.month.getMonth()-1); buildRangeDP(); });
$('#dpNext')?.addEventListener('click',()=>{ rangeDP.month.setMonth(rangeDP.month.getMonth()+1); buildRangeDP(); });
$('#dpToday')?.addEventListener('click',()=>{ const t=new Date(); rangeDP.month=new Date(t.getFullYear(),t.getMonth(),1); rangeDP.start=t; rangeDP.end=t; buildRangeDP(); });
$('#dpClear')?.addEventListener('click',()=>{ rangeDP.start=null; rangeDP.end=null; redrawStates(); });
$('#dpApply')?.addEventListener('click',()=>{
  if(rangeDP.start){
    const s = iso(rangeDP.start), e = iso(rangeDP.end||rangeDP.start);
    $('#fIniBox').textContent = s;
    $('#fFinBox').textContent = e;
    calc();
  }
  $('#dpPop').classList.remove('show');
});
$('#dpPop')?.addEventListener('click',e=>{ if(e.target.id==='dpPop') $('#dpPop').classList.remove('show'); });
$$('[data-dp]').forEach(el=> el.addEventListener('click', openRangeDP));

/* ===== Time picker 12h ===== */
let tpAnchor=null, tp12Hour=8, tpMin=0, tpIsPM=false;
function pad2(n){ return ('0'+n).slice(-2); }
function parse12(str){ const m=(str||'').trim().match(/^(\d{1,2}):([0-5]\d)\s*(AM|PM)$/i); if(!m) return null; let hh12=+m[1]; if(hh12<1||hh12>12) return null; return {hh12:hh12, mm:+m[2], pm:m[3].toUpperCase()==='PM'}; }
function to24(hh12,mm,pm){ let h=hh12%12; if(pm) h+=12; return {h24:h,m:mm}; }
function from24(h24,m){ const pm=h24>=12; let h12=h24%12; if(h12===0)h12=12; return {hh12:h12, mm:m, pm}; }
function openTP(anchor){
  tpAnchor = anchor;
  const cur = (tpAnchor.textContent||'').trim();
  let preset = parse12(cur);
  if(!preset){
    const m = cur.match(/^([01]?\d|2[0-3]):([0-5]\d)$/);
    if(m){ const conv=from24(+m[1],+m[2]); preset={hh12:conv.hh12, mm:conv.mm, pm:conv.pm}; }
  }
  if(preset){ tp12Hour=preset.hh12; tpMin=[0,15,30,45].includes(preset.mm)?preset.mm:0; tpIsPM=preset.pm; }
  else      { tp12Hour=8; tpMin=0; tpIsPM=false; }
  buildTP(); $('#tpPop').classList.add('show');
}
function buildTP(){
  const H = $('#tpHours'); H.innerHTML='';
  for(let h=1; h<=12; h++){
    const el=document.createElement('div'); el.className='tp-hour'+(h===tp12Hour?' sel':''); el.textContent=pad2(h);
    el.onclick=()=>{ tp12Hour=h; buildTP(); }; H.appendChild(el);
  }
  const M=$('#tpMins'); M.innerHTML='';
  [0,15,30,45].forEach(m=>{ const chip=document.createElement('div'); chip.className='tp-chip'+(m===tpMin?' sel':''); chip.textContent=pad2(m); chip.onclick=()=>{ tpMin=m; buildTP(); }; M.appendChild(chip); });
  const amB=$('#tpAM'), pmB=$('#tpPM');
  if(amB&&pmB){ amB.classList.toggle('primary',!tpIsPM); amB.classList.toggle('gray',tpIsPM); pmB.classList.toggle('primary',tpIsPM); pmB.classList.toggle('gray',!tpIsPM);
    amB.onclick=()=>{ tpIsPM=false; buildTP(); }; pmB.onclick=()=>{ tpIsPM=true; buildTP(); }; }
  $('#tpManual').value = `${pad2(tp12Hour)}:${pad2(tpMin)} ${tpIsPM?'PM':'AM'}`;
  $$('#tpPop .tp-q').forEach(q=>{ q.onclick=()=>{ const v=q.dataset.q;
    if(v==='now'){ const d=new Date(); const conv=from24(d.getHours(), d.getMinutes()); tp12Hour=conv.hh12; tpMin=[0,15,30,45].reduce((a,b)=> Math.abs(b-conv.mm)<Math.abs(a-conv.mm)?b:a,0); tpIsPM=conv.pm; buildTP(); return; }
    if(v==='+30'){ let tot=(to24(tp12Hour,tpMin,tpIsPM).h24*60 + tpMin) + 30; tot=(tot+1440)%1440; const h24=Math.floor(tot/60), mm=tot%60; const conv=from24(h24,mm);
      tp12Hour=conv.hh12; tpMin=[0,15,30,45].reduce((a,b)=> Math.abs(b-conv.mm)<Math.abs(a-conv.mm)?b:a,0); tpIsPM=conv.pm; buildTP(); return; }
    const p=parse12(v); if(!p) return; tp12Hour=p.hh12; tpMin=p.mm; tpIsPM=p.pm; buildTP();
  }; });
}
function applyTP(){ if(!tpAnchor) return; tpAnchor.textContent = `${pad2(tp12Hour)}:${pad2(tpMin)} ${tpIsPM?'PM':'AM'}`; $('#tpPop').classList.remove('show'); calc(); }
$('#tpApply')?.addEventListener('click',applyTP);
$('#tpClear')?.addEventListener('click',()=>{ if(tpAnchor){ tpAnchor.textContent='--:-- AM/PM'; } $('#tpPop').classList.remove('show'); calc(); });
$('#tpSetManual')?.addEventListener('click',()=>{ const v=($('#tpManual').value||'').trim().toUpperCase(); const p=parse12(v); if(!p){ alert('Escribe una hora válida: hh:mm AM/PM'); return;} tp12Hour=p.hh12; tpMin=p.mm; tpIsPM=p.pm; applyTP(); });
$('#tpClose')?.addEventListener('click',()=> $('#tpPop').classList.remove('show'));
$('#tpPop')?.addEventListener('click',e=>{ if(e.target.id==='tpPop') $('#tpPop').classList.remove('show'); });
$$('[data-tp]').forEach(el=> el.addEventListener('click', ()=> openTP(el) ));

/* Vehículos */
function renderVeh(){
  const list=JSON.parse(localStorage.getItem('vc_autos')||'[]');
  const c=$('#vehList'); if(!c) return; c.innerHTML='';
  list.forEach(a=>{
    c.insertAdjacentHTML('beforeend',`
      <div class="vitem" style="display:grid;grid-template-columns:120px 1fr 160px;gap:12px;align-items:center;border:1px solid var(--stroke);border-radius:12px;padding:12px;margin-bottom:12px">
        <img src="${esc(a.img)||'../assets/media/'}" alt="" style="width:120px;height:72px;object-fit:cover;border-radius:10px;background:#F3F4F6">
        <div>
          <div style="font-weight:800">${esc(a.clase)}</div>
          <div class="small">${esc(a.nombre)} o similar</div>
          <div class="small" style="margin-top:6px">🚪 ${a.puertas} · 👥 ${a.pax} · ⚙️ ${a.transm}</div>
        </div>
        <div style="text-align:right">
          <div style="background:#F3F4F6;border:1px solid #E5E7EB;border-radius:999px;padding:7px 10px;font-weight:900;display:inline-block">$${a.precioDia.toLocaleString()} MXN</div>
          <div style="margin-top:10px"><button class="btn primary" data-pick="${a.id}">Seleccionar</button></div>
        </div>
      </div>
    `);
  });
}
$('#btnVeh')?.addEventListener('click',()=>{ renderVeh(); $('#vehPop')?.classList.add('show'); });
$('#vehClose')?.addEventListener('click',()=>$('#vehPop')?.classList.remove('show'));
$('#vehPop')?.addEventListener('click',e=>{ if(e.target.id==='vehPop') $('#vehPop').classList.remove('show'); });
$('#vehList')?.addEventListener('click',e=>{
  const id=e.target?.dataset?.pick; if(!id) return;
  const a=(JSON.parse(localStorage.getItem('vc_autos'))||[]).find(x=>x.id===id); if(!a) return;
  $('#vehiculoSel').value=`${a.clase} · ${a.nombre}`; $('#precioDia').value=a.precioDia; calc(); $('#vehPop').classList.remove('show');
});

/* Adicionales */
const addCatalog=[
  {id:'seat',   head:'Silla para niño',         icon:'🧒', desc:'18–36 kg',                 price:150, per:'day',  max:3},
  {id:'upgrade',head:'Upgrade',                  icon:'⬆️', desc:'Mejora de categoría',     price:200, per:'day',  max:1},
  {id:'gps',    head:'Servicio / GPS',          icon:'📍', desc:'Cobertura estatal',       price:200, per:'day',  max:1},
  {id:'driver+',head:'Conductor Adicional',     icon:'🧑‍✈️',desc:'En la reservación',       price:150, per:'day',  max:2},
  {id:'young',  head:'Conductor Menor de edad', icon:'🙂', desc:'Con permiso',             price:200, per:'day',  max:1},
  {id:'expired',head:'Licencia vencida',        icon:'🪪', desc:'Verificación manual',     price:350, per:'rent', max:1},
  {id:'phone',  head:'Accesorios celular',      icon:'📱', desc:'Cargador/manos libres',   price:100, per:'rent', max:2}
];
const addState=Object.fromEntries(addCatalog.map(a=>[a.id,0]));
function renderAdds(){
  const g=$('#addGrid'); if(!g) return; g.innerHTML='';
  addCatalog.forEach(a=>{
    g.insertAdjacentHTML('beforeend',`
      <div class="add" data-id="${a.id}">
        <div class="head">${esc(a.head)}</div>
        <div class="body">
          <div class="iconbig">${a.icon}</div>
          <div class="small">${esc(a.desc)}</div>
          <div class="price">$ ${a.price} MXN ${a.per==='day'?'× Día':'× Renta'}</div>
          <div class="qty">
            <button type="button" data-act="minus">–</button>
            <input type="text" value="0" readonly>
            <button type="button" data-act="plus">+</button>
          </div>
        </div>
      </div>
    `);
  });
}
renderAdds();
$('#addGrid')?.addEventListener('click',e=>{
  const card=e.target.closest('.add'); if(!card) return;
  const id=card.dataset.id; const cat=addCatalog.find(x=>x.id===id);
  if(e.target.dataset.act==='plus'){ addState[id]=Math.min(cat.max,(addState[id]||0)+1); }
  if(e.target.dataset.act==='minus'){ addState[id]=Math.max(0,(addState[id]||0)-1); }
  card.querySelector('input').value=addState[id]; calc();
});

/* Cálculo */
function parseDT(d,t){
  if(!d||!t) return null;
  const m12=(t||'').match(/^(\d{1,2}):([0-5]\d)\s*(AM|PM)$/i);
  let h=0, mm=0;
  if(m12){
    let hh12=+m12[1]; mm=+m12[2]; const pm=m12[3].toUpperCase()==='PM'; if(hh12<1||hh12>12) return null;
    h=(hh12%12)+(pm?12:0);
  }else{
    const m24=(t||'').match(/^([01]?\d|2[0-3]):([0-5]\d)$/); if(!m24) return null; h=+m24[1]; mm=+m24[2];
  }
  const [Y,M,Da]=(d||'').split('-').map(Number); if(!Y||!M||!Da) return null;
  return new Date(Y,M-1,Da,h,mm,0,0);
}
function computeDays(){
  const d1=$('#fIniBox').textContent.trim(), d2=$('#fFinBox').textContent.trim();
  const t1=$('#hIniBox').textContent.trim(), t2=$('#hFinBox').textContent.trim();
  const ini=parseDT(d1,t1), fin=parseDT(d2,t2); if(!ini||!fin) return 0;
  const ms=fin-ini; if(ms<=0) return 0; const day=86400000, grace=3600000;
  const base=Math.floor(ms/day); const rem=ms%day; let days=base+(rem>grace?1:0); if(days===0) days=1; return days;
}
function prote(){ const v=$('#proteccion').value; if(!v) return null; const [code,price]=v.split('|'); return {code,price:Number(price)}; }
function extras(days){
  const arr=[]; addCatalog.forEach(a=>{ const q=addState[a.id]||0; if(!q) return;
    const total=(a.per==='day')? a.price*days*q : a.price*q; arr.push({id:a.id,name:a.head, per:a.per, price:a.price, qty:q, total});
  }); return arr;
}
function calc(){
  const days=computeDays(); $('#diasBadge').textContent=`${days} día(s)`;
  const p=Number($('#precioDia').value||0); const base=p*days;
  $('#baseLine').textContent=days? `${days} día(s) @ $${p.toLocaleString()} MXN`:'—';

  const pr=prote(); const prTot=pr? pr.price*days:0;
  $('#proteName').textContent = pr? (pr.price? `${pr.code} $${pr.price}/día (${days}d)` : pr.code) : '—';

  const xs=extras(days); const xsTxt=xs.length? xs.map(e=>`${e.name}${e.qty>1?` ×${e.qty}`:''}`).join(' + ') : '—';
  $('#extrasName').textContent=xsTxt; const xsTot=xs.reduce((s,e)=>s+e.total,0);

  const subtotal=base+prTot+xsTot, iva=+(subtotal*0.16).toFixed(2), total=+(subtotal+iva).toFixed(2);
  $('#subTot').textContent=F(subtotal); $('#iva').textContent=F(iva); $('#total').textContent=F(total);

  const tmp={ sedePick:$('#sedePick').value, sedeDrop:$('#sedeDrop').value,
    fIni:$('#fIniBox').textContent.trim(), fFin:$('#fFinBox').textContent.trim(),
    hIni:$('#hIniBox').textContent.trim(), hFin:$('#hFinBox').textContent.trim(),
    days, precioDia:p, vehiculo:$('#vehiculoSel').value, prot:pr||null,
    adds:addState, addCatalog, subtotal, iva, total, moneda:$('#moneda').value, tc:Number($('#tc').value||17) };
  localStorage.setItem('vc_reserva_tmp', JSON.stringify(tmp));
}
;['precioDia','proteccion','moneda','tc'].forEach(id=>document.getElementById(id)?.addEventListener('input',calc));
$('#recalc')?.addEventListener('click',calc);

/* init fechas por defecto */
(function initDates(){
  const d=new Date(), fmt=x=>x.toISOString().slice(0,10), add=(x,n)=>{const y=new Date(x);y.setDate(y.getDate()+n);return y};
  $('#fIniBox').textContent=fmt(d); $('#hIniBox').textContent='08:00 AM';
  $('#fFinBox').textContent=fmt(add(d,2)); $('#hFinBox').textContent='11:00 AM';
  calc();
})();
(function loadTmp(){ try{
  const t=JSON.parse(localStorage.getItem('vc_reserva_tmp')||'null'); if(!t) return;
  $('#sedePick').value=t.sedePick||$('#sedePick').value; $('#sedeDrop').value=t.sedeDrop||$('#sedeDrop').value;
  if(t.fIni) $('#fIniBox').textContent=t.fIni; if(t.fFin) $('#fFinBox').textContent=t.fFin;
  if(t.hIni) $('#hIniBox').textContent=t.hIni; if(t.hFin) $('#hFinBox').textContent=t.hFin;
  if(t.precioDia) $('#precioDia').value=t.precioDia;
  if(t.moneda) $('#moneda').value=t.moneda; if(t.tc) $('#tc').value=t.tc;
  if(t.adds){ Object.assign(addState,t.adds); addCatalog.forEach(a=>{const card=$(`#addGrid .add[data-id="${a.id}"]`); if(card) card.querySelector('input').value=addState[a.id]||0;}); }
  calc();
}catch{}})();

/* Persistencia */
function nextId(){ const k='vc_res_seq'; const n=(Number(localStorage.getItem(k)||'0')+1); localStorage.setItem(k,n);
  const d=new Date(); const y=d.getFullYear(), m=('0'+(d.getMonth()+1)).slice(-2), da=('0'+d.getDate()).slice(-2);
  return `R-${y}${m}${da}-${('0000'+n).slice(-4)}`; }
function liveObj(){ const t=JSON.parse(localStorage.getItem('vc_reserva_tmp')||'{}');
  const cli={nombre:$('#cNombre').value.trim(), apellidos:$('#cApellidos').value.trim(), email:$('#cEmail').value.trim(), tel:$('#cTel').value.trim(), pais:$('#cPais').value.trim(), vuelo:$('#cVuelo').value.trim(), sms:$('#smsOK').checked};
  return { id:'SIN-ID', createdAt:Date.now(), status:'Edición', ...t, totalsMXN:{subtotal:t.subtotal||0,iva:t.iva||0,total:t.total||0}, cliente:cli };
}
function collect(status){ const o=liveObj(); o.id=nextId(); o.status=status; return o; }
function persist(obj){ const k='vc_reservas'; const arr=JSON.parse(localStorage.getItem(k)||'[]'); arr.push(obj); localStorage.setItem(k,JSON.stringify(arr)); localStorage.removeItem('vc_reserva_tmp'); }

/* Ticket + compartir */
function buildTicket(obj){
  $('#tId').textContent=safe(obj.id);
  $('#tStatus').textContent=safe(obj.status);
  $('#tCreated').textContent=new Date(obj.createdAt||Date.now()).toLocaleString('es-MX');

  const nombre = [obj.cliente?.nombre, obj.cliente?.apellidos].filter(Boolean).join(' ') || '—';
  $('#tCliente').textContent = nombre;
  $('#tContacto').textContent = [obj.cliente?.email||'—', obj.cliente?.tel||'—'].join(' · ');
  $('#tPais').textContent = safe(obj.cliente?.pais);
  $('#tVuelo').textContent = safe(obj.cliente?.vuelo);
  $('#tSms').textContent = obj.cliente?.sms ? 'Acepta SMS' : '—';

  const p = parseDT(obj.fIni, obj.hIni), d = parseDT(obj.fFin, obj.hFin);
  const pDate = fmtDateMX(obj.fIni), dDate = fmtDateMX(obj.fFin);
  const pTime = p? fmt12(p.getHours(), p.getMinutes()) : '—';
  const dTime = d? fmt12(d.getHours(), d.getMinutes()) : '—';

  $('#tPick').textContent = `${safe(obj.sedePick)} · ${pDate} ${pTime}`;
  $('#tDrop').textContent = `${safe(obj.sedeDrop)} · ${dDate} ${dTime}`;
  $('#tVeh').textContent  = safe(obj.vehiculo);
  $('#tDias').textContent = obj.days? `${obj.days} día(s)` : '—';

  $('#tRate').textContent = obj.precioDia? fmtMXN(obj.precioDia) : '—';
  const protTxt = obj.prot ? (obj.prot.price>0 ? `${obj.prot.code} · ${fmtMXN(obj.prot.price)}/día` : obj.prot.code) : '—';
  $('#tProt').textContent = protTxt;

  const xs = extras(obj.days||0);
  $('#tExtras').innerHTML = xs.length
    ? xs.map(e=>`${e.name}${e.qty>1?` ×${e.qty}`:''} <span class="tsub">(${e.per==='day'?'día':'renta'} · ${fmtMXN(e.price)})</span>`).join('<br>')
    : '—';

  const monedaTxt = obj.moneda==='USD' ? `USD (TC ${Number(obj.tc||17).toFixed(2)})` : 'MXN';
  $('#tMoneda').textContent = monedaTxt;

  $('#tSubtotal').textContent = F(obj.subtotal||0);
  $('#tIva').textContent      = F(obj.iva||0);
  $('#tTotal').textContent    = F(obj.total||0);
}
function prepareShare(obj){
  const p = parseDT(obj.fIni, obj.hIni), d = parseDT(obj.fFin, obj.hFin);
  const pDate = fmtDateMX(obj.fIni), dDate = fmtDateMX(obj.fFin);
  const pTime = p? fmt12(p.getHours(), p.getMinutes()) : '—';
  const dTime = d? fmt12(d.getHours(), d.getMinutes()) : '—';

  const text =
    `Reserva ${safe(obj.id)}%0A`+
    `Cliente: ${encodeURIComponent([obj.cliente?.nombre,obj.cliente?.apellidos].filter(Boolean).join(' ')||'—')}%0A`+
    `Contacto: ${encodeURIComponent((obj.cliente?.email||'—')+' · '+(obj.cliente?.tel||'—'))}%0A`+
    `Pick Up: ${encodeURIComponent(`${safe(obj.sedePick)} · ${pDate} ${pTime}`)}%0A`+
    `Devolución: ${encodeURIComponent(`${safe(obj.sedeDrop)} · ${dDate} ${dTime}`)}%0A`+
    `Vehículo: ${encodeURIComponent(safe(obj.vehiculo))}%0A`+
    `Días: ${obj.days||0}%0A`+
    `Total: ${encodeURIComponent(F(obj.total||0))}%0A`+
    `Gracias por elegir Viajero.`;

  const email = (obj.cliente?.email||'').trim();
  const raw   = (obj.cliente?.tel||'').replace(/\D/g, '');
  let   phone = raw; if (raw.length === 10) phone = '52' + raw;

  $('#btnMail').href = `mailto:${encodeURIComponent(email)}?subject=${encodeURIComponent('Confirmación de Reservación '+safe(obj.id))}&body=${text}`;
  if (phone.length >= 10) {
    $('#btnWa').href = `https://wa.me/${phone}?text=${text}`;
    $('#btnSms').href = `sms:+${phone}?&body=${text}`;
  } else {
    $('#btnWa').href = `https://wa.me/?text=${text}`;
    $('#btnSms').href = `sms:?&body=${text}`;
  }
}

/* PDF */
async function generateTicketPDF(obj) {
  const { jsPDF } = window.jspdf;
  const ticketSec = document.getElementById('ticket');
  const before = ticketSec.style.display;
  ticketSec.style.display = 'block';
  const node = document.getElementById('ticketCard');
  const canvas = await html2canvas(node,{scale:2,backgroundColor:'#ffffff',useCORS:true});
  ticketSec.style.display = before || 'none';
  const pdf = new jsPDF({ unit:'pt', format:'a4' });
  const pageW=pdf.internal.pageSize.getWidth(), pageH=pdf.internal.pageSize.getHeight();
  const imgData = canvas.toDataURL('image/png');
  const ratio = Math.min(pageW / canvas.width, pageH / canvas.height);
  const w = canvas.width * ratio, h = canvas.height * ratio;
  const x=(pageW - w)/2, y=36;
  pdf.addImage(imgData, 'PNG', x, y, w, h, undefined, 'FAST');
  const blob = pdf.output('blob');
  const fileName = `Ticket_${(obj.id||'SIN-ID').replace(/[^A-Za-z0-9_-]/g,'')}.pdf`;
  return { blob, fileName };
}
async function shareTicketPDF(obj) {
  try {
    const { blob, fileName } = await generateTicketPDF(obj);
    const file = new File([blob], fileName, { type:'application/pdf' });
    if (navigator.canShare && navigator.canShare({ files:[file] })) {
      await navigator.share({ files:[file], title:`Reservación ${obj.id}`, text:`Ticket de reservación ${obj.id}` });
      return;
    }
    const url=URL.createObjectURL(blob); const a=document.createElement('a'); a.href=url; a.download=fileName; document.body.appendChild(a); a.click(); a.remove(); URL.revokeObjectURL(url);
    alert('Se descargó el PDF para adjuntarlo en WhatsApp o correo.');
  } catch(e){ console.error(e); alert('No se pudo generar el PDF. Revisa la consola.'); }
}

/* Botones */
$('#btnPrint')?.addEventListener('click', async ()=>{
  let o = liveObj();
  if (!o.id || o.id === 'SIN-ID') o.id = nextId();
  buildTicket(o);
  await shareTicketPDF(o);
});
$('#saveDraft')?.addEventListener('click',()=>{ calc(); const d=collect('Borrador'); persist(d); buildTicket(d); prepareShare(d); alert(`Borrador guardado (#${d.id}).`); });
$('#saveAll')?.addEventListener('click',()=>{
  calc();
  if(!$('#vehiculoSel').value){ alert('Selecciona un vehículo.'); return; }
  if(!$('#fIniBox').textContent.trim() || !$('#fFinBox').textContent.trim()){ alert('Indica fechas.'); return; }
  const ok=$('#cNombre').value.trim() && $('#cApellidos').value.trim() && $('#cEmail').value.trim();
  if(!ok && !confirm('Faltan datos del solicitante. ¿Guardar como Borrador?')) return;
  const d=collect(ok?'Confirmada':'Borrador'); persist(d); buildTicket(d); prepareShare(d);
  if(confirm(`Reservación ${d.status.toLowerCase()} (#${d.id}). ¿Ver ticket?`)){ document.getElementById('ticket').style.display='block'; window.scrollTo(0,document.body.scrollHeight); }
});
