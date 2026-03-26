{{-- resources/views/Admin/checklist_pdf_interno.blade.php --}}
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <title>Checklist Interno - PDF</title>

  <style>
  @page{
  size: 230mm 470mm; /* igual que cliente */
  margin: 20mm;
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

*{ box-sizing:border-box; }

html, body{
  margin:0;
  padding:0;
  background:#fff !important;
  color:var(--ink);
  font-family: DejaVu Sans, Arial, sans-serif !important;
  font-size:11px;
}

.checklist-container-pdf{
  width:100%;
  margin:0;
  padding:0;
  background:#fff;
}

h1,h2,h3,h4,p{
  margin:0;
  padding:0;
}

/* Ocultar cosas de la vista web si se reusa */
.modal-firma,
.checklist-actions,
.uploader,
.preview{
  display:none !important;
}

/* ===================================================== */
/* HEADER (estilo muy parecido al del cliente)           */
/* ===================================================== */

.cl-header{
  width:100%;
  display: table;
  table-layout: fixed;
  background:#fff;
  color:var(--ink);
  padding: 8px 6px 12px;
  margin-bottom: 10px;
  border-bottom: 3px solid var(--brand);
}

.cl-header > div{
  display: table-cell;
  vertical-align: middle;
}

.cl-logo{
  width:170px;
  text-align:left;
}
.cl-logo img{
  width:150px;
  height:auto;
}

.cl-title-box{
  text-align:center;
  padding: 0 10px;
}
.cl-title-box h1{
  margin:0;
  font-size:16px;
  font-weight:900;
  letter-spacing:.6px;
}
.cl-title-box h2{
  margin: 3px 0 6px;
  font-size:11px;
  font-weight:700;
  color:#374151;
}

.office-info{
  margin-top:2px;
  font-size:9.5px;
  line-height:1.3;
  color: var(--muted);
}

.cl-ra-box{
  width:165px;
  text-align:center;
  background:#f7f7f8;
  border:1px solid var(--stroke);
  border-radius:10px;
  padding:8px 6px;
  font-size:9.5px;
}
.cl-ra-box span{
  display:block;
  color:#4b5563;
}
.cl-ra-box strong{
  display:block;
  margin-top:4px;
  font-size:13px;
  font-weight:900;
}
.cl-ra-box small{
  display:block;
  margin-top:3px;
  font-size:9px;
  color:var(--muted);
}

/* ===================================================== */
/* SECCIONES (tarjetas)                                  */
/* ===================================================== */

.paper-section{
  background:#fff;
  border:1px solid var(--line);
  border-radius:14px;
  padding:16px;
  margin-top:14px;
  page-break-inside: avoid;
}

/* mismas tarjetas que el cliente usa como sec-block */
.sec-block{
  background:#fff;
  border:1px solid var(--line);
  border-radius:14px;
  padding:16px;
  margin-top:14px;
  page-break-inside: avoid;
}

.sec-title{
  font-size:12px;
  font-weight:900;
  margin-bottom:6px;
  text-transform:uppercase;
}
.sec-title.center{
  text-align:center;
}

/* ===================================================== */
/* TABLAS                                                */
/* ===================================================== */

table{
  width:100%;
  border-collapse:collapse;
}

/* tabla de datos del vehículo (ajustada para no desbordar) */
.vehicle-table{
  width:100%;
  border-collapse:collapse;
  font-size:11px;
  table-layout:fixed;  /* clave para que respete el ancho */
}

.vehicle-table th,
.vehicle-table td{
  border:1px solid var(--line);
  padding:6px 6px;      /* un poco menos padding */
  text-align:center;
}

/* aquí quitamos nowrap para que KILOMETRAJE REGRESO pueda partirse */
.vehicle-table th{
  background:#f3f4f6;
  font-weight:700;
  white-space:normal;
  word-break:break-word;
}

/* por si usas otras tablas sencillas */
th,td{
  border:1px solid var(--line);
  padding:5px 6px;
  font-size:11px;
  text-align:center;
}

/* ===================================================== */
/* TEXTOS / COMENTARIOS                                  */
/* ===================================================== */

.comment-box{
  border:1px solid var(--stroke);
  border-radius:8px;
  padding:8px 10px;
  min-height:70px;
  font-size:10.5px;
  line-height:1.45;
  background:#fff;
  white-space:pre-wrap;
  overflow-wrap:anywhere;
  word-break:break-word;
  margin-bottom:4px;
}

.legal-text{
  font-size:9.8px;
  line-height:1.4;
  text-align:left;
  margin-bottom:8px;
  overflow-wrap:anywhere;
  word-break:break-word;
}

.rules-list{
  margin:0;
  padding-left:16px;
  font-size:10.5px;
}
.rules-list li{
  margin-bottom:4px;
  line-height:1.35;
  color:#111;
  overflow-wrap:anywhere;
  word-break:break-word;
}

/* ===================================================== */
/* ACEPTACIÓN DE CARGOS                                  */
/* ===================================================== */

.accept-line{
  margin-top:6px;
  display: table;
  width:100%;
  table-layout: fixed;
}
.accept-label{
  display: table-cell;
  width:30%;
  vertical-align: middle;
  font-size: 10.5px;
  font-weight: 700;
}
.accept-sign{
  display: table-cell;
  width:70%;
  vertical-align: middle;
  text-align:left;
}
.xbox{
  display:inline-block;
  width:18px;
  height:18px;
  border:1px solid var(--ink);
  text-align:center;
  font-size:11px;
  line-height:16px;
  margin-left:8px;
}

/* ===================================================== */
/* LAYOUT DOS COLUMNAS (comentario / daños interiores)   */
/* ===================================================== */

.two-cols{
  width:100%;
  display: table;
  table-layout: fixed;
}
.two-cols > div{
  display: table-cell;
  width:50%;
  vertical-align: top;
  padding-right: 8px;
}
.two-cols > div:last-child{
  padding-right: 0;
  padding-left: 8px;
}

/* ===================================================== */
/* TABLAS DE FIRMAS                                      */
/* ===================================================== */

.sign-table{
  width:100%;
  border-collapse: collapse;
  margin-top: 8px;
  font-size: 10.5px;
  table-layout: fixed;
}

.sign-table th,
.sign-table td{
  border: 1px solid var(--stroke);
  padding: 8px 10px;
  vertical-align: middle;
}

.sign-table th{
  background: var(--bg-soft);
  font-weight: 900;
  text-align: center;
  white-space: nowrap;
}

/* anchos exactos para 4 columnas */
.sign-table th:nth-child(1),
.sign-table td:nth-child(1){ width: 22%; }

.sign-table th:nth-child(2),
.sign-table td:nth-child(2){ width: 50%; }

.sign-table th:nth-child(3),
.sign-table td:nth-child(3){ width: 14%; }

.sign-table th:nth-child(4),
.sign-table td:nth-child(4){ width: 14%; }

/* nombre alineado como input */
.sign-table td:nth-child(1){
  text-align: left;
  padding-left: 12px;
}

/* firma centrada */
.sign-table td:nth-child(2){
  text-align: center;
}

/* altura tipo vista */
.sign-table td{
  height: 90px;
}

/* imagen firma centrada y estable */
.firma-img,
.sign-table .firma-img{
  display:block;
  margin: 0 auto;
  max-width: 95%;
  max-height: 120px;
  height:auto;
  border: 1px solid var(--stroke);
  border-radius: 10px;
  background:#fff;
}

/* ===================================================== */
/* FOTOS (por si luego las usas)                         */
/* ===================================================== */

.foto-grid{
  display:table;
  width:100%;
  table-layout:fixed;
  border-collapse:separate;
  border-spacing:4px;
}
.foto-cell{
  display:table-cell;
  vertical-align:top;
  text-align:center;
}
.foto-thumb{
  width:110px;
  height:80px;
  object-fit:cover;
  border-radius:8px;
  border:1px solid var(--line);
  margin-bottom:3px;
}
.foto-label{
  font-size:9px;
  color:var(--muted);
}

/* ===================================================== */
/* FOOTER                                                */
/* ===================================================== */

.pdf-footer{
  text-align:right;
  margin-top:4px;
  font-size:9.5px;
  color:var(--muted);
}

/* Evitar cortes feos */
.paper-section,
.sec-block,
.vehicle-table,
.sign-table{
  page-break-inside: avoid;
}


/* ===========================
   DIAGRAMA + INVENTARIO
=========================== */

.diagram-table{
    width:100%;
    border-collapse:collapse;
}

.diagram-cell{
    width:50%;
    vertical-align:top;
    padding:8px;
}

/* ===========================
   DIAGRAMA + PUNTOS
=========================== */

.car-box-pdf{
    position: relative;
    width: 100%;
    max-width: 260px;
    margin: 0 auto;
    height:320px;
}

.car-diagram-img{
    display:block;
    width:100%;
    height:auto;
    max-height: 320px;
}

.car-damage-layer{
    position:absolute;
    top:0;
    left:0;
    width:100%;
    height:100%;
    transform: translate(-14px, -75px);
}

.damage-dot{
    position:absolute;
    width:16px;
    height:16px;
    border-radius:50%;
    box-sizing:border-box;
    background: transparent;
    border: 2px solid #ff4d6a;
}

.damage-dot.selected{
    background:#ff4d6a;
    border-color:#ff4d6a;
    box-shadow: 0 0 8px rgba(255,77,106,.6);
}

/* Posiciones */
.damage-z1  { top:  9.4%; left: 50%; }
.damage-z2  { top: 16.4%; left: 50%; }
.damage-z5  { top: 28.5%; left: 50%; }

.damage-z3  { top: 30.1%; left: 19.4%; }
.damage-z4  { top: 30.1%; left: 80.6%; }

.damage-z6  { top: 41.0%; left: 19.4%; }
.damage-z7  { top: 41.0%; left: 80.6%; }

.damage-z8  { top: 53.5%; left: 19.4%; }
.damage-z9  { top: 53.5%; left: 80.6%; }

.damage-z10 { top: 50.0%; left: 50%; }

.damage-z11 { top: 66.0%; left: 19.4%; }
.damage-z12 { top: 66.0%; left: 80.6%; }

.damage-z13 { top: 78.9%; left: 50%; }

.damage-z15 { top: 35.8%; left: 14.6%; }
.damage-z16 { top: 35.8%; left: 85.3%; }
.damage-z17 { top: 70.9%; left: 14.6%; }
.damage-z18 { top: 70.9%; left: 85.3%; }

/* ===========================
   INVENTARIO
=========================== */

.entrega-title{
    font-weight:900;
    margin-bottom:10px;
    font-size:12px;
    text-align:center;
}

.entrega-table{
    width:100%;
    border-collapse:collapse;
    font-size:10.5px;
}

.entrega-table td{
    padding:6px 8px;
    border-bottom:1px solid #eee;
}

.entrega-table tr:nth-child(even){
    background:#f7f7f7;
}

.entrega-table td:nth-child(2),
.entrega-table td:nth-child(4){
    text-align:right;
    font-weight:bold;
}

/* ===========================
   FOTOS (4 POR HOJA)
=========================== */

.foto-page{
    width:100%;
    border-collapse:collapse;
}

.foto-page td{
    width: 50%;
}

.foto-big-cell{
    padding: 12mm 8mm;
    text-align:center;
    vertical-align:middle;
}

.foto-big{
    width:100%;
    object-fit:contain;
    border-radius:10px;
    border:1px solid #e5e7eb;
}

.foto-big-4{
    max-height: 80mm;
}

.foto-big-cell-4{
    padding: 5mm 6mm;
}

.foto-label-big{
    font-size:10px;
    color:#6b7280;
    margin-top:4px;
}

</style>

</head>

<body>
<body>
@php

 // Tipo de checklist: salida / entrada (USADO EN VARIAS PARTES)
    $tipoChecklistLocal = $tipoChecklist ?? ($tipo ?? 'salida');

 // ============================
    // 2) Inicializar arreglos seguros
    // ============================
    $danosPorZona      = $danosPorZona      ?? null;
    $inventarioCliente = $inventarioCliente ?? null;
    $fotosChecklist    = $fotosChecklist    ?? null;

    if (!is_array($danosPorZona)) {
        $danosPorZona = [];
    }
    if (!is_array($inventarioCliente)) {
        $inventarioCliente = [];
    }
    if (!is_array($fotosChecklist)) {
        $fotosChecklist = [];
    }

    /*
     * ADAPTADOR PARA LOS NUEVOS DATOS DEL CONTROLADOR
     * - $danos        -> $danosPorZona (para el diagrama)
     * - $inventario   -> $inventarioCliente (para la tabla con ✔)
     * - $fotosSalidaPdf / $fotosEntradaPdf -> $fotosChecklist (para fotos grandes)
     */

    // ============================
    // 3) Daños -> zonas para el diagrama
    // ============================
    if (empty($danosPorZona) && !empty($danos ?? null) && is_array($danos)) {
        $zonasTmp = [];
        foreach ($danos as $d) {
            if (isset($d['zona'])) {
                $zonasTmp[] = (int) $d['zona'];
            } elseif (isset($d['zona_id'])) {
                $zonasTmp[] = (int) $d['zona_id'];
            }
        }
        // Quitar nulls, duplicados y reindexar
        $danosPorZona = array_values(
            array_unique(
                array_filter($zonasTmp, function ($z) {
                    return !is_null($z);
                })
            )
        );
    }

    // ============================
    // 4) Inventario -> mapa clave => valor (1 / 0)
    // ============================
    if (empty($inventarioCliente) && !empty($inventario ?? null) && is_array($inventario)) {
        foreach ($inventario as $item) {
            $clave = $item['clave'] ?? null;
            if (!$clave) {
                continue;
            }
            $inventarioCliente[$clave] = (int) ($item['valor'] ?? 0);
        }
    }

    // ============================
    // 5) Fotos -> $fotosChecklist
    // ============================
    if (empty($fotosChecklist)) {
        $fotosChecklist = [
            'frente'         => null,
            'atras'          => null,
            'lado_izquierdo' => null,
            'lado_derecho'   => null,
            'interiores'     => [],
        ];

        // Bloque que viene del controlador según salida / entrada
        $bloqueFotos = $tipoChecklistLocal === 'entrada'
            ? ($fotosEntradaPdf ?? [])
            : ($fotosSalidaPdf ?? []);

        if (!empty($bloqueFotos) && is_array($bloqueFotos)) {
            // Mapeamos categorías de la API a las llaves que usa esta vista
            $mapCategorias = [
                'frente'         => 'frente',
                'atras'          => 'atras',
                'lado_conductor' => 'lado_izquierdo',
                'lado_pasajero'  => 'lado_derecho',
            ];

            foreach ($mapCategorias as $catOrigen => $catDestino) {
                if (!empty($bloqueFotos[$catOrigen])) {
                    $f = $bloqueFotos[$catOrigen];

                    // Puede venir como stdClass o como array
                    $archivo = is_array($f) ? ($f['archivo'] ?? null) : ($f->archivo ?? null);
                    $mime    = is_array($f) ? ($f['mime_type'] ?? 'image/jpeg') : ($f->mime_type ?? 'image/jpeg');

                    if ($archivo) {
                        $fotosChecklist[$catDestino] =
                            'data:' . $mime . ';base64,' . base64_encode($archivo);
                    }
                }
            }

            // Interiores: TODAS las fotos en un arreglo
            if (!empty($bloqueFotos['interiores']) && is_array($bloqueFotos['interiores'])) {
                foreach ($bloqueFotos['interiores'] as $fotoInt) {
                    if (!$fotoInt) {
                        continue;
                    }

                    $archivo = is_array($fotoInt)
                        ? ($fotoInt['archivo'] ?? null)
                        : ($fotoInt->archivo ?? null);

                    $mime = is_array($fotoInt)
                        ? ($fotoInt['mime_type'] ?? 'image/jpeg')
                        : ($fotoInt->mime_type ?? 'image/jpeg');

                    if ($archivo) {
                        $fotosChecklist['interiores'][] =
                            'data:' . $mime . ';base64,' . base64_encode($archivo);
                    }
                }
            }
        }
    }

    // ============================
    // 6) Diagrama del carro en base64 para DomPDF
    // ============================
    $carDiagramPath   = public_path('img/diagrama-carro-danos3.png');
    $carDiagramExists = file_exists($carDiagramPath);
@endphp




  <div class="checklist-container-pdf">

    {{-- ========================= CABECERA ========================= --}}
    <header class="cl-header">
      <div class="cl-logo">
        <img src="{{ public_path('img/Logotipo Fondo.jpg') }}" alt="Viajero">
      </div>

      <div class="cl-title-box">
        <h1>VIAJERO CAR RENTAL</h1>
        <h2>Hoja de Inspección / Check List – Interno</h2>
        <p class="office-info">
          Business Center INNERA Central Park, Armando Birlain Shaffler 2001 Torre 2<br>
          76090 Santiago de Querétaro, Qro. • Centro Sur
        </p>
      </div>

      <div class="cl-ra-box">
        <span>No. Rental Agreement</span>
        <strong>{{ $contrato->numero_contrato ?? $contrato->id_contrato ?? '—' }}</strong>
        <small>Checklist interno para control de flota</small>
      </div>
    </header>

    {{-- ====================== DATOS DEL VEHÍCULO =================== --}}
<section class="paper-section">
  <h3 class="sec-title">Datos del vehículo</h3>

  <table class="vehicle-table">
    <tr>
      <th>CATEGORIA</th>
      <td>{{ $tipoVehiculo ?? '—' }}</td>

      <th>MODELO</th>
      <td>{{ $modelo ?? '—' }}</td>

      <th>PLACAS</th>
      <td>{{ $placas ?? '—' }}</td>

      <th>COLOR</th>
      <td>{{ $color ?? '—' }}</td>

      <th>TRANSMISIÓN</th>
      <td>{{ $transmision ?? '—' }}</td>
    </tr>

    <tr>
      <th>CD. QUE ENTREGA</th>
      <td>{{ $ciudadEntrega ?? '—' }}</td>

      <th>CD. QUE RECIBE</th>
      <td>{{ $ciudadRecibe ?? '—' }}</td>

      <th>KILOMETRAJE SALIDA</th>
      <td>{{ $kmSalida ?? '—' }}</td>

      <th>KILOMETRAJE REGRESO</th>
      <td>{{ $kmRegreso ?? '—' }}</td>

      <th>PROTECCIÓN</th>
      <td>{{ $proteccion ?? '—' }}</td>
    </tr>
  </table>
</section>


    {{-- ====================== NIVELES DE GASOLINA ================== --}}
    <section class="paper-section">
      <h3 class="sec-title">Gasolina – Niveles registrados</h3>

      <table>
        <tr>
          <th>Gasolina salida</th>
          <td>{{ $gasolinaSalida ?? '—' }}</td>
          <th>Gasolina regreso</th>
          <td>{{ $gasolinaRegreso ?? '—' }}</td>
        </tr>
      </table>
    </section>

     {{-- DIAGRAMA + INVENTARIO --}}
    <section class="sec-block">
        <h3 class="sec-title center">Diagrama de daños y equipo entregado</h3>

        <table class="diagram-table">
            <tr>
                                        {{-- DIAGRAMA --}}
            <td class="diagram-cell">
                <div class="car-box-pdf">
                    @if($carDiagramExists)
                        {{-- Imagen del diagrama desde public/img --}}
                        <img src="{{ $carDiagramPath }}"
                             alt="Diagrama de vehículo"
                             class="car-diagram-img">

                        {{-- Capa de puntos encima del diagrama --}}
                        <div class="car-damage-layer">
                            {{-- DEFENSA DELANTERA --}}
                            <div class="damage-dot damage-z1  {{ in_array(1,  $danosPorZona) ? 'selected' : '' }}"></div>
                            <div class="damage-dot damage-z2  {{ in_array(2,  $danosPorZona) ? 'selected' : '' }}"></div>

                            {{-- COFRE / PARABRISAS --}}
                            <div class="damage-dot damage-z5  {{ in_array(5,  $danosPorZona) ? 'selected' : '' }}"></div>

                            {{-- COSTADOS FRONTALES --}}
                            <div class="damage-dot damage-z3  {{ in_array(3,  $danosPorZona) ? 'selected' : '' }}"></div>
                            <div class="damage-dot damage-z4  {{ in_array(4,  $danosPorZona) ? 'selected' : '' }}"></div>

                            {{-- PUERTAS DELANTERAS --}}
                            <div class="damage-dot damage-z6  {{ in_array(6,  $danosPorZona) ? 'selected' : '' }}"></div>
                            <div class="damage-dot damage-z7  {{ in_array(7,  $danosPorZona) ? 'selected' : '' }}"></div>

                            {{-- PUERTAS TRASERAS --}}
                            <div class="damage-dot damage-z8  {{ in_array(8,  $danosPorZona) ? 'selected' : '' }}"></div>
                            <div class="damage-dot damage-z9  {{ in_array(9,  $danosPorZona) ? 'selected' : '' }}"></div>

                            {{-- TECHO --}}
                            <div class="damage-dot damage-z10 {{ in_array(10, $danosPorZona) ? 'selected' : '' }}"></div>

                            {{-- COSTADOS TRASEROS --}}
                            <div class="damage-dot damage-z11 {{ in_array(11, $danosPorZona) ? 'selected' : '' }}"></div>
                            <div class="damage-dot damage-z12 {{ in_array(12, $danosPorZona) ? 'selected' : '' }}"></div>

                            {{-- DEFENSA TRASERA --}}
                            <div class="damage-dot damage-z13 {{ in_array(13, $danosPorZona) ? 'selected' : '' }}"></div>

                            {{-- LLANTAS --}}
                            <div class="damage-dot damage-z15 {{ in_array(15, $danosPorZona) ? 'selected' : '' }}"></div>
                            <div class="damage-dot damage-z16 {{ in_array(16, $danosPorZona) ? 'selected' : '' }}"></div>
                            <div class="damage-dot damage-z17 {{ in_array(17, $danosPorZona) ? 'selected' : '' }}"></div>
                            <div class="damage-dot damage-z18 {{ in_array(18, $danosPorZona) ? 'selected' : '' }}"></div>
                        </div>
                    @else
                        <span style="font-size:9px; color:red;">
                            No se encontró img/diagrama-carro-danos3.png
                        </span>
                    @endif
                </div>
            </td>

                {{-- INVENTARIO --}}
                <td class="diagram-cell">
                    <div class="entrega-title">EL CLIENTE SE LO LLEVA</div>

                    <table class="entrega-table">
                        <tr>
                            <td>PLACAS</td>
                            <td>{{ ($inventarioCliente['placas'] ?? 0) ? '✔' : '—' }}</td>

                            <td>ESPEJOS LATERALES</td>
                            <td>{{ ($inventarioCliente['espejos_laterales'] ?? 0) ? '✔' : '—' }}</td>
                        </tr>

                        <tr>
                            <td>TOLDO-JEEP</td>
                            <td>{{ ($inventarioCliente['toldo'] ?? 0) ? '✔' : '—' }}</td>

                            <td>ESPEJO INTERIOR</td>
                            <td>{{ ($inventarioCliente['espejo_interior'] ?? 0) ? '✔' : '—' }}</td>
                        </tr>

                        <tr>
                            <td>TARJETA DE CIRCULACIÓN</td>
                            <td>{{ ($inventarioCliente['tcirculacion'] ?? 0) ? '✔' : '—' }}</td>

                            <td>ANTENA</td>
                            <td>{{ ($inventarioCliente['antena'] ?? 0) ? '✔' : '—' }}</td>
                        </tr>

                        <tr>
                            <td>PÓLIZA DE SEGURO</td>
                            <td>{{ ($inventarioCliente['poliza'] ?? 0) ? '✔' : '—' }}</td>

                            <td>TAPÓN DE GASOLINA</td>
                            <td>{{ ($inventarioCliente['tapon_gasolina'] ?? 0) ? '✔' : '—' }}</td>
                        </tr>

                        <tr>
                            <td>LLANTA DE REFACCIÓN</td>
                            <td>{{ ($inventarioCliente['refaccion'] ?? 0) ? '✔' : '—' }}</td>

                            <td>TAPETES</td>
                            <td>{{ ($inventarioCliente['tapetes'] ?? 0) ? '✔' : '—' }}</td>
                        </tr>

                        <tr>
                            <td>GATO</td>
                            <td>{{ ($inventarioCliente['gato'] ?? 0) ? '✔' : '—' }}</td>

                            <td>LLAVE DE ENCENDIDO</td>
                            <td>{{ ($inventarioCliente['llave_encendido'] ?? 0) ? '✔' : '—' }}</td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>
    </section>

    {{-- COMENTARIOS Y DAÑOS --}}
  <section class="sec-block">
    <div class="two-cols">
      <div>
        <h3 class="sec-title">Comentario</h3>
        <div class="comment-box">
          {{ $comentario_cliente ?? '—' }}
        </div>
      </div>

      <div>
        <h3 class="sec-title">Daños Interiores</h3>
        <div class="comment-box">
          {{ $danos_interiores ?? '—' }}
        </div>
      </div>
    </div>
  </section>

  {{-- ACEPTACIÓN POSIBLES CARGOS --}}
  <section class="sec-block">
    <p class="legal-text">
      He verificado que el vehículo lleva el equipo especial especificado.
      Que los daños están marcados en imagen de auto y no soy responsable por daños
      o robo parcial o total; salvo una negligencia. Así mismo, he sido informado(a) de los posibles cargos adicionales por incumplimiento
      de las políticas de uso del vehículo.
    </p>

    <ul class="rules-list">
      <li>No se permite fumar dentro de la unidad.</li>
      <li>No se permite manchar interior/exterior con sustancias químicas u orgánicas.</li>
      <li>No se permite el uso de huachicol ni combustibles diferentes a gasolina Premium.</li>
      <li>No se permite el cambio de piezas originales con las que se renta la unidad.</li>
    </ul>

    <div class="accept-line">
      <div class="accept-label">Acepto condiciones y posibles cargos</div>
      <div class="accept-sign">
        @if(!empty($contrato->firma_cliente))
          <img src="{{ $contrato->firma_cliente }}" class="firma-img" alt="Firma cliente acepta condiciones">
        @else
          <span class="xbox">X</span>
        @endif
      </div>
    </div>
  </section>

  {{-- FIRMAS --}}
<section class="sec-block">
  <p class="legal-text">
    Por el presente acuse, recibo este vehículo en las condiciones descritas anteriormente
    y me comprometo a notificar a un representante de Viajero Car Rental de cualquier
    discrepancia antes de salir de los locales de Viajero Car Rental.
  </p>

  @php
    // ✅ Cliente con apellidos (resistente a nulls)
    $clienteNombreCompleto = trim(
      ($reservacion->nombre_cliente ?? '') . ' ' . ($reservacion->apellidos_cliente ?? '')
    );

    if ($clienteNombreCompleto === '') {
      $clienteNombreCompleto = ($nombreCliente ?? '—'); // fallback si ya lo traes armado
    }

    // ✅ Asesor (ya lo mandas desde controller como $asesor)
    $asesorNombre = !empty($asesor) ? $asesor : '—';
  @endphp

  {{-- CLIENTE --}}
  <table class="sign-table">
    <tr>
      <th>Nombre del Cliente</th>
      <th>Firma del Cliente</th>
      <th>Fecha</th>
      <th>Hora</th>
    </tr>
    <tr>
      <td>{{ $clienteNombreCompleto }}</td>
      <td>
        @if(!empty($contrato->firma_cliente))
          <img src="{{ $contrato->firma_cliente }}" class="firma-img" alt="Firma cliente">
        @else
          —
        @endif
      </td>
<td>{{
    ($firma_cliente_fecha ?? $firmaClienteFecha)
    ? \Carbon\Carbon::parse($firma_cliente_fecha ?? $firmaClienteFecha)->translatedFormat('d-M-Y')
    : '—'
}}</td>
<td>{{
    ($firma_cliente_hora ?? $firmaClienteHora)
    ? \Carbon\Carbon::parse($firma_cliente_hora ?? $firmaClienteHora)->format('H:i')
    : '—'
}}</td>
    </tr>
  </table>

  {{-- PERSONAL VIAJERO --}}
  <h3 class="sec-title" style="margin-top:10px;">Sólo personal de Viajero</h3>

  <table class="sign-table">
    <tr>
      <th>Entregó</th>
      <th>Firma</th>
      <th>Fecha</th>
      <th>Hora</th>
    </tr>
    <tr>
      <td>{{ $entrego_nombre ?? ($asesor ?? '—') }}</td>
      <td>
        @if(!empty($contrato->firma_arrendador))
          <img src="{{ $contrato->firma_arrendador }}" class="firma-img" alt="Firma arrendador">
        @else
          —
        @endif
      </td>
<td>{{
      $entrego_fecha
    ? \Carbon\Carbon::parse($entrego_fecha)->translatedFormat('d-M-Y')
    : '—'
}}</td>

<td>{{
    $entrego_hora
    ? \Carbon\Carbon::parse($entrego_hora)->format('H:i')
    : '—'
}}</td>
    </tr>

    <tr>
      <th>Recibió</th>
      <th>Firma</th>
      <th>Fecha</th>
      <th>Hora</th>
    </tr>
    <tr>
      <td>{{ $entrego_nombre ?? ($asesor ?? '—') }}</td>

      <td>
        @if(!empty($contrato->firma_arrendador))
          <img src="{{ $contrato->firma_arrendador }}" class="firma-img" alt="Firma arrendador recibe">
        @else
          —
        @endif
      </td>
<td>{{
    $recibio_fecha
    ? \Carbon\Carbon::parse($recibio_fecha)->translatedFormat('d-M-Y')
    : '—'
}}</td>

<td>{{
    $recibio_hora
    ? \Carbon\Carbon::parse($recibio_hora)->format('H:i')
    : '—'
}}</td>
    </tr>
  </table>
</section>

  {{-- =========================================
         FOTOS – EVIDENCIA FOTOGRÁFICA (4 POR HOJA)
       ========================================= --}}
    @php
        $fotoFrente   = $fotosChecklist['frente']         ?? null;
        $fotoAtras    = $fotosChecklist['atras']          ?? null;
        $fotoIzq      = $fotosChecklist['lado_izquierdo'] ?? null;
        $fotoDer      = $fotosChecklist['lado_derecho']   ?? null;
        $interiores   = $fotosChecklist['interiores']     ?? [];
        $tituloEvid   = 'Evidencia fotográfica – ' . ((($tipo ?? 'salida') === 'entrada') ? 'Regreso' : 'Salida');

        // 🔹 Construimos un arreglo con las fotos EXTERIORES en orden
        $fotosExteriores = [];

        if ($fotoFrente) {
            $fotosExteriores[] = [
                'src'   => $fotoFrente,
                'label' => 'Foto delantera',
            ];
        }

        if ($fotoAtras) {
            $fotosExteriores[] = [
                'src'   => $fotoAtras,
                'label' => 'Foto trasera',
            ];
        }

        if ($fotoIzq) {
            $fotosExteriores[] = [
                'src'   => $fotoIzq,
                'label' => 'Lado izquierdo',
            ];
        }

        if ($fotoDer) {
            $fotosExteriores[] = [
                'src'   => $fotoDer,
                'label' => 'Lado derecho',
            ];
        }
    @endphp

    @if(!empty($fotosExteriores) || !empty($interiores))

        {{-- ======================================
             EXTERIORES – HASTA 4 POR HOJA (2x2)
           ====================================== --}}
        @if(!empty($fotosExteriores))
            @for($i = 0; $i < count($fotosExteriores); $i += 4)
                <div style="page-break-before: always;"></div>
                <section class="sec-block">
                    <h3 class="sec-title center">{{ $tituloEvid }}</h3>

                    <table class="foto-page">
                        {{-- Fila superior: izquierda (i) y derecha (i+1) --}}
                        <tr>
                            {{-- Celda superior izquierda --}}
                            <td class="foto-big-cell foto-big-cell-4">
                                @if(isset($fotosExteriores[$i]))
                                    <img src="{{ $fotosExteriores[$i]['src'] }}"
                                         class="foto-big foto-big-4"
                                         alt="{{ $fotosExteriores[$i]['label'] }}">
                                    <div class="foto-label-big">{{ $fotosExteriores[$i]['label'] }}</div>
                                @endif
                            </td>

                            {{-- Celda superior derecha --}}
                            <td class="foto-big-cell foto-big-cell-4">
                                @if(isset($fotosExteriores[$i + 1]))
                                    <img src="{{ $fotosExteriores[$i + 1]['src'] }}"
                                         class="foto-big foto-big-4"
                                         alt="{{ $fotosExteriores[$i + 1]['label'] }}">
                                    <div class="foto-label-big">{{ $fotosExteriores[$i + 1]['label'] }}</div>
                                @endif
                            </td>
                        </tr>

                        {{-- Fila inferior: izquierda (i+2) y derecha (i+3) si existen --}}
                        @if(isset($fotosExteriores[$i + 2]) || isset($fotosExteriores[$i + 3]))
                            <tr>
                                {{-- Celda inferior izquierda --}}
                                <td class="foto-big-cell foto-big-cell-4">
                                    @if(isset($fotosExteriores[$i + 2]))
                                        <img src="{{ $fotosExteriores[$i + 2]['src'] }}"
                                             class="foto-big foto-big-4"
                                             alt="{{ $fotosExteriores[$i + 2]['label'] }}">
                                        <div class="foto-label-big">{{ $fotosExteriores[$i + 2]['label'] }}</div>
                                    @endif
                                </td>

                                {{-- Celda inferior derecha --}}
                                <td class="foto-big-cell foto-big-cell-4">
                                    @if(isset($fotosExteriores[$i + 3]))
                                        <img src="{{ $fotosExteriores[$i + 3]['src'] }}"
                                             class="foto-big foto-big-4"
                                             alt="{{ $fotosExteriores[$i + 3]['label'] }}">
                                        <div class="foto-label-big">{{ $fotosExteriores[$i + 3]['label'] }}</div>
                                    @endif
                                </td>
                            </tr>
                        @endif
                    </table>
                </section>
            @endfor
        @endif

        {{-- ======================================
             INTERIORES – 4 POR HOJA (2x2)
           ====================================== --}}
        @if(!empty($interiores))
            @for($i = 0; $i < count($interiores); $i += 4)
                <div style="page-break-before: always;"></div>
                <section class="sec-block">
                    <h3 class="sec-title center">{{ $tituloEvid }} – Interior</h3>

                    <table class="foto-page">
                        {{-- Fila superior: izquierda (i) y derecha (i+1) --}}
                        <tr>
                            {{-- Celda superior izquierda --}}
                            <td class="foto-big-cell foto-big-cell-4">
                                @if(isset($interiores[$i]))
                                    <img src="{{ $interiores[$i] }}"
                                         class="foto-big foto-big-4"
                                         alt="Foto de interior {{ $i + 1 }}">
                                    <div class="foto-label-big">Foto de interior {{ $i + 1 }}</div>
                                @endif
                            </td>

                            {{-- Celda superior derecha --}}
                            <td class="foto-big-cell foto-big-cell-4">
                                @if(isset($interiores[$i + 1]))
                                    <img src="{{ $interiores[$i + 1] }}"
                                         class="foto-big foto-big-4"
                                         alt="Foto de interior {{ $i + 2 }}">
                                    <div class="foto-label-big">Foto de interior {{ $i + 2 }}</div>
                                @endif
                            </td>
                        </tr>

                        {{-- Fila inferior: izquierda (i+2) y derecha (i+3) si existen --}}
                        @if(isset($interiores[$i + 2]) || isset($interiores[$i + 3]))
                            <tr>
                                {{-- Celda inferior izquierda --}}
                                <td class="foto-big-cell foto-big-cell-4">
                                    @if(isset($interiores[$i + 2]))
                                        <img src="{{ $interiores[$i + 2] }}"
                                             class="foto-big foto-big-4"
                                             alt="Foto de interior {{ $i + 3 }}">
                                        <div class="foto-label-big">Foto de interior {{ $i + 3 }}</div>
                                    @endif
                                </td>

                                {{-- Celda inferior derecha --}}
                                <td class="foto-big-cell foto-big-cell-4">
                                    @if(isset($interiores[$i + 3]))
                                        <img src="{{ $interiores[$i + 3] }}"
                                             class="foto-big foto-big-4"
                                             alt="Foto de interior {{ $i + 4 }}">
                                        <div class="foto-label-big">Foto de interior {{ $i + 4 }}</div>
                                    @endif
                                </td>
                            </tr>
                        @endif
                    </table>
                </section>
            @endfor
        @endif

    @endif



    <div class="footer-note">
        Documento generado por Viajero Car Rental ·
        {{ \Carbon\Carbon::now()->translatedFormat('d-M-Y H:i') }}
    </div>



  </div>
</body>
</html>
