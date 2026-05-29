document.addEventListener("DOMContentLoaded", () => {
    const tbody = document.getElementById("tbodyIndividuales");
    const csrf = document.querySelector("meta[name='csrf-token']").content;

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

        if (option.dataset.desglose == "1") {
            document.getElementById(cajaPrecio).style.display = "none";
            document.getElementById(cajaDesglose).style.display = "block";
        } else {
            document.getElementById(cajaPrecio).style.display = "block";
            document.getElementById(cajaDesglose).style.display = "none";
        }
    }

    document.getElementById("newSeccion").addEventListener("change", function () {
        toggleDesglose(this, "caja_precio_nuevo", "caja_desglose_nuevo");
    });
    document.getElementById("editSeccion").addEventListener("change", function () {
        toggleDesglose(this, "caja_precio_edit", "caja_desglose_edit");
    });

    // ==========================
    // CARGAR LISTA
    // ==========================
    function cargar(highlightId = null) {
        fetch("/admin/seguros-individuales/list")
            .then(r => r.json())
            .then(json => {
                if (!json) return;
                tbody.innerHTML = "";

                json.data.forEach(i => {
                    let precioTxt = i.precio_por_dia > 0
                        ? `$${parseFloat(i.precio_por_dia).toFixed(2)}`
                        : `<span style='color:gray;'>Por vehículo</span>`;

                    const isHighlight = i.id_individual == highlightId ? 'class="row-highlight"' : '';

                    tbody.innerHTML += `
                        <tr ${isHighlight}>
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

        document.querySelectorAll('.new-precio-auto').forEach(i => i.value = 0);
        toggleDesglose(document.getElementById("newSeccion"), "caja_precio_nuevo", "caja_desglose_nuevo");
        openModal("modalNuevo");
    });

    btnGuardarNuevo.addEventListener("click", () => {
        let jsonPrecios = {};
        document.querySelectorAll(".new-precio-auto").forEach(input => {
            jsonPrecios[input.dataset.id] = parseFloat(input.value) || 0;
        });

        const nombre = document.getElementById("newNombre").value;
        if(!nombre) {
            if(window.alertify) alertify.warning("El nombre es requerido");
            return;
        }

        setLoading(btnGuardarNuevo, true);
        fetch("/admin/seguros-individuales", {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
                "X-CSRF-TOKEN": csrf
            },
            body: JSON.stringify({
                nombre: nombre,
                descripcion: document.getElementById("newDescripcion").value,
                id_seccion: document.getElementById("newSeccion").value,
                precio_por_dia: document.getElementById("newPrecio").value,
                precios_por_categoria: jsonPrecios,
                activo: document.getElementById("newActivo").checked ? 1 : 0
            })
        })
            .then(r => r.json())
            .then(res => {
                closeModal("modalNuevo");
                // Recargar y resaltar el último (aproximado si no viene el ID en la respuesta)
                cargar(); 
                if (window.alertify) alertify.success("Seguro creado con éxito");
            })
            .finally(() => setLoading(btnGuardarNuevo, false));
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

                let precios = d.precios_por_categoria || {};
                document.querySelectorAll(".edit-precio-auto").forEach(input => {
                    input.value = precios[input.dataset.id] || 0;
                });

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

        setLoading(btnGuardarEdit, true);
        fetch(`/admin/seguros-individuales/${id}`, {
            method: "PUT",
            headers: {
                "Content-Type": "application/json",
                "X-CSRF-TOKEN": csrf
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
                cargar(id); // Resaltar el editado
                if (window.alertify) alertify.success("Seguro actualizado");
            })
            .finally(() => setLoading(btnGuardarEdit, false));
    });

    // ==========================
    // ELIMINAR SEGURO
    // ==========================
    window.eliminar = id => {
        if (confirm("¿Estás seguro? Esta acción eliminará el seguro de forma permanente.")) {
            fetch(`/admin/seguros-individuales/${id}`, {
                method: "DELETE",
                headers: { "X-CSRF-TOKEN": csrf }
            })
                .then(() => {
                    cargar();
                    if (window.alertify) alertify.success("Seguro eliminado");
                });
        }
    };

    // ==========================================================
    // GESTIÓN DE SECCIONES (crear / editar / eliminar / listar)
    // ==========================================================

    let cacheSecciones = null;

    function cargarSecciones(highlightId = null) {
        fetch("/admin/secciones-seguros/list")
            .then(r => r.json())
            .then(json => {
                cacheSecciones = json.data;
                const cont = document.getElementById("listaSecciones");
                cont.innerHTML = "";

                // Estado vacío elegante si no hay secciones
                if (json.data.length === 0) {
                    cont.innerHTML = `
                    <div style="text-align: center; padding: 30px 10px; color: #64748b;">
                        <span style="font-size: 24px; display: block; margin-bottom: 10px;">📋</span>
                        No hay secciones registradas aún.
                    </div>`;
                    return;
                }

                json.data.forEach(sec => {
                    // Colores condicionales para el badge
                    const requiere = sec.requiere_desglose_autos == 1;
                    const desglose = requiere ? "🚗 Por vehículo" : "💰 Precio único";
                    const bgBadge = requiere ? "#e0e7ff" : "#f1f5f9"; // Azul claro vs Gris claro
                    const colorBadge = requiere ? "#3730a3" : "#475569"; // Azul oscuro vs Gris oscuro

                    const nombreSeguro = sec.nombre.replace(/'/g, "\\'");
                    const isHighlight = sec.id_seccion == highlightId ? 'row-highlight' : '';

                    cont.innerHTML += `
                    <div class="fila-seccion ${isHighlight}" id="row-sec-${sec.id_seccion}">
                        <div style="display: flex; flex-direction: column; gap: 6px;">
                            <strong style="color: #1e292b; font-size: 14px;">${sec.nombre}</strong> 
                            <span class="badge-desglose" style="background: ${bgBadge}; color: ${colorBadge}; width: fit-content;">
                                ${desglose}
                            </span>
                        </div>
                        <div style="white-space:nowrap; display: flex; gap: 8px;">
                            <button type="button" class="btn-lista btn-lista-editar" onclick="editarSeccion(${sec.id_seccion}, '${nombreSeguro}', ${sec.requiere_desglose_autos})">
                                Editar
                            </button>
                            <button type="button" class="btn-lista btn-lista-eliminar" onclick="eliminarSeccion(${sec.id_seccion})">
                                Eliminar
                            </button>
                        </div>
                    </div>
                `;
                });
            });
    }

    function resetFormSeccion() {
        document.getElementById("secId").value = "";
        document.getElementById("secNombre").value = "";
        document.getElementById("secDesglose").checked = false;

        document.getElementById("tituloModalSeccion").textContent = "Gestión de Secciones";
        document.getElementById("btnGuardarSeccion").textContent = "Guardar Sección";
        document.getElementById("btnCancelarEdicionSec").style.display = "none";
    }

    window.editarSeccion = (id, nombre, desglose) => {
        document.getElementById("secId").value = id;
        document.getElementById("secNombre").value = nombre;
        document.getElementById("secDesglose").checked = desglose == 1;

        document.getElementById("tituloModalSeccion").textContent = "Editando Sección...";
        document.getElementById("btnGuardarSeccion").textContent = "Actualizar Sección";
        document.getElementById("btnCancelarEdicionSec").style.display = "inline-block";
    };

    document.getElementById("btnCancelarEdicionSec").addEventListener("click", resetFormSeccion);

    window.eliminarSeccion = (id) => {
        if (confirm("¿Eliminar sección? No podrás revertir esto y podría afectar a los seguros asignados.")) {
            fetch(`/admin/secciones-seguros/${id}`, {
                method: "DELETE",
                headers: { "X-CSRF-TOKEN": csrf }
            })
                .then(r => r.json())
                .then(res => {
                    if (!res.ok) {
                        if (window.alertify) alertify.error(res.msg);
                        else alert(res.msg);
                        return;
                    }
                    document.querySelectorAll(`#newSeccion option[value='${id}'], #editSeccion option[value='${id}']`).forEach(o => o.remove());
                    cargarSecciones();
                    resetFormSeccion();
                    if (window.alertify) alertify.success("Sección eliminada");
                });
        }
    };

    btnGuardarSeccion.addEventListener("click", () => {
        const id = document.getElementById("secId").value;
        const nombre = document.getElementById("secNombre").value;
        const desglose = document.getElementById("secDesglose").checked ? 1 : 0;

        if (!nombre) {
            if (window.alertify) alertify.warning('Escribe un nombre para la sección');
            return;
        }

        const esEdicion = id !== "";
        const url = esEdicion ? `/admin/secciones-seguros/${id}` : "/admin/secciones-seguros";
        const metodo = esEdicion ? "PUT" : "POST";

        setLoading(btnGuardarSeccion, true);
        fetch(url, {
            method: metodo,
            headers: {
                "Content-Type": "application/json",
                "X-CSRF-TOKEN": csrf
            },
            body: JSON.stringify({ nombre: nombre, requiere_desglose: desglose })
        })
            .then(r => r.json())
            .then(res => {
                if (!res.ok) {
                    if (window.alertify) alertify.error(res.msg || "No se pudo guardar");
                    return;
                }

                const sec = res.seccion;

                if (esEdicion) {
                    document.querySelectorAll(`#newSeccion option[value='${sec.id_seccion}'], #editSeccion option[value='${sec.id_seccion}']`).forEach(o => {
                        o.textContent = sec.nombre;
                        o.dataset.desglose = sec.requiere_desglose_autos;
                    });
                } else {
                    const opcionHtml = `<option value="${sec.id_seccion}" data-desglose="${sec.requiere_desglose_autos}">${sec.nombre}</option>`;
                    document.getElementById("newSeccion").insertAdjacentHTML('beforeend', opcionHtml);
                    document.getElementById("editSeccion").insertAdjacentHTML('beforeend', opcionHtml);
                }

                if (window.alertify) alertify.success(esEdicion ? "Sección actualizada" : "Sección creada");
                resetFormSeccion();
                cargarSecciones(sec.id_seccion);
            })
            .finally(() => setLoading(btnGuardarSeccion, false));
    });

    document.getElementById("btnGestionSecciones").addEventListener("click", () => {
        resetFormSeccion();
        cargarSecciones();
        openModal("modalSeccion");
    });

    // ==========================
    // UTILIDADES DE RENDIMIENTO Y UX
    // ==========================
    function setLoading(btn, isLoading) {
        if (isLoading) {
            btn.dataset.originalText = btn.innerHTML;
            btn.disabled = true;
            btn.innerHTML = `<i class="fas fa-spinner fa-spin"></i> Procesando...`;
        } else {
            btn.disabled = false;
            btn.innerHTML = btn.dataset.originalText;
        }
    }

    // INICIAL
    cargar();
});

// ==========================
// FUNCIONES DE MODAL GLOBALES
// ==========================
window.openModal = function (id) {
    document.getElementById(id).style.display = "flex";
}

window.closeModal = function (id) {
    document.getElementById(id).style.display = "none";
}