{{-- resources/views/Admin/contrato-final-pdf.blade.php --}}
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <title>Contrato Final - PDF</title>

  <style>
  /* ==========================================================
     ✅ PDF FINAL (DomPDF Friendly)
     - Hoja 1: info
     - Hoja 2: cláusulas 1 columna justificado
     - Fix: SIN hoja en blanco
  ========================================================== */

  /* ✅ Margen real del PDF (AJUSTA AQUÍ) */
  @page{
    size: legal portrait;
    margin: 6mm;
  }

  :root{
    --brand:#FF1E2D;
    --brand-2:#D6121F;
    --ink:#101828;
    --muted:#667085;
    --stroke:#E5E7EB;
    --paper:#ffffff;
  }

  *{ box-sizing:border-box; }

  html, body{
    margin:0;
    padding:0;
    background:#fff !important;
    color:var(--ink);
    font-family: DejaVu Sans, Arial, sans-serif !important;
  }

  .contrato-final-container{
    width:100%;
    margin:0;
    padding: 0;
    background:#fff;
  }

  .acciones-contrato, .acciones-extra, .modal-firma{ display:none !important; }

  /* ==========================================================
     ✅ HOJA 1
  ========================================================== */

  .contrato-card{
    background: var(--paper);
    border: 0;
    border-radius: 0;
  }

  .encabezado{
    background: var(--brand);
    color:#fff;
    padding: 18px 20px;
    width:100%;
    display: table;
    table-layout: fixed;
  }
  .encabezado .left{
    display: table-cell;
    vertical-align: middle;
    width:70%;
  }
  .encabezado .right{
    display: table-cell;
    vertical-align: middle;
    width:30%;
    text-align:right;
    font-size: 12px;
    line-height: 1.25;
  }

  .logo-contrato{
    width: 54px;
    height:auto;
    border-radius: 10px;
    background: rgba(255,255,255,.14);
    padding: 6px;
    vertical-align: middle;
  }
  .titulo-texto{
    display:inline-block;
    vertical-align: middle;
    margin-left: 12px;
  }
  .titulo-texto h2{
    margin:0;
    font-size: 18px;
    font-weight: 900;
    letter-spacing:.2px;
  }
  .titulo-texto p{
    margin:2px 0 0;
    font-size: 12px;
    opacity:.95;
  }

  .secciones{
    padding: 14px 16px 16px;
    background:#fff;
  }

  .bloque{
    border: 1px solid var(--stroke);
    border-radius: 14px;
    background:#fff;
    padding: 12px 12px 12px 14px;
    margin-bottom: 10px;
    border-left: 4px solid var(--brand);
  }
  .bloque h3{
    margin:0 0 8px 0;
    font-size: 13px;
    font-weight: 900;
  }
  .bloque ul{
    margin:0;
    padding-left: 18px;
  }
  .bloque ul li{
    margin: 0 0 5px 0;
    font-size: 11.2px;
    line-height: 1.22;
  }

  /* En PDF, mejor 1 columna (DomPDF) */
  .bloque.ul-2cols ul{ columns: unset !important; }

  .tabla-tarifas{
    width:100%;
    border-collapse: collapse;
    border:1px solid var(--stroke);
    font-size: 11px;
    margin-top: 6px;
  }
  .tabla-tarifas th{
    background:#F8FAFC;
    text-align:left;
    font-weight: 900;
  }
  .tabla-tarifas th, .tabla-tarifas td{
    padding: 7px 8px;
    border:1px solid var(--stroke);
  }

  .totales{
    margin-top: 8px;
    padding: 10px 12px;
    border: 1px dashed var(--stroke);
    border-radius: 12px;
    background:#fff;
    font-size: 11px;
  }
  .totales p{ margin: 4px 0; }
  .totales .total-final{
    margin-top: 8px;
    padding-top: 8px;
    border-top: 1px solid var(--stroke);
    font-size: 12.5px;
    font-weight: 900;
    color: var(--brand);
  }

  .firmas{
    width:100%;
    display: table;
    table-layout: fixed;
    border-collapse: separate;
    border-spacing: 10px 0;
    margin-top: 8px;
  }
  .firma-box{
    display: table-cell;
    width:50%;
    border: 1px solid var(--stroke);
    border-radius: 12px;
    padding: 10px;
    text-align:center;
    vertical-align: top;
    background:#fff;
  }
  .firma-box p{
    margin: 0 0 8px;
    font-size: 11px;
    font-weight: 900;
  }
  .firma-img{
    width: 120px;
    max-width:100%;
    border: 1px solid var(--stroke);
    border-radius: 10px;
    background:#fff;
    display:block;
    margin: 0 auto;
  }

  .pie-contrato{
    text-align:center;
    margin: 10px 0 0;
    padding: 8px 0 0;
    border-top: 1px solid var(--stroke);
    font-size: 10.5px;
    color: var(--muted);
  }

  /* Evitar cortes feos en hoja 1 */
  .bloque, .tabla-tarifas, .totales, .firmas{
    page-break-inside: avoid;
  }

  /* ==========================================================
     ✅ HOJA 2 (Cláusulas)
     ✅ FIX hoja en blanco:
        - USAMOS page-break-before en hoja2
        - NO usamos <div class="page-break">
  ========================================================== */

  .hoja2{
    width:100%;
    background:#fff;

    /* ✅ FIX: este es el ÚNICO salto entre hojas */
    page-break-before: always;
  }

  /* ✅ OPCIÓN 1 (más ancha): menos “aire” lateral */
  .hoja2-wrap{
    width: 100%;
    padding: 0 10mm; /* antes 12mm -> ahora MÁS ANCHA */
  }

  .hoja2-marco{
    border: 2px solid #1f1f1f;
    border-radius: 46px;

    /* ✅ un poquito menos padding para ganar ancho útil */
    padding: 14px 12px 12px;

    width: 100%;

    /* ✅ MÁS ANCHO el marco */
    max-width: 182mm; /* antes 175mm */

    margin: 0 auto;

    /* ✅ OPCIÓN 2 (más larga): más alto */
    min-height: 336mm; /* antes 330mm */
  }

  .hoja2-inner{
    /* ✅ Texto MÁS ANCHO */
    max-width: 170mm; /* antes 164mm */
    margin: 0 auto;
  }

  .hoja2-encabezado{
    font-size: 9.6px;
    line-height: 1.16;
    text-transform: uppercase;
    text-align: left;
    margin: 0 0 7px 0;
  }

  .hoja2-titulo{
    font-size: 17.5px;
    font-weight: 900;
    text-align:center;
    margin: 0 0 9px 0;
    letter-spacing: .6px;
  }

  .clausula{
    font-size: 9.2px;
    line-height: 1.16;
    margin: 0 0 6px 0;
    text-align: justify;
    word-break: break-word;
    overflow-wrap: break-word;
    hyphens: auto;
  }
  .clausula b{ font-weight: 900; }

  .hoja2-pie{
    margin-top: 10px;
    font-size: 9.8px;
  }

  .pie-linea{
    display:inline-block;
    border-bottom: 1.8px solid #1f1f1f;
    height: 14px;
    vertical-align: bottom;
  }

  .w1{ width: 320px; }
  .w2{ width: 90px; }
  .w3{ width: 160px; }
  .w4{ width: 140px; }

  .hoja2 .firmas{
    width:100%;
    display: table;
    table-layout: fixed;
    border-collapse: separate;
    border-spacing: 10px 0;
    margin-top: 10px;
  }

  .hoja2 .firma-box{
    display: table-cell;
    width:50%;
    text-align:center;
    font-size: 9.8px;
    border: none;
    padding: 0;
    background: transparent;
    vertical-align: top;
  }

  .firma-linea{
    width: 80%;
    height: 16px;
    border-bottom: 1.8px solid #1f1f1f;
    margin: 0 auto 4px;
  }
  </style>
