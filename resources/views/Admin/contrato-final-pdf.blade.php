{{-- resources/views/Admin/contrato-final-pdf.blade.php --}}
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <title>Contrato Final - PDF</title>

  <style>
  /* ==========================================================
     🧾 CONFIG BÁSICA PDF
  ========================================================== */

  @page{
    /* La hoja ahora es A4 vertical */
    size: A4 portrait;
    margin: 6mm;
  }

  :root{
    --brand:#FF1E2D;
    --brand-2:#D6121F;
    --ink:#101828;
    --muted:#667085;
    --stroke:#E5E7EB;
    --paper:#ffffff;
  }

  *{
    box-sizing:border-box;
  }

  html, body{
    margin:0;
    padding:0;
    background:#fff !important;
    color:var(--ink);
    font-family: "Bahnschrift", Arial, sans-serif;
  }

  .contrato-final-container{
    width:100%;
    margin:0;
    padding: 0;
    background:#fff;
  }

  /* ==========================================================
   🧾 ENCABEZADO CONTRATO (AJUSTADO AL DISEÑO ORIGINAL)
========================================================== */

.header-contrato{
  width:100%;
  padding: 12mm 16mm 4mm 16mm;
  position:relative;
}

.header-layout{
  display: table;
  width:100%;
  table-layout: fixed;
}

.header-left{
  display: table-cell;
  width: 60%;
  vertical-align: top;
}

.header-right{
  display: table-cell;
  width: 40%;
  vertical-align: top;
  text-align:right;
  position:relative;
}

/* LOGO */

.logo-viajero{
  display:block;
  height: 16mm;          /* tamaño del logo */
  margin-left: -6mm;     /* mueve izquierda (-) / derecha (+) */
  margin-top: -6mm;      /* mueve arriba (-) / abajo (+) */
  margin-bottom: 6mm;    /* distancia entre logo y texto */
  width:auto;
}

/* TEXTOS */

.header-textos{
  margin-top: 0;
}

.header-titulo{
  margin:0 0 1.5mm 0;
  font-size: 21pt;
  font-weight: 700;
  color:#444444;
  line-height:1.2;
}

.header-subtitulo{
  margin:0;
  font-size: 12pt;
  font-weight:600;
  color:#5A5A5A;
}

/* FIGURA  A GRANDE */

.header-figura{
  position:absolute;
  right:-32mm;   /* mueve derecha (-) / izquierda (+) */
  top:0mm;     /* mueve arriba (-) / abajo (+) */
  height:105mm;  /* tamaño de la figura */
  width:auto;
  z-index:1;
  opacity:0.9;
}

/* BLOQUE DERECHO */

.header-datos{
  position:relative;
  z-index:2;
  font-size: 10pt;
  color:#9A9A9A;
  text-align:right;
  margin-top: 2mm;
}

.header-datos-linea{
  margin: 0 0 2.2mm 0;
  line-height:1.3;
}

/* CHIPS */

.chip-rojo{
  display:inline-block;
  background: var(--brand);
  color:#fff;
  padding: 1.3mm 4.2mm;
  border-radius: 999px;
  margin-left: 3mm;
  font-size: 9pt;
  font-weight:700;
  white-space: nowrap;
}

  /* ==========================================================
   🧾 TÍTULO SECCIÓN: INFORMACIÓN DE TU VEHÍCULO
========================================================== */

.titulo-seccion-vehiculo{
  margin: 10mm 16mm 4mm 16mm;
}

