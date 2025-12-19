@extends('layouts.Ventas')
@section('title', 'Contrato Final')
@section('css-vistaContratoFinal')
    <link rel="stylesheet" href="{{ asset('css/ContratoFinal.css') }}">
@endsection

@section('contenidoContratoFinal')
<div class="contrato-final-container"  id="contratoApp" data-id-contrato="{{ $contrato->id_contrato }}">

    <!-- ============================
            BOTONES SUPERIORES
    ============================= -->
    <div class="acciones-contrato">
        <button class="btn btn-pdf">Imprimir / Guardar PDF</button>
        <button id="btnAbrirModalAviso">Enviar correo</button>


        <span class="badge-ra">RA: —</span>
    </div>

    <!-- ============================
             TARJETA DEL CONTRATO
    ============================= -->
    <div class="contrato-card">

        <!-- ENCABEZADO ROJO -->
        <div class="encabezado">
            <div class="logo-titulo">
                <img src="{{ asset('img/Logo3.jpg') }}" class="logo-contrato" alt="Viajero Car Rental">
                <div class="titulo-texto">
                    <h2>VIAJERO CAR RENTAL</h2>
                    <p>CONTRATO DE ARRENDAMIENTO / RENTAL AGREEMENT</p>
                </div>
            </div>

            <div class="encabezado-info">
                <p>Emitido: —</p>
                <p>Tipo de cambio: —</p>
            </div>
        </div>

        <!-- CUERPO PRINCIPAL -->
        <div class="secciones">

            <!-- ============================
                    DATOS DEL ARRENDATARIO
            ============================= -->
            <section class="bloque">
    <h3>Datos de Arrendatario</h3>
    <ul>
        <li><b>Nombre:</b> {{ $reservacion->nombre_cliente ?? '—' }}</li>
        <li><b>Correo:</b> {{ $reservacion->email_cliente ?? '—' }}</li>
        <li><b>Teléfono(s):</b> {{ $reservacion->telefono_cliente ?? '—' }}</li>
        <li><b>País:</b> {{ $licencia->pais_emision ?? '—' }}</li>
        <li><b>Vuelo:</b> {{ $reservacion->no_vuelo ?? '—' }}</li>
        <li><b>Lugar de entrega:</b> {{ $reservacion->sucursal_entrega_nombre ?? '—' }}</li>
    </ul>
</section>


            <!-- ============================
                       LICENCIA
            ============================= -->
            <section class="bloque">
    <h3>Atención / Licencia</h3>
    <ul>
        <li><b>No. Licencia:</b> {{ $licencia->numero_identificacion ?? '—' }}</li>
        <li><b>Vence:</b> {{ $licencia->fecha_vencimiento ?? '—' }}</li>
        <li><b>Estado:</b> {{ $licencia->pais_emision ?? '—' }}</li>
    </ul>
</section>


            <!-- ============================
                    DATOS FISCALES

            <section class="bloque">
                <h3>Datos Fiscales</h3>
                <ul>
                    <li><b>RFC:</b> —</li>
                    <li><b>Razón Social:</b> PÚBLICO EN GENERAL</li>
                    <li><b>Dirección:</b> —</li>
                    <li><b>Estado / País:</b> —</li>
                </ul>
            </section>
            ============================= -->

            <!-- ============================
                     ITINERARIO
            ============================= -->
            <section class="bloque">
    <h3>Itinerario</h3>
    <ul>
        <li><b>Oficina de salida:</b> {{ $reservacion->sucursal_retiro_nombre ?? '—' }}</li>
        <li><b>Fecha/Hora salida:</b>
            {{ $reservacion->fecha_inicio ?? '—' }}
            {{ $reservacion->hora_retiro ?? '—' }}
        </li>

        <li><b>Oficina de regreso:</b> {{ $reservacion->sucursal_entrega_nombre ?? '—' }}</li>
        <li><b>Fecha/Hora regreso:</b>
            {{ $reservacion->fecha_fin ?? '—' }}
            {{ $reservacion->hora_entrega ?? '—' }}
        </li>

        <li><b>Días de renta:</b> {{ $dias }}</li>
    </ul>
</section>


            <!-- ============================
         VEHÍCULO
