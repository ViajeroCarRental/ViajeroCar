@extends('layouts.Admin')
@section('Titulo', 'Usuarios Admin')
    @section('css-vistaUsuariosAdmin')
        <link rel="stylesheet" href="{{ asset('css/usuariosAdmin.css') }}">
    @endsection
@section('contenidoUsuariosAdmin')
    <!-- MAIN -->
    <main class="main">
        <div class="top">
            <div class="h1">Administración</div>
                <button class="btn gray burger" id="burger">☰</button>
        </div>

    <div class="grid">
        <div>
            <h2 style="margin:0 0 6px;font-size:28px">Usuarios</h2>
            <div class="small">Alta, edición, roles y acceso a módulos.</div>
        </div>

      <!-- STATS -->
      <div class="stats">
        <div class="stat">
          <div class="title">Activos</div>
          <div class="num" id="sActivos">0</div>
        </div>
        <div class="stat">
          <div class="title">Invitaciones</div>
          <div class="num" id="sInvites">0</div>
        </div>
        <div class="stat">
          <div class="title">Admins</div>
          <div class="num" id="sAdmins">0</div>
        </div>
        <div class="stat">
          <div class="title">Desactivados</div>
          <div class="num" id="sOff">0</div>
        </div>
      </div>

      <!-- FILTERS + CTAs -->
      <div class="card">
        <div class="cnt">
          <div class="filters" style="margin-bottom:10px">
            <div style="position:relative">
              <input id="q" class="input" placeholder="🔎 Buscar por nombre o email">
            </div>
            <select id="fRol">
              <option value="">Todos los roles</option>
            </select>
            <select id="fSede">
              <option value="">Todas las sedes</option>
            </select>
          </div>

          <div style="display:flex;gap:10px;flex-wrap:wrap;margin-bottom:6px">
            <button class="btn primary" id="btnAdd">➕ Agregar usuario</button>
            <button class="btn ghost" id="btnInvite">✉️ Invitar</button>
            <button class="btn gray" id="btnExport">⬇️ Exportar CSV</button>
            <button class="btn warn" id="btnReset">🔁 Restablecer demo</button>
          </div>

          <!-- TABLE -->
          <div class="table-wrap">
            <table class="table" id="tbl">
              <thead>
                <tr>
                  <th>Nombre</th><th>Email</th><th>Rol</th><th>Sede</th><th>Módulos</th><th>Estatus</th><th>Acciones</th>
                </tr>
              </thead>
              <tbody id="tbody">
                <tr><td colspan="7"><div class="empty">Aún no hay usuarios. Usa “Agregar usuario” o “Restablecer demo”.</div></td></tr>
              </tbody>
            </table>
          </div>

        </div>
      </div>
    </div>
  </main>
</div>

<!-- MODAL: Add/Edit -->
<div class="pop" id="userPop">
  <div class="box">
    <header><span id="userPopTitle">Nuevo usuario</span><button class="btn gray" id="userClose">✖</button></header>
    <div class="form">
      <div><label>Nombre</label><input class="input" id="uNombre" placeholder="Nombre"></div>
      <div><label>Apellidos</label><input class="input" id="uApellidos" placeholder="Apellidos"></div>
      <div><label>Email</label><input class="input" id="uEmail" placeholder="email@dominio.com"></div>
      <div><label>Sede</label><select id="uSede" class="input"></select></div>
      <div><label>Rol</label><select id="uRol" class="input"></select></div>
      <div>
        <label>Módulos (Ctrl/⌘ para multiselección)</label>
        <select id="uMods" multiple size="5" class="input">
          <option>Reservaciones</option>
          <option>Cotizaciones</option>
          <option>Activas</option>
          <option>Visor</option>
          <option>Historial</option>
          <option>Administración</option>
          <option>Reportes</option>
        </select>
      </div>
      <div>
        <label>Estatus</label>
        <select id="uStatus" class="input">
          <option value="activo">Activo</option>
          <option value="invitacion">Invitación</option>
          <option value="desactivado">Desactivado</option>
        </select>
      </div>
    </div>
    <footer>
      <button class="btn gray" id="userCancel">Cancelar</button>
      <button class="btn primary" id="userSave">Guardar</button>
    </footer>
  </div>
</div>

    @section('js-vistaUsuariosAdmin')
        <script src="{{ asset('js/usuariosAdmin.js') }}"></script>

    @endsection
@endsection
