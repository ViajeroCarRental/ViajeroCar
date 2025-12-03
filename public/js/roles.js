const pop = document.getElementById("rolesPop");
const btnNuevo = document.getElementById("btnNuevo");
const closePop = document.getElementById("closePop");
const btnCancelar = document.getElementById("btnCancelar");
const btnGuardar = document.getElementById("btnGuardar");

const nombre = document.getElementById("rolNombre");
const desc = document.getElementById("rolDesc");
const permBox = document.getElementById("permBox");

let editId = null;

const csrf = document
    .querySelector('meta[name="csrf-token"]')
    .getAttribute("content");


/* ================================
   MOSTRAR POP
================================ */
btnNuevo.onclick = () => {
    editId = null;
    nombre.value = "";
    desc.value = "";
    permBox.innerHTML = "";
    cargarPermisos([]);
    document.getElementById("usuariosAsignados").style.display = "none";
    pop.style.display = "flex";
};

/* ================================
   CERRAR POP
================================ */
closePop.onclick = btnCancelar.onclick = () => pop.style.display = "none";

/* ================================
   LISTAR ROLES
================================ */
function cargarRoles() {
    fetch("/admin/roles/listar")
        .then(res => res.json())
        .then(data => {
            let body = "";
            data.forEach(r => {
                body += `
                <tr>
                    <td>${r.nombre}</td>
                    <td>${r.descripcion ?? "—"}</td>
                    <td>${r.total_usuarios}</td>
                    <td>
                        <button class="btn btn-primary btn-sm" onclick="editar(${r.id_rol})">Editar</button>
                        <button class="btn btn-danger btn-sm" onclick="eliminar(${r.id_rol})">Eliminar</button>
                    </td>
                </tr>`;
            });
            document.getElementById("rolesBody").innerHTML = body;
        });
}

cargarRoles();


/* ================================
   EDITAR ROL
================================ */
window.editar = (id) => {
    fetch(`/admin/roles/obtener/${id}`)
        .then(res => res.json())
        .then(data => {

            editId = id;
            nombre.value = data.rol.nombre;
            desc.value = data.rol.descripcion ?? "";

            cargarPermisos(data.permisosAsignados);

            // mostrar usuarios
            const lista = document.getElementById("listaUsuarios");
            lista.innerHTML = "";
            const box = document.getElementById("usuariosAsignados");

            box.style.display = "block";

            if (data.usuarios.length > 0) {
                data.usuarios.forEach(u => {
                    lista.innerHTML += `
                        <li>${u.nombres} ${u.apellidos} — ${u.correo}</li>`;
                });
            } else {
                lista.innerHTML = "<li>No hay usuarios asignados.</li>";
            }

            pop.style.display = "flex";
        });
};

/* ================================
   CARGAR PERMISOS
================================ */
function cargarPermisos(asignados = []) {
    fetch("/admin/roles/obtener/1") // solo para obtener catálogo de permisos
        .then(r => r.json())
        .then(data => {
            permBox.innerHTML = "";
            data.permisos.forEach(p => {
                const checked = asignados.includes(p.id_permiso) ? "checked" : "";
                permBox.innerHTML += `
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" value="${p.id_permiso}" ${checked}>
                        <label>${p.nombre}</label>
                    </div>`;
            });
        });
}

/* ================================
   GUARDAR ROL
================================ */
btnGuardar.onclick = () => {

    const permisos = [];
    permBox.querySelectorAll("input:checked").forEach(c => permisos.push(c.value));

    const payload = {
        nombre: nombre.value,
        descripcion: desc.value,
        permisos
    };

    const metodo = editId ? "PUT" : "POST";
    const url = editId
        ? `/admin/roles/actualizar/${editId}`
        : `/admin/roles/crear`;

    fetch(url, {
        method: metodo,
        headers: {
            "Content-Type": "application/json",
            "X-CSRF-TOKEN": csrf
        },
        body: JSON.stringify(payload)
    })
        .then(res => res.json())
        .then(() => {
            pop.style.display = "none";
            cargarRoles();
        });
};

/* ================================
   ELIMINAR
================================ */
window.eliminar = (id) => {
    if (!confirm("¿Eliminar este rol?")) return;

    fetch(`/admin/roles/eliminar/${id}`, {
        method: "DELETE",
        headers: { "X-CSRF-TOKEN": csrf }
    })
        .then(res => res.json())
        .then(() => cargarRoles());
};
