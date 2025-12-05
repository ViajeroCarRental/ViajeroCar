@extends('layouts.Ventas')

@section('Titulo', 'Check List – Inspección')

@section('css-vistareservacionesAdmin')
<link rel="stylesheet" href="{{ asset('css/checklist.css') }}">
@endsection

@section('contenidoreservacionesAdmin')

@php
    // Niveles de gasolina
    $niveles = [
        "0","1/16","2/16","3/16","1/4","5/16","6/16",
        "7/16","1/2","9/16","10/16","11/16",
        "3/4","13/16","14/16","15/16","1"
    ];
@endphp
<input type="hidden" id="idContrato" value="{{ $reservacion->id_reservacion }}">

<div class="checklist-container">

    <!-- ============================================ -->
    <!--            ENCABEZADO SUPERIOR               -->
    <!-- ============================================ -->
    <header class="cl-header">
        <div class="cl-logo">
            <img src="/img/Logotipo Fondo.jpg" alt="Logo Viajero">
        </div>

        <div class="cl-title-box">
            <h1>VIAJERO CAR RENTAL</h1>
            <h2>Hoja de Inspección / Check List</h2>

            <p class="office-info">
                OFICINA<br>
                Business Center INNERA Central Park, Armando Birlain Shaffler 2001 Torre2<br>
                76090 Santiago de Querétaro, Qro.<br>
                Centro Sur
            </p>
        </div>

        <div class="cl-ra-box">
            <span>No. Rental Agreement</span>
            <strong>{{ $reservacion->id_reservacion ?? '' }}</strong>
        </div>
    </header>



    <!-- ============================================ -->
<!--                DATOS DEL VEHÍCULO            -->
<!-- ============================================ -->
<section class="paper-section">
    <h3 class="sec-title">Datos del vehículo</h3>

    <table class="vehicle-table">
        <tr>
            <th>TIPO</th>
            <td>{{ $tipo ?? '—' }}</td>

            <th>MODELO</th>
            <td>{{ $modelo ?? '—' }}</td>

            <th>PLACAS</th>
            <td>{{ $placas ?? '—' }}</td>

            <th>COLOR</th>
            <td>{{ $color ?? '—' }}</td>

            <th>TRANSMISIÓN</th>
            <td>{{ $transmision ?? '—' }}</td>
        </tr>

        <tr>
            <th>CD. QUE ENTREGA</th>
            <td>{{ $ciudadEntrega ?? '—' }}</td>

            <th>CD. QUE RECIBE</th>
            <td>{{ $ciudadRecibe ?? '—' }}</td>

            <th>KILOMETRAJE SALIDA</th>
            <td>{{ $kmSalida ?? '—' }}</td>

            <th>KILOMETRAJE REGRESO</th>
            <td>{{ $kmRegreso ?? '—' }}</td>

            <th>PROTECCIÓN</th>
            <td>{{ $proteccion ?? '—' }}</td>
        </tr>
    </table>
</section>




    <!-- ====================================================== -->
<!--       GASOLINA – GAUGE ORIGINAL + DROPDOWN NUEVO       -->
<!-- ====================================================== -->

