<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Convenio Member Prefer</title>
    @include('admin.pdfStyles')
</head>

<body>

    @include('admin.pdfHeader')

    <main>

        <section class="section">
            <p style="text-align: justify;">
                El presente convenio de prestación de servicios de arrendamiento de vehículos se celebra entre
                <strong>GRUPO VIAJERO CAR RENTAL</strong>, en lo sucesivo
                <strong>"EL ARRENDADOR"</strong>, y
                <strong>{{ $cliente->nombres ?? ($cliente->nombre ?? ($cliente->nombre_completo ?? 'EL CLIENTE')) }}</strong>,
                en lo sucesivo <strong>"EL CLIENTE"</strong>, quienes manifiestan su voluntad de sujetarse
                a las declaraciones, cláusulas y anexos descritos en el presente documento.
            </p>
        </section>

        <section class="section">

            <h2 class="section-title">
                Definiciones
            </h2>

            <div class="box">

                <p class="clause">
                    <span class="clause-number">ARRENDADOR.</span>
                    Grupo Viajero Car Rental, empresa dedicada al arrendamiento de vehículos automotores.
                </p>

                <p class="clause">
                    <span class="clause-number">CLIENTE.</span>
                    Persona física o moral que acepta las condiciones establecidas en el presente convenio.
                </p>

                <p class="clause">
                    <span class="clause-number">VEHÍCULO.</span>
                    Unidad automotriz entregada temporalmente al cliente conforme a este convenio.
                </p>

                <p class="clause">
                    <span class="clause-number">PERIODO DE RENTA.</span>
                    Tiempo comprendido entre la entrega y devolución del vehículo.
                </p>

                <p class="clause">
                    <span class="clause-number">TARIFAS.</span>
                    Importes comerciales autorizados para el cliente y anexados al presente convenio.
                </p>

                <p class="clause">
                    <span class="clause-number">PROTECCIONES.</span>
                    Coberturas contratadas por el cliente para disminuir su responsabilidad económica durante la renta.
                </p>

            </div>

        </section>

        @php
            // Función para convertir número a romano
            $aRomano = function ($num) {
                $num = (int) $num;
                if ($num <= 0) {
                    return $num;
                }

                $mapa = [
                    'M' => 1000,
                    'CM' => 900,
                    'D' => 500,
                    'CD' => 400,
                    'C' => 100,
                    'XC' => 90,
                    'L' => 50,
                    'XL' => 40,
                    'X' => 10,
                    'IX' => 9,
                    'V' => 5,
                    'IV' => 4,
                    'I' => 1,
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

            // Contador global de cláusulas (arranca en 0, se incrementa antes de usar)
            $contadorClausula = 0;
        @endphp

        <section class="section">
            <h2 class="section-title">Cláusulas generales</h2>

            <div class="box">
                <p class="clause">
                    <span class="clause-number">{{ $aRomano(++$contadorClausula) }}. OBJETO.</span>
                    El presente convenio tiene por objeto establecer los términos bajo los cuales EL ARRENDADOR podrá
                    proporcionar a EL CLIENTE servicios de arrendamiento de vehículos.
                </p>

                <p class="clause">
                    <span class="clause-number">{{ $aRomano(++$contadorClausula) }}. TARIFAS.</span>
                    Las tarifas aplicables serán aquellas señaladas en el anexo correspondiente del presente convenio.
                </p>

                <p class="clause">
                    <span class="clause-number">{{ $aRomano(++$contadorClausula) }}. USO DEL VEHÍCULO.</span>
                    EL CLIENTE se obliga a utilizar el vehículo de forma responsable, conforme a la ley y a las
                    condiciones pactadas.
                </p>

                <p class="clause">
                    <span class="clause-number">{{ $aRomano(++$contadorClausula) }}. RESPONSABILIDAD.</span>
                    EL CLIENTE será responsable por daños, multas, cargos, faltantes o cualquier uso indebido del
                    vehículo durante el periodo de renta.
                </p>

                <p class="clause">
                    <span class="clause-number">{{ $aRomano(++$contadorClausula) }}. DEVOLUCIÓN.</span>
                    EL CLIENTE deberá devolver el vehículo en la fecha, hora, condiciones y lugar acordados.
                </p>
            </div>
        </section>

        @if ($clausulas->count())
            <section class="section">
                <h2 class="section-title">Cláusulas adicionales</h2>    

                <div class="box">
                    @foreach ($clausulas as $clausula)
                        <p class="clause">
                            <span class="clause-number">{{ $aRomano(++$contadorClausula) }}.</span>
                            {!! nl2br(e($clausula->texto)) !!}
                        </p>
                    @endforeach
                </div>
            </section>
        @endif

        <section class="section page-break">

            <h2 class="section-title">
                Anexo I · Datos del cliente
            </h2>

            <div class="box">

                <table class="client-table">

                    <thead>

                        <tr>

                            <th colspan="2">
                                Cliente
                            </th>

                            <th colspan="2">
                                Documentación
                            </th>

                        </tr>

                    </thead>

                    <tbody>

                        <tr>

                            <td class="label">
                                Nombre
                            </td>

                            <td>
                                {{ $cliente->nombres ?? ($cliente->nombre ?? ($cliente->nombre_completo ?? 'No registrado')) }}
                            </td>

                            <td class="label">
                                Tipo ID
                            </td>

                            <td>
                                {{ strtoupper($cliente->tipo_identificacion ?? '-') }}
                            </td>

                        </tr>

                        <tr>

                            <td class="label">
                                Correo
                            </td>

                            <td>
                                {{ $cliente->correo ?? ($cliente->email ?? '-') }}
                            </td>

                            <td class="label">
                                No. Identificación
                            </td>

                            <td>
                                {{ $cliente->numero_identificacion ?? '-' }}
                            </td>

                        </tr>

                        <tr>

                            <td class="label">
                                Teléfono
                            </td>

                            <td>
                                {{ $cliente->numero ?? ($cliente->telefono ?? ($cliente->celular ?? '-')) }}
                            </td>

                            <td class="label">
                                No. Licencia
                            </td>

                            <td>
                                {{ $cliente->numero_licencia ?? '-' }}
                            </td>

                        </tr>

                        <tr>

                            <td class="label">
                                Fecha nacimiento
                            </td>

                            <td>
                                {{ $cliente->fecha_nacimiento ? \Carbon\Carbon::parse($cliente->fecha_nacimiento)->format('d/m/Y') : '-' }}
                            </td>

                            <td class="label">
                                Vigencia licencia
                            </td>

                            <td>
                                {{ $cliente->vigencia_licencia ? \Carbon\Carbon::parse($cliente->vigencia_licencia)->format('d/m/Y') : '-' }}
                            </td>

                        </tr>

                    </tbody>

                </table>

            </div>

        </section>

        @if (($convenio->tipo ?? '') === 'moral')
            <section class="section page-break">

                <h2 class="section-title">
                    Anexo II · Datos de la empresa
                </h2>

                <div class="box">

                    <table class="client-table">

                        <thead>

                            <tr>

                                <th colspan="2">
                                    Empresa
                                </th>

                                <th colspan="2">
                                    Representante legal
                                </th>

                            </tr>

                        </thead>

                        <tbody>

                            <tr>

                                <td class="label">
                                    Razón social
                                </td>

                                <td>
                                    {{ $moral->razon_social ?? ($facturacion->razon_social ?? '-') }}
                                </td>

                                <td class="label">
                                    Nombre
                                </td>

                                <td>
                                    {{ $moral->representante_nombre ?? '-' }}
                                </td>

                            </tr>

                            <tr>

                                <td class="label">
                                    RFC
                                </td>

                                <td>
                                    {{ $facturacion->rfc ?? '-' }}
                                </td>

                                <td class="label">
                                    Correo
                                </td>

                                <td>
                                    {{ $moral->representante_correo ?? '-' }}
                                </td>

                            </tr>

                            <tr>

                                <td class="label">
                                    Teléfono
                                </td>

                                <td>
                                    {{ $moral->telefono_empresa ?? '-' }}
                                </td>

                                <td class="label">
                                    Teléfono
                                </td>

                                <td>
                                    {{ $moral->representante_telefono ?? '-' }}
                                </td>

                            </tr>

                            <tr>

                                <td class="label">
                                    Correo
                                </td>

                                <td>
                                    {{ $moral->correo_empresa ?? '-' }}
                                </td>

                                <td class="label">
                                    No. Identificación
                                </td>

                                <td>
                                    {{ $moral->representante_identificacion ?? '-' }}
                                </td>

                            </tr>

                        </tbody>

                    </table>

                </div>

            </section>
        @endif

        @if (($convenio->tipo ?? '') === 'moral')

            <section class="section">

                <h2 class="section-title">
                    Anexo III · Datos fiscales
                </h2>

                <div class="box">

                    <table class="client-table">

                        <thead>
                            <tr>
                                <th colspan="2">Facturación</th>
                                <th colspan="2">Domicilio fiscal</th>
                            </tr>
                        </thead>

                        <tbody>

                            <tr>
                                <td class="label">RFC</td>
                                <td>{{ $facturacion->rfc ?? '-' }}</td>

                                <td class="label">Calle</td>
                                <td>
                                    {{ $facturacion->calle ?? '-' }}
                                    {{ $facturacion->numero_exterior ?? '' }}
                                    @if (!empty($facturacion->numero_interior))
                                        Int. {{ $facturacion->numero_interior }}
                                    @endif
                                </td>
                            </tr>

                            <tr>
                                <td class="label">Razón social</td>
                                <td>{{ $facturacion->razon_social ?? '-' }}</td>

                                <td class="label">Colonia</td>
                                <td>{{ $facturacion->colonia ?? '-' }}</td>
                            </tr>

                            <tr>
                                <td class="label">Uso CFDI</td>
                                <td>{{ $facturacion->uso_cfdi ?? '-' }}</td>

                                <td class="label">Municipio</td>
                                <td>{{ $facturacion->municipio ?? '-' }}</td>
                            </tr>

                            <tr>
                                <td class="label">Régimen fiscal</td>
                                <td>{{ $facturacion->regimen_fiscal ?? '-' }}</td>

                                <td class="label">Estado</td>
                                <td>{{ $facturacion->estado ?? '-' }}</td>
                            </tr>

                            <tr>
                                <td class="label">Correo factura</td>
                                <td>{{ $facturacion->correo_factura ?? '-' }}</td>

                                <td class="label">Código postal</td>
                                <td>{{ $facturacion->codigo_postal ?? '-' }}</td>
                            </tr>

                        </tbody>

                    </table>

                </div>

            </section>

        @endif

        <section class="section page-break">
            <h2 class="section-title">
                Anexo {{ ($convenio->tipo ?? '') === 'moral' ? 'IV' : 'II' }} · Tarifas autorizadas
            </h2>

            <div class="box">
                <table class="rate-pdf-table">
                    <thead>
                        <tr>
                            <th class="col-category">Categoría</th>
                            <th class="col-money">Diaria</th>
                            <th class="col-money">Semanal</th>
                            <th class="col-money">Mensual</th>
                            <th class="col-protection">Protección</th>
                            <th class="col-total">Total diario</th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse($tarifas as $tarifa)
                            <tr>
                                <td class="col-category">{{ $tarifa->categoria ?? 'Sin categoría' }}</td>
                                <td class="col-money">${{ number_format($tarifa->tarifa_diaria ?? 0, 2) }}</td>
                                <td class="col-money">${{ number_format($tarifa->tarifa_semanal ?? 0, 2) }}</td>
                                <td class="col-money">${{ number_format($tarifa->tarifa_mensual ?? 0, 2) }}</td>
                                <td class="col-protection">{{ $tarifa->paquete_nombre ?? 'Sin protección' }}</td>
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

        <section class="section">

            <h2 class="section-title">
                Firmas
            </h2>

            <table class="signature-grid">

                <tr>

                    @if (($convenio->tipo ?? '') === 'moral')

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

                                @if (!empty($convenio->firma_conductor))
                                    <img class="signature-img" src="{{ $convenio->firma_conductor }}">
                                @endif

                            </div>

                            <div class="signature-line">
                                Conductor autorizado
                            </div>

                            <div class="signature-role">
                                Usuario autorizado
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
                    @else
                        <td class="signature-cell">

                            <div class="signature-box">

                                @if (!empty($convenio->firma_cliente))
                                    <img class="signature-img" src="{{ $convenio->firma_cliente }}">
                                @endif

                            </div>

                            <div class="signature-line">
                                {{ $cliente->nombres ?? ($cliente->nombre ?? ($cliente->nombre_completo ?? 'Cliente')) }}
                            </div>

                            <div class="signature-role">
                                Cliente
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

                    @endif

                </tr>

            </table>

        </section>

    </main>

    @include('admin.pdfFooter')

</body>

</html>
