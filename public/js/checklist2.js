// ==============================
// DEBUG HELPER
// ==============================
const DEBUG_CAMBIO_AUTO = true;
function debugCambioAuto(...args) {
    if (DEBUG_CAMBIO_AUTO) {
        console.log('[CambioAuto]', ...args);
    }
}

(function () {
    const root = document.getElementById('checklist2-root');
    debugCambioAuto('JS checklist2 inicializado', { root });
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

    const urlGuardarDano            = root.dataset.urlGuardarDano;
    const urlEliminarDanoBase       = root.dataset.urlEliminarDanoBase;
    const urlVehiculosCategoriaBase = root.dataset.urlVehiculosCategoriaBase;
    const urlSetVehiculoNuevo       = root.dataset.urlSetVehiculoNuevo;

    const csrfTokenMeta = document.querySelector('meta[name="csrf-token"]');
    const csrfToken = csrfTokenMeta ? csrfTokenMeta.getAttribute('content') : '';

    // ==============================
    // COMPRESION DE IMAGENES
    // ==============================
    function compressImage(file, maxWidth = 1600, quality = 0.7) {
        return new Promise((resolve, reject) => {
            try {
                const img = new Image();

                img.onload = () => {
                    let width = img.width;
                    let height = img.height;

                    if (width > maxWidth) {
                        const ratio = maxWidth / width;
                        width = maxWidth;
                        height = height * ratio;
                    }

                    const canvas = document.createElement('canvas');
                    canvas.width = width;
                    canvas.height = height;

                    const ctx = canvas.getContext('2d');
                    ctx.drawImage(img, 0, 0, width, height);

                    canvas.toBlob(
                        (blob) => {
                            if (!blob) {
                                return reject(new Error('No se pudo comprimir la imagen'));
                            }

                            const baseName = file.name.replace(/\.[^.]+$/, '');
                            const newName = `${baseName}-cmp.jpg`;

                            const compressedFile = new File([blob], newName, {
                                type: 'image/jpeg',
                                lastModified: Date.now(),
                            });

                            debugCambioAuto('Imagen comprimida', {
                                originalSizeMB: (file.size / (1024 * 1024)).toFixed(2),
                                compressedSizeMB: (compressedFile.size / (1024 * 1024)).toFixed(2),
                                name: newName,
                            });

                            resolve(compressedFile);
                        },
                        'image/jpeg',
                        quality
                    );
                };

                img.onerror = () => reject(new Error('No se pudo leer la imagen'));
                const url = URL.createObjectURL(file);
                img.onloadend = () => {
                    URL.revokeObjectURL(url);
                };
                img.src = url;
            } catch (err) {
                reject(err);
            }
        });
    }

    let contextoActual = null;
    let zonaActual = null;
    let puntoActual = null;
    let fotoTemporalUrl = null;

    const modal = document.getElementById("modalDano");
    const modalZonaLabel = document.getElementById("modalZonaLabel");
    const modalContextoLabel = document.getElementById("modalContextoLabel");

    const tipoInput = document.getElementById("tipoDano");
    const comentarioInput = document.getElementById("comentarioDano");
    const costoInput = document.getElementById("costoDano");
    const fotoInput = document.getElementById("fotoDano");
    const previewFoto = document.getElementById("previewFotoDano");

    // ==============================
    // HELPERS TABLAS
    // ==============================
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
            tr.dataset.id = data.id;
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
        recalcularTotal(contexto);
    }

    let mismosDaniosEmpresa = false;
    const btnMismos = document.getElementById("btnMismosDaniosEmpresa");

    if (btnMismos) {
        btnMismos.addEventListener("click", () => {
            mismosDaniosEmpresa = !mismosDaniosEmpresa;

            if (mismosDaniosEmpresa) {
                btnMismos.classList.add("is-active");
                btnMismos.textContent = "✅ Son los mismos daños del checklist";

                document
                    .querySelectorAll('.car-svg[data-context="empresa"] .point-dot')
                    .forEach(circ => circ.classList.add("disabled"));
            } else {
                btnMismos.classList.remove("is-active");
                btnMismos.textContent = "Son los mismos daños del checklist";

                document
                    .querySelectorAll('.car-svg[data-context="empresa"] .point-dot')
                    .forEach(circ => circ.classList.remove("disabled"));
            }
        });
    }

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

    document.querySelectorAll(".car-svg").forEach(svg => {
        const contexto = svg.dataset.context;

        svg.querySelectorAll(".point-dot").forEach(circle => {
            circle.addEventListener("click", () => {
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

    if (fotoInput) {
        fotoInput.addEventListener("change", async (e) => {
            const file = e.target.files[0];

            if (!file) {
                previewFoto.style.display = "none";
                if (fotoTemporalUrl) {
                    URL.revokeObjectURL(fotoTemporalUrl);
                    fotoTemporalUrl = null;
                }
                return;
            }

            try {
                let finalFile = file;
                if (file.type && file.type.startsWith('image/')) {
                    finalFile = await compressImage(file, 1600, 0.7);
                }

                const dt = new DataTransfer();
                dt.items.add(finalFile);
                fotoInput.files = dt.files;

                if (fotoTemporalUrl) {
                    URL.revokeObjectURL(fotoTemporalUrl);
                }
                fotoTemporalUrl = URL.createObjectURL(finalFile);
                previewFoto.src = fotoTemporalUrl;
                previewFoto.style.display = "block";

                debugCambioAuto('Foto de daño preparada (comprimida)', {
                    originalSizeMB: (file.size / (1024 * 1024)).toFixed(2),
                    finalSizeMB: (finalFile.size / (1024 * 1024)).toFixed(2),
                });

            } catch (err) {
                console.error(err);
                // NOTIFICACIÓN DESACTIVADA - solo console
                console.warn('No se pudo procesar la fotografía del daño. Intenta con otra imagen.');
                fotoInput.value = '';
                previewFoto.style.display = "none";
            }
        });
    }

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

    if (btnCancelar) {
        btnCancelar.addEventListener("click", cerrarModal);
    }

    if (modal) {
        modal.addEventListener("click", (e) => {
            if (e.target === modal) {
                cerrarModal();
            }
        });
    }

    if (btnGuardar) {
        btnGuardar.addEventListener("click", async () => {
            if (!contextoActual || !zonaActual) {
                console.warn('Selecciona primero un punto del diagrama.');
                return;
            }

            if (!fotoInput.files[0]) {
                console.warn('La fotografía del daño es obligatoria.');
                return;
            }

            const tipoVal = (tipoInput.value || '').trim();
            const comentarioVal = (comentarioInput.value || '').trim();
            const costoVal = costoInput.value || '';

            const formData = new FormData();
            formData.append('contexto', contextoActual);
            formData.append('zona', zonaActual);
            formData.append('tipo_dano', tipoVal);
            formData.append('comentario', comentarioVal);
            formData.append('costo_estimado', costoVal);
            formData.append('foto', fotoInput.files[0]);

            debugCambioAuto('Guardar daño: preparando envío', {
                contextoActual,
                zonaActual,
                tipo: tipoVal,
                comentario: comentarioVal,
                costo: costoVal,
                tieneFoto: !!fotoInput.files[0],
            });

            try {
                const resp = await fetch(urlGuardarDano, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': csrfToken,
                    },
                    body: formData
                });

                debugCambioAuto('POST urlGuardarDano → response', {
                    url: urlGuardarDano,
                    status: resp.status,
                    ok: resp.ok,
                });

                const data = await resp.json();

                debugCambioAuto('Respuesta JSON guardarDano', data);

                if (!resp.ok || !data.ok) {
                    throw new Error(data.message || 'Error al guardar el daño.');
                }

                const danoResp = data.dano || {};

                agregarFilaDano(contextoActual, {
                    id: danoResp.id,
                    zona: zonaActual,
                    descripcion: tipoVal || comentarioVal,
                    costo_estimado: costoVal
                });

                console.log('Daño guardado correctamente.');
                cerrarModal();

            } catch (error) {
                console.error(error);
                console.error('Ocurrió un error al guardar el daño.');
            }
        });
    }

    // ==============================
    // ELIMINAR DAÑO
    // ==============================
    document.addEventListener('click', function (e) {
        const btn = e.target.closest('.cl2-dano-delete');
        if (!btn) return;

        const idFoto = btn.dataset.id;
        const contexto = btn.dataset.contexto;
        const fila = btn.closest('tr');

        debugCambioAuto('Click eliminar daño', { idFoto, contexto });

        // Confirmación desactivada - acción directa
        const ejecutarEliminacion = async () => {
            try {
                const url = urlEliminarDanoBase.replace('__ID__', idFoto);

                debugCambioAuto('DELETE eliminarDano → disparando fetch', { url });

                const resp = await fetch(url, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json',
                    },
                });

                const data = await resp.json();
                debugCambioAuto('Respuesta JSON eliminarDano', {
                    status: resp.status,
                    ok: resp.ok,
                    data,
                });

                if (!resp.ok || !data.ok) {
                    throw new Error(data.message || 'Error al eliminar el daño.');
                }

                if (fila) fila.remove();

                const tbody = document.querySelector(
                    '.cl2-danos-table[data-context="' + contexto + '"] tbody'
                );
                if (tbody && !tbody.querySelector('.cl2-dano-row')) {
                    const trEmpty = document.createElement('tr');
                    trEmpty.classList.add('cl2-danos-empty');
                    trEmpty.innerHTML = '<td colspan="5">Sin daños registrados.</td>';
                    tbody.appendChild(trEmpty);
                }

                recalcularTotal(contexto);

                console.log('Daño eliminado.');
            } catch (error) {
                console.error(error);
                console.error('Ocurrió un error al eliminar el daño.');
            }
        };

        // Eliminación directa sin confirmación
        ejecutarEliminacion();
    });

    // ==============================
    // CAMBIO DE AUTO
    // ==============================
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

    if (filtroColor)  filtroColor.addEventListener('input', renderVehiculosLista);
    if (filtroModelo) filtroModelo.addEventListener('input', renderVehiculosLista);
    if (filtroSerie)  filtroSerie.addEventListener('input', renderVehiculosLista);

    if (selectCategoriaCliente && urlVehiculosCategoriaBase) {
        selectCategoriaCliente.addEventListener('change', async (e) => {
            const idCat = e.target.value;

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
                console.error('No se pudieron cargar los vehículos de esa categoría.');
            }
        });
    }

    if (listaVehiculos && urlSetVehiculoNuevo) {
        listaVehiculos.addEventListener('click', async (e) => {
            const btn = e.target.closest('.btn-vehiculo');
            if (!btn) return;

            const idVehiculo = btn.dataset.id;
            if (!idVehiculo) return;

            debugCambioAuto('Seleccionar vehículo en modal', {
                idVehiculo,
                tipo: btn.dataset.tipo,
                modelo: btn.dataset.modelo,
                placas: btn.dataset.placas,
                transmision: btn.dataset.transmision,
                fuel: btn.dataset.fuel,
                km: btn.dataset.km,
            });

            if (spanTipo)   spanTipo.textContent   = btn.dataset.tipo || 'N/A';
            if (spanModelo) spanModelo.textContent = btn.dataset.modelo || 'N/A';
            if (spanPlacas) spanPlacas.textContent = btn.dataset.placas || 'N/A';
            if (spanTrans)  spanTrans.textContent  = btn.dataset.transmision || 'N/A';
            if (spanFuel)   spanFuel.textContent   = btn.dataset.fuel || 'N/A';
            if (spanKm)     spanKm.textContent     = btn.dataset.km || 'N/A';

            if (inputIdVehiculoNuevo) {
                inputIdVehiculoNuevo.value = idVehiculo;
            }

            try {
                debugCambioAuto('POST setVehiculoNuevo → disparando fetch', {
                    url: urlSetVehiculoNuevo,
                    payload: { id_vehiculo_nuevo: idVehiculo },
                });

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

                debugCambioAuto('Respuesta JSON setVehiculoNuevo', {
                    status: resp.status,
                    ok: resp.ok,
                    data,
                });

                if (!resp.ok || !data.ok) {
                    throw new Error(data.message || 'Error al asignar el vehículo nuevo.');
                }

                console.log('Vehículo seleccionado para el cambio (en proceso).');
                cerrarModalVehiculos();

            } catch (error) {
                console.error(error);
                console.error('No se pudo asignar el vehículo nuevo.');
            }
        });
    }

    // ==============================
    // SYNC ALTURA
    // ==============================
    function syncExtraBlocksHeight() {
        const blocks = document.querySelectorAll('.cl2-extra-block');
        if (blocks.length < 2) return;

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

    // ==============================
    // FOTOS CAMBIO
    // ==============================
    const inputCambio = document.getElementById('inputFotosCambio');
    const previewCambio = document.getElementById('preview-fotosCambio');
    const msgCambio = document.querySelector('#uploaderCambioAuto .cl2-photo-msg');

    let filesCambio = [];

    function actualizarInputCambio() {
        if (!inputCambio) return;

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

        syncExtraBlocksHeight();
    }

    if (inputCambio) {
        inputCambio.addEventListener('change', async (e) => {
            const nuevas = Array.from(e.target.files || []);
            if (!nuevas.length) return;

            const comprimidas = [];

            for (const file of nuevas) {
                if (!file.type || !file.type.startsWith('image/')) {
                    comprimidas.push(file);
                    continue;
                }

                try {
                    const compressed = await compressImage(file, 1600, 0.7);
                    comprimidas.push(compressed);
                } catch (err) {
                    console.error('Error al comprimir imagen de cambio:', err);
                    comprimidas.push(file);
                }
            }

            filesCambio = filesCambio.concat(comprimidas);
            inputCambio.value = '';
            renderPreviewCambio();
            actualizarInputCambio();
        });
    }

    if (previewCambio) {
        previewCambio.addEventListener('click', (e) => {
            const btn = e.target.closest('.cl2-photo-remove');
            if (!btn) return;

            const index = parseInt(btn.dataset.index, 10);
            if (isNaN(index)) return;

            filesCambio.splice(index, 1);
            renderPreviewCambio();
            actualizarInputCambio();
        });
    }

    recalcularTotal('empresa');
    recalcularTotal('cliente');

    syncExtraBlocksHeight();

    window.addEventListener('load', syncExtraBlocksHeight);
    window.addEventListener('resize', syncExtraBlocksHeight);

})();
