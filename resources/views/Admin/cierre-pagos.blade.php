<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            font-size: 13px;
            margin: 0;
            padding: 0;
            background: #f4f6fb;
        }

        .container {
            width: 100%;
            padding: 25px 40px;
            background: #fff;
        }

        h1, h2, h3 {
            color: #b1060f;
            margin-bottom: 6px;
        }

        .header {
            text-align: center;
            margin-bottom: 25px;
        }

        .header img {
            width: 140px;
        }

        .section {
            margin-top: 25px;
            padding: 15px;
            border-left: 5px solid #E50914;
            background: #fff2f2;
            border-radius: 10px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 12px;
            font-size: 12px;
        }

        table th {
            background: #E50914;
            color: #fff;
            padding: 8px;
            text-align: left;
        }

        table td {
            padding: 7px;
            border-bottom: 1px solid #ddd;
        }

        .totales {
            margin-top: 25px;
            text-align: right;
            font-size: 15px;
        }

        .totales strong {
            font-size: 17px;
            color: #b1060f;
        }
    </style>
</head>

<body>
<div class="container">

    <!-- DATOS GENERALES -->
    <div class="section">
        <h2>Datos del Cliente</h2>
        <p><strong>Cliente:</strong> {{ $reservacion->nombre_cliente }}</p>
        <p><strong>Correo:</strong> {{ $reservacion->email_cliente }}</p>
        <p><strong>Teléfono:</strong> {{ $reservacion->telefono_cliente }}</p>
    </div>

    <div class="section">
        <h2>Información de la Reservación</h2>
        <p><strong>Folio Reservación:</strong> {{ $reservacion->codigo }}</p>
        <p><strong>Folio Contrato:</strong> {{ $contrato->numero_contrato }}</p>
        <p><strong>Fecha Inicio:</strong> {{ $reservacion->fecha_inicio }}</p>
        <p><strong>Fecha Fin:</strong> {{ $reservacion->fecha_fin }}</p>
    </div>

    <!-- PAGOS -->
    <div class="section">
        <h2>Pagos Registrados</h2>

        @if(count($pagos))
        <table>
            <thead>
                <tr>
                    <th>Fecha</th>
                    <th>Método</th>
                    <th>Origen</th>
                    <th>Estatus</th>
                    <th>Referencia</th>
                    <th>Monto</th>
                </tr>
            </thead>
            <tbody>
            @foreach($pagos as $p)
                <tr>
                    <td>{{ $p->created_at }}</td>
                    <td>{{ $p->metodo }}</td>
                    <td>{{ $p->origen_pago }}</td>
                    <td>{{ $p->estatus }}</td>
                    <td>{{ $p->referencia_pasarela }}</td>
                    <td>${{ number_format($p->monto, 2) }}</td>
                </tr>
            @endforeach
            </tbody>
        </table>
        @else
            <p>No se registraron pagos.</p>
        @endif
    </div>

    <!-- SERVICIOS -->
    <div class="section">
        <h2>Servicios Adicionales</h2>

        @if(count($servicios))
        <table>
            <thead>
                <tr>
                    <th>Servicio</th>
                    <th>Cantidad</th>
                    <th>Precio Unit.</th>
                    <th>Total</th>
                </tr>
            </thead>
            <tbody>
            @foreach($servicios as $s)
                <tr>
                    <td>{{ $s->nombre }}</td>
                    <td>{{ $s->cantidad }}</td>
                    <td>${{ number_format($s->precio_unitario, 2) }}</td>
                    <td>${{ number_format($s->cantidad * $s->precio_unitario, 2) }}</td>
                </tr>
            @endforeach
            </tbody>
        </table>
        @else
            <p>No se registraron servicios adicionales.</p>
        @endif
    </div>

    <!-- SEGUROS -->
    <div class="section">
        <h2>Seguros Contratados</h2>

        @if(count($segurosPaquete) || count($segurosIndividuales))
        <table>
            <thead>
                <tr>
                    <th>Seguro</th>
                    <th>Precio/Día</th>
                    <th>Cantidad</th>
                    <th>Total</th>
                </tr>
            </thead>
            <tbody>

            @foreach($segurosPaquete as $sp)
                <tr>
                    <td>{{ $sp->nombre }}</td>
                    <td>${{ number_format($sp->precio_por_dia, 2) }}</td>
                    <td>{{ $dias }}</td>
                    <td>${{ number_format($sp->precio_por_dia * $dias, 2) }}</td>
                </tr>
            @endforeach

            @foreach($segurosIndividuales as $si)
                <tr>
                    <td>{{ $si->nombre }}</td>
                    <td>${{ number_format($si->precio_por_dia, 2) }}</td>
                    <td>{{ $si->cantidad }}</td>
                    <td>${{ number_format($si->precio_por_dia * $si->cantidad, 2) }}</td>
                </tr>
            @endforeach

            </tbody>
        </table>
        @else
            <p>No se contrataron seguros.</p>
        @endif
    </div>

    <!-- CARGOS ADICIONALES -->
    <div class="section">
        <h2>Cargos Adicionales</h2>

        @if(count($cargos))
        <table>
            <thead>
                <tr>
                    <th>Concepto</th>
                    <th>Monto</th>
                    <th>Notas</th>
                </tr>
            </thead>
            <tbody>
            @foreach($cargos as $c)
                <tr>
                    <td>{{ $c->concepto }}</td>
                    <td>${{ number_format($c->monto, 2) }}</td>
                    <td>{{ $c->notas }}</td>
                </tr>
            @endforeach
            </tbody>
        </table>
        @else
            <p>No se registraron cargos adicionales.</p>
        @endif
    </div>

    <!-- TOTAL FINAL -->
    <div class="totales">
        <p><strong>Total Final:</strong> ${{ number_format($reservacion->total, 2) }} MXN</p>
    </div>

</div>
</body>
</html>
