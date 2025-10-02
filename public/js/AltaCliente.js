/* ===== helpers ===== */
const $=s=>document.querySelector(s); const $$=s=>Array.from(document.querySelectorAll(s));
const debounce=(fn,ms=350)=>{let t;return(...a)=>{clearTimeout(t);t=setTimeout(()=>fn(...a),ms)}};
const readLS=k=>{try{const x=localStorage.getItem(k);return x?JSON.parse(x):null}catch{ return null }};
const writeLS=(k,v)=>localStorage.setItem(k,JSON.stringify(v));
const toast=(m,ms=1100)=>{const t=$('#toast');t.textContent=m;t.style.opacity=1;t.style.transform='translateY(0)';setTimeout(()=>{t.style.opacity=0;t.style.transform='translateY(10px)'},ms)};

/* ===== keys ===== */
const K={ draft:'viajero_cliente_draft', clientes:'vc_clientes' };

/* ===== preset consecutivo/fecha ===== */
(function(){
  const d=new Date();
  $('#fechaReg').value=d.toISOString().slice(0,10);
  const sug=`C${String(d.getFullYear()).slice(-2)}${String(d.getMonth()+1).padStart(2,'0')}${String(d.getDate()).padStart(2,'0')}-${String(d.getHours()).padStart(2,'0')}${String(d.getMinutes()).padStart(2,'0')}`;
  if(!$('#noCliente').value) $('#noCliente').value=sug;
  updateResumen(); // inicial
})();

/* ===== mock buscar por licencia (demo) ===== */
const MOCK=[{lic:'LIC123456',nombres:'Eduardo',apPat:'Quintero',apMat:'Nava',cel:'5599738740',email:'eduardo@mail.com',pais:'México',estado:'CDMX',calle:'Av. Reforma 100',colonia:'Centro',mun:'Cuauhtémoc',cp:'06000',fnac:'1990-04-01'}];
function fillFrom(o){ if(!o) return;
  $('#nombres').value=o.nombres||''; $('#apPat').value=o.apPat||''; $('#apMat').value=o.apMat||'';
  $('#cel').value=o.cel||''; $('#email').value=o.email||''; $('#pais').value=o.pais||'México';
  $('#estado').value=o.estado||'CDMX'; $('#calle').value=o.calle||''; $('#colonia').value=o.colonia||'';
  $('#mun').value=o.mun||''; $('#cp').value=o.cp||''; $('#fnac').value=o.fnac||'';
}

/* ===== validaciones ===== */
const reCP=/^[0-9]{5}$/; const reTel=/^[0-9]{10}$/; const reRFC=/^[A-ZÑ&]{3,4}\d{6}[A-Z0-9]{3}$/i;
/* OJO: licencia YA NO es obligatoria en el paso 1 */
const basicsValid =()=> $('#nombres').value.trim() && $('#apPat').value.trim() && reTel.test($('#cel').value.trim()) && $('#email').value.includes('@') && reCP.test($('#cp').value.trim());
const fiscalValid =()=>{ const rfc=$('#rfc').value.trim(); if(!rfc) return true; return reRFC.test(rfc) && $('#razon').value.trim() && $('#domFiscal').value.trim(); };

/* ===== RESUMEN en vivo ===== */
function updateResumen(){
  const id=$('#noCliente').value.trim()||'—';
  const nom=[$('#nombres').value,$('#apPat').value,$('#apMat').value].filter(Boolean).join(' ')||'—';
  const nac=$('#fnac').value?`Nac. ${$('#fnac').value}`:'';
  const contacto=$('#cel').value||'—';
  const email=$('#email').value||'';
  const dom1=[$('#calle').value,$('#colonia').value,$('#mun').value].filter(Boolean).join(', ')||'—';
  const cp=$('#cp').value?`CP ${$('#cp').value}`:'';
  const lic=$('#lic').value||'—';
  const vig=$('#licVig').value?`Vig. ${$('#licVig').value}`:'';
  const fisc=$('#rfc').value?`${$('#rfc').value} · ${$('#razon').value||''}`:'—';
  const cfdi=$('#cfdi').value||'';
  const notas=$('#notas').value||'—';
  const fecha=$('#fechaReg').value||'—';

  $('#r_id').textContent=id;
  $('#r_nombre').textContent=nom; $('#r_nac').textContent=nac;
  $('#r_contacto').textContent=contacto; $('#r_email').textContent=email;
  $('#r_dom').textContent=dom1; $('#r_cp').textContent=cp;
  $('#r_lic').textContent=lic; $('#r_licvig').textContent=vig;
  $('#r_fiscal').textContent=fisc; $('#r_cfdi').textContent=cfdi;
  $('#r_notas').textContent=notas; $('#r_fecha').textContent=fecha;
}

/* Habilitación: solo “Registrar y continuar” */
function reevaluate(){
  const okSave = basicsValid() && fiscalValid();
  ['#btnSave','#btnSave2'].forEach(sel=>{ const b=$(sel); if(b) b.disabled=!okSave; });
}

