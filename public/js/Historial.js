/* ===== util ===== */
const $ = s => document.querySelector(s);
const qs = (s, c=document) => Array.from(c.querySelectorAll(s));
const Fmx = v => '$' + Number(v||0).toLocaleString('es-MX',{minimumFractionDigits:2,maximumFractionDigits:2}) + ' MXN';
const toISO = d => !d ? '' : new Date(d).toISOString().slice(0,10);
const inRange = (d,ini,fin)=>{ if(!ini&&!fin) return true; const x=new Date(d).setHours(0,0,0,0); if(ini&&x<new Date(ini).setHours(0,0,0,0)) return false; if(fin&&x>new Date(fin).setHours(0,0,0,0)) return false; return true; };
const initials = (name='') => name.trim().split(/\s+/).slice(0,2).map(s=>s[0]||'').join('').toUpperCase() || '—';

/* ===== base de contrato desde HTML (route en href) ===== */
const CONTRATO_BASE = document.getElementById('tbody')?.dataset?.contratoBase || '/admin/contrato';

/* ===== datos (de tu app) ===== */
let raw = JSON.parse(localStorage.getItem('vc_reservas')||'[]');

// Si no hay nada, genero dummy para demo
if(!raw.length){
  const cats=['Económico','Compacto','Intermedio','SUV'];
  const marcas=['NISSAN®','CHEVROLET®','KIA®','VOLKSWAGEN®'];
  const suc=['Centro','Aeropuerto','Bernardo Quintana'];
  const sts=['Reservada','En contrato','En curso','Finalizada','Cancelada','No show'];
  for(let i=1;i<=40;i++){
    const d1 = new Date(Date.now()-Math.random()*60*86400000);
    const d2 = new Date(d1.getTime()+ (1+Math.floor(Math.random()*10))*86400000);
    const days = Math.max(1, Math.round((d2-d1)/86400000));
    const pd = [280,320,388,450][Math.floor(Math.random()*4)];
    const subtotal = days*pd + Math.floor(Math.random()*1200);
    const iva = +(subtotal*0.16).toFixed(2);
    const total = +(subtotal+iva).toFixed(2);
    const pagado = +(Math.random()*total).toFixed(2);
    raw.push({
      id:'R-'+String(i).padStart(4,'0'),
      fIni:toISO(d1), fFin:toISO(d2),
      cliente:{nombre:['Mario','Andrea','Luis','Paola','Brenda','Raúl'][Math.floor(Math.random()*6)],
               apellidos:['López','Hernández','García','Martínez'][Math.floor(Math.random()*4)]},
      vehiculo:`${cats[Math.floor(Math.random()*cats.length)]} · ${marcas[Math.floor(Math.random()*marcas.length)]} Sentra`,
      sedePick:suc[Math.floor(Math.random()*suc.length)],
      status:sts[Math.floor(Math.random()*sts.length)],
      totalsMXN:{ total }, pagos:[{monto:pagado}]
    });
  }
}

/* ===== Normalizador ===== */
function normalize(r, i){
  const folio = r.folio || r.id || ('R-'+String(i+1).padStart(4,'0'));
  const fecha = r.fecha || r.fechaContrato || r.fFin || r.fIni || '';

  let cliente = r.cliente;
  if(typeof cliente === 'object' && cliente){
    const nom = (cliente.nombre||'').toString().trim();
    const ape = (cliente.apellidos||'').toString().trim();
    cliente = (nom+' '+ape).trim();
  }
  if(typeof cliente !== 'string') cliente = (r.nombre || '').toString();

  const vehiculo = r.vehiculo || r.auto || r.categoria || '';

  let dias = r.dias;
  if(!dias && r.fIni && r.fFin){
    const A = new Date(r.fIni), B = new Date(r.fFin);
    const diff = Math.ceil((B-A)/86400000); dias = Math.max(1, diff);
  }
  if(!dias) dias = '-';

  const sucursal = r.sucursal || r.sede || r.sedePick || '-';
  const status = r.status || r.estatus || 'Reservada';

  const total = Number(r?.totalsMXN?.total ?? r.total ?? 0);
  const pagado = Number((r.pagos||[]).reduce((a,p)=>a+Number(p.monto||0),0) ?? r.pagado ?? 0);
  const saldo = +(total - pagado).toFixed(2);

  return { folio, fecha, cliente, vehiculo, dias, sucursal, status, total, pagado, saldo };
}

// Crea arreglo uniforme
let all = raw.map(normalize);

/* ===== estado UI ===== */
let state={ q:'', ini:'', fin:'', status:'', pago:'', sucursal:'', vehiculo:'', sort:{k:'fecha',dir:'desc'}, page:1, pp:20 };

/* ===== filtros + sort ===== */
const inPagoBucket = (saldo, bucket) =>
  !bucket || (bucket==='Pagada' && saldo<=0) || (bucket==='Pendiente' && saldo>0) || (bucket==='Saldo a favor' && saldo<0);

function apply(){
  const {q,ini,fin,status,pago,sucursal,vehiculo} = state;
  let rows = all.filter(r=>{
    const matchQ = !q || (r.folio+' '+r.cliente+' '+r.vehiculo).toLowerCase().includes(q.toLowerCase());
    const matchDate = !r.fecha || inRange(r.fecha, ini, fin);
    const matchSt = !status || r.status===status;
    const matchPago = inPagoBucket(r.saldo, pago);
    const matchSucursal = !sucursal || r.sucursal===sucursal;
    const matchVeh = !vehiculo || (r.vehiculo||'').toLowerCase().includes(vehiculo.toLowerCase());
    return matchQ && matchDate && matchSt && matchPago && matchSucursal && matchVeh;
  });

  const {k,dir} = state.sort;
  rows.sort((a,b)=>{
    const pick = (row)=> (k==='total'?row.total : k==='pagado'?row.pagado : k==='saldo'?row.saldo : row[k]);
    const av = pick(a), bv = pick(b);
    if(av<bv) return dir==='asc'?-1:1;
    if(av>bv) return dir==='asc'?1:-1;
    return 0;
  });
  return rows;
}

