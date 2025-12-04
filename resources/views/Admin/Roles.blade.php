@extends('layouts.admin')

{{-- SECCIÓN CSS --}}
@section('css-vistaRoles')
<link rel="stylesheet" href="{{ asset('css/roles.css') }}">
@endsection

{{-- CONTENIDO PRINCIPAL --}}
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

{{-- MODAL --}}
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
                <div id="permBox" class="perm-box"></div>
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

{{-- ===================================================== --}}
{{-- ========================= JS ========================= --}}
{{-- ===================================================== --}}
<script>

const csrf = document.querySelector('meta[name="csrf-token"]').content;

const pop = document.getElementById("rolesPop");
const btnNuevo = document.getElementById("btnNuevo");
const closePop = document.getElementById("closePop");
const btnCancelar = document.getElementById("btnCancelar");
const btnGuardar = document.getElementById("btnGuardar");

const nombre = document.getElementById("rolNombre");
const permBox = document.getElementById("permBox");

let editId = null;

/* MOSTRAR POP */
btnNuevo.onclick = () => {
    editId = null;
    nombre.value = "";
    permBox.innerHTML = "";
    cargarPermisos([]);
    document.getElementById("usuariosAsignados").style.display = "none";
    pop.style.display = "flex";
};

/* CERRAR POP */
closePop.onclick = btnCancelar.onclick = () => {
    pop.style.display = "none";
};

/* LISTAR ROLES */
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

/* EDITAR */
window.editar = (id) => {
    fetch(`/admin/roles/obtener/${id}`)
        .then(res => res.json())
        .then(data => {
            editId = id;
            nombre.value = data.rol.nombre;
            cargarPermisos(data.permisosAsignados);
            const box = document.getElementById("usuariosAsignados");
            const lista = document.getElementById("listaUsuarios");
            lista.innerHTML = "";
            if (data.usuarios.length > 0) {
                box.style.display = "block";
                data.usuarios.forEach(u => {
                    lista.innerHTML += `<li>${u.nombres} ${u.apellidos} — ${u.correo}</li>`;
                });
            } else {
                box.style.display = "block";
                lista.innerHTML = "<li>No hay usuarios asignados.</li>";
            }
            pop.style.display = "flex";
        });
};

/* CARGAR PERMISOS */
function cargarPermisos(asignados = []) {
    fetch("/admin/permisos/listar")
        .then(res => res.json())
        .then(permisos => {
            permBox.innerHTML = "";
            permisos.forEach(p => {
                const checked = asignados.includes(p.id_permiso) ? "checked" : "";
                permBox.innerHTML += `
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" value="${p.id_permiso}" ${checked}>
                        <label>${p.nombre}</label>
                    </div>`;
            });
        });
}

/* GUARDAR */
btnGuardar.onclick = () => {
    const seleccion = [];
    permBox.querySelectorAll("input:checked").forEach(c => seleccion.push(c.value));
    const payload = { nombre: nombre.value, permisos: seleccion };
    const method = editId ? "PUT" : "POST";
    const url = editId ? `/admin/roles/actualizar/${editId}` : `/admin/roles/crear`;
    fetch(url, {
        method: method,
        headers: { "Content-Type": "application/json", "X-CSRF-TOKEN": csrf },
        body: JSON.stringify(payload)
    })
    .then(res => res.json())
    .then(() => {
        pop.style.display = "none";
        cargarRoles();
    });
};

/* ELIMINAR */
window.eliminarRol = (id) => {
    if (!confirm("¿Eliminar este rol?")) return;
    fetch(`/admin/roles/eliminar/${id}`, {
        method: "DELETE",
        headers: { "X-CSRF-TOKEN": csrf }
    })
    .then(res => res.json())
    .then(() => cargarRoles());
};

</script>

@endsection
