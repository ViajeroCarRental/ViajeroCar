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

@media only screen and (max-width: 600px) {
  .footer-col {
    display: block !important;
    width: 100% !important;
    text-align: center !important;
    margin-bottom: 15px !important;

  }

  .footer-main {
      text-align: left !important;
  }

  .footer-col ul {
    padding: 0 !important;
  }

  .footer-pay img {
    width: 35px !important;
    margin-bottom: 5px !important;
  }
}
@media only screen and (max-width: 600px) {

  /* Imagen grande */
  .auto-container table img {
    width: 90% !important;
    max-width: 280px !important;
    margin: 0 auto 10px auto !important;
    display: block !important;
  }

  /* =========================
     EXTRAS RESPONSIVE
  ========================= */
  .extras-responsive td {
    display: block !important;
    width: 100% !important;
    padding-bottom: 12px !important;
  }

  /* =========================
     AJUSTES GENERALES
  ========================= */
  .content {
    padding: 20px !important;
  }

  .summary-card {
    padding: 14px !important;
  }

}
@media only screen and (max-width: 600px) {

  .auto-container table tr {
    display: flex !important;
    flex-direction: column !important;
  }

  /* TEXTO ARRIBA */
  .auto-container table tr td:last-child {
    order: 1;
    padding: 0 !important;
  }

  /* IMAGEN ABAJO */
  .auto-container table tr td:first-child {
    order: 2;
    text-align: center !important;
    margin-top: 10px !important;
  }

  .auto-container table img {
    width: 85% !important;
    max-width: 260px !important;
  }
}

/* Desktop */
.auto-desktop {
  display: block;
}

/* Mobile oculto */
.auto-mobile {
  display: none;
}

/* En móvil */
@media only screen and (max-width: 600px) {
  .auto-desktop {
    display: none !important;
  }
  .auto-mobile {
    display: block !important;
  }

  /* Reset para la tabla de precios */
  .price-table {
    display: table !important;
    width: 100% !important;
    table-layout: auto !important;
  }

  .price-table tbody {
    display: table-row-group !important;
  }

  .price-table tr {
    display: table-row !important;
  }

  .price-table td {
    display: table-cell !important;
  }

  /* Específico para la fila total */
  .price-table .total-row {
    display: table-row !important;
  }

  .price-table .total-row td {
    display: table-cell !important;
    white-space: nowrap !important;
    float: none !important;
    width: auto !important;
  }

  /* Ajustar la primera celda (label) */
  .price-table .total-label {
    width: 60% !important;
    text-align: left !important;
  }

  /* Ajustar la segunda celda (value) */
  .price-table .total-value {
    width: 40% !important;
    text-align: right !important;
  }
}

  </style>
</head>

<body>

<div class="container">

 <!-- HEADER NUEVO -->
<div class="header" style="background-color: #E50914; padding: 20px 26px; border-radius: 16px 16px 0 0; overflow: hidden;">
    <table width="100%" cellpadding="0" cellspacing="0" border="0" style="border-collapse: collapse;">
        <tr>
            <td align="left" valign="middle">
                 <img src="https://imgur.com/kreor7B.jpg"
                     alt="Viajero Car Rental"
                     width="220px"
                     style="display: block; width: 220pxpx; max-width: 100%; height: auto; border: 0; outline: none; text-decoration: none;">
            </td>

            <td align="right" style="vertical-align: middle; color: #ffffff; font-family: 'Poppins', Arial, sans-serif;">
                <p style=" margin:0; font-size:14px; text-transform:uppercase; font-weight:400; letter-spacing:1.5px; display:inline-block; min-width:220px; text-align:right; padding-right:8px;">
                   @if($tipo === 'linea' || $tipo === 'en_linea')
                      {{ __('Reserva confirmada') }}
                   @else
                      {{ __('RESERVACIÓN') }}
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
        {{ __('¡Gracias!') }}
        <strong>{{ strtoupper(trim(($reservacion->nombre_cliente ?? 'Cliente') . ' ' . ($reservacion->apellidos_cliente ?? ''))) }}</strong>
    </p>

    <p class="lead">
        @if($tipo === 'linea' || $tipo === 'en_linea')
            {{ __('Tu vehículo ya está reservado, el pago ha sido recibido exitosamente.') }}
        @else
            {{ __('Tu vehículo ya está reservado') }}
        @endif
        {{ __(', el siguiente código es tu número de reservación, da') }}
        <a href="{{ route('visor.show', ['id' => $reservacion->id_reservacion]) }}">{{ __('click aquí') }}</a>
        {{ __('para más información.') }}
    </p>

    <p class="lead" style="margin-top:0; text-align: justify; font-size: 16px; line-height: 1.75;">
        {{ __('La siguiente información se calculó con los datos proporcionados en el proceso de reservación, cualquier modificación relacionada con lo que esta reservación describe podría resultar en una variación contra el precio acordado.') }}
    </p>
