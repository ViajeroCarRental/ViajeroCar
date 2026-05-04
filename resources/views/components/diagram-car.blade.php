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
        position: fixed;
        inset: 0;
        background: rgba(0,0,0,.5);
        justify-content: center;
        align-items: center;
        z-index: 9999;
    }

    #modalDaño .box {
        background: white;
        width: 320px;
        padding: 22px;
        border-radius: 14px;
        box-shadow: 0 0 15px rgba(0,0,0,.15);
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

            {{-- DEFENSA DELANTERA --}}
            <circle class="point-dot" data-zone="1" cx="400" cy="120" r="26" />
            <circle class="point-dot" data-zone="2" cx="400" cy="210" r="26" />

            {{-- COFRE / PARABRISAS --}}
            <circle class="point-dot" data-zone="5" cx="400" cy="365" r="26" />

            {{-- COSTADOS FRONTALES --}}
            <circle class="point-dot" data-zone="3" cx="155" cy="385" r="26" />
            <circle class="point-dot" data-zone="4" cx="645" cy="385" r="26" />

            {{-- PUERTAS DELANTERAS --}}
            <circle class="point-dot" data-zone="6" cx="155" cy="525" r="26" />
            <circle class="point-dot" data-zone="7" cx="645" cy="525" r="26" />

            {{-- PUERTAS TRASERAS --}}
            <circle class="point-dot" data-zone="8" cx="155" cy="685" r="26" />
            <circle class="point-dot" data-zone="9" cx="645" cy="685" r="26" />

            {{-- TECHO --}}
            <circle class="point-dot" data-zone="10" cx="400" cy="640" r="26" />

            {{-- COSTADOS TRASEROS --}}
            <circle class="point-dot" data-zone="11" cx="155" cy="845" r="26" />
            <circle class="point-dot" data-zone="12" cx="645" cy="845" r="26" />

            {{-- DEFENSA TRASERA --}}
            <circle class="point-dot" data-zone="13" cx="400" cy="1010" r="26" />

            {{-- LLANTAS EXACTAS --}}
            <circle class="point-dot" data-zone="15" cx="117"  cy="458" r="26" />
            <circle class="point-dot" data-zone="16" cx="682"  cy="458" r="26" />
            <circle class="point-dot" data-zone="17" cx="117"  cy="908" r="26" />
            <circle class="point-dot" data-zone="18" cx="682"  cy="908" r="26" />

        </svg>
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

