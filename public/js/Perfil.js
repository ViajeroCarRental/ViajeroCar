const qs = s=>document.querySelector(s);
const qsa = s=>[...document.querySelectorAll(s)];
const showToast = (t='Changes saved')=>{
  const el=qs('#toast'); el.innerHTML = '<i class="fa-solid fa-check"></i> ' + t;
  el.classList.add('show'); setTimeout(()=>el.classList.remove('show'), 2500);
};

const topbar = qs('#topbar');
function toggleTopbar(){ window.scrollY>40 ? topbar.classList.add('solid') : topbar.classList.remove('solid'); }
toggleTopbar(); window.addEventListener('scroll', toggleTopbar, {passive:true});
qs('#year').textContent = new Date().getFullYear();
qs('#hamburger')?.addEventListener('click', ()=>{
  const menu = qs('#mainMenu');
  const open = getComputedStyle(menu).display === 'none';
  menu.style.display = open ? 'flex' : 'none';
  if(open){ menu.style.flexDirection='column'; menu.style.gap='12px'; }
});
(function markActive(){
  const current = (location.pathname.split('/').pop() || 'inicio.html').toLowerCase();
  qsa('.menu a').forEach(a=>{
    const href=(a.getAttribute('href')||'').toLowerCase();
    a.classList.toggle('active', href===current);
  });
})();

(function(){
  const AUTH_KEY = 'vj_auth';
  function getAuth(){ try{ return JSON.parse(localStorage.getItem(AUTH_KEY)||'null'); }catch(e){ return null; } }
  function isLogged(){ return !!localStorage.getItem(AUTH_KEY); }
  function setAuth(obj){ localStorage.setItem(AUTH_KEY, JSON.stringify(obj)); window.dispatchEvent(new StorageEvent('storage',{key:AUTH_KEY})); }
  function logout(){ localStorage.removeItem(AUTH_KEY); window.dispatchEvent(new StorageEvent('storage',{key:AUTH_KEY})); }
  window.VJ_AUTH = { getAuth, isLogged, setAuth, logout, KEY:AUTH_KEY, URLS:{ LOGIN:'login.html', PROFILE:'perfil.html' } };
})();

if(!window.VJ_AUTH.isLogged()){
  const next = encodeURIComponent('perfil.html');
  location.replace(`login.html?next=${next}`);
}

(function syncAccountIcon(){
  const link = qs('#accountLink');
  function paint(){
    if(window.VJ_AUTH.isLogged()){
      const u = window.VJ_AUTH.getAuth() || {};
      link.href = window.VJ_AUTH.URLS.PROFILE;
      link.title = 'My profile';
      link.innerHTML = `<span class="avatar-mini">${(u.name?.[0] || u.email?.[0] || 'U').toUpperCase()}</span>`;
    }else{
      link.href = window.VJ_AUTH.URLS.LOGIN;
      link.title = 'Sign in';
      link.innerHTML = '<i class="fa-regular fa-user"></i>';
    }
  }
  paint();
  window.addEventListener('storage', e=>{ if(e.key===window.VJ_AUTH.KEY) paint(); });
})();

qsa('.s-btn').forEach(b=>{
  b.addEventListener('click', ()=>{
    qsa('.s-btn').forEach(x=>x.classList.remove('active'));
    b.classList.add('active');
    const t = b.dataset.target;
    qsa('.panel').forEach(p=>p.classList.remove('show'));
    qs(t)?.classList.add('show');
  });
});

const birthPicker = qs('#birthPicker');
const birthPop    = birthPicker.querySelector('.cal-pop');
const birthInput  = qs('#pfBirthDisplay'); // visible dd/mm/aaaa
const pfBirth     = qs('#pfBirth');        // hidden ISO yyyy-mm-dd

function pad(n){ return String(n).padStart(2,'0'); }
function fmtISO(d){ return `${d.getFullYear()}-${pad(d.getMonth()+1)}-${pad(d.getDate())}`; }
function fmtDMY(d){ return `${pad(d.getDate())}/${pad(d.getMonth()+1)}/${d.getFullYear()}`; }
function parseISO(s){ const m = /^(\d{4})-(\d{2})-(\d{2})$/.exec(s||''); if(!m) return null; return new Date(Number(m[1]), Number(m[2])-1, Number(m[3])); }
function sameDay(a,b){ return a && b && a.getFullYear()==b.getFullYear() && a.getMonth()==b.getMonth() && a.getDate()==b.getDate(); }

