<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <title>Anexo Conductor Adicional - PDF</title>
    <style>
    /* ==========================================
   VARIABLES Y BASE
========================================== */
:root {
  --brand: #E50914;
  --brand-dark: #bb0811;
  --ink: #111827;
  --muted: #6b7280;
  --border: #e5e7eb;
  --paper: #ffffff;
  --bg: #f3f4f6;
  --radius-md: 12px;
  --radius-lg: 16px;
  --shadow-sm: 0 4px 14px rgba(15, 23, 42, 0.08);
  --font: 'Poppins', system-ui, -apple-system, BlinkMacSystemFont, sans-serif;
}

/* ==========================================
   CONFIGURACIÓN DE PÁGINA (DomPDF)
========================================== */
@page {
  size: 230mm 470mm; /* ancho, alto */
  margin: 20mm;
}

* {
  box-sizing: border-box;
}

html, body {
  margin: 0;
  padding: 0;
  background: #f3f4f6 !important;
  color: #111827;
  font-family: DejaVu Sans, Arial, sans-serif !important;
  font-size: 11px;
}

h1, h2, h3, h4, p {
  margin: 0;
  padding: 0;
}

/* Contenedor principal (formato carta centrado) */
.document-wrapper {
  background: var(--paper);
  padding: 28px 34px 38px;
  margin: 22px auto;
  width: 95%;
  max-width: 1000px;
  border-radius: var(--radius-lg);
  box-shadow: var(--shadow-sm);
  color: var(--ink);
}

/* ===============================
        ENCABEZADO (PDF)
================================= */

.doc-header {
  width: 100%;
  display: table;            /* Más compatible con DomPDF que flex */
  table-layout: fixed;
  border-bottom: 2px solid var(--brand);
  padding-bottom: 16px;
  margin-bottom: 20px;
}

.header-left,
.header-right {
  display: table-cell;
  vertical-align: middle;
}

.header-left {
  width: 60%;
}

.header-right {
  width: 40%;
  text-align: right;
}

/* Logo del encabezado */
.big-header-logo {
  width: 260px;             /* Tamaño similar al de la vista */
  height: auto;
  object-fit: contain;      /* Ya no "cover" para que no se recorte */
  object-position: left center;
  display: block;
  margin: 0 0 8px 0;
}

/* Estos pueden quedarse igual que en la vista */
.company-info p {
  margin: 0;
  font-size: 13px;
  color: var(--muted);
}

.company-info p:first-child {
  font-weight: 600;
  color: var(--ink);
  font-size: 14px;
}

.doc-meta {
  text-align: right;
}

.doc-label {
  display: inline-block;
  font-size: 11px;
  text-transform: uppercase;
  letter-spacing: 1.8px;
  color: var(--muted);
}

.doc-title {
  margin: 2px 0 0;
  font-size: 18px;
  font-weight: 700;
  letter-spacing: 0.5px;
}

.meta-grid {
  display: inline-block;    /* Mejor para PDF que flex */
  margin-top: 6px;
  font-size: 13px;
}

.meta-item {
  text-align: right;
}

.meta-label {
  display: block;
  color: var(--muted);
  font-size: 12px;
}

.meta-value {
  font-weight: 600;
  color: var(--ink);
}


/* Responsive del encabezado (en PDF casi no aplica, pero no estorba) */
@media (max-width: 768px) {
  .doc-header {
    flex-direction: column;
    align-items: flex-start;
    gap: 14px;
  }

  .header-right {
    align-items: flex-start;
    text-align: left;
  }

  .meta-grid {
    flex-direction: column;
    align-items: flex-start;
  }
}

/* ===============================
            INTRO
================================= */

.intro-section {
  text-align: center;
  margin-bottom: 16px;
}

.section-title {
  font-size: 16px;
  font-weight: 600;
  margin-bottom: 4px;
  text-transform: uppercase;
  letter-spacing: 1.4px;
}

.section-sub {
  font-size: 13px;
  color: var(--muted);
  margin: 0;
}

/* ===============================
    BLOQUES / TARJETAS
================================= */

.card-block {
  background: #fafafa;
  border-radius: var(--radius-md);
  border: 1px solid var(--border);
  padding: 18px 18px 20px;
  margin-bottom: 14px;
}

.block-header {
  margin-bottom: 10px;
}

.block-title {
  font-size: 14px;
  font-weight: 600;
  margin: 0 0 4px;
  text-transform: uppercase;
  letter-spacing: 1px;
}

.block-subtitle {
  margin: 0;
  font-size: 12px;
  color: var(--muted);
}

/* ===============================
            TABLA
================================= */

.styled-table {
  width: 100%;
  border-collapse: collapse;
  font-size: 13px;
  margin-top: 8px;
}

/* Encabezado tipo contrato */
.styled-table thead th {
  background: #f9fafb;
  color: var(--ink);
  padding: 8px 10px;
  text-align: center;
  font-weight: 600;
  border-bottom: 2px solid var(--brand);
  border-top: 1px solid #111;
  border-left: 1px solid #111;
  border-right: 1px solid #111;
}

/* Celdas cuerpo */
.styled-table td {
  border-left: 1px solid #111;
  border-right: 1px solid #111;
  border-bottom: 1px solid #111;
  padding: 7px 10px;
  text-align: center;
}

.styled-table tbody tr:nth-child(even) {
  background: #fdfdfd;
}

.styled-table tbody tr:hover {
  background: #f3f4f6;
}

.img-cell {
  text-align: center;
}

/* Columna de firma (sigue siendo útil en el PDF) */
.firma-col {
  width: 40%;
}

.doc-thumb {
  width: 65px;
  height: auto;
  border-radius: 4px;
  border: 1px solid #000;
}

.no-img {
  font-style: italic;
  color: var(--muted);
  font-size: 12px;
}

.empty-row {
  text-align: center;
  padding: 14px;
  font-size: 13px;
  color: var(--muted);
}

/* ===============================
        TEXTO LEGAL
================================= */

.legal-block {
  background: #ffffff;
}

.legal-section p {
  font-size: 13px;
  line-height: 1.6;
  margin-bottom: 10px;
  text-align: justify;
}

/* ===============================
            FIRMAS
================================= */

.signatures-wrapper {
  display: grid;
  grid-template-columns: repeat(2, minmax(0, 1fr));
  gap: 24px;
  margin-top: 8px;
}

.signature-card {
  text-align: center;
}

.signature-image {
  max-width: 260px;
  height: auto;
  display: block;
  margin: 0 auto 10px;
}

.sig-line {
  width: 260px;
  height: 1px;
  background: #000;
  margin: 0 auto 6px;
}

.sig-label {
  font-size: 12px;
  font-weight: 500;
}

/* ==========================================
   EVITAR CORTES FEOS EN PDF
========================================== */
.doc-header,
.intro-section,
.card-block,
.styled-table,
.signature-card {
  page-break-inside: avoid;
}

  </style>
</head>

<body>

    <!-- =============================== -->
    <!-- ENCABEZADO DOCUMENTO           -->
    <!-- =============================== -->
    <header class="doc-header">
        <div class="header-left">
            <img src="{{ public_path('img/Logotipo Fondo.jpg') }}" class="big-header-logo">

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