</head>

<body>
    @php
    use Carbon\Carbon;

    // Fecha de retiro
    $fechaRetiro = Carbon::parse($reservacion->fecha_inicio);

    $dia  = $fechaRetiro->day;
    $mes  = $fechaRetiro->translatedFormat('F'); // mes en texto
    $anio = $fechaRetiro->year;

    // Lugar: sucursal de retiro
    $lugar = $reservacion->sucursal_retiro_nombre ?? '________________';

    // ✅ Nombre completo del cliente (nombre + apellidos)
    $nombreCompletoCliente = trim(
        ($reservacion->nombre_cliente ?? '') . ' ' . ($reservacion->apellidos_cliente ?? '')
    );
@endphp

  <div class="contrato-final-container">

    {{-- ✅ HOJA 1 --}}
    <section class="contrato-card">
      <header class="encabezado">
        <div class="left">
          <img class="logo-contrato" src="{{ public_path('img/Logo3.jpg') }}" alt="Viajero">
          <div class="titulo-texto">
            <h2>VIAJERO CAR RENTAL</h2>
            <p>Contrato de Arrendamiento / Rental Agreement</p>
          </div>
        </div>

        <div class="right">
          <div><b>Contrato:</b> {{ $contrato->numero_contrato ?? '—' }}</div>
          <div><b>Código:</b> {{ $reservacion->codigo ?? '—' }}</div>
          <div><b>Fecha:</b> {{ \Carbon\Carbon::now()->format('d/m/Y') }}</div>
        </div>
      </header>

      <div class="secciones">

        <article class="bloque ul-2cols">
  <h3>Datos del Arrendatario</h3>
  <ul>
    <li>
      <b>Nombre:</b>
      {{ $nombreCompletoCliente !== '' ? $nombreCompletoCliente : ($reservacion->nombre_cliente ?? '—') }}
    </li>
    <li><b>Correo:</b> {{ $reservacion->email_cliente ?? '—' }}</li>
    <li><b>Teléfono:</b> {{ $reservacion->telefono_cliente ?? '—' }}</li>
    {{-- País viene de la licencia, igual que en la vista --}}
    <li><b>País:</b> {{ $licencia->pais_emision ?? '—' }}</li>
    <li><b>Vuelo:</b> {{ $reservacion->no_vuelo ?? '—' }}</li>
  </ul>
