{{-- resources/views/Admin/contrato-final-pdf.blade.php --}}
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <title>Contrato Final - PDF</title>

  <style>
  /* ==========================================================
     CONFIG BÁSICA PDF
  ========================================================== */
  @page{
    size: A4 portrait;
    margin: 0;
  }

  @page clausulas{
    size: A4 portrait;
    margin: 14mm 6mm 10mm 6mm;
  }

  :root{
    --brand:#D6001C;
    --brand-2:#d32f2f;
    --ink:#111827;
    --muted:#4b5563;
    --stroke:#e5e7eb;
  }

  *{ box-sizing:border-box; margin:0; padding:0; }

  html, body{
    background:#fff !important;
    color:var(--ink);
    font-family: 'Inter','Poppins', Arial, sans-serif;
    font-size: 10px;
    line-height: 1.25;
    -webkit-print-color-adjust: exact;
    print-color-adjust: exact;
  }

  .contrato-final-container{
    width:100%;
    margin:0;
    padding:0;
    background:#fff;
  }

  /* ==========================================================
     ENCABEZADO - BLANCO
  ========================================================== */
  .encabezado-blanco{
    width:100%;
    display: table;
    table-layout: fixed;
    padding: 5px 6mm 7px 6mm;
  }

  .logo-titulo-blanco{
    display: table-cell;
    width: 50%;
    vertical-align: middle;
  }

  .logo-contrato{
    width: 205px;
    height: auto;
    filter: brightness(0) saturate(100%) invert(11%) sepia(96%) saturate(6019%) hue-rotate(354deg) brightness(93%) contrast(118%);
  }

  .encabezado-datos-cell{
    display: table-cell;
    width: 50%;
    vertical-align: middle;
    text-align: right;
    position: relative;
  }

  .logo-fondo-derecho{
    position: absolute;
    right: -18mm;
    top: -8px;
    height: 195px;
    width: auto;
    z-index: 0;
    opacity: 0.9;
    filter: grayscale(100%) brightness(0) invert(88%);
  }

  .encabezado-datos{
    position: relative;
    z-index: 1;
  }

  .encabezado-datos p{
    margin: 5px 0;
    font-size: 13.5px;
    color: #222;
    white-space: nowrap;
  }

  .burbuja-roja{
    background-color: var(--brand-2);
    color: #fff;
    padding: 3px 14px;
    border-radius: 10px;
    font-weight: bold;
    display: inline-block;
    text-align: center;
    font-size: 13.5px;
    margin-left: 6px;
  }

  .ico{
    width: 11px;
    height: 11px;
    display: inline-block;
    vertical-align: -1px;
    margin-right: 3px;
    stroke: currentColor;
    fill: none;
    stroke-width: 2;
    stroke-linecap: round;
    stroke-linejoin: round;
  }

  .itinerario-item .info-line .ico{ color: var(--brand-2); }
  .adicionales-table .ico,
  .gasolina-cell .ico{ color: #ffffff; stroke-width: 2.3; }

  /* ==========================================================
     TARJETA GRACIAS (IZQUIERDA)
  ========================================================== */
  .tarjeta-blanca-izquierda{
    padding: 9px 6mm 9px 6mm;
    text-align: left;
    margin-bottom: 2px;
  }

  .tarjeta-blanca-izquierda .gracias{
    color: #1a1a1a;
    font-size: 20px;
    font-weight: 400;
    margin: 0;
    line-height: 1.25;
  }

  .tarjeta-blanca-izquierda .gracias strong{
    font-weight: 700;
    color: #000;
  }

  .tarjeta-blanca-izquierda .frase{
    color: #4b5563;
    font-size: 13px;
    margin: 4px 0 0 0;
    font-style: italic;
  }

  /* ==========================================================
     TÍTULOS DE SECCIÓN (ROJOS)
  ========================================================== */
  .titulo-seccion{
    color: var(--brand);
    font-size: 15px;
    font-weight: 800;
    letter-spacing: .3px;
    margin: 7px 0 3px 0;
    text-transform: uppercase;
  }

  /* ==========================================================
     CUERPO - SECCIONES
  ========================================================== */
  .secciones{
    padding: 1px 6mm 0;
  }

  .row-full{ display: block; margin-top: 11px; }

  /* ==========================================================
     VEHÍCULO
  ========================================================== */
  .bloque-vehiculo{
    background-color: var(--brand-2);
    border-top-left-radius: 24px;
    border-bottom-left-radius: 24px;
    padding: 10px 14px 10px 26px;
    width: calc(100% + 6mm);
    margin-right: -6mm;
    box-sizing: border-box;
    color:#fff;
  }

  .vehiculo-grid{
    display: table;
    width:100%;
    table-layout: fixed;
    color:#fff;
  }

  .vehiculo-item{
    display: table-cell;
    vertical-align: top;
  }

  .vehiculo-item .label{
    display:block;
    font-size: 9.5px;
    font-weight: 700;
    color:#fff;
    margin-bottom: 2px;
  }

  .vehiculo-item .value{
    display:block;
    font-size: 10.5px;
    font-weight: 600;
    color:#fff;
  }

  .gasolina-row{
    display: table;
    width:100%;
    table-layout: fixed;
    margin-top: 8px;
    color:#fff;
  }

  .gasolina-cell{
    display: table-cell;
    vertical-align: middle;
  }

  .gasolina-cell.derecha{ text-align: left; }

  .gasolina-cell .label{
    font-size: 10.5px;
    font-weight: 700;
    color:#fff;
  }

  .gasolina-cell .value{
    font-size: 11px;
    font-weight: 600;
    color:#fff;
  }

  .gasolina-icon{
    display:inline-block;
    height: 5mm;
    width:auto;
    margin-right: 4px;
    vertical-align: middle;
  }

  /* ==========================================================
     ARRENDATARIO / ITINERARIO (2 columnas)
  ========================================================== */
  .row-dos-columnas{
    display: flex;
    align-items: stretch;
    width:100%;
    margin-top: 10px;
  }

  .col{
    flex: 1 1 0;
    display: flex;
    flex-direction: column;
    min-width: 0;
  }

  .col-izq{ padding-right: 3px; }
  .col-der{ padding-left: 3px; }

  .bloque-arrendatario,
  .bloque-itinerario{
    background:#fff;
    border-radius: 10px;
    padding: 5px 8px;
  }

  .arrendatario-item{
    display: table;
    width:100%;
    padding: 2px 0;
    border-bottom: 1px solid #f3f4f6;
  }

  .arrendatario-item:last-of-type{ border-bottom: none; }

  .arrendatario-item .label{
    display: table-cell;
    font-weight: 700;
    color: var(--muted);
    width: 42mm;
    font-size: 9.5px;
  }

  .arrendatario-item .value{
    display: table-cell;
    color: var(--ink);
    font-size: 9.5px;
  }

  /* Tabla Licencia */
  .licencia-table{
    width:100%;
    border-collapse: collapse;
    margin-top: 4px;
    font-size: 9.5px;
  }

  .licencia-table th{
    background: #f3f4f6;
    border: 1px solid var(--stroke);
    padding: 2px 4px;
    font-weight: 700;
    text-align: left;
  }

  .licencia-table td{
    border: 1px solid var(--stroke);
    padding: 2px 4px;
  }

  /* Itinerario */
  .itinerario-item{
    padding: 2px 0;
    margin-bottom: 3px;
  }

  .itinerario-item .label{
    font-weight: 700;
    color: var(--ink);
    font-size: 11px;
    margin-bottom: 2px;
    display: block;
  }

  .itinerario-item .value{
    color: var(--ink);
    font-weight: 600;
    font-size: 10.5px;
    line-height: 1.3;
  }

  .itinerario-item .info-line{
    display: block;
    margin: 0;
  }

  .itinerario-item .info-line i{
    display: inline-block;
    width: 14px;
    text-align: center;
    font-style: normal;
    filter: brightness(0);
  }

  /* ==========================================================
     TARIFAS / ADICIONALES (2 columnas, rojas)
  ========================================================== */
  .bloque-tarifas{
    background: var(--brand);
    border-radius: 10px;
    padding: 6px 8px;
    box-sizing: border-box;
    width: calc(100% + 6mm + 3px);
    margin-left: calc(-6mm - 3px);
    flex: 1;
  }

  .tarifas-table{
    width:100%;
    border-collapse: collapse;
    font-size: 9.5px;
    color:#fff;
  }

  .tarifas-table th{
    background: var(--brand);
    border: 1px solid rgba(255,255,255,.5);
    padding: 2px 4px;
    font-weight: 700;
    text-align: left;
    color:#fff;
  }

  .tarifas-table td{
    background: var(--brand);
    border: 1px solid rgba(255,255,255,.5);
    padding: 2px 4px;
    color:#ffffff;
    font-weight: 600;
  }

  .totales{
    margin-top: 4px;
    padding: 3px 4px 0 4px;
    text-align: right;
    border-top: 2px solid #fff;
  }

  .totales p{
    margin: 1px 0;
    font-size: 9.5px;
    color:#fff;
  }

  .totales .total-final{
    font-size: 13px;
    font-weight: 900;
    color:#fff;
  }

  .bloque-adicionales{
    background: var(--brand);
    border-radius: 10px;
    padding: 6px 8px;
    box-sizing: border-box;
    width: calc(100% + 6mm + 3px);
    margin-right: calc(-6mm - 3px);
    flex: 1;
  }

  .adicionales-table{
    width:100%;
    border-collapse: collapse;
    font-size: 9.5px;
    color:#fff;
  }

  .adicionales-table th{
    background: var(--brand);
    border: 1px solid rgba(255,255,255,.5);
    padding: 2px 4px;
    font-weight: 700;
    text-align: left;
    color:#fff;
  }

  .adicionales-table td{
    background: var(--brand);
    border: 1px solid rgba(255,255,255,.5);
    padding: 2px 4px;
    color:#ffffff;
    font-weight: 600;
  }

  .adicionales-table td[colspan]{
    text-align: center;
    font-weight: 500;
  }

  .adicional-item.no-seleccionado td{
    opacity: .92;
  }

  .badge-cantidad,
  .badge-ubicacion,
  .badge-inactivo{
    font-size: 9px;
    padding: 1px 5px;
    border-radius: 8px;
    margin-left: 4px;
    white-space: nowrap;
  }

  .badge-cantidad{ background: rgba(255,255,255,.25); }
  .badge-ubicacion{ background: rgba(255,255,255,.20); font-style: italic; }
  .badge-inactivo{ background: rgba(255,255,255,.18); font-style: italic; color:#ffffff; }

  .texto-inactivo{ opacity:.92; }

  .adicional-total-row td{
    border-top: 2px solid #fff;
  }

  .fa-gas-pump, .fa-user-plus, .fa-user-minus, .fa-child,
  .fa-location-dot, .fa-truck, .fa-flag-checkered, .fa-fire,
  .fa-regular, .fa-solid, .fa-calendar, .fa-clock{
    font-style: normal;
  }

  /* ==========================================================
     ACEPTACIÓN Y FIRMAS
  ========================================================== */
  .bloque-aceptacion{
    background:#fff;
    border-radius: 10px;
    padding: 5px 4px;
  }

  .aceptacion-texto{
    font-size: 9px;
    line-height: 1.3;
    text-align: justify;
    margin-bottom: 5px;
    color: var(--ink);
  }

  .aceptacion-texto a{
    color: var(--brand);
    text-decoration: underline;
  }

  .firmas-container{
    display: table;
    width:100%;
    table-layout: fixed;
    margin-top: 3px;
  }

  .firma-item{
    display: table-cell;
    text-align: center;
    width: 50%;
    padding: 0 20px;
    vertical-align: bottom;
  }

  .firma-label{
    font-size: 9px;
    font-weight: 700;
    color: var(--muted);
    margin-bottom: 2px;
    text-transform: uppercase;
  }

  .firma-img{
    width: 90%;
    height: 34px;
    max-width: 160px;
    object-fit: contain;
    display: block;
    margin: 0 auto;
  }

  .firma-linea,
  .firma-linea-roja{
    width: 80%;
    height: 4px;
    border-bottom: 2px solid #000;
    margin: 0 auto;
  }

  .firma-nombre{
    font-size: 10px;
    font-weight: 600;
    margin-top: 3px;
    color: var(--ink);
  }

  /* ==========================================================
     GASOLINA / NOTAS / FACTURACIÓN
  ========================================================== */
  .bloque-gasolina,
  .bloque-notas,
  .bloque-facturacion{
    background:#fff;
    border-radius: 10px;
    padding: 4px 4px;
  }

  .gasolina-texto{
    font-size: 9px;
    color: var(--ink);
    margin:0;
    line-height: 1.25;
  }

  .nota-gas{
    font-size: 9px;
    color: #6b7280;
    display: block;
    margin-top: 1px;
  }

  .nota-title{
    font-size: 9.5px;
    font-weight: 800;
    color: var(--ink);
    margin-bottom: 2px;
  }

  .nota{
    font-size: 10px;
    line-height: 1.25;
    margin: 1px 0;
    color: var(--ink);
  }

  .fact-title{
    font-size: 11px;
    font-weight: 800;
    color: var(--ink);
    margin-bottom: 3px;
  }

  .fact-grid{
    display: table;
    width:100%;
    table-layout: fixed;
    border-collapse: collapse;
  }

  .fact-row{ display: table-row; }

  .fact-item{
    display: table-cell;
    width: 33.33%;
    padding: 1px 0;
    border-bottom: 1px solid #f3f4f6;
    font-size: 10px;
  }

  .fact-item .label{
    font-weight: 700;
    color: var(--muted);
    display: inline;
  }

  .fact-item .value{
    color: var(--ink);
    display: inline;
    margin-left: 4px;
  }

  /* ==========================================================
     PIE DE PÁGINA - ROJO
  ========================================================== */
  .pie-rojo{
    background: var(--brand);
    padding: 12px 6mm 20px 6mm;
    color:#fff;
    margin-top: 6px;
  }

  .pie-empresa{
    font-size: 14.5px;
    margin-bottom: 3px;
    text-align: left;
  }

  .pie-empresa strong{ font-weight: 900; }

  .pie-contenido-columnas{
    display: table;
    width:100%;
    table-layout: fixed;
    margin-top: 5px;
  }

  .col-pie{
    display: table-cell;
    vertical-align: top;
    font-size: 10.5px;
    line-height: 1.4;
  }

  .col-pie.izq{ text-align: left; }
  .col-pie.der{ text-align: right; }

  .pie-rojo p{ margin: 2px 0; font-size: 12px; }
  .pie-rojo span{ font-size: 12px; display: block; }

  /* ==========================================================
     PAGE BREAK + HOJA 2 (CLÁUSULAS) - solo PDF
  ========================================================== */
  .page-break{
    page-break-before: always;
    break-before: page;
  }

  .clausulas-page{
    page: clausulas;
    font-family: Arial, sans-serif;
    color:#111;
  }

  .clausulas-intro{
    text-align:center;
    font-size: 9.5pt;
    line-height: 1.25;
    letter-spacing: 0.02em;
    text-transform: uppercase;
    margin: 0 0 5mm 0;
  }

  .clausulas-title{
    text-align:center;
    font-size: 26pt;
    font-weight: 800;
    letter-spacing: 0.02em;
    color: #4a4a4a;
    margin: 0 0 6mm 0;
    text-transform: uppercase;
  }

  .clausulas-body{ margin: 0; }

  .clausulas-body p{
    margin: 0 0 3.5mm 0;
    font-size: 9pt;
    line-height: 1.35;
    text-align: justify;
  }

  .clausula-tag{
    font-weight: 800;
    text-transform: uppercase;
    color: #111;
  }

  .clausulas-notas{
    margin: 5mm 0 0 0;
    font-size: 8.5pt;
    line-height: 1.3;
    color:#222;
  }

  .clausulas-notas .nota-titulo{
    font-weight: 800;
    text-transform: uppercase;
  }

  .clausulas-notas .nota{ margin: 1.5mm 0 3mm 0; }

  .clausulas-fecha{
    margin: 6mm 0 2mm 0;
    text-align:center;
    font-size: 10pt;
    color:#333;
  }

  .linea-roja{
    display:inline-block;
    border-bottom: 1.2pt solid #000;
    min-width: 45mm;
    height: 4mm;
    vertical-align: bottom;
    margin: 0 2mm;
  }

  .clausulas-firmas{
    margin: 4mm 0 0 0;
    width: 100%;
    display: table;
    table-layout: fixed;
  }

  .firma-col{
    display: table-cell;
    width: 50%;
    text-align: center;
    vertical-align: top;
  }

  .firma-col .firma-line{
    width: 70%;
    margin: 0 auto 2mm auto;
    border-top: 1.2pt solid #000;
  }

  .firma-col .firma-img{
    height: 12mm;
    margin: 0 auto 0 auto;
  }

  .firma-col .firma-nombre{
    font-size: 11pt;
    font-weight: 700;
    margin: 0;
  }

  .pie-rojo{
    page-break-inside: avoid;
  }
  </style>
</head>

<body>

@php
  // ===== Iconos SVG de contorno (line icons) =====
  $icoUbicacion = '<svg class="ico" viewBox="0 0 24 24"><path d="M12 21s-6-5.686-6-10a6 6 0 0 1 12 0c0 4.314-6 10-6 10z"/><circle cx="12" cy="11" r="2.5"/></svg>';
  $icoCalendario = '<svg class="ico" viewBox="0 0 24 24"><rect x="3.5" y="5" width="17" height="16" rx="2"/><path d="M3.5 9h17M8 3v4M16 3v4"/></svg>';
  $icoReloj = '<svg class="ico" viewBox="0 0 24 24"><circle cx="12" cy="12" r="8.5"/><path d="M12 7.5V12l3 2"/></svg>';
  $icoPersona = '<svg class="ico" viewBox="0 0 24 24"><circle cx="12" cy="8" r="3.5"/><path d="M5 20c0-3.5 3-6 7-6s7 2.5 7 6"/></svg>';
  $icoBebe = '<svg class="ico" viewBox="0 0 24 24"><circle cx="12" cy="7" r="3.5"/><path d="M9 7h.01M15 7h.01M6 20c0-3 2.5-5.5 6-5.5s6 2.5 6 5.5"/></svg>';
  $icoCamion = '<svg class="ico" viewBox="0 0 24 24"><path d="M3 6h10v9H3zM13 9h4l3 3v3h-7z"/><circle cx="7" cy="18" r="1.8"/><circle cx="17" cy="18" r="1.8"/></svg>';
  $icoMeta = '<svg class="ico" viewBox="0 0 24 24"><path d="M5 21V4M5 4h11l-2 3 2 3H5"/></svg>';
  $icoFuego = '<svg class="ico" viewBox="0 0 24 24"><path d="M12 3s5 4 5 9a5 5 0 0 1-10 0c0-1.5.7-2.8 1.5-3.5C8.5 10 9 12 10 12c0-2 2-4 2-9z"/></svg>';
@endphp

<div class="contrato-final-container">

  {{-- ============================ ENCABEZADO ============================ --}}
  <div class="encabezado-blanco">
    <div class="logo-titulo-blanco">
      @if(!empty($logoBase64))
        <img src="{{ $logoBase64 }}" class="logo-contrato" alt="Viajero Car Rental">
      @endif
    </div>

    <div class="encabezado-datos-cell">
      @if(!empty($imgABase64))
        <img src="{{ $imgABase64 }}" class="logo-fondo-derecho" alt="">
      @endif
      <div class="encabezado-datos">
        <p><strong>No. Rental Agreement:</strong>
          <span class="burbuja-roja">{{ $contrato->id_contrato ?? '—' }}</span>
        </p>
        <p><strong>Fecha de apertura:</strong>
          <span class="burbuja-roja">{{ now()->translatedFormat('d/M/Y H:i') }}</span>
        </p>
        <p><strong>Reservación:</strong>
          <span class="burbuja-roja">{{ $reservacion->id_reservacion ?? '—' }}</span>
        </p>
      </div>
    </div>
  </div>

  {{-- ============================ GRACIAS ============================ --}}
  <div class="tarjeta-blanca-izquierda">
    <p class="gracias">
      Gracias por tu reserva,
      <strong>{{ trim(($reservacion->nombre_cliente ?? '') . ' ' . ($reservacion->apellidos_cliente ?? '')) ?: 'Cliente' }}</strong>
    </p>
    <p class="frase">Disfruta el camino tanto como tu destino.</p>
  </div>

  {{-- ============================ CUERPO ============================ --}}
  <div class="secciones">

    {{-- ===================== VEHÍCULO ===================== --}}
    <div class="row-full">
      <h3 class="titulo-seccion">Información de tu vehículo</h3>
      <div class="bloque-vehiculo">
        <div class="vehiculo-grid">
          <div class="vehiculo-item"><span class="label">Modelo:</span><span class="value">{{ $vehiculo->modelo ?? '—' }}</span></div>
          <div class="vehiculo-item"><span class="label">Categoría:</span><span class="value">{{ $vehiculo->categoria ?? '—' }}</span></div>
          <div class="vehiculo-item"><span class="label">Color:</span><span class="value">{{ $vehiculo->color ?? '—' }}</span></div>
          <div class="vehiculo-item"><span class="label">Placas:</span><span class="value">{{ $vehiculo->placa ?? '—' }}</span></div>
          <div class="vehiculo-item"><span class="label">Transmisión:</span><span class="value">{{ $vehiculo->transmision ?? '—' }}</span></div>
          <div class="vehiculo-item"><span class="label">Kilometraje:</span><span class="value">{{ number_format($vehiculo->kilometraje ?? 0) }}</span></div>
        </div>

        <div class="gasolina-row">
          <div class="gasolina-cell">
            {!! $icoFuego !!}
            <span class="label">Capacidad del tanque:</span>
            <span class="value">{{ $vehiculo->capacidad_tanque ?? '—' }} LITROS</span>
          </div>
          <div class="gasolina-cell derecha">
            <span class="label">Gasolina de salida:</span>
            <span class="value">{{ $vehiculo->gasolina_actual ?? '—' }} LITROS</span>
          </div>
        </div>
      </div>
    </div>

    {{-- ===================== ARRENDATARIO + ITINERARIO ===================== --}}
    <div class="row-dos-columnas">

      {{-- ARRENDATARIO --}}
      <div class="col col-izq">
        <h3 class="titulo-seccion">Arrendatario</h3>
        <div class="bloque-arrendatario">
          <div class="arrendatario-item">
            <span class="label">Nombre:</span>
            <span class="value">{{ trim(($reservacion->nombre_cliente ?? '') . ' ' . ($reservacion->apellidos_cliente ?? '')) ?: '—' }}</span>
          </div>
          <div class="arrendatario-item">
            <span class="label">Fecha de nacimiento (DOB):</span>
            <span class="value">{{ $fechaNacimiento ? \Carbon\Carbon::parse($fechaNacimiento)->format('d/m/Y') : '—' }}</span>
          </div>
          <div class="arrendatario-item">
            <span class="label">Edad:</span>
            <span class="value">{{ isset($edad) && $edad !== null ? $edad . ' años' : '—' }}</span>
          </div>
          <div class="arrendatario-item">
            <span class="label">Teléfono:</span>
            <span class="value">{{ $reservacion->telefono_cliente ?? '—' }}</span>
          </div>
          <div class="arrendatario-item">
            <span class="label">Correo:</span>
            <span class="value">{{ $reservacion->email_cliente ?? '—' }}</span>
          </div>
          <div class="arrendatario-item">
            <span class="label">Dirección:</span>
            <span class="value">{{ $reservacion->direccion_cliente ?? '—' }}</span>
          </div>

          <table class="licencia-table">
            <thead>
              <tr>
                <th>No. Licencia</th>
                <th>Vencimiento</th>
                <th>País</th>
                <th>Estado</th>
              </tr>
            </thead>
            <tbody>
              <tr>
                <td>{{ $licencia->numero_identificacion ?? '—' }}</td>
                <td>{{ $licencia->fecha_vencimiento ?? '—' }}</td>
                <td>{{ $licencia->pais_emision ?? '—' }}</td>
                <td>{{ $licencia->estado ?? '—' }}</td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      {{-- ITINERARIO --}}
      <div class="col col-der">
        <h3 class="titulo-seccion">Itinerario</h3>
        <div class="bloque-itinerario">
          <div class="itinerario-item">
            <span class="label">Check in:</span>
            <div class="value">
              <div class="info-line">{!! $icoUbicacion !!} {{ $reservacion->sucursal_retiro_nombre ?? '—' }}</div>
              <div class="info-line">{!! $icoCalendario !!} {{ $reservacion->fecha_inicio ? \Carbon\Carbon::parse($reservacion->fecha_inicio)->translatedFormat('d/M/Y') : '—' }}</div>
              <div class="info-line">{!! $icoReloj !!} {{ $reservacion->hora_retiro ? \Carbon\Carbon::parse($reservacion->hora_retiro)->format('H:i') : '—' }} HRS</div>
            </div>
          </div>
          <div class="itinerario-item">
            <span class="label">Check out:</span>
            <div class="value">
              <div class="info-line">{!! $icoUbicacion !!} {{ $reservacion->sucursal_entrega_nombre ?? '—' }}</div>
              <div class="info-line">{!! $icoCalendario !!} {{ $reservacion->fecha_fin ? \Carbon\Carbon::parse($reservacion->fecha_fin)->translatedFormat('d/M/Y') : '—' }}</div>
              <div class="info-line">{!! $icoReloj !!} {{ $reservacion->hora_entrega ? \Carbon\Carbon::parse($reservacion->hora_entrega)->format('H:i') : '—' }} HRS</div>
            </div>
          </div>
        </div>
      </div>

    </div>

    {{-- ===================== TARIFAS + ADICIONALES ===================== --}}
    <div class="row-dos-columnas">

      {{-- TARIFAS --}}
      <div class="col col-izq">
        <h3 class="titulo-seccion">Tarifas</h3>
        <div class="bloque-tarifas">
          <table class="tarifas-table">
            <thead>
              <tr>
                <th>Concepto</th>
                <th>Días</th>
                <th>Precio por día</th>
                <th>Total</th>
              </tr>
            </thead>
            <tbody>
              <tr>
                <td>Tarifa base</td>
                <td>{{ $dias }}</td>
                <td>$ {{ number_format($tarifaBase, 2) }}</td>
                <td>$ {{ number_format($tarifaBase * $dias, 2) }}</td>
              </tr>

              @foreach ($paquetes as $p)
                <tr>
                  <td>{{ $p->nombre }}</td>
                  <td>{{ $dias }}</td>
                  <td>$ {{ number_format($p->precio_por_dia, 2) }}</td>
                  <td>$ {{ number_format($p->precio_por_dia * $dias, 2) }}</td>
                </tr>
              @endforeach

              @foreach ($individuales as $i)
                <tr>
                  <td>{{ $i->nombre }}</td>
                  <td>{{ $dias }}</td>
                  <td>$ {{ number_format($i->precio_por_dia, 2) }}</td>
                  <td>$ {{ number_format($i->precio_por_dia * $dias, 2) }}</td>
                </tr>
              @endforeach
            </tbody>
          </table>

          <div class="totales">
            <p><strong>Subtotal:</strong> $ {{ number_format($subtotal, 2) }}</p>
            <p><strong>IVA.</strong> $ {{ number_format($subtotal * 0.16, 2) }}</p>
            <p><strong>Cuotas locales e impuestos federales</strong> $ {{ number_format($subtotal * 0.16, 2) }}</p>
            <p class="total-final"><strong>TOTAL:</strong> $ {{ number_format($totalFinal, 2) }}</p>
          </div>
        </div>
      </div>

      {{-- ADICIONALES --}}
      <div class="col col-der">
        <h3 class="titulo-seccion">Adicionales</h3>
        <div class="bloque-adicionales">
          <table class="adicionales-table">
            <thead>
              <tr>
                <th>Producto</th>
                <th>Días</th>
                <th>Precio por día</th>
              </tr>
            </thead>
            <tbody>
              @php
                $totalAdicionales = 0;

                $extrasSeleccionados = [];
                foreach (($extras ?? []) as $extra) {
                    $extrasSeleccionados[$extra->nombre] = [
                        'precio'   => $extra->precio_unitario ?? 0,
                        'cantidad' => $extra->cantidad ?? 1,
                    ];
                }

                $deliveryActivo = isset($deliveryInfo) && $deliveryInfo && (($deliveryInfo->precio_unitario ?? 0) > 0);
                $dropoffActivo  = isset($dropoffInfo)  && $dropoffInfo  && (($dropoffInfo->precio_unitario ?? 0) > 0);
                $gasolinaActiva = isset($gasolinaInfo) && $gasolinaInfo && (($gasolinaInfo->precio_unitario ?? 0) > 0);

                $serviciosMostrar = [
                    'Additional driver'   => ['icono' => $icoPersona, 'es_especial' => false],
                    'Conductor menor'     => ['icono' => $icoPersona, 'es_especial' => false],
                    'Baby seat'           => ['icono' => $icoBebe,    'es_especial' => false],
                    'GPS'                 => ['icono' => $icoUbicacion,'es_especial' => false],
                    'Delivery'            => ['icono' => $icoCamion,  'es_especial' => true],
                    'Drop Off'            => ['icono' => $icoMeta,    'es_especial' => true],
                    'Gasolina (faltante)' => ['icono' => $icoFuego,   'es_especial' => true],
                ];
              @endphp

              @foreach ($serviciosMostrar as $nombre => $config)
                @php
                    $icono = $config['icono'];
                    $esEspecial = $config['es_especial'];
                    $seleccionado = isset($extrasSeleccionados[$nombre]);
                    $detalles = '';

                    if ($nombre === 'Delivery' && $deliveryActivo) {
                        $seleccionado = true;
                        $precio = $deliveryInfo->precio_unitario ?? 0;
                        $cantidad = 1;
                        $detalles = $deliveryInfo->direccion ?? '';
                    } elseif ($nombre === 'Drop Off' && $dropoffActivo) {
                        $seleccionado = true;
                        $precio = $dropoffInfo->precio_unitario ?? 0;
                        $cantidad = 1;
                        $detalles = $dropoffInfo->destino ?? '';
                    } elseif ($nombre === 'Gasolina (faltante)' && $gasolinaActiva) {
                        $seleccionado = true;
                        $precio = $gasolinaInfo->precio_unitario ?? 0;
                        $cantidad = $gasolinaInfo->cantidad ?? 1;
                        $detalles = ($gasolinaInfo->litros ?? 0) > 0 ? $gasolinaInfo->litros . ' L' : '';
                    } elseif ($seleccionado) {
                        $precio = $extrasSeleccionados[$nombre]['precio'];
                        $cantidad = $extrasSeleccionados[$nombre]['cantidad'];
                    } else {
                        $precio = 0;
                        $cantidad = 0;
                    }

                    if ($seleccionado && $precio > 0) {
                        if (!$esEspecial) {
                            $totalAdicionales += $precio * $cantidad * $dias;
                        } else {
                            $totalAdicionales += $precio;
                        }
                    }

                    $estadoClase     = ($seleccionado && $precio > 0) ? 'seleccionado' : 'no-seleccionado';
                    $estadoTexto     = ($seleccionado && $precio > 0) ? number_format($precio, 2) : '0.00';
                    $cantidadMostrar = ($seleccionado && $cantidad > 0) ? $cantidad : 0;
                    $diasMostrar     = !$esEspecial ? $dias : '—';
                @endphp
                <tr class="adicional-item {{ $estadoClase }}">
                  <td>
                    {!! $icono !!}
                    {{ $nombre }}
                    @if ($seleccionado && $cantidadMostrar > 1)
                      <span class="badge-cantidad">×{{ $cantidadMostrar }}</span>
                    @endif
                    @if ($seleccionado && !empty($detalles))
                      <span class="badge-ubicacion">{{ $detalles }}</span>
                    @endif
                    @if (!$seleccionado || $precio == 0)
                      <span class="badge-inactivo">(No seleccionado)</span>
                    @endif
                  </td>
                  <td class="{{ !$seleccionado || $precio == 0 ? 'texto-inactivo' : '' }}">{{ $diasMostrar }}</td>
                  <td class="{{ !$seleccionado || $precio == 0 ? 'texto-inactivo' : '' }}">$ {{ $estadoTexto }}</td>
                </tr>
              @endforeach

              @if ($totalAdicionales > 0)
                <tr class="adicional-total-row">
                  <td colspan="2" style="text-align:right; font-weight:bold;">TOTAL ADICIONALES</td>
                  <td style="font-weight:bold;">$ {{ number_format($totalAdicionales, 2) }}</td>
                </tr>
              @else
                <tr class="adicional-total-row">
                  <td colspan="3" style="text-align:center; font-weight:bold;">Ningún adicional seleccionado</td>
                </tr>
              @endif
            </tbody>
          </table>
        </div>
      </div>

    </div>

    {{-- ===================== ACEPTACIÓN + FIRMAS ===================== --}}
    <div class="row-full">
      <div class="bloque-aceptacion">
        <p class="aceptacion-texto">
          Acepto plenamente las obligaciones descritas en la carátula y en el clausulado de este contrato.
          Declaro bajo protesta de decir verdad, haber recibido el auto descrito en el apartado de salida
          y acepto las condiciones generales al inicio de la renta, así mismo entiendo y acepto las condiciones
          del tratamiento de mis datos personales como se describe en el aviso de privacidad que se encuentra
          a mi disposición en:
          <a href="https://www.viajeroacrental.mx/">https://www.viajeroacrental.mx/</a>
        </p>

        <div class="firmas-container">
          @php

            $firmaSrc = function ($valor) {
                if (empty($valor)) return null;
                if (str_starts_with($valor, 'data:image')) return $valor;
                if (str_starts_with($valor, 'http://') || str_starts_with($valor, 'https://')) return $valor;

                $candidatos = [
                    public_path(ltrim($valor, '/')),
                    storage_path('app/public/' . ltrim(str_replace('storage/', '', $valor), '/')),
                    $valor,
                ];
                foreach ($candidatos as $ruta) {
                    if (is_file($ruta) && ($bytes = @file_get_contents($ruta)) !== false) {
                        $ext = strtolower(pathinfo($ruta, PATHINFO_EXTENSION));
                        $mime = in_array($ext, ['jpg','jpeg']) ? 'image/jpeg'
                              : ($ext === 'gif' ? 'image/gif'
                              : ($ext === 'webp' ? 'image/webp' : 'image/png'));
                        return 'data:' . $mime . ';base64,' . base64_encode($bytes);
                    }
                }
                return 'data:image/png;base64,' . $valor;
            };

            $firmaClienteCaratula = $firmaSrc($contrato->firma_cliente ?? null);
            $firmaArrendadorCaratula = $firmaSrc($vehiculo->firma_propietario ?? null);
          @endphp

          <div class="firma-item">
            <p class="firma-label">(firma de arrendatario)</p>
            @if (!empty($firmaClienteCaratula))
              <img src="{{ $firmaClienteCaratula }}" class="firma-img">
            @endif
            <div class="firma-linea-roja"></div>
            <p class="firma-nombre">{{ trim(($reservacion->nombre_cliente ?? '') . ' ' . ($reservacion->apellidos_cliente ?? '')) ?: 'CLIENTE' }}</p>
          </div>

          <div class="firma-item">
            <p class="firma-label">(firma de arrendador)</p>
            @if (!empty($firmaArrendadorCaratula))
              <img src="{{ $firmaArrendadorCaratula }}" class="firma-img">
            @endif
            <div class="firma-linea-roja"></div>
            <p class="firma-nombre">VIAJERO CAR RENTAL</p>
          </div>
        </div>
      </div>
    </div>

    {{-- ===================== GASOLINA ===================== --}}
    <div class="row-full">
      <div class="bloque-gasolina">
        <p class="gasolina-texto">
          <strong>GASOLINA:</strong> PRECIO POR LITRO FALTANTE $13.16 MXN MAS CARGO POR SERVICIO DE 23.96
          MXN POR LITRO FALTANTE IMPUESTOS INCLUIDOS
          <span class="nota-gas">(APLICABLE SI LA OPCION DE PREPAGO DE GAS NO FUE ADQUIRIDA)</span>
        </p>
      </div>
    </div>

    {{-- ===================== NOTAS ===================== --}}
    <div class="row-full">
      <div class="bloque-notas">
        <p class="nota-title"><strong>INFORMACIÓN DE LOS CARGOS TOTALES:</strong></p>
        <table style="width:100%; border-collapse:collapse;">
          <tr>
            <td style="width:50%; vertical-align:top; padding-right:8px;">
              <p class="nota"><strong>(1)</strong> Al firmar este contrato el cliente declara tener conocimiento de todas las condiciones establecidas y acepta el clausulado al reverso.</p>
              <p class="nota"><strong>(2)</strong> Los cargos son ESTIMADOS, el importe total a pagar del contrato aparecerá al cierre del mismo.</p>
              <p class="nota"><strong>(3)</strong> Usted va alquilar y devolver el vehículo en el momento y lugares indicados. Gasolina no reembolsable en prepago. EXCEPTO si se regresa con tanque lleno.</p>
              <p class="nota"><strong>(4)</strong> NO SE ACEPTA EFECTIVO como pago ni como deposito.</p>
            </td>
            <td style="width:50%; vertical-align:top; padding-left:8px;">
              <p class="nota"><strong>(5)</strong> CDW 0% incluye: ROBO %, llantas, rines, cristales y espejos.</p>
              <p class="nota"><strong>(6)</strong> CDW20%, CDW10%, PCDW NO incluye llantas, rines, cristales y espejos.</p>
              <p class="nota"><strong>(7)</strong> Ninguna protección cubre GPS, Placas o llaves.</p>
              <p class="nota"><strong>(8)</strong> CDW20%, CDW10%, PDW. LDW revocado en caso de negligencia del conductor o si existen conductores NO autorizados en el contrato.</p>
            </td>
          </tr>
        </table>
      </div>
    </div>

    {{-- ===================== FACTURACIÓN ===================== --}}
    <div class="row-full">
      <div class="bloque-facturacion">
        <p class="fact-title"><strong>Datos de Facturación</strong></p>
        <div class="fact-grid">
          <div class="fact-row">
            <div class="fact-item"><span class="label">No. cliente fiscal:</span><span class="value">{{ $reservacion->cliente_fiscal ?? '—' }}</span></div>
            <div class="fact-item"><span class="label">RFC:</span><span class="value">{{ $reservacion->rfc_cliente ?? '—' }}</span></div>
            <div class="fact-item"><span class="label">Razón social:</span><span class="value">{{ $reservacion->razon_social_cliente ?? '—' }}</span></div>
          </div>
          <div class="fact-row">
            <div class="fact-item"><span class="label">Calle:</span><span class="value">{{ $reservacion->direccion_cliente ?? '—' }}</span></div>
            <div class="fact-item"><span class="label">No. Ext.</span><span class="value">{{ $reservacion->num_ext_cliente ?? '—' }}</span></div>
            <div class="fact-item"><span class="label">No. Int.</span><span class="value">{{ $reservacion->num_int_cliente ?? '—' }}</span></div>
          </div>
          <div class="fact-row">
            <div class="fact-item"><span class="label">C.P.:</span><span class="value">{{ $reservacion->cp_cliente ?? '—' }}</span></div>
            <div class="fact-item"><span class="label">Colonia:</span><span class="value">{{ $reservacion->colonia_cliente ?? '—' }}</span></div>
            <div class="fact-item"><span class="label">Estado:</span><span class="value">{{ $reservacion->estado_cliente ?? '—' }}</span></div>
          </div>
          <div class="fact-row">
            <div class="fact-item"><span class="label">Municipio:</span><span class="value">{{ $reservacion->municipio_cliente ?? '—' }}</span></div>
            <div class="fact-item"><span class="label">País:</span><span class="value">{{ $reservacion->pais_cliente ?? '—' }}</span></div>
            <div class="fact-item"><span class="label">Ciudad:</span><span class="value">{{ $reservacion->ciudad_cliente ?? '—' }}</span></div>
          </div>
        </div>
      </div>
    </div>

  </div> {{-- /secciones --}}

  {{-- ============================ PIE ROJO ============================ --}}
  <footer class="pie-rojo">
    <p class="pie-empresa"><strong>VIAJERO CAR RENTAL</strong></p>
    <div class="pie-contenido-columnas">
      <div class="col-pie izq">
        <p>Business Center INNERA Central Park. Armando Birlain Shaffler #2001, Torre 2, Centro Sur, Qro.</p>
        <p>Teléfono: 442 303 26 68 &nbsp; Celular: 442 716 97 93 | &nbsp; 442 343 07 70</p>
      </div>
      <div class="col-pie der">
        <span>Arrendador: José Juan de Dios Hernández Resendiz</span>
        <span>Facturación: facturación@viajeroacr-rental.com</span>
        <span>Reservaciones: reservaciones@viajeroacr-rental.com</span>
      </div>
    </div>
  </footer>

</div> {{-- /contrato-final-container --}}


{{-- ============================================================
     HOJA 2 - CLÁUSULAS (solo en el PDF que se envía por correo)
============================================================ --}}

@php
  $arrendadorNombre   = $arrendadorNombre   ?? '—';
  $arrendatarioNombre = $arrendatarioNombre ?? (trim(($reservacion->nombre_cliente ?? '') . ' ' . ($reservacion->apellidos_cliente ?? '')) ?: '—');
  $lugarFirma = $lugarFirma ?? '—';
  $diaFirma   = $diaFirma   ?? '—';
  $mesFirma   = $mesFirma   ?? '—';
  $anioFirma  = $anioFirma  ?? '—';
@endphp

<section class="clausulas-page">

  <div class="clausulas-intro">
    CONTRATO DE ARRENDAMIENTO, QUE CELEBRA POR UNA PARTE LA COMPAÑÍA CUYA RAZÓN SOCIAL APARECE EN EL APARTADO NO. 1 DEL ANVERSO DE ESTE CONTRATO COMO ARRENDADORA, Y POR LA OTRA, LA PERSONA CUYO NOMBRE APARECE EN EL APARTADO NO. 2 DEL ANVERSO DE ESTE CONTRATO, CON CARÁCTER DE ARRENDATARIA.
  </div>

  <div class="clausulas-title">Clausulas</div>

  <div class="clausulas-body">
    <p><span class="clausula-tag">PRIMERA.</span> LA ARRENDADORA entrega en arrendamiento a la ARRENDATARIA cuyo nombre aparece en la carátula de este documento y dicha ARRENDATARIA recibe en tal carácter el vehículo objeto de este contrato en condiciones normales, mecánicas y de carrocería, consignadas en el inventario respectivo, con el carácter de BIEN ARRENDADO, a tener bajo su custodia y a su entera satisfacción, el vehículo de referencia y se obliga a pagar a la ARRENDADORA la renta señalada del contrato y a precisar de mercado, el o los faltantes de accesorios y partes del vehículo que recibe en el momento de entrega del mismo.</p>

    <p><span class="clausula-tag">SEGUNDA.</span> El término forzoso de este contrato de arrendamiento está señalado en la carátula de este contrato y nunca podrá ser prorrogado por ninguna de las partes, sin que aparezca constancia de voluntad de los mismos. en un nuevo contrato de arrendamiento.</p>

    <p><span class="clausula-tag">TERCERA.</span> LA ARRENDATARIA pagará como precio del arrendamiento el anticipo y precisamente el lugar donde deberán ser pagadas las cantidades estipuladas en el contrato de arrendamiento. Los pagos serán efectuados conforme a lo indicado en la carátula de este contrato. La renta deberá ser totalmente pagada aun cuando el vehículo se encuentre en uso de LA ARRENDATARIA, desde este momento, en plena posesión del automóvil y hasta la fecha en que lo reciba en devolución, a su entera satisfacción. LA ARRENDADORA.</p>

    <p><span class="clausula-tag">CUARTA.</span> LA ARRENDATARIA se obliga a entregar en devolución el vehículo arrendado precisamente en la hora y fecha convenidas y en la oficina de la ARRENDADORA en que se hubiera pactado la devolución, apareciendo esos datos en la carátula de este contrato de la misma que el vehículo se encontrará lavado y en condiciones normales, siendo el vehículo devuelto con el tanque lleno de gasolina. LA ARRENDATARIA deberá devolverlo en el lugar indicado en la carátula de este contrato. LA ARRENDATARIA deberá devolver el vehículo al lugar convenido en el contrato y en el plazo estipulado, más el importe que corresponda si la arrendataria del tiempo normal de traslado del lugar donde LA ARRENDATARIA haya dejado el vehículo a la oficina donde debió entregarlo de acuerdo con este contrato, aplicándose en todo caso la cuota diaria.</p>

    <p><span class="clausula-tag">QUINTA.</span> LA ARRENDATARIA tal como antes se señala se obliga a entregar el vehículo arrendado al término de este contrato, con el solo desgaste del uso normal y moderado, precisamente en la fecha y hora convenida y saldando en la carátula del contrato. con el pago del arrendamiento y en las condiciones señaladas en el contrato.</p>

    <p><span class="clausula-tag">SEXTA.</span> En caso de que LA ARRENDADORA niegue cualquier diligencia, previa autorización del pago de las prestaciones debidas por LA ARRENDATARIA o bien obtenga el vehículo devuelto legalmente aplicando las medidas de orden judicial o por acuerdo entre las partes, se autoriza LA ARRENDADORA para disponer del vehículo en la forma que estime más adecuada, ya sea. venderlo, arrendarlo o cualquier otra forma de disposición que convenga a los intereses de LA ARRENDADORA.</p>

    <p><span class="clausula-tag">SÉPTIMA.</span> LA ARRENDATARIA se obliga a mantener el vehículo en buenas condiciones, realizando los servicios de mantenimiento preventivo y correctivo necesarios para su buen funcionamiento y conservación, así como a cubrir los gastos derivados de su uso normal.</p>

    <p><span class="clausula-tag">OCTAVA.</span> El vehículo arrendado se destinará única y exclusivamente al transporte de LA ARRENDATARIA y sus acompañantes, y solo podrá ser manejado por LA ARRENDATARIA o por conductores autorizados que cuenten con licencia vigente. Queda prohibido usar el vehículo para fines distintos a los pactados.</p>

    <p><span class="clausula-tag">NOVENA.</span> El vehículo arrendado no podrá ser conducido fuera de los límites del territorio de la República Mexicana, sin el previo consentimiento expreso y por escrito de LA ARRENDADORA.</p>

    <p><span class="clausula-tag">DÉCIMA.</span> LA ARRENDATARIA se obliga a no permitir que el vehículo sea utilizado para actividades ilícitas o contrarias a la ley.</p>

    <p><span class="clausula-tag">DÉCIMA PRIMERA.</span> LA ARRENDATARIA será responsable de cualquier daño. desperfecto o pérdida total o parcial del vehículo durante la vigencia del presente contrato. aun cuando sea causado por terceros.</p>

    <p><span class="clausula-tag">DÉCIMA SEGUNDA.</span> En caso de accidente, robo o pérdida total del vehículo. LA ARRENDATARIA deberá dar aviso inmediato a LA ARRENDADORA y a las autoridades correspondientes. obligándose a cubrir los daños y perjuicios conforme a lo estipulado en este contrato.</p>

    <p><span class="clausula-tag">DÉCIMA TERCERA.</span> LA ARRENDATARIA no podrá subarrendar. prestar. ceder o permitir el uso del vehículo a terceros sin autorización previa y por escrito de LA ARRENDADORA.</p>

    <p><span class="clausula-tag">DÉCIMA CUARTA.</span> LA ARRENDATARIA se obliga a pagar todas las multas, infracciones, gastos de arrastre. corralón y cualquier otro cargo que se genere por el uso del vehículo durante la vigencia del contrato.</p>

    <p><span class="clausula-tag">DÉCIMA QUINTA.</span> LA ARRENDATARIA deberá cubrir el importe de los daños ocasionados al vehículo por negligencia. imprudencia o mal uso del mismo.</p>

    <p><span class="clausula-tag">DÉCIMA SEXTA.</span> LA ARRENDADORA no será responsable por los objetos personales dejados dentro del vehículo arrendado durante el tiempo que se encuentre en posesión de LA ARRENDATARIA.</p>

    <p><span class="clausula-tag">DÉCIMA SÉPTIMA.</span> LA ARRENDATARIA reconoce que ha recibido el vehículo en óptimas condiciones y se obliga a devolverlo en el mismo estado, salvo el desgaste normal por el uso.</p>

    <p><span class="clausula-tag">DÉCIMA OCTAVA.</span> En caso de incumplimiento de cualquiera de las obligaciones establecidas en el presente contrato. LA ARRENDADORA podrá darlo por rescindido sin necesidad de declaración judicial.</p>

    <p><span class="clausula-tag">DÉCIMA NOVENA.</span> Para la interpretación y cumplimiento del presente contrato. las partes se someten a la jurisdicción de los tribunales competentes del Estado de Querétaro. renunciando a cualquier otro fuero que pudiera corresponderles por razón de su domicilio presente o futuro.</p>

    <p><span class="clausula-tag">VIGÉSIMA.</span> Las partes manifiestan que conocen y aceptan todas y cada una de las cláusulas del presente contrato. firmándolo de conformidad.</p>
  </div>

  <div class="clausulas-notas">
    <div class="nota"><span class="nota-titulo">GASOLINA:</span> PRECIO POR LITRO FALTANTE $13.16 MXN MAS CARGO POR SERVICIO DE 23.96 MXN POR LITRO FALTANTE IMPUESTOS INCLUIDOS (APLICABLE SI LA OPCION DE PREPAGO DE GAS NO FUE ADQUIRIDA)</div>

    <div class="nota"><span class="nota-titulo">INFORMACIÓN DE LOS CARGOS TOTALES:</span><br>
      (1) Al firmar este contrato el cliente declara tener conocimiento de todas las condiciones establecidas y acepta el clausulado al reverso.
      (2) Los cargos son ESTIMADOS, el importe total a pagar del contrato aparecera al cierre del mismo.
      (3) Usted va a alquilar y devolver el vehiculo en el momento y lugares indicados. Gasolina no reembolsable en prepago, EXCEPTO si se regresa con tanque lleno.
      <br><br>
      <span class="nota-titulo">1.</span> NO SE ACEPTA EFECTIVO como pago ni como deposito.
      <span class="nota-titulo">2.</span> CDW 0% incluye: ROBO %, llantas, rines, cristales y espejos.
      <span class="nota-titulo">3.</span> CDW20%, CDW10%, PCDW NO incluye llantas, rines, cristales y espejos.
      <span class="nota-titulo">4.</span> Ninguna protección cubre GPS. Placas o llaves.
      <span class="nota-titulo">5.</span> CDW20%, CDW10%, PDW, LDW revocado en caso de negligencia del conductor o si existen conductores NO autorizados en el contrato.
    </div>
  </div>

  <div class="clausulas-fecha">
    En <span class="linea-roja">{{ $lugarFirma }}</span>
    al día <span class="linea-roja" style="min-width:16mm;">{{ $diaFirma }}</span>
    del mes de <span class="linea-roja" style="min-width:30mm;">{{ $mesFirma }}</span>
    del año <span class="linea-roja" style="min-width:20mm;">{{ $anioFirma }}</span>
  </div>

  <div class="clausulas-firmas">
    @php
      if (!isset($firmaSrc)) {
          $firmaSrc = function ($valor) {
              if (empty($valor)) return null;
              if (str_starts_with($valor, 'data:image')) return $valor;
              if (str_starts_with($valor, 'http://') || str_starts_with($valor, 'https://')) return $valor;
              $candidatos = [
                  public_path(ltrim($valor, '/')),
                  storage_path('app/public/' . ltrim(str_replace('storage/', '', $valor), '/')),
                  $valor,
              ];
              foreach ($candidatos as $ruta) {
                  if (is_file($ruta) && ($bytes = @file_get_contents($ruta)) !== false) {
                      $ext = strtolower(pathinfo($ruta, PATHINFO_EXTENSION));
                      $mime = in_array($ext, ['jpg','jpeg']) ? 'image/jpeg'
                            : ($ext === 'gif' ? 'image/gif'
                            : ($ext === 'webp' ? 'image/webp' : 'image/png'));
                      return 'data:' . $mime . ';base64,' . base64_encode($bytes);
                  }
              }
              return 'data:image/png;base64,' . $valor;
          };
      }
      $firmaArrSrc = $firmaSrc($contrato->firma_arrendador ?? null);
      $firmaCliSrc = $firmaSrc($contrato->firma_cliente ?? null);
    @endphp

    {{-- FIRMA ARRENDADOR --}}
    <div class="firma-col">
      @if(!empty($firmaArrSrc))
        <img class="firma-img" src="{{ $firmaArrSrc }}" alt="Firma arrendador">
      @endif
      <div class="firma-line"></div>
      <p class="firma-nombre">{{ $arrendadorNombre }}</p>
    </div>

    {{-- FIRMA ARRENDATARIO --}}
    <div class="firma-col">
      @if(!empty($firmaCliSrc))
        <img class="firma-img" src="{{ $firmaCliSrc }}" alt="Firma arrendatario">
      @endif
      <div class="firma-line"></div>
      <p class="firma-nombre">{{ $arrendatarioNombre }}</p>
    </div>
  </div>

</section>
</body>
</html>