.titulo-vehiculo-texto{
  font-family: "Bahnschrift", Arial, sans-serif;
  font-weight: 800;
  font-size: 13pt;
  color: var(--brand);
  letter-spacing: 0.16em;
  text-transform: uppercase;
}

  /* ==========================================================
     🔴 FRANJA ROJA: INFORMACIÓN DEL VEHÍCULO
  ========================================================== */

  .vehiculo-info-box{
    margin: 0 16mm 8mm 16mm;     /* mismos márgenes horizontales que el título */
    width: calc(100% - 32mm);    /* 16mm izq + 16mm der */
    background: var(--brand);
    border-radius: 12mm 12mm 6mm 6mm; /* redondeado fuerte arriba como la imagen */
    padding: 7mm 10mm 6mm 10mm;
    color:#ffffff;
    font-family: Arial, sans-serif;
    font-size: 9pt;
  }

  .vehiculo-info-top{
    display: table;
    width:100%;
    table-layout: fixed;
    margin-bottom: 4mm;
  }

  .vehiculo-info-item{
    display: table-cell;
    vertical-align: top;
    padding-right: 4mm;
    text-align:left;
  }

  .vehiculo-info-label{
    display:block;
    font-weight:700;
    margin-bottom: 1mm;
  }

  .vehiculo-info-value{
    display:block;
    font-weight:400;
  }

  .vehiculo-info-bottom{
    display: table;
    width:100%;
    table-layout: fixed;
    margin-top: 1mm;
  }

  .vehiculo-info-bottom-left,
  .vehiculo-info-bottom-right{
    display: table-cell;
    vertical-align: middle;
    text-align:left;
  }

  .vehiculo-info-bottom-right{
    text-align:right;
  }

  .vehiculo-gas-icon{
    display:inline-block;
    height: 5mm;         /* icono de la gasolinera */
    width:auto;
    margin-right: 2mm;
    vertical-align: middle;
  }

  .vehiculo-inline-label{
    font-weight:700;
  }

  .vehiculo-inline-value{
    font-weight:400;
  }

  /* ==========================================================
     🔴 SECCIÓN ARRENDATARIO / ITINERARIO
  ========================================================== */

  .seccion-dos-columnas{
    margin: 6mm 16mm 4mm 16mm;
    width: calc(100% - 32mm); /* 16mm izq + 16mm der */
  }

  .seccion-dos-columnas-inner{
    display: table;
    width: 100%;
    table-layout: fixed;
  }

  .col-arrendatario,
  .col-itinerario{
    display: table-cell;
    vertical-align: top;
  }

  .col-arrendatario{
    padding-right: 8mm;
  }

  .col-itinerario{
    padding-left: 8mm;
  }

  .titulo-col-rojo{
    font-family: "Bahnschrift", Arial, sans-serif;
    font-weight: 700;
    font-size: 11.5pt;
    color: var(--brand);
    letter-spacing: 0.14em;
    text-transform: uppercase;
    margin: 0 0 3mm 0;
  }

  .titulo-col-derecha{
    text-align: right;
  }

  .arrendatario-datos{
    font-family: Arial, sans-serif;
    font-size: 9.3pt;
    color: #000;
  }

  .arrendatario-row{
    margin-bottom: 1.8mm;
  }

  .arrendatario-label{
    font-weight: 700;
  }

  .arrendatario-label-inline{
    margin-left: 10mm;
  }

  .arrendatario-value{
    font-weight: 400;
  }

  .arrendatario-underline{
    display: inline-block;
    padding-bottom: 0.3mm;
    border-bottom: 0.4pt solid #000;
    min-width: 35mm;
  }

  .arrendatario-tabla-licencia{
    width: 100%;
    margin-top: 3mm;
    border-collapse: collapse;
    font-family: Arial, sans-serif;
    font-size: 8.8pt;
  }

  .arrendatario-tabla-licencia thead tr th{
    border-top: 0.5pt solid #000;
    padding-top: 1.5mm;
    font-weight: 700;
    text-align: left;
  }

  .arrendatario-tabla-licencia tbody tr td{
    padding-top: 0.8mm;
    font-weight: 400;
  }

  .itinerario-bloque{
    font-family: Arial, sans-serif;
    font-size: 9.3pt;
    margin-bottom: 6mm;
  }

  .itinerario-label{
    font-weight: 700;
    margin: 0 0 1mm 0;
  }

  .itinerario-texto{
    margin: 0;
    line-height: 1.3;
  }

    /* ==========================================================
     🔴 SECCIÓN TARIFAS / ADICIONALES (franja roja abajo)
  ========================================================== */

  .tarifas-adicionales-wrap{
    margin-top: 6mm;
    background: var(--brand);
    color:#ffffff;
    padding: 4mm 0 5mm 0;
    width:100%;
  }

  .tarifas-adicionales-inner{
    margin: 0 16mm;
    width: calc(100% - 32mm); /* 16mm izq + 16mm der */
    display: table;
    table-layout: fixed;
  }

  .tarifas-col{
    display: table-cell;
    vertical-align: top;
    font-family: Arial, sans-serif;
    font-size: 8.8pt;
  }

  .tarifas-col-left{
    padding-right: 4mm;
    border-right: 0.4pt solid rgba(255,255,255,0.7);
  }

  .tarifas-col-right{
    padding-left: 4mm;
  }

  .tarifas-titulo{
    font-family: "Bahnschrift", Arial, sans-serif;
    font-weight: 700;
    font-size: 11.5pt;
    letter-spacing: 0.14em;
    text-transform: uppercase;
    margin: 0 0 3mm 0;
    color:#ffffff;
  }

  .tarifas-tabla,
  .adicionales-tabla{
    width:100%;
    border-collapse: collapse;
    font-family: Arial, sans-serif;
    font-size: 8.8pt;
  }

  .tarifas-tabla thead th,
  .adicionales-tabla thead th{
    padding: 1mm 0;
    border-bottom: 0.4pt solid rgba(255,255,255,0.7);
    text-align:left;
    font-weight:700;
  }

  .tarifas-tabla tbody td,
  .adicionales-tabla tbody td{
    padding: 0.9mm 0;
    border-bottom: 0.2pt solid rgba(255,255,255,0.25);
  }

  .tarifas-tabla th:nth-child(2),
  .tarifas-tabla td:nth-child(2),
  .tarifas-tabla th:nth-child(3),
  .tarifas-tabla td:nth-child(3),
  .tarifas-tabla th:nth-child(4),
  .tarifas-tabla td:nth-child(4){
    text-align:right;
  }

  .adicionales-tabla th:nth-child(2),
  .adicionales-tabla td:nth-child(2),
  .adicionales-tabla th:nth-child(3),
  .adicionales-tabla td:nth-child(3){
    text-align:right;
  }

  .tarifas-totales-tabla{
    width:100%;
    margin-top: 3mm;
    border-collapse: collapse;
    font-size: 9pt;
  }

  .tarifas-totales-tabla td{
    padding-top: 0.7mm;
  }

  .tarifas-totales-tabla .lbl{
    font-weight:700;
  }

  .tarifas-totales-tabla .val{
    text-align:right;
    font-weight:700;
  }

  .tarifas-totales-tabla .total-label{
    padding-top: 1.4mm;
  }

  .tarifas-totales-tabla .total-value{
    padding-top: 1.4mm;
    font-size: 9.5pt;
  }

  /* ==========================================================
   🧾 SECCIÓN FINAL (aceptación + firma + gasolina + facturación + footer)
   - DomPDF friendly (tables)
========================================================== */

