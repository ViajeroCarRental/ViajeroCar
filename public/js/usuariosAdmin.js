document.addEventListener("DOMContentLoaded", () => {
    console.log("🔥 usuariosAdmin.js cargado");

    const btnAdd = document.getElementById("btnAdd");
    const tbodyAdmins = document.getElementById("tbodyAdmins");

    const pop = document.getElementById("userPop");
    const popTitle = document.getElementById("userPopTitle");
    const btnClose = document.getElementById("userClose");
    const btnCancel = document.getElementById("userCancel");
    const btnSave = document.getElementById("userSave");

    const uId = document.getElementById("uId");
    const uNombre = document.getElementById("uNombre");
    const uApellidos = document.getElementById("uApellidos");
    const uEmail = document.getElementById("uEmail");
    const uNumero = document.getElementById("uNumero");
    const uRol = document.getElementById("uRol");
    const uActivo = document.getElementById("uActivo");
    const uPassword = document.getElementById("uPassword");

    const csrfToken = document
        .querySelector('meta[name="csrf-token"]')
        ?.getAttribute("content");

    let modo = "create";

    const abrirModal = () => pop.style.display = "flex";
    const cerrarModal = () => pop.style.display = "none";

    const limpiarForm = () => {
        uId.value = "";
        uNombre.value = "";
        uApellidos.value = "";
        uEmail.value = "";
        uNumero.value = "";
        uRol.value = "";
        uActivo.value = "1";
        if (uPassword) uPassword.value = "";
    };

    const cargarDesdeFila = (tr) => {
        uId.value = tr.dataset.id;
        uNombre.value = tr.dataset.nombres;
        uApellidos.value = tr.dataset.apellidos;
        uEmail.value = tr.dataset.correo;
        uNumero.value = tr.dataset.numero;
        uRol.value = tr.dataset.rolId;
        uActivo.value = tr.dataset.activo === "0" ? "0" : "1";
        if (uPassword) uPassword.value = "";
    };

    // ========================
    // 🆕 BOTÓN NUEVO
    // ========================
    btnAdd?.addEventListener("click", () => {
        modo = "create";
        popTitle.textContent = "Nuevo usuario administrativo";
        limpiarForm();
        abrirModal();
    });

    // ========================
    // ✏ EDITAR
    // ========================
    tbodyAdmins?.addEventListener("click", (e) => {
        const btn = e.target.closest(".btn-edit-user");
        if (!btn) return;

        const tr = btn.closest("tr");
        modo = "edit";
        popTitle.textContent = "Editar usuario administrativo";

        cargarDesdeFila(tr);
        abrirModal();
    });

    // ========================
    // 🗑 ELIMINAR
    // ========================
    tbodyAdmins?.addEventListener("click", (e) => {
        const btn = e.target.closest(".btn-delete-user");
        if (!btn) return;

        const tr = btn.closest("tr");
        const id = tr.dataset.id;
        const nombre = `${tr.dataset.nombres} ${tr.dataset.apellidos}`;

        if (!confirm(`¿Eliminar al usuario "${nombre}"?`)) return;

        const fd = new FormData();
        fd.append("_method", "DELETE");

        fetch(`/admin/usuarios/${id}`, {
            method: "POST",
            headers: {
                "X-CSRF-TOKEN": csrfToken,
                "Accept": "application/json"
            },

            body: fd,
        })
            .then(async (r) => {
                if (r.headers.get("content-type")?.includes("application/json")) {
                    return r.json();
                }
                const html = await r.text();
                console.error("ERROR HTML DELETE:", html);
                alert("❌ Error interno del servidor (DELETE). Revisa la consola.");
                throw new Error("Respuesta no JSON");
            })
            .then((data) => {
                if (data.ok) location.reload();
            })
            .catch(err => console.error("Error DELETE:", err));
    });

    // ========================
    // 💾 GUARDAR (CREATE / EDIT)
    // ========================
    btnSave?.addEventListener("click", () => {
        const fd = new FormData();

        fd.append("nombres", uNombre.value.trim());
        fd.append("apellidos", uApellidos.value.trim());
        fd.append("correo", uEmail.value.trim());
        fd.append("numero", uNumero.value.trim());
        fd.append("id_rol", uRol.value);
        fd.append("activo", uActivo.value);

        // Si el usuario escribió contraseña → enviarla
        if (uPassword && uPassword.value.trim() !== "") {
            fd.append("password", uPassword.value.trim());
        }

        let url = "/admin/usuarios";

        if (modo === "edit") {
            url = `/admin/usuarios/${uId.value}`;
            fd.append("_method", "PUT");
        }

        fetch(url, {
            method: "POST",
            headers: { "X-CSRF-TOKEN": csrfToken },
            body: fd
        })
            .then(async (r) => {
                // Detectar si Laravel respondió JSON
                if (r.headers.get("content-type")?.includes("application/json")) {
                    return r.json();
                }

                // Respuesta HTML (error)
                const html = await r.text();
                console.error("ERROR HTML CREATE/UPDATE:", html);
                alert("❌ Error interno del servidor. Revisa la consola.");
                throw new Error("Respuesta no JSON");
            })
            .then((data) => {
                if (data.ok) {
                    cerrarModal();
                    location.reload();
                } else {
                    alert(data.message ?? "Error al guardar.");
                }
            })
            .catch(err => console.error("ERROR JS:", err));
    });

    btnClose?.addEventListener("click", cerrarModal);
    btnCancel?.addEventListener("click", cerrarModal);
});