/* ===== payload / autosave ===== */
function payload(){
  return {
    noCliente: $('#noCliente').value.trim(),
    registro: $('#fechaReg').value,
    pais: $('#pais').value, estado: $('#estado').value,
    nombres: $('#nombres').value.trim(), apPat: $('#apPat').value.trim(), apMat: $('#apMat').value.trim(),
    fnac: $('#fnac').value, cel: $('#cel').value.trim(), email: $('#email').value.trim(),
    domicilio:{calle:$('#calle').value.trim(),colonia:$('#colonia').value.trim(),mun:$('#mun').value.trim(),cp:$('#cp').value.trim()},
    notas: $('#notas').value.trim(),
    /* licencia opcional en paso 1 */
    licencia:{numero:$('#lic').value.trim(),pais:$('#licPais').value,vigencia:$('#licVig').value},
    /* fiscal opcional */
    fiscal: $('#rfc').value ? {rfc:$('#rfc').value.trim(),razon:$('#razon').value.trim(),cfdi:$('#cfdi').value,dom:$('#domFiscal').value.trim(),correo:$('#mailFiscal').value.trim()} : null
  };
}
const saveDraft=debounce(()=>{ writeLS(K.draft,payload()); },500);

/* listeners para resumen + autosave + validar */
$$('input,textarea,select').forEach(el=> el.addEventListener('input',()=>{ updateResumen(); reevaluate(); saveDraft(); }));

/* cargar borrador si existe */
(function(){
  const d=readLS(K.draft); if(!d) { reevaluate(); return; }
  $('#noCliente').value=d.noCliente||$('#noCliente').value; $('#fechaReg').value=d.registro||$('#fechaReg').value;
  $('#pais').value=d.pais||'México'; $('#estado').value=d.estado||'CDMX';
  $('#nombres').value=d.nombres||''; $('#apPat').value=d.apPat||''; $('#apMat').value=d.apMat||''; $('#fnac').value=d.fnac||'';
  $('#cel').value=d.cel||''; $('#email').value=d.email||'';
  $('#calle').value=d.domicilio?.calle||''; $('#colonia').value=d.domicilio?.colonia||''; $('#mun').value=d.domicilio?.mun||''; $('#cp').value=d.domicilio?.cp||'';
  $('#notas').value=d.notas||'';
  $('#lic').value=d.licencia?.numero||''; $('#licPais').value=d.licencia?.pais||'México'; $('#licVig').value=d.licencia?.vigencia||'';
  $('#rfc').value=d.fiscal?.rfc||''; $('#razon').value=d.fiscal?.razon||''; $('#cfdi').value=d.fiscal?.cfdi||''; $('#domFiscal').value=d.fiscal?.dom||''; $('#mailFiscal').value=d.fiscal?.correo||'';
  updateResumen(); reevaluate();
})();

/* ===== guardar definitivo y navegar a PASO 2 ===== */
function upsert(arr,p){
  const idx=arr.findIndex(x=>x.id===p.noCliente);
  const item={ id:p.noCliente, registro:p.registro, nombreCompleto:`${p.nombres} ${p.apPat} ${p.apMat}`.replace(/\s+/g,' ').trim(),
    email:p.email, celular:p.cel, domicilio:p.domicilio, licencia:p.licencia, fiscal:p.fiscal, notas:p.notas };
  if(idx>=0) arr[idx]=item; else arr.push(item);
  return arr;
}
const toPaso2 = (p)=> `paso2.html?cliente=${encodeURIComponent(p.noCliente)}`;

function ensureClientId(){
  let id = $('#noCliente').value.trim();
  if(!id){
    const d=new Date();
    id = `C${String(d.getFullYear()).slice(-2)}${String(d.getMonth()+1).padStart(2,'0')}${String(d.getDate()).padStart(2,'0')}-${String(d.getHours()).padStart(2,'0')}${String(d.getMinutes()).padStart(2,'0')}`;
    $('#noCliente').value=id;
  }
  return id;
}
function saveAndGo(){
  ensureClientId();
  const p=payload();
  const cat=readLS(K.clientes)||[];
  writeLS(K.clientes, upsert(cat,p));
  localStorage.removeItem(K.draft);
  toast('Cliente registrado');
  setTimeout(()=>location.href=toPaso2(p),250);
}

/* ===== eventos ===== */
$('#btnBuscar').addEventListener('click',()=>{
  const lic=$('#lic').value.trim();
  const hit=MOCK.find(x=>x.lic.toLowerCase()===lic.toLowerCase());
  if(hit){ fillFrom(hit); toast('Datos precargados desde licencia'); updateResumen(); saveDraft(); }
  else toast('Sin coincidencias');
  reevaluate();
});

const backUrl='clientes.html';
$('#btnBack').onclick=()=>location.href=backUrl;

$('#btnSave').onclick = saveAndGo;
$('#btnSave2').onclick= saveAndGo;

function clearAll(){
  $$( 'input,textarea,select').forEach(el=>{
    if(el.id==='fechaReg') return;
    if(el.tagName==='SELECT') el.selectedIndex=0; else el.value='';
  });
  $('#pais').value='México'; $('#estado').value='CDMX'; $('#licPais').value='México'; $('#cfdi').value='';
  const d=new Date(); $('#noCliente').value=`C${String(d.getFullYear()).slice(-2)}${String(d.getMonth()+1).padStart(2,'0')}${String(d.getDate()).padStart(2,'0')}-${String(d.getHours()).padStart(2,'0')}${String(d.getMinutes()).padStart(2,'0')}`;
  localStorage.removeItem(K.draft); updateResumen(); reevaluate(); toast('Formulario limpiado');
}
$('#btnClear').onclick=clearAll; $('#btnClear2').onclick=clearAll;

// validación y resumen inicial
updateResumen(); reevaluate();
