<style>
    /* ===== TARJETA COMPLETA ===== */
    .checklist-card {
        width: 100%;
        background: #ffffff;
        border-radius: 20px;
        padding: 30px;
        box-shadow: 0 4px 14px rgba(0,0,0,.06);
        display: flex;
        gap: 40px;
        align-items: flex-start;
    }

    /* ===== AUTO ===== */
    .car-box {
        width: 280px;
        position: relative;
        padding: 0;
    }

    .car-svg {
        width: 100%;
        height: auto;
        display: block;
    }

    .interior-damage {
        width: 100%;
        margin-top: 12px;
        padding: 11px 14px;
        border: 2px solid #ff4d6a;
        border-radius: 12px;
        background: #fff;
        color: #333;
        font-weight: 800;
        cursor: pointer;
        transition: background .15s ease, color .15s ease, box-shadow .15s ease;
    }

    .interior-damage:hover,
    .interior-damage.selected {
        background: #ff4d6a;
        color: #fff;
        box-shadow: 0 5px 14px rgba(255,77,106,.25);
    }

    /* ===== PUNTOS SVG ===== */
    .point-dot {
    fill: rgba(255,255,255,0.95);        /* círculo casi blanco cuando NO hay daño */
    stroke: #ff4d6a;
    stroke-width: 4;
    cursor: pointer;
    transition: stroke-width .15s ease,
                filter .15s ease,
                fill .15s ease;          /* 👈 añadimos transición del fill */
}


    /* YA NO USA SCALE -> YA NO SE MUEVE EL PUNTO */
    .point-dot:hover {
        stroke-width: 6;
    }

    .point-dot.selected {
    stroke-width: 8;
    filter: drop-shadow(0 0 6px rgba(255,0,0,.7));
    fill: rgba(255,77,106,0.9);   /* 👈 AQUÍ se rellena el círculo */
}


    /* ===== TABLA DERECHA ===== */
    .tabla-entrega {
        flex: 1;
        margin-top: 10px;
    }

    .tabla-entrega h3 {
        font-weight: 900;
        margin-bottom: 15px;
        font-size: 22px;
        text-align: center;
    }

    table.entrega {
        width: 100%;
        border-collapse: collapse;
        font-size: 15px;
    }

    table.entrega tr:nth-child(even) {
        background: #f7f7f7;
    }

    table.entrega td {
        padding: 10px 15px;
        border-bottom: 1px solid #eee;
    }

    table.entrega tr:last-child td {
        border-bottom: none;
    }

    table.entrega td:nth-child(2),
    table.entrega td:nth-child(4) {
        text-align: right;
        font-weight: bold;
    }

    /* ===== MODAL ===== */
    #modalDaño {
        display: none;
        position: fixed !important;
        inset: 0 !important;
        width: 100vw;
        height: 100vh;
        padding: 20px;
        background: rgba(0, 0, 0, 0.55);
        z-index: 999999;

        align-items: center;
        justify-content: center;

        box-sizing: border-box;
    }

    #modalDaño .box {
        position: relative;
        width: 100%;
        max-width: 360px;
        padding: 22px;
        margin: 0;

        background: #ffffff;
        border-radius: 14px;
        box-shadow: 0 18px 45px rgba(0, 0, 0, 0.25);

        box-sizing: border-box;
    }

    #modalDaño h4 {
        font-size: 18px;
        font-weight: 800;
        margin-bottom: 10px;
    }

    #modalDaño textarea {
        width: 100%;
        padding: 10px;
        resize: none;
        min-height: 90px;
        background: #f7f7f7;
        border-radius: 10px;
        border: 1px solid #ddd;
    }

    .btn {
        width: 100%;
        padding: 10px;
        border-radius: 10px;
        border: none;
        margin-top: 10px;
        font-weight: 600;
        font-size: 15px;
        cursor: pointer;
    }

    .btn-save { background:#1976D2; color:white; }
    .btn-cancel { background:#aaa; color:white; }
</style>


<div class="checklist-card">

    {{-- ================== AUTO ================== --}}
    <div class="car-box">
        <svg id="carSVG" class="car-svg" viewBox="0 0 800 1280">

            {{-- imagen base --}}
            <image href="{{ asset('img/diagrama-carro-danos3.png') }}"
                   x="0" y="0" width="800" height="1280" />

            {{-- ================== PUNTOS ================== --}}

            {{-- FASCIA DELANTERA, COFRE Y PARABRISAS --}}
            <circle class="point-dot damage-trigger" data-zone="1" cx="400" cy="120" r="26">
                <title>Fascia delantera</title>
            </circle>
            <circle class="point-dot damage-trigger" data-zone="2" cx="400" cy="350" r="26">
                <title>Cofre</title>
            </circle>
            <circle class="point-dot damage-trigger" data-zone="5" cx="400" cy="500" r="26">
                <title>Parabrisas</title>
            </circle>

            {{-- ESTRIBOS --}}
            <circle class="point-dot damage-trigger" data-zone="3" cx="105" cy="655" r="26">
                <title>Estribo izquierdo</title>
            </circle>
            <circle class="point-dot damage-trigger" data-zone="4" cx="695" cy="655" r="26">
                <title>Estribo derecho</title>
            </circle>

            {{-- PUERTAS DELANTERAS --}}
            <circle class="point-dot damage-trigger" data-zone="6" cx="190" cy="555" r="26">
                <title>Puerta piloto delantera</title>
            </circle>
            <circle class="point-dot damage-trigger" data-zone="7" cx="610" cy="555" r="26">
                <title>Puerta copiloto delantera</title>
            </circle>

            {{-- PUERTAS DE PASAJEROS --}}
            <circle class="point-dot damage-trigger" data-zone="8" cx="190" cy="735" r="26">
                <title>Puerta pasajeros izquierda</title>
            </circle>
            <circle class="point-dot damage-trigger" data-zone="9" cx="610" cy="735" r="26">
                <title>Puerta pasajeros derecha</title>
            </circle>

            {{-- TECHO, PUERTA TRASERA Y FASCIA TRASERA --}}
            <circle class="point-dot damage-trigger" data-zone="10" cx="400" cy="690" r="26">
                <title>Techo</title>
            </circle>
            <circle class="point-dot damage-trigger" data-zone="11" cx="400" cy="930" r="26">
                <title>Puerta trasera</title>
            </circle>
            <circle class="point-dot damage-trigger" data-zone="13" cx="400" cy="1110" r="26">
                <title>Fascia trasera</title>
            </circle>

            {{-- LLANTAS --}}
            <circle class="point-dot damage-trigger" data-zone="15" cx="65" cy="377" r="26">
                <title>Llanta delantera izquierda</title>
            </circle>
            <circle class="point-dot damage-trigger" data-zone="16" cx="718" cy="377" r="26">
                <title>Llanta delantera derecha</title>
            </circle>
            <circle class="point-dot damage-trigger" data-zone="17" cx="65" cy="850" r="26">
                <title>Llanta trasera izquierda</title>
            </circle>
            <circle class="point-dot damage-trigger" data-zone="18" cx="718" cy="850" r="26">
                <title>Llanta trasera derecha</title>
            </circle>

        </svg>

        {{-- Interiores queda fuera del auto y puede registrar varios daños --}}
        <button type="button"
                class="interior-damage damage-trigger"
                data-zone="14">
            + Agregar daño en interiores
        </button>
    </div>

    <div class="tabla-entrega">
    <h3>EL CLIENTE SE LO LLEVA</h3>

    <table class="entrega">
        <tr>
            <td>PLACAS</td>
            <td><input type="checkbox" class="itemCheck" data-item="placas"></td>

            <td>ESPEJOS LATERALES</td>
            <td><input type="checkbox" class="itemCheck" data-item="espejos_laterales"></td>
        </tr>

        <tr>
            <td>TOLDO-JEEP</td>
            <td><input type="checkbox" class="itemCheck" data-item="toldo"></td>

            <td>ESPEJO INTERIOR</td>
            <td><input type="checkbox" class="itemCheck" data-item="espejo_interior"></td>
        </tr>

        <tr>
            <td>TARJETA DE CIRCULACIÓN</td>
            <td><input type="checkbox" class="itemCheck" data-item="tcirculacion"></td>

            <td>ANTENA</td>
            <td><input type="checkbox" class="itemCheck" data-item="antena"></td>
        </tr>

        <tr>
            <td>PÓLIZA DE SEGURO</td>
            <td><input type="checkbox" class="itemCheck" data-item="poliza"></td>

            <td>TAPÓN DE GASOLINA</td>
            <td><input type="checkbox" class="itemCheck" data-item="tapon_gasolina"></td>
        </tr>

        <tr>
            <td>LLANTA DE REFACCIÓN</td>
            <td><input type="checkbox" class="itemCheck" data-item="refaccion"></td>

            <td>TAPETES</td>
            <td><input type="checkbox" class="itemCheck" data-item="tapetes"></td>
        </tr>

        <tr>
            <td>GATO</td>
            <td><input type="checkbox" class="itemCheck" data-item="gato"></td>

            <td>LLAVE DE ENCENDIDO</td>
            <td><input type="checkbox" class="itemCheck" data-item="llave_encendido"></td>
        </tr>
    </table>

    <button id="guardarInventario" class="btn btn-save" style="margin-top:20px;">
        Guardar inventario
    </button>
</div>


</div>

{{-- ================== MODAL ================== --}}
<div id="modalDaño">
    <div class="box">
        <h4 id="tituloModal">Zona</h4>
        <textarea id="comentarioDaño"
                  placeholder="Describe el daño o comentario..."></textarea>

                     <!-- 🔥 NUEVO -->
        <input type="file"
               id="fotoDaño"
               accept="image/*"
               capture="environment">

        <!-- preview -->
        <div id="previewFoto" style="margin-top:10px;"></div>

        <button type="button" id="guardarDaño" class="btn btn-save">Guardar</button>
        <button type="button" id="cancelarDaño" class="btn btn-cancel">Cancelar</button>
    </div>
</div>

<script>
document.addEventListener("DOMContentLoaded", () => {

    /* =====================================================
       CONFIGURACIÓN DE ZONAS
    ===================================================== */

    const nombresZonas = {
        1: "Fascia delantera",
        2: "Cofre",
        3: "Estribo izquierdo",
        4: "Estribo derecho",
        5: "Parabrisas",
        6: "Puerta piloto delantera",
        7: "Puerta copiloto delantera",
        8: "Puerta pasajeros izquierda",
        9: "Puerta pasajeros derecha",
        10: "Techo",
        11: "Puerta trasera",
        13: "Fascia trasera",
        14: "Interiores",
        15: "Llanta delantera izquierda",
        16: "Llanta delantera derecha",
        17: "Llanta trasera izquierda",
        18: "Llanta trasera derecha"
    };

    /* =====================================================
       ELEMENTOS GENERALES
    ===================================================== */

    const modal = document.getElementById("modalDaño");
    const tituloModal = document.getElementById("tituloModal");
    const comentarioInput = document.getElementById("comentarioDaño");
    const fotoInput = document.getElementById("fotoDaño");
    const previewFoto = document.getElementById("previewFoto");
    const guardarDañoBtn = document.getElementById("guardarDaño");
    const cancelarDañoBtn = document.getElementById("cancelarDaño");
    const guardarInventarioBtn = document.getElementById("guardarInventario");
    const contratoInput = document.getElementById("idContrato");

    if (!modal || !contratoInput) {
        console.error("No se encontró el modal de daños o el ID del contrato.");
        return;
    }

    const idContrato = contratoInput.value;

    let zonaSeleccionada = null;
    let fotoSeleccionada = null;
    let urlPreviewActual = null;
    let posicionScrollModal = 0;

    /* =====================================================
       FUNCIONES DEL MODAL
    ===================================================== */

    function abrirModal(zona) {
        zonaSeleccionada = zona;

        tituloModal.textContent =
            nombresZonas[zonaSeleccionada] || "Registrar daño";

        comentarioInput.value = "";
        fotoInput.value = "";
        previewFoto.innerHTML = "";
        fotoSeleccionada = null;

        if (urlPreviewActual) {
            URL.revokeObjectURL(urlPreviewActual);
            urlPreviewActual = null;
        }

        /*
         * Mueve el modal directamente al body para que
         * position: fixed tome como referencia la pantalla.
         */
        if (modal.parentElement !== document.body) {
            document.body.appendChild(modal);
        }

        /* Guarda exactamente dónde está viendo el usuario */
        posicionScrollModal = window.scrollY;

        document.body.style.position = "fixed";
        document.body.style.top = `-${posicionScrollModal}px`;
        document.body.style.left = "0";
        document.body.style.right = "0";
        document.body.style.width = "100%";
        document.body.style.overflow = "hidden";

        modal.style.display = "flex";

        setTimeout(() => {
            comentarioInput.focus({
                preventScroll: true
            });
        }, 100);
    }

    function cerrarModal() {
        modal.style.display = "none";

        document.body.style.position = "";
        document.body.style.top = "";
        document.body.style.left = "";
        document.body.style.right = "";
        document.body.style.width = "";
        document.body.style.overflow = "";

        window.scrollTo({
            top: posicionScrollModal,
            left: 0,
            behavior: "instant"
        });

        comentarioInput.value = "";
        fotoInput.value = "";
        previewFoto.innerHTML = "";

        zonaSeleccionada = null;
        fotoSeleccionada = null;

        if (urlPreviewActual) {
            URL.revokeObjectURL(urlPreviewActual);
            urlPreviewActual = null;
        }
    }

    /* =====================================================
       MARCAR INVENTARIO INICIALMENTE
    ===================================================== */

    document.querySelectorAll(".itemCheck").forEach(check => {
        check.checked = true;
    });

    /* =====================================================
       ABRIR MODAL AL PRESIONAR UNA ZONA
    ===================================================== */

    document.querySelectorAll(".damage-trigger").forEach(punto => {
        punto.addEventListener("click", () => {
            abrirModal(punto.dataset.zone);
        });
    });

    /* =====================================================
       COMPRIMIR IMAGEN
    ===================================================== */

    function compressImage(file, maxWidth = 1200, quality = 0.7) {
        return new Promise((resolve, reject) => {
            const img = new Image();
            const imageUrl = URL.createObjectURL(file);

            img.onload = () => {
                let width = img.naturalWidth;
                let height = img.naturalHeight;

                if (width > maxWidth) {
                    const ratio = maxWidth / width;

                    width = maxWidth;
                    height = Math.round(height * ratio);
                }

                const canvas = document.createElement("canvas");
                canvas.width = width;
                canvas.height = height;

                const context = canvas.getContext("2d");

                if (!context) {
                    URL.revokeObjectURL(imageUrl);
                    reject(new Error("No se pudo procesar la imagen."));
                    return;
                }

                context.drawImage(img, 0, 0, width, height);

                canvas.toBlob(
                    blob => {
                        URL.revokeObjectURL(imageUrl);

                        if (!blob) {
                            reject(new Error("No se pudo comprimir la imagen."));
                            return;
                        }

                        const nombreOriginal =
                            file.name.replace(/\.[^/.]+$/, "") || "foto";

                        const archivoComprimido = new File(
                            [blob],
                            `${nombreOriginal}.jpg`,
                            {
                                type: "image/jpeg",
                                lastModified: Date.now()
                            }
                        );

                        resolve(archivoComprimido);
                    },
                    "image/jpeg",
                    quality
                );
            };

            img.onerror = () => {
                URL.revokeObjectURL(imageUrl);
                reject(new Error("No se pudo leer la imagen."));
            };

            img.src = imageUrl;
        });
    }

    /* =====================================================
       SELECCIONAR Y MOSTRAR FOTO
    ===================================================== */

    fotoInput.addEventListener("change", async event => {
        const file = event.target.files[0];

        if (!file) {
            fotoSeleccionada = null;
            previewFoto.innerHTML = "";
            return;
        }

        if (!file.type.startsWith("image/")) {
            alert("Selecciona un archivo de imagen válido.");
            fotoInput.value = "";
            return;
        }

        try {
            fotoInput.disabled = true;

            const imagenComprimida = await compressImage(file);
            fotoSeleccionada = imagenComprimida;

            if (urlPreviewActual) {
                URL.revokeObjectURL(urlPreviewActual);
            }

            urlPreviewActual = URL.createObjectURL(imagenComprimida);

            previewFoto.innerHTML = `
                <img
                    src="${urlPreviewActual}"
                    alt="Vista previa del daño"
                    style="
                        display:block;
                        width:100%;
                        max-height:240px;
                        object-fit:contain;
                        border-radius:8px;
                    "
                >
            `;
        } catch (error) {
            console.error(error);
            fotoSeleccionada = null;
            fotoInput.value = "";
            previewFoto.innerHTML = "";

            alert("Ocurrió un error al procesar la imagen.");
        } finally {
            fotoInput.disabled = false;
        }
    });

    /* =====================================================
       GUARDAR DAÑO
    ===================================================== */

    guardarDañoBtn.addEventListener("click", async () => {
        const comentario = comentarioInput.value.trim();

        if (!zonaSeleccionada) {
            alert("No se seleccionó una zona del vehículo.");
            return;
        }

        if (!comentario) {
            alert("Escribe un comentario del daño.");
            comentarioInput.focus();
            return;
        }

        const textoOriginal = guardarDañoBtn.textContent;

        guardarDañoBtn.disabled = true;
        guardarDañoBtn.textContent = "Guardando...";

        try {
            const formData = new FormData();

            formData.append("id_contrato", idContrato);
            formData.append("zona", zonaSeleccionada);
            formData.append("comentario", comentario);
            formData.append("modo", "{{ $modo }}");

            if (fotoSeleccionada) {
                formData.append("foto", fotoSeleccionada);
            }

            const respuesta = await fetch(
                "{{ route('contrato.guardarDano', ['id' => $id]) }}",
                {
                    method: "POST",
                    headers: {
                        "X-CSRF-TOKEN": "{{ csrf_token() }}",
                        "Accept": "application/json"
                    },
                    body: formData
                }
            );

            const data = await respuesta.json();

            if (!respuesta.ok || !data.ok) {
                alert(data.msg || "No se pudo guardar el daño.");
                return;
            }

            const puntoSeleccionado = document.querySelector(
                `.damage-trigger[data-zone="${zonaSeleccionada}"]`
            );

            puntoSeleccionado?.classList.add("selected");

            cerrarModal();

        } catch (error) {
            console.error(error);
            alert("Error de conexión al guardar el daño.");

        } finally {
            guardarDañoBtn.disabled = false;
            guardarDañoBtn.textContent = textoOriginal;
        }
    });

    /* =====================================================
       CERRAR MODAL
    ===================================================== */

    cancelarDañoBtn.addEventListener("click", cerrarModal);

    /*
     * Cierra al presionar directamente sobre el fondo oscuro.
     */
    modal.addEventListener("click", event => {
        if (event.target === modal) {
            cerrarModal();
        }
    });

    /*
     * Cierra con la tecla Escape.
     */
    document.addEventListener("keydown", event => {
        if (
            event.key === "Escape" &&
            modal.style.display === "flex"
        ) {
            cerrarModal();
        }
    });

    /* =====================================================
       GUARDAR INVENTARIO
    ===================================================== */

    guardarInventarioBtn?.addEventListener("click", async () => {
        const items = {};

        document.querySelectorAll(".itemCheck").forEach(check => {
            if (check.checked) {
                items[check.dataset.item] = 1;
            }
        });

        const textoOriginal = guardarInventarioBtn.textContent;

        guardarInventarioBtn.disabled = true;
        guardarInventarioBtn.textContent = "Guardando...";

        try {
            const respuesta = await fetch(
                "{{ route('contrato.guardarInventario') }}",
                {
                    method: "POST",
                    headers: {
                        "Content-Type": "application/json",
                        "X-CSRF-TOKEN": "{{ csrf_token() }}",
                        "Accept": "application/json"
                    },
                    body: JSON.stringify({
                        id_contrato: idContrato,
                        inventario: items
                    })
                }
            );

            const data = await respuesta.json();

            if (!respuesta.ok || !data.ok) {
                alert(data.msg || "Error al guardar el inventario.");
                return;
            }

            alert(data.msg || "Inventario guardado correctamente.");

        } catch (error) {
            console.error(error);
            alert("Error de conexión al guardar el inventario.");

        } finally {
            guardarInventarioBtn.disabled = false;
            guardarInventarioBtn.textContent = textoOriginal;
        }
    });

    /* =====================================================
       CARGAR DAÑOS GUARDADOS
    ===================================================== */

    async function cargarDanos() {
        try {
            const respuesta = await fetch(
                `/admin/checklist/${idContrato}/danos`,
                {
                    headers: {
                        "Accept": "application/json"
                    }
                }
            );

            const data = await respuesta.json();

            if (!respuesta.ok || !data.ok) {
                console.error(
                    data.msg || "No se pudieron cargar los daños."
                );
                return;
            }

            data.danos.forEach(dano => {
                const punto = document.querySelector(
                    `.damage-trigger[data-zone="${dano.zona}"]`
                );

                punto?.classList.add("selected");
            });

        } catch (error) {
            console.error("Error al cargar los daños:", error);
        }
    }

    cargarDanos();
});
</script>
