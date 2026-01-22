<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Cambio de Auto – PDF</title>

    <style>
        /* ==============================
   CONFIGURACIÓN DE LA HOJA PDF
============================== */
@page {
    /* Igual estilo que tus otros PDFs */
    size: legal portrait;
    margin: 10mm;
}

:root{
    --brand:#D6001C;
    --ink:#111827;
    --muted:#6b7280;
    --line:#e5e7eb;
    --stroke:#e5e7eb;
    --paper:#ffffff;
    --bg-soft:#f8fafc;
}

*{
    box-sizing:border-box;
}

html, body{
    margin: 0;
    padding: 0;
    background: #f3f4f6;                      /* fondo gris suave */
    color: var(--ink);
    font-family: DejaVu Sans, Arial, sans-serif;
    font-size: 11px;
}


/* Envoltura general del documento */
.checklist-pdf-container{
    width:100%;
    margin:0;
    padding:0;
    background:#fff;
}

/* “Hoja” interna centrada, estilo parecido a la vista */
.checklist2-container {
    background: #ffffff;
    padding: 35px;                     /* como en la vista */
    margin: 8mm auto;                  /* un poco de margen arriba/abajo */
    max-width: 195mm;                  /* ancho adaptado a legal */
    border-radius: 10px;               /* esquinas redondeadas */
    box-shadow: 0 6px 25px rgba(0,0,0,0.15); /* sombra parecida */
    font-family: 'Arial', sans-serif;  /* igual que la vista */
}


/* Títulos básicos */
h1,h2,h3,h4,p{
    margin:0;
    padding:0;
}

/* ==========================
   ENCABEZADO
========================== */
.cl2-header {
    display: table;                   /* tabla en lugar de flex */
    width: 100%;
    border-bottom: 3px solid #E50914;
    padding-bottom: 15px;
}

/* Cada bloque del header será una celda de la tabla */
.cl2-logo,
.cl2-title-block,
.cl2-ra-box {
    display: table-cell;
    vertical-align: top;
}

/* Logo a la izquierda */
.cl2-logo img {
    width: 220px;
    height: auto;
    display: block;
}

/* Bloque central */
.cl2-title-block {
    text-align: center;
    padding: 0 20px;
}

.cl2-title-block h1 {
    margin: 0;
    font-size: 20px;
    font-weight: bold;
    color: #222;
}

.cl2-title-block h2 {
    margin-top: 2px;
    font-size: 10px;
    font-weight: 600;
    color: #444;
}

.office-info {
    margin-top: 4px;
    font-size: 10px;
    color: #555;
    line-height: 1.4;
}

/* Bloque de RA a la derecha */
.cl2-ra-box {
    text-align: right;
    padding-left: 20px;
}

.cl2-ra-box .label {
    font-size: 13px;
    font-weight: bold;
    color: #333;
}

.cl2-ra-box .value {
    font-size: 18px;
    font-weight: bold;
    margin-bottom: 5px;
    color: #E50914;
}

/* Versión pequeña para fecha */
.cl2-ra-box .label.small {
    font-size: 11px;
}

.cl2-ra-box .value.small {
    font-size: 12px;
    font-weight: 700;
    color: #111827;
}



/* ==========================
   COLUMNAS PRINCIPALES
========================== */
.cl2-columns {
    display: table;          /* en lugar de flex */
    width: 100%;
    table-layout: fixed;
    margin-top: 26px;
}

.cl2-col {
    display: table-cell;     /* cada columna es una celda */
    width: 50%;
    vertical-align: top;     /* que arranquen arriba igual que en la vista */
    padding: 0 8px;          /* un poco de espacio lateral */
}


/* Títulos de bloque rojo */
.cl2-section-title {
    background:#E50914;
    color:#fff;
    padding:7px 0;
    text-align:center;
    font-size:14px;
    font-weight:bold;
    border-radius:4px;
    margin-bottom:10px;
}

/* Tablas de datos (autos) */
.cl2-table {
    width:100%;
    border-collapse:collapse;
    font-size:12px;
    margin-bottom:14px;
}

.cl2-table th {
    background:#f8f8f8;
    padding:5px 6px;
    border:1px solid #ccc;
    width:45%;
    text-align:left;
}

.cl2-table td {
    padding:5px 6px;
    border:1px solid #ccc;
}

