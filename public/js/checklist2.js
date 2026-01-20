(function () {
    const root = document.getElementById('checklist2-root');
    if (!root) return;

    const nombresZonas = {
        1: "Defensa delantera",
        2: "Defensa delantera superior",
        3: "Costado izquierdo frontal",
        4: "Costado derecho frontal",
        5: "Cofre / parabrisas",
        6: "Puerta delantera izquierda",
        7: "Puerta delantera derecha",
        8: "Puerta trasera izquierda",
        9: "Puerta trasera derecha",
        10: "Techo",
        11: "Costado trasero izquierdo",
        12: "Costado trasero derecho",
        13: "Defensa trasera",
        15: "Llanta delantera izquierda",
        16: "Llanta delantera derecha",
        17: "Llanta trasera izquierda",
        18: "Llanta trasera derecha",
    };

    // URLs que vienen del data-* del contenedor
    const urlGuardarDano      = root.dataset.urlGuardarDano;
    const urlEliminarDanoBase = root.dataset.urlEliminarDanoBase;
        const urlVehiculosCategoriaBase = root.dataset.urlVehiculosCategoriaBase;
    const urlSetVehiculoNuevo       = root.dataset.urlSetVehiculoNuevo;


    // Token CSRF
    const csrfTokenMeta = document.querySelector('meta[name="csrf-token"]');
    const csrfToken = csrfTokenMeta ? csrfTokenMeta.getAttribute('content') : '';

    let contextoActual = null;  // empresa | cliente
    let zonaActual = null;      // número de zona
    let puntoActual = null;     // referencia al <circle> clickeado
    let fotoTemporalUrl = null; // para la vista previa

    const modal = document.getElementById("modalDano");
    const modalZonaLabel = document.getElementById("modalZonaLabel");
    const modalContextoLabel = document.getElementById("modalContextoLabel");

    const tipoInput = document.getElementById("tipoDano");
    const comentarioInput = document.getElementById("comentarioDano");
    const costoInput = document.getElementById("costoDano");
    const fotoInput = document.getElementById("fotoDano");
    const previewFoto = document.getElementById("previewFotoDano");

    // ==========================
    // HELPERS PARA TABLAS
    // ==========================
    function recalcularTotal(contexto) {
        const filas = document.querySelectorAll(
            '.cl2-danos-table[data-context="' + contexto + '"] tbody tr.cl2-dano-row'
        );

        let total = 0;
        filas.forEach(tr => {
            const costo = parseFloat(tr.dataset.costo || '0');
            if (!isNaN(costo)) {
                total += costo;
            }
        });

        const divTotal = document.querySelector(
            '.cl2-danos-total[data-context="' + contexto + '"]'
        );
        if (divTotal) {
            divTotal.textContent = 'Total daños: $' + total.toFixed(2) + ' MXN';
        }
    }

    function agregarFilaDano(contexto, data) {
        const tbody = document.querySelector(
            '.cl2-danos-table[data-context="' + contexto + '"] tbody'
        );
        if (!tbody) return;

        // Quitar fila "Sin daños registrados" si existe
        const vacia = tbody.querySelector('.cl2-danos-empty');
        if (vacia) {
            vacia.remove();
        }

        const tr = document.createElement('tr');
        tr.classList.add('cl2-dano-row');
        tr.dataset.contexto = contexto;
        tr.dataset.zona = data.zona;
        tr.dataset.costo = data.costo_estimado || 0;
        if (data.id) {
            tr.dataset.id = data.id; // id_foto_cambio
        }

        const costoNum = parseFloat(data.costo_estimado || '0');
        const costoTexto = isNaN(costoNum) ? '$0.00' : '$' + costoNum.toFixed(2);

        tr.innerHTML = `
            <td>${data.zona}</td>
            <td>${data.descripcion || '—'}</td>
            <td>${costoTexto}</td>
            <td>Foto cargada</td>
            <td>
                <button type="button"
                        class="cl2-dano-delete"
                        data-id="${data.id || ''}"
                        data-contexto="${contexto}">
                    ✕
                </button>
            </td>
        `;

        tbody.appendChild(tr);

        // Recalcular total para ese contexto
        recalcularTotal(contexto);
    }

    // Flag para "Son los mismos daños del checklist"
    let mismosDaniosEmpresa = false;

    // Botón que activa/desactiva el modo
    const btnMismos = document.getElementById("btnMismosDaniosEmpresa");

    if (btnMismos) {
        btnMismos.addEventListener("click", () => {
            // Cambiamos el estado (ON/OFF)
            mismosDaniosEmpresa = !mismosDaniosEmpresa;

            if (mismosDaniosEmpresa) {
                // Modo ACTIVADO → deshabilitar puntos de EMPRESA
                btnMismos.classList.add("is-active");
                btnMismos.textContent = "✅ Son los mismos daños del checklist";

                document
                    .querySelectorAll('.car-svg[data-context="empresa"] .point-dot')
                    .forEach(circ => circ.classList.add("disabled"));
            } else {
                // Modo DESACTIVADO → volver a habilitar puntos de EMPRESA
                btnMismos.classList.remove("is-active");
                btnMismos.textContent = "Son los mismos daños del checklist";

                document
                    .querySelectorAll('.car-svg[data-context="empresa"] .point-dot')
                    .forEach(circ => circ.classList.remove("disabled"));
            }
        });
    }

    // Hacer clic en toda la caja "uploader" para abrir la cámara/galería
    const uploaderCaja = document.querySelector("#modalDano .uploader");
    if (uploaderCaja && fotoInput) {
        uploaderCaja.addEventListener("click", (e) => {
            if (e.target.tagName.toLowerCase() !== "input") {
                fotoInput.click();
            }
        });
    }

    const btnGuardar = document.getElementById("guardarDano");
    const btnCancelar = document.getElementById("cancelarDano");

    // Inicializar listeners en los SVG
    document.querySelectorAll(".car-svg").forEach(svg => {
        const contexto = svg.dataset.context; // empresa / cliente

        svg.querySelectorAll(".point-dot").forEach(circle => {
            circle.addEventListener("click", () => {

                // Si es contexto EMPRESA y está activo "mismos daños", no hacemos nada
                if (contexto === "empresa" && mismosDaniosEmpresa) {
                    return;
                }

                zonaActual = circle.dataset.zone;
                contextoActual = contexto;
                puntoActual = circle;

                const nombreZona = nombresZonas[zonaActual] || ("Zona " + zonaActual);
                modalZonaLabel.textContent = nombreZona;

                modalContextoLabel.textContent = contexto === "empresa"
                    ? "AUTO RECIBIDO POR EMPRESA"
                    : "AUTO ENTREGADO A CLIENTE";

                // Limpiar campos del modal
                tipoInput.value = "";
                comentarioInput.value = "";
                costoInput.value = "";
                fotoInput.value = "";

                if (fotoTemporalUrl) {
                    URL.revokeObjectURL(fotoTemporalUrl);
                    fotoTemporalUrl = null;
                }
                previewFoto.style.display = "none";

                modal.style.display = "flex";
            });
        });
    });

    // Vista previa de foto
    fotoInput.addEventListener("change", (e) => {
        const file = e.target.files[0];
        if (file) {
            if (fotoTemporalUrl) {
                URL.revokeObjectURL(fotoTemporalUrl);
            }
            fotoTemporalUrl = URL.createObjectURL(file);
            previewFoto.src = fotoTemporalUrl;
            previewFoto.style.display = "block";
        } else {
            previewFoto.style.display = "none";
            if (fotoTemporalUrl) {
                URL.revokeObjectURL(fotoTemporalUrl);
                fotoTemporalUrl = null;
            }
        }
    });

    function cerrarModal() {
        modal.style.display = "none";
        contextoActual = null;
        zonaActual = null;
        puntoActual = null;

        tipoInput.value = "";
        comentarioInput.value = "";
        costoInput.value = "";
        fotoInput.value = "";

        if (fotoTemporalUrl) {
            URL.revokeObjectURL(fotoTemporalUrl);
            fotoTemporalUrl = null;
        }
        previewFoto.style.display = "none";
    }

    btnCancelar.addEventListener("click", cerrarModal);

    // Cerrar modal al hacer clic fuera
    modal.addEventListener("click", (e) => {
        if (e.target === modal) {
            cerrarModal();
        }
    });

    // Guardar daño → llamar al backend por AJAX
    btnGuardar.addEventListener("click", async () => {
        if (!contextoActual || !zonaActual) {
    if (window.alertify) {
        alertify.warning('Selecciona primero un punto del diagrama.');
    } else {
        alert('Selecciona primero un punto del diagrama.');
    }
    return;
}


        if (!fotoInput.files[0]) {
    if (window.alertify) {
        alertify.warning('La fotografía del daño es obligatoria.');
    } else {
        alert('La fotografía del daño es obligatoria.');
    }
    return;
}


        const tipoVal = (tipoInput.value || '').trim();
        const comentarioVal = (comentarioInput.value || '').trim();
        const costoVal = costoInput.value || '';

        const formData = new FormData();
        formData.append('contexto', contextoActual); // empresa / cliente
        formData.append('zona', zonaActual);
        formData.append('tipo_dano', tipoVal);
        formData.append('comentario', comentarioVal);
        formData.append('costo_estimado', costoVal);
        formData.append('foto', fotoInput.files[0]);

        try {
            const resp = await fetch(urlGuardarDano, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': csrfToken,
                },
                body: formData
            });

            const data = await resp.json();

if (!resp.ok || !data.ok) {
    throw new Error(data.message || 'Error al guardar el daño.');
}

// Tomamos el objeto "dano" que mandó el backend
const danoResp = data.dano || {};

// Agregar fila a la tabla correspondiente (empresa / cliente)
agregarFilaDano(contextoActual, {
    id: danoResp.id, // id_foto_cambio
    zona: zonaActual,
    descripcion: tipoVal || comentarioVal,
    costo_estimado: costoVal
});


            if (window.alertify) {
    alertify.success('Daño guardado correctamente.');
} else {
    alert('Daño guardado correctamente.');
}
cerrarModal();

        } catch (error) {
    console.error(error);
    if (window.alertify) {
        alertify.error('Ocurrió un error al guardar el daño.');
    } else {
        alert('Ocurrió un error al guardar el daño.');
    }
}
    });

    // =======================================
    // ELIMINAR DAÑO (click en la X)
    // =======================================
    document.addEventListener('click', function (e) {
        const btn = e.target.closest('.cl2-dano-delete');
        if (!btn) return;

        const idFoto = btn.dataset.id;
        const contexto = btn.dataset.contexto;
        const fila = btn.closest('tr');

        const ejecutarEliminacion = async () => {
            try {
                const url = urlEliminarDanoBase.replace('__ID__', idFoto);

                const resp = await fetch(url, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json',
                    },
                });

                const data = await resp.json();

                if (!resp.ok || !data.ok) {
                    throw new Error(data.message || 'Error al eliminar el daño.');
                }

                // Quitar fila del DOM
                if (fila) fila.remove();

                // Si ya no quedan filas, mostrar "Sin daños registrados"
                const tbody = document.querySelector(
                    '.cl2-danos-table[data-context="' + contexto + '"] tbody'
                );
                if (tbody && !tbody.querySelector('.cl2-dano-row')) {
                    const trEmpty = document.createElement('tr');
                    trEmpty.classList.add('cl2-danos-empty');
                    trEmpty.innerHTML = '<td colspan="5">Sin daños registrados.</td>';
                    tbody.appendChild(trEmpty);
                }

                // Recalcular total
                recalcularTotal(contexto);

                if (window.alertify) {
                    alertify.success('Daño eliminado.');
                } else {
                    alert('Daño eliminado.');
                }
            } catch (error) {
                console.error(error);
                if (window.alertify) {
                    alertify.error('Ocurrió un error al eliminar el daño.');
                } else {
                    alert('Ocurrió un error al eliminar el daño.');
                }
            }
        };

        // Confirmación con Alertify (si existe) o confirm nativo
        if (window.alertify) {
            alertify.confirm(
                'Eliminar daño',
                '¿Seguro que deseas eliminar este daño?',
                function () { ejecutarEliminacion(); },
                function () { /* cancelado */ }
            );
        } else {
            if (confirm('¿Seguro que deseas eliminar este daño?')) {
                ejecutarEliminacion();
            }
        }
    });

        // ==========================================================
    //   CAMBIO DE AUTO – LADO CLIENTE (MODAL VEHÍCULOS)
    // ==========================================================
    const selectCategoriaCliente = document.getElementById('categoriaCliente');
    const modalVehiculos  = document.getElementById('modalVehiculos');
    const listaVehiculos  = document.getElementById('listaVehiculos');
    const filtroColor     = document.getElementById('filtroColor');
    const filtroModelo    = document.getElementById('filtroModelo');
    const filtroSerie     = document.getElementById('filtroSerie');
    const btnCerrarModal1 = document.getElementById('cerrarModalVehiculos');
    const btnCerrarModal2 = document.getElementById('cerrarModalVehiculos2');

    const spanTipo    = document.getElementById('cliente-tipo');
    const spanModelo  = document.getElementById('cliente-modelo');
    const spanPlacas  = document.getElementById('cliente-placas');
    const spanTrans   = document.getElementById('cliente-transmision');
    const spanFuel    = document.getElementById('cliente-fuel');
    const spanKm      = document.getElementById('cliente-km');
    const inputIdVehiculoNuevo = document.getElementById('idVehiculoNuevoSeleccionado');

    let vehiculosCategoria = [];

    function abrirModalVehiculos() {
        if (modalVehiculos) {
            modalVehiculos.classList.add('show-modal');
        }
    }

    function cerrarModalVehiculos() {
        if (modalVehiculos) {
            modalVehiculos.classList.remove('show-modal');
        }
    }

    if (btnCerrarModal1) btnCerrarModal1.addEventListener('click', cerrarModalVehiculos);
    if (btnCerrarModal2) btnCerrarModal2.addEventListener('click', cerrarModalVehiculos);

    // Cerrar modal haciendo clic en el fondo oscuro
    if (modalVehiculos) {
        modalVehiculos.addEventListener('click', (e) => {
            if (e.target === modalVehiculos) {
                cerrarModalVehiculos();
            }
        });
    }

    function renderVehiculosLista() {
        if (!listaVehiculos) return;

        listaVehiculos.innerHTML = '';

        const colorFiltro  = (filtroColor?.value || '').toLowerCase();
        const modeloFiltro = (filtroModelo?.value || '').toLowerCase();
        const serieFiltro  = (filtroSerie?.value || '').toLowerCase();

        const filtrados = vehiculosCategoria.filter(v => {
            const color  = (v.color || '').toLowerCase();
            const modelo = (v.modelo || '').toLowerCase();
            const serie  = (v.numero_serie || '').toLowerCase();

            let ok = true;
            if (colorFiltro && !color.includes(colorFiltro)) ok = false;
            if (modeloFiltro && !modelo.includes(modeloFiltro)) ok = false;
            if (serieFiltro && !serie.includes(serieFiltro)) ok = false;

            return ok;
        });

        if (filtrados.length === 0) {
            listaVehiculos.innerHTML = '<p>No hay vehículos disponibles para esta categoría.</p>';
            return;
        }

        filtrados.forEach(v => {
            const div = document.createElement('div');
            div.classList.add('vehiculo-card');

            const nombre = v.nombre_publico || ((v.marca || '') + ' ' + (v.modelo || '')).trim();

            div.innerHTML = `
                <div class="vehiculo-info">
                    <div><strong>${nombre}</strong></div>
                    <div>Placas: ${v.placa || 'N/A'}</div>
                    <div>Transmisión: ${v.transmision || 'N/A'}</div>
                    <div>Combustible: ${v.combustible || 'N/A'}</div>
                    <div>Kilometraje: ${v.kilometraje ?? 'N/A'} km</div>
                    <div>Fuel: ${v.gasolina_actual ?? 'N/A'}</div>
                </div>
                <div>
                    <button type="button"
                            class="btn-vehiculo"
                            data-id="${v.id_vehiculo}"
                            data-tipo="${v.tipo_servicio || ''}"
                            data-modelo="${v.modelo || ''}"
                            data-placas="${v.placa || ''}"
                            data-transmision="${v.transmision || ''}"
                            data-fuel="${v.gasolina_actual ?? ''}"
                            data-km="${v.kilometraje ?? ''}">
                        Seleccionar
                    </button>
                </div>
            `;

            listaVehiculos.appendChild(div);
        });
    }

    // Filtros en vivo
    if (filtroColor)  filtroColor.addEventListener('input', renderVehiculosLista);
    if (filtroModelo) filtroModelo.addEventListener('input', renderVehiculosLista);
    if (filtroSerie)  filtroSerie.addEventListener('input', renderVehiculosLista);

    // Cuando se selecciona una categoría → cargar vehículos y abrir modal
    if (selectCategoriaCliente && urlVehiculosCategoriaBase) {
        selectCategoriaCliente.addEventListener('change', async (e) => {
            const idCat = e.target.value;

            // Si limpian el select, limpiamos la lista
            if (!idCat) {
                vehiculosCategoria = [];
                if (listaVehiculos) listaVehiculos.innerHTML = '';
                return;
            }

            try {
                const url = urlVehiculosCategoriaBase.replace('__CAT__', encodeURIComponent(idCat));

                const resp = await fetch(url, {
                    headers: {
                        'Accept': 'application/json',
                    },
                });

                const data = await resp.json();

                if (!resp.ok || !data.ok) {
                    throw new Error(data.message || 'Error al cargar vehículos.');
                }

                vehiculosCategoria = data.vehiculos || [];
                renderVehiculosLista();
                abrirModalVehiculos();

            } catch (error) {
                console.error(error);
                if (window.alertify) {
                    alertify.error('No se pudieron cargar los vehículos de esa categoría.');
                } else {
                    alert('No se pudieron cargar los vehículos de esa categoría.');
                }
            }
        });
    }

    // Click en "Seleccionar" dentro de la lista de vehículos
    if (listaVehiculos && urlSetVehiculoNuevo) {
        listaVehiculos.addEventListener('click', async (e) => {
            const btn = e.target.closest('.btn-vehiculo');
            if (!btn) return;

            const idVehiculo = btn.dataset.id;
            if (!idVehiculo) return;

            // 1) Actualizar la tabla del lado CLIENTE con los datos del carro nuevo
            if (spanTipo)   spanTipo.textContent   = btn.dataset.tipo || 'N/A';
            if (spanModelo) spanModelo.textContent = btn.dataset.modelo || 'N/A';
            if (spanPlacas) spanPlacas.textContent = btn.dataset.placas || 'N/A';
            if (spanTrans)  spanTrans.textContent  = btn.dataset.transmision || 'N/A';
            if (spanFuel)   spanFuel.textContent   = btn.dataset.fuel || 'N/A';
            if (spanKm)     spanKm.textContent     = btn.dataset.km || 'N/A';

            if (inputIdVehiculoNuevo) {
                inputIdVehiculoNuevo.value = idVehiculo;
            }

            // 2) Llamar al backend para registrar el vehículo nuevo en cambios_vehiculo (estado en_proceso)
            try {
                const resp = await fetch(urlSetVehiculoNuevo, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json',
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        id_vehiculo_nuevo: idVehiculo,
                    }),
                });

                const data = await resp.json();

                if (!resp.ok || !data.ok) {
                    throw new Error(data.message || 'Error al asignar el vehículo nuevo.');
                }

                if (window.alertify) {
                    alertify.success('Vehículo seleccionado para el cambio (en proceso).');
                } else {
                    alert('Vehículo seleccionado para el cambio (en proceso).');
                }

                cerrarModalVehiculos();

            } catch (error) {
                console.error(error);
                if (window.alertify) {
                    alertify.error('No se pudo asignar el vehículo nuevo.');
                } else {
                    alert('No se pudo asignar el vehículo nuevo.');
                }
            }
        });
    }

        // ===================================
    //  Sincronizar altura de extra-block
    //  (empresa vs cliente)
    // ===================================
    function syncExtraBlocksHeight() {
        const blocks = document.querySelectorAll('.cl2-extra-block');
        if (blocks.length < 2) return;

        // Resetear minHeight antes de medir
        blocks.forEach(b => {
            b.style.minHeight = '0px';
        });

        const maxHeight = Math.max(
            ...Array.from(blocks).map(b => b.offsetHeight)
        );

        blocks.forEach(b => {
            b.style.minHeight = maxHeight + 'px';
        });
    }


    // ================================
