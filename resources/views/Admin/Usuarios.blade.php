@extends('layouts.Admin')

@section('Titulo', 'Usuarios Admin')

@section('css-vistaUsuariosAdmin')
<link rel="stylesheet" href="{{ asset('css/usuariosAdmin.css') }}">
@endsection

@section('contenidoUsuariosAdmin')

<main class="main">

    <div class="top">
        <div class="h1">Administración</div>
    </div>

    <div class="grid">

        {{-- TITULO --}}
        <div>
            <h2 style="margin:0 0 6px;font-size:28px">Usuarios del sistema</h2>
            <div class="small">
                Alta, edición, roles y acceso a módulos.  
                Aquí se muestran los usuarios administrativos y los clientes registrados.
            </div>
        </div>

        {{-- STATS --}}
        <div class="stats">
            <div class="stat stat-filter" data-filter="activos">
                <div class="title">Activos</div>
                <div class="num" id="sActivos">{{ $totales['activos'] ?? 0 }}</div>
            </div>
            <div class="stat stat-filter" data-filter="invitaciones">
                <div class="title">Invitaciones</div>
                <div class="num" id="sInvites">{{ $totales['invitaciones'] ?? 0 }}</div>
            </div>
            <div class="stat stat-filter" data-filter="admins">
                <div class="title">Admins</div>
                <div class="num" id="sAdmins">{{ $totales['admins'] ?? 0 }}</div>
            </div>
            <div class="stat stat-filter" data-filter="inactivos">
                <div class="title">Inactivos</div>
                <div class="num" id="sOff">{{ $totales['desactivados'] ?? 0 }}</div>
            </div>
        </div>

        {{-- GRAFICA --}}
        <div class="card" style="margin-bottom:22px">
            <div class="cnt">
                <h3 style="margin-bottom:8px">Estadísticas generales del sistema</h3>
                <canvas id="usuariosChart" height="100"></canvas>
            </div>
        </div>

        {{-- BLOQUE 1: USUARIOS ADMIN --}}
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
                    </div>
                </div>

                {{-- FILTROS --}}
                <div class="filters" style="margin:10px 0 16px">
                    <div style="position:relative;flex:1">
                        <input id="q" class="input" placeholder="🔎 Buscar por nombre o email">
                    </div>

                    <select id="fRol" class="input">
                        <option value="">Todos</option>
                        @foreach(($roles ?? []) as $rol)
                            <option value="{{ $rol->id_rol }}">{{ $rol->nombre }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- TABLA ADMINISTRADORES --}}
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
                            @forelse($admins ?? [] as $admin)
                                <tr
                                    data-id="{{ $admin->id_usuario }}"
                                    data-nombres="{{ $admin->nombres }}"
                                    data-apellidos="{{ $admin->apellidos }}"
                                    data-correo="{{ $admin->correo }}"
                                    data-numero="{{ $admin->numero }}"
                                    data-rol-id="{{ $admin->rol_id_principal }}"
                                    data-activo="{{ $admin->activo }}"
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

        {{-- BLOQUE CLIENTES --}}
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

                {{-- TABLA CLIENTES --}}
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
                                <th>Acciones</th>
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

                                    <td>
                                        <button class="btn tiny danger btn-delete-client"
                                                data-id="{{ $cli->id_usuario }}">
                                            🗑 Eliminar
                                        </button>
                                    </td>
                                </tr>

                            @empty
                                <tr>
                                    <td colspan="8">
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

    </div>

</main>

{{-- MODAL DE USUARIO --}}
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
                <div style="position:relative">
                    <input class="input" id="uPassword" type="password" placeholder="Mínimo 6 caracteres">
                    <button type="button" id="togglePass"
                        style="position:absolute;right:10px;top:50%;transform:translateY(-50%);border:none;background:transparent;cursor:pointer;">
                        👁
                    </button>
                </div>
            </div>

            <div>
                <label>Rol</label>
                <select id="uRol" class="input">
                    <option value="" disabled selected>Selecciona un rol</option>
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

{{-- Chart.js --}}
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

{{-- Scripts de la vista --}}
<script>
document.addEventListener("DOMContentLoaded", () => {

    // -------------------------
    // GRAFICA 🟦
    // -------------------------
    const ctx = document.getElementById('usuariosChart').getContext('2d');

    const gradActivos = ctx.createLinearGradient(0, 0, 0, 200);
    gradActivos.addColorStop(0, "#4CAF50");
    gradActivos.addColorStop(1, "#2E7D32");

    const gradInactivos = ctx.createLinearGradient(0, 0, 0, 200);
    gradInactivos.addColorStop(0, "#EF5350");
    gradInactivos.addColorStop(1, "#B71C1C");

    const gradAdmins = ctx.createLinearGradient(0, 0, 0, 200);
    gradAdmins.addColorStop(0, "#42A5F5");
    gradAdmins.addColorStop(1, "#0D47A1");

    const gradClientes = ctx.createLinearGradient(0, 0, 0, 200);
    gradClientes.addColorStop(0, "#AB47BC");
    gradClientes.addColorStop(1, "#4A148C");

    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: ['Activos', 'Inactivos', 'Admins', 'Clientes'],
            datasets: [{
                label: 'Usuarios',
                data: [
                    {{ $conteos['activos'] }},
                    {{ $conteos['inactivos'] }},
                    {{ $conteos['admins'] }},
                    {{ $conteos['clientes'] }}
                ],
                backgroundColor: [
                    gradActivos,
                    gradInactivos,
                    gradAdmins,
                    gradClientes
                ],
                borderRadius: 14,
                barThickness: 55,
                borderSkipped: false
            }]
        },
        options: {
            responsive: true,
            animation: { duration: 900, easing: "easeOutQuart" },
            plugins: {
                legend: { display: false }
            },
            scales: { y: { beginAtZero: true } }
        }
    });

    // VER / OCULTAR PASS
    const uPassword = document.getElementById("uPassword");
    const togglePass = document.getElementById("togglePass");

    togglePass?.addEventListener("click", () => {
        if (uPassword.type === "password") {
            uPassword.type = "text";
            togglePass.textContent = "🙈";
        } else {
            uPassword.type = "password";
            togglePass.textContent = "👁";
        }
    });

});
</script>

{{-- JS PRINCIPAL --}}
<script src="{{ asset('js/usuariosAdmin.js') }}"></script>

@endsection
