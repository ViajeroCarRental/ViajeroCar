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
<input type="hidden" id="idContrato" value="{{ $contrato->id_contrato }}">

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
            <strong>{{ $contrato->numero_contrato ?? $contrato->id_contrato ?? '' }}</strong>
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

            <td>
    <span id="kmRegresoText"
          style="cursor:pointer; text-decoration:underline;">
        {{ $kmRegreso ?? '—' }}
    </span>

    <input type="number"
           id="kmRegresoInput"
           value="{{ $kmRegreso ?? '' }}"
           style="display:none; width:100px;"
           min="0">

    <button id="btnGuardarKm"
            style="display:none;"
            class="btn btn-sm btn-primary">
        Guardar
    </button>
</td>


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
<!--           IMÁGENES GENERALES                 -->
<!-- ============================================ -->
<section class="paper-section">
  <h3 class="sec-title center">Imágenes generales del vehículo</h3>

  <div class="photo-grid">

    <!-- ================== SALIDA ================== -->
    <div class="photo-column">
      <h4 class="photo-column-title">SALIDA</h4>

      <!-- 1. FRENTE (1 sola foto) -->
      <div class="photo-slot">
        <div class="photo-slot-label">1. FRENTE</div>
        <div class="photo-uploader" data-name="frenteSalida" data-max-files="1">
          <span class="photo-uploader-msg">Toca para tomar foto o elegir de la galería</span>
          <input
              type="file"
              name="frente_salida"
              accept="image/*"
              capture="environment">
        </div>
        <div class="photo-preview" id="prev-frenteSalida"></div>
      </div>

      <!-- 2. PARABRISAS (1 sola foto) -->
      <div class="photo-slot">
        <div class="photo-slot-label">2. PARABRISAS</div>
        <div class="photo-uploader" data-name="parabrisasSalida" data-max-files="1">
          <span class="photo-uploader-msg">Toca para tomar foto o elegir de la galería</span>
          <input
              type="file"
              name="parabrisas_salida"
              accept="image/*"
              capture="environment">
        </div>
        <div class="photo-preview" id="prev-parabrisasSalida"></div>
      </div>

      <!-- 3. LADO CONDUCTOR (1 sola foto) -->
      <div class="photo-slot">
        <div class="photo-slot-label">3. LADO CONDUCTOR</div>
        <div class="photo-uploader" data-name="ladoConductorSalida" data-max-files="1">
          <span class="photo-uploader-msg">Toca para tomar foto o elegir de la galería</span>
          <input
              type="file"
              name="lado_conductor_salida"
              accept="image/*"
              capture="environment">
        </div>
        <div class="photo-preview" id="prev-ladoConductorSalida"></div>
      </div>

      <!-- 4. LADO PASAJERO (1 sola foto) -->
      <div class="photo-slot">
        <div class="photo-slot-label">4. LADO PASAJERO</div>
        <div class="photo-uploader" data-name="ladoPasajeroSalida" data-max-files="1">
          <span class="photo-uploader-msg">Toca para tomar foto o elegir de la galería</span>
          <input
              type="file"
              name="lado_pasajero_salida"
              accept="image/*"
              capture="environment">
        </div>
        <div class="photo-preview" id="prev-ladoPasajeroSalida"></div>
      </div>

      <!-- 5. ATRÁS (1 sola foto) -->
      <div class="photo-slot">
        <div class="photo-slot-label">5. ATRÁS</div>
        <div class="photo-uploader" data-name="atrasSalida" data-max-files="1">
          <span class="photo-uploader-msg">Toca para tomar foto o elegir de la galería</span>
          <input
              type="file"
              name="atras_salida"
              accept="image/*"
              capture="environment">
        </div>
        <div class="photo-preview" id="prev-atrasSalida"></div>
      </div>

      <!-- 6. INTERIORES (MÁX 8 fotos) -->
      <div class="photo-slot">
        <div class="photo-slot-label">6. INTERIORES <span class="photo-hint">(máx. 8 fotos)</span></div>
        <div class="photo-uploader" data-name="interioresSalida" data-max-files="8">
          <span class="photo-uploader-msg">Toca para tomar fotos o elegir de la galería</span>
          <input
              type="file"
              name="interiores_salida[]"
              accept="image/*"
              capture="environment"
              multiple>
        </div>
        <div class="photo-preview" id="prev-interioresSalida"></div>
      </div>
    </div>

    <!-- ================== REGRESO ================== -->
    <div class="photo-column">
      <h4 class="photo-column-title">REGRESO</h4>

      <!-- 1. FRENTE (1 sola foto) -->
      <div class="photo-slot">
        <div class="photo-slot-label">1. FRENTE</div>
        <div class="photo-uploader" data-name="frenteRegreso" data-max-files="1">
          <span class="photo-uploader-msg">Toca para tomar foto o elegir de la galería</span>
          <input
              type="file"
              name="frente_regreso"
              accept="image/*"
              capture="environment">
        </div>
        <div class="photo-preview" id="prev-frenteRegreso"></div>
      </div>

      <!-- 2. PARABRISAS (1 sola foto) -->
      <div class="photo-slot">
        <div class="photo-slot-label">2. PARABRISAS</div>
        <div class="photo-uploader" data-name="parabrisasRegreso" data-max-files="1">
          <span class="photo-uploader-msg">Toca para tomar foto o elegir de la galería</span>
          <input
              type="file"
              name="parabrisas_regreso"
              accept="image/*"
              capture="environment">
        </div>
        <div class="photo-preview" id="prev-parabrisasRegreso"></div>
      </div>

      <!-- 3. LADO CONDUCTOR (1 sola foto) -->
      <div class="photo-slot">
        <div class="photo-slot-label">3. LADO CONDUCTOR</div>
        <div class="photo-uploader" data-name="ladoConductorRegreso" data-max-files="1">
          <span class="photo-uploader-msg">Toca para tomar foto o elegir de la galería</span>
          <input
              type="file"
              name="lado_conductor_regreso"
              accept="image/*"
              capture="environment">
        </div>
        <div class="photo-preview" id="prev-ladoConductorRegreso"></div>
      </div>

      <!-- 4. LADO PASAJERO (1 sola foto) -->
      <div class="photo-slot">
        <div class="photo-slot-label">4. LADO PASAJERO</div>
        <div class="photo-uploader" data-name="ladoPasajeroRegreso" data-max-files="1">
          <span class="photo-uploader-msg">Toca para tomar foto o elegir de la galería</span>
          <input
              type="file"
              name="lado_pasajero_regreso"
              accept="image/*"
              capture="environment">
        </div>
        <div class="photo-preview" id="prev-ladoPasajeroRegreso"></div>
      </div>

      <!-- 5. ATRÁS (1 sola foto) -->
      <div class="photo-slot">
        <div class="photo-slot-label">5. ATRÁS</div>
        <div class="photo-uploader" data-name="atrasRegreso" data-max-files="1">
          <span class="photo-uploader-msg">Toca para tomar foto o elegir de la galería</span>
          <input
              type="file"
              name="atras_regreso"
              accept="image/*"
              capture="environment">
        </div>
        <div class="photo-preview" id="prev-atrasRegreso"></div>
      </div>

      <!-- 6. INTERIORES (MÁX 8 fotos) -->
      <div class="photo-slot">
        <div class="photo-slot-label">6. INTERIORES <span class="photo-hint">(máx. 8 fotos)</span></div>
        <div class="photo-uploader" data-name="interioresRegreso" data-max-files="8">
          <span class="photo-uploader-msg">Toca para tomar fotos o elegir de la galería</span>
          <input
              type="file"
              name="interiores_regreso[]"
              accept="image/*"
              capture="environment"
              multiple>
        </div>
        <div class="photo-preview" id="prev-interioresRegreso"></div>
      </div>
    </div>

  </div>
