const $=s=>document.querySelector(s); const esc=s=>(s??'').toString().replace(/[&<>"]/g,m=>({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;'}[m]));
const QUOTES_KEY='vc_cotizaciones'; const GRACE_MS=3600000;
function parseDT(d,t){ if(!d||!t) return null; const [Y,M,Da]=d.split('-').map(Number); const [h,m]=t.split(':').map(Number); return new Date(Y,M-1,Da,h,m,0,0); }
function now(){ return new Date(); }
function fmtMXN(v){ return '$'+Number(v||0).toLocaleString('es-MX',{minimumFractionDigits:2,maximumFractionDigits:2})+' MXN'; }

function getQuotes(){ return JSON.parse(localStorage.getItem(QUOTES_KEY)||'[]'); }
function setQuotes(arr){ localStorage.setItem(QUOTES_KEY, JSON.stringify(arr)); }
function isExpired(q){ const dt=parseDT(q.fIni,q.hIni); if(!dt) return false; return (now()-dt)>GRACE_MS; }
function cleanup(silent=false){
  const arr=getQuotes(); const keep=arr.filter(q=>!isExpired(q));
  const removed=arr.length-keep.length; setQuotes(keep);
  if(!silent && removed>0) alert(`Se eliminaron ${removed} cotización(es) vencida(s).`);
}
function timeToExpire(q){
  const dt=parseDT(q.fIni,q.hIni); if(!dt) return '—';
  const ms=dt-now(); if(ms<=0) return 'Vencida';
  const h=Math.floor(ms/3600000); const m=Math.round((ms%3600000)/60000); return `${h}h ${String(m).padStart(2,'0')}m`;
}
function removeQuoteById(id){
  const arr=getQuotes().filter(q=>q.id!==id); setQuotes(arr);
}
function nextResId(){
  const k='vc_res_seq'; const n=(Number(localStorage.getItem(k)||'0')+1); localStorage.setItem(k,n);
  const d=new Date(); const y=d.getFullYear(), m=('0'+(d.getMonth()+1)).slice(-2), da=('0'+d.getDate()).slice(-2);
  return `RES-${y}${m}${da}-${('0000'+n).slice(-4)}`;
}
function moveQuoteToReservation(q){
  const KR='vc_reservas';
  const res={
    id:nextResId(), createdAt:Date.now(), status:'Reservada', fuente:'Cotización', cotizacionId:q.id,
    sedePick:q.sedePick, sedeDrop:q.sedeDrop, fIni:q.fIni, fFin:q.fFin, hIni:q.hIni, hFin:q.hFin, days:q.days,
    vehiculo:q.vehiculo, precioDia:q.precioDia, prot:q.prot||null, adds:q.adds||{}, addCatalog:q.addCatalog||[],
    subtotal:q.subtotal, iva:q.iva, total:q.total, moneda:q.moneda, tc:q.tc, totalsMXN:q.totalsMXN||{subtotal:0,iva:0,total:0},
    cliente:q.cliente||{}
  };
  const arr=JSON.parse(localStorage.getItem(KR)||'[]'); arr.push(res); localStorage.setItem(KR, JSON.stringify(arr));
  removeQuoteById(q.id);
  return res;
}

function render(){
  cleanup(true);
  const showAll = document.getElementById('showAll').checked;
  const arr = getQuotes().sort((a,b)=>(parseDT(a.fIni,a.hIni)||0)-(parseDT(b.fIni,b.hIni)||0));
  const list = arr.filter(q=> showAll ? true : !isExpired(q));
  document.getElementById('countVig').textContent = arr.filter(q=>!isExpired(q)).length;

  document.getElementById('tbody').innerHTML = list.map(q=>{
    const cliente = `${esc(q.cliente?.nombre||'—')} ${esc(q.cliente?.apellidos||'')}`.trim();
    const rango = `${esc(q.fIni||'—')} ${esc(q.hIni||'')} → ${esc(q.fFin||'—')} ${esc(q.hFin||'')}`;
    const total = fmtMXN(q.totalsMXN?.total ?? q.total ?? 0);
    const venc = isExpired(q) ? `<span class="tag red">Vencida</span>` : `<span class="tag green">${esc(timeToExpire(q))}</span>`;
    return `<tr data-id="${q.id}">
      <td><b>${esc(q.id)}</b></td>
      <td>${cliente}</td>
      <td class="small">${rango}</td>
      <td class="small">${esc(q.vehiculo||'—')}</td>
      <td><b>${total}</b></td>
      <td class="small">${esc(q.status||'Cotización')}</td>
      <td>${venc}</td>
      <td class="actions">
        <button class="btn gray" data-act="view">Ver / Continuar</button>
        <button class="btn primary" data-act="reserve">Pasar a reservación</button>
        <button class="btn" style="background:#fff;border:1px solid var(--stroke);color:#b42318" data-act="delete">Eliminar</button>
      </td>
    </tr>`;
  }).join('') || `<tr><td colspan="8" class="small">No hay cotizaciones guardadas.</td></tr>`;
}

document.getElementById('tbody').addEventListener('click', (e)=>{
  const btn = e.target.closest('button[data-act]'); if(!btn) return;
  const tr = e.target.closest('tr'); const id = tr?.dataset?.id; if(!id) return;
  const arr=getQuotes(); const q=arr.find(x=>x.id===id); if(!q) return;

  const act = btn.dataset.act;
  if(act==='view'){
    // precargar en tmp y abrir el flujo de cotizar
    localStorage.setItem('vc_cot_tmp', JSON.stringify(q));
    location.href='cotizar.html';
  }
  if(act==='reserve'){
    if(isExpired(q)){ alert('Esta cotización ya está vencida.'); return; }
    const res = moveQuoteToReservation(q);
    alert(`Reservación creada (#${res.id}) a partir de ${q.id}.`);
    render();
    location.href='reservaciones.html';
  }
  if(act==='delete'){
    if(confirm(`Eliminar ${q.id}?`)){ removeQuoteById(q.id); render(); }
  }
});

document.getElementById('cleanup').addEventListener('click', ()=>{ cleanup(false); render(); });
document.getElementById('showAll').addEventListener('change', render);

/* init */
render();