function badgeClass(st){
  return st==='Finalizada' ? 'st-ok'
       : (st==='Cancelada'||st==='No show') ? 'st-err'
       : (st==='En curso') ? 'st-info'
       : 'st-warn';
}

/* ===== render ===== */
function render(){
  const rows = apply();

  // summary
  const count = rows.length;
  const total = rows.reduce((s,r)=>s+r.total,0);
  const pagado = rows.reduce((s,r)=>s+r.pagado,0);
  const saldo = +(total-pagado).toFixed(2);
  $('#sumCount').textContent = count;
  $('#sumTotal').textContent = Fmx(total);
  $('#sumPagado').textContent = Fmx(pagado);
  $('#sumSaldo').textContent = Fmx(saldo);

  // pagination
  const pp = state.pp; const pages = Math.max(1, Math.ceil(count/pp));
  if(state.page>pages) state.page=pages;
  const ini = (state.page-1)*pp; const fin = Math.min(ini+pp, count);
  $('#range').textContent = `${count?ini+1:0}–${fin} de ${count}`;

  // table
  const tb = $('#tbody'); tb.innerHTML='';
  if(!rows.length){
    tb.innerHTML = '<tr><td colspan="11" style="text-align:center;color:#667085">Sin resultados</td></tr>';
    return;
  }

  rows.slice(ini,fin).forEach(r=>{
    const tr = document.createElement('tr');
    tr.innerHTML = `
      <td><a class="link" href="${CONTRATO_BASE}?folio=${encodeURIComponent(r.folio)}">${r.folio}</a></td>
      <td>${r.fecha || '-'}</td>
      <td>
        <div class="client">
          <span class="avatar">${initials(r.cliente)}</span>
          <span>${r.cliente || '-'}</span>
        </div>
      </td>
      <td>${r.vehiculo || '-'}</td>
      <td>${r.dias}</td>
      <td>${r.sucursal}</td>
      <td><span class="status ${badgeClass(r.status)}">${r.status}</span></td>
      <td>${Fmx(r.total)}</td>
      <td>${Fmx(r.pagado)}</td>
      <td>${Fmx(r.saldo)}</td>
      <td>
        <a class="btn gray" data-ver href="${CONTRATO_BASE}?folio=${encodeURIComponent(r.folio)}">Ver</a>
      </td>`;
    tb.appendChild(tr);
  });
}

/* ===== eventos ===== */
$('#btnFiltrar').onclick = ()=>{
  state.q=$('#q').value.trim(); state.ini=$('#fini').value; state.fin=$('#ffin').value;
  state.status=$('#fstatus').value; state.pago=$('#fpago').value;
  state.sucursal=$('#fsucursal').value; state.vehiculo=$('#fvehiculo').value;
  state.page=1; render();
};
$('#btnClear').onclick = ()=>{
  ['q','fini','ffin','fstatus','fpago','fsucursal','fvehiculo'].forEach(id=>{$('#'+id).value='';});
  state={...state,q:'',ini:'',fin:'',status:'',pago:'',sucursal:'',vehiculo:'',page:1}; render();
};
$('#pp').onchange = e=>{ state.pp=+e.target.value; state.page=1; render(); };
$('#prev').onclick = ()=>{ if(state.page>1){ state.page--; render(); } };
$('#next').onclick = ()=>{ state.page++; render(); };

// sort headers
qs('th[data-k]', $('#tbl thead')).forEach(th=>{
  th.addEventListener('click', ()=>{
    const k=th.dataset.k;
    if(state.sort.k===k){ state.sort.dir = state.sort.dir==='asc'?'desc':'asc'; }
    else { state.sort={k,dir:'asc'}; }
    render();
  });
});

// export CSV
function toCSV(rows){
  const header = ['Folio','Fecha','Cliente','Vehículo','Días','Sucursal','Estatus','Total','Pagado','Saldo'];
  const lines = [header.join(',')];
  rows.forEach(r=>{
    lines.push([r.folio,r.fecha||'',r.cliente||'',r.vehiculo||'',r.dias||'',r.sucursal||'',r.status,r.total,r.pagado,r.saldo]
      .map(v=>`"${String(v??'').replace(/"/g,'""')}"`).join(','));
  });
  return lines.join('\n');
}
$('#btnExport').onclick = ()=>{
  const rows = apply();
  const blob = new Blob([toCSV(rows)], {type:'text/csv;charset=utf-8;'});
  const a=document.createElement('a'); a.href=URL.createObjectURL(blob); a.download='historial_rentas.csv'; a.click();
};

// imprimir
$('#btnPrint').onclick = ()=>{
  const keep = document.body.innerHTML;
  const title = '<h2 style="font-family:Cabin,Arial">Historial de Rentas</h2>';
  const tbl = $('#tbl').outerHTML;
  document.body.innerHTML = title + tbl;
  window.print();
  location.reload();
};

// (No se requiere listener especial: el <a data-ver> ya trae href correcto)

/* init dates (últimos 90 días) */
const finD = new Date(); const iniD = new Date(Date.now()-90*86400000);
$('#fini').value=toISO(iniD); $('#ffin').value=toISO(finD);
state.ini=$('#fini').value; state.fin=$('#ffin').value;

/* primer render */
render();
