/* ===== util ===== */
const $=s=>document.querySelector(s); const $$=s=>Array.from(document.querySelectorAll(s));
const esc=s=>(s??'').toString().replace(/[&<>"]/g,m=>({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;'}[m]));
const fmtMXN=v=>'$'+Number(v||0).toLocaleString('es-MX',{minimumFractionDigits:2,maximumFractionDigits:2})+' MXN';
const fmtUSD=v=>'$'+Number(v||0).toFixed(2)+' USD';
const F=v=>($('#moneda').value==='USD'? fmtUSD(v/Number($('#tc').value||17)) : fmtMXN(v));

/* ===== seed mínima ===== */
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
      {id:'C-AVE0', clase:'C | COMPACTO AUTOMÁTICO', nombre:'CHEVROLET® Aveo', puertas:5, pax:5, transm:'AUTOMÁTICO', img:'../rentas/aveo.png', precioDia:1600},
      {id:'D-VIRT', clase:'D | INTERMEDIO', nombre:'VOLKSWAGEN® Virtus', puertas:5, pax:5, transm:'AUTOMÁTICO', img:'../assets/media/virtus.jpg', precioDia:2400},
      {id:'E-SENT', clase:'E | GRANDE', nombre:'NISSAN® Sentra', puertas:4, pax:5, transm:'AUTOMÁTICO', img:'../assets/media/sentra.jpg', precioDia:2650}
    ]));
  }
})();

/* ===== sedes ===== */
(function fillSedes(){
  const sedes=JSON.parse(localStorage.getItem('vc_sedes')||'[]');
  $('#sedePick').innerHTML=sedes.map(s=>`<option>${esc(s.name)}</option>`).join('');
  $('#sedeDrop').innerHTML=sedes.map(s=>`<option>${esc(s.name)}</option>`).join('');
  $('#sedeDrop').value=$('#sedePick').value;
})();

/* ===== pasos ===== */
const showStep=n=>{ $$('[data-step]').forEach(el=>el.style.display = (Number(el.dataset.step)===n?'block':'none')); };
$('#go2').onclick = ()=>{ showStep(2); };
$('#back1').onclick= ()=>{ showStep(1); };
$('#go3').onclick = ()=>{ showStep(3); buildTicket(liveObj()); prepareShare(liveObj()); };
$('#back2').onclick= ()=>{ showStep(2); };
showStep(1);

/* ===== date picker ===== */
const dp={anchor:null, sel:null, date:new Date()}; const dow=['Dom','Lun','Mar','Mié','Jue','Vie','Sáb'];
function openDP(anchor){dp.anchor=anchor;$('#dpPop').classList.add('show');buildDP();}
function buildDP(){ const d=new Date(dp.date.getFullYear(),dp.date.getMonth(),1); $('#dpMonth').textContent=d.toLocaleString('es-MX',{month:'long',year:'numeric'}).replace(/^./,c=>c.toUpperCase()); const g=$('#dpGrid'); g.innerHTML=''; dow.forEach(n=>g.insertAdjacentHTML('beforeend',`<div class="dow">${n}</div>`)); for(let i=0;i<((d.getDay()+7)%7);i++) g.insertAdjacentHTML('beforeend','<div></div>'); const last=new Date(d.getFullYear(),d.getMonth()+1,0).getDate(); for(let day=1;day<=last;day++){ const el=document.createElement('div'); el.className='cell'; el.textContent=day; el.onclick=()=>{ dp.sel=new Date(d.getFullYear(),d.getMonth(),day); $$('.cell.sel').forEach(x=>x.classList.remove('sel')); el.classList.add('sel'); }; g.appendChild(el);} }
$('#dpPrev').onclick=()=>{dp.date.setMonth(dp.date.getMonth()-1);buildDP();}
$('#dpNext').onclick=()=>{dp.date.setMonth(dp.date.getMonth()+1);buildDP();}
$('#dpToday').onclick=()=>{dp.sel=new Date();dp.date=new Date();buildDP();}
$('#dpClear').onclick=()=>{dp.sel=null;}
$('#dpApply').onclick=()=>{ if(dp.sel){ dp.anchor.textContent=dp.sel.toISOString().slice(0,10); calc(); } $('#dpPop').classList.remove('show'); };
$('#dpPop').addEventListener('click',e=>{ if(e.target.id==='dpPop') $('#dpPop').classList.remove('show'); });
$$('[data-dp]').forEach(el=>el.addEventListener('click',()=>openDP(el)));