/* ==========================
   DIAGRAMA + DAÑOS
========================== */
.cl2-car-diagram {
    margin-top:16px;
    display:flex;
    flex-direction:column;
    align-items:center;
    gap:10px;
}

.cl2-car-svg-box {
    width:260px;
    max-width:100%;
    position:relative;
}

/* Imagen del diagrama cuando es <img> (lado EMPRESA) */
.cl2-car-diagram img {
    width:260px;        /* mismo ancho que el SVG de la derecha */
    max-width:100%;
    height:auto;
    display:block;
}


.cl2-car-svg-box .car-svg {
    width:100%;
    height:auto;
    display:block;
}

.cl2-car-hint {
    font-size:11px;
    color:#666;
    text-align:center;
}

/* Puntos del SVG (solo visuales en PDF) */
.point-dot {
    fill:rgba(255,255,255,0.95);
    stroke:#ff4d6a;
    stroke-width:4;
}

.point-dot.selected {
    stroke-width:6;
}

/* ==========================
   TABLAS DE DAÑOS
========================== */
.cl2-danos-table {
    width:100%;
    border-collapse:collapse;
    font-size:11px;
    margin-top:4px;
}

.cl2-danos-table thead {
    background:#f0f0f0;
}

.cl2-danos-table th,
.cl2-danos-table td {
    padding:5px 6px;
    border:1px solid #e0e0e0;
    text-align:left;
}

.cl2-danos-table th:nth-child(3),
.cl2-danos-table td:nth-child(3) {
    text-align:right;
    white-space:nowrap;
}

.cl2-danos-table td:nth-child(4),
.cl2-danos-table td:nth-child(5) {
    text-align:center;
    white-space:nowrap;
}

.cl2-danos-empty {
    text-align:center;
    font-style:italic;
    color:#888;
}

.cl2-danos-total {
    margin-top:4px;
    width:100%;
    text-align:right;
    font-size:11px;
    font-weight:700;
    color:#333;
}

/* un poco más de aire para la tabla de daños del cliente */
.cl2-danos-table[data-context="cliente"] {
    margin-top:8px;
}

/* ==========================
   FIRMAS
========================== */
.cl2-sign-box {
    margin-top:22px;
    text-align:center;
    font-size:12px;
}

.cl2-sign-box .label {
    font-weight:600;
    letter-spacing:0.03em;
    text-transform:uppercase;
    color:#4b5563;
}

.cl2-sign-box .line {
    margin:8px 0;
    padding:4px 0;
    border-bottom:1px solid #d1d5db;
    min-height:55px;
    display:flex;
    align-items:flex-end;
    justify-content:center;
}

/* Firma del cliente/asesor en imagen */
.cl2-sign-img,
.firma-img {
    max-height:60px;
    max-width:220px;
    object-fit:contain;
    display:block;
}

.cl2-sign-box .name {
    margin-top:5px;
    font-weight:500;
    color:#111827;
}

    </style>
</head>
<body>

<div class="checklist-pdf-container">
    <div class="checklist2-container">

        <!-- ENCABEZADO -->
        <header class="cl2-header">

            <div class="cl2-logo">
                {{-- Si usas Dompdf y no te carga, puedes cambiar a: src="{{ public_path('img/Logotipo Fondo.jpg') }}" --}}
                <img src="{{ public_path('img/Logotipo Fondo.jpg') }}" alt="Viajero Car Rental">
            </div>

            <div class="cl2-title-block">
                <h1>VIAJERO CAR RENTAL</h1>
                <h2>CONTRATO DE ARRENDAMIENTO / RENTAL AGREEMENT</h2>

                <p class="office-info">
                    BUGAMBILIAS #7, LOS BENITOS, COLÓN<br>
                    QUERÉTARO, Qro. CP 76259<br>
                    gerencia-mkt@viajerocar-rental.com<br>
                    Tel. 441 690 09 98 / Cel. 442 716 97 93
                </p>
            </div>

            <div class="cl2-ra-box">
                <div class="label">No. Rental Agreement</div>
                <div class="value">
                    {{ $contrato->numero_contrato ?? '-----' }}
                </div>

                <div class="label small">Fecha de Cambio</div>
                <div class="value small">
                    {{ $fechaCambio ?? now()->format('d/m/Y H:i') }}
                </div>
            </div>

        </header>

        @php
    $tieneDaniosEmpresa = isset($danosEmpresa) && $danosEmpresa->count() > 0;
    $totalEmpresa = $tieneDaniosEmpresa
        ? $danosEmpresa->sum('costo_estimado')
        : 0;