</section>






    <!-- ============================================ -->
<!--           SECCIÓN DE ACEPTACIÓN              -->
<!-- ============================================ -->

<section class="paper-section">
    <p class="legal-text">
        {{ $leyendaSeguro ?? 'He verificado que el vehículo lleva el equipo especial especificado. Que los daños están marcados en imagen de auto y no soy responsable por daños o robo parcial o total; salvo una negligencia.' }}
    </p>
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
        <span>Acepto:</span>

        @if($contrato->firma_cliente)
            <img src="{{ $contrato->firma_cliente }}"
                 class="firma-img"
                 alt="Firma del cliente">
        @else
            <button class="btn-open-sign"
                    data-type="cliente">
                Firmar Cliente
            </button>
        @endif
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

    <!-- ================= CLIENTE ================= -->
    <table class="sign-table">
        <tr>
            <th>Nombre del Cliente</th>
            <th>Firma del Cliente</th>
            <th>Fecha</th>
            <th>Hora</th>
        </tr>
        <tr>
            <td>
                <input type="text"
                       class="input-line"
                       data-field="firma_cliente_nombre"
                       placeholder="Nombre del cliente"
                       value="{{ $clienteNombre ?? '' }}">
            </td>

            <td>
                @if($contrato->firma_cliente)
                    <img src="{{ $contrato->firma_cliente }}" class="firma-img">
                @else
                    <button class="btn-open-sign" data-type="cliente">
                        Firmar Cliente
                    </button>
                @endif
            </td>

            <td>
                <input type="date"
                       class="input-line"
                       data-field="firma_cliente_fecha">
            </td>

            <td>
                <input type="time"
                       class="input-line"
                       data-field="firma_cliente_hora">
            </td>
        </tr>
    </table>

    <h3 class="sec-title">Sólo personal de Viajero</h3>

        {{-- Firmas para JS (reutilizar firma si entregó = recibió) --}}
    <input type="hidden" id="firmaArrendadorSrc" value="{{ $contrato->firma_arrendador ?? '' }}">
    <input type="hidden" id="firmaRecibioSrc"   value="{{ $contrato->firma_recibio ?? '' }}">


    <!-- ================= ENTREGA ================= -->
    <table class="sign-table">
        <tr>
            <th>Entregó</th>
            <th>Firma</th>
            <th>Fecha</th>
            <th>Hora</th>
        </tr>
        <tr>
            <td>
                <input type="text"
                       class="input-line"
                       data-field="entrego_nombre"
                       placeholder="Nombre del agente que entrega"
                       value="{{ $asesorNombre ?? '' }}">
            </td>

            <td>
                @if($contrato->firma_arrendador)
                    <img src="{{ $contrato->firma_arrendador }}" class="firma-img">
                @else
                    <button class="btn-open-sign" data-type="arrendador">
                        Firmar Agente
                    </button>
                @endif
            </td>

            <td>
                <input type="date"
                       class="input-line"
                       data-field="entrego_fecha">
            </td>

            <td>
                <input type="time"
                       class="input-line"
                       data-field="entrego_hora">
            </td>
        </tr>

         <!-- ================= RECIBE ================= -->
        <tr>
            <th>Recibió</th>
            <th>Firma</th>
            <th>Fecha</th>
            <th>Hora</th>
        </tr>
        <tr>
            {{-- SELECT con agentes (SuperAdmin + Ventas) --}}
            <td>
                @php
    // Nombre real guardado en el contrato (si ya lo seleccionaron antes)
    $recibioNombre = trim($contrato->recibio_nombre ?? '');