</div>

   <!-- ===================== RESUMEN ===================== -->
<h2 class="summary-title">{{ __('Resumen de tu reserva') }}</h2>

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
        <td class="left">{{ __('Lugar y fecha') }}</td>
        <td class="right">
            {{ __('RESERVACIÓN') }}<br>
            {{ $reservacion->codigo }}
        </td>
    </tr>
</table>

<div class="summary-line-red"></div>
<!-- ===================== LUGAR Y FECHA ===================== -->
@php
    \Carbon\Carbon::setLocale(app()->getLocale());

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

@php
    // Obtener el idioma actual
    $locale = app()->getLocale();

    // Mapeo de traducciones de sucursales (Español → Inglés)
    $traduccionesLugares = [
        // Querétaro
        'Querétaro Aeropuerto' => 'Querétaro Airport',
        'Querétaro Central de Autobuses' => 'Querétaro Bus Station',
        'Querétaro Oficina Plaza Central Park' => 'Querétaro Central Park Office',
        'Querétaro - Querétaro Aeropuerto' => 'Querétaro - Querétaro Airport',
        'Querétaro - Querétaro Central de Autobuses' => 'Querétaro - Querétaro Bus Station',
        'Querétaro - Oficina Central Park' => 'Querétaro - Central Park Office',

        // Guanajuato
        'Guanajuato - Central de Autobuses León de los Aldamas' => 'Guanajuato - Leon Bus Station',
        'Central de Autobuses León de los Aldamas' => 'Leon Bus Station',
        'Aeropuerto Internacional de Guanajuato, Silao' => 'Guanajuato International Airport, Silao',

        // Aguascalientes
        'Aeropuerto Internacional de Aguascalientes' => 'Aguascalientes International Airport',

        // CDMX
        'Aeropuerto Internacional de Ciudad de México' => 'Mexico City International Airport',
        'Aeropuerto Internacional Felipe Ángeles' => 'Felipe Ángeles International Airport',

        // Durango
        'Aeropuerto Internacional de Durango' => 'Durango International Airport',

        // Guerrero
        'Aeropuerto Internacional de Acapulco' => 'Acapulco International Airport',

        // Jalisco
        'Aeropuerto Internacional Miguel Hidalgo (GDL)' => 'Miguel Hidalgo International Airport (GDL)',
        'Aeropuerto Internacional Puerto Vallarta' => 'Puerto Vallarta International Airport',

        // Monterrey
        'Aeropuerto Internacional de Monterrey' => 'Monterrey International Airport',

        // Morelia
        'Aeropuerto Internacional General Francisco Mujica' => 'General Francisco Mujica International Airport',

        // Oaxaca
        'Aeropuerto Internacional de Oaxaca' => 'Oaxaca International Airport',

        // Puebla
        'Aeropuerto Internacional de Puebla' => 'Puebla International Airport',

        // San Luis Potosí
        'Aeropuerto Internacional de San Luis Potosí' => 'San Luis Potosí International Airport',

        // Tabasco
        'Aeropuerto Internacional Carlos Rovirosa Pérez (VSA)' => 'Carlos Rovirosa Pérez International Airport (VSA)',

        // Tamaulipas
        'Aeropuerto Internacional de Tampico' => 'Tampico International Airport',
        'Tamaulipas - Aeropuerto Internacional de Tampico' => 'Tamaulipas - Tampico International Airport',

        // Toluca
        'Aeropuerto Internacional de Toluca' => 'Toluca International Airport',

        // Veracruz
        'Aeropuerto Internacional de Veracruz' => 'Veracruz International Airport',

        // Zacatecas
        'Aeropuerto Internacional de Zacatecas' => 'Zacatecas International Airport',
    ];

    // Función para traducir el lugar (VERSIÓN FLEXIBLE con coincidencia parcial)
    function traducirLugar($lugar, $traducciones, $locale) {
        if (empty($lugar)) {
            return __('Lugar no especificado');
        }

        // Si es español, devolver el original
        if ($locale === 'es') {
            return $lugar;
        }

        // Buscar traducción exacta
        if (isset($traducciones[$lugar])) {
            return $traducciones[$lugar];
        }

        // 🔥 NUEVO: Buscar coincidencia parcial (más flexible)
        foreach ($traducciones as $es => $en) {
            if (str_contains($lugar, $es)) {
                // Reemplazar la parte que coincide
                return str_replace($es, $en, $lugar);
            }
        }

        // Si no hay traducción, devolver original
        return $lugar;
    }

    // Aplicar traducción
    $lugarRetiroTraducido = traducirLugar($lugarRetiro ?? '', $traduccionesLugares, $locale);
    $lugarEntregaTraducido = traducirLugar($lugarEntrega ?? '', $traduccionesLugares, $locale);