@endphp

<!-- COLUMNAS PRINCIPALES -->
<section class="cl2-columns">

    <!-- COLUMNA IZQUIERDA – AUTO RECIBIDO POR EMPRESA -->
    <div class="cl2-col">
        <h3 class="cl2-section-title">AUTO RECIBIDO POR EMPRESA</h3>

        <table class="cl2-table">
            <tr>
                <th>CATEGORIA</th>
                <td>{{ $categoria->codigo ?? 'N/A' }}</td>
            </tr>
            <tr>
                <th>TIPO</th>
                <td>{{ $vehiculo->tipo_servicio ?? 'N/A' }}</td>
            </tr>
            <tr>
                <th>MODELO</th>
                <td>{{ $vehiculo->modelo ?? 'N/A' }}</td>
            </tr>
            <tr>
                <th>PLACAS</th>
                <td>{{ $vehiculo->placa ?? 'N/A' }}</td>
            </tr>
            <tr>
                <th>TRANSMISIÓN</th>
                <td>{{ $vehiculo->transmision ?? 'N/A' }}</td>
            </tr>
            <tr>
                <th>FUEL OUT</th>
                <td>
                    @if(!is_null($vehiculo->gasolina_actual ?? null))
                        {{ $vehiculo->gasolina_actual }}
                    @else
                        N/A
                    @endif
                </td>
            </tr>
            <tr>
                <th>KILOMETRAJE OUT</th>
                <td>{{ $vehiculo->kilometraje ?? 'N/A' }}</td>
            </tr>
        </table>

        {{-- DIAGRAMA ESTÁTICO – EMPRESA (solo imagen, sin puntos interactivos) --}}
        <div class="cl2-car-diagram">
            <img src="{{ public_path('img/diagrama-carro-danos3.png') }}" alt="Diagrama de daños">
        </div>

        {{-- TABLA DE DAÑOS – EMPRESA (solo si hay registros) --}}
        <table class="cl2-danos-table">
            <thead>
                <tr>
                    <th>Zona</th>
                    <th>Daño / Nota</th>
                    <th>Costo</th>
                    <th>Foto</th>
                </tr>
            </thead>
            <tbody>
                @if($tieneDaniosEmpresa)
                    @foreach($danosEmpresa as $dano)
                        <tr class="cl2-dano-row">
                            <td>{{ $dano->zona }}</td>
                            <td>{{ $dano->tipo_dano ?? $dano->comentario ?? '—' }}</td>
                            <td>
                                ${{ number_format($dano->costo_estimado ?? 0, 2) }}
                            </td>
                            <td>
                                @if(!empty($dano->nombre_archivo))
                                    Foto registrada
                                @else
                                    —
                                @endif
                            </td>
                        </tr>
                    @endforeach
                @else
                    <tr class="cl2-danos-empty">
                        <td colspan="4">Sin daños registrados.</td>
                    </tr>
                @endif
            </tbody>
        </table>

        <div class="cl2-danos-total">
            Total daños: ${{ number_format($totalEmpresa, 2) }} MXN
        </div>

        {{-- FIRMA ASESOR --}}
<div class="cl2-sign-box">
    <span class="label">FIRMA ASESOR</span>

    <div class="line">
        @if($contrato->firma_arrendador)
            {{-- En PDF normalmente ya es una ruta base64 o storage accesible --}}
            <img src="{{ $contrato->firma_arrendador }}"
                 class="cl2-sign-img"
                 alt="Firma asesor">
        @endif
    </div>

    <span class="name">
        @if(isset($asesor))
            {{ $asesor->nombres }} {{ $asesor->apellidos }}
        @else
            NOMBRE DEL ASESOR
        @endif
    </span>
</div>


    </div>

    <!-- COLUMNA DERECHA – AUTO ENTREGADO A CLIENTE -->
