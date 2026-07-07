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

    .brand-logo {
      width: 210px;
      max-width: 210px;
      height: auto;
      display: block;
      border: none;
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

    .info-box {
      background: #fff2f2;
      border-left: 5px solid #E50914;
      border-radius: 10px;
      padding: 18px 25px;
      margin: 25px 0;
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

    .item {
      width: 100%;
      border-collapse: collapse;
      margin: 0 0 10px 0;
    }

    .item .label {
      font-weight: 600;
      color: #111;
      width: 40%;
    }

    .item .value {
      color: #111;
      text-align: left;
    }

    .price-table {
      width: 100%;
      border-collapse: collapse;
    }

    .price-table td {
      padding: 2px 0;
    }

    .price-table .p-label {
      color: #111;
    }

    .price-table .p-value {
      text-align: right;
      color: #111;
    }

    .price-total {
      font-weight: 800;
      font-size: 18px;
      padding-top: 6px !important;
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

    .extras-row { width: 100%; border-collapse: collapse; }
    .extras-hr { height: 1px; background: #111; margin: 10px 0 14px; }
    .ex-item { width: 33.33%; vertical-align: top; padding: 10px 8px; }
    .ex-wrap { width: 100%; border-collapse: collapse; }

    .ex-check {
      width: 18px;
      height: 18px;
      border: 2px solid #111;
      border-radius: 3px;
    }

    .ex-check.on {
      background: #E50914;
      border-color: #E50914;
    }

    .ex-name {
      font-weight: 800;
      font-size: 14px;
      color: #111;
    }

    .ex-price {
      font-size: 13px;
      opacity: .85;
      color: #111;
    }

    .ex-subtitle {
      font-size: 12px;
      font-weight: 800;
      letter-spacing: .4px;
      text-transform: uppercase;
      opacity: .85;
      margin: 14px 0 8px;
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

    .footer-logo-cell {
      text-align: right !important;
      padding-left: 20px;
      width: 30%;
    }

    .footer-logo-cell img {
      height: 28px;
      max-height: 28px;
      width: auto;
      display: inline-block;
      border: none;
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

    .header-logo {
      width: 260px;
      max-width: 260px;
      height: auto;
      display: block;
      border: none;
    }

    .ubicacion-text {
      font-size: 12px;
      margin: 0 0 6px;
    }
  </style>
</head>

<body>
  <div class="container">

<div class="container">

    <!-- HEADER NUEVO -->
  <div class="header">
    <table class="header-table" role="presentation">
      <tr>
        <td style="vertical-align:middle;">
        <img src="{{ $message->embed($logoPath) }}"
            alt="Viajero Car Rental"
            style="width:210px; max-width:210px; height:auto; display:block; border:none;">
        </td>

        <td class="resv-box">
          <p class="label">Reservación</p>
          <p class="code">{{ $reservacion->codigo }}</p>
        </td>
      </tr>
    </table>
  </div>


  <!-- CONTENT -->
  <div class="content">

        <!-- MENSAJE NUEVO (tipo imagen 1) -->
    <div class="hero">
      <p class="thanks">
        ¡Gracias! <strong>{{ strtoupper(trim(($reservacion->nombre_cliente ?? '') . ' ' . ($reservacion->apellidos_cliente ?? ''))) }}</strong>
      </p>

      <p class="lead">
        Tu vehículo ya está reservado, el siguiente código es tu número de reservación,
        da <a href="{{ $url_detalle ?? '#' }}">click aquí</a> para más información.
      </p>

      <p class="lead" style="margin-top:0;">
        La siguiente información se calculó con los datos proporcionados en el proceso de reservación,
        cualquier modificación relacionada con lo que esta reservación describe podría resultar en una variación contra el precio acordado.
      </p>
    </div>

    <div class="content">

        <!-- RESUMEN NUEVO (tipo imagen 2) -->
    <h2 class="summary-title">Resumen de tu reserva</h2>

    <div class="summary-card">

      <!-- Encabezado interno -->
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

      <!-- Lugar y fecha -->
      <table class="item" role="presentation">
        <tr>
          <td class="label">Entrega:</td>
          <td class="value">
  <div>{{ $reservacion->fecha_inicio }} {{ $reservacion->hora_retiro ?? '' }}</div>
  <div style="font-size:13px; opacity:.85;">{{ $lugarRetiro ?? '-' }}</div>
</td>

        </tr>
        <tr>
          <td class="label">Devolución:</td>
          <td class="value">
  <div>{{ $reservacion->fecha_fin }} {{ $reservacion->hora_entrega ?? '' }}</div>
  <div style="font-size:13px; opacity:.85;">{{ $lugarEntrega ?? '-' }}</div>
</td>

        </tr>
      </table>

      <div class="summary-line"></div>

     <!-- Tu Auto -->
<p class="section-title">Tu Auto</p>

<table role="presentation" style="width:100%; border-collapse:collapse;">
  <tr>
    <!-- Imagen -->
   <td style="width:45%; vertical-align:middle; padding:10px 0;">
    {{-- Usamos la variable que enviamos --}}
    <img src="{{ $imgCategoria }}"
         alt="Vehículo"
         width="260"
         style="display:block; border:0;">
</td>

    <!-- Texto -->
    <td style="width:55%; vertical-align:middle; padding:10px 0 10px 10px;">
      <p class="auto-title">
        {{ $tuAuto['titulo'] ?? ($categoria->descripcion ?? '-') }}
      </p>

      <p class="auto-subtitle">
        {{ $tuAuto['subtitulo'] ?? 'CATEGORÍA ' . ($categoria->codigo ?? '-') }}
      </p>

      <div class="auto-specs">
        <div><strong>{{ $tuAuto['pax'] ?? 5 }}</strong> pasajeros</div>
        <div><strong>{{ $tuAuto['small'] ?? 2 }}</strong> maletas chicas</div>
        <div><strong>{{ $tuAuto['big'] ?? 1 }}</strong> maletas grandes</div>
        <div class="muted">{{ $tuAuto['transmision'] ?? 'Transmisión manual o automática' }}</div>
        <div class="muted">{{ $tuAuto['tech'] ?? 'Apple CarPlay | Android Auto' }}</div>
      </div>

      <h2 class="summary-title">Resumen de tu reserva</h2>

      <div class="summary-card">

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

        <table class="item" role="presentation">
          <tr>
            <td class="label">Entrega:</td>
            <td class="value">
              <div>{{ $reservacion->fecha_inicio }} {{ $reservacion->hora_retiro ?? '' }}</div>
              <div style="font-size:13px; opacity:.85;">{{ $lugarRetiro ?? '-' }}</div>
            </td>
          </tr>
          <tr>
            <td class="label">Devolución:</td>
            <td class="value">
              <div>{{ $reservacion->fecha_fin }} {{ $reservacion->hora_entrega ?? '' }}</div>
              <div style="font-size:13px; opacity:.85;">{{ $lugarEntrega ?? '-' }}</div>
            </td>
          </tr>
        </table>

        <div class="summary-line"></div>

        <p class="section-title">Tu Auto</p>

        <table role="presentation" style="width:100%; border-collapse:collapse;">
          <tr>
            <td style="width:45%; vertical-align:middle; padding:10px 0;">
              <img src="{{ $imgCategoria ?? (rtrim(config('app.url'), '/') . '/img/categorias/placeholder.png') }}" alt="Vehículo" style="width:100%; max-width:260px; height:auto; display:block; border:none;">
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

        <p class="section-title">Extras</p>
        <div class="extras-hr" style="height:1px; background:#111; margin:10px 0 14px;"></div>

        @php
          $servCards = collect();
          if (!empty($extrasReserva) && count($extrasReserva) > 0) {
            foreach ($extrasReserva as $ex) {
              $servCards->push([
                'nombre' => $ex->nombre ?? 'Servicio',
                'precio' => isset($ex->precio_unitario) ? ('$' . number_format((float)$ex->precio_unitario, 2) . ' c/u') : '',
                'tipo'   => 'servicio',
              ]);
            }
          }

          $segCard = null;
          if (!empty($seguroReserva)) {
            $segCard = [
              'nombre' => $seguroReserva->nombre ?? 'Seguro',
              'precio' => isset($seguroReserva->precio_por_dia) ? ('$' . number_format((float)$seguroReserva->precio_por_dia, 2) . ' por día') : '',
              'tipo'   => 'seguro',
            ];
          }

          $servChunks = $servCards->chunk(3);
        @endphp

        @if($servCards->count() === 0)
          <div style="font-size:14px; opacity:.85;">No seleccionados</div>
        @else
          <table role="presentation" style="width:100%; border-collapse:collapse;">
            @foreach($servChunks as $row)
              <tr>
                @foreach($row as $c)
                  <td style="width:33.33%; padding:10px 8px; vertical-align:top;">
                    <table role="presentation" style="width:100%; border-collapse:collapse;">
                      <tr>
                        <td style="width:26px; vertical-align:top; padding-top:2px;">
                          <div style="width:18px; height:18px; background:#E50914; border:2px solid #E50914; border-radius:3px;"></div>
                        </td>
                        <td style="vertical-align:top;">
                          <div style="font-weight:800; font-size:14px; color:#111; line-height:1.2;">{{ $c['nombre'] }}</div>
                          @if(!empty($c['precio']))
                            <div style="font-size:13px; opacity:.85; color:#111; margin-top:2px;">{{ $c['precio'] }}</div>
                          @endif
                        </td>
                      </tr>
                    </table>
                  </td>
                @endforeach
                @for($i = $row->count(); $i < 3; $i++)
                  <td style="width:33.33%; padding:10px 8px;"></td>
                @endfor
              </tr>
            @endforeach
          </table>
        @endif

        @if($segCard)
          <div style="margin-top:10px;"></div>
          <div style="font-size:12px; font-weight:800; letter-spacing:.4px; text-transform:uppercase; opacity:.85; margin:14px 0 8px;">Paquete de seguro</div>
          <table role="presentation" style="width:100%; border-collapse:collapse;">
            <tr>
              <td style="width:33.33%; padding:10px 8px; vertical-align:top;">
                <table role="presentation" style="width:100%; border-collapse:collapse;">
                  <tr>
                    <td style="width:26px; vertical-align:top; padding-top:2px;">
                      <div style="width:18px; height:18px; background:#E50914; border:2px solid #E50914; border-radius:3px;"></div>
                    </td>
                    <td style="vertical-align:top;">
                      <div style="font-weight:800; font-size:14px; color:#111; line-height:1.2;">{{ $segCard['nombre'] }}</div>
                      @if(!empty($segCard['precio']))
                        <div style="font-size:13px; opacity:.85; color:#111; margin-top:2px;">{{ $segCard['precio'] }}</div>
                      @endif
                    </td>
                  </tr>
                </table>
              </td>
              <td style="width:33.33%; padding:10px 8px;"></td>
              <td style="width:33.33%; padding:10px 8px;"></td>
            </tr>
          </table>
        @endif

        <div class="summary-line"></div>

        @php
          $fechaInicio = \Carbon\Carbon::parse($reservacion->fecha_inicio);
          $fechaFin    = \Carbon\Carbon::parse($reservacion->fecha_fin);
          $diasCorreo  = max(1, $fechaInicio->diffInDays($fechaFin));
          $tarifaBaseDia   = (float) ($reservacion->tarifa_base ?? 0);
          $tarifaBaseTotal = round($tarifaBaseDia * $diasCorreo, 2);
        @endphp

        <p class="section-title">Detalles del precio</p>
        <table class="price-table" role="presentation">
          <tr>
            <td class="p-label">Tarifa base</td>
            <td class="p-value">${{ number_format($tarifaBaseTotal, 2) }} MXN</td>
          </tr>
          <tr>
            <td class="p-label">Opciones de renta</td>
            <td class="p-value">${{ number_format((float)($opcionesRentaTotal ?? 0), 2) }} MXN</td>
          </tr>
          <tr>
            <td class="p-label">Cargos e IVA</td>
            <td class="p-value">${{ number_format($reservacion->impuestos, 2) }} MXN</td>
          </tr>
          <tr>
            <td class="p-label price-total">TOTAL</td>
            <td class="p-value price-total">${{ number_format($reservacion->total, 2) }} MXN</td>
          </tr>
        </table>

        <div class="divider"></div>

      {{-- Fila superior: redes + logo de palabra --}}
      <div class="footer-top">
        <div class="footer-social">
    <a href="https://wa.me/524423032668">
        <img src="https://upload.wikimedia.org/wikipedia/commons/6/6b/WhatsApp.svg" alt="WhatsApp" style="max-height:20px; display:block;">
    </a>
    <a href="https://www.facebook.com/viajerocarental">
        <img src="https://upload.wikimedia.org/wikipedia/commons/b/b8/2021_Facebook_icon.svg" alt="Facebook" style="max-height:20px; display:block;">
    </a>
    <a href="https://www.instagram.com/viajerocarental">
        <img src="https://upload.wikimedia.org/wikipedia/commons/e/e7/Instagram_logo_2016.svg" alt="Instagram" style="max-height:20px; display:block;">
    </a>
    <a href="https://www.tiktok.com/@viajerocarental">
        <img src="https://cdn-icons-png.flaticon.com/512/3046/3046121.png" alt="TikTok" style="max-height:20px; display:block;">
    </a>
</div>

        <div class="footer-logo-word">
          <img src="{{ $message->embed(public_path('img/Logo3.jpg')) }}" alt="Viajero Car Rental">
        </div>
      </div>

      <div class="footer-sep"></div>

      {{-- Fila central: ubicaciones + links --}}
      <div class="footer-main">
        {{-- Columna 1: ubicaciones --}}
        <div class="footer-col">
          <p>📍 OFICINA CENTRAL PARK, QUERÉTARO</p>
          <p>📍 PICK-UP AEROPUERTO DE QUERÉTARO</p>
          <p>📍 PICK-UP AEROPUERTO DE LEÓN</p>
        </div>

        {{-- Columna 2: links centro --}}
        <div class="footer-col">
          <ul>
            <li><a href="{{ route('rutaReservaciones') }}">MI RESERVA</a></li>
            <li><a href="{{ route('rutaCatalogo') }}">AUTOS</a></li>
            <li><a href="https://viajerocarental.com/empresas">EMPRESAS</a></li>
            <li><a href="{{ route('rutaPoliticas') }}">TÉRMINOS Y CONDICIONES</a></li>
            <li><a href="{{ route('rutaContacto') }}">CONTACTO</a></li>
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

        {{-- Columna 3: links derecha --}}
        <div class="footer-col">
          <ul>
            <li><a href="https://viajerocarental.com/blog">BLOG</a></li>
            <li><a href="{{ route('rutaFAQ') }}">F.A.Q</a></li>
            <li><a href="{{ route('rutaPoliticas') }}">AVISO DE PRIVACIDAD</a></li>
            <li><a href="{{ route('rutaPoliticas') }}">POLÍTICA DE LIMPIEZA</a></li>
            <li><a href="{{ route('rutaPoliticas') }}">POLÍTICA DE RENTA</a></li>
          </ul>
        </div>
      </div>

      {{-- Fila inferior: métodos de pago --}}
      <div class="footer-pay">
        <img src="{{ $message->embed(public_path('img/visa.jpg')) }}" alt="Visa">
        <img src="{{ $message->embed(public_path('img/mastercard.png')) }}" alt="Mastercard">
        <img src="{{ $message->embed(public_path('img/america.png')) }}" alt="American Express">
        <img src="{{ $message->embed(public_path('img/oxxo.png')) }}" alt="OXXO">
        <img src="{{ $message->embed(public_path('img/pago.png')) }}" alt="Mercado Pago">
        <img src="{{ $message->embed(public_path('img/paypal.png')) }}" alt="PayPal">
      </div>

    </div>

    <div class="site-footer">
      <div class="footer-inner">

        <table class="footer-table" cellpadding="0" cellspacing="0" border="0">
          <tr>
            <td align="left" valign="middle" style="width: 70%;">
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
            <td align="right" valign="middle" style="width: 30%; padding-left: 20px;">
              <img src="https://res.cloudinary.com/xpcjjkal/image/upload/v1783358827/LogoR_uwdvtq.png" alt="Viajero Car Rental" style="height: 28px; max-height: 28px; width: auto; display: inline-block; border: none;">
            </td>
          </tr>
        </table>

        <div class="footer-sep"></div>

        <table class="footer-main-table" cellpadding="0" cellspacing="0" border="0">
          <tr>
            <td width="33%" valign="top" style="padding-right: 15px;">
              <p class="ubicacion-text">📍 OFICINA CENTRAL PARK, QUERÉTARO</p>
              <p class="ubicacion-text">📍 PICK-UP AEROPUERTO DE QUERÉTARO</p>
              <p class="ubicacion-text">📍 PICK-UP AEROPUERTO DE LEÓN</p>
            </td>
            <td width="33%" valign="top" style="padding-right: 15px;">
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