</article>


        <article class="bloque ul-2cols">
  <h3>Licencia del Cliente</h3>
  <ul>
    <li><b>No. Licencia:</b> {{ $licencia->numero_identificacion ?? '—' }}</li>
    <li><b>Vence:</b> {{ $licencia->fecha_vencimiento ?? '—' }}</li>
    <li><b>Emitida en:</b> {{ $licencia->pais_emision ?? '—' }}</li>
  </ul>
</article>


        <article class="bloque ul-2cols">
          <h3>Itinerario</h3>
          <ul>
            <li><b>Oficina de salida:</b> {{ $reservacion->sucursal_retiro_nombre ?? '—' }}</li>
            <li><b>Fecha/Hora salida:</b> {{ $reservacion->fecha_inicio ?? '—' }} {{ $reservacion->hora_retiro ?? '' }}</li>
            <li><b>Oficina de regreso:</b> {{ $reservacion->sucursal_entrega_nombre ?? '—' }}</li>
            <li><b>Fecha/Hora regreso:</b> {{ $reservacion->fecha_fin ?? '—' }} {{ $reservacion->hora_entrega ?? '' }}</li>
          </ul>
        </article>

        <article class="bloque ul-2cols">
          <h3>Vehículo Asignado</h3>
          <ul>
            <li><b>Modelo:</b> {{ $vehiculo->modelo ?? '—' }}</li>
            <li><b>Categoría:</b> {{ $vehiculo->categoria ?? '—' }}</li>
            <li><b>Color:</b> {{ $vehiculo->color ?? '—' }}</li>
            <li><b>Transmisión:</b> {{ $vehiculo->transmision ?? '—' }}</li>
            <li><b>Kilometraje:</b> {{ $vehiculo->kilometraje ?? '—' }}</li>
            <li><b>Gasolina inicial:</b> {{ $contrato->gasolina_inicial ?? '—' }}</li>
          </ul>
        </article>

        <article class="bloque">
          <h3>Tarifas y Protecciones</h3>

          <table class="tabla-tarifas">
            <thead>
              <tr>
                <th>Concepto</th>
                <th>Días</th>
                <th>Precio/Día</th>
                <th>Total (MXN)</th>
              </tr>
            </thead>
            <tbody>
              <tr>
                <td>Tarifa Base</td>
                <td>{{ $dias ?? 1 }}</td>
                <td>${{ number_format($tarifaBase ?? 0, 2) }}</td>
                <td>${{ number_format(($tarifaBase ?? 0) * ($dias ?? 1), 2) }}</td>
              </tr>

              @foreach(($paquetes ?? []) as $p)
                <tr>
                  <td>{{ $p->nombre ?? 'Paquete' }}</td>
                  <td>{{ $dias ?? 1 }}</td>
                  <td>${{ number_format($p->precio_por_dia ?? 0, 2) }}</td>
                  <td>${{ number_format(($p->precio_por_dia ?? 0) * ($dias ?? 1), 2) }}</td>
                </tr>
              @endforeach

              @foreach(($individuales ?? []) as $i)
                <tr>
                  <td>{{ $i->nombre ?? 'Protección' }}</td>
                  <td>{{ $dias ?? 1 }}</td>
                  <td>${{ number_format($i->precio_por_dia ?? 0, 2) }}</td>
                  <td>${{ number_format(($i->precio_por_dia ?? 0) * ($dias ?? 1), 2) }}</td>
                </tr>
              @endforeach

              @foreach(($extras ?? []) as $e)
                <tr>
                  <td>{{ $e->nombre ?? 'Servicio' }}</td>
                  <td>{{ $dias ?? 1 }}</td>
                  <td>${{ number_format($e->precio_unitario ?? 0, 2) }}</td>
                  <td>${{ number_format(($e->precio_unitario ?? 0) * ($dias ?? 1), 2) }}</td>
                </tr>
              @endforeach
            </tbody>
          </table>

          @php
            $subtotalCalc = $subtotal ?? 0;
            $ivaCalc = $subtotalCalc * 0.16;
            $totalCalc = $totalFinal ?? ($subtotalCalc + $ivaCalc);
          @endphp

          <div class="totales">
            <p><b>Subtotal:</b> ${{ number_format($subtotalCalc, 2) }}</p>
            <p><b>IVA (16%):</b> ${{ number_format($ivaCalc, 2) }}</p>
            <p class="total-final">TOTAL: ${{ number_format($totalCalc, 2) }} MXN</p>
          </div>
        </article>

        <article class="bloque">
          <h3>Firmas</h3>

          <div class="firmas">
            <div class="firma-box">
              <p>Firma del Cliente</p>
              @if(!empty($contrato->firma_cliente))
                <img class="firma-img" src="{{ $contrato->firma_cliente }}" alt="Firma cliente">
              @else
                <div style="height:60px;border:1px dashed #e5e7eb;border-radius:10px;"></div>
              @endif
            </div>

            <div class="firma-box">
              <p>Firma del Arrendador</p>
              @if(!empty($contrato->firma_arrendador))
                <img class="firma-img" src="{{ $contrato->firma_arrendador }}" alt="Firma arrendador">
              @else
                <div style="height:60px;border:1px dashed #e5e7eb;border-radius:10px;"></div>
              @endif
            </div>
          </div>

          <div class="pie-contrato">
            Documento generado por Viajero Car Rental • {{ \Carbon\Carbon::now()->format('d/m/Y H:i') }}
          </div>
        </article>

      </div>
    </section>

    {{-- ✅ HOJA 2 — CLÁUSULAS (1 COLUMNA) --}}
    <section class="hoja2">
      <div class="hoja2-wrap">
        <div class="hoja2-marco">
          <div class="hoja2-inner">

            <p class="hoja2-encabezado">
              CONTRATO DE ARRENDAMIENTO, QUE CELEBRA POR UNA PARTE LA COMPAÑÍA CUYA RAZÓN SOCIAL APARECE EN EL APARTADO NO. 1 DEL ANVERSO DE ESTE CONTRATO COMO ARRENDADORA, Y POR LA OTRA, LA PERSONA CUYO NOMBRE APARECE EN EL APARTADO NO. 2 DEL ANVERSO DE ESTE CONTRATO, CON CARÁCTER DE ARRENDATARIA.
            </p>

            <h2 class="hoja2-titulo">CLAUSULAS</h2>

            {{-- ✅ (NO QUITO NADA) — mismas 20 cláusulas --}}
            <p class="clausula"><b>PRIMERA.</b> LA ARRENDADORA entrega en arrendamiento a la ARRENDATARIA cuyo nombre aparece en la carátula de este documento, y dicha ARRENDATARIA recibe en tal carácter el vehículo objeto de este contrato en condiciones normales, mecánicas y de carrocería, consignadas en el inventario respectivo, con el carácter de BIEN ARRENDADO, a tener bajo su custodia y a su entera satisfacción, el vehículo de referencia y se obliga a pagar a la ARRENDADORA la renta señalada del contrato y a precisar de mercado, el o los faltantes de accesorios y partes del vehículo que recibe en el momento de entrega del mismo.</p>

            <p class="clausula"><b>SEGUNDA.</b> El término forzoso de este contrato de arrendamiento está señalado en la carátula de este contrato y nunca podrá ser prorrogado por ninguna de las partes, sin que aparezca constancia de voluntad de los mismos, en un nuevo contrato de arrendamiento.</p>

            <p class="clausula"><b>TERCERA.</b> LA ARRENDATARIA pagará como precio del arrendamiento el anticipo y precisamente el lugar donde deberán ser pagadas las cantidades estipuladas en el contrato de arrendamiento. Los pagos serán efectuados conforme a lo indicado en la carátula de este contrato. La renta deberá ser totalmente pagada aun cuando el vehículo se encuentre en uso de LA ARRENDATARIA, desde este momento, en plena posesión del automóvil y hasta la fecha en que lo reciba en devolución, a su entera satisfacción, LA ARRENDADORA.</p>

            <p class="clausula"><b>CUARTA.</b> LA ARRENDATARIA se obliga a entregar en devolución el vehículo arrendado precisamente en la hora y fecha convenidas y en la oficina de la ARRENDADORA en que se hubiera pactado la devolución, apareciendo esos datos en la carátula de este contrato de la misma que el vehículo se encontrará lavado y en condiciones normales, siendo el vehículo devuelto con el tanque lleno de gasolina. LA ARRENDATARIA deberá devolverlo en el lugar indicado en la carátula de este contrato. LA ARRENDATARIA deberá devolver el vehículo al lugar convenido en el contrato y en el plazo estipulado, más el importe que corresponda si la arrendataria del tiempo normal de traslado del lugar donde LA ARRENDATARIA haya dejado el vehículo a la oficina donde debió entregarlo de acuerdo con este contrato, aplicándose en todo caso la cuota diaria.</p>

            <p class="clausula"><b>QUINTA.</b> LA ARRENDATARIA tal como antes se señala se obliga a entregar el vehículo arrendado al término de este contrato, con el solo desgaste del uso normal y moderado, precisamente en la fecha y hora convenida y saldando en la carátula del contrato, con el pago del arrendamiento y en las condiciones señaladas en el contrato.</p>

            <p class="clausula"><b>SEXTA.</b> En caso de que LA ARRENDADORA niegue cualquier diligencia, previa autorización del pago de las prestaciones debidas por LA ARRENDATARIA o bien obtenga el vehículo devuelto legalmente aplicando las medidas de orden judicial o por acuerdo entre las partes, se autoriza a LA ARRENDADORA para disponer del vehículo en la forma que estime más adecuada, ya sea, venderlo, arrendarlo o cualquier otra forma de disposición que convenga a los intereses de LA ARRENDADORA.</p>

            <p class="clausula"><b>SÉPTIMA.</b> LA ARRENDATARIA se obliga a mantener el vehículo en buenas condiciones, realizando los servicios de mantenimiento preventivo y correctivo necesarios para su buen funcionamiento y conservación, así como a cubrir los gastos derivados de su uso normal.</p>

            <p class="clausula"><b>OCTAVA.</b> El vehículo arrendado se destinará única y exclusivamente al transporte de LA ARRENDATARIA y sus acompañantes, y solo podrá ser manejado por LA ARRENDATARIA o por conductores autorizados que cuenten con licencia vigente. Queda prohibido usar el vehículo para fines distintos a los pactados.</p>

            <p class="clausula"><b>NOVENA.</b> El vehículo arrendado no podrá ser conducido fuera de los límites del territorio de la República Mexicana, sin el previo consentimiento expreso y por escrito de LA ARRENDADORA.</p>

            <p class="clausula"><b>DÉCIMA.</b> LA ARRENDATARIA se obliga a no permitir que el vehículo sea utilizado para actividades ilícitas o contrarias a la ley.</p>

            <p class="clausula"><b>DÉCIMA PRIMERA.</b> LA ARRENDATARIA será responsable de cualquier daño, desperfecto o pérdida total o parcial del vehículo durante la vigencia del presente contrato, aun cuando sea causado por terceros.</p>

            <p class="clausula"><b>DÉCIMA SEGUNDA.</b> En caso de accidente, robo o pérdida total del vehículo, LA ARRENDATARIA deberá dar aviso inmediato a LA ARRENDADORA y a las autoridades correspondientes, obligándose a cubrir los daños y perjuicios conforme a lo estipulado en este contrato.</p>

            <p class="clausula"><b>DÉCIMA TERCERA.</b> LA ARRENDATARIA no podrá subarrendar, prestar, ceder o permitir el uso del vehículo a terceros sin autorización previa y por escrito de LA ARRENDADORA.</p>

            <p class="clausula"><b>DÉCIMA CUARTA.</b> LA ARRENDATARIA se obliga a pagar todas las multas, infracciones, gastos de arrastre, corralón y cualquier otro cargo que se genere por el uso del vehículo durante la vigencia del contrato.</p>

            <p class="clausula"><b>DÉCIMA QUINTA.</b> LA ARRENDATARIA deberá cubrir el importe de los daños ocasionados al vehículo por negligencia, imprudencia o mal uso del mismo.</p>

            <p class="clausula"><b>DÉCIMA SEXTA.</b> LA ARRENDADORA no será responsable por los objetos personales dejados dentro del vehículo arrendado durante el tiempo que se encuentre en posesión de LA ARRENDATARIA.</p>

            <p class="clausula"><b>DÉCIMA SÉPTIMA.</b> LA ARRENDATARIA reconoce que ha recibido el vehículo en óptimas condiciones y se obliga a devolverlo en el mismo estado, salvo el desgaste normal por el uso.</p>

            <p class="clausula"><b>DÉCIMA OCTAVA.</b> En caso de incumplimiento de cualquiera de las obligaciones establecidas en el presente contrato, LA ARRENDADORA podrá darlo por rescindido sin necesidad de declaración judicial.</p>

            <p class="clausula"><b>DÉCIMA NOVENA.</b> Para la interpretación y cumplimiento del presente contrato, las partes se someten a la jurisdicción de los tribunales competentes del Estado de Querétaro, renunciando a cualquier otro fuero que pudiera corresponderles por razón de su domicilio presente o futuro.</p>

            <p class="clausula"><b>VIGÉSIMA.</b> Las partes manifiestan que conocen y aceptan todas y cada una de las cláusulas del presente contrato, firmándolo de conformidad.</p>

            <div class="hoja2-pie">
              <div>
                En <span class="pie-linea w1">{{ $lugar }}</span>
                a los <span class="pie-linea w2">{{ $dia }}</span>
                día del mes de <span class="pie-linea w3">{{ ucfirst($mes) }}</span>
                de <span class="pie-linea w4">{{ $anio }}</span>
              </div>


              @if(!empty($contrato->firma_arrendador) || !empty($contrato->firma_cliente))
                  <div class="firmas">

                    {{-- FIRMA DEL ARRENDADOR --}}
                    @if(!empty($contrato->firma_arrendador))
                      <div class="firma-box">
                        <p>Firma del Arrendador</p>
                        <img class="firma-img" src="{{ $contrato->firma_arrendador }}" alt="Firma arrendador">
                      </div>
                    @endif

                    {{-- FIRMA DEL CLIENTE --}}
                    @if(!empty($contrato->firma_cliente))
                      <div class="firma-box">
                        <p>Firma del Cliente</p>
                        <img class="firma-img" src="{{ $contrato->firma_cliente }}" alt="Firma cliente">
                            <p style="margin-top:6px;font-size:10px;font-weight:700;">
      {{ $nombreCompletoCliente !== '' ? $nombreCompletoCliente : ($reservacion->nombre_cliente ?? '—') }}
    </p>

                      </div>
                    @endif

                  </div>
                @endif



          </div>
        </div>
      </div>
    </section>

  </div>
</body>
</html>