let selectedDate = null;
let viewMonth = new Date();
const today = new Date(); today.setHours(0,0,0,0);
const MONTHS = ['January','February','March','April','May','June','July','August','September','October','November','December'];

function openBirthPop(){
  birthPop.classList.add('show');
  renderBirthCal();
}
function closeBirthPop(){ birthPop.classList.remove('show'); }

function renderBirthCal(){
  const y = viewMonth.getFullYear(), m = viewMonth.getMonth();
  const first = new Date(y,m,1);
  const startGrid = new Date(y,m,1 - ((first.getDay()+6)%7)); // Monday as first day

  const maxY = today.getFullYear();
  const minY = maxY - 100;

  birthPop.innerHTML = `
    <div class="cal-head">
      <button type="button" class="cal-nav-btn" aria-label="Previous year" data-nav="-12"><i class="fa-solid fa-angles-left"></i></button>
      <button type="button" class="cal-nav-btn" aria-label="Previous month" data-nav="-1"><i class="fa-solid fa-chevron-left"></i></button>
      <div class="cal-controls">
        <select class="cal-month" aria-label="Month">
          ${MONTHS.map((nm,idx)=>`<option value="${idx}" ${idx===m?'selected':''}>${nm}</option>`).join('')}
        </select>
        <select class="cal-year" aria-label="Year">
          ${Array.from({length:(maxY-minY+1)}).map((_,i)=>{
            const yy = maxY - i; return `<option value="${yy}" ${yy===y?'selected':''}>${yy}</option>`;
          }).join('')}
        </select>
      </div>
      <button type="button" class="cal-nav-btn" aria-label="Next month" data-nav="1"><i class="fa-solid fa-chevron-right"></i></button>
      <button type="button" class="cal-nav-btn" aria-label="Next year" data-nav="12"><i class="fa-solid fa-angles-right"></i></button>
    </div>
    <div class="cal-grid">
      ${['Mon','Tue','Wed','Thu','Fri','Sat','Sun'].map(d=>`<div class="dow">${d}</div>`).join('')}
      ${Array.from({length:42}).map((_,i)=>{
        const d = new Date(startGrid); d.setDate(startGrid.getDate()+i);
        const isMuted = d.getMonth()!==m;
        const isFuture = d.getTime() > today.getTime(); // no future dates allowed
        const classes = [
          'day',
          isMuted ? 'muted' : '',
          isFuture ? 'disabled' : '',
          selectedDate && sameDay(d,selectedDate) ? 'selected':''
        ].join(' ');
        return `<div class="${classes}" data-date="${d.toISOString()}">${d.getDate()}</div>`;
      }).join('')}
    </div>
  `;

  birthPop.querySelectorAll('[data-nav]').forEach(btn=>{
    btn.addEventListener('click', ()=>{
      viewMonth.setMonth(viewMonth.getMonth()+Number(btn.dataset.nav));
      // clamp year to allowed range for DOB (no future)
      if(viewMonth.getFullYear() > today.getFullYear()) viewMonth.setFullYear(today.getFullYear());
      if(viewMonth.getFullYear() < minY) viewMonth.setFullYear(minY);
      renderBirthCal();
    });
  });

  const selM = birthPop.querySelector('.cal-month');
  const selY = birthPop.querySelector('.cal-year');
  selM.addEventListener('change', ()=>{ viewMonth.setMonth(Number(selM.value)); renderBirthCal(); });
  selY.addEventListener('change', ()=>{ viewMonth.setFullYear(Number(selY.value)); renderBirthCal(); });

  birthPop.querySelectorAll('.day').forEach(cell=>{
    cell.addEventListener('click', ()=>{
      if(cell.classList.contains('disabled')) return;
      const d = new Date(cell.dataset.date);
      selectedDate = d;
      pfBirth.value = fmtISO(d);
      birthInput.value = fmtDMY(d);
      qs('#pfAge').value = calcAge(pfBirth.value) || '';
      closeBirthPop();
    });
  });
}

birthPicker.addEventListener('click', (e)=>{
  if(qs('#btnSave').disabled) return;
  e.stopPropagation();
  openBirthPop();
});
document.addEventListener('click', (e)=>{
  if(!birthPicker.contains(e.target)) closeBirthPop();
});

