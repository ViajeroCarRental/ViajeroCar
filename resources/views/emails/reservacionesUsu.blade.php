<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="color-scheme" content="light dark">
  <meta name="supported-color-schemes" content="light dark">

  <style>
    body, table, td, a { -webkit-text-size-adjust:100%; -ms-text-size-adjust:100%; }
    table, td { border-collapse:collapse !important; }
    body { margin:0 !important; padding:0 !important; width:100% !important; }

    body {
      font-family: 'Poppins', Arial, sans-serif;
      background-color: #f4f6fb;
      color: #333333;
    }

    .container {
      max-width: 700px;
      margin: 40px auto;
      background-color: #ffffff;
      border-radius: 16px;
      overflow: hidden;
      box-shadow: 0 8px 25px rgba(0,0,0,0.08);
      border: 1px solid #e2e8f0;
    }

    /* HEADER NUEVO (tipo imagen 1) */
    .header{
      background:#b1060f; /* rojo sólido */
      padding: 22px 26px;
      color:#fff;
    }

    .header-table{
      width:100%;
      border-collapse:collapse;
    }

    .brand-logo{
      width:210px;
      max-width:210px;
      height:auto;
      display:block;
      border:none;
    }

    .resv-box{
      text-align:right;
      vertical-align:middle;
      font-weight:700;
      letter-spacing:.5px;
    }

    .resv-box .label{
      font-size:18px;
      text-transform:uppercase;
      opacity:.95;
      margin:0;
    }

    .resv-box .code{
      font-size:22px;
      margin:6px 0 0 0;
    }

    /* Mensaje nuevo (tipo imagen 1) */
    .hero{
      padding: 30px 40px 10px;
    }

    .hero .thanks{
      font-size:22px;
      font-weight:700;
      margin:0 0 14px 0;
    }

    .hero .lead{
      font-size:16px;
      line-height:1.75;
      margin:0 0 18px 0;
    }

    .hero a{
      color:#E50914;
      text-decoration:underline;
      font-weight:600;
    }

    .content {
      padding: 35px 40px 40px;
      font-size: 15px;
      line-height: 1.7;
    }

    .divider {
      height: 1px;
      background: #b22222;
      margin: 35px 0;
    }

    .footer {
      background: #fafafa;
      color: #777;
      font-size: 13px;
      text-align: center;
      padding: 20px 30px;
      border-top: 1px solid #e2e8f0;
    }

    .footer a {
      color: #E50914;
      text-decoration: none;
    }

    /* =========================
       RESUMEN (tipo imagen 2)
    ========================= */
    .summary-title{
      font-size: 22px;
      font-weight: 700;
      margin: 10px 0 14px;
      color:#111;
    }

    .summary-card{
      border:1px solid #1f2937;
      border-radius: 16px;
      padding: 18px 18px;
    }

    .summary-top{
      width:100%;
      border-collapse:collapse;
      margin-bottom: 10px;
    }

    .summary-top .left{
      font-size: 18px;
      font-weight: 700;
    }

    .summary-top .right{
      text-align:right;
      font-weight:700;
      letter-spacing:.4px;
    }

    .summary-line{
    height:1px;
    background:#b22222;
    margin: 18px 0;
    }

    /* Línea roja para separar secciones */
    .summary-line-red {
    border-top: 2px solid #b22222;
    margin: 15px 0;
    }

    /* Lugar y fecha con iconos */
    .location-item {
    margin-bottom: 20px;
    }
    .location-header {
    display: flex;
    align-items: center;
    gap: 10px;
    margin-bottom: 6px;
    }
    .location-icon svg {
    width: 18px;
    height: 18px;
    stroke: #000000;
    stroke-width: 2;
    }
    .location-title {
    font-weight: 700;
    font-size: 14px;
    text-transform: uppercase;
    color: #111827;
    }
    .location-datetime {
    font-size: 14px;
    font-weight: 600;
    color: #111827;
    margin-left: 28px;
    margin-bottom: 4px;
    }
    .location-place {
    font-size: 13px;
    color: #6b7280;
    margin-left: 28px;
    } /***/

    .section-title{
      font-size:16px;
      font-weight:700;
      margin: 0 0 10px 0;
    }

    .item{
      width:100%;
      border-collapse:collapse;
      margin: 0 0 10px 0;
    }

    .item .label{
      font-weight:600;
      color:#111;
      width: 40%;
    }

    .item .value{
      color:#111;
      text-align:left;
    }

    /* =========================
   TABLA DE PRECIOS MEJORADA
   ========================= */
    .price-table {
        width: 100%;
        border-collapse: collapse;
        margin: 15px 0;
    }

    .price-table td {
        padding: 6px 0;
        border: none;
    }

    .price-table .price-label {
        color: #4b5563;
        font-weight: 500;
        font-size: 14px;
        text-align: left;
        width: 60%;
    }

    .price-table .price-value {
        text-align: right;
        font-weight: 600;
        color: #1f2937;
        font-size: 14px;
        width: 40%;
    }

    /* Fila divisora (gris) */
    .price-table .divider-row td {
        padding: 0;
    }

    .price-table .divider-line {
        border-top: 1px solid #e5e7eb;
        margin: 8px 0;
    }

    /* Fila TOTAL (rojo) */
    .price-table .total-row td {
        padding-top: 12px;
        padding-bottom: 4px;
    }

    .price-table .total-label {
        font-weight: 800;
        font-size: 16px;
        color: #b22222;
        text-align: left;
    }

    .price-table .total-value {
        font-weight: 800;
        font-size: 18px;
        color: #b22222;
        text-align: right;
    }

    .price-table .p-label{
      color:#111;
    }

    .price-table .p-value{
      text-align:right;
      color:#111;
    }

    .price-total{
      font-weight:800;
      font-size:18px;
      padding-top: 6px !important;
    }

    /* Texto debajo de Detalles del precio */
    .price-note{
      font-size:14px;
      line-height:1.7;
      color:#111;
      margin: 18px 0 10px;
    }

    /* Línea roja inferior */
    .price-note-line{
      height:3px;
      background:#b1060f;
      margin-top: 10px;
    }

    /* =========================
       Requisitos y protección LI
    ========================= */
    .info-section{
      font-size:14px;
      line-height:1.7;
      color:#111;
      margin-top: 18px;
    }

    .info-section-title{
      font-size:15px;
      font-weight:700;
      margin:0 0 6px;
    }

    .info-section-list{
      margin:0 0 12px;
      padding-left:18px;
    }

    .info-section-list li{
      margin:0 0 4px;
    }

    .info-section-paragraph{
      margin:0 0 16px;
    }

    /* =========================
       TU AUTO
    ========================= */
    .auto-container {
    display: flex;
    gap: 24px;
    margin: 15px 0;
    }
    .auto-image {
    flex: 0 0 130px;
    }
    .auto-image img {
    width: 100%;
    height: auto;
    border-radius: 12px;
    }
    .auto-details {
    flex: 1;
    }
    .auto-title {
    font-size: 18px;
    font-weight: 800;
    color: #111827;
    margin: 0 0 4px;
    }
    .auto-subtitle {
    font-size: 11px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    color: #6b7280;
    margin: 0 0 12px;
    }
    .auto-specs {
    display: flex;
    flex-wrap: wrap;
    gap: 16px;
    margin: 10px 0;
    }
    .spec-item {
    font-size: 13px;
    color: #4b5563;
    display: flex;
    align-items: center;
    }
    .spec-item svg {
    stroke: #000000;
    margin-right: 4px;
    }
    .spec-item strong {
    color: #111827;
    font-weight: 700;
    }
    .auto-includes {
    font-size: 12px;
    color: #4b5563;
    margin-top: 8px;
    }
    .auto-includes svg {
    stroke: #000000;
    }

    /* =========================
       EXTRAS
    ========================= */
    .extras-list {
    margin: 10px 0;
    }
    .extra-item {
    display: flex;
    align-items: flex-start;
    gap: 12px;
    margin-bottom: 16px;
    padding: 0;
    border: none;
    background: transparent;
    }
    .extra-checkbox {
    width: 20px;
    height: 20px;
    border-radius: 4px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    background: white;
    border: 2px solid #d1d5db;
    font-size: 12px;
    font-weight: bold;
    color: white;
    flex-shrink: 0;
    margin-top: 2px;
    }
    .extra-checkbox.selected {
    background: #b22222;
    border-color: #b22222;
    color: white;
    }
    .extra-content {
    flex: 1;
    }
    .extra-name {
    font-weight: 800;
    font-size: 14px;
    color: #111827;
    margin: 0 0 4px;
    }
    .extra-desc {
    font-size: 12px;
    color: #6b7280;
    margin: 0 0 4px;
    }
    .extra-price {
    font-size: 12px;
    font-weight: 600;
    color: #111827;
    }
        /* Extras en 3 columnas */
    .extras-three-columns {
        width: 100%;
        border-collapse: collapse;
        margin: 10px 0;
    }
    .extras-three-columns td {
        vertical-align: top;
        padding: 8px 10px;
    }
    /* Extras en grid flexible */
