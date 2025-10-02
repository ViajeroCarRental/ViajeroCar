/* ===== UTIL ===== */
const $=s=>document.querySelector(s);
const $$=s=>Array.from(document.querySelectorAll(s));
const uid = ()=> 'U-' + Math.random().toString(36).slice(2,8);
const esc = s => (s??'').toString().replace(/[&<>"]/g, m=>({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;'}[m]));
const store = {
  get(){ return JSON.parse(localStorage.getItem('admin_users')||'[]'); },
  set(arr){ localStorage.setItem('admin_users', JSON.stringify(arr)); }
};
const ROLES = ['Admin','Supervisor','Operador','Invitado'];
const SEDES = ['Aeropuerto del Bajío (BJX)','Aeropuerto de Querétaro (AIQ)','Aeropuerto GDL','Aeropuerto MTY','Querétaro Centro'];

/* ===== UI INIT ===== */
$('#burger')?.addEventListener('click',()=>$('#side').classList.toggle('show'));

(function fillFilters(){
  $('#fRol').innerHTML = '<option value="">Todos los roles</option>' + ROLES.map(r=>`<option>${esc(r)}</option>`).join('');
  $('#fSede').innerHTML = '<option value="">Todas las sedes</option>' + SEDES.map(s=>`<option>${esc(s)}</option>`).join('');
  $('#uRol').innerHTML = ROLES.map(r=>`<option>${esc(r)}</option>`).join('');
  $('#uSede').innerHTML = SEDES.map(s=>`<option>${esc(s)}</option>`).join('');
})();

/* ===== STATE ===== */
let editingId = null;

/* ===== RENDER ===== */
function kpill(txt, cls=''){return `<span class="kpill ${cls}">${esc(txt)}</span>`}
function render(){
  const q=($('#q').value||'').toLowerCase().trim();
  const fRol=$('#fRol').value||'';
  const fSede=$('#fSede').value||'';

  const data = store.get().filter(u=>{
    const matchesQ = !q || (u.nombre+' '+u.apellidos+' '+u.email).toLowerCase().includes(q);
    const matchesR = !fRol || u.rol===fRol;
    const matchesS = !fSede || u.sede===fSede;
    return matchesQ && matchesR && matchesS;
  });

  const tbody = $('#tbody');
  if(!data.length){
    tbody.innerHTML = `<tr><td colspan="7"><div class="empty">Sin resultados. Ajusta filtros o agrega un usuario.</div></td></tr>`;
  }else{
    tbody.innerHTML = data.map(u=>{
      const mods = (u.modulos||[]).map(m=>kpill(m,'blue')).join(' ');
      const est  = u.status==='activo' ? kpill('Activo','green')
                 : u.status==='desactivado' ? kpill('Desactivado','red')
                 : kpill('Invitación','');
      return `<tr>
        <td>${esc(u.nombre)} ${esc(u.apellidos||'')}</td>
        <td>${esc(u.email)}</td>
        <td>${kpill(u.rol)}</td>
        <td>${esc(u.sede)}</td>
        <td>${mods||'—'}</td>
        <td>${est}</td>
        <td>
          <div class="row-actions">
            <button class="btn gray" data-ed="${u.id}">✏️ Editar</button>
            ${u.status!=='desactivado'
              ? `<button class="btn warn" data-deact="${u.id}">⏸ Desactivar</button>`
              : `<button class="btn primary" data-act="${u.id}">▶️ Activar</button>`}
            <button class="btn gray" data-del="${u.id}">🗑️ Eliminar</button>
          </div>
        </td>
      </tr>`;
    }).join('');
  }

  // Stats
  const all = store.get();
  $('#sActivos').textContent = all.filter(u=>u.status==='activo').length;
  $('#sInvites').textContent = all.filter(u=>u.status==='invitacion').length;
  $('#sAdmins').textContent  = all.filter(u=>u.rol==='Admin' && u.status!=='desactivado').length;
  $('#sOff').textContent     = all.filter(u=>u.status==='desactivado').length;
}

/* ===== EVENTS: Filters ===== */
$('#q').addEventListener('input',render);
$('#fRol').addEventListener('change',render);
$('#fSede').addEventListener('change',render);

/* ===== MODAL ADD/EDIT ===== */
function openUserModal(editId=null){
  editingId = editId;
  $('#userPopTitle').textContent = editId ? 'Editar usuario' : 'Nuevo usuario';
  if(editId){
    const u = store.get().find(x=>x.id===editId);
    if(!u) return;
    $('#uNombre').value = u.nombre||'';
    $('#uApellidos').value = u.apellidos||'';
    $('#uEmail').value = u.email||'';
    $('#uSede').value = u.sede||SEDES[0];
    $('#uRol').value = u.rol||ROLES[2];
    $('#uStatus').value = u.status||'activo';
    // módulos
    $$('#uMods option').forEach(o=>o.selected=(u.modulos||[]).includes(o.value));
  }else{
    $('#uNombre').value = '';
    $('#uApellidos').value = '';
    $('#uEmail').value = '';
    $('#uSede').value = SEDES[0];
    $('#uRol').value = ROLES[2];
    $('#uStatus').value = 'activo';
    $$('#uMods option').forEach(o=>o.selected=false);
  }
  $('#userPop').classList.add('show');
}
function closeUserModal(){ $('#userPop').classList.remove('show'); }
$('#btnAdd').addEventListener('click',()=>openUserModal());
$('#userClose').addEventListener('click',closeUserModal);
$('#userCancel').addEventListener('click',closeUserModal);

/* ===== SAVE ===== */
$('#userSave').addEventListener('click',()=>{
  const nombre=($('#uNombre').value||'').trim();
  const apellidos=($('#uApellidos').value||'').trim();
  const email=($('#uEmail').value||'').trim();
  const sede=$('#uSede').value;
  const rol=$('#uRol').value;
  const status=$('#uStatus').value;
  const modulos=$$('#uMods option:checked').map(o=>o.value);

  if(!nombre||!email){ alert('Nombre y Email son obligatorios'); return; }

  const arr = store.get();
  if(editingId){
    const i = arr.findIndex(u=>u.id===editingId);
    if(i>=0){ arr[i]={...arr[i], nombre, apellidos, email, sede, rol, status, modulos}; }
  }else{
    arr.push({ id:uid(), nombre, apellidos, email, sede, rol, status, modulos, createdAt:Date.now() });
  }
  store.set(arr);
  closeUserModal();
  render();
});

/* ===== ROW ACTIONS (edit/activate/deactivate/delete) ===== */
$('#tbody').addEventListener('click',e=>{
  const ed = e.target.getAttribute('data-ed');
  const deact = e.target.getAttribute('data-deact');
  const act = e.target.getAttribute('data-act');
  const del = e.target.getAttribute('data-del');
  let arr = store.get();
  if(ed){ openUserModal(ed); return; }
  if(deact){
    arr = arr.map(u=>u.id===deact?{...u,status:'desactivado'}:u);
    store.set(arr); render(); return;
  }
  if(act){
    arr = arr.map(u=>u.id===act?{...u,status:'activo'}:u);
    store.set(arr); render(); return;
  }
  if(del){
    if(confirm('¿Eliminar usuario? Esta acción no se puede deshacer.')){
      arr = arr.filter(u=>u.id!==del);
      store.set(arr); render();
    }
  }
});

/* ===== INVITAR / CSV / RESET ===== */
$('#btnInvite').addEventListener('click',()=>{
  const mailto = 'mailto:?subject=' + encodeURIComponent('Invitación a VIAJERO')
    + '&body=' + encodeURIComponent('Hola,\n\nHas sido invitado a VIAJERO. Crea tu contraseña y accede a los módulos asignados.\n\nSaludos.');
  window.location.href = mailto;
});
$('#btnExport').addEventListener('click',()=>{
  const arr=store.get();
  if(!arr.length){ alert('No hay datos para exportar.'); return; }
  const header=['id','nombre','apellidos','email','sede','rol','status','modulos','createdAt'];
  const rows = [header.join(',')].concat(arr.map(u=>{
    const line = [
      u.id, u.nombre, u.apellidos||'', u.email, u.sede, u.rol, u.status,
      (u.modulos||[]).join('|'), new Date(u.createdAt||Date.now()).toISOString()
    ].map(v=> `"${String(v).replace(/"/g,'""')}"`).join(',');
    return line;
  })).join('\n');
  const blob = new Blob([rows],{type:'text/csv;charset=utf-8;'});
  const url = URL.createObjectURL(blob);
  const a = document.createElement('a'); a.href=url; a.download='usuarios.csv'; a.click(); URL.revokeObjectURL(url);
});
$('#btnReset').addEventListener('click',()=>{
  if(!confirm('Restablecer datos de demostración?')) return;
  const demo=[
    {id:uid(), nombre:'Gael', apellidos:'Arriaga', email:'gael@viajero.mx', sede:SEDES[4], rol:'Admin', status:'activo', modulos:['Administración','Reservaciones','Reportes'], createdAt:Date.now()-86400000*4},
    {id:uid(), nombre:'María', apellidos:'Lara', email:'maria@viajero.mx', sede:SEDES[0], rol:'Supervisor', status:'activo', modulos:['Reservaciones','Activas','Historial'], createdAt:Date.now()-86400000*2},
    {id:uid(), nombre:'Jorge', apellidos:'Soto', email:'jorge@viajero.mx', sede:SEDES[1], rol:'Operador', status:'invitacion', modulos:['Reservaciones'], createdAt:Date.now()-86400000},
    {id:uid(), nombre:'Ana', apellidos:'Paz', email:'ana@viajero.mx', sede:SEDES[2], rol:'Operador', status:'desactivado', modulos:['Reservaciones','Visor'], createdAt:Date.now()}
  ];
  store.set(demo);
  render();
});

/* ===== START ===== */
render();
