/* ===== helpers ===== */
const $ = s => document.querySelector(s);
const $$ = (s,el=document)=>Array.from(el.querySelectorAll(s));
const Fmx = v => '$'+Number(v||0).toLocaleString('es-MX',{minimumFractionDigits:2,maximumFractionDigits:2})+' MXN';
const nonEmpty = v => v!==undefined && v!==null && v!=='' && !(typeof v==='number' && isNaN(v));

/* nombre/cliente tolerante */
function fmtCliente(c){
  if(!c) return '—';
  if(typeof c==='string') return c;
  const nombre = [c.nombre,c.apellidos,c.fullname,c.fullName,c.name].filter(Boolean).join(' ').trim();
  return nombre || '—';
}
/* convierte objetos a texto para extraer teléfono */
function asText(v){ try{ return (typeof v==='object') ? JSON.stringify(v) : String(v??''); }catch{ return String(v??''); } }
function extractPhoneFromText(t){
  const s = asText(t);
  const m = s.match(/(\+?\d[\d\-\s\(\)]{6,}\d)/); /* patrón flexible */
  return m ? m[1].replace(/\s+/g,' ').trim() : '';
}
function fmtTel(r){
  const c = r.cliente||{};
  return r.telefono || r.phone ||
         c.telefono || c.phone || c.tel ||
         extractPhoneFromText(r.contacto) ||
         '—';
}
function fmtCat(r){
  if(nonEmpty(r.categoria)) return r.categoria;
  const s = r.vehiculo_req || r.vehiculo || '';
  const p = s.split('|')[0]?.trim();
  return p || '—';
}
function fmtFolio(r){ return r.folio || r.id || '—'; }
function stringifyAdds(adds){
  if(!adds || typeof adds!=='object') return '—';
  const map={seat:'Asiento bebé', gps:'GPS', driver:'Conductor adicional', young:'Conductor joven', expired:'Licencia expirada', phone:'Línea telefónica'};
  const out = [];
  for(const k of Object.keys(adds)){
    const v = adds[k];
    if(!v) continue;
    const label = map[k] || k.toUpperCase();
    out.push(typeof v==='number' && v>1 ? `${label} (x${v})` : label);
  }
  return out.length ? out.join(' · ') : '—';
}
function statusDot(r){
  const st = (r.status||'').toLowerCase();
  if(st.includes('final')) return 'ok';
  if(st.includes('cancel')) return 'err';
  if(st.includes('reser')) return 'warn';
  return 'info';
}

/* ===== dataset (solo lectura) ===== */
let all = JSON.parse(localStorage.getItem('vc_reservas')||'[]');

/* demo si no hay nada */
if(!all.length){
  const names=['ARIEL GUERRERO','RAUL FERNANDEZ RAMIREZ','GENARO HERNÁNDEZ CORTES','ORESTSY OCTAVIO ORTIZ OVANDO','CLIENTE RODÓ'];
  for(let i=1;i<=7;i++){
    const dias=[1,2,3,6,8,12,21][Math.floor(Math.random()*7)];
    all.push({
      id:'RES-20250923-00'+i,
      folio:'R-20250923-00'+i,
      fechaCheckout:'-',
      hora:'-',
      dias:'-',
      categoria:['C','D','E'][Math.floor(Math.random()*3)],
      cliente:names[Math.floor(Math.random()*names.length)],
      telefono:'—',
      status:['Reservada','En contrato','Finalizada'][Math.floor(Math.random()*3)],
      confirmado_en:'-',
      contacto:{country:'MX', phone:'+52 414 581 0406'},
      entrega:'Aeropuerto de Querétaro (AIQ)',
      devolucion:'Aeropuerto de Querétaro (AIQ)',
      vehiculo_req:'C | COMPACTO AUTOMÁTICO · CHEVROLET Aveo o similar',
      totalsMXN:{total:6500.64},
      prot:{code:'LI (0)'},
      adds:{seat:0,gps:0,driver:0,young:0,expired:0,phone:0}
    });
  }
}

/* ===== state ===== */
let state={ q:'', sort:{k:'folio',dir:'asc'}, page:1, pp:25 };

/* ===== core ===== */
function apply(){
  const q = state.q.toLowerCase();
  let rows = all.filter(r=>{
    const hay = [
      fmtFolio(r), r.hora, r.dias, fmtCat(r),
      fmtCliente(r.cliente), fmtTel(r), r.vehiculo_req||r.vehiculo||''
    ].join(' ').toLowerCase();
    return !q || hay.includes(q);
  });
  const {k,dir} = state.sort;
  rows.sort((a,b)=>{
    const av = (k==='cliente') ? fmtCliente(a.cliente) : (k==='telefono') ? fmtTel(a) : (k==='categoria') ? fmtCat(a) : (a[k]||'');
    const bv = (k==='cliente') ? fmtCliente(b.cliente) : (k==='telefono') ? fmtTel(b) : (k==='categoria') ? fmtCat(b) : (b[k]||'');
    return av==bv ? 0 : (av>bv ? (dir==='asc'?1:-1) : (dir==='asc'?-1:1));
  });
  return rows;
}

