document.addEventListener("DOMContentLoaded", () => {

    const tbody = document.getElementById("tbodySeguros");
    const btnNuevo = document.getElementById("btnNuevo");

    // ===============================
    // Cargar tabla principal
    // ===============================
    function cargarSeguros() {
        fetch("/admin/seguros/list")
            .then(r => r.json())
            .then(json => {
                tbody.innerHTML = "";

                json.data.forEach(seg => {
                    // Formateamos los números a dos decimales de forma segura
                    const precio = parseFloat(seg.precio_por_dia).toFixed(2);
                    const colision = parseFloat(seg.deducible_colision).toFixed(2);
                    const robo = parseFloat(seg.deducible_robo).toFixed(2);

                    tbody.innerHTML += `
                        <tr>
                            <td class="mono">${seg.orden}</td>
                            <td><strong>${seg.nombre}</strong></td>
                            <td class="mono">$${precio}</td>
                            <td class="mono">$${colision}</td>
                            <td class="mono">$${robo}</td>
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
                const depositos = json.depositos || {}; // Mapa de montos de la tabla depositos

                // Llenamos campos base
                document.getElementById("editId").value = d.id_paquete;
                document.getElementById("editNombre").value = d.nombre;
                document.getElementById("editDescripcion").value = d.descripcion;
                document.getElementById("editPrecio").value = d.precio_por_dia;
                document.getElementById("editOrden").value = d.orden;
                document.getElementById("editDeducibleColision").value = d.deducible_colision;
                document.getElementById("editDeducibleRobo").value = d.deducible_robo;
                document.getElementById("editActivo").checked = d.activo == 1;

                // Calculamos el deducible total para revertir el porcentaje visualmente
                const colision = parseFloat(d.deducible_colision) || 0;
                const robo = parseFloat(d.deducible_robo) || 0;
                const totalDeducible = colision + robo;

                // Pintamos los porcentajes correspondientes a cada auto
                document.querySelectorAll(".edit-porcentaje").forEach(input => {
                    const catId = input.dataset.id;
                    const montoGarantia = parseFloat(depositos[catId]) || 0;

                    if (totalDeducible > 0 && montoGarantia > 0) {
                        // Revertimos la ecuación: (Monto / Total Deducible) * 100
                        input.value = Math.round((montoGarantia / totalDeducible) * 100);
                    } else {
                        input.value = 0;
                    }
                });

                // 🟢 Marcar las protecciones seleccionadas
                document.querySelectorAll(".edit-prot").forEach(chk => chk.checked = false); // Limpiar previos
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

    // ===============================
    // Eliminar Paquete
    // ===============================
    window.eliminar = (id) => {
        if (!confirm("¿Seguro que deseas eliminar este paquete y todas sus garantías vinculadas?")) return;

        fetch(`/admin/seguros/${id}`, {
            method: "DELETE",
            headers: { "X-CSRF-TOKEN": document.querySelector("meta[name='csrf-token']").content }
        })
        .then(r => r.json())
        .then(() => cargarSeguros());
    };

    // ===============================
    // Abrir Modal Nuevo (Limpia campos)
    // ===============================
    btnNuevo.addEventListener("click", () => {
        document.getElementById("newNombre").value = "";
        document.getElementById("newDescripcion").value = "";
        document.getElementById("newPrecio").value = "0.00";
        document.getElementById("newOrden").value = "0";
        document.getElementById("newDeducibleColision").value = "0.00";
        document.getElementById("newDeducibleRobo").value = "0.00";
        document.getElementById("newActivo").checked = true;
        
        // Resetea los porcentajes de autos a 0
        document.querySelectorAll(".new-porcentaje").forEach(input => input.value = 0);
        // 🟢 Resetea las protecciones seleccionadas
        document.querySelectorAll(".new-prot").forEach(chk => chk.checked = false);

        openModal("modalNuevo");
    });

    // ===============================
    // Guardar Nuevo Paquete (POST)
    // ===============================
    document.getElementById("btnGuardarNuevo").addEventListener("click", () => {
        
        // Recolectamos dinámicamente los porcentajes ingresados
        let porcentajesObj = {};
        document.querySelectorAll(".new-porcentaje").forEach(input => {
            porcentajesObj[input.dataset.id] = parseFloat(input.value) || 0;
        });

        // 🟢 Recolectar las protecciones marcadas
        let proteccionesArr = Array.from(document.querySelectorAll(".new-prot:checked")).map(chk => chk.value);

        fetch("/admin/seguros", {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
                "X-CSRF-TOKEN": document.querySelector("meta[name='csrf-token']").content
            },
            body: JSON.stringify({
                nombre: document.getElementById("newNombre").value,
                descripcion: document.getElementById("newDescripcion").value,
                precio_por_dia: document.getElementById("newPrecio").value,
                orden: document.getElementById("newOrden").value,
                deducible_colision: document.getElementById("newDeducibleColision").value,
                deducible_robo: document.getElementById("newDeducibleRobo").value,
                activo: document.getElementById("newActivo").checked ? 1 : 0,
                porcentajes: porcentajesObj,
                protecciones: proteccionesArr // 👈 Enviamos el array
            })
        })
        .then(r => r.json())
        .then(res => {
            if(res.ok) {
                closeModal("modalNuevo");
                cargarSeguros();
            } else {
                alert("Error al guardar: " + res.msg);
            }
        });
    });

    // ===============================
    // Actualizar Paquete Existente (PUT)
    // ===============================
    document.getElementById("btnGuardarEdit").addEventListener("click", () => {
        const id = document.getElementById("editId").value;

        // Recolectamos dinámicamente los porcentajes editados
        let porcentajesObj = {};
        document.querySelectorAll(".edit-porcentaje").forEach(input => {
            porcentajesObj[input.dataset.id] = parseFloat(input.value) || 0;
        });

        // 🟢 Recolectar las protecciones marcadas en edición
        let proteccionesArr = Array.from(document.querySelectorAll(".edit-prot:checked")).map(chk => chk.value);

        fetch(`/admin/seguros/${id}`, {
            method: "PUT",
            headers: {
                "Content-Type": "application/json",
                "X-CSRF-TOKEN": document.querySelector("meta[name='csrf-token']").content
            },
            body: JSON.stringify({
                nombre: document.getElementById("editNombre").value,
                descripcion: document.getElementById("editDescripcion").value,
                precio_por_dia: document.getElementById("editPrecio").value,
                orden: document.getElementById("editOrden").value,
                deducible_colision: document.getElementById("editDeducibleColision").value,
                deducible_robo: document.getElementById("editDeducibleRobo").value,
                activo: document.getElementById("editActivo").checked ? 1 : 0,
                porcentajes: porcentajesObj,
                protecciones: proteccionesArr // 👈 Enviamos el array
            })
        })
        .then(r => r.json())
        .then(res => {
            if(res.ok) {
                closeModal("modalEditar");
                cargarSeguros();
            } else {
                alert("Error al actualizar: " + res.msg);
            }
        });
    });

    cargarSeguros();
});

// Helpers para los modales
function openModal(id) {
    document.getElementById(id).style.display = "flex";
}

function closeModal(id) {
    document.getElementById(id).style.display = "none";
}