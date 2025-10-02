/* ===== Helpers ===== */
const $=s=>document.querySelector(s); const $$=s=>Array.from(document.querySelectorAll(s));
const toast=(m,ms=1100)=>{const t=$('#toast');t.textContent=m;t.style.opacity=1;t.style.transform='translateY(0)';setTimeout(()=>{t.style.opacity=0;t.style.transform='translateY(10px)'},ms)};
const debounce=(fn,ms=350)=>{let t;return(...a)=>{clearTimeout(t);t=setTimeout(()=>fn(...a),ms)}};
const q = new URLSearchParams(location.search);
const readLS=k=>{try{const x=localStorage.getItem(k);return x?JSON.parse(x):null}catch{return null}};
const writeLS=(k,v)=>localStorage.setItem(k,JSON.stringify(v));

/* ===== Keys ===== */
const K={ draft:'viajero_licencia_draft', clientes:'vc_clientes' };

/* ===== Cliente activo ===== */
function pickLastClientId(){
  const cat=readLS(K.clientes)||[];
  if(!cat.length) return null;
  // último insertado (al final)
  return cat[cat.length-1].id;
}
function getActiveClientId(){
  const fromQuery = q.get('cliente');
  if(fromQuery) return fromQuery;
  return pickLastClientId();
}
function ensureClientIdInUrl(id){
  if(!id) return;
  const url = new URL(location.href);
  url.searchParams.set('cliente', id);
  history.replaceState(null,'',url.toString());
}

/* ===== Mock búsqueda (demo) ===== */
const MOCK=[
  { numero:'1070', vence:'2026-12-31', estado:'Querétaro', pais:'México', clase:'A', restr:'' },
  { numero:'ABC123456', vence:'2027-05-10', estado:'CDMX', pais:'México', clase:'A', restr:'Lentes' }
];
function fill(o){
  if(!o) return;
  $('#licNumero').value=o.numero||'';
  $('#licVence').value=o.vence||'';
  $('#licEstado').value=o.estado||'';
  $('#licPais').value=o.pais||'México';
  $('#licClase').value=o.clase||'';
  $('#licRestr').value=o.restr||'';
  updatePreview(); reevaluate();
}

/* ===== UI ===== */
function updatePreview(){ $('#numPreview').textContent = $('#licNumero').value.trim() || '—'; }
const isValidFull = ()=> $('#licNumero').value.trim() && $('#licVence').value && $('#licPais').value;
function reevaluate(){
  const okSave = isValidFull();
  ['#btnGuardar'].forEach(sel=>{ const b=$(sel); if(b) b.disabled=!okSave; });
  const hasId = !!getActiveClientId();
  const canNext = hasId && $('#licNumero').value.trim().length>0;
  ['#btnContinuar'].forEach(sel=>{ const b=$(sel); if(b) b.disabled=!canNext; });
}

/* ===== Autosave ===== */
function payload(){
  return {
    numero: $('#licNumero').value.trim(),
    vence:  $('#licVence').value,
    estado: $('#licEstado').value.trim(),
    pais:   $('#licPais').value,
    clase:  $('#licClase').value.trim(),
    restr:  $('#licRestr').value.trim()
  };
}
const saveDraft=debounce(()=>{ writeLS(K.draft,payload()); },500);
$$('input,select').forEach(el=> el.addEventListener('input',()=>{ updatePreview(); reevaluate(); saveDraft(); }));

/* Cargar borrador y/o datos del cliente */
(function preload(){
  const id = getActiveClientId();
  if(id) ensureClientIdInUrl(id);

  const cat = readLS(K.clientes)||[];
  const c = id ? cat.find(x=>x.id===id) : null;
  const lic = c?.licencia;
  const d = readLS(K.draft);

  if(lic){ fill({numero:lic.numero, vence:lic.vigencia||lic.vence, estado:lic.estado, pais:lic.pais, clase:lic.clase, restr:lic.restr}); }
  else if(d){ fill(d); }
  else { updatePreview(); }

  if(id){
    document.title += ` • Cliente ${id}`;
    $('#subCliente').textContent = `Captura/valida la licencia para el cliente ${id}.`;
  }else{
    $('#subCliente').textContent = 'No se encontró un cliente. Registra uno en el Paso 1.';
  }
  reevaluate();
})();

/* ===== Guardado en el cliente (upsert) ===== */
function upsertLicenciaInClient(id, lic){
  const cat = readLS(K.clientes)||[];
  let c = id ? cat.find(x=>x.id===id) : null;
  if(!c){
    // si no hay cliente (acceso directo a paso 2), creamos uno mínimo
    c = { id: id || ('C-'+Date.now()), nombreCompleto:'', email:'', celular:'', domicilio:{}, fiscal:null, notas:'' };
    cat.push(c);
  }
  c.licencia = { numero:lic.numero, vigencia:lic.vence, estado:lic.estado, pais:lic.pais, clase:lic.clase, restr:lic.restr };
  writeLS(K.clientes, cat);
}

/* ===== Eventos ===== */
$('#btnBuscar').onclick=()=>{
  const n=$('#licNumero').value.trim();
  const hit = MOCK.find(x=>x.numero.toLowerCase()===n.toLowerCase()) || (n==='1070'?MOCK[0]:null);
  if(hit){ fill(hit); writeLS(K.draft,payload()); toast('Coincidencia encontrada'); }
  else { toast('Sin coincidencias'); }
};

$('#btnLimpiar').onclick=()=>{
  $$('input,select').forEach(el=>{
    if(el.id==='licPais') el.value='México'; else el.value='';
  });
  localStorage.removeItem(K.draft);
  updatePreview(); reevaluate(); toast('Limpio');
};

/* Guardar (se queda en el paso 2) */
$('#btnGuardar').onclick=()=>{
  const id = getActiveClientId();
  if(!id){ toast('Primero crea un cliente en el Paso 1'); return; }
  upsertLicenciaInClient(id, payload());
  localStorage.removeItem(K.draft);
  toast('Licencia guardada');
  reevaluate();
};

/* Continuar a Paso 3 (guarda y navega) */
$('#btnContinuar').onclick=()=>{
  const id = getActiveClientId();
  if(!id){ toast('Primero crea un cliente en el Paso 1'); return; }
  upsertLicenciaInClient(id, payload());
  localStorage.removeItem(K.draft);
  location.href = `paso3.html?cliente=${encodeURIComponent(id)}`;
};

$('#btnBack').onclick=()=> history.length>1 ? history.back() : location.href='paso1.html';
