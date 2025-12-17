<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">

    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 13px;
            color: #333;
            margin: 0;
            padding: 0;
        }

        .ticket-container {
            width: 100%;
            max-width: 520px;
            margin: 0 auto;
            padding: 25px 28px;
            border: 1px solid #ddd;
        }

        .header {
            text-align: center;
            margin-bottom: 18px;
        }

        .header img {
            width: 140px;
            margin-bottom: 8px;
        }

        h2 {
            margin: 5px 0 0 0;
            font-weight: 600;
            font-size: 20px;
            color: #C7000A;
        }

        .section-title {
            font-weight: bold;
            margin-top: 18px;
            margin-bottom: 6px;
            color: #C7000A;
            font-size: 15px;
        }

        .line {
            border-bottom: 1px solid #bbb;
            margin: 10px 0;
        }

        .row {
            margin-bottom: 5px;
        }

        .label {
            font-weight: bold;
        }

        .disclaimer {
            margin-top: 18px;
            font-size: 12px;
            line-height: 1.4;
            text-align: justify;
        }

        .footer {
            text-align: center;
            margin-top: 20px;
            font-size: 12px;
            color: #666;
        }

    </style>
</head>

<body>

<div class="ticket-container">

    <!-- HEADER -->
    <div class="header">
        <img src="{{ asset('img/Logo3.jpg') }}" alt="Viajero Car Rental">
        <h2>Ticket de Cierre</h2>
    </div>

    <!-- CONTRATO -->
    <div class="section-title">Datos del Contrato</div>

    <div class="row"><span class="label">Número de contrato:</span> {{ $contrato->numero_contrato }}</div>
    <div class="row"><span class="label">Fecha de cierre:</span> {{ $contrato->cerrado_en }}</div>
    <div class="row"><span class="label">Código reserva:</span> {{ $reservacion->codigo }}</div>

    <div class="line"></div>

    <!-- CLIENTE -->
    <div class="section-title">Datos del Cliente</div>

    <div class="row"><span class="label">Nombre:</span> {{ $reservacion->nombre_cliente }}</div>
    <div class="row"><span class="label">Teléfono:</span> {{ $reservacion->telefono_cliente }}</div>
    <div class="row"><span class="label">Correo:</span> {{ $reservacion->email_cliente }}</div>

    <div class="line"></div>

    <!-- VEHÍCULO -->
    <div class="section-title">Datos del Vehículo</div>

    <div class="row"><span class="label">Vehículo:</span> {{ $vehiculo->marca }} {{ $vehiculo->modelo }} ({{ $vehiculo->anio }})</div>
    <div class="row"><span class="label">Color:</span> {{ $vehiculo->color }}</div>
    <div class="row"><span class="label">Transmisión:</span> {{ $vehiculo->transmision }}</div>
    <div class="row"><span class="label">Placas:</span> {{ $vehiculo->placa }}</div>
    <div class="row"><span class="label">VIN:</span> {{ $vehiculo->vin }}</div>

    <div class="line"></div>

    <!-- RENTA -->
    <div class="section-title">Periodo de Renta</div>

    <div class="row"><span class="label">Fecha inicio:</span> {{ $reservacion->fecha_inicio }}</div>
    <div class="row"><span class="label">Fecha fin:</span> {{ $reservacion->fecha_fin }}</div>
    <div class="row"><span class="label">Días totales:</span> {{ $dias }}</div>

    <div class="line"></div>

    <!-- LIBERACIÓN -->
    <div class="section-title">Liberación de Responsabilidad</div>

    <div class="disclaimer">
        El cliente confirma haber realizado la devolución del vehículo en las condiciones acordadas,
        sin adeudos pendientes y habiendo entregado llaves, documentos y accesorios.
        <br><br>
        A partir de la fecha y hora indicadas en este comprobante, el cliente queda
        <strong>liberado de toda responsabilidad civil, administrativa o penal</strong> relacionada
        con el vehículo descrito anteriormente, salvo daños posteriores comprobables que correspondan
        a faltantes o anomalías no detectadas al momento de la entrega.
    </div>

    <div class="footer">
        Gracias por elegir <strong>Viajero Car Rental</strong>.
    </div>

</div>

</body>
</html>
