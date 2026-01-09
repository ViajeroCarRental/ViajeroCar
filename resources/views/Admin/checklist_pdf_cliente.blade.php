
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <title>Checklist Cliente - PDF</title>

  <style>
  /* ==========================================================
     ✅ CHECKLIST CLIENTE - PDF (DomPDF Friendly)
  ========================================================== */

  @page{
  size: 230mm 470mm; /* ancho, alto */
  margin: 20mm;
}


  :root{
  --brand:#D6001C;
  --ink:#111827;
  --muted:#6b7280;
  --line:#e5e7eb;
  --stroke:#e5e7eb;   /* ✅ NUEVO: estaba faltando */
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
    font-size: 11px;
  }

  .checklist-pdf-container{
    width:100%;
    margin:0;
    padding:0;
    background:#fff;
  }

  h1,h2,h3,h4,p{
    margin:0;
    padding:0;
  }

 /* ===================================================== */
/* HEADER (DomPDF friendly) — estilo del segundo header  */
/* ===================================================== */

.cl-header{
  width:100%;
  display: table;
  table-layout: fixed;
  background:#fff;          /* ya no rojo */
  color:var(--ink);
  padding: 8px 6px 12px;
  margin-bottom: 10px;
  border-bottom: 3px solid var(--brand);
}

/* columnas */
.cl-logo,
.cl-title-box,
.cl-ra-box{
  display: table-cell;
  vertical-align: middle;
}

.cl-logo{
  width: 170px;
  text-align:left;
}
.cl-logo img{
  width: 150px;
  height:auto;
}

.cl-title-box{
  text-align:center;
  padding: 0 10px;
}
.cl-title-box h1{
  margin:0;
  font-size: 16px;
  font-weight: 900;
  letter-spacing: .6px;
}
.cl-title-box h2{
  margin: 3px 0 6px;
  font-size: 11px;
  font-weight: 700;
  color:#374151;
}

/* OJO: ya tienes .office-info, aquí solo la ajusto al look 2 */
.office-info{
  margin-top: 2px;
  font-size: 9.5px;
  line-height: 1.3;
  color: var(--muted);
}

/* caja derecha */
.cl-ra-box{
  width: 165px;
  text-align:center;
  background: #f7f7f8;
  border: 1px solid var(--stroke);
  border-radius: 10px;
  padding: 8px 6px;
}
.cl-ra-box span{
  display:block;
  font-size: 9.5px;
  color:#4b5563;
}
.cl-ra-box strong{
  display:block;
  margin-top: 4px;
  font-size: 13px;
  font-weight: 900;
}


  .logo-cl{
    width: 54px;
    height:auto;
    border-radius: 10px;
    background: rgba(255,255,255,.14);
    padding: 4px;
    vertical-align: middle;
  }
  .cl-title-text{
    display:inline-block;
    vertical-align: middle;
    margin-left: 10px;
  }
  .cl-title-text h2{
    margin:0;
    font-size: 16px;
    font-weight: 900;
    letter-spacing:.2px;
  }
  .cl-title-text p{
    margin:2px 0 0;
    font-size: 10px;
    opacity:.95;
  }

  .cl-header-center h1{
    font-size: 14px;
    font-weight: 900;
    margin-bottom: 3px;
  }
  .cl-header-center h2{
    font-size: 11px;
    font-weight: 700;
  }

  .cl-header-right span{
    display:block;
  }
  .cl-header-right b{
    display:block;
    font-size: 10.5px;
  }

  .office-info{
  margin-top: 2px;
  font-size: 9.5px;
  line-height: 1.3;
  color: var(--muted);
}


  .paper-section{
  background:#fff;
  border:1px solid var(--line);
  border-radius:14px;
  padding:16px;
  margin-top:14px;
}

