/* ===== Helpers ===== */
const $=s=>document.querySelector(s), $$=s=>Array.from(document.querySelectorAll(s));
const Fmx=v=>'$'+Number(v||0).toLocaleString('es-MX',{minimumFractionDigits:2,maximumFractionDigits:2})+' MXN';
const toast=msg=>{const t=$('#toast');t.textContent=msg;t.classList.add('show');setTimeout(()=>t.classList.remove('show'),1600);};

/* ===== Data (localStorage) ===== */
const KEY='vc_reservas';
function seedIfEmpty(){
  if(localStorage.getItem(KEY)) return;
  localStorage.setItem(KEY, JSON.stringify([
    {
      id:'MX-0E33B8', status:'Pendiente',
      fIni:'2025-09-09', hIni:'08:00', fFin:'2025-09-11', hFin:'11:00',
      sedePick:'Aeropuerto de Querétaro (AIQ)', sedeDrop:'Aeropuerto de Querétaro (AIQ)',
      totalsMXN:{subtotal:6000, iva:960, total:6960},
      vehiculo:'C | COMPACTO AUTOMÁTICO · CHEVROLET® Aveo',
      prot:{code:'LI',price:0}, adds:{gps:1,expired:1}, addCatalog:[{id:'gps',head:'Servicio / GPS'},{id:'expired',head:'Licencia vencida'}],
      canal:'Web',
      cliente:{nombre:'MARIO', apellidos:'BERNAL', email:'mario@gmail.com', tel:'+52 442 000 0000', pais:'MÉXICO'}
    },
    {
      id:'MX-8C0EA9', status:'Pendiente',
      fIni:'2025-09-10', hIni:'13:00', fFin:'2025-09-22', hFin:'11:00',
      sedePick:'AIQ', sedeDrop:'AIQ',
      totalsMXN:{subtotal:11200, iva:1792, total:12992},
      vehiculo:'D | INTERMEDIO VOLKSWAGEN® Virtus o similar',
      prot:{code:'LI',price:0}, adds:{}, addCatalog:[],
      canal:'Web',
      cliente:{nombre:'RAUL', apellidos:'FERNANDEZ RAMIREZ', email:'raul@example.com', tel:'4427169793', pais:'MÉXICO'}
    }
  ]));
}
seedIfEmpty();

/* ===== Estado de UI ===== */
const state={page:1,size:10,search:''};

/* ===== Render ===== */
function statusBadge(s){
  s=(s||'').toLowerCase();
  if(s.includes('conf')) return '<span class="badge st-ok">CONFIRMADA</span>';
  if(s.includes('cancel')) return '<span class="badge st-cancel">CANCELADA</span>';
  return '<span class="badge st-pend">PENDIENTE</span>';
}

function row(r, idx){
  const tel = r.cliente?.tel ? String(r.cliente.tel) : '';
  const pais = r.cliente?.pais || '—';
  const addsText = Object.entries(r.adds||{})
    .filter(([k,v])=>v>0)
    .map(([k,v])=>{
      const cat=(r.addCatalog||[]).find(a=>a.id===k);
      return `${cat?cat.head:k}${v>1?' ×'+v:''}`;
    }).join(', ') || '—';
  return `
  <tr data-i="${idx}">
    <td><button class="tgl" aria-label="ver detalles">+</button></td>
    <td>${r.id}</td>
    <td>${r.fFin||'—'}</td>
    <td>${r.hFin||'—'}</td>
    <td>${(r.cliente?.nombre||'').toUpperCase()}</td>
    <td>${(r.cliente?.apellidos||'')}</td>
    <td>${r.cliente?.email||''}</td>
    <td>${statusBadge(r.status)}</td>
    <td class="col-actions"></td>
  </tr>
  <tr class="detail"><td></td><td colspan="8">
    <div class="card">
      <div class="card-hd">
        <div class="card-title">Reserva <span class="pill">🔑 ${r.id}</span></div>
        <div class="card-meta">${statusBadge(r.status)} <span class="pill">🧾 ${r.canal||'Canal'}</span></div>
      </div>

      <div class="card-bd">
        <!-- Bloque 1: Contacto + Vehículo + Adicionales -->
        <div class="block">
          <div class="kv">
            <div class="k">👤 Contacto</div>
            <div class="v">${pais}${tel?` · <b>📞 ${tel}</b>`:''}</div>
          </div>
          <div class="kv">
            <div class="k">🚗 Vehículo</div>
            <div class="v">${r.vehiculo||'—'}</div>
          </div>
          <div class="kv">
            <div class="k">➕ Adicionales</div>
            <div class="v">${addsText}</div>
          </div>
        </div>

        <!-- Bloque 2: Timeline entrega / devolución -->
        <div class="block">
          <div class="timeline">
            <div class="tl-item">
              <div class="tl-dot"></div>
              <div class="tl-body">
                <div class="tl-title">Entrega</div>
                <div class="tl-sub">${r.sedePick||'—'}</div>
                <div class="v">${r.fIni||'—'} · ${r.hIni||'—'} HRS</div>
              </div>
            </div>
            <div class="tl-item">
              <div class="tl-dot" style="background:#0EA5E9"></div>
              <div class="tl-body">
                <div class="tl-title">Devolución</div>
                <div class="tl-sub">${r.sedeDrop||'—'}</div>
                <div class="v">${r.fFin||'—'} · ${r.hFin||'—'} HRS</div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <div class="card-ft">
        <div class="total">
          <span>💳 Total:</span>
          <span>${Fmx(r?.totalsMXN?.total||0)}</span>
          <small>· FORMA DE PAGO (OFICINA)</small>
        </div>
        <div style="display:flex;gap:10px;flex-wrap:wrap">
          <button class="btn b-primary" data-act="confirm">✔ Confirmar</button>
          <button class="btn b-red" data-act="cancel">✖ Cancelar</button>
          <button class="btn b-gray" data-act="resend">📧 Reenviar correo</button>
        </div>
      </div>
    </div>
  </td></tr>`;
}