/* ===== time picker ===== */
let tpAnchor=null; function openTP(anchor){ tpAnchor=anchor; const g=$('#tpGrid'); g.innerHTML=''; for(let h=0;h<24;h++){ for(let m=0;m<60;m+=30){ const hh=(''+h).padStart(2,'0'), mm=(''+m).padStart(2,'0'); const el=document.createElement('div'); el.className='tcell'; el.textContent=`${hh}:${mm}`; el.onclick=()=>{ tpAnchor.textContent=`${hh}:${mm}`; $('#tpPop').classList.remove('show'); calc(); }; g.appendChild(el); } } $('#tpPop').classList.add('show'); }
$('#tpClose').onclick=()=>$('#tpPop').classList.remove('show');
$('#tpPop').addEventListener('click',e=>{ if(e.target.id==='tpPop') $('#tpPop').classList.remove('show'); });
$$('[data-tp]').forEach(el=>el.addEventListener('click',()=>openTP(el)));

/* ===== vehículos ===== */
function renderVeh(){ const list=JSON.parse(localStorage.getItem('vc_autos')||'[]'); const c=$('#vehList'); c.innerHTML=''; list.forEach(a=>{ c.insertAdjacentHTML('beforeend',`
  <div class="vitem">
    <img src="${esc(a.img)}" alt="vehículo">
    <div>
      <div style="font-weight:800">${esc(a.clase)}</div>
      <div class="small">${esc(a.nombre)} o similar</div>
      <div class="small" style="margin-top:6px">🚪 ${a.puertas} · 👥 ${a.pax} · ⚙️ ${a.transm}</div>
    </div>
    <div style="text-align:right">
      <div class="pill">$${a.precioDia.toLocaleString()} MXN</div>
      <div style="margin-top:10px"><button class="btn primary" data-pick="${a.id}">Seleccionar</button></div>
    </div>
  </div>`); }); }
$('#btnVeh').onclick=()=>{ renderVeh(); $('#vehPop').classList.add('show'); };
$('#vehClose').onclick=()=>$('#vehPop').classList.remove('show');
$('#vehPop').addEventListener('click',e=>{ if(e.target.id==='vehPop') $('#vehPop').classList.remove('show'); });
$('#vehList').addEventListener('click',e=>{
  const id=e.target.dataset.pick; if(!id) return;
  const a=(JSON.parse(localStorage.getItem('vc_autos'))||[]).find(x=>x.id===id); if(!a) return;
  $('#vehiculoSel').value=`${a.clase} · ${a.nombre}`; $('#precioDia').value=a.precioDia; calc(); $('#vehPop').classList.remove('show');
});

