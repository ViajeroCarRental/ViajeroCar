/* ========= Helpers ========= */
const $=s=>document.querySelector(s); const $$=s=>Array.from(document.querySelectorAll(s));
const qs=new URLSearchParams(location.search);
const toast=(m,ms=1100)=>{const t=$('#toast');t.textContent=m;t.style.opacity=1;t.style.transform='translateY(0)';setTimeout(()=>{t.style.opacity=0;t.style.transform='translateY(10px)'},ms)};
const readLS=k=>{try{const v=localStorage.getItem(k);return v?JSON.parse(v):null}catch{return null}};
const writeLS=(k,v)=>localStorage.setItem(k,JSON.stringify(v));
const debounce=(fn,ms=400)=>{let t;return(...a)=>{clearTimeout(t);t=setTimeout(()=>fn(...a),ms)}};

/* ========= Keys ========= */
const K={clientes:'vc_clientes', draft:'viajero_fiscal_draft'};

/* ========= Estado / cliente actual ========= */
let currentId = qs.get('cliente') || '';

function setClientId(id){
  currentId = id || currentId;
  $('#cliId').textContent = currentId || '—';
  const url = new URL(location.href);
  if(currentId) url.searchParams.set('cliente', currentId);
  else url.searchParams.delete('cliente');
  history.replaceState({}, '', url.toString());
}

/* ========= Utilidades de clientes ========= */
function getLastClient(){
  const cat = readLS(K.clientes)||[];
  if(!cat.length) return null;
  const withDate = cat.filter(x=>!!x.registro);
  if(withDate.length){
    withDate.sort((a,b)=> new Date(b.registro) - new Date(a.registro));
    return withDate[0];
  }
  return cat[cat.length-1];
}
function getClientById(id){
  const cat = readLS(K.clientes)||[];
  return cat.find(x=>x.id===id) || null;
}

/* ========= Cargar datos ========= */
(function preload(){
  // 1) tomar ID de query o último cliente y fijarlo en URL
  if(!currentId){
    const last = getLastClient();
    if(last) setClientId(last.id);
  }else{
    setClientId(currentId);
  }

  // 2) intentar llenar desde el catálogo del cliente actual
  const c = currentId ? getClientById(currentId) : null;
  if(c?.fiscal) fill(c.fiscal);

  // 3) sobre-escribir con draft si existiera
  const d = readLS(K.draft);
  if(d) fill(d);

  updateTags();
  reevaluate();
})();

/* ========= Form helpers ========= */
function payload(){
  return {
    rfc: $('#rfc').value.trim().toUpperCase(),
    razon: $('#razon').value.trim(),
    cfdi: $('#cfdi').value,
    dom: {
      calle: $('#calle').value.trim(),
      numext: $('#numext').value.trim(),
      numint: $('#numint').value.trim(),
      referencia: $('#refer').value.trim(),
      colonia: $('#colonia').value.trim(),
      cp: $('#cp').value.trim(),
      municipio: $('#municipio').value.trim(),
      ciudad: $('#ciudad').value.trim(),
      estado: $('#estado').value.trim(),
      pais: $('#pais').value
    },
    correo: $('#correo').value.trim(),
    notas: $('#notas').value.trim()
  };
}
function fill(f){
  if(!f) return;
  $('#rfc').value=f.rfc||'';
  $('#razon').value=f.razon||'';
  $('#cfdi').value=f.cfdi||'';
  $('#calle').value=f.dom?.calle||'';
  $('#numext').value=f.dom?.numext||'';
  $('#numint').value=f.dom?.numint||'';
  $('#refer').value=f.dom?.referencia||'';
  $('#colonia').value=f.dom?.colonia||'';
  $('#cp').value=f.dom?.cp||'';
  $('#municipio').value=f.dom?.municipio||'';
  $('#ciudad').value=f.dom?.ciudad||'';
  $('#estado').value=f.dom?.estado||'';
  $('#pais').value=f.dom?.pais||'México';
  $('#correo').value=f.correo||'';
  $('#notas').value=f.notas||'';
}
function updateTags(){
  $('#rfctag').textContent = $('#rfc').value.trim() || '—';
  $('#razontag').textContent = $('#razon').value.trim() || '—';
}

/* ========= Validación ========= */
const reRFC=/^[A-ZÑ&]{3,4}\d{6}[A-Z0-9]{3}$/i;
const reCP=/^\d{5}$/;
function isValid(){
  if(!currentId) return false;
  const f = payload();
  const minOk = reRFC.test(f.rfc) && f.razon && reCP.test(f.dom.cp) &&
                f.dom.calle && f.dom.numext && f.dom.colonia &&
                f.dom.municipio && f.dom.estado;
  const mailOk = !f.correo || f.correo.includes('@');
  return minOk && mailOk;
}
function reevaluate(){
  const ok = isValid();
  $('#btnGuardar').disabled = !ok;
  $('#btnFinalizar').disabled = !ok;
}

/* ========= Autosave ========= */
const saveDraft = debounce(()=>{ writeLS(K.draft, payload()); }, 450);
$$('input,select,textarea').forEach(el=> el.addEventListener('input',()=>{ updateTags(); reevaluate(); saveDraft(); }));

/* ========= Catálogo ========= */
function upsertFiscal(id, fiscal){
  const cat = readLS(K.clientes)||[];
  let c = cat.find(x=>x.id===id);
  if(!c){
    c = { id, nombreCompleto:'', email:'', celular:'', domicilio:{}, licencia:null, fiscal:null, notas:'' };
    cat.push(c);
  }
  c.fiscal = fiscal;
  writeLS(K.clientes, cat);
}

/* ========= Botones ========= */
$('#btnLast').onclick = ()=>{
  const last = getLastClient();
  if(!last){ toast('No hay clientes registrados'); return; }
  setClientId(last.id);
  const c = getClientById(last.id);
  if(c?.fiscal) fill(c.fiscal);
  updateTags(); reevaluate(); toast(`Cliente actual: ${last.id}`);
};

$('#btnClear').onclick = ()=>{
  $$('input,select,textarea').forEach(el=>{
    if(el.id==='pais') el.value='México';
    else if(el.tagName==='SELECT') el.value='';
    else el.value='';
  });
  updateTags(); reevaluate();
  localStorage.removeItem(K.draft);
  toast('Formulario limpio');
};

$('#btnGuardar').onclick = ()=>{
  if(!isValid()){ toast('Completa los campos obligatorios'); return; }
  upsertFiscal(currentId, payload());
  localStorage.removeItem(K.draft);
  toast('Datos fiscales guardados');
};

$('#btnFinalizar').onclick = ()=>{
  if(!isValid()){ toast('Completa los campos obligatorios'); return; }
  upsertFiscal(currentId, payload());
  localStorage.removeItem(K.draft);
  location.href = 'clientes.html';
};

$('#btnBack').onclick = ()=> {
  const url = 'paso2.html' + (currentId?`?cliente=${encodeURIComponent(currentId)}`:'');
  if(history.length>1) history.back(); else location.href=url;
};