@endphp

<div style="margin-bottom: 20px;">

    <!-- PICK-UP -->
    <table role="presentation" width="100%" style="margin-bottom:16px;">
        <tr>
            <td width="24" style="vertical-align:top;">
                <img src="https://imgur.com/UymwMqF.png" width="20" style="display:block;">
            </td>
            <td style="padding-left:8px;">
                <div style="font-weight:700; font-size:13px; margin-bottom:6px;">
                    {{ __('PICK-UP') }}
                </div>
                <table role="presentation" width="100%" style="font-size:13px;">
                    <tr>
                        <td style="color:#6b7280; width:60px;">{{ __('Date') }}:</td>
                        <td style="font-weight:600;">
                            {{ ucfirst(\Carbon\Carbon::parse($reservacion->fecha_inicio)->translatedFormat('D. d M. Y')) }}
                        </td>
                    </tr>
                    <tr>
                        <td style="color:#6b7280;">{{ __('Time') }}:</td>
                        <td style="font-weight:600;">
                            {{ \Carbon\Carbon::parse($reservacion->hora_retiro)->format('g:i A') }}
                        </td>
                    </tr>
                    <tr>
                        <td style="color:#6b7280;">{{ __('Location') }}:</td>
                        <td style="font-weight:600;">
                            {{ $lugarRetiroTraducido }}
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>

    <!-- DEVOLUCIÓN -->
    <table role="presentation" width="100%">
        <tr>
            <td width="24" style="vertical-align:top;">
                <img src="https://imgur.com/UymwMqF.png" width="20" style="display:block;">
            </td>
            <td style="padding-left:8px;">
                <div style="font-weight:700; font-size:13px; margin-bottom:6px;">
                    {{ __('RETURN') }}
                </div>
                <table role="presentation" width="100%" style="font-size:13px;">
                    <tr>
                        <td style="color:#6b7280; width:60px;">{{ __('Date') }}:</td>
                        <td style="font-weight:600;">
                            {{ ucfirst(\Carbon\Carbon::parse($reservacion->fecha_fin)->translatedFormat('D. d M. Y')) }}
                        </td>
                    </tr>
                    <tr>
                        <td style="color:#6b7280;">{{ __('Time') }}:</td>
                        <td style="font-weight:600;">
                            {{ \Carbon\Carbon::parse($reservacion->hora_entrega)->format('g:i A') }}
                        </td>
                    </tr>
                    <tr>
                        <td style="color:#6b7280;">{{ __('Location') }}:</td>
                        <td style="font-weight:600;">
                            {{ $lugarEntregaTraducido }}
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>

</div>

<!-- ===================== TU AUTO ===================== -->
<div style="font-weight:700; font-size:16px; margin-bottom:12px;">
    {{ __('YOUR CAR') }}
</div>

<div style="border-top:2px solid #e11d48; width:100%; margin-bottom:15px;"></div>


<!-- ===================== DESKTOP ===================== -->
<div class="auto-desktop" style="margin:15px 0;">

