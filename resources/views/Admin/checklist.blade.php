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
                <th>TIPO</th><td>{{ $tipo ?? '' }}</td>
                <th>MODELO</th><td>{{ $modelo ?? '' }}</td>
                <th>PLACAS</th><td>{{ $placas ?? '' }}</td>
                <th>COLOR</th><td>{{ $color ?? '' }}</td>
                <th>TRANSMISIÓN</th><td>{{ $transmision ?? '' }}</td>
            </tr>

            <tr>
                <th>CD. QUE ENTREGA</th><td>{{ $ciudadEntrega ?? '' }}</td>
                <th>CD. QUE RECIBE</th><td>{{ $ciudadRecibe ?? '' }}</td>
                <th>KILOMETRAJE SALIDA</th><td>{{ $kmSalida ?? '' }}</td>
                <th>KILOMETRAJE REGRESO</th><td>{{ $kmRegreso ?? '' }}</td>
                <th>PROTECCIÓN</th><td>{{ $proteccion ?? '' }}</td>
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

                <!-- pastilla negra ORIGINAL -->
                <div class="gas-pill">
                    <span>Nivel seleccionado:</span>
                    <strong id="gasSalidaTxt">—</strong>
                </div>

                <!-- GAUGE ORIGINAL RESTAURADO -->
                <div class="fuel-gauge">
                    <svg viewBox="0 0 200 120" class="fuel-svg">

                        <!-- arco coloreado ORIGINAL -->
                        <defs>
                            <linearGradient id="arcSalidaColor" x1="0%" y1="0%" x2="100%" y2="0%">
                                <stop offset="0%" stop-color="#dc2626"/>
                                <stop offset="30%" stop-color="#f97316"/>
                                <stop offset="50%" stop-color="#facc15"/>
                                <stop offset="75%" stop-color="#22c55e"/>
                                <stop offset="100%" stop-color="#d1d5db"/>
                            </linearGradient>
                        </defs>

                        <!-- arco base -->
                        <path d="M20 100 A80 80 0 0 1 180 100"
                              fill="none"
                              stroke="#e5e7eb"
                              stroke-width="22"
                              stroke-linecap="round"/>

                        <!-- arco de progreso -->
                        <path id="arcSalida"
                              d="M20 100 A80 80 0 0 1 180 100"
                              fill="none"
                              stroke="url(#arcSalidaColor)"
                              stroke-width="22"
                              stroke-linecap="round"
                              stroke-dasharray="283"
                              stroke-dashoffset="283"/>

                        <!-- aguja ORIGINAL -->
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

                <!-- selector nuevo -->
                <label class="fuel-label">Seleccionar nivel</label>
                <select id="selectGasSalida" class="fuel-select">
                    <option value="">—</option>
                    @foreach($niveles as $i => $n)
                        <option value="{{ $n }}" data-pct="{{ round(($i/(count($niveles)-1))*100) }}">{{ $n }}</option>
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

                <!-- GAUGE ORIGINAL RESTAURADO -->
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
                <select id="selectGasRecibe" class="fuel-select">
                    <option value="">—</option>
                    @foreach($niveles as $i => $n)
                        <option value="{{ $n }}" data-pct="{{ round(($i/(count($niveles)-1))*100) }}">{{ $n }}</option>
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
    <!--           RESTO DEL DOCUMENTO ORIGINAL       -->
    <!-- ============================================ -->
    <!-- (todo lo demás lo dejo exactamente igual, no lo toqué) -->

    <section class="paper-section">
        <p class="legal-text">
            He verificado que el vehículo lleva el equipo especial especificado.  
            Que los daños están marcados en imagen de auto y no soy responsable por daños  
            o robo parcial o total; salvo una negligencia.
        </p>

        <div class="accept-line">
            <span>Acepto:</span>
            <div class="line"></div>
            <span class="client-name"></span>
        </div>
    </section>


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
            <div class="line"></div>
            <span class="xbox">X</span>
        </div>
    </section>


    <section class="paper-section">
        <h3 class="sec-title">Comentario</h3>
        <div class="comment-box"></div>

        <h3 class="sec-title">Daños Interiores</h3>
        <div class="comment-box"></div>
    </section>


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
                <td></td>
                <td>________________________</td>
                <td>__________</td>
                <td>__________</td>
            </tr>
        </table>

        <h3 class="sec-title">Sólo personal de Viajero</h3>

        <table class="sign-table">
            <tr><th>Entregó</th><th>Firma</th><th>Fecha</th><th>Hora</th></tr>
            <tr><td></td><td></td><td></td><td></td></tr>
            <tr><th>Recibió</th><th>Firma</th><th>Fecha</th><th>Hora</th></tr>
            <tr><td></td><td></td><td></td><td></td></tr>
        </table>

    </section>

</div>
@endsection




@section('js-vistareservacionesAdmin')
<script>
document.addEventListener("DOMContentLoaded", () => {

    const maxLength = 283;

    function setupGauge(selectId, arcId, needleId, txtId) {

        const select = document.getElementById(selectId);
        const arc = document.getElementById(arcId);
        const needle = document.getElementById(needleId);
        const txt = document.getElementById(txtId);

        select.addEventListener("change", () => {

            const option = select.selectedOptions[0];
            const pct = option.dataset.pct ? parseFloat(option.dataset.pct) : 0;
            const val = option.value || "—";

            // actualizar texto
            txt.textContent = val;

            // arco
            const offset = maxLength - (maxLength * (pct / 100));
            arc.style.strokeDashoffset = offset;

            // ángulo calibrado (el ORIGINAL)
            const angle = -90 + (pct * 1.8);
            needle.style.transform = `rotate(${angle}deg)`;
        });
    }

    setupGauge("selectGasSalida", "arcSalida", "needleSalida", "gasSalidaTxt");
    setupGauge("selectGasRecibe", "arcRecibe", "needleRecibe", "gasRecibeTxt");

});
</script>
@endsection
