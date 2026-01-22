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
    <h1>Cambio de Vehículo en su Contrato</h1>
  </div>

  <!-- CONTENT -->
  <div class="content">

    <p>
      Estimado(a) <strong>{{ $clienteNombre }}</strong>,
    </p>

    <p>
      Le enviamos adjunto el <strong>formato de cambio de vehículo</strong>
      correspondiente a su reservación
      <strong>{{ $codigoReservacion ?? '' }}</strong>.
    </p>

    <p>
      En este documento encontrará el detalle del vehículo originalmente
      asignado, el nuevo vehículo entregado, así como el registro de los
      daños y fotografías asociados a este cambio.
    </p>

    <div class="info-box">
      <p>
        Le recomendamos <strong>revisar cuidadosamente la información
        del documento</strong> y conservarlo como respaldo de las
        condiciones en que se realizó el cambio de vehículo.
        Si detecta alguna aclaración o corrección, por favor contacte a
        nuestros agentes de inmediato.
      </p>
    </div>

    <p>
      Nuestro equipo está a su disposición para resolver cualquier duda
      relacionada con su contrato o con el cambio de vehículo realizado.
    </p>

    <div class="divider"></div>

    <p style="color:#555;">
      Gracias por confiar en <strong>Viajero Car Rental</strong>.<br>
      Será un placer seguir atendiéndole en sus próximos viajes.
    </p>

  </div>

  <!-- FOOTER -->
  <div class="footer">
    © {{ date('Y') }} <strong>Viajero Car Rental</strong><br>
    <a href="https://viajerocarental.com">www.viajerocarental.com</a> |
    <a href="mailto:reservaciones@viajerocarental.com">reservaciones@viajerocarental.com</a>
  </div>

</div>

</body>
</html>