.seccion-final{
  margin: 0 16mm;
  width: calc(100% - 32mm);
  font-family: Arial, sans-serif;
  color: #111;
}

/* --- Bloque aceptación + firma --- */
.final-acepta-firma{
  display: table;
  width: 100%;
  table-layout: fixed;
  margin-top: 6mm;
  margin-bottom: 3mm;
}

.final-acepta{
  display: table-cell;
  width: 68%;
  vertical-align: top;
  font-size: 10pt;
  line-height: 1.35;
  padding-right: 6mm;
}

.final-firma{
  display: table-cell;
  width: 32%;
  vertical-align: top;
  text-align: center;
  padding-left: 2mm;
}

.final-firma-label{
  font-size: 12pt;
  color: #6b6b6b;
  margin: 2mm 0 3mm 0;
}

.final-firma-linea{
  border-top: 1.2pt solid var(--brand);
  margin: 1mm 0 3mm 0;
}

.final-firma-nombre{
  font-size: 14pt;
  font-weight: 700;
  margin: 0;
}

/* Imagen firma (si viene como imagen) */
.firma-img{
  display: block;
  margin: 0 auto 2mm auto;
  max-width: 60mm;
  height: 12mm;          /* ajusta si tu firma sale muy alta */
  object-fit: contain;
}

/* --- Lineas rojas separadoras --- */
.final-sep-roja{
  border-top: 1.2pt solid var(--brand);
  margin: 0;
}

/* --- Bloque gasolina / cargos --- */
.final-cargos{
  padding: 4mm 0 4mm 0;
  font-size: 9.2pt;
  line-height: 1.35;
}

.final-cargos .lbl{
  font-weight: 700;
}

.final-cargos h3{
  font-size: 10.5pt;
  margin: 2mm 0 1.5mm 0;
  font-weight: 800;
}

.final-cargos .nota{
  margin: 0 0 2mm 0;
}

.final-cargos .lista{
  margin: 0;
  padding: 0;
}

.final-cargos .lista span{
  display: inline-block;
  margin-right: 3mm;
}

/* --- Datos de facturación --- */
.final-facturacion{
  display: table;
  width: 100%;
  table-layout: fixed;
  padding: 5mm 0 5mm 0;
}

.factu-titulo{
  display: table-cell;
  width: 22%;
  vertical-align: top;
  font-family: "Bahnschrift", Arial, sans-serif;
  font-weight: 800;
  font-size: 16pt;
  line-height: 1.05;
  padding-right: 6mm;
}

.factu-datos{
  display: table-cell;
  width: 78%;
  vertical-align: top;
  font-size: 11pt;
  color: #2b2b2b;
}

/* rejilla de datos (2 filas tipo columnas) */
.factu-grid{
  display: table;
  width: 100%;
  table-layout: fixed;
}

.factu-row{
  display: table-row;
}

.factu-cell{
  display: table-cell;
  vertical-align: top;
  padding-bottom: 2mm;
}

.factu-cell.w25{ width: 25%; }
.factu-cell.w30{ width: 30%; }
.factu-cell.w35{ width: 35%; }
.factu-cell.w40{ width: 40%; }
.factu-cell.w50{ width: 50%; }
.factu-cell.w60{ width: 60%; }

.factu-label{
  font-weight: 800;
  margin-right: 2mm;
}

.factu-value{
  color: #808080;
}

/* --- Footer rojo (como banda inferior) --- */
.footer-rojo{
  margin-top: 0;
  background: var(--brand-2);
  color: #fff;
  width: 100%;
  padding: 6mm 0 6mm 0;
}

.footer-rojo-inner{
  margin: 0 16mm;
  width: calc(100% - 32mm);
  display: table;
  table-layout: fixed;
}

.footer-col{
  display: table-cell;
  vertical-align: top;
  font-size: 10.5pt;
  line-height: 1.35;
}

.footer-col-left{
  width: 60%;
  padding-right: 6mm;
}

.footer-col-right{
  width: 40%;
  padding-left: 6mm;
}

.footer-titulo{
  font-family: "Bahnschrift", Arial, sans-serif;
  font-weight: 900;
  font-size: 16pt;
  margin: 0 0 2mm 0;
  letter-spacing: 0.02em;
}

.footer-strong{
  font-weight: 800;
}

/* ==========================================================
   📄 SALTO DE PÁGINA (HOJA 2)
========================================================== */
.page-break{
  page-break-before: always;   /* DomPDF */
  break-before: page;          /* Chromium */
}

/* ==========================================================
   🧾 HOJA 2 - CLAUSULAS (estilo como PDF diseñador)
   - A4 con margin 6mm ya lo tienes en @page
   - DomPDF/Chromium friendly
========================================================== */

.page-break{
  page-break-before: always;
}

.clausulas-page{
  /* Respetamos el margin de @page; aquí solo controlamos “aire” interno */
  padding-top: 6mm;
  font-family: Arial, sans-serif;  /* en el PDF se ve más “Arial/Helvetica” que Bahnschrift */
  color:#111;
}

/* Párrafo superior centrado */
.clausulas-intro{
  text-align:center;
  font-size: 10.2pt;
  line-height: 1.25;
  letter-spacing: 0.02em;
  text-transform: uppercase;
  margin: 0 8mm 6mm 8mm; /* un poquito de margen lateral extra como el diseño */
}

/* Título “CLAUSULAS” grande */
.clausulas-title{
  text-align:center;
  font-size: 30pt;
  font-weight: 800;
  letter-spacing: 0.02em;
  color:#4a4a4a;
  margin: 0 0 8mm 0;
}

