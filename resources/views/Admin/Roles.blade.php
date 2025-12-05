@extends('layouts.admin')

@section('css-vistaRoles')
<link rel="stylesheet" href="{{ asset('css/roles.css') }}">
@endsection

@section('contenidoRoles')

<meta name="csrf-token" content="{{ csrf_token() }}">

<div class="roles-container">

    <div class="header-flex">
        <h3>Roles y permisos</h3>
        <button id="btnNuevo" class="btn btn-danger shadow-sm">+ Nuevo rol</button>
    </div>

    <div class="table-wrap">
        <table class="table roles-table">
            <thead>
                <tr>
                    <th>Rol</th>
                    <th>Usuarios</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody id="rolesBody"></tbody>
        </table>
    </div>
</div>

<!-- ============================================
     MODAL CREAR / EDITAR ROL
============================================ -->
<div class="pop" id="rolesPop" style="display:none">
    <div class="pop-box">

        <header class="pop-header">
            <h4 id="tituloModal">Nuevo rol</h4>
            <button id="closePop" class="btn-close">×</button>
        </header>

        <div class="pop-body">
            <div class="form-group">
                <label>Nombre del rol</label>
                <input class="input" id="rolNombre" placeholder="Ej. Superadministrador">
            </div>

            <div class="form-group mt-3" id="usuariosAsignados" style="display:none">
                <h5>Usuarios con este rol</h5>
                <ul id="listaUsuarios" class="lista-usuarios"></ul>
            </div>
        </div>

        <footer class="pop-footer">
            <button id="btnCancelar" class="btn btn-secondary">Cancelar</button>
            <button id="btnGuardar" class="btn btn-danger">Guardar rol</button>
        </footer>

    </div>
</div>

<!-- ============================================
     MODAL LISTA DE USUARIOS ASIGNADOS
============================================ -->
<div class="pop" id="usuariosPop" style="display:none">
    <div class="pop-box" style="width:480px;">

        <header class="pop-header">
            <h4>Usuarios asignados</h4>
            <button class="btn-close" onclick="cerrarUsuariosPop()">×</button>
        </header>

        <div class="pop-body" id="usuariosLista"></div>

        <footer class="pop-footer">
            <button class="btn btn-secondary" onclick="cerrarUsuariosPop()">Cerrar</button>
        </footer>

    </div>
</div>

<script>
const csrf = document.querySelector('meta[name="csrf-token"]').content;
let editId = null;

/* ================
   MODALES
================ */
const pop = document.getElementById("rolesPop");
const usuariosPop = document.getElementById("usuariosPop");

/* ================
   ELEMENTOS
================ */
const btnNuevo = document.getElementById("btnNuevo");
const closePop = document.getElementById("closePop");
const btnCancelar = document.getElementById("btnCancelar");
const btnGuardar = document.getElementById("btnGuardar");
const nombre = document.getElementById("rolNombre");

/* =========================================================
   NUEVO ROL
========================================================= */
btnNuevo.onclick = () => {
    editId = null;
    nombre.value = "";
    document.getElementById("usuariosAsignados").style.display = "none";
    pop.style.display = "flex";
};

closePop.onclick = btnCancelar.onclick = () => pop.style.display = "none";

/* =========================================================
   LISTAR ROLES
========================================================= */
function cargarRoles() {
    fetch("/admin/roles/listar")
        .then(res => res.json())
        .then(data => {
            let html = "";
            data.forEach(r => {

                html += `
                <tr>
                    <td>${r.nombre}</td>

                    <td>
                        <button class="btn-user-count" onclick="verUsuarios(${r.id_rol})">
                            ${r.usuarios.length}
                        </button>
                    </td>

                    <td>
                        <button class="btn-action btn-editar" onclick="editar(${r.id_rol})">Editar</button>
                        <button class="btn-action btn-eliminar" onclick="eliminarRol(${r.id_rol})">Eliminar</button>
                    </td>
                </tr>`;
            });
            document.getElementById("rolesBody").innerHTML = html;
        });
}
cargarRoles();

/* =========================================================
   VER USUARIOS ASIGNADOS (MODAL)
========================================================= */
window.verUsuarios = (id) => {
    fetch(`/admin/roles/obtener/${id}`)
        .then(res => res.json())
        .then(data => {

            const lista = data.usuarios;
            const cont = document.getElementById("usuariosLista");

            if (lista.length === 0) {
                cont.innerHTML = "<p style='color:#888'>Sin usuarios asignados.</p>";
            } else {
                cont.innerHTML = lista.map(u => `
                    <div class="usuario-item">
                        <b>${u.nombres} ${u.apellidos}</b><br>
                        <small>${u.correo}</small>
                    </div>
                `).join("");
            }

            usuariosPop.style.display = "flex";
        });
};

function cerrarUsuariosPop() {
    usuariosPop.style.display = "none";
}

/* =========================================================
   EDITAR
========================================================= */
window.editar = (id) => {
    fetch(`/admin/roles/obtener/${id}`)
        .then(res => res.json())
        .then(data => {

            editId = id;
            nombre.value = data.rol.nombre;

            const lista = document.getElementById("listaUsuarios");
            lista.innerHTML = data.usuarios.length
                ? data.usuarios.map(u => `
                    <li><b>${u.nombres} ${u.apellidos}</b><br><small>${u.correo}</small></li>
                  `).join("")
                : "<li>No hay usuarios.</li>";

            document.getElementById("usuariosAsignados").style.display = "block";
            pop.style.display = "flex";
        });
};

/* =========================================================
   GUARDAR (CREAR / EDITAR)
========================================================= */
btnGuardar.onclick = () => {
    const form = new FormData();
    form.append("_token", csrf);
    form.append("nombre", nombre.value);

    let url = "/admin/roles/crear";
    if (editId) url = `/admin/roles/actualizar/${editId}`;

    fetch(url, { method: "POST", body: form })
    .then(r => r.json())
    .then(() => {
        pop.style.display = "none";
        cargarRoles();
    });
};

/* =========================================================
   ELIMINAR
========================================================= */
window.eliminarRol = (id) => {
    if (!confirm("¿Eliminar este rol?")) return;

    const form = new FormData();
    form.append("_token", csrf);

    fetch(`/admin/roles/eliminar/${id}`, {
        method: "POST",
        body: form
    })
    .then(r => r.json())
    .then(() => cargarRoles());
};
</script>

@endsection
