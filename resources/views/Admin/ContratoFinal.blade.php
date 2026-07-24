@extends('layouts.Ventas')
@section('title', 'Contrato Final')
@section('css-vistaContratoFinal')
    <link rel="stylesheet" href="{{ asset('css/ContratoFinal.css') }}">
@endsection

@section('contenidoContratoFinal')
    @php
        $seccionesObligatorias = ['contrato', 'clausulas', 'checklist'];

        if ($tieneConductorAdicional) {
            $seccionesObligatorias[] = 'conductor_adicional';
        }

        $seccionesRevisadas = collect($seccionesObligatorias)
            ->filter(fn ($seccion) => !empty($revisionesContrato[$seccion]))
            ->count();

        $revisionCompleta = $seccionesRevisadas === count($seccionesObligatorias);
    @endphp

    <div class="contrato-final-container" id="contratoApp" data-id-contrato="{{ $contrato->id_contrato }}">

        <!-- ============================
                            BOTONES SUPERIORES
                    ============================= -->
        <div class="acciones-contrato">
            <button type="button" class="btn btn-pdf">Imprimir / Guardar PDF</button>

            <div class="revision-resumen">
                <span id="contadorRevisiones">
                    {{ $seccionesRevisadas }} de {{ count($seccionesObligatorias) }} documentos revisados
                </span>
            </div>

            <button
                type="button"
                id="btnAbrirModalAviso"
                class="btn-enviar-contrato"
                {{ $revisionCompleta ? '' : 'disabled' }}
            >
                <i class="fa-solid fa-envelope"></i>
                Enviar correo
            </button>

            <span class="badge-ra">
                RA: {{ $contrato->numero_contrato ?? $contrato->id_contrato ?? '—' }}
            </span>
        </div>

        <div class="documentos-acordeon">
            <section
                class="documento-acordeon abierto {{ !empty($revisionesContrato['contrato']) ? 'revisado' : '' }}"
                data-seccion="contrato"
                data-revisado="{{ !empty($revisionesContrato['contrato']) ? '1' : '0' }}"
            >
                <button type="button" class="documento-encabezado" aria-expanded="true">
                    <span class="documento-numero">1</span>
                    <span class="documento-titulo">
                        <strong>Contrato de arrendamiento</strong>
                        <small>{{ !empty($revisionesContrato['contrato']) ? 'Revisado' : 'Pendiente de revisión' }}</small>
                    </span>
                    <span class="documento-estado">
                        <i class="fa-solid {{ !empty($revisionesContrato['contrato']) ? 'fa-circle-check' : 'fa-circle' }}"></i>
                    </span>
                    <i class="fa-solid fa-chevron-down documento-flecha"></i>
                </button>

                <div class="documento-contenido">

        <!-- ============================
                            TARJETA DEL CONTRATO
                    ============================= -->
        <div class="contrato-card">

            <!-- ==========================================
            ENCABEZADO - BLANCO con Logo + Datos con Imagen de Fondo
        ========================================== -->
            <div class="encabezado-blanco">
                <div class="logo-titulo-blanco">
                    <img src="{{ asset('img/LogoB.png') }}" class="logo-contrato" alt="Viajero Car Rental">
                </div>

                <div class="encabezado-datos-container">
                    <img src="{{ asset('img/A.png') }}" class="logo-fondo-derecho" alt="Logo fondo">
                    <div class="encabezado-datos">
                        <p><strong>No. Rental Agreement:</strong> <span
                                class="burbuja-roja">{{ $contrato->id_contrato ?? '—' }}</span></p>
                        <p><strong>Fecha de apertura:</strong> <span
                                class="burbuja-roja">{{ now()->translatedFormat('d/M/Y H:i') }}</span></p>
                        <p><strong>Reservación:</strong> <span
                                class="burbuja-roja">{{ $reservacion->id_reservacion ?? '—' }}</span></p>
                    </div>
                </div>
            </div>

            <!-- ==========================================
            TARJETA BLANCA - GRACIAS (ALINEADA A LA IZQUIERDA)
        ========================================== -->
            <div class="tarjeta-blanca-izquierda">
                <p class="gracias">
                    Gracias por tu reserva,
                    <strong>{{ trim(($reservacion->nombre_cliente ?? '') . ' ' . ($reservacion->apellidos_cliente ?? '')) ?: 'Cliente' }}</strong>
                </p>
                <p class="frase">Disfruta el camino tanto como tu destino.</p>
            </div>

            <!-- ==========================================
                            CUERPO PRINCIPAL
                    ========================================== -->
            <div class="secciones">
                <!-- ==========================================
                                VEHÍCULO - Una columna (Título ROJO)
                        ========================================== -->
                <div class="row-full">
                    <h3 class="titulo-seccion" style="color: #d32f2f;">INFORMACIÓN DE TU VEHÍCULO</h3>
                    <div class="bloque-vehiculo">
                        <div class="vehiculo-grid">
                            <div class="vehiculo-item"><span class="label">Modelo:</span><span
                                    class="value">{{ $vehiculo->modelo ?? '—' }}</span></div>
                            <div class="vehiculo-item"><span class="label">Categoría:</span><span
                                    class="value">{{ $vehiculo->categoria ?? '—' }}</span></div>
                            <div class="vehiculo-item"><span class="label">Color:</span><span
                                    class="value">{{ $vehiculo->color ?? '—' }}</span></div>
                            <div class="vehiculo-item"><span class="label">Placas:</span><span
                                    class="value">{{ $vehiculo->placa ?? '—' }}</span></div>
                            <div class="vehiculo-item"><span class="label">Transmisión:</span><span
                                    class="value">{{ $vehiculo->transmision ?? '—' }}</span></div>
                            <div class="vehiculo-item"><span class="label">Kilometraje:</span><span
                                    class="value">{{ number_format($vehiculo->kilometraje ?? 0) }}</span></div>
                        </div>

                        <div class="gasolina-row">
                            <div class="vehiculo-item" style="flex-direction: row; gap: 8px;">
                                <span><i class="fa-solid fa-gas-pump"></i></span>
                                <span class="label" style="margin:0;">Capacidad del tanque:</span>
                                <span class="value">{{ $vehiculo->capacidad_tanque ?? '—' }} LITROS</span>
                            </div>
                            <div class="vehiculo-item" style="flex-direction: row; gap: 8px;">
                                <span class="label" style="margin:0;">Gasolina de salida:</span>
                                <span class="value">{{ $vehiculo->gasolina_actual ?? '—' }} LITROS</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- ==========================================
                    ARRENDATARIO + ITINERARIO - Dos columnas (Títulos ROJOS)
                        ========================================== -->
                <div class="row-dos-columnas">

                    <!-- Columna Izquierda: ARRENDATARIO -->
                    <div class="col">
                        <h3 class="titulo-seccion">ARRENDATARIO</h3>
                        <div class="bloque-arrendatario">
                            <div class="arrendatario-item">
                                <span class="label">Nombre:</span>
                                <span class="value">
                                    {{ trim(($reservacion->nombre_cliente ?? '') . ' ' . ($reservacion->apellidos_cliente ?? '')) ?: '—' }}
                                </span>
                            </div>
                            <div class="arrendatario-item">
                                <span class="label">Fecha de nacimiento (DOB):</span>
                                <span class="value">{{ $fechaNacimiento ?? '—' }}</span>
                            </div>
                            <div class="arrendatario-item">
                                <span class="label">Edad:</span>
                                <span class="value">{{ $edad ? $edad . ' años' : '—' }}</span>
                            </div>
                            <div class="arrendatario-item">
                                <span class="label">Teléfono:</span>
                                <span class="value">{{ $reservacion->telefono_cliente ?? '—' }}</span>
                            </div>
                            <div class="arrendatario-item">
                                <span class="label">Correo:</span>
                                <span class="value">{{ $reservacion->email_cliente ?? '—' }}</span>
                            </div>
                            <div class="arrendatario-item">
                                <span class="label">Dirección:</span>
                                <span class="value">{{ $reservacion->direccion_cliente ?? '—' }}</span>
                            </div>

                            <!-- Tabla de licencia -->
                            <table class="licencia-table">
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
                                        <td>{{ $licencia->estado ?? '—' }}</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Columna Derecha: ITINERARIO -->
                    <div class="col">
                        <h3 class="titulo-seccion">ITINERARIO</h3>
                        <div class="bloque-itinerario">
                            <div class="itinerario-item">
                                <span class="label">Check in:</span>
                                <div class="value">
                                    <div class="info-line">
                                        <i class="fa-solid fa-location-dot"></i>
                                        {{ $reservacion->sucursal_retiro_nombre ?? '—' }}
                                    </div>
                                    <div class="info-line">
                                        <i class="fa-regular fa-calendar"></i>
                                        {{ $reservacion->fecha_inicio ? \Carbon\Carbon::parse($reservacion->fecha_inicio)->translatedFormat('d/M/Y') : '—' }}
                                    </div>
                                    <div class="info-line">
                                        <i class="fa-regular fa-clock"></i>
                                        {{ $reservacion->hora_retiro ? \Carbon\Carbon::parse($reservacion->hora_retiro)->format('H:i') : '—' }}
                                        HRS
                                    </div>
                                </div>
                            </div>
                            <div class="itinerario-item">
                                <span class="label">Check out:</span>
                                <div class="value">
                                    <div class="info-line">
                                        <i class="fa-solid fa-location-dot"></i>
                                        {{ $reservacion->sucursal_entrega_nombre ?? '—' }}
                                    </div>
                                    <div class="info-line">
                                        <i class="fa-regular fa-calendar"></i>
                                        {{ $reservacion->fecha_fin ? \Carbon\Carbon::parse($reservacion->fecha_fin)->translatedFormat('d/M/Y') : '—' }}
                                    </div>
                                    <div class="info-line">
                                        <i class="fa-regular fa-clock"></i>
                                        {{ $reservacion->hora_entrega ? \Carbon\Carbon::parse($reservacion->hora_entrega)->format('H:i') : '—' }}
                                        HRS
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>

                <!-- ==========================================
                    TARIFAS + ADICIONALES - Dos columnas (Títulos ROJOS)
                        ========================================== -->
                <div class="row-dos-columnas">

                    <!-- Columna Izquierda: TARIFAS -->
                    <div class="col">
                        <h3 class="titulo-seccion">TARIFAS</h3>
                        <div class="bloque-tarifas">
                            <table class="tarifas-table">
                                <thead>
                                    <tr>
                                        <th>Concepto</th>
                                        <th>Días</th>
                                        <th>Precio por día</th>
                                        <th>Total</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td>Tarifa base</td>
                                        <td>{{ $dias }}</td>
                                        <td>$ {{ number_format($tarifaBase, 2) }}</td>
                                        <td>$ {{ number_format($tarifaBase * $dias, 2) }}</td>
                                    </tr>

                                    {{-- Paquetes de seguro --}}
                                    @foreach ($paquetes as $p)
                                        <tr>
                                            <td>{{ $p->nombre }}</td>
                                            <td>{{ $dias }}</td>
                                            <td>$ {{ number_format($p->precio_por_dia, 2) }}</td>
                                            <td>$ {{ number_format($p->precio_por_dia * $dias, 2) }}</td>
                                        </tr>
                                    @endforeach

                                    {{-- Seguros individuales --}}
                                    @foreach ($individuales as $i)
                                        <tr>
                                            <td>{{ $i->nombre }}</td>
                                            <td>{{ $dias }}</td>
                                            <td>$ {{ number_format($i->precio_por_dia, 2) }}</td>
                                            <td>$ {{ number_format($i->precio_por_dia * $dias, 2) }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>

                            <div class="totales">
                                <p><strong>Subtotal:</strong> $ {{ number_format($subtotal, 2) }}</p>
                                <p><strong>IVA.</strong> $ {{ number_format($subtotal * 0.16, 2) }}</p>
                                <p><strong>Cuotas locales e impuestos federales</strong> $
                                    {{ number_format($subtotal * 0.16, 2) }}</p>
                                <p class="total-final"><strong>TOTAL:</strong> $ {{ number_format($totalFinal, 2) }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- Columna Derecha: ADICIONALES -->
                    <div class="col">
                        <h3 class="titulo-seccion">ADICIONALES</h3>
                        <div class="bloque-adicionales">
                            <table class="adicionales-table">
                                <thead>
                                    <tr>
                                        <th>Producto</th>
                                        <th>Días</th>
                                        <th>Precio por día</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @php
                                        $totalAdicionales = 0;

                                        $iconosServicios = [
                                            'Gasolina (faltante)' => 'fa-gas-pump',
                                            'Servicio de Litro Faltante' => 'fa-gas-pump',
                                            'Additional driver' => 'fa-user-plus',
                                            'Baby seat' => 'fa-child',
                                            'GPS' => 'fa-location-dot',
                                            'Drop Off' => 'fa-flag-checkered',
                                            'Delivery' => 'fa-truck',
                                        ];

                                        $extrasSeleccionados = [];
                                        foreach ($extras as $extra) {
                                            $extrasSeleccionados[$extra->nombre] = [
                                                'precio' => $extra->precio_unitario ?? 0,
                                                'cantidad' => $extra->cantidad ?? 1,
                                            ];
                                        }

                                        $deliveryActivo = $deliveryInfo && ($deliveryInfo->precio_unitario ?? 0) > 0;
                                        $dropoffActivo = $dropoffInfo && ($dropoffInfo->precio_unitario ?? 0) > 0;
                                        $gasolinaActiva = $gasolinaInfo && ($gasolinaInfo->precio_unitario ?? 0) > 0;
                                        $serviciosMostrar = [
                                            'Additional driver' => ['icono' => 'fa-user-plus', 'es_especial' => false],
                                            'Conductor menor' => ['icono' => 'fa-user-minus', 'es_especial' => false],
                                            'Baby seat' => ['icono' => 'fa-child', 'es_especial' => false],
                                            'GPS' => ['icono' => 'fa-location-dot', 'es_especial' => false],
                                            'Delivery' => ['icono' => 'fa-truck', 'es_especial' => true],
                                            'Drop Off' => ['icono' => 'fa-flag-checkered', 'es_especial' => true],
                                            'Gasolina (faltante)' => ['icono' => 'fa-fire', 'es_especial' => true],
                                        ];
                                    @endphp

                                    @foreach ($serviciosMostrar as $nombre => $config)
                                        @php
                                            $icono = $config['icono'];
                                            $esEspecial = $config['es_especial'];

                                            $seleccionado = isset($extrasSeleccionados[$nombre]);

                                            if ($nombre === 'Delivery' && $deliveryActivo) {
                                                $seleccionado = true;
                                                $precio = $deliveryInfo->precio_unitario ?? 0;
                                                $cantidad = 1;
                                                $detalles = $deliveryInfo->direccion ?? '';
                                            } elseif ($nombre === 'Drop Off' && $dropoffActivo) {
                                                $seleccionado = true;
                                                $precio = $dropoffInfo->precio_unitario ?? 0;
                                                $cantidad = 1;
                                                $detalles = $dropoffInfo->destino ?? '';
                                            } elseif ($nombre === 'Gasolina (faltante)' && $gasolinaActiva) {
                                                $seleccionado = true;
                                                $precio = $gasolinaInfo->precio_unitario ?? 0;
                                                $cantidad = $gasolinaInfo->cantidad ?? 1;
                                                $detalles =
                                                    ($gasolinaInfo->litros ?? 0) > 0
                                                        ? $gasolinaInfo->litros . ' L'
                                                        : '';
                                            } elseif ($seleccionado) {
                                                $precio = $extrasSeleccionados[$nombre]['precio'];
                                                $cantidad = $extrasSeleccionados[$nombre]['cantidad'];
                                                $detalles = '';
                                            } else {
                                                $precio = 0;
                                                $cantidad = 0;
                                                $detalles = '';
                                            }

                                            // Calcular subtotal
                                            if ($seleccionado && $precio > 0) {
                                                if (!$esEspecial) {
                                                    $totalAdicionales += $precio * $cantidad * $dias;
                                                } else {
                                                    $totalAdicionales += $precio;
                                                }
                                            }

                                            $estadoClase =
                                                $seleccionado && $precio > 0 ? 'seleccionado' : 'no-seleccionado';
                                            $estadoTexto =
                                                $seleccionado && $precio > 0 ? number_format($precio, 2) : '0.00';
                                            $cantidadMostrar = $seleccionado && $cantidad > 0 ? $cantidad : 0;
                                            $diasMostrar = !$esEspecial ? $dias : '—';

                                            if ($esEspecial) {
                                                $diasMostrar = '—';
                                            }
                                        @endphp
                                        <tr class="adicional-item {{ $estadoClase }}">
                                            <td>
                                                <i class="fa-solid {{ $icono }}"></i>
                                                {{ $nombre }}
                                                @if ($seleccionado && $cantidadMostrar > 1)
                                                    <span class="badge-cantidad">×{{ $cantidadMostrar }}</span>
                                                @endif
                                                @if ($seleccionado && !empty($detalles))
                                                    <span class="badge-ubicacion">{{ $detalles }}</span>
                                                @endif
                                                @if (!$seleccionado || $precio == 0)
                                                    <span class="badge-inactivo">(No seleccionado)</span>
                                                @endif
                                            </td>
                                            <td class="{{ !$seleccionado || $precio == 0 ? 'texto-inactivo' : '' }}">
                                                {{ $diasMostrar }}
                                            </td>
                                            <td class="{{ !$seleccionado || $precio == 0 ? 'texto-inactivo' : '' }}">
                                                $ {{ $estadoTexto }}
                                            </td>
                                        </tr>
                                    @endforeach

                                    {{-- FILA DE TOTAL --}}
                                    @if ($totalAdicionales > 0)
                                        <tr class="adicional-total-row">
                                            <td colspan="2"
                                                style="text-align: right; font-weight: bold; font-size:16px; color: #ffffff;">
                                                TOTAL ADICIONALES
                                            </td>
                                            <td style="font-weight: bold; font-size:18px; color: #ffffff;">
                                                $ {{ number_format($totalAdicionales, 2) }}
                                            </td>
                                        </tr>
                                    @else
                                        <tr class="adicional-total-row">
                                            <td colspan="3"
                                                style="text-align: center; font-weight: bold; color: #94a3b8;">
                                                Ningún adicional seleccionado
                                            </td>
                                        </tr>
                                    @endif
                                </tbody>
                            </table>
                        </div>
                    </div>

                </div>

                <!-- ==========================================
                        ACEPTACIÓN + FIRMAS - Una columna (BLANCO)
                        ========================================== -->
                <div class="row-full">
                    <div class="bloque-aceptacion">
                        <p class="aceptacion-texto">
                            Acepto plenamente las obligaciones descritas en la carátula y en el clausulado de este contrato.
                            Declaro bajo protesta de decir verdad, haber recibido el auto descrito en el apartado de salida
                            y acepto las condiciones generales al inicio de la renta, así mismo entiendo y acepto las
                            condiciones
                            del tratamiento de mis datos personales como se describe en el aviso de privacidad que se
                            encuentra
                            a mi disposición en:
                            <a href="https://www.viajerocarental.com" target="_blank">https://www.viajerocarental.com</a>
                        </p>

                        <div class="firmas-container">
                            <div class="firma-item">
                                <p class="firma-label">(firma de arrendatario)</p>
                                @if (!empty($contrato->firma_cliente))
                                    <img src="{{ $contrato->firma_cliente }}" class="firma-img">
                                @else
                                    <div class="firma-linea"></div>
                                @endif
                                <p class="firma-nombre">
                                    {{ trim(($reservacion->nombre_cliente ?? '') . ' ' . ($reservacion->apellidos_cliente ?? '')) ?: 'CLIENTE' }}
                                </p>
                            </div>

                            <div class="firma-item">
                                <p class="firma-label">(firma de arrendador)</p>
                                @if (!empty($vehiculo?->firma_propietario))
                                    <img src="{{ $vehiculo->firma_propietario }}" class="firma-img">
                                @else
                                    <div class="firma-linea"></div>
                                @endif
                                <p class="firma-nombre">VIAJERO CAR RENTAL</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- ==========================================
                            GASOLINA - Una columna (BLANCO)
                        ========================================== -->
                <div class="row-full">
                    <div class="bloque-gasolina">
                        <p class="gasolina-texto">
                            <strong>GASOLINA:</strong> PRECIO POR LITRO FALTANTE $13.16 MXN MAS CARGO POR SERVICIO DE 23.96
                            MXN POR LITRO FALTANTE IMPUESTOS INCLUIDOS
                            <span class="nota-gas">(APLICABLE SI LA OPCION DE PREPAGO DE GAS NO FUE ADQUIRIDA)</span>
                        </p>
                    </div>
                </div>

                <!-- ==========================================
                            NOTAS - Una columna (BLANCO)
                        ========================================== -->
                <div class="row-full">
                    <div class="bloque-notas">
                        <p class="nota-title"><strong>INFORMACIÓN DE LOS CARGOS TOTALES:</strong></p>
                        <p class="nota"><strong>(1)</strong> Al firmar este contrato el cliente declara tener
                            conocimiento de todas las condiciones establecidas y acepta el clausulado al reverso.</p>
                        <p class="nota"><strong>(2)</strong> Los cargos son ESTIMADOS, el importe total a pagar del
                            contrato aparecerá al cierre del mismo.</p>
                        <p class="nota"><strong>(3)</strong> Usted va alquilar y devolver el vehículo en el momento y
                            lugares indicados. Gasolina no reembolsable en prepago. EXCEPTO si se regresa con tanque lleno.
                        </p>
                        <p class="nota"><strong>(4)</strong> NO SE ACEPTA EFECTIVO como pago ni como deposito.</p>
                        <p class="nota"><strong>(5)</strong> CDW 0% incluye: ROBO %, llantas, rines, cristales y espejos.
                        </p>
                        <p class="nota"><strong>(6)</strong> CDW20%, CDW10%, PCDW NO incluye llantas, rines, cristales y
                            espejos.</p>
                        <p class="nota"><strong>(7)</strong> Ninguna protección cubre GPS, Placas o llaves.</p>
                        <p class="nota"><strong>(8)</strong> CDW20%, CDW10%, PDW. LDW revocado en caso de negligencia del
                            conductor o si existen conductores NO autorizados en el contrato.</p>
                    </div>
                </div>

                <!-- ==========================================
                        DATOS DE FACTURACIÓN - Una columna (BLANCO)
                        ========================================== -->
                <div class="row-full">
                    <div class="bloque-facturacion">
                        <p class="fact-title"><strong>Datos de Facturación</strong></p>
                        <div class="fact-grid">
                            <div class="fact-item">
                                <span class="label">No. cliente fiscal:</span>
                                <span class="value">{{ $reservacion->cliente_fiscal ?? '—' }}</span>
                            </div>
                            <div class="fact-item">
                                <span class="label">RFC:</span>
                                <span class="value">{{ $reservacion->rfc_cliente ?? '—' }}</span>
                            </div>
                            <div class="fact-item">
                                <span class="label">Razón social:</span>
                                <span class="value">{{ $reservacion->razon_social_cliente ?? '—' }}</span>
                            </div>
                            <div class="fact-item">
                                <span class="label">Calle:</span>
                                <span class="value">{{ $reservacion->direccion_cliente ?? '—' }}</span>
                            </div>
                            <div class="fact-item">
                                <span class="label">No. Ext.</span>
                                <span class="value">{{ $reservacion->num_ext_cliente ?? '—' }}</span>
                            </div>
                            <div class="fact-item">
                                <span class="label">No. Int.</span>
                                <span class="value">{{ $reservacion->num_int_cliente ?? '—' }}</span>
                            </div>
                            <div class="fact-item">
                                <span class="label">C.P.:</span>
                                <span class="value">{{ $reservacion->cp_cliente ?? '—' }}</span>
                            </div>
                            <div class="fact-item">
                                <span class="label">Colonia:</span>
                                <span class="value">{{ $reservacion->colonia_cliente ?? '—' }}</span>
                            </div>
                            <div class="fact-item">
                                <span class="label">Estado:</span>
                                <span class="value">{{ $reservacion->estado_cliente ?? '—' }}</span>
                            </div>
                            <div class="fact-item">
                                <span class="label">Municipio:</span>
                                <span class="value">{{ $reservacion->municipio_cliente ?? '—' }}</span>
                            </div>
                            <div class="fact-item">
                                <span class="label">País:</span>
                                <span class="value">{{ $reservacion->pais_cliente ?? '—' }}</span>
                            </div>
                            <div class="fact-item">
                                <span class="label">Ciudad:</span>
                                <span class="value">{{ $reservacion->ciudad_cliente ?? '—' }}</span>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>

        <!-- ==========================================
                    PIE DE PÁGINA - ROJO
                ========================================== -->
        <footer class="pie-rojo">
            <p class="pie-empresa"><strong>VIAJERO CAR RENTAL</strong></p>

            <div class="pie-contenido-columnas">
                <div class="col-izq">
                    <p>Business Center INNERA Central Park. Armando Birlain Shaffler #2001, Torre 2, Centro Sur, Qro.</p>
                    <p>Teléfono: 442 303 26 68 &nbsp; Celular: 442 716 97 93 | &nbsp; 442 343 07 70</p>
                </div>

                <div class="col-der">
                    <span>Arrendador: José Juan de Dios Hernández Resendiz</span>
                    <div class="pie-correos">
                        <span>Facturación: facturación@viajeroacr-rental.com</span>
                        <span>Reservaciones: reservaciones@viajeroacr-rental.com</span>
                    </div>
                </div>
            </div>
        </footer>

                    <div class="confirmacion-revision">
                        <button
                            type="button"
                            class="btn-confirmar-revision"
                            data-marcar-revision="contrato"
                            {{ !empty($revisionesContrato['contrato']) ? 'disabled' : '' }}
                        >
                            <i class="fa-solid fa-check"></i>
                            <span>{{ !empty($revisionesContrato['contrato']) ? 'Contrato revisado' : 'Marcar contrato como revisado' }}</span>
                        </button>
                    </div>
                </div>
            </section>

            <section
                class="documento-acordeon {{ !empty($revisionesContrato['clausulas']) ? 'revisado' : '' }}"
                data-seccion="clausulas"
                data-revisado="{{ !empty($revisionesContrato['clausulas']) ? '1' : '0' }}"
            >
                <button type="button" class="documento-encabezado" aria-expanded="false">
                    <span class="documento-numero">2</span>
                    <span class="documento-titulo">
                        <strong>Cláusulas del contrato</strong>
                        <small>{{ !empty($revisionesContrato['clausulas']) ? 'Revisadas' : 'Pendientes de revisión' }}</small>
                    </span>
                    <span class="documento-estado">
                        <i class="fa-solid {{ !empty($revisionesContrato['clausulas']) ? 'fa-circle-check' : 'fa-circle' }}"></i>
                    </span>
                    <i class="fa-solid fa-chevron-down documento-flecha"></i>
                </button>

                <div class="documento-contenido">
                    <div class="clausulas-documento">
                        @include('Admin.clausulas-contrato')
                    </div>

                    <div class="confirmacion-revision">
                        <button
                            type="button"
                            class="btn-confirmar-revision"
                            data-marcar-revision="clausulas"
                            {{ !empty($revisionesContrato['clausulas']) ? 'disabled' : '' }}
                        >
                            <i class="fa-solid fa-check"></i>
                            <span>{{ !empty($revisionesContrato['clausulas']) ? 'Cláusulas revisadas' : 'Marcar cláusulas como revisadas' }}</span>
                        </button>
                    </div>
                </div>
            </section>

            <section
                class="documento-acordeon {{ !empty($revisionesContrato['checklist']) ? 'revisado' : '' }}"
                data-seccion="checklist"
                data-revisado="{{ !empty($revisionesContrato['checklist']) ? '1' : '0' }}"
            >
                <button type="button" class="documento-encabezado" aria-expanded="false">
                    <span class="documento-numero">3</span>
                    <span class="documento-titulo">
                        <strong>Checklist del vehículo</strong>
                        <small>{{ !empty($revisionesContrato['checklist']) ? 'Revisado' : 'Pendiente de revisión' }}</small>
                    </span>
                    <span class="documento-estado">
                        <i class="fa-solid {{ !empty($revisionesContrato['checklist']) ? 'fa-circle-check' : 'fa-circle' }}"></i>
                    </span>
                    <i class="fa-solid fa-chevron-down documento-flecha"></i>
                </button>

                <div class="documento-contenido">
                    <div class="documento-frame-container">
                        <iframe
                            class="documento-frame"
                            title="Checklist del vehículo"
                            loading="lazy"
                            src="{{ route('checklist.ver', [
                                'id' => $contrato->id_contrato,
                                'modo' => 'salida',
                                'embed' => 1
                            ]) }}"
                        ></iframe>
                    </div>

                    <div class="confirmacion-revision">
                        <button
                            type="button"
                            class="btn-confirmar-revision"
                            data-marcar-revision="checklist"
                            {{ !empty($revisionesContrato['checklist']) ? 'disabled' : '' }}
                        >
                            <i class="fa-solid fa-check"></i>
                            <span>{{ !empty($revisionesContrato['checklist']) ? 'Checklist revisado' : 'Marcar checklist como revisado' }}</span>
                        </button>
                    </div>
                </div>
            </section>

            @if ($tieneConductorAdicional)
                <section
                    class="documento-acordeon {{ !empty($revisionesContrato['conductor_adicional']) ? 'revisado' : '' }}"
                    data-seccion="conductor_adicional"
                    data-revisado="{{ !empty($revisionesContrato['conductor_adicional']) ? '1' : '0' }}"
                >
                    <button type="button" class="documento-encabezado" aria-expanded="false">
                        <span class="documento-numero">4</span>
                        <span class="documento-titulo">
                            <strong>Conductor adicional</strong>
                            <small>{{ !empty($revisionesContrato['conductor_adicional']) ? 'Revisado' : 'Pendiente de revisión' }}</small>
                        </span>
                        <span class="documento-estado">
                            <i class="fa-solid {{ !empty($revisionesContrato['conductor_adicional']) ? 'fa-circle-check' : 'fa-circle' }}"></i>
                        </span>
                        <i class="fa-solid fa-chevron-down documento-flecha"></i>
                    </button>

                    <div class="documento-contenido">
                        <div class="documento-frame-container">
                            <iframe
                                class="documento-frame"
                                title="Conductor adicional"
                                loading="lazy"
                                src="{{ route('anexo.ver', [
                                    'id' => $contrato->id_contrato,
                                    'embed' => 1
                                ]) }}"
                            ></iframe>
                        </div>

                        <div class="confirmacion-revision">
                            <button
                                type="button"
                                class="btn-confirmar-revision"
                                data-marcar-revision="conductor_adicional"
                                {{ !empty($revisionesContrato['conductor_adicional']) ? 'disabled' : '' }}
                            >
                                <i class="fa-solid fa-check"></i>
                                <span>{{ !empty($revisionesContrato['conductor_adicional']) ? 'Conductor adicional revisado' : 'Marcar conductor adicional como revisado' }}</span>
                            </button>
                        </div>
                    </div>
                </section>
            @endif
        </div>
    </div>

    <!-- MODAL FIRMA ARRENDADOR -->
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

    <!-- MODAL AVISO LEGAL -->
    <div id="modalAviso" class="modal-firma" style="display:none;">
        <div class="modal-body" style="max-width:650px;">

            <h3>Aviso de responsabilidad</h3>

            <p style="font-size:15px; margin-bottom:10px;">
                Por favor, lea el siguiente texto de responsabilidad y firme para confirmar que está de acuerdo:
            </p>

            @php
                $fechaDevolucionAviso = !empty($reservacion->fecha_fin)
                    ? \Carbon\Carbon::parse($reservacion->fecha_fin)
                        ->locale('es')
                        ->translatedFormat('d \d\e F \d\e Y')
                    : 'la fecha establecida';

                $horaDevolucionAviso = !empty($reservacion->hora_entrega)
                    ? \Carbon\Carbon::parse($reservacion->hora_entrega)
                        ->format('H:i')
                    : 'la hora establecida';

                $nombreArrendatarioAviso = !empty($reservacion->nombre_cliente)
                    ? trim(
                        $reservacion->nombre_cliente . ' ' .
                        ($reservacion->apellidos_cliente ?? '')
                    )
                    : '________________';
            @endphp

            <div
                style="
                    background:#fff3f3;
                    padding:12px 15px;
                    border-left:4px solid #c00;
                    border-radius:8px;
                    margin-bottom:18px;
                "
            >
                <p id="textoOriginal" style="margin:0; line-height:1.6; text-align:justify;">
                    Yo,
                    <strong>{{ $nombreArrendatarioAviso }}</strong>,
                    manifiesto estar plenamente consciente de que cualquier daño, negligencia o mal uso del vehículo
                    que no esté cubierto por mi paquete de seguro o protecciones individuales será responsabilidad mía,
                    y acepto pagar los cargos adicionales que pudieran generarse conforme a las políticas de
                    Viajero Car Rental. Asimismo, me comprometo a regresar el vehículo el día
                    <strong>{{ $fechaDevolucionAviso }}</strong>
                    a las
                    <strong>{{ $horaDevolucionAviso }} horas</strong>,
                    conforme a la fecha y hora establecidas en el contrato.
                </p>
            </div>

            <div style="margin-top:10px;">
                <p style="font-size:14px; margin-bottom:8px;">
                    Firma del arrendatario en conformidad con el aviso:
                </p>

                <div
                    style="
                        border:1px solid #ccc;
                        border-radius:8px;
                        padding:10px;
                        background:#fafafa;
                    "
                >
                    <canvas
                        id="padAviso"
                        width="500"
                        height="200"
                    ></canvas>

                    <div
                        class="firma-buttons"
                        style="
                            margin-top:10px;
                            display:flex;
                            gap:10px;
                        "
                    >
                        <button
                            id="clearAviso"
                            class="btn btn-gray"
                            type="button"
                        >
                            Limpiar firma
                        </button>
                    </div>
                </div>
            </div>

            <div
                class="firma-buttons"
                style="
                    margin-top:15px;
                    display:flex;
                    gap:10px;
                    justify-content:flex-end;
                "
            >
                <button
                    id="cancelarAviso"
                    class="btn btn-gray"
                    type="button"
                >
                    Cancelar
                </button>

                <button
                    id="confirmarAviso"
                    class="btn btn-red"
                    type="button"
                >
                    Confirmar y Enviar
                </button>
            </div>

        </div>
    </div>

@endsection

@section('js-vistaContratoFinal')
   <script src="https://cdn.jsdelivr.net/npm/signature_pad@4.1.7/dist/signature_pad.umd.min.js"></script>
<script src="{{ asset('js/ContratoFinal.js') }}"></script>
@endsection