@endphp

                <select
    id="selectRecibioNombre"
    class="input-line"
    data-field="recibio_nombre">
    <option value="">Selecciona agente...</option>
    @foreach($agentes as $ag)
        @php
            $nombreAgente = trim($ag->nombre);

            // 1) Si ya hay recibido guardado en BD, usamos ese
            if ($recibioNombre !== '') {
                $seleccionado = ($nombreAgente === $recibioNombre) ? 'selected' : '';
            } else {
                // 2) Si no hay recibido, por default usamos al asesor que entrega
                $seleccionado = ($nombreAgente === ($asesorNombre ?? '')) ? 'selected' : '';
            }
        @endphp
        <option value="{{ $nombreAgente }}" {{ $seleccionado }}>
            {{ $nombreAgente }}
        </option>
    @endforeach
</select>

            </td>

            {{-- Firma de quien recibe (propia columna firma_recibio) --}}
            <td>
                @php $firmaRecibio = $contrato->firma_recibio ?? null; @endphp

                @if($firmaRecibio)
                    <img src="{{ $firmaRecibio }}" class="firma-img">
                @else
                    <button class="btn-open-sign" data-type="recibio">
                        Firmar quien recibe
                    </button>
                @endif
            </td>

            <td>
                <input type="date"
                       class="input-line"
                       data-field="recibio_fecha">
            </td>

            <td>
                <input type="time"
                       class="input-line"
                       data-field="recibio_hora">
            </td>
        </tr>
    </table>

</section>

