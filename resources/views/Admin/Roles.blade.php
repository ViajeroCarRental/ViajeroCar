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

<!-- ===============================
     MODAL POPUP
=============================== -->
<div class="pop" id="rolesPop" style="display:none">
    <div class="pop-box">

        <header class="pop-header">
            <h4 id="tituloModal">Nuevo rol</h4>
            <button id="closePop" class="btn-close"></button>
        </header>

        <div class="pop-body">

            <div class="form-group">
                <label>Nombre del rol</label>
                <input class="input" id="rolNombre" placeholder="Ej. Operador">
            </div>

            <div class="form-group mt-3">
                <h5>Permisos</h5>
                <div id="permBox" class="perm-box">
                    <!-- No hay permisos aún, así que se mostrará vacío sin romper -->
                    <p style="color:#777; font-size: 14px;">(Permisos no disponibles)</p>
                </div>
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

<!-- ===============================
     JAVASCRIPT
=============================== -->
<script>

const csrf = document.querySelector('meta[name="csrf-token"]').content;

let editId = null;

const pop = document.getElementById("rolesPop");
const btnNuevo = document.getElementById("btnNuevo");
const closePop = document.getElementById("closePop");
const btnCancelar = document.getElementById("btnCancelar");
const btnGuardar = document.getElementById("btnGuardar");

const nombre = document.getElementById("rolNombre");
const permBox = document.getElementById("permBox");

/* =====================================================
   NUEVO ROL
===================================================== */
btnNuevo.onclick = () => {
    editId = null;
    nombre.value = "";

    // limpiamos permisos (aunque no existan)
    permBox.innerHTML = `<p style="color:#777; font-size: 14px;">(Permisos no disponibles)</p>`;

    document.getElementById("usuariosAsignados").style.display = "none";

    pop.style.display = "flex";
};

/* CERRAR POPUP */
closePop.onclick = btnCancelar.onclick = () => {
    pop.style.display = "none";
};

/* =====================================================
   LISTAR ROLES
===================================================== */
function cargarRoles() {
    fetch("/admin/roles/listar")
        .then(res => res.json())
        .then(data => {
            let html = "";

            data.forEach(r => {
                html += `
                <tr>
                    <td>${r.nombre}</td>
                    <td>${r.total_usuarios}</td>
                    <td>
                        <button class="btn-editar" onclick="editar(${r.id_rol})">Editar</button>
                        <button class="btn-eliminar" onclick="eliminarRol(${r.id_rol})">Eliminar</button>
                    </td>
                </tr>`;
            });

            document.getElementById("rolesBody").innerHTML = html;
        });
}
cargarRoles();

/* =====================================================
   EDITAR ROL
===================================================== */
window.editar = (id) => {

    fetch(`/admin/roles/obtener/${id}`)
        .then(res => res.json())
        .then(data => {

            editId = id;

            nombre.value = data.rol.nombre;

            // No hay permisos, así que simplemente mostramos vacío
            permBox.innerHTML = `<p style="color:#777; font-size: 14px;">(Permisos no disponibles)</p>`;

            // usuarios también estarán vacíos
            const box = document.getElementById("usuariosAsignados");
            const lista = document.getElementById("listaUsuarios");

            lista.innerHTML = "<li>No hay usuarios asignados.</li>";
            box.style.display = "block";

            pop.style.display = "flex";
        })
        .catch(err => console.error(err));
};

/* =====================================================
   GUARDAR ROL (CREAR / EDITAR)
===================================================== */
btnGuardar.onclick = () => {

    const form = new FormData();
    form.append("nombre", nombre.value);

    let url = "/admin/roles/crear";
    let metodo = "POST";

    if (editId) {
        url = `/admin/roles/actualizar/${editId}`;
        metodo = "PUT";
    }

    fetch(url, {
        method: metodo,
        headers: { "X-CSRF-TOKEN": csrf },
        body: form
    })
    .then(res => res.json())
    .then(() => {
        pop.style.display = "none";
        cargarRoles();
    })
    .catch(err => console.error(err));
};

/* =====================================================
   ELIMINAR ROL
===================================================== */
window.eliminarRol = (id) => {
    if (!confirm("¿Eliminar este rol?")) return;

    fetch(`/admin/roles/eliminar/${id}`, {
        method: "DELETE",
        headers: { "X-CSRF-TOKEN": csrf }
    })
    .then(res => res.json())
    .then(() => cargarRoles())
    .catch(err => console.error(err));
};

</script>

@endsection
