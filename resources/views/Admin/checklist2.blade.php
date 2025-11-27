@extends('layouts.Ventas')

@section('Titulo', 'Checklist – Entrega y Recepción')

{{-- CSS SOLO VISUAL --}}
@section('css-vistaFacturar')
<link rel="stylesheet" href="{{ asset('css/checklist2.css') }}">
@endsection

@section('contenidoFacturar')

<div class="checklist2-container">

    <!-- ENCABEZADO -->
    <header class="cl2-header">

        <div class="cl2-logo">
            <img src="/img/logo-viajero.png" alt="Viajero Car Rental">
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
            <div class="value">-----</div>

            <div class="label small">Fecha de Cambio</div>
            <div class="value small">--/--/---- --:--</div>
        </div>

    </header>


    <!-- COLUMNAS PRINCIPALES -->
    <section class="cl2-columns">

        <!-- COLUMNA IZQUIERDA -->
        <div class="cl2-col">
            <h3 class="cl2-section-title">AUTO RECIBIDO POR EMPRESA</h3>

            <table class="cl2-table">
                <tr><th>CATEGORIA</th><td>N/A</td></tr>
                <tr><th>TIPO</th><td>N/A</td></tr>
                <tr><th>MODELO</th><td>N/A</td></tr>
                <tr><th>PLACAS</th><td>N/A</td></tr>
                <tr><th>TRANSMISIÓN</th><td>N/A</td></tr>
                <tr><th>Capacidad de Gasolina</th><td>N/A</td></tr>
                <tr><th>FUEL OUT</th><td>N/A</td></tr>
                <tr><th>KILOMETRAJE OUT</th><td>N/A</td></tr>
                <tr><th>FUEL IN</th><td>N/A</td></tr>
                <tr><th>KILOMETRAJE IN</th><td>N/A</td></tr>
            </table>

            <div class="cl2-car-diagram">
                <img src="/img/diagrama-auto.png" alt="Diagrama Auto">
            </div>

            <div class="cl2-sign-box">
                <span>FIRMA</span>
                <div class="line"></div>
                <span class="name">___________________</span>
            </div>
        </div>


        <!-- COLUMNA DERECHA -->
        <div class="cl2-col">
            <h3 class="cl2-section-title">AUTO ENTREGADO A CLIENTE</h3>

            <table class="cl2-table">
                <tr><th>CATEGORIA</th><td>N/A</td></tr>
                <tr><th>SIZE</th><td>N/A</td></tr>
                <tr><th>TIPO</th><td>N/A</td></tr>
                <tr><th>MODELO</th><td>N/A</td></tr>
                <tr><th>PLACAS</th><td>N/A</td></tr>
                <tr><th>COLOR</th><td>N/A</td></tr>
                <tr><th>TRANSMISIÓN</th><td>N/A</td></tr>
                <tr><th>Capacidad del tanque</th><td>N/A</td></tr>
                <tr><th>FUEL OUT</th><td>N/A</td></tr>
                <tr><th>KILOMETRAJE OUT</th><td>N/A</td></tr>
            </table>

            <div class="cl2-car-diagram">
                <img src="/img/diagrama-auto.png" alt="Diagrama Auto">
            </div>

            <div class="cl2-sign-box">
                <span>FIRMA</span>
                <div class="line"></div>
                <span class="name">___________________</span>
            </div>

        </div>

    </section>

</div>

@endsection
