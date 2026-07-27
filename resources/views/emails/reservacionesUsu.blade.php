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

    .header {
      background: #E50914;
      padding: 22px 26px;
      color: #fff;
    }

    .header-table {
      width: 100%;
      border-collapse: collapse;
    }

    .resv-box {
      text-align: right;
      vertical-align: middle;
      font-weight: 700;
      letter-spacing: .5px;
    }

    .resv-box .label {
      font-size: 18px;
      text-transform: uppercase;
      opacity: .95;
      margin: 0;
    }

    .resv-box .code {
      font-size: 22px;
      margin: 6px 0 0 0;
    }

    .hero {
      padding: 30px 40px 10px;
    }

    .hero .thanks {
      font-size: 22px;
      font-weight: 700;
      margin: 0 0 14px 0;
    }

    .hero .lead {
      font-size: 16px;
      line-height: 1.75;
      margin: 0 0 18px 0;
    }

    .hero a {
      color: #E50914;
      text-decoration: underline;
      font-weight: 600;
    }

    .content {
      padding: 35px 40px 40px;
      font-size: 15px;
      line-height: 1.7;
    }

    .divider {
      height: 1px;
      background: #e2e8f0;
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

    .summary-title {
      font-size: 22px;
      font-weight: 700;
      margin: 10px 0 14px;
      color: #111;
    }

    .summary-card {
      border: 1px solid #1f2937;
      border-radius: 16px;
      padding: 18px 18px;
    }

    .summary-top {
      width: 100%;
      border-collapse: collapse;
      margin-bottom: 10px;
    }

    .summary-top .left {
      font-size: 18px;
      font-weight: 700;
    }

    .summary-top .right {
      text-align: right;
      font-weight: 700;
      letter-spacing: .4px;
    }

    .summary-line {
      height: 1px;
      background: #1f2937;
      margin: 12px 0 14px;
    }

    .section-title {
      font-size: 16px;
      font-weight: 700;
      margin: 0 0 10px 0;
    }

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

    .price-table .divider-row td {
      padding: 0;
    }

    .price-table .divider-line {
      border-top: 1px solid #e5e7eb;
      margin: 8px 0;
    }

    .price-table .total-row td {
      padding-top: 12px;
      padding-bottom: 4px;
    }

    .price-table .total-label {
      font-weight: 800;
      font-size: 16px;
      color: #E50914;
      text-align: left;
    }

    .price-table .total-value {
      font-weight: 800;
      font-size: 18px;
      color: #E50914;
      text-align: right;
    }

    .price-note {
      font-size: 14px;
      line-height: 1.7;
      color: #111;
      margin: 18px 0 10px;
    }

    .price-note-line {
      height: 3px;
      background: #E50914;
      margin-top: 10px;
    }

    .info-section {
      font-size: 14px;
      line-height: 1.7;
      color: #111;
      margin-top: 18px;
    }

    .info-section-title {
      font-size: 15px;
      font-weight: 700;
      margin: 0 0 6px;
    }

    .info-section-list {
      margin: 0 0 12px;
      padding-left: 18px;
    }

    .info-section-list li {
      margin: 0 0 4px;
    }

    .info-section-paragraph {
      margin: 0 0 16px;
    }

    .auto-title {
      font-size: 22px;
      font-weight: 800;
      margin: 0;
      color: #111;
    }

    .auto-subtitle {
      font-size: 13px;
      font-weight: 700;
      letter-spacing: .6px;
      text-transform: uppercase;
      opacity: .85;
      margin: 4px 0 10px;
    }

    .auto-specs {
      font-size: 13px;
      line-height: 1.6;
      color: #111;
    }

    .auto-specs .muted { opacity: .85; }

    .auto-includes {
      font-size: 13px;
      margin-top: 10px;
      color: #111;
      opacity: .9;
    }

    .site-footer {
      margin-top: 40px;
      background: #e5e7eb;
      padding: 18px 30px 20px;
      font-size: 13px;
      color: #111827;
    }

    .footer-inner {
      max-width: 700px;
      margin: 0 auto;
    }

    .footer-table {
      width: 100%;
      border-collapse: collapse;
    }

    .footer-table td {
      vertical-align: middle;
      padding: 4px 0;
    }

    .footer-social {
      font-size: 0;
      white-space: nowrap;
    }

    .footer-social a {
      display: inline-block;
      margin-right: 14px;
    }

    .footer-social img {
      max-height: 20px;
      display: block;
    }

    .footer-sep {
      height: 1px;
      background: #111827;
      margin: 8px 0 14px;
    }

    .footer-main-table {
      width: 100%;
      border-collapse: collapse;
    }

    .footer-main-table td {
      vertical-align: top;
      padding: 5px 10px 5px 0;
      font-size: 12px;
    }

    .footer-main-table ul {
      list-style: none;
      padding: 0;
      margin: 0;
    }

    .footer-main-table li {
      margin-bottom: 4px;
    }

    .footer-main-table a {
      color: #111827;
      text-decoration: none;
      font-size: 12px;
      text-transform: uppercase;
    }

    .footer-pay {
      border-top: 1px solid #111827;
      padding-top: 10px;
      text-align: center;
    }

    .footer-pay img {
      max-height: 22px;
      display: inline-block;
      margin-right: 10px;
    }

    .ubicacion-text {
      font-size: 12px;
      margin: 0 0 6px;
    }

    .location-block {
      margin-bottom: 16px;
    }

    .location-block:last-child {
      margin-bottom: 0;
    }

    .location-title {
      font-weight: 700;
      font-size: 13px;
      color: #111;
      margin-bottom: 4px;
    }

    .location-datetime {
      font-size: 14px;
      font-weight: 600;
      color: #111;
      margin-bottom: 2px;
    }

    .location-place {
      font-size: 13px;
      color: #6b7280;
    }

    /* === IMAGEN DEL AUTO MÁS GRANDE === */
    .auto-img-cell img {
      width: 100% !important;
      max-width: 320px !important;
      min-width: 240px !important;
      height: auto !important;
      display: block !important;
      border: none !important;
      margin: 0 auto !important;
    }

    /* === RESPONSIVO MÓVIL === */
    @media only screen and (max-width: 600px) {
      .container {
        margin: 0 !important;
        border-radius: 0 !important;
      }

      .content, .hero {
        padding-left: 20px !important;
        padding-right: 20px !important;
      }

      .summary-card {
        padding: 14px !important;
      }

      .header {
        padding: 16px 16px !important;
      }

      table.header-table td.hdr-logo img {
        max-width: 150px !important;
      }

      table.header-table td.hdr-resv .label {
        font-size: 12px !important;
      }

      table.header-table td.hdr-resv .code {
        font-size: 16px !important;
        white-space: nowrap !important;
      }

      /* AUTO: imagen arriba, texto abajo - IMAGEN MÁS GRANDE */
      table.auto-table,
      table.auto-table tbody,
      table.auto-table tr {
        display: block !important;
        width: 100% !important;
      }

      table.auto-table td.auto-img-cell,
      table.auto-table td.auto-info-cell {
        display: block !important;
        width: 100% !important;
        max-width: 100% !important;
        box-sizing: border-box !important;
        padding: 0 !important;
      }

      table.auto-table td.auto-img-cell {
        padding: 0 0 16px 0 !important;
        text-align: center !important;
      }

      table.auto-table td.auto-img-cell img {
        width: 100% !important;
        max-width: 280px !important;
        min-width: 200px !important;
        height: auto !important;
        margin: 0 auto !important;
        display: block !important;
      }

      table.auto-table td.auto-info-cell {
        text-align: left !important;
      }

      .auto-title {
        font-size: 20px !important;
      }

      /* PRICE TABLE */
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

      .price-table .total-row {
        display: table-row !important;
      }

      .price-table .total-row td {
        display: table-cell !important;
        white-space: nowrap !important;
        float: none !important;
        width: auto !important;
      }

      .price-table .total-label {
        width: 60% !important;
        text-align: left !important;
      }

      .price-table .total-value {
        width: 40% !important;
        text-align: right !important;
      }

      /* === FOOTER MÓVIL === */
      .site-footer {
        padding: 16px 16px 18px !important;
        margin-top: 24px !important;
      }

      .footer-social img {
        max-height: 22px !important;
      }

      .footer-social a {
        margin-right: 10px !important;
      }

      table.footer-table td.foot-logo-cell img {
        height: 20px !important;
        max-height: 20px !important;
      }

      table.footer-main-table,
      table.footer-main-table tbody,
      table.footer-main-table tr {
        display: block !important;
        width: 100% !important;
      }

      table.footer-main-table td.foot-col {
        display: block !important;
        width: 100% !important;
        max-width: 100% !important;
        box-sizing: border-box !important;
        padding: 0 0 14px 0 !important;
        text-align: center !important;
      }

      .footer-pay img {
        max-height: 16px !important;
        width: auto !important;
        margin: 0 5px 0 0 !important;
      }
    }
  </style>
</head>

<body>
  <div class="container">

    <!-- HEADER -->
    <div class="header">
      <table class="header-table" role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="width:100%; table-layout:fixed; border-collapse:collapse;">
        <tr>
          <td class="hdr-logo" width="55%" style="width:55%; vertical-align:middle; padding:0;">
            <img src="https://res.cloudinary.com/xpcjjkal/image/upload/v1783355566/Logo3_p1kxin.jpg" alt="Viajero Car Rental" style="width:100%; max-width:210px; height:auto; display:block; border:none;">
          </td>
          <td class="hdr-resv" width="45%" style="width:45%; vertical-align:middle; padding:0 0 0 10px; text-align:right;">
            <p class="label" style="font-size:15px; text-transform:uppercase; opacity:.95; margin:0; font-weight:700; letter-spacing:.5px;">Reservación</p>
            <p class="code" style="font-size:20px; margin:4px 0 0 0; font-weight:700; letter-spacing:.5px;">{{ $reservacion->codigo }}</p>
          </td>
        </tr>
      </table>
    </div>

    <div class="content">

      @php
        $serviciosDisponibles = [
          ['nombre' => 'Silla de bebé', 'desc' => 'Baby safety seat.', 'precio' => 150, 'unidad' => 'por día'],
          ['nombre' => 'Gasolina Prepago', 'desc' => 'Full tank based on vehicle category.', 'precio' => 1200, 'unidad' => 'por tanque'],
          ['nombre' => 'Conductor adicional', 'desc' => 'Add an extra driver.', 'precio' => 150, 'unidad' => 'por día'],
        ];
      @endphp

      <!-- MENSAJE -->
      <div class="hero">
        <p class="thanks">
          ¡Gracias! <strong>{{ strtoupper(trim(($reservacion->nombre_cliente ?? 'Cliente') . ' ' . ($reservacion->apellidos_cliente ?? ''))) }}</strong>
        </p>
        <p class="lead">
          @if($tipo === 'linea' || $tipo === 'en_linea')
            {{ __('Tu vehículo ya está reservado, el pago ha sido recibido exitosamente.') }}
          @else
            {{ __('Tu vehículo ya está reservado') }}
          @endif
          {{ __(', el siguiente código es tu número de reservación, da') }}
          <a href="{{ route('visor.show', ['id' => $reservacion->id_reservacion]) }}">click aquí</a>
          {{ __('para más información.') }}
        </p>
        <p class="lead" style="margin-top:0;">
          La siguiente información se calculó con los datos proporcionados en el proceso de reservación,
          cualquier modificación relacionada con lo que esta reservación describe podría resultar en una variación contra el precio acordado.
        </p>
      </div>

      <!-- RESUMEN -->
      <h2 class="summary-title">Resumen de tu reserva</h2>

      <div class="summary-card">

        @php
          // Incluir las horas de retiro y entrega para calcular bien los días.
          $fechaInicio = \Carbon\Carbon::parse(
              $reservacion->fecha_inicio . ' ' . ($reservacion->hora_retiro ?? '00:00:00')
          );
          $fechaFin = \Carbon\Carbon::parse(
              $reservacion->fecha_fin . ' ' . ($reservacion->hora_entrega ?? '00:00:00')
          );
          // Días con tolerancia de 1 hora (misma fórmula que en todo el sistema):
          // si el exceso sobre las 24h es de MÁS de 1 hora, suma un día.
          $horasCorreo = $fechaInicio->lt($fechaFin) ? $fechaInicio->diffInHours($fechaFin) : 0;
          $diasBaseCorreo = intdiv($horasCorreo, 24);
          $horasExtraCorreo = $horasCorreo % 24;
          $diasCorreo = ($horasExtraCorreo > 1) ? $diasBaseCorreo + 1 : max(1, $diasBaseCorreo);
          $tarifaBaseDia = (float) ($reservacion->tarifa_base ?? 0);
          $tarifaBaseTotal = round($tarifaBaseDia * $diasCorreo, 2);
          $extrasIds = collect($extrasReserva)->pluck('id_servicio')->toArray();

          $locale = app()->getLocale();
          $traduccionesLugares = [
            'Querétaro Aeropuerto' => 'Querétaro Airport',
            'Querétaro Central de Autobuses' => 'Querétaro Bus Station',
            'Querétaro Oficina Plaza Central Park' => 'Querétaro Central Park Office',
            'Querétaro - Querétaro Aeropuerto' => 'Querétaro - Querétaro Airport',
            'Querétaro - Querétaro Central de Autobuses' => 'Querétaro - Querétaro Bus Station',
            'Querétaro - Oficina Central Park' => 'Querétaro - Central Park Office',
            'Guanajuato - Central de Autobuses León de los Aldamas' => 'Guanajuato - Leon Bus Station',
            'Central de Autobuses León de los Aldamas' => 'Leon Bus Station',
            'Aeropuerto Internacional de Guanajuato, Silao' => 'Guanajuato International Airport, Silao',
            'Aeropuerto Internacional de Aguascalientes' => 'Aguascalientes International Airport',
            'Aeropuerto Internacional de Ciudad de México' => 'Mexico City International Airport',
            'Aeropuerto Internacional Felipe Ángeles' => 'Felipe Ángeles International Airport',
            'Aeropuerto Internacional de Durango' => 'Durango International Airport',
            'Aeropuerto Internacional de Acapulco' => 'Acapulco International Airport',
            'Aeropuerto Internacional Miguel Hidalgo (GDL)' => 'Miguel Hidalgo International Airport (GDL)',
            'Aeropuerto Internacional Puerto Vallarta' => 'Puerto Vallarta International Airport',
            'Aeropuerto Internacional de Monterrey' => 'Monterrey International Airport',
            'Aeropuerto Internacional General Francisco Mujica' => 'General Francisco Mujica International Airport',
            'Aeropuerto Internacional de Oaxaca' => 'Oaxaca International Airport',
            'Aeropuerto Internacional de Puebla' => 'Puebla International Airport',
            'Aeropuerto Internacional de San Luis Potosí' => 'San Luis Potosí International Airport',
            'Aeropuerto Internacional Carlos Rovirosa Pérez (VSA)' => 'Carlos Rovirosa Pérez International Airport (VSA)',
            'Aeropuerto Internacional de Tampico' => 'Tampico International Airport',
            'Tamaulipas - Aeropuerto Internacional de Tampico' => 'Tamaulipas - Tampico International Airport',
            'Aeropuerto Internacional de Toluca' => 'Toluca International Airport',
            'Aeropuerto Internacional de Veracruz' => 'Veracruz International Airport',
            'Aeropuerto Internacional de Zacatecas' => 'Zacatecas International Airport',
          ];

          function traducirLugar($lugar, $traducciones, $locale) {
            if (empty($lugar)) return 'Lugar no especificado';
            if ($locale === 'es') return $lugar;
            if (isset($traducciones[$lugar])) return $traducciones[$lugar];
            foreach ($traducciones as $es => $en) {
              if (str_contains($lugar, $es)) {
                return str_replace($es, $en, $lugar);
              }
            }
            return $lugar;
          }

          $lugarRetiroTraducido = traducirLugar($lugarRetiro ?? '', $traduccionesLugares, $locale);
          $lugarEntregaTraducido = traducirLugar($lugarEntrega ?? '', $traduccionesLugares, $locale);
        @endphp

        <table class="summary-top" role="presentation">
          <tr>
            <td class="left">Lugar y fecha</td>
            <td class="right">
              RESERVACIÓN<br>
              {{ $reservacion->codigo }}
            </td>
          </tr>
        </table>

        <div class="summary-line"></div>

        <!-- PICK-UP -->
        <div class="location-block">
          <div class="location-title">PICK-UP</div>

          <table role="presentation" cellpadding="0" cellspacing="0" border="0" style="border-collapse:collapse; width:100%;">
            <tr>
              <td style="width:24px; vertical-align:middle; padding:2px 8px 2px 0;">
                <img src="https://api.iconify.design/lucide/calendar.svg?color=%23111111&width=16&height=16"
                     width="16" height="16" alt="" style="display:block; border:0;">
              </td>
              <td style="vertical-align:middle; padding:2px 0; font-size:14px; font-weight:600; color:#111;">
                Fecha: <strong>{{ ucfirst(\Carbon\Carbon::parse($reservacion->fecha_inicio)->translatedFormat('D. d M. Y')) }}</strong>
              </td>
            </tr>
            <tr>
              <td style="width:24px; vertical-align:middle; padding:2px 8px 2px 0;">
                <img src="https://api.iconify.design/lucide/clock.svg?color=%23111111&width=16&height=16"
                     width="16" height="16" alt="" style="display:block; border:0;">
              </td>
              <td style="vertical-align:middle; padding:2px 0; font-size:14px; font-weight:600; color:#111;">
                Hora: <strong>{{ \Carbon\Carbon::parse($reservacion->hora_retiro)->format('g:i A') }}</strong>
              </td>
            </tr>
            <tr>
              <td style="width:24px; vertical-align:top; padding:2px 8px 2px 0;">
                <img src="https://api.iconify.design/lucide/map-pin.svg?color=%236b7280&width=16&height=16"
                     width="16" height="16" alt="" style="display:block; border:0;">
              </td>
              <td style="vertical-align:top; padding:2px 0; font-size:13px; color:#6b7280;">
                Location: <strong>{{ $lugarRetiroTraducido }}</strong>
              </td>
            </tr>
          </table>
        </div>

        <!-- RETURN -->
        <div class="location-block">
          <div class="location-title">RETURN</div>

          <table role="presentation" cellpadding="0" cellspacing="0" border="0" style="border-collapse:collapse; width:100%;">
            <tr>
              <td style="width:24px; vertical-align:middle; padding:2px 8px 2px 0;">
                <img src="https://api.iconify.design/lucide/calendar.svg?color=%23111111&width=16&height=16"
                     width="16" height="16" alt="" style="display:block; border:0;">
              </td>
              <td style="vertical-align:middle; padding:2px 0; font-size:14px; font-weight:600; color:#111;">
                Fecha: <strong>{{ ucfirst(\Carbon\Carbon::parse($reservacion->fecha_fin)->translatedFormat('D. d M. Y')) }}</strong>
              </td>
            </tr>
            <tr>
              <td style="width:24px; vertical-align:middle; padding:2px 8px 2px 0;">
                <img src="https://api.iconify.design/lucide/clock.svg?color=%23111111&width=16&height=16"
                     width="16" height="16" alt="" style="display:block; border:0;">
              </td>
              <td style="vertical-align:middle; padding:2px 0; font-size:14px; font-weight:600; color:#111;">
                Hora: <strong>{{ \Carbon\Carbon::parse($reservacion->hora_entrega)->format('g:i A') }}</strong>
              </td>
            </tr>
            <tr>
              <td style="width:24px; vertical-align:top; padding:2px 8px 2px 0;">
                <img src="https://api.iconify.design/lucide/map-pin.svg?color=%236b7280&width=16&height=16"
                     width="16" height="16" alt="" style="display:block; border:0;">
              </td>
              <td style="vertical-align:top; padding:2px 0; font-size:13px; color:#6b7280;">
                Location: <strong>{{ $lugarEntregaTraducido }}</strong>
              </td>
            </tr>
          </table>
        </div>

        <div class="summary-line"></div>
<!-- ============================================ -->
<!-- === TU AUTO - IMAGEN MÁS GRANDE === -->
<!-- ============================================ -->
<p class="section-title">Tu Auto</p>

<table role="presentation" class="auto-table" width="100%" cellpadding="0" cellspacing="0" border="0" style="width:100%; border-collapse:collapse;">
  <tr>
    <td class="auto-img-cell" width="45%" style="width:45%; vertical-align:middle; padding:10px 0;">
      @php
        $rutaImgUsr = ltrim(parse_url($imgCategoria, PHP_URL_PATH) ?? '', '/');
        $rutaImgUsrLocal = public_path($rutaImgUsr);
      @endphp
      @if($rutaImgUsr && file_exists($rutaImgUsrLocal))
        <img src="{{ $message->embed($rutaImgUsrLocal) }}" alt="Vehículo" width="320" style="width:100%; max-width:320px; min-width:240px; height:auto; display:block; border:none; margin:0 auto;">
      @else
        <img src="{{ $imgCategoria }}" alt="Vehículo" width="320" style="width:100%; max-width:320px; min-width:240px; height:auto; display:block; border:none; margin:0 auto;">
      @endif
    </td>
    <td class="auto-info-cell" style="width:55%; vertical-align:middle; padding:10px 0 10px 10px;">
      <p class="auto-title">{{ $tuAuto['titulo'] ?? ($categoria->descripcion ?? '-') }}</p>
      <p class="auto-subtitle">{{ $tuAuto['subtitulo'] ?? 'CATEGORÍA ' . ($categoria->codigo ?? '-') }}</p>

      <!-- SPECS EN UNA SOLA FILA -->
      <table role="presentation" cellpadding="0" cellspacing="0" border="0" style="border-collapse:collapse; margin:8px 0 10px;">
        <tr>
          <td style="vertical-align:middle; padding:0 4px 0 0;">
            <img src="https://api.iconify.design/lucide/user.svg?color=%23111111&width=14&height=14"
                 width="14" height="14" alt="" style="display:block; border:0;">
          </td>
          <td style="vertical-align:middle; padding:0 12px 0 0; font-size:13px; font-weight:700; color:#111; white-space:nowrap;">
            {{ $tuAuto['pax'] ?? 5 }}
          </td>

          <td style="vertical-align:middle; padding:0 4px 0 0;">
            <img src="https://api.iconify.design/lucide/briefcase.svg?color=%23111111&width=14&height=14"
                 width="14" height="14" alt="" style="display:block; border:0;">
          </td>
          <td style="vertical-align:middle; padding:0 12px 0 0; font-size:13px; font-weight:700; color:#111; white-space:nowrap;">
            {{ $tuAuto['small'] ?? 2 }}
          </td>

          <td style="vertical-align:middle; padding:0 4px 0 0;">
            <img src="https://api.iconify.design/lucide/luggage.svg?color=%23111111&width=14&height=14"
                 width="14" height="14" alt="" style="display:block; border:0;">
          </td>
          <td style="vertical-align:middle; padding:0 12px 0 0; font-size:13px; font-weight:700; color:#111; white-space:nowrap;">
            {{ $tuAuto['big'] ?? 1 }}
          </td>

          <td style="vertical-align:middle; padding:0 4px 0 0;">
            <img src="https://api.iconify.design/lucide/settings-2.svg?color=%23111111&width=14&height=14"
                 width="14" height="14" alt="" style="display:block; border:0;">
          </td>
          <td style="vertical-align:middle; padding:0 12px 0 0; font-size:13px; color:#111; white-space:nowrap;">
            {{ $tuAuto['transmision'] ?? 'Automática' }}
            </td>

          <td style="vertical-align:middle; padding:0 4px 0 0;">
            <img src="https://api.iconify.design/lucide/snowflake.svg?color=%23111111&width=14&height=14"
                 width="14" height="14" alt="" style="display:block; border:0;">
          </td>
          <td style="vertical-align:middle; padding:0; font-size:13px; color:#111; white-space:nowrap;">
            A/C
          </td>
        </tr>
      </table>

      <!-- BADGES CARPLAY / ANDROID AUTO -->
      <table role="presentation" cellpadding="0" cellspacing="0" border="0" style="border-collapse:collapse; margin:0 0 10px;">
        <tr>
          <td style="padding:0 8px 0 0;">
            <table role="presentation" cellpadding="0" cellspacing="0" border="0"
                   style="border-collapse:separate; background:#111827; border-radius:20px;">
              <tr>
                <td style="padding:5px 10px 5px 10px; vertical-align:middle;">
                  <img src="https://api.iconify.design/lucide/car.svg?color=%23ffffff&width=13&height=13"
                       width="13" height="13" alt="" style="display:block; border:0;">
                </td>
                <td style="padding:5px 12px 5px 0; vertical-align:middle; font-size:12px; font-weight:700; color:#ffffff; white-space:nowrap;">
                  CarPlay
                </td>
              </tr>
            </table>
          </td>
          <td style="padding:0;">
            <table role="presentation" cellpadding="0" cellspacing="0" border="0"
                   style="border-collapse:separate; background:#22c55e; border-radius:20px;">
              <tr>
                <td style="padding:5px 10px 5px 10px; vertical-align:middle;">
                  <img src="https://api.iconify.design/lucide/bot.svg?color=%23ffffff&width=13&height=13"
                       width="13" height="13" alt="" style="display:block; border:0;">
                </td>
                <td style="padding:5px 12px 5px 0; vertical-align:middle; font-size:12px; font-weight:700; color:#ffffff; white-space:nowrap;">
                  Android Auto
                </td>
              </tr>
            </table>
          </td>
        </tr>
      </table>

      <!-- INCLUYE -->
      <table role="presentation" cellpadding="0" cellspacing="0" border="0" style="border-collapse:collapse;">
        <tr>
          <td style="vertical-align:middle; padding:0 6px 0 0;">
            <img src="https://api.iconify.design/lucide/check.svg?color=%23111111&width=14&height=14"
                 width="14" height="14" alt="" style="display:block; border:0;">
          </td>
          <td style="vertical-align:middle; font-size:12px; font-weight:700; color:#111; letter-spacing:.3px;">
            {{ $tuAuto['incluye'] ?? 'KM ILIMITADOS | PROTECCIÓN A TERCEROS (LI)' }}
          </td>
        </tr>
      </table>
    </td>
  </tr>
</table>

<div class="summary-line"></div>

        <!-- EXTRAS -->
        <p class="section-title">Extras</p>
        <div class="summary-line"></div>

        @php
          $tipoCambio    = 20;
          $moneda        = $locale === 'en' ? 'USD' : 'MXN';
          $simboloMoneda = '$';

          $convertir = function ($mxn) use ($moneda, $tipoCambio) {
              return $moneda === 'USD'
                  ? number_format((float) $mxn / $tipoCambio, 2)
                  : number_format((float) $mxn, 2);
          };

          $filasExtras = [];

          foreach (($extrasReserva ?? []) as $ex) {
            $idServ = (int)   ($ex->id_servicio ?? 0);
            $cant   = (float) ($ex->cantidad ?? 1);
            $total  = (float) ($ex->total ?? 0);
            $cobro  = strtolower((string) ($ex->tipo_cobro ?? ''));
            // El campo real que trae el controlador es 'precio_unitario', no 'precio'.
            $precio = (float) ($ex->precio_unitario ?? 0);

            if ($idServ === 11) {
              $unidad = __('Return at a different branch');
            } elseif ($idServ === 1 || $cobro === 'por_tanque') {
              $unidad = number_format($cant, 0) . ' L x $' . $convertir($precio) . ' ' . $moneda;
            } elseif ($cobro === 'por_evento') {
              $unidad = number_format($cant, 0) . ' x $' . $convertir($precio) . ' ' . $moneda;
            } else {
              $unidad = number_format($cant, 0) . ' x $' . $convertir($precio) . ' ' . $moneda
                      . ' x ' . $diasCorreo . ' ' . __('day(s)');
            }

            $filasExtras[] = [
              'nombre' => $ex->nombre ?? 'Servicio',
              'desc'   => $ex->descripcion ?? '',
              'unidad' => $unidad,
              'total'  => $total,
            ];
          }
        @endphp

        @if(count($filasExtras) === 0)
          <div style="font-size:14px; opacity:.85; margin:15px 0;">{{ __('No add-ons selected') }}</div>
        @else
          <table role="presentation" class="extras-table" cellpadding="0" cellspacing="0" border="0" style="width:100%; border-collapse:collapse; margin:15px 0;">
            @foreach($filasExtras as $f)
              <tr>
                <td class="ex-check-cell" width="34" style="width:34px; vertical-align:top; padding:10px 10px 10px 0;">
                  <table role="presentation" cellpadding="0" cellspacing="0" border="0"
                         style="border-collapse:collapse; width:20px; height:20px; border:2px solid #111; border-radius:3px;">
                    <tr>
                      <td align="center" valign="middle" style="width:20px; height:20px; text-align:center; padding:0;">
                        <img src="https://api.iconify.design/lucide/check.svg?color=%23E50914&width=14&height=14"
                             width="14" height="14" alt="" style="display:inline-block; border:0; vertical-align:middle;">
                      </td>
                    </tr>
                  </table>
                </td>
                <td class="ex-info-cell" style="vertical-align:top; padding:10px 10px 10px 0;">
                  <div style="font-weight:800; font-size:15px; color:#111; line-height:1.3;">{{ __($f['nombre']) }}</div>
                  @if(!empty($f['desc']))
                    <div style="font-size:13px; color:#555; margin-top:3px; line-height:1.5;">{{ __($f['desc']) }}</div>
                  @endif
                  <div style="font-size:12px; color:#888; margin-top:3px;">{{ $f['unidad'] }}</div>
                </td>
                <td class="ex-total-cell" align="right" style="vertical-align:top; padding:10px 0; white-space:nowrap;">
                  <span style="font-weight:800; font-size:15px; color:#111;">
                    TOTAL ${{ $convertir($f['total']) }} {{ $moneda }}
                  </span>
                </td>
              </tr>
            @endforeach
          </table>
        @endif

        @php
          $tarifaBaseTotalConvertida    = $convertir($tarifaBaseTotal);
          $opcionesRentaTotalConvertida = $convertir($opcionesRentaTotal);
          $impuestosConvertidos         = $convertir($reservacion->impuestos);
          $totalConvertido              = $convertir($reservacion->total);
        @endphp

        <p class="section-title">Detalles del precio</p>
        <div class="summary-line"></div>

        <table class="price-table" role="presentation">
          <tr>
            <td class="price-label">Tarifa base</td>
            <td class="price-value">{{ $simboloMoneda }}{{ $tarifaBaseTotalConvertida }} {{ $moneda }}</td>
          </tr>
          <tr>
            <td class="price-label">Opciones de renta</td>
            <td class="price-value">{{ $simboloMoneda }}{{ $opcionesRentaTotalConvertida }} {{ $moneda }}</td>
          </tr>
          <tr>
            <td class="price-label">Cargos e IVA</td>
            <td class="price-value">{{ $simboloMoneda }}{{ $impuestosConvertidos }} {{ $moneda }}</td>
          </tr>
          <tr class="divider-row">
            <td colspan="2"><div class="divider-line"></div></td>
          </tr>
          <tr class="total-row">
            <td class="total-label"><strong>TOTAL</strong></td>
            <td class="total-value"><strong>{{ $simboloMoneda }}{{ $totalConvertido }} {{ $moneda }}</strong></td>
          </tr>
        </table>

        <p style="margin-top:15px;">
          @if($tipo === 'linea')
            <strong>Método de pago:</strong> PayPal<br>
            <strong>Total pagado:</strong> {{ $simboloMoneda }}{{ $totalConvertido }} {{ $moneda }}
          @else
            <strong>Método de pago:</strong> Pago en mostrador
          @endif
        </p>

      </div>

      <!-- TEXTO Y LÍNEA ROJA -->
      <p class="price-note">
        VIAJERO te garantiza el tamaño del vehículo y sus características, más no el modelo específico.
        Nos comprometemos a entregarte un auto de la categoría reservada, por ejemplo un auto compacto,
        pudiendo ser cualquiera de las marcas que manejamos en nuestra flota dentro de este grupo.
      </p>
      <div class="price-note-line"></div>

      <!-- REQUISITOS -->
      <div class="info-section">
        <p class="info-section-title">Requisitos para rentar</p>
        <ul class="info-section-list">
          <li>Tarjeta de crédito: Con un mínimo de antigüedad de un año, todas nuestras rentas deben ser amparadas con una tarjeta de crédito.</li>
          <li>Edad mínima 21 años: Aplica un cargo por conductor joven si eres menor de 25 años.</li>
          <li>Identificación con fotografía: Credencial del IFE/INE o Pasaporte.</li>
          <li>Licencia para conducir: Deberá estar vigente.</li>
          <li>Relevos de responsabilidad: Elegir entre nuestras opciones de protección para el auto (100%, 90%, 80% o 0%).</li>
        </ul>
        <p class="info-section-paragraph">
          Los requisitos de renta pueden variar, si requieres más información comunícate al 01 (442) 303 26 68
          o escríbenos a reservaciones@viajerocarental.com
        </p>
        <p class="info-section-title">Protección limitada de responsabilidad hacia terceros (LI)</p>
        <p class="info-section-paragraph">
          Protege a terceros por daños y perjuicios ocasionados en un accidente y cubre la cantidad mínima
          requerida por ley. Tú eliges el nivel de responsabilidad sobre el auto que más vaya acorde a tus
          necesidades y presupuesto. Pregunta por nuestros relevos de responsabilidad (opcionales) al llegar
          al mostrador de cualquiera de nuestras oficinas.
        </p>
      </div>

    </div>

    <!-- FOOTER -->
    <div class="site-footer">
      <div class="footer-inner">

        <table class="footer-table" width="100%" cellpadding="0" cellspacing="0" border="0" style="width:100%; table-layout:fixed; border-collapse:collapse;">
          <tr>
            <td class="foot-social-cell" align="left" valign="middle" width="60%" style="width:60%;">
              <div class="footer-social">
                <a href="https://wa.me/524423032668">
                  <img src="https://res.cloudinary.com/xpcjjkal/image/upload/f_auto,q_auto/whatsapp_dtdhsa" alt="WhatsApp">
                </a>
                <a href="https://www.facebook.com/viajerocarental">
                  <img src="https://res.cloudinary.com/xpcjjkal/image/upload/v1783354979/facebook_n5drvl.png" alt="Facebook">
                </a>
                <a href="https://www.instagram.com/viajerocarental">
                  <img src="https://res.cloudinary.com/xpcjjkal/image/upload/v1783355021/instagram_azxt4t.png" alt="Instagram">
                </a>
                <a href="https://www.tiktok.com/@viajerocarental">
                  <img src="https://res.cloudinary.com/xpcjjkal/image/upload/v1783355062/tiktok_romtce.png" alt="TikTok">
                </a>
              </div>
            </td>
            <td class="foot-logo-cell" align="right" valign="middle" width="40%" style="width:40%; padding-left:10px;">
              <img src="https://res.cloudinary.com/xpcjjkal/image/upload/v1783358827/LogoR_uwdvtq.png" alt="Viajero Car Rental" style="height:28px; max-height:28px; width:auto; max-width:100%; display:inline-block; border:none;">
            </td>
          </tr>
        </table>

        <div class="footer-sep"></div>

        <table class="footer-main-table" width="100%" cellpadding="0" cellspacing="0" border="0" style="width:100%; border-collapse:collapse;">
          <tr>
            <td class="foot-col" width="33%" valign="top" style="width:33%; padding-right:15px;">
              <table role="presentation" cellpadding="0" cellspacing="0" border="0" style="border-collapse:collapse;">
                <tr>
                  <td style="width:20px; vertical-align:top; padding:0 6px 6px 0;">
                    <img src="https://api.iconify.design/lucide/map-pin.svg?color=%23111827&width=13&height=13"
                         width="13" height="13" alt="" style="display:block; border:0;">
                  </td>
                  <td style="vertical-align:top; padding:0 0 6px; font-size:12px; color:#111827; line-height:1.4;">
                    OFICINA CENTRAL PARK, QUERÉTARO
                  </td>
                </tr>
                <tr>
                  <td style="width:20px; vertical-align:top; padding:0 6px 6px 0;">
                    <img src="https://api.iconify.design/lucide/map-pin.svg?color=%23111827&width=13&height=13"
                         width="13" height="13" alt="" style="display:block; border:0;">
                  </td>
                  <td style="vertical-align:top; padding:0 0 6px; font-size:12px; color:#111827; line-height:1.4;">
                    PICK-UP AEROPUERTO DE QUERÉTARO
                  </td>
                </tr>
                <tr>
                  <td style="width:20px; vertical-align:top; padding:0 6px 0 0;">
                    <img src="https://api.iconify.design/lucide/map-pin.svg?color=%23111827&width=13&height=13"
                         width="13" height="13" alt="" style="display:block; border:0;">
                  </td>
                  <td style="vertical-align:top; padding:0; font-size:12px; color:#111827; line-height:1.4;">
                    PICK-UP AEROPUERTO DE LEÓN
                  </td>
                </tr>
              </table>
            </td>
            <td class="foot-col" width="33%" valign="top" style="width:33%; padding-right:15px;">
              <ul>
                <li><a href="{{ route('rutaReservaciones') }}">MI RESERVA</a></li>
                <li><a href="{{ route('rutaCatalogo') }}">AUTOS</a></li>
                <li><a href="https://viajerocarental.com/empresas">EMPRESAS</a></li>
                <li><a href="{{ route('rutaPoliticas') }}">TÉRMINOS Y CONDICIONES</a></li>
                <li><a href="{{ route('rutaContacto') }}">CONTACTO</a></li>
              </ul>
            </td>
            <td class="foot-col" width="33%" valign="top" style="width:33%;">
              <ul>
                <li><a href="https://viajerocarental.com/blog">BLOG</a></li>
                <li><a href="{{ route('rutaFAQ') }}">F.A.Q</a></li>
                <li><a href="{{ route('rutaPoliticas') }}">AVISO DE PRIVACIDAD</a></li>
                <li><a href="{{ route('rutaPoliticas') }}">POLÍTICA DE LIMPIEZA</a></li>
                <li><a href="{{ route('rutaPoliticas') }}">POLÍTICA DE RENTA</a></li>
              </ul>
            </td>
          </tr>
        </table>

        <div class="footer-pay">
          <img src="https://res.cloudinary.com/xpcjjkal/image/upload/v1783355140/visa_ootjas.jpg" alt="Visa">
          <img src="https://res.cloudinary.com/xpcjjkal/image/upload/v1783355180/mastercard_g4sfkg.png" alt="Mastercard">
          <img src="https://res.cloudinary.com/xpcjjkal/image/upload/v1783355306/america_nzg98m.png" alt="American Express">
          <img src="https://res.cloudinary.com/xpcjjkal/image/upload/v1783355407/oxxo_zllhgk.png" alt="OXXO">
          <img src="https://res.cloudinary.com/xpcjjkal/image/upload/v1783355459/mercadop_qhqrwm.jpg" alt="Mercado Pago">
          <img src="https://res.cloudinary.com/xpcjjkal/image/upload/v1783355507/paypal_rrsa9u.png" alt="PayPal">
        </div>

      </div>
    </div>

    <div class="footer">
      © {{ date('Y') }} <strong>Viajero Car Rental</strong><br>
      <a href="https://viajerocarental.com">www.viajerocarental.com</a> |
      <a href="mailto:reservaciones@viajerocarental.com">reservaciones@viajerocarental.com</a>
    </div>

  </div>
</body>
</html>
