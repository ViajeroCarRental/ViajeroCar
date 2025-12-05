<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">

<style>
/* =========================================
      AJUSTE GLOBAL *TAL CUAL A LA HOJA*
========================================= */
html, body {
    margin: 0;
    padding: 0;

    /* 🔥 Coincidir EXACTAMENTE con el tamaño del PDF */
    width: 1000px !important;
    height: 1500px !important;

    background: #f5f7fb;
    font-family: 'Poppins', Arial, sans-serif;
    color: #333;
}

/* Contenedor general para controlar el layout */
.pdf-wrap {
    width: 100%;
    height: 100%;
    padding: 25px 35px;   /* Ajusta según necesites */
    box-sizing: border-box;
}

/* =========================================
      ENCABEZADO ROJO FULL WIDTH
========================================= */
.encabezado {
    background: #ff1e2d;
    width: 100%;
    padding: 28px 40px;
    border-radius: 15px;
    color: #fff;

    display: flex;
    align-items: center;
}

.logo-contrato {
    width: 130px;
    margin-right: 18px;
}

.encabezado h2 {
    margin: 0;
    font-size: 28px;
    font-weight: 700;
}

.encabezado p {
    margin: 0;
    font-size: 16px;
    opacity: .95;
}

/* =========================================
      SECCIONES
========================================= */
.section {
    width: 100%;
    background: #fff;
    border-radius: 14px;
    margin-top: 18px;
    padding: 18px 22px;
    border-left: 6px solid #E50914;
    box-shadow: 0 3px 10px rgba(0,0,0,.06);
}

.section h3 {
    margin: 0 0 8px 0;
    font-size: 20px;
    color: #E50914;
}

/* =========================================
      TABLAS
========================================= */
.table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 14px;
    table-layout: fixed; /* Evita que se expanda */
}

.table th, .table td {
    border: 1px solid #ddd;
    padding: 10px;
    font-size: 14px;
    word-break: break-word;
}

.table th {
    background: #fce7e7;
    color: #b1060f;
    font-weight: 600;
}

/* =========================================
      FIRMAS
========================================= */
.firma-img {
    width: 200px;
    height: 90px;
    object-fit: contain;
    border: 1px solid #ccc;
    margin-top: 10px;
}

/* =========================================
      TOTALES
========================================= */
.total-final {
    font-size: 22px;
    font-weight: 800;
    color: #b1060f;
    text-align: right;
    margin-top: 15px;
}

/* Contenedor que centra todo el PDF */
.pdf-center {
    width: 1000px;     /* igual al tamaño del PDF */
    height: 1500px;    /* igual al tamaño del PDF */
    margin: 0 auto;    /* 🔥 CENTRADO HORIZONTAL PERFECTO */
    padding: 0;
    background: #f5f7fb;
}

/* Contenedor interno */
.pdf-wrap {
    width: 90%;               /* ajusta cuánto espacio quieres usar del ancho */
    margin: 0 auto;           /* 🔥 centra el contenido dentro del PDF */
    padding: 20px 0;
}


</style>
</head>

<body>
<div class="pdf-center">
    <div class="pdf-wrap">
<!-- ============================
      ENCABEZADO ROJO
============================ -->
<div class="encabezado">
    <div class="logo-titulo">
        <img src="{{ public_path('img/Logo3.jpg') }}" class="logo-contrato" alt="Viajero Car Rental">
        <div class="titulo-texto">
            <h2>VIAJERO CAR RENTAL</h2>
            <p>CONTRATO DE ARRENDAMIENTO / RENTAL AGREEMENT</p>
        </div>
    </div>
</div>

<h2 style="text-align:center; margin-top:-10px; color:#b1060f;">
    Contrato Final de Arrendamiento
</h2>

<!-- ============================
      DATOS CLIENTE
============================ -->
<div class="section">
    <h3>Datos del Arrendatario</h3>
    <ul>
        <li><b>Nombre:</b> {{ $reservacion->nombre_cliente }}</li>
        <li><b>Correo:</b> {{ $reservacion->email_cliente }}</li>
        <li><b>Teléfono:</b> {{ $reservacion->telefono_cliente }}</li>
        <li><b>País:</b> {{ $licencia->pais_emision ?? '—' }}</li>
        <li><b>Vuelo:</b> {{ $reservacion->no_vuelo ?? '—' }}</li>
    </ul>
