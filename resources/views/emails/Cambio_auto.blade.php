<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
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
  </style>
</head>

<body>

<div class="container">

  <!-- HEADER -->
  <div class="header">
    <img src="{{ asset('img/Logo3.jpg') }}" alt="Viajero Car Rental">
    <h1>{{ __('messages.cambio_vehiculo_titulo') }}</h1>
  </div>

  <!-- CONTENT -->
  <div class="content">

    <p>
      {{ __('messages.estimado_cliente') }} <strong>{{ $clienteNombre }}</strong>,
    </p>

    <p>
      {!! __('messages.enviamos_formato_cambio', ['codigo' => $codigoReservacion ?? '']) !!}
    </p>

    <p>
      {{ __('messages.detalle_documento') }}
    </p>

    <div class="info-box">
      <p>
        {{ __('messages.revisar_documento') }}
      </p>
    </div>

    <p>
      {{ __('messages.equipo_disponible') }}
    </p>

    <div class="divider"></div>

    <p style="color:#555;">
      {{ __('messages.gracias_confianza') }}<br>
      {{ __('messages.seguir_atendiendo') }}
    </p>

  </div>

  <!-- FOOTER -->
  <div class="footer">
    © {{ date('Y') }} <strong>Viajero Car Rental</strong> — {{ __('messages.todos_derechos') }}<br>
    <a href="https://viajerocarental.com">www.viajerocarental.com</a> |
    <a href="mailto:reservaciones@viajerocarental.com">reservaciones@viajerocarental.com</a>
  </div>

</div>

</body>
</html>