.extras-grid-flex {
    display: flex;
    flex-wrap: wrap;
    gap: 24px;
    margin: 15px 0;
}
.extras-card {
    flex: 1;
    min-width: 180px;
}
.extras-checkbox-large {
    width: 24px;
    height: 24px;
    border-radius: 6px;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}
.extras-checkbox-large.selected {
    background: #b22222;
    border: 2px solid #b22222;
}
.extras-checkbox-large.empty {
    background: white;
    border: 2px solid #d1d5db;
}

    /* =========================
       FOOTER TIPO LANDING
    ========================= */
    .site-footer{
      margin-top:40px;
      background:#e5e7eb;
      padding:18px 30px 20px;
      font-size:13px;
      color:#111827;
    }

    .footer-inner{
      max-width:700px;
      margin:0 auto;
    }

    .footer-top{
      display:flex;
      justify-content:space-between;
      align-items:center;
      margin-bottom:8px;
    }

    .footer-social{
      font-size:0;
    }

    .footer-social a{
      display:inline-block;
      margin-right:14px;
    }

    .footer-social img{
      max-height:20px;
      display:block;
    }

    .footer-logo-word{
      font-size:26px;
      font-weight:800;
      letter-spacing:.12em;
    }

    .footer-sep{
      height:1px;
      background:#111827;
      margin:8px 0 14px;
    }

    .footer-main{
      display:flex;
      justify-content:space-between;
      gap:24px;
      margin-bottom:14px;
    }

    .footer-col{
      flex:1;
    }

    .footer-col p{
      font-size:12px;
      margin:0 0 6px;
    }

    .footer-col ul{
      list-style:none;
      padding:0;
      margin:0;
    }

    .footer-col li{
      margin-bottom:4px;
    }

    .footer-col a{
      color:#111827;
      text-decoration:none;
      font-size:12px;
      text-transform:uppercase;
    }

    .footer-col a:hover{
      text-decoration:underline;
    }

    .footer-pay{
      border-top:1px solid #111827;
      padding-top:10px;
    }

    .footer-pay img{
      max-height:22px;
      display:inline-block;
      margin-right:10px;
    }
  </style>
