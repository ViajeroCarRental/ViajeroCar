<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <title>Anexo Conductor Adicional - PDF</title>
    <style>
    /* ==========================================
       CONFIGURACIÓN DE PÁGINA (DomPDF)
    ========================================== */
    @page{
      size: 230mm 470mm; /* ancho, alto */
      margin: 20mm;
    }

    *{
      box-sizing:border-box;
    }

    html, body{
      margin:0;
      padding:0;
      background:#f3f4f6 !important;
      color:#111827;
      font-family: DejaVu Sans, Arial, sans-serif !important;
      font-size: 11px;
    }

    h1,h2,h3,h4,p{
      margin:0;
      padding:0;
    }

    /* ==========================================
       ENCABEZADO DOCUMENTO (mismas clases)
    ========================================== */
    .doc-header{
      width:100%;
      display:table;
      table-layout:fixed;
      background:#ffffff;
      border-radius:16px;
      padding:12px 16px;
      margin-bottom:14px;
      border:1px solid #e5e7eb;
      box-shadow:0 4px 12px rgba(15,23,42,.06);
    }

    .header-left,
    .header-right{
      display:table-cell;
      vertical-align:middle;
    }

    .header-left{
      width:60%;
    }

    .big-header-logo{
      width:160px;
      height:auto;
      margin-bottom:8px;
    }

    .company-info p{
      font-size:11px;
      line-height:1.35;
    }

    .header-right{
      width:40%;
      text-align:right;
    }

    .doc-meta{
      display:inline-block;
      text-align:right;
      font-size:10px;
    }

    .doc-label{
      display:block;
      font-size:10px;
      text-transform:uppercase;
      color:#6b7280;
      letter-spacing:.08em;
      margin-bottom:3px;
    }

    .doc-title{
      font-size:14px;
      font-weight:700;
      margin:0 0 6px;
    }

    .meta-grid{
      border-radius:12px;
      border:1px solid #e5e7eb;
      background:#f9fafb;
      padding:6px 10px;
      margin-top:4px;
    }

    .meta-item{
      font-size:9.5px;
      margin-bottom:2px;
    }

    .meta-item:last-child{
      margin-bottom:0;
    }

    .meta-label{
      display:block;
      color:#6b7280;
    }

    .meta-value{
      display:block;
      font-weight:700;
    }

    /* ==========================================
       INTRO / NOTA
    ========================================== */
    .intro-section{
      border-radius:16px;
      border:1px solid #e5e7eb;
      background:#ffffff;
      padding:14px 16px;
      margin-top:10px;
    }

    .section-title{
      font-size:12px;
      font-weight:700;
      text-align:center;
      text-transform:uppercase;
      margin-bottom:4px;
    }

    .section-sub{
      font-size:10.5px;
      text-align:center;
      color:#6b7280;
    }

    /* ==========================================
       BLOQUES / TARJETAS
    ========================================== */
    .card-block{
      border-radius:16px;
      border:1px solid #e5e7eb;
      background:#ffffff;
      padding:14px 16px;
      margin-top:14px;
    }

    .block-header{
      margin-bottom:8px;
    }

    .block-title{
      font-size:11.5px;
      font-weight:700;
      margin-bottom:2px;
    }

    .block-subtitle{
      font-size:10px;
      color:#6b7280;
    }

    /* ==========================================
       TABLA CONDUCTOR ADICIONAL
    ========================================== */
    .styled-table{
      width:100%;
      border-collapse:collapse;
      font-size:10.5px;
      margin-top:6px;
    }

    .styled-table th,
    .styled-table td{
      border:1px solid #e5e7eb;
      padding:8px 10px;
      text-align:center;
      vertical-align:middle;
    }

    .styled-table th{
      background:#f3f4f6;
      font-weight:600;
      white-space:nowrap;
    }

    .img-cell{
      text-align:center;
    }

    .firma-col{
      width:40%;
    }

    .doc-thumb{
      max-width:180px;
      max-height:90px;
      height:auto;
      border-radius:10px;
      border:1px solid #e5e7eb;
      background:#ffffff;
    }

    .no-img{
      font-size:10px;
      color:#9ca3af;
    }

    .empty-row{
      text-align:center;
      font-size:10px;
      color:#9ca3af;
    }

    /* ==========================================
       TEXTO LEGAL
    ========================================== */
    .legal-block .legal-section p{
      font-size:10.2px;
      line-height:1.5;
      margin-bottom:6px;
      text-align:justify;
    }

    .legal-block .legal-section p:last-child{
      margin-bottom:0;
    }

    /* ==========================================
       FIRMAS
    ========================================== */
    .signatures-wrapper{
      margin-top:10px;
      text-align:center;
    }

    .signature-card{
      display:inline-block;
      padding:12px 18px 10px;
      border-radius:14px;
      border:1px solid #e5e7eb;
      background:#f9fafb;
    }

    .signature-image{
      max-width:220px;
      max-height:90px;
      height:auto;
      border-radius:10px;
      border:1px solid #e5e7eb;
      background:#ffffff;
    }

    .sig-line{
      margin-top:6px;
      border-top:1px solid #111827;
      width:100%;
    }

    .sig-label{
      margin-top:4px;
      font-size:10px;
    }

    /* ==========================================
       EVITAR CORTES FEOS EN PDF
    ========================================== */
    .doc-header,
    .intro-section,
    .card-block,
    .styled-table,
    .signature-card{
      page-break-inside:avoid;
    }
  </style>
</head>

