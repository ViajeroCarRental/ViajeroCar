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

    /* HEADER */
    .header {
      background: linear-gradient(90deg, #E50914, #b1060f);
      text-align: center;
      padding: 35px 25px;
      color: #ffffff;
    }

    .header img {
      width: 150px;
      margin-bottom: 15px;
      display: block;
      margin-left: auto;
      margin-right: auto;
      border: none;
    }

    .header h1 {
      font-size: 26px;
      font-weight: 600;
      margin: 0;
      letter-spacing: 0.3px;
    }

    /* CONTENT */
    .content {
      padding: 35px 40px 40px;
      color: #2b2b2b;
      line-height: 1.7;
      font-size: 15px;
    }

    .info-box {
      background: #fff2f2;
      border-left: 5px solid #E50914;
      border-radius: 10px;
      padding: 18px 25px;
      margin: 25px 0;
    }

    .info-box p {
      margin: 6px 0;
      font-size: 15px;
    }

    .firmas-title {
      margin-top: 25px;
      font-weight: 600;
      font-size: 16px;
      color: #111;
    }

    .firma-label {
      margin-top: 15px;
      font-weight: 600;
    }

    .firma-img {
      width: 180px;
      border: 1px solid #ccc;
      border-radius: 6px;
      margin-top: 8px;
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
      font-weight: 500;
    }

    /* DARK MODE */
    @media (prefers-color-scheme: dark) {
      body {
        background-color: #121212 !important;
        color: #f4f4f4 !important;
      }
      .container {
        background-color: #1e1e1e !important;
        border: 1px solid #333 !important;
        box-shadow: none !important;
      }
      .header {
        background: linear-gradient(90deg, #E50914, #ff4d5a) !important;
        color: #ffffff !important;
      }
      .info-box {
        background: rgba(229, 9, 20, 0.12) !important;
        border-left-color: #ff4d5a !important;
      }
      .footer {
        background: #181818 !important;
        color: #ccc !important;
        border-top-color: #333 !important;
      }
      .footer a {
        color: #ff4d5a !important;
      }
    }

    .info-box h3 {
        font-size: 17px;
        font-weight: 600;
        margin-bottom: 10px;
    }

    .info-box p {
        font-size: 15px !important;
        line-height: 1.6;
        white-space: pre-wrap;
    }

  </style>
</head>

<body>

<div class="container">

  <!-- HEADER -->
  <div class="header">
    <img src="{{ asset('img/Logo3.jpg') }}" alt="Viajero Car Rental">
    <h1>Contrato Final de Arrendamiento</h1>
  </div>

  <!-- CONTENT -->
  <div class="content">

    @php
    $nombreCompleto = trim(
        ($reservacion->nombre_cliente ?? '') . ' ' . ($reservacion->apellidos_cliente ?? '')
    );
@endphp

<p>
    Estimado(a)
    <strong>{{ $nombreCompleto !== '' ? $nombreCompleto : ($reservacion->nombre_cliente ?? 'Cliente') }}</strong>,
</p>

    <p>Adjuntamos su <strong>Contrato Final de Renta</strong> correspondiente a su reservación.</p>

    <div class="info-box">
      <p><strong>Fecha inicio:</strong> {{ $reservacion->fecha_inicio }}</p>
      <p><strong>Fecha fin:</strong> {{ $reservacion->fecha_fin }}</p>
      <p><strong>Total:</strong> ${{ number_format($totalFinal, 2) }} MXN</p>
    </div>

            {{-- =============================
         AVISO LEGAL DEL CLIENTE
    ============================== --}}
    @php
        $textoAviso = isset($aviso) ? trim($aviso) : '';
    @endphp

    @if($textoAviso !== '')
        <div class="info-box" style="margin-top: 25px;">
            <h3 style="margin: 0 0 10px; color:#b1060f;">Confirmación del Cliente</h3>

            {{-- Texto que el cliente aceptó (ya incluye su nombre completo) --}}
            <p style="white-space: pre-wrap; font-size:15px; line-height:1.6; text-align:justify;">
                {{ $textoAviso }}
            </p>

            {{-- Firma capturada en el modal de aviso --}}
            @php
                $firmaBase64 = $contrato->firma_aviso ?? null;
            @endphp

            @if(!empty($firmaBase64))
                <p class="firma-label" style="margin-top:18px;">
                    Firma del arrendatario en conformidad con el aviso:
                </p>

                @php
                    // 1) Quitar el encabezado "data:image/png;base64,"
                    $comaPos = strpos($firmaBase64, ',');
                    $datosBase64 = $comaPos !== false
                        ? substr($firmaBase64, $comaPos + 1)
                        : $firmaBase64;

                    // 2) Decodificar base64 a binario
                    $firmaBinaria = base64_decode($datosBase64);

                    // 3) Incrustar la imagen en el correo como CID
                    $cidFirmaAviso = $message->embedData(
                        $firmaBinaria,
                        'firma-aviso.png',
                        'image/png'
                    );
                @endphp

                <img src="{{ $cidFirmaAviso }}"
                alt="Firma del arrendatario"
                class="firma-img"
                style="display:block; margin:8px auto 0; border:1px solid #ccc; border-radius:6px; width:180px;">


                @php
                    $nombreCompletoAviso = trim(
                        ($reservacion->nombre_cliente ?? '') . ' ' .
                        ($reservacion->apellidos_cliente ?? '')
                    );
                @endphp

                @if($nombreCompletoAviso !== '')
                  <p style="margin-top:6px; font-size:14px; font-weight:600; text-align:center;">
                    {{ $nombreCompletoAviso }}
                  </p>
                @endif

            @endif
        </div>
    @endif






    <div class="divider"></div>

    <p style="margin-top:20px;">Gracias por elegir <strong>Viajero Car Rental</strong>.</p>

  </div>

  <!-- FOOTER -->
  <div class="footer">
    © {{ date('Y') }} <strong>Viajero Car Rental</strong> — Todos los derechos reservados.<br>
    <a href="https://viajerocarental.com">www.viajerocarental.com</a> |
    <a href="mailto:reservaciones@viajerocarental.com">reservaciones@viajerocarental.com</a>
  </div>

</div>

</body>
</html>