<<script>
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

    const modal = document.getElementById("modalDaño");
    const idContrato = document.getElementById("idContrato").value;

    let zonaSeleccionada = null;

        // 🔹 Al cargar el componente, marcamos TODOS los ítems de inventario
    document.querySelectorAll(".itemCheck").forEach(chk => {
        chk.checked = true;
    });


    // ======================================
    // 1) CUANDO SE PRESIONA UN PUNTO
    // ======================================
    document.querySelectorAll(".point-dot").forEach(punto => {
        punto.addEventListener("click", () => {

            zonaSeleccionada = punto.dataset.zone;

            // Cambiar título del modal
            document.getElementById("tituloModal").textContent =
                nombresZonas[zonaSeleccionada];

            // Limpiar textarea
            document.getElementById("comentarioDaño").value = "";
            document.getElementById("fotoDaño").value = "";
            document.getElementById("previewFoto").innerHTML = "";
            fotoSeleccionada = null;

            modal.style.display = "flex";
        });
    });

    // ======================================
    // 2) GUARDAR DAÑO EN BD y foto
    // ======================================

    let fotoSeleccionada = null;

    document.getElementById("guardarDaño").onclick = async () => {

        const comentario = document.getElementById("comentarioDaño").value.trim();

        if (!comentario) {
            alert("Escribe un comentario del daño.");
            return;
        }

        try {
              const formData = new FormData();
        formData.append("id_contrato", idContrato);
        formData.append("zona", zonaSeleccionada);
        formData.append("comentario", comentario);
        formData.append("modo", "{{ $modo }}");

        if (fotoSeleccionada) {
            formData.append("foto", fotoSeleccionada);
        }


            const resp = await fetch("{{ route('contrato.guardarDano', ['id' => $id]) }}", {
                method: "POST",
                headers: {
                    //"Content-Type": "application/json",
                    "X-CSRF-TOKEN": "{{ csrf_token() }}"
                },
                  body: formData
            });

            const data = await resp.json();

            if (data.ok) {
                // marcar punto como seleccionado visualmente
                document.querySelector(`.point-dot[data-zone="${zonaSeleccionada}"]`)
                    .classList.add("selected");

                modal.style.display = "none";
            } else {
                alert("Error al guardar: " + data.msg);
            }

        } catch (e) {
            alert("Error de conexión.");
            console.error(e);
        }
    };

    function compressImage(file, maxWidth = 1200, quality = 0.7) {
    return new Promise((resolve, reject) => {
        const img = new Image();

        img.onload = () => {
            let width = img.width;
            let height = img.height;

            if (width > maxWidth) {
                const ratio = maxWidth / width;
                width = maxWidth;
                height = height * ratio;
            }

            const canvas = document.createElement("canvas");
            canvas.width = width;
            canvas.height = height;

            const ctx = canvas.getContext("2d");
            ctx.drawImage(img, 0, 0, width, height);

            canvas.toBlob(
                (blob) => {
                    if (!blob) return reject("Error al comprimir");

                    const newFile = new File([blob], "foto.jpg", {
                        type: "image/jpeg"
                    });

                    resolve(newFile);
                },
                "image/jpeg",
                quality
            );
        };

        img.onerror = reject;
        img.src = URL.createObjectURL(file);
    });
}

    document.getElementById("fotoDaño").addEventListener("change", async (e) => {
    const file = e.target.files[0];
    if (!file) return;

    try {
        const compressed = await compressImage(file);
        fotoSeleccionada = compressed;

        const reader = new FileReader();
        reader.onload = (ev) => {
            document.getElementById("previewFoto").innerHTML =
                `<img src="${ev.target.result}" style="width:100%; border-radius:8px;">`;
        };
        reader.readAsDataURL(compressed);

    } catch (err) {
        alert("Error al procesar imagen");
    }
});


        document.getElementById("guardarInventario").addEventListener("click", async () => {
        const idContrato = document.getElementById("idContrato").value;

        const items = {};

        // 🔹 Solo agregamos al payload los que SIGUEN marcados
        document.querySelectorAll(".itemCheck").forEach(chk => {
            if (chk.checked) {
                items[chk.dataset.item] = 1;
            }
        });

        try {
            const resp = await fetch("{{ route('contrato.guardarInventario') }}", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    "X-CSRF-TOKEN": "{{ csrf_token() }}"
                },
                body: JSON.stringify({
                    id_contrato: idContrato,
                    inventario: items
                })
            });

            const data = await resp.json();

            if (!resp.ok || !data.ok) {
                alert(data.msg || "Error al guardar inventario.");
                return;
            }

            alert(data.msg || "Inventario guardado.");
        } catch (e) {
            console.error(e);
            alert("Error de conexión al guardar el inventario.");
        }
    });



    // ======================================
    // 3) CERRAR MODAL
    // ======================================
    document.getElementById("cancelarDaño").onclick = () => {
        modal.style.display = "none";
    };

    // ======================================
    // 4) AL CARGAR LA VISTA – MARCAR ZONAS YA GUARDADAS
    // ======================================
    async function cargarDanos() {
        const resp = await fetch(`/admin/checklist/${idContrato}/danos`);
        const data = await resp.json();

        if (data.ok) {
            data.danos.forEach(d => {
                document.querySelector(`.point-dot[data-zone="${d.zona}"]`)
                    ?.classList.add("selected");
            });
        }
    }

    cargarDanos();
</script>