============================= -->
<section class="bloque bloque-mitad">
    <h3>Vehículo</h3>
    <ul>
        <li><b>Modelo:</b> {{ $vehiculo->modelo ?? '—' }}</li>
        <li><b>Categoría:</b> {{ $vehiculo->categoria ?? '—' }}</li>
        <li><b>Color:</b> {{ $vehiculo->color ?? '—' }}</li>
        <li><b>Transmisión:</b> {{ $vehiculo->transmision ?? '—' }}</li>
        <li><b>Gasolina de salida:</b>
    @if($vehiculo && $vehiculo->gasolina_actual !== null)
        {{ $vehiculo->gasolina_actual }}/16
    @else
        —
    @endif
</li>

        <li><b>Kilómetros:</b>
            {{ $vehiculo->kilometraje !== null ? number_format($vehiculo->kilometraje) : '—' }}
        </li>
    </ul>
</section>


            <!-- ============================
     TARIFAS Y PROTECCIONES
============================ -->
<section class="bloque bloque-mitad">
    <h3>Tarifas y Protecciones</h3>

    <table class="tabla-tarifas">
        <thead>
            <tr>
                <th>Concepto</th>
                <th>Días</th>
                <th>Precio/Día (MXN)</th>
                <th>MXN</th>
                <th>USD</th>
                <th>Aceptó</th>
            </tr>
        </thead>

        <tbody>

    {{-- Tarifa Base --}}
    <tr>
        <td>Tarifa Base</td>
        <td>{{ $dias }}</td>
        <td>${{ number_format($tarifaBase, 2) }}</td>
        <td>${{ number_format($tarifaBase * $dias, 2) }}</td>
        <td>${{ number_format(($tarifaBase * $dias) / 17, 2) }}</td>
        <td>✔</td>
    </tr>

    {{-- Paquetes de seguro --}}
    @foreach ($paquetes as $p)
    <tr>
        <td>{{ $p->nombre }}</td>
        <td>{{ $dias }}</td>
        <td>${{ number_format($p->precio_por_dia, 2) }}</td>
        <td>${{ number_format($p->precio_por_dia * $dias, 2) }}</td>
        <td>${{ number_format(($p->precio_por_dia * $dias) / 17, 2) }}</td>
        <td>✔</td>
    </tr>
    @endforeach

    {{-- Seguros individuales --}}
    @foreach ($individuales as $i)
    <tr>
        <td>{{ $i->nombre }}</td>
        <td>{{ $dias }}</td>
        <td>${{ number_format($i->precio_por_dia, 2) }}</td>
        <td>${{ number_format($i->precio_por_dia * $dias, 2) }}</td>
        <td>${{ number_format(($i->precio_por_dia * $dias) / 17, 2) }}</td>
        <td>✔</td>
    </tr>
    @endforeach

    {{-- Servicios adicionales --}}
    @foreach ($extras as $e)
    <tr>
        <td>{{ $e->nombre }}</td>
        <td>{{ $dias }}</td>
        <td>${{ number_format($e->precio_unitario, 2) }}</td>
        <td>${{ number_format($e->precio_unitario * $dias, 2) }}</td>
        <td>${{ number_format(($e->precio_unitario * $dias) / 17, 2) }}</td>
        <td>✔</td>
    </tr>
    @endforeach

</tbody>

    </table>

    <div class="totales">
        <p><b>SUBTOTAL:</b> ${{ number_format($subtotal, 2) }}</p>
        <p><b>IVA 16%:</b> ${{ number_format($subtotal * 0.16, 2) }}</p>

        <h3><b>TOTAL:</b> ${{ number_format($totalFinal, 2) }}</h3>
    </div>
</section>


            <section class="bloque">
    <h3>Firmas</h3>
    <div class="firmas">

        <!-- Firma del cliente -->
        <div class="firma-item">
            <p><b>FIRMA DE ARRENDATARIO(A):</b></p>

            @if($contrato->firma_cliente)
                <img src="{{ $contrato->firma_cliente }}" class="firma-img">
            @else
                <button id="btnFirmaCliente" class="btn btn-red">Capturar firma cliente</button>
            @endif
        </div>

        <!-- Firma del arrendador -->
        <div class="firma-item">
            <p><b>FIRMA DE ARRENDADOR(A):</b></p>

            @if($contrato->firma_arrendador)
                <img src="{{ $contrato->firma_arrendador }}" class="firma-img">
            @else
                <button id="btnFirmaArrendador" class="btn btn-blue">Capturar firma arrendador</button>
            @endif
        </div>

    </div>