/* Cuerpo de cláusulas */
.clausulas-body{
  margin: 0 8mm; /* el diseñador deja más “colchón” a los lados */
}

.clausulas-body p{
  margin: 0 0 4.2mm 0;   /* separación entre cláusulas */
  font-size: 9.4pt;
  line-height: 1.35;
  text-align: justify;
}

/* Etiqueta PRIMERA., SEGUNDA., etc */
.clausula-tag{
  font-weight: 800;
  text-transform: uppercase;
}

/* Bloque gasolina y cargos (abajo) */
.clausulas-notas{
  margin: 6mm 8mm 0 8mm;
  font-size: 8.6pt;
  line-height: 1.28;
  color:#222;
}

.clausulas-notas .nota-titulo{
  font-weight: 800;
  text-transform: uppercase;
}

.clausulas-notas .nota{
  margin: 1.6mm 0 3mm 0;
}

/* Renglón “En ____ al día __ del mes __ del año __” */
.clausulas-fecha{
  margin: 7mm 8mm 2mm 8mm;
  text-align:center;
  font-size: 10pt;
  color:#333;
}

/* Línea roja tipo “subrayado” (como el PDF) */
.linea-roja{
  display:inline-block;
  border-bottom: 1.2pt solid var(--brand-2);
  min-width: 55mm;
  height: 4mm;
  vertical-align: bottom;
  margin: 0 2mm;
}

/* Firma: dos columnas centradas */
.clausulas-firmas{
  margin: 3mm 8mm 0 8mm;
  width: calc(100% - 16mm);
  display: table;
  table-layout: fixed;
}

.firma-col{
  display: table-cell;
  width: 50%;
  text-align: center;
  vertical-align: top;
}

.firma-label{
  font-size: 10pt;
  color:#666;
  margin: 0 0 2mm 0;
}

.firma-line{
  width: 70%;
  margin: 0 auto 2mm auto;
  border-top: 1.2pt solid var(--brand-2);
}

.firma-nombre{
  font-size: 11pt;
  font-weight: 700;
  margin: 0;
}
  </style>
</head>

<body>
@php
    use Carbon\Carbon;

    // Nombre completo del cliente
    $nombreCompletoCliente = trim(
        ($reservacion->nombre_cliente ?? '') . ' ' . ($reservacion->apellidos_cliente ?? '')
    );

    // Nombre que se usará en el saludo
    $nombreSaludo = $reservacion->nombre_cliente
        ?? ($nombreCompletoCliente !== '' ? $nombreCompletoCliente : 'Cliente');

    // Tipo de cambio
    $tipoCambioValor = $tipoCambio ?? null;
    $textoTipoCambio = $tipoCambioValor !== null
        ? '$' . number_format($tipoCambioValor, 2) . ' MXN'
        : '—';

    // Fecha de apertura
    $fechaAperturaRaw = $contrato->fecha_apertura
        ?? (isset($reservacion->fecha_inicio)
            ? ($reservacion->fecha_inicio . ' ' . ($reservacion->hora_retiro ?? '00:00'))
            : null);

    if ($fechaAperturaRaw) {
        $fechaAperturaCarbon = Carbon::parse($fechaAperturaRaw);
        $textoFechaApertura = $fechaAperturaCarbon->format('d/m/y g:i a');
    } else {
        $textoFechaApertura = '—';
    }
@endphp

