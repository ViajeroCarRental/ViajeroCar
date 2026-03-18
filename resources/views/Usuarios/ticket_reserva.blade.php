<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
  <meta charset="UTF-8">
  <title>{{ __('messages.ticket_reserva') }}</title>
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
  <h2>{{ __('messages.ticket_reserva') }}</h2>
  <div class="data">
    <p><strong>{{ __('messages.folio') }}:</strong> {{ $folio }}</p>
    <p><strong>{{ __('messages.cliente') }}:</strong> {{ $nombre }}</p>
    <p><strong>{{ __('messages.vehiculo') }}:</strong> {{ $vehiculo }}</p>
    <p><strong>{{ __('messages.fecha_entrega') }}:</strong> {{ $pickup }}</p>
    <p><strong>{{ __('messages.fecha_devolucion') }}:</strong> {{ $dropoff }}</p>
    <p class="total"><strong>{{ __('messages.total_a_pagar') }}:</strong> ${{ $total }} MXN</p>
    <p><strong>{{ __('messages.metodo_pago') }}:</strong> {{ $metodo }}</p>
  </div>
  <p style="margin-top:25px; text-align:center; color:#555;">{{ __('messages.gracias_elegir') }}</p>
</body>
</html>
