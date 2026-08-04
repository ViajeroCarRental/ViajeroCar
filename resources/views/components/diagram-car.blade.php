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

    /* ===== MODAL DE DAÑOS (estilizado, tonos rojos) ===== */
    #modalDaño {
        display: none;
        position: fixed !important;
        inset: 0 !important;
        width: 100vw;
        height: 100vh;
        padding: 20px;
        background: rgba(60, 8, 15, .55);
        backdrop-filter: blur(3px);
        z-index: 999999;

        align-items: flex-start;   /* la caja se ancla arriba; el top lo pone el JS */
        justify-content: center;

        box-sizing: border-box;
    }

    #modalDaño .box {
        position: relative;
        width: 100%;
        max-width: 380px;
        padding: 0;                /* el padding ahora va en head/body */
        margin: 0;

        background: #ffffff;
        border-radius: 20px;
        overflow: hidden;
        box-shadow: 0 24px 60px rgba(150, 20, 30, .35);
        animation: invPop .25s cubic-bezier(.34,1.56,.64,1);

        box-sizing: border-box;
    }

    /* Cabecera roja con degradado */
    #modalDaño .dano-head {
        background: linear-gradient(135deg, #ff4d6a 0%, #c9184a 100%);
        padding: 22px 24px 20px;
        text-align: center;
        color: #fff;
    }

    #modalDaño .dano-icon {
        width: 56px;
        height: 56px;
        margin: 0 auto 10px;
        border-radius: 50%;
        background: rgba(255,255,255,.2);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 28px;
    }

    #modalDaño h4 {
        font-size: 19px;
        font-weight: 900;
        margin: 0;
        letter-spacing: .3px;
        color: #fff;
    }

    /* Cuerpo del modal */
    #modalDaño .dano-body {
        padding: 20px 22px 22px;
    }

    #modalDaño textarea {
        width: 100%;
        padding: 12px;
        resize: none;
        min-height: 90px;
        background: #fdf3f5;
        border-radius: 12px;
        border: 1.5px solid #f6d0d9;
        box-sizing: border-box;
        font-size: 15px;
        transition: border-color .15s ease, box-shadow .15s ease;
    }

    #modalDaño textarea:focus {
        outline: none;
        border-color: #ff4d6a;
        box-shadow: 0 0 0 3px rgba(255,77,106,.15);
    }

    /* Input de archivo estilizado como botón suave */
    #modalDaño input[type="file"] {
        width: 100%;
        margin-top: 12px;
        font-size: 14px;
        color: #c9184a;
        box-sizing: border-box;
    }

    #modalDaño input[type="file"]::file-selector-button {
        padding: 9px 14px;
        margin-right: 10px;
        border: none;
        border-radius: 10px;
        background: #fdeef1;
        color: #c9184a;
        font-weight: 700;
        cursor: pointer;
        transition: background .15s ease;
    }

    #modalDaño input[type="file"]::file-selector-button:hover {
        background: #fbdde3;
    }

    .btn {
        width: 100%;
        padding: 13px;
        border-radius: 12px;
        border: none;
        margin-top: 12px;
        font-weight: 800;
        font-size: 15px;
        cursor: pointer;
        transition: transform .12s ease, box-shadow .15s ease, opacity .15s;
    }

    .btn:active { transform: scale(.97); }

    /* Botón guardar en rojo (degradado) */
    .btn-save {
        background: linear-gradient(135deg, #ff4d6a, #c9184a);
        color: #fff;
        box-shadow: 0 6px 16px rgba(201,24,74,.35);
    }
    .btn-save:hover { box-shadow: 0 8px 22px rgba(201,24,74,.45); }

    /* Botón cancelar suave */
    .btn-cancel {
        background: #fdeef1;
        color: #c9184a;
    }
    .btn-cancel:hover { background: #fbdde3; }

    /* ===================================================== */
    /*   MODAL BONITO DE INVENTARIO (tonos rojos)          */
    /* ===================================================== */
    #modalInventario {
        display: none;
        position: fixed;
        inset: 0;
        z-index: 9999999;
        background: rgba(60, 8, 15, .55);
        backdrop-filter: blur(3px);
        align-items: flex-start;      /* la caja se ancla arriba; el top lo pone el JS */
        justify-content: center;
        padding: 20px;
        box-sizing: border-box;
    }

    #modalInventario.show {
        display: flex;
        animation: invFade .2s ease;
    }

    @keyframes invFade { from { opacity: 0 } to { opacity: 1 } }

    #modalInventario .inv-box {
        width: 100%;
        max-width: 380px;
        background: #fff;
        border-radius: 20px;
        overflow: hidden;
        box-shadow: 0 24px 60px rgba(150, 20, 30, .35);
        animation: invPop .25s cubic-bezier(.34,1.56,.64,1);
    }

    @keyframes invPop {
        from { transform: scale(.9) translateY(10px); opacity: 0 }
        to   { transform: scale(1) translateY(0); opacity: 1 }
    }

    /* Cabecera roja con degradado */
    #modalInventario .inv-head {
        background: linear-gradient(135deg, #ff4d6a 0%, #c9184a 100%);
        padding: 26px 24px 22px;
        text-align: center;
        color: #fff;
        position: relative;
    }

    #modalInventario .inv-icon {
        width: 60px;
        height: 60px;
        margin: 0 auto 12px;
        border-radius: 50%;
        background: rgba(255,255,255,.2);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 30px;
        font-weight: 900;
    }

    #modalInventario .inv-head h4 {
        margin: 0;
        font-size: 20px;
        font-weight: 900;
        letter-spacing: .3px;
    }

    #modalInventario .inv-body {
        padding: 22px 24px 24px;
        text-align: center;
    }

    #modalInventario .inv-msg {
        color: #444;
        font-size: 15px;
        line-height: 1.5;
        margin: 0 0 20px;
    }

    #modalInventario .inv-actions {
        display: flex;
        gap: 10px;
    }

    #modalInventario .inv-btn {
        flex: 1;
        padding: 13px;
        border: none;
        border-radius: 12px;
        font-weight: 800;
        font-size: 15px;
        cursor: pointer;
        transition: transform .12s ease, box-shadow .15s ease, opacity .15s;
    }

    #modalInventario .inv-btn:active { transform: scale(.96) }

    #modalInventario .inv-btn-primary {
        background: linear-gradient(135deg, #ff4d6a, #c9184a);
        color: #fff;
        box-shadow: 0 6px 16px rgba(201,24,74,.35);
    }
    #modalInventario .inv-btn-primary:hover { box-shadow: 0 8px 22px rgba(201,24,74,.45) }

    #modalInventario .inv-btn-ghost {
        background: #fdeef1;
        color: #c9184a;
    }
    #modalInventario .inv-btn-ghost:hover { background: #fbdde3 }

    /* Estado de error: rojo más oscuro/sobrio */
    #modalInventario.inv-error .inv-head {
        background: linear-gradient(135deg, #e5383b 0%, #8b0000 100%);
    }

    /* ===================================================== */
    /*   RESPONSIVE: SECCIÓN AUTO EN MÓVIL                  */
    /* ===================================================== */
    @media (max-width: 760px){
        /* El diagrama y la tabla dejan de ir lado a lado y se apilan */
        .checklist-card{
            flex-direction: column;
            gap: 20px;
            padding: 16px;
            align-items: stretch;
        }

        /* El diagrama del auto ocupa el ancho y se centra, sin ancho fijo */
        .car-box{
            width: 100%;
            max-width: 320px;
            margin: 0 auto;
        }

        /* La tabla ocupa todo el ancho debajo del diagrama */
        .tabla-entrega{
            width: 100%;
            margin-top: 0;
        }

        .tabla-entrega h3{
            font-size: 18px;
            margin-bottom: 10px;
        }

        /* ---- TABLA "EL CLIENTE SE LO LLEVA" EN MÓVIL ----
           La tabla tiene 4 columnas: item | check | item | check.
           En móvil las reacomodamos a 2 columnas (texto | check),
           de modo que cada item quede con su checkbox en su propia
           línea y el texto tenga espacio (ya no se parte letra a letra). */
        table.entrega{
            display: block;
            width: 100%;
            font-size: 14px;
        }

        table.entrega tbody{
            display: block;
            width: 100%;
        }

        /* Cada fila = grid de 2 columnas; los 4 td se acomodan en 2 filas:
           td1(texto) td2(check) / td3(texto) td4(check) */
        table.entrega tr{
            display: grid;
            grid-template-columns: 1fr auto;
            align-items: center;
            width: 100%;
        }

        table.entrega td{
            padding: 12px 12px;
            border-bottom: 1px solid #eee;
            /* Texto normal: NO romper palabras a la fuerza */
            word-break: normal;
            overflow-wrap: normal;
            white-space: normal;
        }

        /* Columnas de texto (1 y 3) alineadas a la izquierda */
        table.entrega td:nth-child(1),
        table.entrega td:nth-child(3){
            text-align: left;
            font-weight: 600;
        }

        /* Columnas de checkbox (2 y 4) alineadas a la derecha */
        table.entrega td:nth-child(2),
        table.entrega td:nth-child(4){
            text-align: right;
            justify-self: end;
        }

        /* Los checkbox con buen tamaño para tocar */
        table.entrega td input[type="checkbox"]{
            width: 20px;
            height: 20px;
        }

        /* El rayado alterno por fila deja de tener sentido con grid;
           lo quitamos para que no confunda visualmente */
        table.entrega tr:nth-child(even){
            background: transparent;
        }
    }

    @media (max-width: 480px){
        .checklist-card{
            padding: 12px;
        }

        table.entrega{
            font-size: 13px;
        }

        table.entrega td{
            padding: 10px 10px;
        }
    }
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

    <button id="guardarInventario" class="btn btn-save"
            style="margin-top:20px; background:#2e9e4f; box-shadow:0 6px 16px rgba(46,158,79,.3);">
        Guardar inventario
    </button>
