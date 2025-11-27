@extends('layouts.Ventas')

@section('Titulo', 'Check List – Inspección')

@section('css-vistareservacionesAdmin')
<link rel="stylesheet" href="{{ asset('css/checklist.css') }}">
@endsection

@section('contenidoreservacionesAdmin')

<div class="checklist-container">

  <!-- ============================================ -->
  <!--            ENCABEZADO SUPERIOR               -->
  <!-- ============================================ -->
  <header class="cl-header">
      <div class="cl-logo">
          <img src="/img/logo-viajero.png" alt="Logo Viajero">
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
          <strong>{{ $reservacion->id_reservacion ?? '30566' }}</strong>
      </div>
  </header>


  <!-- ============================================ -->
  <!--                DATOS DEL VEHÍCULO            -->
  <!-- ============================================ -->
  <section class="paper-section">
    <h3 class="sec-title">Datos del vehículo</h3>

    <table class="vehicle-table">
        <tr>
            <th>TIPO</th><td>{{ $tipo ?? 'N/A' }}</td>
            <th>MODELO</th><td>{{ $modelo ?? '2022' }}</td>
            <th>PLACAS</th><td>{{ $placas ?? 'UPP571F' }}</td>
            <th>COLOR</th><td>{{ $color ?? 'ROJO' }}</td>
            <th>TRANSMISIÓN</th><td>{{ $transmision ?? 'AUTOMÁTICO' }}</td>
        </tr>

        <tr>
            <th>CD. QUE ENTREGA</th><td>{{ $ciudadEntrega ?? 'INNERRA BUSINESS CENTER' }}</td>
            <th>CD. QUE RECIBE</th><td>{{ $ciudadRecibe ?? 'N/A' }}</td>
            <th>KILOMETRAJE SALIDA</th><td>{{ $kmSalida ?? '1,156 KM' }}</td>
            <th>KILOMETRAJE REGRESO</th><td>{{ $kmRegreso ?? '---' }}</td>
            <th>PROTECCIÓN</th><td>LDW</td>
        </tr>
    </table>
  </section>



  <!-- ============================================ -->
  <!--          GASOLINA – SALIDA                   -->
  <!-- ============================================ -->
  <section class="paper-section gas-wrap">
    <div class="gas-head">
      <h3 class="sec-title">Gasolina – Salida</h3>
      <div class="gas-pill">
        <span>Nivel seleccionado:</span>
        <strong id="gasSalidaTxt">—</strong>
      </div>
    </div>

    <!-- barra moderna -->
    <div class="gas-bar">
      <div class="gas-fill" id="gasSalidaFill"></div>

      <div class="gas-marks">
        @php
          $niveles = [
            "0","1/16","2/16","3/16","1/4","5/16","6/16","7/16",
            "1/2","9/16","10/16","11/16","3/4","13/16","14/16","15/16","1"
          ];
        @endphp

        @foreach($niveles as $i => $n)
          <button
            type="button"
            class="gas-mark"
            data-value="{{ $n }}"
            data-percent="{{ round(($i/(count($niveles)-1))*100) }}"
          >
            {{ $n }}
          </button>
        @endforeach
      </div>
    </div>

    <!-- firma -->
    <div class="sign-row">
        <label>Firma de Arrendador(a):</label>
        <span class="sig-line">MAYRA CARMONA GÓMEZ</span>
    </div>

    <!-- TABLA ORIGINAL DEL DOCUMENTO -->
    <table class="fuel-table-original">
      <tr>
          <th>Gas Salida</th>
          <th>Fracción</th>
          <th>Dieciseisavos</th>
          <th>Octavos</th>
      </tr>
      <tr>
          <td>1</td>
          <td>15/16</td>
          <td>1</td>
          <td>—</td>
      </tr>
    </table>
  </section>



  <!-- ============================================ -->
  <!--          GASOLINA – RECIBIDO                 -->
  <!-- ============================================ -->
  <section class="paper-section gas-wrap">
    <div class="gas-head">
      <h3 class="sec-title">Gasolina – Recibido</h3>
      <div class="gas-pill">
        <span>Nivel seleccionado:</span>
        <strong id="gasRecibeTxt">—</strong>
      </div>
    </div>

    <!-- barra moderna -->
    <div class="gas-bar">
      <div class="gas-fill" id="gasRecibeFill"></div>

      <div class="gas-marks">
        @foreach($niveles as $i => $n)
          <button
            type="button"
            class="gas-mark gas-mark--recibe"
            data-value="{{ $n }}"
            data-percent="{{ round(($i/(count($niveles)-1))*100) }}"
          >
            {{ $n }}
          </button>
        @endforeach
      </div>
    </div>

    <!-- firma -->
    <div class="sign-row">
        <label>Firma de Arrendador(a):</label>
        <span class="sig-line">MAYRA CARMONA GÓMEZ</span>
    </div>

    <!-- TABLA ORIGINAL -->
    <table class="fuel-table-original">
      <tr>
          <th>Gas Recibido</th>
          <th>Fracción</th>
          <th>Dieciseisavos</th>
          <th>Octavos</th>
      </tr>
      <tr>
          <td>1</td>
          <td>15/16</td>
          <td>1</td>
          <td>—</td>
      </tr>
    </table>
  </section>



  <!-- ============================================ -->
  <!--             DIAGRAMA DE VEHÍCULO             -->
  <!-- ============================================ -->
  <section class="paper-section">
    <h3 class="sec-title center">Daños visuales</h3>

    <div class="diagram-card">
      <!-- etiquetas -->
      <div class="diagram-label diagram-label--top">LADO PASAJERO</div>
      <div class="diagram-label diagram-label--bottom">LADO CONDUCTOR</div>
      <div class="diagram-label diagram-label--left">FRENTE</div>
      <div class="diagram-label diagram-label--right">REVERSO</div>

      <!-- tu SVG -->
      @include('components.diagram-car') 
      <!-- puedes reemplazar o usar el SVG directo -->
    </div>
  </section>



  <!-- ============================================ -->
  <!--          EQUIPO QUE SE LLEVA (ORIGINAL)      -->
  <!-- ============================================ -->
  <section class="paper-section">
    <h3 class="sec-title">El cliente se lo lleva</h3>

    <table class="equip-table">
      <tr><td>PLACAS</td><td>2</td><td>ESPEJOS LATERALES</td><td>2</td></tr>
      <tr><td>TOLDO-JEEP</td><td>1</td><td>ESPEJO INTERIOR</td><td>1</td></tr>
      <tr><td>TARJETA DE CIRCULACIÓN</td><td>1</td><td>ANTENA</td><td>1</td></tr>
      <tr><td>TARJETA DE VERIFICACIÓN</td><td>1</td><td>RADIO</td><td>1</td></tr>
      <tr><td>PÓLIZA DE SEGURO</td><td>1</td><td>TAPÓN DE GASOLINA</td><td>1</td></tr>
      <tr><td>LLANTA DE REFACCIÓN</td><td>1</td><td>TAPETES</td><td>4</td></tr>
      <tr><td>GATO</td><td>1</td><td>LLAVE DE ENCENDIDO</td><td>1</td></tr>
      <tr><td>HERRAMIENTA</td><td>1</td><td>BIRLOS DE SEGURIDAD</td><td>1</td></tr>
      <tr><td>POLVERAS</td><td>4</td><td>TUERCA DE SEGURIDAD</td><td>1</td></tr>
    </table>
  </section>



  <!-- ============================================ -->
  <!--           TEXTO LEGAL ORIGINAL               -->
  <!-- ============================================ -->
  <section class="paper-section">
    <p class="legal-text">
        He verificado que el vehículo lleva el equipo especial especificado.  
        Que los daños están marcados en imagen de auto y no soy responsable por daños  
        o robo parcial o total; salvo una negligencia.
    </p>

    <div class="accept-line">
      <span>Acepto:</span>
      <div class="line"></div>
      <span class="client-name">MAYRA CARMONA GÓMEZ</span>
    </div>
  </section>



  <!-- ============================================ -->
  <!--          INFORMACIÓN DE POSIBLES CARGOS      -->
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
      <div class="line"></div>
      <span class="xbox">Acepto X</span>
    </div>
  </section>



  <!-- ============================================ -->
  <!--              COMENTARIOS + DAÑOS             -->
  <!-- ============================================ -->
  <section class="paper-section">
    <h3 class="sec-title">Comentario</h3>
    <div class="comment-box"></div>

    <h3 class="sec-title">Daños Interiores</h3>
    <div class="comment-box"></div>
  </section>



  <!-- ============================================ -->
  <!--             BLOQUE FINAL ORIGINAL            -->
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
        <td>MAYRA CARMONA GÓMEZ</td>
        <td>________________________</td>
        <td>__________</td>
        <td>__________</td>
      </tr>
    </table>

    <h3 class="sec-title">Sólo personal de Viajero</h3>

    <table class="sign-table">
      <tr><th>Entregó</th><th>Firma</th><th>Fecha</th><th>Hora</th></tr>
      <tr><td>__________________</td><td>__________________</td><td>__________</td><td>__________</td></tr>
      <tr><th>Recibió</th><th>Firma</th><th>Fecha</th><th>Hora</th></tr>
      <tr><td>__________________</td><td>__________________</td><td>__________</td><td>__________</td></tr>
    </table>
  </section>

