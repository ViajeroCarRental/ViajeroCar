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
        fill: rgba(255,255,255,0.95);
        stroke: #ff4d6a;
        stroke-width: 4;
        cursor: pointer;
        transition: stroke-width .15s ease, filter .15s ease;
    }

    /* YA NO USA SCALE -> YA NO SE MUEVE EL PUNTO */
    .point-dot:hover {
        stroke-width: 6;
    }

    .point-dot.selected {
        stroke-width: 8;
        filter: drop-shadow(0 0 6px rgba(255,0,0,.7));
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

    {{-- ================== TABLA DERECHA ================== --}}
    <div class="tabla-entrega">
        <h3>EL CLIENTE SE LO LLEVA</h3>

        <table class="entrega">
            <tr><td>PLACAS</td><td>2</td><td>ESPEJOS LATERALES</td><td>2</td></tr>
            <tr><td>TOLDO-JEEP</td><td>1</td><td>ESPEJO INTERIOR</td><td>1</td></tr>
            <tr><td>TARJETA DE CIRCULACIÓN</td><td>1</td><td>ANTENA</td><td>1</td></tr>
            <tr><td>TARJETA DE VERIFICACIÓN</td><td>1</td><td>RADIO</td><td>1</td></tr>
            <tr><td>PÓLIZA DE SEGURO</td><td>1</td><td>TAPÓN DE GASOLINA</td><td>1</td></tr>
            <tr><td>LLANTA DE REFACCIÓN</td><td>1</td><td>TAPETES</td><td>4</td></tr>
            <tr><td>GATO</td><td>1</td><td>LLAVE DE ENCENDIDO</td><td>1</td></tr>
            <tr><td>HERRAMIENTA</td><td>1</td><td>AVISO DE SEGURIDAD</td><td>1</td></tr>
            <tr><td>POLVERAS</td><td>4</td><td>TUERCA DE SEGURIDAD</td><td>1</td></tr>
        </table>
    </div>

</div>

{{-- ================== MODAL ================== --}}
<div id="modalDaño">
    <div class="box">
        <h4 id="tituloModal">Zona</h4>
        <textarea id="comentarioDaño"
                  placeholder="Describe el daño o comentario..."></textarea>
        <button type="button" id="guardarDaño" class="btn btn-save">Guardar</button>
        <button type="button" id="cancelarDaño" class="btn btn-cancel">Cancelar</button>
    </div>
</div>

<script>
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

    document.querySelectorAll(".point-dot").forEach(p => {
        p.addEventListener("click", () => {
            const z = p.dataset.zone;
            p.classList.toggle("selected");

            document.getElementById("tituloModal").textContent =
                nombresZonas[z] || ("Zona " + z);

            document.getElementById("comentarioDaño").value = "";
            modal.style.display = "flex";
        });
    });

    document.getElementById("guardarDaño").onclick =
    document.getElementById("cancelarDaño").onclick = () => {
        modal.style.display = "none";
    };
</script>