<section class="paper-section gas-wrap">
    <h3 class="sec-title center">Gasolina – Inspección</h3>

    <div class="fuel-grid">

        <!-- ======================= -->
        <!-- GASOLINA SALIDA         -->
        <!-- ======================= -->
        <div class="fuel-card">

            <h4 class="fuel-title">Gasolina – Salida</h4>

            <!-- pastilla -->
            <div class="gas-pill">
                <span>Nivel seleccionado:</span>
                <strong id="gasSalidaTxt">—</strong>
            </div>

            <!-- GAUGE -->
            <div class="fuel-gauge">
                <svg viewBox="0 0 200 120" class="fuel-svg">

                    <defs>
                        <linearGradient id="arcSalidaColor" x1="0%" y1="0%" x2="100%" y2="0%">
                            <stop offset="0%" stop-color="#dc2626"/>
                            <stop offset="30%" stop-color="#f97316"/>
                            <stop offset="50%" stop-color="#facc15"/>
                            <stop offset="75%" stop-color="#22c55e"/>
                            <stop offset="100%" stop-color="#d1d5db"/>
                        </linearGradient>
                    </defs>

                    <path d="M20 100 A80 80 0 0 1 180 100"
                          fill="none"
                          stroke="#e5e7eb"
                          stroke-width="22"
                          stroke-linecap="round"/>

                    <path id="arcSalida"
                          d="M20 100 A80 80 0 0 1 180 100"
                          fill="none"
                          stroke="url(#arcSalidaColor)"
                          stroke-width="22"
                          stroke-linecap="round"
                          stroke-dasharray="283"
                          stroke-dashoffset="283"/>

                    <line id="needleSalida"
                          x1="100" y1="100"
                          x2="100" y2="32"
                          stroke="#0f172a"
                          stroke-width="4"
                          stroke-linecap="round"
                          style="transform-origin:100px 100px; transform:rotate(-90deg); transition:.45s ease;" />

                    <circle cx="100" cy="100" r="7" fill="#0f172a"/>

                    <text x="26" y="110" class="gauge-label">E</text>
                    <text x="100" y="28" class="gauge-label">1/2</text>
                    <text x="174" y="110" class="gauge-label">F</text>
                </svg>
            </div>

            <!-- selector con valor inicial -->
            <label class="fuel-label">Seleccionar nivel</label>
            <select id="selectGasSalida"
        class="fuel-select"
        data-inicial="{{ $gasolinaSalida ?? '' }}">

                <option value="">—</option>
                @foreach($niveles as $i => $n)
                    <option value="{{ $n }}"
                            data-pct="{{ round(($i/(count($niveles)-1))*100) }}">
                        {{ $n }}
                    </option>
                @endforeach
            </select>
        </div>



        <!-- ======================= -->
        <!-- GASOLINA RECIBIDO       -->
        <!-- ======================= -->
        <div class="fuel-card">

            <h4 class="fuel-title">Gasolina – Recibido</h4>

            <div class="gas-pill">
                <span>Nivel seleccionado:</span>
                <strong id="gasRecibeTxt">—</strong>
            </div>

            <div class="fuel-gauge">
                <svg viewBox="0 0 200 120" class="fuel-svg">

                    <defs>
                        <linearGradient id="arcRecibeColor" x1="0%" y1="0%" x2="100%" y2="0%">
                            <stop offset="0%" stop-color="#dc2626"/>
                            <stop offset="30%" stop-color="#f97316"/>
                            <stop offset="50%" stop-color="#facc15"/>
                            <stop offset="75%" stop-color="#22c55e"/>
                            <stop offset="100%" stop-color="#d1d5db"/>
                        </linearGradient>
                    </defs>

                    <path d="M20 100 A80 80 0 0 1 180 100"
                          fill="none"
                          stroke="#e5e7eb"
                          stroke-width="22"
                          stroke-linecap="round"/>

                    <path id="arcRecibe"
                          d="M20 100 A80 80 0 0 1 180 100"
                          fill="none"
                          stroke="url(#arcRecibeColor)"
                          stroke-width="22"
                          stroke-linecap="round"
                          stroke-dasharray="283"
                          stroke-dashoffset="283"/>

                    <line id="needleRecibe"
                          x1="100" y1="100"
                          x2="100" y2="32"
                          stroke="#0f172a"
                          stroke-width="4"
                          stroke-linecap="round"
                          style="transform-origin:100px 100px; transform:rotate(-90deg); transition:.45s ease;" />

                    <circle cx="100" cy="100" r="7" fill="#0f172a"/>

                    <text x="26" y="110" class="gauge-label">E</text>
                    <text x="100" y="28" class="gauge-label">1/2</text>
                    <text x="174" y="110" class="gauge-label">F</text>
                </svg>
            </div>

            <label class="fuel-label">Seleccionar nivel</label>
            <select id="selectGasRecibe"
                    class="fuel-select"
                    data-inicial="{{ $gasolinaRegreso ?? '' }}">
                <option value="">—</option>
                @foreach($niveles as $i => $n)
                    <option value="{{ $n }}"
                            data-pct="{{ round(($i/(count($niveles)-1))*100) }}">
                        {{ $n }}
                    </option>
                @endforeach
            </select>
        </div>

    </div>