<body>

    <!-- =============================== -->
    <!-- ENCABEZADO DOCUMENTO           -->
    <!-- =============================== -->
    <header class="doc-header">
        <div class="header-left">
            <img src="{{ asset('/img/Logotipo Fondo.jpg') }}" class="big-header-logo">

            <div class="company-info">
                <p>Viajero Car Rental</p>
                <p>Anexo de contrato – Conductor adicional</p>
            </div>
        </div>

        <div class="header-right">
            <div class="doc-meta">
                <span class="doc-label">Anexo de Contrato</span>
                <h1 class="doc-title">Autorización de Conductor Adicional</h1>

                <div class="meta-grid">
                    <div class="meta-item">
                        <span class="meta-label">No. Rental Agreement</span>
                        <span class="meta-value">
                            {{ $contrato->numero_contrato ?? $contrato->id_contrato ?? '---' }}
                        </span>
                    </div>
                    <div class="meta-item">
                        <span class="meta-label">Fecha</span>
                        <span class="meta-value">
                            {{ \Carbon\Carbon::now()->format('d/m/Y') }}
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </header>

        <!-- =============================== -->
    <!-- INTRO / NOTA                   -->
    <!-- =============================== -->
    <section class="intro-section">
        <h2 class="section-title">Autorización para Conductor Adicional Aceptado</h2>
        <p class="section-sub" id="subConductores">
            Revise la información del conductor adicional autorizado para conducir el vehículo.
        </p>
    </section>

    <!-- =============================== -->
    <!-- BLOQUE: DATOS DEL CONDUCTOR    -->
    <!-- =============================== -->
    <section class="card-block">
        <div class="block-header">
            <h3 class="block-title">Datos del conductor adicional</h3>
            <p class="block-subtitle">
                Este anexo corresponde a un conductor adicional registrado para este contrato.
            </p>
        </div>

        <table class="styled-table">
            <thead>
                <tr>
                    <th>Nombre (Name)</th>
                    <th>No. Licencia</th>
                    <th>Firma del Conductor</th>
                </tr>
            </thead>

            <tbody id="tbodyConductor">
                @php
                    $conductorActual = $conductor ?? null;
                    $nombreCompleto = $conductorActual
                        ? trim(($conductorActual->nombres ?? '') . ' ' . ($conductorActual->apellidos ?? ''))
                        : null;
                @endphp

                @if($conductorActual)
                    <tr data-id-conductor="{{ $conductorActual->id_conductor }}">
                        {{-- Nombre --}}
                        <td>
                            {{ $nombreCompleto ?: '---' }}
                        </td>

                        {{-- Licencia --}}
                        <td>
                            {{ $conductorActual->numero_licencia ?? '---' }}
                        </td>

                        {{-- Firma del conductor --}}
                        <td class="img-cell firma-col">
                            @if(!empty($conductorActual->firma_conductor))
                                <img src="{{ asset($conductorActual->firma_conductor) }}"
                                     class="doc-thumb"
                                     alt="Firma conductor">
                            @else
                                <span class="no-img">Firma pendiente</span>
                            @endif
                        </td>
                    </tr>
                @else
                    <tr>
                        <td colspan="3" class="empty-row">
                            No se encontró información del conductor adicional.
                        </td>
                    </tr>
                @endif
            </tbody>
        </table>
    </section>

    <!-- =============================== -->
    <!-- BLOQUE: TEXTO LEGAL            -->
    <!-- =============================== -->
    <section class="card-block legal-block">
        <div class="block-header">
            <h3 class="block-title">Declaraciones y aceptación</h3>
            <p class="block-subtitle">
                El conductor adicional y el arrendador reconocen y aceptan las condiciones descritas a continuación.
            </p>
        </div>

        <section class="legal-section">
             <p>
                <strong>CON MI FIRMA:</strong> Certifico que tengo la mayoría de edad y que poseo una licencia de conducir
                vigente y válida. Acepto que seré conjunta y solidariamente responsable de las obligaciones del titular
                del Contrato de Renta, incluyendo la obligación de indemnizar sin límite según los términos del mismo.
            </p>

            <p>
                Entiendo y acepto que mediante este documento no podré solicitar cambio de vehículo ni extensión del
                periodo de renta. Cualquier modificación al Contrato de Renta deberá realizarse por los canales formales
                establecidos por la arrendadora.
            </p>

            <p>
                Autorizo expresamente al conductor(es) adicional(es) arriba indicado(s) para conducir el vehículo
                amparado por el Contrato de Renta, bajo los mismos términos, condiciones, restricciones y responsabilidades
                aplicables al titular del contrato.
            </p>
        </section>
    </section>

    <!-- =============================== -->
    <!-- BLOQUE: FIRMA ÚNICA            -->
    <!-- =============================== -->
    <section class="card-block">
        <div class="block-header">
            <h3 class="block-title">Firma del anexo</h3>
            <p class="block-subtitle">
                La firma del arrendador se realizará de forma digital en el sistema.
            </p>
        </div>

        <div class="signatures-wrapper">

            {{-- Firma arrendador --}}
<div class="signature-card">
    @if($contrato->firma_arrendador)
        <img src="{{ $contrato->firma_arrendador }}" class="signature-image" alt="Firma arrendador">
    @else
        <span style="opacity:.6;font-size:.85rem">
            Firma pendiente
        </span>
    @endif

    <div class="sig-line"></div>
    <p class="sig-label">Firma del Arrendador(a)</p>

</div>


        </div>
    </section>


</body>
</html>
