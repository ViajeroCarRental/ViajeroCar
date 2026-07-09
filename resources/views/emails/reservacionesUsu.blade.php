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

    @media only screen and (max-width: 600px) {
      .footer-main-table td {
        display: block !important;
        width: 100% !important;
        text-align: center !important;
        padding: 8px 0 !important;
      }

      .footer-pay img {
        width: 35px !important;
        margin-bottom: 5px !important;
      }

      .content {
        padding: 20px !important;
      }

      .summary-card {
        padding: 14px !important;
      }

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
    }
  </style>
</head>

<body>
  <div class="container">

    <!-- HEADER -->
    <div class="header">
      <table class="header-table" role="presentation">
        <tr>
          <td style="vertical-align:middle;">
            <img src="https://res.cloudinary.com/xpcjjkal/image/upload/v1783355566/Logo3_p1kxin.jpg" alt="Viajero Car Rental" style="width:210px; max-width:210px; height:auto; display:block; border:none;">
          </td>
          <td class="resv-box">
            <p class="label">Reservación</p>
            <p class="code">{{ $reservacion->codigo }}</p>
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
          $fechaInicio = \Carbon\Carbon::parse($reservacion->fecha_inicio);
          $fechaFin = \Carbon\Carbon::parse($reservacion->fecha_fin);
          $diasCorreo = max(1, $fechaInicio->diffInDays($fechaFin));
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
          <div class="location-datetime">
            Fecha: <strong>{{ ucfirst(\Carbon\Carbon::parse($reservacion->fecha_inicio)->translatedFormat('D. d M. Y')) }}</strong>
          </div>
          <div class="location-datetime">
            Hora: <strong>{{ \Carbon\Carbon::parse($reservacion->hora_retiro)->format('g:i A') }}</strong>
          </div>
          <div class="location-place">
            Location: <strong>{{ $lugarRetiroTraducido }}</strong>
          </div>
        </div>

        <!-- RETURN -->
        <div class="location-block">
          <div class="location-title">RETURN</div>
          <div class="location-datetime">
            Fecha: <strong>{{ ucfirst(\Carbon\Carbon::parse($reservacion->fecha_fin)->translatedFormat('D. d M. Y')) }}</strong>
          </div>
          <div class="location-datetime">
            Hora: <strong>{{ \Carbon\Carbon::parse($reservacion->hora_entrega)->format('g:i A') }}</strong>
          </div>
          <div class="location-place">
            Location: <strong>{{ $lugarEntregaTraducido }}</strong>
          </div>
        </div>

        <div class="summary-line"></div>

        <!-- TU AUTO -->
        <p class="section-title">Tu Auto</p>

        <table role="presentation" style="width:100%; border-collapse:collapse;">
          <tr>
            <td style="width:45%; vertical-align:middle; padding:10px 0;">
              @php
                $rutaImgUsr = ltrim(parse_url($imgCategoria, PHP_URL_PATH) ?? '', '/');
                $rutaImgUsrLocal = public_path($rutaImgUsr);
              @endphp
              @if($rutaImgUsr && file_exists($rutaImgUsrLocal))
                <img src="{{ $message->embed($rutaImgUsrLocal) }}" alt="Vehículo" style="width:100%; max-width:260px; height:auto; display:block; border:none;">
              @else
                <img src="{{ $imgCategoria }}" alt="Vehículo" style="width:100%; max-width:260px; height:auto; display:block; border:none;">
              @endif
            </td>
            <td style="width:55%; vertical-align:middle; padding:10px 0 10px 10px;">
              <p class="auto-title">{{ $tuAuto['titulo'] ?? ($categoria->descripcion ?? '-') }}</p>
              <p class="auto-subtitle">{{ $tuAuto['subtitulo'] ?? 'CATEGORÍA ' . ($categoria->codigo ?? '-') }}</p>
              <div class="auto-specs">
                <div><strong>{{ $tuAuto['pax'] ?? 5 }}</strong> pasajeros</div>
                <div><strong>{{ $tuAuto['small'] ?? 2 }}</strong> maletas chicas</div>
                <div><strong>{{ $tuAuto['big'] ?? 1 }}</strong> maletas grandes</div>
                <div class="muted">{{ $tuAuto['transmision'] ?? 'Transmisión manual o automática' }}</div>
                <div class="muted">{{ $tuAuto['tech'] ?? 'Apple CarPlay | Android Auto' }}</div>
              </div>
              <div class="auto-includes">{{ $tuAuto['incluye'] ?? 'KM ilimitados | Reelevo de Responsabilidad (LI)' }}</div>
            </td>
          </tr>
        </table>

        <div class="summary-line"></div>

        <!-- EXTRAS -->
        <p class="section-title">Extras</p>
        <div class="summary-line"></div>

        <div style="margin:15px 0;">
          <table role="presentation" width="100%">
            <tr>
              @foreach($serviciosDisponibles as $index => $servicio)
                @php
                  $seleccionado = collect($extrasReserva)->pluck('nombre')->contains($servicio['nombre']);
                  $tipoCambio = 20;
                  $moneda = $locale === 'en' ? 'USD' : 'MXN';
                  $simboloMoneda = '$';
                  $precioExtra = $servicio['precio'];
                  if ($moneda === 'USD') {
                    $precioExtra = $precioExtra / $tipoCambio;
                  }
                  $precioExtraFormateado = number_format($precioExtra, $moneda === 'USD' ? 2 : 0);
                  $unidadTraducida = __($servicio['unidad']);
                @endphp
                <td width="33%" style="vertical-align:top; padding-bottom:12px;">
                  <table role="presentation">
                    <tr>
                      <td style="vertical-align:top; padding-right:8px;">
                        <div style="width:18px; height:18px; border-radius:4px; border:2px solid {{ $seleccionado ? '#E50914' : '#d1d5db' }}; background:{{ $seleccionado ? '#E50914' : '#fff' }}; text-align:center; line-height:16px; font-size:12px; color:white; font-weight:bold;">
                          @if($seleccionado) ✓ @endif
                        </div>
                      </td>
                      <td>
                        <div style="font-size:13px; font-weight:700; color:#111;">{{ $servicio['nombre'] }}</div>
                        <div style="font-size:11px; color:#6b7280;">{{ $servicio['desc'] }}</div>
                        <div style="font-size:11px; font-weight:600; color:#111;">{{ $simboloMoneda }}{{ $precioExtraFormateado }} / {{ $unidadTraducida }} {{ $moneda === 'USD' ? 'USD' : 'MXN' }}</div>
                      </td>
                    </tr>
                  </table>
                </td>
                @if(($index + 1) % 3 == 0 && !$loop->last)
                  </tr><tr>
                @endif
              @endforeach
            </tr>
          </table>
        </div>

        @php
          $tipoCambio = 20;
          $moneda = $locale === 'en' ? 'USD' : 'MXN';
          $simboloMoneda = '$';

          function formatearPrecioEmail($montoMXN, $moneda, $tipoCambio) {
            if ($moneda === 'USD') {
              $monto = $montoMXN / $tipoCambio;
              return number_format($monto, 2);
            }
            return number_format($montoMXN, 2);
          }

          $tarifaBaseTotalConvertida = formatearPrecioEmail($tarifaBaseTotal, $moneda, $tipoCambio);
          $opcionesRentaTotalConvertida = formatearPrecioEmail($opcionesRentaTotal, $moneda, $tipoCambio);
          $impuestosConvertidos = formatearPrecioEmail($reservacion->impuestos, $moneda, $tipoCambio);
          $totalConvertido = formatearPrecioEmail($reservacion->total, $moneda, $tipoCambio);
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

        <table class="footer-table" cellpadding="0" cellspacing="0" border="0">
          <tr>
            <td align="left" valign="middle" style="width:70%;">
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
            <td align="right" valign="middle" style="width:30%; padding-left:20px;">
              <img src="https://res.cloudinary.com/xpcjjkal/image/upload/v1783358827/LogoR_uwdvtq.png" alt="Viajero Car Rental" style="height:28px; max-height:28px; width:auto; display:inline-block; border:none;">
            </td>
          </tr>
        </table>

        <div class="footer-sep"></div>

        <table class="footer-main-table" cellpadding="0" cellspacing="0" border="0">
          <tr>
            <td width="33%" valign="top" style="padding-right:15px;">
              <p class="ubicacion-text">📍 OFICINA CENTRAL PARK, QUERÉTARO</p>
              <p class="ubicacion-text">📍 PICK-UP AEROPUERTO DE QUERÉTARO</p>
              <p class="ubicacion-text">📍 PICK-UP AEROPUERTO DE LEÓN</p>
            </td>
            <td width="33%" valign="top" style="padding-right:15px;">
              <ul>
                <li><a href="{{ route('rutaReservaciones') }}">MI RESERVA</a></li>
                <li><a href="{{ route('rutaCatalogo') }}">AUTOS</a></li>
                <li><a href="https://viajerocarental.com/empresas">EMPRESAS</a></li>
                <li><a href="{{ route('rutaPoliticas') }}">TÉRMINOS Y CONDICIONES</a></li>
                <li><a href="{{ route('rutaContacto') }}">CONTACTO</a></li>
              </ul>
            </td>
            <td width="33%" valign="top">
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
