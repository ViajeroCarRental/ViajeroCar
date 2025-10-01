/* ==== Lógica (idéntica, con UI compacta) ==== */
const $=s=>document.querySelector(s);
const $$=s=>Array.from(document.querySelectorAll(s));
const esc=s=>(s??'').toString().replace(/[&<>"]/g,m=>({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;'}[m]));
const uid=()=> 'R-' + Math.random().toString(36).slice(2,8);

$('#burger')?.addEventListener('click',()=>$('#side').classList.toggle('show'));

const rolesStore = {
  get(){ return JSON.parse(localStorage.getItem('admin_roles')||'[]'); },
  set(v){ localStorage.setItem('admin_roles', JSON.stringify(v)); },
  defaultGet(){ return localStorage.getItem('admin_default_role') || ''; },
  defaultSet(id){ localStorage.setItem('admin_default_role', id||''); }
};
const usersStore = { get(){ return JSON.parse(localStorage.getItem('admin_users')||'[]'); } };

function kpill(txt,cls=''){return `<span class="kpill ${cls}">${esc(txt)}</span>`}
function countUsersForRole(rname){ return usersStore.get().filter(u=>u.rol===rname).length; }
function renderStats(){
  const roles=rolesStore.get();
  const perms=roles.reduce((a,r)=>a+(r.permissions?.length||0),0);
  $('#sRoles').textContent=roles.length; $('#sPerms').textContent=perms;
  const def=roles.find(r=>r.id===rolesStore.defaultGet()); $('#sDefault').textContent=def?def.name:'—';
}
function renderTable(){
  const roles=rolesStore.get(), def=rolesStore.defaultGet(), tb=$('#tbody');
  if(!roles.length){ tb.innerHTML = `<tr><td colspan="7" class="small" style="padding:20px;text-align:center">Sin roles.</td></tr>`; renderStats(); return; }
  tb.innerHTML = roles.map(r=>{
    const mods=(r.modules||[]).map(m=>kpill(m,'blue')).join(' ');
    const cnt=r.permissions?.length||0, us=countUsersForRole(r.name);
    const scope = r.scope==='global'?kpill('Global'):kpill('Sede');
    return `<tr>
      <td><div style="font-weight:900">${esc(r.name)} ${r.id===def?kpill('Por defecto','green'):''}</div></td>
      <td>${esc(r.description||'—')}</td>
      <td>${scope}</td>
      <td>${mods||'—'}</td>
      <td>${kpill(cnt+' permisos')}</td>
      <td>${kpill(us,us>0?'green':'')}</td>
      <td>
        <div class="row-actions">
          <button class="btn gray" data-ed="${r.id}">✏️ Editar</button>
          <button class="btn gray" data-clone="${r.id}">📑 Clonar</button>
          ${r.id!==def?`<button class="btn warn" data-def="${r.id}">⭐ Defecto</button>`:''}
          <button class="btn gray" data-del="${r.id}">🗑️ Eliminar</button>
        </div>
      </td>
    </tr>`;
  }).join('');
  renderStats();
}

/* Modal: helpers */
function currentPerms(){ return $$('#rolePop [data-perm]:checked').map(i=>i.getAttribute('data-perm')); }
function setPerms(arr){ $$('#rolePop [data-perm]').forEach(c=>c.checked = !!arr?.includes(c.getAttribute('data-perm'))); }
function setModules(arr){ $$('#rolePop [data-mod]').forEach(c=>c.checked = !!arr?.includes(c.getAttribute('data-mod'))); }
function getModules(){ return $$('#rolePop [data-mod]:checked').map(c=>c.getAttribute('data-mod')); }
function syncAll(){
  const t=$$('#rolePop [data-perm]').length, s=$$('#rolePop [data-perm]:checked').length;
  $('#rAll').checked = (s>0 && s===t);
}
function toggleGroup(key,on){
  const map={
    usuarios:['users.view','users.create','users.edit','users.delete'],
    reservas:['res.create','res.edit','res.cancel','res.price','res.close'],
    sedes:['sites.view','sites.manage'],
    reportes:['rep.view','rep.export'],
    admin:['admin.roles','admin.settings']
  }[key]||[];
  $$('#rolePop [data-perm]').forEach(c=>{
    if(map.includes(c.getAttribute('data-perm'))) c.checked=on;
  });
  syncAll();
}

/* Open/close */
let editingId=null, pendingDeleteId=null;
function openRoleModal(id=null,isClone=false){
  editingId = id && !isClone ? id : null;
  const roles=rolesStore.get();
  const data = roles.find(r=>r.id===id) || {name:'',description:'',scope:'sede',modules:[],permissions:[]};
  $('#roleTitle').textContent = isClone?`Clonar rol “${data.name}”`:(id?'Editar rol':'Nuevo rol');
  $('#rName').value = isClone ? (data.name+' (copia)') : data.name;
  $('#rDesc').value = data.description||''; $('#rScope').value=data.scope||'sede';
  setModules(data.modules||[]); setPerms(data.permissions||[]); syncAll();
  $('#rolePop').classList.add('show');
}
function closeRoleModal(){ $('#rolePop').classList.remove('show'); }
$('#btnNew').addEventListener('click',()=>openRoleModal());
$('#roleClose').addEventListener('click',closeRoleModal);
$('#roleCancel').addEventListener('click',closeRoleModal);

/* Group toggles + select all */
$('[data-gcheck="usuarios"]').addEventListener('change',e=>toggleGroup('usuarios',e.target.checked));
$('[data-gcheck="reservas"]').addEventListener('change',e=>toggleGroup('reservas',e.target.checked));
$('[data-gcheck="sedes"]').addEventListener('change',e=>toggleGroup('sedes',e.target.checked));
$('[data-gcheck="reportes"]').addEventListener('change',e=>toggleGroup('reportes',e.target.checked));
$('[data-gcheck="admin"]').addEventListener('change',e=>toggleGroup('admin',e.target.checked));
$('#rAll').addEventListener('change',e=>{
  const on=e.target.checked; $$('#rolePop [data-perm]').forEach(c=>c.checked=on);
});
$('#rolePop').addEventListener('change',e=>{
  if(e.target.matches('[data-perm]')) syncAll();
});

/* Guardar */
$('#roleSave').addEventListener('click',()=>{
  const name=($('#rName').value||'').trim(); if(!name){ alert('El nombre del rol es obligatorio.'); return; }
  const payload={ name, description:($('#rDesc').value||'').trim(), scope:$('#rScope').value,
                  modules:getModules(), permissions:currentPerms(), createdAt:Date.now() };
  const roles=rolesStore.get();
  if(editingId){ const i=roles.findIndex(r=>r.id===editingId); if(i>=0) roles[i]={...roles[i],...payload}; }
  else{ roles.push({id:uid(), ...payload}); }
  rolesStore.set(roles); closeRoleModal(); renderTable();
});

/* Row actions */
$('#tbody').addEventListener('click',e=>{
  const ed=e.target.getAttribute('data-ed');
  const clone=e.target.getAttribute('data-clone');
  const del=e.target.getAttribute('data-del');
  const def=e.target.getAttribute('data-def');
  if(ed){ openRoleModal(ed,false); return; }
  if(clone){ openRoleModal(clone,true); return; }
  if(def){ rolesStore.defaultSet(def); renderTable(); return; }
  if(del){
    const roles=rolesStore.get(); const r=roles.find(x=>x.id===del);
    const cnt=usersStore.get().filter(u=>u.rol===r.name).length;
    if(cnt>0){
      pendingDeleteId=del; fillReassignOptions(del);
      $('#reassignCount').textContent=String(cnt);
      $('#reassignMsg').innerHTML=`El rol <b>${esc(r.name)}</b> tiene <b>${cnt}</b> usuario(s). Selecciona destino.`;
      $('#reassignPop').classList.add('show');
    }else{
      if(confirm('¿Eliminar rol?')){ rolesStore.set(roles.filter(x=>x.id!==del)); if(rolesStore.defaultGet()===del) rolesStore.defaultSet(''); renderTable(); }
    }
  }
});

/* Reasignación */
function fillReassignOptions(excludeId){
  const roles=rolesStore.get().filter(r=>r.id!==excludeId);
  $('#reassignRole').innerHTML=roles.map(r=>`<option value="${r.id}">${esc(r.name)}</option>`).join('');
}
$('#reassignClose').addEventListener('click',()=>$('#reassignPop').classList.remove('show'));
$('#reassignCancel').addEventListener('click',()=>$('#reassignPop').classList.remove('show'));
$('#reassignApply').addEventListener('click',()=>{
  const targetId=$('#reassignRole').value; const roles=rolesStore.get(); const delId=pendingDeleteId;
  const delRole=roles.find(r=>r.id===delId); const targetRole=roles.find(r=>r.id===targetId); if(!targetRole) return;
  const users=usersStore.get().map(u=>u.rol===delRole.name?{...u,rol:targetRole.name}:u);
  localStorage.setItem('admin_users', JSON.stringify(users));
  rolesStore.set(roles.filter(r=>r.id!==delId)); if(rolesStore.defaultGet()===delId) rolesStore.defaultSet('');
  $('#reassignPop').classList.remove('show'); renderTable();
});

/* Export / Import / Seed */
$('#btnExport').addEventListener('click',()=>{
  const data={default:rolesStore.defaultGet(),roles:rolesStore.get()};
  const blob=new Blob([JSON.stringify(data,null,2)],{type:'application/json'});
  const url=URL.createObjectURL(blob); const a=document.createElement('a'); a.href=url; a.download='roles.json'; a.click(); URL.revokeObjectURL(url);
});
$('#fileImport').addEventListener('change',async e=>{
  const f=e.target.files?.[0]; if(!f) return;
  try{ const txt=await f.text(); const data=JSON.parse(txt);
    if(!Array.isArray(data.roles)) throw new Error('Formato no válido');
    rolesStore.set(data.roles); rolesStore.defaultSet(data.default||''); renderTable(); alert('Roles importados.');
  }catch{ alert('No se pudo importar el JSON.'); } finally{ e.target.value=''; }
});
$('#btnSeed').addEventListener('click',()=>{
  if(!confirm('Restablecer roles de demostración?')) return;
  const all=['users.view','users.create','users.edit','users.delete','res.create','res.edit','res.cancel','res.price','res.close','sites.view','sites.manage','rep.view','rep.export','admin.roles','admin.settings'];
  const demo=[
    {id:uid(),name:'Admin',description:'Acceso total',scope:'global',modules:['Reservaciones','Cotizaciones','Activas','Visor','Historial','Administración','Reportes'],permissions:all,createdAt:Date.now()-3e5},
    {id:uid(),name:'Supervisor',description:'Operación y reportes',scope:'global',modules:['Reservaciones','Activas','Historial','Reportes'],permissions:['res.create','res.edit','res.cancel','res.price','res.close','rep.view','rep.export','users.view'],createdAt:Date.now()-2e5},
    {id:uid(),name:'Operador',description:'Alta/edición en su sede',scope:'sede',modules:['Reservaciones','Activas','Visor'],permissions:['res.create','res.edit','res.cancel','users.view'],createdAt:Date.now()-1e5}
  ];
  rolesStore.set(demo); rolesStore.defaultSet(demo[2].id); renderTable();
});

/* Start */
renderTable();