</div>
@endsection



@section('js-vistareservacionesAdmin')
<script>
document.addEventListener("DOMContentLoaded", () => {

  // ===== GAS SALIDA =====
  const salidaFill = document.getElementById("gasSalidaFill");
  const salidaTxt  = document.getElementById("gasSalidaTxt");

  document.querySelectorAll(".gas-mark:not(.gas-mark--recibe)").forEach(btn => {
    btn.addEventListener("click", () => {
      const pct = btn.dataset.percent;
      const val = btn.dataset.value;

      salidaFill.style.width = pct + "%";
      salidaTxt.textContent = val;

      document.querySelectorAll(".gas-mark:not(.gas-mark--recibe)").forEach(b => b.classList.remove("active"));
      btn.classList.add("active");
    });
  });

  // ===== GAS RECIBIDO =====
  const recibeFill = document.getElementById("gasRecibeFill");
  const recibeTxt  = document.getElementById("gasRecibeTxt");

  document.querySelectorAll(".gas-mark--recibe").forEach(btn => {
    btn.addEventListener("click", () => {
      const pct = btn.dataset.percent;
      const val = btn.dataset.value;

      recibeFill.style.width = pct + "%";
      recibeTxt.textContent = val;

      document.querySelectorAll(".gas-mark--recibe").forEach(b => b.classList.remove("active"));
      btn.classList.add("active");
    });
  });

});
</script>
@endsection
