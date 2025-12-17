<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            color: #111;
            font-size: 14px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        .header td {
            vertical-align: middle;
        }

        .logo {
            height: 60px;
        }

        .folio {
            text-align: right;
        }

        .folio span {
            color: #D6121F;
            font-weight: bold;
            font-size: 15px;
        }

        hr {
            margin: 16px 0;
            border: none;
            border-top: 1px solid #ccc;
        }

        h2 {
            font-size: 18px;
            margin-bottom: 10px;
        }

        h3 {
            margin-top: 24px;
            font-size: 16px;
        }

        .categoria-img {
            width: 100%;
            border-radius: 8px;
        }

        .precio-table td {
            padding: 6px 0;
        }

        .precio-table tr.total td {
            border-top: 1px solid #ccc;
            font-weight: bold;
        }

        .precio-table tr.total td:last-child {
            color: #D6121F;
        }

        .footer {
            margin-top: 40px;
            color: #555;
        }
    </style>
</head>
<body>

    <!-- ENCABEZADO -->
    <table class="header">
        <tr>
            <td>
                <img src="{{ $logoPath }}" class="logo">
            </td>
            <td class="folio">
                <strong>NO. DE COTIZACIÓN</strong><br>
                <span>{{ $folio }}</span><br>
                <small>Fecha: {{ $fechaHoy }}</small>
            </td>
        </tr>
    </table>

    <hr>

    <!-- RESUMEN -->
    <h2>Resumen de tu cotización</h2>

    <p><strong>Entrega:</strong> {{ $pickup_name }} ({{ $pickup_date }} {{ $pickup_time }})</p>
    <p><strong>Devolución:</strong> {{ $dropoff_name }} ({{ $dropoff_date }} {{ $dropoff_time }})</p>
    <p><strong>Días:</strong> {{ $dias }}</p>

    <!-- CATEGORÍA -->
    <h3>Categoría seleccionada</h3>

    <table cellpadding="6">
        <tr>
            <td width="30%">
                <img src="{{ $imgCategoria }}" class="categoria-img">
            </td>
            <td width="70%" style="vertical-align: top;">
                <strong style="font-size:16px;">{{ $categoria->nombre }}</strong><br>
                <small>{{ $categoria->descripcion }}</small><br>
                <small>
                    Tarifa base diaria:
                    ${{ number_format($tarifaDiaria, 2) }} MXN
                </small>
            </td>
        </tr>
    </table>

    <!-- EXTRAS -->
    <h3>Opciones seleccionadas</h3>

    <ul>
        {!! $extrasList !!}
    </ul>

    <!-- PRECIOS -->
    <h3>Detalles del precio</h3>

    <table class="precio-table">
        <tr>
            <td>Tarifa base</td>
            <td style="text-align:right;">
                ${{ number_format($tarifaDiaria * $dias, 2) }} MXN
            </td>
        </tr>
        <tr>
            <td>Opciones</td>
            <td style="text-align:right;">
                ${{ number_format($total - $iva - ($tarifaDiaria * $dias), 2) }} MXN
            </td>
        </tr>
        <tr>
            <td>Cargos e IVA</td>
            <td style="text-align:right;">
                ${{ number_format($iva, 2) }} MXN
            </td>
        </tr>
        <tr class="total">
            <td>TOTAL</td>
            <td style="text-align:right;">
                ${{ number_format($total, 2) }} MXN
            </td>
        </tr>
    </table>

    <div class="footer">
        Gracias por elegir <strong>Viajero Car Rental</strong>.
    </div>

</body>
</html>