<!-- ============================================ -->
<!--           ACCIONES CHECKLIST                 -->
<!-- ============================================ -->
<section class="paper-section">
    <div class="checklist-actions">
        <button type="button"
                id="btnChecklistSalida"
                class="btn btn-primary">
            Enviar checklist de salida
        </button>

        <button type="button"
                id="btnChecklistEntrada"
                class="btn btn-outline-primary">
            Enviar checklist de regreso
        </button>
    </div>
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
       RELOJ PARA FECHAS Y HORAS DE FIRMA / ENTREGA / RECIBO
    ============================================================= */
    const camposFecha = [
        "firma_cliente_fecha",
        "entrego_fecha",
        "recibio_fecha",
    ];

    const camposHora = [
        "firma_cliente_hora",
        "entrego_hora",
        "recibio_hora",
    ];

    function actualizarFechasHorasAhora() {
        const ahora = new Date();

        const yyyy = ahora.getFullYear();
        const mm   = String(ahora.getMonth() + 1).padStart(2, "0");
        const dd   = String(ahora.getDate()).padStart(2, "0");
        const hh   = String(ahora.getHours()).padStart(2, "0");
        const min  = String(ahora.getMinutes()).padStart(2, "0");

        const fechaStr = `${yyyy}-${mm}-${dd}`;   // formato yyyy-mm-dd
        const horaStr  = `${hh}:${min}`;          // formato HH:MM

        // Rellenar TODAS las fechas
        camposFecha.forEach((field) => {
            const input = document.querySelector(`[data-field="${field}"]`);
            if (input) {
                input.value = fechaStr;
            }
        });

        // Rellenar TODAS las horas
        camposHora.forEach((field) => {
            const input = document.querySelector(`[data-field="${field}"]`);
            if (input) {
                input.value = horaStr;
            }
        });
    }

    // Primera carga al abrir la vista
    actualizarFechasHorasAhora();

    // Se siguen actualizando mientras la vista esté abierta
    setInterval(actualizarFechasHorasAhora, 30 * 1000);

    // 🗂 Archivos seleccionados por cada uploader (clave = data-name)
    const uploaderFiles = {};

    /**
     * Comprime una imagen usando canvas.
     * - maxWidth: ancho máximo (el alto se ajusta solo)
     * - quality: calidad JPEG (0–1)
     */
    function compressImage(file, maxWidth = 1600, quality = 0.7) {
        return new Promise((resolve, reject) => {
            const img = new Image();

            img.onload = () => {
                let width  = img.width;
                let height = img.height;

                // Redimensionar si es más grande que maxWidth
                if (width > maxWidth) {
                    const ratio = maxWidth / width;
                    width  = maxWidth;
                    height = height * ratio;
                }

                const canvas = document.createElement("canvas");
                canvas.width  = width;
                canvas.height = height;

                const ctx = canvas.getContext("2d");
                ctx.drawImage(img, 0, 0, width, height);

                canvas.toBlob(
                    (blob) => {
                        if (!blob) {
                            return reject(new Error("No se pudo comprimir la imagen"));
                        }

                        const base    = file.name.replace(/\.[^.]+$/, "");
                        const newName = `${base}-cmp.jpg`;

                        const compressedFile = new File([blob], newName, {
                            type: "image/jpeg",
                            lastModified: Date.now(),
                        });

                        resolve(compressedFile);
                    },
                    "image/jpeg",
                    quality
                );
            };

            img.onerror = () => reject(new Error("No se pudo leer la imagen"));
            img.src = URL.createObjectURL(file);
        });
    }

    /* ==========================================================
       FUNCIÓN PARA CONFIGURAR GAUGE
    ============================================================= */
    function setupGauge(selectId, arcId, needleId, txtId) {

        const select = document.getElementById(selectId);
        if (!select) return;

        const arc = document.getElementById(arcId);
        const needle = document.getElementById(needleId);
        const txt = document.getElementById(txtId);

        function updateGauge() {
            const option = select.selectedOptions[0];
            const pct = option && option.dataset.pct ? parseFloat(option.dataset.pct) : 0;
            const val = option ? (option.value || "—") : "—";

            if (txt) {
                txt.textContent = val;
            }

            if (arc) {
                const offset = maxLength - (maxLength * (pct / 100));
                arc.style.strokeDashoffset = offset;
            }

            if (needle) {
                const angle = -90 + (pct * 1.8);
                needle.style.transform = `rotate(${angle}deg)`;
            }
        }

        select.addEventListener("change", updateGauge);

        const inicial = select.dataset.inicial;
        if (inicial) {
            [...select.options].forEach(op => {
                if (op.value === inicial) op.selected = true;
            });
        }
        updateGauge();
    }

    /* Inicializar gauges */
    setupGauge("selectGasSalida", "arcSalida", "needleSalida", "gasSalidaTxt");
    setupGauge("selectGasRecibe", "arcRecibe", "needleRecibe", "gasRecibeTxt");

    /* ==========================================================
       GUARDAR GASOLINA DE REGRESO
    ============================================================= */
    const selectGasRecibeEl = document.getElementById("selectGasRecibe");
    if (selectGasRecibeEl) {
        selectGasRecibeEl.addEventListener("change", async (e) => {

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
            if (data.ok) {
                alertify.success(data.msg || "Gasolina de regreso guardada.");
            } else {
                alertify.error(data.msg || "Error al guardar gasolina de regreso.");
            }
        });
    }

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

            tipoFirma = btn.dataset.type; // "cliente", "arrendador" o "recibio"

            if (tipoFirma === "cliente") {
                tituloModal.textContent = "Firma del Cliente";
            } else if (tipoFirma === "arrendador") {
                tituloModal.textContent = "Firma del Agente que entrega";
            } else if (tipoFirma === "recibio") {
                tituloModal.textContent = "Firma del Agente que recibe";
            } else {
                tituloModal.textContent = "Firma";
            }

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
            alertify.warning("La firma está vacía.");
            return;
        }

        const dataURL = signaturePad.toDataURL("image/png");

        let url = null;

        if (tipoFirma === "cliente") {
            url = "/contrato/firma-cliente";
        } else if (tipoFirma === "arrendador") {
            url = "/contrato/firma-arrendador";
        } else if (tipoFirma === "recibio") {
            url = "/contrato/firma-recibio";
        }

        if (!url) {
            alertify.error("Tipo de firma desconocido.");
            return;
        }

        const resp = await fetch(url, {
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

        if (!resp.ok) {
            alertify.error("Error al guardar la firma.");
            return;
        }

        alertify.success("Firma guardada correctamente.");
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
                    "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').content
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

    /* ==========================================================
       EDITAR + GUARDAR KILOMETRAJE DE REGRESO
    ============================================================= */
    const kmText = document.getElementById("kmRegresoText");
    const kmInput = document.getElementById("kmRegresoInput");
    const btnGuardarKm = document.getElementById("btnGuardarKm");

    if (kmText && kmInput && btnGuardarKm) {

        // Al dar clic en el texto
        kmText.addEventListener("click", () => {
            kmText.style.display = "none";
            kmInput.style.display = "inline-block";
            btnGuardarKm.style.display = "inline-block";
            kmInput.focus();
        });

        // Guardar kilometraje
        btnGuardarKm.addEventListener("click", async () => {

            const km = kmInput.value;

            if (!km || km < 0) {
                alertify.warning("Kilometraje inválido.");
                return;
            }

            const resp = await fetch(`/admin/checklist/${CHECKLIST_ID}/actualizar-km`, {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').content
                },
                body: JSON.stringify({
                    km_regreso: km
                })
            });

            if (!resp.ok) {
                alertify.error("Error al guardar kilometraje.");
                return;
            }

            // Actualizar texto
            kmText.textContent = km;

            // Volver a modo lectura
            kmInput.style.display = "none";
            btnGuardarKm.style.display = "none";
            kmText.style.display = "inline";

            alertify.success("Kilometraje de regreso guardado.");
        });
    }

    /* ==========================================================
       📸 NUEVO FLUJO: Vista previa + compresión por apartado
       - Usa .photo-uploader (HTML nuevo)
       - data-name => clave en uploaderFiles (frenteSalida, etc.)
       - data-max-files:
            - 1 → siempre se reemplaza la foto anterior
            - >1 → acumula hasta el máximo (interiores: 8)
    ============================================================= */
    document.querySelectorAll('.photo-uploader input[type="file"]').forEach((input) => {
        input.addEventListener("change", async (e) => {
            const contenedor = e.target.closest(".photo-uploader");
            if (!contenedor) return;

            const slotName = contenedor.getAttribute("data-name");
            if (!slotName) return;

            const maxFilesAttr = contenedor.getAttribute("data-max-files");
            const maxFiles = maxFilesAttr ? parseInt(maxFilesAttr, 10) : 99;

            const previewDiv = document.getElementById(`prev-${slotName}`);
            if (!previewDiv) return;

            // Inicializar arreglo para este apartado
            if (!uploaderFiles[slotName]) {
                uploaderFiles[slotName] = [];
            }

            let newFiles = Array.from(e.target.files || []);
            if (!newFiles.length) return;

            // Si solo se permite 1 foto, se reemplaza siempre por la última selección
            if (maxFiles === 1) {
                uploaderFiles[slotName] = [];
            } else {
                const actuales = uploaderFiles[slotName].length;
                const disponibles = maxFiles - actuales;

                if (disponibles <= 0) {
                    // 🔔 Alerta especial para daños interiores (máx 8)
                    if (slotName === "interioresSalida" || slotName === "interioresRegreso") {
                        alertify.warning("Ya alcanzaste el límite de 8 fotos en Daños interiores.");
                    } else {
                        alertify.warning(`Solo se permiten ${maxFiles} foto(s) en este apartado.`);
                    }
                    input.value = "";
                    return;
                }

                if (newFiles.length > disponibles) {
                    if (slotName === "interioresSalida" || slotName === "interioresRegreso") {
                        alertify.warning(
                            `Solo se permiten ${maxFiles} fotos en Daños interiores.\n` +
                            `Se tomarán solo las primeras ${disponibles} fotos seleccionadas.`
                        );
                    } else {
                        alertify.warning(
                            `Solo se permiten ${maxFiles} foto(s) en este apartado.\n` +
                            `Se tomarán solo las primeras ${disponibles} foto(s).`
                        );
                    }
                    newFiles = newFiles.slice(0, disponibles);
                }
            }

            // 🔽 Comprimir cada archivo nuevo
            const compressedList = [];
            for (const file of newFiles) {
                if (!file.type.startsWith("image/")) continue;

                try {
                    const compressed = await compressImage(file, 1600, 0.7);
                    compressedList.push(compressed);
                } catch (err) {
                    console.error("Error al comprimir imagen:", err);
                    compressedList.push(file);
                }
            }

            // Agregar los archivos comprimidos al arreglo existente
            uploaderFiles[slotName] = uploaderFiles[slotName].concat(compressedList);

            // Limpiar el input para permitir volver a abrir cámara/galería
            input.value = "";

            // Reconstruir la vista previa con TODOS los archivos almacenados en este slot
            previewDiv.innerHTML = "";
            uploaderFiles[slotName].forEach((file) => {
                const reader = new FileReader();
                reader.onload = (ev) => {
                    const thumb = document.createElement("div");
                    thumb.classList.add("thumb");
                    thumb.innerHTML = `
                        <img src="${ev.target.result}" alt="Vista previa">
                        <button type="button" class="rm" title="Quitar">×</button>
                    `;

                    previewDiv.appendChild(thumb);
                    previewDiv.removeAttribute("data-has-server-file");

                    // Quitar solo ESTA foto (del arreglo y de la vista)
                    thumb.querySelector(".rm").addEventListener("click", () => {
                        uploaderFiles[slotName] = uploaderFiles[slotName].filter(
                            (f) => f !== file
                        );
                        thumb.remove();
                    });
                };
                reader.readAsDataURL(file);
            });
        });
    });

    /* ==========================================================
       📤 Enviar checklist de SALIDA
    ============================================================= */
    const btnChecklistSalida = document.getElementById("btnChecklistSalida");

    if (btnChecklistSalida) {
        btnChecklistSalida.addEventListener("click", async () => {
            try {
                const token = document.querySelector('meta[name="csrf-token"]').content;

                const formData = new FormData();
                formData.append("_token", token);
                formData.append("tipo", "salida");

                // 📝 Campos de comentarios
                const comentario = document.querySelector('[data-field="comentario_cliente"]');
                const danos      = document.querySelector('[data-field="danos_interiores"]');

                const fcFecha = document.querySelector('[data-field="firma_cliente_fecha"]');
                const fcHora  = document.querySelector('[data-field="firma_cliente_hora"]');
                const eFecha  = document.querySelector('[data-field="entrego_fecha"]');
                const eHora   = document.querySelector('[data-field="entrego_hora"]');

                formData.append("comentario_cliente", comentario ? comentario.value : "");
                formData.append("danos_interiores",   danos      ? danos.value      : "");

                formData.append("firma_cliente_fecha", fcFecha ? fcFecha.value : "");
                formData.append("firma_cliente_hora",  fcHora  ? fcHora.value  : "");
                formData.append("entrego_fecha",       eFecha  ? eFecha.value  : "");
                formData.append("entrego_hora",        eHora   ? eHora.value   : "");

                // 📸 Fotos de salida por apartado → se mandan como
                // frente_salida, parabrisas_salida, etc.
                const mapSlotsToFields = {
                    frenteSalida:        "frente_salida",
                    parabrisasSalida:    "parabrisas_salida",
                    ladoConductorSalida: "lado_conductor_salida",
                    ladoPasajeroSalida:  "lado_pasajero_salida",
                    atrasSalida:         "atras_salida",
                    interioresSalida:    "interiores_salida[]",
                };

                const MAX_MB    = 2048;
                const MAX_BYTES = MAX_MB * 1024 * 1024;

                let hayFotos = false;

                for (const [slotName, fieldName] of Object.entries(mapSlotsToFields)) {
                    const files = uploaderFiles[slotName] || [];
                    if (!files.length) continue;

                    hayFotos = true;

                    if (slotName === "interioresSalida") {
                        // Puede haber varias
                        for (const file of files) {
                            if (file.size > MAX_BYTES) {
                                alertify.error(
                                    `La foto "${file.name}" pesa ${(file.size / 1024 / 1024).toFixed(1)} MB.\n` +
                                    `El máximo permitido es ${MAX_MB} MB.`
                                );
                                return;
                            }
                            formData.append(fieldName, file);
                        }
                    } else {
                        // Solo se usa 1 foto por apartado (la última seleccionada)
                        const file = files[files.length - 1];
                        if (file.size > MAX_BYTES) {
                            alertify.error(
                                `La foto "${file.name}" pesa ${(file.size / 1024 / 1024).toFixed(1)} MB.\n` +
                                `El máximo permitido es ${MAX_MB} MB.`
                            );
                            return;
                        }
                        formData.append(fieldName, file);
                    }
                }

                // No hacemos validación de "mínimo 1 foto" aquí,
                // lo valida el backend y regresa 422 si no hay ninguna.

                const resp = await fetch(`/admin/checklist/${CHECKLIST_ID}/enviar-salida`, {
                    method: "POST",
                    headers: {
                        "X-CSRF-TOKEN": token
                    },
                    body: formData
                });

                const rawText = await resp.text();
                let data = null;

                try {
                    data = JSON.parse(rawText);
                } catch (e) {}

                if (!resp.ok || !data || data.ok === false) {
                    let msg = "Error al enviar el checklist de salida.";

                    if (data && data.errors) {
                        msg = Object.values(data.errors).flat().join("\n");
                    } else if (data && data.msg) {
                        msg = data.msg;
                    } else if (
                        resp.status === 413 ||
                        rawText.toLowerCase().includes("post_max_size") ||
                        rawText.toLowerCase().includes("upload_max_filesize")
                    ) {
                        msg = "Las fotos son demasiado pesadas para el servidor. " +
                              "Intenta con menos fotos o en menor resolución.";
                    } else {
                        msg = `Error ${resp.status}:\n` + (rawText || "(sin cuerpo de respuesta)");
                    }

                    alertify.error(msg);
                    return;
                }

                alertify.success(data.msg || "Checklist de salida guardado correctamente.");
            } catch (err) {
                console.error(err);

                let msg = "Error de red al enviar el checklist de salida.";

                if (err && typeof err.message === "string" && err.message.includes("failed to upload")) {
                    msg = "Una de las fotos no se pudo subir (suele ser por tamaño o conexión).\n" +
                          "Intenta con menos fotos o en menor resolución.";
                } else if (err && err.message) {
                    msg += "\nDetalle: " + err.message;
                }

                alertify.error(msg);
            }
        });
    }

    /* ==========================================================
       📤 Enviar checklist de REGRESO
    ============================================================= */
    const btnChecklistEntrada = document.getElementById("btnChecklistEntrada");

    if (btnChecklistEntrada) {
        btnChecklistEntrada.addEventListener("click", async () => {
            try {
                const token = document.querySelector('meta[name="csrf-token"]').content;

                const formData = new FormData();
                formData.append("_token", token);
                formData.append("tipo", "entrada");

                // 📝 Campos de comentarios (SOLO regreso)
                const comentario = document.querySelector('[data-field="comentario_cliente"]');
                const danos      = document.querySelector('[data-field="danos_interiores"]');

                const rFecha  = document.querySelector('[data-field="recibio_fecha"]');
                const rHora   = document.querySelector('[data-field="recibio_hora"]');

                formData.append("comentario_cliente", comentario ? comentario.value : "");
                formData.append("danos_interiores",   danos      ? danos.value      : "");

                formData.append("recibio_fecha",       rFecha  ? rFecha.value  : "");
                formData.append("recibio_hora",        rHora   ? rHora.value   : "");

                                // 📸 Fotos de REGRESO: mandar por categoría igual que en SALIDA
                const mapSlotsToFieldsEntrada = {
                    frenteRegreso:        "frente_regreso",
                    parabrisasRegreso:    "parabrisas_regreso",
                    ladoConductorRegreso: "lado_conductor_regreso",
                    ladoPasajeroRegreso:  "lado_pasajero_regreso",
                    atrasRegreso:         "atras_regreso",
                    interioresRegreso:    "interiores_regreso[]", // puede llevar varias
                };

                const MAX_MB    = 2048;
                const MAX_BYTES = MAX_MB * 1024 * 1024;

                let totalFotosRegreso = 0;

                for (const [slotName, fieldName] of Object.entries(mapSlotsToFieldsEntrada)) {
                    const files = uploaderFiles[slotName] || [];
                    if (!files.length) continue;

                    if (slotName === "interioresRegreso") {
                        // 👉 interiores: se mandan TODAS (hasta el tope que tú ya limitas en el uploader)
                        for (const file of files) {
                            if (file.size > MAX_BYTES) {
                                alertify.error(
                                    `La foto "${file.name}" pesa ${(file.size / 1024 / 1024).toFixed(1)} MB.\n` +
                                    `El máximo permitido es ${MAX_MB} MB.`
                                );
                                return;
                            }
                            formData.append(fieldName, file);
                            totalFotosRegreso++;
                        }
                    } else {
                        // 👉 frente / parabrisas / lados / atrás: solo la ÚLTIMA seleccionada
                        const file = files[files.length - 1];
                        if (file.size > MAX_BYTES) {
                            alertify.error(
                                `La foto "${file.name}" pesa ${(file.size / 1024 / 1024).toFixed(1)} MB.\n` +
                                `El máximo permitido es ${MAX_MB} MB.`
                            );
                            return;
                        }
                        formData.append(fieldName, file);
                        totalFotosRegreso++;
                    }
                }

                if (!totalFotosRegreso) {
                    alertify.warning("Debes cargar al menos una foto del vehículo (regreso).");
                    return;
                }


                const resp = await fetch(`/admin/checklist/${CHECKLIST_ID}/enviar-entrada`, {
                    method: "POST",
                    headers: {
                        "X-CSRF-TOKEN": token
                    },
                    body: formData
                });

                const rawText = await resp.text();
                let data = null;

                try {
                    data = JSON.parse(rawText);
                } catch (e) {}

                if (!resp.ok || !data || data.ok === false) {
                    let msg = "Error al enviar el checklist de regreso.";

                    if (data && data.errors) {
                        msg = Object.values(data.errors).flat().join("\n");
                    } else if (data && data.msg) {
                        msg = data.msg;
                    } else if (
                        resp.status === 413 ||
                        rawText.toLowerCase().includes("post_max_size") ||
                        rawText.toLowerCase().includes("upload_max_filesize")
                    ) {
                        msg = "Las fotos son demasiado pesadas para el servidor. " +
                              "Intenta con menos fotos o en menor resolución.";
                    } else {
                        msg = `Error ${resp.status}:\n` + (rawText || "(sin cuerpo de respuesta)");
                    }

                    alertify.error(msg);
                    return;
                }

                alertify.success(data.msg || "Checklist de regreso guardado correctamente.");
            } catch (err) {
                console.error(err);

                let msg = "Error de red al enviar el checklist de regreso.";

                if (err && typeof err.message === "string" && err.message.includes("failed to upload")) {
                    msg = "Una de las fotos no se pudo subir (suele ser por tamaño o conexión).\n" +
                          "Intenta con menos fotos o en menor resolución.";
                } else if (err && err.message) {
                    msg += "\nDetalle: " + err.message;
                }

                alertify.error(msg);
            }
        });
    }

    /* ==========================================================
       Reutilizar firma si el mismo asesor entrega y recibe
    ============================================================= */
    const selectRecibio = document.getElementById("selectRecibioNombre");
    const inputEntrego  = document.querySelector('[data-field="entrego_nombre"]');
    const firmaArrInput = document.getElementById("firmaArrendadorSrc");
    const firmaRecInput = document.getElementById("firmaRecibioSrc");

    if (selectRecibio && inputEntrego && firmaArrInput) {

        const firmaArrSrc = firmaArrInput.value || "";
        const firmaRecSrcInicial = firmaRecInput ? (firmaRecInput.value || "") : "";

        async function guardarFirmaRecibio(firma) {
            const resp = await fetch("/contrato/firma-recibio", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').content
                },
                body: JSON.stringify({
                    id_contrato: CONTRATO_ID,
                    firma: firma
                })
            });
            return resp.ok;
        }

        async function manejarCambioRecibio() {
            const nombreEntrego = (inputEntrego.value || "").trim();
            const nombreRecibio = (selectRecibio.value || "").trim();

            // Nada seleccionado → limpiamos firma de "Recibió"
            if (!nombreRecibio) {
                await guardarFirmaRecibio("");
                return;
            }

            // MISMO asesor → copiar siempre la firma del arrendador si existe
            if (nombreEntrego && nombreRecibio === nombreEntrego && firmaArrSrc) {
                const ok = await guardarFirmaRecibio(firmaArrSrc);
                if (ok) {
                    alertify.success("Se reutilizó la firma del agente que entrega para 'Recibió'.");
                    location.reload();
                }
                return;
            }

            // Distinto asesor → limpiar firma y exigir que firme ese nuevo agente
            await guardarFirmaRecibio("");
            alertify.warning("Seleccionaste otro agente. Debe capturar una nueva firma para 'Recibió'.");
            location.reload();
        }

        // 👉 Al cambiar manualmente el select
        selectRecibio.addEventListener("change", manejarCambioRecibio);

        // 👉 Al cargar la página:
        (function autoSyncAlCargar() {
            const nombreEntrego = (inputEntrego.value || "").trim();
            const nombreRecibio = (selectRecibio.value || "").trim();
            const firmaRecActual = firmaRecSrcInicial;

            if (!firmaRecActual &&
                firmaArrSrc &&
                nombreEntrego &&
                nombreEntrego === nombreRecibio) {
                manejarCambioRecibio();
            }
        })();
    }

});
</script>




<!-- Librería oficial SignaturePad -->
    <script src="https://cdn.jsdelivr.net/npm/signature_pad@2.3.2/dist/signature_pad.min.js"></script>

@endsection