</section>



            <!-- ============================
                   COMBUSTIBLE
            ============================= -->
            <section class="bloque">
                <h3>Combustible</h3>
                <p>
                    GASOLINA: PRECIO POR LITRO FALTANTE
                    <b>$13.16 MXN</b> + servicio
                    <b>$23.96 MXN</b> por litro faltante.
                </p>
            </section>

            <!-- ============================
              NOTAS FINALES / AVISOS
            ============================= -->
            <section class="bloque notas">
                <p>(1) Al firmar este contrato el cliente declara tener conocimiento de todas las condiciones establecidas...</p>
                <p>(2) Los cargos son ESTIMADOS; el importe total aparecerá al cierre del mismo.</p>
                <p>(3) Usted va a alquilar y devolver el vehículo...</p>
                <p>(4) Ninguna protección cubre GPS, llaves, etc.</p>
            </section>

            <!-- PIE DE PÁGINA -->
            <footer class="pie-contrato">
                <p>Business Center INNERA Central Park, Armando Birlain Shaffler...</p>
                <p>reservaciones@viajerocar-rental.com / facturacion@viajerocar-rental.com</p>

                <span class="badge-ra">RA: —</span>
            </footer>

        </div>
    </div>
</div>

<div class="acciones-extra">
    <a href="{{ route('checklist.ver', [
    'id' => $contrato->id_contrato,
    'modo' => 'salida'
]) }}" class="btn-checklist">
    Checklist
</a>
    <a href="{{ route('checklist.cambio-auto') }}" class="btn-checklist">
        Cambio de Vehículo
    </a>

    <a href="#" class="btn-checklist">
        Conductor adicional
    </a>
</div>



<div id="modalCliente" class="modal-firma">
    <div class="modal-body">
        <h3>Firma del Cliente</h3>
        <canvas id="padCliente" width="500" height="200"></canvas>

        <div class="firma-buttons">
            <button id="clearCliente" class="btn btn-gray">Limpiar</button>
            <button id="saveCliente" class="btn btn-red">Guardar firma</button>
        </div>
    </div>
</div>

<div id="modalArrendador" class="modal-firma">
    <div class="modal-body">
        <h3>Firma del Arrendador</h3>
        <canvas id="padArrendador" width="500" height="200"></canvas>

        <div class="firma-buttons">
            <button id="clearArr" class="btn btn-gray">Limpiar</button>
            <button id="saveArr" class="btn btn-blue">Guardar firma</button>
        </div>
    </div>
</div>


<!-- MODAL DE AVISO LEGAL PARA ENVÍO DE CORREO -->
<div id="modalAviso" class="modal-firma" style="display:none;">
    <div class="modal-body" style="max-width:650px;">
        <h3>Aviso de responsabilidad</h3>

        <p style="font-size:15px; margin-bottom:10px;">
            Por favor copie y escriba exactamente el siguiente texto para confirmar que está de acuerdo:
        </p>

        <div style="
            background:#fff3f3;
            padding:12px 15px;
            border-left:4px solid #c00;
            border-radius:8px;
            margin-bottom:18px;
        ">
            <p id="textoOriginal">
                Yo, ('Nombre Completo'), manifiesto estar plenamente consciente de que cualquier daño,
                negligencia o mal uso del vehículo que no esté cubierto por mi paquete de seguro o protecciones
                individuales será responsabilidad mía, y acepto pagar los cargos adicionales que pudieran
                generarse conforme a las políticas de Viajero Car Rental.
            </p>
        </div>

        <textarea id="textoCliente"
            placeholder="Escriba aquí exactamente el mismo texto anterior…"
            style="width:100%; height:140px; padding:12px; border-radius:8px; border:1px solid #ccc;">
        </textarea>

        <div class="firma-buttons" style="margin-top:15px;">
            <button id="cancelarAviso" class="btn btn-gray">Cancelar</button>
            <button id="confirmarAviso" class="btn btn-red">Confirmar y Enviar</button>
        </div>
    </div>
</div>

@endsection

@section('js-vistaContratoFinal')
<!-- Librería oficial SignaturePad -->
    <script src="https://cdn.jsdelivr.net/npm/signature_pad@2.3.2/dist/signature_pad.min.js"></script>
    <script src="{{ asset('js/ContratoFinal.js') }}"></script>
@endsection