function detailsBox(r){
  return `
  <div class="details">
    <div class="kv"><div class="k">Estado</div><div><span class="tag">${r.status||'—'}</span></div></div>
    <div class="kv"><div class="k">Reservación confirmada el</div><div>${r.confirmado_en||'—'}</div></div>
    <div class="kv"><div class="k">Datos de contacto</div><div>${asText(r.contacto)||'—'}</div></div>
    <div class="kv"><div class="k">Entrega</div><div>${r.entrega||'—'}</div></div>
    <div class="kv"><div class="k">Devolución</div><div>${r.devolucion||'—'}</div></div>
    <div class="kv"><div class="k">Vehículo requerido</div><div>${r.vehiculo_req||r.vehiculo||'—'}</div></div>
    <div class="kv"><div class="k">Total (MXN)</div><div><b>${Fmx(r.totalsMXN?.total||0)}</b> <span class="badge">Forma de pago: Oficina</span></div></div>
    <div class="kv"><div class="k">Adicionales</div><div>${stringifyAdds(r.adds)}</div></div>
  </div>`;
}

function render(){
  const rows = apply();
  const tb = $('#tbody'); tb.innerHTML='';
  const pp = state.pp|0; const pages = Math.max(1, Math.ceil(rows.length/pp));
  if(state.page>pages) state.page=pages; const ini=(state.page-1)*pp; const fin=Math.min(ini+pp, rows.length);
  $('#range').textContent = `${rows.length?ini+1:0}–${fin} de ${rows.length}`;

  if(!rows.length){
    tb.innerHTML='<tr><td colspan="8" style="text-align:center;color:#667085;background:#fff;border:1px solid var(--stroke);border-radius:12px;padding:12px">Sin resultados</td></tr>';
    return;
  }

  rows.slice(ini,fin).forEach(r=>{
    const id = fmtFolio(r);
    const tr = document.createElement('tr');
    tr.className = 'data-row';
    tr.innerHTML = `
      <td class="cell-flex cell-toggle">
        <button class="toggle" aria-label="expandir">+</button>
        <span class="dot ${statusDot(r)}"></span>
      </td>
      <td>${id}</td>
      <td class="c-fecha">${r.fechaCheckout||'-'}</td>
      <td>${r.hora||'-'}</td>
      <td class="c-dias">${r.dias||'-'}</td>
      <td class="c-categoria">${fmtCat(r)}</td>
      <td>${fmtCliente(r.cliente)}</td>
      <td>${fmtTel(r)}</td>`;
    tb.appendChild(tr);

    const trd = document.createElement('tr'); trd.className='details-row';
    trd.innerHTML = `<td colspan="8">${detailsBox(r)}</td>`;
    trd.style.display='none';
    tb.appendChild(trd);
  });
}

/* ===== eventos ===== */
$('#q').addEventListener('input', e=>{ state.q=e.target.value; state.page=1; render(); });
$('#pp').addEventListener('change', e=>{ state.pp=+e.target.value; state.page=1; render(); });
$('#prev').onclick = ()=>{ if(state.page>1){ state.page--; render(); } };
$('#next').onclick = ()=>{ state.page++; render(); };

/* sort */
$$('th[data-k"]').forEach(th=>{
  th.addEventListener('click', ()=>{
    const k=th.dataset.k;
    state.sort = (state.sort.k===k) ? {k,dir:(state.sort.dir==='asc'?'desc':'asc')} : {k,dir:'asc'};
    render();
  });
});

/* Abrir/cerrar detalles
   — Funciona al clic en el botón +, la primera celda o cualquier parte de la fila */
$('#tbody').addEventListener('click', e=>{
  const inToggleCell = e.target.closest('.cell-toggle, .toggle, tr.data-row');
  if(!inToggleCell) return;
  const tr = e.target.closest('tr.data-row');
  if(!tr) return;
  const detail = tr.nextElementSibling;
  if(!detail || !detail.classList.contains('details-row')) return;
  const open = detail.style.display !== 'none';
  detail.style.display = open ? 'none' : 'table-row';
  const btn = tr.querySelector('.toggle'); if(btn) btn.textContent = open ? '+' : '–';
});

/* ===== init ===== */
render();
