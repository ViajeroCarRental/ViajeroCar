{{-- resources/views/layouts/Admin.blade.php --}}
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>@yield('Titulo')</title>

  {{-- Bootstrap (opcional, útil para utilidades) --}}
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

  {{-- Estilos del layout admin --}}
  <link rel="stylesheet" href="{{ asset('css/admin-layout.css') }}">
</head>
<body class="admin-body">
    <style>
        /* public/css/admin-layout.css */

/* Ajuste general */
.admin-body{ margin:0; background:#0f1113; color:#e9ecef; padding-top:56px; }

/* Topbar */
.admin-topbar{
  position: fixed; inset:0 0 auto 0; height:56px;
  display:flex; align-items:center; gap:12px; padding:0 16px;
  background:#121416; color:#f8f9fa; z-index:1030;
  border-bottom:1px solid rgba(255,255,255,.07);
}
.admin-topbar .brand{ font-weight:700; letter-spacing:.3px; }
.admin-topbar .topbar-slot{ margin-left:auto; }
.btn-toggle{
  background:#1f2328; color:#f8f9fa; border:0; padding:6px 10px;
  border-radius:8px; cursor:pointer; line-height:1;
}
.btn-toggle:hover{ background:#2a2f36; }

/* Sidebar */
.admin-sidebar{
  position: fixed; top:56px; left:0; width:260px;
  height:calc(100vh - 56px); background:#1c1f24; color:#e9ecef;
  box-shadow:2px 0 8px rgba(0,0,0,.35);
  overflow-y:auto; transition:transform .25s ease;
}
.admin-sidebar.is-collapsed{ transform:translateX(-100%); }
.sidebar-header{
  padding:16px; font-weight:700; font-size:1rem;
  border-bottom:1px solid rgba(255,255,255,.07);
}

/* Menú */
.menu{ padding:10px; }
.menu-link{
  width:100%; text-align:left; display:block;
  color:#e9ecef; background:transparent; border:0;
  padding:10px 12px; border-radius:8px; cursor:pointer;
  transition:background .2s,color .2s;
  text-decoration:none;
}
.menu-link:hover{ background:#2a2f36; color:#ffc107; }

/* Submenús */
.has-submenu{ margin-top:6px; }
.has-submenu .submenu{
  display:none; padding:6px 0 6px 8px;
}
.has-submenu.open .submenu{ display:block; }  /* para móvil con click */

@media (min-width: 992px){
  /* en desktop, mostrar también al pasar el mouse */
  .has-submenu:hover .submenu{ display:block; }
}

.submenu a{
  display:block; padding:8px 12px; margin:2px 0;
  border-radius:8px; color:#cfd6dd; text-decoration:none;
}
.submenu a:hover{ background:#323844; color:#ffc107; }

/* Contenido principal */
.admin-content{
  margin-left:260px; padding:20px; min-height:calc(100vh - 56px);
}
@media (max-width: 992px){
  .admin-content{ margin-left:0; }
}

    </style>

  {{-- Navbar superior (vacía) --}}
<header class="admin-topbar">
  <button id="sidebarToggle" class="btn-toggle" aria-label="Abrir/cerrar menú">☰</button>
  <div class="brand">@yield('TopbarTitulo','Panel Admin')</div>
  <div class="topbar-slot">
    @yield('TopbarContenido')
  </div>
</header>

{{-- Sidebar lateral con rutas correctas --}}
<aside class="admin-sidebar" id="adminSidebar">
  <div class="sidebar-header">Viajero Car · Admin</div>

  <nav class="menu">
    <a class="menu-link" href="{{ route('rutadashboardadmin') }}">Dashboard</a>

    <div class="has-submenu">
      <button class="menu-link" type="button">Reservas</button>
      <div class="submenu">
        <a href="{{ route('rutareservacionesadmin') }}">Reservaciones</a>
        <a href="{{ route('rutarentasadmin') }}">Rentas</a>
        <a href="{{ route('rutacontratosadmin') }}">Contratos</a>
      </div>
    </div>

    <div class="has-submenu">
      <button class="menu-link" type="button">Vehículos</button>
      <div class="submenu">
        <a href="{{ route('rutainventarioadmin') }}">Inventario</a>
        <a href="{{ route('rutacalendariodeocupacionadmin') }}">Calendario de Ocupación</a>
      </div>
    </div>

    <div class="has-submenu">
      <button class="menu-link" type="button">Clientes</button>
      <div class="submenu">
        <a href="{{ route('rutausuariosyrolesadmin') }}">Usuarios y Roles</a>
      </div>
    </div>

    <div class="has-submenu">
      <button class="menu-link" type="button">Facturación</button>
      <div class="submenu">
        <a href="{{ route('rutafacturasadmin') }}">Facturas</a>
        <a href="{{ route('rutapagosadmin') }}">Pagos</a>
        <a href="{{ route('rutareportesadmin') }}">Reportes</a>
      </div>
    </div>

    <a class="menu-link" href="{{ route('rutamembresiasadmin') }}">Membresías</a>

    <div class="has-submenu">
      <button class="menu-link" type="button">Configuración</button>
      <div class="submenu">
        <a href="{{ route('rutaconfiguracionadmin') }}">Configuración General</a>
        <a href="{{ route('rutaplantillasadmin') }}">Plantillas</a>
        <a href="{{ route('rutabitacoraadmin') }}">Bitácora</a>
      </div>
    </div>
  </nav>
</aside>


  {{-- Contenido específico de cada vista admin --}}

  <div class="container">
    @yield('contenidoBitacora')

  </div>

  <div class="container">
    @yield('contenidoCalendario')

  </div>

  <div class="container">
    @yield('contenidoConfiguracion')

  </div>

  <div class="container">
    @yield('contenidoContratos')

  </div>

  <div class="container">
    @yield('contenidoDashboard')

  </div>

  <div class="container">
    @yield('contenidoFacturas')

  </div>

  <div class="container">
    @yield('contenidoInventario')

  </div>

  <div class="container">
    @yield('contenidoPagos')

  </div>

  <div class="container">
    @yield('contenidoPlantillas')

  </div>

  <div class="container">
    @yield('contenidoRentas')

  </div>

  <div class="container">
    @yield('contenidoReportes')

  </div>

  <div class="container">
    @yield('contenidoReservaciones')

  </div>

  <div class="container">
    @yield('contenidoUsuariosYRoles')

  </div>

  <div class="container">
    @yield('contenidoMembresias')

  </div>

  {{-- Bootstrap JS (para helpers si los usas) --}}
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

  {{-- JS pequeño para toggles --}}
  <script>
    // Abrir/cerrar sidebar en móvil
    const btn = document.getElementById('sidebarToggle');
    const sidebar = document.getElementById('adminSidebar');
    if (btn && sidebar) {
      btn.addEventListener('click', () => {
        sidebar.classList.toggle('is-collapsed');
      });
    }
    // Abrir/cerrar submenús al hacer click en el botón (útil en móvil)
    document.querySelectorAll('.has-submenu > button.menu-link').forEach(btn=>{
      btn.addEventListener('click', e=>{
        e.currentTarget.parentElement.classList.toggle('open');
      });
    });
  </script>

  @stack('scripts')
</body>
</html>