</div>

<!-- ============================
      LICENCIA
============================ -->
<div class="section">
    <h3>Licencia del cliente</h3>
    <ul>
        <li><b>No. Licencia:</b> {{ $licencia->numero_identificacion ?? '—' }}</li>
        <li><b>Vence:</b> {{ $licencia->fecha_vencimiento ?? '—' }}</li>
        <li><b>Emitida en:</b> {{ $licencia->pais_emision ?? '—' }}</li>
    </ul>
</div>

<!-- ============================
      ITINERARIO
============================ -->
<div class="section">
    <h3>Itinerario</h3>
    <ul>
        <li><b>Oficina de salida:</b> {{ $reservacion->sucursal_retiro_nombre }}</li>
        <li><b>Fecha/Hora salida:</b> {{ $reservacion->fecha_inicio }} {{ $reservacion->hora_retiro }}</li>

        <li><b>Oficina de regreso:</b> {{ $reservacion->sucursal_entrega_nombre }}</li>
        <li><b>Fecha/Hora regreso:</b> {{ $reservacion->fecha_fin }} {{ $reservacion->hora_entrega }}</li>

        <li><b>Días de renta:</b> {{ $dias }}</li>
    </ul>
</div>

<!-- ============================
      VEHÍCULO
============================ -->
<div class="section">
    <h3>Vehículo Asignado</h3>
    <ul>
        <li><b>Modelo:</b> {{ $vehiculo->modelo }}</li>
        <li><b>Categoría:</b> {{ $vehiculo->categoria }}</li>
        <li><b>Color:</b> {{ $vehiculo->color }}</li>
        <li><b>Transmisión:</b> {{ $vehiculo->transmision }}</li>
        <li><b>Kilometraje:</b> {{ number_format($vehiculo->kilometraje) }}</li>
        <li><b>Gasolina inicial:</b> {{ $vehiculo->gasolina_actual }}/16</li>
    </ul>
</div>

<!-- ============================
      TARIFAS Y PROTECCIONES
============================ -->
<div class="section">
    <h3>Tarifas y Protecciones</h3>

<table class="table">
<thead>
<tr>
<th>Concepto</th>
<th>Días</th>
<th>Precio/Día</th>
<th>MXN</th>
</tr>
</thead>
<tbody>

<tr>
<td>Tarifa Base</td>
<td>{{ $dias }}</td>
<td>${{ number_format($tarifaBase,2) }}</td>
<td>${{ number_format($tarifaBase * $dias,2) }}</td>
</tr>

@foreach($paquetes as $p)
<tr>
<td>{{ $p->nombre }}</td>
<td>{{ $dias }}</td>
<td>${{ number_format($p->precio_por_dia,2) }}</td>
<td>${{ number_format($p->precio_por_dia * $dias,2) }}</td>
</tr>
@endforeach

@foreach($individuales as $i)
<tr>
<td>{{ $i->nombre }}</td>
<td>{{ $dias }}</td>
<td>${{ number_format($i->precio_por_dia,2) }}</td>
<td>${{ number_format($i->precio_por_dia * $dias,2) }}</td>
</tr>
@endforeach

@foreach($extras as $e)
<tr>
<td>{{ $e->nombre }}</td>
<td>{{ $dias }}</td>
<td>${{ number_format($e->precio_unitario,2) }}</td>
<td>${{ number_format($e->precio_unitario * $dias,2) }}</td>
</tr>
@endforeach

</tbody>
</table>

<div class="totales">
    <p><b>Subtotal:</b> ${{ number_format($subtotal,2) }}</p>
    <p><b>IVA 16%:</b> ${{ number_format($subtotal * 0.16,2) }}</p>
    <p class="total-final">TOTAL: ${{ number_format($totalFinal,2) }} MXN</p>
</div>

</div>

<!-- ============================
      FIRMAS
============================ -->
<div class="section">
    <h3>Firmas</h3>

    @if($contrato->firma_cliente)
        <p><b>Firma del Cliente:</b></p>
        <img src="{{ $contrato->firma_cliente }}" class="firma-img">
    @endif

    @if($contrato->firma_arrendador)
        <p><b>Firma del Arrendador:</b></p>
        <img src="{{ $contrato->firma_arrendador }}" class="firma-img">
    @endif
</div>
    </div>
</div>

</body>
</html>
