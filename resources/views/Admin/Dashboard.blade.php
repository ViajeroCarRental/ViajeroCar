<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8" />
<link rel="icon" type="image/png" href="{{ asset('img/image.png') }}">
<title>Panel | Viajero Car Rental</title>
<meta name="viewport" content="width=device-width, initial-scale=1" />
<link rel="stylesheet" href="{{ asset('assets/style.css') }}" />
<style>
/* ====== Fuente + Render ====== */
@import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;800&display=swap');

:root{
  /* Colores base */
  --ink:#0f172a; --muted:#6b7280; --stroke:rgba(255,255,255,.22); --ring:rgba(255,255,255,.35); --card:rgba(255,255,255,.08);

  /* Marca */
  --red:#E50914; --red2:#ff4d5a;
  --orange:#ff7a1a; --amber:#f59e0b;
  --violet:#8b5cf6; --indigo:#6366f1; --sky:#0ea5e9;
  --emerald:#10b981; --teal:#14b8a6; --pink:#ec4899; --rose:#ff3b5c;

  /* UI */
  --radius: 16px;
  --shadow-sm: 0 10px 30px rgba(2,6,23,.18);
  --shadow-md: 0 18px 48px rgba(2,6,23,.22);
  --shadow-lg: 0 28px 80px rgba(2,6,23,.28);

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
  margin:0; color:var(--ink);
  font-family:'Inter', ui-sans-serif, system-ui, -apple-system, "Segoe UI", Roboto, "Helvetica Neue", Arial, "Noto Sans";
  font-size: var(--fs-16);
  line-height: 1.55;
  text-rendering: optimizeLegibility;
  -webkit-font-smoothing: antialiased; -moz-osx-font-smoothing: grayscale;

  /* FONDO LIQUID-GLASS */
  min-height:100svh;
  background:
    radial-gradient(1200px 600px at 10% -10%, rgba(255, 112, 128, .32), transparent 60%),
    radial-gradient(1000px 520px at 110% 0%, rgba(99, 102, 241, .28), transparent 60%),
    radial-gradient(900px 560px at 50% 120%, rgba(14,165,233,.26), transparent 60%),
    linear-gradient(180deg, #0b1224 0%, #0a0f1f 35%, #0d1327 100%);
  position:relative;
  overflow-x:hidden;
}

/* Grano sutil + rejilla */
body::before{
  content:""; position:fixed; inset:0; pointer-events:none; z-index:0;
  background:
    radial-gradient(circle at 25% 15%, rgba(255,255,255,.06), transparent 30%),
    radial-gradient(circle at 75% 85%, rgba(255,255,255,.04), transparent 30%),
    repeating-linear-gradient(0deg, rgba(255,255,255,.03) 0 1px, transparent 1px 32px),
    repeating-linear-gradient(90deg, rgba(255,255,255,.025) 0 1px, transparent 1px 32px);
  mix-blend-mode: overlay;
}

/* ====== Utilidades de VIDRIO (liquid glass) ====== */
.glass{
  background: linear-gradient(180deg, rgba(255,255,255,.18), rgba(255,255,255,.06)) border-box;
  backdrop-filter: blur(14px) saturate(120%);
  -webkit-backdrop-filter: blur(14px) saturate(120%);
  border:1px solid var(--ring);
  border-radius:var(--radius);
  position:relative;
  overflow:hidden;
  box-shadow: var(--shadow-sm);
}
.glass::before{ /* borde interior suave */
  content:""; position:absolute; inset:0; pointer-events:none;
  background:linear-gradient(180deg, rgba(255,255,255,.55), rgba(255,255,255,0) 28%) border-box;
  mask:linear-gradient(#000,#000) exclude, linear-gradient(#000,#000);
  mask-composite: exclude; /* soporte moderno */
  border-radius:inherit;
  opacity:.55;
}
.gloss{ /* brillo diagonal */
  position:absolute; inset:-40% -40% auto -40%; height:160%;
  background:linear-gradient(120deg, rgba(255,255,255,.22), rgba(255,255,255,0) 45%);
  transform:rotate(-8deg);
  pointer-events:none;
}

/* ===== Topbar ===== */
.top{
  display:flex; justify-content:space-between; align-items:center;
  padding:12px clamp(12px,2.5vw,22px);
  position:sticky; top:14px; z-index:20; border:0;
  margin-inline:auto; width:min(1200px,94%);
}
.top::before{ /* sombra suave bajo topbar */
  content:""; position:absolute; inset:0; border-radius:20px; z-index:-2;
  box-shadow: 0 24px 80px rgba(2,6,23,.45);
  opacity:.55;
}
.top .brand img{width: clamp(110px,16vw,140px); border-radius:12px; display:block}
.badge{
  display:inline-flex; gap:8px; align-items:center;
  padding:6px 10px; border:1px solid rgba(255,255,255,.28);
  border-radius:999px; color:#e5e7eb; font-weight:700; font-size: var(--fs-12);
  background:linear-gradient(180deg, rgba(255,255,255,.10), rgba(255,255,255,.04));
  backdrop-filter: blur(10px);
}
.dot{width:8px; height:8px; border-radius:999px; background:#10b981; box-shadow:0 0 0 3px rgba(255,255,255,.18)}
.right{display:flex; gap:10px; align-items:center}

.btn{
  border:1px solid rgba(255,255,255,.25);
  padding:10px 14px; border-radius:12px; font-weight:800; cursor:pointer;
  font-size: var(--fs-14);
  transition: transform .12s ease, box-shadow .25s ease, filter .2s ease, background .2s ease, color .2s ease;
  will-change: transform;
  background:linear-gradient(180deg, rgba(255,255,255,.14), rgba(255,255,255,.06));
  color:#f8fafc; backdrop-filter: blur(10px);
}
.btn:active{ transform: translateY(1px) }
.btn:focus-visible{ outline: 3px solid rgba(99,102,241,.45); outline-offset:2px }

.ghost{
  background:linear-gradient(180deg, rgba(255,255,255,.12), rgba(255,255,255,.05));
  color:#ffd2d7; border-color:rgba(255, 71, 87, .35);
}
.ghost:hover{ box-shadow: var(--shadow-sm); filter:saturate(1.05) }

.primary{
  border-color:rgba(255,255,255,.35);
  color:#0b1224; font-weight:900;
  background:
    radial-gradient(120% 220% at 0% 0%, rgba(255,255,255,.9) 0%, rgba(255,255,255,.75) 40%, rgba(255,255,255,.55) 60%, transparent 75%),
    linear-gradient(135deg, rgba(255,82,93,.95), rgba(229,9,20,.95));
  box-shadow:0 16px 42px rgba(229,9,20,.35);
}
.primary:hover{ box-shadow:0 22px 58px rgba(229,9,20,.45); filter:saturate(1.05) }

/* Bell + panel (glass) */
.bell{
  position:relative; border:1px solid rgba(255,255,255,.28);
  background:linear-gradient(180deg, rgba(255,255,255,.16), rgba(255,255,255,.06));
  border-radius:12px; padding:9px 11px; cursor:pointer; font-size: var(--fs-16);
  transition: box-shadow .25s ease, transform .12s ease, filter .2s ease; color:#e5e7eb;
  backdrop-filter: blur(10px);
}
.bell:hover{ box-shadow: var(--shadow-sm); filter:brightness(1.08) }
.bell .count{
  position:absolute; top:-6px; right:-6px; background:linear-gradient(135deg,#ff4d5a,#E50914); color:#fff;
  font-weight:800; font-size:12px; padding:2px 6px; border-radius:999px; box-shadow:0 0 0 3px rgba(11,18,36,.9)
}
.panel{
  position:absolute; right:0; top:46px; width:min(420px,92vw);
  border-radius:16px; display:none; overflow:hidden; z-index:60;
  border:1px solid rgba(255,255,255,.28);
  background:linear-gradient(180deg, rgba(255,255,255,.14), rgba(255,255,255,.06));
  backdrop-filter: blur(16px) saturate(120%); color:#e5e7eb; box-shadow: var(--shadow-lg);
}
.panel.show{ display:block }
.panel header{
  padding:12px 14px; background:linear-gradient(90deg, rgba(255,225,228,.18), rgba(255,245,220,.14));
  color:#ffd2d7; font-weight:800; font-size: var(--fs-14)
}
.panel .item{
  display:flex; gap:10px; padding:12px 14px; border-top:1px solid rgba(255,255,255,.12); font-size: var(--fs-14);
  color:#e2e8f0;
}
.tag{font-size:11px; padding:2px 8px; border-radius:999px; border:1px solid rgba(255,255,255,.22); background:rgba(255,255,255,.08)}
.tag.red{background:rgba(255,77,90,.18); color:#ffd2d7}
.tag.orange{background:rgba(255,190,120,.16); color:#ffedd5}
.tag.blue{background:rgba(99,102,241,.18); color:#e0e7ff}

/* ===== Hero liquid ===== */
.hero{
  position:relative; overflow:hidden; border:0; padding-block: 10px;
  background: transparent;
}
.hero::before{
  content:""; position:absolute; inset:auto -20% -32% -20%; height:420px; z-index:0;
  background:
    radial-gradient(60% 120% at 20% 50%, rgba(255,157,169,.35), transparent 60%),
    radial-gradient(60% 120% at 80% 45%, rgba(14,165,233,.28), transparent 60%),
    linear-gradient(90deg, rgba(255,255,255,.06), rgba(255,255,255,0));
  filter: blur(40px);
}

/* BURBUJAS LIQUIDAS animadas */
.hero::after{
  content:""; position:absolute; inset:0; z-index:-1; pointer-events:none;
  background:
    radial-gradient(120px 160px at 12% 30%, rgba(255, 112, 128,.28), transparent 60%),
    radial-gradient(150px 170px at 86% 20%, rgba(99,102,241,.22), transparent 60%),
    radial-gradient(140px 180px at 75% 86%, rgba(16,185,129,.20), transparent 60%);
  animation: floaty 14s ease-in-out infinite alternate;
}
@keyframes floaty{
  0%{ transform: translateY(0) translateX(0) }
  100%{ transform: translateY(-22px) translateX(8px) }
}

.wrap{max-width:1200px; margin:0 auto; padding:18px clamp(14px,2.4vw,32px) 32px; position:relative; z-index:1}
.wrow{display:grid; grid-template-columns:1.1fr .9fr; gap: clamp(12px,2.2vw,20px)}
@media (max-width:980px){ .wrow{grid-template-columns:1fr} }

/* Títulos */
.hi{
  font-size: clamp(36px, 6vw, 56px);
  font-weight: 900; margin: 8px 0 12px; letter-spacing:.4px; line-height:1.15;
  color:#f8fafc;
  text-shadow: 0 6px 28px rgba(0,0,0,.35);
}
.hi span{
  background: linear-gradient(90deg, var(--red), var(--red2));
  -webkit-background-clip: text; background-clip: text; color: transparent;
  filter: drop-shadow(0 8px 28px rgba(255,77,90,.35));
}
.sub{
  font-size: clamp(18px, 2.4vw, 22px);
  font-weight: 500; color:#e2e8f0; max-width: 680px; line-height:1.5; opacity:.95;
}
.chips{display:flex; gap:10px; flex-wrap:wrap}
.chip{
  display:inline-flex; gap:6px; align-items:center; padding:6px 10px; border-radius:999px;
  font-weight:800; border:1px solid rgba(255,255,255,.28); font-size: var(--fs-12);
  color:#e5e7eb; background:linear-gradient(180deg, rgba(255,255,255,.14), rgba(255,255,255,.06)); backdrop-filter: blur(10px);
}
.chip.c1{box-shadow: inset 0 0 0 9999px rgba(255,77,90,.06)}
.chip.c2{box-shadow: inset 0 0 0 9999px rgba(255,180,120,.06)}
.chip.c3{box-shadow: inset 0 0 0 9999px rgba(120,140,255,.06)}
.chip.c4{box-shadow: inset 0 0 0 9999px rgba(120,255,220,.06)}

.actions{display:flex; gap:10px; margin-top:12px; flex-wrap:wrap}
.actions .primary{position:relative; overflow:hidden}

/* Ripple */
.ripple{
  position:absolute; width:16px; height:16px; border-radius:999px; background:rgba(255,255,255,.55);
  transform:scale(0); animation:rip .6s ease-out forwards; pointer-events:none
}
@keyframes rip{to{transform:scale(16); opacity:0}}

.media{
  width:100%; aspect-ratio:16/9; border-radius:18px; overflow:hidden; border:1px solid rgba(255,255,255,.28);
  background:linear-gradient(180deg, rgba(255,255,255,.10), rgba(255,255,255,.04));
  position:relative; box-shadow: var(--shadow-md);
  backdrop-filter: blur(10px);
}
.media img,.media video{width:100%; height:100%; object-fit:cover; display:block; filter:saturate(1.02) contrast(1.02)}
.media::after{content:""; position:absolute; inset:0; background:linear-gradient(180deg, rgba(0,0,0,.06), rgba(0,0,0,.18))}

/* ===== KPIs (ahora de vidrio) ===== */
.kpis{display:grid; grid-template-columns:repeat(4,minmax(0,1fr)); gap: clamp(10px,1.4vw,14px); margin-top:18px}
@media (max-width:1100px){ .kpis{grid-template-columns:1fr 1fr} }
@media (max-width:640px){ .kpis{grid-template-columns:1fr} }
.kpi{
  border-radius:16px; padding:14px; position:relative; overflow:hidden;
  border:1px solid rgba(255,255,255,.28);
  background:linear-gradient(180deg, rgba(255,255,255,.14), rgba(255,255,255,.06));
  backdrop-filter: blur(12px); box-shadow: var(--shadow-sm); color:#e5e7eb;
}
.kpi h4{margin:0 0 6px; color:#cbd5e1; font-size: var(--fs-12); letter-spacing:.2px}
.kpi b{font-size: var(--fs-28); font-weight:900; color:#fff}
.kpi::before{
  content:""; position:absolute; left:0; right:0; top:0; height:3px;
  background:var(--kpiGrad, linear-gradient(90deg,#ffa3ab,#ff4d5a,#ffa3ab));
  background-size:200% 100%; animation:slide 6s linear infinite;
  opacity:.9;
}
@keyframes slide{to{background-position:200% 0}}
.kpi.k1{ --kpiGrad: linear-gradient(90deg,#ff9aa7,#ff4d5a,#ff9aa7) }
.kpi.k2{ --kpiGrad: linear-gradient(90deg,#ffd08a,#ff7a1a,#ffd08a) }
.kpi.k3{ --kpiGrad: linear-gradient(90deg,#9ec3ff,#6366f1,#9ec3ff) }
.kpi.k4{ --kpiGrad: linear-gradient(90deg,#a7f3d0,#10b981,#a7f3d0) }

/* ===== Módulos (cards de vidrio con encabezado degradado) ===== */
.section{max-width:1200px; margin:22px auto; padding:0 clamp(14px,2.4vw,18px)}
.grid{display:grid; grid-template-columns:repeat(3,minmax(0,1fr)); gap: clamp(12px,1.6vw,16px)}
@media (max-width:980px){ .grid{grid-template-columns:1fr 1fr} }
@media (max-width:640px){ .grid{grid-template-columns:1fr} }

.mod{
  border-radius:18px; overflow:hidden; cursor:pointer; position:relative; transform:translateZ(0);
  transition: transform .18s ease, box-shadow .25s ease, filter .25s ease, outline .2s ease;
  border:1px solid rgba(255,255,255,.28);
  background:linear-gradient(180deg, rgba(255,255,255,.14), rgba(255,255,255,.06));
  backdrop-filter: blur(12px); box-shadow: var(--shadow-sm); color:#e5e7eb;
}
@media (hover:hover){
  .mod:hover{transform:translateY(-2px); box-shadow: var(--shadow-md); filter:saturate(1.06)}
}
.mod:focus-within, .mod:focus-visible{ outline: 3px solid rgba(99,102,241,.35); outline-offset:2px }
.mod .head{
  padding:14px 16px; color:#fff; font-weight:900; letter-spacing:.2px; display:flex; align-items:center; gap:10px; font-size: var(--fs-18);
  border-bottom:1px solid rgba(255,255,255,.16);
}
.mod .ic{
  width:38px; height:38px; border-radius:12px; display:grid; place-items:center; font-size:20px;
  background:rgba(255,255,255,.18); border:1px solid rgba(255,255,255,.35)
}
.mod .body{ padding:16px }
.mod .body p{margin:0; color:#e2e8f0; font-size: var(--fs-14)}
.mod .go{margin-top:12px; display:inline-flex; gap:8px; align-items:center; color:#fff; font-weight:900; font-size: var(--fs-14)}
.mod.locked{opacity:.6; cursor:not-allowed}
.mod.locked::after{content:"🔒 Sin acceso"; position:absolute; right:12px; top:12px; font-weight:800; color:#fff; font-size: var(--fs-12)}

/* Gradientes por tema (líquidos) */
.mod[data-theme="autos"]  .head{ background:linear-gradient(120deg,#ff7a1a 0%, #ff0022 60%, #ff3355 100%) }
.mod[data-theme="rentas"] .head{ background:linear-gradient(120deg,#6366f1 0%, #ff0033 60%, #ff2244 100%) }
.mod[data-theme="admin"]  .head{ background:linear-gradient(120deg,#10b981 0%, #ff0022 60%, #ff2244 100%) }

/* Quick links (chips de vidrio) */
.qwrap{display:flex; gap:10px; flex-wrap:wrap; margin-top:10px}
.q{
  display:inline-flex; gap:8px; align-items:center; padding:10px 12px; border-radius:12px;
  background:linear-gradient(180deg, rgba(255,255,255,.14), rgba(255,255,255,.06));
  border:1px solid rgba(255,255,255,.28); font-weight:800; font-size: var(--fs-12); color:#e5e7eb;
  backdrop-filter: blur(10px);
}
.q.cA{box-shadow: inset 0 0 0 9999px rgba(255,77,90,.06)}
.q.cB{box-shadow: inset 0 0 0 9999px rgba(120,140,255,.06)}
.q.cC{box-shadow: inset 0 0 0 9999px rgba(120,255,220,.06)}

/* Footer */
.foot{
  max-width:1200px; margin:18px auto 28px; padding:0 clamp(14px,2.4vw,18px);
  color:#cbd5e1; font-size: var(--fs-12)
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

/* ===== Modo claro automático (si alguien lo fuerza) ===== */
@media (prefers-color-scheme: light){
  body{
    background:
      radial-gradient(1200px 600px at 10% -10%, rgba(255, 112, 128, .18), transparent 60%),
      radial-gradient(1000px 520px at 110% 0%, rgba(99, 102, 241, .16), transparent 60%),
      radial-gradient(900px 560px at 50% 120%, rgba(14,165,233,.14), transparent 60%),
      linear-gradient(180deg, #f6f8ff 0%, #f5f7ff 35%, #f0f4ff 100%);
    color:#0f172a;
  }
  .hi{ color:#0b1224; text-shadow:none }
  .sub,.q,.chip,.kpi,.mod,.panel,.btn,.bell,.media{
    color:#0f172a;
    border-color: rgba(0,0,0,.08);
    background:linear-gradient(180deg, rgba(255,255,255,.9), rgba(255,255,255,.75));
    backdrop-filter: blur(12px) saturate(120%);
    -webkit-backdrop-filter: blur(12px) saturate(120%);
    box-shadow: 0 14px 38px rgba(16,24,40,.12);
  }
  .badge{ color:#1f2937; border-color: rgba(0,0,0,.08); background:linear-gradient(180deg,#fff,#fafafa) }
  .primary{ color:#0b1224 }
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
    <img src="{{ asset('img/image.png') }}" alt="Viajero Car Rental">
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
        <div class="hi">Hola, <span id="who">colaborador</span> 🫡</div>
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
          <img src="{{ asset('img/audi.gif') }}" alt="Auto en movimiento">
        </div>
      </div>
    </div>
  </div>
</section>

<!-- MÓDULOS -->
<section class="section">
  <h3 style="margin:0 0 8px">Módulos</h3>
  <div class="grid">

    <!-- 🚗 Flotilla -->
    <article class="mod" id="modAutos" 
             data-link="{{ route('rutaFlotilla') }}" 
             data-theme="autos">
      <div class="head"><div class="ic">🚗</div>Flotilla</div>
      <div class="body">
        <p>Flotilla, mantenimiento, pólizas, carrocería y gastos.</p>
        <div class="go">Entrar →</div>
      </div>
    </article>

    <!-- 🧾 Rentas -->
    <article class="mod" id="modRentas" 
             data-link="{{ route('rutaInicioVentas') }}" 
             data-theme="rentas">
      <div class="head"><div class="ic">🧾</div>Rentas</div>
      <div class="body">
        <p>Reservaciones, cotizaciones y seguimiento de contratos.</p>
        <div class="go">Entrar →</div>
      </div>
    </article>

    <!-- ⚙️ Administración -->
    <article class="mod" id="modAdmin" 
             data-link="{{ route('rutaUsuarios') }}" 
             data-theme="admin">
      <div class="head"><div class="ic">⚙️</div>Administración</div>
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

  // Bloquea el menú contextual (clic derecho)
  document.addEventListener('contextmenu', function(e) {
    e.preventDefault();
  });
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
  window.location.href='{{ route('rutaInicioVentas') }}';
};
document.getElementById('goAutos').onclick  = (e)=>{
  ripple(e);
  window.location.href='{{ route('rutaFlotilla') }}';
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