<table role="presentation" width="100%">
<tr>

  <!-- IMAGEN IZQUIERDA -->
  <td width="200" style="vertical-align:middle;">
    <img src="{{ $imgCategoria }}" style="width:200px; border-radius:12px;">
  </td>

  <!-- INFO -->
  <td style="padding-left:20px;">

    <!-- TITULO -->
    <div style="font-size:20px; font-weight:800; color:#111827; margin-bottom:4px;">
      {{ $categoria->descripcion }}
    </div>

    <!-- SUBTITULO -->
    <div style="font-size:12px; font-weight:700; color:#6b7280; margin-bottom:10px;">
      {{ strtoupper($categoria->nombre) }} | {{ __('CATEGORY') }} {{ $categoria->codigo }}
    </div>

    <!-- ICONOS -->
    <table role="presentation" style="margin-bottom:10px; white-space:nowrap;">
      <tr>

        <td style="font-size:13px; padding-right:12px; white-space:nowrap;">
          <img src="https://cdn.jsdelivr.net/npm/@fortawesome/fontawesome-free/svgs/solid/user.svg" style="width:14px;">
          <strong>{{ $tuAuto['pax'] }}</strong>
        </td>

        <td style="font-size:13px; padding-right:12px;">
          <img src="https://cdn.jsdelivr.net/npm/@fortawesome/fontawesome-free/svgs/solid/suitcase-rolling.svg" style="width:14px;">
          <strong>{{ $tuAuto['small'] }}</strong>
        </td>

        <td style="font-size:13px; padding-right:12px;">
          <img src="https://cdn.jsdelivr.net/npm/@fortawesome/fontawesome-free/svgs/solid/briefcase.svg" style="width:14px;">
          <strong>{{ $tuAuto['big'] }}</strong>
        </td>

        <td style="font-size:13px; padding-right:12px;">
          <strong>T | {{ $categoria->id_categoria == 9 ? __('Manual') : __('Automatic') }}</strong>
        </td>

        <td style="font-size:13px;">
          <img src="https://cdn.jsdelivr.net/npm/@fortawesome/fontawesome-free/svgs/regular/snowflake.svg" style="width:14px;">
          <span style="font-weight:600;">A/C</span>
        </td>

      </tr>
    </table>

    <!-- TAGS -->
    <div style="margin-bottom:10px;">
      <span style="background:#111827;color:#fff;padding:6px 12px;border-radius:20px;font-size:12px;font-weight:600;">
        CarPlay
      </span>

      <span style="background:#16a34a;color:#fff;padding:6px 12px;border-radius:20px;font-size:12px;font-weight:600;">
        Android Auto
      </span>
    </div>

    <!-- INCLUYE -->
    <div style="font-size:13px; color:#111;">
      ✓ {{ __('Unlimited KM') }} | {{ __('Third-party liability protection (LI)') }}
    </div>

  </td>

</tr>
</table>

</div>


<!-- ===================== MOBILE ===================== -->
<div class="auto-mobile" style="margin:15px 0;">

<table role="presentation" width="100%">

<tr>
<td>

  <!-- TITULO -->
  <div style="font-size:20px; font-weight:800; color:#111827; margin-bottom:4px;">
    {{ $categoria->descripcion }}
  </div>

  <!-- SUBTITULO -->
  <div style="font-size:12px; font-weight:700; color:#6b7280; margin-bottom:10px;">
    {{ strtoupper($categoria->nombre) }} | {{ __('CATEGORY') }} {{ $categoria->codigo }}
  </div>

  <!-- ICONOS -->
  <table role="presentation" style="margin-bottom:10px; white-space:nowrap;">
    <tr>

      <td style="font-size:13px; padding-right:12px;">
        <img src="https://cdn.jsdelivr.net/npm/@fortawesome/fontawesome-free/svgs/solid/user.svg" style="width:14px;">
        <strong>{{ $tuAuto['pax'] }}</strong>
      </td>

      <td style="font-size:13px; padding-right:12px;">
        <img src="https://cdn.jsdelivr.net/npm/@fortawesome/fontawesome-free/svgs/solid/suitcase-rolling.svg" style="width:14px;">
        <strong>{{ $tuAuto['small'] }}</strong>
      </td>

      <td style="font-size:13px; padding-right:12px;">
        <img src="https://cdn.jsdelivr.net/npm/@fortawesome/fontawesome-free/svgs/solid/briefcase.svg" style="width:14px;">
        <strong>{{ $tuAuto['big'] }}</strong>
      </td>

      <td style="font-size:13px; padding-right:12px;">
        <strong>T | {{ $categoria->id_categoria == 9 ? 'Manual' : 'Automática' }}</strong>
      </td>

      <td style="font-size:13px;">
        <img src="https://cdn.jsdelivr.net/npm/@fortawesome/fontawesome-free/svgs/regular/snowflake.svg" style="width:14px;">
        <span style="font-weight:600;">A/C</span>
      </td>

    </tr>
  </table>

  <!-- TAGS -->
  <div style="margin-bottom:10px;">
    <span style="background:#111827;color:#fff;padding:6px 12px;border-radius:20px;font-size:12px;font-weight:600;">
      CarPlay
    </span>

    <span style="background:#16a34a;color:#fff;padding:6px 12px;border-radius:20px;font-size:12px;font-weight:600;">
      Android Auto
    </span>
  </div>

  <!-- INCLUYE -->
  <div style="font-size:13px; color:#111;">
    ✓ {{ __('Unlimited KM') }} | {{ __('Third-party liability protection (LI)') }}
  </div>

</td>
</tr>

<!-- IMAGEN ABAJO SOLO EN MOBILE -->
<tr>
<td align="center" style="padding-top:10px;">
  <img src="{{ $imgCategoria }}" style="width:90%; max-width:260px; border-radius:12px;">
</td>
</tr>

</table>