</head>

<body>

<div class="container">

 <!-- HEADER NUEVO -->
 <div class="header" style="background-color: #b1060f; padding: 20px 26px; border-radius: 16px 16px 0 0; overflow: hidden;">
    <table width="100%" cellpadding="0" cellspacing="0" border="0" style="border-collapse: collapse;">
        <tr>
            <td align="left" valign="middle">
                <img src="{{ url('img/Logo3.jpg') }}"
                     alt="Viajero Car Rental"
                     width="180"
                     style="display: block; width: 180px; max-width: 100%; height: auto; border: 0; outline: none; text-decoration: none;">
            </td>

            <td align="right" style="vertical-align: middle; color: #ffffff; font-family: 'Poppins', Arial, sans-serif;">
                <p style=" margin:0; font-size:14px; text-transform:uppercase; font-weight:400; letter-spacing:1.5px; display:inline-block; min-width:220px; text-align:right; padding-right:8px;">
                   @if($tipo === 'linea' || $tipo === 'en_linea')
                      Reserva confirmada
                   @else
                      RESERVACIÓN
                   @endif
                </p>
                <p style="margin: 5px 0 0 0; font-size: 22px; font-weight: 700; letter-spacing: 1px;">
                  {{ $reservacion->codigo }}
               </p>
            </td>
        </tr>
       </table>
     </td>
    </tr>
 </table>
  </div>

 <!-- CONTENT -->
