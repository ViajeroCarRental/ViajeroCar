document.addEventListener("DOMContentLoaded", () => {

    const tbody = document.getElementById("tbodySeguros");
    const btnNuevo = document.getElementById("btnNuevo");

    // ===================================================
    // Formateo dinámico de inputs (dinero / porcentaje)
    // ===================================================
    const limpiarNumero = (val) => {
        const limpio = String(val).replace(/[^0-9.]/g, '');
        return parseFloat(limpio) || 0;
    };

    document.addEventListener("focusin", (e) => {
        const id = e.target.id;
        const cls = e.target.classList;
        if (id === "newPrecio" || id === "editPrecio" ||
            id === "newDeducibleColision" || id === "editDeducibleColision" ||
            id === "newDeducibleRobo" || id === "editDeducibleRobo" ||
            cls.contains("new-monto") || cls.contains("edit-monto")) {
            let valorLimpio = e.target.value.replace(/[^0-9.]/g, '');
            if (parseFloat(valorLimpio) === 0) valorLimpio = "";
            e.target.value = valorLimpio;
        }
    });

    document.addEventListener("focusout", (e) => {
        const id = e.target.id;
        const cls = e.target.classList;
        if (id === "newPrecio" || id === "editPrecio" || cls.contains("new-monto") || cls.contains("edit-monto")) {
            const num = parseFloat(e.target.value) || 0;
            e.target.value = "$" + num.toFixed(2);
        } else if (id === "newDeducibleColision" || id === "editDeducibleColision" ||
                   id === "newDeducibleRobo" || id === "editDeducibleRobo") {
            const num = parseFloat(e.target.value) || 0;
            e.target.value = num.toFixed(2) + " %";
        }
    });

    // ===================================================
    // AUTO: recalcular precio y descripción según marcados
    //   - precio: suma data-precio de los que tienen data-suma="1"
    //   - descripción: junta data-desc de TODOS los marcados
    //   El usuario puede sobrescribir ambos después.
    // ===================================================
    const recalcularDesde = (claseCheckbox, idPrecio, idDescripcion) => {
        const marcados = Array.from(document.querySelectorAll(`.${claseCheckbox}:checked`));

        // Precio: ahora suman TODAS las protecciones marcadas según su precio_por_dia
        let suma = 0;
        marcados.forEach(chk => {
            suma += parseFloat(chk.dataset.precio) || 0;
        });
        const inputPrecio = document.getElementById(idPrecio);
        if (inputPrecio) inputPrecio.value = "$" + suma.toFixed(2);

        // Descripción: junta las descripciones de TODOS los marcados
        const descripciones = marcados
            .map(chk => (chk.dataset.desc || "").trim())
            .filter(d => d.length > 0);
        const inputDesc = document.getElementById(idDescripcion);
        if (inputDesc) inputDesc.value = descripciones.join("\n");
    };

    // Listener para los checkboxes de NUEVO
    document.addEventListener("change", (e) => {
        if (e.target.classList.contains("new-prot")) {
            recalcularDesde("new-prot", "newPrecio", "newDescripcion");
        }
        if (e.target.classList.contains("edit-prot")) {
            recalcularDesde("edit-prot", "editPrecio", "editDescripcion");
        }
    });

    // ===============================
    // Cargar tabla principal
    // ===============================
    function cargarSeguros() {
        fetch("/admin/seguros/list")
            .then(r => r.json())
            .then(json => {
                tbody.innerHTML = "";
                json.data.forEach(seg => {
                    const precio = parseFloat(seg.precio_por_dia).toFixed(2);
                    const colision = parseFloat(seg.deducible_colision).toFixed(2);
                    const robo = parseFloat(seg.deducible_robo).toFixed(2);

                    tbody.innerHTML += `
                        <tr>
                            <td><strong>${seg.nombre}</strong></td>
                            <td class="mono">$${precio}</td>
                            <td class="mono">${colision}%</td>
                            <td class="mono">${robo}%</td>
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

    // ===============================
    // Cargar datos en Modal Editar
    // ===============================
    window.editar = (id) => {
        fetch(`/admin/seguros/${id}`)
            .then(r => r.json())
            .then(json => {
                const d = json.data;
                const depositos = json.depositos || {};

                document.getElementById("editId").value = d.id_paquete;
                document.getElementById("editNombre").value = d.nombre;
                document.getElementById("editDescripcion").value = d.descripcion || "";
                document.getElementById("editPrecio").value = "$" + parseFloat(d.precio_por_dia).toFixed(2);
                document.getElementById("editDeducibleColision").value = parseFloat(d.deducible_colision).toFixed(2) + " %";
                document.getElementById("editDeducibleRobo").value = parseFloat(d.deducible_robo).toFixed(2) + " %";
                document.getElementById("editActivo").checked = d.activo == 1;

                document.querySelectorAll(".edit-monto").forEach(input => {
                    const catId = input.dataset.id;
                    const montoGarantia = parseFloat(depositos[catId]) || 0;
                    input.value = "$" + montoGarantia.toFixed(2);
                });

                document.querySelectorAll(".edit-prot").forEach(chk => chk.checked = false);
                if (json.protecciones) {
                    document.querySelectorAll(".edit-prot").forEach(chk => {
                        if (json.protecciones.includes(parseInt(chk.value))) {
                            chk.checked = true;
                        }
                    });
                }

                openModal("modalEditar");
            });
    };

    // ===================================================
    // Eliminar (SweetAlert2)
    // ===================================================
    window.eliminar = (id) => {
        Swal.fire({
            title: '¿Estás seguro?',
            text: "¡No podrás revertir esto y se eliminarán todas las garantías vinculadas!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#ff1e2d',
            cancelButtonColor: '#e5e7eb',
            confirmButtonText: 'Sí, eliminar paquete',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                fetch(`/admin/seguros/${id}`, {
                    method: "DELETE",
                    headers: { "X-CSRF-TOKEN": document.querySelector("meta[name='csrf-token']").content }
                })
                .then(r => r.json())
                .then(() => {
                    Swal.fire({
                        title: '¡Eliminado!',
                        text: 'El paquete ha sido borrado correctamente.',
                        icon: 'success',
                        timer: 2000,
                        showConfirmButton: false
                    });
                    cargarSeguros();
                });
            }
        });
    };

    // ===============================
    // Abrir Modal Nuevo
    // ===============================
    btnNuevo.addEventListener("click", () => {
        document.getElementById("newNombre").value = "";
        document.getElementById("newDescripcion").value = "";
        document.getElementById("newPrecio").value = "$0.00";
        document.getElementById("newDeducibleColision").value = "0.00 %";
        document.getElementById("newDeducibleRobo").value = "0.00 %";
        document.getElementById("newActivo").checked = true;

        document.querySelectorAll(".new-monto").forEach(input => input.value = "$0.00");
        document.querySelectorAll(".new-prot").forEach(chk => chk.checked = false);

        openModal("modalNuevo");
    });

    // ===================================================
    // Guardar NUEVO
    // ===================================================
    document.getElementById("btnGuardarNuevo").addEventListener("click", () => {
        let montosObj = {};
        document.querySelectorAll(".new-monto").forEach(input => {
            montosObj[input.dataset.id] = limpiarNumero(input.value);
        });

        let proteccionesArr = Array.from(document.querySelectorAll(".new-prot:checked")).map(chk => chk.value);
        const ordenEl = document.getElementById("newOrden");

        fetch("/admin/seguros", {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
                "X-CSRF-TOKEN": document.querySelector("meta[name='csrf-token']").content
            },
            body: JSON.stringify({
                nombre: document.getElementById("newNombre").value,
                descripcion: document.getElementById("newDescripcion").value,
                precio_por_dia: limpiarNumero(document.getElementById("newPrecio").value),
                orden: ordenEl ? ordenEl.value : 0,
                deducible_colision: limpiarNumero(document.getElementById("newDeducibleColision").value),
                deducible_robo: limpiarNumero(document.getElementById("newDeducibleRobo").value),
                activo: document.getElementById("newActivo").checked ? 1 : 0,
                montos: montosObj,
                protecciones: proteccionesArr
            })
        })
        .then(r => r.json())
        .then(res => {
            if(res.ok) {
                closeModal("modalNuevo");
                cargarSeguros();
                Swal.fire({ title: '¡Guardado con éxito!', text: 'El paquete se ha registrado en el sistema.', icon: 'success', timer: 2000, showConfirmButton: false });
            } else {
                Swal.fire({ title: 'Error al guardar', text: res.msg, icon: 'error', confirmButtonColor: '#2563eb' });
            }
        });
    });

    // ===================================================
    // Actualizar
    // ===================================================
    document.getElementById("btnGuardarEdit").addEventListener("click", () => {
        const id = document.getElementById("editId").value;

        let montosObj = {};
        document.querySelectorAll(".edit-monto").forEach(input => {
            montosObj[input.dataset.id] = limpiarNumero(input.value);
        });

        let proteccionesArr = Array.from(document.querySelectorAll(".edit-prot:checked")).map(chk => chk.value);
        const ordenEl = document.getElementById("editOrden");

        fetch(`/admin/seguros/${id}`, {
            method: "PUT",
            headers: {
                "Content-Type": "application/json",
                "X-CSRF-TOKEN": document.querySelector("meta[name='csrf-token']").content
            },
            body: JSON.stringify({
                nombre: document.getElementById("editNombre").value,
                descripcion: document.getElementById("editDescripcion").value,
                precio_por_dia: limpiarNumero(document.getElementById("editPrecio").value),
                orden: ordenEl ? ordenEl.value : 0,
                deducible_colision: limpiarNumero(document.getElementById("editDeducibleColision").value),
                deducible_robo: limpiarNumero(document.getElementById("editDeducibleRobo").value),
                activo: document.getElementById("editActivo").checked ? 1 : 0,
                montos: montosObj,
                protecciones: proteccionesArr
            })
        })
        .then(r => r.json())
        .then(res => {
            if(res.ok) {
                closeModal("modalEditar");
                cargarSeguros();
                Swal.fire({ title: '¡Paquete Actualizado!', text: 'Los cambios se guardaron correctamente.', icon: 'success', timer: 2000, showConfirmButton: false });
            } else {
                Swal.fire({ title: 'Error al actualizar', text: res.msg, icon: 'error', confirmButtonColor: '#2563eb' });
            }
        });
    });

    cargarSeguros();
});

// Cerrar modales haciendo clic afuera
window.addEventListener("click", (e) => {
    if (e.target.classList.contains("modal")) {
        closeModal(e.target.id);
    }
});

function openModal(id) {
    document.getElementById(id).style.display = "flex";
}

function closeModal(id) {
    document.getElementById(id).style.display = "none";
}
