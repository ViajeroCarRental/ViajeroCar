<style>
    /* ===== TARJETA COMPLETA ===== */
    .checklist-card {
        width: 100%;
        background: white;
        border-radius: 18px;
        border: 1px solid #ddd;
        padding: 30px;
        box-shadow: 0 4px 15px rgba(0,0,0,.07);
        display: flex;
        gap: 35px;
        align-items: flex-start;
    }

    /* ===== AUTO IZQUIERDA ===== */
    .car-box {
        width: 260px;
        padding: 18px;
        border-radius: 12px;
        border: 1px solid #e7e7e7;
        background: #fafafa;
    }

    #carSVG {
        width: 100%;
        height: auto;
        display: block;
    }

    .zone:hover { cursor: pointer; opacity: 0.6; }
    .zone.selected rect,
    .zone.selected circle {
        fill: rgba(255, 0, 0, 0.35);
    }
    .zone text {
        pointer-events: none;
        fill: #000;
        font-weight: bold;
    }

    /* ===== TABLA DERECHA ===== */
    .tabla-entrega {
        flex: 1;
    }

    .tabla-entrega h3 {
        font-weight: 900;
        margin-bottom: 15px;
        font-size: 20px;
    }

    table.entrega {
        width: 100%;
        border-collapse: collapse;
        font-size: 15px;
        background: white;
        border-radius: 15px;
        overflow: hidden;
    }

    table.entrega tr:nth-child(even) {
        background: #fafafa;
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

    /* ===== MODAL BONITO ===== */
    #modalDaño {
        display:none;
        position:fixed;
        inset:0;
        background:rgba(0,0,0,.55);
        justify-content:center;
        align-items:center;
        z-index:9999;
    }

    #modalDaño .box {
        background:white;
        width:320px;
        padding:22px;
        border-radius:14px;
        box-shadow:0 0 15px rgba(0,0,0,.15);
    }

    #modalDaño h4 {
        font-size:18px;
        font-weight:800;
        margin-bottom:10px;
    }

    #modalDaño textarea {
        width:100%;
        padding:10px;
        resize:none;
        background:#f7f7f7;
        border-radius:10px;
        border:1px solid #ddd;
    }

    .btn {
        width:100%;
        padding:10px;
        border-radius:10px;
        border:none;
        margin-top:10px;
        font-weight:600;
        font-size:15px;
    }

    .btn-save { background:#1976D2; color:white; }
    .btn-cancel { background:#aaa; color:white; }

</style>

<div class="checklist-card">

    <!-- ======================================================= -->
    <!--                      AUTO IZQUIERDA                      -->
    <!-- ======================================================= -->
    <div class="car-box">
        <svg id="carSVG" viewBox="0 0 300 780" xmlns="http://www.w3.org/2000/svg">

            <image
                href="{{ asset('img/diagrama-carro-danos3.png') }}"
                width="300"
                height="780"
                preserveAspectRatio="xMidYMid meet"
            />

            <!-- 🔥 ZONAS (igual que antes) -->
            <g class="zone" data-zone="1">
                <rect x="80" y="15" width="140" height="50" fill="transparent"/>
                <text x="150" y="45">1</text>
            </g>

            <g class="zone" data-zone="10">
                <rect x="70" y="330" width="160" height="120" fill="transparent"/>
                <text x="150" y="390">10</text>
            </g>

            <!-- agrega las demás zonas aquí -->

        </svg>
    </div>

    <!-- ======================================================= -->
    <!--             DERECHA: TABLA "EL CLIENTE SE LO LLEVA"     -->
    <!-- ======================================================= -->
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

<!-- ======================================================= -->
<!--                       MODAL                              -->
<!-- ======================================================= -->
<div id="modalDaño">
    <div class="box">
        <h4 id="tituloModal">Parte del vehículo</h4>

        <textarea id="comentarioDaño" rows="3"
                  placeholder="Describe el daño..."></textarea>

        <button class="btn btn-save" id="guardarDaño">Guardar</button>
        <button class="btn btn-cancel" id="cancelarDaño">Cancelar</button>
    </div>
</div>

<script>
    /* NOMBRES REALES POR CADA ZONA */
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
        10: "Techo trasero",
        11: "Costado izquierdo trasero",
        12: "Costado derecho trasero",
        13: "Defensa trasera",
        14: "Parte frontal superior",
        15: "Llanta delantera izquierda",
        16: "Llanta delantera derecha",
        17: "Llanta trasera izquierda",
        18: "Llanta trasera derecha",
    };

    const modal = document.getElementById("modalDaño");

    document.querySelectorAll('.zone').forEach(z => {
        z.addEventListener('click', () => {

            let zona = z.dataset.zone;

            if (z.classList.contains('selected')) {
                z.classList.remove('selected');
                return;
            }

            z.classList.add('selected');

            document.getElementById("tituloModal").textContent =
                nombresZonas[zona] || ("Zona " + zona);

            document.getElementById("comentarioDaño").value = "";

            modal.style.display = "flex";
        });
    });

    document.getElementById("guardarDaño").onclick = () => {
        modal.style.display = "none";
    };
    document.getElementById("cancelarDaño").onclick = () => {
        modal.style.display = "none";
    };
</script>
