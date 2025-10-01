<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8" />
<title>Panel | Viajero Car Rental</title>
<meta name="viewport" content="width=device-width, initial-scale=1" />
<link rel="stylesheet" href="{{ asset('assets/style.css') }}" />
<style>
/* ====== Tipografía bonita + render ====== */
@import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;800&display=swap');

:root{
  /* Colores base */
  --ink:#0f172a; --muted:#6b7280; --stroke:#e9eef5; --ring:#ffe2e5; --card:#fff;
  /* Marca */
  --red:#E50914; --red2:#ff4d5a;
  --orange:#ff7a1a; --amber:#f59e0b;
  --violet:#8b5cf6; --indigo:#6366f1; --sky:#0ea5e9;
  --emerald:#10b981; --teal:#14b8a6; --pink:#ec4899; --rose:#ff3b5c;

  /* UI */
  --radius: 14px;
  --shadow-sm: 0 6px 18px rgba(17,24,39,.08);
  --shadow-md: 0 12px 28px rgba(17,24,39,.10);
  --shadow-lg: 0 20px 46px rgba(17,24,39,.14);

  /* Tipografía fluida */
  --fs-12: clamp(11px, .75vw, 12px);
  --fs-14: clamp(12px, .9vw, 14px);
  --fs-16: clamp(14px, 1.1vw, 16px);
  --fs-18: clamp(15px, 1.2vw, 18px);
  --fs-20: clamp(16px, 1.4vw, 20px);
  --fs-24: clamp(18px, 2vw, 24px);
  --fs-28: clamp(20px, 2.4vw, 28px);
  --fs-32: clamp(22px, 3vw, 32px);
  --fs-40: clamp(30px, 5vw, 44px);
}

*{box-sizing:border-box}
html{scroll-behavior:smooth}
body{
  margin:0; color:var(--ink); background:#fff;
  font-family: 'Inter', ui-sans-serif, system-ui, -apple-system, "Segoe UI", Roboto, "Helvetica Neue", Arial, "Noto Sans", "Apple Color Emoji","Segoe UI Emoji";
  font-size: var(--fs-16);
  line-height: 1.55;
  text-rendering: optimizeLegibility;
  -webkit-font-smoothing: antialiased; -moz-osx-font-smoothing: grayscale;
}

