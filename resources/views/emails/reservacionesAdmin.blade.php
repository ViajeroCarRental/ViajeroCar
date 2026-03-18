<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
  <meta charset="UTF-8">
  <meta name="color-scheme" content="light dark">
  <meta name="supported-color-schemes" content="light dark">

  <style>
    body, table, td, a { -webkit-text-size-adjust:100%; -ms-text-size-adjust:100%; }
    table, td { border-collapse:collapse !important; }
    body { margin:0 !important; padding:0 !important; width:100% !important; }

    body {
      font-family: 'Poppins', Arial, sans-serif;
      background-color: #f4f6fb;
      color: #333333;
    }

    .container {
      max-width: 700px;
      margin: 40px auto;
      background-color: #ffffff;
      border-radius: 16px;
      overflow: hidden;
      box-shadow: 0 8px 25px rgba(0,0,0,0.08);
      border: 1px solid #e2e8f0;
    }

    /* HEADER NUEVO (tipo imagen 1) */
.header{
  background:#b1060f; /* rojo sólido como tu ejemplo */
  padding: 22px 26px;
  color:#fff;
}

/* Usamos tabla para compatibilidad real en clientes de correo */
.header-table{
  width:100%;
  border-collapse:collapse;
}

.brand-logo{
  width:210px; /* más grande como tu ejemplo */
  max-width:210px;
  height:auto;
  display:block;
  border:none;
}

.resv-box{
  text-align:right;
  vertical-align:middle;
  font-weight:700;
  letter-spacing:.5px;
}

.resv-box .label{
  font-size:18px;
  text-transform:uppercase;
  opacity:.95;
  margin:0;
}

.resv-box .code{
  font-size:22px;
  margin:6px 0 0 0;
}

/* Mensaje nuevo (tipo imagen 1) */
.hero{
  padding: 30px 40px 10px;
}

.hero .thanks{
  font-size:22px;
  font-weight:700;
  margin:0 0 14px 0;
}

.hero .lead{
  font-size:16px;
  line-height:1.75;
  margin:0 0 18px 0;
}

.hero a{
  color:#E50914;
  text-decoration:underline;
  font-weight:600;
}


    .content {
      padding: 35px 40px 40px;
      font-size: 15px;
      line-height: 1.7;
    }

    .info-box {
      background: #fff2f2;
      border-left: 5px solid #E50914;
      border-radius: 10px;
      padding: 18px 25px;
      margin: 25px 0;
    }

    .divider {
      height: 1px;
      background: #e2e8f0;
      margin: 35px 0;
    }

    .footer {
      background: #fafafa;
      color: #777;
      font-size: 13px;
      text-align: center;
      padding: 20px 30px;
      border-top: 1px solid #e2e8f0;
    }

    .footer a {
      color: #E50914;
      text-decoration: none;
    }

    /* =========================
   RESUMEN (tipo imagen 2)
========================= */
.summary-title{
  font-size: 22px;
  font-weight: 700;
  margin: 10px 0 14px;
  color:#111;
}

.summary-card{
  border:1px solid #1f2937;
  border-radius: 16px;
  padding: 18px 18px;
}

.summary-top{
  width:100%;
  border-collapse:collapse;
  margin-bottom: 10px;
}

.summary-top .left{
  font-size: 18px;
  font-weight: 700;
}

.summary-top .right{
  text-align:right;
  font-weight:700;
  letter-spacing:.4px;
}

.summary-line{
  height:1px;
  background:#1f2937;
  margin: 12px 0 14px;
}

.section-title{
  font-size:16px;
  font-weight:700;
  margin: 0 0 10px 0;
}

.item{
  width:100%;
  border-collapse:collapse;
  margin: 0 0 10px 0;
}

.item .label{
  font-weight:600;
  color:#111;
  width: 40%;
}

.item .value{
  color:#111;
  text-align:left;
}

.price-table{
  width:100%;
  border-collapse:collapse;
}

.price-table td{
  padding: 2px 0;
}

.price-table .p-label{
  color:#111;
}

