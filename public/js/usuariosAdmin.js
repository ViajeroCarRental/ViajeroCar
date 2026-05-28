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
    const uNombreUsuario  = document.getElementById("uNombreUsuario");
    const uNumero = document.getElementById("uNumero");
    const uRol = document.getElementById("uRol");
    const uActivo = document.getElementById("uActivo");
    const uPassword = document.getElementById("uPassword");


    // 🆕 FIRMA
    const firmaCanvas = document.getElementById("uFirmaPad");
    const firmaClear  = document.getElementById("uFirmaClear");
    let firmaPad = null;
    let firmaPrevia = null; // base64 cargada al editar

    if (firmaCanvas && window.SignaturePad) {
        firmaPad = new SignaturePad(firmaCanvas, { minWidth: 1, maxWidth: 2 });
    }

    firmaClear?.addEventListener("click", () => {
        firmaPad?.clear();
        firmaPrevia = null;
    });

    const cargarFirmaEnCanvas = (dataUrl) => {
        if (!firmaPad || !dataUrl) return;
        firmaPad.clear();
        const img = new Image();
        img.onload = () => {
            const ctx = firmaCanvas.getContext("2d");
            ctx.drawImage(img, 0, 0, firmaCanvas.width, firmaCanvas.height);
            firmaPad._isEmpty = false;
        };
        img.src = dataUrl;
    };




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
        uNombreUsuario.value = "";
        uNumero.value = "";
        uRol.value = "";
        uActivo.value = "1";
        if (uPassword) uPassword.value = "";
        firmaPad?.clear();
        firmaPrevia = null;
    };

    const cargarDesdeFila = (tr) => {
        uId.value = tr.dataset.id;
        uNombre.value = tr.dataset.nombres;
        uApellidos.value = tr.dataset.apellidos;
        uNombreUsuario.value  = tr.dataset.nombreUsuario ?? "";
        uNumero.value = tr.dataset.numero;
        uRol.value = tr.dataset.rolId;
        uActivo.value = tr.dataset.activo === "0" ? "0" : "1";
        if (uPassword) uPassword.value = "";
        firmaPrevia = tr.dataset.firma || null;
        cargarFirmaEnCanvas(firmaPrevia);
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

        if (uNombreUsuario.value.trim() === "") {
            alert("Debes ingresar el nombre de usuario.");
            return;
        }
        if (uNombreUsuario.value.trim().length > 15) {
            alert("El nombre de usuario no puede exceder 15 caracteres.");
            return;
        }

        // 🆕 Resolver firma: nueva si dibujó, previa si está editando, o null
        let firmaData = null;
        if (firmaPad && !firmaPad.isEmpty()) {
            firmaData = firmaPad.toDataURL("image/png");
        } else if (modo === "edit" && firmaPrevia) {
            firmaData = firmaPrevia;
        }

        const fd = new FormData();

        fd.append("nombres", uNombre.value.trim());
        fd.append("apellidos", uApellidos.value.trim());
        fd.append("nombre_usuario", uNombreUsuario.value.trim());
        fd.append("numero", uNumero.value.trim());
        fd.append("id_rol", uRol.value);
        fd.append("activo", uActivo.value);

        if (firmaData) fd.append("firma", firmaData);

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