/* ===== adicionales ===== */
const addCatalog=[
  {id:'seat',   head:'Silla para niño',         icon:'🧒', desc:'18–36 kg',                 price:150, per:'day',  max:3},
  {id:'upgrade',head:'Upgrade',                  icon:'⬆️', desc:'Mejora de categoría',     price:200, per:'day',  max:1},
  {id:'gps',    head:'Servicio / GPS',          icon:'📍', desc:'Cobertura estatal',       price:200, per:'day',  max:1},
  {id:'driver+',head:'Conductor Adicional',     icon:'🧑‍✈️',desc:'En la cotización',        price:150, per:'day',  max:2},
  {id:'young',  head:'Conductor Menor de edad', icon:'🙂', desc:'Con permiso',             price:200, per:'day',  max:1},
  {id:'expired',head:'Licencia vencida',        icon:'🪪', desc:'Verificación manual',     price:350, per:'rent', max:1},
  {id:'phone',  head:'Accesorios celular',      icon:'📱', desc:'Cargador/manos libres',   price:100, per:'rent', max:2}
];
const addState=Object.fromEntries(addCatalog.map(a=>[a.id,0]));
function renderAdds(){
  const g=$('#addGrid'); g.innerHTML='';
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
$('#addGrid').addEventListener('click',e=>{
  const card=e.target.closest('.add'); if(!card) return;
  const id=card.dataset.id; const cat=addCatalog.find(x=>x.id===id);
  if(e.target.dataset.act==='plus'){ addState[id]=Math.min(cat.max,(addState[id]||0)+1); }
  if(e.target.dataset.act==='minus'){ addState[id]=Math.max(0,(addState[id]||0)-1); }
  card.querySelector('input').value=addState[id]; calc();
});

/* ===== cálculo ===== */
function parseDT(d,t){ if(!d||!t) return null; const [Y,M,Da]=d.split('-').map(Number); const [h,m]=t.split(':').map(Number); return new Date(Y,M-1,Da,h,m,0,0); }
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
    const total=(a.per==='day')? a.price*days*q : a.price*q; arr.push({name:a.head, per:a.per, price:a.price, qty:q, total});
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
  localStorage.setItem('vc_cot_tmp', JSON.stringify(tmp));
}
['precioDia','proteccion','moneda','tc'].forEach(id=>document.getElementById(id).addEventListener('input',calc));
$('#recalc').onclick=calc;

/* init fechas por defecto */
(function initDates(){
  const d=new Date(), fmt=x=>x.toISOString().slice(0,10), add=(x,n)=>{const y=new Date(x);y.setDate(y.getDate()+n);return y};
  $('#fIniBox').textContent=fmt(d); $('#hIniBox').textContent='08:00';
  $('#fFinBox').textContent=fmt(add(d,2)); $('#hFinBox').textContent='11:00';
  calc();
})();
(function loadTmp(){ try{
  const t=JSON.parse(localStorage.getItem('vc_cot_tmp')||'null'); if(!t) return;
  $('#sedePick').value=t.sedePick; $('#sedeDrop').value=t.sedeDrop;
  if(t.fIni) $('#fIniBox').textContent=t.fIni; if(t.fFin) $('#fFinBox').textContent=t.fFin;
  if(t.hIni) $('#hIniBox').textContent=t.hIni; if(t.hFin) $('#hFinBox').textContent=t.hFin;
  if(t.precioDia) $('#precioDia').value=t.precioDia;
  if(t.moneda) $('#moneda').value=t.moneda; if(t.tc) $('#tc').value=t.tc;
  if(t.adds){ Object.assign(addState,t.adds); addCatalog.forEach(a=>{const card=$(`#addGrid .add[data-id="${a.id}"]`); if(card) card.querySelector('input').value=addState[a.id]||0;}); }
  calc();
}catch{}})();

/* ===== persistencia (cotizaciones) ===== */
function nextId(){ const k='vc_cot_seq'; const n=(Number(localStorage.getItem(k)||'0')+1); localStorage.setItem(k,n);
  const d=new Date(); const y=d.getFullYear(), m=('0'+(d.getMonth()+1)).slice(-2), da=('0'+d.getDate()).slice(-2);
  return `COT-${y}${m}${da}-${('0000'+n).slice(-4)}`; }
function liveObj(){ const t=JSON.parse(localStorage.getItem('vc_cot_tmp')||'{}');
  const cli={nombre:$('#cNombre').value.trim(), apellidos:$('#cApellidos').value.trim(), email:$('#cEmail').value.trim(), tel:$('#cTel').value.trim(), pais:$('#cPais').value.trim(), vuelo:$('#cVuelo').value.trim(), sms:$('#smsOK').checked};
  return { id:'SIN-ID', createdAt:Date.now(), status:'Cotización', ...t, totalsMXN:{subtotal:t.subtotal||0,iva:t.iva||0,total:t.total||0}, cliente:cli };
}
function collect(status){ const o=liveObj(); o.id=nextId(); o.status=status; return o; }
function persist(obj){ const k='vc_cotizaciones'; const arr=JSON.parse(localStorage.getItem(k)||'[]'); arr.push(obj); localStorage.setItem(k,JSON.stringify(arr)); localStorage.removeItem('vc_cot_tmp'); }