</div>


</div>

{{-- ================== MODAL DE DAÑOS (estilizado) ================== --}}
<div id="modalDaño">
    <div class="box">
        <div class="dano-head">
            <div class="dano-icon">
                <svg viewBox="0 0 24 24" width="30" height="30" fill="none"
                     stroke="#fff" stroke-width="2"
                     stroke-linecap="round" stroke-linejoin="round">
                    <path d="M14.7 6.3a1 1 0 0 0 0 1.4l1.6 1.6a1 1 0 0 0 1.4 0l3.77-3.77a6 6 0 0 1-7.94 7.94l-6.91 6.91a2.12 2.12 0 0 1-3-3l6.91-6.91a6 6 0 0 1 7.94-7.94l-3.76 3.76z"/>
                </svg>
            </div>
            <h4 id="tituloModal">Zona</h4>
        </div>

        <div class="dano-body">
            <textarea id="comentarioDaño"
                      placeholder="Describe el daño o comentario..."></textarea>

            {{-- Foto del daño --}}
            <input type="file"
                   id="fotoDaño"
                   accept="image/*"
                   capture="environment">

            {{-- Vista previa --}}
            <div id="previewFoto" style="margin-top:10px;"></div>

            <button type="button" id="guardarDaño" class="btn btn-save">Guardar</button>
            <button type="button" id="cancelarDaño" class="btn btn-cancel">Cancelar</button>
        </div>
    </div>
