<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8" />
<title>Panel | Viajero Car Rental</title>
<meta name="viewport" content="width=device-width, initial-scale=1" />

<link rel="stylesheet" href="{{ asset('assets/style.css') }}" />
<link rel="stylesheet" href="{{ asset('css/dashboard.css') }}" />
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
<link rel="icon" href="{{ asset('img/Icono.ico') }}" type="image/x-icon">
<link rel="shortcut icon" href="{{ asset('img/Icono.ico') }}" type="image/x-icon">
</head>

<body>
<!-- ===== NAVBAR ===== -->
<div class="top">
  <div class="brand">
    <img src="{{ asset('img/LogoR.png') }}" alt="Viajero Car Rental">
  </div>

  <div class="right">
    <span id="hello" class="muted"></span>

    <button id="logout" class="ghost btn">
      <i class="fa-solid fa-right-from-bracket"></i>
      <span>Salir</span>
    </button>
  </div>
</div>

<!-- ===== HERO ===== -->
<section class="hero">
  <div class="wrap">
    <div class="wrow">
      <div>
        <div class="hi">
          Bienvenido(a), <span id="who">colaborador</span> 
        </div>

        <p class="sub">
          Bienvenido(a) a tu panel.
        </p>
      </div>
    </div>
  </div>
</section>

<!-- ===== MÓDULOS ===== -->
<section class="section">
  <h3 class="title-modulos">Módulos</h3>

  <div class="grid">

    <!-- FLOTILLA -->
    <article class="mod" id="modAutos" data-link="{{ route('rutaFlotilla') }}">
      <div class="head">
        <div class="ic">
          <i class="fa-solid fa-car-side"></i>
        </div>
        Flotilla
      </div>

      <div class="body">
        <p>Flotilla, mantenimiento, pólizas, carrocería y gastos.</p>
        <div class="go">Entrar →</div>
      </div>
    </article>

    <!-- RENTAS -->
    <article class="mod" id="modRentas" data-link="{{ route('rutaInicioVentas') }}">
      <div class="head">
        <div class="ic">
          <i class="fa-regular fa-file-lines"></i>
        </div>
        Rentas
      </div>

      <div class="body">
        <p>Reservaciones, cotizaciones y seguimiento de contratos.</p>
        <div class="go">Entrar →</div>
      </div>
    </article>

    <!-- ADMIN -->
    <article class="mod" id="modAdmin" data-link="{{ route('admin.usuarios.index') }}">
      <div class="head">
        <div class="ic">
          <i class="fa-solid fa-gear"></i>
        </div>
        Administración
      </div>

      <div class="body">
        <p>Usuarios, roles/permisos, sedes, auditoría y seguridad.</p>
        <div class="go">Entrar →</div>
      </div>
    </article>

  </div>
</section>

<!-- ===== FOOTER ===== -->
<p class="foot">
  © Viajero Car Rental · Panel interno
</p>

<!-- ===== JS ===== -->
<script src="{{ asset('assets/session.js') }}"></script>

<script>
/* ==============================
   🔐 Sesión
============================== */
document.getElementById('hello').textContent = "{{ session('rol') }}";
document.getElementById('who').textContent = "{{ session('nombre') ?? 'colaborador' }}";

/* ==============================
   🚪 Logout
============================== */
document.getElementById('logout').onclick = () => {
  const logoutForm = document.getElementById('logoutForm');
  if (logoutForm) logoutForm.submit();
};

/* ==============================
   🧭 Navegación módulos
============================== */
document.querySelectorAll('.mod').forEach(el => {
  el.addEventListener('click', () => {
    const href = el.getAttribute('data-link');

    if (href && !el.classList.contains('locked')) {
      window.location.href = href;
    }
  });
});

/* ==============================
   🔒 Control por rol
============================== */
document.addEventListener("DOMContentLoaded", () => {
  const rol = "{{ session('rol') }}";

  const modAutos  = document.getElementById("modAutos");
  const modRentas = document.getElementById("modRentas");
  const modAdmin  = document.getElementById("modAdmin");

  switch (rol) {
    case "SuperAdmin":
      break;

    case "Flotilla":
      modRentas.classList.add("locked");
      modAdmin.classList.add("locked");
      break;

    case "Ventas":
      modAutos.classList.add("locked");
      modAdmin.classList.add("locked");
      break;

    case "Usuario":
    default:
      window.location.href = "{{ route('rutaHome') }}";
      break;
  }
});
</script>

<!-- FORM LOGOUT -->
<form id="logoutForm" action="{{ route('logout') }}" method="POST" style="display:none;">
  @csrf
</form>

</body>
</html>