/* ===== NUEVO: util ids y mover a reservaciones ===== */
function nextResId(){
  const k='vc_res_seq';
  const n=(Number(localStorage.getItem(k)||'0')+1);
  localStorage.setItem(k,n);
  const d=new Date();
  const y=d.getFullYear(), m=('0'+(d.getMonth()+1)).slice(-2), da=('0'+d.getDate()).slice(-2);
  return `RES-${y}${m}${da}-${('0000'+n).slice(-4)}`;
}
function removeQuoteById(id){
  const k='vc_cotizaciones';
  const arr=JSON.parse(localStorage.getItem(k)||'[]');
  const out=arr.filter(q=>q.id!==id);
  localStorage.setItem(k, JSON.stringify(out));
}
function moveQuoteToReservation(quote){
  const res = {
    id: nextResId(),
    createdAt: Date.now(),
    status: 'Reservada',
    fuente: 'Cotización',
    cotizacionId: quote.id,

    sedePick: quote.sedePick,
    sedeDrop: quote.sedeDrop,
    fIni: quote.fIni,
    fFin: quote.fFin,
    hIni: quote.hIni,
    hFin: quote.hFin,
    days: quote.days,

    vehiculo: quote.vehiculo,
    precioDia: quote.precioDia,
    prot: quote.prot || null,
    adds: quote.adds || {},
    addCatalog: quote.addCatalog || [],

    subtotal: quote.subtotal,
    iva: quote.iva,
    total: quote.total,
    moneda: quote.moneda,
    tc: quote.tc,
    totalsMXN: quote.totalsMXN || {subtotal:0,iva:0,total:0},

    cliente: quote.cliente || {}
  };
  const KR='vc_reservas';
  const resArr=JSON.parse(localStorage.getItem(KR)||'[]');
  resArr.push(res);
  localStorage.setItem(KR, JSON.stringify(resArr));

  removeQuoteById(quote.id);
  localStorage.removeItem('vc_cot_tmp');

  return res;
}

/* ===== ticket + compartir ===== */
function buildTicket(obj){
  $('#tId').textContent=obj.id;
  $('#tCliente').textContent=`${obj.cliente.nombre||'—'} ${obj.cliente.apellidos||''}`.trim();
  $('#tContacto').textContent=`${obj.cliente.email||'—'} · ${obj.cliente.tel||'—'}`;
  $('#tPick').textContent=`${obj.sedePick} · ${obj.fIni} ${obj.hIni}`;
  $('#tDrop').textContent=`${obj.sedeDrop} · ${obj.fFin} ${obj.hFin}`;
  $('#tVeh').textContent=obj.vehiculo || '—';
  const addTxt=Object.entries(obj.adds||{}).filter(([k,v])=>v>0).map(([k,v])=>{
    const cat=(obj.addCatalog||[]).find(a=>a.id===k); return `${cat?cat.head:k}${v>1?` ×${v}`:''}`;
  });
  const opt=[]; if(obj.prot && obj.prot.price>0) opt.push(`Protección ${obj.prot.code}`);
  $('#tOpts').textContent=[...opt,...addTxt].join(', ') || '—';
  $('#tTotal').textContent=fmtMXN(obj.totalsMXN.total);
}
function prepareShare(obj){
  const text =
    `Cotización ${obj.id}%0A`+
    `Cliente: ${encodeURIComponent($('#tCliente').textContent)}%0A`+
    `Pick Up: ${encodeURIComponent($('#tPick').textContent)}%0A`+
    `Devolución: ${encodeURIComponent($('#tDrop').textContent)}%0A`+
    `Vehículo: ${encodeURIComponent($('#tVeh').textContent)}%0A`+
    `Total: ${encodeURIComponent($('#tTotal').textContent)}%0A`+
    `Gracias por elegir Viajero.`;

  const email = (obj.cliente.email||'').trim();
  const raw   = (obj.cliente.tel||'').replace(/\D/g, '');
  let   phone = raw;
  if (raw.length === 10) phone = '52' + raw;

  $('#btnMail').href = `mailto:${encodeURIComponent(email)}?subject=${encodeURIComponent('Cotización '+obj.id)}&body=${text}`;
  $('#btnWa').href   = phone.length>=10? `https://wa.me/${phone}?text=${text}` : `https://wa.me/?text=${text}`;
  $('#btnSms').href  = phone.length>=10? `sms:+${phone}?&body=${text}` : `sms:?&body=${text}`;
}

