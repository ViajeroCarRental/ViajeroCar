@extends('layouts.Ventas')
@section('Titulo', 'Contrato')

@section('css-vistaContrato')
    <!-- CSS de Flatpickr (solo el base) -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">

    <!-- JS de Flatpickr -->
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script src="https://cdn.jsdelivr.net/npm/flatpickr/dist/l10n/es.js"></script>

    <!-- TU CSS de Contrato DEBE ir DESPUÉS para sobrescribir -->
    <link rel="stylesheet" href="{{ asset('css/Contrato.css') }}">
@endsection

@section('contenidoContrato')

    <main class="main" id="contratoApp" data-id-contrato="{{ $contrato->id_contrato ?? '' }}"
        data-numero="{{ $contrato->numero_contrato ?? '' }}" data-id-reservacion="{{ $reservacion->id_reservacion ?? '' }}"
        data-id-categoria="{{ $reservacion->id_categoria ?? '' }}">

        {{-- =========================================
           NAVBAR ROJA (estilo IDENTICO a reservaciones)
        ========================================= --}}
        <header class="topbar-contrato">
            <div class="nav-contrato">
                <div class="brand-contrato">
                    <i class="fas fa-car"></i>
                    <span>VIAJERO</span>
                    <span class="brand-separator">|</span>
                    <span class="brand-subtitle">Gestión de Contrato</span>
                </div>

                <div class="contrato-info">
                    <span class="contrato-numero">
                        <i class="fas fa-hashtag"></i>
                        No. Contrato:
                        <strong id="contratoNumeroDisplay">{{ $contrato->numero_contrato ?? '0001' }}</strong>
                    </span>
                </div>

                <div class="nav-actions-contrato">
                    <div class="resumen-wrapper">
                        <button type="button" class="btn-resumen-contrato" id="btnToggleDetalle">
                            <i class="fas fa-shopping-cart"></i>
                            <div class="resumen-totales-container">
                                <span id="btnTotalTextContrato">${{ number_format($total ?? 0, 2) }} MXN</span>
                                <small id="btnTotalUsdContrato"
                                    class="resumen-usd">${{ number_format(($total ?? 0) / 20, 2) }} USD</small>
                            </div>
                            <i class="fas fa-chevron-down" id="iconoFlechaResumen"></i>
                        </button>
                    </div>
                </div>
            </div>

            {{-- Desplegable del resumen --}}
            <div class="resumen-desplegable-contrato" id="resumenDetalleContainer">
                <div class="resumen-card-contrato">
                    <div class="head">
                        <i class="fas fa-receipt"></i> RESUMEN DEL CONTRATO
                    </div>
                    <div class="cnt resumen-compacto" id="resumenCompacto">
                        <div id="vehiculo_info" class="vehiculo-mini-wrap">
                            <img id="resumenImgVeh" src="{{ $vehiculo->imagen_render ?? '' }}" alt="Imagen del vehículo"
                                class="vehiculo-img">
                            <p class="vehiculo-nombre" id="resumenVehCompacto">—</p>
                            <p class="vehiculo-mini" id="resumenCategoriaCompacto">Categoría: —</p>
                            <p class="vehiculo-mini" id="resumenDiasCompacto">Días de renta: —</p>
                            <p class="vehiculo-mini" id="resumenFechasCompacto">— / —</p>
                            <div class="protecciones-compacto-box">
                                <span class="protecciones-compacto-label">Protecciones: </span>
                                <span class="protecciones-compacto-value" id="resumenProteccionesCompacto">
                                    —
                                </span>
                            </div>
                        </div>
                        <div class="totalBox" style="margin-top:12px;">
                            <div class="kv">
                                <div>Total actual</div>
                                <div class="total" id="resumenTotalCompacto">${{ number_format($total ?? 0, 2) }} MXN</div>
                            </div>
                        </div>
                        <button id="btnVerDetalle" class="btn-resumen-contrato-detalle">Ver detalle ▼</button>
                    </div>

                    <div class="cnt resumen-detalle" id="resumenDetalle" style="display:none;">
                        <div id="detalleContenido">
                            <section class="res-block">
                                <h4>Código de reservación</h4>
                                <p id="detCodigo">—</p>
                            </section>
                            <section class="res-block">
                                <h4>Datos del cliente</h4>
                                <p id="detCliente">{{ strtoupper($reservacion->nombre_cliente ?? '—') }}</p>
                                <p id="detTelefono">{{ $telFinal }}</p>
                                <p id="detEmail">{{ $reservacion->email_cliente ?? '—' }}</p>
                            </section>
                            <section class="res-block">
                                <h4>Vehículo</h4>
                                <p><b id="detModelo">—</b></p>
                                <p>Marca: <span id="detMarca">—</span></p>
                                <p>Categoría: <span id="detCategoria">—</span></p>
                                <p>Transmisión: <span id="detTransmision">—</span></p>
                                <p>Pasajeros: <span id="detPasajeros">—</span></p>
                                <p>Puertas: <span id="detPuertas">—</span></p>
                                <p>Kilometraje actual: <span id="detKm">—</span></p>
                            </section>
                            <section class="res-block">
                                <h4>Fechas y horarios</h4>
                                <p>Salida: <span id="detFechaSalida">{{ $fechaInicio->format('Y-m-d') }}</span> · <span
                                        id="detHoraSalida">{{ $horaRetiro->format('h:i A') }}</span></p>
                                <p>Entrega: <span id="detFechaEntrega">{{ $fechaFin->format('Y-m-d') }}</span> · <span
                                        id="detHoraEntrega">{{ $horaEntrega->format('h:i A') }}</span></p>
                                <p><strong>Días totales:</strong> <span id="detDiasRenta">{{ $diasTotales }}</span></p>
                            </section>
                            <section class="res-block">
                                <h4>Paquetes de cobertura</h4>
                                <ul id="r_seguros_lista" class="det-lista">
                                    <li class="empty">—</li>
                                </ul>
                                <p>Total: <b id="r_seguros_total">—</b></p>
                            </section>
                            <section class="res-block">
                                <h4>Adicionales</h4>
                                <ul id="r_servicios_lista" class="det-lista">
                                    <li class="empty">—</li>
                                </ul>
                                <p>Total: <b id="r_servicios_total">—</b></p>
                            </section>
                            <section class="res-block">
                                <h4>Total desglosado</h4>
                                <p>Tarifa base: <b id="r_base_precio">${{ number_format($precioReal, 2) }}</b>
                                    <button id="btnEditarTarifa" class="btn-icono-editar" title="Editar tarifa base">
                                        <i class="fas fa-pencil-alt"></i>
                                    </button>
                                </p>
                                <p>Horas de cortesía: <b id="r_cortesia">{{ $reservacion->horas_cortesia ?? 1 }}</b>
                                    <button id="btnEditarCortesia" class="btn-icono-editar"
                                        title="Editar horas de cortesía">
                                        <i class="fas fa-pencil-alt"></i>
                                    </button>
                                </p>
                                <p>Subtotal: <b id="r_subtotal">${{ number_format($subtotal, 2) }}</b></p>
                                <p>IVA: <b id="r_iva">${{ number_format($iva, 2) }}</b></p>
                                <p>Total contrato: <b id="r_total_final">${{ number_format($total, 2) }}</b></p>
                            </section>
                            <section class="res-block">
                                <h4>Pagos y saldo</h4>
                                <p>Pagos realizados: <b id="detPagos">—</b></p>
                                <p>Saldo pendiente: <b id="detSaldo">—</b></p>
                            </section>
                        </div>
                        <button id="btnOcultarDetalle" class="btn-resumen-contrato-detalle">Ocultar detalle ▲</button>
                    </div>
                </div>
            </div>
        </header>

        {{-- Navbar --}}
        <nav class="stepper-navbar">
            <ul class="stepper-list">
                <li class="stepper-item active" data-step-indicator="1">
                    <div class="stepper-circle">1</div>
                    <div class="stepper-title">Reservación</div>
                </li>
                <li class="stepper-line"></li>
                <li class="stepper-item" data-step-indicator="2">
                    <div class="stepper-circle">2</div>
                    <div class="stepper-title">Servicios</div>
                </li>
                <li class="stepper-line"></li>
                <li class="stepper-item" data-step-indicator="3">
                    <div class="stepper-circle">3</div>
                    <div class="stepper-title">Protecciones</div>
                </li>
                <li class="stepper-line"></li>
                <li class="stepper-item" data-step-indicator="4">
                    <div class="stepper-circle">4</div>
                    <div class="stepper-title">Documentación</div>
                </li>
                <li class="stepper-line"></li>
                <li class="stepper-item" data-step-indicator="5">
                    <div class="stepper-circle">5</div>
                    <div class="stepper-title">Revisión Final</div>
                </li>
                <li class="stepper-line"></li>
                <li class="stepper-item" data-step-indicator="6">
                    <div class="stepper-circle">6</div>
                    <div class="stepper-title">Pago</div>
                </li>
            </ul>
        </nav>

        <div class="grid full-width-grid">

            <section class="steps">

                {{-- Paso 1 --}}
                <article class="step active" data-step="1">
                    <div class="body contrato-resumen" id="contratoInicial"
                        data-id-contrato="{{ $contrato->id_contrato ?? '' }}"
                        data-id-reservacion="{{ $reservacion->id_reservacion ?? '' }}"
                        data-id-vehiculo="{{ $vehiculo->id_vehiculo ?? ($reservacion->id_vehiculo ?? '') }}"
                        data-id-categoria="{{ $reservacion->id_categoria ?? '' }}"
                        data-codigo-categoria="{{ $codigoCat ?? '' }}" data-codigo="{{ $reservacion->codigo ?? '' }}"
                        data-inicio="{{ $reservacion->fecha_inicio ?? '' }}"
                        data-fin="{{ $reservacion->fecha_fin ?? '' }}">

                        <div class="grid-diseno-imagen">
                            <!-- ================================================ -->
                            <!-- COLUMNA IZQUIERDA: Datos de Reservación (TARJETA) -->
                            <!-- ================================================ -->
                            <div class="col-reservacion">
                                <div class="tarjeta-blanca">
                                    <div class="tarjeta-blanca-header">
                                        <h3 class="tarjeta-blanca-titulo"> <i class="fas fa-clipboard-list"></i>Datos de
                                            Reservación</h3>
                                    </div>
                                    <div class="tarjeta-blanca-body">
                                        <div class="campo-resumen">
                                            <label class="campo-resumen-label">Código Reservación</label>
                                            <span
                                                class="campo-resumen-valor">{{ strtoupper($reservacion->codigo ?? '—') }}</span>
                                        </div>

                                        <div class="campo-resumen" style="margin-bottom: 30px;">
                                            <label class="campo-resumen-label">Titular</label>
                                            <span
                                                class="campo-resumen-valor">{{ strtoupper($reservacion->nombre_cliente ?? '') }}
                                                {{ strtoupper($reservacion->apellidos_cliente ?? '') }}</span>
                                        </div>

                                        {{-- Tarjetas de Fecha con Flatpickr --}}
                                        <div class="flex-fechas">
                                            {{-- Tarjeta Entrega --}}
                                            <div class="tarjeta-fecha" id="tarjetaEntrega">
                                                <div class="tarjeta-fecha-header">ENTREGA</div>
                                                <div class="tarjeta-fecha-body">
                                                    <span id="txtDiaEntrega"
                                                        class="fecha-numero">{{ $fechaInicio->format('d') }}</span>
                                                    <div class="fecha-mes-anio">
                                                        <span
                                                            id="txtMesEntrega">{{ strtoupper($fechaInicio->translatedFormat('M')) }}</span>
                                                        <span id="txtAnioEntrega">{{ $fechaInicio->format('Y') }}</span>
                                                    </div>
                                                    <div id="txtHoraEntrega" class="fecha-hora">
                                                        {{ $horaRetiro->format('H:i') }} HRS</div>
                                                </div>
                                                <input type="text" id="pickerEntrega" class="flatpickr-input" readonly
                                                    value="{{ $fechaInicio->format('Y-m-d H:i') }}">
                                            </div>

                                            {{-- Tarjeta Devolución --}}
                                            <div class="tarjeta-fecha" id="tarjetaDevolucion">
                                                <div class="tarjeta-fecha-header">DEVOLUCIÓN</div>
                                                <div class="tarjeta-fecha-body">
                                                    <span id="txtDiaDevolucion"
                                                        class="fecha-numero">{{ $fechaFin->format('d') }}</span>
                                                    <div class="fecha-mes-anio">
                                                        <span
                                                            id="txtMesDevolucion">{{ strtoupper($fechaFin->translatedFormat('M')) }}</span>
                                                        <span id="txtAnioDevolucion">{{ $fechaFin->format('Y') }}</span>
                                                    </div>
                                                    <div id="txtHoraDevolucion" class="fecha-hora">
                                                        {{ $horaEntrega->format('H:i') }} HRS</div>
                                                </div>
                                                <input type="text" id="pickerDevolucion" class="flatpickr-input"
                                                    readonly value="{{ $fechaFin->format('Y-m-d H:i') }}">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- ================================================ -->
                            <!-- COLUMNA DERECHA: Datos del Auto (TARJETA)         -->
                            <!-- ================================================ -->
                            <div class="col-auto">
                                <div class="tarjeta-blanca tarjeta-vehiculo">
                                    <div class="tarjeta-blanca-header">
                                        <h3 class="tarjeta-blanca-titulo"><i class="fas fa-car"></i> Datos del Auto</h3>
                                    </div>
                                    <div class="tarjeta-blanca-body">
                                        {{-- Nombre y categoría --}}
                                        <div class="vehiculo-titulo">
                                            <h2 id="detNombreVehiculoStep1" class="vehiculo-nombre-paso1">
                                                {{ $vehiculo->nombre_publico ?? trim(($vehiculo->marca ?? '') . ' ' . ($vehiculo->modelo ?? '')) ?: 'Vehículo' }}
                                            </h2>
                                            <p class="vehiculo-categoria-paso1">
                                                <span id="detCategoriaCodigoStep1">{{ $codigoCat }}</span> |
                                                <span
                                                    id="detCategoriaNombreStep1">{{ strtoupper($catActual->nombre ?? '') }}</span>
                                            </p>
                                        </div>

                                        {{-- ========================================== --}}
                                        {{-- LAYOUT HORIZONTAL: Imagen + Especificaciones --}}
                                        {{-- ========================================== --}}
                                        <div class="vehiculo-horizontal">
                                            {{-- Imagen del vehículo --}}
                                            <div class="vehiculo-imagen-wrap">
                                                <div class="imagen-vehiculo-container">
                                                    <img src="{{ $imgFinal }}" id="mainImgVeh"
                                                        class="imagen-vehiculo" alt="Coche">
                                                </div>
                                            </div>

                                            {{-- Especificaciones --}}
                                            <div class="vehiculo-especificaciones-wrap">
                                                <div class="especificaciones-horizontal">
                                                    <div class="especificacion-item-horizontal">
                                                        <span class="especificacion-label-horizontal">Puertas</span>
                                                        <span id="step1Puertas"
                                                            class="especificacion-valor-horizontal">{{ $vehiculo->puertas ?? 0 }}</span>
                                                    </div>
                                                    <div class="especificacion-item-horizontal">
                                                        <span class="especificacion-label-horizontal">Pasajeros</span>
                                                        <span id="step1Pasajeros"
                                                            class="especificacion-valor-horizontal">{{ $vehiculo->asientos ?? 0 }}</span>
                                                    </div>
                                                    <div class="especificacion-item-horizontal">
                                                        <span class="especificacion-label-horizontal">Transmisión</span>
                                                        <span id="step1Transmision"
                                                            class="especificacion-valor-horizontal">{{ strtoupper($vehiculo->transmision ?? 'N/A') }}</span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        {{-- ========================================== --}}
                                        {{-- FIN LAYOUT HORIZONTAL                      --}}
                                        {{-- ========================================== --}}

                                        {{-- Botones --}}
                                        <div class="botones-accion-paso1">
                                            <button type="button" class="btn secondary" id="btnElegirVehiculo">Elegir
                                                vehículo</button>
                                            <button type="button" class="btn secondary" id="btnCambiarCategoria">Cambiar
                                                categoría</button>
                                            <button class="btn primary" id="go2">Continuar</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </article>

                {{-- Paso 2 --}}
                <article class="step" data-step="2">
                    <div class="body">
                        <section class="section">
                            <div class="head">Selecciona servicios adicionales</div>

                            <div class="cnt">
                                {{-- ========================================== --}}
                                {{-- SECCIÓN 1: SERVICIOS NORMALES --}}
                                {{-- ========================================== --}}
                                <div class="servicios-seccion">
                                    <div class="servicios-seccion-titulo">
                                        <span class="seccion-icono"><i class="fas fa-clipboard-list"></i></span>
                                        Servicios disponibles
                                        <span class="seccion-badge">{{ count($servicios) }}</span>
                                    </div>

                                    <div id="serviciosGrid" class="add-grid">
                                        @forelse ($servicios as $s)
                                            @php
                                                $iconos = [
                                                    'Additional driver' => 'fa-user-plus',
                                                    'Conductor menor' => 'fa-user-minus',
                                                    'Baby seat' => 'fa-child',
                                                    'GPS' => 'fa-location-dot',
                                                ];
                                                $icono = $iconos[$s->nombre] ?? 'fa-cube';
                                                $descripcion = $s->descripcion ?? 'Servicio adicional disponible';
                                            @endphp

                                            <div class="card-servicio" data-id="{{ $s->id_servicio }}"
                                                data-precio="{{ $s->precio }}" data-tipo="{{ $s->tipo_cobro }}"
                                                data-nombre="{{ $s->nombre }}">

                                                <div class="servicio-header">
                                                    <span class="servicio-numero" data-tooltip="{{ $descripcion }}">
                                                        <i class="fa-solid {{ $icono }}"></i>
                                                    </span>
                                                    <span class="servicio-nombre">{{ $s->nombre }}</span>
                                                </div>

                                                <div class="servicio-precio">
                                                    <strong>${{ number_format($s->precio, 2) }} MXN/día</strong>
                                                </div>

                                                <div class="servicio-footer">
                                                    <div class="contador">
                                                        <button class="btn-contador menos">−</button>
                                                        <span
                                                            class="cantidad">{{ $serviciosReservados[$s->id_servicio] ?? 0 }}</span>
                                                        <button class="btn-contador mas">+</button>
                                                    </div>
                                                </div>
                                            </div>
                                        @empty
                                            <p>No hay servicios adicionales disponibles.</p>
                                        @endforelse
                                    </div>
                                </div>

                                {{-- ========================================== --}}
                                {{-- SECCIÓN 2: SERVICIOS ESPECIALES --}}
                                {{-- ========================================== --}}
                                <div class="servicios-seccion especiales">
                                    <div class="servicios-seccion-titulo">
                                        <span class="seccion-icono"><i class="fas fa-rocket"></i></span>
                                        Servicios especiales
                                        <span class="seccion-badge">3</span>
                                    </div>

                                    <div class="especiales-grid">
                                        {{-- DELIVERY --}}
                                        <div class="cargo-item delivery-wrapper {{ !empty($delivery->activo) ? 'active' : '' }}"
                                            data-id-reservacion="{{ $reservacion->id_reservacion }}"
                                            data-delivery-activo="{{ $delivery->activo ?? 0 }}"
                                            data-delivery-km="{{ $delivery->kms ?? '' }}"
                                            data-delivery-direccion="{{ $delivery->direccion ?? '' }}"
                                            data-delivery-total="{{ $delivery->total ?? 0 }}"
                                            data-delivery-ubicacion="{{ isset($delivery->id_ubicacion) ? $delivery->id_ubicacion : '' }}"
                                            data-costo-km="{{ $costoKmCategoria ?? 0 }}">

                                            <div class="cargo-item-header">
                                                <span class="servicio-numero"
                                                    data-tooltip="Entrega del vehículo a domicilio.">
                                                    <i class="fa-solid fa-truck"></i>
                                                </span>
                                                <span class="servicio-nombre">Servicio de Delivery</span>
                                                <div class="cargo-item-toggle">
                                                    <label class="switch switch-toggle">
                                                        <input type="checkbox" id="deliveryToggle" name="delivery_activo"
                                                            {{ !empty($delivery->activo) ? 'checked' : '' }}>
                                                        <span class="slider"></span>
                                                    </label>
                                                    <span class="toggle-label">Activar</span>
                                                </div>
                                            </div>

                                            <div id="deliveryFields" class="cargo-item-fields"
                                                style="display: {{ !empty($delivery->activo) ? 'block' : 'none' }};">
                                                <div class="extras-grid">
                                                    <div class="form-group">
                                                        <label>Ubicación</label>
                                                        <select id="deliveryUbicacion" name="delivery_ubicacion"
                                                            class="form-control-simple">
                                                            <option value="">Seleccione...</option>
                                                            @foreach ($ubicaciones as $u)
                                                                <option value="{{ $u->id_ubicacion }}"
                                                                    data-km="{{ $u->km }}"
                                                                    {{ !empty($delivery->id_ubicacion) && $delivery->id_ubicacion == $u->id_ubicacion ? 'selected' : '' }}>
                                                                    {{ $u->destino }} ({{ $u->km }} km)
                                                                </option>
                                                            @endforeach
                                                            <option value="0"
                                                                {{ isset($delivery->id_ubicacion) && $delivery->id_ubicacion == 0 ? 'selected' : '' }}>
                                                                Dirección manual
                                                            </option>
                                                        </select>
                                                    </div>
                                                    <div id="groupDireccion" class="form-group"
                                                        style="display: {{ isset($delivery->id_ubicacion) && $delivery->id_ubicacion == 0 ? 'block' : 'none' }};">
                                                        <label>Dirección</label>
                                                        <input type="text" id="deliveryDireccion"
                                                            class="form-control-simple" placeholder="Ej. Centro"
                                                            value="{{ $delivery->direccion ?? '' }}">
                                                    </div>
                                                    <div id="groupKm" class="form-group"
                                                        style="display: {{ isset($delivery->id_ubicacion) && $delivery->id_ubicacion == 0 ? 'block' : 'none' }};">
                                                        <label>Distancia (Km)</label>
                                                        <input type="number" min="0" id="deliveryKm"
                                                            class="form-control-simple" placeholder="0"
                                                            value="{{ $delivery->kms ?? '' }}">
                                                    </div>
                                                </div>
                                                <div class="cargo-item-total">
                                                    <span class="cargo-item-total-label">Costo Delivery</span>
                                                    <span id="deliveryTotal" class="cargo-item-total-amount">
                                                        ${{ number_format($delivery->total ?? 0, 2) }} MXN
                                                    </span>
                                                </div>
                                                <input type="hidden" id="deliveryPrecioKm"
                                                    value="{{ $costoKmCategoria ?? 0 }}">
                                                <input type="hidden" id="deliveryTotalHidden"
                                                    value="{{ $delivery->total ?? 0 }}">
                                            </div>
                                        </div>

                                        {{-- DROPOFF --}}
                                        <div class="cargo-item dropoff-wrapper {{ $dropActivo ?? false ? 'active' : '' }}"
                                            data-id="6" data-monto="{{ $dropTotal ?? 0 }}">
                                            <div class="cargo-item-header">
                                                <span class="servicio-numero"
                                                    data-tooltip="Recolección del vehículo en otro destino.">
                                                    <i class="fa-solid fa-flag-checkered"></i>
                                                </span>
                                                <span class="servicio-nombre">Servicio de Dropoff</span>
                                                <div class="cargo-item-toggle">
                                                    <label class="switch switch-toggle">
                                                        <input type="checkbox" id="switchDropoffCheckbox"
                                                            {{ $dropActivo ?? false ? 'checked' : '' }}>
                                                        <span class="slider"></span>
                                                    </label>
                                                    <span class="toggle-label">Activar</span>
                                                </div>
                                            </div>

                                            <div id="dropoffFields" class="cargo-item-fields"
                                                style="display: {{ $dropActivo ?? false ? 'block' : 'none' }};">
                                                <div class="extras-grid">
                                                    <div class="form-group">
                                                        <label>Ubicación</label>
                                                        <select id="dropUbicacion" class="form-control-simple">
                                                            <option value="">Seleccione...</option>
                                                            @foreach ($ubicaciones as $u)
                                                                <option value="{{ $u->id_ubicacion }}"
                                                                    data-km="{{ $u->km }}"
                                                                    {{ ($dropDest ?? '') == ($u->estado ?? '') . ' - ' . ($u->destino ?? '') ? 'selected' : '' }}>
                                                                    {{ ($u->estado ?? '') . ' - ' . ($u->destino ?? '') }}
                                                                    ({{ $u->km }} km)
                                                                </option>
                                                            @endforeach
                                                            <option value="0"
                                                                {{ $esManual ?? false ? 'selected' : '' }}>
                                                                Dirección manual</option>
                                                        </select>
                                                    </div>
                                                    <div id="dropGroupDireccion" class="form-group"
                                                        style="display: {{ $esManual ?? false ? 'block' : 'none' }};">
                                                        <label>Dirección</label>
                                                        <input type="text" id="dropDireccion"
                                                            class="form-control-simple" placeholder="Ej. Calle Las Flores"
                                                            value="{{ $dropDest ?? '' }}">
                                                    </div>
                                                    <div id="dropGroupKm" class="form-group"
                                                        style="display: {{ $esManual ?? false ? 'block' : 'none' }};">
                                                        <label>Distancia (Km)</label>
                                                        <input type="number" min="0" id="dropKm"
                                                            class="form-control-simple" placeholder="Ej. 25"
                                                            value="{{ $dropKm ?? '' }}">
                                                    </div>
                                                </div>
                                                <div id="dropCostoKm" class="cargo-item-costo-km">
                                                    Costo por km: <b
                                                        id="dropCostoKmHTML">${{ number_format($costoKmCategoria ?? 0, 2) }}</b>
                                                </div>
                                                <div class="cargo-item-total">
                                                    <span class="cargo-item-total-label">Total Dropoff</span>
                                                    <span id="dropTotalHTML" class="cargo-item-total-amount">
                                                        ${{ number_format($dropTotal ?? 0, 2) }} MXN
                                                    </span>
                                                </div>
                                            </div>
                                        </div>

                                        {{-- GASOLINA --}}
                                        <div class="cargo-item gasolina-wrapper {{ $cargoGas ? 'active' : '' }}"
                                            data-id="5" data-monto="{{ $cargoGas->monto ?? 0 }}">
                                            <div class="cargo-item-header">
                                                <span class="servicio-numero"
                                                    data-tooltip="Paga solo la gasolina faltante para devolverlo vacío. ($20.00 MXN / Litro)">
                                                    <i class="fa-solid fa-gas-pump"></i>
                                                </span>
                                                <span class="servicio-nombre">Gasolina Prepago</span>
                                                <div class="cargo-item-toggle">
                                                    <label class="switch switch-toggle">
                                                        <input type="checkbox" id="switchGasolinaCheckbox"
                                                            {{ $cargoGas ? 'checked' : '' }}>
                                                        <span class="slider"></span>
                                                    </label>
                                                    <span class="toggle-label">Activar</span>
                                                </div>
                                            </div>

                                            <div id="gasolinaFields" class="cargo-item-fields"
                                                style="display: {{ $cargoGas ? 'block' : 'none' }}; text-align: center;">
                                                <div class="gasolina-info">
                                                    <div>Nivel actual del vehículo: <b
                                                            id="gasNivelTexto">{{ $vehiculo->nivel_gasolina ?? '--/16' }}</b>
                                                    </div>
                                                    <div>Faltante a cobrar: <b id="gasLitrosTexto">0 L</b></div>
                                                </div>
                                                <div class="cargo-item-total">
                                                    <span class="cargo-item-total-label">Costo Gasolina</span>
                                                    <span id="gasTotalHTML" class="cargo-item-total-amount">
                                                        ${{ number_format($cargoGas->monto ?? 0, 2) }} MXN
                                                    </span>
                                                </div>
                                                <input type="hidden" id="gasNivelActual" value="{{ $nivelFraccion }}">
                                                <input type="hidden" id="gasPrecioLitro" value="20">
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                {{-- ========================================== --}}
                                {{-- BOTONES --}}
                                {{-- ========================================== --}}
                                <div class="acciones">
                                    <button class="btn gray" id="back1">Atrás</button>
                                    <button class="btn primary" id="go3">Continuar protecciones</button>
                                </div>
                            </div>
                        </section>
                    </div>
                </article>

                {{-- paso 3 --}}
                <article class="step" data-step="3">
                    <div class="body paso3-body">
                        <div class="paso3-contenedor-blanco">
                            <h2 class="paso3-titulo-principal">Selecciona tus protecciones</h2>

                            <!-- Botón para abrir el modal -->
                            <div style="text-align: center; margin-bottom: 30px;">
                                <button type="button" class="btn-primary-modal" id="btnAbrirModalProtecciones">
                                    <i class="fas fa-shield-alt"></i>Seleccionar Protecciones
                                </button>
                            </div>

                            {{-- ============================================================ --}}
                            {{-- MODAL DE PANTALLA COMPLETA --}}
                            {{-- ============================================================ --}}
                            <div id="modalProtecciones" class="modal-fullscreen">
                                <div class="modal-fullscreen-content">
                                    {{-- HEADER DEL MODAL --}}
                                    <div class="modal-fullscreen-header">

                                        <div class="modal-header-left">
                                            <h2 class="modal-title">Protecciones</h2>
                                        </div>

                                        <div class="modal-header-right">

                                            {{-- CARRITO DEL MODAL --}}
                                            <div class="modal-resumen-wrapper">

                                                <button type="button" class="btn-resumen-contrato"
                                                    id="btnToggleDetalleModal">
                                                    <i class="fas fa-shopping-cart"></i>

                                                    <div class="resumen-totales-container">
                                                        <span id="btnTotalTextContratoModal">
                                                            {{ '$' . number_format($total ?? 0, 2) }} MXN
                                                        </span>

                                                        <small id="btnTotalUsdContratoModal" class="resumen-usd">
                                                            {{ '$' . number_format(($total ?? 0) / 20, 2) }} USD
                                                        </small>
                                                    </div>

                                                    <i class="fas fa-chevron-down" id="iconoFlechaResumenModal"></i>
                                                </button>

                                            </div>

                                            <button type="button" class="modal-close-btn-modal"
                                                id="btnCerrarModalProtecciones">
                                                ✕
                                            </button>

                                        </div>

                                    </div>
                                    {{-- DESPLEGABLE DEL CARRITO EN MODAL --}}
                                    <div class="resumen-desplegable-contrato resumen-desplegable-modal"
                                        id="resumenDetalleContainerModal" style="display:none;">

                                        <div class="resumen-card-contrato">

                                            <div class="head">
                                                <i class="fas fa-receipt"></i> RESUMEN DEL CONTRATO
                                            </div>

                                            <div class="cnt resumen-compacto" id="resumenCompactoModal">

                                                <div id="vehiculo_info_modal" class="vehiculo-mini-wrap">
                                                    <img id="resumenImgVehModal"
                                                        src="{{ $vehiculo->imagen_render ?? '' }}"
                                                        alt="Imagen del vehículo" class="vehiculo-img">

                                                    <p class="vehiculo-nombre" id="resumenVehCompactoModal">—</p>
                                                    <p class="vehiculo-mini" id="resumenCategoriaCompactoModal">Categoría:
                                                        —</p>
                                                    <p class="vehiculo-mini" id="resumenDiasCompactoModal">Días de renta:
                                                        —</p>
                                                    <p class="vehiculo-mini" id="resumenFechasCompactoModal">— / —</p>
                                                    <div class="protecciones-compacto-box">
                                                        <span class="protecciones-compacto-label">Protecciones</span>
                                                        <span class="protecciones-compacto-value" id="resumenProteccionesCompactoModal">
                                                            —
                                                        </span>
                                                    </div>
                                                </div>

                                                <div class="totalBox" style="margin-top:12px;">
                                                    <div class="kv">
                                                        <div>Total actual</div>

                                                        <div class="total" id="resumenTotalCompactoModal">
                                                            {{ '$' . number_format($total ?? 0, 2) }} MXN
                                                        </div>
                                                    </div>
                                                </div>

                                                <button type="button" id="btnVerDetalleModal"
                                                    class="btn-resumen-contrato-detalle">
                                                    Ver detalle ▼
                                                </button>

                                            </div>

                                            <div class="cnt resumen-detalle" id="resumenDetalleModal"
                                                style="display:none;">

                                                <div id="detalleContenidoModal">

                                                    <section class="res-block">
                                                        <h4>Código de reservación</h4>
                                                        <p id="detCodigoModal">—</p>
                                                    </section>

                                                    <section class="res-block">
                                                        <h4>Datos del cliente</h4>
                                                        <p id="detClienteModal">
                                                            {{ strtoupper($reservacion->nombre_cliente ?? '—') }}</p>
                                                        <p id="detTelefonoModal">{{ $telFinal ?? '—' }}</p>
                                                        <p id="detEmailModal">{{ $reservacion->email_cliente ?? '—' }}
                                                        </p>
                                                    </section>

                                                    <section class="res-block">
                                                        <h4>Vehículo</h4>
                                                        <p><b id="detModeloModal">—</b></p>
                                                        <p>Marca: <span id="detMarcaModal">—</span></p>
                                                        <p>Categoría: <span id="detCategoriaModal">—</span></p>
                                                        <p>Transmisión: <span id="detTransmisionModal">—</span></p>
                                                        <p>Pasajeros: <span id="detPasajerosModal">—</span></p>
                                                        <p>Puertas: <span id="detPuertasModal">—</span></p>
                                                        <p>Kilometraje actual: <span id="detKmModal">—</span></p>
                                                    </section>

                                                    <section class="res-block">
                                                        <h4>Fechas y horarios</h4>
                                                        <p>
                                                            Salida:
                                                            <span id="detFechaSalidaModal">—</span>
                                                            ·
                                                            <span id="detHoraSalidaModal">—</span>
                                                        </p>

                                                        <p>
                                                            Entrega:
                                                            <span id="detFechaEntregaModal">—</span>
                                                            ·
                                                            <span id="detHoraEntregaModal">—</span>
                                                        </p>

                                                        <p>
                                                            <strong>Días totales:</strong>
                                                            <span id="detDiasRentaModal">—</span>
                                                        </p>
                                                    </section>

                                                    <section class="res-block">
                                                        <h4>Paquetes de cobertura</h4>

                                                        <ul id="r_seguros_listaModal" class="det-lista">
                                                            <li class="empty">—</li>
                                                        </ul>

                                                        <p>Total: <b id="r_seguros_totalModal">—</b></p>
                                                    </section>

                                                    <section class="res-block">
                                                        <h4>Adicionales</h4>

                                                        <ul id="r_servicios_listaModal" class="det-lista">
                                                            <li class="empty">—</li>
                                                        </ul>

                                                        <p>Total: <b id="r_servicios_totalModal">—</b></p>
                                                    </section>

                                                    <section class="res-block">
                                                        <h4>Total desglosado</h4>
                                                        <p>Tarifa base: <b id="r_base_precioModal">—</b></p>
                                                        <p>Horas de cortesía: <b id="r_cortesiaModal">—</b></p>
                                                        <p>Subtotal: <b id="r_subtotalModal">—</b></p>
                                                        <p>IVA: <b id="r_ivaModal">—</b></p>
                                                        <p>Total contrato: <b id="r_total_finalModal">—</b></p>
                                                    </section>

                                                    <section class="res-block">
                                                        <h4>Pagos y saldo</h4>
                                                        <p>Pagos realizados: <b id="detPagosModal">—</b></p>
                                                        <p>Saldo pendiente: <b id="detSaldoModal">—</b></p>
                                                    </section>

                                                </div>

                                                <button type="button" id="btnOcultarDetalleModal"
                                                    class="btn-resumen-contrato-detalle">
                                                    Ocultar detalle ▲
                                                </button>

                                            </div>

                                        </div>

                                    </div>


                                    {{-- TABS DE NAVEGACIÓN --}}
                                    <div class="modal-tabs">
                                        <button class="tab-btn active" data-view="paquetes" id="tabPaquetes">
                                            Paquetes
                                        </button>
                                        <button class="tab-btn" data-view="individuales" id="tabIndividuales">
                                            Individuales
                                        </button>
                                    </div>

                                    {{-- CUERPO DEL MODAL --}}
                                    <div class="modal-fullscreen-body">
                                        {{-- VISTA DE PAQUETES --}}
                                        <div id="modal-vista-paquetes" class="modal-view active">
                                            <div class="scroll-h" id="paquetesScrollContainer">
                                                @foreach ($seguros as $seguro)
                                                    @php
                                                        $mapaTipos = [
                                                            'PROTECCIÓN BÁSICA' => 'CDW declinado',
                                                            'PROTECCIÓN ESTÁNDAR' => 'CDW 10%',
                                                            'PROTECCIÓN PREMIUM' => 'CDW 20%',
                                                            'PROTECCIÓN TOTAL 10%' => 'CDW 10%',
                                                            'PROTECCIÓN TOTAL 20%' => 'CDW 20%',
                                                            'PROTECCIÓN COMPLETA' => 'CDW 20%',
                                                            'DAÑOS A TERCEROS' => 'PDW',
                                                            'ROBO TOTAL' => 'CDW declinado',
                                                            'LDW' => 'LDW',
                                                            'PDW' => 'PDW',
                                                            'SEGURO BÁSICO' => 'CDW declinado',
                                                            'SEGURO ESTÁNDAR' => 'CDW 10%',
                                                            'SEGURO PREMIUM' => 'CDW 20%',
                                                            'CDW PACK 1' => 'CDW 10%',
                                                            'CDW PACK 2' => 'CDW 20%',
                                                            'CDW PACK 3' => 'CDW 20%',
                                                            'DECLINE PROTECTIONS' => 'CDW declinado',
                                                            'DECLINE CDW' => 'CDW declinado',
                                                            'DECLINE' => 'CDW declinado',
                                                        ];

                                                        $nombreUpper = strtoupper(trim($seguro->nombre));
                                                        $tipoProteccion = 'CDW declinado';

                                                        if (isset($mapaTipos[$nombreUpper])) {
                                                            $tipoProteccion = $mapaTipos[$nombreUpper];
                                                        } else {
                                                            foreach ($mapaTipos as $key => $value) {
                                                                if (
                                                                    strpos($nombreUpper, $key) !== false ||
                                                                    strpos($key, $nombreUpper) !== false
                                                                ) {
                                                                    $tipoProteccion = $value;
                                                                    break;
                                                                }
                                                            }
                                                        }
                                                    @endphp

                                                    <div class="pack-card seguro-item"
                                                        data-id="{{ $seguro->id_seguro }}"
                                                        data-precio="{{ $seguro->precio_por_dia }}"
                                                        data-tipo="{{ $tipoProteccion }}">

                                                        <div class="body">
                                                            {{-- TÍTULO --}}
                                                            <h4>{{ $seguro->nombre }}</h4>

                                                            {{-- LISTA DE COBERTURAS --}}
                                                            <ul class="desc-list">
                                                                @php
                                                                    $coberturas = explode(
                                                                        "\n",
                                                                        $seguro->cobertura ?? '',
                                                                    );

                                                                    // Palabras clave que se resaltarán en negrita (solo negro)
                                                                    $palabrasClave = [
                                                                        'Responsable',
                                                                        'cubierto',
                                                                        'Cubierta',
                                                                        'Premium',
                                                                        'Incluye',
                                                                        'no incluye',
                                                                        'Asistencia',
                                                                        'Gastos médicos',
                                                                        'Tiempo perdido',
                                                                        'Responsabilidad civil',
                                                                        'Asistencia Legal',
                                                                        'Perdida total',
                                                                        'Robo',
                                                                        'Deducible',
                                                                        'Daños',
                                                                        'valor factura',
                                                                        'llantas',
                                                                        'accesorios',
                                                                        'rines',
                                                                        'cristales',
                                                                        'Grúa',
                                                                        'corralón',
                                                                        'envío de llaves',
                                                                        'gasolina',
                                                                        'apertura de auto',
                                                                        'cambio de neumático',
                                                                        'paso de corriente',
                                                                        'bumper a bumper',
                                                                        'carrosería',
                                                                        'AL',
                                                                        'NO CUBRE',
                                                                        'por evento',
                                                                        'hasta',
                                                                        'de lado a lado',
                                                                        'pase lo que pase',
                                                                    ];
                                                                @endphp
                                                                @foreach ($coberturas as $cobertura)
                                                                    @if (trim($cobertura))
                                                                        <li>
                                                                            @php
                                                                                // Eliminar el guion al inicio del texto
                                                                                $texto = trim($cobertura);
                                                                                // Eliminar guion y espacio al inicio (ej: "- El cliente" -> "El cliente")
                                                                                $texto = preg_replace(
                                                                                    '/^[-\s]+/',
                                                                                    '',
                                                                                    $texto,
                                                                                );

                                                                                // 1. Resaltar porcentajes (ej: 0%, 5%, 10%, 20%, 30%) en negrita
                                                                                $texto = preg_replace(
                                                                                    '/(\d+%)/',
                                                                                    '<strong>$1</strong>',
                                                                                    $texto,
                                                                                );

                                                                                // 2. Resaltar montos en pesos (ej: 250,000 MXN, 3,000,000 MXN) en negrita
                                                                                $texto = preg_replace(
                                                                                    '/(\d{1,3}(?:,\d{3})*)\s*(MXN)/',
                                                                                    '<strong>$1 $2</strong>',
                                                                                    $texto,
                                                                                );

                                                                                // 3. Resaltar números sueltos (ej: 250,000, 3,000,000, 1,000,000)
                                                                                $texto = preg_replace(
                                                                                    '/(\b\d{1,3}(?:,\d{3})*\b)/',
                                                                                    '<strong>$1</strong>',
                                                                                    $texto,
                                                                                );

                                                                                // 4. Resaltar palabras clave en negrita
                                                                                foreach ($palabrasClave as $palabra) {
                                                                                    $texto = str_replace(
                                                                                        $palabra,
                                                                                        '<strong>' .
                                                                                            $palabra .
                                                                                            '</strong>',
                                                                                        $texto,
                                                                                    );
                                                                                }
                                                                            @endphp
                                                                            {!! $texto !!}
                                                                        </li>
                                                                    @endif
                                                                @endforeach
                                                                {{-- GARANTÍA --}}
                                                                <div class="garantia-item">
                                                                    <i class="fas fa-shield-alt"></i>
                                                                    <strong>GARANTÍA:</strong>
                                                                    <span class="garantia-valor"
                                                                        id="garantia-{{ $seguro->id_seguro }}">
                                                                        $0 MXN
                                                                    </span>
                                                                </div>
                                                            </ul>

                                                            {{-- PRECIO --}}
                                                            <div class="precio">
                                                                <strong>${{ number_format($seguro->precio_por_dia, 2) }}</strong>
                                                                <span>MXN / día</span>
                                                            </div>
                                                        </div>

                                                        {{-- BOTÓN --}}
                                                        <div class="actions">
                                                            <div class="btn-proteccion-wrapper">
                                                                <input type="radio" id="pack_{{ $seguro->id_seguro }}"
                                                                    name="paquete_seguro"
                                                                    value="{{ $seguro->id_seguro }}"
                                                                    class="input-paquete hidden-radio"
                                                                    {{ $seguro->id_seguro == ($seguroSeleccionado->id_seguro ?? null) ? 'checked' : '' }}>
                                                                <label for="pack_{{ $seguro->id_seguro }}"
                                                                    class="btn-proteccion-dividido desactivado">
                                                                    <span class="btn-texto">Seleccionar</span>
                                                                </label>
                                                            </div>
                                                        </div>
                                                    </div>
                                                @endforeach
                                            </div>
                                        </div>

                                        {{-- VISTA DE INDIVIDUALES --}}
                                        <div id="modal-vista-individuales" class="modal-view">
                                            <div class="nota-amarilla">
                                                <strong>Modo Personalizado:</strong> Arma tu propio paquete seleccionando
                                                protecciones individuales.
                                            </div>

                                            {{-- 1. Colisión y robo --}}
                                            <h4 class="categoria-titulo-individual">Colisión y robo</h4>
                                            <div class="individuales-grid">
                                                @foreach ($grupo_colision as $ind)
                                                    @php
                                                        // Limpiar el nombre: eliminar paréntesis y contenido
                                                        $nombreLimpio = preg_replace('/\([^)]*\)/', '', $ind->nombre);
                                                        $nombreLimpio = trim($nombreLimpio);

                                                        // Extraer el porcentaje si existe
                                                        $porcentaje = '';
                                                        $tieneDeducible = false;
                                                        if (preg_match('/(\d+%)/', $ind->nombre, $matches)) {
                                                            $porcentaje = $matches[1];
                                                            if (
                                                                strpos(strtoupper($ind->nombre), 'DEDUCIBLE') !== false
                                                            ) {
                                                                $tieneDeducible = true;
                                                            }
                                                        }

                                                        // Para "DECLINE CDW", mostrar "DC"
                                                        if (strpos(strtoupper($ind->nombre), 'DECLINE') !== false) {
                                                            $titulo = 'DECLINE CDW';
                                                        } else {
                                                            // Tomar el nombre completo limpio (sin paréntesis)
                                                            $titulo = $nombreLimpio;
                                                        }
                                                    @endphp
                                                    <div class="individual-card" data-id="{{ $ind->id_individual }}"
                                                        data-precio="{{ $ind->precio_por_dia }}">
                                                        {{-- Fila superior: Título + Tooltip --}}
                                                        <div class="individual-card-row">
                                                            <div class="individual-titulo">
                                                                <span
                                                                    class="individual-nombre">{{ $titulo }}</span>
                                                            </div>
                                                            <div class="individual-tooltip-wrapper">
                                                                <span class="individual-info-icon"
                                                                    data-tooltip="{{ $ind->descripcion }}">
                                                                    <i class="fas fa-info-circle"></i>
                                                                </span>
                                                                <div class="individual-tooltip-content">
                                                                    <p>{{ $ind->descripcion }}</p>
                                                                </div>
                                                            </div>
                                                        </div>

                                                        {{-- Precio en burbuja --}}
                                                        <div class="individual-precio-bubble">
                                                            <span
                                                                class="precio-monto">${{ number_format($ind->precio_por_dia, 2) }}</span>
                                                            <span class="precio-por-dia">MXN x DÍA</span>
                                                        </div>

                                                        {{-- Switch en pastilla --}}
                                                        <div class="individual-switch-wrapper">
                                                            <label class="switch-pill">
                                                                <input type="checkbox"
                                                                    id="ind_{{ $ind->id_individual }}"
                                                                    value="{{ $ind->id_individual }}"
                                                                    class="input-individual switch-individual"
                                                                    data-id="{{ $ind->id_individual }}">
                                                                <span class="slider-pill"></span>
                                                            </label>
                                                        </div>
                                                    </div>
                                                @endforeach
                                            </div>

                                            {{-- 2. Gastos médicos --}}
                                            <h4 class="categoria-titulo-individual">Gastos médicos</h4>
                                            <div class="individuales-grid">
                                                @foreach ($grupo_medicos as $ind)
                                                    @php
                                                        $nombreLimpio = preg_replace('/\([^)]*\)/', '', $ind->nombre);
                                                        $nombreLimpio = trim($nombreLimpio);

                                                        $porcentaje = '';
                                                        $tieneDeducible = false;
                                                        if (preg_match('/(\d+%)/', $ind->nombre, $matches)) {
                                                            $porcentaje = $matches[1];
                                                            if (
                                                                strpos(strtoupper($ind->nombre), 'DEDUCIBLE') !== false
                                                            ) {
                                                                $tieneDeducible = true;
                                                            }
                                                        }

                                                        if (strpos(strtoupper($ind->nombre), 'DECLINE') !== false) {
                                                            $titulo = 'DC';
                                                        } else {
                                                            $titulo = $nombreLimpio;
                                                        }
                                                    @endphp
                                                    <div class="individual-card" data-id="{{ $ind->id_individual }}"
                                                        data-precio="{{ $ind->precio_por_dia }}">
                                                        <div class="individual-card-row">
                                                            <div class="individual-titulo">
                                                                <span
                                                                    class="individual-nombre">{{ $titulo }}</span>
                                                            </div>
                                                            <div class="individual-tooltip-wrapper">
                                                                <span class="individual-info-icon"
                                                                    data-tooltip="{{ $ind->descripcion }}">
                                                                    <i class="fas fa-info-circle"></i>
                                                                </span>
                                                                <div class="individual-tooltip-content">
                                                                    <p>{{ $ind->descripcion }}</p>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="individual-precio-bubble">
                                                            <span
                                                                class="precio-monto">${{ number_format($ind->precio_por_dia, 2) }}</span>
                                                            <span class="precio-por-dia">MXN x DÍA</span>
                                                        </div>
                                                        <div class="individual-switch-wrapper">
                                                            <label class="switch-pill">
                                                                <input type="checkbox"
                                                                    id="ind_{{ $ind->id_individual }}"
                                                                    value="{{ $ind->id_individual }}"
                                                                    class="input-individual switch-individual"
                                                                    data-id="{{ $ind->id_individual }}">
                                                                <span class="slider-pill"></span>
                                                            </label>
                                                        </div>
                                                    </div>
                                                @endforeach
                                            </div>

                                            {{-- 3. Asistencia para el camino --}}
                                            <h4 class="categoria-titulo-individual">Asistencia para el camino</h4>
                                            <div class="individuales-grid">
                                                @foreach ($grupo_asistencia as $ind)
                                                    @php
                                                        $nombreLimpio = preg_replace('/\([^)]*\)/', '', $ind->nombre);
                                                        $nombreLimpio = trim($nombreLimpio);

                                                        $porcentaje = '';
                                                        $tieneDeducible = false;
                                                        if (preg_match('/(\d+%)/', $ind->nombre, $matches)) {
                                                            $porcentaje = $matches[1];
                                                            if (
                                                                strpos(strtoupper($ind->nombre), 'DEDUCIBLE') !== false
                                                            ) {
                                                                $tieneDeducible = true;
                                                            }
                                                        }

                                                        if (strpos(strtoupper($ind->nombre), 'DECLINE') !== false) {
                                                            $titulo = 'DC';
                                                        } else {
                                                            $titulo = $nombreLimpio;
                                                        }
                                                    @endphp
                                                    <div class="individual-card" data-id="{{ $ind->id_individual }}"
                                                        data-precio="{{ $ind->precio_por_dia }}">
                                                        <div class="individual-card-row">
                                                            <div class="individual-titulo">
                                                                <span
                                                                    class="individual-nombre">{{ $titulo }}</span>
                                                            </div>
                                                            <div class="individual-tooltip-wrapper">
                                                                <span class="individual-info-icon"
                                                                    data-tooltip="{{ $ind->descripcion }}">
                                                                    <i class="fas fa-info-circle"></i>
                                                                </span>
                                                                <div class="individual-tooltip-content">
                                                                    <p>{{ $ind->descripcion }}</p>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="individual-precio-bubble">
                                                            <span
                                                                class="precio-monto">${{ number_format($ind->precio_por_dia, 2) }}</span>
                                                            <span class="precio-por-dia">MXN x DÍA</span>
                                                        </div>
                                                        <div class="individual-switch-wrapper">
                                                            <label class="switch-pill">
                                                                <input type="checkbox"
                                                                    id="ind_{{ $ind->id_individual }}"
                                                                    value="{{ $ind->id_individual }}"
                                                                    class="input-individual switch-individual"
                                                                    data-id="{{ $ind->id_individual }}">
                                                                <span class="slider-pill"></span>
                                                            </label>
                                                        </div>
                                                    </div>
                                                @endforeach
                                            </div>

                                            {{-- 4. Daños a terceros --}}
                                            {{-- 4. Daños a terceros --}}
                                            @php
                                                $tercerosFiltrados = collect($grupo_terceros)
                                                    ->filter(function ($item) {
                                                        return stripos($item->nombre, 'DECLINE') === false;
                                                    })
                                                    ->sortByDesc('precio_por_dia'); // ORDENAR DE MAYOR A MENOR
                                            @endphp

                                            @if ($tercerosFiltrados->isNotEmpty())
                                                <h4 class="categoria-titulo-individual">Daños a terceros</h4>
                                                <div class="individuales-grid">
                                                    @foreach ($tercerosFiltrados as $ind)
                                                        @php
                                                            $nombreLimpio = preg_replace(
                                                                '/\([^)]*\)/',
                                                                '',
                                                                $ind->nombre,
                                                            );
                                                            $nombreLimpio = trim($nombreLimpio);

                                                            $porcentaje = '';
                                                            $tieneDeducible = false;
                                                            if (preg_match('/(\d+%)/', $ind->nombre, $matches)) {
                                                                $porcentaje = $matches[1];
                                                                if (
                                                                    strpos(strtoupper($ind->nombre), 'DEDUCIBLE') !==
                                                                    false
                                                                ) {
                                                                    $tieneDeducible = true;
                                                                }
                                                            }

                                                            $titulo = $nombreLimpio;
                                                        @endphp
                                                        <div class="individual-card" data-id="{{ $ind->id_individual }}"
                                                            data-precio="{{ $ind->precio_por_dia }}">
                                                            <div class="individual-card-row">
                                                                <div class="individual-titulo">
                                                                    <span
                                                                        class="individual-nombre">{{ $titulo }}</span>
                                                                </div>
                                                                <div class="individual-tooltip-wrapper">
                                                                    <span class="individual-info-icon"
                                                                        data-tooltip="{{ $ind->descripcion }}">
                                                                        <i class="fas fa-info-circle"></i>
                                                                    </span>
                                                                    <div class="individual-tooltip-content">
                                                                        <p>{{ $ind->descripcion }}</p>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <div class="individual-precio-bubble">
                                                                <span
                                                                    class="precio-monto">${{ number_format($ind->precio_por_dia, 2) }}</span>
                                                                <span class="precio-por-dia">MXN x DÍA</span>
                                                            </div>
                                                            <div class="individual-switch-wrapper">
                                                                <label class="switch-pill">
                                                                    <input type="checkbox"
                                                                        id="ind_{{ $ind->id_individual }}"
                                                                        value="{{ $ind->id_individual }}"
                                                                        class="input-individual switch-individual"
                                                                        data-id="{{ $ind->id_individual }}">
                                                                    <span class="slider-pill"></span>
                                                                </label>
                                                            </div>
                                                        </div>
                                                    @endforeach
                                                </div>
                                            @endif

                                            {{-- 5. Protecciones automáticas --}}
                                            <h4 class="categoria-titulo-individual">Protecciones automáticas</h4>
                                            <div class="individuales-grid">
                                                @foreach ($grupo_protecciones as $ind)
                                                    @php
                                                        $nombreLimpio = preg_replace('/\([^)]*\)/', '', $ind->nombre);
                                                        $nombreLimpio = trim($nombreLimpio);

                                                        $porcentaje = '';
                                                        $tieneDeducible = false;
                                                        if (preg_match('/(\d+%)/', $ind->nombre, $matches)) {
                                                            $porcentaje = $matches[1];
                                                            if (
                                                                strpos(strtoupper($ind->nombre), 'DEDUCIBLE') !== false
                                                            ) {
                                                                $tieneDeducible = true;
                                                            }
                                                        }

                                                        if (strpos(strtoupper($ind->nombre), 'DECLINE') !== false) {
                                                            $titulo = 'DC';
                                                        } else {
                                                            $titulo = $nombreLimpio;
                                                        }
                                                    @endphp
                                                    <div class="individual-card" data-id="{{ $ind->id_individual }}"
                                                        data-precio="{{ $ind->precio_por_dia }}">
                                                        <div class="individual-card-row">
                                                            <div class="individual-titulo">
                                                                <span
                                                                    class="individual-nombre">{{ $titulo }}</span>
                                                            </div>
                                                            <div class="individual-tooltip-wrapper">
                                                                <span class="individual-info-icon"
                                                                    data-tooltip="{{ $ind->descripcion }}">
                                                                    <i class="fas fa-info-circle"></i>
                                                                </span>
                                                                <div class="individual-tooltip-content">
                                                                    <p>{{ $ind->descripcion }}</p>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="individual-precio-bubble">
                                                            <span
                                                                class="precio-monto">${{ number_format($ind->precio_por_dia, 2) }}</span>
                                                            <span class="precio-por-dia">MXN x DÍA</span>
                                                        </div>
                                                        <div class="individual-switch-wrapper">
                                                            <label class="switch-pill">
                                                                <input type="checkbox"
                                                                    id="ind_{{ $ind->id_individual }}"
                                                                    value="{{ $ind->id_individual }}"
                                                                    class="input-individual switch-individual"
                                                                    data-id="{{ $ind->id_individual }}">
                                                                <span class="slider-pill"></span>
                                                            </label>
                                                        </div>
                                                    </div>
                                                @endforeach
                                            </div>
                                        </div>
                                    </div>

                                    {{-- FOOTER DEL MODAL --}}
                                    <div class="modal-fullscreen-footer">
                                        <div class="resumen-total-inline">
                                            Total de protecciones: <span id="total_seguros_modal"
                                                class="total-seguros-color">${{ isset($seguroSeleccionado) ? number_format($seguroSeleccionado->precio_por_dia, 2) : '0.00' }}
                                                MXN</span>
                                        </div>
                                        <div class="modal-footer-buttons">
                                            <button type="button" class="btn gray"
                                                id="btnCerrarModalFooter">Cerrar</button>
                                            <button type="button" class="btn primary"
                                                id="btnAplicarProtecciones">Aplicar</button>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            {{-- Resumen actual fuera del modal --}}
                            <div class="resumen-seleccion-paso"
                                style="margin-top: 20px; padding: 15px 20px; background: #f8fafc; border-radius: 10px; border: 1px solid #e2e8f0;">
                                <div
                                    style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 10px;">
                                    <div style="display: flex; align-items: center; gap: 12px; flex-wrap: wrap;">
                                        <span style="font-weight: 600; color: #1e293b; font-size: 0.95rem;"><i
                                                class="fas fa-shield-alt"></i>Protección seleccionada:</span>
                                        <span id="resumen_nombre_proteccion"
                                            style="font-weight: 500; color: #22c55e; font-size: 0.95rem; background: #dcfce7; padding: 4px 12px; border-radius: 20px;">
                                            Ninguna
                                        </span>
                                    </div>
                                    <div
                                        style="display: flex; align-items: center; gap: 8px; background: #ffffff; padding: 4px 16px; border-radius: 20px; border: 1px solid #e2e8f0;">
                                        <span style="font-weight: 600; color: #475569; font-size: 0.85rem;">Total:</span>
                                        <span id="total_seguros_resumen" class="total-seguros-color"
                                            style="font-weight: 700; color: #1e293b; font-size: 1rem;">
                                            ${{ isset($seguroSeleccionado) ? number_format($seguroSeleccionado->precio_por_dia, 2) : '0.00' }}
                                            MXN
                                        </span>
                                    </div>
                                </div>
                            </div>

                            <div class="boton-atras-paso3">
                                <button class="btn gray" id="back2"> Atrás</button>
                                <button type="button" class="btn-rojo-ancho disabled" id="go4">Continuar con
                                    Documentación</button>
                            </div>

                        </div>
                    </div>
                </article>

            </section>

        </div>

        {{-- ========================================================================= --}}
        {{-- ================================ MODALES ================================ --}}
        {{-- ========================================================================= --}}

        {{-- Modal de vehículos --}}
        <div id="modalVehiculos" class="modal-vehiculos">
            <div class="modal-vehiculos-content">
                <div class="modal-vehiculos-header">
                    <span class="modal-vehiculos-titulo">Inventario de Vehículos</span>
                    <button type="button" id="cerrarModalVehiculos" class="modal-close-btn"><i
                            class="fas fa-times"></i></button>
                </div>

                {{-- Filtros superiores (Nuevas clases del CSS) --}}
                <div class="modal-vehiculos-filtros">
                    <span class="filtros-label">Filtros rápidos:</span>
                    <input type="text" id="filtroPlacas" placeholder="Placas..." class="filtro-input">
                    <input type="text" id="filtroColor" placeholder="Color..." class="filtro-input">
                    <input type="text" id="filtroModelo" placeholder="Modelo..." class="filtro-input">
                    <input type="text" id="filtroCategoria" placeholder="Categoría..." class="filtro-input">
                </div>

                {{-- Tabla con scroll --}}
                <div class="table-responsive modal-vehiculos-tabla">
                    <table class="tabla-excel-vehiculos">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>PLACAS</th>
                                <th>CATEGORÍA</th>
                                <th>MODELO</th>
                                <th>TRANSMISIÓN</th>
                                <th>COLOR</th>
                                <th>GAS (1/16)</th>
                                <th>GAS (L)</th>
                                <th>KILOMETRAJE</th>
                                <th>VERIF.</th>
                                <th>MANTENIMIENTO</th>
                                <th>SEGURO</th>
                                <th class="accion-header">ACCIÓN</th>
                            </tr>
                        </thead>
                        <tbody id="listaVehiculosTabla">
                            {{-- JavaScript inyectará las filas aquí --}}
                        </tbody>
                    </table>
                </div>

                <div
                    style="padding: 16px 24px; background: #f8fafc; border-top: 1px solid #e2e8f0; display: flex; justify-content: flex-end;">
                    <button id="cerrarModalVehiculos2" class="btn gray">Cerrar inventario</button>
                </div>
            </div>
        </div>

        {{-- Modal de confirmación de edición de inventario --}}
        <div id="modalConfirmEdicion" class="modal-vehiculos" style="display:none; z-index:100001;">
            <div class="modal-vehiculos-content" style="max-width:440px; height:auto;">
                <div class="modal-vehiculos-header">
                    <span class="modal-vehiculos-titulo">Confirmar cambio</span>
                    <button type="button" id="cerrarConfirmEdicion" class="modal-close-btn">✕</button>
                </div>

                <div style="padding:24px;">
                    <p style="margin:0 0 16px; color:#475569;">Vas a modificar el siguiente vehículo:</p>

                    <div
                        style="background:#f8fafc; border:1px solid #e2e8f0; border-radius:8px; padding:16px; margin-bottom:16px;">
                        <div style="display:flex; justify-content:space-between; margin-bottom:8px;">
                            <span style="color:#64748b;">Categoría:</span>
                            <b id="confCategoria">—</b>
                        </div>
                        <div style="display:flex; justify-content:space-between; margin-bottom:8px;">
                            <span style="color:#64748b;">Color:</span>
                            <b id="confColor">—</b>
                        </div>
                        <div style="display:flex; justify-content:space-between;">
                            <span style="color:#64748b;">Placas:</span>
                            <b id="confPlacas">—</b>
                        </div>
                    </div>

                    <div style="background:#eff6ff; border:1px solid #bfdbfe; border-radius:8px; padding:12px 16px;">
                        <div style="display:flex; justify-content:space-between;">
                            <span style="color:#1e40af;" id="confCampoLabel">Campo</span>
                            <span>
                                <s id="confValorAnterior" style="color:#94a3b8;">—</s>
                                <b id="confValorNuevo" style="color:#1d4ed8; margin-left:8px;">—</b>
                            </span>
                        </div>
                    </div>
                </div>

                <div
                    style="padding:16px 24px; background:#f8fafc; border-top:1px solid #e2e8f0; display:flex; justify-content:flex-end; gap:8px;">
                    <button id="btnCancelarEdicion" class="btn gray">Cancelar</button>
                    <button id="btnConfirmarEdicion" class="btn primary">Confirmar</button>
                </div>
            </div>
        </div>

        {{-- Modal de upgrade --}}
        <div id="modalUpgrade" class="upgrade-modal">
            <div class="upgrade-card">
                <button class="upgrade-close" id="cerrarUpgrade">✕</button>
                <div class="upgrade-discount-badge"><span id="upgDescuento"></span></div>
                <div class="upgrade-image-wrapper"><img id="upgImagenVehiculo" src="" alt="Vehículo upgrade">
                </div>
                <h3 class="upgrade-categoria" id="upgTitulo"></h3>
                <h4 class="upgrade-nombre-vehiculo" id="upgNombreVehiculo"></h4>
                <p class="upgrade-descripcion" id="upgDescripcion"></p>
                <div class="upgrade-beneficios" id="upgBeneficios"></div>
                <div id="upgSpecs" class="upgrade-specs"></div>
                <div class="upgrade-precios">
                    <span class="upgrade-precio-inflado" id="upgPrecioInflado"></span>
                    <span class="upgrade-precio-real" id="upgPrecioReal"></span>
                </div>
                <div class="upgrade-buttons">
                    <button id="btnRechazarUpgrade" class="btn-upgrade-cancel">No gracias</button>
                    <button id="btnAceptarUpgrade" class="btn-upgrade-accept">Aceptar upgrade</button>
                </div>
            </div>
        </div>

        {{-- Modal de cambio de categoría (Dinámico) --}}
        <div id="modalCategorias" class="modal-vehiculos">
            <div class="modal-vehiculos-content">
                <div class="modal-vehiculos-header">
                    <span class="modal-vehiculos-titulo">Selecciona una nueva Categoría</span>
                    <button type="button" id="cerrarModalCategorias" class="modal-close-btn">✕</button>
                </div>

                <div class="categorias-grid" id="contenedorCategoriasJS">
                </div>

                <div
                    style="padding: 16px 24px; background: #f8fafc; border-top: 1px solid #e2e8f0; display: flex; justify-content: flex-end;">
                    <button id="cerrarModalCategorias2" class="btn gray">Cancelar</button>
                </div>
            </div>
        </div>

    </main>
@endsection

@section('js-vistaContrato')
    <script>
        window.ID_RESERVACION = "{{ $reservacion->id_reservacion ?? '' }}";
        window.ID_CONTRATO = "{{ $contrato->id_contrato ?? '' }}";
        window.csrfToken = "{{ csrf_token() }}";

        window.contratoId = window.ID_CONTRATO;
        window.clienteContratoUrl = "{{ route('contrato.obtenerCliente', $contrato->id_contrato ?? 0) }}";
    </script>
    <script src="{{ asset('js/ContratoGlobal.js') }}" defer></script>
    <script src="{{ asset('js/Contrato.js') }}" defer></script>
@endsection