<div class="contrato-final-container">

  {{-- 🧾 ENCABEZADO --}}
  <header class="header-contrato">
    <div class="header-layout">
      {{-- LADO IZQUIERDO: LOGO + MENSAJE --}}
      <div class="header-left">
        {{-- Cambia la ruta del logo --}}
        <img
          class="logo-viajero"
          src="{{ public_path('img/VIAJEROPDF.png') }}"
          alt="Viajero Car Rental"
        >

        <div class="header-textos">
          <h1 class="header-titulo">
            Este es nuestro acuerdo, {{ $nombreSaludo }}
          </h1>
          <p class="header-subtitulo">
            Disfruta el camino tanto como tu destino.
          </p>
        </div>
      </div>

      {{-- LADO DERECHO: FIGURA + ETIQUETAS ROJAS --}}
      <div class="header-right">
        {{-- Cambia la ruta de la figura gris --}}
        <img
          class="header-figura"
          src="{{ public_path('img/A.png') }}"
          alt=""
        >

        <div class="header-datos">
          <p class="header-datos-linea">
            No. Rental Agreement:
            <span class="chip-rojo">
              {{ $contrato->numero_contrato ?? '—' }}
            </span>
          </p>

          <p class="header-datos-linea">
            Tipo de cambio:
            <span class="chip-rojo">
              {{ $textoTipoCambio }}
            </span>
          </p>

          <p class="header-datos-linea">
            Fecha de apertura:
            <span class="chip-rojo">
              {{ $textoFechaApertura }}
            </span>
          </p>

          <p class="header-datos-linea">
            Reservación:
            <span class="chip-rojo">
              {{ $reservacion->codigo ?? '—' }}
            </span>
          </p>
        </div>
      </div>
    </div>
  </header>

  {{-- TÍTULO: INFORMACIÓN DE TU VEHÍCULO --}}
  <div class="titulo-seccion-vehiculo">
    <div class="titulo-vehiculo-texto">
      INFORMACIÓN DE TU VEHÍCULO
    </div>
    <div class="titulo-vehiculo-linea"></div>
  </div>

  {{-- FRANJA ROJA CON DATOS DEL VEHÍCULO --}}
  <div class="vehiculo-info-box">
    <div class="vehiculo-info-top">
      <div class="vehiculo-info-item">
        <span class="vehiculo-info-label">Modelo:</span>
        <span class="vehiculo-info-value">
          {{ $vehiculo->modelo ?? '—' }}
        </span>
      </div>

      <div class="vehiculo-info-item">
        <span class="vehiculo-info-label">Categoría:</span>
        <span class="vehiculo-info-value">
          {{ $vehiculo->categoria ?? '—' }}
        </span>
      </div>

      <div class="vehiculo-info-item">
        <span class="vehiculo-info-label">Color:</span>
        <span class="vehiculo-info-value">
          {{ $vehiculo->color ?? '—' }}
        </span>
      </div>

      <div class="vehiculo-info-item">
        <span class="vehiculo-info-label">Placas:</span>
        <span class="vehiculo-info-value">
          {{ $vehiculo->placas ?? '—' }}
        </span>
      </div>

      <div class="vehiculo-info-item">
        <span class="vehiculo-info-label">Transmisión:</span>
        <span class="vehiculo-info-value">
          {{ $vehiculo->transmision ?? '—' }}
        </span>
      </div>

      <div class="vehiculo-info-item">
        <span class="vehiculo-info-label">Kilometraje:</span>
        <span class="vehiculo-info-value">
          {{ isset($vehiculo->kilometraje) ? number_format($vehiculo->kilometraje, 0) : '—' }}
        </span>
      </div>
    </div>

    <div class="vehiculo-info-bottom">
      <div class="vehiculo-info-bottom-left">
        {{-- Cambia la ruta del icono --}}
        <img
          class="vehiculo-gas-icon"
          src="{{ public_path('img/icono-gasolina.png') }}"
          alt="Gasolina"
        >
        <span class="vehiculo-inline-label">Capacidad del tanque:</span>
        <span class="vehiculo-inline-value">
          {{ $vehiculo->capacidad_tanque ?? '—' }}
        </span>
      </div>

      <div class="vehiculo-info-bottom-right">
        <span class="vehiculo-inline-label">Gasolina de salida:</span>
        <span class="vehiculo-inline-value">
          {{ $contrato->gasolina_inicial ?? '—' }}
        </span>
      </div>
    </div>
  </div>

</div> {{-- /contrato-final-container --}}

{{-- Cálculos para DOB, edad y fechas de check in/out --}}
@php
// ✅ Locale español para días/meses
\Carbon\Carbon::setLocale('es');

$dobTexto  = '—';
$edadTexto = '—';

// ✅ DOB: ya viene desde reservación o “inyectado” por el controlador
$fechaNacRaw = $reservacion->fecha_nacimiento ?? null;

if (!empty($fechaNacRaw)) {
    try {
        $fn = \Carbon\Carbon::parse($fechaNacRaw);


        $dobTexto = mb_strtoupper($fn->translatedFormat('d/M/Y'), 'UTF-8');

        $edadTexto = $fn->age . ' años';
    } catch (\Exception $e) {
        $dobTexto  = '—';
        $edadTexto = '—';
    }
}


// Helper para dejarlo como: JUE 05 MAR 2026 - 14:30 HRS
$formatearItinerario = function ($carbon) {

    // Día y mes en español
    $fecha = $carbon->translatedFormat('D d M Y');

    // Mayúsculas (incluye acentos)
    $fecha = mb_strtoupper($fecha, 'UTF-8');

    // Hora 24h
    $hora = $carbon->format('H:i');

    return $fecha . ' - ' . $hora . ' HRS';
};


// Check out
$textoCheckOut = '—';
if (!empty($reservacion->fecha_inicio)) {
    $co = \Carbon\Carbon::parse(
        $reservacion->fecha_inicio . ' ' . ($reservacion->hora_retiro ?? '00:00')
    );

    $textoCheckOut = $formatearItinerario($co);
}


// Check in
$textoCheckIn = '—';
if (!empty($reservacion->fecha_fin)) {
    $ci = \Carbon\Carbon::parse(
        $reservacion->fecha_fin . ' ' . ($reservacion->hora_entrega ?? '00:00')
    );

    $textoCheckIn = $formatearItinerario($ci);
}
@endphp

