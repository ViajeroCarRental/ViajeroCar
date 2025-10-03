document.addEventListener('DOMContentLoaded', () => {
  const DEFAULT_HOME = 'inicio.html';
  const urlq = new URLSearchParams(location.search);
  const NEXT_URL = urlq.get('next') || DEFAULT_HOME;

  // --- Topbar (opcional en esta página)
  const topbar = document.getElementById('topbar');
  function toggleTopbar(){
    if (!topbar) return;
    if (window.scrollY > 40) topbar.classList.add('solid');
    else topbar.classList.remove('solid');
  }
  if (topbar) {
    toggleTopbar();
    window.addEventListener('scroll', toggleTopbar, { passive: true });
  }

  // --- Footer year (opcional)
  const yearEl = document.getElementById('year');
  if (yearEl) yearEl.textContent = new Date().getFullYear();

  // --- Menú hamburguesa (opcional)
  const hamburger = document.getElementById('hamburger');
  const mainMenu  = document.getElementById('mainMenu');
  if (hamburger && mainMenu) {
    hamburger.addEventListener('click', () => {
      const open = getComputedStyle(mainMenu).display === 'none';
      mainMenu.style.display = open ? 'flex' : 'none';
      if (open) {
        mainMenu.style.flexDirection = 'column';
        mainMenu.style.gap = '12px';
      }
    });
  }

  // --- Mini “auth” local demo (sin backend)
  (function(){
    const AUTH_KEY = 'vj_auth';
    const URLS = { LOGIN:'login.html', PROFILE:'perfil.html' };

    function getAuth(){ try{ return JSON.parse(localStorage.getItem(AUTH_KEY)||'null'); }catch(e){ return null; } }
    function isLogged(){ return !!localStorage.getItem(AUTH_KEY); }
    function setAuth(obj){ localStorage.setItem(AUTH_KEY, JSON.stringify(obj)); window.dispatchEvent(new StorageEvent('storage',{key:AUTH_KEY})); }
    function logout(){ localStorage.removeItem(AUTH_KEY); window.dispatchEvent(new StorageEvent('storage',{key:AUTH_KEY})); }

    window.VJ_AUTH = { getAuth, isLogged, setAuth, logout, URLS, KEY:AUTH_KEY };
  })();

  // --- Icono de cuenta (opcional en esta página)
  (function syncAccountIcon(){
    const link = document.getElementById('accountLink');
    if (!link) return; // no existe en la vista de login
    function paint(){
      if(window.VJ_AUTH.isLogged()){
        const u = window.VJ_AUTH.getAuth() || {};
        link.href = window.VJ_AUTH.URLS.PROFILE;
        link.title = 'Mi perfil';
        link.classList.add('active');
        link.innerHTML = `<span class="avatar-mini">${(u.name?.[0] || u.email?.[0] || 'U').toUpperCase()}</span>`;
        const here = location.pathname.split('/').pop();
        if(here.toLowerCase().startsWith('login')) {
          setTimeout(()=> location.replace(NEXT_URL), 300);
        }
      }else{
        link.href = window.VJ_AUTH.URLS.LOGIN;
        link.title = 'Iniciar sesión';
        link.innerHTML = '<i class="fa-regular fa-user"></i>';
      }
    }
    paint();
    window.addEventListener('storage', e=>{ if(e.key===window.VJ_AUTH.KEY) paint(); });
  })();

  // --- Pestañas: Iniciar sesión / Crear cuenta
  const seg = document.getElementById('tabs');
  if (seg){
    const buttons = seg.querySelectorAll('.seg-btn');
    const slider  = seg.querySelector('.seg-slider');
    const panels  = document.querySelectorAll('.auth-panel');

    function setActive(idx){
      buttons.forEach((b,i)=>b.classList.toggle('active', i===idx));
      panels.forEach(p=>p.classList.remove('show'));
      const targetSel = buttons[idx]?.dataset?.target;
      const target = targetSel ? document.querySelector(targetSel) : null;
      if (target) target.classList.add('show');
      if (slider) slider.style.transform = `translateX(${idx*100}%)`;
    }

    buttons.forEach((b,i)=> b.addEventListener('click',()=> setActive(i)));
    setActive(0); // inicia mostrando "Iniciar sesión"
  }

  // --- Mostrar / ocultar contraseña (ojito)
  document.querySelectorAll('.eye').forEach(btn=>{
    btn.addEventListener('click', ()=>{
      const sel = btn.getAttribute('data-target');
      const inp = sel ? document.querySelector(sel) : null;
      if(!inp) return;
      const newType = inp.type === 'password' ? 'text' : 'password';
      inp.type = newType;
      btn.innerHTML = newType==='password'
        ? '<i class="fa-regular fa-eye"></i>'
        : '<i class="fa-regular fa-eye-slash"></i>';
    });
  });

  // --- Modal de bienvenida (opcional)
  const wm = document.getElementById('welcomeModal');
  const wmBar = document.getElementById('wmBar');
  const wmTitle = document.getElementById('wmTitle');
  const wmSub = document.getElementById('wmSub');
  const wmCount = document.getElementById('wmCount');
  const wmNow = document.getElementById('wmNow');
  const WELCOME_DELAY_MS = 2000;

  function redirectNext(){ window.location.assign(NEXT_URL); }
  function showWelcome(name=''){
    if (!wm) return redirectNext();
    wmTitle && (wmTitle.textContent = `¡Bienvenido${name ? ', ' + name : ''}!`);
    wmSub && (wmSub.textContent = NEXT_URL.includes('reserva') ? 'Vamos a tu reserva' : 'Preparando tu cuenta');
    if (wmBar) wmBar.style.width = '0%';
    wm.classList.add('show'); document.body.style.overflow='hidden';
    const start = performance.now();
    let remaining = Math.ceil(WELCOME_DELAY_MS/1000);
    if (wmCount) wmCount.textContent = `Redirigiendo en ${remaining} s…`;
    function step(ts){
      const p = Math.min((ts - start) / WELCOME_DELAY_MS, 1);
      if (wmBar) wmBar.style.width = (p*100).toFixed(1) + '%';
      const left = Math.max(0, Math.ceil((WELCOME_DELAY_MS - (ts - start))/1000));
      if (wmCount && left !== remaining){ remaining = left; wmCount.textContent = `Redirigiendo en ${remaining} s…`; }
      if(p < 1) requestAnimationFrame(step);
    }
    requestAnimationFrame(step);
    setTimeout(redirectNext, WELCOME_DELAY_MS);
    wmNow && wmNow.focus();
  }
  wmNow?.addEventListener('click', redirectNext);
  wm?.querySelector('.wm-backdrop')?.addEventListener('click', redirectNext);

  // --- Login (demo)
  document.getElementById('formLogin')?.addEventListener('submit', e=>{
    e.preventDefault();
    const u = document.getElementById('loginUser');
    const p = document.getElementById('loginPass');
    let ok = true;
    [u,p].forEach(i=>{
      const m = i?.parentElement?.querySelector('.msg');
      if(!i) return;
      if(!i.value.trim()){ m && (m.textContent='Campo requerido'); ok=false; } else { m && (m.textContent=''); }
    });
    if(!ok) return;

    const raw = u.value.trim();
    const emailLike = raw.includes('@') ? raw : `${raw}@example.com`;
    const name = (emailLike.split('@')[0]||'Usuario').replace(/[^a-z0-9]/ig,' ').trim();
    window.VJ_AUTH.setAuth({ name: name || 'Usuario', email: emailLike, ts: Date.now() });
    localStorage.setItem('vj_last_user', raw);
    showWelcome(name);
  });

  // --- Autofill último usuario
  (function(){
    const last = localStorage.getItem('vj_last_user');
    if(last && document.getElementById('loginUser')) document.getElementById('loginUser').value = last;
  })();

  // --- “Olvidé mi contraseña” (demo)
  document.getElementById('forgotLink')?.addEventListener('click', ()=>{
    const email = prompt('Ingresa tu correo para restablecer:');
    if(!email) return;
    alert('Si tu correo existe en nuestra base, te enviaremos instrucciones para restablecer tu contraseña.');
  });

  // --- Registro + verificación (demo)
  const formRegister = document.getElementById('formRegister');
  const verifyModal  = document.getElementById('verifyModal');
  const vClose       = document.getElementById('vClose');
  const verifyEmailEl= document.getElementById('verifyEmail');
  const mockMail     = document.getElementById('mockMail');
  const codeInputs   = Array.from(document.querySelectorAll('#codeInputs input'));
  const btnVerify    = document.getElementById('btnVerify');
  const btnResend    = document.getElementById('btnResend');
  const resendTimer  = document.getElementById('resendTimer');
  let currentCode = '', timer=null, seconds=30;

  function emailValid(v){ return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(v); }
  function passStrength(pw){ let s=0; if(pw.length>=8)s++; if(/[A-Z]/.test(pw))s++; if(/[a-z]/.test(pw))s++; if(/\d|[^\w\s]/.test(pw))s++; return Math.min(s,4); }
  function updateStrength(){
    const pw = document.getElementById('rPass')?.value || '';
    const lvl = passStrength(pw);
    const wrap = document.getElementById('passStrength');
    if(!wrap) return;
    wrap.querySelectorAll('span').forEach((sp,i)=> sp.classList.toggle('active', i<lvl));
    const lbl = document.getElementById('strengthLabel');
    if (lbl) lbl.textContent = 'Fortaleza: ' + (['—','Débil','Media','Buena','Fuerte'][lvl]);
  }
  document.getElementById('rPass')?.addEventListener('input', updateStrength);

  // placeholders flotantes seguros
  document.querySelectorAll('.field input').forEach(inp=>{
    if(!inp.hasAttribute('placeholder')) inp.setAttribute('placeholder',' ');
  });

  formRegister?.addEventListener('submit', e=>{
    e.preventDefault();
    const fields = ['rName','rApPat','rApMat','rBirth','rEmail','rEmail2','rPass','rPass2'];
    let ok = true;
    fields.forEach(id=>{
      const el = document.getElementById(id);
      const msg = el?.parentElement?.querySelector('.msg');
      if(!el) return;
      msg && (msg.textContent = '');
      if(!el.value.trim()){ msg && (msg.textContent='Campo requerido'); ok=false; }
    });
    const e1 = document.getElementById('rEmail')?.value.trim() || '';
    const e2 = document.getElementById('rEmail2')?.value.trim() || '';
    const p1 = document.getElementById('rPass')?.value || '';
    const p2 = document.getElementById('rPass2')?.value || '';
    if(e1 && !emailValid(e1)){ setMsg('rEmail','Correo inválido'); ok=false; }
    if(e1 && e2 && e1!==e2){ setMsg('rEmail2','El correo no coincide'); ok=false; }
    if(p1 && p2 && p1!==p2){ setMsg('rPass2','La contraseña no coincide'); ok=false; }
    if(!document.getElementById('rTos')?.checked){ alert('Debes aceptar el Aviso de Privacidad y los Términos.'); ok=false; }
    if(!ok) return;

    verifyEmailEl && (verifyEmailEl.textContent = e1);
    currentCode = Array.from({length:6},()=>Math.floor(Math.random()*10)).join('');
    mockMail && (mockMail.innerText = `Simulación de correo:\nTu código de verificación es: ${currentCode}`);
    openModal();
  });

  function setMsg(id,t){
    const el = document.getElementById(id);
    if (el) el.parentElement.querySelector('.msg').textContent=t;
  }

  function openModal(){
    if(!verifyModal) return;
    verifyModal.classList.add('show');
    startTimer();
    codeInputs.forEach(i=>i.value='');
    codeInputs[0]?.focus();
  }
  function closeModal(){ verifyModal?.classList.remove('show'); clearInterval(timer); }
  vClose?.addEventListener('click', closeModal);
  verifyModal?.querySelector('.modal-backdrop')?.addEventListener('click', closeModal);

  function startTimer(){
    seconds = 30;
    if (btnResend) btnResend.disabled = true;
    if (resendTimer) resendTimer.textContent = `(${seconds}s)`;
    clearInterval(timer);
    timer = setInterval(()=>{
      seconds--;
      if (resendTimer) resendTimer.textContent = `(${seconds}s)`;
      if(seconds<=0){
        clearInterval(timer);
        if (btnResend) btnResend.disabled=false;
        if (resendTimer) resendTimer.textContent='';
      }
    },1000);
  }

  codeInputs.forEach((inp,idx)=>{
    inp.addEventListener('input', ()=>{
      inp.value = inp.value.replace(/\D/g,'').slice(0,1);
      if(inp.value && idx<codeInputs.length-1) codeInputs[idx+1].focus();
    });
    inp.addEventListener('keydown', e=>{
      if(e.key==='Backspace' && !inp.value && idx>0) codeInputs[idx-1].focus();
    });
  });

  btnVerify?.addEventListener('click', ()=>{
    const value = codeInputs.map(i=>i.value).join('');
    if(value.length<6){ alert('Ingresa el código completo.'); return; }
    if(value!==currentCode){ alert('Código incorrecto.'); return; }
    closeModal();
    const name = (document.getElementById('rName')?.value || 'Usuario').trim();
    const email = (document.getElementById('rEmail')?.value || '').trim();
    window.VJ_AUTH.setAuth({ name, email, ts: Date.now(), new:true });
    showWelcome(name);
  });

  btnResend?.addEventListener('click', ()=>{
    currentCode = Array.from({length:6},()=>Math.floor(Math.random()*10)).join('');
    if (mockMail) mockMail.innerText = `Simulación de correo:\nTu nuevo código es: ${currentCode}`;
    startTimer();
  });
});