<div class="content">

    @php
    // Función para formatear fecha y hora
    function formatDateTimeEmailView($date, $time) {
        if (!$date) return '';

        $carbon = \Carbon\Carbon::parse($date);
        $fechaFormateada = $carbon->translatedFormat('D. d \d\e M.');

        $horaFormateada = '';
        if ($time) {
            $horaCarbon = \Carbon\Carbon::parse($time);
            $horaFormateada = $horaCarbon->format('g:i A');
        }

        return trim($fechaFormateada . ' ' . $horaFormateada);
    }

    // Definir las variables usando los datos de la reservación
    $pickupFormatted = formatDateTimeEmailView($reservacion->fecha_inicio, $reservacion->hora_retiro);
    $dropoffFormatted = formatDateTimeEmailView($reservacion->fecha_fin, $reservacion->hora_entrega);

    $serviciosDisponibles = [
        ['nombre' => 'Silla de bebé', 'desc' => 'Baby safety seat.', 'precio' => 150, 'unidad' => 'por día'],
        ['nombre' => 'Gasolina Prepago', 'desc' => 'Full tank based on vehicle category.', 'precio' => 1200, 'unidad' => 'por tanque'],
        ['nombre' => 'Conductor adicional', 'desc' => 'Add an extra driver.', 'precio' => 150, 'unidad' => 'por día'],
    ];
@endphp

  <!-- MENSAJE NUEVO -->
  <div class="hero">
    <p class="thanks">
      ¡Gracias!
      <strong>{{ strtoupper(trim(($reservacion->nombre_cliente ?? 'Cliente') . ' ' . ($reservacion->apellidos_cliente ?? ''))) }}</strong>
    </p>

    <p class="lead">
      @if($tipo === 'linea' || $tipo === 'en_linea')
        Tu vehículo ya está reservado, el pago ha sido recibido exitosamente.
      @else
        Tu vehículo ya está reservado
      @endif
      , el siguiente código es tu número de reservación,
      da <a href="{{ route('visor.show', ['id' => $reservacion->id_reservacion]) }}">click aquí</a> para más información.
    </p>

     <p class="lead" style="margin-top:0; text-align: justify; font-size: 16px; line-height: 1.75;">
        La siguiente información se calculó con los datos proporcionados en el proceso de reservación,
        cualquier modificación relacionada con lo que esta reservación describe podría resultar en una variación contra el precio acordado.
     </p>
   </div>

   <!-- ===================== RESUMEN ===================== -->
<h2 class="summary-title">Resumen de tu reserva</h2>

<div class="summary-card">

@php
    $pickup  = \Carbon\Carbon::parse($reservacion->fecha_inicio . ' ' . $reservacion->hora_retiro);
    $dropoff = \Carbon\Carbon::parse($reservacion->fecha_fin . ' ' . $reservacion->hora_entrega);

    $fechaInicio = \Carbon\Carbon::parse($reservacion->fecha_inicio);
    $fechaFin    = \Carbon\Carbon::parse($reservacion->fecha_fin);
    $diasCorreo  = max(1, $fechaInicio->diffInDays($fechaFin));

    $tarifaBaseDia   = (float) ($reservacion->tarifa_base ?? 0);
    $tarifaBaseTotal = round($tarifaBaseDia * $diasCorreo, 2);

    // IDs de extras seleccionados
    $extrasIds = collect($extrasReserva)->pluck('id_servicio')->toArray();
@endphp

<!-- ENCABEZADO -->
<table class="summary-top" role="presentation">
    <tr>
        <td class="left">Lugar y fecha</td>
        <td class="right">
            RESERVACIÓN<br>
            {{ $reservacion->codigo }}
        </td>
    </tr>
</table>