</section>



    <!-- ============================================ -->
    <!--             DIAGRAMA DE VEHÍCULO             -->
    <!-- ============================================ -->
    <section class="paper-section">
        <h3 class="sec-title center">Auto</h3>

        <div class="diagram-card">

            @include('components.diagram-car')
        </div>
    </section>



    <!-- ============================================ -->
<!--           SECCIÓN DE ACEPTACIÓN              -->
<!-- ============================================ -->

<section class="paper-section">
    <p class="legal-text">
        He verificado que el vehículo lleva el equipo especial especificado.
        Que los daños están marcados en imagen de auto y no soy responsable por daños
        o robo parcial o total; salvo una negligencia.
    </p>

    <div class="accept-line">
        <span>Acepto:</span>
        <input type="text" class="input-line"
               placeholder="Nombre del cliente"
               data-field="acepto_cliente_nombre">
    </div>
</section>

<!-- ============================================ -->
<!--           POSIBLES CARGOS                    -->
<!-- ============================================ -->

<section class="paper-section">
    <h3 class="sec-title">Información de posibles cargos</h3>

    <ol class="rules-list">
        <li>No se permite Fumar dentro de la unidad.</li>
        <li>No se permite manchar interior/exterior con sustancias químicas u orgánicas.</li>
        <li>No se permite el uso de huachicol ni combustibles diferentes a gasolina Premium.</li>
        <li>No se permite el cambio de piezas originales con las que se renta la unidad.</li>
    </ol>

    <div class="accept-line">
        <span>Acepto</span>
        <span class="xbox">X</span>
    </div>
</section>

<!-- ============================================ -->
<!--           COMENTARIOS & DAÑOS                -->
<!-- ============================================ -->

<section class="paper-section">
    <h3 class="sec-title">Comentario</h3>
    <textarea class="comment-input" data-field="comentario_cliente"
              placeholder="Escribe comentarios aquí..."></textarea>

    <h3 class="sec-title">Daños Interiores</h3>
    <textarea class="comment-input" data-field="danos_interiores"
              placeholder="Describe los daños interiores..."></textarea>
</section>

<!-- ============================================ -->
<!--           TABLA FIRMAS                       -->
<!-- ============================================ -->