</div>
<!-- ===================== EXTRAS ===================== -->
<div style="font-weight: 700; font-size: 16px; margin-bottom: 12px;">
    {{ __('EXTRAS') }}
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

                    // ============================================
                    // CONVERSIÓN DE MONEDA PARA EXTRAS
                    // ============================================
                    $tipoCambio = 20; // 1 USD = 20 MXN
                    $locale = app()->getLocale();
                    $moneda = $locale === 'en' ? 'USD' : 'MXN';
                    $simboloMoneda = '$';

                    // Convertir precio del extra si está en inglés
                    $precioExtra = $servicio['precio'];
                    if ($moneda === 'USD') {
                        $precioExtra = $precioExtra / $tipoCambio;
                    }
                    $precioExtraFormateado = number_format($precioExtra, $moneda === 'USD' ? 2 : 0);

                    // Traducir la unidad
                    $unidadTraducida = __($servicio['unidad']);
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
                                    {{ __($servicio['nombre']) }}
                                </div>

                                <div style="font-size:11px; color:#6b7280;">
                                    {{ __($servicio['desc']) }}
                                </div>

                                <div style="font-size:11px; font-weight:600; color:#111;">
                                    {{ $simboloMoneda }}{{ $precioExtraFormateado }} / {{ $unidadTraducida }} {{ $moneda === 'USD' ? 'USD' : 'MXN' }}
                                </div>
                            </td>

                        </tr>
                    </table>

                </td>

                @if(($index + 1) % 3 == 0)
                    <tr>
                        @if(!$loop->last)
                            <tr> @endif
                @endif

            @endforeach

        </tr>
    </table>
</div>

@php
    // ============================================
    // LÓGICA DE CONVERSIÓN DE MONEDA
    // ============================================
    $tipoCambio = 20; // 1 USD = 20 MXN

    // Detectar idioma actual
    $locale = app()->getLocale();
    $moneda = $locale === 'en' ? 'USD' : 'MXN';
    $simboloMoneda = '$'; // El símbolo es el mismo para ambas

    // Función para formatear precio según moneda
    function formatearPrecioEmail($montoMXN, $moneda, $tipoCambio) {
        if ($moneda === 'USD') {
            $monto = $montoMXN / $tipoCambio;
            return number_format($monto, 2);
        }
        return number_format($montoMXN, 2);
    }

    // Aplicar conversión a todas las variables de precio
    $tarifaBaseTotalConvertida = formatearPrecioEmail($tarifaBaseTotal, $moneda, $tipoCambio);
    $opcionesRentaTotalConvertida = formatearPrecioEmail($opcionesRentaTotal, $moneda, $tipoCambio);
    $impuestosConvertidos = formatearPrecioEmail($reservacion->impuestos, $moneda, $tipoCambio);
    $totalConvertido = formatearPrecioEmail($reservacion->total, $moneda, $tipoCambio);
@endphp

<!-- ===================== PRECIOS ===================== -->
<p class="section-title">{{ __('Price details') }}</p>
<div class="summary-line-red"></div>

<table class="price-table" role="presentation">
    <!-- Tarifa base -->
    <tr>
        <td class="price-label">{{ __('Base rate') }}</td>
        <td class="price-value">{{ $simboloMoneda }}{{ $tarifaBaseTotalConvertida }} {{ $moneda }}</td>
    </tr>

    <!-- Opciones de renta -->
    <tr>
        <td class="price-label">{{ __('Rental options') }}</td>
        <td class="price-value">{{ $simboloMoneda }}{{ $opcionesRentaTotalConvertida }} {{ $moneda }}</td>
    </tr>

    <!-- Cargos e IVA -->
    <tr>
        <td class="price-label">{{ __('Charges and Taxes') }}</td>
        <td class="price-value">{{ $simboloMoneda }}{{ $impuestosConvertidos }} {{ $moneda }}</td>
    </tr>

    <!-- Línea divisora gris -->
    <tr class="divider-row">
        <td colspan="2">
            <div class="divider-line"></div>
        </td>
    </tr>

    <!-- TOTAL (en rojo) -->
    <tr class="total-row">
        <td class="total-label"><strong>{{ __('TOTAL') }}</strong></td>
        <td class="total-value"><strong>{{ $simboloMoneda }}{{ $totalConvertido }} {{ $moneda }}</strong></td>
    </tr>
</table>

<!-- ===================== PAGO ===================== -->
<p style="margin-top:15px;">
@if($tipo === 'linea')
    <strong>{{ __('Payment method') }}:</strong> {{ __('PayPal') }}<br>
    <strong>{{ __('Total paid') }}:</strong> {{ $simboloMoneda }}{{ $totalConvertido }} {{ $moneda }}
@else
    <strong>{{ __('Payment method') }}:</strong> {{ __('Counter payment') }}<br>
    @if($locale === 'en')
    @endif
@endif
</p>