</div>

{{-- ================== MODAL INVENTARIO (bonito, tonos rojos) ================== --}}
<div id="modalInventario">
    <div class="inv-box">
        <div class="inv-head">
            <div class="inv-icon" id="invIcon">
                <svg viewBox="0 0 24 24" width="32" height="32" fill="none"
                     stroke="#fff" stroke-width="2.5"
                     stroke-linecap="round" stroke-linejoin="round">
                    <polyline points="20 6 9 17 4 12"/>
                </svg>
            </div>
            <h4 id="invTitulo">Inventario guardado</h4>
        </div>
        <div class="inv-body">
            <p class="inv-msg" id="invMensaje">Se guardó correctamente.</p>
            <div class="inv-actions" id="invActions">
                <button type="button" class="inv-btn inv-btn-primary" id="invAceptar">Aceptar</button>
            </div>
        </div>
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
       MODAL BONITO DE INVENTARIO (reemplaza los alerts)
    ===================================================== */

    const modalInv      = document.getElementById("modalInventario");
    const invIcon       = document.getElementById("invIcon");
    const invTitulo     = document.getElementById("invTitulo");
    const invMensaje    = document.getElementById("invMensaje");
    const invAceptarBtn = document.getElementById("invAceptar");

    // SVG de check (éxito) y de alerta (error), en blanco
    const svgCheck = `
        <svg viewBox="0 0 24 24" width="32" height="32" fill="none"
             stroke="#fff" stroke-width="2.5"
             stroke-linecap="round" stroke-linejoin="round">
            <polyline points="20 6 9 17 4 12"/>
        </svg>`;

    const svgAlerta = `
        <svg viewBox="0 0 24 24" width="32" height="32" fill="none"
             stroke="#fff" stroke-width="2.5"
             stroke-linecap="round" stroke-linejoin="round">
            <line x1="12" y1="8" x2="12" y2="13"/>
            <line x1="12" y1="17" x2="12.01" y2="17"/>
            <circle cx="12" cy="12" r="10"/>
        </svg>`;

    function mostrarInventarioModal({ tipo = "success", titulo, mensaje } = {}) {
        modalInv.classList.toggle("inv-error", tipo === "error");
        invIcon.innerHTML      = tipo === "error" ? svgAlerta : svgCheck;
        invTitulo.textContent  = titulo  || (tipo === "error" ? "Algo salió mal" : "Listo");
        invMensaje.textContent = mensaje || "";

        modalInv.classList.add("show");

        // Ubicar la caja justo sobre el botón de guardar
        posicionarInventarioModal();
    }

    /* Coloca la caja del modal de inventario centrada sobre la tarjeta
       del auto (.checklist-card), sin salirse de la pantalla. Se usa
       position: fixed (viewport) + margin-top. */
    function posicionarInventarioModal() {
        const box = modalInv.querySelector(".inv-box");
        if (!box) return;

        // Referencia: la tarjeta completa del auto
        const referencia = document.querySelector(".checklist-card");

        const margen = 16;
        const vpH = window.innerHeight;
        const boxH = box.offsetHeight || 0;

        let top;
        if (referencia &&
            typeof referencia.getBoundingClientRect === "function") {
            const r = referencia.getBoundingClientRect();
            // Centrar la caja verticalmente respecto a la tarjeta del auto
            top = r.top + (r.height / 2) - (boxH / 2);
        } else {
            // Sin referencia: centrar en el viewport
            top = (vpH - boxH) / 2;
        }

        // Limitar para que no se salga por arriba ni por abajo
        const topMax = vpH - boxH - margen;
        if (top > topMax) top = topMax;
        if (top < margen) top = margen;

        box.style.marginTop = `${top}px`;
        box.style.marginBottom = `${margen}px`;
    }

    function cerrarInventarioModal() {
        modalInv.classList.remove("show");
    }

    if (invAceptarBtn) {
        invAceptarBtn.addEventListener("click", cerrarInventarioModal);
    }

    if (modalInv) {
        modalInv.addEventListener("click", event => {
            if (event.target === modalInv) cerrarInventarioModal();
        });
    }

    /* =====================================================
       FUNCIONES DEL MODAL
    ===================================================== */

    function abrirModal(zona, trigger) {
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

        /* Bloquear el scroll del fondo SIN mover el body
           (mover el body con position:fixed descolocaba el modal). */
        posicionScrollModal = window.scrollY;
        document.body.style.overflow = "hidden";

        modal.style.display = "flex";

        /* Posicionar el modal cerca del botón/punto que lo abrió */
        posicionarModalDano(trigger);

        setTimeout(() => {
            comentarioInput.focus({
                preventScroll: true
            });
        }, 100);
    }

    /* Coloca la caja del modal a la altura del botón que lo disparó,
       sin salirse de la pantalla. */
    function posicionarModalDano(trigger) {
        const box = modal.querySelector(".box");
        if (!box) return;

        // Alinear la caja desde arriba y controlar posición con margin-top
        modal.style.alignItems = "flex-start";
        modal.style.justifyContent = "center";

        const margen = 16;
        const vpH = window.innerHeight;
        const boxH = box.offsetHeight || 0;

        let top;
        if (trigger && typeof trigger.getBoundingClientRect === "function") {
            const r = trigger.getBoundingClientRect();
            // Centrar verticalmente respecto al botón/punto
            top = r.top + (r.height / 2) - (boxH / 2);
        } else {
            // Sin referencia: centrar en el viewport
            top = (vpH - boxH) / 2;
        }

        // Limitar para que no se salga por arriba ni por abajo
        const topMax = vpH - boxH - margen;
        if (top > topMax) top = topMax;
        if (top < margen) top = margen;

        box.style.marginTop = `${top}px`;
        box.style.marginBottom = `${margen}px`;
    }

    function cerrarModal() {
        modal.style.display = "none";

        document.body.style.overflow = "";

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
            abrirModal(punto.dataset.zone, punto);
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

        // También cerrar el modal de inventario con Escape
        if (
            event.key === "Escape" &&
            modalInv &&
            modalInv.classList.contains("show")
        ) {
            cerrarInventarioModal();
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
                mostrarInventarioModal({
                    tipo: "error",
                    titulo: "No se pudo guardar",
                    mensaje: data.msg || "Ocurrió un error al guardar el inventario."
                });
                return;
            }

            mostrarInventarioModal({
                tipo: "success",
                titulo: "Inventario guardado",
                mensaje: data.msg || "Se guardó correctamente."
            });

        } catch (error) {
            console.error(error);
            mostrarInventarioModal({
                tipo: "error",
                titulo: "Error de conexión",
                mensaje: "No se pudo conectar con el servidor. Intenta de nuevo."
            });

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