{{-- SECCIÓN: ARRENDATARIO / ITINERARIO --}}
<div class="seccion-dos-columnas">
  <div class="seccion-dos-columnas-inner">

    {{-- ARRENDATARIO --}}
    <div class="col-arrendatario">
      <h2 class="titulo-col-rojo">ARRENDATARIO</h2>

      <div class="arrendatario-datos">

        <div class="arrendatario-row">
          <span class="arrendatario-label">Nombre:</span>
          <span class="arrendatario-value">
            {{ $nombreCompletoCliente !== '' ? $nombreCompletoCliente : ($reservacion->nombre_cliente ?? '—') }}
          </span>
        </div>

        <div class="arrendatario-row">
          <span class="arrendatario-label">Fecha de nacimiento (DOB):</span>
          <span class="arrendatario-value">{{ $dobTexto !== '—' ? '(' . $dobTexto . ')' : '—' }}</span>
        </div>

        <div class="arrendatario-row">
          <span class="arrendatario-label">Edad:</span>
          <span class="arrendatario-value">{{ $edadTexto }}</span>

          <span class="arrendatario-label arrendatario-label-inline">Teléfono:</span>
          <span class="arrendatario-value">
            {{ $reservacion->telefono_cliente ?? '—' }}
          </span>
        </div>

        <div class="arrendatario-row">
          <span class="arrendatario-label">Correo:</span>
          <span class="arrendatario-value">
            {{ $reservacion->email_cliente ?? '—' }}
          </span>
        </div>


        <table class="arrendatario-tabla-licencia">
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
              <td>{{ $licencia->estado_emision ?? '—' }}</td>
            </tr>
          </tbody>
        </table>

      </div>
    </div>

    {{-- ITINERARIO --}}
    <div class="col-itinerario">
      <h2 class="titulo-col-rojo titulo-col-derecha">ITINERARIO</h2>

      <div class="itinerario-bloque">
        <p class="itinerario-label">Check in:</p>
        <p class="itinerario-texto">
          {{ $reservacion->sucursal_retiro_nombre ?? '—' }}<br>
          {{ $textoCheckOut }}
        </p>
      </div>

      <div class="itinerario-bloque">
        <p class="itinerario-label">Check out:</p>
        <p class="itinerario-texto">
          {{ $reservacion->sucursal_entrega_nombre ?? '—' }}<br>
          {{ $textoCheckIn }}
        </p>
      </div>

    </div>

  </div>
</div>
{{-- =======================================================
     SECCIÓN TARIFAS / ADICIONALES (franja roja)
   ======================================================= --}}
@php
    // Cálculos para mostrar en el cuadro de totales
    $ivaCalc        = $subtotal * 0.16;
    $cuotasLocales  = 0; // si más adelante definen un monto, va aquí
@endphp

<div class="tarifas-adicionales-wrap">
  <div class="tarifas-adicionales-inner">

    {{-- COLUMNA IZQUIERDA: TARIFAS --}}
    <div class="tarifas-col tarifas-col-left">
      <h2 class="tarifas-titulo">TARIFAS</h2>

      <table class="tarifas-tabla">
        <thead>
          <tr>
            <th>Concepto</th>
            <th>Días</th>
            <th>Precio por día</th>
            <th>Total</th>
          </tr>
        </thead>
        <tbody>
          {{-- Tarifa base --}}
          <tr>
            <td>Tarifa base</td>
            <td>{{ $dias ?? 1 }}</td>
            <td>${{ number_format($tarifaBase ?? 0, 2) }}</td>
            <td>${{ number_format(($tarifaBase ?? 0) * ($dias ?? 1), 2) }}</td>
          </tr>

          {{-- Paquetes de seguro --}}
          @foreach(($paquetes ?? []) as $p)
            <tr>
              <td>{{ $p->nombre ?? 'Paquete' }}</td>
              <td>{{ $dias ?? 1 }}</td>
              <td>${{ number_format($p->precio_por_dia ?? 0, 2) }}</td>
              <td>${{ number_format(($p->precio_por_dia ?? 0) * ($dias ?? 1), 2) }}</td>
            </tr>
          @endforeach

          {{-- Seguros individuales --}}
          @foreach(($individuales ?? []) as $i)
            <tr>
              <td>{{ $i->nombre ?? 'Protección' }}</td>
              <td>{{ $dias ?? 1 }}</td>
              <td>${{ number_format($i->precio_por_dia ?? 0, 2) }}</td>
              <td>${{ number_format(($i->precio_por_dia ?? 0) * ($dias ?? 1), 2) }}</td>
            </tr>
          @endforeach
        </tbody>
      </table>

      {{-- Totales al pie, igual que en el diseño --}}
      <table class="tarifas-totales-tabla">
        <tr>
          <td class="lbl">Subtotal:</td>
          <td class="val">
            ${{ number_format($subtotal ?? 0, 2) }}
          </td>
        </tr>
        <tr>
          <td class="lbl">I.V.A.</td>
          <td class="val">
            ${{ number_format($ivaCalc, 2) }}
          </td>
        </tr>
        <tr>
          <td class="lbl">Cuotas locales e impuestos federales</td>
          <td class="val">
            ${{ number_format($cuotasLocales, 2) }}
          </td>
        </tr>
        <tr>
          <td class="lbl total-label">TOTAL:</td>
          <td class="val total-value">
            ${{ number_format($totalFinal ?? (($subtotal ?? 0) + $ivaCalc + $cuotasLocales), 2) }}
          </td>
        </tr>
      </table>
    </div>

    {{-- COLUMNA DERECHA: ADICIONALES --}}
    <div class="tarifas-col tarifas-col-right">
      <h2 class="tarifas-titulo">ADICIONALES</h2>

      <table class="adicionales-tabla">
        <thead>
          <tr>
            <th>Producto</th>
            <th>Días</th>
            <th>Precio por día</th>
          </tr>
        </thead>
        <tbody>
          @forelse(($extras ?? []) as $e)
            <tr>
              <td>{{ $e->nombre ?? 'Servicio' }}</td>
              <td>{{ $dias ?? 1 }}</td>
              <td>${{ number_format($e->precio_unitario ?? 0, 2) }}</td>
            </tr>
          @empty
            {{-- Si no hay extras, dejamos una fila vacía para que no se vea el cuadro en blanco --}}
            <tr>
              <td colspan="3">Sin adicionales contratados.</td>
            </tr>
          @endforelse
        </tbody>
      </table>
    </div>



  </div>
</div>
{{-- =======================================================
     SECCIÓN FINAL (aceptación + firma + gasolina/cargos + datos facturación + footer)
   ======================================================= --}}