</div>{{-- cierre .summary-card --}}

   {{-- Texto y línea roja debajo del detalle de precio --}}
    <p class="price-note" style="text-align: justify; font-size: 14px; line-height: 1.7; color: #111; margin: 18px 0 10px;">
    {{ __('VIAJERO te garantiza el tamaño del vehículo y sus características, más no el modelo específico. Nos comprometemos a entregarte un auto de la categoría reservada, por ejemplo un auto compacto, pudiendo ser cualquiera de las marcas que manejamos en nuestra flota dentro de este grupo.') }}
</p>
    <div class="price-note-line"></div>

        {{-- Bloque: Requisitos y protección LI --}}
          <div class="info-section">

        <!-- ¿Necesitas factura? -->
         <p class="info-section-title" style="font-size: 15px; font-weight: 700; margin: 0 0 8px 0; text-align: justify;">
              {{ __('¿Necesitas factura?') }}
         </p>
         <p class="info-section-paragraph" style="margin: 0 0 18px 0; text-align: justify; font-size: 14px; line-height: 1.7;">
             {{ __('Viajero Car Rental cuenta con lineamientos específicos para la emisión de facturas, los cuales dependen de la fecha de cierre del contrato. Se recomienda realizar la solicitud dentro de los tiempos establecidos para garantizar su correcto procesamiento.') }}
         </p>

        <!-- Política de combustible -->
    <p class="info-section-title" style="font-size: 15px; font-weight: 700; margin: 0 0 8px 0; text-align: justify;">
        {{ __('Política de combustible') }}
    </p>
    <p class="info-section-paragraph" style="margin: 0 0 18px 0; text-align: justify; font-size: 14px; line-height: 1.7;">
        {{ __('El combustible no está incluido en la tarifa. El vehículo deberá devolverse con el tanque lleno; de lo contrario, se aplicará un cargo adicional por servicio de recarga.') }}
    </p>

    <!-- Requisitos para rentar un vehículo -->
    <p class="info-section-title" style="font-size: 15px; font-weight: 700; margin: 0 0 8px 0; text-align: justify;">
        {{ __('Requisitos para rentar un vehículo') }}
    </p>
    <ul class="info-section-list" style="margin: 0 0 12px; padding-left: 18px; text-align: justify;">
        <li style="margin: 0 0 4px; text-align: justify;">
    <strong>{{ __('Tarjeta de crédito:') }}</strong> {{ __('Es indispensable contar con una tarjeta de crédito a nombre del titular, con al menos un año de antigüedad, para garantizar la renta.') }}
</li>

<li style="margin: 0 0 4px; text-align: justify;">
    <strong>{{ __('Edad mínima:') }}</strong> {{ __('21 años. Aplica un cargo adicional para conductores menores de 25 años.') }}
</li>

<li style="margin: 0 0 4px; text-align: justify;">
    <strong>{{ __('Identificación oficial:') }}</strong> {{ __('INE/IFE (mexicanos) o pasaporte (extranjeros), vigente y con fotografía.') }}
</li>

<li style="margin: 0 0 4px; text-align: justify;">
    <strong>{{ __('Licencia de conducir:') }}</strong> {{ __('Vigente y válida.') }}
</li>

<li style="margin: 0 0 4px; text-align: justify;">
    <strong>{{ __('Relevos de responsabilidad:') }}</strong> {{ __('El cliente deberá elegir una opción de protección para el vehículo (100%, 90%, 80% o sin protección).') }}
</li>

<li style="margin: 0 0 4px; text-align: justify;">
    <strong>{{ __('Variaciones:') }}</strong> {{ __('Los requisitos pueden variar. Para más información: 01 (442) 303 26 68 o reservaciones@viajerocar-rental.com') }}