<div class="summary-line-red"></div>
<!-- ===================== LUGAR Y FECHA ===================== -->
@php
    \Carbon\Carbon::setLocale('es');

    // Fuente 1: Variables pasadas directamente al mail (si existen)
    $pickupTimeRaw = $pickupTime ?? null;
    $dropoffTimeRaw = $dropoffTime ?? null;

    // Fuente 2: Si no, usar $reservacion (pero tiene 00:00)
    if (empty($pickupTimeRaw) || $pickupTimeRaw == '00:00' || $pickupTimeRaw == '00:00:00') {
        $pickupTimeRaw = $reservacion->hora_retiro ?? '00:00';
    }

    if (empty($dropoffTimeRaw) || $dropoffTimeRaw == '00:00' || $dropoffTimeRaw == '00:00:00') {
        $dropoffTimeRaw = $reservacion->hora_entrega ?? '00:00';
    }

    // Fuente 3: Si las horas son 00:00, usar las que vienen de $pickupTime (si existe en el contexto)
    if (($pickupTimeRaw == '00:00' || $pickupTimeRaw == '00:00:00') && isset($pickupTime)) {
        $pickupTimeRaw = $pickupTime;
    }

    if (($dropoffTimeRaw == '00:00' || $dropoffTimeRaw == '00:00:00') && isset($dropoffTime)) {
        $dropoffTimeRaw = $dropoffTime;
    }

    // Limpiar formato
    $pickupTimeClean = preg_replace('/:\d{2}$/', '', $pickupTimeRaw);
    $dropoffTimeClean = preg_replace('/:\d{2}$/', '', $dropoffTimeRaw);

    // Fechas
    $pickupDateStr = $reservacion->fecha_inicio ?? null;
    $dropoffDateStr = $reservacion->fecha_fin ?? null;

    // Formatear fecha y hora
    $pickupFormatted = 'FECHA NO DISPONIBLE';
    $dropoffFormatted = 'FECHA NO DISPONIBLE';

    if ($pickupDateStr && $pickupTimeClean) {
        try {
            $pickup = \Carbon\Carbon::parse($pickupDateStr . ' ' . $pickupTimeClean);
            $pickupFormatted = strtoupper($pickup->translatedFormat('D. d M. Y H:i A'));
            $pickupFormatted = str_replace(['AM','PM'], ['A.M','P.M'], $pickupFormatted);
        } catch (\Exception $e) {
            $pickupFormatted = $pickupDateStr . ' ' . $pickupTimeClean . ' HRS';
        }
    }

    if ($dropoffDateStr && $dropoffTimeClean) {
        try {
            $dropoff = \Carbon\Carbon::parse($dropoffDateStr . ' ' . $dropoffTimeClean);
            $dropoffFormatted = strtoupper($dropoff->translatedFormat('D. d M. Y H:i A'));
            $dropoffFormatted = str_replace(['AM','PM'], ['A.M','P.M'], $dropoffFormatted);
        } catch (\Exception $e) {
            $dropoffFormatted = $dropoffDateStr . ' ' . $dropoffTimeClean . ' HRS';
        }
    }
@endphp

<div style="margin-bottom: 20px;">
    <!-- PICK-UP -->
    <div style="margin-bottom: 16px;">
        <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 8px;">
            <img src="https://imgur.com/UymwMqF.png" alt="icon" width="20" height="20" style="display: block; margin-right:6px;">
            <div style="font-weight: 700; font-size: 13px;">
                PICK-UP:
            </div>
        </div>
        <div style="font-weight: 600; font-size: 14px; margin-left: 28px;">
            {{ $pickupFormatted }}
        </div>
        <div style="font-size: 13px; color: #6b7280; margin-left: 28px;">
            {{ $lugarRetiro ?? 'Lugar no especificado' }}
        </div>
    </div>

    <!-- DEVOLUCIÓN -->
    <div>
        <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 8px;">
            <img src="https://imgur.com/UymwMqF.png" alt="icon" width="20" height="20" style="display: block; margin-right:6px;">
            <div style="font-weight: 700; font-size: 13px;">
                DEVOLUCIÓN:
            </div>
        </div>
        <div style="font-weight: 600; font-size: 14px; margin-left: 28px;">
            {{ $dropoffFormatted }}
        </div>
        <div style="font-size: 13px; color: #6b7280; margin-left: 28px;">
            {{ $lugarEntrega ?? 'Lugar no especificado' }}
        </div>
    </div>
</div>

<!-- ===================== TU AUTO ===================== -->
<div style="font-weight:700; font-size:16px; margin-bottom:12px;">
    TU AUTO
</div>

<div style="border-top:2px solid #e11d48; width:100%; margin-bottom:15px;"></div>