<section class="paper-section">

    <p class="legal-text">
        Por el presente acuse, recibo este vehículo en las condiciones descritas anteriormente
        y me comprometo a notificar a un representante de Viajero Car Rental de cualquier
        discrepancia antes de salir de los locales de Viajero Car Rental.
    </p>

    <table class="sign-table">
        <tr>
            <th>Nombre del Cliente</th>
            <th>Firma del Cliente</th>
            <th>Fecha</th>
            <th>Hora</th>
        </tr>
        <tr>
            <td>
                <input type="text" class="input-line"
                       data-field="firma_cliente_nombre"
                       placeholder="Nombre del cliente">
            </td>

            <td>
                @if($contrato->firma_cliente)
                    <img src="{{ $contrato->firma_cliente }}" class="firma-img">
                @else
                    <button class="btn-open-sign" data-type="cliente">Firmar Cliente</button>
                @endif
            </td>

            <td>
                <input type="date" class="input-line"
                       data-field="firma_cliente_fecha">
            </td>

            <td>
                <input type="time" class="input-line"
                       data-field="firma_cliente_hora">
            </td>
        </tr>
    </table>

    <h3 class="sec-title">Sólo personal de Viajero</h3>

    <table class="sign-table">
        <tr>
            <th>Entregó</th>
            <th>Firma</th>
            <th>Fecha</th>
            <th>Hora</th>
        </tr>
        <tr>
            <td>
                <input type="text" class="input-line"
                       data-field="entrego_nombre"
                       placeholder="Nombre del agente que entrega">
            </td>

            <td>
                @if($contrato->firma_arrendador)
                    <img src="{{ $contrato->firma_arrendador }}" class="firma-img">
                @else
                    <button class="btn-open-sign" data-type="arrendador">Firmar Agente</button>
                @endif
            </td>

            <td><input type="date" class="input-line" data-field="entrego_fecha"></td>
            <td><input type="time" class="input-line" data-field="entrego_hora"></td>
        </tr>

        <tr>
            <th>Recibió</th>
            <th>Firma</th>
            <th>Fecha</th>
            <th>Hora</th>
        </tr>
        <tr>
            <td>
                <input type="text" class="input-line"
                       data-field="recibio_nombre"
                       placeholder="Nombre del agente que recibe">
            </td>

            <td><button class="btn-open-sign" data-type="arrendador">Firmar</button></td>

            <td><input type="date" class="input-line" data-field="recibio_fecha"></td>
            <td><input type="time" class="input-line" data-field="recibio_hora"></td>
        </tr>
    </table>

</section>

<!-- =======================================================
     MODAL ÚNICO DE FIRMAS (CLIENTE / AGENTE)
======================================================= -->
<div id="modalFirma" class="modal-firma">
    <div class="modal-content">

        <h3 id="tituloModalFirma">Firma</h3>

        <canvas id="padFirma" width="400" height="180"></canvas>

        <div class="modal-buttons">
            <button id="btnClearFirma" class="btn-clear">Limpiar</button>
            <button id="btnGuardarFirma" class="btn-save">Guardar</button>
            <button id="btnCerrarModal" class="btn-close">Cerrar</button>
        </div>

    </div>
</div>

<style>
.modal-firma{
    position: fixed;
    top:0; left:0;
    width:100%; height:100%;
    background:rgba(0,0,0,.6);
    display:none;
    justify-content:center;
    align-items:center;
    z-index:9999;
}

.modal-content{
    background:#fff;
    padding:25px;
    border-radius:12px;
    width:460px;
    text-align:center;
}

canvas{
    border:1px solid #222;
    border-radius:6px;
    background:#fafafa;
    margin-bottom:15px;
}

.modal-buttons button{
    padding:10px 18px;
    margin:5px;
    border:none;
    border-radius:8px;
    cursor:pointer;
}