.price-table .p-value{
  text-align:right;
  color:#111;
}

.price-total{
  font-weight:800;
  font-size:18px;
  padding-top: 6px !important;
}
/* Texto debajo de Detalles del precio */
.price-note{
  font-size:14px;
  line-height:1.7;
  color:#111;
  margin: 18px 0 10px;
}

/* Línea roja inferior (como en la imagen) */
.price-note-line{
  height:3px;
  background:#b1060f; /* mismo rojo de header */
  margin-top: 10px;
}

/* =========================
   Requisitos y protección LI
========================= */
.info-section{
  font-size:14px;
  line-height:1.7;
  color:#111;
  margin-top: 18px;
}

.info-section-title{
  font-size:15px;
  font-weight:700;
  margin:0 0 6px;
}

.info-section-list{
  margin:0 0 12px;
  padding-left:18px;   /* para que se vean bien las viñetas */
}

.info-section-list li{
  margin:0 0 4px;
}

.info-section-paragraph{
  margin:0 0 16px;
}


/* =========================
   TU AUTO (bloque tipo imagen)
========================= */
.auto-title{
  font-size:22px;
  font-weight:800;
  margin:0;
  color:#111;
}
.auto-subtitle{
  font-size:13px;
  font-weight:700;
  letter-spacing:.6px;
  text-transform:uppercase;
  opacity:.85;
  margin:4px 0 10px;
}
.auto-specs{
  font-size:13px;
  line-height:1.6;
  color:#111;
}
.auto-specs .muted{ opacity:.85; }
.auto-includes{
  font-size:13px;
  margin-top:10px;
  color:#111;
  opacity:.9;
}
/* =========================
   EXTRAS (estilo imagen 1)
========================= */
.extras-row{ width:100%; border-collapse:collapse; }
.extras-hr{ height:1px; background:#111; margin:10px 0 14px; }
.ex-item{ width:33.33%; vertical-align:top; padding:10px 8px; }
.ex-wrap{ width:100%; border-collapse:collapse; }
.ex-check{
  width:18px; height:18px;
  border:2px solid #111; border-radius:3px;
}
.ex-check.on{
  background:#b1060f;  /* rojo */
  border-color:#b1060f;
}
.ex-name{ font-weight:800; font-size:14px; color:#111; }
.ex-price{ font-size:13px; opacity:.85; color:#111; }
.ex-subtitle{ font-size:12px; font-weight:800; letter-spacing:.4px; text-transform:uppercase; opacity:.85; margin:14px 0 8px; }

/* =========================
   FOOTER TIPO LANDING (correo)
========================= */
.site-footer{
  margin-top:40px;
  background:#e5e7eb;         /* gris similar al de la web */
  padding:18px 30px 20px;
  font-size:13px;
  color:#111827;
}

.footer-inner{
  max-width:700px;
  margin:0 auto;
}

/* fila superior: redes + logo palabra */
.footer-top{
  display:flex;
  justify-content:space-between;
  align-items:center;
  margin-bottom:8px;
}

.footer-social{
  font-size:0;                /* para quitar espacios entre imgs */
}

.footer-social a{
  display:inline-block;
  margin-right:14px;
}

.footer-social img{
  max-height:20px;
  display:block;
}

.footer-logo-word{
  font-size:26px;
  font-weight:800;
  letter-spacing:.12em;
}

/* línea divisoria */
.footer-sep{
  height:1px;
  background:#111827;
  margin:8px 0 14px;
}

/* bloque central: ubicaciones + links */
.footer-main{
  display:flex;
  justify-content:space-between;
  gap:24px;
  margin-bottom:14px;
}

.footer-col{
  flex:1;
}

.footer-col p{
  font-size:12px;
  margin:0 0 6px;
}

.footer-col ul{
  list-style:none;
  padding:0;
  margin:0;
}

.footer-col li{
  margin-bottom:4px;
}

.footer-col a{
  color:#111827;
  text-decoration:none;
  font-size:12px;
  text-transform:uppercase;
}

.footer-col a:hover{
  text-decoration:underline;
}

/* fila pagos */
.footer-pay{
  border-top:1px solid #111827;
  padding-top:10px;
}

.footer-pay img{
  max-height:22px;
  display:inline-block;
  margin-right:10px;
}
/* Logo del header en correos */
.header-logo{
  width: 260px;
  max-width: 260px;
  height: auto;
  display: block;
  border: none;
}

.footer-logo-word img{
  height: 28px;
  max-height: 28px;
  width: auto;
  display: block;
}

  </style>
</head>

<body>

<div class="container">

    <!-- HEADER NUEVO -->
  <div class="header">
    <table class="header-table" role="presentation">
      <tr>
        <td style="vertical-align:middle;">
          <img src="{{ rtrim(config('app.url'), '/') . '/img/Logo3.jpg' }}" alt="Viajero Car Rental">

        </td>

        <td class="resv-box">
          <p class="label">{{ __('messages.reservacion') }}</p>
          <p class="code">{{ $reservacion->codigo }}</p>
        </td>
      </tr>
    </table>
  </div>


  <!-- CONTENT -->
  <div class="content">

        <!-- MENSAJE NUEVO (tipo imagen 1) -->
    <div class="hero">
      <p class="thanks">
        {{ __('messages.gracias') }} <strong>{{ strtoupper(trim(($reservacion->nombre_cliente ?? '') . ' ' . ($reservacion->apellidos_cliente ?? ''))) }}</strong>
      </p>

      <p class="lead">
        {!! __('messages.vehiculo_reservado', ['url' => $url_detalle ?? '#']) !!}
      </p>

      <p class="lead" style="margin-top:0;">
        {{ __('messages.info_calculada') }}
      </p>
    </div>


        <!-- RESUMEN NUEVO (tipo imagen 2) -->
    <h2 class="summary-title">{{ __('messages.resumen_reserva') }}</h2>

    <div class="summary-card">

      <!-- Encabezado interno -->
      <table class="summary-top" role="presentation">
        <tr>
          <td class="left">{{ __('messages.lugar_y_fecha') }}</td>
          <td class="right">
            {{ __('messages.reservacion') }}<br>
            {{ $reservacion->codigo }}
          </td>
        </tr>
      </table>

      <div class="summary-line"></div>

      <!-- Lugar y fecha -->
      <table class="item" role="presentation">
        <tr>
          <td class="label">{{ __('messages.entrega') }}</td>
          <td class="value">
  <div>{{ $reservacion->fecha_inicio }} {{ $reservacion->hora_retiro ?? '' }}</div>
  <div style="font-size:13px; opacity:.85;">{{ $lugarRetiro ?? '-' }}</div>
</td>

        </tr>
        <tr>
          <td class="label">{{ __('messages.devolucion') }}</td>
          <td class="value">
  <div>{{ $reservacion->fecha_fin }} {{ $reservacion->hora_entrega ?? '' }}</div>
  <div style="font-size:13px; opacity:.85;">{{ $lugarEntrega ?? '-' }}</div>
</td>

        </tr>
      </table>

      <div class="summary-line"></div>

     <!-- Tu Auto -->
<p class="section-title">{{ __('messages.tu_auto') }}</p>

<table role="presentation" style="width:100%; border-collapse:collapse;">
  <tr>
    <!-- Imagen -->
    <td style="width:45%; vertical-align:middle; padding:10px 0;">
      <img
        src="{{ $imgCategoria ?? (rtrim(config('app.url'), '/') . '/img/categorias/placeholder.png') }}"
        alt="{{ __('messages.tu_auto') }}"
        style="width:100%; max-width:260px; height:auto; display:block; border:none;"
      >
    </td>

    <!-- Texto -->
    <td style="width:55%; vertical-align:middle; padding:10px 0 10px 10px;">
      <p class="auto-title">
        {{ $tuAuto['titulo'] ?? ($categoria->descripcion ?? '-') }}
      </p>

      <p class="auto-subtitle">
        {{ $tuAuto['subtitulo'] ?? __('messages.categoria') . ' ' . ($categoria->codigo ?? '-') }}
      </p>

      <div class="auto-specs">
        <div><strong>{{ $tuAuto['pax'] ?? 5 }}</strong> {{ __('messages.pasajeros') }}</div>
        <div><strong>{{ $tuAuto['small'] ?? 2 }}</strong> {{ __('messages.maletas_chicas') }}</div>
        <div><strong>{{ $tuAuto['big'] ?? 1 }}</strong> {{ __('messages.maletas_grandes') }}</div>
        <div class="muted">{{ $tuAuto['transmision'] ?? __('messages.transmision') }}</div>
        <div class="muted">{{ $tuAuto['tech'] ?? 'Apple CarPlay | Android Auto' }}</div>
      </div>

      <div class="auto-includes">
        {{ $tuAuto['incluye'] ?? __('messages.incluye') }}
      </div>
    </td>
  </tr>
</table>



      <div class="summary-line"></div>

     <p class="section-title">{{ __('messages.extras') }}</p>
<div class="extras-hr" style="height:1px; background:#111; margin:10px 0 14px;"></div>

@php
  // 1) Servicios (extras) primero
  $servCards = collect();

  if (!empty($extrasReserva) && count($extrasReserva) > 0) {
    foreach ($extrasReserva as $ex) {
      $servCards->push([
        'nombre' => $ex->nombre ?? 'Servicio',
        'precio' => isset($ex->precio_unitario)
          ? ('$' . number_format((float)$ex->precio_unitario, 2) . ' ' . __('messages.c_u'))
          : '',
        'tipo'   => 'servicio',
      ]);
    }
  }

  // 2) Seguro abajo (pero en el mismo apartado Extras)
  $segCard = null;
  if (!empty($seguroReserva)) {
    $segCard = [
      'nombre' => $seguroReserva->nombre ?? __('messages.seguro'),
      'precio' => isset($seguroReserva->precio_por_dia)
        ? ('$' . number_format((float)$seguroReserva->precio_por_dia, 2) . ' ' . __('messages.por_dia'))
        : '',
      'tipo'   => 'seguro',
    ];
  }

  // helper: render en filas de 3
  $servChunks = $servCards->chunk(3);
@endphp

{{-- =========================
     SERVICIOS (EXTRAS)
========================= --}}
@if($servCards->count() === 0)
  <div style="font-size:14px; opacity:.85;">{{ __('messages.no_seleccionados') }}</div>
@else
  <table role="presentation" style="width:100%; border-collapse:collapse;">
    @foreach($servChunks as $row)
      <tr>
        @foreach($row as $c)
          <td style="width:33.33%; padding:10px 8px; vertical-align:top;">
            <table role="presentation" style="width:100%; border-collapse:collapse;">
              <tr>
                {{-- Cuadro "seleccionado" (rojo) --}}
                <td style="width:26px; vertical-align:top; padding-top:2px;">
                  <div style="width:18px; height:18px; background:#b1060f; border:2px solid #b1060f; border-radius:3px;"></div>
                </td>

                {{-- Texto --}}
                <td style="vertical-align:top;">
                  <div style="font-weight:800; font-size:14px; color:#111; line-height:1.2;">
                    {{ $c['nombre'] }}
                  </div>
                  @if(!empty($c['precio']))
                    <div style="font-size:13px; opacity:.85; color:#111; margin-top:2px;">
                      {{ $c['precio'] }}
                    </div>
                  @endif
                </td>
              </tr>
            </table>
          </td>
        @endforeach

        {{-- Relleno si faltan columnas --}}
        @for($i = $row->count(); $i < 3; $i++)
          <td style="width:33.33%; padding:10px 8px;"></td>
        @endfor
      </tr>
    @endforeach
  </table>
@endif

{{-- =========================
     SEGURO ABAJO (MISMO APARTADO)
========================= --}}
@if($segCard)
  <div style="margin-top:10px;"></div>
  <div style="font-size:12px; font-weight:800; letter-spacing:.4px; text-transform:uppercase; opacity:.85; margin:14px 0 8px;">
    {{ __('messages.paquete_seguro') }}
  </div>

  <table role="presentation" style="width:100%; border-collapse:collapse;">
    <tr>
      <td style="width:33.33%; padding:10px 8px; vertical-align:top;">
        <table role="presentation" style="width:100%; border-collapse:collapse;">
          <tr>
            {{-- Cuadro "seleccionado" (rojo) --}}
            <td style="width:26px; vertical-align:top; padding-top:2px;">
              <div style="width:18px; height:18px; background:#b1060f; border:2px solid #b1060f; border-radius:3px;"></div>
            </td>

            {{-- Texto --}}
            <td style="vertical-align:top;">
              <div style="font-weight:800; font-size:14px; color:#111; line-height:1.2;">
                {{ $segCard['nombre'] }}
              </div>
              @if(!empty($segCard['precio']))
                <div style="font-size:13px; opacity:.85; color:#111; margin-top:2px;">
                  {{ $segCard['precio'] }}
                </div>
              @endif
            </td>
          </tr>
        </table>
      </td>

      {{-- Deja el resto vacío para que no se vea “raro” --}}
      <td style="width:33.33%; padding:10px 8px;"></td>
      <td style="width:33.33%; padding:10px 8px;"></td>
    </tr>
  </table>
@endif

      <div class="summary-line"></div>

      @php
    $fechaInicio = \Carbon\Carbon::parse($reservacion->fecha_inicio);
    $fechaFin    = \Carbon\Carbon::parse($reservacion->fecha_fin);
    $diasCorreo  = max(1, $fechaInicio->diffInDays($fechaFin));

    $tarifaBaseDia   = (float) ($reservacion->tarifa_base ?? 0);
    $tarifaBaseTotal = round($tarifaBaseDia * $diasCorreo, 2);
@endphp

     <p class="section-title">{{ __('messages.detalles_precio') }}</p>
<table class="price-table" role="presentation">
  <tr>
    <td class="p-label">{{ __('messages.tarifa_base') }}</td>
    <td class="p-value">
      ${{ number_format($tarifaBaseTotal, 2) }} MXN
    </td>
  </tr>
  <tr>
    <td class="p-label">{{ __('messages.opciones_renta') }}</td>
    <td class="p-value">
      ${{ number_format((float)($opcionesRentaTotal ?? 0), 2) }} MXN
    </td>
  </tr>
  <tr>
    <td class="p-label">{{ __('messages.cargos_e_iva') }}</td>
    <td class="p-value">
      ${{ number_format($reservacion->impuestos, 2) }} MXN
    </td>
  </tr>
  <tr>
      <tr>
    <td class="p-label price-total">{{ __('messages.total') }}</td>
    <td class="p-value price-total">
      ${{ number_format($reservacion->total, 2) }} MXN
    </td>
  </tr>
</table>



<div class="divider"></div>

{{-- 🔻 Texto y línea roja debajo del detalle de precio --}}
<p class="price-note">
  {{ __('messages.nota_garantia') }}
</p>
<div class="price-note-line"></div>

{{-- Bloque: Requisitos y protección LI --}}
<div class="info-section">
  <p class="info-section-title">{{ __('messages.requisitos_rentar') }}</p>

  <ul class="info-section-list">
    <li>{{ __('messages.requisito1') }}</li>
    <li>{{ __('messages.requisito2') }}</li>
    <li>{{ __('messages.requisito3') }}</li>
    <li>{{ __('messages.requisito4') }}</li>
    <li>{{ __('messages.requisito5') }}</li>
  </ul>

  <p class="info-section-paragraph">
    {{ __('messages.requisitos_variar') }}
  </p>

  <p class="info-section-title">{{ __('messages.proteccion_li') }}</p>

  <p class="info-section-paragraph">
    {{ __('messages.proteccion_li_desc') }}
  </p>
</div>

</div> {{-- <-- aquí sí cierras .summary-card --}}

  </div>
   {{-- FOOTER GRANDE TIPO LANDING --}}
  <div class="site-footer">
    <div class="footer-inner">

      {{-- Fila superior: redes + logo de palabra --}}
      <div class="footer-top">
        <div class="footer-social">
          {{-- Aquí pon las rutas reales de tus redes o déjalas en "#" y luego las cambias --}}
          <a href="https://wa.me/524423032668">
            <img src="{{ asset('img/email/whatsapp-black.png') }}" alt="WhatsApp">
          </a>
          <a href="https://www.facebook.com/viajerocarental">
            <img src="{{ asset('img/email/facebook-black.png') }}" alt="Facebook">
          </a>
          <a href="https://www.instagram.com/viajerocarental">
            <img src="{{ asset('img/email/instagram-black.png') }}" alt="Instagram">
          </a>
          <a href="https://www.tiktok.com/@viajerocarental">
            <img src="{{ asset('img/email/tiktok-black.png') }}" alt="TikTok">
          </a>
        </div>

        <div class="footer-logo-word">
          <img src="{{ asset('img/Logo3.jpg') }}" alt="Viajero Car Rental">
        </div>
      </div>

      <div class="footer-sep"></div>

      {{-- Fila central: ubicaciones + links --}}
      <div class="footer-main">
        {{-- Columna 1: ubicaciones --}}
        <div class="footer-col">
          <p>📍 {{ __('messages.oficina_central') }}</p>
          <p>📍 {{ __('messages.pickup_aeropuerto_qro') }}</p>
          <p>📍 {{ __('messages.pickup_aeropuerto_leon') }}</p>
        </div>

        {{-- Columna 2: links centro --}}
        <div class="footer-col">
          <ul>
            <li><a href="{{ route('rutaReservaciones') }}">{{ __('messages.mi_reserva') }}</a></li>
            <li><a href="{{ route('rutaCatalogo') }}">{{ __('messages.autos') }}</a></li>
            <li><a href="https://viajerocarental.com/empresas">{{ __('messages.empresas') }}</a></li>
            <li><a href="{{ route('rutaPoliticas') }}">{{ __('messages.terminos_condiciones') }}</a></li>
            <li><a href="{{ route('rutaContacto') }}">{{ __('messages.contacto') }}</a></li>
          </ul>
        </div>

        {{-- Columna 3: links derecha --}}
        <div class="footer-col">
          <ul>
            <li><a href="https://viajerocarental.com/blog">{{ __('messages.blog') }}</a></li>
            <li><a href="{{ route('rutaFAQ') }}">{{ __('messages.faq') }}</a></li>
            <li><a href="{{ route('rutaPoliticas') }}">{{ __('messages.aviso_privacidad') }}</a></li>
            <li><a href="{{ route('rutaPoliticas') }}">{{ __('messages.politica_limpieza') }}</a></li>
            <li><a href="{{ route('rutaPoliticas') }}">{{ __('messages.politica_renta') }}</a></li>
          </ul>
        </div>
      </div>

      {{-- Fila inferior: métodos de pago --}}
      <div class="footer-pay">
        <img src="{{ asset('img/visa.jpg') }}" alt="Visa">
        <img src="{{ asset('img/mastercard.png') }}" alt="Mastercard">
        <img src="{{ asset('img/america.png') }}" alt="American Express">
        <img src="{{ asset('img/oxxo.png') }}" alt="OXXO">
        <img src="{{ asset('img/pago.png') }}" alt="Mercado Pago">
        <img src="{{ asset('img/paypal.png') }}" alt="PayPal">
      </div>

    </div>
  </div>


  <!-- FOOTER SIMPLE EXISTENTE -->
  <div class="footer">
    © {{ date('Y') }} <strong>Viajero Car Rental</strong> — {{ __('messages.todos_derechos') }}<br>
    <a href="https://viajerocarental.com">www.viajerocarental.com</a> |
    <a href="mailto:reservaciones@viajerocarental.com">reservaciones@viajerocarental.com</a>
  </div>

</div>

</body>
</html>
