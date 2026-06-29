document.addEventListener("DOMContentLoaded", () => {
    const tbody = document.getElementById("tbodyIndividuales");
    const csrf = document.querySelector("meta[name='csrf-token']").content;

    // BOTONES
    const btnNuevo = document.getElementById("btnNuevo");
    const btnGuardarNuevo = document.getElementById("btnGuardarNuevo");
    const btnGuardarEdit = document.getElementById("btnGuardarEdit");
    const btnGuardarSeccion = document.getElementById("btnGuardarSeccion");

    // ==========================================================
    // FORMATEO DINÁMICO DE DINERO
    // ==========================================================
    const limpiarNumero = (val) => {
        if (!val) return 0;
        const limpio = String(val).replace(/[^0-9.]/g, '');
        return parseFloat(limpio) || 0;
    };

    document.addEventListener("focusin", (e) => {
        if (e.target.classList.contains("input-money")) {
            let valorLimpio = e.target.value.replace(/[^0-9.]/g, '');
            if (parseFloat(valorLimpio) === 0) valorLimpio = "";
            e.target.value = valorLimpio;
        }
    });

    document.addEventListener("focusout", (e) => {
        if (e.target.classList.contains("input-money")) {
            const num = parseFloat(e.target.value) || 0;
            e.target.value = "$" + num.toFixed(2);
        }
    });

    // ==========================
    // LÓGICA DE MOSTRAR/OCULTAR DESGLOSE Y TIPO DE PRECIO
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

    // Lógica para ocultar/mostrar el input si eligen "Incluido"
    document.querySelectorAll('input[name="newTipoPrecio"]').forEach(radio => {
        radio.addEventListener('change', function() {
            document.getElementById('wrapper_input_nuevo').style.display = this.value === 'precio' ? 'block' : 'none';
        });
    });

    document.querySelectorAll('input[name="editTipoPrecio"]').forEach(radio => {
        radio.addEventListener('change', function() {
            document.getElementById('wrapper_input_edit').style.display = this.value === 'precio' ? 'block' : 'none';
        });
    });

    // ==========================
    // CARGAR LISTA (TABLAS DINÁMICAS)
    // ==========================
    function cargar(highlightId = null) {
        fetch("/admin/seguros-individuales/list")
            .then(r => r.json())
            .then(json => {
                if (!json) return;

                document.querySelectorAll('[id^="tbody-seccion-"]').forEach(tb => {
                    tb.innerHTML = "";
                });

                json.data.forEach(i => {
                    let precioNum = parseFloat(i.precio_por_dia);
                    let precioTxt = precioNum > 0
                        ? `$${precioNum.toFixed(2)}`
                        : `<span style="color: #16a34a; font-weight: bold;">Incluido</span>`;

                    const isHighlight = i.id_individual == highlightId ? 'class="row-highlight"' : '';

                    const filaHtml = `
                        <tr ${isHighlight}>
                            <td style="min-width: 180px;">
                                <strong>${i.nombre}</strong>
                            </td>
                            <td title="${i.descripcion || ''}">
                                <div style="max-width: 350px; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; text-overflow: ellipsis; font-size: 0.9em; color: #555;">
                                    ${i.descripcion || ''}
                                </div>
                            </td>
                            <td style="font-family: monospace; white-space: nowrap;">${precioTxt}</td>
                            <td>${i.activo ? "Sí" : "No"}</td>
                            <td style="width: 160px;">
                                <div style="display: flex; gap: 8px; justify-content: center; align-items: center;">
                                    <button class="btn btn-sm btn-warning" onclick="editar(${i.id_individual})">Editar</button>
                                    <button class="btn btn-sm btn-danger" onclick="eliminar(${i.id_individual})">Eliminar</button>
                                </div>
                            </td>
                        </tr>
                    `;

                    const targetTbody = document.getElementById(`tbody-seccion-${i.id_seccion}`);
                    if (targetTbody) {
                        targetTbody.innerHTML += filaHtml;
                    }
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
        document.getElementById("newPrecio").value = "$0.00";
        document.getElementById("newActivo").checked = true;

        document.querySelector('input[name="newTipoPrecio"][value="precio"]').checked = true;
        document.getElementById('wrapper_input_nuevo').style.display = 'block';

        document.querySelectorAll('.new-precio-auto').forEach(i => i.value = "$0.00");
        toggleDesglose(document.getElementById("newSeccion"), "caja_precio_nuevo", "caja_desglose_nuevo");
        openModal("modalNuevo");
    });

    btnGuardarNuevo.addEventListener("click", () => {
        document.activeElement.blur();
        
        const nombre = document.getElementById("newNombre").value;
        if (!nombre) {
            Swal.fire('Atención', 'El nombre es requerido', 'warning');
            return;
        }

        // Determinar precio final y limpiar formato
        let rawPrecio = document.getElementById("newPrecio").value;
        let precioFinalNuevo = document.querySelector('input[name="newTipoPrecio"]:checked').value === 'incluido' 
            ? 0 
            : limpiarNumero(rawPrecio);

        let jsonPrecios = {};
        document.querySelectorAll(".new-precio-auto").forEach(input => {
            jsonPrecios[input.dataset.id] = limpiarNumero(input.value);
        });

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
                precio_por_dia: precioFinalNuevo,
                precios_por_categoria: jsonPrecios,
                activo: document.getElementById("newActivo").checked ? 1 : 0
            })
        })
            .then(r => r.json())
            .then(res => {
                closeModal("modalNuevo");
                cargar();
                Swal.fire('¡Creado!', 'El seguro ha sido creado con éxito.', 'success');
            })
            .catch(error => {
                Swal.fire('Error', 'Ocurrió un error al procesar la solicitud.', 'error');
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
                document.getElementById("editDescripcion").value = d.descripcion || '';
                document.getElementById("editSeccion").value = d.id_seccion;
                document.getElementById("editActivo").checked = d.activo == 1;

                // Formatear dinero y asignar radio buttons
                if (d.precio_por_dia > 0) {
                    document.querySelector('input[name="editTipoPrecio"][value="precio"]').checked = true;
                    document.getElementById('wrapper_input_edit').style.display = 'block';
                    document.getElementById("editPrecio").value = "$" + parseFloat(d.precio_por_dia).toFixed(2);
                } else {
                    document.querySelector('input[name="editTipoPrecio"][value="incluido"]').checked = true;
                    document.getElementById('wrapper_input_edit').style.display = 'none';
                    document.getElementById("editPrecio").value = "$0.00";
                }

                let precios = d.precios_por_categoria || {};
                document.querySelectorAll(".edit-precio-auto").forEach(input => {
                    input.value = "$" + parseFloat(precios[input.dataset.id] || 0).toFixed(2);
                });

                toggleDesglose(document.getElementById("editSeccion"), "caja_precio_edit", "caja_desglose_edit");
                openModal("modalEditar");
            });
    };

    btnGuardarEdit.addEventListener("click", () => {
        document.activeElement.blur();
        const id = document.getElementById("editId").value;
        
        let rawPrecioEdit = document.getElementById("editPrecio").value;
        let precioFinalEdit = document.querySelector('input[name="editTipoPrecio"]:checked').value === 'incluido' 
            ? 0 
            : limpiarNumero(rawPrecioEdit);

        let jsonPrecios = {};
        document.querySelectorAll(".edit-precio-auto").forEach(input => {
            jsonPrecios[input.dataset.id] = limpiarNumero(input.value);
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
                precio_por_dia: precioFinalEdit,
                precios_por_categoria: jsonPrecios,
                activo: document.getElementById("editActivo").checked ? 1 : 0
            })
        })
            .then(r => r.json())
            .then(() => {
                closeModal("modalEditar");
                cargar(id);
                Swal.fire('¡Actualizado!', 'La información ha sido guardada correctamente.', 'success');
            })
            .catch(error => {
                Swal.fire('Error', 'Ocurrió un error al actualizar la solicitud.', 'error');
            })
            .finally(() => setLoading(btnGuardarEdit, false));
    });

    // ==========================
    // ELIMINAR SEGURO
    // ==========================
    window.eliminar = id => {
        document.activeElement.blur();
        Swal.fire({
            title: '¿Estás completamente seguro?',
            text: "Esta acción eliminará el seguro de forma permanente y no se puede revertir.",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#dc3545',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Sí, eliminar',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                fetch(`/admin/seguros-individuales/${id}`, {
                    method: "DELETE",
                    headers: { "X-CSRF-TOKEN": csrf }
                })
                    .then(() => {
                        cargar();
                        Swal.fire('¡Eliminado!', 'El seguro desapareció del sistema.', 'success');
                    })
                    .catch(() => {
                        Swal.fire('Error', 'No se pudo eliminar el seguro.', 'error');
                    });
            }
        });
    };

    // ==========================================================
    // GESTIÓN DE SECCIONES (CREAR / EDITAR / ELIMINAR)
    // ==========================================================
    let cacheSecciones = null;

    function cargarSecciones(highlightId = null) {
        fetch("/admin/secciones-seguros/list")
            .then(r => r.json())
            .then(json => {
                cacheSecciones = json.data;
                const cont = document.getElementById("listaSecciones");
                cont.innerHTML = "";

                if (json.data.length === 0) {
                    cont.innerHTML = `
                    <div style="text-align: center; padding: 30px 10px; color: #64748b;">
                        <span style="font-size: 24px; display: block; margin-bottom: 10px;">📋</span>
                        No hay secciones registradas aún.
                    </div>`;
                    return;
                }

                json.data.forEach(sec => {
                    const requiere = sec.requiere_desglose_autos == 1;
                    const desglose = requiere ? "🚗 Por vehículo" : "💰 Precio único";
                    const bgBadge = requiere ? "#e0e7ff" : "#f1f5f9"; 
                    const colorBadge = requiere ? "#3730a3" : "#475569"; 

                    const nombreSeguro = sec.nombre.replace(/'/g, "\\'");
                    const isHighlight = sec.id_seccion == highlightId ? 'row-highlight' : '';

                    cont.innerHTML += `
                    <div class="fila-seccion ${isHighlight}" id="row-sec-${sec.id_seccion}">
                        <div style="display: flex; flex-direction: column; gap: 6px;">
                            <strong style="color: #1e292b; font-size: 14px;">${sec.nombre}</strong> 
                            <span class="badge-desglose" style="background: ${bgBadge}; color: ${colorBadge}; width: fit-content; padding: 2px 6px; border-radius: 4px; font-size: 11px;">
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
        document.activeElement.blur();
        Swal.fire({
            title: '¿Eliminar sección?',
            text: "No podrás revertir esto y podría afectar a los seguros asignados.",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#dc3545',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Sí, eliminar',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                fetch(`/admin/secciones-seguros/${id}`, {
                    method: "DELETE",
                    headers: { "X-CSRF-TOKEN": csrf }
                })
                    .then(r => r.json())
                    .then(res => {
                        if (!res.ok) {
                            Swal.fire('Error', res.msg, 'error');
                            return;
                        }
                        Swal.fire({
                            title: '¡Eliminada!',
                            text: 'La sección fue eliminada.',
                            icon: 'success'
                        }).then(() => {
                            window.location.reload();
                        });
                    });
            }
        });
    };

    btnGuardarSeccion.addEventListener("click", () => {
        document.activeElement.blur();
        const id = document.getElementById("secId").value;
        const nombre = document.getElementById("secNombre").value;
        const desglose = document.getElementById("secDesglose").checked ? 1 : 0;

        if (!nombre) {
            Swal.fire('Atención', 'Escribe un nombre para la sección', 'warning');
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
                    Swal.fire('Error', res.msg || "No se pudo guardar", 'error');
                    return;
                }
                Swal.fire({
                    title: '¡Guardado!',
                    text: esEdicion ? "Sección actualizada exitosamente." : "Sección creada exitosamente.",
                    icon: 'success'
                }).then(() => {
                    window.location.reload();
                });
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