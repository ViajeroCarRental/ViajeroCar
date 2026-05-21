document.addEventListener("DOMContentLoaded", () => {
    const tbody = document.getElementById("tbodyIndividuales");

    // BOTONES
    const btnNuevo = document.getElementById("btnNuevo");
    const btnGuardarNuevo = document.getElementById("btnGuardarNuevo");
    const btnGuardarEdit = document.getElementById("btnGuardarEdit");
    const btnGuardarSeccion = document.getElementById("btnGuardarSeccion");

    // ==========================
    // LÓGICA DE MOSTRAR/OCULTAR DESGLOSE
    // ==========================
    function toggleDesglose(selectElement, cajaPrecio, cajaDesglose) {
        let option = selectElement.options[selectElement.selectedIndex];
        if (!option) return;

        // Si la sección tiene el flag de desglose = 1 (Robo y colisión)
        if (option.dataset.desglose == "1") {
            document.getElementById(cajaPrecio).style.display = "none";
            document.getElementById(cajaDesglose).style.display = "block";
        } else {
            // Seguros normales (Gastos médicos, etc)
            document.getElementById(cajaPrecio).style.display = "block";
            document.getElementById(cajaDesglose).style.display = "none";
        }
    }

    // Escuchamos los cambios en los selects
    document.getElementById("newSeccion").addEventListener("change", function() {
        toggleDesglose(this, "caja_precio_nuevo", "caja_desglose_nuevo");
    });
    document.getElementById("editSeccion").addEventListener("change", function() {
        toggleDesglose(this, "caja_precio_edit", "caja_desglose_edit");
    });

    // ==========================
    // CARGAR LISTA
    // ==========================
    function cargar() {
        fetch("/admin/seguros-individuales/list")
            .then(r => r.json())
            .then(json => {
                if (!json) return;
                tbody.innerHTML = "";

                json.data.forEach(i => {
                    // Si requiere desglose, el precio base aparece como "Variable"
                    let precioTxt = i.precio_por_dia > 0 ? `$${parseFloat(i.precio_por_dia).toFixed(2)}` : `<span style='color:gray;'>Por vehículo</span>`;
                    
                    tbody.innerHTML += `
                        <tr>
                            <td><strong>${i.nombre}</strong></td>
                            <td><span style="background: #e2e8f0; padding: 2px 8px; border-radius: 4px; font-size: 12px;">${i.seccion_nombre || 'N/A'}</span></td>
                            <td style="font-family: monospace;">${precioTxt}</td>
                            <td>${i.activo ? "Sí" : "No"}</td>
                            <td>
                                <button class="btn btn-sm btn-warning" onclick="editar(${i.id_individual})">Editar</button>
                                <button class="btn btn-sm btn-danger" onclick="eliminar(${i.id_individual})">Eliminar</button>
                            </td>
                        </tr>
                    `;
                });
            });
    }

    // ==========================
    // NUEVO SEGURO
    // ==========================
    btnNuevo.addEventListener("click", () => {
        document.getElementById("newNombre").value = "";
        document.getElementById("newDescripcion").value = "";
        document.getElementById("newSeccion").value = "";
        document.getElementById("newPrecio").value = "0.00";
        document.getElementById("newActivo").checked = true;
        
        // Reset precios desglose
        document.querySelectorAll('.new-precio-auto').forEach(i => i.value = 0);
        
        // Forzamos el toggle para que se oculte el desglose
        toggleDesglose(document.getElementById("newSeccion"), "caja_precio_nuevo", "caja_desglose_nuevo");
        
        openModal("modalNuevo");
    });

    btnGuardarNuevo.addEventListener("click", () => {
        // Recolectar JSON de precios si el desglose está visible
        let jsonPrecios = {};
        document.querySelectorAll(".new-precio-auto").forEach(input => {
            jsonPrecios[input.dataset.id] = parseFloat(input.value) || 0;
        });

        fetch("/admin/seguros-individuales", {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
                "X-CSRF-TOKEN": document.querySelector("meta[name='csrf-token']").content
            },
            body: JSON.stringify({
                nombre: document.getElementById("newNombre").value,
                descripcion: document.getElementById("newDescripcion").value,
                id_seccion: document.getElementById("newSeccion").value,
                precio_por_dia: document.getElementById("newPrecio").value,
                precios_por_categoria: jsonPrecios, // 👈 Se manda como Objeto, Laravel lo hace JSON
                activo: document.getElementById("newActivo").checked ? 1 : 0
            })
        })
        .then(r => r.json())
        .then(() => {
            closeModal("modalNuevo");
            cargar();
        });
    });

    // ==========================
    // EDITAR SEGURO
    // ==========================
    window.editar = id => {
        fetch(`/admin/seguros-individuales/${id}`)
            .then(r => r.json())
            .then(json => {
                const d = json.data;

                document.getElementById("editId").value = d.id_individual;
                document.getElementById("editNombre").value = d.nombre;
                document.getElementById("editDescripcion").value = d.descripcion;
                document.getElementById("editSeccion").value = d.id_seccion;
                document.getElementById("editPrecio").value = d.precio_por_dia;
                document.getElementById("editActivo").checked = d.activo == 1;

                // Llenar JSON en los inputs
                let precios = d.precios_por_categoria || {};
                document.querySelectorAll(".edit-precio-auto").forEach(input => {
                    input.value = precios[input.dataset.id] || 0;
                });

                // Forzamos el toggle
                toggleDesglose(document.getElementById("editSeccion"), "caja_precio_edit", "caja_desglose_edit");

                openModal("modalEditar");
            });
    };

    btnGuardarEdit.addEventListener("click", () => {
        const id = document.getElementById("editId").value;
        
        let jsonPrecios = {};
        document.querySelectorAll(".edit-precio-auto").forEach(input => {
            jsonPrecios[input.dataset.id] = parseFloat(input.value) || 0;
        });

        fetch(`/admin/seguros-individuales/${id}`, {
            method: "PUT",
            headers: {
                "Content-Type": "application/json",
                "X-CSRF-TOKEN": document.querySelector("meta[name='csrf-token']").content
            },
            body: JSON.stringify({
                nombre: document.getElementById("editNombre").value,
                descripcion: document.getElementById("editDescripcion").value,
                id_seccion: document.getElementById("editSeccion").value,
                precio_por_dia: document.getElementById("editPrecio").value,
                precios_por_categoria: jsonPrecios,
                activo: document.getElementById("editActivo").checked ? 1 : 0
            })
        })
        .then(r => r.json())
        .then(() => {
            closeModal("modalEditar");
            cargar();
        });
    });

    // ==========================
    // CREAR SECCIÓN AL VUELO
    // ==========================
    btnGuardarSeccion.addEventListener("click", () => {
        let nombre = document.getElementById("secNombre").value;
        let desglose = document.getElementById("secDesglose").checked ? 1 : 0;

        if (!nombre) return alert("Escribe un nombre para la sección");

        fetch("/admin/secciones-seguros", {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
                "X-CSRF-TOKEN": document.querySelector("meta[name='csrf-token']").content
            },
            body: JSON.stringify({ nombre: nombre, requiere_desglose: desglose })
        })
        .then(r => r.json())
        .then(res => {
            if (res.ok) {
                // Inyectamos la nueva sección en ambos Selects
                let opcionHtml = `<option value="${res.seccion.id_seccion}" data-desglose="${res.seccion.requiere_desglose_autos}">${res.seccion.nombre}</option>`;
                
                document.getElementById("newSeccion").insertAdjacentHTML('beforeend', opcionHtml);
                document.getElementById("editSeccion").insertAdjacentHTML('beforeend', opcionHtml);

                // Seleccionamos automáticamente la que acabamos de crear en el modal de Nuevo
                document.getElementById("newSeccion").value = res.seccion.id_seccion;
                
                // Forzamos el evento change
                document.getElementById("newSeccion").dispatchEvent(new Event('change'));

                document.getElementById("secNombre").value = "";
                document.getElementById("secDesglose").checked = false;
                closeModal("modalSeccion");
            }
        });
    });

    // ==========================
    // ELIMINAR SEGURO
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
// FUNCIONES DE MODAL GLOBALES
// ==========================
window.openModal = function(id) {
    document.getElementById(id).style.display = "flex";
}

window.closeModal = function(id) {
    document.getElementById(id).style.display = "none";
}