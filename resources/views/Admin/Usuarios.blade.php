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
                <h2 style="margin:0 0 6px;font-size:28px">Usuarios del sistema</h2>
                <div class="small">
                    Alta, edición, roles y acceso a módulos.  
                    Aquí se muestran los usuarios administrativos y los clientes registrados.
                </div>
            </div>

            <!-- STATS -->
            <div class="stats">
                <div class="stat">
                    <div class="title">Activos</div>
                    <div class="num" id="sActivos">{{ $totales['activos'] ?? 0 }}</div>
                </div>
                <div class="stat">
                    <div class="title">Invitaciones</div>
                    <div class="num" id="sInvites">{{ $totales['invitaciones'] ?? 0 }}</div>
                </div>
                <div class="stat">
                    <div class="title">Admins</div>
                    <div class="num" id="sAdmins">{{ $totales['admins'] ?? 0 }}</div>
                </div>
                <div class="stat">
                    <div class="title">Desactivados</div>
                    <div class="num" id="sOff">{{ $totales['desactivados'] ?? 0 }}</div>
                </div>
            </div>

            <!-- ==========================
                 BLOQUE 1: USUARIOS ADMIN
            =========================== -->
            <div class="card">
                <div class="cnt">
                    <div class="head" style="display:flex;justify-content:space-between;align-items:center;gap:10px">
                        <div>
                            <h3 style="margin:0">Usuarios administrativos</h3>
                            <div class="small">
                                Usuarios con uno o más roles asignados (acceso al panel).
                            </div>
                        </div>
                        <div style="display:flex;gap:8px;flex-wrap:wrap">
                            <button class="btn primary" id="btnAdd">➕ Agregar usuario</button>
                            <button class="btn ghost" id="btnExport">⬇️ Exportar CSV</button>
                        </div>
                    </div>

                    <!-- FILTERS -->
                    <div class="filters" style="margin:10px 0 16px">
                        <div style="position:relative;flex:1">
                            <input id="q" class="input" placeholder="🔎 Buscar por nombre o email">
                        </div>

                        <select id="fRol" class="input">
                            <option value="">Todos los roles</option>
                            @foreach(($roles ?? []) as $rol)
                                <option value="{{ $rol->id_rol }}">{{ $rol->nombre }}</option>
                            @endforeach
                        </select>

                        <select id="fSede" class="input">
                            <option value="">Todas las sedes</option>
                            {{-- Placeholder, no hay campo sede en BD --}}
                        </select>
                    </div>

                    <!-- TABLE: ADMIN USERS -->
                    <div class="table-wrap">
                        <table class="table" id="tbl">
                            <thead>
                                <tr>
                                    <th>Nombre</th>
                                    <th>Email</th>
                                    <th>Teléfono</th>
                                    <th>Rol(es)</th>
                                    <th>Estatus</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody id="tbodyAdmins">
                                @forelse(($admins ?? []) as $admin)
                                    <tr
                                        data-id="{{ $admin->id_usuario }}"
                                        data-nombres="{{ $admin->nombres }}"
                                        data-apellidos="{{ $admin->apellidos }}"
                                        data-correo="{{ $admin->correo }}"
                                        data-numero="{{ $admin->numero }}"
                                        data-rol-id="{{ $admin->rol_id_principal }}"
                                        data-activo="{{ $admin->activo ? 1 : 0 }}"
                                    >
                                        <td>{{ $admin->nombres }} {{ $admin->apellidos }}</td>
                                        <td>{{ $admin->correo }}</td>
                                        <td>{{ $admin->numero ?? '—' }}</td>
                                        <td>{{ $admin->roles }}</td>
                                        <td>
                                            @if ($admin->activo)
                                                <span class="status status-ok">Activo</span>
                                            @else
                                                <span class="status status-off">Desactivado</span>
                                            @endif
                                        </td>
                                        <td>
                                            <button class="btn tiny btn-edit-user">✏️ Editar</button>
                                            <button class="btn tiny danger btn-delete-user">🗑 Eliminar</button>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6">
                                            <div class="empty">
                                                Aún no hay usuarios administrativos con roles asignados.
                                            </div>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- ==========================
                 BLOQUE 2: CLIENTES
            =========================== -->
            <div class="card">
                <div class="cnt">
                    <div class="head" style="display:flex;justify-content:space-between;align-items:center;gap:10px">
                        <div>
                            <h3 style="margin:0">Clientes registrados</h3>
                            <div class="small">
                                Usuarios finales que se registran en la web para hacer reservaciones.
                            </div>
                        </div>
                    </div>

                    <div class="table-wrap" style="margin-top:12px">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Nombre</th>
                                    <th>Email</th>
                                    <th>Teléfono</th>
                                    <th>País</th>
                                    <th>Tipo</th>
                                    <th>Estatus</th>
                                    <th>Verificación</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse(($clientes ?? []) as $cli)
                                    <tr>
                                        <td>{{ $cli->nombres }} {{ $cli->apellidos }}</td>
                                        <td>{{ $cli->correo }}</td>
                                        <td>{{ $cli->numero ?? '—' }}</td>
                                        <td>{{ $cli->pais ?? '—' }}</td>
                                        <td>
                                            @if ($cli->miembro_preferente)
                                                <span class="badge badge-preferente">Preferente</span>
                                            @else
                                                <span class="badge">Regular</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if ($cli->activo)
                                                <span class="status status-ok">Activo</span>
                                            @else
                                                <span class="status status-off">Inactivo</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if ($cli->email_verificado)
                                                <span class="status status-ok">Verificado</span>
                                            @else
                                                <span class="status status-warn">Sin verificar</span>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7">
                                            <div class="empty">
                                                Aún no hay clientes registrados en el sistema.
                                            </div>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                </div>
            </div>
        </div> <!-- /.grid -->
    </main>

    <!-- MODAL: Alta / Edición de usuario admin -->
    <div class="pop" id="userPop" style="display:none">
        <div class="box">
            <header style="display:flex;align-items:center;justify-content:space-between;gap:10px">
                <span id="userPopTitle">Nuevo usuario</span>
                <button class="btn gray" id="userClose">✖</button>
            </header>

            <div class="form">
                <input type="hidden" id="uId">

                <div>
                    <label>Nombre</label>
                    <input class="input" id="uNombre" placeholder="Nombres">
                </div>

                <div>
                    <label>Apellidos</label>
                    <input class="input" id="uApellidos" placeholder="Apellidos">
                </div>

                <div>
                    <label>Email</label>
                    <input class="input" id="uEmail" placeholder="email@dominio.com">
                </div>

                <div>
                    <label>Teléfono</label>
                    <input class="input" id="uNumero" placeholder="4420000000">
                </div>
 
                <div>
                 <label>Contraseña</label>
                 <input class="input" id="uPassword" type="password" placeholder="Mínimo 6 caracteres">
                </div>

                <div> 
                    <label>Rol</label>
                    <select id="uRol" class="input">
                        <option value="">Selecciona un rol</option>
                        @foreach(($roles ?? []) as $rol)
                            <option value="{{ $rol->id_rol }}">{{ $rol->nombre }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label>Estatus</label>
                    <select id="uActivo" class="input">
                        <option value="1">Activo</option>
                        <option value="0">Desactivado</option>
                    </select>
                </div>

                <div class="small" style="margin-top:8px;color:#667085">
                    Nota: la contraseña por defecto para nuevos usuarios es <b>123456</b>.
                </div>
            </div>

            <footer style="margin-top:14px;display:flex;justify-content:flex-end;gap:10px">
                <button class="btn gray" id="userCancel">Cancelar</button>
                <button class="btn primary" id="userSave">Guardar</button>
            </footer>
        </div>
    </div>
@endsection

@section('js-vistaUsuariosAdmin')
    <script src="{{ asset('js/usuariosAdmin.js') }}"></script>
@endsection
