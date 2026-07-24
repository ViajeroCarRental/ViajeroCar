<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Responsiva de Conductor</title>

    {{-- ============================================================
         ESTILOS (mismos que pdfStyles del convenio)
    ============================================================ --}}
    <style>
        @page {
            margin: 100px 42px 65px 42px;
        }

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            color: #1f2937;
            font-family: DejaVu Sans, sans-serif;
            font-size: 10.5px;
            line-height: 1.45;
        }

        .pdf-header {
            position: fixed;
            top: -72px;
            left: 0;
            right: 0;
            height: 68px;
            border-bottom: 2px solid #b91c1c;
            padding-bottom: 3.5px;
        }

        .header-table,
        .header-table tr,
        .header-table td {
            border: none;
            padding: 0;
        }

        .header-logo {
            width: 18%;
            vertical-align: middle;
        }

        .logo-img {
            width: 120px;
            height: auto;
        }

        .header-info {
            width: 62%;
            vertical-align: middle;
        }

        .pdf-title {
            color: #b91c1c;
            font-size: 16px;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: .2px;
            line-height: 1;
        }

        .pdf-subtitle {
            margin-top: 3px;
            color: #111827;
            font-size: 9px;
            font-weight: bold;
            text-transform: uppercase;
            line-height: 1;
        }

        .company-data {
            margin-top: 3px;
            color: #4b5563;
            font-size: 7.8px;
            line-height: 1.05;
        }

        .header-folio {
            width: 20%;
            text-align: right;
            vertical-align: top;
        }

        .folio-label {
            color: #6b7280;
            font-size: 10px;
            text-transform: uppercase;
            line-height: 1;
        }

        .folio-number {
            margin-top: 4px;
            color: #b91c1c;
            font-size: 16px;
            font-weight: bold;
            line-height: 1;
        }

        .folio-date {
            margin-top: 8px;
            color: #374151;
            font-size: 10px;
            line-height: 1;
        }

        .brand-name {
            color: #111827;
            font-weight: bold;
        }

        .section {
            margin-bottom: 14px;
            page-break-inside: avoid;
        }

        .section-title {
            margin: 0 0 8px;
            padding: 7px 10px;
            color: #ffffff;
            background: #b91c1c;
            font-size: 11px;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: .3px;
        }

        .box {
            border: 1px solid #d1d5db;
            padding: 10px;
            background: #ffffff;
        }

        .muted {
            color: #6b7280;
        }

        .small {
            font-size: 9px;
        }

        .text-center {
            text-align: center;
        }

        .text-right {
            text-align: right;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th {
            padding: 7px 6px;
            color: #ffffff;
            background: #991b1b;
            border: 1px solid #7f1d1d;
            font-size: 9px;
            text-transform: uppercase;
        }

        td {
            padding: 6px;
            border: 1px solid #d1d5db;
            vertical-align: top;
        }

        .clause {
            margin-bottom: 8px;
            text-align: justify;
        }

        .clause-number {
            color: #b91c1c;
            font-weight: bold;
        }

        /* ===================== FIRMAS (3 celdas) ===================== */
        .signature-grid {
            width: 100%;
            margin-top: 34px;
        }

        .signature-cell {
            width: 33.33%;
            padding: 0 8px;
            text-align: center;
            vertical-align: bottom;
        }

        .signature-img {
            display: block;
            width: 170px;
            height: 70px;
            object-fit: contain;
            margin: 0 auto 8px;
        }

        .signature-box {
            height: 78px;
            display: flex;
            align-items: flex-end;
            justify-content: center;
        }

        .signature-line {
            border-top: 1px solid #111827;
            padding-top: 6px;
            margin-top: 6px;
            font-size: 9px;
            font-weight: bold;
        }

        .signature-role {
            font-size: 8px;
            color: #6b7280;
            margin-top: 2px;
        }

        .pdf-footer {
            position: fixed;
            bottom: -48px;
            left: 0;
            right: 0;
            height: 34px;
            border-top: 1px solid #d1d5db;
            color: #6b7280;
            font-size: 7.8px;
            padding-top: 7px;
        }

        .footer-table,
        .footer-table tr,
        .footer-table td {
            border: none;
            padding: 0;
        }

        .footer-left {
            width: 60%;
            text-align: left;
        }

        .footer-right {
            width: 40%;
            text-align: right;
        }

        .page-break {
            page-break-after: always;
        }

        /* ===================== TABLA DE DATOS (cliente/conductor) ===================== */
        .client-table th {
            background: #b91c1c;
            color: #ffffff;
            font-size: 9px;
            text-align: center;
            letter-spacing: .4px;
        }

        .client-table td {
            font-size: 9px;
            padding: 6px 7px;
        }

        .client-table .label {
            width: 16%;
            background: #f3f4f6;
            color: #374151;
            font-weight: bold;
        }

        .client-table td:nth-child(2) {
            width: 38%;
        }

        .client-table td:nth-child(4) {
            width: 30%;
        }

        /* ===================== TABLA DE TARIFAS (igual que el convenio) ===================== */
        .rate-pdf-table {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
        }

        .rate-pdf-table th {
            background: #b91c1c;
            color: #ffffff;
            border: 1px solid #7f1d1d;
            padding: 7px 5px;
            font-size: 8.5px;
            text-align: center;
            text-transform: uppercase;
        }

        .rate-pdf-table td {
            border: 1px solid #d1d5db;
            padding: 6px 5px;
            font-size: 8.7px;
            vertical-align: middle;
        }

        .rate-pdf-table tbody tr:nth-child(even) td {
            background: #f9fafb;
        }

        .rate-pdf-table .col-category {
            width: 24%;
            font-weight: bold;
        }

        .rate-pdf-table .col-money {
            width: 14%;
            text-align: right;
        }

        .rate-pdf-table .col-protection {
            width: 20%;
        }

        .rate-pdf-table .col-total {
            width: 14%;
            text-align: right;
            font-weight: bold;
            color: #991b1b;
        }
    </style>
</head>

<body>

    @php
        // Codigo de responsiva: 3 digitos (001, 002...), igual que el convenio
        $folio = str_pad(
            $responsiva->id_responsiva ?? ($convenio->id_convenio ?? 0),
            3, '0', STR_PAD_LEFT
        );

        // Numero de convenio generado (referencia cruzada)
        $folioConvenio = str_pad($convenio->id_convenio ?? 0, 3, '0', STR_PAD_LEFT);

        $fecha = !empty($convenio->created_at)
            ? \Carbon\Carbon::parse($convenio->created_at)->locale('es')->translatedFormat('d/M/Y')
            : now()->locale('es')->translatedFormat('d/M/Y');
        $firmaConductor = $conductor->firma ?? ($convenio->firma_conductor ?? null);
    @endphp

    <header class="pdf-header">
        <table class="header-table">
            <tr>
                <td class="header-logo">
                    <img src="{{ public_path('img/Logo5.png') }}" class="logo-img">
                </td>

                <td class="header-info">
                    <div class="pdf-title">Responsiva de Conductor</div>
                    <div class="pdf-subtitle">Grupo Viajero Car Rental</div>

                    <div class="company-data">
                        Tel. {{ $telefonoEmpresa ?? '442 716 9793' }}<br>
                        Direccion: {{ $direccionEmpresa ?? 'Business Center INNERA Central Park, Armando Birlain Shaffler 2001 Torre2, 9C, 76090 Santiago de Queretaro, Qro.' }}
                    </div>
                </td>

                <td class="header-folio">
                    <div class="folio-label">Responsiva</div>
                    <div class="folio-number">#{{ $folio }}</div>
                    <div class="folio-date">{{ $fecha }}</div>
                </td>
            </tr>
        </table>
    </header>

    <main>

        <section class="section">
            <p style="text-align: justify;">
                La presente <strong>carta responsiva</strong> se emite en relacion con el
                <strong>Convenio Member Prefer No. {{ $folioConvenio }}</strong>, celebrado entre
                <strong>GRUPO VIAJERO CAR RENTAL</strong>, en lo sucesivo
                <strong>"EL ARRENDADOR"</strong>, y
                <strong>{{ $moral->razon_social ?? (trim(($cliente->nombres ?? '') . ' ' . ($cliente->apellidos ?? '')) ?: 'EL CLIENTE') }}</strong>,
                en lo sucesivo <strong>"EL CLIENTE"</strong>. Mediante este documento,
                <strong>{{ $conductor->nombre ?? 'EL CONDUCTOR' }}</strong>,
                en lo sucesivo <strong>"EL CONDUCTOR"</strong>, se hace responsable del uso del vehiculo
                arrendado conforme a las condiciones aqui descritas.
            </p>
        </section>

        <section class="section">
            <h2 class="section-title">Datos del cliente (Persona Moral)</h2>

            <div class="box">
                <table class="client-table">
                    <thead>
                        <tr>
                            <th colspan="2">Empresa</th>
                            <th colspan="2">Representante legal</th>
                        </tr>
                    </thead>

                    <tbody>
                        <tr>
                            <td class="label">Razon social</td>
                            <td>{{ $moral->razon_social ?? ($facturacion->razon_social ?? '-') }}</td>

                            <td class="label">Nombre</td>
                            <td>{{ $moral->representante_nombre ?? '-' }}</td>
                        </tr>

                        <tr>
                            <td class="label">RFC</td>
                            <td>{{ $facturacion->rfc ?? '-' }}</td>

                            <td class="label">Correo</td>
                            <td>{{ $moral->representante_correo ?? '-' }}</td>
                        </tr>

                        <tr>
                            <td class="label">Telefono</td>
                            <td>{{ $moral->telefono_empresa ?? '-' }}</td>

                            <td class="label">Telefono</td>
                            <td>{{ $moral->representante_telefono ?? '-' }}</td>
                        </tr>

                        <tr>
                            <td class="label">Correo</td>
                            <td>{{ $moral->correo_empresa ?? '-' }}</td>

                            <td class="label">No. Identificacion</td>
                            <td>{{ $moral->representante_identificacion ?? '-' }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </section>

        <section class="section">
            <h2 class="section-title">Datos del conductor</h2>

            <div class="box">
                <table class="client-table">
                    <thead>
                        <tr>
                            <th colspan="2">Conductor</th>
                            <th colspan="2">Documentacion</th>
                        </tr>
                    </thead>

                    <tbody>
                        <tr>
                            <td class="label">Nombre</td>
                            <td>{{ $conductor->nombre ?? '-' }}</td>

                            <td class="label">No. Identificacion</td>
                            <td>{{ $conductor->identificacion ?? '-' }}</td>
                        </tr>

                        <tr>
                            <td class="label">Correo</td>
                            <td>{{ $conductor->correo ?? '-' }}</td>

                            <td class="label">No. Licencia</td>
                            <td>{{ $conductor->licencia ?? '-' }}</td>
                        </tr>

                        <tr>
                            <td class="label">Telefono</td>
                            <td>{{ $conductor->telefono ?? '-' }}</td>

                            <td class="label">Vigencia licencia</td>
                            <td>
                                {{ !empty($conductor->vigencia_licencia)
                                    ? \Carbon\Carbon::parse($conductor->vigencia_licencia)->format('d/m/Y')
                                    : '-' }}
                            </td>
                        </tr>

                        <tr>
                            <td class="label">Fecha nacimiento</td>
                            <td>
                                {{ !empty($conductor->nacimiento)
                                    ? \Carbon\Carbon::parse($conductor->nacimiento)->format('d/m/Y')
                                    : '-' }}
                            </td>

                            <td class="label">Convenio No.</td>
                            <td>#{{ $folioConvenio }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </section>

        {{-- ============================================================
             TARIFAS AUTORIZADAS (heredadas de la empresa / "papá")
             Misma tabla que el convenio. Se jalan de cliente_tarifa_convenio
             ligadas al id_cliente del convenio.
        ============================================================ --}}
        <section class="section">
            <h2 class="section-title">Tarifas autorizadas</h2>

            <div class="box">
                <table class="rate-pdf-table">
                    <thead>
                        <tr>
                            <th class="col-category">Categoria</th>
                            <th class="col-money">Diaria</th>
                            <th class="col-money">Semanal</th>
                            <th class="col-money">Mensual</th>
                            <th class="col-protection">Proteccion</th>
                            <th class="col-total">Total diario</th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse(($tarifas ?? []) as $tarifa)
                            <tr>
                                <td class="col-category">{{ $tarifa->categoria ?? 'Sin categoria' }}</td>
                                <td class="col-money">${{ number_format($tarifa->tarifa_diaria ?? 0, 2) }}</td>
                                <td class="col-money">${{ number_format($tarifa->tarifa_semanal ?? 0, 2) }}</td>
                                <td class="col-money">${{ number_format($tarifa->tarifa_mensual ?? 0, 2) }}</td>
                                <td class="col-protection">{{ $tarifa->paquete_nombre ?? 'Sin proteccion' }}</td>
                                <td class="col-total">${{ number_format($tarifa->total_diario ?? 0, 2) }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center muted">No hay tarifas registradas.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>

        @php
            $aRomano = function ($num) {
                $num = (int) $num;
                if ($num <= 0) return $num;

                $mapa = [
                    'M' => 1000, 'CM' => 900, 'D' => 500, 'CD' => 400,
                    'C' => 100, 'XC' => 90, 'L' => 50, 'XL' => 40,
                    'X' => 10, 'IX' => 9, 'V' => 5, 'IV' => 4, 'I' => 1,
                ];

                $resultado = '';
                foreach ($mapa as $romano => $valor) {
                    while ($num >= $valor) {
                        $resultado .= $romano;
                        $num -= $valor;
                    }
                }
                return $resultado;
            };

            $contadorClausula = 0;
        @endphp

        <section class="section">
            <h2 class="section-title">Clausulas de la responsiva</h2>

            <div class="box">
                <p class="clause">
                    <span class="clause-number">{{ $aRomano(++$contadorClausula) }}. RESPONSABILIDAD.</span>
                    EL CONDUCTOR declara estar autorizado por EL CLIENTE para operar el vehiculo arrendado y
                    se compromete a utilizarlo de forma responsable, conforme a la ley y a las condiciones
                    pactadas en el convenio de referencia.
                </p>

                <p class="clause">
                    <span class="clause-number">{{ $aRomano(++$contadorClausula) }}. DANOS Y MULTAS.</span>
                    EL CONDUCTOR sera responsable por danos, multas, cargos, faltantes o cualquier uso indebido
                    del vehiculo durante el periodo en que lo tenga a su cargo.
                </p>

                <p class="clause">
                    <span class="clause-number">{{ $aRomano(++$contadorClausula) }}. USO PERSONAL E INTRANSFERIBLE.</span>
                    EL CONDUCTOR se obliga a no ceder, subarrendar ni permitir el uso del vehiculo a terceros
                    no autorizados por EL ARRENDADOR.
                </p>

                @if (isset($clausulas) && $clausulas->count())
                    @foreach ($clausulas as $clausula)
                        <p class="clause">
                            <span class="clause-number">{{ $aRomano(++$contadorClausula) }}.</span>
                            {!! nl2br(e($clausula->texto)) !!}
                        </p>
                    @endforeach
                @endif
            </div>
        </section>

        {{-- ============================================================
             FIRMAS (3 celdas: representante legal, conductor, asesor)
        ============================================================ --}}
        <section class="section" style="page-break-before: always;">
            <h2 class="section-title">Firmas</h2>

            <table class="signature-grid">
                <tr>
                    <td class="signature-cell">
                        <div class="signature-box">
                            @if (!empty($convenio->firma_representante))
                                <img class="signature-img" src="{{ $convenio->firma_representante }}">
                            @endif
                        </div>

                        <div class="signature-line">
                            {{ $moral->representante_nombre ?? 'Representante legal' }}
                        </div>

                        <div class="signature-role">
                            Representante legal
                        </div>
                    </td>

                    <td class="signature-cell">
                        <div class="signature-box">
                            @if (!empty($firmaConductor))
                                <img class="signature-img" src="{{ $firmaConductor }}">
                            @endif
                        </div>

                        <div class="signature-line">
                            {{ $conductor->nombre ?? 'Conductor' }}
                        </div>

                        <div class="signature-role">
                            Conductor autorizado
                        </div>
                    </td>

                    <td class="signature-cell">
                        <div class="signature-box">
                            @if (!empty($convenio->firma_asesor))
                                <img class="signature-img" src="{{ $convenio->firma_asesor }}">
                            @endif
                        </div>

                        <div class="signature-line">
                            Grupo Viajero Car Rental
                        </div>

                        <div class="signature-role">
                            Representante de la empresa
                        </div>
                    </td>
                </tr>
            </table>
        </section>

    </main>

    <footer class="pdf-footer">
        <table class="footer-table">
            <tr>
                <td class="footer-left">
                    (c) Grupo Viajero Car Rental - Tel. 442 716 9793
                </td>

                <td class="footer-right">
                    Responsiva de Conductor
                </td>
            </tr>
        </table>
    </footer>

</body>

</html>