<div class="cl2-col">
    <h3 class="cl2-section-title">AUTO ENTREGADO A CLIENTE</h3>

    <table class="cl2-table" id="tablaAutoCliente">
        <tr>
            <th>CATEGORIA</th>
            <td>
                {{ $categoriaNuevo->codigo ?? 'N/A' }}
            </td>
        </tr>
        <tr>
            <th>TIPO</th>
            <td>
                <span id="cliente-tipo">
                    {{ $vehiculoNuevo->tipo_servicio ?? 'N/A' }}
                </span>
            </td>
        </tr>
        <tr>
            <th>MODELO</th>
            <td>
                <span id="cliente-modelo">
                    {{ $vehiculoNuevo->modelo ?? 'N/A' }}
                </span>
            </td>
        </tr>
        <tr>
            <th>PLACAS</th>
            <td>
                <span id="cliente-placas">
                    {{ $vehiculoNuevo->placa ?? 'N/A' }}
                </span>
            </td>
        </tr>
        <tr>
            <th>TRANSMISIÓN</th>
            <td>
                <span id="cliente-transmision">
                    {{ $vehiculoNuevo->transmision ?? 'N/A' }}
                </span>
            </td>
        </tr>
        <tr>
            <th>FUEL OUT</th>
            <td>
                <span id="cliente-fuel">
                    @if(!is_null($vehiculoNuevo->gasolina_actual ?? null))
                        {{ $vehiculoNuevo->gasolina_actual }}
                    @else
                        N/A
                    @endif
                </span>
            </td>
        </tr>
        <tr>
            <th>KILOMETRAJE OUT</th>
            <td>
                <span id="cliente-km">
                    {{ $vehiculoNuevo->kilometraje ?? 'N/A' }}
                </span>
            </td>
        </tr>
    </table>

    {{-- Puedes dejar este hidden si quieres mantener el id en el PDF, no afecta nada --}}
    <input type="hidden"
           id="idVehiculoNuevoSeleccionado"
           name="id_vehiculo_nuevo"
           value="{{ $vehiculoNuevo->id_vehiculo ?? '' }}">

        {{-- DIAGRAMA – CLIENTE (en PDF es solo visual) --}}
    <div class="cl2-car-diagram">
        <img src="{{ public_path('img/diagrama-carro-danos3.png') }}"
             alt="Diagrama de daños cliente">
        <p class="cl2-car-hint">
            Daños registrados al entregar el vehículo al cliente.
        </p>
    </div>



    {{-- TABLA DE DAÑOS – CLIENTE (solo lectura, sin botones) --}}
    <table class="cl2-danos-table" data-context="cliente">
        <thead>
            <tr>
                <th>Zona</th>
                <th>Daño / Nota</th>
                <th>Costo</th>
                <th>Foto</th>
            </tr>
        </thead>
        <tbody>
            @php
                $tieneDaniosCliente = isset($danosCliente) && $danosCliente->count() > 0;
            @endphp

            @if($tieneDaniosCliente)
                @foreach($danosCliente as $dano)
                    <tr class="cl2-dano-row"
                        data-contexto="cliente"
                        data-zona="{{ $dano->zona }}"
                        data-costo="{{ $dano->costo_estimado ?? 0 }}">
                        <td>{{ $dano->zona }}</td>
                        <td>{{ $dano->tipo_dano ?? $dano->comentario ?? '—' }}</td>
                        <td>
                            ${{ number_format($dano->costo_estimado ?? 0, 2) }}
                        </td>
                        <td>Foto cargada</td>
                    </tr>
                @endforeach
            @else
                <tr class="cl2-danos-empty">
                    <td colspan="4">Sin daños registrados.</td>
                </tr>
            @endif
        </tbody>
    </table>

    @php
        $totalCliente = isset($danosCliente) ? $danosCliente->sum('costo_estimado') : 0;
    @endphp

    <div class="cl2-danos-total" data-context="cliente">
        Total daños: ${{ number_format($totalCliente, 2) }} MXN
    </div>

    {{-- FIRMA CLIENTE --}}
    <div class="cl2-sign-box">
        <span class="label">FIRMA CLIENTE</span>

        @if(!empty($contrato->firma_cliente))
            <div class="line">
                <img src="{{ $contrato->firma_cliente }}"
                     class="cl2-sign-img"
                     alt="Firma del cliente">
            </div>
        @else
            <div class="line"></div>
        @endif

        <span class="name">
            @if(!empty($reservacion->nombre_cliente) || !empty($reservacion->apellidos_cliente))
                {{ $reservacion->nombre_cliente }} {{ $reservacion->apellidos_cliente }}
            @else
                NOMBRE DEL CLIENTE
            @endif
        </span>
    </div>
</div>


</section>


    </div>
</div>

</body>
</html>