@php
  // Nombre arrendatario (ya lo traes calculado arriba en $nombreCompletoCliente)
  $nombreArrendatarioFirma = $nombreCompletoCliente !== '' ? $nombreCompletoCliente : ($reservacion->nombre_cliente ?? '—');

  // ✅ Firma arrendador desde contratos
  // Ajusta el nombre del campo según tu BD: firma_arrendador / firma / etc.
  // Puede ser:
  //  - Ruta relativa en public (ej. "firmas/firma.png")
  //  - o null
  $firmaArrendadorPath = !empty($contrato->firma_arrendador)
      ? public_path($contrato->firma_arrendador)
      : null;
@endphp

<div class="seccion-final">

  {{-- Aceptación + firma --}}
  <div class="final-acepta-firma">
    <div class="final-acepta">
      Acepto plenamente las obligaciones descritas en la carátula y en el clausulado de este contrato.
      Declaro bajo protesta de decir verdad, haber recibido el auto descrito en el apartado de salida y
      acepto las condiciones generales al inicio de la renta, así mismo entiendo y acepto las condiciones
      del tratamiento de mis datos personales como se describe en el aviso de privacidad que se encuentra
      a mi disposición en: https://www.viajerocarental.mx/
    </div>

    <div class="final-firma">

  {{-- Firma del ARRENDATARIO (cliente) desde BD --}}
  @php
    $firmaClienteRaw = $contrato->firma_cliente ?? null;

    $firmaClienteSrc = null;
    if (!empty($firmaClienteRaw)) {
        $firmaClienteSrc = str_starts_with($firmaClienteRaw, 'data:image')
            ? $firmaClienteRaw
            : 'data:image/png;base64,' . $firmaClienteRaw;
    }
  @endphp

  {{-- Firma manuscrita --}}
  @if(!empty($firmaClienteSrc))
    <img class="firma-img" src="{{ $firmaClienteSrc }}" alt="Firma arrendatario">
  @endif

  {{-- Línea de firma --}}
  <div class="final-firma-linea"></div>

  {{-- Nombre del arrendatario --}}
  <p class="final-firma-nombre">{{ $nombreArrendatarioFirma }}</p>

</div>
  </div>

  <hr class="final-sep-roja">

  {{-- Gasolina + info cargos --}}
  <div class="final-cargos">

  <div class="nota">
    <span class="lbl">GASOLINA:</span>
    PRECIO POR LITRO FALTANTE $13.16 MXN MAS CARGO POR SERVICIO DE 23.96 MXN POR LITRO FALTANTE
    IMPUESTOS INCLUIDOS (APLICABLE SI LA OPCION DE PREPAGO DE GAS NO FUE ADQUIRIDA)
  </div>

  <h3>INFORMACIÓN DE LOS CARGOS TOTALES:</h3>

  <div class="nota">
    <strong>(1)</strong> Al firmar este contrato el cliente declara tener conocimiento de todas las condiciones establecidas
    y acepta el clausulado al reverso.
    <strong>(2)</strong> Los cargos son ESTIMADOS, el importe total a pagar del contrato aparecera al cierre del mismo.
    <strong>(3)</strong> Usted va a alquilar y devolver el vehiculo en el momento y lugares indicados. Gasolina no reembolsable
    en prepago, EXCEPTO si se regresa con tanque lleno.
  </div>

  <div class="nota">
    <span class="lbl"><strong>1.</strong></span> NO SE ACEPTA EFECTIVO como pago ni como deposito.
    <span class="lbl"><strong>2.</strong></span> CDW 0% incluye: ROBO %, llantas, rines, cristales y espejos.
    <span class="lbl"><strong>3.</strong></span> CDW20%, CDW10%, PCDW NO incluye llantas, rines, cristales y espejos.
    <span class="lbl"><strong>4.</strong></span> Ninguna protección cubre GPS, Placas o llaves
    <span class="lbl"><strong>5.</strong></span> CDW20%, CDW10%, PDW, LDW revocado en caso de negligencia del conductor o si existen conductores NO autorizados en el contrato.
  </div>

</div>

  <hr class="final-sep-roja">

  {{-- Datos de facturación (estático como la imagen)
  <div class="final-facturacion">
    <div class="factu-titulo">
      Datos de<br>Facturación
    </div>

    <div class="factu-datos">
      <div class="factu-grid">

        <div class="factu-row">
          <div class="factu-cell w30">
            <span class="factu-label">No. cliente fiscal:</span>
            <span class="factu-value">2</span>
          </div>

          <div class="factu-cell w35">
            <span class="factu-label">Calle:</span>
            <span class="factu-value">Lagos de Pátzcuaro</span>
          </div>

          <div class="factu-cell w35">
            <span class="factu-label">Colonia:</span>
            <span class="factu-value">Capital Sur</span>
          </div>
        </div>

        <div class="factu-row">
          <div class="factu-cell w30">
            <span class="factu-label">RFC:</span>
            <span class="factu-value">SORH930507TG3</span>
          </div>

          <div class="factu-cell w35">
            <span class="factu-label">No. Ext.</span>
            <span class="factu-value">200</span>
            &nbsp;&nbsp;
            <span class="factu-label">No. Int.</span>
            <span class="factu-value">88</span>
          </div>

          <div class="factu-cell w35">
            <span class="factu-label">Estado:</span>
            <span class="factu-value">Querétaro</span>
          </div>
        </div>

        <div class="factu-row">
          <div class="factu-cell w60">
            <span class="factu-label">Razón social:</span>
            <span class="factu-value">HORACIO DE JESÚS SOTELO DE LA ROSA</span>
          </div>

          <div class="factu-cell w40">
            <span class="factu-label">Municipio:</span>
            <span class="factu-value">El Marqués</span>
          </div>
        </div>

        <div class="factu-row">
          <div class="factu-cell w60">
            <span class="factu-label">C.P.</span>
            <span class="factu-value">76246</span>
            &nbsp;&nbsp;&nbsp;
            <span class="factu-label">Ciudad:</span>
            <span class="factu-value">San Isidro Miranda</span>
          </div>

          <div class="factu-cell w40">
            <span class="factu-label">País:</span>
            <span class="factu-value">México</span>
          </div>
        </div>

      </div>
    </div>
  </div>--}}