</li>
    </ul>

    <!-- Protección a terceros (LI – Responsabilidad civil) -->
    <p class="info-section-title" style="font-size: 15px; font-weight: 700; margin: 0 0 8px 0; text-align: justify;">
        {{ __('Protección a terceros (LI – Responsabilidad civil)') }}
    </p>
    <p class="info-section-paragraph" style="margin: 0 0 18px 0; text-align: justify; font-size: 14px; line-height: 1.7;">
        {{ __('Esta protección cubre daños a terceros ocasionados durante un accidente, conforme a los mínimos establecidos por la ley. El cliente puede elegir el nivel de cobertura que mejor se adapte a sus necesidades y presupuesto, incluyendo opciones adicionales disponibles en mostrador.') }}
    </p>

    <!-- Depósito en garantía -->
    <p class="info-section-title" style="font-size: 15px; font-weight: 700; margin: 0 0 8px 0; text-align: justify;">
        {{ __('Depósito en garantía') }}
    </p>
    <p class="info-section-paragraph" style="margin: 0 0 18px 0; text-align: justify; font-size: 14px; line-height: 1.7;">
        {{ __('Se realizará una preautorización en la tarjeta de crédito como respaldo de la renta. Este monto no representa un cargo definitivo y será liberado posteriormente, sujeto a la revisión del vehículo.') }}
    </p>

    <!-- Cancelaciones y reembolsos -->
    <p class="info-section-title" style="font-size: 15px; font-weight: 700; margin: 0 0 8px 0; text-align: justify;">
        {{ __('Cancelaciones y reembolsos') }}
    </p>
    <p class="info-section-paragraph" style="margin: 0 0 18px 0; text-align: justify; font-size: 14px; line-height: 1.7;">
        {{ __('Los reembolsos se determinan según el tiempo de anticipación de la cancelación. En cancelaciones tardías o en caso de no presentarse, no aplicará reembolso.') }}
    </p>

    <!-- Política de limpieza -->
    <p class="info-section-title" style="font-size: 15px; font-weight: 700; margin: 0 0 8px 0; text-align: justify;">
        {{ __('Política de limpieza') }}
    </p>
    <p class="info-section-paragraph" style="margin: 0 0 18px 0; text-align: justify; font-size: 14px; line-height: 1.7;">
        {{ __('Los vehículos deben devolverse en condiciones adecuadas. Se aplicará un cargo de $4,000 MXN en caso de suciedad excesiva, olores fuertes o evidencia de consumo de tabaco.') }}
    </p>

    <!-- Infracciones y responsabilidades -->
    <p class="info-section-title" style="font-size: 15px; font-weight: 700; margin: 0 0 8px 0; text-align: justify;">
        {{ __('Infracciones y responsabilidades') }}
    </p>
    <p class="info-section-paragraph" style="margin: 0 0 18px 0; text-align: justify; font-size: 14px; line-height: 1.7;">
        {{ __('El cliente será responsable de todas las infracciones generadas durante el periodo de renta. Podrá aplicarse un cargo administrativo adicional.') }}
    </p>

    <!-- Cargos e impuestos -->
    <p class="info-section-title" style="font-size: 15px; font-weight: 700; margin: 0 0 8px 0; text-align: justify;">
        {{ __('Cargos e impuestos') }}
    </p>
    <p class="info-section-paragraph" style="margin: 0 0 18px 0; text-align: justify; font-size: 14px; line-height: 1.7;">
        {{ __('Todos los servicios están sujetos a IVA y pueden incluir cargos adicionales como aeropuerto, telemetría o devolución en otra ubicación.') }}
    </p>

    <!-- Aviso de privacidad -->
    <p class="info-section-title" style="font-size: 15px; font-weight: 700; margin: 0 0 8px 0; text-align: justify;">
        {{ __('Aviso de privacidad') }}
    </p>
    <p class="info-section-paragraph" style="margin: 0 0 18px 0; text-align: justify; font-size: 14px; line-height: 1.7;">
        {{ __('Los datos personales serán utilizados exclusivamente para fines operativos, administrativos y comerciales relacionados con el servicio. El cliente podrá ejercer sus derechos ARCO en cualquier momento.') }}
    </p>

    <!-- Términos y condiciones -->
    <p class="info-section-title" style="font-size: 15px; font-weight: 700; margin: 0 0 8px 0; text-align: justify;">
        {{ __('Términos y condiciones') }}
    </p>
    <p class="info-section-paragraph" style="margin: 0 0 18px 0; text-align: justify; font-size: 14px; line-height: 1.7;">
        {{ __('El monto final de la renta puede variar según las condiciones al momento de la contratación. Viajero Car Rental se reserva el derecho de negar el servicio en caso de incumplimiento de los requisitos establecidos.') }}
    </p>

