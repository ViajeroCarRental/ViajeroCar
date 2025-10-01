@extends('layouts.Admin')

@section('Titulo', 'Roles y Permisos')

{{-- CSS específico --}}
@section('css-vistaRoles')
<link rel="stylesheet" href="{{ asset('css/rolesAdmin.css') }}">
@endsection

{{-- CONTENIDO --}}
@section('contenidoRoles')
  <!-- Main -->
  <main class="main">
    <div class="top">
      <div class="h1">Administración</div>
      <button class="btn gray burger" id="burger">☰</button>
    </div>

    <div class="grid">
      <div>
        <h2 style="margin:0 0 4px; font-size:24px">Roles y permisos</h2>
        <div class="small">Perfiles de acceso por módulo y acción.</div>
      </div>

      <div class="stats">
        <div class="card">
          <div class="cnt">
            <div class="small">Total de roles</div>
            <div style="font-size:24px; font-weight:900" id="sRoles">0</div>
          </div>
        </div>

        <div class="card">
          <div class="cnt">
            <div class="small">Permisos configurados</div>
            <div style="font-size:24px; font-weight:900" id="sPerms">0</div>
          </div>
        </div>

        <div class="card">
          <div class="cnt">
            <div class="small">Rol por defecto</div>
            <div style="font-weight:900" id="sDefault">—</div>
          </div>
        </div>
      </div>

      <div style="display:flex; gap:8px; flex-wrap:wrap">
        <button class="btn primary" id="btnNew">➕ Nuevo rol</button>
        <button class="btn gray" id="btnExport">⬇️ Exportar JSON</button>

        <label class="btn ghost" style="cursor:pointer">
          ⬆️ Importar JSON
          <input type="file" id="fileImport" accept="application/json" style="display:none">
        </label>

        <button class="btn warn" id="btnSeed">🔁 Restablecer demo</button>
      </div>

      <div class="card">
        <div class="head">Roles configurados</div>
        <div class="cnt">
          <table class="table">
            <thead>
              <tr>
                <th>Rol</th>
                <th>Descripción</th>
                <th>Alcance</th>
                <th>Módulos</th>
                <th>Permisos</th>
                <th>Usuarios</th>
                <th>Acciones</th>
              </tr>
            </thead>
            <tbody id="tbody">
              <tr>
                <td colspan="7" style="padding:20px; text-align:center" class="small">
                  Aún no hay roles. Usa “Nuevo rol” o “Restablecer demo”.
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </main>

  <!-- MODAL: editor -->
  <div class="pop" id="rolePop">
    <div class="box">
      <header>
        <span id="roleTitle">Nuevo rol</span>
        <button class="btn gray" id="roleClose">✖</button>
      </header>

      <div class="form">
        <div>
          <label>Nombre del rol</label>
          <input id="rName" class="input" placeholder="Ej. Operador">
        </div>

        <div>
          <label>Descripción</label>
          <input id="rDesc" class="input" placeholder="Breve descripción">
        </div>

        <div>
          <label>Alcance</label>
          <select id="rScope" class="input">
            <option value="global">Global (todas las sedes)</option>
            <option value="sede">Sólo su sede</option>
          </select>
        </div>

        <div>
          <label>Módulos habilitados</label>
          <div class="mods">
            <label><input type="checkbox" data-mod="Reservaciones"> Reservaciones</label>
            <label><input type="checkbox" data-mod="Cotizaciones"> Cotizaciones</label>
            <label><input type="checkbox" data-mod="Activas"> Activas</label>
            <label><input type="checkbox" data-mod="Visor"> Visor</label>
            <label><input type="checkbox" data-mod="Historial"> Historial</label>
            <label><input type="checkbox" data-mod="Administración"> Administración</label>
            <label><input type="checkbox" data-mod="Reportes"> Reportes</label>
          </div>
          <div class="small" style="margin-top:4px">
            Sólo verán y podrán abrir los módulos marcados.
          </div>
        </div>

        <!-- PERMISOS -->
        <div class="permsWrap">
          <div class="group">
            <div class="ghead">
              <h4>Usuarios</h4>
              <label class="gtoggle">
                <input type="checkbox" data-gcheck="usuarios"> Marcar grupo
              </label>
            </div>
            <div class="gbody">
              <label><input type="checkbox" data-perm="users.view"> Ver</label>
              <label><input type="checkbox" data-perm="users.create"> Crear</label>
              <label><input type="checkbox" data-perm="users.edit"> Editar</label>
              <label><input type="checkbox" data-perm="users.delete"> Eliminar</label>
            </div>
          </div>

          <div class="group">
            <div class="ghead">
              <h4>Reservaciones</h4>
              <label class="gtoggle">
                <input type="checkbox" data-gcheck="reservas"> Marcar grupo
              </label>
            </div>
            <div class="gbody">
              <label><input type="checkbox" data-perm="res.create"> Crear</label>
              <label><input type="checkbox" data-perm="res.edit"> Editar</label>
              <label><input type="checkbox" data-perm="res.cancel"> Cancelar</label>
              <label><input type="checkbox" data-perm="res.price"> Modificar precios</label>
              <label><input type="checkbox" data-perm="res.close"> Cerrar contrato</label>
            </div>
          </div>

          <div class="group">
            <div class="ghead">
              <h4>Sedes</h4>
              <label class="gtoggle">
                <input type="checkbox" data-gcheck="sedes"> Marcar grupo
              </label>
            </div>
            <div class="gbody">
              <label><input type="checkbox" data-perm="sites.view"> Ver</label>
              <label><input type="checkbox" data-perm="sites.manage"> Administrar</label>
            </div>
          </div>

          <div class="group">
            <div class="ghead">
              <h4>Reportes</h4>
              <label class="gtoggle">
                <input type="checkbox" data-gcheck="reportes"> Marcar grupo
              </label>
            </div>
            <div class="gbody">
              <label><input type="checkbox" data-perm="rep.view"> Ver</label>
              <label><input type="checkbox" data-perm="rep.export"> Exportar</label>
            </div>
          </div>

          <div class="group">
            <div class="ghead">
              <h4>Administración</h4>
              <label class="gtoggle">
                <input type="checkbox" data-gcheck="admin"> Marcar grupo
              </label>
            </div>
            <div class="gbody">
              <label><input type="checkbox" data-perm="admin.roles"> Gestionar roles</label>
              <label><input type="checkbox" data-perm="admin.settings"> Configuración general</label>
            </div>
          </div>

          <div class="group">
            <div class="ghead">
              <h4>Todos</h4>
              <label class="gtoggle">
                <input type="checkbox" id="rAll"> Marcar todo
              </label>
            </div>
            <div class="gbody small">
              Activa o desactiva todos los permisos con un clic.
            </div>
          </div>
        </div>
      </div>

      <footer>
        <button class="btn gray" id="roleCancel">Cancelar</button>
        <button class="btn primary" id="roleSave">Guardar rol</button>
      </footer>
    </div>
  </div>

  <!-- MODAL: reasignación -->
  <div class="pop" id="reassignPop">
    <div class="box" style="width:min(520px,96vw)">
      <header>
        <span>Reasignar usuarios</span>
        <button class="btn gray" id="reassignClose">✖</button>
      </header>

      <div class="cnt" style="padding:10px 12px">
        <div class="small" id="reassignMsg">
          Este rol tiene usuarios. Selecciona un rol destino.
        </div>

        <div class="hr"></div>

        <label>Nuevo rol para <span id="reassignCount">0</span> usuario(s)</label>
        <select id="reassignRole" class="input"></select>
      </div>

      <footer>
        <button class="btn gray" id="reassignCancel">Cancelar</button>
        <button class="btn primary" id="reassignApply">Reasignar y eliminar</button>
      </footer>
    </div>
  </div>
@endsection

{{-- JS específico --}}
@section('js-vistaRoles')
<script src="{{ asset('js/roles.js') }}" defer></script>
@endsection
