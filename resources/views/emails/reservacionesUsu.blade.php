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
    <h1>
      {{ $tipo === 'linea' ? 'Reserva Confirmada' : 'Confirmación de Reservación' }}
    </h1>
  </div>

  <!-- CONTENT -->
  <div class="content">

    <p>
      Estimado(a) <strong>{{ $reservacion->nombre_cliente ?? 'Cliente' }}</strong>,
    </p>

    <p>
      @if($tipo === 'linea')
        Su reservación ha sido <strong>confirmada</strong> y el pago fue recibido exitosamente.
      @else
        Su reservación fue registrada exitosamente. El pago se realizará <strong>en mostrador</strong>.
      @endif
    </p>

    <!-- INFO PRINCIPAL -->
    <div class="info-box">
      <p><strong>Código de reserva:</strong> {{ $reservacion->codigo }}</p>

      <p><strong>Cliente:</strong></p>
      <p>Nombre: {{ $reservacion->nombre_cliente ?? 'No especificado' }}</p>
      <p>Correo: {{ $reservacion->email_cliente ?? '-' }}</p>
      <p>Teléfono: {{ $reservacion->telefono_cliente ?? '-' }}</p>
      <p>Vuelo: {{ $reservacion->no_vuelo ?? '-' }}</p>

      <br>

      <p><strong>Fechas:</strong></p>
      <p>Entrega: {{ $reservacion->fecha_inicio }} {{ $reservacion->hora_retiro }}</p>
      <p>Devolución: {{ $reservacion->fecha_fin }} {{ $reservacion->hora_entrega }}</p>

      <br>

      <p><strong>Montos:</strong></p>
      <p>Subtotal: ${{ number_format($reservacion->subtotal, 2) }} MXN</p>
      <p>Impuestos: ${{ number_format($reservacion->impuestos, 2) }} MXN</p>

      @if($tipo === 'linea')
        <p><strong>Total pagado:</strong> ${{ number_format($reservacion->total, 2) }} MXN</p>
        <p><strong>Método de pago:</strong> PayPal</p>
        <p><strong>Transacción:</strong> {{ $reservacion->paypal_order_id ?? 'No disponible' }}</p>
      @else
        <p><strong>Total a pagar en mostrador:</strong> ${{ number_format($reservacion->total, 2) }} MXN</p>
      @endif

      <br>

      <p><strong>Fecha de registro:</strong> {{ $reservacion->created_at }}</p>
    </div>

    <!-- NOTAS -->
    <p><strong>Notas importantes:</strong></p>
    <ul>
      <li>Los seguros obligatorios no están incluidos en este monto.</li>
      <li>Se cotizan y confirman con un agente de Viajero Car Rental.</li>
      <li>Tarifas y disponibilidad sujetas a cambio sin previo aviso.</li>
      <li>Se requiere tarjeta de crédito física del titular al recoger el vehículo.</li>
    </ul>

    <div class="divider"></div>

    <p>
      Si necesita factura, aclaraciones o desea realizar una nueva reservación,
      con gusto podemos ayudarle.
    </p>

    <p>
      Gracias por elegir <strong>Viajero Car Rental</strong>.
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