<div style="margin:15px 0;">

    <table role="presentation" width="100%">
        <tr>

            <!-- IMAGEN -->
            <td width="130" style="vertical-align: top;">
                <img src="{{ $imgCategoria }}" width="120"
                     style="display:block; border-radius:12px;">
            </td>

            <!-- INFO -->
            <td style="padding-left:15px; vertical-align: top;">

                <!-- TITULO -->
                <div style="font-size:18px; font-weight:800; color:#111827;">
                    {{ $categoria->descripcion }}
                </div>

                <!-- SUBTITULO -->
                <div style="font-size:12px; font-weight:700; color:#6b7280; margin-bottom:10px;">
                    {{ strtoupper($categoria->nombre) }} | CATEGORÍA {{ $categoria->codigo }}
                </div>

                <!-- ICONOS -->
                <div style="font-size:13px; color:#111; margin-top:6px;">

                    <img src="https://cdn.jsdelivr.net/npm/@fortawesome/fontawesome-free/svgs/solid/user.svg"
                         width="14" style="vertical-align:middle;">
                    <strong>{{ $tuAuto['pax'] }}</strong>

                    &nbsp;&nbsp;

                    <img src="https://cdn.jsdelivr.net/npm/@fortawesome/fontawesome-free/svgs/solid/suitcase-rolling.svg"
                         width="14" style="vertical-align:middle;">
                    <strong>{{ $tuAuto['small'] }}</strong>

                    &nbsp;&nbsp;

                    <img src="https://cdn.jsdelivr.net/npm/@fortawesome/fontawesome-free/svgs/solid/briefcase.svg"
                         width="14" style="vertical-align:middle;">
                    <strong>{{ $tuAuto['big'] }}</strong>

                    &nbsp;&nbsp;

                    <span style="font-weight:600;">
                    T | {{ __('Automatic') }}
                    </span>

                    &nbsp;&nbsp;

                    <img src="https://cdn.jsdelivr.net/npm/@fortawesome/fontawesome-free/svgs/regular/snowflake.svg"
                         width="14" style="vertical-align:middle;">
                    <span style="font-weight:600;">A/C</span>

                </div>

                <!-- CARPLAY / ANDROID -->
                <div style="margin-top:10px;">

                    <span style="
                        background:#111827;
                        color:#fff;
                        padding:6px 12px;
                        border-radius:20px;
                        font-size:12px;
                        font-weight:600;
                        display:inline-block;
                        margin-right:6px;
                    ">
                        CarPlay
                    </span>

                    <span style="
                        background:#16a34a;
                        color:#fff;
                        padding:6px 12px;
                        border-radius:20px;
                        font-size:12px;
                        font-weight:600;
                        display:inline-block;
                    ">
                        Android Auto
                    </span>

                </div>

                <!-- INCLUYE -->
                <div style="margin-top:10px; font-size:13px; color:#111;">
                    ✓ KM ilimitados | Relevo de Responsabilidad (LI)
                </div>

            </td>
        </tr>
    </table>

</div>

<!-- ===================== EXTRAS ===================== -->
 <div style="font-weight: 700; font-size: 16px; margin-bottom: 12px;">
        EXTRAS
    </div>
<div class="summary-line-red"></div>

<div style="margin: 15px 0;">

    <table role="presentation" width="100%">
        <tr>

            @foreach($serviciosDisponibles as $index => $servicio)

                @php
                    $seleccionado = collect($extrasReserva)
                        ->pluck('nombre')
                        ->contains($servicio['nombre']);
                @endphp

                <td width="33%" style="vertical-align: top; padding-bottom: 12px;">

                    <table role="presentation">
                        <tr>

                            <!-- CUADRO -->
                            <td style="vertical-align: top; padding-right: 8px;">
                                <div style="
                                    width:18px;
                                    height:18px;
                                    border-radius:4px;
                                    border:2px solid {{ $seleccionado ? '#b22222' : '#d1d5db' }};
                                    background: {{ $seleccionado ? '#b22222' : '#fff' }};
                                    text-align:center;
                                    line-height:16px;
                                    font-size:12px;
                                    color:white;
                                    font-weight:bold;
                                ">
                                    @if($seleccionado) ✓ @endif
                                </div>
                            </td>

                            <!-- TEXTO -->
                            <td>
                                <div style="font-size:13px; font-weight:700; color:#111;">
                                    {{ $servicio['nombre'] }}
                                </div>

                                <div style="font-size:11px; color:#6b7280;">
                                    {{ $servicio['desc'] }}
                                </div>

                                <div style="font-size:11px; font-weight:600; color:#111;">
                                    ${{ number_format($servicio['precio'],0) }} / {{ $servicio['unidad'] }}
                                </div>
                            </td>

                        </tr>
                    </table>

                </td>

                @if(($index + 1) % 3 == 0)
                    </tr><tr>
                @endif

            @endforeach

        </tr>
    </table>