</div>

    </div>
      {{-- FOOTER GRANDE TIPO LANDING --}}
  <div class="site-footer">
    <div class="footer-inner">


       <div class="footer-social" style="display:flex; align-items:center; gap:12px;">

  <a href="https://wa.me/524423032668">
    <img src="https://cdn-icons-png.flaticon.com/512/733/733585.png"
         alt="WhatsApp"
         style="width:22px; height:22px; object-fit:contain;">
  </a>

  <a href="https://www.facebook.com/viajerocarental">
    <img src="https://viajerocar-production.up.railway.app/img/facebook.png"
         alt="Facebook"
         style="width:22px; height:22px; object-fit:contain;">
  </a>

  <a href="https://www.instagram.com/viajerocarental">
    <img src="https://viajerocar-production.up.railway.app/img/instagram.png"
         alt="Instagram"
         style="width:22px; height:22px; object-fit:contain;">
  </a>

  <a href="https://www.tiktok.com/@viajerocarental">
    <img src="https://viajerocar-production.up.railway.app/img/tiktok.png"
         alt="TikTok"
         style="width:22px; height:22px; object-fit:contain;">
  </a>


        <div class="footer-logo-word" style="margin-left: auto; flex-shrink: 0;">
          <img src="https://viajerocar-production.up.railway.app/img/LogoR.png" width="140" style="display: block; width: 140px; height: auto; border: 0;">
        </div>
       </div>
       </div>

      <div class="footer-sep"></div>

      <div class="footer-main">
        <div class="footer-col">
         <p style="margin:5px 0;">
            <img src="https://i.imgur.com/l9Ib5lO.png"  width="16" style="vertical-align:middle; margin-right:8px;">
            <span style="vertical-align:middle;">
                {{ __('OFICINA CENTRAL PARK, QUERÉTARO') }}
          </span>
         </p>

         <p style="margin:5px 0;">
           <img src="https://i.imgur.com/l9Ib5lO.png" width="16" style="vertical-align:middle; margin-right:8px;">
            <span style="vertical-align:middle;">
                 {{ __('PICK-UP AEROPUERTO DE QUERÉTARO') }}
            </span>
         </p>

        <p style="margin:5px 0;">
          <img src="https://i.imgur.com/l9Ib5lO.png"  width="16" style="vertical-align:middle; margin-right:8px;">
           <span style="vertical-align:middle;">
                  {{ __('PICK-UP CENTRAL DE AUTOBUSES QUERÉTARO') }}
           </span>
        </p>
        </div>

        <div class="footer-col">
          <ul>
            <li><a href="{{ route('rutaReservaciones') }}">{{ __('MI RESERVA') }}</a></li>
            <li><a href="{{ route('rutaCatalogo') }}">{{ __('AUTOS') }}</a></li>
            <li><a href="https://viajerocarental.com/empresas">{{ __('EMPRESAS') }}</a></li>
            <li><a href="{{ route('rutaPoliticas') }}">{{ __('TÉRMINOS Y CONDICIONES') }}</a></li>
            <li><a href="{{ route('rutaContacto') }}">{{ __('CONTACTO') }}</a></li>
          </ul>
        </div>

         <div class="footer-col" style="text-align: right;">
          <ul style="list-style: none; padding-left: 0; margin-left: 0;">
           <li><a href="https://viajerocarental.com/blog">{{ __('BLOG') }}</a></li>
           <li><a href="{{ route('rutaFAQ') }}">{{ __('F.A.Q') }}</a></li>
           <li><a href="{{ route('rutaPoliticas') }}">{{ __('AVISO DE PRIVACIDAD') }}</a></li>
           <li><a href="{{ route('rutaPoliticas') }}">{{ __('POLÍTICA DE LIMPIEZA') }}</a></li>
           <li><a href="{{ route('rutaPoliticas') }}">{{ __('POLÍTICA DE RENTA') }}</a></li>
         </ul>
        </div>
      </div>

    <div class="footer-pay" style="padding-top:10px;">
        <img src="https://viajerocar-production.up.railway.app/img/visa.jpg" alt="Visa" width="40" style="display:inline-block; width:40px; height:auto; border:0; margin-right:8px;">
        <img src="https://viajerocar-production.up.railway.app/img/mastercard.png" alt="Mastercard" width="40" style="display:inline-block; width:40px; height:auto; border:0; margin-right:8px;">
        <img src="https://viajerocar-production.up.railway.app/img/america.png" alt="American Express" width="40" style="display:inline-block; width:40px; height:auto; border:0; margin-right:8px;">
        <img src="https://viajerocar-production.up.railway.app/img/oxxo.png" alt="OXXO" width="40" style="display:inline-block; width:40px; height:auto; border:0; margin-right:8px;">
        <img src="https://viajerocar-production.up.railway.app/img/pago.png" alt="Mercado Pago" width="40" style="display:inline-block; width:40px; height:auto; border:0; margin-right:8px;">
        <img src="https://viajerocar-production.up.railway.app/img/paypal.png" alt="PayPal" width="40" style="display:inline-block; width:40px; height:auto; border:0;">
     </div>
    </div>
  </div>

  <!-- FOOTER SIMPLE -->
  <div class="footer">
    © {{ date('Y') }} <strong>{{ __('Viajero Car Rental') }}</strong><br>
    <a href="https://viajerocarental.com">www.viajerocarental.com</a> |
    <a href="mailto:reservaciones@viajerocarental.com">reservaciones@viajerocarental.com</a>
  </div>

</div>

</body>
</html>
