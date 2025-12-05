document.addEventListener("DOMContentLoaded", () => {

    console.log("JS de Seguros Individuales cargado correctamente 🚗");

    const tbody = document.getElementById("tbodyIndividuales");

    // BOTONES DEL DOM
    const btnNuevo = document.getElementById("btnNuevo");
    const btnGuardarNuevo = document.getElementById("btnGuardarNuevo");
    const btnGuardarEdit = document.getElementById("btnGuardarEdit");

    const newNombre = document.getElementById("newNombre");
    const newDescripcion = document.getElementById("newDescripcion");
    const newPrecio = document.getElementById("newPrecio");
    const newActivo = document.getElementById("newActivo");

    const editId = document.getElementById("editId");
    const editNombre = document.getElementById("editNombre");
    const editDescripcion = document.getElementById("editDescripcion");
    const editPrecio = document.getElementById("editPrecio");
    const editActivo = document.getElementById("editActivo");

    // ==========================
    // CARGAR LISTA
    // ==========================
    function cargar() {
        fetch("/admin/seguros-individuales/list")
            .then(async r => {
                if (!r.ok) {
                    let txt = await r.text();
                    console.error("❌ Error 500 en el backend:", txt);
                    alert("Error al cargar los seguros individuales.");
                    return;
                }
                return r.json();
            })
            .then(json => {
                if (!json) return;

                tbody.innerHTML = "";

                json.data.forEach(i => {
                    tbody.innerHTML += `
                        <tr>
                            <td>${i.nombre}</td>
                            <td>$${i.precio_por_dia}</td>
                            <td>${i.activo ? "Sí" : "No"}</td>
                            <td>
                                <button class="btn btn-warning" onclick="editar(${i.id_individual})">Editar</button>
                                <button class="btn btn-danger" onclick="eliminar(${i.id_individual})">Eliminar</button>
                            </td>
                        </tr>
                    `;
                });
            })
            .catch(e => console.error("Error en fetch:", e));
    }

    // ==========================
    // NUEVO
    // ==========================
    btnNuevo.addEventListener("click", () => openModal("modalNuevo"));

    btnGuardarNuevo.addEventListener("click", () => {
        fetch("/admin/seguros-individuales", {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
                "X-CSRF-TOKEN": document.querySelector("meta[name='csrf-token']").content
            },
            body: JSON.stringify({
                nombre: newNombre.value,
                descripcion: newDescripcion.value,
                precio_por_dia: newPrecio.value,
                activo: newActivo.checked ? 1 : 0
            })
        })
        .then(r => r.json())
        .then(() => {
            closeModal("modalNuevo");
            cargar();
        });
    });

    // ==========================
    // EDITAR
    // ==========================
    window.editar = id => {
        fetch(`/admin/seguros-individuales/${id}`)
            .then(r => r.json())
            .then(json => {
                const d = json.data;

                editId.value = d.id_individual;
                editNombre.value = d.nombre;
                editDescripcion.value = d.descripcion;
                editPrecio.value = d.precio_por_dia;
                editActivo.checked = d.activo == 1;

                openModal("modalEditar");
            });
    };

    btnGuardarEdit.addEventListener("click", () => {
        const id = editId.value;

        fetch(`/admin/seguros-individuales/${id}`, {
            method: "PUT",
            headers: {
                "Content-Type": "application/json",
                "X-CSRF-TOKEN": document.querySelector("meta[name='csrf-token']").content
            },
            body: JSON.stringify({
                nombre: editNombre.value,
                descripcion: editDescripcion.value,
                precio_por_dia: editPrecio.value,
                activo: editActivo.checked ? 1 : 0
            })
        })
        .then(r => r.json())
        .then(() => {
            closeModal("modalEditar");
            cargar();
        });
    });

    // ==========================
    // ELIMINAR
    // ==========================
    window.eliminar = id => {
        if (!confirm("¿Eliminar este seguro?")) return;

        fetch(`/admin/seguros-individuales/${id}`, {
            method: "DELETE",
            headers: { "X-CSRF-TOKEN": document.querySelector("meta[name='csrf-token']").content }
        })
        .then(() => cargar());
    };

    // INICIAL
    cargar();
});


// ==========================
// FUNCIONES DE MODAL
// ==========================
function openModal(id) {
    document.getElementById(id).style.display = "flex";
}

function closeModal(id) {
    document.getElementById(id).style.display = "none";
}
