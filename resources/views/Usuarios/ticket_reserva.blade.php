<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Ticket de Reserva</title>
  <style>
    body { font-family: DejaVu Sans, sans-serif; margin: 40px; color: #333; }
    .logo { text-align: center; margin-bottom: 20px; }
    .logo img { width: 150px; }
    h2 { text-align: center; color: #c00; margin-bottom: 10px; }
    .data { margin-top: 15px; line-height: 1.6; font-size: 14px; }
    .total { font-weight: bold; font-size: 16px; color: #111; }
  </style>
</head>
<body>
  <div class="logo">
    <img src="{{ public_path('img/Logo3.jpg') }}" alt="Viajero Car Rental">
  </div>
  <h2>Ticket de Reserva</h2>
  <div class="data">
    <p><strong>Folio:</strong> {{ $folio }}</p>
    <p><strong>Cliente:</strong> {{ $nombre }}</p>
    <p><strong>Vehículo:</strong> {{ $vehiculo }}</p>
    <p><strong>Fecha de entrega:</strong> {{ $pickup }}</p>
    <p><strong>Fecha de devolución:</strong> {{ $dropoff }}</p>
    <p class="total"><strong>Total a pagar:</strong> ${{ $total }} MXN</p>
    <p><strong>Método de pago:</strong> {{ $metodo }}</p>
  </div>
  <p style="margin-top:25px; text-align:center; color:#555;">Gracias por elegir Viajero Car Rental.</p>
</body>
</html>