function calcAge(iso){
  if(!iso) return '';
  const d = parseISO(iso);
  if(!d) return '';
  const now = new Date();
  let a = now.getFullYear() - d.getFullYear();
  const m = now.getMonth() - d.getMonth();
  if (m < 0 || (m === 0 && now.getDate() < d.getDate())) a--;
  return String(a);
}
function ensureMember(u){
  if(u.member) return u.member;
  const rnd = ()=>Math.floor(1000+Math.random()*9000);
  u.member = `VJ-${rnd()}-${rnd()}`;
  return u.member;
}
function loadProfile(){
  const u = window.VJ_AUTH.getAuth() || {};
  ensureMember(u);
  qs('#pfName').value   = u.name || '';
  qs('#pfEmail').value  = u.email || '';
  qs('#pfPhone').value  = u.phone || '';
  pfBirth.value         = u.birth || '';
  selectedDate          = parseISO(u.birth || '') || null;
  viewMonth             = selectedDate ? new Date(selectedDate) : new Date();
  birthInput.value      = selectedDate ? fmtDMY(selectedDate) : '';
  qs('#pfAge').value    = u.birth ? calcAge(u.birth) : '';
  qs('#pfLoc').value    = u.loc || '';
  qs('#pfMember').value = u.member || '';
  window.VJ_AUTH.setAuth(u);
}
loadProfile();

const btnEdit = qs('#btnEdit'), btnSave = qs('#btnSave');
function setEditable(on){
  ['#pfName','#pfEmail','#pfPhone','#pfLoc'].forEach(sel=>{
    const el = qs(sel); el.disabled = !on;
  });
  qs('#birthPicker').classList.toggle('disabled', !on);
  btnSave.disabled = !on;
}
btnEdit.addEventListener('click', ()=> setEditable(true));

pfBirth.addEventListener('change', ()=>{
  const d = parseISO(pfBirth.value);
  birthInput.value = d? fmtDMY(d) : '';
  qs('#pfAge').value = d? calcAge(pfBirth.value) : '';
  selectedDate = d || null;
  viewMonth = d ? new Date(d) : new Date();
});

qs('#formPerfil').addEventListener('submit', e=>{
  e.preventDefault();
  const u = window.VJ_AUTH.getAuth() || {};
  u.name  = qs('#pfName').value.trim();
  u.email = qs('#pfEmail').value.trim();
  u.phone = qs('#pfPhone').value.trim();
  u.birth = pfBirth.value || '';
  u.loc   = qs('#pfLoc').value.trim();
  u.member= qs('#pfMember').value.trim() || ensureMember(u);
  window.VJ_AUTH.setAuth(u);
  setEditable(false);
  showToast('Profile updated');
  window.dispatchEvent(new StorageEvent('storage',{key:window.VJ_AUTH.KEY}));
});

const RES_KEY = 'vj_reservas';
function seedReservations(){
  const now = new Date();
  const fmt = (d)=>d.toLocaleDateString('es-MX',{day:'2-digit',month:'2-digit',year:'numeric'});
  const addDays = (n)=>{const x=new Date(now);x.setDate(x.getDate()+n);return x};
  const sample = [
    { id:'CT-' + (10000+Math.floor(Math.random()*89999)), car:'Chevrolet Aveo or similar', dates:`${fmt(addDays(2))} → ${fmt(addDays(5))}`, status:'pending' },
    { id:'CT-' + (10000+Math.floor(Math.random()*89999)), car:'Kia Sportage or similar',    dates:`${fmt(addDays(-1))} → ${fmt(addDays(3))}`, status:'active' },
    { id:'CT-' + (10000+Math.floor(Math.random()*89999)), car:'Volkswagen Virtus or similar',dates:`${fmt(addDays(-14))} → ${fmt(addDays(-10))}`, status:'completed' },
    { id:'CT-' + (10000+Math.floor(Math.random()*89999)), car:'BMW 3 Series or similar',     dates:`${fmt(addDays(12))} → ${fmt(addDays(16))}`, status:'pending' },
  ];
  localStorage.setItem(RES_KEY, JSON.stringify(sample));
  return sample;
}
function getReservations(){
  try{
    const v = JSON.parse(localStorage.getItem(RES_KEY)||'null');
    if(!v || !Array.isArray(v) || v.length===0) return seedReservations();
    return v;
  }catch(e){ return seedReservations(); }
}
function paintReservations(filter='all'){
  const tbody = qs('#resTable tbody'); tbody.innerHTML='';
  const list = getReservations().filter(r=> filter==='all' ? true : r.status===filter);
  const cls = (st)=> st==='pending'?'st-pend' : st==='active'?'st-act':'st-fin';
  list.forEach(r=>{
    const tr = document.createElement('tr');
    let statusText = '';
    if(r.status==='pending') statusText = '🕒 Pending';
    else if(r.status==='active') statusText = '✅ Active';
    else statusText = '✔ Completed';
    tr.innerHTML = `
       <td>${r.id}</td>
       <td>${r.car}</td>
       <td>${r.dates}</td>
       <td><span class="status ${cls(r.status)}">${statusText}</span></td>`;
    tbody.appendChild(tr);
  });
}
paintReservations('all');