</div>

<!-- ===================== PRECIOS ===================== -->
<p class="section-title">Detalles del precio</p>
<div class="summary-line-red"></div>

<table class="price-table" role="presentation">
    <!-- Tarifa base -->
    <tr>
        <td class="price-label">Tarifa base</td>
        <td class="price-value">${{ number_format($tarifaBaseTotal, 2) }} MXN</td>
    </tr>

    <!-- Opciones de renta -->
    <tr>
        <td class="price-label">Opciones de renta</td>
        <td class="price-value">${{ number_format($opcionesRentaTotal, 2) }} MXN</td>
    </tr>

    <!-- Cargos e IVA -->
    <tr>
        <td class="price-label">Cargos e IVA</td>
        <td class="price-value">${{ number_format($reservacion->impuestos, 2) }} MXN</td>
    </tr>

    <!-- Línea divisora gris -->
    <tr class="divider-row">
        <td colspan="2">
            <div class="divider-line"></div>
        </td>
    </tr>

    <!-- TOTAL (en rojo) -->
    <tr class="total-row">
        <td class="total-label"><strong>TOTAL</strong></td>
        <td class="total-value"><strong>${{ number_format($reservacion->total, 2) }} MXN</strong></td>
    </tr>
</table>

<!-- ===================== PAGO ===================== -->
<p style="margin-top:15px;">
@if($tipo === 'linea')
    <strong>Método de pago:</strong> PayPal<br>
    <strong>Total pagado:</strong> ${{ number_format($reservacion->total, 2) }} MXN
@else
    <strong>Método de pago:</strong> Pago en mostrador<br>
@endif
</p>

