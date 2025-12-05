document.addEventListener("DOMContentLoaded", () => {

    const tbody = document.getElementById("tbodySeguros");
    const btnNuevo = document.getElementById("btnNuevo");

    // ===============================
    // Cargar tabla
    // ===============================
    function cargarSeguros() {
        fetch("/admin/seguros/list")
            .then(r => r.json())
            .then(json => {
                tbody.innerHTML = "";

                json.data.forEach(seg => {
                    tbody.innerHTML += `
                        <tr>
                            <td>${seg.nombre}</td>
                            <td>$${seg.precio_por_dia}</td>
                            <td>${seg.activo ? "Sí" : "No"}</td>
                            <td>
                                <button class="btn btn-sm btn-warning" onclick="editar(${seg.id_paquete})">Editar</button>
                                <button class="btn btn-sm btn-danger" onclick="eliminar(${seg.id_paquete})">Eliminar</button>
                            </td>
                        </tr>
                    `;
                });
            });
    }

    window.editar = (id) => {
        fetch(`/admin/seguros/${id}`)
            .then(r => r.json())
            .then(json => {
                const d = json.data;

                document.getElementById("editId").value = d.id_paquete;
                document.getElementById("editNombre").value = d.nombre;
                document.getElementById("editDescripcion").value = d.descripcion;
                document.getElementById("editPrecio").value = d.precio_por_dia;
                document.getElementById("editActivo").checked = d.activo == 1;

                openModal("modalEditar");
            });
    };

    window.eliminar = (id) => {
        if (!confirm("¿Seguro que deseas eliminar este paquete?")) return;

        fetch(`/admin/seguros/${id}`, {
            method: "DELETE",
            headers: { "X-CSRF-TOKEN": document.querySelector("meta[name='csrf-token']").content }
        })
        .then(r => r.json())
        .then(() => cargarSeguros());
    };


    btnNuevo.addEventListener("click", () => {
        openModal("modalNuevo");
    });

    document.getElementById("btnGuardarNuevo").addEventListener("click", () => {
        fetch("/admin/seguros", {
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
            cargarSeguros();
        });
    });

    document.getElementById("btnGuardarEdit").addEventListener("click", () => {
        const id = editId.value;

        fetch(`/admin/seguros/${id}`, {
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
            cargarSeguros();
        });
    });

    cargarSeguros();
});

function openModal(id) {
    document.getElementById(id).style.display = "flex";
}

function closeModal(id) {
    document.getElementById(id).style.display = "none";
}