async function generatePDFAndShare(obj){
  buildTicket(obj);
  document.getElementById('ticket').style.display='block';
  const card=document.getElementById('ticketCard');
  const canvas = await html2canvas(card,{scale:2});
  const imgData = canvas.toDataURL('imagePNG');
  const { jsPDF } = window.jspdf;
  const pdf = new jsPDF('p','pt',[card.offsetWidth, card.offsetHeight]);
  pdf.addImage(imgData,'PNG',0,0,card.offsetWidth,card.offsetHeight);
  const blob = pdf.output('blob');
  const file = new File([blob], `${obj.id}.pdf`, {type:'application/pdf'});
  try{
    if(navigator.share && navigator.canShare && navigator.canShare({files:[file]})){
      await navigator.share({title:`Cotización ${obj.id}`, text:`Viajero · Cotización ${obj.id}`, files:[file]});
    }else{
      const url = URL.createObjectURL(blob);
      const a=document.createElement('a'); a.href=url; a.download=`${obj.id}.pdf`; a.click();
      URL.revokeObjectURL(url);
      alert('PDF generado y descargado. Adjunta el archivo en WhatsApp/Correo/SMS.');
    }
  }catch(e){ console.warn(e); }
}

/* ===== botones existentes ===== */
$('#btnPrint').onclick=()=>{ const o=liveObj(); buildTicket(o); document.getElementById('ticket').style.display='block'; window.print(); };
$('#saveDraft').onclick=()=>{ calc(); const d=collect('Borrador'); persist(d); buildTicket(d); prepareShare(d); alert(`Borrador guardado (#${d.id}).`); };
$('#saveQuote').onclick=async ()=>{
  calc();
  if(!$('#vehiculoSel').value){ alert('Selecciona un vehículo.'); return; }
  if(!$('#fIniBox').textContent.trim() || !$('#fFinBox').textContent.trim()){ alert('Indica fechas.'); return; }
  const ok=$('#cNombre').value.trim() && $('#cApellidos').value.trim();
  if(!ok && !confirm('Faltan datos del solicitante. ¿Continuar de todos modos?')) return;
  const d=collect('Enviada'); persist(d); buildTicket(d); prepareShare(d); await generatePDFAndShare(d);
};

/* ===== NUEVOS handlers: Confirmar / Cancelar ===== */
$('#btnConfirm').onclick = ()=>{
  calc();
  if(!$('#vehiculoSel').value){ alert('Selecciona un vehículo.'); return; }
  if(!$('#fIniBox').textContent.trim() || !$('#fFinBox').textContent.trim()){ alert('Indica fechas.'); return; }
  const okCli = $('#cNombre').value.trim() && $('#cApellidos').value.trim();
  if(!okCli && !confirm('Faltan datos del solicitante. ¿Continuar de todos modos?')) return;

  const q = collect('Confirmada'); // generar ID de cotización
  persist(q);

  const res = moveQuoteToReservation(q);
  alert(`Reservación creada (#${res.id}) a partir de ${q.id}.`);
  location.href = 'reservaciones.html';
};

$('#btnCancel').onclick = ()=>{
  if(!confirm('¿Deseas descartar esta cotización? Esta acción no se puede deshacer.')) return;
  localStorage.removeItem('vc_cot_tmp');
  alert('Cotización descartada.');
  location.reload();
};

// inicial
buildTicket(liveObj()); prepareShare(liveObj());
