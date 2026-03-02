{{-- resources/views/Admin/contrato-final-pdf.blade.php --}}
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <title>Contrato Final - PDF</title>

  <style>
  /* ==========================================================
     🧾 CONFIG BÁSICA PDF
  ========================================================== */

  @page{
    /* La hoja ahora es A4 vertical */
    size: A4 portrait;
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

  *{
    box-sizing:border-box;
  }

  html, body{
    margin:0;
    padding:0;
    background:#fff !important;
    color:var(--ink);
    font-family: "Bahnschrift", Arial, sans-serif;
  }

  .contrato-final-container{
    width:100%;
    margin:0;
    padding: 0;
    background:#fff;
  }

  /* ==========================================================
     🧾 ENCABEZADO NUEVO
     - Logo izquierda
     - Texto "Gracias por tu reserva, nombre
     - Texto secundario
     - Bloque derecho con etiquetas grises + chips rojos
     - Banda "INFORMACIÓN DE TU VEHÍCULO"
  ========================================================== */

  .header-contrato{
    width:100%;
    padding: 14mm 16mm 6mm 16mm;
    position:relative;
  }

  .header-layout{
    display: table;
    width:100%;
    table-layout: fixed;
  }

  .header-left{
    display: table-cell;
    width: 60%;
    vertical-align: top;
  }

  .header-right{
    display: table-cell;
    width: 40%;
    vertical-align: top;
    text-align:right;
    position:relative;
  }

  /* Logo VIAJERO */
  .logo-viajero{
    display:block;
    height: 12mm;         /* Ajusta si tu logo es más alto/bajo */
    width:auto;
    margin-bottom: 8mm;
  }

  /* Bloque de textos de saludo */
  .header-textos{
    margin-top: 0;
  }

  .header-titulo{
    margin:0 0 2mm 0;
    font-size: 18pt;
    font-weight: 700;
    color:#3B3B3B;
  }

  .header-subtitulo{
    margin:0;
    font-size: 11pt;
    font-weight: 400;
    color:#7A7A7A;
  }

  /* Figura gris del lado derecho (ruta/gota) */
  .header-figura{
    position:absolute;
    right: 4mm;
    top: -8mm;
    height: 60mm;         /* Alto aproximado como en el diseño */
    width:auto;
    z-index:1;
  }

  /* Bloque de datos en la esquina derecha */
  .header-datos{
    position:relative;
    z-index:2;
    font-size: 9pt;
    color:#B3B3B3;
    text-align:right;
    margin-top: 6mm;
  }

  .header-datos-linea{
    margin: 0 0 2.5mm 0;
    line-height:1.3;
  }

  .chip-rojo{
    display:inline-block;
    background: var(--brand);
    color:#fff;
    padding: 1.4mm 5mm;
    border-radius: 999px;
    margin-left: 4mm;
    font-size: 8.5pt;
    font-weight:700;
    white-space: nowrap;
  }

  /* ==========================================================
     🧾 TÍTULO SECCIÓN: INFORMACIÓN DE TU VEHÍCULO
  ========================================================== */

  .titulo-seccion-vehiculo{
    margin: 6mm 16mm 4mm 16mm;
    /* Usamos "table" para compatibilidad con DomPDF */
    display: table;
    width: calc(100% - 32mm); /* 16mm izq + 16mm der */
  }

  .titulo-vehiculo-texto{
    display: table-cell;
    white-space: nowrap;
    font-family: "Bahnschrift", Arial, sans-serif;
    font-weight: 700;
    font-size: 11.5pt;
    color: var(--brand);
    letter-spacing: 0.14em;
  }

  .titulo-vehiculo-linea{
    display: table-cell;
    width: 100%;
    padding-left: 6mm;
    vertical-align: middle;
  }

  .titulo-vehiculo-linea::before{
    content:"";
    display:block;
    border-bottom: 2px solid var(--brand);
    width: 100%;
  }

  /* ==========================================================
     🔴 FRANJA ROJA: INFORMACIÓN DEL VEHÍCULO
  ========================================================== */

  .vehiculo-info-box{
    margin: 0 16mm 8mm 16mm;     /* mismos márgenes horizontales que el título */
    width: calc(100% - 32mm);    /* 16mm izq + 16mm der */
    background: var(--brand);
    border-radius: 12mm 12mm 6mm 6mm; /* redondeado fuerte arriba como la imagen */
    padding: 7mm 10mm 6mm 10mm;
    color:#ffffff;
    font-family: Arial, sans-serif;
    font-size: 9pt;
  }

  .vehiculo-info-top{
    display: table;
    width:100%;
    table-layout: fixed;
    margin-bottom: 4mm;
  }

  .vehiculo-info-item{
    display: table-cell;
    vertical-align: top;
    padding-right: 4mm;
    text-align:left;
  }

  .vehiculo-info-label{
    display:block;
    font-weight:700;
    margin-bottom: 1mm;
  }

  .vehiculo-info-value{
    display:block;
    font-weight:400;
  }

  .vehiculo-info-bottom{
    display: table;
    width:100%;
    table-layout: fixed;
    margin-top: 1mm;
  }

  .vehiculo-info-bottom-left,
  .vehiculo-info-bottom-right{
    display: table-cell;
    vertical-align: middle;
    text-align:left;
  }

  .vehiculo-info-bottom-right{
    text-align:right;
  }

  .vehiculo-gas-icon{
    display:inline-block;
    height: 5mm;         /* icono de la gasolinera */
    width:auto;
    margin-right: 2mm;
    vertical-align: middle;
  }

  .vehiculo-inline-label{
    font-weight:700;
  }

  .vehiculo-inline-value{
    font-weight:400;
  }

  /* ==========================================================
     🔴 SECCIÓN ARRENDATARIO / ITINERARIO
  ========================================================== */

  .seccion-dos-columnas{
    margin: 6mm 16mm 4mm 16mm;
    width: calc(100% - 32mm); /* 16mm izq + 16mm der */
  }

  .seccion-dos-columnas-inner{
    display: table;
    width: 100%;
    table-layout: fixed;
  }

  .col-arrendatario,
  .col-itinerario{
    display: table-cell;
    vertical-align: top;
  }

  .col-arrendatario{
    padding-right: 8mm;
  }

  .col-itinerario{
    padding-left: 8mm;
  }

  .titulo-col-rojo{
    font-family: "Bahnschrift", Arial, sans-serif;
    font-weight: 700;
    font-size: 11.5pt;
    color: var(--brand);
    letter-spacing: 0.14em;
    text-transform: uppercase;
    margin: 0 0 3mm 0;
  }

  .titulo-col-derecha{
    text-align: right;
  }

  .arrendatario-datos{
    font-family: Arial, sans-serif;
    font-size: 9.3pt;
    color: #000;
  }

  .arrendatario-row{
    margin-bottom: 1.8mm;
  }

  .arrendatario-label{
    font-weight: 700;
  }

  .arrendatario-label-inline{
    margin-left: 10mm;
  }

  .arrendatario-value{
    font-weight: 400;
  }

  .arrendatario-underline{
    display: inline-block;
    padding-bottom: 0.3mm;
    border-bottom: 0.4pt solid #000;
    min-width: 35mm;
  }

  .arrendatario-tabla-licencia{
    width: 100%;
    margin-top: 3mm;
    border-collapse: collapse;
    font-family: Arial, sans-serif;
    font-size: 8.8pt;
  }

  .arrendatario-tabla-licencia thead tr th{
    border-top: 0.5pt solid #000;
    padding-top: 1.5mm;
    font-weight: 700;
    text-align: left;
  }

  .arrendatario-tabla-licencia tbody tr td{
    padding-top: 0.8mm;
    font-weight: 400;
  }

  .itinerario-bloque{
    font-family: Arial, sans-serif;
    font-size: 9.3pt;
    margin-bottom: 6mm;
  }

  .itinerario-label{
    font-weight: 700;
    margin: 0 0 1mm 0;
  }

  .itinerario-texto{
    margin: 0;
    line-height: 1.3;
  }

    /* ==========================================================
     🔴 SECCIÓN TARIFAS / ADICIONALES (franja roja abajo)
  ========================================================== */

  .tarifas-adicionales-wrap{
    margin-top: 6mm;
    background: var(--brand);
    color:#ffffff;
    padding: 4mm 0 5mm 0;
    width:100%;
  }

  .tarifas-adicionales-inner{
    margin: 0 16mm;
    width: calc(100% - 32mm); /* 16mm izq + 16mm der */
    display: table;
    table-layout: fixed;
  }

  .tarifas-col{
    display: table-cell;
    vertical-align: top;
    font-family: Arial, sans-serif;
    font-size: 8.8pt;
  }

  .tarifas-col-left{
    padding-right: 4mm;
    border-right: 0.4pt solid rgba(255,255,255,0.7);
  }

  .tarifas-col-right{
    padding-left: 4mm;
  }

  .tarifas-titulo{
    font-family: "Bahnschrift", Arial, sans-serif;
    font-weight: 700;
    font-size: 11.5pt;
    letter-spacing: 0.14em;
    text-transform: uppercase;
    margin: 0 0 3mm 0;
    color:#ffffff;
  }

  .tarifas-tabla,
  .adicionales-tabla{
    width:100%;
    border-collapse: collapse;
    font-family: Arial, sans-serif;
    font-size: 8.8pt;
  }

  .tarifas-tabla thead th,
  .adicionales-tabla thead th{
    padding: 1mm 0;
    border-bottom: 0.4pt solid rgba(255,255,255,0.7);
    text-align:left;
    font-weight:700;
  }

  .tarifas-tabla tbody td,
  .adicionales-tabla tbody td{
    padding: 0.9mm 0;
    border-bottom: 0.2pt solid rgba(255,255,255,0.25);
  }

  .tarifas-tabla th:nth-child(2),
  .tarifas-tabla td:nth-child(2),
  .tarifas-tabla th:nth-child(3),
  .tarifas-tabla td:nth-child(3),
  .tarifas-tabla th:nth-child(4),
  .tarifas-tabla td:nth-child(4){
    text-align:right;
  }

  .adicionales-tabla th:nth-child(2),
  .adicionales-tabla td:nth-child(2),
  .adicionales-tabla th:nth-child(3),
  .adicionales-tabla td:nth-child(3){
    text-align:right;
  }

  .tarifas-totales-tabla{
    width:100%;
    margin-top: 3mm;
    border-collapse: collapse;
    font-size: 9pt;
  }

  .tarifas-totales-tabla td{
    padding-top: 0.7mm;
  }

  .tarifas-totales-tabla .lbl{
    font-weight:700;
  }

  .tarifas-totales-tabla .val{
    text-align:right;
    font-weight:700;
  }

  .tarifas-totales-tabla .total-label{
    padding-top: 1.4mm;
  }

  .tarifas-totales-tabla .total-value{
    padding-top: 1.4mm;
    font-size: 9.5pt;
  }
  </style>
</head>

<body>
@php
    use Carbon\Carbon;

    // Nombre completo del cliente
    $nombreCompletoCliente = trim(
        ($reservacion->nombre_cliente ?? '') . ' ' . ($reservacion->apellidos_cliente ?? '')
    );

    // Nombre que se usará en el saludo
    $nombreSaludo = $reservacion->nombre_cliente
        ?? ($nombreCompletoCliente !== '' ? $nombreCompletoCliente : 'Cliente');

    // Tipo de cambio
    $tipoCambioValor = $tipoCambio ?? null;
    $textoTipoCambio = $tipoCambioValor !== null
        ? '$' . number_format($tipoCambioValor, 2) . ' MXN'
        : '—';

    // Fecha de apertura
    $fechaAperturaRaw = $contrato->fecha_apertura
        ?? (isset($reservacion->fecha_inicio)
            ? ($reservacion->fecha_inicio . ' ' . ($reservacion->hora_retiro ?? '00:00'))
            : null);

    if ($fechaAperturaRaw) {
        $fechaAperturaCarbon = Carbon::parse($fechaAperturaRaw);
        $textoFechaApertura = $fechaAperturaCarbon->format('d/m/y g:i a');
    } else {
        $textoFechaApertura = '—';
    }
@endphp

<div class="contrato-final-container">

  {{-- 🧾 ENCABEZADO --}}
  <header class="header-contrato">
    <div class="header-layout">
      {{-- LADO IZQUIERDO: LOGO + MENSAJE --}}
      <div class="header-left">
        {{-- Cambia la ruta del logo --}}
        <img
          class="logo-viajero"
          src="{{ public_path('img/VIAJEROPDF.png') }}"
          alt="Viajero Car Rental"
        >

        <div class="header-textos">
          <h1 class="header-titulo">
            Gracias por tu reserva, {{ $nombreSaludo }}
          </h1>
          <p class="header-subtitulo">
            Disfruta el camino tanto como tu destino.
          </p>
        </div>
      </div>

      {{-- LADO DERECHO: FIGURA + ETIQUETAS ROJAS --}}
      <div class="header-right">
        {{-- Cambia la ruta de la figura gris --}}
        <img
          class="header-figura"
          src="{{ public_path('img/A.png') }}"
          alt=""
        >

        <div class="header-datos">
          <p class="header-datos-linea">
            No. Rental Agreement:
            <span class="chip-rojo">
              {{ $contrato->numero_contrato ?? '—' }}
            </span>
          </p>

          <p class="header-datos-linea">
            Tipo de cambio:
            <span class="chip-rojo">
              {{ $textoTipoCambio }}
            </span>
          </p>

          <p class="header-datos-linea">
            Fecha de apertura:
            <span class="chip-rojo">
              {{ $textoFechaApertura }}
            </span>
          </p>

          <p class="header-datos-linea">
            Reservación:
            <span class="chip-rojo">
              {{ $reservacion->codigo ?? '—' }}
            </span>
          </p>
        </div>
      </div>
    </div>
  </header>

  {{-- TÍTULO: INFORMACIÓN DE TU VEHÍCULO --}}
  <div class="titulo-seccion-vehiculo">
    <div class="titulo-vehiculo-texto">
      INFORMACIÓN DE TU VEHÍCULO
    </div>
    <div class="titulo-vehiculo-linea"></div>
  </div>

  {{-- FRANJA ROJA CON DATOS DEL VEHÍCULO --}}
  <div class="vehiculo-info-box">
    <div class="vehiculo-info-top">
      <div class="vehiculo-info-item">
        <span class="vehiculo-info-label">Modelo:</span>
        <span class="vehiculo-info-value">
          {{ $vehiculo->modelo ?? '—' }}
        </span>
      </div>

      <div class="vehiculo-info-item">
        <span class="vehiculo-info-label">Categoría:</span>
        <span class="vehiculo-info-value">
          {{ $vehiculo->categoria ?? '—' }}
        </span>
      </div>

      <div class="vehiculo-info-item">
        <span class="vehiculo-info-label">Color:</span>
        <span class="vehiculo-info-value">
          {{ $vehiculo->color ?? '—' }}
        </span>
      </div>

      <div class="vehiculo-info-item">
        <span class="vehiculo-info-label">Placas:</span>
        <span class="vehiculo-info-value">
          {{ $vehiculo->placas ?? '—' }}
        </span>
      </div>

      <div class="vehiculo-info-item">
        <span class="vehiculo-info-label">Transmisión:</span>
        <span class="vehiculo-info-value">
          {{ $vehiculo->transmision ?? '—' }}
        </span>
      </div>

      <div class="vehiculo-info-item">
        <span class="vehiculo-info-label">Kilometraje:</span>
        <span class="vehiculo-info-value">
          {{ isset($vehiculo->kilometraje) ? number_format($vehiculo->kilometraje, 0) : '—' }}
        </span>
      </div>
    </div>

    <div class="vehiculo-info-bottom">
      <div class="vehiculo-info-bottom-left">
        {{-- Cambia la ruta del icono --}}
        <img
          class="vehiculo-gas-icon"
          src="{{ public_path('img/icono-gasolina.png') }}"
          alt="Gasolina"
        >
        <span class="vehiculo-inline-label">Capacidad del tanque:</span>
        <span class="vehiculo-inline-value">
          {{ $vehiculo->capacidad_tanque ?? '—' }}
        </span>
      </div>

      <div class="vehiculo-info-bottom-right">
        <span class="vehiculo-inline-label">Gasolina de salida:</span>
        <span class="vehiculo-inline-value">
          {{ $contrato->gasolina_inicial ?? '—' }}
        </span>
      </div>
    </div>
  </div>

</div> {{-- /contrato-final-container --}}

{{-- Cálculos para DOB, edad y fechas de check in/out --}}
@php
    $dobTexto = '—';
    $edadTexto = '—';

    if (!empty($reservacion->fecha_nacimiento)) {
        $fn = \Carbon\Carbon::parse($reservacion->fecha_nacimiento);
        $dobTexto  = '(' . $fn->format('d/m/Y') . ')';
        $edadTexto = $fn->age . ' años';
    }

    // Check out
    $textoCheckOut = '—';
    if (!empty($reservacion->fecha_inicio)) {
        $co = \Carbon\Carbon::parse(
            $reservacion->fecha_inicio . ' ' . ($reservacion->hora_retiro ?? '00:00')
        );
        $textoCheckOut = $co->format('d/m/y  -  H:i') . ' HRS';
    }

    // Check in
    $textoCheckIn = '—';
    if (!empty($reservacion->fecha_fin)) {
        $ci = \Carbon\Carbon::parse(
            $reservacion->fecha_fin . ' ' . ($reservacion->hora_entrega ?? '00:00')
        );
        $textoCheckIn = $ci->format('d/m/y  -  H:i') . ' HRS';
    }
@endphp

{{-- SECCIÓN: ARRENDATARIO / ITINERARIO --}}
<div class="seccion-dos-columnas">
  <div class="seccion-dos-columnas-inner">

    {{-- ARRENDATARIO --}}
    <div class="col-arrendatario">
      <h2 class="titulo-col-rojo">ARRENDATARIO</h2>

      <div class="arrendatario-datos">

        <div class="arrendatario-row">
          <span class="arrendatario-label">Nombre:</span>
          <span class="arrendatario-value">
            {{ $nombreCompletoCliente !== '' ? $nombreCompletoCliente : ($reservacion->nombre_cliente ?? '—') }}
          </span>
        </div>

        <div class="arrendatario-row">
          <span class="arrendatario-label">Fecha de nacimiento (DOB):</span>
          <span class="arrendatario-value">{{ $dobTexto }}</span>
        </div>

        <div class="arrendatario-row">
          <span class="arrendatario-label">Edad:</span>
          <span class="arrendatario-value">{{ $edadTexto }}</span>

          <span class="arrendatario-label arrendatario-label-inline">Teléfono:</span>
          <span class="arrendatario-value">
            {{ $reservacion->telefono_cliente ?? '—' }}
          </span>
        </div>

        <div class="arrendatario-row">
          <span class="arrendatario-label">Correo:</span>
          <span class="arrendatario-value">
            {{ $reservacion->email_cliente ?? '—' }}
          </span>
        </div>

        <div class="arrendatario-row">
          <span class="arrendatario-label">Dirección:</span>
          <span class="arrendatario-underline">
            {{ $reservacion->direccion_cliente ?? '—' }}
          </span>
        </div>

        <table class="arrendatario-tabla-licencia">
          <thead>
            <tr>
              <th>No. Licencia</th>
              <th>Vencimiento</th>
              <th>País</th>
              <th>Estado</th>
            </tr>
          </thead>
          <tbody>
            <tr>
              <td>{{ $licencia->numero_identificacion ?? '—' }}</td>
              <td>{{ $licencia->fecha_vencimiento ?? '—' }}</td>
              <td>{{ $licencia->pais_emision ?? '—' }}</td>
              <td>{{ $licencia->estado_emision ?? '—' }}</td>
            </tr>
          </tbody>
        </table>

      </div>
    </div>

    {{-- ITINERARIO --}}
    <div class="col-itinerario">
      <h2 class="titulo-col-rojo titulo-col-derecha">ITINERARIO</h2>

      <div class="itinerario-bloque">
        <p class="itinerario-label">Check out:</p>
        <p class="itinerario-texto">
          {{ $reservacion->sucursal_retiro_nombre ?? '—' }}<br>
          {{ $textoCheckOut }}
        </p>
      </div>

      <div class="itinerario-bloque">
        <p class="itinerario-label">Check in:</p>
        <p class="itinerario-texto">
          {{ $reservacion->sucursal_entrega_nombre ?? '—' }}<br>
          {{ $textoCheckIn }}
        </p>
      </div>

    </div>

  </div>
</div>
{{-- =======================================================
     SECCIÓN TARIFAS / ADICIONALES (franja roja)
   ======================================================= --}}
@php
    // Cálculos para mostrar en el cuadro de totales
    $ivaCalc        = $subtotal * 0.16;
    $cuotasLocales  = 0; // si más adelante definen un monto, va aquí
@endphp

<div class="tarifas-adicionales-wrap">
  <div class="tarifas-adicionales-inner">

    {{-- COLUMNA IZQUIERDA: TARIFAS --}}
    <div class="tarifas-col tarifas-col-left">
      <h2 class="tarifas-titulo">TARIFAS</h2>

      <table class="tarifas-tabla">
        <thead>
          <tr>
            <th>Concepto</th>
            <th>Días</th>
            <th>Precio por día</th>
            <th>Total</th>
          </tr>
        </thead>
        <tbody>
          {{-- Tarifa base --}}
          <tr>
            <td>Tarifa base</td>
            <td>{{ $dias ?? 1 }}</td>
            <td>${{ number_format($tarifaBase ?? 0, 2) }}</td>
            <td>${{ number_format(($tarifaBase ?? 0) * ($dias ?? 1), 2) }}</td>
          </tr>

          {{-- Paquetes de seguro --}}
          @foreach(($paquetes ?? []) as $p)
            <tr>
              <td>{{ $p->nombre ?? 'Paquete' }}</td>
              <td>{{ $dias ?? 1 }}</td>
              <td>${{ number_format($p->precio_por_dia ?? 0, 2) }}</td>
              <td>${{ number_format(($p->precio_por_dia ?? 0) * ($dias ?? 1), 2) }}</td>
            </tr>
          @endforeach

          {{-- Seguros individuales --}}
          @foreach(($individuales ?? []) as $i)
            <tr>
              <td>{{ $i->nombre ?? 'Protección' }}</td>
              <td>{{ $dias ?? 1 }}</td>
              <td>${{ number_format($i->precio_por_dia ?? 0, 2) }}</td>
              <td>${{ number_format(($i->precio_por_dia ?? 0) * ($dias ?? 1), 2) }}</td>
            </tr>
          @endforeach
        </tbody>
      </table>

      {{-- Totales al pie, igual que en el diseño --}}
      <table class="tarifas-totales-tabla">
        <tr>
          <td class="lbl">Subtotal:</td>
          <td class="val">
            ${{ number_format($subtotal ?? 0, 2) }}
          </td>
        </tr>
        <tr>
          <td class="lbl">I.V.A.</td>
          <td class="val">
            ${{ number_format($ivaCalc, 2) }}
          </td>
        </tr>
        <tr>
          <td class="lbl">Cuotas locales e impuestos federales</td>
          <td class="val">
            ${{ number_format($cuotasLocales, 2) }}
          </td>
        </tr>
        <tr>
          <td class="lbl total-label">TOTAL:</td>
          <td class="val total-value">
            ${{ number_format($totalFinal ?? (($subtotal ?? 0) + $ivaCalc + $cuotasLocales), 2) }}
          </td>
        </tr>
      </table>
    </div>

    {{-- COLUMNA DERECHA: ADICIONALES --}}
    <div class="tarifas-col tarifas-col-right">
      <h2 class="tarifas-titulo">ADICIONALES</h2>

      <table class="adicionales-tabla">
        <thead>
          <tr>
            <th>Producto</th>
            <th>Días</th>
            <th>Precio por día</th>
          </tr>
        </thead>
        <tbody>
          @forelse(($extras ?? []) as $e)
            <tr>
              <td>{{ $e->nombre ?? 'Servicio' }}</td>
              <td>{{ $dias ?? 1 }}</td>
              <td>${{ number_format($e->precio_unitario ?? 0, 2) }}</td>
            </tr>
          @empty
            {{-- Si no hay extras, dejamos una fila vacía para que no se vea el cuadro en blanco --}}
            <tr>
              <td colspan="3">Sin adicionales contratados.</td>
            </tr>
          @endforelse
        </tbody>
      </table>
    </div>

  </div>
</div>
</body>
</html>
