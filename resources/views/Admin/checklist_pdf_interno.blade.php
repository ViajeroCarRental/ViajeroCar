{{-- resources/views/Admin/checklist-interno-pdf.blade.php --}}
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

</style>

</head>

<body>
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
      <td>{{ $firma_cliente_fecha ?? $firmaClienteFecha ?? '—' }}</td>
      <td>{{ $firma_cliente_hora ?? $firmaClienteHora ?? '—' }}</td>
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
      <td>{{ $entrego_fecha ?? '—' }}</td>
      <td>{{ $entrego_hora ?? '—' }}</td>
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
      <td>{{ $recibio_fecha ?? '—' }}</td>
      <td>{{ $recibio_hora ?? '—' }}</td>
    </tr>
  </table>
</section>

  </div>
</body>
</html>
