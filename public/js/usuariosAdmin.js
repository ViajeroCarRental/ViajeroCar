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

    const abrirModal = () => (pop.style.display = "flex");
    const cerrarModal = () => (pop.style.display = "none");

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
    // 🆕 NUEVO USUARIO
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
    // 🗑 ELIMINAR ADMIN
    // ========================
    tbodyAdmins?.addEventListener("click", (e) => {
        const btn = e.target.closest(".btn-delete-user");
        if (!btn) return;

        const tr = btn.closest("tr");
        const id = tr.dataset.id;
        const nombre = `${tr.dataset.nombres} ${tr.dataset.apellidos}`;

        if (!confirm(`¿Eliminar al usuario "${nombre}"?`)) return;

        const fd = new FormData();

        fetch(`/admin/usuarios/${id}/delete`, {
            method: "POST",
            headers: {
                "X-CSRF-TOKEN": csrfToken,
                "Accept": "application/json"
            },
            body: fd
        })
            .then(r => r.json())
            .then(data => {
                if (data.ok) location.reload();
                else alert(data.message ?? "Error al eliminar.");
            })
            .catch(err => console.error("ERROR DELETE ADMIN:", err));
    });

    // ========================
    // 💾 GUARDAR
    // ========================
    btnSave?.addEventListener("click", () => {
        const fd = new FormData();

        fd.append("nombres", uNombre.value.trim());
        fd.append("apellidos", uApellidos.value.trim());
        fd.append("correo", uEmail.value.trim());
        fd.append("numero", uNumero.value.trim());
        fd.append("id_rol", uRol.value);
        fd.append("activo", uActivo.value);

        // contraseña opcional
        if (uPassword && uPassword.value.trim() !== "") {
            fd.append("password", uPassword.value.trim());
        }

        let url = "/admin/usuarios";

        if (modo === "edit") {
            const id = uId.value;
            url = `/admin/usuarios/${id}/update`;
        }

        fetch(url, {
            method: "POST",
            headers: { "X-CSRF-TOKEN": csrfToken, "Accept": "application/json" },
            body: fd
        })
            .then(r => r.json())
            .then(data => {
                if (data.ok) {
                    cerrarModal();
                    location.reload();
                } else {
                    alert(data.message ?? "Error al guardar.");
                }
            })
            .catch(err => console.error("ERROR CREATE/UPDATE:", err));
    });

    // ==============================
    // 🗑 ELIMINAR CLIENTE
    // ==============================
    document.addEventListener("click", e => {
        const btn = e.target.closest(".btn-delete-client");
        if (!btn) return;

        const id = btn.dataset.id;

        if (!confirm("¿Eliminar este cliente?")) return;

        const fd = new FormData();

        fetch(`/admin/clientes/${id}/delete`, {
            method: "POST",
            headers: {
                "X-CSRF-TOKEN": csrfToken,
                "Accept": "application/json"
            },
            body: fd
        })
            .then(r => r.json())
            .then(data => {
                if (data.ok) location.reload();
                else alert(data.message ?? "Error al eliminar cliente.");
            })
            .catch(err => console.error("ERROR DELETE CLIENTE:", err));
    });

    btnClose?.addEventListener("click", cerrarModal);
    btnCancel?.addEventListener("click", cerrarModal);
});