function getFiltered(){
  const all = JSON.parse(localStorage.getItem(KEY)||'[]');
  if(!state.search) return all;
  const t = state.search.trim().toLowerCase();
  return all.filter(r=>(
    [r.id,r.status,r.cliente?.nombre,r.cliente?.apellidos,r.cliente?.email]
      .filter(Boolean).some(v=>String(v).toLowerCase().includes(t))
  ));
}

function render(){
  const data=getFiltered();
  const totalPages=Math.max(1,Math.ceil(data.length/state.size));
  state.page=Math.min(state.page,totalPages);

  const start=(state.page-1)*state.size;
  const items=data.slice(start,start+state.size);

  const tb=$('#tbody'); tb.innerHTML='';
  items.forEach((r,i)=>tb.insertAdjacentHTML('beforeend',row(r,start+i)));

  // toggles
  $$('#tbody .tgl').forEach(btn=>{
    const tr=btn.closest('tr'); const det=tr.nextElementSibling;
    det.style.display='none';
    btn.onclick=()=>{ const open=btn.classList.toggle('open'); btn.textContent=open?'–':'+'; det.style.display=open?'table-row':'none'; };
  });

  // acciones (dentro del detalle)
  $('#tbody').onclick=(e)=>{
    const act=e.target?.dataset?.act; if(!act) return;
    const tr=e.target.closest('tr').previousElementSibling; // fila principal
    const idx=Number(tr.dataset.i);
    const all=JSON.parse(localStorage.getItem(KEY)||'[]');
    const item=all[idx]; if(!item) return;

    if(act==='confirm'){
      item.status='Confirmada';
      localStorage.setItem(KEY, JSON.stringify(all));
      toast('Reservación confirmada'); render();
    }
    if(act==='cancel'){
      if(confirm('¿Cancelar esta reservación?')){
        item.status='Cancelada';
        localStorage.setItem(KEY, JSON.stringify(all));
        toast('Reservación cancelada'); render();
      }
    }
    if(act==='resend'){
      const to = encodeURIComponent(item.cliente?.email||'');
      const subject = encodeURIComponent(`Reenvío de Reservación ${item.id}`);
      const body = encodeURIComponent(
        `Hola ${item.cliente?.nombre||''},\n\n`+
        `Detalles de tu reservación:\n`+
        `Clave: ${item.id}\n`+
        `Entrega: ${item.sedePick||''} · ${item.fIni||''} ${item.hIni||''}\n`+
        `Devolución: ${item.sedeDrop||''} · ${item.fFin||''} ${item.hFin||''}\n`+
        `Vehículo: ${item.vehiculo||''}\n`+
        `Total: ${Fmx(item?.totalsMXN?.total||0)}\n\n`+
        `Gracias por elegir Viajero.`
      );
      window.location.href=`mailto:${to}?subject=${subject}&body=${body}`;
    }
  };

  // pager
  $('#pgInfo').textContent=`Página ${state.page} de ${totalPages} · ${data.length} registro(s)`;
  $('#prev').disabled=state.page<=1;
  $('#next').disabled=state.page>=totalPages;
}

$('#selSize').onchange=e=>{state.size=Number(e.target.value||10);state.page=1;render();}
$('#txtSearch').oninput=e=>{state.search=e.target.value;state.page=1;render();}
$('#prev').onclick=()=>{state.page--;render();}
$('#next').onclick=()=>{state.page++;render();}

/* Init */
render();