/* ===== Topbar ===== */
.top{
  display:flex; justify-content:space-between; align-items:center;
  padding:12px clamp(12px,2.5vw,22px);
  border-bottom:1px solid var(--stroke);
  position:sticky; top:0; background:#fff; z-index:50;
  backdrop-filter: saturate(120%) blur(6px);
}
.brand{display:flex; align-items:center; gap:12px}
.brand img{width: clamp(110px,16vw,140px); border-radius:10px; display:block}
.badge{
  display:inline-flex; gap:8px; align-items:center;
  padding:6px 10px; border:1px solid var(--stroke);
  border-radius:999px; background:#fff; color:#374151;
  font-size: var(--fs-12); font-weight:700
}
.dot{width:8px; height:8px; border-radius:999px; background:#10b981}
.right{display:flex; gap:10px; align-items:center}
.btn{
  border:none; padding:10px 14px; border-radius:12px; font-weight:800; cursor:pointer;
  font-size: var(--fs-14);
  transition: transform .12s ease, box-shadow .2s ease, background .2s ease, color .2s ease;
  will-change: transform;
}
.btn:active{ transform: translateY(1px) }
.btn:focus-visible{ outline: 3px solid rgba(99,102,241,.4); outline-offset:2px; }

.ghost{background:#fff; color:var(--red); border:1px solid #ffd6da}
.ghost:hover{ box-shadow: var(--shadow-sm); background:#fff6f7 }

.primary{
  color:#fff; background:linear-gradient(135deg,var(--red),var(--red2));
  box-shadow:0 10px 24px rgba(229,9,20,.28)
}
.primary:hover{ box-shadow:0 14px 32px rgba(229,9,20,.34); filter:saturate(1.02) }

/* Bell + panel */
.bell{
  position:relative; border:1px solid var(--stroke); background:#fff; border-radius:12px;
  padding:9px 11px; cursor:pointer; font-size: var(--fs-16);
  transition: box-shadow .2s ease, transform .12s ease;
}
.bell:hover{ box-shadow: var(--shadow-sm) }
.bell .count{
  position:absolute; top:-6px; right:-6px; background:var(--red); color:#fff;
  font-weight:800; font-size:12px; padding:2px 6px; border-radius:999px; box-shadow:0 0 0 3px #fff
}
.panel{
  position:absolute; right:0; top:46px; width:min(420px,92vw);
  background:#fff; border:1px solid var(--ring); border-radius:14px;
  box-shadow: var(--shadow-lg); display:none; overflow:hidden; z-index:60
}
.panel.show{ display:block }
.panel header{
  padding:12px 14px; background:linear-gradient(90deg,#fff3f4,#fffaf2);
  color:#b30b16; font-weight:800; font-size: var(--fs-14)
}
.panel .item{
  display:flex; gap:10px; padding:12px 14px; border-top:1px solid #f4e5e6; font-size: var(--fs-14)
}
.tag{font-size:11px; padding:2px 8px; border-radius:999px; border:1px solid #ffe2e5; background:#fff}
.tag.red{background:#ffe8ea; color:#d21c2b}
.tag.orange{background:#fff4e5; color:#b45309}
.tag.blue{background:#eef2ff; color:#4338ca}

/* ===== Hero multicolor ===== */
.hero{
  position:relative; overflow:hidden; border-bottom:1px solid var(--stroke);
  background:
    radial-gradient(1200px 320px at -15% -40%, #ffe4ea 10%, transparent 60%),
    radial-gradient(900px 300px at 115% -30%, #ffe7d6 10%, transparent 60%),
    radial-gradient(1000px 380px at 50% 120%, #e8f6ff 10%, transparent 60%),
    #fff;
}
.hero::before{
  content:""; position:absolute; inset:-40% -40% auto -40%; height:220px;
  background:linear-gradient(90deg, #ff9aa7, #ff7a1a, #f59e0b, #8b5cf6, #6366f1, #0ea5e9, #ff9aa7);
  background-size:200% 100%; opacity:.18; filter:blur(16px); transform:rotate(-6deg);
  animation:rainbow 12s linear infinite;
}
@keyframes rainbow{ to{ background-position:200% 0 } }

.wrap{max-width:1200px; margin:0 auto; padding:18px clamp(14px,2.4vw,32px) 32px}
.wrow{display:grid; grid-template-columns:1.1fr .9fr; gap: clamp(12px,2.2vw,20px)}
@media (max-width:980px){ .wrow{grid-template-columns:1fr} }

/* ===== Títulos del hero — MÁS GRANDES y rojos originales ===== */
.hi{
  font-size: clamp(36px, 6vw, 56px); /* grande en desktop y tablet */
  font-weight: 900;
  margin: 8px 0 12px;
  letter-spacing: .4px;
  line-height: 1.15;
}
.hi span{
  background: linear-gradient(90deg, var(--red), var(--red2)); /* rojos de marca */
  -webkit-background-clip: text;
  background-clip: text;
  color: transparent;
}

/* Subtítulo debajo — más legible */
.sub{
  font-size: clamp(18px, 2.4vw, 22px);
  font-weight: 500;
  color: var(--ink);
  max-width: 680px;
  line-height: 1.5;
  opacity: .9;
}

.chips{display:flex; gap:10px; flex-wrap:wrap}
.chip{
  display:inline-flex; gap:6px; align-items:center; padding:6px 10px; border-radius:999px;
  font-weight:800; background:#fff; border:1px solid var(--ring); font-size: var(--fs-12)
}
.chip.c1{border-color:#ffd5d9; background:#fff0f2}
.chip.c2{border-color:#ffe2c7; background:#fff7ed}
.chip.c3{border-color:#d8e7ff; background:#eef6ff}
.chip.c4{border-color:#c9f0e5; background:#eafaf1}
.actions{display:flex; gap:10px; margin-top:12px; flex-wrap:wrap}
.actions .primary{position:relative; overflow:hidden}

/* Ripple */
.ripple{
  position:absolute; width:16px; height:16px; border-radius:999px; background:rgba(255,255,255,.55);
  transform:scale(0); animation:rip .6s ease-out forwards; pointer-events:none
}
@keyframes rip{to{transform:scale(16); opacity:0}}

.media{
  width:100%; aspect-ratio:16/9; border-radius:18px; overflow:hidden; border:2px solid #ffd7dc;
  background:#000; position:relative; box-shadow: var(--shadow-md)
}
.media img,.media video{width:100%; height:100%; object-fit:cover; display:block}
.media::after{content:""; position:absolute; inset:0; background:linear-gradient(180deg, rgba(0,0,0,.08), rgba(0,0,0,.22))}

/* ===== KPIs multicolor ===== */
.kpis{display:grid; grid-template-columns:repeat(4,minmax(0,1fr)); gap: clamp(10px,1.4vw,14px); margin-top:18px}
@media (max-width:1100px){ .kpis{grid-template-columns:1fr 1fr} }
@media (max-width:640px){ .kpis{grid-template-columns:1fr} }
.kpi{
  background:#fff; border:2px solid var(--ring); border-radius:16px; padding:14px;
  box-shadow: var(--shadow-sm); position:relative; overflow:hidden;
}
.kpi h4{margin:0 0 6px; color:#6b7280; font-size: var(--fs-12); letter-spacing:.2px}
.kpi b{font-size: var(--fs-28); font-weight:900; color:#111827}
.kpi::before{
  content:""; position:absolute; left:0; right:0; top:0; height:4px;
  background:var(--kpiGrad, linear-gradient(90deg,#ffa3ab,#ff4d5a,#ffa3ab));
  background-size:200% 100%; animation:slide 6s linear infinite;
}
@keyframes slide{to{background-position:200% 0}}
.kpi.k1{ --kpiGrad: linear-gradient(90deg,#ff9aa7,#ff4d5a,#ff9aa7) }
.kpi.k2{ --kpiGrad: linear-gradient(90deg,#ffd08a,#ff7a1a,#ffd08a) }
.kpi.k3{ --kpiGrad: linear-gradient(90deg,#9ec3ff,#6366f1,#9ec3ff) }
.kpi.k4{ --kpiGrad: linear-gradient(90deg,#a7f3d0,#10b981,#a7f3d0) }

/* ===== Módulos ===== */
.section{max-width:1200px; margin:22px auto; padding:0 clamp(14px,2.4vw,18px)}
.grid{display:grid; grid-template-columns:repeat(3,minmax(0,1fr)); gap: clamp(12px,1.6vw,16px)}
@media (max-width:980px){ .grid{grid-template-columns:1fr 1fr} }
@media (max-width:640px){ .grid{grid-template-columns:1fr} }

.mod{
  border:0; border-radius:18px; padding:0; overflow:hidden; cursor:pointer; position:relative;
  transform:translateZ(0);
  box-shadow: var(--shadow-sm);
  transition: transform .18s ease, box-shadow .25s ease, filter .25s ease, outline .2s ease;
  background:#fff;
}
@media (hover:hover){
  .mod:hover{transform:translateY(-2px); box-shadow: var(--shadow-md); filter:saturate(1.06)}
}
.mod:focus-within, .mod:focus-visible{ outline: 3px solid rgba(99,102,241,.35); outline-offset:2px }
.mod .head{
  padding:14px 16px; color:#fff; font-weight:900; letter-spacing:.2px;
  display:flex; align-items:center; gap:10px; font-size: var(--fs-18)
}
.mod .ic{
  width:38px; height:38px; border-radius:12px; display:grid; place-items:center;
  font-size:20px; background:rgba(255,255,255,.22); border:1px solid rgba(255,255,255,.35)
}
.mod .body{ background:#fff; padding:16px; border-top:1px solid rgba(0,0,0,.05) }
.mod .body p{margin:0; color:#4b5563; font-size: var(--fs-14)}
.mod .go{margin-top:12px; display:inline-flex; gap:8px; align-items:center; color:#111827; font-weight:900; font-size: var(--fs-14)}
.mod.locked{opacity:.55; cursor:not-allowed}
.mod.locked::after{content:"🔒 Sin acceso"; position:absolute; right:12px; top:12px; font-weight:800; color:#fff; font-size: var(--fs-12)}
/* Gradientes por tema */
.mod[data-theme="autos"]  .head{ background:linear-gradient(120deg,#ff7a1a 0%, #ff0011ff 60%, #ff0022ff 100%) }
.mod[data-theme="rentas"] .head{ background:linear-gradient(120deg,#6366f1 0%, #ff0000ff 60%, #ff0000ff 100%) }
.mod[data-theme="admin"]  .head{ background:linear-gradient(120deg,#10b981 0%, #ff0000ff 60%, #ff0000ff 100%) }

/* Quick links */
.qwrap{display:flex; gap:10px; flex-wrap:wrap; margin-top:10px}
.q{
  display:inline-flex; gap:8px; align-items:center; padding:10px 12px; border-radius:12px;
  background:#fff; border:1px solid var(--ring); font-weight:800; font-size: var(--fs-12)
}
.q.cA{border-color:#ffd5d9; background:#fff0f2}
.q.cB{border-color:#d8e7ff; background:#eef6ff}
.q.cC{border-color:#c9f0e5; background:#eafaf1}

/* Footer */
.foot{
  max-width:1200px; margin:18px auto 28px; padding:0 clamp(14px,2.4vw,18px);
  color:#9aa1ac; font-size: var(--fs-12)
}

/* ===== Responsividad fina ===== */
@media (max-width: 900px){
  .hi{ font-size: clamp(32px, 7vw, 48px) }
  .sub{ font-size: clamp(17px, 2.8vw, 20px) }
}
@media (max-width: 820px){
  .top{ padding:10px clamp(10px,2vw,16px) }
  .right{ gap:8px }
  .btn{ padding:9px 12px; border-radius:10px }
}
@media (max-width: 600px){
  .brand img{ width: 120px }
  .panel{ right: 50%; transform: translateX(50%); width: min(92vw, 420px) }
}
@media (max-width: 400px){
  .btn{ width: 100% }
  .actions{ gap:8px }
}

/* ===== Dark mode (auto) ===== */
@media (prefers-color-scheme: dark){
  :root{
    --ink:#e5e7eb; --muted:#a3aab6; --stroke:#2b3340; --ring:#402328; --card:#0b1220;
  }
  body{ background:#0b1120; color:var(--ink) }
  .top{ background: rgba(10,16,28,.68); border-bottom:1px solid var(--stroke) }
  .badge{ background:#0e1628; border-color:#243145; color:#cbd5e1 }
  .ghost{ background:#0e1628; border-color:#402328; color:#ff9aa7 }
  .ghost:hover{ background:#131c30 }
  .bell{ background:#0e1628; border-color:#243145; color:#e5e7eb }
  .panel{ background:#0e1628; border-color:#402328 }
  .panel header{ background: linear-gradient(90deg,#261318,#1c2432); color:#ffd2d7 }
  .panel .item{ border-top-color:#2c1a1e }
  .hero{ border-bottom-color:#243145 }
  .media{ border-color:#3d1f26; box-shadow: 0 18px 44px rgba(0,0,0,.45) }
  .kpi{ background:#0f172a; border-color:#402328; box-shadow: 0 18px 44px rgba(0,0,0,.35) }
  .mod{ background:#0f172a; box-shadow: 0 18px 44px rgba(0,0,0,.35) }
  .mod .body{ background:#0f172a; border-top-color:#0b1120 }
  .q{ background:#0f172a; border-color:#243145; color:#e5e7eb }
  .q.cA{ background:#20141a; border-color:#402328 }
  .q.cB{ background:#141a26; border-color:#243145 }
  .q.cC{ background:#0f1a18; border-color:#183138 }
  .foot{ color:#7b8392 }
}

/* ===== Accesibilidad y movimiento reducido ===== */
@media (prefers-reduced-motion: reduce){
  *{ animation: none !important; transition: none !important; }
}
</style>
</head>
<body>

<!-- TOPBAR -->
<div class="top">
  <div class="brand">
    <img src="{{ asset('img/Logo4.jpg') }}" alt="Viajero Car Rental">
    <div class="badge"><span class="dot" id="net"></span> <span id="netText">En línea</span></div>
  </div>
  <div class="right">
    <div style="position:relative">
      <button class="bell" id="bell">🔔<span class="count" id="bellCount">0</span></button>
      <div class="panel" id="notif"><header>Notificaciones</header><div id="notifList"></div></div>
    </div>
    <span id="hello" class="muted"></span>
    <button id="logout" class="ghost btn">Salir</button>
  </div>
</div>

<!-- HERO -->
<section class="hero">
  <div class="wrap">
    <div class="wrow">
      <div>
        <div class="hi">Hola, <span id="who">colaborador</span> 👋</div>
        <p class="sub">Bienvenido(a) a tu panel. Elige un módulo o usa los atajos.</p>
        <div class="chips">
          <span class="chip c1">⚡ Rápido</span>
          <span class="chip c2">🔒 Seguro</span>
          <span class="chip c3">🎯 Productivo</span>
          <span class="chip c4" id="siteChip" style="display:none"></span>
        </div>
        <div class="actions">
          <button class="btn primary" id="goRentas">➕ Nuevo contrato</button>
          <button class="btn ghost" id="goAutos">🚗 Ver flotilla</button>
        </div>
      </div>
      <div>
        <div class="media">
          <img src="{{ asset('assets/media/audi.gif') }}" alt="Auto en movimiento">
        </div>
      </div>
    </div>
  </div>
</section>

<!-- MÓDULOS -->
<section class="section">
  <h3 style="margin:0 0 8px">Módulos</h3>
  <div class="grid">
    <!-- AUTOS: ahora sí usa route() de Laravel -->
    <article class="mod" id="modAutos" data-link="{{ route('rutaFlotilla') }}" data-theme="autos">
      <div class="head"><div class="ic">🚗</div> Autos</div>
      <div class="body">
        <p>Flotilla, mantenimiento, pólizas, carrocería y gastos.</p>
        <div class="go">Entrar →</div>
      </div>
    </article>

    <article class="mod" id="modRentas" data-link="{{ url('Rentas/panel.html') }}" data-theme="rentas">
      <div class="head"><div class="ic">🧾</div> Servicios</div>
      <div class="body">
        <p>Reservaciones, cotizaciones y seguimiento de contratos.</p>
        <div class="go">Entrar →</div>
      </div>
    </article>

    <article class="mod" id="modAdmin" data-link="{{ url('Administracion/admin-usuarios.html') }}" data-theme="admin">
      <div class="head"><div class="ic">⚙️</div> Administración</div>
      <div class="body">
        <p>Usuarios, roles/permisos, sedes, auditoría y seguridad.</p>
        <div class="go">Entrar →</div>
      </div>
    </article>
  </div>
</section>

<p class="foot">© Viajero Car Rental · Panel interno</p>

<script src="{{ asset('assets/session.js') }}"></script>
<script>
/* ===== Sesión ===== */
const u = (typeof getUser === 'function') ? getUser() : { name:'', role:'', email:'' };
if(!u){ window.location.href='{{ url('index.html') }}'; }
document.getElementById('hello').textContent = u.role || '';
document.getElementById('who').textContent = u.name || 'colaborador';
const site = localStorage.getItem('vc_site');
if(site){
  const el=document.getElementById('siteChip');
  el.style.display='inline-flex';
  el.textContent='📍 '+site;
}
document.getElementById('logout').onclick = ()=>{
  localStorage.removeItem('vc_user');
  window.location.href='{{ url('index.html') }}';
};

/* ===== Estado de red ===== */
const net = document.getElementById('net'), netText=document.getElementById('netText');
function setNet(on){
  net.style.background=on?'#10b981':'#ef4444';
  netText.textContent=on?'En línea':'Sin conexión';
}
setNet(navigator.onLine);
window.addEventListener('online',()=>setNet(true));
window.addEventListener('offline',()=>setNet(false));

/* ===== Navegación ===== */
document.querySelectorAll('.mod').forEach(el=>{
  el.addEventListener('click', ()=>{
    const href=el.getAttribute('data-link');
    if(href && href.trim() !== '') window.location.href = href;
  });
});

/* ===== Atajos + ripple ===== */
function ripple(e){
  const t=e.currentTarget, r=document.createElement('span');
  r.className='ripple';
  const rect=t.getBoundingClientRect();
  r.style.left=(e.clientX-rect.left)+'px';
  r.style.top=(e.clientY-rect.top)+'px';
  t.appendChild(r);
  setTimeout(()=>r.remove(),600);
}
document.getElementById('goRentas').onclick = (e)=>{
  ripple(e);
  window.location.href='{{ url('Rentas/activas.html') }}';
};
document.getElementById('goAutos').onclick  = (e)=>{
  ripple(e);
  window.location.href='{{ route('rutaDashboard') }}';
};

/* ===== KPIs (demo) ===== */
const k = { autos: 18, hoy: 3, alerts: 5, todos: 9 };
function countTo(el, val, ms=900){
  const start=+el.textContent||0, diff=val-start, t0=performance.now();
  (function anim(t){
    const p=Math.min(1,(t-t0)/ms);
    el.textContent = Math.round(start + diff*p);
    if(p<1) requestAnimationFrame(anim);
  })(t0);
}
document.querySelectorAll('[data-kpi]').forEach(el=>countTo(el, k[el.dataset.kpi]||0));

/* ===== Notificaciones (demo) ===== */
const alerts = [
  { tag:'orange', msg:'2 reservaciones por entregar hoy.' },
  { tag:'red',    msg:'Póliza de un vehículo está por vencer.' },
  { tag:'blue',   msg:'Nueva versión del módulo de Administración.' },
];
const list = document.getElementById('notifList');
list.innerHTML = alerts.map(a=>
  `<div class="item"><span class="tag ${a.tag}">
  ${a.tag==='red'?'Urgente':a.tag==='orange'?'Próximo':'Info'}</span>
  <div>${a.msg}</div></div>`
).join('');
document.getElementById('bellCount').textContent = alerts.length;
const bell=document.getElementById('bell'), panel=document.getElementById('notif');
bell.onclick=()=>panel.classList.toggle('show');
document.addEventListener('click',e=>{
  if(!bell.contains(e.target) && !panel.contains(e.target)) panel.classList.remove('show');
});

</script>
</body>
</html>