</div>{{-- cierre .summary-card --}}

    {{-- Texto y línea roja debajo del detalle de precio --}}
    <p class="price-note" style="text-align: justify; font-size: 14px; line-height: 1.7; color: #111; margin: 18px 0 10px;">
     VIAJERO te garantiza el tamaño del vehículo y sus características, más no el modelo
     específico. Nos comprometemos a entregarte un auto de la categoría reservada, por
     ejemplo un auto compacto, pudiendo ser cualquiera de las marcas que manejamos en
     nuestra flota dentro de este grupo.
    </p>
    <div class="price-note-line"></div>

          {{-- Bloque: Requisitos y protección LI --}}
    <div class="info-section">
       <p class="info-section-title" style="font-size: 15px; font-weight: 700; margin: 0 0 6px;">
           Requisitos para rentar
       </p>

     <ul class="info-section-list" style="margin: 0 0 12px; padding-left: 18px; text-align: justify;">
    <li style="margin: 0 0 4px; text-align: justify;">Tarjeta de crédito: Con un mínimo de antigüedad de un año, todas nuestras rentas deben ser amparadas con una tarjeta de crédito.</li>
    <li style="margin: 0 0 4px; text-align: justify;">Edad mínima 21 años: Aplica un cargo por conductor joven si eres menor de 25 años.</li>
    <li style="margin: 0 0 4px; text-align: justify;">Identificación con fotografía: Credencial del IFE/INE o Pasaporte.</li>
    <li style="margin: 0 0 4px; text-align: justify;">Licencia para conducir: Deberá estar vigente.</li>
    <li style="margin: 0 0 4px; text-align: justify;">Relevos de responsabilidad: Elegir entre nuestras opciones de protección para el auto (100%, 90%, 80% o 0%).</li>
  </ul>


      <p class="info-section-paragraph" style="margin: 0 0 16px; text-align: justify; font-size: 14px; line-height: 1.7;">
        Los requisitos de renta pueden variar, si requieres más información comunícate al 01 (442) 303 26 68 o escríbenos a reservaciones@viajerocar-rental.com
     </p>

      <p class="info-section-title">Protección limitada de responsabilidad hacia terceros (LI)</p>

      <p class="info-section-paragraph" style="text-align: justify;">
         Protege a terceros por daños y perjuicios ocasionados en un accidente y cubre la cantidad mínima
         requerida por ley. Tú eliges el nivel de responsabilidad sobre el auto que más vaya acorde a tus
         necesidades y presupuesto. Pregunta por nuestros relevos de responsabilidad (opcionales) al llegar
        al mostrador de cualquiera de nuestras oficinas.
      </p>
    </div>

  </div>

      {{-- FOOTER GRANDE TIPO LANDING --}}
  <div class="site-footer">
    <div class="footer-inner">

      <div class="footer-top">
        <div class="footer-social">
          <a href="https://wa.me/524423032668">
            <img src="{{ asset('img/email/whatsapp-black.png') }}" alt="WhatsApp">
          </a>
          <a href="https://www.facebook.com/viajerocarental">
            <img src="{{ asset('img/email/facebook-black.png') }}" alt="Facebook">
          </a>
          <a href="https://www.instagram.com/viajerocarental">
            <img src="{{ asset('img/email/instagram-black.png') }}" alt="Instagram">
          </a>
          <a href="https://www.tiktok.com/@viajerocarental">
            <img src="{{ asset('img/email/tiktok-black.png') }}" alt="TikTok">
          </a>
        </div>

        <div class="footer-logo-word">
          <img src="{{ asset('img/LogoB.png') }}" alt="Viajero" class="footer-logo">
        </div>
      </div>

      <div class="footer-sep"></div>

      <div class="footer-main">
        <div class="footer-col">
         <p style="margin:5px 0;">
            <img src="https://i.imgur.com/l9Ib5lO.png"  width="16" style="vertical-align:middle; margin-right:8px;">
            <span style="vertical-align:middle;">
                 OFICINA CENTRAL PARK, QUERÉTARO
          </span>
         </p>

         <p style="margin:5px 0;">
           <img src="https://i.imgur.com/l9Ib5lO.png" width="16" style="vertical-align:middle; margin-right:8px;">
            <span style="vertical-align:middle;">
                  PICK-UP AEROPUERTO DE QUERÉTARO
            </span>
         </p>

        <p style="margin:5px 0;">
          <img src="https://i.imgur.com/l9Ib5lO.png"  width="16" style="vertical-align:middle; margin-right:8px;">
           <span style="vertical-align:middle;">
                 PICK-UP CENTRAL DE AUTOBUSES QUERÉTARO
           </span>
        </p>
        </div>

        <div class="footer-col">
          <ul>
            <li><a href="{{ route('rutaReservaciones') }}">MI RESERVA</a></li>
            <li><a href="{{ route('rutaCatalogo') }}">AUTOS</a></li>
            <li><a href="https://viajerocarental.com/empresas">EMPRESAS</a></li>
            <li><a href="{{ route('rutaPoliticas') }}">TÉRMINOS Y CONDICIONES</a></li>
            <li><a href="{{ route('rutaContacto') }}">CONTACTO</a></li>
          </ul>
        </div>

         <div class="footer-col" style="text-align: right;">
          <ul style="list-style: none; padding-left: 0; margin-left: 0;">
           <li><a href="https://viajerocarental.com/blog">BLOG</a></li>
           <li><a href="{{ route('rutaFAQ') }}">F.A.Q</a></li>
           <li><a href="{{ route('rutaPoliticas') }}">AVISO DE PRIVACIDAD</a></li>
           <li><a href="{{ route('rutaPoliticas') }}">POLÍTICA DE LIMPIEZA</a></li>
           <li><a href="{{ route('rutaPoliticas') }}">POLÍTICA DE RENTA</a></li>
         </ul>
        </div>
      </div>

      <div class="footer-pay">
        <img src="{{ asset('img/visa.jpg') }}" alt="Visa">
        <img src="{{ asset('img/mastercard.png') }}" alt="Mastercard">
        <img src="{{ asset('img/america.png') }}" alt="American Express">
        <img src="{{ asset('img/oxxo.png') }}" alt="OXXO">
        <img src="{{ asset('img/pago.png') }}" alt="Mercado Pago">
        <img src="{{ asset('img/paypal.png') }}" alt="PayPal">
      </div>

    </div>
  </div>

  <!-- FOOTER SIMPLE -->
  <div class="footer">
    © {{ date('Y') }} <strong>Viajero Car Rental</strong><br>
    <a href="https://viajerocarental.com">www.viajerocarental.com</a> |
    <a href="mailto:reservaciones@viajerocarental.com">reservaciones@viajerocarental.com</a>
  </div>

</div>

</body>
</html>