qsa('#resFilters .pill').forEach(p=>{
  p.addEventListener('click', ()=>{
    qsa('#resFilters .pill').forEach(x=>x.classList.remove('active'));
    p.classList.add('active');
    paintReservations(p.dataset.status);
  });
});

const CARDS_KEY = 'vj_cards';
function seedCards(){
  const cards = [
    { id:crypto.randomUUID?.()||String(Date.now()), brand:'visa',      number:'4111111111111111', name:'Juan Pérez', exp:'12/26', alias:'Personal' },
    { id:crypto.randomUUID?.()||String(Date.now()+1), brand:'mastercard', number:'5555555555554444', name:'Juan Pérez', exp:'03/27', alias:'Business' },
  ];
  localStorage.setItem(CARDS_KEY, JSON.stringify(cards));
  return cards;
}
function getCards(){
  try{
    const v = JSON.parse(localStorage.getItem(CARDS_KEY)||'null');
    if(!v || !Array.isArray(v) || v.length===0) return seedCards();
    return v;
  }catch(e){ return seedCards(); }
}
function setCards(arr){ localStorage.setItem(CARDS_KEY, JSON.stringify(arr)); }
function mask(num){ return (num||'').replace(/\D/g,'').replace(/.(?=.{4})/g,'•').replace(/(.{4})/g,'$1 ').trim(); }
function brandIcon(b){
  if(b==='visa') return '<img src="https://upload.wikimedia.org/wikipedia/commons/4/41/Visa_Logo.png" alt="Visa" style="height:16px;background:#fff;padding:2px 6px;border-radius:4px;box-shadow:0 2px 6px rgba(0,0,0,.12)">';
  if(b==='mastercard') return '<img src="https://upload.wikimedia.org/wikipedia/commons/2/2a/Mastercard-logo.svg" alt="Mastercard" style="height:16px;background:#fff;padding:2px 6px;border-radius:4px;box-shadow:0 2px 6px rgba(0,0,0,.12)">';
  if(b==='amex') return '<img src="https://upload.wikimedia.org/wikipedia/commons/3/30/American_Express_logo_%282018%29.svg" alt="Amex" style="height:16px;background:#fff;padding:2px 6px;border-radius:4px;box-shadow:0 2px 6px rgba(0,0,0,.12)">';
  return '<i class="fa-regular fa-credit-card"></i>';
}
function paintCards(){
  const wrap = qs('#cardsWrap'); wrap.innerHTML='';
  getCards().forEach(c=>{
    const div = document.createElement('div');
    div.className = 'cc';
    div.innerHTML = `
      <div class="cc-head">
        <div class="cc-brand">${brandIcon(c.brand)} <span>${(c.alias||c.brand||'Card').toUpperCase()}</span></div>
        <span style="font-size:12px;color:#6b7280">${c.exp}</span>
      </div>
      <div class="cc-body">
        <div class="cc-num">${mask(c.number)}</div>
        <div class="cc-row"><span>${c.name}</span> · <span style="text-transform:capitalize">${c.brand}</span></div>
        <div class="cc-actions">
          <button class="btn-icon" data-edit="${c.id}"><i class="fa-regular fa-pen-to-square"></i> Edit</button>
          <button class="btn-icon" data-del="${c.id}"><i class="fa-regular fa-trash-can"></i> Delete</button>
        </div>
      </div>`;
    wrap.appendChild(div);
  });

  wrap.querySelectorAll('[data-edit]').forEach(b=> b.addEventListener('click', ()=> openCardModal(b.dataset.edit)));
  wrap.querySelectorAll('[data-del]').forEach(b=> b.addEventListener('click', ()=>{
    if(!confirm('Delete this card?')) return;
    const id = b.dataset.del;
    const arr = getCards().filter(x=>x.id!==id);
    setCards(arr); paintCards(); showToast('Card deleted');
  }));
}
paintCards();

