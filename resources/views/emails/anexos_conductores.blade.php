{{-- resources/views/emails/anexos_conductores.blade.php --}}
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
      letter-spacing: 0.3px;
    }

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

    @media (prefers-color-scheme: dark) {
      body { background-color: #121212 !important; color: #f4f4f4 !important; }
      .container { background-color: #1e1e1e !important; border: 1px solid #333 !important; box-shadow: none !important; }
      .header { background: linear-gradient(90deg, #E50914, #ff4d5a) !important; }
      .info-box { background: rgba(229, 9, 20, 0.12) !important; border-left-color: #ff4d5a !important; }
      .footer { background: #181818 !important; color: #ccc !important; border-top-color: #333 !important; }
      .footer a { color: #ff4d5a !important; }
    }
  </style>
</head>

<body>

<div class="container">

  <!-- HEADER -->
  <div class="header">
    <img src="{{ asset('img/Logo3.jpg') }}" alt="Viajero Car Rental">
    <h1>{{ __('messages.anexos_titulo_correo') }}</h1>
  </div>

  <!-- CONTENT -->
  <div class="content">

    <p>
      {{ __('messages.estimado_cliente') }}
      <strong>{{ $reservacion->nombre_cliente ?? __('messages.cliente') }}</strong>,
    </p>

    <p>
      {!! __('messages.enviamos_anexos') !!}
    </p>

    <p>
      {!! __('messages.pdf_adjuntos') !!}
    </p>

    <div class="info-box">
      <p>
        <strong>{{ __('messages.folio_contrato') }}</strong>
        {{ $contrato->numero_contrato ?? $contrato->id_contrato ?? '—' }}
      </p>

      @if(!empty($reservacion->fecha_inicio) || !empty($reservacion->hora_retiro))
        <p>
          <strong>{{ __('messages.fecha_hora_inicio') }}</strong>
          {{ $reservacion->fecha_inicio ?? '—' }}
          {{ $reservacion->hora_retiro ?? '' }}
        </p>
      @endif

      @if(!empty($reservacion->ciudad_retiro_nombre))
        <p>
          <strong>{{ __('messages.oficina_ciudad_salida') }}</strong>
          {{ $reservacion->ciudad_retiro_nombre }}
        </p>
      @endif
    </div>

    <p>
      {{ __('messages.anexos_documentacion') }}
    </p>

    <div class="divider"></div>

    <p style="margin-top:20px;">
      {!! __('messages.gracias_confianza') !!}<br>
      {{ __('messages.excelente_viaje') }}
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
