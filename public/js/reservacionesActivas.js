/* ====== utils ====== */
const $=s=>document.querySelector(s);
const Fmx=v=>'$'+Number(v||0).toLocaleString('es-MX',{minimumFractionDigits:2,maximumFractionDigits:2})+' MXN';
const fmtDate = ts => new Date(ts).toISOString().slice(0,10);
const esc = s => (s??'').toString().replace(/[&<>"]/g,m=>({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;'}[m]));

/* ====== storage ====== */
let store=[];
function loadStore(){ store = JSON.parse(localStorage.getItem('vc_reservas')||'[]'); }
function saveStore(){ localStorage.setItem('vc_reservas', JSON.stringify(store)); }
function activos(){ return store.filter(r => (r.status||'').toLowerCase()!=='cancelada'); }
function removeFromShareIfNeeded(id){
  const tmp = localStorage.getItem('vc_contrato_en_proceso');
  if(!tmp) return;
  try{ const o=JSON.parse(tmp); if(o && o.id===id) localStorage.removeItem('vc_contrato_en_proceso'); }catch{}
}

/* ====== rutas de edición / cambio (ajústalas a tu backend) ====== */
const makeEditUrl = id => `final.html?id=${encodeURIComponent(id)}&edit=1`;  // ej: /reservas/${id}/editar
const makeSwapUrl = id => `cambioauto.html?id=${encodeURIComponent(id)}`;      // ej: /reservas/${id}/cambio-auto

/* ====== render ====== */
function render(list){
  const q = ($('#q').value||'').trim().toLowerCase();
  const body = $('#tbody'); body.innerHTML='';
  let conf=0, borr=0;

  list
    .filter(r=>{
      const text = `${r.id} ${r.cliente?.nombre||''} ${r.cliente?.apellidos||''} ${r.cliente?.email||''}`.toLowerCase();
      return !q || text.includes(q);
    })
    .forEach(r=>{
      const estadoLc = (r.status||'').toLowerCase();
      const editable = estadoLc==='confirmada' || estadoLc==='en contrato';
      if(editable) conf++; else borr++;

      const row = document.createElement('div');
      row.className='row';

      const total = r.total || r.totalsMXN?.total || 0;

      // estado badge
      const stateCls = estadoLc==='confirmada' ? 'ok' : (estadoLc==='borrador' ? 'gray' : 'warn');

      // acciones compactas
      const actionsWrap = document.createElement('div');
      actionsWrap.className = 'actions-wrap';

      if(editable){
        const aEdit = document.createElement('a');
        aEdit.className='chip';
        aEdit.href=makeEditUrl(esc(r.id));
        aEdit.dataset.act='edit';
        aEdit.textContent='✏️ Editar';
        actionsWrap.appendChild(aEdit);

        const aSwap = document.createElement('a');
        aSwap.className='chip ghost';
        aSwap.href=makeSwapUrl(esc(r.id));
        aSwap.dataset.act='swap';
        aSwap.textContent='🚗 Cambio';
        actionsWrap.appendChild(aSwap);
      }

      const btnDel = document.createElement('button');
      btnDel.className='iconbtn danger';
      btnDel.dataset.act='del';
      btnDel.dataset.id=r.id;
      btnDel.title='Eliminar';
      btnDel.textContent='🗑️';
      actionsWrap.appendChild(btnDel);

      // contenido de fila
      row.innerHTML = `
        <div class="cell-ellipsis" title="${esc(r.id)}">${esc(r.id)}</div>
        <div>${esc(fmtDate(r.createdAt||Date.now()))}</div>
        <div class="cell-ellipsis" title="${esc((r.cliente?.nombre||'')+' '+(r.cliente?.apellidos||''))}">${esc((r.cliente?.nombre||'')+' '+(r.cliente?.apellidos||''))}</div>
        <div class="cell-ellipsis cell-email" title="${esc(r.cliente?.email||'—')}">${esc(r.cliente?.email||'—')}</div>
        <div><span class="state ${stateCls}">${esc(r.status||'—')}</span></div>
        <div class="total">${Fmx(total)}</div>
      `;

      // Inserta acciones (como último grid item)
      row.appendChild(actionsWrap);

      // abrir modal salvo clic en acciones
      row.addEventListener('click',ev=>{
        const act = ev.target && ev.target.dataset ? ev.target.dataset.act : '';
        if(act==='del' || act==='edit' || act==='swap') return;
        openModal(r);
      });

      // evita burbuja en acciones
      actionsWrap.querySelectorAll('[data-act]').forEach(el=>{
        el.addEventListener('click',e=>e.stopPropagation());
      });

      // eliminar
      btnDel.addEventListener('click',()=>delById(r.id));

      body.appendChild(row);
    });

  $('#count').textContent = list.length;
  $('#countConf').textContent = conf;
  $('#countBorr').textContent = borr;
}

/* ====== modal ====== */
let current=null;
function openModal(r){
  current=r;
  $('#mTitle').textContent = `Contrato Reservación ${r.id}`;
  const fechas = `${r.fIni||'—'} ${r.hIni||''} HRS al ${r.fFin||'—'} ${r.hFin||''} HRS`;
  $('#mBody').innerHTML = `
    <div class="kv"><div>Fechas</div><div>${esc(fechas)}</div></div>
    <div class="kv"><div>Vehículo</div><div>${esc(r.vehiculo||'—')}</div></div>
    <div class="kv"><div>Forma Pago</div><div>OFICINA</div></div>
  `;
  $('#modal').classList.add('show');
}
function closeModal(){ $('#modal').classList.remove('show'); }
$('#mClose').onclick=closeModal; $('#mCancel').onclick=closeModal;

/* eliminar desde modal */
$('#mDel').onclick=()=>{ if(current) delById(current.id); };

/* contrato */
$('#mGo').onclick=()=>{
  if(!current) return;
  localStorage.setItem('vc_contrato_en_proceso', JSON.stringify(current));
  location.href = 'contrato.html';
};

/* ====== eliminar ====== */
function delById(id){
  if(!id) return;
  if(!confirm(`¿Eliminar la reservación ${id} de forma definitiva?`)) return;
  loadStore();
  const before = store.length;
  store = store.filter(r=>r.id!==id);
  saveStore();
  removeFromShareIfNeeded(id);
  closeModal();
  render(activos());
  alert(before!==store.length ? `Reservación ${id} eliminada.` : `No se encontró la reservación ${id}.`);
}

/* ====== init ====== */
loadStore();
render(activos());
$('#q').addEventListener('input',()=>render(activos()));