//  FOTOS DEL CAMBIO DE AUTO
// ================================
const inputCambio = document.getElementById('inputFotosCambio');
const previewCambio = document.getElementById('preview-fotosCambio');
const msgCambio = document.querySelector('#uploaderCambioAuto .cl2-photo-msg');

let filesCambio = []; // aquí guardamos TODAS las fotos seleccionadas

function actualizarInputCambio() {
    const dt = new DataTransfer();
    filesCambio.forEach(f => dt.items.add(f));
    inputCambio.files = dt.files;

    if (msgCambio) {
        msgCambio.textContent = filesCambio.length
            ? `${filesCambio.length} foto(s) seleccionada(s)`
            : 'Toca para cámara o galería (JPG/PNG)';
    }
}

function renderPreviewCambio() {
    if (!previewCambio) return;

    previewCambio.innerHTML = '';

    filesCambio.forEach((file, index) => {
        const thumb = document.createElement('div');
        thumb.className = 'cl2-photo-thumb';

        const img = document.createElement('img');
        const url = URL.createObjectURL(file);
        img.src = url;
        img.onload = () => URL.revokeObjectURL(url);

        const btnX = document.createElement('button');
        btnX.type = 'button';
        btnX.className = 'cl2-photo-remove';
        btnX.textContent = '×';
        btnX.dataset.index = index.toString();

        thumb.appendChild(img);
        thumb.appendChild(btnX);
        previewCambio.appendChild(thumb);
    });

    // 🔹 cada vez que cambian las fotos, reajustamos la altura de los bloques
    syncExtraBlocksHeight();
}


// Cuando seleccionas nuevas fotos
if (inputCambio) {
    inputCambio.addEventListener('change', (e) => {
        const nuevas = Array.from(e.target.files || []);

        // las agregamos a las que ya había
        filesCambio = filesCambio.concat(nuevas);

        renderPreviewCambio();
        actualizarInputCambio();
    });
}

// Eliminar una sola foto con la X (delegación de eventos)
if (previewCambio) {
    previewCambio.addEventListener('click', (e) => {
        const btn = e.target.closest('.cl2-photo-remove');
        if (!btn) return;

        const index = parseInt(btn.dataset.index, 10);
        if (isNaN(index)) return;

        filesCambio.splice(index, 1);  // quitamos esa sola foto
        renderPreviewCambio();
        actualizarInputCambio();
    });
}


        // Recalcular totales al cargar (por si ya venían daños desde BD)
    recalcularTotal('empresa');
    recalcularTotal('cliente');

    // Ajustar alturas iniciales de los bloques
    syncExtraBlocksHeight();

    // Volver a ajustar en cambios de tamaño de ventana
    window.addEventListener('load', syncExtraBlocksHeight);
    window.addEventListener('resize', syncExtraBlocksHeight);

})();