.btn-clear{ background:#ccc; }
.btn-save{ background:#28a745; color:white; }
.btn-close{ background:#c82333; color:white; }
</style>

</div>
@endsection




@section('js-vistareservacionesAdmin')
<script>
document.addEventListener("DOMContentLoaded", () => {

    /* ==========================================================
       OBTENER ID DEL CONTRATO (SIRVE PARA GUARDAR FIRMAS Y CAMPOS)
    ============================================================= */
    const contratoApp = document.getElementById("idContrato");
    const CONTRATO_ID = contratoApp.value;

    /* ==========================================================
       CHECKLIST ID LARAVEL → JS
    ============================================================= */
    const CHECKLIST_ID = {{ $id }};
    const maxLength = 283;

    /* ==========================================================
       FUNCIÓN PARA CONFIGURAR GAUGE
    ============================================================= */
    function setupGauge(selectId, arcId, needleId, txtId) {

        const select = document.getElementById(selectId);
        const arc = document.getElementById(arcId);
        const needle = document.getElementById(needleId);
        const txt = document.getElementById(txtId);

        function updateGauge() {
            const option = select.selectedOptions[0];
            const pct = option.dataset.pct ? parseFloat(option.dataset.pct) : 0;
            const val = option.value || "—";

            txt.textContent = val;

            const offset = maxLength - (maxLength * (pct / 100));
            arc.style.strokeDashoffset = offset;

            const angle = -90 + (pct * 1.8);
            needle.style.transform = `rotate(${angle}deg)`;
        }

        select.addEventListener("change", updateGauge);

        const inicial = select.dataset.inicial;
        if (inicial) {
            [...select.options].forEach(op => {
                if (op.value === inicial) op.selected = true;
            });
            updateGauge();
        }
    }

    /* Inicializar gauges */
    setupGauge("selectGasSalida", "arcSalida", "needleSalida", "gasSalidaTxt");
    setupGauge("selectGasRecibe", "arcRecibe", "needleRecibe", "gasRecibeTxt");

    /* ==========================================================
       GUARDAR GASOLINA DE REGRESO
    ============================================================= */
    document.getElementById("selectGasRecibe").addEventListener("change", async (e) => {

        const nivel = e.target.value;

        const resp = await fetch(`/checklist/${CHECKLIST_ID}/guardar-gasolina`, {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
                "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').content
            },
            body: JSON.stringify({
                gasolina_regreso: nivel
            })
        });

        const data = await resp.json();
        alert(data.msg);
    });

    /* ==========================================================
       MODAL ÚNICO DE FIRMAS
    ============================================================= */
    const modal = document.getElementById("modalFirma");
    const canvas = document.getElementById("padFirma");
    const btnClear = document.getElementById("btnClearFirma");
    const btnGuardar = document.getElementById("btnGuardarFirma");
    const btnCerrar = document.getElementById("btnCerrarModal");
    const tituloModal = document.getElementById("tituloModalFirma");

    let tipoFirma = null;
    let signaturePad = new SignaturePad(canvas);

    /* ABRIR MODAL */
    document.querySelectorAll(".btn-open-sign").forEach(btn => {
        btn.addEventListener("click", () => {

            tipoFirma = btn.dataset.type;

            tituloModal.textContent =
                tipoFirma === "cliente"
                    ? "Firma del Cliente"
                    : "Firma del Arrendador";

            signaturePad.clear();
            modal.style.display = "flex";
        });
    });

    /* LIMPIAR */
    btnClear.onclick = () => signaturePad.clear();

    /* CERRAR */
    btnCerrar.onclick = () => {
        modal.style.display = "none";
    };

    /* GUARDAR FIRMA */
    btnGuardar.onclick = async () => {
        if (signaturePad.isEmpty()) {
            alert("La firma está vacía");
            return;
        }

        const dataURL = signaturePad.toDataURL("image/png");

        const url = tipoFirma === "cliente"
            ? "/contrato/firma-cliente"
            : "/contrato/firma-arrendador";

        await fetch(url, {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
                "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').content
            },
            body: JSON.stringify({
                id_contrato: CONTRATO_ID,
                firma: dataURL
            })
        });

        alert("Firma guardada correctamente");
        modal.style.display = "none";
        location.reload();
    };

    /* ==========================================================
       GUARDAR CAMPOS TEXTO AUTOMÁTICAMENTE
    ============================================================= */
    document.querySelectorAll("[data-field]").forEach(input => {
        input.addEventListener("change", async () => {

            await fetch("/contrato/guardar-dato", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    "X-CSRF-TOKEN": document.querySelector('meta[name=\"csrf-token\"]').content
                },
                body: JSON.stringify({
                    id_contrato: CONTRATO_ID,
                    campo: input.dataset.field,
                    valor: input.value
                })
            });

            console.log("Guardado:", input.dataset.field, input.value);
        });
    });

});
</script>
<!-- Librería oficial SignaturePad -->
    <script src="https://cdn.jsdelivr.net/npm/signature_pad@2.3.2/dist/signature_pad.min.js"></script>

@endsection