/* ✅ NUEVO: en PDF tus secciones usan sec-block, así que le damos look de tarjeta */
.sec-block{
  background:#fff;
  border:1px solid var(--line);
  border-radius:14px;
  padding:16px;
  margin-top:14px;
}



  .sec-title{
    font-size: 12px;
    font-weight: 900;
    margin-bottom: 6px;
    text-transform: uppercase;
  }

  .sec-title.center{
    text-align:center;
  }

 .vehicle-table{
  width:100%;
  border-collapse:collapse;
  font-size:11px;
}
.vehicle-table th,
.vehicle-table td{
  border:1px solid var(--line);
  padding:8px 10px;
  text-align:center;
}
.vehicle-table th{
  background:#f3f4f6;
  font-weight:700;
  white-space:nowrap;
}


  .small-muted{
    font-size: 9px;
    color: var(--muted);
  }

  .comment-box{
  border: 1px solid var(--stroke);
  border-radius: 8px;
  padding: 8px 10px;
  min-height: 70px;              /* ✅ más parecido a la vista */
  font-size: 10.5px;
  line-height: 1.45;
  background:#fff;

  /* ✅ Anti-desborde DomPDF */
  white-space: pre-wrap;
  overflow-wrap: anywhere;
  word-break: break-word;
}


  .legal-text{
  font-size: 9.8px;
  line-height: 1.4;
  text-align: left;              /* ✅ DomPDF + justify suele desbordar */
  margin-bottom: 8px;

  /* ✅ Anti-desborde */
  overflow-wrap: anywhere;
  word-break: break-word;
}


  .accept-line{
    margin-top: 6px;
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
  .firma-img{
  max-width: 220px;     /* ✅ se ve más como tu vista */
  max-height: 90px;
  height:auto;
  border: 1px solid var(--stroke);
  border-radius: 8px;
  background:#fff;
  display:inline-block;
}


  .rules-list{
  margin: 0;
  padding-left: 16px;            /* ✅ más controlado que margin-left */
  font-size: 10.5px;
}
.rules-list li{
  margin-bottom: 4px;
  line-height: 1.35;
  overflow-wrap: anywhere;
  word-break: break-word;
}


  .xbox{
    display:inline-block;
    width: 18px;
    height: 18px;
    border: 1px solid var(--ink);
    text-align:center;
    font-size: 11px;
    line-height: 16px;
    margin-left: 8px;
  }

  /* ===========================
   TABLAS DE FIRMAS (PDF)
   =========================== */

.sign-table{
  width:100%;
  border-collapse: collapse;
  margin-top: 8px;
  font-size: 10.5px;
  table-layout: fixed;            /* ✅ clave para que DomPDF alinee perfecto */
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
  text-align: center;             /* ✅ como tu imagen 2 */
  white-space: nowrap;
}

/* ✅ anchos exactos para 4 columnas */
.sign-table th:nth-child(1),
.sign-table td:nth-child(1){ width: 22%; }

.sign-table th:nth-child(2),
.sign-table td:nth-child(2){ width: 50%; }

.sign-table th:nth-child(3),
.sign-table td:nth-child(3){ width: 14%; }

.sign-table th:nth-child(4),
.sign-table td:nth-child(4){ width: 14%; }

/* ✅ el nombre del cliente/asesor alineado como input */
.sign-table td:nth-child(1){
  text-align: left;
  padding-left: 12px;
}

/* ✅ centrar firma + que no se desmadre */
.sign-table td:nth-child(2){
  text-align: center;
}

/* ✅ altura “tipo vista” */
.sign-table td{
  height: 90px;
}

/* ✅ imagen firma centrada y con tamaño estable */
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


  .diagram-card{
    border: 1px solid var(--stroke);
    border-radius: 10px;
    padding: 8px 8px 6px;
    background:#fff;
    text-align:center;
  }

  .footer-note{
    margin-top: 4px;
    font-size: 9px;
    text-align:center;
    color: var(--muted);
  }

  .two-cols{
  width:100%;
  display: table;
  table-layout: fixed;
}
.two-cols > div{
  display: table-cell;
  width:50%;                     /* ✅ clave: DomPDF respeta mejor */
  vertical-align: top;
  padding-right: 8px;
}
.two-cols > div:last-child{
  padding-right: 0;
  padding-left: 8px;
}


  /* Evitar cortes horribles */
  .sec-block,
  .vehicle-table,
  .sign-table,
  .diagram-card{
    page-break-inside: avoid;
  }
  </style>
</head>

<body>
@php
    // Por si quieres asegurar nombre cliente / asesor
    $nombreCliente = $clienteNombre ?? ($reservacion->nombre_cliente ?? '—');
    $asesor = $asesorNombre ?? '—';
@endphp

<div class="checklist-pdf-container">

 <!-- ============================================ -->
    <!--            ENCABEZADO SUPERIOR               -->
    <!-- ============================================ -->
    <header class="cl-header">
        <div class="cl-logo">
            <<img src="{{ public_path('img/Logotipo Fondo.jpg') }}" alt="Logo Viajero">

        </div>

        <div class="cl-title-box">
            <h1>VIAJERO CAR RENTAL</h1>
            <h2>Hoja de Inspección / Check List</h2>

            <p class="office-info">
                OFICINA<br>
                Business Center INNERA Central Park, Armando Birlain Shaffler 2001 Torre2<br>
                76090 Santiago de Querétaro, Qro.<br>
                Centro Sur
            </p>
        </div>

        <div class="cl-ra-box">
            <span>No. Rental Agreement</span>
            <strong>{{ $contrato->numero_contrato ?? $contrato->id_contrato ?? '' }}</strong>
        </div>
    </header>

 {{-- DATOS DEL VEHÍCULO --}}
<section class="paper-section">
  <h3 class="sec-title">Datos del vehículo</h3>

  <table class="vehicle-table">
    <tr>
      <th>CATEGORIA</th>
      <td>{{ $tipoVehiculo ?? '—' }}</td>

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

      <th>PROTECCIÓN</th>
      <td>{{ $proteccion ?? '—' }}</td>
    </tr>
  </table>
</section>

<br>

  {{-- GASOLINA --}}
  <section class="sec-block">
    <h3 class="sec-title center">Gasolina </h3>

    <table class="vehicle-table">
      <tr>
        <th>Gasolina – Salida</th>
        <td>{{ $gasolinaSalida ?? '—' }}</td>

        <th>Gasolina – Recibido</th>
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


  <div class="footer-note">
    Documento generado por Viajero Car Rental ·
    {{ \Carbon\Carbon::now()->format('d/m/Y H:i') }}
  </div>

</div>
</body>
</html>