const cardModal = qs('#cardModal');
const cardForm  = qs('#cardForm');
const ccName = qs('#ccName'), ccNumber = qs('#ccNumber'), ccBrand = qs('#ccBrand'), ccExp = qs('#ccExp'), ccAlias = qs('#ccAlias');
let editingId = null;

function openCardModal(id=null){
  editingId = id;
  if(id){
    const c = getCards().find(x=>x.id===id);
    qs('#cardModalTitle').innerHTML = '<i class="fa-solid fa-credit-card"></i> Edit card';
    ccName.value = c?.name||''; ccNumber.value=c?.number?.replace(/(.{4})/g,'$1 ').trim()||''; ccBrand.value=c?.brand||'visa'; ccExp.value=c?.exp||''; ccAlias.value=c?.alias||'';
  }else{
    qs('#cardModalTitle').innerHTML = '<i class="fa-solid fa-credit-card"></i> Add card';
    cardForm.reset();
  }
  cardModal.classList.add('show');
  cardForm.querySelector('input,select')?.focus();
}
function closeCardModal(){ cardModal.classList.remove('show'); }
qs('#cardCancel').addEventListener('click', closeCardModal);
qs('#cardClose').addEventListener('click', closeCardModal);
cardModal.querySelector('.modal-backdrop').addEventListener('click', closeCardModal);

ccNumber.addEventListener('input', ()=>{
  let v = ccNumber.value.replace(/\D/g,'').slice(0,19);
  v = v.replace(/(.{4})/g,'$1 ').trim();
  ccNumber.value = v;
});
ccExp.addEventListener('input', ()=>{
  let v = ccExp.value.replace(/\D/g,'').slice(0,4);
  if(v.length>=3) v = v.slice(0,2) + '/' + v.slice(2);
  ccExp.value = v;
});

cardForm.addEventListener('submit', e=>{
  e.preventDefault();
  const rawNum = ccNumber.value.replace(/\s/g,'');
  if(rawNum.length < 15){ alert('Invalid card number.'); return; }
  const data = {
    id: editingId || (crypto.randomUUID?.()||String(Date.now())),
    name: ccName.value.trim(),
    number: rawNum,
    brand: ccBrand.value,
    exp: ccExp.value.trim(),
    alias: ccAlias.value.trim()
  };
  let arr = getCards();
  if(editingId){
    arr = arr.map(x=> x.id===editingId ? data : x);
    showToast('Card updated');
  }else{
    arr.push(data); showToast('Card added');
  }
  setCards(arr); paintCards(); closeCardModal();
});

const logoutModal   = qs('#logoutModal');
const logoutBtn     = qs('#btnLogout');
const logoutCancel  = qs('#logoutCancel');
const logoutConfirm = qs('#logoutConfirm');
const loClose       = qs('#loClose');

function openLogoutModal(){
  logoutModal.classList.add('show');
  logoutCancel.focus();
  document.addEventListener('keydown', onLoEsc);
}
function closeLogoutModal(){
  logoutModal.classList.remove('show');
  document.removeEventListener('keydown', onLoEsc);
  logoutBtn.focus();
}
function onLoEsc(e){ if(e.key==='Escape') closeLogoutModal(); }

logoutBtn.addEventListener('click', openLogoutModal);
logoutCancel.addEventListener('click', closeLogoutModal);
loClose.addEventListener('click', closeLogoutModal);
logoutModal.querySelector('.modal-backdrop').addEventListener('click', closeLogoutModal);

logoutConfirm.addEventListener('click', ()=>{
  window.VJ_AUTH.logout();
  location.assign('inicio.html');
});

logoutBtn.addEventListener('keydown', e=>{ if(e.key==='Enter' || e.key===' ') { e.preventDefault(); openLogoutModal(); } });

(function seedAuth(){
  if(!window.VJ_AUTH.isLogged()){
    window.VJ_AUTH.setAuth({
      name:'Juan Pérez',
      email:'juan@example.com',
      phone:'4421234567',
      birth:'1990-05-20',
      loc:'Querétaro, Qro.',
      member:''
    });
  }
})();