</div> {{-- /seccion-final --}}

{{-- Footer rojo final --}}
<div class="footer-rojo">
  <div class="footer-rojo-inner">
    <div class="footer-col footer-col-left">
      <div class="footer-titulo">VIAJERO CAR RENTAL</div>
      Business Center INNERA Central Park, Armando Birlain Shaffler #2001, Torre 2, Centro Sur, Qro.<br>
      Teléfono de reservaciones: 442 303 26 68 &nbsp;&nbsp;&nbsp;&nbsp;
      Celular de módulo: 442 716 97 93 y 442 343 07 70
    </div>

    <div class="footer-col footer-col-right">
      <span class="footer-strong">Arrendador:</span> José Juan de Dios Hernández Resendiz<br>
      <span class="footer-strong">Facturación:</span> facturación@viajerocar-rental.com<br>
      <span class="footer-strong">Reservaciones:</span> reservaciones@viajerocar-rental.com
    </div>
  </div>
</div>

{{-- ===========================
     HOJA 2 - CLAUSULAS
=========================== --}}
<div class="page-break"></div>

@php
  // Nombres (ajusta si tú ya los tienes en variables)
  $arrendadorNombre = $arrendadorNombre ?? 'Juan de Dios Hernández Resendiz';
  $arrendatarioNombre = $nombreArrendatarioFirma ?? ($reservacion->nombre_cliente ?? '—');

  // Datos de fecha/lugar (puedes reemplazar por datos reales si ya los tienes)
  $lugarFirma = $lugarFirma ?? 'Santiago de Querétaro';
  $diaFirma   = $diaFirma   ?? '12';
  $mesFirma   = $mesFirma   ?? 'Febrero';
  $anioFirma  = $anioFirma  ?? '2026';
@endphp

<section class="clausulas-page">

  <div class="clausulas-intro">
    CONTRATO DE ARRENDAMIENTO, QUE CELEBRA POR UNA PARTE LA COMPAÑÍA CUYA RAZÓN SOCIAL APARECE EN EL APARTADO NO. 1 DEL ANVERSO DE ESTE CONTRATO COMO ARRENDADORA, Y POR LA OTRA, LA PERSONA CUYO NOMBRE APARECE EN EL APARTADO NO. 2 DEL ANVERSO DE ESTE CONTRATO, CON CARÁCTER DE ARRENDATARIA.
  </div>

  <div class="clausulas-title">CLAUSULAS</div>

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
      <span class="nota-titulo">4.</span> Ninguna protección cubre GPS. Placas o llaves
      <span class="nota-titulo">5.</span> CDW20%, CDW10%, PDW, LDW revocado en caso de negligencia del conductor o si existen conductores NO autorizados en el contrato.
    </div>
  </div>

 <div class="clausulas-fecha">
  En <span class="linea-roja">{{ $lugarFirma }}</span>
  al día <span class="linea-roja" style="min-width:18mm;">{{ $diaFirma }}</span>
  del mes de <span class="linea-roja" style="min-width:32mm;">{{ $mesFirma }}</span>
  del año <span class="linea-roja" style="min-width:22mm;">{{ $anioFirma }}</span>
</div>

<div class="clausulas-firmas">

  {{-- ===============================
       FIRMA ARRENDADOR
  =============================== --}}
  <div class="firma-col">

    @php
      $firmaArrRaw = $contrato->firma_arrendador ?? null;

      $firmaArrSrc = null;
      if (!empty($firmaArrRaw)) {
          $firmaArrSrc = str_starts_with($firmaArrRaw, 'data:image')
              ? $firmaArrRaw
              : 'data:image/png;base64,' . $firmaArrRaw;
      }
    @endphp

    @if(!empty($firmaArrSrc))
      <img class="firma-img" src="{{ $firmaArrSrc }}" alt="Firma arrendador">
    @endif

    <div class="firma-line"></div>
    <p class="firma-nombre">{{ $arrendadorNombre }}</p>

  </div>


  {{-- ===============================
       FIRMA ARRENDATARIO (CLIENTE)
  =============================== --}}
  <div class="firma-col">

    @php
      $firmaClienteRaw = $contrato->firma_cliente ?? null;

      $firmaClienteSrc = null;
      if (!empty($firmaClienteRaw)) {
          $firmaClienteSrc = str_starts_with($firmaClienteRaw, 'data:image')
              ? $firmaClienteRaw
              : 'data:image/png;base64,' . $firmaClienteRaw;
      }
    @endphp

    @if(!empty($firmaClienteSrc))
      <img class="firma-img" src="{{ $firmaClienteSrc }}" alt="Firma arrendatario">
    @endif

    <div class="firma-line"></div>
    <p class="firma-nombre">{{ $arrendatarioNombre }}</p>

  </div>

</div>

</section>
</body>
</html>
